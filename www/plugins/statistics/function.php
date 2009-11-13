<?php
/*
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 *	================================
 *	Plugin Name: Statistics/统计
 *	Plugin URI: http://www.iDreamSoft.cn
 *	Description: Statistics/统计
 *	Version: 1.0
 *	Author: 枯木
 *	Author URI: http://G.iDreamSoft.cn
 *	TAG:<!--{iCMS:plugins name='statistics'}-->
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_plugins_statistics($vars="",$iCMS){
	$ac=$iCMS->db->getValue("SELECT count(id) FROM #iCMS@__article WHERE visible='1'");
	$asum=$iCMS->db->getValue("SELECT SUM(hits) FROM #iCMS@__article WHERE visible='1'");
	$cc=$iCMS->db->getValue("SELECT count(id) FROM #iCMS@__comment WHERE `isexamine`='1'");
	$mc=$iCMS->db->getValue("SELECT count(id) FROM #iCMS@__message where `secret`='off'");
	echo"日志: <b>{$ac}</b> 篇<br/>评论: <b>{$cc}</b> 个<br/>留言: <b>{$mc}</b> 个<br/>访问: <b>{$asum}</b> 次<br/>";
}
?>