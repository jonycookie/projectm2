<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

class KC_XML_class{

private $dom;

/**
	从字符串中创建xml对象
	@param string $str;
	@return void
*/
public function load_string($str){
	$this->dom=simplexml_load_string($str);
}
/**
	从文件中读取创建xml对象
	@param string $filepath
	@return void
*/
public function load_file($filepath){
	$path=ROOT.$filepath;
	if(is_file($path)){

		kc_runtime('loadXMLFile');

		$this->dom=simplexml_load_file($path);

		kc_runtime('loadXMLFile',1);

	}else{
		global $king;
		kc_error($king->lang->get('system/error/notxmlfile').' '.$path);
	}
}

/**
	数组转换为xml文档
	@param array  $array 数组
	@param int    $is    递归时用的参数
	@return string
*/
public function array2xml($array,$is=0){

	$str='';

	foreach($array as $key => $val){
		if(is_array($val)){//若为数组
			$str.="<$key>".NL.$this->array2xml($val,1).NL."</$key>".NL;
		}elseif(kc_validate($key,23)){

			kc_validate($val,4)||$val==null
				? $str.="<$key>$val</$key>".NL
				: $str.="<$key><![CDATA[".$val."]]></$key>".NL;
/*
*/
		}
	}

	if($is==0){
		$s='<?xml version="1.0" encoding="UTF-8"?>';
		$s.='<kingcms>';
		$s.=$str;
		$s.='</kingcms>';
	}else{
		$s=$str;
	}

	return $s;
}
/**
	xml转换为数组
	@param object $dom
	@return array
*/
public function xml2array($dom=null){
	$dom= $dom==null ? $this->dom:$dom;
	if(get_class($dom)=='SimpleXMLElement'){
		$attributes=$dom->attributes();
		foreach($attributes as $k=>$v){
			if ($v) $a[$k]=(string) $v;
		}
		$x=$dom;
		$dom=get_object_vars($dom);
	}
	if(is_array($dom)){
		if (count($dom) == 0) return (string) $x;
		foreach($dom as $key=>$value){
			$r[$key]=$this->xml2array($value);
		}
		if(isset($a)) $r['@']=$a;
		return $r;
	}
	return (string) $dom;
}
/**
	读取属性，并以数组形式返回
	@param string $node 路径
	@return array
*/
public function attrib($node){
	$attributes='';
	eval('$attributes=$this->dom->'.$node.'[0]->attributes();');
	return $attributes;
}

}
?>
