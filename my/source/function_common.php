<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_common.php 7358 2008-05-13 06:15:41Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//SQL ADDSLASHES
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

//ȡ��HTML����
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

//�ַ������ܼ���
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	
	$ckey_length = 4;	// �����Կ���� ȡֵ 0-32;
				// ���������Կ���������������κι��ɣ�������ԭ�ĺ���Կ��ȫ��ͬ�����ܽ��Ҳ��ÿ�β�ͬ�������ƽ��Ѷȡ�
				// ȡֵԽ�����ı䶯����Խ�����ı仯 = 16 �� $ckey_length �η�
				// ����ֵΪ 0 ʱ���򲻲��������Կ

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

//���cookie
function clearcookie() {
	global $_SGLOBAL;
	
	ssetcookie('auth', '', -86400 * 365);
	$_SGLOBAL['supe_uid'] = 0;
	$_SGLOBAL['supe_username'] = '';
}

//cookie����
function ssetcookie($var, $value, $life=0) {
	global $_SGLOBAL, $_SC, $_SERVER;
	setcookie($_SC['cookiepre'].$var, $value, $life?($_SGLOBAL['timestamp']+$life):0, $_SC['cookiepath'], $_SC['cookiedomain'], $_SERVER['SERVER_PORT']==443?1:0);
}

//���ݿ�����
function dbconnect() {
	global $_SGLOBAL, $_SC;

	include_once(S_ROOT.'./source/class_mysql.php');

	$_SGLOBAL['db'] = new dbstuff;
	$_SGLOBAL['db']->charset = $_SC['dbcharset'];
	$_SGLOBAL['db']->connect($_SC['dbhost'], $_SC['dbuser'], $_SC['dbpw'], $_SC['dbname'], $_SC['pconnect']);
}

//��ȡ����IP
function getonlineip() {
	global $_SGLOBAL;
	
	if(empty($_SGLOBAL['onlineip'])) {
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$_SGLOBAL['onlineip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	}
	return $_SGLOBAL['onlineip'];
}

//��ȡ��ǰ�û���Ϣ
function getuser() {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE;

	if($_SCOOKIE['auth']) {
		@list($password, $uid) = explode("\t", authcode($_SCOOKIE['auth'], 'DECODE'));
		$_SGLOBAL['supe_uid'] = intval($uid);
		$wheresql = "uid='$_SGLOBAL[supe_uid]' AND password='$password'";
		if($_SGLOBAL['supe_uid']) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('session')." WHERE $wheresql");
			if($_SGLOBAL['member'] = $_SGLOBAL['db']->fetch_array($query)) {
				$_SGLOBAL['supe_username'] = addslashes($_SGLOBAL['member']['username']);
			} else {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('member')." WHERE $wheresql");
				if($member = $_SGLOBAL['db']->fetch_array($query)) {
					$_SGLOBAL['supe_username'] = addslashes($member['username']);
					$setarr = array('uid' => $_SGLOBAL['supe_uid'], 'username' => $_SGLOBAL['supe_username'], 'password' => $password);
					insertsession($setarr);//��¼
				} else {
					$_SGLOBAL['supe_uid'] = 0;
				}
			}
		}
	}
	if(empty($_SGLOBAL['supe_uid'])) {
		clearcookie();
	} else {
		$_SGLOBAL['username'] = $_SGLOBAL['member']['username'];
	}
}

//����session
function insertsession($setarr) {
	global $_SGLOBAL, $_SCONFIG;
	
	$_SCONFIG['onlinehold'] = intval($_SCONFIG['onlinehold']);
	if($_SCONFIG['onlinehold'] < 300) $_SCONFIG['onlinehold'] = 300;
	$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$setarr[uid]' OR lastactivity<'".($_SGLOBAL['timestamp']-$_SCONFIG['onlinehold'])."'");

	//��������
	$setarr['lastactivity'] = $_SGLOBAL['timestamp'];
	//����groupid,credit
	$query = $_SGLOBAL['db']->query("SELECT s.groupid, s.credit, sf.nocss
		FROM ".tname('space')." s
		LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
		WHERE s.uid='$setarr[uid]'");
	if($space = $_SGLOBAL['db']->fetch_array($query)) {
		//��Ҫ����
		$gid = getgroupid($space['credit'], $space['groupid']);
		if($gid != $space['groupid']) {
			updatetable('space', array('groupid'=>$gid), array('uid'=>$setarr['uid']));
			$space['groupid'] = $gid;
		}
		$setarr['groupid'] = $space['groupid'];
		$setarr['credit'] = $space['credit'];
		$setarr['nocss'] = $space['nocss'];
		$_SGLOBAL['member'] = $setarr;
		inserttable('session', $setarr, 0, true);
	} else {
		$space = array();
	}

	return $space;
}

//��ȡ������
function tname($name) {
	global $_SC;
	return $_SC['tablepre'].$name;
}

//�Ի���
function showmessage($msgkey, $url_forward='', $second=1, $values=array()) {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_TPL;
	
	if(empty($_SGLOBAL['inajax']) && $url_forward && empty($second)) {
		//ֱ��301��ת
		obclean();
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url_forward");
	} else {
		//��ʾ��Ϣҳ��
		include_once(S_ROOT.'./language/lang_showmessage.php');
		if(isset($_SGLOBAL['msglang'][$msgkey])) {
			$message = lang_replace($_SGLOBAL['msglang'][$msgkey], $values);
		} else {
			$message = $msgkey;
		}
		//��ʾ
		obclean();
		if(!empty($url_forward)) {
			$second = $second * 1000;
			$message .= "<script>setTimeout(\"window.location.href ='$url_forward';\", $second);</script>";
		}
		include template('showmessage');
	}
	exit();
}

//�ж��ύ�Ƿ���ȷ
function submitcheck($var) {
	global $_SGLOBAL, $_SC;

	if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		return true;
	} else {
		return false;
	}
}

//��������
function inserttable($tablename, $insertsqlarr, $returnid=0, $replace = false) {
	global $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.'`'.$insert_key.'`';
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	$method = $replace?'REPLACE':'INSERT';
	$_SGLOBAL['db']->query($method.' INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.') ');
	if($returnid && !$replace) {
		return $_SGLOBAL['db']->insert_id();
	}
}

//��������
function updatetable($tablename, $setsqlarr, $wheresqlarr) {
	global $_SGLOBAL;

	$setsql = $comma = '';
	foreach ($setsqlarr as $set_key => $set_value) {
		$setsql .= $comma.'`'.$set_key.'`'.'=\''.$set_value.'\'';
		$comma = ', ';
	}
	$where = $comma = '';
	if(empty($wheresqlarr)) {
		$where = '1';
	} elseif(is_array($wheresqlarr)) {
		foreach ($wheresqlarr as $key => $value) {
			$where .= $comma.'`'.$key.'`'.'=\''.$value.'\'';
			$comma = ' AND ';
		}
	} else {
		$where = $wheresqlarr;
	}
	$_SGLOBAL['db']->query('UPDATE '.tname($tablename).' SET '.$setsql.' WHERE '.$where);
}

//��ȡ�û��ռ���Ϣ
function getspace($key, $indextype='uid') {
	global $_SGLOBAL, $_SCONFIG;
	
	if(!empty($_SGLOBAL['do_index'])) {
		$select = 'sf.*';
	} else {
		$select = 'sf.note, sf.privacy, sf.friend, sf.feedfriend, sf.resideprovince, sf.residecity';
	}
	$space = array();
	$query = $_SGLOBAL['db']->query("SELECT $select, s.* FROM ".tname('space')." s
		LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
		WHERE s.{$indextype}='$key'");
	if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
		if($indextype=='uid') {
			include_once(S_ROOT.'./uc_client/client.php');
			if($user = uc_get_user($key, 1)) {
				include_once(S_ROOT.'./source/function_space.php');
				$space = space_open($user[0], addslashes($user[1]), 0, addslashes($user[2]));
			}
		}
	}
	if($space) {
		$space['self'] = $space['uid']==$_SGLOBAL['supe_uid']?1:0;
		$space['privacy'] = empty($space['privacy'])?(empty($_SCONFIG['privacy'])?array():$_SCONFIG['privacy']):unserialize($space['privacy']);
		$space['frienduid'] = $space['friend'];
	}
	return $space;
}

//�����˽
function ckprivacy($type, $feedmode=0) {
	global $_SGLOBAL, $space;
	
	$result = false;
	if($feedmode) {
		if(!empty($space['privacy']['feed'][$type])) {
			$result = true;
		}
	} else{
		if($space['self'] || empty($space['privacy']['view'][$type])) {
			$result = true;
		}
		if(!$result && $space['privacy']['view'][$type] == 1) {
			//�Ƿ����
			$space['isfriend'] = $space['self'];
			if($space['frienduid'] && in_array($_SGLOBAL['supe_uid'], explode(',', $space['frienduid']))) {
				$space['isfriend'] = 1;//�Ǻ���
			}
			if($space['isfriend']) {
				$result = true;
			}
		}
	}
	return $result;
}

//��ȡ�û���
function getgroupid($credit, $gid=0) {
	global $_SGLOBAL;

	if(!@include_once(S_ROOT.'./data/data_usergroup.php')) {
		include_once(S_ROOT.'./source/function_cache.php');
		usergroup_cache();
	}
	
	$needfind = false;
	if($gid && !empty($_SGLOBAL['usergroup'][$gid])) {
		$group = $_SGLOBAL['usergroup'][$gid];
		if(empty($group['system'])) {
			if($group['credithigher']<$credit || $group['creditlower']>$credit) {
				$needfind = true;
			}
		}
	} else {
		$needfind = true;
	}
	if($needfind) {
		$query = $_SGLOBAL['db']->query("SELECT gid FROM ".tname('usergroup')." WHERE creditlower<='$credit' AND system='0' ORDER BY creditlower DESC LIMIT 1");
		$gid = $_SGLOBAL['db']->result($query, 0);
	}
	return $gid;
}

//���Ȩ��
function checkperm($permtype) {
	global $_SGLOBAL;

	@include_once(S_ROOT.'./data/data_usergroup.php');
	
	//��������
	if(empty($_SGLOBAL['member'])) {
		return '';
	} else {
		$gid = getgroupid($_SGLOBAL['member']['credit'], $_SGLOBAL['member']['groupid']);
		if($gid != $_SGLOBAL['member']['groupid']) {
			//��Ҫ����
			updatetable('space', array('groupid'=>$gid), array('uid'=>$_SGLOBAL['supe_uid']));
			updatetable('session', array('groupid'=>$gid), array('uid'=>$_SGLOBAL['supe_uid']));
		}
	}
	return empty($_SGLOBAL['usergroup'][$gid][$permtype])?'':$_SGLOBAL['usergroup'][$gid][$permtype];
}

//д������־
function runlog($type, $message, $halt=0) {
	global $_SGLOBAL;
	if(@$fp = fopen(S_ROOT.'./data/log_run.php', 'a')) {
		@fwrite($fp, "<?exit?>$_SGLOBAL[timestamp]\t$type\t$_SGLOBAL[supe_uid]\t".str_replace(array("\r", "\n"), array(' ', ' '), trim(shtmlspecialchars($message)))."\n");
		@fclose($fp);
	}
	if($halt) exit();
}

//��ȡ�ַ���
function getstr($string, $length, $in_slashes=0, $out_slashes=0, $censor=0, $bbcode=0, $html=0) {
	global $_SC, $_SGLOBAL;
	
	$string = trim($string);
	
	if($in_slashes) {
		//������ַ���slashes
		$string = sstripslashes($string);
	}
	if($html < 0) {
		//ȥ��html��ǩ
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
		$string = shtmlspecialchars($string);
	} elseif ($html == 0) {
		//ת��html��ǩ
		$string = shtmlspecialchars($string);
	}
	if($censor) {
		//��������
		@include_once(S_ROOT.'./data/data_censor.php');
		if($_SGLOBAL['censor']['banned'] && preg_match($_SGLOBAL['censor']['banned'], $string)) {
			showmessage('information_contains_the_shielding_text');
		} else {
			$string = empty($_SGLOBAL['censor']['filter']) ? $string :
				@preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $string);
		}
	}
	if($length && strlen($string) > $length) {
		//�ض��ַ�
		$wordscut = '';
		if(strtolower($_SC['charset']) == 'utf-8') {
			//utf8����
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
		$string = $wordscut;
	}
	if($bbcode) {
		//����bboce
		include_once(S_ROOT.'./source/function_bbcode.php');
		$string = bbcode($string, $bbcode);
	}
	if($out_slashes) {
		$string = saddslashes($string);
	}
	return trim($string);
}

//ʱ���ʽ��
function sgmdate($dateformat, $timestamp='', $format=0) {
	global $_SCONFIG, $_SGLOBAL;
	if(empty($timestamp)) {
		$timestamp = $_SGLOBAL['timestamp'];
	}
	$result = '';
	if($format) {
		$time = $_SGLOBAL['timestamp'] - $timestamp;
		if($time > 24*3600) {
			$result = gmdate($dateformat, $timestamp + $_SCONFIG['timeoffset'] * 3600);
		} elseif ($time > 3600) {
			$result = intval($time/3600).mlang('hour').mlang('before');
		} elseif ($time > 60) {
			$result = intval($time/60).mlang('minute').mlang('before');
		} elseif ($time > 0) {
			$result = $time.mlang('second').mlang('before');
		} else {
			$result = mlang('now');
		}
	} else {
		$result = gmdate($dateformat, $timestamp + $_SCONFIG['timeoffset'] * 3600);
	}
	return $result;
}

//�ַ���ʱ�仯
function sstrtotime($string) {
	global $_SCONFIG;
	$time = '';
	if($string) {
		$time = strtotime($string);
	}
	return empty($time)?'':$time;
}

//��ҳ
function multi($num, $perpage, $curpage, $mpurl) {
	global $_SCONFIG;
	$page = 5;
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&' : '?';
	$realpages = 1;
	if($num > $perpage) {
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $_SCONFIG['maxpage'] && $_SCONFIG['maxpage'] < $realpages ? $_SCONFIG['maxpage'] : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1" class="first">1 ...</a>' : '').
			($curpage > 1 ? '<a href="'.$mpurl.'page='.($curpage - 1).'" class="prev">&lsaquo;&lsaquo;</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' :
				'<a href="'.$mpurl.'page='.$i.'">'.$i.'</a>';
		}

		$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.'page='.($curpage + 1).'" class="next">&rsaquo;&rsaquo;</a>' : '').
			($to < $pages ? '<a href="'.$mpurl.'page='.$pages.'" class="last">... '.$realpages.'</a>' : '');

		$multipage = $multipage ? '<div class="pages">'.'<em>&nbsp;'.$num.'&nbsp;</em>'.$multipage.'</div>' : '';
	}
	$maxpage = $realpages;
	return $multipage;
}

//ob
function obclean() {
	global $_SC;

	ob_end_clean();
	if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

//ģ�����
function template($name) {
	global $_SC, $_SGLOBAL;

	$tpl = strexists($name,'/')?$name:"template/$_SC[template]/$name";
	$objfile = S_ROOT.'./data/tpl_cache/'.str_replace('/','_',$tpl).'.php';
	if(!file_exists($objfile)) {
		include_once(S_ROOT.'./source/function_template.php');
		parse_template($tpl);
	}
	return $objfile;
}

//��ģ����¼��
function subtplcheck($subfiles, $mktime, $tpl) {
	global $_SC;

	if($_SC['tplrefresh'] && ($_SC['tplrefresh'] == 1 || mt_rand(1, $_SC['tplrefresh']) == 1)) {
		$subfiles = explode('|', $subfiles);
		foreach ($subfiles as $subfile) {
			@$submktime = filemtime(S_ROOT.'./'.$subfile.'.htm');
			if($submktime > $mktime) {
				include_once(S_ROOT.'./source/function_template.php');
				parse_template($tpl);
				break;
			}
		}
	}
}

//ģ��
function block($param) {
	global $_SBLOCK;
	
	include_once(S_ROOT.'./source/function_block.php');
	block_batch($param);
}

//��ȡ��Ŀ
function getcount($tablename, $wherearr, $get='COUNT(*)') {
	global $_SGLOBAL;
	if(empty($wherearr)) {
		$wheresql = '1';
	} else {
		$wheresql = $mod = '';
		foreach ($wherearr as $key => $value) {
			$wheresql .= $mod."`$key`='$value'";
			$mod = ' AND ';
		}
	}
	return $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT $get FROM ".tname($tablename)." WHERE $wheresql LIMIT 1"), 0);
}

//�������
function ob_out() {
	global $_SGLOBAL, $_SCONFIG;
	
	$content = ob_get_contents();
	
	$preg_searchs = $preg_replaces = $str_searchs = $str_replaces = array();
	
	if($_SCONFIG['allowrewrite']) {
		$preg_searchs[] = "/\<a href\=\"space\.php\?(uid|do)+\=([a-z0-9\=\&]+?)\"/ie";
		$preg_searchs[] = "/\<a href\=\"space.php\"/i";
		$preg_searchs[] = "/\<a href\=\"network\.php\?ac\=([a-z0-9\=\&]+?)\"/ie";
		$preg_searchs[] = "/\<a href\=\"network.php\"/i";
		
		$preg_replaces[] = 'rewrite_url(\'space-\',\'\\2\')';
		$preg_replaces[] = '<a href="space.html"';
		$preg_replaces[] = 'rewrite_url(\'network-\',\'\\1\')';
		$preg_replaces[] = '<a href="network.html"';
	}
	if($_SCONFIG['linkguide']) {
		$preg_searchs[] = "/\<a href\=\"http\:\/\/(.+?)\"/ie";
		$preg_replaces[] = 'iframe_url(\'\\1\')';
	}
	
	if($_SGLOBAL['inajax']) {
		$preg_searchs[] = "/([\x01-\x09\x0b-\x0c\x0e-\x1f])+/";
		$preg_replaces[] = ' ';
		
		$str_searchs[] = ']]>';
		$str_replaces[] = ']]&gt;';
	}
	
	if($preg_searchs) {
		$content = preg_replace($preg_searchs, $preg_replaces, $content);
	}
	if($str_searchs) {
		$content = trim(str_replace($str_searchs, $str_replaces, $content));
	}
	
	obclean();
	echo $content;
	if($_SGLOBAL['inajax']) {
		echo "]]></root>";
		exit();
	} elseif(D_BUG) {
		@include_once(S_ROOT.'./source/inc_debug.php');
	}
}

//rewrite����
function rewrite_url($pre, $para) {
	$para = str_replace(array('&','='), array('-', '-'), $para);
	return '<a href="'.$pre.$para.'.html"';
}

//����
function iframe_url($url) {
	$url = rawurlencode(base64_encode($url));
	return "<a href=\"link.php?url=$url\"";
}

//���������ؼ���
function stripsearchkey($string) {
	$string = trim($string);
	$string = str_replace('*', '%', addcslashes($string, '%_'));
	$string = str_replace('_', '\_', $string);
	return $string;
}

//�Ƿ����ζ�������
function isholddomain($domain) {
	global $_SCONFIG;
	
	$domain = strtolower($domain);
	
	if(preg_match("/^[^a-z]/i", $domain)) return true;
	$holdmainarr = empty($_SCONFIG['holddomain'])?array('www'):explode('|', $_SCONFIG['holddomain']);
	$ishold = false;
	foreach ($holdmainarr as $value) {
		if(strpos($value, '*') === false) {
			if(strtolower($value) == $domain) {
				$ishold = true;
				break;
			}
		} else {
			$value = str_replace('*', '', $value);
			if(@preg_match("/$value/i", $domain)) {
				$ishold = true;
				break;
			}
		}
	}
	return $ishold;
}

//�����ַ�
function simplode($ids) {
	return "'".implode("','", $ids)."'";
}

//��ʾ���̴���ʱ��
function debuginfo() {
	global $_SGLOBAL, $_SCONFIG;

	$mtime = explode(' ', microtime());
	$totaltime = number_format(($mtime[1] + $mtime[0] - $_SGLOBAL['supe_starttime']), 6);

	$info = 'Processed in '.$totaltime.' second(s), '.$_SGLOBAL['db']->querynum.' queries'.
			($_SCONFIG['gzipcompress'] ? ', Gzip enabled' : NULL);
	
	return $info;
}

//��ʽ����С����
function formatsize($size) {
	$prec=3;
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

//��ȡ�ļ�����
function sreadfile($filename) {
	$content = '';
	if(function_exists('file_get_contents')) {
		@$content = file_get_contents($filename);
	} else {
		if(@$fp = fopen($filename, 'r')) {
			@$content = fread($fp, filesize($filename));
			@fclose($fp);
		}
	}
	return $content;
}

//д���ļ�
function swritefile($filename, $writetext, $openmod='w') {
	if(@$fp = fopen($filename, $openmod)) {
		flock($fp, 2);
		fwrite($fp, $writetext);
		fclose($fp);
		return true;
	} else {
		runlog('error', "File: $filename write error.");
		return false;
	}
}

//��������ַ�
function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//�ж��ַ����Ƿ����
function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}

//��ȡ����
function data_get($var) {
	global $_SGLOBAL;

	$query = $_SGLOBAL['db']->query("SELECT datavalue FROM ".tname('data')." WHERE var='$var' LIMIT 1");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		return $value['datavalue'];
	} else {
		return '';
	}
}

//��������
function data_set($var, $datavalue) {
	global $_SGLOBAL;

	$_SGLOBAL['db']->query("REPLACE INTO ".tname('data')." (var, datavalue) VALUES ('$var', '$datavalue')");
}

//���վ���Ƿ�ر�
function checkclose() {
	global $_SGLOBAL, $_SCONFIG;

	//վ��ر�
	if($_SCONFIG['close'] && !checkperm('closeignore')) {
		if(empty($_SCONFIG['closereason'])) {
			showmessage('site_temporarily_closed');
		} else {
			showmessage($_SCONFIG['closereason']);
		}
	}
	//IP���ʼ��
	if((!ipaccess($_SCONFIG['ipaccess']) || ipbanned($_SCONFIG['ipbanned'])) && !checkperm('closeignore')) {
		showmessage('ip_is_not_allowed_to_visit');
	}
}

//�Ƿ�ѡ�ɰ���
function ismoderator($moderator) {
	global $_SGLOBAL;
	
	if(empty($moderator)) {
		$ismod = false;
	} else {
		$marr = explode("\t", $moderator);
		if(in_array($_SGLOBAL['supe_username'], $marr)) {
			$ismod = true;
		}
	}
	return $ismod;
}

//վ������
function getsiteurl() {
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	return 'http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1);
}

//��ȡ�ļ�����׺
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

//����
function creditrule($mode, $type) {
	global $_SGLOBAL;
	
	if(!@include_once(S_ROOT.'./data/data_creditrule.php')) {
		include_once(S_ROOT.'./source/function_cache.php');
		creditrule_cache();
	}
	$credit = 0;
	if(!empty($_SGLOBAL['creditrule'])) {
		if(!empty($_SGLOBAL['creditrule'][$mode][$type])) {
			$credit = $_SGLOBAL['creditrule'][$mode][$type];
		}
	}
	return intval($credit);
}

//���»���
function updatespacestatus($creditmode, $optype) {
	global $_SGLOBAL;
	
	$lastname = $optype=='search'?'lastsearch':'lastpost';
	$credit = creditrule($creditmode, $optype);
	if($credit) {
		$creditsql = ($creditmode == 'get')?"+$credit":"-$credit";
		$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET credit=credit{$creditsql} WHERE uid='$_SGLOBAL[supe_uid]'");//����session
	} else {
		$creditsql = '';
	}
	$creditsql = $creditsql?",credit=credit{$creditsql}":'';
	$updatetimesql = $optype=='search'?'':",updatetime='$_SGLOBAL[timestamp]'";//����������
	
	//����״̬
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET $lastname='$_SGLOBAL[timestamp]' $updatetimesql $creditsql WHERE uid='$_SGLOBAL[supe_uid]'");
}

//ȥ��slassh
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

//��ʾ���
function adshow($pagetype) {
	global $_SGLOBAL;
	
	@include_once(S_ROOT.'./data/data_ad.php');
	if(empty($_SGLOBAL['ad']) || empty($_SGLOBAL['ad'][$pagetype])) return false;
	$ads = $_SGLOBAL['ad'][$pagetype];
	$key = mt_rand(0, count($ads)-1);
	$id = $ads[$key];
	$file = S_ROOT.'./data/adtpl/'.$id.'.htm';
	echo sreadfile($file);
}

//����ת��
function siconv($str, $out_charset, $in_charset='') {
	global $_SC;
	
	$in_charset = empty($in_charset)?strtoupper($_SC['charset']):strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);
	if($in_charset != $out_charset) {
		 if (file_exists(S_ROOT.'./source/chinese/class.Chinese.php')) {
			//���
			$in_charset = str_replace(array('GBK', 'UTF-8'), array('GB2312', 'UTF8'), $in_charset);
			$out_charset = str_replace(array('GBK', 'UTF-8'), array('GB2312', 'UTF8'), $out_charset);
			include_once(S_ROOT.'./source/chinese/class.Chinese.php');
			$codeTablesDir = S_ROOT.'./source/chinese/config/';
			$chs = new Chinese($in_charset, $out_charset, $str, $codeTablesDir);
			if($outstr = $chs->ConvertIT()) {
				return $outstr;
			}
		} elseif (function_exists('iconv') && (@$outstr = iconv("$in_charset//IGNORE", "$out_charset//IGNORE", $str))) {
			return $outstr;
		} elseif (function_exists('mb_convert_encoding') && (@$outstr = mb_convert_encoding($str, $out_charset, $in_charset))) {
			return $outstr;
		}
	}
	return $str;//ת��ʧ��
}

//��ȡ�û�����
function getpassport($username, $password) {
	global $_SGLOBAL, $_SC;

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

//�û�����ʱ�������
function interval_check($type) {
	global $_SGLOBAL, $space;
	
	$intervalname = $type.'interval';
	$lastname = 'last'.$type;

	$waittime = 0;
	if($interval = checkperm($intervalname)) {
		$lasttime = isset($space[$lastname])?$space[$lastname]:getcount('space', array('uid'=>$_SGLOBAL['supe_uid']), $lastname);
		$waittime = $interval - ($_SGLOBAL['timestamp'] - $lasttime);
	}
	return $waittime;
}

//�����ϴ�ͼƬ����
function mkpicurl($pic, $thumb=1) {
	global $_SCONFIG, $_SC, $space;

	$url = '';
	if(isset($pic['picnum']) && $pic['picnum'] < 1) {
		$url = 'image/nopic.gif';
	} elseif(isset($pic['picflag'])) {
		if($pic['pic']) {
			if($pic['picflag'] == 1) {
				$url = $_SC['attachurl'].$pic['pic'];
			} elseif ($pic['picflag'] == 2) {
				$url = $_SCONFIG['ftpurl'].$pic['pic'];
			} else {
				$url = $pic['pic'];
			}
		}
	} elseif(isset($pic['filepath'])) {
		$pic['pic'] = $pic['filepath'];
		if($pic['pic']) {
			if($thumb && $pic['thumb']) $pic['pic'] .= '.thumb.jpg';
			if($pic['remote']) {
				$url = $_SCONFIG['ftpurl'].$pic['pic'];
			} else {
				$url = $_SC['attachurl'].$pic['pic'];
			}
		}
	} else {
		$url = $pic['pic'];
	}
	if($url && $pic['friend']==4) {
		$url = 'image/nopublish.jpg';
	} 
	return $url;
}

//��������ͼƬ����
function getpicurl($picurl, $maxlenth='200') {
	$picurl = shtmlspecialchars(trim($picurl));
	if($picurl) {
		if(preg_match("/^http\:\/\/.{5,$maxlenth}\.(jpg|gif|png)$/i", $picurl)) return $picurl;
	}
	return '';
}

//������������
function getstar($credit) {
	global $_SCONFIG;
	
	$starimg = '';
	if($_SCONFIG['starcredit'] > 1) {
		//����������
		$starnum = intval($credit/$_SCONFIG['starcredit']) + 1;
		if($_SCONFIG['starlevelnum'] < 2) {
			if($starnum > 10) $starnum = 10;
			for($i = 0; $i < $starnum; $i++) {
				$starimg .= '<img src="image/star_level1.gif" align="absmiddle" alt="'.$credit.'" title="'.$credit.'" />';
			}
		} else {
			//����ȼ�(10��)
			for($i = 10; $i > 0; $i--) {
				$numlevel = intval($starnum / pow($_SCONFIG['starlevelnum'], ($i - 1)));
				if($numlevel > 10) $numlevel = 10;
				if($numlevel) {
					for($j = 0; $j < $numlevel; $j++) {
						$starimg .= '<img src="image/star_level'.$i.'.gif" align="absmiddle" alt="'.$credit.'" title="'.$credit.'" />';
					}
					break;
				}
			}
		}
	}
	if(empty($starimg)) $starimg = '<img src="image/credit.gif" alt="'.$credit.'" align="absmiddle" alt="'.$credit.'" title="'.$credit.'" />';
	return $starimg;
}

//������ҳ
function smulti($start, $perpage, $count, $url, $ajaxdiv='') {
	global $_SGLOBAL;
	
	$multi = array('last'=>-1, 'next'=>-1, 'begin'=>-1, 'end'=>-1, 'html'=>'');
	if($start > 0) {
		if(empty($count)) {
			showmessage('no_data_pages');
		} else {
			$multi['last'] = $start - $perpage;
		}
	}

	$showhtml = 0;
	if($count == $perpage) {
		$multi['next'] = $start + $perpage;
	}
	$multi['begin'] = $start + 1;
	$multi['end'] = $start + $count;

	if($multi['begin'] >= 0) {
		if($multi['last'] >= 0) {
			$showhtml = 1;
			if($_SGLOBAL['inajax']) {
				$multi['html'] .= "<a href=\"javascript:;\" onclick=\"ajaxget('$url&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">|&lt;</a> <a href=\"javascript:;\" onclick=\"ajaxget('$url&start=$multi[last]&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">&lt;</a> ";
			} else {
				$multi['html'] .= "<a href=\"$url\">|&lt;</a> <a href=\"$url&start=$multi[last]\">&lt;</a> ";
			}
		} else {
			$multi['html'] .= "&lt;";
		}
		$multi['html'] .= " $multi[begin]~$multi[end] ";
		if($multi['next'] >= 0) {
			$showhtml = 1;
			if($_SGLOBAL['inajax']) {
				$multi['html'] .= " <a href=\"javascript:;\" onclick=\"ajaxget('$url&start=$multi[next]&ajaxdiv=$ajaxdiv', '$ajaxdiv')\">&gt;</a> ";
			} else {
				$multi['html'] .= " <a href=\"$url&start=$multi[next]\">&gt;</a>";
			}
		} else {
			$multi['html'] .= " &gt;";
		}
	}

	return $showhtml?$multi:array();
}

//��ȡ����״̬
function getfriendstatus($uid, $fuid) {
	global $_SGLOBAL;
	
	$query = $_SGLOBAL['db']->query("SELECT status FROM ".tname('friend')." WHERE uid='$uid' AND fuid='$fuid' LIMIT 1");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		return $value['status'];
	} else {
		return -1;
	}
}

//�����齨
function renum($array) {
	$newnums = $nums = array();
	foreach ($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[] = $num;
	}
	return array($nums, $newnums);
}

//��鶨��
function ckfriend($invalue) {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $space;
	
	if($invalue['uid'] == $_SGLOBAL['supe_uid']) return true;//�Լ�
	if(isset($invalue['picnum']) && $invalue['picnum'] < 1) return false;//û��ͼƬ

	$result = false;
	switch ($invalue['friend']) {
		case 0://ȫվ�û��ɼ�
			$result = true;
			break;
		case 1://ȫ���ѿɼ�
			if($space['self']) {
				$result = true;
			} else {
				if($space['uid'] == $invalue['uid']) {
					//�Ƿ����
					$space['isfriend'] = $space['self'];
					if($space['frienduid'] && in_array($_SGLOBAL['supe_uid'], explode(',', $space['frienduid']))) {
						$space['isfriend'] = 1;//�Ǻ���
					}
					$isfriend = $space['isfriend'];
				} else {
					$isfriend = getfriendstatus($_SGLOBAL['supe_uid'], $invalue['uid']);
				}
				if($isfriend) $result = true;
			}
			break;
		case 2://��ָ�����ѿɼ�
			if($invalue['target_ids']) {
				$target_ids = explode(',', $invalue['target_ids']);
				if(in_array($_SGLOBAL['supe_uid'], $target_ids)) $result = true;
			}
			break;
		case 3://���Լ��ɼ�
			break;
		case 4://ƾ����鿴
			$result = true;
			break;
		default:
			break;
	}
	return $result;
}

//����feed
function mkfeed($feed, $actors=array()) {
	global $_SGLOBAL;
	
	$title_data = unserialize($feed['title_data']);
	$body_data = unserialize($feed['body_data']);
	
	//title
	$searchs = $replaces = array();
	if($title_data) {
		foreach (array_keys($title_data) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $title_data[$key];
		}
	}

	$searchs[] = '{actor}';
	$replaces[] = empty($actors)?"<a href=\"space.php?uid=$feed[uid]\">$feed[username]</a>":implode(mlang('dot'), $actors);
	$searchs[] = '{app}';
	if(empty($_SGLOBAL['app'][$feed['appid']])) {
		$replaces[] = '';
	} else {
		$app = $_SGLOBAL['app'][$feed['appid']];
		$replaces[] = "<a href=\"$app[url]\">$app[name]</a>";
	}
	$feed['title_template'] = str_replace($searchs, $replaces, $feed['title_template']);
	
	//body
	$searchs = $replaces = array();
	if($body_data) {
		foreach (array_keys($body_data) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $body_data[$key];
		}
	}
	$searchs[] = '{actor}';
	$replaces[] = "<a href=\"space.php?uid=$feed[uid]\">$feed[username]</a>";
	$feed['body_template'] = str_replace($searchs, $replaces, $feed['body_template']);
	
	return $feed;
}


//��������
function mkshare($share) {
	$share['body_data'] = unserialize($share['body_data']);
	
	//body
	$searchs = $replaces = array();
	if($share['body_data']) {
		foreach (array_keys($share['body_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $share['body_data'][$key];
		}
	}
	$share['body_template'] = str_replace($searchs, $replaces, $share['body_template']);
	
	return $share;
}

//ip��������
function ipaccess($ipaccess) {
	return empty($ipaccess)?true:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipaccess, '/')).")/", getonlineip());
}


//ip���ʽ�ֹ
function ipbanned($ipbanned) {
	return empty($ipbanned)?false:preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($ipbanned, '/')).")/", getonlineip());
}

//���start
function ckstart($start, $perpage) {
	global $_SCONFIG;
	
	$maxstart = $perpage*intval($_SCONFIG['maxpage']);
	if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
		showmessage('length_is_not_within_the_scope_of');
	}
}

//����ͷ��
function avatar($uid, $size='small') {
	return UC_API.'/avatar.php?uid='.$uid.'&size='.$size;
}

//����Ƿ��¼
function checklogin() {
	global $_SGLOBAL;
	
	if(empty($_SGLOBAL['supe_uid'])) {
		ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
		showmessage('to_login', 'do.php?ac=login', 0);
	}
}

//�������
function lang($key, $vars=array()) {
	global $_SGLOBAL;
	
	include_once(S_ROOT.'./language/lang_source.php');
	if(isset($_SGLOBAL['sourcelang'][$key])) {
		$result = lang_replace($_SGLOBAL['sourcelang'][$key], $vars);
	} else {
		$result = $key;
	}
	return $result;
}

//�������
function mlang($key) {
	global $_SGLOBAL;
	
	include_once(S_ROOT.'./language/lang_main.php');
	if(isset($_SGLOBAL['mainlang'][$key])) {
		$result = $_SGLOBAL['mainlang'][$key];
	} else {
		$result = $key;
	}
	return $result;
}

//�����滻
function lang_replace($text, $vars) {
	if($vars) {
		foreach ($vars as $k => $v) {
			$rk = $k + 1;
			$text = str_replace('\\'.$rk, $v, $text);
		}
	}
	return $text;
}

//����û�����
function getfriendgroup() {
	global $_SCONFIG, $space;
	
	$groups = array();
	$spacegroup = empty($space['privacy']['groupname'])?array():$space['privacy']['groupname'];
	for($i=0; $i<$_SCONFIG['groupnum']; $i++) {
		if($i == 0) {
			$groups[0] = lang('friend_group_default');
		} else {
			if(!empty($spacegroup[$i])) {
				$groups[$i] = $spacegroup[$i];
			} else {
				$groups[$i] = lang('friend_group').'('.$i.')';
			}
		}
	}
	return $groups;
}

//��ȡ����
function sub_url($url, $length) {
	if(strlen($url) > $length) {
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	return $url;
}

?>