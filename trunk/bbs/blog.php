<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: blog.php 11169 2007-11-08 06:30:00Z tiger $
*/

define('CURSCRIPT', 'blog');

require_once './include/common.inc.php';

$spacestatus or dheader("location: {$boardurl}");

require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
require_once DISCUZ_ROOT.'./include/space.func.php';
include_once language('spaces');

$discuz_action = 151;

$blogtopic = $db->fetch_first("SELECT t.fid, t.special, t.price, t.attachment, t.authorid, t.author, t.dateline, t.replies, t.closed, m.groupid, p.pid, p.subject, p.message, p.htmlon, p.smileyoff, p.bbcodeoff, p.attachment, p.rate, p.ratetimes
	FROM {$tablepre}threads t
	INNER JOIN {$tablepre}posts p ON p.tid=t.tid AND p.invisible='0'
	LEFT JOIN {$tablepre}members m ON m.uid=t.authorid
	WHERE t.tid='$tid' AND t.blog='1' AND t.displayorder>='0' ORDER BY p.dateline LIMIT 1");
$blogtopic or showmessage('blog_topic_nonexistence');

$uid = $blogtopic['authorid'];
$member = $db->fetch_first("SELECT m.*, mf.*
	FROM {$tablepre}members m
	LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
	WHERE m.uid='$uid'");
$member['xspacestatus'] && $supe['status'] && dheader("location: $supe[siteurl]?uid-$uid");

$page = max(1, intval($page));
$start_limit = ($page - 1) * $ppp;


$spacesettings = getspacesettings($member['uid']);

$menulist = array();
$modulelist = explode('][', ']'.str_replace("\t", '', $spacesettings['layout']).'[');
foreach($modulelist as $module) {
	if(array_key_exists($module, $listmodule)) {
		$menulist[$listmodule[$module]] = $module;
	}
}
ksort($menulist);

$showsettings = str_pad(decbin($showsettings), 3, '0', STR_PAD_LEFT);
$customshow = $discuz_uid ? str_pad(base_convert($customshow, 10, 3), 3, '0', STR_PAD_LEFT) : '222';
$showimages = $customshow{2} == 2 ? $showsettings{2} : $customshow{2};

$attachtags = array();
$attachpids = $viewattachlist = 0;
$username = $blogtopic['author'];

$multipage = $multipage = spacemulti($blogtopic['replies'], $ppp, $page, "blog.php?tid=$tid");

$usesigcheck = $discuz_uid && $sigstatus ? 'checked="checked"' : '';
$allowpostreply = (!$blogtopic['closed'] || $forum['ismoderator']) && ((!$forum['replyperm'] && $allowreply) || ($forum['replyperm'] && forumperm($forum['replyperm'])) || $forum['allowreply']);

$forum['allowbbcode'] = $forum['allowbbcode'] ? ($_DCACHE['usergroups'][$blogtopic['groupid']]['allowcusbbcode'] ? 2 : 1) : 0;
$blogtopic['message'] = discuzcode($blogtopic['message'], $blogtopic['smileyoff'], $blogtopic['bbcodeoff'], $blogtopic['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], $forum['allowimgcode'], $forum['allowhtml'], ($forum['jammer'] && $blogtopic['authorid'] != $discuz_uid ? 1 : 0), 0, 0, $forum['allowmediacode'], $blogtopic['pid']);
$videoopen && $blogtopic['message'] = videocode($blogtopic['message'], $tid, $blogtopic['pid']);
$blogtopic['karma'] = karmaimg($blogtopic['rate'], $blogtopic['ratetimes']);
$blogtopic['dateline'] = gmdate("$dateformat $timeformat", $blogtopic['dateline'] + $timeoffset * 3600);
$titleextra = ' - '.$blogtopic['subject'];
$blogtopic['attachments'] = array();

if($blogtopic['attachment'] && (!empty($forum['allowgetattach']) || ($allowgetattach && !$forum['getattachperm']) || forumperm($forum['getattachperm']))) {

	require_once DISCUZ_ROOT.'./include/attachment.func.php';

	$blogtopic['attachment'] = 0;
	if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $blogtopic['message'], $matchaids)) {
		$attachtags[$blogtopic['pid']] = $matchaids[1];
	}
	$tmp[$blogtopic['pid']] = $blogtopic;
	parseattach($blogtopic['pid'], $attachtags, $tmp);
	$blogtopic = $tmp[$blogtopic['pid']];

}

if($tagstatus) {
	$query = $db->query("SELECT tagname FROM {$tablepre}threadtags WHERE tid='$tid'");
	$blogtopic['tags'] = '';
	while($tags = $db->fetch_array($query)) {
		$metakeywords .= $tags['tagname'].',';
		$blogtopic['tags'] .= '<a href="tag.php?name='.rawurlencode($tags['tagname']).'" target="_blank"><font color=red>'.$tags['tagname'].'</font></a> ';
	}
}

$commentlist = array();
if($blogtopic['replies']) {
	$query = $db->query("SELECT p.*, m.username, m.groupid, m.regdate, m.posts, m.credits
		FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		WHERE p.tid='$tid' AND p.invisible='0' AND p.pid<>'$blogtopic[pid]'
		ORDER BY p.dateline
		LIMIT $start_limit, $ppp");

	while($post = $db->fetch_array($query)) {

		if($post['username']) {
			if($_DCACHE['usergroups'][$post['groupid']]['userstatusby'] == 1) {
				$post['authortitle'] = strip_tags($_DCACHE['usergroups'][$post['groupid']]['grouptitle']);
				$post['stars'] = $_DCACHE['usergroups'][$post['groupid']]['stars'];
			} elseif($_DCACHE['usergroups'][$post['groupid']]['userstatusby'] == 2) {
				foreach($_DCACHE['ranks'] as $rank) {
					if($post['posts'] > $rank['postshigher']) {
						$post['authortitle'] = strip_tags($rank['ranktitle']);
						$post['stars'] = $rank['stars'];
						break;
					}
				}
			}

			$post['regdate'] = gmdate($dateformat, $post['regdate'] + $timeoffset * 3600);

		} else {

			if(!$post['authorid']) {
				$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
			}
			$post['posts'] = $post['credits'] = $post['regdate'] = 'N/A';
		}

		$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $showimages ? 1 : 0), $forum['allowhtml'], ($forum['jammer'] && $post['authorid'] != $discuz_uid ? 1 : 0), 0, $post['authorid'], $post['pid']);
		$videoopen && $post['message'] = videocode($post['message'], $tid, $post['pid']);

		$post['attachments'] = array();
		if($post['attachment'] && (!empty($forum['allowgetattach']) || ($allowgetattach && !$forum['getattachperm']) || forumperm($forum['getattachperm']))) {
			$attachpids .= ",$post[pid]";
			$post['attachment'] = 0;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
				$attachtags[$post['pid']] = $matchaids[1];
			}
		}
		$commentlist[$post['pid']] = $post;
	}

	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $commentlist);
	}
}

include template('space');

?>