<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: newtrade.inc.php 10115 2007-08-24 00:58:08Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$discuz_action = 11;

if(empty($forum['fid']) || $forum['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if($special != 2 || !submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {
	showmessage('undefined_action', NULL, 'HALTED');
}

if(!$allowposttrade) {
	showmessage('group_nopermission', NULL, 'NOPERM');
}

$sgid = intval($sgid);
if($iscircle) {
        $mycircles = array();
        if($discuz_uid) {
        	supe_dbconnect();
                $query = $supe['db']->query("SELECT gid, groupname FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND flag>0", 'SILENT');
                while($mycircle = $supe['db']->fetch_array($query)) {
                        $mycircles[$mycircle['gid']] = cutstr($mycircle['groupname'], 30);
                }
        }
        if($sgid) {
		supe_dbconnect();
	        $query = $supe['db']->query("SELECT g.groupname, gf.headerimage, gf.css FROM {$supe[tablepre]}groups g, {$supe[tablepre]}groupfields gf WHERE g.gid='$sgid' AND g.flag=1 AND g.gid=gf.gid", 'SILENT');
	        $circle = $supe['db']->fetch_array($query);
	        if(!$discuz_uid || !$supe['db']->result($supe['db']->query("SELECT COUNT(*) FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND gid='$sgid' AND flag>0", 'SILENT'), 0)) {
	        	showmessage('circle_nopermission');
	        }
        }
}

if(!$discuz_uid && !((!$forum['postperm'] && $allowpost) || ($forum['postperm'] && forumperm($forum['postperm'])))) {
	showmessage('group_nopermission', NULL, 'NOPERM');
} elseif(empty($forum['allowpost'])) {
	if(!$forum['postperm'] && !$allowpost) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($forum['postperm'] && !forumperm($forum['postperm'])) {
		showmessage('post_forum_newthread_nopermission', NULL, 'HALTED');
	}
}

$isblog = empty($isblog) ? '' : 'yes';
if($isblog && (!$allowuseblog || !$forum['allowshare'])) {
	showmessage('post_newthread_blog_invalid', NULL, 'HALT');
}

checklowerlimit($postcredits);


if($subject == '' || $message == '') {
	showmessage('post_sm_isnull');
}

if($post_invalid = checkpost()) {
	showmessage($post_invalid);
}

if(checkflood()) {
	showmessage('post_flood_ctrl');
}

$item_price = floatval($item_price);
if(!trim($item_name)) {
	showmessage('trade_please_name');
} elseif($maxtradeprice && ($mintradeprice > $item_price || $maxtradeprice < $item_price)) {
	showmessage('trade_price_between');
} elseif(!$maxtradeprice && $mintradeprice > $item_price) {
	showmessage('trade_price_more_than');
} elseif($item_number < 1) {
	showmessage('tread_please_number');
}

if(!empty($_FILES['tradeattach']['tmp_name'][0])) {
	$_FILES['attach'] = array_merge_recursive((array)$_FILES['attach'], $_FILES['tradeattach']);
}

if($allowpostattach && is_array($_FILES['attach'])) {
	foreach($_FILES['attach']['name'] as $attachname) {
		if($attachname != '') {
			checklowerlimit($postattachcredits);
			break;
		}
	}
}

$typeid = isset($typeid) ? $typeid : 0;
$tradetypeid = isset($tradetypeid) ? $tradetypeid : 0;
$iconid = !empty($iconid) && isset($_DCACHE['icons'][$iconid]) ? $iconid : 0;
$displayorder = $modnewthreads ? -2 : (($forum['ismoderator'] && !empty($sticktopic)) ? 1 : 0);
$digest = ($forum['ismoderator'] && !empty($addtodigest)) ? 1 : 0;
$blog = $allowuseblog && $forum['allowshare'] && !empty($addtoblog) ? 1 : 0;
$readperm = $allowsetreadperm ? $readperm : 0;
$isanonymous = $isanonymous && $allowanonymous ? 1 : 0;

$threadmessage = $counterdesc."\t\t\t".$aboutcounter;

$author = !$isanonymous ? $discuz_user : '';

$moderated = $digest || $displayorder > 0 ? 1 : 0;

$attachment = ($allowpostattach && $attachments = attach_upload()) ? 1 : 0;

$subscribed = !empty($emailnotify) && $discuz_uid ? 1 : 0;

$supe_pushstatus = $supe['status'] && $forum['supe_pushsetting']['status'] == 1 && !$modnewthreads ? '1' : '0';

$sgidadd1 = $sgidadd2 = '';
if($iscircle) {
	$sgidadd1 = ', sgid';
	$sgidadd2 = ", '$sgid'";
}
$db->query("INSERT INTO {$tablepre}threads (fid, readperm, price, iconid, typeid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, blog, special, attachment, subscribed, moderated, supe_pushstatus, replies $sgidadd1)
	VALUES ('$fid', '$readperm', '$price', '$iconid', '$typeid', '$author', '$discuz_uid', '$subject', '$timestamp', '$timestamp', '$author', '$displayorder', '$digest', '$blog', '$special', '$attachment', '$subscribed', '$moderated', '$supe_pushstatus', '1' $sgidadd2)");
$tid = $db->insert_id();

if($subscribed) {
	$db->query("REPLACE INTO {$tablepre}subscriptions (uid, tid, lastpost, lastnotify)
		VALUES ('$discuz_uid', '$tid', '$timestamp', '$timestamp')", 'UNBUFFERED');
}

$db->query("REPLACE INTO {$tablepre}mythreads (uid, tid, dateline, special) VALUES ('$discuz_uid', '$tid', '$timestamp', '$special')", 'UNBUFFERED');

if($moderated) {
	updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
	updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
}

$bbcodeoff = checkbbcodes($message, !empty($bbcodeoff));
$smileyoff = checksmilies($message, !empty($smileyoff));
$parseurloff = !empty($parseurloff);
$htmlon = bindec(($tagstatus && !empty($tagoff) ? 1 : 0).($allowhtml && !empty($htmlon) ? 1 : 0));

$pinvisible = $modnewthreads ? -2 : 0;
$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
	VALUES ('$fid', '$tid', '1', '$discuz_user', '$discuz_uid', '$subject', '$timestamp', '$threadmessage', '$onlineip', '$pinvisible', '$isanonymous', '$usesig', '$htmlon', '$bbcodeoff', '$smileyoff', '$parseurloff', '0')");

if($tagstatus && $tags != '') {
	$tagarray = array_unique(explode(' ', censor($tags)));
	$tagcount = 0;
	foreach($tagarray as $tagname) {
		$tagname = trim($tagname);
		if(preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $tagname)) {
			$query = $db->query("SELECT closed FROM {$tablepre}tags WHERE tagname='$tagname'");
			if($db->num_rows($query)) {
				if(!$tagstatus = $db->result($query, 0)) {
					$db->query("UPDATE {$tablepre}tags SET total=total+1 WHERE tagname='$tagname'", 'UNBUFFERED');
				}
			} else {
				$db->query("INSERT INTO {$tablepre}tags (tagname, closed, total)
					VALUES ('$tagname', 0, 1)", 'UNBUFFERED');
				$tagstatus = 0;
			}
			if(!$tagstatus) {
				$db->query("INSERT {$tablepre}threadtags (tagname, tid) VALUES ('$tagname', $tid)", 'UNBUFFERED');
			}
			$tagcount++;
			if($tagcount > 4) {
				unset($tagarray);
				break;
			}
		}
	}
}

$pinvisible = $modnewreplies ? -2 : 0;
$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
		VALUES ('$fid', '$tid', '0', '$discuz_user', '$discuz_uid', '$subject', '$timestamp', '$message', '$onlineip', '$pinvisible', '$isanonymous', '$usesig', '$htmlon', '$bbcodeoff', '$smileyoff', '$parseurloff', '$attachment')");
$pid = $db->insert_id();

threadtype_checkoption(1, 1);

$optiondata = array();
if($tradetypes && $typeoption && $checkoption) {
	$optiondata = threadtype_validator($typeoption);
}

if($tradetypes && $optiondata) {
	foreach($optiondata as $optionid => $value) {
		$db->query("INSERT INTO {$tablepre}tradeoptionvars (typeid, pid, optionid, value)
			VALUES ('$tradetypeid', '$pid', '$optionid', '$value')");
	}
}

$tradeaid = 0;
if($attachment) {
	$searcharray = $pregarray = $replacearray = array();
	foreach($attachments as $key => $attach) {
		$db->query("INSERT INTO {$tablepre}attachments (tid, pid, dateline, readperm, price, filename, description, filetype, filesize, attachment, downloads, isimage, uid, thumb, remote)
			VALUES ('$tid', '$pid', '$timestamp', '$attach[perm]', '$attach[price]', '$attach[name]', '$attach[description]', '$attach[type]', '$attach[size]', '$attach[attachment]', '0', '$attach[isimage]', '$attach[uid]', '$attach[thumb]', '$attach[remote]')");
		$searcharray[] = '[local]'.$localid[$key].'[/local]';
		$pregarray[] = '/\[localimg=(\d{1,3}),(\d{1,3})\]'.$localid[$key].'\[\/localimg\]/is';
		$insertid = $db->insert_id();
		$replacearray[] = '[attach]'.$db->insert_id().'[/attach]';
	}
	if(!empty($_FILES['tradeattach']['tmp_name'][0])) {
		$tradeaid = $insertid;
	}
	$message = str_replace($searcharray, $replacearray, preg_replace($pregarray, $replacearray, $message));
	$db->query("UPDATE {$tablepre}posts SET message='$message' WHERE pid='$pid'");
	updatecredits($discuz_uid, $postattachcredits, count($attachments));
}

require_once DISCUZ_ROOT.'./api/alipayapi.php';
trade_create(array(
	'tid' => $tid,
	'pid' => $pid,
	'aid' => $tradeaid,
	'typeid' => $tradetypeid,
	'item_expiration' => $item_expiration,
	'thread' => $thread,
	'discuz_uid' => $discuz_uid,
	'author' => $author,
	'seller' => $seller,
	'item_name' => $item_name,
	'item_price' => $item_price,
	'item_number' => $item_number,
	'item_quality' => $item_quality,
	'item_locus' => $item_locus,
	'transport' => $transport,
	'postage_mail' => $postage_mail,
	'postage_express' => $postage_express,
	'postage_ems' => $postage_ems,
	'item_type' => $item_type,
	'item_costprice' => $item_costprice
));

if($iscircle && $sgid) {
	supe_dbconnect();
	$query = $supe['db']->query("UPDATE {$supe[tablepre]}groups SET lastpost='$timestamp' WHERE gid='$sgid'", 'SILENT');
}

if($modnewthreads) {

	$db->query("UPDATE {$tablepre}forums SET todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
	$allowuseblog && $isblog && $blog ? showmessage('post_newthread_mod_blog_succeed', "blog.php?uid=$discuz_uid") :
		showmessage('post_newthread_mod_succeed', "forumdisplay.php?fid=$fid");

} else {

	if($digest) {
		foreach($digestcredits as $id => $addcredits) {
			$postcredits[$id] = (isset($postcredits[$id]) ? $postcredits[$id] : 0) + $addcredits;
		}
	}
	updatepostcredits('+', $discuz_uid, $postcredits);

	$lastpost = "$tid\t$subject\t$timestamp\t$author";
	$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', threads=threads+1, posts=posts+2, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
	if($forum['type'] == 'sub') {
		$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
	}

	if($allowuseblog && $isblog && $blog) {
		showmessage('post_newthread_blog_succeed', "blog.php?tid=$tid");
	} else {
		showmessage('post_newthread_succeed', "viewthread.php?tid=$tid&extra=$extra".(!empty($frombbs) ? "&frombbs=$frombbs" : ''));
	}

}

?>