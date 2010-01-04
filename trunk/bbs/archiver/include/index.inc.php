<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: index.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once './include/header.inc.php';

?>
<div id="nav"><a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a></div>
<h1><a href="<?=$fullversion['link']?>" target="_blank"><?=$lang['full_version']?>: <?=$fullversion['title']?></a></h1>
<?

$forums = $subforums = array();
$categories = array(0 => array('fid' => 0, 'name' => $_DCACHE['settings']['bbname']));

foreach($_DCACHE['forums'] as $forum) {
	if(forumperm($forum['viewperm'])) {
		if($forum['type'] == 'group') {
			$categories[] = $forum;
		} else {
			$forum['type'] == 'sub' ? $subforums[$forum['fup']][] = $forum : $forums[$forum['fup']][] = $forum;
		}
	 }
}

echo "<ul class=\"archiver_forumlist\">\n";

foreach($categories as $category) {
	if(isset($forums[$category['fid']])) {
		echo "<li><h3>$category[name]</h3><ul>\n";
		foreach($forums[$category[fid]] as $forum) {
			echo "<li><a href=\"archiver/{$qm}fid-{$forum[fid]}.html\">$forum[name]</a>\n";
			if(isset($subforums[$forum['fid']])) {
				echo "<ul>\n";
				foreach($subforums[$forum['fid']] as $subforum) {
					echo "<li><a href=\"archiver/{$qm}fid-$subforum[fid].html\">$subforum[name]</a></li>\n";
				}
				echo "</ul></li>\n";
			}
		}
		echo "</li></ul>\n";
	}
}

echo "</ul>\n";

?>