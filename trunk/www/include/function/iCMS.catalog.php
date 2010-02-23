<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');

function iCMS_catalog($vars,&$iCMS){
	$row = isset($vars['row'])?(int)$vars['row']:"10";
	$cacheTime = isset($vars['time'])?(int)$vars['time']:"-1";
	$whereSQL=" WHERE ishidden='0'";
	if($vars['att']=='list'){
		$whereSQL.=" and `attr` != 'page'";
	}elseif($vars['att']=='page'){
		$whereSQL.=" and `attr` = 'page'";
	}
	if(isset($vars['mid'])){
		$whereSQL.=" and `mid` = '{$vars['mid']}'";
	}
	switch ($vars['type']) {
		case "top":	
			$vars['id'] && $whereSQL.= GetIDSQL($vars['id'],'id');
			$whereSQL.=" AND rootid='0'";
		break;
		case "subtop":	
			$vars['id'] && $whereSQL.= GetIDSQL($vars['id'],'id');
		break;
		case "sub":	
			$whereSQL.= GetIDSQL(TplCid($vars['id']),'id');
		break;
		case "subone":	
			$whereSQL.= GetIDSQL(TplCid($vars['id'],false),'id');
		break;
		case "allsub":
			$whereSQL.= GetIDSQL(TplCid(),'id');
		break;
		case "self":
			$cParent=$iCMS->cache('catalog.parent','include/syscache',0,true);
			$whereSQL.=GetIDSQL(TplCid($cParent[$vars['id']],false),'id');
		break;
	}
	isset($vars['id!']) && $whereSQL.= GetIDSQL($vars['id!'],'id');

	if($vars['cache']==false){
		$iCMS->config['iscache']=false;
		$rs = '';
	}else{
		$iCMS->config['iscache']=true;
		$cacheName='catalog/'.md5($whereSQL);
		$rs=$iCMS->cache($cacheName);
	}
	if(empty($rs)){
		$rs=$iCMS->db->getArray("SELECT id FROM `#iCMS@__catalog`{$whereSQL} ORDER BY `order`,`id` ASC LIMIT $row");
		$catalog=$iCMS->cache('catalog.cache','include/syscache',0,true);
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$rs[$i]=$catalog[$rs[$i]['id']];
			$rs[$i]['url']=$rs[$i]['attr']=='page'?$iCMS->iurl('page',array('link'=>$rs[$i]['dir'],'url'=>$rs[$i]['url'],'domain'=>$rs[$i]['domain'])):$iCMS->iurl('list',array('id'=>$rs[$i]['id'],'link'=>$rs[$i]['dir'],'url'=>$rs[$i]['url'],'domain'=>$rs[$i]['domain']));
			$rs[$i]['link']="<a href='{$rs[$i]['url']}'>{$rs[$i]['name']}</a>";
		}
		$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
	}
	return $rs;
}
?>