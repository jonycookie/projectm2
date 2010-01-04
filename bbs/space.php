<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: space.php 11169 2007-11-08 06:30:00Z tiger $
*/

define('CURSCRIPT', 'space');
require_once './include/common.inc.php';

$discuz_action = 150;

$querystring = explode('/', $_SERVER['QUERY_STRING']);
$uid = !empty($uid) ? intval($uid) : intval($querystring[0]);
$username = !isset($username) || $uid ? '' : $username;
$mod = !empty($mod) ? $mod : $querystring[1];
$starttime = !empty($starttime) ? intval($starttime) : intval($querystring[2]);
$endtime = !empty($endtime) ? intval($endtime) : intval($querystring[3]);
$multipage = $titleextra = '';
$menulist = $modulelist = array();

$action = $inajax ? 'viewpro' : $action;
if(!empty($action) && $action == 'viewpro') {
	require_once DISCUZ_ROOT.'./include/viewpro.inc.php';
}

if(!$mod || $mod == 'myblogs') {
	require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
}
require_once DISCUZ_ROOT.'./include/space.func.php';
include_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
include_once language('spaces');

$member = $db->fetch_first("SELECT m.*, mf.*, s.lastactivity as online
	FROM {$tablepre}members m
	LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
	LEFT JOIN {$tablepre}sessions s ON s.uid=m.uid AND s.invisible='0'
	WHERE ".($uid ? "m.uid='$uid'" : "m.username='$username'")." LIMIT 1");
if(!$member) {
	showmessage('member_nonexistence');
}

$uid = $member['uid'];

if($spacestatus && $supe['status'] && $member['xspacestatus']) {
	dheader("location: $supe[siteurl]?uid/$uid");
}

if(!$spacestatus || in_array($member['groupid'], array(4, 5, 6))) {
	dheader("location: {$boardurl}space.php?action=viewpro&uid=$uid");
}

$spacesettings = getspacesettings($uid);

if(!empty($preview) && $uid == $discuz_uid) {
	$spacesettings['layout'] = explode('|', $preview);
	$spacesettings['side'] = intval($spaceside);
} else {
	$spacesettings['layout'] = explode("\t", $spacesettings['layout']);
}
if(!empty($style)) {
	$spacesettings['style'] = str_replace('/', '', $style);
	if(!file_exists(DISCUZ_ROOT.'./mspace/'.$spacesettings['style'].'/style.ini')) {
		showmessage('space_style_nofound', NULL, 'HALTED');
	}
}

$layout = $moduledata = array();
foreach($spacesettings['layout'] as $k => $layoutitem) {
	$layout[$k] = explode('][', ']'.$layoutitem.'[');
	$layout[$k] = array_slice($layout[$k], 1, count($layout[$k]) - 2);
	$newlayout = array();
	foreach($layout[$k] as $module) {
		if(array_key_exists($module, $modulesettings)) {
			$newlayout[] = $module;
		}
	}
	$layout[$k] = $newlayout;
	$modulelist = array_merge($modulelist, $layout[$k]);
}

if(!empty($mod) && array_key_exists($mod, $listmodule)) {
	if(!intval($spacedata['limit'.$mod]) || !in_array($mod, $modulelist)) {
		if($mod == 'mytrades' && intval($spacedata['limit'.$mod])) {
			$modulelist[] = 'mytrades';
			$spacesettings['layout'][1] .= '[mytrades]';
			$spacesettings['layout'] = daddslashes($spacesettings['layout'], 1);
			$db->query("UPDATE {$tablepre}memberspaces SET layout='".$spacesettings['layout'][0]."\t".$spacesettings['layout'][1]."\t".$spacesettings['layout'][2]."' WHERE uid='$uid'");
		} else {
			dheader("location: {$boardurl}space.php?uid=$uid");
		}
	}
	if($spacesettings['side'] == 1) {
		$side = 0;
	} elseif($spacesettings['side'] == 2) {
		$side = 2;
	} else {
		$side = 0;
		$spacesettings['side'] = 1;
	}
	$layout[$side] = array('userinfo');
	$layout[1] = array($mod);
	$titleextra = ' - '.$spacelanguage[$mod];
	$moduledata[$mod]['value'] = updatespacecache($uid, $mod, TRUE);
	if($mod == 'myblogs') {
		$layout[$side][] = 'calendar';
		if($discuz_uid && $uid == $discuz_uid && $allowpost && $allowuseblog) {
			require_once DISCUZ_ROOT.'./include/forum.func.php';
			$forumselect = forumselect();
			if($discuz_uid == $uid) {
				$layout[$side][] = 'postblog';
			}
		}
		$layout[$side][] = 'hotblog';
		$layout[$side][] = 'lastpostblog';
		$hotblog = spacecaches($uid, array('hotblog', 'lastpostblog'));
		$moduledata = array_merge($moduledata, $hotblog);
	} elseif($mod == 'mytrades') {
		$layout[$side][] = 'mytradetypes';
		$layout[$side][] = 'tradeinfo';
		if(!isset($tradetypeid)) {
			$layout[1][] = 'mycounters';
			$moduledata['mycounters']['value'] = updatespacecache($uid, 'mycounters', TRUE);
		}
		$tmp = spacecaches($uid, 'mytradetypes');
		$moduledata['mytradetypes']['value'] = $tmp['mytradetypes']['value'];
		$moduledata['tradeinfo']['value'] = $member;
	}
} else {
	$moduledata = spacecaches($uid, $modulelist);
}

foreach($modulelist as $module) {
	if(array_key_exists($module, $listmodule) && intval($spacedata['limit'.$module])) {
		$menulist[$listmodule[$module]] = $module;
	}
}
ksort($menulist);

$moduledata['userinfo']['value'] = $member;

include template('space_module');
include template('space');

?>