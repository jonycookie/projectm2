<?php
!defined('IN_ADMIN') && die('Forbidden');
require GetLang('dbset');
$step = GetGP('step');
if($admin_name!=$manager) Showmsg('onlycreator');
if($step==2){
	InitGP(array('creator_pwd','creator_pwd2','creator'),'P');
	$creator_pwd !=$creator_pwd2 && Showmsg('set_confirmpwd');
	$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
	foreach($S_key as $value){
		if (strpos($creator_pwd,$value)!==false){
			adminmsg('illegal_password');
		}
	}
	if($creator_pwd){
		$creator_pwd = md5($creator_pwd);
		$setting['pwd'] = $creator_pwd;
	}else{
		$creator_pwd = $manager_pwd;
	}
	if($creator){
		$setting['user'] = $creator;
	}
	require_once(R_P.'require/class_cache.php');
	$cache = new Cache();
	$cache->sql($setting);
	if($db->get_one("SELECT * FROM cms_admin WHERE username='$creator'")){
		$db->update("UPDATE cms_admin SET password='$creator_pwd' WHERE username='$creator'");
	}else{
		$db->update("INSERT INTO cms_admin SET password='$creator_pwd',username='$creator'");
	}
	adminmsg('operate_success');
}
require PrintEot('set_creator');
adminbottom();
?>