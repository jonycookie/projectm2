<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: threadpay.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!isset($extcredits[$creditstrans])) {
	showmessage('credits_transaction_disabled');
}

$payment = $db->fetch_first("SELECT COUNT(*) AS payers, SUM(netamount) AS income FROM {$tablepre}paymentlog WHERE tid='$tid'");
$thread['payers'] = $payment['payers'];
$thread['netprice'] = !$maxincperthread || ($maxincperthread && $payment['income'] < $maxincperthread) ? floor($thread['price'] * (1 - $creditstax)) : 0;
$thread['creditstax'] = sprintf('%1.2f', $creditstax * 100).'%';
$thread['endtime'] = $maxchargespan ? gmdate("$dateformat $timeformat", $timestamp + $maxchargespan * 3600 + $timeoffset * 3600) : 0;

$firstpost = $db->fetch_first("SELECT * FROM {$tablepre}posts WHERE tid='$tid' AND first='1' LIMIT 1");
$pid = $firstpost['pid'];
$freemessage[$pid]['message'] = '';
if(preg_match_all("/\[free\](.+?)\[\/free\]/is", $firstpost['message'], $matches)) {
	foreach($matches[1] AS $match) {
		$freemessage[$pid]['message'] .= discuzcode($match, $firstpost['smileyoff'], $firstpost['bbcodeoff'], sprintf('%00b', $firstpost['htmlon']), $forum['allowsmilies'], $forum['allowbbcode'], $forum['allowimgcode'], $forum['allowhtml'], 0).'<br />';
	}
}

$attachtags = array();
if($allowgetattach) {
	if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $freemessage[$pid]['message'], $matchaids)) {
		$attachtags[$pid] = $matchaids[1];
	}
}

if($attachtags) {
	require_once DISCUZ_ROOT.'./include/attachment.func.php';
	parseattach($pid, $attachtags, $freemessage, $showimages);
}

$thread['freemessage'] = $freemessage[$pid]['message'];
unset($freemessage);
?>