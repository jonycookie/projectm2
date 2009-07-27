<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_usergroups.php 11220 2009-02-26 02:56:05Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageusergroups')) {
	showmessage('no_authority_management_operation');
}

//取得单个数据
$thevalue = $list = array();
$_GET['groupid'] = empty($_GET['groupid'])?0:intval($_GET['groupid']);
if($_GET['groupid']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroups')." WHERE groupid='$_GET[groupid]'");
	if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('user_group_does_not_exist');
	}
}

if(submitcheck('thevaluesubmit')) {

	//用户组名
	$_POST['set']['grouptitle'] = shtmlspecialchars($_POST['set']['grouptitle']);
	if(empty($_POST['set']['grouptitle'])) showmessage('user_group_were_not_empty');
	$setarr = array('grouptitle' => $_POST['set']['grouptitle']);
	
	//详细权限
	$perms = array_keys($_POST['set']);
	$nones = array('groupid', 'grouptitle', 'system');
	foreach ($perms as $value) {
		if(!in_array($value, $nones)) {
			$_POST['set'][$value] = intval($_POST['set'][$value]);
			if($thevalue[$value] != $_POST['set'][$value]) {
				$setarr[$value] = $_POST['set'][$value];
			}
		}
	}

	if(empty($thevalue['groupid'])) {
		//添加
		inserttable('usergroups', $setarr);
	} else {
		//更新
		updatetable('usergroups', $setarr, array('groupid'=>$thevalue['groupid']));
	}
	
	//更新缓存
	include_once(S_ROOT.'./function/cache.func.php');
	updategroupcache();

	showmessage('do_success', S_URL.'/admincp.php?action=usergroups');
	
} elseif(submitcheck('copysubmit')) {
	
	//移除不需要复制的变量
	unset($thevalue['grouptitle']);
	unset($thevalue['groupid']);
	unset($thevalue['creditlower']);
	unset($thevalue['system']);
	$copyvalue = saddslashes($thevalue);
	foreach($_POST['aimgroup'] as $key => $value) {
		$groupid = intval($value);
		updatetable('usergroups', $copyvalue, array('groupid'=>$groupid));
	}
	
	//更新缓存
	include_once(S_ROOT.'./function/cache.func.php');
	updategroupcache();

	showmessage('do_success', S_URL.'/admincp.php?action=usergroups');
	
}

if(empty($_GET['op'])) {
	
	//浏览列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroups'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[$value['system']][] = $value;
	}
	
} elseif ($_GET['op'] == 'add') {

	//添加
	$thevalue = array('groupid' => 0);

} elseif ($_GET['op'] == 'copy') {
	
	//复制
	$from = $thevalue['grouptitle'];
	$groupid = $thevalue['groupid'];
	$thevalue = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroups')." WHERE groupid!='$groupid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$grouparr[] = $value;
	}

} elseif ($_GET['op'] == 'delete' && $thevalue) {

	//删除
	if($thevalue['system'] != '1') {
		//删除
		$_SGLOBAL['db']->query("DELETE FROM ".tname('usergroups')." WHERE groupid='$_GET[groupid]'");
	} else {
		showmessage('system_user_group_could_not_be_deleted');
	}

	//更新缓存
	include_once(S_ROOT.'./function/cache.func.php');
	updategroupcache();

	showmessage('do_success', S_URL.'/admincp.php?action=usergroups');
}

$output = '';
$s_url = S_URL;
if($_GET[op]=='copy') {
	$groupstr = '';
	if($grouparr) {
		foreach($grouparr as $key => $value) {
			$groupstr .= "<option value=\"$value[groupid]\">$value[grouptitle]</option>";
		}
	}

} elseif($_GET['op']=='add' || $_GET['op']=='edit') {

	foreach($thevalue as $key => $value) {
		if(strlen($value) == 1 && !in_array($key, array('groupid'))) $thevalue[$key] = empty($value) ? array(' checked','') : array('',' checked');
	}

}
include template('admin/tpl/usergroups.htm', 1);

?>