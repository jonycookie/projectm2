<?php
error_reporting(0);
define('W_P',__FILE__ ? substr(__FILE__,0,-14) : '../');
require_once(W_P.'global.php');
require_once(D_P.'data/cache/cate.php');
require_once(R_P.'require/chinese.php');
require_once(R_P.'wap/wap_mod.php');
$sys['gzip'] == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
$tplpath = 'wap';
if(!$sys['wapifopen']){
	wap_msg('wap_closed');
}
if($sys['lang'] != 'utf8'){
	$chs = new Chinese('UTF8',$sys['lang']);
	foreach($_POST as $key=>$value){
		//$$key=$chs->Convert($$key);
		$_POST[$key] = $chs->Convert($value);
	}
}
$wapcids = explode(',',$sys['wapcids']);

/**
 * 验证cid的合法性
 *
 * @param string $cid
 */
function catecheck($cid){
	global $db;
	$fm=$db->get_one("SELECT type,mid FROM cms_category WHERE cid='$cid'");
	if(!$fm || !$fm['type'] || $fm['mid'] !=1){
		wap_msg('wap_cid_right');
	}
}

/**
 * 转换WML的保留字符
 *
 * @param string $msg
 * @return string
 */
function wap_cv($msg){
	$msg = str_replace('&','&amp;',$msg);
	$msg = str_replace('&nbsp;',' ',$msg);
	$msg = str_replace('"','&quot;',$msg);
	$msg = str_replace("'",'&#39;',$msg);
	$msg = str_replace("<","&lt;",$msg);
	$msg = str_replace(">","&gt;",$msg);
	$msg = str_replace("\t","   &nbsp;  &nbsp;",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);
	return $msg;
}

/**
 * 将纯文本转化为 WML
 *
 * @param string $content
 * @return string
 */
function text2wml($content) {
	$content = str_replace('$', '$$', $content);
	$content = str_replace("\r\n", "\n", htmlspecialchars($content));
	$content = explode("\n", $content);
	//print_r($content);flush();exit;
	for ($i = 0; $i < count($content); $i++) {
		$content[$i] = trim($content[$i]);
		if (str_replace(" ", "", $content[$i]) == "") unset($content[$i]);
	}
	$content = str_replace("<p><br /></p>\n", "", "<p>".implode("<br /></p>\n<p>", $content)."<br /></p>\n");

	return $content;
}

/**
 * 将 HTML 网页内容转化为WML
 *
 * @param string $content
 * @return string
 */
function htm2wml($content) {
	global $charset;
	$content = preg_replace("/<style .*?<\/style>/is", "", $content);
	$content = preg_replace("/<script .*?<\/script>/is", "", $content);
	$content = preg_replace("/<br \s*\/?\/>/i", "\n", $content);
	$content = preg_replace("/<\/?p>/i", "\n", $content);
	$content = preg_replace("/<\/?td>/i", "\n", $content);
	$content = preg_replace("/<\/?div>/i", "\n", $content);
	$content = preg_replace("/<\/?blockquote>/i", "\n", $content);
	$content = preg_replace("/<\/?li>/i", "\n", $content);
	$content = preg_replace("/\&nbsp\;/i", " ", $content);
	$content = preg_replace("/\&nbsp/i", " ", $content);
	$content = strip_tags($content);
	if(strtoupper(substr($charset,0,2))=='GB'){
		$htmlcharset = 'GB2312';
	}elseif(strtoupper(substr($charset,0,3))=='BIG'){
		$htmlcharset = 'BIG5';
	}elseif(strtoupper(substr($charset,0,3))=='UTF'){
		$htmlcharset = 'UTF-8';
	}
	$content = html_entity_decode($content, ENT_QUOTES, $htmlcharset);
	$content = preg_replace("/\&\#.*?\;/i", "", $content);

	return text2wml($content);
}

/**
 * Enter description here...
 *
 * @param string $text
 * @param num $spsize
 * @return string
 */
function getPageContent($text,$spsize){
	if(strlen($text)<$spsize) return array("$text");
	$bds = explode('<',$text);
	$npageBody 	= "";
	$istable 	= 0;
	$j			= 0;
	$contents 	= array();
	foreach($bds as $i=>$k){
		if($i==0){
			$npageBody .= $bds[$i];
			continue;
		}
		$bds[$i] = "<".$bds[$i];
		if(strlen($bds[$i])>6){
			$tname = substr($bds[$i],1,5);
			if(strtolower($tname)=='table'){
				$istable++;
			}else if(strtolower($tname)=='/tabl'){
				$istable--;
			}
			if($istable>0){
				$npageBody .= $bds[$i];
				continue;
			}else{
				$npageBody .= $bds[$i];
			}
		}else{
			$npageBody .= $bds[$i];
		}
		if(strlen($npageBody)>$spsize){
			$contents[]	= $npageBody;
			$npageBody	= "";
		}
	}
	$npageBody && $contents[] = $npageBody;
	return $contents;
}
?>