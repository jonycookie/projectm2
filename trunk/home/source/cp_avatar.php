<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_avatar.php 10586 2008-12-10 06:53:47Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('avatarsubmit')) {
	if($_POST['guidemode']) {
		showmessage('do_success', 'space.php?do=home&view=guide&step=2', 0);
	} else {
		showmessage('do_success', 'cp.php?ac=avatar', 0);
	}
}

//ͷ��
include_once S_ROOT.'./uc_client/client.php';
$uc_avatarflash = uc_avatar($_SGLOBAL['supe_uid'], (empty($_SCONFIG['avatarreal'])?'virtual':'real'));

//�ж��û��Ƿ�������ͷ��
$setarr = array();
$avatar_exists = ckavatar($space['uid']);
if($avatar_exists) {
	if(!$space['avatar']) {
		$setarr['avatar'] = 1;
		$setarr['updatetime'] = $_SGLOBAL['timestamp'];
	}
} else {
	if($space['avatar']) {
		$setarr['avatar'] = 0;
	}
}

if($setarr) {
	updatetable('space', $setarr, array('uid'=>$space['uid']));
	//�����¼
	if($_SCONFIG['my_status']) {
		inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
	}
}

include template("cp_avatar");

?>