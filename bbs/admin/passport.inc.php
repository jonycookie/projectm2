<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: passport.inc.php 9769 2007-08-14 02:08:43Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if(!in_array($action, array('passport', 'shopex'))) {
	cpmsg('undefined_action');
}

$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable LIKE 'passport_%'");
while($setting = $db->fetch_array($query)) {
	$settings[$setting['variable']] = $setting['value'];
}

if($settings['passport_status'] && $settings['passport_status'] != $action) {
	cpmsg('passport_notmatch');
}

if(!submitcheck('ppsubmit')) {

	shownav($action == 'passport' ? 'menu_passport_settings' : 'menu_passport_'.$action);
	showtips($action == 'passport' ? 'passport_tips' : 'passport_'.$action.'_tips');

?>
<form method="post" name="settings" action="admincp.php">
<input type="hidden" name="action" value="<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

	$passportcredits = '<select name="settingsnew[passport_extcredits]">';
	for($i = 0; $i <= 8; $i++) {
		$passportcredits .= '<option value="'.$i.'" '.($settings['passport_extcredits'] == $i ? 'selected' : '').'>'.($i ? 'extcredits'.$i.(!empty($extcredits[$i]['title']) ? ' ('.$extcredits[$i]['title'].')' : '') : $lang['passport_extcredits_none']).'</option>';
	}
	$passportcredits .= '</select>';

	showtype('passport_settings', 'top');
	showsetting('passport_status', 'settingsnew[passport_status]', $settings['passport_status'] == $action, 'radio', '', '', 1);
	if($action == 'shopex') {
		showsetting('passport_shopex', 'settingsnew[passport_shopex]', $settings['passport_shopex'], 'radio');
	}
	showsetting('passport_url', 'settingsnew[passport_url]', $settings['passport_url'], 'text');
	showsetting('passport_key', 'settingsnew[passport_key]', $settings['passport_key'], 'text');
	showsetting('passport_expire', 'settingsnew[passport_expire]', $settings['passport_expire'], 'text');

	if($action == 'shopex') {
		echo '<input type="hidden" name="settingsnew[passport_register_url]" value="index.php?gOo=cmVnaXN0ZXJfMS5kd3Q=">'.
			'<input type="hidden" name="settingsnew[passport_login_url]" value="index.php?gOo=bG9naW4uZHd0">'.
			'<input type="hidden" name="settingsnew[passport_logout_url]" value="index.php?gOo=bG9nb3V0X2FjdC5kbw==">';
	} else {
		showsetting('passport_register_url', 'settingsnew[passport_register_url]', $settings['passport_register_url'], 'text');
		showsetting('passport_login_url', 'settingsnew[passport_login_url]', $settings['passport_login_url'], 'text');
		showsetting('passport_logout_url', 'settingsnew[passport_logout_url]', $settings['passport_logout_url'], 'text');
	}

	showsetting('passport_extcredits', '', '', $passportcredits);
	showtype('', 'bottom');

	echo '<br /><center><input class="button" type="submit" name="ppsubmit" value="'.$lang['submit'].'"></center></form>';

} else {

	if($settingsnew['passport_status']) {

		if(!$settingsnew['passport_url'] || !$settingsnew['passport_register_url'] || !$settingsnew['passport_login_url'] || !$settingsnew['passport_logout_url']) {
			cpmsg('passport_url_invalid');
		} elseif(strlen($settingsnew['passport_key']) < 10) {
			cpmsg('passport_key_invalid');
		}

		$settingsnew['passport_status'] = $action;
		$settingsnew['passport_expire'] = $settingsnew['passport_expire'] < 0 ? 0 : $settingsnew['passport_expire'];
		if(substr(($settingsnew['passport_url'] = trim($settingsnew['passport_url'])), -1) != '/') {
			$settingsnew['passport_url'] = $settingsnew['passport_url'].'/';
		}

	} else {

		$settingsnew['passport_status']
			= $settingsnew['passport_register_url']
			= $settingsnew['passport_login_url']
			= $settingsnew['passport_logout_url']
			= '';

	}

	foreach($settingsnew as $key => $val) {
		if(isset($settings[$key]) && $settings[$key] != $val) {
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$key', '$val')");
		}
	}

	updatecache('settings');

	cpmsg('passport_update_succeed');

}

?>