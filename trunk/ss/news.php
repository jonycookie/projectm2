<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: news.php 10898 2008-12-31 02:58:50Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(!empty($_SCONFIG['htmlindex'])) {
	$_SHTML['action'] = 'news';
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlindextime']);
	$_SCONFIG['debug'] = 0;
}

$title = $lang['news'].' - '.$_SCONFIG['sitename'];
$keywords = $lang['news'];
$description = $lang['news'];

$guidearr = array();
$guidearr[] = array('url' => geturl('action/news'),'name' => $channels['menus']['news']['name']);

$tplname = 'news_index';

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