<?php
defined('IN_EXT') or die('Forbidden');

$action = GetGP('action');
empty($action) && $action = 'view';
if($action == 'view'){
	$keyword = GetGP('keyword');
	$keyword = Char_cv($keyword);
	$sql = $keyword ? " AND name='$keyword' " : "";
	$rs = $db->query("SELECT * FROM cms_const WHERE type='TPL' $sql ORDER BY id DESC");
	while($rt = $db->fetch_array($rs)){
		$vars[] = $rt;
	}
	require PrintExt('header');
	require PrintExt('admin');
	adminbottom();
}elseif($action == 'add'){
	require PrintExt('header');
	require PrintExt('admin');
	adminbottom();
}elseif($action == 'edit'){
	require_once(R_P.'require/class_const.php');
	$varid = (int)GetGP('varid');
	$const = new TplConst();
	$var = $const->getConstById($varid);
	${'type_'.$var['type']} = 'selected';
	require PrintExt('header');
	require PrintExt('admin');
	adminbottom();
}elseif($action == 'del'){
	require_once(R_P.'require/class_const.php');
	$varid = GetGP('varid');
	$const = new TplConst();
	$const->delConstById($varid);
	require_once(R_P.'require/class_cache.php');
	Cache::templateclean();
	adminmsg('operate_success');
}elseif($action == 'save'){
	require_once(R_P.'require/class_const.php');
	$array = Init_GP(array('title','name','value','id','type'));
	$const = new TplConst();
	$const->setConst($array);
	require_once(R_P.'require/class_cache.php');
	Cache::templateclean();
	adminmsg('operate_success');
}
?>