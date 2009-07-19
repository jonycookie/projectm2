<?php
!defined('IN_ADMIN') && die('Forbidden');

require_once(D_P.'data/cache/cate.php');
require_once(R_P.'require/class_action.php');
require_once(R_P.'require/class_cache.php');
require_once(R_P.'require/chinese.php');
if($admin_name!=$manager && $admindb['privcate'] && $cid && !in_array($cid,$admindb['privcate'])){
	Showmsg('privilege');
}
$action = GetGP('action');
$showcontent = new ShowContent();
$showcontent->doIt($action);
/**
 * 处理栏目的内容在后台的展示
 *
 */
class ShowContent{

	var $catedb;
	var $moduledb;

	function __construct(){
		global $catedb,$moduledb;
		$this->catedb = $catedb;
		$this->moduledb = $moduledb;
	}

	function ShowContent(){
		$this->__construct();
	}

	function doIt($action){
		!$action && $action='show';
		switch ($action) {
			case 'show':
				$this->show();
				break;
			case 'view':
				$this->viewContent();
				break;
			case 'digest':
				$this->digest();
				break;
			case 'tags':
				$this->tags();
				break;
			default:
				$this->action();
				break;
		}
	}

	function show(){
		global $action,$db,$basename;
		$up = (int)GetGP('up');
		$sqladd = '';
		if (!$up){
			$cname = 'ROOT';
			$sqladd = " WHERE up='0' ";
		}else{
			$sqladd = " WHERE up='$up' ";
			$cname = $this->catedb[$up]['cname'];
		}
		$rs = $db->query("SELECT * FROM cms_category $sqladd ORDER BY taxis DESC");
		$category = array();
		while ($ct = $db->fetch_array($rs)){
			$ct['module'] = $this->moduledb[$ct['mid']]['mname'];
			$category[] = $ct;
		}
		require PrintEot('content');
		adminbottom();
	}

	function viewContent(){
		global $sys,$db,$action,$basename,$cid;
		extract(Init_GP(array('displaynum','keyword','displaytype','page','order','orderby')));
		if($this->catedb[$cid]['mid']=='-2' && (!$sys['aggrebbs'] || !$sys['bbs_dbname'])) Showmsg('mod_needaggrebbs');
		if($this->catedb[$cid]['mid']=='-1' && (!$sys['aggreblog'] || !$sys['blog_dbname'])) Showmsg('mod_needaggreblog');
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select = $cate->treeByMid($this->catedb[$cid]['mid']);
		extract($db->get_one("SELECT * FROM cms_category WHERE cid='$cid'"));
		if(!$cname) Showmsg('pub_cateerror');
		$mid == 0 && Showmsg('pub_nocontent');

		if(!$displaynum){
			$displaynum = 30;
		}else {
			$numadd = "displaynum=$displaynum";
		}
		if($displaytype==1){
			$where = "where:ifpub=1";
			$pubadd = "displaytype=1";
		}elseif ($displaytype==2){
			$where = "where:ifpub=0";
			$pubadd = "displaytype=2";
		}
		if($keyword){
			$keyword = Char_cv($keyword);
			$keywordadd = "keyword=$keyword";
			$where = $where ? $where." AND title LIKE('%$keyword%')" : "WHERE:title LIKE('%$keyword%')";
		}
		!$orderby && $orderby = 'ASC';
		!$order && $order = 'ifpub';
		if(in_array($order,array('tid','title','ifpub','digest','hits','comnum','publisher'))){
			$orderparam = "order:$order $orderby,postdate DESC";
		}elseif($order=='postdate'){
			$orderparam = "order:postdate $orderby";
		}else{
			$orderparam = "order:ifpub,postdate DESC";
		}
		$orderimg[$order] = "<img src=\"images/admin/order_$orderby.gif\" />";
		if($orderby == 'DESC'){
			$orderby1 = 'ASC';
		}else{
			$orderby1 = 'DESC';
		}
		if(!is_numeric($page) || $page<=0) $page=1;
		$start = ($page-1)*$displaynum;
		$cururl = "$basename&action=view&cid=$cid&$numadd&$pubadd&$keywordadd";
		require_once(R_P.'require/class_cms.php');
		$cms = new Cms();
		$cms->pageurl = "$cururl&order=$order&orderby=$orderby&";
		$content = $cms->thread("cid:$cid;num:page-$displaynum;mid:$mid;$orderparam;$where");
		if($mid>0) $this->showDigest($content);
		$hottag = $this->hottag();
		${'displaytype_'.$displaytype} = " selected ";
		require PrintEot('content');
		adminbottom();
	}

	function digest(){ //设定精华
		global $tid,$cid,$mid,$db;
		$digestnum = (int)GetGP('digestnum');
		if($db->update("UPDATE cms_contentindex SET digest='$digestnum' WHERE tid='$tid'")){
			exit("100");
		}else{
			exit("200");
		}
	}

	function showDigest(&$array){ //展示精华推荐效果
		foreach ($array as $key=>$val){
			$img = "<div id='d_{$key}' oncontextmenu=\"digest('".$val['tid']."','".$key."','0');return false;\"><div onmouseout=\"reSet('$key','".$array[$key]['digest']."');\">";
			for($i=1;$i<=3;$i++){
				$imgname = $i<=$array[$key]['digest'] ? 'ok' : 'no';
				$img.="<img id='img{$key}_{$i}' class='st' src='images/admin/star_$imgname.gif' onmouseover=\"showStar('$key','$i');\" onclick=\"digest('".$val['tid']."','".$key."','".$i."');\" />";
			}
			/*
			if($array[$key]['digest']){
				$array[$key]['digest_c']="<img class='st' src=images/admin/star_c.gif onclick=\"digest('".$val['tid']."','".$key."','0');\" title='取消精华' />";
			}
			*/
			$img.="</div></div>";
			$array[$key]['digest'] = $img;
		}
	}

	function action(){
		global $cid,$tid,$basename,$sys,$sqlcachefile;
		extract(Init_GP(array('tocid','tids','job')));
		if(!$tids && $tid) $tids = $tid;
		if($job=='batchtag'){
			global $tagsid;
			$tagsid = $this->tags();
		}
		$action = new Action($job);
		$action->cate($cid);
		$action->target($tocid);
		$action->doIt($tids);
		Cache::writeCache('cate');
		if($sqlcachefile){
			foreach($sqlcachefile as $val) {
				if(strpos($val,'.cache') && !strpos($val,'..')){
					P_unlink($val);
				}
			}
			unset($sqlcachefile);
		}
		adminmsg('operate_success',"$basename&action=view&cid=$cid");
	}

	function tags(){
		global $db;
		$tags = GetGP('tags');
		$tags = Char_cv($tags);
		$tags = explode(',',$tags);
		array_splice($tags,5);
		$tagid = array();
		foreach($tags as $tag){
			$tag = trim($tag);
			if(!$tag){
				continue;
			}
			$rs = $db->get_one("SELECT tagid FROM cms_tags WHERE tagname='$tag'");
			if($rs){
				$tagid[] = $rs['tagid'];
			}else{
				$db->update("INSERT INTO cms_tags SET tagname='$tag',num=0");
				$tagid[] = $db->insert_id();
			}
		}
		return $tagid;
	}

	function hottag(){
		include(D_P.'data/cache/tagscache.php');
		$tmpText = "";
		foreach($hottags as $tag){
			$tmpText .= addslashes($tag['tagname']).',';
		}
		return $tmpText;
	}
}
?>