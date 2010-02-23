<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');

function TplCid($cid = "0",$all=true){
	global $iCMS;
	$cIds=array();
	$cArray=$iCMS->cache('catalog.rootid','include/syscache',0,true);
	if($cArray[$cid]){
		foreach($cArray[$cid] AS $id){
			$cIds[]=$id;
			if($all){
				$_cIds	= TplCid($id);
				$_cIds && $cIds[]=$_cIds;
			}
		}
	}
	unset($cArray);
	return implode(',',$cIds);
}
function GetIDSQL($vars,$field,$not=''){
	$sql = "";
	if(strstr($vars,',')){
		$ids=implode(',',array_map('intval',explode(',',$vars)));
		$sql.=$not=='not'?" AND $field NOT IN ($ids)":" AND $field IN ($ids) ";
	}else{
		$vars=intval($vars);
		$sql.=$not=='not'?" AND $field<>'$vars'  ":" AND $field='$vars' ";
	}
	return $sql;
}
?>