<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: misc.inc.php 11201 2007-11-09 03:23:03Z chenchen $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'onlinelist') {

	if(!submitcheck('onlinesubmit')) {

		$listarray = array();
		$query = $db->query("SELECT * FROM {$tablepre}onlinelist");
		while($list = $db->fetch_array($query)) {
			$list['title'] = dhtmlspecialchars($list['title']);
			$listarray[$list['groupid']] = $list;
		}

		$onlinelist = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type<>'member'");
		$group = array('groupid' => 0, 'grouptitle' => 'Member');
		do {
			$onlinelist .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"3\" name=\"displayordernew[$group[groupid]]\" value=\"{$listarray[$group[groupid]][displayorder]}\"></td>\n".
				"<td class=\"altbg2\">".($group['groupid'] <= 8 ? $lang['usergroups_system_'.$group['groupid']] : $group['grouptitle'])."</td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"15\" name=\"titlenew[$group[groupid]]\" value=\"".($listarray[$group['groupid']]['title'] ? $listarray[$group['groupid']]['title'] : $group['grouptitle'])."\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"20\" name=\"urlnew[$group[groupid]]\" value=\"{$listarray[$group[groupid]][url]}\">\n".
				($listarray[$group['groupid']]['url'] ? "<img src=\"images/common/{$listarray[$group['groupid']]['url']}\">" : '')."</td></tr>\n";
		} while($group = $db->fetch_array($query));

		shownav('menu_misc_onlinelist');
		showtips('onlinelist_tips');

?>
<form method="post" action="admincp.php?action=onlinelist">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><?=$lang['display_order']?></td><td><?=$lang['usergroups_title']?></td><td><?=$lang['usergroups_title']?></td><td><?=$lang['onlinelist_image']?></td></tr>
<?=$onlinelist?></table><br />
<center><input class="button" type="submit" name="onlinesubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($urlnew)) {
			$db->query("DELETE FROM {$tablepre}onlinelist");
			foreach($urlnew as $id => $url) {
				$url = trim($url);
				if($id == 0 || $url) {
					$db->query("INSERT INTO {$tablepre}onlinelist (groupid, displayorder, title, url)
						VALUES ('$id', '$displayordernew[$id]', '$titlenew[$id]', '$url')");
				}
			}
		}

		updatecache('onlinelist');
		cpmsg('onlinelist_succeed', 'admincp.php?action=onlinelist');

	}

} elseif($action == 'forumlinks') {

	if(!submitcheck('forumlinksubmit')) {

		$forumlinks = '';
		$query = $db->query("SELECT * FROM {$tablepre}forumlinks ORDER BY displayorder");
		while($forumlink = $db->fetch_array($query)) {
			$forumlinks .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$forumlink[id]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"3\" name=\"displayorder[$forumlink[id]]\" value=\"$forumlink[displayorder]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"15\" name=\"name[$forumlink[id]]\" value=\"".dhtmlspecialchars($forumlink[name])."\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"url[$forumlink[id]]\" value=\"$forumlink[url]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"15\" name=\"description[$forumlink[id]]\" value=\"$forumlink[description]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"logo[$forumlink[id]]\" value=\"$forumlink[logo]\"></td></tr>\n";
		}

		shownav('menu_misc_links');
		showtips('forumlinks_tips');

?>
<form method="post" action="admincp.php?action=forumlinks">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['forumlinks_edit']?></td></tr>
<tr align="center" class="category">
<td><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['display_order']?></td><td><?=$lang['forumlinks_edit_name']?></td><td><?=$lang['forumlinks_edit_url']?></td><td><?=$lang['forumlinks_edit_description']?></td>
<td><?=$lang['forumlinks_edit_logo']?></td></tr>
<?=$forumlinks?>
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="3"	name="newdisplayorder"></td>
<td><input type="text" size="15" name="newname"></td>
<td><input type="text" size="15" name="newurl"></td>
<td><input type="text" size="15" name="newdescription"></td>
<td><input type="text" size="15" name="newlogo"></td>
</tr></table><br />
<center><input class="button" type="submit" name="forumlinksubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	{$tablepre}forumlinks WHERE	id IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}forumlinks SET displayorder='$displayorder[$id]', name='$name[$id]', url='$url[$id]',description='$description[$id]',logo='$logo[$id]' WHERE id='$id'");
			}
		}

		if($newname != '') {
			$db->query("INSERT INTO	{$tablepre}forumlinks (displayorder, name, url, description, logo) VALUES ('$newdisplayorder', '$newname', '$newurl', '$newdescription', '$newlogo')");
		}

		updatecache('forumlinks');
		cpmsg('forumlinks_succeed', 'admincp.php?action=forumlinks');

	}

} elseif($action == 'medals') {

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$query = $db->query("SELECT * FROM {$tablepre}medals");
		while($medal = $db->fetch_array($query)) {
			$checkavailable = $medal['available'] ? 'checked' : '';
			$medals .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\" width=\"48\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$medal[medalid]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"30\" name=\"name[$medal[medalid]]\" value=\"$medal[name]\"></td>\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"available[$medal[medalid]]\" value=\"1\" $checkavailable></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"25\" name=\"image[$medal[medalid]]\" value=\"$medal[image]\">\n".
				"<img src=\"images/common/$medal[image]\"></td></tr>\n";
		}

		shownav('menu_misc_medals');
		showtips('medals_tips');

?>
<form method="post" action="admincp.php?action=medals">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['medals_edit']?></td></tr>
<tr align="center" class="category">
<td><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form, 'delete')"><?=$lang['del']?></td>
<td><?=$lang['name']?></td><td><?=$lang['available']?></td><td><?=$lang['medals_image']?></td></tr>
<?=$medals?>
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="30" name="newname"></td>
<td><input class="checkbox" type="checkbox" name="availablenew" value="1"></td>
<td><input type="text" size="25" name="newimage"></td>
</tr></table><br />
<center><input class="button" type="submit" name="medalsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	{$tablepre}medals WHERE medalid IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}medals SET name=".($name[$id] ? '\''.dhtmlspecialchars($name[$id]).'\'' : 'name').", available='$available[$id]', image=".($image[$id] ? '\''.$image[$id].'\'' : 'image')." WHERE medalid='$id'");
			}
		}

		if($newname != '' && $newimage != '') {
			$db->query("INSERT INTO	{$tablepre}medals (name, available, image) VALUES ('".dhtmlspecialchars($newname)."', '$newavailable', '$newimage')");
		}

		updatecache('medals');
		cpmsg('medals_succeed', 'admincp.php?action=medals');
	}

} elseif($action == 'discuzcodes') {

	if(!submitcheck('bbcodessubmit') && !$edit) {

		$discuzcodes = '';
		$query = $db->query("SELECT * FROM {$tablepre}bbcodes");
		while($bbcode = $db->fetch_array($query)) {
			$discuzcodes .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$bbcode[id]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"tagnew[$bbcode[id]]\" value=\"$bbcode[tag]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"25\" name=\"iconnew[$bbcode[id]]\" value=\"$bbcode[icon]\"></td>\n".

				"<td class=\"altbg2\">".($bbcode[icon] ? "<img src=\"images/common/$bbcode[icon]\" border=\"0\"" : ' ')."</td>\n".

				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] ? 'checked' : NULL)."></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=discuzcodes&edit=$bbcode[id]\">[$lang[detail]]</a></td></tr>\n";
		}

		shownav('menu_posting_discuzcodes');
		showtips('discuzcodes_edit_tips');

?>
<form method="post" action="admincp.php?action=discuzcodes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['discuzcodes_edit']?></td></tr>
<tr align="center" class="category">
<td width="5%"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['discuzcodes_tag']?><td><?=$lang['discuzcodes_icon_file']?></td><td><?=$lang['discuzcodes_icon']?></td><td><?=$lang['available']?></td>
<td><?=$lang['edit']?></td></tr>
<?=$discuzcodes?>
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="15" name="newtag"></td>
<td><input type="text" size="25" name="newicon"></td>
<td colspan="3">&nbsp;</td>
</tr></table><br />
<center><input class="button" type="submit" name="bbcodessubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} elseif(submitcheck('bbcodessubmit')) {

		if(is_array($delete)) {
			$ids = '\''.implode('\',\'', $delete).'\'';
			$db->query("DELETE FROM	{$tablepre}bbcodes WHERE id IN ($ids)");
		}

		if(is_array($tagnew)) {
			foreach($tagnew as $id => $val) {
				if(!preg_match("/^[0-9a-z]+$/i", $tagnew[$id]) && strlen($tagnew[$id]) < 20) {
					cpmsg('discuzcodes_edit_tag_invalid');
				}
				$db->query("UPDATE {$tablepre}bbcodes SET tag='$tagnew[$id]', icon='$iconnew[$id]', available='$availablenew[$id]' WHERE id='$id'");
			}
		}

		if($newtag != '') {
			if(!preg_match("/^[0-9a-z]+$/i", $newtag && strlen($newtag) < 20)) {
				cpmsg('discuzcodes_edit_tag_invalid');
			}
			$db->query("INSERT INTO	{$tablepre}bbcodes (tag, icon, available, params, nest)
				VALUES ('$newtag', '$newicon', '0', '1', '1')");
		}

		updatecache(array('bbcodes', 'bbcodes_display'));
		cpmsg('discuzcodes_edit_succeed', 'admincp.php?action=discuzcodes');

	} elseif($edit) {

		$bbcode = $db->fetch_first("SELECT * FROM {$tablepre}bbcodes WHERE id='$edit'");
		if(!$bbcode) {
			cpmsg('undefined_action');
		}

		if(!submitcheck('editsubmit')) {
			$bbcode['prompt'] = str_replace("\t", "\n", $bbcode['prompt']);
			echo "<form method=\"post\" action=\"admincp.php?action=discuzcodes&edit=$edit&formhash=".FORMHASH."\">\n";

			showtype($lang['discuzcodes_edit'].' - '.$bbcode['tag'], 'top');
			showsetting('discuzcodes_edit_tag', 'tagnew', $bbcode['tag'], 'text');
			showsetting('discuzcodes_edit_replacement', 'replacementnew', $bbcode['replacement'], 'textarea');
			showsetting('discuzcodes_edit_example', 'examplenew', $bbcode['example'], 'text');
			showsetting('discuzcodes_edit_explanation', 'explanationnew', $bbcode['explanation'], 'text');
			showsetting('discuzcodes_edit_params', 'paramsnew', $bbcode['params'], 'text');
			showsetting('discuzcodes_edit_prompt', 'promptnew', $bbcode['prompt'], 'textarea');
			showsetting('discuzcodes_edit_nest', 'nestnew', $bbcode['nest'], 'text');
			showtype('', 'bottom');

			echo "<br /><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

		} else {

			$tagnew = trim($tagnew);
			if(!preg_match("/^[0-9a-z]+$/i", $tagnew)) {
				cpmsg('discuzcodes_edit_tag_invalid');
			} elseif($paramsnew < 1 || $paramsnew > 3 || $nestnew < 1 || $nestnew > 3) {
				cpmsg('discuzcodes_edit_range_invalid');
			}
			$promptnew = trim(preg_replace("/\r\n|\r|\n/", "\t", str_replace("\t", '', $promptnew)));

			$db->query("UPDATE {$tablepre}bbcodes SET tag='$tagnew', replacement='$replacementnew', example='$examplenew', explanation='$explanationnew', params='$paramsnew', prompt='$promptnew', nest='$nestnew' WHERE id='$edit'");

			updatecache(array('bbcodes', 'bbcodes_display'));
			cpmsg('discuzcodes_edit_succeed', 'admincp.php?action=discuzcodes');

		}
	}

} elseif($action == 'censor') {

	$page = max(1, intval($page));
	$ppp = 30;

	$emptycensortable = $adminid == 1 ? '<input type="radio" class="radio" name="overwrite" value="2"> '.$lang['censor_batch_add_clear'] : '';
	$addcensors = isset($addcensors) ? trim($addcensors) : '';

	if(isset($do) && $do == 'export' && $adminid == 1) {

		ob_end_clean();
		dheader('Cache-control: max-age=0');
		dheader('Expires: '.gmdate('D, d M Y H:i:s', $timestamp - 31536000).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename=CensorWords.txt');
		dheader('Content-Type: text/plain');

		$query = $db->query("SELECT find, replacement FROM {$tablepre}words ORDER BY id");
		while($censor = $db->fetch_array($query)) {
			$censor['replacement'] = str_replace('*', '', $censor['replacement']) <> '' ? $censor['replacement'] : '';
			echo $censor['find'].($censor['replacement'] != '' ? '='.stripslashes($censor['replacement']) : '')."\n";
		}
		exit();

	} elseif(submitcheck('addcensorsubmit') && $addcensors != '') {
		$oldwords = array();
		if($adminid == 1 && $overwrite == 2) {
			$db->query("TRUNCATE {$tablepre}words");
		} else {
			$query = $db->query("SELECT find, admin FROM {$tablepre}words");
			while($censor = $db->fetch_array($query)) {
				$oldwords[md5($censor['find'])] = $censor['admin'];
			}
			$db->free_result($query);
		}

		$censorarray = explode("\n", $addcensors);
		$updatecount = $newcount = $ignorecount = 0;
		foreach($censorarray as $censor) {
			list($newfind, $newreplace) = array_map('trim', explode('=', $censor));
			$newreplace = $newreplace <> '' ? daddslashes(str_replace("\\\'", '\'', $newreplace), 1) : '**';
			if(strlen($newfind) < 3) {
				$ignorecount ++;
				continue;
			} elseif(isset($oldwords[md5($newfind)])) {
				if($overwrite && ($adminid == 1 || $oldwords[md5($newfind)] == $discuz_userss)) {
					$updatecount ++;
					$db->query("UPDATE {$tablepre}words SET replacement='$newreplace' WHERE `find`='$newfind'");
				} else {
					$ignorecount ++;
				}
			} else {
				$newcount ++;
				$db->query("INSERT INTO	{$tablepre}words (admin, find, replacement) VALUES
					('$discuz_user', '$newfind', '$newreplace')");
				$oldwords[md5($newfind)] = $discuz_userss;
			}
		}
		updatecache('censor');
		cpmsg('censor_batch_add_succeed', "admincp.php?action=$action&page=999999");

	} elseif(!submitcheck('censorsubmit')) {

		$censorwords = '';
		$totalcount = $db->result_first("SELECT count(*) FROM {$tablepre}words");

		$page = $page > ceil($totalcount / $ppp) ? ceil($totalcount / $ppp) : $page;
		$page = max(1, intval($page));

		$startlimit = ($page - 1) * $ppp;

		$multipage = multi($totalcount, $ppp, $page, "admincp.php?action=$action");
		$query = $db->query("SELECT * FROM {$tablepre}words ORDER BY id LIMIT $startlimit, $ppp");
		while($censor =	$db->fetch_array($query)) {
			$censor['replacement'] = stripslashes($censor['replacement']);
			$disabled = $adminid != 1 && $censor['admin'] != $discuz_userss ? 'disabled' : NULL;
			$censorwords .=	"<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"30\" name=\"replace[$censor[id]]\" value=\"$censor[replacement]\" $disabled></td>\n".
				"<td class=\"altbg2\">$censor[admin]</td></tr>\n";
		}

		shownav('menu_posting_censors');
		showtips('censor_tips');

?>
<form method="post" action="admincp.php?action=censor">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="page" value="<?=$page?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="5%"><input class="checkbox" type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['censor_word']?></td><td><?=$lang['censor_replacement']?></td><td><?=$lang['operator']?></td></tr>
<?=$censorwords?>
<tr class="altbg1">
<td align="center"><?=$lang['add_new']?></td>
<td align="center"><input type="text" size="30"	name="newfind"></td>
<td align="center"><input type="text" size="30"	name="newreplace"></td>
<td>&nbsp;</td>
</tr></table><br />
<?=$multipage?>
<center><input class="button" type="submit" name="censorsubmit" value="<?=$lang['submit']?>"></center>
</form><br />
<form method="post" action="admincp.php?action=censor">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="page" value="<?=$page?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['censor_batch_add']?></td></tr>
<tr>
<td width="125" class="altbg1"><?=$lang['censor_batch_add_tips']?></td>
<td  class="altbg2">
<textarea style="width: 90%" rows="10" cols="80" name="addcensors"></textarea><br />
<?=$emptycensortable?>
<input type="radio" class="radio" name="overwrite" value="1" > <?=$lang['censor_batch_add_overwrite']?>
<br />
<input type="radio" class="radio" name="overwrite" value="0" checked> <?=$lang['censor_batch_add_no_overwrite']?>
<br />
</td>
</tr></table><br />
<center><input class="button" type="submit" name="addcensorsubmit" value="<?=$lang['submit']?>"></center>
</form><br />

<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}words WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if(is_array($find)) {
			foreach($find as $id =>	$val) {
				$find[$id]  = $val = trim(str_replace('=', '', $find[$id]));
				if(strlen($val) < 3) {
					cpmsg('censor_keywords_tooshort');
				}
				$replace[$id] = daddslashes(str_replace("\\\'", '\'', $replace[$id]), 1);
				$db->query("UPDATE {$tablepre}words SET find='$find[$id]', replacement='$replace[$id]' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
			}
		}

		$newfind = trim(str_replace('=', '', $newfind));
		$newreplace  = trim($newreplace);

		if($newfind != '') {
			if(strlen($newfind) < 3) {
				cpmsg('censor_keywords_tooshort');
			}
			$newreplace = daddslashes(str_replace("\\\'", '\'', $newreplace), 1);
			if($oldcenser = $db->fetch_first("SELECT admin FROM {$tablepre}words WHERE find='$newfind'")) {
				cpmsg('censor_keywords_existence');
			} else {
				$db->query("INSERT INTO	{$tablepre}words (admin, find, replacement) VALUES
					('$discuz_user', '$newfind', '$newreplace')");
			}
		}

		updatecache('censor');
		cpmsg('censor_succeed', 'admincp.php?action=censor&page='.$page);

	}

} elseif($action == 'icons') {

	if(!submitcheck('iconsubmit')) {

		$icons = $newimages = '';
		$imgfilter =  array();
		$query = $db->query("SELECT * FROM {$tablepre}smilies WHERE type='icon' ORDER BY displayorder");
		while($smiley =	$db->fetch_array($query)) {
			$icons	.= "<tr	align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\"></td>\n".
				"<td class=\"altbg1\" colspan=\"2\">$smiley[url]</td>\n".
				"<td class=\"altbg2\"><img src=\"images/icons/$smiley[url]\"></td></tr>\n";
			$imgfilter[] = $smiley[url];
		}

		$newid = 0;
		$imgextarray = array('jpg', 'gif');
		$iconsdir = dir(DISCUZ_ROOT.'./images/icons');
		while($entry = $iconsdir->read()) {
			if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && is_file(DISCUZ_ROOT.'./images/icons/'.$entry)) {
				$newimages .= "<tr align=\"center\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"addcheck[$newid]\" class=\"checkbox\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"2\" name=\"adddisplayorder[$newid]\" value=\"0\"></td>\n".
					"<td class=\"altbg1\" colspan=\"2\"><input type=\"text\" size=\"35\" name=\"addurl[$newid]\" value=\"$entry\" readonly></td>\n".
					"<td class=\"altbg2\"><img src=\"images/icons/$entry\"></td></tr>\n";
				$newid ++;
			}
		}
		$iconsdir->close();
		$newimages = $newimages ? $newimages : '<tr><td class="altbg1" colspan="5">'.$lang['info_tips'].'</td></tr>';

		shownav('menu_thread_icon');

?>
<form method="post" action="admincp.php?action=icons">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['smilies_edit_icon']?></td></tr>
<tr align="center" class="category">
<td width="50"><input type="checkbox" name="chkall" onclick="checkall(this.form, 'delete')" class="checkbox"><?=$lang['del']?></td><td><?=$lang['display_order']?></td>
<td colspan="2"><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$icons?>
</table><br />
<center><input class="button" type="submit" name="iconsubmit" value="<?=$lang['submit']?>"></center><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['icon_add']?></td></tr>
<tr align="center" class="category">
<td width="50"><input type="checkbox" name="chkall2" onclick="checkall(this.form, 'addcheck', 'chkall2')" class="checkbox"><?=$lang['enabled']?></td><td><?=$lang['display_order']?></td>
<td colspan="2"><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$newimages?>
</table><br />
<center><input class="button" type="submit" name="iconsubmit" value="<?=$lang['submit']?>"></center><br />
</form>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}smilies WHERE id IN ($ids)");
		}

		if(is_array($displayorder)) {
			foreach($displayorder AS $id => $val) {
				$displayorder[$id] = intval($displayorder[$id]);
				$db->query("UPDATE {$tablepre}smilies SET displayorder='$displayorder[$id]' WHERE id='$id'");
			}
		}

		if(is_array($addurl)) {
			foreach($addurl as $k => $v) {
				if($addcheck[$k]) {
					$query = $db->query("INSERT INTO {$tablepre}smilies (displayorder, type, url)
						VALUES ('{$adddisplayorder[$k]}', 'icon', '$addurl[$k]')");
				}
			}
		}

		updatecache('icons');

		cpmsg('thread_icon_succeed', "admincp.php?action=icons");
	}

} elseif($action == 'attachtypes') {

	if(!submitcheck('typesubmit')) {

		$attachtypes = '';
		$query = $db->query("SELECT * FROM {$tablepre}attachtypes");
		while($type = $db->fetch_array($query)) {
			$attachtypes .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\"></td></tr>\n";
		}

		shownav('menu_posting_attachtypes');
		showtips('attachtypes_tips');

?>
<form method="post" action="admincp.php?action=attachtypes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="5%"><input class="checkbox" type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['attachtypes_ext']?></td><td><?=$lang['attachtypes_maxsize']?></td></tr>
<?=$attachtypes?>
<tr class="altbg1">
<td align="center"><?=$lang['add_new']?></td>
<td align="center"><input type="text" size="10"	name="newextension"></td>
<td align="center"><input type="text" size="15"	name="newmaxsize"></td>
</tr></table><br />
<center><input class="button" type="submit" name="typesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}attachtypes WHERE id IN ($ids)");
		}

		if(is_array($extension)) {
			foreach($extension as $id => $val) {
				$db->query("UPDATE {$tablepre}attachtypes SET extension='$extension[$id]', maxsize='$maxsize[$id]' WHERE id='$id'");
			}
		}

		if($newextension != '') {
			$newextension = trim($newextension);
			if($db->result_first("SELECT id FROM {$tablepre}attachtypes WHERE extension='$newextension'")) {
				cpmsg('attachtypes_duplicate');
			}
			$db->query("INSERT INTO	{$tablepre}attachtypes (extension, maxsize) VALUES
					('$newextension', '$newmaxsize')");
		}

		cpmsg('attachtypes_succeed', 'admincp.php?action=attachtypes');

	}

} elseif($action == 'crons') {

	if(empty($edit) && empty($run)) {

		if(!submitcheck('cronssubmit')) {

			$crons = '';
			$query = $db->query("SELECT * FROM {$tablepre}crons ORDER BY type DESC");
			while($cron = $db->fetch_array($query)) {
				if(!$supe['status'] && substr($cron['name'], 0, 5) == 'supe_') {
					continue;
				}
				$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';
				foreach(array('weekday', 'day', 'hour', 'minute') as $key) {
					if(in_array($cron[$key], array(-1, ''))) {
						$cron[$key] = '<b>*</b>';
					} elseif($key == 'weekday') {
						$cron[$key] = $lang['crons_week_day_'.$cron[$key]];
					} elseif($key == 'minute') {
						foreach($cron[$key] = explode("\t", $cron[$key]) as $k => $v) {
							$cron[$key][$k] = sprintf('%02d', $v);
						}
						$cron[$key] = implode(',', $cron[$key]);
					}
				}

				$cron['lastrun'] = $cron['lastrun'] ? gmdate("$dateformat<\b\\r>$timeformat", $cron['lastrun'] + $_DCACHE['settings']['timeoffset'] * 3600) : '<b>N/A</b>';
				$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_DCACHE['settings']['timeoffset'] * 3600 < $timestamp ? 'style="color: #ff0000"' : '';
				$cron['nextrun'] = $cron['nextrun'] ? gmdate("$dateformat<\b\\r>$timeformat", $cron['nextrun'] + $_DCACHE['settings']['timeoffset'] * 3600) : '<b>N/A</b>';
				$crons .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$cron[cronid]\" ".($cron['type'] == 'system' ? 'disabled' : '')."></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" name=\"namenew[$cron[cronid]]\" size=\"20\" value=\"$cron[name]\"><br /><b>$cron[filename]</b></td>\n".
					"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$cron[cronid]]\" value=\"1\" ".($cron['available'] ? 'checked' : '')." $disabled></td>\n".
					"<td class=\"altbg2\">".$lang['crons_type_'.$cron['type']]."</td>".
					"<td class=\"altbg1\">$cron[minute]</td>\n".
					"<td class=\"altbg2\">$cron[hour]</td>\n".
					"<td class=\"altbg1\">$cron[day]</td>\n".
					"<td class=\"altbg2\">$cron[weekday]</td>\n".
					"<td class=\"altbg1\">$cron[lastrun]</td>\n".
					"<td class=\"altbg2\" $cron[nextcolor]>$cron[nextrun]</td>\n".
					"<td class=\"altbg1\"><a href=\"admincp.php?action=crons&edit=$cron[cronid]\">[$lang[edit]]</a>".
					($cron['available'] ? " <a href=\"admincp.php?action=crons&run=$cron[cronid]\">[$lang[crons_run]]</a>" : " <span disabled>[$lang[crons_run]]</span>").
					"</td></tr>";
			}

			shownav('menu_misc_crons');
			showtips('crons_tips');

?>
<form method="post" action="admincp.php?action=crons">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="5%"><input class="checkbox" type="checkbox" name="chkall" class="header" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['name']?></td><td><?=$lang['available']?></td><td><?=$lang['type']?></td><td><?=$lang['crons_minute']?></td>
<td width="5%"><?=$lang['crons_hour']?></td><td width="5%"><?=$lang['crons_day']?></td><td width="6%"><?=$lang['crons_week_day']?></td>
<td><?=$lang['crons_last_run']?></td><td><?=$lang['crons_next_run']?></td><td><?=$lang['operation']?></td></tr>
<?=$crons?>
<tr align="center" class="altbg1">
<td><?=$lang['add_new']?></td><td><input type="text" size="20" name="newname"></td><td colspan="9">&nbsp;</td>
</tr></table><br />
<center><input class="button" type="submit" name="cronssubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

		} else {

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM {$tablepre}crons WHERE cronid IN ($ids) AND type='user'");
			}

			if(is_array($namenew)) {
				foreach($namenew as $id => $name) {
					$db->query("UPDATE {$tablepre}crons SET name='".dhtmlspecialchars($namenew[$id])."', available='".$availablenew[$id]."' ".($availablenew[$id] ? '' : ', nextrun=\'0\'')." WHERE cronid='$id'");
				}
			}

			if($newname = trim($newname)) {
				$db->query("INSERT INTO {$tablepre}crons (name, type, available, weekday, day, hour, minute, nextrun)
					VALUES ('".dhtmlspecialchars($newname)."', 'user', '0', '-1', '-1', '-1', '', '$timestamp')");
			}

			$query = $db->query("SELECT cronid, filename FROM {$tablepre}crons");
			while($cron = $db->fetch_array($query)) {
				if(!file_exists(DISCUZ_ROOT.'./include/crons/'.$cron['filename'])) {
					$db->query("UPDATE {$tablepre}crons SET available='0', nextrun='0' WHERE cronid='$cron[cronid]'");
				}
			}

			//updatecache('crons');
			updatecache('settings');
			cpmsg('crons_succeed', 'admincp.php?action=crons');

		}

	} else {

		$cronid = empty($run) ? $edit : $run;
		$cron = $db->fetch_first("SELECT * FROM {$tablepre}crons WHERE cronid='$cronid'");
		if(!$cron) {
			cpmsg('undefined_action');
		}
		$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
		$cron['minute'] = explode("\t", $cron['minute']);

		if(!empty($edit)) {

			if(!submitcheck('editsubmit')) {

				shownav('menu_misc_crons');
				showtips('crons_edit_tips');

				$weekdayselect = $dayselect = $hourselect = $minuteselect = '';

				for($i = 0; $i <= 6; $i++) {
					$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').">".$lang['crons_week_day_'.$i]."</option>";
				}

				for($i = 1; $i <= 31; $i++) {
					$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i</option>";
				}

				for($i = 0; $i <= 23; $i++) {
					$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i</option>";
				}

				for($i = 0; $i < 12; $i++) {
					$minuteselect .= '<select name="minutenew[]"><option value="-1">*</option>';
					for($j = 0; $j <= 59; $j++) {
						$minuteselect .= "<option value=\"$j\" ".($cron['minute'][$i] != '' && $cron['minute'][$i] == $j ? 'selected' : '').">".sprintf("%02d", $j)."</option>";
					}
					$minuteselect .= '</select>'.($i == 5 ? '<br />' : ' ');
				}

				echo "<form method=\"post\" action=\"admincp.php?action=crons&edit=$cronid&formhash=".FORMHASH."\">\n";

				showtype($lang['crons_edit'].' - '.$cron['name'], 'top');
				showsetting('crons_edit_weekday', '', '', "<select name=\"weekdaynew\"><option value=\"-1\">*</option>$weekdayselect</select>");
				showsetting('crons_edit_day', '', '', "<select name=\"daynew\"><option value=\"-1\">*</option>$dayselect</select>");
				showsetting('crons_edit_hour', '', '', "<select name=\"hournew\"><option value=\"-1\">*</option>$hourselect</select>");
				showsetting('crons_edit_minute', '', '', $minuteselect);
				showsetting('crons_edit_filename', 'filenamenew', $cron['filename'], 'text');
				showtype('', 'bottom');

				echo "<br /><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

			} else {

				$daynew = $weekdaynew != -1 ? -1 : $daynew;

				if(is_array($minutenew)) {
					sort($minutenew = array_unique($minutenew));
					foreach($minutenew as $key => $val) {
						if($val < 0 || $var > 59) {
							unset($minutenew[$key]);
						}
					}
					$minutenew = implode("\t", $minutenew);
				} else {
					$minutenew = '';
				}

				if(preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $filenamenew)) {
					cpmsg('crons_filename_illegal');
				} elseif(!is_readable(DISCUZ_ROOT.($cronfile = "./include/crons/$filenamenew"))) {
					cpmsg('crons_filename_invalid');
				} elseif($weekdaynew == -1 && $daynew == -1 && $hournew == -1 && $minutenew == '') {
					cpmsg('crons_time_invalid');
				}

				$db->query("UPDATE {$tablepre}crons SET weekday='$weekdaynew', day='$daynew', hour='$hournew', minute='$minutenew', filename='".trim($filenamenew)."' WHERE cronid='$cronid'");

				updatecache('crons');

				require_once DISCUZ_ROOT.'./include/cron.func.php';
				cronnextrun($cron);

				cpmsg('crons_succeed', 'admincp.php?action=crons');

			}

		} else {

			if(!@include_once DISCUZ_ROOT.($cronfile = "./include/crons/$cron[filename]")) {
				cpmsg('crons_run_invalid');
			} else {
				require_once DISCUZ_ROOT.'./include/cron.func.php';
				cronnextrun($cron);
				cpmsg('crons_run_succeed', 'admincp.php?action=crons');
			}

		}

	}

} elseif($action == 'creditslog') {

	$lpp = empty($lpp) ? 50 : $lpp;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $lpp;

	$keywordadd = !empty($keyword) ? "AND c.fromto LIKE '%$keyword%'" : '';

	$mpurl = "admincp.php?action=$action&keyword=".rawurlencode($keyword)."&lpp=$lpp";
	if(!empty($operations) && is_array($operations)) {
		$operationadd = "AND c.operation IN ('".implode('\',\'', $operations)."')";
		foreach($operations as $operation) {
			$mpurl .= '&operations[]='.rawurlencode($operation);
		}
	} else {
		$operationadd = '';
	}

	$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}creditslog c WHERE 1 $keywordadd $operationadd");

	$multipage = multi($num, $lpp, $page, $mpurl);

	$creditsoperations = '';
	foreach(array('TFR', 'RCV', 'EXC', 'UGP', 'AFD') as $operation) {
		$creditsoperations .= '<input class="checkbox" type="checkbox" name="operations[]" value="'.$operation.'" '.(!empty($operations) && is_array($operations) && in_array($operation, $operations) ? 'checked' : '').'> '.$lang['logs_credit_operation_'.strtolower($operation)].' &nbsp; ';
	}

	$logs = '';
	$total['send'] = $total['receive'] = array();
	$query = $db->query("SELECT c.*, m.username FROM {$tablepre}creditslog c
		LEFT JOIN {$tablepre}members m USING (uid)
		WHERE 1 $keywordadd $operationadd ORDER BY dateline DESC LIMIT $start_limit, $lpp");

	while($log = $db->fetch_array($query)) {
		$total['send'][$log['sendcredits']] += $log['send'];
		$total['receive'][$log['receivecredits']] += $log['receive'];
		$log['dateline'] = gmdate('y-n-j H:i', $log['dateline'] + $timeoffset * 3600);
		$log['operation'] = $lang['logs_credit_operation_'.strtolower($log['operation'])];
		$logs .= "<tr align=\"center\"><td class=\"altbg1\"><a href=\"space.php?action=viewpro&username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]</td>".
			"<td class=\"altbg2\">$log[fromto]</td>".
			"<td class=\"altbg1\">$log[dateline]</td>".
			"<td class=\"altbg2\">".(isset($extcredits[$log['sendcredits']]) ? $extcredits[$log['sendcredits']]['title'].' '.$log['send'].' '.$extcredits[$log['sendcredits']]['unit'] : $log['send'])."</td>".
			"<td class=\"altbg1\">".(isset($extcredits[$log['receivecredits']]) ? $extcredits[$log['receivecredits']]['title'].' '.$log['receive'].' '.$extcredits[$log['receivecredits']]['unit'] : $log['receive'])."</td>".
			"<td class=\"altbg2\">$log[operation]</td></tr>";
	}

	$result = array('send' => array(), 'receive' => array());
	foreach(array('send', 'receive') as $key) {
		foreach($total[$key] as $id => $amount) {
			if(isset($extcredits[$id])) {
				$result[$key][] = $extcredits[$id]['title'].' '.$amount.' '.$extcredits[$id]['unit'];
			}
		}
	}

	shownav('logs_credit');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['logs_credit']?></td></tr>


<form method="post" action="admincp.php?action=creditslog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td width="20%"><?=$lang['logs_lpp']?></td>
<td width="45%"><input type="text" name="lpp" size="40" maxlength="40" value="<?=$lpp?>"></td>
<td width="20%"><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=creditslog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg1"><td><?=$lang['logs_search']?></td><td><input type="text" name="keyword" size="40" value="<?=dhtmlspecialchars($keyword)?>"></td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=creditslog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td><?=$lang['action']?></td><td><?=$creditsoperations?></td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

</table><br /><br />

<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="16%"><?=$lang['username']?></td>
<td width="16%"><?=$lang['logs_credit_fromto']?></td>
<td width="17%"><?=$lang['time']?></td>
<td width="16%"><?=$lang['logs_credit_send']?></td>
<td width="15%"><?=$lang['logs_credit_receive']?></td>
<td width="20%"><?=$lang['action']?></td>
</tr>
<?=$logs?>
<tr class="category" align="right"><td colspan="6"><b><?=$lang['logs_credit_send_total']?></b> <?=implode('; ', $result['send'])?> <b>|</b> <b><?=$lang['logs_credit_receive_total']?></b> <?=implode(', ', $result['receive'])?></td></tr>
</table>

<?=$multipage?>
<?

} elseif($action == 'tags') {

	if(!$tagstatus) {
		cpmsg('tags_not_open', "admincp.php?action=settings&do=functions#subtitle_tags");
	}

	if(submitcheck('tagsubmit') && !empty($tag)) {
		$tagdelete = $tagclose = $tagopen = array();
		foreach($tag as $key => $value) {
			if($value == -1) {
				$tagdelete[] = $key;
			} elseif($value == 1) {
				$tagclose[] = $key;
			} elseif($value == 0) {
				$tagopen[] = $key;
			}
		}

		if($tagdelete) {
			$db->query("DELETE FROM {$tablepre}tags WHERE tagname IN (".implodeids($tagdelete).")", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threadtags WHERE tagname IN (".implodeids($tagdelete).")", 'UNBUFFERED');
		}

		if($tagclose) {
			$db->query("UPDATE {$tablepre}tags SET closed=1 WHERE tagname IN (".implodeids($tagclose).")", 'UNBUFFERED');
		}

		if($tagopen) {
			$db->query("UPDATE {$tablepre}tags SET closed=0 WHERE tagname IN (".implodeids($tagopen).")", 'UNBUFFERED');
		}

		if($tagdelete || $tagclose || $tagopen) {
			updatecache(array('tags_index', 'tags_viewthread'));
		}

		cpmsg('tags_updated', "admincp.php?action=tags&tagsearchsubmit=yes&cins=".intval($cins)."&tagname=".rawurlencode($tagname)."&threadnumlower=".intval($threadnumlower)."&threadnumhigher=".intval($threadnumhigher));

	}

	shownav('menu_posting_tags');

	if(!submitcheck('tagsearchsubmit', 1)) {

	$tagcount[0] = $db->result_first("SELECT count(*) FROM {$tablepre}tags");
	$tagcount[1] = $db->result_first("SELECT count(*) FROM {$tablepre}tags WHERE closed=1");

	include DISCUZ_ROOT.'./forumdata/cache/cache_index.php';

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['menu_posting_tags']?></td></tr>
<tr><td class="altbg2">
<?=$lang['tags_count']?>: <?=$tagcount[0]?> (<?=$lang['tags_status_1']?> <?=$tagcount[1]?>)<br />
<?=$lang['tags_hot']?>: <br /><?=$_DCACHE['tags']?>
</td></tr><table><br />

<form method="post" action="admincp.php?action=tags">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['tags_search']?></td></tr>

<tr><td class="altbg1" width="45%"><?=$lang['tags_tag']?>:</td>
<td align="right" class="altbg2" width="40%">
<?=$lang['case_insensitive']?> <input type="checkbox" name="cins" value="1" class="checkbox">
<br /><input type="text" name="tagname" size="40" value="<?=dhtmlspecialchars($username)?>"></td></tr>

<tr><td class="altbg1" width="45%"><?=$lang['tags_threadnum_lower']?></td>
<td align="right" class="altbg2" width="40%">
<input type="text" name="threadnumlower" size="40" value="<?=dhtmlspecialchars($username)?>"></td></tr>

<tr><td class="altbg1" width="45%"><?=$lang['tags_threadnum_higher']?></td>
<td align="right" class="altbg2" width="40%">
<input type="text" name="threadnumhigher" size="40" value="<?=dhtmlspecialchars($username)?>"></td></tr>

<tr><td class="altbg1" width="45%"><?=$lang['tags_status']?></td>
<td align="right" class="altbg2" width="40%">
<input type="radio" name="status" value="-1" class="radio" checked> <?=$lang['all']?>&nbsp;
<input type="radio" name="status" value="1" class="radio"> <?=$lang['tags_status_1']?>&nbsp;
<input type="radio" name="status" value="0" class="radio"> <?=$lang['tags_status_0']?>
</td></tr>

</table>
<br /><center>
<input name="tagsearchsubmit" class="button" type="submit" value="<?=$lang['tags_search']?>">
</center>
</form>
<?

		} else {
			$tagpp = 100;
			$page = max(1, intval($page));

			$sqladd = $tagname ? ($cins ? '' : 'BINARY')." tagname LIKE '%".str_replace(array('%', '*', '_'), array('\%', '%', '\_'), $tagname)."%'" : '1';
			$sqladd .= $threadnumlower ? " AND total<'".intval($threadnumlower)."'" : '';
			$sqladd .= $threadnumhigher ? " AND total>'".intval($threadnumhigher)."'" : '';
			$sqladd .= $status != -1 ? " AND closed='".intval($status)."'" : '';

			$pagetmp = $page;

			$num = $db->result_first("SELECT count(*) FROM {$tablepre}tags WHERE $sqladd");
			do{
				$query = $db->query("SELECT * FROM {$tablepre}tags WHERE $sqladd ORDER BY total DESC LIMIT ".(($pagetmp - 1) * $tagpp).", $tagpp");
				$pagetmp--;
			} while(!$db->num_rows($query) && $pagetmp);
			$tags = '';
			while($tag = $db->fetch_array($query)) {
				$tags .= '<tr><td class="altbg1">';
				$tags .= '<a href="tag.php?name='.rawurlencode($tag['tagname']).'" target="_blank">'.$tag['tagname'].'</a></td>';
				$tags .= '<td class="altbg2">'.$tag['total'].'</td>';
				$tags .= '<td class="altbg1" style="text-align:center">';
				$tags .= '<input name="tag['.$tag['tagname'].']" type="radio" class="radio" value="-1"> '.$lang['delete'].'&nbsp;';
				$tags .= '<input name="tag['.$tag['tagname'].']" type="radio" class="radio" value="1"'.($tag['closed'] ? ' checked' : '').'> '.$lang['tags_status_1'].'&nbsp;';
				$tags .= '<input name="tag['.$tag['tagname'].']" type="radio" class="radio" value="0"'.(!$tag['closed'] ? ' checked' : '').'> '.$lang['tags_status_0'].'';
				$tags .= '</td>';
				$tags .= '</tr>';
			}

			$multipage = multi($num, $tagpp, $page, "admincp.php?action=tags&tagsearchsubmit=yes&cins=".intval($cins)."&tagname=".rawurlencode($tagname)."&threadnumlower=".intval($threadnumlower)."&threadnumhigher=".intval($threadnumhigher));

?>

<?=$multipage?>
<form method="post" action="admincp.php?action=tags&page=<?=$page?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="tagname" value="<?=htmlspecialchars($tagname)?>">
<input type="hidden" name="threadnumlower" value="<?=intval($threadnumlower)?>">
<input type="hidden" name="threadnumhigher" value="<?=intval($threadnumhigher)?>">
<input type="hidden" name="status" value="<?=intval($status)?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['menu_posting_tags']?></td></tr>
<tr class="category"><td><?=$lang['tags_tag']?></td>
<td><?=$lang['tags_threadnum']?></td><td width="300">
<input class="button" type="button" value="<?=$lang['tags_all_delete']?>" onclick="checkalloption(this.form, '-1')">
<input class="button" type="button" value="<?=$lang['tags_all_close']?>" onclick="checkalloption(this.form, '1')">
<input class="button" type="button" value="<?=$lang['tags_all_open']?>" onclick="checkalloption(this.form, '0')">
</td></tr>
<?=$tags?>
</table>
<?=$multipage?><br />
<center>
<input name="tagsubmit" class="button" type="submit" value="<?=$lang['submit']?>">
</center>
</form>

<?

	}
}

?>