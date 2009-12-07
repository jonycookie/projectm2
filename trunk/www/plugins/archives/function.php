<?php
/*
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 *	================================
 *	Plugin Name: Archives/文章归档
 *	Plugin URI: http://www.iDreamSoft.cn
 *	Description: Archives/文章归档
 *	Version: 1.0
 *	Author: 枯木
 *	Author URI: http://G.iDreamSoft.cn
 *	TAG:<!--{iCMS:plugins name='archives'}-->
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_plugins_archives($vars="",$iCMS){
	$rs = $iCMS->db->getArray("SELECT A.pubdate FROM `#iCMS@__article` AS A,#iCMS@__catalog AS C WHERE visible='1' AND A.cid=C.id AND C.ishidden='0' ORDER BY pubdate DESC");
	for ($i=0;$i<count($rs);$i++){
		$article[] = get_date($rs[$i]['pubdate'],'Y-m');
	}
	$arr = array_count_values($article);
	$i=0;
	foreach($arr as $key => $val){
		list($y, $m) = explode('-', $key);
		$archive[$i]['url']=$y.'_'.$m;
		$archive[$i]['date']="{$y}年{$m}月";
		$archive[$i]['count']=$val;
		$i++;
	}
	$iCMS->value('archive',$archive);
	$iCMS->output('archive',$vars['tpl'],'file:');
}
?>