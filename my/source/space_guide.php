<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_guide.php 7245 2008-04-30 09:04:54Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$step = empty($_GET['step'])?1:intval($_GET['step']);
if(!in_array($step, array(1,2,3,4))) $step = 1;

$actives = array($step => ' class="active"');
$_SGLOBAL['guidemode'] = 1;

//扩展资料
if($step == 1 || $step == 3) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacefield')." WHERE uid='$space[uid]'");
	$spacefield = $_SGLOBAL['db']->fetch_array($query);
	$space = array_merge($space, $spacefield);
}
	
if($step == 1) {
	include_once(S_ROOT.'./source/cp_profile.php');
} elseif($step == 2) {
	//选吧
	include_once(S_ROOT.'./source/cp_mtag.php');
} elseif($step == 3) {
	//找好友
	$_GET['op'] = 'find';
	include_once(S_ROOT.'./source/cp_friend.php');
} elseif($step == 4) {
	//关闭向导
	updatetable('space', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('do_success', 'space.php?do=home&view=all&shownotice=yes', 0);
}

?>