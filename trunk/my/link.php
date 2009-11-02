<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: link.php 7236 2008-04-30 01:20:22Z liguode $
*/

include_once('./common.php');

$refer = empty($_SGLOBAL['refer'])?"space.php?do=home":$_SGLOBAL['refer'];

if(empty($_GET['url'])) {
	showmessage('do_success', $refer, 0);
} else {
	$show_url = 'http://'.base64_decode($_GET['url']);
	$url = shtmlspecialchars_decode($show_url);
	
	if(!$_SCONFIG['linkguide']) {
		showmessage('do_success', $url, 0);//直接跳转
	}
	
	$url_ext = str_replace('http://www.', 'http://', $url);
	$now_url_ext = str_replace('http://www.', 'http://', getsiteurl());
	if(strexists($url_ext, $now_url_ext)) {
		showmessage('do_success', $url, 0);//本站内的
	}
}

$space = array();
if($_SGLOBAL['supe_uid']) {
	$space = getspace($_SGLOBAL['supe_uid']);
}
if(empty($space)) {
	//游客直接跳转
	showmessage('do_success', $url, 0);
}

$show_url = sub_url($show_url, 70);

//模板调用
include_once template("link");

//html反编译
function shtmlspecialchars_decode($text){
    return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
}


?>