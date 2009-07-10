<?php
/***************************************************
*	install.php - installation of PHPwind		   *
*	Author: Fengyu,Yuling						   *
*	PHPwind (http://www.phpwind.net)			   *
***************************************************/
define('IN_EXT',true);
define("IN_CMS",true);
error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
set_magic_quotes_runtime(0);
if(!@ini_get('register_globals') || !get_magic_quotes_gpc()){
	@extract($_POST,EXTR_SKIP);
	@extract($_GET,EXTR_SKIP);
}
!$_POST && $_POST=array();
!$_GET && $_GET=array();
foreach($_POST as $_key=>$_value){
	!ereg("^\_",$_key) && $$_key=$_POST[$_key];
}
foreach($_GET as $_key=>$_value){
	!ereg("^\_",$_key) && $$_key=$_GET[$_key];
}

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$basename = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
$REQUEST_URI = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$bbsurl = 'http://'.$_SERVER['HTTP_HOST'].substr($REQUEST_URI,0,strrpos($REQUEST_URI,'/'));
$wind_version = '3.3';

eval('$__file__=__FILE__;');
define('D_P',$__file__ ? dirname($__file__).'/' :	'./');
define('R_P',D_P);
define('E_P',R_P.'extensions/advert/');
ob_start();
include(R_P.'lang/install_lang.php');
require_once(R_P.'lang/header.htm');
file_exists(D_P.'data/install.lock') && Promptmsg('have_file');
$stepmsg = $backmsg = $input = $log = $gojs = $gourl = '';
$systitle = $lang['title_install'];
$syslogo = 'install';
if ($step) {
	$stepmsg = $lang['step_'.$step];
	$stepleft = $lang['step_'.$step.'_left'];
	$stepright = $lang['step_'.$step.'_right'];
}
$steptitle = $step;
$footer = false;
if(!$step){
	$footer = true;
	$lang['log_install'] = str_replace('{#basename}',$basename,$lang['log_install']);
//	$lang['log_partner'] = @file("http://u.phpwind.com/install/partner.php?url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]");
//	$lang['log_partner'] = implode('',$lang['log_partner']);
//	$lang['log_partner'] = PostHost("http://u.phpwind.com/install/partner.php?url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]");
	$wind_licence= readover(R_P.'licence.txt');
	$wind_licence = str_replace('  ', '&nbsp; ', nl2br($wind_licence));
} elseif ($step == 1) {
	$wind_licence = str_replace(array('  ',"\n"),array('&nbsp; ','<br />'),readover('licence.txt'));
	writeover(R_P.'data/log1.txt',$lang['success_1']);
} elseif($step==2){

	//$language='wind';
	//$error=language($language);

	include(D_P.'data/sql_config.php');
	$check=1;
	$correct='......<font class=r>OK</font>';
	$incorrect=$lang['777_test'];
	$uncorrect=$lang['no_file'];
	$w_check=array(
		'data',
		'data/sql_config.php',
		'data/cache',
		'data/comment',
		'data/js',
		'data/rss',
		'data/sql',
		'data/tpl_cache',
		'template/user',
		'attachment',
		'attachment/temp',
		'attachment/s',
		'www',
		'script/cms',
	);

	$msg = array();
	foreach ($w_check as $filename) {
		!file_exists(R_P.$filename) && Promptmsg('error_unfind');
		!is_writable(R_P.$filename) && Promptmsg('error_777');
		$msg[] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_2']);
	}
	$msg = implode("\n",$msg);
	writeover(R_P.'data/log2.txt',$msg);
} elseif($step==3){
	@set_time_limit(0);
	!$manager_email && Promptmsg('error_nothing');
	$input = "<input type=\"hidden\" name=\"manager_email\" value=\"$manager_email\">";
	if ($_POST['from']!='prompt') {
		if (!$dbhost || !$dbuser || !$dbpw || !$dbname || !$PW || !$manager || !$manager_pwd) {
			Promptmsg('error_nothing');
		}
		if ($manager_pwd !== $manager_ckpwd) {
			Promptmsg('error_ckpwd');
		}
		$manager_pwd = md5($manager_pwd);
		$charset = str_replace('-','',$lang['db_charset']);
		$writetofile=
"<?php
/**
* $lang[dbinfo]
*/
\$dbhost\t\t=\t'$dbhost';\t\t// $lang[dbhost]
\$dbuser\t\t=\t'$dbuser';\t\t// $lang[dbuser]
\$dbpw\t\t=\t'$dbpw';\t\t// $lang[dbpw]
\$dbname\t\t=\t'$dbname';\t\t// $lang[dbname]
\$database\t=\t'mysql';\t\t// $lang[database]
\$_pre\t\t=\t'$PW';\t\t// $lang[dbpre]
\$pconnect\t=\t'0';\t\t//$lang[dbpconnect]

/*
$lang[dbcharset]
*/
\$charset\t\t=\t\t'$charset';

/**
* $lang[ma_info]
*/
\$manager\t\t=\t\t'$manager';\t\t//$lang[dbmanagername]
\$manager_pwd\t=\t\t'$manager_pwd';\n//$lang[dbmanagerpwd]
".'?>';
		writeover(D_P.'data/sql_config.php',$writetofile);
		include(D_P.'data/sql_config.php');
		include(R_P.'require/class_db.php');
		$db = new DB($dbhost, $dbuser, $dbpw, '', $charset, $pconnect);
		mysql_error() && Promptmsg('error_canconection');
		if(!@mysql_select_db($dbname,$db->linkId)) {
			if(mysql_get_server_info() > '4.1' && $charset){
				mysql_query("CREATE DATABASE $dbname DEFAULT CHARACTER SET $charset");
			}else{
				mysql_query("CREATE DATABASE $dbname");
			}
			mysql_error() && Promptmsg('error_nodatabase');
		}
		$db->select_db($dbname);
		$query=$db->query("SHOW TABLES LIKE '".$_pre."admin'");
//		while($TABLE=$db->fetch_array($query,MYSQL_NUM)){
//			$D_exists=$TABLE[0]==$_pre.'admin' ? 1 : 0;
//		}
		while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
			$rt[0]==$PW.'admin' && Promptmsg('have_install',3);
		}
	}else {
		require R_P.'data/sql_config.php';
		require Pcv(R_P.'require/class_db.php');
		$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$charset,$pconnect);
	}
	include(R_P.'require/class_cache.php');
	$content=readover(R_P."lang/install_wind.sql");
	$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
	$writearray = array($lang['success_3']);
	creat_table($content);
	$timestamp	= time();
	$t			= getdate($timestamp+8*3600);
	$tdtime		= (floor($timestamp/3600)-$t['hours'])*3600;
	$writepwd	= md5($password);
	$db->update("INSERT INTO `cms_admin` (`uid`, `username`, `password`, `logintime`, `ip`, `priv`, `email`, `loginfail`) VALUES (1, '$manager', '$manager_pwd', $timestamp, '', '', '$adminemail', 0)");
	require_once(R_P.'require/chinese.php');
	require_once(R_P.'require/class_xml.php');
	$uninstalldb = extlist();
	$writearray[] = $lang['success_3_2'];
	$writearray = implode("\n",$writearray);
	writeover(R_P.'data/log3.txt',$writearray);
//	$lang['have_install']=str_replace('$dbname',$dbname,$lang['have_install']);
} elseif($step==4){
	include(D_P.'data/sql_config.php');
	include(R_P.'require/class_db.php');
	include(R_P.'require/class_cache.php');
	$s_url = rawurlencode($_SERVER['HTTP_HOST']);
	$db = new DB($dbhost, $dbuser, $dbpw, $dbname,$charset, $pconnect);
	$db_hash=confuse();
	if(!($REQUEST_URI=$_SERVER['REQUEST_URI'])){
		$REQUEST_URI=$_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	}
	$wwwurl='http://'.$_SERVER['HTTP_HOST'].substr($REQUEST_URI,0,strrpos($REQUEST_URI,'/'));
	$db->update("REPLACE INTO `cms_config` VALUES ('db_title', 'CMS v3.3', '');");
	$db->update("REPLACE INTO `cms_config` VALUES ('db_url', '$wwwurl', '')");
	$db->update("REPLACE INTO `cms_config` VALUES ('db_contact', 'mailto:admin@admin.com', '')");
	$db->update("REPLACE INTO `cms_config` VALUES ('db_hash', '$db_hash', '')");
	$db->update($lang['ext_sql']);
	ext_cache();
	$cache = new Cache();
	$cache->update();
	include_once(D_P.'data/cache/config.php');
	$cache->sql();
	include_once(D_P.'data/cache/cate.php');
	foreach ($catedb as $cid=>$cateinfo){
		$cache->singleCate($cid);
	}
	if(!is_writeable(R_P.'install.php')){
		$unlinkerror='<tr><td align=left class=c align=middle colSpan=2>&nbsp;&nbsp;&nbsp;&nbsp;'.$lang['del_install'].'</td></tr>';
	}
	$writearray[] = $lang['success_5_2'];
	$writearray = implode('<wind>',$writearray);
	for ($i=1;$i<4;$i++) {
		$log .= readover(R_P."data/log$i.txt")."\n";
	}
	$log = str_replace("\n",'<wind>',$log).$writearray;
}elseif ($step == 'finish') {
	writeover(D_P.'data/install.lock');
	for ($i=1;$i<4;$i++) {
		@unlink(D_P."data/log$i.txt");
	}
	if (!is_writeable($basename)) {
		$lang['success_install'] .= "<br /><small><font color=\"red\">$lang[error_delinstall]</font></small>";
	}
	$lang['success_install'] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_install']);
	require(R_P.'lang/install.htm');
	@unlink($basename);
	footer();
}

require(R_P.'lang/install.htm');footer();

function creat_table($content) {
	global $db,$installinfo,$_pre,$lang,$charset,$writearray;

	$sql=explode("\n",$content);
	$query='';
	foreach($sql as $key => $value){
		$value=trim($value);
		if(!$value || $value[0]=='#') continue;
		if(eregi("\;$",$value)){
			$query.=$value;
			if(eregi("^CREATE",$query)){
				$name=substr($query,13,strpos($query,'(')-13);
				$c_name=str_replace('cms_',$_pre,$name);
				$installinfo.='<font color="#0000EE">'.$lang['creat_table'].'</font>'.$c_name.' ... <font color="#0000EE">'.$lang['success'].'</font><br>';

				$extra = substr(strrchr($query,')'),1);
				$tabtype = substr(strchr($extra,'='),1);
				$tabtype = substr($tabtype, 0, strpos($tabtype,strpos($extra,' ') ? ' ' : ';'));

				$query = str_replace($extra,'',$query);
				$tablename = trim(substr($query,0,strpos($query,'(')));
				$tablename = substr($tablename,strrpos($tablename,' ')+1);
				$writearray[] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_3_1']);
				if($db->server_info() > '4.1'){
					$extra = $charset ? "ENGINE=$tabtype DEFAULT CHARSET=$charset;" : "ENGINE=$tabtype;";
				}else{
					$extra = "TYPE=$tabtype;";
				}
				$query .= $extra;
			}
			$db->query($query);
			$query='';
		} else{
			$query.=$value;
		}
	}
}

function confuse(){
	$rand='0123%^&*45ICV%^&*B6789qazw~!@#$sxedcrikolpQWER%^&*TYUNM';
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<10;$i++){
		$code.=$rand[mt_rand(0,strlen($rand))];
	}
	return $code;
}

function readover($filename,$method="rb"){
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function writeover($filename,$data,$method="rb+"){
	@touch($filename);
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_EX);
		fputs($handle,$data);
		if($method=="rb+") ftruncate($handle,strlen($data));
		fclose($handle);
	}
}
function GetLang($filename){
	global $sys;
	empty($sys['lang']) && $sys['lang']='utf-8';
	return R_P."lang/$sys[lang]/$filename.php";
}
function Promptmsg($msg,$tostep=null){
	@extract($GLOBALS, EXTR_SKIP);
	require(R_P.'lang/install_lang.php');
	$lang[$msg] && $msg = $lang[$msg];
	$msg = preg_replace("/{#(.+?)}/eis",'$\\1',$msg);
	$url = $backurl = 'javascript:history.go(-1);';
	$backmsg = !empty($tostep) ? $stepleft : '';
	if (!$backmsg) {
		$lang['last'] = $lang['back'];
		@unlink("log$step.txt");
	} else {
		$url = "document.getElementById('install').submit();";
	}
	require(R_P.'lang/promptmsg.htm');
	footer();
}
function footer(){
	require_once(R_P.'lang/footer.htm');
	$output = trim(str_replace(array('<!--<!---->','<!---->',"\r",substr(R_P,0,-1)),'',ob_get_contents()),"\n");
	ob_end_clean();
	ob_start();
	echo $output;
	exit;
}
function Pcv($filename,$ifcheck=1){
	$tmpname = strtolower($filename);
	if (strpos($tmpname,'http://')!==false || ($ifcheck && strpos($tmpname,'..')!==false)) {
		exit('Forbidden');
	}
	return $filename;
}
function adminmsg(){
}
function language($language,$tplpath=''){
	if($tplpath){
		$tpl_w=$tplpath;
		$tpl_a='cp_'.$tplpath;
	} else{
		$tpl_w='wind';
		$tpl_a='admin';
	}
	$error=0;
	if(!is_dir(R_P."lang/$language")){
		$error=1;
	}
	if(!is_dir(R_P."template/$tpl_w")){
		if(!@mkdir("./template/$tpl_w")){
			$error=2;
		}
		@chmod(R_P."template/$tpl_w",0777);
	}
	if(!is_dir(R_P."template/$tpl_a")){
		if(!@mkdir(R_P."template/$tpl_a")){
			$error=2;
		}
		@chmod(R_P."template/$tpl_a",0777);
	}
	$lang=array(
				'lang_action.php',		'lang_bbscode.php',
				'lang_email.php',		'lang_masigle.php',
				'lang_msg.php',			'lang_post.php',
				'lang_refreshto.php',	'lang_sort.php',
				'lang_toollog.php',		'lang_log.php',
				'lang_writemsg.php',	'lang_wap.php',
			);
	$cp_lang=array(
				'cp_lang_all.php',		'cp_lang_cpmsg.php',
				'cp_lang_left.php',		'cp_lang_rightset.php',
			);
	foreach($lang as $key=>$value){
		writeover(R_P."template/$tpl_w/$value",readover(R_P."lang/$language/$value"));
		@chmod(R_P."template/$tpl_w/$value",0777);
	}
	foreach($cp_lang as $key=>$value){
		writeover(R_P."template/$tpl_a/$value",readover(R_P."lang/$language/$value"));
		@chmod(R_P."template/$tpl_a/$value",0777);
	}

	include_once(R_P."lang/$language/all_lang.php");
	$dir=opendir(R_P."lang/$language/wind/");
	while($file=readdir($dir)){
		if(eregi("\.htm$",$file)){
			$content=readover(R_P."lang/$language/wind/$file");
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			writeover(R_P."template/$tpl_w/$file",$content);
			@chmod(R_P."template/$tpl_w/$file",0777);
		}
	}

	$dir=opendir(R_P."lang/$language/admin/");
	while($file=readdir($dir)){
		if(eregi("\.htm$",$file)){
			$content=readover(R_P."lang/$language/admin/$file");
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			writeover(R_P."template/$tpl_a/$file",$content);
			@chmod(R_P."template/$tpl_a/$file",0777);
		}
	}

	$dir=opendir(R_P."lang/$language/");
	while($file=readdir($dir)){
		if(eregi("\.js$",$file)){
			$content=readover(R_P."lang/$language/$file");
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			writeover(R_P."data/$file",$content);
			@chmod(R_P."data/$file",0777);
		}
	}
	return $error;
}

function generatestr($len) {
	mt_srand((double)microtime() * 1000000);
    $keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars)-1;
	$str = '';
	for ($i=0;$i<$len;$i++){
		$str .= $keychars[mt_rand(0,$maxlen)];
	}
	return substr(md5($str.time().$_SERVER["HTTP_USER_AGENT"].$GLOBALS['db_hash']),0,$len);
}
function SitStrCode($string,$key,$action='ENCODE'){
	$string	= $action == 'ENCODE' ? $string : base64_decode($string);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($string); $i++){
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$code = $action == 'DECODE' ? $code : str_replace('=','',base64_encode($code));
	return $code;
}

function HtmlConvert(&$array){
	if(is_array($array)){
		foreach($array as $key => $value){
			if(!is_array($value)){
				$array[$key]=htmlspecialchars($value);
			}else{
				HtmlConvert($array[$key]);
			}
		}
	} else{
		$array=htmlspecialchars($array);
	}
}

function extlist(){
	$extlist = array();
	if ($fp = opendir(R_P.'extensions')) {
		$infodb = array();
		while (($extdir = readdir($fp))) {
			if (strpos($extdir,'.')===false) {
				$infodb = getInfo($extdir);
				!$infodb['name'] && $infodb['name'] = $extdir;
				$infodb['dir'] = $extdir;
				$extlist[] = $infodb;
			}
		}
		closedir($fp);
	}
	return $extlist;
}

function getInfo($extdir){
	global $lang;
	$infodb = array();
	$XMLDoc = new XMLDoc();
	$chs = new Chinese("utf-8",$lang['db_charset']);
	$phpversion = array();
	$phpversion = explode('.',PHP_VERSION);
	$phpversion = array_shift($phpversion);
	if($XMLDoc->LoadFromFile(R_P."extensions/$extdir/info.xml")){
		$XMLDoc->parse();
		$element = $XMLDoc->GetDocumentElement();
		$child = $element->GetChild();
		foreach($child as $val){
			if($phpversion<5) {
				$infodb[$val->GetTagName()] = $val->GetData();
			}else {
				$infodb[$val->GetTagName()] = $chs->Convert($val->GetData());
			}
		}
	}
	return $infodb;
}

function openfile($filename){
	$filedata=readover($filename);
	$filedata=str_replace("\n","\n<:wind:>",$filedata);
	$filedb=explode("<:wind:>",$filedata);
	$count=count($filedb);
	if($filedb[$count-1]==''||$filedb[$count-1]=="\r"){unset($filedb[$count-1]);}
	if(empty($filedb)){$filedb[0]="";}
	return $filedb;
}

function pw_var_export($input,$f = 1,$t = null) {
	$output = '';
	if(is_array($input)){
		$output .= "array(\n";
		foreach($input as $key => $value){
			$output .= $t."\t".pw_var_export($key,$f,$t."\t").' => '.pw_var_export($value,$f,$t."\t");
			$output .= ",\n";
		}
		$output .= $t.')';
	} elseif(is_int($input) || is_double($input)){
		$output .= "$input";
	} elseif(is_string($input) && strlen($input)>0){
		$output .= $f ? "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'" : "'$input'";
	} elseif(is_bool($input)){
		$output .= $input ? 'true' : 'false';
	} else{
		$output .= 'NULL';
	}
	return $output;
}

function db_cv($array){
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key]=str_replace(array("\\","'"),array("\\\\","\'"),$value);
		}
	}
	return $array;
}

function key_cv($key){
	$key = str_replace(
	array(';','\\','/','(',')','$'),
	'',
	$key
	);
	return $key;
}

function htmlchars_decode($str){
	$encode = array('&amp;','&quot;','&#039;','&lt;','&gt;');
	$decode = array('&','"','\'','<','>');
	$str = str_replace($encode,$decode,$str);
	return $str;
}

function ext_cache(){
	AdvertJsCache();
	include(R_P.'extensions/nav/include/cache.class.php');
	navCache::cache();
	include(R_P.'extensions/wordfilter/include/cache.class.php');
	wdfCache::cache();
}

function Char_cv($msg){
	$msg = str_replace('&amp;','&',$msg);
	$msg = str_replace('&nbsp;',' ',$msg);
	$msg = str_replace('"','&quot;',$msg);
	$msg = str_replace("'",'&#039;',$msg);
	$msg = str_replace("<","&lt;",$msg);
	$msg = str_replace(">","&gt;",$msg);
	$msg = str_replace("\t","   &nbsp;  &nbsp;",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);
	return $msg;
}

function AdvertJsCache($pid){
	global $db;
	include(R_P.'extensions/advert/include/cache.class.php');
	$timestamp = time();
	$advinfo = $adpinfo = $type = array();
	$type = array('img'=>1,'flash'=>2,'txt'=>3,'code'=>4,'page'=>5);
	$sqladd='';
	$pid && $sqladd = "WHERE pid='$pid'";
	$prs = $db->query("SELECT * FROM cms_adposition $sqladd");
	while($adpinfo = $db->fetch_array($prs)){
		$adpinfo['setting'] = unserialize($adpinfo['setting']);
		$ars = $db->query("SELECT * FROM cms_advert WHERE pid='$adpinfo[pid]' AND endtime > '$timestamp'");
		$advinfo='';
		while($art = $db->fetch_array($ars)){
			$art['cid'] = ','.$art['cid'].',';
			$art['type'] = $type[$art['type']];
			$art['config'] = unserialize($art['config']);
			$art['intro'] = str_replace("\r\n",'\n',addslashes($art['intro']));
			//$art['intro'] = addslashes($art['intro']);
			$advinfo[] = $art;
		}
		advCache::jsCache($adpinfo,$advinfo);
	}
}
function PostHost($host,$data='',$method='GET',$showagent=null,$port=null){
	//Copyright (c) 2003-06 PHPWind
	$parse = @parse_url($host);
	if (empty($parse)) return false;
	if ((int)$port>0) {
		$parse['port'] = $port;
	} elseif (!$parse['port']) {
		$parse['port'] = '80';
	}
	$parse['host'] = str_replace(array('http://','https://'),array('','ssl://'),"$parse[scheme]://").$parse['host'];
	if (!$fp=@fsockopen($parse['host'],$parse['port'],$errnum,$errstr,30)) {
		return false;
	}
	$method = strtoupper($method);
	$wlength = $wdata = $responseText = '';
	$parse['path'] = str_replace(array('\\','//'),'/',$parse['path'])."?$parse[query]";
	if ($method=='GET') {
		$separator = $parse['query'] ? '&' : '';
		substr($data,0,1)=='&' && $data = substr($data,1);
		$parse['path'] .= $separator.$data;
	} elseif ($method=='POST') {
		$wlength = "Content-length: ".strlen($data)."\r\n";
		$wdata = $data;
	}
	$write = "$method $parse[path] HTTP/1.1\r\nHost: $parse[host]\r\nContent-type: application/x-www-form-urlencoded\r\n{$wlength}Connection: close\r\n\r\n$wdata";
	@fwrite($fp,$write);
	while ($data = @fread($fp, 4096)) {
		$responseText .= $data;
	}
	@fclose($fp);
	empty($showagent) && $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
	return $responseText;
}
?>