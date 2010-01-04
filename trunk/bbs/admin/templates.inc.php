<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: templates.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder');

if($action == 'templates') {

	if(!$edit) {

		if(!submitcheck('tplsubmit')) {

			$templates = '';
			$query = $db->query("SELECT * FROM {$tablepre}templates");
			while($tpl = $db->fetch_array($query)) {
				$templates .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" ".($tpl[templateid] == 1 ? 'disabled ' : '')."value=\"$tpl[templateid]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"8\" name=\"namenew[$tpl[templateid]]\" value=\"$tpl[name]\"></td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"20\" name=\"directorynew[$tpl[templateid]]\" value=\"$tpl[directory]\"></td>\n".
					"<td class=\"altbg2\">$tpl[copyright]</td>\n".
					"<td class=\"altbg1\"><a href=\"admincp.php?action=templates&edit=$tpl[templateid]\">[$lang[detail]]</a></td></tr>\n";
			}

			shownav('menu_styles_templates');

?>
<form method="post" action="admincp.php?action=templates&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['templates_name']?></td><td><?=$lang['templates_directory']?></td><td><?=$lang['copyright']?></td><td><?=$lang['edit']?></td></tr>
<?=$templates?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type="text" size="8" name="newname"></td>
<td><input type="text" size="20" name="newdirectory"></td>
<td><input type="text" size="25" name="newcopyright"></td>
<td>&nbsp;</td>
</tr></table><br />
<center><input class="button" type="submit" name="tplsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		} else {

			if($newname) {
				if(!$newdirectory) {
					cpmsg('templates_new_directory_invalid');
				} elseif(!istpldir($newdirectory)) {
					$directory = $newdirectory;
					cpmsg('templates_directory_invalid');
				}
				$db->query("INSERT INTO {$tablepre}templates (name, directory, copyright)
					VALUES ('$newname', '$newdirectory', '$newcopyright')", 'UNBUFFERED');
			}

			foreach($directorynew as $id => $directory) {
				if(!$delete || ($delete && !in_array($id, $delete))) {
					if(!istpldir($directory)) {
						cpmsg('templates_directory_invalid');
					} elseif($id == 1 && $directory != './templates/default') {
						cpmsg('templates_default_directory_invalid');
					}
					$db->query("UPDATE {$tablepre}templates SET name='$namenew[$id]', directory='$directorynew[$id]' WHERE templateid='$id'", 'UNBUFFERED');
				}
			}

			if(is_array($delete)) {
				if(in_array('1', $delete)) {
					cpmsg('templates_delete_invalid');
				}
				$ids = implodeids($delete);
				$db->query("DELETE FROM {$tablepre}templates WHERE templateid IN ($ids) AND templateid<>'1'", 'UNBUFFERED');
				$db->query("UPDATE {$tablepre}styles SET templateid='1' WHERE templateid IN ($ids)", 'UNBUFFERED');
			}

			updatecache('styles');
			cpmsg('templates_update_succeed', 'admincp.php?action=templates');

		}

	} else {

		$template = $db->fetch_first("SELECT * FROM {$tablepre}templates WHERE templateid='$edit'");
		if(!$template) {
			cpmsg('undefined_action');
		} elseif(!istpldir($template['directory'])) {
			$directory = $template['directory'];
			cpmsg('templates_directory_invalid');
		}

		$warning = $template['templateid'] == 1 ? 'templates_edit_default_comment' : 'templates_edit_nondefault_comment';
		if($keyword) {
			$keywordadd = " - $lang[templates_keyword] <i>".dhtmlspecialchars(stripslashes($keyword))."</i> - <a href=\"admincp.php?action=templates&edit=$edit\">[$lang[templates_view_all]]</a>";
			$keywordenc = rawurlencode($keyword);
		}

		$tpldir = dir(DISCUZ_ROOT.'./'.$template['directory']);
		$tplarray = $langarray = $differ = $new = array();
		while($entry = $tpldir->read()) {
			$extension = strtolower(fileext($entry));
			if($extension == 'htm' || $extension == 'php') {
				if($extension == 'htm') {
					$tplname = substr($entry, 0, -4);
					$pos = strpos($tplname, '_');
					if($keyword) {
						if(!stristr(implode("\n", file(DISCUZ_ROOT."./$template[directory]/$entry")), $keyword)) {
							continue;
						}
					}
					if(!$pos) {
						$tplarray[$tplname][] = $tplname;
					} else {
						$tplarray[substr($tplname, 0, $pos)][] = $tplname;
					}
				} else {
					$langarray[] = substr($entry, 0, -9);
				}
				if($template['templateid'] != 1) {
					if(file_exists(DISCUZ_ROOT."./templates/default/$entry")) {
						if(md5_file(DISCUZ_ROOT."./templates/default/$entry") != md5_file(DISCUZ_ROOT."./$template[directory]/$entry")) {
							$differ[] = $entry;
						}
					} else {
						$new[] = $entry;
					}
				}
			}
		}
		$tpldir->close();

		ksort($tplarray);
		ksort($langarray);
		$templates = $languages = '';

		$allowedittpls = checkpermission('tpledit', 0);
		foreach($tplarray as $tpl => $subtpls) {
			$templates .= "<ul><li><b>$tpl</b><ul>\n";
			foreach($subtpls as $subtpl) {
				$filename = "$subtpl.htm";
				$resetlink = '';
				if(in_array($filename, $differ)) {
					$subtpl = '<font color=\'#FF0000\'>'.$subtpl.'</font>';
					$resetlink = " <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$filename&reset=yes\">[$lang[templates_reset]]</a>";
				}
				if(in_array($filename, $new)) {
					$subtpl = '<font color=\'#00FF00\'>'.$subtpl.'</font>';
				}
				if($allowedittpls) {
					$templates .= "<li>$subtpl &nbsp; <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$filename&keyword=$keywordenc\">[$lang[edit]]</a> ".
						"<a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$filename&delete=yes\">[$lang[delete]]</a>$resetlink";
				} else {
					$templates .= "<li>$subtpl &nbsp; <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$filename&keyword=$keywordenc\">[$lang[view]]</a> ";
				}
			}
			$templates .= "</ul></ul>\n";
		}
		foreach($langarray as $langpack) {
			$resetlink = '';
			$langpackname = $langpack;
			if(is_array($differ) && in_array($langpack.'.lang.php', $differ)) {
				$langpackname = '<font color=\'#FF0000\'>'.$langpackname.'</font>';
				$resetlink = " <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$langpack.lang.php&reset=yes\">[$lang[templates_reset]]</a>";
			}
			if($allowedittpls) {
				$languages .= "<ul><li>$langpackname &nbsp; <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$langpack.lang.php\">[$lang[edit]]</a>";
				$languages .= $template['templateid'] != 1 ? " <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$langpack.lang.php&delete=yes\">[$lang[delete]]</a>" : '';
				$languages .= "$resetlink</ul>\n";
			} else {
				$languages .= "<ul><li>$langpackname &nbsp; <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$langpack.lang.php\">[$lang[view]]</a>";
				$languages .= "</ul>\n";
			}
		}

		shownav('templates_maint');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['templates_maint']?> - <?=$template['name']?></td></tr>

<form method="post" action="admincp.php?action=tpladd&edit=<?=$edit?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr class="altbg2"><td width="25%"><?=$lang['templates_maint_new']?></td>
<td width="55%"><input type="text" name="name" size="40" maxlength="40"></td>
<td width="20%"><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="get" action="admincp.php">
<input type="hidden" name="action" value="templates">
<input type="hidden" name="edit" value="<?=$edit?>">
<tr class="altbg1"><td><?=$lang['templates_maint_search']?></td><td><input type="text" name="keyword" size="40"></td>
<td><input class="button" type="submit" value="<?=$lang['submit']?>"></td></tr></form>

</table><br />
<?

showtips($warning);

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['templates_select']?><?=$keywordadd?></td></tr>
<tr class="altbg1"><td>
<ul><li><b>Discuz! <?=$lang['templates_language_pack']?></b><?=$languages?></ul>
<ul><li><b>Discuz! <?=$lang['templates_html']?></b><?=$templates?></ul>
</td></tr></table>
<?

	}

} elseif($action == 'tplcopy') {
	checkpermission('tpledit');
	$query = $db->query("SELECT directory FROM {$tablepre}templates WHERE templateid='$templateid'");
	if(!$srctemplate = $db->fetch_array($query)) {
		cpmsg('templates_edit_nonexistence');
	}

	$query = $db->query("SELECT directory FROM {$tablepre}templates WHERE templateid='$copyto'");
	if(!$desctemplate = $db->fetch_array($query)) {
		cpmsg('templates_edit_nonexistence');
	}

	if(!file_exists(DISCUZ_ROOT.$desctemplate['directory'])) {
		$directory = $desctemplate['directory'];
		cpmsg('templates_directory_invalid');
	}

	$newfilename = DISCUZ_ROOT.$desctemplate['directory']."/$fn";
	if(file_exists($newfilename) && !$confirmed) {
		cpmsg('templates_desctpl_exists', "admincp.php?action=tplcopy&templateid=$templateid&fn=$fn&copyto=$copyto", 'form');
	}

	if(!copy(DISCUZ_ROOT."./$srctemplate[directory]/$fn", $newfilename)) {
		cpmsg('templates_tplcopy_invalid');
	}

	cpmsg('templates_tplcopy_succeed', "admincp.php?action=tpledit&templateid=$templateid&fn=$fn");

} elseif($action == 'tpledit') {
	$allowedittpls = checkpermission('tpledit', 0);
	$query = $db->query("SELECT * FROM {$tablepre}templates WHERE templateid='$templateid'");
	if(!$template = $db->fetch_array($query)) {
		cpmsg('templates_edit_nonexistence');
	}

	$directorys = '';
	$query = $db->query("SELECT templateid, directory FROM {$tablepre}templates WHERE templateid!='$templateid' GROUP BY directory");
	while($directory = $db->fetch_array($query)) {
		$directorys .='<option value="'.$directory['templateid'].'">'.$directory['directory'].'</option>';
	}

	$fn = str_replace(array('..', '/', '\\'), array('', '', ''), $fn);
	$filename = DISCUZ_ROOT."./$template[directory]/$fn";
	if(!is_writeable($filename)) {
		cpmsg('templates_edit_invalid');
	}

	$keywordenc = rawurlencode($keyword);

	if(!submitcheck('editsubmit') && $delete != 'yes' && $reset != 'yes') {

		$islang = FALSE;
		if(preg_match('/\.lang\.php$/i', $filename) && $fn != 'customfaq.lang.php') {
			$currentlang = $lang;
			$currentmsglang = $msglang;
			unset($lang, $msglang);
			include $filename;
			$islang = TRUE;
			$langinputs = '';
			isset($actioncode) && $langinputs .= langedit('actioncode');
			isset($language) && $langinputs .= langedit('language');
			isset($lang) && $langinputs .= langedit('lang');
			isset($msglang) && $langinputs .= langedit('msglang');
			isset($spacelanguage) && $langinputs .= langedit('spacelanguage');
			$lang = $currentlang;
			$msglang = $currentmsglang;
		} else {
			$fp = @fopen($filename, 'rb');
			$content = @fread($fp, filesize($filename));
			fclose($fp);
		}

		$resetbutton = $onclickevent = $checkresult = '';
		if($template['templateid'] != 1) {
			$defaulttpl = DISCUZ_ROOT."./templates/default/$fn";
			if(file_exists($defaulttpl) && md5_file($defaulttpl) != md5_file($filename)) {
				$resetbutton = ' <input class="button" style="vertical-align: middle" type="button" value="'.$lang['templates_reset'].'" accesskey="r" onclick="location.href=\'admincp.php?action=tpledit&templateid='.$template['templateid'].'&fn='.$fn.'&keyword='.$keywordenc.'&reset=yes\'"> '.
					 (strtolower(fileext($fn)) == 'htm' ? '<input class="button" style="vertical-align: middle" type="button" value="'.$lang['templates_check'].'" onclick="location.href=\'admincp.php?action=tpledit&templateid='.$template['templateid'].'&fn='.$fn.'&keyword='.$keywordenc.'&checktpl=yes\'"> ' : '');
			}

			$dellist = $addlist = array();
			if($checktpl && strtolower(fileext($fn)) == 'htm') {
				$fp = @fopen($defaulttpl, 'rb');
				$defaultcontent = @fread($fp, filesize($defaulttpl));
				fclose($fp);
				if(substr($fn, 0, 3) == 'css') {
					$find = "/\.[^\{]+?\s+{/s";
				} else {
					$find = "/(\<\!\-\-)?\{.+?\}(\-\-\>)?/s";
				}

				preg_match_all($find, $defaultcontent, $defaultmatch);
				preg_match_all($find, $content, $match);
				$defaultarray = $matcharray = array();
				foreach($defaultmatch[0] as $value) {
					while(in_array($value, $defaultarray)) {
						$value .= ' ';
					}
					$defaultarray[] = $value;
				}
				foreach($match[0] as $value) {
					while(in_array($value, $matcharray)) {
						$value .= ' ';
					}
					$matcharray[] = $value;
				}
				$dellist = dhtmlspecialchars(array_diff($defaultarray, $matcharray));
				$addlist = dhtmlspecialchars(array_diff($matcharray, $defaultarray));

				if($dellist) {
					$checkresult .= '<tr class="category"><td>'.$lang['templates_check_del'].'</td></tr>';
					foreach($dellist as $item) {
						$checkresult .= '<tr><td class="altbg2">'.trim($item).'</td></tr>';
					}
				}
				if($addlist) {
					$checkresult .= '<tr class="category"><td>'.$lang['templates_check_add'].'</td></tr>';
					foreach($addlist as $item) {
						$checkresult .= '<tr><td class="altbg2">'.trim($item).'</td></tr>';
					}
				}
				$result = '<br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder"><tr class="header"><td>'.$lang['templates_check_result'].
						'&nbsp;&nbsp;<a href="admincp.php?action=tpledit&templateid=1&fn='.$fn.'&keyword='.$keywordenc.'" target="_blank">['.$lang['templates_default'].']</a></td></tr>';
				if($checkresult) {
					$result .= $checkresult.'</table>';
				} else {
					$result .= '<tr><td class="altbg2">'.$lang['templates_check_ok'].'</td></tr></table>';
				}
				$checkresult = $result;
			}
		} else {
			$onclickevent = 'onclick="return confirm(\''.$lang['templates_edit_default_overwriteconfirm'].'\')"';
		}

		$content = dhtmlspecialchars($content);

		shownav('templates_edit');

		$filemtime = date("$dateformat $timeformat", filemtime($filename));

?>
<script language="JavaScript">
var n = 0;
function displayHTML(obj) {
	win = window.open(" ", 'popup', 'toolbar = no, status = no, scrollbars=yes');
	win.document.write("" + obj.value + "");
}
function HighlightAll(obj) {
	obj.focus();
	obj.select();
	if(document.all) {
		obj.createTextRange().execCommand("Copy");
		window.status = "<?=$lang['templates_edit_clickboard']?>";
		setTimeout("window.status=''", 1800);
	}
}
function findInPage(obj, str, noalert) {
	var txt, i, found;
	if(str == "") {
		return false;
	}
	if(document.layers) {
		if(!obj.find(str)) {
			while(obj.find(str, false, true)) {
				n++;
			}
		} else {
			n++;
		}
		if(n == 0 && !noalert) {
			alert("<?=$lang['templates_edit_keyword_not_found']?>");
		}
	}
	if(document.all) {
		txt = obj.createTextRange();
		for(i = 0; i <= n && (found = txt.findText(str)) != false; i++) {
			txt.moveStart('character', 1);
			txt.moveEnd('textedit');
		}
		if(found) {
			txt.moveStart('character', -1);
			txt.findText(str);
			txt.select();
			txt.scrollIntoView();
			n++;
			return true;
		} else {
			if(n > 0) {
				n = 0;
				findInPage(obj, str, noalert);
			} else if(!noalert) {
				alert("<?=$lang['templates_edit_keyword_not_found']?>");
			}
		}
	}
	return false;
}

<?

if($islang) {

?>
	var ni = 0;
	var niprev = 0;
	function MultifindInPage(obj, str) {
		for(var i = ni; i < obj.elements.length; i++) {
			if(obj.elements[i].type == 'textarea') {
				if(findInPage(obj.elements[i], str, 1)) {
					ni = i;
					break;
				}
			}
			if(i == obj.elements.length - 1) ni = 0;
		}
	}
<?

}

?>
</script>
<form method="post" action="admincp.php?action=tpledit&templateid=<?=$templateid?>&fn=<?=$fn?>&">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="keyword" value="<?=$keywordenc?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['templates_edit']?> - <?=$template['name']?> <?=$fn?> - <?=$lang['filecheck_filemtime']?>: <?=$filemtime?></td></tr>
<tr><td class="altbg1" align="center"><div align="center">
<?

if($islang) {

?>
<div style="overflow-x: hidden;overflow-y: scroll; width:100%; height: 300px">
<table width="100%" border="0">
<?=$langinputs?>
</table>
</div>
</td></tr><tr><td class="altbg1" align="center"><div align="center">
<?

} else {

?>
<textarea cols="100" rows="25" name="templatenew" style="width: 95%;"><?=$content?></textarea><br />
<?

}

?>
<input name="search" type="text" accesskey="t" size="20" onChange="n=0;">
<?

if($islang) {

?>
<input class="button" type="button" value="<?=$lang['search']?>" accesskey="f" onClick="MultifindInPage(this.form, this.form.search.value)">&nbsp;&nbsp;&nbsp;
<?

} else {

?>
<input class="button" type="button" value="<?=$lang['search']?>" accesskey="f" onClick="findInPage(this.form.templatenew, this.form.search.value)">&nbsp;&nbsp;&nbsp;
<?

}

?>
<input class="button" type="button" value="<?=$lang['return']?>" accesskey="e" onClick="location.href='admincp.php?action=templates&edit=<?=$templateid?>&keyword=<?=$keywordenc?>'">
<input class="button" type="button" value="<?=$lang['preview']?>" accesskey="p" onClick="displayHTML(this.form.templatenew)">
<input class="button" type="button" value="<?=$lang['copy']?>" accesskey="c" onClick="HighlightAll(this.form.templatenew)">

<?
		if($allowedittpls) {
			echo "<input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\" $onclickevent><br />";
			if($directorys) {
				echo $lang['templates_copyto_otherdirs']."<select id=\"copyto\" style=\"vertical-align: middle\">".
					"$directorys</select> <input style=\"vertical-align: middle\" class=\"button\" type=\"button\" value=\"$lang[templates_start_copy]\" ".
					"accesskey=\"r\" onclick=\"if(\$('copyto').value == 1 && confirm('$lang[templates_edit_default_overwriteconfirm]') || \$('copyto').value != 1) location.href='admincp.php?action=tplcopy&templateid={$template['templateid']}&fn={$fn}&copyto='+\$('copyto').value\">";
			}
			echo $resetbutton;
		}
		echo '</div></td></tr></table></form>'.$checkresult;

	} elseif($delete == 'yes') {
		checkpermission('tpledit');
		if(!$confirmed) {
			cpmsg('templates_delete_confirm', "admincp.php?action=tpledit&templateid=$templateid&fn=$fn&delete=yes", 'form');
		} else {
			if(@unlink($filename)) {
				cpmsg('templates_delete_succeed', "admincp.php?action=templates&edit=$templateid");
			} else {
				cpmsg('templates_delete_fail');
			}
		}

	} elseif($reset == 'yes') {
		checkpermission('tpledit');
		if(!$confirmed) {
			cpmsg('templates_reset_confirm', "admincp.php?action=tpledit&templateid=$templateid&fn=$fn&keyword=$keywordenc&reset=yes", 'form');
		} else {
			$defaultfilename = DISCUZ_ROOT.'./templates/default/'.$fn;
			$filename = DISCUZ_ROOT."./$template[directory]/$fn";

			if(!copy($defaultfilename, $filename)) {
				cpmsg('templates_edit_invalid');
			}

			cpmsg('templates_reset_succeed', "admincp.php?action=templates&edit=$templateid&keyword=$keywordenc");
		}

	} else {
		checkpermission('tpledit');
		if(preg_match('/\.lang\.php$/i', $filename) && $fn != 'customfaq.lang.php') {
			$templatenew = '';
			foreach($langnew as $key => $value) {
				$templatenew .= '$'.$key." = array\n(\n";
				foreach($value as $key1 => $value1) {
					if(substr($value1, strlen($value1) -1 , 1) == '\\') {
						$value1 .= '\\\\';
					}
					$templatenew .= "\t'$key1' => '".str_replace('\\\\\'', '\\\'', addcslashes(stripslashes(str_replace("\x0d\x0a", "\x0a", $value1)), "'"))."',\n";
				}
				$templatenew .= ");\n";
			}
			$templatenew = "<?php\n\n// Language Pack for Discuz! Version 1.0.0\n\n$templatenew\n?>";
		} else {
			$templatenew = stripslashes(str_replace("\x0d\x0a", "\x0a", $templatenew));
		}
		$fp = fopen($filename, 'wb');
		flock($fp, 2);
		fwrite($fp, $templatenew);
		fclose($fp);

		if(substr(basename($filename), 0, 3) == 'css') {
			updatecache('styles');
		}

		cpmsg('templates_edit_succeed', "admincp.php?action=templates&edit=$templateid&keyword=$keywordenc");

	}

} elseif($action == 'tpladd') {
	checkpermission('tpledit');
	$query = $db->query("SELECT * FROM {$tablepre}templates WHERE templateid='$edit'");
	if(!$template = $db->fetch_array($query)) {
		cpmsg('templates_add_invalid');
	} elseif(!istpldir($template['directory'])) {
		$directory = $template['directory'];
		cpmsg('templates_directory_invalid');
	} elseif(file_exists(DISCUZ_ROOT."./$template[directory]/$name.htm")) {
		cpmsg('templates_add_duplicate');
	} elseif(!@$fp = fopen(DISCUZ_ROOT."./$template[directory]/$name.htm", 'wb')) {
		cpmsg('templates_add_file_invalid');
	}

	@fclose($fp);
	cpmsg('templates_add_succeed', "admincp.php?action=tpledit&templateid=1&fn=$name.htm");

}

function langedit($var) {
	$return = '<tr><td colspan="2"><b>'.$var.'</b></td></tr>';
	global $$var;
	foreach($$var as $key => $value) {
		$return .= '<tr><td width="100" style="border:0">'.$key.'</td><td style="border:0"><textarea cols="100" rows="3" name="langnew['.$var.']['.$key.']" style="width: 95%;">'.dhtmlspecialchars($value).'</textarea></td></tr>';
	}
	return $return;
}

?>