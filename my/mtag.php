<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network.php 7293 2008-05-06 01:49:26Z liguode $
*/

include_once('./common.php');

//是否关闭站点
checkclose();

//处理rewrite
if($_SCONFIG['allowrewrite'] && isset($_GET['rewrite'])) {
	$rws = explode('-', $_GET['rewrite']);
	$_GET['do'] = $rws[0];
	if(isset($rws[1])) {
		$rw_count = count($rws);
		for ($rw_i=1; $rw_i<$rw_count; $rw_i=$rw_i+2) {
			$_GET[$rws[$rw_i]] = empty($rws[$rw_i+1])?'':$rws[$rw_i+1];
		}
	}
	unset($_GET['rewrite']);
}

//允许的方法
$dos = array('index', 'thread', 'member');
$do = (empty($_GET['do']) || !in_array($_GET['do'], $dos))?'index':$_GET['do'];
$theurl = "mtag.php?do=$do";

$space = $_SGLOBAL['supe_uid']?getspace($_SGLOBAL['supe_uid']):array();

//数据处理
include_once(S_ROOT."./source/mtag_{$do}.php");
?>