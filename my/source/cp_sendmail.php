<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_sendmail.php 12180 2009-05-14 09:38:02Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_GET['op'] = empty($_GET['op']) ? '' : trim($_GET['op']);

if(submitcheck('setsendemailsubmit')) {
	//ÓÊÏäÌáÐÑÉèÖÃ
	if(empty($_SCONFIG['sendmailday'])) {
		showmessage('no_privilege');
	}
	$_POST['sendmail'] = addslashes(serialize($_POST['sendmail']));
	updatetable('spacefield', array('sendmail'=>$_POST['sendmail']), array('uid'=>$space['uid']));
	showmessage('do_success', 'cp.php?ac=sendmail');
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