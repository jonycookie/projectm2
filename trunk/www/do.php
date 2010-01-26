<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do.php 9293 2008-10-30 06:44:42Z liguode $
*/

include_once('./common.php');

//获取方法
$ac = empty($_GET['ac'])?'':$_GET['ac'];

//自定义登录
if($ac == $_SCONFIG['login_action']) {
	$ac = 'login';
} elseif($ac == 'login') {
	$ac = '';
}
if($ac == $_SCONFIG['register_action']) {
	$ac = 'register';
} elseif($ac == 'register') {
	$ac = '';
}

//允许的方法
$acs = array('login', 'comment', 'wall', 'register', 'lostpasswd', 'swfupload', 'inputpwd',
	'sns', 'viewspace', 'relatekw', 'ajax', 'seccode', 'sendmail');
if(empty($ac) || !in_array($ac, $acs)) {
	showmessage('enter_the_space', 'index.php', 0);
}

//链接
$theurl = 'do.php?ac='.$ac;

include_once(S_ROOT.'./source/do_'.$ac.'.php');

?>