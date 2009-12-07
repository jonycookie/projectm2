<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_usercp_tpl($p){
	return 'templates/'.$p.'.php';
}
function iCMS_usercp_menu($title=''){
	include iCMS_usercp_tpl('menu');
}
/*
 * @版权信息禁止更改
 * @请见iCMS使用许可协议<http://www.idreamsoft.cn/doc/iCMS.License.html>
 */
function iCMS_user_login(){
	include iCMS_usercp_tpl('login');
}
//function redirect($msg, $url="", $t='3',$more="") {
//	include da_user_page('redirect');
//}
function user_catalog_select($currentid="0",$cid="0",$level = 1,$args=NULL,$mid='0'){
	global $iCMS;
	$catalog	= $iCMS->cache('catalog.array','include/syscache',0,true);
	$args && parse_str($args,$T);
	if(isset($catalog[$cid])){
		foreach($catalog[$cid] AS $root=>$C){
			if($C['mid']==$mid||$C['mid']=="-1"||$mid=='all'){
				if($C['ishidden']=="0"&& $C['issend']=="1"){
					$t=$level=='1'?"":"├ ";
					$c=$level=='1'?"p3":"p4";
					$selected=($currentid==$C['id'])?"selected='selected'":"";
					if(empty($C['url'])){
						if(empty($args)){
							$option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}] </option>";
						}else{
							isset($T['page'])&&$C['attr']=='page' && $option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[p:{$C['dir']}] </option>";
							isset($T['index'])&&$C['attr']=='index' && $option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}] </option>";
							if(isset($T['channel'])&&$C['attr']=='channel'){
								if($T['channel']){
									$option.="<optgroup label=\"{$C['name']}\"></optgroup>";
								}else{
									$option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}] </option>";
								}
							}
							isset($T['list'])&&$C['attr']=='list' && $option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}]</option>";
						}
						$option.=user_catalog_select($currentid,$C['id'],$level+1,$args);
					}
				}else{
					$option.=user_catalog_select($currentid,$C['id'],$level+1,$args);
				}
			}
		}
	}
	return $option;
}
?>