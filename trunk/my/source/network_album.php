<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_album.php 7296 2008-05-06 06:39:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//初始化
$gets = $multi = $list = array();

if(!empty($_GET['searchmode'])) {
	//需要登录
	checklogin();
	
	//判断是否搜索太快
	$waittime = interval_check('search');
	if($waittime > 0) {
		showmessage('search_short_interval');
	}
	
	$gets['key'] = empty($_GET['key'])?'':(stripsearchkey($_GET['key'])?$_GET['key']:'');
	$_GET['starttime'] = empty($_GET['starttime'])?'':sstrtotime($_GET['starttime']);
	if($_GET['starttime']) $gets['starttime'] = sgmdate('Y-m-d', $_GET['starttime']);
	$_GET['endtime'] = empty($_GET['endtime'])?'':sstrtotime($_GET['endtime']);
	if($_GET['endtime']) $gets['endtime'] = sgmdate('Y-m-d', $_GET['endtime']);
	if($gets['endtime'] && $gets['endtime'] <= $gets['starttime']) {
		$gets['starttime'] = $gets['endtime'] = '';
	}
	
	//搜索积分
	cksearchcredit($ac);
	
	//开始搜索
	$wherearr = array();
	if($value = sstrtotime($gets['starttime'])) {
		$wherearr[] = "main.dateline >= '$value'";
	}
	if($value = sstrtotime($gets['endtime'])) {
		$wherearr[] = "main.dateline <= '$value'";
	}
	$wherearr[] = "main.friend = '0'";
	//关键字
	if($inkey = stripsearchkey($gets['key'])) {
		if(preg_match("/( AND |\+|&|\s)/i", $inkey) && !preg_match("/( OR |\|)/i", $inkey)) {
			$keys = preg_replace("/( AND |&| )/i", "+", $inkey);
			$andor = ' AND ';
		} else {
			$keys = preg_replace("/( OR |\|)/i", "+", $inkey);
			$andor = ' OR ';
		}
		$is = array();
		foreach (explode('+', $keys) as $value) {
			if($value = trim($value)) {
				$is[] = "main.albumname LIKE '%$value%'";
			}
		}
		if($is) {
			$wherearr[] = '('.implode($andor, $is).')';
		}
	}
	if(empty($wherearr)) {
		showmessage('set_the_correct_search_content');
	}

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." main WHERE ".implode(' AND ', $wherearr)." ORDER BY main.updatetime DESC LIMIT 0, 100");//最多100条
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['pic'] = mkpicurl($value);
		$list[] = $value;
	}
	
	//更新最后操作时间
	updatespacestatus('pay', 'search');
	
} else {
	//分页
	$perpage = 50;
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//检查开始数
	ckstart($start, $perpage);

	//处理查询
	$count = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE friend='0' ORDER BY updatetime DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['picnum']>0) {
			$value['pic'] = mkpicurl($value);
			$list[] = $value;
		}
		$count++;
	}
	
	//分页
	$multi = empty($_SCONFIG['networkpage'])?array('html'=>'networkpage'):smulti($start, $perpage, $count, $theurl);
}
	
?>