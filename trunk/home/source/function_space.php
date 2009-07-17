<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_space.php 10903 2008-12-31 06:06:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//��ͨ�ռ�
function space_open($uid, $username, $gid=0, $email='') {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($uid) || empty($username)) return array();

	//��֤�ռ��Ƿ񱻹���Աɾ��
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacelog')." WHERE uid='$uid' AND flag='-1'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('the_space_has_been_closed');
	}
	$space = array(
		'uid' => $uid,
		'username' => $username,
		'dateline' => $_SGLOBAL['timestamp'],
		'groupid' => $gid
	);
	inserttable('space', $space, 0, true);
	inserttable('spacefield', array('uid'=>$uid, 'email'=>$email), 0, true);

	//����PM
	if($_SGLOBAL['supe_uid'] && $_SGLOBAL['supe_uid'] != $uid) {
		include_once S_ROOT.'./uc_client/client.php';
		uc_pm_send($_SGLOBAL['supe_uid'], $uid, cplang('space_open_subject'), cplang('space_open_message', array(getsiteurl())), 1, 0, 0);
	}

	//����feed
	include_once(S_ROOT.'./source/function_cp.php');
	$_uid = $_SGLOBAL['supe_uid'];
	$_username = $_SGLOBAL['supe_username'];
	$_SGLOBAL['supe_uid'] = $uid;
	$_SGLOBAL['supe_username'] = addslashes($username);
	feed_add('profile', cplang('feed_space_open'));
	
	$_SGLOBAL['supe_uid'] = $_uid;
	$_SGLOBAL['supe_username'] = $_username;
	
	return $space;
}

//���session
function insertsession($setarr) {
	global $_SGLOBAL, $_SCONFIG;

	$_SCONFIG['onlinehold'] = intval($_SCONFIG['onlinehold']);
	if($_SCONFIG['onlinehold'] < 300) $_SCONFIG['onlinehold'] = 300;
	$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$setarr[uid]' OR lastactivity<'".($_SGLOBAL['timestamp']-$_SCONFIG['onlinehold'])."'");

	//�������
	$ip = getonlineip(1);
	$setarr['lastactivity'] = $_SGLOBAL['timestamp'];
	$setarr['ip'] = $ip;
	inserttable('session', $setarr, 0, true, 1);

	//�����û�
	updatetable('space', array('lastlogin'=>$_SGLOBAL['timestamp'], 'ip' => $ip), array('uid'=>$setarr['uid']), 1);
}

//��ȡ����
function gettask() {
	global $space, $_SGLOBAL;
	
	$task = array();
	if(!@include_once(S_ROOT.'./data/data_task.php')) {
		include_once(S_ROOT.'./source/function_cache.php');
		task_cache();
	}
	
	if($_SGLOBAL['task']) {
		$usertasks = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usertask')." WHERE uid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$usertasks[$value['taskid']] = $value;
		}
		//��Ҫִ�е�����
		foreach ($_SGLOBAL['task'] as $value) {
			if($value['starttime'] <= $_SGLOBAL['timestamp'] && (empty($usertasks[$value['taskid']]) || ($value['nexttime'] && $_SGLOBAL['timestamp']-$usertasks[$value['taskid']]['dateline'] >= $value['nexttime']))) {
				$value['image'] = empty($value['image'])?'image/task.gif':$value['image'];
				$task = $value;
				break;
			}
		}
	}

	return $task;
}

?>