<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: blog.php 9984 2008-11-21 08:57:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$blogcount = getcount('blog', array('uid'=>$space['uid']));
if($blogcount) {

	$task['done'] = 1;//活动完成

} else {

	//活动完成向导
	$task['guide'] = '
		<strong>请按照以下的说明来参与本活动：</strong>
		<ul>
		<li>1. <a href="cp.php?ac=blog" target="_blank">新窗口打开发表日志页面</a>；</li>
		<li>2. 在新打开的页面中，书写自己的第一篇日志，并进行发布。</li>
		</ul>';

}

?>