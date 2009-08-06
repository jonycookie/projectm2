<?php
class KC_Verify_class{

/* ------>>> 验证码函数 <<<-------------------------- */

/**
	显示验证码
	@param 
	@return 
*/
public function Show(){
	global $king,$action;

	if(!$king->config('verifyopen'))
		return;
//kc_error(print_r($_POST,1));
	$verifynum=$king->config('verifynum');
	$_array=array(
		array('k_verify',0,$king->lang->get('system/check/notverify'),$verifynum,$verifynum*2),//如果不*2的话,这个长度验证和中文验证码可能有问题
		array('k_verify_salt',0,$king->lang->get('system/check/notverify'),12,12),//随机码
	);
	$verify=kc_post('k_verify');
	$k_verify_salt=kc_post('k_verify_salt');
	if(isset($verify{0})&&isset($k_verify_salt{0})){
		$verify_server=$king->cache->get('verify/'.$k_verify_salt,time()-$king->config('verifytime'));//服务器上存储的验证码
		$_array[]=array('k_verify',12,$king->lang->get('system/check/verifytimeout'),$verify_server=='');
		$king->cache->del('verify/'.$k_verify_salt);//获取值后马上删除
		$_array[]=array('k_verify',12,$king->lang->get('system/check/verify'),strtolower($verify)!=strtolower($verify_server));
	}
/*
	$s=$king->htmForm($king->lang->get('system/common/verify'),"<input autocomplete=\"off\" onClick=\"$.kc_verify()\" onFocus=\"$.kc_verify()\" class=\"k_in w50\" name=\"k_verify\" id=\"k_verify\" type=\"text\" maxlength=\"$verifynum\" /><span id=\"k_verify_show\"></span>",$_array);
	$s.=kc_htm_hidden(array('k_verify_salt'=>''));
*/
	$id=$action=='ajax'?'k_ajax_verify':'k_verify';

	$s=$king->htmForm($king->lang->get('system/common/verify'),"<input type=\"text\" maxlength=\"$verifynum\" class=\"k_verify k_in w50\" name=\"k_verify\" id=\"$id\"/>",$_array);
	$s.="<input type=\"hidden\" name=\"k_verify_salt\" id=\"{$id}_salt\" />";
	return $s;
}

/**
	写验证码
	@param string $salt  随机参数
	@param string $str   随机参数值
	@return bool
*/
public function Put($salt,$str){
	global $king;
	$king->cache->put('verify/'.$salt,$str);
}
/**
	清理多余的临时验证码文件
	@return void
*/
public function Clear(){
	global $king;
	$array=kc_f_getdir(KC_CACHE_PATH.'/verify','php');
	foreach($array as $val){
		$filetime=filemtime(KC_ROOT.KC_CACHE_PATH.'/verify/'.$val);//读取文件日期
		if($filetime<time()-$king->config('verifytime'))//如果文件日期小于给定的日期，则删除
			kc_f_delete(KC_CACHE_PATH.'/verify/'.$val);
	}

}

}
?>