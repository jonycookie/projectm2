<?php
defined('IN_EXT') or die('Forbidden');
require_once(D_P.'data/cache/cate.php');
include_once(D_P.'data/cache/ext_contribute.php');
$cids = explode(',',$contribute['cids']);
InitGP(array('action','step','cid','ck'));
if(!$action) {
	$contricate = array();
	foreach($cids as $cid) {
		$contricate[$cid] = $catedb[$cid];
		$contricate[$cid]['contributeurl'] = $basename."&action=refer&cid=$cid";
	}
	$contribute['cids']	= $contricate;
}elseif($action=='refer') {
	$cid = (int)$cid;
	require_once(R_P.'require/class_content.php');
	$mid = $catedb[$cid]['mid'];
	if(!$cid || !$mid) {
		throwError('ext_cidnotexist');
	}
	if(!in_array($cid,$cids)) {
		throwError('ext_cidnotsustain');
	}
	$content	= new Content($mid);
	$fieldids	= array();
	foreach($content->fields as $key=>$val) {
		if(!$val['ifcontribute']) {
			unset($content->fields[$key]);
			continue;
		}
		$fieldids[$key] = $val['fieldid'];
	}
	if(!$step) {
		$inputArea = $content->inputArea('custom');
	}else {
		require_once(E_P.'include/class_contribute.php');
		if(empty($_COOKIE) || $timestamp-GetCookie('contribute')<30) throwError('ext_wait');
		if($contribute['ckcontribute']){
			$ck = strtolower($ck);
			GdConfirm($ck);
		}
		empty($_POST['title']) && throwError('ext_notitle');
		$contens = array();
		foreach($fieldids as $key=>$val) {
			if(in_array($content->fields[$key]['inputtype'],array('input','edit','basic')) && strlen($_POST[$val])<3) {
				Extmsg('ext_toosmall');
			}
			$contens[$val] = convert($_POST[$val],$content->fields[$key]['inputtype']);
		}
		$contens['postdate'] = $timestamp;
		if($contribute['checkcontribute']){
			$content->InsertData($contens,$cid,$mid,1);
		}else{
			$content->InsertData($contens,$cid,$mid,2);
		}
		
		Cookie('contribute',$timestamp);
		Extmsg('ext_successful');
	}
}
require_once(R_P.'require/class_cate.php');
require_once(R_P.'require/class_cms.php');
require_once(R_P.'require/class_extend.php');
$cms	= new Cms();
$cate	= new Cate();
$extend = new Extend();
$metakeyword = $metadescrip = $sys['title'].','.$ext_config[$E_name]['name'];
require TemplateExt('index');

function Char_ar($msg) {
	if(is_array($msg)) {
		foreach($msg as $key=>$val) {
			$msg[$key] = Char_ar($val);
		}
	}else {
		return Char_cv($msg);
	}
}

function HTMLtoUBB($message) {
	$searcharray =array(
		"<u>","</u>",
		"<b>","</b>",
		"<i>","</i>",
		"<em>","</em>",
		"<strike>","</strike>",
		"<strong>","</strong>",
		"<li>","</li>",
		"<ol>","</ol>",
		"<ul>","</ul>",
		"<p>","</p>",
		"<br />"
	);
	$replacearray = array(
		"[u]","[/u]",
		"[b]","[/b]",
		"[i]","[/i]",
		"[em]","[/em]",
		"[strike]","[/strike]",
		"[strong]","[/strong]",
		"[li]","[/li]",
		"[ol]","[/ol]",
		"[ul]","[/ul]",
		"[p]","[/p]",
		"[br]"
	);
	$message = str_replace($searcharray,$replacearray,$message);

	$searcharray	= array("/<a href=\\\\\"([^\[]*)\\\\\">(.+?)<\/a>/is");
	$replacearray	= array("[url=\\1]\\2[/url]");
	$message		= preg_replace($searcharray,$replacearray,$message);
	return $message;
}
function UBBtoHTML($message) {
	$searcharray = array(
		"[u]","[/u]",
		"[b]","[/b]",
		"[i]","[/i]",
		"[em]","[/em]",
		"[strike]","[/strike]",
		"[strong]","[/strong]",
		"[li]","[/li]",
		"[ol]","[/ol]",
		"[ul]","[/ul]",
		"[p]","[/p]",
		"[br]"
	);
	$replacearray =array(
		"<u>","</u>",
		"<b>","</b>",
		"<i>","</i>",
		"<em>","</em>",
		"<strike>","</strike>",
		"<strong>","</strong>",
		"<li>","</li>",
		"<ol>","</ol>",
		"<ul>","</ul>",
		"<p>","</p>",
		"<br />"
	);
	$message = str_replace($searcharray,$replacearray,$message);
	$searcharray	= array("/\[url=([^\[]*)\](.+?)\[\/url\]/is");
	$replacearray	= array("<a href=\"\\1\">\\2</a>");
	$message		= preg_replace($searcharray,$replacearray,$message);
	return $message;
}

function convert($message,$type) {
	if(in_array($type,array('edit','basic'))) {
		$message = HTMLtoUBB($message);
		$message = Char_ar($message);
		$message = UBBtoHTML($message);
	}else {
		$message = Char_ar($message);
	}
	return $message;
}
?>