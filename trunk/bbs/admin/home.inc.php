<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(@file_exists(DISCUZ_ROOT.'./install.php')) {
	@unlink(DISCUZ_ROOT.'./install.php');
	if(@file_exists(DISCUZ_ROOT.'./install.php')) {
		dexit('Please delete install.php via FTP!');
	}
}

@include_once DISCUZ_ROOT.'./discuz_version.php';
require_once DISCUZ_ROOT.'./include/attachment.func.php';

$siteuniqueid = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='siteuniqueid'");
if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$discuz_user.$timestamp), 0, 4).random(6);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('siteuniqueid', '$siteuniqueid')");
}

if(empty($_DCACHE['settings']['authkey']) || strlen($_DCACHE['settings']['authkey']) < 16) {
	$authkey = $_DCACHE['settings']['authkey'] = substr(md5($siteuniqueid.$bbname.$timestamp), 8, 8).random(8);
	$db->query("REPLACE INTO {$tablepre}settings SET variable='authkey', value='$authkey'");
	updatesettings();
}

$onlines = array();
$query = $db->query("SELECT a.*, m.username, m.adminid, m.regip
	FROM {$tablepre}adminsessions a
	LEFT JOIN {$tablepre}members m USING(uid) ORDER BY a.errorcount");

while($member = $db->fetch_array($query)) {
	$memlink = '<a href="space.php?action=viewpro&uid='.$member['uid'].'" target="_blank" alt="'.
		"$lang[time]: ".gmdate("$dateformat $timeformat", $member['dateline'] + $timeoffset * 3600)."\n".
		($member['errorcount'] == -1 ? '' : "$lang[home_onlines_errors]: $member[errorcount]\n").
		($allowviewip && ($adminid <= $member['adminid'] || $member['adminid'] <= 0) ? "$lang[home_online_regip]: ".
		"$member[regip]\n$lang[home_onlines_ip]: $member[ip]" : '').'">'.
		$member['username'].'</a>';
	$onlines[] = $member['errorcount'] == -1 ? $memlink : "<i>$memlink</i>";
}

if(submitcheck('notesubmit')) {
	if(is_array($delete)) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id IN ('".implode('\',\'', $delete)."') AND (admin='$discuz_user' OR adminid>='$adminid')");
	}
	if($newmessage) {
		$newaccess[$adminid] = 1;
		$newaccess = bindec(intval($newaccess[1]).intval($newaccess[2]).intval($newaccess[3]));
		$newexpiration = strtotime($newexpiration) - $timeoffset * 3600 + date('Z');
		$newmessage = nl2br(dhtmlspecialchars($newmessage));
		$db->query("INSERT INTO {$tablepre}adminnotes (admin, access, adminid, dateline, expiration, message)
			VALUES ('$discuz_user', '$newaccess', '$adminid', '$timestamp', '$newexpiration', '$newmessage')");
	}
}

switch($adminid) {
	case 1: $access = '4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}

$notes = '';
$query = $db->query("SELECT * FROM {$tablepre}adminnotes WHERE access IN ($access) ORDER BY dateline DESC");
while($note = $db->fetch_array($query)) {
	if($note['expiration'] < $timestamp) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id='$note[id]'");
	} else {
		$note['adminenc'] = rawurlencode($note['admin']);
		$note['dateline'] = gmdate("$dateformat $timeformat", $note['dateline'] + $timeoffset * 3600);
		$note['expiration'] = gmdate($dateformat, $note['expiration'] + $timeoffset * 3600);
		$note['access'] = sprintf('%03b', $note['access']);
		$notes .= "<tr class=\"smalltxt\"><td class=\"altbg1\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" ".($note['admin'] == $discuz_userss || $note['adminid'] >= $adminid ? "value=\"$note[id]\"" : 'disabled')."></td>\n".
			"<td class=\"altbg2\" align=\"center\"><a href=\"space.php?action=viewpro&username=$note[adminenc]\" target=\"_blank\">$note[admin]</a></td>\n".
			"<td class=\"altbg1\" align=\"center\">$note[dateline]</td>\n".
			"<td class=\"altbg2\"><b>$note[message]</b></td>\n".
			"<td class=\"altbg1\" align=\"center\">".($note['access'][0] ? $lang['yes'] : '')."</td>\n".
			"<td class=\"altbg2\" align=\"center\">".($note['access'][1] ? $lang['yes'] : '')."</td>\n".
			"<td class=\"altbg1\" align=\"center\">".($note['access'][2] ? $lang['yes'] : '')."</td>\n".
			"<td class=\"altbg2\" align=\"center\">$note[expiration]</td></tr>\n";
	}
}

if($adminid == 1) {

	require_once DISCUZ_ROOT.'./include/forum.func.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
	$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
	$dbversion = $db->result_first("SELECT VERSION()");

	if(@ini_get('file_uploads')) {
		$fileupload = ini_get('upload_max_filesize');
	} else {
		$fileupload = '<font color="red">'.$lang['no'].'</font>';
	}

	$groupselect = '';
	$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups ORDER BY creditslower, groupid");
	while($group = $db->fetch_array($query)) {
		$groupselect .= '<option value="'.$group['groupid'].'">'.$group['grouptitle'].'</option>';
	}

	$dbsize = 0;
	$query = $db->query("SHOW TABLE STATUS LIKE '$tablepre%'", 'SILENT');
	while($table = $db->fetch_array($query)) {
		$dbsize += $table['Data_length'] + $table['Index_length'];
	}
	$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

	if(isset($attachsize)) {
		$attachsize = $db->result_first("SELECT SUM(filesize) FROM {$tablepre}attachments");
		$attachsize = is_numeric($attachsize) ? sizecount($attachsize) : $lang['unknown'];
	} else {
		$attachsize = '<a href="admincp.php?action=home&attachsize">[ '.$lang['detail'].' ]</a>';
	}

	$postsmod = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE first='0' AND invisible='-2'");

	$threadsdel = $threadsmod = 0;
	$query = $db->query("SELECT displayorder FROM {$tablepre}threads WHERE displayorder<'0'");
	while($thread = $db->fetch_array($query)) {
		if($thread['displayorder'] == -1) {
			$threadsdel++;
		} elseif($thread['displayorder'] == -2) {
			$threadsmod++;
		}
	}

} elseif($allowmodpost) {

	if($adminid == 3) {
		$fids = '0';
		$query = $db->query("SELECT fid FROM {$tablepre}moderators WHERE uid='$discuz_uid'");
		while($forum = $db->fetch_array($query)) {
			$fids .= ','.$forum['fid'];
		}
		if($fids) {
			$fidadd = "fid IN ($fids) AND";
		} else {
			$fidadd = '';
			$allowmodpost = 0;
		}
	}

	$threadsmod = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE $fidadd displayorder='-2'");
	$postsmod = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE $fidadd first='0' AND invisible='-2'");

}

cpheader();
shownav('header_admin');

$save_mastermobile = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='mastermobile'");
$save_mastermobile = !empty($save_mastermobile) ? authcode($save_mastermobile, 'DECODE', $authkey) : '';

$securityadvise = '';
$securityadvise .= !$discuz_secques ? $lang['home_secques_invalid'] : '';
$securityadvise .= ($adminid == 1 && $groupid == 1 && empty($forumfounders)) ? $lang['home_security_nofounder'] : '';
$securityadvise .= ($adminid == 1 && $groupid == 1 && $admincp['tpledit']) ? $lang['home_security_tpledit'] : '';
$securityadvise .= ($adminid == 1 && $groupid == 1 && $admincp['runquery']) ? $lang['home_security_runquery'] : '';

$userformhash = FORMHASH;
if(isfounder() && submitcheck('securyservice')) {
	$new_mastermobile = trim($new_mastermobile);
	if(empty($new_mastermobile)) {
		$save_mastermobile = $new_mastermobile;
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('mastermobile', '$new_mastermobile')");
	} elseif($save_mastermobile != $new_mastermobile && strlen($new_mastermobile) == 11 && is_numeric($new_mastermobile) && (substr($new_mastermobile, 0, 2) == '13' || substr($new_mastermobile, 0, 2) == '15')) {
		$save_mastermobile = $new_mastermobile;
		$new_mastermobile = authcode($new_mastermobile, 'ENCODE', $authkey);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('mastermobile', '$new_mastermobile')");
	}
}

$view_mastermobile = !empty($save_mastermobile) ? substr($save_mastermobile, 0 , 3).'*****'.substr($save_mastermobile, -3) : '';

echo <<<EOT
<form method="post" action="admincp.php?action=home">
<input type="hidden" name="formhash" value="$userformhash">
<input type="hidden" name="securyservice" value="yes">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3">$lang[home_security_tips]</td></tr>
EOT;

if(isfounder()) {
	$hidemobile = '<input type="text" class="altbg1" value="'.(!empty($view_mastermobile) ? $lang['home_security_service_mobile_save'] : $lang['home_security_service_mobile_none']).'" disabled="true" style="border:0" size="50">';
	echo <<<EOT
	<tr class="altbg1">
		<td>$lang[home_security_service]</td>
		<td colspan=2>$lang[home_security_service_info]</td>
	</tr>
	<tr class="altbg2">
		<td width="20%">$lang[home_security_service_mobile]</td>
		<td><input type="text" size="30" class="input" name='new_mastermobile' value='$view_mastermobile'> &nbsp;$hidemobile</td>
		<td width="20%"><input type="submit" class="button" name="securyservice" value="$lang[submit]"></td>
	</tr>

EOT;
}

if($securityadvise) {
	echo '<tr class="altbg1"><td width="20%">'.$lang['home_security_advise'].'</td><td colspan=2>'.$securityadvise.'</td></tr>';
}
?>
</table></form><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['home_onlines']?></td></tr>
<tr><td><?=implode(', ', $onlines)?></td></tr>
</table><br />
<? if($isfounder) {

	$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
	if(empty($insenz['authkey']) && $onlineip != '127.0.0.1') {
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
			<tr class="header"><td>'.$lang['insenz_note'].'</td></tr>
			<tr><td><b>'.$lang['insenz_note_join_insenz'].'</b><a href="admincp.php?action=insenz"><font color="red"><u>'.$lang['insenz_note_register'].'</u></font></a></td></tr>
			</table><br />';
	} elseif($insenz['status']) {
		require_once DISCUZ_ROOT.'./include/insenz.func.php';
?>

<div id="insenznews"></div>

<? }} ?>

<div id="boardnews"></div>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['home_stuff']?></td></tr>

<? if(($adminid == 2 || $adminid == 3) && ($allowedituser || $allowbanuser)) { ?>

<form method="post" action="admincp.php?action=editmember&searchsubmit=yes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg1"><td><?=$lang['home_edit_member']?></td>
<td><input type="text" size="30" name="username"></td><td><input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></td></tr></form>

<? } ?>

<? if($adminid == 1) { ?>

<form method="post" action="admincp.php?action=members&searchsubmit=yes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg1"><td><?=$lang['home_edit_member']?></td>
<td><input type="text" size="30" name="username"></td><td><input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=forumdetail">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td><?=$lang['home_edit_forum']?></td>
<td><select name="fid"><option value="">&nbsp;&nbsp;> <?=$lang['select']?></option><option value="">&nbsp;</option>
<?=forumselect(1)?></select></td><td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=usergroups">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg1"><td><?=$lang['home_edit_group']?></td>
<td><select name="edit"><?=$groupselect?></td><td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<? } ?>

</table>
<br />
<form method="post" action="admincp.php?action=home">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="5%"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form, 'delete');"><?=$lang['del']?></td>
<td width="12%"><?=$lang['username']?></td>
<td width="12%"><?=$lang['time']?></td>
<td width="30%"><?=$lang['announce_home_options']?></td>
<td width="8%" nowrap><?=$lang['usergroups_system_1']?></td>
<td width="8%" nowrap><?=$lang['usergroups_system_2']?></td>
<td width="8%" nowrap><?=$lang['usergroups_system_3']?></td>
<td width="15%"><?=$lang['validity']?></td></tr>
<?=$notes?>
<tr align="center"><td class="altbg1"><?=$lang['add_new']?></td>
<td class="altbg2" colspan="3"><textarea name="newmessage" rows="2" style="width: 95%; word-break: break-all"></textarea></td>
<td class="altbg1"><input class="checkbox" type="checkbox" name="newaccess[1]" value="1" checked <?=($adminid == 1 ? 'disabled' : '')?>></td>
<td class="altbg2"><input class="checkbox" type="checkbox" name="newaccess[2]" value="1" checked <?=($adminid == 2 ? 'disabled' : '')?>></td>
<td class="altbg1"><input class="checkbox" type="checkbox" name="newaccess[3]" value="1" checked <?=($adminid == 3 ? 'disabled' : '')?>></td>
<td class="altbg2"><input type="text" name="newexpiration" size="8" value="<?=gmdate('Y-n-j', $timestamp + $timeoffset * 3600 + 86400 * 30)?>">
<input class="button" type="submit" name="notesubmit" value="<?=$lang['submit']?>"></td></tr>
</table>
<br />

<? if((($threadsmod || $postsmod) && $allowmodpost) || ($threadsdel && $adminid == 1)) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['home_threads_posts']?></td></tr>
<?

$thisbg = 'altbg1';
if($allowmodpost) {
	if($threadsmod) {
		$thisbg = $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';
		echo "<tr class=\"$thisbg\" style=\"font-weight: bold\"><td width=\"25%\"><a href=\"admincp.php?action=modthreads\">$lang[home_mod_threads]</a></td><td>$threadsmod</td></tr>\n";
	}
	if($postsmod) {
		$thisbg = $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';
		echo "<tr class=\"$thisbg\" style=\"font-weight: bold\"><td width=\"25%\"><a href=\"admincp.php?action=modreplies\">$lang[home_mod_posts]</a></td><td>$postsmod</td></tr>\n";
	}
}
if($threadsdel && $adminid == 1) {
	$thisbg = $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';
	echo "<tr class=\"$thisbg\" style=\"font-weight: bold\"><td width=\"25%\"><a href=\"admincp.php?action=recyclebin\">$lang[home_delete_threads]</td><td>$threadsdel</td></tr>\n";
}

?>

</table>
<br />

<? } if($adminid == 1) { ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['home_sys_info']?></td></tr>
<tr class="altbg2"><td width="25%"><?=$lang['home_discuz_version']?></td><td>Discuz! <?=DISCUZ_VERSION?> Release <?=DISCUZ_RELEASE?> [ <a href="http://www.discuz.net/forumdisplay.php?fid=10" target="_blank"><?=$lang['home_check_newversion']?></a> ]</td></tr>
<tr class="altbg1"><td width="25%"><?=$lang['home_environment']?></td><td><?=$serverinfo?></td></tr>
<tr class="altbg2"><td><?=$lang['home_database']?></td><td><?=$dbversion?></td></tr>
<tr class="altbg1"><td><?=$lang['home_upload_perm']?></td><td><?=$fileupload?></td></tr>
<tr class="altbg2"><td><?=$lang['home_database_size']?></td><td><?=$dbsize?></td></tr>
<tr class="altbg1"><td><?=$lang['home_attach_size']?></td><td><?=$attachsize?></td></tr>
<tr class="altbg2"><td><?=$lang['menu_tools_fileperms']?></td><td><a href='admincp.php?action=fileperms'>[ <?=$lang['check']?> ]</a></td></tr>
</table>
<br />

<? } ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['home_dev']?></td></tr>
<tr class="altbg2"><td width="25%"><?=$lang['home_dev_copyright']?></td><td class="smalltxt"><a href="http://www.comsenz.com" target="_blank">&#x5eb7;&#x76db;&#x521b;&#x60f3;(&#x5317;&#x4eac;)&#x79d1;&#x6280;&#x6709;&#x9650;&#x516c;&#x53f8; (Comsenz Inc.)</a></td></tr>
<tr class="altbg1"><td><?=$lang['home_dev_manager']?></td><td class="smalltxt"><a href="http://www.discuz.net/space.php?uid=1" target="_blank">&#x6234;&#x5FD7;&#x5EB7; (Kevin 'Crossday' Day)</a></td></tr>
<tr class="altbg2"><td><?=$lang['home_dev_team']?></td><td class="smalltxt"><a href="http://www.discuz.net/space.php?uid=2691" target="_blank">Liang 'Readme' Chen</a>, <a href="http://www.discuz.net/space.php?uid=1519" target="_blank">Yang 'Summer' Xia</a>, <a href="http://www.discuz.net/space.php?uid=859" target="_blank">Haibo 'cnteacher' Wang</a>, <a href="http://www.discuz.net/space.php?uid=16678" target="_blank">Yang 'Tiger' Song</a>, <a href="http://www.discuz.net/space.php?uid=10407" target="_blank">Qiang Liu</a>, <a href="http://www.discuz.net/space.php?uid=80629" target="_blank">Ning 'Monkey' Hou</a>, <a href="http://www.discuz.net/space.php?uid=122246" target="_blank">Min 'Heyond' Huang</a>, <a href="http://www.discuz.net/space.php?uid=22195" target="_blank">Chao 'Langwan' Luo</a>, <a href="http://www.discuz.net/space.php?uid=210272" target="_blank">XiaoDun 'Kenshine' Fang</a>,  <a href="http://www.discuz.net/space.php?uid=492114" target="_blank">Liang 'Metthew' Xu</a></td></tr>
<tr class="altbg1"><td><?=$lang['home_dev_addons']?></td><td class="smalltxt"><a href="http://www.discuz.net/space.php?uid=9600" target="_blank">theoldmemory</a>, <a href="http://www.discuz.net/space.php?uid=2629" target="_blank">rain5017</a>, <a href="http://www.discuz.net/space.php?uid=26926" target="_blank">Snow Wolf</a>, <a href="http://www.discuz.net/space.php?uid=17149" target="_blank">hehechuan</a>, <a href="http://www.discuz.net/space.php?uid=9132" target="_blank">pk0909</a>, <a href="http://www.discuz.net/space.php?uid=248" target="_blank">feixin</a>, <a href="http://www.discuz.net/space.php?uid=675" target="_blank">Laobing Jiuba</a>, <a href="http://www.discuz.net/space.php?uid=7155" target="_blank">Gregry</a>, <a href="http://www.discuzsupport.net/" target="_blank">Discuz! Support Team</a></td></tr>
<tr class="altbg2"><td><?=$lang['home_dev_skins']?></td><td class="smalltxt"><a href="http://www.discuz.net/space.php?uid=13877" target="_blank">Artery</a>, <a href="http://www.discuz.net/space.php?uid=233" target="_blank">Huli Hutu</a>, <a href="http://www.discuz.net/space.php?uid=122" target="_blank">Lao Gui</a>, <a href="http://www.discuz.net/space.php?uid=159" target="_blank">Tyc</a>, <a href="http://www.discuz.net/space.php?uid=177" target="_blank">stoneage</a>, <a href="http://www.discuz.net/space.php?uid=294092" target="_blank">Fangming 'Lushnis' Li</a>, <a href="http://www.discuz.net/space.php?uid=362790" target="_blank">Defeng 'Dfox' Xu</a></td></tr>
<tr class="altbg1"><td><?=$lang['home_dev_enterprise_site']?></td><td class="smalltxt"><a href="http://www.comsenz.com" target="_blank">http://www.Comsenz.com</a></td></tr>
<tr class="altbg2"><td><?=$lang['home_dev_project_site']?></td><td class="smalltxt"><a href="http://www.discuz.com" target="_blank">http://www.Discuz.com</a></td></tr>
<tr class="altbg1"><td><?=$lang['home_dev_community']?></td><td class="smalltxt"><a href="http://www.discuz.net" target="_blank">http://www.Discuz.net</a></td></tr>
</table>
<br />
