<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: magic_reporter.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(submitcheck('usesubmit')) {

	if(empty($username)) {
		showmessage('magics_info_nonexistence');
	}

	$member = getuserinfo($username, array('uid', 'groupid'));
	checkmagicperm($magicperm['targetgroups'], $member['groupid']);

	$query = $db->query("SELECT action FROM {$tablepre}sessions WHERE uid='$member[uid]'");
	if(!$msession = $db->fetch_array($query)) {
		$magicmessage = 'magics_RTK_on_message';
	} else {
		include language('actions');
		$magicmessage = 'magics_RTK_off_message';
	}

	usemagic($magicid, $magic['num']);
	updatemagiclog($magicid, '2', '1', '0', '', '', $member['uid']);
	showmessage($magicmessage);

}

function showmagic() {
	global $username, $lang;
	magicshowtype($lang['option'], 'top');
	magicshowsetting($lang['target_username'], 'username', $username, 'text');
	magicshowtype('', 'bottom');
}

?>