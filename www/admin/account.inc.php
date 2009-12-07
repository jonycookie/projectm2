<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'manage':
	$Admin->MP("menu_account_manage");
	include iPATH.'include/group.class.php';
	$group =new group('a');
	$maxperpage =20;
	$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__admin` order by uid DESC");
	page($total,$maxperpage,"位管理员");
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__admin` order by uid DESC LIMIT {$firstcount},{$maxperpage}");
	$_count=count($rs);
	include iCMS_admincp_tpl("account.manage");
break;
case 'edit':
	$Admin->MP("menu_account_edit");
	include iPATH.'include/group.class.php';
	$group =new group('a');
	$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__admin` WHERE `uid`='".intval($_GET['uid'])."'");
	$info=unserialize($rs->info);
	include iCMS_admincp_tpl("account.edit");
break;
case 'power':
	$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__admin` WHERE `uid`='".intval($_GET['uid'])."'");
	include iCMS_admincp_tpl("account.power");
break;
case 'cpower':
	include_once iPATH.'include/catalog.class.php';
	$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__admin` WHERE `uid`='".intval($_GET['uid'])."'");
	$catalog =new catalog();
	$catalog->allArray();
	include iCMS_admincp_tpl("account.cpower");
break;
case 'del':
	$uid=(int)$_GET['uid'];
	$uid=="1" && alert('系统管理员不允许删除！',"url:{__SELF__}?do=account&operation=manage");
	$uid&&$iCMS->db->query("DELETE FROM `#iCMS@__admin` WHERE `uid`='$uid'");
	alert('已删除!',"url:{__SELF__}?do=account&operation=manage");
break;
case 'post':
	$uid=(int)$_POST['uid'];
	if($action=='power'){
		$power=@implode(",",$_POST['power']);
	    $iCMS->db->query("UPDATE `#iCMS@__admin` SET `power` = '{$power}' WHERE `uid` ='$uid' LIMIT 1");
	    redirect("设置完成!",__SELF__.'?do=account&operation=power&uid='.$uid);
	}elseif($action=='cpower'){
		$power=@implode(",",$_POST['cpower']);
	    $iCMS->db->query("UPDATE `#iCMS@__admin` SET `cpower` = '{$power}' WHERE `uid` ='$uid' LIMIT 1");
	    redirect("设置完成!",__SELF__.'?do=account&operation=cpower&uid='.$uid);
	}elseif($action=='update'){
		foreach($_POST['groupid'] AS $uid=>$groupid){
			$iCMS->db->query("UPDATE `#iCMS@__admin` SET `groupid` = '{$groupid}' WHERE `uid` ='$uid' LIMIT 1");
		}
		_Header();
	}elseif($action=='edit'){
		$username= dhtmlspecialchars($_POST['name']);
		$groupid= $_POST['groupid'];
		$email	= dhtmlspecialchars($_POST['email']);
	    $pwd	= md5($_POST['pwd']);
	    $password= md5($_POST['pwd2']);
		if(!$pwd||!$password)alert("密码,确认密码不能为空");
		$pwd!=$password&&alert("密码与确认密码不一致!");
		$email && !eregi("^([_\.0-9a-z-]+)@([0-9a-z][0-9a-z-]+)\.([a-z]{2,6})$",$email) && alert("E-mail格式错误!!");
		if(empty($uid)){
			$iCMS->db->getValue("SELECT `uid` FROM `#iCMS@__admin` WHERE `username`='{$name}'") && alert("该用户名已经存在!");
			$iCMS->db->query("INSERT INTO `#iCMS@__admin` (`username`,`password`,`groupid`,`name`,`gender`,`email`,`info`,`power`,`cpower`,`lastip`,`lastlogintime`,`logintimes`,`post`)values('$username', '$password', '$groupid', '', '0', '$email', '', '', '', '0.0.0.0', '".time()."', '0', '0')");
			redirect("添加完成!",__SELF__.'?do=account&operation=manage');
	    }else{
		    if($pwd||$password) $iCMS->db->query("UPDATE `#iCMS@__admin` SET `password` = '$password' WHERE `uid` ='$uid' LIMIT 1");
		    $iCMS->db->query("UPDATE `#iCMS@__admin` SET `name`='$name',`groupid`='$groupid',`email`='$email' WHERE `uid` ='$uid' LIMIT 1");
		    redirect("编辑完成!",__SELF__.'?do=account&operation=edit&uid='.$uid);
	    }
	}
break;
}

?>
