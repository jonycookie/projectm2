<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_mtag.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

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
	$gets['fieldid'] = empty($_GET['fieldid'])?0:intval($_GET['fieldid']);
	
	//搜索积分
	cksearchcredit($ac);
	
	//开始搜索
	$wherearr = array();
	if($gets['fieldid']) {
		$wherearr[] = "main.fieldid = '$gets[fieldid]'";
	}
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
				$is[] = "main.tagname LIKE '%$value%'";
			}
		}
		if($is) {
			$wherearr[] = '('.implode($andor, $is).')';
		}
	}
	if(empty($wherearr)) {
		showmessage('set_the_correct_search_content');
	}

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." main WHERE ".implode(' AND ', $wherearr)." ORDER BY main.membernum DESC LIMIT 0, 100");//最多100条
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$list[$value['fieldid']][] = $value;
	}
	
	//更新最后操作时间
	updatespacestatus('pay', 'search');
	
} else {
	//分页
	$perpage = 100;
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//检查开始数
	ckstart($start, $perpage);
	
	//处理查询
	$count = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." FORCE INDEX(membernum) ORDER BY membernum DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$list[$value['fieldid']][] = $value;
		$count++;
	}
	
	//分页
	$multi = empty($_SCONFIG['networkpage'])?array('html'=>'networkpage'):smulti($start, $perpage, $count, $theurl);
}

//选吧
$fieldids = array($gets['fieldid']=>' selected');

?>