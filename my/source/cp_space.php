<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_space.php 7250 2008-04-30 10:57:28Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//二级域名
$domainlength = checkperm('domainlength');

if(submitcheck('spacesubmit')) {

	$setarr = array();
	//二级域名
	$_POST['domain'] = strtolower(trim($_POST['domain']));
	if($_POST['domain'] != $space['domain']) {
		if(empty($domainlength) || empty($_POST['domain'])) {
			$setarr['domain'] = '';
		} else {
			if(strlen($_POST['domain']) < $domainlength) {
				showmessage('domain_length_error', '', 1, array($domainlength));
			}
			if(strlen($_POST['domain']) > 30) {
				showmessage('two_domain_length_not_more_than_30_characters');
			}
			if(!preg_match("/^[a-z][a-z0-9]*$/", $_POST['domain'])) {
				showmessage('only_two_names_from_english_composition_and_figures');
			}

			if(isholddomain($_POST['domain'])) {
				showmessage('domain_be_retained');//debug
			}

			$count = getcount('space', array('domain'=>$_POST['domain']));
			if($count) {
				showmessage('two_domain_have_been_occupied');
			}
			
			$setarr['domain'] = $_POST['domain'];
		}
	}
	if($setarr) updatetable('space', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	
	showmessage('do_success', 'cp.php?ac=space');
	
} elseif(submitcheck('pwdsubmit')) {
	
	if($_POST['newpasswd1'] != $_POST['newpasswd2']) {
		showmessage('password_inconsistency');
	}
	if($_POST['newpasswd1'] != addslashes($_POST['newpasswd1'])) {
		showmessage('profile_passwd_illegal');
	}
	@include_once(S_ROOT.'./uc_client/client.php');
	
	$ucresult = uc_user_edit($_SGLOBAL['supe_username'], $_POST['password'], $_POST['newpasswd1'], $space['email']);
	if($ucresult == -1) {
		showmessage('old_password_invalid');
	} elseif($ucresult == -4) {
		showmessage('email_format_is_wrong');
	} elseif($ucresult == -5) {
		showmessage('email_not_registered');
	} elseif($ucresult == -7) {
		showmessage('no_change');
	} elseif($ucresult == -8) {
		showmessage('protection_of_users');
	}
	clearcookie();
	showmessage('getpasswd_succeed', 'do.php?ac=login');
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

} elseif($_GET['op'] == 'credit') {
	
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
		$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET credit=credit-$amount WHERE uid='$_SGLOBAL[supe_uid]'");
		
		showmessage('do_success', 'cp.php?ac=space&op=exchange');
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
		showmessage('do_success', 'cp.php?ac=space&op=addsize');
	}
}

include_once template("cp_space");

?>