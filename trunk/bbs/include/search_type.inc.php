<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: search_trade.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(isset($searchid)) {

	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;

	$index = $db->fetch_first("SELECT searchstring, keywords, threads, threadtypeid, tids FROM {$tablepre}searchindex WHERE searchid='$searchid' AND threadtypeid='$typeid'");
	if(!$index) {
		showmessage('search_id_invalid');
	}

	$threadlist = $typelist = $resultlist = $optionlist = array();
	$query = $db->query("SELECT tid, subject, dateline FROM {$tablepre}threads WHERE tid IN ($index[tids]) AND displayorder>=0 ORDER BY dateline LIMIT $start_limit, $tpp");
	while($info = $db->fetch_array($query)) {
		$threadlist[$info['tid']]['dateline'] = gmdate("$dateformat $timeformat", $info['dateline'] + $timeoffset * 3600);
		$threadlist[$info['tid']]['subject'] = $info['subject'];
	}

	@include_once DISCUZ_ROOT.'./forumdata/cache/threadtype_'.$index['threadtypeid'].'.php';

	$query = $db->query("SELECT tid, optionid, value FROM {$tablepre}typeoptionvars WHERE tid IN ($index[tids])");
	while($info = $db->fetch_array($query)) {
		if($_DTYPE[$info['optionid']]['search']) {
			$typelist[$info['tid']][$info['optionid']]['value'] = $info['value'];
			$optionlist[] = $_DTYPE[$info['optionid']]['title'];
		}
	}

	$optionlist = $optionlist ? array_unique($optionlist) : '';

	$choiceshow = '';
	foreach($threadlist as $tid => $thread) {
		$resultlist[$tid]['subject'] = $thread['subject'];
		$resultlist[$tid]['dateline'] = $thread['dateline'];
		if(is_array($typelist[$tid])) {
			foreach($typelist[$tid] as $optionid => $value) {
				if(in_array($_DTYPE[$optionid]['type'], array('select', 'radio'))) {
					$resultlist[$tid]['option'][] = $_DTYPE[$optionid]['choices'][$value['value']];
				} elseif($_DTYPE[$optionid]['type'] == 'checkbox') {
					foreach(explode("\t", $value['value']) as $choiceid) {
						$choiceshow .= $_DTYPE[$optionid]['choices'][$choiceid].'&nbsp;';
					}
					$resultlist[$tid]['option'][] = $choiceshow;
				} elseif($_DTYPE[$optionid]['type'] == 'image') {
					$maxwidth = $_DTYPE[$optionid]['maxwidth'] ? 'width="'.$_DTYPE[$optionid]['maxwidth'].'"' : '';
					$maxheight = $_DTYPE[$optionid]['maxheight'] ? 'height="'.$_DTYPE[$optionid]['maxheight'].'"' : '';
					$resultlist[$tid]['option'][] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\"><img src=\"$value[value]\"  $maxwidth $maxheight border=\"0\"></a>" : '';
				} elseif($_DTYPE[$optionid]['type'] == 'url') {
					$resultlist[$tid]['option'][] = $optiondata[$optionid] ? "<a href=\"$value[value]\" target=\"_blank\">$value[value]</a>" : '';
				} else {
					$resultlist[$tid]['option'][] = $value['value'];
				}
			}
		}
	}

	$colspan = count($optionlist) + 2;
	$multipage = multi($index['threads'], $tpp, $page, "search.php?searchid=$searchid&amp;srchtype=threadtype&amp;typeid=$index[threadtypeid]&amp;searchsubmit=yes");
	$url_forward = 'search.php?'.$_SERVER['QUERY_STRING'];
	include template('search_type');

} else {

	checklowerlimit($creditspolicy['search'], -1);

	$forumsarray = array();
	if(!empty($srchfid)) {
		foreach((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
			if($forum = intval(trim($forum))) {
				$forumsarray[] = $forum;
			}
		}
	}

	$fids = $comma = '';
	foreach($_DCACHE['forums'] as $fid => $forum) {
		if($forum['type'] != 'group' && (!$forum['viewperm'] && $readaccess) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
			if(!$forumsarray || in_array($fid, $forumsarray)) {
				$fids .= "$comma'$fid'";
				$comma = ',';
			}
		}
	}

	$srchoption = $tab = '';
	if($searchoption && is_array($searchoption)) {
		foreach($searchoption as $optionid => $option) {
			$srchoption .= $tab.$optionid;
			$tab = "\t";
		}
	}

	$searchstring = 'type|'.addslashes($srchoption);
	$searchindex = array('id' => 0, 'dateline' => '0');

	$query = $db->query("SELECT searchid, dateline,
		('$searchctrl'<>'0' AND ".(empty($discuz_uid) ? "useip='$onlineip'" : "uid='$discuz_uid'")." AND $timestamp-dateline<$searchctrl) AS flood,
		(searchstring='$searchstring' AND expiration>'$timestamp') AS indexvalid
		FROM {$tablepre}searchindex
		WHERE ('$searchctrl'<>'0' AND ".(empty($discuz_uid) ? "useip='$onlineip'" : "uid='$discuz_uid'")." AND $timestamp-dateline<$searchctrl) OR (searchstring='$searchstring' AND expiration>'$timestamp')
		ORDER BY flood");

	while($index = $db->fetch_array($query)) {
		if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
			$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
			break;
		} elseif($index['flood']) {
			showmessage('search_ctrl', "search.php?srchtype=threadtype&typeid=$selecttypeid&srchfid=$fid");
		}
	}

	if($searchindex['id']) {

		$searchid = $searchindex['id'];

	} else {

		if((!$searchoption || !is_array($searchoption)) && !$selecttypeid) {
			showmessage('search_threadtype_invalid', "search.php?srchtype=threadtype&typeid=$selecttypeid&srchfid=$fid");
		} elseif(isset($srchfid) && $srchfid != 'all' && !(is_array($srchfid) && in_array('all', $srchfid)) && empty($forumsarray)) {
			showmessage('search_forum_invalid', "search.php?srchtype=threadtype&typeid=$selecttypeid&srchfid=$fid");
		} elseif(!$fids) {
			showmessage('group_nopermission', NULL, 'NOPERM');
		}

		if($maxspm) {
			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}searchindex WHERE dateline>'$timestamp'-60") >= $maxspm) {
				showmessage('search_toomany', 'search.php');
			}
		}

		$sqlsrch = $or = '';
		if(!empty($searchoption) && is_array($searchoption)) {
			foreach($searchoption as $optionid => $option) {
				if($option['value']) {
					if(in_array($option['type'], array('number', 'radio', 'select'))) {
						$option['value'] = intval($option['value']);
						$exp = '=';
						if($option['condition']) {
							$exp = $option['condition'] == 1 ? '>' : '<';
						}
						$sql = "value$exp'$option[value]'";
					} elseif($option['type'] == 'checkbox') {
						$sql = "value LIKE '%\t".(implode("\t", $option['value']))."\t%'";
					} else {
						$sql = "value LIKE '%$option[value]%'";
					}
					$sqlsrch .= $or."(optionid='$optionid' AND $sql) ";
					$or = 'OR ';
				}
			}
		}

		$threads = $tids = 0;
		$query = $db->query("SELECT tid, typeid FROM {$tablepre}typeoptionvars WHERE (expiration='0' OR expiration>'$timestamp') ".($sqlsrch ? 'AND '.$sqlsrch : '')."");
		while($post = $db->fetch_array($query)) {
			if($post['typeid'] == $selecttypeid) {
				if($thread['closed'] <= 1) {
					$tids .= ','.$post['tid'];
				}
			}
		}
		$db->free_result($query);

		if($fids) {
			$query = $db->query("SELECT tid, closed FROM {$tablepre}threads WHERE tid IN ($tids) AND fid IN ($fids) LIMIT $maxsearchresults");
			while($post = $db->fetch_array($query)) {
				if($thread['closed'] <= 1) {
					$tids .= ','.$post['tid'];
					$threads++;
				}
			}
		}

		$db->query("INSERT INTO {$tablepre}searchindex (keywords, searchstring, useip, uid, dateline, expiration, threads, threadtypeid, tids)
				VALUES ('$keywords', '$searchstring', '$onlineip', '$discuz_uid', '$timestamp', '$expiration', '$threads', '$selecttypeid', '$tids')");
		$searchid = $db->insert_id();

		updatecredits($discuz_uid, $creditspolicy['search'], -1);

	}

	showmessage('search_redirect', "search.php?searchid=$searchid&amp;srchtype=threadtype&amp;typeid=$selecttypeid&amp;searchsubmit=yes");

}

?>