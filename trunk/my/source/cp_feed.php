<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_feed.php 6968 2008-04-03 10:16:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$feedid = empty($_GET['feedid'])?0:intval($_GET['feedid']);

if($_GET['op'] == 'delete') {
	if(submitcheck('feedsubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deletefeeds(array($feedid))) {
			showmessage('do_success', $_POST['refer']);
		} else {
			showmessage('no_privilege');
		}
	}
} elseif($_GET['op'] == 'ignore') {
	
	$icon = empty($_GET['icon'])?'':preg_replace("/[^0-9a-zA-Z\_\-\.]/", '', $_GET['icon']);
	if(submitcheck('feedignoresubmit')) {
		$uid = empty($_POST['uid'])?0:intval($_POST['uid']);
		if($icon) {
			$icon_uid = $icon.'|'.$uid;
			if(empty($space['privacy']['filter_icon']) || !is_array($space['privacy']['filter_icon'])) {
				$space['privacy']['filter_icon'] = array();
			}
			$space['privacy']['filter_icon'][$icon_uid] = $icon_uid;
			privacy_update();
		}
		showmessage('do_success', $_POST['refer']);
	}
}

include template('cp_feed');

?>