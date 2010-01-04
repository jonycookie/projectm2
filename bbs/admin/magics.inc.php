<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: magics.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder');

if($action == 'magic_config') {

	if(!submitcheck('magicsubmit')) {

		$settings = array();
		$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable IN ('magicstatus', 'magicmarket', 'maxmagicprice')");
		while($setting = $db->fetch_array($query)) {
			$settings[$setting['variable']] = $setting['value'];
		}

		shownav('magics');

?>
<form method="post" name="settings" action="admincp.php?action=magic_config">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('magics_config', 'top');
		showsetting('magics_open', 'settingsnew[magicstatus]', $settings['magicstatus'], 'radio');
		showsetting('magics_market_open', 'settingsnew[magicmarket]', $settings['magicmarket'], 'radio');
		showsetting('magics_market_percent', 'settingsnew[maxmagicprice]', $settings['maxmagicprice'], 'text');
		showtype('', 'bottom');

?>
<br /><center><input type="submit" class="button" name="magicsubmit" value="<?=$lang['submit']?>"></form>
<?

	} else {

		if(is_array($settingsnew)) {
			foreach($settingsnew as $variable => $value) {
				$db->query("UPDATE {$tablepre}settings SET value='$value' WHERE variable='$variable'");
			}
		}

		updatecache('settings');

		cpmsg('magics_config_succeed', 'admincp.php?action=magic_config');
	}


} elseif($action == 'magic') {

	if(!submitcheck('magicsubmit')) {

		$magiclist = '';
		$addtype = $typeid ? "WHERE type='".intval($typeid)."'" : '';

		$query = $db->query("SELECT * FROM {$tablepre}magics $addtype ORDER BY displayorder");
		while($magic = $db->fetch_array($query)) {
			$magictype = $lang['magics_type_'.$magic['type']];

			$magiclist .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$magic[magicid]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"3\" name=\"displayorder[$magic[magicid]]\" value=\"$magic[displayorder]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"10\" name=\"name[$magic[magicid]]\" value=\"$magic[name]\"></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=magic&typeid=$magic[type]\">$magictype</a></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"5\" name=\"price[$magic[magicid]]\" value=\"$magic[price]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"5\" name=\"num[$magic[magicid]]\" value=\"$magic[num]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"25\" name=\"description[$magic[magicid]]\" value=\"$magic[description]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"hidden\" name=\"identifier[$magic[magicid]]\" value=\"$magic[identifier]\">$magic[identifier]</td>\n".
				"<td class=\"altbg1\"><input type=\"checkbox\" class=\"checkbox\" name=\"available[$magic[magicid]]\" value=\"1\" ".(!$magic['name'] || !$magic['identifier'] || !$magic['filename'] ? 'disabled' : ($magic['available'] ? 'checked' : ''))."></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=magicedit&magicid=$magic[magicid]\">[$lang[detail]]</a></td>\n";
		}
		shownav('magics');
		showtips('magics_tips');

?>
<form method="post" action="admincp.php?action=magic">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="10"><?=$lang['magics_edit']?></td></tr>
<tr align="center" class="category">
<td><input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['display_order']?></td><td><?=$lang['name']?></td><td><?=$lang['type']?></td>
<td><?=$lang['price']?></td><td><?=$lang['num']?></td><td><?=$lang['description']?></td><td><?=$lang['magics_identifier']?></td><td><?=$lang['available']?></td><td><?=$lang['detail']?></td>
</tr>
<?=$magiclist?>
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="3"	name="newdisplayorder"></td>
<td><input type="text" size="10" name="newname"></td>
<td><select name="newtype"><option value="1" selected><?=$lang['magics_type_1']?></option><option value="2"><?=$lang['magics_type_2']?></option><option value="3"><?=$lang['magics_type_3']?></option></select></td>
<td><input type="text" size="5" name="newprice"></td>
<td><input type="text" size="5" name="newnum"></td>
<td><input type="text" size="25" name="newdescription"></td>
<td><input type="text" size="5" name="newidentifier"></td>
<td></td><td></td>
</tr></table><br />
<center><input type="submit" class="button" name="magicsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		$newname = dhtmlspecialchars(trim($newname));
		$newidentifier = dhtmlspecialchars(trim(strtoupper($newidentifier)));

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}magics WHERE magicid IN ($ids)");
			$db->query("DELETE FROM {$tablepre}membermagics WHERE magicid IN ($ids)");
			$db->query("DELETE FROM {$tablepre}magicmarket WHERE magicid IN ($ids)");
			$db->query("DELETE FROM {$tablepre}magiclog WHERE magicid IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}magics SET available='$available[$id]', name='$name[$id]', identifier='$identifier[$id]', description='$description[$id]', displayorder='$displayorder[$id]', price='$price[$id]', num='$num[$id]' WHERE magicid='$id'");
			}
		}

		if($newname != '') {
			$query = $db->query("SELECT magicid FROM {$tablepre}magics WHERE identifier='$newidentifier'");
			if($db->num_rows($query)) {
				cpmsg('magics_identifier_invalid');
			}
			$db->query("INSERT INTO {$tablepre}magics (type, name, identifier, description, displayorder, price, num) VALUES ('$newtype', '$newname', '$newidentifier', '$newdescription', '$newdisplayorder', '$newprice', '$newnum')");
		}

		updatecache('magics');
		cpmsg('magics_data_succeed', 'admincp.php?action=magic');

	}

} elseif($action == 'magicedit') {

	if(!submitcheck('magiceditsubmit')) {

		$magicid = intval($magicid);

		$magic = $db->fetch_first("SELECT * FROM {$tablepre}magics WHERE magicid='$magicid'");

		$magicperm = unserialize($magic['magicperm']);

		$groups = $fourms = array();
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups");
		while($group = $db->fetch_array($query)) {
			$groups[] = $group;
		}

		$query = $db->query("SELECT fid, name FROM {$tablepre}forums WHERE type NOT IN ('group') AND status>0");
		while($forum = $db->fetch_array($query)) {
			$forums[] = $forum;
		}

		$usergroupsperm = $targetgroupsperm = $forumperm = '';

		$num = -1;
		$usergroupsperm = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr>";

		foreach($groups as $group) {
			$num++;
			if($num && $num % 4 == 0) {
				$usergroupsperm .= "</tr><tr>";
			}
			$checked = strstr($magicperm['usergroups'], "\t$group[groupid]\t") ? 'checked' : NULL;
			$usergroupsperm .= "<td style=\"border:0px\"><input type=\"checkbox\" class=\"checkbox\" name=\"usergroupsperm[]\" value=\"$group[groupid]\" $checked> $group[grouptitle]</td>\n";
		}
		$usergroupsperm .= '</tr></table>';

		$num = -1;
		$targetgroupsperm = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr>";

		foreach($groups as $group) {
			$num++;
			if($num && $num % 4 == 0) {
				$targetgroupsperm .= "</tr><tr>";
			}
			$checked = strstr($magicperm['targetgroups'], "\t$group[groupid]\t") ? 'checked' : NULL;
			$targetgroupsperm .= "<td style=\"border:0px\"><input type=\"checkbox\" class=\"checkbox\" name=\"targetgroupsperm[]\" value=\"$group[groupid]\" $checked> $group[grouptitle]</td>\n";
		}
		$targetgroupsperm .= '</tr></table>';

		$num = -1;
		$forumperm = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr>";
		foreach($forums as $forum) {
			$num++;
			if($num && $num % 4 == 0) {
				$forumperm .= "</tr><tr>";
			}
			$checked = strstr($magicperm['forum'], "\t$forum[fid]\t") ? 'checked' : NULL;
			$forumperm .= "<td style=\"border:0px\"><input type=\"checkbox\" class=\"checkbox\" name=\"forumperm[]\" value=\"$forum[fid]\" $checked>  $forum[name]</td>\n";
		}
		$forumperm .= '</tr></table>';

		$checksupplytype = array($magic['supplytype'] => 'checked');

		shownav('magics');
		showtips('magics_edit_tips');

?>
<form method="post" action="admincp.php?action=magicedit&magicid=<?=$magicid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$typeselect = array($magic['type'] => 'selected');
		showtype($lang['magics_edit'].' - '.$magic['name'], 'top');
		showsetting('magics_edit_name', 'namenew', $magic['name'], 'text');
		showsetting('magics_edit_identifier', 'identifiernew', $magic['identifier'], 'text');
		showsetting('magics_edit_type', '', '', '<select name="typenew"><option value="1" '.$typeselect[1].'>'.$lang['magics_type_1'].'</option><option value="2" '.$typeselect[2].'>'.$lang['magics_type_2'].'</option><option value="3" '.$typeselect[3].'>'.$lang['magics_type_3'].'</option></select>');

		showsetting('magics_edit_price', 'pricenew', $magic['price'], 'text');
		showsetting('magics_edit_num', 'numnew', $magic['num'], 'text');
		showsetting('magics_edit_weight', 'weightnew', $magic['weight'], 'text');
		showsetting('magics_edit_supplytype', '', '', '<input name="supplytypenew" type="radio" class="radio" value="0" '.$checksupplytype[0].'>&nbsp;'.$lang['magics_goods_stack_none'].'<br /><input name="supplytypenew" type="radio" class="radio" value="1" '.$checksupplytype[1].'>&nbsp;'.$lang['magics_goods_stack_day'].'<br /><input name="supplytypenew" type="radio" class="radio" value="2" '.$checksupplytype[2].'>&nbsp;'.$lang['magics_goods_stack_week'].'<br /><input name="supplytypenew" type="radio" class="radio" value="3" '.$checksupplytype[3].'>&nbsp;'.$lang['magics_goods_stack_month']);
		showsetting('magics_edit_supplynum', 'supplynumnew', $magic['supplynum'], 'text');
		showsetting('magics_edit_filename', 'filenamenew', $magic['filename'], 'text');
		showsetting('magics_edit_description', 'descriptionnew', $magic['description'], 'textarea');

		showtype('magics_edit_perm');
		showsetting('magics_edit_usergroupperm', '', '', $usergroupsperm, '15%');
		if($magic['type'] == 2 || $magic['type'] == 3) {
			showsetting('magics_edit_targetgroupperm', '', '', $targetgroupsperm, '15%');
		}
		if($magic['type'] == 1) {
			showsetting('magics_edit_forumperm', '', '', $forumperm, '15%');
		}
		showtype('', 'bottom');

?>
<br /><center><input type="submit" class="button" name="magiceditsubmit" value="<?=$lang['submit']?>"></center>
</form><br />
<?

	} else {

		$namenew	= dhtmlspecialchars(trim($namenew));
		$identifiernew	= dhtmlspecialchars(trim(strtoupper($identifiernew)));
		$descriptionnew	= dhtmlspecialchars($descriptionnew);
		$filenamenew	= dhtmlspecialchars($filenamenew);
		$typenew	= ($typenew > 0 && $typenew <= 3) ? $typenew : 1;
		$availablenew   = !$identifiernew || !$filenamenew ? 0 : 1;

		$magicperm['usergroups'] = is_array($usergroupsperm) && !empty($usergroupsperm) ? "\t".implode("\t",$usergroupsperm)."\t" : '';
		$magicperm['targetgroups'] = is_array($targetgroupsperm) && !empty($targetgroupsperm) ? "\t".implode("\t",$targetgroupsperm)."\t" : '';
		$magicperm['forum'] = is_array($forumperm) && !empty($forumperm) ? "\t".implode("\t",$forumperm)."\t" : '';
		$magicpermnew = addslashes(serialize($magicperm));

		$supplytypenew = intval($supplytypenew);
		$supplynumnew = $supplytypenew ? intval($supplynumnew) : 0;

		if(!$namenew) {
			cpmsg('magics_parameter_invalid');
		}

		$query = $db->query("SELECT magicid FROM {$tablepre}magics WHERE identifier='$identifiernew' AND magicid!='$magicid'");
		if($db->num_rows($query)) {
			cpmsg('magics_identifier_invalid');
		}

		if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $filenamenew)) {
			cpmsg('magics_filename_illegal');
		} elseif(!is_readable(DISCUZ_ROOT.($magicfile = "./include/magic/$filenamenew"))) {
			cpmsg('magics_filename_invalid');
		}

		$db->query("UPDATE {$tablepre}magics SET available='$availablenew', type='$typenew', name='$namenew', identifier='$identifiernew', description='$descriptionnew', price='$pricenew', num='$numnew', supplytype='$supplytypenew', supplynum='$supplynumnew', weight='$weightnew', filename='$filenamenew', magicperm='$magicpermnew' WHERE magicid='$magicid'");

		updatecache('magics');
		cpmsg('magics_data_succeed', 'admincp.php?action=magic');

	}

} elseif($action == 'magicmarket') {

	if(!submitcheck('marketsubmit')) {

		$marketlist = '';
		$query = $db->query("SELECT ma.*, m.name, m.description, m.weight FROM {$tablepre}magicmarket ma, {$tablepre}magics m WHERE m.magicid=ma.magicid");
		while($market = $db->fetch_array($query)) {
			$market['weight'] = $market['weight'] * $market['num'];
			$marketlist .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$market[mid]\"></td>\n".
				"<td class=\"altbg2\">$market[name]</td>\n".
				"<td class=\"altbg1\">$market[username]</td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"5\" name=\"price[$market[mid]]\" value=\"$market[price]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"5\" name=\"num[$market[mid]]\" value=\"$market[num]\"></td>\n".
				"<td class=\"altbg2\">$market[weight]</td>\n".
				"<td class=\"altbg1\">$market[description]</td>\n";
		}
		shownav('magics_market');

?>
<form method="post" action="admincp.php?action=magicmarket">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="10"><?=$lang['magics_market']?></td></tr>
<tr align="center" class="category">
<td width="6%"><input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['name']?></td><td><?=$lang['magics_market_seller']?></td><td><?=$lang['price']?></td><td><?=$lang['num']?></td><td><?=$lang['weight']?></td>
<td width="40%"><?=$lang['description']?></td>
</tr>
<?=$marketlist?>
</table><br />
<center><input type="submit" class="button" name="marketsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}magicmarket WHERE mid IN ($ids)");
		}

		if(is_array($price)) {
			foreach($price as $id => $val) {
				$db->query("UPDATE {$tablepre}magicmarket SET price='$price[$id]', num='$num[$id]' WHERE mid='$id'");
			}
		}

		cpmsg('magics_data_succeed', 'admincp.php?action=magicmarket');

	}

} elseif($action == 'magiclog') {

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_magics.php';

	$lpp = empty($lpp) ? 50 : $lpp;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $lpp;

	$mpurl = "admincp.php?action=magiclog&lpp=$lpp";

	if(!empty($operations) && is_array($operations)) {
		$operationadd = "AND ma.action IN ('".implode('\',\'', $operations)."')";
		foreach($operations as $operation) {
			$mpurl .= '&operations[]='.rawurlencode($operation);
		}
	} else {
		$operationadd = '';
	}

	if(!empty($magicid)) {
		$magicidadd = "AND ma.magicid='$magicid'";
	} else {
		$magicidadd = '';
	}

	$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}magiclog ma WHERE 1 $magicidadd $operationadd");

	$multipage = multi($num, $lpp, $page, $mpurl);

	$check = array();
	$check[$magicid] = 'selected="selected"';

	$magicselect = '';
	foreach($_DCACHE['magics'] as $id => $magic) {
		$magicselect .= '<option value="'.$id.'" '.$check[$id].'>'.$magic['name'].'</option>';
	}

	$magicoperations = '';
	foreach(array('1', '2', '3', '4', '5') as $operation) {
		$magicoperations .= '<input class="checkbox" type="checkbox" name="operations[]" value="'.$operation.'" '.(!empty($operations) && is_array($operations) && in_array($operation, $operations) ? 'checked' : '').'> '.$lang['logs_magic_operation_'.$operation].' &nbsp; ';
	}

	$query = $db->query("SELECT ma.*, m.username FROM {$tablepre}magiclog ma
		LEFT JOIN {$tablepre}members m USING (uid)
		WHERE 1 $magicidadd $operationadd ORDER BY dateline DESC LIMIT $start_limit, $lpp");

	while($log = $db->fetch_array($query)) {
		$log['name'] = $_DCACHE['magics'][$log['magicid']]['name'];
		$log['dateline'] = gmdate('Y-n-j H:i', $log['dateline'] + $timeoffset * 3600);
		$log['action'] = $lang['logs_magic_operation_'.$log['action']];
		$logs .= "<tr align=\"center\"><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]</td>".
			"<td class=\"altbg2\">$log[name]</td>".
			"<td class=\"altbg1\">$log[dateline]</td>".
			"<td class=\"altbg2\">$log[amount]</td>".
			"<td class=\"altbg1\">$log[price]</td>".
			"<td class=\"altbg2\">$log[action]</td></tr>";
	}

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['logs_magic']?></td></tr>


<form method="post" action="admincp.php?action=magiclog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td width="20%"><?=$lang['logs_lpp']?></td>
<td width="62%"><input type="text" name="lpp" size="40" maxlength="40" value="<?=$lpp?>"></td>
<td width="18%"><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=magiclog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg1"><td><?=$lang['magics_type']?></td><td><select name="magicid"><option value="0"><?=$lang['magics_type_all']?></option><?=$magicselect?></select></td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=magiclog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td><?=$lang['action']?></td><td><?=$magicoperations?></td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

</table><br />

<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="16%"><?=$lang['username']?></td>
<td width="16%"><?=$lang['name']?></td>
<td width="17%"><?=$lang['time']?></td>
<td width="16%"><?=$lang['num']?></td>
<td width="15%"><?=$lang['price']?></td>
<td width="20%"><?=$lang['action']?></td>
</tr>
<?=$logs?>
</table>
<?=$multipage?>
<?

}

?>