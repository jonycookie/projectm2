<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: video.inc.php 11068 2007-10-31 02:54:25Z heyond $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='videoinfo'");
$settings = unserialize($db->result($query, 0));

if($action == 'videoconfig') {

	$settings['bbname'] = $settings['bbname'] ? $settings['bbname'] : $bbname;
	$settings['url'] = $settings['url'] ? $settings['url'] : $boardurl;
	$settingsnew['sitetype'] = $settings['sitetype'];

	$sitetypeselect = $br = '';
	if($sitetypearray = explode("\t", $settings['sitetype'])) {
		foreach($sitetypearray as $key => $sitetype) {
			$br = ($key + 1) % 6 == 0 ? '<br />' : '';
			$selected = $settings['type'] == $key + 1 ? 'checked' : '';
			$sitetypeselect .= '<input type="radio" class="radio" name="settingsnew[type]" value="'.($key + 1).'" '.$selected.'> '.$sitetype.'&nbsp;&nbsp;&nbsp;'.$br;
		}
	}

	if(!submitcheck('configsubmit')) {

		shownav('video_config');
		if(empty($settings['email'])) {
			showtips('video_tips');
		} else {
			showtips('video_tips_account');
		}

?>
<form method="post" name="settings" action="admincp.php?action=videoconfig">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('video_config','top');
		showsetting('video_open', 'settingsnew[open]', $settings['open'], 'radio');

		showtype('video_basic');
		showsetting('video_site_name', 'settingsnew[bbname]', $settings['bbname'], 'text');
		showsetting('video_site_url', 'settingsnew[url]', $settings['url'], 'text');
		showsetting('video_site_email', 'settingsnew[email]', $settings['email'], 'text');
		showsetting('video_site_logo', 'settingsnew[logo]', $settings['logo'], 'text');
		showsetting('video_site_type', '', '', $sitetypeselect);

		showtype('', 'bottom');

		echo '<br /><center><input class="button" type="submit" name="configsubmit" value="'.$lang['submit'].'"></form>';

	} else {

		if($charset != 'utf-8') {
			require_once DISCUZ_ROOT.'./include/chinese.class.php';
			$chs = new Chinese($charset, 'UTF-8');
			$settingsnew['bbnameu8'] = $chs->Convert($settingsnew['bbname']);
		} else {
			$settingsnew['bbnameu8'] = $settingsnew['bbname'];
		}

		$settingsnew['vtype'] = str_replace("\r\n", "\n", $settingsnew['vtype']);
		$settingsnew['vtype'] = str_replace("\n", "\t", $settingsnew['vtype']);
		if($settingsnew['vtype']) {
			$sitetypexml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<channels>\n";
			foreach(explode("\t", $settingsnew['vtype']) as $key => $typename) {
				$sitetypexml .= $typename ? "	<channel id='".intval($key + 32)."' name='".($charset != 'utf-8' ? $chs->Convert(htmlspecialchars($typename)) : $typename)."' />\n" : '';
			}
			$sitetypexml .= "</channels>";
			if($fp = @fopen(DISCUZ_ROOT.'./forumdata/video_category.xml', 'wb')) {
				fwrite($fp, $sitetypexml);
				fclose($fp);
			}
		}

		if(!$settings['vsiteid'] && $settingsnew['open']) {
			if(!$settingsnew['bbname'] || !$settingsnew['url'] || !$settingsnew['email'] || !$settingsnew['type']) {
				cpmsg('video_config_invalid');
			}
			$videoinfo = dfopen("http://union.bokecc.com/discuz2/reg.bo?siteName=".urlencode($settingsnew['bbnameu8'])."&siteUrl=".urlencode($settingsnew['url'])."&siteLogo=".urlencode($settingsnew['logo'])."&siteType=".intval($settingsnew['type'])."&siteEmail=".urlencode($settingsnew['email']));
			videocheckcode($videoinfo);
			$videoinfo = explode(',', $videoinfo);
			if($videoinfo[0] != '-1') {
				$settingsnew['vsiteid'] = trim($videoinfo[0]);
				$settingsnew['vpassword'] = trim($videoinfo[1]);
				$settingsnew['vkey'] = trim($videoinfo[2]);
			}
		} else {
			if($settingsnew['bbname'] != $settings['bbname'] || $settingsnew['email'] != $settings['email'] || $settingsnew['url'] != $settings['url'] || $settingsnew['logo'] != $settings['logo'] || $settingsnew['type'] != $settings['type']) {
				$code = urlencode(authcode("vpassword=$vpassword&siteName=".urlencode($settingsnew['bbnameu8'])."&siteUrl=".urlencode($settingsnew['url'])."&siteLogo=".urlencode($settingsnew['logo'])."&siteType=".intval($settingsnew['type'])."&siteEmail=".urlencode($settingsnew['email']), 'ENCODE', $vkey));
				$videoinfo = dfopen("http://union.bokecc.com/discuz2/edit.bo?siteid=$vsiteid&code=$code");
				if($videoinfo == '-1') {
					cpmsg('video_error_code_update_error');
				}
			}
			$settingsnew['vsiteid'] = trim($settings['vsiteid']);
			$settingsnew['vpassword'] = trim($settings['vpassword']);
			$settingsnew['vkey'] = trim($settings['vkey']);
		}
		$settingsnew['vclasses'] = $settings['vclasses'];
		$settingsnew['vclassesable'] = $settings['vclassesable'];
		unset($settingsnew['bbnameu8']);
		$settingsnew = addslashes(serialize($settingsnew));
		$db->query("UPDATE {$tablepre}settings SET value='$settingsnew' WHERE variable='videoinfo'");

		updatecache('settings');
		cpmsg('video_config_succeed');

	}

} elseif($action == 'videobind') {

	if(!submitcheck('bindsubmit')) {

		shownav('menu_video_bind');
		if(empty($vsiteid)) {
			showtips('video_tips_bind');
		} else {
			showtips('video_tips_account_exists');
		}

?>
<form method="post" name="settings" action="admincp.php?action=videobind">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('video_cc_bind','top');
		showsetting('video_cc_email', 'settingsnew[vemail]', $settings['email'], 'text');
		showsetting('video_cc_password', 'settingsnew[vpassword]', $settings['vpassword'], 'password');
		showtype('', 'bottom');
		echo '<br /><center><input class="button" type="submit" name="bindsubmit" value="'.$lang['submit'].'"></form>';

	} else {

		$returninfo = dfopen("http://union.bokecc.com/discuz2/bind.bo?vpassword=$settingsnew[vpassword]&siteEmail=$settingsnew[vemail]");
		if(videocheckbind($returninfo)) {
			list($vsiteid, $vkey) = explode(',', $returninfo);
			$settings['vsiteid'] = $vsiteid;
			$settings['vkey'] = $vkey;
			$settings['email'] = $settingsnew['vemail'];
			$settings['vpassword'] = $settingsnew['vpassword'];
			$settings = addslashes(serialize($settings));
			$db->query("UPDATE {$tablepre}settings SET value='$settings' WHERE variable='videoinfo'");
			updatecache('settings');
			cpmsg('video_bind_succeed');
		}
	}

} elseif($action == 'videoclass') {

	$settingsnew['sitetype'] = $settings['sitetype'];

	$sitetypeselect = $br = '';
	if($sitetypearray = explode("\t", $settings['sitetype'])) {
		foreach($sitetypearray as $key => $sitetype) {
			$br = ($key + 1) % 6 == 0 ? '<br />' : '';
			$selected = $settings['type'] == $key + 1 ? 'checked' : '';
			$sitetypeselect .= '<input type="radio" class="radio" name="settingsnew[type]" value="'.($key + 1).'" '.$selected.'> '.$sitetype.'&nbsp;&nbsp;&nbsp;'.$br;
		}
	}

	if(!submitcheck('classsubmit')) {
		//vclasses
		$vclasses = $settings['vclasses'];
		$vclassesable = $settings['vclassesable'];
		$insideclasses = $customclasses = '<div style="clear: both; overflow: hidden;">';
		for($i=1; $i<33; $i++) {
			$checked = in_array($i, $vclassesable) ? ' checked="checked"' : '';
			$vclasses[$i] && $insideclasses .= '<li style="list-style: none; width: 100px; float: left;"><input type="checkbox" name="vclassesablenew[]" class="radio"'.$checked.' value="'.$i.'"> '.$vclasses[$i].'</li>';
		}
		$insideclasses .= '</div>';
		for($i=33; $i<45; $i++) {
			$checked = in_array($i, $vclassesable) ? ' checked="checked"' : '';
			$customclasses .= '<li style="list-style: none; width: 100px; float: left;""><input type="checkbox" name="vclassesablenew[]" class="radio"'.$checked.' value="'.$i.'"> <input type="text" size="8" name="vclassesnew['.$i.']" value="'.$vclasses[$i].'"></li>';
		}
		$customclasses .= '</div>';

?>
<form method="post" name="settings" action="admincp.php?action=videoclass">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('menu_video_class', 'top');
		showsetting('video_class', '',  '', $insideclasses.$customclasses);
		showtype('', 'bottom');
		echo '<br /><center><input class="button" type="submit" name="classsubmit" value="'.$lang['submit'].'"></form>';
	} else {

		foreach($vclassesnew as $k=>$v) {
			$settings['vclasses'][$k] = $vclassesnew[$k];
		}
		foreach($vclassesnew as $k=>$v) {
			if(empty($settings['vclasses'][$k])) {
				unset($settings['vclasses'][$k]);
			}
		}

		$settings['vclassesable'] = $vclassesablenew;
		$settings = addslashes(serialize($settings));
		$db->query("UPDATE {$tablepre}settings SET value='$settings' WHERE variable='videoinfo'");
		updatecache('settings');
		cpmsg('video_class_update_succeed');
	}
}

function videocheckcode($code) {
	if($code == '-1') {
		cpmsg('video_error_code_email_invalid');
	} elseif($code == '-2') {
		cpmsg('video_error_code_email_registered');
	} elseif($code == '-3') {
		cpmsg('video_error_code_url_invalid');
	} elseif($code == '-4') {
		cpmsg('video_error_code_sitename_invalid');
	} elseif($code == '-5') {
		cpmsg('video_error_code_sitetype_invalid');
	} elseif($code == '-6') {
		cpmsg('video_error_code_server_unknown');
	} elseif(empty($code)) {
		cpmsg('video_error_code_server_busy');
	} else {
		return TRUE;
	}
}

function videocheckbind($code) {
	if($code == '-1') {
		cpmsg('video_bind_error_code_1');
	} elseif($code == '-2') {
		cpmsg('video_bind_error_code_2');
	} elseif($code == '-3') {
		cpmsg('video_bind_error_code_3');
	} elseif($code == '-4' || $code == '') {
		cpmsg('video_bind_error_code_4');
	} else {
		return TRUE;
	}
}

?>