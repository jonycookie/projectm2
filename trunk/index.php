<?php

define('KC_INDEX',True);

require_once 'global.php';

function king_def(){

	global $king;

	$url=$king->geturl();

	$classname=$url['classname'];

	$cls=new $classname;

/*
	$cachepath=md5($url);

	$king->cache->get();
*/
//kc_error('<pre>'.print_r($url,1));

	$s=$cls->index($url);

	echo $s;
	
}


?>