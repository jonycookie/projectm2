<?php
!function_exists('adminmsg') && die('Forbbiden');
$action = GetGP('action');
if($action=='in'){
	InitGP(array('username','password','ck'),'P',1);
	if($very['ckadmin']){
		empty($ck) && Showmsg('login_nock');
		$ck = strtolower($ck);
		GdConfirm($ck);
	}
	if(empty($username) || empty($password)) Showmsg('login_empty');
	$username = trim($username);
	$password = MD5(trim($password));
	$admininfo = $db->get_one("SELECT password,loginfail,logintime FROM cms_admin WHERE username='$username' LIMIT 1");
	if($admininfo['loginfail'] >= 15){
		if($admininfo['logintime']+3600*24 > $timestamp){
			$db->update("UPDATE cms_admin SET loginfail=0 WHERE username='$admin_name'");
		}else{
			Showmsg('login_maxerror');
		}
	}
	if($username==$manager && $password==$manager_pwd){ //创始人
		$ck_time = $timestamp + 3600;
		Cookie("Adminuser",$username."\t".$password,$ck_time);
		$db->update("UPDATE cms_admin SET loginfail=0,logintime='$timestamp',ip='$onlineip' WHERE username='$username'");
		$adminFileName = end(explode('/',$admin_file));
		ObHeader($adminFileName);
	}else{ //非创始人
		if(!$admininfo || $admininfo['password']!=$password){
			$db->update("UPDATE cms_admin SET loginfail=loginfail+1,logintime='$timestamp',ip='$onlineip' WHERE username='$username'");
			$record_name= str_replace('|','&#124;',Char_cv($_POST['username']));
			$record_pwd	= str_replace('|','&#124;',Char_cv($_POST['password']));
			$new_record="|$record_name|$record_pwd|Logging Failed|$onlineip|$timestamp|\n";
			writeover($logfile,$new_record,"ab");
			Showmsg('login_error');
		}else{
			$ck_time = $timestamp + 3600;
			Cookie("Adminuser",$username."\t".$password,$ck_time);
			$db->update("UPDATE cms_admin SET loginfail=0,logintime='$timestamp',ip='$onlineip' WHERE username='$username'");
			$adminFileName = end(explode('/',$admin_file));
			ObHeader($adminFileName);
		}
	}
}elseif($action=='out'){
	Cookie('Adminuser','');
	$adminFileName = end(explode('/',$admin_file));
	ObHeader($adminFileName);
}
require PrintEot('header');
require PrintEot('login');
adminbottom();
?>