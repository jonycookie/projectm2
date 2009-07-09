<?php
!defined('IN_CMS') && die('Forbidden');
require_once(R_P.'require/class_bbs.php');
/**
 *  BBS调用模型
 */
class PHPWind extends BBS{

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

		if(strpos($order,"DESC") || strpos($order,"ASC")){
			$orderby = "ORDER BY md.$order";
		}else{
			$orderby = "ORDER BY md.$order DESC";
		}
		if($order=='todaypost'){
			$todaytime = date("y-m-d",time());
			$todaytime = strtotime($todaytime);
			$orderby   = "WHERE md.lastpost>$todaytime ".$orderby;
		}elseif($order=='monthpost'){
			$todaytime = date("y-m",time())."-1";
			$todaytime = strtotime($todaytime);
			$orderby   = "WHERE md.lastpost>$todaytime ".$orderby;
		}
		$rs = $this->mysql->query("SELECT md.uid,md.$order,m.username FROM {$this->config['dbpre']}memberdata md LEFT JOIN {$this->config['dbpre']}members m USING(uid) $orderby LIMIT 0,$num");

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

		$fidNotIn && $fidNotIn = " AND d.".$fidNotIn;
		$rs = $this->mysql->query("SELECT f.fid,f.name,d.$order FROM {$this->config['dbpre']}forumdata d LEFT JOIN {$this->config['dbpre']}forums f USING(fid) WHERE f.type<>'category' $fidNotIn ORDER BY d.$order DESC LIMIT 0,$num");

		while ($rt = $this->mysql->fetch_array($rs)) {
			$forumdb['title'] = strip_tags($rt['name']);
			$forumdb['url']   = $this->config['url'].$this->getUrl($rt['fid'],'forum');
			$forumdb['value'] = $rt[$order];
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
		$articleInfo = array();
		$cachefile	 ='bbs_article_'.$num.$order.$fid;
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
			$fidNotIn && $fidNotIn = " WHERE ".$fidNotIn;
			$order = $this->getField($order);
			$rs = $this->mysql->query("SELECT $tid,$title,$order FROM $this->table $fidNotIn ORDER BY $order DESC LIMIT $num");
			while ($rt = $this->mysql->fetch_array($rs)) {
				$articledb['title'] = strip_tags($rt[$title]);
				$articledb['url']	= $this->config['url'].$this->getUrl($rt[$tid],'article');
				$articledb['value'] = $rt[$order];
				$articleInfo[] = $articledb;
			}
		}elseif ($order=='digest'){//最新精华
			$fidNotIn && $fidNotIn=" AND ".$fidNotIn;
			$rs = $this->mysql->query("SELECT $tid,$title,$postdate FROM $this->table WHERE $digest>0 $fidNotIn ORDER BY $postdate DESC LIMIT $num");
			while ($rt = $this->mysql->fetch_array($rs)) {
				$articledb['title'] = strip_tags($rt[$title]);
				$articledb['url']	= $this->config['url'].$this->getUrl($rt[$tid],'article');
				$articledb['value'] = $rt[$postdate];
				$articleInfo[] = $articledb;
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

		$fidNotIn && $fidNotIn = ' AND t.'.$fidNotIn;
		$rt = $this->mysql->query("SELECT tid,attachurl FROM {$this->config['dbpre']}attachs t WHERE t.type like '%img' AND t.tid>0 $fidNotIn ORDER BY t.aid DESC LIMIT $num");
		$attchInfo = $tids = array();
		while ($a = $this->mysql->fetch_array($rt)) {
			$tids[] = $a['tid'];
			$attchInfo[$a['tid']] = $a['attachurl'];
		}
		$this->mysql->free_result($rt);
		if($attchInfo){
			$tids = implode(',',$tids);
			$rs	  = $this->mysql->query("SELECT tid,subject,postdate FROM {$this->table} WHERE tid IN($tids)");
		}

		while ($rt = $this->mysql->fetch_array($rs)) {
			$imagedb['title'] = strip_tags($rt[$subject]);
			if($this->config['type']=='PHPWind'){
				$rt[$attachurl] = $attchInfo[$rt[$tid]];
			}
			$imagedb['photo']	= $this->config['attachurl'].$rt[$attachurl];
			$imagedb['url']		= $this->config['url'].$this->getUrl($rt[$tid],'article');
			$imageInfo[] = $imagedb;
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

		$rs = $this->mysql->query("SELECT tagname,num as total FROM {$this->config['dbpre']}tags WHERE ifhot='0' ORDER BY num DESC LIMIT $num");

		while($rt = $this->mysql->fetch_array($rs)){
			$tagsdb['title'] = strip_tags($rt['tagname']);
			$tagsdb['value'] = $rt['total'];
			$tagsdb['url']	 = $this->config['url'].$this->getUrl(rawurlencode($rt['tagname']),'tag');
			$tagsInfo[] = $tagsdb;
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
		$num = intval($num);
		$bbsInfo	= array();
		$cachefile	= 'bbs_bbsinfo_'.$num.$order.$fid;
		if ($bbsInfo = $this->readcache($cachefile)) {
			return $bbsInfo;
		}

		$rs = $this->mysql->get_one("SELECT * FROM {$this->config['dbpre']}bbsinfo WHERE id='1'");
		$bbsInfo['mnew']	= $rs['newmember'];
		$bbsInfo['mtotal']	= $rs['totalmember'];
		$bbsInfo['holnum']	= $rs['higholnum'];
		$bbsInfo['holtime']	= get_date($rs['higholtime']);
		$bbsInfo['yposts']	= $rs['yposts'];
		$bbsInfo['hposts']	= $rs['hposts'];
		$rs = $this->mysql->get_one("SELECT SUM(tpost) AS tposts,SUM(topic) AS threads,SUM(article) AS posts FROM {$this->config['dbpre']}forumdata");
		$bbsInfo['tposts']  = $rs['tposts'];
		$bbsInfo['threads'] = $rs['threads'];
		$bbsInfo['posts']	= $rs['posts'];

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

		$rs = $this->mysql->query("SELECT name,url,descrip,logo FROM {$this->config['dbpre']}sharelinks WHERE ifcheck=1 ORDER BY threadorder LIMIT $num");

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
		$cachefile = 'bbs_notice_'.$num.$order.$fid;
		if ($noticeInfo = $this->readcache($cachefile)) {
			return $noticeInfo;
		}

		$rs = $this->mysql->query("SELECT aid as id,ffid,vieworder,author,startdate,url,enddate,subject,content FROM pw_announce WHERE ffid='0' AND startdate<='$timestamp' AND (enddate>='$timestamp' OR enddate='') ORDER BY vieworder,startdate DESC LIMIT $num");

		while($rt = $this->mysql->fetch_array($rs)){
			$noticedb['title']	= strip_tags($rt['subject']);
			$noticedb['content']= $rt['content'];
			$noticedb['author'] = $rt['author'];
			$noticedb['url']	= $rt['url'] ? $rt['url'] : $this->config['url'].$this->getUrl($rt['id'],'notice');
			$noticedb['url']	= eregi('^(http://)',$noticedb['url']) ? $noticedb['url'] : $this->config['url'].'/'.$noticedb['url'];
			$noticeInfo[]		= $noticedb;
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

		$rs = $this->mysql->query("SELECT fid FROM {$this->config['dbpre']}forums WHERE f_type='hidden'");
		while ($fid = $this->mysql->fetch_array($rs)) {
			$hiddenFid[] = $fid['fid'];
		}
		$recycle = $this->mysql->get_one("SELECT db_value FROM {$this->config['dbpre']}config WHERE db_name='db_recycle'");
		if($recycle['db_value']){
			$hiddenFid[] = $recycle['db_value']; //回收站
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
				'user'		=> 'profile'.$this->config['htmdir'].'action-show-uid-'.$id.$this->config['htmext'],
				'forum'		=> 'thread'.$this->config['htmdir'].'fid-'.$id.$this->config['htmext'],
				'article'	=> 'read'.$this->config['htmdir'].'tid-'.$id.$this->config['htmext'],
				'tag'		=> 'job'.$this->config['htmdir'].'action-tag-tagname-'.$id.$this->config['htmext'],
				'notice'	=> 'notice'.$this->config['htmdir'].'fid--1'.$this->config['htmext'].'#'.$id
			);
		}else{
			$url = array(
				'user'		=> 'profile.php?action=show&uid='.$id,
				'forum'		=> 'thread.php?fid='.$id,
				'article'	=> 'read.php?tid='.$id,
				'tag'		=> 'job.php?action=tag&tagname='.$id,
				'notice'	=> 'notice.php?fid=-1#'.$id
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
			'hits'		=> 'hits',
			'postdate'	=> 'postdate',
			'digest'	=> 'digest',
			'lastpost'	=> 'lastpost',
			'replies'	=> 'replies',
			'title'		=> 'subject',
			'article'	=> 'article',
			'tpost'		=> 'tpost',
			'topic'		=> 'topic',
			'money'		=> 'money',
			'rvrc'		=> 'rvrc',
			'todaypost'	=> 'todaypost',
			'monthpost'	=> 'monthpost',
			'credit'	=> 'credit',
			'postnum'	=> 'postnum',
			'attachurl'	=> 'attachurl',
			'author'	=> 'author',
			'shield'	=> 'ifshield',
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
	function getTtable($tid){
		$tmsgs = 'tmsgs';
		$db_tlist = $this->mysql->get_one("SELECT db_value FROM {$this->config['dbpre']}config WHERE db_name='db_tlist'");
		if($db_tlist){
			$tlistdb = unserialize($db_tlist['db_value']);
			foreach($tlistdb as $key=>$value){
				if($key>0 && $tid>$value){
					$tmsgs = 'tmsgs'.intval($key);
					break;
				}
			}
		}

		return $tmsgs;
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
		$this->order = (!$this->order && !($this->table=='pw_threads')) ? 'tid':'lastpost';
		if($this->order == 'lastpost' && $this->sqladd){
			$this->sqladd .= 'AND t.ifcheck=1';
			$this->order = 'topped DESC,t.lastpost';
		}
		if($this->onlyimg){
			if($this->condition['fid']){
				$sqladd="WHERE t.fid IN(".$this->condition['fid'].") AND t.type like'%img' AND t.tid>0 ";
			}else{
				$sqladd=" WHERE t.type like'%img' AND t.tid>0 ";
			}
			$this->totalQuery = "SELECT COUNT(*) AS total FROM {$this->config['dbpre']}attachs t $sqladd GROUP BY t.tid ";
			$rt = $this->mysql->query("SELECT tid,attachurl FROM {$this->config['dbpre']}attachs t $sqladd ORDER BY t.aid DESC LIMIT $start,$displaynum");
			$attchInfo = $tids = array();
			while ($a = $this->mysql->fetch_array($rt)){
				$tids[] = $a['tid'];
				$attchInfo[$a['tid']] = $a['attachurl'];
			}
			$this->mysql->free_result($rt);
			if($attchInfo){
				$tids = implode(',',$tids);

				$sqladd = " WHERE t.tid IN($tids)";
				$rs = $this->mysql->query("SELECT t.* FROM {$this->table} t $sqladd ORDER BY t.$this->order DESC");
			}
		}else{
			$this->totalQuery = "SELECT count(*) AS total FROM {$this->table} t $this->sqladd";
			$rs = $this->mysql->query("SELECT t.* FROM {$this->table} t $this->sqladd ORDER BY t.$this->order DESC LIMIT $start,$displaynum");
		}

		$content = array();
		$upTids  = '';
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
		global $attachments,$bbsurl,$aids,$catedb;
		$attachments = array();
		$tid = intval($tid);
		!$tid && throwError('data_error');

		$tmsgs = $this->getTtable($tid);
		$addtion = unserialize(stripslashes($catedb[$cid]['addtion']));
		$fids = explode(',',$addtion['fid']);
		$rs = $this->mysql->get_one("SELECT t.*,m.* FROM $this->table t LEFT JOIN {$this->config['dbpre']}{$tmsgs} m USING(tid) WHERE t.tid='$tid'");
		$rs && !in_array($rs['fid'],$fids) && throwError('data_error');
		$rs['aid'] && $attachments	= unserialize($rs['aid']);
		foreach($attachments as $key => $val){
			$attachments[$key] = $val['type'] == 'img' ? '<img src="'.$this->config['attachurl'].$val['attachurl'].'" alt="'.$val['desc'].'"/>' : '附件：<a href="'.$this->config['url'].$this->getUrl($tid,'article').'" title="转到论坛"/>'.$val['name'].'</a>';
		}
		$rs['title']	= $rs[$this->getField('title')];
		$rs['postdate'] = $rs[$this->getField('postdate')];
		$rs['hits']		= $rs[$this->getField('hits')];
		$rs['replis']	= $rs[$this->getField('replies')];
		$rs['author']	= $rs[$this->getField('author')];
		$rs['fromsite'] = $this->config['url'];
		$rs['bbsurl']	= $bbsurl = $this->config['url'].$this->getUrl($rs['tid'],'article');
		$rs['content'] 	= BBSCode::convert($rs['content'],'',$rs['author']);
		if($this->cid && $catedb[$this->cid]['htmlpub']) {
			$htm_ext	= $GLOBALS['sys']['htmext'] ? $GLOBALS['sys']['htmext'] : 'html';
			$threaddir	= $GLOBALS['sys']['htmdir'].'/'.$this->cid.'/'.$rs['itemid'].'.'.$htm_ext;
			if(file_exists($threaddir)) {
				$rs['ifpub'] = 1;
			}else {
				$rs['ifpub'] = 0;
			}
		}else {
			$rs['ifpub'] = 1;
		}
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

	function getMemberInfo($id,$type=null){
		if($type){
			$id = Char_cv($id);
			$detail = $this->mysql->get_one("SELECT m.uid,m.username,m.password,m.safecv,m.icon,m.regdate,m.honor,m.newpm,m.newrp,m.groupid,m.memberid,md.postnum,md.rvrc,md.money,md.credit,md.currency,md.onlinetime,md.lastvisit FROM {$this->config['dbpre']}members m LEFT JOIN {$this->config['dbpre']}memberdata md ON m.uid=md.uid WHERE m.username='$id'");
		}else{
			if(!is_numeric($id)){
				return false;
			}
			$detail = $this->mysql->get_one("SELECT m.uid,m.username,m.password,m.safecv,m.icon,m.regdate,m.honor,m.newpm,m.newrp,m.groupid,m.memberid,md.postnum,md.rvrc,md.money,md.credit,md.currency,md.onlinetime,md.lastvisit FROM {$this->config['dbpre']}members m LEFT JOIN {$this->config['dbpre']}memberdata md ON m.uid=md.uid WHERE m.uid='$id'");
		}
		if(!$detail){
			return false;
		}
		$detail['ontime']		= (int)($detail['onlinetime']/3600);
		$detail['rvrc']			= (int)$detail['rvrc']/10;
		$detail['lastlogin']	= get_date($detail['lastvisit']);
		if($detail['groupid']==-1) {
			$rt = $this->mysql->get_one("SELECT grouptitle FROM {$this->config['dbpre']}usergroups WHERE gid='$detail[memberid]'");
		}else {
			$rt = $this->mysql->get_one("SELECT grouptitle FROM {$this->config['dbpre']}usergroups WHERE gid='$detail[groupid]'");
		}
		$detail['group'] = $rt['grouptitle'];
		return $detail;
	}
}

class BBSCode{

	function convert($message,$allow,$author){
		global $sys,$phpcode_htm,$code_htm,$bbsurl;

		$message  = nl2br($message);
		$code_num = 0;
		$code_htm = array();
		if(strpos($message,"[code]") !== false && strpos($message,"[/code]") !== false){
			$message = preg_replace("/\[code\](.+?)\[\/code\]/eis","BBSCode::phpcode('\\1')",$message);
		}
		if(strpos($message,"[payto]") !== false && strpos($message,"[/payto]") !== false){
			$message = preg_replace("/\[payto\](.+?)\[\/payto\]/eis","BBSCode::payto('\\1')",$message);
		}
		$message = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is', "<ol type=\"\\1\" style=\"margin:0 0 0 25px\">\\2</ol>", $message);

		$searcharray  = array('[u]','[/u]','[b]','[/b]','[i]','[/i]','[list]','[li]','[/li]','[/list]','[sub]',
		'[/sub]','[sup]','[/sup]','[strike]','[/strike]','[blockquote]','[/blockquote]','[hr]'
		);
		$replacearray = array('<u>','</u>','<b>','</b>','<i>','</i>','<ul style="margin:0 0 0 15px">','<li>','</li>', '</ul>','<sub>','</sub>','<sup>','</sup>','<strike>','</strike>','<blockquote>','</blockquote>','<hr />'
		);
		$message = str_replace($searcharray,$replacearray,$message);

		$message = str_replace("p_w_upload",$sys['bbs_attachdir'],$message);//此处位置不可调换
		$searcharray = array(
		"/\[font=([^\[]+?)\](.+?)\[\/font\]/is",
		"/\[color=([#0-9a-z]{1,10})\](.+?)\[\/color\]/is",
		"/\[backcolor=([#0-9a-z]{1,10})\](.+?)\[\/backcolor\]/is",
		"/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is",
		"/\[email\]([^\[]*)\[\/email\]/is",
		"/\[size=(\d+)\](.+?)\[\/size\]/eis",
		"/(\[align=)(left|center|right|justify)(\])(.+?)(\[\/align\])/is",
		"/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is"
		);
		$replacearray = array(
		"<span style=\"font-family:\\1\">\\2</span>",
		"<span style=\"color:\\1\">\\2</span>",
		"<span style=\"background-color:\\1\">\\2</span>",
		"<a href=\"mailto:\\1\">\\2</a>",
		"<a href=\"mailto:\\1\">\\1</a>",
		"BBSCode::size('\\1','\\2','$allow[size]')",
		"<div align=\"\\2\">\\4</div>",
		"<div style=\"width:\\1px;filter:glow(color=\\2,strength=\\3);\">\\4</div>"
		);
		$message = preg_replace($searcharray,$replacearray,$message);

		$message = preg_replace("/\[img\](.+?)\[\/img\]/eis","BBSCode::cvpic('\\1','','$allow[picwidth]','$allow[picheight]')",$message);

		if(strpos($message,'[/URL]')!==false || strpos($message,'[/url]')!==false){
			$searcharray = array(
			"/\[url=(https?|ftp|gopher|news|telnet|mms|rtsp)([^\[\s]+?)\](.+?)\[\/url\]/eis",
			"/\[url\]www\.([^\[]+?)\[\/url\]/eis",
			"/\[url\](https?|ftp|gopher|news|telnet|mms|rtsp)([^\[]+?)\[\/url\]/eis"
			);
			$replacearray = array(
			"BBSCode::cvurl('\\1','\\2','\\3')",
			"BBSCode::cvurl('\\1')",
			"BBSCode::cvurl('\\1','\\2')",
			);
			$message = preg_replace($searcharray,$replacearray,$message);
		}

		$searcharray = array(
		"/\[fly\]([^\[]*)\[\/fly\]/is",
		"/\[move\]([^\[]*)\[\/move\]/is",
		);
		$replacearray = array(
		"<marquee width=90% behavior=alternate scrollamount=3>\\1</marquee>",
		"<marquee scrollamount=3>\\1</marquee>",
		);
		$message = preg_replace($searcharray,$replacearray,$message);

		$t = 0;
		while(strpos($message,"[table") !== false && strpos($message,"[/table]") !== false){
			$message = preg_replace('/\[table(=(\d{1,3}(%|px)?))?\](.*?)\[\/table\]/eis', "BBSCode::table('\\2','\\3','\\4')",$message);
			if(++$t>4) break;
		}
//对要求回复、隐藏帖、出售帖简单处理

		if(strpos($message,"[post]") !== false && strpos($message,"[/post]") !== false){
			$message = preg_replace("/\[post\](.+?)\[\/post\]/eis","BBSCode::post('\\1')",$message);
		}
		if(strpos($message,"[hide") !== false && strpos($message,"[/hide]")!==false){
			$message = preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/eis","BBSCode::hiden('\\2')",$message);
		}
		if(strpos($message,"[sell") !== false && strpos($message,"[/sell]") !== false){
			$message = preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/eis","BBSCode::sell('\\2')",$message);
		}
		if(strpos($message,"[quote]") !== false && strpos($message,"[/quote]") !== false){
			$message = preg_replace("/\[quote\](.+?)\[\/quote\]/eis","BBSCode::qoute('\\1')",$message);
		}
		if(is_array($code_htm)){
			krsort($code_htm);
			foreach($code_htm as $codehtm){
				foreach($codehtm as $key=>$value){
					$message=str_replace("<\twind_code_$key\t>",$value,$message);
				}
			}
		}

		$message = preg_replace("/(\[flash=)(\d+?)(\,)(\d+?)(\])(.+?)(\[\/flash\])/is","<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH=\\2 HEIGHT=\\4><PARAM NAME=MOVIE VALUE=\\6><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC=\\6 WIDTH=\\2 HEIGHT=\\4 PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT><br />[<a target=_blank href=\\6>Full Screen</a>] ",$message);

		$message = preg_replace(
		array(
		"/\[wmv=(0|1)\](.+?)\[\/wmv\]/eis",
		"/\[wmv=([0-9]{1,3})\,([0-9]{1,3})\,(0|1)\](.+?)\[\/wmv\]/eis",
		"/\[rm\](.+?)\[\/rm\]/eis",
		"/\[rm=([0-9]{1,3})\,([0-9]{1,3})\,(0|1)\](.+?)\[\/rm\]/eis"
		),
		array(
		"BBSCode::wmvplayer('\\2','314','53','\\1')",
		"BBSCode::wmvplayer('\\4','\\1','\\2','\\3')",
		"BBSCode::rmplayer('\\1')",
		"BBSCode::rmplayer('\\4','\\1','\\2','\\3')"
		),$message
		);

		$message = preg_replace("/\[iframe\](.+?)\[\/iframe\]/is","Iframe Close: <a target=_blank href='\\1'>\\1</a>",$message);
		$aids = array();
		$message = BBSCode::attachment($message);
		if(is_array($phpcode_htm)){
			foreach($phpcode_htm as $key=>$value){
				$message=str_replace("<\twind_phpcode_$key\t>",$value,$message);
			}
		}
		if(strpos($message,'[s:')!==false){
			global $act;
			$act = "<font color=red><b>[$author]</b></font>";
			$message = preg_replace("/\[s:(.+?)\]/eis","BBSCode::postcache('\\1','1')",$message);
		}
		return $message;
	}

	function attachment($message){
		$message=preg_replace("/\[attachment=([0-9]+)\]/eis","BBSCode::upload('\\1')",$message);
		return $message;
	}

	function upload($aid){
		global $attachments,$aids;
		if($attachments[$aid]){
			$aids[]=$aid;
			return $attachments[$aid];
		} else{
			return "[attachment=$aid]";
		}
	}

	function table($width,$unit,$text){
		global $tdcolor;

		if($width){
			$unit!='%' && $unit = 'px';
			$width = $unit == 'px' ? ($width < 600 ? $width : 600).'px' : ($width < 98 ? $width : 98).'%';
		} else{
			$width = '98%';
		}
		$table = "<table style=\"border:1px solid $tdcolor;width:$width\">";

		$text = preg_replace('/\[td=(\d{1,2}),(\d{1,2})(,(\d{1,3}%?))?\]/is','<td colspan="\\1" rowspan="\\2" width="\\4">',$text);
		$text = preg_replace('/\[tr\]/is','<tr class="tr3">',$text);
		$text = preg_replace('/\[td\]/is','<td>',$text);
		$text = preg_replace('/\[\/(tr|td)\]/is','</\\1>',$text);

		$table .= $text;
		$table .= '</table>';

		return str_replace('\\"','"',$table);
	}

	function size($size,$code,$allowsize){
		$allowsize && $size > $allowsize && $size = $allowsize;
		return "<font size=\"$size\">".str_replace('\\"','"',$code)."</font>";
	}

	function cvurl($http,$url='',$name=''){
		global $code_num,$code_htm;
		$code_num++;
		if(!$url){
			$url="<a href=\"http://www.$http\" target=\"_blank\">www.$http</a>";
		} elseif(!$name){
			$url="<a href=\"$http$url\" target=\"_blank\">$http$url</a>";
		} else{
			$url="<a href=\"$http$url\" target=\"_blank\">".str_replace('\\"','"',$name)."</a>";
		}
		$code_htm[0][$code_num]=$url;
		return "<\twind_code_$code_num\t>";
	}

	function nopic($url){
		global $code_num,$code_htm;
		$code_num++;
		$code_htm[-1][$code_num]="<img src=\"images/file/img.gif\" align=\"absbottom\" border=\"0\"> <a target=\"_blank\" href=\"$url\">img: $url</a>";
		return "<\twind_code_$code_num\t>";
	}

	function cvpic($url,$type='',$picwidth='',$picheight=''){
		global $picpath,$attachpath,$code_num,$code_htm;
		$code_num++;

		$lower_url=strtolower($url);
		if(substr($lower_url,0,4)!='http')$url="{$GLOBALS['sys']['bbs_url']}/$url";
		if(strpos($lower_url,'login')!==false && (strpos($lower_url,'action=quit')!==false || strpos($lower_url,'action-quit')!==false)){
			$url=preg_replace('/login/i','log in',$url);
		}
		if($picwidth || $picheight){
			$onload = "onload=\"";
			$picwidth  && $onload .= "if(this.width>'$picwidth')this.width='$picwidth';";
			$picheight && $onload .= "if(this.height>'$picheight')this.height='$picheight';";
			$onload .= "\"";
			$code="<img src=\"$url\" border=\"0\" onclick=\"if(this.width>=$picwidth) window.open('$url');\" $onload>";
		} else{
			$code="<img src=\"$url\" border=\"0\" onclick=\"if(this.width>screen.width-461) window.open('$url');\">";
		}
		$code_htm[-1][$code_num]=$code;

		if($type){
			return $code;
		} else{
			return "<\twind_code_$code_num\t>";
		}
	}

	function phpcode($code){
		global $phpcode_htm,$codeid;
		$code = str_replace(array("[attachment=",'\\"'),array("&#91;attachment=",'"'),$code);
		$codeid ++;
		$phpcode_htm[$codeid]="<blockquote id=\"code$codeid\">".preg_replace("/^(\<br \/\>)?(.*)/is","\\2",$code)."</blockquote>";

		return "<\twind_phpcode_$codeid\t>";
	}

	function qoute($code){
		global $code_num,$code_htm,$i_table;
		$code_num++;
		$code_htm[6][$code_num]="<h6 class=\"quote\">Quote:</h6><blockquote>".str_replace('\\"','"',$code)."</blockquote>";
		return "<\twind_code_$code_num\t>";
	}

	function post($code){
		global $code_num,$code_htm,$lang,$bbsurl;
		require_once GetLang('bbscode');
		$code_num++;
		$code_htm[3][$code_num]="<blockquote>$lang[bbcode_hide]</blockquote>";
		return "<\twind_code_$code_num\t>";
	}

	function hiden($code){
		global $code_num,$code_htm,$lang,$bbsurl;
		require_once GetLang('bbscode');
		$code_num++;
		$code_htm[4][$code_num] = "<blockquote>".$lang['bbcode_encode']."</blockquote>";
		return "<\twind_code_$code_num\t>";
	}

	function sell($code){
		global $code_num,$code_htm,$lang,$bbsurl;
		require_once GetLang('bbscode');
		$code_num++;
		$code_htm[5][$code_num]="<blockquote>".$lang['bbcode_sell']."</blockquote>";
		return "<\twind_code_$code_num\t>";
	}

	function shield($code){
		global $lang,$attachper,$groupid,$bbsurl;
		require_once GetLang('bbscode');
		$lang[$code] && $code = $lang[$code];
		$attachper = 0;
		return "<span style=\"color:black;background-color:#ffff66\">$code</span>";
	}

	function wmvplayer($wmvurl,$width='314',$height='256',$auto='1'){
		return "<div><EMBED src=\"$wmvurl\" HEIGHT=\"$height\" WIDTH=\"$width\" AutoStart=\"$auto\" ShowStatusBar=\"1\"></EMBED></div>";
	}

	function rmplayer($rmurl,$width='316',$height='241',$auto='1'){
		global $lang,$bbsurl;
		require_once GetLang('bbscode');
		return "<object classid=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA height=\"$height\" id=Player width=\"$width\" VIEWASTEXT><param name=\"_ExtentX\" value=\"12726\"><param name=\"_ExtentY\" value=\"8520\"><param name=\"AUTOSTART\" value=\"0\"><param name=\"SHUFFLE\" value=\"0\"><param name=\"PREFETCH\" value=\"0\"><param name=\"NOLABELS\" value=\"0\"><param name=\"CONTROLS\" value=\"ImageWindow\"><param name=\"CONSOLE\" value=\"_master\"><param name=\"LOOP\" value=\"0\"><param name=\"NUMLOOP\" value=\"0\"><param name=\"CENTER\" value=\"0\"><param name=\"MAINTAINASPECT\" value=\"$rmurl\"><param name=\"BACKGROUNDCOLOR\" value=\"#000000\"></object><br><object classid=clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA height=32 id=Player2 width=\"$width\" VIEWASTEXT><param name=\"_ExtentX\" value=\"18256\"><param name=\"_ExtentY\" value=\"794\"><param name=\"AUTOSTART\" value=\"$auto\"><param name=\"SHUFFLE\" value=\"0\"><param name=\"PREFETCH\" value=\"0\"><param name=\"NOLABELS\" value=\"0\"><param name=\"CONTROLS\" value=\"controlpanel\"><param name=\"CONSOLE\" value=\"_master\"><param name=\"LOOP\" value=\"0\"><param name=\"NUMLOOP\" value=\"0\"><param name=\"CENTER\" value=\"0\"><param name=\"MAINTAINASPECT\" value=\"0\"><param name=\"BACKGROUNDCOLOR\" value=\"#000000\"><param name=\"SRC\" value=\"$rmurl\"></object><br><script language=javascript>function FullScreen(){document.Player.SetFullScreen();}</script><input type='button' onclick='javascript:FullScreen()' value='$lang[full_screen]'>";
	}

	function postcache($key){
		global $sys,$act;
		require(D_P.'data/cache/bbs_cache.php');
		!$face[$key] && $face[$key] = current($face);
		if($face[$key][2]){
			return "<br /><img src=\"$sys[bbs_url]/$sys[bbs_picpath]/post/smile/{$face[$key][0]}\" /><br />[<font color=red><b>$act</b></font>] {$face[$key][2]}<br />";
		} else{
			return "<img src=\"$sys[bbs_url]/$sys[bbs_picpath]/post/smile/{$face[$key][0]}\" />";
		}
	}

	function payto($code){
		global $lang,$bbsurl;
		require_once GetLang('bbscode');
		$tmp          = substr($code,strpos($code,'(seller)')+8);
		$seller       = str_replace(array('[email]','[/email]'),'',substr($tmp,0,strpos($tmp,'(/seller)')));
		$tmp          = substr($code,strpos($code,'(subject)')+9);
		$subject      = substr($tmp,0,strpos($tmp,'(/subject)'));
		$tmp          = substr($code,strpos($code,'(body)')+6);
		$body         = substr($tmp,0,strpos($tmp,'(/body)'));
		$tmp          = substr($code,strpos($code,'(price)')+7);
		$price        = substr($tmp,0,strpos($tmp,'(/price)'));
		$tmp          = substr($code,strpos($code,'(ordinary_fee)')+14);
		$ordinary_fee = substr($tmp,0,strpos($tmp,'(/ordinary_fee)'));
		$tmp          = substr($code,strpos($code,'(express_fee)')+13);
		$express_fee  = substr($tmp,0,strpos($tmp,'(/express_fee)'));
		$tmp          = substr($code,strpos($code,'(contact)')+9);
		$contact      = substr($tmp,0,strpos($tmp,'(/contact)'));
		$tmp          = substr($code,strpos($code,'(demo)')+6);
		$demo         = substr($tmp,0,strpos($tmp,'(/demo)'));
		$tmp          = substr($code,strpos($code,'(method)')+8);
		$method       = substr($tmp,0,strpos($tmp,'(/method)'));

		$body=str_replace('\"','"',$body);
		$str = '<br>';
		$seller       && $str .= "$lang[seller]$seller<br><br>";
		$subject      && $str .= "$lang[subject]$subject<br><br>";
		$body         && $str .= "$lang[body]$body<br><br>";
		$price        && $str .= "$lang[price]$price<br><br>";
		if(($ordinary_fee || $express_fee) && $method=='2'){
			$str .= $lang['postage'];
			$ordinary_fee && $str .= "$lang[ordinary_fee]$ordinary_fee&nbsp; ";
			$express_fee  && $str .= "$lang[express_fee]$express_fee";
			$str .= "<br><br>";
		}else{
			$str .= "$lang[postage_seller]<br><br>";
		}
		$contact      && $str .= "$lang[contact]$contact<br><br>";
		$demo         && $str .= "$lang[demo]$demo<br><br>";
		$body = substrs(str_replace('<br>',"\n",$body),100);
		if($method==1){
			$str .= "<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=".rawurlencode(str_replace('&#46;','.',$seller))."&item_name=".rawurlencode($subject)."&item_number=phpw*&amount=$price&no_shipping=0&no_note=1&currency_code=CNY&notify_url=http://www.phpwind.com/pay/payto.php?date=".$_SERVER['HTTP_HOST'].get_date(time(),'-YmdHis')."&bn=phpwind&charset=$db_charset' target='_blank'><img src='$imgpath/paypal.gif'></a>";
		}elseif($method==2){
			$str .= "<a href='https://www.alipay.com/payto:$seller?subject=".rawurlencode($subject)."&body=".rawurlencode($body)."&price=$price&ordinary_fee=$ordinary_fee&express_fee=$express_fee&partner=8868&readonly=true' target='_blank'><img src='$imgpath/alipay.gif'></a>";
		}elseif($method==3){
			$str.="<a href='https://www.99bill.com/paylink/intialPaylinkIndexForw.do?pay=".rawurlencode(str_replace('&#46;','.',$seller))."&dealAmount=$price' target='_blank'><img src='$imgpath/99bill.gif'></a>";
		}
		return $str;
	}
}
?>