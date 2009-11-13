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
		$id=intval($_GET['id']);
		$id && $iCMS->db->query("DELETE FROM `#iCMS@__message` WHERE `id` ='$id'");
		_Header(__SELF__.'?do=message');
	break;
	case 'post':
		if(isset($_POST['delete'])){
			foreach($_POST['delete'] as $k=>$id){
				$id && $iCMS->db->query("DELETE FROM `#iCMS@__message` WHERE `id` ='$id'");
			}
			_Header(__SELF__.'?do=message');
		}else{
			alert("请选择要删除的留言！");
		}
	break;
	default:
		$Admin->MP("menu_message");
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__message` order by id DESC");
		page($total,$maxperpage,"条留言");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__message` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include iCMS_admincp_tpl('message');
}
?>
