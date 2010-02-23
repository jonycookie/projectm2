<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
foreach($menu_array AS $key=>$menus){
	if($Admin->MP('header_'.$key,'F')){
		echo '<ul id="menu_'.$key.'" style="display: none">';
		foreach($menus as $k=>$url) {
			if($Admin->MP($k,'F')){
				echo '<li><a id="'.$k.'" href="'.(substr($url, 0, 4) == 'http' ? $url : __SELF__.'?do='.$url).'" target="main">'.lang($k).'</a></li>';
			}
		}
		echo '</ul>';
	}
}
?>