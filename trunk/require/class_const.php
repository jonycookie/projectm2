<?php
defined('IN_CMS') or die('Forbidden');

class TplConst{
	var $type;
	var $db;

	function TplConst($type=null){
		$this->__construct($type);
	}

	function __construct($type=null){
		global $db;
		$this->db = $db;
		$this->type = $type ? $type : 'TPL';
	}

	function isHave($name){
		$name = Char_cv($name);
		$rt = $this->db->get_one("SELECT id FROM cms_const WHERE name='$name'");
		if($rt){
			return $rt['id'];
		}else{
			return false;
		}
	}

	function getConstByValue($value){
		$value = Char_cv($value);
		$rt = $this->db->get_one("SELECT * FROM cms_const WHERE value='$value' AND type='$this->type'");
		return $rt;
	}

	function getConstByName($name){
		$name = Char_cv($name);
		$rt = $this->db->get_one("SELECT * FROM cms_const WHERE name='$name' AND type='$this->type'");
		return $rt;
	}

	function getConstById($id){
		$id = intval($id);
		$rt = $this->db->get_one("SELECT * FROM cms_const WHERE id='$id' AND type='$this->type'");
		return $rt;
	}

	function getConstByType($type){
		$type = Char_cv($type);
		$const = array();
		$rs = $this->db->query("SELECT * FROM cms_const WHERE type='$type'");
		while($rt = $this->db->fetch_array($rs)){
			$const[] = $rt;
		}
		return $const;
	}

	function delConstByName($names){
		$name = Char_cv($name);
		$this->db->update("DELETE FROM cms_const WHERE name='$name' AND type='$this->type'");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('TplConst');
	}

	function delConstByValue($value){
		$value = Char_cv($value);
		$this->db->update("DELETE FROM cms_const WHERE value='$value' AND type='$this->type'");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('TplConst');
	}

	function delConstById($id){
		$id = intval($id);
		$this->db->update("DELETE FROM cms_const WHERE id='$id' AND type='$this->type'");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('TplConst');
	}

	function setConst(&$array){
		$array['title'] = Char_cv($array['title']);
		$array['name'] = Char_cv($array['name']);
		$array['value'] = Char_cv($array['value']);
		$array['type'] && $this->type = $array['type'];
		$array['id'] = intval($array['id']);
		if(!preg_match("/^[a-zA-Z0-9_]{3,}$/",$array['name'])){
			Showmsg('const_nameerror');
		}
		if(strpos($array['name'],$this->type)===false){
			$array['name'] = strtoupper($this->type).'_'.$array['name'];
		}
		$id = $this->isHave($array['name']);
		if(!$id || ($id && $array['id']==$id) ){
			$this->save($array);
		}else{
			Showmsg('const_nameexist');
		}
	}

	function save(&$array){
		if($array['id']){
			$sql = "UPDATE cms_const SET title= '$array[title]',name='$array[name]',value='$array[value]',type='$this->type' WHERE id='$array[id]'";
		}else{
			$sql = "INSERT INTO cms_const (title,name,value,type) VALUES ('$array[title]','$array[name]', '$array[value]', '$this->type')";
		}
		$this->db->update($sql);
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('TplConst');
	}
}
?>