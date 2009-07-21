<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_password.php 10145 2008-11-26 03:05:39Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('emailchecksubmit')) {
	
	//检查邮箱
	$_POST['email'] = isemail($_POST['email'])?$_POST['email']:'';
	if(empty($_POST['email'])) {
		showmessage('email_error');
	}
	
	//验证密码
	if($_POST['email'] != $space['email']) {
		if(!$passport = getpassport($_SGLOBAL['supe_username'], $_POST['password'])) {
			showmessage('password_is_not_passed');
		}
		//更新资料
		$space['email'] = $_POST['email'];
		updatetable('spacefield', array('email'=>$_POST['email'], 'emailcheck'=>0), array('uid'=>$space['uid']));
	}
	
	$mailhash = md5($space['email'].'|'.md5($_SCONFIG['sitekey']).'|'.$space['uid']);
	$siteurl = getsiteurl();
	$checkurl = $siteurl.'cp.php?ac=sendmail&amp;op=check&amp;hash='.$mailhash;
	$mailsubject = cplang('active_email_subject');
	$mailmessage = cplang('active_email_msg', array($checkurl));
	smail(0, $space['email'], $mailsubject, $mailmessage);
	
	showmessage('email_check_send');
	
} elseif(submitcheck('pwdsubmit')) {
	
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	@include_once(S_ROOT.'./uc_client/client.php');
	
	$ucresult = uc_user_edit($_SGLOBAL['supe_username'], $_POST['password'], $_POST['newpasswd1'], $space['email']);

	if($ucresult == -1) {
		showmessage('old_password_invalid');
	} elseif($ucresult == -4) {
		showmessage('email_format_is_wrong');
	} elseif($ucresult == -5) {
		showmessage('email_not_registered');
	} elseif($ucresult == -6) {
		showmessage('email_has_been_registered');
	} elseif($ucresult == -7) {
		showmessage('no_change');
	} elseif($ucresult == -8) {
		showmessage('protection_of_users');
	}
	clearcookie();
	showmessage('getpasswd_succeed', 'do.php?ac='.$_SCONFIG['login_action']);
}

$actives = array($ac => ' class="active"');

include_once template("cp_password");

?>