<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
require_once(iPATH."include/function/template.php");
header("Content-Type: text/xml");
$iCMS->assign('id',intval($_GET['id']));
$iCMS->iPrint("iSYSTEM","rss");
?>