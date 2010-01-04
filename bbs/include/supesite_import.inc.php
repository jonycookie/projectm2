<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: supesite_import.inc.php 10115 2007-08-24 00:58:08Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$supe['status']) {
	showmessage('supe_resource_imported_forbid');
}

supe_dbconnect();

$discuz_action = 15;

$navtitle = '';

if(!$discuz_uid && !((!$forum['postperm'] && $allowpost) || ($forum['postperm'] && forumperm($forum['postperm'])))) {
	showmessage('group_nopermission', NULL, 'NOPERM');
} elseif(empty($forum['allowpost'])) {
	if(!$forum['postperm'] && !$allowpost) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($forum['postperm'] && !forumperm($forum['postperm'])) {
		showmessage('post_forum_newthread_nopermission', NULL, 'HALTED');
	}
}

checklowerlimit($postcredits);

$itemid = intval($itemid);

if(!submitcheck('importsubmit', 0, $seccodecheck, $secqaacheck)) {

	include_once DISCUZ_ROOT.'./include/forum.func.php';
	$forumselect = forumselect();

	$query = $db->query("SELECT f.fid,ff.threadtypes
		FROM {$tablepre}forumfields ff
		LEFT JOIN {$tablepre}forums f ON f.fid=ff.fid
		ORDER BY f.type, f.displayorder");
	$data = $threadtypes = array();
	$js_threadtypes = '';
	$js_threadtypesrequired = '';
	while($data = $db->fetch_array($query)) {
		$data['threadtypes'] = unserialize($data['threadtypes']);
		$threadtypes[$data['fid']] = $data['threadtypes']['types'];
		$js_threadtypes .= "threadtypes[{$data[fid]}] = new Array();\r\n";
		if(is_array($data['threadtypes']['types']) && $data['threadtypes']['types']) {
			foreach($data['threadtypes']['types'] as $k=>$v) {
				$js_threadtypes .= "threadtypes[{$data[fid]}][$k] = '$v';\r\n";
			}
		}
		$js_threadtypesrequired .= "threadtypesrequired[{$data[fid]}] = '{$data[threadtypes][required]}';\r\n";
	}

	include template('supesite_import');

} else {

	if(!$fid = intval($fid)) {
		showmessage('supe_pls_select_forum');
	}

	if(checkflood()) {
		showmessage('post_flood_ctrl');
	}

	$typeid = intval($_POST['typeid']);
	@list($defaulttypeid) = array_slice($forum['threadtypes']['types'], 0, 1);
	$typeid = isset($forum['threadtypes']['types'][$typeid]) ? $typeid : ($forum['threadtypes']['required'] ? $defaulttypeid : 0);

	include_once DISCUZ_ROOT.'./include/supesite.func.php';
	$trade = array();
	$special = 0;
	$subject = $message = '';
	$item = supe_xspace2forum($itemid, $subject, $message, $special, $iconid, $trade);
	$iconid = intval($iconid);
	$item = daddslashes($item, 1);
	$subject = addslashes($subject);
	$message = addslashes($message);

	if(!$item) {
		showmessage('supe_resource_not_exist');
	}
	if($item['uid'] != $discuz_uid) {
		showmessage('supe_resource_is_not_of_you');
	}
	if($item['tid']) {
		showmessage('supe_resource_have_imported_into_forum');
	}

	if($item['replynum'] && ($modnewthreads || $modnewreplies)) {
		showmessage('supe_imported_newthread_have_replies');
	}

	if($special == 2) {
		$allowposttrade = substr(sprintf('%04b', $forum['allowpostspecial']), -2, 1) && $allowposttrade;
		if(!$ec_account) {
			showmessage('supe_imported_trade_notallowed');
		} elseif(!$allowposttrade) {
			showmessage('supe_imported_trade_forum_notallowed');
		}
	}

	$displayorder = $modnewthreads ? '-2' : '0';
	$db->query("INSERT INTO {$tablepre}threads (fid, readperm, price, iconid, typeid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, blog, special, attachment, moderated, itemid)
		VALUES ('$fid', '0', '0', '$iconid', '$typeid', '$item[username]', '$item[uid]', '$subject', '$item[dateline]', '$timestamp', '$item[username]', '$displayorder', '0', '0', '$special', '0', '0', '$itemid')");
	$tid = $db->insert_id();

	if($special == 2) {
		require_once DISCUZ_ROOT.'./api/alipayapi.php';
		$trade['tid'] = $tid;
		$trade['thread'] = $thread;
		$trade['discuz_uid'] = $discuz_uid;
		$trade['author'] = $discuz_user;
		trade_create($trade);
	}
	unset($displayorder, $trade);

	$pinvisible = $modnewthreads ? -2 : 0;
	$htmlon = $forum['allowhtml'] || $allowhtml ? 1 : 0;
	$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
		VALUES ('$fid', '$tid', '1', '$item[username]', '$item[uid]', '$subject', '$item[dateline]', '$message', '$onlineip', '$pinvisible', '1', '$htmlon', '0', '1', '0', '0')");
	$pid = $db->insert_id();

	$postuids = $lastreply = $reply = array();
	$replypost = 0;

	if($item['replynum'] == 0) {

		if(!$modnewthreads) {

			updatepostcredits('+', $discuz_uid, $postcredits);

			$lastpost = "$tid\t$subject\t$timestamp\t$item[username]";
			$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');

			if($forum['type'] == 'sub') {
				$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
			}
			unset($lastpost);

			$supe['db']->query("UPDATE {$supe[tablepre]}spaceitems SET tid='$tid' WHERE itemid='$itemid'", 'UNBUFFERED');
			showmessage('supe_imported_succeed', "viewthread.php?tid=$tid");

		} else {

			$supe['db']->query("UPDATE {$supe[tablepre]}spaceitems SET tid='$tid' WHERE itemid='$itemid'", 'UNBUFFERED');
			showmessage('supe_newthread_import_mod_succeed', "forumdisplay.php?fid=$fid");
		}

	} else {

		$query = $supe['db']->query("SELECT cid, itemid, type, uid, authorid, author, ip, dateline, rates, message FROM {$supe[tablepre]}spacecomments WHERE itemid='$itemid' ORDER BY dateline ASC");
		while($reply = $db->fetch_array($query)) {
			$reply = daddslashes($reply, 1);

			$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
				VALUES ('$fid', '$tid', '0', '$reply[author]', '$reply[authorid]', '', '$reply[dateline]', '$reply[message]', '$reply[ip]', '0', '', '0', '0', '0', '1', '0')");

			$postuids[] = $reply['uid'];

			$lastreply = $reply;
			$replypost++;
		}

		$db->query("UPDATE {$tablepre}threads SET lastpost='$timestamp', replies=replies+$replypost WHERE fid='$fid' AND tid='$tid'", 'UNBUFFERED');
		$replycredits = $forum['replycredits'] ?  $forum['replycredits'] : $_DCACHE['settings']['creditspolicy']['reply'];

		updatepostcredits('+', $postuids, $replycredits);

		unset($postuids, $reply);

		$lastpost = "$tid\t$subject\t$timestamp\t$lastreply[author]";
		$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', threads=threads+1, posts=posts+$replypost, todayposts=todayposts+$replypost WHERE fid='$fid'", 'UNBUFFERED');
		unset($lastreply, $replypost);

		if($forum['type'] == 'sub') {
			$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
		}

		$supe['db']->query("DELETE FROM {$supe[tablepre]}spacecomments WHERE itemid='$itemid'", 'UNBUFFERED');
		$supe['db']->query("UPDATE {$supe[tablepre]}spaceitems SET tid='$tid' WHERE itemid='$itemid'", 'UNBUFFERED');
		showmessage('supe_imported_succeed', "viewthread.php?tid=$tid");
	}
}

?>