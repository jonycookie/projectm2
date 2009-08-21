<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do.php 6892 2008-03-30 02:21:40Z liguode $
*/

include_once('./common.php');

//获取方法
$ac = empty($_GET['ac'])?'':$_GET['ac'];

//允许的方法
$acs = array('login', 'comment', 'wall', 'register', 'lostpasswd', 'swfupload', 'inputpwd',
	'sns', 'viewspace', 'relatekw', 'ajax', 'seccode');
if(empty($_GET['ac']) || !in_array($_GET['ac'], $acs)) {
	showmessage('enter_the_space', 'space.php', 0);
} else {
	$ac = $_GET['ac'];
}

//链接
$theurl = 'do.php?ac='.$ac;

include_once(S_ROOT.'./source/do_'.$ac.'.php');

?>