<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

class feedback_class{// implements KingCMS_module

private $path='feedback';	//当前模块目录
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

public function infofeedback($kid){
	global $king;
	$cachepath="feedback/info/$kid";

	if(!$feedback=$king->cache->get($cachepath)){
		if(!$feedback=$king->db->getRows_one("select * from %s_feedback where kid=$kid"))
			kc_error($king->lang->get('system/error/param'));
		$king->cache->put($cachepath,$feedback);
	}
	return $feedback;
}

/* ------>>> 安装部分 <<<---------------------------- */

public function install(){
	global $king;

	//_feedback
	$sql='ktitle char(100) not null,
	kname char(50) not null,
	kemail char(100) not null,
	kphone char(20) null,
	kqq char(50) null,
	kcontent text null,
	nread tinyint(1) not null default 0,
	norder int not null default 0,
	ndate int(10) not null default 0,
	INDEX(ktitle)';
	
	$king->db->createTable('%s_feedback',$sql,'kid');

	$this->install_update($this->dbver);

	//写模块安装记录
	if(!$king->db->getRows_one('SELECT * FROM %s_module where kpath=\'feedback\';')){
		$_array=array(
			'kname' =>$king->lang->get('feedback/name'),
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
	return true;
}







/* ------>>> 标签解析 <<<---------------------------- */

/**

	@param string $name   标签名  feedback
	@param string $inner  循环体内的
	@param array $ass     assign 内容
	@param array $attrib  属性数组

	@return string

*/

public function tag($name,$inner,$ass,$attrib){
	global $king;


}








}//!portal_class

?>
