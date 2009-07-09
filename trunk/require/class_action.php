<?php
!defined('IN_CMS') && die('Forbidden');

require_once(D_P.'data/cache/cate.php');
require_once(D_P.'data/cache/field.php');
require_once(R_P.'require/class_content.php');

/**
 * VeryCMS的最主要的类之一：操作类
 * 本类负责所有内容的群体/单体操作管理：发布静态、更新静态、删除静态等
 *
 * @copyright PHPWind
 * @author Aileenguan
 */
class Action
{
	var $tids;
	var $fromCid;
	var $toCid;
	var $table;
	var $mid;
	var $method;
	var $catedb;

	/**
	 * 集体操作的构造函数
	 *
	 * @param string $method
	 */
	function __construct($method){ //PHP5 操作类型
		global $catedb;
		set_time_limit(0);
		$this->catedb = $catedb;
		$this->method = $method;
	}

	function Action($method){ //PHP4
		$this->__construct($method);
	}

	/**
	 * 设置当前操作的栏目
	 *
	 * @param integer $fromCid
	 */
	function cate($fromCid){
		$this->fromCid = intval($fromCid);
		if(!$this->catedb[$this->fromCid]) Showmsg('action_fromciderror');
	}

	/**
	 * 设置目标操作栏目
	 *
	 * @param integer $toCid
	 */
	function target($toCid){
		$this->toCid = (int)$toCid;
		if($tocid && !$this->catedb[$this->toCid]) Showmsg('action_tociderror');
	}

	/**
	 *  检验内容模型是否合法
	 *
	 */
	function checkMid(){
		if($this->method=='usegather' || $this->method=='delgather'){
			global $db;
			$gid = $GLOBALS['gid'];
			!$gid && Showmsg('nofromcid');
			$rs = $db->get_one("SELECT mid FROM cms_gather WHERE gid='$gid'");
			$fromMid = $rs['mid'];
		}else{
			$fromMid = $this->catedb[$this->fromCid]['mid'];
		}
		if(in_array($this->method,array('move','copy','usegather'))){
			!$this->toCid && Showmsg('action_notoCid');
			$this->fromCid == $this->toCid && Showmsg('action_sameCid');
			if($fromMid	!= $this->catedb[$this->toCid]['mid']) Showmsg('action_differentmid');
		}
		$this->mid = $fromMid;
		if(!$this->mid) Showmsg('action_miderror');
		$bbsblogAction = array('publist','pubview','pubupdate','pubcancel');
		if($this->mid < 0 && !in_array($this->method,$bbsblogAction)){
			Showmsg('action_bbsorblog');
		}elseif($this->mid > 0){
			$this->table = 'cms_content'.$this->mid;
		}
	}

	/**
	 * 根据传入的要操作的tid数组，判断动作进行具体操作
	 *
	 * @param array $tids
	 */
	function doIt($tids=''){
		if($this->method!='pubindex'){
			$this->checkMid();
			if(!is_array($tids)){//如果传入的数据不是数组，则数组化之
				$tids = array((int)$tids);
			}
			sort($tids);
			$this->tids = $tids;
		}
		switch ($this->method){
			case 'move':
				$this->move();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'destroy':
				$this->delete(1);
				break;
			case 'copy':
				$this->copy();
				break;
			case 'pubview':
				$this->pubview();
				break;
			case 'pubindex':
				$this->pubindex();
				break;
			case 'publist':
				return $this->publist();
				break;
			case 'pubcancel':
				$this->pubcacel();
				break;
			case 'pubupdate':
				$this->pubupdate();
				break;
			case 'usegather':
				$this->usegather();
				break;
			case 'delgather':
				$this->delgather();
				break;
			case 'batchtag':
				$this->batchtag();
				break;
			default:
				Showmsg('undefined_request');
				break;
		}
	}

	/**
	 * 群体移动内容
	 *
	 */
	function move(){
		global $db,$very;
		set_time_limit(0);
		$this->siftTid();
		$tids = implode(',',$this->tids);
		$rs = $db->query("SELECT url,fpageurl,ifpub FROM cms_contentindex WHERE tid IN($tids) AND cid='$this->fromCid'");
		$url = array();
		$total = $new = 0;
		while ($p = $db->fetch_array($rs)){
			if($p['ifpub']==1 && $p['url']){ //记录已经发布者，若已生成静态，则删除之
				$this->unlinkHtml($p['url'],$p['fpageurl']);
			}elseif($p['ifpub']==0){
				$new++;
			}
			$total++;
		}
		$db->update("UPDATE cms_category SET total=total-$total,new=new-$new WHERE cid='$this->fromCid'");
		$db->update("UPDATE cms_category SET total=total+$total,new=new+$total WHERE cid='$this->toCid'");
		$db->update("UPDATE cms_contentindex SET cid='$this->toCid',url='',ifpub=0 WHERE tid IN($tids)"); //移动文章之后将删除旧有url，移动之后所有文章默认为未发布
	}

	/**
	 * 群体删除内容
	 *
	 */
	function delete($destroy=0){
		global $very,$db,$_OUTPUT,$bbs,$blog,$cms;
		set_time_limit(0);
		$this->siftTid();
		$cid = $this->fromCid;
		$tids = implode(',',$this->tids);
		$rs = $db->query("SELECT url,ifpub,fpageurl FROM cms_contentindex WHERE tid IN($tids) AND cid='$this->fromCid'");
		$url = array();
		$new = 0;
		$total = 0;
		while($p = $db->fetch_array($rs)){
			if($p['ifpub']==1 && $p['url']){
				$this->unlinkHtml($p['url'],$p['fpageurl']);
			}elseif ($p['ifpub']==0){
				$new++;
			}
			$total++;
		}
		$firsttid = reset($this->tids);
		$lasttid = end($this->tids);
		$tidnum=intval($lasttid)-intval($firsttid)+3;
		$begin = $db->get_one("SELECT tid FROM cms_contentindex WHERE cid='$cid' AND ifpub='1' AND tid<'$firsttid' ORDER BY tid DESC LIMIT 1");
		$begintid = $begin ? $begin['tid'] : $firsttid;
		$rs = $db->query("SELECT url,tid,ifpub,fpage,linkurl,fpageurl FROM cms_contentindex WHERE cid='$cid' AND ifpub='1' AND tid>='$begintid' OR tid IN('$tids') ORDER BY tid LIMIT $tidnum");

		$flag = "";
		$refurbish = array();

		while ($p = $db->fetch_array($rs)){
			if(in_array($p[tid],$this->tids)){
				$flag=1;
				if($endtid){
					$refurbish[]	= $endtid;
					$endtid = "";
				}
			}else{
				if($flag==1){
					$refurbish[] = $p;
				}else{
					$endtid = $p;
				}
				$flag	= 0;
			}
			if($breaknumber) break;
			if($p[tid]==$lasttid) $breaknumber=1;
		}
		if($destroy){
			$db->update("DELETE FROM $this->table WHERE tid IN($tids)");
			$db->update("DELETE FROM cms_contentindex WHERE tid IN($tids)");
			$db->update("DELETE FROM cms_attachindex WHERE tid IN($tids)");
			$db->update("DELETE FROM cms_contenttag WHERE tid IN($tids)");
		}else{
			$db->update("UPDATE cms_contentindex SET cid='-1',ifpub='0' WHERE tid IN($tids)");
			foreach($this->tids as $t){
				$sqladd .= ($sqladd ? ',' : '')."('$t','$cid','$GLOBALS[timestamp]','$GLOBALS[admin_name]')";
			}
			$sqladd && $db->update("INSERT INTO cms_recycle(tid,cid,deltime,admin) VALUES $sqladd");
		}
		$db->update("UPDATE cms_category SET total=total-$total,new=new-$new WHERE cid='$this->fromCid'");

		extract($GLOBALS, EXTR_SKIP);
		if($this->catedb[$cid]['type']==0 || !$this->catedb[$cid]['htmlpub']) return ;

		$mid = $this->mid;
		if ($mid<0){
			$this->pubview();
			return ;
		}
		if($this->catedb[$cid]['htmlpub']){
			$filepath = $this->htmlDir($cid);
			foreach($refurbish as $p){
				if(!$p['ifpub'] || $p['linkurl']) continue;
				if(empty($p['url'])){
					$p['url'] = $filepath.'/'.$p['tid'].'.'.$very['htmext'];
				}
				$urlinfo = pathinfo($p['url']);
				$old_dir = current(explode('/',$p['url']));
				$new_dir = current(explode('/',$filepath));
				$this->unlinkHtml($p['url'],$p['fpageurl']);
				if($old_dir!=$new_dir){
					$p['url'] = $filepath.'/'.$urlinfo['basename'];
				}else{
					$this->checkHtmlDir($very['htmdir'].'/'.$urlinfo['dirname']);
				}
				$fpageurl = $this->createHtml($p['tid'],$cid,$p['url'],$p['fpage']);
				$db->update("UPDATE cms_contentindex SET url='$p[url]',fpageurl='$fpageurl' WHERE tid='$p[tid]'");
			}
		}else{
			$db->update("UPDATE cms_contentindex SET url='',fpageurl='' WHERE tid IN ($tids)");
		}
	}

	/**
	 * 群体复制内容
	 *
	 */
	function copy(){
		global $db;
		set_time_limit(0);
		$this->siftTid();
		$content = new Content($this->mid);
		foreach ($this->tids as $tid){
			$rs = $db->get_one("SELECT i.*,c.* FROM cms_contentindex i LEFT JOIN $this->table c USING(tid) WHERE i.tid='$tid'");
			Add_S($rs);
			$content->InsertData($rs,$this->toCid);
		}
	}

	/**
	 * 采集入库
	 *
	 */
	function usegather(){
		global $db;
		$this->siftTid();
		$num = count($this->tids);
		$tids = implode(',',$this->tids);
//		$db->update("UPDATE cms_contentindex SET cid='$this->toCid' WHERE tid IN($tids) AND cid=0");
		$db->update("UPDATE cms_contentindex a LEFT JOIN cms_collection b USING(tid) SET a.cid='$this->toCid',a.postdate=b.gathertime WHERE a.tid IN($tids) AND a.cid=0");
		$db->update("UPDATE cms_category SET new=new+$num,total=total+$num WHERE cid='$this->toCid'");
	}

	/**
	 * 采集删除
	 *
	 */
	function delgather(){
		global $db;
		$this->siftTid();
		$tids = implode(',',$this->tids);
		$db->update("DELETE FROM $this->table WHERE tid IN($tids)");
		$db->update("DELETE FROM cms_collection WHERE tid IN($tids)");
		$db->update("DELETE FROM cms_contentindex WHERE tid IN($tids) AND cid=0");
	}

	#############################生成静态的操作方法######################

	/**
	 * 发布整站首页
	 *
	 */
	function pubindex(){
		global $fielddb,$moduledb,$catedb,$very,$db,$_OUTPUT,$bbs,$blog,$cms,$extend;
		extract($GLOBALS, EXTR_SKIP);
		$_OUTPUT = '';
		require(R_P.'index.php');
		if ($very['indexupdate']){
			$_OUTPUT .= "\n<script src='update.php?type=index'></script>";
		}
		if(!writeover(R_P.'index.'.$very['htmext'],$_OUTPUT)){
			Showmsg('pub_writeindexfail');
		}

	}

	/**
	 * 发布栏目首页
	 *
	 */
	function publist(){
		global $fielddb,$moduledb,$catedb,$very,$_OUTPUT,$db,$page,$bbs,$blog,$cms,$extend;
		set_time_limit(0);
		extract($GLOBALS, EXTR_SKIP);
		$cid = $this->fromCid;
		if($this->catedb[$cid]['type']==0) return ; //此为不需要发布的栏目
		$_OUTPUT = '';
		if($this->catedb[$cid]['listpub']){ //如果发布静态
			if($this->catedb[$cid]['path']){
				$filepath = $this->catedb[$cid]['path'].'/';
			}else {
				$filepath = $cid.'/';
			}
			$this->checkHtmlDir($very['htmdir'].'/'.$filepath);
			$listurl = $very['htmdir'].'/'.$filepath.'index.'.$very['htmext'];
			$pageurl = $listurl; //首页地址
			/* 列表页自动分页处理 */
			if(!is_numeric($page) || $page<=0){
				$page = 1;
			}
			$page = intval($page);
			if($page > 1){
				$listurl = $very['htmdir'].'/'.$filepath.'index_'.$page.'.'.$very['htmext'];
			}
			require(R_P.'list.php');
			if($this->catedb[$cid]['autoupdate']){
				$_OUTPUT .= "\n<script src='update.php?type=list&cid=$cid&page=$page'></script>";
			}
			if(!writeover(D_P.$listurl,$_OUTPUT)){
				Showmsg('pub_writehtmlfail');
			}
			if(is_object($cms) && $cms->autoRun==1){
				$page++;
				return $page;
			}
		}
		return ;
	}

	/**
	 * 发布内容页面
	 *
	 */
	function pubview(){
		global $fielddb,$moduledb,$catedb,$very,$_OUTPUT,$db,$timestamp,$bbs,$blog,$cms,$extend;
		set_time_limit(0);
		$this->siftTid();
		extract($GLOBALS, EXTR_SKIP);
		!$very['htmext'] && $very['htmext']='html';
		$cid	= $this->fromCid;
		$mid	= $this->mid;
		//栏目静态发布目录
		$filepath = $this->htmlDir($cid);
		if($mid > 0){
			$total	= 0;
			$tids	= implode(',',$this->tids);
			$rs		= $db->query("SELECT tid,linkurl,ifpub,fpage FROM cms_contentindex WHERE tid IN($tids) AND cid='$this->fromCid' AND ifpub<>1"); //ifpub<>1 只操作未发布的
			$db->update("UPDATE cms_contentindex SET ifpub=1 WHERE tid IN($tids) AND cid='$this->fromCid' AND ifpub=0");
			$articles = array();
			$htmltids = '';
			while ($p = $db->fetch_array($rs)) {
				$tid = $p['tid'];
				if($this->catedb[$cid]['htmlpub'] && !$p['linkurl'] && $this->catedb[$cid]['type']){
					$htmltids .= $htmltids ? ','.$tid:$tid;
				}
				$articles[$tid] = $p;
			}
			if($htmltids) {
				$db->update("UPDATE cms_contentindex SET url=CONCAT('$filepath','/',tid,'.','$very[htmext]') WHERE tid IN($htmltids) AND cid='$this->fromCid'");
			}
			foreach($articles as $tid=>$p) {
				$filename = $fpageurl = '';
				if($this->catedb[$cid]['htmlpub'] && !$p['linkurl'] && $this->catedb[$cid]['type']){
					//如果本栏目生成静态且该内容不是一个外部链接
					$filename = $filepath.'/'.$tid.'.'.$very['htmext'];
					$fpageurl = $this->createHtml($tid,$cid,$filename,$p['fpage']);
				}
				$db->update("UPDATE cms_contentindex SET url='$filename',ifpub=1,fpageurl='$fpageurl' WHERE tid='$p[tid]'");
				$total++;
			}
			$db->update("UPDATE cms_category SET new=new-$total WHERE cid='$cid'");
		}elseif ($mid < 0){
//			foreach($this->tids as $tid) {
//				$filename = D_P.$very['htmdir'].'/'.$filepath.$tid.'.'.$very['htmext'];
//				if(!writeover($filename,"")){
//					return false;
//				}
//			}
			foreach ($this->tids as $tid){
				$filename = $filepath.'/'.$tid.'.'.$very['htmext'];
				$this->createHtml($tid,$cid,$filename);
			}
		}
	}

	/**
	 * 更新已经发布的内容
	 *
	 */
	function pubupdate(){
		global $db,$very,$_OUTPUT,$bbs,$blog,$cms,$extend;
		$this->siftTid();
		extract($GLOBALS, EXTR_SKIP);
		$tids = implode(',',$this->tids);
		$cid = $this->fromCid;
		if($this->catedb[$cid]['type']==0 || !$this->catedb[$cid]['htmlpub']) return ;
		//此为不需要发布页面的栏目
		$mid = $this->mid;
		if($mid<0){
			if($this->catedb[$cid]['htmlpub']){
				$filepath = $this->htmlDir($cid);
				foreach ($this->tids as $tid){
					$filename = $filepath.'/'.$tid.'.'.$very['htmext'];
					$this->unlinkHtml($filename);
				}
			}
			$this->pubview();
			return ;
		}
		if($this->catedb[$cid]['htmlpub']){
			$filepath = $this->htmlDir($cid);
			$rs = $db->query("SELECT url,tid,ifpub,fpage,linkurl,fpageurl FROM cms_contentindex WHERE tid IN ($tids)");
			while ($p = $db->fetch_array($rs)){
				if($p['ifpub']!='1' || $p['linkurl']) continue;//要把外部链接内容和未发布的内容排除在外
				if(empty($p['url'])){ //过去不存在文件地址，则要新构造
					$p['url'] = $filepath.'/'.$p['tid'].'.'.$very['htmext'];
				}
				$urlinfo = pathinfo($p['url']);
				$old_dir = current(explode('/',$p['url']));
				$new_dir = current(explode('/',$filepath));
				$this->unlinkHtml($p['url'],$p['fpageurl']); //删除旧文件以便重新生成
				if($old_dir!=$new_dir){ //说明栏目静态文件发布点已经改变
					$p['url'] = $filepath.'/'.$urlinfo['basename']; //新的文件地址
				}else{ //发布点未改变时检查其目录是否还存在
					$this->checkHtmlDir($very['htmdir'].'/'.$urlinfo['dirname']);
				}
				$fpageurl = $this->createHtml($p['tid'],$cid,$p['url'],$p['fpage']);

				$db->update("UPDATE cms_contentindex SET url='$p[url]',fpageurl='$fpageurl' WHERE tid='$p[tid]'");
			}

		}else{
			$db->update("UPDATE cms_contentindex SET url='',fpageurl='' WHERE tid IN ($tids)");
		}
	}

	/**
	 * 根据路径来生成静态文件
	 *
	 * @param integer $tid
	 * @param string $url
	 * @param boolean $fpage 是否需要多步操作
	 * @return string $fpageurl 多页地址
	 */
	function createHtml($tid,$cid,$url,$fpage=0){
		global $very,$_OUTPUT;
		extract($GLOBALS, EXTR_SKIP);
		$page	= intval($page);
		$script	= "\n<script src=\"update.php?type=click&tid=$tid&cid=$cid\"></script>";
		if ($fpage){ //如果是需要进行分页处理的内容
			$page		= 1;
			$fpageurl	= array();
			$contentUrl	= $very['htmdir'].'/'.$url;
			do{
				$_OUTPUT='';
				require(R_P.'view.php');
				$_OUTPUT.=$script;
				if(!writeover(D_P.$cont->pageurl,$_OUTPUT)){
					Showmsg('pub_writehtmlfail');
				}
				$page++;
				$fpageurl[]=$cont->pageurl; //获取到每一页的地址并保存起来
			}while ($cont->autoRun==1);
			$fpageurl = implode('|',$fpageurl);
		}else{ //不需要进行分页处理的内容
			$_OUTPUT='';
			require(R_P.'view.php');
			$_OUTPUT.=$script;
			if(!writeover(D_P.$very['htmdir'].'/'.$url,$_OUTPUT)){
				Showmsg('pub_writehtmlfail');
			}
		}
		return $fpageurl; //将多页地址返回
	}

	/**
	 * 删除已生成的内容HTML文件
	 *
	 * @param string $url
	 * @param string $urls
	 */
	function unlinkHtml($url,$urls){
		global $very;
		if($urls){ //删除多页
			foreach (explode('|',$urls) as $url){
				if(empty($url)) continue;
//				$url = D_P.$very['htmdir'].'/'.$url;
				$url = D_P.$url;
				unlink($url);
			}
		}else{
			if(empty($url)) return ;
			$url = D_P.$very['htmdir'].'/'.$url;
			unlink($url);
		}
	}

	/**
	 * 取消已发布内容
	 *
	 */
	function pubcacel(){
		global $db,$very;
		$this->siftTid();
		$tids = implode(',',$this->tids);
		$mid = $this->mid;
		$cid = $this->fromCid;
		if($mid<0){
			$filepath = $this->htmlDir($cid);
			foreach ($this->tids as $tid){
				$filename = $filepath.'/'.$tid.'.'.$very['htmext'];
				$this->unlinkHtml($filename);
			}
		} else{
			$rs = $db->query("SELECT url,tid,ifpub,fpageurl FROM cms_contentindex WHERE tid IN ($tids)");
			$new=0;
			while ($p = $db->fetch_array($rs)){
				if(!$p['ifpub']) continue;
				$this->unlinkHtml($p['url'],$p['fpageurl']);
				$new++;
			}
			$db->update("UPDATE cms_contentindex SET ifpub=0,url='' WHERE tid IN($tids)");
			$db->update("UPDATE cms_category SET new=new+$new WHERE cid='$this->fromCid'");
		}
	}

	/**
	 * 获取栏目html文件存储目录
	 *
	 * @param integer $cid
	 * @return string
	 */
	function htmlDir($cid){
		global $very,$timestamp;
		if($this->catedb[$cid]['path']){
			$filepath = $this->catedb[$cid]['path'].'/';
		}else {
			$filepath = $cid.'/';
		}
		if($this->mid<0){
			$this->checkHtmlDir($very['htmdir'].'/'.$filepath);
			return $filepath;
		}
		//BBS,Blog调用类不生成分目录，因为没有数据入库

		switch ($very['htmmkdir']){
			case 1:
				$mk = get_date($timestamp,'Y');
				break;
			case 2:
				$mk = get_date($timestamp,'Y-m');
				break;
			case 3:
				$mk = get_date($timestamp,'y-m-d');
				break;
			case 4:
				if($this->catedb[$cid]['path']) {
					$filepath = $this->catedb[$cid]['path'].'/'.get_date($timestamp,'Y-m');
				}else {
					$filepath = "00".$cid.get_date($timestamp,'ym');
				}
				break;
			default:
				$mk= get_date($timestamp,'y-m-d');
				break;
		}
		$filepath .= $mk;
		$this->checkHtmlDir($very['htmdir'].'/'.$filepath);
		/*		if(!is_dir(R_P.$very['htmdir'].'/'.$filepath)){
		mkdir(R_P.$very['htmdir'].'/'.$filepath);
		chmod(R_P.$very['htmdir'].'/'.$filepath,0777);
		}*/
		return $filepath;
	}

	/**
	 * 创建文件目录，对Html文件的目录进行判断，如果不存在，一级一级重新创建
	 *
	 * @param string $path
	 */
	function checkHtmlDir($path)
	{
		if(is_dir(R_P.$path)) return ;
		$dirs = explode('/',$path);
		$dirpath = '';
		while ($directory = array_shift($dirs)) {
			$dirpath .='/'.$directory;
			if(!is_dir(R_P.$dirpath)){
				mkdir(R_P.$dirpath);
				if(!chmod(R_P.$dirpath,0777)){
					Showmsg('pub_mkdirfail');
				}
			}
		}
	}

	/**
	 * 本方法对传递过来的Tid进行过滤筛选
	 *
	 */
	function siftTid(){
		$tids = array();
		foreach ($this->tids as $tid){
			if(!is_numeric($tid)) continue;
			if($tid<=0) continue;
			$tids[] = (int)$tid;
		}
		if(count($tids)==0) Showmsg('noinvaliddata'); //无有效数据
		$this->tids = $tids;
	}
/* 涉及到服务器负载问题，本功能不再使用
	function pubreup(){
		global $very,$db,$_OUTPUT,$bbs,$blog,$cms;
		$mid = $this->mid;
		if ($mid<0) {
			return ;
		}
		$cid = $this->fromCid;
		$tids = implode(',',$this->tids);
		$firsttid = reset($this->tids);
		$lasttid = end($this->tids);
		$tidnum=intval($lasttid)-intval($firsttid)+3;
		$begin = $db->get_one("SELECT tid FROM cms_contentindex WHERE cid='$cid' AND ifpub='1' AND tid<'$firsttid' ORDER BY tid DESC LIMIT 1");
		$begintid= $begin ? $begin['tid'] : $firsttid;
		$rs = $db->query("SELECT url,tid,ifpub,fpage,linkurl,fpageurl FROM cms_contentindex WHERE cid='$cid' AND ifpub='1' AND tid>='$begintid' OR tid IN('$tids') ORDER BY tid LIMIT $tidnum");

		$flag = "";
		$refurbish = array();

		while ($p = $db->fetch_array($rs)){
			if(in_array($p[tid],$this->tids)){
				$flag=1;
				if($endtid){
					$refurbish[]	= $endtid;
					$endtid = "";
				}
			}else{
				if($flag==1){
					$refurbish[] = $p;
				}else{
					$endtid = $p;
				}
				$flag	= 0;
			}
			if($breaknumber) break;
			if($p[tid]==$lasttid) $breaknumber=1;
		}
		extract($GLOBALS, EXTR_SKIP);
		if($this->catedb[$cid]['type']==0 || !$this->catedb[$cid]['htmlpub']) return ;
		if($this->catedb[$cid]['htmlpub']){
			$filepath = $this->htmlDir($cid);

			foreach($refurbish as $p){
				if(!$p['ifpub'] || $p['linkurl']) continue;
				if(empty($p['url'])){
					$p['url'] = $filepath.'/'.$p['tid'].'.'.$very['htmext'];
				}
				$urlinfo = pathinfo($p['url']);
				$old_dir = current(explode('/',$p['url']));
				$new_dir = current(explode('/',$filepath));
				$this->unlinkHtml($p['url'],$p['fpageurl']);
				if($old_dir!=$new_dir){
					$p['url'] = $filepath.'/'.$urlinfo['basename'];
				}else{
					$this->checkHtmlDir($very['htmdir'].'/'.$urlinfo['dirname']);
				}
				$fpageurl = $this->createHtml($p['tid'],$cid,$p['url'],$p['fpage']);
				$db->update("UPDATE cms_contentindex SET url='$p[url]',fpageurl='$fpageurl' WHERE tid='$p[tid]'");
			}
		}else{
			$db->update("UPDATE cms_contentindex SET url='',fpageurl='' WHERE tid IN ($tids)");
		}

	}
*/

	function batchtag(){
		global $db,$tagsid;
		$this->siftTid();
		if(empty($tagsid)){
			return null;
		}
		foreach($tagsid as $tagid){
			$rs = $db->query("SELECT tid FROM cms_contenttag WHERE mid='$this->mid' AND tagid='$tagid'");
			$old_tid_array = $old_tid = $result_tid = array();
			while ($old_tid = $db->fetch_array($rs)) {
				$old_tid_array[] = $old_tid['tid'];
			}
			$result_tid = array_diff ($this->tids,$old_tid_array);
			if(count($result_tid)==0) continue;
			$sql = '';
			$i = 0;
			$sql = 'INSERT INTO cms_contenttag(tagid,tid,mid) VALUES';
			foreach($result_tid as $tid){
				$sql.="(".$tagid.",".$tid.",".$this->mid."),";
				$i++;
			}
			$sql = substr($sql,0,strlen($sql)-1);
			$db->update("$sql");
			$db->update("UPDATE cms_tags SET num=num+'$i' WHERE tagid='$tagid'");

		}
		Cache::writeCache('tags');
	}

}
?>