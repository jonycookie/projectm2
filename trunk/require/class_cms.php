<?php
!defined('IN_CMS') && die('Forbidden');
require_once(D_P.'data/cache/cate.php');
require_once(D_P.'data/cache/field.php');
$very['aggreblog'] && require_once(R_P.'require/class_blog.php');
$very['aggrebbs'] &&	require_once(R_P.'require/class_bbs.php');
require_once(R_P.'require/class_ajax.php');

/**
 * VeryCMS的最主要的类之一：输出类
 * 本类负责读取所有内容
 * @example $cms->thread("mid:1;cid:1;num:1,10;where:photo!='';order:postdate DESC");
 * ;分号区别每一个参数
 * :冒号来区分条件和条件的值
 * 必需条件:	1.必须指定mid--内容模型ID	2.调用数量num必须指定
 * @copyright PHPWind
 * @author Aileenguan
 */
class Cms {

	/**
	 * 要调用的栏目ID
	 *
	 * @var array
	 * @access private
	 */
	var $cids;

	/**
	 * 内容模型ID
	 *
	 * @var integer
	 * @access private
	 */
	var $mid;

	/**
	 * bbs整合,blog整合
	 *
	 * @var object
	 * @access private
	 */
	var $bbs,$blog;

	/**
	 * 所有传递进来的参数
	 *
	 * @var Array
	 * @access private
	 */
	var $parameter;

	/**
	 * 模型缓存、栏目缓存、字段内容缓存
	 *
	 * @var array
	 * @access private
	 */
	var $catedb,$moduledb,$fielddb;

	/**
	 * 读取开始标志，每次读取数量,分页内容
	 *
	 * @var integer
	 */
	var $start,$displaynum;

	/**
	 * 分页url，倘若有传递进来此url，则按照此url进行分页处理
	 *
	 * @var string
	 * @access public
	 */
	var $pageurl; //主要用于后台查看

	var $listurl; //主要用于生成列表页分页

	/**
	 * 当前数页
	 *
	 * @var integer
	 */
	var $pageNo;

	/**
	 * 自动分页开关
	 * @var boolean
	 * @access public
	 */
	var $autopage = 0;

	/**
	 * 分页的具体内容
	 *
	 * @var string
	 * @access public
	 */
	var $page;

	/**
	 * 当前内容模型下需要操作的表
	 *
	 * @var string
	 * @access private
	 */
	var $table;

	var $autoRun;

	var $ajax;

	function __construct()
	{ //PHP5
		global $catedb,$moduledb,$fielddb;
		$this->catedb = $catedb;
		$this->moduledb = $moduledb;
		$this->fielddb = $fielddb;
	}

	function Cms(){ //PHP4
		$this->__construct();
	}

	function selectTable(){
		$this->table='cms_content'.$this->mid;
	}


	/**
	 * 本方法对传递进来的参数进行解析并根据内容模型来返回相应的信息
	 *
	 * @param string $condition
	 * @return array
	 */
	function thread($condition){
		//初始化
		global $page,$listnum;
		$page = $page?$page:intval(GetGP('page'));
		$this->autoPage=0;
		$this->cids='';
		$this->page='';
		$this->parameter = array();
		$this->mid='';

		$conditionMD5 = strtolower($condition);
		$condition = explode(';',$conditionMD5);
		foreach ($condition as $part){
			list($key,$value) = explode(':',$part);
			$this->parameter[$key]=$value;
		}
		$allKeys = array_keys($this->parameter);

		if(!in_array('mid',$allKeys) && !in_array('cid',$allKeys)) throwError('cms_nomidandcid');
		if(!in_array('num',$allKeys)) throwError('cms_nonum');
		$this->mid = intval($this->parameter['mid']);

		$sqlcache = intval($GLOBALS['very']['sqlcache'])*60;
		if($sqlcache && $this->mid>0 && strpos($this->parameter['num'],"page-")===false && (SCR == 'index' || SCR == 'view'|| !defined('IN_ADMIN'))){
			//发布首页的时候 或者 内容更新的时候
			global $sqlcachefile;
			if(!$sqlcachefile){
				$sqlcachefile = array();
			}
			$cachefile = substr(md5($conditionMD5),0,15);
			$cachefile = D_P.'data/sql/'.$cachefile.'.cache';
			if(!in_array($cachefile,$sqlcachefile)){
				$sqlcachefile[] = $cachefile;
			}
			if ($GLOBALS['timestamp'] - filemtime($cachefile) < $sqlcache){
				$cacheStr = readover($cachefile);
				$array = unserialize($cacheStr);
//				$this->page = $array['page'];
				unset($array['page']);
				return $array;
			}
		}
		!is_array($this->moduledb[$this->mid]) && throwError('cms_miderror');

		//解析分页
		if(strpos($this->parameter['num'],"page-")!==false){ //自动分页
			$displaynum = str_replace('page-','',$this->parameter['num']);
			$this->displaynum = (int)$displaynum;
			$listnum = $this->displaynum;

			$page<=0 && $page=1;
			$this->start = ($page-1)*$displaynum;
			$this->autoPage = 1; //开启自动分页
		}elseif(strpos($this->parameter['num'],",")===false){
			$this->start=0;
			$this->displaynum = (int)$this->parameter['num'];
		}else{
			list($this->start,$this->displaynum) = explode(',',$this->parameter['num']);
		}

		$this->getCid(); //设置要读取的cid

		if($this->mid ==-2) {
			$array = $this->readbbs();
		}elseif($this->mid == -1) {
			$array = $this->readblog();
		}else {
			$array = $this->readArticle();
		}
		if ($sqlcache && $array && strpos($this->parameter['num'],"page-")===false && (SCR == 'index' || SCR == 'view'|| !function_exists('adminmsg'))) {
//			$array['page'] = $this->page;
			writeover($cachefile,serialize($array));
		}
		unset($array['page']);
		return $array;
	}

	/**
	 * 根据传入的条件，解析获取到要查询的cid数组
	 *
	 */
	function getCid(){
		if($this->parameter['cid'] && !ereg("^[-,0-9(all)]+$",$this->parameter['cid'])){
			//判断参数数否合法 只能是all 数字 -组成
			throwError('cms_ciderror');
		}
		if($this->parameter['cid']){
			$thecid = $this->parameter['cid'];
			$cidsArray = explode(',',$thecid);
			if(count($cidsArray)==1){
				$this->includeChild($thecid);
			}else{
				foreach ($cidsArray as $cid){
					$this->includeChild($cid);
				}
			}
			foreach ($this->cids as $c){
				if($this->catedb[$c]['mid']!=$this->mid) unset($this->cids[$c]); //过滤掉不是此内容模型的分类
			}
		}

	}
	/**
	 * 获取列表的分类Url
	 *
	 * @param integer $cid
	 * @return string
	 */
	function getListUrl($cid){
		global $very;
		$m = $this->catedb[$cid];
		if($m['listpub'] && $m['listurl']){
			$m['url']=$m['listurl'];
		}else{
			$m['url']='list.php?cid='.$cid;
		}
		return $m['url'];
	}

	/**
	 * 对系统内嵌模型的数据进行读取
	 *
	 * @return array
	 */
	function readArticle(){ //读取文章
		global $db,$very;
		$this->selectTable();
		if($this->cids){
			$cids = implode(',',$this->cids);
			$cidAdd = " AND cid IN(". $cids .") ";
		}
		//此判断用于在后台浏览时，需要获取所有发布和未发布的文章，前台则不然
		if(function_exists('adminmsg') && $GLOBALS['adminjob']=='content' && $GLOBALS['action']=='view'){
			$ifpub = "i.ifpub>='$ifpub'";
		}else{
			$ifpub = "i.ifpub=1";
		}

		$this->query = "SELECT * FROM cms_contentindex i LEFT JOIN $this->table c USING(tid) WHERE $ifpub AND i.mid='$this->mid' ";
		if($this->parameter['tid']){
			$this->query .= " AND i.tid IN($this->parameter[tid]) ";
		}else{
			/*开始解析WHERE条件*/
			if($this->parameter['where']){
				$this->query.="AND i.".$this->parameter['where'];
			}
			if($cids){
				$this->query.=$cidAdd;
			}
			if($this->parameter['digest']){
				if(is_numeric($this->parameter['digest'])){
					$this->query.=" AND i.digest={$this->parameter['digest']}";
				}else{
					$this->query.=" AND i.digest IN({$this->parameter['digest']})"; //多种精华
				}
			}
			/*WHERE条件构造完毕*/

			$this->totalQuery = str_replace('SELECT * ','SELECT COUNT(*) AS total ',$this->query);

			//开始构造排序方法
			if($this->parameter['order']){
				$this->parameter['order'] =substr( str_replace(array(',',',i.custom-',',i.system-'),array(',i.',',c.',',i.'),','.$this->parameter['order']),1);
				$this->query.=" ORDER BY ". $this->parameter['order']." ";
			}else{
				$this->query.=" ORDER BY i.postdate DESC"; //默认是按照添加的顺序显示的
			}
			if($this->parameter['num']){
				$this->query.=" LIMIT ".$this->start.",".$this->displaynum;
			}
		}
		//排序信息构造完毕
		$rs = $db->query($this->query);

		$thread = array();
		while ($threaddb = $db->fetch_array($rs)){
			!function_exists('adminmsg') && $this->getvalue($threaddb); //某些特殊字段的值前台显示时需要处理
			$threaddb['catename'] = $this->catedb[$threaddb['cid']]['cname'];
			$threaddb['cateurl'] = $this->getListUrl($threaddb['cid']);
			if($threaddb['linkurl']){
				$threaddb['url'] = $threaddb['linkurl']; //如果存在链接
			}else{
				if($this->catedb[$threaddb['cid']]['htmlpub'] && $threaddb['url']){
					$threaddb['url']=$threaddb['ifpub']==1 ? "$very[htmdir]/$threaddb[url]" : '';
				}else{
					$threaddb['url']=$threaddb['ifpub']==1 ? "view.php?tid=".$threaddb['tid']."&cid=".$threaddb['cid'] : '';
				}
			}
			$titlestyle = unserialize($threaddb['titlestyle']);
			$threaddb['titlestyle'] = '';
			$titlestyle['titlecolor'] && $threaddb['titlestyle'] .= "color:$titlestyle[titlecolor];";
			$titlestyle['titleb'] && $threaddb['titlestyle'] .= 'font-weight:bold;';
			$titlestyle['titleii'] && $threaddb['titlestyle'] .= 'font-style:italic;';
			$titlestyle['titleu'] && $threaddb['titlestyle'] .= 'text-decoration:underline;';
			$titlestyle['titlebgcolor'] && $threaddb['titlestyle'] .= "background-color:$titlestyle[titlebgcolor];";
			$thread[] = $threaddb;
		}
		$this->autoPage && $this->countPage();
		return $thread;
	}

	/**
	 * 获取内容模型中的CheckBox Select Radio等类型的值
	 *
	 * @param Array $array
	 */
	function getvalue(&$array)
	{
		foreach ($this->fielddb[$this->mid] as $val)
		{
			if($val['inputtype']=='select' || $val['inputtype']=='radio')
			{
				$defaultlabel = explode('|',$val['inputlabel']);
				$defaultvalue = explode('|',$val['defaultvalue']);
				$key_pos = array_search($array[$val['fieldid']],$defaultvalue);
				$array[$val['fieldid']] = $defaultlabel[$key_pos];
			}
			elseif ($val['inputtype']=='checkbox')
			{
				$defaultlabel = explode('|',$val['inputlabel']);
				$defaultvalue = explode('|',$val['defaultvalue']);
				$values = explode(',',$array[$val['fieldid']]);
				$newvalue = array();
				foreach ($values as $v)
				{
					if(!$v) continue;
					$key_pos = array_search($v,$defaultvalue);
					$newvalue[] = $defaultlabel[$key_pos];
				}
				$array[$val['fieldid']] = implode(',',$newvalue);
			}
		}
	}

	/**
	 * 读取bbs整合模块
	 *
	 * @return array
	 */
	function readbbs(){ //读取bbs板块
		global $bbs;
		if (!$GLOBALS['very']['aggrebbs']) {
			return array();
		}
		$bbsCids = explode(',',$this->parameter['cid']);
		$bbsCid	= $bbsCids[0];
		$bbsCid = str_replace('all-','',$bbsCid); //bbs不支持此类调用方法
		$bbsCid = intval($bbsCid);
		//!is_numeric($this->parameter['cid']) && throwError('cms_cidnum');
		if(!is_object($this->bbs)){
			if(is_object($bbs)){
				$this->bbs = $bbs;
			}else{
				$bbs = $this->bbs = newBBS($GLOBALS['very']['bbs_type']);
			}
		}
		$this->bbs->onlyimg=0; //允许只调用有图片的文章
		if($this->parameter['where']=="photo!=''"){
			$this->bbs->onlyimg=1;
		}
		$rs = $this->catedb[$bbsCid];
		if(!$rs) throwError('bbs_nocondition');
		$addtion = unserialize(stripslashes($rs['addtion']));
		if($addtion['viewtype']){
			if ($this->catedb[$bbsCid]['path']) {
				$this->bbs->viewtype = $this->catedb[$bbsCid]['path'];
			}else{
				$this->bbs->viewtype = $bbsCid;
			}
		}else{
			$this->bbs->viewtype = 0;
		}
		$this->bbs->cid = $bbsCid;
		$this->bbs->readConfig($addtion);
		$thread = $this->bbs->getThread($this->start,$this->displaynum);
		$this->autoPage && $this->countPage();
		return $thread;
	}

	/**
	 * 读取blog整合模块
	 *
	 */
	function readblog()
	{
		global $blog;
		if (!$GLOBALS['very']['aggreblog'])
		{
			return array();
		}
		$blogCids = explode(',',$this->parameter['cid']);
		$blogCid = $blogCids[0];
		$blogCid = str_replace('all-','',$blogCid); //bbs不支持此类调用方法
		$blogCid = intval($blogCid);
		if(is_object($blog)){
			$this->blog = $blog;
		}else{
			$blog = $this->blog = newBlog($GLOBALS['very']['blog_type']);
		}
		$this->blog->onlyimg=0; //允许只调用有图片的文章
		if($this->parameter['where']=="photo!=''"){
			$this->blog->onlyimg=1;
		}
		$rs = $this->catedb[$blogCid];
		if(!$rs) throwError('blog_nocondition');
		$condition = unserialize(stripslashes($rs['addtion']));
		//print_r($condition);flush();exit;
		if($condition['viewtype']){
			if ($this->catedb[$blogCid]['path']) {
				$this->blog->viewtype = $this->catedb[$blogCid]['path'];
			}else{
				$this->blog->viewtype = $blogCid;
			}
		}else{
			$this->blog->viewtype = 0;
		}
		$blogcategory = $condition['fid'];
		$this->blog->readConfig($rs['addtion']);
		$thread = $this->blog->getBlog($blogcategory,$this->start,$this->displaynum);
		$this->autoPage && $this->countPage();
		return $thread;
	}

	/**
	 * 自动计算分页
	 *
	 */
	function countPage()
	{
		global $very;
		$page = ($this->start/$this->displaynum)+1;
		$htmlpage = 0;
		if($this->pageurl){
			$url = $this->pageurl;
		}else {
			if(SCR=='list'){ //对于列表页的自动分页处理
				$thecid = $GLOBALS['cid'];
				if($this->catedb[$thecid]['listpub']){
					$htmlpage = 1; //静态分页
				}else{
					$url = 'list.php?cid='.$thecid.'&';
				}
			}else {
				$this->autoPage = 0; //只有列表页才会产生自动分页
				return ;
			}
		}
		if($this->mid==-2){
			$total		= $this->bbs->total();
		}elseif ($this->mid == -1){
			$total		= $this->blog->total();
		}else{
			global $db;
			$rs = $db->get_one($this->totalQuery);
			$total = $rs['total'];
		}
		$numofpage	= ceil($total/$this->displaynum);
		if($htmlpage){ //静态分页
			//if($very['listpage'] && $numofpage>$very['listpage']) $numofpage=$very['listpage'];
			//$pages = $this->htmlPage($page,$numofpage,$very['listpage']);
			$pages = $this->htmlPage($page,$numofpage);
			if($page<$numofpage){
				$this->autoRun=1;  //需要继续生成
			}else{
				$this->autoRun=0; //不需继续
			}
		}else{ //动态分页
			$pages = numofpage($total,$page,$numofpage,$url);
		}
		$this->page = $pages;
	}

	/**
	 * 处理静态文件的分页
	 *
	 * @param integer $page 当前页
	 * @param integer $numofpage 总页数
	 * @param string $url 分页url
	 * @param integer $max
	 * @return string
	 */
	function htmlPage($page,$numofpage,$max=0){ //静态自动分页处理
		global $very,$catedb,$cid;
		$url = $this->listurl;
		if(!$url && $cid && $catedb[$cid]['listurl']) {
			$url = $catedb[$cid]['listurl'];
		}
		strpos($url,'http://')===false && $url = $very['url'].'/'.$url;
		$url_ext 	= end(explode('.',$url));
		$cid		= $GLOBALS['cid'];
		$ext_len	= strlen($url_ext)+1;
		$name_s 	= substr($url,0,-$ext_len); //截取掉.htm / .html
		$total 		= $numofpage;
		$max && $numofpage > $max && $numofpage=$max;
		if($very['listpage']) {
			if($page <= $very['listpage']) {
				$minpos = 1;
				$maxpos = $very['listpage'];
			}else {
				$position	= ($page-$very['listpage'])%5;
				$position	= $position ? $position:5;
				$minpos		= $page+1-$position;
				$maxpos		= $page+5-$position;
				$maxpos		= min($maxpos,$numofpage);
			}
		}
		if($numofpage <= 1 || !is_numeric($page)){
			return '';
		}else{
			$pages="<div class=\"pages\"><a href=\"{$url}\" style=\"font-weight:bold\">&laquo;</a>";
			$flag=0;
			for($i=$page-3;$i<=$page-1;$i++){
				if($i<1) continue;
				if($i==1){
					$thepage = $url;
				}else{
					if($very['listpage']) {
						if(!defined('IN_ADMIN') && !defined('UPDATE')) {
							$thepage = $very['url']."/list.php?cid=".$cid."&page=".$i;
						}else {
							if($i>$maxpos) {
								$thepage = $very['url']."/list.php?cid=".$cid."&page=".$i;
							}else {
								$thepage = "{$name_s}_$i.{$url_ext}";
							}
						}
					}else {
						$thepage = "{$name_s}_$i.{$url_ext}";
					}
				}
				$pages.="<a href=\"$thepage\">$i</a>";
			}
			$pages.="<b> $page </b>";
			if($page<$numofpage)
			{
				for($i=$page+1;$i<=$numofpage;$i++)
				{
					if($very['listpage']) {
						if(!defined('IN_ADMIN') && !defined('UPDATE')) {
							$thepage = $very['url']."/list.php?cid=".$cid."&page=".$i;
						}else {
							if($i>$maxpos) {
								$thepage = $very['url']."/list.php?cid=".$cid."&page=".$i;
							}else {
								$thepage = "{$name_s}_$i.{$url_ext}";
							}
						}
					}else {
						$thepage = "{$name_s}_$i.{$url_ext}";
					}
					$pages.="<a href=\"$thepage\">$i</a>";
					$flag++;
					if($flag==4) break;
				}
			}
			if($very['listpage'] && $page<$numofpage && $numofpage>$very['listpage']) {
				if(!defined('IN_ADMIN') && !defined('UPDATE')) {
					$lastpageurl = $very['url']."/list.php?cid=".$cid."&page=".$numofpage;
				}else {
					if($i>$maxpos) {
						$lastpageurl = $very['url']."/list.php?cid=".$cid."&page=".$numofpage;
					}else {
						$lastpageurl = "{$name_s}_$numofpage.{$url_ext}";
					}
				}
			}else {
				$lastpageurl = "{$name_s}_$numofpage.{$url_ext}";
			}
			$pages.="<input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){if(this.value<=1) return; location='$very[url]/list.php?cid=$cid&page='+this.value;return false;}\"><a href=\"$lastpageurl\" style=\"font-weight:bold\">&raquo;</a> Pages: ( $page/$total total )</div>";
			return $pages;
		}
	}

	/**
	 * 站点公告调用
	 *
	 * @param integer $num1
	 * @param integer $num2
	 * @return array
	 */
	function notice($num1,$num2=''){
		global $db,$very;
		//include(D_P.'data/cache/ext_config.php');
		if($ext_config['notice']['ifopen']!==1){
			return array();
		}
		if(!$num2){
			$start = 0;
			$end = $num1 ? intval($num1) : 10;
		}else{
			$start = $num1 ? intval($num1)-1 : 0;
			$end = intval($num2);
		}
		$rs = $db->query("SELECT * FROM cms_notice ORDER BY postdate DESC LIMIT $start,$end");
		$noticedb = array();
		while ($notice = $db->fetch_array($rs)){
			$notice['url'] = "extensions.php?E_name=notice#".$notice['nid'];
			$noticedb[] = $notice;
		}
		return $noticedb;
	}

	/**
	 * 分页符
	 *
	 * @param
	 */
	function page(){
		return $this->page;
	}

	/**
	 * 查找子分类
	 *
	 * @param integer $cid
	 */
	function includeChild($cid){
		if(strpos($cid,"all-")===false){
			$this->cids[]=$cid;
		}else{
			$cid = str_replace("all-",'',$cid);
			$cid = (int)$cid;
			$this->cids[]=$cid;
			$this->getChild($cid);
		}
	}

	/**
	 * 递归获取父分类下的子分类
	 *
	 * @param integer $cid
	 */
	function getChild($cid){
		foreach ($this->catedb as $childId =>$child)
		{
			if($child['up']==$cid)
			{
				$this->cids[]=$childId;
				$this->getChild($childId);
			}
		}
	}

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
				$mk = get_date($timestamp,'y-m-d');
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
	 * 热门标签
	 *
	 * @param integer $num1
	 * @param integer $num2
	 * @return array
	 */
	function Tags($num1,$num2=''){
		global $db,$very;
		if($num2){
			$start = $num1 ? intval($num1)-1 : 0;
			$end = intval($num2);
		}else{
			$start = 0;
			$end = $num1 ? intval($num1) : 10;
		}
		$rs = $db->query("SELECT * FROM cms_tags ORDER BY num DESC LIMIT $start,$end");
		$tagsdb = array();
		while ($tag = $db->fetch_array($rs)){
			$tag['url'] = 'search.php?action=tag&tagname='.urlencode($tag['tagname']);
			$tagsdb[] = $tag;
		}
		return $tagsdb;
	}
	 /**
	 * 解析tids
	 *
	 * @param string
	 * @param integer $start
	 * @param integer $num
	 * @return array
	 */
	function parseTids($tids,$num) {
		global $very,$db;
		if(strpos($num,',')===false) {
			$start	= 0;
			$num	= (int) $num;
		}else {
			list($start,$num)	= explode(",",$num);
		}
		(!$start || !is_numeric($start) || $start<0) && $start = 0;
		(!$num || !is_numeric($num)) && $num = 0;
		if($tids && preg_match('/^(\d+\,)*\d+$/',$tids)) {
			$rt = $db->query("SELECT tid,title,url,cid,mid,linkurl,ifpub FROM cms_contentindex WHERE tid IN($tids)");
			$option = $fieldtids = $result = array();
			while($contentlink = $db->fetch_array($rt)) {
				if($contentlink['linkurl']){
					$contentlink['url'] = $contentlink['linkurl']; //如果存在链接
				}else{
					if($this->catedb[$contentlink['cid']]['htmlpub'] && $contentlink['url']){
						$contentlink['url']=$contentlink['ifpub'] ? "$very[htmdir]/$contentlink[url]" : '';
					}else{
						$contentlink['url']=$contentlink['ifpub'] ? "view.php?tid=".$contentlink['tid']."&cid=".$contentlink['cid'] : '';
					}
				}
				$option[$contentlink[tid]] = $contentlink;
			}
			$fieldtids = explode(",",$tids);
			foreach($fieldtids as $val) {
				if(!$val) continue;
				if($option[$val]) {
					$result[]=$option[$val];
				}
			}
			if($num) {
				return array_slice($result, $start,$num);
			}else {
				return array_slice($result, $start);
			}
		}else {
			return false;
		}
	 }

	 function getAjax($param,$thisid,$sytleid){
		
		$ajaxfile = substr(md5($param),0,32).".js";
		if(!file_exists(D_P."data/js/".$ajaxfile)||time()-filemtime(D_P."data/js/".$ajaxfile)>60){
			!is_object($this->ajax) && $this->ajax = new Ajax($this->fielddb[$this->mid]);
			$this->ajax->mid		 = $this->mid;
			$this->ajax->condition = $param;
			$this->ajax->cachefile = D_P."data/js/".$ajaxfile;
			$this->ajax->thisid	 = $thisid;
			$this->ajax->getThread();
		}
		echo "<script language=\"javascript\" src=\"data/js/".$ajaxfile."\"></script>\r\n<script language=\"javascript\">javascriptcn.ready( function(){parseTemplate(".$thisid.",'".$thisid."','".$sytleid."');})</script>";
	 }
}

/**
 * 单个文章内容读取类
 *
 */
class Cont extends Cms {
	/**
	 * 当前页url
	 *
	 * @var string
	 */
	var $pageurl;
	/**
	 * 内容页url
	 *
	 * @var string
	 */
	var $url;

	/**
	 * 当前要读取的内容Tid
	 *
	 * @var integer
	 */
	var $tid;

	/**
	 * 当前要读取的栏目Cid
	 *
	 * @var integer
	 */
	var $cid;

	/**
	 * 文章的上一页，下一页
	 *
	 * @var string
	 */
	var $preUrl;
	var $nextUrl;

	/* 下面方法用于显示单篇内容 */
	function getone($cid,$tid,$page=1)
	{
		global $db,$very;
		$this->pageurl = '';
		$this->cid = intval($cid);
		$this->tid = intval($tid);
		$page = empty($page) ? 1 : intval($page);
		$this->mid = $this->catedb[$this->cid]['mid'];

		if($this->mid=='-2'){
			if(!is_object($this->bbs)){
				$this->bbs = newBBS($very['bbs_type']);
			}
			return $this->bbs->getone($this->tid,$this->cid);
		}elseif ($this->mid=='-1'){
			if(!is_object($this->blog)){
				$this->blog = newBlog($very['blog_type']);
			}
			$this->blog->cid = $this->cid;
			return $this->blog->getone($this->tid);
		}

		$this->selectTable($this->mid);

		$cachefile = substr(md5($this->table.$tid.$cid),0,15);
		$cachefile = D_P.'data/sql/'.$cachefile.'.cache';
		if ($page!=1 && file_exists($cachefile)) {
			$cacheStr	= readover($cachefile);
			$array		= unserialize($cacheStr);
			$array['content'] = $array['content'][$page-1];
			$array['content'] .= $this->fpage($page,$array['count']);
			if($page<$array['count']){
				$this->autoRun=1;
			}else {
				$this->autoRun=0;
				unlink($cachefile);
			}
			$this->preUrl  && $array['preUrl']  = $this->preUrl;
			$this->nextUrl && $array['nextUrl'] = $this->nextUrl;
			return $array;
		}

		$rs = $db->get_one("SELECT * FROM cms_contentindex i LEFT JOIN $this->table c USING(tid) WHERE i.tid='$tid' AND cid='$cid'");
		if(!function_exists('adminmsg'))
		{
			$rs['ifpub']!=1 && throwError('data_error');
			!$rs && throwError('data_error');
		}
		$this->getvalue($rs);
		list($tags,$tagIds) = $this->getTags($tid); //获取到Tag名称信息以及TagId信息
		if($this->catedb[$this->cid]['copyctrl']){
			foreach($this->fielddb[$this->mid] as $key=>$val) {
				if($val['inputtype']=='edit') {
					$rs[$val['fieldid']] = $this->copyctrl($rs[$val['fieldid']]);
				}
			}
		}
		$content_array = explode('<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>',$rs['content']);
		/* 此处功能不完善，尚需要对没有分页标签的自动分页进行处理 */
		$count = count($content_array);
		if($count==1){
			$content_array = explode('<div style="page-break-after: always;"><span style="display: none;">&nbsp;</span></div>',$rs['content']);
			$count = count($content_array);
		}
		$rs['content'] = $content_array[$page-1];
		$rs['content'] .= $this->fpage($page,$count);
		$page != '1' && $rs['photo'] = '';
		if($page<$count){
			$this->autoRun=1;
		}else {
			$this->autoRun=0;
		}
		$rs['tags'] = $tags;
		if($tagIds){
			$rs['links'] = $this->getLinks($tagIds);
		}
		if ($rs['fpage'] && defined('IN_ADMIN')) {
			$array = $rs;
			$array['content'] = $content_array;
			$array['count']	  = $count;
			writeover($cachefile,serialize($array));
		}
		$this->preUrl  && $rs['preUrl']  = $this->preUrl;
		$this->nextUrl && $rs['nextUrl'] = $this->nextUrl;
		return $rs;
	}

	function fpage($page,$count){
		global $very;
		!$page && $page = 1;
		if(!$this->url){
			if(function_exists('adminmsg')){return;}
			$this->url = $this->pageurl = 'view.php?cid='.$this->cid.'&tid='.$this->tid;
			if($count>1){
				$str = "";
				$nextPage = $page+1;
				$prePage = $page-1;
				$nextUrl = $this->url.'&page='.$nextPage;
				$preUrl	 = $prePage <= 1 ? $this->url.'&page=1' : $this->url.'&page='.$prePage;
				$this->pageurl = $this->url.'&page='.$Page;
				if($page>1){
					$this->preUrl  = $very['url']."/".$preUrl;
					$str.="[<a href='$preUrl'>上一页</a>]";
				}
				for($i=1;$i<=$count;$i++){
					$theurl = $this->url.'&page='.$i;
					if($i==$page){
						$str.="&nbsp;$i&nbsp;";
					}else {
						$str.="&nbsp;<a href=\"$theurl\">$i</a>&nbsp;";
					}
				}
				if($page<$count){
					$this->nextUrl = $very['url']."/".$nextUrl;
					$str.="[<a href='$nextUrl'>下一页</a>]";
				}
				return "<div style=\"float:right;\">$str</div>";
			}
		}else{
			$url_ext = end(explode('.',$this->url));
			$url_ext = '.'.$url_ext;
			$url_pre = substr($this->url,0,-strlen($url_ext));
			if($count>1){
				$str = "";
				if($page==1){
					$this->pageurl = $this->url;
					$nextUrl = $url_pre.'_2'.$url_ext;
				}else{
					$nextPage = $page+1;
					$prePage = $page-1;
					$nextUrl = $url_pre.'_'.$nextPage.$url_ext;
					$preUrl	 = $prePage == 1 ? $this->url : $url_pre.'_'.$prePage.$url_ext;
					$this->pageurl = $url_pre.'_'.$page.$url_ext;
				}
				if($page>1){
					$this->preUrl  = $very['url']."/".$preUrl;
					$str.="[<a href='$preUrl'>上一页</a>]";
				}
				for($i=1;$i<=$count;$i++){
					if($i==1){
						$theurl = $this->url;
					}else{
						$theurl = $url_pre.'_'.$i.$url_ext;
					}
					if($i==$page){
						$str.="&nbsp;$i&nbsp;";
					}else {
						$str.="&nbsp;<a href=\"$theurl\">$i</a>&nbsp;";
					}
				}
				if($page<$count){
					$this->nextUrl = $very['url']."/".$nextUrl;
					$str.="[<a href='$nextUrl'>下一页</a>]";
				}
				return "<div style=\"float:right;\">$str</div>";
			}else{
				$this->pageurl = $this->url;
			}
		}
		return ;
	}

	function getNext($postdate){ //获取下一篇内容
		global $db,$very;
		$postdate = (int)$postdate;
		if(!$postdate) {
			$rs = $db->get_one("SELECT postdate FROM cms_contentindex WHERE tid='$this->tid'");
			$postdate = $rs['postdate'];
		}
		$rs = $db->get_one("SELECT title,tid,url,linkurl FROM cms_contentindex WHERE postdate<'$postdate' AND cid='$this->cid' AND ifpub='1' ORDER BY postdate DESC LIMIT 1");
		if($rs){
			if($rs['linkurl']){
				$rs['url'] = $rs['linkurl'];
			}elseif($this->catedb[$this->cid]['htmlpub'] && !$this->catedb[$this->cid]['linkurl'] && $this->catedb[$this->cid]['type']){
				if(!$rs['url']){
					$rs['url'] = $this->htmlDir($this->cid);
					$rs['url'] .= '/'.$rs['tid'].'.'.$very['htmext'];
				}
				$rs['url'] = $very['htmdir']."/".$rs['url'];
			}else{
				$rs['url'] = 'view.php?tid='.$rs['tid'].'&cid='.$this->cid;
			}
			return $rs;
		}else{
			return false;
		}
	}

	function getPrev($postdate){ //前一篇内容
		global $db,$very;
		$postdate = (int)$postdate;
		if(!$postdate) {
			$rs = $db->get_one("SELECT postdate FROM cms_contentindex WHERE tid='$this->tid'");
			$postdate = $rs['postdate'];
		}
		$rs = $db->get_one("SELECT title,tid,url,linkurl FROM cms_contentindex WHERE postdate>'$postdate' AND cid='$this->cid' AND ifpub='1' ORDER BY postdate ASC LIMIT 1");
		if($rs){
			if($rs['linkurl']){
				$rs['url'] = $rs['linkurl'];
			}elseif($this->catedb[$this->cid]['htmlpub'] && !$this->catedb[$this->cid]['linkurl'] && $this->catedb[$this->cid]['type']){
				if(!$rs['url']){
					$rs['url'] = $this->htmlDir($this->cid);
					$rs['url'] .= '/'.$rs['tid'].'.'.$very['htmext'];
				}
				$rs['url'] = $very['htmdir']."/".$rs['url'];
			}else{
				$rs['url'] = 'view.php?tid='.$rs['tid'].'&cid='.$this->cid;
			}
			return $rs;
		}else{
			return false;
		}
	}

	function jsGetPrev($length) {
		global $very;
		 return "<script language=\"javascript\" src=\"$very[url]/update.php?type=getprev&tid=$this->tid&cid=$this->cid&length=$length\"></script>";
	}

	function jsGetNext($length) {
		global $very;
		 return "<script language=\"javascript\" src=\"$very[url]/update.php?type=getnext&tid=$this->tid&cid=$this->cid&length=$length\"></script>";
	}

	/**
	 * 某内容的相关Tag
	 *
	 * @param integer $tid
	 * @return string
	 */
	function getTags()
	{
		global $db,$very;
		$rs = $db->query("SELECT * FROM cms_contenttag t LEFT JOIN cms_tags s USING(tagid) WHERE t.tid='$this->tid' ");
		$tagsInfo	= array();
		$tagids		= array();
		while ($tag = $db->fetch_array($rs))
		{
			if(!$tag['tagname']) continue;
			$tagsInfo[] = "<a href=\"search.php?action=tag&tagname=".urlencode($tag['tagname'])."\" target=\"_blank\">".$tag['tagname']."</a>";
			$tagids[] = $tag['tagid'];
		}
		$tagsInfo = implode('&nbsp;&nbsp;',$tagsInfo);
		return array($tagsInfo,$tagids);
	}

	function getLinks($tagids)
	{
		global $db,$very;
		$num = $very['linksnum'];
		$tagids = implode(',',$tagids);
		!$num && $num=5; //相关文章调用数目
		$sqlnum = $num*2;
		$rs = $db->query("SELECT * FROM cms_contenttag g LEFT JOIN cms_contentindex t USING(tid) WHERE g.tagid IN($tagids) AND t.ifpub=1 AND g.tid!='$this->tid' ORDER BY t.tid DESC LIMIT $sqlnum");
		$linksInfo = array();
		$i = 0;
		while ($lk = $db->fetch_array($rs)){
			if(array_key_exists($lk['tid'],$linksInfo)){
				continue;
			}
			if($lk['linkurl']){
				$lk['url'] = $lk['linkurl'];
			}elseif($lk['url']){
				$lk['url'] = $very['htmdir'].'/'.$lk['url'];
			}else {
				$lk['url'] = 'view.php?tid='.$lk['tid'].'&cid='.$lk['cid'];
			}
			$linksInfo[$lk['tid']] = $lk;
			$i++;
			if($i==$num){
				break;
			}
		}
		return $linksInfo;
	}
	/*文章水印函数
	* @param string $val
	* @return string
	*/
	function copyctrl($val){
		$hiddenhtml = array('1'=>"div",'2'=>"span");
		$html	= $hiddenhtml[mt_rand(1,2)];
		$lenth=10;
		mt_srand((double)microtime() * 1000000);
		for($i=0;$i<$lenth;$i++){
			$randval.=chr(mt_rand(0,126));
		}
		$randval=str_replace('<','&lt;',$randval);
		$randval1 = "<$html style=\"display:none\"> $randval </$html>&nbsp;<br />";
		$randval2 = "<$html style=\"display:none\"> $randval </$html>&nbsp;</p>";
		$val = preg_replace("/<br \/>/is","$randval1",$val);
		$val = preg_replace("/，/","<$html style=\"display:none\"> $randval </$html>,",$val);
		return preg_replace("/<\/p>/is","$randval2",$val);
	}

	function getComment($cid,$num) {
		global $db;
		$comment = array();
		$num = $num ? intval($num):5;
		$cid = intval($cid);
		$query = $db->query("SELECT title,comnum,url,cid,tid,mid FROM cms_contentindex c WHERE c.cid='$cid' ORDER BY c.comnum DESC LIMIT $num");
		while($rs = $db->fetch_array($query)) {
			$comment[] =$rs;
		}
		return $comment;
	}
}
?>