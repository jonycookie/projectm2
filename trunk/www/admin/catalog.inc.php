<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
include_once(iPATH.'include/catalog.class.php');
switch ($operation) {
case 'add':
	$Admin->MP(array("menu_index_catalog_add","menu_catalog_add"));
	$catalog =new catalog();
	if($_GET['cid']){
		$Admin->CP(intval($_GET['cid']),'Permission_Denied',__SELF__.'?do=catalog');
		$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__catalog` WHERE `id`='".intval($_GET['cid'])."'",ARRAY_A);
		$rootid=$rs['rootid'];
	}else{
		$rootid=intval($_GET['rid']);
		$rootid && $Admin->CP($rootid,'Permission_Denied',__SELF__.'?do=catalog');
	}
	if(empty($rs)){
		$rs=array();
		$rs['attr']		= 'list';
		$rs['ishidden']	= '0';
		$rs['order']	= '0';
		$rs['isexamine']= '1';
		$rs['issend']	= '1';
	}
	include iCMS_admincp_tpl("catalog.add");
break;
case 'post':
	if($action=='save'){
	    $rootid		=intval($_POST['rootid']);
	    $mid		=intval($_POST['mid']);
	    $name		=$_POST['name'];
	    $ishidden	=intval($_POST['ishidden']);
	    $issend		=intval($_POST['issend']);
	    $isexamine	=intval($_POST['isexamine']);
	    $order		=intval($_POST['order']);
	    $domain		=$_POST['domain'];
	    $url		=$_POST['url'];
	    $password	=$_POST['password'];
	    $icon		=$_POST['icon'];
	    $dir		=$_POST['dir'];
	    $keywords	=$_POST['keywords'];
	    $description=$_POST['description'];
	    $attr		=$_POST['attr'];
	    $tpl		=$_POST['tpl'];
	    $cid		=intval($_POST['cid']);
	    $pinyin		=intval($_POST['pinyin']);
	    ($cid && $cid==$rootid) && alert("不能以自身做为上级栏目");
	    empty($name) && alert("栏目名称不能为空!");
	    switch($attr){
	    	case "list":
	    		$tpl['list']?$tpl_list=$tpl['list']:alert("请选择栏目模板");
	    		$tpl['content']?$tpl_contents=$tpl['content']:alert("请选择内容模板");
	    	break;
	    	case "channel":
	    		$tpl['channel']?$tpl_index=$tpl['channel']:alert("请选择频道封面模板");
	    	break;
	    	case "page":
	    		$tpl['page']?$tpl_index=$tpl['page']:alert("请选择栏目模板");
	    	break;
	    	default:
	    	alert('怪事!你怎么跑这里来了!!\n你想干嘛??');
	    }
	    ($pinyin==1||empty($dir))&&$dir=pinyin($name);
	    $attr=="page"&&$issend='0';
	    if(empty($cid)){
	    	$iCMS->db->getValue("SELECT `dir` FROM `#iCMS@__catalog` where `dir` ='$dir'") && alert('该栏目别名/目录已经存在!请另选一个');
	    	$iCMS->db->query("INSERT INTO `#iCMS@__catalog` (`rootid`,`mid`,`order`,`name`,`password`,`keywords`,`description`,`dir`,`domain`,`url`,`icon`,`tpl_index`,`tpl_list`,`tpl_contents`,`attr`,`isexamine`,`ishidden`,`issend`) 
	    		VALUES ('$rootid','$mid', '$order', '$name','$password','$keywords', '$description', '$dir','$domain', '$url','$icon','$tpl_index', '$tpl_list', '$tpl_contents', '$attr','$isexamine','$ishidden','$issend')");
			$catalog =new catalog();
		   	$catalog->cache();
	    	redirect("栏目添加完成!",__SELF__.'?do=catalog');
	    }else{
	    	$Admin->CP($cid,'Permission_Denied',__SELF__.'?do=catalog');
	    	$rootid!=$catalog->catalog[$cid]['rootid'] && $Admin->CP($rootid,'Permission_Denied',__SELF__.'?do=catalog');
	    	$iCMS->db->getValue("SELECT `dir` FROM `#iCMS@__catalog` where `dir` ='$dir' AND `id` !='$cid'") && alert('该栏目别名/目录已经存在!请另选一个');
	    	$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `rootid` = '$rootid',`mid` = '$mid',`order` = '$order',`name` = '$name',`password`='$password',`keywords` = '$keywords',`description` = '$description',`dir` = '$dir',`url` = '$url',`domain` = '$domain',`icon`='$icon',`tpl_index` = '$tpl_index',`tpl_list` = '$tpl_list',`tpl_contents` = '$tpl_contents',`attr` = '$attr',`isexamine`='$isexamine',`ishidden`='$ishidden',`issend`='$issend' WHERE `id` ='$cid' ");
		   	$catalog =new catalog();
		   	$catalog->cache();
	    	redirect("栏目编辑完成!",__SELF__.'?do=catalog');
	    }
	}
	if($action=='edit'){
		foreach($_POST['order'] AS $cid=>$order){
				$Admin->CP($cid) && $iCMS->db->query("UPDATE `#iCMS@__catalog` SET `name` = '".$_POST['name'][$cid]."',`order` = '".intval($order)."' WHERE `id` ='".intval($cid)."' LIMIT 1");
		}
		$catalog =new catalog();
		$catalog->cache();
		_Header(__SELF__."?do=catalog");
	}
break;
case 'del':
	$id=(int)$_GET['id'];
	$Admin->CP($id,'Permission_Denied',__SELF__.'?do=catalog');
	$catalog =new catalog();
	if($id){
		if(empty($catalog->array[$id])){
			$iCMS->db->query("DELETE FROM `#iCMS@__catalog` WHERE `id` = '$id'");
			$art=$iCMS->db->getArray("SELECT id FROM `#iCMS@__article` WHERE `cid` = '$id'");
			if($art){
				foreach($art as $a){
					delArticle($a['id']);
				}
			}
			$catalog =new catalog();
		   	$catalog->cache();
			alert("删除成功!",'url:'.__SELF__.'?do=catalog');
		}else{
			alert("请先删除本栏目下的子栏目",'url:'.__SELF__.'?do=catalog');
		}
	}
break;
case 'move':
	alert("暂无此功能!",'url:'.__SELF__.'?do=catalog');
break;
default:
	$Admin->MP("menu_catalog_manage");
	$catalog =new catalog();
	$operation && set_cookie('selectopt',$operation);
	$operation	= get_cookie('selectopt');
	empty($operation) && $operation='fold';
	include iCMS_admincp_tpl("catalog.manage");
}
?>
