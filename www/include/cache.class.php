<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class cache {
	var $_cache_sock;
	var $_have_zlib;
	var $_compress_enable;
	var $_dirs;
	var $_file;
	function __construct($args){
//		$this->set_dirs($args['dirs']);
		$this->_dirs=$args['dirs'];
		$this->_dir_level = empty($args['level']) ? -1 : floor(32/$args['level']);
		$this->_compress_enable = $args['compress'];
		$this->_have_zlib = function_exists("gzcompress");

		$this->_cache_sock = array();
	}
	//PHP4 Class
	function cache($args){
		$this->__construct($args);
	}
	function add ($key, $val, $exp = 0){
		$this->_file=$this->get_file($key,'add');
		$value=array(
			"Time"=>time(),
			"Expires"=>$exp,
			"Data"=>$val,
		);
		$data=serialize($value);
		$this->_cache_sock='<?php exit;?>';
		if ($this->_have_zlib && $this->_compress_enable){
			$this->_cache_sock.=gzcompress($data, 9);
		}else{
			$this->_cache_sock.=$data;
		}
		return writefile($this->_file,$this->_cache_sock);
	}
	function get ($key){
		$this->_file=$this->get_file($key,'get');
		if(!file_exists($this->_file)) return false;
		$D=openfile($this->_file);
		$D=str_replace('<?php exit;?>','',$D);
		$value=unserialize(($this->_have_zlib && $this->_compress_enable)?gzuncompress($D):$D);
		if($value['Expires']==0){
			return $value['Data'];
		}else{
			$_time=time();
			return ($_time-$value['Time']<$value['Expires'])?$value['Data']:false;
		}
	}
	function get_multi ($keys){
		foreach ($keys as $key){
			$value[$key]=$this->get ($key);
		}
		return $value;
	}
	function replace ($key, $value, $exp=0){}
	function delete ($key='', $time = 0){}
   	function get_file($key,$method){
		$dirPath=$this->_dirs.'/'.(strpos($key,'/')!==false?dirname($key):'');
   		if($this->_dir_level!=-1){
	   		$a=str_split(md5($key),$this->_dir_level);
	   		$dirPath.='/'.implode('/',$a).'/';
		}
		if (!file_exists($dirPath) && $method=='add'){
			createdir($dirPath);
		}
		$strrchr=strrchr($key,'/');
		$strrchr!==false && $key=$strrchr;
		return $dirPath.$key.'.php';
   	}
}
if (!function_exists("str_split")) {
  function str_split($str,$length = 1) {
    if ($length < 1) return false;
    $strlen = strlen($str);
    $ret = array();
    for ($i = 0; $i < $strlen; $i += $length) {
     $ret[] = substr($str,$i,$length);
    }
    return $ret;
  }
}
//$c = new cache(array(
//				'dirs'=>"cache_dir_1",
//				'level'=>"1",
//		));
//$c->add("test",array(1,2,3,4,5,6),1000);
//$c->add("asd",array(1,2,3,4,5,6),10);
//$c->add("123123",array(1,2,3,4,5,6),1);
//$rs[]=$c->get("test");
//$rs[]=$c->get("asd");
//$rs[]=$c->get("123123");
//$rs2=$c->get_multi(array("test","asd","123123"));
//var_dump($rs);
//var_dump($rs2);
?>