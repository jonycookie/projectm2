<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'manage':
		$Admin->MP("menu_group_manage");
		include iPATH.'include/group.class.php';
		$group = new group();
		$type  = $_GET['type'];
		include iCMS_admincp_tpl('group.manage');
	break;
	case 'power':
		$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__group` WHERE `gid`='".intval($_GET['groupid'])."'");
		include iCMS_admincp_tpl('group.power');
	break;
	case 'cpower':
		include_once iPATH.'include/catalog.class.php';
		$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__group` WHERE `gid`='".intval($_GET['groupid'])."'");
		iCMS_admincp_head();
		$catalog =new catalog();
		$catalog->allArray();
		include iCMS_admincp_tpl('group.cpower');
	break;
	case 'del':
		$gid=(int)$_GET['groupid'];
		$gid&&$iCMS->db->query("DELETE FROM `#iCMS@__group` WHERE `gid`='$gid'");
		alert('已删除!',"url:".__SELF__."?do=group&operation=manage");
	break;
	case 'post':
		if($action=='power'){
			$gid=(int)$_POST['gid'];
			$power=@implode(",",$_POST['power']);
		    $iCMS->db->query("UPDATE `#iCMS@__group` SET `power` = '{$power}' WHERE `gid` ='$gid' LIMIT 1");
		    redirect("设置完成!",__SELF__.'?do=group&operation=manage');
		}elseif($action=='cpower'){
			$gid=(int)$_POST['gid'];
			$power=@implode(",",$_POST['cpower']);
		    $iCMS->db->query("UPDATE `#iCMS@__group` SET `cpower` = '{$power}' WHERE `gid` ='$gid' LIMIT 1");
		    redirect("设置完成!",__SELF__.'?do=group&operation=manage');
		}elseif($action=='edit'){
			foreach($_POST['name'] as $id=>$value){
				$iCMS->db->query("update `#iCMS@__group` set `name`='$value',`order`='".$_POST['order'][$id]."' where `gid`='$id'");
			}
			if($_POST['addnewname']){
				$iCMS->db->query("INSERT INTO `#iCMS@__group`(`gid`,`name`,`order`,`power`,`cpower`,`type`) VALUES (NULL,'".$_POST['addnewname']."','".$_POST['addneworder']."','','','".$_POST['type']."')");
			}
			_Header();
		}
	break;
}

?>
