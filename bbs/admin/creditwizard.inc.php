<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: creditwizard.inc.php 10115 2007-08-24 00:58:08Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

$step = in_array($step, array(1, 2, 3)) ? $step : 1;

cpheader();

shownav('menu_tools_creditwizard');

$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable in
	('extcredits', 'initcredits', 'creditspolicy', 'creditsformula', 'creditsformulaexp',
	'creditstrans', 'creditstax', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan')");
while($setting = $db->fetch_array($query)) {
	$$setting['variable'] = $setting['value'];
}
$extcredits = unserialize($extcredits);
$initcredits = explode(',', $initcredits);
$creditspolicy = unserialize($creditspolicy);

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><?=$lang['menu_tools_creditwizard']?></td></tr>
<tr><td><?=$lang['creditwizard_tips']?></td></tr></table><br />
<?

if($step == 1) {

	if($resetcredit >= 1 && $resetcredit <= 8) {
		$initcredits[$resetcredit] = intval($initcredits[$resetcredit]);
		if(!submitcheck('confirmed')) {
			cpmsg('creditwizard_resetusercredit_warning', 'admincp.php?action=creditwizard&step=1&resetcredit='.$resetcredit, 'form');
		} else {
			$db->query("UPDATE {$tablepre}members SET extcredits$resetcredit = $initcredits[$resetcredit]", 'UNBUFFERED');
			cpmsg('creditwizard_resetusercredit_ok', 'admincp.php?action=creditwizard&step=1');
		}
		exit;
	}

	if(!$credit) {

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" align="center">
<tr class="header"><td colspan="4"><?=$lang['creditwizard_step_menu_1']?></td></tr>
<tr class="category"><td><?=$lang['credits_id']?></td><td><?=$lang['credits_title']?></td><td><?=$lang['creditwizard_status']?></td><td><?=$lang['edit']?></td></tr>
<?

		for($i = 1; $i <= 8; $i++) {
			echo '<tr align="center"><td class="altbg1">extcredits'.$i.'</td><td class="altbg2">'.$extcredits[$i]['title'].($i == $creditstrans ? $lang['creditwizard_iscreditstrans'] : '').'</td>'.
				'<td class="altbg1">'.($extcredits[$i]['available'] ? $lang['creditwizard_available'] : $lang['creditwizard_unavailable']).'</td>'.
				'<td class="altbg2"><a href="admincp.php?action=creditwizard&step=1&credit='.$i.'">['.$lang['creditwizard_detail'].']</a>'.
				'<a href="admincp.php?action=creditwizard&step=1&resetcredit='.$i.'">['.$lang['creditwizard_resetusercredit'].']</a></td></tr>';
		}

?>
</table>
<?

	} else {

		if(!submitcheck('settingsubmit')) {

			$credit = $credit >=1 && $credit <= 8 ? $credit : 1;
			$type = $type >=1 && $type <= 3 ? $type : 1;
			$typestr = '<a href="admincp.php?action=creditwizard&step=1&credit='.$credit.'&type=1">'.$lang['creditwizard_settingtype_global'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			$typestr .= '<a href="admincp.php?action=creditwizard&step=1&credit='.$credit.'&type=2">'.$lang['creditwizard_settingtype_forum'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
			$typestr .= '<a href="admincp.php?action=creditwizard&step=1&credit='.$credit.'&type=3">'.$lang['creditwizard_settingtype_usergroup'].'</a>';

			$creditselect = '<select onchange="location.href=\'admincp.php?action=creditwizard&step=1&type='.$type.'&credit=\' + this.value">';
			for($i = 1;$i <= 8;$i++) {
				$creditselect .= '<option value="'.$i.'"'.($credit == $i ? ' selected' : '').'>extcredits'.$i.($extcredits[$i]['title'] ? ' ('.$extcredits[$i]['title'].')' : '').'</option>';
			}
			$creditselect .= '</select>&nbsp;&nbsp;';
			$tips = empty($type) || $type == 1 ? $lang['creditwizard_settingtype_global_tips'] : ($type == 2 ? $lang['creditwizard_settingtype_forum_tips'] : ($type == 3 ? $lang['creditwizard_settingtype_usergroup_tips'] : ''));

?>
<form method="post" action="admincp.php?action=creditwizard&step=1&credit=<?=$credit?>&type=<?=$type?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" align="center">
<tr class="header"><td colspan="2">
<a href="admincp.php?action=creditwizard&step=1"><?=$lang['creditwizard_step_menu_1']?></a> - extcredits<?=$credit.($extcredits[$credit]['title'] ? '('.$extcredits[$credit]['title'].')' : '')?>
</td></tr>
<tr class="category"><td class="altbg2"><?=$typestr?></td><td class="altbg2" style="text-align: right"><?=$creditselect?></td></tr>
<tr class="category"><td colspan="2" class="altbg2"><?=$tips?></td></tr>
<?

			if($type == 1) {

				showtype('settings_credits_extended');
				showsetting('creditwizard_credit_title', 'settingsnew[title]', $extcredits[$credit]['title'], 'text');
				showsetting('creditwizard_credits_unit', 'settingsnew[unit]', $extcredits[$credit]['unit'], 'text');
				showsetting('creditwizard_credits_ratio', 'settingsnew[ratio]', $extcredits[$credit]['ratio'], 'text');
				showsetting('creditwizard_credits_init', 'settingsnew[init]', intval($initcredits[$credit]), 'text');
				showsetting('creditwizard_credits_available', 'settingsnew[available]', intval($extcredits[$credit]['available']), 'radio');
				showsetting('creditwizard_credits_show_in_thread', 'settingsnew[showinthread]', intval($extcredits[$credit]['showinthread']), 'radio');
				showsetting('settings_creditwizard_outport', 'settingsnew[allowexchangeout]', intval($extcredits[$credit]['allowexchangeout']), 'radio');
				showsetting('settings_creditwizard_allow_inport', 'settingsnew[allowexchangein]', intval($extcredits[$credit]['allowexchangein']), 'radio');

				showtype('settings_credits_policy');

				showsetting('settings_credits_policy_post', 'settingsnew[policy_post]', intval($creditspolicy['post'][$credit]), 'text');
				showsetting('settings_credits_policy_reply', 'settingsnew[policy_reply]', intval($creditspolicy['reply'][$credit]), 'text');
				showsetting('settings_credits_policy_digest', 'settingsnew[policy_digest]', intval($creditspolicy['digest'][$credit]), 'text');
				showsetting('settings_credits_policy_post_attach', 'settingsnew[policy_postattach]', intval($creditspolicy['postattach'][$credit]), 'text');
				showsetting('settings_credits_policy_get_attach', 'settingsnew[policy_getattach]', intval($creditspolicy['getattach'][$credit]), 'text');
				showsetting('settings_credits_policy_send_pm', 'settingsnew[policy_pm]', intval($creditspolicy['pm'][$credit]), 'text');
				showsetting('settings_credits_policy_search', 'settingsnew[policy_search]', intval($creditspolicy['search'][$credit]), 'text');
				showsetting('settings_credits_policy_promotion_visit', 'settingsnew[policy_promotion_visit]', intval($creditspolicy['promotion_visit'][$credit]), 'text');
				showsetting('settings_credits_policy_promotion_register', 'settingsnew[policy_promotion_register]', intval($creditspolicy['promotion_register'][$credit]), 'text');
				showsetting('settings_credits_policy_trade', 'settingsnew[policy_tradefinished]', intval($creditspolicy['tradefinished'][$credit]), 'text');
				showsetting('settings_credits_policy_poll', 'settingsnew[policy_votepoll]', intval($creditspolicy['votepoll'][$credit]), 'text');
				showsetting('settings_credits_lowerlimit', 'settingsnew[lowerlimit]', intval($extcredits[$credit]['lowerlimit']), 'text');

?>
<tr><td colspan="2" class="altbg1"><?=$lang['settings_credits_policy_comment']?></td></tr>
<?

				showtype('', 'bottom');

?>
<br /><center>
<input class="button" type="reset" name="settingsubmit" value="<?=$lang['reset']?>">
<input class="button" type="submit" name="settingsubmit" value="<?=$lang['submit']?>">
</form>
<?

			} elseif($type == 2) {

				require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
				$fids = implode(',', array_keys($_DCACHE['forums']));
				$query = $db->query("SELECT fid, postcredits, replycredits, getattachcredits, postattachcredits, digestcredits
					FROM {$tablepre}forumfields WHERE fid in ($fids)");
				while($forumcredit = $db->fetch_array($query)) {
					$forumcredit['postcreditsstatus'] = $forumcredit['postcredits'] ? 'checked' : '';
					$forumcredit['postcredits'] = $forumcredit['postcredits'] ? unserialize($forumcredit['postcredits']) : array();
					$forumcredit['postcredits'] = intval($forumcredit['postcredits'][$credit]);
					$forumcredit['replycreditsstatus'] = $forumcredit['replycredits'] ? 'checked' : '';
					$forumcredit['replycredits'] = $forumcredit['replycredits'] ? unserialize($forumcredit['replycredits']) : array();
					$forumcredit['replycredits'] = intval($forumcredit['replycredits'][$credit]);
					$forumcredit['getattachcreditsstatus'] = $forumcredit['getattachcredits'] ? 'checked' : '';
					$forumcredit['getattachcredits'] = $forumcredit['getattachcredits'] ? unserialize($forumcredit['getattachcredits']) : array();
					$forumcredit['getattachcredits'] = intval($forumcredit['getattachcredits'][$credit]);
					$forumcredit['postattachcreditsstatus'] = $forumcredit['postattachcredits'] ? 'checked' : '';
					$forumcredit['postattachcredits'] = $forumcredit['postattachcredits'] ? unserialize($forumcredit['postattachcredits']) : array();
					$forumcredit['postattachcredits'] = intval($forumcredit['postattachcredits'][$credit]);
					$forumcredit['digestcreditsstatus'] = $forumcredit['digestcredits'] ? 'checked' : '';
					$forumcredit['digestcredits'] = $forumcredit['digestcredits'] ? unserialize($forumcredit['digestcredits']) : array();
					$forumcredit['digestcredits'] = intval($forumcredit['digestcredits'][$credit]);
					$forumcredits[$forumcredit['fid']] = $forumcredit;
				}

				$credittable = '';
				foreach($_DCACHE['forums'] as $fid => $forum) {
					if($forum['type'] != 'group') {
						$credittable .= "<tr><td class=\"altbg1\" width=\"22%\"><input class=\"checkbox\" title=\"$lang[select_all]\" type=\"checkbox\" name=\"chkallv$fid\" onclick=\"checkallvalue(this.form, $fid, 'chkallv$fid')\">";
						$credittable .= $forum['type'] == 'forum' ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
						$credittable .= "&nbsp;<a href=\"admincp.php?frames=yes&action=forumdetail&fid=$fid\" target=\"_blank\">$forum[name]</a></td>";
						$credittable .= "<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"postcreditsstatus[$fid]\" value=\"$fid\" {$forumcredits[$fid][postcreditsstatus]}>&nbsp;<input type=\"text\" name=\"postcredits[$fid]\" size=\"2\" value=\"{$forumcredits[$fid][postcredits]}\"></td>";
						$credittable .= "<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"replycreditsstatus[$fid]\" value=\"$fid\" {$forumcredits[$fid][replycreditsstatus]}>&nbsp;<input type=\"text\" name=\"replycredits[$fid]\" size=\"2\" value=\"{$forumcredits[$fid][replycredits]}\"></td>";
						$credittable .= "<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"digestcreditsstatus[$fid]\" value=\"$fid\" {$forumcredits[$fid][digestcreditsstatus]}>&nbsp;<input type=\"text\" name=\"digestcredits[$fid]\" size=\"2\" value=\"{$forumcredits[$fid][digestcredits]}\"></td>";
						$credittable .= "<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"postattachcreditsstatus[$fid]\" value=\"$fid\" {$forumcredits[$fid][postattachcreditsstatus]}>&nbsp;<input type=\"text\" name=\"postattachcredits[$fid]\" size=\"2\" value=\"{$forumcredits[$fid][postattachcredits]}\"></td>";
						$credittable .= "<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"getattachcreditsstatus[$fid]\" value=\"$fid\" {$forumcredits[$fid][getattachcreditsstatus]}>&nbsp;<input type=\"text\" name=\"getattachcredits[$fid]\" size=\"2\" value=\"{$forumcredits[$fid][getattachcredits]}\"></td>";
						$credittable .= '</tr>';
					}
				}

				showtype('creditwizard_forum_creditspolicy', '', '', 7);

?>
<tr class="category"><td><?=$lang['forum']?></td>
<td><input class="checkbox" type="checkbox" name="chkall1" onclick="checkall(this.form, 'postcreditsstatus', 'chkall1')"> <?=$lang['forums_edit_postcredits_add']?></td>
<td><input class="checkbox" type="checkbox" name="chkall2" onclick="checkall(this.form, 'replycreditsstatus', 'chkall2')"> <?=$lang['forums_edit_replycredits_add']?></td>
<td><input class="checkbox" type="checkbox" name="chkall3" onclick="checkall(this.form, 'digestcreditsstatus', 'chkall3')"> <?=$lang['settings_credits_policy_digest']?></td>
<td><input class="checkbox" type="checkbox" name="chkall4" onclick="checkall(this.form, 'postattachcreditsstatus', 'chkall4')"> <?=$lang['settings_credits_policy_post_attach']?></td>
<td><input class="checkbox" type="checkbox" name="chkall5" onclick="checkall(this.form, 'getattachcreditsstatus', 'chkall5')"> <?=$lang['settings_credits_policy_get_attach']?></td></tr>
<?=$credittable?>
</table>
<br /><center>
<input class="button" type="button" value="<?=$lang['creditwizard_return']?>" onclick="location.href='admincp.php?action=creditwizard&step=1'">
<input class="button" type="reset" name="settingsubmit" value="<?=$lang['reset']?>">
<input class="button" type="submit" name="settingsubmit" value="<?=$lang['submit']?>">
</center>
</form>
<?

			} else {

				$query = $db->query("SELECT groupid, grouptitle, raterange FROM {$tablepre}usergroups ORDER BY type DESC, groupid");
				$raterangetable = '';
				while($group = $db->fetch_array($query)) {
					$ratemin = $ratemax = $ratemrpd = '';
					foreach(explode("\n", $group['raterange']) as $range) {
						$range = explode("\t", $range);
						if($range[0] == $credit) {
							$ratemin = $range[1];$ratemax = $range[2];$ratemrpd = $range[3];break;
						}
					}
					$raterangetable .= "<tr><td class=\"altbg1\" width=\"22%\">
					<input class=\"checkbox\" type=\"checkbox\" name=\"raterangestatus[$group[groupid]]\" value=\"1\" ".($ratemin && $ratemax && $ratemax ? 'checked' : '')."> <a href=\"admincp.php?frames=yes&action=usergroups&edit=$group[groupid]\" target=\"_blank\">$group[grouptitle]</a></td>";
					$raterangetable .= "<td class=\"altbg2\"><input type=\"text\" name=\"ratemin[$group[groupid]]\" size=\"3\" value=\"$ratemin\"></td>";
					$raterangetable .= "<td class=\"altbg1\"><input type=\"text\" name=\"ratemax[$group[groupid]]\" size=\"3\" value=\"$ratemax\"></td>";
					$raterangetable .= "<td class=\"altbg2\"><input type=\"text\" name=\"ratemrpd[$group[groupid]]\" size=\"3\" value=\"$ratemrpd\"></td></tr>";
				}

				showtype('creditwizard_forum_groupraterange', '', '', 4);

?>
<tr class="category"><td><?=$lang['forum']?></td>
<td><?=$lang['usergroups_edit_raterange_min']?></td>
<td><?=$lang['usergroups_edit_raterange_max']?></td>
<td><?=$lang['usergroups_edit_raterange_mrpd']?></td>
<?=$raterangetable?>
</table>
<br /><center>
<input class="button" type="reset" name="settingsubmit" value="<?=$lang['reset']?>">
<input class="button" type="submit" name="settingsubmit" value="<?=$lang['submit']?>">
</center>
</form>
<?

			}

		} else {

			if($type == 1) {

				if($creditstrans == $credit && empty($settingsnew['available'])) {
					cpmsg('settings_creditstrans_invalid');
				}

				$initcredits[$credit] = $settingsnew['init'];
				$initcredits = implode(',', $initcredits);

				$extcredits[$credit] = array(
					'title' => dhtmlspecialchars(stripslashes($settingsnew['title'])),
					'unit' => dhtmlspecialchars(stripslashes($settingsnew['unit'])),
					'ratio' => ($settingsnew['ratio'] > 0 ? (float)$settingsnew['ratio'] : 0),
					'available' => $settingsnew['available'],
					'showinthread' => $settingsnew['showinthread'],
					'allowexchangeout' => $settingsnew['allowexchangeout'],
					'allowexchangein' => $settingsnew['allowexchangein'],
					'lowerlimit' => intval($settingsnew['lowerlimit']));
				$extcredits = addslashes(serialize($extcredits));

				$creditspolicy['post'][$credit] = intval($settingsnew['policy_post']);
				$creditspolicy['reply'][$credit] = intval($settingsnew['policy_reply']);
				$creditspolicy['digest'][$credit] = intval($settingsnew['policy_digest']);
				$creditspolicy['postattach'][$credit] = intval($settingsnew['policy_postattach']);
				$creditspolicy['getattach'][$credit] = intval($settingsnew['policy_getattach']);
				$creditspolicy['pm'][$credit] = intval($settingsnew['policy_pm']);
				$creditspolicy['search'][$credit] = intval($settingsnew['policy_search']);
				$creditspolicy['promotion_visit'][$credit] = intval($settingsnew['policy_promotion_visit']);
				$creditspolicy['promotion_register'][$credit] = intval($settingsnew['policy_promotion_register']);
				$creditspolicy['tradefinished'][$credit] = intval($settingsnew['policy_tradefinished']);
				$creditspolicy['votepoll'][$credit] = intval($settingsnew['policy_votepoll']);
				$creditspolicy = serialize($creditspolicy);

				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('initcredits', '$initcredits')");
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('extcredits', '$extcredits')");
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('creditspolicy', '$creditspolicy')");

				updatecache('settings');
				cpmsg('creditwizard_edit_succeed', 'admincp.php?action=creditwizard&step=1&credit='.$credit.'&type=1');

			} elseif($type == 2) {

				require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
				$fids = implode(',', array_keys($_DCACHE['forums']));
				$query = $db->query("SELECT fid, postcredits, replycredits, getattachcredits, postattachcredits, digestcredits
					FROM {$tablepre}forumfields WHERE fid in ($fids)");
				$sqls = array();
				while($forumcredit = $db->fetch_array($query)) {
					$forumcredit['postcredits'] = $forumcredit['postcredits'] ? unserialize($forumcredit['postcredits']) : array();
					$forumcredit['postcredits'][$credit] = intval($postcredits[$forumcredit['fid']]);
					$forumcredit['postcredits'][$credit]  = $forumcredit['postcredits'][$credit] < -99 ? -99 : $forumcredit['postcredits'][$credit];
					$forumcredit['postcredits'][$credit]  = $forumcredit['postcredits'][$credit] > 99 ? 99 : $forumcredit['postcredits'][$credit];
					$sql = "postcredits='".($postcreditsstatus[$forumcredit['fid']] ? addslashes(serialize($forumcredit['postcredits'])) : '')."'";

					$forumcredit['replycredits'] = $forumcredit['replycredits'] ? unserialize($forumcredit['replycredits']) : array();
					$forumcredit['replycredits'][$credit] = intval($replycredits[$forumcredit['fid']]);
					$forumcredit['replycredits'][$credit]  = $forumcredit['replycredits'][$credit] < -99 ? -99 : $forumcredit['replycredits'][$credit];
					$forumcredit['replycredits'][$credit]  = $forumcredit['replycredits'][$credit] > 99 ? 99 : $forumcredit['replycredits'][$credit];
					$sql .= ",replycredits='".($replycreditsstatus[$forumcredit['fid']] ? addslashes(serialize($forumcredit['replycredits'])) : '')."'";

					$forumcredit['getattachcredits'] = $forumcredit['getattachcredits'] ? unserialize($forumcredit['getattachcredits']) : array();
					$forumcredit['getattachcredits'][$credit] = intval($getattachcredits[$forumcredit['fid']]);
					$forumcredit['getattachcredits'][$credit]  = $forumcredit['getattachcredits'][$credit] < -99 ? -99 : $forumcredit['getattachcredits'][$credit];
					$forumcredit['getattachcredits'][$credit]  = $forumcredit['getattachcredits'][$credit] > 99 ? 99 : $forumcredit['getattachcredits'][$credit];
					$sql .= ",getattachcredits='".($getattachcreditsstatus[$forumcredit['fid']] ? addslashes(serialize($forumcredit['getattachcredits'])) : '')."'";

					$forumcredit['postattachcredits'] = $forumcredit['postattachcredits'] ? unserialize($forumcredit['postattachcredits']) : array();
					$forumcredit['postattachcredits'][$credit] = intval($postattachcredits[$forumcredit['fid']]);
					$forumcredit['postattachcredits'][$credit]  = $forumcredit['postattachcredits'][$credit] < -99 ? -99 : $forumcredit['postattachcredits'][$credit];
					$forumcredit['postattachcredits'][$credit]  = $forumcredit['postattachcredits'][$credit] > 99 ? 99 : $forumcredit['postattachcredits'][$credit];
					$sql .= ",postattachcredits='".($postattachcreditsstatus[$forumcredit['fid']] ? addslashes(serialize($forumcredit['postattachcredits'])) : '')."'";

					$forumcredit['digestcredits'] = $forumcredit['digestcredits'] ? unserialize($forumcredit['digestcredits']) : array();
					$forumcredit['digestcredits'][$credit] = intval($digestcredits[$forumcredit['fid']]);
					$forumcredit['digestcredits'][$credit]  = $forumcredit['digestcredits'][$credit] < -99 ? -99 : $forumcredit['digestcredits'][$credit];
					$forumcredit['digestcredits'][$credit]  = $forumcredit['digestcredits'][$credit] > 99 ? 99 : $forumcredit['digestcredits'][$credit];
					$sql .= ",digestcredits='".($digestcreditsstatus[$forumcredit['fid']] ? addslashes(serialize($forumcredit['digestcredits'])) : '')."'";

					$db->query("UPDATE {$tablepre}forumfields SET $sql WHERE fid=$forumcredit[fid]", 'UNBUFFERED');
				}

				cpmsg('creditwizard_edit_succeed', 'admincp.php?action=creditwizard&step=1&credit='.$credit.'&type=2');

			} else {

				$query = $db->query("SELECT groupid, grouptitle, raterange FROM {$tablepre}usergroups");
				$raterangetable = '';
				while($group = $db->fetch_array($query)) {
					$raterangenew = '';
					$rangearray = array();
					foreach(explode("\n", $group['raterange']) as $range) {
						$ranges = explode("\t", $range);
						$rangearray[$ranges[0]] = $range;
					}
					$range = array();
					if($raterangestatus[$group['groupid']]) {
						$range[0] = $credit;
						$range[1] = intval($ratemin[$group['groupid']] < -999 ? -999 : $ratemin[$group['groupid']]);
						$range[2] = intval($ratemax[$group['groupid']] > 999 ? 999 : $ratemax[$group['groupid']]);
						$range[3] = intval($ratemrpd[$group['groupid']] > 99999 ? 99999 : $ratemrpd[$group['groupid']]);
						if(!$range[3] || $range[2] <= $range[1] || $range[3]< max(abs($range[1]), abs($range[2]))) {
							cpmsg('creditwizard_edit_rate_invalid');
						}
						$rangearray[$credit] = implode("\t", $range);
					} else {
						unset($rangearray[$credit]);
					}
					$raterangenew = $rangearray ? implode("\n", $rangearray) : '';
					$db->query("UPDATE {$tablepre}usergroups SET raterange='$raterangenew' WHERE groupid=$group[groupid]", 'UNBUFFERED');
				}

				updatecache('usergroups');
				updatecache('admingroups');
				cpmsg('creditwizard_edit_succeed', 'admincp.php?action=creditwizard&step=1&credit='.$credit.'&type=3');

			}

		}

	}

} elseif($step == 2) {

	if(!submitcheck('settingsubmit')) {

		$formulareplace .= '\'<u>'.$lang['settings_creditsformula_digestposts'].'</u>\',\'<u>'.$lang['settings_creditsformula_posts'].'</u>\',\'<u>'.$lang['settings_creditsformula_oltime'].'</u>\',\'<u>'.$lang['settings_creditsformula_pageviews'].'</u>\'';

?>
<script>

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function insertunit(text, textend) {
	$('creditsformulanew').focus();
	textend = isUndefined(textend) ? '' : textend;
	if(!isUndefined($('creditsformulanew').selectionStart)) {
		var opn = $('creditsformulanew').selectionStart + 0;
		if(textend != '') {
			text = text + $('creditsformulanew').value.substring($('creditsformulanew').selectionStart, $('creditsformulanew').selectionEnd) + textend;
		}
		$('creditsformulanew').value = $('creditsformulanew').value.substr(0, $('creditsformulanew').selectionStart) + text + $('creditsformulanew').value.substr($('creditsformulanew').selectionEnd);
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(textend != '') {
			text = text + sel.text + textend;
		}
		sel.text = text.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character', -strlen(text));
	} else {
		$('creditsformulanew').value += text;
	}
	formulaexp();
}

var formulafind = new Array('digestposts', 'posts', 'oltime', 'pageviews');
var formulareplace = new Array(<?=$formulareplace?>);
function formulaexp() {
	var result = $('creditsformulanew').value;
<?

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $extcredits[$i]['available'] ? $extcredits[$i]['title'] : $lang['settings_creditsformula_extcredits'].$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.$extcredittitle.'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="insertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		echo 'result = result.replace(/digestposts/g, \'<u>'.$lang['settings_creditsformula_digestposts'].'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.$lang['settings_creditsformula_posts'].'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.$lang['settings_creditsformula_oltime'].'</u>\');';
		echo 'result = result.replace(/pageviews/g, \'<u>'.$lang['settings_creditsformula_pageviews'].'</u>\');';

?>
	$('creditsformulaexp').innerHTML = '<u><?=$lang['settings_creditsformula_credits']?></u>=' + result;
}
</script>
<form method="post" action="admincp.php?action=creditwizard&step=2">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('creditwizard_step_menu_2', 'top');

?>
<tr><td colspan="2" class="altbg1">
<b><?=$lang['settings_creditsformula']?></b><br /><span class="smalltxt"><?=$lang['creditwizard_current_formula_comment']?></span>
<br />
<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('creditsformulanew', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('creditsformulanew', 0)">
<div style="width:90%" class="formulaeditor">
<div><?=$extcreditsbtn?><br />
<a href="###" onclick="insertunit('digestposts')"><?=$lang['settings_creditsformula_digestposts']?></a>&nbsp;
<a href="###" onclick="insertunit('posts')"><?=$lang['settings_creditsformula_posts']?></a>&nbsp;
<a href="###" onclick="insertunit('oltime')"><?=$lang['settings_creditsformula_oltime']?></a>&nbsp;
<a href="###" onclick="insertunit('pageviews')"><?=$lang['settings_creditsformula_pageviews']?></a>&nbsp;
<a href="###" onclick="insertunit('+')">&nbsp;+&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit('-')">&nbsp;-&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit('*')">&nbsp;*&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit('/')">&nbsp;/&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit('(', ')')">&nbsp;(&nbsp;)&nbsp;</a><br />
<span id="creditsformulaexp"><?=$creditsformulaexp?></span>
</div>
<textarea name="creditsformulanew" id="creditsformulanew" style="width:100%" rows="3" onkeyup="formulaexp()"><?=dhtmlspecialchars($creditsformula)?></textarea>
</div>
<br /><?=$lang['creditwizard_current_formula_notice']?>
</td></tr>
<?

		showtype('', 'bottom');

?>
<br /><center>
<input class="button" type="submit" name="settingsubmit" value="<?=$lang['submit']?>">
</center>
</form>
<?

	} else {

		if(!preg_match("/^([\+\-\*\/\.\d\(\)]|((extcredits[1-8]|digestposts|posts|pageviews|oltime)([\+\-\*\/\(\)]|$)+))+$/", $creditsformulanew) || !is_null(@eval(preg_replace("/(digestposts|posts|pageviews|oltime|extcredits[1-8])/", "\$\\1", $creditsformulanew).';'))) {
			cpmsg('settings_creditsformula_invalid');
		}

		$creditsformulaexpnew = $creditsformulanew;
		foreach(array('digestposts', 'posts', 'oltime', 'pageviews', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8') as $var) {
			if($extcredits[$creditsid = preg_replace("/^extcredits(\d{1})$/", "\\1", $var)]['available']) {
				$replacement = $extcredits[$creditsid]['title'];
			} else {
				$replacement = $lang['settings_creditsformula_'.$var];
			}
			$creditsformulaexpnew = str_replace($var, '<u>'.$replacement.'</u>', $creditsformulaexpnew);
		}
		$creditsformulaexpnew = addslashes('<u>'.$lang['settings_creditsformula_credits'].'</u>='.$creditsformulaexpnew);

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('creditsformula', '".addslashes($creditsformulanew)."')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('creditsformulaexp', '".addslashes($creditsformulaexpnew)."')");

		updatecache('settings');

		cpmsg('creditwizard_edit_succeed', 'admincp.php?action=creditwizard&step=2');

	}

} else {

	if(!submitcheck('settingsubmit')) {

?>
<form method="post" action="admincp.php?action=creditwizard&step=3">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$creditstransselect = '';
		for($i = 0; $i <= 8; $i++) {
			if($i == 0 || $extcredits[$i]['available']) {
				$creditstransselect .= '<option value="'.$i.'" '.($i == intval($creditstrans) ? 'selected' : '').'>'.($i ? 'extcredits'.$i.' ('.$extcredits[$i]['title'].')' : $lang['none']).'</option>';
			}
		}

		showtype('creditwizard_step_menu_3', 'top');

?>
<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_creditstrans']?></b><br /><?=$lang['creditwizard_creditstrans_comment']?>
</td><tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['settings_creditstrans']?></b></td><td class="altbg2"><select onchange="$('allowcreditstrans').style.display = this.value != 0 ? '' : 'none'" name="creditstransnew"><?=$creditstransselect?></select></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['settings_creditstax']?></b><br /><?=$lang['settings_creditstax_comment']?></td><td class="altbg2"><input name="creditstaxnew" id="creditstaxnew" type="text" size="8" value="<?=$creditstax?>">
<br /><input name="creditstaxradio" class="radio" type="radio" value="0.01" onclick="$('creditstaxnew').value = this.value"<? echo $creditstax == 0.01 ? ' checked' : ''; ?>> <?=$lang['low']?> (0.01)
<br /><input name="creditstaxradio" class="radio" type="radio" value="0.1" onclick="$('creditstaxnew').value = this.value"<? echo $creditstax == 0.1 ? ' checked' : ''; ?>> <?=$lang['middle']?> (0.1)
<br /><input name="creditstaxradio" class="radio" type="radio" value="0.5" onclick="$('creditstaxnew').value = this.value"<? echo $creditstax == 0.5 ? ' checked' : ''; ?>> <?=$lang['high']?> (0.5)
</td></tr>

<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_exchange']?></b><br /><?=$lang['creditwizard_exchange_comment']?>
</td><tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['settings_exchangemincredits']?></b></td><td class="altbg2"><input name="exchangemincreditsnew" id="exchangemincreditsnew" type="text" size="8" value="<?=$exchangemincredits?>">
<br /><input name="exchangemincreditsradio" class="radio" type="radio" value="100" onclick="$('exchangemincreditsnew').value = this.value"<? echo $exchangemincredits == 100 ? ' checked' : ''; ?>> <?=$lang['low']?> (100)
<br /><input name="exchangemincreditsradio" class="radio" type="radio" value="1000" onclick="$('exchangemincreditsnew').value = this.value"<? echo $exchangemincredits == 1000 ? ' checked' : ''; ?>> <?=$lang['middle']?> (1000)
<br /><input name="exchangemincreditsradio" class="radio" type="radio" value="5000" onclick="$('exchangemincreditsnew').value = this.value"<? echo $exchangemincredits == 5000 ? ' checked' : ''; ?>> <?=$lang['high']?> (5000)
</td></tr>
</table>

<div id="allowcreditstrans"<? echo !$creditstrans ? ' style="display:none"' : ''; ?>><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2">
<b><?=$lang['creditwizard_allowcreditstrans']?></b>
</td></tr>

<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_transfer']?></b><br /><?=$lang['creditwizard_transfer_comment']?>
</td><tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['settings_transfermincredits']?></b></td><td class="altbg2"><input name="transfermincreditsnew" id="transfermincreditsnew" type="text" size="8" value="<?=$transfermincredits?>">
<br /><input name="transfermincreditsradio" class="radio" type="radio" value="100" onclick="$('transfermincreditsnew').value = this.value"<? echo $transfermincredits == 100 ? ' checked' : ''; ?>> <?=$lang['low']?> (100)
<br /><input name="transfermincreditsradio" class="radio" type="radio" value="1000" onclick="$('transfermincreditsnew').value = this.value"<? echo $transfermincredits == 1000 ? ' checked' : ''; ?>> <?=$lang['middle']?> (1000)
<br /><input name="transfermincreditsradio" class="radio" type="radio" value="5000" onclick="$('transfermincreditsnew').value = this.value"<? echo $transfermincredits == 5000 ? ' checked' : ''; ?>> <?=$lang['high']?> (5000)
</td></tr>

<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_sell']?></b><br /><?=$lang['creditwizard_sell_comment']?>
</td><tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['settings_maxincperthread']?></b></td><td class="altbg2"><input name="maxincperthreadnew" id="maxincperthreadnew" type="text" size="8" value="<?=$maxincperthread?>">
<br /><input name="maxincperthreadradio" class="radio" type="radio" value="0" onclick="$('maxincperthreadnew').value = this.value"<? echo $maxincperthread == 0 ? ' checked' : ''; ?>> <?=$lang['nolimit']?> (0)
<br /><input name="maxincperthreadradio" class="radio" type="radio" value="10" onclick="$('maxincperthreadnew').value = this.value"<? echo $maxincperthread == 10 ? ' checked' : ''; ?>> <?=$lang['low']?> (10)
<br /><input name="maxincperthreadradio" class="radio" type="radio" value="50" onclick="$('maxincperthreadnew').value = this.value"<? echo $maxincperthread == 50 ? ' checked' : ''; ?>> <?=$lang['middle']?> (50)
<br /><input name="maxincperthreadradio" class="radio" type="radio" value="100" onclick="$('maxincperthreadnew').value = this.value"<? echo $maxincperthread == 100 ? ' checked' : ''; ?>> <?=$lang['high']?> (100)
</td></tr><tr><td class="altbg1" width="45%"><b><?=$lang['settings_maxchargespan']?></b></td><td class="altbg2"><input name="maxchargespannew" id="maxchargespannew" type="text" size="8" value="<?=$maxchargespan?>">
<br /><input name="maxchargespanradio" class="radio" type="radio" value="0" onclick="$('maxchargespannew').value = this.value"<? echo $maxchargespan == 0 ? ' checked' : ''; ?>> <?=$lang['nolimit']?> (0)
<br /><input name="maxchargespanradio" class="radio" type="radio" value="5" onclick="$('maxchargespannew').value = this.value"<? echo $maxchargespan == 5 ? ' checked' : ''; ?>> <?=$lang['low']?> (5)
<br /><input name="maxchargespanradio" class="radio" type="radio" value="24" onclick="$('maxchargespannew').value = this.value"<? echo $maxchargespan == 24 ? ' checked' : ''; ?>> <?=$lang['middle']?> (24)
<br /><input name="maxchargespanradio" class="radio" type="radio" value="48" onclick="$('maxchargespannew').value = this.value"<? echo $maxchargespan == 48 ? ' checked' : ''; ?>> <?=$lang['high']?> (48)
</td></tr>

<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_reward']?></b><br /><?=$lang['creditwizard_reward_comment']?>
</td></tr>

<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_ratio']?></b><br /><?=$lang['creditwizard_ratio_comment']?>
</td></tr>

<tr class="category"><td colspan="2">
<b><?=$lang['creditwizard_trade']?></b><br /><?=$lang['creditwizard_trade_comment']?>
</td></tr>

<?

		showtype('', 'bottom');

?>
</div>
<br /><center>
<input class="button" type="submit" name="settingsubmit" value="<?=$lang['submit']?>">
</center>
</form>
<?

	} else {
		if($creditstaxnew < 0 || $creditstaxnew >= 1) {
			$creditstaxnew = 0;
		}

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('creditstrans', '".((float)$creditstransnew)."')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('creditstax', '".((float)$creditstaxnew)."')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('transfermincredits', '".((float)$transfermincreditsnew)."')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('exchangemincredits', '".((float)$exchangemincreditsnew)."')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('maxincperthread', '".((float)$maxincperthreadnew)."')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('maxchargespan', '".((float)$maxchargespannew)."')");

		updatecache('settings');
		cpmsg('creditwizard_edit_succeed', 'admincp.php?action=creditwizard&step=3');
	}
}

?>