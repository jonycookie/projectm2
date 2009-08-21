<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_network.php 7004 2008-04-09 09:12:00Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(!checkperm('managenetwork')) {
	cpmessage('no_authority_management_operation');
}

//取得单个数据
$thevalue = array();
$network = data_get('network');
$network = empty($network)?array():unserialize(sstripslashes($network));
$module = trim($_GET['module'])?trim($_GET['module']):'';

if(submitcheck('thevaluesubmit')) {
	$key = key($_POST['network']);
	$networkcache = array();
	$wherearr = $sql = array();
	if(empty($_POST['network'][$key]['usedefault'])) {
		$_POST['network'][$key]['start'] = intval($_POST['network'][$key]['start']);
		$_POST['network'][$key]['limit'] = intval($_POST['network'][$key]['limit']) ? intval($_POST['network'][$key]['limit']) : 1;
		switch($key) {
			case 'space':
				$uids = getdotstring($_POST['network'][$key]['uid'], 'int');
				if($uids) $wherearr[] = 'uid IN ('.$uids.')';
				if($_POST['network'][$key]['updatetime']) {
					 $wherearr[] = getscopequery('updatetime', $_POST['network'][$key]['updatetime'], 1);
				}
				if($_POST['network'][$key]['dateline']) {
					 $wherearr[] = getscopequery('dateline', $_POST['network'][$key]['dateline'], 1);
				}
				$scopequery = getscopequery('viewnum', $_POST['network'][$key]['viewnum']);
				if($scopequery) $wherearr[] = $scopequery;
				$scopequery = getscopequery('friendnum', $_POST['network'][$key]['friendnum']);
				if($scopequery) $wherearr[] = $scopequery;
				$scopequery = getscopequery('credit', $_POST['network'][$key]['credit']);
				if($scopequery) $wherearr[] = $scopequery;
				if($wherearr) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY '.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
			case 'doing':
				$uids = getdotstring($_POST['network'][$key]['uid'], 'int');
				if($uids) $wherearr[] = 'uid IN ('.$uids.')';
				if($_POST['network'][$key]['dateline']) {
					 $wherearr[] = getscopequery('dateline', $_POST['network'][$key]['dateline'], 1);
				}
				
				if($wherearr) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY '.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
			case 'blog':
				$wherearr[] = "b.friend='0'";
				if($_POST['network'][$key]['dateline']) {
					 $wherearr[] = getscopequery('dateline', $_POST['network'][$key]['dateline'], 1, 'b');
				}
				
				$uids = getdotstring($_POST['network'][$key]['uid'], 'int');
				if($uids) $wherearr[] = 'b.uid IN ('.$uids.')';
				$scopequery = getscopequery('viewnum', $_POST['network'][$key]['viewnum'], 0, 'b');
				if($scopequery) $wherearr[] = $scopequery;
				
				$scopequery = getscopequery('replynum', $_POST['network'][$key]['replynum'], 0, 'b');
				if($scopequery) $wherearr[] = $scopequery;
				if($wherearr) $sql['where'] = implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY b.'.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
			case 'album':
				$wherearr[] = "friend='0'";
				$uids = getdotstring($_POST['network'][$key]['uid'], 'int');
				if($uids) $wherearr[] = 'uid IN ('.$uids.')';
				
				if($_POST['network'][$key]['updatetime']) {
					 $wherearr[] = getscopequery('updatetime', $_POST['network'][$key]['updatetime'], 1);
				}
				if($_POST['network'][$key]['dateline']) {
					 $wherearr[] = getscopequery('dateline', $_POST['network'][$key]['dateline'], 1);
				}
				$scopequery = getscopequery('picnum', $_POST['network'][$key]['picnum']);
				if($scopequery) $wherearr[] = $scopequery;
				
				if($wherearr) $sql['where'] = implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY '.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
			case 'share':
				$uids = getdotstring($_POST['network'][$key]['uid'], 'int');
				if($uids) $wherearr[] = 'uid IN ('.$uids.')';
				if($_POST['network'][$key]['dateline']) {
					 $wherearr[] = getscopequery('dateline', $_POST['network'][$key]['dateline'], 1);
				}
				
				if($wherearr) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY '.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
			case 'mtag':
				$tagids = getdotstring($_POST['network'][$key]['tagid'], 'int');
				if($tagids) $wherearr[] = 'tagid IN ('.$tagids.')';
				
				$fieldids = getdotstring($_POST['network'][$key]['fieldid'], 'int');
				if($fieldids) $wherearr[] = 'fieldid IN ('.$fieldids.')';
				$scopequery = getscopequery('membernum', $_POST['network'][$key]['membernum']);
				if($scopequery) $wherearr[] = $scopequery;
				if($wherearr) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY '.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
			case 'thread':
	
				$uids = getdotstring($_POST['network'][$key]['uid'], 'int');
				if($uids) $wherearr[] = 'uid IN ('.$uids.')';
				if($_POST['network'][$key]['lastpost']) {
					 $wherearr[] = getscopequery('lastpost', $_POST['network'][$key]['lastpost'], 1);
				}
				if($_POST['network'][$key]['dateline']) {
					 $wherearr[] = getscopequery('dateline', $_POST['network'][$key]['dateline'], 1);
				}
				
				$scopequery = getscopequery('viewnum', $_POST['network'][$key]['viewnum']);
				if($scopequery) $wherearr[] = $scopequery;
				
				$scopequery = getscopequery('replynum', $_POST['network'][$key]['replynum']);
				if($scopequery) $wherearr[] = $scopequery;
				if($wherearr) $sql['where'] = 'WHERE '.implode(' AND ', $wherearr);
				if($_POST['network'][$key]['order']) {
					$sql['order'] = 'ORDER BY '.$_POST['network'][$key]['order'].' '.$_POST['network'][$key]['sc'];
				}
				$sql['limit'] = getlimit($_POST['network'][$key]['start'], $_POST['network'][$key]['limit']);
				break;
		}
		
		$sqlstring = implode(' ', $sql);
		$_POST['network'][$key]['sql'] = $sqlstring;
		$network[$key] = $_POST['network'][$key];
	} else {
		$network[$key] = array();
		$network[$key]['usedefault'] = 1;
	}
	foreach(array('space', 'doing', 'blog', 'album', 'share', 'mtag', 'thread') as $val) {
		$sql = '';
		$sql = trim($network[$val]['sql']);
		$networkcache[$val] = empty($sql) ? '' : $sql;
	}

	include_once(S_ROOT.'./source/function_cache.php');
	cache_write('network_setting', "network", $networkcache);
	data_set('network', addslashes(serialize($network)));
	cpmessage('do_success', 'admincp.php?ac=network');
} elseif(submitcheck('settingsubmit')) {
	
	$setarr = array();
	foreach ($_POST['config'] as $var => $value) {
		$value = trim($value);
		if(!isset($_SCONFIG[$var]) || $_SCONFIG[$var] != $value) {
			$setarr[] = "('$var', '$value')";
		}
	}
	if($setarr) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $setarr));
		//更新缓存
		include_once(S_ROOT.'./source/function_cache.php');
		config_cache();
	}
	cpmessage('do_success', 'admincp.php?ac=network');
}

$configs = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('config')." WHERE var IN ('networkpage', 'networkupdate')");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['datavalue'] = shtmlspecialchars($value['datavalue']);
	$configs[$value['var']] = $value['datavalue'];
}

if($module == 'mtag') {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
}

if(empty($network[$module])) {
	$network[$module]['usedefault'] = 1;
}

//生成选择框
function getselectstr($var, $optionarray, $value='', $other='') {

	$selectstr = '<select id="'.$var.'" name="'.$var.'"'.$other.'>';
	foreach ($optionarray as $optionkey => $optionvalue) {
		$selectstr .= '<option value="'.$optionkey.'">'.$optionvalue.'</option>';
	}
	$selectstr = str_replace('value="'.$value.'"', 'value="'.$value.'" selected', $selectstr);
	$selectstr .= '</select>';
	return $selectstr;
}

function getscopequery($var, $tarr, $isdate=0, $pre='') {
	global $_SGLOBAL;

	$wheresql = '';
	if(!empty($pre)) $pre = $pre.'.';
	if($tarr) {
		if($isdate) {
			$tarr = intval($tarr);
			if($tarr) $wheresql = $pre.$var.'>='.($_SGLOBAL['timestamp']-$tarr);
		} else {
			$tarr[0] = intval($tarr[0]);
			$tarr[1] = intval($tarr[1]);
			if($tarr[0] && $tarr[1] && $tarr[1] > $tarr[0]) {
				$wheresql = '('.$pre.$var.'>='.$tarr[0].' AND '.$pre.$var.'<='.$tarr[1].')';
			} else if($tarr[0] && empty($tarr[1])) {
				$wheresql = $pre.$var.'>='.$tarr[0];
			} else if(empty($tarr[0]) && $tarr[1]) {
				$wheresql = $pre.$var.'<='.$tarr[1];
			}
		}
	}
	return $wheresql;
}
//如果$string不是变量，则返回加上‘’的字符串
function getdotstring ($string, $vartype, $allownull=false, $varscope=array(), $sqlmode=1, $unique=true) {

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
function getlimit($start, $limit) {
	$start = $start? $start : 0;;
	$limit = $limit ? $limit : 1;
	return 'LIMIT '.$start.','.$limit;
}
	

?>