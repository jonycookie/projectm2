<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
require_once(iPATH."include/function/template.php");

$action=$_GET['action'];
if($action=='list'){
	$iCMS->assign('get',$_GET);
	echo $iCMS->iPrint("iSYSTEM","js/list");
}
if($action=='comment'){
	$iCMS->get['id']=(int)$_GET['aid'];
	$iCMS->assign('mid',(int)$_GET['mid']);
	$iCMS->assign('sortid',(int)$_GET['sortid']);
	echo $iCMS->iPrint("iSYSTEM","js/comment");
}
?>