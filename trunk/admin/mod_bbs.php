<?php
!defined('IN_ADMIN') && die('Forbidden');

$step = GetGP('step');
if(!$step){
	ifcheck($sys['aggrebbs'],'aggrebbs');
	ifcheck($sys['bbs_htmifopen'],'bbs_htmifopen');
	if ($fp = opendir(R_P.'combine/bbs')) {
		$bbstype = array();
		while (($extdir = readdir($fp))) {
			if(preg_match('/^class_(.+)\.php$/i',$extdir,$match)) {
				if(strpos($match[1],'PHPWind')!==false){
					array_unshift($bbstype,$match[1]);
				}else{
					$bbstype[]=$match[1];
				}
			}
		}
		closedir($fp);
	}
	!count($bbstype) && Showmsg('mod_not_exist');
	$bbsSelect = '';
	foreach ($bbstype as $type){
		$bbsSelect .= "<option value=\"$type\">$type</option>";
	}
	$bbsSelect = str_replace("value=\"$sys[bbs_type]\"","value=\"$sys[bbs_type]\" selected",$bbsSelect);
}elseif($step==2){
	$config = GetGP('config');
	if($config['aggrebbs']){
		!eregi('^http://',$config['bbs_url']) && Showmsg('mod_invalidurl');
		empty($config['bbs_dbname']) && Showmsg('mod_nodbname');
		if(mysql_connect($config['bbs_dbhost'],$config['bbs_dbuser'],$config['bbs_dbpw'])){

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
	require(D_P.'data/cache/config.php');
	$sys['aggrebbs'] && $cache->bbs_config();
	adminmsg('operate_success');
}
require PrintEot('mod_bbs');
adminbottom();
?>