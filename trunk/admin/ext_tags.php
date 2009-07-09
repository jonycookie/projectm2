<?php
defined('IN_ADMIN') or die('Forbidden');
$action = GetGP('action');
$tag = new Tags();
$tag->doIt($action);

class Tags{
	function doIt($action){
		if(!$action){
			$this->ShowTag();
		}elseif ($action=='del'){
			$this->DelTag();
		}elseif ($action=='add'){
			$this->AddTag();
		}
	}

	function AddTag(){
		global $db;
		$tagname = GetGP('tagname');
		$tagname = Char_cv(trim($tagname));
		$tagname = str_replace(',','',$tagname);
		!$tagname && Showmsg('ext_notagname');
		$rt = $db->get_one("SELECT tagid FROM cms_tags WHERE tagname='$tagname'");
		if(!$rt){
			$db->update("INSERT INTO cms_tags SET tagname='$tagname',num=0");
			require_once(R_P.'require/class_cache.php');
			Cache::writeCache('tags');
			adminmsg('ext_tagaddok');
		}else{
			adminmsg('ext_tagexist');
		}
	}

	function DelTag(){
		global $db;
		$tagid = GetGP('tagid');
		if(is_array($tagid)){
			$tagid = checkselid($tagid);
		}else{
			$tagid = intval($tagid);
		}
		$db->update("DELETE FROM cms_tags WHERE tagid IN($tagid)");
		$db->update("DELETE FROM cms_contenttag WHERE tagid IN($tagid)");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('tags');
		adminmsg('operate_success');
	}

	function ShowTag(){
		global $db,$basename;
		$keyword = Char_cv(GetGP('keyword'));
		$page = GetGP('page');
		$sqladd = $keyword ? " WHERE tagname='$keyword' " : "";
		$rs = $db->get_one("SELECT COUNT(*) AS total FROM cms_tags $sqladd");
		$total = $rs['total'];
		if(!$page or !is_numeric($page) or $page<=0) $page = 1;
		$displaynum = 30;
		$start = (intval($page)-1)*$displaynum;
		$numofpage = ceil($total/$displaynum);
		$pages = numofpage($total,$page,$numofpage,"$basename&keyword=$keyword&");
		$tagdb = $db->query("SELECT * FROM cms_tags $sqladd ORDER BY tagid DESC LIMIT $start,$displaynum");
		require PrintEot('header');
		require PrintEot('ext_tags');
		adminbottom();
	}
}
?>