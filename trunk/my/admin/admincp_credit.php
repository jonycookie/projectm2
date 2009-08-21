<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_credit.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//х╗оч
if(!checkperm('managecredit')) {
	cpmessage('no_authority_management_operation');
}

if(submitcheck('creditsubmit')) {

	$ins = array();
	foreach ($_POST['get'] as $key => $value) {
		$ins['get'][$key] = abs(intval($value));
	}
	foreach ($_POST['pay'] as $key => $value) {
		$ins['pay'][$key] = abs(intval($value));
	}
	data_set('creditrule', serialize($ins));
	
	include_once(S_ROOT.'./source/function_cache.php');
	creditrule_cache();
	
	cpmessage('do_success', 'admincp.php?ac=credit');
}

@include_once(S_ROOT.'./data/data_creditrule.php');
if(empty($_SGLOBAL['creditrule'])) {
	$get = $pay = array();
} else {
	$get = $_SGLOBAL['creditrule']['get'];
	$pay = $_SGLOBAL['creditrule']['pay'];
}

?>