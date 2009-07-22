<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Application.php 9640 2008-11-11 10:28:12Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Application extends MyBase {

	function update($appId, $appName, $version, $displayMethod, $displayOrder = null) {
		$fields = array('appname' => $appName);
		$where = array('appid'	=> $appId);
		$result = updatetable('userapp', $fields, $where);

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $displayMethod, null, null, $displayOrder);
		return new APIResponse($result);
	}

	function remove($appIds) {
		global $_SGLOBAL;
		$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', tname('userapp'), simplode($appIds));
		$result = $_SGLOBAL['db']->query($sql);

		$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', tname('myapp'), simplode($appIds));
		$_SGLOBAL['db']->query($sql);
		
		//update cache
		include_once(S_ROOT.'./source/function_cache.php');
		userapp_cache();
		
		return new APIResponse($result);
	}

	function setFlag($applications, $flag) {
		global $_SGLOBAL;

		$flag = ($flag == 'disabled') ? -1 : ($flag == 'default' ? 1 : 0);
		$appIds = array();
		if ($applications && is_array($applications)) {
			foreach($applications as $application) {
				$this->refreshApplication($application['appId'], $application['appName'], null, null, null, $flag, null);
				$appIds[] = $application['appId'];
			}
		}

		if ($flag == -1) {
			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', tname('feed'), simplode($appIds));
			$_SGLOBAL['db']->query($sql);

			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', tname('userapp'), simplode($appIds));
			$_SGLOBAL['db']->query($sql);

			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', tname('myinvite'), simplode($appIds));
			$_SGLOBAL['db']->query($sql);

			$sql = sprintf('DELETE FROM %s WHERE type IN (%s)', tname('notification'), simplode($appIds));
			$_SGLOBAL['db']->query($sql);
		}

		$result = true;
		return new APIResponse($result);
	}

}
?>
