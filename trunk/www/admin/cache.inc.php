<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'post':
		if($action=='cache'){
			if($_POST['catalog']){
				include_once(iPATH.'include/catalog.class.php');
				$catalog =new catalog();
				$catalog->cache();
			}
			$_POST['tpl']		&& $iCMS->clear_compiled_tpl();
			$_POST['keywords']	&& keywords_cache();
			$_POST['tags']		&& tags_cache();
			$_POST['model']		&& model_cache();
			$_POST['field']		&& field_cache();
			$_POST['config']	&& CreateConfigFile();
			
			if($_POST['Re-Statistics']){
				$rs=$iCMS->db->getArray("SELECT id FROM `#iCMS@__catalog` ORDER BY `id` DESC");
				$_count=count($rs);
				for ($i=0;$i<$_count;$i++){
					$c=$iCMS->db->getValue("SELECT count(*) FROM #iCMS@__article where `cid`='".$rs[$i]['id']."' LIMIT 1 ");
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` ='$c' WHERE `id` ='".$rs[$i]['id']."' LIMIT 1 ");
				}
			}
			
			redirect("执行完毕！",__SELF__.'?do=cache');
		}
	break;
	default:
		$Admin->MP("menu_cache");
		include iCMS_admincp_tpl("cache");
}
?>
