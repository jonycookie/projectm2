<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tools.inc.php 16904 2008-11-27 05:46:00Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if($action == 'updatecache') {

	updatecache();

	if($tplrefresh) {
		$tpl = dir(DISCUZ_ROOT.'./forumdata/templates');
		while($entry = $tpl->read()) {
			if(preg_match("/\.tpl\.php$/", $entry)) {
				@unlink(DISCUZ_ROOT.'./forumdata/templates/'.$entry);
			}
		}
		$tpl->close();
	}

	if($jsstatus) {
		$js = dir(DISCUZ_ROOT.'./forumdata/cache');
		while($entry = $js->read()) {
			if(preg_match("/^javascript_/", $entry)) {
				@unlink(DISCUZ_ROOT.'./forumdata/cache/'.$entry);
			}
		}
		$js->close();
	}

	cpmsg('update_cache_succeed');

} elseif($action == 'jswizard') {

	if(!$jsstatus) {
		header('location: admincp.php?action=settings&do=javascript');
	}

	shownav('menu_tools_javascript');

	showtype('jswizard', 'top');
	echo '<tr><td class="altbg2"><a href="admincp.php?action=settings&do=javascript">'.$lang['jswizard_basesettings'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;';
	if($jsstatus) {
		echo '<a href="admincp.php?action=jswizard">'.$lang['jswizard_project'].'</a>';
	}
	echo '</td></tr>';
	showtype('', 'bottom');
	echo '<br />';

	$jswizard = array();
	$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable LIKE 'jswizard_".($jssetting != '' ? $jssetting : ($jskey != '' ? $jskey : '%'))."'");
	while($settings = $db->fetch_array($query)) {
		$jswizard[substr($settings['variable'], 9)] = unserialize($settings['value']);
	}

	$type = !empty($function) ? $function : (isset($type) ? $type : '');
	$edit = isset($edit) ? $edit : NULL;
	$editext = ($jssetting || $edit) ? '<input type="hidden" name="edit" value="'.dhtmlspecialchars($jssetting ? $jssetting : $edit).'">' : '';
	ksort($jswizard);

	if(!empty($type)) {
		if(empty($jskey)) {
			$jskey = $type.'_'.random(3);
		}
		$jspreview = '';
		if(!empty($function) && !empty($jssetting) && isset($jswizard[$jssetting]['url'])) {
			$parameter = $jswizard[$jssetting]['parameter'];
			$jskey = $jssetting;
			$jssetting = $jswizard[$jssetting]['url'];
			$preview = $jssubmit = TRUE;
		} else {
			$jssetting = '';
		}
	}
	$jskey = trim($jskey);

	if(empty($type)) {

		if(!submitcheck('jsdelsubmit') && !submitcheck('jsexportsubmit') && !submitcheck('importsubmit')) {

			$jstypes = array('threads', 'forums', 'memberrank', 'stats', 'images', -1 => 'custom');

			showtype('jswizard_addjs', 'top');

			echo '<tr><td class="altbg2">';
			foreach($jstypes as $jstype) {
				echo '<input type="button" class="button" value="'.$lang['jswizard_'.$jstype].'" onclick="location.href=\'admincp.php?action=jswizard&type='.$jstype.'\'">&nbsp;&nbsp;';
			}
			echo '</td></tr>';
			showtype('', 'bottom');
			echo '<br />';

?>

<form method="post" action="admincp.php?action=jswizard">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="3%"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,'keyarray')"></td>
<td><?=$lang['jswizard_key']?></td><td><?=$lang['type']?></td></tr>
<?

			foreach($jswizard as $key => $jssetting) {
				echo '<tr><td class="altbg1"><input class="checkbox" type="checkbox" name="keyarray[]" value="'.dhtmlspecialchars($key).'">';
				echo '<td class="altbg2"><a href="admincp.php?action=jswizard&function='.$jstypes[$jssetting['type']].'&jssetting='.rawurlencode($key).'">'.$key.'</a></td><td class="altbg1">'.$lang['jswizard_'.$jstypes[$jssetting['type']]].'</td>';
			}
			echo '</td></tr>';
			showtype('', 'bottom');

?>
<br /><center><input class="button" type="submit" name="jsdelsubmit" value="<?=$lang['jswizard_delete']?>">&nbsp; &nbsp;<input class="button" type="submit" name="jsexportsubmit" value="<?=$lang['jswizard_download']?>"></center>
</form>

<br />
<form method="post" action="admincp.php?action=jswizard">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['jswizard_import_stick']?></td></tr>
<tr><td class="altbg1">	<div align="center"><textarea name="importdata" cols="80" rows="8"></textarea><br />
<input class="checkbox" type="radio" name="importrewrite" value="0" checked><?=$lang['jswizard_import_default']?>&nbsp;
<input class="checkbox" type="radio" name="importrewrite" value="1"><?=$lang['jswizard_import_norewrite']?>&nbsp;
<input class="checkbox" type="radio" name="importrewrite" value="2"><?=$lang['jswizard_import_rewrite']?>
</td></tr>
</table><br />
<center><input class="button" type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		} elseif(submitcheck('jsdelsubmit')) {
			if(is_array($keyarray)) {
				$keys = implode("','jswizard_", $keyarray);
				$db->query("DELETE FROM {$tablepre}settings WHERE variable IN ('jswizard_$keys')");
				updatecache('jswizard');
				cpmsg('jswizard_succeed', 'admincp.php?action=jswizard');
			} else {
				header("location: {$boardurl}admincp.php?action=jswizard");
				dexit();
			}
		} elseif(submitcheck('jsexportsubmit')) {
			if(is_array($keyarray)) {
				$keys = implode("','jswizard_", $keyarray);
				$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable IN ('jswizard_$keys')");
				$exportdataarray = array();
				while($exportdata = $db->fetch_array($query)) {
					$value = unserialize($exportdata['value']);
					switch($value['type']) {
						case 0:
							unset($value['parameter']['threads_forums']);
							unset($value['parameter']['tids']);
							unset($value['parameter']['typeids']);
						break;
						case 1:
							unset($value['parameter']['forums_forums']);
						break;
						case 4:
							unset($value['parameter']['images_forums']);
						break;
					}
					$exportdataarray[preg_replace("/^jswizard_/", '', $exportdata['variable'])] = serialize($value);
				}

				$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

				$jswizard_export = "# Discuz! JSWizard Dump\n".
					"# Version: Discuz! $version\n".
					"# Time: $time  \n".
					"# From: $bbname ($boardurl) \n".
					"#\n".
					"# Discuz! Community: http://www.Discuz.net\n".
					"# Please visit our website for latest news about Discuz!\n".
					"# --------------------------------------------------------\n\n\n".
					wordwrap(base64_encode(serialize($exportdataarray)), 60, "\n", 1);

				ob_end_clean();
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				header('Cache-Control: no-cache, must-revalidate');
				header('Pragma: no-cache');
				header('Content-Encoding: none');
				header('Content-Length: '.strlen($jswizard_export));
				header('Content-Disposition: attachment; filename=discuz_jswizard_'.date('Ymd').'.txt');
				header('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));

				echo $jswizard_export;
				dexit();
			} else {
				header("location: {$boardurl}admincp.php?action=jswizard");
				dexit();
			}
		} elseif(submitcheck('importsubmit')) {
			$importdata = preg_replace("/(#.*\s+)*/", '', $importdata);
			$importarray = unserialize(base64_decode($importdata));
			$keys = implode("','jswizard_", array_keys($importarray));

			if($importrewrite != 2) {
				$query = $db->query("SELECT variable FROM {$tablepre}settings WHERE variable IN ('jswizard_$keys')");
				$existkeyarray = array();
				while($existkey = $db->fetch_array($query)) {
					if($importrewrite == 1) {
						unset($importarray[preg_replace("/^jswizard_/", '', $existkey['variable'])]);
					} else {
						$existkeyarray[] = $existkey['variable'];
					}
				}

				if($importrewrite == 0 && $existkeyarray) {
					$existkeys = implode(", ", $existkeyarray);
					cpmsg('jswizard_import_exist');
				}
			}

			foreach($importarray as $key => $value) {
				$value = addslashes($value);
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('jswizard_$key', '$value')");
			}

			updatecache('jswizard');
			cpmsg('jswizard_succeed', 'admincp.php?action=jswizard');
		}

	} elseif($type == 'threads') {

		/* Threads == Start == */
		$tcheckorderby = array((isset($parameter['orderby']) ? $parameter['orderby'] : 'lastpost') => 'checked');
		for($i = 0; $i <= 6; $i++) {
			$tcheckspecial[$i] = !empty($parameter['special'][$i]) ? 'checked' : '';
			$tcheckdigest[$i] = !empty($parameter['digest'][$i]) ? 'checked' : '';
			$tcheckstick[$i] = !empty($parameter['stick'][$i]) ? 'checked' : '';
		}
		$parameter['newwindow'] = isset($parameter['newwindow']) ? intval($parameter['newwindow']) : 1;
		$tradionewwindow[$parameter['newwindow']] = 'checked';

		$jsthreadtypeselect = '<select multiple="multiple" name="parameter[typeids][]" size="5"><option value="all">'.$lang['jswizard_all_typeids'].'</optoin><option value="">&nbsp;</option>';
		$query = $db->query("SELECT typeid, name FROM {$tablepre}threadtypes ORDER BY typeid DESC");
		while($threadtype = $db->fetch_array($query)) {
			$jsthreadtypeselect .= '<option value="'.$threadtype['typeid'].'" '.(isset($parameter['typeids']) && in_array($threadtype['typeid'], $parameter['typeids']) ? 'selected' : '').'>'.$threadtype['name'].'</option>';
		}
		$jsthreadtypeselect .= '</select>';
		$trewardstatus = array(intval($parameter['rewardstatus']) => 'checked');

		if($jssubmit && $function == 'threads') {

			$jsurl = $jssetting ? $jssetting : "function=$function".
				($parameter['threads_forums'] && !in_array('all', $parameter['threads_forums'])? '&fids='.jsfids($parameter['threads_forums']) : '').
				"&maxlength=$parameter[maxlength]".
				"&fnamelength=$parameter[fnamelength]".
				"&blog=$parameter[blog]".
				"&startrow=$parameter[startrow]".
				"&picpre=".rawurlencode($parameter['picpre']).
				"&items=$parameter[items]".
				"&tag=".rawurlencode($parameter[tag]).
				'&tids='.str_replace(',', '_', $parameter['tids']).
				($parameter['keyword'] ? '&keyword='.rawurlencode($parameter['keyword']) : '').
				($parameter['typeids'] && !in_array('all', $parameter['typeids'])? '&typeids='.jsfids($parameter['typeids']) : '').
				"&special=".bindec(intval($parameter['special'][1]).intval($parameter['special'][2]).intval($parameter['special'][3]).intval($parameter['special'][4]).intval($parameter['special'][5]).intval($parameter['special'][6]).intval($parameter['special'][0])).
				"&rewardstatus=$parameter[rewardstatus]".
				"&digest=".bindec(intval($parameter['digest'][1]).intval($parameter['digest'][2]).intval($parameter['digest'][3]).intval($parameter['digest'][4])).
				"&stick=".bindec(intval($parameter['stick'][1]).intval($parameter['stick'][2]).intval($parameter['stick'][3]).intval($parameter['stick'][4])).
				"&newwindow=$parameter[newwindow]".
				"&threadtype=$parameter[threadtype]".
				"&highlight=$parameter[highlight]".
				"&orderby=$parameter[orderby]".
				"&jscharset=$parameter[jscharset]".
				($parameter['cachelife'] != '' ? "&cachelife=$parameter[cachelife]" : '').
				(!empty($parameter['jstemplate']) ? '&jstemplate='.rawurlencode($parameter['jstemplate']) : '');

			$jsurlview = "$jsurl&nocache=yes";
			$jsurlview = "{$boardurl}api/javascript.php?$jsurlview&verify=".md5($authkey.$jsurlview);
			if(!$preview) {
				jssavesetting(0);
			}
			$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
			$jspreview = "<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
				dhtmlspecialchars("<script language=\"JavaScript\" src=\"{$boardurl}api/javascript.php?key=".rawurlencode($jskey)."\"></script>").
				"</textarea><br /><div class=\"jswizard\"><script language=\"JavaScript\" src=\"$jsurlview\"></script></div><br />";
		}

		if($jspreview) {
			showtype('preview', 'top');
			echo '<tr><td class="altbg1">'.$jspreview;
			showtype('', 'bottom');
			echo '<br />';
		}

		echo '<form method="post" action="admincp.php?action=jswizard&function=threads#'.$lang['jswizard_threads'].'">';

		showtype('jswizard_jstemplate', 'top');
		echo '<tr><td class="altbg1" colspan="2">'.$lang['jswizard_threads_jstemplate_comment'].'<br />';
		echo '<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 0)"><br />';
		jsinsertunit();
		echo '<textarea cols="100" rows="5" id="jstemplate" name="parameter[jstemplate]" style="width: 95%;">'.($parameter['jstemplate'] != '' ? stripslashes($parameter['jstemplate']) : '{prefix} {subject}<br />').'</textarea></td></tr>';
		showtype('', 'bottom');
		echo '<br />';

		showtype('jswizard_threads', 'top');
		showsetting('jswizard_jskey', 'jskey', $jskey, 'text');
		showsetting('jswizard_cachelife', 'parameter[cachelife]', $parameter['cachelife'] != '' ? intval($parameter['cachelife']) : '', 'text');
		showsetting('jswizard_threads_fids', '', '', jsforumselect('threads'));
		showsetting('jswizard_threads_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
		showsetting('jswizard_threads_items', 'parameter[items]', isset($parameter['items']) ? $parameter['items'] : 10, 'text');
		showsetting('jswizard_threads_maxlength', 'parameter[maxlength]', isset($parameter['maxlength']) ? $parameter['maxlength'] : 50, 'text');
		showsetting('jswizard_threads_fnamelength', 'parameter[fnamelength]', $parameter['fnamelength'], 'radio');
		showsetting('jswizard_threads_picpre', 'parameter[picpre]', $parameter['picpre'], 'text');
		showsetting('jswizard_threads_tids', 'parameter[tids]', ($parameter['tids'] ? str_replace('_', ',', $parameter['tids']) : ''), 'text');
		showsetting('jswizard_threads_keyword', 'parameter[keyword]', $parameter['keyword'], 'text');
		showsetting('jswizard_threads_tag', 'parameter[tag]', $parameter['tag'], 'text');
		showsetting('jswizard_threads_typeids', '', '', $jsthreadtypeselect);
		showsetting('jswizard_threads_threadtype', 'parameter[threadtype]', $parameter['threadtype'], 'radio');
		showsetting('jswizard_threads_highlight', 'parameter[highlight]', $parameter['highlight'], 'radio');
		showsetting('jswizard_threads_blog', 'parameter[blog]', $parameter['blog'], 'radio');
		showsetting('jswizard_threads_special', '', '', '<input class="checkbox" type="checkbox" name="parameter[special][1]" value="1" '.$tcheckspecial[1].'> '.$lang['jswizard_special_1'].'<br /><input class="checkbox" type="checkbox" name="parameter[special][2]" value="1" '.$tcheckspecial[2].'> '.$lang['jswizard_special_2'].'<br /><input class="checkbox" type="checkbox" onclick="$(\'special_reward_ext\').style.display = this.checked ? \'\' : \'none\'" name="parameter[special][3]" value="1" '.$tcheckspecial[3].'> '.$lang['jswizard_special_3'].'<br /><input class="checkbox" type="checkbox" name="parameter[special][4]" value="1" '.$tcheckspecial[4].'> '.$lang['jswizard_special_4'].'<br /><input class="checkbox" type="checkbox" name="parameter[special][5]" value="1" '.$tcheckspecial[5].'> '.$lang['jswizard_special_5'].'<br /><input class="checkbox" type="checkbox" name="parameter[special][6]" value="1" '.$tcheckspecial[6].'> '.$lang['jswizard_special_6'].'<br /><input class="checkbox" type="checkbox" name="parameter[special][0]" value="1" '.$tcheckspecial[0].'> '.$lang['jswizard_special_0']);
		echo '</tbody><tbody class="sub" id="special_reward_ext" style="display: '.($tcheckspecial[3] ? '' : 'none').'">';
		showsetting('jswizard_threads_special_reward', '', '', '<input class="radio" type="radio" name="parameter[rewardstatus]" value="0" '.$trewardstatus[0].'> '.$lang['jswizard_threads_special_reward_0'].'<br /><input class="radio" type="radio" name="parameter[rewardstatus]" value="1" '.$trewardstatus[1].'> '.$lang['jswizard_threads_special_reward_1'].'<br /><input class="radio" type="radio" name="parameter[rewardstatus]" value="2" '.$trewardstatus[2].'> '.$lang['jswizard_threads_special_reward_2']);
		echo '</tbody><tbody>';
		showsetting('jswizard_threads_digest', '', '', '<input class="checkbox" type="checkbox" name="parameter[digest][1]" value="1" '.$tcheckdigest[1].'> '.$lang['jswizard_digest_1'].'<br /><input class="checkbox" type="checkbox" name="parameter[digest][2]" value="1" '.$tcheckdigest[2].'> '.$lang['jswizard_digest_2'].'<br /><input class="checkbox" type="checkbox" name="parameter[digest][3]" value="1" '.$tcheckdigest[3].'> '.$lang['jswizard_digest_3'].'<br /><input class="checkbox" type="checkbox" name="parameter[digest][4]" value="1" '.$tcheckdigest[4].'> '.$lang['jswizard_digest_0'].'');
		showsetting('jswizard_threads_stick', '', '', '<input class="checkbox" type="checkbox" name="parameter[stick][1]" value="1" '.$tcheckstick[1].'> '.$lang['jswizard_stick_1'].'<br /><input class="checkbox" type="checkbox" name="parameter[stick][2]" value="1" '.$tcheckstick[2].'> '.$lang['jswizard_stick_2'].'<br /><input class="checkbox" type="checkbox" name="parameter[stick][3]" value="1" '.$tcheckstick[3].'> '.$lang['jswizard_stick_3'].'<br /><input class="checkbox" type="checkbox" name="parameter[stick][4]" value="1" '.$tcheckstick[4].'> '.$lang['jswizard_stick_0'].'');
		showsetting('jswizard_threads_newwindow', 'parameter[newwindow]', '', '<input class="radio" type="radio" name="parameter[newwindow]" value="0" '.$tradionewwindow[0].'> '.$lang['jswizard_newwindow_self'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="1" '.$tradionewwindow[1].'> '.$lang['jswizard_newwindow_blank'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="2" '.$tradionewwindow[2].'> '.$lang['jswizard_newwindow_main']);
		showsetting('jswizard_threads_orderby', '', '', '<input class="radio" type="radio" name="parameter[orderby]" value="lastpost" '.$tcheckorderby['lastpost'].'> '.$lang['jswizard_threads_orderby_lastpost'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="dateline" '.$tcheckorderby['dateline'].'> '.$lang['jswizard_threads_orderby_dateline'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="replies" '.$tcheckorderby['replies'].'> '.$lang['jswizard_threads_orderby_replies'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="views" '.$tcheckorderby['views'].'> '.$lang['jswizard_threads_orderby_views']);
		if(strtoupper($charset) != 'UTF-8') {
			showsetting('jswizard_charset', 'parameter[jscharset]', $parameter['jscharset'], 'radio');
		} else {
			showsetting('jswizard_charsetr', array('parameter[jscharset]', array(array(0, $lang['none']), array(1, 'GBK'), array(2, 'BIG5'))), intval($parameter['jscharset']), 'mradio');
		}
		showtype('', 'bottom');
		echo $editext.'<br /><center><input class="button" type="submit" name="jssubmit" value="'.$lang['jswizard_preview'].'">&nbsp; &nbsp;<input class="button" type="button" onclick="this.form.preview.value=0;this.form.jssubmit.click()" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="1"></center></form><br />';
		/* Threads == End == */

	} elseif($type == 'forums') {

		/* Forums == Start == */
		$fcheckorderby = array((isset($parameter['orderby']) ? $parameter['orderby'] : 'displayorder') => 'checked');
		$parameter['newwindow'] = isset($parameter['newwindow']) ? intval($parameter['newwindow']) : 1;
		$tradionewwindow[$parameter['newwindow']] = 'checked';

		if($jssubmit && $function == 'forums') {

			$jsurl = $jssetting ? $jssetting : "function=$function".
				($parameter['forums_forums'] && !in_array('all', $parameter['forums_forums'])? '&fups='.jsfids($parameter['forums_forums']) : '').
				"&startrow=$parameter[startrow]".
				"&items=$parameter[items]".
				"&newwindow=$parameter[newwindow]".
				"&orderby=$parameter[orderby]".
				"&jscharset=$parameter[jscharset]".
				($parameter['cachelife'] != '' ? "&cachelife=$parameter[cachelife]" : '').
				(!empty($parameter['jstemplate']) ? '&jstemplate='.rawurlencode($parameter['jstemplate']) : '');

			$jsurlview = "$jsurl&nocache=yes";
			$jsurlview = "{$boardurl}api/javascript.php?$jsurlview&verify=".md5($authkey.$jsurlview);
			if(!$preview) {
				jssavesetting(1);
			}
			$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
			$jspreview = "<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
				dhtmlspecialchars("<script language=\"JavaScript\" src=\"{$boardurl}api/javascript.php?key=".rawurlencode($jskey)."\"></script>").
				"</textarea><br /><div class=\"jswizard\"><script language=\"JavaScript\" src=\"$jsurlview\"></script></div><br />";

		}

		if($jspreview) {
			showtype('preview', 'top');
			echo '<tr><td class="altbg1">'.$jspreview;
			showtype('', 'bottom');
			echo '<br />';
		}

		echo '<form method="post" action="admincp.php?action=jswizard&function=forums#'.$lang['jswizard_forums'].'">';

		showtype('jswizard_jstemplate', 'top');
		echo '<tr><td class="altbg1" colspan="2">'.$lang['jswizard_forums_jstemplate_comment'].'<br />';
		echo '<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 0)"><br />';
		jsinsertunit();
		echo '<textarea cols="100" rows="5" id="jstemplate" name="parameter[jstemplate]" style="width: 95%;">'.($parameter['jstemplate'] != '' ? stripslashes($parameter['jstemplate']) : '{forumname}<br />').'</textarea></td></tr>';
		showtype('', 'bottom');
		echo '<br />';

		showtype('jswizard_forums', 'top');
		showsetting('jswizard_jskey', 'jskey', $jskey, 'text');
		showsetting('jswizard_cachelife', 'parameter[cachelife]', $parameter['cachelife'] != '' ? intval($parameter['cachelife']) : '', 'text');
		showsetting('jswizard_forums_fups', '', '', jsforumselect('forums'));
		showsetting('jswizard_forums_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
		showsetting('jswizard_forums_items', 'parameter[items]', intval($parameter['items']), 'text');
		showsetting('jswizard_forums_newwindow', 'parameter[newwindow]', '', '<input class="radio" type="radio" name="parameter[newwindow]" value="0" '.$tradionewwindow[0].'> '.$lang['jswizard_newwindow_self'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="1" '.$tradionewwindow[1].'> '.$lang['jswizard_newwindow_blank'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="2" '.$tradionewwindow[2].'> '.$lang['jswizard_newwindow_main']);
		showsetting('jswizard_forums_orderby', '', '', '<input class="radio" type="radio" name="parameter[orderby]" value="displayorder" '.$fcheckorderby['displayorder'].'> '.$lang['jswizard_forums_orderby_displayorder'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="threads" '.$fcheckorderby['threads'].'> '.$lang['jswizard_forums_orderby_threads'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="posts" '.$fcheckorderby['posts'].'> '.$lang['jswizard_forums_orderby_posts']);
		if(strtoupper($charset) != 'UTF-8') {
			showsetting('jswizard_charset', 'parameter[jscharset]', $parameter['jscharset'], 'radio');
		} else {
			showsetting('jswizard_charsetr', array('parameter[jscharset]', array(array(0, $lang['none']), array(1, 'GBK'), array(2, 'BIG5'))), intval($parameter['jscharset']), 'mradio');
		}
		showtype('', 'bottom');
		echo $editext.'<br /><center><input class="button" type="submit" name="jssubmit" value="'.$lang['jswizard_preview'].'">&nbsp; &nbsp;<input class="button" type="button" onclick="this.form.preview.value=0;this.form.jssubmit.click()" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="1"></center></form><br />';
		/* Forums == End == */

	} elseif($type == 'memberrank') {

		/* Member Rank == Start == */
		$mcheckorderby = array((isset($parameter['orderby']) ? $parameter['orderby'] : 'credits') => 'checked');
		$parameter['newwindow'] = isset($parameter['newwindow']) ? intval($parameter['newwindow']) : 1;
		$tradionewwindow[$parameter['newwindow']] = 'checked';

		if($jssubmit && $function == 'memberrank') {
			$jsurl = $jssetting ? $jssetting : "function=$function".
				"&startrow=$parameter[startrow]".
				"&items=$parameter[items]".
				"&newwindow=$parameter[newwindow]".
				"&orderby=$parameter[orderby]".
				"&jscharset=$parameter[jscharset]".
				($parameter['cachelife'] != '' ? "&cachelife=$parameter[cachelife]" : '').
				(!empty($parameter['jstemplate']) ? '&jstemplate='.rawurlencode($parameter['jstemplate']) : '');

			$jsurlview = "$jsurl&nocache=yes";
			$jsurlview = "{$boardurl}api/javascript.php?$jsurlview&verify=".md5($authkey.$jsurlview);
			if(!$preview) {
				jssavesetting(2);
			}
			$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
			$jspreview = "<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
				dhtmlspecialchars("<script language=\"JavaScript\" src=\"{$boardurl}api/javascript.php?key=".rawurlencode($jskey)."\"></script>").
				"</textarea><br /><div class=\"jswizard\"><script language=\"JavaScript\" src=\"$jsurlview\"></script></div><br />";
		}

		if($jspreview) {
			showtype('preview', 'top');
			echo '<tr><td class="altbg1">'.$jspreview;
			showtype('', 'bottom');
			echo '<br />';
		}

		echo '<form method="post" action="admincp.php?action=jswizard&function=memberrank#'.$lang['jswizard_memberrank'].'">';

		showtype('jswizard_jstemplate', 'top');
		echo '<tr><td class="altbg1" colspan="2">'.$lang['jswizard_memberrank_jstemplate_comment'].'<br />';
		echo '<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 0)"><br />';
		jsinsertunit();
		echo '<textarea cols="100" rows="5" id="jstemplate" name="parameter[jstemplate]" style="width: 95%;">'.($parameter['jstemplate'] != '' ? stripslashes($parameter['jstemplate']) : '{regdate} {member} {value}<br />').'</textarea></td></tr>';
		showtype('', 'bottom');
		echo '<br />';

		showtype('jswizard_memberrank', 'top');
		showsetting('jswizard_jskey', 'jskey', $jskey, 'text');
		showsetting('jswizard_cachelife', 'parameter[cachelife]', $parameter['cachelife'] != '' ? intval($parameter['cachelife']) : '', 'text');
		showsetting('jswizard_memberrank_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
		showsetting('jswizard_memberrank_items', 'parameter[items]', isset($parameter['items']) ? $parameter['items'] : 10, 'text');
		showsetting('jswizard_memberrank_newwindow', 'parameter[newwindow]', '', '<input class="radio" type="radio" name="parameter[newwindow]" value="0" '.$tradionewwindow[0].'> '.$lang['jswizard_newwindow_self'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="1" '.$tradionewwindow[1].'> '.$lang['jswizard_newwindow_blank'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="2" '.$tradionewwindow[2].'> '.$lang['jswizard_newwindow_main']);
		showsetting('jswizard_memberrank_orderby', '', '', '<input class="radio" type="radio" name="parameter[orderby]" value="credits" '.$mcheckorderby['credits'].'> '.$lang['jswizard_memberrank_orderby_credits'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="posts" '.$mcheckorderby['posts'].'> '.$lang['jswizard_memberrank_orderby_posts'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="digestposts" '.$mcheckorderby['digestposts'].'> '.$lang['jswizard_memberrank_orderby_digestposts'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="regdate" '.$mcheckorderby['regdate'].'> '.$lang['jswizard_memberrank_orderby_regdate'].'<br /><input class="radio" type="radio" name="parameter[orderby]" value="todayposts" '.$mcheckorderby['todayposts'].'> '.$lang['jswizard_memberrank_orderby_todayposts']);
		if(strtoupper($charset) != 'UTF-8') {
			showsetting('jswizard_charset', 'parameter[jscharset]', $parameter['jscharset'], 'radio');
		} else {
			showsetting('jswizard_charsetr', array('parameter[jscharset]', array(array(0, $lang['none']), array(1, 'GBK'), array(2, 'BIG5'))), intval($parameter['jscharset']), 'mradio');
		}
		showtype('', 'bottom');
		echo $editext.'<br /><center><input class="button" type="submit" name="jssubmit" value="'.$lang['jswizard_preview'].'">&nbsp; &nbsp;<input class="button" type="button" onclick="this.form.preview.value=0;this.form.jssubmit.click()" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="1"></center></form><br />';
		/* Member Rank == End == */

	} elseif($type == 'stats') {

		/* Stats == Start == */
		$predefined = array('forums', 'threads', 'posts', 'members', 'online', 'onlinemembers');

		if($jssubmit && $function == 'stats') {
			if($jssetting) {
				$jsurl = $jssetting;
			} else {
				$jsurl = "function=$function".
					"&jscharset=$parameter[jscharset]";
				asort($displayorder);
				foreach($displayorder as $key => $order) {
					if($parameter[$key]['display']) {
						$jsurl .= "&info[$key]=".rawurlencode($parameter[$key]['title']);
					}
				}
			}
			$jsurl .= ($parameter['cachelife'] != '' ? "&cachelife=$parameter[cachelife]" : '').
				(!empty($parameter['jstemplate']) ? '&jstemplate='.rawurlencode($parameter['jstemplate']) : '');

			$jsurlview = "$jsurl&nocache=yes";
			$jsurlview = "{$boardurl}api/javascript.php?$jsurlview&verify=".md5($authkey.$jsurlview);
			if(!$preview) {
				jssavesetting(3);
			}
			$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
			$jspreview = "<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
				dhtmlspecialchars("<script language=\"JavaScript\" src=\"{$boardurl}api/javascript.php?key=".rawurlencode($jskey)."\"></script>").
				"</textarea><br /><div class=\"jswizard\"><script language=\"JavaScript\" src=\"$jsurlview\"></script></div><br />";
		}

		if($jspreview) {
			showtype('preview', 'top');
			echo '<tr><td class="altbg1">'.$jspreview;
			showtype('', 'bottom');
			echo '<br />';
		}

		echo '<form method="post" action="admincp.php?action=jswizard&function=stats#'.$lang['jswizard_stats'].'"><a name="'.$lang['jswizard_stats'].'"></a>';

		showtype('jswizard_jstemplate', 'top');
		echo '<tr><td class="altbg1" colspan="2">'.$lang['jswizard_stats_jstemplate_comment'].'<br />';
		echo '<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 0)"><br />';
		jsinsertunit();
		echo '<textarea cols="100" rows="5" id="jstemplate" name="parameter[jstemplate]" style="width: 95%;">'.($parameter['jstemplate'] != '' ? stripslashes($parameter['jstemplate']) : '{name} {value}<br />').'</textarea></td></tr>';
		showtype('', 'bottom');
		echo '<br />';

		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder"><tr class="header"><td colspan="4">'.$lang['jswizard_stats'].'</td></tr>';
		showsetting('jswizard_jskey', 'jskey', $jskey, 'text');
		showsetting('jswizard_cachelife', 'parameter[cachelife]', $parameter['cachelife'] != '' ? intval($parameter['cachelife']) : '', 'text');
		if(strtoupper($charset) != 'UTF-8') {
			showsetting('jswizard_charset', 'parameter[jscharset]', $parameter['jscharset'], 'radio');
		} else {
			showsetting('jswizard_charsetr', array('parameter[jscharset]', array(array(0, $lang['none']), array(1, 'GBK'), array(2, 'BIG5'))), intval($parameter['jscharset']), 'mradio');
		}
		echo '</table><br />';
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">';
		echo '<tr class="category" align="center"><td>'.$lang['jswizard_stats_display'].'</td><td>'.$lang['jswizard_stats_display_title'].'</td><td>'.$lang['jswizard_stats_display_name'].'</td><td>'.$lang['display_order'].'</td></tr>';
		$order = 0;
		foreach($predefined as $key) {
			echo '<tr align="center"><td class="altbg1"><input class="checkbox" type="checkbox" name="parameter['.$key.'][display]" value="1" '.(!isset($parameter[$key]) || $parameter[$key]['display'] ? 'checked' : '').'></td>'.
				'<td class="altbg1">'.$lang['jswizard_stats_'.$key].'</td>'.
				'<td class="altbg2"><input type="text" name="parameter['.$key.'][title]" size="15" value="'.($parameter[$key]['title'] ? $parameter[$key]['title'] : $lang['jswizard_stats_'.$key].':').'"></td>'.
				'<td class="altbg2"><input type="text" name="displayorder['.$key.']" size="3" value="'.(isset($displayorder[$key]) ? intval($displayorder[$key]) : ++$order).'"></td></tr>';
		}
		echo '</table>'.$editext.'<br /><center><input class="button" type="submit" name="jssubmit" value="'.$lang['jswizard_preview'].'">&nbsp; &nbsp;<input class="button" type="button" onclick="this.form.preview.value=0;this.form.jssubmit.click()" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="1"></center></form><br />';
		/* Stats == End == */

	} elseif($type == 'images') {

		/* Images == Start == */
		for($i = 1; $i <= 4; $i++) {
			$icheckdigest[$i] = !empty($parameter['digest'][$i]) ? 'checked' : '';
		}
		$parameter['newwindow'] = isset($parameter['newwindow']) ? intval($parameter['newwindow']) : 1;
		$tradionewwindow[$parameter['newwindow']] = 'checked';

		if($jssubmit && $function == 'images') {
			$jsurl = $jssetting ? $jssetting : "function=$function".
				($parameter['images_forums'] && !in_array('all', $parameter['images_forums'])? '&fids='.jsfids($parameter['images_forums']) : '').
				"&maxwidth=$parameter[maxwidth]".
				"&maxheight=$parameter[maxheight]".
				"&startrow=$parameter[startrow]".
				"&items=$parameter[items]".
				"&blog=$parameter[blog]".
				"&digest=".bindec(intval($parameter['digest'][1]).intval($parameter['digest'][2]).intval($parameter['digest'][3]).intval($parameter['digest'][4])).
				"&newwindow=$parameter[newwindow]".
				"&jscharset=$parameter[jscharset]".
				($parameter['cachelife'] != '' ? "&cachelife=$parameter[cachelife]" : '').
				(!empty($parameter['jstemplate']) ? '&jstemplate='.rawurlencode($parameter['jstemplate']) : '');

			$jsurlview = "$jsurl&nocache=yes";
			$jsurlview = "{$boardurl}api/javascript.php?$jsurlview&verify=".md5($authkey.$jsurlview);
			if(!$preview) {
				jssavesetting(4);
			}
			$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
			$jspreview = "<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
				dhtmlspecialchars("<script language=\"JavaScript\" src=\"{$boardurl}api/javascript.php?key=".rawurlencode($jskey)."\"></script>").
				"</textarea><br /><div class=\"jswizard\"><script language=\"JavaScript\" src=\"$jsurlview\"></script></div><br />";
		}

		if($jspreview) {
			showtype('preview', 'top');
			echo '<tr><td class="altbg1">'.$jspreview;
			showtype('', 'bottom');
			echo '<br />';
		}

		echo '<form method="post" action="admincp.php?action=jswizard&function=images#'.$lang['jswizard_images'].'">';

		showtype('jswizard_jstemplate', 'top');
		echo '<tr><td class="altbg1" colspan="2">'.$lang['jswizard_images_jstemplate_comment'].'<br />';
		echo '<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 0)"><br />';
		jsinsertunit();
		echo '<textarea cols="100" rows="5" id="jstemplate" name="parameter[jstemplate]" style="width: 95%;">'.($parameter['jstemplate'] != '' ? stripslashes($parameter['jstemplate']) : '{image}').'</textarea></td></tr>';
		showtype('', 'bottom');
		echo '<br />';

		showtype('jswizard_images', 'top');
		showsetting('jswizard_jskey', 'jskey', $jskey, 'text');
		showsetting('jswizard_cachelife', 'parameter[cachelife]', $parameter['cachelife'] != '' ? intval($parameter['cachelife']) : '', 'text');
		showsetting('jswizard_images_fids', '', '', jsforumselect('images'));
		showsetting('jswizard_images_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
		showsetting('jswizard_images_items', 'parameter[items]', isset($parameter['items']) ? $parameter['items'] : 5, 'text');
		showsetting('jswizard_images_maxwidth', 'parameter[maxwidth]', isset($parameter['maxwidth']) ? $parameter['maxwidth'] : 200, 'text');
		showsetting('jswizard_images_maxheight', 'parameter[maxheight]', isset($parameter['maxheight']) ? $parameter['maxheight'] : 200, 'text');
		showsetting('jswizard_images_blog', 'parameter[blog]', $parameter['blog'], 'radio');
		showsetting('jswizard_images_digest', '', '', '<input class="checkbox" type="checkbox" name="parameter[digest][1]" value="1" '.$icheckdigest[1].'> '.$lang['jswizard_digest_1'].'<br /><input class="checkbox" type="checkbox" name="parameter[digest][2]" value="1" '.$icheckdigest[2].'> '.$lang['jswizard_digest_2'].'<br /><input class="checkbox" type="checkbox" name="parameter[digest][3]" value="1" '.$icheckdigest[3].'> '.$lang['jswizard_digest_3'].'<br /><input class="checkbox" type="checkbox" name="parameter[digest][4]" value="1" '.$icheckdigest[4].'> '.$lang['jswizard_digest_0']);
		showsetting('jswizard_images_newwindow', 'parameter[newwindow]', '', '<input class="radio" type="radio" name="parameter[newwindow]" value="0" '.$tradionewwindow[0].'> '.$lang['jswizard_newwindow_self'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="1" '.$tradionewwindow[1].'> '.$lang['jswizard_newwindow_blank'].'<br /><input class="radio" type="radio" name="parameter[newwindow]" value="2" '.$tradionewwindow[2].'> '.$lang['jswizard_newwindow_main']);
		if(strtoupper($charset) != 'UTF-8') {
			showsetting('jswizard_charset', 'parameter[jscharset]', $parameter['jscharset'], 'radio');
		} else {
			showsetting('jswizard_charsetr', array('parameter[jscharset]', array(array(0, $lang['none']), array(1, 'GBK'), array(2, 'BIG5'))), intval($parameter['jscharset']), 'mradio');
		}
		showtype('', 'bottom');
		echo $editext.'<br /><center><input class="button" type="submit" name="jssubmit" value="'.$lang['jswizard_preview'].'">&nbsp; &nbsp;<input class="button" type="button" onclick="this.form.preview.value=0;this.form.jssubmit.click()" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="1"></center></form><br />';
		/* Images == End == */

	} elseif($type == 'custom') {

		/* Custom == Start == */
		if($jssubmit && $function == 'custom') {

			$jsurl = $jssetting ? $jssetting : "function=$function".
				"&jscharset=$parameter[jscharset]".
				($parameter['cachelife'] != '' ? "&cachelife=$parameter[cachelife]" : '').
				(!empty($parameter['jstemplate']) ? '&jstemplate='.rawurlencode($parameter['jstemplate']) : '');

			$jsurlview = "$jsurl&nocache=yes";
			$jsurlview = "{$boardurl}api/javascript.php?$jsurlview&verify=".md5($authkey.$jsurlview);
			if(!$preview) {
				jssavesetting(-1);
			}
			$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
			$jspreview = "<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
				dhtmlspecialchars("<script language=\"JavaScript\" src=\"{$boardurl}api/javascript.php?key=".rawurlencode($jskey)."\"></script>").
				"</textarea><br /><div class=\"jswizard\"><script language=\"JavaScript\" src=\"$jsurlview\"></script></div><br />";
		}

		if($jspreview) {
			showtype('preview', 'top');
			echo '<tr><td class="altbg1">'.$jspreview;
			showtype('', 'bottom');
			echo '<br />';
		}

		echo '<form method="post" action="admincp.php?action=jswizard&function=custom#'.$lang['jswizard_custom'].'">';

		showtype('jswizard_jstemplate', 'top');
		echo '<tr><td class="altbg1" colspan="2">'.$lang['jswizard_custom_jstemplate_comment'].'<br />';
		echo '<img src="images/admincp/zoomin.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 1)"> <img src="images/admincp/zoomout.gif" onmouseover="this.style.cursor=\'pointer\'" onclick="zoomtextarea(\'jstemplate\', 0)"><br />';
		jsinsertunit();
		echo '<textarea cols="100" rows="5" id="jstemplate" name="parameter[jstemplate]" style="width: 95%;">'.($parameter['jstemplate'] != '' ? stripslashes($parameter['jstemplate']) : '').'</textarea></td></tr>';
		showtype('', 'bottom');
		echo '<br />';

		showtype('jswizard_custom', 'top');
		showsetting('jswizard_jskey', 'jskey', $jskey, 'text');
		showsetting('jswizard_cachelife', 'parameter[cachelife]', $parameter['cachelife'] != '' ? intval($parameter['cachelife']) : '', 'text');
		if(strtoupper($charset) != 'UTF-8') {
			showsetting('jswizard_charset', 'parameter[jscharset]', $parameter['jscharset'], 'radio');
		} else {
			showsetting('jswizard_charsetr', array('parameter[jscharset]', array(array(0, $lang['none']), array(1, 'GBK'), array(2, 'BIG5'))), intval($parameter['jscharset']), 'mradio');
		}
		showtype('', 'bottom');
		echo $editext.'<br /><center><input class="button" type="submit" name="jssubmit" value="'.$lang['jswizard_preview'].'">&nbsp; &nbsp;<input class="button" type="button" onclick="this.form.preview.value=0;this.form.jssubmit.click()" value="'.$lang['submit'].'"><input name="preview" type="hidden" value="1"></center></form><br />';
		/* Custom == End == */

	}

} elseif($action == 'fileperms') {

	shownav('menu_tools_fileperms');
	showtips('fileperms_tips');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['fileperms_check']?></td></tr>
<tr><td class="altbg1"><br /><ul>
<?

	$entryarray = array(
		'attachments',
		'forumdata',
		'customavatars',
		'forumdata/threadcaches'
	);

	foreach(array('templates', 'forumdata/cache', 'forumdata/logs', 'forumdata/templates') as $directory) {
		getdirentry($directory);
	}

	$fault = 0;
	foreach($entryarray as $entry) {
		$fullentry = DISCUZ_ROOT.'./'.$entry;
		if(!is_dir($fullentry) && !file_exists($fullentry)) {
			continue;
		} else {
			if(!is_writeable($fullentry)) {
				echo '<li style="color: #FF0000">'.(is_dir($fullentry) ? $lang['fileperms_dir'] : $lang['fileperms_file'])." ./$entry $lang[fileperms_unwritable]";
				$fault = 1;
			}
		}
	}
	echo ($fault ? '' : '<li>'.$lang['fileperms_check_ok']).'</ul><br /></td></tr></table>';

}

function jsforumselect($function) {
	global $parameter, $lang, $db, $tablepre;
	if(empty($function) || in_array($function, array('forums', 'threads', 'images'))) {
		$forumselect = '<select name="parameter['.$function.'_forums][]" size="5" multiple="multiple">'.
			'<option value="all" '.(is_array($parameter[$function.'_forums']) && in_array('all', $parameter[$function.'_forums']) ? 'selected="selected"' : '').'> '.$lang['jswizard_all_forums'].'</option>'.
			'<option value="">&nbsp;</option>';
		if($function == 'forums') {
			$query = $db->query("SELECT fid, name FROM {$tablepre}forums WHERE type='group' AND status>0 ORDER BY displayorder");
			while($category = $db->fetch_array($query)) {
				$forumselect .= '<option value="'.$category['fid'].'">'.strip_tags($category['name']).'</option>';
			}
		} else {
			require_once DISCUZ_ROOT.'./include/forum.func.php';
			$forumselect .= forumselect();
		}
		$forumselect .= '</select>';

		if(is_array($parameter[$function.'_forums'])) {
			foreach($parameter[$function.'_forums'] as $key => $value) {
				if(!$value) {
					unset($parameter[$function.'_forums'][$key]);
				}
			}
			if(!in_array('all', $parameter[$function.'_forums'])) {
				$forumselect = preg_replace("/(\<option value=\"(".implode('|', $parameter[$function.'_forums']).")\")(\>)/", "\\1 selected=\"selected\"\\3", $forumselect);
			}
		}
		return $forumselect;
	}
}

function jsfids($fidarray) {
	foreach($fidarray as $key => $val) {
		if(empty($val)) {
			unset($fidarray[$key]);
		}
	}
	return implode('_', $fidarray);
}

function jssavesetting($type) {
	global $db, $tablepre, $jswizard, $jsurl, $parameter, $jskey, $edit;
	$editadd = $edit ? "AND variable!='jswizard_$edit'" : '';
	if($db->result_first("SELECT variable FROM {$tablepre}request WHERE variable='$jskey' $editadd")) {
		cpmsg('jswizard_jskey_exists');
	}
	$jskey = str_replace('&', '', $jskey);
	$jswizard = addslashes(serialize(array('type' => $type, 'url' => $jsurl, 'parameter' => $parameter)));
	if(strlen($jswizard) > 65535) {
		cpmsg('jswizard_overflow');
	}
	if($edit) {
		$db->query("UPDATE {$tablepre}settings SET variable='jswizard_$jskey', value='$jswizard' WHERE variable='jswizard_$edit'");
	} else {
		$db->query("INSERT INTO {$tablepre}settings (variable, value) VALUES ('jswizard_$jskey', '$jswizard')");
	}
	updatecache('jswizard');
	@unlink(DISCUZ_ROOT.'./forumdata/cache/javascript_'.$jskey.'.php');
	cpmsg('jswizard_succeed', 'admincp.php?action=jswizard');
}

function jsinsertunit() {

?>
<script>
function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function insertunit(text) {
	$('jstemplate').focus();
	if(!isUndefined($('jstemplate').selectionStart)) {
		var opn = $('jstemplate').selectionStart + 0;
		$('jstemplate').value = $('jstemplate').value.substr(0, $('jstemplate').selectionStart) + text + $('jstemplate').value.substr($('jstemplate').selectionEnd);
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		sel.text = text.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character', -strlen(text));
	} else {
		$('jstemplate').value += text;
	}
}
</script>
<?

}

function getdirentry($directory) {
	global $entryarray;
	$dir = dir(DISCUZ_ROOT.'./'.$directory);
	while($entry = $dir->read()) {
		if($entry != '.' && $entry != '..') {
			if(is_dir(DISCUZ_ROOT.'./'.$directory.'/'.$entry)) {
				$entryarray[] = $directory.'/'.$entry;
				getdirentry($directory."/".$entry);
			} else {
				$entryarray[] = $directory.'/'.$entry;
			}
		}
	}
	$dir->close();
}

?>