<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: members.inc.php 17159 2008-12-08 06:33:42Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

@set_time_limit(600);

cpheader();

if($action == 'memberadd') {

	if(!submitcheck('addsubmit')) {

		$groupselect = '';
		$query = $db->query("SELECT groupid, type, grouptitle, creditshigher FROM {$tablepre}usergroups WHERE type='member' AND creditshigher='0' OR (groupid NOT IN ('5', '6', '7') AND radminid<>'1' AND type<>'member') ORDER BY type DESC, (creditshigher<>'0' || creditslower<>'0'), creditslower");
		while($group = $db->fetch_array($query)) {
			if($group['type'] == 'member' && $group['creditshigher'] == 0) {
				$groupselect .= "<option value=\"$group[groupid]\" selected>$group[grouptitle]</option>\n";
			} else {
				$groupselect .= "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			}
		}
		shownav('menu_members_add');

?>
<form method="post" action="admincp.php?action=memberadd">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_add']?></td></tr>

<tr><td class="altbg1" width="50%"><?=$lang['members_add_uid_range']?></td>
<td align="right" class="altbg2"><input type="text" name="uidlowerlimit" size="5"> - <input type="text" name="uidupperlimit" size="5"></td></tr>

<tr><td class="altbg1"><?=$lang['username']?>:</td>
<td align="right" class="altbg2"><input type="text" name="newusername"></td></tr>

<tr><td class="altbg1"><?=$lang['password']?>:</td>
<td align="right" class="altbg2"><input type="text" name="newpassword"></td></tr>

<tr><td class="altbg1"><?=$lang['email']?>:</td>
<td align="right" class="altbg2"><input type="text" name="newemail"></td></tr>

<tr>
<td class="altbg1"><?=$lang['usergroup']?>:</td>
<td align="right" class="altbg2"><select name="newgroupid"><?=$groupselect?></select></td>
</tr>

<tr><td class="altbg1"><?=$lang['members_add_email_notify']?></td>
<td align="right" class="altbg2"><input class="checkbox" type="checkbox" name="emailnotify" value="yes"></td></tr>

</table><br />
<center><input class="button" type="submit" name="addsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$newusername = trim($newusername);
		$newpassword = trim($newpassword);
		$newemail = trim($newemail);

		if(!$newusername || !$newpassword || !$newemail) {
			cpmsg('members_add_invalid');
		}

		if(strlen($newusername) < 3) {
			cpmsg('members_add_tooshort');
		} elseif(strlen($newusername) > 15) {
			cpmsg('members_add_toolong');
		}

		$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censoruser = trim($censoruser)), '/')).')$/i';
		if(preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\s\t\<\>\&]|$guestexp/is", $newusername) || ($censoruser && @preg_match($censorexp, $newusername))) {
			cpmsg('members_add_illegal');
		}

		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$newusername'");
		if($db->num_rows($query)) {
			cpmsg('members_add_username_duplicate');
		}

		$uidadd1 = $uidadd2 = '';
		$uidupperlimit = intval($uidupperlimit);
		$uidlowerlimit = intval($uidlowerlimit);

		if($uidlowerlimit && $uidupperlimit >= $uidlowerlimit) {
			$uid = $uidlowerlimit;
			$query = $db->query("SELECT uid FROM {$tablepre}members WHERE uid BETWEEN '$uidlowerlimit' AND '$uidupperlimit' ORDER BY uid");
			while($member = $db->fetch_array($query)) {
				if($member['uid'] > $uid) {
					break;
				} else {
					$uid ++;
				}
			}
			if($uid <= $uidupperlimit) {
				$uidadd1 = 'uid, ';
				$uidadd2 = $uid.', ';
			} else {
				cpmsg('members_add_uid_invalid');
			}
		}

		$group = $db->fetch_first("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$newgroupid'");
		$newadminid = in_array($group['radminid'], array(1, 2, 3)) ? $group['radminid'] : ($group['type'] == 'special' ? -1 : 0);
		if($group['radminid'] == 1) {
			cpmsg('members_add_admin_none');
		}
		if(in_array($group['groupid'], array(5, 6, 7))) {
			cpmsg('members_add_ban_all_none');
		}

		$db->query("INSERT INTO {$tablepre}members ($uidadd1 username, password, secques, gender, adminid, groupid, regip, regdate, lastvisit, lastactivity, posts, credits, email, bday, sigstatus, tpp, ppp, styleid, dateformat, timeformat, showemail, newsletter, invisible, timeoffset)
			VALUES ($uidadd2 '$newusername', '".md5($newpassword)."', '', '0', '$newadminid', '$newgroupid', 'Manual Acting', '$timestamp', '$timestamp', '$timestamp', '0', '0', '$newemail', '0000-00-00', '0', '0', '0', '0', '0', '{$_DCACHE[settings][timeformat]}', '1', '1', '0', '{$_DCACHE[settings][timeoffset]}')");
		$uid = $db->insert_id();

		$db->query("REPLACE INTO {$tablepre}memberfields (uid) VALUES ('$uid')");

		if($emailnotify == 'yes') {
			sendmail("$newusername <$newemail>", 'add_member_subject', 'add_member_message');
		}

		updatecache('settings');
		$newusername = stripslashes($newusername);
		cpmsg('members_add_succeed');
	}

} elseif($action == 'members') {
	switch($submitname) {
		case 'newslettersubmit': shownav('menu_members_newsletter'); $adminnavtitle = $lang['menu_members_newsletter']; break;
		case 'creditsubmit': shownav('menu_members_credits'); $adminnavtitle = $lang['menu_members_credits']; break;
		case 'deletesubmit': shownav('menu_members_delete'); $adminnavtitle = $lang['menu_members_delete']; break;
		default : shownav('menu_members_edit'); $adminnavtitle = $lang['menu_members_edit']; break;
	}

	if(!submitcheck('searchsubmit', 1) && !submitcheck('deletesubmit', 1) && !submitcheck('sendsubmit', 1) && !submitcheck('editsubmit') && !submitcheck('updatecreditsubmit',1)) {
		$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
		$insenzgid = !empty($insenz['groupid']) ? "'$insenz[groupid]'" : "'0'";
		$groupselect = '';
		$usergroupid = isset($usergroupid) && is_array($usergroupid) ? $usergroupid : array();
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid NOT IN ($insenzgid, '6', '7') ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower");
		while($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupid) ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		$monthselect = $dayselect = '';
		for($m=1; $m<=12; $m++) {
			$m = sprintf("%02d", $m);
			$monthselect .= "<option value=\"$m\" ".($birthmonth == $m ? 'selected' : '').">$m</option>\n";
		}
		for($d=1; $d<=31; $d++) {
			$d = sprintf("%02d", $d);
			$dayselect .= "<option value=\"$d\" ".($birthday == $d ? 'selected' : '').">$d</option>\n";
		}

		$searchcredits = '';
		if(!empty($extcredits)) {
			foreach($extcredits as $id => $credit) {
				$searchcredits .= "<tr><td class=\"altbg1\">$credit[title] $lang[members_search_lower]:</td>\n".
					"<td align=\"right\" class=\"altbg2\"><input type=\"text\" name=\"lower[extcredits$id]\" value=\"".dhtmlspecialchars($lower['extcredits'.$id])."\" size=\"40\"></td></tr>\n".
					"<tr><td class=\"altbg1\">$credit[title] $lang[members_search_higher]:</td>\n".
					"<td align=\"right\" class=\"altbg2\"><input type=\"text\" name=\"higher[extcredits$id]\" value=\"".dhtmlspecialchars($higher['extcredits'.$id])."\" size=\"40\"></td></tr>\n";
			}
		}

?>
<form method="post" action="admincp.php?action=members&submitname=<?=$submitname?>" onSubmit="if($('updatecredittype1') && $('updatecredittype1').checked && !window.confirm('<?=$lang['members_credits_clean_alarm']?>')){return false;} else {return true;}">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$adminnavtitle?> - <?=$lang['members_search']?></td></tr>

<tr><td class="altbg1" width="45%"><?=$lang['members_search_user']?></td>
<td align="right" class="altbg2" width="40%">
<?=$lang['case_insensitive']?> <input type="checkbox" name="cins" value="1" class="checkbox" checked>
<br /><input type="text" name="username" size="40" value="<?=dhtmlspecialchars($username)?>"></td></tr>

<tr><td class="altbg1" valign="top"><?=$lang['members_search_group']?><br /><?=$lang['members_search_comment']?></td>
<td align="right" class="altbg2">
<select name="usergroupid[]" size="5" multiple="multiple" style="width: 65%">
<option value="all"<?php echo in_array('all', $usergroupid) ? ' selected' : '';?>><?=$lang['unlimited']?></option>
<?=$groupselect?>
</select></td></tr>

<tr><td class="altbg1">&nbsp;</td>
<td align="right" class="altbg2" style="text-align: right;"><input class="checkbox" type="checkbox" value="1" onclick="$('advanceoption').style.display = $('advanceoption').style.display == 'none' ? '' : 'none'; this.value = this.value == 1 ? 0 : 1; this.checked = this.value == 1 ? false : true"><?=$lang['more_options']?> &nbsp; </td></tr>

<tbody id="advanceoption" style="display: none">

<tr><td class="altbg1"><?=$lang['members_search_email']?></td>
<td align="right" class="altbg2"><input type="text" name="srchemail" size="40" value="<?=dhtmlspecialchars($srchemail)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['credits']?> <?=$lang['members_search_lower']?>:</td>
<td align="right" class="altbg2"><input type="text" name="lower[credits]" size="40" value="<?=dhtmlspecialchars($lower[credits])?>"></td></tr>

<tr><td class="altbg1"><?=$lang['credits']?> <?=$lang['members_search_higher']?>:</td>
<td align="right" class="altbg2"><input type="text" name="higher[credits]" size="40" value="<?=dhtmlspecialchars($higher[credits])?>"></td></tr>

<?=$searchcredits?>

<tr><td class="altbg1"><?=$lang['members_postslower']?>:</td>
<td align="right" class="altbg2"><input type="text" name="postslower" value="<?=dhtmlspecialchars($postslower)?>" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['members_postshigher']?>:</td>
<td align="right" class="altbg2"><input type="text" name="postshigher" size="40" value="<?=dhtmlspecialchars($postshigher)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_regip']?></td>
<td align="right" class="altbg2"><input type="text" name="regip" size="40" value="<?=dhtmlspecialchars($regip)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_lastip']?></td>
<td align="right" class="altbg2"><input type="text" name="lastip" size="40" value="<?=dhtmlspecialchars($lastip)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_regdatebefore']?></td>
<td align="right" class="altbg2"><input type="text" name="regdatebefore" size="40" value="<?=dhtmlspecialchars($regdatebefore)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_regdateafter']?></td>
<td align="right" class="altbg2"><input type="text" name="regdateafter" size="40" value="<?=dhtmlspecialchars($regdateafter)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_lastvisitbefore']?></td>
<td align="right" class="altbg2"><input type="text" name="lastvisitbefore" size="40" value="<?=dhtmlspecialchars($lastvisitbefore)?>"></td></tr>
<tr><td class="altbg1"><?=$lang['members_search_lastvisitafter']?></td>
<td align="right" class="altbg2"><input type="text" name="lastvisitafter" size="40" value="<?=dhtmlspecialchars($lastvisitafter)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_lastpostbefore']?></td>
<td align="right" class="altbg2"><input type="text" name="lastpostbefore" size="40" value="<?=dhtmlspecialchars($lastpostbefore)?>"></td></tr>
<tr><td class="altbg1"><?=$lang['members_search_lastpostafter']?></td>
<td align="right" class="altbg2"><input type="text" name="lastpostafter" size="40" value="<?=dhtmlspecialchars($lastpostafter)?>"></td></tr>

<tr><td class="altbg1"><?=$lang['members_search_birthday']?></td>
<td align="right" class="altbg2"><input type="text" name="birthyear" size="5" value="<?=dhtmlspecialchars($year)?>"> <?=$lang['year']?> <select name="birthmonth"><option value="">&nbsp;</option><?=$monthselect?></select> <?=$lang['month']?> <select name="birthday"><option value="">&nbsp;</option><?=$dayselect?></select> <?=$lang['day']?></td></tr>
</tbody>


</table><br /><center>
<?

		switch($submitname) {
			case 'newslettersubmit':  echo '<input class="button" type="submit" name="newslettersubmit" value="'.$lang['members_newsletter'].'">'; break;
			case 'creditsubmit': echo '<input class="button" type="submit" name="creditsubmit" value="'.$lang['members_credits'].'">'; break;
			default : echo '<input class="button" type="submit" name="searchsubmit" value="'.$lang['members_search'].'">&nbsp;&nbsp<input class="button" type="submit" name="deletesubmit" value="'.$lang['members_delete'].'">'; break;
		}

		echo '</center>';

	}

	if(submitcheck('searchsubmit', 1) || submitcheck('deletesubmit', 1) || submitcheck('newslettersubmit') || submitcheck('sendsubmit', 1) || submitcheck('creditsubmit') || submitcheck('updatecreditsubmit', 1)) {

		$memberperpage = 100;

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $memberperpage;
		$dateoffset = date('Z') - ($timeoffset * 3600);
		$username = trim($username);

		$conditions = '';
		if($username != '') {
			$usernames = explode(',', $username);
			foreach($usernames as $username) {
				$usernameary[] = ($cins ? '' : 'BINARY')." username LIKE '".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $username)."'";
			}
			$conditions .= " AND (".implode(' OR ', $usernameary).")";
		}

		$conditions .= $srchemail != '' ? " AND email LIKE '".str_replace('*', '%', $srchemail)."'" : '';
		$conditions .= !empty($usergroupid) && !in_array('all', $usergroupid) != '' ? " AND groupid IN ('".implode('\',\'', $usergroupid)."')" : '';

		if(is_array($higher)) {
			foreach($higher as $credit => $value) {
				$credit = intval(substr($credit, 10));
				if($value != '' && $credit > 0 && $credit < 9) {
					$value = intval($value);
					$conditions .= " AND extcredits$credit>'$value'";
				}
			}
		}
		if(is_array($lower)) {
			foreach($lower as $credit => $value) {
				$credit = intval(substr($credit, 10));
				if($value != '' && $credit > 0 && $credit < 9) {
					$value = intval($value);
					$conditions .= " AND extcredits$credit<'$value'";
				}
			}
		}

		$conditions .= $postshigher != '' ? " AND posts>'$postshigher'" : '';
		$conditions .= $postslower != '' ? " AND posts<'$postslower'" : '';

		$conditions .= $higher['credits'] != '' ? " AND credits>'$higher[credits]'" : '';
		$conditions .= $lower['credits'] != '' ? " AND credits<'$lower[credits]'" : '';

		$conditions .= $regip != '' ? " AND regip LIKE '$regip%'" : '';
		$conditions .= $lastip != '' ? " AND lastip LIKE '$lastip%'" : '';

		$conditions .= $regdatebefore != '' ? " AND regdate<'".(strtotime($regdatebefore) + $dateoffset)."'" : '';
		$conditions .= $regdateafter != '' ? " AND regdate>'".(strtotime($regdateafter) + $dateoffset)."'" : '';
		$conditions .= $lastvisitafter != '' ? " AND lastvisit>'".(strtotime($lastvisitafter) + $dateoffset)."'" : '';
		$conditions .= $lastvisitbefore != '' ? " AND lastvisit<'".(strtotime($lastvisitbefore) + $dateoffset)."'" : '';
		$conditions .= $lastpostafter != '' ? " AND lastpost>'".(strtotime($lastpostafter) + $dateoffset)."'" : '';
		$conditions .= $lastpostbefore != '' ? " AND lastpost<'".(strtotime($lastpostbefore) + $dateoffset)."'" : '';

		$conditions .= $birthyear != '' || $birthmonth != '' || $birthday != '' ? " AND bday LIKE '".(($birthyear ? $birthyear : '%').'-'.($birthmonth ? $birthmonth : '%').'-'.($birthday ? $birthday : '%'))."'" : '';

		$conditions .= submitcheck('newslettersubmit') || submitcheck('sendsubmit', 1) ? " AND newsletter='1'" : '';

		if(!$conditions && !$uidarray && submitcheck('deletesubmit', 1)) {
			cpmsg('members_search_invalid');
		} else {
			$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
			$conditions = (!empty($insenz['groupid']) ? "groupid<>$insenz[groupid]" : '1').$conditions;
		}

		$urladd = '';
		if(is_array($usergroupid)) {
			foreach($usergroupid as $gid => $value) {
				if($value != '') {
					$urladd .= '&usergroupid[]='.rawurlencode($value);
				}
			}
		}

		foreach(array('lower', 'higher') as $key) {
			if(is_array($$key)) {
				foreach($$key as $column => $value) {
					$urladd .= '&'.$key.'['.$column.']='.rawurlencode($value);
				}
			}
		}

		$conditionurladd = "$urladd&cins=".rawurlencode($cins)."&username=".rawurlencode($username)."&srchemail=".rawurlencode($srchemail)."&regdatebefore=".rawurlencode($regdatebefore)."&regdateafter=".rawurlencode($regdateafter)."&postshigher=".rawurlencode($postshigher)."&postslower=".rawurlencode($postslower)."&regip=".rawurlencode($regip)."&lastip=".rawurlencode($lastip)."&lastvisitafter=".rawurlencode($lastvisitafter)."&lastvisitbefore=".rawurlencode($lastvisitbefore)."&lastpostafter=".rawurlencode($lastpostafter)."&lastpostbefore=".rawurlencode($lastpostbefore)."&birthyear=".rawurlencode($birthyear)."&birthmonth=".rawurlencode($birthmonth)."&birthday=".rawurlencode($birthday);

		$membernum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE $conditions");

		if(submitcheck('newslettersubmit') || submitcheck('creditsubmit')) {

			if(submitcheck('creditsubmit', 1)) {
				$next = 'updatecreditsubmit';
				$variable = 'creditsnotify';
			} else {
				$next = 'sendsubmit';
				$variable = 'newsletter';
			}


?>
<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="9"><?=$lang['members_search_result']?> <?=$membernum?></td></tr>
<?

			if(!$membernum) {

				echo '<tr><td bgcolor="'.ALTBG2.'" colspan="2">'.$lang['members_search_nonexistence'].'</td></tr></table><br /></form>';

			} else {

				if($next == 'updatecreditsubmit') {
					$creditscols = $creditsvalue = $resetcredits = $js_extcreditids = '';
					for($i=1; $i<=8; $i++) {
						$js_extcreditids .= (isset($extcredits[$i]) ? ($js_extcreditids ? ',' : '').$i : '');
						$creditscols .= '<td width="10%">'.(isset($extcredits[$i]) ? $extcredits[$i]['title'] : 'extcredits'.$i).'</td>';
						$creditsvalue .= '<td class="altbg'.($i % 2 + 1).'">'.(isset($extcredits[$i]) ? '<input type="text" size="3" name="addextcredits['.$i.']" id="addextcredits['.$i.']" value="0"> '.$extcredits['$i']['unit'] : '<input type="text" size="3" value="N/A" disabled>').'</td>';
						$resetcredits .= '<td class="altbg'.($i % 2 + 1).'">'.(isset($extcredits[$i]) ? '<input type="checkbox" name="resetextcredits['.$i.']"  id="resetextcredits['.$i.']" value="1" class="radio" disabled> '.$extcredits['$i']['unit'] : '<input type="checkbox" disabled  class="radio">').'</td>';
					}

?>
<tr class="category" align="center"><td width="20%"><?=$lang['credits_title']?></td><?=$creditscols?></tr>
<tr align="center"><td class="altbg1"><input type="radio" name="updatecredittype" id="updatecredittype0" value="0" class="radio" onclick="var extcredits = new Array(<?=$js_extcreditids?>); for(k in extcredits) {$('resetextcredits['+extcredits[k]+']').disabled = true; $('addextcredits['+extcredits[k]+']').disabled = false;}" checked><?=$lang['members_credits_value']?></td><?=$creditsvalue?></tr>
<tr align="center"><td class="altbg1"><input type="radio" name="updatecredittype" id="updatecredittype1" value="1" class="radio" onclick="var extcredits = new Array(<?=$js_extcreditids?>); for(k in extcredits) {$('addextcredits['+extcredits[k]+']').disabled = true; $('resetextcredits['+extcredits[k]+']').disabled = false;}"><?=$lang['members_credits_clean']?> </td><?=$resetcredits?></tr>
</table>
<br />

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><input class="checkbox" type="checkbox" name="sendcreditsletter" value="1" onclick="$('messagebody').disabled=!this.checked; $('messagebody').style.display = $('messagebody').style.display == '' ? 'none' : ''" class="checkbox"> <?=$lang['members_credits_notify']?></td></tr>
<?

				}

				$subject = $message = '';
				$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='$variable'");
				if($settings = $db->result($query, 0)) {
					$settings = unserialize($settings);
					$subject = $settings['subject'];
					$message = $settings['message'];
				}

?>
<tbody id="messagebody"<?php echo $next == 'updatecreditsubmit' ? ' disabled style="display: none;"' : '';?>>
<tr>
<td class="altbg1"><?=$lang['subject']?>:</td>
<td class="altbg2"><input type="text" name="subject" size="80" value=<?=dhtmlspecialchars($subject)?>></td>
</tr>

<tr>
<td class="altbg1" valign="top"><?=$lang['message']?>:</td><td class="altbg2">
<textarea cols="80" rows="10" name="message"><?=dhtmlspecialchars($message)?></textarea></td></tr>

<tr>
<td class="altbg1"><?=$lang['members_newsletter_send_via']?></td>
<td class="altbg2">
<input class="radio" type="radio" value="email" name="sendvia" onclick="$('pertask').value = 1"> <?=$lang['email']?>
<input class="radio" type="radio" value="pm" checked name="sendvia" onclick="$('pertask').value = 100"> <?=$lang['pm']?>
</td></tr>

<tr>
<td class="altbg1"><?=$lang['members_newsletter_num']?></td>
<td class="altbg2"><input type="text" name="pertask" id="pertask" value="100" size="10"></td>
</tr>
</tbody>
</table><br />

<center><input class="button" type="submit" name="<?=$next?>" value="<?=$lang['submit']?>"></center></form>
<?

			}
		}

		if(submitcheck('sendsubmit', 1) || submitcheck('updatecreditsubmit', 1)) {

			if(submitcheck('updatecreditsubmit', 1)) {
				$submit =  'updatecreditsubmit';
				$variable = 'creditsnotify';
			} else {
				$submit =  'sendsubmit';
				$variable = 'newsletter';
			}

			if(!empty($current)) {

				$subject = $message = '';
				if($settings = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='$variable'")) {
					$settings = unserialize($settings);
					$subject = $settings['subject'];
					$message = $settings['message'];
				}

			} else {

				$current = 0;
				if(($submit == 'sendsubmit' || !empty($sendcreditsletter)) && (!($subject = trim($subject)) || !($message = trim(str_replace("\t", ' ', $message))))) {
					cpmsg('members_newsletter_sm_invalid');
				}

				if($submit == 'updatecreditsubmit') {

					$updatesql = '';
					if($updatecredittype == 0) {
						if(is_array($addextcredits) && !empty($addextcredits)) {
							foreach($addextcredits as $key => $value) {
								$value = intval($value);
								if(isset($extcredits[$key]) && !empty($value)) {
									$updatesql .= ", extcredits{$key}=extcredits{$key}+($value)";
								}
							}
						}
					} else {
						if(is_array($resetextcredits) && !empty($resetextcredits)) {
							foreach($resetextcredits as $key => $value) {
								$value = intval($value);
								if(isset($extcredits[$key]) && !empty($value)) {
									$updatesql .= ", extcredits{$key}=0";
								}
							}
						}
					}

					if(!empty($updatesql)) {
						$db->query("UPDATE {$tablepre}members set uid=uid $updatesql WHERE $conditions", 'UNBUFFTERED');
					} else {
						cpmsg('members_credits_invalid');
					}

					if(!$sendcreditsletter) {
						cpmsg('members_credits_succeed');
					}

				}

				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('$variable', '".
					addslashes(serialize(array('subject' => $subject, 'message' => $message)))."')");
			}

			$pertask = intval($pertask);
			$current = intval($current);

			$uids = $emails = $comma = '';

			if($sendvia == 'pm') {
                                $query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE maxpmnum>0");
                                while($group = $db->fetch_array($query)) {
                                        $groupmaxpm[$group['groupid']] = 1;
                                }
                                $query = $db->query("SELECT uid, username, groupid, email FROM {$tablepre}members WHERE $conditions LIMIT $current, $pertask");
                        } else {
                                $query = $db->query("SELECT uid, username, email FROM {$tablepre}members WHERE $conditions LIMIT $current, $pertask");
                        }

                        while($member = $db->fetch_array($query)) {
                                if($sendvia == 'pm' && isset($groupmaxpm[$member['groupid']])) {
                                        $uids .= $comma.$member['uid'];
                                        $db->query("INSERT INTO {$tablepre}pms (msgfrom, msgfromid, msgtoid, folder, new, subject, dateline, message)
                                                VALUES('$discuz_user', '$discuz_uid', '$member[uid]', 'inbox', '1', '".dhtmlspecialchars($subject)."', '$timestamp', '$message')");
					$comma = ',';
                                } elseif($sendvia == 'email') {
                                        $emails .= $comma."$member[username] <$member[email]>";
					$comma = ',';
                                }
                        }

			if($uids || $emails) {
				if($sendvia == 'pm') {
					$db->query("UPDATE {$tablepre}members SET newpm='1' WHERE uid IN ($uids)");
				} elseif($sendvia == 'email') {
					sendmail($emails, $subject, $message);
				}
				$next = $current + $pertask;
				cpmsg("$lang[members_newsletter_send]: $lang[members_newsletter_processing]", "admincp.php?action=members&{$submit}=yes&current=$next&pertask=$pertask&sendvia=".rawurlencode($sendvia).$conditionurladd);
			} else {
				cpmsg(($submit == 'sendsubmit') ? 'members_newsletter_succeed' : 'members_credits_notify_succeed');
			}

		} elseif(submitcheck('searchsubmit', 1)) {

			$multipage = multi($membernum, $memberperpage, $page, "admincp.php?action=members&searchsubmit=yes$urladd&cins=".rawurlencode($cins)."&username=".rawurlencode($username)."&srchemail=".rawurlencode($srchemail)."&regdatebefore=".rawurlencode($regdatebefore)."&regdateafter=".rawurlencode($regdateafter)."&postshigher=".rawurlencode($postshigher)."&postslower=".rawurlencode($postslower)."&regip=".rawurlencode($regip)."&lastip=".rawurlencode($lastip)."&lastvisitafter=".rawurlencode($lastvisitafter)."&lastvisitbefore=".rawurlencode($lastvisitbefore)."&lastpostafter=".rawurlencode($lastpostafter)."&lastpostbefore=".rawurlencode($lastpostbefore)."&birthyear=".rawurlencode($birthyear)."&birthmonth=".rawurlencode($birthmonth)."&day=".rawurlencode($birthday));

			$usergroups = array();
			$query = $db->query("SELECT groupid, type, grouptitle FROM {$tablepre}usergroups");
			while($group = $db->fetch_array($query)) {
				switch($group['type']) {
					case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
					case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
				}
				$usergroups[$group['groupid']] = $group;
			}

			$altbg1 = 'altbg'.((count($extcredits) + 3) % 2 ? '2' : '1');
			$altbg2 = $altbg1 == 'altbg1' ? 'altbg2' : 'altbg1';

			$query = $db->query("SELECT uid, username, adminid, groupid, credits, extcredits1, extcredits2,
				extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8, posts FROM {$tablepre}members WHERE $conditions LIMIT $start_limit, $memberperpage");

			while($member = $db->fetch_array($query)) {
				$memberextcredits = array();
				foreach($extcredits as $id => $credit) {
					$memberextcredits[] = $extcredits[$id]['title'].': '.$member['extcredits'.$id];
				}
				$members .= "<tr align=\"center\" class=\"smalltxt\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"uidarray[]\" value=\"$member[uid]\"".($member['adminid'] == 1 ? 'disabled' : '')." class=\"checkbox\"></td>\n".
					"<td class=\"altbg2\"><a href=\"space.php?action=viewpro&uid=$member[uid]\" target=\"_blank\">$member[username]</a></td>\n".
					"<td class=\"altbg1\" title=\"".implode("\n", $memberextcredits)."\">$member[credits]</td>\n<td class=\"$altbg1\">$member[posts]</td>\n".
					"<td class=\"$altbg2\">{$usergroups[$member[adminid]][grouptitle]}</td>\n".
					"<td class=\"$altbg1\">{$usergroups[$member[groupid]][grouptitle]}</td>\n".
					"<td class=\"$altbg2\"><a href=\"admincp.php?action=editgroups&uid=$member[uid]\">[$lang[usergroup]]</a> ".
					"<a href=\"admincp.php?action=access&uid=$member[uid]\">[$lang[access]]</a> ".
					($extcredits ? "<a href=\"admincp.php?action=editcredits&uid=$member[uid]\">[$lang[credits]]</a> " : "<span disabled>[$lang[edit]]</span> ").
					"<a href=\"admincp.php?action=editmedals&uid=$member[uid]\">[$lang[medals]]</a> ".
					"<a href=\"admincp.php?action=memberprofile&uid=$member[uid]\">[$lang[detail]]</a></td></tr>\n";
			}

			showtips('members_tips');

?>
<?=$multipage?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" style="table-layout: fixed">
<form method="post" action="admincp.php?action=members">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr align="center" class="header">
<td width="48"><input type="checkbox" name="chkall" onclick="checkall(this.form, 'uidarray')" class="checkbox"><?=$lang['del']?></td>
<td width="100"><?=$lang['username']?></td><td width="60"><?=$lang['credits']?></td><td width="60"><?=$lang['posts']?></td><td width="80"><?=$lang['admingroup']?><td width="80"><?=$lang['usergroup']?></td><td width="250"><?=$lang['edit']?></td></tr>
<?=$members?>
</table>
<?=$multipage?>
<br /><center><input class="button" type="submit" name="deletesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

		} elseif(submitcheck('deletesubmit', 1)) {

			$uids = 0;
			$extra = '';

			if(empty($uidarray)) {
				$query = $db->query("SELECT uid, groupid, adminid FROM {$tablepre}members WHERE $conditions AND adminid<>1 AND groupid<>1");
			} else {
				$uids = is_array($uidarray) ? '\''.implode('\', \'', $uidarray).'\'' : '0';
				$query = $db->query("SELECT uid, groupid, adminid FROM {$tablepre}members WHERE uid IN($uids) AND adminid<>1 AND groupid<>1");
			}

			$membernum = $db->num_rows($query);

			$uids = $comma = '';
			while($member = $db->fetch_array($query)) {
				if($membernum < 2000 || !empty($uidarray)) {
					$extra .= '<input type="hidden" name="uidarray[]" value="'.$member['uid'].'">';
				}
				$uids .= $comma.$member['uid'];
				$comma = ',';
			}

			$extraincludepost .= '<br /><br /><input type="checkbox" name="includepost" value="1" class="checkbox">'.$lang['members_delete_post'];

			if((empty($membernum) || empty($uids))) {
				cpmsg('members_no_find_deluser');
			}

			if(!$confirmed) {

				cpmsg('members_delete_confirm', "admincp.php?action=members&deletesubmit=yes&confirmed=yes".$conditionurladd, 'form', $extra.$extraincludepost);

			} else {

				if(empty($includepost)) {

					$query = $db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
					$numdeleted = $db->affected_rows();
					$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}buddys WHERE uid IN ($uids) OR buddyid IN ($uids)",'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}pms WHERE msgfromid IN ($uids) OR msgtoid IN ($uids)", 'UNBUFFERED');
					$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');
					cpmsg('members_delete_succeed');

				} else {

					$numdeleted = $numdeleted ? $numdeleted : count($uidarray);
					$pertask = 1000;
					$current = intval($current);

					$next = $current + $pertask;
					$threads = $fids = $threadsarray = array();

					$query = $db->query("SELECT f.fid, t.tid FROM {$tablepre}threads t LEFT JOIN {$tablepre}forums f ON t.fid=f.fid WHERE t.authorid IN ($uids) ORDER BY f.fid LIMIT $pertask");
					while($thread = $db->fetch_array($query)) {
						$threads[$thread['fid']] .= ($threads[$thread['fid']] ? ',' : '').$thread['tid'];
					}

					$nextlink = "admincp.php?action=members&confirmed=yes&deletesubmit=yes&includepost=yes&current=$next&pertask=$pertask&lastprocess=$processed".$conditionurladd;
					if($threads) {
						foreach($threads as $fid=>$tids) {
							$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($tids)");
							while($attach = $db->fetch_array($query)) {
								dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
							}

							foreach(array('threads', 'threadsmod', 'relatedthreads', 'posts', 'polls', 'polloptions', 'trades', 'activities', 'activityapplies', 'debates', 'debateposts', 'attachments', 'favorites', 'mythreads', 'myposts', 'subscriptions', 'typeoptionvars', 'forumrecommend') as $value) {
								$db->query("DELETE FROM {$tablepre}$value WHERE tid IN ($tids)", 'UNBUFFERED');
							}

							require_once DISCUZ_ROOT.'./include/post.func.php';
							updateforumcount($fid);
						}
						if($globalstick && $stickmodify) {
							require_once DISCUZ_ROOT.'./include/cache.func.php';
							updatecache('globalstick');
						}


?>
<br /><br /><br /><br /><br /><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['discuz_message']?></td></tr><tr><td class="altbg2" align="center" style="text-align: center;">
<br /><br />
<?=$lang['members_delete_post']?>: <?php eval('echo "'.$lang['members_delete_processing'].'";');?>
<form action="<?=$nextlink?>" id="deleteform" method="post">
<?=$extra?>
<br /><br /><a href="###" onclick="$('deleteform').submit();"><?=$lang['message_redirect']?></a>
</form>

<script>
setTimeout("$('deleteform').submit();", 3000);
</script>
<br /><br />
</td></tr></table>
<br /><br /><br />
<?

						cpfooter();
						dexit();

					} elseif($uids) {

						$query = $db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
						$numdeleted = $db->affected_rows();
						$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
						$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
						$db->query("DELETE FROM {$tablepre}buddys WHERE uid IN ($uids) OR buddyid IN ($uids)", 'UNBUFFERED');
						$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
						$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
						$db->query("DELETE FROM {$tablepre}pms WHERE msgfromid IN ($uids) OR msgtoid IN ($uids)", 'UNBUFFERED');
						$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');

						$query = $db->query("SELECT uid, attachment, thumb, remote FROM {$tablepre}attachments WHERE uid IN ($uids) LIMIT $pertask");
						while($attach = $db->fetch_array($query)) {
							dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
						}

						$db->query("DELETE FROM {$tablepre}attachments WHERE uid IN ($uids)");

						$db->query("DELETE FROM {$tablepre}posts WHERE authorid IN ($uids)");
						$db->query("DELETE FROM {$tablepre}trades WHERE sellerid IN ($uids)");

						cpmsg('members_delete_succeed');

					} else {

						cpmsg('members_no_find_deluser');

					}
				}
			}
		}

	} else {
		echo '</form>';
	}

} elseif($action == 'membersmerge') {

	if(!submitcheck('mergesubmit')) {
		shownav('menu_members_merge');

?>
<form method="post" action="admincp.php?action=membersmerge">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['members_merge']?></td></tr>
<tr align="center"><td class="altbg1" width="40%"><?=$lang['members_merge_source']?> 1:</td>
<td class="altbg2" width="45%"><input type="text" name="source[]" size="20"></td></tr>
<tr align="center"><td class="altbg1" width="40%"><?=$lang['members_merge_source']?> 2:</td>
<td class="altbg2" width="45%"><input type="text" name="source[]" size="20"></td></tr>
<tr align="center"><td class="altbg1" width="40%"><?=$lang['members_merge_source']?> 3:</td>
<td class="altbg2" width="45%"><input type="text" name="source[]" size="20"></td></tr>
<tr align="center"><td class="altbg1" width="40%"><?=$lang['members_merge_target']?>:</td>
<td class="altbg2" width="45%"><input type="text" name="target" size="20"></td></tr>
</table><br /><center><input class="button" type="submit" name="mergesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		$suids = $susernames = $comma = $tuid = $tusername = $sourcemember = $targetmember = '';

		if(is_array($source)) {
			$query = $db->query("SELECT uid, username, adminid, groupid FROM {$tablepre}members WHERE username IN ('".implode('\',\'', $source)."') AND username<>''");
			while($member = $db->fetch_array($query)) {
				if($member['adminid'] == 1 || $member['groupid'] == 1) {
					cpmsg('members_dont_contain_admin_merge');
				}
				$suids .= $comma.$member['uid'];
				$susernames .= $comma.'\''.addslashes($member['username']).'\'';
				$sourcemember .= $comma.$member['username'];
				$comma = ', ';
			}
		}

		$member = $db->fetch_first("SELECT uid, username FROM {$tablepre}members WHERE username='$target'");
		if(!$member || !$suids) {
			cpmsg('members_merge_invalid');
		}

		if(in_array($target, $source)) {
			cpmsg('members_sameness');
		}

		$tuid = $member['uid'];
		$tusername = addslashes($member['username']);
		$targetmember = $member['username'];

		if(!$confirmed) {

			$extra = '<input type="hidden" name="target" value="'.dhtmlspecialchars($target).'">';
			foreach($source as $username) {
				$extra .= '<input type="hidden" name="source[]" value="'.dhtmlspecialchars($username).'">';
			}

			cpmsg('members_merge_confirm', "admincp.php?action=membersmerge&mergesubmit=yes", 'form', $extra);

		} else {

			$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}adminnotes SET admin='$tusername' WHERE admin IN ($susernames)");
			$db->query("UPDATE {$tablepre}adminsessions SET uid='$tuid' WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}announcements SET author='$tusername' WHERE author IN ($susernames)");
			$db->query("UPDATE {$tablepre}banned SET admin='$tusername' WHERE admin IN ($susernames)");
			$db->query("DELETE FROM {$tablepre}buddys WHERE uid IN ($suids) OR buddyid IN ($suids)");
			$db->query("UPDATE {$tablepre}favorites SET uid='$tuid' WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}myposts SET uid='$tuid' WHERE uid IN ($suids)", 'SILENT');
			$db->query("DELETE FROM {$tablepre}myposts WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}mythreads SET uid='$tuid' WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}pms SET msgfromid='$tuid', msgfrom='$tusername' WHERE msgfromid IN ($suids)");
			$db->query("UPDATE {$tablepre}pms SET msgtoid='$tuid' WHERE msgtoid IN ($suids)");
			$db->query("UPDATE {$tablepre}posts SET author='$tusername', authorid='$tuid' WHERE authorid IN ($suids)");
			$db->query("UPDATE {$tablepre}ratelog SET uid='$tuid', username='$tusername' WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}threads SET author='$tusername', authorid='$tuid' WHERE authorid IN ($suids)");
			$db->query("UPDATE {$tablepre}threads SET lastposter='$tusername' WHERE lastposter IN ($susernames)");
			$db->query("UPDATE {$tablepre}threadsmod SET uid='$tuid', username='$tusername' WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}validating SET admin='$tusername' WHERE admin IN ($susernames)");
			$db->query("DELETE FROM {$tablepre}onlinetime WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}spacecaches WHERE uid IN ($suids)");

			$member = $db->fetch_first("SELECT SUM(credits) AS credits, SUM(extcredits1) AS extcredits1,
				SUM(extcredits2) AS extcredits2, SUM(extcredits3) AS extcredits3,
				SUM(extcredits4) AS extcredits4, SUM(extcredits5) AS extcredits5,
				SUM(extcredits6) AS extcredits6, SUM(extcredits7) AS extcredits7,
				SUM(extcredits8) AS extcredits8, SUM(posts) AS posts,
				SUM(digestposts) AS digestposts, SUM(pageviews) AS pageviews,
				SUM(oltime) AS oltime
				FROM {$tablepre}members WHERE uid IN ($suids)");

			$db->query("UPDATE {$tablepre}members SET credits=credits+$member[credits],
				extcredits1=extcredits1+$member[extcredits1], extcredits2=extcredits2+$member[extcredits2],
				extcredits3=extcredits3+$member[extcredits3], extcredits4=extcredits4+$member[extcredits4],
				extcredits5=extcredits5+$member[extcredits5], extcredits6=extcredits6+$member[extcredits6],
				extcredits7=extcredits7+$member[extcredits7], extcredits8=extcredits8+$member[extcredits8],
				posts=posts+$member[posts], digestposts=digestposts+$member[digestposts],
				pageviews=pageviews+$member[pageviews], oltime=oltime+$member[oltime]
				WHERE uid='$tuid'");
			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($suids)");

			updatecache('settings');

			cpmsg('members_merge_succeed');

		}

	}

} elseif($action == 'editgroups') {

	$member = $db->fetch_first("SELECT m.uid, m.username, m.adminid, m.groupid, m.groupexpiry, m.extgroupids, m.credits,
		mf.groupterms, u.type AS grouptype, u.grouptitle
		FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE m.uid='$uid'");

	if(!$member) {
		cpmsg('members_edit_nonexistence');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow');
	}

	if(!submitcheck('editsubmit')) {

		$checkadminid = array(($member['adminid'] >= 0 ? $member['adminid'] : 0) => 'checked');

		$member['groupterms'] = unserialize($member['groupterms']);

		if($member['groupterms']['main']) {
			$expirydate = gmdate('Y-n-j', $member['groupterms']['main']['time'] + $timeoffset * 3600);
			$expirydays = ceil(($member['groupterms']['main']['time'] - $timestamp) / 86400);
			$selecteaid = array($member['groupterms']['main']['adminid'] => 'selected');
			$selectegid = array($member['groupterms']['main']['groupid'] => 'selected');
		} else {
			$expirydate = $expirydays = '';
			$selecteaid = array($member['adminid'] => 'selected');
			$selectegid = array(($member['grouptype'] == 'member' ? 0 : $member['groupid']) => 'selected');
		}

		$class = 'altbg1';
		$extgroupcount = 0;
		$extgroups = $expgroups = $curtype = $thisbg = '';
		$extgrouparray = explode("\t", $member['extgroupids']);
		$groups = array('system' => '', 'special' => '', 'member' => '');
		$group = array('groupid' => 0, 'radminid' => 0, 'type' => '', 'grouptitle' => $lang['usergroups_system_0'], 'creditshigher' => 0, 'creditslower' => '0');
		$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
		$insenzgid = !empty($insenz['groupid']) ? "'$insenz[groupid]'" : "'0'";
		$query = $db->query("SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower
			FROM {$tablepre}usergroups WHERE groupid NOT IN ($insenzgid, '6', '7') ORDER BY creditshigher, groupid");
		do {
			if($group['groupid'] && !in_array($group['groupid'], array(4, 5, 6, 7, 8)) && ($group['type'] == 'system' || $group['type'] == 'special')) {
				$extgroups .= ($extgroupcount++ % 2 == 0 ? '</tr><tr>' : '').
					'<td class="altbg2"><input class="checkbox" type="checkbox" name="extgroupidsnew[]" value="'.$group['groupid'].'" '.(in_array($group['groupid'], $extgrouparray) ? 'checked' : '').'> '.$group['grouptitle'].'</td><td align="center" class="altbg2"><input type="text" size="9" name="extgroupexpirynew['.$group['groupid'].']" value="'.(in_array($group['groupid'], $extgrouparray) && !empty($member['groupterms']['ext'][$group['groupid']]) ? gmdate('Y-n-j', $member['groupterms']['ext'][$group['groupid']] + $timeoffset * 3600) : '').'" onclick="showcalendar(event, this)"></td>';
			}
			if($group['groupid'] && $group['type'] == 'member' && !($member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) && $member['groupid'] != $group['groupid']) {
				continue;
			}

			$expgroups .= '<option name="expgroupidnew" value="'.$group['groupid'].'" '.$selectegid[$group['groupid']].'>'.$group['grouptitle'].'</option>';

			if($group['groupid'] != 0) {
				$thisbg = $curtype == $group['type'] && $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';
				$curtype = $group['type'];
				$groupradmin = $group['type'] == 'special' && !$group['radminid'] ? '<select name="adminidnew['.$group['groupid'].']"><option value="0"'.($member['adminid'] == 0 ? ' selected' : '').'>'.$lang['usergroups_system_0'].'</option><option value="3"'.($member['adminid'] == 3 ? ' selected' : '').'>'.$lang['usergroups_system_3'].'</option><option value="2"'.($member['adminid'] == 2 ? ' selected' : '').'>'.$lang['usergroups_system_2'].'</option><option value="1"'.($member['adminid'] == 1 ? ' selected' : '').'>'.$lang['usergroups_system_1'].'</option></select>' : ($group['radminid'] ? $lang['usergroups_system_'.$group['radminid']] : '<span class="warning">X</span>');
				$groups[$group['type']] .= '<tr class="'.$thisbg.'"><td><input class="radio" type="radio" name="groupidnew" value="'.$group['groupid'].'" '.($member['groupid'] == $group['groupid'] ? 'checked' : '').'> '.$group['grouptitle'].'</td><td>'.$groupradmin.'</td></tr>';
			}
		} while($group = $db->fetch_array($query));

		$extgroups .= $extgroupcount++ % 2 != 0 ? '<td colspan="2" class="altbg2">&nbsp;</tr><tr>' : '';

		if(!$groups['member']) {
			$group = $db->fetch_first("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type='member' AND creditshigher>='0' ORDER BY creditshigher LIMIT 1");
			$groups['member'] = '<tr><td class="altbg1"><input class="radio" type="radio" name="groupidnew" value="'.$group['groupid'].'"> '.$group['grouptitle'].'</td></tr>';
		}

		shownav('members_edit_groups');

?>
<script src="include/javascript/calendar.js" type="text/javascript"></script>
<form method="post" action="admincp.php?action=editgroups&uid=<?=$member['uid']?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit_groups']?> - <?=$member['username']?></td></tr>
<tr class="altbg1"><td><table cellspacing="0" cellpadding="10" width="100%" align="center">
<tr><td width="35%" valign="top">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['usergroup']?></td><td><?=$lang['members_edit_groups_related_adminid']?></td></tr>
<tr><td class="category" colspan="2"><span class="bold"><?=$lang['usergroups_system']?></span></td></tr>
<?=$groups['system']?>
<tr><td class="category" colspan="2"><span class="bold"><?=$lang['usergroups_special']?></span></td></tr>
<?=$groups['special']?>
<tr><td class="category" colspan="2"><span class="bold"><?=$lang['usergroups_member']?></span></td></tr>
<?=$groups['member']?>
</table>

</td>
<td width="65%" align="right" valign="top">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="4"><?=$lang['members_edit_groups_extended']?></td></tr>
<tr align="center" class="category"><td width="30%"><?=$lang['usergroup']?></td><td width="20%"><?=$lang['validity']?></td><td width="30%"><?=$lang['usergroup']?></td><td width="20%"><?=$lang['validity']?></td>
<?=$extgroups?>
<tr><td colspan="4" class="altbg2"><?=$lang['members_edit_groups_extended_comment']?></td></tr>
</table><br />

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['validity']?></td></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['members_edit_groups_validity']?></b><br /><span class="smalltxt"><?=$lang['members_edit_groups_validity_comment']?></span></td>
<td class="altbg2" width="40%">
<input class="radio" type="radio" name="expirytype" value="date" checked> <input type="text" name="expirydatenew" value="<?=$expirydate?>" size="15"> <?=$lang['members_edit_groups_validity_date']?><br />
<input class="radio" type="radio" name="expirytype" value="days"> <input type="text" name="expirydaysnew" value="<?=$expirydays?>" size="15"> <?=$lang['members_edit_groups_validity_days']?><br /></td></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['members_edit_groups_orig_groupid']?></b></td><td class="altbg2" width="40%">
<select name="expadminidnew">
<option value="0" <?=$selecteaid[0]?>><?=$lang['usergroups_system_0']?></option>
<option value="1" <?=$selecteaid[1]?>><?=$lang['usergroups_system_1']?></option>
<option value="2" <?=$selecteaid[2]?>><?=$lang['usergroups_system_2']?></option>
<option value="3" <?=$selecteaid[3]?>><?=$lang['usergroups_system_3']?></option>
</select></td></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['members_edit_groups_orig_adminid']?></b></td><td class="altbg2" width="40%">
<select name="expgroupidnew"><?=$expgroups?></select></td></tr>
</table><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit_reason']?></td></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['members_edit_groups_ban_reason']?></b><br /><span class="smalltxt"><?=$lang['members_edit_groups_ban_reason_comment']?></span></td>
<td class="altbg2" width="40%"><textarea name="reason" rows="5" cols="30"></textarea></td></tr>
</table><br /><br /><center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</td></tr></table>
</td></tr>
</table></form>
<?

	} else {

		$group = $db->fetch_first("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$groupidnew'");
		if(!$group) {
			cpmsg('undefined_action');
		}

		if(strlen(is_array($extgroupidsnew) ? implode("\t", $extgroupidsnew) : '') > 60) {
			cpmsg('members_edit_groups_toomany');
		}

		$adminidnew = $adminidnew[$groupidnew];
		switch($group['type']) {
			case 'member':
				$groupidnew = in_array($adminidnew, array(1, 2, 3)) ? $adminidnew : $groupidnew;
				break;
			case 'special':
				if($group['radminid']) {
					$adminidnew = $group['radminid'];
				} elseif(!in_array($adminidnew, array(1, 2, 3))) {
					$adminidnew = -1;
				}
				break;
			case 'system':
				$adminidnew = in_array($groupidnew, array(1, 2, 3)) ? $groupidnew : -1;
				break;
		}

		$groupterms = array();
		if($expirytype == 'date' && $expirydatenew) {
			$maingroupexpirynew = strtotime($expirydatenew) - date('Z') + $timeoffset * 3600;
		} elseif($expirytype == 'days' && $expirydaysnew) {
			$maingroupexpirynew = $timestamp + $expirydaysnew * 86400;
		} else {
			$maingroupexpirynew = 0;
		}

		if($maingroupexpirynew) {

			$group = $db->fetch_first("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$expgroupidnew'");
			if(!$group) {
				$expgroupidnew = in_array($expadminidnew, array(1, 2, 3)) ? $expadminidnew : $expgroupidnew;
			} else {
				switch($group['type']) {
					case 'special':
						if($group['radminid']) {
							$expadminidnew = $group['radminid'];
						} elseif(!in_array($expadminidnew, array(1, 2, 3))) {
							$expadminidnew = -1;
						}
						break;
					case 'system':
						$expadminidnew = in_array($expgroupidnew, array(1, 2, 3)) ? $expgroupidnew : -1;
						break;
				}
			}

			if($expgroupidnew == $groupidnew) {
				cpmsg('members_edit_groups_illegal');
			} elseif($maingroupexpirynew > $timestamp) {
				if($expgroupidnew || $expadminidnew) {
					$groupterms['main'] = array('time' => $maingroupexpirynew, 'adminid' => $expadminidnew, 'groupid' => $expgroupidnew);
				} else {
					$groupterms['main'] = array('time' => $maingroupexpirynew);
				}
				$groupterms['ext'][$groupidnew] = $maingroupexpirynew;
			}

		}

		if(is_array($extgroupexpirynew)) {
			foreach($extgroupexpirynew as $extgroupid => $expiry) {
				if(is_array($extgroupidsnew) && in_array($extgroupid, $extgroupidsnew) && !isset($groupterms['ext'][$extgroupid]) && $expiry && ($expiry = strtotime($expiry) - date('Z') + $timeoffset * 3600) > $timestamp) {
					$groupterms['ext'][$extgroupid] = $expiry;
				}
			}
		}

		$grouptermsnew = addslashes(serialize($groupterms));
		$groupexpirynew = groupexpiry($groupterms);
		$extgroupidsnew = $extgroupidsnew && is_array($extgroupidsnew) ? implode("\t", $extgroupidsnew) : '';

		$db->query("UPDATE {$tablepre}members SET groupid='$groupidnew', adminid='$adminidnew', extgroupids='$extgroupidsnew', groupexpiry='$groupexpirynew' WHERE uid='$member[uid]'");
		$db->query("UPDATE {$tablepre}memberfields SET groupterms='$grouptermsnew' WHERE uid='$member[uid]'");

		if($groupidnew != $member['groupid'] && (in_array($groupidnew, array(4, 5)) || in_array($member['groupid'], array(4, 5)))) {
			banlog($member['username'], $member['groupid'], $groupidnew, $groupexpirynew, $reason);
		}

		cpmsg('members_edit_groups_succeed', "admincp.php?action=editgroups&uid=$member[uid]");

	}

} elseif($action == 'editcredits' && $uid && $extcredits) {

	$member = $db->fetch_first("SELECT m.*, u.grouptitle, u.type, u.creditslower, u.creditshigher
		FROM {$tablepre}members m
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE uid='$uid'");
	if(!$member) {
		cpmsg('members_edit_nonexistence');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow');
	}

	if(!submitcheck('creditsubmit')) {

		eval("\$membercredit = @round($creditsformula);");

		if($jscreditsformula = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='creditsformula'")) {
			$jscreditsformula = str_replace(array('digestposts', 'posts', 'pageviews', 'oltime'), array($member['digestposts'], $member['posts'],$member['pageviews'],$member['oltime']), $jscreditsformula);
		}

		$creditscols = $creditsvalue = '';
		for($i = 1; $i <= 8; $i++) {
			$jscreditsformula = str_replace('extcredits'.$i, "extcredits[$i]", $jscreditsformula);
			$creditscols .= '<td width="9%">'.(isset($extcredits[$i]) ? $extcredits[$i]['title'] : 'extcredits'.$i).'</td>';
			$creditsvalue .= '<td class="altbg'.(($i + 1) % 2 + 1).'">'.(isset($extcredits[$i]) ? '<input type="text" size="3" name="extcreditsnew['.$i.']" id="extcreditsnew['.$i.']" value="'.$member['extcredits'.$i].'" onkeyup="membercredits()"> '.$extcredits['$i']['unit'] : '<input type="text" size="3" value="N/A" disabled>').'</td>';
		}

		$creditsrangs = $member['type'] == 'member' ? "$member[creditshigher]~$member[creditslower]" : 'N/A';

		shownav('members_edit_credits');
		showtips('credits_tips');

?>
<form name="input" method="post" action="admincp.php?action=editcredits&uid=<?=$uid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="10"><?=$lang['members_edit_credits']?> - <?=$member['username']?>(<?=$member['grouptitle']?>)</td></tr>
<tr class="category" align="center"><td width="14%"><?=$lang['members_edit_credits_ranges']?></td><td width="14%"><?=$lang['credits']?></td><?=$creditscols?></tr>
<tr align="center"><td class="altbg1"><?=$creditsrangs?></td><td class="altbg2"><input type="text" name="jscredits" id="jscredits" value="<?=$membercredit?>" size="3" disabled></td><?=$creditsvalue?></tr>
</table><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit_reason']?></td></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['members_edit_credits_reason']?></b><br /><span class="smalltxt"><?=$lang['members_edit_credits_reason_comment']?></span></td>
<td class="altbg2" width="40%"><textarea name="reason" rows="5" cols="30" style="width: 90%"></textarea></td></tr>
</table><br /><center>
<script language="JavaScript">
var extcredits = new Array();
function membercredits() {
	var credits = 0;
	for(var i = 1; i <= 8; i++) {
		e = $('extcreditsnew['+i+']');
		if(e && parseInt(e.value)) {
			extcredits[i] = parseInt(e.value);
		} else {
			extcredits[i] = 0;
		}
	}
	$('jscredits').value = Math.round(<?=$jscreditsformula?>);
}
</script>
<input class="button" type="submit" name="creditsubmit" value="<?=$lang['submit']?>">
</center></form>
<?

	} else {

		$diffarray = array();
		$sql = $comma = '';
		if(is_array($extcreditsnew)) {
			foreach($extcreditsnew as $id => $value) {
				if($member['extcredits'.$id] != ($value = intval($value))) {
					$diffarray[$id] = $value - $member['extcredits'.$id];
					$sql .= $comma."extcredits$id='$value'";
					$comma = ', ';
				}
			}
		}

		if($diffarray) {
			if(empty($reason)) {
				cpmsg('members_edit_reason_invalid');
			}

			foreach($diffarray as $id => $diff) {
				$logs[] = dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$member[username]\t$id\t$diff\t0\t\t$reason");
			}
			$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$uid'");
			writelog('ratelog', $logs);
		}

		cpmsg('members_edit_credits_succeed', "admincp.php?action=editcredits&uid=$uid");

	}

} elseif($action == 'editmedals' && $uid) {

	$member = $db->fetch_first("SELECT m.uid, m.username, mf.medals
		FROM {$tablepre}memberfields mf, {$tablepre}members m
		WHERE mf.uid='$uid' AND m.uid=mf.uid");

	if(!$member) {
		cpmsg('members_edit_nonexistence');
	}

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$membermedals = explode("\t", $member['medals']);
		$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available='1'");
		while($medal = $db->fetch_array($query)) {
			$medals .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><img src=\"images/common/$medal[image]\"></td>\n".
				"<td class=\"altbg2\">$medal[name]</td>\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" ".(in_array($medal['medalid'], $membermedals) ? 'checked' : '')."></td></td>\n";
		}

		if(!$medals) {
			cpmsg('members_edit_medals_nonexistence');
		}

		shownav('medals_edit');

?>
<form method="post" action="admincp.php?action=editmedals&uid=<?=$uid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['medals_edit']?> - <?=$member['username']?></td></tr>
<tr class="category" align="center"><td><?=$lang['medals_image']?></td><td><?=$lang['name']?></td><td><?=$lang['medals_grant']?></td></tr>
<?=$medals?>
</table><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit_reason']?></td></tr>
<tr><td class="altbg1" width="45%"><b><?=$lang['members_edit_medals_reason']?></b><br /><span class="smalltxt"><?=$lang['members_edit_medals_reason_comment']?></span></td>
<td class="altbg2" width="40%"><textarea name="reason" rows="5" cols="30"></textarea></td></tr>
</table><br /><center>
<input class="button" type="submit" name="medalsubmit" value="<?=$lang['submit']?>">
</center></form>
<?

	} else {

		$medalsarray = array();
		if(is_array($medals)) {
			foreach($medals as $medalid => $newgranted) {
				if($newgranted) {
					$medalsarray[] = intval($medalid);
				}
			}
		}

		$medalsnew = implode("\t", $medalsarray);
		$reason = preg_replace("/(\r\n|\r|\n)/", '<br />', dhtmlspecialchars(trim($reason)));

		if($member['medals'] != $medalsnew) {
			if(empty($reason)) {
				cpmsg('members_edit_reason_invalid');
			} else {
				$db->query("UPDATE {$tablepre}memberfields SET medals='$medalsnew' WHERE uid='$uid'");
			}
		}

		$origmedalsarray = explode("\t", $member['medals']);
		$logs = array();
		foreach(array_unique(array_merge($origmedalsarray, $medalsarray)) as $medalid) {
			if($medalid) {
				$orig = in_array($medalid, $origmedalsarray);
				$new = in_array($medalid, $medalsarray);
				if($orig != $new) {
					if($orig && !$new) {
						$medalaction = 'revoke';
					} elseif(!$orig && $new) {
						$medalaction = 'grant';
					}
					$logs[] = dhtmlspecialchars("$timestamp\t$discuz_userss\t$onlineip\t$member[username]\t$medalid\t$medalaction\t$reason");
				}
			}
		}

		writelog('medalslog', $logs);

		cpmsg('members_edit_medals_succeed', "admincp.php?action=editmedals&uid=$uid");

	}

} elseif($action == 'banmember') {

	if(!$allowbanuser) {
		cpmsg('action_noaccess');
	}

	$member = $currentstatus = array();
	if(!empty($username) || !empty($uid)) {
		$member = $db->fetch_first("SELECT m.*, mf.*, u.type AS grouptype, u.allowsigbbcode, u.allowsigimgcode FROM {$tablepre}members m
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
			WHERE ".($uid ? "m.uid='$uid'" : "m.username='$username'"));

		if(!$member) {
			cpmsg('members_edit_nonexistence');
		} elseif(($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['grouptype'] == 'special') {
			cpmsg('members_edit_illegal');
		}

		$member['groupterms'] = unserialize($member['groupterms']);
		$member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? gmdate('Y-n-j', $member['groupterms']['main']['time'] + $timeoffset * 3600) : '';
		if($member['groupid'] == 4) {
			$check['post'] = 'checked';
			$currentstatus['post'] = ' ('.$lang['members_edit_current_status'].')';
		} elseif($member['groupid'] == 5) {
			$check['visit'] = 'checked';
			$currentstatus['visit']  = ' ('.$lang['members_edit_current_status'].')';
		} else {
			$check['none'] = 'checked';
			$currentstatus['none']  = ' ('.$lang['members_edit_current_status'].')';
		}
	}

	if(!submitcheck('bansubmit')) {

		echo '<script src="include/javascript/calendar.js" type="text/javascript"></script>';
		shownav('members_edit_ban_user');
		echo '<form method="post" action="admincp.php?action=banmember&uid=&formhash='.FORMHASH.'">';
		showtype($lang['members_edit_ban_user'].($member['username'] ? ' -'.$member['username'] : ''), 'top');
		showsetting('members_edit_ban_username', 'username', $member['username'], 'text');
		showsetting('members_edit_ban', '', '', '<input type="radio" name="bannew" value="" '.$check['none'].' class="radio"> '.$lang['members_edit_ban_none'].$currentstatus['none'].'<br /><input type="radio" name="bannew" value="post" '.$check['post'].'  class="radio"> '.$lang['members_edit_ban_post'].$currentstatus['post'].'<br /><input type="radio" name="bannew" value="visit" '.$check['visit'].'  class="radio"> '.$lang['members_edit_ban_visit'].$currentstatus['visit']);
		showsetting('members_edit_ban_validity', '', '', selectday('banexpirynew', array(0, 1, 3, 5, 7, 14, 30, 60, 90, 180, 365)));
		if($adminid == 1) {
			showsetting('members_edit_ban_delpost', 'delpost', '', 'radio');
		}
		showsetting('members_edit_ban_reason', 'reason', '', 'textarea');
		showtype('', 'bottom');
		echo '<br /><center><input class="button" type="submit" name="bansubmit" value="'.$lang['submit'].'"></center></form>';

	} else {
		
		if(empty($member)) {
			cpmsg('members_edit_nonexistence');
		}

		$sql = 'uid=uid';
		$reason = trim($reason);
		if(!$reason && ($reasonpm == 1 || $reasonpm == 3)) {
			cpmsg('members_edit_reason_invalid');
		}

		if($bannew == 'post' || $bannew == 'visit') {
			$groupidnew = $bannew == 'post' ? 4 : 5;
			$banexpirynew = !empty($banexpirynew) ? $timestamp + $banexpirynew * 86400 : 0;
			$banexpirynew = $banexpirynew > $timestamp ? $banexpirynew : 0;
			if($banexpirynew) {
				$member['groupterms']['main'] = array('time' => $banexpirynew, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
				$member['groupterms']['ext'][$groupidnew] = $banexpirynew;
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$sql .= ', groupexpiry=0';
			}
			$adminidnew = -1;
		} elseif($member['groupid'] == 4 || $member['groupid'] == 5) {
			if(!empty($member['groupterms']['main']['groupid'])) {
				$groupidnew = $member['groupterms']['main']['groupid'];
				$adminidnew = $member['groupterms']['main']['adminid'];
				unset($member['groupterms']['main']);
				unset($member['groupterms']['ext'][$member['groupid']]);
				$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
			} else {
				$groupidnew = $db->result_first("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
				$adminidnew = 0;
			}
		} else {
			$groupidnew = $member['groupid'];
			$adminidnew = $member['adminid'];
		}

		$sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
		$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$member[uid]'");

		if($allowbanuser && ($db->affected_rows($query))) {
			banlog($member['username'], $member['groupid'], $groupidnew, $banexpirynew, $reason);
		}

		$db->query("UPDATE {$tablepre}memberfields SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");

		if($delpost && $bannew && $adminid == 1) {
			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE uid='$member[uid]'");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			}

			$db->query("DELETE FROM {$tablepre}threads WHERE authorid='$member[uid]'", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}posts WHERE authorid='$member[uid]'", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}trades WHERE sellerid='$member[uid]'", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE uid='$member[uid]'", 'UNBUFFERED');
		}

		cpmsg('members_edit_succeed', 'admincp.php?action=banmember');

	}



} elseif($action == 'editmember') {

	shownav('members_edit');

	if(empty($uid) && empty($username)) {

?>
<form method="post" action="admincp.php?action=editmember">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit']?> - <?=$lang['members_search']?></td></tr>
<tr class="altbg2">
<td><?=$lang['username']?>:</td><td><input type="text" name="username"></td></tr>
</table><br /><center>
<input class="button" type="submit" name="membersubmit" value="<?=$lang['submit']?>">
</center></form><br />
<?

	} else {

		$member = $db->fetch_first("SELECT m.*, mf.*, u.type AS grouptype, u.allowsigbbcode, u.allowsigimgcode, u.allowcusbbcode, u.allowbiobbcode, u.allowbioimgcode, u.allowcusbbcode FROM {$tablepre}members m
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
			WHERE ".($uid ? "m.uid='$uid'" : "m.username='$username'"));

		if(!$member) {
			cpmsg('members_edit_nonexistence');
		} elseif(($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['grouptype'] == 'special') {
			cpmsg('members_edit_illegal');
		}

		$member['groupterms'] = unserialize($member['groupterms']);

		if(!submitcheck('editsubmit')) {

			echo "<br /><form method=\"post\" action=\"admincp.php?action=editmember&uid=$member[uid]&formhash=".FORMHASH."\">";

			require_once DISCUZ_ROOT.'./include/editor.func.php';

			$member['bio'] = html2bbcode($member['bio']);
			$member['signature'] = html2bbcode($member['sightml']);

			if($allowedituser) {
				showtype($lang['members_edit'].' - '.$member[username], 'top');
				showsetting('members_edit_location', 'locationnew', $member['location'], 'text');
				showsetting('members_edit_bio', 'bionew', $member['bio'], 'textarea');
				showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');
				showtype('', 'bottom');
			}

			echo '<br /><br /><center><input class="button" type="submit" name="editsubmit" value="'.$lang['submit'].'"></center></form>';

		} else {

			$sql = 'uid=uid';

			if($allowedituser) {
				require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

				$signaturenew = censor($signaturenew);
				$sightmlnew = addslashes(discuzcode(stripslashes($signaturenew), 1, 0, 0, 0, $member['allowsigbbcode'], $member['allowsigimgcode'], 0));
				$bionew = censor(dhtmlspecialchars($bionew));
				$biohtmlnew = addslashes(discuzcode(stripslashes($bionew), 1, 0, 0, 0, ($member['allowbiobbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowbioimgcode'], 0));
				$locationnew = dhtmlspecialchars($locationnew);

				$sql .= ', sigstatus=\''.($signaturenew ? 1 : 0).'\'';
				$db->query("UPDATE {$tablepre}memberfields SET location='$locationnew', bio='$biohtmlnew', sightml='$sightmlnew' WHERE uid='$member[uid]'");
			}

			$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$member[uid]'");

			$db->query("UPDATE {$tablepre}memberfields SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");

			cpmsg('members_edit_succeed', 'admincp.php?action=editmember');

		}

	}

} elseif($action == 'access') {

	$member = $db->fetch_first("SELECT username, adminid, groupid FROM {$tablepre}members WHERE uid='$uid'");
	if(!$member) {
		cpmsg('undefined_action');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow');
	}

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	if(!submitcheck('accesssubmit')) {

		$accessmasks = array();
		$query = $db->query("SELECT * FROM {$tablepre}access WHERE uid='$uid'");
		while($access = $db->fetch_array($query)) {
			$accessmasks[$access['fid']] = $access;
		}

		$members = '';
		foreach($_DCACHE['forums'] as $fid => $forum) {
			if($forum['type'] != 'group') {
				if(isset($accessmasks[$fid])) {
					$check = array(	'default'	=> '',
					'view'		=> ($accessmasks[$fid]['allowview'] ? 'checked' : ''),
					'post'		=> ($accessmasks[$fid]['allowpost'] ? 'checked' : ''),
					'reply'		=> ($accessmasks[$fid]['allowreply'] ? 'checked' : ''),
					'getattach'	=> ($accessmasks[$fid]['allowgetattach'] ? 'checked' : ''),
					'postattach'	=> ($accessmasks[$fid]['allowpostattach'] ? 'checked' : ''));
				} else {
					$check = array(	'default'	=> 'checked',
					'view'		=> '',
					'post'		=> '',
					'reply'		=> '',
					'getattach'	=> '',
					'postattach'	=> '');
				}

				$members .= "<tr><td class=\"altbg1\" width=\"22%\"><input class=\"checkbox\" title=\"$lang[select_all]\" type=\"checkbox\" name=\"chkallv$fid\" onclick=\"checkallvalue(this.form, $fid, 'chkallv$fid')\">";
				$members .= $forum['type'] == 'forum' ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$members .= "&nbsp;<a href=\"admincp.php?action=forumdetail&fid=$fid\">$forum[name]</a></td>".
					"<td class=\"altbg2\" width=\"15%\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"defaultnew[$fid]\" value=\"1\" $check[default]></td>\n".
					"<td class=\"altbg1\" width=\"10%\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"allowviewnew[$fid]\" value=\"$fid\" $check[view]></td>\n".
					"<td class=\"altbg2\" width=\"12%\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"allowpostnew[$fid]\" value=\"$fid\" $check[post]></td>\n".
					"<td class=\"altbg1\" width=\"10%\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"allowreplynew[$fid]\" value=\"$fid\" $check[reply]></td>\n".
					"<td class=\"altbg2\" width=\"18%\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"allowgetattachnew[$fid]\" value=\"$fid\" $check[getattach]></td>\n".
					"<td class=\"altbg1\" width=\"13%\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"allowpostattachnew[$fid]\" value=\"$fid\" $check[postattach]></td></tr>";
			}
		}

		shownav('access_edit');
		showtips('access_tips');

?>
<form method="post" action="admincp.php?action=access&uid=<?=$uid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="7"><?=$lang['access_edit']?> - <?=$member['username']?></td></tr>
<tr class="category" align="center">
<td><?=$lang['forum']?></td>
<td><input class="checkbox" type="checkbox" name="chkall1" onclick="checkall(this.form, 'defaultnew', 'chkall1')"> <?=$lang['access_default']?></td>
<td><input class="checkbox" type="checkbox" name="chkall2" onclick="checkall(this.form, 'allowviewnew', 'chkall2')"> <?=$lang['access_view']?></td>
<td><input class="checkbox" type="checkbox" name="chkall3" onclick="checkall(this.form, 'allowpostnew', 'chkall3')"> <?=$lang['access_post']?></td>
<td><input class="checkbox" type="checkbox" name="chkall4" onclick="checkall(this.form, 'allowreplynew', 'chkall4')"> <?=$lang['access_reply']?></td>
<td><input class="checkbox" type="checkbox" name="chkall5" onclick="checkall(this.form, 'allowgetattachnew', 'chkall5')"> <?=$lang['access_getattach']?></td>
<td><input class="checkbox" type="checkbox" name="chkall6" onclick="checkall(this.form, 'allowpostattachnew', 'chkall6')"> <?=$lang['access_postattach']?></td></tr>
<?=$members?>
</table><br />
<center>
<input class="button" type="reset" name="accesssubmit" value="<?=$lang['reset']?>">
<input class="button" type="submit" name="accesssubmit" value="<?=$lang['submit']?>">
</center></form>
<?

	} else {

		$accessarray = array();
		if(is_array($_DCACHE['forums'])) {
			foreach($_DCACHE['forums'] as $fid => $forum) {
				if($forum['type'] != 'group') {
					if(!$defaultnew[$fid] && ($allowviewnew[$fid] || $allowpostnew[$fid] || $allowreplynew[$fid] || $allowgetattachnew[$fid] || $allowpostattachnew[$fid])) {
						$accessarray[$fid] = "'$allowviewnew[$fid]', '$allowpostnew[$fid]', '$allowreplynew[$fid]', '$allowgetattachnew[$fid]', '$allowpostattachnew[$fid]'";
					}
				}
			}
		}

		$db->query("DELETE FROM {$tablepre}access WHERE uid='$uid'");
		$db->query("UPDATE {$tablepre}members SET accessmasks='".($accessarray ? 1 : 0)."' WHERE uid='$uid'");

		foreach($accessarray as $fid => $access) {
			$db->query("INSERT INTO {$tablepre}access (uid, fid, allowview, allowpost, allowreply, allowgetattach, allowpostattach)
					VALUES ('$uid', '$fid', $access)");
		}

		updatecache('forums');

		cpmsg('access_succeed');

	}

} elseif($action == 'memberprofile') {

	$member = $db->fetch_first("SELECT m.*, mf.*, o.*, u.type, u.allowsigbbcode, u.allowsigimgcode, u.allowcusbbcode, u.allowbiobbcode, u.allowbioimgcode, u.allowcusbbcode FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		LEFT JOIN {$tablepre}onlinetime o ON o.uid=m.uid
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE m.uid='$uid'");

	if(!$member) {
		cpmsg('undefined_action');
	} elseif(!$isfounder && ($member['adminid'] == 1 || $member['groupid'] == 1)) {
		cpmsg('members_super_edit_admin_allow');
	}
	$member['uid'] = intval($uid);

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_profilefields.php';
	$fields = array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']);

	if(!submitcheck('editsubmit')) {

		require_once DISCUZ_ROOT.'./include/editor.func.php';

		$styleselect = "<select name=\"styleidnew\">\n<option value=\"\">$lang[use_default]</option>";
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$styleselect .= "<option value=\"$style[styleid]\" ".($style['styleid'] == $member['styleid'] ? 'selected="selected"' : '').">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		$tfcheck = array($member['timeformat'] => 'checked');
		$gendercheck = array($member['gender'] => 'checked');
		$pscheck = array($member['pmsound'] => 'checked');

		$member['regdate'] = gmdate('Y-n-j h:i A', $member['regdate'] + $timeoffset * 3600);
		$member['lastvisit'] = gmdate('Y-n-j h:i A', $member['lastvisit'] + $timeoffset * 3600);

		$member['bio'] = html2bbcode($member['bio']);
		$member['signature'] = html2bbcode($member['sightml']);

		shownav('members_edit');

		echo "<form method=\"post\" action=\"admincp.php?action=memberprofile&uid=$uid&formhash=".FORMHASH."\">";

		showtype("$lang[members_edit] - $member[username]", 'top');
		showsetting('members_edit_username', 'usernamenew', $member['username'], 'text');
		showsetting('members_edit_password', 'passwordnew', '', 'text');
		showsetting('members_edit_clearquestion', 'clearquestion', !$member['secques'], 'radio');
		showsetting('members_edit_clearspacecache', 'clearspacecache', '', 'radio');
		showsetting('members_edit_nickname', 'nicknamenew', $member['nickname'], 'text');
		showsetting('members_edit_gender', '', '', '<input class="radio" type="radio" name="gendernew" value="1" '.$gendercheck[1].'> '.$lang['members_edit_gender_male'].' <input class="radio" type="radio" name="gendernew" value="2" '.$gendercheck[2].'> '.$lang['members_edit_gender_female'].' <input class="radio" type="radio" name="gendernew" value="0" '.$gendercheck[0].'> '.$lang['members_edit_gender_secret']);
		showsetting('members_edit_email', 'emailnew', $member['email'], 'text');
		showsetting('members_edit_posts', 'postsnew', $member['posts'], 'text');
		showsetting('members_edit_digestposts', 'digestpostsnew', $member['digestposts'], 'text');
		showsetting('members_edit_pageviews', 'pageviewsnew', $member['pageviews'], 'text');
		showsetting('members_edit_online_total', 'totalnew', $member['total'], 'text');
		showsetting('members_edit_online_thismonth', 'thismonthnew', $member['thismonth'], 'text');
		showsetting('members_edit_regip', 'regipnew', $member['regip'], 'text');
		showsetting('members_edit_regdate', 'regdatenew', $member['regdate'], 'text');
		showsetting('members_edit_lastvisit', 'lastvisitnew', $member['lastvisit'], 'text');
		showsetting('members_edit_lastip', 'lastipnew', $member['lastip'], 'text');

		showtype('members_edit_info');
		showsetting('members_edit_site', 'sitenew', $member['site'], 'text');
		showsetting('members_edit_qq', 'qqnew', $member['qq'], 'text');
		showsetting('members_edit_icq', 'icqnew', $member['icq'], 'text');
		showsetting('members_edit_yahoo', 'yahoonew', $member['yahoo'], 'text');
		showsetting('members_edit_msn', 'msnnew', $member['msn'], 'text');
		showsetting('members_edit_taobao', 'taobaonew', $member['taobao'], 'text');
		showsetting('members_edit_alipay', 'alipaynew', $member['alipay'], 'text');
		showsetting('members_edit_location', 'locationnew', $member['location'], 'text');
		showsetting('members_edit_bday', 'bdaynew', $member['bday'], 'text');
		showsetting('members_edit_avatar', 'avatarnew', $member['avatar'], 'text');
		showsetting('members_edit_avatar_width', 'avatarwidthnew', $member['avatarwidth'], 'text');
		showsetting('members_edit_avatar_height', 'avatarheightnew', $member['avatarheight'], 'text');
		showsetting('members_edit_bio', 'bionew', $member['bio'], 'textarea');
		showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');

		showtype('members_edit_option');
		showsetting('members_edit_style', '', '', $styleselect);
		showsetting('members_edit_tpp', 'tppnew', $member['tpp'], 'text');
		showsetting('members_edit_ppp', 'pppnew', $member['ppp'], 'text');
		showsetting('members_edit_cstatus', 'cstatusnew', $member['customstatus'], 'text');
		showsetting('members_edit_timeformat', '', '', '<input class="radio" type="radio" name="timeformatnew" value="0" '.$tfcheck[0].'> '.$lang['default'].' &nbsp; <input class="radio" type="radio" name="timeformatnew" value="1" '.$tfcheck[1].'> '.$lang['members_edit_timeformat_12'].' &nbsp; <input class="radio" type="radio" name="timeformatnew" value="2" '.$tfcheck[2].'> '.$lang['members_edit_timeformat_24']);
		showsetting('members_edit_timeoffset', 'timeoffsetnew', $member['timeoffset'], 'text');
		showsetting('members_edit_pmsound', '', '', '<input class="radio" type="radio" value="0" name="pmsoundnew" '.$pscheck[0].'>'.$lang['none'].' &nbsp; <input class="radio" type="radio" value="1" name="pmsoundnew" '.$pscheck[1].'><a href="images/sound/pm_1.wav">#1</a> &nbsp; <input class="radio" type="radio" value="2" name="pmsoundnew" '.$pscheck[2].'><a href="images/sound/pm_2.wav">#2</a> &nbsp; <input class="radio" type="radio" value="3" name="pmsoundnew" '.$pscheck[3].'><a href="images/sound/pm_3.wav">#3</a>');
		showsetting('members_edit_invisible', 'invisiblenew', $member['invisible'], 'radio');
		showsetting('members_edit_showemail', 'showemailnew', $member['showemail'], 'radio');
		showsetting('members_edit_newsletter', 'newsletternew', $member['newsletter'], 'radio');
		showsetting('members_edit_ignorepm', 'ignorepmnew', $member['ignorepm'], 'textarea');

		if($fields) {
			showtype('members_edit_profilefield');
			foreach($fields as $field) {
				if($field['selective']) {
					$fieldselect = "<select name=\"field_$field[fieldid]new\"><option value=\"\">&nbsp;</option>";
					foreach($field['choices'] as $index => $choice) {
						$fieldselect .= "<option value=\"$index\" ".($index == $member['field_'.$field['fieldid']] ? 'selected="selected"' : '').">$choice</option>";
					}
					$fieldselect .= '</select>';
					showsetting($field['title'], '', '', $fieldselect);
				} else {
					showsetting($field['title'], "field_$field[fieldid]new", $member['field_'.$field['fieldid']], 'text');
				}
			}
		}

		showtype('', 'bottom');

		echo '<br /><br /><center><input class="button" type="submit" name="editsubmit" value="'.$lang['submit'].'"></center></form>';

	} else {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$usernameold = addslashes($member['username']);
		if($usernamenew && $usernameold != $usernamenew) {
			$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$usernamenew'");
			if(($db->result($query, 0)) && ($db->result($query, 0)) != $member['uid']) {
				cpmsg('members_edit_duplicate');
			}
			$db->query("UPDATE {$tablepre}announcements SET author='$usernamenew' WHERE author='$usernameold'");
			$db->query("UPDATE {$tablepre}banned SET admin='$usernamenew' WHERE admin='$usernameold'");
			$db->query("UPDATE {$tablepre}forums SET lastpost=REPLACE(lastpost, '\t$usernameold', '\t$usernamenew')");
			$db->query("UPDATE {$tablepre}members SET username='$usernamenew' WHERE uid='$member[uid]'");
			$db->query("UPDATE {$tablepre}pms SET msgfrom='$usernamenew' WHERE msgfromid='$member[uid]'");
			$db->query("UPDATE {$tablepre}posts SET author='$usernamenew' WHERE authorid='$member[uid]'");
			$db->query("UPDATE {$tablepre}threads SET author='$usernamenew' WHERE authorid='$member[uid]'");
			$db->query("UPDATE {$tablepre}threads SET lastposter='$usernamenew' WHERE lastposter='$usernameold'");
			$db->query("UPDATE {$tablepre}threadsmod SET username='$usernamenew' WHERE uid='$member[uid]'");

			$username = $usernamenew;
		}

		$creditsnew = intval($creditsnew);

		$regdatenew = strtotime($regdatenew);
		$lastvisitnew = strtotime($lastvisitnew);

		$passwordadd = $passwordnew ? ", password='".md5($passwordnew)."'" : '';
		$secquesadd = $clearquestion ? ", secques=''" : '';

		$signaturenew = censor($signaturenew);
		$sigstatusnew = $signaturenew ? 1 : 0;
		$sightmlnew = addslashes(discuzcode(stripslashes($signaturenew), 1, 0, 0, 0, ($member['allowsigbbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowsigimgcode'], 0));
		$bionew = censor(dhtmlspecialchars($bionew));
		$biohtmlnew = addslashes(discuzcode(stripslashes($bionew), 1, 0, 0, 0, ($member['allowbiobbcode'] ? ($member['allowcusbbcode'] ? 2 : 1) : 0), $member['allowbioimgcode'], 0));

		$oltimenew = round($totalnew / 60);

		$fieldadd = '';
		foreach(array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']) as $field) {
			$field_key = 'field_'.$field['fieldid'];
			$field_val = trim(${'field_'.$field['fieldid'].'new'});
			if($field['selective'] && $field_val != '' && !isset($field['choices'][$field_val])) {
				cpmsg('undefined_action');
			} else {
				$fieldadd .= ", $field_key='".dhtmlspecialchars($field_val)."'";
			}
		}

		if($clearspacecache) {
			$db->query("DELETE FROM {$tablepre}spacecaches WHERE uid='$uid'");
		}

		$db->query("UPDATE {$tablepre}members SET gender='$gendernew', email='$emailnew', posts='$postsnew', digestposts='$digestpostsnew',
			pageviews='$pageviewsnew', regip='$regipnew', regdate='$regdatenew', lastvisit='$lastvisitnew', lastip='$lastipnew', bday='$bdaynew',
			styleid='$styleidnew', tpp='$tppnew', ppp='$pppnew', timeformat='$timeformatnew', oltime='$oltimenew',
			showemail='$showemailnew', newsletter='$newsletternew', invisible='$invisiblenew', timeoffset='$timeoffsetnew',
			pmsound='$pmsoundnew', sigstatus='$sigstatusnew' $passwordadd $secquesadd WHERE uid='$uid'");

		$db->query("UPDATE {$tablepre}memberfields SET nickname='$nicknamenew', site='$sitenew', qq='$qqnew', icq='$icqnew', yahoo='$yahoonew', msn='$msnnew',
			taobao='$taobaonew', alipay='$alipaynew', location='$locationnew', bio='$biohtmlnew', customstatus='$cstatusnew', ignorepm='$ignorepmnew', avatar='$avatarnew',
			avatarwidth='$avatarwidthnew', avatarheight='$avatarheightnew', sightml='$sightmlnew'
			$fieldadd WHERE uid='$uid'");

		$db->query("REPLACE INTO {$tablepre}onlinetime (uid, thismonth, total)
			VALUES ('$uid', '$thismonthnew', '$totalnew')");

		cpmsg('members_edit_succeed');

	}

} elseif($action == 'profilefields') {

	if(!submitcheck('fieldsubmit') && !submitcheck('editsubmit') && !$edit) {

		$query = $db->query("SELECT * FROM {$tablepre}profilefields");
		while($field = $db->fetch_array($query)) {
			$profilefields .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[{$field[fieldid]}]\" value=\"$field[fieldid]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"18\" name=\"titlenew[{$field[fieldid]}]\" value=\"$field[title]\">\n".
					"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[{$field[fieldid]}]\" value=\"1\" ".($field['available'] ? 'checked' : '')."></td>\n".
					"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"invisiblenew[{$field[fieldid]}]\" value=\"1\" ".($field['invisible'] ? 'checked' : '')."></td>\n".
					"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"unchangeablenew[{$field[fieldid]}]\" value=\"1\" ".($field['unchangeable'] ? 'checked' : '')."></td>\n".
					"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"showinthreadnew[{$field[fieldid]}]\" value=\"1\" ".($field['showinthread'] ? 'checked' : '')."></td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayordernew[{$field[fieldid]}]\" value=\"$field[displayorder]\"></td>\n".
					"<td class=\"altbg2\"><a href=\"admincp.php?action=profilefields&edit=$field[fieldid]\">[$lang[detail]]</a></td></tr>\n";
		}
		shownav('members_edit_profilefields');

		showtips('members_edit_profilefields_tips');

?>
<form method="post" action="admincp.php?action=profilefields">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['fields_title']?></td><td><?=$lang['available']?></td><td><?=$lang['fields_invisible']?></td><td><?=$lang['fields_unchangeable']?></td><td><?=$lang['fields_show_in_thread']?></td><td><?=$lang['display_order']?></td><td><?=$lang['edit']?></td></tr>
<?=$profilefields?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type='text' name="newtitle" size="18"></td>
<td colspan="6">&nbsp;</td>
</tr></table><br />
<center><input class="button" type="submit" name="fieldsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} elseif(submitcheck('fieldsubmit')) {

		if(is_array($titlenew)) {
			foreach($titlenew as $id => $val) {
				$db->query("UPDATE {$tablepre}profilefields SET title='$titlenew[$id]', available='$availablenew[$id]', invisible='$invisiblenew[$id]', displayorder='$displayordernew[$id]', unchangeable='$unchangeablenew[$id]', showinthread='$showinthreadnew[$id]' WHERE fieldid='$id'");
			}
		}

		if(is_array($delete)) {
			$ids = implode('\',\'', $delete);
			$dropfields = implode(',DROP field_', $delete);
			$db->query("DELETE FROM {$tablepre}profilefields WHERE fieldid IN ('$ids')");
			$db->query("ALTER TABLE {$tablepre}memberfields DROP field_$dropfields");
		}

		if($newtitle) {
			$db->query("INSERT INTO {$tablepre}profilefields (available, invisible, title, size)
					VALUES ('1', '0', '$newtitle', '50')");
			$fieldid = $db->insert_id();
			$db->query("ALTER TABLE {$tablepre}memberfields ADD field_$fieldid varchar(50) NOT NULL", 'SILENT');
		}

		updatecache(array('fields_required', 'fields_optional', 'custominfo'));
		cpmsg('fields_edit_succeed', 'admincp.php?action=profilefields');

	} elseif($edit) {

		$field = $db->fetch_first("SELECT * FROM {$tablepre}profilefields WHERE fieldid='$edit'");
		if(!$field) {
			cpmsg('undefined_action');
		}

		if(!submitcheck('editsubmit')) {

			echo "<form method=\"post\" action=\"admincp.php?action=profilefields&edit=$edit&formhash=".FORMHASH."\">\n";

			showtype("$lang[fields_edit] - $field[title]", 'top');
			showsetting('fields_edit_title', 'titlenew', $field['title'], 'text');
			showsetting('fields_edit_desc', 'descriptionnew', $field['description'], 'text');
			showsetting('fields_edit_size', 'sizenew', $field['size'], 'text');
			showsetting('fields_edit_invisible', 'invisiblenew', $field['invisible'], 'radio');
			showsetting('fields_edit_required', 'requirednew', $field['required'], 'radio');
			showsetting('fields_edit_unchangeable', 'unchangeablenew', $field['unchangeable'], 'radio');
			showsetting('fields_edit_show_in_thread', 'showinthreadnew', $field['showinthread'], 'radio');
			showsetting('fields_edit_selective', 'selectivenew', $field['selective'], 'radio');
			showsetting('fields_edit_choices', 'choicesnew', $field['choices'], 'textarea');
			showtype('', 'bottom');

			echo "<br /><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

		} else {

			$titlenew = trim($titlenew);
			$sizenew = $sizenew <= 255 ? $sizenew : 255;
			if(!$titlenew || !$sizenew) {
				cpmsg('fields_edit_invalid');
			}

			if($sizenew != $field['size']) {
				$db->query("ALTER TABLE {$tablepre}memberfields CHANGE field_$edit field_$edit varchar($sizenew) NOT NULL");
			}

			$db->query("UPDATE {$tablepre}profilefields SET title='$titlenew', description='$descriptionnew', size='$sizenew', invisible='$invisiblenew', required='$requirednew', unchangeable='$unchangeablenew', showinthread='$showinthreadnew', selective='$selectivenew', choices='$choicesnew' WHERE fieldid='$edit'");

			updatecache(array('fields_required', 'fields_optional', 'custominfo'));
			cpmsg('fields_edit_succeed', 'admincp.php?action=profilefields');
		}

	}

} elseif($action == 'ipban') {

	if(!submitcheck('ipbansubmit')) {

		require_once DISCUZ_ROOT.'./include/misc.func.php';

		$iptoban = explode('.', $ip);

		$ipbanned = '';
		$query = $db->query("SELECT * FROM {$tablepre}banned ORDER BY dateline");
		while($banned = $db->fetch_array($query)) {
			for($i = 1; $i <= 4; $i++) {
				if($banned["ip$i"] == -1) {
					$banned["ip$i"] = '*';
				}
			}
			$disabled = $adminid != 1 && $banned['admin'] != $discuz_userss ? 'disabled' : '';
			$banned['dateline'] = gmdate($dateformat, $banned['dateline'] + $timeoffset * 3600);
			$banned['expiration'] = gmdate($dateformat, $banned['expiration'] + $timeoffset * 3600);
			$theip = "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4]";
			$ipbanned .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$banned[id]]\" value=\"$banned[id]\" $disabled></td>\n".
				"<td class=\"altbg2\">$theip</td>\n".
				"<td class=\"altbg1\">".convertip($theip, "./")."</td>\n".
				"<td class=\"altbg2\">$banned[admin]</td>\n".
				"<td class=\"altbg1\">$banned[dateline]</td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"10\" name=\"expirationnew[$banned[id]]\" value=\"$banned[expiration]\" $disabled></td></tr>\n";
		}

		shownav('menu_members_ipban');
		showtips('ipban_tips');

?>
<form method="post" action="admincp.php?action=ipban">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['ip']?></td><td><?=$lang['ipban_location']?></td><td><?=$lang['operator']?></td><td><?=$lang['start_time']?></td><td><?=$lang['end_time']?></td></tr>
<?=$ipbanned?>
<tr align="center" class="altbg1">
<td><?=$lang['add_new']?></td>
<td colspan="3"><b>
<input type="text" name="ip1new" value="<?=$iptoban[0]?>" size="3" maxlength="3"> .
<input type="text" name="ip2new" value="<?=$iptoban[1]?>" size="3" maxlength="3"> .
<input type="text" name="ip3new" value="<?=$iptoban[2]?>" size="3" maxlength="3"> .
<input type="text" name="ip4new" value="<?=$iptoban[3]?>" size="3" maxlength="3"></b></td>
<td colspan="2"><?=$lang['validity']?>: <input type="text" name="validitynew" value="30" size="3"> <?=$lang['ipban_days']?></td>
</tr></table><br />
<center><input class="button" type="submit" name="ipbansubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}banned WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if($ip1new != '' && $ip2new != '' && $ip3new != '' && $ip4new != '') {
			$own = 0;
			$ip = explode('.', $onlineip);
			for($i = 1; $i <= 4; $i++) {
				if(!is_numeric(${'ip'.$i.'new'}) || ${'ip'.$i.'new'} < 0) {
					if($adminid != 1) {
						cpmsg('ipban_nopermission');
					}
					${'ip'.$i.'new'} = -1;
					$own++;
				} elseif(${'ip'.$i.'new'} == $ip[$i - 1]) {
					$own++;
				}
				${'ip'.$i.'new'} = intval(${'ip'.$i.'new'});
			}

			if($own == 4) {
				cpmsg('ipban_illegal');
			}

			$query = $db->query("SELECT * FROM {$tablepre}banned");
			while($banned = $db->fetch_array($query)) {
				$exists = 0;
				for($i = 1; $i <= 4; $i++) {
					if($banned["ip$i"] == -1) {
						$exists++;
					} elseif($banned["ip$i"] == ${"ip".$i."new"}) {
						$exists++;
					}
				}
				if($exists == 4) {
					cpmsg('ipban_invalid');
				}
			}

			$expiration = $timestamp + $validitynew * 86400;

			$db->query("UPDATE {$tablepre}sessions SET groupid='6' WHERE ('$ip1new'='-1' OR ip1='$ip1new') AND ('$ip2new'='-1' OR ip2='$ip2new') AND ('$ip3new'='-1' OR ip3='$ip3new') AND ('$ip4new'='-1' OR ip4='$ip4new')");
			$db->query("INSERT INTO {$tablepre}banned (ip1, ip2, ip3, ip4, admin, dateline, expiration)
				VALUES ('$ip1new', '$ip2new', '$ip3new', '$ip4new', '$discuz_user', '$timestamp', '$expiration')");

		}

		if(is_array($expirationnew)) {
			foreach($expirationnew as $id => $expiration) {
				$db->query("UPDATE {$tablepre}banned SET expiration='".strtotime($expiration)."' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
			}
		}

		updatecache('ipbanned');
		cpmsg('ipban_succeed', 'admincp.php?action=ipban');

	}

}

function banlog($username, $origgroupid, $newgroupid, $expiration, $reason) {
	global $discuz_userss, $groupid, $onlineip, $timestamp, $forum, $reason;
	writelog('banlog', dhtmlspecialchars("$timestamp\t$discuz_userss\t$groupid\t$onlineip\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason"));
}

function selectday($varname, $dayarray) {
	global $timestamp, $dateformat, $timeformat, $timeoffset, $lang;
	$selectday = '<select name="'.$varname.'">';
	if($dayarray && is_array($dayarray)) {
		foreach($dayarray as $day) {
			$langday = $day.'_day';
			$daydate = $day ? '('.gmdate("$dateformat $timeformat", ($timestamp + $day * 86400) + $timeoffset * 3600).')' : '';
			$selectday .= '<option value='.$day.'>'.$lang[$langday].'&nbsp;'.$daydate.'</option>';
		}
	}
	$selectday .= '</select>';

	return $selectday;
}
?>