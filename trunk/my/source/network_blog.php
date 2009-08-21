<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_blog.php 7046 2008-04-12 13:53:00Z liguode $
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
	$gets['username'] = empty($_GET['username'])?'':(stripsearchkey($_GET['username'])?$_GET['username']:'');
	$_GET['starttime'] = empty($_GET['starttime'])?'':sstrtotime($_GET['starttime']);
	if($_GET['starttime']) $gets['starttime'] = sgmdate('Y-m-d', $_GET['starttime']);
	$_GET['endtime'] = empty($_GET['endtime'])?'':sstrtotime($_GET['endtime']);
	if($_GET['endtime']) $gets['endtime'] = sgmdate('Y-m-d', $_GET['endtime']);
	if($gets['endtime'] && $gets['endtime'] <= $gets['starttime']) {
		$gets['starttime'] = $gets['endtime'] = '';
	}
	$gets['type'] = empty($_GET['type'])?'subject':(in_array($_GET['type'], array('subject', 'fulltext'))?$_GET['type']:'subject');
	$gets['orderby'] = empty($_GET['orderby'])?'dateline':(in_array($_GET['orderby'], array('dateline', 'lastpost', 'replynum', 'viewnum'))?$_GET['orderby']:'dateline');
	$gets['ascdesc'] = empty($_GET['ascdesc'])?'desc':(in_array($_GET['ascdesc'], array('asc', 'desc'))?$_GET['ascdesc']:'desc');
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
	//作者
	if($value = stripsearchkey($gets['username'])) {
		if(strexists($value, '%')) {
			$wherearr[] = "main.username LIKE '$value'";
		} else {
			$wherearr[] = "main.username = '$value'";
		}
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
				$is[] = ($gets['type']=='fulltext'?'mainfield.message':'main.subject')." LIKE '%$value%'";
			}
		}
		if($is) {
			if($gets['type'] == 'fulltext') {
				$wherearr[] = 'mainfield.blogid=main.blogid';
			}
			$wherearr[] = '('.implode($andor, $is).')';
		}
	}
	if(empty($wherearr)) {
		showmessage('set_the_correct_search_content');
	}
	
	if($gets['type'] == 'fulltext') {
		$sql = ", ".tname('blogfield')." mainfield WHERE ".implode(' AND ', $wherearr);
	} else {
		$sql = "WHERE ".implode(' AND ', $wherearr);
	}
	$sql = "SELECT main.blogid, main.uid, main.username, main.subject, main.dateline 
		FROM ".tname('blog')." main ".$sql." 
		ORDER BY main.{$gets['orderby']} $gets[ascdesc]";
	
	$query = $_SGLOBAL['db']->query($sql.' LIMIT 0, 100');//最多100条
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	
	//更新最后操作时间
	updatespacestatus('pay', 'search');

} else {
	//分页
	$perpage = 20;
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//检查开始数
	ckstart($start, $perpage);

	//处理查询
	$count = 0;
	$query = $_SGLOBAL['db']->query("SELECT b.blogid, b.subject, b.uid, b.username, b.dateline, b.viewnum, b.replynum, b.friend, bf.message 
		FROM ".tname('blog')." b
		LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid 
		WHERE b.friend='0'
		ORDER BY b.dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['message'] = getstr($value['message'], 100, 0, 0, 0, 0, -1);
		$list[] = $value;
		$count++;
	}
	
	//分页
	$multi = empty($_SCONFIG['networkpage'])?array('html'=>'networkpage'):smulti($start, $perpage, $count, $theurl);
	
}

//显示
$typearr = array($gets['type'] => ' checked');
$orderbyarr = array($gets['orderby'] => ' selected');
$ascdescarr = array($gets['ascdesc'] => ' selected');

?>