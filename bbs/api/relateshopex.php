<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: relateshopex.php 9805 2007-08-15 05:59:02Z cnteacher $
*/

error_reporting(0);

chdir('../');
require_once './include/common.inc.php';

if($passport_status == 'shopex' && ($action == 'login' || $action == 'logout') && $verify == md5($action.$forward.$passport_key) && $passport_shopex) {
	$forward = preg_match("/^http:\/\//i", $forward) ? $forward : $boardurl.$forward;
	if($action == 'login' && $discuz_uid) {
		$creditsadd = $passport_extcredits ? 'm.extcredits'.$passport_extcredits.' as credits,' : '';
		$query = $db->query("SELECT m.adminid, m.username, m.password, m.email, $creditsadd m.gender, m.bday, m.regip, m.regdate, mf.site, mf.qq, mf.msn, mf.yahoo
			FROM {$tablepre}members m JOIN {$tablepre}memberfields mf USING(uid) WHERE m.uid='$discuz_uid'");
		$member = $db->fetch_array($query);
		if($member['adminid'] == 1) {
			dheader('Location:'.$forward);
		}
		$auth = '';
		foreach($member as $key => $value) {
			$auth .= $key.'='.$value.'&';
		}
		$auth = passport_encrypt($auth, $passport_key);
	} else {
		$auth = '';
	}
	$verify = md5($action.$auth.$forward.$passport_key);

	dheader('location:'.$passport_url.'index.php?gOo=discuz_reply.do&action='.$action.($action == 'login' ? '&auth='.rawurlencode($auth) : '').'&forward='.rawurlencode($forward).'&verify='.$verify);
} else {
	dheader('location:'.$boardurl.'index.php');
}

function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}

function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}

?>