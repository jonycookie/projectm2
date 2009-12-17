<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
//		$mtime = microtime();
//		$mtime = explode(' ', $mtime);
//		$time_start = $mtime[1] + $mtime[0];

require_once("global.php");
require_once(iPATH."include/function/template.php");
isset($_GET['p'])?$iCMS->page($_GET['p']):$iCMS->index();
//		$mtime = microtime();
//		$mtime = explode(' ', $mtime);
//		$time_end = $mtime[1] + $mtime[0];
//		echo  "<h1>".($time_end - $time_start);
?>