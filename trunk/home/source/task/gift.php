<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: gift.php 10903 2008-12-31 06:06:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if($_SGLOBAL['supe_uid']) {
	
	$task['done'] = 1;//活动完成
	
	$task['result'] = '<p>感谢您参与此次有奖活动，大礼包已经领取到了。欢迎下次继续参与。</p>';
	$task['result'] .= '<p>推荐给您10个1周内比较火热的日志：</p>';
	$task['result'] .= '<br><br><ul class="line_list">';
	$dateline = $_SGLOBAL['timestamp']-3600*24*7;//1周
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE dateline>'$dateline' AND friend='0' ORDER BY replynum DESC LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$task['result'] .= "<li><a href=\"space.php?uid=$value[uid]&do=blog&id=$value[blogid]\" target=\"_blank\">$value[subject]</a> (<a href=\"space.php?uid=$value[uid]\" target=\"_blank\">$value[username]</a>)</li>";
	}
	$task['result'] .= '</ul>';
	
} else {
	
	$task['guide'] = '';
}

?>