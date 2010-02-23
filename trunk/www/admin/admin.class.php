<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class Admin{
	var $uId;
	var $admin;
	var $group;
	var $db;
	var $CT;
	var $loginSuccess=False;
	var $cpower;
	//初始化 操作 兼容PHP4
	function Admin(){
		$this->__construct();
	}
	//初始化 操作 PHP5
	function __construct(){
		global $iCMS;
		$this->db	= $iCMS->db;
	}
	//检验用户
	function checkadmin($a,$p){
		//验证用户 账号/密码
		$this->admin = $this->db->getRow("SELECT * FROM `#iCMS@__admin` WHERE `username`='{$a}' AND `password`='{$p}'");
		if(empty($this->admin)){
			//记录
			$a && runlog('login', 'username='.$a.'&password='.$_POST['password']);
			$this->LoginPage();
		}else{
			$this->uId=$this->admin->uid;
			$this->admin->info && $this->admin->info=unserialize($this->admin->info);
			$this->group = $this->db->getRow("SELECT * FROM `#iCMS@__group` WHERE `gid`='{$this->admin->groupid}'");//用户组
			$this->power = explode(',',$this->merge($this->group->power,$this->admin->power));
			$cpower		 = $this->merge($this->group->cpower,$this->admin->cpower);
			$this->cpower= empty($cpower)?array(0):explode(',',$cpower);
			$this->group->gid=="1" && $this->cpower=NULL;
		}
	}
	//检查栏目权限
	function CP($p=NULL,$T="F",$url=__REF__){
		if($this->group->gid=="1") return TRUE;
		if(is_array($p)?array_intersect($p,$this->cpower):in_array($p,$this->cpower)){
			return TRUE;
		}else{
			if($T=='F'){
				return FALSE;
			}else{
//				$this->cleancookie();
				redirect(lang($T),$url);
				exit();
			}
		};
	}
	//检查后台权限
	function MP($p=NULL,$T="Permission_Denied",$url=__REF__){
		if($this->group->gid=="1") return TRUE;
		if(is_array($p)?array_intersect($p,$this->power):in_array($p,$this->power)){
			return TRUE;
		}else{
			if($T=='F'){
				return FALSE;
			}else{
//				$this->cleancookie();
				redirect(lang($T),$url);
				exit();
			}
		}
	}
	//登陆验证
	function checklogin($a,$p){
		global $_iGLOBAL;
		if(empty($a) && empty($p)){
			$auth		= get_cookie('auth');
			list($a,$p)	= explode('#=iCMS!=#',authcode($auth,'DECODE'));
			$this->checkadmin($a,$p);
		}else{
			$this->checkadmin($a,$p);
			set_cookie('auth',authcode($a.'#=iCMS!=#'.$p,'ENCODE'));
	        $this->db->query("UPDATE `#iCMS@__admin` SET `lastip`='".getip()."',`lastlogintime`='".$_iGLOBAL['timestamp']."',`logintimes`=logintimes+1 WHERE `uid`='$this->uId'");
			redirect('登陆成功, 请稍候......', __SELF__);
		}
	}
	//登陆页
	function LoginPage(){
		include iCMS_admincp_tpl('login');
	}
	//注销
	function logout($url){
		$this->cleancookie();
		redirect('注销成功, 请稍后......',$url);
	}
	function cleancookie(){
		set_cookie("auth", '',-31536000);
		set_cookie("seccode",'',-31536000);
	}
	function merge($G,$A){
		$G && $tmp[]=$G;
		$A && $tmp[]=$A;
		return @implode(',',$tmp);
	}
}
?>