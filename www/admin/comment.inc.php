<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'examine':
		$id=intval($_GET['id']);
		$aid=intval($_GET['aid']);
		$id && $iCMS->db->query("UPDATE `#iCMS@__comment` SET `isexamine` = '1' WHERE `id` ='$id'");
		$aid&&$iCMS->db->query("UPDATE `#iCMS@__article` SET `comments` = comments+1  WHERE `id` ='$aid'");
		_Header(__SELF__.'?do=comment');
	break;
	case 'cancelexamine':
		$id=intval($_GET['id']);
		$aid=intval($_GET['aid']);
		$id && $iCMS->db->query("UPDATE `#iCMS@__comment` SET `isexamine` = '0' WHERE `id` ='$id'");
		$aid&&$iCMS->db->query("UPDATE `#iCMS@__article` SET `comments` = comments-1  WHERE `id` ='$aid'");
		_Header(__SELF__.'?do=comment');
	break;
	case 'del':
		$id	=intval($_GET['id']);
		$aid=intval($_GET['aid']);
		$id && $iCMS->db->query("DELETE FROM `#iCMS@__comment` WHERE `id` ='$id'");
		$aid&&$iCMS->db->query("UPDATE `#iCMS@__article` SET `comments` = comments-1  WHERE `id` ='$aid'");
		_Header(__SELF__.'?do=comment');
	break;
	case 'post':
		if($action=="del"){
			if(isset($_POST['id'])){
				foreach($_POST['id'] as $k=>$id){
					$aid=$_POST['aid'][$id];
					$iCMS->db->query("DELETE FROM `#iCMS@__comment` WHERE `id` ='$id'");
					$iCMS->db->query("UPDATE `#iCMS@__article` SET `comments` = comments-1  WHERE `id` ='$aid'");
				}
				_Header(__SELF__.'?do=comment');
			}else{
				alert("请选择要删除的评论！");
			}
		}
	break;
	default:
		$Admin->MP(array("menu_index_comment","menu_comment"));
		include_once(iPATH.'include/ubb.fun.php');
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__comment` order by id DESC");
		page($total,$maxperpage,"条评论");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__comment` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include iCMS_admincp_tpl('comment');
}
?>
