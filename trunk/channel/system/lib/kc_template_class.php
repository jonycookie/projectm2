<?php
/* ======= >>> KingCMS 类 <<<======================== *

 +   许可协议: http://www.KingCMS.com/license/        +

 +   官方网站: http://www.KingCMS.com/                +

 +   电子邮件: KingCMS(a)Gmail.com                    +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== *

 +   KingCMS模版标签的解析

 * ------>>> Template_class <<<---------------------- */

class KC_Template_class{

private $tmp='';//合并后的模板代码
public  $parent='%\{((\w+):([\w\.]+))([^}]*)/\}|\{((\w+):([\w\.]+))([^}]*)\}(.*?)\{/\5\}%s';//父标签
private $tempArray;//临时数组----
//public  $parent1='%\(\w+)\=([\'"])\w+(\2)%s';//子标签

private $array=array();//assign的数据


public function KC_Template_class($outTmp='',$inTmp=''){//读取模板 并 合并
	global $king;

	$s=kc_f_get_contents(strtolower($outTmp));
	isset($inTmp{0})
		?$s_in=kc_f_get_contents(strtolower($inTmp))
		:$s_in='{king:inside/}';
	$s=preg_replace('/{king:inside\s*\/}/i',$s_in,$s,1);
	$s=preg_replace('%(</title[^>]*>)%s',"\${1}\n<meta name=\"generator\" content=\"KingCMS\"/>\n<script type=\"text/javascript\" src=\"".$king->config('inst')."system/js/jquery.js\"></script>\n<script type=\"text/javascript\" src=\"".$king->config('inst')."system/js/jquery.kc.js\"></script>",$s,1);
	$s=preg_replace('%(\<(script|link|img|input|embed|object|base|area|map|table|td|th|tr|param) [^>]*(src|href|background|value)=(["\']))((\.\.\/)+)(images\/[^>]+\4[^>]*\>)%s',"\${1}".$king->config('siteurl').$king->config('inst')."\${7}",$s);
	//过滤HTML注释<!--  -->
	if($king->config('templatefiternote')){
		$s=preg_replace('/<\!--((?!-->|<\!--).|\n)*-->/i','',$s);
	}


	$this->tmp=$s;
}
/**
	格式化路径
	@param string $m
	@return string
//public function formatPath($m){
	kc_error('<pre>'.print_r($m,1));
}
*/
/**
设置模板内容
*/
public function set($s){
	$this->tmp=$s;
}

/**
	标签与数据关联
	@param string $key : 模板标签，但传递值用的标签以#开头做区分
	@param string $val : 值
*/
public function assign($key,$val){
	$this->array[strtolower($key)]=$val;
}
/**
	输出最终结果
*/
public function output($t=null){
	$tmp=$t ? $t : $this->tmp;
	if(substr($tmp,0,6)=='{Tags}'){
		$s='<div style="border:5px solid #CCC;background:#EFEEEE;padding:15px;line-height:20px;">';
		foreach($this->array as $key => $val){
			$s.="<tt>{king:$key/}</tt> -&gt; $val <br/>";
		}
		$s.='</div>';

	}else{
		kc_runtime('Template');

		$s=preg_replace_callback($this->parent,array(&$this,'regexcallback'),$tmp);

		kc_runtime('Template',1);

	}

	//开始解析php代码

	$parent='/<\?(php)?(\S*?)((.|\n)+?)\?>/is';

	$s=preg_replace_callback($parent,array(&$this,'regexphpcallback'),$s);

	return $s;
}

/**
	PHP代码解析回调函数
	@param array $m  PHP代码内容
	@return string
*/
public function regexphpcallback($m){
	$php=$m[3];

	if(isset($php)){
		ob_start();
		eval($php);
		$s=ob_get_clean();
	}

	return $s;
}
/**
	KingCMS标签解析回调函数
	@param array $m
	@param array $val  传值
	@return string
*/
public function regexcallback($m,$val=null){
/**
$prefix      冒号前面的名称，一般不是king则为php
$name        冒号后面的名称
$attributes  属性
$inner       循环部分
*/
	global $king;
	$s='';
	$ass= $val? $val : $this->array;

	if(count($m)==5){
		$prefix=strtolower($m[2]);
		$name=strtolower($m[3]);
		$attributes=$m[4];
		$attrib=$this->attrib2array($attributes,$ass);
		if(isset($attrib['conn'])){//如果有conn属性，则不继续执行，直接远程调用数据
			if($getconn=$this->getConn($attrib['conn'],$m[0],$ass)){
				return $getconn;//不需要 $this->str_format 过程，因为目标站里已经进行完了
			}else{
				return False;
			}
		}

		switch($prefix){
			case 'king':

				if(array_key_exists($name,$ass)){//如果在ACC列表中存在对象的话
					$s=$ass[$name];
				}else{//不在$ass列表中的，需要单独做判断
/*
					if(in_array($name,array('root'))){
						return $king->config('inst');
					}
*/
					if(false!==($ret=$this->sysinfo($name))) return $ret;

					//特殊的portal标签,增加portal标记，以便在portal中分析
					if(in_array($name,array('nav','pagelist','list','menu','menu1','menu2','menu3','menu4','menu5')))
						$name='portal.'.$name;
					//获得ClassName
					$clsName=kc_f_name($name);

					if(in_array($clsName,array('skin'))){//,'db'
						$s=$king->$clsName->tag($name,'',$ass,$attrib);
					//判断这个class是否已经被安装
					}elseif($king->isModule($clsName)){
						$classname=$clsName.'_class';
						$cls=new $classname;
						$s=$cls->tag($name,'',$ass,$attrib);
					}elseif(in_array($clsName,array('keywords','description'))){
						$s=$ass['title'];
					}else{
						$s="";
					}
				}
			break;

			case 'config':
				$s=$king->getConfig($name);
			break;

			case 'lang':
				$s=$king->lang->get(str_replace('.','/',$name));
			break;

			case 'get':
				$s=kc_get($name,0);
				$validate=kc_val($attrib,'validate');
				if(isset($validate{0})){
					if(!kc_validate($s,$validate)){
						kc_error($king->lang->get('system/error/param').'<br/>ID:'.$name.';Value:'.$s);
					}
				}
			break;

			case 'post':
				$s=kc_post($name,0);
				$validate=kc_val($attrib,'validate');
				if(isset($validate{0})){
					if(!kc_validate($s,$validate)){
						kc_error($king->lang->get('system/error/param').'<br/>ID:'.$name.';Value:'.$s);
					}
				}
			break;

		}

	}else{

		//判断这个class对应的模型是否已经被安装

		$prefix=strtolower($m[6]);
		$name=strtolower($m[7]);
		$attributes=$m[8];
		$inner=$m[9];
		$attrib=$this->attrib2array($attributes,$ass);
		if(isset($attrib['conn'])){//如果有conn属性，则不继续执行，直接远程调用数据
			if($getconn=$this->getConn($attrib['conn'],$m[0],$ass)){
				return $getconn;
			}else{
				return False;
			}
		}

		switch($prefix){
			case 'king':


				if(array_key_exists($name,$ass)){//如果在ACC列表中存在对象的话
					if(is_array($ass[$name])){
						//如果直接传递数组的话，无需转换
						$s=$this->array_format($inner,$ass[$name]);
					}else{

						$split=kc_val($attrib,'split');
						$explode=kc_val($attrib,'explode');

						if(isset($split{0})){

							$assname=$ass[$name];
							if(isset($assname{0})){

								$array_split=explode($split,$ass[$name]);//拆分值为数组
								$array=array();
								$i=1;
								foreach($array_split as $val){
									$array[$name.'_'.$i++]=$val;//设置成{king:V_N/}类型
								}
								$s=$this->array_format($inner,array($array));
							}

						}elseif(isset($explode{0})){//如果是用explode拆分数据的话，输出可循环的
							$assname=$ass[$name];
							if(isset($assname{0})){
								$array_explode=explode($explode,$ass[$name]);
								$array=array();
								foreach($array_explode as $val){
									$array[]=array($name=>$val);
								}
								$s=$this->array_format($inner,$array);
							}
						}elseif(kc_validate($ass[$name],25)){
							$ass_array=unserialize(base64_decode($ass[$name]));
							$s=$this->array_format($inner,$ass_array);
						}

					}
				}else{
					$iscache=False;

					if(in_array($name,array('nav','pagelist','list','menu','menu1','menu2','menu3','menu4','menu5')))
						$name='portal.'.$name;

					$clsName=kc_f_name($name);

					//需要把$attributes中的值替换完成后传递下去
					$attrib=$this->attrib2array($attributes,$ass);//这个是一个数组，结构：Array([listid] => 1 , [type] => 添加测试文章，标题是7486)
					$name=strtolower($name);

					//读取cache属性，如果有缓存，则直接读取对应的缓存文件。

					if(isset($attrib['cache'])){
						$cachepath='system/cache/'.strtolower($attrib['cache']);
						$s=$king->cache->get($cachepath,time()-$king->config('cachetime'));
						if(isset($s{0})) return $s;
						$iscache=True;
					}

					if(isset($attrib['remote'])){//跨站解析标签

					}


					if(in_array($clsName,array('skin'))){//,'db'
						$s=$king->$clsName->tag($name,$inner,$ass,$attrib);
					}elseif($king->isModule($clsName)){
						$classname=kc_f_name($name).'_class';
						$cls=new $classname;

						$s=$cls->tag($name,$inner,$ass,$attrib);

					}else{
						$s="<!-- {$m[0]} -->";
					}

					if($iscache) $king->cache->put($cachepath,$s);//写cache

				}



			break;

		}


	}

	return $this->str_format($s,$attrib);

}
/*
	数组类数据格式化
	@param string $inner 循环调用
	@param array  $array  数组，结构如下

		$array=array(
			array(
				'kid'=>1,
				'listid'=>2,
			),
			array(
				'kid'=>2,
				'listid'=>2,
			),
		);
*/
private function array_format($inner,$array){

	$s='';


	if(!empty($array)){
		foreach($array as $arr){
			$tmp=new KC_Template_class;
			foreach($arr as $key => $val){
				$tmp->assign($key,$val);
			}
			$s.=$tmp->output($inner);
		}

	}

	return $s;
}
/**
	格式化字符串

	@param string $s    : 字符模板
	@paran string $attrib : 字符串属性，应该是size="20"这种类型的，具体做的时候还得进行输出判断，attrib可能的取值如下
		width,height : 如果有这两个或一个属性，则对$str进行文件判断，如果是则进行相关图片处理操作
		replace      : 字符串替换，replace="A|B"，A替换为B
		size         : 字符长度设置
		code         : 字符转换js/html
		none         : 空值替换属性
*/
private function str_format($s,$attrib){

	if(empty($attrib)) return $s;//如果是空值，则直接返回s值

	//转换
	if(array_key_exists('formatstr',$attrib)){
		$code=$attrib['formatstr'];
		if(isset($code{0})){
			switch(strtolower($code)){
				case 'javascript':
					$s=str_replace(
						array('\'',"\n",chr(13)),
						array('\\\'','\n','')
					,$s);
				break;
				case 'urlencode':$s=urlencode($s);break;
				case 'addslashes':$s=addslashes($s);break;
				case 'md5':$s=md5($s);break;
			}
		}
	}
	//应用函数
	if(array_key_exists('fun',$attrib)){
		$fun=$attrib['fun'];
		$funs=explode(',',$fun);
		$array=array(1=>$s);
		foreach($funs as $fun){
			if(function_exists($fun)){//如果有指定的函数，则应用
				$array1=array_map($fun,$array);
			}
		}
		$s=$array1[1];
	}
	//替换
	if(array_key_exists('replace',$attrib)){
		$replace=$attrib['replace'];
		if(is_array($replace)){
			foreach($replace as $key => $val){
				$s=str_replace($key,$val,$s);
			}
		}
/*
		if(isset($replace{0})){
			list($find,$new)=kc_explode('|',$replace,2);
			$s=str_replace($find,$new,$s);
		}
*/
	}
	//长度
	if(array_key_exists('size',$attrib)){

		$size=$attrib['size'];
		if($size){
			if(kc_validate($size,2)){
				$s=kc_substr($s,0,$size);
			}
		}
	}
	//日期格式化
	if(array_key_exists('formatdate',$attrib)){
		$format=$attrib['formatdate'];
		if(kc_validate($s,2)){//默认的时间是int类型的
			$s=kc_formatdate($s,$format);
		}elseif(kc_validate($s,9)){//日期类型 2008-11-9这种格式
			list($yy,$mm,$dd)=explode('-',$s);
			$s=kc_formatdate(gmmktime(0,0,0,$mm,$dd,$yy),$format);//需要转换一下字符
		}
	}
	//数字格式化
	if(array_key_exists('formatnumber',$attrib)){
		if(kc_validate($attrib['formatnumber'],2)){
			$s=number_format($s,$attrib['formatnumber']);
		}
	}
	//缩略图
	if(array_key_exists('width',$attrib)||array_key_exists('height',$attrib)){
		if(array_key_exists('width',$attrib)) $width=$attrib['width'];
		if(array_key_exists('height',$attrib)) $height=$attrib['height'];

		if(($width ||$height) && isset($s{0})){

			$s=kc_image($s,$attrib);

		}
	}
	//默认填充
	if(array_key_exists('none',$attrib)){
		$none=$attrib['none'];
		if(!isset($s{0})){
			$s=$none;
		}
	}

	//前面插入
	if(array_key_exists('before',$attrib)){
		$before=$attrib['before'];
		if(isset($before{0}) && isset($s{0})){
			$s=$before.$s;
		}
	}
	//后面插入,条件是$s不能为空
	if(array_key_exists('after',$attrib)){
		$after=$attrib['after'];
		if(isset($after{0}) && isset($s{0})){
			$s.=$after;
		}
	}


	return $s;
}
/**

	属性转换为数组

	@param string $s    属性 listid="1" type="list"
	@param array  $ass  assign数组

*/
private function attrib2array($s,$ass=null){

	if($ass){
		//替换内部标签
		$this->tempArray=$ass;
		$s=preg_replace_callback('%\(((\w+):(\w+))([^)]*)/\)%s',array(&$this,'attribBack'),$s);
		$this->tempArray=array();
	}

	preg_match_all('/([\w\-!]+)=(["\'])(.*?)\2/s', $s, $array, PREG_SET_ORDER);


	$newArray=array();
	if($array){
		foreach($array as $val){
			$key=strtolower($val[1]);
			if($key=='replace'){//支持多个replace功能
				list($k,$v)=kc_explode('|',$val[3],2);
				$newArray['replace'][$k]=$v;
			}else{
				$newArray[$key]=$val[3];
			}
		}
	}

	return $newArray;

}
private function attribBack($m){
	$attributes=$m[4];
	$attrib=$this->attrib2array($attributes);
	$s='';
	switch(strtolower($m[2])){
		case 'king':
			$s=kc_val($this->tempArray,$m[3]);//值
			if(false!==($ret=$this->sysinfo($m[3]))) return $ret;
		/*
			if(in_array($m[3],array('root','version','cms'))){
				global $king;
				switch($m[3]){
					case 'root':$s=$king->config('inst');break;
					case 'version':$s=$king->devname;break;
					case 'cms':$s="<span>Powerd by <a href=\"http://www.kingcms.com/\" title=\"KingCMS\" target=\"_blank\">KingCMS</a> ".$king->devname ."</span>";break;
				}
				return $s;
			}
		*/
		break;

		case 'get':
			$s=kc_get($m[3],0);
		break;

		case 'post':
			$s=kc_post($m[3],0);
		break;
	}
	return $this->str_format($s,$attrib);
}
/**
	返回系统信息
	@param string name 标签名称
	@return string
*/
private function sysinfo($name){
	if(in_array($name,array('root','version','cms'))){
		global $king;
		switch($name){
			case 'root':$s=$king->config('inst');break;
			case 'version':$s=$king->devname;break;
			case 'cms':$s="<span>Powerd by <a href=\"http://www.kingcms.com/\" title=\"KingCMS\" target=\"_blank\">KingCMS</a> ".$king->devname ."</span>";break;
		}
		return $s;
	}
	return false;
}
/**
	返回%s_conn表信息
	@param string $kname
	@return array || false
*/
public function infoConn($kname){
	global $king;

	$cachepath='system/conn/info';
	$array=$king->cache->get($cachepath);
	if(!$array){
		if($res=$king->db->getRows("select kname,urlpath,ksign from %s_conn")){
			foreach($res as $rs){
				$array[$rs['kname']]=$rs;
			}
			$king->cache->put($cachepath,$array);
		}
	}

	return isset($array[$kname]) ? $array[$kname] : false;
}
/**
	getConn
	@param string $kname 调用的链接名称
	@return string
*/
private function getConn($kname,$tags,$ass){
	if(($info=$this->infoConn($kname))!==false){

		$tags=preg_replace('/conn=(["\'])(.*?)\1/i','',$tags);//


//kc_error($tags);
		$array=array(
			'ass'=>base64_encode(serialize($ass)),
			'kname'=>$kname,
			'tags'=>$tags,
		);
		$sign=md5("ass={$array['ass']}&kname=$kname&tags=$tags{$info['ksign']}");
		$array['sign']=$sign;
		return kc_fopen($info['urlpath'].'/api/kc.php',$array);
	}else{
		return '<!-- Not Connect! -->';
	}
}

public function __destruct(){
	unset($this->array);
}


}//!Template_class

?>