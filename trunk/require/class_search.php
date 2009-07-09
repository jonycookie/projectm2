<?php
!defined('IN_CMS') && die('Forbidden');
require_once(D_P.'data/cache/field.php');
require_once(D_P.'data/cache/cate.php');
/**
 * CMS搜索各种自定义模型
 *
 */
class Search{

	var $type; //查询方式：模糊查询 OR 精确查询
	var $displaynum; //显示条目
	var $config;
	/**
	 * 要搜索的内容表
	 *
	 * @var string
	 */
	var $table;
	/**
	 * 内容表中可供搜索的字段
	 *
	 * @var string
	 */
	var $fielddb;

	/**
	 * 搜索条件
	 *
	 * @var string
	 */
	var $mid;
	var $cid;
	var $ordering;
	var $keyword;
	var $keyword_type;
	var $searchdate;

	/**
	 * 搜索结果
	 *
	 * @var string
	 */
	var $result;

	function __construct(){
		global $sys,$fielddb;
		$this->config = $sys;
		!$this->config['searchmax'] && $this->config['searchmax']=100;
		$this->fielddb = $fielddb;
	}

	function Search(){
		$this->__construct();
	}

	function doIt(){
		global $timestamp;
		$this->type = GetGP('s_type');
		!$this->type && $this->type='simple';
		$step = GetGP('step');
		!in_array($this->type,array('simple','advance','date')) && throwError('搜索方式错误');
		if($step==2){
			if(in_array($this->type,array('simple','advance'))){
				$this->mid = (int)GetGP('mid');
				if(GetCookie("sh") && GetCookie("keyword")!=GetGP('keyword')) throwError('You can not search now,please wait a moment..');
				if(!isset($this->fielddb[$this->mid]) || !$this->mid) throwError('searchmiderror');
				$this->table = 'cms_content'.$this->mid;
				$this->fielddb = $this->fielddb[$this->mid];
				$this->config['searchtime'] && Cookie("sh","1",$this->config['searchtime']+$timestamp);
			}
			$searchMethod = $this->type."Result";
			$this->$searchMethod();
		}
	}

	/**
	 * 搜索页显示
	 *
	 */
	function resultShow(){
		global $moduledb,$sys,$cate;
		$keyword = $this->keyword;
		$rawkeyword = rawurlencode($keyword);
		$total = count($this->result);
		${'select_'.$this->ordering} ='selected';

		$page = GetGP('page');
		if(!is_numeric($page) || $page<1){
			$page = 1;
		}
		$numofpage = ceil($total/20);
		$page <=0 && $page=1;
		$start = ($page-1)*20;
		$pages = numofpage($total,$page,$numofpage,"search.php?step=2&mid=$this->mid&keyword=$rawkeyword&s_type=$this->type&cid=$this->cid&ordering=$this->ordering&keyword_type=$this->keyword_type&");
		$searchresult	= array_slice ($this->result,$start,20);
		$metakeyword	= $sys['title'];
		$mkeyword		= str_replace(' ',',',trim($keyword));
		$metakeyword	.= ','.$mkeyword;
		$metadescrip	= $metakeyword;
		start();
		require Template('result');
		footer();
	}
	/**
	 * 简单搜索
	 * 和高级搜索的区别是：简单搜索只搜索title字段
	 */
	function simpleResult(){
		global $db,$cate,$sys,$catedb,$moduledb,$timestamp;
		$this->keyword	= GetGP('keyword');
		$this->config['searchtime'] && Cookie("keyword","$this->keyword",$this->config['searchtime']+$timestamp);
		(strpos($this->keyword,'\'')||strpos($this->keyword,'\"')||strpos($this->keyword,'#')) && throwError('Condition Error');
		$this->keyword  = Char_cv($this->keyword);
		if(!$this->keyword) throwError('please input the keyword');
		$this->keyword && (strlen(trim($this->keyword))<3||strlen(trim($this->keyword))>80)  && throwError('keyword length error');
		$schinfo = $db->get_one("SELECT * FROM cms_schcache WHERE schkeyword='$this->keyword'");
		if($schinfo) {
			$this->result = unserialize(stripslashes($schinfo['schvalue']));
		}else {
			$where	= "";
			$words	= array();
			if(strpos($this->keyword,' ')){
				$wheres	= array();
				$words	= explode( ' ', $this->keyword);
				foreach($words as $key=>$val) {
					if(!$val || strlen($val)<3) continue;
					$wheres[] ="title LIKE('%$val%')";
				}
				$where	= "(".implode(" OR ",$wheres).")";
			}else {
				$where	= "title LIKE('%$this->keyword%')";
			}
			if(!$where || $where=='()') throwError('没有选择查询条件');

			if($sys['searchrange']) {
				$timelimit = $timestamp-$sys['searchrange']*86400;
				$where	= "postdate>$timelimit AND ".$where;
			}
			$where	= "mid='$this->mid' AND ifpub='1' AND ".$where;

			$rs = $db->query("SELECT * FROM cms_contentindex WHERE $where ORDER BY postdate DESC LIMIT {$this->config['searchmax']}");
			$searchresult = array();
			while ($result = $db->fetch_array($rs)) {
				if($result['linkurl']) {
					$result['url'] = $result['linkurl']; //如果存在链接
				}else{
					if($catedb[$result['cid']]['htmlpub']){
						if(!$result['url']) continue;
						$result['url']	= $sys['htmdir'].'/'.$result['url'];
					}else{
						if($result['cid']<=0) continue;
						$result['url']	= 'view.php?tid='.$result['tid'].'&cid='.$result['cid'];
					}
				}
				$result['cname']	= $catedb[$result['cid']]['cname'];
				$result['listurl']	= $sys['url']."/".$catedb[$result['cid']]['listurl'];
				if(count($words)>1){
					foreach($words as $word){
						$result['title'] = $this->lightShow($result['title'],$word);
					}
				}else{
					$result['title'] = $this->lightShow($result['title'],$this->keyword);
				}
				$searchresult[] = $result;
			}
			$this->result = $searchresult;
			$this->schcache($searchresult);
		}
	}

	/**
	 * 搜索缓存
	 *
	 */
	function schcache($searchresult) {//功能未完善
		global $db,$timestamp;
		$cachetime = 3600;
		$db->update("DELETE FROM cms_schcache WHERE schtime<$timestamp-$cachetime");
		$totle = count($searchsult);
		$searchresult = addslashes(serialize($searchresult));
		if($this->type=='advance') {
			$searchorder = $this->mid."|".$this->cid."|".$this->keyword_type."|".$this->ordering;
			strlen($searchorder)>50 && throwError('Condition Error');
		}else {
			$searchorder = '';
		}
		$db->update("INSERT INTO cms_schcache(schkeyword,schtime,total,schvalue,sorderby) VALUES('$this->keyword','$timestamp','$total','$searchresult','$searchorder')");
	}

	/**
	 * 高级搜索
	 *
	 */
	function advanceResult(){
		global $db,$cate,$sys,$catedb,$moduledb,$timestamp;

		$this->cid		= (int)GetGP('cid');
		$this->ordering	= Char_cv(GetGP('ordering'));
		$this->keyword	= GetGP('keyword');
		$keyword_type   = GetGP('keyword_type');
		$this->config['searchtime'] && Cookie("keyword","$this->keyword",$this->config['searchtime']+$timestamp);
		$this->keyword_type = Char_cv($keyword_type);
		(strpos($this->keyword,'\'')||strpos($this->keyword,'\"')||strpos($this->keyword,'#')) && throwError('Condition Error');
		$this->keyword  = Char_cv($this->keyword);
		!$moduledb[$this->mid]['search'] && throwError('Condition Error');

		if(!$this->keyword) throwError('please input the keyword');
		$this->keyword && (strlen(trim($this->keyword))<3||strlen(trim($this->keyword))>80)  && throwError('keyword length error');
		$searchorder = $this->mid."|".$this->cid."|".$this->keyword_type."|".$this->ordering;
		strlen($searchorder)>50 && throwError('Condition Error');
		$schinfo = $db->get_one("SELECT * FROM cms_schcache WHERE schkeyword='$this->keyword' AND sorderby='$searchorder'");//读取缓存
		if($schinfo) {
			$this->result = unserialize(stripslashes($schinfo['schvalue']));
		}else{
			$where	= "";
			$words	= array();
			$wheres = array();
			$textfield = array();
			$textfield[] = "title";						//高亮替换字段
			if(strpos($this->keyword,' ')){
				$words	= explode( ' ', $this->keyword);
				$wheres = array();
				foreach ($words as $word) {
					if(!$word || strlen($word)<3) continue;
					$wheres2 = array();
					foreach ($this->fielddb as $val){
						$fieldid = GetGP("$val[fieldid]");
						if(!$val['ifsearch']) continue; //不可供搜索，跳过
						if(in_array($val['inputtype'],array('select','radio'))){
							if(!$fieldid) continue; //若没有提交该数据
							$wheres2[] = " ".$val['fieldid']."='".$fieldid."' ";
						}elseif ($val['inputtype']=='checkbox'){
							if(!$fieldid) continue; //若没有提交该数据
							$query	= array();
							foreach ($fieldid as $v){
								if(empty($v)) continue;
								$query[] = " ".$val['fieldid']." LIKE(',".$v.",') ";
							}
							$query		= implode("OR",$query);
							$wheres2[]	= " (".$query.")";
						}else {
							!in_array("$val[fieldid]",$textfield) && $textfield[]= "$val[fieldid]";
							if($keyword_type!=$val['fieldid']) continue;
							$wheres2[]	= "LOWER($val[fieldid]) LIKE '%$word%'";
						}
					}
					$wheres[] = implode( ' AND ', $wheres2 );
				}
				$where = '(' . implode( ' OR ', $wheres ) . ')';
			}else{
				$wheres2 = array();
				foreach ($this->fielddb as $val){
					$fieldid = GetGP("$val[fieldid]");
					if(!$val['ifsearch']) continue;				//不可供搜索，跳过
					if(in_array($val['inputtype'],array('select','radio'))){//未完善
						if(!$fieldid) continue;	//若没有提交该数据
						$wheres2[] = " ".$val['fieldid']."='".$fieldid."' ";
					}elseif ($val['inputtype']=='checkbox'){
						if(!$fieldid) continue;	//若没有提交该数据
						$query = array();
						foreach ($fieldid as $v){
							if(empty($v)) continue;
							$query[] = " ".$val['fieldid']." LIKE(',".$v.",') ";
						}
						$query = implode("OR",$query);
						$wheres2[]	= " (".$query.")";
					}else {
						!in_array("$val[fieldid]",$textfield) && $textfield[]= "$val[fieldid]";
						if($keyword_type!=$val['fieldid']) continue;
						$wheres2[]	= "LOWER($val[fieldid]) LIKE '%$this->keyword%'";
					}
				}
				$where = '(' . implode( ' AND ', $wheres2 ) . ')';
			}
			if(!$where || $where =='()') throwError('没有选择查询条件');
			$order = '';
			switch ($this->ordering) {//排序
				case 'newest':
				default:
					$order = 'a.postdate DESC';
					break;
				case 'oldest':
					$order = 'a.postdate ASC';
					break;
				case 'popular':
					$order = 'a.hits DESC';
					break;
			}
			if($sys['searchrange']) {
				$timelimit = $timestamp-$sys['searchrange']*86400;
				$where	= "a.postdate>$timelimit AND ".$where;
			}
			$midlimit	= $this->cid?"a.mid='$this->mid' AND a.cid='$this->cid' ":"a.mid='$this->mid'";

			$rs = $db->query("SELECT a.tid,a.cid,a.title,a.url,a.publisher,a.postdate,a.linkurl FROM cms_contentindex a LEFT JOIN $this->table b USING(tid) WHERE $midlimit AND a.ifpub='1' AND $where ORDER BY $order LIMIT {$this->config['searchmax']}");

			$searchresult = array();
			$keyword = stripslashes($this->keyword);
			while ($result = $db->fetch_array($rs)) {
				if($result['linkurl']) {
					$result['url'] = $result['linkurl']; //如果存在链接
				}else{
					if($catedb[$result['cid']]['htmlpub']){
						if(!$result['url']) continue;
						$result['url']	= $sys['htmdir'].'/'.$result['url'];
					}else{
						if($result['cid']<=0) continue;
						$result['url']	= 'view.php?tid='.$result['tid'].'&cid='.$result['cid'];
					}
				}
				$result['cname']	= $catedb[$result['cid']]['cname'];
				$result['listurl']	= $sys['url']."/".$catedb[$result['cid']]['listurl'];
				if(count($words)>1){
					foreach($words as $word){
						$result['title'] = $this->lightShow($result['title'],$word);
					}
				}else{
					$result['title'] = $this->lightShow($result['title'],$keyword);
				}
				$searchresult[] = $result;
			}

			$this->result = $searchresult;
			$this->schcache($searchresult);
		}
	}

	/**
	 * 高亮显示
	 *
	 */
	function lightShow($text,$word) {
		if(strpos(strtolower($text),strtolower($word))){
			$text = str_replace(strtolower($word),"<span style=\"background-color:#FFFF00;color:#FF0000;\">$word</span>",strtolower($text)); //搜索关键字高亮显示
		}
		return $text;
	}
	/**
	 * 根据时间来搜索
	 *
	 */
	function dateResult() {
		global $db,$cate,$sys,$catedb,$moduledb,$timestamp;
		$searchdate = GetGP('searchdate');
		$this->searchdate = $searchdate;
		$searchtime = strtotime($searchdate);
		($searchtime==-1 || !is_numeric($searchtime) || $timestamp<$searchtime) && throwError("search time error");
		$searchtimelimit = $searchtime+ 86400;
		$where = "postdate>'$searchtime' AND postdate<'$searchtimelimit'";
		$rs = $db->query("SELECT * FROM cms_contentindex WHERE $where AND ifpub='1' ORDER BY postdate DESC LIMIT {$this->config['searchmax']}");
		$searchresult = array();
		while($result = $db->fetch_array($rs)) {
			if(!$result['url']) continue;
			if($catedb[$result['cid']]['htmlpub']){
				$result['url']	= $sys['htmdir'].'/'.$result['url'];
			}else{
				if($result['cid']<=0) continue;
				$result['url']	= 'view.php?tid='.$result['tid'].'&cid='.$result['cid'];
			}
			$result['cname']	= $catedb[$result['cid']]['cname'];
			$result['listurl']	= $sys['url']."/".$catedb[$result['cid']]['listurl'];
			$searchresult[] = $result;
		}
		$this->result = $searchresult;
	}
	/**
	* ajax返回模型相关的可搜索信息
	*/
	function getSearchField($mid) {
		global $charset,$catedb;
		$this->mid  = (int)$mid;
		!$this->mid && exit("error");
		$catecache  = '';
		$catecache .= "<select name=\"cid\" id=\"cid\">";
		$catecache .= "<option value=\"\">ALL</option>";
		foreach ($catedb as $key => $value) {
			if($value['mid']!=$this->mid || $value['mid']==0) continue;
			$add	= '';
			for ($i=1;$i<$value['depth'];$i++) {
				$add .= '>';
			}
			$catecache .= "<option value=\"$value[cid]\">$add $value[cname]</option>";
		}
		$catecache .= "</select>";
		$this->fielddb = $this->fielddb[$this->mid];
		$searchArea = array();
		$searchArea['select'] .= '<select name=\'keyword_type\'>';
		foreach ($this->fielddb as $fid=>$val){
			if(!$val['ifsearch']) continue;
			if($val['inputtype']=='radio' || $val['inputtype']=='checkbox'){
				$defaultValue = explode('|',$val['defaultvalue']);
				$defaultLabel = explode('|',$val['inputlabel']);
				$str = $val['fieldname'].":";
				foreach ($defaultValue as $key=>$v){
					$checked  = $key==0 ? 'CHECKED' : '';
					$str .="<input name=\"$val[fieldid]\" type=\"".$val['inputtype']."\" value=\"$v\" $checked> $defaultLabel[$key] ";
				}
				$searchArea['check'].=$str;
			}elseif ($val['inputtype']=='select'){
				$str = "<select name=\"".$val['fieldid']."><option value='' selected>".$val['fieldname']."</option>";
				$defaultValue = explode('|',$val['defaultvalue']);
				$defaultLabel = explode('|',$val['inputlabel']);
				foreach ($defaultValue as $key=>$v){
					$str .="<option value=\"$v\"> $defaultLabel[$key] </option>";
				}
				$str .= "</select>";
				$searchArea['check'] .= $str;
			}else{
				$searchArea['select'] .="<option value=\"".$val['fieldid']."\">".$val['fieldname']."</option>";
			}
		}
		$searchArea['select'].="</select>";
		foreach($searchArea as $key=>$val) {
			if(!$val) continue;
			$catecache .=$val;
		}

		if($charset != 'utf8'){
			require_once(R_P.'require/chinese.php');
			$chs = new Chinese($charset,'UTF8');
			$catecache=$chs->Convert($catecache);
		}
		echo($catecache);exit;
	}

	function tagResult(){
		global $db,$moduledb,$sys,$cate,$catedb;
		$tagName = GetGP('tagname');
		$tagName = urldecode($tagName);
		if(!$tagName){
			throwError('data_error');
		}
		$tagName = addslashes($tagName);
		$rs = $db->get_one("SELECT * FROM cms_tags WHERE tagname='$tagName'");
		if(!$rs){
			throwError('data_error');
		}
		if($rs['num']>0){
			$tagId = intval($rs['tagid']);
			unset($rs);
			empty($this->config['tagsnum']) && $this->config['tagsnum'] = 50; //默认显示50篇最新文章
			$rs = $db->query("SELECT i.title,i.url,i.tid,i.cid,i.postdate FROM cms_contenttag t LEFT JOIN cms_contentindex i USING(tid) WHERE t.tagid='$tagId' LIMIT {$this->config[tagsnum]}");
			$tagInfo = array();
			while ($tag = $db->fetch_array($rs)) {
				if($tag['url']){
					$tag['url'] = $this->config['htmdir'].'/'.$tag['url'];
				}else{
					$tag['url'] = "view.php?tid=".$tag['tid']."&cid=".$tag['cid'];
				}
				$tag['cname'] = $catedb[$tag['cid']]['cname'];
				$tagInfo[] = $tag;
			}
		}
		$metakeyword = $metadescrip = $sys['title'].','.$tagName;
		start();
		require Template('tag');
		footer();
	}
}
?>