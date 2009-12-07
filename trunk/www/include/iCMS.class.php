<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class iCMS extends Template {
	var $config='';
	var $db='';
	var $id='';
	var $title='';
	var $cacheID='';
	var $compileID='';
	var $firstcount='0';
	var $pagenav='';
	var $pagesize='';
	var $date=array();
	var $mode='';
	var $dir='';
	var $url='';
	var $action='';
	var $actionSQL='';
	var $result='';
	var $get=array();
	var $iCache;
	//PHP5 Class
	Function __construct(){
		global $config,$db;
		$this->config			= $config;
		$this->db				= $db;
		$this->version			= Version;
		$this->template_dir		= iPATH.'templates';
		$this->compile_dir		= iPATH.'cache';
		$this->cache_dir		= iPATH.'cache';
//		$this->plugins_dir		= array("plugins",iPATH.'include/modifier');
		$this->cache			= false;
		$this->debugging		= false;
		$this->left_delimiter	= '<!--{';
		$this->right_delimiter	= '}-->';
		$this->assign("poweredby", '<a href="http://www.idreamsoft.cn" target="_blank">iCMS</a> V'.Version);
		$this->assign("version", Version);
		$this->assign('site',array("title"=>$this->config['name'],"seotitle"=>$this->config['seotitle'],
					"keywords"	=>$this->config['keywords'],
					"description"=>$this->config['description'],
					"domain"	=>$this->config['domain'],
					"url"		=>$this->config['url'],
					"dir"		=>substr($this->config['dir'],0,-1),
					"index"		=>$this->config['dir'].($this->config['ishtm']?$this->config['indexname'].$this->config['htmlext']:'index'.((($this->config['customlink']=='custom'||$this->config['customlink']=='2') && $this->config['rewrite']['dir']!='.php?')?$this->config['rewrite']['ext']:'.php')),
					"tpl"		=>$this->config['template'],
					"tplpath"	=>$this->config['dir']."templates/".$this->config['template'],
					"tplurl"	=>$this->config['url']."/templates/".$this->config['template'],
					"email"		=>$this->config['masteremail'],
					"icp"		=>$this->config['icp']));
		$this->dir				= $this->config['dir'];
		$this->register_modifier("date", 	"get_date");
		$this->register_modifier("cut", 	"csubstr");
		$this->register_modifier("htmlcut", "htmlSubString");
		$this->register_modifier("count", 	"cstrlen");
		$this->register_modifier("html2txt", "HtmToText");
		$this->register_modifier("pinyin", 	"GetPinyin");
		$this->register_modifier("unicode", "getUNICODE");
		$this->register_modifier("small","gethumb");
	}
	//PHP4 Class
	Function iCMS(){
		$this->__construct();
	}
	//Index
	Function Index($indexTPL='',$indexname=''){
		empty($indexname)&& $indexname=$this->config['indexname'];
		empty($indexTPL)&& $indexTPL=$this->config['indexTPL'];
		$this->jumptohtml(iPATH.$indexname.$this->config['htmlext'],$this->dir.$indexname.$this->config['htmlext']);
		return $this->iPrint($indexTPL);
	}
	//Page
	Function page($p='',$id=0){
		$whereSQL=(empty($p)&&$id)?"`id`='{$id}'":"`dir`='{$p}'";
		$cp=$this->db->getRow("SELECT * FROM `#iCMS@__catalog` WHERE $whereSQL");
		if(empty($cp)){
			$this->error('error:page');
		}else{
			$_urlArray	= array('link'=>$cp->dir,'url'=>$cp->url);
			$this->jumptohtml($this->iurl('page',$_urlArray,'',iPATH));
			$pd=$this->db->getRow("SELECT * FROM `#iCMS@__page` WHERE cid='$cp->id'",ARRAY_A);
			if($pd){
				$this->assign('page',$pd);
				$this->assign(array('title'=>$pd['title'],
					'keywords'=>$pd['keyword'],
					'description'=>$pd['description'],
					'body'=>unhtmlspecialchars($pd['body']),
					'creater'=>$pd['creater'],
					'updater'=>$pd['updater'],
					'createtime'=>$pd['createtime'],
					'updatetime'=>$pd['updatetime']
				));
				$this->get['title']=$pd['title'];
			}
			if($this->config['linkmode']=='id'||$id){
				$this->iList($cp->id,false);
			}elseif($this->config['linkmode']=='title'){
				$this->iList($cp->dir,false);
			}
			return $this->iPrint($cp->tpl_index,'page');
		}
	}
	//List
	Function iList($argv,$show=true,$act=true){
		$sql= $this->linkmodeSQL('id','dir',$argv);
		$rs	= $this->db->getRow("SELECT * FROM `#iCMS@__catalog` WHERE {$sql}",ARRAY_A);
		empty($rs) && $this->error('error:page');
		if($show && $rs['url']){_Header($rs['url']);return;}
		$_urlArray	= $rs['attr'] == 'page'?array('link'=>$rs['dir'],'url'=>$rs['url'],'domain'=>$rs['domain']):array('id'=>$rs['id'],'link'=>$rs['dir'],'url'=>$rs['url'],'domain'=>$rs['domain']);
		$rs['url']	= $rs['attr'] == 'page'?$this->iurl('page',$_urlArray):$this->iurl('list',$_urlArray);
		$rs['link']	= "<a href='{$rs['url']}'>{$rs['name']}</a>";
		$rs['nav']	= $this->shownav($rs['id']);
//		$this->result=$rs;
		$this->assign('sort',$rs);
		if($show){
			$act && $this->jumptohtml($rs['attr'] == 'page'?$this->iurl('page',$_urlArray,'',iPATH):$this->iurl('list',$_urlArray,'',iPATH),$rs['url']);
			switch ($rs['attr']){
			   case 'channel':	return $this->iPrint($rs['tpl_index'],'channel');	break;
			   case 'list':		return $this->iPrint($rs['tpl_list'],'list');	break;
			   case 'page':		return $this->iPrint($rs['tpl_index'],'page');	break;
			}
		}
	}
	//Show
	Function Show($argv,$page=1,$tpl=true){
		$catalog=$this->cache('catalog.cache','include/syscache',0,true);
		$sql=$this->linkmodeSQL('a.id','a.customlink',$argv);
		//$this->mode=="CreateHtml" && $sql.=" and a.url=''";
		$rs=$this->db->getRow("SELECT a.*,d.tpl,d.body,d.subtitle FROM #iCMS@__article as a LEFT JOIN #iCMS@__articledata AS d ON a.id = d.aid WHERE {$sql} AND a.visible ='1'");
		empty($rs) && $this->error('error:page');
		if($catalog[$rs->cid]['ishidden'])	return false;
		if($rs->url){if($this->mode=="CreateHtml"){ return;}else{_header($rs->url);}}
		$rs->catalogdir	= $this->cdir($catalog[$rs->cid]);
		$_urlArray	= array('id'=>$rs->id,'cid'=>$rs->cid,'link'=>$rs->customlink,'url'=>$rs->url,'dir'=>$rs->catalogdir,'domain'=>$catalog[$rs->cid]['domain'],'pubdate'=>$rs->pubdate);
		$_iurlArray	= array('id'=>$rs->id,'link'=>$rs->customlink,'url'=>$rs->url,'dir'=>$rs->catalogdir,'pubdate'=>$rs->pubdate);
		$iHtml		= $this->iurl('show',$_iurlArray,$page-1,iPATH);
		$rs->url	= $this->iurl('show',$_urlArray);
		$tpl && $this->jumptohtml($iHtml,$rs->url);
		$this->get['id']	= $rs->id;
		$this->get['title']	= $rs->title;
		if($this->config['linkmode']=='id'){
			$this->iList($rs->cid,false);
		}elseif($this->config['linkmode']=='title'){
			$this->iList($rs->catalogdir,false);
		}
		if($this->config['ishtm']){
			$rs->hits	= "<script src=\"".$this->config['url']."/action.php?do=hits&id={$rs->id}&action=show\" language=\"javascript\"></script>";
			$rs->digg	= "<script src=\"".$this->config['url']."/action.php?do=digg&id={$rs->id}&action=show\" language=\"javascript\"></script>";
			$rs->comments="<script src=\"".$this->config['url']."/action.php?do=comment&id={$rs->id}\" language=\"javascript\"></script>";
		}
		$picArray	= array();
		preg_match_all("/src=[\"|'| ]+((.*)\.(gif|jpg|jpeg|bmp|png))/isU",$rs->body,$picArray);
		$pA = array_unique($picArray[1]);
		foreach($pA as $key =>$value){
			$iValue = getfilepath(trim($value),iPATH,'+');
			file_exists($iValue) && $rs->photo[]=trim($value);
		}
		$body	=explode('<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>',$rs->body);
		$rs->pagetotal=count($body);
		$nBody	=$body[intval($page-1)];
		$rs->body=$this->keywords($nBody);
		$rs->pagecurrent=$page;
		if($rs->pagetotal>1){
			$rs->pagebreak=($page-1>1)?'<a href="'.$this->iurl('show',$_urlArray,$page-1).'" class="pagebreak" target="_self">上一页</a> ':'<a href="'.$this->iurl('show',$_urlArray).'" class="pagebreak" target="_self">'.$this->language('page:prev').'</a> ';
			for($i=1;$i<=$rs->pagetotal;$i++){
				$cls=$i==$page?"pagebreaksel":"pagebreak";
				$rs->pagebreak.=$i==1?'<a href="'.$this->iurl('show',$_urlArray).'" class="'.$cls.'" target="_self">'.$i.'</a>':'<a href="'.$this->iurl('show',$_urlArray,$i).'" class="'.$cls.'" target="_self">'.$i.'</a>';
			}
			$np=($rs->pagetotal-$page>0)?$page+1:$page;
			$rs->pagebreak.='<a href="'.$this->iurl('show',$_urlArray,$np).'" class="pagebreak" target="_self">'.$this->language('page:next').'</a>';
		}
		$rs->page=array('total'=>$rs->pagetotal,'current'=>$rs->pagecurrent,'break'=>$rs->pagebreak);
	    if($rs->tags){
	    	$tagarray=explode(',',$rs->tags);
	    	if(count($tagarray)>1){
	    		foreach($tagarray AS $tk=>$tag){
	    			if($this->chkTagVisible($tag)){
	    				$rs->tag[$tk]['name']=$tag;
	    				$rs->tag[$tk]['url']=$this->config['url'].'/tag.php?t='.rawurlencode($tag);
	    				$rs->taglink.='<a href="'.$rs->tag[$tk]['url'].'" class="tag" target="_self">'.$rs->tagname[$tk]['name'].'</a> ';
	    			}
	    		}
	    	}else{
	    		if($this->chkTagVisible($tagarray[0])){
	    			$rs->tag[0]['name']=$tagarray[0];
	    			$rs->tag[0]['url']=$this->config['url'].'/tag.php?t='.rawurlencode($tagarray[0]);
	    			$rs->taglink='<a href="'.$this->config['url'].'/tag.php?t='.$rs->tag[0]['url'].'" class="tag" target="_self">'.$tagarray[0].'</a>';
	    		}
	    	}
	    }
	    if($rs->related){
        	$relatedArray=explode("~#~",$rs->related);
        	if($relatedArray)foreach($relatedArray AS $idtitle){
        		list($reid,$retitle)=explode("#|$",$idtitle);
        		($reid && $retitle) && $rel[]=$reid;
        	}
        	if($rel){
	        	$rs->rel=implode(',',$rel);
	        	$this->assign('rel',$rs->rel);
        	}
	    }
		$prers=$this->db->getRow("SELECT * FROM `#iCMS@__article` WHERE `id` < '{$rs->id}' AND `cid`='{$rs->cid}' AND `visible`='1' order by id DESC Limit 1");
		$rs->prev=$prers?'<a href="'.$this->iurl('show',array('id'=>$prers->id,'cid'=>$prers->cid,'link'=>$prers->customlink,'url'=>$prers->url,'dir'=>$rs->catalogdir,'domain'=>$catalog[$rs->cid]['domain'],'pubdate'=>$prers->pubdate)).'" class="prev" target="_self">'.$prers->title.'</a>':$this->language('show:first');
		$nextrs=$this->db->getRow("SELECT * FROM `#iCMS@__article` WHERE `id` > '{$rs->id}'  and `cid`='{$rs->cid}' AND `visible`='1' order by id ASC Limit 1");
		$rs->next=$nextrs?'<a href="'.$this->iurl('show',array('id'=>$nextrs->id,'cid'=>$nextrs->cid,'link'=>$nextrs->customlink,'url'=>$nextrs->url,'dir'=>$rs->catalogdir,'domain'=>$catalog[$rs->cid]['domain'],'pubdate'=>$nextrs->pubdate)).'" class="next" target="_self">'.$nextrs->title.'</a>':$this->language('show:last');
		$this->mode!='CreateHtml'&&$this->db->query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='{$rs->id}' LIMIT 1");
		$rs->link="<a href='{$rs->url}'>{$rs->title}</a>";		
		$this->result	= $rs;

		$this->assign(array(
			'id'		=>$rs->id,//文章ID
			'pic'		=>$rs->pic,//缩略图
			'url'		=>$rs->url,//URL
			'link'		=>$rs->link,//link
			'title'		=>$rs->title,//标题
			'keywords'	=>$rs->keywords,//关键字
			'description'=>$rs->description,//简介
			'source'	=>$rs->source,//出处
			'author'	=>$rs->author,//作　者
			'userid'	=>$rs->userid,//发布者ID
			'postype'	=>$rs->postype,//发布者类型 0用户
			'pubdate'	=>$rs->pubdate,//日期
			'subtitle'	=>$rs->subtitle,//标题
			'body'		=>$rs->body,//内容
			'pagetotal'=>$rs->pagetotal,//分页
			'pagecurrent'=>$rs->pagecurrent,//当前页码
			'pagebreak'=>$rs->pagebreak,//分页
			'hits'		=>$rs->hits,//点击数
			'digg'		=>$rs->digg,//点击数
			'comments'	=>$rs->comments,//回复数
	    	'tag'		=>$rs->tag,//标签 3.1
	    	'taglink'	=>$rs->taglink,//标签 3.1.2
			'prev'		=>$rs->prev,//上下一篇
			'next'		=>$rs->next//下一篇
		));
		$this->assign('show',(array)$rs);//3.1 所有内容

		if($tpl){
			$tpl=empty($rs->tpl)?$catalog[$rs->cid]['tpl_contents']:$rs->tpl;
			return $this->iPrint($tpl,'show');
		}
	}

	//content
	function content($mId,$argv,$tpl=true){
		$catalog	= $this->cache('catalog.cache','include/syscache',0,true);
		$sql		= $this->linkmodeSQL('id','customlink',$argv);
		$__MODEL__	= $this->cache('model.id','include/syscache',0,true);
		$model		= $__MODEL__[$mId];
		$__TABLE__	= $model['table'].'_content';		
		$rs=$this->db->getRow("SELECT * FROM `#iCMS@__$__TABLE__` WHERE {$sql} AND `visible` ='1'");
		empty($rs) && $this->error('error:page');
		if($catalog[$rs->cid]['ishidden'])	return false;
		$rs->catalogdir	= $this->cdir($catalog[$rs->cid]);
		$_urlArray	= array('mId'=>$mId,'id'=>$rs->id,'cid'=>$rs->cid,'link'=>$rs->customlink,'dir'=>$rs->catalogdir,'domain'=>$catalog[$rs->cid]['domain'],'pubdate'=>$rs->pubdate);
		$_iurlArray	= array('mId'=>$mId,'id'=>$rs->id,'link'=>$rs->customlink,'dir'=>$rs->catalogdir,'pubdate'=>$rs->pubdate);
		$iHtml		= $this->iurl('show',$_iurlArray,$page-1,iPATH);
		$rs->url	= $this->iurl('content',$_urlArray);
		$tpl && $this->jumptohtml($iHtml,$rs->url);
		$rs->mid		= $mId;
		$this->get['id']	= $rs->id;
		$this->get['title']	= $rs->title;
		if($this->config['linkmode']=='id'){
			$this->iList($rs->cid,false);
		}elseif($this->config['linkmode']=='title'){
			$this->iList($rs->catalogdir,false);
		}
		if($this->config['ishtm']){
			$rs->hits	= "<script src=\"".$this->config['url']."/action.php?do=hits&mid={$mId}&id={$rs->id}&action=show\" language=\"javascript\"></script>";
			$rs->digg	= "<script src=\"".$this->config['url']."/action.php?do=digg&mid={$mId}&id={$rs->id}&action=show\" language=\"javascript\"></script>";
			$rs->comments="<script src=\"".$this->config['url']."/action.php?do=comment&mid={$mId}&id={$rs->id}\" language=\"javascript\"></script>";
		}
	    if($rs->tags){
	    	$tagarray=explode(',',$rs->tags);
	    	if(count($tagarray)>1){
	    		foreach($tagarray AS $tk=>$tag){
	    			if($this->chkTagVisible($tag)){
		    			$rs->tag[$tk]['name']=$tag;
		    			$rs->tag[$tk]['url']=$this->config['url'].'/tag.php?t='.rawurlencode($tag).'&mid='.$mId;
		    			$rs->taglink.='<a href="'.$rs->tag[$tk]['url'].'" class="tag" target="_self">'.$rs->tag[$tk]['name'].'</a> ';
	    			}
	    		}
	    	}else{
	    		if($this->chkTagVisible($tagarray[0])){
	    			$rs->tag[0]['name']=$tagarray[0];
	    			$rs->tag[0]['url']=$this->config['url'].'/tag.php?t='.rawurlencode($tagarray[0]).'&mid='.$mId;
	    			$rs->taglink='<a href="'.$this->config['url'].'/tag.php?t='.$rs->tag[0]['url'].'" class="tag" target="_self">'.$tagarray[0].'</a>';
	    		}
	    	}
	    }
		if($fArray	= explode(',',$model['field'])){
			$SField	= getSystemField();
			$diff	= array_diff_values($fArray,$SField);
		    if($diff['+'])foreach($rs AS $field=>$val){
		    	if(in_array($field,$diff['+'])){
		    		$FV	= getFieldValue($mId,$field,$val);
		    		$FV!==Null && $rs->$field	= $FV;
		    	}
		    }
		}

		$prers=$this->db->getRow("SELECT * FROM `#iCMS@__$__TABLE__` WHERE `id` < '{$rs->id}' AND `cid`='{$rs->cid}' AND `visible`='1' order by id DESC Limit 1");
		$rs->prev=$prers?'<a href="'.$this->iurl('content',array('mId'=>$mId,'id'=>$prers->id,'cid'=>$prers->cid,'link'=>$prers->customlink,'dir'=>$rs->catalogdir,'domain'=>$catalog[$rs->cid]['domain'],'pubdate'=>$prers->pubdate)).'" class="prev" target="_self">'.$prers->title.'</a>':$this->language('content:first');
		$nextrs=$this->db->getRow("SELECT * FROM `#iCMS@__$__TABLE__` WHERE `id` > '{$rs->id}'  and `cid`='{$rs->cid}' AND `visible`='1' order by id ASC Limit 1");
		$rs->next=$nextrs?'<a href="'.$this->iurl('content',array('mId'=>$mId,'id'=>$nextrs->id,'cid'=>$nextrs->cid,'link'=>$nextrs->customlink,'dir'=>$rs->catalogdir,'domain'=>$catalog[$rs->cid]['domain'],'pubdate'=>$nextrs->pubdate)).'" class="next" target="_self">'.$nextrs->title.'</a>':$this->language('content:last');
		$this->mode!='CreateHtml'&&$this->db->query("UPDATE `#iCMS@__$__TABLE__` SET hits=hits+1 WHERE `id` ='{$rs->id}' LIMIT 1");
		$rs->link="<a href='{$rs->selfurl}'>{$rs->title}</a>";
		$this->result	= $rs;

		$this->assign(array(
			'id'		=>$rs->id,//内容ID
			'mid'		=>$rs->mid,//模型ID
			'selfurl'	=>$rs->selfurl,//URL
			'link'		=>$rs->link,//URL
			'title'		=>$rs->title,//标题
			'userid'	=>$rs->userid,//发布者ID
			'postype'	=>$rs->postype,//发布者类型 0用户
			'pubdate'	=>$rs->pubdate,//日期
			'hits'		=>$rs->hits,//点击数
			'digg'		=>$rs->digg,//点击数
			'comments'	=>$rs->comments,//回复数
	    	'tag'		=>$rs->tag,//标签 3.1
	    	'taglink'	=>$rs->taglink,//标签 3.1.2
			'prev'		=>$rs->prev,//上下一篇
			'next'		=>$rs->next//下一篇
		));
		$this->assign('content',	(array)$rs);//3.1 所有内容

		if($tpl){
			$tpl=empty($rs->tpl)?$catalog[$rs->cid]['tpl_contents']:$rs->tpl;
			return $this->iPrint($tpl,'content');
		}
	}
	//tag
	function tag($argv){
		empty($argv) && alert($this->language('tag:empty'));
		$this->actionSQL=" AND `tags` REGEXP '[[:<:]]".preg_quote($argv, '/')."[[:>:]]'";
		$this->assign("tag",$argv);
		return $this->iPrint("iSYSTEM","tag");
	}
	//search
	function search($type='title',$keyword='',$sortid=""){
		$keyword==''&& alert($this->language('search:keywordempty'));
		empty($type) && alert($this->language('search:typempty'));
		$ikeyword=$keyword;
		$keyword=str_replace(array('%','_'),array('\%','\_'),$keyword);
		switch ($type) {
		   case 'title':
			   $this->actionSQL=" AND `title` like '%{$keyword}%' ";
		   break;
		   case 'content':
			   $this->actionSQL=" And CONCAT(title,keywords,description) like '%{$keyword}%' ";
		   break;
		   case 'author':
			   $this->actionSQL=" AND `author` like '%{$keyword}%' ";
		   break;
		   default:$field='title';
		}
		if($sortid){
			$this->actionSQL.=" AND `cid`='{$sortid}'";
			$this->assign("sortid",$sortid);
		}
		if($id=$this->db->getValue("SELECT id FROM `#iCMS@__search` where `search`='{$keyword}'")){
			$this->db->query("UPDATE `#iCMS@__search` SET `times`=times+1 WHERE `id`='$id'");
		}else{
			$this->db->query("INSERT INTO `#iCMS@__search` (`search`,`times`,`addtime`) VALUES ('{$keyword}','0','".time()."')");
		}
		$this->assign("keyword",$ikeyword);
		$this->iPrint("iSYSTEM","search");
	}
	function comment($aid=0,$mId=0,$sortId=0){
		if(empty($aid)){
			$this->iList($sortId,false);
			$this->iPrint("iSYSTEM","comment.sort");
		}else{
			$catalog=$this->cache('catalog.cache','include/syscache',0,true);
			$total=$this->db->getValue("SELECT count(*) FROM `#iCMS@__comment` WHERE `mid`='$mId' and `sortId`='$sortId' and `isexamine`='1' AND aid='{$aid}'");
			$this->assign("total",$total);
			if(empty($mId)){
				$this->Show($aid,1,false);
				$this->iPrint("iSYSTEM","comment.article");
			}else{
				$this->content($mId,$aid,false);
				$this->iPrint("iSYSTEM","comment.content");
			}
		}
	}
	function message(){
		$this->iPrint("iSYSTEM","message");
	}
//-------------------------------------------------------------------------------------------------
	function cache($cacheName,$cacheDir="",$cacheLevel='',$iscachegzip=''){
		$this->cacheDir=$cacheDir;
		if($this->config['iscache']||$this->cacheDir!=""){
			if(isset($this->iCache)) unset($this->iCache);
				$this->iCache = new cache(array(
							'dirs'		=> iPATH.(empty($this->cacheDir)?$this->config['cachedir']:$this->cacheDir),
							'level'		=> $cacheLevel=='' ?$this->config['cachelevel']:$cacheLevel,
							'compress'	=> $iscachegzip==''?$this->config['iscachegzip']:$iscachegzip
				));
		}
		if($cacheName){
			return is_array($cacheName)?$this->iCache->get_multi($cacheName):$this->iCache->get($cacheName);
		}else{
			return $this;
		}
	}
	function addcache($cacheName,$rs,$cachetime="-1"){
		($this->config['iscache']||$this->cacheDir!="") && $this->iCache->add($cacheName,$rs,($cachetime!="-1"?$cachetime:$this->config['cachetime']));
	}
	function chkTagVisible($name){
		$name=substr(md5($name),8,16);
		$cache=$this->cache('tags.cache','include/syscache',0,true);
		return $cache[$name]['visible'];
	}
	//---plugins---
	Function value($key,$value){
		$this->assign($key,$value);
	}
	Function clear($key){
		$this->clear_assign($key);
	}
	Function output($tpl,$td='',$res=''){
//		$res=='' && $res='file:';
		$this->display($res.$td.'/'.$tpl.".htm",$this->cacheID,$this->compileID);
	}
	//------------------------------------
	Function tpl($tpl){
		if($this->config['ishtm'] && $this->mode=='CreateHtml'){
			return $this->fetch($tpl,$this->cacheID,$this->compileID);
		}else{
			$this->display($tpl,$this->cacheID,$this->compileID);
		}
	}
	Function iPrint($tpl,$p='index'){
		empty($tpl) && $this->error('error:notpl',$tpl);
		strpos($tpl,'{TPL}')!==false && $tpl = str_replace('{TPL}',$this->config['template'],$tpl);
		if(file_exists($this->template_dir."/".$tpl)){
			return $this->tpl($tpl);
		}elseif($this->config['template'] && file_exists($this->template_dir."/".$this->config['template']."/{$p}.htm")){
			return $this->tpl($this->config['template']."/{$p}.htm");
		}elseif($tpl=='iSYSTEM'){
			return $this->tpl("system/{$p}.htm");
		}
	}
	function jumptohtml($fp,$url=''){
		$this->config['ishtm'] && $this->mode!='CreateHtml' && file_exists($fp) && _Header(path($url));
	}
	function language($string){
		$langFile=$this->template_dir.'/'.$this->config['template'].'/language/'.$this->config['language'].'.php';
		if(file_exists($langFile)){
			include($langFile);
		}else{
			include($this->template_dir.'/system/language/'.$this->config['language'].'.php');
		}
		if(strpos($string,':')!==false){
			list($s,$k)=explode(':',$string);			
			return isset($language[$s][$k])?$language[$s][$k]:$string;
		}else{
			return isset($language[$string])?$language[$string]:$string;
		}
	}
	Function error($string,$tpl=""){
//		header('HTTP/1.1 404 Not Found');
		$this->assign('TPL_PATH',$this->config['dir']."templates/".$tpl);
		$this->assign('error',$this->language($string));
		return $this->output('error','system');
	}
	function linkmodeSQL($ifield,$tfield,$argv){
		if($this->config['linkmode']=='id'){
			$sql="{$ifield}='{$argv}'";
		}elseif($this->config['linkmode']=='title'){
			$sql="{$tfield}='{$argv}'";
		}
		return $sql;
	}
	function dirule($a){
		switch($this->config['htmdircreaterule']){
			case "0":$R=$a['dir'];break;
			case "1":$R=get_date($a['pubdate'],'Y-m-d');break;
			case "2":
				$oid= abs(intval($a['id']));
				$id = sprintf("%08s",$oid);
				$idR= substr($id, 0, 4).'/'.substr($id, 4, 2);
				$R=str_replace(array('Y','y','m','n','d','j','C','ID'),
				array(get_date($a['pubdate'],'Y'),get_date($a['pubdate'],'y'),get_date($a['pubdate'],'m'),get_date($a['pubdate'],'n'),get_date($a['pubdate'],'d'),get_date($a['pubdate'],'j'),$a['dir'],$idR),
				$this->config['customhtmdircreaterule']);
			break;
			case "3":
				$oid= abs(intval($a['id']));
				$id = sprintf("%08s",$oid);
				$R	= substr($id, 0, 4).'/'.substr($id, 4, 2);
			break;
		}
		return $R;
	}
	function filerule($a){
		switch($this->config['htmnamerule']){
			case "0":$R=$a['id'];break;
			case "pinyin":$R=$a['link'];break;
			case "pubdate":$R=$a['pubdate'];break;
			case "ids":$R=sprintf("%08s",abs(intval($a['id'])));break;
			default:$R=$a['id'];
		}
		return $R;
	}
	function taghtmrule($a){
		switch($this->config['taghtmrule']){
			case "id":$R=$a['id'];break;
			case "pinyin":$R=$a['link'];break;
			case "md5":$R=substr(md5($a['name']),8,16);break;
			default:$R=$a['id'];
		}
		return $R;
	}
	function domain($cid="0",$D='',$Cdir=false){
		$cache	= $this->cache(array('catalog.parent','catalog.cache'),'include/syscache',0,true);
		$cParent= $cache['catalog.parent'];
		$cCache	= $cache['catalog.cache'];
		$rootid = $cParent[$cid];
		$C		= $cCache[$cid];
		if($Cdir){
			$rootid && $domain.=$this->domain($rootid,$D,true);
			$domain.='/'.$C['dir'];
		}else{
			if($C['domain']){
				return 'http://'.$C['domain'].'/';
			}else{
				$this->config['sortdirrule']=='parent' && $rootid && $domain=$this->domain($rootid,$D);
				if(empty($domain)){
					$domain.=(empty($D)?$this->config['url'].'/':$D).$this->config['htmdir'];
				}
				$C && $domain.=$C['dir'].'/';
			}
		}
		return $domain;
	}
	function iurl($uri,$a=array('id'=>'','link'=>'','url'=>'','dir'=>'','pubdate'=>''),$p='',$D=''){
		if($this->config['ishtm']){
			$url=empty($D)?$this->config['url'].'/':$D;
			switch($uri){
				case 'list':
					$url=$this->domain($a['id'],$D);
				break;
				case 'show':
					$url=$this->domain($a['cid'],$D).$this->dirule($a)."/".$this->filerule($a).($p?"_{$p}":"").$this->config['htmlext'];
//					var_dump($url,$a['dir']);
//					empty($D) && strpos($url,$this->config['url'])===false && $url=str_replace('/'.$a['dir'].'/','/',$url);
					empty($D) && isset($a['domain']) && $url=str_replace('/'.$a['dir'].'/','/',$url);
//					var_dump('/'.$a['dir'].'/',$url);
				break;
				case 'content':
					$url=$this->domain($a['cid'],$D).$this->dirule($a)."/".$a['mId']."_".$this->filerule($a).($p?"_{$p}":"").$this->config['htmlext'];
//					empty($D) && strpos($url,$this->config['url'])===false && $url=str_replace('/'.$a['dir'].'/','/',$url);
					empty($D) && isset($a['domain']) && $url=str_replace('/'.$a['dir'].'/','/',$url);
				break;
				case 'tag'://id pinyin md5 php
					if($this->config['tagrule']=="dir"){
						$url.=$this->config['taghtmdir'].$this->taghtmrule($a);
					}elseif($this->config['tagrule']=="file"){
						$url.=$this->config['taghtmdir'].$this->taghtmrule($a).$this->config['htmlext'];
					}else{
						$url.='tag.php?t='.rawurlencode($a['name']);
					}
				break;
				case 'page':
					if($this->config['pagerule']=="dir"){
						$url.=$this->config['pagehtmdir'].$a['link'];
					}elseif($this->config['pagerule']=="file"){
						$url.=$this->config['pagehtmdir'].$a['link'].$this->config['htmlext'];
					}
				break;
			}
			empty($D) && $url=path($url);
			$a['domain'] && in_array($uri,array('list','page')) && $url='http://'.$a['domain'];
		}else{
			$url=empty($D)?$this->dir:$D;
			if($this->config['linkmode']=='id'){
				$url.=$uri.'.php?id='.$a['id'];
			}elseif($this->config['linkmode']=='title'){
				$url.=$uri.'.php?t='.$a['link'];
			}
			$uri=='content' && $url.='&mid='.$a['mId'];
			$uri=='tag'	&& $url=$this->dir.'tag.php?t='.rawurlencode($a['name']);
			$uri=='page'	&& $url=$this->dir.'index.php?p='.$a['link'];
			
			$p && $url.='&p='.$p;
		}
		if($a['url']){
			if($uri=='show'){
				$url=$this->config['url']."/link.php?id=".$a['id']."&url=".urlencode($a['url']);
			}elseif(in_array($uri,array('list','page'))){
				$url=$a['url'];
			}
		}
		return $url;
	}
	function cdir($c){
		if($this->config['sortdirrule']=='parent'){
			$cdir=substr($this->domain($c['id'],'',true), 1);
		}else{
			$cdir=isset($c['link'])?$c['link']:$c['dir'];
		}
		return $cdir;
	}
	function cper($c){
		return str_replace(array('CID','C'),array($c['id'],$c['dir']),$this->config['sortpagePre']);
	}
	//翻页函数
	function multi($array){
		include_once iPATH.'include/multi.class.php';
		$multi=new multi($array);
		if($multi->totalpage>1){
			$this->assign($array['pagenav'],$multi->show($pnstyle));
			$this->assign('pageA',array('total'=>$multi->totalpage,'current'=>$multi->nowindex,'break'=>$multi->show($pnstyle)));
			$this->assign('multi',$multi);
		}
		$offset	=$multi->offset;
		unset($multi);
		return $offset;
	}
	function shownav($cid="0"){
		$cache	= $this->cache(array('catalog.parent','catalog.cache'),'include/syscache',0,true);
		$cParent= $cache['catalog.parent'];
		$cCache	= $cache['catalog.cache'];
		$rootid = $cParent[$cid];
		$c		= $cCache[$cid];
		$c['sorturl']=$c['attr']=='page'?$this->iurl('page',array('link'=>$c['dir'],'domain'=>$c['domain'])):$this->iurl('list',array('id'=>$c['id'],'link'=>$c['dir'],'url'=>$c['url'],'domain'=>$c['domain']));
		$c['sortlink']="<a href='{$c['sorturl']}'>{$c['name']}</a>";
		$rootid && $nav.=$this->shownav($rootid).$this->language('navTag');
		$nav.=$c['sortlink'];
		return $nav;
	}
	function keywords($content){
		$keywords=$this->cache('keywords.cache','include/syscache',0,true);
		$content = stripslashes($content);
		$_count=count($keywords);
		for($i=0;$i<$_count;$i++){
			$searcharray[]=$keywords[$i]['keyword'];
			$replacearray[]=$keywords[$i]['replace'];
		}
		$content=str_replace($searcharray, $replacearray, $content);
		return $content;
//		return addslashes($content);
	}
}
?>