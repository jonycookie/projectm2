<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_index.php 10806 2008-12-23 07:14:20Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//风格
$_SGLOBAL['space_theme'] = $space['theme'];
$_SGLOBAL['space_css'] = $space['css'];

//是否好友
$space['isfriend'] = $space['self'];
if($space['friends'] && in_array($_SGLOBAL['supe_uid'], $space['friends'])) {
	$space['isfriend'] = 1;//是好友
}

//个人资料
//性别
$space['sex_org'] = $space['sex'];
if(ckprivacy('profile')) {//隐私
	$space['showprofile'] = 1;
	$space['sex'] = $space['sex']=='1'?'<a href="network.php?ac=space&sex=1&searchmode=1">'.lang('man').'</a>':($space['sex']=='2'?'<a href="network.php?ac=space&sex=2&searchmode=1">'.lang('woman').'</a>':'');
	$space['birthday'] = ($space['birthyear']?"$space[birthyear]".lang('year'):'').($space['birthmonth']?"$space[birthmonth]".lang('month'):'').($space['birthday']?"$space[birthday]".lang('day'):'');
	$space['marry'] = $space['marry']=='1'?'<a href="network.php?ac=space&marry=1&searchmode=1">'.lang('unmarried').'</a>':($space['marry']=='2'?'<a href="network.php?ac=space&marry=2&searchmode=1">'.lang('married').'</a>':'');
	$space['birth'] = trim(($space['birthprovince']?"<a href=\"network.php?ac=space&birthprovince=".rawurlencode($space['birthprovince'])."&searchmode=1\">$space[birthprovince]</a>":'').($space['birthcity']?" <a href=\"network.php?ac=space&birthcity=".rawurlencode($space['birthcity'])."&searchmode=1\">$space[birthcity]</a>":''));
	$space['reside'] = trim(($space['resideprovince']?"<a href=\"network.php?ac=space&resideprovince=".rawurlencode($space['resideprovince'])."&searchmode=1\">$space[resideprovince]</a>":'').($space['residecity']?" <a href=\"network.php?ac=space&residecity=".rawurlencode($space['residecity'])."&searchmode=1\">$space[residecity]</a>":''));
	$space['qq'] = empty($space['qq'])?'':"<a target=\"_blank\" href=\"http://wpa.qq.com/msgrd?V=1&Uin=$space[qq]&Site=$space[username]&Menu=yes\">$space[qq]</a>";
	//自定义
	@include_once(S_ROOT.'./data/data_profilefield.php');
	$fields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];
} else {
	$space['showprofile'] = 0;
}
//积分
$space['creditstar'] = getstar($space['credit']);

//域名
$space['domainurl'] = space_domain($space);

if($space['spacenote']) {
	$space['spacenote'] = getstr($space['spacenote'], 50);
}
//个人动态
$feedlist = array();
if(ckprivacy('feed')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			realname_set($value['uid'], $value['username']);
			$feedlist[] = $value;
		}
	}
	$feednum = count($feedlist);
}

//个人分享
$sharelist = array();
if(ckprivacy('share')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value = mkshare($value);
		$sharelist[] = $value;
	}
}

//好友列表
$oluids = array();
$friendlist = array();
if(ckprivacy('friend')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$space[uid]' AND status='1' ORDER BY num DESC, dateline DESC LIMIT 0,16");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['fuid'], $value['fusername']);
		$oluids[$value['fuid']] = $value['fuid'];
		$friendlist[] = $value;
	}
	if($friendlist && empty($space['friendnum'])) {
		//更新好友缓存
		include_once(S_ROOT.'./source/function_cp.php');
		friend_cache($space['uid']);
	}
}

//最近访客列表
$visitorlist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('visitor')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,16");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	realname_set($value['vuid'], $value['vusername']);
	$value['isfriend'] = 0;
	if($space['friends'] && in_array($value['vuid'], $space['friends'])) {
		$value['isfriend'] = 1;
	}
	$oluids[$value['vuid']] = $value['vuid'];
	$visitorlist[] = $value;
}

//记录
$doinglist = array();
if(ckprivacy('doing')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$doinglist[] = $value;
	}
}

//日志
$bloglist = array();
if(ckprivacy('blog')) {
	$query = $_SGLOBAL['db']->query("SELECT b.uid, b.blogid, b.subject, b.dateline, b.pic, b.picflag, b.viewnum, b.replynum, b.friend, b.password, bf.message, bf.target_ids
		FROM ".tname('blog')." b
		LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
		WHERE b.uid='$space[uid]'
		ORDER BY b.dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			$value['pic'] = mkpicurl($value);
			$value['message'] = $value['friend']==4?'':getstr($value['message'], 150, 0, 0, 0, 0, -1);
			$bloglist[] = $value;
		}
	}
	$blognum = count($bloglist);
}

//相册
$albumlist = array();
if(ckprivacy('album')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE uid='$space[uid]' ORDER BY updatetime DESC LIMIT 0,4");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			$value['pic'] = mkpicurl($value);
			$albumlist[] = $value;
		}
	}
}

//个人群组
$mtaglist = array();
if(ckprivacy('mtag')) {
	$query = $_SGLOBAL['db']->query("SELECT field.* FROM ".tname('tagspace')." main
		LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid
		WHERE main.uid='$space[uid]' LIMIT 0, 100");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$mtaglist[$value['fieldid']][] = $value;
	}
	if($mtaglist) {
		ksort($mtaglist);
		@include_once(S_ROOT.'./data/data_profield.php');
	}
}

//话题
$threadlist = array();
if(ckprivacy('mtag')) {
	$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main
		WHERE main.uid='$space[uid]'
		ORDER BY main.lastpost DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$threadlist[] = $value;
	}
}

//留言板
$walllist = array();
$wallnum = 0;
if(ckprivacy('wall')) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE id='$space[uid]' AND idtype='uid' ORDER BY dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['authorid'], $value['author']);
		$value['message'] = strlen($value['message'])>500?getstr($value['message'], 500, 0, 0, 0, 0, -1).' ...':$value['message'];
		$walllist[] = $value;
	}
	$wallnum = getcount('comment', array('id'=>$space['uid'], 'idtype'=>'uid'));
}

//是否在线
$isonline = 0;
$isonline = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT lastactivity FROM ".tname('session')." WHERE uid='$space[uid]'"), 0);
if($isonline) $isonline = sgmdate('H:i:s', $isonline, 1);

//风格
$theme = empty($_GET['theme'])?'':preg_replace("/[^0-9a-z]/i", '', $_GET['theme']);
if($theme == 'uchomedefault') {
	$_SGLOBAL['space_theme'] = $_SGLOBAL['space_css'] = '';
} elseif($theme) {
	$cssfile = S_ROOT.'./theme/'.$theme.'/style.css';
	if(file_exists($cssfile)) {
		$_SGLOBAL['space_theme'] = $theme;
		$_SGLOBAL['space_css'] = '';
	}
} else {
	if(!$space['self'] && $_SGLOBAL['member']['nocss']) {
		$_SGLOBAL['space_theme'] = $_SGLOBAL['space_css'] = '';
	}
}

//最近访客记录
if(!$space['self'] && $_SGLOBAL['supe_uid']) {
	$query = $_SGLOBAL['db']->query("SELECT dateline FROM ".tname('visitor')." WHERE uid='$space[uid]' AND vuid='$_SGLOBAL[supe_uid]'");
	$visitor = $_SGLOBAL['db']->fetch_array($query);
	if(empty($visitor['dateline'])) {
		$setarr = array(
			'uid' => $space['uid'],
			'vuid' => $_SGLOBAL['supe_uid'],
			'vusername' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp']
		);
		inserttable('visitor', $setarr, 0, true);
		show_credit();//竞价排名
	} else {
		if($_SGLOBAL['timestamp'] - $visitor['dateline'] >= 300) {
			updatetable('visitor', array('dateline'=>$_SGLOBAL['timestamp']), array('uid'=>$space['uid'], 'vuid'=>$_SGLOBAL['supe_uid']));
		}
		if($_SGLOBAL['timestamp'] - $visitor['dateline'] >= 3600) {
			show_credit();//1小时后竞价排名
		}
	}
}

//应用显示
$narrowlist = $widelist = $space['userapp'] = array();
if($space['self']) {
	$space['userapp'] = $_SGLOBAL['my_userapp'];
} elseif ($_SCONFIG['my_status']) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('userapp')." WHERE uid='$space[uid]' ORDER BY displayorder DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$space['userapp'][$value['appid']] = $value;
	}
}
if($space['userapp']) {
	include_once(S_ROOT.'./source/function_userapp.php');
	foreach ($space['userapp'] as $value) {
		if(app_ckprivacy($value['privacy']) && $value['myml']) {
			$value['appurl'] = 'userapp.php?id='.$value['appid'];
			if($value['narrow']) {
				$narrowlist[] = $value;
			} else {
				$widelist[] = $value;
			}
		}
	}
}

//获取任务
$task = array();
if($space['self']) {
	include_once(S_ROOT.'./source/function_space.php');
	$task = gettask();
}

//是否在线
$ols = array();
if($oluids) {
	$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('session')." WHERE uid IN (".simplode($oluids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$ols[$value['uid']] = 1;
	}
}


//实名
realname_get();

//feed
foreach ($feedlist as $key => $value) {
	$feedlist[$key] = mkfeed($value);
}

//更新好友热度
if(!$space['self']) {
	$_SGLOBAL['db']->query("UPDATE ".tname('friend')." SET num=num+1 WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$space[uid]'");
}

//去掉广告
$_SGLOBAL['ad'] = array();

//访问统计
if(!$space['self']) {
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET viewnum=viewnum+1 WHERE uid='$space[uid]'");
}

$_GET['view'] = 'me';

include_once template("space_index");

//竞价排名
function show_credit() {
	global $_SGLOBAL, $space;
	$showcredit = getcount('show', array('uid'=>$space['uid']), 'credit');
	if($showcredit>0) {
		$_SGLOBAL['db']->query("UPDATE ".tname('show')." SET credit=credit-1 WHERE uid='$space[uid]'");
	}
}

?>
