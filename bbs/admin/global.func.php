<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: global.func.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

@set_time_limit(0);

function cpmsg($message, $url_forward = '', $msgtype = 'message', $extra = '', $cancelurl = '') {
	extract($GLOBALS, EXTR_SKIP);
	eval("\$message = \"".(isset($msglang[$message]) ? $msglang[$message] : $message)."\";");

	if($msgtype == 'form') {
		$message = "<form method=\"post\" action=\"$url_forward\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\">".
			"<br /><br /><br />$message$extra<br /><br /><br /><br />\n".
			"<input class=\"button\" type=\"submit\" name=\"confirmed\" value=\"$lang[ok]\"> &nbsp; \n".
			"<input class=\"button\" type=\"button\" value=\"$lang[cancel]\" onClick=\"".
			($cancelurl == '' ? 'history.go(-1)' : 'location.href=\''.$cancelurl.'\'').
			";\"></form><br />";
	} else {
		if($url_forward) {
			$message .= "<br /><br /><br /><a href=\"$url_forward\">$lang[message_redirect]</a>";
			$url_forward = transsid($url_forward);
			$message .= "<script>setTimeout(\"redirect('$url_forward');\", 2000);</script>";
		} elseif(strpos($message, $lang['return'])) {
			$message .= "<br /><br /><br /><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a>";
		}
		$message = "<br /><br /><br />$message$extra<br /><br />";
	}

?>
<br /><br /><br /><br /><br /><br />
<table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder">
<tr class="header"><td><?=$lang['discuz_message']?></td></tr><tr><td class="altbg2"><div align="center">
<?=$message?></div><br /><br />
</td></tr></table>
<br /><br /><br />
<?

	cpfooter();
	dexit();
}

function istpldir($dir) {
	return is_dir(DISCUZ_ROOT.'./'.$dir) && !in_array(substr($dir, -1, 1), array('/', '\\')) &&
		 strpos(realpath(DISCUZ_ROOT.'./'.$dir), realpath(DISCUZ_ROOT.'./templates')) === 0;
}

function isplugindir($dir) {
	return !$dir || (!preg_match("/(\.\.|[\\\\]+$)/", $dir) && substr($dir, -1) =='/');
}

function ispluginkey($key) {
	return preg_match("/^[a-z]+[a-z0-9_]*$/i", $key);
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}


function checkpermission($action, $break = 1) {
	if(!isset($GLOBALS['admincp'])) {
		cpmsg('action_access_noexists');
	} elseif($break && !$GLOBALS['admincp'][$action]) {
		cpmsg('action_noaccess_config');
	} else {
		return $GLOBALS['admincp'][$action];
	}
}

function showforum($key, $type = '') {
	global $forums, $showedforums, $lang, $indexname;

	$forum = $forums[$key];
	$showedforums[] = $key;

	return '<li><a href="'.($type == 'group' ? './'.$indexname.'?gid='.$forum['fid'] : './forumdisplay.php?fid='.$forum['fid']).'" target="_blank"><b>'.$forum['name'].'</b><span class="smalltxt">'.
		($forum['status'] ? '' : ' ('.$lang['forums_hidden'].')').'</span></a> - '.
		$lang['display_order'].': <input type="text" name="order['.$forum['fid'].']" value="'.$forum['displayorder'].'" size="1"> - '.
		($type != 'sub' ? '<a href="admincp.php?action=forumadd&fupid='.$forum['fid'].'" title="'.$lang['forums_add_comment'].'">['.$lang['forums_add'].']</a> ' : '').
		'<a href="admincp.php?action=forumdetail&fid='.$forum['fid'].'" title="'.$lang['forums_edit_comment'].'">['.$lang['edit'].']</a>'.
		($type != 'group' ? ' <a href="admincp.php?action=forumcopy&source='.$forum['fid'].'" title="'.$lang['forums_copy_comment'].'">['.$lang['forums_copy'].']</a> ' : ' ').
		'<a href="admincp.php?action=forumdelete&fid='.$forum['fid'].'" title="'.$lang['forums_delete_comment'].'">['.$lang['delete'].']</a> - '.
		'<a href="admincp.php?action=moderators&fid='.$forum['fid'].'" title="'.$lang['forums_moderators_comment'].'">['.$lang['forums_moderators'].($forum['moderators'] ? ': '.str_replace("\t", ', ', $forum['inheritedmod'] ? '<b>'.$forum['moderators'].'</b>' : $forum['moderators']) : '').']</a>'.
		'<br /></li>';
}

function showtype($name, $type = '', $submit = '', $colspan = 2) {
	$name = isset($GLOBALS['lang'][$name]) ? $GLOBALS['lang'][$name] : $name;
	$id = substr(md5($name), 16);
	$submithtml = $submit ? '<center><input class="button" type="submit" name="'.$submit.'" value="'.$GLOBALS['lang']['submit'].'"></center>' : '';
	if($type != 'bottom') {
		if(!$type) {
			echo '</table><br />';
		}
		if(!$type || $type == 'top') {

?>
<a name="<?=$id?>"></a>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="<?=$colspan?>"><?=$name?>
<a href="###" onclick="collapse_change('<?=$id?>')"><img id="menuimg_<?=$id?>" src="./images/admincp/menu_reduce.gif" border="0" style="float: right; margin-top: -12px; margin-right: 8px;" /></a>
</td>
</tr>
<tbody id="menu_<?=$id?>" style="display: yes">
<?

		}
	} else {
		echo '</tbody></table>'.$submithtml;
	}
}

function showsetting($setname, $varname, $value, $type = 'radio', $width = '45%', $disabled = '', $hidden = 0) {
	global $lang;
	$check = array();
	$comment = isset($lang[$setname.'_comment']) ? $lang[$setname.'_comment'] : '';
	$check['disabled'] = $disabled ? ' disabled' : '';
	$width = !$width ? '45%' : $width;

	$aligntop = $type == "textarea" || $width != "45%" ?  "valign=\"top\"" : NULL;
	echo "<tr><td width=\"$width\" class=\"altbg1\" $aligntop>".
		'<b>'.(isset($lang[$setname]) ? $lang[$setname] : $setname).'</b>'.($comment ? '<br /><span class="smalltxt">'.$comment.'</span>' : NULL).
		($disabled ? '<br /><span class="smalltxt" style="color:#FF0000">'.$lang[$setname.'_disabled'].'</span>' : NULL).'</td>'.
		'<td class="altbg2">';

	if($type == 'radio') {
		$value ? $check['true'] = "checked" : $check['false'] = "checked";
		$value ? $check['false'] = '' : $check['true'] = '';
		$check['hidden1'] = $hidden ? 'onclick="$(\'hidden_'.$setname.'\').style.display = \'\';"' : '';
		$check['hidden0'] = $hidden ? 'onclick="$(\'hidden_'.$setname.'\').style.display = \'none\';"' : '';
		echo "<input class=\"radio\" type=\"radio\" name=\"$varname\" value=\"1\" $check[true] $check[hidden1] $check[disabled]> {$lang['yes']} &nbsp; &nbsp; \n".
			"<input class=\"radio\" type=\"radio\" name=\"$varname\" value=\"0\" $check[false] $check[hidden0] $check[disabled]> {$lang['no']}\n";
	} elseif($type == 'radioplus') {
		$value == -1 ? $check['default'] = 'checked' : ($value ? $check['true'] = 'checked' : $check['false'] = 'checked');
		echo "<input class=\"radio\" type=\"radio\" name=\"$varname\" value=\"-1\" $check[default]> ".$lang['default']." &nbsp; &nbsp; \n".
			"<input class=\"radio\" type=\"radio\" name=\"$varname\" value=\"1\" $check[true]> {$lang['yes']} &nbsp; &nbsp; \n".
			"<input class=\"radio\" type=\"radio\" name=\"$varname\" value=\"0\" $check[false]> {$lang['no']}\n";
	} elseif($type{0} == 'm') {
		if(substr($type, 1) == 'radio') {
			$radiocheck = array($value => ' checked');
			$split = count($varname[1]) > 2 ? '<br />' : ' &nbsp; &nbsp; ';
			foreach($varname[1] as $varary) {
				$onclick = '';
				if(!empty($varary[2])) {
					foreach($varary[2] as $ctrlid => $display) {
						$onclick .= '$(\''.$ctrlid.'\').style.display = \''.$display.'\';';
					}
				}
				$onclick && $onclick = ' onclick="'.$onclick.'"';
				echo '<input class="radio" type="radio" name="'.$varname[0].'" value="'.$varary[0].'"'.$radiocheck[$varary[0]].$check['disabled'].$onclick.'> '.$varary[1].$split;
			}
		} else {
			$checkboxs = count($varname[1]);
			$value = sprintf('%0'.$checkboxs.'b', $value);$i = 1;
			foreach($varname[1] AS $key => $var) {
				echo '<input class="checkbox" type="checkbox" name="'.$varname[0].'['.$i.']" value="1"'.($value{$checkboxs - $i} ? ' checked' : '').' '.(!empty($varname[2][$key]) ? $varname[2][$key] : '').'> '.$var.'<br />';
				$i++;
			}
		}
	} elseif($type == 'color') {
		global $stylestuff;
		$preview_varname = str_replace('[', '_', str_replace(']', '', $varname));
		$code = explode(' ', $value);
		$css = '';
		for($i = 0; $i <= 1; $i++) {
			if($code[$i] != '') {
				if($code[$i]{0} == '#') {
					$css .= strtoupper($code[$i]).' ';
				} elseif(preg_match('/^http:\/\//i', $code[$i])) {
					$css .= 'url(\''.$code[$i].'\') ';
				} else {
					$css .= 'url(\''.$stylestuff['imgdir']['subst'].'/'.$code[$i].'\') ';
				}
			}
		}
		$background = trim($css);
		if(!$GLOBALS['coloridcount']) {
			echo "<script>
			function updatecolorpreview(obj) {
				var sp = $(obj + '_v').value.indexOf(' ');
				if(sp == -1) {
					var code = [$(obj + '_v').value];var codel = 1;
				} else {
					var code = [$(obj + '_v').value.substr(0, sp), $(obj + '_v').value.substr(sp + 1)];var codel = 2;
				}
				var css = '';
				for(i = 0;i < codel;i++) {
					if(code[i] != '') {
						if(code[i].substr(0, 1) == '#') {
							css += code[i] + ' ';
						} else {
							css += 'url(\"{$stylestuff['imgdir']['subst']}/' + code[i] + '\") ';
						}
					}
				}
				$(obj).style.background = css;
			}
			</script>";
		}
		$colorid = ++$GLOBALS['coloridcount'];
		echo "<input id=\"c{$colorid}_v\" type=\"text\" size=\"50\" value=\"$value\" name=\"$varname\" onchange=\"updatecolorpreview('c{$colorid}')\">\n".
			"<input id=\"c$colorid\" onclick=\"c{$colorid}_frame.location='images/admincp/getcolor.htm?c{$colorid}';showMenu('c$colorid')\" type=\"button\" value=\"\" style=\"width: 20px;background: $background\"><span id=\"c{$colorid}_menu\" style=\"display: none\" class=\"tableborder\"><iframe name=\"c{$colorid}_frame\" src=\"\" frameborder=\"0\" width=\"164\" height=\"184\" scrolling=\"no\"></iframe></span>\n";
	} elseif($type == 'text' || $type == 'password') {
		echo "<input type=\"$type\" size=\"50\" name=\"$varname\" value=\"".dhtmlspecialchars($value)."\" $check[disabled]>\n";
	} elseif($type == 'calendar') {
		echo "<input type=\"$type\" size=\"50\" name=\"$varname\" value=\"".dhtmlspecialchars($value)."\" onclick=\"showcalendar(event, this)\">\n";
	} elseif($type == 'textarea') {
		$readonly = $disabled ? 'readonly' : '';
		echo "<img src=\"images/admincp/zoomin.gif\" onmouseover=\"this.style.cursor='pointer'\" onclick=\"zoomtextarea('$varname', 1)\"> <img src=\"images/admincp/zoomout.gif\" onmouseover=\"this.style.cursor='pointer'\" onclick=\"zoomtextarea('$varname', 0)\"><br /><textarea $readonly rows=\"6\" name=\"$varname\" id=\"$varname\" cols=\"50\">".dhtmlspecialchars($value)."</textarea>";
	} elseif($type == 'select') {
		echo '<select name="'.$varname[0].'" style="width: 55%">';
		foreach($varname[1] as $option) {
			$selected = $option[0] == $value ? 'selected' : '';
			echo "<option value=\"$option[0]\" $selected>".$option[1]."</option>\n";
		}
		echo '</select>';
	} else {
		echo $type;
	}
	echo '</td></tr>';
	if($hidden) {
		echo '</tbody><tbody class="sub" id="hidden_'.$setname.'" style="display: '.($value ? '' : 'none').'">';
	}
}

function showmenu($title, $menus = array()) {
	global $menucount, $collapse;

	echo '<table width="146" border="0" cellspacing="0" align="center" cellpadding="0" class="leftmenulist" style="margin-bottom: 5px;">';
	if(is_array($menus)) {
		$menucount++;
		$collapsed = preg_match("/\[$menucount\]/", $collapse);

		echo 	'<tr class="leftmenutext"><td><a href="###" onclick="collapse_change('.$menucount.')"><img id="menuimg_'.$menucount.'" src="./images/admincp/menu_'.($collapsed ? 'add' : 'reduce').'.gif" border="0"/></a>&nbsp;'.
			'<a href="###" onclick="collapse_change('.$menucount.')">'.$title.'</a></td></tr>'.
			'<tbody id="menu_'.$menucount.'" style="display:'.($collapsed ? 'none' : '').'">'.
		 	'<tr class="leftmenutd"><td><table border="0" cellspacing="0" cellpadding="0" class="leftmenuinfo">';

		foreach($menus as $menudata) {
			echo $menudata['name'] ? '<tr><td><a href="'.$menudata['url'].'" target="'.($menudata['target'] ? $menudata['target'] : 'main').'">'.$menudata['name'].'</a></td></tr>' : '';
		}
		echo '</table></td></tr></tbody>';
	} else {
		echo "<tr class=\"leftmenutext\"><td><img src=\"./images/admincp/menu_reduce.gif\" />&nbsp;<a href=\"$menus\" target=\"main\">$title</a></td></tr>\n";
	}
	echo "</table>\n";
}

function showtips($tips) {
	extract($GLOBALS, EXTR_SKIP);
	global $_DCOOKIE;

	if(isset($lang[$tips])) {
		eval('$tips = "'.str_replace('"', '\\"', $lang[$tips]).'";');
	}

	$collapsed = preg_match("/\[tip\]/", isset($_DCOOKIE['collapse']) ? $_DCOOKIE['collapse'] : '');

	echo 	'<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
		'<tr class="header"><td><div style="float:left; margin-left:0px; padding-top:8px"><a href="###" onclick="collapse_change(\'tip\')">'.$lang['tips'].'</a></div><div style="float:right; margin-right:4px; padding-bottom:9px">'.
		'<a href="###" onclick="collapse_change(\'tip\')"><img id="menuimg_tip" src="./images/admincp/menu_'.($collapsed ? 'add' : 'reduce').'.gif" border="0"/></a></div>'.
		'</td></tr><tbody id="menu_tip" style="display:'.($collapsed ? 'none' : '').'"><tr><td>'.$tips.'</td></tr></tbody></table><br />';
}

function shownav($navs) {
	$navs = isset($GLOBALS['lang'][$navs]) ? $GLOBALS['lang'][$navs] : $navs;
	echo 	'<table width="100%" border="0" cellpadding="0" cellspacing="0" class="guide">'.
		'<tr><td><a href="#" onClick="parent.menu.location=\'admincp.php?action=menu\'; parent.main.location=\'admincp.php?action=home\';return false;">'.$GLOBALS['lang']['header_system'].'</a>&nbsp;&raquo;&nbsp;'.$navs.'</td></tr></table><br />';
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $db, $sizelimit, $startrow, $extendins, $sqlcompat, $sqlcharset, $dumpcharset, $usehex, $complete, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = $db->query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(strexists($table, 'adminsessions')) {
		return ;
	} elseif(!$query && $db->errno() == 1146) {
		return;
	} elseif(!$query) {
		$usehex = FALSE;
	} else {
		while($fieldrow = $db->fetch_array($query)) {
			$tablefields[] = $fieldrow;
		}
	}
	if(!$startfrom) {

		$createtable = $db->query("SHOW CREATE TABLE $table", 'SILENT');

		if(!$db->error()) {
			$tabledump = "DROP TABLE IF EXISTS $table;\n";
		} else {
			return '';
		}

		$create = $db->fetch_row($createtable);

		if(strpos($table, '.') !== FALSE) {
			$tablename = substr($table, strpos($table, '.') + 1);
			$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
		}
		$tabledump .= $create[1];

		if($sqlcompat == 'MYSQL41' && $db->version() < '4.1') {
			$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
		}
		if($db->version() > '4.1' && $sqlcharset) {
			$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);
		}

		$tablestatus = $db->fetch_first("SHOW TABLE STATUS LIKE '$table'");
		$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
		if($sqlcompat == 'MYSQL40' && $db->version() >= '4.1' && $db->version() < '5.1') {
			if($tablestatus['Auto_increment'] <> '') {
				$temppos = strpos($tabledump, ',');
				$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
			}
			if($tablestatus['Engine'] == 'MEMORY') {
				$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
			}
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if($extendins == '0') {
			while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = $db->query($selectsql);
				$numfields = $db->num_fields($rows);

				$numrows = $db->num_rows($rows);
				while($row = $db->fetch_row($rows)) {
					$comma = $t = '';
					for($i = 0; $i < $numfields; $i++) {
						$t .= $comma.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($t) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($t);\n";
					} else {
						$complete = FALSE;
						break 2;
					}
				}
			}
		} else {
			while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = $db->query($selectsql);
				$numfields = $db->num_fields($rows);

				if($numrows = $db->num_rows($rows)) {
					$t1 = $comma1 = '';
					while($row = $db->fetch_row($rows)) {
						$t2 = $comma2 = '';
						for($i = 0; $i < $numfields; $i++) {
							$t2 .= $comma2.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text'))? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma2 = ',';
						}
						if(strlen($t1) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$t1 .= "$comma1 ($t2)";
							$comma1 = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $t1;\n";
							$complete = FALSE;
							break 2;
						}
					}
					$tabledump .= "INSERT INTO $table VALUES $t1;\n";
				}
			}
		}

		$startrow = $startfrom;
		$tabledump .= "\n";
	}

	return $tabledump;
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function bbsinformation() {

	global $db, $timestamp, $tablepre, $charset, $bbname, $_SERVER, $siteuniqueid, $save_mastermobile;
	$update = array('uniqueid' => $siteuniqueid, 'version' => DISCUZ_VERSION, 'release' => DISCUZ_RELEASE, 'php' => PHP_VERSION, 'mysql' => $db->version(), 'charset' => $charset, 'bbname' => $bbname, 'mastermobile' => $save_mastermobile);

	$updatetime = @filemtime(DISCUZ_ROOT.'./forumdata/updatetime.lock');
	if(empty($updatetime) || ($timestamp - $updatetime > 3600 * 4)) {
		@touch(DISCUZ_ROOT.'./forumdata/updatetime.lock');
		$update['members'] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members");
		$update['threads'] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads");
		$update['posts'] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts");
		$query = $db->query("SELECT special, count(*) as spcount FROM {$tablepre}threads GROUP BY special");
		while($thread = $db->fetch_array($query)) {
			$thread['special'] = intval($thread['special']);
			$update['spt_'.$thread['special']] = $thread['spcount'];
		}
	}

	$data = '';
	foreach($update as $key => $value) {
		$data .= $key.'='.rawurlencode($value).'&';
	}

	return 'update='.rawurlencode(base64_encode($data)).'&md5hash='.substr(md5($_SERVER['HTTP_USER_AGENT'].implode('', $update).$timestamp), 8, 8).'&timestamp='.$timestamp;
}

function cpheader() {
	global  $charset, $cookiepre, $attackevasive;
	$IMGDIR = IMGDIR;
	print <<< EOT

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset">
<link href="./images/admincp/admincp.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">var IMGDIR = '$IMGDIR';var attackevasive = '$attackevasive';</script>
<script src="include/javascript/common.js" type="text/javascript"></script>
<script src="include/javascript/menu.js" type="text/javascript"></script>
<script src="include/javascript/ajax.js" type="text/javascript"></script>
<script type="text/javascript">
function checkalloption(form, value) {
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.value == value && e.type == 'radio' && e.disabled != true) {
			e.checked = true;
		}
	}
}

function checkallvalue(form, value, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.type == 'checkbox' && e.value == value) {
			e.checked = form.elements[checkall].checked;
		}
	}
}

function zoomtextarea(objname, zoom) {
	zoomsize = zoom ? 10 : -10;
	obj = \$(objname);
	if(obj.rows + zoomsize > 0 && obj.cols + zoomsize * 3 > 0) {
		obj.rows += zoomsize;
		obj.cols += zoomsize * 3;
	}
}

function redirect(url) {
	window.location.replace(url);
}

var collapsed = getcookie('{$cookiepre}collapse');
function collapse_change(menucount) {
	if(\$('menu_' + menucount).style.display == 'none') {
		\$('menu_' + menucount).style.display = '';collapsed = collapsed.replace('[' + menucount + ']' , '');
		\$('menuimg_' + menucount).src = './images/admincp/menu_reduce.gif';
	} else {
		\$('menu_' + menucount).style.display = 'none';collapsed += '[' + menucount + ']';
		\$('menuimg_' + menucount).src = './images/admincp/menu_add.gif';
	}
	setcookie('{$cookiepre}collapse', collapsed, 2592000);
}
</script>
</head>

<body leftmargin="10" topmargin="10">
<div id="append_parent"></div>
<table width="100%" border="0" cellpadding="2" cellspacing="6"><tr><td>
EOT;

}

function cpfooter() {
	global $version, $adminid, $db, $tablepre, $action, $bbname, $charset, $timestamp, $isfounder, $insenz;
	global $_COOKIE, $_SESSION, $_DCOOKIE, $_DCACHE, $_DSESSION, $_DCACHE, $_DPLUGIN, $sqldebug, $debuginfo;
	$infmessage = '';
?>
</td></tr></table>
<br /><br /><div class="footer"><hr size="0" noshade color="<?=BORDERCOLOR?>" width="80%">
Powered by <a href="http://www.discuz.net" target="_blank" style="color: <?=TEXT?>"><b>Discuz!</b> <?=$version?></a> &nbsp;&copy; 2001-2007, <b>
<a href="http://www.comsenz.com" target="_blank" style="color: <?=TEXT?>">Comsenz Inc.</a></b><span class="smalltxt"><?=$infmessage?></span></div>
</body>
</html>

<?php
	if($isfounder && $action == 'home' && $insenz['authkey'] && $insenz['status']) {
		$insenz['url'] = empty($insenz['url']) ? 'api.insenz.com' : $insenz['url'];
?>

<script src="http://<?=$insenz[url]?>/news.php?id=<?=$insenz[siteid]?>&t=<?=$timestamp?>&k=<?=md5($insenz[authkey].$insenz[siteid].$timestamp.'Discuz!')?>&insenz_version=<?=INSENZ_VERSION?>&discuz_version=<?=DISCUZ_VERSION.' - '.DISCUZ_RELEASE?>&random=<?=random(4)?>" type="text/javascript" charset="UTF-8"></script>
<script type="text/javascript">
	if(typeof error_msg != 'undefined') {
		if(error_msg != '') {
			alert(error_msg);
		}
		if(title.length || message != '') {
			$('insenznews').innerHTML = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'
				+ '<tr class="header"><td><?=$lang['insenz_note']?></td></tr><tr><td>'
				+ (message ? message : '')
				+ (title.length ? '<br /><b><?=$lang['insenz_note_new_campaign']?></b><a href="admincp.php?action=insenz&c_status=2"><font color="red"><u><?=$lang['insenz_note_link_to_go']?></u></font></a>' : '')
				+ '</td></tr></table><br />';
		}
	}
</script>

<?
	}
	if($adminid == 1 && $action == 'home') {
		echo '<sc'.'ript language="Jav'.'aScript" src="ht'.'tp:/'.'/cus'.'tome'.'r.disc'.'uz.n'.'et/n'.'ews'.'.p'.'hp?'.bbsinformation().'"></s'.'cri'.'pt>';
	}
	updatesession();
}

function isfounder($user = '') {
	$user = empty($user) ? array('uid' => $GLOBALS['discuz_uid'], 'adminid' => $GLOBALS['adminid'], 'username' => $GLOBALS['discuz_userss']) : $user;
	$founders = str_replace(' ', '', $GLOBALS['forumfounders']);
	if($user['adminid'] <> 1) {
		return FALSE;
	} elseif(empty($founders)) {
		return TRUE;
	} elseif(strexists(",$founders,", ",$user[uid],")) {
		return TRUE;
	} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function fetch_table_struct($tablename, $result = 'FIELD') {
	global $db, $tablepre;
	$datas = array();
	$query = $db->query("DESCRIBE $tablepre$tablename");
	while($data = $db->fetch_array($query)) {
		$datas[$data['Field']] = $result == 'FIELD' ? $data['Field'] : $data;
	}
	return $datas;
}

if(!function_exists('ajaxshowheader')) {
	function ajaxshowheader() {
		global $charset, $inajax;
		ob_end_clean();
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		header("Content-type: application/xml");
		echo "<?xml version=\"1.0\" encoding=\"$charset\"?>\n<root><![CDATA[";
	}
}

if(!function_exists('ajaxshowfooter')) {
	function ajaxshowfooter() {
		echo ']]></root>';
	}
}
?>