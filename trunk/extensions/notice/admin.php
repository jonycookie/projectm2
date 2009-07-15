<?php
defined('IN_EXT') or die('Forbidden');

class Notice{
	function doIt(){
		global $action;
		switch ($action){
			case 'add':
				$this->Add();
				break;
			case 'edit':
				$this->Edit();
				break;
			case 'show':
				$this->Show();
				break;
			case 'del':
				$this->del();
				break;
			case 'tplapi':
				$this->TplAPI();
			default:
				$this->Show();
				break;
		}
	}

	function TplAPI(){
		global $basename,$action;
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function Show(){
		global $db,$basename;
		$notice = array();
		$rs = $db->query("SELECT * FROM cms_notice ORDER BY postdate DESC");
		while ($noticedb = $db->fetch_array($rs)) {
			$noticedb['postdate'] = get_date($noticedb['postdate']);
			$notice[] = $noticedb;
		}
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function Add(){
		global $basename,$action;
		$step = GetGP('step');
		if(!$step){
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		}elseif ($step==2){
			$this->Save('add');
			adminmsg('ext_noticeaddok');
		}
	}

	function Edit(){
		global $db,$basename,$action;
		$step = GetGP('step');
		$nid = (int)GetGP('nid');
		if(!$step){
			@extract($db->get_one("SELECT * FROM cms_notice WHERE nid='$nid'"));
			$title = Char_cv($title);
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		}elseif ($step==2){
			$this->Save('edit');
			adminmsg('ext_noticeeditok');
		}
	}

	function Save($job){
		global $sys,$db,$admin_name,$timestamp;
		$title = GetGP('title');
		$content = GetGP('content');
		empty($title) && Showmsg('ext_noticetitle');
		empty($content) && Showmsg('ext_noticecontent');
		wordsfb($title) && Showmsg('title_wordsfb');
		wordsfb($content) && Showmsg('content_wordsfb');
		if($job=='add'){
			$sql = "INSERT INTO ";
		}else{
			$nid = (int)GetGP('nid');
			$sql = "UPDATE ";
			$sqladd = "WHERE nid='$nid'";
		}
		$sql.="cms_notice SET
			title='$title',
			content='$content',
			author='$admin_name',
			postdate='$timestamp'
			$sqladd
		";
		$db->update($sql);
	}

	function del($nid){
		global $db;
		$nid = (int)GetGP('nid');
		if(is_array($nid)){
			$nids = implode(',',$nid);
			$db->update("DELETE FROM cms_notice WHERE nid IN($nids)");
		}else{
			$nid = (int)$nid;
			$db->update("DELETE FROM cms_notice WHERE nid='$nid'");
		}
		adminmsg('ext_noticedelok');
	}
}

$action = GetGP('action');
$notice = new Notice();
$notice->doIt();
?>