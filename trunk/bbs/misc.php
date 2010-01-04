<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: misc.php 11294 2007-11-19 03:44:00Z heyond $
*/

define('NOROBOT', TRUE);
require_once './include/common.inc.php';

if($action == 'maxpages') {

	$pages = intval($pages);
	if(empty($pages)) {
		showmessage('undefined_action', NULL, 'HALTED');
	} else {
		showmessage('max_pages');
	}

} elseif($action == 'customtopics') {

	if(!submitcheck('keywordsubmit', 1)) {

		if($_DCOOKIE['customkw']) {
			$customkwlist = array();
			foreach(@explode("\t", trim($_DCOOKIE['customkw'])) as $key => $keyword) {
				$keyword = dhtmlspecialchars(trim(stripslashes($keyword)));
				$customkwlist[$key]['keyword'] = $keyword;
				$customkwlist[$key]['url'] = '<a href="topic.php?keyword='.rawurlencode($keyword).'" target="_blank">'.$keyword.'</a> ';
			}
		}

		include template('customtopics');

	} else {

		if(!empty($delete) && is_array($delete)) {
			$keywords = implode("\t", array_diff(explode("\t", $_DCOOKIE['customkw']), $delete));
		} else {
			$keywords = $_DCOOKIE['customkw'];
		}

		if($newkeyword = cutstr(dhtmlspecialchars(preg_replace("/[\s\|\t\,\'\<\>]/", '', $newkeyword)), 20)) {
			if($_DCOOKIE['customkw']) {
				if(!preg_match("/(^|\t)".preg_quote($newkeyword, '/')."($|\t)/i", $keywords)) {
					if(count(explode("\t", $keywords)) >= $qihoo['maxtopics']) {
						$keywords = substr($keywords, (strpos($keywords, "\t") + 1))."\t".$newkeyword;
					} else {
						$keywords .= "\t".$newkeyword;
					}
				}
			} else {
				$keywords = $newkeyword;
			}
		}

		dsetcookie('customkw', stripslashes($keywords), 315360000);
		dheader("Location: {$boardurl}misc.php?action=customtopics");

	}

} elseif($action == 'attachpay') {

	$aid = intval($aid);
	if(!$aid) {
		showmessage('undefined_action', NULL, 'HALTED');
	} elseif(!isset($extcredits[$creditstrans])) {
		showmessage('credits_transaction_disabled');
	} elseif(!$discuz_uid) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} else {
		$attach = $db->fetch_first("SELECT a.tid, a.uid, a.price, a.filename, a.description, m.username AS author FROM {$tablepre}attachments a LEFT JOIN {$tablepre}members m ON a.uid=m.uid WHERE a.aid='$aid'");
		if($attach['price'] <= 0) {
			showmessage('undefined_action', NULL, 'HALTED');
		}
	}

	if(($balance = ${'extcredits'.$creditstrans} - $attach['price']) < ($minbalance = 0)) {
		showmessage('credits_balance_insufficient');
	}

	if($db->result_first("SELECT COUNT(*) FROM {$tablepre}attachpaymentlog WHERE aid='$aid' AND uid='$discuz_uid'")) {
		showmessage('attachment_yetpay', "attachment.php?aid=$aid");
	}

	$discuz_action = 81;

	$attach['netprice'] = floor($attach['price'] * (1 - $creditstax));

	if(!submitcheck('paysubmit')) {
		include template('attachpay');
	} else {
		$updateauthor = 1;
		if($maxincperthread > 0) {
			if(($db->result_first("SELECT SUM(netamount) FROM {$tablepre}attachpaymentlog WHERE aid='$aid'")) > $maxincperthread) {
				$updateauthor = 0;
			}
		}
		if($updateauthor) {
			$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+$attach[netprice] WHERE uid='$attach[uid]'");
		}
		$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-$attach[price] WHERE uid='$discuz_uid'");
		$db->query("INSERT INTO {$tablepre}attachpaymentlog (uid, aid, authorid, dateline, amount, netamount)
			VALUES ('$discuz_uid', '$aid', '$attach[uid]', '$timestamp', '$attach[price]', '$attach[netprice]')");

		showmessage('attachment_buy', "attachment.php?aid=$aid");
	}

} elseif($action == 'viewattachpayments') {

	$discuz_action = 82;

	$loglist = array();
	$query = $db->query("SELECT a.*, m.username FROM {$tablepre}attachpaymentlog a
		LEFT JOIN {$tablepre}members m USING (uid)
		WHERE aid='$aid' ORDER BY dateline");
	while($log = $db->fetch_array($query)) {
		$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
		$loglist[] = $log;
	}

	include template('attachpay_view');

} elseif($action == 'getonlines') {

	$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}sessions", 0);
	showmessage($num);

} elseif($action == 'virtualforum') {

	$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
	if(!$fid || !isset($insenz['virtualforums'][$fid])) {
		showmessage('forum_nonexistence', NULL, 'HALTED');
	}
	$forumheight = $forumurl = '';
	if($checksum && $redirect && $checksum == md5($insenz['authkey'].$redirect)) {
		$redirect = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quote;'), $redirect);
		$forumurl = $redirect;
	} else {
		$forumname = $forum['name'];
		$forum = $insenz['virtualforums'][$fid];
		$forum['link'] = urldecode($forum['link']);
		$forumurl = $forum['link']."s_sid=$insenz[siteid]&s_name=".urlencode($sitename)."&s_uname=$discuz_uid&s_url=".urlencode($boardurl)."&s_charset=$charset&s_styleid=$styleid";
		$forumurl .= "&s_checksum=".md5($forumurl.$insenz['authcode']);
		$forumheight = $forum['height'];
	}
	include template('virtualforum');
	exit;

} else {

	if(empty($forum['allowview'])) {
		if(!$forum['viewperm'] && !$readaccess) {
			showmessage('group_nopermission', NULL, 'NOPERM');
		} elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
			showmessage('forum_nopermission', NULL, 'NOPERM');
		}
	} elseif($thread['readperm'] && $thread['readperm'] > $readaccess && !$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
		showmessage('thread_nopermission', NULL, 'NOPERM');
	}

	$thread = $db->fetch_first("SELECT * FROM {$tablepre}threads WHERE tid='$tid' AND displayorder>='0'");
	if(!$thread) {
		showmessage('thread_nonexistence');
	}

	if($forum['type'] == 'forum') {
		$navigation = "&raquo; <a href=\"forumdisplay.php?fid=$fid\">$forum[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$thread[subject]</a> ";
		$navtitle = strip_tags($forum['name']).' - '.$thread['subject'];
	} elseif($forum['type'] == 'sub') {
		$fup = $db->fetch_first("SELECT name, fid FROM {$tablepre}forums WHERE fid='$forum[fup]'");
		$navigation = "&raquo; <a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">$forum[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$thread[subject]</a> ";
		$navtitle = strip_tags($fup['name']).' - '.strip_tags($forum['name']).' - '.$thread['subject'];
	}

}

if($action == 'votepoll' && submitcheck('pollsubmit')) {

        if($supe['status'] && $forum['status'] == 2 && $thread['sgid']) {
                require_once DISCUZ_ROOT.'./include/supesite.func.php';
                $allowvote = supe_circlepermission($thread['sgid']);
        }

	if(!$allowvote) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif(!empty($thread['closed'])) {
		showmessage('thread_poll_closed');
	} elseif(empty($pollanswers)) {
		showmessage('thread_poll_invalid');
	}

	$pollarray = $db->fetch_first("SELECT maxchoices, expiration FROM {$tablepre}polls WHERE tid='$tid'");
	if(!$pollarray) {
		showmessage('undefined_action', NULL, 'HALTED');
	} elseif($pollarray['expiration'] && $pollarray['expiration'] < $timestamp) {
		showmessage('poll_overdue');
	} elseif($pollarray['maxchoices'] && $pollarray['maxchoices'] < count($pollanswers)) {
		showmessage('poll_choose_most');
	}

	$voterids = $discuz_uid ? $discuz_uid : $onlineip;

	$polloptionid = array();
	$query = $db->query("SELECT polloptionid, voterids FROM {$tablepre}polloptions WHERE tid='$tid'");
	while($pollarray = $db->fetch_array($query)) {
		if(strexists("\t".$pollarray['voterids']."\t", "\t".$voterids."\t")) {
			showmessage('thread_poll_voted');
		}
		$polloptionid[] = $pollarray['polloptionid'];
	}

	$polloptionids = '';
	foreach($pollanswers as $key => $id) {
		if(!in_array($id, $polloptionid)) {
			showmessage('undefined_action', NULL, 'HALTED');
		}
		unset($polloptionid[$key]);
		$polloptionids[] = $id;
	}

	$pollanswers = implode('\',\'', $polloptionids);

	$db->query("UPDATE {$tablepre}polloptions SET votes=votes+1, voterids=CONCAT(voterids,'$voterids\t') WHERE polloptionid IN ('$pollanswers')", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}threads SET lastpost='$timestamp' WHERE tid='$tid'", 'UNBUFFERED');
	$db->query("REPLACE INTO {$tablepre}myposts (uid, tid, pid, position, dateline, special) VALUES ('$discuz_uid', '$tid', '', '', '$timestamp', '1')", 'UNBUFFERED');

	updatecredits($discuz_uid, $creditspolicy['votepoll']);

	showmessage('thread_poll_succeed', "viewthread.php?tid=$tid");

} elseif($action == 'viewvote') {

	if(empty($forum['allowview'])) {
		if(!$forum['viewperm'] && !$readaccess) {
			showmessage('group_nopermission', NULL, 'NOPERM');
		} elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
			showmessage('forum_nopermission', NULL, 'NOPERM');
		}
	}

	if($thread['readperm'] && $thread['readperm'] > $readaccess && !$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
		showmessage('thread_nopermission', NULL, 'NOPERM');
	}

	if($forum['password'] && $forum['password'] != $_DCOOKIE['fidpw'.$fid]) {
		dheader("Location: {$boardurl}forumdisplay.php?fid=$fid&amp;sid=$sid");
	}

	$voterpp = 180;
	$page = $_GET['page'];
	if(empty($polloptionid)) {
		$voterids = '';
		$query = $db->query("SELECT voterids FROM {$tablepre}polloptions WHERE tid='$tid'");
		while($polloption = $db->fetch_array($query)) {
			$voterids .= ($voterids ? "\t" : '').trim($polloption['voterids']);
		}
		unset($polloption);
		$arrvoterids = explode("\t", trim($voterids));
		$num = count($arrvoterids);

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $voterpp;
		$multipage = multi($num, $voterpp, $page, "misc.php?action=viewvote&tid=$tid");

	} elseif($adminid == 1) {
		$voterids = '';
		$voterids = $db->result_first("SELECT voterids FROM {$tablepre}polloptions WHERE polloptionid='$polloptionid'");
		$arrvoterids = explode("\t", trim($voterids));
		$num = count($arrvoterids);

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $voterpp;
		$multipage = multi($num, $voterpp, $page, "misc.php?action=viewvote&tid=$tid&polloptionid=$polloptionid");
	}
	$arrvoterids = @array_slice($arrvoterids, $start_limit, $voterpp);
	$voterids = @implode("','", $arrvoterids);

	$voterlist = $voter = array();
	$query = $db->query("SELECT uid, username FROM {$tablepre}members WHERE uid IN ('$voterids')");
	while($voter = $db->fetch_array($query)) {
		$voterlist[] = $voter;
	}
	include template('viewthread_poll_voters');

} elseif($action == 'emailfriend') {

	if(!$discuz_uid) {
		showmessage('not_loggedin', NULL, 'NOPERM');
	}

	$discuz_action = 122;

	if(!submitcheck('sendsubmit')) {

		$fromuid = $creditspolicy['promotion_visit'] ? '&amp;fromuid='.$discuz_uid : '';
		$threadurl = "{$boardurl}viewthread.php?tid=$tid$fromuid";

		$email = $db->result_first("SELECT email FROM {$tablepre}members WHERE uid='$discuz_uid'");

		include template('emailfriend');

	} else {

		if(empty($fromname) || empty($fromemail) || empty($sendtoname) || empty($sendtoemail)) {
			showmessage('email_friend_invalid', NULL, 'HALTED');
		}

		sendmail("$sendtoname <$sendtoemail>", 'email_to_friend_subject', 'email_to_friend_message', "$fromname <$fromemail>");

		showmessage('email_friend_succeed', "viewthread.php?tid=$tid", NULL, 'HALTED');

	}

} elseif($action == 'rate' && $pid) {

	if(!$raterange) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($modratelimit && $adminid == 3 && !$forum['ismoderator']) {
		showmessage('thread_rate_moderator_invalid', NULL, 'HALTED');
	}

	$reasonpmcheck = $reasonpm == 2 || $reasonpm == 3 ? 'checked="checked" disabled' : '';
	if(($reasonpm == 2 || $reasonpm == 3) || !empty($sendreasonpm)) {
		$forumname = strip_tags($forum['name']);
		$sendreasonpm = 1;
	} else {
		$sendreasonpm = 0;
	}

	foreach($raterange as $id => $rating) {
		$maxratetoday[$id] = $rating['mrpd'];
	}

	$query = $db->query("SELECT extcredits, SUM(ABS(score)) AS todayrate FROM {$tablepre}ratelog
		WHERE uid='$discuz_uid' AND dateline>=$timestamp-86400
		GROUP BY extcredits");
	while($rate = $db->fetch_array($query)) {
		$maxratetoday[$rate['extcredits']] = $raterange[$rate['extcredits']]['mrpd'] - $rate['todayrate'];
	}

	$post = $db->fetch_first("SELECT * FROM {$tablepre}posts WHERE pid='$pid' AND invisible='0' AND authorid<>'0'");
	if(!$post || $post['tid'] != $thread['tid'] || !$post['authorid']) {
		showmessage('undefined_action', NULL, 'HALTED');
	} elseif(!$forum['ismoderator'] && $karmaratelimit && $timestamp - $post['dateline'] > $karmaratelimit * 3600) {
		showmessage('thread_rate_timelimit', NULL, 'HALTED');
	} elseif($post['authorid'] == $discuz_uid || $post['tid'] != $tid) {
		showmessage('thread_rate_member_invalid', NULL, 'HALTED');
	} elseif($post['anonymous']) {
		showmessage('thread_rate_anonymous', NULL, 'HALTED');
	} elseif($post['status']) {
		showmessage('thread_rate_banned', NULL, 'HALTED');
	}

	$allowrate = TRUE;
	if(!$dupkarmarate) {
		$query = $db->query("SELECT pid FROM {$tablepre}ratelog WHERE uid='$discuz_uid' AND pid='$pid' LIMIT 1");
		if($db->num_rows($query)) {
			showmessage('thread_rate_duplicate', NULL, 'HALTED');
		}
	}

	$discuz_action = 71;

	$page = intval($page);

	require_once DISCUZ_ROOT.'./include/misc.func.php';

	if(!submitcheck('ratesubmit')) {

		$referer = $boardurl.'viewthread.php?tid='.$tid.'&page='.$page.'#pid'.$pid;

		$ratelist = array();
		foreach($raterange as $id => $rating) {
			if(isset($extcredits[$id])) {
				$ratelist[$id] = '';
				$offset = abs(ceil(($rating['max'] - $rating['min']) / 32));
				for($vote = $rating['min']; $vote <= $rating['max']; $vote += $offset) {
					$ratelist[$id] .= $vote ? '<option value="'.$vote.'">'.($vote > 0 ? '+'.$vote : $vote).'</option>' : '';
				}
			}
		}

		include template('rate');

	} else {

		checkreasonpm();

		$rate = $ratetimes = 0;
		$creditsarray = array();
		foreach($raterange as $id => $rating) {
			$score = intval(${'score'.$id});
			if(isset($extcredits[$id]) && !empty($score)) {
				if(abs($score) <= $maxratetoday[$id]) {
					if($score > $rating['max'] || $score < $rating['min']) {
						showmessage('thread_rate_range_invalid');
					} else {
						$creditsarray[$id] = $score;
						$rate += $score;
						$ratetimes += ceil(max(abs($rating['min']), abs($rating['max'])) / 5);
					}
				} else {
					showmessage('thread_rate_ctrl');
				}
			}
		}

		if(!$creditsarray) {
			showmessage('thread_rate_range_invalid', NULL, 'HALTED');
		}

		updatecredits($post['authorid'], $creditsarray);

		$db->query("UPDATE {$tablepre}posts SET rate=rate+($rate), ratetimes=ratetimes+$ratetimes WHERE pid='$pid'");
		if($post['first']) {
			$threadrate = intval(@($post['rate'] + $rate) / abs($post['rate'] + $rate));
			$db->query("UPDATE {$tablepre}threads SET rate='$threadrate' WHERE tid='$tid'");
		}

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
		$sqlvalues = $comma = '';
		$sqlreason = censor(trim($reason));
		$sqlreason = cutstr(dhtmlspecialchars($sqlreason), 40);
		foreach($creditsarray as $id => $addcredits) {
			$sqlvalues .= "$comma('$pid', '$discuz_uid', '$discuz_user', '$id', '$timestamp', '$addcredits', '$sqlreason')";
			$comma = ', ';
		}
		$db->query("INSERT INTO {$tablepre}ratelog (pid, uid, username, extcredits, dateline, score, reason)
			VALUES $sqlvalues", 'UNBUFFERED');

		include_once DISCUZ_ROOT.'./include/post.func.php';
		$forum['threadcaches'] && @deletethreadcaches($tid);

		$reason = dhtmlspecialchars(censor(trim($reason)));
		if($sendreasonpm) {
			$ratescore = $slash = '';
			foreach($creditsarray as $id => $addcredits) {
				$ratescore .= $slash.$extcredits[$id]['title'].' '.($addcredits > 0 ? '+'.$addcredits : $addcredits).' '.$extcredits[$id]['unit'];
				$slash = ' / ';
			}
			sendreasonpm('post', 'rate_reason');
		}

		$logs = array();
		foreach($creditsarray as $id => $addcredits) {
			$logs[] = dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$post[author]\t$id\t$addcredits\t$tid\t$thread[subject]\t$reason");
		}
		writelog('ratelog', $logs);

		if(!empty($inajax)) {
			$ratecomment = '';
			foreach($creditsarray as $id => $addcredits) {
				$ratecomment .= "<cite><a href=\"space.php?uid=$discuz_uid\" target=\"_blank\">$discuz_user</a></cite> ";
				$ratecomment .= $extcredits[$id]['title'];
				$addcredits = $addcredits > 0 ? "+$addcredits" : $addcredits;
				$ratecomment .= " <strong>$addcredits</strong>";
				$ratecomment .= " <em>$reason</em> ";
				$ratecomment .= gmdate("$dateformat  $timeformat", $timestamp + $timeoffset * 3600);
			}
			include language('messages');
			$thread_rate_log_lately = $language['thread_rate_log_lately'];
			$rate_view = $language['rate_view'];
			$message = $language['thread_rate_succeed'];
			$message .= '<script type="text/javascript">';
			$message .= 'if($("post_rate_div_'.$pid.'")) { $("post_rate_div_'.$pid.'").innerHTML = \'<fieldset><legend><a href="misc.php?action=viewratings&amp;tid=$tid&amp;pid=$post[pid]" title="'.$rate_view.'">'.$thread_rate_log_lately.'</a></legend><ul id="post_rate_$post[pid]"><li>'.$ratecomment.'</li></ul></fieldset>\'; } else { $("post_rate_'.$pid.'").innerHTML = \'<li>'.$ratecomment.'</li>\'; }';
			$message .= '</script>';
			showmessage($message, dreferer());
		} else {
			showmessage('thread_rate_succeed', dreferer());
		}
	}
} elseif($action == 'removerate' && $pid) {

	if(!$forum['ismoderator'] || !$raterange) {
		showmessage('undefined_action');
	}

	$reasonpmcheck = $reasonpm == 2 || $reasonpm == 3 ? 'checked="checked" disabled' : '';
	if(($reasonpm == 2 || $reasonpm == 3) || !empty($sendreasonpm)) {
		$forumname = strip_tags($forum['name']);
		$sendreasonpm = 1;
	} else {
		$sendreasonpm = 0;
	}

	foreach($raterange as $id => $rating) {
		$maxratetoday[$id] = $rating['mrpd'];
	}

	$post = $db->fetch_first("SELECT * FROM {$tablepre}posts WHERE pid='$pid' AND invisible='0' AND authorid<>'0'");
	if(!$post || $post['tid'] != $thread['tid'] || !$post['authorid']) {
		showmessage('undefined_action');
	}

	$discuz_action = 71;

	require_once DISCUZ_ROOT.'./include/misc.func.php';

	if(!submitcheck('ratesubmit')) {

		$referer = $boardurl.'viewthread.php?tid='.$tid.'&page='.$page.'#pid'.$pid;
		$ratelogs = array();
		$query = $db->query("SELECT * FROM {$tablepre}ratelog WHERE pid='$pid' ORDER BY dateline");
		while($ratelog = $db->fetch_array($query)) {
			$ratelog['dbdateline'] = $ratelog['dateline'];
			$ratelog['dateline'] = gmdate("$dateformat $timeformat", $ratelog['dateline'] + $timeoffset * 3600);
			$ratelog['scoreview'] = $ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score'];
			$ratelogs[] = $ratelog;
		}

		include template('rate');

	} else {

		checkreasonpm();

		if(!empty($logidarray)) {

			if($sendreasonpm) {
				$ratescore = $slash = '';
			}

			$query = $db->query("SELECT * FROM {$tablepre}ratelog WHERE pid='$pid'");
			$rate = $ratetimes = 0;
			$logs = array();
			while($ratelog = $db->fetch_array($query)) {
				if(in_array($ratelog['uid'].' '.$ratelog['extcredits'].' '.$ratelog['dateline'], $logidarray)) {
					$rate += $ratelog['score'] = -$ratelog['score'];
					$ratetimes += ceil(max(abs($rating['min']), abs($rating['max'])) / 5);
					updatecredits($post['authorid'], array($ratelog['extcredits'] => $ratelog['score']));
					$db->query("DELETE FROM {$tablepre}ratelog WHERE pid='$pid' AND uid='$ratelog[uid]' AND extcredits='$ratelog[extcredits]' AND dateline='$ratelog[dateline]'", 'UNBUFFERED');
					$logs[] = dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$post[author]\t$ratelog[extcredits]\t$ratelog[score]\t$tid\t$thread[subject]\t$reason\tD");
					if($sendreasonpm) {
						$ratescore .= $slash.$extcredits[$ratelog['extcredits']]['title'].' '.($ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score']).' '.$extcredits[$ratelog['extcredits']]['unit'];
						$slash = ' / ';
					}
				}
			}
			writelog('ratelog', $logs);

			if($sendreasonpm) {
				sendreasonpm('post', 'rate_removereason');
			}

			$db->query("UPDATE {$tablepre}posts SET rate=rate+($rate), ratetimes=ratetimes-$ratetimes WHERE pid='$pid'");
			if($post['first']) {
				$threadrate = @intval(@($post['rate'] + $rate) / abs($post['rate'] + $rate));
				$db->query("UPDATE {$tablepre}threads SET rate='$threadrate' WHERE tid='$tid'");
			}

		}

		showmessage('thread_rate_removesucceed', dreferer());

	}

} elseif($action == 'viewratings' && $pid) {

	$queryr = $db->query("SELECT * FROM {$tablepre}ratelog WHERE pid='$pid' ORDER BY dateline");
	$queryp = $db->query("SELECT p.* ".($bannedmessages ? ", m.groupid " : '').
		" FROM {$tablepre}posts p ".
		($bannedmessages ? "LEFT JOIN {$tablepre}members m ON m.uid=p.authorid" : '').
		" WHERE p.pid='$pid' AND p.invisible='0'");

	if(!($db->num_rows($queryr)) || !($db->num_rows($queryp))) {
		showmessage('thread_rate_log_nonexistence');
	}

	$post = $db->fetch_array($queryp);
	if($post['tid'] != $thread['tid']) {
		showmessage('undefined_action', NULL, 'HALTED');
	}

	$discuz_action = 72;

	if(!$bannedmessages || !$post['authorid'] || ($bannedmessages && $post['authorid'] && !in_array(intval($author['groupid']), array(0, 4, 5)))) {
		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
		$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $forum['allowsmilies'], $forum['allowbbcode'], $forum['allowimgcode'], $forum['allowhtml'], $forum['jammer']);
	} else {
		$post['message'] = '';
	}

	$loglist = array();
	while($log = $db->fetch_array($queryr)) {
		$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
		$log['score'] = $log['score'] > 0 ? '+'.$log['score'] : $log['score'];
		$log['reason'] = dhtmlspecialchars($log['reason']);
		$loglist[] = $log;
	}

	include template('rate_view');

} elseif($action == 'pay') {

	if(!isset($extcredits[$creditstrans])) {
		showmessage('credits_transaction_disabled');
	} elseif($thread['price'] <= 0 || $thread['special'] <> 0) {
		showmessage('undefined_action', NULL, 'HALTED');
	} elseif(!$discuz_uid) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	}

	if(($balance = ${'extcredits'.$creditstrans} - $thread['price']) < ($minbalance = 0)) {
		showmessage('credits_balance_insufficient');
	}

	if($db->result_first("SELECT COUNT(*) FROM {$tablepre}paymentlog WHERE tid='$tid' AND uid='$discuz_uid'")) {
		showmessage('credits_buy_thread', 'viewthread.php?tid='.$tid);
	}

	$discuz_action = 81;

	$thread['netprice'] = floor($thread['price'] * (1 - $creditstax));

	if(!submitcheck('paysubmit')) {

		include template('pay');

	} else {

		$updateauthor = true;
		if($maxincperthread > 0) {
			if(($db->result_first("SELECT SUM(netamount) FROM {$tablepre}paymentlog WHERE tid='$tid'")) > $maxincperthread) {
				$updateauthor = false;
			}
		}

		if($updateauthor) {
			$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+$thread[netprice] WHERE uid='$thread[authorid]'");
		}

		$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-$thread[price] WHERE uid='$discuz_uid'");
		$db->query("INSERT INTO {$tablepre}paymentlog (uid, tid, authorid, dateline, amount, netamount)
			VALUES ('$discuz_uid', '$tid', '$thread[authorid]', '$timestamp', '$thread[price]', '$thread[netprice]')");

		showmessage('thread_pay_succeed', "viewthread.php?tid=$tid");

	}

} elseif($action == 'viewpayments') {

	$discuz_action = 82;

	$loglist = array();
	$query = $db->query("SELECT p.*, m.username FROM {$tablepre}paymentlog p
		LEFT JOIN {$tablepre}members m USING (uid)
		WHERE tid='$tid' ORDER BY dateline");
	while($log = $db->fetch_array($query)) {
		$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
		$loglist[] = $log;
	}

	include template('pay_view');

} elseif($action == 'report') {

	if(!$reportpost) {
		showmessage('thread_report_disabled');
	}

	if(!$discuz_uid) {
		showmessage('not_loggedin', NULL, 'HALTED');
	}

	if(!$thread || !is_numeric($pid)) {
		showmessage('undefined_action', NULL, 'HALTED');
	}

	$discuz_action = 123;

	$floodctrl = $floodctrl * 3;
	if($timestamp - $lastpost < $floodctrl) {
		showmessage('thread_report_flood_ctrl');
	}

	if(!submitcheck('reportsubmit')) {

		include template('reportpost');
		exit;

	} else {

		$posturl = "{$boardurl}viewthread.php?tid=$tid".($page || $pid ? "&amp;page=$page#pid$pid" : NULL);

		$uids = 0;
		$adminids = '';
		$reportto = array();

		if(is_array($to) && count($to)) {

			if(isset($to[3])) {
				$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE fid='$fid'");
				while($member = $db->fetch_array($query)) {
					$uids .= ','.$member['uid'];
				}
			}

			if(!$uids || ($reportpost >= 2 && $to[2])) {
				$adminids .= ',2';
			}

			if($reportpost == 3 && $to[1]) {
				$adminids .= ',1';
			}

			if($adminids) {
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE adminid IN (".substr($adminids, 1).")");
				if(!$db->num_rows($query)) {
					$query = $db->query("SELECT uid FROM {$tablepre}members WHERE adminid='1'");
				}
				while($member = $db->fetch_array($query)) {
					$uids .= ','.$member['uid'];
				}
			}

			$query = $db->query("SELECT uid, ignorepm FROM {$tablepre}memberfields WHERE uid IN ($uids)");
			while($member = $db->fetch_array($query)) {
				if(!preg_match("/(^{ALL}$|(,|^)\s*".preg_quote($discuz_user, '/')."\s*(,|$))/i", $member['ignorepm'])) {
					if(!in_array($member['uid'], $reportto)) {
						$reportto[] = $member['uid'];
					}
				}
			}

			if($reportto) {
				$reason = stripslashes($reason);
				sendpm(implode(',', $reportto), 'reportpost_subject', 'reportpost_message');
			}

			$db->query("UPDATE {$tablepre}members SET lastpost='$timestamp' WHERE uid='$discuz_uid'");

			showmessage('thread_report_succeed', "viewthread.php?tid=$tid");

		} else {

			showmessage('thread_report_invalid');

		}

	}

} elseif($action == 'blog') {

	if(!$discuz_uid || (!$thread['blog'] && (!$allowuseblog || !$forum['allowshare']))) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	}

	if($thread['authorid'] != $discuz_uid) {
		$thread['adminid'] = $db->result_first("SELECT adminid FROM {$tablepre}members WHERE uid='$thread[authorid]'");
		if(!$forum['ismoderator'] || (in_array($thread['adminid'], array(1, 2, 3)) && $adminid > $thread['adminid'])) {
			showmessage('blog_add_illegal');
		}
	}

	$blog = $thread['blog'] ? 0 : 1;
	$db->query("UPDATE {$tablepre}threads SET blog='$blog' WHERE tid='$tid'", 'UNBUFFERED');

	if($forum['ismoderator'] && $thread['authorid'] != $discuz_uid && $blog != $thread['blog']) {
		$reason = '';
		require_once DISCUZ_ROOT.'./include/misc.func.php';
		modlog($thread, ($thread['blog'] ? 'RBL' : 'ABL'));
	}

	showmessage('blog_add_succeed', "viewthread.php?tid=$tid");

} elseif($action == 'viewthreadmod' && $tid) {

	$loglist = array();
	$query = $db->query("SELECT * FROM {$tablepre}threadsmod WHERE tid='$tid' ORDER BY dateline DESC");
	while($log = $db->fetch_array($query)) {
		$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
		$log['expiration'] = !empty($log['expiration']) ? gmdate("$dateformat", $log['expiration'] + $timeoffset * 3600) : '';
		$log['status'] = empty($log['status']) ? 'style="text-decoration: line-through" disabled' : '';
		if($log['magicid']) {
			require_once DISCUZ_ROOT.'./forumdata/cache/cache_magics.php';
			$log['magicname'] = $_DCACHE['magics'][$log['magicid']]['name'];
		}
		$loglist[] = $log;
	}

	if(empty($loglist)) {
		showmessage('threadmod_nonexistence');
	} else {
		include_once language('modactions');
	}

	include template('viewthread_mod');

} elseif($action == 'bestanswer' && $tid && $pid && submitcheck('bestanswersubmit')) {

	$forward = 'viewthread.php?tid='.$tid;

	$post = $db->fetch_first("SELECT authorid, first FROM {$tablepre}posts WHERE pid='$pid' and tid='$tid'");

	if(!($thread['special'] == 3 && $post && ($forum['ismoderator'] || $thread['authorid'] == $discuz_uid) && $post['authorid'] != $thread['authorid'] && $post['first'] == 0 && $discuz_uid != $post['authorid'])) {
		showmessage('reward_cant_operate');
	} elseif($post['authorid'] == $thread['authorid']) {
		showmessage('reward_cant_self');
	} elseif($thread['price'] < 0) {
		showmessage('reward_repeat_selection');
	}
	$thread['netprice'] = ceil($price * ( 1 + $creditstax) );
	$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+$thread[price] WHERE uid='$post[authorid]'");
	$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid='$tid' and answererid='$post[authorid]'");
	$db->query("UPDATE {$tablepre}rewardlog SET answererid='$post[authorid]' WHERE tid='$tid' and authorid='$thread[authorid]'");
	$thread['price'] = '-'.$thread['price'];
	$db->query("UPDATE {$tablepre}threads SET price='$thread[price]' WHERE tid='$tid'");
	$db->query("UPDATE {$tablepre}posts SET dateline=$thread[dateline]+1 WHERE pid='$pid'");

	$thread['dateline'] = gmdate("$dateformat $timeformat", $thread['dateline'] + $timeoffset * 3600);
	if($discuz_uid != $thread['authorid']) {
		sendpm($thread['authorid'], 'reward_question_subject', 'reward_question_message', $discuz_uid, $discuz_user);
	}
	sendpm($post['authorid'], 'reward_bestanswer_subject', 'reward_bestanswer_message', $discuz_uid, $discuz_user);

	showmessage('reward_completion', $forward);

} elseif($action == 'activityapplies') {

	$allowjoinactivity = 1;
        if($supe['status'] && $forum['status'] == 2 && $thread['sgid']) {
                require_once DISCUZ_ROOT.'./include/supesite.func.php';
                $allowjoinactivity = supe_circlepermission($thread['sgid']);
        }

	if(!$discuz_uid || !$allowjoinactivity) {
		showmessage('undefined_action', NULL, 'HALTED');
	}

	if(submitcheck('activitysubmit')) {
		$expiration = $db->result_first("SELECT expiration FROM {$tablepre}activities WHERE tid='$tid'");
		if($expiration && $expiration < $timestamp - date('Z')) {
			showmessage('activity_stop');
		}

		$query = $db->query("SELECT applyid FROM {$tablepre}activityapplies WHERE tid='$tid' and username='$discuz_user'");
		if($db->num_rows($query)) {
			showmessage('activity_repeat_apply', "viewthread.php?tid=$tid&amp;extra=$extra");
		}
		$payvalue = intval($payvalue);
		$payment = $payment ? $payvalue : -1;
		$message = cutstr(dhtmlspecialchars($message), 200);
		$contact = cutstr(dhtmlspecialchars($contact), 200);

		$db->query("INSERT INTO {$tablepre}activityapplies (tid, username, uid, message, verified, dateline, payment, contact)
			VALUES ('$tid', '$discuz_user', '$discuz_uid', '$message', '0', '$timestamp', '$payment', '$contact')");

		showmessage('activity_completion', "viewthread.php?tid=$tid&amp;extra=$extra");
	}

} elseif($action == 'activityapplylist') {

	$activity = $db->fetch_first("SELECT * FROM {$tablepre}activities WHERE tid='$tid'");
	if(!$activity || $thread['special'] != 4) {
		showmessage('undefined_action');
	}

	if(!submitcheck('applylistsubmit')) {
		$sqlverified = $thread['authorid'] == $discuz_uid ? '' : 'AND verified=1';

		$applylist = array();
		$query = $db->query("SELECT applyid, username, uid, message, verified, dateline, payment, contact FROM {$tablepre}activityapplies WHERE tid='$tid' $sqlverified ORDER BY dateline DESC");
		while($activityapplies = $db->fetch_array($query)) {
			$activityapplies['dateline'] = gmdate("$dateformat $timeformat", $activityapplies['dateline'] + $timeoffset * 3600);
			$applylist[] = $activityapplies;
		}

		$activity['starttimefrom'] = gmdate("$dateformat $timeformat", $activity['starttimefrom'] + $timeoffset * 3600);
		$activity['starttimeto'] = $activity['starttimeto'] ? gmdate("$dateformat $timeformat", $activity['starttimeto'] + $timeoffset * 3600) : 0;
		$activity['expiration'] = $activity['expiration'] ? gmdate("$dateformat $timeformat", $activity['expiration'] + $timeoffset * 3600) : 0;

		$applynumbers = $db->result_first("SELECT COUNT(*) FROM {$tablepre}activityapplies WHERE tid='$tid' AND verified=1");

		include template('activity_applylist');
	} else {
		if(empty($applyidarray)) {
			showmessage('activity_choice_applicant', "viewthread.php?tid=$tid&do=viewapplylist");
		} else {
			$uidarray = array();
			$ids = implode('\',\'', $applyidarray);
			$query=$db->query("SELECT a.uid FROM {$tablepre}activityapplies a RIGHT JOIN {$tablepre}members m USING(uid) WHERE a.applyid IN ('$ids')");
			while($uid = $db->fetch_array($query)) {
				$uidarray[] = $uid['uid'];
			}
			$activity_subject = $thread['subject'];
			if($operation == 'delete') {
				$db->query("DELETE FROM {$tablepre}activityapplies WHERE applyid IN ('$ids')", 'UNBUFFERED');

				sendpm(implode(',', $uidarray), 'activity_delete_subject', 'activity_delete_message', $fromid = '0', $from = 'System Message');
				showmessage('activity_delete_completion', "viewthread.php?tid=$tid&do=viewapplylist");
			} else {
				$db->query("UPDATE {$tablepre}activityapplies SET verified=1 WHERE applyid IN ('$ids')", 'UNBUFFERED');

				sendpm(implode(',', $uidarray), 'activity_apply_subject', 'activity_apply_message', $fromid = '0', $from = 'System Message');
				showmessage('activity_auditing_completion', "viewthread.php?tid=$tid&do=viewapplylist");
			}
		}
	}

} elseif($action == 'tradeorder') {

	$trades = array();
	$query=$db->query("SELECT * FROM {$tablepre}trades WHERE tid='$tid' ORDER BY displayorder");

	if($thread['authorid'] != $discuz_uid) {
		showmessage('undefined_action', NULL, 'HALTED');
	}

	if(!submitcheck('tradesubmit')) {

		$stickcount = 0;$trades = $tradesstick = array();
		while($trade = $db->fetch_array($query)) {
			$stickcount = $trade['displayorder'] > 0 ? $stickcount + 1 : $stickcount;
			$trade['displayorderview'] = $trade['displayorder'] < 0 ? 128 + $trade['displayorder'] : $trade['displayorder'];
			if($trade['expiration']) {
				$trade['expiration'] = ($trade['expiration'] - $timestamp) / 86400;
				if($trade['expiration'] > 0) {
					$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
					$trade['expiration'] = floor($trade['expiration']);
				} else {
					$trade['expiration'] = -1;
				}
			}
			if($trade['displayorder'] < 0) {
				$trades[] = $trade;
			} else {
				$tradesstick[] = $trade;
			}
		}
		$trades = array_merge($tradesstick, $trades);
		include template('trade_displayorder');

	} else {

		$count = 0;
		while($trade = $db->fetch_array($query)) {
			$displayordernew = abs(intval($displayorder[$trade['pid']]));
			$displayordernew = $displayordernew > 128 ? 0 : $displayordernew;
			if($stick[$trade['pid']]) {
				$count++;
				$displayordernew = $displayordernew == 0 ? 1 : $displayordernew;
			}
			if(!$stick[$trade['pid']] || $displayordernew > 0 && $tradestick < $count) {
				$displayordernew = -1 * (128 - $displayordernew);
			}
			$db->query("UPDATE {$tablepre}trades SET displayorder='".$displayordernew."' WHERE tid='$tid' AND pid='$trade[pid]'");
		}

		showmessage('trade_displayorder_updated', "viewthread.php?tid=$tid");

	}

} elseif($action == 'debatevote') {

	if(!empty($thread['closed'])) {
		showmessage('thread_poll_closed');
	}

	if(!$discuz_uid) {
		showmessage('debate_poll_nopermission');
	}

	$isfirst = empty($pid) ? TRUE : FALSE;

	$debate = $db->fetch_first("SELECT uid, endtime, affirmvoterids, negavoterids FROM {$tablepre}debates WHERE tid='$tid'");

	if(empty($debate)) {
		showmessage('debate_nofound');
	}

	if($isfirst) {
		$stand = intval($stand);

		if($stand == 1 || $stand == 2) {
			if(strpos($debate['affirmvoterids'], "$discuz_uid\t") !== FALSE || strpos($debate['negavoterids'], "$discuz_uid\t") !== FALSE) {
				showmessage('debate_poll_voted');
			}
		}
		if($stand == 1) {
			$db->query("UPDATE {$tablepre}debates SET affirmvotes=affirmvotes+1 WHERE tid='$tid'");
			$db->query("UPDATE {$tablepre}debates SET affirmvoterids=CONCAT(affirmvoterids, '$discuz_uid\t') WHERE tid='$tid'");
		} elseif($stand == 2) {
			$db->query("UPDATE {$tablepre}debates SET negavotes=negavotes+1 WHERE tid='$tid'");
			$db->query("UPDATE {$tablepre}debates SET negavoterids=CONCAT(negavoterids, '$discuz_uid\t') WHERE tid='$tid'");
		}
		showmessage('debate_poll_thread_succeed');
	}

	$debatepost = $db->fetch_first("SELECT stand, voterids, uid FROM {$tablepre}debateposts WHERE pid='$pid' AND tid='$tid'");
	if(empty($debatepost)) {
		showmessage('debate_nofound');
	}
	$debate = array_merge($debate, $debatepost);
	unset($debatepost);

	if($debate['uid'] == $discuz_uid) {
		showmessage('debate_poll_myself', "viewthread.php?tid=$tid");
	} elseif(strpos($debate['voterids'], "$discuz_uid\t") !== FALSE) {
		showmessage('debate_poll_voted', "viewthread.php?tid=$tid");
	}elseif($debate['endtime'] && $debate['endtime'] < $timestamp) {
		showmessage('debate_poll_end', "viewthread.php?tid=$tid");
	}


	/*
	if($isfirst) {
		$sqladd = $debate['stand'] == 1 ? 'affirmvotes=affirmvotes+1' : ($debate['stand'] == 2 ? 'negavotes=negavotes+1' : '');
		if($sqladd) {
			$db->query("UPDATE {$tablepre}debates SET $sqladd WHERE tid='$tid'");
		}
		unset($sqladd);
	}
	*/

	$db->query("UPDATE {$tablepre}debateposts SET voters=voters+1, voterids=CONCAT(voterids, '$discuz_uid\t') WHERE pid='$pid'");

	showmessage('debate_poll_post_succeed', "viewthread.php?tid=$tid");

}elseif($action == 'debateumpire') {

	$debate = $db->fetch_first("SELECT * FROM {$tablepre}debates WHERE tid='$tid'");

	if(empty($debate)) {
		showmessage('debate_nofound');
	}elseif(!empty($thread['closed']) && $timestamp - $debate['endtime'] > 3600) {
		showmessage('debate_umpire_edit_invalid');
	} elseif($discuz_user != $debate['umpire']) {
		showmessage('debate_umpire_nopermission');
	}

	$debate = array_merge($debate, $thread);


	if(!submitcheck('umpiresubmit')) {
		$query = $db->query("SELECT SUM(dp.voters) as voters, dp.stand, m.uid, m.username FROM {$tablepre}debateposts dp
			LEFT JOIN {$tablepre}members m ON m.uid=dp.uid
			WHERE dp.tid='$tid' AND dp.stand<>0
			GROUP BY m.uid
			ORDER BY voters DESC
			LIMIT 30");
		$candidate = $candidates = array();
		while($candidate = $db->fetch_array($query)) {
			$candidate['username'] = dhtmlspecialchars($candidate['username']);
			$candidates[$candidate['username']] = $candidate;
		}
		$winnerchecked = array($debate['winner'] => ' checked="checked"');

		list($debate['bestdebater']) = preg_split("/\s/", $debate['bestdebater']);

		include template('debate_umpire');
	} else {
		if(empty($bestdebater)) {
			showmessage('debate_umpire_nofound_bestdebater');
		} elseif(empty($winner)) {
			showmessage('debate_umpire_nofound_winner');
		} elseif(empty($umpirepoint)) {
			showmessage('debate_umpire_nofound_point');
		}
		$bestdebateruid = $db->result_first("SELECT uid FROM {$tablepre}members WHERE username='$bestdebater' LIMIT 1");
		if(!$bestdebateruid) {
			showmessage('debate_umpire_bestdebater_invalid');
		}
		if(!$bestdebaterstand = $db->result_first("SELECT stand FROM {$tablepre}debateposts WHERE tid='$tid' AND uid='$bestdebateruid' AND stand>'0' AND uid<>'$debate[uid]' AND uid<>'$discuz_uid' LIMIT 1")) {
			showmessage('debate_umpire_bestdebater_invalid');
		}
		$arr = $db->fetch_first("SELECT SUM(voters) as voters, COUNT(*) as replies FROM {$tablepre}debateposts WHERE tid='$tid' AND uid='$bestdebateruid'");
		$bestdebatervoters = $arr['voters'];
		$bestdebaterreplies = $arr['replies'];

		$umpirepoint = dhtmlspecialchars($umpirepoint);
		$bestdebater = dhtmlspecialchars($bestdebater);
		$winner = intval($winner);
		$db->query("UPDATE {$tablepre}threads SET closed='1' WHERE tid='$tid'");
		$db->query("UPDATE {$tablepre}debates SET umpirepoint='$umpirepoint', winner='$winner', bestdebater='$bestdebater\t$bestdebateruid\t$bestdebaterstand\t$bestdebatervoters\t$bestdebaterreplies', endtime='$timestamp' WHERE tid='$tid'");
		showmessage('debate_umpire_comment_succeed', 'viewthread.php?tid='.$tid);
	}
}

?>