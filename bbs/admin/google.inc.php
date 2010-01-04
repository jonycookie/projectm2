<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: google.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$google = ($google = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='google'")) ? unserialize($google) : array();

if($action == 'google_config') {

	if(!submitcheck('googlesubmit')) {

		$checks = array();
		$checkstatus = array($google['status'] => 'checked');
		$checklocation = array($google['location'] => 'checked');
		$checkrelatedsort = array($google['relatedsort'] => 'checked');
		$google['searchbox'] = sprintf('%03b', $google['searchbox']);
		for($i = 1; $i <= 3; $i++) {
			$checks[$i] = $google['searchbox'][3 - $i] ? 'checked' : '';
		}

		shownav('google_settings_basic');

?>
<form method="post" name="settings" action="admincp.php?action=google_config">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('google','top');
		showsetting('google_status', 'googlenew[status]', $google['status'], 'radio');
		showsetting('google_searchbox', '', '', '<input class="checkbox" type="checkbox" name="googlenew[searchbox][1]" value="1" '.$checks[1].'> '.$lang['google_searchbox_index'].'<br /><input class="checkbox" type="checkbox" name="googlenew[searchbox][2]" value="1" '.$checks[2].'> '.$lang['google_searchbox_forumdisplay'].'<br /><input class="checkbox" type="checkbox" name="googlenew[searchbox][3]" value="1" '.$checks[3].'> '.$lang['google_searchbox_viewthread']);
		showsetting('google_lang', array('googlenew[lang]', array(
			array('', $lang['google_lang_any']),
			array('en', $lang['google_lang_en']),
			array('zh-CN', $lang['google_lang_zh-CN']),
			array('zh-TW', $lang['google_lang_zh-TW']))), $google['lang'], 'mradio');
		showtype('', 'bottom');

		echo '<br /><center><input class="button" type="submit" name="googlesubmit" value="'.$lang['submit'].'"></form>';

	} else {

		$googlenew['searchbox'] = bindec(intval($googlenew['searchbox'][3]).intval($googlenew['searchbox'][2]).intval($googlenew['searchbox'][1]));

		$db->query("UPDATE {$tablepre}settings SET value='".addslashes(serialize($googlenew))."' WHERE variable='google'");
		updatecache('settings');
		updatecache('google');
		cpmsg('google_succeed');

	}

}

?>