<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: announcements.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'announcements') {

	if(!submitcheck('announcesubmit') && !submitcheck('addsubmit') && !$edit) {

		$groupselect = $announcements = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid<4 OR groupid>6");
		while($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
		}

		$announce_type = array(0=>$lang['announce_words'], 1=>$lang['announce_url'], 2=>$lang['announce_pms']);
		$query = $db->query("SELECT * FROM {$tablepre}announcements ORDER BY displayorder, starttime DESC, id DESC");
		while($announce = $db->fetch_array($query)) {
			$disabled = $adminid != 1 && $announce['author'] != $discuz_userss ? 'disabled' : NULL;
			$announce['starttime'] = $announce['starttime'] ? gmdate($dateformat, $announce['starttime'] + $_DCACHE['settings']['timeoffset'] * 3600) : $lang['unlimited'];
			$announce['endtime'] = $announce['endtime'] ? gmdate($dateformat, $announce['endtime'] + $_DCACHE['settings']['timeoffset'] * 3600) : $lang['unlimited'];
			$announcements .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$announce[id]\" $disabled></td>\n".
				"<td class=\"altbg2\"><a href=\"./space.php?action=viewpro&username=".rawurlencode($announce['author'])."\" target=\"_blank\">$announce[author]</a></td>\n".
				"<td class=\"altbg1\"><a href=\"admincp.php?action=announcements&edit=$announce[id]\" $disabled>".dhtmlspecialchars($announce['subject'])."</a></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=announcements&edit=$announce[id]\">".cutstr(strip_tags($announce['message']), 20)."</a></td>\n".
				"<td class=\"altbg1\">".$announce_type[$announce['type']]."</td>\n".
				"<td class=\"altbg2\">$announce[starttime]</td>\n".
				"<td class=\"altbg1\">$announce[endtime]</td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"2\" name=\"displayordernew[$announce[id]]\" value=\"$announce[displayorder]\" $disabled></td></tr>\n";
		}
		$newstarttime = gmdate('Y-n-j', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
		shownav('menu_misc_announces');
		showtips('announce_tips');

?>
<form method="post" action="admincp.php?action=announcements">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="8"><?=$lang['announce_edit']?></td></tr>
<tr align="center" class="category">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['author']?></td><td><?=$lang['subject']?></td><td><?=$lang['message']?></td><td><?=$lang['announce_type']?></td><td><?=$lang['start_time']?></td><td><?=$lang['end_time']?></td><td><?=$lang['display_order']?></td></tr>
<?=$announcements?></table>
<br />
<center><input class="button" type="submit" name="announcesubmit" value="<?=$lang['submit']?>"></center>
</form><br />
<script type="text/javascript" src="include/javascript/calendar.js"></script>

<form method="post" action="admincp.php?action=announcements&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['announce_add']?></td></tr>

<tr><td width="40%" class="altbg1"><b><?=$lang['subject']?>:</b></td>
<td width="45%" class="altbg2"><input type="text" size="45" name="newsubject"></td></tr>

<tr><td width="40%" class="altbg1"><b><?=$lang['start_time']?>:</b><br /><?=$lang['announce_time_comment']?></td>
<td width="45%" class="altbg2"><input type="text" size="45" name="newstarttime" value="<?=$newstarttime?>" onclick="showcalendar(event, this)"></td></tr>

<tr><td width="40%" class="altbg1"><b><?=$lang['end_time']?>:</b><br /><?=$lang['announce_time_comment']?></td>
<td width="45%" class="altbg2"><input type="text" size="45" name="newendtime" onclick="showcalendar(event, this)"> <?=$lang['announce_end_time_comment']?></td></tr>

<tr><td width="40%" class="altbg1" valign="top"><b><?=$lang['announce_type']?>:</b></td>
<td width="45%" class="altbg2">
<input name="newtype" class="radio" type="radio" value="0" checked> <?=$lang['announce_words']?>&nbsp;
<input name="newtype" class="radio" type="radio" value="1"> <?=$lang['announce_url']?>&nbsp;
<input name="newtype" class="radio" type="radio" value="2"> <?=$lang['announce_pms']?>
</td></tr>

<tr><td width="40%" class="altbg1" valign="top"><b><?=$lang['usergroup']?>:</b><br /><?=$lang['announce_usergroup_comment']?></td>
<td width="45%" class="altbg2">
<select name="usergroupid[]" size="5" multiple="multiple" style="width: 65%">
<option value='' selected><?=$lang['all']?></option>
<?=$groupselect?>
</select>
</td></tr>

<tr><td width="40%" class="altbg1" valign="top"><b><?=$lang['message']?>:</b><br /><?=$lang['announce_message_comment']?>
<td width="45%" class="altbg2"><textarea name="newmessage" cols="60" rows="10"></textarea></td></tr>
</table>
<br />
<center><input class="button" type="submit" name="addsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} elseif($edit) {

		$announce = $db->fetch_first("SELECT * FROM {$tablepre}announcements WHERE id='$edit' AND ('$adminid'='1' OR author='$discuz_user')");
		if(!$announce) {
			cpmsg('announce_nonexistence');
		}

		if(!submitcheck('editsubmit')) {

			$groupselect = '';
			$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid<4 OR groupid>6");
			$pmgroups = explode(',', $announce['groups']);

			$groupselectall = empty($announce['groups']) || in_array(0, $pmgroups) ? ' selected' : '';
			while($group = $db->fetch_array($query)) {
				$groupselect .= "<option value=\"$group[groupid]\" ".(!$groupselectall && in_array($group['groupid'], $pmgroups) ? 'selected' : '').">$group[grouptitle]</option>\n";
			}

			$announce['starttime'] = $announce['starttime'] ? gmdate('Y-n-j', $announce['starttime'] + $_DCACHE['settings']['timeoffset'] * 3600) : "";
			$announce['endtime'] = $announce['endtime'] ? gmdate('Y-n-j', $announce['endtime'] + $_DCACHE['settings']['timeoffset'] * 3600) : "";
			$announcecheck = array(intval($announce['type']) => 'checked');
			shownav('menu_misc_announces');

?>
<form method="post" action="admincp.php?action=announcements&edit=<?=$edit?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['announce_edit']?></td></tr>

<tr><td width="21%" class="altbg1"><b><?=$lang['subject']?>:</b></td>
<td width="79%" class="altbg2"><input type="text" size="45" name="subjectnew" value="<?=dhtmlspecialchars($announce['subject'])?>"></td></tr>

<tr><td width="21%" class="altbg1"><b><?=$lang['start_time']?>:</b><br /><?=$lang['announce_time_comment']?></td>
<td width="79%" class="altbg2"><input type="text" size="45" name="starttimenew" value="<?=$announce[starttime]?>"></td></tr>

<tr><td width="21%" class="altbg1"><b><?=$lang['end_time']?>:</b><br /><?=$lang['announce_time_comment']?></td>
<td width="79%" class="altbg2"><input type="text" size="45" name="endtimenew" value="<?=$announce[endtime]?>"> <?=$lang['announce_end_time_comment']?></td></tr>

<tr><td width="40%" class="altbg1" valign="top"><b><?=$lang['announce_type']?></b></td>
<td width="45%" class="altbg2">
<input name="typenew" class="radio" type="radio" value="0" <?=$announcecheck[0]?>> <?=$lang['announce_words']?>&nbsp;
<input name="typenew" class="radio" type="radio" value="1" <?=$announcecheck[1]?>> <?=$lang['announce_url']?>&nbsp;
<input name="typenew" class="radio" type="radio" value="2" <?=$announcecheck[2]?>> <?=$lang['announce_pms']?>
</td></tr>

<tr><td width="40%" class="altbg1" valign="top"><b><?=$lang['usergroup']?>:</b><br /><?=$lang['announce_usergroup_comment']?></td>
<td width="45%" class="altbg2">
<select name="usergroupid[]" size="5" multiple="multiple" style="width: 65%">
<option value='' <?=$groupselectall?>><?=$lang['all']?></option>
<?=$groupselect?>
</select>
</td></tr>

<tr><td width="21%" class="altbg1" valign="top"><b><?=$lang['message']?>:</b><br /><?=$lang['announce_message_comment']?></td>
<td width="79%" class="altbg2"><textarea name="messagenew" cols="60" rows="10"><?=dhtmlspecialchars($announce['message'])?></textarea></td></tr>

</table><br /><center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>">
</form>
<?

		} else {

			if(strpos($starttimenew, '-')) {
				$time = explode('-', $starttimenew);
				$starttimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $_DCACHE['settings']['timeoffset'] * 3600;
			} else {
				$starttimenew = 0;
			}
			if(strpos($endtimenew, '-')) {
				$time = explode('-', $endtimenew);
				$endtimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $_DCACHE['settings']['timeoffset'] * 3600;
			} else {
				$endtimenew = 0;
			}

			if(!$starttimenew || ($endtimenew && $endtimenew <= $timestamp)) {
				cpmsg('announce_time_invalid');
			} elseif(!($subjectnew = trim($subjectnew)) || !($messagenew = trim($messagenew))) {
				cpmsg('announce_invalid');
			} else {
				$messagenew = $typenew == 1 ? explode("\n", $messagenew) : array(0 => $messagenew);
				$groups = in_array(0, $usergroupid) ? '' : implode(',', $usergroupid);
				$db->query("UPDATE {$tablepre}announcements SET subject='$subjectnew', type='$typenew', starttime='$starttimenew', endtime='$endtimenew', message='{$messagenew[0]}', groups='$groups' WHERE id='$edit' AND ('$adminid'='1' OR author='$discuz_user')");
				updatecache('announcements');
				updatecache('announcements_forum');
				updatecache('pmlist');
				cpmsg('announce_succeed', 'admincp.php?action=announcements');
			}
		}

	} elseif(submitcheck('announcesubmit')) {

		if(is_array($delete)) {
			$ids = $comma = '';
			foreach($delete as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM {$tablepre}announcements WHERE id IN ($ids) AND ('$adminid'='1' OR author='$discuz_user')");
		}

		if(is_array($displayordernew)) {
			foreach($displayordernew as $id => $displayorder) {
				$db->query("UPDATE {$tablepre}announcements SET displayorder='$displayorder' WHERE id='$id' AND ('$adminid'='1' OR author='$discuz_user')");
			}
		}

		updatecache(array('pmlist', 'announcements', 'announcements_forum'));
		cpmsg('announce_update_succeed', 'admincp.php?action=announcements');

	} elseif(submitcheck('addsubmit')) {

		$newstarttime = $newstarttime ? strtotime($newstarttime) : 0;
		$newendtime = $newendtime ? strtotime($newendtime) : 0;

		if(!$newstarttime) {
			cpmsg('announce_time_invalid');
		} elseif(!($newsubject = trim($newsubject)) || !($newmessage = trim($newmessage))) {
			cpmsg('announce_invalid');
		} else {
			$newmessage = $newtype == 1 ? explode("\n", $newmessage) : array(0 => $newmessage);
			$groups = in_array(0, $usergroupid) ? '' : implode(',', $usergroupid);
			$db->query("INSERT INTO {$tablepre}announcements (author, subject, type, starttime, endtime, message, groups)
				VALUES ('$discuz_user', '$newsubject', '$newtype', '$newstarttime', '$newendtime', '{$newmessage[0]}', '".$groups."')");
			updatecache('announcements');
			updatecache('announcements_forum');
			updatecache('pmlist');
			cpmsg('announce_succeed', 'admincp.php?action=announcements');
		}
	}

}

?>