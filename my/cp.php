<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp.php 7241 2008-04-30 07:20:51Z liguode $
*/

//通用文件
include_once('./common.php');
include_once(S_ROOT.'./source/function_cp.php');

//允许的方法
$acs = array('space', 'doing', 'upload', 'comment', 'blog', 'album', 'relatekw', 'common', 'class',
	'swfupload', 'thread', 'mtag', 'poke', 'friend',
	'profile', 'theme', 'import', 'feed', 'privacy', 'pm', 'share', 'advance', 'invite');
$ac = (empty($_GET['ac']) || !in_array($_GET['ac'], $acs))?'profile':$_GET['ac'];
$op = empty($_GET['op'])?'':$_GET['op'];

//权限判断
if(empty($_SGLOBAL['supe_uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		ssetcookie('_refer', rawurlencode('cp.php?ac='.$ac));
	}
	showmessage('to_login');
}

//是否关闭站点
if(!in_array($ac, array('common', 'pm'))) {
	checkclose();
}

//扩展
if(($ac == 'theme' && $op == 'diy') || $ac == 'profile' || $ac == 'friend') {
	$_SGLOBAL['do_index'] = 1;

}

//获取空间信息
$space = getspace($_SGLOBAL['supe_uid']);
if(empty($space)) {
	showmessage('space_does_not_exist');
}
	
//菜单
$actives = array($ac => ' class="active"');

include_once(S_ROOT.'./source/cp_'.$ac.'.php');

?>