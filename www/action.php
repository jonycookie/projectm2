<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
$do		= $_GET['do'];
$action	= $_GET['action'];
$id		= intval($_GET['id']);
$mid	= intval($_GET['mid']);
if(empty($mid)){
	$__TABLE__='article';
}else{
	$__MODEL__	= $iCMS->cache('model.id','include/syscache',0,true);
	$model		= $__MODEL__[$mid];
	$__TABLE__	= $model['table'].'_content';
}
switch ($do) {
	case 'digg':
		if($action=='do'){
			$at=(time()-get_cookie('digg_'.$id)>$_iGLOBAL['cookie']['time'])?true:false;
			if($at){
				set_cookie('digg_'.$id,time());
				if($id && $iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `digg` = digg+1  WHERE id='$id'")){
					$json="{state:'1'}";
				}
			}else{
				$json="{state:'0',text:'".$iCMS->language('digged')."' }";
			}
			jsonp($json,$_GET['callback']);
		}
		if($action=='show'){
			$digg=$iCMS->db->getValue("SELECT digg FROM `#iCMS@__$__TABLE__` WHERE id='$id' LIMIT 1");
			echo "document.write('{$digg}');\r\n";
		}
	break;
	case 'hits':
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET hits=hits+1 WHERE `id` ='$id' LIMIT 1");
		if($action=='show'){
			$hits=$iCMS->db->getValue("SELECT hits FROM `#iCMS@__$__TABLE__` WHERE id='$id'");
			echo "document.write('{$hits}');\r\n";
		}
	break;
	case 'comment':
		if(in_array($action,array('up','against'))){
			UA($action,(int)$_GET['cid']);
		}
		if($action=='show'){
			if($iCMS->config['iscomment']){
				$comments=$iCMS->db->getValue("SELECT comments FROM `#iCMS@__$__TABLE__` WHERE id='$id'  LIMIT 1");
				echo "document.write('{$comments}');\r\n";
			}
		}
	break;
}
function UA($act,$cid){
	global $iCMS,$_iGLOBAL;
	$cookietime=$_iGLOBAL['cookie']['time'];
	$ajax=intval($_GET['ajax']);
	$ct=(time()-get_cookie($cid.'_up')>$cookietime && time()-get_cookie($cid.'_against')>$cookietime)?true:false;
	if($ct){
		set_cookie($cid.'_'.$act,time(),$cookietime);
		if($cid && $iCMS->db->query("UPDATE `#iCMS@__comment` SET `{$act}` = {$act}+1  WHERE `id` ='$cid'")){
			$ajax?jsonp("{state:'1'}",$_GET['callback']):_Header($iCMS->dir."comment.php?aid=".$id);
		}
	}else{
		$ajax?jsonp("{state:'0',text:'".$iCMS->language('digged')."' }",$_GET['callback']):alert($iCMS->language('digged'));
	}
}
function jsonp($json,$callback){
	echo $callback.'('.$json.')';exit;
}
?>