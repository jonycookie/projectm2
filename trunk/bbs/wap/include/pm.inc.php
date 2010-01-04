<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: pm.inc.php 16713 2008-11-17 03:25:20Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

$discuz_action = 197;

if(!$discuz_uid) {
	wapmsg('not_loggedin');
}

if(empty($do)) {

	$num_read = $num_unread = 0;
	$query = $db->query("SELECT COUNT(*) AS num, new FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' GROUP BY new='0'");
	while($pm = $db->fetch_array($query)) {
		$pm['new'] ? $num_unread = $pm['num'] : $num_read = $pm['num'];
	}

	echo "<p><a href=\"index.php?action=pm&amp;do=list&amp;unread=yes\">$lang[pm_unread]($num_unread)</a><br />\n".
		"<a href=\"index.php?action=pm&amp;do=list\">$lang[pm_all](".($num_read + $num_unread).")</a><br />\n".
		"<a href=\"index.php?action=pm&amp;do=send\">$lang[pm_send]</a><br />\n".
		"<a href=\"index.php?action=pm&amp;do=delete\">$lang[pm_delete_all]</a></p>";

} else {

	if($do == 'list') {

		echo "<p>$lang[pm_list]<br />\n";

		$unreadadd = empty($unread) ? '' : 'AND new>\'0\'';
		$pageadd = empty($unread) ? '' : '&amp;unread=yes';
		$page = max(1, intval($page));
		$start_limit = $number = ($page - 1) * $waptpp;

		if(!($totalpms = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' $unreadadd"))) {
			wapmsg('pm_nonexistence');
		}

		$query = $db->query("SELECT pmid, new, msgfrom, subject, dateline FROM {$tablepre}pms
			WHERE msgtoid='$discuz_uid' AND folder='inbox' $unreadadd
			ORDER BY dateline DESC
			LIMIT $start_limit, $waptpp");
		while($pm = $db->fetch_array($query)) {
			echo "<a href=\"index.php?action=pm&amp;do=view&amp;pmid=$pm[pmid]\">#".++$number.' '.(empty($unread) && $pm['new'] ? "($lang[unread])" : '').cutstr($pm['subject'], 30)."</a><br />\n".
				" <small>".gmdate("$wapdateformat $timeformat", $pm['dateline'] + $timeoffset * 3600)."<br />\n".
				" $pm[msgfrom]</small><br />\n";
		}

		echo wapmulti($totalpms, $waptpp, $page, "index.php?action=pm&amp;do=$do$pageadd");
		echo "<br /><a href=\"index.php?action=pm&amp;do=send\">$lang[pm_send]</a>\n";

	} elseif($do == 'view') {

		$pm = $db->fetch_first("SELECT * FROM {$tablepre}pms WHERE pmid='$pmid' AND msgtoid='$discuz_uid' AND folder='inbox'");
		if(!$pm) {
			wapmsg('pm_nonexistence');
		}

		echo "<p>$lang[thread]$pm[subject]<br />\n".
			$lang['from'].$pm['msgfrom']."<br />\n".
			$lang['dateline'].gmdate("$wapdateformat $timeformat", $pm['dateline'] + $timeoffset * 3600)."<br />\n".
			"<br />".nl2br(dhtmlspecialchars(trim($pm['message'])))."<br /><br />\n".
			"<a href=\"index.php?action=pm&amp;do=send&amp;pmid=$pmid\">$lang[reply]</a>\n".
			"<a href=\"index.php?action=pm&amp;do=delete&amp;pmid=$pmid\">$lang[delete]</a><br /><br />\n".
		 	"<a href=\"index.php?action=pm&amp;do=list\">$lang[pm_all]</a>";
		$db->query("UPDATE {$tablepre}pms SET new='0' WHERE pmid='$pmid'");

	} elseif($do == 'send') {

		if(empty($msgto)) {

			if(!empty($pmid)) {
				$pm = $db->fetch_first("SELECT msgfrom, subject FROM {$tablepre}pms WHERE pmid='$pmid' AND msgtoid='$discuz_uid' AND folder='inbox'");
				$pm['subject'] = 'Re: '.$pm['subject'];
			} else {
				$pm = array('msgfrom' => '', 'subject' => '');
			}

			echo "<p>$lang[pm_to]:<input type=\"text\" name=\"msgto\" value=\"$pm[msgfrom]\" maxlength=\"15\" format=\"M*m\" /><br />\n".
				"$lang[subject]:<input type=\"text\" name=\"subject\" value=\"$pm[subject]\" maxlength=\"70\" format=\"M*m\" /><br />\n".
				"$lang[message]:<input type=\"text\" name=\"message\" value=\"\" format=\"M*m\" /><br />\n".
				"<anchor title=\"$lang[submit]\">$lang[submit]".
				"<go method=\"post\" href=\"index.php?action=pm&amp;do=send&amp;sid=$sid\">\n".
				"<postfield name=\"msgto\" value=\"$(msgto)\" />\n".
				"<postfield name=\"subject\" value=\"$(subject)\" />\n".
				"<postfield name=\"message\" value=\"$(message)\" />\n".
				"<postfield name=\"formhash\" value=\"".formhash()."\" />\n".
				"</go></anchor>\n";

		} else {

			$floodctrl = $floodctrl * 2;
			if($floodctrl && !$disablepostctrl && $timestamp - $lastpost < $floodctrl) {
				wapmsg('pm_flood_ctrl');
			}

			if($formhash != formhash()) {
				wapmsg('wap_submit_invalid');
			}

			$msgto = wapconvert($msgto);
				
			$member = $db->fetch_first("SELECT m.uid AS msgtoid, mf.ignorepm FROM {$tablepre}members m
				LEFT JOIN {$tablepre}memberfields mf USING (uid)
				WHERE username='$msgto'");
			if(!$member) {
				wapmsg('pm_send_nonexistence');
			}
			if(preg_match("/(^{ALL}$|(,|^)\s*".preg_quote($discuz_user, '/')."\s*(,|$))/i", $member['ignorepm'])) {
				wapmsg('pm_send_ignore');
			}
			if(empty($subject) || empty($message)) {
				wapmsg('pm_sm_isnull');
			}

			$subject = wapconvert($subject);
			$message = wapconvert($message);
			
			$subject = dhtmlspecialchars(cutstr(trim($subject), 75));
			$db->query("INSERT INTO {$tablepre}pms (msgfrom, msgfromid, msgtoid, folder, new, subject, dateline, message)
				VALUES('$discuz_user', '$discuz_uid', '$member[msgtoid]', 'inbox', '1', '$subject', '$timestamp', '$message')");
			$db->query("UPDATE {$tablepre}members SET newpm='1' WHERE uid='$member[msgtoid]'", 'UNBUFFERED');

			if($floodctrl) {
				$db->query("UPDATE {$tablepre}members SET lastpost='$timestamp' WHERE uid='$discuz_uid'");
			}

			wapmsg('pm_send_succeed', array('title' => 'pm_home', 'link' => "index.php?action=pm"));

		}

	} elseif($do == 'delete') {

		if(!empty($pmid)) {
			$db->query("DELETE FROM {$tablepre}pms WHERE pmid='$pmid' AND msgtoid='$discuz_uid' AND folder='inbox'");
			wapmsg('pm_delete_succeed');
		} else {
			if(empty($confirm)) {
				echo "<p><a href=\"index.php?action=pm&amp;do=delete&amp;confirm=yes\">$lang[pm_delete_confirm]</a>";
			} else {
				$db->query("DELETE FROM {$tablepre}pms WHERE new='0' AND msgtoid='$discuz_uid' AND folder='inbox'");
				wapmsg('pm_delete_succeed');
			}
		}

	}

	echo "<br /><br /><a href=\"index.php?action=pm\">$lang[pm_home]</a></p>\n";

}

?>