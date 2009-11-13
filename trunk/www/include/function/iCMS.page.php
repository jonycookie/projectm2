<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_page($vars,&$iCMS){
//	$whereSQL=" visible='1'";
	$whereSQL=" 1=1";
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
		$whereSQL.= GetIDSQL($ids,'cid');
    }
    isset($vars['name']) && $whereSQL.= GetIDSQL($vars['name'],'dir');
	isset($vars['where'])&& $whereSQL.=$vars['where'];
	$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
	if($vars['cache']==false||isset($vars['page'])){
		$iCMS->config['iscache']=false;
		$rs = '';
	}else{
		$iCMS->config['iscache']=true;
		$cacheName='page/'.md5($whereSQL);
		$rs=$iCMS->cache($cacheName);
	}
	if(empty($rs)){
	    $rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__page` WHERE {$whereSQL}");
	    $iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
    }
    return $rs;
}
?>