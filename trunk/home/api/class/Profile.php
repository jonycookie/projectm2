<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Profile.php 7952 2008-07-04 07:14:25Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Profile {

	function setMYML($uId, $appId, $markup, $actionMarkup) {
		global $_SGLOBAL;

		$fields = array('myml'	=> $markup,
						'profileLink'	=> $actionMarkup);
		$where = array('uid'	=> $uId,
					   'appid'	=> $appId
					  );
		updatetable('userapp', $fields, $where);
		$result = $_SGLOBAL['db']->affected_rows();
		return new APIResponse($result);
	}

	function setActionLink($uId, $appId, $actionMarkup) {
		global $_SGLOBAL;

		$fields = array('profilelink'	=> $actionMarkup);
		$where = array('uid'	=> $uId,
					   'appid'	=> $appId
					  );
		updatetable('userapp', $fields, $where);
		$result = $_SGLOBAL['db']->affected_rows();
		return new APIResponse($result);
	}

}

?>
