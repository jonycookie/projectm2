<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
header('Content-Type: text/html; charset=utf-8');
require_once dirname(__FILE__).'/../global.php';
require_once iPATH."admin/function.php";
require_once iPATH."usercp/function.php";
require_once iPATH."usercp/user.class.php";
require_once iPATH.'usercp/usercp.lang.php';

$iCMS->rewrite=false;
unset($_keywords);
$do 		= $_GET['do'];
$operation 	= !empty($_GET['operation']) && is_string($_GET['operation']) ? trim($_GET['operation']) : '';
$action		= $_POST['action'];
$member	= new User;
$_GET['do'] == 'logout' && $member->logout(__SELF__);
if ($action =="login") {
	ckseccode($_POST['seccode']) && alert('验证码错误！');
	$username	= $_POST['username'];
	$password	= md5($_POST['password']);
}
$member->checklogin($username,$password);
//var_dump($member);
//$Admin->MP("USERCP","USERCP_Permission_Denied");
//$menu_array	= include iPATH.'admin/menu.array.php';
?>