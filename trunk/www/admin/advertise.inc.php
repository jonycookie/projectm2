<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'add':
		$id=intval($_GET['advid']);
	    $rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__advertise` where id='$id'",ARRAY_A);
	    $rs['starttime']=get_date($rs['starttime'],'Y-m-d');;
	    $rs['endtime']=empty($rs['endtime'])?'':get_date($rs['endtime'],'Y-m-d');
		$adv=stripslashes_deep(unserialize($rs['code']));
		empty($adv) && $adv=array();
		empty($rs['style']) &&$rs['style']='code';
		include iCMS_admincp_tpl("advertise.add");
	break;
	case 'post':
		if($action=='add'){
	//		print_r($_POST);
			$id		=intval($_POST['id']);
			$state	=intval($_POST['state']);
			$varname=$_POST['varname'];
			$title	=dhtmlspecialchars($_POST['title']);
			$style	=$_POST['style'];
			$starttime=empty($_POST['starttime'])?0:_strtotime($_POST['starttime']);
			$endtime=empty($_POST['endtime'])?0:_strtotime($_POST['endtime']);
			$code=addslashes(serialize($_POST['adv']));
	    	!$varname && alert("广告标识符不能为空");
			if($id){
				$iCMS->db->query("UPDATE `#iCMS@__advertise` SET `varname` = '$varname',`title` = '$title',`style`='$style',`starttime` = '$starttime',`endtime` = '$endtime',`code` = '$code',`status` = '$state' WHERE `id` ='$id'");
			}else{
				$iCMS->db->query("INSERT INTO `#iCMS@__advertise`(`varname` , `title` ,`style`, `starttime` , `endtime` , `code` , `status` ) VALUES ('$varname','$title','$style','$starttime', '$endtime', '$code', '$state')");
			}
			CreateAdvJs($id);
			_Header(__SELF__."?do=advertise");
		}
		if($action=='del'){
			foreach($_POST['delete'] as $id){
				$iCMS->db->query("delete from `#iCMS@__advertise` where `id`='$id'");
				CreateAdvJs($id);
			}
			_Header(__SELF__.'?do=advertise');
		}
		if($action=='js'){
			foreach($_POST['id'] as $id){
				CreateAdvJs($id);
			}
			_Header(__SELF__.'?do=advertise');
		}
	break;
	case 'status':
		$id=intval($_GET['id']);
		$act=intval($_GET['act']);
		$iCMS->db->query("UPDATE `#iCMS@__advertise` SET `status` = '$act' WHERE `id` ='$id'");
		CreateAdvJs($id);
		_Header(__SELF__.'?do=advertise');
	break;
	default:
		$Admin->MP(array("menu_index_advertise","menu_advertise"));
		$maxperpage =30;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__advertise` order by id DESC");
		page($total,$maxperpage,"个广告");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__advertise` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include iCMS_admincp_tpl("advertise");
}
function getadvhtml($style,$c){
	switch ($style) {
	case 'code':
		$html=$c['code']['html'];
		break;
	case "image":
		$c['image']['width'] && $width=" width=\"{$c['image']['width']}\"";
		$c['image']['height'] && $height=" height=\"{$c['image']['height']}\"";
		$html="<a href=\"{$c['image']['link']}\" target=\"_blank\" title=\"{$c['image']['alt']}\"><img src=\"{$c['image']['url']}\" alt=\"{$c['image']['alt']}\"{$width}{$height} alt=\"{$c['image']['alt']}\" border=\"0\"></a>";
		break;
	case "flash":
		$c['flash']['width'] && $width=" width=\"{$c['flash']['width']}\"";
		$c['flash']['height'] && $height=" height=\"{$c['flash']['height']}\"";
		$html="<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0\" {$width}{$height}><param name=\"movie\" value=\"{$c['flash']['url']}\" /><param name=\"quality\" value=\"high\" /><embed src=\"{$c['flash']['url']}\" quality=\"high\" pluginspage=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\" type=\"application/x-shockwave-flash\"{$width}{$height}></embed></object>";
		break;
	case "text":
		$c['text']['size'] &&$style=" style=\"font-size:{$c['text']['size']};\"";
		$html="<a href=\"{$c['text']['link']}\" target=\"_blank\" title=\"{$c['text']['title']}\"{$style}>{$c['text']['title']}</a>";
		break;
	}
	return addslashes($html);
}
function CreateAdvJs($id){
	global $iCMS;
	$rs	  = $iCMS->db->getRow("SELECT * FROM `#iCMS@__advertise` WHERE `id`='$id'");
	$file = "cache/{$rs->style}-id-{$rs->id}.js";
	$rs->code = stripslashes_deep(unserialize($rs->code));
	$html = "/*\n{$rs->varname}\n标签:<!--{iCMS:advertise name=\"{$rs->varname}\"}-->\n*/\n";
	if($rs->status){
		$html.="var timestamp = Date.parse(new Date());\n";
		$html.="var startime = Date.parse(new Date(\"".get_date($rs->starttime,'Y/m/d')."\"));\n";
		$rs->endtime && $html.="var endtime = Date.parse(new Date(\"".get_date($rs->endtime,'Y/m/d')."\"));\n";
		$html.="if(timestamp>=startime";
		$rs->endtime && $html.="||timestamp<endtime";
		$html.="){\n";
		$html.= document(getadvhtml($rs->style,$rs->code));
		$html.="}";
	}
	writefile(iPATH.$file,$html);
}
function document($HTML){
	$HTML = str_replace("\r\n", "\n", $HTML);
	foreach(explode("\n",$HTML) AS $val){
		$JS.="document.writeln(\"".$val."\");\n";
	}
	return $JS;
}
?>
