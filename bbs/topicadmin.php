<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: topicadmin.php 16905 2008-11-27 05:50:59Z cnteacher $
*/

define('CURSCRIPT', 'topicadmin');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/post.func.php';
require_once DISCUZ_ROOT.'./include/misc.func.php';

$discuz_action = 201;
$modpostsnum = $iscircleadmin = 0;
$resultarray = $thread = array();

if($gid = intval($gid)) {
        if($supe['circlestatus'] && $discuz_uid && $action == 'moderate' && $operation == 'delete' && supe_submitcheck(1)) {
                supe_dbconnect();
        	$query = $supe['db']->query("SELECT COUNT(*) FROM {$supe[tablepre]}groupuid WHERE gid='$gid' AND uid='$discuz_uid' AND flag>1", 'SILENT');
                $iscircleadmin = $db->result($query, 0);
        }
}

if(!$discuz_uid || !($forum['ismoderator'] || $iscircleadmin)) {
	showmessage('admin_nopermission', NULL, 'HALTED');
}

if($forum['type'] == 'forum') {
	$navigation = "&raquo; <a href=\"forumdisplay.php?fid=$fid\">$forum[name]</a>";
	$navtitle = strip_tags($forum['name']);
} else {
	$fup = $db->fetch_first("SELECT fid, name FROM {$tablepre}forums WHERE fid='$forum[fup]'");
	$navigation = "&raquo; <a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">$forum[name]</a> ";
	$navtitle = strip_tags($fup['name']).' - '.strip_tags($forum['name']);
}

if(!empty($tid)) {

	$thread = $db->fetch_first("SELECT * FROM {$tablepre}threads WHERE tid='$tid' AND fid='$fid' AND displayorder>='0'");
	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	$navigation .= " &raquo; <a href=\"viewthread.php?tid=$tid\">$thread[subject]</a> ";
	$navtitle .= ' - '.$thread['subject'].' - ';

	if(($thread['special'] && in_array($action, array('copy', 'split', 'merge'))) || ($thread['digest'] == '-1' && !in_array($action, array('delpost', 'banpost', 'getip')))) {
		showmessage('special_noaction');
	}
}
// Reason P.M. Preprocess Start
$reasonpmcheck = $reasonpm == 2 || $reasonpm == 3 ? 'checked="checked" disabled' : '';
if(($reasonpm == 2 || $reasonpm == 3) || !empty($sendreasonpm)) {
	$forumname = strip_tags($forum['name']);
	$sendreasonpm = 1;
} else {
	$sendreasonpm = 0;
}
// End

$postcredits = $forum['postcredits'] ? $forum['postcredits'] : $creditspolicy['post'];
$replycredits = $forum['replycredits'] ? $forum['replycredits'] : $creditspolicy['reply'];
$digestcredits = $forum['digestcredits'] ? $forum['digestcredits'] : $creditspolicy['digest'];
$postattachcredits = $forum['postattachcredits'] ? $forum['postattachcredits'] : $creditspolicy['postattach'];


if(in_array($action, array('moderate', 'delete', 'move', 'highlight', 'type', 'close', 'stick', 'digest', 'supe_push', 'bump', 'recommend'))) {

	require_once DISCUZ_ROOT.'./include/moderation.inc.php';

} elseif($action == 'delpost') {

	if(!($deletepids = implodeids($topiclist))) {
		showmessage('admin_delpost_invalid');
	} elseif(!$allowdelpost || !$tid) {
		showmessage('admin_nopermission', NULL, 'HALTED');
	}  else {
		$query = $db->query("SELECT pid FROM {$tablepre}posts WHERE pid IN ($deletepids) AND first='1'");
		if($db->num_rows($query)) {
			dheader("Location: {$boardurl}topicadmin.php?action=delete&tid=$thread[tid]");
		}
	}

	if(!submitcheck('delpostsubmit')) {

		$deleteid = '';
		foreach($topiclist as $id) {
			$deleteid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
		}

		include template('topicadmin_delpost');

	} else {

		checkreasonpm();

		$pids = 0;
		$posts = $uidarray = $puidarray = $auidarray = array();
		$losslessdel = $losslessdel > 0 ? $timestamp - $losslessdel * 86400 : 0;
		$query = $db->query("SELECT pid, authorid, dateline, message, first FROM {$tablepre}posts WHERE pid IN ($deletepids) AND tid='$tid'");
		while($post = $db->fetch_array($query)) {
			if(!$post['first']) {
				$posts[] = $post;
				$pids .= ','.$post['pid'];
				if($post['dateline'] < $losslessdel) {
					$uidarray[] = $post['authorid'];
				} else {
					$puidarray[] = $post['authorid'];
				}
				$modpostsnum ++;
			}
		}

		if($uidarray) {
			updatepostcredits('-', $uidarray, array());
		}
		if($puidarray) {
			updatepostcredits('-', $puidarray, $replycredits);
		}
		$query = $db->query("SELECT uid, attachment, thumb, remote FROM {$tablepre}attachments WHERE pid IN ($pids)");
		while($attach = $db->fetch_array($query)) {
			if(in_array($attach['uid'], $puidarray)) {
				$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
			}
			dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
		}
		if($auidarray) {
			updateattachcredits('-', $auidarray, $postattachcredits);
		}

		$logs = array();
		$query = $db->query("SELECT r.extcredits, r.score, p.authorid, p.author FROM {$tablepre}ratelog r LEFT JOIN {$tablepre}posts p ON r.pid=p.pid WHERE r.pid IN ($pids)");
		while($author = $db->fetch_array($query)) {
			if($author['score'] > 0) {
				$db->query("UPDATE {$tablepre}members SET extcredits{$author[extcredits]}=extcredits{$author[extcredits]}-($author[score]) WHERE uid = $author[authorid]");
				$author[score] = $extcredits[$id]['title'].' '.-$author[score].' '.$extcredits[$id]['unit'];
				$logs[] = dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$author[author]\t$author[extcredits]\t$author[score]\t$thread[tid]\t$thread[subject]\t$delpostsubmit");
			}
		}
		if(!empty($logs)) {
			writelog('ratelog', $logs);
			unset($logs);
		}

		$db->query("DELETE FROM {$tablepre}ratelog WHERE pid IN ($pids)");
		$db->query("DELETE FROM {$tablepre}myposts WHERE pid IN ($pids)");
		$db->query("DELETE FROM {$tablepre}attachments WHERE pid IN ($pids)");
		$db->query("DELETE FROM {$tablepre}posts WHERE pid IN ($pids)");

		if($thread['special']) {
			$db->query("DELETE FROM {$tablepre}trades WHERE pid IN ($pids)");
		}

		updatethreadcount($tid, 1);
		updateforumcount($fid);

		$forum['threadcaches'] && deletethreadcaches($thread['tid']);

		$modaction = 'DLP';

		$resultarray = array(
		'redirect'	=> "viewthread.php?tid=$tid&amp;page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_delete_post') : array()),
		'modtids'	=> 0,
		'modlog'	=> $thread
		);

	}

} elseif($action == 'refund' && $allowrefund && $thread['price'] > 0) {

	if(!isset($extcredits[$creditstrans])) {
		showmessage('credits_transaction_disabled');
	}

	if($thread['special'] != 0) {
		showmessage('special_refundment_invalid');
	}

	if(!submitcheck('refundsubmit')) {

		$payment = $db->fetch_first("SELECT COUNT(*) AS payers, SUM(netamount) AS netincome FROM {$tablepre}paymentlog WHERE tid='$tid'");

		include template('topicadmin_refund');

	} else {

		$modaction = 'RFD';
		$modpostsnum ++;

		checkreasonpm();

		$totalamount = 0;
		$amountarray = array();

		$logarray = array();
		$query = $db->query("SELECT * FROM {$tablepre}paymentlog WHERE tid='$tid'");
		while($log = $db->fetch_array($query)) {
			$totalamount += $log['amount'];
			$amountarray[$log['amount']][] = $log['uid'];
		}

		$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-$totalamount WHERE uid='$thread[authorid]'");
		$db->query("UPDATE {$tablepre}threads SET price='-1', moderated='1' WHERE tid='$thread[tid]'");

		foreach($amountarray as $amount => $uidarray) {
			$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+$amount WHERE uid IN (".implode(',', $uidarray).")");
		}

		$db->query("UPDATE {$tablepre}paymentlog SET amount='0', netamount='0' WHERE tid='$tid'");

		$resultarray = array(
		'redirect'	=> "viewthread.php?tid=$tid",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_moderate') : array()),
		'modtids'	=> $thread['tid'],
		'modlog'	=> $thread
		);

	}

} elseif($action == 'repair') {

	$replies = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='$tid' AND invisible='0'") - 1;

	$query = $db->query("SELECT a.aid FROM {$tablepre}posts p, {$tablepre}attachments a WHERE a.tid='$tid' AND a.pid=p.pid AND p.invisible='0' LIMIT 1");
	$attachment = $db->num_rows($query) ? 1 : 0;

	$firstpost  = $db->fetch_first("SELECT pid, subject, rate FROM {$tablepre}posts WHERE tid='$tid' AND invisible='0' ORDER BY dateline LIMIT 1");
	$firstpost['subject'] = addslashes(cutstr($firstpost['subject'], 79));
	@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);

	$lastpost  = $db->fetch_first("SELECT author, dateline FROM {$tablepre}posts WHERE tid='$tid' AND invisible='0' ORDER BY dateline DESC LIMIT 1");

	$db->query("UPDATE {$tablepre}threads SET subject='$firstpost[subject]', replies='$replies', lastpost='$lastpost[dateline]', lastposter='".addslashes($lastpost['author'])."', rate='$firstpost[rate]', attachment='$attachment' WHERE tid='$tid'", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}posts SET first='1', subject='$firstpost[subject]' WHERE pid='$firstpost[pid]'", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}posts SET first='0' WHERE tid='$tid' AND pid<>'$firstpost[pid]'", 'UNBUFFERED');
	showmessage('admin_succeed', "viewthread.php?tid=$tid");

} elseif($action == 'getip' && $allowviewip) {

	$member = $db->fetch_first("SELECT m.adminid, p.first, p.useip FROM {$tablepre}posts p
				LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
				WHERE pid='$pid' AND tid='$tid'");
	if(!$member) {
		showmessage('thread_nonexistence', NULL, 'HALTED');
	} elseif(($member['adminid'] == 1 && $adminid > 1) || ($member['adminid'] == 2 && $adminid > 2)) {
		showmessage('admin_getip_nopermission', NULL, 'HALTED');
	} elseif($member['first'] && $thread['digest'] == '-1') {
		showmessage('special_noaction');
	}

	$member['iplocation'] = convertip($member['useip']);

	include template('topicadmin_getip');

} elseif($action == 'split') {

	if(!submitcheck('splitsubmit')) {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$replies = $thread['replies'];
		if($replies <= 0) {
			showmessage('admin_split_invalid');
		}

		$postlist = array();
		$query = $db->query("SELECT * FROM {$tablepre}posts WHERE tid='$tid' ORDER BY dateline");
		while($post = $db->fetch_array($query)) {
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $forum['allowsmilies'], $forum['allowbbcode'], $forum['allowimgcode'], $forum['allowhtml']);
			$postlist[] = $post;
		}

		include template('topicadmin_split');

	} else {

		if(!trim($subject)) {
			showmessage('admin_split_subject_invalid');
		} elseif(!$pids = implodeids($split)) {
			showmessage('admin_split_new_invalid');
		}

		$modaction = 'SPL';

		checkreasonpm();

		$db->query("INSERT INTO {$tablepre}threads (fid, subject) VALUES ('$fid', '".dhtmlspecialchars($subject)."')");
		$newtid = $db->insert_id();

		$db->query("UPDATE {$tablepre}posts SET tid='$newtid' WHERE pid IN ($pids)");
		$db->query("UPDATE {$tablepre}attachments SET tid='$newtid' WHERE pid IN ($pids)");

		$splitauthors = array();
		$query = $db->query("SELECT pid, tid, authorid, subject, dateline FROM {$tablepre}posts WHERE tid='$newtid' AND invisible='0' GROUP BY authorid ORDER BY dateline");
		while($splitauthor = $db->fetch_array($query)) {
			$splitauthor['subject'] = $subject;
			$splitauthors[] = $splitauthor;
		}

		$db->query("UPDATE {$tablepre}posts SET first='1', subject='$subject' WHERE pid='".$splitauthors[0]['pid']."'", 'UNBUFFERED');

		$fpost = $db->fetch_first("SELECT pid, author, authorid, dateline FROM {$tablepre}posts WHERE tid='$tid' ORDER BY dateline LIMIT 1");
		$db->query("UPDATE {$tablepre}threads SET author='$fpost[author]', authorid='$fpost[authorid]', dateline='$fpost[dateline]', moderated='1' WHERE tid='$tid'");
		$db->query("UPDATE {$tablepre}posts SET subject='".addslashes($thread['subject'])."' WHERE pid='$fpost[pid]'");

		$fpost = $db->fetch_first("SELECT author, authorid, dateline, rate FROM {$tablepre}posts WHERE tid='$newtid' ORDER BY dateline ASC LIMIT 1");
		$db->query("UPDATE {$tablepre}threads SET author='$fpost[author]', authorid='$fpost[authorid]', dateline='$fpost[dateline]', rate='".intval(@($fpost['rate'] / abs($fpost['rate'])))."', moderated='1' WHERE tid='$newtid'");

		updatethreadcount($tid);
		updatethreadcount($newtid);
		updateforumcount($fid);

		$forum['threadcaches'] && deletethreadcaches($thread['tid']);

		$modpostsnum++;
		$resultarray = array(
		'redirect'	=> "forumdisplay.php?fid=$fid",
		'reasonpm'	=> ($sendreasonpm ? array('data' => $splitauthors, 'var' => 'thread', 'item' => 'reason_moderate') : array()),
		'modtids'	=> $thread['tid'].','.$newtid,
		'modlog'	=> array($thread, array('tid' => $newtid, 'subject' => $subject))
		);

	}

} elseif($action == 'merge') {

	if(!submitcheck('mergesubmit')) {

		include template('topicadmin_merge');

	} else {

		$modaction = 'MRG';

		checkreasonpm();

		$other = $db->fetch_first("SELECT tid, fid, authorid, subject, views, replies, dateline, special FROM {$tablepre}threads WHERE tid='$othertid' AND displayorder>='0'");
		if(!$other) {
			showmessage('admin_merge_nonexistence');
		} elseif($other['special']) {
			showmessage('special_noaction');
		}
		if($othertid == $tid || ($adminid == 3 && $other['fid'] != $forum['fid'])) {
			showmessage('admin_merge_invalid');
		}

		$other['views'] = intval($other['views']);
		$other['replies']++;

		$db->query("UPDATE {$tablepre}posts SET tid='$tid' WHERE tid='$othertid'");
		$postsmerged = $db->affected_rows();

		$db->query("UPDATE {$tablepre}attachments SET tid='$tid' WHERE tid='$othertid'");
		$db->query("DELETE FROM {$tablepre}threads WHERE tid='$othertid'");
		$db->query("DELETE FROM {$tablepre}threadsmod WHERE tid='$othertid'");
		$db->query("DELETE FROM {$tablepre}mythreads WHERE tid='$othertid'");
		$db->query("DELETE FROM {$tablepre}myposts WHERE tid='$othertid'");

		$firstpost = $db->fetch_first("SELECT pid, fid, authorid, author, subject, dateline FROM {$tablepre}posts WHERE tid='$tid' AND invisible='0' ORDER BY dateline LIMIT 1");
		$db->query("UPDATE {$tablepre}posts SET first=(pid='$firstpost[pid]'), fid='$firstpost[fid]' WHERE tid='$tid'");
		$db->query("UPDATE {$tablepre}threads SET authorid='$firstpost[authorid]', author='".addslashes($firstpost['author'])."', subject='".addslashes($firstpost['subject'])."', dateline='$firstpost[dateline]', views=views+$other[views], replies=replies+$other[replies], moderated='1' WHERE tid='$tid'");

		if($fid == $other['fid']) {
			$db->query("UPDATE {$tablepre}forums SET threads=threads-1 WHERE fid='$fid'");
		} else {
			$db->query("UPDATE {$tablepre}forums SET threads=threads-1, posts=posts-$postsmerged WHERE fid='$other[fid]'");
			$db->query("UPDATE {$tablepre}forums SET posts=$posts+$postsmerged WHERE fid='$fid'");
		}

		$forum['threadcaches'] && deletethreadcaches($thread['tid']);

		$modpostsnum ++;
		$resultarray = array(
		'redirect'	=> "forumdisplay.php?fid=$fid",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_merge') : array()),
		'modtids'	=> $thread['tid'],
		'modlog'	=> array($thread, $other)
		);

	}

} elseif($action == 'copy' && $thread) {

	if(!submitcheck('copysubmit')) {
		require_once DISCUZ_ROOT.'./include/forum.func.php';
		$forumselect = forumselect();
		include template('topicadmin_copy');

	} else {

		$modaction = 'CPY';
		checkreasonpm();

		$query = $db->query("SELECT fid, name, modnewposts FROM {$tablepre}forums WHERE fid='$copyto' AND status>0 AND type<>'group'");
		if(!$toforum = $db->fetch_array($query)) {
			showmessage('admin_copy_invalid');
		} else {
			$modnewthreads = (!$allowdirectpost || $allowdirectpost == 1) && $toforum['modnewposts'] ? 1 : 0;
			$modnewreplies = (!$allowdirectpost || $allowdirectpost == 2) && $toforum['modnewposts'] ? 1 : 0;
			if($modnewthreads || $modnewreplies) {
				showmessage('admin_copy_hava_mod');
			}
		}


		$thread['tid'] = '';
		$thread['fid'] = $copyto;
		$thread['dateline'] = $thread['lastpost'] = $timestamp;
		$thread['lastposter'] = $thread['author'];
		$thread['views'] = $thread['replies'] = 0;
		$thread['digest'] = $thread['blog'] = $thread['rate'] = 0;
		$thread['displayorder'] = 0;
		$thread['attachment'] = 0;

		$db->query("INSERT INTO {$tablepre}threads VALUES ('".implode("', '", daddslashes($thread, 1))."')");
		$threadid = $db->insert_id();

		if($post = $db->fetch_first("SELECT * FROM {$tablepre}posts WHERE tid='$tid' AND first=1 LIMIT 1")) {
			$post['pid'] = '';
			$post['tid'] = $threadid;
			$post['fid'] = $copyto;
			$post['dateline'] = $timestamp;
			$post['attachment'] = 0;
			$post['invisible'] = $post['rate'] = $post['ratetimes'] = 0;
			$db->query("INSERT INTO {$tablepre}posts VALUES  ('".implode("', '", daddslashes($post, 1))."')");
		}

		updatepostcredits('+', $post['authorid'], '');

		updateforumcount($copyto);
		updateforumcount($fid);

		$modpostsnum ++;
		$resultarray = array(
		'redirect'	=> "forumdisplay.php?fid=$fid",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_copy') : array()),
		'modtids'	=> $thread['tid'],
		'modlog'	=> array($thread, $other)
		);
	}

} elseif($action == 'removereward') {

	$modaction = 'RMR';

	if(!is_array($thread) || $thread['special'] != '3' || $thread['price'] >= 0) {
		showmessage('reward_end');
	}

	$answererid = $db->result_first("SELECT answererid FROM {$tablepre}rewardlog WHERE tid='$thread[tid]'");

	$thread[price] = abs($thread[price]);

	$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+$thread[price] WHERE uid='$thread[authorid]'", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-$thread[price] WHERE uid='$answererid'", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}threads SET special='0', price='0' WHERE tid='$thread[tid]'", 'UNBUFFERED');
	$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid='$thread[tid]'", 'UNBUFFERED');


	showmessage('admin_succeed', "viewthread.php?tid=$tid");

} elseif($action == 'banpost') {

	if(!($banpids = implodeids($topiclist))) {
		showmessage('admin_banpost_invalid');
	} elseif(!$allowbanpost || !$tid) {
		showmessage('admin_nopermission', NULL, 'HALTED');
	}

	$posts = array();
	$query = $db->query("SELECT first, authorid FROM {$tablepre}posts WHERE pid IN ($banpids) AND tid='$tid'");
	while($post = $db->fetch_array($query)) {
		if($post['first'] && $thread['digest'] == '-1') {
			showmessage('special_noaction');
		}
		$posts[] = $post;
	}

	if(!submitcheck('banpostsubmit')) {

		$banid = '';
		foreach($topiclist as $id) {
			$banid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
		}

		include template('topicadmin_banpost');

	} else {

		$banned = intval($banned);
		$modaction = $banned ? 'BNP' : 'UBN';

		checkreasonpm();

		$db->query("UPDATE {$tablepre}posts SET status='$banned' WHERE pid IN ($banpids) AND tid='$tid'", 'UNBUFFERED');

		$resultarray = array(
		'redirect'	=> "viewthread.php?tid=$tid&amp;page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_ban_post') : array()),
		'modtids'	=> 0,
		'modlog'	=> $thread
		);

	}

} else {

	showmessage('undefined_action', NULL, 'HALTED');

}

if($resultarray) {

	if($resultarray['modtids']) {
		updatemodlog($resultarray['modtids'], $modaction, $resultarray['expiration']);
	}

	updatemodworks($modaction, $modpostsnum);
	if(is_array($resultarray['modlog'])) {
		if(isset($resultarray['modlog']['tid'])) {
			modlog($resultarray['modlog'], $modaction);
		} else {
			foreach($resultarray['modlog'] as $thread) {
				modlog($thread, $modaction);
			}
		}
	}

	if($resultarray['reasonpm']) {
		include language('modactions');
		$modaction = $modactioncode[$modaction];
		foreach($resultarray['reasonpm']['data'] as ${$resultarray['reasonpm']['var']}) {
			sendreasonpm($resultarray['reasonpm']['var'], $resultarray['reasonpm']['item']);
		}
	}

	showmessage((isset($resultarray['message']) ? $resultarray['message'] : 'admin_succeed'), $resultarray['redirect']);

}

?>