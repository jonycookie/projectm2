<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: space.func.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$modulesettings = array(
	'userinfo'	=> array('1', 0, 1),
	'calendar'	=> array('1', 0),
	'myblogs'	=> array('', 1),
	'mythreads'	=> array('', 1),
	'myreplies'	=> array('', 1),
	'myrewards'	=> array('', 1),
	'mytrades'	=> array('', 1),
	'myvideos'	=> array('1', 2),
	'mycounters'	=> array('1', 2),
	'myfriends'	=> array('1', 2),
	'myfavforums'	=> array('1', 2),
	'myfavthreads'	=> array('1', 2)
);

$listmodule = array(
	'myblogs' => 1,
	'mythreads' => 2,
	'myreplies' => 3,
	'myrewards' => 4,
	'mytrades' => 5,
	'myvideos' => 6,
	'myfavforums' => 7,
	'myfavthreads' => 8
);

function getspacesettings($uid) {
	global $db, $tablepre, $discuz_uid;

	if($spacesettings = $db->fetch_first("SELECT * FROM {$tablepre}memberspaces WHERE uid='$uid'")) {
		$spacesettings['style'] = empty($spacesettings['style']) ? 'default' : str_replace('/', '', $spacesettings['style']);
		if(!file_exists(DISCUZ_ROOT.'./mspace/'.$spacesettings['style'].'/style.ini')) {
			$spacesettings['style'] = 'default';
		}
	} else {
		$spacesettings['style'] = 'default';
		$spacesettings['layout'] = "[userinfo][calendar][myreplies][myfavforums]\t[myblogs][mythreads]\t";
		$spacesettings['side'] = 1;
		$db->query("INSERT INTO {$tablepre}memberspaces (uid, style, description, layout, side) VALUES ('$uid', '$spacesettings[style]', '', '$spacesettings[layout]', '$spacesettings[side]')");
	}

	return $spacesettings;
}

function spacecutstr($str, $length) {
	global $_DCACHE;
	include_once DISCUZ_ROOT.'./forumdata/cache/cache_post.php';
	$bbcodes = 'b|i|u|color|size|font|align|list|indent|url|email|code|free|table|tr|td|img|swf|payto|float'.($_DCACHE['bbcodes_display'] ? '|'.implode('|', array_keys($_DCACHE['bbcodes_display'])) : '');
	$str = dhtmlspecialchars(cutstr(strip_tags(preg_replace(array(
			"/\[hide=?\d*\](.+?)\[\/hide\]/is",
			"/\[quote](.*)\[\/quote]/siU",
			"/\[($bbcodes)=?.*\]/iU",
			"/\[\/($bbcodes)\]/i",
			"/\[attach\](\d+)\[\/attach\]/i",
			"/\[media=(\w{1,4}),(\d{1,4}),(\d{1,4}),(\d)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/i",
		), array(
			'',
			'',
			'',
			'',
			'',
			"\\5"
		), $str)), $length));
	$find = array("/http:\/\/[a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+?\.(jpg|gif|png|bmp)/is", "/(\n|\r|\r\n){2,}/", "/\s{2,}/");
	$replace = array("<img onload=\"if(this.width>320) {this.resized=true;this.width=320;}\" src=\"\\0\">", "\r\n", '');
	$str = preg_replace($find, $replace, $str);
	return trim(nl2br($str));
}

function updatespacecache($uid, $module, $list = FALSE) {
	global $_DCOOKIE, $db, $mod, $tablepre, $timestamp, $tpp, $page, $multipage, $starttime, $endtime, $spacedata, $lastvisit, $videoopen, $tradetypeid;

	if(!file_exists(DISCUZ_ROOT.'./forumdata/cache/cache_spacesettings.php')) {
		require_once DISCUZ_ROOT.'./include/cache.func.php';
		updatespacesettings();
	}
	require DISCUZ_ROOT.'./forumdata/cache/cache_spacesettings.php';

	if($list) {
		$tpp = $mod != 'mytrades' ? $tpp : 15;
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;
		$parms['items'] = "$start_limit, $tpp";
	} else {
		$parms['items'] = intval($spacedata['limit'.$module]);
	}
	$parms['list'] = $list;
	$parms['conditions'] = $parms['extraquery'] = '';
	$parms['cols'] = '*';

	$user_func = 'module_'.$module;
	$user_func($parms);

	$tids = $datalist = array();
	$query = $db->query("SELECT $parms[cols] FROM {$tablepre}$parms[table] $parms[conditions] LIMIT $parms[items]");

	while($data = $db->fetch_array($query)) {
		if(!empty($data['message'])) {
			$data['message'] = spacecutstr($data['message'], $spacedata['textlength']);
			$videoopen && $data['message'] = videocode($data['message'], $data['tid'], $data['pid']);
		}
		if($data['tid'] && $lastvisit < $data['lastpost'] && (empty($_DCOOKIE['oldtopics']) || strpos($_DCOOKIE['oldtopics'], 'D'.$data['tid'].'D') === FALSE)) {
			$data['subject'] .= ' <a href="redirect.php?tid='.$data['tid'].'&amp;goto=newpost#newpost" target="_blank"><img src="'.IMGDIR.'/firstnew.gif" border="0" alt="" /></a>';
		}
		if($parms['extraquery']) {
			$tids[] = $data['tid'];
			$datalist[$data['tid']] = $data;
		} else {
			$datalist[] = $data;
		}
	}

	if($tids) {
		$query = $db->query($parms['extraquery'].'('.implodeids($tids).')');
		while($data = $db->fetch_array($query)) {
			$datalist[$data['tid']] = array_merge($datalist[$data['tid']], $data);
		}
	}

	if(!$list) {
		$db->query("REPLACE INTO {$tablepre}spacecaches (uid, variable, value, expiration) VALUES ('$uid', '$module', '".addslashes(serialize($datalist))."', '".($timestamp + $spacedata['cachelife'])."')");
	} else {
		$num = $db->result_first("SELECT count(*) FROM {$tablepre}$parms[table] $parms[conditions]");
		$module = empty($parms['pagemodule']) ? $module : $parms['pagemodule'];
		$multipage = spacemulti($num, $tpp, $page, "space.php?uid=$uid&amp;mod=$module".($starttime ? "&amp;starttime=$starttime" : '').($endtime ? "&amp;endtime=$endtime" : '').(isset($tradetypeid) ? "&amp;tradetypeid=$tradetypeid" : ''));
	}
	return $datalist;
}

function spacemulti($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $simple = 0, $onclick = '') {
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
	$onclick = $onclick ? ' onclick="'.$onclick.'(event)"' : '';
	if($num > $perpage) {
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1" class="p_redirect"'.$onclick.'>|&lsaquo;</a>' : '').
			($curpage > 1 && !$simple ? '<a href="'.$mpurl.'page='.($curpage - 1).'" class="p_redirect">&lsaquo;&lsaquo;</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<a class="p_curpage">'.$i.'</a>' :
				'<a href="'.$mpurl.'page='.$i.'" class="p_num"'.$onclick.'>'.$i.'</a>';
		}

		$multipage .= ($curpage < $pages && !$simple ? '<a href="'.$mpurl.'page='.($curpage + 1).'" class="p_redirect"'.$onclick.'>&rsaquo;&rsaquo;</a>' : '').
			($to < $pages ? '<a href="'.$mpurl.'page='.$pages.'" class="p_redirect"'.$onclick.'>&rsaquo;|</a>' : '').
			($curpage == $maxpages ? '<a class="p_redirect" href="misc.php?action=maxpages&amp;pages='.$maxpages.'">&rsaquo;?</a>' : '').
			(!$simple && $pages > $page ? '<a class="p_pages" style="padding: 0px"><input class="p_input" type="text" name="custompage" onKeyDown="if(event.keyCode==13) {window.location=\''.$mpurl.'page=\'+this.value; return false;}"></a>' : '');

		$multipage = $multipage ? '<div class="p_bar">'.(!$simple ? '<a class="p_total">&nbsp;'.$num.'&nbsp;</a><a class="p_pages">&nbsp;'.$curpage.'/'.$realpages.'&nbsp;</a>' : '').$multipage.'</div>' : '';
	}
	return $multipage;
}

function calendar() {
	global $db, $tablepre, $uid, $starttime, $timestamp, $timeoffset, $dateformat;

	$starttime = isset($starttime) ? intval($starttime) : 0;
	$starttime = $starttime ? $starttime : $timestamp;

	$pendtime = $starttime - (gmdate('j', $starttime + $timeoffset * 3600) - 1) * 86400 - ($starttime + $timeoffset * 3600) % 86400;
	$pstarttime = $pendtime - gmdate('t', $pendtime + $timeoffset * 3600 - 1) * 86400;

	$nstarttime = $pendtime + gmdate('t', $pendtime + $timeoffset * 3600 + 1) * 86400;
	$nendtime = $nstarttime + gmdate('t', $nstarttime + $timeoffset * 3600 + 1) * 86400;

	list($skip, $dim) = explode('-', gmdate('w-t', $pendtime + $timeoffset * 3600 + 1));
	$rows = ceil(($skip + $dim) / 7);

	$blogs = array();
	$query = $db->query("SELECT dateline FROM {$tablepre}threads WHERE blog='1' AND authorid='$uid' AND dateline BETWEEN '$pendtime' AND '$nstarttime' AND displayorder>='0'");
	while($blog = $db->fetch_array($query)) {
		$day = gmdate('j', $blog['dateline'] + $timeoffset * 3600);
		!isset($blogs[$day]) ? $blogs[$day] = array('num' => 1, 'dateline' => $blog['dateline'] - $blog['dateline'] % 86400) : $blogs[$day]['num']++;
	}

	$cal = '';
	for($row = 0; $row < $rows; $row++) {
		$cal .= '<tr class="row" align="center">';
		for($col = 0; $col < 7; $col++) {
			$cur = $row * 7 + $col - $skip + 1;
			$curtd = $row * 7 + $col < $skip || $cur > $dim ? '&nbsp;' : $cur;
			if(!isset($blogs[$cur])) {
				$cal .= '<td>'.$curtd.'</td>';
			} else {
				$cal .= '<td><a href="space.php?'.$uid.'/myblogs/'.$blogs[$cur]['dateline'].'/'.($blogs[$cur]['dateline'] + 86400).'" title=" '.$blogs[$cur]['num'].' ">'.$cur.'</a></td>';
			}
		}
		$cal .= '</tr>';
	}

	$calendar = array('curtime' => gmdate('Y-m', $starttime + $timeoffset * 3600), 'pstarttime' => $pstarttime, 'pendtime' => $pendtime, 'nstarttime' => $nstarttime, 'nendtime' => $nendtime, 'html' => $cal);
	viewcalendar($calendar);
}

function spacecaches($uid, $modulelist = '') {
	global $db, $tablepre, $timestamp;

	$moduledata = array();
	if(is_array($modulelist)) {
		$allmodules = array(
			'mythreads',
			'myreplies',
			'myrewards',
			'mytrades',
			'myvideos',
			'myfriends',
			'myfavforums',
			'myfavthreads',
			'myblogs',
			'hotblog',
			'lastpostblog',
			);
		$modulelist = empty($modulelist) ? $allmodules : array_intersect($modulelist, $allmodules);
		$query = $db->query("SELECT * FROM {$tablepre}spacecaches WHERE uid='$uid'");
	} else {
		$query = $db->query("SELECT * FROM {$tablepre}spacecaches WHERE uid='$uid' AND variable='$modulelist'");
		$modulelist = $allmodules = array($modulelist);
	}
	while($module = $db->fetch_array($query)) {
		$moduledata[$module['variable']]['expiration'] = $module['expiration'];
		$moduledata[$module['variable']]['value'] = unserialize($module['value']);
	}
	foreach($allmodules as $module) {
		if(in_array($module, $modulelist) && (empty($moduledata[$module]) || $timestamp > $moduledata[$module]['expiration'])) {
			$moduledata[$module]['value'] = updatespacecache($uid, $module);
		}
		if(!empty($modulelist) && !in_array($module, $modulelist)) {
			unset($moduledata[$module]);
		}
	}
	return $moduledata;
}

function module_mythreads(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'mythreads m';
	if(!$parms['list']) {
		$parms['cols'] = 't.tid, t.subject, t.special, t.price, t.fid, t.views, t.replies, t.author, t.authorid, t.lastpost, t.lastposter, t.attachment, p.pid, p.message';
		$parms['conditions'] = "INNER JOIN {$tablepre}posts p ON p.tid=m.tid AND p.first='1'
			INNER JOIN {$tablepre}threads t ON t.blog='0' AND t.authorid='$uid' AND t.author!='' AND t.price='0' AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]'
			WHERE m.uid='$uid' AND m.tid=t.tid ORDER BY m.dateline DESC";
	} else {
		$parms['cols'] = 't.tid, t.subject, t.special, t.fid, t.views, t.replies, t.author, t.authorid, t.lastpost, t.lastposter, t.attachment';
		$parms['conditions'] = "INNER JOIN {$tablepre}threads t ON t.blog='0' AND t.authorid='$uid' AND t.author!='' AND t.price='0' AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]'
			WHERE m.uid='$uid' AND m.tid=t.tid ORDER BY m.dateline DESC";
	}
}

function module_myreplies(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'myposts m';
	$parms['cols'] = 't.tid, t.subject, t.special, t.price, t.fid, t.views, t.replies, t.author, t.authorid, t.lastpost, t.lastposter, t.attachment, p.pid'.(!$parms['list'] ? ', p.message' : '');
	$parms['conditions'] = "INNER JOIN {$tablepre}posts p ON p.pid=m.pid AND p.anonymous='0'
		INNER JOIN {$tablepre}threads t ON t.tid=m.tid AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]'
		WHERE m.uid='$uid' ORDER BY m.dateline DESC";
}

function module_myrewards(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'threads t';
	if(!$parms['list']) {
		$parms['cols'] = 't.tid, t.fid, t.views, t.replies, t.price, t.subject, p.pid, p.message';
		$parms['conditions'] = "INNER JOIN {$tablepre}posts p ON p.tid=t.tid AND p.first='1'
			WHERE t.authorid='$uid' AND t.author!='' AND t.special='3' AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]' ORDER BY t.lastpost DESC";
	} else {
		$parms['cols'] = 't.tid, t.fid, t.views, t.replies, t.price, t.subject';
		$parms['conditions'] = "WHERE t.authorid='$uid' AND t.author!='' AND t.special='3' AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]' ORDER BY t.lastpost DESC";
	}
	$parms['extraquery'] = "SELECT r.tid, r.answererid, m.username FROM {$tablepre}rewardlog r LEFT JOIN {$tablepre}members m ON m.uid=r.answererid WHERE r.authorid='$uid' AND r.tid IN ";
}

function module_mytrades(&$parms) {
	global $uid, $tablepre, $tradetypeid;
	$parms['table'] = 'trades';
	$parms['cols'] = '*';
	if($tradetypeid == 'all') {
		$typeadd = '';
	} elseif($tradetypeid == 'stick') {
		$typeadd = 'AND displayorder>0';
	} elseif($tradetypeid == '') {
		$typeadd = 'AND displayorder>0';
		$parms['items'] = 9;
	} else {
		$typeadd = 'AND typeid=\''.intval($tradetypeid).'\'';
	}
	$parms['conditions'] = "WHERE sellerid='$uid' $typeadd ORDER BY dateline DESC";
}

function module_myvideos(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'videos v, '.$tablepre.'members m';
	$parms['cols'] = 'm.username, v.*';
	$parms['conditions'] = "WHERE m.uid='$uid' AND m.uid=v.uid ORDER BY v.dateline DESC";
}

function module_mytradetypes(&$parms) {
	global $uid;
	$parms['table'] = 'trades';
	$parms['cols'] = 'typeid';
	$parms['conditions'] = "WHERE sellerid='$uid' GROUP BY typeid";
	$parms['items'] = '65535';
}

function module_mycounters(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'threads t';
	$parms['cols'] = 't.tid, t.fid, t.views, t.replies, t.price, t.subject';
	$parms['conditions'] = "WHERE t.authorid='$uid' AND t.author!='' AND t.special='2' AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]' ORDER BY t.lastpost DESC";
	$parms['pagemodule'] = 'mytrades';
}

function format_expiration(&$trade) {
	global $timestamp;
	if($trade['expiration']) {
		$trade['expiration'] = ($trade['expiration'] - $timestamp) / 86400;
		if($trade['expiration'] > 0) {
			$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
			$trade['expiration'] = floor($trade['expiration']);
		} else {
			$trade['expiration'] = -1;
		}
	}
}

function module_myfriends(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'members m';
	$parms['cols'] = 'm.uid, m.username, mf.spacename';
	$parms['conditions'] = "LEFT JOIN {$tablepre}buddys b ON m.uid=b.buddyid
		LEFT JOIN {$tablepre}memberfields mf ON m.uid=mf.uid
		WHERE b.uid='$uid'";
}

function module_myfavforums(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'forums f';
	$parms['cols'] = 'f.fid, f.name, f.threads, f.posts, f.todayposts';
	$parms['conditions'] = ",{$tablepre}favorites fav
		WHERE fav.fid=f.fid AND fav.uid='$uid' AND fav.fid IN ($parms[infids])";
}

function module_myfavthreads(&$parms) {
	global $uid, $tablepre;
	$parms['table'] = 'threads t';
	$parms['cols'] = 't.tid, t.subject, t.special, t.price, t.fid, t.views, t.replies, t.lastposter, t.lastpost';
	$parms['conditions'] = ",{$tablepre}favorites fav
		WHERE fav.tid=t.tid AND fav.uid='$uid' AND t.fid IN ($parms[infids]) AND t.displayorder>='0' AND t.readperm<='$parms[readaccess]' ORDER BY t.lastpost DESC";
}

function module_myblogs(&$parms) {
	global $uid, $tablepre, $starttime, $endtime;
	$starttimeadd = $starttime ? "AND t.dateline>='$starttime'" : '';
	$endtimeadd = $endtime ? "AND t.dateline<'$endtime'" : '';
	$parms['table'] = 'threads t';
	$parms['cols'] = 't.tid, t.subject, t.special, t.price, t.fid, t.views, t.replies, t.authorid, t.dateline, t.lastpost, t.lastposter, t.attachment, p.pid, p.message';
	$parms['conditions'] = "INNER JOIN {$tablepre}posts p ON p.tid=t.tid AND p.first='1' AND p.anonymous='0'
		WHERE t.blog='1' AND t.authorid='$uid' $starttimeadd $endtimeadd ORDER BY t.dateline DESC";
}

function module_hotblog(&$parms) {
	global $uid;
	$parms['table'] = 'threads';
	$parms['cols'] = 'tid, subject, views, replies';
	$parms['conditions'] = "WHERE blog='1' AND authorid='$uid' AND displayorder>='0' ORDER BY views DESC";
	$parms['items'] = 5;
}

function module_lastpostblog(&$parms) {
	global $uid;
	$parms['table'] = 'threads';
	$parms['cols'] = 'tid, subject, views, replies';
	$parms['conditions'] = "WHERE blog='1' AND authorid='$uid' AND displayorder>='0' ORDER BY lastpost DESC";
	$parms['items'] = 5;
}

?>