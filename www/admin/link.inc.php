<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'add':
		include	iCMS_admincp_tpl('link.add');
	break;
	case 'post':
		if($action=='edit'){
			if(isset($_POST['delete'])){
				foreach($_POST['delete'] as $k=>$id){
					$iCMS->db->query("delete from `#iCMS@__links` where `id`='$id'");
				}
				_Header();
			}
			foreach($_POST['name'] as $id=>$value){
				$iCMS->db->query("update `#iCMS@__links` set `name`='$value',`logo`='".$_POST['logo'][$id]."',`url`='".$_POST['url'][$id]."',`desc`='".$_POST['description'][$id]."',`orderid`='".$_POST['displayorder'][$id]."' where `id`='$id'");
			}
			_Header();
		}
		if($action=='add'){
			$name	= dhtmlspecialchars($_POST['name']); 
			$url	= dhtmlspecialchars($_POST['url']); 
			$desc	= dhtmlspecialchars($_POST['description']); 
			$logo	= dhtmlspecialchars($_POST['logo']); 
			$orderid= intval($_POST['displayorder']);
			empty($name)&&alert('网站名称不能为空!');
			empty($url)&&alert('网站URL不能为空!');
			strpos($url,'http://')===false && $url='http://'.$url;
			$iCMS->db->query("INSERT INTO `#iCMS@__links` (`name`,`logo`,`desc`,`url`,`orderid`) VALUES ('$name','$logo','$desc','$url','$orderid')");
			_Header(__SELF__."?do=link");
		}
	break;
	default:
		$Admin->MP(array("menu_index_link","menu_link"));
		$maxperpage = 60;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__links` ORDER BY `logo`, `orderid` ASC");
		page($total,$maxperpage,'个链接');
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__links` ORDER BY `logo`, `orderid` ASC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include	iCMS_admincp_tpl('link');
}
?>
