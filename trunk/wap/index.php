<?php
require_once('global.php');
InitGP(array('prog'));
if(!in_array($prog,array('index','cate','phone'))){
	$prog = 'index';
}

wap_header('index',$sys['title']);

if($prog=='cate'){
	$cids	= array();
	$query	= $db->query("SELECT cid FROM cms_category WHERE type>0 AND mid=1");//目前只支持文章模型
	while($rt = $db->fetch_array($query)){
		$cids[] = $rt['cid'];
	}
}
require_once PrintEot('wap_index');
wap_footer();
?>