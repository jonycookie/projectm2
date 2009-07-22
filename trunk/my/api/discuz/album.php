<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: album.php 10549 2008-12-09 05:46:20Z zhengqingpeng $
*/
if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$uid = !empty($_GET['uid']) ? intval($_GET['uid']) : 0;
$aid = !empty($_GET['aid']) ? intval($_GET['aid']) : 0;
$start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
$perpage = !empty($_GET['perpage']) ? intval($_GET['perpage']) : 8;

$sql = '';
if($_SGLOBAL['supe_uid'] != $uid) {
	$sql = " AND friend='0' ";
}
	
if($aid && $uid) {
	
	$piclist = array();
	$count = isset($_GET['count']) ? intval($_GET['count']) : 0;
	
	if(empty($count)) {
		$query = $_SGLOBAL['db']->query("SELECT picnum FROM ".tname('album')." WHERE albumid='$aid' AND uid='$uid' $sql");
		$value = $_SGLOBAL['db']->fetch_array($query);
		$count = $value['picnum'];
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$aid' AND uid='$uid' ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['bigpic'] = mkpicurl($value, 0);
		$value['pic'] = mkpicurl($value);
		$value['count'] = $count;
		$piclist[] = $value;
	}
	echo serialize($piclist);
	
} elseif($uid) {
	
	$query = $_SGLOBAL['db']->query("SELECT albumid, albumname, picnum FROM ".tname('album')." WHERE uid='$uid' $sql ORDER BY updatetime DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['picnum']) {
			$albumlist[] = $value;
		}	
	}
	echo serialize($albumlist);
} else {
	echo serialize(array());
}


?>