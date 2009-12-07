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
		if($id=(int)$_GET['id']){
			$rs	= $iCMS->db->getRow("SELECT * FROM `#iCMS@__keywords` where `id`='$id'");
		}
		include iCMS_admincp_tpl('keywords.add');
	break;
	case 'del':
		$id=(int)$_GET['id'];
		$id && $iCMS->db->query("DELETE FROM `#iCMS@__keywords` WHERE `id` ='$id'");
		keywords_cache();
		_Header();
	break;
	case 'disabled':
		$id=(int)$_GET['id'];
		$id&&$iCMS->db->query("UPDATE `#iCMS@__keywords` SET `visible` = '0'  WHERE `id` ='$id'");
		keywords_cache();
		_Header();
	break;
	case 'open':
		$id=(int)$_GET['id'];
		$id&&$iCMS->db->query("UPDATE `#iCMS@__keywords` SET `visible` = '1'  WHERE `id` ='$id'");
		keywords_cache();
		_Header();
	break;
	case 'post':
		if($action=='edit'){
			if(isset($_POST['delete'])){
				foreach($_POST['delete'] as $k=>$id){
					$id && $iCMS->db->query("DELETE FROM `#iCMS@__keywords` WHERE `id` ='$id'");
				}
				keywords_cache();
				_Header();
			}
			foreach($_POST['name'] as $id=>$value){
				$iCMS->db->query("update `#iCMS@__keywords` set `keyword`='$value',`replace`='".$_POST['replace'][$id]."' where `id`='$id'");
			}
			keywords_cache();
			_Header();
			
		}
		if($action=='save'){
			$id	= (int)$_POST['id'];
			$keyword=dhtmlspecialchars($_POST['keyword']);
			$replace=$_POST['replace'];
			if(empty($id)){
				$iCMS->db->query("insert into `#iCMS@__keywords`(`keyword`,`replace`,`addtime`,`visible`) values ('$keyword','$replace','".time()."','0')");
			}else{
				$iCMS->db->query("update `#iCMS@__keywords` set `keyword`='$keyword',`replace`='$replace' where id='$id'");
			}
			keywords_cache();
			_Header(__SELF__.'?do=keywords');
		}
	break;
	default:
		$Admin->MP("menu_keywords");
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__keywords` order by id DESC");
		page($total,$maxperpage,"个关键字");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__keywords` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include iCMS_admincp_tpl('keywords');
}
?>
