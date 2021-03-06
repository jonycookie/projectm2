<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_list($vars,&$iCMS){
    $whereSQL	= " visible='1'";
	$cache		= $iCMS->cache(array('catalog.cache','catalog.hidden'),'include/syscache',0,true);
	$cache['catalog.hidden']&&  $whereSQL.=GetIDSQL($cache['catalog.hidden'],'cid','not');
    $maxperpage=isset($vars['row'])?(int)$vars['row']:10;
	$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
    isset($vars['userid'])	&& 	$whereSQL.=" AND `userid`='{$vars['userid']}'";
    isset($vars['author'])	&& 	$whereSQL.=" AND `author`='{$vars['author']}'";
    isset($vars['top'])		&& 	$whereSQL.=" AND `top`='{$vars['top']}'";
    $vars['call']=='user'	&& 	$whereSQL.=" AND `postype`='0'";
    $vars['call']=='admin'	&& 	$whereSQL.=" AND `postype`='1'";
	$catalog = $cache['catalog.cache'];
    if(isset($vars['sortid!'])){
    	$_NCID=TplCid($vars['sortid!']);
     	$_NCID && $NcIds[]=$_NCID;
	   	$vars['sub']=='all' && $NcIds[]=$vars['sortid!'];
    	$ids=($NcIds && $vars['sub']=='all')?implode(',',$NcIds):$vars['sortid!'];
		$whereSQL.= GetIDSQL($ids,'cid','not');
    }
    if(isset($vars['sortid'])){
    	$_CID = TplCid($vars['sortid']);
    	$_CID && $cIds[]=$_CID;
    	$vars['sub']=='all'&& $cIds[]=$vars['sortid'];
    	$ids=($cIds && $vars['sub']=='all')?implode(',',$cIds):$vars['sortid'];
		$whereSQL.= GetIDSQL($ids,'( cid')." OR `vlink` REGEXP '[[:<:]]".preg_quote(str_replace(',','|', $ids), '/')."[[:>:]]')";
    }

    if(isset($vars['type'])){
    	if(strpos($vars['type'],',')){
    		$vars['type'] = str_replace(',','|', $vars['type']);
     		$whereSQL.= " AND `type` REGEXP '[[:<:]]".preg_quote($vars['type'], '/')."[[:>:]]'";
   		}elseif(strpos($vars['type'],'&')){
   			$typeArray=explode('&',$vars['type']);
   			foreach($typeArray as $_type){
     			$whereSQL.= " AND `type` REGEXP '[[:<:]]".preg_quote($_type, '/')."[[:>:]]'";
   			}
   		}else{
     		$whereSQL.= " AND `type` REGEXP '[[:<:]]".preg_quote($vars['type'], '/')."[[:>:]]'";
   		}
    }
	if($vars['loop']=="rel" && empty($vars['id']))return false;
	$vars['id'] && $whereSQL.= GetIDSQL($vars['id'],'id');
	$vars['id!'] && $whereSQL.= GetIDSQL($vars['id!'],'id','not');
	$by=$vars['by']=="ASC"?"ASC":"DESC";
    if($vars['keywords']){
    	if(strpos($vars['keywords'],',')!==false){
    		$kw=explode(',',$vars['keywords']);
    		foreach($kw AS $v){
    			$keywords.=addslashes($v)."|";
    		}
    		$keywords=substr($keywords,0,-1);
	    	$whereSQL.= "  And CONCAT(title,keywords,description) REGEXP '$keywords' ";
    	}else{
    		$vars['keywords']=str_replace(array('%','_'),array('\%','\_'),$vars['keywords']);
	    	$whereSQL.= " AND CONCAT(title,keywords,description) like '%".addslashes($vars['keywords'])."%'";
    	}
    }
    $vars['pic'] && $whereSQL.= " AND `pic`<>''";
    if($vars['action']=='search'||$vars['action']=='tag'){
    	$whereSQL.=$iCMS->actionSQL;
    }
	switch ($vars['orderby']) {
		case "digg":	$orderSQL=" ORDER BY `digg` $by";		break;
		case "hot":		$orderSQL=" ORDER BY `hits` $by";		break;
		case "id":		$orderSQL=" ORDER BY `id` $by";			break;
		case "comments":$orderSQL=" ORDER BY `comments` $by";	break;
		case "pubdate":	$orderSQL=" ORDER BY `pubdate` $by";	break;
		case "disorder":$orderSQL=" ORDER BY `order`,`id` $by";	break;
		case "rand":	$orderSQL=" ORDER BY rand() $by";		break;
		default:		$orderSQL=" ORDER BY `top`,`order`,`id` DESC";
	}
	isset($vars['date']) && list($iCMS->date['y'],$iCMS->date['m'],$iCMS->date['d'])=explode('-',$vars['date']);
	if($iCMS->date){
		$day	= empty($iCMS->date['d'])?'01':$iCMS->date['d'];
		$start	= strtotime($iCMS->date['y'].$iCMS->date['m'].$day);
		$end	= empty($iCMS->date['d'])?$start+84600*$iCMS->date['total']:$start+84600;
		$whereSQL.=" AND `pubdate`<='{$end}' AND `pubdate`>='{$start}'";
	}else{
		isset($vars['startdate'])	&& $whereSQL.=" AND `pubdate`>='".strtotime($vars['startdate'])."'";
		isset($vars['enddate']) 	&& $whereSQL.=" AND `pubdate`<='".strtotime($vars['enddate'])."'";
	}
	isset($vars['where'])		&& $whereSQL.=$vars['where'];
	$offset	= 0;
	if($vars['page']){
		$total	= $iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__article` WHERE {$whereSQL} {$orderSQL}");
		$pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
		$pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
		$offset	= $iCMS->multi(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>$iCMS->language('page:list'),'url'=>$iCMS->url,'nowindex'=>$GLOBALS['page'],'pagenav'=>$pagenav,'pnstyle'=>$pnstyle));
//		$GLOBALS['cpn'] && $iCMS->_vars['pagenav'].='<span><a class="page_more" href="more.php?cid='.$ids.'" target="_self">'.$iCMS->language('page:more').'</a></span>';
		//$iCMS->addto($pagenav,"----------------");
	}
	if($vars['cache']==false||isset($vars['page'])){
		$iCMS->config['iscache']=false;
		$rs = '';
	}else{
		$iCMS->config['iscache']=true;
		$cacheName='list/'.md5($whereSQL.$orderSQL);
		$rs=$iCMS->cache($cacheName);
	}

	if(empty($rs)){
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__article` WHERE {$whereSQL} {$orderSQL} LIMIT {$offset} , {$maxperpage}");
//echo $iCMS->db->last_query;
//$iCMS->db->last_query='explain '.$iCMS->db->last_query;
//$explain=$iCMS->db->getRow($iCMS->db->last_query);
//var_dump($explain);
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			//$rs[$i]['pic'] && $rs[$i]['pic']=$iCMS->dir.$rs[$i]['pic'];
			$C=$catalog[$rs[$i]['cid']];
			$rs[$i]['sort']['name']=$C['name'];
			$rs[$i]['sort']['url']=$C['attr']=='page'?$iCMS->iurl('page',array('link'=>$C['dir'],'url'=>$C['url'],'domain'=>$C['domain'])):$iCMS->iurl('list',array('id'=>$rs[$i]['cid'],'link'=>$C['dir'],'url'=>$C['url'],'domain'=>$C['domain']));
//			$rs[$i]['sort']['url']=$iCMS->iurl('list',array('id'=>$rs[$i]['cid'],'link'=>$C['dir'],'url'=>$C['url'],'domain'=>$C['domain']));
			$rs[$i]['sort']['link']="<a href='{$rs[$i]['sort']['url']}'>{$rs[$i]['sort']['name']}</a>";
			$rs[$i]['url']=$iCMS->iurl('show',array('id'=>$rs[$i]['id'],'cid'=>$rs[$i]['cid'],'link'=>$rs[$i]['customlink'],'url'=>$rs[$i]['url'],'dir'=>$iCMS->cdir($C),'domain'=>$C['domain'],'pubdate'=>$rs[$i]['pubdate']));
			$rs[$i]['link']="<a href='{$rs[$i]['url']}'>{$rs[$i]['title']}</a>";
		    if($rs[$i]['tags'] && isset($vars['tags'])){
		    	$tagarray=explode(',',$rs[$i]['tags']);
		    	if(count($tagarray)>1){
		    		foreach($tagarray AS $tag){
		    			$iCMS->chkTagVisible($tag)&&$tags.='<a href="'.$iCMS->config['url'].'/tag.php?t='.rawurlencode($tag).'" class="tag" target="_self">'.$tag.'</a> ';
		    		}
		    	}else{
		    		$iCMS->chkTagVisible($tagarray[0])&&$tags='<a href="'.$iCMS->config['url'].'/tag.php?t='.rawurlencode($tagarray[0]).'" class="tag" target="_self">'.$tagarray[0].'</a>';
		    	}
		    	$rs[$i]['tags']=$tags;
		    }
		}
		$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
	}
	return $rs;
}
?>