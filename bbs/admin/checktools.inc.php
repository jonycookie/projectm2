<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: checktools.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if(!isfounder()) cpmsg('noaccess_isfounder');

if($action == 'filecheck') {

	shownav('menu_tools_filecheck');

	if(!isset($start)) {
		cpmsg('filecheck_checking', 'admincp.php?action=filecheck&start=yes');
	} else {

		if(!$discuzfiles = @file('admin/discuzfiles.md5')) {
			cpmsg('filecheck_nofound_md5file');
		}

		showtips('filecheck_tips');

		$md5data = array();
		$cachelist = checkcachefiles('forumdata/cache/');
		checkfiles('./', '\.php', 0, 'config.inc.php');
		checkfiles('include/', '\.php|\.htm|\.js');
		checkfiles('templates/default/', '\.php|\.htm');
		checkfiles('wap/', '\.php');
		checkfiles('archiver/', '\.php');
		checkfiles('api/', '\.php');
		checkfiles('plugins/', '\.php');
		checkfiles('admin/', '\.php');

		foreach($discuzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if($md5datanew[$file] != $md5data[$file]) {
				$modifylist[$file] = $md5data[$file];
			}
			$md5datanew[$file] = $md5data[$file];
		}

		$weekbefore = $timestamp - 604800;
		$addlist = @array_merge(@array_diff_assoc($md5data, $md5datanew), $cachelist[2]);
		$dellist = @array_diff_assoc($md5datanew, $md5data);
		$modifylist = @array_merge(@array_diff_assoc($modifylist, $dellist), $cachelist[1]);
		$showlist = @array_merge($md5data, $md5datanew, $cachelist[0]);

		$dirlist = $dirlog = array();
		foreach($showlist as $file => $md5) {
			$dir = dirname($file);
			$filelist = '<tr><td class="altbg1">'.basename($file).'</td>';
			if(file_exists($file)) {
				$filemtime = filemtime($file);
				$filemtime = $filemtime > $weekbefore ? '<b>'.date("$dateformat $timeformat", $filemtime).'</b>' : date("$dateformat $timeformat", $filemtime);
				$filelist .= '<td class="altbg2" style="text-align:right">'.number_format(filesize($file)).' Bytes&nbsp;&nbsp;</td><td class="altbg1">'.$filemtime.'</td>';
			} else {
				$filelist .= '<td class="altbg2"></td><td class="altbg1"></td>';
			}
			if(@array_key_exists($file, $modifylist)) {
				$filelist .= '<td class="altbg2"><font color="#FF0000">'.$lang['filecheck_modify'].'</font></td></tr>';
				$dirlog[$dir]['modify']++;
			} elseif(@array_key_exists($file, $dellist)) {
				$filelist .= '<td class="altbg2"><font color="#0000FF">'.$lang['filecheck_delete'].'</font></td></tr>';
				$dirlog[$dir]['del']++;
			} elseif(@array_key_exists($file, $addlist)) {
				$filelist .= '<td class="altbg2"><font color="#00FF00">'.$lang['filecheck_unknown'].'</font></td></tr>';
				$dirlog[$dir]['add']++;
			} else {
				$filelist .= '<td class="altbg2">'.$lang['filecheck_check_ok'].'</td></tr>';
			}
			$dirlist[$dir] .= $filelist;
		}

	}

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="4"><?=$lang['filecheck_completed']?></td></tr>
<tr><td colspan="4"><?=$lang['filecheck_modify'].': '.count($modifylist).' &nbsp; '.$lang['filecheck_delete'].': '.count($dellist).' &nbsp; '.$lang['filecheck_unknown'].': '.count($addlist)?></td></tr>
<?

foreach($dirlist as $dirname => $filelist) {
	echo '<tr class="header"><td>'.$dirname.'/</td><td colspan="3" style="text-align:right">'.
		($dirlog[$dirname]['modify'] ? $lang['filecheck_modify'].': '.$dirlog[$dirname]['modify'].' &nbsp; ' : '').
		($dirlog[$dirname]['del'] ? $lang['filecheck_delete'].': '.$dirlog[$dirname]['del'].' &nbsp; ' : '').
		($dirlog[$dirname]['add'] ? $lang['filecheck_unknown'].': '.$dirlog[$dirname]['add'].' &nbsp; ' : '').
		'</td><tr><tr class="category"><td>'.$lang['filename'].'</td><td style="text-align:right">'.$lang['size'].'&nbsp;&nbsp;</td><td>'.$lang['filecheck_filemtime'].'</td><td>'.$lang['filecheck_status'].'</td></tr>'.$filelist;
}

?>
</td></tr></table>
<?

} elseif($action == 'dbcheck') {

	shownav('menu_tools_dbcheck');

	if(!$db->query("SHOW FIELDS FROM {$tablepre}settings", 'SILENT')) {
		cpmsg('dbcheck_permissions_invalid');
	}

	if(!isset($start)) {
		cpmsg('dbcheck_checking', 'admincp.php?action=dbcheck&start=yes');
	} else {

		if(!file_exists('admin/discuzdb.md5')) {
			cpmsg('dbcheck_nofound_md5file');
		}

		include DISCUZ_ROOT.'./config.inc.php';
		$dbcharset = empty($dbcharset) ? $charset : $dbcharset;
		unset($dbuser, $dbpw, $dbname);

		$fp = fopen(DISCUZ_ROOT.'./admin/discuzdb.md5', "rb");
		$discuzdb = fread($fp, filesize(DISCUZ_ROOT.'./admin/discuzdb.md5'));
		fclose($fp);
		$dbmd5 = substr($discuzdb, 0, 32);
		$discuzdb = unserialize(substr($discuzdb, 34));
		$settingsdata = $discuzdb[1];
		$discuzdb = $discuzdb[0][0];
		$repair = !empty($repair) ? $repair : array();
		$setting = !empty($setting) ? $setting : array();
		$missingtable = !empty($missingtable) ? $missingtable : array();
		$repairtable = is_array($repairtable) && !empty($repairtable) ? $repairtable : array();

		$except = array('threads' => array('sgid'));
		$query = $db->query("SELECT fieldid FROM {$tablepre}profilefields");
		while($profilefields = $db->fetch_array($query)) {
			$except['memberfields'][] = 'field_'.$profilefields[$fieldid];
		}

		if(submitcheck('repairsubmit') && (!empty($repair) || !empty($setting) || !empty($repairtable) || !empty($missingtable))) {
			$error = '';$errorcount = 0;
			$alter = $fielddefault = array();

			foreach($missingtable as $value) {
				if(!isset($installdata)) {
					$fp = fopen(DISCUZ_ROOT.'./install/discuz.sql', "rb");
					$installdata = fread($fp, filesize(DISCUZ_ROOT.'./install/discuz.sql'));
					fclose($fp);
				}
				preg_match("/CREATE TABLE ".$tablepre.$value."\s+\(.+?;/is", $installdata, $a);
				$db->query(createtable($a[0], $dbcharset));
			}

			foreach($repair as $value) {
				if(!in_array($r_table, $repairtable)) {
					list($r_table, $r_field, $option) = explode('|', $value);
					if(!isset($repairrtable[$r_table]) && $fieldsquery = $db->query("SHOW FIELDS FROM $tablepre$r_table", 'SILENT')) {
						while($fields = $db->fetch_array($fieldsquery)) {
							$fielddefault[$r_table][$fields['Field']] = $fields['Default'];
						}
					}

					$field = $discuzdb[$r_table][$r_field];
					$altersql = '`'.$field['Field'].'` '.$field['Type'];
					$altersql .= $field['Null'] == 'NO' ? ' NOT NULL' : '';
					$altersql .= in_array($fielddefault[$r_table][$field['Field']], array('', '0')) && in_array($field['Default'], array('', '0')) ||
						$field['Null'] == 'NO' && $field['Default'] == '' ||
						preg_match('/text/i', $field['Type']) || preg_match('/auto_increment/i', $field['Extra']) ?
						'' : ' default \''.$field['Default'].'\'';
					$altersql .= $field['Extra'] != '' ? ' '.$field['Extra'] : '';
					$altersql = $option == 'modify' ? "MODIFY COLUMN ".$altersql : "ADD COLUMN ".$altersql;
					$alter[$r_table][] = $altersql;
				}
			}

			foreach($alter as $r_table => $sqls) {
				$db->query("ALTER TABLE `$tablepre$r_table` ".implode(',', $sqls), 'SILENT');
				if($sqlerror = $db->error()) {
					$errorcount += count($sqls);
					$error .= $sqlerror.'<br /><br />';
				}
			}
			$alter = array();

			foreach($repairtable as $value) {
				foreach($discuzdb[$value] as $field) {
					if(!isset($fielddefault[$value]) && $fieldsquery = $db->query("SHOW FIELDS FROM $tablepre$value", 'SILENT')) {
						while($fields = $db->fetch_array($fieldsquery)) {
							$fielddefault[$value][$fields['Field']] = $fields['Default'];
						}
					}
					$altersql = '`'.$field['Field'].'` '.$field['Type'];
					$altersql .= $field['Null'] == 'NO' ? ' NOT NULL' : '';
					$altersql .= in_array($fielddefault[$value][$field['Field']], array('', '0')) && in_array($field['Default'], array('', '0')) ||
						$field['Null'] == 'NO' && $field['Default'] == '' ||
						preg_match('/text/i', $field['Type']) || preg_match('/auto_increment/i', $field['Extra']) ?
						'' : ' default \''.$field['Default'].'\'';
					$altersql .= $field['Extra'] != '' ? ' '.$field['Extra'] : '';
					$altersql = "MODIFY COLUMN ".$altersql;
					$alter[$value][] = $altersql;
				}
			}

			foreach($alter as $r_table => $sqls) {
				$db->query("ALTER TABLE `$tablepre$r_table` ".implode(',', $sqls), 'SILENT');
				if($sqlerror = $db->error()) {
					$errorcount += count($sqls);
					$error .= $sqlerror.'<br /><br />';
				}
			}

			if(!empty($setting)) {
				$settingsdatanow = array();
				$settingsquery = $db->query("SELECT variable FROM {$tablepre}settings WHERE SUBSTRING(variable, 1, 9)<>'jswizard_' ORDER BY variable");
				while($settings = $db->fetch_array($settingsquery)) {
					$settingsdatanew[] = $settings['variable'];
				}
				$settingsdellist = @array_diff($settingsdata, $settingsdatanew);
				if($setting['del'] && is_array($settingsdellist)) {
					foreach($settingsdellist as $variable) {
						$db->query("INSERT INTO {$tablepre}settings (variable, value) VALUES ('$variable', '')", 'SILENT');
					}
				}
				updatecache('settings');
			}

			if($errorcount) {
				cpmsg('dbcheck_repair_error');
			} else {
				cpmsg('dbcheck_repair_completed', 'admincp.php?action=dbcheck');
			}
		}

		$installexists = file_exists(DISCUZ_ROOT.'./install/discuz.sql');
		$discuzdbnew = $deltables = $excepttables = $missingtables = $charseterror = array();
		foreach($discuzdb as $dbtable => $fields) {
			if($fieldsquery = $db->query("SHOW FIELDS FROM $tablepre$dbtable", 'SILENT')) {
				while($fields = $db->fetch_array($fieldsquery)) {
					$r = '/^'.$tablepre.'/';
					$cuttable = preg_replace($r, '', $dbtable);
					if($db->version() < '4.1' && $cuttable == 'sessions' && $fields['Field'] == 'sid') {
						$fields['Type'] = str_replace(' binary', '', $fields['Type']);
					}
					if($cuttable == 'memberfields' && preg_match('/^field\_\d+$/', $fields['Field'])) {
						unset($discuzdbnew[$cuttable][$fields['Field']]);
						continue;
					}
					$discuzdbnew[$cuttable][$fields['Field']]['Field'] = $fields['Field'];
					$discuzdbnew[$cuttable][$fields['Field']]['Type'] = $fields['Type'];
					$discuzdbnew[$cuttable][$fields['Field']]['Null'] = $fields['Null'] == '' ? 'NO' : $fields['Null'];
					$discuzdbnew[$cuttable][$fields['Field']]['Extra'] = $fields['Extra'];
					$discuzdbnew[$cuttable][$fields['Field']]['Default'] = $fields['Default'] == '' || $fields['Default'] == '0' ? '' : $fields['Default'];
				}
				ksort($discuzdbnew[$cuttable]);
			} else {
				$missingtables[] = '<span style="float:left;width:33%">'.(($installexists ? '<input name="missingtable[]" type="checkbox" class="checkbox" value="'.$dbtable.'">' : '').$tablepre.$dbtable).'</span>';
				$excepttables[] = $dbtable;
			}
		}

		if($db->version() > '4.1') {
			$dbcharset = strtoupper($dbcharset) == 'UTF-8' ? 'UTF8' : strtoupper($dbcharset);
			$query = $db->query("SHOW TABLE STATUS LIKE '$tablepre%'");
			while($tables = $db->fetch_array($query)) {
				$r = '/^'.$tablepre.'/';
				$cuttable = preg_replace($r, '', $tables['Name']);
				$tabledbcharset = substr($tables['Collation'], 0, strpos($tables['Collation'], '_'));
				if($dbcharset != strtoupper($tabledbcharset)) {
					$charseterror[] = '<span style="float:left;width:33%">'.$tablepre.$cuttable.'('.$tabledbcharset.')</span>';
				}
			}
		}

		$dbmd5new = md5(serialize($discuzdbnew));

		$settingsdatanow = array();
		$settingsquery = $db->query("SELECT variable FROM {$tablepre}settings WHERE SUBSTRING(variable, 1, 9)<>'jswizard_' ORDER BY variable");
		while($settings = $db->fetch_array($settingsquery)) {
			$settingsdatanew[] = $settings['variable'];
		}
		$settingsdellist = @array_diff($settingsdata, $settingsdatanew);

		if($dbmd5 == $dbmd5new && empty($charseterror) && empty($settingsdellist)) {
			cpmsg('dbcheck_ok');
		}

		$showlist = $addlists = '';
		foreach($discuzdb as $dbtable => $fields) {
			$addlist = $modifylist = $dellist = array();
			if($fields != $discuzdbnew[$dbtable]) {
				foreach($discuzdb[$dbtable] as $key => $value) {
					if(is_array($missingtables) && in_array($tablepre.$dbtable, $missingtables)) {
					} elseif(!isset($discuzdbnew[$dbtable][$key])) {
						$dellist[] = $value;
					} elseif($value != $discuzdbnew[$dbtable][$key]) {
						$modifylist[] = $value;
					}
				}
				if(is_array($discuzdbnew[$dbtable])) {
					foreach($discuzdbnew[$dbtable] as $key => $value) {
						if(!isset($discuzdb[$dbtable][$key]) && !@in_array($value['Field'], $except[$dbtable])) {
							$addlist[] = $value;
						}
					}
				}
			}

			if(($modifylist || $dellist) && !in_array($dbtable, $excepttables)) {
				$showlist .= '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">';

				$showlist .= "<tr class=\"header\"><td width=\"40%\"><b>$tablepre$dbtable</b> $lang[dbcheck_field]</td><td width=\"40%\">$lang[dbcheck_org_field]</td><td width=\"20%\">$lang[dbcheck_status]</td></tr>";

				foreach($modifylist as $value) {
					$showlist .= "<tr class=\"altbg2\"><td><input name=\"repair[]\" class=\"checkbox\" type=\"checkbox\" value=\"$dbtable|$value[Field]|modify\"> <b>".$value['Field']."</b> ".
						$discuzdbnew[$dbtable][$value['Field']]['Type'].
						($discuzdbnew[$dbtable][$value['Field']]['Null'] == 'NO' ? ' NOT NULL' : '').
						(!preg_match('/auto_increment/i', $discuzdbnew[$dbtable][$value['Field']]['Extra']) && !preg_match('/text/i', $discuzdbnew[$dbtable][$value['Field']]['Type']) ? ' default \''.$discuzdbnew[$dbtable][$value['Field']]['Default'].'\'' : '').
						' '.$discuzdbnew[$dbtable][$value['Field']]['Extra'].
						"</td><td><b>".$value['Field']."</b> ".$value['Type'].
						($value['Null'] == 'NO' ? ' NOT NULL' : '').
						(!preg_match('/auto_increment/i', $value['Extra']) && !preg_match('/text/i', $value['Type']) ? ' default \''.$value['Default'].'\'' : '').
						' '.$value['Extra']."</td><td>".
						"<font color=\"#FF0000\">$lang[dbcheck_modify]</font></td></tr>";
				}

				if($modifylist) {
					$showlist .= "<tr class=\"altbg1\"><td colspan=\"3\"><input onclick=\"setrepaircheck(this, this.form, '$dbtable')\" name=\"repairtable[]\" class=\"checkbox\" type=\"checkbox\" value=\"$dbtable\"> <b>$lang[dbcheck_repairtable]</b></td></tr>";
				}

				foreach($dellist as $value) {
					$showlist .= "<tr class=\"altbg2\"><td><input name=\"repair[]\" class=\"checkbox\" type=\"checkbox\" value=\"$dbtable|$value[Field]|add\"> <strike><b>".$value['Field']."</b></strike></td><td> <b>".$value['Field']."</b> ".$value['Type'].($value['Null'] == 'NO' ? ' NOT NULL' : '')."</td><td>".
						"<font color=\"#0000FF\">$lang[dbcheck_delete]</font></td></tr>";
				}

				$showlist .= '</td></tr></table><br />';
			}

			if($addlist) {
				$addlists .= "<tr class=\"category\"><td><b>$tablepre$dbtable</b> $lang[dbcheck_new_field]</td></tr>";

				foreach($addlist as $value) {
					$addlists .= "<tr><td class=\"altbg1\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$value['Field']."</b> ".$discuzdbnew[$dbtable][$value['Field']]['Type'].($discuzdbnew[$dbtable][$value['Field']]['Null'] == 'NO' ? ' NOT NULL' : '')."</td></tr>";
				}
			}

		}

		if($showlist) {
			$showlist = '<tr class="header"><td colspan="3">'.$lang['dbcheck_errorfields_tables'].'</td></tr><tr><td class="altbg1" colspan="3"><br />'.$showlist.'</td></tr>';
		}

		if($missingtables) {
			$showlist .= "<tr class=\"header\"><td colspan=\"3\">$lang[dbcheck_missing_tables]</td></tr>";
			$showlist .= '<tr class="altbg1"><td colspan="3">'.implode('', $missingtables).'</td></tr>';
		}

		if($settingsdellist) {
			$showlist .= "<tr class=\"header\"><td colspan=\"3\">$lang[dbcheck_settings]</td></tr>";
			$showlist .= '<tr class="altbg1"><td colspan="3">';
			$showlist .= "<input name=\"setting[del]\" class=\"checkbox\" type=\"checkbox\" value=\"1\"> ".implode(', ', $settingsdellist).'<br />';
			$showlist .= '</td></tr>';
		}

		$showlist = $showlist ? '<form method="post" action="admincp.php?action=dbcheck&start=yes"><input type="hidden" name="formhash" value="'.FORMHASH.'">'.
			'<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.$showlist.'</table><br /><center>'.
			'<input type="submit" class="button" name="repairsubmit" value="'.$lang['dbcheck_repair'].'"></center></form>' : '';

		if($charseterror) {
			$showlist .= '<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">';
			$showlist .= "<tr class=\"header\"><td colspan=\"3\">$lang[dbcheck_charseterror_tables] ($lang[dbcheck_charseterror_notice] $dbcharset)</td></tr>";
			$showlist .= '<tr class="altbg1"><td colspan="3">'.implode('', $charseterror).'</td></tr></table>';
		}

		if($addlists) {
			$showlist .= '<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
				'<tr class="header"><td colspan="3">'.$lang['dbcheck_userfields'].'</td></tr>'.$addlists.'</table>';
		}

	}

	if(!$showlist) {
		cpmsg('dbcheck_ok');
	} else {
		showtips('dbcheck_tips');

?>
<script type="text/javascript">
function setrepaircheck(obj, form, table) {
	eval('var rem = /^' + table + '\\|.+?\\|modify$/;');
	eval('var rea = /^' + table + '\\|.+?\\|add$/;');
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.type == 'checkbox' && e.name == 'repair[]') {
			if(rem.exec(e.value) != null) {
				if(obj.checked) {
					e.checked = false;
					e.disabled = true;
				} else {
					e.checked = false;
					e.disabled = false;

				}
			}
			if(rea.exec(e.value) != null) {
				if(obj.checked) {
					e.checked = true;
					e.disabled = false;
				} else {
					e.checked = false;
					e.disabled = false;
				}
			}
		}
	}
}
</script>
<?

		echo $showlist;
	}

?>
</form>
<?

} elseif($action == 'ftpcheck') {

	require_once './include/ftp.func.php';
	if(!empty($settingsnew['ftp']['password'])) {
		$settings['ftp'] = unserialize($db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='ftp'"));
		$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($authkey));
		$pwlen = strlen($settingsnew['ftp']['password']);
		if($settingsnew['ftp']['password']{0} == $settings['ftp']['password']{0} && $settingsnew['ftp']['password']{$pwlen - 1} == $settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} && substr($settingsnew['ftp']['password'], 1, $pwlen - 2) == '********') {
			$settingsnew['ftp']['password'] = $settings['ftp']['password'];
		}
	}
	$ftp['pasv'] = intval($settingsnew['ftp']['pasv']);
	$ftp_conn_id = dftp_connect($settingsnew['ftp']['host'], $settingsnew['ftp']['username'], $settingsnew['ftp']['password'], $settingsnew['ftp']['attachdir'], $settingsnew['ftp']['port'], $settingsnew['ftp']['ssl'], 1);
	switch($ftp_conn_id) {
		case '-1':
			$alertmsg = $lang['settings_remote_conerr'];
			break;
		case '-2':
			$alertmsg = $lang['settings_remote_logerr'];
			break;
		case '-3':
			$alertmsg = $lang['settings_remote_pwderr'];
			break;
		case '-4':
			$alertmsg = $lang['settings_remote_ftpoff'];
			break;
		default:
			$alertmsg = '';
	}
	if(!$alertmsg) {
		$tmpdir = md5('Discuz!' + $timestamp);
		if(!dftp_mkdir($ftp_conn_id, $tmpdir)) {
			$alertmsg = $lang['settings_remote_mderr'];
		} else {
			if(!(function_exists('ftp_chmod') && dftp_chmod($ftp_conn_id, 0777, $tmpdir)) && !dftp_site($ftp_conn_id, "'CHMOD 0777 $tmpdir'") && !@ftp_exec($ftp_conn_id, "SITE CHMOD 0777 $tmpdir")) {
				$alertmsg = $lang['settings_remote_chmoderr'].'\n';
			}
			$tmpfile = $tmpdir.'/test.txt';
			if(!dftp_put($ftp_conn_id, $tmpfile, DISCUZ_ROOT.'./robots.txt', FTP_BINARY)) {
				$alertmsg .= $lang['settings_remote_uperr'];
				dftp_delete($ftp_conn_id, $tmpfile);
				dftp_delete($ftp_conn_id, $tmpfile.'.uploading');
				dftp_delete($ftp_conn_id, $tmpfile.'.abort');
				dftp_rmdir($ftp_conn_id, $tmpdir);
			} else {
				if(!@readfile($settingsnew['ftp']['attachurl'].'/'.$tmpfile)) {
					$alertmsg .= $lang['settings_remote_geterr'];
					dftp_delete($ftp_conn_id, $tmpfile);
					dftp_rmdir($ftp_conn_id, $tmpdir);
				} else {
					if(!dftp_delete($ftp_conn_id, $tmpfile)) {
						$alertmsg .= $lang['settings_remote_delerr'];
					} else {
						dftp_rmdir($ftp_conn_id, $tmpdir);
						$alertmsg = $lang['settings_remote_ok'];
					}
				}
			}
		}
	}
	echo '<script language="javascript">alert(\''.str_replace('\'', '\\\'', $alertmsg).'\');parent.$(\'settings\').action=\'admincp.php?action=settings&edit=yes\';parent.$(\'settings\').target=\'_self\'</script>';

} elseif($action == 'mailcheck') {

	$mail = serialize($settingsnew['mail']);
	$test_tos = explode(',', $test_to);
	$date = date('Y-m-d H:i:s');
	$alertmsg = '';

	$title = $lang['settings_mailcheck_title_'.$settingsnew['mail']['mailsend']];
	$message = $lang['settings_mailcheck_message_'.$settingsnew['mail']['mailsend']].' '.$test_from.$lang['settings_mailcheck_date'].' '.$date;

	$bbname = $lang['settings_mailcheck_method_1'];
	sendmail($test_tos[0], $title.' @ '.$date, "$bbname\n\n\n$message", $test_from);
	$bbname = $lang['settings_mailcheck_method_2'];
	sendmail($test_to, $title.' @ '.$date, "$bbname\n\n\n$message", $test_from);

	if(!$alertmsg) {
		$alertmsg = $lang['settings_mailcheck_success_1']."$title @ $date".$lang['settings_mailcheck_success_2'];
	} else {
		$alertmsg = $lang['settings_mailcheck_error'].$alertmsg;
	}

	echo '<script language="javascript">alert(\''.str_replace(array('\'', "\n", "\r"), array('\\\'', '\n', ''), $alertmsg).'\');parent.$(\'settings\').action=\'admincp.php?action=settings&edit=yes\';parent.$(\'settings\').target=\'_self\'</script>';

} elseif($action == 'imagepreview') {

	if(!empty($previewthumb)) {
		$thumbstatus = $settingsnew['thumbstatus'];
		if(!$thumbstatus) {
			cpmsg('thumbpreview_error');
		}
		$imagelib = $settingsnew['imagelib'];
		$imageimpath = $settingsnew['imageimpath'];
		$thumbwidth = $settingsnew['thumbwidth'];
		$thumbheight = $settingsnew['thumbheight'];

		require_once DISCUZ_ROOT.'./include/image.class.php';
		@unlink(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg');
		$image = new Image('images/admincp/watermarkpreview.jpg', 'images/admincp/watermarkpreview.jpg');
		$image->Thumb($thumbwidth, $thumbheight, 1);
		if(file_exists(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg')) {
			shownav('imagepreview_thumb');
			echo '<center><img src="forumdata/watermark_temp.jpg?'.random(5).'"></center>';
		} else {
			cpmsg('thumbpreview_createerror');
		}
	} else {
		$watermarkstatus = $settingsnew['watermarkstatus'];
		if(!$watermarkstatus) {
			cpmsg('watermarkpreview_error');
		}
		$imagelib = $settingsnew['imagelib'];
		$imageimpath = $settingsnew['imageimpath'];
		$watermarktype = $settingsnew['watermarktype'];
		$watermarktrans = $settingsnew['watermarktrans'];
		$watermarkquality = $settingsnew['watermarkquality'];
		$watermarkminwidth = $settingsnew['watermarkminwidth'];
		$watermarkminheight = $settingsnew['watermarkminheight'];
		$settingsnew['watermarktext']['size'] = intval($settingsnew['watermarktext']['size']);
		$settingsnew['watermarktext']['angle'] = intval($settingsnew['watermarktext']['angle']);
		$settingsnew['watermarktext']['shadowx'] = intval($settingsnew['watermarktext']['shadowx']);
		$settingsnew['watermarktext']['shadowy'] = intval($settingsnew['watermarktext']['shadowy']);
		$settingsnew['watermarktext']['translatex'] = intval($settingsnew['watermarktext']['translatex']);
		$settingsnew['watermarktext']['translatey'] = intval($settingsnew['watermarktext']['translatey']);
		$settingsnew['watermarktext']['skewx'] = intval($settingsnew['watermarktext']['skewx']);
		$settingsnew['watermarktext']['skewy'] = intval($settingsnew['watermarktext']['skewy']);
		$settingsnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $settingsnew['watermarktext']['fontpath']);
		$settingsnew['watermarktext']['color'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $settingsnew['watermarktext']['color']);
		$settingsnew['watermarktext']['shadowcolor'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $settingsnew['watermarktext']['shadowcolor']);

		if($watermarktype == 2) {
			if($settingsnew['watermarktext']['fontpath']) {
				$fontpath = $settingsnew['watermarktext']['fontpath'];
				$fontpathnew = 'ch/'.$fontpath;
				$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
				if(!$settingsnew['watermarktext']['fontpath']) {
					$fontpathnew = 'en/'.$fontpath;
					$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
				}
				if(!$settingsnew['watermarktext']['fontpath']) {
					cpmsg('watermarkpreview_fontpath_error');
				}
				$settingsnew['watermarktext']['fontpath'] = 'images/fonts/'.$settingsnew['watermarktext']['fontpath'];
			}

			if($settingsnew['watermarktext']['text'] && strtoupper($charset) != 'UTF-8') {
				include DISCUZ_ROOT.'include/chinese.class.php';
				$c = new Chinese($charset, 'utf8');
				$settingsnew['watermarktext']['text'] = $c->Convert($settingsnew['watermarktext']['text']);
			}
			$settingsnew['watermarktext']['text'] = bin2hex($settingsnew['watermarktext']['text']);
			$watermarktext = $settingsnew['watermarktext'];
		}

		require_once DISCUZ_ROOT.'./include/image.class.php';
		@unlink(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg');
		$image = new Image('images/admincp/watermarkpreview.jpg', 'images/admincp/watermarkpreview.jpg');
		$image->Watermark(1);
		if(file_exists(DISCUZ_ROOT.'./forumdata/watermark_temp.jpg')) {
			shownav('imagepreview_watermark');
			echo '<center><br /><img src="forumdata/watermark_temp.jpg?'.random(5).'"></center>';
		} else {
			cpmsg('watermarkpreview_createerror');
		}
	}

}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type default CHARSET=$dbcharset" : " TYPE=$type");
}

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $md5data;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (preg_match($exts, $entry) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
			if($sub && is_dir($file)) {
				checkfiles($file.'/', $ext, $sub, $skip);
			} else {
				$md5data[$file] = md5_file($file);
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $authkey;
	$dir = opendir($currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = array();
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			$fp = fopen($file, "rb");
			$cachedata = fread($fp, filesize($file));
			fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Created: [\w\s,:]+\n\/\/Identify: (\w{32})\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$authkey) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = $addlist[$file] = '';
			}
		}

	}

	return array($showlist, $modifylist, $addlist);
}

function checkmailerror($type, $error) {
	global $alertmsg;
	$alertmsg .= !$alertmsg ? $error : '';
}

?>