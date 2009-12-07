<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_DB($vars,&$iCMS){
	if(empty($vars['sql'])){
		echo $iCMS->language('SQL:empty');
		return false;
	}else{
		if ( preg_match("/^\\s*(insert|delete|update|replace) /i",$vars['sql']) ) {
			echo $iCMS->language('SQL:IDUR');
			return false;
		}
		if(strstr($vars['sql'], 'members')){
			echo $iCMS->language('SQL:members');
			return false;
		}
		if(strstr($vars['sql'], 'admin')){
			echo $iCMS->language('SQL:admin');
			return false;
		}
		$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
		if($vars['cache']==false){
			$iCMS->config['iscache']=false;
			$rs = '';
		}else{
			$iCMS->config['iscache']=true;
			$cacheName='db/'.md5($vars['sql']);
			$rs=$iCMS->cache($cacheName);
		}
		if(empty($rs)){
			$rs=$iCMS->db->getArray($vars['sql']);
			$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
		}
		return $rs;
	}
}

?>