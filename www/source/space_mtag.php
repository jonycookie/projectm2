<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_mtag.php 10777 2008-12-22 06:44:14Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);

//处理查询
if($id) {
	$perpage = 20;
	$start = ($page-1)*$perpage;
	
	//检查开始数
	ckstart($start, $perpage);
	
	//栏目
	$list = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('mtag')." WHERE fieldid='$id'"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE fieldid='$id' ORDER BY membernum DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(empty($value['pic'])) {
				$value['pic'] = 'image/nologo.jpg';
			}
			$list[] = $value;
		}
	}
	
	//分页
	$multi = multi($count, $perpage, $page, "space.php?uid=$space[uid]&do=mtag&id=$id");

	$fieldtitle = $_SGLOBAL['profield'][$id]['title'];
	
	$sub_actives = array($id => ' class="active"');
	$fieldids = array($id => ' selected');

	include_once template("space_mtag_field");

} elseif($tagid) {

	$actives = array($_GET['view'] => ' class="active"');
	
	//指定的群组
	$mtag = getmtag($tagid);
	
	if($_GET['view'] == 'list' || $_GET['view'] == 'digest') {
		
		$perpage = 30;
		$start = ($page-1)*$perpage;
		
		//检查开始数
		ckstart($start, $perpage);

		$wheresql = ($_GET['view'] == 'list')?'':" AND main.digest='1'";
		
		$list = array();
		$count = 0;
		
		if($mtag['allowview']) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." main WHERE main.tagid='$tagid' $wheresql"),0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
					WHERE main.tagid='$tagid' $wheresql
					ORDER BY main.displayorder DESC, main.lastpost DESC 
					LIMIT $start,$perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);
					realname_set($value['lastauthorid'], $value['lastauthor']);
					$list[] = $value;
				}
			}
			//分页
			$multi = multi($count, $perpage, $page, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=list");
	
			realname_get();
		}
		
		include_once template("space_mtag_list");
		
	} elseif($_GET['view'] == 'member') {
		
		$perpage = 50;
		$start = ($page-1)*$perpage;
		
		//检查开始数
		ckstart($start, $perpage);
		
		//检索
		$wheresql = '';
		$_GET['key'] = stripsearchkey($_GET['key']);
		if($_GET['key']) {
			$wheresql = " AND main.username LIKE '%$_GET[key]%' ";
		}

		
		$list = $fuids = array();
		$count = 0;
		
		if($mtag['allowview']) {
			$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." main WHERE main.tagid='$tagid' $wheresql"),0);
			if($count) {
				$query = $_SGLOBAL['db']->query("SELECT field.*, main.username, main.grade FROM ".tname('tagspace')." main 
					LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid 
					WHERE main.tagid='$tagid' $wheresql ORDER BY main.grade DESC LIMIT $start,$perpage");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					//实名
					realname_set($value['uid'], $value['username']);
					
					$value['p'] = rawurlencode($value['resideprovince']);
					$value['c'] = rawurlencode($value['residecity']);
					$fuids[] = $value['uid'];
					$list[] = $value;
				}
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
			$multi = multi($count, $perpage, $page, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=member");
			
			//实名
			realname_get();
		}
		
		include_once template("space_mtag_member");
	
	} else {

		//群组首页
		$list = $starlist = $modlist = $memberlist = $checklist = array();
		
		if($mtag['allowview']) {
			$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
				WHERE main.tagid='$tagid' 
				ORDER BY main.displayorder DESC, main.lastpost DESC 
				LIMIT 0,50");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				realname_set($value['lastauthorid'], $value['lastauthor']);
				$list[] = $value;
			}
			
			//明星会员
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='1'");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$starlist[] = $value;
			}
			$starlist = sarray_rand($starlist, 12);//随机选择
								
			//会员
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='0' LIMIT 0,12");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$memberlist[] = $value;
			}
		}
		//群主
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade>'7' ORDER BY grade DESC LIMIT 0,12");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$modlist[] = $value;
		}
		//是群主
		if($mtag['grade']>=8) {
			//待审
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND grade='-2' LIMIT 0,12");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				$checklist[] = $value;
			}
		}
		
		realname_get();
		
		include_once template("space_mtag_index");
	}

} else {

	$perpage = 20;
	$start = ($page-1)*$perpage;
	
	//检查开始数
	ckstart($start, $perpage);

	$theurl = "space.php?uid=$space[uid]&do=mtag";
	$actives = array('me' => ' class="active"');

	$list = $tagids = array();
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." main WHERE main.uid='$space[uid]'"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT main.*,field.* FROM ".tname('tagspace')." main 
			LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid 
			WHERE main.uid='$space[uid]' ORDER BY main.grade DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			if(empty($value['pic'])) {
				$value['pic'] = 'image/nologo.jpg';
			}
			if($value['grade']>-2) {
				$tagids[$value['tagid']] = $value['tagid'];//已经批准的群组
			}
			$list[] = $value;
		}
	}

	//分页
	$multi = multi($count, $perpage, $page, $theurl);
	
	//最新话题
	$threadlist = array();
	if($tagids) {
		$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid FROM ".tname('thread')." main
			LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid
			WHERE main.tagid IN (".simplode($tagids).")
			ORDER BY main.lastpost DESC
			LIMIT 0,10");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$threadlist[] = $value;
		}
	}

	realname_get();
	
	include_once template("space_mtag");
}

?>