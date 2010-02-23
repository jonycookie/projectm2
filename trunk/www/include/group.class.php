<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class group{
	var $group=array();
	var $all=array();
	function group($type=NULL){
		$this->__construct($type);
	}
	function __construct($type=NULL){
    	global $iCMS;
    	$this->db			=$iCMS->db;
    	
    	$type && $sql=" and `type`='$type'";
		$rs=$this->db->getArray("SELECT * FROM `#iCMS@__group` where 1=1{$sql} ORDER BY `order` , `gid` ASC",ARRAY_A);
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$this->all[$rs[$i]['gid']]=$this->group[$rs[$i]['type']][]=$rs[$i];
		}
	}
	function select($currentid=NULL,$type="u"){
		if($this->group[$type])foreach($this->group[$type] AS $G){
			$selected=($currentid==$G['gid'])?" selected='selected'":'';
			$option.="<option value='{$G['gid']}'{$selected}>".$G['name']."[GID:{$G['gid']}] </option>";
		}
		return $option;
	}
}
?>