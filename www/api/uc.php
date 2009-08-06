<?php

define('UC_CLIENT_VERSION', '1.5.0');	//note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20090502');

define('API_DELETEUSER', 1);		//note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//note 用户改名 API 接口开关
define('API_GETTAG', 1);		//note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//note 同步登出 API 接口开关
define('API_UPDATEPW', 1);		//note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);	//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);		//note 更新应用列表 开关
define('API_UPDATECLIENT', 1);		//note 更新客户端缓存 开关
define('API_UPDATECREDIT', 0);		//note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 0);	//note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 0);		//note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 0);	//note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('KC_INDEX',True);
define('KC_CALL_FUNC',True);

require_once '../global.php';//加载KingCMS

$king->load('user');//加载用户类
//note 普通的 http 通知方式
	error_reporting(0);
	restore_error_handler();
	set_magic_quotes_runtime(0);
	define('KC_ROOT', substr(dirname(__FILE__), 0, -3));
	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	define('UC_CLIENT_ROOT', KC_ROOT.'user/client/');

	$_DCACHE = $get = $post = array();
//解码传入参数
	$code = @$_GET['code'];
	parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) {
		$get = _stripslashes($get);
	}
//比较时效
	$timestamp = time();
	if($timestamp - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}

	if(empty($get)) {
		exit('Invalid Request');
	}
	$action = $get['action'];

	require_once UC_CLIENT_ROOT.'./lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
		$uc_note = new uc_note();
		exit($uc_note->$get['action']($get, $post));
	} else {
		exit(API_RETURN_FAILED);
	}

class uc_note {
	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once UC_CLIENT_ROOT.'./lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
	//初始化
		/*
		sth to do();
		*/
	}

	function test($get, $post) {
	//UC通信测试接口
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post) {
		$uids = $get['ids'];

		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);
		//note 用户删除 API 接口
		global $king;

		$query = $king->db->query("DELETE FROM %s_user WHERE uid IN ($uids)");
		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post) {
		$uid = $get['uid'];
		$usernameold = $get['oldusername'];
		$usernamenew = $get['newusername'];
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}
		//note 获取标签 API 接口
		global $king;
		if(!kc_validate($uid,2)){return API_RETURN_FAILED;}
		$king->db->update('%s_user',array('username'=>$usernamenew),"uid='$uid'");
//		$king->db->update('%s_comment',array('username'=>$usernamenew),"username='$usernameold'");
		return API_RETURN_SUCCEED;
	}

/*
	function gettag($get, $post) {
		$name = $get['id'];
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}

		//note 获取标签 API 接口
		global $king;

		$name = trim($name);
		if(empty($name) || !preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
			return API_RETURN_FAILED;
		}

		$tag = $king->db->getRows_one("SELECT * FROM ".KC_DB_PRE."tag WHERE ktag='$name'");

		$tpp = 10;
		$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$kcurl = 'http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';
		$query = $this->db->query("SELECT t.* FROM ".KC_DB_PRE."_tag tt LEFT JOIN ".KC_DB_PRE."__article t ON t.tid=tt.tid AND t.displayorder>='0' WHERE tt.tagname='$name' ORDER BY tt.tid DESC LIMIT $tpp");
		$threadlist = array();
		while($tagthread = $this->db->fetch_array($query)) {
			if($tagthread['tid']) {
				$threadlist[] = array(
					'subject' => $tagthread['subject'],
					'uid' => $tagthread['authorid'],
					'username' => $tagthread['author'],
					'dateline' => $tagthread['dateline'],
					'url' => $boardurl.'viewthread.php?tid='.$tagthread['tid'],
				);
			}
		}

		$return = array($name, $threadlist);
		return $this->_serialize($return, 1);
	}
*/
	function synlogin($get, $post) {
		$uid = $get['uid'];
		$username = $get['username'];
		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}

		//note 同步登录 API 接口
		global $king;
		if(!kc_validate($uid,2)){return API_RETURN_FAILED;}
		if($userinfo = $king->db->getRows_one("SELECT userid FROM %s_user WHERE username='".$king->db->escape($username)."'")){
			$userid=$userinfo['userid'];
			unset($userinfo);
			$king->user->userLogin($userid,2592000);
		}
	}

	function synlogout($get, $post) {
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}

		//note 同步登出 API 接口
		global $king;
		$king->user->userLogout();
	}

	function updatepw($get, $post) {
		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}
		$username = $get['username'];
		$password = $get['password'];
		global $king;
		$salt=kc_random(6);
		$md5pass=md5($salt.$password);
		$king->db->update('%s_user',array('salt'=>$salt,'userpass'=>$md5pass),"username='".$king->db->escape($username)."'");

		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) {
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = UC_CLIENT_ROOT.'./data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) {
		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = UC_CLIENT_ROOT.'./data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}
		$UC_API = $post['UC_API'];

		//note 写 app 缓存文件
		$cachefile = UC_CLIENT_ROOT.'./data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		//note 写配置文件
		if(is_writeable(KC_ROOT.'./config.php')) {
			$configfile = trim(file_get_contents(KC_ROOT.'./config.php'));
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
			if($fp = @fopen(KC_ROOT.'./config.php', 'w')) {
				@fwrite($fp, trim($configfile));
				@fclose($fp);
			}
		}
		/*
		todo(更新系统缓存);
		*/
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = UC_CLIENT_ROOT.'./data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}
/*
	function updatecredit($get, $post) {
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];


		$this->db->query("UPDATE ".$this->tablepre."user SET extcredits$credit=extcredits$credit+'$amount' WHERE uid='$uid'");

		$kc_user = $this->db->result_first("SELECT username FROM ".$this->tablepre."members WHERE uid='$uid'");

		$this->db->query("INSERT INTO ".$this->tablepre."creditslog (uid, fromto, sendcredits, receivecredits, send, receive, dateline, operation)
				VALUES ('$uid', '$kc_user', '0', '$credit', '0', '$amount', '$timestamp', 'EXC')");
		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) {
		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		require $this->dbconfig;
		require_once $this->appdir.'./include/db_'.$database.'.class.php';

		$db = new dbstuff;
		$this->db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset ? $dbcharset : $charset);
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

		$uid = intval($get['uid']);
		$credit = intval($get['credit']);
		echo $credit >= 1 && $credit <= 8 ? $this->db->result_first("SELECT extcredits$credit FROM ".$this->tablepre."members WHERE uid='$uid'") : 0;
	}

	function getcreditsettings($get, $post) {
		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		require_once $this->appdir.'./forumdata/cache/cache_settings.php';
		$credits = array();
		foreach($_DCACHE['settings']['extcredits'] as $id => $extcredits) {
			$credits[$id] = array(strip_tags($extcredits['title']), $extcredits['unit']);
		}
		return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post) {
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		require $this->dbconfig;
		$outextcredits = array();
		if($credit) {
			foreach($credit as $appid => $credititems) {
				if($appid == UC_APPID) {
					foreach($credititems as $value) {
						$outextcredits[] = array(
							'appiddesc' => $value['appiddesc'],
							'creditdesc' => $value['creditdesc'],
							'creditsrc' => $value['creditsrc'],
							'title' => $value['title'],
							'unit' => $value['unit'],
							'ratiosrc' => $value['ratiosrc'],
							'ratiodesc' => $value['ratiodesc'],
							'ratio' => $value['ratio']
						);
					}
				}
			}
		}

		global $_DCACHE;
		require_once $this->appdir.'./include/db_'.$database.'.class.php';
		require_once $this->appdir.'./forumdata/cache/cache_settings.php';
		require_once $this->appdir.'./include/cache.func.php';

		$db = new dbstuff;
		$this->db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset ? $dbcharset : $charset);
		unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

		$this->db->query("REPLACE INTO ".$this->tablepre."settings (variable, value) VALUES ('outextcredits', '".addslashes(serialize($outextcredits))."');", 'UNBUFFERED');

		$tmp = array();
		foreach($outextcredits as $value) {
			$key = $value['appiddesc'].'|'.$value['creditdesc'];
			if(!isset($tmp[$key])) {
				$tmp[$key] = array('title' => $value['title'], 'unit' => $value['unit']);
			}
			$tmp[$key]['ratiosrc'][$value['creditsrc']] = $value['ratiosrc'];
			$tmp[$key]['ratiodesc'][$value['creditsrc']] = $value['ratiodesc'];
			$tmp[$key]['creditsrc'][$value['creditsrc']] = $value['ratio'];
		}
		$_DCACHE['settings']['outextcredits'] = $tmp;

		updatesettings();

		return API_RETURN_SUCCEED;

	}
*/
}


function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function _stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}