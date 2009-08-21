<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_inputpwd.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('pwdsubmit')) {

	$blogid = empty($_POST['blogid'])?0:intval($_POST['blogid']);
	$albumid = empty($_POST['albumid'])?0:intval($_POST['albumid']);
	
	$itemarr = array();
	if($blogid) {
		$query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('blog')." WHERE blogid='$blogid'");
		$itemarr = $_SGLOBAL['db']->fetch_array($query);
	} elseif($albumid) {
		$query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('album')." WHERE albumid='$albumid'");
		$itemarr = $_SGLOBAL['db']->fetch_array($query);
	}
	
	if(empty($itemarr)) {
		showmessage('news_does_not_exist');
	}
	
	if($itemarr['password'] && $_POST['viewpwd'] == $itemarr['password']) {
		$cookiename = 'view_pwd_'.($blogid?"blog_$blogid":"album_$albumid");
		ssetcookie($cookiename, md5(md5($itemarr['password'])));
		showmessage('proved_to_be_successful', $_POST['refer']);
	} else {
		showmessage('password_is_not_passed');
	}
}

?>