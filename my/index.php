<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: index.php 7338 2008-05-08 08:29:49Z liguode $
*/

include_once('./common.php');

if(is_numeric($_SERVER['QUERY_STRING'])) {
	showmessage('enter_the_space', "space.php?uid=$_SERVER[QUERY_STRING]", 0);
}

//二级域名
if(!isset($_GET['do']) && $_SCONFIG['allowdomain']) {
	$hostarr = explode('.', $_SERVER['HTTP_HOST']);
	if(count($hostarr) > 2 && $hostarr[0] != 'www' && !isholddomain($hostarr[0])) {
		showmessage('enter_the_space', $_SCONFIG['siteallurl'].'space.php?domain='.$hostarr[0], 0);
	}
}

if($_SGLOBAL['supe_uid']) {
	//已登录，直接跳转个人首页
	showmessage('enter_the_space', 'space.php?do=home', 0);
}

//已有空间数
$spacecount = getcount('space', array());

//最新入住
$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." ORDER BY dateline DESC LIMIT 0,1");
$newmember = $_SGLOBAL['db']->fetch_array($query);

//最后登录名
$membername = empty($_SCOOKIE['loginuser'])?'':sstripslashes($_SCOOKIE['loginuser']);

include template('index');

?>