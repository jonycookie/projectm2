<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: getfeed.php 6970 2008-04-07 06:46:12Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//从uc获取feed
include_once(S_ROOT.'./uc_client/client.php');
if($results = uc_feed_get(50)) {//每次取50个

	$cols = array('uid','username','appid','icon','dateline','hash_template','hash_data','title_template','title_data','body_template','body_data','body_general','image_1','image_1_link','image_2','image_2_link','image_3','image_3_link','image_4','image_4_link','target_ids');
	
	$inserts = $apps = $appkeys = $uid_appids = array();
	foreach ($results as $value) {
		if(empty($value['uid']) || empty($value['username'])) continue;
		
		$vs = array();
		foreach ($cols as $key) {
			if(is_array($value[$key])) {
				//数组处理
				$value[$key] = addslashes(serialize(sstripslashes($value[$key])));
			} else {
				$value[$key] = addslashes($value[$key]);
			}
			$vs[] = '\''.$value[$key].'\'';
		}
		$inserts[] = '('.implode(',', $vs).')';
		
		//应用活动次数统计
		if(empty($apps[$value['uid']][$value['appid']])) $apps[$value['uid']][$value['appid']] = 0;
		$apps[$value['uid']][$value['appid']]++;
		
		$appkeys["$value[uid]-$value[appid]"] = "(uid='$value[uid]' AND appid='$value[appid]')";
		$uid_appids["$value[uid]-$value[appid]"] = array('uid'=>$value['uid'], 'appid'=>$value['appid']);
	}
	//入库
	if($inserts) {
		$_SGLOBAL['db']->query("INSERT INTO ".tname('feed')." (".implode(',', $cols).") VALUES ".implode(',', $inserts));
	}
	
	//处理用户活动app
	if($appkeys) {
		$replaces = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('app')." WHERE ".implode(' OR ', $appkeys));
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$num = intval($apps[$value['uid']][$value['appid']]) + $value['num'];
			$replaces[] = "('$value[uid]', '$value[appid]', '$num', '$_SGLOBAL[timestamp]')";
			unset($uid_appids["$value[uid]-$value[appid]"]);
		}
		//新添加
		if($uid_appids) {
			foreach ($uid_appids as $value) {
				$num = intval($apps[$value['uid']][$value['appid']]);
				$replaces[] = "('$value[uid]', '$value[appid]', '$num', '$_SGLOBAL[timestamp]')";
			}
		}
		if($replaces) {
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('app')." (uid,appid,num,updatetime) VALUES ".implode(',', $replaces));
		}
	}
}

?>