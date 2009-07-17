<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: MyBase.php 10980 2009-01-14 02:57:11Z zhouguoqiang $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class MyBase {

	// 获取用户空间
	function getUserSpace($uId) {
		global $_SGLOBAL;
		$uId = intval($uId);
		$query = $_SGLOBAL['db']->query("SELECT sf.*, s.* FROM ".tname('space')." s
										LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
										WHERE s.uid='$uId'");
		$space = $_SGLOBAL['db']->fetch_array($query);
		return $space;
	}

	function _space2user($space) {
		global $_SC, $_SGLOBAL;
		$founders = explode(',', $_SC['founder']);
		$adminLevel = 'none';
		if (in_array($space['uid'], $founders)) {
			$adminLevel = 'founder';
		} else {
			@include_once S_ROOT . './data/data_usergroup.php';
			if(!empty($_SGLOBAL['usergroup'][$space['groupid']]['manageconfig'])) {
				$adminLevel = 'manager';
			}
		}

		$privacy = unserialize($space['privacy']);
		if (!$privacy) {
			$privacy = array();
		}

		$user = array(
			'uId'		=> $space['uid'],
			'handle'	=> $space['username'],
			'action'	=> $space['action'],
			'realName'	=> $space['name'],
			'realNameChecked' => $space['namestatus'] ? true : false,
			'spaceName'	=> $space['spacename'],
			'gender'	=> $space['sex'] == 1 ? 'male' : ($space['sex'] == 2 ? 'female' : 'unknown'),
			'email'		=> $space['email'],
			'qq'		=> $space['qq'],
			'msn'		=> $space['msn'],
			'birthday'	=> sprintf('%04d-%02d-%02d', $space['birthyear'], $space['birthmonth'], $space['birthday']),
			'bloodType'	=> empty($space['blood']) ? 'unknown' : $space['blood'],
			'relationshipStatus' => $space['marry'] == 1 ? 'single' : ($space['marry'] == 2 ? 'notSingle' : 'unknown'),
			'birthProvince' => $space['birthprovince'],
			'birthCity'	=> $space['birthcity'],
			'resideProvince' => $space['resideprovince'],
			'resideCity'	=> $space['residecity'],
			'viewNum'	=> $space['viewnum'],
			'friendNum'	=> $space['friendnum'],
			'myStatus'	=> $space['note'],
			'lastActivity' => $space['updatetime'],
			'created'	=> $space['dateline'],
			'credit'	=> $space['credit'],
			'isUploadAvatar'	=> $space['avatar'] ? true : false,
			'adminLevel'		=> $adminLevel,
			'homepagePrivacy'	=> $privacy['view']['index'] == 1 ? 'friends' : ($privacy['view']['index'] == 2 ? 'me' : 'public'),
			'profilePrivacy'	=> $privacy['view']['profile'] == 1 ? 'friends' : ($privacy['view']['profile'] == 2 ? 'me' : 'public'),
			'friendListPrivacy'	=> $privacy['view']['friend'] == 1 ? 'friends' : ($privacy['view']['friend'] == 2 ? 'me' : 'public')
		);
		return $user;
	}

	function _getFriends($uId, $num = null) {
		global $_SGLOBAL;

		$sql = sprintf('SELECT * FROM %s WHERE uid = %d AND status = 1 ORDER BY num DESC, dateline DESC', tname('friend'), $uId);
		if ($num) {
			$sql .= ' LIMIT 0, ' . $num;
		}
		$fquery = $_SGLOBAL['db']->query($sql);
		$friends = array();
		while($friend = $_SGLOBAL['db']->fetch_array($fquery)) {
			$friends[] = $friend['fuid'];
		}
		return $friends;
	}

	function refreshApplication($appId, $appName, $version, $displayMethod, $narrow, $flag, $displayOrder) {
		global $_SGLOBAL;
		$fields = array();
		if ($appName !== null && strlen($appName)>1) {
			$fields['appname'] = $appName;
		}
		if ($version !== null) {
			$fields['version'] = $version;
		}
		if ($displayMethod !== null) {
			// todo: remove
			$fields['displaymethod'] = $displayMethod;
		}
		if ($narrow !== null) {
			$fields['narrow'] = $narrow;
		}
		if ($flag !== null) {
			$fields['flag'] = $flag;
		}
		if ($displayOrder !== null) {
			$fields['displayorder'] = $displayOrder;
		}
		$sql = sprintf('SELECT * FROM %s WHERE appid = %d', tname('myapp'), $appId);
		$query = $_SGLOBAL['db']->query($sql);
		if($application = $_SGLOBAL['db']->fetch_array($query)) {
			$where = sprintf('appid = %d', $appId);
			updatetable('myapp', $fields, $where);
		} else {
			$fields['appid'] = $appId;
			$result = inserttable('myapp', $fields, 1);
		}
		
		//update cache
		include_once(S_ROOT.'./source/function_cache.php');
		userapp_cache();
	}
}

class my{

	function parseRequest() {
		global $_SCONFIG;

		include_once(S_ROOT.'./source/function_common.php');
		
		$request = $_POST;
		$module = $request['module'];
		$method = $request['method'];
		
		$errCode = 0;
		$errMessage = '';
		if ($_SCONFIG['close']) {
			$errCode = 2;
			$errMessage = 'Site Closed';
		} elseif (!$_SCONFIG['my_status']) {
			$errCode = 2;
			$errMessage = 'Manyou Service Disabled';
		} elseif (!$_SCONFIG['sitekey']) {
			$errCode = 11;
			$errMessage = 'Client SiteKey NOT Exists';
		} elseif (!$_SCONFIG['my_sitekey']) {
			$errCode = 12;
			$errMessage = 'My SiteKey NOT Exists';
		} elseif (empty($module) || empty($method)) {
			$errCode = '3';
			$errMessage = 'Invalid Method: ' . $moudle . '.' . $method;
		}

		if (get_magic_quotes_gpc()) {
			$request['params'] = sstripslashes($request['params']);
		}
		$mySign = $module . '|' . $method . '|' . $request['params'] . '|' . $_SCONFIG['my_sitekey'];
		$mySign = md5($mySign);
		if ($mySign != $request['sign']) {
			$errCode = '10';
			$errMessage = 'Error Sign';
		}

		if ($errCode) {
			return new APIErrorResponse($errCode, $errMessage);
		}

		$params = unserialize($request['params']);

		$params = $this->myAddslashes($params);
		if ($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				$response[] = $this->callback($param['module'], $param['method'], $param['params']);
			}
			return new APIResponse($response, 'Batch');
		}
		return $this->callback($module, $method, $params);
	}

	function callback($module, $method, $params) {
		global $_SGLOBAL;
		if (isset($params['uId'])) {
			$space = getspace($params['uId']);
			if ($this->_needCheckUserId($module, $method)) {
				if (!$space['uid']) {
					$errCode = 1;
					$errMessage = "User($params[uId]) Not Exists";
					return new APIErrorResponse($errCode, $errMessage);
				}
			}
		}
		$_SGLOBAL['supe_uid'] = $space['uid'];
		$_SGLOBAL['supe_username'] = $space['username'];

		@include_once S_ROOT . './api/class/' . $module . '.php';
		if (!class_exists($module)) {
			$errCode = 3;
			$errMessage = "Class($module) Not Exists";
			return new APIErrorResponse($errCode, $errMessage);
		}

		$class = new $module();
		$response = @call_user_func_array(array(&$class, $method), $params);

		return $response;
	}

	//格式化返回结果
	function formatResponse($data) {
		global $_SCONFIG, $_SC;
		//返回结果要参加一些统一的返回信息
		$res = array(
			'timezone'	=> $_SCONFIG['timeoffset'],
			'version'   => X_VER,
			'charset'	=> $_SC['charset'],
		);
		if (strtolower(get_class($data)) == 'apiresponse' ) {
			if (is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if (strtolower(get_class($result)) == 'apiresponse') {
						$res['result'][]  = $result->getResult();
					} else {
						$res['result'][] = array('errCode' => $result->getErrCode(),
												 'errMessage' =>  $result->getErrMessage()
												);
					}
				}
			} else {
				$res['result']  = $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getErrCode();
			$res['errMessage'] = $data->getErrMessage();
		}
		return serialize($res);
	}

	function _needCheckUserId($module, $method) {
		$myMethod = $module . '.' . $method;
		switch($myMethod) {
			case 'Notifications.send':
			case 'Request.send':
				$res = false;
				break;
			default:
				$res = true;
		}
		return $res;
	}

	function myAddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

}
?>
