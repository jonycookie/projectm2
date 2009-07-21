<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_report.php 10123 2008-11-25 08:48:11Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managereport')) {
	cpmessage('no_authority_management_operation');
}

if (submitcheck('listsubmit')) {
	if($ac != 'report' && !in_array($_POST['optype'], array(1,2))) {
		$_POST['optype'] = 0;
	}
	if($_POST['ids'] && is_array($_POST['ids']) && $_POST['optype']) {
		$createlog = false;
		$url = "admincp.php?ac=$ac&perpage=$_GET[perpage]&page=$_GET[page]";

		if($_POST['optype'] == 1) {
			//忽略举报
			$_SGLOBAL['db']->query("UPDATE ".tname('report')." SET num='0' WHERE rid IN (".simplode($_POST['ids']).")");
			$createlog = true;
			
		} else {

			if($_POST['optype'] == 3) {
				deleteinfo($_POST['ids']);
			}
			//删除举报
			$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid IN (".simplode($_POST['ids']).")");
			$createlog = true;
		}
		cpmessage('do_success', $url);
	}
}

if($_GET['op'] == 'delete') {
	
	$rid = isset($_GET['rid'])?intval($_GET['rid']):0;
	if(!$rid) {
		cpmessage('the_right_to_report_the_specified_id', 'admincp.php?ac=report');
	}
	if($_GET['subop'] == 'delinfo') {
		deleteinfo(array($rid));
	}
	//删除举报
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid='$rid'");
	cpmessage('do_success', 'admincp.php?ac=report');
	
} elseif($_GET['op'] == 'ignore') {
	
	$rid = isset($_GET['rid'])?intval($_GET['rid']):0;
	if(!$rid) {
		cpmessage('the_right_to_report_the_specified_id', 'admincp.php?ac=report');
	}
	$_SGLOBAL['db']->query("UPDATE ".tname('report')." SET num='0' WHERE rid='$rid'");
	cpmessage('do_success', 'admincp.php?ac=report');
}

//处理搜索
$intkeys = array();
if(!isset($_GET['status']) || $_GET['status'] == 1) {
	$_GET['num1'] = 1;
	$_GET['status'] = 1;
} elseif($_GET['status'] == 0) {
	$_GET['num'] = 0;
	$intkeys = array('num');
}

$strkeys = array('idtype');
$randkeys = array(array('intval', 'num'));
$likekeys = array();
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];

$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//排序
$orders = getorders(array('dateline', 'num'), 'new,num DESC');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

$scstr = $_GET['ordersc'] == 'asc'? 'desc' : 'asc';
//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);

//显示分页
if($perpage > 100) {
	$count = 1;
	$selectsql = 'rid';
} else {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('report')." WHERE $wheresql"), 0);
	$selectsql = '*';
}
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$list = array();
$multi = '';

$reports = $users = array();
if($count) {
	$emptyids = $readids = array();
	$ids = $blogids = $picids = $albumids = $spaceids = $mtagids = $threadids = $shareids = array();
	$query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('report')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['user'] = unserialize($value['uids']);
		$emptyids[$value['idtype'].$value['id']] = $ids[] = $value['rid'];
		if($value['new']) {
			$readids[] = $value['rid'];
		}
		switch($value['idtype']) {
			case 'blog':
				$blogids[$value['id']] = $value['id'];
				$list['blog'][$value['id']] = $value;
				break;
			case 'picid':
				$picids[$value['id']] = $value['id'];
				$list['pic'][$value['id']] = $value;
				break;
			case 'album':
				$albumids[$value['id']] = $value['id'];
				$list['album'][$value['id']] = $value;
				break;
			case 'thread':
				$threadids[$value['id']] = $value['id'];
				$list['thread'][$value['id']] = $value;
				break;
			case 'mtag':
				$mtagids[$value['id']] = $value['id'];
				$list['mtag'][$value['id']] = $value;
				break;
			case 'share':
				$shareids[$value['id']] = $value['id'];
				$list['share'][$value['id']] = $value;
				break;
			case 'space':
				$spaceids[$value['id']] = $value['id'];
				$list['space'][$value['id']] = $value;
				break;
		}
	}
	
	if($readids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('report')." SET new='0' WHERE rid IN(".implode(',', $readids).")");
	}

	//取出相关信息
	//日志
	if($blogids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE blogid IN (".simplode($blogids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list['blog'][$value['blogid']]['info'] = $value;
			unset($emptyids['blog'.$value['blogid']]);
		}
	}
	//图片
	if($picids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid IN (".simplode($picids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['pic'] = mkpicurl($value);
			$list['pic'][$value['picid']]['info'] = $value;
			unset($emptyids['picid'.$value['picid']]);
		}
	}
	//相册
	if($albumids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid IN (".simplode($albumids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['pic'] = mkpicurl($value);
			$list['album'][$value['albumid']]['info'] = $value;
			unset($emptyids['album'.$value['albumid']]);
		}
	}
	
	//话题
	if($threadids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid IN (".simplode($threadids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list['thread'][$value['tid']]['info'] = $value;
			unset($emptyids['thread'.$value['tid']]);
		}
	}
	
	//群组
	if($mtagids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid IN (".simplode($mtagids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list['mtag'][$value['tagid']]['info'] = $value;
			unset($emptyids['mtag'.$value['tagid']]);
		}
	}
	
	//分享
	if($shareids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid IN (".simplode($shareids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value = mkshare($value);
			$list['share'][$value['sid']]['info'] = $value;
			unset($emptyids['share'.$value['sid']]);
		}
	}
	//空间
	if($spaceids) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid IN (".simplode($spaceids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list['space'][$value['uid']]['info'] = $value;
			unset($emptyids['space'.$value['uid']]);
		}
	}
	$multi = multi($count, $perpage, $page, $mpurl);
	//删除由删除空间引起的冗余数据
	if($emptyids) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE rid IN (".simplode($emptyids).")");
	}
	
}

//显示分页
if($perpage > 100) {
	$count = count($list);
}

function deleteinfo($ids) {
	global $_SGLOBAL;
	
	include_once(S_ROOT.'./source/function_delete.php');
	$deltype = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('report')." WHERE rid IN (".simplode($ids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$deltype[$value['idtype']][] = $value['id'];
	}
	$gid = getgroupid($_SGLOBAL['member']['credit'], $_SGLOBAL['member']['groupid']);
	//执行相应的删除操作
	foreach($deltype as $key => $value) {
		switch($key) {
			case 'blog':
				$_SGLOBAL['usergroup'][$gid]['manageblog'] = 1;
				deleteblogs($value);
				break;
			case 'picid':
				$_SGLOBAL['usergroup'][$gid]['managealbum'] = 1;
				deletepics($value);
				break;
			case 'album':
				$_SGLOBAL['usergroup'][$gid]['managealbum'] = 1;
				deletealbums($value);
				break;
			case 'thread':
				$_SGLOBAL['usergroup'][$gid]['managethread'] = 1;
				deletethreads(0, $value);
				break;
			case 'mtag':
				$_SGLOBAL['usergroup'][$gid]['managemtag'] = 1;
				deletemtag($value);
				break;
			case 'share':
				$_SGLOBAL['usergroup'][$gid]['manageshare'] = 1;
				deleteshares($value);
				break;
			case 'space':
				$_SGLOBAL['usergroup'][$gid]['managespace'] = 1;
				foreach($value as $uid) {
					deletespace($uid);
				}
				break;
		}
	}
}
?>