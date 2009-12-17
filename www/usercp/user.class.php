<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class User{
	var $uId;
	var $user;
	var $group;
	var $db;
	var $CT;
	var $loginSuccess=False;
	var $cpower;
	//初始化 操作 兼容PHP4
	function User(){
		$this->__construct();
	}
	//初始化 操作 PHP5
	function __construct(){
		global $iCMS;
		$this->db	= $iCMS->db;
	}
	//检验用户
	function checkuser($a,$p){
		//验证用户 账号/密码
		$this->user = $this->db->getRow("SELECT * FROM `#iCMS@__members` WHERE `username`='{$a}' AND `password`='{$p}'");
		if(empty($this->user)){
			//记录
			$a && runlog('user.login', 'username='.$a.'&password='.$_POST['password']);
			$this->LoginPage();
		}else{
			$this->uId=$this->user->uid;
			$this->user->info && $this->user->info=unserialize($this->user->info);
			$this->group = $this->db->getRow("SELECT * FROM `#iCMS@__group` WHERE `gid`='{$this->user->groupid}'");//用户组
			$this->power = explode(',',$this->merge($this->group->power,$this->user->power));
			$cpower		 = $this->merge($this->group->cpower,$this->user->cpower);
			$this->cpower= empty($cpower)?array(0):explode(',',$cpower);
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
//		var_dump($p);
//		$R=is_array($p)?array_intersect($p,$this->power):!in_array($p,$this->power);
//		var_dump($R);
//		exit;
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
			$auth		= get_cookie('user');
			list($a,$p)	= explode('#=iCMS!=#',authcode($auth,'DECODE'));
			$this->checkuser($a,$p);
		}else{
			$this->checkuser($a,$p);
			set_cookie('user',authcode($a.'#=iCMS!=#'.$p,'ENCODE'));
	        $this->db->query("UPDATE `#iCMS@__members` SET `lastip`='".getip()."',`lastlogintime`='".$_iGLOBAL['timestamp']."',`logintimes`=logintimes+1 WHERE `uid`='$this->uId'");
			redirect('登陆成功, 请稍候......', __SELF__);
		}
	}
	//登陆页
	function LoginPage(){
		include iCMS_usercp_tpl('login');
	}
	//注销
	function logout($url){
		$this->cleancookie();
		redirect('注销成功, 请稍后......',$url);
	}
	function cleancookie(){
		set_cookie("user", '',-31536000);
		set_cookie("seccode",'',-31536000);
	}
	function merge($G,$A){
		$G && $tmp[]=$G;
		$A && $tmp[]=$A;
		return @implode(',',$tmp);
	}
	//-----------------------------
	//检验用户
	function __CU__($a,$p){
		//验证用户 账号/密码
		$this->user = $this->db->getRow("SELECT * FROM `#iCMS@__members` WHERE `username`='{$a}' AND `password`='{$p}'");
		if(empty($this->user)){
			//$this->cleancookie();
			return 'login';
		}else{
			$this->uId=$this->user->uid;
			$this->user->info && $this->user->info=unserialize($this->user->info);
			empty($this->user->name) && $this->user->name=$this->user->username;
			$this->group = $this->db->getRow("SELECT * FROM `#iCMS@__group` WHERE `gid`='{$this->user->groupid}'");//用户组
			$this->power = explode(',',$this->merge($this->group->power,$this->user->power));
			$cpower		 = $this->merge($this->group->cpower,$this->user->cpower);
			$this->cpower= empty($cpower)?array(0):explode(',',$cpower);
		}
	}
	function __CL__($a,$p){
		global $_iGLOBAL;
		if(empty($a) && empty($p)){
			$auth		= get_cookie('user');
			list($a,$p)	= explode('#=iCMS!=#',authcode($auth,'DECODE'));
			return $this->__CU__($a,$p);
		}else{
			$cu=$this->__CU__($a,$p);
			if(empty($cu)){
				set_cookie('user',authcode($a.'#=iCMS!=#'.$p,'ENCODE'));
		        $this->db->query("UPDATE `#iCMS@__members` SET `lastip`='".getip()."',`lastlogintime`='".$_iGLOBAL['timestamp']."',`logintimes`=logintimes+1 WHERE `uid`='$this->uId'");
				$cu='success';
			}
			return $cu;
		}
	}

}
?>