<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_profield.php 7078 2008-04-14 08:35:08Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageprofield')) {
	cpmessage('no_authority_management_operation');
}

//取得单个数据
$thevalue = $list = array();
$_GET['fieldid'] = empty($_GET['fieldid'])?0:intval($_GET['fieldid']);
if($_GET['fieldid']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." WHERE fieldid='$_GET[fieldid]'");
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
}
if(!empty($_GET['op']) && $_GET['op'] != 'add' && empty($thevalue)) {
	cpmessage('there_is_no_designated_users_columns');
}

if(submitcheck('fieldsubmit')) {
	$setarr = array(
		'title' => shtmlspecialchars(trim($_POST['title'])),
		'note' => shtmlspecialchars(trim($_POST['note'])),
		'formtype' => shtmlspecialchars(trim($_POST['formtype'])),
		'inputnum' => intval($_POST['inputnum']),
		'choice' => shtmlspecialchars(trim($_POST['choice'])),
		'displayorder' => intval($_POST['displayorder'])
	);
	$_POST['fieldid'] = intval($_POST['fieldid']);
	if(empty($thevalue['fieldid'])) {
		inserttable('profield', $setarr);
	} else {
		updatetable('profield', $setarr, array('fieldid'=>$thevalue['fieldid']));
	}
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	profield_cache();
	
	cpmessage('do_success', 'admincp.php?ac=profield');
	
} elseif (submitcheck('ordersubmit')) {
	foreach ($_POST['displayorder'] as $fieldid => $value) {
		updatetable('profield', array('displayorder'=>intval($value)), array('fieldid'=>intval($fieldid)));
	}
	
	//更新缓存
	include_once(S_ROOT.'./source/function_cache.php');
	profield_cache();
	
	cpmessage('do_success', 'admincp.php?ac=profield');
}

if(empty($_GET['op'])) {
	//列表
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}

} elseif($_GET['op'] == 'add') {
	//添加
	$thevalue = array('filedid' => 0, 'formtype' => 'text');
	$formtypearr = array();

} elseif($_GET['op'] == 'edit') {

	$formtypearr = array($thevalue['formtype'] => ' selected');
	
} elseif($_GET['op'] == 'delete') {
	include_once(S_ROOT.'./source/function_delete.php');
	if($_GET['fieldid'] && deleteprofield(array($_GET['fieldid']))) {
		//更新缓存
		include_once(S_ROOT.'./source/function_cache.php');
		profield_cache();

		cpmessage('do_success', 'admincp.php?ac=profield');
	} else {
		cpmessage('choose_to_delete_the_columns', 'admincp.php?ac=profield');
	}
}

?>