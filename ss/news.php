<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: news.php 13386 2009-10-14 01:32:10Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$channel = $_SGET['action'];
if(!checkperm('allowview')) {
	showmessage('no_permission');
}

if(!empty($_SCONFIG['htmlindex'])) {
	$_SHTML['action'] = 'news';
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlindextime']);
	$_SCONFIG['debug'] = 0;
}

$title = $channels['menus'][$_SGET['action']]['name'].' - '.$_SCONFIG['sitename'];
$keywords = $channels['menus'][$_SGET['action']]['name'];
$description = $channels['menus'][$_SGET['action']]['name'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/'.$_SGET['action']),'name' => $channels['menus'][$_SGET['action']]['name']);

if(!empty($channels['menus'][$_SGET['action']]['tpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$channels['menus'][$_SGET['action']]['tpl'].'.html.php')) {
	$tplname = $channels['menus'][$_SGET['action']]['tpl'];
} else {
	$tplname = 'news_index';
}

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template($tplname);

ob_out();

if(!empty($_SCONFIG['htmlindex'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

?>