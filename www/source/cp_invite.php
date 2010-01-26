<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_invite.php 10977 2009-01-14 02:30:13Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$siteurl = getsiteurl();

$maxcount = 50;//最多好友邀请

$pay_invite = creditrule('pay', 'invite');
$get_invite = creditrule('get', 'invite');
$appid = empty($_GET['app']) ? 0 : intval($_GET['app']);

$inviteapp = $invite_code = '';
if(empty($pay_invite) || $appid) {
	$pay_invite = 0;
	$invite_code = space_key($space, $appid);
}

$siteurl = getsiteurl();
$spaceurl = $siteurl.'space.php?uid='.$_SGLOBAL['supe_uid'];
$mailvar = array(
	"<a href=\"$spaceurl\"><img src=\"".avatar($space['uid'], 'middle')."\" border=\"0\"></a><br>".$_SN[$space['uid']],
	$_SN[$space['uid']],
	$_SCONFIG['sitename'],
	'',
	'',
	$spaceurl,
	''
);

//取出相应的应用
$appinfo = array();
if($appid) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myapp')." WHERE appid='$appid'");
	$appinfo = $_SGLOBAL['db']->fetch_array($query);
	if($appinfo) {
		$inviteapp = "&amp;app=$appid";
		$mailvar[6] = $appinfo['appname'];
	} else {
		$appid = 0;
	}
}
//处理邮件邀请
if(submitcheck('emailinvite')) {
	set_time_limit(0);//设置超时时间
	if($_SCONFIG['closeinvite']) {
		showmessage('close_invite');
	}
	$mails = array_unique(explode(",", $_POST['email']));
	$invitenum = 0;
	$failingmail = array();
	foreach($mails as $key => $value) {
		$value = trim($value);
		if(empty($value) || !isemail($value)) {
			$failingmail[] = $value;
			continue;
		}
		
		if($pay_invite) {
			//计算积分扣减积分
			$credit = intval($pay_invite)*($invitenum+1);
			if(!isemail($value) || ($pay_invite && $credit > $space['credit'])) {
				$failingmail[] = $value;
				continue;
			}
	
			$code = strtolower(random(6));
			$setarr = array(
				'uid' => $_SGLOBAL['supe_uid'],
				'code' => $code,
				'email' => saddslashes($value),
				'type' => 1
			);
			$id = inserttable('invite', $setarr, 1);
			if($id) {
				$mailvar[4] = "{$siteurl}invite.php?{$id}{$code}{$inviteapp}";
				createmail($value, $mailvar);
				$invitenum++;
			} else {
				$failingmail[] = $value;
			}
		} else {
			$mailvar[4] = "{$siteurl}invite.php?u=$space[uid]&amp;c=$invite_code{$inviteapp}";
			if($appid) {
				$mailvar[6] = $appinfo['appname'];
			}
			createmail($value, $mailvar);
		}
	}
	if($pay_invite && $invitenum) {
		$credit = intval($pay_invite)*$invitenum;
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$credit WHERE uid='$_SGLOBAL[supe_uid]'");
	}
	if($failingmail) {
		showmessage('send_result_2', '', 1, array(implode('<br>', $failingmail)));
	} else {
		showmessage('send_result_1');
	}
}
if($_GET['op'] == 'resend') {
	
	$id = $_GET['id'] ? intval($_GET['id']) : 0;
	if(submitcheck('resendsubmit')) {
		if(empty($id)) {
			showmessage('send_result_3');
		}
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE id='$id' AND uid='$_SGLOBAL[supe_uid]' ORDER BY id DESC");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($pay_invite) {
				$inviteurl = "{$siteurl}invite.php?{$value[id]}{$value[code]}";
			} else {
				$inviteurl = "{$siteurl}invite.php?u=$space[uid]&amp;c=$invite_code";
			}
			$mailvar[4] = $inviteurl;
			createmail($value['email'], $mailvar);
			showmessage('send_result_1', $_POST['refer']);
		} else {
			showmessage('send_result_3');
		}
	}
}elseif($_GET['op'] == 'delete') {
	
	$id = $_GET['id'] ? intval($_GET['id']) : 0;
	if(empty($id)) {
		showmessage('there_is_no_record_of_invitation_specified');
	}
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE id='$id' AND uid='$_SGLOBAL[supe_uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(submitcheck('deletesubmit')) {
			$_SGLOBAL['db']->query("DELETE FROM ".tname('invite')." WHERE id='$id'");
			showmessage('do_success', $_POST['refer']);
		}
	} else {
		showmessage('there_is_no_record_of_invitation_specified');
	}
	
} else {
	$list = $flist = array();
	$count = 0;
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE uid='$_SGLOBAL[supe_uid]' ORDER BY id DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['fuid'], $value['fusername']);
		if($value['fuid']) {
			$flist[] = $value;
		} else {
			if($pay_invite) {
				$inviteurl = "{$siteurl}invite.php?{$value[id]}{$value[code]}";
			} else {
				$inviteurl = "{$siteurl}invite.php?u=$space[uid]&amp;c=$invite_code{$inviteapp}";
			}
			if($value['type']) {
				$maillist[] = array(
					'email' => $value['email'],
					'url' => $inviteurl,
					'id' => $value['id']
				);
			} else {
				$list[] = $inviteurl;//没有发送的
				$count++;
			}
		}
	}
	
	if($inviteurl) {
		$mailvar[4] = $inviteurl;
	} elseif($pay_invite) {
		$mailvar[4] = "{$siteurl}invite.php?{$value[id]}{xxxxxx}";
	} else {
		$mailvar[4] = "{$siteurl}invite.php?u=$space[uid]&amp;c=$invite_code{$inviteapp}";
	}
	
	realname_get();
		
	if($pay_invite) {
		$list_str = empty($list)?'':implode("\n", $list);
		
		$maxcount_my = $maxcount - $count;
		$maxinvitenum = empty($pay_invite)?$maxcount_my:intval($space['credit']/$pay_invite);
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
			$credit = intval($pay_invite)*$invitenum;
			if(empty($invitenum) || ($pay_invite && $credit > $space['credit'])) {
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
				}
			}
			showmessage('do_success', 'cp.php?ac=invite', 0);
		}
	}
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$uri = substr($uri, 0, strrpos($uri, '/')+1);
	$actives = array('invite'=>' class="active"');
}
include template('cp_invite');

function createmail($mail, $mailvar) {
	global $_SGLOBAL, $_SCONFIG, $space, $_SN, $appinfo;
	
	$mailvar[3] = empty($_POST['saymsg'])?'':getstr($_POST['saymsg'], 500);
	smail(0, $mail, cplang($appinfo ? 'app_invite_subject' : 'invite_subject', array($_SN[$space['uid']], $_SCONFIG['sitename'], $appinfo['appname'])), cplang($appinfo ? 'app_invite_massage' : 'invite_massage', $mailvar));
}
?>