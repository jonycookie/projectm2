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