<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Friends.php 7952 2008-07-04 07:14:25Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Friends extends MyBase {
	
	function areFriends($uId1, $uId2) {
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')."  WHERE uid='$uId1' AND fuid='$uId2' AND status='1'");
		$result = false;
		if($friend = $_SGLOBAL['db']->fetch_array($query)) {
			$result = true;
		}
		return new APIResponse($result);
	}

	function get($uIds) {
		global $_SGLOBAL;
		$result = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')."  WHERE uid IN ('".implode("','", $uIds)."') AND status='1'");
		while($friend = $_SGLOBAL['db']->fetch_array($query)) {
			$result[$friend['uid']][] = $friend['fuid'];
		}
		return new APIResponse($result);
	}

}

?>
