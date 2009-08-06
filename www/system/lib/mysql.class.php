<?php
/* ======= >>> 封装数据库(Mysql)访问类 <<<=========== *

 +   许可协议: http://www.KingCMS.com/license/        +

 +   官方网站: http://www.KingCMS.com/                +

 +   电子邮件: KingCMS(a)Gmail.com                    +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== */

class KC_mysql_class{

//变量

public  $link=0;         //数据库连接
private $mQuery=0;        //查询结果
private $Field_Result=array();
private $Rows;
private $Fields;
private $Row_Position=0;
private $pid;
private $rn;
public  $mPagelist;           //分页代码，通过$this->pagelist函数获得
private $ispagelist;


public function __construct(){
	//当前页数
	$pid=isset($_GET['pid']) ? $_GET['pid'] :1;
	$rn=isset($_GET['rn']) ? $_GET['rn'] :20;

	$this->pid=(kc_validate($pid,2) ? $pid : 1);
	if($this->pid==0) $this->pid=1;
	//每页显示数

	$this->rn = (kc_validate($rn,2) ? $rn : 20);
	if($this->rn > 100) $this->rn=100;//限制 rn 最大值为100
}
/**
	链接服务器&选择数据库
	@param string $_host  服务器地址
	@param string $_data  数据库名称
	@param string $_user  登录账号
	@param string $_pass  登录密码
	@param int    $_type  链接类型;1永久链接
*/
public function connect($_host='',$_data='',$_user='',$_pass='',$_type=1){

	global $king;

	if($this->link==0){

		$_host=$_host?$_host:KC_DB_HOST;
		$_data=$_data?$_data:KC_DB_DATA;
		$_user=$_user?$_user:KC_DB_USER;
		$_pass=$_pass?$_pass:KC_DB_PASS;

		$this->link=$_type ? @mysql_pconnect($_host,$_user,$_pass) : @mysql_connect($_host,$_user,$_pass);

		if(!$this->link)
			kc_error($king->lang->get('system/dberr/err1'));

		if(!mysql_select_db($_data,$this->link))
			kc_error($king->lang->get('system/dberr/err2'));

	}

	return $this->link;
}
/**
	释放内存
private function free(){

	unset ($this->Row_Result);
	$this->mQurey=0;
}
*/
/**
	查询
	@param string $_str  SQL代码
	@param int    $is    是否忽略错误;1忽略
*/
public function query($_str,$is=0){

	kc_runtime('DataQuery');

	$num=stripos($_str,'where');
	if(isset($num{0})){
		$_str_left=substr($sql,0,$num);
		$_str_right=substr($sql,$num);
		$_str=str_replace(array('%s','%a'),array(KC_DB_PREFIX,KC_DB_ADMIN),$_str_left).$_str_right;
	}else{
		$_str=str_replace(array('%s','%a'),array(KC_DB_PREFIX,KC_DB_ADMIN),$_str);
	}
/*
	if($this->mQuery){//释放上次查询消耗的内存
		$this->free();
	}
*/
	$this->link==0 && $this->connect();//判断数据库连接是否可用

	try{
		mysql_query('set names '.KC_DB_CHARSET);//设置字符集
		$this->mQuery=mysql_query($_str,$this->link);
	}catch(Exception $e){
		if(KC_CONFIG_DEBUG){
			kc_error('<label>'.$e.'</label><textarea style="width:300px;" rows="6">'.htmlspecialchars($_str).'</textarea>');
		}else{
			global $king;
			kc_error('<label>'.$king->lang->get('system/dberr/err3').'</label>');
		}
	}



	kc_runtime('DataQuery',1);

	return $this->mQuery;
}
/**
	添加数据
	@param string $_table 数据表
	@param array  $_array 操作值
	@return int   返回插入的id
*/
public function insert($_table,$_array){
	$_fields=array();
	$_values=array();
	$_table=str_replace(array('%s','%a'),array(KC_DB_PREFIX,KC_DB_ADMIN),$_table);
	foreach($_array as $_key=>$_val){
		array_push($_fields,$_key);
		array_push($_values,$this->escape($_val));
	}
	$_sql='insert into '.$_table.' ('.implode(',',$_fields).') values (\''.implode('\',\'',$_values).'\');';
	if(!$this->query($_sql)){
		kc_error($_sql);
	}
	return mysql_insert_id();
}
/**
	更新数据
	@param string $table 数据表
	@param array  $array 要更新的数据
	@param string $where 条件语句
*/
public function update($table,$array,$where=null){
	$fields=array();
	$values=array();
	$table=str_replace(array('%s','%a'),array(KC_DB_PREFIX,KC_DB_ADMIN),$table);
	foreach($array as $key=>$val){

		$values[]=preg_match("/^\[\[.+\]\]$/",$val)//[[hit=hit+1]]
			? $key.'='.substr($val,2,-2)
			: $key.'=\''.$this->escape($val).'\'';
	}

	$sql='update '.$table.' set '.implode(',',$values).(isset($where{0}) ? ' where '.$where.';' : ';');
//	kc_error($sql);
	$this->query($sql);
}
/**
	返回记录集
	@param string $_sql  SQL语句
	@param int    $_is   是否带有分页1分页
	@param int    $_pid  页数
	@param int    $_rn   每页显示数
	@return array
*/
public function getRows($_sql,$_is=0,$_pid=0,$_rn=0){

	if($_is){
		if($_pid==0)
			$_pid=$this->pid;	 //第x页 即当前页
		if($_rn==0)
			$_rn=$this->rn;		 //每页显示

		$_sql.=' limit '.($_rn*($_pid-1)).','.$_rn.';';
		$this->ispagelist=1;
	}else{
		$this->ispagelist=0;
	}

	$Row_Result=array();

	$this->query($_sql);
	$this->getRows_number();
	for($i=0;$i<$this->Rows;$i++){
		if(!mysql_data_seek($this->mQuery,$i)){

			global $king;
			if(KC_CONFIG_DEBUG){
				kc_error('<label>'.$king->lang->get('system/dberr/err3').'</label><textarea>'.htmlspecialchars($_sql).'</textarea>');
			}else{
				kc_error('<label>'.$king->lang->get('system/dberr/err3').'</label>');
			}
		}
		$Row_Result[$i]=mysql_fetch_assoc($this->mQuery);
	}
	return $Row_Result;
}
/**
	返回记录集，单行操作
	@param string $_sql  SQL语句
	@return array
*/
public function getRows_one($_sql){
	$this->query($_sql);
	if(!$this->mQuery){
		//kc_error(kc_clew(__FILE__,__LINE__,$_sql));
		return False;
	}
	return mysql_fetch_array($this->mQuery);
}
/**
	getCount
	@param
	@return
*/
/**
	返回记录集中的记录行数
	@param string $table  数据库表名称
	@param string $where  查询条件
	@return int
*/
public function getRows_number($table=NULL,$where=NULL){

	if(isset($table{0})){
		global $king;
		$sql="select count(*) count from $table";
		if($where){
			$sql.=" where $where";
//			$s_sql=base64_encode($where);
		}else{
//			$s_sql='none';
		}

		$s_table=substr($table,3,strlen($table)-3);
//		$cachepath="system/getRows_number/{$s_table}/{$s_sql}";
		$cachepath="system/getRows_number/{$s_table}/".md5($sql);
		$rows=$king->cache->get($cachepath,time()-$king->config('cachetime'));
		if(!$rows){
			$res=$this->getRows_one($sql);
			$rows=$res['count'];
			$king->cache->put($cachepath,$rows);
		}
	}else{
		if($this->mQuery){
			$this->Rows=mysql_num_rows($this->mQuery);
		}else{
			$this->Rows=0;
		}
		$rows=$this->Rows;
	}

	return $rows;
}
/**
	返回字段个数
	@return int
*/
public function getFields_number(){
	$this->Fields=mysql_num_fields($this->mQuery);
	return $this->Fields;
}
/**
	分页代码
	@param string $_url   URL格式模板,如index.php?pid=%d&rn=%d,需要注意的是pid必须在rn的前面
	@param string $_per   记录总数
	@param string $_pid   当前页
	@param string $_rn    每页显示
	@param string $_inner 分页内容模板
	@return string
*/

public function pagelist($_url='',$_per='',$_pid=0,$_rn=0,$_inner=null){

	if($this->ispagelist=0){//如果不分页，则不显示分页代码
		return '';
	}
	if($this->mPagelist!='')
		return $this->mPagelist;

	if($_per==0)
		return '';

	if($_pid==0)
		$_pid=$this->pid;//第x页 即当前页
	if($_rn==0)
		$_rn=$this->rn;//每页显示

	$this->mPagelist=kc_pagelist($_url,$_per,$_pid,$_rn,$_inner);

	return $this->mPagelist;
}
/**
	获得下一个值
	@param string $_table 数据表名
	@param string $_where 条件语句
	@param string $_field 要获得下一个值的字段名称
*/
public function neworder($_table,$_where='',$_field='norder'){
	$_where
		? $_where=' where '.$_where
		: $_where='';
	$_res=$this->getRows_one("select max($_field) as c from {$_table} {$_where}");
	return $_res['c']+1;
}
/**
	上移下移数据
	@param string $_table    数据表名
	@param int    $id        索引ID的值
	@param string $_where    条件
	@param int    $_order    排序,1为倒序,0为正序
	@param string $_kidname  索引ID的字段名称
	@param string $_norder   决定排序的字段名称
*/
public function updown($_table,$id,$_where=null,$_order=1,$_kidname='kid',$_norder='norder'){

	$_back=$_SERVER['HTTP_REFERER'];

	$_array1=array('kid'=>0,'norder'=>0);
	$_array2=array('kid'=>0,'norder'=>0);

	//@param int    $_num      偏移量
	$_num=kc_get('NUMBER',2,1);
	//@param string $_act  [up|down]上移或下移
	$_act=kc_post('UPDOWN')=='up'?'up':'down';

	if($_order){
		$_act=='down'
			? $order='desc'
			: $order='asc';
	}else{
		$_act=='up'
			? $order='desc'
			: $order='asc';
	}


	if($_where!=null)
		$_where=' where '.$_where;

	$_sql="select $_kidname,$_norder from $_table $_where order by $_norder $order";

	$this->query($_sql);
	$this->getRows_number();

	if($_num==0)
		$_num=$this->Rows;

	$_table=sprintf($_table,KC_DB_PREFIX);

	for($i=0;$i<$this->Rows;$i++){

		if(!mysql_data_seek($this->mQuery,$i))
			kc_ajax('','',0,'parent.location=\''.$_back.'\'');

		$res=mysql_fetch_array($this->mQuery);

		if($id==$res[$_kidname]){

			$_array1['kid']=$res[$_kidname];
			$_array2['kid']=$res[$_norder];

			for($j=1;$j<=$_num;$j++){

				if(($i+$j)<$this->Rows){
					if(!mysql_data_seek($this->mQuery,$i+$j))
						kc_ajax('','',0,'parent.location=\''.$_back.'\'');

					$res=mysql_fetch_array($this->mQuery);

					$_array1['norder']=$res[$_kidname];
					$_array2['norder']=$res[$_norder];
					mysql_query("update {$_table} set {$_norder}={$_array2['norder']} where {$_kidname}={$_array1['kid']} limit 1;",$this->link);
					mysql_query("update {$_table} set {$_norder}={$_array2['kid']} where {$_kidname}={$_array1['norder']} limit 1;",$this->link);

					$_array2['kid']=$_array2['norder'];

				}
			}
			kc_ajax('','',0,'parent.location=\''.$_back.'\'');
		}
	}
		kc_ajax('','',0,'parent.location=\''.$_back.'\'');
}
/**
	返回数据库版本
*/
public function version(){
	$this->link==0 && $this->connect();//判断数据库连接是否可用
	return 'MySQL/'.mysql_get_server_info();
}
/**
	创建数据表
	@param string $tableName  数据表名称
	@param string $sql        SQL语句
	@param string $kid        递增字段名，若不建立则为空
	@param int    $auto_increment 自动递增字段的初始值
*/
public function createTable($tableName,$sql,$kid=null,$auto_increment=0){
	$kidSql=isset($kid{0}) ? "{$kid} int not null AUTO_INCREMENT primary key,":'';

	$autoSql=$auto_increment>0 ? " AUTO_INCREMENT={$auto_increment}" : '';

	$this->query("create table IF NOT EXISTS {$tableName} ({$kidSql}{$sql}) ENGINE=MyISAM  CHARSET=".KC_DB_CHARSET."{$autoSql};");
}
/**
	修改数据表
	@param
	@return
*/
public function alterTable($tableName,$sql,$is=0){
	$alteration = $is ? " RENAME TO " : "ADD" ;
	$this->query("ALTER TABLE $tableName $alteration ($sql) ;");
}
/**
	创建数据库
	@param
	@return
*/
public function createDB(){
	@mysql_query("CREATE DATABASE IF NOT EXISTS ".KC_DB_DATA." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;",@mysql_connect(KC_DB_HOST,KC_DB_USER,KC_DB_PASS));
}
/**
	安全字符转换
	@param string $s 输出的字符
	@return string
*/
public function escape($s){
	return addslashes($s);
}




} //!DB_CLASS

?>