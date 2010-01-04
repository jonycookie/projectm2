<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: ecommerce.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'alipay') {

	$settings = array();
	$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable='ec_account'");
	while($setting = $db->fetch_array($query)) {
		$settings[$setting['variable']] = $setting['value'];
	}

	if(!submitcheck('alipaysubmit')) {

		shownav('menu_ecommerce_alipay');
		if($from == 'creditwizard') {

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><?=$lang['menu_tools_creditwizard']?></td></tr>
<tr><td><?=$lang['creditwizard_tips']?></td></tr></table><br />
<?

		}
		showtips('alipay_tips');

?>
<form method="post" name="settings" action="admincp.php?action=alipay">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('alipay', 'top');
		showsetting('alipay_account', 'settingsnew[ec_account]', $settings['ec_account'], 'text');
		showtype('', 'bottom');

		echo '<br /><center>';
		echo '<input class="button" type="submit" name="alipaysubmit" value="'.$lang['submit'].'"></form>';

	} else {

		$db->query("UPDATE {$tablepre}settings SET value='$settingsnew[ec_account]' WHERE variable='ec_account'");
		updatecache('settings');

		cpmsg('alipay_succeed');

	}

} elseif($action == 'orders') {

	if(!$creditstrans || !$ec_ratio) {
		cpmsg('orders_disabled');
	}

	if(!submitcheck('ordersubmit')) {

		$statusselect = array(($orderstatus = intval($orderstatus)) => 'selected');

		$orderid = dhtmlspecialchars($orderid);
		$users = dhtmlspecialchars($users);
		$buyer = dhtmlspecialchars($buyer);
		$admin = dhtmlspecialchars($admin);
		$sstarttime = dhtmlspecialchars($sstarttime);
		$sendtime = dhtmlspecialchars($sendtime);
		$cstarttime = dhtmlspecialchars($cstarttime);
		$cendtime = dhtmlspecialchars($cendtime);

		shownav('menu_ecommerce_credit_orders');
		showtips('orders_tips');

?>
<form method="post" action="admincp.php?action=orders">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['orders_search']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['orders_search_status']?></td>
<td class="altbg2" align="right">
<select name="orderstatus">
<option value="0" <?=$statusselect[0]?>> <?=$lang['orders_search_status_all']?></option>
<option value="1" <?=$statusselect[1]?>> <?=$lang['orders_search_status_pending']?></option>
<option value="2" <?=$statusselect[2]?>> <?=$lang['orders_search_status_auto_finished']?></option>
<option value="3" <?=$statusselect[3]?>> <?=$lang['orders_search_status_manual_finished']?></option>
</select>
</td>
</tr>

<tr>
<td class="altbg1" width="45%"><?=$lang['orders_search_id']?></td>
<td class="altbg2" align="right"><input type="text" name="orderid" size="40" value="<?=$orderid?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['orders_search_users']?></td>
<td class="altbg2" align="right"><input type="text" name="users" size="40" value="<?=$users?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['orders_search_buyer']?></td>
<td class="altbg2" align="right"><input type="text" name="buyer" size="40" value="<?=$buyer?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['orders_search_admin']?></td>
<td class="altbg2" align="right"><input type="text" name="admin" size="40" value="<?=$admin?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['orders_search_submit_date']?></td>
<td class="altbg2" align="right">
<input type="text" name="sstarttime" size="10" value="<?=$sstarttime?>"> -
<input type="text" name="sendtime" size="10" value="<?=$sendtime?>"
</td>
</tr>

<tr>
<td class="altbg1"><?=$lang['orders_search_confirm_date']?></td>
<td class="altbg2" align="right">
<input type="text" name="cstarttime" size="10" value="<?=$cstarttime?>"> -
<input type="text" name="cendtime" size="10" value="<?=$cendtime?>"
</td>
</tr>

</table><br />
<center><input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$numvalidate = 0;
		if($validate) {
			$orderids = $comma = '';
			$confirmdate = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);

			$query = $db->query("SELECT * FROM {$tablepre}orders WHERE orderid IN ('".implode('\',\'', $validate)."') AND status='1'");
			while($order = $db->fetch_array($query)) {
				$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+'$order[amount]' WHERE uid='$order[uid]'");
				$orderids .= "$comma'$order[orderid]'";
				$comma = ',';

				$submitdate = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $order['submitdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
				sendpm($order['uid'], 'addfunds_subject', 'addfunds_message', $fromid = '0', $from = 'System Message');
			}
			if($numvalidate = $db->num_rows($query)) {
				$db->query("UPDATE {$tablepre}orders SET status='3', admin='$discuz_user', confirmdate='$timestamp' WHERE orderid IN ($orderids)");
			}
		}

		cpmsg('orders_validate_succeed', "admincp.php?action=orders&searchsubmit=yes&orderstatus=$orderstatus&orderid=$orderid&users=$users&buyer=$buyer&admin=$admin&sstarttime=$sstarttime&sendtime=$sendtime&cstarttime=$cstarttime&cendtime=$cendtime");

	}

	if(submitcheck('searchsubmit', 1)) {

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$sql = '';
		$sql .= $orderstatus != ''	? " AND o.status='$orderstatus'" : '';
		$sql .= $orderid != ''		? " AND o.orderid='$orderid'" : '';
		$sql .= $users != ''		? " AND m.username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')" : '';
		$sql .= $buyer != ''		? " AND o.buyer='$buyer'" : '';
		$sql .= $admin != ''		? " AND o.admin='$admin'" : '';
		$sql .= $sstarttime != ''	? " AND o.submitdate>='".(strtotime($sstarttime) - $timeoffset * 3600)."'" : '';
		$sql .= $sendtime != ''		? " AND o.submitdate<'".(strtotime($sendtime) - $timeoffset * 3600)."'" : '';
		$sql .= $cstarttime != ''	? " AND o.confirmdate>='".(strtotime($cstarttime) - $timeoffset * 3600)."'" : '';
		$sql .= $cendtime != ''		? " AND o.confirmdate<'".(strtotime($cendtime) - $timeoffset * 3600)."'" : '';

		$ordercount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}orders o, {$tablepre}members m WHERE m.uid=o.uid $sql");

		$multipage = multi($ordercount, $tpp, $page, "admincp.php?action=orders&searchsubmit=yes&orderstatus=$orderstatus&orderid=$orderid&users=$users&buyer=$buyer&admin=$admin&sstarttime=$sstarttime&sendtime=$sendtime&cstarttime=$cstarttime&cendtime=$cendtime");

		$orders = '';
		$query = $db->query("SELECT o.*, m.username
			FROM {$tablepre}orders o, {$tablepre}members m
			WHERE m.uid=o.uid $sql ORDER BY o.submitdate DESC
			LIMIT $start_limit, $tpp");

		while($order = $db->fetch_array($query)) {
			switch($order['status']) {
				case 1: $order['orderstatus'] = $lang['orders_search_status_pending']; break;
				case 2: $order['orderstatus'] = '<b>'.$lang['orders_search_status_auto_finished'].'</b>'; break;
				case 3: $order['orderstatus'] = '<b>'.$lang['orders_search_status_manual_finished'].'</b><br />(<a href="space.php?action=viewpro&username='.rawurlencode($order['admin']).'" target="_blank">'.$order['admin'].'</a>)'; break;
			}
			$order['submitdate'] = gmdate("$dateformat $timeformat", $order['submitdate'] + $timeoffset * 3600);
			$order['confirmdate'] = $order['confirmdate'] ? gmdate("$dateformat $timeformat", $order['confirmdate'] + $timeoffset * 3600) : 'N/A';

			$orders .= "<tr align=\"center\" class=\"smalltxt\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"validate[]\" value=\"$order[orderid]\" ".($order['status'] != 1 ? 'disabled' : '')."></td>\n".
				"<td class=\"altbg2\">$order[orderid]</td>\n".
				"<td class=\"altbg1\">$order[orderstatus]</td>\n".
				"<td class=\"altbg2\"><a href=\"space.php?action=viewpro&uid=$order[uid]\" target=\"_blank\">$order[username]</a></td>\n".
				"<td class=\"altbg1\"><a href=\"mailto:$order[buyer]\">$order[buyer]</a></td>\n".
				"<td class=\"altbg2\">{$extcredits[$creditstrans]['title']} $order[amount] {$extcredits[$creditstrans]['unit']}</td>\n".
				"<td class=\"altbg1\">$lang[rmb] $order[price] $lang[rmb_yuan]</td>\n".
				"<td class=\"altbg2\">$order[submitdate]</td>\n".
				"<td class=\"altbg1\">$order[confirmdate]</td></tr>\n";
		}

?>
<form method="post" action="admincp.php?action=orders">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?=$multipage?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['orders_validate']?></td>
<td><?=$lang['orders_id']?></td><td><?=$lang['orders_status']?></td><td><?=$lang['orders_username']?></td><td><?=$lang['orders_buyer']?></td>
<td><?=$lang['orders_amount']?></td><td><?=$lang['orders_price']?></td><td><?=$lang['orders_submitdate']?></td><td><?=$lang['orders_confirmdate']?></td></tr>
<?=$orders?>
</table>

<?=$multipage?><br />
<center><input class="button" type="submit" name="ordersubmit" value="<?=$lang['submit']?>">
</center>
</form>
<?

	}

} elseif($action == 'ec_credit') {

	$defaultrank = array(
		1 => 4,
		2 => 11,
		3 => 41,
		4 => 91,
		5 => 151,
		6 => 251,
		7 => 501,
		8 => 1001,
		9 => 2001,
		10 => 5001,
		11 => 10001,
		12 => 20001,
		13 => 50001,
		14 => 100001,
		15 => 200001
	);

	if(!submitcheck('creditsubmit')) {

		$ec_credit = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='ec_credit'");
		$ec_credit = $ec_credit ? unserialize($ec_credit) : array(
			'maxcreditspermonth' => '6',
			'rank' => $defaultrank
		);

		shownav('menu_ecommerce_credit');
		showtips('ec_credit_tips');

?>
<form method="post" name="settings" action="admincp.php?action=ec_credit">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('ec_credit', 'top');
		showsetting('ec_credit_maxcreditspermonth', 'ec_creditnew[maxcreditspermonth]', $ec_credit['maxcreditspermonth'], 'text');
		showtype('', 'bottom');
?>
<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['ec_credit_rank']?></td></tr>
<tr class="category" align="center"><td><?=$lang['ec_credit_rank']?></td><td><?=$lang['ec_credit_mincredits']?></td><td><?=$lang['ec_credit_maxcredits']?></td><td><?=$lang['ec_credit_sellericon']?></td><td><?=$lang['ec_credit_buyericon']?></td></tr>
<?
		foreach($ec_credit['rank'] AS $rank => $mincredits) {
			echo "<tr align=\"center\"><td class=\"altbg1\">$rank</td><td class=\"altbg2\"><input type=\"text\" size=\"12\" name=\"ec_creditnew[rank][$rank]\" value=\"$mincredits\"></td><td class=\"altbg1\">{$ec_credit[rank][$rank + 1]}</td><td class=\"altbg2\"><img src=\"images/rank/seller/$rank.gif\" border=\"0\"></td><td class=\"altbg1\"><img src=\"images/rank/buyer/$rank.gif\" border=\"0\"></td>\n";
		}
		showtype('', 'bottom');
		echo '<br /><center><input class="button" type="submit" name="creditsubmit" value="'.$lang['submit'].'"></center></form>';

	} else {

		$ec_creditnew['maxcreditspermonth'] = intval($ec_creditnew['maxcreditspermonth']);

		if(is_array($ec_creditnew['rank'])) {
			foreach($ec_creditnew['rank'] AS $rank => $mincredits) {
				$mincredits = intval($mincredits);
				if($rank == 1 && $mincredits <= 0) {
					cpmsg('ecommerce_invalidcredit');
				} elseif($rank > 1 && $mincredits <= $ec_creditnew['rank'][$rank - 1]) {
					cpmsg('ecommerce_must_larger');
				}
				$ec_creditnew['rank'][$rank] = $mincredits;
			}
		} else {
			$ec_creditnew['rank'] = $defaultrank;
		}

		$db->query("UPDATE {$tablepre}settings SET value='".serialize($ec_creditnew)."' WHERE variable='ec_credit'");
		updatecache('settings');

		cpmsg('alipay_succeed');

	}
}

?>