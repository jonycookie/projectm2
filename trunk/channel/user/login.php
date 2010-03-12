<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_inc_check(){

}

function king_def(){
	global $king;

//die(uc_user_synlogin(2));
	if($GLOBALS['ismethod']){//POST过程
		$data=$_POST;
	}else{
		$data=array('re'=>kc_val($_SERVER,'HTTP_REFERER'));
	}
	$data=kc_data(array('re','username','userpass','expire'));

	if($king->config('blackuser','user')){
		$array_black=explode('|',$king->config('blackuser','user'));
		$array_black=array_diff($array_black,array(null));
	}else{
		$array_black=array();
	}

	$s=$king->openForm('login.php');

	//username
	$_array=array(
		array('username',0,3,15),
		array('username',14,$king->lang->get('portal/check/reg/u-1'),array('*','\\',':','?','<','>','|',';',',','\'','!','~','$','#','@','^','(',')','{','}','=','+','%','/')),
		//array('
		array('username',14,$king->lang->get('portal/check/reg/u-2'),$array_black),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/name'),'<input class="k_in w150" type="text" name="username" value="'.htmlspecialchars($data['username']).'" maxlength="15" />',$_array,null,"<tt><a href=\"javascript:; \" class=\"k_user_register\">".$king->lang->get('portal/user/reg')."</a></tt>");

	//pass
	$_array=array(
		array('userpass',0,6,30),
	);
	if($data['userpass'] && $GLOBALS['ischeck']){//有密码 并 账号验证成功的时候进行验证

		$username=$data['username'];
		$is=False;
		if($king->user->isuc){//如果有UC

			if($array_uc=uc_user_login($data['username'],$data['userpass'])){//链接成功
				if((int)$array_uc[0]<0){//用户名不存在或密码错误
					if($res=$king->db->getRows_one("select usermail,userpass,ksalt,userid from %s_user where username='".$king->db->escape($username)."' and uid=0")){//判断本地是否存在这个用户并且未同步到uc
						$userid=$res['userid'];
						if(md5($res['ksalt'].$data['userpass'])==$res['userpass']){//检测密码
							$uid=uc_user_register($username,$data['userpass'],$res['usermail']);//注册用户到uc
							$king->db->update('%s_user',array('lastlogindate'=>time(),'uid'=>($uid>0?$uid:0)),'username=\''.$king->db->escape($username.'\''));
							uc_user_login($data['username'],$data['userpass']);
						}else{
							$is=True;
							$errId=-2;
						}
					}else{
						$is=True;
						$errId=$array_uc[0];
					}
				}else{//登录成功的时候，检查一下本地是否有这个账号
					$uid=$array_uc[0];//UC中的UID

					if($res=$king->db->getRows_one("select userpass,ksalt,userid from %s_user where username='".$king->db->escape($username)."'")){//如果有这么个账号
						$userid=$res['userid'];
						if(md5($res['ksalt'].$data['userpass'])!=$res['userpass']){//若不一致，则进行更新
							$userpass=md5($res['ksalt'].$data['userpass']);
							$king->db->update('%s_user',array('userpass'=>$userpass,'lastlogindate'=>time()),'username=\''.$king->db->escape($username.'\''));
						}else{
							$king->db->update('%s_user',array('lastlogindate'=>time()),'username=\''.$king->db->escape($username.'\''));
						}
					}else{//如果本地没有这个账号，则添加
						$usermail=$array_uc[3];

						$ksalt=kc_random(6);
						$array=array(
							'username'=>$username,
							'userpass'=>md5($ksalt.$data['userpass']),
							'usermail'=>$usermail,
							'ksalt'=>$ksalt,
							'uid'=>$uid,
							'regdate'=>time(),
							'lastlogindate'=>time(),
						);

						$king->db->insert('%s_user',$array);
						$res=$king->db->getRows_one("select userid from %s_user where uid='".$uid."' and isdelete=0");
						$userid=$res['userid'];
					}

				}
			}else{
				kc_error($king->lang->get('portal/error/connect'));//连接错误
			};
		}else{//如果没有UC

			if($res=$king->db->getRows_one("select userpass,ksalt,userid from %s_user where username='".$king->db->escape($username)."' and isdelete=0")){//如果有这么个账号

				$md5pass=md5($res['ksalt'].$data['userpass']);

//				kc_error($md5pass."\t".$res['userpass']);

				if($md5pass!=$res['userpass']){//若不一致，提示错误
					$is=True;
					$errId=-2;
				}else{//验证通过
					$userid=$res['userid'];
					$king->db->update('%s_user',array('lastlogindate'=>time()),'userid='.$userid);
//					$userpass=md5($res['ksalt'].$data['userpass']);
				}
			}else{
				$is=True;
				$errId=-1;
			}
		}

		if(!empty($errId))
			$_array[]=array('userpass',12,$king->lang->get('portal/check/pwd/p'.$errId),$is);

	}
	$s.=$king->htmForm($king->lang->get('portal/user/pass').'','<input class="k_in w150" type="password" name="userpass" maxlength="30" />',$_array,null,"<tt><a href=\"javascript:;\" class=\"k_user_lostpwd\">".$king->lang->get('portal/user/lostpwd')."</a></tt>");
	//expire
	$array_select=array(
		0=>$king->lang->get('system/time/cookie'),
		86400=>$king->lang->get('system/time/oneday'),
		2592000=>$king->lang->get('system/time/jan'),
		15768000=>$king->lang->get('system/time/halfyear'),
		31536000=>$king->lang->get('system/time/ayear'),
		315360000=>$king->lang->get('system/time/forever'),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/expire'),kc_htm_radio('expire',$array_select,2592000));
	//verify
	$verify=new KC_Verify_class;
	$s.=$verify->Show();

	$s.=kc_htm_hidden(array('re'=>$data['re']));

	$s.=$king->closeForm($king->lang->get('system/common/login'));

	if($GLOBALS['ischeck']){

		//写Cookie
		$s=$king->user->userLogin($userid,$data['expire']);

		$array=array(
			'<a href="/">'.$king->lang->get('portal/user/return/home').'</a>',
			'<a href="index.php">'.$king->lang->get('portal/user/return/uc').'</a>',
		);
		if($data['re']){
			$array[]='<a href="'.$data['re'].'">'.$king->lang->get('portal/user/return/re').' : '.$data['re'].'</a>';
			$goto=$data['re'];
		}else{
			$goto=$king->config('inst').'user/index.php';
		}
		$s.=kc_htm_ol($king->lang->get('portal/user/loginok'),$array,$goto);
	}

	$tmp=new KC_Template_class($king->config('templateuser','user'),$king->config('templatepath').'/inside/user/login.htm');
	$tmp->assign('main',$s);
	$tmp->assign('title',$king->lang->get('portal/title/login'));

	echo $tmp->output();

}
?>