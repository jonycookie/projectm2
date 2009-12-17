<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
include iPATH.'include/catalog.class.php';
	$catalog =new catalog();
	$cid	= (int)$_GET['cid'];
	$sql	=" where ";
	$sql.=$_GET['type']=='draft'?"`visible` ='0'":"`visible` ='1'";
	$orderby=$_GET['orderby']?$_GET['orderby']:"id DESC";
	$maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:10;
	$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__article` {$sql} AND `postype`='0' AND `userid`='$member->uId' order by {$orderby}");
	page($total,$maxperpage,"篇文章");
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__article` {$sql} AND `postype`='0' AND `userid`='$member->uId' order by {$orderby} LIMIT {$firstcount} , {$maxperpage}");
	$_count=count($rs);
include iCMS_usercp_tpl("home");
?>