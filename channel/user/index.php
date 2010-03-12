<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_ajax_login(){
	global $king;

	/*
	if($GLOBALS['ismethod']){//POST过程
	}
	*/
	$data=kc_data(array('username','userpass','expire'));

	if($king->config('blackuser','user')){
		$array_black=explode('|',$king->config('blackuser','user'));
		$array_black=array_diff($array_black,array(null));
	}else{
		$array_black=array();
	}

	//username
	$_array=array(
		array('username',0,3,15),
		array('username',14,$king->lang->get('portal/check/reg/u-1'),array('*','\\',':','?','<','>','|',';',',','\'','!','~','$','#','@','^','(',')','{','}','=','+','%','/')),
		//array('
		array('username',14,$king->lang->get('portal/check/reg/u-2'),$array_black),
	);
	$s=$king->htmForm($king->lang->get('portal/user/name'),'<input class="k_in w150" type="text" name="username" value="'.htmlspecialchars($data['username']).'" maxlength="15" />',$_array,null,"<tt><a href=\"#\" class=\"k_user_register\">".$king->lang->get('portal/user/reg')."</a></tt>");

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
							'lastlogindate'=>kc_now(),
						);

						$king->db->insert('%s_user',$array);
						$res=$king->db->getRows_one("select userid from %s_user where uid='".$uid."' and isdelete=0");
						$userid=$res['userid'];
					}
				}
			}else{
				kc_error($king->lang->get('portal/error/connect'));//连接错误
			}
		}else{//如果没有UC

			if($res=$king->db->getRows_one("select userpass,ksalt,userid from %s_user where username='".$king->db->escape($username)."' and isdelete=0")){//如果有这么个账号

				$md5pass=md5($res['ksalt'].$data['userpass']);

				if($md5pass!=$res['userpass']){//若不一致，提示错误
					$is=True;
					$errId=-2;
				}else{//验证通过
					$userid=$res['userid'];
					$king->db->update('%s_user',array('lastlogindate'=>time()),'userid='.$userid);
				}
			}else{
				$is=True;
				$errId=-1;
			}
		}

		if(!empty($errId))
			$_array[]=array('userpass',12,$king->lang->get('portal/check/pwd/p'.$errId),$is);

	}
	$s.=$king->htmForm($king->lang->get('portal/user/pass').'','<input class="k_in w150" type="password" name="userpass" maxlength="30" />',$_array,null,"<tt><a href=\"#\" class=\"k_user_lostpwd\">".$king->lang->get('portal/user/lostpwd')."</a></tt>");
	//expire
	$array_select=array(
		0=>$king->lang->get('system/time/cookie'),
		86400=>$king->lang->get('system/time/oneday'),
		2592000=>$king->lang->get('system/time/jan'),
		15768000=>$king->lang->get('system/time/halfyear'),
		31536000=>$king->lang->get('system/time/ayear'),
		315360000=>$king->lang->get('system/time/forever'),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/expire'),kc_htm_select('expire',$array_select,2592000));
	//verify
	$verify=new KC_Verify_class;
	$s.=$verify->Show();



	if($GLOBALS['ischeck']){

		//写Cookie
		$s=$king->user->userLogin($userid,$data['expire']);

		//登录日志?
		kc_ajax($king->lang->get('portal/user/loginok'),$s."<p class=\"k_ok\">".$king->lang->get('portal/user/welcome')."</p>",1);

	}

	$title=$king->lang->get('system/common/login');
	$but=kc_htm_a($king->lang->get('system/common/login'),"{URL:'".$king->config('inst')."user/index.php',CMD:'login',IS:1}");
	$height=$king->config('verifyopen') ? 170+$king->config('verifyheight') : 160;
	kc_ajax($title,$s,$but,'',420,$height + $GLOBALS['check_num']*15);

}
function king_ajax_logout(){
	global $king;
	$s=$king->user->userLogout();
	kc_ajax($king->lang->get('user/common/logout'),$s."<p class=\"k_ok\">".$king->lang->get('user/ok/logout')."</p>",1);

}
function king_ajax_register(){
	global $king;

	if($king->config('isregister','user')==0){
		kc_ajax($king->lang->get('user/title/regstop'),$king->config('registertip','user'),0,'',500,200);
	}
	if(!kc_post('is')){
		$s=$king->config('reglicense','user');
		$but=kc_htm_a($king->lang->get('portal/user/iaccept'),'{URL:\''.$king->config('inst').'user/index.php\',CMD:\'register\',METHOD:\'GET\',is:1,IS:1}');
		$but.="<a href=\"javascript:;\" class=\"k_close\">".$king->lang->get('system/common/cancel')."</a>";

		$height=400;
		$title=$king->lang->get('portal/title/reglicense');
		$GLOBALS['ischeck']=false;
	}else{

		/*
		if($GLOBALS['ismethod']){//POST过程
			$data=$_POST;
		}
		*/
		$data=kc_data(array('username','userpass','usermail'));

		if($king->config('blackuser','user')){
			$array_black=explode('|',$king->config('blackuser','user'));
			$array_black=array_diff($array_black,array(null));
		}else{
			$array_black=array();
		}

		//username
		$_array=array(
			array('username',0,3,15),
			array('username',14,$king->lang->get('portal/check/reg/u-1'),array('*','\\',':','?','<','>','|',';',',','\'','!','~','$','#','@','^','(',')','{','}','=','+','%','/')),
			array('username',14,$king->lang->get('portal/check/reg/u-2'),$array_black),
			array('username',12,$king->lang->get('portal/check/reg/u-3'),$king->db->getRows_one("select userid from %s_user where username='".$king->db->escape(kc_post('username'))."';")),
		);
		if($king->user->isuc && $GLOBALS['ismethod']){//有提交操作的时候才做验证
			$ucheck=uc_user_checkname(kc_post('username'));
			$_array[]=array('username',12,$king->lang->get('system/check/reg/u'.$ucheck),$ucheck!=1);
		}
		$s=$king->htmForm($king->lang->get('portal/user/name').' (3-15)','<input class="k_in w150" type="text" name="username" value="'.htmlspecialchars(kc_post('username')).'" maxlength="15" />',$_array);
		//pass
		$_array=array(
			array('userpass',0,6,30),
			array('userpass',17,null,'userpass1'),
		);
		$s.=$king->htmForm($king->lang->get('portal/user/pass').' (6-30)','<input class="k_in w150" type="password" name="userpass" maxlength="30" />',$_array);
		//repass
		$s.=$king->htmForm($king->lang->get('portal/user/pass1'),'<input class="k_in w150" type="password" name="userpass1" maxlength="30" />');
		//mail
		$_array=array(
			array('usermail',0,6,32),
			array('usermail',5,$king->lang->get('portal/check/reg/u-4')),
			array('usermail',14,$king->lang->get('portal/check/reg/u-2'),$array_black),
			array('usermail',12,$king->lang->get('portal/check/reg/u-6'),$king->db->getRows_one("select userid from %s_user where usermail='".$king->db->escape(kc_post('usermail'))."';")),
		);
		if($king->user->isuc && $GLOBALS['ismethod']){//有提交操作的时候才做验证
			$ucheck=uc_user_checkemail(kc_post('usermail'));
			$_array[]=array('usermail',12,$king->lang->get('system/check/reg/u'.$ucheck),$ucheck!=1);

		}
		$s.=$king->htmForm($king->lang->get('portal/user/mail'),'<input class="k_in w250" type="text" name="usermail" value="'.htmlspecialchars($data['usermail']).'" maxlength="32" />',$_array);

		//verify
		$verify=new KC_Verify_class;
		$s.=$verify->Show();

		$but=kc_htm_a($king->lang->get('portal/user/register'),'{URL:\''.$king->config('inst').'user/index.php\',CMD:\'register\',is:1,IS:1}');

		$height=$king->config('verifyopen') ? 210+$king->config('verifyheight') : 200;

		$title=$king->lang->get('user/title/reguser');

	}

	if($GLOBALS['ischeck']){
		//先提交到ucenter后再提交到本地数据库
		if($king->user->isuc){
			$uid=uc_user_register($data['username'],$data['userpass'],$data['usermail']);
			if($uid<0)
				error($king->lang->get('portal/check/reg/u'.$uid));
			if($uid==0)
				error($king->lang->get('portal/error/connect'));//发出连接错误
		}else{
			$uid=0;//没有UC的时候，uid设置为0，这样以后同步帐号仅同步uid=0的帐号$king->db->neworder('%s_user',null,'uid');//若没有UC链接的时候uid自动递增
		}

		$array_sql=array('username','usermail');
		$array=array();
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}
		$salt=kc_random(6);
		$md5pass=md5($salt.$data['userpass']);
		$array['userpass']=$md5pass;
		$array['uid']=$uid;
		$array['ksalt']=$salt;
		$array['regdate']=time();
		$userid=$king->db->insert('%s_user',$array);

		//写Cookie
		$s=$king->user->userLogin($userid,2592000);

		kc_ajax($king->lang->get('system/common/welcome'),$s."<p class=\"k_ok\">".$king->lang->get('portal/user/regok')."</p>",1);
	}


	kc_ajax($title,$s,$but,'',500,$height + $GLOBALS['check_num']*15);
}
function king_ajax_resetpwd(){
	global $king;
	$user=$king->user->access();
	//oldpass
	$_array=array(
		array('oldpass',0,6,30),
//		array('oldpass',12,$king->lang->get('user/check/oldpass'),$user['userpass']!=md5($user['ksalt'].kc_post('oldpass'))),
	);
	$s=$king->htmForm($king->lang->get('portal/user/oldpass'),'<input class="k_in w150" type="password" name="oldpass" maxlength="30" />',$_array);
	//pass
	$_array=array(
		array('userpass',0,6,30),
		array('userpass',17,null,'userpass1'),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/newpass'),'<input class="k_in w150" type="password" name="userpass" maxlength="30" />',$_array);
	//repass
	$s.=$king->htmForm($king->lang->get('portal/user/pass1'),'<input class="k_in w150" type="password" name="userpass1" maxlength="30" />');

	$but='<a href="javascript:;" class="k_ajax" rel="{URL:\''.$king->config('inst').'user/index.php\',CMD:\'resetpwd\',IS:1}">'.$king->lang->get('system/common/modify').'</a>';

	if($GLOBALS['ischeck']){
		$ksalt=kc_random(6);

		$md5pass=md5($ksalt.kc_post('userpass'));

		$array=array(
			'ksalt'=>$ksalt,
			'userpass'=>$md5pass,
		);
		//更新ucenter中心的用户资料
		if($king->user->isuc){
			$ret=uc_user_edit($user['username'],kc_post('oldpass'),kc_post('userpass'));
//kc_error($ret);
			if((int)$ret<0){
				kc_error($king->lang->get("portal/check/edit/u{$ret}"));
			}
		}
		$king->db->update('%s_user',$array,"userid=".$user['userid']);
//		kc_error(nl2br(print_r($user,1)));
		//删除用户信息
		$king->user->delUserInfo($king->user->userid);
		//重写Cookie
		$s=$king->user->userLogin($king->user->userid,2592000);


		kc_ajax('OK',$s."<p class=\"k_ok\">".$king->lang->get('system/ok/set')."</p>",1);
	}

	kc_ajax($king->lang->get('user/title/resetpwd'),$s,$but,'',360,170 + $GLOBALS['check_num']*15);
}
function king_ajax_lostpwd(){

	global $king;

	$username=kc_post('username');

	//username
	$_array=array(
		array('username',0,3,15),
		array('username',14,$king->lang->get('portal/check/reg/u-1'),array('*','\\',':','?','<','>','|',';',',','\'','!','~','$','#','@','^','(',')','{','}','=','+','%','/')),
		array('username',12,$king->lang->get('portal/check/lost/name'),!$king->db->getRows_one("select userid from %s_user where isdelete=0 and username='".$king->db->escape(kc_post('username'))."';")),
//这里还要补充是否存在这个用户的验证，并获得userid
	);
	$s=$king->htmForm($king->lang->get('portal/user/name'),'<input class="k_in w150" type="text" name="username" id="username" value="'.htmlspecialchars(kc_post('username')).'" maxlength="15" />',$_array);

	$verify=new KC_Verify_class;
	$s.=$verify->Show();

	if($GLOBALS['ischeck']){

		kc_ajax('','','',"$.kc_ajax('{URL:\'".$king->config('inst')."user/index.php\',CMD:\'lostpwd1\',username:\'".kc_post('username')."\',METHOD:\'GET\',IS:1,URL:\'".$king->config('inst')."user/index.php\'}')");

	}

	$but=kc_htm_a($king->lang->get('system/common/submit'),"{URL:'".$king->config('inst')."user/index.php',CMD:'lostpwd',IS:1}");
	kc_ajax($king->lang->get('portal/user/lostpwd'),$s,$but,'',420,140 + $GLOBALS['check_num']*15);
}
function king_ajax_lostpwd1(){
	global $king;

	$username=kc_post('username');

	//ask
	if($user=$king->db->getRows_one("select userid,uid,userask,useranswer,usermail from %s_user where isdelete=0 and  username='".$king->db->escape(kc_post('username'))."'")){
		if(!$user['userask']){
			kc_error($king->lang->get('user/error/ask'));
		}
	}else{
		kc_error($king->lang->get('system/error/param'));
	}
	$s=$king->htmForm($king->lang->get('user/label/ask'),htmlspecialchars($user['userask']));
	//answer
	$array=array(
		array('useranswer',0,1,16),
		array('useranswer',12,$king->lang->get('portal/check/lost/answer'),$user['useranswer']!=kc_post('useranswer')),
	);
	$s.=$king->htmForm($king->lang->get('user/label/answer'),'<input class="k_in w150" type="text" name="useranswer" id="useranswer" maxlength="16" value="'.htmlspecialchars(kc_post('useranswer')).'" />',$array);

	//mail
	$_array=array(
		array('usermail',0,6,32),
		array('usermail',5,$king->lang->get('portal/check/reg/u-4')),
		array('usermail',12,$king->lang->get('portal/check/lost/mail'),strtolower($user['usermail'])!=strtolower(kc_post('usermail'))),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/mail'),'<input class="k_in w250" type="text" name="usermail" value="'.htmlspecialchars(kc_post('usermail')).'" maxlength="32" />',$_array);

	//pass
	$_array=array(
		array('userpass',0,6,30),
		array('userpass',17,null,'userpass1'),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/pass').' (6-30)','<input class="k_in w150" type="password" name="userpass" id="userpass" maxlength="30" value="'.htmlspecialchars(kc_post('userpass')).'" />',$_array);
	//repass
	$s.=$king->htmForm($king->lang->get('portal/user/pass1'),'<input class="k_in w150" type="password" name="userpass1" id="userpass1" maxlength="30" value="'.htmlspecialchars(kc_post('userpass1')).'" />');

	$verify=new KC_Verify_class;
	$s.=$verify->Show();


	if($GLOBALS['ischeck']){

		$array=array();
		$salt=kc_random(6);
		$md5pass=md5($salt.kc_post('userpass'));
		$array['userpass']=$md5pass;
		$array['ksalt']=$salt;

		$userid=$king->db->update('%s_user',$array,"userid={$user['userid']}");

		//写Cookie
		$s=$king->user->userLogin($user['userid'],2592000);
		$king->user->delUserInfo($user['userid']);

		kc_ajax($king->lang->get('system/common/welcome'),$s."<p class=\"k_ok\">".$king->lang->get('portal/user/lostok')."</p>",0);
	}

	$but=kc_htm_a($king->lang->get('system/common/submit'),"{URL:'".$king->config('inst')."user/index.php',CMD:'lostpwd1',username:'$username',IS:1}");
	$height=$king->config('verifyopen') ? 250+$king->config('verifyheight') : 230;
	kc_ajax($king->lang->get('portal/user/name'),$s,$but,'',420,$height + $GLOBALS['check_num']*15);
}
/* ------>>> KingCMS for PHP <<<--------------------- */

function king_def(){
	global $king;

	$user=$king->user->access();

	$tmp=new KC_Template_class($king->config('templateuser','user'),$king->config('templatepath').'/inside/user/index.htm');
	$tmp->assign('userid',$king->user->userid);
	$tmp->assign('nav',$king->lang->get('user/title/account'));
	$tmp->assign('title',$user['username']);

	echo $tmp->output();

}

function king_edit(){
	global $king;

	$user=$king->user->access();

	$sql='nickname,realname,usertel,useraddress,userpost';

	if($GLOBALS['ismethod']){//POST过程
		$data=$_POST;
	}else{
		if($data=$king->db->getRows_one("select $sql from %s_user where userid=".$user['userid'])){
		}
	}
	$fields=explode(',',$sql);
	$data=kc_data($fields,$data);

	$s=$king->openForm('index.php?action=edit');
	//nickname
	$_array=array(
		array('nickname',0,0,15),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/nickname'),kc_htm_input('nickname',$data['nickname'],15,200),$_array);

	//realname
	$array=array(
		array('realname',0,2,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/realname'),kc_htm_input('realname',$data['realname'],30,100),$array);
	//usertel
	$array=array(
		array('usertel',0,6,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/tel'),kc_htm_input('usertel',$data['usertel'],30,200),$array);
	//useraddress
	$array=array(
		array('useraddress',0,5,250),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/address'),'<textarea cols="10" id="useraddress" name="useraddress" rows="3" class="k_in w400">'.htmlspecialchars($data['useraddress']).'</textarea>',$array);
	//userpost
	$array=array(
		array('userpost',0,6,6),
		array('userpost',2),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/post'),kc_htm_input('userpost',$data['userpost'],6,50),$array);
	$s.=$king->closeForm($king->lang->get('system/common/save'));


	if($GLOBALS['ischeck']){

		//删除用户信息
		$king->user->delUserInfo($king->user->userid);

		$array=array();
		$array_sql=explode(',',$sql);
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}

		$king->db->update('%s_user',$array,"userid={$user['userid']}");

		$array=array(
			'<a href="index.php">'.$king->lang->get('portal/user/return/uc').'</a>',
			'<a href="/">'.$king->lang->get('portal/user/return/home').'</a>',
			'<a href="index.php?action=edit">'.$king->lang->get('system/common/continueedit').'</a>',
		);
		$s=kc_htm_ol($king->lang->get('system/ok/save'),$array,'index.php');
	}

	$tmp=new KC_Template_class($king->config('templateuser','user'),$king->config('templatepath').'/inside/user/edit.htm');
	$tmp->assign('main',$s);
	$tmp->assign('userid',$king->user->userid);
	$tmp->assign('nav',$king->lang->get('user/title/edit'));
	$tmp->assign('title',$king->lang->get('user/title/edit'));

	echo $tmp->output();
}


function king_safe(){
	global $king;

	$user=$king->user->access();

	$sql='useranswer,userask';

	if($GLOBALS['ismethod']){//POST过程
		$data=$_POST;
	}else{
		if($data=$king->db->getRows_one("select $sql from %s_user where userid=".$user['userid'])){
		}
	}
	$fields=explode(',',$sql);
	$data=kc_data($fields,$data);

	$s=$king->openForm('index.php?action=safe');

	$_array=array(
		array('userask',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('user/label/ask'),kc_htm_input('userask',$data['userask'],30,200),$_array);

	$_array=array(
		array('useranswer',0,1,16),
	);
	$s.=$king->htmForm($king->lang->get('user/label/answer'),kc_htm_input('useranswer',$data['useranswer'],30,200),$_array);
	$s.=$king->closeForm($king->lang->get('system/common/save'));


	if($GLOBALS['ischeck']){

		//删除用户信息
		$king->user->delUserInfo($king->user->userid);

		$array=array();
		$array_sql=explode(',',$sql);
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}

		$king->db->update('%s_user',$array,"userid={$user['userid']}");

		$array=array(
			'<a href="index.php">'.$king->lang->get('portal/user/return/uc').'</a>',
			'<a href="/">'.$king->lang->get('portal/user/return/home').'</a>',
			'<a href="index.php?action=safe">'.$king->lang->get('system/common/continueedit').'</a>',
		);
		$s=kc_htm_ol($king->lang->get('system/ok/save'),$array,'index.php');
	}

	$tmp=new KC_Template_class($king->config('templateuser','user'),$king->config('templatepath').'/inside/user/safe.htm');
	$tmp->assign('main',$s);
	$tmp->assign('userid',$king->user->userid);
	$tmp->assign('nav',$king->lang->get('user/title/safe'));
	$tmp->assign('title',$king->lang->get('user/title/safe'));

	echo $tmp->output();
}
/*
编辑头像
*/
function king_head(){
	global $king;

	$s.='编辑头像';

	$tmp=new KC_Template_class($king->config('templateuser','user'),$king->config('templatepath').'/inside/user/head.htm');
	$tmp->assign('main',$s);
	$tmp->assign('userid',$king->user->userid);
	$tmp->assign('nav',$king->lang->get('user/title/head'));
	$tmp->assign('title',$king->lang->get('user/title/head'));

	echo $tmp->output();

}
/*
我的订单
*/
function king_orders(){
	global $king;

	$user=$king->user->access();

/*
	$s='';
	if($data=$king->db->getRows("select oid,ono,kname,nnumber,ntotal from %s_orders where userid={$user['userid']} order by oid desc")){

	}else{
		$s='<p class="k_err">暂时没有订单!</p>';
	}
*/

	$tmp=new KC_Template_class($king->config('templateuser','user'),$king->config('templatepath').'/inside/user/orders.htm');
//	$tmp->assign('main',$s);
	$tmp->assign('userid',$king->user->userid);
	$tmp->assign('nav',$king->lang->get('user/title/orders'));
	$tmp->assign('title',$king->lang->get('user/title/orders'));

	echo $tmp->output();
}

/*
显示订单
*/
function king_orders_show(){
	global $king;

	$user=$king->user->access();

	$oid=kc_get('oid',2,1);//attach来传递oid参数
//kc_error($king->config('templateorders'));
	$tmp=new KC_Template_class($king->config('templateorders','portal'),$king->config('templatepath').'/inside/user/orders_show.htm');

	$tmp->assign('oid',$oid);
	$tmp->assign('userid',$king->user->userid);
	$tmp->assign('nav',$king->lang->get('portal/title/ordersshow'));
	$tmp->assign('title',$king->lang->get('portal/title/ordersshow'));

	echo $tmp->output();
}



?>