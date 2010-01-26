<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: userapp.php 10861 2008-12-30 02:44:00Z liguode $
*/

include_once('./common.php');

//是否关闭站点
checkclose();

//需要登录
checklogin();

//空间信息
$space = getspace($_SGLOBAL['supe_uid']);

if(empty($_SCONFIG['my_status'])) {
	showmessage('no_privilege_my_status');
}

//更新活动状态
updatetable('session', array('lastactivity' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));

$appid = empty($_GET['id'])?'':intval($_GET['id']);

$app = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myapp')." WHERE appid='$appid' LIMIT 1");
if($app = $_SGLOBAL['db']->fetch_array($query)) {
	if($app['flag']<0) {
		showmessage('no_privilege_myapp');
	}
}

//漫游
$my_appId = $appid;
$my_suffix = base64_decode(urldecode($_GET['my_suffix']));

$my_prefix = getsiteurl();

if (!$my_suffix) {
    header('Location: userapp.php?id='.$my_appId.'&my_suffix='.urlencode(base64_encode('/')));
    exit;
}

if (preg_match('/^\//', $my_suffix)) {
    $url = 'http://apps.manyou.com/'.$my_appId.$my_suffix;
} else {
    if ($my_suffix) {
        $url = 'http://apps.manyou.com/'.$my_appId.'/'.$my_suffix;
    } else {
        $url = 'http://apps.manyou.com/'.$my_appId; 
    }
}
if (strpos($my_suffix, '?')) {
    $url = $url.'&my_uchId='.$_SGLOBAL['supe_uid'].'&my_sId='.$_SCONFIG['my_siteid'];
} else {
    $url = $url.'?my_uchId='.$_SGLOBAL['supe_uid'].'&my_sId='.$_SCONFIG['my_siteid'];
}
$url .= '&my_prefix='.urlencode($my_prefix).'&my_suffix='.urlencode($my_suffix);
$current_url = getsiteurl().'userapp.php';
if ($_SERVER['QUERY_STRING']) {
    $current_url = $current_url.'?'.$_SERVER['QUERY_STRING'];
}
$extra = $_GET['my_extra'];
$timestamp = $_SGLOBAL['timestamp'];
$url .= '&my_current='.urlencode($current_url);
$url .= '&my_extra='.urlencode($extra);
$url .= '&my_ts='.$timestamp;
$url .= '&my_appVersion='.$app['version'];
$hash = $_SCONFIG['my_siteid'].'|'.$_SGLOBAL['supe_uid'].'|'.$appid.'|'.$current_url.'|'.$extra.'|'.$timestamp.'|'.$_SCONFIG['my_sitekey'];
$hash = md5($hash);
$url .= '&my_sig='.$hash;
$my_suffix = urlencode($my_suffix);

include_once template("userapp");

?>
