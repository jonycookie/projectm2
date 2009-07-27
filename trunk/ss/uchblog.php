<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: uchblog.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(!empty($_SCONFIG['htmlindex'])) {
	$_SHTML['action'] = 'uchblog';
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlindextime']);
	$_SCONFIG['debug'] = 0;
}

$title = $channels['menus'][$_SGET['action']]['name'].' - '.$_SCONFIG['sitename'];
$keywords = $channels['menus'][$_SGET['action']]['name'];
$description = $channels['menus'][$_SGET['action']]['name'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/uchblog'),'name' => $channels['menus']['uchblog']['name']);

$tplname = 'blog_index';

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