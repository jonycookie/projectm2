<?php
if(!defined('IN_UCHOME')) exit('Access Denied');
$_SGLOBAL['creditrule']=Array
	(
	'get' => Array
		(
		'blog' => 2,
		'pic' => 1,
		'comment' => 1,
		'thread' => 2,
		'post' => 1,
		'invite' => 10
		),
	'pay' => Array
		(
		'blog' => 2,
		'pic' => 1,
		'comment' => 1,
		'thread' => 2,
		'post' => 1,
		'search' => 1,
		'attach' => 10,
		'xmlrpc' => 5,
		'invite' => 0,
		'domain' => 10,
		'realname' => 10
		)
	)
?>