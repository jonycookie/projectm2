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
	$perpage = 30;

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
	$multi['html'] = multi($count, $perpage, $page, "space.php?uid=$thread[uid]&do=$do&id=$id");
	
	//访问统计
	inserttable('log', array('id'=>$id, 'idtype'=>'tid'));

	include_once template("space_thread_view");
	
} else {
	
	$perpage = 30;
	//检查开始数
	ckstart($start, $perpage);
	
	//话题列表
	$wheresql = '';
	if(empty($_GET['view'])) {
		//我加入的选吧
		$tagids = array();
		$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('tagspace')." WHERE uid='$space[uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$tagids[$value['tagid']] = $value['tagid'];
		}
		if($tagids) {
			//加入的选吧
			$wheresql = "main.tagid IN (".simplode($tagids).")";
			$theurl = "space.php?uid=$space[uid]&do=$do";
			$f_index = 'FORCE INDEX(lastpost)';
			
		}
		$actives = array('we'=>' class="active"');
	} else {
		//自己的
		$wheresql = "main.uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$f_index = '';
		$actives = array('me'=>' class="active"');
	}
	
	$list = array();
	$count = 0;
	
	if($wheresql) {
		$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid FROM ".tname('thread')." main $f_index
			LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid WHERE $wheresql 
			ORDER BY main.lastpost DESC 
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['tagname'] = getstr($value['tagname'], 20);
			$list[] = $value;
			$count++;
		}
	}

	//分页
	$multi = smulti($start, $perpage, $count, $theurl);
	
	include_once template("space_thread_list");
}


?>