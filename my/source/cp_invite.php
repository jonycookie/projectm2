<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_invite.php 7211 2008-04-29 01:54:02Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$siteurl = getsiteurl();

$maxcount = 50;//最多好友邀请

@include_once(S_ROOT.'./data/data_creditrule.php');
if(empty($_SGLOBAL['creditrule'])) {
	$get = $pay = array();
} else {
	$get = $_SGLOBAL['creditrule']['get'];
	$pay = $_SGLOBAL['creditrule']['pay'];
}

$list = $flist = array();
$count = 0;
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE uid='$_SGLOBAL[supe_uid]' ORDER BY id DESC");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	if($value['fuid']) {
		$flist[] = $value;
	} else {
		$list[] = "{$siteurl}invite.php?{$value[id]}{$value[code]}";
		$count++;
	}
}
$list_str = empty($list)?'':implode("\n", $list);

$maxcount_my = $maxcount - $count;
$maxinvitenum = empty($pay['invite'])?$maxcount_my:intval($space['credit']/$pay['invite']);
if($maxinvitenum > $maxcount_my) $maxinvitenum = $maxcount_my;
if($maxinvitenum < 0) $maxinvitenum = 0;

//提交
if(submitcheck('invitesubmit')) {
	if($_SCONFIG['closeinvite']) {
		showmessage('close_invite');
	}
	$invitenum = intval($_POST['invitenum']);
	if($invitenum > $maxinvitenum) $invitenum = $maxinvitenum;
	//扣减积分
	$credit = intval($pay['invite'])*$invitenum;
	if(empty($invitenum) || ($pay['invite'] && $credit > $space['credit'])) {
		showmessage('invite_error');
	}
	
	$codes = array();
	for ($i=0;$i<$invitenum;$i++) {
		$code = strtolower(random(6));
		$codes[] = "('$_SGLOBAL[supe_uid]', '$code')";
	}
	if($codes) {
		$_SGLOBAL['db']->query("INSERT INTO ".tname('invite')." (uid, code) VALUES ".implode(',', $codes));
		
		if($credit) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$credit WHERE uid='$_SGLOBAL[supe_uid]'");
			$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET credit=credit-$credit WHERE uid='$_SGLOBAL[supe_uid]'");
		}
	}
	showmessage('do_success', 'cp.php?ac=invite', 0);
}

$actives = array('invite'=>' class="active"');

include template('cp_invite');

?>