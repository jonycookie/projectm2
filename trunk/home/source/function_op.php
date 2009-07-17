<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_op.php 7976 2008-07-07 09:32:18Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//�ϲ�tag
function mergetag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;
	
	//���
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).") AND tagid <> '$newtagid'");

	$tagids[] = $newtagid;
	$tagids = array_unique($tagids);
	
	//���¹�����
	$blogids = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($blogids[$value['blogid']])) $blogids[$value['blogid']] = $value;
	}
	if(empty($blogids)) return true;
	
	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	//����
	$inserts = array();
	foreach ($blogids as $blogid => $value) {
		$inserts[]= "('$newtagid', '$blogid')";
	}
	$_SGLOBAL['db']->query("INSERT INTO ".tname('tagblog')." (tagid, blogid) VALUES ".implode(',', $inserts));
	//����ͳ��
	updatetable('tag', array('blognum'=>count($blogids)), array('tagid'=>$newtagid));
	
	return true;
}

//����/����tag
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

	//����״̬
	if($optype == 'close') {
		//����
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($newtagids).")");
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum='0', close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//�ϲ�mtag
function mergemtag($tagids, $newtagid) {
	global $_SGLOBAL;
	
	if(!checkperm('managemtag')) return false;
	
	//���
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).") AND tagid <> '$newtagid'");
	//���»���/�ظ�
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");
	$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET tagid='$newtagid' WHERE tagid IN (".simplode($tagids).")");

	$tagids[] = $newtagid;
	$tagids = array_unique($tagids);
	
	//���¹�����
	$uids = array();
	$query = $_SGLOBAL['db']->query("SELECT uid, username FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($uids[$value['uid']])) $uids[$value['uid']] = $value;
	}
	if(empty($uids)) return true;
	
	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid IN (".simplode($tagids).")");
	//����
	$inserts = array();
	foreach ($uids as $uid => $value) {
		$inserts[]= "('$newtagid', '$uid', '".addslashes($value['username'])."')";
	}
	$_SGLOBAL['db']->query("INSERT INTO ".tname('tagspace')." (tagid,uid,username) VALUES ".implode(',', $inserts));

	//����ͳ��
	updatetable('mtag', array('membernum'=>count($uids)), array('tagid'=>$newtagid));
	
	return true;
}


//����/����tag
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

	//����״̬
	if($optype == 'close') {
		//����
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='1' WHERE tagid IN (".simplode($newtagids).")");
	} else {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET close='0' WHERE tagid IN (".simplode($newtagids).")");
	}
	
	return true;
}

//���⾫��
function digestthreads($tagid, $tids, $v) {
	global $_SGLOBAL;
	
	$mtag = getmtag($tagid);
	if($mtag['grade']<8) {
		return array();
	}
	
	if(empty($v)) {
		$wheresql = " AND t.digest='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.digest='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$allowmanage = checkperm('managethread');
	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tagid='$tagid' AND t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtids[] = $value['tid'];
		$threads[] = $value;
	}
	
	//����
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET digest='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

//�����ö�
function topthreads($tagid, $tids, $v) {
	global $_SGLOBAL;
	
	$mtag = getmtag($tagid);
	if($mtag['grade']<8) {
		return array();
	}
	
	if(empty($v)) {
		$wheresql = " AND t.displayorder='1'";
		$v = 0;
	} else {
		$wheresql = " AND t.displayorder='0'";
		$v = 1;
	}
	$newtids = $threads = array();
	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tagid='$tagid' AND t.tid IN (".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newtids[] = $value['tid'];
		$threads[] = $value;
	}
	
	//����
	if($newtids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET displayorder='$v' WHERE tid IN (".simplode($newtids).")");
	}

	return $threads;
}

?>