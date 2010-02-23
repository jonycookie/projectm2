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
		if($action=='edit'){
			$sources=explode("\r\n",dhtmlspecialchars($_POST['source']));
			$authors=explode("\r\n",dhtmlspecialchars($_POST['author']));
			$editors=explode("\r\n",dhtmlspecialchars($_POST['author']));
			writefile(iPATH.'include/default.value.php',"<?php\n\$sources=".da_var_export($sources).";\n\$authors=".da_var_export($authors).";\n\$editors=".da_var_export($editors).";\n?>");
			_Header(__SELF__.'?do=default');
		}
	break;
	default:
	$Admin->MP("menu_article_default");
	include_once(iPATH.'include/default.value.php');
	include iCMS_admincp_tpl('default');
}
?>
