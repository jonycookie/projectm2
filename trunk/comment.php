<?php
/*
评论的使用模块
*/
define('SCR','comment');
require_once('global.php');
require_once(R_P.'require/class_comment.php');
require_once(R_P.'require/chinese.php');
//require_once(R_P.'require/class_cate.php');
require_once(D_P.'data/cache/face.php');
//$cate		= new Cate();
InitGP(array("shownum","job","page","ck","c_author","c_message","hideip","ajax"));
$comment	= new Comment();
if($very['commentdays']) {
	$comment->time = (int)$very['commentdays'];
}
!$job && $job ='main';
$comment->$job();
start($very['charset']);
require Template('comment');
footer();
?>