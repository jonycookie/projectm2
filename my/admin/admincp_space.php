<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_space.php 7293 2008-05-06 01:49:26Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managespace')) {
	cpmessage('no_authority_management_operation');
}

include_once(S_ROOT.'./data/data_usergroup.php');

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$result = '';
if($uid) {

	$query = $_SGLOBAL['db']->query("SELECT s.* FROM ".tname('space')." s WHERE s.uid='$uid'");
	if(!$member = $_SGLOBAL['db']->fetch_array($query)) {
		cpmessage('designated_users_do_not_exist');
	}
	$member['addsize'] = intval($member['addsize']/(1024*1024));
}
if($uid != $_SGLOBAL['supe_uid']) {
	//创始人
	if(ckfounder($uid)) {
		cpmessage('not_have_permission_to_operate_founder');
	}
}

if(submitcheck('usergroupsubmit')) {
	
	$setarr = array('credit'=>intval($_POST['credit']), 'addsize'=>intval($_POST['addsize'])*1024*1024);
	$setarr2 = array('credit'=>intval($_POST['credit']));
	
	if($uid != $_SGLOBAL['supe_uid'] || ckfounder($_SGLOBAL['supe_uid'])) {
		if(empty($_POST['groupid'])) {
			$_POST['groupid'] = getgroupid($_POST['credit'], 0);
		}
		$setarr['groupid'] = intval($_POST['groupid']);
		$setarr2['groupid'] = intval($_POST['groupid']);
	}
	
	//积分、用户组
	updatetable('space', $setarr, array('uid'=>$uid));
	updatetable('session', $setarr2, array('uid'=>$uid));
	
	cpmessage('do_success', $_POST['refer']);

}elseif(submitcheck('checksubmit')) {
	if(is_array($_POST['uids']) && !empty($_POST['uids'])) {
		@include_once(S_ROOT.'./uc_client/client.php');
		$_POST['flag'] = intval($_POST['flag']);
		if($_POST['flag'] == 1) {
			$result = uc_user_addprotected($_POST['uids'], $_SGLOBAL['username']);
		} else {
			$result = uc_user_deleteprotected($_POST['uids'], $_SGLOBAL['username']);
		}
		if($result == 1) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET flag='$_POST[flag]' WHERE username IN ('".implode('\',\'', $_POST['uids'])."')");
		} else {
			cpmessage('uc_error', 'admincp.php?ac=space');
		}
	}

	cpmessage('do_success', 'admincp.php?ac=space');

}

if($_GET['op'] == 'delete') {
	include_once(S_ROOT.'./source/function_delete.php');
	$_GET['uid'] = intval($_GET['uid']);
	if(!empty($_GET['uid']) && deletespace($_GET['uid'])) {
		cpmessage('do_success', 'admincp.php?ac=space');
	} else {
		cpmessage('choose_to_delete_the_space', 'admincp.php?ac=space');
	}
} elseif($_GET['op'] == 'usergroup') {

	$groupidarr = array($member['groupid'] => ' selected');
	include template('admin/tpl/space_usergroup');
	exit();
}

$mpurl = 'admincp.php?ac=space';

//处理搜索
$intkeys = array('uid', 'groupid');
$strkeys = array('username');
$randkeys = array(array('sstrtotime','dateline'), array('sstrtotime','updatetime'), array('intval','credit'));
$likekeys = array('spacename');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 's.');
$wherearr = $results['wherearr'];
$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('dateline', 'updatetime', 'friendnum', 'credit'), '', 's.');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100))) $perpage = 20;
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);

$list = array();
$multi = '';

$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('space')." s WHERE $wheresql"), 0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT s.* FROM ".tname('space')." s WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['grouptitle'] = $_SGLOBAL['usergroup'][$value['groupid']]['grouptitle'];
		$value['addsize'] = formatsize($value['addsize']);
		$list[] = $value;
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

?>