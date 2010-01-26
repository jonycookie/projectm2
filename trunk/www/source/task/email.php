<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: email.php 9984 2008-11-21 08:57:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if($space['emailcheck']) {

	$task['done'] = 1;//活动完成

} else {

	//活动完成向导
	$task['guide'] = '
		<strong>请按照以下的说明来参与本活动：</strong>
		<ul>
		<li><a href="cp.php?ac=password" target="_blank">新窗口打开账号设置页面</a>；</li>
		<li>在新打开的设置页面中，将自己的邮箱真实填写，并点击“验证邮箱”按钮；</li>
		<li>几分钟后，系统会给你发送一封邮件，收到邮件后，请按照邮件的说明，访问邮件中的验证链接就可以了。</li>
		</ul>';

}

?>