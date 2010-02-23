<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
case 'del':
	$id=(int)$_GET['id'];
	$id && $iCMS->db->query("DELETE FROM `#iCMS@__contentype` WHERE `id` ='$id'");
	_Header();
break;
case 'post':
	if($action=='edit'){
		if(isset($_POST['delete'])){
			foreach($_POST['delete'] as $k=>$id){
				$id && $iCMS->db->query("DELETE FROM `#iCMS@__contentype` WHERE `id` ='$id'");
			}
			_Header();
		}
		foreach($_POST['name'] as $id=>$value){
			$iCMS->db->query("update `#iCMS@__contentype` set `name`='$value',`type`='".$_POST['type'][$id]."',`val`='".$_POST['val'][$id]."' where `id`='$id'");
		}
		_Header();
		
	}
	if($action=='add'){
		$name=dhtmlspecialchars($_POST['name']);
		$type=dhtmlspecialchars($_POST['type']);
		$val=intval($_POST['val']);
		$iCMS->db->query("INSERT INTO `#iCMS@__contentype` (`name`,`val`,`type`) VALUES ('$name','$val','$type')");
		_Header(__SELF__.'?do=contentype');
	}
break;
default:
	$Admin->MP("menu_contentype");
	$maxperpage =20;
	$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__contentype` order by id DESC");
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__contentype` order by id DESC ");
	$_count=count($rs);
	include iCMS_admincp_tpl('contentype');
}
?>
