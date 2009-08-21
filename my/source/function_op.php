<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_op.php 7312 2008-05-07 01:47:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//合并tag
function mergetag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	//清空
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND tagid <> '$newtagid'");

	$tagids[] = $newtagid;
	$tagids = array_unique($tagids);
	
	//更新关联表
	$blogids = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($blogids[$value['blogid']])) $blogids[$value['blogid']] = $value;
	}
	if(empty($blogids)) return true;
	
	//关联
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	//插入
	$inserts = array();
	foreach ($blogids as $blogid => $value) {
		$inserts[]= "('$newtagid', '$blogid')";
	}
	$_SGLOBAL['db']->query("INSERT INTO ".tname('tagblog')." (tagid, blogid) VALUES ".implode(',', $inserts));
	//更新统计
	updatetable('tag', array('blognum'=>count($blogids)), array('tagid'=>$newtagid));
	
	return true;
}

//锁定/开放tag
function closetag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	$newtagids = array();
	if($optype == 'close') {
		$close = 0;
	} else {
		$close = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND close='$close'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'close') {
		//关联
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($newtagids).")");
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum='0', close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//合并mtag
function mergemtag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	//清空
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND tagid <> '$newtagid'");
	//更新话题/回复
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");
	$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");

	$tagids[] = $newtagid;
	$tagids = array_unique($tagids);
	
	//更新关联表
	$uids = array();
	$query = $_SGLOBAL['db']->query("SELECT uid, username FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($uids[$value['uid']])) $uids[$value['uid']] = $value;
	}
	if(empty($uids)) return true;
	
	//关联
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	//插入
	$inserts = array();
	foreach ($uids as $uid => $value) {
		$inserts[]= "('$newtagid', '$uid', '".addslashes($value['username'])."')";
	}
	$_SGLOBAL['db']->query("INSERT INTO ".tname('tagspace')." (tagid,uid,username) VALUES ".implode(',', $inserts));

	//更新统计
	updatetable('mtag', array('membernum'=>count($uids)), array('tagid'=>$newtagid));
	
	return true;
}


//锁定/开放tag
function closemtag($tagids, $optype) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	$newtagids = array();
	if($optype == 'close') {
		$close = 0;
	} else {
		$close = 1;
	}
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND close='$close'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtagids[] = $value['tagid'];
	}
	if(empty($newtagids)) return false;

	//更新状态
	if($optype == 'close') {
		//关联
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//话题精华
function digestthreads($tids, $v) {
	global $_SGLOBAL;
	
	if(empty($v)) {
		$wheresql = " AND t.digest='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.digest='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$allowmanage = checkperm('managethread');
	$query = $_SGLOBAL['db']->query("SELECT t.*, mt.moderator FROM ".tname('thread')." t LEFT JOIN ".tname('mtag')." mt ON mt.tagid=t.tagid WHERE t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || ismoderator($value['moderator'])) {
			$newtids[] = $value['tid'];
			$threads[] = $value;
		}
	}
	
	//数据
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET digest='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

//话题置顶
function topthreads($tids, $v) {
	global $_SGLOBAL;
	
	if(empty($v)) {
		$wheresql = " AND t.displayorder='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.displayorder='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$allowmanage = checkperm('managethread');
	$query = $_SGLOBAL['db']->query("SELECT t.*, mt.moderator FROM ".tname('thread')." t LEFT JOIN ".tname('mtag')." mt ON mt.tagid=t.tagid WHERE t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || ismoderator($value['moderator'])) {
			$newtids[] = $value['tid'];
			$threads[] = $value;
		}
	}
	
	//数据
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET displayorder='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

?>