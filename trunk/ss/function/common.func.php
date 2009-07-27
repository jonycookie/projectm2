<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: common.func.php 11877 2009-03-30 03:11:29Z zhaofei $
*/


if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

function saddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function parseparameter($param, $nofix=1) {
	global $_SCONFIG;

	$paramarr = array();

	if($nofix && !empty($_SCONFIG['pagepostfix'])) {
		if(strrpos($param, $_SCONFIG['pagepostfix'])) {
			$param = substr($param, 0, strrpos($param, $_SCONFIG['pagepostfix']));
		}
	}

	$sarr = explode('/', $param);
	if(empty($sarr)) return $paramarr;
	if(is_numeric($sarr[0])) $sarr = array_merge(array('uid'), $sarr);
	if(count($sarr)%2 != 0) $sarr = array_slice($sarr, 0, -1);
	for($i=0; $i<count($sarr); $i=$i+2) {
		if(!empty($sarr[$i+1])) $paramarr[$sarr[$i]] = addslashes(str_replace(array('/', '\\'), '', rawurldecode(stripslashes($sarr[$i+1]))));
	}
	return $paramarr;
}

function arraytostring($array, $dot='/') {
	$result = $comma = '';
	foreach ($array as $key => $value) {
		$value = trim($value);
		if($value != '') {
			$result .= $comma.$key.$dot.rawurlencode($value);
			$comma = $dot;
		}
	}
	return $result;
}

//将数组加上单引号,并整理成串
function simplode($sarr, $comma=',') {
	return '\''.implode('\''.$comma.'\'', $sarr).'\'';
}

function gethtmlfile($parray) {

	$htmlarr = array();
	$dirarr = array();
	$id = 0;

	if(empty($parray['page'])) {
		unset($parray['page']);
	} elseif($parray['page'] < 2) {
		unset($parray['page']);
	}
	if(!empty($parray['uid'])) {
		$id = $parray['uid'];
		if(!empty($parray['action'])) {
			if($parray['action'] == 'space' || $parray['action'] == 'spacelist') {
				unset($parray['action']);
			} elseif ($parray['action'] == 'viewspace') {
				unset($parray['action']);
			}
		}
	} elseif(!empty($parray['itemid'])) {
		$id = $parray['itemid'];
	} elseif(!empty($parray['tid'])) {
		$id = $parray['tid'];
	} elseif(!empty($parray['tagid'])) {
		$id = $parray['tagid'];
	} elseif(!empty($parray['catid'])) {
		$id = $parray['catid'];
	} elseif(!empty($parray['fid'])) {
		$id = $parray['fid'];
	}

	$htmlfilename = str_replace(array('action-', 'uid-', 'itemid-'), array('', '', ''), arraytostring($parray, '-'));
	if(!empty($id)) {
		$idvalue = ($id>9)?substr($id, -2, 2):$id;
		$thedir = $idvalue;
		if(!empty($parray['action'])) {
			if($parray['action'] == 'viewnews') {
				$htmlfilename = "n-{$id}";
				if(!empty($parray['page'])) $htmlfilename .= '-'.$parray['page'];
			} elseif($parray['action'] == 'viewthread') {
				$htmlfilename = "t-{$id}";
			}
		}
	}

	if(is_dir(H_DIR) || (!is_dir(H_DIR) && @mkdir(H_DIR))) {
		if(empty($id)) {
			$htmlarr['path'] = H_DIR.'/'.$htmlfilename.'.html';
			$htmlarr['url'] = H_URL.'/'.$htmlfilename.'.html';
		} else {
			$htmldir = H_DIR.'/'.$thedir;
			if(is_dir($htmldir) || (!is_dir($htmldir) && @mkdir($htmldir))) {
				$htmlarr['path'] = H_DIR.'/'.$thedir.'/'.$htmlfilename.'.html';
				$htmlarr['url'] = H_URL.'/'.$thedir.'/'.$htmlfilename.'.html';
			} else {
				$htmlarr['path'] = H_DIR.'/'.$htmlfilename.'.html';
				$htmlarr['url'] = H_URL.'/'.$htmlfilename.'.html';
			}
		}
	} else {
		$htmlarr['path'] = S_ROOT.'./'.$htmlfilename.'.html';
		$htmlarr['url'] = S_URL.'/'.$htmlfilename.'.html';
	}

	return $htmlarr;
}

function geturl($pstring, $urlmode=0) {

	global $_SGLOBAL, $_SCONFIG, $spaceself;

	//URL缓存
	$cachekey = $pstring.$urlmode;
	if(empty($_SGLOBAL['url_cache'])) $_SGLOBAL['url_cache'] = array();
	if(!empty($_SGLOBAL['url_cache'][$cachekey])) {
		return $_SGLOBAL['url_cache'][$cachekey];
	}

	//url结果
	$theurl = '';

	//强制php模式
	$isphp = !empty($spaceself)?1:strexists($pstring, 'php/1');

	//首页链接
	if($pstring == 'action/index') $pstring = '';

	//搜索友好模式
	if(!empty($_SCONFIG['htmlmode']) && $_SCONFIG['htmlmode'] == 2 && !$isphp && $urlmode != 1) {
		$htmlarr = array('uid'=>'', 'action'=>'', 'catid'=>'', 'fid'=>'', 'tagid'=>'', 'itemid'=>'', 'tid'=>'', 'type'=>'', 'view'=>'', 'mode'=>'', 'showpro'=>'', 'itemtypeid'=>'', 'page'=>'');
		$sarr = explode('/', $pstring);

		if(empty($sarr)) $sarr = array('action'=>'index');

		$htmlurlcheck = true;
		for($i=0; $i<count($sarr); $i=$i+2) {
			if(!empty($sarr[$i+1])) {
				if(key_exists($sarr[$i], $htmlarr)) {
					$htmlarr[$sarr[$i]] = addslashes(str_replace(array('/', '\\'), '', rawurldecode(stripslashes($sarr[$i+1]))));
				} else {
					$htmlurlcheck = false;
					break;
				}
			}
		}
		if($htmlurlcheck) {
			$htmls = gethtmlfile($htmlarr);
			if(file_exists($htmls['path'])) {
				$theurl = $htmls['url'];
			}
		}
	}

	//普通模式
	if(empty($theurl)) {
		if(empty($pstring)) {
			if($urlmode == 1) {
				$theurl = S_URL_ALL;
			} else {
				$theurl = S_URL;
			}
		} else {
			$pre = '';
			$para = str_replace('/', '-', $pstring);
			if($isphp) {
				$pre = '/index.php?';
			} else {
				if ($_SCONFIG['urltype'] == 5) {
					$pre = '/index.php/';
				} else {
					$pre = '/?';
				}
			}
			if(empty($para)) $pre = '/';

			if($urlmode == 1) {
				//全部路径
				$theurl = S_URL_ALL.$pre.$para;
			} elseif($urlmode == 2) {
				//处理
				$theurl = S_URL.$pre.$para;
				$theurl = url_remake($theurl);
			} else {
				//常规
				$theurl = S_URL.$pre.$para;
			}
		}
	}

	//url缓存
	$_SGLOBAL['url_cache'][$cachekey] = $theurl;

	return $theurl;
}

function ehtml($type, $updatetime=0) {
	global $_SGLOBAL, $_SGET, $_SHTML, $_SCONFIG, $lang;

	if($type == 'get') {
		$_SGLOBAL['htmlfile']['updatetime'] = $updatetime;
		if(empty($_SGET['php']) && !empty($_SGLOBAL['htmlfile']['path']) && file_exists($_SGLOBAL['htmlfile']['path'])) {
			sheader($_SGLOBAL['htmlfile']['url']);
		}
	} else {
		if(empty($_SHTML['maxpage']) && !empty($_SGLOBAL['htmlfile']['path'])) {
			$content = $_SGLOBAL['content'];
			$theurl = S_URL_ALL.'/index.php?'.arraytostring($_SHTML);
			$codearr = array(
				'url' => rawurlencode($theurl),
				'maketime' => $_SGLOBAL['timestamp'],
				'updatetime' => $_SGLOBAL['htmlfile']['updatetime'],
				'uid' => empty($_SHTML['uid'])?0:$_SHTML['uid'],
				'itemid' => empty($_SHTML['itemid'])?0:$_SHTML['itemid'],
				'action' => $_SHTML['action']
			);

			$code = rawurlencode(implode('/', $codearr));
			$content .= '
			<script language="javascript">
			<!--
			var Modified = new Date(document.lastModified);
			var copyright = document.getElementById("xspace-copyright");
			if(copyright) {
				copyright.innerHTML += "Last update: <a href=\"'.$theurl.'/php/1\" title=\"'.$lang['the_page_can_be_updated_immediately_hits'].'\">"+(Modified.getYear()<200?(Modified.getYear()+1900):Modified.getYear())+"-"+(Modified.getMonth()+1)+"-"+Modified.getDate()+" "+Modified.getHours()+":"+Modified.getMinutes()+":"+Modified.getSeconds() + "</a><br>";
			}
			document.write(\'<script src="'.S_URL.'/batch.html.php?code='.$code.'&amp;lastmodified=\' + Modified.getTime() + \'" type="text\/javascript" language="javascript"><\/script>\');
			//-->
			</script>';

			writefile($_SGLOBAL['htmlfile']['path'], $content);
		}
	}
}

function ob_out() {
	global $_SGLOBAL, $_SCONFIG, $_SC;

	$_SGLOBAL['content'] = ob_get_contents();

	$preg_searchs = $preg_replaces = $str_searchs = $str_replaces = array();

	if($_SGLOBAL['inajax']) {
		$preg_searchs[] = "/([\x01-\x09\x0b-\x0c\x0e-\x1f])+/";
		$preg_replaces[] = ' ';

		$str_searchs[] = ']]>';
		$str_replaces[] = ']]&gt;';
	}

	if($_SCONFIG['urltype'] != 4 && $_SCONFIG['urltype'] != 5) {
		$preg_searchs[] = "/href\=\"(\S*?)\/(index\.php)?\?uid\-([0-9]+)\-?(\S*?)\"/i";
		$preg_replaces[] = 'href="\\1/?\\3/\\4"';
		$preg_searchs[] = "/href\=\"\S*?\/(index\.php)?\?(\S+?)\"/ie";
		$preg_replaces[] = "url_replace('\\2')";
	}

	if($preg_searchs) {
		$_SGLOBAL['content'] = preg_replace($preg_searchs, $preg_replaces, $_SGLOBAL['content']);
	}
	if($str_searchs) {
		$_SGLOBAL['content'] = trim(str_replace($str_searchs, $str_replaces, $_SGLOBAL['content']));
	}

	obclean();
	if($_SGLOBAL['inajax']) {
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		@header("Content-type: application/xml; charset=$_SC[charset]");
		echo '<'."?xml version=\"1.0\" encoding=\"$_SC[charset]\"?>\n";
		echo "<root><![CDATA[".trim($_SGLOBAL['content'])."]]></root>";
		exit();
	} else{
		if($_SCONFIG['headercharset']) {
			@header('Content-Type: text/html; charset='.$_SC['charset']);
		}
		echo $_SGLOBAL['content'];
		if(D_BUG) {
			@include_once(S_ROOT.'./include/debug.inc.php');
		}
	}
}

function url_replace($para, $quote=1) {
	global $_SCONFIG;

	$para = str_replace(
		array(
			'action-viewnews-itemid',
			'action-viewthread-tid',
			'action-category-catid',
			'action-index'
		),
		array(
			'viewnews',
			'viewthread',
			'category',
			''
		),
		$para
	);

	if($_SCONFIG['urltype'] == 3) {
		$pre = '/';
	} elseif($_SCONFIG['urltype'] == 2) {
		$pre = '/index.php/';
	} else {
		$pre = '/?';
	}

	if(empty($para)) {
		$para = '/';
	} elseif(substr($para, -1, 1) == '/' || $_SCONFIG['urltype'] == 3) {
		$para = $pre.$para;
		if($_SCONFIG['urltype'] == 3 && substr($para, -1, 1) != '/') {
			$para .= $_SCONFIG['pagepostfix'];
		}
	} else {
		$para = $pre.$para.$_SCONFIG['pagepostfix'];
	}

	return empty($quote)?S_URL.$para:'href="'.S_URL.$para.'"';
}

function url_remake($url) {
	$url = preg_replace("/(\S*)\/(index\.php)?\?uid\-([0-9]+)\-?(\S*)/i", '\\1/?\\3/\\4', $url);
	$url = preg_replace("/\S*\/(index\.php)?\?(\S+)/ie", "url_replace('\\2', 0)", $url);
	return $url;
}

function sgmdate($timestamp, $dateformat='') {
	global $_SCONFIG;

	if(empty($dateformat)) {
		$dateformat = 'Y-m-d H:i:s';
	}
	if(!empty($timestamp)) {
		return gmdate($dateformat, $timestamp + $_SCONFIG['timeoffset'] * 3600);
	} else {
		return '';
	}
}

//获得表
function tname($name, $mode=0) {
	global $_SC;
	if($mode == 1) {
		return (empty($_SC['dbname_bbs'])?'':'`'.$_SC['dbname_bbs'].'`.').'`'.$_SC['tablepre_bbs'].$name.'`';
	} elseif ($mode == 2) {
		return (empty($_SC['dbname_uch'])?'':'`'.$_SC['dbname_uch'].'`.').'`'.$_SC['tablepre_uch'].$name.'`';
	} else {
		return $_SC['tablepre'].$name;
	}
}

function authcode($string, $operation, $key = '', $expiry = 0) {
	global $_SGLOBAL, $_SCONFIG;

	$ckey_length = 4;	// 随机密钥长度 取值 0-32;
			// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
			// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
			// 当此值为 0 时，则不产生随机密钥

	$key = md5($key ? $key : $_SGLOBAL['authkey']);
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

function getcookie() {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_GET;

	$_SGLOBAL['supe_uid'] = 0;
	$_SGLOBAL['supe_username'] = 'Guest';
	$_SGLOBAL['member'] = array(
		'uid' => 0,
		'groupid' => 0,
		'username' => 'Guest',
		'password' => ''
	);
	$cookie = $_COOKIE[$_SC['cookiepre'].'auth'];
	if($cookie) {
		@list($password, $uid) = explode("\t", authcode($cookie, 'DECODE'));

		$uid = intval($uid);
		$password = addslashes($password);
		$_SGLOBAL['supe_uid'] = $uid;
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('members').' WHERE uid=\''.$_SGLOBAL['supe_uid'].'\' AND password=\''.$password.'\'');
		if($member = $_SGLOBAL['db']->fetch_array($query)) {
			$_SGLOBAL['member'] = $member;
			$_SGLOBAL['supe_username'] = addslashes($member['username']);
			$_SGLOBAL['email'] = addslashes($member['email']);
		} else {
			$_SGLOBAL['supe_uid'] = 0;
		}
	}
	if(empty($_SGLOBAL['supe_uid'])) sclearcookie();
	if(empty($_SGLOBAL['member']['timeoffset'])) $_SGLOBAL['member']['timeoffset'] = $_SCONFIG['timeoffset'];

	//用户组
	@include_once(S_ROOT.'./data/system/group.cache.php');
	if(empty($_SGLOBAL['grouparr'])) {
		include_once(S_ROOT.'./function/cache.func.php');
		updategroupcache();
	}
	if(!empty($_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']])) {
		$_SGLOBAL['group'] = $_SGLOBAL['grouparr'][$_SGLOBAL['member']['groupid']];
	}

	//用户名处理
	$_SGLOBAL['supe_username_show'] = $member['username'];
	$_SGLOBAL['supe_username'] = addslashes($member['username']);
}

//数组转换成字串
function arrayeval($array, $level = 0) {
	$space = '';
	$evaluate = "Array $space(";
	$comma = $space;
	foreach($array as $key => $val) {
		$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
		$val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
		if(is_array($val)) {
			$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",$space";
	}
	$evaluate .= "$space)";
	return $evaluate;
}

function sclearcookie() {
	global $_SGLOBAL;

	ssetcookie('sid', '', -86400 * 365);
	ssetcookie('auth', '', -86400 * 365);
	ssetcookie('sauth', '', -86400 * 365);
}

function ssetcookie($var, $value, $life=0) {
	global $_SGLOBAL, $_SC;

	setcookie($_SC['cookiepre'].$var, $value, $life?$_SGLOBAL['timestamp']+$life:0, $_SC['cookiepath'], $_SC['cookiedomain'], $_SERVER['SERVER_PORT']==443?1:0);
}

//连接数据库
function dbconnect($mode=0) {
	global $_SGLOBAL, $_SC;

	if(empty($_SGLOBAL['db'])) {
		include_once(S_ROOT.'./class/db_mysql.class.php');
		$_SGLOBAL['db'] = new dbstuff;
		$_SGLOBAL['db']->charset = $_SC['dbcharset'];
		$_SGLOBAL['db']->connect($_SC['dbhost'], $_SC['dbuser'], $_SC['dbpw'], $_SC['dbname'], $_SC['pconnect']);
	}
	if($mode==1) {
		if(empty($_SGLOBAL['db_bbs'])) {
			if($_SC['dbhost'] == $_SC['dbhost_bbs'] && ($_SC['dbcharset'] == $_SC['dbcharset_bbs'] || empty($_SC['dbcharset_bbs']))) {
				//同一台服务器
				$_SGLOBAL['db_bbs'] = $_SGLOBAL['db'];
			} else {
				//不同的mysql服务器
				include_once(S_ROOT.'./class/db_mysql.class.php');
				$newlink = $_SC['dbhost'] == $_SC['dbhost_bbs'] && $_SC['dbuser'] == $_SC['dbuser_bbs'] && $_SC['dbcharset'] != $_SC['dbcharset_bbs'] ? 1 : 0;
				$_SGLOBAL['db_bbs'] = new dbstuff;
				$_SGLOBAL['db_bbs']->charset = $_SC['dbcharset_bbs'];
				$_SGLOBAL['db_bbs']->connect($_SC['dbhost_bbs'], $_SC['dbuser_bbs'], $_SC['dbpw_bbs'], $_SC['dbname_bbs'], $_SC['pconnect_bbs'], $newlink);
			}
		}
	} elseif($mode==2) {
		if(empty($_SGLOBAL['db_uch'])) {
			if($_SC['dbhost'] == $_SC['dbhost_uch'] && ($_SC['dbcharset'] == $_SC['dbcharset_uch'] || empty($_SC['dbcharset_uch']))) {
				//同一台服务器
				$_SGLOBAL['db_uch'] = $_SGLOBAL['db'];
			} else {
				//不同的mysql服务器
				include_once(S_ROOT.'./class/db_mysql.class.php');
				$newlink = $_SC['dbhost'] == $_SC['dbhost_uch'] && $_SC['dbuser'] == $_SC['dbuser_uch'] && $_SC['dbcharset'] != $_SC['dbcharset_uch'] ? 1 : 0;
				$_SGLOBAL['db_uch'] = new dbstuff;
				$_SGLOBAL['db_uch']->charset = $_SC['dbcharset_uch'];
				$_SGLOBAL['db_uch']->connect($_SC['dbhost_uch'], $_SC['dbuser_uch'], $_SC['dbpw_uch'], $_SC['dbname_uch'], $_SC['pconnect_uch'], $newlink);
			}
		}
	}
}

function stripsearchkey($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = stripsearchkey($val);
		}
	} else {
		$string = trim($string);
		$string = str_replace('*', '%', addcslashes($string, '%_'));
		$string = str_replace('_', '\_', $string);
	}
	return $string;
}

function postget($var) {
	$value = '';
	if(isset($_POST[$var])) {
		$value = $_POST[$var];
	} elseif (isset($_GET[$var])) {
		$value = $_GET[$var];
	}
	return $value;
}

function obclean() {
	global $_SCONFIG;

	ob_end_clean();
	if ($_SCONFIG['gzipcompress'] && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

function showxml($text, $title='') {
	global $_SCONFIG, $lang;

	if(!empty($title)) {
		$text = '<h5><a href="javascript:;" onclick="document.getElementById(\'xspace-ajax-div\').style.display=\'none\';">'.$lang['close'].'</a>'.$title.'</h5><div class="xspace-ajaxcontent">'.$text.'</div>';
	}
	obclean();
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	header("Content-type: application/xml");
	echo "<?xml version=\"1.0\" encoding=\"$_SCONFIG[charset]\"?>\n";
	echo "<root><![CDATA[";
	echo $text;
	echo "]]></root>";
	exit;
}

//显示信息
function showmessage($message, $url_forward='', $second=1, $vars=array()) {
	global $_SGLOBAL, $_SCONFIG, $_SC, $channels;

	if(empty($_SGLOBAL['inajax']) && $url_forward && empty($second)) {
		//直接301跳转
		obclean();
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url_forward");
	} else {
		if(!defined('IN_SUPESITE_ADMINCP')) {
			$tpl_file = 'showmessage';
			$fullpath = 0;
			include_once(S_ROOT.'./language/message.lang.php');
			if(!empty($mlang[$message])) $message = $mlang[$message];
		} else {
			$tpl_file = 'admin/tpl/showmessage.htm';
			$fullpath = 1;
			include_once(S_ROOT.'./language/admincp_message.lang.php');
			if(!empty($amlang[$message])) $message = $amlang[$message];
		}

		if(isset($_SGLOBAL['mlang'][$message])) $message = $_SGLOBAL['mlang'][$message];
		foreach ($vars as $key => $val) {
			$message = str_replace('{'.$key.'}', $val, $message);
		}
		//显示
		obclean();
		if(!empty($url_forward)) {
			$second = $second * 1000;
			$message .= "<script>setTimeout(\"window.location.href ='$url_forward';\", $second);</script>";
		}

		include template($tpl_file, $fullpath);
		ob_out();
	}
	exit();
}

function secho($array, $eixt=1) {
	if(is_array($array)) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	} else {
		echo '<br>';
		echo shtmlspecialchars($array);
		echo '<br>';
	}
	if($eixt) exit();
}

function submitcheck($var, $checksec=0) {
	global $_SGLOBAL, $_SCONFIG;

	if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_POST['formhash'] == formhash()) {
			if(empty($_SCONFIG['noseccode']) && $checksec) {
				if(!empty($_POST['seccode'])) {
					if(ckseccode($_POST['seccode'])) {
						return true;
					}
					showmessage('incorrect_code');
				}
				return false;
			} else {
				return true;
			}
		} else {
			showmessage('submit_invalid');
		}
	} else {
		return false;
	}
}

function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}

function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function sheader($url){
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $url");
	exit();
}

function replacetable($tablename, $insertsqlarr) {
	global $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.$insert_key;
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	$_SGLOBAL['db']->query('REPLACE INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.') ');
}

//简单跳转的函数
function jumpurl($url, $time=1000, $mode='js') {
	if($mode == 'js') {
		echo "<script>
			function redirect() {
				window.location.replace('$url');
			}
			setTimeout('redirect();', $time);
			</script>";
	} else {
		$time = $time/1000;
		echo "<html><head><title></title><meta http-equiv=\"refresh\" content=\"$time;url=$url\"></head><body></body></html>";
	}
	exit;
}

//获取文件名后缀
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

//获取频道信息
function getchannels() {
	global $_SGLOBAL, $_SCONFIG, $lang;

	$channels = array('default'=>'', 'menus'=>array(), 'types'=>array());

	$_SGLOBAL['type'] = array();
	foreach ($_SCONFIG['channel'] as $value) {

		//默认频道文件
		if(!empty($_SCONFIG['defaultchannel']) && $value['nameid'] == $_SCONFIG['defaultchannel']) {
			if($value['type'] == 'user') {
				$channels['default'] = 'channel/channel_'.$value['nameid'].'.php';//默认频道
			} elseif($value['type'] == 'model') {
				$channels['default'] = 'm.php?name='.$value['nameid'];
			} elseif($value['nameid'] != 'index') {
				$channels['default'] = $value['nameid'].'.php';
			}
		}

		//处理默认链接和名称
		if(empty($value['url'])) {
			if($value['type'] == 'user') {
				$value['url'] = geturl("action/channel/name/$value[nameid]");
			} elseif ($value['type'] == 'model') {
				$value['url'] = S_URL.'/m.php?name='.$value['nameid'];
			} else {
				$value['url'] = geturl("action/$value[nameid]");
			}
		}
		if(empty($value['name'])) $value['name'] = $lang[$value['nameid']];

		$channels['menus'][$value['nameid']] = $value;//全部频道

		//获取系统频道
		if($value['type'] == 'type') {
			$channels['types'][$value['nameid']] = $value;//系统频道
			$_SGLOBAL['type'][] = $value['nameid'];
		}
	}

	return $channels;
}

function getmessagepic($message) {
	$pic = '';
	preg_match("/src\=[\"\']*([^\>\s]{25,105})\.(jpg|gif|png)/i", $message, $mathes);
	if(!empty($mathes[1]) || !empty($mathes[2])) {
		$pic = "{$mathes[1]}.{$mathes[2]}";
	}
	return $pic;
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

//推送feed
function postfeed($feed) {
	global $_SGLOBAL, $channels;

	require_once S_ROOT.'./language/feed.lang.php';
	require_once S_ROOT.'./uc_client/client.php';

	$feed['uid'] = !empty($feed['uid']) ? intval($feed['uid']) : $_SGLOBAL['supe_uid'];
	$feed['username'] = !empty($feed['username']) ? trim($feed['username']) : $_SGLOBAL['supe_username'];
	$feed['title_template'] = !empty($feed['title_template']) ? $flang[$feed['title_template']] : '';
	$feed['body_template'] = !empty($feed['title_template']) ? $flang[$feed['body_template']] : '';
	$feed['title_data'] = !empty($feed['title_data']) ? $feed['title_data'] : array();
	$feed['body_data'] = !empty($feed['body_data']) ? $feed['body_data'] : array();
	$feed['images'] = !empty($feed['images']) ? $feed['images'] : array();

	if(!empty($feed['uid'])) uc_feed_add($feed['icon'], $feed['uid'], $feed['username'], $feed['title_template'], $feed['title_data'], $feed['body_template'], $feed['body_data'], '', '', $feed['images']);
}

//是否显示允许加入事件选项
function allowfeed() {
	global $_SCONFIG, $_SGLOBAL;
	if(!empty($_SCONFIG['allowfeed'])) {
		return true;
	} else {
		return false;
	}
}

//判断事件选项是否是默认选中状态
function addfeedcheck($type) {
	global $space, $cacheinfo;
	if($type < 4) {
		if($space['customaddfeed'] & $type) {
			return true;
		}
	} elseif($type == 4 || $type == 8) {
		$type = $type/4;
		if(!empty($cacheinfo['models']['allowfeed']) && $cacheinfo['models']['allowfeed'] & $type) {
			return true;
		}
	}
	return false;
}

function censor($message, $mod=0) {
	global $_SGLOBAL;
	@include_once(S_ROOT.'/data/system/censor.cache.php');
	if(!empty($_SGLOBAL['censor']) && is_array($_SGLOBAL['censor'])) {
		if($mod == 0) {
			if($_SGLOBAL['censor']['banned'] && preg_match($_SGLOBAL['censor']['banned'], $message)) {
				showmessage('words_can_not_publish_the_shield');
			}
			if($_SGLOBAL['censor']['mod'] && preg_match($_SGLOBAL['censor']['mod'], $message)) {
				showmessage('words_can_not_publish_the_shield');
			}
		} else {
			if(!empty($_SGLOBAL['censor']['banned'])) {
				$message = @preg_replace($_SGLOBAL['censor']['banned'], '**', $message);
			}

			if(!empty($_SGLOBAL['censor']['mod'])) {
				$message = @preg_replace($_SGLOBAL['censor']['mod'], '**', $message);
			}
		}

		if(!empty($_SGLOBAL['censor']['filter'])) {
			$message = @preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $message);
		}

	}
	return $message;
}

//读文件
function sreadfile($filename, $mode='r', $remote=0, $maxsize=0, $jumpnum=0) {
	if($jumpnum > 5) return '';
	$contents = '';

	if($remote) {
		$httpstas = '';
		$urls = initurl($filename);
		if(empty($urls['url'])) return '';

		$fp = @fsockopen($urls['host'], $urls['port'], $errno, $errstr, 20);
		if($fp) {
			if(!empty($urls['query'])) {
				fputs($fp, "GET $urls[path]?$urls[query] HTTP/1.1\r\n");
			} else {
				fputs($fp, "GET $urls[path] HTTP/1.1\r\n");
			}
			fputs($fp, "Host: $urls[host]\r\n");
			fputs($fp, "Accept: */*\r\n");
			fputs($fp, "Referer: $urls[url]\r\n");
			fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 5.00; Windows 98)\r\n");
			fputs($fp, "Pragma: no-cache\r\n");
			fputs($fp, "Cache-Control: no-cache\r\n");
			fputs($fp, "Connection: Close\r\n\r\n");

			$httpstas = explode(" ", fgets($fp, 128));
			if($httpstas[1] == 302 || $httpstas[1] == 302) {
				$jumpurl = explode(" ", fgets($fp, 128));
				return sreadfile(trim($jumpurl[1]), 'r', 1, 0, ++$jumpnum);
			} elseif($httpstas[1] != 200) {
				fclose($fp);
				return '';
			}

			$length = 0;
			$size = 1024;
			while (!feof($fp)) {
				$line = trim(fgets($fp, 128));
				$size = $size + 128;
				if(empty($line)) break;
				if(strexists($line, 'Content-Length')) {
					$length = intval(trim(str_replace('Content-Length:', '', $line)));
					if(!empty($maxsize) && $length > $maxsize) {
						fclose($fp);
						return '';
					}
				}
				if(!empty($maxsize) && $size > $maxsize) {
					fclose($fp);
					return '';
				}
			}
			fclose($fp);

			if(@$handle = fopen($urls['url'], $mode)) {
				if(function_exists('stream_get_contents')) {
					$contents = stream_get_contents($handle);
				} else {
					$contents = '';
					while (!feof($handle)) {
						$contents .= fread($handle, 8192);
					}
				}
				fclose($handle);
			} elseif(@$ch = curl_init()) {
				curl_setopt($ch, CURLOPT_URL, $urls['url']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);//timeout
				$contents = curl_exec($ch);
				curl_close($ch);
			} else {
				//无法远程上传
			}
		}
	} else {
		if(@$handle = fopen($filename, $mode)) {
			$contents = fread($handle, filesize($filename));
			fclose($handle);
		}
	}

	return $contents;
}

//写文件
function writefile($filename, $writetext, $filemod='text', $openmod='w', $eixt=1) {
	if(!@$fp = fopen($filename, $openmod)) {
		if($eixt) {
			exit('File :<br>'.srealpath($filename).'<br>Have no access to write!');
		} else {
			return false;
		}
	} else {
		$text = '';
		if($filemod == 'php') {
			$text = "<?php\r\n\r\nif(!defined('IN_SUPESITE')) exit('Access Denied');\r\n\r\n";
		}
		$text .= $writetext;
		if($filemod == 'php') {
			$text .= "\r\n\r\n?>";
		}
		flock($fp, 2);
		fwrite($fp, $text);
		fclose($fp);
		return true;
	}
}

function initurl($url) {

	$newurl = '';
	$blanks = array('url'=>'');
	$urls = $blanks;

	if(strlen($url)<10) return $blanks;
	$urls = @parse_url($url);
	if(empty($urls) || !is_array($urls)) return $blanks;
	if(empty($urls['scheme'])) return $blanks;
	if($urls['scheme'] == 'file') return $blanks;

	if(empty($urls['path'])) $urls['path'] = '/';
	$newurl .= $urls['scheme'].'://';
	$newurl .= empty($urls['user'])?'':$urls['user'];
	$newurl .= empty($urls['pass'])?'':':'.$urls['pass'];
	$newurl .= empty($urls['host'])?'':((!empty($urls['user']) || !empty($urls['pass']))?'@':'').$urls['host'];
	$newurl .= empty($urls['port'])?'':':'.$urls['port'];
	$newurl .= empty($urls['path'])?'':$urls['path'];
	$newurl .= empty($urls['query'])?'':'?'.$urls['query'];
	$newurl .= empty($urls['fragment'])?'':'#'.$urls['fragment'];

	$urls['port'] = empty($urls['port'])?'80':$urls['port'];
	$urls['url'] = $newurl;

	return $urls;
}

//编译模板文件
function template($tplfile, $fullpath=0) {
	global $_SCONFIG;

	if(empty($fullpath)) {
		$filename = 'templates/'.$_SCONFIG['template'].'/'.$tplfile.'.html.php';
		$objfile = S_ROOT.'./cache/tpl/tpl_'.$_SCONFIG['template'].'_'.$tplfile.'.php';
		$tplfile = S_ROOT.'./'.$filename;
	} else {
		$filename = $tplfile;
		$objfile = str_replace('/', '_', $filename);
		$objfile = S_ROOT.'./cache/tpl/tpl_'.$objfile.'.php';
		$tplfile = S_ROOT.'./'.$filename;
	}

	$tplrefresh = 1;
	if(file_exists($objfile)) {
		if(empty($_SCONFIG['tplrefresh'])) {
			$tplrefresh = 0;
		} else {
			if(@filemtime($tplfile) <= @filemtime($objfile)) {
				$tplrefresh = 0;
			}
		}
	}

	if($tplrefresh) {
		include_once(S_ROOT.'./function/template.func.php');
		parse_template($tplfile, $objfile);
	}
	return $objfile;
}

//格式化路径
function srealpath($path) {
	$path = str_replace('./', '', $path);
	if(DIRECTORY_SEPARATOR == '\\') {
		$path = str_replace('/', '\\', $path);
	} elseif(DIRECTORY_SEPARATOR == '/') {
		$path = str_replace('\\', '/', $path);
	}
	return $path;
}

//从数据表取的CACHE的变量
function getcache($cachekey, $tablename) {
	global $_SGLOBAL, $_SBLOCK, $_SCONFIG;

	if(empty($_SCONFIG['cachegrade'])) $_SCONFIG['cachegrade'] = 1;

	if($_SCONFIG['allowcache'] && !empty($cachekey) && empty($_SBLOCK[$cachekey])) {
		if($_SCONFIG['cachemode'] == 'file') {
			$cachefile = S_ROOT.'./cache/block/'.substr($cachekey, 0, $_SCONFIG['cachegrade']).'/'.$cachekey.'.cache.data';
			if(file_exists($cachefile)) {
				if(@$fp = fopen($cachefile, 'r')) {
					$data = fread($fp,filesize($cachefile));
					fclose($fp);
				}
				$_SBLOCK[$cachekey]['value'] = $data;
				$_SBLOCK[$cachekey]['filemtime'] = filemtime($cachefile);
			}
		} else {
			if(!isset($_SGLOBAL['mkcachetables'])) $_SGLOBAL['mkcachetables'] = array();
			$thetable = tname($tablename.'_'.substr($cachekey, 0, $_SCONFIG['cachegrade']));
			if($query = $_SGLOBAL['db']->query('SELECT * FROM '.$thetable.' WHERE cachekey = \''.$cachekey.'\'', 'SILENT')) {
				while($result = $_SGLOBAL['db']->fetch_array($query)) {
					$_SBLOCK[$result['cachekey']]['value'] = $result['value'];
					$_SBLOCK[$result['cachekey']]['updatetime'] = $result['updatetime'];
				}
			} else {
				$_SGLOBAL['mkcachetables'][] = $thetable;
			}
		}
	}
}

//模块
function block($thekey, $param) {
	global $_SGLOBAL, $_SBLOCK, $_SCONFIG, $_SGET, $lang;

	$_SBLOCK[$thekey] = array();
	$havethekey = false;
	$needcache = 0;

	//缓存key
	$cachekey = smd5($thekey.$param);

	$paramarr = parseparameter($param, 0);
	if(!empty($paramarr['uid'])) {
		$uid = $paramarr['uid'];
	} elseif (!empty($paramarr['authorid'])) {
		$uid = $paramarr['authorid'];
	} else {
		$uid = 0;
	}

	if(!empty($paramarr['cachetime'])) {
		if(!empty($paramarr['perpage']) && !empty($_SGET['page'])) {
			//分页
			$cachekey = smd5($thekey.$param.$_SGET['page']);
		}
		$cacheupdatetime = $paramarr['cachetime'];
	} else {
		$cacheupdatetime = 0;
		$needcache = 3;//DO NOT CACHE
	}

	if($cacheupdatetime) {
		//获取缓存
		$tablename = ($thekey == 'spacetag')?'tagcache':'cache';
		getcache($cachekey, $tablename);

		if(!isset($_SBLOCK[$cachekey])) {
			$needcache = 1;//没有缓存
		} else {
			//创建下次更新时间
			if(!empty($_SBLOCK[$cachekey]['filemtime'])) $_SBLOCK[$cachekey]['updatetime'] = $_SBLOCK[$cachekey]['filemtime'] + $cacheupdatetime;
			if($_SBLOCK[$cachekey]['updatetime'] < $_SGLOBAL['timestamp']) {
				$needcache = 2;//需要更新
			}
		}
	}

	if($needcache) {
		$theblockarr = array();

		include_once(S_ROOT.'./function/block.func.php');
		$block_func = 'block_'.$thekey;
		$theblockarr = $block_func($paramarr);

		$_SBLOCK[$thekey] = $theblockarr;
		$havethekey = true;
		$_SBLOCK[$cachekey]['value'] = serialize($theblockarr);
		$_SBLOCK[$cachekey]['updatetime'] = $_SGLOBAL['timestamp'] + $cacheupdatetime;

		if($needcache == 1 || $needcache == 2) {
			//INSERT-UPDATE
			$_SGLOBAL['tpl_blockvalue'][] = array(
				'cachekey' => $cachekey,
				'uid' => $uid,
				'cachename' => $thekey,
				'value' => $_SBLOCK[$cachekey]['value'],
				'updatetime' => $_SBLOCK[$cachekey]['updatetime']
			);
		}
	}

	if(!$havethekey) {
		if(!empty($_SBLOCK[$cachekey]['value'])) {
			$_SBLOCK[$thekey] = unserialize($_SBLOCK[$cachekey]['value']);
		} else {
			$_SBLOCK[$thekey] = array();
		}
	}

	$iarr = $_SBLOCK[$thekey];
	if(!empty($paramarr['cachename'])) {
		if(empty($_SBLOCK[$thekey]['multipage'])) {
			$_SBLOCK[$paramarr['cachename'].'_multipage'] = '';
		} else {
			$_SBLOCK[$paramarr['cachename'].'_multipage'] = $_SBLOCK[$thekey]['multipage'];
		}
		$_SBLOCK[$paramarr['cachename']] = $_SBLOCK[$thekey];
		unset($_SBLOCK[$paramarr['cachename']]['multipage']);
	}

	if(!empty($paramarr['tpl']) && $paramarr['tpl'] != 'data') {
		$paramarr['tpl'] = 'styles/'.$paramarr['tpl'].'.html.php';
		include template($paramarr['tpl'], 1);
	}

}

//更新指定表中的cachekey
function maketplblockvalue($tablename='cache') {
	global $_SGLOBAL, $_SCONFIG, $dbcharset;

	if(empty($_SCONFIG['cachegrade'])) $_SCONFIG['cachegrade'] = 1;

	if($_SCONFIG['allowcache'] && !empty($_SGLOBAL['tpl_blockvalue'])) {
		if($_SCONFIG['cachemode'] == 'file') {
			//文本存储
			foreach ($_SGLOBAL['tpl_blockvalue'] as $tplvalue) {
				$cachedir = S_ROOT.'./cache/block/'.substr($tplvalue['cachekey'], 0, $_SCONFIG['cachegrade']);
				$dircheck = false;
				if(!is_dir($cachedir)) {
					if(@mkdir($cachedir)) {
						$dircheck = true;
					}
				} else {
					$dircheck = true;
				}
				if($dircheck) {
					$cachefile = $cachedir.'/'.$tplvalue['cachekey'].'.cache.data';
					if(@$fp = fopen($cachefile, 'w')) {
						fwrite($fp, $tplvalue['value']);
						fclose($fp);
					}
				}
			}
		} else {
			//缓存分表
			if(!empty($_SGLOBAL['mkcachetables'])) {
				$enginetype = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(empty($dbcharset)?"":" DEFAULT CHARSET=$dbcharset" ): " TYPE=MYISAM";
				foreach ($_SGLOBAL['mkcachetables'] as $thetable) {
					if(!strexists($thetable, 'cache')) continue;//表名必须含有cache
					$creatsql = "CREATE TABLE `$thetable` (
						cachekey varchar(16) NOT NULL default '',
						uid mediumint(8) unsigned NOT NULL default '0',
						cachename varchar(20) NOT NULL default '',
						`value` mediumtext NOT NULL,
						updatetime int(10) unsigned NOT NULL default '0',
						PRIMARY KEY  (cachekey)
					) $enginetype";
					$_SGLOBAL['db']->query($creatsql, 'SILENT');//创建分表
				}
			}
			$insertsqls = array();
			$thetables = array();
			foreach ($_SGLOBAL['tpl_blockvalue'] as $tplvalue) {
				$thetable = tname($tablename.'_'.substr($tplvalue['cachekey'], 0, $_SCONFIG['cachegrade']));
				$thetables[] = $thetable;
				$insertsqls[$thetable][] = '(\''.addslashes($tplvalue['cachekey']).'\', \''.$tplvalue['uid'].'\', \''.addslashes($tplvalue['cachename']).'\', \''.addslashes($tplvalue['value']).'\', \''.$tplvalue['updatetime'].'\')';
			}
			foreach ($thetables as $thetable) {
				$_SGLOBAL['db']->query('REPLACE INTO '.$thetable.' (cachekey, uid, cachename, value, updatetime) VALUES '.implode(',', $insertsqls[$thetable]), 'SILENT');
			}
		}
	}
}

function smd5($str) {
	return substr(md5($str), 8, 16);
}

function snl2br($message) {
	return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

//替换字符串中的特殊字符
//去掉指定字符串中\\或\'前的\
function sstripslashes($string) {

	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

//得到加上BBS的URL的路径,将$para中的键名和键值作为参数传递
function getbbsurl($scriptname, $para=array()) {
	$str = '';
	$comma = '?';
	if(is_array($para) && $para) {
		foreach ($para as $key => $value) {
			$str .= $comma.$key.'='.rawurlencode($value);
			$comma = '&';
		}
	}
	$scriptname .= $str;

	return B_URL.'/'.$scriptname;
}

function selecttable($tablename, $selectsqlarr, $wheresqlarr, $plussql='') {
	global $_SGLOBAL;

	$selectsql = $comma = '';
	if(count($selectsqlarr)) {
		foreach ($selectsqlarr as $select_key => $select_value) {
			$selectsql .= $comma.$select_value;
			$comma = ', ';
		}
	} else {
		$selectsql = '*';
	}

	$results = array();
	$query = $_SGLOBAL['db']->query('SELECT '.$selectsql.' FROM '.tname($tablename).' WHERE '.getwheresql($wheresqlarr).' '.$plussql);
	while ($r_array = $_SGLOBAL['db']->fetch_array($query)) {
		$results[] = $r_array;
	}
	return $results;
}

function inserttable($tablename, $insertsqlarr, $returnid=0, $replace = false, $silent=0) {
	global $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.'`'.$insert_key.'`';
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	$method = $replace?'REPLACE':'INSERT';
	$_SGLOBAL['db']->query($method.' INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.') ', $silent?'SILENT':'');
	if($returnid && !$replace) {
		return $_SGLOBAL['db']->insert_id();
	}
}

function deletetable($tablename, $wheresqlarr) {
	global $_SGLOBAL;

	if(empty($wheresqlarr)) {
		$_SGLOBAL['db']->query('TRUNCATE TABLE '.tname($tablename));
	} else {
		$_SGLOBAL['db']->query('DELETE FROM '.tname($tablename).' WHERE '.getwheresql($wheresqlarr));
	}
}

function updatetable($tablename, $setsqlarr, $wheresqlarr) {
	global $_SGLOBAL;

	$setsql = $comma = '';
	foreach ($setsqlarr as $set_key => $set_value) {
		$setsql .= $comma.$set_key.'=\''.$set_value.'\'';
		$comma = ', ';
	}
	$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET '.$setsql.' WHERE '.getwheresql($wheresqlarr));
}

function getwheresql($wheresqlarr) {
	$result = $comma = '';
	if(empty($wheresqlarr)) {
		$result = '1';
	} elseif(is_array($wheresqlarr)) {
		foreach ($wheresqlarr as $key => $value) {
			$result .= $comma.$key.'=\''.$value.'\'';
			$comma = ' AND ';
		}
	} else {
		$result = $wheresqlarr;
	}
	return $result;
}

//格式化大小函数,根据字节数自动显示成'KB','MB'等等
function formatsize($size, $prec=3) {
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

//写错误日志函数
function errorlog($type, $message, $halt = 0) {
	global $_SGLOBAL;
	@$fp = fopen(S_ROOT.'./log/errorlog.php', 'a');
	@fwrite($fp, "<?exit?>$_SGLOBAL[timestamp]\t$type\t$_SGLOBAL[supe_uid]\t".str_replace(array("\r", "\n"), array(' ', ' '), trim(shtmlspecialchars($message)))."\n");
	@fclose($fp);
	if($halt) {
		exit();
	}
}

//调试信息,显示进程处理时间
function debuginfo($echo=1) {
	global $_SGLOBAL, $_SCONFIG;

	$info = '';
	if($_SCONFIG['debug']) {
		$mtime = explode(' ', microtime());
		$totaltime = number_format(($mtime[1] + $mtime[0] - $_SGLOBAL['supe_starttime']), 6);
		$info .= 'Processed in '.$totaltime.' second(s), '.$_SGLOBAL['db']->querynum.' queries'.
			($_SCONFIG['gzipcompress'] ? ', Gzip enabled' : NULL);
		$info .= '<br />';
	}
	if(!empty($_SCONFIG['miibeian'])) {
		$info .= '<a href="http://www.miibeian.gov.cn" target="_blank">'.$_SCONFIG['miibeian'].'</a><br />';
	}
	if($echo) {
		echo $info;
	} else {
		return $info;
	}
}

function jsstrip($message) {
	$message = addcslashes($message, '/"\\');
	$message = preg_replace("/([\r\n]+)/i", '\n', $message);
	return trim($message);
}

function cutstr($string, $length, $havedot=0) {
	global $_SCONFIG;

	//判断长度
	if(strlen($string) <= $length) {
		return $string;
	}

	$wordscut = '';
	if(strtolower($_SCONFIG['charset']) == 'utf-8') {
		//utf8编码
		$n = 0;
		$tn = 0;
		$noc = 0;
		while ($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n++;
				$noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n++;
			}
			if ($noc >= $length) {
				break;
			}
		}
		if ($noc > $length) {
			$n -= $tn;
		}
		$wordscut = substr($string, 0, $n);
	} else {
		for($i = 0; $i < $length - 3; $i++) {
			if(ord($string[$i]) > 127) {
				$wordscut .= $string[$i].$string[$i + 1];
				$i++;
			} else {
				$wordscut .= $string[$i];
			}
		}
	}
	//省略号
	if($havedot) {
		return $wordscut.'...';
	} else {
		return $wordscut.'&nbsp;';
	}
}

//生成分页URL地址集合
function multi($num, $perpage, $curpage, $mpurl, $phpurl=1) {

	global $_SHTML, $lang, $_SGLOBAL;
	if(($curpage-1)*$perpage > $num) showmessage('start_listcount_error');
	
	$maxpages = $_SGLOBAL['maxpages'];
	$multipage = $a_name = '';
	if($phpurl) {
		$mpurl .= strpos($mpurl, '?') ? '&' : '?';
	} else {
		$urlarr = $mpurl;
		unset($urlarr['php']);
		unset($urlarr['modified']);
	}
	if($num > $perpage) {
		$page = 10;
		$offset = 2;
		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $curpage + $page - $offset - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if(($to - $from) < $page && ($to - $from) < $pages) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $curpage - $pages + $to;
				$to = $pages;
				if(($to - $from) < $page && ($to - $from) < $pages) {
					$from = $pages - $page + 1;
				}
			}
		}

		if($phpurl) {
			$url = $mpurl.'page=1'.$a_name;
			$url2 = $mpurl.'page='.($curpage - 1).$a_name;
		} else {
			$urlarr['page'] = 1;
			$url = geturl(arraytostring($urlarr)).$a_name;
			$urlarr['page'] = $curpage - 1;
			$url2 = geturl(arraytostring($urlarr)).$a_name;
		}

		$multipage = '<div class="pages"><div>'.($curpage - $offset > 1 && $pages > $page ? '<a href="'.$url.'">1...</a>' : '').($curpage > 1 ? '<a class="prev" href="'.$url2.'">'.$lang['pre_page'].'</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			if($phpurl) {
				$url = $mpurl.'page='.$i.$a_name;
			} else {
				$urlarr['page'] = $i;
				if($urlarr['page'] == 1) unset($urlarr['page']);
				$url = geturl(arraytostring($urlarr)).$a_name;
			}
			$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' : '<a href="'.$url.'">'.$i.'</a>';
		}

		if($phpurl) {
			$url = $mpurl.'page='.($curpage + 1).$a_name;
			$url2 = $mpurl.'page='.$pages.$a_name;
		} else {
			$urlarr['page'] = $curpage + 1;
			if($urlarr['page'] == 1) unset($urlarr['page']);
			$url = geturl(arraytostring($urlarr)).$a_name;
			$urlarr['page'] = $pages;
			if($urlarr['page'] == 1) unset($urlarr['page']);
			$url2 = geturl(arraytostring($urlarr)).$a_name;
		}

		$multipage .= ($to < $pages && $curpage < $maxpages ? '<a href="'.$url2.'" target="_self">...'.$realpages.'</a>' : '').
			($curpage < $pages ? '<a class="next" href="'.$url.'">'.$lang['next_page'].'</a>' : '').
			($pages > $page ? '' : '');
		$multipage .= '</div></div>';
		}

	return $multipage;
}

//跟SGMDATE函数对应
function sdate($dateformat, $timestamp) {
	echo sgmdate($timestamp, $dateformat);
}

//预览附件
function showpreviewimg($attach) {
	global $_SCONFIG;

	$img = '';
	if($attach['isimage']) {
		if(!empty($attach['thumbpath'])) {
			$filepath = $attach['thumbpath'];
		} else {
			$filepath = $attach['filepath'];
		}
		$img = '<img src="'.A_URL.'/'.$filepath.'" width="60" border="0" id="img'.$attach['aid'].'">';
	} else {
		$img = '<img src="images/base/attachment.gif" border="0">';
	}
	return '<a href="batch.download.php?aid='.$attach['aid'].'" target="_blank">'.$img.'</a>';
}

//$value为频道或者广告ID， style为广告投放位置, pagetype广告投放页面
function getad($type, $value, $pagetype='') {
	global $_SGLOBAL, $_SCONFIG;

	$adarr = $paramarr = $advlist = $adresult = array();
	$advhtml = $adpageout = '';
	$advcount = 0;
	if($type == 'system') {
		if($value == 'space') {
			@include_once S_ROOT .'./data/system/adspace.cache.php';
		}else{
			@include_once S_ROOT .'./data/system/adsystem.cache.php';
		}
		//判断此频道是否有广告
		if(!empty($_SGLOBAL['ad'][$value]) || !empty($_SGLOBAL['ad']['all'])){
			if(!empty($_SGLOBAL['ad']['all']) && !empty($_SGLOBAL['ad'][$value])) {
				$adarr = array_merge($_SGLOBAL['ad']['all'],$_SGLOBAL['ad'][$value]);
			}elseif(!empty($_SGLOBAL['ad'][$value])){
				$adarr = $_SGLOBAL['ad'][$value];
			}else{
				$adarr = $_SGLOBAL['ad']['all'];
			}
			foreach($adarr as $key=>$advalue) {
				$paramarr = $advalue['parameters'];
				if(!empty($paramarr['endtime']) && (strtotime($paramarr['startime']) > ($_SGLOBAL['timestamp']+$_SCONFIG['timeoffset']*3600) || (strtotime($paramarr['endtime']) < $_SGLOBAL['timestamp']+$_SCONFIG['timeoffset']*3600)))  {
					continue;
				} else {
					//获取广告代码
					switch($advalue['adtype']) {
						case 'text':
							$advhtml = '<a href="'.$paramarr['texturl'].'" target="_blank" style="font-size: '.$paramarr['fontsize'].'px;">'.stripslashes($paramarr['textcontent']).'</a>';
							break;
						case 'code':
							$advhtml = stripslashes($paramarr['adcodecontent']);
							break;
						case 'image':
							$advhtml = '<a href="'.$paramarr['imageurl'].'" target="_blank"><img src="'.$paramarr['imagesrc'].'" width="'.$paramarr['imagewidth'].'px" height="'.$paramarr['imageheight'].'px" alt="'.$paramarr['imagetext'].'" /></a>';
							break;
						case 'flash':
							$advhtml  = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" adcodebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,45,0" width="'.$paramarr['flashwidth'].'px" height="'.$paramarr['flashheight'].'px">';
							$advhtml .= '<param name="movie" value="'.stripslashes($paramarr['flashsrc']).'" />';
							$advhtml .= '<param name="quality" value="high" />';
							$advhtml .= '<embed src="'.stripslashes($paramarr['flashsrc']).'" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="'.$paramarr['flashwidth'].'px" height="'.$paramarr['flashheight'].'px"></embed>';
							$advhtml .= '</object>';
					}
					if(!empty($pagetype) && in_array($pagetype, explode("\t", $advalue['pagetype'])) || in_array('all', explode("\t", $advalue['pagetype']))) {
						if($advalue['style'] == 'pageoutindex' || $advalue['style'] == 'all') {
							$adpageout = addcslashes($advhtml , '/"\\');
							$adpageout = str_replace("\n",  '<br />',$adpageout);
							$adpageout = str_replace("\r",  '',$adpageout);
							$adpageout = <<<EOF
<script type="text/javascript">
function openwin() {
	OpenWindow=window.open("", (window.name!="newwin")?"newwin":"", "height={$paramarr[outwidth]}px, width={$paramarr[outheight]}px,toolbar=no ,scrollbars=no,menubar=no");
	OpenWindow.document.write("$adpageout");
	OpenWindow.document.close();
}
openwin();
</script>
EOF;
						}
					} else {
						continue;
					}
					switch($advalue['style']) {
						case 'pageoutindex' :
							$advlist['pageoutindex'][] = $adpageout;
							break;
						case 'all' :
							$advlist['pageheadad'][] = $advhtml;
							$advlist['pagecenterad'][] = $advhtml;
							$advlist['pagefootad'][] = $advhtml;
							$advlist['pagemovead'][] = $advhtml;
							$advlist['pageoutad'][] = $advhtml;
							$advlist['siderad'][] = $advhtml;
							$advlist['viewinad'][] = $advhtml;
							$advlist['pageoutindex'][] = $adpageout;
							break;
						default :
							$advlist[$advalue['style']][] = $advhtml;
					}
				}
			}
			foreach($advlist as $key=>$value) {
				$advcount = count($value);
				if($advcount>0) {
					$adresult[$key] = $advcount > 1 ? $value[mt_rand(0, $advcount -1)] : $value[0];
				}
			}
			if(!empty($adresult)) {
				return $adresult;
			} elseif(empty($adresult)){
				return '';
			}
		} else {
			return '';
		}
	} elseif($type == 'user') {
		@include_once S_ROOT .'./data/system/aduser.cache.php';
		$adid = intval($value);
		$adarr = $_SGLOBAL['ad'][$adid];
		$paramarr = $adarr['parameters'];
		if(!empty($paramarr['endtime']) && (strtotime($paramarr['startime']) > ($_SGLOBAL['timestamp']+$_SCONFIG['timeoffset']*3600) || (strtotime($paramarr['endtime']) < $_SGLOBAL['timestamp']+$_SCONFIG['timeoffset']*3600))) {
			return '';
		} else {
			return $paramarr['adechocontent'];
		}
	}
}

//将已上传的附件插入HTML编辑器
function getuploadinserthtml($uploadarr, $noinsert=0, $theaid=0) {
	global $_SCONFIG, $lang, $_SGLOBAL;

	$inserthtml = '';
	$imgstr = '';
	$echojs = true;
	$js = '';
	if(!empty($uploadarr) && is_array($uploadarr)) {
		$inserthtml .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';

		foreach ($uploadarr as $listvalue) {
			$isimg = false;
			$str = '';
			if(in_array($listvalue['attachtype'], array('jpg','jpeg','gif','png', 'bmp'))) {
				$isimg = true;
			}
			if(!$noinsert && $isimg) {
				if($echojs) {
					$js = '<script>setdefaultpic();</script>';
					$echojs = false;
				}
				$str = '<input name="picid" type="radio" id="picid" value="'.$listvalue['aid'].'" />';
			}
			$listvalue['uidcode'] = authcode($listvalue['uid'].'|'.$listvalue['aid'], 'ENCODE');
			$subjectimg = $listvalue['thumbpath'];
			$listvalue['fileurl'] = A_URL.'/'.$listvalue['filepath'];
			$listvalue['thumburl'] = A_URL.'/'.$listvalue['thumbpath'];
			$listvalue['size'] = formatsize($listvalue['size']);
			$listvalue['dateline'] = sgmdate($listvalue['dateline']);

			$inserthtml .= '<tr>';
			$inserthtml .= '<td style="width:60px">'.showpreviewimg($listvalue).'</td>';

			if(!empty($theaid) && $theaid == $listvalue['aid']) {
				$divsubject = '<input type="text" name="editsubject" id="editsubject" size="40" value="'.$listvalue['subject'].'" /><a href="javascript:;" onClick="attacheditsubmit('.$listvalue['aid'].')"><img src="admin/images/icon_succ.gif" style="width:22px;height:23px;border:0px" align="absmiddle" alt="OK" /></a>';
			} else {
				$divsubject = $listvalue['subject'];
			}
			$inserthtml .= '<td><div>'.$str.'<span id="div_upload_'.$listvalue['aid'].'" style="font-weight:bold">'.$divsubject.'</span></div>';
			$inserthtml .= '<a href="'.$listvalue['fileurl'].'" target="_blank">'.$listvalue['filename'].'</a> ('.$listvalue['size'].')<br>';
			$inserthtml .= '<img src="admin/images/action_icon_edit.gif" style="width:16px;height:15px" align="absmiddle" border="0" /> <a href="batch.upload.php?action=edit&noinsert='.$noinsert.'&aid='.$listvalue['aid'].'&uc='.rawurlencode($listvalue['uidcode']).'" target="phpframe">'.$lang['edit'].'</a>';
			$inserthtml .= ' | <a href="batch.upload.php?action=delete&noinsert='.$noinsert.'&aid='.$listvalue['aid'].'&uc='.rawurlencode($listvalue['uidcode']).'" target="phpframe">'.$lang['delete'].'</a>';

			if(!$noinsert) {
				$thehtmlsmallpic = '';
				$attachurl = S_URL.'/batch.download.php?aid='.$listvalue['aid'];
				if($listvalue['isimage']) {
					$thehtml = '<a href="'.$attachurl.'" target="_blank"><img src="'.$listvalue['fileurl'].'" border="0"></a>';
					$thehtmlsmallpic = '<a href="'.$attachurl.'" target="_blank"><img src="'.$listvalue['thumburl'].'" border="0"></a>';
				} else {
					$thehtml = '<a href="'.$attachurl.'" target="_blank"><img src="'.S_URL.'/images/base/attachment.gif" border="0"> '.$listvalue['filename'].'('.$listvalue['size'].')</a>';
				}
				$inserthtml .= ' | <a href="javascript:;" onClick="insertHtml(\''.shtmlspecialchars($thehtml).'\');return false;">'.($isimg?$lang['insert']:$lang['insert_attachments']).'</a>';
				if(!empty($thehtmlsmallpic)) {
					$inserthtml .= ' | <a href="javascript:;" onClick="insertHtml(\''.shtmlspecialchars($thehtmlsmallpic).'\');return false;">'.$lang['insertsmall'].'</a>';
				}
			}
			if($listvalue['attachtype'] == 'jpg' || $listvalue['attachtype'] == 'jpeg') {
				//为保障数据合法生成的校验key
				if(!empty($listvalue['type'])) {
					$_POST['thumbwidth'] = $_SCONFIG['thumbarray'][$listvalue['type']][0];
					$_POST['thumbheight'] = $_SCONFIG['thumbarray'][$listvalue['type']][1];
				}
				$imageauthcode=md5(A_DIR.'/'.$listvalue['filepath'].$_SCONFIG['sitekey'].intval($_POST['thumbwidth']).$listvalue['aid'].intval($_POST['thumbheight']).$_SGLOBAL['authkey'].$listvalue['thumbpath']);
				$inserthtml .= ' | <a href="'.S_URL.'/batch.epitome.php?img='.urlencode(A_DIR.'/'.$listvalue['filepath']).'&imageauthcode='.$imageauthcode.'&imgw='.intval($_POST['thumbwidth']).'&imgh='.intval($_POST['thumbheight']).'&thumbimg='.urlencode($listvalue['thumbpath']).'&id='.urlencode($listvalue['aid']).'" target="_blank">'.$lang['slice'].'</a>';
			}
			$inserthtml .= '<input name="divupload[]" type="hidden" value="'.$listvalue['aid'].'" />';
			$inserthtml .= '</td></tr>';
		}

		$inserthtml .= '</table>'.$imgstr.$js;
	}
	return $inserthtml;
}

//生成下拉框
function getselectstr($var, $optionarray, $value='', $other='') {
	global $_SGET;

	$selectstr = '<select id="'.$var.'" name="'.$var.'"'.$other.'>';
	foreach ($optionarray as $optionkey => $optionvalue) {
		$selectstr .= '<option value="'.$optionkey.'">'.$optionvalue.'</option>';
	}
	if($value=='' && isset($_SGET[$var])) {
		$value = $_SGET[$var];
	}
	$selectstr = str_replace('value="'.$value.'"', 'value="'.$value.'" selected', $selectstr);
	$selectstr .= '</select>';
	return $selectstr;
}

//在$sql前强制加上 SELECT
function getblocksql($sql) {
	$sql = trim($sql);
	$sql = str_replace(';', '', $sql);
	$sql = preg_replace("/^(select)/i", '', $sql);
	$sql = 'SELECT'.$sql;
	$sql = stripslashes($sql);
	return $sql;
}

//读取指定目录下的文件
function sreaddir($dir, $ext='') {

	$filearr = array();
	if(is_dir($dir)) {
		$filedir = dir($dir);
		while(false !== ($entry = $filedir->read())) {
			if(!empty($ext)) {
				if (strtolower(fileext($entry)) == strtolower($ext)) {
					$filearr[$entry] = $entry;
				}
			} else {
				if($entry != '.' && $entry != '..') {
					$filearr[$entry] = $entry;
				}
			}
		}
		$filedir->close();
	}
	return $filearr;
}

//TAG处理函数
function tagshow($message, $tagarr) {
	global $_SGLOBAL;
	$message = preg_replace("/\s*(\<.+?\>)\s*/ies", "tagcode('\\1')", $message);
	foreach ($tagarr as $ret) {
		$message = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($ret, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/sieU", "tagshowname('\\1', '\\2')", $message, 1);
	}
	if(empty($_SGLOBAL['tagcodecount'])) $_SGLOBAL['tagcodecount'] = 0;
	for($i = 1; $i <= $_SGLOBAL['tagcodecount']; $i++) {
		$message = str_replace("[\tSUPESITETAGCODE$i\t]", $_SGLOBAL['tagcodehtml'][$i], $message);
	}
	return $message;
}

//TAG处理(屏蔽<xxx>)
function tagcode($str) {
	global $_SGLOBAL;
	if(empty($_SGLOBAL['tagcodecount'])) $_SGLOBAL['tagcodecount'] = 0;
	$_SGLOBAL['tagcodecount']++;
	$_SGLOBAL['tagcodehtml'][$_SGLOBAL['tagcodecount']] = str_replace('\\"', '"', $str);
	return "[\tSUPESITETAGCODE$_SGLOBAL[tagcodecount]\t]";
}

//TAG处理函数
function tagshowname($thename, $thetext) {
	$name = rawurlencode($thename);
	$thetext = str_replace('\\"', '"', $thetext);
	if(cutstr($thetext,1) != '<') {
		return '<a href="javascript:;" onClick="javascript:tagshow(event, \''.$name.'\');" target="_self"><u><strong>'.$thename.'</strong></u></a>'.$thetext;
	} else {
		return $thename.$thetext;
	}
}

function encodeconvert($encode, $content, $to=0) {
	global $_SCONFIG;

	if($to) {
		$in_charset = strtoupper($_SCONFIG['charset']);
		$out_charset = strtoupper($encode);
	} else {
		$in_charset = strtoupper($encode);
		$out_charset = strtoupper($_SCONFIG['charset']);
	}
	if(!empty($encode) && $in_charset != $out_charset) {
		if (function_exists('iconv') && (@$outstr = iconv("$in_charset//IGNORE", "$out_charset//IGNORE", $content))) {
			$content = $outstr;
		} elseif (function_exists('mb_convert_encoding') && (@$outstr = mb_convert_encoding($content, $out_charset, $in_charset))) {
			$content = $outstr;
		}
	}
	return $content;
}

//如果$string不是变量，则返回加上‘’的字符串
function getdotstring($string, $vartype, $allownull=false, $varscope=array(), $sqlmode=1, $unique=true) {

	if(is_array($string)) {
		$stringarr = $string;
	} else {
		if(substr($string, 0, 1) == '$') {
			return $string;
		}
		$string = str_replace('，', ',', $string);
		$string = str_replace(' ', ',', $string);
		$stringarr = explode(',', $string);
	}

	$newarr = array();
	foreach ($stringarr as $value) {
		$value = trim($value);
		if($vartype == 'int') {
			$value = intval($value);
		}
		if(!empty($varscope)) {
			if(in_array($value, $varscope)) {
				$newarr[] = $value;
			}
		} else {
			if($allownull) {
				$newarr[] = $value;
			} else {
				if(!empty($value)) $newarr[] = $value;
			}
		}
	}

	if($unique) $newarr = sarray_unique($newarr);

	if($vartype == 'int') {
		$string = implode(',', $newarr);
	} else {
		if($sqlmode) {
			$string = '\''.implode('\',\'', $newarr).'\'';
		} else {
			$string = implode(',', $newarr);
		}
	}
	return $string;
}

//将数组中相同的值去掉,同时将后面的键名也忽略掉
function sarray_unique($array) {
	$newarray = array();
	if(!empty($array) && is_array($array)) {
		$array = array_unique($array);
		foreach ($array as $value) {
			$newarray[] = $value;
		}
	}
	return $newarray;
}

//返回标准零时区时间戳
function sstrtotime($timestamp) {
	global $_SCONFIG;

	$timestamp = trim($timestamp);	//过滤首尾空格
	if(empty($timestamp)) return 0;
	$hour = $minute = $second = $month = $day = $year = 0;
	$exparr = $timearr = array();
	if(strpos($timestamp, ' ') !== false && strpos($timestamp, '-') !== false) {
		$timearr = explode(' ', $timestamp);
		$exparr = explode('-', $timearr[0]);
		$year = empty($exparr[0])?0:intval($exparr[0]);
		$month = empty($exparr[1])?0:intval($exparr[1]);
		$day = empty($exparr[2])?0:intval($exparr[2]);
		$exparr = explode(':', $timearr[1]);
		$hour = empty($exparr[0])?0:intval($exparr[0]);
		$minute = empty($exparr[1])?0:intval($exparr[1]);
		$second = empty($exparr[2])?0:intval($exparr[2]);
	} elseif(strpos($timestamp, '-') !== false && strpos($timestamp, ' ') === false) {
		$exparr = explode('-', $timestamp);
		$year = empty($exparr[0])?0:intval($exparr[0]);
		$month = empty($exparr[1])?0:intval($exparr[1]);
		$day = empty($exparr[2])?0:intval($exparr[2]);
	} elseif(!strpos($timestamp, '-') === false && strpos($timestamp, ' ') !== false) {
		$exparr = explode(':', $timestamp);
		$hour = empty($exparr[0])?0:intval($exparr[0]);
		$minute = empty($exparr[1])?0:intval($exparr[1]);
		$second = empty($exparr[2])?0:intval($exparr[2]);
	} else {
		return 0;
	}
	return gmmktime($hour, $minute, $second, $month, $day, $year) - $_SCONFIG['timeoffset'] * 3600;
}

//删除主题
function deleteitems($colname, $ids, $undel=0) {
	global $_SGLOBAL, $_SCONFIG;

	if(is_array($ids)) $ids = simplode($ids);
	if($undel) {
		//移动到回收站
		$_SGLOBAL['db']->query("UPDATE ".tname('spaceitems')." SET folder='3' WHERE $colname IN ($ids)");
		return true;
	}

	$hasharr = $itemarr = array();
	$itemidarr = array();
	$uidarr = array();
	$filearr = array();

	//spaceitems//改变用户统计数据
	$numarr = array();
	$itemtypearr = array();
	$itemuidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spaceitems')." WHERE $colname IN ($ids)");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$type = 'news';
		$hasharr[] = md5($value['subject']);
		if(empty($itemarr[$type])) $itemarr[$type] = array();
		if(empty($numarr[$value['uid']][$type])) $numarr[$value['uid']][$type] = 0;
		if(empty($numarr[$value['uid']]['all'])) $numarr[$value['uid']]['all'] = 0;
		$itemarr[$type][] = $value['itemid'];
		$uidarr[$value['uid']] = $value['uid'];
		$itemidarr[] = $value['itemid'];
		if($type != 'news') {
			$numarr[$value['uid']]['all']++;
			$numarr[$value['uid']][$type]++;
		}
		$itemtypearr[$value['itemid']] = $value['type'];
		$itemuidarr[$value['itemid']] = $value['uid'];
	}
	if(empty($itemidarr)) return false;
	$itemids = implode('\',\'', $itemidarr);

	//删除采集防重记录
	if(!empty($hasharr)) {
		$hash = '\''.implode('\',\'', $hasharr).'\'';
		$_SGLOBAL['db']->query("DELETE FROM ".tname('robotlog')." WHERE hash IN ($hash)");
	}
	//主题贴
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spaceitems')." WHERE itemid IN ('$itemids')");

	//论坛导入
	if(discuz_exists()) {
		dbconnect(1);
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('threads', 1).' SET itemid=\'0\' WHERE itemid IN (\''.$itemids.'\')', 'SILENT');
	}

	//内容
	foreach ($_SGLOBAL['type'] as $type) {
		if(!in_array($type, $itemtypearr)) {
			continue;
		}
		$tablename = tname('spacenews');
		$_SGLOBAL['db']->query("DELETE FROM $tablename WHERE itemid IN ('$itemids')");
	}

	//attachments//不改变用户统计数据
	$uidattachs = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('attachments')." WHERE itemid IN ('$itemids')");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(!empty($value['filepath'])) $filearr[] = A_DIR.'/'.$value['filepath'];
		if(!empty($value['thumbpath'])) $filearr[] = A_DIR.'/'.$value['thumbpath'];
	}
	$_SGLOBAL['db']->query("DELETE FROM ".tname('attachments')." WHERE itemid IN ('$itemids')");

	//spacecomments
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spacecomments')." WHERE itemid IN ('$itemids')");

	//spacetags//更新tags表统计信息
	$tagidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacetags')." WHERE itemid IN ('$itemids')");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($tagidarr[$value['tagid']][$itemtypearr[$value['itemid']]])) $tagidarr[$value['tagid']][$itemtypearr[$value['itemid']]] = 0;
		if(empty($tagidarr[$value['tagid']]['all'])) $tagidarr[$value['tagid']]['all'] = 0;
		$tagidarr[$value['tagid']]['all']++;
		$tagidarr[$value['tagid']][$itemtypearr[$value['itemid']]]++;
	}
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spacetags')." WHERE itemid IN ('$itemids')");

	//举报信息
	$_SGLOBAL['db']->query("DELETE FROM ".tname('reports')." WHERE itemid IN ('$itemids')");

	//删除附件
	if(!empty($filearr)) {
		foreach ($filearr as $value) {
			if(!@unlink($value)) errorlog('attachment', 'Unlink '.$value.' Error.');
		}
	}

	//删除html文件
	foreach ($itemidarr as $itemid) {
		if($itemtypearr[$itemid] == 'news') {
			$id = $itemid;
		} else {
			$id = $itemuidarr[$itemid];
		}
		$idvalue = ($id>9)?substr($id, -2, 2):$id;
		$filedir = H_DIR.'/'.$idvalue;
		if(is_dir($filedir)) {
			$filearr = sreaddir($filedir);
			foreach ($filearr as $file) {
				if(preg_match("/\-$itemid(\.|\-)/i", $file)) {
					@unlink($filedir.'/'.$file);
				}
			}
		}
	}
}

//获取系统分类
function getcategory($type, $space='|----', $delbase=0) {
	global $_SGLOBAL;

	include_once(S_ROOT.'./class/tree.class.php');
	$tree = new Tree($type);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('categories').' WHERE type=\''.$type.'\' ORDER BY upid, displayorder');
	$miniupid = '';
	$delid = array();
	if($delbase) {
		$delid[] = $delbase;
	}
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($miniupid == '') $miniupid = $value['upid'];
		$tree->setNode($value['catid'], $value['upid'], $value);
	}
	//根目录
	$listarr = array();
	if($_SGLOBAL['db']->num_rows($query) > 0) {
		$categoryarr = $tree->getChilds($miniupid);
		foreach ($categoryarr as $key => $catid) {
			$cat = $tree->getValue($catid);
			$cat['pre'] = $tree->getLayer($catid, $space);
			if(!empty($delid) && (in_array($cat['upid'], $delid) || $cat['catid'] == $delbase)) {
				$delid[] = $cat['catid'];
			} else {
				$listarr[$cat['catid']] = $cat;
			}
		}
	}
	return $listarr;

}

//获取模型分类
function getmodelcategory($name, $space='|----') {
	global $_SGLOBAL;

	include_once(S_ROOT.'./class/tree.class.php');
	$tree = new Tree($name);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname($name.'categories').' ORDER BY upid, displayorder');
	$miniupid = '';
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($miniupid == '') $miniupid = $value['upid'];
		$tree->setNode($value['catid'], $value['upid'], $value);
	}
	//根目录
	$listarr = array();
	if($_SGLOBAL['db']->num_rows($query) > 0) {
		$categoryarr = $tree->getChilds($miniupid);
		foreach ($categoryarr as $key => $catid) {
			$cat = $tree->getValue($catid);
			$cat['pre'] = $tree->getLayer($catid, $space);
			$listarr[$cat['catid']] = $cat;
		}
	}

	return $listarr;

}

//获取论坛附件文件的url地址
function getbbsattachment($attach) {
	global $_SCONFIG;

	if(strpos($attach['attachment'], '://') === false) {
		$attachurl = empty($_SCONFIG['bbs_ftp']['attachurl'])?B_A_URL:(empty($attach['remote'])?B_A_URL:$_SCONFIG['bbs_ftp']['attachurl']);
		if(empty($item['thumb'])) {
			return $attachurl.'/'.$attach['attachment'];
		} else {
			return $attachurl.'/'.$attach['attachment'].'.thumb.jpg';
		}
	} else {
		return $attach['attachment'];
	}
}

//切割url
function cuturl($url, $length=65) {
	$urllink = "<a href=\"".(substr(strtolower($url), 0, 4) == 'www.' ? "http://$url" : $url).'" target="_blank">';
	if(strlen($url) > $length) {
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	$urllink .= $url.'</a>';
	return $urllink;
}

//资讯标题样式生成函数
function mktitlestyle($styletitle) {
	if(empty($styletitle)) {
		return '';
	} else {
		$return = '';
		substr($styletitle,8,1) == 1?$em = 'italic':$em = 'none';
		substr($styletitle,9,1) == 1?$strong = 'bold':$strong = 'none';
		substr($styletitle,10,1) == 1?$underline = 'underline':$underline = 'none';
		$color = trim(substr($styletitle,0,6));
		$size = trim(substr($styletitle,6,2));
		if(!empty($color)){
			$return .= 'color:#'.$color.";";
		}
		if(!empty($size)){
			$return .= 'font-size:'.$size."px;";
		}
		$return .= 'font-style:'.$em.';font-weight:'.$strong.';text-decoration:'.$underline;
		return $return;
	}
}

function strim($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = strim($val);
		}
	} else {
		$string = trim($string);
	}
	return $string;
}

function scensor($string, $mod=0) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = scensor($val, $mod);
		}
	} else {
		$string = censor($string, $mod);
	}
	return $string;
}

//获取用户数据
function getpassport($username, $password) {
	$passport = array();
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}

	$ucresult = uc_user_login($username, $password);
	if($ucresult[0] > 0) {
		$passport['uid'] = $ucresult[0];
		$passport['username'] = $ucresult[1];
		$passport['email'] = $ucresult[3];
	}
	return $passport;
}

//产生form防伪码
function formhash() {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($_SGLOBAL['formhash'])) {
		$hashadd = defined('IN_ADMINCP') ? 'Only For SupeSite AdminCP' : '';
		$_SGLOBAL['formhash'] = substr(md5(substr($_SGLOBAL['timestamp'], 0, -7).'|'.$_SGLOBAL['supe_uid'].'|'.md5($_SCONFIG['sitekey']).'|'.$hashadd), 8, 8);
	}
	return $_SGLOBAL['formhash'];
}

//检查权限
function checkperm($permtype, $uid=0) {
	global $_SGLOBAL;

	$founderprem = array('managetpl');
	if (in_array($permtype, $founderprem) && !ckfounder($_SGLOBAL['supe_uid'])) {
		return false;
	}
	if(!@include_once(S_ROOT.'./data/system/group.cache.php')) {
		include_once(S_ROOT.'./function/cache.func.php');
		usergroup_cache();
	}

	if ($uid) {
		$query = $_SGLOBAL['db']->query("SELECT uid, groupid FROM ".tname('members')." WHERE uid='$uid'");
		$member = $_SGLOBAL['db']->fetch_array($query);
		$gid = $member['groupid'];
	} else {
		//升级身份
		if(empty($_SGLOBAL['supe_uid'])) {
			return '';
		} else {
			if(empty($_SGLOBAL['member'])) {
				//获取当前人
				getmember();
			}
			$gid = $_SGLOBAL['member']['groupid'];
		}
	}
	return empty($_SGLOBAL['grouparr'][$gid][$permtype])?'':$_SGLOBAL['grouparr'][$gid][$permtype];
}

//获取用户
function getmember() {
	global $_SGLOBAL, $_SC;

	$_SGLOBAL['supe_uid'] = 0;
	$_SGLOBAL['supe_username'] = '';
	$_SGLOBAL['member'] = array();
	if($_COOKIE[$_SC['cookiepre'].'auth']) {
		@list($password, $uid) = explode("\t", authcode($_COOKIE[$_SC['cookiepre'].'auth'], 'DECODE'));
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('members')." WHERE uid='".intval($uid)."' AND password='".addslashes($password)."'");
		if($member = $_SGLOBAL['db']->fetch_array($query)) {
			$_SGLOBAL['member'] = $member;
			$_SGLOBAL['supe_uid'] = $member['uid'];
			$_SGLOBAL['supe_username'] = addslashes($member['username']);
		} else {
			sclearcookie();
		}
	}
}

function ckstart($start, $perpage) {
	global $_SCONFIG;

	$maxstart = $perpage*intval($_SCONFIG['maxpage']);
	if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
		showmessage('length_is_not_within_the_scope_of');
	}
}

//处理头像
function avatar($uid, $size='small') {
	return UC_API.'/avatar.php?uid='.$uid.'&size='.$size;
}

//检查验证码
function ckseccode($seccode) {
	global $_SCOOKIE;

	$check = true;
	$cookie_seccode = empty($_SCOOKIE['seccode'])?'':authcode($_SCOOKIE['seccode'], 'DECODE');
	if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
		$check = false;
	}
	return $check;
}

//判断是否设置论坛
function discuz_exists() {
	global $_SSCONFIG;
	if(!empty($_SSCONFIG['channel']['bbs']) || !empty($_SSCONFIG['hidechannels']['bbs'])) {
		return true;
	} else {
		return false;
	}
}

//判断是否设置UCenter Home
function uchome_exists() {
	global $_SSCONFIG;

	if(!empty($_SSCONFIG['channel']['uchblog']) || !empty($_SSCONFIG['hidechannels']['uchblog']) || !empty($_SSCONFIG['channel']['uchimage']) || !empty($_SSCONFIG['hidechannels']['uchimage'])) {
		return true;
	} else {
		return false;
	}
}

//检查邮箱是否有效
function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

function getsiteurl() {
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$siteurl = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/'));
	return $siteurl;
}

//生成站点key
function mksitekey() {
	global $_SERVER, $_SC, $_SGLOBAL;
	//16位
	$sitekey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SC['dbhost'].$_SC['dbuser'].$_SC['dbpw'].$_SC['dbname'].substr($_SGLOBAL['timestamp'], 0, 6)), 8, 6).random(10);
	return $sitekey;
}
?>
