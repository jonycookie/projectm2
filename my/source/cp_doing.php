<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_doing.php 7097 2008-04-16 04:17:52Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}
	
if(submitcheck('addsubmit')) {

	if(!checkperm('allowdoing')) {
		showmessage('no_privilege');
	}

	//判断是否操作太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', 1, array($waittime));
	}
	
	$message = getstr($_POST['message'], 200, 1, 1, 1);
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}
	
	//评论
	$doing = array();
	$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
	if($doid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE doid='$doid' AND uid != '$_SGLOBAL[supe_uid]'");
		$doing = $_SGLOBAL['db']->fetch_array($query);
	}
	if($doing) {
		$message = '@'.getstr($doing['username'], 0, 0, 1).' '.$message;
	}
	
	$setarr = array(
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'ip' => getonlineip()
	);
	//入库
	$newdoid = inserttable('doing', $setarr, 1);
	
	//更新空间note
	updatetable('space', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
	updatetable('spacefield', array('note'=>$message), array('uid'=>$_SGLOBAL['supe_uid']));
	
	if($doing) {
		//通知
		$note = lang('note_doing_reply', array("space.php?uid=$doing[uid]&do=doing&doid=$doid", "space.php?uid=$_SGLOBAL[supe_uid]&do=doing&doid=$newdoid"));
		
		notification_add($doing['uid'], 'doing', $note);
	} else {
		//事件feed
		$fs = array();
		$fs['icon'] = 'doing';
		
		$fs['title_template'] = '{actor}'.lang('colon').'{message}';
		$fs['title_data'] = array('message'=>$message);
		
		$fs['body_template'] = '';
		$fs['body_data'] = array();
		$fs['body_general'] = '';
	
		if(ckprivacy('doing', 1)) {
			feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
		}
	}
	showmessage('do_success', $_POST['refer'], 0);
}

//删除
if($_GET['op'] == 'delete') {
	
	$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
	
	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deletedoings(array($doid))) {
			showmessage('do_success', $_POST['refer']);
		} else {
			showmessage('no_privilege');
		}
	}
	
} elseif($_GET['op'] == 'reply') {
	
	$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
	
} else {
	showmessage('non_normal_operation', "space.php?uid=$_SGLOBAL[supe_uid]&do=doing");
}

include template('cp_doing');

?>