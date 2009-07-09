<?php
!defined('IN_CMS') && die('Forbidden');
/**
 *  BBS调用模型
 */
class BBS{
	/**
	 * 基本BBS整合配置
	 *
	 * @var array
	 */
	var $config = array();

	/**
	 * 某版面的具体调用配置
	 *
	 * @var array
	 */
	var $condition;

	/**
	 * MySQL数据库操作对象
	 *
	 * @var object
	 */
	var $mysql;

	/**
	 * BBS内容浏览方式，CMS内浏览还是BBS处浏览
	 *
	 * @var string
	 */
	var $viewtype;

	/**
	 * BBS的帖子内容存储的表
	 *
	 * @var string
	 */
	var $table;

	/**
	 * 数据库查询的附加条件
	 *
	 * @var string
	 */
	var $sqladd;

	/**
	 * 排序方法
	 *
	 * @var string
	 */
	var $order;

	/**
	 * 是否只读取有图片附件类的帖子
	 *
	 * @var boolean
	 */
	var $onlyimg;

	/**
	 * 统计的查询语句
	 *
	 * @var string
	 */
	var $totalQuery;

	/**
	 * 要排除读取的板块id，一个数据库查询条件
	 *
	 * @var string
	 */
	var $notFid;
	var $cid;
	var $fields;
	/**
	 * 构造函数 PHP5
	 *
	 */
	function __construct(){
		global $very;
		if(!$very['aggrebbs']) return ;
		$this->config['dbname']		= $very['bbs_dbname'];
		$this->config['url']		= $very['bbs_url'];
		$this->config['type']		= $very['bbs_type'];
		$this->config['dbpre']		= $very['bbs_dbpre'];
		$this->config['attachdir'] 	= $very['bbs_attachdir'];
		$this->config['picpath'] 	= $very['bbs_picpath'];
		$this->config['charset']	= $very['bbs_charset'];
		$this->config['htmifopen']	= $very['bbs_htmifopen'];
		$this->config['htmdir']		= $very['bbs_htmdir'];
		$this->config['htmext']		= $very['bbs_htmext'];

		if(eregi('^(http://)',$this->config['attachdir'])){
			$this->config['attachurl'] = $this->config['attachdir'].'/';
		}elseif(is_dir($this->config['attachdir']) && strpos($this->config['attachdir'],'/')){
			//也可以指定一个本地服务器上的绝对路径
			$this->config['attachurl'] = $this->config['attachdir'].'/';
		}else{
			$this->config['attachurl'] = $this->config['url'].'/'.$this->config['attachdir'].'/';
		}
		//另外一个DB类的实例
		$this->mysql = new DB($very['bbs_dbhost'],$very['bbs_dbuser'],$very['bbs_dbpw'],$very['bbs_dbname'],$very['bbs_charset'],0);
		$this->setTableName();
	}

	function BBS(){ //PHP4
		$this->__construct();
	}

	/**
	 * 读取数据库查询缓存
	 *
	 * @param string $filename
	 * @param var $cacheTime
	 * @return array
	 */
	function readcache($filename,$cacheTime){
		if(!$GLOBALS['very']['sqlcache']){
			return false;
		}
		if($GLOBALS['very']['querycache']){
			return false;
		}else{
			$cacheTime = $cacheTime ? intval($cacheTime)*60 : intval($GLOBALS['very']['sqlcache'])*60;
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
		if(!$GLOBALS['very']['sqlcache']){
			return false;
		}
		if($GLOBALS['very']['querycache']){
			return false;
		}else{
			$filename = Pcv(D_P."data/sql/$filename.cache");
			return writeover($filename,$info);
		}
	}

	/**
	 * 如下方法供栏目方式整合论坛调用
	 *
	 * @param mixed $condition 整合的设置条件
	 */
	function readConfig($condition){ //传入查询条件
		global $timestamp;
		!is_array($condition) && $condition = unserialize($condition);
		$this->condition = $condition;
		$this->sqladd = '';
		if (empty($condition['fid'])) {
			$this->fidCheck() && $this->sqladd = "WHERE ".$this->fidCheck();
			//如果默认没有设置调用版块，那么就要把隐藏版块给去除
		}
		foreach ($condition as $key=>$value){
			if(!$this->getField($key)) continue;
			if($key=='fid'){
				if(empty($value)) continue;
				if(strpos($value,',')){
					$value = explode(',',$value);
					$this->add($key,$value);
				}else{
					$this->add($key,$value,'=');
				}
				
			}elseif ($key=='taxis'){
				$postdate = $this->getField('postdate');
				$lastpost = $this->getField('lastpost');
				if($value){
					$this->order = $postdate;
				}else{
					$this->order = $lastpost;
				}
			}elseif ($key=='postdate' || $key=='lastpost'){
				if(empty($value)) continue;
				$value = $timestamp-$value*60*60*24;
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
	function add($var,$value,$symbol=''){
		$s = array('=','>=','<=','<>','>','<');
		$symbol = in_array($symbol,$s) ? $symbol : '>=';
		$this->sqladd .= empty($this->sqladd) ? ' WHERE ' : ' AND ';
		$field = $this->getField($var);
		if($field==false) return ;
		if(is_array($value)){
			$value=implode(',',$value);
			$this->sqladd .= " t.$field IN ($value) ";
		}else{
			$this->sqladd .= " t.$field$symbol'$value' ";
		}
	}


//	function __destruct()
//	{
//		$this->mysql->close();
//	}
}

?>