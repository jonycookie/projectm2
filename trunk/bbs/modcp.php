<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: modcp.php 17489 2008-12-26 10:03:22Z cnteacher $
*/

define('NOROBOT', TRUE);
define('SQL_ADD_THREAD', 't.subject, t.authorid, t.digest, ');
require_once './include/common.inc.php';

if(!$discuz_uid || !($forum['ismoderator']) || $forum['digest'] < 0) {
	showmessage('admin_nopermission', NULL, 'HALTED');
}

if($action == 'editsubject') {

	$orig = $db->fetch_first("SELECT m.adminid, p.first, p.authorid, p.author, p.dateline, p.anonymous, p.invisible FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
		WHERE p.tid='$tid' AND p.first='1' AND fid='$fid'");

	if(empty($orig)) {
		showmessage('thread_nonexistence', NULL, 'AJAXERROR');
	}

	periodscheck('postbanperiods');

	if(empty($forum['allowview'])) {
		if(!$forum['viewperm'] && !$readaccess) {
			showmessage('group_nopermission', NULL, 'NOPERM');
		} elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
			showmessage('forum_nopermission', NULL, 'NOPERM');
		}
	}

	if(!$forum['ismoderator'] || !$alloweditpost || (in_array($orig['adminid'], array(1, 2, 3)) && $adminid > $orig['adminid'])) {
		showmessage('post_edit_nopermission', NULL, 'HALTED');
	}

	require_once DISCUZ_ROOT.'./include/post.func.php';
	require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
	$subject = $subjectnew;
	if($post_invalid = checkpost()) {
		showmessage($post_invalid);
	}

	if(!submitcheck('editsubjectsubmit', 1)) {
		include template('modcp_editpost');
		exit;
	} else {
		$subjectnew = dhtmlspecialchars(censor(trim($subjectnew)));
		$query = $db->query("UPDATE {$tablepre}threads SET subject='$subjectnew' WHERE tid='$tid'");
		$query = $db->query("UPDATE {$tablepre}posts SET subject='$subjectnew' WHERE tid='$tid' AND first='1'");
		showmessage('<a href="viewthread.php?tid='.$tid.'">'.stripslashes($subjectnew).'</a>');
	}

} elseif($action == 'editmessage') {

	$orig = $db->fetch_first("SELECT m.adminid, p.first, p.authorid, p.author, p.dateline, p.anonymous, p.invisible, p.message FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
		WHERE p.pid='$pid' AND p.invisible > -1");

	if(empty($orig)) {
		showmessage('post_check', NULL, 'AJAXERROR');
	}

	periodscheck('postbanperiods');

	if(empty($forum['allowview'])) {
		if(!$forum['viewperm'] && !$readaccess) {
			showmessage('group_nopermission', NULL, 'NOPERM');
		} elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
			showmessage('forum_nopermission', NULL, 'NOPERM');
		}
	}

	if(!$forum['ismoderator'] || !$alloweditpost || (in_array($orig['adminid'], array(1, 2, 3)) && $adminid > $orig['adminid'])) {
		showmessage('post_edit_nopermission', NULL, 'HALTED');
	}

	require_once DISCUZ_ROOT.'./include/post.func.php';
	if($post_invalid = checkpost()) {
		showmessage($post_invalid);
	}

	if(!submitcheck('editmessagesubmit', 1)) {
		$orig['message'] = dhtmlspecialchars($orig['message']);
		include template('modcp_editpost');
		exit;
	} else {
		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
		if($do == 'notupdate') {
			$message = $orig['message'];
			$message = discuzcode($message, 0, 0, 0, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $showimages ? 1 : 0), $forum['allowhtml'], 0, 0, $orig['authorid']);
			showmessage(stripslashes($message));
		} else {
			$message = censor(trim($message));
			$query = $db->query("UPDATE {$tablepre}posts SET message='$message' WHERE pid='$pid'");
			$message = discuzcode($message, 0, 0, 0, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $showimages ? 1 : 0), $forum['allowhtml'], 0, 0, $orig['authorid']);
			showmessage(stripslashes($message));
		}
	}

}

include template('modcp');
?>