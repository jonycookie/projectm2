<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_doing.php 7293 2008-05-06 01:49:26Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//Ȩ��
if(!$allowmanage = checkperm('managedoing')) {
	$_GET['uid'] = $_SGLOBAL['supe_uid'];//ֻ�ܲ������˵�
	$_GET['username'] = '';
}

if(submitcheck('deletesubmit')) {
	include_once(S_ROOT.'./source/function_delete.php');
	if(!empty($_POST['ids']) && deletedoings($_POST['ids'])) {
		cpmessage('do_success', $_POST['mpurl']);
	} else {
		cpmessage('choose_to_delete_events', $_POST['mpurl']);
	}
}

$mpurl = 'admincp.php?ac=doing';

//��������
$intkeys = array('uid');
$strkeys = array('ip');
$randkeys = array(array('sstrtotime','dateline'));
$likekeys = array('message');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
$wherearr = $results['wherearr'];
$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

//����
$orders = getorders(array('dateline'), 'doid DESC');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100,1000))) $perpage = 20;

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//��鿪ʼ��
ckstart($start, $perpage);

if($perpage > 100) {
	$count = 1;
	$selectsql = 'doid';
} else {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('doing')." WHERE $wheresql"), 0);
	$selectsql = '*';
}
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$list = array();
$multi = '';

if($count) {
	$query = $_SGLOBAL['db']->query("SELECT $selectsql FROM ".tname('doing')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

if($perpage > 100) {
	$count = count($list);
}

?>