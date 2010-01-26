<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_poke.php 10831 2008-12-26 01:32:49Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

if($uid == $_SGLOBAL['supe_uid']) {
	showmessage('not_to_their_own_greeted');
}

if($op == 'send' || $op == 'reply') {

	if(!checkperm('allowpoke')) {
		showmessage('no_privilege');
	}
	
	//实名认证
	ckrealname('poke');

	//新用户见习
	cknewuser();
	
	//获取对象
	$tospace = getspace($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}

	//黑名单
	if(isblacklist($tospace['uid'])) {
		showmessage('is_blacklist');
	}
	
	//打招呼
	if(submitcheck('pokesubmit')) {
		$setarr = array(
			'uid' => $uid,
			'fromuid' => $_SGLOBAL['supe_uid'],
			'fromusername' => $_SGLOBAL['supe_username'],
			'note' => getstr($_POST['note'], 50, 1, 1),
			'dateline' => $_SGLOBAL['timestamp'],
			'iconid' => intval($_POST['iconid'])
		);
		inserttable('poke', $setarr, 0, true);
		
		//更新我的好友关系热度
		$_SGLOBAL['db']->query("UPDATE ".tname('friend')." SET num=num+1 WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'");

		//发送邮件通知
		smail($uid, '',cplang('poke_subject',array($_SN[$space['uid']], getsiteurl().'cp.php?ac=poke')));
		
		if($op == 'reply') {
			//删除招呼
			$_SGLOBAL['db']->query("DELETE FROM ".tname('poke')." WHERE uid='$_SGLOBAL[supe_uid]' AND fromuid='$uid'");
		}

		showmessage('poke_success', $_POST['refer'], 1, array($_SN[$tospace['uid']]));
	}

} elseif($op == 'ignore') {

	$where = empty($uid)?'':"AND fromuid='$uid'";
	$_SGLOBAL['db']->query("DELETE FROM ".tname('poke')." WHERE uid='$_SGLOBAL[supe_uid]' $where");

	showmessage('has_been_hailed_overlooked');
	
} else {
	
	$perpage = 20;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	//检查开始数
	ckstart($start, $perpage);
	
	//打招呼
	$list = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('poke')." WHERE uid='$space[uid]'"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('poke')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['uid'] = $value['fromuid'];
			$value['username'] = $value['fromusername'];
			realname_set($value['uid'], $value['username']);
			$value['isfriend'] = ($value['uid']==$space['uid'] || ($space['friends'] && in_array($value['uid'], $space['friends'])))?1:0;
			$list[] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, "cp.php?ac=poke");
}

realname_get();

include_once template('cp_poke');

?>