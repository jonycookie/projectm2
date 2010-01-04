<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: magic_money.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(submitcheck('usesubmit')) {

	$getmoney = rand(1, intval($magic['price'] * 1.5));
	$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+'$getmoney' WHERE uid='$discuz_uid'");

	usemagic($magicid, $magic['num']);
	updatemagiclog($magicid, '2', '1', '0', '', '', $discuz_uid);
	showmessage('magics_MOK_message');

}

function showmagic() {
	global $lang;
	magicshowtips($lang['MOK_info'], $lang['option']);
}

?>