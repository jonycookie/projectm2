<?php require_once '../global.php';

/**
 * 添加用户
 */
function king_ajax_adduser(){
	global $king;
	$king->access('user_edt');

	$fields=array('username','userpass','usermail','kremark');

	if($GLOBALS['ismethod']){//POST过程
		$data=kc_data($fields);
	}else{
		$data=array();
		$data['userpass']=kc_random(10,4);
		$data=kc_data($fields,$data);
	}


/*
	foreach($datas as $val){
		if(isset($data[$val])){

		}
	}
*/
	//username
	$_array=array(
		array('username',0,3,15),
		array('username',14,$king->lang->get('portal/check/reg/u-1'),array('*','\\',':','?','<','>','|',';',',','\'','!','~','$','#','@','^','(',')','{','}','=','+','%','/')),
		array('username',12,$king->lang->get('portal/check/reg/u-3'),$king->db->getRows_one("select userid from %s_user where username='".$king->db->escape(kc_post('username'))."';")),
	);
	if($king->user->isuc && $GLOBALS['ismethod']){//有提交操作的时候才做验证
		$ucheck=uc_user_checkname(kc_post('username'));
		$_array[]=array('username',12,$king->lang->get('system/check/reg/u'.$ucheck),$ucheck!=1);
	}
	$s=$king->htmForm($king->lang->get('portal/user/name').' (3-15)',kc_htm_input('username',$data['username'],15,160),$_array);
	//pass
	$_array=array(
		array('userpass',0,6,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/user/pass').' (6-30)',kc_htm_input('userpass',$data['userpass'],30,150),$_array);//'<input class="k_in w150" type="text" name="userpass" maxlength="30" />'
	//usermail
	$_array=array(
		array('usermail',0,6,32),
		array('usermail',5,$king->lang->get('portal/check/reg/u-4')),
		array('usermail',12,$king->lang->get('portal/check/reg/u-6'),$king->db->getRows_one("select userid from %s_user where usermail='".$king->db->escape(kc_post('usermail'))."';")),
	);
	if($king->user->isuc && $GLOBALS['ismethod']){//有提交操作的时候才做验证
		$ucheck=uc_user_checkemail(kc_post('usermail'));
		$_array[]=array('usermail',12,$king->lang->get('portal/check/reg/u'.$ucheck),$ucheck!=1);

	}
	$s.=$king->htmForm($king->lang->get('portal/user/mail'),kc_htm_input('usermail',$data['usermail'],32,250),$_array);
	//kremark
	$s.=$king->htmForm($king->lang->get('user/group/remark'),'<textarea class="k_in w350" cols="130" rows="7" name="kremark" id="kremark">'.htmlspecialchars($data['kremark']).'</textarea>');

	if($GLOBALS['ischeck']){
		//先提交到ucenter后再提交到本地数据库
		if($king->user->isuc){
			$uid=uc_user_register($data['username'],$data['userpass'],$data['usermail']);
			if($uid<0)
				error($king->lang->get('portal/check/reg/u'.$uid));
			if($uid==0)
				error($king->lang->get('portal/error/connect'));//发出连接错误
		}else{
			$kc_uid=$king->db->neworder('%s_user',null,'uid');//若没有UC链接的时候uid自动递增
		}

		$array_sql=array('username','usermail','kremark');
		$array=array();
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}
		$salt=kc_random(6);
		$md5pass=md5($salt.$data['userpass']);
		$array['userpass']=$md5pass;
		$array['uid']=$kc_uid ? $kc_uid : $uid;
		$array['ksalt']=$salt;
		$array['regdate']=time();
		$userid=$king->db->insert('%s_user',$array);

		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/save')."</p>",1);

	}

	kc_ajax($king->lang->get('user/title/adduser'),$s,kc_htm_a($king->lang->get('system/common/add'),"{CMD:'adduser',VAL:'username,userpass,usermail',IS:1}"),'',400,320+$GLOBALS['check_num']*15);
}
/**
 * 删除用户
 */
function king_ajax_delete_user(){
	global $king;
	$king->access('user_delete');

	$list=kc_getlist();
	$king->db->update('%s_user',array('isdelete'=>1),"userid in ($list)");

	$king->log(6,"USERID:$list");
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}
/**
 * 编辑用户组
 */
function king_ajax_edtgroup(){
	global $king;
	$king->access('user_group_edt');
	$gid=kc_get('gid',2);

	if($GLOBALS['ismethod']){//POST过程
		$data=$_POST;
	}else{
		if($gid){
			if(!$data=$king->db->getRows_one("select kname,kremark from %s_usergroup where gid=$gid"))
				kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
		}else{
			$data=array();
		}
	}
	$data=kc_data(array('kname','kremark'),$data);

	$gid=kc_get('gid',2);
	$array=array(
		array('kname',0,1,30),
	);
	$s=$king->htmForm($king->lang->get('user/group/name').' (1-30)',kc_htm_input('kname',$data['kname'],30,200),$array);

	$array=array(
		array('kremark',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('user/group/remark'),'<textarea class="k_in w350" cols="130" rows="7" name="kremark" id="kremark">'.htmlspecialchars($data['kremark']).'</textarea>');

	$but='<a href="javascript:;" class="k_ajax" rel="{CMD:\'edtgroup\',gid:\''.$gid.'\',IS:1}">'.$king->lang->get('system/common/add').'</a>';

	if($GLOBALS['ischeck']){

		$array=array(
			'kname'=>$data['kname'],
			'kremark'=>$data['kremark'],
			);

		if($gid){
			$king->db->update('%s_usergroup',$array,"gid=$gid");
		}else{
			$array['norder']=$king->db->neworder('%s_usergroup');
			$king->db->insert('%s_usergroup',$array);
		}

		$king->cache->del('user/groups');

		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/save')."</p>",1);

	}

	kc_ajax($king->lang->get('user/group/'.($gid?'edt':'add')),$s,$but,'',400,220+$GLOBALS['check_num']*15);
}
/**
 * 删除用户组
 */
function king_ajax_delete_group(){
	global $king;
	$king->access('user_group_delete');
	$gid=kc_get('gid',2,1);

	if($king->db->getRows_one("select userid from %s_user where gid=$gid")){
		kc_error($king->lang->get('user/error/groupexist'));
	}else{
		$king->db->query("delete from %s_usergroup where gid=$gid");
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
	}

}
/**
 * 上下调整用户组
 */
function king_ajax_updown_group(){
	global $king;
	$king->access('user_group_edt');
	$kid=kc_get('kid',2,1);

	$king->db->updown('%s_usergroup',$kid,'',0,'gid');

}
function king_ajax_group_link(){
	global $king;
	$king->access('user_group_link');

	if($GLOBALS['ismethod']){//POST过程
		$data=kc_data(array('username'));
	}else{
		$data=kc_data(array('username'));
	}

	$gid=kc_get('gid',2,1);

	$array=array(
		array('username',0,3,15),
		array('username',12,$king->lang->get('user/check/notuser'),!($res=$king->db->getRows_one("select userid from %s_user where username='".$king->db->escape(kc_post('username'))."';"))),
	);
	$s=$king->htmForm($king->lang->get('user/list/username').' (1-30)',kc_htm_input('username',$data['username'],30,200),$array);

	$but='<a href="javascript:;" class="k_ajax" rel="{CMD:\'group_link\',gid:\''.$gid.'\',IS:1}">'.$king->lang->get('system/common/add').'</a>';

	if($GLOBALS['ischeck']){
		$king->db->update('%s_user',array('gid'=>$gid),"userid={$res['userid']}");
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/set')."</p>");
	}

	kc_ajax($king->lang->get('user/group/link'),$s,$but,'',320,75+$GLOBALS['check_num']*15);
}
function king_ajax_group_link_edt(){
	global $king;
	$king->access('user_group_link');
	$gid=kc_get('gid',2,1);
	$userid=kc_get('userid',2,1);

	$array=$king->user->getGroup();
	$_array=array(
		array('gid',2),
	);
	$s=$king->htmForm($king->lang->get('user/group/select'),kc_htm_select('gid',$array,$gid),$_array);

	$but='<a href="javascript:;" class="k_ajax" rel="{CMD:\'group_link_edt\',userid:'.$userid.',IS:1}">'.$king->lang->get('system/common/modify').'</a>';

	if($GLOBALS['ischeck']){
		$king->db->update('%s_user',array('gid'=>$gid),"userid=$userid");
		$king->user->delUserInfo($userid);
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/set')."</p>",1);
	}

	kc_ajax($king->lang->get('user/group/edtlink'),$s,$but,'',320,75);
}
function king_ajax_user_lock(){
	global $king;
	$king->access('user_edt');

	$is= CMD=='user_lock' ? 1:0;

	$list=kc_getlist();
	$king->db->update('%s_user',array('islock'=>$is),"userid in ($list)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/set')."</p>",1);
}
function king_ajax_user_unlock(){
	call_user_func('king_ajax_user_lock');
}

/* ------>>> KingCMS for PHP <<<--------------------- */

function king_def(){
	global $king;
	$king->access('user');


	$s='<p class="k_table_title">&nbsp;</p>';

	$s=$king->openForm('',$king->lang->get('user/caption/info'),'block_add');
	$s.='<tr><th>'.$king->lang->get('portal/user/count').'</th><td class="w200">'.$king->db->getRows_number('%s_user').'</td>';
	//今日注册用户
	$todaycount=$king->db->getRows_number('%s_user','regdate>'.(ceil(time()/86400-1)*86400));
	$s.='<th>'.$king->lang->get('user/list/todaycount').'</th><td class="w200">'.$todaycount.'</td>';
	$s.='<td><img src="../system/images/white.gif" class="os c6"/><a href="javascript:;" class="k_ajax" rel="{CMD:\'adduser\',METHOD:\'GET\'}">'.$king->lang->get('user/common/adduser').'</a></td></tr>';
	$s.=$king->closeForm('none');

	//用户组管理
	//如果用JavaScript方式来调用的话，和设置圆角的JavaScript代码有冲突。
	$s.=$king->openForm('',$king->lang->get('user/title/group'));
	$s.=$king->htmForm($king->lang->get('user/caption/group'),"<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:'edtgroup',METHOD:'GET'}\">".kc_icon('h4',$king->lang->get('system/common/add')).$king->lang->get('system/common/add')."</a>");
	if($res=$king->db->getRows("select gid,kname,kremark from %s_usergroup order by norder")){
		foreach($res as $rs){
			$manage="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:'edtgroup',METHOD:'GET',gid:{$rs['gid']}}\">".kc_icon('o6',$king->lang->get('system/common/del'))."</a>";
			$manage.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:'delete_group',gid:{$rs['gid']}}\">".kc_icon('g4',$king->lang->get('system/common/del'))."</a>";
			$manage.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:'group_link',METHOD:'GET',gid:{$rs['gid']}}\">".kc_icon('n9',$king->lang->get('user/group/link'))."</a>";
			$manage.="<em class=\"gray\">{$rs['kremark']}</em>";
			$s.=$king->htmForm($rs['kname'],$manage);
		}
	}
	$s.=$king->closeForm('none');
	/*
	$s.='<table class="k_table_form" cellspacing="0">';
	$s.='<tr><th>'.$king->lang->get('user/title/group').'</th><td>';
	$s.='<table class="k_side">';
	$s.='<tr><td class="w200">'.kc_icon('h4').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'edtgroup\',METHOD:\'GET\'}">'.$king->lang->get('user/group/add').'</a></td><td class="w100">&nbsp;</td><td>&nbsp;</td></tr>';

	$s.="<script>function ll(gid,kname,kremark){var s='<tr><td>'+$.kc_icon('a6')+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'edtgroup\',METHOD:\'GET\',gid:\''+gid+'\'}\">'+kname+'</a></td>';";
	$s.="s+='<td><a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete_group\',gid:\''+gid+'\'}\">'+$.kc_icon('g4','".$king->lang->get('system/common/del')."')+'</a>';";
	$s.="s+='<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'group_link\',METHOD:\'GET\',gid:\''+gid+'\'}\">'+$.kc_icon('n9','".$king->lang->get('user/group/link')."')+'</a>';";
	$s.="s+=$.kc_updown(gid,'updown_group')+'</td>';";
	$s.="s+='<td class=\"gray\">'+kremark+'</td></tr>';document.write(s)};";

	if($res=$king->db->getRows("select gid,kname,kremark from %s_usergroup order by norder")){
		foreach($res as $rs){
			$s.="ll({$rs['gid']},'{$rs['kname']}','{$rs['kremark']}');";
		}
	}
	$s.='</script></table>';
	$s.='</table>';
	*/
	$s.=$king->openForm('',$king->lang->get('user/caption/def'));

	//register
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/register').'</th>';
	$s.='<td><input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="#" class="k_user_register">'.$king->lang->get('user/common/register').'</a>').'"/>';
	$s.='<a href="#" class="k_user_register"><img class="os o4" src="../system/images/white.gif"/></a>AJAX'.kc_help('user/help/ajax');
	$s.='</td></tr></tbody>';
	//login
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/login').'</th><td>';
	$s.='<input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="#" class="k_user_login">'.$king->lang->get('user/common/login').'</a>').'"/>';
	$s.='<a href="#" class="k_user_login"><img class="os o4" src="../system/images/white.gif"/></a>AJAX<br/>';
	$s.='<input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="{config:system.inst/}user/login.php">'.$king->lang->get('user/common/login').'</a>').'"/>';
	$s.='<a href="login.php" target="_blank"><img class="os o5" src="../system/images/white.gif"/></a>HTML';
	$s.='</td></tr></tbody>';
	//center
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/center').'</th>';
	$s.='<td><input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="{config:system.inst/}user/index.php">'.$king->lang->get('user/common/center').'</a>').'"/>';
	$s.='<a href="index.php" target="_blank"><img class="os o5" src="../system/images/white.gif"/></a>HTML';
	$s.='</td></tr></tbody>';
	//lostpwd
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/lostpwd').'</th>';
	$s.='<td><input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="#" class="k_user_lostpwd">'.$king->lang->get('user/common/lostpwd').'</a>').'"/>';
	$s.='<a href="#" class="k_user_lostpwd"><img class="os o4" src="../system/images/white.gif"/></a>AJAX';
	$s.='</td></tr></tbody>';
	//resetpwd
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/resetpwd').'</th>';
	$s.='<td><input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="#" class="k_user_resetpwd">'.$king->lang->get('user/title/resetpwd').'</a>').'"/>';
	$s.='<a href="#" class="k_user_resetpwd"><img class="os o4" src="../system/images/white.gif"/></a>AJAX';
	$s.='</td></tr></tbody>';
	//edit
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/edit').'</th>';
	$s.='<td><input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="{config:system.inst/}user/index.php?action=edit">'.$king->lang->get('user/title/edit').'</a>').'"/>';
	$s.='<a href="index.php?action=edit" target="_blank"><img class="os o5" src="../system/images/white.gif"/></a>HTML';
	$s.='</td></tr></tbody>';
	//orders
	$s.='<tbody><tr><th>'.$king->lang->get('user/title/orders').'</th>';
	$s.='<td><input class="k_in w400" type="text" value="'.htmlspecialchars('<a href="{config:system.inst/}user/index.php?action=orders">'.$king->lang->get('user/title/orders').'</a>').'"/>';
	$s.='<a href="index.php?action=orders" target="_blank"><img class="os o5" src="../system/images/white.gif"/></a>HTML';
	$s.='</td></tr></tbody>';

	$s.=$king->closeForm('none');

	$left=array(
		''=>array(
			'href'=>'manage.php',
			'class'=>'sel',
			'ico'=>'a1',
			'title'=>$king->lang->get('user/title/center'),
		),
		'userlist'=>array(
			'href'=>'manage.php?action=userlist',
			'ico'=>'f6',
			'title'=>$king->lang->get('user/title/userlist'),
		),
		'log'=>array(
			'href'=>'manage.php?action=log',
			'ico'=>'e8',
			'title'=>$king->lang->get('user/title/log'),
		),
	);
	$right=array();
	if($king->acc('portal_list')){
		$right[]=array(
			'href'=>'../portal/manage.php',
			'title'=>$king->lang->get('portal/title/list'),
			'ico'=>'a1',
		);
	}
	$right[]=array(
		'href'=>'../system/manage.php?action=upfile&is=1',
		'ico'=>'m5',
		'title'=>$king->lang->get('system/title/upfile'),
	);


	$king->skin->output($king->lang->get('portal/title/user'),$left,$right,$s);
}

function king_userlist(){
	global $king;
	$king->access('user');

	$_sql="SELECT userid,uid,gid,username,usermail,lastlogindate,regdate,islock FROM %s_user where isdelete=0 order by userid";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$_cmd=array(
		$king->lang->get('system/common/del'),
		'delete_user'=>$king->lang->get('user/common/del'),
		$king->lang->get('system/common/setting'),
		'user_lock'=>$king->lang->get('system/common/lock'),
		'user_unlock'=>$king->lang->get('system/common/unlock'),
	);

	$_manage="'<a href=\"manage.php?action=edtuser&userid='+K[0]+'\">'+$.kc_icon('b6','".$king->lang->get('system/common/edit')."')+'</a>'";//ajax方式进行管理
	$_manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'group_link_edt\',METHOD:\'GET\',gid:\''+K[2]+'\',userid:\''+K[0]+'\'}\">'+$.kc_icon('n9','".$king->lang->get('user/group/edtlink')."')+'</a>'";
	$_manage.="+'<a class=\"k_ajax\" rel=\"{CMD:\'delete_user\',list:'+K[0]+'}\">'+$.kc_icon('d6','".$king->lang->get('system/common/del')."')+'</a>'";

	$_js=array(
		"$.kc_list(K[0],K[4],'',1,1,(K[3]==1?'b8':'c8'))",
		$_manage,
		"K[5]",
		"usergroup[K[2]]",
		"K[7]",
		"K[6]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist("manage.php?action=&pid=PID&rn=RN",$king->db->getRows_number('%s_user',"isdelete=0")));
	$s.=$king->tdList(array($king->lang->get('user/list/username'),'manage',$king->lang->get('user/list/mail'),$king->lang->get('user/group/owned'),$king->lang->get('user/list/regdate'),$king->lang->get('user/list/lastdate')),1);

	$usergroup=$king->user->getGroup();
	$s.=kc_js2array('usergroup',$usergroup);

	foreach($res as $rs){//td
		$s.=$king->tdList(array(
			$rs['userid'],
			$rs['uid'],
			$rs['gid'],
			$rs['islock'],
			addslashes(htmlspecialchars($rs['username'])),
			$rs['usermail'],
			kc_formatdate($rs['lastlogindate']),
			kc_formatdate($rs['regdate']),
		));
	}
	$s.=$king->closeList();

	$left=array(
		array(
			'href'=>'manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('user/title/center'),
		),
		array(
			'href'=>'manage.php?action=userlist',
			'class'=>'sel',
			'ico'=>'f6',
			'title'=>$king->lang->get('user/title/userlist')
		),
		array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'adduser\',METHOD:\'GET\'}',
			'ico'=>'c6',
			'title'=>$king->lang->get('system/common/add'),
		),
	);
	$right=array(
		'log'=>array(
			'href'=>'manage.php?action=log',
			'ico'=>'e8',
			'title'=>$king->lang->get('user/title/log'),
		),
		array(
			'href'=>'../portal/manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('portal/title/list'),
		),
	);

	$king->skin->output($king->lang->get('user/title/userlist'),$left,$right,$s);
}
function king_edtuser(){
	global $king;
	$king->access('user_edt');
	$userid=kc_get('userid',2,1);

	$sql='gid,userpoint,nickname,kremark,realname,usertel,useraddress,userpost';

	if($GLOBALS['ismethod']){//POST过程
		$data=$_POST;
	}else{
		if(!$data=$king->db->getRows_one("select uid,username,usermail,$sql from %s_user where userid=$userid"))
			kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
	$fields=explode(',',$sql.',userpass,userport');
	$data=kc_data($fields,$data);

	$s=$king->openForm('manage.php?action=edtuser',$king->lang->get('user/caption/basicinfo'));
/*
*/
	if($king->user->isuc){//ucenter的时候不允许编辑username
		//username
		$s.=$king->htmForm($king->lang->get('user/label/name'),$data['username']);
		$s.=kc_htm_hidden(array('username'=>$data['username']));
	}else{//非ucenter的时候，允许修改username或mail
		//username
		$_array=array(
			array('username',0,3,15),
			array('username',14,$king->lang->get('portal/check/reg/u-1'),array('*','\\',':','?','<','>','|',';',',','\'','!','~','$','#','@','^','(',')','{','}','=','+','%','/')),
			array('username',12,$king->lang->get('portal/check/reg/u-3'),$king->db->getRows_one("select userid from %s_user where username='".$king->db->escape(kc_post('username'))."' and userid<>$userid;")),
		);
		$s.=$king->htmForm($king->lang->get('user/label/name'),kc_htm_input('username',$data['username'],15,150),$_array,'username');
	}
	//mail
	$_array=array(
		array('usermail',0,6,32),
		array('usermail',5,$king->lang->get('portal/check/reg/u-4')),
		array('usermail',12,$king->lang->get('portal/check/reg/u-6'),$king->db->getRows_one("select userid from %s_user where usermail='".$king->db->escape(kc_post('usermail'))."' and userid<>$userid;")),
	);
	$s.=$king->htmForm($king->lang->get('user/label/mail'),kc_htm_input('usermail',$data['usermail'],32,250),$_array,'usermail');
	//pass
	if($data['userpass']){
		$_array=array(
			array('userpass',0,6,30),
		);
	}else{
		$_array=array();
	}
	$s.=$king->htmForm($king->lang->get('portal/user/pass').' (6-30)',kc_htm_input('userpass',$data['userpass'],30,150),$_array,'',kc_htm_setvalue_nl('userpass',kc_random(10,4).NL.kc_random(10,1).NL.kc_random(10)).kc_help('user/help/pass'));
	//'<input class="k_in w150" type="text" name="userpass" maxlength="30" />'

	//CAPTION
	$s.=$king->splitForm($king->lang->get('user/caption/userinfo'));

	//gid
	$array=$king->user->getGroup();
	$_array=array(
		array('gid',2),
	);
	//userpoint
	$_array=array(
		array('userpoint',2),
		array('userpoint',0,1,11),
	);
	$s.=$king->htmForm($king->lang->get('user/common/point'),kc_htm_input('userpoint',$data['userpoint'],11,250),$_array,'userpoint');
	$s.=$king->htmForm($king->lang->get('user/group/select'),kc_htm_select('gid',$array,$data['gid']),$_array);
	//nickname
	$_array=$data['nickname'] ? array(
		array('nickname',0,0,15),
	):array();
	$s.=$king->htmForm($king->lang->get('portal/user/nickname'),kc_htm_input('nickname',$data['nickname'],15,250),$_array,'nickname');
	//realname
	$array=$data['realname'] ? array(
		array('realname',0,2,30),
	):array();
	$s.=$king->htmForm($king->lang->get('portal/user/realname'),kc_htm_input('realname',$data['realname'],30,100),$array);
	//usertel
	$array=$data['usertel']? array(
		array('usertel',0,6,30),
	):array();
	$s.=$king->htmForm($king->lang->get('portal/orders/tel'),kc_htm_input('usertel',$data['usertel'],30,200),$array);
	//useraddress
	$array=$data['useraddress'] ? array(
		array('useraddress',0,5,250),
	):array();
	$s.=$king->htmForm($king->lang->get('portal/user/address'),'<textarea cols="10" id="useraddress" name="useraddress" rows="3" class="k_in w400">'.htmlspecialchars($data['useraddress']).'</textarea>',$array);
	//userpost
	$array=$data['userport'] ? array(
		array('userpost',0,6,6),
		array('userpost',2),
	):array();
	$s.=$king->htmForm($king->lang->get('portal/orders/post'),kc_htm_input('userpost',$data['userpost'],6,50),$array);


	//kremark
	$s.=$king->htmForm($king->lang->get('user/group/remark'),'<textarea class="k_in w400" cols="130" rows="7" name="kremark" id="kremark">'.htmlspecialchars($data['kremark']).'</textarea>');

	$s.=kc_htm_hidden(array('userid'=>$userid,'uid'=>$data['uid']));

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){

		if($king->user->isuc){
			$int=uc_user_edit($data['username'],'',$data['userpass'],$data['usermail'],1);
			if(!in_array($int,array(0,1))){
				kc_error($king->lang->get('portal/check/edit/u'.$int));
			}
			$_array=array();
		}else{
			$_array=array(
				'username'=>$data['username'],
			);
		}

		$_array['usermail']=$data['usermail'];
		if($data['userpass']){//当用户输入密码的时候
			$salt=kc_random(6);
			$md5pass=md5($salt.$data['userpass']);
			$_array['userpass']=$md5pass;
			$_array['ksalt']=$salt;
		}
		$array=explode(',',$sql);//array('gid','userpoint','nickname','kremark');
		foreach($array as $val){
			$_array[$val]=$data[$val];
		}

		$king->db->update('%s_user',$_array,"userid=$userid");

		//删除用户信息
		$king->user->delUserInfo($userid);

//		$king->cache->del('user/userid/'.$userid);

		kc_goto($king->lang->get('system/goto/isback'),'manage.php?action=edtuser&userid='.$userid,'manage.php?action=userlist');
	}

	$left=array(
		array(
			'href'=>'manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('user/title/center'),
		),
		array(
			'href'=>'manage.php?action=userlist',
			'ico'=>'a6',
			'title'=>$king->lang->get('system/common/list'),
		),
		array(
			'class'=>'k_ajax sel',
			'rel'=>'{CMD:\'adduser\',METHOD:\'GET\'}',
			'ico'=>'c6',
			'title'=>$king->lang->get('system/common/edit'),
		),
	);
	$right=array(
		array(
			'href'=>'../portal/manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('portal/title/list'),
		),
	);
	$king->skin->output($king->lang->get('user/title/edtuser'),$left,$right,$s);

}

function king_log(){
	global $king;
	$king->access('user_log');

	$s='';



	$left=array(
		array(
			'href'=>'manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('user/title/center'),
		),
		array(
			'href'=>'manage.php?action=userlist',
			'ico'=>'f6',
			'title'=>$king->lang->get('user/title/userlist'),
		),
		'log'=>array(
			'href'=>'manage.php?action=log',
			'ico'=>'e8',
			'title'=>$king->lang->get('user/title/log'),
		),
	);
	$right=array(
		array(
			'href'=>'../portal/manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('portal/title/list'),
		),
	);
	$king->skin->output($king->lang->get('user/title/edtuser'),$left,$right,$s);

}

?>