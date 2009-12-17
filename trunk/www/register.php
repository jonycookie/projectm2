<?php
require_once("global.php");
require_once(iPATH."include/function/template.php");

$do=$_GET['do'];
if(empty($do)){
	$iCMS->iPrint("iSYSTEM","register");
}elseif($do=='post'){
	if($_POST['action']=='save'){
		ckseccode($_POST['seccode']) && msgJson('seccode','error:seccode');
		$username=dhtmlspecialchars($_POST['username']);
		WordFilter($username) && msgJson('username','filter:username');
		cstrlen($username)<3 && msgJson('username','register:usernameShort');
		cstrlen($username)>12 && msgJson('username','register:usernameLong');
		$iCMS->db->getValue("SELECT uid FROM `#iCMS@__members` where `username`='$username'") && msgJson('username','register:usernameusr');
		
		$password=md5(trim($_POST['password']));
		$pwdrepeat=md5(trim($_POST['pwdrepeat']));
		$password!=$pwdrepeat && msgJson('pwdrepeat','register:different');
		
	    $_POST['email']&&!eregi("^([_\.0-9a-z-]+)@([0-9a-z][0-9a-z-]+)\.([a-z]{2,6})$",$_POST['email']) && msgJson('email','register:emailerror');
	    $email=$_POST['email'];
	    $gender=intval($_POST['gender']);
	    $nickname=dhtmlspecialchars($_POST['nickname']);
//	    cstrlen($info['nickname'])>12 && msgJson(0,'register:nicknamelong');
	    $_POST['icq'] && $info['icq']=intval($_POST['icq']);
		$_POST['home'] && $info['home']=dhtmlspecialchars(stripslashes($_POST['home']));
	    $_POST['year'] && $info['year']=intval($_POST['year']);
	    $_POST['month'] && $info['month']=intval($_POST['month']);
	    $_POST['day'] && $info['day']=intval($_POST['day']);
	    $_POST['from'] && $info['from']=dhtmlspecialchars(stripslashes($_POST['from']));
	    $_POST['signature'] && $info['signature']=dhtmlspecialchars(stripslashes($_POST['signature']));
	    $info=empty($info)?'':addslashes(serialize($info));
		$iCMS->db->query("INSERT INTO `#iCMS@__members` (`username`,`password`,`groupid`,`name`,`gender`,`email`,`info`,`power`,`cpower`,`lastip`,`lastlogintime`,`logintimes`,`post`) VALUES ('$username','$password', '4','$nickname','$gender','$email','$info','','','".getip()."', '".time()."','0','0') ");
		//设置为登陆状态
		set_cookie('user',authcode($username.'#=iCMS!=#'.$password,'ENCODE'));
		msgJson(1,'register:finish');
	}
}