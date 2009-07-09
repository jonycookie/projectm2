<?php
!defined('IN_ADMIN') && die('Forbidden');

$tabledb=array(
	'cms_admin',
	'cms_attach',
	'cms_attachindex',
	'cms_category',
	'cms_collection',
	'cms_comment',
	'cms_commentface',
	'cms_config',
	'cms_const',
	'cms_contentindex',
	'cms_contenttag',
	'cms_extension',
	'cms_field',
	'cms_gather',
	'cms_help',
	'cms_module',
	'cms_recycle',
	'cms_schcache',
	'cms_select',
	'cms_selectvalue',
	'cms_tags'
);
foreach ($moduledb as $key=>$val){
	if($key<=0) continue;
	$tabledb[]='cms_content'.$key;
}
sort($tabledb);
if($_pre!='cms_'){
	foreach($tabledb as $key=>$value){
		$tabledb[$key] = str_replace('cms_',$_pre,$value);
	}
}
$othortable = array();
$query = $db->query("SHOW TABLES");
while ($rt = $db->fetch_array($query)){
	$value = trim(current($rt));
	if(!in_array($value,$tabledb)){
		$othortable[]=$value;
	}
}
?>