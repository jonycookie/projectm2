<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_pm.php 7253 2008-05-04 00:52:14Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./uc_client/client.php');

$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);

if($pmid) {
	
	$list = uc_pm_view($_SGLOBAL['supe_uid'], $pmid);

	$msgfromid = $list[0]['msgfromid'];
	$msgtoid = $list[0]['msgtoid'];

} else {
	$view = (!empty($_GET['view']) && in_array($_GET['view'], array('inbox', 'outbox', 'newbox', 'announce', 'ignore')))?$_GET['view']:'newbox';
	$actives = array($view=>' class="active"');
		
	if($_GET['view'] == 'ignore') {
		$ignorelist = uc_pm_blackls_get($_SGLOBAL['supe_uid']);
	} else {
		//分页
		$perpage = 10;
		$page = empty($_GET['page'])?0:intval($_GET['page']);
		if($page<1) $page = 1;
		$start = ($page-1)*$perpage;
		
		if($view == 'announce') {
			$result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, 'inbox', 'announcepm', 100);
		} else {
			$result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, $view, '', 100);
		}
		
		$count = $result['count'];
		$list = $result['data'];
	
		$multi = array();
		if($view != 'newbox') {
			$multi['html'] = multi($count, $perpage, $page, "space.php?do=pm&view=$view");
		}
		
		if($_SGLOBAL['member']['newpm']) {
			//取消新短消息提示
			updatetable('session', array('newpm'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
			//UCenter
			uc_pm_ignore($_SGLOBAL['supe_uid']);
		}
	}
}

include_once template("space_pm");

?>