<?php

class KC_sqlite_class{

//变量

public  $link;         //数据库连接
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
	@param string $_data  数据库路径
*/
public function connect($data=''){

	global $king;
	if(!isset($this->link)){
		$file=$data ? KC_ROOT.$data : KC_ROOT.KC_DB_SQLITE;

		if(!file_exists($file)){//若找不到文件则报错
			global $king;
			kc_error($king->lang->get('system/dberr/err6'));
		}

		try{
			$this->link=new PDO('sqlite:'.$file);
		}catch(PDOException $e){
			exit('error!');
		}

	}

	return $this->link;
}
/**
	查询
	@param string $sql  SQL代码
	@param int    $is    是否忽略错误;1忽略
*/
public function query($sql,$is=0){
	kc_runtime('DataQuery');//查询计时开始

	$num=stripos($sql,'where');
	if(isset($num{0})){
		$sql_left=substr($sql,0,$num);
		$sql_right=substr($sql,$num);
		$sql=str_replace(array('%s','%a'),array(KC_DB_PRE,KC_DB_ADMIN),$sql_left).$sql_right;
	}else{
		$sql=str_replace(array('%s','%a'),array(KC_DB_PRE,KC_DB_ADMIN),$sql);
	}
	if(!isset($this->link))
		$this->connect();//判断数据库连接是否可用

	$this->mQuery = $this->link->query($sql);

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
	$_table=str_replace(array('%s','%a'),array(KC_DB_PRE,KC_DB_ADMIN),$_table);
	foreach($_array as $_key=>$_val){
		array_push($_fields,$_key);
		array_push($_values,$this->escape($_val));
	}
//	kc_error(print_r($_values,1));
	$sql='insert into '.$_table.' ('.implode(',',$_fields).') values (\''.implode('\',\'',$_values).'\');';
//	kc_error($sql);
	$this->query($sql);

	$newid=$this->link->lastInsertId();//$this->link->lastInsertRowid();

	return $newid;
}
/**
	更新数据
	@param string $_table 数据表
	@param array  $_array 要更新的数据
	@param string $_where 条件语句
*/
public function update($table,$array,$where=null){
	$fields=array();
	$values=array();
	$table=str_replace(array('%s','%a'),array(KC_DB_PRE,KC_DB_ADMIN),$table);
	foreach($array as $key=>$val){

		$values[]=preg_match("/^\[\[.+\]\]$/",$val)//[[hit=hit+1]]
			? $key.'='.substr($val,2,-2)
			: $key.'=\''.$this->escape($val).'\'';
	}

	$sql='update '.$table.' set '.implode(',',$values).(isset($where{0}) ? ' where '.$where.';' : ';');
	$this->query($sql);
}
/**
	返回记录集
	@param string $sql  SQL语句
	@param int    $_is   是否带有分页1分页
	@param int    $_pid  页数
	@param int    $_rn   每页显示数
	@return array
*/
public function getRows($sql,$_is=0,$_pid=0,$_rn=0){
	if($_is){
		if($_pid==0)
			$_pid=$this->pid;	 //第x页 即当前页
		if($_rn==0)
			$_rn=$this->rn;		 //每页显示

		$sql.=' limit '.($_rn*($_pid-1)).','.$_rn.';';
		$this->ispagelist=1;
	}else{
		$this->ispagelist=0;
	}

	$Row_Result=array();
	$this->query($sql);

//	$num=$this->mQuery->rowCount();

//	echo "<p>$sql - $num</p>";
	if(is_object($this->mQuery)){
		$array=$this->mQuery->fetchAll();

	}else{
		$array=array();
//		kc_error($sql);
	}

	return $array;
/*
	$this->getRows_number();
	for($i=0;$i<$this->Rows;$i++){
		if(!sqlite_seek($this->mQuery,$i)){

			global $king;
			if(KC_CONFIG_DEBUG){
				kc_error($king->lang->get('system/dberr/err3').htmlspecialchars($sql));
			}else{
				kc_error($king->lang->get('system/dberr/err3'));
			}
		}
		$Row_Result[$i]=sqlite_fetch_array($this->mQuery);
	}
*/

//	return $Row_Result;
}
/**
	返回记录集，单行操作
	@param string $sql  SQL语句
	@return array
*/
public function getRows_one($sql){
	$this->query($sql);
	if(empty($this->mQuery)){
		return False;
	}
	$rs=$this->mQuery->fetch();
//	kc_error('<pre>'.print_r($rs,1));
	return $rs;
}
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
			$this->Rows=count($this->mQuery);
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
	$this->Fields=count($this->mQuery);
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

	这个函数可以进一步优化，当置顶或垫底的时候，直接获取最大值+1或最小值-1的方法来更新
	这样做就不用遍历很多数据，也可以避免数据过多的时候，超时的问题。
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

	$sql="select $_kidname,$_norder from $_table $_where order by $_norder $order";

	$this->query($sql);
	$this->getRows_number();

	if($_num==0)
		$_num=$this->Rows;

	$_table=sprintf($_table,KC_DB_PRE);

	$array=is_object($this->mQuery) ? $this->mQuery->fetchAll() : $array();

	$count=count($array);
	for($i=0;$i<$count;$i++){
		if($id==$array[$i][$_kidname]){

			$_array1['kid']=$array[$i][$_kidname];
			$_array2['kid']=$array[$i][$_norder];

			for($j=1;$j<=$_num;$j++){

				if(($i+$j)<$count){

					$_array1['norder']=$array[$i+$j][$_kidname];
					$_array2['norder']=$array[$i+$j][$_norder];
					$this->link->query("update {$_table} set {$_norder}={$_array2['norder']} where {$_kidname}={$_array1['kid']}");
					$this->link->query("update {$_table} set {$_norder}={$_array2['kid']} where {$_kidname}={$_array1['norder']}");

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
	if(!isset($this->link)) $this->connect();
	return 'SQLite/'.$this->link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
}
/**
	创建数据表
	@param string $tableName  数据表名称
	@param string $sql        SQL语句
	@param string $kid        递增字段名，若不建立则为空
	!!!@param int    $auto_increment 自动递增字段的初始值,sqlite下没法弄，干脆删掉
*/
public function createTable($tableName,$sql,$kid=null){//,$auto_increment=0

	//当已经存在要查询的表的时候，直接返回
//	if($this->getRows_one("SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'"))
//		return False;

	$kidSql=isset($kid{0}) ? "{$kid} INTEGER PRIMARY KEY,":'';//AUTO_INCREMENT

	$sql=$this->formatSql($sql);

	$this->query("CREATE TABLE IF NOT EXISTS {$tableName} ({$kidSql}{$sql});");

}
/**
	修改数据表
	@param string $tableName  表名
	@param string $sql
	@param int    $is         0 ADD 1 RENAME TO
	@return
*/
public function alterTable($tableName,$sql,$is=0){
	$sql=$this->formatSql($sql);
	$alteration = $is ? " RENAME TO " : "ADD" ;
	$this->query("ALTER TABLE $tableName $alteration $sql ;");
}
/**
	把mysql结构的sql代码格式化输出为sqlite可支持的版本
	@param
	@return
*/
private function formatSql($sql){
	preg_match_all("/(\w+)\s+([a-zA-Z]+)(\(\d+\))?[^\,]*/"
	,$sql,$array,PREG_PATTERN_ORDER  );

	if(is_array($array)){
		$sql=implode(',',$array[0]);
	}else{
		$sql='';
	}
	return $sql;
}
/**
	创建数据库
*/
public function createDB($file=null){

	$file= $file==null ? KC_ROOT.KC_DB_SQLITE : KC_ROOT.$file;

	try{
		$this->link=new PDO('sqlite:'.$file);
	}catch(PDOException $e){
		exit('error!');
	}

}
/**
	安全字符转换
	@param string $s 输出的字符
	@return string
*/
public function escape($s){
//	if(!isset($this->link)) $this->connect();//判断数据库连接是否可用
	return str_replace("'","''",$s);
	//return  sqlite_escape_string($s);
}




} //!DB_CLASS

?>