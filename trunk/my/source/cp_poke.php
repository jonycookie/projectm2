<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_poke.php 7045 2008-04-11 10:32:15Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

if($uid == $_SGLOBAL['supe_uid']) {
	showmessage('not_to_their_own_greeted');
}

if($op == 'send' || $op == 'reply') {
	
	//获取对象
	$tospace = getspace($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}
	
	//打招呼
	if(submitcheck('pokesubmit')) {
		$setarr = array(
			'uid' => $uid,
			'fromuid' => $_SGLOBAL['supe_uid'],
			'fromusername' => $_SGLOBAL['supe_username'],
			'note' => getstr($_POST['note'], 50, 1, 1)
		);
		inserttable('poke', $setarr, 0, true);
		
		if($op == 'reply') {
			//删除招呼
			$_SGLOBAL['db']->query("DELETE FROM ".tname('poke')." WHERE uid='$_SGLOBAL[supe_uid]' AND fromuid='$uid'");
		}

		showmessage('poke_success', $_POST['refer'], 1, array($tospace['username']));
	}
	
	include_once template('cp_poke');
	
} elseif($op == 'ignore') {
	
	$where = empty($uid)?'':"AND fromuid='$uid'";
	$_SGLOBAL['db']->query("DELETE FROM ".tname('poke')." WHERE uid='$_SGLOBAL[supe_uid]' $where");
	
	showmessage('has_been_hailed_overlooked');
}

?>