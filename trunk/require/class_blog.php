<?php
!defined('IN_CMS') && die('Forbidden');
/**
 * Blog整合模型
 *
 */
class Blog{
	/**
	 * 基本blog整合配置
	 *
	 * @var array
	 */
	var $config = array();

	/**
	 * blog内容浏览方式，CMS内浏览还是blog处浏览
	 *
	 * @var string
	 */
	var $viewtype;

	/**
	 * MySQL数据库操作对象
	 *
	 * @var object
	 */
	var $mysql;

	var $sqladd;

	var $order;

	var $onlyimg;
	var $totalQuery;

	/**
	 * 所在的CMS板块
	 *
	 * @var object
	 */
	var $cid;
	/**
	 * 构造函数
	 *
	 */
	function __construct(){
		global $sys;
		if(!$sys['aggreblog']) return ;
		$this->config['dbname']		= $sys['blog_dbname'];
		$this->config['url']		= $sys['blog_url'];
		$this->config['type']		= $sys['blog_type'];
		$this->config['dbpre']		= $sys['blog_dbpre'];
		$this->config['attachdir']	= $sys['blog_attachdir'];
		$this->config['imgdir']		= $sys['blog_imgdir'];
		$this->config['charset']	= $sys['blog_charset'];
		if(eregi('^(http://)',$this->config['attachdir'])){
			$this->config['attachurl'] = $this->config['attachdir'].'/';
		}else{
			$this->config['attachurl'] = $this->config['url'].'/'.$this->config['attachdir'].'/';
		}
		$this->mysql = new DB($sys['blog_dbhost'],$sys['blog_dbuser'],$sys['blog_dbpw'],$sys['blog_dbname'],$sys['blog_charset'],0); //另外一个DB类的实例
		//$this->setTableName();
	}

	function blog(){ //PHP4
		$this->__construct();
	}

	function readConfig($condition){ //传入查询条件
		global $timestamp;
		$condition = unserialize($condition);
		foreach ($condition as $key=>$value){
			//if(!$this->getField($key)) continue;
			if($key=='viewtype') continue; 
			if($key=='fid'){
				if(empty($value)) continue;
				$key	= 'cid';
				$value	= explode(',',$value);
				$this->add($key,$value);
			}elseif ($key=='taxis'){
				if($value)
					$this->order='postdate';
				else
					$this->order='lastpost';
			}elseif ($key=='postdate' || $key=='lastpost'){
				if(empty($value)) continue;
				$value=$timestamp-$value*60*60*24;
				$this->add($key,$value);
			}else{
				if(empty($value)) continue;
				$this->add($key,$value);
			}
		}
	}

	/**
	 * 此方法来设置sql查询语句，根据一个字段以及相应的值来设置查询条件
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	function add($var,$value){
		//$and = empty($this->sqladd) ? 'WHERE' : 'AND';
		//$field = $this->getField($var);
		if(is_array($value)){
			$value=implode(',',$value);
			$this->sqladd.=" AND i.$var IN ($value)";
		}elseif(is_string($value) && !is_numeric($value)) {
			$this->sqladd.=" AND i.$var='$value'";
		}else{
			$this->sqladd.=" AND i.$var>='$value'";
		}
	}

	/**
	 * 读取数据库查询缓存
	 *
	 * @param string $filename
	 * @param var $cacheTime
	 * @return array
	 */
	function readcache($filename,$cacheTime){
		if(!$GLOBALS['sys']['sqlcache']){
			return false;
		}
		if($GLOBALS['sys']['querycache']){
			return false;
		}else{
			$cacheTime = $cacheTime ? intval($cacheTime)*60 : intval($GLOBALS['sys']['sqlcache'])*60;
			if(file_exists($filename) && $GLOBALS['timestamp'] - filemtime($filename) < $cacheTime){
				$str = readover($filename);
				$str = unserialize($str);
				return $str;
			}
		}
		return false;
	}

	/**
	 * 写入数据库查询缓存
	 *
	 * @param string $filename
	 * @param string $info
	 * @return array
	 */
	function writecache($filename,$info){
		if(!$GLOBALS['sys']['sqlcache']){
			return false;
		}
		if($GLOBALS['sys']['querycache']){
			return false;
		}else{
			$filename = Pcv(D_P."data/sql/$filename.cache");
			return writeover($filename,$info);
		}
	}

}
?>