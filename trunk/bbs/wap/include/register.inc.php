<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: register.inc.php 16725 2008-11-17 04:41:46Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

if($discuz_uid) {
	wapmsg('login_succeed');
}

if(!$wapregister) {
	wapmsg('register_disable');
}

$groupinfo = $db->fetch_first("SELECT groupid FROM {$tablepre}usergroups WHERE ".($regverify ? "groupid='8'" : "creditshigher<=".intval($initcredits)." AND ".intval($initcredits)."<creditslower LIMIT 1"));

if(empty($username)) {

	echo "<p>$lang[register_username]:<input type=\"text\" name=\"username\" value=\"\" maxlength=\"15\" /><br />\n".
		"$lang[password]: <input type=\"password\" name=\"password\" value=\"\" /><br />\n".
		"$lang[email]: <input type=\"text\" name=\"email\" value=\"\" /><br />\n".
		"<anchor title=\"$lang[submit]\">$lang[submit]".
		"<go method=\"post\" href=\"index.php?action=register&amp;sid=$sid\">\n".
		"<postfield name=\"username\" value=\"$(username)\" />\n".
		"<postfield name=\"password\" value=\"$(password)\" />\n".
		"<postfield name=\"email\" value=\"$(email)\" />\n".
		"</go></anchor></p>\n";

} else {
	
	$email = trim(wapconvert($email));
	$username = trim(wapconvert($username));
	$regmessage = dhtmlspecialchars(wapconvert($regmessage));

	if(strlen($username) > 15) {
		wapmsg('profile_username_toolang');
	}

	if(strlen($username) < 3) {
		wapmsg('profile_username_tooshort');
	}

	$guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';

	$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censoruser = trim($censoruser)), '/')).')$/i';
	if(preg_match("/^\s*$|^c:\\con\\con$|[%,\*\"\s\t\<\>\&]|$guestexp/is", $username) || ($censoruser && @preg_match($censorexp, $username))) {
		wapmsg('profile_username_illegal');
	}

	if(!$password || $password != addslashes($password)) {
		wapmsg('profile_passwd_illegal');
	}

	$accessexp = '/('.str_replace("\r\n", '|', preg_quote($accessemail, '/')).')$/i';
	$censorexp = '/('.str_replace("\r\n", '|', preg_quote($censoremail, '/')).')$/i';
	$invalidemail = $accessemail ? !preg_match($accessexp, $email) : $censoremail && preg_match($censorexp, $email);
	if(!isemail($email) || $invalidemail) {
		wapmsg('profile_email_illegal');
	}

	if($ipregctrl) {
		foreach(explode("\n", $ipregctrl) as $ctrlip) {
			if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $onlineip)) {
				$ctrlip = $ctrlip.'%';
				$regctrl = 72;
				break;
			}
		}
	} else {
		$ctrlip = $onlineip;
	}

	if($regctrl) {
		$query = $db->query("SELECT ip FROM {$tablepre}regips WHERE ip LIKE '$ctrlip' AND count='-1' AND dateline>$timestamp-'$regctrl'*3600 LIMIT 1");
		if($db->num_rows($query)) {
			wapmsg('register_ctrl', NULL, 'HALTED');
		}
	}

	$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$username'");
	if($db->num_rows($query)) {
		wapmsg('profile_username_duplicate');
	}

	if(!$doublee) {
		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE email='$email' LIMIT 1");
		if($db->num_rows($query)) {
			wapmsg('profile_email_duplicate');
		}
	}

	if($regfloodctrl) {
		if($regattempts = $db->result_first("SELECT count FROM {$tablepre}regips WHERE ip='$onlineip' AND count>'0' AND dateline>'$timestamp'-86400")) {
			if($regattempts >= $regfloodctrl) {
				showmessage('register_flood_ctrl', NULL, 'HALTED');
			} else {
				$db->query("UPDATE {$tablepre}regips SET count=count+1 WHERE ip='$onlineip' AND count>'0'");
			}
		} else {
			$db->query("INSERT INTO {$tablepre}regips (ip, count, dateline)
				VALUES ('$onlineip', '1', '$timestamp')");
		}
	}

	$password = md5($password);

	$idstring = random(6);
	$authstr = $regverify == 1 ? "$timestamp\t2\t$idstring" : '';

	$db->query("INSERT INTO {$tablepre}members (username, password, secques, gender, adminid, groupid, regip, regdate, lastvisit, lastactivity, posts, credits, extcredits1, extcredits2, extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8, email, bday, sigstatus, tpp, ppp)
		VALUES ('$username', '$password', '', '', '0', '$groupinfo[groupid]', '$onlineip', '$timestamp', '$timestamp', '$timestamp', '0', $initcredits, '$email', '', '', '20', '20')");
	$uid = $db->insert_id();

	$db->query("REPLACE INTO {$tablepre}memberfields (uid, authstr) VALUES ('$uid', '$authstr')");

	if($regverify == 2) {
		$db->query("REPLACE INTO {$tablepre}validating (uid, submitdate, moddate, admin, submittimes, status, message, remark)
			VALUES ('$uid', '$timestamp', '0', '', '1', '0', '$regmessage', '')");
	}

	$discuz_uid = $uid;
	$discuz_user = $username;
	$discuz_userss = stripslashes($discuz_user);
	$discuz_pw = $password;
	$groupid = $groupinfo['groupid'];
	$styleid = $styleid ? $styleid : $_DCACHE['settings']['styleid'];

	switch($regverify) {
		case 1:
			sendmail("$discuz_userss <$email>", 'email_verify_subject', 'email_verify_message');
			wapmsg('profile_email_verify');
			break;
		case 2:
			wapmsg('register_manual_verify', array('title' =>'memcp', 'link' => 'memcp.php'));
			break;
		default:
			wapmsg('register_succeed', array('title' =>'home_page', 'link' => 'index.php'));
			break;
	}
}

?>
