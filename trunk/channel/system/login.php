<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS(a)Gmail.com                *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

 //显示登录
//king_ajax_login
function king_ajax_login(){
	/**
	登录页面增加一个$act变量，做登录判断用。
	*/
	global $king;

	$adminname=kc_post('adminname');
	$adminpass=kc_post('adminpass');

	$s='<div class="k_login"><div><label>'.$king->lang->get('system/login/name').':</label><p><input type="text" class="k_in w150" id="adminname" name="adminname" value="'.htmlspecialchars($adminname).'" maxlength="12" />';
	$s.=kc_check(array(array('adminname',0,$king->lang->get('system/check/e0'),2,12),array('adminname',1,$king->lang->get('system/check/e1'))));
	$s.='</p></div>';

	$s.='<div><label>'.$king->lang->get('system/login/pass').':</label><p><input type="password" class="k_in w150" id="adminpass" name="adminpass" maxlength="30" />';
	$s.=kc_check(array(array('adminpass',0,$king->lang->get('system/check/e0'),6,30)));
	if($GLOBALS['ischeck']){
		$s.=kc_check(array(array('adminpass',12,$king->lang->get('system/login/check'),!king_ajax_login_check($adminname,$adminpass))));
	}
	$s.='</p></div>';

	//expire
	$array_select=array(
		0=>$king->lang->get('system/time/cookie'),
		86400=>$king->lang->get('system/time/oneday'),
		2592000=>$king->lang->get('system/time/jan'),
		15768000=>$king->lang->get('system/time/halfyear'),
		315360000=>$king->lang->get('system/time/forever'),
	);

	$s.='<div><label>'.$king->lang->get('portal/user/expire').':</label><p>'.kc_htm_select('expire',$array_select,86400).'</p></div></div>';


	$but=kc_htm_a($king->lang->get('system/common/login'),"{CMD:'login',IS:1}");
	$but.='<a href="javascript:;" title="'.$king->lang->get('system/common/close').'" class="k_close">'.$king->lang->get('system/common/close').'</a>';

//	$s.='</p>';

	if($GLOBALS['ischeck']){

		$_sql="select adminlogin from %a_admin where adminname='".$king->db->escape($adminname)."';";
		if($_res=$king->db->getRows_one($_sql)){
			$_location=$_res['adminlogin'];
		}
		kc_ajax('','',0,"parent.location='{$_location}'");
	}else{
		kc_ajax($king->lang->get('system/login/title'),$s,$but,'',420,220);
		kc_error($but);
	}
} //!king_ajax_login
 //验证管理员帐号
//king_ajax_login_check
function king_ajax_login_check($_name,$_pass){
	global $king;

	if(strlen($_pass)>0){

		$_md5pass=md5($_pass);
		$_sql="select adminname,adminlanguage,adminmode,adminskins from %a_admin where adminname='".$king->db->escape($_name)."' and adminpass='".$_md5pass."' and isdelete=0;";

		if($_res=$king->db->getRows_one($_sql)){

			header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
			setcookie('KingCMS_Admin',$_res['adminname']."\t".md5($_res['adminname'].$_md5pass),(kc_post('expire') ? time() + (int) kc_post('expire') : 0),'/');
			setcookie("language",$_res['adminlanguage'],time()+86400000,'/');

			//写管理员登陆信息
			$_array=array(
				'admindate' =>time(),
				'admincount'=>'[[admincount+1]]',
				);
			$king->db->update('%a_admin',$_array,"adminname='".$king->db->escape($_name)."'");

			$king->log(1,$_name);
			return True;
		}else{
			//写登陆错误log
			$king->log(2,$_name);

			return False;
		}
	}else{
		return False;
	}
} //!king_ajax_login_check
 //显示登录
//king_ajax_about
function king_ajax_about(){
	global $king;
	$s='<div id="k_about"><p><img src="../system/images/logo.png"/></p>';
	$s.='<p>KingCMS for PHP '.$king->devname.'</p>';
	$s.='<p>Version: '.$king->version.'</p>';///'.$king->lang->get('system/login/about').'
	$s.='<p>Copyright &copy; <a href="http://www.kingcms.com/" target="_blank">KingCMS.com</a></p>';
	$s.='<p>2004 - 2010</p>';
	$s.='</div>';
	kc_ajax($king->lang->get('system/common/about').'..',$s,0,'',240,290);
} //!king_ajax_about
 //退出登录
//king_ajax_logout
function king_ajax_logout(){
	global $king;

	$king->access(0);

	$king->cache->del('system/admin/'.$king->admin['adminname']);

	//写注销log
	list($_name,$_pass)=isset($_COOKIE['KingCMS_Admin']) ? kc_explode("\t",$_COOKIE['KingCMS_Admin'],2):array(NULL,NULL);

	$king->log(3,$_name);

	header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
	setcookie('KingCMS_Admin',$_name,-864000,'/');

	kc_ajax('','',0,'parent.location=\'../system/login.php\'');
}


/* ------>>> KingCMS for PHP <<<--------------------- */

function king_def(){
	global $king;

	$king->skin->setPath('system.login.htm');//设置为home模板
	$tip=is_file('../INSTALL.php')?"<table style=\"border:1px solid red;clear:both;margin:50px 0px;width:100%;line-height:40px;text-indent:30px;\"><tr><td><p class=\"red\">".$king->lang->get('system/error/install')."</p></td></tr></table>":'';

	$king->skin->output($king->lang->get('system/login/title'),null,null,'<script type="text/javascript">$.kc_ajax(\'{CMD:\\\'login\\\',METHOD:\\\'GET\\\'}\')</script>'.$tip);

}
?>