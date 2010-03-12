<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

class dbquery_class{// implements KingCMS_module

private $path='dbquery';	//当前模块目录
private $dbver=100;	//当前模块的数据库版本
public  $lang;
private $db;//数据源对象


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

public function info($name){
	global $king;
	$cachepath="dbquery/info/$name";

	if(!$dbquery=$king->cache->get($cachepath)){
		if(!$dbquery=$king->db->getRows_one("select * from %s_dbquery where kname='".$king->db->escape($name)."'"))
			return False;
		$king->cache->put($cachepath,$dbquery);
	}
	return $dbquery;
}



/* ------>>> 安装部分 <<<---------------------------- */

public function install(){
	global $king;

	//_block
	$sql='kname char(100) not null,
	ntype tinyint not null default 0,
	dbhost char(50) null,
	dbname char(50) null,
	dbuser char(50) null,
	dbpass char(50) null,
	dbfile char(100) null,
	dbcharset char(20) null,
	norder int not null default 0';
	
	$king->db->createTable('%s_dbquery',$sql,'kid');

	$this->install_update(100);

	//写模块安装记录
	if(!$king->db->getRows_one('SELECT * FROM %s_module where kpath=\'dbquery\';')){
		$_array=array(
			'kname' =>$king->lang->get('dbquery/name'),
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

	@param string $pre     标签名
	@param string $inner   循环体内的
	@param array  $ass     assign 内容
	@param array  $attrib  属性数组

	@return string

*/

public function tag($pre,$inner,$ass,$attrib){
	global $king;
//	print_r($this->db);

	$name=kc_val($attrib,'name');
	if(isset($name{0})){
		$db=$this->info($name);
		if($db!==False){
			
			switch((int)$db['ntype']){
			case 1://mysql
				$this->db=new KC_mysql_class;
				$this->db->connect($db['dbhost'],$db['dbname'],$db['dbuser'],$db['dbpass']);
			break;
			
			case 2://sqlite
				$this->db=new KC_sqlite_class;
				$this->db->connect($db['dbfile']);
			break;
			
			}
		}else{
			return False;
		}
	}else{
		$this->db=$king->db;
	}



	switch($pre){
		case 'dbquery.count':return $this->tag_count($attrib);break;
		case 'dbquery.select':return $this->tag_select($inner,$attrib);break;
		case 'dbquery':return $this->tag_dbquery($inner,$attrib);break;
	}
}

private function tag_count($attrib){
	//获得表格
	$table=kc_val($attrib,'table');
	if(!kc_validate($table,'/\%s_[A-Za-z0-9_]/')) return False;//判断table数据类型
	//获得搜索条件
	$where=kc_val($attrib,'where');

	return $this->db->getRows_number($table,$where);
}

private function tag_select($inner,$attrib){

	$table=kc_val($attrib,'table');
	if(!kc_validate($table,'/\%s_[A-Za-z0-9_]/')) return False;//判断table数据类型

	$where=isset($attrib['where']) ? ' where '.$attrib['where'] : '';

	$sql=isset($attrib['sql']) ? kc_val($attrib,'sql') : '*';

	$number=isset($attrib['number']) ? $attrib['number'] :10;
	if(!kc_validate($number,2)) $number=10;

	$skip=isset($attrib['skip']) ? $attrib['skip'] : 0;//跳过skip个后读取
	if(!kc_validate($skip,2)) $skip=0;

	$orderby=isset($attrib['orderby']) ? ' order by '.$attrib['orderby'] : '';

	$limit='limit '.$skip.','.$number;

	$s='';
	if($res=$this->db->getRows("select $sql from $table $where $orderby $limit")){
		$tmp=new KC_Template_class();
		foreach($res as $rs){
			foreach($rs as $key => $val){
				$tmp->assign($key,$val);
			}
			$s.=$tmp->output($inner);
		}
	}else{
		return False;
	}

	return $s;

}

private function tag_dbquery($inner,$attrib){
	$query=kc_val($attrib,'query');

	$s='';

	if($res=$this->db->getRows($query)){
		$tmp=new KC_Template_class();
		foreach($res as $rs){
			foreach($rs as $key => $val){
				$tmp->assign($key,$val);
			}
			$s.=$tmp->output($inner);
		}
	}
	return $s;
}



}



?>
