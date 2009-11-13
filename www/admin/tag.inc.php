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
		$id && $iCMS->db->query("DELETE FROM `#iCMS@__tags` WHERE `id` ='$id'");
		tags_cache();
		_Header();
	break;
	case 'updateHTML':
		$id=(int)$_GET['id'];
		$id && $rs=$iCMS->db->getRow("SELECT `name`,`visible` FROM `#iCMS@__tags` WHERE `id` ='$id'");
		$rs->visible?MakeTagHtm($id):alert("禁用的标签,不能生成静态.");
		_Header();
	break;
	case 'disabled':
		$id=(int)$_GET['id'];
		$id&&$iCMS->db->query("UPDATE `#iCMS@__tags` SET `visible` = '0'  WHERE `id` ='$id'");
		tags_cache();
		_Header();
	break;
	case 'open':
		$id=(int)$_GET['id'];
		$id&&$iCMS->db->query("UPDATE `#iCMS@__tags` SET `visible` = '1'  WHERE `id` ='$id'");
		tags_cache();
		_Header();
	break;
	case 'post':
		if($action=='del'){
			if($_POST['id'])foreach($_POST['id'] as $k=>$id){
				$id && $iCMS->db->query("DELETE FROM `#iCMS@__tags` WHERE `id` ='$id'");
			}
			tags_cache();
		}elseif($action=='sortedit'){
			if($_POST['newsortname']){
				$tSort	= $iCMS->cache('tag.sort','include/syscache',0,true);
				$nSort	= array(
					'id'=>count($tSort)+1,
					'name'=>$_POST['newsortname']
				);
				empty($tSort) && $tSort=array();
				$tSort[]=$nSort;
				$iCMS->cache(false,'include/syscache',0,true,false);
				$iCMS->addcache('tag.sort',$tSort,0);
			}
			if(isset($_POST['delete'])){
				$tSort	= $iCMS->cache('tag.sort','include/syscache',0,true);
				foreach($_POST['delete'] as $k=>$id){
					unset($tSort[$id]);
				}
				$iCMS->cache(false,'include/syscache',0,true,false);
				$iCMS->addcache('tag.sort',$tSort,0);
			}
			foreach($_POST['name'] as $id=>$value){
				$tSort[]= array('id'=>$id,'name'=>$value);
			}
			$iCMS->cache(false,'include/syscache',0,true,false);
			$iCMS->addcache('tag.sort',$tSort,0);
		}elseif($action=='html'){
			if($_POST['id'])foreach($_POST['id'] as $k=>$id){
				$id && $rs=$iCMS->db->getRow("SELECT `name`,`visible` FROM `#iCMS@__tags` WHERE `id` ='$id'");
				$rs->visible && MakeTagHtm($rs->name);
			}
		}elseif($action=='tagedit'){
			if($_POST['name'])foreach($_POST['name'] as $id=>$value){
				$iCMS->db->query("update `#iCMS@__tags` set `name`='$value',`sortid`='".$_POST['sortid'][$id]."',`updatetime`='".time()."' where `id`='$id'");
			}
			tags_cache();
		}
		_Header();
	break;
	case 'delsort':
		$id=(int)$_GET['id'];
		$tSort	= $iCMS->cache('tag.sort','include/syscache',0,true);
		unset($tSort[$id]);
		$iCMS->cache(false,'include/syscache',0,true,false);
		$iCMS->addcache('tag.sort',$tSort,0);
		_Header();
	break;
	case 'sort':
		$rs	= $iCMS->cache('tag.sort','include/syscache',0,true);
		$_count=count($rs);
		include iCMS_admincp_tpl("tag.sort");
	break;
	case 'manage':
		$Admin->MP("menu_tag_manage");
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__tags` order by id DESC");
		page($total,$maxperpage,"个TAG");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__tags` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		$tSort	= $iCMS->cache('tag.sort','include/syscache',0,true);
		include iCMS_admincp_tpl("tag.manage");
	break;
}
?>
