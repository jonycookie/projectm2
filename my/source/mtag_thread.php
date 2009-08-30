<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_thread.php 6968 2008-04-03 10:16:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

//分页
$start = empty($_GET['start'])?0:intval($_GET['start']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);

if($id) {
	//话题
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$id' LIMIT 1");
	if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('topic_does_not_exist');
	}
	
	//选吧信息
	$tagid = $thread['tagid'];
	
	include_once(S_ROOT.'./source/function_space.php');
	$mtag = getmtag($tagid, 1);
	
	//帖子列表
	$perpage = 10;

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page < 1) $page = 1;
	$start = ($page-1)*$perpage;
	
	$count = $thread['replynum'];
	
	//检查开始数
	ckstart($start, $perpage);
	
	$pid = empty($_GET['pid'])?0:intval($_GET['pid']);
	$psql = $pid?"(isthread='1' OR pid='$pid') AND":'';
		
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE $psql tid='$thread[tid]' ORDER BY dateline LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	
	//取得内容
	if($list[0]['isthread']) {
		$thread['content'] = $list[0];
		unset($list[0]);
	} else {
		$thread['content'] = array();
	}
	
	//分页
	$multi = array();
	$multi['html'] = multi($count, $perpage, $page, "mtag.php?tagid=$tagid&do=thread&id=$id");
	
	//访问统计
	inserttable('log', array('id'=>$id, 'idtype'=>'tid'));

	include_once template("mtag_thread_view");
	
} else {

		//指定的选吧
		include_once(S_ROOT.'./source/function_space.php');
		$mtag = getmtag($tagid, 1);
		
		$perpage = 30;
		//检查开始数
		ckstart($start, $perpage);
		
		$list = array();
		$count = 0;
		$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
			WHERE main.tagid='$tagid' 
			ORDER BY main.displayorder DESC, main.lastpost DESC 
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
			$count++;
		}
		
		//分页
		$multi = smulti($start, $perpage, $count, "mtag.php?tagid=$tagid&do=thread");

		include_once template("mtag_thread_list");
}


?>