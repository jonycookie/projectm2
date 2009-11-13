<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
include(iPATH.'admin/table.array.php');
//数据统计
$content_datasize = 0;
$tables = $iCMS->db->getArray("SHOW TABLE STATUS");
$_count=count($tables);
for ($i=0;$i<$_count;$i++){
	if(in_array($tables[$i]['Name'],$tabledb)){
		$datasize += $tables[$i]['Data_length'];
		$indexsize += $tables[$i]['Index_length'];
		if (in_array($tables[$i]['Name'],array(DB_PREFIX."article",DB_PREFIX."catalog",DB_PREFIX."comment",DB_PREFIX."articledata"))) {
			$content_datasize += $tables[$i]['Data_length']+$tables[$i]['Index_length'];
		}
	}
}
$c=$iCMS->db->getValue("SELECT count(*) FROM #iCMS@__catalog");
$a=$iCMS->db->getValue("SELECT count(*) FROM #iCMS@__article");
file_exists(iPATH.'license.php') && $license	= include iPATH.'license.php';
include iCMS_admincp_tpl("home");
function okorno($o){
	return $o?'<font color=green>支持<b>√</b></font>':'<font color=red>不支持<b>×</b></font>';
}
?>
