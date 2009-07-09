<?php
define('SCR','index');
unset($_GET,$_POST,$_REQUEST); //首页不需任何参数
require_once('global.php');
if($very['aggrebbs']){
	$bbs = newBBS($very['bbs_type']);
}
if($very['aggreblog'] && $very['blog_type']){
	$blog = newBlog($very['blog_type']);
}

require_once(R_P.'require/class_cate.php');
require_once(R_P.'require/class_cms.php');
require_once(R_P.'require/class_extend.php');
$cms = new Cms();
$cate = new Cate();
$extend = new Extend();
$metakeyword = $very['title'].",".$very['metakeyword'];
$metadescrip = $very['title'].",".$very['metadescrip'];

/* 针对默认首页的设置文件，倘若自行制作模板，并不采用后台首页设置，可不需此段 */
$discate = explode(',',$very['discate']);
foreach ($discate as $key=>$cateId){
	if(!$catedb[$cateId]) unset($discate[$key]);
}
/* 结束 */
start();
require Template();
footer();
?>