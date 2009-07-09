<?php
defined('IN_CMS') or die('Forbidden');
require_once(D_P.'data/cache/cate.php');
class Comment{
	var $mid;
	var $tid;
	var $cid;
	var $title;
	var $total;
	/**
	 * 一次显示的评论条目
	 *
	 * @var integer
	 */
	var $numSize=10;
	/**
	 * 当前页次
	 *
	 * @var integer
	 */
	var $pageNo;
	var $facedb;
	var $page;
	var $time;

	function __construct(){
		global $tid,$cid,$mid,$shownum,$job,$page;
		$this->tid		= intval($tid);
		$this->mid		= intval($mid);
		$this->cid		= intval($cid);
		$this->pageNo	= intval($page);
		$this->numSize	= $shownum?intval($shownum):$this->numSize;
		$this->pageNo<1 && $this->pageNo=1;
	}

	function Comment(){
		$this->__construct();
	}
	/**
	 * 显示评论信息内容
	 * @param integer page 当前显示的页数
	 */
	function main(){
		global $db,$very,$timestamp,$catedb;
		$rt		= $db->get_one("SELECT comnum,title,url FROM cms_contentindex WHERE tid='$this->tid'");
		$cid	= $this->cid;
		$mid	= $this->mid;
		$tid	= $this->tid;
		$title	= $rt['title'];
		$total	= $rt['comnum'];
		$this->total  = $total;
		if($catedb[$cid]['htmlpub']){
			$titleurl = $very['url']."/".$very['htmdir']."/".$rt['url'];
		}else{
			$titleurl = $very['url']."/view.php?tid=".$tid."&cid=".$cid;
		}
		$this->title  = array('title'=>$title,'url'=>$titleurl);
		$start = ($this->pageNo-1)*$this->numSize;
		$page  = ($start/$this->numSize)+1;
		$numofpage = ceil($total/$this->numSize);
		$url   = 'comment.php?cid='.$cid.'&mid='.$mid.'&tid='.$tid.'&';
		$pages = numofpage($total,$page,$numofpage,$url);
		$this->page = $pages;
	}

	/**
	 * AJAX显示评论信息内容
	 */
	function getcomment(){
		global $db,$very;
		$rt = $db->get_one("SELECT comnum FROM cms_contentindex WHERE tid='$this->tid'");
		$total	= $rt['comnum'];
		require_once(R_P.'require/ajax_page.php');
		require(D_P.'data/cache/face.php');
		$this->facedb = $facedb;
		$query	= "comment.php?job=getcomment&tid=$this->tid&mid=$this->mid&cid=$this->cid&shownum=$this->numSize&page";
		$pager	= new pager($total,$this->numSize,$query);
		$pages	= $pager->getPageSet();

		$result	= $this->getsinglecomment();
		start('utf-8');
		require Template('comment_ajax');
		$content = ob_get_contents();
		ob_end_clean();
		start('utf-8');
		$content = $this->toutf8($content);
		$content = str_replace(array("::wind::"),array("<br />"),$content);
		exit($content);
	}

	/**
	 * 显示评论框
	 */
	function showpost() {
		global $very,$facedb;
		$cid	= $this->cid;
		$mid	= $this->mid;
		$tid	= $this->tid;
		$type	= "showpost";
		start('utf-8');
		require Template('comment_ajax');
		$content = ob_get_contents();
		ob_end_clean();
		start('utf-8');
		$content = $this->toutf8($content);
		$content = str_replace(array("::wind::"),array("<br />"),$content);
		exit($content);
	}

	function pub(){
		echo "400"; //为将来登录做准备来返回一个登录状态
		exit();
	}

	function addMsg(){
		global $db,$timestamp,$onlineip,$very,$ck,$c_author,$c_message,$hideip,$ajax,$shownum;
		if($very['ckcomment']){
			$ck = strtolower($ck);
			GdConfirm($ck);
		}
		$hideip = intval($hideip);
		$author = $this->toGbk(Char_cv($c_author));
		$message= $this->toGbk(Char_cv($c_message));
		empty($c_message) && die('0');
		if(empty($_COOKIE) || $timestamp-GetCookie('comment')<10) die('wait'); //10秒内只允许评论一次
		Cookie('comment',$timestamp);
		require_once(D_P.'data/cache/cate_'.$this->cid.'.php');
		if(!$cateinfo['comment']){
			throwError('commentnotallow');
		}
		$db->update("INSERT INTO cms_comment SET tid='$this->tid',cid='$this->cid',mid='$this->mid',author='$author',message='$message',postdate='$timestamp',fromip='$onlineip',hideip='$hideip'");
		if($this->mid >0){
			$db->update("UPDATE cms_contentindex SET comnum=comnum+1 WHERE cid='$this->cid' AND tid='$this->tid'");
		}
		if($ajax==1) {
			$this->getcomment();
		}else {
			echo "success";
			exit;
			//ObHeader("comment.php?job=main&cid=".$this->cid."&mid=".$this->mid."&tid=".$this->tid);
		}
	}

	/*
	*AJAX获取热门评论
	*/
	function gethotcomment() {
		$num	 = $this->numSize ? intval($this->numSize):5;
		$comment = $this->showHotComment($this->cid,$num);
		$content = "<ul class=\"list1\">";
		foreach($comment as $val){
			$val['title'] = substrs($val['title'],24);
			$content .= "<li><a href=\"$val[url]\">$val[title]</a>[$val[comnum]][<a href=\"comment.php?job=main&cid=$val[cid]&mid=$val[mid]&tid=$val[tid]\">评</a>]</li>";
		}
		$content .= "</ul>";
		$content = $this->toutf8($content);
		exit($content);
	}

	/*
	*获取单个文章的评论信息
	*/
	function getsinglecomment() {
		global $db;
		$start	= ($this->pageNo-1)*$this->numSize;
		$rs = $db->query("SELECT * FROM cms_comment WHERE cid='$this->cid' AND tid='$this->tid' ORDER BY postdate DESC LIMIT $start,$this->numSize");
		$result = array();
		while ($com = $db->fetch_array($rs))
		{
			$com['author']  = $com['author']?$com['author']:"网友";
			if($com['hideip']) {
				$com['fromip'] = "已隐藏";
			}else{
				$authorip	= explode('.',$com['fromip']);
				array_pop($authorip);
				array_pop($authorip);
				$authorip[] = '*';
				$authorip[] = '*';
				$com['fromip'] = implode('.',$authorip);
			}
			$com['postdate']= get_date($com['postdate']);
			$com['message'] = preg_replace("/\[:([0-9]+):\]/e","Comment::commentFace('\\1')",$com['message']);
			$result[] = $com;
		}
		return $result;
	}

	/*
	*热门评论排行
	*/
	function showHotComment($cid,$num) {
		global $db,$timestamp,$catedb,$very;
		$comment	= array();
		$time		= $this->time?$this->time:7;
		$timelimit	= $timestamp - $time*86400;
		$cachefile	= substr(md5($cid.$num),0,15);
		$cachefile	= D_P.'data/comment/'.$cachefile.'.cache';
		if (file_exists($cachefile) && ($timestamp-filemtime($cachefile)<600)) {		//缓存时间十分钟
			$cacheStr	= readover($cachefile);
			$array		= unserialize($cacheStr);
			return $array;
		}
		if($cid!='all') {
			$cid = intval($cid);
			$sql = "SELECT title,comnum,url,cid,tid,mid FROM cms_contentindex WHERE cid='$cid' AND postdate>'$timelimit' AND ifpub=1 ORDER BY comnum DESC LIMIT $num";
		}else {
			$sql = "SELECT title,comnum,url,cid,tid,mid FROM cms_contentindex WHERE postdate>'$timelimit' AND cid>=1 AND ifpub=1 ORDER BY comnum DESC LIMIT $num";
		}
		$query = $db->query($sql);
		while($rs = $db->fetch_array($query)) {
			if($catedb[$rs['cid']]['htmlpub']){
				$rs['url'] = $very['url']."/".$very['htmdir']."/".$rs['url'];
			}else{
				$rs['url'] = $very['url']."/view.php?tid=".$rs['tid']."&cid=".$cid;
			}
			$comment[] =$rs;
		}
		writeover($cachefile,serialize($comment));
		return $comment;
	}

	function delMsg(){
		global $db,$id;
		if(!is_array($id)){
			$ids = (int)$id;
			$num = 1;
		}else{
			$ids = array();
			foreach ($id as $i){
				$ids[] = intval($i);
			}
			unset($id);
			$num = count($ids);
			$ids = implode(',',$ids);
		}
		$db->update("DELETE FROM cms_comment WHERE id IN($ids)");
		$db->update("UPDATE cms_contentindex SET comnum=comnum-$num WHERE tid='$this->tid'");
	}

	/**
	 * 将输出的内容转换为utf-8格式
	 *
	 * @param string $str
	 * @return string
	 */
	function toutf8($str){
		global $charset;
		if($charset != 'utf8'){
			$chs = new Chinese($charset,'UTF8');
			$str = $chs->Convert($str);
		}
		return $str;
	}

	function toGbk($str){
		global $charset;
		if($charset != 'utf8'){
			$chs = new Chinese('UTF8',$charset);
			$str = $chs->Convert($str);
		}
		return $str;
	}

	function commentFace($num){
		if(!$this->facedb) {
			require(D_P.'data/cache/face.php');
			$this->facedb = $facedb;
		}
		return "<img src='images/comment/".$this->facedb[$num]['facepath']."'>";
	}
}

?>