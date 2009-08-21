<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_feed.php 7370 2008-05-15 03:28:05Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//向导页面
if((!empty($_GET['view']) && $_GET['view'] == 'guide') || ($space['self'] && $space['updatetime'] == $space['dateline'])) {
	include_once(S_ROOT.'./source/space_guide.php');
	exit();
}

//分页
$perpage = $_SCONFIG['feedmaxnum']<50?50:$_SCONFIG['feedmaxnum'];
$start = empty($_GET['start'])?0:intval($_GET['start']);
//检查开始数
ckstart($start, $perpage);
	
//今天时间开始线
$_SGLOBAL['today'] = strtotime(date('Y-m-d'));

//处理查询
if($space['self'] && empty($space['friendnum']) && empty($_GET['view'])) {
	$_GET['view'] = 'all';
}
if($_GET['view'] == 'all') {
	$wheresql = "appid='".UC_APPID."' AND friend='0' AND icon IN ('doing','blog','album','thread','share')";
	$theurl = "space.php?uid=$space[uid]&do=$do&view=all";
	$f_index = '';
	$actives = array('all'=>' class="active"');
	$notime = 1;
} else {
	if(empty($space['feedfriend'])) {
		$wheresql = "uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$f_index = '';
		$actives = array('me'=>' class="active"');
	} else {
		$wheresql = "uid IN ($space[feedfriend])";
		$theurl = "space.php?uid=$space[uid]&do=$do";
		$f_index = 'FORCE INDEX(dateline)';
		$actives = array('we'=>' class="active"');
		$notime = 1;
	}

	$appid = empty($_GET['appid'])?0:intval($_GET['appid']);
	if($appid) {
		$wheresql .= " AND appid='$appid'";
		$theurl .= "&appid=$appid";
	}
}

$icon = empty($_GET['icon'])?'':trim($_GET['icon']);
if($icon) {
	$wheresql .= " AND icon='$icon'";
	$theurl .= "&icon=$icon";
}

$list = array();
$count = 0;
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." $f_index
	WHERE $wheresql
	ORDER BY dateline DESC
	LIMIT $start,$perpage");
if(empty($space['friend'])) {
	//个人动态
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			$value = mkfeed($value);
			if($value['dateline']>=$_SGLOBAL['today']) {
				$list['today'][] = $value;
			} elseif ($value['dateline']>=$_SGLOBAL['today']-3600*24) {
				$list['yesterday'][] = $value;
			} else {
				$theday = sgmdate('Y-m-d', $value['dateline']);
				$list[$theday][] = $value;
			}
		}
		$count++;
	}

	//分页
	$multi = smulti($start, $perpage, $count, $theurl);
} else {
	$flist = array();
	//好友动态
	$space['filter_icon'] = empty($space['privacy']['filter_icon'])?array():array_keys($space['privacy']['filter_icon']);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($flist[$value['hash_data']][$value['uid']])) {
			if(ckfriend($value) && ckicon_uid($value)) {
				$flist[$value['hash_data']][$value['uid']] = $value;
			}
		}
		$count++;
	}
	//重新整理
	$list = array();
	foreach ($flist as $values) {
		$actors = array();
		$a_value = array();
		foreach ($values as $value) {
			if(empty($a_value)) {
				$a_value = $value;
			}
			$actors[] = "<a href=\"space.php?uid=$value[uid]\">$value[username]</a>";
		}
		$a_value = mkfeed($a_value, $actors);
		if($a_value['dateline']>=$_SGLOBAL['today']) {
			$list['today'][] = $a_value;
		} elseif ($a_value['dateline']>=$_SGLOBAL['today']-3600*24) {
			$list['yesterday'][] = $a_value;
		} else {
			$theday = sgmdate('Y-m-d', $a_value['dateline']);
			$list[$theday][] = $a_value;
		}
	}
	//分页
	$multi = smulti($start, $perpage, $count, $theurl);
}

//好友请求
$addfriendlist = array();
if($space['self'] && empty($start)) {
	$query = $_SGLOBAL['db']->query("SELECT f.*, s.username FROM ".tname('friend')." f 
		LEFT JOIN ".tname('space')." s ON s.uid=f.uid 
		WHERE f.fuid='$space[uid]' AND f.status='0' LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$addfriendlist[] = $value;
	}
}

//侧边栏
//打招呼
$pokelist = array();
if($space['self'] && empty($start)) {
	$query = $_SGLOBAL['db']->query("SELECT fromuid AS uid, fromusername AS username, note FROM ".tname('poke')." WHERE uid='$space[uid]' LIMIT 0,20");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$pokelist[] = $value;
	}
}

//个人通知
$notices = array();
if($space['self'] && empty($start)) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' AND new='1'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$notices[$value['type']]++;
	}
}
if($notices) {
	$noticetypes = array(
		'wall' => mlang('wall'),
		'piccomment' => mlang('pic_comment'),
		'blogcomment' => mlang('blog_comment'),
		'sharecomment' => mlang('share_comment'),
		'sharenotice' => mlang('share_notice'),
		'doing' => mlang('doing_comment'),
		'friend' => mlang('friend_notice'),
		'post' => mlang('thread_comment')
	);
}

if(!$space['self'] || $space['friendnum']) {
	$shownotice_style = 'none';
} else {
	$shownotice_style = '';
}

include_once template("space_feed");

//筛选
function ckicon_uid($feed) {
	global $space;

	if(empty($space['filter_icon'])) return true;
	
	$key = $feed['icon'].'|0';
	if(in_array($key, $space['filter_icon'])) {
		return false;
	} else {
		$key = $feed['icon'].'|'.$feed['uid'];
		if(in_array($key, $space['filter_icon'])) {
			return false;
		}
	}
	return true;
}

?>