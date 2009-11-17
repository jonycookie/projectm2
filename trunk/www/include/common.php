<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
if(!defined('iCMS')) {
	exit('Access Denied');
}
function stripslashes_deep($val) {
	 return is_array($val)?array_map('stripslashes_deep', $val):stripslashes($val);
}
function add_magic_quotes($val) {
	return is_array($val)?array_map('add_magic_quotes',$val):addslashes($val);
}
function msgJson($state,$lang,$frame=false,$break=true){
	global $iCMS;
	$msg=$iCMS->language($lang);
	if($frame){
		echo '<script type="text/javascript">document.domain="'.$iCMS->config['domain'].'";alert("'.$msg.'");';
		if($state=="1")echo ' window.parent.location.reload();';
		echo '</script>';
	}else{
		echo "{state:'$state',msg:'$msg'}";
	}
	$break && exit();
}
//警告
Function alert($str, $url="javascript:"){
	$A=explode(':',$url);
	$script='<script type="text/javaScript">alert("'.$str.'");';
	if($A[0]=='javascript'){
		$script.=empty($A[1])?'history.go(-1);':$A[1];
    }elseif($A[0]=='url'){
    	$A[1]=="1" && $A[1]=__REF__;
    	$script.=empty($A[1])?"window.close();":"window.location.href='{$A[1]}';";
    }
    echo $script.'</script>';
    exit;
}
// 格式化时间
function get_date($timestamp = '',$format){
	global $iCMS;
	empty($format) && $format=$iCMS->config['dateformat'];
	$timeoffset = $iCMS->config['ServerTimeZone'] == '111' ? 0 : $iCMS->config['ServerTimeZone'];
	$iCMS->config['cvtime']&&$cvtime=$iCMS->config['cvtime']*60;
	empty($timestamp) && $timestamp = time();
	return gmdate($format,$timestamp+$timeoffset*3600+$cvtime);
}
// 获取客户端IP
function getip($format=0) {
	global $_iGLOBAL;
	if(empty($_iGLOBAL['ip'])) {
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$_iGLOBAL['ip'] = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	}
	if($format) {
		$ips = explode('.', $_iGLOBAL['ip']);
		for($i=0;$i<3;$i++) {
			$ips[$i] = intval($ips[$i]);
		}
		return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]);
	} else {
		return $_iGLOBAL['ip'];
	}
}

//---------------------------------------------------------------------------------------------
// 中文正则
define("CN_PATTERN",iCMS_CHARSET=="utf-8"?'/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/':'/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/');
//中文长度
Function cstrlen($str){
	if (function_exists('mb_substr')) {
		return mb_strlen($str,iCMS_CHARSET);
	} elseif (function_exists('iconv_substr')) {
		return iconv_strlen($str,iCMS_CHARSET);
	} else {
		preg_match_all(CN_PATTERN, $str, $match);
		return count($match[0]);
	} 
}
/*
 * @todo 中文截取，支持gb2312,gbk,utf-8,big5 
 * @param string $str 要截取的字串
 * @param int $length 截取长度
 * @param $suffix 是否加尾缀
 * @param int $start 截取起始位置
 * @param string $charset utf-8|gb2312|gbk|big5 编码
 */
function csubstr($str,$length,$suffix=FALSE,$start=0){
	if (function_exists('mb_substr')) {
		$more = (mb_strlen($str) > $length) ? TRUE : FALSE;
		$text = mb_substr($str, $start, $length, iCMS_CHARSET);
	} elseif (function_exists('iconv_substr')) {
		$more = (iconv_strlen($str) > $length) ? TRUE : FALSE;
		$text = iconv_substr($str, $start, $length, iCMS_CHARSET);
	} else {
		preg_match_all(CN_PATTERN, $str, $match);
		$text = join("",array_slice($match[0], $start, $length));
		$more = (count($match[0])>$length)?TRUE:FALSE;
	}
	if($suffix && $more) $text.=" ...";
	return $text;
}
function pinyin($str,$split=""){
	$pinyin=include iPATH.'include/pinyin.php';
    preg_match_all(CN_PATTERN,trim($str),$match);
    $s = $match[0]; $c = count($s);
    for ($i=0;$i<$c;$i++) {
    	if($v = array_search_value($s[$i],$pinyin)){
    		$zh && $split && $R[]=$split;
    		$R[]=$v;$zh=true;$az09=false;
   		}else if(eregi("[a-z0-9]",$s[$i])){
  			$zh && $i!=0 && !$az09 && $split && $R[]=$split;
    		$R[]=$s[$i];$zh=true;$az09=true;
    	}else{
//    		array('+',' ','/','?','%','#','&','=')//url
//    		array('\\','/',':','?','*','"','<','>','|')//dir
			$sp=true;
     		if($split){
     			if($s[$i]==' '){
     				$R[]=$sp?'':$split;$sp=false;
     			}else{
     				$R[]=$sp?$split:'';$sp=true;
     			}
     		}else{
     			$R[]='';
     		}
//     		$R[]=$split?(($s[$i]==' ')?$split:
//     		str_replace(array('+',' ','/','?','%','#','&','=','\\',':','*','"','<','>','|'),'',$s[$i])):'';
     		$zh=false;$az09=false;
    	}
    }
    unset($pinyin);
    return implode('',$R);
}
/**
 * 在数组的value里面搜索
 *
 * @param string $p1
 * @param array  $p2
 * @return bool
 */
function array_search_value($p1,$p2){
    while (list($k,$v)=each($p2)) {
        if (strpos($v,$p1)!==false) {
            return $k;
        }
    }
    return false;
}
/**
 * 验证函数
 *
 * @param string $p1    需要验证的字符串
 * @param int    $p2    验证类型
 * @return bool
 */
function validate($p1,$p2){
    switch((string)$p2){
        case '0' : // 数字，字母，逗号，杠，下划线，[，]
            $p3 = '^[\w\,\/\-\[\]]+$';
            break;
        case '1' : // 字母
            $p3 = '^[A-Za-z]+$';
            break;
        case '2' : // 匹配数字
            $p3 = '^\d+$';
            break;
        case '3' : // 字母，数字，下划线，杠
            $p3 = '^[\w\-]+$';
            break;
        case '4' : // Email
            $p3 = '^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$';
            break;
        case '5' : // url
            $p3 = '^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+';
            break;
        case '6' : // 
            $p3 = '^[\d\,\.]+$';
            break;
        default  : // 自定义正则
            $p3 = $p2;
            break;
    }
    return preg_match("/{$p3}/i",$p1);
}
//截取HTML
function htmlSubString($content,$maxlen=300,$suffix=FALSE){
	$content = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
	$wordrows=0;	$outstr="";	$wordend=false;	$beginTags=0;	$endTags=0;	
	foreach($content as $value){
		if (trim($value)=="") continue;
		
		if (strpos(";$value","<")>0){
			if (!preg_match("/(<[^>]+?>)/si",$value) &&cstrlen($value)<=$maxlen) {
				$wordend=true;
				$outstr.=$value;
			}
			if ($wordend==false){
				$outstr.=$value;
				if (!preg_match("/<img([^>]+?)>/is",$value)&& !preg_match("/<param([^>]+?)>/is",$value)&& !preg_match("/<!([^>]+?)>/is",$value)&& !preg_match("/<[br|BR]([^>]+?)>/is",$value)&& !preg_match("/<hr([^>]+?)>/is",$value)&&!preg_match("/<\/([^>]+?)>/is",$value)) {
					$beginTags++;
				}else{
					if (preg_match("/<\/([^>]+?)>/is",$value,$matches)){
						$endTags++;
					}
				}
			}else{
				if (preg_match("/<\/([^>]+?)>/is",$value,$matches)){
					$endTags++;
					$outstr.=$value;
					if ($beginTags==$endTags && $wordend==true) break;
				}else{
					if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<[br|BR]([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)&& !preg_match("/<\/([^>]+?)>/is",$value)) {
						$beginTags++; 
						$outstr.=$value;
					}
				}
			}
		}else{
			if (is_numeric($maxlen)){
				$curLength=cstrlen($value);
				$maxLength=$curLength+$wordrows;
				if ($wordend==false){
					if ($maxLength>$maxlen){
						$outstr.=csubstr($value,$maxlen-$wordrows,FALSE,0);
						$wordend=true;
					}else{
						$wordrows=$maxLength;
						$outstr.=$value;
					}
				}
			}else{
				if ($wordend==false) $outstr.=$value;
			}
		}
	}
	while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)){
		$outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html",$outstr);
	}
	if (strpos(";".$outstr,"[html_")>0){
		$outstr=str_replace("[html_&lt;]","<",$outstr);
		$outstr=str_replace("[html_&gt;]",">",$outstr);
	}
	if($suffix&&cstrlen($outstr)>=$maxlen)$outstr.="．．．";
	return $outstr;
}
//去掉多余的空标签
function strip_empty_html($matches){
	$arr_tags1=explode(" ",$matches[1]);
	if ($arr_tags1[0]==$matches[2]){
		return "";
	}else{
		$matches[0]=str_replace("<","[html_&lt;]",$matches[0]);
		$matches[0]=str_replace(">","[html_&gt;]",$matches[0]);
		return $matches[0];
	}
}

function jstr($SourceText,$SourceLang="UTF-8"){
	return str_replace(array('&#x', ';'),array('\u', ''),getUNICODE($SourceText,$SourceLang));
}
function getUNICODE($SourceText,$SourceLang="GB2312"){
	$utf="";
	$tmp = file(iPATH.'include/gb-unicode.table');
	$unicode_table = array();
	while(list($key,$value)=each($tmp)){
		$unicode_table[hexdec(substr($value,0,6))]=substr($value,9,4);
	}
	while($SourceText){
		if(function_exists('iconv')){
			if(ord(substr($SourceText, 0, 1)) > 127) {
				$utf .= "&#x".dechex(CHSUTF8toU(iconv($SourceLang,"UTF-8", substr($SourceText, 0, 2)))).";";
				$SourceText = substr($SourceText, 2, strlen($SourceText));
			} else {
				$utf .= substr($SourceText, 0, 1);
				$SourceText = substr($SourceText, 1, strlen($SourceText));
			}
		}elseif(ord(substr($SourceText,0,1))>127){
			if($SourceLang=="GB2312")
				$utf.="&#x".$unicode_table[hexdec(bin2hex(substr($SourceText,0,2)))-0x8080].";";

			if($SourceLang=="BIG5")
				$utf.="&#x".$unicode_table[hexdec(bin2hex(substr($SourceText,0,2)))].";";

			$SourceText=substr($SourceText,2,strlen($SourceText));
		}else{
			$utf.=substr($SourceText,0,1);
			$SourceText=substr($SourceText,1,strlen($SourceText));
		}
	}
	return str_replace(array('&#x;', '&#x0;'),array('??', ''),$utf);
}
function CHSUTF8toU($c) {
	switch(strlen($c)) {
		case 1:
			return ord($c);
		case 2:
			$n = (ord($c[0]) & 0x3f) << 6;
			$n += ord($c[1]) & 0x3f;
			return $n;
		case 3:
			$n = (ord($c[0]) & 0x1f) << 12;
			$n += (ord($c[1]) & 0x3f) << 6;
			$n += ord($c[2]) & 0x3f;
			return $n;
		case 4:
			$n = (ord($c[0]) & 0x0f) << 18;
			$n += (ord($c[1]) & 0x3f) << 12;
			$n += (ord($c[2]) & 0x3f) << 6;
			$n += ord($c[3]) & 0x3f;
			return $n;
	}
}
function g2u($instr) {
	if(is_array($instr)){
		return array_map("g2u",$instr);
	}else{
		if (function_exists('mb_convert_encoding')){
			return mb_convert_encoding($instr,'UTF-8','GBK');
		} elseif (function_exists('iconv')) {
			return iconv('GBK','UTF-8',$instr);
		} else {
			$fp = fopen(iPATH.'include/gb-unicode.table', 'r');
			$len = strlen($instr);
			$outstr = '';
			for( $i = $x = 0 ; $i < $len ; $i++ ) {
				$h = ord($instr[$i]);
				if( $h > 160 ) {
					$l = ( $i+1 >= $len ) ? 32 : ord($instr[$i+1]);
					fseek( $fp, ($h-161)*188+($l-161)*2 );
					$uni = fread( $fp, 2 );
					$codenum = ord($uni[0])*256 + ord($uni[1]);
					if( $codenum < 0x800 ) {
						$outstr[$x++] = chr( 192 + $codenum / 64 );
						$outstr[$x++] = chr( 128 + $codenum % 64 );
		#				printf("[%02X%02X]<br>\n", ord($outstr[$x-2]), ord($uni[$x-1]) );
					}
					else {
						$outstr[$x++] = chr( 224 + $codenum / 4096 );
						$codenum %= 4096;
						$outstr[$x++] = chr( 128 + $codenum / 64 );
						$outstr[$x++] = chr( 128 + ($codenum % 64) );
		#				printf("[%02X%02X%02X]<br>\n", ord($outstr[$x-3]), ord($outstr[$x-2]), ord($outstr[$x-1]) );
					}
					$i++;
				}
				else
					$outstr[$x++] = $instr[$i];
			}
			fclose($fp);
			if( $instr != '' )
				return join( '', $outstr);
		}
	}
}
function u2g( $instr ) {
	if(is_array($instr)){
		return array_map("u2g",$instr);
	}else{
		if (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($instr,'GBK','UTF-8');
		} elseif (function_exists('iconv')) {
			return iconv('UTF-8','GBK',$instr);
		} else {
			$fp = fopen(iPATH.'include/unicode-gb.table', 'r' );
			$len = strlen($instr);
			$outstr = '';
			for( $i = $x = 0 ; $i < $len ; $i++ ) {
				$b1 = ord($instr[$i]);
				if( $b1 < 0x80 ) {
					$outstr[$x++] = chr($b1);
		#			printf( "[%02X]", $b1);
				}
				elseif( $b1 >= 224 ) {	# 3 bytes UTF-8
					$b1 -= 224;
					$b2 = ($i+1 >= $len) ? 0 : ord($instr[$i+1]) - 128;
					$b3 = ($i+2 >= $len) ? 0 : ord($instr[$i+2]) - 128;
					$i += 2;
					$uc = $b1 * 4096 + $b2 * 64 + $b3 ;
					fseek( $fp, $uc * 2 );
					$gb = fread( $fp, 2 );
					$outstr[$x++] = $gb[0];
					$outstr[$x++] = $gb[1];
		#			printf( "[%02X%02X]", ord($gb[0]), ord($gb[1]));
				}
				elseif( $b1 >= 192 ) {	# 2 bytes UTF-8
		//			printf( "[%02X%02X]", $b1, ord($instr[$i+1]) );
					$b1 -= 192;
					$b2 = ($i+1>=$len) ? 0 : ord($instr[$i+1]) - 128;
					$i++;
					$uc = $b1 * 64 + $b2 ;
					fseek( $fp, $uc * 2 );
					$gb = fread( $fp, 2 );
					$outstr[$x++] = $gb[0];
					$outstr[$x++] = $gb[1];
		#			printf( "[%02X%02X]", ord($gb[0]), ord($gb[1]));
				}
			}
			fclose($fp);
			if( $instr != '' ) {
		#		echo '##' . $instr . " becomes " . join( '', $outstr) . "<br>\n";
				return join( '', $outstr);
			}
		}
	}
}
function sechtml($string){
    $search = array("/\s+/","/<(\/?)(script|iframe|style|object|html|body|title|link|meta|\?|\%)([^>]*?)>/isU","/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU");
   	$replace = array(" ","&lt;\\1\\2\\3&gt;","\\1\\2",);
	$string = preg_replace ($search, $replace, $string);
  	return $string;
}
//HTML TO TEXT
function HtmToText($string){
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = HtmToText($val);
		}
	} else {
		$search = array ("'<script[^>]*?>.*?</script>'si","'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'","'&(quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i","'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i","'&(copy|#169);'i","'&#(\d+);'e");
		$replace = array ("", "", "\\1", "\"", "&", "<", ">", " ", chr(161), chr(162), chr(163), chr(169), "chr(\\1)");
		$string = preg_replace ($search, $replace, $string);
	}
	return $string;
}
function HTML2JS($string){
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = HTML2JS($val);
		}
	} else {
		$string = str_replace(array("\n","\r","\\","\""), array(' ',' ',"\\\\","\\\""), $string);
	}
	return $string;
}
function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}
function unhtmlspecialchars($string){
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = unhtmlspecialchars($val);
		}
	} else {
		$string = str_replace (array('&amp;','&#039;','&quot;','&lt;','&gt;'), array('&','\'','\"','<','>'), $string );
	}
	return $string;
}
//-----------------------------------------------------------------------
//设置COOKIE
function set_cookie($name, $value = "", $cookiedate = 0){
	global $_COOKIE,$_SERVER,$_iGLOBAL;
	$cookiedomain	= $_iGLOBAL['cookie']['domain'] == "" ? ""  : $_iGLOBAL['cookie']['domain'];
	$cookiepath		= $_iGLOBAL['cookie']['path']   == "" ? "/" : $_iGLOBAL['cookie']['path'];
	$cookiedate		= $cookiedate==0 ? $_iGLOBAL['cookie']['time']:$cookiedate;
	$name 			= $_iGLOBAL['cookie']['prename'].$name;
	$_COOKIE[$name] = $value;
	setcookie($name, $value, $cookiedate ? $_iGLOBAL['timestamp'] + $cookiedate : 0, $cookiepath, $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}
//取得COOKIE
function get_cookie($name){
	global $_COOKIE,$_iGLOBAL;
	if (isset($_COOKIE[$_iGLOBAL['cookie']['prename'].$name])) {
		return $_COOKIE[$_iGLOBAL['cookie']['prename'].$name];
	}
	return FALSE;
}

//关键字过滤器
Function WordFilter(&$content){
	global $iCMS;
	$cache	= $iCMS->cache(array('word.filter','word.disable'),'include/syscache',0,true);
	$filter	= $cache['word.filter'];//filter过滤
	$disable= $cache['word.disable'];//disable禁止
	//禁止关键词
	if (is_array($disable))foreach ($disable AS $val) {
		if ($val && preg_match("/".preg_quote($val, '/')."/i", $content)){
			return true;
		}
	}
	//过滤关键词
	if (is_array($filter))foreach ($filter AS $k =>$val) {
		empty($val[1]) && $val[1]='***';
		$val[0] && $content = preg_replace("/".preg_quote($val[0], '/')."/i",$val[1],$content);
	}
}
//获取远程页面的内容
function fopen_url($url) {
	if (function_exists('file_get_contents')) {
		$file_content = file_get_contents($url);
	} elseif (ini_get('allow_url_fopen') && ($file = @fopen($url, 'rb'))){
		$i = 0;
		while (!feof($file) && $i++ < 1000) {
			$file_content .= strtolower(fread($file, 4096));
		}
		fclose($file);
	} elseif (function_exists('curl_init')) {
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle, CURLOPT_FAILONERROR,1);
  		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'iDreamSoft Check');
		$file_content = curl_exec($curl_handle);
		curl_close($curl_handle);
	} else {
		$file_content = '';
	}
	return $file_content;
}
//文件操作函数
function delfile($filename,$check=1){
	$check && strpos($filename,'..')!==false && exit('What are you doing?');
	@chmod ($filename, 0777);
	return @unlink($filename);
}
function openfile($filename,$check=1,$method="rb"){
	$check && strpos($filename,'..')!==false && exit('What are you doing?');
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function writefile($filename,$data,$check=1,$method="rb+",$iflock=1,$chmod=1){
	$check && strpos($filename,'..')!==false && exit('What are you doing?');
	touch($filename);
	$handle=fopen($filename,$method);
	if($iflock){
		flock($handle,LOCK_EX);
	}
	fwrite($handle,$data);
	if($method=="rb+") ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
}
//创建目录
function createdir($dir){
//	strpos($dir,'.')!==false && alert('目录名不能带有"."!');
    if (!is_dir($dir)) {
        createdir(dirname($dir), 0777);
        @mkdir($dir, 0777);
        @chmod($dir,0777);
    }
    return true;
}
//删除目录
function rmdirs($dir,$df=true){
    if ($dh=@opendir($dir)) {
        while (($file=readdir($dh))!== false) {
            if ($file != "." && $file != "..") {
                $path = $dir.'/'.$file;
                is_dir($path) ? rmdirs($path,$df) : ($df ? @unlink($path) : null);
            }
        }
        closedir($dh);
    }
    return @rmdir($dir);
}
// 获得文件扩展名
Function getext($f) {
	return substr(strrchr($f, "."), 1);
}
Function uploadfile($field,$intro="",$SaveDir="",$SaveFileName="",$type="upload"){
	global $iCMS;
	$UploadDir 		= $iCMS->config['uploadfiledir']."/";
	$RelativePath	= $iCMS->dir.$UploadDir;//相对路径
	$RootPath		= iPATH.$UploadDir;//绝对路径

	if($_FILES[$field]['name']){
		$tmp_name = $_FILES[$field]['tmp_name'];
		!is_uploaded_file($tmp_name) && exit("What are you doing?");
		if($_FILES[$field]['error'] > 0){
			switch((int)$_FILES[$field]['error']){
			case UPLOAD_ERR_NO_FILE:
				@unlink($tmp_name);
				alert('请选择上传文件!');
				return false;break;
			case UPLOAD_ERR_FORM_SIZE: 
				@unlink($tmp_name);
				alert('上传的文件超过大小!');
				return false;break;
			}
			return false;
		}
		$_FileSize = @filesize($tmp_name);
		//文件类型
		preg_match("/\.([a-zA-Z0-9]{2,4})$/",$_FILES[$field]['name'],$exts);
		$FileExt=strtolower($exts[1]);//&#316;&#701;
		CheckValidExt($FileExt);//判断文件类型
		//过滤文件;
		strstr($FileExt, 'ph')&&$FileExt="phpfile";
		in_array($FileExt,array('cer','htr','cdx','asa','asp','jsp','aspx','cgi'))&& $FileExt.="file";
		
		$FileNameTmp =get_date('',"YmdHis").rand(1,999999);
		empty($SaveFileName) && $SaveFileName = $FileNameTmp.".".$FileExt;
		$oFileName = $_FILES[$field]['name'];

		// 文件保存目录方式
		$_CreateDir = "";
		if(empty($SaveDir)){
			if($iCMS->config['savedir']){
				$_CreateDir = str_replace(array('Y','y','m','n','d','j','EXT'),
				array(get_date('','Y'),get_date('','y'),get_date('','m'),get_date('','n'),get_date('','d'),get_date('','j'),$FileExt),
				$iCMS->config['savedir'])."/";
			}
		}else{
			$_CreateDir=$SaveDir."/";
		}
	//	$UploadDir		= $UploadDir.$_CreateDir; 
		$RelativePath	= $RelativePath.$_CreateDir;
		$RootPath		= $RootPath.$_CreateDir;
		//创建目录
		createdir($RootPath);
		//文件名
	//	$sFileName				= $UploadDir.$SaveFileName;
		$RelativePath_FileName	= $RelativePath.$SaveFileName;
		$RootPath_FileName		= $RootPath.$SaveFileName;
		savefile($tmp_name,$RootPath_FileName);

		if(in_array($FileExt,array('gif','jpg','jpeg','png'))){
			if($iCMS->config['isthumb'] &&($iCMS->config['thumbwidth']||$iCMS->config['thumbhight'])){
				$Thumb=MakeThumbnail($RootPath, $RootPath_FileName, $FileNameTmp);
				!empty($Thumb['src']) && imageWaterMark($Thumb['src']);
			}
			imageWaterMark($RootPath.$SaveFileName);
		}
		$RelativePath_FileName	= getfilepath($RelativePath_FileName,'','-');
		// 写入数据库
		if($type=="upload"){
			$iCMS->db->query("INSERT INTO `#iCMS@__file` (`filename`,`ofilename`,`path`,`intro`,`ext`,`size`,`time`,`type`) VALUES ('$SaveFileName', '$oFileName', '$RelativePath_FileName','$intro', '$FileExt', '$_FileSize', '".time()."', 'upload') ");
		}
		$_File=array('fid'=>$iCMS->db->insert_id,'FilePath'=>$RelativePath_FileName,'OriginalFileName'=>$oFileName,'FileName'=>$SaveFileName);
		
		return $_File;
	}else{
		return;
	}
}
//保存文件
function savefile($tn,$FilePath){
	if (function_exists('move_uploaded_file') && @move_uploaded_file($tn, $FilePath)) {
		@chmod ($FilePath, 0666);
	}elseif (@copy($tn, $FilePath)) {
		@chmod ($FilePath, 0666);
	}elseif (@is_readable($tn)) {
		if ($fp = @fopen($tn,'rb')) {
			@flock($fp,2);
			$filedata = @fread($fp,@filesize($tn));
			@fclose($fp);
		}
		if ($fp = @fopen($FilePath, 'wb')) {
			@flock($fp, 2);
			@fwrite($fp, $filedata);
			@fclose($fp);
			@chmod ($FilePath, 0666);
		} else {
			alert("上传出错!");
			return;
		}
	}else{
		alert("上传出错!");
		return;
	}
}
// 获取文件大小
Function GetFileSize($filesize) {
	$R = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	$n = 0;
	while ($filesize >= 1024) {
		$filesize /= 1024;
		$n++;
	}
	return round($filesize,2).' '.$R[$n];
}
//获取文件夹列表
function GetFolderList($d,$dir='',$type=''){
	$sDir = trim($d);
	$type=strtolower($type);
	strpos($sDir,'.')!==false && exit('What are you doing?');
	$s_Url = "";
	$FDir = iPATH.$dir.'/';
	$sCurrDir = $FDir;
	if ($sDir != "") {
		if (is_dir($FDir.$sDir)) {
			$sCurrDir = $FDir.$sDir."/";
		}else{
			$sDir = "";
		}
		$s_Url =(strrpos($sDir, "/") !== false)?substr($sDir, 0, strrpos($sDir, "/")):"";
		$parentfolder=$s_Url;
	}
	if ($handle = opendir($sCurrDir)) {
		while (false !== ($file = readdir($handle))) {
			$sFileType = filetype($sCurrDir."/".$file);
			switch ($sFileType){
			case "dir":
				if ($file!='.'&&$file!='..'&&$file!='admin'){
					$oDirs[] = $file;
				}
				break;
			case "file":
				$oFiles[] = $file;
				break;
			default:
			}
		}
	}

	if (isset($oDirs)){
		foreach( $oDirs as $oDir){
			$s_Url = ($sDir == "")?$oDir:$sDir."/".$oDir;
			$folder[]=array('path'=>$s_Url,'dir'=>$oDir);
		}
	}
	$nFileNum=(isset($oFiles))?count($oFiles):0;
	if ($nFileNum>0){
		foreach( $oFiles as $oFile){
			$sFileName = $sCurrDir.$oFile;
			if(getext($oFile)){
				$s_Url = ($sDir == "")?$oFile:$sDir . "/" . $oFile;
				if($type && strstr($type,getext($oFile))!==false){
					$FileList[]=array('path'=>$s_Url,
						'name'=>$oFile,
						'time'=>get_date(filemtime($sFileName),"Y-m-d H:i"),
						'icon'=>geticon($oFile),
						'ext'=>getext($oFile),
						'size'=>GetFileSize(filesize($sFileName))
					);
				}elseif(empty($type)){
					$FileList[]=array('path'=>$s_Url,
						'name'=>$oFile,
						'time'=>get_date(filemtime($sFileName),"Y-m-d H:i"),
						'icon'=>geticon($oFile),
						'ext'=>getext($oFile),
						'size'=>GetFileSize(filesize($sFileName))
					);
				}
			}
		}
	}
	$s_Url =($sDir == "")?"/":"/" . $sDir . "/";
	$R['FileList']		=$FileList;
	$R['parentfolder']	=$parentfolder;
	$R['folder']		=$folder;
	return $R;
}
function gethumb($sfp,$w='',$h='',$scale=false,$callback=false){
	global $iCMS;
	if(strpos($sfp,'thumb/')!==false||strpos($sfp,'http://')!==false)return $sfp;
	$sfn	= substr($sfp,0,strrpos($sfp,'.'));
	$sfn	= substr($sfn,strrpos($sfn,'/'));
	$tpf	= substr($sfp,0,strrpos($sfp,'/')).'/thumb'.$sfn.'_';
	$rootpf	= getfilepath($tpf,iPATH,'+');
	if($callback){
		$tfArray= glob($rootpf."*");
		if($tfArray)foreach ($tfArray as $filename) {
			if(file_exists($filename)){
				$fn	= substr($filename,0,strrpos($filename,'.'));
				$per= substr($fn,strrpos($fn,'_')+1);
				$tfpList[$per]=$filename;
			}
		}
		return $tfpList;
	}else{
		$srfp	= getfilepath($sfp,iPATH,'+');
		if(file_exists($srfp)){
			empty($w) && $w=$iCMS->config['thumbwidth'];
			empty($h) && $h=$iCMS->config['thumbhight'];
			$twh=$rootpf.$w.'x'.$h.'.'.getext($sfp);
			if(!file_exists($twh)){
				$Thumb=MakeThumbnail(substr($srfp,0,strrpos($srfp,'/')).'/', $srfp, substr($sfn,strrpos($sfn,'/')+1),$w,$h,$scale);
				$twh=$Thumb['src'];
			}
			$src=$iCMS->dir.getfilepath($twh,iPATH,'-');
		}else{
			$src=$iCMS->dir.'include/nopic.gif';
		}
		return $src;
	}
}
function getfilepath($p,$pt='',$m='+'){
	global $iCMS;
//	echo ($p.' | '.$pt.'| '.$m)."<br />";
	$rp	= ($m=='+') ? $iCMS->dir : $pt;
//	echo($m.'$rp='.$rp)."<br />";
	$fp	= ($iCMS->dir=='/') ? (($m=='+')?substr($p,1):$p): str_replace($rp,'',$p);
//	echo($m.'$fp='.$fp)."<br />";
	$fp	= ($m=='+') ? $pt.$fp : str_replace($pt,'',$fp);
//	echo($m.'$fp='.$fp)."<br />";
	return $fp;
}
function gethttpurl($url){
	global $iCMS;
	$a=parse_url($iCMS->config['url']);
	return strtolower($a['scheme'])."://".$a["host"].$url;
}
function CheckValidExt($sExt){
	global $iCMS;
	$aExt = explode(',',strtoupper($iCMS->config['fileext']));
	if(!in_array(strtoupper($sExt),$aExt)){
		alert("不支持上传此类扩展名的附件");
		exit;
	}
}
function RootPath2DomainPath($url){
	global $iCMS;
	$sProtocol = explode("/", $_SERVER["SERVER_PROTOCOL"]);
	$sHost = strtolower($sProtocol[0])."://".$_SERVER["HTTP_HOST"];
	$sPort = $_SERVER["SERVER_PORT"];
	if ($sPort != "80") {
		$sHost = $sHost.":".$sPort;
	}
	return $sHost.$iCMS->dir.$url;
}

function MakeThumbnail($upfiledir,$src,$tName,$tw='',$th='',$scale=true,$tDir="thumb") {
	global $iCMS;
	$R 		= array();
	$image  = "";
	$tMap  = array( 1 => 'gif', 2 => 'jpeg', 3 => 'png' );
	$tw		=empty($tw)?(int)$iCMS->config['thumbwidth']:$tw;
	$th		=empty($th)?(int)$iCMS->config['thumbhight']:$th;
	
	if ( $tw && $th ) {
		list($width, $height,$type) = @getimagesize($src);
		if ( $width < 1 && $height < 1 ) {
			$R['width']    = $tw;
			$R['height']   = $th;
			$R['src'] = $src;
			return $R;
		}

		if ( $width > $tw || $height >$th ) {
			createdir($upfiledir.$tDir);
			if($scale){
				$im = scale_image(array("mw"  => $tw,"mh" => $th,"cw"  => $width,"ch" => $height ));
			}else{
				$im = array('w'=>$tw,'h'=> $th);
			}
			$R['width']   = $im['w'];
			$R['height']  = $im['h'];
			$tName.= '_'.$R['width'].'x'.$R['height'];
			$img	= icf($tMap[$type],$src);
			if ($img['res']) {
				$thumb = imagecreatetruecolor($im['w'], $im['h']);
				imagecopyresampled($thumb, $img['res'], 0, 0, 0, 0, $im['w'], $im['h'], $width, $height);
				PHP_VERSION != '4.3.2'&&UnsharpMask($thumb);
				$R['src'] =$upfiledir.$tDir."/".$tName;
				__image($thumb,$img['type'],$R['src']);
				$R['src'] .='.'.$img['type'];
			} else {
				$R['src'] = $src;
			}
		} else { 
			$R['width']    = $width;
			$R['height']   = $height;
			$R['src'] = $src;
		}
		return $R;
	}

}
function __image($dst,$imgType,$fn,$save=true){
	$save && $fn.=".".$imgType;
	if($imgType == 'gif'){
		imagegif($dst,$fn);
	}elseif($imgType == 'jpg' ){
		imagejpeg($dst,$fn);
	}elseif($imgType == 'png' ){
		imagepng($dst,$fn);
	}
//	imagedestroy($dst);
}
function icf($imgType,$pf){
	if($imgType=='gif' && function_exists('imagecreatefromgif')) {
		$res = imagecreatefromgif($pf);
		$type = 'gif';
	}elseif ($imgType == 'png' && function_exists('imagecreatefrompng')) {
		$res = imagecreatefrompng($pf);
		$type = 'png';
	}elseif ($imgType == 'jpeg' && function_exists('imagecreatefromjpeg')) {
		$res = imagecreatefromjpeg($pf);
		$type = 'jpg';
	}
	return array('res'=>$res,'type'=>$type);
}
function scale_image($a) {
	$ret = array('w' => $a['cw'], 'h' => $a['ch']);
	if ( $a['cw'] > $a['mw'] ) {
		$ret['w']  = $a['mw'];
		$ret['h']  = ceil( ( $a['ch'] * ( ( $a['mw'] * 100 ) / $a['cw'] ) ) / 100 );
		$a['ch'] = $ret['h'];
		$a['cw'] = $ret['w'];
	}
	if ( $a['ch'] > $a['mh'] ) {
		$ret['h']  = $a['mh'];
		$ret['w']  = ceil( ( $a['cw'] * ( ( $a['mh'] * 100 ) / $a['ch'] ) ) / 100 );
	}
	return $ret;
}



function UnsharpMask($img, $amount = 100, $radius = .5, $threshold = 3) {
	$amount = min($amount, 500);
	$amount = $amount * 0.016;
	if ($amount == 0) return true;
	$radius = min($radius, 50);
	$radius = $radius * 2;
	$threshold = min($threshold, 255);
	$radius = abs(round($radius));
	if ($radius == 0) return true;
	$w = ImageSX($img);
	$h = ImageSY($img);
	$imgCanvas  = ImageCreateTrueColor($w, $h);
	$imgCanvas2 = ImageCreateTrueColor($w, $h);
	$imgBlur    = ImageCreateTrueColor($w, $h);
	$imgBlur2   = ImageCreateTrueColor($w, $h);
	ImageCopy($imgCanvas,  $img, 0, 0, 0, 0, $w, $h);
	ImageCopy($imgCanvas2, $img, 0, 0, 0, 0, $w, $h);
	for ($i = 0; $i < $radius; $i++)	{
		ImageCopy($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1);
		ImageCopyMerge($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50);
		ImageCopyMerge($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333);
		ImageCopyMerge($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25);
		ImageCopyMerge($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333);
		ImageCopyMerge($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25);
		ImageCopyMerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 );
		ImageCopyMerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // dow
		ImageCopyMerge($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50);
		ImageCopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);
		ImageCopy($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 20 );
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 16.666667);
		ImageCopyMerge($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		ImageCopy($imgCanvas2, $imgBlur2, 0, 0, 0, 0, $w, $h);
	}
	for ($x = 0; $x < $w; $x++)	{
		for ($y = 0; $y < $h; $y++)	{
			$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
			$rOrig = (($rgbOrig >> 16) & 0xFF);
			$gOrig = (($rgbOrig >>  8) & 0xFF);
			$bOrig =  ($rgbOrig        & 0xFF);
			$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
			$rBlur = (($rgbBlur >> 16) & 0xFF);
			$gBlur = (($rgbBlur >>  8) & 0xFF);
			$bBlur =  ($rgbBlur        & 0xFF);
			$rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
			$gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
			$bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;
			if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
				$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
				ImageSetPixel($img, $x, $y, $pixCol);
			}
		}
	}
	ImageDestroy($imgCanvas);
	ImageDestroy($imgCanvas2);
	ImageDestroy($imgBlur);
	ImageDestroy($imgBlur2);
	return true;
}
function imageWaterMark($groundImage){ 
	global $iCMS;
	if(empty($iCMS->config['iswatermark']))return;
	list($width, $height,$imagetype) = @getimagesize($groundImage);
	if ( $width < $iCMS->config['waterwidth'] || $height<$iCMS->config['waterheight'] ) { 
		return FALSE;
	}
   	$isWaterImage = FALSE; 
	$formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG等格式。"; 
   //读取水印文件 
   if(!empty($iCMS->config['waterimg']) && file_exists(iPATH."include/watermark/".$iCMS->config['waterimg'])){ 
   	   $waterImage	= iPATH."include/watermark/".$iCMS->config['waterimg'];
       $isWaterImage = TRUE; 
       $water_info = @getimagesize($waterImage); 
       $water_w    = $water_info[0];//取得水印图片的宽 
       $water_h    = $water_info[1];//取得水印图片的高 
       switch($water_info[2]){//取得水印图片的格式 
           case 1:$water_im = imagecreatefromgif($waterImage);break; 
           case 2:$water_im = imagecreatefromjpeg($waterImage);break; 
           case 3:$water_im = imagecreatefrompng($waterImage);break; 
           default:die($formatMsg); 
       } 
   }else{
   	   	putenv('GDFONTPATH=' .iPATH.'include/');
   		//$iCMS->config['watertext']=g2u($iCMS->config['watertext']);
   		$fontfile=iPATH.'include/'.$iCMS->config['waterfont'];
   }

   //读取背景图片 
   if(!empty($groundImage) && file_exists($groundImage)){ 
       $ground_info = @getimagesize($groundImage); 
       $ground_w    = $ground_info[0];//取得背景图片的宽 
       $ground_h    = $ground_info[1];//取得背景图片的高 

       switch($ground_info[2]){ //取得背景图片的格式 
           case 1:$ground_im = imagecreatefromgif($groundImage);break; 
           case 2:$ground_im = imagecreatefromjpeg($groundImage);break; 
           case 3:$ground_im = imagecreatefrompng($groundImage);break; 
           default:die($formatMsg); 
       } 
   }else{ 
       die("需要加水印的图片不存在！"); 
   } 

   //水印位置 
   if($isWaterImage){ //图片水印 
       $w = $water_w; 
       $h = $water_h; 
   }else{ //文字水印
	   if($iCMS->config['waterfont']){
	       $temp = imagettfbbox($iCMS->config['waterfontsize'],0,$fontfile,$iCMS->config['watertext']);//取得使用 TrueType 字体的文本的范围 
	       $w = $temp[2] - $temp[6]; 
	       $h = $temp[3] - $temp[7]; 
	       unset($temp); 
	   }else{
	       $w = $iCMS->config['waterfontsize']*cstrlen($iCMS->config['watertext']); 
	       $h = $iCMS->config['waterfontsize']+5; 
	   }
   } 
   if( ($ground_w<$w) || ($ground_h<$h) ){ 
//       echo "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！"; 
       return; 
   } 
   switch($iCMS->config['waterpos']) { 
       case 0://随机 
           $posX = rand(0,($ground_w - $w)); 
           $posY = rand($h,($ground_h - $h)); 
           break; 
       case 1://1为顶端居左 
           $posX = 0; 
           $posY = 0;
           break; 
       case 2://2为顶端居中 
           $posX = ($ground_w - $w) / 2; 
           $posY = 0;
           break; 
       case 3://3为顶端居右 
           $posX = $ground_w - $w; 
           $posY = 0;
           break; 
       case 4://4为中部居左 
           $posX = 0; 
           $posY = ($ground_h - $h) / 2;
           break; 
       case 5://5为中部居中 
           $posX = ($ground_w - $w) / 2;
           $posY = ($ground_h - $h) / 2;
           break; 
       case 6://6为中部居右 
           $posX = $ground_w - $w;
           $posY = ($ground_h - $h) / 2;
           break; 
       case 7://7为底端居左 
           $posX = 0; 
           $posY = $ground_h - $h;
           break; 
       case 8://8为底端居中 
           $posX = ($ground_w - $w) / 2;
           $posY = $ground_h - $h;
           break; 
       case 9://9为底端居右 
           $posX = $ground_w - $w;
           $posY = $ground_h - $h;
           break; 
       default://随机 
           $posX = rand(0,($ground_w - $w)); 
           $posY = rand($h,($ground_h - $h)); 
           break;     
   } 

   //设定图像的混色模式 
   imagealphablending($ground_im, true); 

   if($isWaterImage){ //图片水印 
       imagecopymerge($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h,$iCMS->config['waterpct']);//拷贝水印到目标文件         
   }else{//文字水印 
		if(empty($iCMS->config['watercolor']))$iCMS->config['watercolor']="#FFFFFF";
		if(!empty($iCMS->config['watercolor']) && (strlen($iCMS->config['watercolor'])==7) ){ 
           $R = hexdec(substr($iCMS->config['watercolor'],1,2)); 
           $G = hexdec(substr($iCMS->config['watercolor'],3,2)); 
           $B = hexdec(substr($iCMS->config['watercolor'],5)); 
           $textcolor = imagecolorallocate($ground_im, $R, $G, $B);
       }else{
           die("水印文字颜色格式不正确！"); 
       }
		if($iCMS->config['waterfont']){
			imagettftext($ground_im,$iCMS->config['waterfontsize'], 0, $posX, $posY, $textcolor,$fontfile, $iCMS->config['watertext']);
		}else{
			imagestring ($ground_im, $iCMS->config['waterfontsize'], $posX, $posY, $iCMS->config['watertext'],$textcolor);
		}
   }

   //生成水印后的图片 
   @unlink($groundImage); 
   switch($ground_info[2]){//取得背景图片的格式 
       case 1:imagegif($ground_im,$groundImage);break; 
       case 2:imagejpeg($ground_im,$groundImage);break; 
       case 3:imagepng($ground_im,$groundImage);break; 
       default:die($errorMsg); 
   }
   //释放内存 
   if(isset($water_info))unset($water_info);
   isset($water_im) && imagedestroy($water_im);
   unset($ground_info);
   imagedestroy($ground_im);
}

function rewrite($url,$tag,$tag2){
	global $iCMS;
	if(ereg('^http|ftp|telnet|mms|rtsp|admin.php|rss.php|link.php|'.preg_quote($iCMS->config['rewrite']['ext']).'$',$url)===false){
		strpos($url,'#')!==false && $add = substr($url,strpos($url,'#'));
		$url = str_replace(array('.php?','=','&',$add),
			array($iCMS->config['rewrite']['dir'],$iCMS->config['rewrite']['split'],$iCMS->config['rewrite']['split'],''),
			$url).$iCMS->config['rewrite']['ext'].$add;
		if($iCMS->config['customlink']=="2"){
			if($iCMS->config['linkmode']=='id'){
				$url = str_replace(array('listid','showid'),array('list','show'),$url);
			}elseif($iCMS->config['linkmode']=='title'){
				$url = str_replace(array('listt','showt'),array('list','show'),$url);
			}
			$url = str_replace(array('tagt','commentaid','indexp','searchkeyword','index.php/page','index//page'),array('tag','comment','index','search','index/page','index/page'),$url);
		}
	}
	return stripslashes($tag).$url.stripslashes($tag2);
}
function path($p=''){
	if(strpos($p,'..')=== false)return $p;
	$pA=explode('/',$p);
	$k=array_search('..',$pA);
	unset($pA[$k],$pA[$k-1]);
	$path=implode('/',$pA);
	return strpos($path,'..')=== false ? $path : path($path);
}

//新版
function _header($URL=''){
	empty($URL)&&$URL=__REF__; 
	if(!headers_sent()){
		header("Location: $URL");exit;
	}else{
		echo '<meta http-equiv=\'refresh\' content=\'0;url='.$URL.'\'><script type="text/JavaScript">window.location.replace(\''.$URL.'\');</script>';exit;
	}
}
function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}
//检查验证码
//function ckseccode($seccode,$alert="") {
function ckseccode($seccode) {
	$_seccode=get_cookie('seccode');
	$cookie_seccode = empty($_seccode)?'':authcode($_seccode, 'DECODE');
	if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
		//runlog('seccode','cookie='.strtolower($cookie_seccode).'&post='.strtolower($seccode).'&alert='.$alert);
		//$alert ? alert($alert);
		return true;
	}else{
		return false;
	}
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;

	$key = md5($key ? $key : iCMSKEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}
function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}
//写运行日志
function runlog($file, $log, $halt=0) {
	global $_iGLOBAL,$Admin;
	$log = get_date('','Y-m-d H:i:s')."\t$type\t".getip()."\t".$Admin->uId."\t".__SELF__."\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($log))."\n";
	$yearmonth = get_date($_iGLOBAL['timestamp'],'Ym');
	$logdir = iPATH.'admin/logs/';
	if(!is_dir($logdir)) mkdir($logdir, 0777);
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);
		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>', "\r", "\n"), '', $log)."\n");
		fclose($fp);
	}
	if($halt) exit();
}
//-------------------------------------------------------
//SystemField
function getSystemField(){
	return array('cid','order','title','customlink','editor','userid','tags','pubdate','hits','digg','comments','type','vlink','top','visible');
}
function getFieldValue($mid,$field,$v/*,&$rs,$page=0*/){
	global $iCMS;
	$__FIELD__	= $iCMS->cache('field.model','include/syscache',0,true);
	$F			= $__FIELD__[$field][$mid]?$__FIELD__[$field][$mid]:$__FIELD__[$field][0];
	$rules		= $F['rules'];
	$__RETURN__	= Null;
	switch($Finfo['type']){
		case "radio":
			$rules && $__RETURN__	= $rules[$v];
		break;
//		case "editor":
//			$body	=explode('<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>',$v);
//			$total	=count($body);
//			$nBody	=$body[intval($page-1)];
//			$v		=$iCMS->keywords($nBody);
//			$rs->page=$page;
//			if($total>1){
//				$CLArray=array('id'=>$rs->id,'link'=>$rs->customlink,'dir'=>$rs->catalogdir,'pubdate'=>$rs->pubdate);
//				$pagebreak=($page-1>1)?'<a href="'.$this->iurl('show',$CLArray,$page-1).'" class="pagebreak" target="_self">上一页</a> ':'<a href="'.$this->iurl('show',$CLArray).'" class="pagebreak" target="_self">'.$this->language('page:prev').'</a> ';
//				for($i=1;$i<=$total;$i++){
//					$cls=$i==$page?"pagebreaksel":"pagebreak";
//					$pagebreak.=$i==1?'<a href="'.$this->iurl('show',$CLArray).'" class="'.$cls.'" target="_self">'.$i.'</a>':'<a href="'.$this->iurl('show',$CLArray,$i).'" class="'.$cls.'" target="_self">'.$i.'</a>';
//				}
//				$np=($total-$page>0)?$page+1:$page;
//				$pagebreak.='<a href="'.$this->iurl('show',$CLArray,$np).'" class="pagebreak" target="_self">'.$this->language('page:next').'</a>';
//				$rs->pagebreak=$pagebreak;
//			}
//		break;
		case in_array($Finfo['type'],array('checkbox','select','multiple')):
			$vArray=explode(',',$v);
			if($rules)foreach($rules AS $value=>$text){
				$vArray=explode(',',$v);
				in_array($value,$vArray) && $__RETURN__[$value]	= $text;
			}
		break;		
	}
	return $__RETURN__;
}
?>