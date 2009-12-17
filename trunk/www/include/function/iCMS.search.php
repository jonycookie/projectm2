<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_search($vars,&$iCMS){
	if($vars['call']=="form"){
		$issort=intval($vars['issort']);
		$iCMS->assign('issort',$issort);
		$iCMS->assign('option',tpl_catalog_option(0,0,1,1));
		echo  $iCMS->iPrint("iSYSTEM","searchform");
	}else{
		$maxperpage =isset($vars['row'])?(int)$vars['row']:"10";
		$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
		$by=$vars['by']=='ASC'?"ASC":"DESC";
		switch ($vars['orderby']) {
			case "new":			$orderSQL=" ORDER BY `id` $by";			break;
			default:			$orderSQL=" ORDER BY `times` $by";
		}
		if($vars['cache']==false||isset($vars['page'])){
			$iCMS->config['iscache']=false;
			$rs = '';
		}else{
			$iCMS->config['iscache']=true;
			$cacheName='search/'.md5($orderSQL);
			$rs=$iCMS->cache($cacheName);
		}
		if(empty($rs)){
			$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__search` {$orderSQL} LIMIT 0,{$maxperpage}");
			$_count=count($rs);
			for ($i=0;$i<$_count;$i++){
				$rs[$i]['url']=($iCMS->config['ishtm']?$iCMS->config['url'].'/':$iCMS->dir).'search.php?stype=content&keyword='.urlencode($rs[$i]['search']);
				$rs[$i]['keyword'].='<a href="'.$rs[$i]['url'].'" class="hotkeyword" target="_self">'.$rs[$i]['search'].'</a> ';
			}
			$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
		}
		return $rs;
	}
}
function tpl_catalog_option($currentid="0",$cid="0",$level = 1,$optgroup='0',$type='0'){
	global $iCMS;
	$cArray=$iCMS->cache('catalog.array','include/syscache',0,true);
	if($cArray[$cid]){
		foreach($cArray[$cid] AS $root=>$cata){
			$t=$level=='1'?"":"©À ";
			$c=$level=='1'?"p3":"p4";
			$selected=($currentid==$cata['id'])?"selected='selected'":"";
			if($type!='page'){
				if($optgroup==1){
					if($cata['attr']=='channel'){
						$_option.="<optgroup label=\"{$cata['name']}\"></optgroup>".tpl_catalog_option($currentid,$cata['id'],$level+1);
					}elseif($cata['attr']=='list'){
						$_option.="<option value='{$cata['id']}' class='$c' $selected>".str_repeat("¡¡", $level-1).$t.$cata['name']."</option>".tpl_catalog_option($currentid,$cata['id'],$level+1);
					}
				}else{
					$cata['attr']!='page' && $_option.="<option value='{$cata['id']}' class='$c' $selected>".str_repeat("¡¡", $level-1).$t.$cata['name']."</option>".tpl_catalog_option($currentid,$cata['id'],$level+1);
				}
			}else{
				($cata['attr']=='channel'|| $cata['attr']=='list') && $_option.=tpl_catalog_option($currentid,$cata['id'],$level+1,0,'page');
				$cata['attr']=='page' && $_option.="<option value='{$cata['id']}' class='$c' $selected>".$cata['name']."</option>".tpl_catalog_option($currentid,$cata['id'],$level+1,0,'page');
			}
		}
	}
	unset($cArray);
	return $_option;
}
?>