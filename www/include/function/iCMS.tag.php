<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_tag($vars,&$iCMS){
	$whereSQL=" visible='1'";
	isset($vars['sortid']) && $whereSQL.=" AND sortid='".(int)$vars['sortid']."'";
	$maxperpage =isset($vars['row'])?(int)$vars['row']:"10";
	$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
	$by=$vars['by']=='ASC'?"ASC":"DESC";
	switch ($vars['orderby']) {
		case "hot":			$orderSQL=" ORDER BY `count` $by";		break;
		case "new":			$orderSQL=" ORDER BY `id` $by";			break;
		case "addtime":		$orderSQL=" ORDER BY `addtime` $by";	break;
		case "updatetime":	$orderSQL=" ORDER BY `updatetime` $by";	break;
		case "rand":		$orderSQL=" ORDER BY rand() $by";		break;
		default:			$orderSQL=" ORDER BY `id` $by";
	}
	$offset	= 0;
	if($vars['page']){
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__tags` WHERE {$whereSQL} {$orderSQL}");
		$iCMS->assign("total",$total);
		$pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
		$pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
		$offset	= $iCMS->multi(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>$iCMS->language('page:tag'),'url'=>$iCMS->url,'nowindex'=>$GLOBALS['page'],'pagenav'=>$pagenav,'pnstyle'=>$pnstyle));
	}
	if($vars['cache']==false||isset($vars['page'])){
		$iCMS->config['iscache']=false;
		$rs = '';
	}else{
		$iCMS->config['iscache']=true;
		$cacheName='tags/'.md5($whereSQL.$orderSQL);
		$rs=$iCMS->cache($cacheName);
	}
	if(empty($rs)){
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__tags` WHERE {$whereSQL} {$orderSQL} LIMIT {$offset},{$maxperpage}");
//$iCMS->db->last_query='explain '.$iCMS->db->last_query;
//$explain=$iCMS->db->getRow($iCMS->db->last_query);
//var_dump($explain);
		$_count=count($rs);
		include_once iPATH.'include/pinyin.php';
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['url']=$iCMS->iurl('tag',array('id'=>$rs[$i]['id'],'link'=>pinyin($rs[$i]['name'],$iCMS->config['CLsplit']),'name'=>$rs[$i]['name']));
			$rs[$i]['tags'].='<a href="'.$rs[$i]['url'].'" class="tag" target="_self">'.$rs[$i]['name'].'</a> ';
		}
		$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
	}
	return $rs;
}

?>