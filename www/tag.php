<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
require_once(iPATH."include/function/template.php");
$_GET['t']&&$iCMS->tag(rawurldecode($_GET['t']));
?>