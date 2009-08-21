<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_notice.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 100;
$start = empty($_GET['start'])?0:intval($_GET['start']);
//检查开始数
ckstart($start, $perpage);

//通知类型
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

$type = !empty($_GET['type']) && $noticetypes[$_GET['type']]?$_GET['type']:'';
$typesql = $type?"AND type='$type'":'';

$list = array();
$count = 0;
//处理查询
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' $typesql ORDER BY dateline DESC LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$list[] = $value;
	$count++;
}

//分页
$multi = smulti($start, $perpage, $count, "space.php?do=$do");

//设置本次查看时间
$wherearr = array('uid'=>$_SGLOBAL['supe_uid'], 'new'=>1);
if($type) {
	$wherearr['type'] = $type;
}
updatetable('notification', array('new'=>0), $wherearr);

include_once template("space_notice");

?>