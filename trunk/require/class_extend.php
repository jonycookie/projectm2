<?php
/**
 * This software is the proprietary information of IM2.com.
 * $Id: class_extend.php 494 2008-01-30 07:07:19Z vcteam1 $
 * @Copyright (c) 2003-08 IM2.com Corporation.
 */
defined('IN_CMS') or die('Forbidden');
global $ext_config;
require_once(D_P.'data/cache/ext_config.php');
class Extend {
	function notice($num1,$num2=''){
		global $db,$sys,$ext_config;
		if(!$ext_config['notice']['ifopen']){
			return array();
		}
		if(!$num2){
			$start = 0;
			$end = $num1 ? intval($num1) : 10;
		}else{
			$start = $num1 ? intval($num1)-1 : 0;
			$end = intval($num2);
		}
		$url = extend::URL('notice');
		$rs = $db->query("SELECT * FROM cms_notice ORDER BY postdate DESC LIMIT $start,$end");
		$noticedb = array();
		while ($notice = $db->fetch_array($rs)){
			$notice['url'] = $url.'#'.$notice['nid'];
			$noticedb[] = $notice;
		}
		return $noticedb;
	}

	function URL($ename){
		global $sys,$ext_config;
		if($ext_config[$ename]){
			return $sys['url']."/extensions.php?E_name=".urlencode($ename);
		}else{
			return $sys['url'];
		}
	}

	function poll($id){
		global $db,$timestamp,$ext_config;
		if(!$ext_config['poll']['ifopen']){
			return array();
		}
		$sql = " stime<='$timestamp' AND (stime=etime or etime>='$timestamp') ";
		if(is_numeric($id)){
			$id = intval($id);
			$sql .= " AND id='$id' ";
		}elseif(is_string($id)){
			$id = Char_cv($id);
			$sql .= " AND mark='$id' ";
		}else{
			return array();
		}

		$rt = $db->get_one("SELECT * FROM cms_polls WHERE $sql ORDER BY stime DESC");
		if(!$rt){
			return array();
		}
		$rt['options'] = unserialize($rt['options']);
		$rt['etime'] = get_date($rt['etime'],'Y-m-j');
		$rt['stime'] = get_date($rt['stime'],'Y-m-j');
		return $rt;
	}
}
?>