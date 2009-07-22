<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Site.php 9989 2008-11-21 10:12:08Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Site extends MyBase {

	function getUpdatedUsers($num) {
		global $_SGLOBAL;

		$totalNum = getcount('userlog', '');
		$users = array();

		if ($totalNum) {
			$sql = 'SELECT s.*, sf.*, ul.action, ul.uid
				FROM %s ul
				LEFT JOIN %s s ON ul.uid = s.uid
				LEFT JOIN %s sf ON ul.uid = sf.uid
				ORDER BY ul.dateline  
				LIMIT %d';
			$sql = sprintf($sql, tname('userlog'), tname('space'), tname('spacefield'), $num);
			$query = $_SGLOBAL['db']->query($sql);

			$uIds = array();
			while($space = $_SGLOBAL['db']->fetch_array($query)) {

				$user = $this->_space2user($space);
				$users[] = $user;
				$uIds[] = $space['uid'];

			}
			if ($uIds) {
				$sql = sprintf('DELETE FROM %s WHERE uid IN (%s)', tname('userlog'), simplode($uIds));
				$_SGLOBAL['db']->query($sql);
			}
		}

		$result = array('totalNum'	=> $totalNum,
						'users'		=> $users
					   );
		return new APIResponse($result);
	}

	function getUpdatedFriends($num) {
		global $_SGLOBAL;

		$friends = array();
		$totalNum = getcount('friendlog', '');

		if ($totalNum) {
			$sql = sprintf('SELECT * FROM %s ORDER BY dateline LIMIT %d', tname('friendlog'), $num);
			$query = $_SGLOBAL['db']->query($sql);
			while ($friend = $_SGLOBAL['db']->fetch_array($query)) {
				$friends[] = array('uId'	=> $friend['uid'],
								   'uId2'	=> $friend['fuid'],
								   'action'	=> $friend['action']
								  );

				$sql = sprintf('DELETE FROM %s WHERE uid = %d AND fuid = %d', tname('friendlog'), $friend['uid'], $friend['fuid']);
				$_SGLOBAL['db']->query($sql);
			}

		}

		$result = array('totalNum'	=> $totalNum,
						'friends'	=> $friends
					   );
		return new APIResponse($result);

	}

	function getAllUsers($from, $num) {
		global $_SGLOBAL;

		$totalNum = getcount('space', '');

		$sql = 'SELECT s.*, sf.*
				FROM %s s 
				LEFT JOIN %s sf ON s.uid = sf.uid
				ORDER BY s.uid
				LIMIT %d, %d';
		$sql = sprintf($sql, tname('space'), tname('spacefield'), $from, $num);
		$query = $_SGLOBAL['db']->query($sql);

		$users = array();
		while($space = $_SGLOBAL['db']->fetch_array($query)) {
			$user = $this->_space2user($space);
			$user['friends'] = $this->_getFriends($space['uid']);
			$user['action'] = 'add';
			$users[] = $user;
		}
		$result = array('totalNum'	=> $totalNum,
						'users'		=> $users
					   );
		return new APIResponse($result);
	}

}

?>
