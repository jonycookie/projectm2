<?php

require_once '../global.php';

function king_ajax_salt(){
	global $king;
	$id_fly=kc_post('ID');
	$id=substr($id_fly,0,strlen($id_fly)-4);
	$salt=kc_random(12);
	$js="\$('#{$id}_salt').val('{$salt}');";
	$s="<img alt=\"".$king->lang->get('system/check/verifynew')."\" src=\"".$king->config('inst')."system/verify.php?salt={$salt}\"/>";
	$s.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{URL:'../system/verify.php',ID:'{$id_fly}',CMD:'salt'}\">".$king->lang->get('system/check/verifynew')."</a>";
	kc_ajax('',$s,0,$js);
}

function king_def(){

	global $king;
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // 过去的时间

	header("Content-type: image/png");

	$salt=kc_get('salt',1,1);

	$width=$king->config('verifywidth');//图片长度
	$height=$king->config('verifyheight');//图片高度
	$size=$king->config('verifysize');//文字大小
	$num=$king->config('verifynum');//文字数量
	$content=$king->config('verifycontent');//随机字符

	$array_content=explode('|',$content);
	$array_content=array_diff($array_content,array(null));
	$array_font=kc_f_getdir('system/verify_font','ttf|ttc');

	$str='';

	$img=imageCreate($width,$height);//创建一个空白图像
	imageFilledRectangle($img, 0, 0, $width, $height, imagecolorallocate($img,255,255,255));

	//写字
	for($i=0;$i<$num;$i++){
		$code=$array_content[array_rand($array_content)];
		$str.=$code;//验证码字符
		$color=imageColorAllocate($img,rand(0,128),rand(0,128),rand(0,128));
		$font='verify_font/'.$array_font[array_rand($array_font)];//随机读取一个字体
		$left=rand(round($size*0.2),round($size*0.4))+$i*$size;
		imagettftext($img,rand(round($size*0.7),$size), rand(-20,20), $left,rand(round($size*1.2),$size*1.4),$color,$font,$code);
	}
	//画星号
	$max=$width*$height/400;
	for($i=0;$i<$max;$i++){
		imagestring($img, 15, rand(0,$width), rand(0,$height), '*',rand(192,250));
	}
	//画点
	$max=$width*$height/40;
	for($i=0;$i<$max;$i++){
		imageSetPixel($img, rand(0, $width), rand(0, $height), rand(1,200));
	}
	//画线
	$max=$width*$height/800;
	for($i=0;$i<$max;$i++){
		imageline($img,rand(0,$width),rand(0,$height),rand(0,$width),rand(0,$height),rand(0,255));
	}
	//写验证码到verify中
	$verify=new KC_Verify_class;
	$verify->Put($salt,$str);
	
	imagePng($img);
	imageDestroy($img);

	$verify->Clear();
}
?>