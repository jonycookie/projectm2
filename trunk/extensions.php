<?php
/**
 * 插件前台接口
 *
 * This software is the proprietary information of PHPWind.com.
 * $Id: extensions.php 546 2008-04-01 02:19:25Z vcteam $
 * @Copyright (c) 2003-08 PHPWind.com Corporation.
 */
define('IN_EXT',true);
require_once('global.php');
require_once(D_P.'data/cache/ext_config.php');
$E_name = GetGP('E_name');
if(!preg_match("/^[a-zA-Z0-9_]{1,}$/",$E_name) || !$ext_config[$E_name] || !is_dir(R_P."extensions/$E_name") || !file_exists(R_P."extensions/$E_name/index.php")){
	throwError("extensions_error");
	die;
}elseif($ext_config[$E_name]['ifopen']!=1){
	throwError("extensions_closed");
	die;
}
define('E_P',R_P."extensions/$E_name/");
$basename		= "extensions.php?E_name=$E_name";
$ext_tplpath	= "extensions/$E_name/template";
$ext_imgpath	= "extensions/$E_name/images";
$ext_langpath	= "extensions/$E_name/lang";
require_once(E_P.'index.php');

/**
 * 返回一个插件模板文件的编译路径
 *
 * @param string $tplname
 * @return string
 */
function TemplateExt($tplname){
	global $ext_tplpath;
	$tplname = "$ext_tplpath/$tplname";
	require_once(R_P.'require/template.php');
	return Tpl($tplname,true);
}

/**
 * 返回一个插件模板文件的路径
 *
 * @param string $template
 * @return string
 */
function PrintExt($template,$EXT="htm"){
	if (file_exists(E_P."template/$template.$EXT")) {
		return Pcv(E_P."template/$template.$EXT");
	}
	return PrintEot($template,$EXT);
}

function Extmsg($msg,$jumpurl='',$t=3){
	global $basename,$sys;
	ob_end_clean();
	!$basename && $basename=$_SERVER['REQUEST_URI'];
	!$jumpurl && $jumpurl=$basename;
	$ifjump="<META HTTP-EQUIV='Refresh' CONTENT='$t; URL=$jumpurl'>";
	require_once GetLang('cpmsg');
	if(defined('IN_EXT') && file_exists(E_P.'lang/extmsg.php')){
		require_once(E_P.'lang/extmsg.php');
		$lang = array_merge((array)$lang,(array)$extmsg);
	}
	$lang[$msg] && $msg=$lang[$msg];
	if(strtoupper(substr($sys['lang'],0,2))=='GB'){
		$charset = 'GB2312';
	}elseif(strtoupper(substr($sys['lang'],0,3))=='BIG'){
		$charset = 'BIG5';
	}elseif(strtoupper(substr($sys['lang'],0,3))=='UTF'){
		$charset = 'UTF-8';
	}else{
		$charset = 'GB2312';
	}
	header("Content-type: text/html; charset: $charset");
	$outmsg="<div style='font-size:12px;font-family:verdana;line-height:180%;color:#666;border:dashed 1px #ccc;padding:1px;margin:20px;'>";
	$outmsg.="<div style=\"background: #eeedea;padding-left:10px;font-weight:bold;height:25px;\">$lang[prompt]</div>";
	$outmsg.="<div style='padding:20px;font-size:14px;'><img src='images/admin/ok.gif' align='absmiddle' hspace=20 ><span>$msg</span></div>";
	$outmsg.="<div style=\"text-align:center;height:30px;\">$ifjump<a href=\"$jumpurl\">$lang[back]</a></div>";
	$outmsg.="</div>";
	echo $outmsg;
	exit;
}
?>