<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
header('Content-Type: text/html; charset=utf-8');
$do=$_GET['do'];
if(empty($do)){
	require_once(iPATH."include/function/template.php");
	$iCMS->message();
}elseif($do=='post'){
	if($_POST['action']=='save'){
		$state		=0;
		ckseccode($_POST['seccode']) && msgJson(0,'error:seccode');
		$user			= array();
		$user["name"]	= dhtmlspecialchars($_POST['name']);
	    $user["m"]		= intval($_POST['m']);
	    $user["email"]	= dhtmlspecialchars($_POST['mail']);
	    $user["homepage"]=$_POST['homepage']=='http://'?'':dhtmlspecialchars($_POST['homepage']);
	    $secret			=$_POST['secret'];
	    $messagetext	=$_POST['messagetext'];
	    WordFilter($user["name"]) && msgJson(0,'filter:username');
	    WordFilter($messagetext) && msgJson(0,'filter:content');
	    
		!eregi("^([_\.0-9a-z-]+)@([0-9a-z][0-9a-z-]+)\.([a-z]{2,6})$",$user["email"]) && msgJson(0,'error:email');
		empty($messagetext) && msgJson(0,'message:empty');
		$userdate=serialize($user);
		empty($secret) && $secret='off';

		$iCMS->db->query("INSERT INTO `#iCMS@__message`(`user`,`text`,`reply`,`secret`,`addtime`,`ip`)VALUES ('$userdate','$messagetext','','$secret','".time()."','".getip()."')") &&
		msgJson(1,'message:finish');
	}
}
?>