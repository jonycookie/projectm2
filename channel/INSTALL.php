<?php

define('KC_INDEX',True);

require_once 'global.php';

/* ======= >>> KingCMS <<< ========================== *

 +   @License      http://www.KingCMS.com/license/    +

 +   @Link         http://www.KingCMS.com/            +

 +   @E-Mail       KingCMS(a)Gmail.com                +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== */

//king_ajax_del
function king_ajax_delete(){
	global $king;
	if(file_exists('INSTALL.php')){
		if(unlink('INSTALL.php')){
			kc_ajax('','',0,'parent.location=\'system/login.php\';');
		}else{
			kc_error($king->lang->get('system/error/unlink'));
		}
	}else{
		kc_error($king->lang->get('system/error/notfile'));
	}
}

//king_ajax_language
function king_ajax_language(){
	setcookie('language',kc_post('lang'),time()+864000000,'/');
	kc_ajax('','',0,'parent.location=\'INSTALL.php\';');
}

function king_ajax_config(){
	global $king;
	//dbtype
	$dbtype=kc_post('dbtype');
	if(!in_array($dbtype,array('mysql','sqlite'))){
		kc_error($king->lang->get('system/install/dbtypeerr'));
	}
	//licensed
	$license=kc_post('license');
	if($license!=1){
		kc_error($king->lang->get('system/install/licenseerr'));
	}

	$host=kc_post('host');
	$data=kc_post('data');
	$user=kc_post('user');
	$pass=kc_post('pass');

	$sqlitedata=kc_post('sqlitedata');
	//验证
	if($dbtype=='mysql'){
		//host
		if(!kc_validate($host,'/^[A-Za-z0-9\.\:\/]+$/')){
			kc_error($king->lang->get('system/install/ckhost'));
		}
		//data
		if(!kc_validate($data,'/^[A-Za-z0-9\-\_]+$/')){
			kc_error($king->lang->get('system/install/ckdata'));
		}
		//user
		if(!kc_validate($user,'/^[A-Za-z0-9\-\_]+$/')){
			kc_error($king->lang->get('system/install/ckuser'));
		}		
	}else{
		//sqlitedata
		if(!kc_validate($sqlitedata,'/^[A-Za-z0-9\-\_\.]+$/')){
			kc_error($king->lang->get('system/install/ckdata'));
		}
	}
	
	//pre
	$pre=kc_post('pre');
	if(!kc_validate($pre,'/^[A-Za-z0-9\_]+$/')){
		kc_error($king->lang->get('system/install/ckpre'));
	}
	//preadmin
	$preadmin=kc_post('preadmin');
	if(!kc_validate($preadmin,'/^[A-Za-z0-9\_]+$/')){
		kc_error($king->lang->get('system/install/ckpreadmin'));
	}

	//adminname
	$adminname=kc_get('adminname',1,1);
	if(strlen($adminname)<2||strlen($adminname)>12){
		kc_error($king->lang->get('system/install/ckadminname'));
	}
	//adminpass
	$adminpass=kc_get('adminpass',0,1);
	if(strlen($adminpass)<6||strlen($adminname)>30){
		kc_error($king->lang->get('system/install/ckadminpass'));
	}
	//cache
	$cache=kc_post('cache');
	if(!kc_validate($cache,'/^[A-Za-z0-9\_]+$/')){
		kc_error($king->lang->get('system/install/ckcache'));
	}
	//inst
	$inst=kc_post('inst');
	//timediff
	$timediff=kc_get('timediff',2,1);

	//debug
	$debug=kc_post('debug')==1?'True':'False';
	//isdelete
	$isdelete=kc_post('isdelete')==1?1:0;

	$s=kc_f_get_contents('config.php');

	$s=preg_replace("%(define\('DB_TYPE',')([A-Za-z]+)('\))%s","\${1}{$dbtype}\${3}",$s);
	$s=preg_replace("%(define\('DB_PRE',')([A-Za-z0-9\_]*)('\))%s","\${1}$pre\${3}",$s);
	$s=preg_replace("%(define\('KC_DB_ADMIN',')([A-Za-z0-9\_]*)('\))%s","\${1}$preadmin\${3}",$s);
	//sqlite
	$s=preg_replace("%(define\('DB_SQLITE',')([A-Za-z0-9\-\_\.\/]+)('\))%s","\${1}$sqlitedata\${3}",$s);
	//mysql
	$s=preg_replace("%(define\('DB_HOST',')([A-Za-z0-9\.\:\/]+)('\))%s","\${1}{$host}\${3}",$s);
	$s=preg_replace("%(define\('DB_DATA',')([A-Za-z0-9\-\_]+)('\))%s","\${1}$data\${3}",$s);
	$s=preg_replace("%(define\('DB_USER',')([A-Za-z0-9\-\_]+)('\))%s","\${1}$user\${3}",$s);
	$s=preg_replace("%(define\('DB_PASS',')([^']*)('\))%s","\${1}$pass\${3}",$s);

	$s=preg_replace("%(define\('PATH_CACHE',')([A-Za-z0-9\_]*)('\))%s","\${1}$cache\${3}",$s);
	$s=preg_replace("%(define\('DEBUG',)(True|False)(\))%s","\${1}$debug\${3}",$s);

	if(kc_f_put_contents('config.php',$s)){//写入成功
		$js="\$.kc_ajax('{CMD:\'install\',adminname:\'$adminname\',adminpass:\'$adminpass\',timediff:\'$timediff\',inst:\'$inst\',isdelete:\'$isdelete\'}')";
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/install/crtdb')."</p>","<a href=\"javascript:;\">".$king->lang->get('system/common/cancel')."</a>",$js);
	}else{
		kc_error($king->lang->get('system/install/puterror'));
	}

	//写config.php,并输出ajax执行程序,进入下一步install
}

function king_ajax_install(){
	global $king;

	$king->db->createDB();

	//_caption
	$sql='kpath char(100) not null,
	kmodule char(50) not null';

	$king->db->createTable('%s_system_caption',$sql,'cid');

	//_system
	$sql='cid int(6) not null default 0,
	isshow tinyint(1) not null default 1,
	issys tinyint(1) not null default 1,
	kname char(50) UNIQUE not null,
	norder int not null default 0,
	kmodule char(50) not null,
	kvalue text null,
	ntype tinyint(1) not null default 0,
	nvalidate tinyint(1) not null default 0,
	nsizemin int(8) not null default 0,
	nsizemax int(8) not null default 0,
	koption text null,
	nstylewidth smallint(4) not null default 0,
	nstyleheight smallint(4) not null default 0,
	khelp char(100) null';

	$king->db->createTable('%s_system',$sql,'kid');

	if(!$king->db->getRows_one('SELECT * FROM %s_system')){
		$i=1;
		$array=array(
			array(
				'kname'=>'dbver',
				'isshow'=>0,
				'kmodule'=>'system',
				'kvalue'=>'1.0',
				'norder'=>$i++,
			),
			array(
				'kname'=>'instdate',
				'isshow'=>0,
				'kmodule'=>'system',
				'kvalue'=>time()+(kc_post('timediff')*3600),
				'norder'=>$i++,
			),
			array(
				'kname'=>'key',
				'isshow'=>0,
				'kmodule'=>'system',
				'kvalue'=>kc_random(32),
				'norder'=>$i++,
			),
			array(
				'kname'=>'version',
				'isshow'=>0,
				'kmodule'=>'system',
				'kvalue'=>'1.0',
				'norder'=>$i++,
			),
			array(
				'kname'=>'info',
				'isshow'=>0,
				'kmodule'=>'system',
				'kvalue'=>'<span style="font:12px Arial">Powered by <a href="http://www.kingcms.com/" target="_blank" style="font:12px Verdana"><strong>King</strong>CMS</a></span>',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'system','kpath'=>'basic'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'switch',
				'kmodule'=>'system',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|True'.NL.'0|False',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'sitename',
				'kmodule'=>'system',
				'kvalue'=>'KingCMS',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>200,
				'koption'=>'',
				'nstylewidth'=>400,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'siteurl',
				'kmodule'=>'system',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>200,
				'koption'=>'',
				'nstylewidth'=>400,
				'nstyleheight'=>0,
				'khelp'=>'help/siteurl',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'beian',
				'kmodule'=>'system',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'htmlframe1',
				'kmodule'=>'system',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Table'.NL.'0|DIV(Div+Label)',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'htmlframe0',
				'kmodule'=>'system',
				'kvalue'=>'0',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Table'.NL.'0|DIV(Div+Label)',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){//basic
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'system','kpath'=>'system'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'inst',
				'kmodule'=>'system',
				'kvalue'=>kc_post('inst'),
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'file',
				'kmodule'=>'system',
				'kvalue'=>'index.html',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'timediff',
				'kmodule'=>'system',
				'kvalue'=>kc_post('timediff'),
				'ntype'=>1,
				'nvalidate'=>22,
				'nsizemin'=>1,
				'nsizemax'=>11,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'proptime',
				'kmodule'=>'system',
				'kvalue'=>'0.7',
				'ntype'=>1,
				'nvalidate'=>3,
				'nsizemin'=>1,
				'nsizemax'=>4,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'help/proptime',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uppath',
				'kmodule'=>'system',
				'kvalue'=>'upfiles',
				'ntype'=>1,
				'nvalidate'=>4,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'upimg',
				'kmodule'=>'system',
				'kvalue'=>'jpg|png|gif',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>200,
				'koption'=>'',
				'nstylewidth'=>400,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'upfile',
				'kmodule'=>'system',
				'kvalue'=>'pdf|doc|xls|zip|rar',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>200,
				'koption'=>'',
				'nstylewidth'=>400,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templatepath',
				'kmodule'=>'system',
				'kvalue'=>'template',
				'ntype'=>1,
				'nvalidate'=>4,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templateext',
				'kmodule'=>'system',
				'kvalue'=>'htm|html|shtml',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>200,
				'koption'=>'',
				'nstylewidth'=>400,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templatedefault',
				'kmodule'=>'system',
				'kvalue'=>'default.htm',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templatefiternote',
				'kmodule'=>'system',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'pidline',
				'kmodule'=>'system',
				'kvalue'=>'-',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'help/pidline',
				'norder'=>$i++,
			),

			array(
				'cid'=>$cid,
				'kname'=>'gzencode',
				'kmodule'=>'system',
				'kvalue'=>'0',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'help/gzencode',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'lockip',
				'kmodule'=>'system',
				'kvalue'=>'',
				'ntype'=>2,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>400,
				'nstyleheight'=>120,
				'khelp'=>'help/lockip',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'system','kpath'=>'cache'));
		$array=array(
		array(
				'cid'=>$cid,
				'kname'=>'cachetime',
				'kmodule'=>'system',
				'kvalue'=>'300',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>11,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'cachetip',
				'kmodule'=>'system',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){//rewrite
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'system','kpath'=>'rewrite'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'rewriteline',
				'kmodule'=>'system',
				'kvalue'=>'-',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'_'.NL.'/'.NL.'-',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'rewriteend',
				'kmodule'=>'system',
				'kvalue'=>'.html',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>10,
				'koption'=>'/'.NL.'.html'.NL.'.htm',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){//rewrite
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'system','kpath'=>'verify'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'verifyopen',
				'kmodule'=>'system',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'verifycontent',
				'kmodule'=>'system',
				'kvalue'=>'A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z',
				'ntype'=>2,
				'nvalidate'=>0,
				'nsizemin'=>10,
				'nsizemax'=>9999,
				'nstylewidth'=>400,
				'nstyleheight'=>120,
				'khelp'=>'help/verifycontent',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'verifytime',
				'kmodule'=>'system',
				'kvalue'=>'30',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>3,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'verifywidth',
				'kmodule'=>'system',
				'kvalue'=>'110',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>3,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'verifyheight',
				'kmodule'=>'system',
				'kvalue'=>'40',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>3,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'verifysize',
				'kmodule'=>'system',
				'kvalue'=>'25',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>3,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'verifynum',
				'kmodule'=>'system',
				'kvalue'=>'4',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'4'.NL.'6'.NL.'8',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),

		);
		foreach($array as $val){//rewrite
			$king->db->insert('%s_system',$val);
		}

	}

	//_admin
	$sql='adminname char(12) UNIQUE not null,
	adminpass char(32) not null,
	adminlevel text not null,
	adminlanguage char(30) not null,
	admineditor char(100) null,
	admincount smallint not null default 0,
	adminmode tinyint(1) not null default 1,
	adminskins char(50) not null default \'default\',
	adminlogin char(100),
	siteurl char(100),
	isdelete tinyint(1) not null default 0,
	admindate int(10) not null default 0';
	//admindiymenu text null,

	$king->db->createTable('%a_admin',$sql,'adminid');

	if(!$king->db->getRows_one('SELECT * FROM %a_admin where adminname=\'admin\';')){
		$_array=array(
			'adminname'=>kc_post('adminname'),
			'adminpass'=>md5(kc_post('adminpass')),
			'adminlevel'=>'admin',
			'adminlanguage'=>'zh-cn',
			'admineditor'=>'xheditor',
			'adminmode'=>2,
			'admindate'=>time(),
			'adminlogin'=>'manage.php',
		);
		$king->db->insert('%a_admin',$_array);
	}

	//_module
	$sql='kid1 int not null default 0,
	kname char(50) not null,
	kpath char(50) not null,
	islock tinyint(1) not null default 0,
	kdb text null,
	ndate int(10) not null default 0,
	ndbver smallint(3) not null default 100,
	norder int not null default 0,
	INDEX(islock)';

	$king->db->createTable('%s_module',$sql,'kid');

	//_log
	$sql='adminname char(12) not null,
	nip int(10) not null default 0,
	nlog tinyint(2) not null default 0,
	ktext char(100) null,
	ndate int(10) not null default 0';

	$king->db->createTable('%s_log',$sql,'kid');

	//_bot
	$sql='kname char(30) not null,
	kmark char(255) not null,
	ncount int not null default 0,
	nlastdate int(10) not null default 0,
	ndate int(10) not null default 0';

	$king->db->createTable('%s_bot',$sql,'kid');

	if(!$king->db->getRows_one('SELECT * FROM %s_bot')){
		$_array_bot=array(
			'Baidu'=>'Baiduspider+',
			'Google'=>'Googlebot',
			'Alexa'=>'ia_archiver',
			'Alexa'=>'IAArchiver',
			'ASPSeek'=>'ASPSeek',
			'Yahoo'=>'YahooSeeker',
			'Sohu'=>'sohu-search',
			'Yahoo'=>'help.yahoo.com/help/us/ysearch/slurp',
			'MSN'=>'MSN',
			'AOL'=>'Sqworm/2.9.81-BETA (beta_release; 20011102-760; i686-pc-linux-gnu',
		);
		foreach($_array_bot as $_key => $_value){
			$_array=array(
				'kname'=>$_key,
				'kmark'=>$_value,
			);
			$king->db->insert('%s_bot',$_array);
		}
	}

	//_Upfile
	$sql='kpath char(255) not null,
	ndate int(10) not null default 0,
	userid int not null default 0,
	adminid int not null default 0,
	ntype tinyint(1) not null default 0,
	INDEX(userid),
	INDEX(adminid)';

	$king->db->createTable('%s_upfile',$sql,'kid');

	//_Lnk
	$sql='kname char(20) null,
	ktitle char(100) null,
	kpath char(100) null,
	konclick char(255) null,
	adminid int not null default 0,
	kimage char(100) null,
	isblank tinyint(1) not null default 0,
	norder int not null default 0,

	isflo tinyint(1) not null default 0,
	ntop smallint(4) not null default 50,
	nleft smallint(4) not null default 300,

	INDEX(adminid)';

	$king->db->createTable('%s_lnk',$sql,'kid');

	//_Message
	$sql='adminname char(12) not null,
	kmsg char(100) not null,
	ndate int(10) not null default 0,
	adminid int not null default 0,
	issys tinyint(1) not null default 0,
	klink char(100) null,
	INDEX(adminid),
	INDEX(issys)';

	$king->db->createTable('%s_message',$sql,'kid');

	if(!$king->db->getRows_one('SELECT * FROM %s_message')){
		$_array=array(
			'kmsg'=>$king->lang->get('system/install/think'),
			'adminname'=>'Sin.CS',
			'ndate'=>time(),
			'issys'=>1,
			'klink'=>'http://www.kingcms.com/',
		);
		$king->db->insert('%s_message',$_array);
	}

	//_Event
	$sql='ntype int(6) not null,
	kmsg text null,
	kfile char(100) not null,
	kurl char(255) null,
	nline int(5) not null default 0,
	ndate int(10) not null default 0';

	$king->db->createTable('%s_event',$sql,'kid');

	if(kc_post('isdelete')&&file_exists('INSTALL.php')) unlink('INSTALL.php');

	kc_ajax('OK','<p class="k_ok">'.$king->lang->get('system/install/instok').'</p>',"<a href=\"system/login.php\">".$king->lang->get('system/common/login')."</a>");

}

function king_ajax_repass(){
	global $king;

	//adminname
	$array=array(
		array('readminname',0,2,12),
		array('readminname',1),
	);
	$s="<p class=\"k_htm\"><label>".$king->lang->get('system/admin/name')."</label><input class=\"k_in w150\" type=\"text\" value=\"".kc_post('readminname')."\" id=\"readminname\" name=\"readminname\"/>";
	$s.=kc_check($array);
	$s.="</p>";
	//adminpass
	$array=array(
		array('readminpass',0,6,30),
	);
	$s.="<p class=\"k_htm\"><label>".$king->lang->get('system/admin/pass')."</label><input class=\"k_in w150\" type=\"text\" value=\"".kc_post('readminpass')."\" id=\"readminpass\" name=\"readminpass\"/>";
	$s.=kc_check($array);
	$s.="</p>";
	//but
	$but="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:'repass',IS:1}\">".$king->lang->get('system/common/save')."</a>";

	if($GLOBALS['ischeck']){//POST过程或新添加的过程

		if(!$king->db->getRows_one("SELECT * FROM %a_admin where adminname='".kc_post('readminname')."';")){
				$king->db->insert('%a_admin',array('adminname'=>kc_post('readminname'),'adminpass'=>md5(kc_post('readminpass')),'adminlevel'=>'admin','adminlanguage'=>'zh-cn','admineditor'=>'fckeditor','admindate'=>time(),'adminlogin'=>'../system/manage.php'));
		}else{
			$king->db->update('%a_admin',array("adminpass"=>md5(kc_post('readminpass')),'adminlevel'=>'admin'),"adminname='".kc_post('readminname')."'");
		}
		kc_ajax('OK','<p class="k_ok">'.$king->lang->get('system/ok/save').'</p>');
	}
	kc_ajax($king->lang->get('system/install/repwd'),$s,$but,null,250,120+$GLOBALS['check_num']*15);
}

/* ------>>> actions <<<----------------------------- */

function king_def(){
	global $king;

	$sel_array=array(
		'mysql'=>'MySQL',
		'sqlite'=>'SQLite'
	);

	$phpself=$_SERVER['PHP_SELF'];
	$inst=substr($phpself,0,strlen($phpself)-11);//安装目录
	$select_type=kc_htm_radio('dbtype',$sel_array,'sqlite');//数据库类型

	$array_dirs=array('config.php','system/js');
	$array_func=array('mysql_connect','file_get_contents','file_put_contents','simplexml_load_file');//,'fsockopen'

$s="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<title>".$king->lang->get('system/install/title')."</title>
<link href=\"system/skins/default/style.css\" rel=\"stylesheet\" type=\"text/css\" />
<style type=\"text/css\">
.k_table_form{font-size:12px;}
.k_table_form th{width:200px;color:#000;font-weight:normal;text-indent:5px;padding:5px;}
.k_table_form td{text-indent:5px;}
</style>
<meta name=\"generator\" content=\"KingCMS\"/>
<script type=\"text/javascript\" src=\"system/js/jquery.js\"></script>
<script type=\"text/javascript\" src=\"system/js/jquery.kc.js\"></script>
<script type=\"text/javascript\" src=\"system/skins/default/fun.js\"></script>
<script type=\"text/javascript\">
jQuery(function(\$){

	\$(\"#k_dbtype_mysql , #k_dbtype_sqlite\").click(function(){\$.ck_radio(this)});

	\$.ck_radio=function(obj){
		if(\$(obj).attr('id')=='k_dbtype_mysql'){
			\$('.mysql').show();
			\$('.sqlite').hide();

		}else{
			\$('.sqlite').show();
			\$('.mysql').hide();
		
		}
	}

});

</script>
</head>
<body>
<div id=\"k_ajax\"></div>
<div id=\"top\">
	<a id=\"logo\" href=\"http://www.kingcms.com\" target=\"_blank\"><img alt=\"KingCMS\" src=\"system/skins/default/logo.gif\"/></a>
	<ul class=\"k_menu\">
		<li><a href=\"INSTALL.php\">".$king->lang->get('system/common/install')."</a></li>
		<li><a href=\"javascript:;\">".$king->lang->get('system/common/language')."</a>

				<ul>";


	//language
	$array=kc_f_getdir('system/language','xml');
	$array=array_map('kc_f_name',$array);
	$_language=kc_cookie('language');
	foreach($array as $val){
		$s.='<li><a href="javascript:;" class="k_ajax" rel="{CMD:\'language\',lang:\''.$val.'\'}">';
		if($_language==$val)
			$s.='&bull;&nbsp;';
		$s.=kc_getlang($val).'</a></li>';
	}

	$s.="</ul>
		</li>
	</ul>
</div>
<div id=\"main\">


<table class=\"w0\"><tr><td style=\"vertical-align:top;\" class=\"w10\">
	<form name=\"form_install\" id=\"form_install\">
	<h3 class=\"caption\">".$king->lang->get('system/install/db')."</h3>
	<table class=\"k_table_form\" cellspacing=\"0\">
		<tbody><tr><th>".$king->lang->get('system/install/dbtype')."</th><td>{$select_type}</td></tr></tbody>
		<tr><th>".$king->lang->get('system/install/pre')."</th><td><input id=\"pre\" name=\"pre\" class=\"k_in w200\" value=\"king\"/></td></tr>
		<tr><th>".$king->lang->get('system/install/preadmin')."</th><td><input id=\"preadmin\" name=\"preadmin\" class=\"k_in w200\" value=\"kc\"/></td></tr>

		<tr class=\"mysql none\"><th>".$king->lang->get('system/install/dbhost')."</th><td><input id=\"host\" name=\"host\" class=\"k_in w200\" value=\"localhost\"/></td></tr>
		<tr class=\"mysql none\"><th>".$king->lang->get('system/install/dbdata')."</th><td><input id=\"data\" name=\"data\" class=\"k_in w200\" value=\"test\"/></td></tr>
		<tr class=\"mysql none\"><th>".$king->lang->get('system/install/dbuser')."</th><td><input id=\"user\" name=\"user\" class=\"k_in w200\" value=\"root\"/></td></tr>
		<tr class=\"mysql none\"><th>".$king->lang->get('system/install/dbpass')."</th><td><input id=\"pass\" name=\"pass\" class=\"k_in w200\" value=\"\"/></td></tr>

		<tr class=\"sqlite\"><th>".$king->lang->get('system/install/dbfile')."</th><td><input id=\"sqlitedata\" name=\"sqlitedata\" class=\"k_in w200\" value=\"".kc_random(12).".db3\"/></td></tr>
	</table>

	<h3 class=\"caption\">".$king->lang->get('system/install/admin')."</h3>
	<table class=\"k_table_form\" cellspacing=\"0\">
		<tbody><tr><th>".$king->lang->get('system/install/adminname')."</th><td><input id=\"adminname\" name=\"adminname\" class=\"k_in w200\" value=\"admin\"/></td></tr></tbody>
		<tr><th>".$king->lang->get('system/install/adminpass')."</th><td><input id=\"adminpass\" name=\"adminpass\" class=\"k_in w200\" value=\"admin888\"/></td></tr>
	</table>

	<h3 class=\"caption\">".$king->lang->get('system/level/config')."</h3>
	<table class=\"k_table_form\" cellspacing=\"0\">
		<tbody><tr><th>".$king->lang->get('system/install/cache')."</th><td><input id=\"cache\" name=\"cache\" class=\"k_in w200\" value=\"_cache\"/></td></tr></tbody>

		<tr><th>".$king->lang->get('system/const/inst')."</th><td><input id=\"inst\" name=\"inst\" class=\"k_in w100\" value=\"{$inst}\"/></td></tr>
		<tr><th>".$king->lang->get('system/install/timediff')."</th><td><input id=\"timediff\" name=\"timediff\" class=\"k_in w100\"/></td></tr>
		<tr><th>".$king->lang->get('system/install/debug')."</th><td><input id=\"debug\" value=\"1\" name=\"debug\" type=\"checkbox\" checked=\"checked\"/><label for=\"debug\">".$king->lang->get('system/install/opendebug')."</label></td></tr>
	</table>

	<script type=\"text/javascript\">
	var dateObj = new Date();
	var timediff=".date('G',0).";
	$('#timediff').val(timediff);
	</script>

	<p>
		<input value=\"1\" id=\"license\" name=\"license\" type=\"checkbox\"/><label for=\"license\">".$king->lang->get('system/install/readlicense')."</label>
		[<a href=\"http://www.kingcms.com/license/\" target=\"_blank\">".$king->lang->get('system/install/license')."</a>]
	</p>
		<input value=\"1\" id=\"isdelete\" name=\"isdelete\" type=\"checkbox\" checked=\"checked\"/><label for=\"isdelete\">".$king->lang->get('system/install/isdelete')."</label>
	<p>

	</p>

	<p class=\"k_submit\">

		<input value=\"".$king->lang->get('system/common/install')."[S]\" class=\"k_ajax big\" rel=\"{CMD:'config',FORM:'form_install'}\" type=\"button\" accesskey=\"s\"/>

	</p>
	</form>
</td><td class=\"w1\" style=\"vertical-align:top;\"></td><td>

	<h3 class=\"caption\">".$king->lang->get('system/skin/sys')."</h3>
	<table class=\"k_table_list\" cellspacing=\"0\">
	<tr><th class=\"w10\">".$king->lang->get('system/skin/obj')."</th><th class=\"w5\">".$king->lang->get('system/skin/required')."</th><th class=\"w5\">".$king->lang->get('system/skin/this')."</th></tr>";
	$s.='<tr><td>'.$king->lang->get('system/skin/os').'</td><td>ALL</td><td>'.PHP_OS.'</td></tr>';
	$s.='<tr><td>'.$king->lang->get('system/skin/phpver').'</td><td>5.1.0+</td><td>'.PHP_VERSION.'</td></tr>';
	if(function_exists('disk_free_space'))
		$s.='<tr><td>'.$king->lang->get('system/skin/diskspace').'</td><td>＞2 Mb</td><td>'.kc_f_size(disk_free_space('./')).'</td></tr>';

	$s.="</table>
	<h3 class=\"caption\">".$king->lang->get('system/skin/writeinfo')."</h3>
	<table class=\"k_table_list\" cellspacing=\"0\">
	<tr><th class=\"w10\">".$king->lang->get('system/skin/filedir')."</th><th class=\"w5\">".$king->lang->get('system/skin/required')."</th><th class=\"w5wgfv -k07-87;[yu'pbv9io/h9;'99999\">".$king->lang->get('system/skin/this')."</td></tr>";

	foreach($array_dirs as $val){
		$s.='<tr><td>'.$val.'</td><td>'.$king->lang->get('system/skin/write/w1').'</td><td>'.$king->lang->get('system/skin/write/w'.(is_writable(ROOT.$val)?1:0)).'</td></tr>';
	}

	$s.="</table>
	<h3 class=\"caption\">".$king->lang->get('system/skin/func')."</h3>
	<table class=\"k_table_list\" cellspacing=\"0\">
	<tr><th class=\"w10\">".$king->lang->get('system/skin/funs')."</th><th class=\"w5\">".$king->lang->get('system/skin/required')."</th><th class=\"w5\">".$king->lang->get('system/skin/this')."</th></tr>";
	foreach($array_func as $val){
		$s.='<tr><td>'.$val.'()</td><td>'.$king->lang->get('system/skin/fun/f1').'</td><td>'.$king->lang->get('system/skin/fun/f'.(function_exists($val)?1:0)).'</td></tr>';
	}

	$s.="</table>
	<h3 class=\"caption\">".$king->lang->get('system/skin/other')."</h3>
	<table class=\"k_table_list\" cellspacing=\"0\">
	";
	$s.="<tr><th class=\"w10 red\">".$king->lang->get('system/skin/obj')."</th><th class=\"w5 red\">".$king->lang->get('system/skin/advice')."</th><th class=\"w5 red\">".$king->lang->get('system/skin/this')."</th></tr>
	<tr><td>".$king->lang->get('system/skin/browser')."</td><td>IE 7.0</td><td>".kc_browser()."</td></tr>
	<tr><td>".$king->lang->get('system/skin/safemode')."</td><td>--</td><td>".$king->lang->get('system/skin/open/o'.(ini_get('safe_mode')?1:0))."</td></tr>
	<tr><td>".$king->lang->get('system/skin/maxetime')."</td><td>--</td><td>".ini_get('max_execution_time')."s</td></tr>
	</table>

	<p><img class=\"f6 os\" src=\"system/images/white.gif\"/><a href=\"http://www.kingcms.com/\" class=\"k_ajax\" rel=\"{CMD:'repass',METHOD:'GET'}\">".$king->lang->get('system/install/resetpass')."</a></p>
	<p><img class=\"j2 os\" src=\"system/images/white.gif\"/><a href=\"http://www.kingcms.com/\" class=\"k_ajax\" rel=\"{CMD:'delete'}\">".$king->lang->get('system/install/delfile')."</a></p>
	<p><img class=\"n1 os\" src=\"system/images/white.gif\"/><a href=\"system/login.php\">".$king->lang->get('system/install/login')."</a></p>

</td></tr></table>

</div>
</body>
</html>";


	exit($s);

}

?>

