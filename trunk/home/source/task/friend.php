<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: friend.php 9984 2008-11-21 08:57:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if($space['friendnum']>=5) {

	$task['done'] = 1;//活动完成

} else {

	//向导
	$task['guide'] = '
		<strong>请按照以下的说明来参与本活动：</strong>
		<ul>
		<li>1. <a href="cp.php?ac=friend&op=find" target="_blank">新窗口打开寻找好友页面</a>；</li>
		<li>2. 在新打开的页面中，可以将系统自动给你找到的推荐用户加为好友，也可以自己设置条件寻找并添加为好友；</li>
		<li>3. 接下来，您还需要等待对方批准您的好友申请。</li>
		</ul>';

}

?>