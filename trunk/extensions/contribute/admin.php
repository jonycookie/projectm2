<?php
defined('IN_EXT') or die('Forbidden');
$step = GetGP('step');
if(!$step) {
	$rt		= $db->get_one("SELECT value FROM cms_extension WHERE name='contribute_config'");
	$contribute = unserialize($rt['value']);
	$discate= explode(',',$contribute['cids']);
	ifcheck($contribute['ckcontribute'],'ckcontribute');
	ifcheck($contribute['checkcontribute'],'checkcontribute');
	require_once(E_P.'include/class_contribute.php');
	$cate	= new Contribute();
	$cate_select = $cate->treeCommon();
	foreach ($discate as $cid){
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);
	}
	require_once(R_P.'require/class_content.php');
	$contribute['intro'] = Content::Editor('intro','Basic',stripslashes($contribute['intro']));
}else {
	$discate = GetGP('discate');
	$title	 = GetGP('title');
	$intro	 = GetGP('intro');
	$ckcontribute		= (int)GetGP('ckcontribute');
	$checkcontribute	= (int)GetGP('checkcontribute');
	$cids	 = implode(',',$discate);
	$sqlinto = array();
	$sqlinto['title'] = $title;
	$sqlinto['intro'] = $intro;
	$sqlinto['cids']  = $cids;
	$sqlinto['ckcontribute']	= $ckcontribute;
	$sqlinto['checkcontribute'] = $checkcontribute;

	$value	= addslashes(serialize($sqlinto));
	$db->pw_update(
		"SELECT * FROM cms_extension WHERE name='contribute_config'",
		"UPDATE cms_extension SET value='$value' WHERE name='contribute_config'",
		"INSERT INTO cms_extension (name,value) VALUES('contribute_config','$value')"
	);

	require_once(E_P.'include/cache.class.php');
	contributeCache::cache();
	adminmsg('operate_success');
}
require PrintExt('header');
require PrintExt('admin');
adminbottom();

?>