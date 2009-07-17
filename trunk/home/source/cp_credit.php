<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_credit.php 10953 2009-01-12 02:55:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(submitcheck('friendsubmit')) {
	$showcredit = intval($_POST['stakecredit']);
	if($showcredit > $space['credit']) $showcredit = $space['credit'];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}
	
	//检测好友
	$_POST['fusername'] = trim($_POST['fusername']);
	$fuid = getcount('friend', array('uid'=>$space['uid'], 'fusername'=>$_POST['fusername'], 'status'=>1), 'fuid');
	if(empty($fuid) || $fuid == $space['uid']) {
		showmessage('showcredit_fuid_error');
	}
	
	//赠送
	$count = getcount('show', array('uid'=>$fuid));
	if($count) {
		$_SGLOBAL['db']->query("UPDATE ".tname('show')." SET credit=credit+$showcredit WHERE uid='$fuid'");
	} else {
		inserttable('show', array('uid'=>$fuid, 'username'=>$_POST['fusername'], 'credit'=>$showcredit), 0, true);
	}
	
	//减少自己的积分
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$showcredit WHERE uid='$space[uid]'");
	
	//给好友通知
	notification_add($fuid, 'credit', cplang('note_showcredit', array($showcredit)));
	
	//实名
	realname_set($fuid, $_POST['fusername']);
	realname_get();
	
	//feed
	feed_add('show', cplang('feed_showcredit'), array(
		'fusername'=>"<a href=\"space.php?uid=$fuid\">{$_SN[$fuid]}</a>",
		'credit'=>$showcredit));
	
	showmessage('showcredit_friend_do_success', "network.php?ac=space&view=show");
	
} elseif(submitcheck('showsubmit')) {
	
	$showcredit = intval($_POST['showcredit']);
	if($showcredit > $space['credit']) $showcredit = $space['credit'];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}
	$_POST['note'] = getstr($_POST['note'], 100, 1, 1, 1);
	
	//增加
	$count = getcount('show', array('uid'=>$_SGLOBAL['supe_uid']));
	if($count) {
		$notesql = $_POST['note']?", note='$_POST[note]'":'';
		$_SGLOBAL['db']->query("UPDATE ".tname('show')." SET credit=credit+$showcredit $notesql WHERE uid='$_SGLOBAL[supe_uid]'");
	} else {
		inserttable('show', array('uid'=>$_SGLOBAL['supe_uid'], 'username'=>$_SGLOBAL['supe_username'], 'credit'=>$showcredit, 'note'=>$_POST['note']), 0, true);
	}

	//减少自己的积分
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$showcredit WHERE uid='$space[uid]'");
	
	//feed
	feed_add('show', cplang('feed_showcredit_self'), array('credit'=>$showcredit), '', array(), $_POST['note']);
		
	showmessage('showcredit_do_success', "network.php?ac=space&view=show");
}

if(empty($_GET['op'])) {

	//空间大小
	$maxattachsize = checkperm('maxattachsize');
	if(empty($maxattachsize)) {
		$percent = 0;
		$maxattachsize = '-';
	} else {
		$maxattachsize = $maxattachsize + $space['addsize'];//额外空间
		$percent = intval($space['attachsize']/$maxattachsize*100);
		$maxattachsize = formatsize($maxattachsize);
	}
	$space['attachsize'] = formatsize($space['attachsize']);
	
	//用户组
	$space['grouptitle'] = checkperm('grouptitle');
	
	@include_once(S_ROOT.'./data/data_creditrule.php');
	if(empty($_SGLOBAL['creditrule'])) {
		$get = $pay = array();
	} else {
		$get = $_SGLOBAL['creditrule']['get'];
		$pay = $_SGLOBAL['creditrule']['pay'];
	}

	$groups = array();
	@include_once(S_ROOT.'./data/data_usergroup.php');
	$space['grouptitle'] = checkperm('grouptitle');
	foreach ($_SGLOBAL['usergroup'] as $gid => $value) {
		if(empty($value['system'])) $groups[] = $value;
	}

} elseif ($_GET['op'] == 'exchange') {
	
	@include_once(S_ROOT.'./uc_client/data/cache/creditsettings.php');
	if(submitcheck('exchangesubmit')) {
		$netamount = $tocredits = 0;
		$tocredits = $_POST['tocredits'];
		$outexange = strexists($tocredits, '|');
		if(!$outexange && !$_CACHE['creditsettings'][$tocredits]['ratio']) {
			showmessage('credits_exchange_invalid');
		}
		$amount = intval($_POST['amount']);
		if($amount <= 0) {
			showmessage('credits_transaction_amount_invalid');
		}
		@include_once(S_ROOT.'./uc_client/client.php');
		$ucresult = uc_user_login($_SGLOBAL['supe_username'], $_POST['password']);
		list($tmp['uid']) = saddslashes($ucresult);
		
		if($tmp['uid'] <= 0) {
			showmessage('credits_password_invalid');
		} elseif($space['credit']-$amount < 0) {
			showmessage('credits_balance_insufficient');
		}
		$netamount = floor($amount * 1/$_CACHE['creditsettings'][$tocredits]['ratio']);
		list($toappid, $tocredits) = explode('|', $tocredits);
		
		$ucresult = uc_credit_exchange_request($_SGLOBAL['supe_uid'], $_CACHE['creditsettings'][$tocredits]['creditsrc'], $tocredits, $toappid, $netamount);
		if(!$ucresult) {
			showmessage('extcredits_dataerror');
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$amount WHERE uid='$_SGLOBAL[supe_uid]'");
		
		showmessage('do_success', 'cp.php?ac=credit&op=exchange');
	} elseif(empty($_CACHE['creditsettings'])) {
		showmessage('integral_convertible_unopened');
	}
	
} elseif ($_GET['op'] == 'addsize') {

	@include_once(S_ROOT.'./data/data_creditrule.php');
	if(empty($_SGLOBAL['creditrule'])) {
		$get = $pay = array();
	} else {
		$get = $_SGLOBAL['creditrule']['get'];
		$pay = $_SGLOBAL['creditrule']['pay'];
	}
	
	//更新统计
	$query = $_SGLOBAL['db']->query("SELECT SUM(size) FROM ".tname('pic')." WHERE uid='$_SGLOBAL[supe_uid]'");
	$allsize = $_SGLOBAL['db']->result($query, 0);
	if($allsize != $space['attachsize']) {
		$space['attachsize'] = $allsize;
		updatetable('space', array('attachsize'=>$allsize), array('uid'=>$_SGLOBAL['supe_uid']));
	}
	
	if(empty($pay['attach'])) {
		showmessage('not_enabled_this_feature');
	}
	
	//空间大小
	$maxattachsize = checkperm('maxattachsize');
	if(empty($maxattachsize)) {
		$sizewidth = 0;
		$maxattachsize = '-';
	} else {
		$maxattachsize = $maxattachsize + $space['addsize'];//额外空间
		$sizewidth = intval($space['attachsize']/$maxattachsize*100);
		$maxattachsize = formatsize($maxattachsize);
	}
	$space['attachsize'] = formatsize($space['attachsize']);
	
	//提交处理
	if(submitcheck('addsizesubmit')) {
		$addsize = intval($_POST['addsize']);
		if($addsize < 1) {
			showmessage('space_size_inappropriate');
		}
		$needcredit = $addsize * $pay['attach'];
		if($needcredit > $space['credit']) {
			showmessage('integral_inadequate','',1,array($space['credit'],$needcredit));
		}
		//兑换空间
		$addsize = $addsize*1024*1024;
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$needcredit, addsize=addsize+$addsize WHERE uid='$_SGLOBAL[supe_uid]'");
		
		//feed
		feed_add('credit', cplang('feed_add_attachsize'), array('credit'=>$needcredit, 'size'=>formatsize($addsize)));
	
		showmessage('do_success', 'cp.php?ac=credit&op=addsize');
	}
}

include_once template("cp_credit");

?>