<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: plugins.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

if($action == 'pluginsconfig' && $export) {

	$plugin = $db->fetch_first("SELECT * FROM {$tablepre}plugins WHERE pluginid='$export'");
	if(!$plugin) {
		cpheader();
		cpmsg('undefined_action');
	}

	unset($plugin['pluginid']);

	$pluginarray = array();
	$pluginarray['plugin'] = $plugin;
	$pluginarray['version'] = strip_tags($version);

	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$query = $db->query("SELECT * FROM {$tablepre}pluginhooks WHERE pluginid='$export'");
	while($hook = $db->fetch_array($query)) {
		unset($hook['pluginhookid'], $hook['pluginid']);
		$pluginarray['hooks'][] = $hook;
	}

	$query = $db->query("SELECT * FROM {$tablepre}pluginvars WHERE pluginid='$export'");
	while($var = $db->fetch_array($query)) {
		unset($var['pluginvarid'], $var['pluginid']);
		$pluginarray['vars'][] = $var;
	}

	$plugin_export = "# Discuz! Plugin Dump\n".
		"# Version: Discuz! $version\n".
		"# Time: $time  \n".
		"# From: $bbname ($boardurl) \n".
		"#\n".
		"# Discuz! Community: http://www.Discuz.net\n".
		"# Please visit our website for latest news about Discuz!\n".
		"# --------------------------------------------------------\n\n\n".
		wordwrap(base64_encode(serialize($pluginarray)), 60, "\n", 1);

	ob_end_clean();
	dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	dheader('Cache-Control: no-cache, must-revalidate');
	dheader('Pragma: no-cache');
	dheader('Content-Encoding: none');
	dheader('Content-Length: '.strlen($plugin_export));
	dheader('Content-Disposition: attachment; filename=discuz_plugin_'.$plugin['identifier'].'.txt');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
	echo $plugin_export;
	dexit();

}

cpheader();

if($action == 'plugins') {

	if(!$edit && !$identifier) {

		shownav('menu_plugins_edit');
		showtips('plugins_tips');
		$plugins = '';
		$query = $db->query("SELECT p.*, pv.pluginvarid FROM {$tablepre}plugins p
			LEFT JOIN {$tablepre}pluginvars pv USING(pluginid)
			GROUP BY p.pluginid
			ORDER BY p.available DESC, p.pluginid");

		while($plugin = $db->fetch_array($query)) {
			if(!$plugin['adminid'] || $plugin['adminid'] >= $adminid) {
				$plugin['disabled'] = '';
				$plugin['edit'] = $plugin['pluginvarid'] ? "<a href=\"admincp.php?action=plugins&edit=$plugin[pluginid]\">[$lang[plugins_settings]]</a> " : '';
				if(is_array($plugin['modules'] = unserialize($plugin['modules']))) {
					foreach($plugin['modules'] as $module) {
						if($module['type'] == 3 && (!$module['adminid'] || $module['adminid'] >= $adminid)) {
							$plugin['edit'] .= "<a href=\"admincp.php?action=plugins&identifier=$plugin[identifier]&mod=$module[name]\">[$lang[plugins_settings_module]: $module[menu]]</a> ";
						}
					}
				}
			} else {
				$plugin['disabled'] = 'disabled';
				$plugin['edit'] = "[$lang[detail]]";
			}
			echo 	"<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableborder\" $plugin[disabled]>\n".
				"<tr class=\"header\"><td colspan=\"2\">$plugin[name]".(!$plugin['available'] ? ' ('.$lang['plugins_unavailable'].')' : '')."</td></tr>\n".
				"<tr><td width=\"20%\" class=\"altbg1\">$lang[description]:</td><td class=\"altbg2\">$plugin[description]</td></tr>\n".
				"<tr><td width=\"20%\" class=\"altbg1\">$lang[copyright]:</td><td class=\"altbg2\">$plugin[copyright]</td></tr>\n".
				"<tr><td width=\"20%\" class=\"altbg1\">$lang[edit]:</td><td class=\"altbg2\">$plugin[edit]</td></tr>\n".
				"</table><br />";
		}

	} else {

		$plugin = $db->fetch_first("SELECT * FROM {$tablepre}plugins WHERE ".($identifier ? "identifier='$identifier'" : "pluginid='$edit'"));
		if(!$plugin) {
			cpmsg('undefined_action');
		} else {
			$edit = $plugin['pluginid'];
		}

		$pluginvars = array();
		$query = $db->query("SELECT * FROM {$tablepre}pluginvars WHERE pluginid='$edit' ORDER BY displayorder");
		while($var = $db->fetch_array($query)) {
			$pluginvars[$var['variable']] = $var;
		}

		if(empty($mod)) {

			if(($plugin['adminid'] && $adminid > $plugin['adminid']) || !$pluginvars) {
				cpmsg('noaccess');
			}

			if(!submitcheck('editsubmit')) {
				shownav('menu_plugins_edit');

				echo "<form method=\"post\" action=\"admincp.php?action=plugins&edit=$pluginid&edit=$edit&formhash=".FORMHASH."\">\n";

				showtype($lang['plugins_settings'].' - '.$plugin['name'], 'top');

				foreach($pluginvars as $var) {
					$var['variable'] = 'varsnew['.$var['variable'].']';
					if($var['type'] == 'number') {
						$var['type'] = 'text';
					} elseif($var['type'] == 'select') {
						$var['type'] = "<select name=\"$var[variable]\">\n";
						foreach(explode("\n", $var['extra']) as $key => $option) {
							$option = trim($option);
							if(strpos($option, '=') === FALSE) {
								$key = $option;
							} else {
								$item = explode('=', $option);
								$key = trim($item[0]);
								$option = trim($item[1]);
							}
							$var['type'] .= "<option value=\"".dhtmlspecialchars($key)."\" ".($var['value'] == $key ? 'selected' : '').">$option</option>\n";
						}
						$var['type'] .= "</select>\n";
						$var['variable'] = $var['value'] = '';
					}
					$var['title'] = '</b><b>'.(isset($lang[$var['title']]) ? $lang[$var['title']] : $var['title']).'</b><br />'.
						(isset($lang[$var['description']]) ? $lang[$var['description']] : $var['description']);

					showsetting($var['title'], $var['variable'], $var['value'], $var['type']);
				}

				showtype('', 'bottom');

				echo "<br /><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

			} else {

				if(is_array($varsnew)) {
					foreach($varsnew as $variable => $value) {
						if(isset($pluginvars[$variable])) {
							if($pluginvars[$variable]['type'] == 'number') {
								$value = (float)$value;
							}
							$db->query("UPDATE {$tablepre}pluginvars SET value='$value' WHERE pluginid='$edit' AND variable='$variable'");
						}
					}
				}

				updatecache('plugins');
				cpmsg('plugins_settings_succeed', 'admincp.php?action=plugins');

			}

		} else {

			$modfile = '';
			if(is_array($plugin['modules'] = unserialize($plugin['modules']))) {
				foreach($plugin['modules'] as $module) {
					if($module['type'] == 3 && $module['name'] == $mod && (!$module['adminid'] || $module['adminid'] >= $adminid)) {
						$plugin['directory'] .= (!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '';
						$modfile = './plugins/'.$plugin['directory'].$module['name'].'.inc.php';
						break;
					}
				}
			}

			if($modfile) {
				if(!@include DISCUZ_ROOT.$modfile) {
					cpmsg('plugins_settings_module_nonexistence');
				} else {
					dexit();
				}
			} else {
				cpmsg('undefined_action');
			}

		}

	}

} elseif($action == 'pluginsconfig') {

	if(!submitcheck('configsubmit') && !submitcheck('importsubmit')) {

		$plugins = '';
		$query = $db->query("SELECT * FROM {$tablepre}plugins");
		while($plugin = $db->fetch_array($query)) {
			$plugins .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$plugin[pluginid]\"></td>\n".
				"<td class=\"altbg2\"><b>$plugin[name]</b></td>\n".
				"<td class=\"altbg1\">$plugin[identifier]</td>\n".
				"<td class=\"altbg2\">$plugin[description]</td>\n".
				"<td class=\"altbg1\">$plugin[directory]</td>\n".
				"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$plugin[pluginid]]\" value=\"1\" ".(!$plugin['name'] || !$plugin['identifier'] ? 'disabled' : ($plugin['available'] ? 'checked' : ''))."></td>\n".
				"<td class=\"altbg1\"><a href=\"admincp.php?action=pluginsconfig&export=$plugin[pluginid]\">[$lang[download]]</a></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=pluginsedit&pluginid=$plugin[pluginid]\">[$lang[detail]]</a></td></tr>\n";
		}

		shownav('menu_plugins_config');
		showtips('plugins_config_tips');

?>
<form method="post" action="admincp.php?action=pluginsconfig&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="5%"><input class="checkbox" type="checkbox" name="chkall" class="header" onclick="checkall(this.form, 'delete')"><?=$lang['del']?></td>
<td width="15%"><?=$lang['plugins_name']?></td>
<td width="15%"><?=$lang['plugins_identifier']?></td>
<td width="26%"><?=$lang['description']?></td>
<td width="15%"><?=$lang['plugins_directory']?></td>
<td width="8%"><?=$lang['available']?></td>
<td width="8%"><?=$lang['export']?></td>
<td width="10%"><?=$lang['edit']?></td></tr>
<?=$plugins?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type='text' name="newname" size="12"></td>
<td><input type='text' name="newidentifier" size="8"></td>
<td colspan="6">&nbsp;</td>
</tr></table><br />
<center><input class="button" type="submit" name="configsubmit" value="<?=$lang['submit']?>"></center></form><br />

<form method="post" action="admincp.php?action=pluginsconfig&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['plugins_import']?></td></tr>
<tr><td class="altbg1"><div align="center"><textarea  name="plugindata" cols="80" rows="8"></textarea><br />
<input class="checkbox" type="checkbox" name="ignoreversion" value="1"> <?=$lang['plugins_import_ignore_version']?></div></td></tr>
</table><br /><center><input class="button" type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} elseif(submitcheck('configsubmit')) {

		$db->query("UPDATE {$tablepre}plugins SET available='0'");
		if(is_array($availablenew)) {
			foreach($availablenew as $id => $available) {
				$db->query("UPDATE {$tablepre}plugins SET available='$available' WHERE pluginid='$id'");
			}
		}

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}plugins WHERE pluginid IN ($ids)");
			$db->query("DELETE FROM {$tablepre}pluginvars WHERE pluginid IN ($ids)");
		}

		if(($newname = trim($newname)) || ($newidentifier = trim($newidentifier))) {
			if(!$newname) {
				cpmsg('plugins_edit_name_invalid');
			}
			$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='$newidentifier' LIMIT 1");
			if($db->num_rows($query) || !$newidentifier || !ispluginkey($newidentifier)) {
				cpmsg('plugins_edit_identifier_invalid');
			}
			$db->query("INSERT INTO {$tablepre}plugins (name, identifier, available) VALUES ('".dhtmlspecialchars(trim($newname))."', '$newidentifier', '0')");
		}

		updatecache('plugins');
		updatecache('settings');
		cpmsg('plugins_edit_succeed', 'admincp.php?action=pluginsconfig');

	} elseif(submitcheck('importsubmit')) {

		$plugindata = preg_replace("/(#.*\s+)*/", '', $plugindata);
		$pluginarray = daddslashes(unserialize(base64_decode($plugindata)), 1);

		if(!is_array($pluginarray) || !is_array($pluginarray['plugin'])) {
			cpmsg('plugins_import_data_invalid');
		} elseif(empty($ignoreversion) && strip_tags($pluginarray['version']) != strip_tags($version)) {
			cpmsg('plugins_import_version_invalid');
		}

		$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='{$pluginarray[plugin][identifier]}' LIMIT 1");
		if($db->num_rows($query)) {
			cpmsg('plugins_import_identifier_duplicated');
		}

		$sql1 = $sql2 = $comma = '';
		foreach($pluginarray['plugin'] as $key => $val) {
			if($key == 'directory') {
				//compatible for old versions
				$val .= (!empty($val) && substr($val, -1) != '/') ? '/' : '';
			}
			$sql1 .= $comma.$key;
			$sql2 .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		$db->query("INSERT INTO {$tablepre}plugins ($sql1) VALUES ($sql2)");
		$pluginid = $db->insert_id();

		foreach(array('hooks', 'vars') as $pluginconfig) {
			if(is_array($pluginarray[$pluginconfig])) {
				foreach($pluginarray[$pluginconfig] as $config) {
					$sql1 = 'pluginid';
					$sql2 = '\''.$pluginid.'\'';
					foreach($config as $key => $val) {
						$sql1 .= ','.$key;
						$sql2 .= ',\''.$val.'\'';
					}
					$db->query("INSERT INTO {$tablepre}plugin$pluginconfig ($sql1) VALUES ($sql2)");
				}
			}
		}

		updatecache('plugins');
		updatecache('settings');
		cpmsg('plugins_import_succeed', 'admincp.php?action=pluginsconfig');

	}

} elseif($action == 'pluginsedit' && $pluginid) {

	$plugin = $db->fetch_first("SELECT * FROM {$tablepre}plugins WHERE pluginid='$pluginid'");
	if(!$plugin) {
		cpmsg('undefined_action');
	}

	$plugin['modules'] = unserialize($plugin['modules']);

	if(!submitcheck('editsubmit')) {

		$modules = '';
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $moduleid => $module) {
				$adminidselect = array($module['adminid'] => 'selected');
				$includecheck = empty($val['include']) ? $lang['no'] : $lang['yes'];

				$modules .= "<tr class=\"altbg1\" align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$moduleid]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"namenew[$moduleid]\" value=\"$module[name]\"></td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"15\" name=\"menunew[$moduleid]\" value=\"$module[menu]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"urlnew[$moduleid]\" value=\"".dhtmlspecialchars($module['url'])."\"></td>\n".
					"<td class=\"altbg1\"><select name=\"typenew[$moduleid]\">";
				for($i = 1; $i <= 6; $i++) {
					$modules .= "<option value=\"$i\" ".($module['type'] == $i ? 'selected' : '').">".$lang['plugins_edit_modules_type_'.$i]."</option>";
				}
				$modules .= "</select></td>\n".
					"<td class=\"altbg2\"><select name=\"adminidnew[$moduleid]\">\n".
					"<option value=\"0\" $adminidselect[0]>$lang[usergroups_system_0]</option>\n".
					"<option value=\"1\" $adminidselect[1]>$lang[usergroups_system_1]</option>\n".
					"<option value=\"2\" $adminidselect[2]>$lang[usergroups_system_2]</option>\n".
					"<option value=\"3\" $adminidselect[3]>$lang[usergroups_system_3]</option>\n".
					"</select></td><td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"ordernew[$moduleid]\" value=\"$module[displayorder]\"></td></tr>\n";
			}
		}

		$hooks = '';
		$query = $db->query("SELECT pluginhookid, title, description, available FROM {$tablepre}pluginhooks WHERE pluginid='$plugin[pluginid]'");
		while($hook = $db->fetch_array($query)) {
			$hook['description'] = nl2br(cutstr($hook['description'], 50));
			$hook['evalcode'] = 'eval($hooks[\''.$plugin['identifier'].'_'.$hook['title'].'\']);';
			$hooks .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$hook[pluginhookid]]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" name=\"titlenew[$hook[pluginhookid]]\" size=\"15\" value=\"$hook[title]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" name=\"hookevalcode{$hook[pluginhookid]}\" id=\"hookevalcode{$hook[pluginhookid]}\" size=\"30\" value=\"".($hook['available'] ? $hook[evalcode] : 'N/A')."\" readonly></td>\n".
				"<td class=\"altbg2\">$hook[description]</td>\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$hook[pluginhookid]]\" value=\"1\" ".($hook['available'] ? 'checked' : '')." onclick=\"if(this.checked) {\$('hookevalcode{$hook[pluginhookid]}').value='".addslashes($hook[evalcode])."';}else{\$('hookevalcode{$hook[pluginhookid]}').value='N/A';}\"></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=pluginhooks&pluginid=$plugin[pluginid]&pluginhookid=$hook[pluginhookid]\">[$lang[edit]]</a></td></tr>";
		}

		$vars = '';
		$query = $db->query("SELECT * FROM {$tablepre}pluginvars WHERE pluginid='$plugin[pluginid]' ORDER BY displayorder");
		while($var = $db->fetch_array($query)) {
			$var['type'] = $lang['plugins_edit_vars_type_'. $var['type']];
			$var['title'] .= isset($lang[$var['title']]) ? '<br />'.$lang[$var['title']] : '';
			$vars .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$var[pluginvarid]\"></td>\n".
				"<td class=\"altbg2\">$var[title]</td>\n".
				"<td class=\"altbg1\">$var[variable]</td>\n".
				"<td class=\"altbg2\">$var[type]</td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayordernew[$var[pluginvarid]]\" value=\"$var[displayorder]\"></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=pluginvars&pluginid=$plugin[pluginid]&pluginvarid=$var[pluginvarid]\">[$lang[detail]]</a></td></tr>\n";
		}

		shownav('menu_plugins_config');
		showtips('plugins_edit_tips');

?>
<a name="common"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=common&pluginid=<?=$pluginid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$adminidselect = array($plugin['adminid'] => 'selected');

		showtype($lang['plugins_edit'].' - '.$plugin['name'], 'top');
		showsetting('plugins_edit_name', 'namenew', $plugin['name'], 'text');
		if(!$plugin['copyright']) {
			showsetting('plugins_edit_copyright', 'copyrightnew', $plugin['copyright'], 'text');
		}
		showsetting('plugins_edit_identifier', 'identifiernew', $plugin['identifier'], 'text');
		showsetting('plugins_edit_adminid', '', '', '<select name="adminidnew"><option value="1" '.$adminidselect[1].'>'.$lang['usergroups_system_1'].'</option><option value="2" '.$adminidselect[2].'>'.$lang['usergroups_system_2'].'</option><option value="3" '.$adminidselect[3].'>'.$lang['usergroups_system_3'].'</option></select>');

		showsetting('plugins_edit_directory', 'directorynew', $plugin['directory'], 'text');
		showsetting('plugins_edit_datatables', 'datatablesnew', $plugin['datatables'], 'text');
		showsetting('plugins_edit_description', 'descriptionnew', $plugin['description'], 'textarea');
		showtype('', 'bottom');

?>
<br /><center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form><br />

<a name="modules"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=modules&pluginid=<?=$pluginid?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="7"><?=$lang['plugins_edit_modules']?></td></tr>
<tr class="category" align="center"><td width="45"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['plugins_edit_modules_name']?></td><td><?=$lang['plugins_edit_modules_menu']?></td><td><?=$lang['plugins_edit_modules_menu_url']?></td>
<td><?=$lang['plugins_edit_modules_type']?></td><td><?=$lang['plugins_edit_modules_adminid']?></td><td><?=$lang['display_order']?></td></tr>
<?=$modules?>
<tr class="altbg1" align="center"><td><?=$lang['add_new']?></td><td><input type="text" size="15" name="newname"></td>
<td><input type="text" size="15" name="newmenu"></td>
<td><input type="text" size="15" name="newurl"></td>
<td><select name="newtype">
<option value="1"><?=$lang['plugins_edit_modules_type_1']?></option>
<option value="2"><?=$lang['plugins_edit_modules_type_2']?></option>
<option value="3"><?=$lang['plugins_edit_modules_type_3']?></option>
<option value="4"><?=$lang['plugins_edit_modules_type_4']?></option>
<option value="5"><?=$lang['plugins_edit_modules_type_5']?></option>
<option value="5"><?=$lang['plugins_edit_modules_type_6']?></option>
</select></td><td><select name="newadminid">
<option value="0"><?=$lang['usergroups_system_0']?></option>
<option value="1" selected><?=$lang['usergroups_system_1']?></option>
<option value="2"><?=$lang['usergroups_system_2']?></option>
<option value="3"><?=$lang['usergroups_system_3']?></option>
</select></td><td><input type="text" size="2" name="neworder"></td></tr>
</table><br /><center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form><br />

<a name="hooks"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=hooks&pluginid=<?=$pluginid?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['plugins_edit_hooks']?></td></tr>
<tr class="category" align="center"><td width="45"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td width="20%"><?=$lang['plugins_hooks_title']?></td><td width="25%"><?=$lang['plugins_hooks_callback']?></td><td width="25%"><?=$lang['plugins_hooks_description']?></td>
<td width="45"><?=$lang['available']?></td><td><?=$lang['edit']?></td></tr>
<?=$hooks?>
<tr class="altbg1" align="center"><td><?=$lang['add_new']?></td><td><input type="text" name="newtitle" size="15"></td>
<td colspan="4">&nbsp;</td></tr>
</table><br /><center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form><br />

<a name="vars"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=vars&pluginid=<?=$pluginid?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['plugins_edit_vars']?></td></tr>
<tr class="category" align="center"><td width="45"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['plugins_vars_title']?></td><td><?=$lang['plugins_vars_variable']?></td><td><?=$lang['plugins_vars_type']?></td><td><?=$lang['display_order']?></td><td><?=$lang['edit']?></td></tr>
<?=$vars?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type="text" size="15" name="newtitle"></td>
<td><input type="text" size="15" name="newvariable"></td>
<td><select name="newtype">
<option value="number"><?=$lang['plugins_edit_vars_type_number']?></option>
<option value="text" selected><?=$lang['plugins_edit_vars_type_text']?></option>
<option value="textarea"><?=$lang['plugins_edit_vars_type_textarea']?></option>
<option value="radio"><?=$lang['plugins_edit_vars_type_radio']?></option>
<option value="select"><?=$lang['plugins_edit_vars_type_select']?></option>
<option value="color"><?=$lang['plugins_edit_vars_type_color']?></option>
</seletc></td><td><input type="text" size="2" name="newdisplayorder" value="0"></td>
<td>&nbsp;</td></tr>
</table><br /><center><input class="button" type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($type == 'common') {

			$namenew	= dhtmlspecialchars(trim($namenew));
			$directorynew	= dhtmlspecialchars($directorynew);
			$identifiernew	= trim($identifiernew);
			$datatablesnew	= dhtmlspecialchars(trim($datatablesnew));
			$descriptionnew	= dhtmlspecialchars($descriptionnew);
			$copyrightnew	= $plugin['copyright'] ? addslashes($plugin['copyright']) : dhtmlspecialchars($copyrightnew);
			$adminidnew	= ($adminidnew > 0 && $adminidnew <= 3) ? $adminidnew : 1;

			if(!$namenew) {
				cpmsg('plugins_edit_name_invalid');
			} elseif(!isplugindir($directorynew)) {
				cpmsg('plugins_edit_directory_invalid');
			} elseif($identifiernew != $plugin['identifier']) {
				$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='$identifiernew' LIMIT 1");
				if($db->num_rows($query) || !ispluginkey($identifiernew)) {
					cpmsg('plugins_edit_identifier_invalid');
				}
			}

			$db->query("UPDATE {$tablepre}plugins SET adminid='$adminidnew', name='$namenew', identifier='$identifiernew', description='$descriptionnew', datatables='$datatablesnew', directory='$directorynew', copyright='$copyrightnew' WHERE pluginid='$pluginid'");

		} elseif($type == 'modules') {

			$modulesnew = array();
			$newname = trim($newname);
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $moduleid => $module) {
					if(!isset($delete[$moduleid])) {
						$modulesnew[] = array
							(
							'name'		=> $namenew[$moduleid],
							'menu'		=> $menunew[$moduleid],
							'url'		=> $urlnew[$moduleid],
							'type'		=> $typenew[$moduleid],
							'adminid'	=> ($adminidnew[$moduleid] >= 0 && $adminidnew[$moduleid] <= 3) ? $adminidnew[$moduleid] : $module['adminid'],
							'displayorder'	=> intval($ordernew[$moduleid]),
							);
					}
				}
			}

			$newmodule = array();
			if(!empty($newname)) {
				$modulesnew[] = array
					(
					'name'		=> $newname,
					'menu'		=> $newmenu,
					'url'		=> $newurl,
					'type'		=> $newtype,
					'adminid'	=> $newadminid,
					'displayorder'	=> intval($neworder),
					);
			}

			usort($modulesnew, 'modulecmp');

			$namesarray = array();
			foreach($modulesnew as $key => $module) {
				if(!ispluginkey($module['name'])) {
					cpmsg('plugins_edit_modules_name_invalid');
				} elseif(in_array($module['name'], $namesarray)) {
					cpmsg('plugins_edit_modules_duplicated');
				}

				$namesarray[] = $module['name'];

				$module['menu'] = trim($module['menu']);
				$module['url'] = trim($module['url']);
				$module['adminid'] = $module['adminid'] >= 0 && $module['adminid'] <= 3 ? $module['adminid'] : 1 ;

				switch($module['type']) {
					case 5:
					case 1:
						if(empty($module['url'])) {
							cpmsg('plugins_edit_modules_url_invalid');
						}
						break;
					case 6:
					case 2:
					case 3:
						if(empty($module['menu'])) {
							cpmsg('plugins_edit_modules_menu_invalid');
						}
						unset($module['url']);
						break;
					case 4:
						unset($module['menu'], $module['url']);
						break;
					default:
						cpmsg('undefined_action');
				}

				$modulesnew[$key] = $module;

			}

			$db->query("UPDATE {$tablepre}plugins SET modules='".addslashes(serialize($modulesnew))."' WHERE pluginid='$pluginid'");

		} elseif($type == 'hooks') {

			if(is_array($delete)) {
				$ids = $comma = '';
				foreach($delete as $id => $val) {
					$ids .= "$comma'$id'";
					$comma = ',';
				}
				$db->query("DELETE FROM {$tablepre}pluginhooks WHERE pluginid='$pluginid' AND pluginhookid IN ($ids)");
			}

			if(is_array($titlenew)) {
				$titlearray = array();
				foreach($titlenew as $id => $val) {
					if(!ispluginkey($val) || in_array($val, $titlearray)) {
						cpmsg('plugins_edit_hooks_title_invalid');
					}
					$titlearray[] = $val;
					$db->query("UPDATE {$tablepre}pluginhooks SET title='".dhtmlspecialchars($titlenew[$id])."', available='".intval($availablenew[$id])."' WHERE pluginid='$pluginid' AND pluginhookid='$id'");
				}
			}

			if($newtitle) {
				if(!ispluginkey($newtitle) || (is_array($titlenew) && in_array($newtitle, $titlenew))) {
					cpmsg('plugins_edit_hooks_title_invalid');
				}
				$db->query("INSERT INTO {$tablepre}pluginhooks (pluginid, title, description, code, available)
					VALUES ('$pluginid', '".dhtmlspecialchars($newtitle)."', '', '', 0)");
			}

		} elseif($type == 'vars') {

			if($ids = implodeids($delete)) {
				$db->query("DELETE FROM {$tablepre}pluginvars WHERE pluginid='$pluginid' AND pluginvarid IN ($ids)");
			}

			if(is_array($displayordernew)) {
				foreach($displayordernew as $id => $displayorder) {
					$db->query("UPDATE {$tablepre}pluginvars SET displayorder='$displayorder' WHERE pluginid='$pluginid' AND pluginvarid='$id'");
				}
			}

			$newtitle = dhtmlspecialchars(trim($newtitle));
			$newvariable = trim($newvariable);
			if($newtitle && $newvariable) {
				$query = $db->query("SELECT pluginvarid FROM {$tablepre}pluginvars WHERE pluginid='$pluginid' AND variable='$newvariable' LIMIT 1");
				if($db->num_rows($query) || strlen($newvariable) > 40 || !ispluginkey($newvariable)) {
					cpmsg('plugins_edit_var_invalid');
				}

				$db->query("INSERT INTO {$tablepre}pluginvars (pluginid, displayorder, title, variable, type)
					VALUES ('$pluginid', '$newdisplayorder', '$newtitle', '$newvariable', '$newtype')");
			}

		}

		updatecache('plugins');
		updatecache('settings');
		cpmsg('plugins_edit_succeed', "admincp.php?action=pluginsedit&pluginid=$pluginid#$type");

	}

} elseif($action == 'pluginhooks' && $pluginid && $pluginhookid) {

	$pluginhook = $db->fetch_first("SELECT * FROM {$tablepre}plugins p, {$tablepre}pluginhooks ph WHERE p.pluginid='$pluginid' AND ph.pluginid=p.pluginid AND ph.pluginhookid='$pluginhookid'");
	if(!$pluginhook) {
		cpmsg('undefined_action');
	}

	if(!submitcheck('hooksubmit')) {
		shownav('menu_plugins_edit');
		showtips('plugins_edit_hooks_tips');

?>
<form method="post" action="admincp.php?action=pluginhooks&pluginid=<?=$pluginid?>&pluginhookid=<?=$pluginhookid?>&formhash=<?=FORMHASH?>">
<?

		showtype($lang['plugins_edit_hooks'].' - '.$pluginhook['title'], 'top');
		showsetting('plugins_edit_hooks_description', 'descriptionnew', $pluginhook['description'], 'textarea');
		showsetting('plugins_edit_hooks_code', 'codenew', $pluginhook['code'], 'textarea');
		showtype('', 'bottom');

		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"hooksubmit\" value=\"$lang[submit]\"></center></form>\n<br />";

	} else {

		$descriptionnew	= dhtmlspecialchars(trim($descriptionnew));
		$codenew	= trim($codenew);

		$db->query("UPDATE {$tablepre}pluginhooks SET description='$descriptionnew', code='$codenew' WHERE pluginid='$pluginid' AND pluginhookid='$pluginhookid'");

		updatecache('settings');
		cpmsg('plugins_edit_hooks_succeed', "admincp.php?action=pluginsedit&pluginid=$pluginid");
	}

} elseif($action == 'pluginvars' && $pluginid && $pluginvarid) {

	$pluginvar = $db->fetch_first("SELECT * FROM {$tablepre}plugins p, {$tablepre}pluginvars pv WHERE p.pluginid='$pluginid' AND pv.pluginid=p.pluginid AND pv.pluginvarid='$pluginvarid'");
	if(!$pluginvar) {
		cpmsg('undefined_action');
	}

	if(!submitcheck('varsubmit')) {
		shownav('menu_plugins_edit');

		$typeselect = '<select name="typenew">';
		foreach(array('number', 'text', 'radio', 'textarea', 'select', 'color') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($pluginvar['type'] == $type ? 'selected' : '').'>'.$lang['plugins_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		echo "<form method=\"post\" action=\"admincp.php?action=pluginvars&pluginid=$pluginid&pluginvarid=$pluginvarid&formhash=".FORMHASH."\">\n";

		showtype($lang['plugins_edit_vars'].' - '.$pluginvar['title'], 'top');
		showsetting('plugins_edit_vars_title', 'titlenew', $pluginvar['title'], 'text');
		showsetting('plugins_edit_vars_description', 'descriptionnew', $pluginvar['description'], 'textarea');
		showsetting('plugins_edit_vars_type', '', '', $typeselect);
		showsetting('plugins_edit_vars_variable', 'variablenew', $pluginvar['variable'], 'text');
		showsetting('plugins_edit_vars_extra', 'extranew',  $pluginvar['extra'], 'textarea');
		showtype('', 'bottom');

		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"varsubmit\" value=\"$lang[submit]\"></center></form>\n<br />";

	} else {

		$titlenew	= cutstr(dhtmlspecialchars(trim($titlenew)), 25);
		$descriptionnew	= cutstr(dhtmlspecialchars(trim($descriptionnew)), 255);
		$variablenew	= trim($variablenew);
		$extranew	= dhtmlspecialchars(trim($extranew));

		if(!$titlenew) {
			cpmsg('plugins_edit_vars_title_invalid');
		} elseif($variablenew != $pluginvar['variable']) {
			$query = $db->query("SELECT pluginvarid FROM {$tablepre}pluginvars WHERE variable='$variablenew'");
			if($db->num_rows($query) || !$variablenew || strlen($variablenew) > 40 || !ispluginkey($variablenew)) {
				cpmsg('plugins_edit_vars_invalid');
			}
		}

		$db->query("UPDATE {$tablepre}pluginvars SET title='$titlenew', description='$descriptionnew', type='$typenew', variable='$variablenew', extra='$extranew' WHERE pluginid='$pluginid' AND pluginvarid='$pluginvarid'");

		updatecache('plugins');
		cpmsg('plugins_edit_vars_succeed', "admincp.php?action=pluginsedit&pluginid=$pluginid");
	}

}

function modulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

?>