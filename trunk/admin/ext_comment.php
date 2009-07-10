<?php
defined('IN_ADMIN') or die('Forbidden');
require_once(D_P.'data/cache/cate.php');

$action = GetGP('action');
$com = new CommentManage();
$com->doIt();

class CommentManage{
	var $moduledb;
	var $catedb;
	var $mid;
	/**
	 * 评论图片的路径
	 *
	 * @var string
	 */
	var $basedir;

	function __construct(){
		global $moduledb,$catedb,$mid;
		$this->moduledb = $moduledb;
		$this->catedb = $catedb;
		$this->basedir = "images/comment/";
		$this->mid = $mid ? $mid : 1;
		//$this->table = 'cms_content'.$this->mid;
	}

	function CommentManage(){
		$this->__construct();
	}

	function doIt(){
		global $action;
		!$action && $action='show';
		switch ($action) {
			case 'show':
				$this->ShowComment();
				break;
			case 'delall':
				$this->DelComment();
				break;
			case 'delsingle':
				$this->DelSingle();
				break;
			case 'search':
				$this->Search();
				break;
			case 'face':
				$this->ShowFace();
				break;
			case 'addface':
				$this->AddFace();
				break;
			case 'delface':
				$this->DelFace();
				break;
			case 'edit':
				$this->EditFace();
				break;
			default:
				break;
		}
	}

	function ShowComment(){
		global $db,$sys,$basename,$action;
		$page = (int)GetGP('page');
		$rt = $db->get_one("SELECT COUNT(*) AS total FROM cms_comment WHERE mid='$this->mid'");
		$total = $rt['total'];
		$num = 20;
		if(!$page or $page<1) $page=1;
		$start = ($page-1)*$num;
		$numofpage = ceil($total/$num);
		$pages = numofpage($total,$page,$numofpage,$basename.'&mid='.$this->mid.'&');
		$rs = $db->query("SELECT c.*,t.cid,t.url,t.title FROM cms_comment c LEFT JOIN cms_contentindex t USING(tid) WHERE c.mid='$this->mid' ORDER BY c.postdate DESC LIMIT $start,$num");
		$result = array();
		while ($com = $db->fetch_array($rs)) {
			if($this->catedb[$cid]['htmlpub']){
				$com['url']=$sys['htmdir'].'/'.$com['url'];
			}else{
				$com['url']='view.php?tid='.$com['tid'].'&cid='.$com['cid'];
			}
			$com['message'] = str_replace("::wind::","<br />",$com['message']);
			$com['title'] = substrs($com['title'],20);
			$com['postdate'] = get_date($com['postdate']);
			$result[] = $com;
		}
		${'select_'.$this->mid} = "selected";
		require PrintEot('header');
		require PrintEot('ext_comment');
		adminbottom();
	}

	function DelComment(){
		global $db;
		$ids = GetGP('ids');
		if(empty($ids)) Showmsg('ext_noselect');
		foreach ($ids as $tid=>$id_a){
			$tid = intval($tid);
			$new_i = array();
			foreach ($id_a as $i){
				$new_i[] = intval($i);
			}
			$new_i = implode(',',$new_i);
			$db->update("DELETE FROM cms_comment WHERE id IN($new_i) AND tid='$tid'");
			$rows_num = mysql_affected_rows();
			$db->update("UPDATE cms_contentindex SET comnum=comnum-$rows_num WHERE tid='$tid'");
		}
		adminmsg('ext_delcommentok');
	}

	function DelSingle(){
		global $db;
		$tid = (int)GetGP('tid');
		$id = (int)GetGP('id');
		$db->update("DELETE FROM cms_comment WHERE id='$id'");
		$db->update("UPDATE cms_contentindex SET comnum=comnum-1 WHERE tid='$tid'");
		adminmsg('ext_delcommentok');
	}

	function Search() {
		global $db,$sys,$basename,$action;
		$tid = (int)GetGP('tid');
		$page = (int)GetGP('page');
		$rt = $db->get_one("SELECT COUNT(*) AS total FROM cms_comment WHERE tid='$tid'");
		$total = $rt['total'];
		$num = 20;
		if(!$page or $page<1) $page=1;
		$start = ($page-1)*$num;
		$numofpage = ceil($total/$num);
		$pages = numofpage($total,$page,$numofpage,$basename.'&tid='.$tid.'&action=search&');
		$rs = $db->query("SELECT c.*,t.cid,t.url,t.title FROM cms_comment c LEFT JOIN cms_contentindex t USING(tid) WHERE c.tid='$tid' ORDER BY c.postdate DESC LIMIT $start,$num");
		$result = array();
		while ($com = $db->fetch_array($rs)) {
			if($this->catedb[$cid]['htmlpub']){
				$com['url']=$sys['htmdir'].'/'.$com['url'];
			}else{
				$com['url']='view.php?tid='.$com['tid'].'&cid='.$com['cid'];
			}
			$com['message'] = str_replace("::wind::","<br />",$com['message']);
			$com['message'] = substrs($com['message'],20);
			$com['title'] = substrs($com['title'],20);
			$com['postdate'] = get_date($com['postdate']);
			$result[] = $com;
		}
		require PrintEot('header');
		require PrintEot('ext_comment');
		adminbottom();
	}

	function ShowFace(){
		global $db,$action,$basename,$sys;
		$rs = $db->query("SELECT * FROM cms_commentface ORDER BY taxis DESC,id");
		$smiles_new = $smiles_old = $result = array();
		$picext = array("gif","bmp","jpeg","jpg","png");
		while ($face = $db->fetch_array($rs)) {
			$smiles_old[] = $face['facepath'];
			$face['facepath']=$sys['url']."/".$this->basedir.$face['facepath'];
			$result[] = $face;
		}
		$fp=opendir(R_P."/".$this->basedir);
		$i=0;
		while($smilefile = readdir($fp)){
			if(in_array(strtolower(end(explode(".",$smilefile))),$picext)){
				if(!in_array($smilefile,$smiles_old)){
					$i++;
					$smiles_new[$i]['path']=$smilefile;
					$smiles_new[$i]['src']=$this->basedir."$smilefile";
				}
			}
		}
		closedir($fp);
		require PrintEot('header');
		require PrintEot('ext_comment');
		adminbottom();
	}

	function AddFace(){
		global $db,$basename;
		$faces = GetGP('add');
		empty($faces) && Showmsg('ext_facepathempty');
		foreach($faces as $facepath) {
			$facepath = Char_cv($facepath);
			if(empty($facepath)) continue;
			if($db->get_one("SELECT id FROM cms_commentface WHERE facepath='$facepath'")){
				continue;
			}
			if(!file_exists(R_P.$this->basedir.$facepath)) continue;
			if(ereg("^(\.|/)",$facepath)) continue;
			$db->update("INSERT INTO cms_commentface SET facepath='$facepath'");
		}

		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('comment');
		adminmsg('ext_faceaddok',"$basename&action=face");
	}

	function DelFace(){
		global $db,$basename;
		$id = (int)GetGP('id');
		$db->update("DELETE FROM cms_commentface WHERE id='$id'");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('comment');
		adminmsg('ext_facedelok',"$basename&action=face");
	}

	function EditFace(){
		global $db,$basename;
		$taxis = GetGP('taxis');
		foreach ($taxis as $id=>$t){
			if(!is_numeric($t)) continue;
			$faceintro = Char_cv($_POST['faceintro'][$id]);
			$db->update("UPDATE cms_commentface SET taxis='$t',faceintro='$faceintro' WHERE id='$id'");
		}
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('comment');
		adminmsg('ext_faceeditdok',"$basename&action=face");
	}
}
?>