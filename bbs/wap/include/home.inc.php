<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home.inc.php 11169 2007-11-08 06:30:00Z tiger $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

$newthreads = round(($timestamp - $lastvisit + 600) / 1000) * 1000;
$onlinemem = $onlineguest = $forumnum = 0;

echo "<p>$bbname<br />\n";

if($discuz_uid && $newpm) {
	if($newpm = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' AND new='1'")) {
		echo "<a href=\"index.php?action=pm&amp;do=list&amp;unread=yes\">$newpm $lang[home_newpm]</a><br />\n";
	} else {
		$db->query("UPDATE {$tablepre}members SET newpm='0' WHERE uid='$discuz_uid'");
	}
}

if($discuz_uid) {
	echo (!empty($allowsearch) ? "<br /><a href=\"index.php?action=search&amp;srchfrom=$newthreads&amp;do=submit\">$lang[home_newthreads]</a><br /><a href=\"index.php?action=search\">$lang[search]</a><br />" : '').
		"<a href=\"index.php?action=my&amp;do=fav\">$lang[my_favorites]</a><br />".
		"<a href=\"index.php?action=my\">$lang[my]</a><br />";
}

echo 	"<br />$lang[home_forums]<br />";

$sql = !empty($accessmasks) ?
			"SELECT f.fid, f.name, ff.viewperm, a.allowview FROM {$tablepre}forums f
				LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid
				LEFT JOIN {$tablepre}access a ON a.uid='$discuz_uid' AND a.fid=f.fid
				WHERE f.status>0 AND f.type='forum' ORDER BY f.displayorder"
			: "SELECT f.fid, f.name, ff.viewperm FROM {$tablepre}forums f
				LEFT JOIN {$tablepre}forumfields ff USING(fid)
				WHERE f.status>0 AND f.type='forum' ORDER BY f.displayorder";

$query = $db->query($sql);
while($forum = $db->fetch_array($query)) {
	if(forum($forum) && (!$forum['viewperm'] || (strexists("\t".trim($forum['viewperm'])."\t", "\t".trim($groupid)."\t") && $forum['viewperm']))) {
		echo "<a href=\"index.php?action=forum&amp;fid=$forum[fid]\">".strip_tags($forum['name'])."</a><br/>";
	}
	if($forumnum ++ >= 10) {
		break;
	}
}

echo ($forumnum > 10 ? "<a href=\"index.php?action=forum\">$lang[more]</a><br /><br />" : '').
	"$lang[home_tools]<br />".
	"<a href=\"index.php?action=stats\">$lang[stats]</a><br />".
	"<a href=\"index.php?action=goto\">$lang[goto]</a>".
	(!empty($allowsearch) ? "<br /><br /><input type=\"text\" name=\"srchtxt\" value=\"\" size=\"8\" emptyok=\"true\" /> ".
	"<anchor title=\"submit\">$lang[search]\n".
	"<go method=\"post\" href=\"index.php?action=search&amp;do=submit\" />\n".
	"<postfield name=\"srchtxt\" value=\"$(srchtxt)\" /></anchor>" : '');

$query = $db->query("SELECT uid, COUNT(*) AS count FROM {$tablepre}sessions GROUP BY uid='0'");
while($online = $db->fetch_array($query)) {
	$online['uid'] ? $onlinemem = $online['count'] : $onlineguest = $online['count'];
}

echo "<br /><br />$lang[home_online]".($onlinemem + $onlineguest)."({$onlinemem} $lang[home_members])</p>\n";

?>