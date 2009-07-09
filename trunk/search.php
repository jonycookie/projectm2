<?php
define('SCR','search');
require_once('global.php');
require_once(R_P.'require/class_search.php');
$action = GetGP('action');
!$action && $action ='search';

$search = new Search();
switch($action){
	case 'search':
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$search->doIt();
		$search->resultShow();
		break;
	case 'ajax':
		$search->getSearchField($mid);
		break;
	case 'tag':
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$search->tagResult();
		break;
}
exit();
?>