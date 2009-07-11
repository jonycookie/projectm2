<?php
define('IN_ADMIN',true);
require_once('global.php');
require_once('require/function_admin.php');

start();
$tplpath = 'admin';
$logfile = D_P.'data/cache/admin_record.php';
$admin_file = $_SERVER['PHP_SELF'];
if(!file_exists($logfile)){
	writeover($logfile,"<?php die;?>\n");
}

$adminjob = GetGP('adminjob');
$adminjob = Char_cv($adminjob);
list($admin_name,$admin_password) = explode("\t",GetCookie('Adminuser'));
if(!$admin_name) {
	InitGP(array("admin_name","admin_password","fancy"));
	if($fancy==$sys['hash']) {
		$admin_name = Char_cv($admin_name);
		$admin_password =  Char_cv($admin_password);
	}
}
if(!$admin_name || $adminjob=='login'){
	$login_fail = array();
	$login_fail = F_L_count($logfile,2000);
	$L_left = 9-$login_fail['count_F'];
	$L_T = $login_fail['L_T']+1200-$timestamp;
	if($L_left<0 && $L_T>0){
		Cookie('Adminuser','',0);
		Showmsg('login_fail');
	}
	require_once(R_P.'admin/login.php');
	adminbottom();
}

$admindb = checkAdmin($admin_name,$admin_password);
$admin_uid = $admindb['uid'];
$_GET['verify']  && PostCheck($_GET['verify']);
//根据权限生成菜单
require_once GetLang('menus');
$menu_father = array();
$menu_child  = array();
$menu_allow  = array('home','top','left','main','tree'); //初始化的几个操作，框架以及其框架页面
foreach ($menus as $key=>$menu){
	$menu_father[$key]=$menu['root'];
	$menu_child[$key]=array();
	foreach ($menu as $k=>$v){
		if($k=='root'){
			continue;
		}elseif (($k == 'set_admin' || $k == 'set_creator') && $admin_name!=$manager){
			continue;
		}elseif(in_array($k,$admindb['priv']) || $admin_name==$manager){ //创始人拥有所有权限
			$menu_child[$key][$k]=$v;
			$menu_allow[]=$k;
		}
	}
	if(count($menu_child[$key])==0){
		unset($menu_father[$key]);
		unset($menu_child[$key]);
	}
}

//write log
if(!in_array($adminjob,array('top','left','main'))){
	$new_record  = '';
	$_postdata	 = $_POST ? PostLog($_POST) : '';
	$record_name = str_replace('|','&#124;',Char_cv($admin_name));
	$record_URI	 = str_replace('|','&#124;',Char_cv($REQUEST_URI));
	$new_record = "|$record_name||$record_URI|$onlineip|$timestamp|$_postdata|\n";
	writeover($logfile,$new_record,"ab");
}

if(empty($adminjob)){
	require_once PrintEot('frame');
	adminbottom(0);
}elseif($adminjob=='left'){
	$nav = GetGP('nav','G');
	require_once PrintEot('header');
	require_once PrintEot('left');
	adminbottom(0);
}elseif ($adminjob=='top'){
	require_once PrintEot('header');
	require_once PrintEot('top');
	adminbottom(0);
}elseif($adminjob=='faq'){
	require_once (R_P."admin/faq.php");
}else{
	(strpos($adminjob,'.') || strpos($adminjob,'/')) && Showmsg("bad_request");
	$basename = $admin_file."?adminjob=$adminjob";
	!file_exists(R_P."admin/$adminjob.php") && Showmsg('undefined_action');
	!in_array($adminjob,$menu_allow) && Showmsg('privilege');
	require_once Pcv(R_P."admin/$adminjob.php");
}
?>