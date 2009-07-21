<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Credit.php 10942 2009-01-08 09:25:04Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Credit extends MyBase {

	/**
	 * 获取用户积分
	 *
	 * @param integer $uId 用户Id
	 * @return integer 用户积分
	 */
	function get($uId) {
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query('SELECT credit FROM ' . tname('space') . ' WHERE uid =' . $uId);
		$row = $_SGLOBAL['db']->fetch_array($query);
		return new APIResponse($row['credit']);
	}

	/**
	 * 更新用户的积分
	 *
	 * @param integer $uId 用户Id
	 * @param integer $credits 积分值
	 * @return integer 更新后的用户积分
	 */
	function update($uId, $credits) {
		global $_SGLOBAL;
		$sql = sprintf('UPDATE %s SET credit = credit + %d WHERE uid=%d', tname('space'), $credits, $uId);
		$result = $_SGLOBAL['db']->query($sql);

		$query = $_SGLOBAL['db']->query('SELECT credit FROM ' . tname('space') . ' WHERE uid =' . $uId);
		$row = $_SGLOBAL['db']->fetch_array($query);
		return new APIResponse($row['credit']);
	}
}

?>
