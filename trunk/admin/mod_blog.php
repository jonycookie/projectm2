<?php
!defined('IN_ADMIN') && die('Forbidden');

$step = GetGP('step');
if(!$step){
	ifcheck($sys['aggreblog'],'aggreblog');
	if ($fp = opendir(R_P.'combine/blog')) {
		$blogtype = array();
		while (($extdir = readdir($fp))) {
			if(preg_match('/^class_(.+)\.php$/i',$extdir,$match)) {
				$blogtype[]=$match[1];
			}
		}
		closedir($fp);
	}
	!count($blogtype) && Showmsg('mod_not_exist');
	$blogSelect = '';
	foreach($blogtype as $type) {
		$blogSelect .= "<option value=\"$type\">$type</option>";
	}
	$blogSelect = str_replace("value=\"$sys[blog_type]\"","value=\"$sys[blog_type]\" selected",$blogSelect);
}elseif($step==2){
	$config = GetGP('config');
	if($config['aggreblog']){
		!eregi('^http://',$config['blog_url']) && Showmsg('mod_invalidurl');
		empty($config['blog_dbname']) && Showmsg('mod_nodbname');
		if(mysql_connect($config['blog_dbhost'],$config['blog_dbuser'],$config['blog_dbpw'])){

		}else{
			Showmsg('mod_invaliddb');
		}
		mysql_close();
	}
	foreach ($config as $key=>$value){
		$key = 'db_'.$key;
		$value = addslashes($value);
		$db->pw_update(
			"SELECT * FROM cms_config WHERE db_name='$key'",
			"UPDATE cms_config SET db_value='$value' WHERE db_name='$key'",
			"INSERT INTO cms_config (db_name,db_value) VALUES('$key','$value')"
		);
	}

	require_once(R_P.'require/class_cache.php');
	$cache = new Cache();
	$cache->config();
	adminmsg('operate_success');
}
require PrintEot('mod_blog');
adminbottom();
?>