<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: announcement.php 10115 2007-08-24 00:58:08Z cnteacher $
*/

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

$discuz_action = 21;


$id = intval($id);

$total = 0;
$query = $db->query("SELECT id, starttime, endtime, type, groups FROM {$tablepre}announcements WHERE type!=2 AND starttime<='$timestamp' ORDER BY displayorder, starttime DESC, id DESC");
while($announce = $db->fetch_array($query)) {
	if(!$announce['groups'] || in_array($groupid, explode(',', $announce['groups']))) {
		$total++;
		if(isset($id) && $announce['id'] == $id) {
			$page = ceil($total / $ppp);
		}
	}
}

$page = max(1, intval($page));
$start_limit = ($page - 1) * $ppp;

$multipage = multi($total, $ppp, $page, 'announcement.php');

$announcelist = array();
$query = $db->query("SELECT * FROM {$tablepre}announcements WHERE type!=2 AND starttime<='$timestamp' AND (endtime='0' OR endtime>'$timestamp') ORDER BY displayorder, starttime DESC, id DESC LIMIT $start_limit, $ppp");

if((!empty($id) && !$page) || !$db->num_rows($query)) {
	showmessage('announcement_nonexistence');
}

while($announce = $db->fetch_array($query)) {
	if(!$announce['groups'] || in_array($groupid, explode(',', $announce['groups']))) {
		$announce['authorenc'] = rawurlencode($announce['author']);
		$announce['starttime'] = gmdate($dateformat, $announce['starttime'] + $timeoffset * 3600);
		$announce['endtime'] = $announce['endtime'] ? gmdate($dateformat, $announce['endtime'] + $timeoffset * 3600) : '';
		$announce['message'] = $announce['type'] == 1 ? "[url]{$announce[message]}[/url]" : $announce['message'];
		$announce['message'] = nl2br(discuzcode($announce['message'], 0, 0, 1, 1, 1, 1, 1));
		$announcelist[] = $announce;
	}
}

include template('announcement');

?>