<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_usergroup.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageusergroup')) {
	cpmessage('no_authority_management_operation');
}

//取得单个数据
$thevalue = $list = array();
$_GET['gid'] = empty($_GET['gid'])?0:intval($_GET['gid']);
if($_GET['gid']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup')." WHERE gid='$_GET[gid]'");
	if(!$thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		cpmessage('user_group_does_not_exist');
	}
}

if(submitcheck('thevaluesubmit')) {

	//用户组名
	$_POST['set']['grouptitle'] = shtmlspecialchars($_POST['set']['grouptitle']);
	if(empty($_POST['set']['grouptitle'])) cpmessage('user_group_were_not_empty');
	$setarr = array('grouptitle' => $_POST['set']['grouptitle']);

	//系统
	if(isset($thevalue['system'])) {
		$_POST['set']['system'] = $thevalue['system'];
	} else {
		$_POST['set']['system'] = intval($_POST['set']['system']);
	}
	if(empty($_POST['set']['system'])) {
		//普通用户组
		$_POST['set']['creditlower'] = empty($_POST['set']['creditlower'])?0:intval($_POST['set']['creditlower']);
		if($_POST['set']['creditlower'] > 999999999 || $_POST['set']['creditlower'] < -999999999) cpmessage('integral_limit_error');
		$lowgid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." where creditlower = '{$_POST['set']['creditlower']}'  AND system='0'"), 0);
		if(!empty($lowgid) && $lowgid != $_GET['gid']) {
			cpmessage('integral_limit_duplication_with_other_user_group');
		} 
		$setarr['creditlower'] = $_POST['set']['creditlower'];
	} else {
		//系统用户组
		$setarr['system'] = 1;
	}
	if($thevalue['system'] == '-1') {
		$setarr['system'] = -1;
	}
	
	//详细权限
	$perms = array_keys($_POST['set']);
	$nones = array('gid', 'grouptitle', 'system', 'creditlower');
	foreach ($perms as $value) {
		if(!in_array($value, $nones)) {
			$_POST['set'][$value] = intval($_POST['set'][$value]);
			if($thevalue[$value] != $_POST['set'][$value]) {
				$setarr[$value] = $_POST['set'][$value];
			}
		}
	}
	
	if(empty($thevalue['gid'])) {
		//添加
		inserttable('usergroup', $setarr);
	} else {
		//更新
		updatetable('usergroup', $setarr, array('gid'=>$thevalue['gid']));
	}
	
	groupcredit_update();

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	usergroup_cache();

	cpmessage('do_success', 'admincp.php?ac=usergroup');
} elseif (submitcheck('updatesubmit')) {
	//判断是否有下限重复
	if(count($_POST['creditlower']) != count(array_unique($_POST['creditlower']))) {
		cpmessage('integral_limit_duplication_with_other_user_group');
	} else {
		if(!empty($_POST['creditlower'])) {
			$oldcreditlower = array();
			$query = $_SGLOBAL['db']->query("SELECT gid, creditlower FROM ".tname('usergroup'));
			while($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
				$oldcreditlower[$thevalue['gid']] = $thevalue['creditlower'];
			}
			foreach($_POST['creditlower'] as $gidkey=>$gidvalue) {
				//与原来的用户组积分比较，是否有更新
				if($gidvalue == $oldcreditlower[$gidkey]) {
					continue;
				} else {
					if($gidvalue > 999999999 || $gidvalue < -999999999) cpmessage('integral_limit_error');
					$_SGLOBAL['db']->query("UPDATE ".tname('usergroup')." SET creditlower = '$gidvalue' WHERE gid='$gidkey'");
				}
			}
		}
		cpmessage('do_success', 'admincp.php?ac=usergroup');
	}
}

if(empty($_GET['op'])) {
	
	//浏览列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('usergroup')." ORDER BY creditlower");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[$value['system']][] = $value;
	}

} elseif ($_GET['op'] == 'add') {

	//添加
	$thevalue = array('gid' => 0, 'creditlower'=>0, 'maxattachsize'=>'10', 'maxfriendnum'=>50, 'postinterval'=>60, 'searchinterval'=>60, 'domainlength'=>0);

} elseif ($_GET['op'] == 'edit') {
	//编辑

} elseif ($_GET['op'] == 'delete' && $thevalue) {

	//删除
	if(empty($thevalue['system'])) {

		//删除
		$_SGLOBAL['db']->query("DELETE FROM ".tname('usergroup')." WHERE gid='$_GET[gid]'");

		groupcredit_update();
		
	} elseif($thevalue['system'] == '1') {
		//删除
		$_SGLOBAL['db']->query("DELETE FROM ".tname('usergroup')." WHERE gid='$_GET[gid]'");
	} else {
		cpmessage('system_user_group_could_not_be_deleted');
	}

	//更新用户权限
	updatetable('space', array('groupid'=>0), array('groupid'=>$_GET['gid']));

	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	usergroup_cache();

	cpmessage('do_success', 'admincp.php?ac=usergroup');
}

function groupcredit_update() {
	global $_SGLOBAL;
	
	//起始为-999999999
	$lowergid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." WHERE system='0' ORDER BY creditlower LIMIT 1"), 0);
	if($lowergid) updatetable('usergroup', array('creditlower'=>'-999999999'), array('gid'=>$lowergid));

}

?>