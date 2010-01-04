<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: groups.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'admingroups') {

	$actionarray = array('settings', 'xspace', 'passport', 'shopex', 'forumadd', 'forumsedit', 'forumsmerge',
		'forumdetail', 'forumdelete', 'forumcopy', 'moderators', 'threadtypes', 'memberadd', 'members', 'membersmerge',
		'editgroups', 'access', 'editcredits', 'editmedals', 'memberprofile', 'profilefields', 'ipban', 'usergroups',
		'admingroups', 'ranks', 'announcements', 'styles', 'templates', 'tpladd', 'tpledit', 'modmembers',
		'modthreads', 'modreplies', 'recyclebin', 'alipay', 'orders', 'forumlinks', 'onlinelist', 'medals',
		'censor', 'discuzcodes', 'tags', 'smilies', 'icons', 'attachtypes', 'adv', 'advadd', 'advedit', 'export', 'import',
		'runquery', 'optimize', 'attachments', 'counter', 'threads', 'prune', 'pmprune', 'updatecache', 'jswizard', 'creditwizard',
		'fileperms', 'crons', 'google_config', 'qihoo_config', 'qihoo_topics',
		'pluginsconfig', 'plugins', 'pluginsedit', 'pluginhooks', 'pluginvars', 'illegallog', 'ratelog', 'modslog', 'medalslog',
		'banlog', 'cplog', 'creditslog', 'errorlog');

	if(!submitcheck('groupsubmit')) {

		if(!isset($edit) || empty($edit)) {

			$grouplist = '';
			$query = $db->query("SELECT a.*, u.radminid, u.grouptitle FROM {$tablepre}admingroups a
				LEFT JOIN {$tablepre}usergroups u ON u.groupid=a.admingid
				WHERE a.admingid<>'1' ORDER BY u.radminid, a.admingid");
			while($group = $db->fetch_array($query)) {
				$grouplist .= "<tr align=\"center\"><td class=\"altbg1\">$group[grouptitle]</td><td class=\"altbg2\">".
					($group['admingid'] <= 3 ? $lang['admingroups_type_system'] : $lang['admingroups_type_user'])."</td><td class=\"altbg1\">".$lang['usergroups_system_'.$group['radminid']].
					"</td><td class=\"altbg2\"><a href=\"admincp.php?action=usergroups&edit={$group[admingid]}&return=admingroups\">[{$lang[edit]}]</a></td><td class=\"altbg1\"><a href=\"admincp.php?action=admingroups&edit=$group[admingid]\">[{$lang[edit]}]</a></td></tr>\n";
			}

			shownav('menu_admingroups');
			showtips('admingroups_tips');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['name']?></td><td><?=$lang['type']?></td><td><?=$lang['admingroups_level']?></td><td><?=$lang['admingroups_settings_user']?></td><td><?=$lang['admingroups_settings_admin']?></td></tr>
<?=$grouplist?>
</table>
<?

		} else {

			$edit = intval($edit);
			$group = $db->fetch_first("SELECT a.*, aa.disabledactions, u.radminid, u.grouptitle FROM {$tablepre}admingroups a
				LEFT JOIN {$tablepre}usergroups u ON u.groupid=a.admingid
				LEFT JOIN {$tablepre}adminactions aa ON aa.admingid=a.admingid
				WHERE a.admingid='$edit' AND a.admingid<>'1'");

			if(!$group) {
				cpmsg('undefined_action');
			}

?>
<br /><br /><form method="post" action="admincp.php?action=admingroups&edit=<?=$edit?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

			if($group['radminid'] == 1) {

				$group['disabledactions'] = $group['disabledactions'] ? unserialize($group['disabledactions']) : array();

				showtype($lang['admingroups_edit'].' - '.$group['grouptitle'], 'top');

				foreach($actionarray as $actionstr) {
					showsetting('admingroups_edit_action_'.$actionstr, 'disabledactionnew['.$actionstr.']', !in_array($actionstr, $group['disabledactions']), 'radio');
				}

				showtype('', 'bottom');

			} else {

				$checkstick = array($group['allowstickthread'] => 'checked');

				showtype($lang['admingroups_edit'].' - '.$group['grouptitle'], 'top');
				showsetting('admingroups_edit_edit_post', 'alloweditpostnew', $group['alloweditpost'], 'radio');
				showsetting('admingroups_edit_edit_poll', 'alloweditpollnew', $group['alloweditpoll'], 'radio');
				showsetting('admingroups_edit_stick_thread', '', '', '<input class="radio" type="radio" name="allowstickthreadnew" value="0" '.$checkstick[0].'> '.$lang['admingroups_edit_stick_thread_none'].'<br /><input class="radio" type="radio" name="allowstickthreadnew" value="1" '.$checkstick[1].'> '.$lang['admingroups_edit_stick_thread_1'].'<br /><input class="radio" type="radio" name="allowstickthreadnew" value="2" '.$checkstick[2].'> '.$lang['admingroups_edit_stick_thread_2'].'<br /><input class="radio" type="radio" name="allowstickthreadnew" value="3" '.$checkstick[3].'> '.$lang['admingroups_edit_stick_thread_3'].'');
				showsetting('admingroups_edit_mod_post', 'allowmodpostnew', $group['allowmodpost'], 'radio');
				showsetting('admingroups_edit_del_post', 'allowdelpostnew', $group['allowdelpost'], 'radio');
				showsetting('admingroups_edit_ban_post', 'allowbanpostnew', $group['allowbanpost'], 'radio');
				showsetting('admingroups_edit_mass_prune', 'allowmassprunenew', $group['allowmassprune'], 'radio');
				showsetting('admingroups_edit_refund', 'allowrefundnew', $group['allowrefund'], 'radio');
				showsetting('admingroups_edit_censor_word', 'allowcensorwordnew', $group['allowcensorword'], 'radio');
				showsetting('admingroups_edit_view_ip', 'allowviewipnew', $group['allowviewip'], 'radio');
				showsetting('admingroups_edit_ban_ip', 'allowbanipnew', $group['allowbanip'], 'radio');
				showsetting('admingroups_edit_edit_user', 'alloweditusernew', $group['allowedituser'], 'radio');
				showsetting('admingroups_edit_ban_user', 'allowbanusernew', $group['allowbanuser'], 'radio');
				showsetting('admingroups_edit_mod_user', 'allowmodusernew', $group['allowmoduser'], 'radio');
				showsetting('admingroups_edit_post_announce', 'allowpostannouncenew', $group['allowpostannounce'], 'radio');
				showsetting('admingroups_edit_view_log', 'allowviewlognew', $group['allowviewlog'], 'radio');
				showsetting('admingroups_edit_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
				if($supe['status']) {
					showsetting('admingroups_supe_pushthread', 'supe_allowpushthreadnew', $group['supe_allowpushthread'], 'radio');
				}
				showtype('', 'bottom');

			}

			echo "<br /><center><input class=\"button\" type=\"submit\" name=\"groupsubmit\" value=\"$lang[submit]\"><center></form>";

		}

	} else {

		$group = $db->fetch_first("SELECT groupid, radminid FROM {$tablepre}usergroups WHERE groupid='$edit'");
		if(!$group) {
			cpmsg('undefined_action');
		}

		if($group['radminid'] == 1) {

			$dactionarray = array();
			if(is_array($disabledactionnew)) {
				foreach($disabledactionnew as $key => $value) {
					if(in_array($key, $actionarray) && !$value) {
						$dactionarray[] = $key;
					}
				}
			}

			$db->query("REPLACE INTO {$tablepre}adminactions (admingid, disabledactions)
				VALUES ('$group[groupid]', '".addslashes(serialize($dactionarray))."')");

		} else {

			$supe_allowpushthread_add = $supe['status'] ? ", supe_allowpushthread='$supe_allowpushthreadnew'" : '';
			$db->query("UPDATE {$tablepre}admingroups SET alloweditpost='$alloweditpostnew', alloweditpoll='$alloweditpollnew',
				allowstickthread='$allowstickthreadnew', allowmodpost='$allowmodpostnew', allowbanpost='$allowbanpostnew', allowdelpost='$allowdelpostnew',
				allowmassprune='$allowmassprunenew', allowrefund='$allowrefundnew', allowcensorword='$allowcensorwordnew',
				allowviewip='$allowviewipnew', allowbanip='$allowbanipnew', allowedituser='$alloweditusernew', allowbanuser='$allowbanusernew',
				allowmoduser='$allowmodusernew', allowpostannounce='$allowpostannouncenew', allowviewlog='$allowviewlognew',
				disablepostctrl='$disablepostctrlnew'$supe_allowpushthread_add WHERE admingid='$group[groupid]' AND admingid<>'1'");

		}

		updatecache('usergroups');
		updatecache('admingroups');
		cpmsg('admingroups_edit_succeed', 'admincp.php?action=admingroups');

	}

} elseif($action == 'usergroups') {

	if($do == 'viewsgroup') {
		$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE groupid='$sgroupid'");
		$query = $db->query("SELECT uid, username FROM {$tablepre}members WHERE groupid='$sgroupid' LIMIT 80");
		$sgroups = '';
		while($member = $db->fetch_array($query)) {
			$sgroups .= '<span style="display: block; float: left; width: 8em; overflow: hidden; margin: 2px; height: 1.5em;"><a href="space.php?action=viewpro&uid='.$member['uid'].'" target="_blank">'.$member['username'].'</a></span> ';
		}
		$sgroups .= '<div align="right" style="clear: both;"><br />'.$lang['usernum'].$num.($num > 80 ? '&nbsp;<a href="admincp.php?action=members&searchsubmit=yes&usergroupid[]='.$sgroupid.'">'.$lang['more'].'&raquo;</a>' : '').' </div>';
		ajaxshowheader();
		echo $sgroups;
		ajaxshowfooter();
		exit;
	}

	if(!submitcheck('groupsubmit')) {

		if(!$edit) {
			$sgroups = $smembers = array();
			$sgroupids = '0';
			$smembernum = $membergroup = $specialgroup = $sysgroup = '';
			$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
			$conditions = !empty($insenz['groupid']) ? "WHERE groupid<>$insenz[groupid]" : '';
			$query = $db->query("SELECT groupid, type, grouptitle, creditshigher, creditslower, stars, color, groupavatar FROM {$tablepre}usergroups $conditions ORDER BY creditshigher");
			while($group = $db->fetch_array($query)) {
				if($group['type'] == 'member') {
					$membergroup .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\"></td>\n".
						"<td class=\"altbg2\"><input type=\"text\" size=\"12\" name=\"groupnew[$group[groupid]][grouptitle]\" value=\"$group[grouptitle]\"></td>\n".
						"<td class=\"altbg1\"><input type=\"text\" size=\"6\" name=\"groupnew[$group[groupid]][creditshigher]\" value=\"$group[creditshigher]\">\n".
						"<td class=\"altbg2\">$group[creditslower]\n".
						"<td class=\"altbg1\"><input type=\"text\" size=\"2\"name=\"groupnew[$group[groupid]][stars]\" value=\"$group[stars]\"></td>\n".
						"<td class=\"altbg2\"><input type=\"text\" size=\"6\"name=\"groupnew[$group[groupid]][color]\" value=\"$group[color]\"></td>\n".
						"<td class=\"altbg1\"><input type=\"text\" size=\"12\" name=\"groupnew[$group[groupid]][groupavatar]\" value=\"$group[groupavatar]\"></td>".
						"<td class=\"altbg2\" nowrap><a href=\"admincp.php?action=usergroups&edit=$group[groupid]\">[$lang[detail]]</a></td></tr>\n";
				} elseif($group['type'] == 'system') {
					$sysgroup .= "<tr align=\"center\">\n".
						"<td class=\"altbg2\"><input type=\"text\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\"></td>\n".
						"<td class=\"altbg1\">".$lang['usergroups_system_'.$group['groupid']]."</td>\n".
						"<td class=\"altbg2\"><input type=\"text\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\"></td>\n".
						"<td class=\"altbg1\"><input type=\"text\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\"></td>\n".
						"<td class=\"altbg2\"><input type=\"text\" size=\"12\" name=\"group_avatar[$group[groupid]]\" value=\"$group[groupavatar]\"></td>\n".
						"<td class=\"altbg1\" nowrap><a href=\"admincp.php?action=usergroups&edit=$group[groupid]\">[$lang[detail]]</a></td></tr>\n";
				} elseif($group['type'] == 'special') {
					$sgroups[] = $group;
					$sgroupids .= ','.$group['groupid'];
				}
			}

			$projectselect = '';
			$project = array();
			$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='group'");
			while($project = $db->fetch_array($query)) {
				$projectselect .= '<option value="'.$project['id'].'">'.$project['name'].'</option>';
			}

			foreach($sgroups as $group) {
				if(is_array($smembers[$group['groupid']])) {
					$num = count($smembers[$group['groupid']]);
					$specifiedusers = implode('', $smembers[$group['groupid']]).($num > $smembernum[$group['groupid']] ? '<br /><div style="float: right; clear: both; margin:5px"><a href="admincp.php?action=members&searchsubmit=yes&usergroupid[]='.$group['groupid'].'" style="text-align: right;">'.$lang['more'].'&raquo;</a>&nbsp;</div>' : '<br /><br />');
					unset($smembers[$group['groupid']]);
				} else {
					$specifiedusers = '';
					$num = 0;
				}
				$specifiedusers = "<style>#specifieduser span{width: 9em; height: 2em; float: left; overflow: hidden; margin: 2px;}</style><div id=\"specifieduser\">$specifiedusers</div>";

				$specialgroup .= "<tr align=\"center\" valign=\"top\">
					<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\"></td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\"></td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"12\" name=\"group_avatar[$group[groupid]]\" value=\"$group[groupavatar]\"></td>\n".
					"<td class=\"altbg2\" nowrap><a href=\"admincp.php?action=usergroups&sgroupid=$group[groupid]&do=viewsgroup\" onclick=\"ajaxget(this.href, 'sgroup_$group[groupid]', 'sgroup_$group[groupid]', 'auto');doane(event);\">[$lang[view]]</a></td>\n".
					"<td class=\"altbg1\" nowrap><a href=\"admincp.php?action=usergroups&edit=$group[groupid]\">[$lang[detail]]</a></td></tr>\n";
				$specialgroup .= "<tbody><tr><td colspan=\"7\" id=\"sgroup_$group[groupid]\" style=\"display: none\"></td></tr></tbody>";
			}
			shownav('menu_usergroups');
			showtips('usergroups_tips');

?>
<form method="post" action="admincp.php?action=usergroups&type=member">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="8"><?=$lang['usergroups_member']?> - <?=$lang['usergroups_detail']?></td></tr>
<tr class="category" align="center"><td width="48"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['usergroups_title']?></td><td><?=$lang['members_creditshigher']?></td><td><?=$lang['members_creditslower']?></td><td><?=$lang['usergroups_stars']?></td><td><?=$lang['usergroups_color']?></td><td><?=$lang['usergroups_avatar']?></td><td><?=$lang['edit']?></td></tr>
<?=$membergroup?>

<tbody id="addnewusergroup">
<tr align="center" class="altbg1"><td><?=$lang['add_new']?> <a href="###" onclick="newnode = $('addnewusergroup2').firstChild.cloneNode(true); $('addnewusergroup').appendChild(newnode)">[+]</a></td>
<td><input type="text" size="12" name="groupnewadd[grouptitle][]"></td>
<td><input type="text" size="6" name="groupnewadd[creditshigher][]"></td>
<td>&nbsp;</td>
<td><input type="text" size="2" name="groupnewadd[stars][]"></td>
<td align="right"><?=$lang['usergroups_scheme']?></td><td colspan="3"><select name="groupnewadd[projectid][]" style="vertical-align: middle;"><option value="0"><?=$lang['none']?></option><?=$projectselect?></select></td>
</tr>
</tbody>

<tbody id="addnewusergroup2" style="display: none;"><tr align="center" class="altbg1"><td>&nbsp;</td>
<td><input type="text" size="12" name="groupnewadd[grouptitle][]"></td>
<td><input type="text" size="6" name="groupnewadd[creditshigher][]"></td>
<td>&nbsp;</td>
<td><input type="text" size="2" name="groupnewadd[stars][]"></td>
<td align="right"><?=$lang['usergroups_scheme']?></td><td colspan="3"><select name="groupnewadd[projectid][]" style="vertical-align: middle;"><option value="0"><?=$lang['none']?></option><?=$projectselect?></select></td>
</tr></tbody>

</table><br />
<center><?=$warning?><input class="button" type="submit" name="groupsubmit" value="<?=$lang['submit']?>">&nbsp;</form><br />

<form method="post" action="admincp.php?action=usergroups&type=special">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="7"><?=$lang['usergroups_special']?> - <?=$lang['usergroups_detail']?></td></tr>
<tr class="category" align="center">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td nowrap width="20%"><?=$lang['usergroups_title']?></td>
<td nowrap width="20%"><?=$lang['usergroups_stars']?></td>
<td nowrap width="15%"><?=$lang['usergroups_color']?></td>
<td nowrap width="15%"><?=$lang['usergroups_avatar']?></td>
<td nowrap width="10%"><?=$lang['view']?></td>
<td nowrap width="10%"><?=$lang['edit']?></td></tr>
<?=$specialgroup?>

<tbody id="addnewspecialgroup">
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?> <a href="###" onclick="newnode = $('addnewspecialgroup2').firstChild.cloneNode(true); $('addnewspecialgroup').appendChild(newnode)">[+]</a></td>
<td><input type="text" size="12" name="grouptitlenewadd[]"></td>
<td><input type="text" size="2" name="starsnewadd[]"></td>
<td><input type="text" size="6" name="colornewadd[]"></td>
<td><input type="text" size="12" name="groupavatarnewadd[]"></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
</tbody>

<tbody id="addnewspecialgroup2" style="display: none;"><tr align="center" class="altbg1">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="12" name="grouptitlenewadd[]"></td>
<td><input type="text" size="2" name="starsnewadd[]"></td>
<td><input type="text" size="6" name="colornewadd[]"></td>
<td><input type="text" size="12" name="groupavatarnewadd[]"></td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr><tbody>

</table><br /><center>
<input class="button" type="submit" name="groupsubmit" value="<?=$lang['submit']?>"></center></form><br />

<form method="post" action="admincp.php?action=usergroups&type=system">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['usergroups_system']?> - <?=$lang['usergroups_detail']?></td></tr>
<tr class="category" align="center">
<td><?=$lang['usergroups_title']?></td><td><?=$lang['usergroups_status']?></td><td><?=$lang['usergroups_stars']?></td><td><?=$lang['usergroups_color']?><td><?=$lang['usergroups_avatar']?></td><td><?=$lang['edit']?></td></tr>
<?=$sysgroup?>
</table><br /><center>
<input class="button" type="submit" name="groupsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		} else {

			$group = $db->fetch_first("SELECT * FROM {$tablepre}usergroups WHERE groupid='$edit'");

			if(!submitcheck('detailsubmit') && !submitcheck('saveconfigsubmit')) {
				$projectselect = "<select name=\"projectid\" onchange=\"window.location='admincp.php?action=usergroups&edit=$edit&projectid='+this.options[this.options.selectedIndex].value\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
				$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='group'");
				while($project = $db->fetch_array($query)) {
					$projectselect .= "<option value=\"$project[id]\" ".($project['id'] == $projectid ? 'selected="selected"' : NULL).">$project[name]</option>\n";
				}
				$projectselect .= '</select>';

				if(!empty($projectid)) {
					$group = @array_merge($group, unserialize($db->result_first("SELECT value FROM {$tablepre}projects WHERE id='$projectid'")));
				}

				echo "<form method=\"post\" action=\"admincp.php?action=usergroups&edit=$edit&formhash=".FORMHASH.($return == 'admingroups' ? '&return=admingroups' : '')."\">\n";

				showtype('usergroups_edit', 'top');
				showsetting('usergroups_edit_title', 'grouptitlenew', $group['grouptitle'], 'text');
				showsetting('usergroups_scheme', '', '', $projectselect);

				if($group['type'] == 'special') {
					$selectra = array($group['radminid'] => 'selected="selected"');
					showsetting('usergroups_edit_radminid', '', '', "<select name=\"radminidnew\"><option value=\"0\" $selectra[0]>$lang[none]</option><option value=\"1\" $selectra[1]>$lang[usergroups_system_1]</option><option value=\"2\" $selectra[2]>$lang[usergroups_system_2]</option><option value=\"3\" $selectra[3]>$lang[usergroups_system_3]</option>");

					showtype('usergroups_edit_system');
					echo '<tr><td colspan="2" class="altbg2">'.$lang['usergroups_edit_system_comment'].'</td></tr>';
					if($group['system'] == 'private') {
						$system = array('public' => 0, 'dailyprice' => 0, 'minspan' => 0);
					} else {
						$system = array('public' => 1, 'dailyprice' => 0, 'minspan' => 0);
						list($system['dailyprice'], $system['minspan']) = explode("\t", $group['system']);
					}
					showsetting('usergroups_edit_system_public', 'system_publicnew', $system['public'], 'radio');
					showsetting('usergroups_edit_system_dailyprice', 'system_dailypricenew', $system['dailyprice'], 'text');
					showsetting('usergroups_edit_system_minspan', 'system_minspannew', $system['minspan'], 'text');
				}

				showtype('usergroups_edit_basic');
				if($group['groupid'] == 7) {
					echo '<input type="hidden" name="allowvisitnew" value="1">';
				} else {
					showsetting('usergroups_edit_visit', 'allowvisitnew', $group['allowvisit'], 'radio');
				}
				showsetting('usergroups_edit_read_access', 'readaccessnew', $group['readaccess'], 'text');
				showsetting('usergroups_edit_view_profile', 'allowviewpronew', $group['allowviewpro'], 'radio');
				showsetting('usergroups_edit_view_stats', 'allowviewstatsnew', $group['allowviewstats'], 'radio');
				showsetting('usergroups_edit_invisible', 'allowinvisiblenew', $group['allowinvisible'], 'radio');
				showsetting('usergroups_edit_multigroups', 'allowmultigroupsnew', $group['allowmultigroups'], 'radio');
				showsetting('usergroups_edit_allowtransfer', 'allowtransfernew', $group['allowtransfer'], 'radio');
				showsetting('usergroups_edit_search', array('allowsearchnew', array(
					array(0, $lang['usergroups_edit_search_disable']),
					array(1, $lang['usergroups_edit_search_thread']),
					array(2, $lang['usergroups_edit_search_post']))), $group['allowsearch'], 'mradio');
				showsetting('usergroups_edit_avatar', array('allowavatarnew', array(
					array(0, $lang['usergroups_edit_avatar_disable']),
					array(1, $lang['usergroups_edit_avatar_board']),
					array(2, $lang['usergroups_edit_avatar_custom']),
					array(3, $lang['usergroups_edit_avatar_upload']))), $group['allowavatar'], 'mradio');
				showsetting('usergroups_edit_reasonpm', array('reasonpmnew', array(
					array(0, $lang['usergroups_edit_reasonpm_none']),
					array(1, $lang['usergroups_edit_reasonpm_reason']),
					array(2, $lang['usergroups_edit_reasonpm_pm']),
					array(3, $lang['usergroups_edit_reasonpm_both']))), $group['reasonpm'], 'mradio');
				showsetting('usergroups_edit_blog', 'allowuseblognew', $group['allowuseblog'], 'radio');
				showsetting('usergroups_edit_nickname', 'allownicknamenew', $group['allownickname'], 'radio');
				showsetting('usergroups_edit_cstatus', 'allowcstatusnew', $group['allowcstatus'], 'radio');
				showsetting('usergroups_edit_disable_periodctrl', 'disableperiodctrlnew', $group['disableperiodctrl'], 'radio');
				showsetting('usergroups_edit_max_pm_num', 'maxpmnumnew', $group['maxpmnum'], 'text');
				showsetting('usergroups_edit_hour_posts', 'maxpostsperhournew', $group['maxpostsperhour'], 'text');

				showtype('usergroups_specialthread');
				showsetting('usergroups_special_activity', 'allowpostactivitynew', $group['allowpostactivity'], 'radio');
				showsetting('usergroups_edit_post_poll', 'allowpostpollnew', $group['allowpostpoll'], 'radio');
				showsetting('usergroups_edit_vote', 'allowvotenew', $group['allowvote'], 'radio');
				showsetting('usergroups_special_reward', 'allowpostrewardnew', $group['allowpostreward'], 'radio');
				showsetting('usergroups_special_reward_min', 'minrewardpricenew', $group['minrewardprice'], "text");
				showsetting('usergroups_special_reward_max', 'maxrewardpricenew', $group['maxrewardprice'], "text");
				showsetting('usergroups_special_trade', 'allowposttradenew', $group['allowposttrade'], 'radio');
				showsetting('usergroups_special_trade_min', 'mintradepricenew', $group['mintradeprice'], "text");
				showsetting('usergroups_special_trade_max', 'maxtradepricenew', $group['maxtradeprice'], "text");
				showsetting('usergroups_special_trade_stick', 'tradesticknew', $group['tradestick'], "text");
				showsetting('usergroups_special_debate', 'allowpostdebatenew', $group['allowpostdebate'], "radio");
				$videoopen && showsetting('usergroups_special_video', 'allowpostvideonew', $group['allowpostvideo'], "radio");

				showtype('usergroups_edit_thread');
				showsetting('usergroups_edit_post', 'allowpostnew', $group['allowpost'], 'radio');
				showsetting('usergroups_edit_reply', 'allowreplynew', $group['allowreply'], 'radio');
				showsetting('usergroups_edit_direct_post', array('allowdirectpostnew', array(
					array(0, $lang['usergroups_edit_direct_post_none']),
					array(1, $lang['usergroups_edit_direct_post_reply']),
					array(2, $lang['usergroups_edit_direct_post_thread']),
					array(3, $lang['usergroups_edit_direct_post_all']))), $group['allowdirectpost'], 'mradio');
				showsetting('usergroups_edit_anonymous', 'allowanonymousnew', $group['allowanonymous'], 'radio');
				showsetting('usergroups_edit_set_read_perm', 'allowsetreadpermnew', $group['allowsetreadperm'], 'radio');
				showsetting('usergroups_edit_maxprice', 'maxpricenew', $group['maxprice'], 'text');
				showsetting('usergroups_edit_hide_code', 'allowhidecodenew', $group['allowhidecode'], 'radio');
				showsetting('usergroups_edit_html', 'allowhtmlnew', $group['allowhtml'], 'radio');
				showsetting('usergroups_edit_custom_bbcode', 'allowcusbbcodenew', $group['allowcusbbcode'], 'radio');
				showsetting('usergroups_edit_bio_bbcode', 'allowbiobbcodenew', $group['allowbiobbcode'], 'radio');
				showsetting('usergroups_edit_bio_img_code', 'allowbioimgcodenew', $group['allowbioimgcode'], 'radio');
				showsetting('usergroups_edit_max_bio_size', 'maxbiosizenew', $group['maxbiosize'], 'text');
				showsetting('usergroups_edit_sig_bbcode', 'allowsigbbcodenew', $group['allowsigbbcode'], 'radio');
				showsetting('usergroups_edit_sig_img_code', 'allowsigimgcodenew', $group['allowsigimgcode'], 'radio');
				showsetting('usergroups_edit_max_sig_size', 'maxsigsizenew', $group['maxsigsize'], 'text');

				showtype('usergroups_edit_attachment');
				showsetting('usergroups_edit_get_attach', 'allowgetattachnew', $group['allowgetattach'], 'radio');
				showsetting('usergroups_edit_post_attach', 'allowpostattachnew', $group['allowpostattach'], 'radio');
				showsetting('usergroups_edit_set_attach_perm', 'allowsetattachpermnew', $group['allowsetattachperm'], 'radio');
				showsetting('usergroups_edit_max_attach_size', 'maxattachsizenew', $group['maxattachsize'], 'text');
				showsetting('usergroups_edit_max_size_per_day', 'maxsizeperdaynew', $group['maxsizeperday'], 'text');
				showsetting('usergroups_edit_attach_ext', 'attachextensionsnew', $group['attachextensions'], 'text');


				showtype('usergroups_magic');
				showsetting('usergroups_magic_permission', array('allowmagicsnew', array(
					array(0, $lang['usergroups_magic_unallowed']),
					array(1, $lang['usergroups_magic_allow']),
					array(2, $lang['usergroups_magic_allow_and_pass']))), $group['allowmagics'], 'mradio');
				showsetting('usergroups_magic_discount', 'magicsdiscountnew', $group['magicsdiscount'], 'text');
				showsetting('usergroups_magic_max', 'maxmagicsweightnew', $group['maxmagicsweight'], 'text');

				showtype('usergroups_invite');
				showsetting('usergroups_invite_permission', 'allowinvitenew', $group['allowinvite'], 'radio');
				showsetting('usergroups_invitesend_permission', 'allowmailinvitenew', $group['allowmailinvite'], 'radio');
				showsetting('usergroups_invite_price', 'invitepricenew', $group['inviteprice'], 'text');
				showsetting('usergroups_invite_buynum', 'maxinvitenumnew', $group['maxinvitenum'], 'text');
				showsetting('usergroups_invite_maxinviteday', 'maxinvitedaynew', $group['maxinviteday'], 'text');

				showtype('', 'bottom');

				$raterangearray = array();
				foreach(explode("\n", $group['raterange']) as $range) {
					$range = explode("\t", $range);
					$raterangearray[$range[0]] = array('min' => $range[1], 'max' => $range[2], 'mrpd' => $range[3]);
				}
				echo '<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
					'<tr class="header"><td colspan="6">'.$lang['usergroups_edit_credits'].'-'.$lang['usergroups_edit_raterange'].'</td></tr>'.
					'<tr align="center" class="category"><td>&nbsp;</td><td>'.$lang['credits_id'].'</td><td>'.$lang['credits_title'].'</td><td>'.$lang['usergroups_edit_raterange_min'].'</td><td>'.$lang['usergroups_edit_raterange_max'].'</td><td>'.$lang['usergroups_edit_raterange_mrpd'].'</td></tr>';
				for($i = 1; $i <= 8; $i++) {
					if(isset($extcredits[$i])) {
						echo '<tr align="center"><td class="altbg1"><input class="checkbox" type="checkbox" name="raterangenew['.$i.'][allowrate]" value="1" '.(empty($raterangearray[$i]) ? '' : 'checked').'></td>'.
							'<td class="altbg2">extcredits'.$i.'</td>'.
							'<td class="altbg1">'.$extcredits[$i]['title'].'</td>'.
							'<td class="altbg2"><input type="text" name="raterangenew['.$i.'][min]" size="3" value="'.$raterangearray[$i]['min'].'"></td>'.
							'<td class="altbg1"><input type="text" name="raterangenew['.$i.'][max]" size="3" value="'.$raterangearray[$i]['max'].'"></td>'.
							'<td class="altbg2"><input type="text" name="raterangenew['.$i.'][mrpd]" size="3" value="'.$raterangearray[$i]['mrpd'].'"></td></tr>';
					}
				}
				echo '<tr><td colspan="6" class="altbg2">'.$lang['usergroups_edit_raterange_comment'].'</td></tr></td></tr>';
				echo "</table><br /><center><input class=\"button\" type=\"submit\" name=\"detailsubmit\" value=\"$lang[submit]\">";
				echo "&nbsp;&nbsp;&nbsp;<input class=\"button\" type=\"submit\" name=\"saveconfigsubmit\" value=\"".$lang['saveconf']."\"><center></form>";

			} else {

				$systemnew = 'private';

				if($group['type'] == 'special') {
					if($system_publicnew) {
						if($radminidnew) {
							cpmsg('usergroups_edit_public_invalid');
						} else {
							if($system_dailypricenew > 0) {
								if(!$creditstrans) {
									cpmsg('usergroups_edit_creditstrans_disabled');
								} else {
									$system_minspannew = $system_minspannew <= 0 ? 1 : $system_minspannew;
									$systemnew = intval($system_dailypricenew)."\t".intval($system_minspannew);
								}
							} else {
								$systemnew = "0\t0";
							}
						}
					}
					if(in_array($radminidnew, array(1, 2, 3))) {
						$query = $db->query("SELECT admingid FROM {$tablepre}admingroups WHERE admingid='$group[groupid]'");
						if(!$db->num_rows($query)) {
							if($radminidnew == 1) {
								$db->query("REPLACE INTO {$tablepre}admingroups (admingid, alloweditpost, alloweditpoll, allowstickthread, allowmodpost, allowdelpost, allowmassprune, allowcensorword, allowviewip, allowbanip, allowedituser, allowmoduser, allowbanuser, allowpostannounce, allowviewlog, disablepostctrl)
									VALUES ('$group[groupid]', 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)");
								$db->query("REPLACE INTO {$tablepre}adminactions (admingid, disabledactions)
									VALUES ('$group[groupid]', '')");
							} else {
								$db->query("REPLACE INTO {$tablepre}admingroups (admingid)
									VALUES ('$group[groupid]')");
							}
						}
					} else {
						$radminidnew = 0;
						$db->query("DELETE FROM {$tablepre}admingroups WHERE admingid='$group[groupid]'");
					}
				} else {
					$radminidnew = $group['type'] == 'system' && in_array($group['groupid'], array(1, 2, 3)) ? $group['groupid'] : 0;
				}

				if(is_array($raterangenew)) {
					foreach($raterangenew as $id => $rate) {
						if($id >= 1 && $id <= 8 && $rate['allowrate']) {
							$rate['min'] = intval($rate['min'] < -999 ? -999 : $rate['min']);
							$rate['max'] = intval($rate['max'] > 999 ? 999 : $rate['max']);
							$rate['mrpd'] = intval($rate['mrpd'] > 99999 ? 99999 : $rate['mrpd']);
							if(!$rate['mrpd'] || $rate['max'] <= $rate['min'] || $rate['mrpd'] < max(abs($rate['min']), abs($rate['max']))) {
								cpmsg('usergroups_edit_rate_invalid');
							} else {
								$raterangenew[$id] = implode("\t", array($id, $rate['min'], $rate['max'], $rate['mrpd']));
							}
						} else {
							unset($raterangenew[$id]);
						}
					}
				}
				$raterangenew = $raterangenew ? implode("\n", $raterangenew) : '';
				$maxpricenew = $maxpricenew < 0 ? 0 : intval($maxpricenew);
				$maxpostsperhournew = $maxpostsperhournew > 255 ? 255 : intval($maxpostsperhournew);

				$extensionarray = array();
				foreach(explode(',', $attachextensionsnew) as $extension) {
					if($extension = trim($extension)) {
						$extensionarray[] = $extension;
					}
				}
				$attachextensionsnew = implode(', ', $extensionarray);

				if($maxtradepricenew == $mintradepricenew || $maxtradepricenew < 0 || $mintradepricenew <= 0 || ($maxtradepricenew && $maxtradepricenew < $mintradepricenew)) {
					cpmsg('trade_fee_error');
				} elseif(($maxrewardpricenew != 0 && $minrewardpricenew >= $maxrewardpricenew) || $minrewardpricenew < 1 || $minrewardpricenew < 0 || $maxrewardpricenew < 0) {
					cpmsg('reward_credits_error');
				}
				$tradesticknew = $tradesticknew > 0 ? intval($tradesticknew) : 0;
				$maxinvitedaynew = $maxinvitedaynew > 0 ? intval($maxinvitedaynew) : 10;

				$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$grouptitlenew', radminid='$radminidnew', system='$systemnew', allowvisit='$allowvisitnew',
					readaccess='$readaccessnew', allowmultigroups='$allowmultigroupsnew', allowtransfer='$allowtransfernew', allowviewpro='$allowviewpronew',
					allowviewstats='$allowviewstatsnew', allowinvisible='$allowinvisiblenew', allowsearch='$allowsearchnew', allowavatar='$allowavatarnew',
					reasonpm='$reasonpmnew', allowuseblog='$allowuseblognew', allownickname='$allownicknamenew', allowcstatus='$allowcstatusnew',
					disableperiodctrl='$disableperiodctrlnew', maxpmnum='$maxpmnumnew', maxpostsperhour='$maxpostsperhournew', maxinvitenum='$maxinvitenumnew', maxinviteday='$maxinvitedaynew', allowpost='$allowpostnew', allowreply='$allowreplynew',
					allowanonymous='$allowanonymousnew', allowsetreadperm='$allowsetreadpermnew', maxprice='$maxpricenew', allowhidecode='$allowhidecodenew',
					allowhtml='$allowhtmlnew', allowpostpoll='$allowpostpollnew', allowdirectpost='$allowdirectpostnew', allowvote='$allowvotenew',
					allowcusbbcode='$allowcusbbcodenew', allowsigbbcode='$allowsigbbcodenew', allowsigimgcode='$allowsigimgcodenew', allowinvite='$allowinvitenew', allowmailinvite='$allowmailinvitenew', raterange='$raterangenew',
					maxsigsize='$maxsigsizenew', allowgetattach='$allowgetattachnew', allowpostattach='$allowpostattachnew',
					allowsetattachperm='$allowsetattachpermnew', allowpostreward='$allowpostrewardnew', maxrewardprice='$maxrewardpricenew', minrewardprice='$minrewardpricenew', inviteprice='$invitepricenew',
					maxattachsize='$maxattachsizenew', maxsizeperday='$maxsizeperdaynew', attachextensions='$attachextensionsnew',
					allowbiobbcode='$allowbiobbcodenew', allowbioimgcode='$allowbioimgcodenew', maxbiosize='$maxbiosizenew',
					maxtradeprice='$maxtradepricenew', mintradeprice='$mintradepricenew', tradestick='$tradesticknew', allowposttrade='$allowposttradenew', allowpostactivity='$allowpostactivitynew', ".($videoopen ? "allowpostvideo='$allowpostvideonew', " : '')."allowmagics='$allowmagicsnew', maxmagicsweight='$maxmagicsweightnew', magicsdiscount='$magicsdiscountnew', allowpostdebate='$allowpostdebatenew' WHERE groupid='$edit'");

				if($allowinvisiblenew == 0 && $group['allowinvisible'] != $allowinvisiblenew) {
					$db->query("UPDATE {$tablepre}members SET invisible='0' WHERE groupid='$edit'");
				}

				if($group['type'] == 'special' && $radminidnew != $group['radminid']) {
					$db->query("UPDATE {$tablepre}members SET adminid='".($radminidnew ? $radminidnew : -1)."' WHERE groupid='$edit' AND adminid='$group[radminid]'");
				}

				updatecache('usergroups');

				if(submitcheck('saveconfigsubmit')) {
					$projectid = intval($projectid);
					dheader("Location: {$boardurl}admincp.php?action=projectadd&id=$edit&type=group&projectid=$projectid");
				} else {
					cpmsg('usergroups_edit_succeed', 'admincp.php?action='.($return != 'admingroups' ? 'usergroups' : 'admingroups'));
				}
			}

		}

	} else {

		if($type == 'member') {

			$groupnewadd = array_flip_keys($groupnewadd);
			foreach($groupnewadd as $k=>$v) {
				if(!$v['grouptitle'] || !$v['creditshigher']) {
					unset($groupnewadd[$k]);
				}
			}
			$groupnewkeys = array_keys($groupnew);
			$maxgroupid = max($groupnewkeys);
			foreach($groupnewadd as $k=>$v) {
				$groupnew[$k+$maxgroupid+1] = $v;
			}
			$orderarray = array();
			if(is_array($groupnew)) {
				foreach($groupnew as $id => $group) {
					if((is_array($delete) && in_array($id, $delete)) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
						unset($groupnew[$id]);
					} else {
						$orderarray[$group['creditshigher']] = $id;
					}
				}
			}

			if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
				cpmsg('usergroups_update_credits_invalid');
			}

			ksort($orderarray);
			$rangearray = array();
			$lowerlimit = array_keys($orderarray);
			for($i = 0; $i < count($lowerlimit); $i++) {
				$rangearray[$orderarray[$lowerlimit[$i]]] = array
					(
					'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
					'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 999999999
					);
			}

			$project = $projects = array();
			$query = $db->query("SELECT * FROM {$tablepre}projects WHERE type='group'");
			while($project = $db->fetch_array($query)) {
				$project['value'] = unserialize($project['value']);
				$sqladd = '';
				foreach($project['value'] as $k=>$v) {
					$sqladd .= ",$k='$v'";
				}
				$project['sqladd'] = $sqladd;
				$projects[$project['id']] = $project;
			}

			foreach($groupnew as $id => $group) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('usergroups_update_credits_duplicate');
				}

				if(in_array($id, $groupnewkeys)) {
					$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]', color='$group[color]', groupavatar='$group[groupavatar]' WHERE groupid='$id' AND type='member'");
				} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
					$project = $projects[$group['projectid']];
					$db->query("INSERT INTO {$tablepre}usergroups SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]' $project[sqladd]");
				}
			}

			if(!empty($delete)) {
				$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid IN ('".implode('\',\'', $delete)."') AND type='member'");
			}

		} elseif($type == 'special') {

			if(is_array($grouptitlenewadd)) {
				foreach($grouptitlenewadd as $k=>$v) {
					if($v) {
						$db->query("INSERT INTO {$tablepre}usergroups (type, grouptitle, stars, color, groupavatar, allowvisit, readaccess, allowpost, allowsigbbcode)
							VALUES ('special', '$grouptitlenewadd[$k]', '$starsnewadd[$k]', '$colornewadd[$k]', '$groupavatarnewadd[$k]', '1', '1', '1', '1')");
					}
				}
			}
			$ids = $comma = '';
			if(is_array($group_title)) {
				foreach($group_title as $id => $title) {
					if($delete[$id]) {
						$ids .= "$comma'$id'";
						$comma = ',';
					} else {
						$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group_title[$id]', stars='$group_stars[$id]', color='$group_color[$id]', groupavatar='$group_avatar[$id]' WHERE groupid='$id'");
					}
				}
			}
			if($ids) {
				$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid IN ($ids) AND type='special'");
				$db->query("DELETE FROM {$tablepre}admingroups WHERE admingid IN ($ids)");
				$db->query("DELETE FROM {$tablepre}adminactions WHERE admingid IN ($ids)");
				$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
				$db->query("UPDATE {$tablepre}members SET groupid='".$db->result($query, 0)."', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
				//$db->query("UPDATE {$tablepre}members SET groupid='".$db->result($query, 0)."', adminid='0' WHERE groupid IN ($ids) AND adminid='-1'", 'UNBUFFERED');
				//$db->query("UPDATE {$tablepre}members SET groupid=adminid WHERE groupid IN ($ids) AND adminid IN ('1', '2', '3')", 'UNBUFFERED');
			}
		} elseif($type == 'system') {
			if(is_array($group_title)) {
				foreach($group_title as $id => $title) {
					$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group_title[$id]', stars='$group_stars[$id]', color='$group_color[$id]', groupavatar='$group_avatar[$id]' WHERE groupid='$id'");
				}
			}
		}

		updatecache('usergroups');
		cpmsg('usergroups_update_succeed', 'admincp.php?action=usergroups');
	}

} elseif($action == 'ranks') {

	if(!submitcheck('ranksubmit')) {

		$ranks = '';
		$query = $db->query("SELECT * FROM {$tablepre}ranks ORDER BY postshigher");
		while($rank = $db->fetch_array($query)) {
			$ranks .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$rank[rankid]}]\" value=\"$rank[rankid]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"12\" name=\"ranktitlenew[{$rank[rankid]}]\" value=\"$rank[ranktitle]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"6\" name=\"postshighernew[{$rank[rankid]}]\" value=\"$rank[postshigher]\">\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"2\"name=\"starsnew[{$rank[rankid]}]\" value=\"$rank[stars]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"6\"name=\"colornew[{$rank[rankid]}]\" value=\"$rank[color]\"></td>";
		}

		shownav('menu_ranks');
		showtips('ranks_tips');

?>
<form method="post" action="admincp.php?action=ranks">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['ranks_title']?></td><td><?=$lang['ranks_postshigher']?></td><td><?=$lang['ranks_stars']?></td><td><?=$lang['ranks_color']?></td></tr>
<?=$ranks?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type="text" size="12" name="newranktitle"></td>
<td><input type="text" size="6" name="newpostshigher"></td>
<td><input type="text" size="2" name="newstars"></td>
<td><input type="text" size="6" name="newcolor"></td>
</tr></table><br />
<center><input class="button" type="submit" name="ranksubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		if($delete) {
			$ids = implode('\',\'', $delete);
			$db->query("DELETE FROM {$tablepre}ranks WHERE rankid IN ('$ids')");
		}

		foreach($ranktitlenew as $id => $value) {
			$db->query("UPDATE {$tablepre}ranks SET ranktitle='$ranktitlenew[$id]', postshigher='$postshighernew[$id]', stars='$starsnew[$id]', color='$colornew[$id]' WHERE rankid='$id'");
		}

		if($newranktitle) {
			$db->query("INSERT INTO {$tablepre}ranks (ranktitle, postshigher, stars, color)
				VALUES ('$newranktitle', '$newpostshigher', '$newstars', '$newcolor')");
		}

		updatecache('ranks');
		cpmsg('ranks_succeed', 'admincp.php?action=ranks');
	}
}

function array_flip_keys($arr) {
	$arr2 = array();
	$arrkeys = array_keys($arr);
	list(, $first) = each(array_slice($arr, 0, 1));
	if($first) {
		foreach($first as $k=>$v) {
			foreach($arrkeys as $key) {
				$arr2[$k][$key] = $arr[$key][$k];
			}
		}
	}
	return $arr2;
}

?>