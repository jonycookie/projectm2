<?php
/* ======= >>> 内容缓存类 <<<======================== *

 +   许可协议: http://www.KingCMS.com/license/        +

 +   官方网站: http://www.KingCMS.com/                +

 +   电子邮件: KingCMS(a)Gmail.com                    +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== *

 +   KingCMS缓存类，比较简单，待进一步完善

 * ------>>> Cache_class <<<------------------------- */

/**

对$GLOBALS['Cache_class_array']的count进行判断，如果大于100则要清空，不然内存消耗太大。

*/
class KC_Cache_class{

private $ext='.php';

/*
public function __construct(){
	//用全局数组缓存
	if(empty($GLOBALS['Cache_class_array']))
		$GLOBALS['Cache_class_array']=array();
}
public function __destruct(){
	unset($GLOBALS['Cache_class_array']);
} //!__destruct
*/
/**
	读取缓存的内容
	@param string $path     路径
	@param string $time     缓存文件过期时间设置
		1  当$time==1的时候，仅读取预定的缓存时间内的内容

*/
public function get($path,$time=null){
	global $king;
/*
	if(empty($GLOBALS['Cache_class_array']))
		$GLOBALS['Cache_class_array']=array();
*/
	$path=PATH_CACHE.'/'.strtolower($path).$this->ext;
/*
*/
	if(empty($GLOBALS['file_get_contents_array']))
		$GLOBALS['file_get_contents_array']=array();

	if(array_key_exists($path,$GLOBALS['file_get_contents_array'])){//如果已经存在这个键值
		return $GLOBALS['file_get_contents_array'][$path];
	}else{//如果没有这个键值，则加入
		if(file_exists(ROOT.$path)){
			if($time){//如果有日期限制，则需要做比较
				if($time==1) $time=time()-$king->config('cachetime');
				$filetime=filemtime(ROOT.$path);//读取文件日期
				if($filetime<$time)//如果文件日期小于给定的日期，则为过时
					return false;
			}
			/**/
//			$s=unserialize(kc_f_get_contents($path));
			$s=unserialize(substr(kc_f_get_contents($path),49));
			/**
			//unserialize效率竟然远远高于include??
			$s=include(PATH_CACHE.'/'.$path.$this->ext);
			/**/
			$GLOBALS['file_get_contents_array'][$path]=$s;//重写file数组
			return $s;
		}else{
			return false;
		}
	}
/*
*/
}
/**

	写入缓存的内容

	@param string $path     路径
	@param string $content  要缓存的内容

*/
public function put($path,$content){

	$path=PATH_CACHE.'/'.strtolower($path).$this->ext;
	/**/
	kc_f_put_contents($path,'<?php exit(\'No direct script access allowed\'); ?>'.serialize($content));
	/**
	if(is_array($content)){
		kc_f_put_contents(PATH_CACHE.'/'.$path.$this->ext,print_r($content,1));
	}else{
		kc_f_put_contents(PATH_CACHE.'/'.$path.$this->ext,$content);
	}
	/**/
	$GLOBALS['file_get_contents_array'][$path]=$content;
	return $content;
}
/**

	删除缓存文件

	@param string $path     路径

*/
public function del($path){
	$path=strtolower($path);
	kc_f_delete(PATH_CACHE.'/'.$path.'.php');
}
/**

	删除缓存目录

	@param string $path     路径

*/
public function rd($path=null){

	if($path){
		$path=strtolower($path);
		kc_f_rd(PATH_CACHE.'/'.$path);
	}else{
		kc_f_rd(PATH_CACHE);
	}
}
/**
	读取文件缓存信息
	@param string $path  路径
	@return string
*/
public function info($path){
	global $king;
	$s='<table class="k_cache"><tr><td class="l">'.kc_icon('n1').' '.$king->lang->get('system/time/cache').': ';
	$filename=ROOT.PATH_CACHE.'/'.$path.$this->ext;
	$filemtime=is_file($filename)?filemtime($filename):0;
	$s.=kc_formatdate($filemtime);
	$s.=' -&gt; ('.kc_formattime(time()-$filemtime);
	$s.=')</td><td class="c w100"><a class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'close_cachetip\'}">';
	$s.=$king->lang->get('system/time/cacheclose');
	$s.='</a></td></tr></table>';
	return $s;
}





}//!Cache_class

?>
