<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch($operation){
	case 'profile':
		$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__members` WHERE `uid`='".$member->uId."'");
		$rs->info=unserialize($rs->info);
		include iCMS_usercp_tpl("profile");
	break;
	case 'post':
		if($action=='edit'){
			$uid=$member->uId;
			$info=array();
		    if($_POST['pwd']||$_POST['pwd1']||$_POST['pwd2']){
			    $pwd=md5(trim($_POST['pwd']));
			    $pwd1=md5(trim($_POST['pwd1']));
			    $pwd2=md5(trim($_POST['pwd2']));
		    	if(!$_POST['pwd']||!$_POST['pwd1']||!$_POST['pwd2'])alert("修改密码.原密码,新密码,确认密码不能为空");
			    $pwd!=$user['password']&&alert("原密码错误!");
			    $pwd1!=$pwd2&&alert("新密码与确认密码不一致!");
			    $iCMS->db->query("UPDATE `#iCMS@__members` SET `password` = '$pwd2' WHERE `uid` ='$uid' LIMIT 1");
			}
	//	    $username=dhtmlspecialchars($_POST['name']);
		    $_POST['email']&&!eregi("^([_\.0-9a-z-]+)@([0-9a-z][0-9a-z-]+)\.([a-z]{2,6})$",$_POST['email'])&&alert("E-mail格式错误!!");
		    $email=stripslashes($_POST['email']);
		    $gender=intval($_POST['gender']);
		    $info['nickname']=dhtmlspecialchars(stripslashes($_POST['nickname']));
		    cstrlen($info['nickname'])>12 && alert("昵称长度大于12");
		    $info['icq']=intval($_POST['icq']);
			$info['home']=dhtmlspecialchars(stripslashes($_POST['home']));
		    $info['year']=intval($_POST['year']);
		    $info['month']=intval($_POST['month']);
		    $info['day']=intval($_POST['day']);
		    $info['from']=dhtmlspecialchars(stripslashes($_POST['from']));
		    $info['signature']=dhtmlspecialchars(stripslashes($_POST['signature']));
		    $user['info']=$info;
		    $iCMS->db->query("UPDATE `#iCMS@__members` SET `info` = '".addslashes(serialize($user['info']))."',`email`='$email',`gender`='$gender' WHERE `uid` ='$uid' LIMIT 1");
		    _Header(__SELF__.'?do=setting&operation=profile');
		}
	break;
}
?>