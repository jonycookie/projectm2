<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
function iCMS_plugins($arguments,&$iCMS){
	add_magic_quotes($arguments);
	strpos($arguments['name'],'..')!==false && exit('Forbidden');
	$fn='iCMS_plugins_'.$arguments['name'];
	if (!function_exists($fn)){
		$plugpath	= iCMS_PLUGINS_PATH.'/'.$arguments['name'];
		$confpath	= $plugpath.'/config.php';
		$funpath	= $plugpath.'/function.php';
		$arguments['tpl'] = $plugpath.'/templates';
		if(file_exists($funpath)){
//			$arguments['config']= $iCMS->cache('config',"plugins/".$arguments['name'],0,true);
			require_once($confpath);
			require_once($funpath);
//			$iCMS->output($arguments['name'],$arguments['tpl']);
		}else{
			$iCMS->trigger_error("function '" . $fn . "' does not exist in iCMS plugins", E_USER_ERROR,__FILE__,__LINE__);
		}
	}
	return $fn($arguments,$iCMS);
//	return call_user_func_array($fn,array($arguments,$iCMS));
}
?>