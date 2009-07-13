<?php
/**
 * This software is the proprietary information of IM2.com.
 * $Id: global.php 624 2008-06-03 09:48:16Z vcteam1 $
 *
 * @Copyright (c) 2003-08 IM2.com Corporation.
 */
error_reporting(E_ERROR | E_PARSE);
set_magic_quotes_runtime(0);
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');
$defined_vars = get_defined_vars();

foreach ($defined_vars as $_key => $_value) {
	if(!in_array($_key,array('GLOBALS','_POST','_GET','_COOKIE','_SERVER','_FILES'))){
		${$_key} = '';
		unset(${$_key});
	}
}
unset($_key,$_value,$defined_vars);
$t_array = explode(' ',microtime());
$P_S_T	 = $t_array[0] + $t_array[1];

$timestamp = $t_array[1];
define('IN_CMS',true);
define('D_P',__FILE__ ?	getdirname(__FILE__).'/' :	'./');
define('R_P',D_P);

if(!get_magic_quotes_gpc()){
	Add_S($_POST);
	Add_S($_GET);
	Add_S($_COOKIE);
}
Add_S($_FILES);

if($_SERVER['HTTP_X_FORWARDED_FOR']){
	$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}elseif($_SERVER['HTTP_CLIENT_IP']){
	$onlineip = $_SERVER['HTTP_CLIENT_IP'];
}else{
	$onlineip = $_SERVER['REMOTE_ADDR'];
}
$onlineip = preg_match("/^[\d]([\d\.]){5,13}[\d]$/", $onlineip) ? $onlineip : 'unknown';
!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
$REQUEST_URI  = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

include_once(D_P.'data/cache/config.php');
require_once(D_P.'data/sql_config.php');
require_once(R_P.'require/class_db.php');
include_once(D_P.'data/cache/nav.php');
$sys['cvtime'] != 0 && $timestamp += $sys['cvtime']*60;
$queryNum = 0;
$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$charset,$pconnect);
unset($dbhost,$dbuser,$dbpw,$dbname,$t_array);

$_OUTPUT		= '';
$wind_spend		= '';
$wind_version	= "3.3";
$wind_repair	= '';
$user_tplpath	= 'template/user'; //用户模板路径
$default_tplpath= $sys['default_tplpath']?$sys['default_tplpath']:"template/default";	//系统模板路径

if($sys['rewrite_dir'] && $sys['rewrite_ext']){
	$_SINIT	= array('..',')','<','=');
	$_SHAVE = array('&#46;&#46;','&#41;','&#60;','&#61;');
	$self_array = explode('-',$sys['rewrite_ext'] ? substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],$sys['rewrite_ext'])) : $_SERVER['QUERY_STRING']);
	$s_count = count($self_array);
	for($i=0;$i<$s_count;$i++){
		$_key	= $self_array[$i];
		$_value	= $self_array[++$i];
		CheckVar($_value,$_SINIT,$_SHAVE);
		$_GET[$_key] = addslashes(rawurldecode($_value));
	}
	$_SINIT=$_SHAVE=$self_array='';
	unset($_SINIT,$_SHAVE,$self_array);
}

$mid = (int)GetGP('mid');
$cid = (int)GetGP('cid');
$tid = (int)GetGP('tid');

if(in_array(SCR,array('index','list','view'))){
	require_once(D_P.'data/cache/cate.php');
	checkRefer();
}

//主要函数库

function start($charset=''){
	global $sys;
	!$charset && $charset=$sys['lang'];
	ob_start();
	$charset && @header("Content-Type: text/html; charset=$charset");
}

/**
 * 写Cookie
 *
 * @param string $ck_Var Cookie变量名
 * @param string $ck_Value 值
 * @param integer $ck_Time 保存时间
 */
function Cookie($ck_Var,$ck_Value,$ck_Time = 'F'){
	global $timestamp,$sys;
	$ck_Time = $ck_Time == 'F' ? $timestamp + 31536000 : ($ck_Value == '' && $ck_Time == 0 ? $timestamp - 31536000 : $ck_Time);
	$S		 = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
	$sys['ckdomain'] = '';
	$sys['ckpath'] = '/';
	setCookie(CookiePre().'_'.$ck_Var,$ck_Value,$ck_Time,$sys['ckpath'],$sys['ckdomain'],$S);
}

function PwStrtoTime($time){
	global $sys;
	return function_exists('date_default_timezone_set') ? strtotime($time) - $sys['timedf']*3600 : strtotime($time);
}

/**
 * 读Cookie
 *
 * @param string $Var
 * @return mixed
 */
function GetCookie($Var){
	return $_COOKIE[CookiePre().'_'.$Var];
}

function CookiePre(){
	global $sys;
	return substr(md5($sys['url']),0,5);
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

function Add_S(&$array){
	foreach($array as $key=>$value){
		if(!is_array($value)){
			$array[$key]=addslashes($value);
		}else{
			Add_S($array[$key]);
		}
	}
}

function Strip_S(&$array){
	foreach($array as $key=>$value){
		if(!is_array($value)){
			$array[$key]=stripslashes($value);
		}else{
			Strip_S($array[$key]);
		}
	}
}

function get_date($timestamp,$timeformat=''){
	global $sys;
	$date_show = $timeformat ? $timeformat : $sys['datefm'];
	$offset = $sys['timedf']=='111' ? 0 : $sys['timedf'];
	return gmdate($date_show,$timestamp+$offset*3600);
}

/**
 * 截取字符串,多编码
 *
 * @param string $content	原字符串
 * @param string $length	截取长度
 * @param string $num		0=字节  1=个数
 * @param string $add		结尾添加 '..'
 * @param string $code		编码 utf-8或其他
 * @return string
 */
function substrs($content,$length,$num=0,$add=0,$code=''){
	global $sys;
	$code = $code ? $code : strtoupper($sys['lang']);
	$content = strip_tags($content);
	if($length && strlen($content)>$length){
		$retstr='';
		if($code == 'UTF-8'){
			$retstr = utf8_trim($content,$length,$num);
		}else{
			for($i = 0; $i < $length; $i++) {
				if(ord($content[$i]) > 127){
					if($num){
						$retstr .=$content[$i].$content[$i+1];
						$i++;
						$length++;
					}elseif(($i+1<$length)){
						$retstr .=$content[$i].$content[$i+1];
						$i++;
					}
				}else{
					$retstr .=$content[$i];
				}
			}
		}
		return $retstr.($add ? '..' : '');
	}
	return $content;
}

function utf8_trim($string,$length,$num) {
	if($length && strlen($string)>$length){
		if($num){
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $string, $t_string);
			return join('', array_slice($t_string[0], 0, $length));
		}else{
			$hex = '';
			$str = substr($string,0,$length);
			for($i=$length-1;$i>=0;$i--){
				$ch   = ord($str[$i]);
				$hex .= ' '.$ch;
				if(($ch & 128)==0)	return substr($str,0,$i);
				if(($ch & 192)==192)return substr($str,0,$i);
			}
			return($str.$hex);
		}
	}
	return $string;
}

/**
 * 读取文件内容
 *
 * @copyright PHPWind
 * @param string $filename
 * @param string $method
 * @return string
 */
function readover($filename,$method="rb"){
	strpos($filename,'..')!==false && exit('Forbidden');
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}

/**
 * 将指定内容写入文件
 *
 * @copyright PHPWind
 * @param string $filename 文件名
 * @param string $data 要写入的数据
 * @param string $method 操作方法
 * @param boolean $iflock 是否锁定
 */
function writeover($filename,$data,$method="rb+",$iflock=1,$check=1,$chmod=1){
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
	$handle=fopen($filename,$method);
	$iflock && flock($handle,LOCK_EX);
	if(@fwrite($handle,$data)=== FALSE){
		fclose($handle);
		return false;
	}
	if($method=="rb+") ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
	return true;
}

function PrintEot($template,$EXT="htm"){
	//Copyright (c) 2003-07 PHPWind
	global $tplpath;
	if(!$template) $template=N;
	$path=Pcv(R_P."template/$tplpath/$template.$EXT");
	!file_exists($path) && $path=R_P."template/user/$template.$EXT";
	return $path;
}

function getdirname($path){
	if(strpos($path,'\\')!==false){
		return substr($path,0,strrpos($path,'\\'));
	}elseif(strpos($path,'/')!==false){
		return substr($path,0,strrpos($path,'/'));
	}else{
		return '/';
	}
}

function GdConfirm($code){
	Cookie('cknum','',0);
	if(!$code || !SafeCheck(explode("\t",StrCode(GetCookie('cknum'),'DECODE')),$code,'cknum',1800)){
		if(function_exists('adminmsg')){
			Showmsg('ckerror');
		}elseif(defined('IN_EXT')) {
			throwError('ckerror');
		}else{
			echo "ckerror";
			exit();
		}
	}
}

function SafeCheck($CK,$PwdCode,$var='AdminUser',$expire=1800)
{
	global $timestamp;
	$t	= $timestamp - $CK[0];
	if($t > $expire || $CK[2] != md5($PwdCode.$CK[0])){
		Cookie($var,'',0);
		return false;
	}else{
		$CK[0] = $timestamp;
		$CK[2] = md5($PwdCode.$timestamp);
		$Value = implode("\t",$CK);
		$$var  = StrCode($Value);
		Cookie($var,StrCode($Value));
		return true;
	}
}

function StrCode($string,$action='ENCODE'){
	global $sys;
	$key	= substr(md5($_SERVER["HTTP_USER_AGENT"].$sys['hash']),8,18);
	$string	= $action == 'ENCODE' ? $string : base64_decode($string);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($string); $i++){
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$code = $action == 'DECODE' ? $code : base64_encode($code);
	return $code;
}

function randstr($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}

function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	return $randval;
}

/**
 * 返回语言包文件
 *
 * @param string $filename
 * @return string
 */
function GetLang($filename){
	global $sys;
	empty($sys['lang']) && $sys['lang']='gbk';
	return Pcv(R_P."lang/$sys[lang]/$filename.php");
}

/**
 * 得到分页内容
 *
 * @param integer $count 数量合计
 * @param integer $page 当前页
 * @param integer $numofpage 合计页
 * @param string $url 分页url
 * @param integer $max
 * @return string
 */
function numofpage($count,&$page,$numofpage,$url,$max=0){
	$total=$numofpage;
	$max && $numofpage > $max && $numofpage=$max;
	if($numofpage <= 1 || !is_numeric($page)){
		return '';
	}else{
		if($page<1){
			$page = 1;
		}elseif($page>$numofpage){
			$page = $numofpage;
		}
		$pages="<div class=\"pages\"><a href=\"{$url}page=1\" style=\"font-weight:bold\">&laquo;</a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++){
			if($i<1) continue;
			$pages.="<a href=\"{$url}page=$i\">$i</a>";
		}
		$pages.="<b> $page </b>";
		if($page<$numofpage){
			for($i=$page+1;$i<=$numofpage;$i++){
				$pages.="<a href=\"{$url}page=$i\">$i</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$pages.="<input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){ location='{$url}page='+this.value;return false;}\"><a href=\"{$url}page=$numofpage\" style=\"font-weight:bold\">&raquo;</a> Pages: ( $page/$total total )</div>";
		return $pages;
	}
}

/**
 * 抛出一个致命的程序错误
 *
 * @param string $msg
 */
function throwError($msg){
	ob_end_clean();
	extract($GLOBALS, EXTR_SKIP);
	require GetLang('error');
	if(defined('IN_EXT') && file_exists(E_P.'lang/extmsg.php')){
		require_once(E_P.'lang/extmsg.php');
		$lang = array_merge((array)$lang,(array)$extmsg);
	}
	$lang[$msg] && $msg=$lang[$msg];

	$errmsg="<div style='font-size:12px;font-family:verdana;line-height:180%;color:#666;border:dashed 1px #ccc;padding:1px;margin:20px;'>";
	$errmsg.="<div style=\"background: #eeedea;padding-left:10px;font-weight:bold;height:25px;\">$lang[prompt]</div>";
	$errmsg.="<div style='padding:20px;font-size:14px;'><span>$msg</span></div>";
	$errmsg.="<div style=\"text-align:center;height:30px;\"><a href='javascript:history.go(-1)'>$lang[back]</a></div>";
	$errmsg.="</div>";
	die($errmsg);
}

/*function __autoload($class_name) { //PHP5
$class_name = strtolower($class_name);
require_once R_P.'require/class_'.$class_name.'.php';
}*/

/**
 * 结束ob输出，并把缓存输出到浏览器或者赋值给指定变量以生成静态
 *
 */
function footer(){
	global $sys,$_OUTPUT,$cid,$page;
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
	
	if($sys['rewrite']){
		$output = preg_replace(
		"/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'>\s]+\.php\?[^\"\'>\s]+)([\"|\']?)/ies",
		"Htm_cv('\\3','<a\\1href=\"')",
		$output
		);
	}
	ob_end_clean();
	if(class_exists('Action') || function_exists('adminmsg')){
		//如果是后台传入,则为生成静态动作
		$_OUTPUT = $output;
	}else{
		($sys['gzip'] == 1 && function_exists('ob_gzhandler')) ? ob_start('ob_gzhandler') : ob_start();
//		$cid = intval($cid);
//		$page = $page ? intval($page) : 1;
//		SCR == 'list' && $output .= "\n<script src='update.php?type=list&cid=".$cid."&page=".$page."'></script>";
		echo $output;
//		flush();
		exit;
	}
}

/**
 * 页面伪静态化
 *
 * @param string $url
 * @param string $tag
 * @return string
 */
function Htm_cv($url,$tag){
	global $sys;
	if(!preg_match("^(http|ftp|telnet|mms|rtsp)|admin.php|rss.php",$url) || strpos($url,$sys['url'])===0){
		if(strpos($url,'#')!==false){
			$add = substr($url,strpos($url,'#'));
		}
		$url = str_replace(
		array('.php?','=','&',$add),
		array($sys['rewrite_dir'],'-','-',''),
		htmlchars_decode($url)
		).$sys['rewrite_ext'].$add;
	}
	return stripslashes($tag)."$url\"";
}

/**
 * 返回一个模板文件的编译路径
 *
 * @param string $tplname
 * @return string
 */
function Template($tplname){
	global $cid,$user_tplpath,$default_tplpath,$catedb;
	if(!$catedb) require_once(D_P.'data/cache/cate.php');
	require_once(R_P.'require/template.php');
	return Tpl($tplname);
}



/**
 * 根据原图返回一个缩略图路径
 *
 * @param string $sourceImg 原图路径
 * @param integer $width 缩略宽度
 * @param integer $height 缩略高速
 * @param integer $quality 缩略质量
 * @return string
 */
function miniImg($sourceImg,$width,$height,$quality=85){
	global $sys,$attach;
	if ($sys['skipgif']) { //忽略对Gif图片的处理
		if(strtolower(end(explode('.',$sourceImg))) == 'gif') return $sourceImg;
	}
	require_once(R_P.'require/class_attach.php'); //需要对附件进行处理，调用附件处理类
	if(!is_object($attach)){ //倘若类没有实例化，则new之
		$attach = new Attach();
	}
	$attach->picheight = $height;
	$attach->picwidth = $width;
	$imgarray = $attach->getAttachPath($sourceImg);
	if($imgarray) {
		list($sourceImg,$targetImg,$SmallImg) = $imgarray;
	}else {
		return $sourceImg;
	}
	if(!filesize($targetImg)){
		$attach->resize_image($sourceImg,$targetImg,$width,$height,$quality);
	}
	return $SmallImg;
}

/**
 * 检验访问方式是否合法，是否应该通过静态来访问
 *
 */
function checkRefer(){
	global $catedb,$sys,$cid,$tid,$db;
	if(function_exists('adminmsg')) return ; //说明是后台浏览
	$tid = (int)$tid;
	$cid = (int)$cid;
	$page = (int)GetGP('page');
	if(SCR=='list'){
		!$cid && throwError('nocid');
		!$catedb[$cid]['type'] && throwError('notpubcate');
		if($catedb[$cid]['listpub'] && $catedb[$cid]['listurl']){
			if(!function_exists('adminmsg')){
				if(!$page) {
					$jumpurl = $catedb[$cid]['listurl'];
				}else {
					$url_ext = end(explode('.',$catedb[$cid]['listurl']));
					$ext_len = strlen($url_ext)+1;
					$name_s  = substr($catedb[$cid]['listurl'],0,-$ext_len);
					$jumpurl = $name_s."_".$page.".".$url_ext;
				}
				if(file_exists(R_P.$jumpurl)){
					ObHeader($jumpurl);
				}
			}
		}
		return ;
	}elseif (SCR=='view'){
		!$cid && throwError('nocid');
		//!$catedb[$cid]['type'] && throwError('notpubcate');
		if($catedb[$cid]['htmlpub'] && !function_exists('adminmsg')){
			$mid = $catedb[$cid]['mid'];
			if ($mid<=0) return;
			$rs = $db->get_one("SELECT url FROM cms_contentindex WHERE tid='$tid' AND cid='$cid' AND ifpub>=1");
			if($rs){
				if($rs['url']) {
					$jumpurl = $sys['htmdir'].'/'.$rs['url'];
					if(file_exists(R_P.$jumpurl)){
						ObHeader($jumpurl);
					}
				}
				
			}else{
				throwError('data_error');
			}
		}
		return ;
	}elseif (SCR=='index'){
		if(/*$sys['htmlindex'] && */!function_exists('adminmsg')){
			if(!file_exists(R_P.'index.'.$sys['htmext'])){
				exit("首页尚未生成，请到后台更新首页");
			}else{
				ObHeader('index.'.$sys['htmext']); //倘若不是发布动作，转向到静态页
			}
		}
	}
}

function ObHeader($URL){
	global $sys;
	if($sys['rewrite'] && strtolower(substr($URL,0,4))!='http'){
		$URL="$sys[url]/$URL";
	}
	if($sys['gzip']){
		header("Location: $URL");exit;
	}else{
		ob_start();
		echo "<script language='javascript'>\n";
		echo "window.location='$URL';\n";
		echo "</script>";
		exit;
	}
}

/**
 * 根据PW特有格式返回数组
 *
 * @param string $filename
 * @return array
 */
function openfile($filename){
	$filedata=readover($filename);
	$filedata=str_replace("\n","\n<:wind:>",$filedata);
	$filedb=explode("<:wind:>",$filedata);
	$count=count($filedb);
	if($filedb[$count-1]==''||$filedb[$count-1]=="\r"){unset($filedb[$count-1]);}
	if(empty($filedb)){$filedb[0]="";}
	return $filedb;
}

/**
 * 生成一个随机字符串
 *
 * @param integer $num
 * @return string
 */
function randomStr($num){
	$word= 'abcdefghijklmnopqrstuvwxyz0123456789';
	$len = strlen($word);
	$len = $len-2;
	$str = '';
	for ($x=0;$x<$num;$x++){
		$i = rand(0,$len);
		$theword = substr($word,$i,1);
		$str .= $theword;
	}
	return $str;
}

function generateStr($len){
	global $sys;
	mt_srand((double)microtime() * 1000000);
    $keychars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWYXZ";
	$maxlen = strlen($keychars)-1;
	$str	= '';
	for ($i=0;$i<$len;$i++){
		$str .= $keychars[mt_rand(0,$maxlen)];
	}
	return substr(md5($str.time().$_SERVER["HTTP_USER_AGENT"].$sys['hash']),mt_rand(0,32-$len),$len);
}

/**
 * 判断浏览器类型
 *
 * @param
 * @return string
 *   Array['Type']		浏览器类型: MSIE  Gecko  Opera  Safari
 *   Array['Version']	浏览器版本:
 */
function IsBrowser(){
	$sAgent = $_SERVER['HTTP_USER_AGENT'] ;
	$result = array();
	if ( strpos($sAgent, 'MSIE') !== false && strpos($sAgent, 'mac') === false && strpos($sAgent, 'Opera') === false ){
		$result['Type']		= 'MSIE';
		$result['Version']	= (float)substr($sAgent, strpos($sAgent, 'MSIE') + 5, 3);
	}else if ( strpos($sAgent, 'Gecko/') !== false ){
		$result['Type']		= 'Gecko';
		$result['Version']	= (int)substr($sAgent, strpos($sAgent, 'Gecko/') + 6, 8);
	}else if (strpos($sAgent,'Opera/') !== false){
		$result['Type']		= 'Opera';
		$result['Version']	= (int)substr($sAgent,strpos($sAgent,'Opera/')+6,5);
	}else if (strpos($sAgent,'Safari/') !== false){
		$result['Type']		= 'Safari';
		$result['Version']	= (int)substr($sAgent,strpos($sAgent,'Safari/')+7,8);
	}else{
		return false;
	}
	return $result;
}

/**
 * html标签匹配
 *
 * @param string $str
 * @return string
 */
function htmlbalance($str){
	preg_match_all ("/<([\w]+)[^>]*>.*/",$str, $htmlbegin);
	preg_match_all ("/<([\/][\w]+)[^>]*>.*/",$str, $htmlend);
	$sinhtml = array("br","BR","input","INPUT","hr","HR","img","IMG");
	$htmlbegin[1] = array_diff($htmlbegin[1],$sinhtml);
	if(count($htmlbegin[1]) == count($htmlend[1])){
		return $str;
	}else{
		$htmlbegin	= array_change_key_case(array_count_values($htmlbegin[1]));
		$htmlend	= array_change_key_case(array_count_values($htmlend[1]));
		foreach($htmlbegin as $key=>$val){
			$keyend = "/".$key;
			@$htmlend[$keyend] = $htmlend[$keyend]?$htmlend[$keyend]:0;
			$htmlcount = $val-$htmlend[$keyend];
			if($htmlcount){
				for($i=0;$i<$htmlcount;$i++){
					$str .= "<".$keyend.">";
				}
			}
		}
		return $str;
	}
}

/**
 * html特殊字符解码
 *
 * @param string $str
 * @return string
 */
function htmlchars_decode($str){
	$encode = array('&amp;','&quot;','&#039;','&lt;','&gt;');
	$decode = array('&','"','\'','<','>');
	$str	= str_replace($encode,$decode,$str);
	return $str;
}

/**
 * 中文字符截取，防乱码
 *
 * @return string
 */
function get_substr($string,$start='0',$length=''){
	$start  = (int)$start;
	$length = (int)$length;
	$i = 0;
	if(!$string){
		return;
	}
	if($start>=0){
		while($i<$start){
			if(ord($string[$i])>127){
				$i = $i+2;
			}else{
				$i++;
			}
		}
		$start = $i;
		if($length==''){
			return substr($string,$start);
		}elseif($length>0){
			$end = $start+$length;
			while($i<$end){
				if(ord($string[$i])>127){
					$i = $i+2;
				}else{
					$i++;
				}
			}
			if($end != $i-1){
				$end = $i;
			}else{
				$end--;
			}
			$length = $end-$start;
			return substr($string,$start,$length);
		}elseif($length==0){
			return;
		}else{
			$length = strlen($string)-abs($length)-$start;
			return get_substr($string,$start,$length);
		}
	}else{
	  $start = strlen($string)-abs($start);
	  return get_substr($string,$start,$length);
	}
}

/**
 * 删除文件
 *
 * @return
 */
function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	return @unlink($filename);
}

/**
 * 批量初始化POST or GET变量,并数组返回
 *
 * @param Array $keys
 * @param string $method
 * @param var $htmcv
 * @return Array
 */
function Init_GP($keys,$method='GP',$htmcv=0){
	!is_array($keys) && $keys = array($keys);
	$array = array();
	foreach($keys as $val){
		$array[$val] = NULL;
		if($method!='P' && isset($_GET[$val])){
			$array[$val] = $_GET[$val];
		} elseif($method!='G' && isset($_POST[$val])){
			$array[$val] = $_POST[$val];
		}
		$htmcv && $array[$val] = Char_cv($array[$val]);
	}
	return $array;
}

/**
 * 批量初始化POST or GET变量,并将变量全局化
 *
 * @param Array $keys
 * @param string $method
 * @param var $htmcv
 */
function InitGP($keys,$method='GP',$htmcv=0){
	!is_array($keys) && $keys = array($keys);
	foreach($keys as $val){
		$GLOBALS[$val] = NULL;
		if($method!='P' && isset($_GET[$val])){
			$GLOBALS[$val] = $_GET[$val];
		} elseif($method!='G' && isset($_POST[$val])){
			$GLOBALS[$val] = $_POST[$val];
		}
		$htmcv && $GLOBALS[$val] = Char_cv($GLOBALS[$val]);
	}
}

/**
 * 初始化单一POST or GET 变量
 *
 * @param string $key
 * @param string $method
 * @return unknown
 */
function GetGP($key,$method='GP'){
	if($method=='G' || $method!='P' && isset($_GET[$key])){
		return $_GET[$key];
	}
	return $_POST[$key];
}

/**
 * 包含本地路径安全检查
 *
 * @param string $filename
 * @param var $ifcheck
 * @return unknown
 */
function Pcv($filename,$ifcheck=1){
	strpos($filename,'http://')!==false && exit('Forbidden');
	$ifcheck && strpos($filename,'..')!==false && exit('Forbidden');
	return $filename;
}

/**
 * 伪静态GET字符过滤
 *
 * @param unknown_type $var
 * @param string $_SINIT
 * @param string $_SHAVE
 */
function CheckVar(&$var,$_SINIT,$_SHAVE){
	if(is_array($var)){
		foreach($var as $key=>$value){
			CheckVar($var[$key],$_SINIT,$_SHAVE);
		}
	}else{
		$var=str_replace($_SINIT,$_SHAVE,$var);
	}
}

/**
 * 数据库中指定的表是否存在
 *
 * @param object $db
 * @param string $table_name
 * @param string $fields_name
 * @return bool
 */
function table_exists(&$db,$table_name,$fields_name=''){
	$rt = $db->get_one("SHOW TABLES LIKE'$table_name'");
	if(!$rt){
		return false;
	}elseif($fields_name){
		$rt = $db->get_one("SHOW COLUMNS FROM $table_name LIKE'$fields_name'");
		if(!$rt){
			return false;
		}
	}
	return true;
}

/**
 * 禁用词语过滤
 *
 * @param string $message
 * @return bool
 */
function wordsfb(&$message){
	global $ext_config;
	if(!isset($ext_config)){
		include(D_P."data/cache/ext_config.php");
	}
	if(!$ext_config['wordfilter']['ifopen']){
		return false;
	}
	include(D_P."data/cache/wordfilter.php");
	if($wordsfb){
		foreach($wordsfb as $k=>$v){
			if(strpos($message,$k)!==false){
				global $banword;
				$banword = $k;
				return $banword;
			}
		}
	}
	if($replace){
		foreach($replace as $k=>$v){
			$message = preg_replace("/$k/i",$v,$message);
		}
	}
	return false;
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

function newBlog($type) {
	$fileDir = Pcv(R_P.'combine/blog/class_'.$type.'.php');
	!file_exists($fileDir) && exit('file_not_exist');
	require_once($fileDir);
	!class_exists($type) && exit('class_not_exist');
	return new $type();
}

function newBBS($type) {
	$fileDir = Pcv(R_P.'combine/bbs/class_'.$type.'.php');
	!file_exists($fileDir) && exit('file_not_exist');
	require_once($fileDir);
	!class_exists($type) && exit('class_not_exist');
	return new $type();
}
?>