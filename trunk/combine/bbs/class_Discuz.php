<?php
!defined('IN_CMS') && die('Forbidden');
require_once(R_P.'require/class_bbs.php');
/**
 *  BBS调用模型
 */
class Discuz extends BBS{
	
	

	/**
	 * 用户排行
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function user($num,$order='money',$fid=0){ //$fid无用，仅为跟其他排行方法同样有三个参数供统一调用而已
		$num		= intval($num);
		$userinfo	= array();
		$cachefile	= 'bbs_user_'.$num.$order.$fid;
		if($userinfo = $this->readcache($cachefile)){
			return $userinfo;
		}
		$order = $this->getField($order);
		
		$rs	= $this->mysql->query("SELECT uid,username,$order FROM {$this->config['dbpre']}members ORDER BY $order DESC LIMIT 0,$num");
	
		while($rt = $this->mysql->fetch_array($rs)){
			$userdb['title'] = strip_tags($rt['username']);
			$userdb['url']	 = $this->config['url'].$this->getUrl($rt['uid'],'user');
			if($order=='rvrc' && $this->config['type']=='PHPWind') $rt[$order]=ceil($rt[$order]/10);
			$userdb['value'] = $rt[$order];
			$userinfo[] = $userdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($userinfo));
		return $userinfo;
	}

	/**
	 * 版块排行
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function forum($num,$order='topic',$fid=0){
		$num = intval($num);
		$foruminfo = array();
		$cachefile = 'bbs_forum_'.$num.$order.$fid;
		if($foruminfo = $this->readcache($cachefile)){
			return $foruminfo;
		}
		$fidNotIn = $this->fidCheck($fid);
		$order	  = $this->getField($order);
		
		$fidNotIn && $fidNotIn = " AND ".$fidNotIn;
		$rs = $this->mysql->query("SELECT fid,name,$order FROM {$this->config['dbpre']}forums WHERE status>0 AND type<>'group' $fidNotIn ORDER BY $order DESC LIMIT 0,$num");

		while ($rt = $this->mysql->fetch_array($rs)) {
			$forumdb['title']	= strip_tags($rt['name']);
			$forumdb['url']		= $this->config['url'].$this->getUrl($rt['fid'],'forum');
			$forumdb['value']	= $rt[$order];
			$foruminfo[] = $forumdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($foruminfo));
		return $foruminfo;
	}

	/**
	 * 文章排行
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function article($num,$order='hits',$fid=0){
		$num = intval($num);
		$articleInfo= array();
		$cachefile  ='bbs_article_'.$num.$order.$fid;
		if($articleInfo = $this->readcache($cachefile)){
			return $articleInfo;
		}
		$fidNotIn	= $this->fidCheck($fid);
		$fid		= $this->getField('fid');
		$postdate	= $this->getField('postdate');
		$hits		= $this->getField('hits');
		$replies	= $this->getField('replies');
		$title		= $this->getField('title');
		$tid		= $this->getField('tid');
		$digest		= $this->getField('digest');

		if(in_array($order,array('reply','hits','postdate'))){ //回复数 点击数 发表时间
			$fidNotIn && $fidNotIn=" WHERE ".$fidNotIn;
			$order = $this->getField($order);
			$rs = $this->mysql->query("SELECT $tid,$title,$order FROM $this->table $fidNotIn ORDER BY $order DESC LIMIT $num");
			while ($rt = $this->mysql->fetch_array($rs)) {
				$articledb['title'] = strip_tags($rt[$title]);
				$articledb['url']	= $this->config['url'].$this->getUrl($rt[$tid],'article');
				$articledb['value'] = $rt[$order];
				$articleInfo[]		= $articledb;
			}
		}elseif ($order=='digest'){//最新精华
			$fidNotIn && $fidNotIn=" AND ".$fidNotIn;
			$rs = $this->mysql->query("SELECT $tid,$title,$postdate FROM $this->table WHERE $digest>0 $fidNotIn ORDER BY $postdate DESC LIMIT $num");
			while ($rt = $this->mysql->fetch_array($rs)) {
				$articledb['title'] = strip_tags($rt[$title]);
				$articledb['url']	= $this->config['url'].$this->getUrl($rt[$tid],'article');
				$articledb['value'] = $rt[$postdate];
				$articleInfo[]		= $articledb;
			}
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($articleInfo));
		return $articleInfo;
	}

	/**
	 * 图片排行
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function image($num,$order,$fid=0){
		$num = intval($num);
		$imageInfo = array();
		$cachefile = 'bbs_image_'.$num.$order.$fid;
		if ($imageInfo = $this->readcache($cachefile)) {
			return $imageInfo;
		}
		$fidNotIn	= $this->fidCheck($fid);
		$attachurl	= $this->getField('attachurl');
		$subject	= $this->getField('title');
		$tid		= $this->getField('tid');
		$order		= $this->getField($order);

		if ($order=='digest') {
			$sqladd = "AND t.digest>0";
		}
		$rs = $this->mysql->query("SELECT a.tid,a.attachment,t.subject,t.dateline FROM {$this->config['dbpre']}attachments a LEFT JOIN $this->table t USING(tid) WHERE a.isimage='1' ORDER BY a.aid DESC LIMIT $num");

		while ($rt = $this->mysql->fetch_array($rs)) {
			$imagedb['title'] = strip_tags($rt[$subject]);
			if($this->config['type']=='PHPWind'){
				$rt[$attachurl] = $attchInfo[$rt[$tid]];
			}
			$imagedb['photo']	= $this->config['attachurl'].$rt[$attachurl];
			$imagedb['url']		= $this->config['url'].$this->getUrl($rt[$tid],'article');
			$imageInfo[]		= $imagedb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($imageInfo));
		return $imageInfo;
	}

	/**
	 * 论坛标签排行
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function tags($num,$order,$fid=0){
		$num = intval($num);
		$tagsInfo	= array();
		$cachefile	= 'bbs_tags_'.$num.$order.$fid;
		if ($tagsInfo = $this->readcache($cachefile)) {
			return $tagsInfo;
		}
		$rs = $this->mysql->query("SELECT tagname,total FROM {$this->config['dbpre']}tags WHERE closed='0' ORDER BY total DESC LIMIT $num");

		while($rt = $this->mysql->fetch_array($rs)){
			$tagsdb['title'] = strip_tags($rt['tagname']);
			$tagsdb['value'] = $rt['total'];
			$tagsdb['url']	 = $this->config['url'].$this->getUrl(rawurlencode($rt['tagname']),'tag');
			$tagsInfo[]		 = $tagsdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($tagsInfo));
		return $tagsInfo;
	}

	/**
	 * 论坛信息
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function bbsinfo($num,$order,$fid=0){
		$num		= intval($num);
		$bbsInfo	= array();
		$cachefile	= 'bbs_bbsinfo_'.$num.$order.$fid;
		if ($bbsInfo = $this->readcache($cachefile)) {
			return $bbsInfo;
		}
		
		$rs = $this->mysql->get_one("SELECT username FROM {$this->config['dbpre']}members ORDER BY uid DESC LIMIT 1");
		$bbsInfo['mnew']	= $rs['username'];
		$rs = $this->mysql->get_one("SELECT COUNT(*) as mtotal FROM {$this->config['dbpre']}members");
		$bbsInfo['mtotal']	= $rs['mtotal'];
		$rs = $this->mysql->query("SELECT * FROM {$this->config['dbpre']}settings WHERE variable IN ('onlinerecord', 'historyposts')");
		while($rt = $this->mysql->fetch_array($rs)){
			$rt['value'] = explode("\t",$rt['value']);
			if($rt['variable']=='onlinerecord'){
				$bbsInfo['holnum'] = $rs['value'][0];
				$bbsInfo['holtime']= get_date($rs['value'][1]);
			}elseif($rt['variable']=='historyposts'){
				$bbsInfo['yposts'] = $rt['value'][0];
				$bbsInfo['hposts'] = $rt['value'][1];
			}
		}
		$rs = $this->mysql->get_one("SELECT SUM(todayposts) AS tposts FROM {$this->config['dbpre']}forums WHERE status>0");
		$bbsInfo['tposts'] = $rs['tposts'];
		$rs = $this->mysql->get_one("SELECT COUNT(*) as threads FROM {$this->config['dbpre']}threads WHERE displayorder>='0'");
		$bbsInfo['threads'] = $bbsdb['threads'];
		$rs = $this->mysql->get_one("SELECT COUNT(*) as posts FROM {$this->config['dbpre']}posts");
		$bbsInfo['posts'] = $rs['posts'];

		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($bbsInfo));
		return $bbsInfo;
	}

	/**
	 * 论坛友情链接
	 *
	 * @return string
	 */
	function links($num,$order,$fid=0){
		$num = intval($num);
		$linksInfo = array();
		$cachefile = 'bbs_links_'.$num.$order.$fid;
		if ($linksInfo = $this->readcache($cachefile)) {
			return $linksInfo;
		}

		$rs = $this->mysql->query("SELECT name,url,description as descrip,logo FROM {$this->config['dbpre']}forumlinks ORDER BY displayorder LIMIT $num");

		while($rt = $this->mysql->fetch_array($rs)){
			$linksdb['title']	= strip_tags($rt['name']);
			$linksdb['descrip'] = $rt['descrip'];
			$linksdb['url']		= $rt['url'];
			$linksdb['photo']	= eregi('^(http://)',$rt['logo']) ? $rt['logo'] : $this->config['url'].'/'.$rt['logo'];
			if($linksdb['photo']){
				$linksdb['type'] = 1;
			}else{
				$linksdb['type'] = 0;
			}
			$linksInfo[] = $linksdb;
		}

		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($linksInfo));
		return $linksInfo;
	}

	/**
	 * 论坛公告
	 *
	 * @return string
	 */
	function notice($num,$order,$fid=0){
		global $timestamp;
		$num = intval($num);
		$noticeInfo = array();
		$cachefile	= 'bbs_notice_'.$num.$order.$fid;
		if ($noticeInfo = $this->readcache($cachefile)) {
			return $noticeInfo;
		}

		$rs = $this->mysql->query("SELECT id,author,subject,type,starttime,endtime,displayorder,groups,message as content FROM {$this->config['dbpre']}announcements WHERE type<>'2' AND groups='' AND starttime<='$timestamp' AND (endtime>='$timestamp' OR endtime='0') ORDER BY displayorder,starttime DESC LIMIT $num");

		while($rt = $this->mysql->fetch_array($rs)){
			$noticedb['title']	= strip_tags($rt['subject']);
			$noticedb['content']	= $rt['content'];
			$noticedb['author'] = $rt['author'];
			if($rt['type']=='1'){
				$rt['url'] = $rt['content'];
			}
			$noticedb['url'] = $rt['url'] ? $rt['url'] : $this->config['url'].$this->getUrl($rt['id'],'notice');
			$noticedb['url'] = eregi('^(http://)',$noticedb['url']) ? $noticedb['url'] : $this->config['url'].'/'.$noticedb['url'];
			$noticeInfo[]	 = $noticedb;
		}

		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($noticeInfo));
		return $noticeInfo;
	}

	/**
	 * 筛选出隐藏版块的fid 并构造查询的WHERE条件返回
	 *
	 * @return string
	 */
	function fidCheck($fid=0){
		$fidField = $this->getField('fid');
		if($fid){
			$fid = intval($fid);
			return " $fidField IN($fid) ";
		}
		if($this->notFid){
			return $this->notFid;
		}
		$hiddenFid = array();

		$rs = $this->mysql->query("SELECT fid FROM {$this->config['dbpre']}forums WHERE status='0'");
		while ($fid = $this->mysql->fetch_array($rs)) {
			$hiddenFid[] = $fid['fid'];
		}

		$hiddenFid = array_unique($hiddenFid);
		$hiddenFid && $hiddenSQL = " $fidField NOT IN(".implode(',',$hiddenFid).") ";
		$this->notFid = $hiddenSQL;
		return $hiddenSQL;
	}

	/**
	 * 获取到相应的url地址
	 *
	 * @param integer $id 帖子tid或者板块fid等之类的id
	 * @param string $type
	 * @return array
	 */
	function getUrl($id,$type){
		if($this->config['htmifopen']){
			$url = array(
				'user'		=> 'profile-uid-'.$id.$this->config['htmext'],
				'forum'		=> 'forum-'.$id.$this->config['htmext'],
				'article'	=> 'thread-'.$id.$this->config['htmext'],
				'tag'		=> 'tag.php?name='.$id,
				'notice'	=> 'announcement.php?id='.$id.'#'.$id
			);
		}else{
			$url = array(
				'user'=>'viewpro.php?uid='.$id,
				'forum'=>'forumdisplay.php?fid='.$id,
				'article'=>'viewthread.php?tid='.$id,
				'tag'=>'tag.php?name='.$id,
				'notice'	=> 'announcement.php?id='.$id.'#'.$id
			);
		}
		return '/'.$url[$type];
	}


	/**
	 * 获取字段名,因为BBS版本不一致导致的字段名不一致,此方法来返回一个字段名
	 *
	 * @param string $var
	 * @return string
	 */
	function getField($var){

		$this->fields = array(
		'tid'		=> 'tid',
		'fid'		=> 'fid',
		'hits'		=> 'views',
		'postdate'	=> 'dateline',
		'digest'	=> 'digest',
		'lastpost'	=> 'lastpost',
		'replies'	=> 'replies',
		'title'		=> 'subject',
		'article'	=> 'threads',
		'tpost'		=> 'todayposts',
		'topic'		=> 'posts',
		'money'		=> 'extcredits2',
		'rvrc'		=> 'extcredits1',
		'todaypost'	=> 'todaypost',
		'postnum'	=> 'posts',
		'attachurl'	=> 'attachment',
		);
				
		if($this->fields[$var]){
			return $this->fields[$var];
		}else{
			return ;
		}
	}

	function setTableName(){
		$this->table = $this->config['dbpre'].'threads';

	}
	/**
	 * 根据条件读取BBS数据，本方法主要供CMS类的thread方法调用
	 *
	 * @param integer $start
	 * @param integer $displaynum
	 * @return array
	 */
	function getThread($start,$displaynum){
		global $upTids,$catedb;
		!$this->order && $this->order = 'tid';
		if($this->onlyimg){
			if($this->sqladd){
				$sqladd = $this->sqladd." AND t.attachment=1 AND a.isimage=1";
			}else{
				$sqladd = " WHERE t.attachment=1 AND a.isimage=1";
			}
			$this->totalQuery = "SELECT count(*) AS total FROM {$this->table} t LEFT JOIN {$this->config['dbpre']}attachments a USING(tid) $sqladd ";
			$rs = $this->mysql->query("SELECT t.*,t.attachment AS at,a.* FROM {$this->table} t LEFT JOIN {$this->config['dbpre']}attachments a USING(tid) $sqladd ORDER BY t.$this->order DESC LIMIT $start,$displaynum");
			//有图片的情况下
		}else{
			$this->totalQuery = "SELECT count(*) AS total FROM {$this->table} t $this->sqladd";
			$rs = $this->mysql->query("SELECT * FROM {$this->table} t $this->sqladd ORDER BY t.$this->order DESC LIMIT $start,$displaynum");
		}

		$content	= array();
		$upTids		= '';
		$postdate	= $this->getField('postdate');
		$subject	= $this->getField('title');
		$attachurl	= $this->getField('attachurl');
		$hits		= $this->getField('hits');
		$htm_ext	= $GLOBALS['sys']['htmext'] ? $GLOBALS['sys']['htmext'] : 'html';
		while ($thread = $this->mysql->fetch_array($rs)) {
			$thread['title']	= $thread[$subject];
			$thread['postdate']	= $thread[$postdate];
			$thread['hits']		= $thread[$hits];
			if($this->viewtype){
				if($catedb[$this->cid]['htmlpub']){
					$thread['url'] = $GLOBALS['sys']['htmdir'].'/'.$this->viewtype.'/'.$thread['tid'].'.'.$htm_ext;
					if(!file_exists($thread['url'])){
						$thread['ifpub'] = 0;
						if($catedb[$this->cid]['autopub']){
							$upTids .= $upTids ? '|'.$thread['tid'] : $thread['tid'];
							$thread['ifpub'] = 2;
						}
						//$thread['url'] = $GLOBALS['sys']['url']."/view.php?tid=".$thread['tid']."&cid=".$this->cid;
						$thread['url'] = $this->config['url'].$this->getUrl($thread['tid'],'article');
					}else{
						$thread['ifpub']	= 1;
					}
				}else{
					$thread['url']	 = $GLOBALS['sys']['url']."/view.php?tid=".$thread['tid']."&cid=".$this->cid;
					$thread['ifpub'] = -1;
				}
			}else{//BBS原帖地址
				$thread['url']		= $this->config['url'].$this->getUrl($thread['tid'],'article');
				$thread['ifpub']	= -1;
			}
			if((SCR=='list'||SCR=='view') && $thread['ifpub']==0) continue;

			$thread['publisher'] = $thread['author'];
			if($this->onlyimg)
			{
				!$thread[$attachurl] && $thread[$attachurl] = $attchInfo[$thread['tid']];
				$thread['photo'] = $this->config['attachurl'].$thread[$attachurl];
			}
			$content[] = $thread;
		}
		$this->mysql->free_result($rs);
		if($upTids){
			writeover(D_P.'data/cache/updatelist_'.$this->cid.'.cache',$upTids);
		}
		return $content;
	}

	function getOne($tid,$cid){
		global $attachments,$bbsurl,$aids;
		$attachments = array();
		$tid = intval($tid);
		!$tid && throwError('data_error');

		$rs = $this->mysql->get_one("SELECT * FROM $this->table t LEFT JOIN {$this->config['dbpre']}posts p USING(tid) WHERE tid='$tid'");
		!$rs && throwError('data_error');
		
		$rs['title']	= $rs[$this->getField('title')];
		$rs['postdate'] = $rs[$this->getField('postdate')];
		$rs['hits']		= $rs[$this->getField('hits')];
		$rs['replis']	= $rs[$this->getField('replies')];
		$rs['author']	= $rs[$this->getField('author')];
		$rs['fromsite'] = $this->config['url'];
		$rs['bbsurl']	= $bbsurl = $this->config['url'].$this->getUrl($rs['tid'],'article');

		if($rs['attachment']) {
			$rt = $this->mysql->query("SELECT * FROM {$this->config['dbpre']}attachments WHERE pid='$rs[pid]'");

			while($attach = $this->mysql->fetch_array($rt)) {
				$attachments[$attach[aid]] = $attach['isimage'] == 1 ? '<img src="'.$this->config['attachurl'].$attach['attachment'].'" alt="'.$attach['description'].'"/>' : '附件：<a href="'.$rs['bbsurl'].'" title="转到论坛"/>'.$attach['filename'].'</a>';
			}
		}
		$rs['content'] 	= Discuzcode::convert($rs['message']);

		

		foreach($aids as $value){
			if($attachments[$value]){
				unset($attachments[$value]);
			}
		}
		while($val=array_pop($attachments)){
			$type = substr($val,1,3);
			if($type == 'img'){
				$rs['content']=$val."<br />".$rs['content'];
			}else{
				$rs['content'].="<br />".$val;
			}
		}
		unset($attachments,$aids);
		return $rs;
	}

	/**
	 * 统计内容总数
	 *
	 * @return integer
	 */
	function total(){
		$total = $this->mysql->get_one($this->totalQuery);
		return $total['total'];
	}

}

class Discuzcode {
	function convert($message) {
		//$message = preg_replace("/\s*\[code\](.+?)\[\/code\]\s*/ies", "Discuzcode::codedisp('\\1')", $message);

		$message = Discuzcode::dhtmlspecialchars($message);
		$searcharray = array(
			"/\[url\]\s*(www.|https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|bctp:\/\/|ed2k:\/\/|thunder:\/\/|synacast:\/\/){1}([^\[\"']+?)\s*\[\/url\]/ie",
			"/\[url=www.([^\[\"']+?)\](.+?)\[\/url\]/is",
			"/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/is",
			"/\[email\]\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*\[\/email\]/i",
			"/\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[\/email\]/is",
			"/\[color=([#\w]+?)\](.+?)\[\/color\]/i",
			"/\[size=(\d+?)\](.+?)\[\/size\]/i",
			"/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\](.+?)\[\/size\]/i",
			"/\[font=([^\[\<]+?)\](.+?)\[\/font\]/i",
			"/\[align=(left|center|right)\](.+?)\[\/align\]/i",
			"/\[float=(left|right)\](.+?)\[\/float\]/i"
		);
		$replacearray = array(
			"Discuzcode::cuturl('\\1\\2')",
			"<a href=\"http://www.\\1\" target=\"_blank\">\\2</a>",
			"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>",
			"<a href=\"mailto:\\1@\\2\">\\1@\\2</a>",
			"<a href=\"mailto:\\1@\\2\">\\3</a>",
			"<font color=\"\\1\">\\2</font>",
			"<font size=\"\\1\">\\2</font>",
			"<font style=\"font-size: \\1\">\\2</font>",
			"<font face=\"\\1 \">\\2</font>",
			"<p align=\"\\1\">\\2</p>",
			"<span style=\"float: \\1;\">\\2</span>"
		);
		$message = preg_replace($searcharray,$replacearray,$message);
		$message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies","Discuzcode::parsetable('\\1', '\\2', '\\3')",$message);
		$searcharray = array(
			"[b]", "[/b]",
			"[i]", "[/i]", "[u]", "[/u]", "[list]", "[list=1]", "[list=a]",
			"[list=A]", "[*]", "[/list]", "[indent]", "[/indent]", "[/float]"
		);
		$replacearray = array(
			"<strong>", "</strong>", "<i>",
			"</i>", "<u>", "</u>", "<ul>", "<ul type=1 class=list1>", "<ul type=a class=lista>",
			"<ul type=A class=listua>", "<li>", "</ul>", "<blockquote>", "</blockquote>", "</span>"
		);
		$message = str_replace($searcharray,$replacearray,$message);


		$searcharray	= "/\[media=(\w{1,4}),(\d{1,4}),(\d{1,4}),(\d)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies";
		$replacearray	= "Discuzcode::parsemedia('\\1', \\2, \\3, \\4, '\\5')";

		$message = preg_replace($searcharray,$replacearray,$message);

		$message = preg_replace(array(
				"/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies",
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
				"/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
			), array(
				"Discuzcode::bbcodeurl('\\1', ' <img src=\"images/attachicons/flash.gif\" align=\"absmiddle\" alt=\"\" /> <a href=\"%s\" target=\"_blank\">Flash: %s</a> ')",
				"Discuzcode::bbcodeurl('\\1', '<img src=\"%s\" border=\"0\" onclick=\"zoom(this)\" onload=\"if(this.width>document.body.clientWidth*0.5) {this.resized=true;this.width=document.body.clientWidth*0.5;this.style.cursor=\'pointer\';} else {this.onclick=null}\" alt=\"\" />')",
				"Discuzcode::bbcodeurl('\\3', '<img width=\"\\1\" height=\"\\2\" src=\"%s\" border=\"0\" alt=\"\" />')"
			) , $message);
		$aids	 = array();
		$message = Discuzcode::attachment($message);
		$message = preg_replace("/\[hide\](.+?)\[\/hide\]/is","<font color=red>此处为隐藏内容，请到论坛浏览</font>",$message);

		return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	}
	function parsetable($width, $bgcolor, $message) {
		if(!preg_match("/^\[tr(?:=([\(\)%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
			return preg_replace("/\[tr(?:=([\(\)%,#\w]+))?\]|\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]|\[\/td\]|\[\/tr\]/", '', $message);
		}
		$width = substr($width, -1) == '%' ? (substr($width, 0, -1) <= 98 ? intval($width).'%' : '98%') : ($width <= 560 ? intval($width).'px' : '98%');
		return '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>').
			str_replace('\\"', '"', preg_replace(array(
					"/\[tr(?:=([\(\)%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
					"/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/ie",
					"/\[\/td\]\s*\[\/tr\]/i"
				), array(
					"Discuzcode::parsetrtd('\\1', '\\2', '\\3', '\\4')",
					"Discuzcode::parsetrtd('td', '\\1', '\\2', '\\3')",
					'</td></tr>'
				), $message)
			).'</table>';
	}
	function cuturl($url) {
		$length = 65;
		$urllink = "<a href=\"".(substr(strtolower($url), 0, 4) == 'www.' ? "http://$url" : $url).'" target="_blank">';
		if(strlen($url) > $length) {
			$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		$urllink .= $url.'</a>';
		return $urllink;
	}

	function dhtmlspecialchars($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = Discuzcode::dhtmlspecialchars($val);
			}
		} else {
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
		}
		return $string;
	}

	function parsemedia($type, $width, $height, $autostart, $url) {
		if(in_array($type, array('ra', 'rm', 'wma', 'wmv', 'mp3', 'mov'))) {
			$url		= str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
			$mediaid	= 'media_'.random(3);
			$autostartw = $autostart ? 1 : 0;
			switch($type) {
				case 'ra'	: return '<object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="autostart" value="'.$autostart.'" /><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" '.($autostart ? 'autostart="true"' : '').' console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';break;
				case 'rm'	: return '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.$autostart.'" /><param name="src" value="'.$url.'" /><param name="controls" value="imagewindow" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="IMAGEWINDOW" console="'.$mediaid.'_" width="'.$width.'" height="'.$height.'"></embed></object><br /><object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" type="audio/x-pn-realaudio-plugin" controls="ControlPanel" '.($autostart ? 'autostart="true"' : '').' console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>';break;
				case 'wma'	: return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="64"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="audio/x-ms-wma" width="'.$width.'" height="64"></embed></object>';break;
				case 'wmv'	: return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="video/x-ms-wmv" width="'.$width.'" height="'.$height.'"></embed></object>';break;
				case 'mp3'	: return '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="64"><param name="autostart" value="'.$autostart.'" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="'.$autostart.'" type="application/x-mplayer2" width="'.$width.'" height="64"></embed></object>';break;
				case 'mov'	: return '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'.$width.'" height="'.$height.'"><param name="autostart" value="'.($autostart ? 'true' : 'false').'" /><param name="src" value="'.$url.'" /><embed controller="true" width="'.$width.'" height="'.$height.'" src="'.$url.'" autostart="'.($autostart ? 'true' : 'false').'"></embed></object>';break;
			}
		}
		return;
	}
	function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
		return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor ? ' bgcolor="'.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width ? ' width="'.$width.'"' : '').'>';
	}
	function bbcodeurl($url, $tags) {
		if(!preg_match("/<.+?>/s", $url)) {
			if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://'))) {
				$url = 'http://'.$url;
			}
			return str_replace(array('submit', 'logging.php'), array('', ''), sprintf($tags, $url, addslashes($url)));
		} else {
			return '&nbsp;'.$url;
		}
	}

	function attachment($message){
		$message = preg_replace("/\[attach\]([0-9]+)\[\/attach\]/eis","Discuzcode::upload('\\1')",$message);
		return $message;
	}

	function upload($aid){
		global $attachments,$aids;
		if($attachments[$aid]){
			$aids[]=$aid;
			return $attachments[$aid];
		} else{
			return "[attach]".$aid."[/attach]";
		}
	}
}

?>