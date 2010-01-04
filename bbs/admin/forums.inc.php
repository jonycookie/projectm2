<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forums.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'forumadd')  {

	if((!submitcheck('catsubmit') && !submitcheck('forumsubmit'))) {
		$addforumtype = '';
		$groupselect = $forumselect = "<select name=\"fup\">\n";
		$query = $db->query("SELECT fid, name, type, fup FROM {$tablepre}forums WHERE type<>'sub' ORDER BY displayorder");
		while($fup = $db->fetch_array($query)) {
			$fups[] = $fup;
		}
		if(is_array($fups)) {
			foreach($fups as $forum1) {
				if($forum1['type'] == 'group') {
					if(isset($fupid) && $fupid == $forum1['fid']) {
						$fupselected = 'selected';
						$addforumtype = 'group';
					} else {
						$fupselected = '';
					}

					$groupselect .= "<option value=\"$forum1[fid]\" $fupselected>$forum1[name]</option>\n";
					$forumselect .= "<optgroup label=\"$forum1[name]\">\n";
					foreach($fups as $forum2) {
						if($forum2['type'] == 'forum' && $forum2['fup'] == $forum1['fid']) {
							if(isset($fupid) && $fupid == $forum2['fid']) {
								$fupselected = 'selected';
								$addforumtype = 'forum';
							} else {
								$fupselected = '';
							}

							$forumselect .= "<option value=\"$forum2[fid]\" $fupselected>&nbsp; &gt; $forum2[name]</option>\n";
						}
					}
				}
			}
			foreach($fups as $forum0) {
				if($forum0['type'] == 'forum' && $forum0['fup'] == 0) {
					if(isset($fupid) && $fupid == $forum0['fid']) {
						$fupselected = 'selected';
						$addforumtype = $forum0['type'];
					} else {
						$fupselected = '';
					}
					if($forum0['type'] == 'group') {
						$groupselect .= "<option value=\"$forum0[fid]\" $fupselected>$forum0[name]</option>\n";
					} else {
						$forumselect .= "<option value=\"$forum0[fid]\" $fupselected>$forum0[name]</option>\n";
					}
				}
			}
		}
		$groupselect .= '</select>';
		$forumselect .= '</select>';

		$projectselect = "<select name=\"projectid\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
		$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='forum'");
		while($project = $db->fetch_array($query)) {
			$projectselect .= "<option value=\"$project[id]\">$project[name]</option>\n";
		}
		$projectselect .= '</select>';

		shownav('menu_forums_add');
		showtips('forums_add_tips');

		if(empty($addforumtype)) {

?>
<br /><form method="post" action="admincp.php?action=forumadd&add=category">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['forums_add_category']?></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['name']?>:</td>
<td class="altbg2" width="85%"><input type="text" name="newcat" value="<?=$lang['forums_add_category_name']?>" size="20"></td>
</table><br /><center>
<input class="button" type="submit" name="catsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		}

		if(empty($addforumtype) || $addforumtype == 'group') {

?>
<br /><form method="post" action="admincp.php?action=forumadd&add=forum">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['forums_add_forum']?></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['name']?>:</td>
<td class="altbg2" width="85%"><input type="text" name="newforum" value="<?=$lang['forums_add_forum_name']?>" size="20"></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['forums_add_parent_category']?>:</td>
<td class="altbg2" width="85%"><?=$groupselect?></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['forums_scheme']?></td>
<td class="altbg2" width="85%"><?=$projectselect?></td></tr></table><br />
<center><input class="button" type="submit" name="forumsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		}

		if(empty($addforumtype) || $addforumtype == 'forum') {

?>
<br /><form method="post" action="admincp.php?action=forumadd&add=forum">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['forums_add_sub']?></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['name']?>:</td>
<td class="altbg2" width="28%"><input type="text" name="newforum" value="<?=$lang['forums_add_sub_name']?>" size="20"></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['forums_add_parent_forum']?>:</td>
<td class="altbg2" width="27%"><?=$forumselect?></td></tr>
<tr align="center"><td class="altbg1" width="15%"><?=$lang['forums_scheme']?></td>
<td class="altbg2" width="85%"><?=$projectselect?></td></tr></table><br />
<center><input class="button" type="submit" name="forumsubmit" value="<?=$lang['submit']?>"></center>
</form><br />
<?

		}

	} elseif(submitcheck('catsubmit')) {

		if(strlen($newcat) > 50) {
			cpmsg('forums_name_toolong');
		}

		$db->query("INSERT INTO {$tablepre}forums (type, name, status)
			VALUES ('group', '$newcat', '1')");
		$fid = $db->insert_id();

		$db->query("INSERT INTO {$tablepre}forumfields (fid)
			VALUES ('$fid')");

		updatecache('forums');
		cpmsg('forums_add_category_succeed', 'admincp.php?action=forumsedit');

	} elseif(submitcheck('forumsubmit')) {

		if(strlen($newforum) > 50) {
			cpmsg('forums_name_toolong');
		}

		if(!$fup) {
			cpmsg('forums_noparent');
		}

		$modarray = $forumfields = array();
		$table_forum_columns = array('fup', 'type', 'name', 'status', 'styleid', 'allowsmilies', 'allowhtml', 'allowbbcode', 'allowimgcode', 'allowanonymous', 'allowshare', 'allowpostspecial', 'alloweditrules', 'allowpaytoauthor', 'alloweditpost', 'modnewposts', 'recyclebin', 'jammer', 'forumcolumns', 'threadcaches', 'disablewatermark', 'autoclose', 'simple');
		$table_forumfield_columns = array('fid', 'attachextensions', 'threadtypes', 'postcredits', 'replycredits', 'digestcredits', 'postattachcredits', 'getattachcredits', 'viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');

		$forum = $db->fetch_first("SELECT * FROM {$tablepre}forums WHERE fid='$fup'");
		if(!empty($projectid)) {
			$project = unserialize($db->result_first("SELECT value FROM {$tablepre}projects WHERE id='$projectid'"));

			foreach($table_forum_columns as $field) {
				$forumfields[$field] = $project[$field];
			}

			foreach($table_forumfield_columns as $field) {
				$forumfields[$field] = $project[$field];
			}

		} else {
			$forumfields['allowsmilies'] = $forumfields['allowbbcode'] = $forumfields['allowimgcode'] = $forumfields['allowshare'] = 1;
			$forumfields['allowpostspecial'] = 127;
		}

		$forumfields['fup'] = $forum ? $fup : 0;
		$forumfields['type'] = $forum['type'] == 'forum' ? 'sub' : 'forum';
		$forumfields['name'] = $newforum;
		$forumfields['status'] = 1;

		$sql1 = $sql2 = $comma = '';
		foreach($table_forum_columns as $field) {
			if(isset($forumfields[$field])) {
				$sql1 .= "$comma$field";
				$sql2 .= "$comma'{$forumfields[$field]}'";
				$comma = ', ';
			}
		}

		$db->query("INSERT INTO {$tablepre}forums ($sql1) VALUES ($sql2)");
		$forumfields['fid'] = $fid = $db->insert_id();

		$sql1 = $sql2 = $comma = '';
		foreach($table_forumfield_columns as $field) {
			if(isset($forumfields[$field])) {
				$sql1 .= "$comma$field";
				$sql2 .= "$comma'{$forumfields[$field]}'";
				$comma = ', ';
			}
		}

		$db->query("INSERT INTO {$tablepre}forumfields ($sql1) VALUES ($sql2)");

		$query = $db->query("SELECT uid, inherited FROM {$tablepre}moderators WHERE fid='$fup'");
		while($mod = $db->fetch_array($query)) {
			if($mod['inherited'] || $forum['inheritedmod']) {
				$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, inherited)
					VALUES ('$mod[uid]', '$fid', '1')");
			}
		}

		updatecache('forums');
		cpmsg('forums_add_forum_succeed', 'admincp.php?action=forumsedit');

	}

} elseif($action == 'forumsedit') {

	if(!submitcheck('editsubmit')) {
		shownav('menu_forums_edit');
		showtips('forums_tips');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['forums_edit']?></td></tr>
<tr><td class="altbg1"><br />
<form method="post" action="admincp.php?action=forumsedit">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$forums = $showedforums = array();
		$query = $db->query("SELECT f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.inheritedmod, ff.moderators
			FROM {$tablepre}forums f LEFT JOIN {$tablepre}forumfields ff USING(fid)
			ORDER BY f.type<>'group', f.displayorder");

		while($forum = $db->fetch_array($query)) {
			$forums[] = $forum;
		}
		for($i = 0; $i < count($forums); $i++) {
			if($forums[$i]['type'] == 'group') {
				echo '<ul>'.showforum($i, 'group');
				for($j = 0; $j < count($forums); $j++) {
					if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'forum') {
						echo '<ul>'.showforum($j);
						for($k = 0; $k < count($forums); $k++) {
							if($forums[$k]['fup'] == $forums[$j]['fid'] && $forums[$k]['type'] == 'sub') {
								echo '<ul>'.showforum($k, 'sub').'</ul>';
							}
						}
						echo '</ul>';
					}
				}
				echo '</ul>';
			} elseif(!$forums[$i]['fup'] && $forums[$i]['type'] == 'forum') {
				echo '<ul>'.showforum($i);
				for($j = 0; $j < count($forums); $j++) {
					if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'sub') {
						echo '<ul>'.showforum($j, 'sub').'</ul>';
					}
				}
				echo '</ul>';
			}
		}

		foreach($forums as $key => $forum) {
			if(!in_array($key, $showedforums)) {
				$db->query("UPDATE {$tablepre}forums SET fup='0', type='forum' WHERE fid='$forum[fid]'");
				echo '<ul>'.showforum($key).'</ul>';
			}
		}

		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center><br /></form></td></tr></table>\n";

	} else {

		// read from groups
		$usergroups = array();
		$query = $db->query("SELECT groupid, type, creditshigher, creditslower FROM {$tablepre}usergroups");
		while($group = $db->fetch_array($query)) {
			$usergroups[$group['groupid']] = $group;
		}

		if(is_array($order)) {
			foreach($order as $fid => $value) {
				$db->query("UPDATE {$tablepre}forums SET displayorder='$order[$fid]' WHERE fid='$fid'");
			}
		}

		updatecache('forums');

		cpmsg('forums_update_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'moderators' && $fid) {

	if(!submitcheck('modsubmit')) {

		$moderators = '';
		$query = $db->query("SELECT m.username, mo.* FROM {$tablepre}members m, {$tablepre}moderators mo WHERE mo.fid='$fid' AND m.uid=mo.uid ORDER BY mo.inherited, mo.displayorder");
		while($mod = $db->fetch_array($query)) {

			$moderators .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$mod[uid]\" ".($mod['inherited'] ? 'disabled' : '').">\n".
				"<td class=\"altbg2\"><a href=\"space.php?action=viewpro&uid=$mod[uid]\" target=\"_blank\">$mod[username]</a></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" name=\"displayordernew[$mod[uid]]\" value=\"$mod[displayorder]\" size=\"2\"></td>\n".
				"<td class=\"altbg2\">".($mod['inherited'] ? '<b>'.$lang['yes'].'</b>' : $lang['no'])."</td></tr>\n";
		}

		if($forum['type'] == 'group' || $forum['type'] == 'sub') {
			$checked = $forum['type'] == 'group' ? 'checked' : '';
			$disabled = 'disabled';
		} else {
			$checked = $forum['inheritedmod'] ? 'checked' : '';
			$disabled = '';
		}
		shownav('forums_moderators_edit');
		showtips('forums_moderators_tips');
?>
<form method="post" action="admincp.php?action=moderators&fid=<?=$fid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="4"><?=$lang['forums_moderators_edit']?> - <?=$forum['name']?></td></tr>
<tr align="center" class="category"><td><?=$lang['del']?></td><td><?=$lang['username']?></td><td><?=$lang['display_order']?></td><td><?=$lang['forums_moderators_inherited']?></td></tr>
<?=$moderators?>
<tr align="center"><td class="altbg1"><?=$lang['add_new']?></td><td class="altbg2"><input type='text' name="newmoderator" size="20"></td><td class="altbg1"><input type="text" name="newdisplayorder" size="2" value="0"></td><td class="altbg2">&nbsp;</td></tr>
<tr><td colspan="4" class="altbg2"><input class="checkbox" type="checkbox" name="inheritedmodnew" value="1" <?=$checked?> <?=$disabled?>> <?=$lang['forums_moderators_inherit']?></td></tr>
</table><br />
<center><input class="button" type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($forum['type'] == 'group') {
			$inheritedmodnew = 1;
		} elseif($forum['type'] == 'sub') {
			$inheritedmodnew = 0;
		}

		if(!empty($delete) || $newmoderator || (bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {

			$fidarray = $newmodarray = $origmodarray = array();

			if($forum['type'] == 'group') {
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type='forum' AND fup='$fid'");
				while($sub = $db->fetch_array($query)) {
					$fidarray[] = $sub['fid'];
				}
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type='sub' AND fup IN ('".implode('\',\'', $fidarray)."')");
				while($sub = $db->fetch_array($query)) {
					$fidarray[] = $sub['fid'];
				}
			} elseif($forum['type'] == 'forum') {
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type='sub' AND fup='$fid'");
				while($sub = $db->fetch_array($query)) {
					$fidarray[] = $sub['fid'];
				}
			}

			if(is_array($delete)) {
				foreach($delete as $uid) {
					$db->query("DELETE FROM {$tablepre}moderators WHERE uid='$uid' AND ((fid='$fid' AND inherited='0') OR (fid IN ('".implode('\',\'', $fidarray)."') AND inherited='1'))");
				}

				$excludeuids = 0;
				$deleteuids = '\''.implode('\',\'', $delete).'\'';
				$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE uid IN ($deleteuids)");
				while($mod = $db->fetch_array($query)) {
					$excludeuids .= ','.$mod['uid'];
				}

				$usergroups = array();
				$query = $db->query("SELECT groupid, type, radminid, creditshigher, creditslower FROM {$tablepre}usergroups");
				while($group = $db->fetch_array($query)) {
					$usergroups[$group['groupid']] = $group;
				}

				$query = $db->query("SELECT uid, groupid, credits FROM {$tablepre}members WHERE uid IN ($deleteuids) AND uid NOT IN ($excludeuids) AND adminid NOT IN (1,2)");
				while($member = $db->fetch_array($query)) {
					if($usergroups[$member['groupid']]['type'] == 'special' && $usergroups[$member['groupid']]['radminid'] != 3) {
						$adminidnew = -1;
						$groupidnew = $member['groupid'];
					} else {
						$adminidnew = 0;
						foreach($usergroups as $group) {
							if($group['type'] == 'member' && $member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) {
								$groupidnew = $group['groupid'];
								break;
							}
						}
					}
					$db->query("UPDATE {$tablepre}members SET adminid='$adminidnew', groupid='$groupidnew' WHERE uid='$member[uid]'");
				}
			}

			if((bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {
				$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE fid='$fid' AND inherited='0'");
				while($mod = $db->fetch_array($query)) {
					$origmodarray[] = $mod['uid'];
					if(!$forum['inheritedmod'] && $inheritedmodnew) {
						$newmodarray[] = $mod['uid'];
					}
				}
				if($forum['inheritedmod'] && !$inheritedmodnew) {
					$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ('".implode('\',\'', $origmodarray)."') AND fid IN ('".implode('\',\'', $fidarray)."') AND inherited='1'");
				}
			}

			if($newmoderator) {
				$member = $db->fetch_first("SELECT uid FROM {$tablepre}members WHERE username='$newmoderator'");
				if(!$member) {
					cpmsg('members_edit_nonexistence');
				} else {
					$newmodarray[] = $member['uid'];
					$db->query("UPDATE {$tablepre}members SET groupid='3' WHERE uid='$member[uid]' AND adminid NOT IN (1,2,3,4,5,6,7,8,-1)");
					$db->query("UPDATE {$tablepre}members SET adminid='3' WHERE uid='$member[uid]' AND adminid NOT IN (1,2)");
					$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, displayorder, inherited)
						VALUES ('$member[uid]', '$fid', '$newdisplayorder', '0')");
				}
			}

			foreach($newmodarray as $uid) {
				$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, displayorder, inherited)
					VALUES ('$uid', '$fid', '$newdisplayorder', '0')");

				if($inheritedmodnew) {
					foreach($fidarray as $ifid) {
						$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, inherited)
							VALUES ('$uid', '$ifid', '1')");
					}
				}
			}

			if($forum['type'] == 'group') {
				$inheritedmodnew = 1;
			} elseif($forum['type'] == 'sub') {
				$inheritedmodnew = 0;
			}
			$db->query("UPDATE {$tablepre}forums SET inheritedmod='$inheritedmodnew' WHERE fid='$fid'");

		}

		if(is_array($displayordernew)) {
			foreach($displayordernew as $uid => $order) {
				$db->query("UPDATE {$tablepre}moderators SET displayorder='$order' WHERE fid='$fid' AND uid='$uid'");
			}
		}

		$moderators = $tab = '';
		$query = $db->query("SELECT m.username FROM {$tablepre}members m, {$tablepre}moderators mo WHERE mo.fid='$fid' AND mo.inherited='0' AND m.uid=mo.uid ORDER BY mo.displayorder");
		while($mod = $db->fetch_array($query)) {
			$moderators .= $tab.addslashes($mod['username']);
			$tab = "\t";
		}
		$db->query("UPDATE {$tablepre}forumfields SET moderators='$moderators' WHERE fid='$fid'");

		cpmsg('forums_moderators_update_succeed', "admincp.php?action=moderators&fid=$fid");

	}

} elseif($action == 'forumsmerge') {

	if(!submitcheck('mergesubmit') || $source == $target) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';
		require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

		$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> $lang[select]</option><option value=\"\">&nbsp;</option>".str_replace('%', '%%', forumselect()).'</select>';
		shownav('menu_forums_merge');

?>
<form method="post" action="admincp.php?action=forumsmerge">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['forums_merge']?></td></tr>
<tr align="center"><td class="altbg1" width="40%"><?=$lang['forums_merge_source']?>:</td>
<td class="altbg2" width="45%"><?=sprintf($forumselect, "source")?></td></tr>
<tr align="center"><td class="altbg1" width="40%"><?=$lang['forums_merge_target']?>:</td>
<td class="altbg2" width="45%"><?=sprintf($forumselect, "target")?></td></tr>
</table><br /><center><input class="button" type="submit" name="mergesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}forums WHERE fid IN ('$source', '$target') AND type<>'group'") != 2) {
			cpmsg('forums_nonexistence');
		}

		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}forums WHERE fup='$source'")) {
			cpmsg('forums_merge_source_sub_notnull');
		}

		$db->query("UPDATE {$tablepre}threads SET fid='$target' WHERE fid='$source'");
		$db->query("UPDATE {$tablepre}posts SET fid='$target' WHERE fid='$source'");

		$sourceforum = $db->fetch_first("SELECT threads, posts FROM {$tablepre}forums WHERE fid='$source'");

		$db->query("UPDATE {$tablepre}forums SET threads=threads+$sourceforum[threads], posts=posts+$sourceforum[posts] WHERE fid='$target'");
		$db->query("DELETE FROM {$tablepre}forums WHERE fid='$source'");
		$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='$source'");
		$db->query("DELETE FROM {$tablepre}moderators WHERE fid='$source'");

		$query = $db->query("SELECT * FROM {$tablepre}access WHERE fid='$source'");
		while($access = $db->fetch_array($query)) {
			$db->query("INSERT INTO {$tablepre}access (uid, fid, allowview, allowpost, allowreply, allowgetattach)
				VALUES ('$access[uid]', '$target', '$access[allowview]', '$access[allowpost]', '$access[allowreply]', '$access[allowgetattach]')", 'SILENT');
		}
		$db->query("DELETE FROM {$tablepre}access WHERE fid='$source'");

		updatecache('forums');

		cpmsg('forums_merge_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'forumdetail') {

	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');

	$forum = $db->fetch_first("SELECT *, f.fid AS fid FROM {$tablepre}forums f
		LEFT JOIN {$tablepre}forumfields ff USING (fid)
		WHERE f.fid='$fid'");

	if(!$forum) {
		cpmsg('forums_nonexistence');
	}

	$query = $db->query("SELECT disabledactions FROM {$tablepre}adminactions WHERE admingid='$groupid'");
	$dactionarray = ($dactionarray = unserialize($db->result($query, 0))) ? $dactionarray : array();
	$allowthreadtypes = !in_array('threadtypes', $dactionarray);

	if(!empty($projectid)) {
		$query = $db->query("SELECT value FROM {$tablepre}projects WHERE id='$projectid'");
		$forum = @array_merge($forum, unserialize($db->result($query, 0)));
	}

	if(!submitcheck('detailsubmit') && !submitcheck('saveconfigsubmit')) {
		shownav('menu_forums_detail');

?>
<script language="JavaScript">
var typenum = 1;
function addtype() {
	if(typenum > 9) return;
	$('type_' + typenum).style.display = '';
	typenum++;
}
</script>
<form method="post" action="admincp.php?action=forumdetail&fid=<?=$fid?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="type" value="<?=$forum['type']?>">
<input type="hidden" name="detailsubmit" value="submit">
<?

		if($forum['type'] == 'group') {

			showtype("$lang[forums_cat_detail] - $forum[name]", 'top');
			showsetting('forums_cat_name', 'namenew', $forum['name'], 'text');
			showsetting('forums_sub_horizontal', 'forumcolumnsnew', $forum['forumcolumns'], 'text');
			showtype('', 'bottom');

		} else {
			showtips('forums_edit_tips');
			$projectselect = "<select name=\"projectid\" onchange=\"window.location='admincp.php?action=forumdetail&fid=$fid&projectid='+this.options[this.options.selectedIndex].value\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
			$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='forum'");
			while($project = $db->fetch_array($query)) {
				$projectselect .= "<option value=\"$project[id]\" ".($project['id'] == $projectid ? 'selected="selected"' : NULL).">$project[name]</option>\n";
			}
			$projectselect .= '</select>';

			$fupselect = "<select name=\"fupnew\">\n";
			$query = $db->query("SELECT fid, type, name, fup FROM {$tablepre}forums WHERE fid<>'$fid' AND type<>'sub' ORDER BY displayorder");
			while($fup = $db->fetch_array($query)) {
				$fups[] = $fup;
			}
			if(is_array($fups)) {
				foreach($fups as $forum1) {
					if($forum1['type'] == 'group') {
						$selected = $forum1['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
						$fupselect .= "<option value=\"$forum1[fid]\" $selected>$forum1[name]</option>\n";
						foreach($fups as $forum2) {
							if($forum2['type'] == 'forum' && $forum2['fup'] == $forum1['fid']) {
								$selected = $forum2['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
								$fupselect .= "<option value=\"$forum2[fid]\" $selected>&nbsp; &gt; $forum2[name]</option>\n";
							}
						}
					}
				}
				foreach($fups as $forum0) {
					if($forum0['type'] == 'forum' && $forum0['fup'] == 0) {
						$selected = $forum0['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
						$fupselect .= "<option value=\"$forum0[fid]\" $selected>$forum0[name]</option>\n";
					}
				}
			}
			$fupselect .= '</select>';

			$groups = array();
			$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups");
			while($group = $db->fetch_array($query)) {
				$groups[] = $group;
			}

			$styleselect = "<select name=\"styleidnew\"><option value=\"0\">$lang[use_default]</option>";
			$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
			while($style = $db->fetch_array($query)) {
				$styleselect .= "<option value=\"$style[styleid]\" ".
					($style['styleid'] == $forum['styleid'] ? 'selected="selected"' : NULL).
					">$style[name]</option>\n";
			}
			$styleselect .= '</select>';

			if($forum['autoclose']) {
				$acoption = $forum['autoclose'] / abs($forum['autoclose']);
				$forum['autoclose'] = abs($forum['autoclose']);
			} else {
				$acoption = 0;
			}
			$checkac = array($acoption => 'checked');

			$viewaccess = $postaccess = $replyaccess = $getattachaccess = $postattachaccess = '';

			$query = $db->query("SELECT m.username, a.* FROM {$tablepre}access a LEFT JOIN {$tablepre}members m USING (uid) WHERE fid='$fid'");
			while($access = $db->fetch_array($query)) {
				$member = ", <a href=\"admincp.php?action=access&uid=$access[uid]\" target=\"_blank\">$access[username]</a>";
				$viewaccess .= $access['allowview'] ? $member : NULL;
				$postaccess .= $access['allowpost'] ? $member : NULL;
				$replyaccess .= $access['allowreply'] ? $member : NULL;
				$getattachaccess .= $access['allowgetattach'] ? $member : NULL;
				$postattachaccess .= $access['allowpostattach'] ? $member : NULL;
			}
			unset($member);

                        if($forum['status'] != 2) {
        			if($forum['threadtypes']) {
        				$forum['threadtypes'] = unserialize($forum['threadtypes']);
        				$forum['typemodels'] = unserialize($forum['typemodels']);
        				$forum['threadtypes']['status'] = 1;
        			} else {
        				$forum['threadtypes'] = array('status' => 0, 'required' => 0, 'listable' => 0, 'prefix' => 0, 'options' => array());
        			}

        			$typeselect = '';

        			$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
        			while($type = $db->fetch_array($query)) {

        				$typeselected = array();
        				if(isset($forum['threadtypes']['flat'][$type['typeid']])) {
        					$typeselected[1] = 'checked';
        				} elseif(isset($forum['threadtypes']['selectbox'][$type['typeid']])) {
        					$typeselected[2] = 'checked';
        				} else {
        					$typeselected[0] = 'checked';
        				}
        				$typeselected[3] = $forum['threadtypes']['show'][$type['typeid']] ? 'checked' : '';

        				$showtype = TRUE;
        				if($type['special'] && !@include_once DISCUZ_ROOT.'./forumdata/cache/threadtype_'.$type['typeid'].'.php') {
        					$showtype = FALSE;
        				}
        				$typeselect .= $showtype ? "<tr align=\"center\"><td class=\"altbg1\">$type[name]</td>".
        					"<td class=\"altbg2\">$type[description]".($type['special'] ? '&nbsp;&nbsp;'.$lang['menu_forums_types'] : '')."</td>".
        					"<td class=\"altbg1\"><input class=\"radio\" type=\"radio\" name=\"threadtypesnew[options][{$type[typeid]}]\" value=\"0\" $typeselected[0]></td>".
        					"<td class=\"altbg2\"><input class=\"radio\" type=\"radio\" name=\"threadtypesnew[options][{$type[typeid]}]\" value=\"1\" $typeselected[1]></td>".
        					"<td class=\"altbg1\"><input class=\"radio\" type=\"radio\" name=\"threadtypesnew[options][{$type[typeid]}]\" value=\"2\" $typeselected[2]></td>".
        					"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"threadtypesnew[options][show][{$type[typeid]}]\" value=\"3\" $typeselected[3] ".(!$type['special'] ? 'disabled' : '')."></td>".
        					"</tr>" : '';
        			}

        			$typeselect = $typeselect ? $typeselect : '<tr><td class="altbg1" colspan="6">'.$lang['forums_edit_threadtypes_options_null'].'</td></tr>';

                        	$num = 0;
        			$query = $db->query("SELECT * FROM {$tablepre}typemodels ORDER BY displayorder");
        			while($model = $db->fetch_array($query)) {
        				$num++;
	       				$typemodelselect .= $num && $num % 6 == 0 ? '<br />' : '';

	       				$modelchecked = $forum['typemodels'][$model['id']] ? 'checked' : '';
        				$typemodelselect .= "<input class=\"checkbox\" type=\"checkbox\" name=\"typemodel[]\" value=\"$model[id]\" $modelchecked>$model[name]&nbsp;&nbsp;";
	       			}

                        }

			$forum['postcredits'] = $forum['postcredits'] ? unserialize($forum['postcredits']) : array();
			$forum['replycredits'] = $forum['replycredits'] ? unserialize($forum['replycredits']) : array();
			$forum['digestcredits'] = $forum['digestcredits'] ? unserialize($forum['digestcredits']) : array();
			$forum['postattachcredits'] = $forum['postattachcredits'] ? unserialize($forum['postattachcredits']) : array();
			$forum['getattachcredits'] = $forum['getattachcredits'] ? unserialize($forum['getattachcredits']) : array();
			$forum['circle'] = $forum['status'] == 2 ? 1 : 0;
			$simplebin = sprintf('%08b', $forum['simple']);
			$forum['defaultorderfield'] = bindec(substr($simplebin, 0, 2));
			$forum['defaultorder'] = ($forum['simple'] & 32) ? 1 : 0;
			$forum['subforumsindex'] = bindec(substr($simplebin, 3, 2));
			$forum['subforumsindex'] = $forum['subforumsindex'] == 0 ? -1 : ($forum['subforumsindex'] == 2 ? 0 : 1);
			$forum['simple'] = $forum['simple'] & 1;
			$forum['modrecommend'] = $forum['modrecommend'] ? unserialize($forum['modrecommend']) : '';
			$forum['formulaperm'] = unserialize($forum['formulaperm']);$forum['formulaperm'] = $forum['formulaperm'][0];

			showtype('menu_forums_detail', 'top');
			showsetting('forums_scheme', '', '', $projectselect);

			showtype("forums_basic_settings");

			showsetting('forums_edit_name', 'namenew', $forum['name'], 'text');
			showsetting('forums_edit_display', 'statusnew', $forum['status'], 'radio');
			showsetting('forums_edit_perm_passwd', 'passwordnew', $forum['password'], 'text');
			showsetting('forums_edit_up', '', '', $fupselect);
			showsetting('forums_edit_redirect', 'redirectnew', $forum['redirect'], 'text');
			showsetting('forums_edit_icon', 'iconnew', $forum['icon'], 'text');
			showsetting('forums_edit_description', 'descriptionnew', $forum['description'], 'textarea');
			showsetting('forums_edit_rules', 'rulesnew', $forum['rules'], 'textarea');
			showsetting('forums_edit_keyword', 'keywordsnew', $forum['keywords'], 'text');

			showtype('forums_extend_conf');
			showsetting('forums_edit_style', '', '', $styleselect);
			showsetting('forums_sub_horizontal', 'forumcolumnsnew', $forum['forumcolumns'], 'text');
			showsetting('forums_edit_subforumsindex', 'subforumsindexnew', $forum['subforumsindex'], 'radioplus');
			showsetting('forums_edit_simple', 'simplenew', $forum['simple'], 'radio');
			showsetting('forums_edit_defaultorderfield', array('defaultorderfieldnew', array(
					array(0, $lang['forums_edit_order_lastpost']),
					array(1, $lang['forums_edit_order_starttime']),
					array(2, $lang['forums_edit_order_replies']),
					array(3, $lang['forums_edit_order_views']))), $forum['defaultorderfield'], 'mradio');
			showsetting('forums_edit_defaultorder', array('defaultordernew', array(
					array(0, $lang['forums_edit_order_desc']),
					array(1, $lang['forums_edit_order_asc']))), $forum['defaultorder'], 'mradio');
			showsetting('forums_threadcache', 'threadcachesnew', $forum['threadcaches'], 'text');
			showsetting('forums_edit_edit_rules', array('alloweditrulesnew', array(
				array(0, $lang['forums_edit_edit_rules_html_none']),
				array(1, $lang['forums_edit_edit_rules_html_no']),
				array(2, $lang['forums_edit_edit_rules_html_yes']))), $forum['alloweditrules'], 'mradio');
			showsetting('forums_edit_recommend', 'modrecommendnew[open]', $forum['modrecommend']['open'], 'radio', '', '', 1);
			showsetting('forums_edit_recommend_sort', array('modrecommendnew[sort]', array(
				array(0, $lang['forums_edit_recommend_sort_manual']),
				array(1, $lang['forums_edit_recommend_sort_auto']),
				array(2, $lang['forums_edit_recommend_sort_mix']))), $forum['modrecommend']['sort'], 'mradio');
			showsetting('forums_edit_recommend_orderby', array('modrecommendnew[orderby]', array(
				array(0, $lang['forums_edit_recommend_orderby_dateline']),
				array(1, $lang['forums_edit_recommend_orderby_lastpost']),
				array(2, $lang['forums_edit_recommend_orderby_views']),
				array(3, $lang['forums_edit_recommend_orderby_replies']),
				array(4, $lang['forums_edit_recommend_orderby_digest']))), $forum['modrecommend']['orderby'], 'mradio');
			showsetting('forums_edit_recommend_num', 'modrecommendnew[num]', $forum['modrecommend']['num'], 'text');
			showsetting('forums_edit_recommend_maxlength', 'modrecommendnew[maxlength]', $forum['modrecommend']['maxlength'], 'text');
			showsetting('forums_edit_recommend_cachelife', 'modrecommendnew[cachelife]', $forum['modrecommend']['cachelife'], 'text');
			showsetting('forums_edit_recommend_dateline', 'modrecommendnew[dateline]', $forum['modrecommend']['dateline'], 'text');
			echo '</tbody><tbody>';
			if($supe['status']) {
			        if($supe['circlestatus']) {
			        	showsetting('circle_forum', 'circlenew', $forum['circle'], 'radio');
			        }
				$forum['supe_pushsetting'] = unserialize($forum['supe_pushsetting']);
				$forum['supe_pushsetting']['status'] = intval($forum['supe_pushsetting']['status']);
				$forum['supe_pushsetting']['filter']['digest'] = intval($forum['supe_pushsetting']['filter']['digest']);
				$forum['supe_pushsetting']['filter']['displayorder'] = intval($forum['supe_pushsetting']['filter']['displayorder']);
				$supe_pushsetchecked = array($forum['supe_pushsetting']['status'] => ' checked');
				$supe_pushsetdigestselected = array($forum['supe_pushsetting']['filter']['digest'] => ' selected');
				$supe_pushsetdisplayorderselected = array($forum['supe_pushsetting']['filter']['displayorder'] => ' selected');
				echo '<tr><td class="altbg1"><b>'.$lang['supe_collection_mode'].'</b><br />'.$lang['supe_collection_mode_comment'].'</td>';
				echo '<td class="altbg2"><input class="radio" type="radio" name="supe_pushsetting[status]" value="0" '.$supe_pushsetchecked[0].' onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'">'.$lang['supe_pushsetting_status_0'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="1" '.$supe_pushsetchecked[1].' onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'">'.$lang['supe_pushsetting_status_1'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="2" '.$supe_pushsetchecked[2].' onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'">'.$lang['supe_pushsetting_status_2'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="3" '.$supe_pushsetchecked[3].' onclick="$(\'supe_pushsetting_filter_div\').style.display=\'block\'">'.$lang['supe_pushsetting_status_3'];
				echo '<div id="supe_pushsetting_filter_div" style="display:'.($forum['supe_pushsetting']['status'] == 3 ? 'block' : 'none').'">';
					echo '<br />'.$lang['supe_pushsetting_views'].' >= <input type="input" name="supe_pushsetting[filter][views]" value="'.$forum['supe_pushsetting']['filter']['views'].'" size="8">';
					echo '<br />'.$lang['supe_pushsetting_replys'].' >= <input type="input" name="supe_pushsetting[filter][replies]" value="'.$forum['supe_pushsetting']['filter']['replies'].'" size="8">';
					echo '<br />'.$lang['supe_pushsetting_digest'].' >= <select name="supe_pushsetting[filter][digest]"><option value="0"'.$supe_pushsetdigestselected[0].'></option><option value="1"'.$supe_pushsetdigestselected[1].'>'.$lang['forums_digest_one'].'</option><option value="2"'.$supe_pushsetdigestselected[2].'>'.$lang['forums_digest_two'].'</option><option value="3"'.$supe_pushsetdigestselected[3].'>'.$lang['forums_digest_three'].'</option></select>';
					echo '<br />'.$lang['supe_pushsetting_stick'].' >= <select name="supe_pushsetting[filter][displayorder]"><option value="0"'.$supe_pushsetdisplayorderselected[0].'></option><option value="1"'.$supe_pushsetdisplayorderselected[1].'>'.$lang['forums_stick_one'].'</option><option value="2"'.$supe_pushsetdisplayorderselected[2].'>'.$lang['forums_stick_two'].'</option><option value="3"'.$supe_pushsetdisplayorderselected[3].'>'.$lang['forums_stick_three'].'</option></select>';
				echo '</div></td></tr>';
				unset($supe_pushsetchecked, $supe_pushsetdigestselected, $supe_pushsetdisplayorderselected);
			}

			showtype('forums_edit_options');
			showsetting('forums_edit_modposts', array('modnewpostsnew', array(
				array(0, $lang['none']),
				array(1, $lang['forums_edit_modposts_threads']),
				array(2, $lang['forums_edit_modposts_posts']))), $forum['modnewposts'], 'mradio');
			showsetting('forums_edit_alloweditpost', 'alloweditpostnew', $forum['alloweditpost'], 'radio');
			showsetting('forums_edit_recyclebin', 'recyclebinnew', $forum['recyclebin'], 'radio');
			showsetting('forums_edit_share', 'allowsharenew', $forum['allowshare'], 'radio');
			showsetting('forums_edit_html', 'allowhtmlnew', $forum['allowhtml'], 'radio');
			showsetting('forums_edit_bbcode', 'allowbbcodenew', $forum['allowbbcode'], 'radio');
			showsetting('forums_edit_imgcode', 'allowimgcodenew', $forum['allowimgcode'], 'radio');
			showsetting('forums_edit_mediacode', 'allowmediacodenew', $forum['allowmediacode'], 'radio');
			showsetting('forums_edit_smilies', 'allowsmiliesnew', $forum['allowsmilies'], 'radio');
			showsetting('forums_edit_jammer', 'jammernew', $forum['jammer'], 'radio');
			showsetting('forums_edit_anonymous', 'allowanonymousnew', $forum['allowanonymous'], 'radio');
			showsetting('forums_edit_disablewatermark', 'disablewatermarknew', $forum['disablewatermark'], 'radio');
			showsetting('forums_edit_allowpostspecial', array('allowpostspecialnew', array(
				$lang['forums_thread_poll'],
				$lang['forums_thread_trade'],
				$lang['forums_thread_reward'],
				$lang['forums_thread_activity'],
				$lang['forums_thread_debate'],
				$lang['forums_thread_video'])), $forum['allowpostspecial'], 'mcheckbox');
			showsetting('forums_edit_allowspecialonly', 'allowspecialonlynew', $forum['allowspecialonly'], 'radio');
			if(!empty($tradetypes) && is_array($tradetypes)) {
				$forum['tradetypes'] = $forum['tradetypes'] == '' ? -1 : unserialize($forum['tradetypes']);
				$tradetypeselect = '';
				foreach($tradetypes as $typeid => $typename) {
					$tradetypeselect .= '<input class="checkbox" type="checkbox" name="tradetypesnew[]" value="'.$typeid.'" '.($forum['tradetypes'] == -1 || @in_array($typeid, $forum['tradetypes']) ? 'checked' : '').'> '.$typename.'<br />';
				}
				showsetting('forums_edit_trade_type', '', '', $tradetypeselect);
			}
			showsetting('forums_edit_trade_payto', 'allowpaytoauthornew', $forum['allowpaytoauthor'], 'radio');
			showsetting('forums_edit_autoclose', '', '', '<input class="radio" type="radio" name="autoclosenew" value="0" '.$checkac[0].' onclick="this.form.autoclosetimenew.disabled=true;"> '.$lang['forums_edit_autoclose_none'].'<br /><input class="radio" type="radio" name="autoclosenew" value="1" '.$checkac[1].' onclick="this.form.autoclosetimenew.disabled=false;"> '.$lang['forums_edit_autoclose_dateline'].'<br /><input class="radio" type="radio" name="autoclosenew" value="-1" '.$checkac[-1].' onclick="this.form.autoclosetimenew.disabled=false;"> '.$lang['forums_edit_autoclose_lastpost']);
			showsetting('forums_edit_autoclose_time', '', '', '<input type="text" size="30" value="'.$forum['autoclose'].'" name="autoclosetimenew" '.($acoption ? '' : 'disabled').'>');
			showsetting('forums_edit_attach_ext', 'attachextensionsnew', $forum['attachextensions'], 'text');

			showtype('forums_edit_credits', '', '', 7);
			echo '<tr class="category"><td>'.$lang['credits_id'].'</td><td>'.$lang['credits_title'].'</td><td>'.$lang['forums_edit_postcredits_add'].'</td><td>'.$lang['forums_edit_replycredits_add'].'</td><td>'.$lang['settings_credits_policy_digest'].'</td><td>'.$lang['settings_credits_policy_post_attach'].'</td><td>'.$lang['settings_credits_policy_get_attach'].'</td></tr>';
			$customcreditspolicy = '';
			if(is_array($extcredits)) {
				foreach($extcredits AS $i => $extcredit) {
					$customcreditspolicy .= "<tr align=\"center\"><td class=\"altbg1\" width=\"10%\">extcredits$i</td>".
						"<td class=\"altbg2\" width=\"10%\">{$extcredit['title']}</td>".
						"<td class=\"altbg1\" width=\"12%\"><input type=\"text\" size=\"2\" name=\"postcreditsnew[$i]\" value=\"".$forum['postcredits'][$i]."\"></td>".
						"<td class=\"altbg2\" width=\"12%\"><input type=\"text\" size=\"2\" name=\"replycreditsnew[$i]\" value=\"".$forum['replycredits'][$i]."\"></td>".
						"<td class=\"altbg1\" width=\"12%\"><input type=\"text\" size=\"2\" name=\"digestcreditsnew[$i]\" value=\"".$forum['digestcredits'][$i]."\"></td>".
						"<td class=\"altbg2\" width=\"12%\"><input type=\"text\" size=\"2\" name=\"postattachcreditsnew[$i]\" value=\"".$forum['postattachcredits'][$i]."\"></td>".
						"<td class=\"altbg1\" width=\"12%\"><input type=\"text\" size=\"2\" name=\"getattachcreditsnew[$i]\" value=\"".$forum['getattachcredits'][$i]."\"></td></tr>";
				}
			}
			$customcreditspolicy .= '<tr><td colspan="7">'.$lang['forums_edit_credits_comment'].'<br /><a href="member.php?action=credits&view=forum_post&fid='.$fid.'" target="_blank">'.$lang['forums_edit_credits_preview'].'</a></td></tr>';
			echo $customcreditspolicy;

			if($allowthreadtypes) {
				showtype('forums_edit_threadtypes');
				if($forum['status'] != 2) {
					showsetting('forums_edit_threadtypes_status', 'threadtypesnew[status]', $forum['threadtypes']['status'], 'radio');
					showsetting('forums_edit_threadtypes_required', 'threadtypesnew[required]', $forum['threadtypes']['required'], 'radio');
					showsetting('forums_edit_threadtypes_listable', 'threadtypesnew[listable]', $forum['threadtypes']['listable'], 'radio');
					showsetting('forums_edit_threadtypes_prefix', 'threadtypesnew[prefix]', $forum['threadtypes']['prefix'], 'radio');
					showsetting('forums_edit_threadtypes_typemodel', '', '', $typemodelselect);

					echo '</table><br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
						'<tr class="header"><td width="10%">'.$lang['forums_edit_cat_name'].'</td><td width="25%">'.$lang['forums_sort_note'].'</td><td width="10%">'.$lang['not_use'].'</td><td width="15%">'.$lang['forums_threadtypes_use_cols'].'</td><td width="15%">'.$lang['forums_threadtypes_use_choice'].'</td><td width="20%">'.$lang['forums_threadtypes_show'].'</td></tr>'.
						$typeselect.'<tr><td colspan=6>'.$lang['add_new'].'<a href="###" onclick="addtype()">[+]</a></td></tr><tbody id="type_0"><tr align="center"><td class="altbg1"><input type="text" name="newname[0]" size="15"></td><td class="altbg2"><input type="text" name="newdescription[0]" size="15"></td><td class="altbg1"><input class="radio" type="radio" name="newoptions[0]" value="0"></td><td class="altbg2"><input class="radio" type="radio" name="newoptions[0]" value="1" checked></td><td class="altbg1"><input class="radio" type="radio" name="newoptions[0]" value="2"></td><td class="altbg1"><input class="checkbox" type="checkbox" name="newoptions[0]" value="2"></td></tr></tbody>';

					for($i = 1; $i < 10; $i++) {
						echo '<tbody id="type_'.$i.'" style="display: none"><tr align="center"><td class="altbg1"><input type="text" name="newname['.$i.']" size="15"></td><td class="altbg2"><input type="text" name="newdescription['.$i.']" size="15"></td><td class="altbg1"><input class="radio" type="radio" name="newoptions['.$i.']" value="0"></td><td class="altbg2"><input class="radio" type="radio" name="newoptions['.$i.']" value="1" checked></td><td class="altbg1"><input class="radio" type="radio" name="newoptions['.$i.']" value="2"></td><td class="altbg1"><input class="checkbox" type="checkbox" name="newoptions['.$i.']" value="2"></td></tr></tbody>';
					}

					echo '<tr><td colspan="6">'.$lang['settings_threadtypes_comment'].'</td></tr>';

				} else {
					echo '<tr><td>'.$lang['forums_edit_threadtypes_close'].'</td></tr>';
				}
			}

			echo '</table><br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
				'<tr class="header"><td>'.$lang['forums_edit_perm'].'</td>'.
				'<td><input class="checkbox" type="checkbox" name="chkall1" onclick="checkall(this.form, \'viewperm\', \'chkall1\')"> '.$lang['forums_edit_perm_view'].'</td>'.
				'<td><input class="checkbox" type="checkbox" name="chkall2" onclick="checkall(this.form, \'postperm\', \'chkall2\')"> '.$lang['forums_edit_perm_post'].'</td>'.
				'<td><input class="checkbox" type="checkbox" name="chkall3" onclick="checkall(this.form, \'replyperm\', \'chkall3\')"> '.$lang['forums_edit_perm_reply'].'</td>'.
				'<td><input class="checkbox" type="checkbox" name="chkall4" onclick="checkall(this.form, \'getattachperm\', \'chkall4\')"> '.$lang['forums_edit_perm_get_attach'].'</td>'.
				'<td><input class="checkbox" type="checkbox" name="chkall5" onclick="checkall(this.form, \'postattachperm\', \'chkall5\')"> '.$lang['forums_edit_perm_post_attach'].'</td></tr>';
			foreach($groups as $group) {
				echo '<tr><td class="altbg1"><input class="checkbox" title="'.$lang['select_all'].'" type="checkbox" name="chkallv'.$group['groupid'].'" onclick="checkallvalue(this.form, '.$group['groupid'].', \'chkallv'.$group['groupid'].'\')"> '.$group[grouptitle].'</td>';
				$altbgthis = 'altbg1';
				foreach($perms as $perm) {
					$checked = strstr($forum[$perm], "\t$group[groupid]\t") ? 'checked' : NULL;
					$altbgthis = $altbgthis == 'altbg2' ? 'altbg1' : 'altbg2';
					echo '<td class="'.$altbgthis.'"><input class="checkbox" type="checkbox" name="'.$perm.'[]" value="'.$group['groupid'].'" '.$checked.'></td>';
				}
				echo '</tr>';
			}
			echo '</td></tr><tr><td colspan="6">'.$lang['forums_edit_perm_comment'].'</td></tr>';

			echo '<tr class="header"><td colspan="6"><b>'.$lang['forums_edit_access_mask'].'</b></td></tr>';
			echo '<tr><td class="altbg1">'.$lang['forums_edit_perm_view'].':</td><td class="altbg2" colspan="5">'.substr($viewaccess, 2).'</td></tr>';
			echo '<tr><td class="altbg1">'.$lang['forums_edit_perm_post'].':</td><td class="altbg2" colspan="5">'.substr($postaccess, 2).'</td></tr>';
			echo '<tr><td class="altbg1">'.$lang['forums_edit_perm_reply'].':</td><td class="altbg2" colspan="5">'.substr($replyaccess, 2).'</td></tr>';
			echo '<tr><td class="altbg1">'.$lang['forums_edit_perm_get_attach'].':</td><td class="altbg2" colspan="5">'.substr($getattachaccess, 2).'</td></tr>';
			echo '<tr><td class="altbg1">'.$lang['forums_edit_perm_post_attach'].':</td><td class="altbg2" colspan="5">'.substr($postattachaccess, 2).'</td></tr>';

			showtype('settings_formulaperm');

			$formulareplace .= '\'<u>'.$lang['settings_creditsformula_digestposts'].'</u>\',\'<u>'.$lang['settings_creditsformula_posts'].'</u>\',\'<u>'.$lang['settings_creditsformula_oltime'].'</u>\',\'<u>'.$lang['settings_creditsformula_pageviews'].'</u>\'';

?>
<script>

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function insertunit(text, textend) {
	$('formulapermnew').focus();
	textend = isUndefined(textend) ? '' : textend;
	if(!isUndefined($('formulapermnew').selectionStart)) {
		var opn = $('formulapermnew').selectionStart + 0;
		if(textend != '') {
			text = text + $('formulapermnew').value.substring($('formulapermnew').selectionStart, $('formulapermnew').selectionEnd) + textend;
		}
		$('formulapermnew').value = $('formulapermnew').value.substr(0, $('formulapermnew').selectionStart) + text + $('formulapermnew').value.substr($('formulapermnew').selectionEnd);
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(textend != '') {
			text = text + sel.text + textend;
		}
		sel.text = text.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character', -strlen(text));
	} else {
		$('formulapermnew').value += text;
	}
	formulaexp();
}

var formulafind = new Array('digestposts', 'posts', 'oltime', 'pageviews');
var formulareplace = new Array(<?=$formulareplace?>);
function formulaexp() {
	var result = $('formulapermnew').value;
<?

		$extcreditsbtn = '';
		for($i = 1; $i <= 8; $i++) {
			$extcredittitle = $extcredits[$i]['title'] ? $extcredits[$i]['title'] : $lang['settings_creditsformula_extcredits'].$i;
			echo 'result = result.replace(/extcredits'.$i.'/g, \'<u>'.$extcredittitle.'</u>\');';
			$extcreditsbtn .= '<a href="###" onclick="insertunit(\'extcredits'.$i.'\')">'.$extcredittitle.'</a> &nbsp;';
		}

		echo 'result = result.replace(/digestposts/g, \'<u>'.$lang['settings_creditsformula_digestposts'].'</u>\');';
		echo 'result = result.replace(/posts/g, \'<u>'.$lang['settings_creditsformula_posts'].'</u>\');';
		echo 'result = result.replace(/oltime/g, \'<u>'.$lang['settings_creditsformula_oltime'].'</u>\');';
		echo 'result = result.replace(/pageviews/g, \'<u>'.$lang['settings_creditsformula_pageviews'].'</u>\');';
		echo 'result = result.replace(/and/g, \'&nbsp;&nbsp;'.$lang['settings_formulaperm_and'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/or/g, \'&nbsp;&nbsp;'.$lang['settings_formulaperm_or'].'&nbsp;&nbsp;\');';
		echo 'result = result.replace(/>=/g, \'&ge;\');';
		echo 'result = result.replace(/<=/g, \'&le;\');';

?>
	$('formulapermexp').innerHTML = result;
}
</script>
<tr><td colspan="2" class="altbg1">
<span class="smalltxt"><?=$lang['settings_formulaperm_comment']?></span>
<br />
<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('creditsformulanew', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('creditsformulanew', 0)">
<div style="width:90%" class="formulaeditor">
<div><?=$extcreditsbtn?><br />
<a href="###" onclick="insertunit(' digestposts ')"><?=$lang['settings_creditsformula_digestposts']?></a>&nbsp;
<a href="###" onclick="insertunit(' posts ')"><?=$lang['settings_creditsformula_posts']?></a>&nbsp;
<a href="###" onclick="insertunit(' oltime ')"><?=$lang['settings_creditsformula_oltime']?></a>&nbsp;
<a href="###" onclick="insertunit(' pageviews ')"><?=$lang['settings_creditsformula_pageviews']?></a>&nbsp;
<a href="###" onclick="insertunit(' + ')">&nbsp;+&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' - ')">&nbsp;-&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' * ')">&nbsp;*&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' / ')">&nbsp;/&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' > ')">&nbsp;>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' >= ')">&nbsp;>=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' < ')">&nbsp;<&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' <= ')">&nbsp;<=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' = ')">&nbsp;=&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' (', ') ')">&nbsp;(&nbsp;)&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' and ')">&nbsp;<?=$lang['settings_formulaperm_and']?>&nbsp;</a>&nbsp;
<a href="###" onclick="insertunit(' or ')">&nbsp;<?=$lang['settings_formulaperm_or']?>&nbsp;</a>&nbsp;<br />
<span id="formulapermexp"><?=$formulapermexp?></span>
</div>
<textarea name="formulapermnew" id="formulapermnew" style="width:100%" rows="3" onkeyup="formulaexp()"><?=dhtmlspecialchars($forum['formulaperm'])?></textarea>
</div><script>formulaexp()</script>
<br /><?=$lang['creditwizard_current_formula_notice']?>
</td></tr>
<?

			showtype('', 'bottom');

		}

		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"detailsubmit\" value=\"$lang[submit]\">";
		if($forum['type'] != 'group') {
			echo "&nbsp;&nbsp;&nbsp;<input class=\"button\" type=\"submit\" name=\"saveconfigsubmit\" value=\"".$lang['saveconf']."\"></form>";
		}

	} else {

		if(strlen($namenew) > 50) {
			cpmsg('forums_name_toolong');
		}

		if($formulapermnew && !preg_match("/^(\+|\-|\*|\/|\.|>|<|=|\d|\s|extcredits[1-8]|digestposts|posts|pageviews|oltime|and|or)+$/", $formulapermnew) || !is_null(@eval(preg_replace("/(digestposts|posts|pageviews|oltime|extcredits[1-8])/", "\$\\1", $formulapermnew).';'))) {
			cpmsg('forums_formulaperm_error');
		}

		$formulapermary[0] = $formulapermnew;
		$formulapermary[1] = preg_replace("/(digestposts|posts|pageviews|oltime|extcredits[1-8])/", "\$_DSESSION['\\1']", $formulapermnew);
		$formulapermnew = addslashes(serialize($formulapermary));

		if($type == 'group') {

			if($namenew) {
				$db->query("UPDATE {$tablepre}forums SET name='$namenew',forumcolumns='".intval($forumcolumnsnew)."' WHERE fid='$fid'");
				updatecache('forums');

				cpmsg('forums_edit_succeed', 'admincp.php?action=forumsedit');
			} else {
				cpmsg('forums_edit_name_invalid');
			}

		} else {

			$extensionarray = array();
			foreach(explode(',', $attachextensionsnew) as $extension) {
				if($extension = trim($extension)) {
					$extensionarray[] = $extension;
				}
			}
			$attachextensionsnew = implode(', ', $extensionarray);

			foreach($perms as $perm) {
				${$perm.'new'} = is_array($$perm) && !empty($$perm) ? "\t".implode("\t", $$perm)."\t" : '';
			}

			$fupadd = '';
			if($fupnew != $forum['fup']) {
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE fup='$fid'");
				if($db->num_rows($query)) {
					cpmsg('forums_edit_sub_notnull');
				}

				$fup = $db->fetch_first("SELECT fid, type, inheritedmod FROM {$tablepre}forums WHERE fid='$fupnew'");

				$fupadd = ", type='".($fup['type'] == 'forum' ? 'sub' : 'forum')."', fup='$fup[fid]'";
				$db->query("DELETE FROM {$tablepre}moderators WHERE fid='$fid' AND inherited='1'");
				$query = $db->query("SELECT * FROM {$tablepre}moderators WHERE fid='$fupnew' ".($fup['inheritedmod'] ? '' : "AND inherited='1'"));
				while($mod = $db->fetch_array($query)) {
					$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, displayorder, inherited)
						VALUES ('$mod[uid]', '$fid', '0', '1')");
				}

				$moderators = $tab = '';
				$query = $db->query("SELECT m.username FROM {$tablepre}members m, {$tablepre}moderators mo WHERE mo.fid='$fid' AND mo.inherited='0' AND m.uid=mo.uid ORDER BY mo.displayorder");
				while($mod = $db->fetch_array($query)) {
					$moderators .= $tab.addslashes($mod['username']);
					$tab = "\t";
				}
				$db->query("UPDATE {$tablepre}forumfields SET moderators='$moderators' WHERE fid='$fid'");
			}

                        $statusnew = $supe['status'] && $supe['circlestatus'] && $circlenew ? 2 : $statusnew;
                        $supe_pushsetting[status] = $statusnew == 2 && !$supe_pushsetting[status] ? 1 : $supe_pushsetting[status];
			$supe_pushsettingadd = $supe['status'] ? ", supe_pushsetting='".serialize($supe_pushsetting)."'" : '';

			if(!$allowsharenew && (bool)$allowsharenew != (bool)$forum['allowshare']) {
				$db->query("UPDATE {$tablepre}threads SET blog='0' WHERE fid='$fid'");
			}
			$allowpostspecialtrade = intval($allowpostspecialnew[2]);
			$allowpostspecialnew = bindec(intval($allowpostspecialnew[6]).intval($allowpostspecialnew[5]).intval($allowpostspecialnew[4]).intval($allowpostspecialnew[3]).intval($allowpostspecialnew[2]).intval($allowpostspecialnew[1]));
			$allowspecialonlynew = $allowpostspecialnew ? $allowspecialonlynew : 0;
			$forumcolumnsnew = intval($forumcolumnsnew);
			$threadcachesnew = max(0, min(100, intval($threadcachesnew)));
			$subforumsindexnew = $subforumsindexnew == -1 ? 0 : ($subforumsindexnew == 0 ? 2 : 1);
			$simplenew = bindec(sprintf('%02d', decbin($defaultorderfieldnew)).$defaultordernew.sprintf('%02d', decbin($subforumsindexnew)).'00'.$simplenew);

			$db->query("UPDATE {$tablepre}forums SET status='$statusnew', name='$namenew', styleid='$styleidnew', allowshare='$allowsharenew', alloweditpost='$alloweditpostnew',
				allowpostspecial='$allowpostspecialnew', allowspecialonly='$allowspecialonlynew', allowpaytoauthor='$allowpaytoauthornew', allowhtml='$allowhtmlnew', allowbbcode='$allowbbcodenew', allowimgcode='$allowimgcodenew', allowmediacode='$allowmediacodenew',
				allowsmilies='$allowsmiliesnew', alloweditrules='$alloweditrulesnew', modnewposts='$modnewpostsnew',
				recyclebin='$recyclebinnew', jammer='$jammernew', allowanonymous='$allowanonymousnew', forumcolumns='$forumcolumnsnew', threadcaches='$threadcachesnew',
				simple='$simplenew', disablewatermark='$disablewatermarknew', autoclose='".intval($autoclosenew * $autoclosetimenew)."' $fupadd
				WHERE fid='$fid'");

			$query = $db->query("SELECT fid FROM {$tablepre}forumfields WHERE fid='$fid'");
			if(!($db->num_rows($query))) {
				$db->query("INSERT INTO {$tablepre}forumfields (fid)
					VALUES ('$fid')");
			}

			foreach(array('post', 'reply', 'digest', 'postattach', 'getattach') AS $item) {
				if(${$item.'creditsnew'}) {
					foreach(${$item.'creditsnew'} AS $i => $v) {
						if($v == '') {
							unset(${$item.'creditsnew'}[$i]);
						} else {
							$v = intval($v);
							${$item.'creditsnew'}[$i]  = $v < -99 ? -99 : $v;
							${$item.'creditsnew'}[$i]  = $v > 99 ? 99 : $v;
						}
					}
				}
				${$item.'creditsnew'} = ${$item.'creditsnew'} ? addslashes(serialize(${$item.'creditsnew'})) : '';
			}

			$threadtypesnew['types'] = $threadtypesnew['flat'] = $threadtypes['selectbox'] = $threadtypes['special'] = $threadtypes['show'] = array();

			if($allowthreadtypes && $forum['status'] != 2) {
				if(is_array($newname) && $newname) {
					$newname = array_unique($newname);
					if($newname) {
						foreach($newname AS $key => $val) {
							$val = trim($val);
							if($val) {
								$newtypeid = $db->result_first("SELECT typeid FROM {$tablepre}threadtypes WHERE name='$val'");
								if(!$newtypeid) {
									$db->query("INSERT INTO	{$tablepre}threadtypes (name, description) VALUES
										('$val', '".dhtmlspecialchars(trim($newdescription[$key]))."')");
									$newtypeid = $db->insert_id();
								}
								if($newoptions[$key] == 1) {
									$threadtypesnew['types'][$newtypeid] = $threadtypesnew['flat'][$newtypeid] = $val;
								} elseif($newoptions[$key] == 2) {
									$threadtypesnew['types'][$newtypeid] = $threadtypesnew['selectbox'][$newtypeid] = $val;
								}
							}
						}
					}
					$threadtypesnew['status'] = 1;
				} else {
					$newname = array();
				}
				if($threadtypesnew['status']) {
					if(is_array($threadtypesnew['options']) && $threadtypesnew['options']) {

						$typeids = '0';
						foreach($threadtypesnew['options'] as $key => $val) {
							$typeids .= $val ? ', '.intval($key) : '';
						}

						$query = $db->query("SELECT * FROM {$tablepre}threadtypes WHERE typeid IN ($typeids) ORDER BY displayorder");
						while($type = $db->fetch_array($query)) {
							if($threadtypesnew['options'][$type['typeid']] == 1) {
								$threadtypesnew['types'][$type['typeid']] = $threadtypesnew['flat'][$type['typeid']] = $type['name'];
							} elseif($threadtypesnew['options'][$type['typeid']] == 2) {
								$threadtypesnew['types'][$type['typeid']] = $threadtypesnew['selectbox'][$type['typeid']] = $type['name'];
							}
							$threadtypesnew['special'][$type['typeid']] = $type['special'];
							$threadtypesnew['expiration'][$type['typeid']] = $type['expiration'];
							$threadtypesnew['show'][$type['typeid']] = $threadtypesnew['options']['show'][$type['typeid']] ? 1 : 0;
							$threadtypesnew['typemodelid'][$type['typeid']] = $type['modelid'];
						}
					}
					$threadtypesnew = $threadtypesnew['types'] ? addslashes(serialize(array
						(
						'required' => (bool)$threadtypesnew['required'],
						'listable' => (bool)$threadtypesnew['listable'],
						'prefix' => (bool)$threadtypesnew['prefix'],
						'types' => $threadtypesnew['types'],
						'selectbox' => $threadtypesnew['selectbox'],
						'flat' => $threadtypesnew['flat'],
						'special' => $threadtypesnew['special'],
						'show' => $threadtypesnew['show'],
						'expiration' => $threadtypesnew['expiration'],
						'modelid' => $threadtypesnew['typemodelid'],
						))) : '';
				} else {
					$threadtypesnew = '';
				}
				$threadtypesadd = "threadtypes='$threadtypesnew',";

				if($typemodel) {
					$query = $db->query("SELECT id, name FROM {$tablepre}typemodels WHERE id IN (".implodeids($typemodel).") ORDER BY displayorder");
					while($model = $db->fetch_array($query)) {
						$threadtypemodel[$model['id']]['name'] = $model['name'];
					}
					$threadtypemodeladd = addslashes(serialize($threadtypemodel));
				}

			} else {
				$threadtypesadd = $threadtypemodeladd = '';
			}

			if(!empty($tradetypes) && is_array($tradetypes) && $allowpostspecialtrade) {
				if(count($tradetypes) == count($tradetypesnew)) {
					$tradetypesnew = '';
				} else {
					$tradetypesnew = addslashes(serialize($tradetypesnew));
				}
			} else {
				$tradetypesnew = '';
			}

			$modrecommendnew['num'] = $modrecommendnew['num'] ? intval($modrecommendnew['num']) : 10;
			$modrecommendnew['cachelife'] = $modrecommendnew['cachelife'] ? intval($modrecommendnew['cachelife']) : 900;
			$modrecommendnew['maxlength'] = $modrecommendnew['maxlength'] ? intval($modrecommendnew['maxlength']) : 0;
			$modrecommendnew['dateline'] = $modrecommendnew['dateline'] ? intval($modrecommendnew['dateline']) : 0;
			$modrecommendnew = $modrecommendnew && is_array($modrecommendnew) ? addslashes(serialize($modrecommendnew)) : '';
			$db->query("UPDATE {$tablepre}forumfields SET description='$descriptionnew', icon='$iconnew', password='$passwordnew', redirect='$redirectnew', rules='$rulesnew',
				attachextensions='$attachextensionsnew', $threadtypesadd postcredits='$postcreditsnew', replycredits='$replycreditsnew', digestcredits='$digestcreditsnew',
				postattachcredits='$postattachcreditsnew', getattachcredits='$getattachcreditsnew', viewperm='$viewpermnew', postperm='$postpermnew', replyperm='$replypermnew', tradetypes='$tradetypesnew', typemodels='$threadtypemodeladd',
				getattachperm='$getattachpermnew', postattachperm='$postattachpermnew', formulaperm='$formulapermnew', modrecommend='$modrecommendnew', keywords='$keywordsnew'$supe_pushsettingadd WHERE fid='$fid'");

			if($modrecommendnew && !$modrecommendnew['sort']) {
				require_once DISCUZ_ROOT.'./include/forum.func.php';
				recommendupdate($fid, $modrecommendnew, '1');
			}

			if($statusnew == 0) {
				$db->query("UPDATE {$tablepre}forums SET status='$statusnew' WHERE fup='$fid'", 'UNBUFFERED');
			}

			updatecache('forums');

			if(submitcheck('saveconfigsubmit') && $type != 'group') {
				$projectid = intval($projectid);
				dheader("Location: {$boardurl}admincp.php?action=projectadd&id=$fid&type=forum&projectid=$projectid");
			} else {
				cpmsg('forums_edit_succeed', 'admincp.php?action=forumsedit');
			}
		}

	}

} elseif($action == 'forumdelete') {

	if($ajax) {
		ob_end_clean();
		require_once DISCUZ_ROOT.'./include/post.func.php';
		$tids = 0;

		$total = intval($total);
		$pp = intval($pp);
		$currow = intval($currow);

		$query = $db->query("SELECT tid FROM {$tablepre}threads WHERE fid='$fid' LIMIT $pp");
		while($thread = $db->fetch_array($query)) {
			$tids .= ','.$thread['tid'];
		}

		if($tids) {
			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($tids)");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			}

			foreach(array('threads', 'threadsmod', 'relatedthreads', 'posts', 'polls', 'polloptions', 'trades', 'activities', 'activityapplies', 'debate', 'debateposts', 'videos', 'attachments', 'favorites', 'mythreads', 'myposts', 'subscriptions', 'typeoptionvars', 'forumrecommend') as $value) {
				$db->query("DELETE FROM {$tablepre}$value WHERE tid IN ($tids)", 'UNBUFFERED');
			}
		}

		if($currow + $pp > $total) {
			$db->query("DELETE FROM {$tablepre}forums WHERE fid='$fid'");
			$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='$fid'");
			$db->query("DELETE FROM {$tablepre}moderators WHERE fid='$fid'");
			$db->query("DELETE FROM {$tablepre}access WHERE fid='$fid'");
			echo 'TRUE';
			exit;
		}

		updatecache('forums');
		echo 'GO';
		exit;

	} else {

		if($finished) {

			cpmsg('forums_delete_succeed', 'admincp.php?action=forumsedit');

		}

		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}forums WHERE fup='$fid'")) {
			cpmsg('forums_delete_sub_notnull');
		}

		if(!$confirmed) {

			cpmsg('forums_delete_confirm', "admincp.php?action=forumdelete&fid=$fid", 'form');

		} else {

			$threads = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE fid='$fid'");

			echo "
			<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"6\"><tr><td>
			<br /><br /><br /><br /><br /><br />
			<table width=\"500\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" class=\"tableborder\">
			<tr class=\"header\"><td>".$lang['discuz_message']."</td></tr><tr><td class=\"altbg2\"><div align=\"center\">
			<form method=\"post\" action=\"admincp.php?action=forumdelete&fid=2\"><input type=\"hidden\" name=\"formhash\" value=\"6a47c68f\">
			<br /><br /><br />".$lang['forums_delete_alarm']."<br /><br />
			<div class=\"process\" >
				<div id=\"percent\" class=\"processbg\">0%</div>
			</div>
			<br /><br /><br /><br /></div><br /><br />
			</td></tr></table>
			<br /><br /><br />
			</td></tr></table>
			";

			echo "<div id=\"statusid\" style=\"display:none\"></div>";
			echo "<script src=\"include/javascript/ajax.js\"></script>";
			echo "

			<script>
				var xml_http_building_link = '".$lang['xml_http_building_link']."';
				var xml_http_sending = '".$lang['xml_http_sending']."';
				var xml_http_loading = '".$lang['xml_http_loading']."';
				var xml_http_load_failed = '".$lang['xml_http_load_failed']."';
				var xml_http_data_in_processed = '".$lang['xml_http_data_in_processed']."';
				function forumsdelete(url, total, pp, currow) {

					var x = new Ajax('HTML', 'statusid');
					x.get(url+'&ajax=1&pp='+pp+'&total='+total+'&currow='+currow, function(s) {
						if(s != 'GO') {
							location.href = 'admincp.php?action=forumdelete&finished=1';
						}

						currow += pp;
						var percent = ((currow / total) * 100).toFixed(0);
						percent = percent > 100 ? 100 : percent;
						document.getElementById('percent').innerHTML = percent+'%';
						document.getElementById('percent').style.backgroundPosition = '-'+percent+'%';

						if(currow < total) {
							forumsdelete(url, total, pp, currow);
						}
					});
				}
				forumsdelete('admincp.php?action=forumdelete&fid=$fid&confirmed=1', $threads, 2000, 0);
			</script>
			";
		}
	}

} elseif($action == 'forumrules') {

	shownav('menu_forums_rules');

	if(empty($fid)) {

		$forums = "<option value=\"\">$lang[none]</option>";

		if($adminid == 2) {
			$query = $db->query("SELECT fid, name FROM {$tablepre}forums
				WHERE alloweditrules>'0' AND type IN ('forum', 'sub')");
		} else {
			$query = $db->query("SELECT f.fid, f.name, m.uid FROM {$tablepre}forums f
				LEFT JOIN {$tablepre}moderators m ON m.uid='$discuz_uid' AND m.fid=f.fid
				WHERE alloweditrules>'0' AND f.type IN ('forum', 'sub')");
		}

		while($forum = $db->fetch_array($query)) {
			if($forum['uid'] || $adminid == 2) {
				$forums .= "<option value=\"$forum[fid]\">".strip_tags($forum['name'])."</option>";
			}
		}

		if($forums) {
			$forums = '<select onchange="window.location=(\'admincp.php?action=forumrules&amp;fid=\'+this.options[this.selectedIndex].value);">'.$forums.'</select>';
		} else {
			cpmsg('forums_rules_nopermission');
		}

?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['forums_edit']?></td></tr>
<tr class="altbg2">
<td><?=$lang['forum']?>:</td><td><?=$forums?></td></tr>
</table><br /><br />
<?

	} else {

		$access = 0;
		if($adminid == 2) {
			$access = 1;
		} elseif($adminid == 3) {
			$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE uid='$discuz_uid' AND fid='$fid'");
			$access = $db->num_rows($query) ? 1 : 0;
		}

		$forum = $db->fetch_first("SELECT f.fid, f.name, f.alloweditrules, ff.rules FROM {$tablepre}forums f
			LEFT JOIN {$tablepre}forumfields ff USING (fid)
			WHERE f.fid='$fid' AND alloweditrules>'0' AND type IN ('forum', 'sub')");

		if(!$access || !$forum) {
			cpmsg('forums_rules_nopermission');
		}

		if(!submitcheck('rulessubmit')) {

			$comment = $lang[($forum['alloweditrules'] == 1 ? 'forums_edit_edit_rules_html_no' : 'forums_edit_edit_rules_html_yes')];

?>
<form method="post" action="admincp.php?action=forumrules&fid=<?=$fid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['forums_edit']?> - <?=$forum['name']?></td></tr>
<tr class="altbg2"><td valign="top"><span class="bold"><?=$lang['forums_edit_rules']?></span><br /><?=$comment?></td>
<td><textarea name="rulesnew" rows="5" cols="60"><?=dhtmlspecialchars($forum['rules'])?></textarea></td></tr>
</table><br /><center>
<input class="button" type="submit" name="rulessubmit" value="<?=$lang['submit']?>">
</center></form><br />
<?

		} else {

			if($forum['alloweditrules'] != 2) {
				$rulesnew = dhtmlspecialchars($rulesnew);
			}

			$db->query("UPDATE {$tablepre}forumfields SET rules='$rulesnew' WHERE fid='$fid'");

			cpmsg('forums_rules_succeed');

		}

	}

} elseif($action == 'forumcopy') {

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	$source = intval($source);
	$sourceforum = $_DCACHE['forums'][$source];

	if(empty($sourceforum) || $sourceforum['type'] == 'group') {
		cpmsg('forums_copy_source_invalid');
	}

	$optgroups = array
		(
		'normal'	=> array('modnewposts', 'recyclebin', 'allowshare', 'allowhtml', 'allowbbcode', 'allowimgcode', 'allowmediacode', 'allowsmilies', 'jammer', 'allowanonymous', 'disablewatermark', 'allowpostspecial'),
		'credits'	=> array('postcredits', 'replycredits'),
		'access'	=> array('password', 'viewperm', 'postperm', 'replyperm', 'getattachperm' ,'postattachperm', 'formulaperm'),
		'misc'		=> array('threadtypes', 'attachextensions', 'modrecommend', 'tradetypes')
		);

	if(!submitcheck('copysubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		$forumselect = '<select name="target[]" size="10" multiple="multiple" style="width: 80%">'.forumselect().'</select>';
		$optselect = '<select name="options[]" size="10" multiple="multiple" style="width: 80%">';

		foreach($optgroups as $optgroup => $options) {
			$optselect .= '<optgroup label="'.$lang['forums_copy_optgroups_'.$optgroup]."\">\n";
			foreach($options as $option) {
				$optselect .= "<option value=\"$option\">".$lang['forums_copy_options_'.$option]."</option>\n";
			}
		}
		$optselect .= '</select>';
		showtips('forums_copy_tips');

?>
<form method="post" action="admincp.php?action=forumcopy">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="source" value="<?=$source?>">
<?

		showtype($lang['forums_copy'].' - '.$lang['forums_copy_source'].' - '.$sourceforum['name'], 'top');
		showsetting('forums_copy_target', '', '', $forumselect);
		showsetting('forums_copy_options', '', '', $optselect);
		showtype('', 'bottom');
		echo "<br /><br /><center><input class=\"button\" type=\"submit\" name=\"copysubmit\" value=\"$lang[submit]\"></form>";

	} else {

		$fids = $comma = '';
		if(is_array($target) && count($target)) {
			foreach($target as $fid) {
				if(($fid = intval($fid)) && $fid != $source ) {
					$fids .= $comma.$fid;
					$comma = ',';
				}
			}
		}
		if(empty($fids)) {
			cpmsg('forums_copy_target_invalid');
		}

		$forumoptions = array();
		if(is_array($options) && !empty($options)) {
			foreach($options as $option) {
				if($option = trim($option)) {
					if(in_array($option, $optgroups['normal'])) {
						$forumoptions['forums'][] = $option;
					} elseif(in_array($option, $optgroups['misc']) || in_array($option, $optgroups['credits']) || in_array($option, $optgroups['access'])) {
						$forumoptions['forumfields'][] = $option;
					}
				}
			}
		}

		if(empty($forumoptions)) {
			cpmsg('forums_copy_options_invalid');
		}

		foreach(array('forums', 'forumfields') as $table) {
			if(is_array($forumoptions[$table]) && !empty($forumoptions[$table])) {
				$sourceforum = $db->fetch_first("SELECT ".implode($forumoptions[$table],',')." FROM {$tablepre}$table WHERE fid='$source'");
				if(!$sourceforum) {
					cpmsg('forums_copy_source_invalid');
				}

				$updatequery = 'fid=fid';
				foreach($sourceforum as $key => $val) {
					$updatequery .= ", $key='".addslashes($val)."'";
				}
				$db->query("UPDATE {$tablepre}$table SET $updatequery WHERE fid IN ($fids)");
			}
		}

		updatecache('forums');
		cpmsg('forums_copy_succeed', 'admincp.php?action=forumsedit');

	}
} elseif($action == 'forumrecommend') {

	shownav('forums_recommend');
	if(empty($fid)) {
		if($adminid == 3) {
			$query = $db->query("SELECT f.name, f.fid FROM {$tablepre}moderators m, {$tablepre}forums f
				WHERE m.uid='$discuz_uid' AND m.fid=f.fid
				ORDER BY f.displayorder");
			while($forum = $db->fetch_array($query)) {
				$forumlist[] = $forum;
			}
		} else {
			$query = $db->query("SELECT name, fid FROM {$tablepre}forums WHERE type!='group' ORDER BY displayorder");
			while($forum = $db->fetch_array($query)) {
				$forumlist[] = $forum;
			}
		}

		$forums = "<option value=\"\">$lang[none]</option>";
		foreach($forumlist as $forum) {
			$forums .= "<option value=\"$forum[fid]\">".strip_tags($forum['name'])."</option>";
		}

		if($forums) {
			$forums = '<select onchange="window.location=(\'admincp.php?action=forumrecommend&amp;fid=\'+this.options[this.selectedIndex].value);">'.$forums.'</select>';
		} else {
			cpmsg('forums_recommend_error');
		}
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['forums_recommend']?></td></tr>
<tr class="altbg2">
<td><?=$lang['forum']?>:</td><td><?=$forums?></td></tr>
</table><br /><center>
</center><br />
<?

	} else {

		if(!submitcheck('recommendsubmit')) {

			$useradd = '';
			if($adminid == 3) {
				if(!$db->fetch_first("SELECT * FROM {$tablepre}moderators WHERE uid='$discuz_uid' AND fid='$fid' ORDER BY displayorder")) {
					cpmsg('forums_recommend_error');
				}
				$useradd = "AND moderatorid='$discuz_uid'";
			}


			require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

			$page = max(1, intval($page));
			$start_limit = ($page - 1) * $tpp;

			$threadcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}forumrecommend WHERE fid='$fid' $useradd");
			$multipage = multi($threadcount, $tpp, $page, "admincp.php?action=forumrecommend&fid=$fid&page=$page");

			$threadlist = '';
			$query = $db->query("SELECT f.*, m.username, m.uid
				FROM {$tablepre}forumrecommend f
				LEFT JOIN {$tablepre}members m ON f.moderatorid=m.uid
				WHERE f.fid='$fid' $useradd LIMIT $start_limit,$tpp");
			while($thread = $db->fetch_array($query)) {
				$thread['author'] = $thread['author'] ? $thread['author'] : $lang['anonymous'];
				$thread['username'] = $thread['username'] ? "<a href=\"space.php?uid=$thread[uid]\" target=\"_blank\">$thread[username]</a>" : 'System';
				$threadlist .= "<tr align=\"center\">\n".
					"<td class=\"altbg1\"><input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$thread[tid]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"3\" name=\"displayorder[$thread[tid]]\" value=\"$thread[displayorder]\"></td>\n".
					"<td class=\"altbg1\">".$_DCACHE['forums'][$thread['fid']]['name']."</td>\n".
					"<td class=\"altbg2\"><a href=\"viewthread.php?tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a></td>\n".
					"<td class=\"altbg1\"><a href=\"space.php?uid=$thread[authorid]\" target=\"_blank\">$thread[author]</td>\n".
					"<td class=\"altbg2\">$thread[username]</td>\n".
					"<td class=\"altbg1\">".($thread['expiration'] ? gmdate("$dateformat $timeformat", $thread['expiration'] + ($timeoffset * 3600)) : $lang['nolimit'])."</td>\n";
			}

?>
<form method="post" action="admincp.php?action=forumrecommend">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="fid" value="<?=$fid?>">
<input type="hidden" name="page" value="<?=$page?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="10"><?=$lang['forums_recommend']?></td></tr>
<tr align="center" class="category">
<td width="5%"><input type="checkbox" name="chkall" class="checkbox" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td width="8%"><?=$lang['display_order']?></td><td width="12%"><?=$lang['forums_recommend_name']?></td><td width="40%"><?=$lang['subject']?></td>
<td width="10%"><?=$lang['author']?></td><td width="10%"><?=$lang['forums_recommend_mod']?></td><td width="15%"><?=$lang['forums_recommend_expiration']?></td></td>
</tr>
<?=$threadlist?>
</table><?=$multipage?><br />
<center><input type="submit" class="button" name="recommendsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

		} else {

			if(is_array($displayorder)) {
				foreach($displayorder as $id =>	$val) {
					$db->query("UPDATE {$tablepre}forumrecommend SET displayorder='$displayorder[$id]' WHERE tid='$id'");
				}
			}

			if(!empty($delete)) {
				$threadtids = array();
				if($adminid > 2) {
					$query = $db->query("SELECT tid FROM {$tablepre}forumrecommend WHERE fid='$fid' AND moderatorid='$discuz_uid' AND tid IN (".implodeids($delete).")");
					while($thread = $db->fetch_array($query)) {
						$threadtids[] = $thread['tid'];
					}
				} else {
					$threadtids = $delete;
				}

				$db->query("DELETE FROM {$tablepre}forumrecommend WHERE tid IN (".implodeids($threadtids).")");
			}

			cpmsg('forums_recommend_succeed', "admincp.php?action=forumrecommend&amp;fid=$fid&amp;page=$page");

		}
	}
}

?>