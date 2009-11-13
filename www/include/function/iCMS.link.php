<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_link($vars,&$iCMS){
	$limit =isset($vars['row'])?(int)$vars['row']:"100";
	$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
	switch($vars['type']){
		case "text":$sql=" WHERE `logo`='' ";break;
		case "image":$sql=" WHERE `logo`!='' ";break;
		default:$sql='';
	}
	if($vars['cache']==false){
		$iCMS->config['iscache']=false;
		$rs = '';
	}else{
		$iCMS->config['iscache']=true;
		$cacheName='links/'.md5($sql);
		$rs=$iCMS->cache($cacheName);
	}
	if(empty($rs)){
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__links`{$sql} ORDER BY orderid ASC,id DESC LIMIT 0 , $limit");
		$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
	}
	return $rs;
}

?>