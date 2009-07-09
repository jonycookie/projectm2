<?php
!defined('IN_ADMIN') && die('Forbidden');
InitGP(array('step','action'));
if(!$action){
	if(!$step){
		$upload_file_size = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';
		require PrintEot('header');
		require PrintEot('file_upload');
		adminbottom();
	}elseif ($step==2){
		!is_dir($very['attachdir']) && Showmsg('pub_attachmenterror');
		!is_writeable($very['attachdir']) && Showmsg('pub_attachementwrite');
		require_once(R_P.'require/class_attach.php');
		$attach = new Attach();
		$attach->type ="ajax";
		$attach->upload();
		$i = $attach->uploadnum;
		adminmsg('pub_uploadok');
	}
}elseif($action == 'setftp') {
	if(!$step){
		@include_once(D_P.'data/cache/ftp_config.php');
		$ftp_pass = substr($ftp_pass,0,1).'********'.substr($ftp_pass,-1);
		ifcheck($very['ifftp'],'ifftp');
		require PrintEot('header');
		require PrintEot('file_upload');
		adminbottom();
	}elseif($step==2) {
		InitGP(array('ftp','config'),'P');
		@include_once(D_P.'data/cache/ftp_config.php');
		foreach ($config as $key=>$value){
			$key = 'db_'.$key;
			$value = addslashes($value);
			$db->pw_update(
			"SELECT * FROM cms_config WHERE db_name='$key'",
			"UPDATE cms_config SET db_value='$value' WHERE db_name='$key'",
			"INSERT INTO cms_config (db_name,db_value) VALUES('$key','$value')"
			);
		}
		$ftp_pass = substr($ftp_pass,0,1).'********'.substr($ftp_pass,-1);
		foreach($ftp as $key=>$value){
			if($$key != $value){
				$rt=$db->get_one("SELECT db_name FROM cms_config WHERE db_name='$key'");
				if($rt['db_name']==$key){
					$db->update("UPDATE cms_config SET db_value='$value' WHERE db_name='$key'");
				} else{
					$db->update("INSERT INTO cms_config(db_name,db_value) VALUES ('$key','$value')");
				}
			}
		}
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->config();
		$cache->updatecache_ftp();
		adminmsg('operate_success',$basename.'&action=setftp');
	}

}

?>