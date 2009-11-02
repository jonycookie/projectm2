<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_lostpasswd.php 7249 2008-04-30 09:53:57Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':$_GET['op'];

if(submitcheck('lostpwsubmit')) {
	
	$mailarr = array();
	include_once(S_ROOT.'./uc_client/client.php');
	list($tmp['uid'], , $tmp['email']) = uc_get_user($_POST['username']);
	
	//获取UCHome本身的邮箱地址
	$spaceinfo = array();
	$query = $_SGLOBAL['db']->query('SELECT s.uid, s.username, sf.email FROM '.tname('space').' s, '.tname('spacefield')." sf WHERE s.username='$_POST[username]' AND sf.uid=s.uid");
	$spaceinfo = $_SGLOBAL['db']->fetch_array($query);
	
	if($spaceinfo['email'] != $_POST['email'] && $tmp['email'] != $_POST['email']) {
		showmessage('getpasswd_account_notmatch');
	}
	
	//创始人不允许找回密码
	$founderarr = explode(',', $_SC['founder']);
	if(in_array($tmp['uid'], $founderarr) || in_array($spaceinfo['uid'], $founderarr)) {
		showmessage('getpasswd_account_invalid');
	}
	if($spaceinfo['email'] == $tmp['email']) {
		$mailarr[] = $tmp['email'];
	} else {
		if($tmp['email']) $mailarr[] = $tmp['email'];
		if($spaceinfo['email']) $mailarr[] = $spaceinfo['email'];
	}
	$idstring = random(6);
	$reseturl = getsiteurl().'do.php?ac=lostpasswd&op=reset&uid='.$tmp['uid'].'&id='.$idstring;
	updatetable('spacefield', array('authstr'=>$_SGLOBAL['timestamp']."\t1\t".$idstring), array('uid'=>$tmp['uid']));
	$mail_subject = lang('get_passwd_subject');
	$mail_message = lang('get_passwd_message', array($reseturl));
	$mail_message = preg_replace("/(\<br\>|\<br \/\>)/i", "\r\n", $mail_message);
	include_once(S_ROOT.'./source/function_sendmail.php');
	sendmail($mailarr, $mail_subject, $mail_message);//发送邮件
	showmessage('getpasswd_send_succeed', 'do.php?ac=login', 5);
	
} elseif(submitcheck('resetsubmit')) {
	
	$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
	$id = empty($_POST['id'])?0:trim($_POST['id']);
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}
	$query = $_SGLOBAL['db']->query('SELECT s.username, sf.email, sf.authstr FROM '.tname('space').' s, '.tname('spacefield')." sf WHERE s.uid='$uid' AND sf.uid=s.uid");
	$space = $_SGLOBAL['db']->fetch_array($query);
	checkuser($id, $space);
	uc_user_edit(addslashes($space['username']), $_POST['newpasswd1'], $_POST['newpasswd1'], $space['email'], 1);
	updatetable('spacefield', array('authstr'=>''), array('uid'=>$uid));
	showmessage('getpasswd_succeed', 'do.php?ac=login');
}

if($op == 'reset') {
	$query = $_SGLOBAL['db']->query('SELECT s.username, sf.email, sf.authstr FROM '.tname('space').' s, '.tname('spacefield')." sf WHERE s.uid='$_GET[uid]' AND sf.uid=s.uid");
	$space = $_SGLOBAL['db']->fetch_array($query);
	checkuser($_GET['id'], $space);
}

include template('do_lostpasswd');

//验证地址地否有效
function checkuser($id, $space) {
	global $_SGLOBAL;
	if(empty($space)) {
		showmessage('user_does_not_exist');
	}
	list($dateline, $operation, $idstring) = explode("\t", $space['authstr']);
	if($dateline < $_SGLOBAL['timestamp'] - 86400 * 3 || $operation != 1 || $idstring != $id) {
		showmessage('getpasswd_illegal');
	}
}
?>