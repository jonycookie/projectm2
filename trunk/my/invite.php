<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: invite.php 7045 2008-04-11 10:32:15Z liguode $
*/

include_once('./common.php');

//是否关闭站点
checkclose();

$id = 0;
$code = '';

$get = empty($_SERVER['QUERY_STRING'])?'':$_SERVER['QUERY_STRING'];
$invite = getinvite($get);
$space = getspace($invite['uid']);
if(empty($space)) {
	showmessage('space_does_not_exist');
}

//是否好友
$space['isfriend'] = $space['self'];
if($space['frienduid'] && in_array($_SGLOBAL['supe_uid'], explode(',', $space['frienduid']))) {
	$space['isfriend'] = 1;//是好友
}
if($space['isfriend']) {
	showmessage('you_have_friends');
}

if(submitcheck('invitesubmit')) {
	include_once(S_ROOT.'./source/function_cp.php');
	invite_update($invite['id'], $_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $space['uid'], addslashes($space['username']));

	showmessage('friends_add', 'space.php?do=friend', 1, array($space['username']));
}

include_once template('invite');

function getinvite($invite) {
	global $_SGLOBAL;
	
	$invite_len = strlen($invite);
	if($invite_len > 6) {
		$code = substr($invite, -6);
		$id = str_replace($code, '', $invite);
		$id = intval($id);
	}
	if(empty($id)) {
		showmessage('invite_code_error');
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE id='$id' AND code='$code'");
	if(!$invite = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('invite_code_error');
	}
	if($invite['fuid']) {
		showmessage('invite_code_fuid');
	}
	if($_SGLOBAL['supe_uid'] == $invite['uid']) {
		showmessage('should_not_invite_your_own');
	}
	return $invite;
}

?>