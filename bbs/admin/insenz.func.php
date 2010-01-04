<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: insenz.func.php 10624 2007-09-14 05:39:39Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

function insenz_checkfiles() {
	global $file;
	if(INSENZ_CHECKFILES) {
		$insenzfiles = array(
			'727ee8758b60a8d281f9d4a7e9778fbc' => './api/insenz.php',
			'b19e7fd23c85e4612674d144b20fb95c' => './include/insenz.func.php',
			'7a283729f138829bfd3eeae975338ed3' => './include/insenz_cron.func.php',
			'e1b1d72d90ef300dedac7c8c8f872fd8' => './include/xmlparser.class.php'
		);
		foreach($insenzfiles AS $md5 => $file) {
			if(!file_exists($file) || $md5 != md5_file(DISCUZ_ROOT.$file)) {
				cpmsg('insenz_upload_failed');
			}
		}
	}
}

function checkusername($username) {
	$username = trim($username);
	if(strlen($username) < 4 || strlen($username) > 20) {
		insenz_alert('insenz_username_length_outof_range', 'username');
	} elseif(!preg_match("/^\w+$/i", $username)) {
		insenz_alert('insenz_username_length_out_of_ranger', 'username');
	}
	return $username;
}

function checkpassword($password, $password2) {
	if($password != $password2) {
		insenz_alert('insenz_password_twice_diffenrent', 'password2');
	} elseif(strlen($password) < 6 || strlen($password) > 20) {
		insenz_alert('insenz_password_length_outof_range', 'password');
	} elseif(!preg_match("/^[0-9a-z!#$%&()+\\-.\\[\\]\\/\\\\@?{}|:;]+$/i", $password)) {
		insenz_alert('insenz_password_include_special_character', 'password');
	}
        return $password;
}

function checkname($name) {
	$name = trim($name);
	if(strlen($name) < 4 || strlen($name) > 30) {
		insenz_alert('insenz_name_length_outof_range', 'name');
	} elseif(htmlspecialchars($name) != $name) {
		insenz_alert('insenz_name_illegal', 'name');
	}
	return $name;
}

function checkemail($email, $emailname) {
	$email = trim($email);
	if(strlen($email) < 7 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) {
		insenz_alert('insenz_'.$emailname.'_illegal', $emailname);
	}
	return htmlspecialchars($email);
}

function checkidcard($idcard) {
	$idcard = htmlspecialchars(trim($idcard));

	if(strlen($idcard) == 18) {
		if(idcard_verify_number(substr($idcard, 0, 17)) == strtoupper($idcard{17})) {
			return $idcard;
		}
	}
	insenz_alert('insenz_idcard_illegal', 'idcard');
}

function checktel($tel1, $tel2, $tel3, $telname) {
	if(!preg_match("/^\d{2,4}$/", $tel1) || !preg_match("/^\d{5,10}$/", $tel2) || ($tel3 && !preg_match("/^\d{1,5}$/", $tel3))) {
		insenz_alert($telname.'insenz_illegal', $telname);
	}
	return $tel1.'-'.$tel2.'-'.$tel3;
}

function checkqq($qq) {
	if(!(preg_match("/^([0-9]+)$/", $qq) && strlen($qq) >= 5 && strlen($qq) <= 12)) {
		insenz_alert('insenz_qq_illegal', 'qq');
	}
	return $qq;
}

function checkmobile($mobile) {
	if(!preg_match("/^1(3|5)\d{9}$/", $mobile)) {
		insenz_alert('insenz_mobile_illegal', 'mobile');
	}
	return $mobile;
}

function checkcpc($country, $province, $city) {
	$country = intval($country);
	if($country < 10000 || $country > 70300) {
		insenz_alert('insenz_country_illegal', 'country');
	}
	$province = intval($province);
	if($country == 10000 && ($province < 10100 || $province > 13100)) {
		insenz_alert('insenz_province_illegal', 'province');
	}
	$city = intval($city);
	if($country == 10000 && ($city < 10101 || $city > 13107)) {
		insenz_alert('insenz_city_illegal', 'city');
	}
	return array($country, $province, $city);
}

function checkaddress($address) {
	$address = htmlspecialchars(trim($address));
	if(strlen($address) < 8) {
		insenz_alert('insenz_real_address_illegal', 'address');
	}
	return cutstr($address, 200);
}

function checkpostcode($postcode) {
	if(!preg_match("/^\d{6}$/", $postcode)) {
		insenz_alert('insenz_post_code_illegal', 'postcode');
	}
	return $postcode;
}

function idcard_verify_number($idcard_base) {
	$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
	$checksum = 0;
	for($i = 0; $i < strlen($idcard_base); $i++) {
		$checksum += substr($idcard_base, $i, 1) * $factor[$i];
	}
	$mod = $checksum % 11;
	$verify_number = $verify_number_list[$mod];
	return $verify_number;
}

function checkmasks($return = FALSE) {
	global $insenz, $db, $tablepre, $admins, $members, $noneexistusers, $member;

	if(!$admins = trim($admins)) {
		$return ? cpmsg('insenz_require_one_admin') : insenz_alert('insenz_require_one_admin', 'admins');
	} else {
		$admins = array_unique(explode("\n", str_replace(array("\r\n", "\r"), array("\n", "\n"), $admins)));
	}

	$admin_masks = $member_masks = array();
	$query = $db->query("SELECT uid, username, adminid FROM {$tablepre}members WHERE username IN ('".implode("','", $admins)."')");
	while($member = $db->fetch_array($query)) {
		if($member['adminid'] <= 0) {
			$return ? cpmsg('insenz_illegal_admin') : insenz_alert('insenz_illegal_admin', 'admins');
		}
		$admin_masks[$member['uid']] = addslashes($member['username']);
	}
	if($noneexistusers = array_diff($admins, $admin_masks)) {
		$noneexistusers = stripslashes(implode(', ', $noneexistusers));
		$return ? cpmsg('insenz_user_not_exists') : insenz_alert('insenz_user_not_exists', 'admins');
	}

	$members = array_unique(explode("\n", str_replace(array("\r\n", "\r"), array("\n", "\n"), trim($members))));
	if(count($members) < 2) {
		$return ? cpmsg('insenz_require_two_normal_poster') : insenz_alert('insenz_require_two_normal_poster', 'members');
	}

	$query = $db->query("SELECT uid, username, adminid FROM {$tablepre}members WHERE username IN ('".implode("','", $members)."')");
	while($member = $db->fetch_array($query)) {
		if($member['adminid'] > 0) {
			$return ? cpmsg('insenz_not_normal_user') : insenz_alert('insenz_not_normal_user', 'members');
		}
		$member_masks[$member['uid']] = addslashes($member['username']);
	}
	if($noneexistusers = array_diff($members, $member_masks)) {
		$noneexistusers = stripslashes(implode(', ', $noneexistusers));
		$return ? cpmsg('insenz_user_not_exists') : insenz_alert('insenz_user_not_exists', 'members');
	}

	$modified = $insenz['admin_masks'] != $admin_masks || $insenz['member_masks'] != $member_masks;

	if(INSENZ_SAFEMODE && $insenz['member_masks'] != $member_masks) {
		if(!empty($insenz['groupid'])) {
			$query = $db->query("SELECT COUNT(*) FROM {$tablepre}usergroups WHERE groupid='$insenz[groupid]'");
			if(!$db->result($query, 0)) {
				unset($insenz['groupid']);
			}
		}
		if(empty($insenz['groupid'])) {
			$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type='member' ORDER BY creditslower DESC LIMIT 5");
			$groups = array();
			while($group = $db->fetch_array($query)) {
				$groups[$group['groupid']] = $group['grouptitle'];
			}
			$randgid = array_rand($groups);
			$grouptitle = $groups[$randgid] ? $groups[$randgid] : 'Member';
			$query = $db->query("SELECT * FROM {$tablepre}usergroups WHERE groupid='$randgid'");
			$fieldnums = mysql_num_fields($query);
			$group = $db->fetch_array($query);
			$fields = '';
			for($i = 0; $i < $fieldnums; $i++) {
				$field = mysql_field_name($query, $i);
				if(!in_array($field, array('groupid', 'type', 'grouptitle', 'allowpost', 'allowreply'))) {
					$fields .= ", $field='$group[$field]'";
				}
			}
			$db->query("INSERT INTO {$tablepre}usergroups SET type='special', grouptitle='$grouptitle', allowpost='1', allowreply='1' $fields");
			$insenz['groupid'] = $db->insert_id();
			require_once DISCUZ_ROOT.'./include/cache.func.php';
			updatecache('usergroups');
		}
		$db->query("UPDATE {$tablepre}members SET adminid=-1, groupid='$insenz[groupid]' WHERE uid IN (".implodeids(array_keys($member_masks)).")");
	}

	$insenz['admin_masks'] = $admin_masks;
	$insenz['member_masks'] = $member_masks;

	if($return) {
		return $modified;
	}

}

function checkip() {
	global $boardurl, $onlineip;

	if(INSENZ_CHECKIP) {
		$longip = ip2long($onlineip);
		if(substr($onlineip, 0, 1) == '0' || substr($onlineip, 0, 3) == '127' || isInRange($longip, 167772160, 184549375) || isInRange($longip, -1408237568, -1407188993) || isInRange($longip, -1062731776, -1062666241)) {
			cpmsg('insenz_intranet_not_allowed');
		}
	}

}

function isInRange($x, $min, $max) {
        return $x >= $min && $x <= $max;
}

function insenz_register($type) {
	global $insenz, $db, $tablepre, $_DCACHE, $boardurl, $timestamp, $authkey, $discuz_uid, $discuz_user, $response;

	checkip();
	if($type == 1) {
		@extract($insenz['profile']);
		foreach(array('username', 'password', 'name', 'idcard', 'email1', 'email2', 'qq', 'msn', 'tel1', 'tel2', 'tel3', 'mobile', 'fax1', 'fax2', 'fax3', 'country', 'province', 'city', 'address', 'postcode', 'alipay') AS $item) {
			$$item = stripslashes($$item);
		}
	} else {
		$username = $insenz['profile']['username'];
		$password = $insenz['profile']['password'];
	}

	$insenz['notify'] = is_array($insenz['notify']) ? $insenz['notify'] : array(2);
	$insenz['hardadstatus'] = is_array($insenz['hardadstatus']) ? $insenz['hardadstatus'] : array(1, 2, 3, 4, 5);

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}members");
	$members = $db->result($query, 0);
	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}members WHERE posts>0");
	$post_members = $db->result($query, 0);
	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}threads WHERE displayorder>='0'");
	$threads = $db->result($query, 0);
	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE invisible='0'");
	$posts = $db->result($query, 0);

	$date = getdate($timestamp);
	$yesterday_end = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
	$lastweek_start = $yesterday_end - 7*86400;

	$lastweek_pertopics = $lastweek_perposts = $forumstats = array();

	$query = $db->query("SELECT DISTINCT(fid) AS fid, COUNT(tid) AS topics FROM {$tablepre}threads WHERE dateline BETWEEN $lastweek_start AND $yesterday_end GROUP BY fid ORDER BY topics DESC");
	while($p = $db->fetch_array($query)) {
		$lastweek_pertopics[$p['fid']] = ceil($p['topics'] / 7);
	}

	$query = $db->query("SELECT DISTINCT(fid) AS fid, COUNT(pid) AS posts FROM {$tablepre}posts WHERE dateline BETWEEN $lastweek_start AND $yesterday_end GROUP BY fid ORDER BY posts DESC");
	while($p = $db->fetch_array($query)) {
		$lastweek_perposts[$p['fid']] = ceil($p['posts'] / 7);
	}

	$query = $db->query("SELECT f.fid, f.fup, f.type, f.name, f.threads, f.posts, f.displayorder, f.status, f.simple, ff.description, ff.redirect FROM {$tablepre}forums f LEFT JOIN {$tablepre}forumfields ff ON f.fid=ff.fid");
	while($forum = $db->fetch_array($query)) {
		$fid = $forum['fid'];
		unset($forum['fid']);
		$forum['lastweek_pertopics'] = intval($lastweek_pertopics[$fid]);
		$forum['lastweek_perposts'] = intval($lastweek_perposts[$fid]);
		$forumstats[$fid] = $forum;
	}

	$postdata = '<cmd id="register"><handle>'.insenz_convert($username).'</handle>
		<passwd>'.$password.'</passwd>';

	if($type == 1) {
		$postdata .= '<name>'.insenz_convert($name).'</name>
			<idcard>'.$idcard.'</idcard>
			<tel>'.$tel1.'-'.$tel2.'-'.$tel3.'</tel>
			<mobile>'.$mobile.'</mobile>
			<fax>'.$fax1.'-'.$fax2.'-'.$fax3.'</fax>
			<email>'.$email1.'</email>
			<email2>'.$email2.'</email2>
			<qq>'.$qq.'</qq>
			<msn>'.$msn.'</msn>
			<alipay>'.$alipay.'</alipay>
			<country>'.$country.'</country>
			<province>'.$province.'</province>
			<city>'.$city.'</city>
			<addr>'.insenz_convert($address).'</addr>
			<postcode>'.$postcode.'</postcode>';
	}

	$postdata .= '<url>'.$boardurl.'</url>
		<s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key>
		<bbname>'.insenz_convert($_DCACHE['settings']['bbname']).'</bbname>
		<members>'.$members.'</members>
		<post_members>'.$post_members.'</post_members>
		<topics>'.$threads.'</topics>
		<posts>'.$posts.'</posts>
		<softadstatus>'.intval($insenz['softadstatus']).'</softadstatus>
		<notify>'.implode(',', $insenz['notify']).'</notify>
		<autoextend>'.intval($insenz['autoextend']).'</autoextend>
		<hardadstatus>'.implode(',', $insenz['hardadstatus']).'</hardadstatus>
		<relatedadstatus>'.intval($insenz['relatedadstatus']).'</relatedadstatus>
		<virtualforumstatus>'.intval($insenz['virtualforumstatus']).'</virtualforumstatus>';

	foreach($forumstats AS $fid => $forum) {
		$postdata .= '<board>
			<board_id>'.$fid.'</board_id>
			<parent_id>'.$forum['fup'].'</parent_id>
			<board_type>'.$forum['type'].'</board_type>
			<name>'.insenz_convert($forum['name']).'</name>
			<topics>'.$forum['threads'].'</topics>
			<posts>'.$forum['posts'].'</posts>
			<lastweek_pertopics>'.$forum['lastweek_pertopics'].'</lastweek_pertopics>
			<lastweek_perposts>'.$forum['lastweek_perposts'].'</lastweek_perposts>
			<description>'.insenz_convert($forum['description']).'</description>
			<status>'.$forum['status'].'</status>
			<simple>'.$forum['simple'].'</simple>
			<redirect>'.insenz_convert($forum['redirect']).'</redirect>
			<displayorder>'.$forum['displayorder'].'</displayorder></board>';
	}

	$postdata .= '<type>'.($type == 1 ? 'register' : 'bind').'</type></cmd>';

	unset($insenz['siteid']);
	$response = insenz_request($postdata);
	if($response['status']) {
		insenz_alert($response['data']);
	} else {
		$response = $response['data'];
	}

	$status = $response['response'][0]['status'][0]['VALUE'];

	if($status == 0) {

		$insenz['authkey'] = $response['response'][0]['authkey'][0]['VALUE'];
		$insenz['siteid'] = $response['response'][0]['site_id'][0]['VALUE'];
		$insenz['uid'] = $discuz_uid;
		$insenz['username'] = $discuz_userss;
		insenz_updatesettings();
		unset($insenz['profile'], $insenz['step']);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
		require_once DISCUZ_ROOT.'./include/cache.func.php';
		updatecache('settings');
		insenz_cpmsg('insenz_register_succeed');

	} else {
		$response['reason'] = insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0);
		unset($insenz['authkey'], $insenz['siteid']);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
		insenz_alert('insenz_register_failed');

	}

}

function insenz_showsettings($do = '') {
	global $insenz, $db, $tablepre, $discuz_user, $timestamp, $lang;

	$type = array('basic' => $lang['menu_insenz_settings_basic'], 'softad' => $lang['menu_insenz_settings_softad'], 'hardad' => $lang['menu_insenz_settings_hardad'], 'relatedad' => $lang['menu_insenz_settings_relatedad'], 'virtualforum' => $lang['menu_insenz_settings_virtualforum']);

	if(!$do || $do == 'basic') {

		$insenz['notify'] = is_array($insenz['notify']) ? $insenz['notify'] : array();
		$insenz['notify'][2] = 1;
		$insenz_notify = bindec(intval($insenz['notify'][2]).intval($insenz['notify'][1]));

		$msgto = '';
		if(!empty($insenz['msgtoid'])) {
			$query = $db->query("SELECT username FROM {$tablepre}members WHERE uid='$insenz[msgtoid]'");
			$msgto = $db->result($query, 0);
		}
		$msgto = $msgto ? $msgto : $discuz_userss;

		$disabled = $insenz['notify'][1] ? 0 : 1;

		if(!$do) {
			echo '<tr class="category"><td colspan="2">'.$type['basic'].'</td></tr>';
		} else {
			showtype($type['basic'], 'top');
		}

		//showsetting('insenz_settings_domain', 'host', $insenz['host'], 'text');
		showsetting('insenz_settings_notify', array('notify', array($lang['insenz_settings_notify1'], $lang['insenz_settings_notify2']), array('onclick="this.form.msgto.disabled=this.checked?false:true;"', 'disabled')), $insenz_notify, 'mcheckbox');
		showsetting('insenz_settings_msgto', 'msgto', $msgto, 'text', '45%', $disabled);
		echo '</tbody><tbody>';

	}

	if(!$do || $do == 'softad') {

		$insenz['softadstatus'] = isset($insenz['softadstatus']) ? $insenz['softadstatus'] : 2;
		$softadstatus1 = $insenz['softadstatus'] ? 1 : 0;
		$softadstatus2 = $insenz['softadstatus'] == 2 ? 1 : 0;
		$readonly = $do && !empty($insenz['lastmodified']) && $timestamp - $insenz['lastmodified'] < 14 * 86400;
		$nextmodified = gmdate("$GLOBALS[dateformat] $GLOBALS[timeformat]", (empty($insenz['lastmodified']) || $insenz['lastmodified'] + 14 * 86400 < $timestamp ? $timestamp : $insenz['lastmodified'] + 14 * 86400) + $GLOBALS['timeoffset'] * 3600);

		if(!$do) {
			echo '<tr class="category"><td colspan="2">'.$type['softad'].'</td></tr>';
		} else {
			showtype($type['softad'], 'top');
		}

		showsetting('insenz_settings_softadstatus1', 'softadstatus1', $softadstatus1, 'radio', '', '', 1);
		showsetting('insenz_settings_softadstatus2', 'softadstatus2', $softadstatus2, 'radio');
		//showsetting('insenz_settings_autoextend', 'autoextend', $insenz['autoextend'], 'radio');
		showsetting('<b>'.$lang['insenz_settings_admins'].'</b><br><span class="smalltxt">'.$lang['insenz_settings_admins_comment'].$nextmodified.'</span>', 'admins', $insenz['admin_masks'] ? stripslashes(implode("\n", $insenz['admin_masks'])) : '', 'textarea', '45%', $readonly);
		showsetting('<b>'.$lang['insenz_settings_members'].'</b><br><span class="smalltxt">'.$lang['insenz_settings_members_comment'].$nextmodified.'</span>', 'members', $insenz['member_masks'] ? stripslashes(implode("\n", $insenz['member_masks'])) : '', 'textarea', '45%', $readonly);
		echo '</tbody><tbody>';

	}

	if(!$do || $do == 'hardad') {

		/* note
		$query = $db->query("SELECT COUNT(*) AS nums, type FROM {$tablepre}advertisements WHERE type IN ('headerbanner', 'thread', 'interthread', 'footerbanner') GROUP BY type");
		$ads = array();
		while($ad = $db->fetch_array($query)) {
			$ads[$ad['type']] = $ad['nums'];
		}
		*/

		$availableadvs = array(
			1 => $lang['insenz_settings_availableadvs1'],
			2 => $lang['insenz_settings_availableadvs2'],
			3 => $lang['insenz_settings_availableadvs3'],
			4 => $lang['insenz_settings_availableadvs4'],
			5 => $lang['insenz_settings_availableadvs5'],
			6 => $lang['insenz_settings_availableadvs6'],
			7 => $lang['insenz_settings_availableadvs7'],
			8 => $lang['insenz_settings_availableadvs8']
		);
		$insenz['availableadvs'] = is_array($insenz['availableadvs']) ? $insenz['availableadvs'] : array(1, 2, 3, 4, 5);
		$insenz['hardadstatus'] = is_array($insenz['hardadstatus']) ? $insenz['hardadstatus']: array(1, 2, 3, 4, 5);
		$insenz_availableadvs = array();
		foreach($insenz['availableadvs'] AS $ad) {
			$insenz_availableadvs[$ad] = $availableadvs[$ad];
		}

		if(!$do) {
			echo '<tr class="category"><td colspan="2">'.$type['hardad'].'</td></tr>';
		} else {
			showtype($type['hardad'], 'top');
		}

		$insenz_hardadstatus = '';
		for($i = count($insenz['availableadvs']); $i >= 1; $i--) {
			$insenz_hardadstatus .= in_array($i, $insenz['hardadstatus']) ? 1 : 0;
		}
		$insenz_hardadstatus = bindec($insenz_hardadstatus);

		showsetting('insenz_settings_hardadstatus', array('hardadstatus', $insenz_availableadvs), $insenz_hardadstatus, 'mcheckbox');

	}

	if(!$do || $do == 'relatedad') {

		$insenz['relatedadstatus'] = isset($insenz['relatedadstatus']) ? $insenz['relatedadstatus'] : 1;

		if(!$do) {
			echo '<tr class="category"><td colspan="2">'.$type['relatedad'].'</td></tr>';
		} else {
			showtype($type['relatedad'], 'top');
		}

		showsetting('insenz_settings_relatedadstatus', 'relatedadstatus', $insenz['relatedadstatus'], 'radio');

	}

	if(!$do || $do == 'virtualforum') {

		$insenz['virtualforumstatus'] = isset($insenz['virtualforumstatus']) ? $insenz['virtualforumstatus'] : 1;

		if(!$do) {
			echo '<tr class="category"><td colspan="2">'.$type['virtualforum'].'</td></tr>';
		} else {
			showtype($type['virtualforum'], 'top');
		}

		showsetting('insenz_settings_virtualforumstatus', 'virtualforumstatus', $insenz['virtualforumstatus'], 'radio');

	}

}

function insenz_updatesettings() {
	global $insenz, $response;

	$insenz['availableadvs'] = explode(',', $response['response'][0]['availableadvs'][0]['VALUE']);
	$insenz['hardadstatus'] = array_intersect(is_array($insenz['hardadstatus']) ? $insenz['hardadstatus'] : array(), $insenz['availableadvs']);
	if($insenz['relatedadstatus']) {
		$insenz['topicrelatedad'] = insenz_convert($response['response'][0]['topicrelatedad'][0]['VALUE'], 0);
		$insenz['traderelatedad'] = insenz_convert($response['response'][0]['traderelatedad'][0]['VALUE'], 0);
		$insenz['relatedtrades'] = insenz_convert($response['response'][0]['relatedtrades'][0]['VALUE'], 0);
	} else {
		$insenz['topicrelatedad'] = $insenz['traderelatedad'] = $insenz['relatedtrades'] = '';
	}
	$jsurl = $response['response'][0]['jsurl'][0]['VALUE'];
	$insenz['jsurl'] = $jsurl ? $jsurl : $insenz['jsurl'];
	if(isset($response['response'][0]['topicstatus'])) {
		$insenz['topicstatus'] = intval($response['response'][0]['topicstatus'][0]['VALUE']);
	}
	$insenz['status'] = $insenz['softadstatus'] || $insenz['hardadstatus'] || $insenz['topicrelatedad'] || $insenz['traderelatedad'] || $insenz['topicstatus'];

}

function insenz_shownav($navs) {
	global $lang;
	$navs = isset($GLOBALS['lang'][$navs]) ? $GLOBALS['lang'][$navs] : $navs;
	shownav('<a href="admincp.php?action=insenz&operation=campaignlist&c_status=2">'.$lang['header_insenz'].'</a>&nbsp;&raquo;&nbsp;'.$navs);
}

function insenz_request($data, $return = TRUE, $fp = '') {
	global $insenz, $timestamp, $msglang;

	@include_once DISCUZ_ROOT.'./discuz_version.php';
	$authkey = !empty($insenz['authkey']) ? $insenz['authkey'] : 'Discuz!INSENZ';
	$t_hex = sprintf("%08x", $timestamp);
	$postdata = '<?xml version="1.0" encoding="UTF'.'-8"?><request insenz_version="'.INSENZ_VERSION.'" discuz_version="'.DISCUZ_VERSION.' - '.DISCUZ_RELEASE.'">'.$data.'</request>';
	$postdata = insenz_authcode($t_hex.md5($authkey.$postdata.$t_hex).$postdata, 'ENCODE', $authkey);

	if(!$fp && !$fp = @fsockopen($insenz['host'], 80)) {
		if(!$return) {
			return;
		} else {
			return array('status' => 1, 'data' => $msglang['insenz_connect_failed']);
		}
	}
	$insenz['siteid'] = intval($insenz['siteid']);
	@fwrite($fp, "POST http://$insenz[url]/discuz.php?s_id=$insenz[siteid] HTTP/1.0\r\n");
	@fwrite($fp, "Host: $insenz[host]\r\n");
	@fwrite($fp, "Content-Type: file\r\n");
	@fwrite($fp, "Content-Length: " . strlen($postdata) ."\r\n\r\n");
	@fwrite($fp, $postdata);

	if(!$return) {
		@fclose($fp);
		return;
	}

	$res = '';
	$isheader = 1;
	while(!feof($fp)) {
		$buffer = @fgets($fp, 1024);
		if(!$isheader) {
			$res .= $buffer;
		} elseif(trim($buffer) == '') {
			$isheader = 0;
		}
	}

	@fclose($fp);

	if(empty($res)) {
		return array('status' => 1, 'data' => $msglang['insenz_connect_failed']);
	}

	if(!$response = insenz_authcode($res, 'DECODE', $authkey)) {
		return array('status' => 1, 'data' => $msglang['insenz_transport_failed']);
	}

	$checkKey = substr($response, 0, 40);
	$response = substr($response, 40);
	$t_hex = substr($checkKey, 0, 8);
	$t = base_convert($t_hex, 16, 10);

	if(abs($timestamp - $t) > 1200) {
		return array('status' => 1, 'data' => $msglang['insenz_connect_timeout']);
	} elseif($checkKey != $t_hex.md5($authkey.$response.$t_hex)) {
		return array('status' => 1, 'data' => 'Invalid Key or Data');
	}

	require_once DISCUZ_ROOT.'./include/xmlparser.class.php';

	$xmlparse = new XMLParser;
	$xmlparseddata = $xmlparse->getXMLTree($response);

	if(!is_array($xmlparseddata) || !is_array($xmlparseddata['response'])) {
		return array('status' => 1, 'data' => $msglang['insenz_transport_failed']);
	}

	return array('status' => 0, 'data' => $xmlparseddata);

}

function insenz_alert($message, $focusobj = '') {
	extract($GLOBALS, EXTR_SKIP);
	eval("\$message = \"".(isset($msglang[$message]) ? $msglang[$message] : $message)."\";");
	echo '<script type="text/javascript">alert(\''.str_replace('\'', '\\\'', $message).'\');'.($focusobj ? 'parent.document.form1.'.$focusobj.'.focus();' : '').'</script>';
	exit;
}

function insenz_cpmsg($message, $extra = '') {
	extract($GLOBALS, EXTR_SKIP);
	eval("\$message = \"".(isset($msglang[$message]) ? $msglang[$message] : $message)."\";");

	$url_forward = 'admincp.php?action=insenz'.$extra;
	$message .= "<br /><br /><br /><a href=\"$url_forward\">$lang[message_redirect]</a>";
	$url_forward = transsid($url_forward);

	echo '<script type="text/javascript">parent.setTimeout("redirect(\''.$url_forward.'\');", 2000);parent.$("insenz_body").innerHTML = \'<br /><br /><br /><br /><br /><br /><table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder"><tr class="header"><td>'.$lang['discuz_message'].'</td></tr><tr><td class="altbg2"><br /><br /><div align="center">'.$message.'</div><br /><br /></td></tr></table><br /><br /><br />\';</script>';

	dexit();
}

?>