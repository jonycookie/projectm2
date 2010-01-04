<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: counter.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

$visitor = array();

$visitor['agent'] = $_SERVER['HTTP_USER_AGENT'];
list($visitor['month'], $visitor['week'], $visitor['hour']) = explode("\t", gmdate("Ym\tw\tH", $timestamp + $_DCACHE['settings']['timeoffset'] * 3600));

if(!$sessionexists) {
	if(strpos($visitor['agent'], 'Netscape')) {
		$visitor['browser'] = 'Netscape';
	} elseif(strpos($visitor['agent'], 'Lynx')) {
		$visitor['browser'] = 'Lynx';
	} elseif(strpos($visitor['agent'], 'Opera')) {
		$visitor['browser'] = 'Opera';
	} elseif(strpos($visitor['agent'], 'Konqueror')) {
		$visitor['browser'] = 'Konqueror';
	} elseif(strpos($visitor['agent'], 'MSIE')) {
		$visitor['browser'] = 'MSIE';
	} elseif(substr($visitor['agent'], 0, 7) == 'Mozilla') {
		$visitor['browser'] = 'Mozilla';
	} else {
		$visitor['browser'] = 'Other';
	}

	if(strpos($visitor['agent'], 'Win')) {
		$visitor['os'] = 'Windows';
	} elseif(strpos($visitor['agent'], 'Mac')) {
		$visitor['os'] = 'Mac';
	} elseif(strpos($visitor['agent'], 'Linux')) {
		$visitor['os'] = 'Linux';
	} elseif(strpos($visitor['agent'], 'FreeBSD')) {
		$visitor['os'] = 'FreeBSD';
	} elseif(strpos($visitor['agent'], 'SunOS')) {
		$visitor['os'] = 'SunOS';
	} elseif(strpos($visitor['agent'], 'OS/2')) {
		$visitor['os'] = 'OS/2';
	} elseif(strpos($visitor['agent'], 'AIX')) {
		$visitor['os'] = 'AIX';
	} elseif(preg_match("/(Bot|Crawl|Spider)/i", $visitor['agent'])) {
		$visitor['os'] = 'Spiders';
	} else {
		$visitor['os'] = 'Other';
	}
	$visitorsadd = "OR (type='browser' AND variable='$visitor[browser]') OR (type='os' AND variable='$visitor[os]')".
		($discuz_user ? " OR (type='total' AND variable='members')" : " OR (type='total' AND variable='guests')");
	$updatedrows = 7;
} else {
	$visitorsadd = '';
	$updatedrows = 4;
}

$db->query("UPDATE {$tablepre}stats SET count=count+1 WHERE (type='total' AND variable='hits') $visitorsadd OR (type='month' AND variable='$visitor[month]') OR (type='week' AND variable='$visitor[week]') OR (type='hour' AND variable='$visitor[hour]')");

if($updatedrows > $db->affected_rows()) {
	$db->query("INSERT INTO {$tablepre}stats (type, variable, count)
			VALUES ('month', '$visitor[month]', '1')", 'SILENT');
}

?>