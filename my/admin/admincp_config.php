<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_config.php 7186 2008-04-25 08:35:23Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageconfig')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('thevaluesubmit')) {

	$setarr = array();
	
	if(empty($_POST['config']['seccode_login'])) $_POST['config']['seccode_login'] = 0;
	if(empty($_POST['config']['seccode_register'])) $_POST['config']['seccode_register'] = 0;
	foreach ($_POST['config'] as $var => $value) {
		$value = trim($value);
		if(!isset($_SCONFIG[$var]) || $_SCONFIG[$var] != $value) {
			$setarr[] = "('$var', '$value')";
		}
	}
	if($setarr) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $setarr));
	}
	
	//data设置
	$datas = array();
	foreach ($_POST['data'] as $var => $value) {
		$datas[$var] = trim(stripslashes($value));
	}
	data_set('setting', addslashes(serialize($datas)));
	
	//发送邮件设置
	$mails = array();
	foreach ($_POST['mail'] as $var => $value) {
		$mails[$var] = trim(stripslashes($value));
	}
	data_set('mail', addslashes(serialize($mails)));

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();;

	cpmessage('do_success', 'admincp.php?ac=config');
}

$configs = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('config'));
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$configs[$value['var']] = shtmlspecialchars($value['datavalue']);
}
if(empty($configs['siteallurl'])) $configs['siteallurl'] = getsiteurl();

$datas = data_get('setting');
$datas = empty($datas)?array():unserialize($datas);

$mails = data_get('mail');
$mails = empty($mails)?array():unserialize($mails);

$onlineip = getonlineip();

?>