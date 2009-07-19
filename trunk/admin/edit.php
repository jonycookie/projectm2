<?php
!defined('IN_ADMIN') && die('Forbidden');

require_once(R_P.'require/class_content.php');
require_once(D_P.'data/cache/cate.php');
require_once(R_P.'require/chinese.php');
InitGP(array("action","inputname","inputtype","type","page","step"));
/**
 * 管理内容，添加、编辑。
*/
if($admin_name!=$manager && $admindb['privcate'] && $cid && !in_array($cid,$admindb['privcate'])){
	Showmsg('privilege');
}
class Edit extends Content {
	var $mid;
	var $cid;

	function __construct(){
		global $action;
		!$action && $action='add';
		in_array($action,array('add','edit')) && $this->show();
		$this->selectMethod($action);
	}

	function Edit(){
		$this->__construct();
	}

	function show(){
		global $action,$cid,$timestamp,$catedb;
		$defaultcid = GetCookie('defaultcid');
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select = $cate->tree();
		if(!$defaultcid && !$cid){
			require PrintEot('edit');
			adminbottom();
		}
		!$cid && $cid = $defaultcid;

		if($catedb[$cid]['mid']<0){
			Showmsg('pub_cannotadd'); //调用类型的模型不能添加内容
		}elseif ($catedb[$cid]['mid']==0){
			Showmsg('pub_linkadd'); //外部栏目无法添加内容
		}else{
			Cookie('defaultcid',$cid,$timestamp+31536000);
		}
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected=\"selected\"",$cate_select);
		$catename = $catedb[$cid]['cname'];
		$this->mid = $catedb[$cid]['mid'];
		$this->cid = $cid;
		$this->catedb = $catedb;
	}

	function selectMethod($action){
		switch ($action){
			case 'add':
				$this->addContent();
				break;
			case 'edit':
				$this->editContent();
				break;
			case 'addimage':
				$this->addAttach('img');
				break;
			case 'addmedia':
				$this->addAttach('flash');
				break;
			case 'upload': //编辑的时候上传附件
				$this->uploadAttach();
				break;
			case 'fckupload':
				$this->FckUpload();
				break;
			case 'addattach':
				$this->addAttach('attach');
				break;
			case 'selecttids':
				$this->selecttids();
				break;
			case 'searchtids':
				$this->searchtids();
				break;
			case 'selectTpl':
				$this->selectTpl();
				break;
			default:
				$this->addContent();
				break;
		}
	}

	function addContent(){
		global $step,$action,$basename;
		parent::__construct($this->mid);
		$cid = $this->cid;
		if(!$step){
			require_once(R_P.'require/class_cate.php');
			require_once(R_P.'require/color.php');
			$cate = new Cate();
			$cate_select = $cate->tree();
			$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected=\"selected\"",$cate_select);
			$inputArea = $this->inputArea();
			$hottag = $this->hottag();
			foreach ($colors as $c){
				$color_select .= "<option value=\"$c\" style=\"background-color:$c;color:$c\">$c</option>";
			}
			require PrintEot('edit');
			adminbottom();
		}elseif ($step==2){
			empty($_POST['title']) && Showmsg('pub_notitle');
			$_POST['tagsid'] = $this->tags();
			if($_POST['postdate']) {
				$_POST['postdate'] = PwStrtoTime($_POST['postdate']);
			}else {
				$_POST['postdate'] = $GLOBALS['timestamp'];
			}
			$this->InsertData($_POST,$this->cid);
			adminmsg('pub_addok');
		}
	}

	function editContent(){
		global $step,$tid,$admin_file,$action,$basename;
		$cid = $this->cid;
		parent::__construct($this->mid);
		if(!$step){
			require_once(R_P.'require/color.php');
			require_once(R_P.'require/class_cate.php');
			$cate = new Cate();
			$cate_select = $cate->tree();
			$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected=\"selected\"",$cate_select);
			$inputArea	= $this->editArea($tid);
			$tags		= $this->getTags($tid);
			$aids		= $this->getAttach($tid);
			$aids		= implode(',',$aids);
			$hottag		= $this->hottag();
			$digest		= $inputArea['digest'];
			$template	= $inputArea['template'];
			$linkurl	= $inputArea['linkurl'];
			$postdate	= $inputArea['postdate'];
			$photo		= $inputArea['photo'];
			$titlestyle = unserialize($inputArea['titlestyle']);
			${'digest_'.$digest} = 'checked';
			$titleb_check = $titlestyle['titleb'] ? 'checked=\"checked\"' : '';
			$titleii_check= $titlestyle['titleii'] ? 'checked=\"checked\"' : '';
			$titleu_check = $titlestyle['titleu'] ? 'checked=\"checked\"' : '';
			foreach ($colors as $c){
				if($c == $titlestyle['titlecolor']){
					$color_select .= "<option value=\"$c\" style=\"background-color:$c;color:$c\" selected=\"selected\">$c</option>";
				}else{
					$color_select .= "<option value=\"$c\" style=\"background-color:$c;color:$c\">$c</option>";
				}
			}
			unset($inputArea['template'],$inputArea['digest'],$inputArea['linkurl'],$inputArea['photo'],$inputArea['template'],$inputArea['titlestyle'],$inputArea['postdate']);
			require PrintEot('edit');
			adminbottom();
		}elseif ($step==2){
			empty($_POST['title']) && Showmsg('pub_notitle');
			$_POST['tagsid'] = $this->tags();
			if($_POST['postdate']) {
				$_POST['postdate'] = PwStrtoTime($_POST['postdate']);
			}else {
				$_POST['postdate'] = $GLOBALS['timestamp'];
			}
			$this->UpdateData($_POST,$_POST['tid']);
			if($GLOBALS['catedb'][$cid]['htmlpub']){
				$tid = intval($_POST['tid']);
				adminmsg('pub_edithtmlok',"$admin_file?adminjob=content&action=whole&cid=$cid&tid=$tid&job=pubupdate");
			}else{
				adminmsg('pub_editok',"$admin_file?adminjob=content&cid=$this->cid&action=view");
			}
		}
	}

	/**
	 * 插入附件资源
	 *
	 * @param string $type
	 * $type:attach：附件，img：图片附件
	 */

	function addAttach($atttype='attach'){ //插入附件资源
		global $inputname,$inputtype,$basename,$type,$page,$sys;
		require_once R_P.'require/class_attach.php';
		$attach = new Attach();
		$attach->displaynum = 15;
		$files = $attach->show($page,$type);
		$pages = $attach->pages;
		if($atttype=='img') {
			require PrintEot('edit_addimage');
		}elseif($atttype=='flash') {
			require PrintEot('edit_addmedia');
		}else{
			require PrintEot('edit_addattach');
		}
		adminbottom(0);
	}

	function uploadAttach(){
		global $basename,$inputname,$inputtype;
		require_once(R_P.'require/class_attach.php');
		$attach = new Attach();
		$attach->upload();
		echo '<script type="text/javascript">' ;
		echo 'function goclick(element){if(document.all){parent.document.getElementById(element).click();}else{var evt = document.createEvent("MouseEvents");evt.initEvent("click",true,true);parent.document.getElementById(element).dispatchEvent(evt);parent.location.href=parent.document.getElementById(element).href;}}';
		echo 'alert("Your file has been successfully uploaded!");';
		//echo 'parent.reload.click()';
		echo 'goclick("reload");';
		echo '</script>' ;
		exit ;
		//adminmsg('pub_uploadok',$basename."&inputname=$inputname&inputtype=$inputtype&action=addimage");
	}

	/**
	 * Fck编辑器的专用上传
	 *
	 */
	function FckUpload(){
		global $basename,$inputname,$inputtype;
		require_once(R_P.'require/class_attach.php');
		$attach = new Attach();
		$attach->upload();
		$errorNumber = 0;
		$fileName = $attach->fileName;
		$fileUrl = $GLOBALS['sys']['attachdir'].'/'.$attach->filePath;
		echo '<script type="text/javascript">' ;
		echo 'window.parent.OnUploadCompleted(' . $errorNumber . ',"' . str_replace( '"', '\\"', $fileUrl ) . '","' . str_replace( '"', '\\"', $fileName ) . '", "' . str_replace( '"', '\\"', $customMsg ) . '") ;' ;
		echo '</script>' ;
		exit ;
	}
	/**
	 * 查找文章
	 *
	 */
	function searchtids(){
		global $inputname,$inputtype,$basename,$type,$page,$sys,$moduledb;
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select = $cate->tree();
		require_once R_P.'require/class_search.php';
		$search = new Search();
		$search->doIt();
		$total = count($search->result);
		${'select_'.$search->ordering} ='selected';

		if(!is_numeric($page) || $page<1){
			$page = 1;
		}
		$numofpage = ceil($total/20);
		$page <=0 && $page=1;
		$start = ($page-1)*20;
		$pages = numofpage($total,$page,$numofpage,"admin.php?adminjob=edit&action=searchtids&inputname=$inputname&inputtype=$inputtype&step=2&mid=$search->mid&keyword=$search->keyword&s_type=$search->type&cid=$search->cid&ordering=$search->ordering&keyword_type=$search->keyword_type&searchdate=$search->searchdate&");
		$searchresult = array_slice ($search->result,$start,20);
		$content = $searchresult;
		require PrintEot('edit_search');
		adminbottom(0);
	}

	function selecttids() {
		global $sys,$db,$action,$basename,$catedb,$moduledb,$inputname,$inputtype;
		extract(Init_GP(array('displaynum','page','cid')));
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select = $cate->tree();
		if(!$cid) {
			preg_match('/<option value=\"([\d+])\"/i',$cate_select,$matches);
			$cid = $matches['1'];
		}
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected",$cate_select);
		extract($db->get_one("SELECT * FROM cms_category WHERE cid='$cid'"));
		if(!$cname) Showmsg('pub_cateerror');
		$mid == 0 && Showmsg('pub_nocontent');

		if($mid=='-2' && (!$sys['aggrebbs'] || !$sys['bbs_dbname'])) Showmsg('mod_needaggrebbs');
		if($mid=='-1' && (!$sys['aggreblog']|| !$sys['blog_dbname'])) Showmsg('mod_needaggreblog');

		if(!$displaynum){
			$displaynum = 30;
		}else {
			$numadd = "displaynum=$displaynum&";
		}

		$where = " where:ifpub=1 ";
		$pubadd = "displaytype=1&";

		if(!is_numeric($page) || $page<=0) $page=1;
		$start = ($page-1)*$displaynum;

		require_once(R_P.'require/class_cms.php');
		$cms = new Cms();
		$cms->pageurl="$basename&action=selecttids&inputname=$inputname&inputtype=$inputtype&cid=$cid&$numadd$pubadd&";
		$content = $cms->thread("cid:$cid;num:page-$displaynum;mid:$mid;order:ifpub,postdate DESC;$where");
		$pages = $cms->page;
		require PrintEot('edit_search');
		adminbottom(0);
	}

	function selectTpl(){ //内容页选择模板
		global $user_tplpath,$basename;
		extract(Init_GP(array('direct','inputname','job')));
		require_once(R_P.'require/class_path.php');
		$p = new path(D_P.$user_tplpath);
		$p->viewurl = "$basename&action=selectTpl&inputname=$inputname&";
		$p->fileurl = "insertTpl";
		$p->setDir($direct);
		if($job=='up') $p->up();
		$files = $p->getDir();
		$direct = $p->currentpath;
		require PrintEot('selecttpl');
		adminbottom(0);
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

$edit = new Edit();
?>