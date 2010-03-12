<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */


function __autoload($class_name){
	//加入对文件的判断，如果不存在则输出错误提示

	$clsname=strtolower(substr($class_name,0,strlen($class_name)-6));

	if(file_exists($fpath=ROOT.'system/lib/'.strtolower($class_name).'.php')){
		require($fpath);
	}else{
		if(file_exists($fpath=ROOT.strtolower($clsname).'/core.class.php')){
			require($fpath);
		}else{
			kc_error("The file <strong>{$fpath}</strong> does not exist.");
		}
	}
}
/**

	输出错误提示

	@param string $s        错误提示内容
	@param string $_js      错误提示的同时执行的javascript代码
	@param string $_width   窗口宽度
	@param string $_height  窗口高度
	@return string
*/
function kc_error($s,$_js='',$_width=350,$_height=100){
	if($GLOBALS['action']=='ajax'){
		kc_ajax('Warning!',"<p class=\"k_err\">$s</p>",0,$_js,$_width,$_height);
	}else{

	exit('<html><head><meta http-equiv="Content-Type" content="text/html; charset='.PAGE_CHARSET.'" /><title>ERROR!</title><style>body{font-family:Arial,宋体;font-size:75%;}</style></head>
<body><h2>ERROR!</h2><p>'.$s.'</p></body></html>');
	}
}
/**

	显示帮助提示图标

	@param string $module  帮助内容所在的模块目录
	@param string $path    对应语言包里的路径
	@param int    $width   帮助窗口的长度
	@param int    $height  帮助窗口的高度

	@return string

*/
function kc_help($path,$width=320,$height=265){
	$s='';
	if(isset($path{0})){
		$s="<a class=\"k_help\" href=\"javascript:;\" title=\"Help!\" rel=\"{URL:'../system/manage.php',ID:'k_help_Fly',CMD:'help',path:'$path',IS:2}\"><img class=\"g6 os\" src=\"../system/images/white.gif\"/></a>";
	}
	return $s;
}
/**

	k_ajax弹出窗口

	@param string $title    k_ajax标题
	@param string $main     主体内容
	@param string $but      对话框底部按钮
	@param string $js       激活被执行的javascript代码
	@param int    $width    窗口宽度
	@param int    $height   窗口高度

	@return string

*/
function kc_ajax($title,$main,$but=0,$js='',$width=320,$height=100){
	global $king;

	$s='';
	if($but===0){
		$s.='<a href="javascript:;" class="k_close">'.$king->lang->get('system/common/enter').'</a>';
	}elseif($but===1){
		$_url=$_SERVER['HTTP_REFERER'];
		if(isset($_url{0})){
			$s.='<a href="'.$_url.'" class="k_goto" rel="{URL:\\\''.$_url.'\\\'}">';//eval() ? $_url='eval(parent.location=\''.$_url.'\')'
		}else{
			$s.='<a href="javascript:;" class="k_close">';
		}
		$s.=$king->lang->get('system/common/enter').'</a>';
	}else{
		$s.=addslashes($but);
	}
	$title=addslashes($title);
	$main=str_replace(array("\n",chr(13)),array('\n',''),addslashes($main));
	$but=addslashes($but);
	$js=str_replace(array("\n",chr(13)),array('\n',''),addslashes($js));
//	$js=addslashes($js);
	$s="{title:'$title',main:'$main',but:'$s',js:'$js',width:$width,height:$height}";
	exit($s);
}
/**
	显示搜索表单
	@param string $s 表单内容
	@return string
*/
function kc_ajax_query($str){

	$str="<div id=\"k_search\">{$str}</div>";
	$js="\$('#k_form_list').prepend('$str');$.kc_close();";

	kc_ajax('','','',$js);
}
/**
	程序错误提示输出

	@param string $file  : __FILE__
	@param string $line  : __LINE__

*/
function kc_clew($file,$line,$msg=null){
	$s="<p class=\"red\">File:".basename($file).";<br/>Line:{$line}</p>";
	isset($msg{0}) && $s.="<p>$msg</p>";
	return $s;
}

/**

	去掉转义符

	@param string|array $_data 要转义的数组或字符串

	@return string

*/
function kc_stripslashes_array(&$_data){
	if (is_array($_data)){
		foreach ($_data as $_key => $_value){
			$_data[$_key]=kc_stripslashes_array($_value);
		}
		return $_data;
	}else{
		return stripslashes($_data);
	}
}
/**
	页面加载
*/
function kc_pageLoad(){
	//去掉转义字符
	if (KC_MAGIC_QUOTES_GPC){
		$_GET=kc_stripslashes_array($_GET);
		$_POST=kc_stripslashes_array($_POST);
		$_COOKIE=kc_stripslashes_array($_COOKIE);
		$array=array('PHP_SELF','SCRIPT_URI','QUERY_STRING','PATH_INFO','PATH_TRANSLATED');
		foreach($array as $val){
			if(isset($_SERVER[$val]))
				$_SERVER[$val]=htmlspecialchars($_SERVER[$val]);
		}
	}

	//设置ismethod值 true:post ; false:get
	$ismethod=kc_post('METHOD') ? True : False;
	$GLOBALS['ismethod']=!($_SERVER['REQUEST_METHOD']=='GET' || $ismethod);
}
/**
	记录调试
*/
function kc_error_handler($type,$msg,$file,$line){
//	if(!in_array($type,array(E_NOTICE))){//E_USER_ERROR,E_USER_WARNING,E_USER_NOTICE,E_WARNING,E_PARSE,E_CORE_ERROR,E_CORE_WARNING
		global $king;


		if(!DEBUG) return;//关闭错误记录的话……直接退出
		echo "<h2>$msg</h2><p>$file - $line</p>";
		if(!$king->db->link) return;//如果没有数据库链接则直接退出


/*
		//需要忽略的提示
		$ignore=array('Undefined index');
		foreach($ignore as $val){
			if(substr($msg,0,strlen($val))==$val)
				return;
		}
*/
		//读取数据库链接，当数据库链接可用的时候，写系统错误记录
		$file=substr($file,strlen(ROOT));
		$msg=strip_tags(str_replace(ROOT,'',$msg));
		$array=array(
			'ntype'=>$type,
			'kmsg'=>$msg,
			'kfile'=>$file,
			'nline'=>$line,
			'ndate'=>time(),
			'kurl'=>substr($_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] :''),0,255),
		);
		$king->db->insert('%s_event',$array);
}
/**

	字符串类型验证

	@param string     $s      要验证的字符串数据
	@param int|string $_type  数据类型
		0 : 任意字符
		1 : 数字和字母
		2 : 数字,无符号
		3 : 数字，逗号
		4 : 数字字母和下划线，中划线会有问题
		5 : 邮箱验证
		6 : 网址
		7 : 完整http图片地址+本地图片完整路径
		8 : 日期+时间 类型
		9 : 日期 类型
		10: 版本号 1.0.23
		13: 颜色值
		22: 数字,有符号
		23: 第一个字符是字母,后面的由数字、字母和下划线构成
		24: 数字、字母和中下划线,用在目录验证
		25: base64_encode转换后的数据
		33: 带符号的数字组合,逗号分开
		default: 自定义正则验证

	@return bool

*/
function kc_validate($s,$_type){
	switch($_type){
		case 1:$_reg='/^[a-zA-Z0-9]+$/';break;
		case 2:$_reg='/^[0-9]+$/';break;
		case 3:$_reg='/^([0-9\.]+\,?)+$/';break;
		case 4:$_reg='/^[A-Za-z0-9\_]+$/';break;
		case 5:
			$_reg='/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';break;
		case 6:
			//$_reg='/^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\.\=\?\+\-~`@\:!%#]|(&)|&)+/';
			$_reg='/^[a-zA-Z]{3,10}:\/\/[^\s]+$/';
			break;
		case 7:
			global $king;
//			$_bool=in_array(kc_f_ext($s),explode('|',$king->config('upimg')));
//			retrun $_bool;
			$_reg='/^([a-zA-Z]{3,10}:\/\/)?[^\s]+\.('.$king->config('upimg').')$/';
			//$_reg='/^((http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\.\=\?\+\-~`@\:!%#]|(&)|&)+|([\w\/\\\.\=\?\+\-~`@\':!%#]|(&)|&)+)\.('.$king->config('upimg').')$/';
			break;//jpeg|jpg|gif|png|bmp
		case 8:
			$_reg='/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/';break;
		case 9:
			$_reg='/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29))$/';break;
		case 10:$_reg='/^\d?\.\d?\.\d{4}$/';break;
		case 13:$_reg='/^#?[0-9A-Fa-f]{6}$/';break;
		default:$_reg=$_type;
		case 22:$_reg='/^\-?[0-9]+$/';break;
		case 23:$_reg='/^[a-zA-Z][a-zA-Z0-9\_]*/';break;
		case 24:$_reg='/^[a-zA-Z0-9-_]+$/';break;
		case 25:$_reg='/[a-zA-Z0-9\+\%]+(\=)*$/';break;
		case 33:$_reg='/^(\-?[0-9]+\,?)+$/';break;
		default:$_reg=$_type;
	}

	$_bool= $_type==0 ? true : preg_match($_reg,$s);
	return $_bool;
}
/**

	表单验证

	@param array $_array	二维数组,大体结构如下：

		$_array=array(
			array($formname , $type   , $tip   ,  exp),
			array(表单名称,   验证类型  错误提示  扩展
			array(...),
			...
		)
		@param string     $formname    表单名称
		@param int|string $type        表单数据类型
			0 :
					扩展部分为空时，不能为空
					扩展部分不为空时，限定字符串长度 [,最小长度,最大长度] 如： array('adminname',0,'长度不正确' [ ,2,12] )

					举例：
						array1('title',0,null,1,100)//输出默认错误提示，字符串长度为1-100
						array1('title',0,1,100)//同上，简化了null

						array1('title',0)//不能为空

			1 : 必须由 字母 和 数字 组成
			2 : 必须为 整数
			3 : 数字，逗号
			4 : 数字、字母和下划线
			5 : 邮箱
			6 : 网址
			7 : 图片网址
			8 : 日期+时间 验证
			9 : 日期 验证

			12: True/False判断，True的时候输出错误提示 [,True或False]
			13: 颜色值
			14: 不能包含特殊规定的符号或单词，数组类型
			15: 不能包含特殊符号['\:*?<>|;,]，并开头不能用斜线/，用来判断文件路径及类型
			16: 必须为数字，并且数字有取值范围 [,最小值,最大值]
			17: 重复密码验证 [,表单名称]
			18: 保留对象,即不能等于,数组类型
			19: 必须包含的值
			20: 必须为指定范围的值，即设定取值范围,和18相反
			21: html提交框的安全验证
			22: 数字,有符号
			23: 必须为指定范围的值，提交值之间用逗号分开，用来验证多选项目的值
			24: 保留用户名
			33: 带符号的数字组合，逗号分开
			default: 自定义正则验证，如：array(表单名称, 正则表达式,错误提示)
		@param string $tip         错误提示，不填写则输出系统预置的提示

	@return string

*/

function kc_check($_array){
	global $king;

	$post='';

	$_errlang='<span class="k_error">%s</span>';

	if(!$GLOBALS['ismethod']){
		$GLOBALS['ischeck']=False;
		//如果在这里返回javascript来进行数据验证，结果会更加完美。
		return '';
	}

	for($i=0;$i<count($_array);$i++){
		$post_str=kc_post($_array[$i][0]);//获得POST对应值

		$post=is_array($post_str) ? implode(',',$post_str) : $post_str;//判断提交值是否为数组类型

		if(empty($post) && !in_array($_array[$i][1],array(0,12))) break;//提交值为空 并 验证类型 不等于长度验证 或 是否验证 的时候，无需验证

		switch($_array[$i][1]){
		case 0 :
			if(count($_array[$i])==3||count($_array[$i])==2){
				$_is=!(kc_strlen($post)>0);
				$_array[$i][1]='00';
			}elseif(count($_array[$i])==4){
				$_is=(kc_strlen($post)<$_array[$i][2]||kc_strlen($post)>$_array[$i][3]);
				$_array[$i][2]=null;
			}elseif(count($_array[$i])==5){
				$_is=(kc_strlen($post)<$_array[$i][3]||kc_strlen($post)>$_array[$i][4]);
			}else{
				$_is=True;
			}
			break;
		case 1 :$_is=(!kc_validate($post,1));break;
		case 2 :$_is=(!kc_validate($post,2));break;
		case 3 :$_is=(!kc_validate($post,3));break;
		case 4 :$_is=(!kc_validate($post,4));break;
		case 5 :$_is=(!kc_validate($post,5));break;
		case 6 :$_is=(!kc_validate($post,6));break;
		case 7 :
			$filetype=$king->config('upimg');
			$_is=!(in_array(kc_f_ext($post),explode('|',$filetype)));
		break;

		case 8 :$_is=(!kc_validate($post,8));break;
		case 9 :$_is=(!kc_validate($post,9));break;

		case 12:$_is=$_array[$i][3];break;
		case 13:$_is=(!kc_validate($post,13));break;

		case 14:
//			if($_array[$i][3]){//首先，要保证这个数组成立??空数组？
				$array=$_array[$i][3];
				foreach($array as $val){
					if(kc_validate(stripos($post,$val),2)){
						$_is=True;
						break;
					}
				}
//			}
		break;

		case 15:
			$_arr_str1=array('/','.');
			$s2='*?<>|;,\'!~$#@^(){}=+%';

			if(count($_array[$i])==4){
				$filetype=$_array[$i][3];
			}else{
				$filetype=$king->config('templateext');
			}

			$_is=in_array(substr($post,0,1),$_arr_str1);
			//如果$_is=True 或 最后一个为/，则没有必要继续验证
			if( (!$_is) && substr($post,-1,1)!='/' && isset($post{0}) ){
				$_is=!( in_array( kc_f_ext($post), explode('|',$filetype) ) );
			}
			//路径中不能包含.php
			if(!$_is){
				$_is=kc_validate(stripos($post,'.php'),2);
			}
			//如果$_is=True，则没有必要继续验证
			if(!$_is){
				for($j=0;$j<strlen($s2);$j++){
					if(false!==(strpos($post,$s2{$j}))){
						$_is=True;
						break;
					}
				}
			}
		break;

		case 16:$_is=($post<$_array[$i][3]||$post>$_array[$i][4]);break;
		case 17:$_is=!($post==stripslashes($_POST[$_array[$i][3]]));break;
		case 18:$_is=in_array($post,$_array[$i][3]);break;
		case 19:$_is=!kc_validate(strpos($post,$_array[$i][3]),2);break;
		case 20:$_is=!in_array($post,$_array[$i][3]);break;
		case 21:$_is=preg_match("/(<(\/?)(script|base|iframe|style|html|body|title|link|meta|\?|\%)([^>]*?)>|(<[^>]*)\son[a-zA-Z]+\s*=([^>]*>))/isU",$post);break;
		case 22 :$_is=(!kc_validate($post,22));break;
		case 23:
//			$post_str
			$array_post=is_array($post_str)?$post_str:explode(',',$post);
//		$_is=False;
			foreach($array_post as $val){
				if(!in_array($val,$_array[$i][3])){
					$_is=True;break;
				}
			}
		case 33 :$_is=(!kc_validate($post,33));break;
		break;

		default:
			$_is=(!kc_validate($post,$_array[$i][1]));
			$_array[$i][1]='999';
		}
		if($_is){	//上面得到的$_is为True的时候，提示错误
			$GLOBALS['ischeck']=False;
			$GLOBALS['check_num']++;
			$_tip=isset($_array[$i][2])
				? $_array[$i][2]
				: $king->lang->get('system/check/e'.$_array[$i][1]);
			return sprintf($_errlang,$_tip);
		}
	}
}

/**
	可以处理中文的substr函数,中文占两个字符处理
	@param $str,$from,$len 参数和substr一致
	@return string
*/
function kc_substr($str,$from,$len){
	preg_match_all('#(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)#s',$str,$array, PREG_PATTERN_ORDER);

	$from1=0;$len1=0;
	$s='';
	foreach($array[0] as $key => $val){
		$n=ord($val)>=128 ? 2:1;
		$from1+=$n;
		if($from1>$from){
			$len1+=$n;
			if($len1<=$len){
				$s.=$val;
			}else{
				return $s.'..';
			}
		}
	}
	return $s;
}
/**
	返回文字长度,中文占两个字符
	@param string $str
	@return int
*/
function kc_strlen($str){
	preg_match_all ('#(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)#s',$str,$array, PREG_PATTERN_ORDER);
	$len=0;
	foreach($array[0] as $val){
		$len+=ord($val)>=128 ?2:1;
	}
	return $len;
}
/**
	图像处理
	@param string $s       图片路径
	@param array  $attrib  图片属性
		需要有width和height属性
		外加一些滤镜
	@return string 返回缩略图地址
*/
function kc_image($s,$attrib){

	global $king;

	$width=kc_val($attrib,'width');
	$height=kc_val($attrib,'height');
	$position=kc_val($attrib,'position');

	if(!is_file(ROOT.$s))//确定文件存在
		return $s;
	list($_width, $_height)=getimagesize(ROOT.$s);

	if($_width && $_height){
		$width=kc_validate($width,2)?$width:120;
		$height=kc_validate($height,2)?$height:90;
	}else{
		return $s;
	}

	$newdir=dirname($s)."/thumb_{$width}x{$height}";//新目录
	$newimg=$newdir.'/'.basename($s);//新文件

/**/
	//如果缩略图文件存在，则直接返回缩略图文件地址
	if(is_file(ROOT.$newimg)){
		return $king->config('inst').$newimg;
	}
/**
 * 如果加入截图位置的话，
 * location=top,left,right,bottom,topleft,topright,leftbottom,rightbottom,
 */
	if($_width/$_height>=$width/$height){
		$w=round(($_width/$_height)*$height);
		$h=$height;
		$x=round(($width-$w)/2);
		$y=0;
	}else{
		$w=$width;
		$h=round(($_height/$_width)*$width);
		$x=0;
		$y=round(($height-$h)/2);
	}

	switch ($position){
		case 'top':$y=0;break;
		case 'left':$x=0;break;
		case 'right':$x=$x*2;break;
		case 'bottom':$y=$y*2;break;

		case 'lefttop':$x=0;$y=0;break;
		case 'topleft':$x=0;$y=0;break;

		case 'righttop':$x=$x*2;$y=0;break;
		case 'topright':$x=$x*2;$y=0;break;

		case 'leftbottom':$x=0;$y=$y*2;break;
		case 'bottomleft':$x=0;$y=$y*2;break;

		case 'rightbottom':$x=$x*2;$y=$y*2;break;
		case 'bottomright':$x=$x*2;$y=$y*2;break;

		default:
			if (kc_validate($position,'/^\-?\d+\,\-?\d+$/')){
				list($x,$y)=explode(',',$position);
			}

	}

	$ext=kc_f_ext($s);

	if(in_array(strtolower($ext),array('jpg','jpeg','gif','png'))){
		$fext=$ext=='jpg'?'jpeg':strtolower($ext);
	}else{
		return $s;
	}

	$func='imagecreatefrom'.$fext;
	$source=$func(ROOT.$s);
	$im=imagecreatetruecolor($width, $height);

	//拷贝图像新建
	imagecopyresampled($im,$source,$x,$y,0,0,$w,$h,$_width,$_height);

	//重设长宽，减1是位置像素问题
	$w1=$width-1;
	$h1=$height-1;

	//置空
	$empty=kc_val($attrib,'empty');
	if(isset($empty{0})){

		$c1=kc_validate(kc_val($attrib,'empty-color'),13)?$attrib['empty-color']:'FFFFFF';//颜色
		$s1=kc_validate(kc_val($attrib,'empty-size'),2)?$attrib['empty-size']:0;//边框的厚度
		$r1=kc_validate(kc_val($attrib,'empty-r'),2)?$attrib['empty-r']:5;//圆角(半径)
		$empty_filter=kc_val($attrib,'empty-filter');//滤镜
		$empty_filter_color=kc_validate(kc_val($attrib,'empty-filter-color'),13) ? $attrib['empty-filter-color'] : '#666666';//颜色

		$color=kc_hex2rgb($im,$c1);//转换颜色

		for($i=0;$i<$s1;$i++){
			imagerectangle($im,$i,$i,$w1-$i,$h1-$i,$color);//方框填充
		}

		switch(strtolower($empty)){
			case 'rectangle'://矩形

				//滤镜
				switch(strtolower($empty_filter)){
					case 'shadow':
						for($i=0;$i<$s1;$i++){
							$color=kc_hex2rgb($im,$empty_filter_color,(1-$i/$s1)*100);
							imageline($im,$s1+$i,$h1-$s1+$i,$w1-$s1+$i,$h1-$s1+$i,$color);
							imageline($im,$w1-$s1+$i,$s1+$i,$w1-$s1+$i,$h1-$s1+$i,$color);
						}
					break;
				}

			break;

			case 'fillet'://圆角矩形
				$d1=$s1+$r1-1;//位置

				for($i=0;$i<$r1+$s1;$i++){
					//画ARC
					$r=($r1+$i)*2-1;
					imagearc($im, $d1,    $d1,    $r,$r, 180,270,$color);//左上
					imagearc($im, $w1-$d1,$d1,    $r,$r, 270,360,$color);//右上
					imagearc($im, $w1-$d1,$h1-$d1,$r,$r,   0, 90,$color);//右下
					imagearc($im, $d1,    $h1-$d1,$r,$r,  90,180,$color);//左下
				}

				//滤镜
				switch(strtolower($empty_filter)){
					case 'shadow':
						for($i=0;$i<$s1;$i++){
							$r=($r1+$i)*2-1;
							$color=kc_hex2rgb($im,$empty_filter_color,(1-$i/$s1)*100);
							imageline($im,$d1+$i,$h1-$s1+$i,$w1-$d1,$h1-$s1+$i,$color);
							imageline($im,$w1-$s1+$i,$d1+$i+1,$w1-$s1+$i,$h1-$d1,$color);
							imagearc($im, $w1-$d1,$h1-$d1,$r,$r,   0, 90,$color);//右下

						}
					break;
				}

			break;

			case 'chamfer'://斜切矩形
				$d1=$s1+$r1-1;//位置

				for($i=0;$i<$r1;$i++){
					imageline($im,$w1-$d1+$i,$s1,$w1-$s1,$d1-$i,$color);//右上
					imageline($im,$w1-$s1,$h1-$d1+$i,$w1-$d1+$i,$h1-$s1,$color);//右下
					imageline($im,$d1-$i,    $h1-$s1,$s1,    $h1-$d1+$i,$color);//左下
					imageline($im,$s1,    $d1-$i,$d1-$i,    $s1,$color);//左上
				}

				//滤镜
				switch(strtolower($empty_filter)){
					case 'shadow':
						for($i=0;$i<$s1;$i++){
							$r=($r1+$i)*2-1;
							$color=kc_hex2rgb($im,$empty_filter_color,(1-$i/$s1)*100);
							imageline($im,$d1+$i+1,$h1-$s1+$i,$w1-$d1,$h1-$s1+$i,$color);
							imageline($im,$w1-$s1+$i,$d1+$i+1,$w1-$s1+$i,$h1-$d1,$color);
							imageline($im,$w1-$s1+$i,$h1-$d1,$w1-$d1,$h1-$s1+$i,$color);//右下

						}
					break;
				}

			break;

		}
	}

	//border
	$border=kc_val($attrib,'border');
	if(isset($border{0})){

		$c1=kc_validate(kc_val($attrib,'border-color'),13)?$attrib['border-color']:'000000';//颜色
		$m1=kc_validate(kc_val($attrib,'border-margin'),2)?$attrib['border-margin']:0;//移位
		$r1=kc_validate(kc_val($attrib,'border-r'),2)?$attrib['border-r']:5;//圆角(半径)
		$s1=kc_validate(kc_val($attrib,'border-size'),2)?$attrib['border-size']:1;//宽度

		$color=kc_hex2rgb($im,$c1);//转换颜色

		switch(strtolower($border)){
			case 'rectangle':
				for($i=0;$i<$s1;$i++){//边框要一层层加
					imagerectangle($im,$i+$m1,$i+$m1,$w1-$i-$m1,$h1-$i-$m1,$color);
				}
			break;

			case 'fillet'://圆角矩形

				$d1=$m1+$r1;//设置距离
				for($i=0;$i<$s1;$i++){//边框要一层层加
					$d=$r1*2-$i*2;//直径
					//画线
					imageline($im,$d1,    $m1+$i,    $w1-$d1,$m1+$i,    $color);//上
					imageline($im,$w1-$m1-$i,$d1,    $w1-$m1-$i,$h1-$d1,$color);//右
					imageline($im,$d1,    $h1-$m1-$i,$w1-$d1,$h1-$m1-$i,$color);//下
					imageline($im,$m1+$i,    $d1,    $m1+$i,    $h1-$d1,$color);//左
					//画ARC
					imagearc($im,$d1,    $d1,    $d,$d,180,270,$color);//左上
					imagearc($im,$w1-$d1,$d1,    $d,$d,270,360,$color);//右上
					imagearc($im,$w1-$d1,$h1-$d1,$d,$d,  0, 90,$color);//右下
					imagearc($im,$d1,    $h1-$d1,$d,$d, 90,180,$color);//左下
				}
			break;

			case 'chamfer'://斜切矩形
				$d1=$m1+$r1-1;//设置距离
				for($i=0;$i<$s1;$i++){//边框要一层层加
					$d=$r1*2-$i*2;//直径
					//画线
					imageline($im,$d1,    $m1+$i,    $w1-$d1,$m1+$i,    $color);//上
					imageline($im,$w1-$m1-$i,$d1,    $w1-$m1-$i,$h1-$d1,$color);//右
					imageline($im,$d1,    $h1-$m1-$i,$w1-$d1,$h1-$m1-$i,$color);//下
					imageline($im,$m1+$i,    $d1,    $m1+$i,    $h1-$d1,$color);//左
				}
				$s2=ceil(sqrt(2)*$s1);
				for($i=0;$i<$s2;$i++){//边框要一层层加
					imageline($im,$w1-$d1-$i,$m1,$w1-$m1,$d1+$i,$color);//右上
					imageline($im,$w1-$m1,$h1-$d1-$i,$w1-$d1-$i,$h1-$m1,$color);//右下
					imageline($im,$d1+$i,    $h1-$m1,$m1,    $h1-$d1-$i,$color);//左下
					imageline($im,$m1,    $d1+$i,$d1+$i,    $m1,$color);//左上
				}
			break;
		}
	}

	//水印
	$watermark=kc_val($attrib,'watermark');
	if(isset($watermark{0})){
		//先判断文件是否存在
		if(is_file(ROOT.$watermark)){
			list($_width_water,$_height_water)=getimagesize(ROOT.$watermark);

			if(kc_validate($_width_water,2)&&kc_validate($_height_water,2)){

				$wx=kc_validate(kc_val($attrib,'watermark-x'),2)?$attrib['watermark-x']:10;
				$wy=kc_validate(kc_val($attrib,'watermark-y'),2)?$attrib['watermark-y']:10;
				$pct=kc_validate(kc_val($attrib,'watermark-opacity'),2)?$attrib['watermark-opacity']:100;
				$water_color=kc_val($attrib,'watermark-color') ? $attrib['watermark-color']:'FFFFFF';//透明色

				$ext_water=kc_f_ext($watermark);

				if(in_array(strtolower($ext_water),array('jpg','jpeg','gif','png'))){
					$fext_water=$ext_water=='jpg'?'jpeg':strtolower($ext_water);
				}

				$func_water='imagecreatefrom'.$fext_water;
				$water=$func_water(ROOT.$watermark);//装载水印图片

				$color=kc_hex2rgb($water,$water_color);//转换颜色
				imagecolortransparent($water,$color);
				imagecopymerge($im,$water,$wx,$wy,0,0,$_width_water,$_height_water,$pct); // 左上

			}
		}
	}

	//插入字体
	$text=kc_val($attrib,'text');
	if(isset($text{0})){
		$tx=kc_validate(kc_val($attrib,'text-x'),2)?$attrib['text-x']:10;
		$ty=kc_validate(kc_val($attrib,'text-y'),2)?$attrib['text-y']:20;
		$s1=kc_validate(kc_val($attrib,'text-size'),2)?$attrib['text-size']:12;
		$a1=kc_validate(kc_val($attrib,'text-angle'),2)?$attrib['text-angle']:0;
		$f1=is_file(ROOT.kc_val($attrib,'text-font'))?$attrib['text-font']:'system/verify_font/MilkCocoa.TTF';
		$c1=kc_validate(kc_val($attrib,'text-color'),13)?$attrib['text-color']:'000000';//颜色

		$color=kc_hex2rgb($im,$c1);//转换颜色

		imagettftext($im,$s1,$a1, $tx,$ty,$color,ROOT.$f1,$text);

	}

	//开启抗锯齿
	imageantialias($im,True);

	$func='image'.$fext;
	kc_f_md($newdir);//创建目录

	if($func($im,ROOT.$newimg)){//保存缩略图
		$s=$newimg;
	}

	return $king->config('inst').$s;
}
/**
	颜色值转换为RGB
	@param resource $img   图片资源
	@param string   $color 常规的HTML颜色
	@param int      $pct   不透明度
	@return resource
*/
function kc_hex2rgb($img,$color,$pct=100){
	$color= $color{0}=='#'?substr($color,1):$color;

	if(strlen($color)!=6){
		return imagecolorallocate($img,0,0,0);
	}else{
		$color=strtoupper($color);
	}

	$array=array();
	for ($i=0;$i<3;$i++){
		$n=hexdec(substr($color,(2*$i),2));
		$array[$i]=round($n +(255-$n)*(1-$pct/100));
	}
//kc_error(print_r($array,1));
	return imagecolorallocate($img,$array[0],$array[1],$array[2]);
}

/**

	进度条界面

	进度条
	@param string $id        进度条的ID
	@param string $title     进度条中显示的内容
	@param int    $num       当前项目数
	@param int    $count     项目总数
	@param string $body      moreinfo中输出的内容

	@$_GET['time'] 函数外参数,页面开始执行时间,必须值
*/
function kc_progress($id='progress',$title=null,$num=0,$count=1,$body=''){
	global $king;

	if(!$title)
		$title=$king->lang->get('system/progress/loading');

	list($_msec,$_sec)=explode(' ',microtime());
	$thistime=$_sec+$_msec;

	switch($num){
		case 0:
			$count===0
				? $s='<script>window.parent.$.kc_progress(\''.$id.'\',\''.$king->lang->get('system/progress/ok').' ('.$king->lang->get('system/progress/not').')\',300)</script>'
				: $s='<p class="k_progress" id="'.$id.'"><label>'.$title.'</label><span><em style="width:0px;"></em></span><var>0%</var></p>';
		break;

/*
		case $num>=$count:
			$diffstart=$thistime-$_GET['time'];//开始时间差
			$str=$king->lang->get('system/progress/alltime').': '.kc_formattime($diffstart);
			$s='<script>window.parent.$.kc_progress(\''.$id.'\',\''.$king->lang->get('system/progress/ok').'\',\''.$count.'/'.$count.' '.$str.'\',500);$(\'body\').prepend(\''.addslashes($body).'\')</script>';
		break;
*/
		default:

			//判断客户端的链接状态
			if(connection_aborted()) exit;

			$prop=$num/$count;

			$percent=round($prop*100,1);//百分比

			list($_msec,$_sec)=explode(' ',microtime());
			$time=$_sec+$_msec;//当前时间

			if(empty($GLOBALS['KC_PROGRESS_TIME'])){//有上次执行时间
				$GLOBALS['KC_PROGRESS_TIME']=$GLOBALS['KC_START_SCRIPT_RUNTIME'];
			}
			$timediff=$time-$GLOBALS['KC_PROGRESS_TIME'];//单次运行时差
			$GLOBALS['KC_PROGRESS_TIME']=$time;//设置时间

			$diff_this=$time-$_GET['time'];//当前到最初的时间差

			$str=$king->lang->get('system/progress/remainder').': '.kc_formattime(($diff_this/$num)*($count-$num));

			$s='<script>window.parent.$.kc_progress(\''.$id.'\',\''.addslashes($title).'\',\''.$num.'/'.$count.' '.$str.' - '.$percent.'%\','.round($prop*500).');$(\'body\').prepend(\''.addslashes($body).'\')</script>';

			usleep((int)($king->config('proptime')*$timediff*1000000));

			list($_msec,$_sec)=explode(' ',microtime());
			$GLOBALS['KC_PROGRESS_TIME']=$_sec+$_msec;//重新设置时间
	}

	$GLOBALS['KC_LAST_PROGRESS_RUNTIME']=$thistime;//设置当前时间

	return $s.NL;
}
/**
 * 抓图
 * param string  $s  HTML代码
 * return string
 */
function kc_grab($s){
	$s=preg_replace_callback('/(<img([^>]*))( src=)(["\'])(.*?)\4(([^>]*)\/?>)/is','kc_grab_regexcallback',$s);
	return $s;
}
function kc_grab_regexcallback($m){
	global $king;

	if(!kc_validate($m[5],7)){
		return $m[0];
	}

	if($path=kc_grab_get($m[5])){
		if($m[5]==$path){//如果录入的和返回的路径一样的话，存储失败
			return $m[0];
		}else{
			return $m[1].$m[3].$m[4].$king->config('inst').$path.$m[4].$m[6];
		}
	}else{
		return $m[0];
	}

	preg_match_all('/([\w\-]+)=(["\'])(.*?)\2/s', $m[0], $array, PREG_SET_ORDER);

	$newArray=array();
	if(is_array($array)){
		foreach($array as $val){
			$newArray+=array(strtolower($val[1])=>strtolower($val[3]));
		}
	}

}
function kc_grab_get($imgpath){
	global $king;
	if($img=file_get_contents($imgpath)){
		$fext=kc_f_ext($imgpath);//扩展名
		$date=kc_formatdate(time(),'Y-m-d h:i');
		list($msec,$sec)=explode(' ',microtime());
		$path=$king->config('uppath').'/image/'.kc_formatdate(time(),'Y/m/d/h/i').'/'.kc_random(3).round($msec*1E6).'.'.$fext;
		if(kc_f_put_contents($path,$img)){//写文件成功
			return $path;
		}else{
			return $imgpath;
		}

	}else{
		return False;
	}
}
/**
	POST/GET方式提交参数 抓页面
	@param string  $url    目标站网址
	@param array   $send   提交的参数值
	@param string  $charset 编码，默认是UTF-8，gb就写GBK
	@param string  $cookie  伪造Cookie
	@param int     $timeout 超时时间
	@return string
*/
function kc_fopen($url,$send='',$charset='UTF-8',$cookie='',$timeout=15){
	$purl=parse_url($url);
	$host=$purl['host'];
	$path=$purl['path'] ? $purl['path'].(isset($purl['query']) ? '?'.$purl['query'] : '') : '/';
	$port = !empty($purl['port']) ? $purl['port'] : 80;

	if($send){

		$array=array();
		foreach($send as $key => $val){
			$array[]=$key.'='.urlencode( $charset=='UTF-8' ? $val : iconv('UTF-8',$charset,$val) );
		}
		$str=implode('&',$array);

		$s="POST $path HTTP/1.0\r\n";
		$s.="Accept: */*\r\n";
		$s.="Referer: $url\r\n";
		$s.="Accept-Language: zh-cn\r\n";
		$s.="Content-Type: application/x-www-form-urlencoded\r\n";
		$s.="User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
		$s.="Host: $host\r\n";
		$s.="Content-Length: ".strlen($str)."\r\n";
		$s.="Connection: Close\r\n";
		$s.="Cache-Control: no-cache\r\n";
		$s.="Cookie: $cookie\r\n\r\n";
		$s.=$str;

	}else{
		$s="GET $path HTTP/1.0\r\n";
		$s.="Accept: */*\r\n";
		$s.="Referer: $url\r\n";
		$s.="Accept-Language: zh-cn\r\n";
		$s.="User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
		$s.="Host: $host\r\n";
		$s.="Connection: Close\r\n";
		$s.="Cookie: $cookie\r\n\r\n";
	}

	if( ($fp=fsockopen($host,$port,$errno,$errstr,$timeout)) !== false){

		stream_set_blocking($fp, true);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $s);
		$status=stream_get_meta_data($fp);

		if(!$status['timed_out']) {


	//		fputs ($fp, $s);
			$s='';

			do{
				$s.=fgets($fp,4096);
			}while(strpos($s,"\r\n\r\n")===false);

			$s='';
			while(false===feof ($fp)){
				$s.=fread( $fp, 8192 );
			}

			return $s;
		}else{
			return "Timed out!";
		}

	}
	return false;
}
/* ------>>> GET 函数 <<<---------------------------- */
/**
	返回语言说明，如：简体中文
	@param string $_lang   语言代号，一般是语言包文件名(无扩展名)
	@return string
*/
function kc_getlang($lang){
	$array=array(
		'ar'=>'Arabic','bg'=>'Bulgarian','bs'=>'Bosnian','ca'=>'Catalan','cs'=>'Czech',
		'da'=>'Danish','de'=>'German','el'=>'Greek','en'=>'English','en-au'=>'English (Australia)',
		'en-uk'=>'English (United Kingdom)','eo'=>'Esperanto','es'=>'Spanish','et'=>'Estonian',
		'eu'=>'Basque','fa'=>'Persian','fi'=>'Finnish','fr'=>'French','gl'=>'Galician','he'=>'Hebrew',
		'hr'=>'Croatian','hu'=>'Hungarian','it'=>'Italian','ko'=>'Korea','lt'=>'Lithuanian','nl'=>'Dutch',
		'no'=>'Norwegian','pl'=>'Polish','pt'=>'Portuguese (Portugal)','pt-br'=>'Portuguese (Brazil)',
		'ro'=>'Romanian','ru'=>'Russian','sk'=>'Slovak','sl'=>'Slovenian','sr'=>'Serbian (Cyrillic)',
		'sr-latn'=>'Serbian (Latin)','sv'=>'Swedish','th'=>'Thai','tr'=>'Turkish','uk'=>'Ukrainian',
		'zh'=>'繁體中文','zh-cn'=>'简体中文','ja'=>'日本語');
	return isset($array[$lang]) ? $array[$lang] : $lang;
}
/**

	获得对应的id值，如果GET过程中没有则查找POST，如果都没有，则返回0，如果有就以$_type来验证数据类型

	@param string $name    要获取的参数key
	@param int    $type  数据类型验证，详情请见kc_validate()
	@param bool   $is     true的时候，必须有值

	@return string

*/
function kc_get($name,$type=2,$is=0){
	global $king;

	$val=isset($_GET[$name]) ? $_GET[$name] :'';
	if(!isset($val{0}))
		$val=isset($_POST[$name]) ? $_POST[$name] : '';
	if(isset($val{0})){
		if(kc_validate($val,$type)){
			$_getid=$val;
		}else{
			kc_error($king->lang->get('system/error/param').'<br/>ID:'.$name.';Value:'.$val);
		}
	}else{
		if($is){
			kc_error($king->lang->get('system/error/not').': '.$name);
		}else{
			$_getid='';
		}
	}
	return $_getid;
}
/**
	获得post表单，和kc_get相似
	@param string $name  要获取的参数key
	@param int    $type  数据类型验证，详情请见kc_validate()
	@param bool   $is     true的时候，必须有值
	@return string
*/
function kc_post($name,$type=0,$is=0){
	global $king;

	$post=isset($_POST[$name]) ? $_POST[$name] : '';

	if(isset($post{0})){//如果有值，则判断类型
		if(!kc_validate($post,$type)){
			kc_error($king->lang->get('system/error/param').'<br/>ID:'.$name.';Value:'.$post);
		}
	}

	if($is && !isset($post{0})){//要求有值的时候判断
		kc_error($king->lang->get('system/error/not').': '.$name);
	}

	return $post;

}
/**
	数组中获得值
	@param array  $array  有值的数组
	@param string $val    键值
	@param string $def    如果数组中没有键值的时候，返回这个值
	return string
*/
function kc_val($array,$val,$def=''){
	$str='';
	if(is_array($array)){
		$str=isset($array[$val])?$array[$val]:$def;
	}
	return $str;
}
/**
	初始化表单值
	@param $fields array 字段列表
	@param $data   array 提交值对应
	@return
*/
function kc_data($fields,$data=NULL){
	//如果为空值,直接从POST中获得值
	if($data===NULL) $data=$_POST;

	foreach($fields as $val){
		if(!isset($data[$val]))//如果data数组中没有对应的键值，则创建简直，并把其值设置为空
			$data[$val]='';
	}

	return $data;
}
/**
	算是explode的扩展,explode指定limit后，数量不足的时候，并不自动填充，这并不适合用list
	@param $separator string 分割线
	@param $str       string 要分割的内容
	@param $limit     int    输出数组的长度，必须大于1
	@return array
*/
function kc_explode($separator,$str,$limit){
	$array=explode($separator,$str,$limit);

	for($i=0;$i<$limit;$i++){
		if(!isset($array[$i]))
			$array[$i]='';
	}
	return $array;
}
/**

	获得list复选框列表

	@param int        $_is    验证是否为必须选择复选框
		1:  不能为空
		0:  可以为空
	@param int|string $_type  数据类型验证,详见kc_validate()

	@return string

*/
function kc_getlist($_is=1,$_type=3){
	global $king;

	if($_is&&!($_list=kc_post('list'))){

		kc_error($king->lang->get('system/error/select'));
	}
	if(!kc_validate($_list,$_type)){
		kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
	return $_list;
}
/* ------>>> 构造HTML标签 <<<------------------------ */


/**
	单行文本框
	@param string $id    文本框id和name值
	@param string $value 文本内容
	@param int    $size  文本框长度
	@param string $exp   其他属性扩展
	@return string
*/
function kc_htm_input($id,$value='',$maxlength=255,$size=200,$exp=''){
	return "<input name=\"{$id}\" type=\"text\" id=\"{$id}\" ".(isset($exp{0})?$exp.' ':'')."value=\"".htmlspecialchars($value)."\" class=\"k_in w{$size}\" maxlength=\"{$maxlength}\" />";
}
/**
	多行文本编辑框
	@param string $id    文本框id和name值
	@param string $value 文本内容
	@param int    $size  文本框长度
	@param int    $rows  行数
	@param string $exp   其他属性扩展
	@return string
*/
function kc_htm_textarea($id,$value='',$size=400,$rows=5,$exp=''){
	return "<textarea id=\"$id\" name=\"$id\" cols=\"5\" rows=\"5\" class=\"k_in w{$size}\" $exp>".htmlspecialchars($value)."</textarea>";
}
/**

	输出HTML标签<select>...</select>的HTML代码

	@param strong $_name    select标签对应的表单名称
	@param array  $_array   表单对象及值
		array(
			$key=>$value,
		)
		数组对应关系：<option value="$key">$value</option>
	@param string $_default  默认选择值
	@param string $_insert   <select>标签的扩展部分，比如加入onChange等javascript代码

	@return string

*/
function kc_htm_select($_name,$_array,$_default='',$_insert=''){
	$array_def=is_array($_default)?$_default:explode(',',$_default);
	/**
	stripos($_insert,'multiple')
		? $s ='<select name="'.$_name.'[]" id="'.$_name.'"'.$_insert.'>'
		: $s ='<select name="'.$_name.'" id="'.$_name.'"'.$_insert.'>';
	**/

	if(stripos($_insert,'multiple')){

		$s='<select name="'.$_name.'[]" id="'.$_name.'"'.$_insert.'>';
		foreach($_array as $_key => $_value){
			$s.='<option value="'.$_key.'"';
			$s.=in_array($_key,$array_def) ? ' selected="selected"' : '';
			$s.='>'.$_value.'</option>';
		}

	}else{
		$s='<select name="'.$_name.'" id="'.$_name.'"'.$_insert.'>';
		foreach($_array as $_key => $_value){
			$s.='<option value="'.$_key.'"';
			$s.=((string)$_key===(string)$_default ? ' selected="selected"' : '');
			$s.='>'.$_value.'</option>';
		}
	}

	$s.='</select>';
	return $s;
}
/**

	返回复选框HTML代码 <input type="checkbox"/>

	@param string $_name     复选框表单名称
	@param array  $_array    表单对象及值
	@param string $_default  被选值

	@return string

*/
function kc_htm_checkbox($_name,$_array,$_default=''){
	$s='<span>';
	$array_def=is_array($_default) ? $_default:explode(',',$_default);
	foreach($_array as $_key => $_value){
		if($_value=='-'){
			$s.=' - ';
		}elseif($_value=='|'){
			$s.='<br/>';
		}elseif(substr($_value,-1,1)=='['){
			$s.='<em class="checkbox">'.$_value;
		}elseif($_value==']'){
			$s.=']</em>';
		}elseif($_value{0}=='-'&&substr($_value,-1)=='-'){
			$s.='<strong>'.$_value.'</strong><br/>';
		}else{
			if(count($array_def)>0){
					in_array($_key,$array_def)
						? $s.='<input type="checkbox" name="'.$_name.'[]" id="k_'.$_name.'_'.$_key.'" value="'.$_key.'" checked="true"><label for="k_'.$_name.'_'.$_key.'">'.htmlspecialchars($_value).'</label>'
						: $s.='<input type="checkbox" name="'.$_name.'[]" id="k_'.$_name.'_'.$_key.'" value="'.$_key.'"><label for="k_'.$_name.'_'.$_key.'">'.htmlspecialchars($_value).'</label>';
			}else{
				$s.='<input type="checkbox" name="'.$_name.'[]" id="k_'.$_name.'_'.$_key.'" value="'.$_key.'"><label for="k_'.$_name.'_'.$_key.'">'.htmlspecialchars($_value).'</label>';
			}
		}
	}
	$s.='</span>';
	return $s;
}
/**

	返回单选按钮HTML代码 <input type="radio"/>

	@param string $_name     单选框表单名称
	@param array  $_array    表单对象及值
	@param string $_default  被选值

	@return string

*/
function kc_htm_radio($name,$array,$default='',$exp=''){
	$s='<span>';
	foreach($array as $key => $value){
		$default==$key
			? $s.='<input type="radio" name="'. $name .'" id="k_'. $name .'_'. $key .'" value="'. $key .'" checked="true" '. $exp .'/><label for="k_'.$name.'_'.$key.'">'.htmlspecialchars($value).'</label>'
			: $s.='<input type="radio" name="'. $name .'" id="k_'. $name .'_'. $key .'" value="'. $key .'" '.$exp.'/><label for="k_'.$name.'_'.$key.'">'.htmlspecialchars($value).'</label>';
	}
	$s.='</span>';
	return $s;
}
/**

	返回提交表单按钮HTML代码 <input type="submit"/>

	@param string $_name  按钮上显示的内容

	@return string

*/
function kc_htm_submit($_name,$_exp=''){
	$s ='<p class="k_submit"><input type="submit" value="'.$_name.'[S]" accesskey="s"/>'.$_exp.'</p>';
	return $s;
}
/**

	返回按钮的HTML代码 <input type="button"/>+$exp

	@param string $_name     按钮上显示的内容
	@param string $_onclick  onClick事件
	@param bool   $is=0      当设置为1的时候，disabled=false
	@param string $_exp      对按钮进行扩展，比如加上链接之类的

	@return string

*/
function kc_htm_button($_name,$_onclick,$is=0,$_exp=''){
	$s ='<p class="k_submit"><input type="button" value="'.$_name.'[S]" accesskey="s" onClick="'.$_onclick.';';
	$s.=$is ? '' : 'this.disabled=true';
	$s.='" />'.$_exp.'</p>';
	return $s;
}
/**
	返回链接按钮
	@param string $name 显示内容
	@param string $rel  rel，ajax操作传递值
	@return string
*/
function kc_htm_a($name,$rel){
	$s ="<a href=\"javascript:;\" title=\"{$name}\" class=\"k_ajax\" rel=\"{$rel}\">{$name}</a>";;
	return $s;
}
/**

	返回隐藏域HTML代码 <input type="hidden"/>

	@param array $_array   隐藏域对象及值
		array(
			$key=>$val,
		)
		对应关系:
		<input value="$val" name="$key"/>

	@return string

*/
function kc_htm_hidden($_array){
	$s='';
	foreach($_array as $_key => $_value){
		$s.='<input type="hidden" name="'.$_key.'" id="'.$_key.'" value="'.htmlspecialchars($_value).'"/>';
	}
	return $s;
}
/**

	返回IFRAME代码

	@param string $src     被调用页面
	@param int    $width   框宽度
	@param int    $height  框高度

*/
function kc_htm_iframe($src,$width=0,$height=0,$class=''){
	if($width==0 && $height==0){
		$s='<iframe class="none" border="0" style="width:500px;height:500px;line-height:0px;" src="'.$src.'"></iframe>';
	}else{
		$s='<iframe border="0"'.(isset($class{0})?" class=\"$class\"":"").' style="width:'.$width.'px;height:'.$height.'px;" src="'.$src.'"></iframe>';
	}
	return $s;
}
/**

	返回编辑器调用代码

	@param string $_name    编辑器对应的表单名称
	@param string $_content 编辑器中的内容
	@param int    $width    编辑器宽度
	@param int    $height   编辑器高度
	@param string $def      指定编辑器名称
	@param string $code     编辑代码类型，默认为html

	@return string

*/
function kc_htm_editor($name,$content,$width=780,$height=360,$def='xheditor',$code='html'){
	global $king;

	if(isset($king->admin['admineditor'])){
		$def=$king->admin['admineditor'];
	}

	$s='';

	switch(strtolower($def)){

		case 'fckeditor':

			if(empty($GLOBALS['htm_editor_isread']))
				require_once(ROOT."system/editor/fckeditor/fckeditor_php5.php");

			$oFCKeditor = new FCKeditor($name) ;
			$oFCKeditor->BasePath = $king->config('inst')."system/editor/fckeditor/" ;
			$oFCKeditor->Value = $content ;
			$oFCKeditor->Width = $width;
			$oFCKeditor->Height = $height;
			$s=$oFCKeditor->CreateHtml() ;

		break;

		case'tiny_mce':

			if(empty($GLOBALS['htm_editor_isread']))
				$s='<script type="text/javascript" src="../system/editor/tiny_mce/tiny_mce.js"></script>';

			$s.='<script type="text/javascript">
				// Default skin
				tinyMCE.init({
					// General options
					mode : "exact",
					elements : "'.$name.'",
					theme : "advanced",
					plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups",

					// Theme options
					theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
					theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
					theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,

					// Example content CSS (should be your site CSS)
					content_css : "css/content.css",

					// Drop lists for link/image/media/template dialogs
					template_external_list_url : "lists/template_list.js",
					external_link_list_url : "lists/link_list.js",
					external_image_list_url : "lists/image_list.js",
					media_external_list_url : "lists/media_list.js",

					// Replace values for the template plugin
					template_replace_values : {
						username : "Some User",
						staffid : "991234"
					}
				});
			</script>
			<textarea id="'.$name.'" name="'.$name.'" rows="15" cols="80" style="width:'.$width.'px;height:'.$height.'px">'.htmlspecialchars($content).'</textarea>';

		break;

		case 'ewebeditor':
			$s="<input id=\"$name\" name=\"$name\" value=\"".htmlspecialchars($content)."\" type=\"hidden\" />";
			$s.="<iframe ID=\"eWebEditor1\" src=\"../system/editor/eWebEditor/ewebeditor.htm?id=$name&style=light\" frameborder=\"0\" scrolling=\"no\" width=\"$width\" HEIGHT=\"$height\"></iframe>";
		break;

		case 'textarea':
			$s='<div class="k_editor"><textarea cols="100" rows="10" id="'.$name.'" name="'.$name.'" class="k_in" style="width:'.$width.'px;height:'.$height.'px;word-break:break-all;" >'.htmlspecialchars($content).'</textarea></div>';
		break;

		case 'edit_area':
			if(empty($GLOBALS['htm_editor_isread']))
				$s="<script language=\"javascript\" type=\"text/javascript\" src=\"../system/editor/edit_area/edit_area_full.js\"></script>";

			$s.="<script language=\"javascript\" type=\"text/javascript\">
			editAreaLoader.init({id: \"$name\",start_highlight: true,allow_resize: \"both\",allow_toggle: false,language:\"zh-cn\",font_family: \"verdana, 新宋体\",replace_tab_by_spaces: 4,font_size: \"8\",toolbar: \"|,highlight,search, |, undo, redo, |,html, |, select_font,|,fullscreen\",plugins: \"html\",syntax: \"$code\"});</script>";
			$s.="<textarea id=\"$name\" name=\"$name\" rows=\"15\" cols=\"80\" style=\"width:{$width}px;height:{$height}px\">".htmlspecialchars($content)."</textarea>";
		break;

		case 'nicedit':
			if(empty($GLOBALS['htm_editor_isread']))
				$s='<script src="../system/editor/nicEdit/nicEdit.js" type="text/javascript"></script>';
			$s.='<textarea cols="100" rows="10" style="width:'.$width.'px;height:'.$height.'px;" id="'.$name.'" name="'.$name.'">'.htmlspecialchars($content).'</textarea>';
			$s.='<script type="text/javascript">new nicEditor({fullPanel : true,iconsPath : \'../system/editor/nicEdit/nicEditorIcons.gif\'}).panelInstance(\''.$name.'\');</script>';
		break;
		
		default:
			//默认调用xheditor编辑器
			if(empty($GLOBALS['htm_editor_isread']))
				$s='<script src="../system/editor/xheditor/xheditor-zh-cn.js" type="text/javascript"></script>';
			$inst=$king->config('inst');
			$s.="<script type=\"text/javascript\">
			\$(pageInit);
			function pageInit()
			{
				var jdata={
					emots:{'qq':{'name':'QQ','count':55,'width':25,'height':25,'line':11}},
					upLinkUrl:\"!../system/editor/xheditor/xheditor_plugins/multiupload/multiupload.html?uploadurl={$inst}system/upload.php&ext=附件文件(*.zip;*.rar;*.pdf;*.doc;*.xls;*.docx;*.xlsx;*.txt)\",
					upImgUrl:'!../system/editor/xheditor/xheditor_plugins/multiupload/multiupload.html?uploadurl={$inst}system/upload.php&ext=图片文件(*.jpg;*.jpeg;*.gif;*.png)',
					upFlashUrl:'!../system/editor/xheditor/xheditor_plugins/multiupload/multiupload.html?uploadurl={$inst}system/upload.php&ext=Flash动画(*.swf)',
					upMediaUrl:'!../system/editor/xheditor/xheditor_plugins/multiupload/multiupload.html?uploadurl={$inst}system/upload.php&ext=多媒体文件(*.wmv;*.avi;*.wma;*.mp3;*.mid)',
					shortcuts:{'ctrl+enter':submitForm}
				};
				\$('#$name').xheditor(true,jdata);
			}
			function submitForm(){\$('#k_formlist').submit();}
			</script>
			";
			$s.='<textarea cols="100" rows="10" style="width:'.$width.'px;height:'.$height.'px;" id="'.$name.'" name="'.$name.'">'.htmlspecialchars($content).'</textarea>';

	}

	$GLOBALS['htm_editor_isread']=True;

	return '<div class="k_editor">'.$s.'</div>';
}
/**
	OL选项
	@param string $title  标题，用H5输出
	@return string
*/
function kc_htm_ol($title,$array,$goto=null){
	$s="<h5 class=\"ol\">{$title}</h5><p class=\"ol\"><ol>";
	foreach($array as $val){
		$s.="<li>{$val}</li>";
	}
	$s.='</ol></p>';
	if(isset($goto{0})){
		$s.='<script type="text/javascript">setTimeout("parent.location=\''.addslashes($goto).'\'",3000);</script>';
	}
	return $s;
}
/**
	预置内容填写表单
	@param string $name    要插入的表单名称
	@param array  $array   预设的表单列表或语言包中的位置
		array(
			$key=>$val,
			插入到表单的值=>要显示的内容
		)
	@param int    $width    预设框的长度
	@param bool   $is       是否带有序号,默认没有

*/
function kc_htm_setvalue($name,$array,$width=100,$is=0){
/**
	global $king;
	$_array=array(''=>$king->lang->get('system/common/setvalue'));
	foreach($array as $key => $val){//key为数字类型的时候，不能用array_push方式合并，注意，不要简化这段代码
		$_array[$key]=$val;
	}
	$s='<span class="setvalue">'.kc_htm_select("{$name}_val",$_array,$default,'onChange="this.options[this.selectedIndex].value.length?$(\''.$name.'\').value=this.options[this.selectedIndex].value:void(0)"').'</span>';
*/
	$i=1;
	$value=$is? '<p>':'<span>';
	foreach($array as $key => $val){
		$value.='<a rel="{value:\''.addslashes($key).'\'}" >';//onclick="$(\''.$name.'\').value=\''.$key.'\'"
		$value.=$is? '<i>'.($i++).'.</i>':'';
		$value.=htmlspecialchars($val).'</a>';
	}
	$value.=$is? '</p>':'</span>';

	$s=kc_htm_hidden(array($name.'_setvalue'=>$value));
	/**
	$s.='<a href="javascript:;" onclick="kc_setvalue(\''.$name.'\');">'.kc_icon('m4').'</a>';
	$s.='<table id="'.$name.'_table" class="k_setvalue" onblur="kc_display(\''.$name.'_table\')" style="width:'.$width.'px;height='.$height.'px;visibility:hidden"><tr><td id="'.$name.'_fly"></td></tr></table>';
	*/
	//$s.='<script>kc_setvalue(\''.$name.'\',\''.$width.'\',\''.$is.'\')<\/script>';
	$s.='<a class="k_setvalue" rel="{ID:\''.$name.'\',width:\''.$width.'\',IS:'.$is.'}"><img src="../system/images/white.gif" class="os m4"/></a>';
	return $s;
}
/**
文本转换为数组并调用kc_htm_setvalue
即为换行拆分多个预置的值
getdefault
**/
function kc_htm_setvalue_nl($name,$str){
	$array=array();
	if(isset($str{0})){
		$array1=explode(NL,$str);
		foreach($array1 as $val){
			if(isset($val{0})){
				$array2=explode('|',$val);
				if(count($array2)==1){
					$array[$val]=$val;
				}else{
					$array3=explode('|',$val,-1);
					$array[implode('|',$array3)]=$array2[count($array2)-1];
				}
			}
		}
		return kc_htm_setvalue($name,$array);
	}
}
/**

	返回语言包

	@return array
		array(
			$key=>$val,
			文件名(无扩展名)=>对应的语言说明，如：简体中文
		)

*/
function kc_htm_selectlang(){
	$_array_lang=kc_f_getdir('system/language/','xml');
	$_array_key=array_map('kc_f_name',$_array_lang);
	$_array_val=array_map('kc_getlang',$_array_key);
	$_array=array_combine($_array_key, $_array_val);
	return $_array;
}

/* ------>>> 文件目录操作 <<<------------------------ */


/**

	读取指定的目录下面的文件和文件夹列表，并以数组形式返回

	@param string $_path  要读取的路径，相对于安装目录
	@param string $_type  返回类型
		*       所有文件和文件夹
		dir     目录
		file    文件
		[ext]   指定扩展名，只返回指定的扩展名的文件列表，如：xml|doc，则返回xml和doc文件列表

	@return array

*/
function kc_f_getdir($_path='',$_type='*',$_ignore=array('.','..','.svn')){

	global $king;

	$_array=array();


	if($_handle=opendir(ROOT.$_path)){
		while (false !== ($_file=@readdir($_handle))){
			$_file=kc_f_iconv($_file);
			if(!in_array($_file,$_ignore)){

				if(is_dir(ROOT.$_path.$_file)){//如果是dir
					if($_type=='*'||$_type=='dir')
						$_array[]=$_file;
				}else{//文件
					if($_type=='*'||$_type=='file'||preg_match("/^.+\.({$_type})$/i",$_file))
						$_array[]=$_file;
				}
			}
		}
		closedir($_handle);
		return $_array;
	}else{
		kc_error($king->lang->get('system/error/notdir').':'.$_path);
	}
}


/**

	返回带有单位的文件大小，自动设置适合于大小的单位

	@param int  $_size   文件的大小，单位为字节

	@return string

*/
function kc_f_size($_size) {
	$_array=array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	$_pos=0;
	while ($_size >= 1024) {
		$_size /= 1024;
		$_pos++;
	}
	return number_format($_size,2).' '.$_array[$_pos];
}
/**
	返回文件大小
	@param
	@return
*/
function kc_f_filesize($filepath){
	$filepath=kc_f_iconv($filepath,1);
	if(is_file(ROOT.$filepath)){
		return filesize(ROOT.$filepath);
	}
}

/**

	从完整的路径中返回文件名，不包括扩展名

	@param string $_name   文件名或完整路径

	@return string

*/
function kc_f_name($_name){
	$_name=basename($_name);
	$_array=explode('.',$_name);
	return $_array[0];
}
/**

	从完整的路径中返回文件扩展名

	@param string $_name   文件名或完整路径

	@return string

*/
function kc_f_ext($_name){
	$ext=strtolower(substr(strrchr($_name,'.'),1));
	return $ext;
}
/**

	返回文件类型图标的完整HTML代码

	@param string $_name  文件名或路径

	@return string

*/
function kc_f_kc_icon($name){
	$icon=kc_f_ico($name);
	return kc_icon($icon,$name);
}
/**

	返回文件类型图标对应的<icon.gif>图片位置代号

	@param string $_name  文件名或路径

	@return string

*/
function kc_f_ico($name){
	switch(kc_f_ext($name)){
		case 'doc':$s='b3';break;
		case 'htm':$s='f3';break;
		case 'html':$s='f3';break;
		case 'js':$s='l3';break;
		case 'css':$s='l3';break;
		case 'mid':$s='k3';break;
		case 'wma':$s='k3';break;
		case 'mp3':$s='k3';break;
		case 'pdf':$s='j3';break;
		case 'php':$s='a3';break;
		case 'php3':$s='a3';break;
		case 'jpg':$s='d3';break;
		case 'png':$s='i3';break;
		case 'gif':$s='d3';break;
		case 'bmp':$s='d3';break;
		case 'swf':$s='h3';break;
		case 'txt':$s='h6';break;
		case 'xls':$s='c3';break;
		case 'zip':$s='e3';break;
		case 'rar':$s='e3';break;
		case '7z':$s='e3';break;
		default:$s='g3';
	}
	return $s;
}
/**

	调用文件浏览器的图片按钮

	@param string $id         获得返回值的表单ID
	@param string $path       起始打开的目录地址，相对于安装目录,如果有$file值,则
	@param string $filetype   可上传的文件类型 默认0：图片类型，1：文件类型，2：模板类型
	@param bool   $is         插入方式
		0: 选择插入后关闭文件浏览器
		1: 选择插入后不关闭文件浏览器，并继续点击的时候追加插入，用在多列表模式
	@param string $jsfun      插入结束后调用执行的javascript函数
	@param string $file       如果有文件，则显示文件

	@return string

*/
function kc_f_brow($id,$path,$filetype=0,$is=0,$jsfun='',$file=''){
	global $king;

	$s='';
	//模板类型的时候，增加一个快速插入
	if($filetype==2&&is_dir(ROOT.$path)){
		if($array=kc_f_getdir($path,$king->config('templateext'))){
			$array_setvalue=array();
			foreach($array as $val){
				$array_setvalue[$path.'/'.$val]=$val;
			}
			$s=kc_htm_setvalue($id,$array_setvalue);
		}
	}

	if(substr($path,-1)!='/')//老版本的没有写/来区分文件和目录，所以设置自动补充
		$path.='/';

	$s.="<a href=\"javascript:;\" class=\"k_ajax\" ";
	$s.="rel=\"{URL:'../system/manage.php',CMD:'brow',id:'$id',path_def:'$path',jsfun:'$jsfun',is:$is,filetype:$filetype,VAL:'$id'}\">";
	$s.=kc_icon('a9',$king->lang->get('system/common/browserver'))."</a>";
/*
	if($is){
		$s.='<a class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'brow\',id:\''.$id.'\',path:\''.urlencode($path).'\',jsfun:\''.urlencode($jsfun).'\',is:1,filetype:\''.$filetype.'\',path_def:\''.$path.'\'}">'.kc_icon('a9',$king->lang->get('system/common/browserver')).'</a>';
	}else{
		$s.='<a class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'brow\',id:\''.$id.'\',path:($(\'#'.$id.'\').val()==\'\'?\''.$path.'\':$(\'#'.$id.'\').val()),is:0,jsfun:\''.urlencode($jsfun).'\',filetype:\''.$filetype.'\',path_def:\''.$path.'\'}">'.kc_icon('a9',$king->lang->get('system/common/browserver')).'</a>';
	}
*/
	return $s;
}
/**

	返回调用颜色表的图片按钮

	@param string $id      获得返回值的表单ID

	@return string

*/
function kc_f_color($id){
	$s="<a href=\"javascript:;\" class=\"k_color\" rel=\"{id:'$id'}\"><img src=\"../system/images/white.gif\" class=\"os a8\"/></a>";
	return $s;
}
/**

	连续创建文件夹/创建目录

	@param string $path   要创建的文件夹路径，相对于安装目录

	@return bool

*/
function kc_f_md($path){
	$path=kc_f_iconv($path,1);
	$array=explode('/',$path);
	$new='';
	$is=true;
	foreach($array as $val){
		$new.=$val.'/';
		if(isset($val{0})){
			if(!file_exists(ROOT.$new)){
				if(!@mkdir(ROOT.$new))
					$is=False;
			}
		}
	}
	return $is;
}
/**

	删除文件夹/删除目录
	@papram string $path;  要删除的文件夹路径
	@return void
*/
function kc_f_rd($path,$is=0){

	$path=kc_f_iconv($path,1);

	if(!is_dir(ROOT.$path)) return;

	$array=kc_f_getdir($path);
	foreach($array as $val){
		$file=$path.'/'.$val;
		if($val!=''){
			if(is_dir(ROOT.$file)){//目录
				kc_f_rd($file,1);
				rmdir(ROOT.$file);
			}else{
				kc_f_delete($file);
			}

		}
	}
	if(!$is&&is_dir(ROOT.$path)){
		rmdir(ROOT.$path);
	}
}

/**

	读取文本文件到字符串，并返回

	@param string $filename  被读取的文本文件地址，相对于安装目录

	@return $string

*/
function kc_f_get_contents($filename){

	$filename=kc_f_iconv($filename,1);

	if(empty($GLOBALS['file_get_contents_array']))
		$GLOBALS['file_get_contents_array']=array();
	if(array_key_exists($filename,$GLOBALS['file_get_contents_array'])){//如果已经存在这个键值
		$s=$GLOBALS['file_get_contents_array'][$filename];
	}else{//如果没有这个键值，则加入

		kc_runtime('getContent');


		if(is_file(ROOT.$filename)){//如果存在则读取
/*
			$s=file_get_contents(ROOT.$filename);

*/
			$s='';
			$fh = fopen(ROOT.$filename,"r");
				while (!feof($fh)) {
				$s.=fgets($fh);
			}

			fclose($fh);



			$GLOBALS['file_get_contents_array'][$filename]=$s;
		}else{
			$s='';
		}
		kc_runtime('getContent',1);

	}

	return $s;

}
/**

	把字符串写入文件，返回是否成功

	@param string $filename  要写入的文件地址，相对于安装目录
	@param string $s         要写入的文本内容
	@param bool   $is        当写入失败的时候，是否提示错误，默认为不提示

	@return bool

*/
function kc_f_put_contents($filename,$s,$is=false){
	global $king;

	$filename=kc_f_iconv($filename,1);

	kc_f_md(dirname($filename));//创建目录

	//去掉bom
	if(substr($s,0,3)==pack("CCC",0xef,0xbb,0xbf)){
		$s=substr($s,3);
	}

	kc_runtime('putContent');

	$strlen=@file_put_contents(ROOT.$filename,$s,LOCK_EX);

	kc_runtime('putContent',1);

	if(is_int($strlen)){//写入成功
		return true;
	}else{//写入失败
		if($is){
			kc_error($king->lang->get('system/error/putcontents').'<br/>'.$filename);
		}
	}

}
/**
	判断文件是否存在
	@param string $path
	@return bool
*/
function kc_f_isfile($path){
	$path=kc_f_iconv($path,1);

	return is_file(ROOT.$path);
}
/**
	删除文件
	@param string $path      文件路径
	@return bool;
*/
function kc_f_delete($path){

	$path=kc_f_iconv($path,1);

	if(is_file(ROOT.$path)){
		return unlink(ROOT.$path);
	}
	return False;
}
/**
	重命名文件或文件夹
	@param string $old 源文件
	@param string $new 新的
	@return bool
*/
function kc_f_rename($old,$new){
	if($old===$new) return True;
	$old=kc_f_iconv($old,1);
	$new=kc_f_iconv($new,1);
	return rename(ROOT.$old,ROOT.$new);
}
/**
	获得修改时间
	@param
	@return
*/
function kc_f_mtime($file){
	$file=kc_f_iconv($file,1);
	return filemtime(ROOT.$file);
}
/**
	文件操作编码转换
	@param strin $s    字符串
	@param bool  $is=0 默认读取，1的时候是写入
	@return
*/
function kc_f_iconv($s,$is=0){

	$lang=kc_val($_SERVER,'HTTP_ACCEPT_LANGUAGE');

	if(substr($lang,0,5)=="zh-cn"){
		$code='GBK';
	}elseif(substr($lang,0,5) == "zh-tw"){
		$code='BIG5';
	}

	if(isset($code)){
		$s=$is ? kc_iconv($s,$code,PAGE_CHARSET) : kc_iconv($s,PAGE_CHARSET,$code);
	}

	return $s;
}




/* ------>>> 其他功能函数 <<<------------------------ */




/**
 * 编码转换
 * @param string $s   录入的
 * @param string $out 输出编码类型
 * @param string $in  输入编码类型
 * @return string
*/
function kc_iconv($s,$out='UTF-8',$in='GBK'){
	if($out==$in)
		return $s;

	if(function_exists('iconv')){
//		echo "[$in|$out]";
		$s=iconv($in,"$out//IGNORE",$s);
	}elseif(function_exists('mb_convert_encoding')){
		$s=mb_convert_encoding($s,$out,$in);
	}

	return $s;

}

/**
	读cookie
	@param
	@return
*/
function kc_cookie($name){
	if(empty($_COOKIE[$name])){
		return False;
	}else{
		return $_COOKIE[$name];
	}
}
/**
	写Cookie
	@param string $name 名称
	@param string $val  值
	@param time   $expire  时间
	@return
*/
function kc_setCookie($name,$val,$expire = 0) {
	global $king;
	setcookie($name, $val,$expire ? time() + $expire : 0, $king->config('inst'));
}
/**
	计算页面执行时间，在执行文件的头尾各插入两次即可
	@param int $_round  精确到小数点
	@return real
*/
function kc_script_runtime($_round =4){
	if(!empty($GLOBALS['KC_START_SCRIPT_RUNTIME'])){
		list($_msec,$_sec)= explode(' ',microtime());
		return round(($_sec+$_msec)-$GLOBALS['KC_START_SCRIPT_RUNTIME'],$_round);
	}else{
		list($_msec,$_sec)=explode(' ',microtime());
		$GLOBALS['KC_START_SCRIPT_RUNTIME']=$_sec+$_msec;
	}
}
/**
	计算函数执行时间
	@param string $name  对象名称
	@param int    $is    0 记录开始时间 1计算时差
	@return array
*/
function kc_runtime($name,$is=0){
	if($is==0){//记录时间
		//
		$GLOBALS['KC_RUNTIME'][$name]['start']=microtime();
	}else{
		list($_msec,$_sec)=explode(' ',$GLOBALS['KC_RUNTIME'][$name]['start']);
		list($msec,$sec)=explode(' ',microtime());
		$diff=$msec+$sec-$_msec-$_sec;

		if(array_key_exists('runtime',$GLOBALS['KC_RUNTIME'][$name])){
			$GLOBALS['KC_RUNTIME'][$name]['runtime']+=$diff;
			$GLOBALS['KC_RUNTIME'][$name]['number']+=1;
		}else{
			$GLOBALS['KC_RUNTIME'][$name]['runtime']=$diff;
			$GLOBALS['KC_RUNTIME'][$name]['number']=1;
		}
	}
}
//


/**
	转换phpinfo为数组
	@param
	@return
*/
function kc_parsePHPModules() {
 ob_start();
 phpinfo(INFO_MODULES);
 $s=ob_get_contents();
 ob_end_clean();

 $s=strip_tags($s,'<h2><th><td>');
 $s=preg_replace('/<th[^>]*>([^<]+)<\/th>/',"<info>\\1</info>",$s);
 $s=preg_replace('/<td[^>]*>([^<]+)<\/td>/',"<info>\\1</info>",$s);
 $vTmp=preg_split('/(<h2>[^<]+<\/h2>)/',$s,-1,PREG_SPLIT_DELIM_CAPTURE);
 $vModules=array();
 for ($i=1;$i<count($vTmp);$i++) {
  if (preg_match('/<h2>([^<]+)<\/h2>/',$vTmp[$i],$vMat)) {
   $vName=trim($vMat[1]);
   $vTmp2=explode("\n",$vTmp[$i+1]);
   foreach ($vTmp2 AS $vOne) {
   $vPat='<info>([^<]+)<\/info>';
   $vPat3="/$vPat\s*$vPat\s*$vPat/";
   $vPat2="/$vPat\s*$vPat/";
   if (preg_match($vPat3,$vOne,$vMat)) { // 3cols
     $vModules[$vName][trim($vMat[1])]=array(trim($vMat[2]),trim($vMat[3]));
   } elseif (preg_match($vPat2,$vOne,$vMat)) { // 2cols
     $vModules[$vName][trim($vMat[1])]=trim($vMat[2]);
   }
   }
  }
 }
 return $vModules;
}

/**

	返回当前IP地址

	@return int

*/
function kc_getip(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])){   //check ip from share internet
		$ip=$_SERVER['HTTP_CLIENT_IP'];
	}elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){   //to check ip is pass from proxy
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	$one='([0-9]|[0-9]{2}|1\d\d|2[0-4]\d|25[0-5])';
	if(!@preg_match('/'.$one.'\.'.$one.'\.'.$one.'\.'.$one.'$/', $ip)){$ip='0.0.0.0';};
	return ip2long($ip);
}
/**

	换行符转换为空格

	@param string $_text

	@return string

*/
function kc_clsnl($_text){
	return preg_replace('/\r\n|\n|\r/',' ',$_text);
}
/**

	弹窗提示下一步操作或当前操作结果

	@param string $_lang        提示语言
	@param string $_url_ok      点击确定的时候跳转的页面
	@param string $_url_cancel  点击取消的时候跳转的页面

	@return string

*/
function kc_goto($_lang,$_url_ok='',$_url_cancel=''){
	$s='<meta http-equiv="Content-Type" content="text/html; charset='.PAGE_CHARSET.'" />';
	$s.='<script type="text/javascript">';
	if($_url_cancel){
		$s.='confirm(\''.$_lang.'\')?eval("parent.location=\''.$_url_ok.'\'"):eval("parent.location=\''.$_url_cancel.'\'");';
	}else{
		$_url_ok==''
			? $s.='alert(\''.$_lang.'\');'
			: $s.='alert(\''.$_lang.'\');eval("parent.location=\''.$_url_ok.'\'");';
	}
	$s.='</script>';
	exit($s);
}

/**

	调用icon图标

	@param string $_fname  图标在<icons.gif>文件中的位置
	@param string $_alt    图片Alt属性值

	@return string

*/
function kc_icon($_fname='',$_alt='',$onclick=''){
	return '<img class="'.$_fname.' os"'.(empty($_alt) ? '' : 'title="'.addslashes($_alt).'" alt="'.addslashes($_alt).'"').' src="../system/images/white.gif"'.($onclick?" onclick=\"{$onclick}\"":'').'/>';
}
/**

	随机数

	@param int $length   返回的随机值的长度
	@param int $type     返回的随机值的类型
		1: 数字
		2: 小写字母
		3: 大些字母
		default: 数字和小写字符

	@return string

*/
function kc_random($length,$type=0){
	switch($type){
		case 1:$pattern="1234567890";break;
		case 2:$pattern="abcdefghijklmnopqrstuvwxyz";break;
		case 3:$pattern="ABCDEFGHIJKLMNOPQRSTUVWXYZ";break;
		case 4:$pattern="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890~!@#$%^&*()_-+=";break;
		default:$pattern="1234567890abcdefghijklmnopqrstuvwxyz";
	}
	$size=strlen($pattern)-1;
	$key=$pattern{rand(0,$size)};
	for($i=1;$i<$length;$i++)
	{
		$key.= $pattern{rand(0,$size)};
	}
	return $key;
}
/**

	取超长的字符串的前后一部分，中间省略号输出

	@param string $s         要处理的字符串
	@param int    $leftnum   左侧截取的长度
	@param int    $rightnum  右侧截取的长度

	@return string

*/
function kc_short($s,$leftnum=10,$rightnum=10){
	if(strlen($s)>$leftnum+$rightnum){
		$s=substr($s,0,$leftnum-3).' ... '.substr($s,2-$rightnum,$rightnum-2);
	}
	return $s;
}
/**

	关键字格式化为数据库中可搜索的sql代码

	@param string $query   原始关键字
	@param string $field   要搜索的数据字段
	@param string $is      搜索限定方式
		and: 检索同时包含所有关键字的字段
		or : 检索任何一个关键字的字段

*/
function kc_likey($query,$field='ktitle',$is='and'){

	$array_str=array('%','\'','"','@','!','<','>','*','(',')','&','+','=','#','^',';',',','.','/','\\');

	$s=str_replace($array_str,' ',$query);
	$array=explode(' ',$s);
	array_unique($array);
	$array=array_diff($array,array(null));

	$s=implode('%\' '.$is.' '.$field.' like \'%',$array);

	if(isset($s{0})){
		$s='('.$field.' like \'%'.$s.'%\')';
	}else{
		$s='';
	}

	return $s;

}
/**
	日期格式化
	@param int    $time
	@param string $mode 输出模式，这个属性有点多余，完全可以用gmdate来实现
	@return string
*/
function kc_formatdate($time,$mode='Y-m-d'){
	global $king;

	if($mode!='Y-m-d'){
		$time+=$king->config('timediff')*3600;
		$s=gmdate($mode,$time);
	}else{
		$ftime=gmdate('Ymd',$time);
		if($ftime==gmdate('Ymd',time())){
			$time+=$king->config('timediff')*3600;
			$s='<em class="c1">'.$king->lang->get('system/time/today')." ".gmdate('(H:i)',$time).'</em>';
		}elseif($ftime==gmdate('Ymd',time()-86400)){
			$time+=$king->config('timediff')*3600;
			$s='<em class="c2">'.$king->lang->get('system/time/yester')." ".gmdate('(H:i)',$time).'</em>';
		}elseif($ftime==gmdate('Ymd',time()-172800)){
			$s='<em class="c3">前天 '.gmdate('(H:i)',$time).'</em>';
		}else{
			$time+=$king->config('timediff')*3600;
			$s=gmdate($mode,$time);
		}
	}

	return $s;//.'['.time().'-'.$time.'='.(time()-$time).']';
}

/**
	时间(秒)格式化为更大的单位
	@param real $time  时间(秒)
	@return real
*/
function kc_formattime($time){
	global $king;

	$time=(int) ($time*1000);

	if($time>=86400000){
		return round($time/86400000,2).' '.$king->lang->get('system/time/day');
	}elseif($time>=3600000){
		return round($time/3600000,2).' '.$king->lang->get('system/time/hour');
	}elseif($time>=60000){
		return round($time/60000,2).' '.$king->lang->get('system/time/minute');
	}elseif($time>=1000){
		return round($time/1000,2).' '.$king->lang->get('system/time/second');
	}else{
		return round($time,2).' '.$king->lang->get('system/time/ms');
	}
}
/**

	分页列表的设置
	@param string $_url   : 分页连接代码，格式如：index.php?pid=%d&rn=%d，注意的是pid必须在rn的前面
	@param int    $_per   : record总数
	@param int    $_pid   : 当前页
	@param int    $_rn    : 每页显示数
	@param string $_inner : 模板

*/
function kc_pagelist($_url='',$_per,$_pid=1,$_rn=20,$_inner=null){

	$_count=($_per/$_rn); //总页数 可能非int类型，所以做如下比较并赋值

	if($_per==0||$_count==1)
		return;

	if($_pid==null)
		$_pid=1;

	if($_count!=(int)$_count)
		$_count=(int)$_count+1;

	if($_pid>$_count)		 //如果当前页大于总页数，这个是不现实的..
		$_pid=$_count;

	$_url=str_replace('RN',$_rn,$_url);

	$_inner=isset($_inner{0}) ? $_inner : '<p class="k_pagelist">{king:Previous/}{king:Standard/}{king:Next/}{king:Jump/}</p>';

	//full
	$full='';
	for($i=1;$i<=$_count;$i++){
		$_pid==$i
			? $full.='<strong>'.$i.'</strong>'
			: $full.='<a href="'.kc_formatpath($_url,$i).'">'.$i.'</a>';
	}

	//select
	$select='<select onChange="parent.location=this.options[this.selectedIndex].value">';
	for($i=1;$i<=$_count;$i++){
		$_pid==$i
			? $select.='<option selected="selected">'.$i.'</option>'
			: $select.='<option value="'.kc_formatpath($_url,$i).'">'.$i.'</option>';
	}
	$select.='</select>';

	//Next
	$next=($_pid==$_count)
		? '<span>Next &gt;</span>'
		: '<a href="'.kc_formatpath($_url,$_pid+1).'">Next &gt;</a>';

	//Previous
	$previous=($_pid==1)
		? '<span>&lt; Previous</span>'
		: '<a href="'.kc_formatpath($_url,$_pid-1,5).'">&lt; Previous</a>';

	//Standard
	$_numr=5;							//每页右侧显示翻页数量
	$_numl=2;						 //左侧显示2个
	$_num=$_numr+$_numl+1;	 //合计显示

	$sta= $_pid==1 ? '<strong>1</strong>' : '<a href="'.kc_formatpath($_url,1).'">1</a>';//Page 1

	if($_count>=2){	 //Page 2
		$sta.= $_pid==2 ? '<strong>2</strong>' : '<a href="'.kc_formatpath($_url,2).'">2</a>';
	}

	if($_pid>=$_numl+4 && $_count>$_num+3)
		$sta.='<i>...</i>';

	$i_sta=$_pid-$_numl;		//开始
	$i_end=$_pid+$_numr;		//结束

	if($_pid<=$_numl+2){		 //重新设置结束
		$i_end=$_num+2;
	}

	if($_pid>=$_count-$_num+2){ //重新设置开始
		$i_sta=$_count-$_num;
	}

	for($i=$i_sta;$i<$i_end;$i++){	//循环
		if($i>=3 && $i<=$_count-2){
			$i==$_pid
				? $sta.='<strong>'.$i.'</strong>'
				: $sta.='<a href="'.kc_formatpath($_url,$i).'">'.$i.'</a>';
//				: $sta.='<a href="'.sprintf($_url1,$i,$_rn).'">'.$i.'</a>';
		}
	}

	if($_pid+$_numr<=$_count-2 && $_count-2>$_num+1)
		$sta.='<i>...</i>';

	if($_count>3){
		$_pid==$_count-1			//Page Count-1
			? $sta.='<strong>'.($_count-1).'</strong>'
			: $sta.='<a href="'.kc_formatpath($_url,$_count-1).'">'.($_count-1).'</a>';
	}

	if($_pid==$_count){	 //Page Count
		if($_count>=3)
			$sta.='<strong>'.$_count.'</strong>';
	}else{
		if($_count>=3)
			$sta.='<a href="'.kc_formatpath($_url,$_count).'">'.$_count.'</a>';
	}
	//first
	$_pid==1
		? $first='<strong>&lt;&lt; First</strong>'
		: $first='<a href="'.kc_formatpath($_url,1).'">&lt;&lt; First</a>';

	//last
	$_pid==$_count
		? $last='<strong>Last &gt;&gt;</strong>'
		: $last='<a href="'.kc_formatpath($_url,$_count).'">Last &gt;&gt;</a>';

	//jump
	$jump='<input type="text" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.kc_formatpath($_url,"'+this.value+'").'\'; return false;}" />';//sprintf(str_replace('pid=PID','pid=%s',$_url),"'+this.value+'",$_rn)

	$tmp=new KC_Template_class();

	$tmp->assign('standard',$sta);
	$tmp->assign('select',$select);
	$tmp->assign('previous',$previous);//上一个
	$tmp->assign('next',$next);//下一个
	$tmp->assign('first',$first);//第一个
	$tmp->assign('last',$last);//最后一个
	$tmp->assign('full',$full);
	$tmp->assign('jump',$jump);

	$tmp->assign('pagecount',$_count);
	$tmp->assign('count',$_per);
	$tmp->assign('pid',$_pid);
	$tmp->assign('rn',$_rn);
	$s=$tmp->output($_inner);

	return $s;
}
/**

	判断文件与否，并输出格式化路径
	@param string $path   路径或文件名
	@param int    $pid    第X页
	@param int    $is     默认0输出链接;1输出文件生成地址(这个输出链接为相对根路径)

*/
function kc_formatPath($path,$pid=1,$is=0){
	global $king;
	if($path==''){
		return False;
	}elseif(strpos($path,'PID')===False){//若不包含PID，则根据需要进行补充
		if(substr($path,-1,1)=='/'){//目录结构
			if($pid=='1'){
				$path= $is ? $path.$king->config('file') : $path;
			}else{
				$path=substr($path,0,strlen($path)-1).$king->config('pidline').$pid.'/'.($is ? $king->config('file') : '');
			}
		}else{//文件结构
			$ext=strrchr($path,'.');
			$fname=substr($path,0,strlen($path)-strlen($ext));
			if($pid==1){
				//$path=$fname.$ext;
			}else{
				$path=$fname.$king->config('pidline').$pid.$ext;
			}
//			kc_error($path);
		}

	}else{//有PID
		$path=str_replace($king->config('pidline').'PID',$pid==1?'':$king->config('pidline').$pid,$path);
		if($is && substr($path,-1,1)=='/'){//输出生成地址
			$path.=$king->config('file');
		}
	}
	return $path;
}
/**
	返回浏览器名称
	@param int $is  返回值形式 0:只返回浏览器名称 1:+版本
	@return
*/
function kc_browser($is=0){

	$array=array (
	"OPERA",
	"MSIE",
	"NETSCAPE",
	"FIREFOX",
	"SAFARI",
	"KONQUEROR",
	"MOZILLA"
	);

	$browser="OTHER";

	foreach ($array as $val){
		if ( ($s=strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $val)) !== FALSE ){
			$f=$s + strlen($val);
			$version=substr($_SERVER['HTTP_USER_AGENT'], $f, 5);
			$version=preg_replace('/[^0-9,.]/','',$version);

			$browser=$val;
			break;
		}
	}

	if($is){
		return $browser;
	}else{
		return $browser.' '.$version;
	}
}
/**
	输出javascript数组
	@param string $_arrayname  要在javascript中输出的数组名称
	@param array  $_array      php的数组
		array(
			int $key=>string $value,
		)
*/
function kc_js2array($_arrayname,$_array){
	$s ='var '.$_arrayname.'=new Array();';
	foreach($_array as $_key => $_value){
		$s.=$_arrayname.'['.$_key.']'.'=\''.addslashes($_value).'\';';
	}
	return $s;
}
/**
	判断路径的函数
	@param string $s 路径
	@return string
*/
function kc_FullPath($s){
	global $king;
	return ((kc_validate($s,6) || substr($s,0,1)=='/') ? $s : $king->config('inst').$s);
}


?>