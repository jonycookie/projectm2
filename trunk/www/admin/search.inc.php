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
			$rs	= $iCMS->db->getRow("SELECT * FROM `#iCMS@__search` where `id`='$id'");
		}
		include iCMS_admincp_tpl('search.add');
	break;
	case 'del':
		$id=(int)$_GET['id'];
		$id && $iCMS->db->query("DELETE FROM `#iCMS@__search` WHERE `id` ='$id'");
		search_cache();
		_Header();
	break;
	case 'disabled':
		$id=(int)$_GET['id'];
		$id&&$iCMS->db->query("UPDATE `#iCMS@__search` SET `visible` = '0'  WHERE `id` ='$id'");
		search_cache();
		_Header();
	break;
	case 'open':
		$id=(int)$_GET['id'];
		$id&&$iCMS->db->query("UPDATE `#iCMS@__search` SET `visible` = '1'  WHERE `id` ='$id'");
		search_cache();
		_Header();
	break;
	case 'post':
		if($action=='edit'){
			if(isset($_POST['delete'])){
				foreach($_POST['delete'] as $k=>$id){
					$id && $iCMS->db->query("DELETE FROM `#iCMS@__search` WHERE `id` ='$id'");
				}
				search_cache();
				_Header();
			}
			foreach($_POST['search'] as $id=>$value){
				$value=str_replace(array('%','_'),array('\%','\_'),$value);
				$iCMS->db->query("update `#iCMS@__search` set `search`='$value',`times`='".$_POST['times'][$id]."' where `id`='$id'");
			}
			search_cache();
			_Header();
			
		}
		if($action=='save'){
			$id	= (int)$_POST['id'];
			$search=dhtmlspecialchars($_POST['search']);
			$search=str_replace(array('%','_'),array('\%','\_'),$search);
			$times=(int)$_POST['times'];
			if(empty($id)){
				$iCMS->db->query("insert into `#iCMS@__search`(`search`,`times`,`addtime`) values ('$search','$times','".time()."')");
			}else{
				$iCMS->db->query("update `#iCMS@__search` set `search`='$search',`times`='$times' where id='$id'");
			}
			search_cache();
			_Header(__SELF__.'?do=search');
		}
	break;
	default:
		$Admin->MP("menu_search");
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__search` order by id DESC");
		page($total,$maxperpage,"个关键字");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__search` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include iCMS_admincp_tpl('search');
}
?>
