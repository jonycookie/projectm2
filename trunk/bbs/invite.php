<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: invite.php 11169 2007-11-08 06:30:00Z tiger $
*/

require_once './include/common.inc.php';

if($regstatus < 1) {
	showmessage('invite_close');
} elseif(!$discuz_uid || !$allowinvite) {
	showmessage('group_nopermission', NULL, 'NOPERM');
}

$action = !empty($action) ? $action : 'availablelog';

if($action == 'buyinvite') {

	$myinvitenum = intval($db->result_first("SELECT COUNT(*) FROM {$tablepre}invites WHERE uid='$discuz_uid' AND dateline>'$timestamp'-86400 AND dateline<'$timestamp'"));

	if($maxinvitenum && $myinvitenum == $maxinvitenum) {
		showmessage('invite_num_range_invalid', 'invite.php');
	}

	if(!submitcheck('buysubmit')) {
		include template('invite_get');
	} else {
		$amount = intval($amount);
		$buyinvitecredit = $amount ? $amount * $inviteprice : 0;

		if(!$amount || $amount < 0) {
			showmessage('invite_num_invalid', 'invite.php');
		} elseif(${'extcredits'.$creditstrans} < $buyinvitecredit && $buyinvitecredit) {
			showmessage('invite_credits_no_enough', 'invite.php');
		} elseif(($maxinvitenum && $myinvitenum + $amount > $maxinvitenum) || $amount > 50) {
			showmessage('invite_num_buy_range_invalid', 'invite.php');
		} elseif($buyinvitecredit && !$creditstrans) {
			showmessage('credits_transaction_disabled');
		} else {
			for($i=1; $i<=$amount; $i++) {
				$invitecode = substr(md5($discuz_uid.$timestamp.random(6)), 0, 10).random(6);
				$expiration = $timestamp + $maxinviteday * 86400;
				$db->query("INSERT INTO {$tablepre}invites (uid, dateline, expiration, inviteip, invitecode) VALUES ('$discuz_uid', '$timestamp', '$expiration', '$onlineip', '$invitecode')", 'UNBUFFERED');
			}
			if($buyinvitecredit && $creditstrans) {
				$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-'$buyinvitecredit' WHERE uid='$discuz_uid'");
			}
			showmessage('invite_buy_succeed', 'invite.php');
		}
	}


} elseif(in_array($action, array('availablelog', 'invalidlog', 'usedlog'))) {

	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;

	$cur_class = array($action => 'class="current"');

	$sql = $status = '';
	switch($action) {
		case 'availablelog':
			$status = '1';
			$sql = "SELECT dateline, expiration, invitecode, status
				FROM {$tablepre}invites
				WHERE uid='$discuz_uid' AND status IN ('1', '3') ORDER BY dateline DESC LIMIT $start_limit,$tpp";
			break;
		case 'usedlog':
			$status = '2';
			$sql = "SELECT i.dateline, i.expiration, i.invitecode, m.username, m.uid
				FROM {$tablepre}invites i, {$tablepre}members m
				WHERE i.uid='$discuz_uid' AND i.status='2' AND i.reguid=m.uid ORDER BY dateline DESC LIMIT $start_limit,$tpp";
			break;
		case 'invalidlog':
			$status = '4';
			$sql = "SELECT dateline, expiration, invitecode
				FROM {$tablepre}invites
				WHERE uid='$discuz_uid' AND status='4' ORDER BY dateline DESC LIMIT $start_limit,$tpp";
			break;
	}

	$invitenum = intval($db->result_first("SELECT COUNT(*) FROM {$tablepre}invites WHERE uid='$discuz_uid' AND status='$status'"));
	$multipage = multi($invitenum, $tpp, $page, "invite.php?action=$action");

	$invitelist = array();
	$query = $db->query("$sql");
	while($invite = $db->fetch_array($query)) {
		$invite['dateline'] = gmdate("$dateformat $timeformat", $invite['dateline'] + ($timeoffset * 3600));
		$invite['expiration'] = gmdate("$dateformat $timeformat", $invite['expiration'] + ($timeoffset * 3600));
		$invitelist[] = $invite;
	}

	include template('invite_log');

} elseif($action == 'sendinvite') {

	if(!$allowmailinvite) {
		showmessage('group_nopermission', 'invite.php');
	}

	if(!submitcheck('sendsubmit')) {

		$fromuid = $creditspolicy['promotion_register'] ? '&amp;fromuid='.$discuz_uid : '';
		$threadurl = "{$boardurl}$regname?invitecode=$invitecode$fromuid";

		$email = $db->result_first("SELECT email FROM {$tablepre}members WHERE uid='$discuz_uid'");

		include template('invite_send');
	} else {
		if(empty($fromname) || empty($fromemail) || empty($sendtoname) || empty($sendtoemail)) {
			showmessage('email_friend_invalid');
		}

		if(!$invitenum = $db->result_first("SELECT invitecode FROM {$tablepre}invites WHERE uid='$discuz_uid' AND status='1' AND invitecode='$invitecode'")) {
			showmessage('invite_invalid');
		} else {
			$db->query("UPDATE {$tablepre}invites SET status='3' WHERE uid='$discuz_uid' AND invitecode='$invitecode'");
			sendmail("$sendtoname <$sendtoemail>", 'email_to_invite_subject', 'email_to_invite_message', "$fromname <$fromemail>");
			showmessage('email_invite_succeed', 'invite.php');
		}
	}

} elseif($action == 'markinvite') {

	$changestatus = $do == 'undo' ? 1 : 3;

	if(!empty($invitecode)) {
		$db->query("UPDATE {$tablepre}invites SET status='$changestatus' WHERE uid='$discuz_uid' AND invitecode='$invitecode'");
	}

	$invite = $db->fetch_first("SELECT invitecode, dateline, expiration FROM {$tablepre}invites WHERE uid='$discuz_uid' AND invitecode='$invitecode'");
	$invite['dateline'] = gmdate("$dateformat $timeformat", $invite['dateline'] + ($timeoffset * 3600));
	$invite['expiration'] = gmdate("$dateformat $timeformat", $invite['expiration'] + ($timeoffset * 3600));

	include template('invite_log');

} else {

	showmessage('undefined_action');
}

?>