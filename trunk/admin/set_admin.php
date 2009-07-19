<?php
!defined('IN_ADMIN') && die('Forbidden');
!in_array('set_admin',$menu_allow) && Showmsg('privilege');

InitGP(array('action','step'));
if(!$action){
	$rs = $db->query("SELECT * FROM cms_admin");
	$admindb = array();
	while($admins = $db->fetch_array($rs)){
		$admins['logintime'] = $admins['logintime'] ? get_date($admins['logintime']) : '--';
		empty($admins['ip']) && $admins['ip'] = '--';
		empty($admins['email']) && $admins['email'] = '--';
		$admins['priv'] = explode($admins['priv']);
		$admindb[] = $admins;
	}
}elseif ($action=='del'){
	$uid = (int)GetGP('uid');
	$db->update("DELETE FROM cms_admin WHERE uid='$uid'");
	adminmsg('admin_delok');
}elseif ($action=='add'){
	if(!$step){
		//print $REQUEST_URI;
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select=$cate->tree();
	}elseif($step==2){
		InitGP(array('new_username','new_password','new_password2','new_email','check','privcate'),'P',1);
		if($admin_name!=$manager) Showmsg('set_onlymanager');
		empty($new_username) && Showmsg('admin_nousername');
		empty($new_password) && Showmsg('admin_nopassword');
		$new_password != $new_password2 && Showmsg('set_confirmpwd');
		$rs = $db->get_one("SELECT * FROM cms_admin WHERE username='$new_username'");
		$rs && Showmsg('admin_userexists');
		$new_password = md5($new_password);
		$new_privilege = array();
		foreach ($check as $key=>$v){
			$v==1 && $new_privilege[] = Char_cv($key);
		}
		$new_privilege = implode(',',$new_privilege);
		foreach($privcate as $key=>$val){
			if(!((int)$val)){
				unset($privcate[$key]);
			}
		}
		$privcate = implode(',',$privcate);
		$db->update("INSERT INTO cms_admin SET
			`username`='$new_username',
			`password`='$new_password',
			`email`='$new_email',
			`priv`='$new_privilege',
			`privcate`='$privcate'
		");
		adminmsg('admin_addok');
	}
}elseif ($action=='edit'){
	$uid = (int)GetGP('uid');
	$edit = $db->get_one("SELECT * FROM cms_admin WHERE uid='$uid'");
	$edit['username'] == $manager && Showmsg('set_cannoteditmanager');
	if(!$step){
		$edit['priv'] = explode(',',$edit['priv']);
		$privcate = explode(',',$edit['privcate']);
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select=$cate->tree();
		foreach ($privcate as $cid){
			$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);
		}
	}elseif ($step == 2){
		InitGP(array('new_username','new_password','new_password2','new_email','check','privcate'),'P',1);
		if($admin_name!=$manager) Showmsg('set_onlymanager');
		$sqladd = '';
		if(!empty($new_password)){
			$new_password!=$new_password2 && Showmsg('set_confirmpwd');
			$new_password = md5($new_password);
			$sqladd = ",`password`='$new_password'";
		}
		$new_privilege = array();
		foreach ($check as $key=>$v){
			$v==1 && $new_privilege[] = Char_cv($key);
		}
		$new_privilege = implode(',',$new_privilege);
		foreach($privcate as $key=>$val){
			if(!((int)$val)){
				unset($privcate[$key]);
			}
		}
		$privcate = implode(',',$privcate);
		$db->update("UPDATE cms_admin SET
			`email`='$new_email',`priv`='$new_privilege',`privcate`='$privcate' $sqladd WHERE uid='$uid'
		");
		adminmsg('admin_editok');
	}
}
require PrintEot('set_admin');
adminbottom();
?>