<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Users.php 9758 2008-11-14 08:06:33Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Users extends MyBase {
	
	function getInfo($uIds, $fields = array()) {
		global $_SGLOBAL;
		$result = array();
		$query = $_SGLOBAL['db']->query("SELECT sf.*, s.* FROM ".tname('space')." s
			LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
			WHERE s.uid IN ('".implode("','", $uIds)."')");
		while($space = $_SGLOBAL['db']->fetch_array($query)) {
			$user = $this->_space2user($space);
			$tmp = array();
			if($fields) {
				foreach($fields as $field) {
					$tmp[$field] = $user[$field];
				}
			} else {
				$tmp = $user;
			}
			$result[] = $tmp;
		}
		return new APIResponse($result);
	}

	function getFriendInfo($uId, $num) {
		global $_SGLOBAL;

		$allFriends = $this->_getFriends($uId);
		$totalNum = count($allFriends);
		$result = array('totalNum'	=> $totalNum,
						'friends' => array(),
						'allFriends' => $allFriends
						);
		$num = $num > $totalNum ? $totalNum : $num;
		if (is_array($allFriends)) {
			for($i = 0; $i < $num; $i++) {
				$friendId = $allFriends[$i];
				$space = $this->getUserSpace($friendId);
				$user = $this->_space2user($space);
				$result['friends'][] = $user;
			}
		}
		return new APIResponse($result);
	}

}
?>
