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
if($sys['commentdays']) {
	$comment->time = (int)$sys['commentdays'];
}
!$job && $job ='main';
$comment->$job();
start($sys['charset']);
require Template('comment');
footer();
?>