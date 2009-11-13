<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'post':
		if($action=='edit'){
			$disable=explode("\r\n",dhtmlspecialchars($_POST['disable']));
			$filter=explode("\r\n",dhtmlspecialchars($_POST['filter']));
			if(is_array($filter))foreach($filter AS $k=> $val){
				$filterArray[$k]=explode("=",$val);
			}
			$iCMS->cache(false,'include/syscache',0,true,false);
			$iCMS->addcache('word.disable',$disable,0);
			$iCMS->addcache('word.filter',$filterArray,0);
			_Header(__SELF__.'?do=filter');
		}
	break;
	default:
	$Admin->MP("menu_filter");
	$cache	= $iCMS->cache(array('word.filter','word.disable'),'include/syscache',0,true);
	if(is_array($cache['word.filter']))foreach($cache['word.filter'] AS $k=> $val){
		$filterArray[$k]=implode("=",$val);
	}
	include iCMS_admincp_tpl('filter');
}
?>
