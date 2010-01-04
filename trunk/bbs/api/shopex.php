<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: shopex.php 9805 2007-08-15 05:59:02Z cnteacher $
*/

error_reporting(0);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);


$table_member_columns = array('username', 'password', 'secques', 'email', 'adminid', 'groupid', 'gender', 'bday', 'regip', 'regdate', 'timeoffset', 'newsletter');
$table_memberfields_columns = array('site', 'location', 'qq', 'icq', 'msn', 'yahoo');

define('IN_DISCUZ', true);
define('DISCUZ_ROOT', './');

$timestamp = time();

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
}

chdir('../');
require_once './config.inc.php';
require_once './include/db_'.$database.'.class.php';
require_once './forumdata/cache/cache_settings.php';

if($_DCACHE['settings']['passport_status'] != 'shopex') {
	exit('Passport disabled');
} elseif($_GET['verify'] != md5($_GET['action'].$_GET['auth'].$_GET['forward'].$_DCACHE['settings']['passport_key'])) {
	exit('Illegal request');
}

if($_GET['action'] == 'login') {

	$memberfields = $remoteinfo = array();
	parse_str(passport_decrypt($_GET['auth'], $_DCACHE['settings']['passport_key']), $member);
	foreach($member as $key => $val) {
		if(in_array($key, array('username', 'password', 'email', 'credits', 'gender', 'bday', 'regip', 'regdate', 'site', 'qq', 'msn', 'yahoo'))) {
			$memberfields[$key] = addslashes($val);
		} elseif(in_array($key, array('cookietime', 'time'))) {
			$remoteinfo[$key] = $val;
		} elseif($key == 'isadmin') {
			if($val) {
				$memberfields['groupid'] = $memberfields['adminid'] = 1;
			}
		}
	}

	if(strlen($memberfields['username'] = preg_replace("/(c:\\con\\con$|[%,\*\"\s\t\<\>\&])/i", "", $memberfields['username'])) > 15) {
		$memberfields['username'] = substr($memberfields['username'], 0, 15);
	}

	if(empty($remoteinfo['time']) || empty($memberfields['username']) || empty($memberfields['password']) || empty($memberfields['email'])) {
		exit('Lack of required parameters');
	} elseif($timestamp - $remoteinfo['time'] > $_DCACHE['settings']['passport_expire']) {
		exit('Request expired');
	}

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($_DCACHE['settings']['passport_extcredits']) {
		$memberfields['extcredits'.$_DCACHE['settings']['passport_extcredits']] = $memberfields['credits'];
		$table_member_columns[] = 'extcredits'.$_DCACHE['settings']['passport_extcredits'];
	}

	$memberfields['regip'] = empty($memberfields['regip']) ? onlineip() : $memberfields['regip'];
	$memberfields['regdate'] = empty($memberfields['regdate']) ? $timestamp : $memberfields['regdate'];

	$query = $db->query("SELECT uid, secques FROM {$tablepre}members WHERE username='$memberfields[username]'");
	if($member = $db->fetch_array($query)) {
		$sql = $comma = '';
		foreach($table_member_columns as $field) {
			if(isset($memberfields[$field])) {
				$sql .= "$comma$field='{$memberfields[$field]}'";
				$comma = ', ';
			}
		}
		$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$member[uid]'");

		$sql = $comma = '';
		foreach($table_memberfields_columns as $field) {
			if(isset($memberfields[$field])) {
				$sql .= "$comma$field='{$memberfields[$field]}'";
				$comma = ', ';
			}
		}

		if($sql) {
			$db->query("UPDATE {$tablepre}memberfields SET $sql WHERE uid='$member[uid]'");
		}
	} else {
		if(empty($memberfields['groupid'])) {
			$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditshigher='0'");
			$memberfields['groupid'] = $db->result($query, 0);
			$memberfields['adminid'] = 0;
		}
		$memberfields['timeoffset'] = !$memberfields['timeoffset'] ? 9999 : $memberfields['timeoffset'];
		$memberfields['newsletter'] = !$memberfields['newsletter'] ? 1 : $memberfields['newsletter'];

		$sql1 = $sql2 = $comma = '';
		foreach($table_member_columns as $field) {
			if(isset($memberfields[$field])) {
				$sql1 .= "$comma$field";
				$sql2 .= "$comma'{$memberfields[$field]}'";
				$comma = ', ';
			}
		}
		$db->query("INSERT INTO {$tablepre}members ($sql1) VALUES ($sql2)");
		$table_memberfields_columns[] = 'uid';
		$memberfields['uid'] = $member['uid'] = $db->insert_id();
		$member['secques'] = '';

		$sql1 = $sql2 = $comma = '';
		foreach($table_memberfields_columns as $field) {
			if(isset($memberfields[$field])) {
				$sql1 .= "$comma$field";
				$sql2 .= "$comma'{$memberfields[$field]}'";
				$comma = ', ';
			}
		}

		$db->query("REPLACE INTO {$tablepre}memberfields ($sql1) VALUES ($sql2)");

		$_DCACHE['settings']['lastmember'] = $memberfields['username'];
		$_DCACHE['settings']['totalmembers']++;

		updatemembercache();
	}

	dsetcookie('sid', '', -86400 * 365);
	dsetcookie('auth', authcode("$memberfields[password]\t".(isset($memberfields['secques']) ? $memberfields['secques'] : $member['secques'])."\t$member[uid]", 'ENCODE'), ($remoteinfo['cookietime'] ? $remoteinfo['cookietime'] : 0));

	header('Location: '.(empty($_GET['forward']) ? $_DCACHE['settings']['passport_url'] : $_GET['forward']));
	exit;

} elseif($_GET['action'] == 'logout') {

	dsetcookie('auth', '', -86400 * 365);
	dsetcookie('sid', '', -86400 * 365);

	header('Location: '.(empty($_GET['forward']) ? $_DCACHE['settings']['passport_url'] : $_GET['forward']));
	exit;

} else {

	exit('Invalid action');

}

function arrayeval($array, $level = 0) {
	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	foreach($array as $key => $val) {
		$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
		$val = !is_array($val) && (!preg_match("/^\d+$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
		if(is_array($val)) {
			$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",\n$space";
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

function authcode($string, $operation, $key = '') {

	global $_DCACHE;

	$key = md5($key ? $key : md5($_DCACHE['settings']['authkey'].$_SERVER['HTTP_USER_AGENT']));
	$key_length = strlen($key);

	$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);

	$rndkey = $box = array();
	$result = '';

	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
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
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}

}

function dsetcookie($var, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,
		$life ? $timestamp + $life : 0, $cookiepath,
		$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function onlineip() {
	global $_SERVER;
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	$onlineip = preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
}

function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}

function passport_decrypt($txt, $key) {
	$txt = passport_key(base64_decode($txt), $key);
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) {
		$md5 = $txt[$i];
		$tmp .= $txt[++$i] ^ $md5;
	}
	return $tmp;
}

function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}

function updatemembercache() {
	$dir = './forumdata/cache/';
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(@$fp = fopen($dir.'cache_settings.php', 'w')) {
		fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n".
			"//Created on ".date("M j, Y, G:i")."\n\n\$_DCACHE['settings'] = ".arrayeval($GLOBALS['_DCACHE']['settings'])."?>");
		fclose($fp);
	} else {
		exit('Can not write to cache files, please check directory ./forumdata/ and ./forumdata/cache/ .');
	}
}

?>