<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread.php 16794 2008-11-20 07:11:39Z cnteacher $
*/

define('CURSCRIPT', 'viewthread');
define('SQL_ADD_THREAD', ' t.dateline, t.special, t.lastpost AS lastthreadpost,');
require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/forum.func.php';

$page = max($page, 1);
if($cachethreadlife && $forum['threadcaches'] && !$discuz_uid && $page == 1 && !$forum['special']) {
	viewthread_loadcache();
}

require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

$discuz_action = 3;

$supe['status'] && supe_dbconnect();
$thread = $db->fetch_first("SELECT * FROM {$tablepre}threads t WHERE tid='$tid' AND displayorder>='0'");

if(!$thread) {
	$tid = intval($_GET['tid']);
	if($supe['status'] && is_numeric($tid) && !empty($supe_fromsupesite) && md5("$discuz_auth_key-$tid") == $supe_fromsupesite) {
		$supe['db']->query("UPDATE {$supe[tablepre]}spaceitems SET tid='0' WHERE tid='$tid'", 'SILENT');
	}
	showmessage('thread_nonexistence');
}

$oldtopics = isset($_DCOOKIE['oldtopics']) ? $_DCOOKIE['oldtopics'] : 'D';
if(strpos($oldtopics, 'D'.$tid.'D') === FALSE) {
	$oldtopics = 'D'.$tid.$oldtopics;
	if(strlen($oldtopics) > 3072) {
		$oldtopics = preg_replace("((D\d+)+D).*$", "\\1", substr($oldtopics, 0, 3072));
	}
	dsetcookie('oldtopics', $oldtopics, 3600);
}

if($lastvisit < $thread['lastpost'] && (!isset($_DCOOKIE['fid'.$fid]) || $thread['lastpost'] > $_DCOOKIE['fid'.$fid])) {
	dsetcookie('fid'.$fid, $thread['lastpost'], 3600);
}

$thisgid = 0;

$thread['subjectenc'] 	= rawurlencode($thread['subject']);
$fromuid 		= $creditspolicy['promotion_visit'] && $discuz_uid ? '&amp;fromuid='.$discuz_uid : '';
$supe_fromdiscuz 	= $supe['status'] && $thread['itemid'] ? md5("$discuz_auth_key-$thread[itemid]") : '';
$iscircle 		= $supe['status'] && $supe['circlestatus'] && $forum['status'] == 2 && $thread['sgid'];

$navigation = '&raquo; <a href="'.($iscircle && empty($frombbs) ? $supe['siteurl'].'?action_mygroup_gid_'.$thread['sgid'].'_op_list_type_bbs_fid_'.$fid : 'forumdisplay.php?fid='.$fid.($extra ? '&amp;'.preg_replace("/^(&amp;)*/", '', $extra) : '')).'">'.$forum['name'].'</a> &raquo; '.$thread['subject'];

$navtitle = $thread['subject'].' - '.strip_tags($forum['name']);
if($forum['type'] == 'sub') {
	$fup = $db->fetch_first("SELECT fid, name FROM {$tablepre}forums WHERE fid='$forum[fup]'");
	$navigation = '&raquo; <a href="'.($iscircle ? $supe['siteurl'].'?action_mygroup_gid_'.$thread['sgid'].'_op_list_type_bbs_fid_'.$fup[fid] : 'forumdisplay.php?fid='.$fup[fid]).'">'.$fup[name].'</a> '.$navigation;
	$navtitle = $navtitle.' - '.strip_tags($fup['name']);
}
$navtitle .= ' - ';

$forum['typemodels'] = $forum['typemodels'] ? unserialize($forum['typemodels']) : array();
$threadtype = isset($forum['threadtypes']['types'][$thread['typeid']]) ? 1 : 0;
$typetemplate = '';
$optiondata = $optionlist = array();
if($thread['typeid'] && $threadtype) {
	if($forum['threadtypes']['special'][$thread['typeid']]) {
		if(@include_once DISCUZ_ROOT.'./forumdata/cache/threadtype_'.$thread['typeid'].'.php') {
			$query = $db->query("SELECT optionid, value FROM {$tablepre}typeoptionvars WHERE tid='$tid'");
			while($option = $db->fetch_array($query)) {
				$optiondata[$option['optionid']] = $option['value'];
			}

			foreach($_DTYPE as $optionid => $option) {
				$optionlist[$option['identifier']]['title'] = $_DTYPE[$optionid]['title'];
				if($_DTYPE[$optionid]['type'] == 'checkbox') {
					$optionlist[$option['identifier']]['value'] = '';
					foreach(explode("\t", $optiondata[$optionid]) as $choiceid) {
						$optionlist[$option['identifier']]['value'] .= $_DTYPE[$optionid]['choices'][$choiceid].'&nbsp;';
					}
				} elseif(in_array($_DTYPE[$optionid]['type'], array('radio', 'select'))) {
					$optionlist[$option['identifier']]['value'] = $_DTYPE[$optionid]['choices'][$optiondata[$optionid]];
				} elseif($_DTYPE[$optionid]['type'] == 'image') {
					$maxwidth = $_DTYPE[$optionid]['maxwidth'] ? 'width="'.$_DTYPE[$optionid]['maxwidth'].'"' : '';
					$maxheight = $_DTYPE[$optionid]['maxheight'] ? 'height="'.$_DTYPE[$optionid]['maxheight'].'"' : '';
					$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\"><img src=\"$optiondata[$optionid]\"  $maxwidth $maxheight border=\"0\"></a>" : '';
				} elseif($_DTYPE[$optionid]['type'] == 'url') {
					$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\">$optiondata[$optionid]</a>" : '';
				} elseif($_DTYPE[$optionid]['type'] == 'textarea') {
					$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? nl2br($optiondata[$optionid]) : '';
				} else {
					$optionlist[$option['identifier']]['value'] = $optiondata[$optionid];
				}
			}

			$typetemplate = $_DTYPETEMPLATE ? preg_replace(array("/\[(.+?)value\]/ies", "/{(.+?)}/ies"), array("showoption('\\1', 'value')", "showoption('\\1', 'title')"), $_DTYPETEMPLATE) : '';
		}
	}

	$thread['subject'] = ($forum['threadtypes']['listable'] ? '<a href="forumdisplay.php?fid='.$fid.'&amp;filter=type&amp;typeid='.$thread['typeid'].'">['.$forum['threadtypes']['types'][$thread['typeid']].']</a>' : '['.$forum['threadtypes']['types'][$thread['typeid']].']').' '.$thread['subject'];
}

if(empty($forum['allowview'])) {
	if(!$forum['viewperm'] && !$readaccess) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
		$navtitle = '';
		showmessage('forum_nopermission', NULL, 'NOPERM');
	}
}

if($forum['formulaperm']) {
	forumformulaperm($forum['formulaperm']);
}

if($forum['password'] && $forum['password'] != $_DCOOKIE['fidpw'.$fid]) {
	dheader("Location: {$boardurl}forumdisplay.php?fid=$fid&amp;sid=$sid");
}

if($thread['readperm'] && $thread['readperm'] > $readaccess && !$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
	showmessage('thread_nopermission', NULL, 'NOPERM');
}

if($thread['price'] > 0 && $thread['special'] == 0) {

	if($maxchargespan && $timestamp - $thread['dateline'] >= $maxchargespan * 3600) {
		$db->query("UPDATE {$tablepre}threads SET price='0' WHERE tid='$tid'");
		$thread['price'] = 0;
	} else {
		if(!$discuz_uid) {
			showmessage('group_nopermission', NULL, 'NOPERM');

		} elseif(!$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
			$query = $db->query("SELECT tid FROM {$tablepre}paymentlog WHERE tid='$tid' AND uid='$discuz_uid'");
			if(!$db->num_rows($query)) {
				require_once DISCUZ_ROOT.'./include/threadpay.inc.php';
				$threadpay = TRUE;
			}
		}
	}
}

if($iscircle) {
        require_once DISCUZ_ROOT.'./include/supesite_circle.inc.php';
}

$forum['modrecommend'] = $forum['modrecommend'] ? unserialize($forum['modrecommend']) : array();
$raterange = $modratelimit && $adminid == 3 && !$forum['ismoderator'] ? array() : $raterange;
$extra = rawurlencode($extra);

$allowgetattach = !empty($forum['allowgetattach']) || ($allowgetattach && !$forum['getattachperm']) || forumperm($forum['getattachperm']);

$postlist = $attachtags = $attachlist = array();
$attachpids = $announcepm = 0;
if(empty($action) && $tid) {

	$thisgid = $forum['type'] == 'forum' ? $forum['fup'] : $_DCACHE['forums'][$forum['fup']]['fup'];
	$lastmod = $thread['moderated'] ? viewthread_lastmod() : array();

	$pmlist = array();
	if($_DCACHE['pmlist']) {
		$readapmids = !empty($_DCOOKIE['readapmid']) ? explode('D', $_DCOOKIE['readapmid']) : array();
		foreach($_DCACHE['pmlist'] as $pm) {
			if($discuz_uid && (empty($pm['groups']) || in_array($groupid, $pm['groups']))) {
				if(!in_array($pm['pmid'], $readapmids)) {
					$pm['announce'] = TRUE;
					$pmlist[] = $pm;
					$announcepm++;
				}
			}
		}
	}
	if($discuz_uid && $newpm) {
		require_once DISCUZ_ROOT.'./include/pmprompt.inc.php';
	}

	$showsettings = str_pad(decbin($showsettings), 3, '0', STR_PAD_LEFT);

	$customshow = $discuz_uid ? str_pad(base_convert($customshow, 10, 3), 3, '0', STR_PAD_LEFT) : '222';

	$showsignatures = $customshow{0} == 2 ? $showsettings{0} : $customshow{0};
	$showavatars = $customshow{1} == 2 ? $showsettings{1} : $customshow{1};
	$showimages = $customshow{2} == 2 ? $showsettings{2} : $customshow{2};
	$allowpaytoauthor = $forum['allowpaytoauthor'];

	$highlightstatus = isset($highlight) && str_replace('+', '', $highlight) ? 1 : 0;

	$usesigcheck = $discuz_uid && $sigstatus ? 'checked="checked"' : '';
	$allowpostreply = ((!$thread['closed'] && !checkautoclose()) || $forum['ismoderator']) && ((!$forum['replyperm'] && $allowreply) || ($forum['replyperm'] && forumperm($forum['replyperm'])) || $forum['allowreply']);
	$allowpost = (!$forum['postperm'] && $allowpost) || ($forum['postperm'] && forumperm($forum['postperm'])) || $forum['allowpost'];

	if($allowpost) {
		$allowpostpoll = $allowpostpoll && ($forum['allowpostspecial'] & 1);
		$allowposttrade = $allowposttrade && ($forum['allowpostspecial'] & 2);
		$allowpostreward = $allowpostreward && ($forum['allowpostspecial'] & 4) && isset($extcredits[$creditstrans]);
		$allowpostactivity = $allowpostactivity && ($forum['allowpostspecial'] & 8);
		$allowpostdebate = $allowpostdebate && ($forum['allowpostspecial'] & 16);
		$allowpostvideo = $allowpostvideo && ($forum['allowpostspecial'] & 32) && $videoopen;
	} else {
		$allowpostpoll = $allowposttrade = $allowpostreward = $allowpostactivity = $allowpostdebate = $allowpostvideo = FALSE;
	}

	$visitedforums = $visitedforums ? visitedforums() : '';
	$forumselect = $forummenu = '';

	$forumselect = $forummenu = '';
	if($forumjump) {
		if($jsmenu[1]) {
			$forummenu = forumselect(FALSE, 1);
		} else {
			$forumselect = forumselect(FALSE, 1);
		}
	}


	$relatedthreadlist = array();
	$relatedthreadupdate = FALSE;
	$relatedkeywords = $tradekeywords = $metakeywords = $firstpid = '';
	$randnum = $qihoo['relate']['webnum'] ? rand(1, 1000) : '';
	$statsdata = $statsdata ? dhtmlspecialchars($statsdata) : '';
	if($qihoo['relate']['bbsnum'] || $insenz['topicrelatedad'] || ($insenz['traderelatedad'] && $thread['special'] == 2)) {
		$site = site();
		$query = $db->query("SELECT type, expiration, keywords, relatedthreads FROM {$tablepre}relatedthreads WHERE tid='$tid'");
		if($db->num_rows($query)) {
			while($related = $db->fetch_array($query)) {
				if($related['expiration'] <= $timestamp) {
					$relatedthreadupdate = TRUE;
					$qihoo_up = 1;
				} elseif($qihoo['relate']['bbsnum'] && $related['type'] == 'general') {
					$relatedthreadlist = unserialize($related['relatedthreads']);
					if($related['keywords']) {
						$keywords = str_replace("\t", ' ', $related['keywords']);
						$searchkeywords = rawurlencode($keywords);
						$statskeywords = urlencode($keywords);
						$statsurl = urlencode($boardurl.'viewthread.php?tid='.$tid);
						foreach(explode("\t", $related['keywords']) as $keyword) {
							$relatedkeywords .= $keyword ? '<a href="http://search.qihoo.com/sint/qusearch.html?kw='.rawurlencode($keyword).'&amp;domain='.site().'&amp;ics='.$charset.'" target="_blank">'.$keyword.'</a> ' : '';
							$metakeywords .= $keyword ? $keyword.',' : '';
						}
					}
				} elseif($related['type'] == 'trade') {
					$tradekeywords = explode("\t", $related['keywords']);
					$tradekeywords = $tradekeywords[array_rand($tradekeywords)];
				}
			}
		} else {
			$relatedthreadupdate = TRUE;
			$qihoo_up = 0;
		}
		$relatedthreadupdate && $verifykey = md5($authkey.$tid.$thread['subjectenc'].$charset.$site);
	}
	$relatedthreads = array();
	if(!empty($relatedthreadlist)) {
		if(!isset($_COOKIE['discuz_collapse']) || strpos($_COOKIE['discuz_collapse'], 'relatedthreads') === FALSE) {
			$relatedthreads['img'] = 'collapsed_no.gif';
			$relatedthreads['style'] = '';
		} else {
			$relatedthreads['img'] = 'collapsed_yes.gif';
			$relatedthreads['style'] = 'display: none';
		}
	}

	$supe_pushstatusadd = '';
	if($supe['status'] && $supe_allowpushthread && $forum['supe_pushsetting']['status'] == 3) {
		if(($thread['views'] && $forum['supe_pushsetting']['filter']['views'] && $thread['views'] >= intval($forum['supe_pushsetting']['filter']['views'])) ||
			($thread['replies'] && $forum['supe_pushsetting']['filter']['replies'] && $thread['replies'] >= intval($forum['supe_pushsetting']['filter']['replies'])) ||
			($thread['digest'] && $forum['supe_pushsetting']['filter']['digest'] && $thread['digest'] >= intval($forum['supe_pushsetting']['filter']['digest'])) ||
			($thread['displayorder'] && $forum['supe_pushsetting']['filter']['displayorder'] && $thread['displayorder'] >= intval($forum['supe_pushsetting']['filter']['displayorder']))) {
			if($thread['supe_pushstatus'] == 0) {
				$supe_pushstatusadd = ", supe_pushstatus='3'";
			}
		} elseif($thread['supe_pushstatus'] == 3) {
			$supe_pushstatusadd = ", supe_pushstatus='0'";
		}
	}

	if($tagstatus) {
		$query = $db->query("SELECT tagname FROM {$tablepre}threadtags WHERE tid='$tid'");
		$thread['tags'] = '';
		while($tags = $db->fetch_array($query)) {
			$metakeywords .= $tags['tagname'].',';
			$thread['tags'] .= '<a href="tag.php?name='.rawurlencode($tags['tagname']).'" target="_blank">'.$tags['tagname'].'</a> ';
		}
		$relatedthreadupdate && $thread['tagsenc'] = rawurlencode(strip_tags($thread['tags']));
	}

	if(empty($inajax)) {
		viewthread_updateviews();
	}
	
	@extract($_DCACHE['custominfo']);

	if($thread['special'] > 0) {
		$thread['starttime'] = gmdate("$dateformat $timeformat", $thread['dateline'] + $timeoffset * 3600);
		$thread['remaintime'] = '';

		if(!empty($do) && $do == 'viewspecialpost') {
			include_once DISCUZ_ROOT.'./include/viewthread_special.inc.php';
		} else {
			switch($thread['special']) {
				case 1: include_once DISCUZ_ROOT.'./include/viewthread_poll.inc.php'; break;
				case 2: include_once DISCUZ_ROOT.'./include/viewthread_trade.inc.php'; break;
				case 3: include_once DISCUZ_ROOT.'./include/viewthread_reward.inc.php'; break;
				case 4: include_once DISCUZ_ROOT.'./include/viewthread_activity.inc.php'; break;
				case 5: include_once DISCUZ_ROOT.'./include/viewthread_debate.inc.php'; break;
				case 6: include_once DISCUZ_ROOT.'./include/viewthread_video.inc.php'; break;
			}
		}
	}

	$onlyauthoradd = '';
	$authorid = intval($authorid);
	if($authorid) {
		$thread['replies'] = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='$tid' AND invisible='0' AND authorid='$authorid' AND anonymous='0'") - 1;
		if($thread['replies'] < 0) {
			showmessage('undefined_action');
		}
		$onlyauthoradd = "AND p.authorid='$authorid'";
	};

	$ppp = $forum['threadcaches'] && !$discuz_uid ? $_DCACHE['settings']['postperpage'] : $ppp;
	$totalpage = ceil(($thread['replies'] + 1) / $ppp);
	$page > $totalpage && $page = $totalpage;
	$pagebydesc = $page > 50 && $page > ($totalpage / 2) ? TRUE : FALSE;

	if($pagebydesc) {
		$firstpagesize = ($thread['replies'] + 1) % $ppp;
		$ppp3 = $ppp2 = $page == $totalpage && $firstpagesize ? $firstpagesize : $ppp;
		$realpage = $totalpage - $page + 1;
		$start_limit = max(0, ($realpage - 2) * $ppp + $firstpagesize);
		$numpost = ($page - 1) * $ppp;
		$pageadd =  "ORDER BY dateline DESC LIMIT $start_limit, $ppp2";
	} else {
		$start_limit = $numpost = ($page - 1) * $ppp;
		if($start_limit > $thread['replies']) {
			$start_limit = $numpost = 0;
			$page = 1;
		}
		$pageadd =  "ORDER BY dateline LIMIT $start_limit, $ppp";
	}


	$multipage = multi($thread['replies'] + 1, $ppp, $page, "viewthread.php?tid=$tid&amp;extra=$extra".(isset($highlight) ? "&amp;highlight=".rawurlencode($highlight) : '').(!empty($authorid) ? "&amp;authorid=$authorid" : ''));

	$newpostanchor = $postcount = $ratelogpids = 0;

	$onlineauthors = array();
	$query = $db->query("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
		m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
		m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
		mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
		mf.avatarheight, mf.sightml AS signature, mf.customstatus, mf.spacename $fieldsadd
		FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		WHERE p.tid='$tid' AND p.invisible='0' $onlyauthoradd  $pageadd");

	while($post = $db->fetch_array($query)) {
		$postlist[$post['pid']] = viewthread_procpost($post);
	}
	if($pagebydesc) {
		$postlist = array_reverse($postlist, TRUE);
	}

	if($vtonlinestatus == 2 && $onlineauthors) {
		$query = $db->query("SELECT uid FROM {$tablepre}sessions WHERE uid IN(".(implode(',', $onlineauthors)).") AND invisible=0");
		$onlineauthors = array();
		while($author = $db->fetch_array($query)) {
			$onlineauthors[$author['uid']] = 1;
		}
	} else {
		$onlineauthors = array();
	}
	if($ratelogpids) {
		$query = $db->query("SELECT * FROM {$tablepre}ratelog WHERE pid IN ($ratelogpids) ORDER BY dateline DESC");
		while($ratelog = $db->fetch_array($query)) {
			if(count($postlist[$ratelog['pid']]['ratelog']) < $ratelogrecord) {
				$ratelog['dateline'] = gmdate("$dateformat $timeformat", $ratelog['dateline'] + $timeoffset * 3600);
				$ratelog['score'] = $ratelog['score'] > 0 ? '+'.$ratelog['score'] : $ratelog['score'];
				$ratelog['reason'] = dhtmlspecialchars($ratelog['reason']);
				$postlist[$ratelog['pid']]['ratelog'][] = $ratelog;
			}
		}
	}

	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $postlist, $showimages);
	}

	viewthread_parsetags();

	if(empty($postlist)) {
		showmessage('undefined_action', NULL, 'HALTED');
	} else {
		$seodescription = current($postlist);
		$seodescription = cutstr(htmlspecialchars(strip_tags($seodescription['message'])), 150);
	}

	include template('viewthread');

} elseif($action == 'printable' && $tid) {

	require_once DISCUZ_ROOT.'./include/printable.inc.php';

}

function viewthread_updateviews() {
	global $delayviewcount, $supe_pushstatusadd, $timestamp, $tablepre, $tid, $db, $adminid;
	
	if(($delayviewcount == 1 || $delayviewcount == 3) && !$supe_pushstatusadd) {
		$logfile = './forumdata/cache/cache_threadviews.log';
		if(substr($timestamp, -2) == '00') {
			require_once DISCUZ_ROOT.'./include/misc.func.php';
			updateviews('threads', 'tid', 'views', $logfile);
		}
		if(@$fp = fopen(DISCUZ_ROOT.$logfile, 'a')) {
			fwrite($fp, "$tid\n");
			fclose($fp);
		} elseif($adminid == 1) {
			showmessage('view_log_invalid');
		}
	} else {

		$db->query("UPDATE LOW_PRIORITY {$tablepre}threads SET views=views+1 $supe_pushstatusadd WHERE tid='$tid'", 'UNBUFFERED');
	}
	unset($supe_pushstatusadd);
}

function viewthread_procpost($post, $special = 0) {

	global $_DCACHE, $newpostanchor, $numpost, $thisbg, $postcount, $ratelogpids, $onlineauthors, $lastvisit, $thread,
		$attachpids, $attachtags, $forum, $dateformat, $timeformat, $timeoffset, $userstatusby, $allowgetattach,
		$allowpaytoauthor, $ratelogrecord, $showimages, $forum, $discuz_uid, $showavatars, $pagebydesc, $ppp, $ppp2,$ppp3,
		$firstpid, $videoopen;

	if(!$newpostanchor && $post['dateline'] > $lastvisit) {
		$post['newpostanchor'] = '<a name="newpost"></a>';
		$newpostanchor = 1;
	} else {
		$post['newpostanchor'] = '';
	}

	$post['lastpostanchor'] = $numpost == $thread['replies'] ? '<a name="lastpost"></a>' : '';


	if($pagebydesc) {
		$post['number'] = $numpost + $ppp2--;
		$post['count'] = $ppp == $ppp3 ? $ppp - $postcount - 1 : $ppp3 - $postcount - 1;
	} else {
		$post['number'] = ++$numpost;
		$post['count'] = $postcount;
	}

	$postcount++;

	$post['dbdateline'] = $post['dateline'];
	$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
	$post['groupid'] = $_DCACHE['usergroups'][$post['groupid']] ? $post['groupid'] : 7;

	if($post['username']) {
		$onlineauthors[] = $post['authorid'];
		$post['usernameenc'] = rawurlencode($post['username']);
		!$special && $post['groupid'] = getgroupid($post['authorid'], $_DCACHE['usergroups'][$post['groupid']], $post);
		$post['readaccess'] = $_DCACHE['usergroups'][$post['groupid']]['readaccess'];
		if($_DCACHE['usergroups'][$post['groupid']]['userstatusby'] == 1) {
			$post['authortitle'] = $_DCACHE['usergroups'][$post['groupid']]['grouptitle'];
			$post['stars'] = $_DCACHE['usergroups'][$post['groupid']]['stars'];
		} elseif($_DCACHE['usergroups'][$post['groupid']]['userstatusby'] == 2) {
			foreach($_DCACHE['ranks'] as $rank) {
				if($post['posts'] > $rank['postshigher']) {
					$post['authortitle'] = $rank['ranktitle'];
					$post['stars'] = $rank['stars'];
					break;
				}
			}
		}

		$post['alipay'] = $allowpaytoauthor ? $post['alipay']: '';
		$post['taobaoas'] = addslashes($post['taobao']);
		$post['authoras'] = !$post['anonymous'] ? ' '.addslashes($post['author']) : '';
		$post['regdate'] = gmdate($dateformat, $post['regdate'] + $timeoffset * 3600);
		$post['lastdate'] = gmdate($dateformat, $post['lastactivity'] + $timeoffset * 3600);
		$post['allowuseblog'] = $_DCACHE['usergroups'][$post['groupid']]['allowuseblog'];

		if($post['medals']) {
			@include_once DISCUZ_ROOT.'./forumdata/cache/cache_medals.php';
			foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid) {
				if(isset($_DCACHE['medals'][$medalid])) {
					$post['medals'][$key] = $_DCACHE['medals'][$medalid];
				} else {
					unset($post['medals'][$key]);
				}
			}
		}
		if($showavatars) {
			if($_DCACHE['usergroups'][$post['groupid']]['allowavatar'] && $post['avatar']) {
				$post['avatar'] = '<div class="avatar"><img src="'.$post['avatar'].'" width="'.$post['avatarwidth'].'" height="'.$post['avatarheight'].'" border="0" alt="" />';
			} else {
				$post['avatar'] = '<div class="avatar"><img class="avatar" src="images/avatars/noavatar.gif" alt="" />';
			}
			if($_DCACHE['usergroups'][$post['groupid']]['groupavatar']) {
				$post['avatar'] .= '<br /><img src="'.$_DCACHE['usergroups'][$post['groupid']]['groupavatar'].'" border="0" alt="" />';
			}
			$post['avatar'] .= '</div>';
		}

	} else {
		if(!$post['authorid']) {
			$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
		}
	}
	$post['attachments'] = array();
	if($post['attachment']) {
		if($allowgetattach && !$threadpay) {
			$attachpids .= ",$post[pid]";
			$post['attachment'] = 0;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
				$attachtags[$post['pid']] = $matchaids[1];
			}
		} else {
			$post['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $post['message']);
		}
	}

	$ratelogpids .= ($ratelogrecord && $post['ratetimes']) ? ','.$post['pid'] : '';
	$forum['allowbbcode'] = $forum['allowbbcode'] ? ($_DCACHE['usergroups'][$post['groupid']]['allowcusbbcode'] ? 2 : 1) : 0;
	$post['ratings'] = karmaimg($post['rate'], $post['ratetimes']);
	$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $showimages ? 1 : 0), $forum['allowhtml'], ($forum['jammer'] && $post['authorid'] != $discuz_uid ? 1 : 0), 0, $post['authorid'], $forum['allowmediacode'], $post['pid']);
	$videoopen && $post['message'] = videocode($post['message'], $post['tid'], $post['pid']);
	$post['signature'] = $post['usesig'] ? $post['signature'] : '';
	$post['first'] && $firstpid = $post['pid'];
	return $post;
}

function showoption($var, $type) {
	global $optionlist;
	if($optionlist[$var][$type]) {
		return $optionlist[$var][$type];
	} else {
		return 'none';
	}
}

function viewthread_loadcache() {
	global $tid, $forum, $timestamp, $cachethreadlife, $_DCACHE, $gzipcompress, $supe_pushstatusadd, $debug, $styleid;
	$forum['livedays'] = ceil(($timestamp - $forum['dateline']) / 86400);
	$forum['lastpostdays'] = ceil(($timestamp - $forum['lastthreadpost']) / 86400);

	$threadcachemark = 100 - (
		$forum['displayorder'] * 15 +
		$forum['digest'] * 10 +
		min($forum['views'] / max($forum['livedays'], 10) * 2, 50) +
		max(-10, (15 - $forum['lastpostdays'])) +
		min($forum['replies'] / $_DCACHE['settings']['postperpage'] * 1.5, 15));
	if($threadcachemark < $forum['threadcaches']) {

		$threadcache = getcacheinfo($tid);

		if($timestamp - $threadcache['filemtime'] > $cachethreadlife) {
			@unlink($threadcache['filename']);
			define('CACHE_FILE', $threadcache['filename']);
			$styleid = $_DCACHE['settings']['styleid'];
			@include DISCUZ_ROOT.'./forumdata/cache/style_'.$styleid.'.php';
		} else {
			readfile($threadcache['filename']);

			$supe_pushstatusadd = '';
			viewthread_updateviews();
			$debug && debuginfo();
			$debug ? die('<script type="text/javascript">document.getElementById("debuginfo").innerHTML = " '.($debug ? 'Update at '.gmdate("H:i:s", $threadcache['filemtime'] + 3600 * 8).', Processed in '.$debuginfo['time'].' second(s), '.$debuginfo['queries'].' Queries'.($gzipcompress ? ', Gzip enabled' : '') : '').'";</script>') : die();
		}
	}
}

function viewthread_lastmod() {
	global $db, $tablepre, $dateformat, $timeformat, $timeoffset, $tid;
	if($lastmod = $db->fetch_first("SELECT uid AS moduid, username AS modusername, dateline AS moddateline, action AS modaction, magicid
		FROM {$tablepre}threadsmod
		WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1")) {
		include language('modactions');
		$lastmod['modusername'] = $lastmod['modusername'] ? $lastmod['modusername'] : 'System';
		$lastmod['moddateline'] = gmdate("$dateformat $timeformat", $lastmod['moddateline'] + $timeoffset * 3600);
		$lastmod['modaction'] = $modactioncode[$lastmod['modaction']];
		if($lastmod['magicid']) {
			require_once DISCUZ_ROOT.'./forumdata/cache/cache_magics.php';
			$lastmod['magicname'] = $_DCACHE['magics'][$lastmod['magicid']]['name'];
		}
	} else {
		$db->query("UPDATE {$tablepre}threads SET moderated='0' WHERE tid='$tid'", 'UNBUFFERED');
	}
	return $lastmod;
}

function viewthread_parsetags() {
	global $tagstatus, $_DCACHE, $firstpid, $postlist;
	if($firstpid && $tagstatus && !($postlist[$firstpid]['htmlon'] & 2) && !empty($_DCACHE['tags'])) {
		$postlist[$firstpid]['message'] = '<div id="firstpost">'.$postlist[$firstpid]['message'].'</div><script type="text/javascript">var tagarray = '.$GLOBALS['_DCACHE']['tags'][0].';var tagencarray = '.$GLOBALS['_DCACHE']['tags'][1].';parsetag();</script>';
	}
}

function remaintime($time) {
	$seconds 	= $time % 60;
	$minutes 	= $time % 3600 / 60;
	$hours 		= $time % 86400 / 3600;
	$days 		= $time / 86400;
	return array((int)$days, (int)$hours, (int)$minutes, (int)$seconds);
}

?>