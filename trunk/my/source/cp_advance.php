<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_advance.php 9055 2008-10-21 06:22:45Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

define('IN_ADMINCP', TRUE);//���ε�¼ʹ��

//���ε�¼ȷ��(���Сʱ)
$session = array();
$query = $_SGLOBAL['db']->query("SELECT errorcount FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]' AND dateline+1800>='$_SGLOBAL[timestamp]'");
$session = $_SGLOBAL['db']->fetch_array($query);
if($session['errorcount'] == -1) {
	//��¼�ɹ�
	showmessage('do_success', 'admincp.php', 0);
}

include_once template("cp_advance");

?>