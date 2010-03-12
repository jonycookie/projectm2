<?php !defined('INC') && exit('No direct script access allowed');

class KC_Language_class{

//变量

private $mDoc=array();
private $mPath=array();
private $mModule=array();
private $mLang;

/**
	读取模板
	@param string $node  语言结构 system/common/title
	@return
*/
public function get($lang,$num=0){

	list($module,$node)=explode('/',$lang,2);

	if(!in_array($module,$this->mModule)){
		if(!$this->load($module))
			return '['.$lang.']';
	}

	$entries=$this->mPath[$module]->evaluate('//kingcms/'.$node);

	if(!empty($entries->item($num)->nodeValue)){
		$value=$entries->item($num)->nodeValue;
	}else{
		$value='['.$lang.']';
	}
	return nl2br($value);
}
/**
	返回语言
	@param
	@return
*/
public function getLang(){
	return $this->mLang();
}
/**
	设置语言
	@param string $lang 语言
	@return void
*/
public function set($lang){
	$this->mDoc=array();
	$this->mPath=array();
	$this->mModule=array();
	$this->mLang=$lang;
}
/**
	获取当前模块目录
	@return string
*/
private function getPath(){
	$path=$_SERVER['PHP_SELF'];
	return basename(dirname($path));
}
/**
	加载语言包文件
	@param $module  : 模块(插件)名称
	@param $language: 语言
	@return
*/
private function load($module='system'){/*
*/
	$filepath='';
	$getLanguage='';

	$this->mDoc[$module]=new DOMDocument;

	if($this->mLang=='')
		$language=kc_cookie('language');

	if(!isset($language{0}))
		$language=LANGUAGE;


	if($module=='plugin'){
		$path=$this->getPath();
		global $action;
		$plugin= $action=='ajax' ? CMD : $action;
		$filepath=ROOT.$path.'/plugin/'.$plugin.'/'.$language.'.xml';
	}else{
		$filepath=ROOT.$module.'/language/'.$language.'.xml';
	}

	if(!file_exists($filepath)){
		$language=LANGUAGE;
		$filepath=ROOT.$module.'/language/'.$language.'.xml';
	}

	if(file_exists($filepath)){
		$this->mLang=$language;
	}else{
		return False;
	}

	$this->mDoc[$module]->load($filepath);

	$this->mPath[$module]=new DOMXPath($this->mDoc[$module]);

	$this->mModule[]=$module;

	if($module=='system'){
		$jsFile='system/js/lang.'.$language.'.js';
		if(!file_exists(ROOT.$jsFile)){//若无文件
			$entries=@$this->mPath['system'];
			$lang=array();

			$s="jQuery.extend({kc_lang:function(s){var lang=new Array();".NL;
			$array=array('delete','clear','logout','set','close');
			foreach($array as $val){
				$s.="lang['{$val}']='".addslashes($entries->evaluate('//kingcms/confirm/'.$val)->item(0)->nodeValue)."';".NL;
			}
			$s.="lang['timeout']='".addslashes($entries->evaluate('//kingcms/error/timeout')->item(0)->nodeValue)."';".NL;
			$s.="lang['empty']='".addslashes($entries->evaluate('//kingcms/error/empty')->item(0)->nodeValue)."';".NL;
			$s.="lang['enter']='".addslashes($entries->evaluate('//kingcms/common/enter')->item(0)->nodeValue)."';".NL;
			$s.="lang['up']='".addslashes($entries->evaluate('//kingcms/common/moveup')->item(0)->nodeValue)."';".NL;
			$s.="lang['down']='".addslashes($entries->evaluate('//kingcms/common/movedown')->item(0)->nodeValue)."';".NL;
			$s.="lang['updown']='".addslashes($entries->evaluate('//kingcms/common/updown')->item(0)->nodeValue)."';".NL;
			for($i=0;$i<=6;$i++){
				$s.="lang['week{$i}']='".addslashes($entries->evaluate('//kingcms/time/week'.$i)->item(0)->nodeValue)."';".NL;
			}

			$s.="return lang[s];}});";

			kc_f_put_contents($jsFile,$s);
		}
	}

	return True;
}

/**
	释放
	@param
	@return
*/
public function close(){
	unset($this->mDoc);
	unset($this->mPath);
	unset($this->mModule);
}


}


?>