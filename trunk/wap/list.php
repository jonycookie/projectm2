<?php
require_once('global.php');
$listdb = array();
if($cid){
	InitGP(array('page'));
	$per = 10;
	$fm  = $db->get_one("SELECT total FROM cms_category WHERE cid='$cid'");
	(!is_numeric($page) || $page < 1) && $page=1;
	$totle = ceil($fm['total']/$per);
	$totle ==0 ? $page=1 : ($page > $totle ? $page=$totle : '');
	$next  = $page+1;
	$pre   = $page==1 ? 1 : $page-1;
	catecheck($cid);
	$list  = '';
	$satrt = ($page-1)*$per;
	$id    = $satrt;
	$limit = "LIMIT $satrt,$per";
	$query = $db->query("SELECT tid,title,postdate FROM cms_contentindex WHERE cid='$cid' AND ifpub=1 ORDER BY postdate DESC $limit");
	while($rt=$db->fetch_array($query)){
		$id++;
		$rt['postdate'] = get_date($rt['postdate']);
		$rt['id'] = $id;
		$listdb[] = $rt;
	}
}
wap_header('list',$very['title']);
require_once PrintEot('wap_list');
wap_footer();
?>