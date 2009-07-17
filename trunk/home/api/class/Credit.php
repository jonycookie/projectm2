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
	 * ��ȡ�û�����
	 *
	 * @param integer $uId �û�Id
	 * @return integer �û�����
	 */
	function get($uId) {
		global $_SGLOBAL;
		$query = $_SGLOBAL['db']->query('SELECT credit FROM ' . tname('space') . ' WHERE uid =' . $uId);
		$row = $_SGLOBAL['db']->fetch_array($query);
		return new APIResponse($row['credit']);
	}

	/**
	 * �����û��Ļ���
	 *
	 * @param integer $uId �û�Id
	 * @param integer $credits ����ֵ
	 * @return integer ���º���û�����
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
