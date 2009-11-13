<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
$do=$_GET['do'];
if(empty($do)){
	require_once(iPATH."include/function/template.php");
	$iCMS->comment((int)$_GET['aid'],(int)$_GET['mid'],(int)$_GET['sortid']);
}else{
	if($do=='replay'){
		$frame=$_POST['iframe']?true:false;
		if($_POST['action']=='save'){
		    ckseccode($_POST['seccode']) && msgJson(0,'error:seccode',$frame);
		    $username	=dhtmlspecialchars($_POST['username']);
			$password	=trim($_POST['password']);
		    $iseditor	= (int)$_POST['iseditor'];
	//	    //去除链接
		    $commentext	=preg_replace("/(<a[ \t\r\n]{1,}href=[\"']{0,}http:\/\/[^\/]([^>]*)>)|(<\/a>)/isU","",stripslashes($_POST['commentext']));
		    $commentext	=str_replace(array('<p>&nbsp;</p>','<p style="margin: 9px 3px; color: #000000; line-height: 20px; text-align: left">&nbsp;</p>'),'',$commentext);
		    $commentext	=$iseditor?addslashes(sechtml($commentext)):addslashes(dhtmlspecialchars($commentext));
		    $title		=dhtmlspecialchars($_POST['title']);
		    $aid		=(int)$_POST['aid'];
		    $sortid		=(int)$_POST['sortid'];
		    $mid		=(int)$_POST['mid'];
		    $quote		=(int)$_POST['quote'];
		    WordFilter($username) && msgJson(0,'filter:username',$frame);
		    WordFilter($commentext) && msgJson(0,'filter:content',$frame);
		    WordFilter($title) && msgJson(0,'filter:title',$frame);
		    empty($mid) && $mid=0;
			if($iCMS->config['anonymous']&&empty($password)){
				$uid='0';
			}else{
		    	if(empty($username) ||empty($password)){
			    	empty($username) && msgJson(0,'comment:emptyusername',$frame);
			    	empty($password) && msgJson(0,'comment:emptypassword',$frame);
		    		
		    	}
		    	require_once iPATH."usercp/user.class.php";
		    	require_once iPATH.'usercp/usercp.lang.php';
		    	$member	= new User;
		    	$cl		= $member->__CL__($username,md5($password));
		    	if($cl=='login'){
		    		msgJson(0,'comment:error',$frame);
		    	}elseif($cl=='success'){
		    		$uid=$member->uId;
			    	$username=empty($member->user->info['nickname'])?$member->user->username:$member->user->info['nickname'];
		    	}
			}
			!$iCMS->config['anonymousname']&&$iCMS->config['anonymousname']=$iCMS->language('guest');
		    $iCMS->config['anonymous'] && empty($username) && $username=$iCMS->config['anonymousname'];
		    $isexamine=$iCMS->config['isexamine']?'0':'1';
		    !$commentext && msgJson(0,'comment:empty',$frame);
			
		    if($iCMS->db->query("INSERT INTO `#iCMS@__comment` (`aid`,`sortid`,`mid`,`username`,`uid`,`quote`,`atitle`,`contents`,`reply`,`addtime`,`ip`,`isexamine`,`up`,`against`,`zt`) VALUES ('$aid','$sortid','$mid', '$username', '$uid','$quote', '$title', '$commentext','', '".time()."', '".getip()."', '$isexamine', '0', '0', '0')")){
				if($iCMS->config['isexamine']){
					msgJson(1,'comment:examine',$frame);
				}else{
					if(empty($mid)){
						$__TABLE__='article';
					}else{
						$__MODEL__	= $iCMS->cache('model.id','include/syscache',0,true);
						$model		= $__MODEL__[$mid];
						$__TABLE__	= $model['table'].'_content';
					}
					$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `comments` = comments+1  WHERE `id` ='$aid'");
					msgJson(1,'comment:post',$frame);
				}
			}else{
				msgJson(1,'comment:Unknown',$frame);
			}
		}
	}
}
?>