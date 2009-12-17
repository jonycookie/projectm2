<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once("global.php");
empty($_GET['id']) && exit();
$iCMS->db->query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='".(int)$_GET['id']."' LIMIT 1");
_header(urldecode($_GET['url']));
?>