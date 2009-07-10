<?php
/**
ajax 获取动态排行类
**/
!defined('IN_CMS') && die('Forbidden');
class Ajax{
	var $mid;
	var $index;
	var $cachefile;
	var $condition;
	var $field;
	var $thisid;
	function __construct($field){ //PHP5 操作类型
//		$this->condition = $condition;
//		$this->cachefile = D_P."js/".substr(md5($condition),32).".js";
		$this->field	= $field;
		$this->index	= array('title','postdate','url','linkurl','photo','digest','comnum','publisher','titlestyle');
	}

	function Ajax($method){ //PHP4
		$this->__construct();
	}

	function getThread(){
		global $cms;
		!is_object($cms) && $cms = new Cms;
		$array = $cms->thread($this->condition);
		$jscontent = "var ".$this->thisid."=new Array();\r\n";
		$i=0;
		foreach($array as $content){
			$jscontent.=$this->thisid."[$i]={";
			$tempvar =array();
			foreach($content as $key=>$val){
				if($this->mid>0){
					foreach($this->field as $field){
						if(in_array($key,$this->index)||($key==$field['fieldid'] && !in_array($field['inputtype'],array('edit','basic')))){
							$key == "postdate" && $val = get_date($val,'Y-m-d');
							$tempvar[]="'".$key."':'".addslashes($val)."'";
							break;
						}
					}
				}else{
					$key == "postdate" && $val = get_date($val,'Y-m-d');
					$tempvar[]="'".$key."':'".addslashes($val)."'";
				}
			}
			$jscontent.=implode(',',$tempvar)."};\r\n";
			$i++;
		}
		writeover($this->cachefile,$jscontent);
	}
}
?>