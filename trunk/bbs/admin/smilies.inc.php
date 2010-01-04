<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: smilies.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

if($action == 'smilies' && $export) {
	$smileyarray = $db->fetch_first("SELECT name, directory FROM {$tablepre}imagetypes WHERE typeid='$export' AND type='smiley'");
	if(!$smileyarray) {
		cpheader();
		cpmsg('smilies_export_invalid');
	}

	$smileyarray['smilies'] = array();
	$query = $db->query("SELECT typeid, displayorder, code, url FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$export'");
	while($smiley = $db->fetch_array($query)) {
		$smileyarray['smilies'][] = $smiley;
	}

	$smileyarray['version'] = strip_tags($version);
	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$smiley_export = "# Discuz! Smilies Dump\n".
			"# Version: Discuz! $version\n".
			"# Time: $time\n".
			"# From: $bbname ($boardurl)\n".
			"#\n".
			"# This file was BASE64 encoded\n".
			"#\n".
			"# Discuz! Community: http://www.Discuz.net\n".
			"# Please visit our website for latest news about Discuz!\n".
			"# --------------------------------------------------------\n\n\n".
			wordwrap(base64_encode(serialize($smileyarray)), 50, "\n", 1);

	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($smiley_export));
	dheader('Content-Disposition: attachment; filename=discuz_smilies_'.$smileyarray['name'].'.txt');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));

	echo $smiley_export;
	dexit();
}

cpheader();

if($action == 'smilies' && !$export) {

	if(!submitcheck('smiliessubmit') && !submitcheck('importsubmit') && !$edit && !$export) {

		$smileyselect = $smileydirs = '';
		$dirfilter = array();
		$query = $db->query("SELECT * FROM {$tablepre}imagetypes WHERE type='smiley' ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$squery = $db->query("SELECT COUNT(*) FROM {$tablepre}smilies WHERE typeid='$type[typeid]'");
			$smiliesnum = $db->result($squery, 0);
			$smileyselect .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\" ".($smiliesnum ? 'disabled' : '')."></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" name=\"namenew[$type[typeid]]\" value=\"$type[name]\" size=\"15\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\" size=\"2\"></td>\n".
				"<td class=\"altbg2\">./images/smilies/$type[directory]</td>\n".
				"<td class=\"altbg1\">$smiliesnum</td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=smilies&export=$type[typeid]\">[$lang[download]]</a></td>\n".
				"<td class=\"altbg1\"><a href=\"admincp.php?action=smilies&edit=$type[typeid]\">[$lang[detail]]</a></td></tr>\n";
			$dirfilter[] = $type['directory'];
		}

		$smdir = DISCUZ_ROOT.'./images/smilies';
		$smiliesdir = dir($smdir);
		$dirnum = 0;
		while($entry = $smiliesdir->read()) {
			if($entry != '.' && $entry != '..' && !in_array($entry, $dirfilter) && preg_match("/^\w+$/", $entry) && strlen($entry) < 30 && is_dir($smdir.'/'.$entry)){
				$smileydirs .= '<tr><td class="altbg1">'.($dirnum ? '&nbsp;' : $lang['add_new']).'</td><td class="altbg2"><input type="text" name="newname[]" size="15"></td><td class="altbg1"><input type="text" name="newdisplayorder[]" size="2"></td><td class="altbg2">./images/smilies/'.$entry.'<input type="hidden" name="newdirectory[]" value="'.$entry.'"></td><td colspan="3">&nbsp;</td></tr>';
				$dirnum++;
			}
		}

		$smileydirs = $smileydirs ? $smileydirs : '<tr><td class="altbg1">'.$lang['add_new'].'</td><td class="altbg2" colspan="6">'.$lang['smiliesupload_tips'].'</td></tr>';

		shownav('smilies_edit');
		showtips('smileytypes_tips');

?>
<form method="post" action="admincp.php?action=smilies">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['smilies_type']?></td><td><?=$lang['display_order']?></td><td><?=$lang['templates_directory']?></td><td><?=$lang['smilies_nums']?></td><td><?=$lang['export']?></td><td><?=$lang['edit']?></td></tr>
<?=$smileyselect?>
<?=$smileydirs?>
</table><br />
<center><input class="button" type="submit" name="smiliessubmit" value="<?=$lang['submit']?>"></center></form><br />

<form method="post" action="admincp.php?action=smilies">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['smilies_import']?></td></tr>
<tr><td class="altbg1"><div align="center"><textarea  name="smiliesdata" cols="80" rows="8"></textarea><br /></div></td></tr>
</table><br /><center><input class="button" type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} elseif(submitcheck('smiliessubmit')) {

		if(is_array($namenew)) {
			foreach($namenew as $id => $val) {
				$db->query("UPDATE {$tablepre}imagetypes SET name='$val', displayorder='$displayordernew[$id]' WHERE typeid='$id'");
			}
		}

		if($ids = implodeids($delete)) {
			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}smilies WHERE type='smiley' AND typeid IN ($ids)")) {
				cpmsg('smilies_delete_invalid');
			}
			$db->query("DELETE FROM {$tablepre}imagetypes WHERE typeid IN ($ids)");
		}

		if(is_array($newname)) {
			foreach($newname AS $key => $val) {
				$smurl = './images/smilies/'.$newdiredctory[$key];
				if(!is_dir(DISCUZ_ROOT.$smurl)) {
					cpmsg('smilies_directory_invalid');
				}
				$db->query("INSERT INTO {$tablepre}imagetypes (name, type, displayorder, directory) VALUES ('$val', 'smiley', '$newdisplayorder[$key]', '$newdirectory[$key]')");
			}
		}

		updatecache('smileytypes');
		cpmsg('smilies_edit_succeed', 'admincp.php?action=smilies');

	} elseif(submitcheck('importsubmit')) {

		$smiliesdata = preg_replace("/(#.*\s+)*/", '', $smiliesdata);
		$smileyarray = daddslashes(unserialize(base64_decode($smiliesdata)), 1);

		if(!is_array($smileyarray) || !is_array($smileyarray['smilies'])) {
			cpmsg('smilies_import_data_invalid');
		}

		$renamed = 0;
		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}imagetypes WHERE type='smiley' AND name='$smileyarray[name]'")) {
			$smileyarray['name'] .= '_'.random(4);
			$renamed = 1;
		}
		$db->query("INSERT INTO {$tablepre}imagetypes (name, type, directory)
			VALUES ('$smileyarray[name]', 'smiley', '$smileyarray[directory]')");
		$typeid = $db->insert_id();

		foreach($smileyarray['smilies'] AS $key => $smiley) {
			$substitute = @htmlspecialchars($substitute);
			$db->query("INSERT INTO {$tablepre}smilies (type, typeid, displayorder, code, url)
				VALUES ('smiley', '$typeid', '$smiley[displayorder]', '$smiley[code]', '$smiley[url]')");
		}

		updatecache(array('smileytypes', 'smilies', 'smilies_display'));
		cpmsg($renamed ? 'smilies_import_succeed_renamed' : 'smilies_import_succeed', 'admincp.php?action=smilies');

	} elseif($edit) {

		if(!submitcheck('editsubmit')) {

			$smiliesperpage = 10;
			$page = max(1, intval($page));
			$start_limit = ($page - 1) * $smiliesperpage;

			$type = $db->fetch_first("SELECT typeid, name, directory FROM {$tablepre}imagetypes WHERE typeid='$edit' AND type='smiley'");
			$smurl = './images/smilies/'.$type['directory'];
			$smdir = DISCUZ_ROOT.$smurl;
			if(!is_dir($smdir)) {
				cpmsg('smilies_directory_invalid');
			}

			$num = $db->result_first("SELECT COUNT(*) FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$edit'");
			$multipage = multi($num, $smiliesperpage, $page, 'admincp.php?action=smilies&edit='.$edit);

			$smileynum = 1;
			$smilies = '';
			$query = $db->query("SELECT * FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$edit' ORDER BY displayorder LIMIT $start_limit, $smiliesperpage");
			while($smiley =	$db->fetch_array($query)) {
				$smilies .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\"></td>\n".
					"<td class=\"altbg2\">$smiley[id]</td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"25\" name=\"code[$smiley[id]]\" value=\"".dhtmlspecialchars($smiley['code'])."\" id=\"code_$smileynum\" smileyid=\"$smiley[id]\"></td>\n".
					"<td class=\"altbg1\"><input type=\"hidden\" value=\"$smiley[url]\" id=\"url_$smileynum\">$smiley[url]</td>\n".
					"<td class=\"altbg2\"><img src=\"$smurl/$smiley[url]\" border=\"0\" onload=\"if(this.height>30) {this.resized=true; this.height=30; this.title='$lang[image_newwindow]';}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\"></td></tr>\n";
				$imgfilter[] = $smiley[url];
				$smileynum ++;
			}

			if($search) {
				$newid = 1;
				$newimages = '';
				$imgextarray = array('jpg', 'gif');
				$imgfilter =  array();
				$query = $db->query("SELECT url FROM {$tablepre}smilies WHERE type='smiley' AND typeid='$edit'");
				while($img = $db->fetch_array($query)) {
					$imgfilter[] = $img[url];
				}
				$smiliesdir = dir($smdir);
				while($entry = $smiliesdir->read()) {
					if(in_array(strtolower(fileext($entry)), $imgextarray) && !in_array($entry, $imgfilter) && preg_match("/^[\w\-\.\[\]\(\)\<\> &]+$/", substr($entry, 0, strrpos($entry, '.'))) && strlen($entry) < 30 && is_file($smdir.'/'.$entry)) {
						$newimages .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"add[$newid]\" value=\"\" checked=\"checked\"></td>\n".
							"<td class=\"altbg2\"><input type=\"text\" size=\"2\" name=\"adddisplayorder[$newid]\" value=\"0\"></td>\n".
							"<td class=\"altbg1\"><input type=\"text\" size=\"25\" name=\"addcode[$newid]\" value=\"\" id=\"addcode_$newid\" smileyid=\"$smiley[id]\"></td>\n".
							"<td class=\"altbg2\"><input type=\"hidden\" size=\"25\" name=\"addurl[$newid]\" value=\"$entry\" id=\"addurl_$newid\">$entry</td>\n".
							"<td class=\"altbg1\"><img src=\"$smurl/$entry\" border=\"0\" onload=\"if(this.height>30) {this.resized=true; this.height=30; this.title='$lang[image_newwindow]';}\" onmouseover=\"if(this.resized) this.style.cursor='pointer';\" onclick=\"if(!this.resized) {return false;} else {window.open(this.src);}\"></td></tr>\n";
						$newid ++;
					}
				}
				$smiliesdir->close();

				ajaxshowheader();

				if($newimages) {
?>

<form method="post" action="admincp.php?action=smilies&edit=<?=$edit?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6" align="left"><?=$lang['smilies_add']?></td></tr>
<tr align="center" class="category">
<td width="50"><input type="checkbox" name="chkall" onclick="checkall(this.form, 'add')" class="checkbox" checked="checked"><?=$lang['enabled']?></td>
<td><?=$lang['display_order']?></td>
<td><?=$lang['smilies_edit_code']?></td><td><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$newimages?>
<tr><td colspan="5"><?=$lang['smilies_edit_add_code']?> <input type="text" size="2" value="<?=$lang['smilies_prefix']?>" id="addprefix" onclick="clearinput(this, '<?=$lang['smilies_prefix']?>')" style="vertical-align: middle"> + <select id="addmiddle" style="vertical-align: middle"><option value="1"><?=$lang['smilies_edit_order_file']?></option><option value="2"><?=$lang['smilies_edit_order_radom']?></option></select> + <input type="text" size="2" value="<?=$lang['smilies_suffix']?>" id="addsuffix" onclick="clearinput(this, '<?=$lang['smilies_suffix']?>')" style="vertical-align: middle"> <button type="button" onclick="addsmileycodes('<?=$newid?>', 'add');" style="vertical-align: middle"><?=$lang['apply']?></button></td></tr>
</table><br />
<center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"> &nbsp; <input class="button" type="button" value="<?=$lang['smilies_research']?>" onclick="ajaxget('admincp.php?action=smilies&edit=<?=$edit?>&search=yes', 'addsmilies', 'addsmilies', 'auto');doane(event);"></center></form>

<?
				} else {
					eval("\$lang[smilies_edit_add_tips] = \"".$lang['smilies_edit_add_tips']."\";");
					echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder"><tr class="header"><td align="left">'.$lang['smilies_add'].'</td></tr><tr><td class="altbg1">'.$lang['smilies_edit_add_tips'].'</td></tr></table><center><input class="button" type="button" value="'.$lang['smilies_research'].'" onclick="ajaxget(\'admincp.php?action=smilies&edit='.$edit.'&search=yes\', \'addsmilies\', \'addsmilies\', \'auto\');doane(event);"></center>';
				}

				ajaxshowfooter();
				exit;
			}

			shownav('menu_posting_smilies');
			showtips('smilies_tips');

?>
<form method="post" action="admincp.php?action=smilies&edit=<?=$edit?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6" align="left"><?=$lang['smilies_edit']?> - <?=$type[name]?></td></tr>
<tr align="center" class="category">
<td width="50"><input type="checkbox" name="chkall" onclick="checkall(this.form, 'delete')" class="checkbox"><?=$lang['del']?></td><td><?=$lang['smilies_id']?></td><td><?=$lang['display_order']?></td>
<td><?=$lang['smilies_edit_code']?></td><td><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$smilies?>
<tr><td colspan="6"><?=$lang['smilies_edit_add_code']?> <input type="text" size="2" value="<?=$lang['smilies_prefix']?>" id="prefix" onclick="clearinput(this, '<?=$lang['smilies_prefix']?>')" style="vertical-align: middle"> + <select id="middle" style="vertical-align: middle"><option value="1"><?=$lang['smilies_edit_order_file']?></option><option value="2"><?=$lang['smilies_edit_order_radom']?></option><option value="3"><?=$lang['smilies_id']?></option></select> + <input type="text" size="2" value="<?=$lang['smilies_suffix']?>" id="suffix" onclick="clearinput(this, '<?=$lang['smilies_suffix']?>')" style="vertical-align: middle"> <button type="button" onclick="addsmileycodes('<?=$smileynum?>', '');" style="vertical-align: middle"><?=$lang['apply']?></button></td></tr>

</table>
<?=$multipage?>
<center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"> &nbsp; <input class="button" type="button" value="<?=$lang['return']?>" onclick="window.location='admincp.php?action=smilies'"></center></form>
<br />

<div id="addsmilies">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2" align="left"><?=$lang['smilies_add']?></td></tr>
<tr><td class="altbg1"><b><?=$lang['smilies_type']?>:</b></td><td class="altbg2"><?=$type[name]?></td></tr>
<tr><td class="altbg1"><b><?=$lang['templates_directory']?>:</b><br><span class="smalltxt"><?=$lang['smilies_add_search']?></span></td><td class="altbg2"><?=$smurl?></td></tr>
</table>
<center><input class="button" type="button" value="<?=$lang['search']?>" onclick="ajaxget('admincp.php?action=smilies&edit=<?=$edit?>&search=yes', 'addsmilies', 'addsmilies', 'auto');doane(event);"></center></div>

<script type="text/javascript">
	function addsmileycodes(smiliesnum, pre) {
		smiliesnum = parseInt(smiliesnum);
		if(smiliesnum > 1) {
			for(var i = 1; i < smiliesnum; i++) {
				var prefix = trim($(pre + 'prefix').value);
				var suffix = trim($(pre + 'suffix').value);
				var page = parseInt('<?=$page?>');
				var middle = $(pre + 'middle').value == 1 ? $(pre + 'url_' + i).value.substr(0,$(pre + 'url_' + i).value.lastIndexOf('.')) : ($(pre + 'middle').value == 2 ? i + page * 10 : $(pre + 'code_' + i).attributes['smileyid'].nodeValue);
				if(!prefix || prefix == '<?=$lang['smilies_prefix']?>') {
					alert('<?=$lang[smilies_prefix_tips]?>');
					return;
				}
				suffix = !suffix || suffix == '<?=$lang['smilies_suffix']?>' ? '' : suffix;
				$(pre + 'code_' + i).value = prefix + middle + suffix;
			}
		}
	}
	function clearinput(obj, defaultval) {
		if(obj.value == defaultval) {
			obj.value = '';
		}
	}
</script>

<?

		} else {

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM	{$tablepre}smilies WHERE id IN ($ids)");
			}

			if(is_array($displayorder)) {
				foreach($displayorder AS $id => $val) {
					$displayorder[$id] = intval($displayorder[$id]);
					$code[$id] = trim($code[$id]);
					if(empty($code[$id])) {
						$db->query("DELETE FROM {$tablepre}smilies WHERE id='$id'");
					} else {
						$db->query("UPDATE {$tablepre}smilies SET displayorder='$displayorder[$id]', code='$code[$id]' WHERE id='$id'");
					}
				}
			}

			if(is_array($add)) {
				foreach($add AS $k => $v) {
					$addcode[$k] = trim($addcode[$k]);
					if($addcode[$k] != '') {
						$db->query("INSERT INTO {$tablepre}smilies (type, displayorder, typeid, code, url)
							VALUES ('smiley', '{$adddisplayorder[$k]}', '$edit', '$addcode[$k]', '$addurl[$k]')");
					}
				}
			}

			updatecache(array('smilies', 'smilies_display'));
			cpmsg('smilies_edit_succeed', "admincp.php?action=smilies&edit=$edit");
		}

	}

}

?>