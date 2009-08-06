<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Request.php 11800 2009-03-25 07:46:16Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Request extends MyBase {

	function send($uId, $recipientIds, $appId, $requestName, $myml, $type) {
		global $_SGLOBAL;
		include_once(S_ROOT.'./source/function_cp.php');
		$now = time();
		$result = array();
		$type = ($type == 'request') ? 1 : 0;

		$fields = array('typename'	=> $requestName,
						'appid'	=> $appId,
						'type'	=> $type,
						'fromuid'	=> $uId,
						'dateline'	=> $now
					   );
					   
		foreach($recipientIds as $key => $val) {
			$hash = crc32($appId . $val . $now . rand(0, 1000));
			$hash = sprintf('%u', $hash);
			$fields['touid'] = intval($val);
			$fields['hash'] = $hash;
			$fields['myml'] = str_replace('{{MyReqHash}}', $hash, $myml);
			$result[] = inserttable('myinvite',	$fields, 1);
			//����ͳ��
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET myinvite=myinvite+1 WHERE uid='$fields[touid]'");
		}
		return new APIResponse($result);
	}

}
?>
