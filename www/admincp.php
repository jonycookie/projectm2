<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once 'admin/global.php';
if((empty($do) || isset($frames)) && empty($action)){
	$extra = cpurl('url');
	$extra = $extra && $do ? $extra : 'do=home';
	$admincpfile=substr(__SELF__,strrpos(__SELF__,'/')+1);
	require_once 'admin/templates/admincp.php';
}else{
	if(in_array($do, array('home','catalog','link','comment','advertise','contentype','message','tag','keywords','search','cache', 'setting', 'article','default','filter', 'user', 'database', 'model','content', 'field','plugin','modifier', 'file', 'html','ajax','dialog','account','group','template'))) {
		require_once iPATH.'admin/'.$do.'.inc.php';
	} else {
		echo "What are you doing?";
	}
}
?>