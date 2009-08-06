<?php !defined('KC_IN') && exit('No direct script access allowed');



class user_class extends portal_class{

public $isuc;//是否整合UCenter 1整合 0不整合
public $userid=0;

public function __construct(){

	global $king;

	//设置是否整合UC
	$this->isuc=$king->config('usercenter','user');

	//静态化变量
	if($this->isuc && !defined('UC_CONNECT')){
		$array_const=array('UC_CONNECT','UC_DBHOST','UC_DBUSER','UC_DBPW','UC_DBNAME','UC_DBCHARSET','UC_DBTABLEPRE','UC_KEY','UC_API','UC_CHARSET','UC_IP','UC_APPID','UCPATH','UC_DBCONNECT');
		foreach($array_const as $val){
			define($val,$king->config(strtolower($val),'user'));
		}
		require_once KC_ROOT.$king->config('ucpath','user').'/client.php';
	}
}
/**
 * 写入UserCookie
 * @param int    $userid    用户userid
 * @param string $username  用户名
 * @param int    $expire    Cookie失效时间
 * @param string $md5pass   经过md5加密后的密码(数据库中存储的)，整合UC的时候无效
 * @return void
 */
public function userLogin($userid,$expire){//补充日期信息
	global $king,$action;

	$user=$this->infoUser($userid);
	kc_setCookie('auth_'.$king->config('userpre','user') , $user['authcookie'] , $expire);

	if($this->isuc){
		return uc_user_synlogin($user['uid']);
	}

	return '';

}
public function userLogout(){
	global $king;
	kc_setCookie('auth_'.$king->config('userpre','user') , '' , -86400 * 366 );
	$s='';
	if($this->isuc){
		$s=uc_user_synlogout();
	}
	return $s;
}
/**
 * 用户登录及权限验证
 * @param int $gid  用户组
 * @return array
*/
public function access($gid=0){
	global $king;

	if(!($user=$this->checkLogin())){//若未登录状态，则跳到登陆页

		if($GLOBALS['action']=='ajax'){//ajax页面里不做跳转
			$js="$.kc_ajax('{URL:\'".$king->config('inst')."user/index.php\',CMD:\'login\',IS:1,METHOD:\'GET\'}')";
			kc_ajax('','','',$js);
		}else{
			header("Location: ".$king->config('inst')."user/login.php");
		}
	}

	if($gid!==0){//如果gid不等于0的话，则比较gid
		if($gid!=$user['gid']){
			if($GLOBALS['action']=='ajax'){
				kc_error($king->lang->get('user/error/gaccess'));
			}else{
				$tmp=new KC_Template_class($king->config('templatelogin','user'),$king->config('templatepath').'/inside/system/error.htm');
				$tmp->assign('main',$king->lang->get('user/error/gaccess'));
				$tmp->assign('title',$king->lang->get('system/common/error'));
				exit($tmp->output());
			}
		}
	}

	//权限验证
	return $user;

}
/**
 * 验证用户是否已登录
 */
public function checkLogin(){
	global $king;

	if($auth=kc_cookie('auth_'.$king->config('userpre','user'))){

		list($userid,$username,$userpass)=explode("\t",$auth);

		$user=$this->infoUser($userid);
		if($user['authcookie']!=$auth){
			return False;
		}
		$this->userid=$userid;
		return $user;
	}else{
		return False;
	}
}
/* ------>>> get信息 <<<----------------------------- */
public function getGroup(){
	global $king;

	$cachepath="user/groups";
	if(!$array=$king->cache->get($cachepath)){
		$array=array(0=>$king->lang->get('user/group/default'));//默认用户组
		if($res=$king->db->getRows("select gid,kname from %s_usergroup;")){
			foreach($res as $rs){
				$array[$rs['gid']]=htmlspecialchars($rs['kname']);
			}
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/* ------>>> Info信息 <<<---------------------------- */

/**
 * 用户信息
 * @param int  $userid
 * @return Array
 */
public function infoUser($userid=''){
	global $king;

	if(!$userid){
		$array=array(
			'username'=>'['.$king->lang->get('user/name/guest').']',
			'nickname'=>$king->lang->get('user/name/nkguest'),
			'userid'=>0,
		);
		return $array;
	}elseif($userid==='-1'){
		$array=array(
			'username'=>'['.$king->lang->get('user/name/admin').']',
			'nickname'=>$king->lang->get('user/name/nkadmin'),
			'userid'=>-1,
		);
		return $array;
	}
	$cachepath="user/info/".wordwrap($userid,1,'/',1);

	if(!$array=$king->cache->get($cachepath,1)){
		if(!kc_validate($userid,2))//判断是否为数字类型，以免被注入
			return False;
		if(!$res=$king->db->getRows_one("select * from %s_user where userid=$userid;"))
			return False;

		$array=array();
		foreach($res as $key => $val){
			if(!kc_validate($key,2))
				$array[$key]=htmlspecialchars($val);
		}
		$array['authcookie']=$userid."\t".$res['username']."\t".md5($res['username'].$king->config('key'));//cookie中的userpass段
		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/**
 * delUserInfo
 * @param int $userid  用户id
 * @return void
 */
public function delUserInfo($userid){
	global $king;

	$cachepath="user/info/".wordwrap($userid,1,'/',1);
	$king->cache->del($cachepath);
}
public function infoGroup($gid){
	global $king;

	$cachepath="user/group/".$gid;

	if(!$array=$king->cache->get($cachepath,1)){
		if($gid==0){
			$array=array(
				'gid'=>0,
				'kname'=>$king->lang->get('user/group/default'),
				'norder'=>0,
				'kaccess'=>'',
				'kremark'=>'',
				'kmenu'=>'',
			);
		}elseif($res=$king->db->getRows_one("select * from %s_usergroup where gid=$gid")){
			$array=array();
			foreach($res as $key => $val){
				if(!kc_validate($key,2)){
					$array[$key]=htmlspecialchars($val);
				}
			}
		}else{
			return False;
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;
}



/* ------>>> 标签解析 <<<---------------------------- */

public function tag($name,$inner,$ass,$attrib){
	global $king;
	$names=explode('.',$name);
	$type=isset($names[1]) ? $names[1] : '';

	switch($type){
		case '':$s=$this->tag_user_info($inner,$attrib);break;
		case 'info':$s=$this->tag_user_info($inner,$attrib);break;
		case 'state':$s=$this->tag_user_state($inner,$attrib);break;
		case 'group':$s=$this->tag_user_group($inner,$attrib);break;

		default:
	}
	
	return $s;

}
/**
 * 用javascript判断用户状态
 */
private function tag_user_state($inner,$attrib){

	$id=$attrib['id']?$attrib['id']:'k_userstate';
	list($no,$yes)=preg_split("/{\?king\:user\.state *}/i",$inner);

	$tmp=new KC_Template_class;
	$no=$tmp->output($no);
	$tmp->assign('username','[USERNAME]');
	$tmp->assign('userid','[USERID]');
	$yes=$tmp->output($yes);

	$s="<span id=\"$id\">{$no}</span><span id=\"{$id}_hide\" class=\"none\">$yes</span><script type=\"text/javascript\">$.kc_userstate('$id')</script>";


	return $s;
}
private function tag_user_info($inner,$attrib){

	if(!$userid=kc_val($attrib,'userid'))
		return False;

	$user=$this->infoUser($userid);

	$tmp=new KC_Template_class;
	foreach($user as $key => $val){
		if(!in_array($key,array('userask','userpass','useranswer','ksalt','kremark','authcookie'))){
			$tmp->assign($key,$val);
		}
	}
	$s=$tmp->output($inner);
	return $s;

}
private function tag_user_group($inner,$attrib){
	global $king;


	$whereArray=array();

	$gid=kc_val($attrib,'gid');

	if($gid==0){//默认帐号
		
	}

	$group=$this->infoGroup($gid);

	$tmp=new KC_Template_class;
	$tmp->assign('gid',$group['gid']);
	$tmp->assign('name',$group['kname']);
	$tmp->assign('access',$group['kaccess']);
	$tmp->assign('menu',$group['kmenu']);
	$s=$tmp->output($inner);
	return $s;


}



}
?>