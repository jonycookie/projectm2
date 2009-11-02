<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_cache.php 7046 2008-04-12 13:53:00Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecache')) {
	cpmessage('no_authority_management_operation');
}

$turl = 'admincp.php?ac=cache';

//更新缓存
if(submitcheck('cachesubmit')) {
	include_once(S_ROOT.'./source/function_cp.php');
	include_once(S_ROOT.'./source/function_cache.php');
	
	//系统缓存
	if(empty($_POST['cachetype']) || in_array('database', $_POST['cachetype'])) {
		config_cache();
		usergroup_cache();
		profilefield_cache();
		profield_cache();
		censor_cache();
		block_cache();
	}
	//模板编译缓存
	if(empty($_POST['cachetype']) || in_array('tpl', $_POST['cachetype'])) {
		tpl_cache();
	}
	//模块缓存
	if(empty($_POST['cachetype']) || in_array('block', $_POST['cachetype'])) {
		block_data_cache();
	}
	//随便看看缓存
	if(empty($_POST['cachetype']) || in_array('network', $_POST['cachetype'])) {
		@unlink(S_ROOT.'./data/data_network.php');
	}

	cpmessage('do_success', $turl);

}

?>