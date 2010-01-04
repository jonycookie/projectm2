<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: logs.inc.php 11186 2007-11-09 01:38:28Z heyond $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

function get_log_files($logdir='E:\test', $action='action') {
	$dir = opendir($logdir);
	$files = array();
	while($entry = readdir($dir)) {
		$files[] = $entry;
	}
	closedir($dir);
	
	sort($files);
	$logfile = $action;
	$logfiles = array();
	foreach($files as $file) {
		if(strpos($file, $logfile) !== FALSE) {
			$logfiles[] = $file;
		}
	}
	$logfiles = array_slice($logfiles, -2, 2);
	return $logfiles;
}

$lpp = empty($lpp) ? 50 : $lpp;

if(!in_array($action, array('illegallog', 'ratelog', 'modslog', 'medalslog', 'banlog', 'cplog', 'errorlog', 'invitelog'))) {
	cpmsg('undefined_action');
}
$logdir = DISCUZ_ROOT.'./forumdata/logs/';
$logfiles = get_log_files($logdir, $action);
$logs = array();
foreach($logfiles as $logfile) {
	$logs = array_merge($logs, file($logdir.$logfile));
}


$page = max(1, intval($page));
$start = ($page - 1) * $lpp;
$logs = array_reverse($logs);

if(empty($keyword)) {
	$num = count($logs);
	$multipage = multi($num, $lpp, $page, "admincp.php?action=$action&lpp=$lpp");
	$logs = array_slice($logs, $start, $lpp);

} else {
	foreach($logs as $key => $value) {
		if(strpos($value, $keyword) === FALSE) {
			unset($logs[$key]);
		}
	}
	$multipage = '';
}

$lognames = array
	(
	'illegallog'	=> 'logs_passwd',
	'ratelog'	=> 'logs_rating',
	'modslog'	=> 'logs_moderate',
	'medalslog'	=> 'logs_medal',
	'banlog'	=> 'logs_banned',
	'cplog'		=> 'logs_cp',
	'errorlog'	=> 'logs_error',
	'invitelog'	=> 'logs_invite'
	);

shownav($lang[$lognames[$action]]);

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang[$lognames[$action]]?></td></tr>
<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td width="25%"><?=$lang['logs_lpp']?></td>
<td width="55%"><input type="text" name="lpp" size="40" maxlength="40" value="<?=$lpp?>"></td>
<td width="20%"><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>
<? if($action != 'invitelog') {?>
<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td><?=$lang['logs_search']?></td><td><input type="text" name="keyword" size="40" value="<?=dhtmlspecialchars($keyword)?>"></td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>
<?
} else {
	$inviteoperations = '';
	foreach(array('1', '2', '3', '4') as $single) {
		$inviteoperations .= '<input class="checkbox" type="checkbox" name="status[]" value="'.$single.'" '.(!empty($status) && is_array($status) && in_array($single, $status) ? 'checked' : '').'> '.$lang['invite_status_'.$single].' &nbsp; ';
	}
?>
<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td><?=$lang['action']?></td><td>
<?=$inviteoperations?>
</td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>
<?}?>
</table><br />
<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<?

$usergroup = array();
if(in_array($action, array('ratelog', 'modslog', 'banlog', 'cplog'))) {
	$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups");
	while($group = $db->fetch_array($query)) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}

if($action == 'illegallog') {

	echo "<tr class=\"header\">".
		"<td>$lang[logs_passwd_username]</td>".
		"<td>$lang[logs_passwd_password]</td>".
		"<td>$lang[logs_passwd_security]</td>".
		"<td>$lang[ip]</td>".
		"<td>$lang[time]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		if(strtolower($log[2]) == strtolower($discuz_userss)) {
			$log[2] = "<b>$log[2]</b>";
		}
		$log[5] = $allowviewip ? $log[5] : '-';

		echo "<tr align=\"center\"><td class=\"altbg1\">$log[2]</td>\n".
			"<td class=\"altbg2\">$log[3]</td><td class=\"altbg1\">$log[4]</td>\n".
			"<td class=\"altbg2\">$log[5]</td><td class=\"altbg1\">$log[1]</td></tr>\n";
	}

} elseif($action == 'ratelog') {

	echo "<tr class=\"header\">".
		"<td width=\"13%\">$lang[username]</td>".
		"<td width=\"12%\">$lang[usergroup]</td>".
		"<td width=\"12%\">$lang[time]</td>".
		"<td width=\"13%\">$lang[logs_rating_username]</td>".
		"<td width=\"14%\">$lang[logs_rating_rating]</td>".
		"<td width=\"23%\">$lang[subject]</td>".
		"<td width=\"13%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		$log[2] = "<a href=\"space.php?action=viewpro&username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]</a>";
		$log[3] = $usergroup[$log[3]];
		if($log[4] == $discuz_userss) {
			$log[4] = "<b>$log[4]</b>";
		}
		$log[4] = "<a href=\"space.php?action=viewpro&username=".rawurlencode($log[4])."\" target=\"_blank\">$log[4]</a>";
		$log[6] = $extcredits[$log[5]]['title'].' '.($log[6] < 0 ? "<b>$log[6]</b>" : "+$log[6]").' '.$extcredits[$log[5]]['unit'];
		$log[7] = $log[7] ? "<a href=\"./viewthread.php?tid=$log[7]\" target=\"_blank\" title=\"$log[8]\">".cutstr($log[8], 20)."</a>" : "<i>$lang[logs_rating_manual]</i>";

		echo "<tr align=\"center\"><td class=\"altbg1\">$log[2]</a></td><td class=\"altbg2\">$log[3]</td>\n".
			"<td class=\"altbg1\">$log[1]</td><td class=\"altbg2\">$log[4]</td>\n".
			"<td class=\"altbg1\">".(trim($log[10]) == 'D' ? $lang['logs_rating_delete'] : '')."$log[6]</td><td class=\"altbg2\">$log[7]</td>\n".
			"<td class=\"altbg1\">$log[9]</td></tr>\n";
	}

} elseif($action == 'modslog') {

	include language('modactions');

	echo "<tr class=\"header\">".
		"<td width=\"13%\">$lang[operator]</td>".
		"<td width=\"10%\">$lang[usergroup]</td>".
		"<td width=\"10%\">$lang[ip]</td>".
		"<td width=\"16%\">$lang[time]</td>".
		"<td width=\"12%\">$lang[forum]</td>".
		"<td width=\"19%\">$lang[thread]</td>".
		"<td width=\"10%\">$lang[action]</td>".
		"<td width=\"10%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		$log[2] = stripslashes($log[2]);
		$log[3] = $usergroup[$log[3]];
		$log[4] = $allowviewip ? $log[4] : '-';
		$log[6] = "<a href=\"./forumdisplay.php?fid=$log[5]\" target=\"_blank\">$log[6]</a>";
		$log[8] = "<a href=\"./viewthread.php?tid=$log[7]\" target=\"_blank\" title=\"$log[8]\">".cutstr($log[8], 15)."</a>";
		$log[9] = $modactioncode[trim($log[9])];

		echo "<tr align=\"center\"><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $discuz_userss ? "<b>$log[2]</b>" : $log[2])."</td>\n".
			"<td class=\"altbg2\">$log[3]</td><td class=\"altbg1\">$log[4]</td>\n".
			"<td class=\"altbg2\">$log[1]</td><td class=\"altbg1\">$log[6]</td>\n".
			"<td class=\"altbg2\">$log[8]</td><td class=\"altbg1\">$log[9]</td>\n".
			"<td class=\"altbg2\">$log[10]</td></tr>\n";
	}

} elseif($action == 'medalslog') {

	$medalsarray = array();
	$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available>'0'");
	while($medal = $db->fetch_array($query)) {
		$medalsarray[$medal['medalid']] = "<img src=\"images/common/$medal[image]\" border=\"0\" align=\"absmiddle\"> $medal[name]";
	}

	echo "<tr class=\"header\">".
		"<td width=\"13%\">$lang[operator]</td>".
		"<td width=\"13%\">$lang[ip]</td>".
		"<td width=\"13%\">$lang[time]</td>".
		"<td width=\"13%\">$lang[username]</td>".
		"<td width=\"7%\">$lang[action]</td>".
		"<td width=\"18%\">$lang[logs_medal_name]</td>".
		"<td width=\"23%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		$log[3] = $allowviewip ? $log[3] : '-';
		$log[4] = "<a href=\"space.php?action=viewpro&username=".rawurlencode($log[4])."\" target=\"_blank\">$log[4]</a>";
		$log[5] = isset($medalsarray[$log[5]]) ? $medalsarray[$log[5]] : $lang['members_edit_medals_unavailable'];
		$log[6] = $lang['members_edit_medals_'.$log[6]];

		echo "<tr align=\"center\"><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $discuz_userss ? "<b>$log[2]</b>" : $log[2])."</td>\n".
			"<td class=\"altbg2\">$log[3]</td><td class=\"altbg1\">$log[1]</td>\n".
			"<td class=\"altbg2\">$log[4]</td><td class=\"altbg1\">$log[6]</td>\n".
			"<td class=\"altbg2\">$log[5]</td><td class=\"altbg2\">$log[7]</td></tr>\n";
	}

} elseif($action == 'banlog') {

	echo "<tr class=\"header\">".
		"<td width=\"10%\">$lang[operator]</td>".
		"<td width=\"10%\">$lang[usergroup]</td>".
		"<td width=\"10%\">$lang[ip]</td>".
		"<td width=\"10%\">$lang[time]</td>".
		"<td width=\"10%\">$lang[username]</td>".
		"<td width=\"5%\">$lang[operation]</td>".
		"<td width=\"20%\">$lang[logs_banned_group]</td>".
		"<td width=\"8%\">$lang[validity]</td>".
		"<td width=\"17%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		$log[4] = $allowviewip ? $log[4] : '-';
		$log[3] = $usergroup[$log[3]];
		$log[8] = trim($log[8]) ? gmdate('y-n-j', $log[8] + $timeoffset * 3600) : '';

		echo "<tr align=\"center\"><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]</td>\n".
			"<td class=\"altbg2\">$log[3]</td><td class=\"altbg1\">$log[4]</td>\n".
			"<td class=\"altbg2\">$log[1]</td><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log[5])."\" target=\"_blank\">$log[5]</a></td>\n".
			"<td class=\"altbg2\">".(in_array($log[6], array(4, 5)) && !in_array($log[7], array(4, 5)) ? '<i>'.$lang['logs_banned_unban'].'</i>' : '<b>'.$lang['logs_banned_ban'].'</b>')."</td>".
			"<td class=\"altbg1\">{$usergroup[$log[6]]} / {$usergroup[$log[7]]}</td><td class=\"altbg1\">$log[8]</td>\n".
			"<td class=\"altbg1\">$log[9]</td></tr>\n";
	}

} elseif($action == 'cplog') {

	echo "<tr class=\"header\">".
		"<td width=\"10%\">$lang[operator]</td>".
		"<td width=\"10%\">$lang[usergroup]</td>".
		"<td width=\"10%\">$lang[ip]</td>".
		"<td width=\"18%\">$lang[time]</td>".
		"<td width=\"15%\">$lang[action]</td>".
		"<td width=\"37%\">$lang[other]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		$log[2] = stripslashes($log[2]);
		$log[3] = $usergroup[$log[3]];
		$log[4] = $allowviewip ? $log[4] : '-';
		$log[5] = $lang['cplog_action_'.rtrim($log[5])];
		echo "<tr align=\"center\"><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log[2])."\" target=\"_blank\">".($log[2] != $discuz_userss ? "<b>$log[2]</b>" : $log[2])."</a></td>\n".
			"<td class=\"altbg2\">$log[3]</td><td class=\"altbg1\">$log[4]</td>\n".
			"<td class=\"altbg2\">$log[1]</td><td class=\"altbg1\">$log[5]</td>\n".
			"<td class=\"altbg2\">$log[6]&nbsp;</td></tr>\n";
	}

} elseif($action == 'errorlog') {

	echo "<tr class=\"header\">".
		"<td width=\"8%\">$lang[type]</td>".
		"<td width=\"15%\">$lang[username]</td>".
		"<td width=\"15%\">$lang[time]</td>".
		"<td width=\"62%\">$lang[message]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = gmdate('y-n-j H:i', $log[1] + $timeoffset * 3600);
		$tmp = explode('&lt;br&gt;', $log[3]);
		$username = $tmp[0];
		$ip = $tmp[1];

		echo "<tr align=\"center\"><td class=\"altbg1\">$log[2]</td>\n".
			"<td class=\"altbg2\"><a href=\"space.php?action=viewpro&username=".rawurlencode($username)."\" target=\"_blank\">$username</a><br />$ip</td><td class=\"altbg1\">$log[1]</td>\n".
			"<td class=\"altbg2\">$log[4]</td></tr>\n";
	}

} elseif($action == 'invitelog') {

	if(!submitcheck('invitesubmit')) {
		echo "<form method=\"post\" action=\"admincp.php?action=$action\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\">".
			"<tr class=\"header\">".
			"<td width=\"7%\"><input type=\"checkbox\" name=\"chkall\" class=\"checkbox\" onclick=\"checkall(this.form)\">$lang[del]</td>".
			"<td width=\"8%\">$lang[logs_invite_buyer]</td>".
			"<td width=\"15%\">$lang[logs_invite_buydate]</td>".
			"<td width=\"15%\">$lang[logs_invite_expiration]</td>".
			"<td width=\"12%\">$lang[logs_invite_ip]</td>".
			"<td width=\"20%\">$lang[logs_invite_code]</td>".
			"<td width=\"18%\">$lang[logs_invite_status]</td>".
			"</tr>\n";

		$tpp = $lpp ? intval($lpp) : $tpp;
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$addstatus = '';
		if($status && is_array($status)) {
			foreach($status as $single) {
				$statusurl .= '&status[]='.rawurlencode($single);
			}
			$addstatus = 'AND status IN ('.implodeids($status).')';
		}

		$invitecount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}invites WHERE 1 $addstatus");
		$multipage = multi($invitecount, $tpp, $page, "admincp.php?action=invitelog&lpp=$lpp$statusurl");

		$query = $db->query("SELECT i.*, m.username FROM {$tablepre}invites i, {$tablepre}members m
				WHERE i.uid=m.uid $addstatus
				ORDER BY i.dateline LIMIT $start_limit,$tpp");
		while($invite = $db->fetch_array($query)) {
			$invite['status'] = $lang['invite_status_'.$invite['status']];
			echo "<tr class=\"altbg1\">".
				"<td><input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$invite[invitecode]\"></td>".
				"<td><a href=\"space.php?action=viewpro&uid=$invite[uid]\">$invite[username]</a></td>".
				"<td>".gmdate('Y-n-j H:i', $invite['dateline'] + $timeoffset * 3600)."</td>".
				"<td>".gmdate('Y-n-j H:i', $invite['expiration'] + $timeoffset * 3600)."</td>".
				"<td>$invite[inviteip]</td>".
				"<td>$invite[invitecode]</td>".
				"<td>$invite[status]</td>".
				"</tr>\n";
		}
	} else {

		if($deletelist = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}invites WHERE invitecode IN ($deletelist)");
		}

		header("Location: {$boardurl}admincp.php?action=invitelog");
	}

}
?>
</table><?=$multipage?>
<?
if($action == 'invitelog') {
	echo '<br/><center><input class="button" type="submit" name="invitesubmit" value="'.$lang['submit'].'"></form></center>';
}
?>
