<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_mtag.php 7296 2008-05-06 06:39:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

$start = empty($_GET['start'])?0:intval($_GET['start']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);

//处理查询
if($id) {
	$perpage = 50;
	//检查开始数
	ckstart($start, $perpage);
	
	//栏目
	$list = array();
	$count = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE fieldid='$id' ORDER BY membernum DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$list[] = $value;
		$count++;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, "space.php?uid=$space[uid]&do=mtag&id=$id");

	$fieldtitle = $_SGLOBAL['profield'][$id]['title'];

	include_once template("space_mtag_field");

} elseif($tagid) {

	$actives = array($_GET['view'] => ' class="active"');
	
	//指定的选吧
	include_once(S_ROOT.'./source/function_space.php');
	$mtag = getmtag($tagid, 1);
	
	if($_GET['view'] == 'list' || $_GET['view'] == 'digest') {
		
		$perpage = 30;
		//检查开始数
		ckstart($start, $perpage);

		$wheresql = ($_GET['view'] == 'list')?'':" AND main.digest='1'";
		
		$list = array();
		$count = 0;
		$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
			WHERE main.tagid='$tagid' $wheresql
			ORDER BY main.displayorder DESC, main.lastpost DESC 
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
			$count++;
		}
		
		//分页
		$multi = smulti($start, $perpage, $count, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=list");

		include_once template("space_mtag_list");
		
	} elseif($_GET['view'] == 'member') {
		
		$perpage = 50;
		//检查开始数
		ckstart($start, $perpage);
		
		$list = $fuids = array();
		$count = 0;
		$query = $_SGLOBAL['db']->query("SELECT field.*, main.username FROM ".tname('tagspace')." main 
			LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid 
			WHERE main.tagid='$tagid' LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$fuids[] = $value['uid'];
			$list[] = $value;
			$count++;
		}
		
		//在线状态
		$ols = array();
		if($fuids) {
			$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$ols[$value['uid']] = $value['lastactivity'];
			}
		}

		//分页
		$multi = smulti($start, $perpage, $count, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=member");
		
		include_once template("space_mtag_member");
	
	} else {

		//列表
		$list = array();
		$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
			WHERE main.tagid='$tagid' 
			ORDER BY main.displayorder DESC, main.lastpost DESC 
			LIMIT 0,30");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		
		//会员
		$memberlist = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' LIMIT 0,12");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$memberlist[] = $value;
		}
		
		$musers = empty($mtag['moderator'])?'':explode("\t", $mtag['moderator']);
	
		include_once template("space_mtag_index");
	}

} else {

	$perpage = 50;
	//检查开始数
	ckstart($start, $perpage);

	$query = $_SGLOBAL['db']->query("SELECT main.*,field.* FROM ".tname('tagspace')." main 
		LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid 
		WHERE main.uid='$space[uid]' LIMIT $start,$perpage");
	$theurl = "space.php?uid=$space[uid]&do=mtag";
	$actives = array('me' => ' class="active"');

	$list = array();
	$count = 0;
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$list[$value['fieldid']][] = $value;
		$count++;
	}

	//分页
	$multi = smulti($start, $perpage, $count, $theurl);

	include_once template("space_mtag");
}

?>