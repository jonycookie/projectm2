<?php
!defined('IN_ADMIN') && die('Forbidden');
$action = GetGP('action','P');
if($action){
	require_once(R_P.'require/class_cache.php');
	switch ($action){
		case 'cate':
			Cache::writeCache('cate');
			break;
		case 'homepage':
			require_once(R_P.'require/class_action.php');
			$action = new Action('pubindex');
			$action->doIt();
			break;
		case 'template':
			Cache::templateclean();
			break;
		case 'sql':
			Cache::sqlclean();
			break;
		case 'comment':
			Cache::commentclean();
			break;
		case 'mod':
			Cache::sql();
			Cache::writeCache('field');
			break;
		case 'modconfig':
			Cache::writeCache('bbs_config');
			break;
		case 'all':
			Cache::writeCache('update');
			break;
	}
	adminmsg('operate_success');
}
require PrintEot('set_cache');
adminbottom();
?>