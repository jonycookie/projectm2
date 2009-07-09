<?php
define('UPDATE',true);
require_once('global.php');
InitGP(array('page','type'),'G');
if($type == 'list'){
	if(!$cid) exit;
	!file_exists(D_P.'data/cache/cate_'.$cid.'.php') && exit;
	require_once(D_P.'data/cache/cate_'.$cid.'.php');
	if(!$cateinfo['path']){
		$cateinfo['path'] = $cid;
	}
	if($page>1){
		$listurl = $sys['htmdir'].'/'.$cateinfo['path'].'/index_'.$page.'.'.$sys['htmext'];
	}else{
		$listurl = $sys['htmdir'].'/'.$cateinfo['listurl'];
	}
	if($cateinfo['listpub'] && $timestamp - filemtime($listurl) > $cateinfo['autoupdate']*60){
		require_once(R_P.'require/class_action.php');
		$action = new Action('publist');
		$action->cate($cid);
		$action->doIt();
	}else{
		$upTids = readover(D_P.'data/cache/updatelist_'.$cid.'.cache');
		if($upTids){
			if($upTids=='complete'){
				require_once(R_P.'require/class_action.php');
				$action = new Action('publist');
				$action->cate($cid);
				$action->doIt();
				writeover(D_P.'data/cache/updatelist_'.$cid.'.cache','');
			}else{
				$opnum = $sys['opnum'] ? intval($sys['opnum']) : 30;
				$upTids = explode('|',$upTids);
				$upTid = array_splice($upTids,0,$opnum);
				$upTids = implode('|',$upTids);
				empty($upTids) && $upTids = 'complete';
				writeover(D_P.'data/cache/updatelist_'.$cid.'.cache',$upTids);
				require_once(R_P.'require/class_action.php');
				$action = new Action('pubview');
				$action->cate($cid);
				$action->doIt($upTid);
			}

		}
	}
}elseif ($type == 'index'){
	if($timestamp - filemtime(R_P.'index.html') > $sys['indexupdate']*60){
		InitGP(array('page','type'),'G');
		require_once(R_P.'require/class_action.php');
		$action = new Action('pubindex');
		$action->doIt();
	}
}elseif($type == 'click'){
	if(!$tid || !$cid) exit;
	!file_exists(D_P.'data/cache/cate_'.$cid.'.php') && exit;
	require_once(D_P.'data/cache/cate_'.$cid.'.php');
	if($cateinfo['mid']>0){
		$rs = $db->get_one("SELECT hits,comnum FROM cms_contentindex WHERE tid='$tid'");
	}else if ($cateinfo['mid']==-2) {
		$bbs = newBBS($sys['bbs_type']);
		$hits = $bbs->getField('hits');
		$replies = $bbs->getField('replies');
		$rs = $bbs->mysql->get_one("SELECT $hits AS hits,$replies AS comnum FROM $bbs->table WHERE tid='$tid'");
	}
	!$rs['hits'] && $rs['hits'] = 1;
	!$rs['comnum'] && $rs['comnum'] = 0;

	Cookie($tid,'1',$timestamp+600);
print <<<EOT
if(document.getElementById('hits')!=null) {
	var hits = document.getElementById('hits');
	hits.innerHTML = '{$rs[hits]}';
}

if(document.getElementById('comnum')!=null) {
	var comnum = document.getElementById('comnum');
	comnum.innerHTML = '{$rs[comnum]}';
}
EOT;
	if(!GetCookie($tid)){
		$db->update("UPDATE cms_contentindex SET hits=hits+1 WHERE tid='$tid'");
	}
}elseif ($type == 'listup'){
	if(!$cid) exit;
	!file_exists(D_P.'data/cache/cate_'.$cid.'.php') && exit;
	require_once(D_P.'data/cache/cate_'.$cid.'.php');
	if(!$cateinfo['path']){
		$cateinfo['path'] = $cid;
	}
	if($page>1){
		$listurl = $sys['htmdir'].'/'.$cateinfo['path'].'/index_'.$page.'.'.$sys['htmext'];
	}else{
		$listurl = $sys['htmdir'].'/'.$cateinfo['listurl'];
	}
	require_once(R_P.'require/class_action.php');
	$action = new Action('publist');
	$action->cate($cid);
	$action->doIt();
}elseif($type=='getprev') {
	if(!$tid || !$cid) exit;
	$length = (int) GetGP('length');
	!$length && $length=20;
	require_once(R_P.'require/class_cms.php');
	$cont = new Cont();
	$cont->tid = $tid;
	$cont->cid = $cid;
	$content = $cont->getPrev();
	if(!$content) exit;
	$content['title'] = substrs($content['title'],$length);
print <<<EOT
	document.write("<a href=\"$content[url]\" title=\"$content[title]\">$content[title]</a>");
EOT;
}elseif($type=='getnext') {
	if(!$tid || !$cid) exit;
	$length = (int) GetGP('length');
	!$length && $length=20;
	require_once(R_P.'require/class_cms.php');
	$cont = new Cont();
	$cont->tid = $tid;
	$cont->cid = $cid;
	$content = $cont->getNext();
	if(!$content) exit;
	$content['title'] = substrs($content['title'],$length);
print <<<EOT
	document.write("<a href=\"$content[url]\" title=\"$content[title]\">$content[title]</a>");
EOT;
}


?>