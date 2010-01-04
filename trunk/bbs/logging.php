<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: logging.php 11169 2007-11-08 06:30:00Z tiger $
*/

define('NOROBOT', TRUE);
define('CURSCRIPT', 'logging');

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/misc.func.php';

if($action == 'logout' && !empty($formhash)) {

	if($_DCACHE['settings']['frameon'] && $_DCOOKIE['frameon'] == 'yes') {
		$extrahead .= '<script>if(top != self) {parent.leftmenu.location.reload();}</script>';
	}

	if($formhash != FORMHASH) {
		showmessage('logout_succeed', dreferer());
	}

	clearcookies();
	$groupid = 7;
	$discuz_uid = 0;
	$discuz_user = $discuz_pw = '';
	$styleid = $_DCACHE['settings']['styleid'];

	if($passport_status == 'shopex' && $passport_shopex && $_DSESSION['adminid'] != 1) {
		$dreferer = dreferer();
		$verify = md5('logout'.$dreferer.$passport_key);
		showmessage('logout_succeed', 'api/relateshopex.php?action=logout&forward='.rawurlencode($dreferer).'&verify='.$verify);
	} else {
		showmessage('logout_succeed', dreferer());
	}

} elseif($action == 'login') {

	if($discuz_uid) {
		showmessage('login_succeed', $indexname);
	}
	$field = $loginfield == 'uid' ? 'uid' : 'username';

	//get secure code checking status (pos. -2)
	$seccodecheck = substr(sprintf('%05b', $seccodestatus), -2, 1);

	if($seccodecheck && $seccodedata['loginfailedcount']) {
		$seccodecheck = $db->result_first("SELECT count(*) FROM {$tablepre}failedlogins WHERE ip='$onlineip' AND count>='$seccodedata[loginfailedcount]' AND $timestamp-lastupdate<=900");
	}

	$seccodemiss = !empty($loginsubmit) && $seccodecheck && !$seccodeverify ? TRUE : FALSE;
	if(!submitcheck('loginsubmit', 1, $seccodemiss ? FALSE : $seccodecheck)) {

		$discuz_action = 6;

		$referer = dreferer();

		$thetimenow = '(GMT '.($timeoffset > 0 ? '+' : '').$timeoffset.') '.
			gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600).

		$styleselect = '';
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles WHERE available='1'");
		while($styleinfo = $db->fetch_array($query)) {
			$styleselect .= "<option value=\"$styleinfo[styleid]\">$styleinfo[name]</option>\n";
		}

		$_DCOOKIE['cookietime'] = isset($_DCOOKIE['cookietime']) ? $_DCOOKIE['cookietime'] : 2592000;
		$cookietimecheck = array((isset($_DCOOKIE['cookietime']) ? intval($_DCOOKIE['cookietime']) : 2592000) => 'checked="checked"');

		if($seccodecheck) {
			$seccode = random(6, 1) + $seccode{0} * 1000000;
		}

		include template('login');

	} else {

		if($_DCACHE['settings']['frameon'] && $_DCOOKIE['frameon'] == 'yes') {
			$extrahead .= '<script>if(top != self) {parent.leftmenu.location.reload();}</script>';
		}
		$discuz_uid = 0;
		$discuz_user = $discuz_pw = $discuz_secques = $md5_password = '';
		$member = array();

		$loginperm = logincheck();
		if(!$loginperm) {
			showmessage('login_strike');
		}

		$secques = quescrypt($questionid, $answer);

		if(isset($loginauth)) {
			$field = 'username';
			$password = 'VERIFIED';
			list($username, $md5_password) = daddslashes(explode("\t", authcode($loginauth, 'DECODE')), 1);
		} else {
			$md5_password = md5($password);
			$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
		}

		$member = $db->fetch_first("SELECT m.uid AS discuz_uid, m.username AS discuz_user, m.password AS discuz_pw, m.secques AS discuz_secques,
					m.adminid, m.groupid, m.styleid AS styleidmem, m.lastvisit, m.lastpost, u.allowinvisible
					FROM {$tablepre}members m LEFT JOIN {$tablepre}usergroups u USING (groupid)
					WHERE m.$field='$username'");


		if($member['discuz_uid'] && $member['discuz_pw'] == $md5_password) {

			if($member['discuz_secques'] == $secques && !$seccodemiss) {

				extract($member);

				$discuz_userss = $discuz_user;
				$discuz_user = addslashes($discuz_user);

				if(($allowinvisible && $loginmode == 'invisible') || $loginmode == 'normal') {
					$db->query("UPDATE {$tablepre}members SET invisible='".($loginmode == 'invisible' ? 1 : 0)."' WHERE uid='$member[discuz_uid]'", 'UNBUFFERED');
				}

				$styleid = intval(empty($_POST['styleid']) ? ($styleidmem ? $styleidmem :
						$_DCACHE['settings']['styleid']) : $_POST['styleid']);

				$cookietime = intval(isset($_POST['cookietime']) ? $_POST['cookietime'] :
						($_DCOOKIE['cookietime'] ? $_DCOOKIE['cookietime'] : 0));

				dsetcookie('cookietime', $cookietime, 31536000);
				dsetcookie('auth', authcode("$discuz_pw\t$discuz_secques\t$discuz_uid", 'ENCODE'), $cookietime);

				$sessionexists = 0;

				if($passport_status == 'shopex' && $passport_shopex) {
					if($groupid == 8) {
						$verify = md5('loginmemcp.php'.$passport_key);
						showmessage('login_succeed_inactive_member', 'api/relateshopex.php?action=login&forward=memcp.php&verify='.$verify);
					} else {
						$dreferer = dreferer();
						$verify = md5('login'.$dreferer.$passport_key);
						showmessage('login_succeed', 'api/relateshopex.php?action=login&forward='.rawurlencode($dreferer).'&verify='.$verify);
					}
				} else {
					if($groupid == 8) {
						showmessage('login_succeed_inactive_member', 'memcp.php');
					} else {
						showmessage('login_succeed', dreferer());
					}
				}

			} elseif(empty($secques) || $seccodemiss) {

				$username = dhtmlspecialchars($member['discuz_user']);
				$loginmode = dhtmlspecialchars($loginmode);
				$styleid = intval($styleid);
				$cookietime = intval($cookietime);
				$loginauth = authcode($member['discuz_user']."\t".$member['discuz_pw'], 'ENCODE');

				include template('login_secques');
				dexit();

			}

		}


		$errorlog = dhtmlspecialchars(
			$timestamp."\t".
			($member['discuz_user'] ? $member['discuz_user'] : stripslashes($username))."\t".
			($password)."\t".
			($secques ? "Ques #".intval($questionid) : '')."\t".
			$onlineip);
		writelog('illegallog', $errorlog);

		loginfailed($loginperm);

		showmessage('login_invalid', 'logging.php?action=login', 'HALTED');

	}

} else {
	showmessage('undefined_action');
}

?>