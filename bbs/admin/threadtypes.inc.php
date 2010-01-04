<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: threadtypes.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'threadtypes') {

	if($special) {
		$special = 1;
		$navlang = 'threadtype_infotypes';
	} else {
		$special = 0;
		$navlang = 'forums_threadtypes';
	}

	if(!submitcheck('typesubmit')) {

		$forumsarray = $fidsarray = array();
		$query = $db->query("SELECT f.fid, f.name, ff.threadtypes FROM {$tablepre}forums f , {$tablepre}forumfields ff WHERE ff.threadtypes<>'' AND f.fid=ff.fid");
		while($forum = $db->fetch_array($query)) {
			$forum['threadtypes'] = unserialize($forum['threadtypes']);
			if(is_array($forum['threadtypes']['types'])) {
				foreach($forum['threadtypes']['types'] as $typeid => $name) {
					$forumsarray[$typeid][] = '<a href="forumdisplay.php?fid='.$forum['fid'].'" target="_blank">'.$forum['name'].'</a> [<a href="admincp.php?action=forumdetail&fid='.$forum['fid'].'">'.$lang['edit'].'</a>]';
					$fidsarray[$typeid][] = $forum['fid'];
				}
			}
		}

		if($special) {
			$typemodelopt = '';
			$query = $db->query("SELECT id, name FROM {$tablepre}typemodels ORDER BY displayorder");
			while($typemodel = $db->fetch_array($query)) {
				$typemodelopt .= "<option value=\"$typemodel[id]\" ".($typemodel['id'] == $threadtype['special'] ? 'selected="selected"' : '').">$typemodel[name]</option>";
			}
		}

		$threadtypes = '';
		$query = $db->query("SELECT * FROM {$tablepre}threadtypes WHERE ".($special ? "special!='0'" : "special='0'")." ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$threadtypes .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"namenew[$type[typeid]]\" value=\"".dhtmlspecialchars($type['name'])."\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"30\" name=\"descriptionnew[$type[typeid]]\" value=\"$type[description]\"></td>\n".
				"<td class=\"altbg1\">".(is_array($forumsarray[$type['typeid']]) ? implode(', ', $forumsarray[$type['typeid']])."<input type=\"hidden\" name=\"fids[$type[typeid]]\" value=\"".implode(', ', $fidsarray[$type['typeid']])."\">" : '')."</td>\n".
				($special ? "<td class=\"altbg2\"><a href=\"admincp.php?action=typedetail&typeid=$type[typeid]\">[$lang[detail]]</a></td></tr>\n" : '');
		}
		shownav($navlang);
		!$special ? showtips('forums_threadtypes_tips') : '';

?>
<form method="post" action="admincp.php?action=threadtypes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="special" value="<?=$special?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang[$navlang]?></td></tr>
<tr align="center" class="category">
<td><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['name']?></td>
<td><?=$lang['display_order']?></td>
<td><?=$lang['description']?></td>
<td><?=$lang['forums_threadtypes_forums']?></td>
<?
echo $special ? "<td>$lang[detail]</td>" : '';
?>
</tr>
<?=$threadtypes?>
<tr align="center" class="altbg1">
<td><?=$lang['add_new']?></td>
<td><input type='text' name="newname" size="15"></td>
<td><input type="text" name="newdisplayorder" size="2" value="0"></td>
<td><input type="text" name="newdescription" size="30" value=""></td>
<td>&nbsp;</td>
<?
echo $special ? '<td>&nbsp;</td>' : '';
?>
</tr>
</table><br />
<center><input class="button" type="submit" name="typesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$updatefids = $modifiedtypes = array();

		if(is_array($delete)) {

			$deleteids = implodeids($delete);
			$db->query("DELETE FROM {$tablepre}typeoptionvars WHERE typeid IN ($deleteids)");
			$db->query("DELETE FROM {$tablepre}tradeoptionvars WHERE typeid IN ($deleteids)");
			$db->query("DELETE FROM {$tablepre}typevars WHERE typeid IN ($deleteids)");
			$db->query("DELETE FROM {$tablepre}threadtypes WHERE typeid IN ($deleteids) AND special='$special'");

			if($db->affected_rows()) {
				$db->query("UPDATE {$tablepre}threads SET typeid='0' WHERE typeid IN ($deleteids)");
				foreach($delete AS $id) {
					if(is_array($namenew) && isset($namenew[$id])) {
						unset($namenew[$id]);
					}
					if(!empty($fids[$id])) {
						foreach(explode(',', $fids[$id]) AS $fid) {
							if($fid = intval($fid)) {
								$updatefids[$fid]['deletedids'][] = intval($id);
							}
						}
					}
				}
			}
		}

		if(is_array($namenew) && $namenew) {
			foreach($namenew as $typeid => $val) {
				$db->query("UPDATE {$tablepre}threadtypes SET name='".trim($namenew[$typeid])."', description='".dhtmlspecialchars(trim($descriptionnew[$typeid]))."', displayorder='$displayordernew[$typeid]', special='$special' WHERE typeid='$typeid'");
				if($db->affected_rows()) {
					$modifiedtypes[] = $typeid;
				}
			}

			if($modifiedtypes = array_unique($modifiedtypes)) {
				foreach($modifiedtypes AS $id) {
					if(!empty($fids[$id])) {
						foreach(explode(',', $fids[$id]) AS $fid) {
							if($fid = intval($fid)) {
								$updatefids[$fid]['modifiedids'][] = $id;
							}
						}
					}
				}
			}
		}

		if($updatefids) {
			$query = $db->query("SELECT fid, threadtypes FROM {$tablepre}forumfields WHERE fid IN (".implodeids(array_keys($updatefids)).") AND threadtypes<>''");
			while($forum = $db->fetch_array($query)) {
				$fid = $forum['fid'];
				$forum['threadtypes'] = unserialize($forum['threadtypes']);
				if($updatefids[$fid]['deletedids']) {
					foreach($updatefids[$fid]['deletedids'] AS $id) {
						unset($forum['threadtypes']['types'][$id], $forum['threadtypes']['flat'][$id], $forum['threadtypes']['selectbox'][$id]);
					}
				}
				if($updatefids[$fid]['modifiedids']) {
					foreach($updatefids[$fid]['modifiedids'] AS $id) {
						if(isset($forum['threadtypes']['types'][$id])) {
							$namenew[$id] = trim($namenew[$id]);
							$forum['threadtypes']['types'][$id] = $namenew[$id];
							if(isset($forum['threadtypes']['selectbox'][$id])) {
								$forum['threadtypes']['selectbox'][$id] = $namenew[$id];
							} else {
								$forum['threadtypes']['flat'][$id] = $namenew[$id];
							}
						}
					}
				}
				$db->query("UPDATE {$tablepre}forumfields SET threadtypes='".addslashes(serialize($forum['threadtypes']))."' WHERE fid='$fid'");
			}
		}

		if($newname != '') {
			$newname = trim($newname);
			$query = $db->query("SELECT typeid FROM {$tablepre}threadtypes WHERE name='$newname'");
			if($db->num_rows($query)) {
				cpmsg('forums_threadtypes_duplicate');
			}
			$db->query("INSERT INTO	{$tablepre}threadtypes (name, description, displayorder, special) VALUES
					('$newname', '".dhtmlspecialchars(trim($newdescription))."', '$newdisplayorder', '$special')");
			$typeid = $db->insert_id();
		}

		$forwardurl = !$newname || ($newname && !$special) ? 'admincp.php?action=threadtypes&special='.$special : 'admincp.php?action=typedetail&typeid='.$typeid;
		cpmsg('forums_threadtypes_succeed', $forwardurl);

	}

} elseif($action == 'typeoption') {

	if(!submitcheck('typeoptionsubmit')) {

		$typeoptions = '';
		$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE classid='0' ORDER BY displayorder");
		while($option = $db->fetch_array($query)) {
			$classoptions .= "<input class=\"button\" type=\"button\" value=\"$option[title]\" onclick=\"window.location='admincp.php?action=typeoption&classid=$option[optionid]';\"> &nbsp;";
		}

		$classid = $classid ? intval($classid) : $db->result_first("SELECT * FROM {$tablepre}typeoptions WHERE classid='0' ORDER BY displayorder LIMIT 1");

		if($classid) {
			if(!$typetitle = $db->result_first("SELECT title FROM {$tablepre}typeoptions WHERE optionid='$classid'")) {
				cpmsg('threadtype_infotypes_noexist', 'admincp.php?action=threadtypes');
			}

			$typeoptions = '';
			$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE classid='$classid' ORDER BY displayorder");
			while($option = $db->fetch_array($query)) {
				$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$typeoptions .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\"></td>\n".
					"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"title[$option[optionid]]\" value=\"".dhtmlspecialchars($option['title'])."\"></td>\n".
					"<td class=\"altbg1\">$option[identifier]<input type=\"hidden\" name=\"identifier[$option[optionid]]\" value=\"$option[identifier]\"></td>\n".
					"<td class=\"altbg2\">$option[type]</td>\n".
					"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\"></td>\n".
					"<td class=\"altbg2\"><a href=\"admincp.php?action=optiondetail&optionid=$option[optionid]\">[$lang[detail]]</a></td></tr>\n";
			}
		}

		shownav('threadtype_infotypes_option');

?>
<form method="post" action="admincp.php?action=typeoption&typeid=<?=$typeid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="classid" value="<?=$classid?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['threadtype_cat_manage']?></td></tr>
<tr class="category" align="center">
<td><?=$classoptions?></td>
</tr>
</table><br />
<?if($classid) {?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['threadtype_manage']?> - <?=$typetitle?></td></tr>
<tr class="category" align="center">
<td width="45"><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['name']?></td>
<td><?=$lang['threadtype_variable']?></td>
<td><?=$lang['threadtype_type']?></td>
<td><?=$lang['display_order']?></td>
<td><?=$lang['edit']?></td></tr>
<?=$typeoptions?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type="text" size="15" name="newtitle"></td>
<td><input type="text" size="15" name="newidentifier"></td>
<td><select name="newtype">
<option value="number"><?=$lang['threadtype_edit_vars_type_number']?></option>
<option value="text" selected><?=$lang['threadtype_edit_vars_type_text']?></option>
<option value="textarea"><?=$lang['threadtype_edit_vars_type_textarea']?></option>
<option value="radio"><?=$lang['threadtype_edit_vars_type_radio']?></option>
<option value="checkbox"><?=$lang['threadtype_edit_vars_type_checkbox']?></option>
<option value="select"><?=$lang['threadtype_edit_vars_type_select']?></option>
<option value="calendar"><?=$lang['threadtype_edit_vars_type_calendar']?></option>
<option value="email"><?=$lang['threadtype_edit_vars_type_email']?></option>
<option value="image"><?=$lang['threadtype_edit_vars_type_image']?></option>
<option value="url"><?=$lang['threadtype_edit_vars_type_url']?></option>
</seletc></td><td><input type="text" size="2" name="newdisplayorder" value="0"></td>
<td>&nbsp;</td></tr>
</table><br />
<?}?>
<center><input class="button" type="submit" name="typeoptionsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}typeoptions WHERE optionid IN ($ids)");
		}

		if(is_array($title)) {
			foreach($title as $id => $val) {
				$db->query("UPDATE {$tablepre}typeoptions SET displayorder='$displayorder[$id]', title='$title[$id]', identifier='$identifier[$id]' WHERE optionid='$id'");
			}
		}

		$newtitle = dhtmlspecialchars(trim($newtitle));
		$newidentifier = trim($newidentifier);
		if($newtitle && $newidentifier) {
			$query = $db->query("SELECT optionid FROM {$tablepre}typeoptions WHERE identifier='$newidentifier' LIMIT 1");
			if($db->num_rows($query) || strlen($newidentifier) > 40  || !ispluginkey($newidentifier)) {
				cpmsg('threadtype_infotypes_optionvariable_invalid');
			}
			$db->query("INSERT INTO {$tablepre}typeoptions (classid, displayorder, title, identifier, type)
				VALUES ('$classid', '$newdisplayorder', '$newtitle', '$newidentifier', '$newtype')");
		} elseif($newtitle && !$newidentifier) {
			cpmsg('threadtype_infotypes_option_invalid', 'admincp.php?action=typeoption&classid='.$classid);
		}
		updatecache('threadtypes');
		cpmsg('threadtype_infotypes_succeed', 'admincp.php?action=typeoption&classid='.$classid);

	}

} elseif($action == 'optiondetail') {

	$option = $db->fetch_first("SELECT * FROM {$tablepre}typeoptions WHERE optionid='$optionid'");
	if(!$option) {
		cpmsg('undefined_action');
	}

	if(!submitcheck('editsubmit')) {

		shownav('threadtype_infotypes_option');

		$typeselect = '<select name="typenew" onchange="var styles, key;styles=new Array(\'number\',\'text\',\'radio\', \'checkbox\', \'textarea\', \'select\', \'image\'); for(key in styles) {var obj=$(\'style_\'+styles[key]); obj.style.display=styles[key]==this.options[this.selectedIndex].value?\'\':\'none\';}">';
		foreach(array('number', 'text', 'radio', 'checkbox', 'textarea', 'select', 'calendar', 'email', 'url', 'image') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($option['type'] == $type ? 'selected' : '').'>'.$lang['threadtype_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		$option['rules'] = unserialize($option['rules']);

		echo "<form method=\"post\" action=\"admincp.php?action=optiondetail&optionid=$optionid&formhash=".FORMHASH."\">\n";

		showtype('threadtype_infotypes_option_config', 'top');
		showsetting($lang['name'], 'titlenew', $option['title'], 'text');
		showsetting($lang['threadtype_variable'], 'identifiernew', $option['identifier'], 'text');
		showsetting($lang['type'], '', '', $typeselect);
		showsetting('fields_edit_desc', 'descriptionnew', $option['description'], 'textarea');

		showoptiontype('number', $option['type']);
		showsetting('fields_edit_maxnum', 'rules[number][maxnum]', $option['rules']['maxnum'], 'text');
		showsetting('fields_edit_minnum', 'rules[number][minnum]', $option['rules']['minnum'], 'text');

		showoptiontype('text', $option['type']);
		showsetting('fields_edit_textmax', 'rules[text][maxlength]', $option['rules']['maxlength'], 'text');

		showoptiontype('textarea', $option['type']);
		showsetting('fields_edit_textmax', 'rules[textarea][maxlength]', $option['rules']['maxlength'], 'text');

		showoptiontype('select', $option['type']);
		showsetting('fields_edit_choices', 'rules[select][choices]', $option['rules']['choices'], 'textarea');

		showoptiontype('radio', $option['type']);
		showsetting('fields_edit_choices', 'rules[radio][choices]', $option['rules']['choices'], 'textarea');

		showoptiontype('checkbox', $option['type']);
		showsetting('fields_edit_choices', 'rules[checkbox][choices]', $option['rules']['choices'], 'textarea');

		showoptiontype('image', $option['type']);
		showsetting('fields_edit_images_weight', 'rules[image][maxwidth]', $option['rules']['maxwidth'], 'text');
		showsetting('fields_edit_images_height', 'rules[image][maxheight]', $option['rules']['maxheight'], 'text');

		showtype('', 'bottom');

		echo "</div><center><input class=\"button\" type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

	} else {

		$titlenew = trim($titlenew);
		if(!$titlenew || !$identifiernew) {
			cpmsg('threadtype_infotypes_option_invalid');
		}

		$query = $db->query("SELECT optionid FROM {$tablepre}typeoptions WHERE identifier='$identifiernew' AND optionid!='$optionid' LIMIT 1");
		if($db->num_rows($query) || strlen($identifiernew) > 40  || !ispluginkey($identifiernew)) {
			cpmsg('threadtype_infotypes_optionvariable_invalid');
		}

		$db->query("UPDATE {$tablepre}typeoptions SET title='$titlenew', description='$descriptionnew', identifier='$identifiernew', type='$typenew', rules='".addslashes(serialize($rules[$typenew]))."' WHERE optionid='$optionid'");

		updatecache('threadtypes');
		cpmsg('threadtype_infotypes_option_succeed', 'admincp.php?action=optiondetail&optionid='.$optionid);
	}

} elseif($action == 'typedetail') {

	if($operation == 'classlist') {
		$classoptions = '';
		$classidarray = array();
		!$classid && $classid = 0;
		$query = $db->query("SELECT optionid, title FROM {$tablepre}typeoptions WHERE classid='$classid' ORDER BY displayorder");
		while($option = $db->fetch_array($query)) {
			$classidarray[] = $option['optionid'];
			$classoptions .= "<input class=\"button\" type=\"button\" value=\"$option[title]\" onclick=\"ajaxget('admincp.php?action=typedetail&operation=optionlist&typeid=$typeid&classid=$option[optionid]', 'optionlist', 'optionlist', 'Loading...', '', checkedbox)\"> &nbsp;";
		}

		include template('header');
		echo $classoptions;
		include template('footer');
		exit;
	} elseif($operation == 'optionlist') {
		if(!$classid) {
			$classid = $db->result_first("SELECT optionid FROM {$tablepre}typeoptions WHERE classid='0' ORDER BY displayorder LIMIT 1");
		}
		$query = $db->query("SELECT optionid FROM {$tablepre}typevars WHERE typeid='$typeid'");
		$option = $options = array();
		while($option = $db->fetch_array($query)) {
			$options[] = $option['optionid'];
		}

		$optionlist = '';
		$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE classid='$classid' ORDER BY displayorder");
		while($option = $db->fetch_array($query)) {
			$optionlist .= "<input ".(in_array($option['optionid'], $options) ? ' checked ' : '')."class=\"checkbox\" type=\"checkbox\" name=\"typeselect[]\" value=\"$option[optionid]\" onclick=\"insertoption(this.value);\">".dhtmlspecialchars($option['title'])."&nbsp;&nbsp;";
		}
		include template('header');
		echo $optionlist;
		include template('footer');
		exit;
	} elseif($operation == 'typelist') {
		$option = $db->fetch_first("SELECT * FROM {$tablepre}typeoptions WHERE optionid='$optionid' LIMIT 1");
		include template('header');
		$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
		$option['available'] = 1;
		echo typeoptionproc($option);
		include template('footer');
		exit;
	}

	if(!submitcheck('typedetailsubmit') && !submitcheck('typepreviewsubmit')) {

		$threadtype = $db->fetch_first("SELECT name, template, modelid, expiration FROM {$tablepre}threadtypes WHERE typeid='$typeid'");
		$threadtype['modelid'] = isset($modelid) ? intval($modelid) : $threadtype['modelid'];
		$threadtype['expiration'] ? $check['true'] = "checked" : $check['false'] = "checked";

		$typemodelopt = '';
		$existoption = $showoption = array();
		$query = $db->query("SELECT id, name, options, customoptions FROM {$tablepre}typemodels ORDER BY displayorder");
		while($typemodel = $db->fetch_array($query)) {
			if($typemodel['id'] == $threadtype['modelid']) {
				foreach(explode("\t", $typemodel['customoptions']) as $id) {
					$existoption[$id] = 0;
				}

				foreach(explode("\t", $typemodel['options']) as $id) {
					$existoption[$id] = 1;
				}
			}
			$typemodelopt .= "<option value=\"$typemodel[id]\" ".($typemodel['id'] == $threadtype['modelid'] ? 'selected="selected"' : '').">$typemodel[name]</option>";
		}

		$typeoptions = $jsoptionids = '';
		$query = $db->query("SELECT t.optionid, t.displayorder, t.available, t.required, t.unchangeable, t.search, tt.title, tt.type, tt.identifier
			FROM {$tablepre}typevars t, {$tablepre}typeoptions tt
			WHERE t.typeid='$typeid' AND t.optionid=tt.optionid ORDER BY t.displayorder");
		while($option = $db->fetch_array($query)) {
			$jsoptionids .= "optionids.push($option[optionid]);\r\n";
			$optiontitle[$option['identifier']] = $option['title'];
			$showoption[$option['optionid']]['optionid'] = $option['optionid'];
			$showoption[$option['optionid']]['title'] = $option['title'];
			$showoption[$option['optionid']]['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
			$showoption[$option['optionid']]['identifier'] = $option['identifier'];
			$showoption[$option['optionid']]['displayorder'] = $option['displayorder'];
			$showoption[$option['optionid']]['available'] = $option['available'];
			$showoption[$option['optionid']]['required'] = $option['required'];
			$showoption[$option['optionid']]['unchangeable'] = $option['unchangeable'];
			$showoption[$option['optionid']]['search'] = $option['search'];
		}

		if($existoption && is_array($existoption)) {
			$optionids = $comma = '';
			foreach($existoption as $optionid => $val) {
				$optionids .= $comma.$optionid;
				$comma = '\',\'';
			}
			$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE optionid IN ('$optionids')");
			while($option = $db->fetch_array($query)) {
				$showoption[$option['optionid']]['optionid'] = $option['optionid'];
				$showoption[$option['optionid']]['title'] = $option['title'];
				$showoption[$option['optionid']]['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$showoption[$option['optionid']]['identifier'] = $option['identifier'];
				$showoption[$option['optionid']]['required'] = $existoption[$option['optionid']];
				$showoption[$option['optionid']]['available'] = 1;
				$showoption[$option['optionid']]['unchangeable'] = 0;
				$showoption[$option['optionid']]['model'] = 1;
			}
		}

		foreach($showoption as $optionid => $option) {
			$typeoptions .= '<tr><td colspan="10" style="border: 0px; padding: 0px;" id="optionid'.$optionid.'"><TABLE width="100%" cellspacing="0" cellpadding="0" style="margin:0px;">'.typeoptionproc($option).'</TABLE></td></tr>';
		}

		if($threadtype['template']) {
			$previewtemplate = preg_replace("/{(.+?)}/ies", "showoption('\\1', 'title')", $threadtype['template']);
			$previewtemplate = preg_replace("/\[(.+?)value\]/ies", "showoption('\\1', 'value')", $previewtemplate);
		}

		shownav('menu_forums_threadtypes');

?>
<form method="post" action="admincp.php?action=typedetail&typeid=<?=$typeid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['threadtype_models']?></td>
</tr>
<tr>
<td width="45%" class="altbg1"><b><?=$lang['threadtype_models_select']?></b><br /><span class="smalltxt"><?=$lang['threadtype_models_select_comment']?></span></td>
<td class="altbg2">
	<select name="modelid" onchange="window.location=('admincp.php?action=typedetail&typeid=<?=$typeid?>&amp;modelid='+this.options[this.selectedIndex].value+'')"><option value="0"><?=$lang['none']?></option><?=$typemodelopt?></select>
</td>
</tr>
<tr>
<td width="45%" class="altbg1"><b><?=$lang['threadtype_infotypes_validity']?></b><br /><span class="smalltxt"><?=$lang['threadtype_infotypes_validity_comment']?></span></td>
<td class="altbg2">
	<input type="radio" class="radio" name="typeexpiration" value="1" <?=$check['true']?>><?=$lang['yes']?> &nbsp; &nbsp; <input type="radio" class="radio" name="typeexpiration" value="0" <?=$check['false']?>><?=$lang['no']?>
</td>
</tr>
</table><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_add_option']?></td></tr>
<tr class="altbg1" align="center"><td id="classlist">
<?=$classoptions?>
</td></tr>
<tr class="altbg1"><td id="optionlist"><?=$optionlist?></td></tr>
<tr><td width="100%">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" id="typelist">
<tr class="header"><td colspan="10"><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_exist_option']?></td></tr>
<tr class="category" align="center">
<td width="10%">
<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td width="10%"><?=$lang['name']?></td>
<td width="15%"><?=$lang['type']?></td>
<td width="8%"><?=$lang['available']?></td>
<td width="8%"><?=$lang['required']?></td>
<td width="8%"><?=$lang['unchangeable']?></td>
<td width="8%"><?=$lang['threadtype_infotypes_search']?></td>
<td width="10%"><?=$lang['display_order']?></td>
<td width="10%"><?=$lang['threadtype_infotypes_add_template']?></td>
<td width="10%"><?=$lang['edit']?></td>
</tr>
<?=$typeoptions?>
</table>
</td>
</tr>
</table>

<center>
<input class="button" type="submit" name="typedetailsubmit" value="<?=$lang['submit']?>">&nbsp;&nbsp;&nbsp;
</center>

<a name="template"><br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$threadtype['name']?> - <?=$lang['threadtype_infotypes_template']?></td></tr>
<tr class="altbg1"><td>

<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('typetemplate', 1)">
<img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor='pointer'" onclick="zoomtextarea('typetemplate', 0)"><br />
<textarea cols="100" rows="5" id="typetemplate" name="typetemplate" style="width: 95%;"><?=$threadtype['template']?></textarea>
<br />
<b><?=$lang['threadtype_infotypes_template']?>:</b>
<br />
<?=$lang['threadtype_infotypes_template_tips']?>
<?php
	if($previewtemplate) {
		echo '<br /><fieldset style="padding: 1em; margin: 1em;"><legend><b>'.$lang['threadtype_infotypes_template_preview'].':</b></legend>';
		echo $previewtemplate;
		echo '</fieldset>';
	}
?>
</td></tr>
</table><br />
</a>
<center>
<input class="button" type="submit" name="typedetailsubmit" value="<?=$lang['submit']?>">&nbsp;&nbsp;&nbsp;
<input class="button" type="submit" name="typepreviewsubmit" value="<?=$lang['template_preview']?>">
</center>
</form>
<script type="text/javascript">
var optionids = new Array();
<?=$jsoptionids?>
function insertvar(text) {
	$('typetemplate').focus();
	selection = document.selection;
	if(selection && selection.createRange) {
		var sel = selection.createRange();
		sel.text = '<li><b>{' + text + '}</b>: [' + text + "value]</li>\r\n";
		sel.moveStart('character', -strlen(text));
	} else {
		$('typetemplate').value += '<li><b>{' + text + '}<b>: [' + text + "value]</li>\r\n";
	}
}
function checkedbox() {
	var tags = $('optionlist').getElementsByTagName('input');
	for(var i=0; i<tags.length; i++) {
		if(in_array(tags[i].value, optionids)) {
			tags[i].checked = true;
		}
	}
}
function insertoption(optionid) {
	var x = new Ajax();
	x.optionid = optionid;
	x.get('admincp.php?action=typedetail&operation=typelist&inajax=1&optionid=' + optionid, function(s, x) {
		if(!in_array(x.optionid, optionids)) {
			var otr = $('typelist').insertRow(-1);
			var otd = otr.insertCell(-1);
			otd.colSpan = 10;

			otd.id = 'optionid' + optionid;
			otd.style.border = '0px';
			otd.style.padding = '0px';
			otd.style.margin = '0px';
			otd.style.width="100%";
			otd.innerHTML = '<TABLE width="100%" cellspacing="0" cellpadding="0" style="margin: 0px;">' + s + '</TABLE>';
			optionids.push(x.optionid);
		} else {
			if(is_ie){
				$('optionid' + x.optionid).parentNode.removeNode(true);
			} else {
				$('optionid' + x.optionid).parentNode.removeChild($('optionid' + x.optionid));
			}
			for(var i=0; i<optionids.length; i++) {
				if(optionids[i] == x.optionid) {
					optionids[i] = 0;
				}
			}
		}
	});
}
</script>
<script type="text/javascript">ajaxget('admincp.php?action=typedetail&operation=classlist', 'classlist');</script>
<script type="text/javascript">ajaxget('admincp.php?action=typedetail&operation=optionlist&typeid=<?=$typeid?>', 'optionlist', '', '', '', checkedbox);</script>
<?

	} else {

		$db->query("UPDATE {$tablepre}threadtypes SET special='1', modelid='".intval($modelid)."', template='$typetemplate', expiration='$typeexpiration' WHERE typeid='$typeid'");

		if(submitcheck('typedetailsubmit')) {

			$orgoption = $orgoptions = $addoption = array();
			$query = $db->query("SELECT optionid FROM {$tablepre}typevars WHERE typeid='$typeid'");
			while($orgoption = $db->fetch_array($query)) {
				$orgoptions[] = $orgoption['optionid'];
			}

			if(intval($modelid)) {
				$modelopt = $db->fetch_first("SELECT options, customoptions FROM {$tablepre}typemodels WHERE id='$modelid'");
				if($modelopt['customoptions']) {
					foreach(explode("\t", $modelopt['customoptions']) as $id) {
						$addoption[$id] = $required[$id] = 0;
						$available[$id] = 1;
					}
				}

				if($modelopt['options']) {
					foreach(explode("\t", $modelopt['options']) as $id) {
						$addoption[$id] = $available[$id] = $required[$id] = 1;
					}
				}
			}

			$addoption = $addoption ? (array)$addoption + (array)$displayorder : (array)$displayorder;

			@$newoptions = array_keys($addoption);

			if(empty($addoption)) {
				cpmsg('threadtype_infotypes_invalid');
			}

			@$delete = array_merge((array)$delete, array_diff($orgoptions, $newoptions));

			if($delete) {
				if($ids = implodeids($delete)) {
					$db->query("DELETE FROM {$tablepre}typevars WHERE typeid='$typeid' AND optionid IN ($ids)");
				}
				foreach($delete as $id) {
					unset($addoption[$id]);
				}
			}

			if(is_array($addoption)) {
				foreach($addoption as $id => $val) {
					$db->query("INSERT INTO {$tablepre}typevars (typeid, optionid, available, required) VALUES ('$typeid', '$id', '1', '".intval($val)."')", 'SILENT');
					$db->query("UPDATE {$tablepre}typevars SET displayorder='$displayorder[$id]', available='$available[$id]', required='$required[$id]', unchangeable='$unchangeable[$id]', search='$search[$id]' WHERE typeid='$typeid' AND optionid='$id'");
				}
			}

			updatecache('threadtypes');
			cpmsg('threadtype_infotypes_succeed', 'admincp.php?action=typedetail&typeid='.$typeid);

		} elseif(submitcheck('typepreviewsubmit')) {
			header("Location: {$boardurl}admincp.php?action=typedetail&typeid=$typeid#template");
		}

	}

} elseif($action == 'typemodel') {

	if(!submitcheck('modelsubmit')) {
		$typemodels = '';
		$query = $db->query("SELECT * FROM {$tablepre}typemodels ORDER BY displayorder");
		while($model = $db->fetch_array($query)) {
			$typemodels .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$model[id]\" ".($model['type'] ? 'disabled' : '')."></td>\n".
			"<td class=\"altbg1\"><input type=\"text\" name=\"name[$model[id]]\" value=\"$model[name]\">\n".
			"<td class=\"altbg1\"><input type=\"text\" size=\"10\" name=\"displayorder[$model[id]]\" value=\"$model[displayorder]\"></td>\n".
			"<td class=\"altbg1\"><a href=\"admincp.php?action=modeldetail&modelid=$model[id]\">[$lang[detail]]</a></td></tr>\n";
		}

?>
<form method="post" action="admincp.php?action=typemodel">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" id="typelist">
<tr class="header"><td colspan="7"><?=$lang['threadtype_models']?></td></tr>
<tr class="category" align="center">
<td width="15%">
<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['name']?></td>
<td><?=$lang['display_order']?></td>
<td><?=$lang['edit']?></td>
</tr>
<?=$typemodels?>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type="text" name="newtitle"></td>
<td><input type="text" size="10" name="newdisplayorder"></td>
<td>&nbsp;</td></tr>
</table>

<center>
<input class="button" type="submit" name="modelsubmit" value="<?=$lang['submit']?>">&nbsp;&nbsp;&nbsp;
</center>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}typemodels WHERE id IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id => $val) {
				$db->query("UPDATE {$tablepre}typemodels SET displayorder='$displayorder[$id]', name='$name[$id]' WHERE id='$id'");
			}
		}

		if($newtitle) {
			$db->query("INSERT INTO {$tablepre}typemodels (name, displayorder, type)
				VALUES ('$newtitle', '$newdisplayorder', '0')");
		}

		cpmsg('threadtype_infotypes_model_succeed', 'admincp.php?action=typemodel');

	}

} elseif($action == 'modeldetail') {

	if(!submitcheck('modeldetailsubmit')) {

		$classoptions = $modeloption = $sysoption = $sysoptselect = '';
		$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE classid!='0' ORDER BY displayorder");
		while($option = $db->fetch_array($query)) {
			$classoptions .= "<option value=\"$option[optionid]\">$option[title]</option>";
		}

		$model = $db->fetch_first("SELECT * FROM {$tablepre}typemodels WHERE id='".intval($modelid)."'");
		if(!$model) {
			cpmsg('undefined_action');
		}

		$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE optionid IN (".implodeids(explode("\t", $model['customoptions'])).")");
		while($modelopt = $db->fetch_array($query)){
			$modeloption .=  "<option value=\"$modelopt[optionid]\">$modelopt[title]</option>";
		}

		if($model['type']) {
			$query = $db->query("SELECT * FROM {$tablepre}typeoptions WHERE optionid IN (".implodeids(explode("\t", $model['options'])).")");
			while($modelopt = $db->fetch_array($query)){
				$sysoption .=  "<option value=\"$modelopt[optionid]\">$modelopt[title]</option>";
			}

			$sysoptselect = '<select name="" size="8" multiple="multiple" style="width: 50%">'.$sysoption.'</select>';
		}

		$optselect = '<select name="" size="8" multiple="multiple" style="width: 50%" id="coptselect">'.$classoptions.'</select>';
		$hoptselect = '<select name="customoptions[]" size="8" multiple="multiple" style="width: 50%" id="moptselect">'.$modeloption.'</select>';

?>
<script type="text/javascript">
function copyoption(s1, s2) {
	var s1 = $(s1);
	var s2 = $(s2);
	var len = s1.options.length;
	for(var i=0; i<len; i++) {
		op = s1.options[i];
		if(op.selected == true && !optionexists(s2, op.value)) {
			o = op.cloneNode(true);
			s2.appendChild(o);
		}
	}
}

function optionexists(s1, value) {
	var len = s1.options.length;
		for(var i=0; i<len; i++) {
			if(s1.options[i].value == value) {
				return true;
			}
		}
	return false;
}

function removeoption(s1) {
	var s1 = $(s1);
	var len = s1.options.length;
	for(var i=s1.options.length - 1; i>-1; i--) {
		op = s1.options[i];
		if(op.selected && op.selected == true) {
			s1.removeChild(op);
		}
	}
	return false;
}

function selectalloption(s1) {
	var s1 = $(s1);
	var len = s1.options.length;
	for(var i=s1.options.length - 1; i>-1; i--) {
		op = s1.options[i];
		op.selected = true;
	}
}
</script>

<form method="post" action="admincp.php?action=modeldetail&modelid=<?=$modelid?>&formhash=<?=FORMHASH?>" onsubmit="selectalloption('moptselect');">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['threadtype_models_basic_setting']?></td>
</tr>
<tr>
<td width="45%" class="altbg1" ><b><?=$lang['name']?></b></td>
<td class="altbg2"><input type="text" size="50" name="namenew" value="<?=$model['name']?>"></td>
</tr>
</table><br />

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['threadtype_models_option_setting']?></td>
</tr>
<?if($model['type']) {?>
<tr>
<td width="45%" class="altbg1" ><b><?=$lang['threadtype_models_option_model']?></b></td>
<td class="altbg2"><?=$sysoptselect?><br />
</td></tr>
<?}?>
<tr>
<td width="45%" class="altbg1" ><b><?=$lang['threadtype_models_option_user']?></b></td>
<td class="altbg2"><?=$hoptselect?><br /><a href="###" onclick="removeoption('moptselect')">[<?=$lang['del']?>]</a>
</td></tr>
<tr>
<td width="45%" class="altbg1" ><b><?=$lang['threadtype_models_option_system']?></b></td>
<td class="altbg2"><?=$optselect?><br /><a href="###" onclick="copyoption('coptselect', 'moptselect')">[<?=$lang['threadtype_models_option_copy']?>]</a>
</td></tr>
</table><br />
<center>
<input class="button" type="submit" name="modeldetailsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {
		$customoptionsnew = $customoptions && is_array($customoptions) ? implode("\t", $customoptions) : '';
		$db->query("UPDATE {$tablepre}typemodels SET name='$namenew', customoptions='$customoptionsnew' WHERE id='$modelid'");

		cpmsg('threadtype_infotypes_model_succeed', 'admincp.php?action=modeldetail&modelid='.$modelid);
	}

}

function showoptiontype($type, $curtype) {
	echo 	'</table><br /></div><div id="style_'.$type.'" style="'.($type != $curtype ? 'display: none' : '').'" class="maintablediv">'.
		'<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
		'<tr class="header"><td colspan="2">'.$GLOBALS['lang']['threadtype_edit_vars_type_'.$type].'</td></tr>';
}

function showoption($var, $type) {
	global $optiontitle, $lang;
	if($optiontitle[$var]) {
		$optiontitle[$var] = $type == 'title' ? $optiontitle[$var] : $optiontitle[$var].$lang['value'];
		return $optiontitle[$var];
	} else {
		return "!$var!";
	}
}

function typeoptionproc($option) {
	global $lang;
	return "<tr align=\"center\">
		<td class=\"altbg1\" width=\"10%\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\" ".($option['model'] ? 'disabled' : '')."></td>\n".
		"<td class=\"altbg2\" width=\"10%\">".dhtmlspecialchars($option['title'])." </td>\n".
		"<td class=\"altbg1\" width=\"15%\">$option[type]</td>\n".
		"<td class=\"altbg2\" width=\"8%\"><input class=\"checkbox\" type=\"checkbox\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '')."></td>\n".
		"<td class=\"altbg1\" width=\"8%\"><input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '')."></td>\n".
		"<td class=\"altbg2\" width=\"8%\"><input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '')."></td>\n".
		"<td class=\"altbg1\" width=\"8%\"><input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]]\" value=\"1\" ".($option['search'] ? 'checked' : '')."></td>\n".
		"<td class=\"altbg2\" width=\"10%\"><input type=\"text\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\"></td>\n".
		"<td class=\"altbg1\" width=\"10%\"><a href=\"###\" onclick=\"insertvar('$option[identifier]');doane(event);return false;\">[".$lang['threadtype_infotypes_add_template']."]</a></td>\n".
		"<td class=\"altbg2\" width=\"10%\"><a href=\"admincp.php?action=optiondetail&optionid=$option[optionid]\">[".$lang['edit']."]</a></td></tr>\n";
}
?>