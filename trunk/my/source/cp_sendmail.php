<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_sendmail.php 9167 2008-10-24 06:55:15Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_GET['op'] = empty($_GET['op']) ? '' : trim($_GET['op']);

if(submitcheck('setsendemailsubmit')) {

	if(empty($_SCONFIG['sendmailday'])) {
		showmessage('no_privilege');
	}
	$_POST['sendmail'] = addslashes(serialize($_POST['sendmail']));
	updatetable('spacefield', array('sendmail'=>$_POST['sendmail']), array('uid'=>$space['uid']));
	showmessage('do_success', 'cp.php?ac=sendmail');
}

//ÓÊÏä¼¤»î
if($_GET['op'] == 'check') {
	$_GET['hash'] = empty($_GET['hash']) ? '' : trim($_GET['hash']);
	$mailhash = md5($space['email'].'|'.md5($_SCONFIG['sitekey']).'|'.$space['uid']);
	if($_GET['hash'] == $mailhash) {
		updatetable('spacefield', array('emailcheck'=>'1'), array('uid'=>$space['uid']));
		$jumpurl = empty($_SCONFIG['sendmailday'])?'cp.php?ac=password':'cp.php?ac=sendmail';
		showmessage('email_check_sucess', $jumpurl);
	} else {
		showmessage('email_check_error');
	}
}

if(empty($_SCONFIG['sendmailday'])) {
	showmessage('no_privilege');
}

//ÌîÐ´ÓÊÏä
if(empty($space['email'])) {
	showmessage('email_input');
}

$sendmail = array();
$space['sendmail'] = empty($space['sendmail']) ? array() : unserialize($space['sendmail']);
if($space['sendmail']) {
	foreach($space['sendmail'] as $mkey=>$mailset) {
		if($mkey != 'frequency') {
			$sendmail[$mkey] = empty($space['sendmail'][$mkey]) ? '' : ' checked';
		} else {
			$sendmail[$mkey] = array($space['sendmail']['frequency'] => 'selected');
		}
	}
}

include_once template("cp_sendmail");

?>