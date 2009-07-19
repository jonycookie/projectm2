<?php
!defined('IN_ADMIN') && die('Forbidden');
if(!$viewtype) $viewtype='data';
InitGP(array('action','aid','page','type','displaynum','keyword'));
require_once(R_P.'require/class_attach.php');
$attach = new Attach();
if(!$action||$action =='view'){
	$attach->displaynum = $displaynum?$displaynum:30;
	!$type && $type='all';
	$files = $attach->show($page,$type,$keyword);
	$pages = $attach->pages;
	require PrintEot('file_attach');
	adminbottom();
}elseif ($action=='del'){
	$attach->del($aid);
	adminmsg('pub_unlinkok');
}elseif ($action=='viewquote'){
	$aid = intval($aid);
	require_once(D_P.'data/cache/cate.php');
	$q = $quoteDb = array();
	$rs = $db->query("SELECT * FROM cms_attachindex WHERE aid='$aid'");
	while ($a = $db->fetch_array($rs)){
		$quoteDb[$a['mid']][] = $a['tid'];
	}
	foreach ($quoteDb as $mid=>$tids){
		if(empty($tids)) continue;
		$tids = implode(',',$tids);
		$rt = $db->query("SELECT tid,cid,title FROM cms_contentindex WHERE tid IN($tids)");
		while ($r = $db->fetch_array($rt)){
			$q[] = array(
				'tid'=>$r['tid'],
				'mname'=>$moduledb[$mid]['mname'],
				'cname'=>$catedb[$r['cid']]['cname'],
				'title'=>$r['title'],
				'cid'=>$r['cid'],
			);
		}
	}
	header("content-type:text/html;charset={$charset}");
	require PrintEot('file_attach');
	adminbottom(0);
}
?>