<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
require_once(iPATH."include/function/template.php");
$p=isset($_GET['p'])?intval($_GET['p']):1;
if($iCMS->config['linkmode']=='id'){
	$iCMS->Show($_GET['id'],$p);
}elseif($iCMS->config['linkmode']=='title'){
	$iCMS->Show($_GET['t'],$p);
}
?>