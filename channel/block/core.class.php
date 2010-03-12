<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

class block_class{// implements KingCMS_module

private $path='block';	//当前模块目录
private $dbver=100;	//当前模块的数据库版本
public  $lang;


/**
	构造函数，主要是用做版本判断
*/
public function __construct(){
	global $king;

	$dbver=(int)$king->getModuleVer($this->path);
	if($this->dbver > $dbver){
		if($this->install_update($dbver)){//成功则返回True，则更新数据库中的版本信息
			$array=array(
				'ndbver'=>$this->dbver,
			);
			$king->db->update('%s_module',$array,"kpath='{$this->path}'");
		}
	}
}

public function infoBlock($kid){
	global $king;
	$cachepath="block/info/$kid";

	if(!$block=$king->cache->get($cachepath)){
		if(!$block=$king->db->getRows_one("select * from %s_block where kid=$kid"))
			kc_error($king->lang->get('system/error/param'));
		$king->cache->put($cachepath,$block);
	}
	return $block;
}

/* ------>>> 安装部分 <<<---------------------------- */

public function install(){
	global $king;

	//_block
	$sql='kid1 int not null default 0,
	kname char(100) not null,
	kcontent text null,
	ntype tinyint not null default 0,
	bid int not null default 0,
	norder int not null default 0,
	INDEX(kname)';
	
	$king->db->createTable('%s_block',$sql,'kid');

	$this->install_update(100);

	//写模块安装记录
	if(!$king->db->getRows_one('SELECT * FROM %s_module where kpath=\'block\';')){
		$_array=array(
			'kname' =>$king->lang->get('block/name'),
			'kpath' =>$this->path,
			'kdb'   =>'',
			'ndate' =>time(),
			'ndbver'=>$this->dbver,
			'norder'=>$king->db->neworder('%s_module'),
			'nshow'=>0,
			);
		$kid1=$king->db->insert("%s_module",$_array);

		return true;
	}else{
		return false;
	}



} //!install
public function install_update($ver){


	return True;
}







/* ------>>> 标签解析 <<<---------------------------- */

/**

	@param string $name   标签名  portal.article
	@param string $inner  循环体内的
	@param array $ass     assign 内容
	@param array $attrib  属性数组

	@return string

*/

public function tag($name,$inner,$ass,$attrib){
	global $king;

	$name=kc_val($attrib,'name');
	if(empty($name)){
		return $king->lang->get('block/error/name',4);
	}

	if(!$res=$king->db->getRows("select kid,ntype,bid,kcontent from %s_block where kname='".$king->db->escape($name)."' "))
		return $king->lang->get('block/error/name',5);

	$array=array();
	foreach($res as $rs){
		$array["{$rs['ntype']}-{$rs['bid']}"]=$rs['kcontent'];
	}

	//很麻烦的绑定判断
	$listid=kc_val($ass,'listid');
	if(empty($listid)){//如果listid为空值的话，直接调用默认值
		$content=kc_val($array,'0-0');
	}else{
		if(isset($array["1-$listid"])){//先判断listid
			$content=$array["1-$listid"];
		}else{//再判断modelid
			if(!isset($ass['modelid'])){//若ass中没有modelid，则从info中加载
				$king->Load('portal');//加载portal类
				$info=$king->portal->infoList($listid);
				$modelid=$info['modelid'];
			}else{
				$modelid=$ass['modelid'];
			}
			//判断modelid
			if(isset($array["2-$modelid"])){
				$content=$array["2-$modelid"];
			}else{//连modelid都没有的情况下才会去判断siteid
				if(!isset($ass['siteid'])){//若ass中没有siteid
					if(empty($info)){//如果info没有加载，则加载，似乎这个可能性发生的概率为0?
						$king->Load('portal');
						$info=$king->portal->infoList($listid);
					}
					$siteid=$info['siteid'];
				}else{
					$siteid=$ass['siteid'];
				}
				if(isset($array["3-$siteid"])){
					$content=$array["3-$siteid"];
				}else{//只能调用默认值
					$content=kc_val($array,"0-0");
				}
			}
		}
	}

	//获得了$content值后，调用模板解析
	$tmp=new KC_Template_class;
	if(is_array($ass)){
		foreach($ass as $key => $val){
			$tmp->assign($key,$val);
		}
	}
	$s=$tmp->output($content);

	return $s;

}



























}//!portal_class

?>
