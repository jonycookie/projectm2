<?php
define('SCR','list');
require_once('global.php');

$page = $page ? intval($page) : intval(GetGP('page'));
unset($_GET,$_POST,$_REQUEST); //本页只需要两个参数

if (!$cid){
	die('Forbidden');
}

require_once(D_P.'data/cache/cate.php');
Strip_S($catedb);

require_once(R_P.'require/class_cms.php');
require_once(R_P.'require/class_cate.php');
require_once(R_P.'require/class_extend.php');
$extend = new Extend();
$cate = new Cate();
$cms = new Cms();
$cms->listurl = $pageurl;
/*#### SEO关键字 ####*/
$metakeyword = $metadescrip = $sys['title'].",".$catedb[$cid]['cname'];
if($catedb[$cid]['metakeyword']){
	$metakeyword .= ",".$catedb[$cid]['metakeyword'];
}else if($sys['metakeyword']){
	$metakeyword .= ",".$sys['metakeyword'];
}
if($catedb[$cid]['metadescrip']){
	$metadescrip .= ",".$catedb[$cid]['metadescrip'];
}else if($sys['metadescrip']){
	$metadescrip .= ",".$sys['metadescrip'];
}

start($sys['charset']);
require Template();
footer();
?>