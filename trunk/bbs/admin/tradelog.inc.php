<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tradelog.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./api/alipayapi.php';
include_once language('misc');

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder');

$page = max(1, intval($page));
$start_limit = ($page - 1) * $tpp;

$filter = !isset($filter) ? -1 : $filter;
$sqlfilter = $filter >= 0 ? "WHERE status='$filter'" : '';

$count = $db->fetch_first("SELECT sum(price) as pricesum, sum(tax) as taxsum FROM {$tablepre}tradelog status $sqlfilter");

$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}tradelog $sqlfilter");
$multipage = multi($num, $tpp, $page, "admincp.php?action=tradelog&filter=$filter");

$query = $db->query("SELECT * FROM {$tablepre}tradelog $sqlfilter ORDER BY lastupdate DESC LIMIT $start_limit, $tpp");

shownav('menu_ecommerce_trade_orders');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan=6"><?=$lang['tradelog_order_status']?>: <select style="vertical-align: middle" onchange="location.href='admincp.php?action=tradelog&filter=' + this.value"><option value='-1'><?=$lang['tradelog_all_order']?></option>
<?

$statuss = trade_getstatus(0, -1);
foreach($statuss as $key => $value) {
	echo "<option value=\"$key\" ".($filter == $key ? 'selected' : '').">$value</option>";
}

?>
</select>
</td></tr>
<tr class="altbg1"><td colspan=6"><?=$lang['tradelog_order_count']?> <? echo $num;if($count['pricesum']) {?>, <?=$lang['tradelog_trade_total']?> <?=$count['pricesum']?> <?=$lang['rmb_yuan']?>, <?=$lang['tradelog_fee_total']?> <?=$count['taxsum']?> <?=$lang['rmb_yuan']?><?}?></td></tr>
</table><br />

<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td align="center" width="10%"><?=$lang['tradelog_trade_no']?></td>
<td align="center" width="15%"><?=$lang['tradelog_trade_name']?></td>
<td align="center" width="7%"><?=$lang['tradelog_buyer']?></td>
<td align="center" width="7%"><?=$lang['tradelog_seller']?></td>
<td align="center" width="10%"><?=$lang['tradelog_money']?></td>
<td align="center" width="10%"><?=$lang['tradelog_fee']?></td>
<td align="center" width="20%"><?=$lang['tradelog_order_status']?></td>
</tr>
<?

while($tradelog = $db->fetch_array($query)) {

$tradelog['status'] = trade_getstatus($tradelog['status']);
$tradelog['lastupdate'] = gmdate("$dateformat $timeformat", $tradelog['lastupdate'] + $timeoffset * 3600);
$tradelog['tradeno'] = $tradelog['offline'] ? $lang['tradelog_offline'] : $tradelog['tradeno'];
?>
	<tr>
	<td align="center" class="altbg1">&nbsp;<?=$tradelog['tradeno']?></td>
	<td align="center" class="altbg2"><a target="_blank" href="viewthread.php?do=tradeinfo&tid=<?=$tradelog['tid']?>&pid=<?=$tradelog['pid']?>"><?=$tradelog['subject']?></a></td>
	<td align="center" class="altbg1"><a target="_blank" href="space.php?action=viewpro&uid=<?=$tradelog['buyerid']?>"><?=$tradelog['buyer']?></a></td>
	<td align="center" class="altbg2"><a target="_blank" href="space.php?action=viewpro&uid=<?=$tradelog['sellerid']?>"><?=$tradelog['seller']?></a></td>
	<td align="center" class="altbg1"><?=$tradelog['price']?></td>
	<td align="center" class="altbg2"><?=$tradelog['tax']?></td>
	<td align="center" class="altbg1"><a target="_blank" href="trade.php?orderid=<?=$tradelog['orderid']?>"><?=$tradelog['status']?><br /><?=$tradelog['lastupdate']?></td>
	</tr>

<?}?>
</table>
<?=$multipage?>