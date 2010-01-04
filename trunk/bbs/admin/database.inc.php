<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: database.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';;

require_once DISCUZ_ROOT.'./include/attachment.func.php';
cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder');


$excepttables = array_merge(array($tablepre.'adminsessions', $tablepre.'failedlogins', $tablepre.'pmsearchindex', $tablepre.'relatedthreads', $tablepre.'rsscaches', $tablepre.'searchindex', $tablepre.'spacecaches', $tablepre.'sessions'),
	($supe['status'] ? array($supe['tablepre'].'cache', $supe['tablepre'].'corpus', $supe['tablepre'].'rss', $supe['tablepre'].'spacecache', $supe['tablepre'].'tagcache') : array()));

if(!$backupdir = $db->result($db->query("SELECT value FROM {$tablepre}settings WHERE variable='backupdir'"), 0)) {
	$backupdir = random(6);
	@mkdir('./forumdata/backup_'.$backupdir, 0777);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) values ('backupdir', '$backupdir')");
}
$backupdir = 'backup_'.$backupdir;
if(!is_dir('./forumdata/'.$backupdir)) {
	mkdir('./forumdata/'.$backupdir, 0777);
}

if($action == 'export') {

	if(!submitcheck('exportsubmit', 1)) {

		$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
		$sqlcharsets = "<input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value=\"\" checked> $lang[default]".
			($dbcharset ? " &nbsp; <input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value=\"$dbcharset\"> ".strtoupper($dbcharset) : '').
			($db->version() > '4.1' && $dbcharset != 'utf8' ? " &nbsp; <input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value='utf8'> UTF-8</option>" : '');

		$tables = $tablelist = '';
		if($tables = $db->fetch_first("SELECT value FROM {$tablepre}settings WHERE variable='custombackup'")) {
			$tables = unserialize($tables['value']);
			$tables = is_array($tables) ? $tables : '';
		}

		$discuz_tables = fetchtablelist($tablepre);

		$query = $db->query("SELECT datatables FROM {$tablepre}plugins WHERE datatables<>''");
		while($plugin = $db->fetch_array($query)) {
			foreach(explode(',', $plugin['datatables']) as $table) {
				if($table = trim($table)) {
					$discuz_tables[] = array('Name' => $table);
				}
			}
		}

		$rowcount = 0;
		foreach($discuz_tables as $table) {
			$tablelist .= ($rowcount % 4 ? '' : '</tr><tr>')."<td><input class=\"checkbox\" type=\"checkbox\" name=\"customtables[]\" value=\"$table[Name]\" checked> $table[Name]</td>\n";
			$rowcount++;
		}
		$tablelist .= '</tr>';

		if(!empty($supe['tablepre'])) {
			$supe_tables = fetchtablelist($supe['tablepre']);
			$rowcount =0;
			$tablelist .='<tr><td colspan="4"><b>'.$lang['database_export_supe_table'].'</b>&nbsp;&nbsp;<input type="checkbox" name="chkall2" onclick="checkall(this.form, \'supetables\', \'chkall2\')"  class="checkbox" checked> <b>'.$lang['database_export_custom_select_all'].'</td></tr>';
			foreach($supe_tables as $table) {
				$tablelist .= ($rowcount % 4 ? '' : '</tr><tr>')."<td><input class=\"checkbox\" type=\"checkbox\" name=\"supetables[]\" value=\"$table[Name]\" checked> $table[Name]</td>\n";
				$rowcount++;
			}
		} else {
			$supe_tables = array();
		}
		shownav('menu_database_export');
		showtips('database_export_tips');

?>
<form name="backup" method="post" action="admincp.php?action=export">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="setup" value="1">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['database_export_type']?></td></tr>
<tr>
<td class="altbg1" width="40%"><input class="radio" type="radio" value="discuz" name="type" onclick="$('showtables').style.display='none'" checked> <?=$lang['database_export_discuz']?></td>
<td class="altbg2" width="45%"><?=$lang['database_export_discuz_comment']?></td></tr>

<?php if($supe['status']) {?>
<tr>
<td class="altbg1"><input class="radio" type="radio" value="discuzsupesite" name="type" onclick="$('showtables').style.display='none'"> <?=$lang['database_export_discuzsupesite']?></td>
<td class="altbg2"></td></tr>
<?php }?>

<tr>
<td class="altbg1"><input class="radio" type="radio" value="custom" name="type" onclick="$('showtables').style.display=''"> <?=$lang['database_export_custom']?></td>
<td class="altbg2"><?=$lang['database_export_custom_comment']?></td></tr>

<tbody id="showtables" style="display:none">
<tr>
<td class="altbg2" colspan="2">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td colspan="4"><b><?=$lang['database_export_discuz_table']?></b> <input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form, 'customtables')" checked> <b><?=$lang['database_export_custom_select_all']?></b>
</td>
<?=$tablelist?>
</table>
</td>
</tr>
</tbody>

<tr><td class="altbg1">&nbsp;</td>
<td align="right" class="altbg2" style="text-align: right;"><input class="checkbox" type="checkbox" value="1" onclick="$('advanceoption').style.display = $('advanceoption').style.display == 'none' ? '' : 'none'; this.value = this.value == 1 ? 0 : 1; this.checked = this.value == 1 ? false : true"><?=$lang['more_options']?> &nbsp; </td></tr>

<tbody id="advanceoption" style="display: none;">

<tr class="header"><td colspan="2"><?=$lang['database_export_method']?></td></tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="method" value="shell" <?=$shelldisabled?> onclick="if(<?=intval($db->version() < '4.1')?>) {if(this.form.sqlcompat[2].checked==true) this.form.sqlcompat[0].checked=true; this.form.sqlcompat[2].disabled=true; this.form.sizelimit.disabled=true;} else {this.form.sqlcharset[0].checked=true; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=true;}}"> <?=$lang['database_export_shell']?></td>
<td class="altbg2">&nbsp;</td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="method" value="multivol" checked onclick="this.form.sqlcompat[2].disabled=false; this.form.sizelimit.disabled=false; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=false;}"> <?=$lang['database_export_multivol']?></td>
<td class="altbg2"><input type="text" size="40" name="sizelimit" value="2048"></td>
</tr>

<tr class="header"><td colspan="2"><?=$lang['database_export_options']?></td></tr>

<tr>
<td class="altbg1">&nbsp;<?=$lang['database_export_options_extended_insert']?></td>
<td class="altbg2"><input class="radio" type="radio" name="extendins" value="1"> <?=$lang['yes']?> &nbsp; <input class="radio" type="radio" name="extendins" value="0" checked> <?=$lang['no']?></td>
</tr>

<tr>
<td class="altbg1">&nbsp;<?=$lang['database_export_options_sql_compatible']?></td>
<td class="altbg2"><input class="radio" type="radio" name="sqlcompat" value="" checked> <?=$lang['default']?> &nbsp; <input class="radio" type="radio" name="sqlcompat" value="MYSQL40"> MySQL 3.23/4.0.x &nbsp; <input class="radio" type="radio" name="sqlcompat" value="MYSQL41"> MySQL 4.1.x/5.x &nbsp;
</td>
</tr>

<tr>
<td class="altbg1">&nbsp;<?=$lang['database_export_options_charset']?></td>
<td class="altbg2"><?=$sqlcharsets?>
</td>
</tr>

<tr>
<td class="altbg1">&nbsp;<?=$lang['database_export_usehex']?></td>
<td class="altbg2"><input class="radio" type="radio" name="usehex" value="1" checked> <?=$lang['yes']?> &nbsp; <input class="radio" type="radio" name="usehex" value="0"> <?=$lang['no']?></td>
</td>
</tr>

<?

		if(function_exists('gzcompress')) {

?>

<tr>
<td class="altbg1">&nbsp;<?=$lang['database_export_usezip']?></td>
<td class="altbg2"><input class="radio" type="radio" name="usezip" value="1"> <?=$lang['database_export_zip_1']?> &nbsp; <input class="radio" type="radio" name="usezip" value="2"> <?=$lang['database_export_zip_2']?> &nbsp; <input class="radio" type="radio" name="usezip" value="0" checked> <?=$lang['database_export_zip_3']?></td>
</td>
</tr>

<?

		}

?>

<tr>
<td class="altbg1">&nbsp;<?=$lang['database_export_filename']?></td>
<td class="altbg2"><input type="text" size="40" name="filename" value="<?=date('ymd').'_'.random(8)?>"> .sql</td>
</tr>
</tbody>
</table><br /><center>
<input class="button" type="submit" name="exportsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		$db->query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

		if(!$filename || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $filename)) {
			cpmsg('database_export_filename_invalid');
		}

		$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);
		if($type == 'discuz') {
			$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
		} elseif($type == 'discuzsupesite') {
			$tables = arraykeys2(array_merge(fetchtablelist($tablepre), ($supe['tablepre'] ? fetchtablelist($supe['tablepre']) : array())), 'Name');
		} elseif($type == 'custom') {
			$tables = array();
			if(empty($setup)) {
				if($tables = $db->fetch_first("SELECT value FROM {$tablepre}settings WHERE variable='custombackup'")) {
					$tables = unserialize($tables['value']);
				}
			} else {
				$customtables= array_merge(empty($customtables) ? array() : $customtables, empty($supetables) ? array() : $supetables);
				$customtablesnew = empty($customtables)? '' : addslashes(serialize($customtables));
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('custombackup', '$customtablesnew')");
				$tables = & $customtables;
			}
			if( !is_array($tables) || empty($tables)) {
				cpmsg('database_export_custom_invalid');
			}
		}

		$query = $db->query("SELECT datatables FROM {$tablepre}plugins WHERE datatables<>''");
		while($plugin = $db->fetch_array($query)) {
			foreach(explode(',', $plugin['datatables']) as $table) {
				if($table = trim($table)) {
					$tables[] = $table;
				}
			}
		}

		$volume = intval($volume) + 1;
		$idstring = '# Identify: '.base64_encode("$timestamp,$version,$type,$method,$volume")."\n";


		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', $GLOBALS['charset']);
		$setnames = ($sqlcharset && $db->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if($db->version() > '4.1') {
			if($sqlcharset) {
				$db->query("SET NAMES '".$sqlcharset."';\n\n");
			}
			if($sqlcompat == 'MYSQL40') {
				$db->query("SET SQL_MODE='MYSQL40'");
			} elseif($sqlcompat == 'MYSQL41') {
				$db->query("SET SQL_MODE=''");
			}
		}

		$backupfilename = './forumdata/'.$backupdir.'/'.str_replace(array('/', '\\', '.'), '', $filename);

		if($usezip) {
			require_once DISCUZ_ROOT.'admin/zip.func.php';
		}

		if($method == 'multivol') {

			$sqldump = '';
			$tableid = intval($tableid);
			$startfrom = intval($startfrom);

			$complete = TRUE;
			for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $sizelimit * 1000; $tableid++) {
				$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
				if($complete) {
					$startfrom = 0;
				}
			}

			$dumpfile = $backupfilename."-%s".'.sql';
			!$complete && $tableid--;
			if(trim($sqldump)) {
				$sqldump = "$idstring".
					"# <?exit();?>\n".
					"# Discuz! Multi-Volume Data Dump Vol.$volume\n".
					"# Version: Discuz! $version\n".
					"# Time: $time\n".
					"# Type: $type\n".
					"# Table Prefix: $tablepre\n".
					"#\n".
					"# Discuz! Home: http://www.discuz.com\n".
					"# Please visit our website for newest infomation about Discuz!\n".
					"# --------------------------------------------------------\n\n\n".
					"$setnames".
					$sqldump;
				$dumpfilename = sprintf($dumpfile, $volume);
				@$fp = fopen($dumpfilename, 'wb');
				@flock($fp, 2);
				if(@!fwrite($fp, $sqldump)) {
					@fclose($fp);
					cpmsg('database_export_file_invalid');
				} else {
					fclose($fp);
					if($usezip == 2) {
						$fp = fopen($dumpfilename, "r");
						$content = @fread($fp, filesize($dumpfilename));
						fclose($fp);
						$zip = new zipfile();
						$zip->addFile($content, basename($dumpfilename));
						$fp = fopen(sprintf($backupfilename."-%s".'.zip', $volume), 'w');
						if(@fwrite($fp, $zip->file()) !== FALSE) {
							@unlink($dumpfilename);
						}
						fclose($fp);
					}
					unset($sqldump, $zip, $content);
					cpmsg('database_export_multivol_redirect', "admincp.php?action=export&type=".rawurlencode($type)."&saveto=server&filename=".rawurlencode($filename)."&method=multivol&sizelimit=".rawurlencode($sizelimit)."&volume=".rawurlencode($volume)."&tableid=".rawurlencode($tableid)."&startfrom=".rawurlencode($startrow)."&extendins=".rawurlencode($extendins)."&sqlcharset=".rawurlencode($sqlcharset)."&sqlcompat=".rawurlencode($sqlcompat)."&exportsubmit=yes&usehex=$usehex&usezip=$usezip");
				}
			} else {
				$volume--;
				$filelist = '<ul>';
				cpheader();

				if($usezip == 1) {
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$unlinks = '';
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($dumpfile, $i);
						$fp = fopen($filename, "r");
						$content = @fread($fp, filesize($filename));
						fclose($fp);
						$zip->addFile($content, basename($filename));
						$unlinks .= "@unlink('$filename');";
						$filelist .= "<li><a href=\"$filename\">$filename\n";
					}
					$fp = fopen($zipfilename, 'w');
					if(@fwrite($fp, $zip->file()) !== FALSE) {
						eval($unlinks);
					} else {
						cpmsg('database_export_multivol_succeed');
					}
					unset($sqldump, $zip, $content);
					fclose($fp);
					@touch('./forumdata/'.$backupdir.'/index.htm');
					$filename = $zipfilename;
					cpmsg('database_export_zip_succeed');
				} else {
					@touch('./forumdata/'.$backupdir.'/index.htm');
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($usezip == 2 ? $backupfilename."-%s".'.zip' : $dumpfile, $i);
						$filelist .= "<li><a href=\"$filename\">$filename\n";
					}
					cpmsg('database_export_multivol_succeed');
				}
			}

		} else {

			$tablesstr = '';
			foreach($tables as $table) {
				$tablesstr .= '"'.$table.'" ';
			}

			require './config.inc.php';
			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = $db->query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $db->fetch_array($query, MYSQL_NUM);

			$dumpfile = addslashes(dirname(dirname(__FILE__))).'/'.$backupfilename.'.sql';
			@unlink($dumpfile);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			@shell_exec($mysqlbin.'mysqldump --force --quick '.($db->version() > '4.1' ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($extendins == 1 ? ' --extended-insert' : '').''.($db->version() > '4.1' && $sqlcompat == 'MYSQL40' ? ' --compatible=mysql40' : '').' --host="'.$dbhost.($dbport ? (is_numeric($dbport) ? ' --port='.$dbport : ' --socket="'.$dbport.'"') : '').'" --user="'.$dbuser.'" --password="'.$dbpw.'" "'.$dbname.'" '.$tablesstr.' > '.$dumpfile);

			if(@file_exists($dumpfile)) {

				if($usezip) {
					require_once DISCUZ_ROOT.'admin/zip.func.php';
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$fp = fopen($dumpfile, "r");
					$content = @fread($fp, filesize($dumpfile));
					fclose($fp);
					$zip->addFile($idstring."# <?exit();?>\n ".$setnames."\n #".$content, basename($dumpfile));
					$fp = fopen($zipfilename, 'w');
					@fwrite($fp, $zip->file());
					fclose($fp);
					@unlink($dumpfile);
					@touch('./forumdata/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.zip';
					unset($sqldump, $zip, $content);
					cpmsg('database_export_zip_succeed');
				} else {
					if(@is_writeable($dumpfile)) {
						$fp = fopen($dumpfile, 'rb+');
						@fwrite($fp, $idstring."# <?exit();?>\n ".$setnames."\n #");
						fclose($fp);
					}
					@touch('./forumdata/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.sql';
					cpmsg('database_export_succeed');
				}

			} else {

				cpmsg('database_shell_fail');

			}

		}
	}

} elseif($action == 'importzip') {

	require_once DISCUZ_ROOT.'admin/zip.func.php';
	$unzip = new SimpleUnzip();
	$unzip->ReadFile($datafile_server);

	if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match("/\.sql$/i", $importfile = $unzip->GetName(0))) {
		cpmsg('database_import_file_illegal');
	}

	$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($unzip->GetData(0), 0, 256))));
	$confirm = !empty($confirm) ? 1 : 0;
	if(!$confirm && $identify[1] != $version) {
		cpmsg('database_import_confirm', 'admincp.php?action=importzip&datafile_server=$datafile_server&importsubmit=yes&confirm=yes', 'form');
	}

	$sqlfilecount = 0;
	foreach($unzip->Entries as $entry) {
		if(preg_match("/\.sql$/i", $entry->Name)) {
			$fp = fopen('./forumdata/'.$backupdir.'/'.$entry->Name, 'w');
			fwrite($fp, $entry->Data);
			fclose($fp);
			$sqlfilecount++;
		}
	}

	if(!$sqlfilecount) {
		cpmsg('database_import_file_illegal');
	}

	$info = basename($datafile_server).'<br />'.$lang['version'].': '.$identify[1].'<br />'.$lang['type'].': '.$lang['database_export_'.$identify[2]].'<br />'.$lang['database_method'].': '.($identify[3] == 'multivol' ? $lang['database_multivol'] : $lang['database_shell']).'<br />';

	if(isset($multivol)) {
		$multivol++;
		$datafile_server = preg_replace("/-(\d+)(\..+)$/", "-$multivol\\2", $datafile_server);
		if(file_exists($datafile_server)) {
			cpmsg('database_import_multivol_unzip_redirect', 'admincp.php?action=importzip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes');
		} else {
			cpmsg('database_import_multivol_confirm', 'admincp.php?action=import&from=server&datafile_server='.$datafile_vol1.'&importsubmit=yes&delunzip=yes', 'form', '', 'admincp.php?action=import');
		}
	}

	if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $datafile_server)) {
		$datafile_vol1 = $datafile_server;
		$datafile_server = preg_replace("/-1(\..+)$/", "-2\\1", $datafile_server);
		if(file_exists($datafile_server)) {
			cpmsg('database_import_multivol_unzip', 'admincp.php?action=importzip&multivol=1&datafile_vol1=./forumdata/'.$backupdir.'/'.$importfile.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'form');
		}
	}

	cpmsg('database_import_unzip', 'admincp.php?action=import&from=server&datafile_server=./forumdata/'.$backupdir.'/'.$importfile.'&importsubmit=yes&delunzip=yes', 'form', '', 'admincp.php?action=import');

} elseif($action == 'import') {

	checkpermission('dbimport');
	if(!submitcheck('importsubmit', 1) && !submitcheck('deletesubmit')) {

		$exportlog = array();
		if(is_dir(DISCUZ_ROOT.'./forumdata/'.$backupdir)) {
			$dir = dir(DISCUZ_ROOT.'./forumdata/'.$backupdir);
			while($entry = $dir->read()) {
				$entry = './forumdata/'.$backupdir.'/'.$entry;
				if(is_file($entry)) {
					if(preg_match("/\.sql$/i", $entry)) {
						$filesize = filesize($entry);
						$fp = fopen($entry, 'rb');
						$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						fclose ($fp);
						$exportlog[] = array(
							'version' => $identify[1],
							'type' => $identify[2],
							'method' => $identify[3],
							'volume' => $identify[4],
							'filename' => $entry,
							'dateline' => filemtime($entry),
							'size' => $filesize
						);
					} elseif(preg_match("/\.zip$/i", $entry)) {
						$filesize = filesize($entry);
						$exportlog[] = array(
							'type' => 'zip',
							'filename' => $entry,
							'size' => filesize($entry),
							'dateline' => filemtime($entry)
						);
					}
				}
			}
			$dir->close();
		} else {
			cpmsg('database_export_dest_invalid');
		}

		$exportinfo = '';
		foreach($exportlog as $info) {
			$info['dateline'] = is_int($info['dateline']) ? gmdate("$dateformat $timeformat", $info['dateline'] + $timeoffset * 3600) : $lang['unknown'];
			$info['size'] = sizecount($info['size']);
			$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
			$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? $lang['database_multivol'] : $lang['database_shell']) : '';
			$exportinfo .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".basename($info['filename'])."\"></td>\n".
				"<td class=\"altbg2\"><a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
				"<td class=\"altbg1\">$info[version]</td>\n".
				"<td class=\"altbg2\">$info[dateline]</td>\n".
				"<td class=\"altbg1\">".$lang['database_export_'.$info['type']]."</td>\n".
				"<td class=\"altbg2\">$info[size]</td>\n".
				"<td class=\"altbg1\">$info[method]</td>\n".
				"<td class=\"altbg2\">$info[volume]</td>\n".
				($info['type'] == 'zip' ? "<td class=\"altbg1\"><a href=\"admincp.php?action=importzip&datafile_server=$info[filename]&importsubmit=yes\">[$lang[database_import_unzip]]</a></td>\n" :
				"<td class=\"altbg1\"><a href=\"admincp.php?action=import&from=server&datafile_server=$info[filename]&importsubmit=yes\"".
				($info['version'] != $version ? " onclick=\"return confirm('$lang[database_import_confirm]');\"" : '').">[$lang[import]]</a></td>\n");
		}
		shownav('menu_database_import');
		showtips('database_import_tips');

?>
<form name="restore" method="post" action="admincp.php?action=import" enctype="multipart/form-data">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['database_import']?></td>
</tr>

<tr>
<td class="altbg1" width="40%"><input class="radio" type="radio" name="from" value="server" checked onclick="this.form.datafile_server.disabled=!this.checked;this.form.datafile.disabled=this.checked"><?=$lang['database_import_from_server']?></td>
<td class="altbg2" width="45%"><input type="text" size="40" name="datafile_server" value="./forumdata/<?=$backupdir?>/"></td></tr>

<tr>
<td class="altbg1" width="40%"><input class="radio" type="radio" name="from" value="local" onclick="this.form.datafile_server.disabled=this.checked;this.form.datafile.disabled=!this.checked"><?=$lang['database_import_from_local']?></td>
<td class="altbg2" width="45%"><input type="file" size="29" name="datafile" disabled></td></tr>

</table><br /><center>
<input class="button" type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center>
</form><br />

<form method="post" action="admincp.php?action=import">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="9"><?=$lang['database_export_file']?></td></tr>
<tr align="center" class="category"><td width="48"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['filename']?></td><td><?=$lang['version']?></td>
<td><?=$lang['time']?></td><td><?=$lang['type']?></td>
<td><?=$lang['size']?></td><td><?=$lang['database_method']?></td>
<td><?=$lang['database_volume']?></td><td><?=$lang['operation']?></td></tr>
<?=$exportinfo?>
</table><br /><center>
<input class="button" type="submit" name="deletesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	 } elseif(submitcheck('importsubmit', 1)) {

		$readerror = 0;
		$datafile = '';
		if($from == 'server') {
			$datafile = DISCUZ_ROOT.'./'.$datafile_server;
		}

		/*elseif($from == 'local') {
			$datafile = $_FILES['datafile']['tmp_name'];
		}*/
		if(@$fp = fopen($datafile, 'rb')) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
			$dumpinfo = array('method' => $identify[3], 'volume' => intval($identify[4]));
			if($dumpinfo['method'] == 'multivol') {
				$sqldump .= fread($fp, filesize($datafile));
			}
			fclose($fp);
		} else {
			if($autoimport) {
				updatecache();
				cpmsg('database_import_multivol_succeed');
			} else {
				cpmsg('database_import_file_illegal');
			}
		}

		if($dumpinfo['method'] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);
			$supetablepredot = strpos($supe['tablepre'], '.');
			$supe['dbname'] =  $supetablepredot !== FALSE ? substr($supe['tablepre'], 0, $supetablepredot) : '';

			foreach($sqlquery as $sql) {

				$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);

				if(substr($sql, 0, 11) == 'INSERT INTO') {
					$sqldbname = substr($sql, 12, 20);
					$dotpos = strpos($sqldbname, '.');
					if($dotpos !== FALSE) {
						if(empty($supe['dbmode'])) {
							$sql = 'INSERT INTO `'.$supe['dbname'].'`.'.substr($sql, 13 + $dotpos);
						} else {
							supe_dbconnect();
						}
					}
				}

				if($sql != '') {
					$db->query($sql, 'SILENT');
					if(($sqlerror = $db->error()) && $db->errno() != 1062) {
						$db->halt('MySQL Query Error', $sql);
					}
				}
			}

			if($delunzip) {
				@unlink($datafile_server);
			}

			$datafile_next = preg_replace("/-($dumpinfo[volume])(\..+)$/", "-".($dumpinfo['volume'] + 1)."\\2", $datafile_server);

			if($dumpinfo['volume'] == 1) {
				cpmsg('database_import_multivol_prompt',
					"admincp.php?action=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes".(!empty($delunzip) ? '&delunzip=yes' : ''),
					'form');
			} elseif($autoimport) {
				cpmsg('database_import_multivol_redirect', "admincp.php?action=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes".(!empty($delunzip) ? '&delunzip=yes' : ''));
			} else {
				updatecache();
				cpmsg('database_import_succeed');
			}
		} elseif($dumpinfo['method'] == 'shell') {
			require './config.inc.php';
			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = $db->query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $db->fetch_array($query, MYSQL_NUM);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			shell_exec($mysqlbin.'mysql -h"'.$dbhost.($dbport ? (is_numeric($dbport) ? ' -P'.$dbport : ' -S"'.$dbport.'"') : '').
				'" -u"'.$dbuser.'" -p"'.$dbpw.'" "'.$dbname.'" < '.$datafile);

			updatecache();
			cpmsg('database_import_succeed');
		} else {
			cpmsg('database_import_format_illegal');
		}

	} elseif(submitcheck('deletesubmit')) {
		if(is_array($delete)) {
			foreach($delete as $filename) {
				@unlink('./forumdata/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename));
			}
			cpmsg('database_file_delete_succeed');
		} else {
			cpmsg('database_file_delete_invalid');
		}
	}

} elseif($action == 'runquery') {
	$checkperm = checkpermission('runquery', 0);

	$runquerys = array();
	@include_once(DISCUZ_ROOT.'admin/quickqueries.inc.php');

	if(!submitcheck('sqlsubmit')) {
		shownav('menu_database_query');
		showtips('database_run_query_tips');

		$runqueryselect = '';
		foreach($simplequeries as $key => $query) {
			if(empty($query['sql'])) {
				$runqueryselect .= "<optgroup label=\"$query[comment]\">";
			} else {
				$runqueryselect .= '<option value="'.$key.'">'.$query['comment'].'</option>';
			}
		}
		if($runqueryselect) {
			$runqueryselect = '<select name="queryselect" style="width:45%">'.$runqueryselect.'</select>';
		}

		$queries = $queryselect ? $runquerys[$queryselect] : '';

?>
<form method="post" action="admincp.php?action=runquery">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="option" value="simple">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan=2><?=$lang['database_run_query_simply']?></td></tr>
<tr class="altbg1">
<td align="center"><center><?=$runqueryselect?> &nbsp;&nbsp;<input type="submit" name="sqlsubmit" value="<?=$lang['submit']?>"></center></td></tr>
</table>
</form>
<br />
<?

		if($checkperm) {

?>
<form method="post" action="admincp.php?action=runquery">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="option" value="">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan=2><?=$lang['database_run_query']?></td></tr>
<tr class="altbg1">
<td valign="top">
<div align="center">
<br /><textarea cols="85" rows="10" name="queries"><?=$queries?></textarea><br />
<br /><?=$lang['database_run_query_comment']?></div>
</td></tr></table>
<br /><center><input class="button" type="submit" name="sqlsubmit" value="<?=$lang['submit']?>"></center>
</form></td></tr>
<?

		}

	} else {

		if($option == 'simple') {
			$queryselect = intval($queryselect);
			$queries = isset($simplequeries[$queryselect]) && $simplequeries[$queryselect]['sql'] ? $simplequeries[$queryselect]['sql'] : '';
		} elseif(!$checkperm) {
			cpmsg('database_run_query_denied');
		}

		$sqlquery = splitsql(str_replace(array(' cdb_', ' {tablepre}', ' `cdb_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre), $queries));
		$affected_rows = 0;
		foreach($sqlquery as $sql) {
			if(trim($sql) != '') {
				$db->query(stripslashes($sql), 'SILENT');
				if($sqlerror = $db->error()) {
					break;
				} else {
					$affected_rows += intval($db->affected_rows());
				}
			}
		}

		cpmsg($sqlerror ? 'database_run_query_invalid' : 'database_run_query_succeed');
	}

} elseif($action == 'optimize') {
	shownav('menu_database_optimize');
	showtips('database_optimize_tips');

?>
<form name="optimize" method="post" action="admincp.php?action=optimize">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><input class="checkbox" type="checkbox" name="chkall" class="header" onclick="checkall(this.form)" checked><?=$lang['database_optimize_opt']?></td><td><?=$lang['database_optimize_table_name']?></td><td><?=$lang['type']?></td><td><?=$lang['database_optimize_rows']?></td>
<td><?=$lang['database_optimize_data']?></td><td><?=$lang['database_optimize_index']?></td><td><?=$lang['database_optimize_frag']?></td></tr>
<?

	$optimizetable = '';
	$totalsize = 0;
	$tablearray = empty($supe['tablepre']) ? array( 0 =>$tablepre) : array( 0 => $tablepre, 1 => $supe['tablepre']);
	if(!submitcheck('optimizesubmit')) {
		foreach($tablearray as $tp) {
			$query = $db->query("SHOW TABLE STATUS LIKE '$tp%'", 'SILENT');
			while($table = $db->fetch_array($query)) {
				if($table['Data_free'] && $table[$tabletype] == 'MyISAM') {
					$checked = $table[$tabletype] == 'MyISAM' ? 'checked' : 'disabled';
					echo "<tr><td class=\"altbg1\" align=\"center\"><input class=\"checkbox\" type=\"checkbox\" name=\"optimizetables[]\" value=\"$table[Name]\" $checked></td>\n".
						"<td class=\"altbg2\" align=\"center\">$table[Name]</td>\n".
						"<td class=\"altbg1\" align=\"center\">".$table[$tabletype]."</td>\n".
						"<td class=\"altbg2\" align=\"center\">$table[Rows]</td>\n".
						"<td class=\"altbg1\" align=\"center\">$table[Data_length]</td>\n".
						"<td class=\"altbg2\" align=\"center\">$table[Index_length]</td>\n".
						"<td class=\"altbg1\" align=\"center\">$table[Data_free]</td></tr>\n";
					$totalsize += $table['Data_length'] + $table['Index_length'];
				}
			}
		}
		if(empty($totalsize)) {
			echo "<tr><td colspan=\"7\" align=\"right\">".$lang['database_optimize_done']."</td></tr></table></div>";
		} else {
			echo "<tr><td colspan=\"7\" align=\"right\">$lang[database_optimize_used] ".sizecount($totalsize)."</td></tr></table></div><br /><center><input class=\"button\" type=\"submit\" name=\"optimizesubmit\" value=\"$lang[submit]\"></center>";
		}
	} else {
		//$db->query("DELETE FROM {$tablepre}subscriptions", 'UNBUFFERED');
		//$db->query("UPDATE {$tablepre}memberfields SET authstr=''", 'UNBUFFERED');

		foreach($tablearray as $tp) {
			$query = $db->query("SHOW TABLE STATUS LIKE '$tp%'", 'SILENT');
			while($table = $db->fetch_array($query)) {
				if(is_array($optimizetables) && in_array($table['Name'], $optimizetables)) {
					$db->query("OPTIMIZE TABLE $table[Name]");
				}

				echo "<tr>\n".
					"<td class=\"altbg1\" align=\"center\">".$lang['yes']."</td>\n".
					"<td class=\"altbg2\" align=\"center\">$table[Name]</td>\n".
					"<td class=\"altbg1\" align=\"center\">".($db->version() > '4.1' ?  $table['Engine'] : $table['Type'])."</td>\n".
					"<td class=\"altbg2\" align=\"center\">$table[Rows]</td>\n".
					"<td class=\"altbg1\" align=\"center\">$table[Data_length]</td>\n".
					"<td class=\"altbg2\" align=\"center\">$table[Index_length]</td>\n".
					"<td class=\"altbg1\" align=\"center\">0</td>\n".
					"</tr>\n";
				$totalsize += $table['Data_length'] + $table['Index_length'];
			}
		}
		echo "<tr><td colspan=\"7\" align=\"right\">$lang[database_optimize_used] ".sizecount($totalsize)."</td></tr></table>";
	}

	echo '</table></form>';
}

function fetchtablelist($tablepre = '') {
	global $db;
	$arr = explode('.', $tablepre);
	$dbname = $arr[1] ? $arr[0] : '';
	$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
	!$tablepre && $tablepre = '*';
	$tables = $table = array();
	$query = $db->query("SHOW TABLE STATUS $sqladd");
	while($table = $db->fetch_array($query)) {
		$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
		$tables[] = $table;
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $val) {
		$return[] = $val[$key2];
	}
	return $return;
}


function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

?>