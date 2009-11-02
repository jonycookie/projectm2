<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_index.php 7226 2008-04-29 07:35:18Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$network = array();

$cachelost = true;
$writestate = $_SCONFIG['networkupdate'] ? true : false;
if($_SCONFIG['networkupdate']) {
	$cachefile = S_ROOT.'./data/data_network.php';
	$lockfile = S_ROOT.'./data/data_network.lock';

	if(file_exists($cachefile)) {
		@$mktime = filemtime($cachefile);
		if($_SGLOBAL['timestamp'] - $mktime > $_SCONFIG['networkupdate']) {
			if(file_exists($lockfile)) {
				$writestate = false;
				@touch($lockfile);
			}
			
		} else {
			include_once($cachefile);
			$cachelost = false;
		}
	}
}
if($cachelost) {

	//载入缓存配置
	@include_once(S_ROOT.'./data/data_network_setting.php');
	$sql = '';
	//成员列表
	$netcache['spacelist'] = array();
	if(empty($network['space'])) {
		$sql = " FORCE INDEX (updatetime) ORDER BY updatetime DESC LIMIT 0,12";
	} else {
		$sql = ' '.trim($network['space']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$netcache['spacelist'][] = $value;
	}
	
	//迷你博客
	$netcache['doinglist'] = array();
	if(empty($network['doing'])) {
		$sql = " FORCE INDEX (dateline) ORDER BY dateline DESC LIMIT 0,5";
	} else {
		$sql = ' '.trim($network['doing']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$netcache['doinglist'][] = $value;
	}
	
	//个人分享
	$netcache['sharelist'] = array();
	if(empty($network['share'])) {
		$sql = " FORCE INDEX (dateline) ORDER BY dateline DESC LIMIT 0,10";
	} else {
		$sql = ' '.trim($network['share']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value = mkshare($value);
		$netcache['sharelist'][] = $value;
	}
	
	//日志
	$netcache['bloglist'] = array();
	if(empty($network['blog'])) {
		$sql = " b.friend='0' ORDER BY b.dateline DESC LIMIT 0,5";
	} else {
		$sql = trim($network['blog']);
	}
	$query = $_SGLOBAL['db']->query("SELECT b.blogid, b.subject, b.uid, b.username, b.dateline, b.viewnum, b.replynum, b.friend, bf.message 
				FROM ".tname('blog')." b
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE ".$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			$value['message'] = $value['friend']==4?lang('password_message'):getstr($value['message'], 150, 0, 0, 0, 0, -1);
			$netcache['bloglist'][] = $value;
		}
	}
	
	//相册
	$netcache['albumlist'] = array();
	if(empty($network['album'])) {
		$sql = " friend='0' ORDER BY updatetime DESC LIMIT 0,5";
	} else {
		$sql = trim($network['album']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE ".$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			$value['pic'] = mkpicurl($value);
			$netcache['albumlist'][] = $value;
		}
	}
	
	//个人选吧
	$netcache['mtaglist'] = array();
	if(empty($network['mtag'])) {
		$sql = " FORCE INDEX (membernum) ORDER BY membernum DESC LIMIT 0,30";
	} else {
		$sql = ' '.trim($network['mtag']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$netcache['mtaglist'][$value['fieldid']][] = $value;
	}
		
	//话题
	$netcache['threadlist'] = array();
	if(empty($network['thread'])) {
		$sql = ' FORCE INDEX(lastpost) ORDER BY lastpost DESC LIMIT 0,10';
	} else {
		$sql = ' '.trim($network['thread']);
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread').$sql);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$netcache['threadlist'][] = $value;
	}
	
	//写缓存操作
	if($writestate) {
		include_once(S_ROOT.'./source/function_cache.php');
		cache_write('network', "netcache", $netcache);
		@unlink($lockfile);
	}
}

if($netcache['mtaglist']) {
	@include_once(S_ROOT.'./data/data_profield.php');
}
?>