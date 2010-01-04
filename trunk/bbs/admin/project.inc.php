<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: project.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

if(!isfounder()) {
	cpheader();
	cpmsg('noaccess');
}

$query = $db->query("SELECT disabledactions FROM {$tablepre}adminactions WHERE admingid='$groupid'");
$dactionarray = ($dactionarray = unserialize($db->result($query, 0))) ? $dactionarray : array();
$allowforumsedit = !in_array('forumsedit', $dactionarray) ? 1 : 0;
$allowusergroups = !in_array('usergroups', $dactionarray) ? 1 : 0;
$allowcreditwizard = !in_array('creditwizard', $dactionarray) ? 1 : 0;

if(empty($allowforumsedit) && empty($allowusergroups) && empty($allowcreditwizard)) {
	cpheader();
	cpmsg('action_noaccess');
}

if($action == 'project' && $export) {

	$projectarray = $db->fetch_first("SELECT * FROM {$tablepre}projects WHERE id='$export'");
	if(!$projectarray) {
		cpheader();
		cpmsg('undefined_action');
	}

	if(($projectarray['type'] == 'forum' && empty($allowforumsedit)) || ($projectarray['type'] == 'group' && empty($allowusergroups)) || ($projectarray['type'] == 'extcredit' && empty($allowcreditwizard))) {
		cpheader();
		cpmsg('action_noaccess');
	}

	$projectarray['version'] = strip_tags($version);
	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$project_export = "# Discuz! Project Dump ($projectarray[type])\n".
		"# Version: Discuz! $version\n".
		"# Time: $time  \n".
		"# From: $bbname ($boardurl) \n".
		"#\n".
		"# Discuz! Community: http://www.Discuz.net\n".
		"# Please visit our website for latest news about Discuz!\n".
		"# --------------------------------------------------------\n\n\n".
		wordwrap(base64_encode(serialize($projectarray)), 60, "\n", 1);

	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($project_export));
	dheader('Content-Disposition: attachment; filename=discuz_project_'.$projectarray['type'].'_'.$projectarray['name'].'.txt');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));

	echo $project_export;
	dexit();

}

cpheader();

if($action == 'project') {

	if(!submitcheck('projectsubmit') && !submitcheck('importsubmit')) {

		$listarray = array();
		$projectlist = $typeadd = $selecttype = '';
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * 10;

		$allowaction = array(
			'forum' => $allowforumsedit,
			'group' => $allowusergroups,
			'extcredit' => $allowcreditwizard,
		);

		if(!empty($type) && in_array($type, array('forum', 'group', 'extcredit'))) {

			foreach($allowaction as $key => $val) {
				if($type == $key && empty($val)) {
					cpmsg('action_noaccess');
				}
			}

			$typeadd = "WHERE type='$type'";
			$selecttype = '&amp;type='.$type;

		} else {

			$typeadd = $comma = '';
			foreach($allowaction as $key => $val) {
				if(!empty($val)) {
					$typeadd .= $comma."'$key'";
					$comma = ', ';
				}
			}
			$typeadd = 'WHERE type IN ('.$typeadd.')';

		}

		$projectnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}projects $typeadd");

		$query = $db->query("SELECT id, name, type, description FROM {$tablepre}projects $typeadd LIMIT $start_limit, 10");
		while($list = $db->fetch_array($query)) {
			$list['name'] = dhtmlspecialchars($list['name']);
			$list['description'] = dhtmlspecialchars($list['description']);
			$type = 'project_'.$list['type'].'_scheme';
			$projectlist .= "<tr align=\"center\">\n".
					"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$list[id]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"name[$list[id]]\" value=\"$list[name]\"></td>\n".
					"<td class=\"altbg2\">$lang[$type]</td>\n".
					"<td class=\"altbg2\"><input type=\"textarea\" size=\"40\" name=\"description[$list[id]]\" value=\"$list[description]\"></td>\n".
					"<td class=\"altbg2\">[<a href=\"admincp.php?action=project&amp;export=$list[id]\">".$lang['download']."</a>]</td>\n".
					"<td class=\"altbg2\">[<a href=\"admincp.php?action=projectapply&amp;projectid=$list[id]&amp;type=$list[type]\">".$lang['apply']."</a>]</td></tr>\n";
		}

		$multipage = multi($projectnum, 10, $page, "admincp.php?action=project$selecttype");
		shownav('project_scheme');

?>
<form method="post" action="admincp.php?action=project">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><?=$lang['project_forum_scheme_sort']?></td></tr>
<tr><td>
<?
if($allowforumsedit) {
	echo '<input class="button" type="button" value="'.$lang['project_forum_scheme'].'" onclick="window.location=\'admincp.php?action=project&amp;type=forum\';"> &nbsp;';
}
if($allowusergroups) {
	echo '<input class="button" type="button" value="'.$lang['project_group_scheme'].'" onclick="window.location=\'admincp.php?action=project&amp;type=group\';"> &nbsp;';
}
if($allowcreditwizard) {
	echo '<input class="button" type="button" value="'.$lang['project_extcredit_scheme'].'" onclick="window.location=\'admincp.php?action=project&amp;type=extcredit\';"> &nbsp;';
}
?>
</td></tr>
</table><br />
<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="5%"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td width="20%"><?=$lang['name']?></td><td width="15%"><?=$lang['type']?></td><td width="40%"><?=$lang['description']?></td><td width="10%"><?=$lang['export']?></td><td width="10%"><?=$lang['detail']?></td></tr>
<?=$projectlist?></table>
<?=$multipage?><br />
<center><input class="button" type="submit" name="projectsubmit" value="<?=$lang['submit']?>"></center><br />

<form method="post" action="admincp.php?action=project">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['project_import_stick']?></td></tr>
<tr><td class="altbg1">	<div align="center"><textarea  name="projectdata" cols="80" rows="8"></textarea><br /></td></tr>
</table><br />
<center><input class="button" type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} elseif(submitcheck('projectsubmit')) {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM {$tablepre}projects WHERE id IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}projects SET name='$name[$id]', description='$description[$id]' WHERE id='$id'");
			}
		}

		cpmsg('project_update_forum', 'admincp.php?action=project');

	} elseif(submitcheck('importsubmit')) {

		$projectdata = preg_replace("/(#.*\s+)*/", '', $projectdata);
		$projectarray = daddslashes(unserialize(base64_decode($projectdata)), 1);

		if(!is_array($projectarray)) {
			cpmsg('project_import_data_invalid');
		} elseif(strip_tags($projectarray['version']) != strip_tags($version)) {
			cpmsg('project_export_version');
		} else {
			$db->query("INSERT INTO {$tablepre}projects (name, type, description, value) VALUES ('$projectarray[name]', '$projectarray[type]', '$projectarray[description]', '$projectarray[value]')");
			cpmsg('project_import_succeed', 'admincp.php?action=project');
		}

	}

} elseif($action == 'projectadd') {

	$delfields = array
		(
		'forum'	=> array('fid', 'fup', 'type', 'name', 'status', 'displayorder', 'threads', 'posts', 'todayposts', 'lastpost', 'description', 'password', 'icon', 'redirect', 'moderators', 'rules', 'threadtypes'),
		'group'	=> array('groupid', 'radminid', 'type', 'system', 'grouptitle', 'creditshigher', 'creditslower', 'stars', 'color', 'groupavatar')
		);

	if(!submitcheck('addsubmit')) {
		shownav('project_scheme_add');

		if(!empty($projectid)) {
			$query = $db->query("SELECT name, description, value FROM {$tablepre}projects WHERE id='$projectid'");
			$project = $db->fetch_array($query);
		}

		if(($type == 'forum' && empty($allowforumsedit)) || ($type == 'group' && empty($allowusergroups)) || ($type == 'extcredit' && empty($allowcreditwizard))) {
			cpmsg('action_noaccess');
		}

		$allselected = 'selected';
		if($type == 'forum' || $type == 'group') {
			$listoption = '';
			$fieldarray = $type == 'forum' ? array_merge(fetch_table_struct('forums'), fetch_table_struct('forumfields')) : fetch_table_struct('usergroups');
			$listfields = array_diff($fieldarray, $delfields[$type]);
			foreach($listfields as $field) {
				$listoption .= '<option value="'.$field.'">'.$lang['project_option_'.$type.'_'.$field].'</option>';
			}
		} elseif($type == 'extcredit') {
			$value = unserialize($project['value']);
			$savemethod = $value['savemethod'];
			$allselected = '';
			$listoption = '<option value="1"'.(@in_array(1, $savemethod) ? ' selected': '').'>'.$lang['project_credits_item_config'].'</option>';
			$listoption .= '<option value="2"'.(@in_array(2, $savemethod) ? ' selected': '').'>'.$lang['project_credits_rule_config'].'</option>';
			$listoption .= '<option value="3"'.(@in_array(3, $savemethod) ? ' selected': '').'>'.$lang['project_credits_use_config'].'</option>';
		}

?>
<form method="post" action="admincp.php?action=projectadd&id=<?=$id?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="projectid" value="<?=$projectid?>">
<input type="hidden" name="type" value="<?=$type?>">
<input type="hidden" name="detailsubmit" value="submit">
<?

		showtype("project_scheme_save", 'top');

		if(!empty($projectid)) {
			showsetting('project_scheme_cover', 'coverwith', '', 'radio');
		}

		showsetting('project_scheme_option', '', '', '<select name="fieldoption[]" size="10" multiple="multiple" style="width: 80%"><option value="all" '.$allselected.'>'.$lang['all'].'</option>'.$listoption.'</select>');

		showsetting('project_scheme_title', 'name', $project['name'], 'text');
		showsetting('project_scheme_description', 'description', $project['description'], 'textarea');
		showtype('', 'bottom');

		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"addsubmit\" value=\"$lang[submit]\">";

	} else {

		$type = !empty($type) && in_array($type, array('forum', 'group', 'extcredit')) ? $type : '';

		if(empty($name)) {
			cpmsg('project_no_title');
		}

		if($type == 'forum') {
			$value = $db->fetch_first("SELECT f.*, ff.* FROM {$tablepre}forums f
				LEFT JOIN {$tablepre}forumfields ff USING (fid)
				WHERE f.fid='$id'");
			if(!$value) {
				cpmsg('forums_nonexistence');
			}

		} elseif($type == 'group') {
			$value = $db->fetch_first("SELECT * FROM {$tablepre}usergroups WHERE groupid='$id'");
			if(!$value) {
				cpmsg('project_no_usergroup');
			}
		} elseif($type == 'extcredit') {
			if(empty($fieldoption)) {
				cpmsg('project_no_item');
			}
			$delfields = array();
			$fieldoption = in_array('all', $fieldoption) ? array(1, 2, 3) : $fieldoption;
			$variables = in_array(1, $fieldoption) ? ", 'extcredits', 'creditspolicy'" : '';
			$variables .= in_array(2, $fieldoption) ? ", 'creditsformula'" : '';
			$variables .= in_array(3, $fieldoption) ? ", 'creditstrans', 'creditstax', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan'" : '';

			$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable IN (''$variables)");
			$value['savemethod'] = $fieldoption;
			while($data = $db->fetch_array($query)) {
				$value[$data['variable']] = $data['value'];
			}
		}

		if($type == 'forum' || $type == 'group') {
			if(in_array('all', $fieldoption)) {
				foreach($delfields[$type] as $field) {
					unset($value[$field]);
				}
			} else {
				$selectlist = '';
				foreach($value as $key => $val) {
					if(in_array($key, $fieldoption)) {
						$selectlist[$key] .= $val;
					}
				}
				$value = $selectlist;
			}
		}

		$value = !empty($value) ? addslashes(serialize($value)) : '';

		if(!empty($projectid) && !empty($coverwith)) {
			$db->query("UPDATE {$tablepre}projects SET name='$name', description='$description', value='$value' WHERE id='$projectid'");
		} else {
			$db->query("INSERT INTO {$tablepre}projects (name, type, description, value) VALUES ('$name', '$type', '$description', '$value')");
		}

		if($type == 'forum') {
			cpmsg('project_sava_succeed', 'admincp.php?action=forumdetail&fid='.$id);
		} elseif($type == 'group') {
			cpmsg('project_sava_succeed', 'admincp.php?action=usergroups&edit='.$id);
		} elseif($type == 'extcredit') {
			cpmsg('project_sava_succeed', 'admincp.php?action=settings&do=credits');
		}

	}

} elseif($action == 'projectapply') {

	$type = !empty($type) && in_array($type, array('forum', 'group', 'extcredit')) ? $type : 'forum';

	if(($type == 'forum' && empty($allowforumsedit)) || ($type == 'group' && empty($allowusergroups)) || ($type == 'extcredit' && empty($allowcreditwizard))) {
		cpmsg('action_noaccess');
	}

	$projectselect = "<select name=\"projectid\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
	$query = $db->query("SELECT id, name, type FROM {$tablepre}projects WHERE type='$type'");
	while($project = $db->fetch_array($query)) {
		$projectselect .= "<option value=\"$project[id]\" ".($project['id'] == $projectid ? 'selected="selected"' : NULL).">$project[name]</option>\n";
	}
	$projectselect .= '</select>';

	if(!submitcheck('applysubmit')) {

		if($type == 'forum') {

			require_once DISCUZ_ROOT.'./include/forum.func.php';
			$forumselect = '<select name="target[]" size="10" multiple="multiple" style="width: 80%">'.forumselect().'</select>';

		} elseif($type == 'group') {

			$groupselect = '<select name="target[]" size="10" multiple="multiple" style="width: 80%">';
			$query = $db->query("SELECT groupid, type, grouptitle, creditshigher, creditslower, stars, color, groupavatar FROM {$tablepre}usergroups ORDER BY creditshigher");
			while($group = $db->fetch_array($query)) {
				$groupselect .= '<option value="'.$group['groupid'].'">'.$group['grouptitle'].'</option>';
			}
			$groupselect .= '</select>';

		} elseif($type == 'extcredit') {

			dheader('location:admincp.php?action=settings&do=credits&projectid='.$projectid);

		}

		shownav('project_global_forum');

?>
<form method="post" action="admincp.php?action=projectapply">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="projectid" value="<?=$projectid?>">
<?

		showtype('project_scheme_forum', 'top');
		showsetting('project_scheme_title', '', '', $projectselect);
		if($type == 'forum') {
			showsetting('forums_copy_target', '', '', $forumselect);
		} elseif($type == 'group') {
			showsetting('project_target_usergroup', '', '', $groupselect);
		}

		showtype('', 'bottom');
		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"applysubmit\" value=\"$lang[submit]\"></form>";

	} else {

		if(empty($target)) {
			cpmsg('project_target_item');
		}

		$applyids = implodeids($target);

		$project = $db->fetch_first("SELECT type, value FROM {$tablepre}projects WHERE id='$projectid'");
		if(!$project) {
			cpmsg('project_no_scheme');
		}

		if(!$value = unserialize($project['value'])) {
			cpmsg('project_invalid');
		}

		if($project['type'] == 'forum') {

			$table_forum_columns = array('styleid', 'allowsmilies', 'allowhtml', 'allowbbcode', 'allowimgcode', 'allowanonymous', 'allowshare', 'allowpostspecial', 'alloweditrules', 'allowpaytoauthor', 'alloweditpost', 'allowspecialonly', 'modnewposts', 'recyclebin', 'jammer', 'forumcolumns', 'threadcaches', 'disablewatermark', 'autoclose', 'simple');
			$table_forumfield_columns = array('attachextensions', 'postcredits', 'replycredits', 'digestcredits', 'postattachcredits', 'getattachcredits', 'viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm', 'modrecommend', 'formulaperm');

			$updatesql = $comma = '';
			foreach($table_forum_columns as $field) {
				if(isset($value[$field])) {
					$updatesql .= "$comma$field='".addslashes($value[$field])."'";
					$comma = ', ';
				}
			}

			if($updatesql) {
				$db->query("UPDATE {$tablepre}forums SET $updatesql WHERE fid IN ($applyids)");
			}

			$updatesql = $comma = '';
			foreach($table_forumfield_columns as $field) {
				if(isset($value[$field])) {
					$updatesql .= "$comma$field='".addslashes($value[$field])."'";
					$comma = ', ';
				}
			}

			if($updatesql) {
				$db->query("UPDATE {$tablepre}forumfields SET $updatesql WHERE fid IN ($applyids)");
			}

		} elseif($project['type'] == 'group') {

			$usergroup_columns = array('readaccess', 'allowvisit', 'allowpost', 'allowreply', 'allowpostpoll', 'allowpostreward', 'allowposttrade', 'allowpostactivity', 'allowpostvideo', 'allowdirectpost', 'allowgetattach', 'allowpostattach', 'allowvote', 'allowmultigroups', 'allowsearch', 'allowavatar', 'allowcstatus', 'allowuseblog', 'allowinvisible', 'allowtransfer', 'allowsetreadperm', 'allowsetattachperm', 'allowhidecode', 'allowhtml', 'allowcusbbcode', 'allowanonymous', 'allownickname', 'allowsigbbcode', 'allowsigimgcode', 'allowviewpro', 'allowviewstats', 'disableperiodctrl', 'reasonpm', 'maxprice', 'maxpmnum', 'maxsigsize', 'maxattachsize', 'maxsizeperday', 'maxpostsperhour', 'attachextensions', 'raterange', 'mintradeprice', 'maxtradeprice', 'minrewardprice', 'maxrewardprice', 'magicsdiscount', 'allowmagics', 'maxmagicsweight', 'allowbiobbcode', 'allowbioimgcode', 'maxbiosize', 'maxbiotradesize', 'allowinvite', 'allowmailinvite', 'inviteprice', 'maxinvitenum', 'maxinviteday');

			$updatesql = $comma = '';
			foreach($usergroup_columns as $field) {
				if(isset($value[$field])) {
					$updatesql .= "$comma$field='".addslashes($value[$field])."'";
					$comma = ', ';
				}
			}

			if($updatesql) {
				$db->query("UPDATE {$tablepre}usergroups SET $updatesql WHERE groupid IN ($applyids)");
			}
		}

		cpmsg('project_scheme_succeed');

	}
}

?>