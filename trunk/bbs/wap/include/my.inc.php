<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: my.inc.php 11169 2007-11-08 06:30:00Z tiger $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

if(empty($discuz_uid)) {
	wapmsg('not_loggedin');
}

$uid = !empty($uid) ? intval($uid) : $discuz_uid;
$username = !empty($username) ? dhtmlspecialchars($username) : '';
$usernameadd = $uid ? "m.uid='$uid'" : "m.username='$username'";

if(empty($do)) {

	$member = $db->fetch_first("SELECT m.*, mf.* FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		WHERE $usernameadd LIMIT 1");

	if(!$member) {
		wapmsg('my_nonexistence');
	}

	if($member['gender'] == '1') {
		$member['gender'] = $lang['my_male'];
	} elseif($member['gender'] == '2') {
		$member['gender'] = $lang['my_female'];
	} else {
		$member['gender'] = $lang['my_secrecy'];
	}

	echo "<p>$lang[my]<br /><br />".
		"$lang[my_uid] $member[uid]<br />".
		"$lang[my_username] $member[username]<br />".
		"$lang[my_gender] $member[gender]<br />".
		($member['bday'] != '0000-00-00' ? "$lang[my_bday] $member[bday]<br />" : '').
		($member['location'] ? "$lang[my_location] $member[location]<br />" : '').
		($member['bio'] ? "$lang[my_bio] $member[bio]<br /><br />" : '');

	if($uid == $discuz_uid) {
		echo 	"<a href=\"index.php?action=myphone\">$lang[my_phone]</a><br />".
			"<a href=\"index.php?action=my&amp;do=fav\">$lang[my_favorites]</a><br />".
			"<a href=\"index.php?action=pm\">$lang[pm]</a></p>";
	} else {
		echo "<br /><a href=\"index.php?action=pm&amp;do=send&amp;pmuid=$member[uid]\">$lang[pm_send]</a></p>";
	}

} else {

	if($do == 'fav') {

		if(!empty($favid)) {
			$selectid = $type == 'thread' ? 'tid' : 'fid';
			if($db->result_first("SELECT $selectid FROM {$tablepre}favorites WHERE uid='$discuz_uid' AND $selectid='$favid' LIMIT 1")) {
				wapmsg('fav_existence');
			} else {
				$db->query("INSERT INTO {$tablepre}favorites (uid, $selectid)
					VALUES ('$discuz_uid', '$favid')");
				wapmsg('fav_add_succeed');
			}
		} else {
			echo "<p>$lang[my_threads]<br />";
			$query = $db->query("SELECT m.*, t.subject FROM {$tablepre}mythreads m, {$tablepre}threads t
					WHERE m.uid = '$discuz_uid' AND m.tid = t.tid ORDER BY m.dateline DESC LIMIT 0, 3");
			while($mythread = $db->fetch_array($query)) {
				echo "<a href=\"index.php?action=thread&amp;tid=$mythread[tid]\">".cutstr($mythread['subject'], 15)."</a><br />";
			}

			echo "<br />$lang[my_replies]<br />";
			$query = $db->query("SELECT m.*, t.subject FROM {$tablepre}myposts m, {$tablepre}threads t
					WHERE m.uid = '$discuz_uid' AND m.tid = t.tid ORDER BY m.dateline DESC LIMIT 0, 3");
			while($mypost = $db->fetch_array($query)) {
				echo "<a href=\"index.php?action=thread&amp;tid=$mypost[tid]\">".cutstr($mypost['subject'], 15)."</a><br />";
			}

			echo "<br />$lang[my_fav_thread]<br />";
			$query = $db->query("SELECT t.tid, t.subject FROM {$tablepre}favorites fav, {$tablepre}threads t
					WHERE fav.tid=t.tid AND t.displayorder>='0' AND fav.uid='$discuz_uid' ORDER BY t.lastpost DESC LIMIT 0, 3");
			while($favthread = $db->fetch_array($query)) {
				echo "<a href=\"index.php?action=thread&amp;tid=$favthread[tid]\">".cutstr($favthread['subject'], 24)."</a><br />";
			}

			echo "<br />$lang[my_fav_forum]<br />";
			$query = $db->query("SELECT f.fid, f.name FROM {$tablepre}favorites fav, {$tablepre}forums f WHERE fav.uid='$discuz_uid' AND fav.fid=f.fid ORDER BY f.displayorder DESC LIMIT 0, 3");
			while($favforum = $db->fetch_array($query)) {
				echo "<a href=\"index.php?action=forum&amp;fid=$favforum[fid]\">$favforum[name]</a><br />";
			}
			echo '</p>';
		}
	}
}

?>