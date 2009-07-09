<?php
define('SCR','view');

require_once('global.php');
require_once(D_P.'data/cache/face.php');
$page	= $page?$page:intval(GetGP('page'));
unset($_GET,$_POST,$_REQUEST); //本页只需要两个参数
!$tid && throwError('notid');

require_once(R_P.'require/class_cms.php');
$cms	=	new Cms();
$cont	=	new Cont();
$mid	=	$catedb[$cid]['mid'];
$view	=	array();

$cont->url = $contentUrl;
$view	=	$cont->getone($cid,$tid,$page);
if(($mid==-1 || $mid==-2) && $catedb[$cid]['htmlpub'] && $view['ifpub']!=1 && !defined('IN_ADMIN')) throwError('no right');

require_once(R_P.'require/class_cate.php');
require_once(R_P.'require/class_extend.php');
$cate = new Cate();
$extend = new Extend();

if(!function_exists('adminmsg')){ //说明不是发布动作而是动态浏览
	if(!GetCookie("$tid"))	{
		$db->update("UPDATE cms_contentindex SET hits=hits+1 WHERE tid='$tid' AND cid='$cid'");
		$val = rand(1,10);
		$ck_time = $timestamp + 3600;
		Cookie("$tid","$val",$ck_time); //防止反复刷新来提高点击数
	}
}

/*#### SEO关键字 ####*/
$tagkeywords = $metakeyword = $metadescrip = '';
if($view['tags']){
	$tagkeyword = explode("&nbsp;&nbsp;",$view['tags']);
	foreach($tagkeyword as $val) {
		$tagbegin = strpos($val,'>')+1;
		$tagend	= strpos($val,'</a>');
		$tagend = $tagend-$tagbegin;
		$tagkeywords .= substr($val,$tagbegin,$tagend).",";
	}
}

if($tagkeywords){
	$metakeyword = $tagkeywords;
	$metadescrip = $tagkeywords;
}
if($catedb[$cid]['metakeyword']){
	$metakeyword = $metakeyword ? $metakeyword.",".$catedb[$cid]['metakeyword']:$catedb[$cid]['metakeyword'];
}else if($sys['metakeyword']){
	$metakeyword = $metakeyword ? $metakeyword.",".$sys['metakeyword']:$sys['metakeyword'];
}
if($catedb[$cid]['metadescrip']){
	$metadescrip = $metadescrip ? $view['title'].",".$metadescrip.",".$catedb[$cid]['metadescrip']:$view['title'].",".$catedb[$cid]['metadescrip'];
}else if($sys['metadescrip']){
	$metadescrip = $metadescrip ? $view['title'].",".$metadescrip.",".$sys['metadescrip']:$view['title'].",".$sys['metadescrip'];
}
$metakeyword = $metakeyword ? $metakeyword.",".$sys['title']:$sys['title'];
$metadescrip = $metadescrip ? $metadescrip.",".$sys['title']:$sys['title'];

start($sys['charset']);
require Template($view['template']);
footer();
?>