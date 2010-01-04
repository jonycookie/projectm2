<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: styles.inc.php 19193 2009-08-17 02:59:31Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

if($action == 'styles' && $export) {
	$stylearray = $db->fetch_first("SELECT s.name, s.templateid, t.name AS tplname, t.directory, t.copyright FROM {$tablepre}styles s LEFT JOIN {$tablepre}templates t ON t.templateid=s.templateid WHERE styleid='$export'");
	if(!$stylearray) {
		cpheader();
		cpmsg('styles_export_invalid');
	}

	$stylearray['version'] = strip_tags($version);
	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$query = $db->query("SELECT * FROM {$tablepre}stylevars WHERE styleid='$export'");
	while($style = $db->fetch_array($query)) {
		$stylearray['style'][$style['variable']] = $style['substitute'];
	}

	$style_export = "# Discuz! Style Dump\n".
			"# Version: Discuz! $version\n".
			"# Time: $time\n".
			"# From: $bbname ($boardurl)\n".
			"#\n".
			"# This file was BASE64 encoded\n".
			"#\n".
			"# Discuz! Community: http://www.Discuz.net\n".
			"# Please visit our website for latest news about Discuz!\n".
			"# --------------------------------------------------------\n\n\n".
			wordwrap(base64_encode(serialize($stylearray)), 50, "\n", 1);

	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($style_export));
	dheader('Content-Disposition: attachment; filename=discuz_style_'.$stylearray['name'].'.txt');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));

	echo $style_export;
	dexit();
}

cpheader();

if($action == 'styles' && !$export) {

	$predefinedvars = array('available', 'bgcolor', 'altbg1', 'altbg2', 'link', 'bordercolor', 'headercolor', 'headertext', 'catcolor',
				'tabletext', 'text', 'borderwidth', 'tablespace', 'fontsize', 'msgfontsize', 'msgbigsize', 'msgsmallsize',
				'font', 'smfontsize', 'smfont', 'boardimg', 'imgdir', 'maintablewidth', 'stypeid', 'bgborder',
				'catborder', 'inputborder', 'lighttext', 'headermenu', 'headermenutext', 'framebgcolor',
				'noticebg', 'commonboxborder', 'tablebg', 'highlightlink', 'commonboxbg', 'boxspace', 'portalboxbgcode',
				'noticeborder', 'noticetext');

	if(!submitcheck('stylesubmit') && !submitcheck('importsubmit') && !$edit && !$export) {

		$defaultstyleid = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='styleid'");
		$styleselect = '';
		$query = $db->query("SELECT s.styleid, s.available, s.name, t.name AS tplname, t.copyright FROM {$tablepre}styles s LEFT JOIN {$tablepre}templates t ON t.templateid=s.templateid");
		while($styleinfo = $db->fetch_array($query)) {
			$styleselect .= "<tr align=\"center\"><td class=\"altbg1\">".($styleinfo['styleid'] != $defaultstyleid ? "<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$styleinfo[styleid]\">" : NULL)."</td>\n".
				"<td class=\"altbg2\"><input type=\"text\" name=\"namenew[$styleinfo[styleid]]\" value=\"$styleinfo[name]\" size=\"18\"></td>\n".
				"<td class=\"altbg1\">".($styleinfo['styleid'] != $defaultstyleid ? "<input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$styleinfo[styleid]]\" value=\"1\" ".($styleinfo['available'] ? 'checked' : NULL).">" : "<input class=\"checkbox\" type=\"hidden\" name=\"availablenew[$styleinfo[styleid]]\" value=\"1\">")."</td>\n".
				"<td class=\"altbg2\">$styleinfo[styleid]</td>\n".
				"<td class=\"altbg1\">$styleinfo[tplname]</td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=styles&export=$styleinfo[styleid]\">[$lang[download]]</a></td>\n".
				"<td class=\"altbg1\"><a href=\"admincp.php?action=styles&edit=$styleinfo[styleid]\">[$lang[detail]]</a></td></tr>\n";
		}

		shownav('menu_styles');
		showtips('styles_tips');

?>
<form method="post" action="admincp.php?action=styles">
<input name="updatecsscache" type="hidden" value="0">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['styles_name']?></td><td><?=$lang['available']?></td><td>styleID</td><td><?=$lang['styles_template']?></td></td><td><?=$lang['export']?></td><td><?=$lang['edit']?></td></tr>
<?=$styleselect?>
<tr align="center" class="altbg1"><td class="altbg1"><?=$lang['add_new']?></td>
<td><input type='text' name="newname" size="18"></td>
<td colspan="6">&nbsp;</td>
</tr></table><br />
<center><input class="button" type="submit" name="stylesubmit" value="<?=$lang['submit']?>">&nbsp;&nbsp;<input onclick="this.form.updatecsscache.value = 1" class="button" type="submit" name="stylesubmit" value="<?=$lang['styles_csscache_update']?>"></center></form><br />

<form method="post" action="admincp.php?action=styles">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['styles_import']?></td></tr>
<tr><td class="altbg1"><div align="center"><textarea  name="styledata" cols="80" rows="8"></textarea><br />
<input class="checkbox" type="checkbox" name="ignoreversion" value="1"> <?=$lang['styles_import_ignore_version']?></div></td></tr>
</table><br /><center><input class="button" type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} elseif(submitcheck('stylesubmit')) {

		if($updatecsscache) {
			updatecache('styles');
			cpmsg('csscache_update', 'admincp.php?action=styles');
		} else {
			if(is_array($namenew)) {
				foreach($namenew as $id => $val) {
					$db->query("UPDATE {$tablepre}styles SET name='$namenew[$id]', available='$availablenew[$id]' WHERE styleid='$id'");
				}
			}

			if($ids = implodeids($delete)) {
				if($db->result_first("SELECT COUNT(*) FROM {$tablepre}settings WHERE variable='styleid' AND value IN ($ids)")) {
					cpmsg('styles_delete_invalid');
				}

				$db->query("DELETE FROM {$tablepre}styles WHERE styleid IN ($ids)");
				$db->query("DELETE FROM {$tablepre}stylevars WHERE styleid IN ($ids)");
				$db->query("UPDATE {$tablepre}members SET styleid='0' WHERE styleid IN ($ids)");
				$db->query("UPDATE {$tablepre}forums SET styleid='0' WHERE styleid IN ($ids)");
				$db->query("UPDATE {$tablepre}sessions SET styleid='$_DCACHE[settings][styleid]' WHERE styleid IN ($ids)");
			}

			if($newname) {
				$db->query("INSERT INTO {$tablepre}styles (name, templateid) VALUES ('$newname', '1')");
				$styleidnew = $db->insert_id();
				foreach($predefinedvars as $variable) {
					$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable)
						VALUES ('$styleidnew', '$variable')");
				}
			}

			updatecache('settings');
			updatecache('styles');
			cpmsg('styles_edit_succeed', 'admincp.php?action=styles');
		}

	} elseif(submitcheck('importsubmit')) {

		$styledata = preg_replace("/(#.*\s+)*/", '', $styledata);
		$stylearray = daddslashes(unserialize(base64_decode($styledata)), 1);

		if(!is_array($stylearray)) {
			cpmsg('styles_import_data_invalid');
		} elseif(empty($ignoreversion) && strip_tags($stylearray['version']) != strip_tags($version)) {
			cpmsg('styles_import_version_invalid');
		}

		$renamed = 0;
		if($stylearray['templateid'] != 1) {
			$templatedir = DISCUZ_ROOT.'./'.$stylearray['directory'];
			if(!is_dir($templatedir)) {
				if(!@mkdir($templatedir, 0777)) {
					$basedir = dirname($stylearray['directory']);
					cpmsg('styles_import_directory_invalid');
				}
			}

			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}templates WHERE name='$stylearray[tplname]'")) {
				$stylearray['tplname'] .= '_'.random(4);
				$renamed = 1;
			}
			$db->query("INSERT INTO {$tablepre}templates (name, directory, copyright)
				VALUES ('$stylearray[tplname]', '$stylearray[directory]', '$stylearray[copyright]')");
			$templateid = $db->insert_id();
		} else {
			$templateid = 1;
		}

		if($db->result_first("SELECT COUNT(*) FROM {$tablepre}styles WHERE name='$stylearray[name]'")) {
			$stylearray['name'] .= '_'.random(4);
			$renamed = 1;
		}
		$db->query("INSERT INTO {$tablepre}styles (name, templateid)
			VALUES ('$stylearray[name]', '$templateid')");
		$styleidnew = $db->insert_id();

		foreach($stylearray['style'] as $variable => $substitute) {
			$substitute = @htmlspecialchars($substitute);
			$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
				VALUES ('$styleidnew', '$variable', '$substitute')");
		}

		updatecache('styles');
		updatecache('settings');
		cpmsg($renamed ? 'styles_import_succeed_renamed' : 'styles_import_succeed', 'admincp.php?action=styles');

	} elseif($edit) {

		if(!submitcheck('editsubmit')) {

			$style = $db->fetch_first("SELECT name, templateid FROM {$tablepre}styles WHERE styleid='$edit'");
			if(!$style) {
				cpmsg('undefined_action');
			}

			$stylecustom = '';
			$stylestuff = $existvars = array();
			$query = $db->query("SELECT * FROM {$tablepre}stylevars WHERE styleid='$edit'");
			while($stylevar = $db->fetch_array($query)) {
				if(in_array($stylevar['variable'], $predefinedvars)) {
					$stylestuff[$stylevar['variable']] = array('id' => $stylevar['stylevarid'], 'subst' => $stylevar['substitute']);
					$existvars[] = $stylevar['variable'];
				} else {
					$stylecustom .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$stylevar[stylevarid]\"></td>\n".
						"<td class=\"altbg2\"><b>{".strtoupper($stylevar[variable])."}</b></td>\n".
						"<td class=\"altbg1\"><textarea name=\"stylevar[$stylevar[stylevarid]]\" cols=\"50\" rows=\"2\">$stylevar[substitute]</textarea></td>\n".
						"</tr>";
				}
			}
			if($diffvars = array_diff($predefinedvars, $existvars)) {
				foreach($diffvars as $variable) {
					$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
						VALUES ('$edit', '$variable', '')");
					$stylestuff[$variable] = array('id' => $db->insert_id(), 'subst' => '');
				}
			}

			$tplselect = array();
			$query = $db->query("SELECT templateid, name FROM {$tablepre}templates");
			while($template = $db->fetch_array($query)) {
				$tplselect[] = array($template['templateid'], $template['name']);
			}

			$smileytypes = array();
			$query = $db->query("SELECT typeid, name FROM {$tablepre}imagetypes");
			while($type = $db->fetch_array($query)) {
				$smileytypes[] = array($type['typeid'], $type['name']);
			}

			shownav('styles_edit');

			echo "<form method=\"post\" action=\"admincp.php?action=styles&edit=$edit&formhash=".FORMHASH."\">\n";

			showtype($lang['styles_edit'].' - '.$style['name'], 'top');
			showsetting('styles_edit_name', 'namenew', $style['name'], 'text', '55%');
			showsetting('styles_edit_tpl', array('templateidnew', $tplselect), $style['templateid'], 'select');
			showsetting('styles_edit_smileytype', array("stylevar[{$stylestuff[stypeid][id]}]", $smileytypes), $stylestuff['stypeid']['subst'], 'select');
			showsetting('styles_edit_logo', "stylevar[{$stylestuff[boardimg][id]}]", $stylestuff['boardimg']['subst'], 'text', '55%');
			showsetting('styles_edit_imgdir', "stylevar[{$stylestuff[imgdir][id]}]", $stylestuff['imgdir']['subst'], 'text', '55%');

			showtype('styles_edit_font_color');
			showsetting('styles_edit_font', "stylevar[{$stylestuff[font][id]}]", $stylestuff['font']['subst'], 'text', '55%');
			showsetting('styles_edit_fontsize', "stylevar[{$stylestuff[fontsize][id]}]", $stylestuff['fontsize']['subst'], 'text', '55%');
			showsetting('styles_edit_msgfontsize', "stylevar[{$stylestuff[msgfontsize][id]}]", $stylestuff['msgfontsize']['subst'], 'text', '55%');
			showsetting('styles_edit_msgbigsize', "stylevar[{$stylestuff[msgbigsize][id]}]", $stylestuff['msgbigsize']['subst'], 'text', '55%');
			showsetting('styles_edit_msgsmallsize', "stylevar[{$stylestuff[msgsmallsize][id]}]", $stylestuff['msgsmallsize']['subst'], 'text', '55%');
			showsetting('styles_edit_smfont', "stylevar[{$stylestuff[smfont][id]}]", $stylestuff['smfont']['subst'], 'text', '55%');
			showsetting('styles_edit_smfontsize', "stylevar[{$stylestuff[smfontsize][id]}]", $stylestuff['smfontsize']['subst'], 'text', '55%');
			showsetting('styles_edit_link', "stylevar[{$stylestuff[link][id]}]", $stylestuff['link']['subst'], 'color', '55%');
			showsetting('styles_edit_highlightlink', "stylevar[{$stylestuff[highlightlink][id]}]", $stylestuff['highlightlink']['subst'], 'color', '55%');
			showsetting('styles_edit_headertext', "stylevar[{$stylestuff[headertext][id]}]", $stylestuff['headertext']['subst'], 'color', '55%');
			showsetting('styles_edit_tabletext', "stylevar[{$stylestuff[tabletext][id]}]", $stylestuff['tabletext']['subst'], 'color', '55%');
			showsetting('styles_edit_text', "stylevar[{$stylestuff[text][id]}]", $stylestuff['text']['subst'], 'color', '55%');
			showsetting('styles_edit_lighttext', "stylevar[{$stylestuff[lighttext][id]}]", $stylestuff['lighttext']['subst'], 'color', '55%');

			showtype('styles_edit_table');
			showsetting('styles_edit_maintablewidth', "stylevar[{$stylestuff[maintablewidth][id]}]", $stylestuff['maintablewidth']['subst'], 'text', '55%');
			showsetting('styles_edit_tablespace', "stylevar[{$stylestuff[tablespace][id]}]", $stylestuff['tablespace']['subst'],   'text', '55%');
			showsetting('styles_edit_tablebg', "stylevar[{$stylestuff[tablebg][id]}]", $stylestuff['tablebg']['subst'], 'color', '55%');
			showsetting('styles_edit_borderwidth', "stylevar[{$stylestuff[borderwidth][id]}]", $stylestuff['borderwidth']['subst'], 'text', '55%');
			showsetting('styles_edit_bordercolor', "stylevar[{$stylestuff[bordercolor][id]}]", $stylestuff['bordercolor']['subst'], 'color', '55%');
			showsetting('styles_edit_bgcolor', "stylevar[{$stylestuff[bgcolor][id]}]", $stylestuff['bgcolor']['subst'], 'color', '55%');
			showsetting('styles_edit_headercolor', "stylevar[{$stylestuff[headercolor][id]}]", $stylestuff['headercolor']['subst'], 'color', '55%');
			showsetting('styles_edit_catcolor', "stylevar[{$stylestuff[catcolor][id]}]", $stylestuff['catcolor']['subst'], 'color', '55%');
			showsetting('styles_edit_catborder', "stylevar[{$stylestuff[catborder][id]}]", $stylestuff['catborder']['subst'], 'color', '55%');
			showsetting('styles_edit_portalboxbgcode', "stylevar[{$stylestuff[portalboxbgcode][id]}]", $stylestuff['portalboxbgcode']['subst'], 'color', '55%');
			showsetting('styles_edit_altbg1', "stylevar[{$stylestuff[altbg1][id]}]", $stylestuff['altbg1']['subst'], 'color', '55%');
			showsetting('styles_edit_altbg2', "stylevar[{$stylestuff[altbg2][id]}]", $stylestuff['altbg2']['subst'], 'color', '55%');
			showsetting('styles_edit_bgborder', "stylevar[{$stylestuff[bgborder][id]}]", $stylestuff['bgborder']['subst'], 'color', '55%');
			showsetting('styles_edit_noticebg', "stylevar[{$stylestuff[noticebg][id]}]", $stylestuff['noticebg']['subst'], 'color', '55%');
			showsetting('styles_edit_noticeborder', "stylevar[{$stylestuff[noticeborder][id]}]", $stylestuff['noticeborder']['subst'], 'color', '55%');
			showsetting('styles_edit_noticetext', "stylevar[{$stylestuff[noticetext][id]}]", $stylestuff['noticetext']['subst'], 'color', '55%');
			showsetting('styles_edit_commonboxborder', "stylevar[{$stylestuff[commonboxborder][id]}]", $stylestuff['commonboxborder']['subst'], 'color', '55%');
			showsetting('styles_edit_commonboxbg', "stylevar[{$stylestuff[commonboxbg][id]}]", $stylestuff['commonboxbg']['subst'], 'color', '55%');
			showsetting('styles_edit_boxspace', "stylevar[{$stylestuff[boxspace][id]}]", $stylestuff['boxspace']['subst'], 'text', '55%');

			showtype('styles_other_table');
			showsetting('styles_edit_inputborder', "stylevar[{$stylestuff[inputborder][id]}]", $stylestuff['inputborder']['subst'], 'color', '55%');
			showsetting('styles_edit_headermenu', "stylevar[{$stylestuff[headermenu][id]}]", $stylestuff['headermenu']['subst'], 'color', '55%');
			showsetting('styles_edit_headermenutext', "stylevar[{$stylestuff[headermenutext][id]}]", $stylestuff['headermenutext']['subst'], 'color', '55%');
			showsetting('styles_edit_framebgcolor', "stylevar[{$stylestuff[framebgcolor][id]}]", $stylestuff['framebgcolor']['subst'], 'color', '55%');

			showtype('', 'bottom');

?>
<br /><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['styles_edit_variable']?></td><td><?=$lang['styles_edit_subst']?></td></tr>
<?=$stylecustom?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type='text' name="newcvar" size="20"></td>
<td><textarea name="newcsubst" cols="50" rows="2"></textarea></td>
</tr></table>
<?

			echo "<br /><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

		} else {

			if($newcvar && $newcsubst) {
				if($db->result_first("SELECT COUNT(*) FROM {$tablepre}stylevars WHERE variable='$newcvar' AND styleid='$edit'")) {
					cpmsg('styles_edit_variable_duplicate');
			} elseif(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $newcvar)) {
					cpmsg('styles_edit_variable_illegal');
				}
				$newcvar = strtolower($newcvar);
				$db->query("INSERT INTO {$tablepre}stylevars (styleid, variable, substitute)
					VALUES ('$edit', '$newcvar', '$newcsubst')");
			}

			$db->query("UPDATE {$tablepre}styles SET name='$namenew', templateid='$templateidnew' WHERE styleid='$edit'");
			foreach($stylevar as $id => $substitute) {
				$substitute = @htmlspecialchars($substitute);
				$db->query("UPDATE {$tablepre}stylevars SET substitute='$substitute' WHERE stylevarid='$id' AND styleid='$edit'");
			}

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM {$tablepre}stylevars WHERE stylevarid IN ($ids) AND styleid='$edit'");
			}

			updatecache('styles');
			cpmsg('styles_edit_succeed', 'admincp.php?action=styles'.($newcvar && $newcsubst ? '&edit='.$edit : ''));

		}

	}

}

?>