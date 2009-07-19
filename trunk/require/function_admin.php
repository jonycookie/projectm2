<?php
!defined('IN_CMS') && die('Forbidden');

/**
* admin Functions
*/
function Showmsg($msg){
	extract($GLOBALS, EXTR_SKIP);
	require GetLang('cpmsg');
	if(defined('IN_EXT') && file_exists(E_P.'lang/extmsg.php')){
		require_once(E_P.'lang/extmsg.php');
		$lang = array_merge((array)$lang,(array)$extmsg);
	}
	$lang[$msg] && $msg=$lang[$msg];
	$errmsg="<div class=\"t\">";
	$errmsg.="<div style=\"background: #eeedea;padding:10px;font-weight:bold;\">$lang[prompt]</div>";
	$errmsg.="<div style='padding:20px;font-size:14px;'><img src='images/admin/bg_face.gif' align='absmiddle' hspace=20 >$msg</div>";
	$errmsg.="<div style=\"text-align:center;height:30px;\"><input class=btn type=button onclick='window.history.go(-1)' value=' $lang[back] ' /></div>";
	$errmsg.="</div>";
	die($errmsg);
}

function adminbottom($display=1){
	global $sys;
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
	ob_end_clean();
	$sys['gzip'] == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	echo $output;
	exit;
}

function checkAdmin($admin_name,$admin_password){
	global $db,$manager,$manager_pwd,$timestamp,$logfile;
	if($GLOBALS['sys']['cktime']){
		$GLOBALS['sys']['cktime']<5 && $GLOBALS['sys']['cktime']=5; //必须保证5分钟的有效时间
		$cktime = $GLOBALS['sys']['cktime']*60;
	}else{
		$cktime = 1800;
	}
	$ck_time = $timestamp + $cktime;
	Cookie('Adminuser',$admin_name."\t".$admin_password,$ck_time); //更新活动时间
	if($admin_name==$manager && $admin_password==$manager_pwd){
		return ;
	}elseif($admin_name){
		$men = $db->get_one("SELECT * FROM cms_admin WHERE username='$admin_name' LIMIT 1");
		if($men && $men['password'] == $admin_password){
			$men['priv'] = explode(',',$men['priv']);
			if($men['privcate']){
				$men['privcate'] = explode(',',$men['privcate']);
			}
			return $men;
		}else{
			$record_name= str_replace('|','&#124;',Char_cv($admin_name));
			$record_pwd	= str_replace('|','&#124;',Char_cv($admin_password));
			$new_record="<?die;?>|$record_name|$record_pwd|Logging Failed|$onlineip|$timestamp|\n";
			writeover($logfile,$new_record,"ab");
		}
	}
	Cookie('Adminuser','',0);
	Showmsg('login_invalidation');
}

function adminmsg($msg,$jumpurl='',$t=2){
	extract($GLOBALS, EXTR_SKIP);
	!$basename && $basename=$_SERVER['REQUEST_URI'];
	!$jumpurl && $jumpurl=$basename;
	$ifjump="<META HTTP-EQUIV='Refresh' CONTENT='$t; URL=$jumpurl'>";
	require_once GetLang('cpmsg');
	if(defined('IN_EXT') && file_exists(E_P.'lang/extmsg.php')){
		require_once(E_P.'lang/extmsg.php');
		$lang = array_merge((array)$lang,(array)$extmsg);
	}
	$lang[$msg] && $msg=$lang[$msg];
	$outmsg="<div style='font-size:12px;font-family:verdana;line-height:180%;color:#666;border:dashed 1px #ccc;padding:1px;margin:20px;'>";
	$outmsg.="<div style=\"background: #eeedea;padding-left:10px;font-weight:bold;height:25px;\">$lang[prompt]</div>";
	$outmsg.="<div style='padding:20px;font-size:14px;'><img src='images/admin/ok.gif' align='absmiddle' hspace=20 ><span>$msg</span></div>";
	$outmsg.="<div style=\"text-align:center;height:30px;\">$ifjump<a href=\"$jumpurl\">$lang[back]</a></div>";
	$outmsg.="</div>";
	echo $outmsg;
	adminbottom();
}

function operate($msg,$jumpurl='',$t=2){
	extract($GLOBALS, EXTR_SKIP);
	!$basename && $basename=$_SERVER['REQUEST_URI'];;
	!$jumpurl && $jumpurl=$basename;
	$ifjump="<META HTTP-EQUIV='Refresh' CONTENT='$t; URL=$jumpurl'>";
	require_once GetLang('cpmsg');
	if(defined('IN_EXT') && file_exists(E_P.'lang/extmsg.php')){
		require_once(E_P.'lang/extmsg.php');
		$lang = array_merge((array)$lang,(array)$extmsg);
	}
	$lang[$msg] && $msg=$lang[$msg];
	$outmsg="<div style='font-size:12px;font-family:verdana;line-height:180%;color:#666;border:dashed 1px #ccc;padding:1px;margin:20px;'>";
	$outmsg.="<div style=\"background: #eeedea;padding-left:10px;font-weight:bold;height:25px;\">$lang[prompt]</div>";
	$outmsg.="<div style='padding:20px;font-size:14px;'><img src='images/admin/ok.gif' align='absmiddle' hspace=20 ><span>$msg</span></div>";
	$outmsg.="<div style=\"text-align:center;height:30px;\">$ifjump<a href=\"$basename\">$lang[back]</a></div>";
	$outmsg.="</div>";
	$outmsg.="<script language=\"javascript\">\n
	var left = parent.leftFrame;\n
	left.location.reload();\n
	</script>\n
	";
	echo $outmsg;
	adminbottom();
}

function ifcheck($var,$out){
	global ${$out.'_Y'},${$out.'_N'},$checks;
	if($var){
		${$out.'_Y'}="checked=\"checked\"";
		$checks[$out.'_Y']="checked=\"checked\"";
	}else{
		${$out.'_N'}="checked=\"checked\"";
		$checks[$out.'_N']="checked=\"checked\"";
	}
}

function readlog($filename,$offset=1024000){
	$readb=array();
	if($fp=@fopen($filename,"rb")){
		flock($fp,LOCK_SH);
		$size=filesize($filename);
		$size>$offset ? fseek($fp,-$offset,SEEK_END): $offset=$size;
		$readb=fread($fp,$offset);
		fclose($fp);
		$readb=str_replace("\n","\n<:wind:>",$readb);
		$readb=explode("<:wind:>",$readb);
		$count=count($readb);
		if($readb[$count-1]==''||$readb[$count-1]=="\r"){unset($readb[$count-1]);}
		if(empty($readb)){$readb[0]="";}
	}
	return $readb;
}

function PostLog($log){
	foreach($log as $key=>$val){
		if(is_array($val)){
			$data .= "$key=array(".PostLog($val).")";
		}else{
			$val = str_replace(array("\n","\r","|"),array('','','&#124;'),$val);
			if($key=='password' || $key=='check_pwd'){
				$data .= "$key=***, ";
			}else{
				$data .= "$key=$val, ";
			}
		}
	}
	return $data;
}

function EncodeUrl($url){
	global $sys,$admin_name,$admin_uid;
	$url_a = substr($url,strrpos($url,'?')+1);
	substr($url,-1)=='&' && $url=substr($url,0,-1);
	parse_str($url_a,$url_a);
	$source='';
	foreach($url_a as $key=>$val){
		$source .= $key.$val;
	}
	$posthash=substr(md5($source.$admin_name.$admin_uid.$sys['hash']),0,8);
	$url .= "&verify=$posthash";
	return $url;
}

function PostCheck($verify){
	global $sys,$admin_name,$admin_uid;
	$source='';
	foreach($_GET as $key=>$val){
		if($key!='verify'){
			$source .= $key.$val;
		}
	}
	if($verify!=substr(md5($source.$admin_name.$admin_uid.$sys['hash']),0,8)){
		adminmsg('bad_request');
	}else{
		return true;
	}
}

function checkselid($selid){
	if(is_array($selid)){
		$ret='';
		foreach($selid as $key => $value){
			if(!is_numeric($value)){
				return false;
			}
			$ret .= $ret ? ','.$value : $value;
		}
		return $ret;
	} else{
		return '';
	}
}

/**
 * 后台连续登陆错误次数
 *
 * @param string $filename  日志文件
 * @param string $offset    记录字节数
 * @return array $result    count_F:错误次数  L_T:最后登陆错误时间
 */
function F_L_count($filename,$offset){
	global $onlineip,$timestamp;
	$result=array();
	if($fp=@fopen($filename,"rb")){
		flock($fp,LOCK_SH);
		fseek($fp,-$offset,SEEK_END);
		$readb=fread($fp,$offset);
		fclose($fp);
		$readb=trim($readb);
		$readb=explode("\n",$readb);
		$count=count($readb);
		$tmp=array();
		for($i=$count-1;$i>0;$i--){
			$tmp=explode("|",$readb[$i]);
			if(strpos($readb[$i],"|Logging Failed|$onlineip|")===false){
				continue;
			}elseif($result['count_F']>=10 || $tmp['5']<$timestamp-1200){
				break;
			}
			$result['count_F']++;
			$result['L_T'] < $tmp['5'] && $result['L_T'] = $tmp['5'];
		}
	}
	return $result;
}
?>