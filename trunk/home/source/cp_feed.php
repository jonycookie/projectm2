<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_feed.php 9540 2008-11-07 06:57:27Z liguode $
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
} elseif($_GET['op'] == 'get') {

	//获得好友的feed
	$cp_mode = 1;
	$_GET['start'] = intval($_GET['start']);
	if($_GET['start'] < 1) {
		$_GET['start'] = $_SCONFIG['feedmaxnum']<50?50:$_SCONFIG['feedmaxnum'];
		$_GET['start'] = $_GET['start'] + 1;
	}
	include_once(S_ROOT.'./source/space_feed.php');
}

include template('cp_feed');

?>