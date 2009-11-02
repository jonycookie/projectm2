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
	$_GET['ac'] = $rws[0];
	if(isset($rws[1])) {
		$rw_count = count($rws);
		for ($rw_i=1; $rw_i<$rw_count; $rw_i=$rw_i+2) {
			$_GET[$rws[$rw_i]] = empty($rws[$rw_i+1])?'':$rws[$rw_i+1];
		}
	}
	unset($_GET['rewrite']);
}

//是否公开
if(empty($_SCONFIG['networkpublic'])) {
	checklogin();
}

//允许的方法
$acs = array('space', 'doing', 'blog', 'album', 'mtag', 'thread', 'share');
$ac = (empty($_GET['ac']) || !in_array($_GET['ac'], $acs))?'index':$_GET['ac'];
$theurl = "network.php?ac=$ac";

$space = $_SGLOBAL['supe_uid']?getspace($_SGLOBAL['supe_uid']):array();

//数据处理
include_once(S_ROOT."./source/network_{$ac}.php");

//菜单激活
$menuactives = array('network'=>' class="active"');

//应用列表
@include_once(S_ROOT.'./uc_client/data/cache/apps.php');

//模板调用
$actives = array($ac => ' class="active"');
include_once template("network_$ac");

//判断搜索积分
function cksearchcredit($ac) {
	global $space, $gets;
	
	//搜索积分
	$paycredit = creditrule('pay', 'search');
	$mcredit = empty($space['credit'])?0:intval($space['credit']);
	if($mcredit < $paycredit) {
		showmessage('integral_inadequate','',1,array($mcredit,$paycredit));
	}
	if($paycredit) {
		//不是二次提交
		if(!isset($_GET['confirm'])) {
			$nexturl = "network.php?ac=$ac";
			foreach ($gets as $gkey => $gvalue) {
				if($gvalue) $nexturl .= '&'.$gkey.'='.rawurlencode($gvalue);
			}
			$nexturl .= "&searchmode=1&confirm";
			showmessage('points_deducted_yes_or_no','',1,array($paycredit, $nexturl, $ac));
		}
	}
}

?>