<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: login.inc.php 16713 2008-11-17 03:25:20Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./include/misc.func.php';

if(empty($logout)) {

	if(empty($username)) {

		echo "<p>$lang[login_username]:<input type=\"text\" name=\"username\" maxlength=\"15\" format=\"M*m\" /><br />\n".
			"$lang[password]: <input type=\"password\" name=\"password\" value=\"\" format=\"M*m\" /><br />\n".
			"<anchor title=\"$lang[submit]\">$lang[submit]".
			"<go method=\"post\" href=\"index.php?action=login&amp;sid=$sid\">\n".
			"<postfield name=\"username\" value=\"$(username)\" />\n".
			"<postfield name=\"password\" value=\"$(password)\" />\n".
			"</go></anchor></p>\n";

	} else {

		$loginperm = logincheck();

		if(!$loginperm) {
			wapmsg('login_strike');
		}
		
		$answer = wapconvert($answer);
		$username = wapconvert($username);

		$secques = quescrypt($questionid, $answer);

		if(isset($loginauth)) {
			list($username, $md5_password) = daddslashes(explode("\t", authcode($loginauth, 'DECODE')), 1);
		} else {
			$md5_password = md5($password);
		}

		$usernameadd = preg_match("/^\d+$/", $username) ? "(uid='$username' OR username='$username')" : "username='$username'";
		$member = $db->fetch_first("SELECT uid AS discuz_uid, username AS discuz_user, password AS discuz_pw, secques AS discuz_secques, groupid, invisible
			FROM {$tablepre}members WHERE $usernameadd");

		if($member['discuz_uid'] && $member['discuz_pw'] == $md5_password) {

			if($member['discuz_secques'] && $member['discuz_secques'] != $secques) {
				$loginauth = authcode($member['discuz_user']."\t".$member['discuz_pw'], 'ENCODE');
				echo "<p>$lang[security_question]:
					<select name=\"questionid\">
					<option value=\"0\">$lang[security_question_0]</option>
					<option value=\"1\">$lang[security_question_1]</option>
					<option value=\"2\">$lang[security_question_2]</option>
					<option value=\"3\">$lang[security_question_3]</option>
					<option value=\"4\">$lang[security_question_4]</option>
					<option value=\"5\">$lang[security_question_5]</option>
					<option value=\"6\">$lang[security_question_6]</option>
					<option value=\"7\">$lang[security_question_7]</option>
					</select><br />\n".
					"$lang[security_answer]: <input type=\"answer\" name=\"answer\" value=\"\" format=\"M*m\" /><br />\n".
					"<anchor title=\"$lang[submit]\">$lang[submit]".
					"<go method=\"post\" href=\"index.php?action=login&amp;sid=$sid\">\n".
					"<postfield name=\"questionid\" value=\"$(questionid)\" />\n".
					"<postfield name=\"answer\" value=\"$(answer)\" />\n".
					"<postfield name=\"username\" value=\"$member[discuz_user]\" />\n".
					"<postfield name=\"loginauth\" value=\"$loginauth\" />\n".
					"</go></anchor></p>\n";
			} else {
				@extract($member);
				dsetcookie('auth', authcode("$discuz_pw\t$discuz_secques\t$discuz_uid", 'ENCODE'), 2592000);
				wapmsg('login_succeed');
			}

		} else {

			$errorlog = dhtmlspecialchars(
				$timestamp."\t".
				($member['discuz_user'] ? $member['discuz_user'] : stripslashes($username))."\t".
				$password."\t".
				($secques ? "Ques #".intval($questionid) : '')."\t".
				$onlineip);
			writelog('illegallog', $errorlog);
			loginfailed($loginperm);
			wapmsg('login_invalid');

		}

	}

} elseif(!empty($formhash) && $formhash == FORMHASH) {

	$discuz_uid = 0;
	$discuz_user = '';
	$groupid = 7;

	wapmsg('logout_succeed');

}

?>