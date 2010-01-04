<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: moderation.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || CURSCRIPT != 'topicadmin') {
	exit('Access Denied');
}

if($action != 'moderate') {
	$operation = $action;
	$action = 'moderate';
	$moderate = array($tid);
}

if(!in_array($operation, array('delete', 'move', 'copy', 'highlight', 'type', 'close', 'stick', 'digest', 'supe_push', 'removereward', 'bump', 'recommend')) || (!$allowdelpost && !$iscircleadmin && $operation == 'delete') || (!$allowstickthread && $operation == 'stick')) {
	showmessage('admin_moderate_invalid');
}

$threadlist = $loglist = array();
if($tids = implodeids($moderate)) {
	$sgidadd = $iscircleadmin ? "AND sgid='$gid'" : '';
	$query = $db->query("SELECT * FROM {$tablepre}threads WHERE tid IN ($tids) AND fid='$fid' AND displayorder>='0' AND digest>='0' $sgidadd LIMIT $tpp");
	while($thread = $db->fetch_array($query)) {
		$thread['lastposterenc'] = rawurlencode($thread['lastposter']);
		$thread['dblastpost'] = $thread['lastpost'];
		$thread['lastpost'] = gmdate("$dateformat $timeformat", $thread['lastpost'] + $timeoffset * 3600);
		$threadlist[$thread['tid']] = $thread;
		$tid = empty($tid) ? $thread['tid'] : $tid;
	}
}

if(empty($threadlist)) {
	showmessage('admin_moderate_invalid');
}

$modpostsnum = count($threadlist);
$single = $modpostsnum == 1 ? TRUE : FALSE;
$referer = "forumdisplay.php?fid=$fid";

if(!submitcheck('modsubmit')) {
	if($operation == 'move') {
		require_once DISCUZ_ROOT.'./include/forum.func.php';
		$forumselect = forumselect();
	} elseif($operation == 'highlight') {
		$stylecheck = array();
		$colorcheck = array(0 => 'checked="checked"');
		if($single) {
			$string = sprintf('%02d', $threadlist[$tid]['highlight']);
			$stylestr = sprintf('%03b', $string[0]);
			for($i = 1; $i <= 3; $i++) {
				$stylecheck[$i] = $stylestr[$i - 1] ? 'checked="checked"' : '';
			}
			$colorcheck = array($string[1] => 'checked="checked"');
		}
	} elseif($operation == 'type') {
		$typeselect = typeselect('', 1);
	}

	if(in_array($operation, array('stick', 'digest', 'highlight', 'close', 'supe_push', 'recommend'))) {

		$expirationmin = gmdate($dateformat, $timestamp + 86400 + $timeoffset * 3600);
		$expirationmax = gmdate($dateformat, $timestamp + 86400 * 180 + $timeoffset * 3600);

		$expirationdefault = '';
		$stickcheck  = $digestcheck = $closecheck = $supe_pushstatus = array();

		if($single) {

			empty($threadlist[$tid]['displayorder']) ? $stickcheck[1] ='checked="checked"' : $stickcheck[$threadlist[$tid]['displayorder']] = 'checked="checked"';
			empty($threadlist[$tid]['digest']) ? $digestcheck[1] = 'checked="checked"' : $digestcheck[$threadlist[$tid]['digest']] = 'checked="checked"';
			empty($threadlist[$tid]['supe_pushstatus']) ? $supe_pushstatus[2] = 'checked="checked"' : $supe_pushstatus[$threadlist[$tid]['supe_pushstatus']] = 'checked="checked"';
			empty($threadlist[$tid]['closed']) ? $closecheck[0] = 'checked="checked"' : $closecheck[1] = 'checked="checked"';

			if($threadlist[$tid]['moderated']) {
				switch($operation) {
					case 'stick': $actionarray = array('EST'); break;
					case 'digest': $actionarray = array('EDI'); break;
					case 'highlight': $actionarray = array('EHL'); break;
					case 'close': $actionarray = array('ECL', 'EOP'); break;
					case 'supe_push': $actionarray = array('PTS', 'RFS'); break;
					default: $actionarray = array();
				}
				$query = $db->query("SELECT * FROM {$tablepre}threadsmod WHERE tid='{$threadlist[0][tid]}' ORDER BY dateline DESC");
				while($log = $db->fetch_array($query)) {
					$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
					$log['expiration'] = !empty($log['expiration']) ? gmdate("$dateformat", $log['expiration'] + $timeoffset * 3600) : '';
					if($log['status'] && in_array($log['action'], $actionarray)) {
						$expirationdefault = $log['expiration'];
					}
					$log['status'] = empty($log['status']) ? 'style="text-decoration: line-through" disabled' : '';
					$loglist[] = $log;
				}
				if(!empty($loglist)) {
					include_once language('modactions');
				}
			}
		}
	}

	include template('topicadmin_moderate');

} else {

	$moderatetids = implodeids(array_keys($threadlist));
	checkreasonpm();

	if($operation == 'delete') {

		$stickmodify = 0;
		foreach($threadlist as $thread) {
			if($thread['digest']) {
				updatecredits($thread['authorid'], $digestcredits, -$thread['digest'], 'digestposts=digestposts-1');
			}
			if(in_array($thread['displayorder'], array(2, 3))) {
				$stickmodify = 1;
			}
		}

		$losslessdel = $losslessdel > 0 ? $timestamp - $losslessdel * 86400 : 0;

		//Update members' credits and post counter
		$uidarray = $tuidarray = $ruidarray = array();
		$query = $db->query("SELECT first, authorid, dateline FROM {$tablepre}posts WHERE tid IN ($moderatetids)");
		while($post = $db->fetch_array($query)) {
			if($post['dateline'] < $losslessdel) {
				$uidarray[] = $post['authorid'];
			} else {
				if($post['first']) {
					$tuidarray[] = $post['authorid'];
				} else {
					$ruidarray[] = $post['authorid'];
				}
			}
		}

		if($uidarray) {
			updatepostcredits('-', $uidarray, array());
		}
		if($tuidarray) {
			updatepostcredits('-', $tuidarray, $postcredits);
		}
		if($ruidarray) {
			updatepostcredits('-', $ruidarray, $replycredits);
		}
		$modaction = 'DEL';

		if($forum['recyclebin']) {

			$db->query("UPDATE {$tablepre}threads SET displayorder='-1', digest='0', moderated='1' WHERE tid IN ($moderatetids)");
			$db->query("UPDATE {$tablepre}posts SET invisible='-1' WHERE tid IN ($moderatetids)");

		} else {

			$auidarray = array();

			$query = $db->query("SELECT uid, attachment, dateline, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($moderatetids)");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
				if($attach['dateline'] > $losslessdel) {
					$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
				}
			}

			if($auidarray) {
				updateattachcredits('-', $auidarray, $postattachcredits);
			}

			$videoopen && videodelete($moderate, TRUE);

			foreach(array('threads', 'threadsmod', 'relatedthreads', 'posts', 'polls', 'polloptions', 'trades', 'activities', 'activityapplies', 'debates', 'videos', 'debateposts', 'attachments', 'favorites', 'mythreads', 'myposts', 'subscriptions', 'typeoptionvars', 'forumrecommend') as $value) {
				$db->query("DELETE FROM {$tablepre}$value WHERE tid IN ($moderatetids)", 'UNBUFFERED');
			}

		}

		if($globalstick && $stickmodify) {
			require_once DISCUZ_ROOT.'./include/cache.func.php';
			updatecache('globalstick');
		}

		updateforumcount($fid);

	} else {

		if(isset($expiration) && !empty($expiration) && in_array($operation, array('stick', 'digest', 'highlight', 'close'))) {
			$expiration = strtotime($expiration) - $timeoffset * 3600 + date('Z');
			if(gmdate('Ymd', $expiration + $timeoffset * 3600) <= gmdate('Ymd', $timestamp + $timeoffset * 3600) || ($expiration > $timestamp + 86400 * 180)) {
				showmessage('admin_expiration_invalid');
			}
		} else {
			$expiration = 0;
		}

		if($operation == 'stick' || $operation == 'digest') {

			$level = intval($level);
			if($level < 0 || $level > 3 || ( $operation == 'stick' && $level > $allowstickthread)) {
				showmessage('undefined_action');
			}

			$expiration = $level ? $expiration : 0;

			if($operation == 'stick') {

				$db->query("UPDATE {$tablepre}threads SET displayorder='$level', moderated='1' WHERE tid IN ($moderatetids)");

				$stickmodify = 0;
				foreach($threadlist as $thread) {
					$stickmodify = (in_array($thread['displayorder'], array(2, 3)) || in_array($level, array(2, 3))) && $level != $thread['displayorder'] ? 1 : $stickmodify;
				}

				if($globalstick && $stickmodify) {
					require_once DISCUZ_ROOT.'./include/cache.func.php';
					updatecache('globalstick');
				}

				$modaction = $level ? ($expiration ? 'EST' : 'STK') : 'UST';
				$db->query("UPDATE {$tablepre}threadsmod SET status='0' WHERE tid IN ($moderatetids) AND action IN ('STK', 'UST', 'EST', 'UES')", 'UNBUTTERED');

			} elseif($operation == 'digest') {

				$db->query("UPDATE {$tablepre}threads SET digest='$level', moderated='1' WHERE tid IN ($moderatetids)");

				foreach($threadlist as $thread) {
					if($thread['digest'] != $level) {
						$digestpostsadd = ($thread['digest'] > 0 && $level == 0) || ($thread['digest'] == 0 && $level > 0) ? 'digestposts=digestposts'.($level == 0 ? '-' : '+').'1' : '';
						updatecredits($thread['authorid'], $digestcredits, $level - $thread['digest'], $digestpostsadd);
					}
				}

				$modaction = $level ? ($expiration ? 'EDI' : 'DIG') : 'UDG';
				$db->query("UPDATE {$tablepre}threadsmod SET status='0' WHERE tid IN ($moderatetids) AND action IN ('DIG', 'UDI', 'EDI', 'UED')", 'UNBUTTERED');

			}
		} elseif($operation == 'close') {

			$modaction = empty($close) ? ($expiration ? 'EOP' : 'OPN'): ($expiration ? 'ECL' : 'CLS');
			$close = ($modaction == 'ECL' || $modaction == 'CLS') ? 1 : 0;

			$db->query("UPDATE {$tablepre}threads SET closed='$close', moderated='1' WHERE tid IN ($moderatetids)");
			$db->query("UPDATE {$tablepre}threadsmod SET status='0' WHERE tid IN ($moderatetids) AND action IN ('CLS','OPN', 'ECL', 'UCL', 'EOP', 'UEO')", 'UNBUTTERED');

		} elseif($operation == 'move') {

			$toforum = $db->fetch_first("SELECT fid, name, modnewposts, allowpostspecial FROM {$tablepre}forums WHERE fid='$moveto' AND status>0 AND type<>'group'");
			if(!$toforum) {
				showmessage('admin_move_invalid');
			} elseif($fid == $toforum['fid']) {
				showmessage('admin_move_illegal');
			} else {
				$moveto = $toforum['fid'];
				$modnewthreads = (!$allowdirectpost || $allowdirectpost == 1) && $toforum['modnewposts'] ? 1 : 0;
				$modnewreplies = (!$allowdirectpost || $allowdirectpost == 2) && $toforum['modnewposts'] ? 1 : 0;
				if($modnewthreads || $modnewreplies) {
					showmessage('admin_move_have_mod');
				}
			}

			if($adminid == 3) {
				if($accessmasks) {
					$accessadd1 = ', a.allowview, a.allowpost, a.allowreply, a.allowgetattach, a.allowpostattach';
					$accessadd2 = "LEFT JOIN {$tablepre}access a ON a.uid='$discuz_uid' AND a.fid='$moveto'";
				}
				$priv = $db->fetch_first("SELECT ff.postperm, m.uid AS istargetmod $accessadd1
						FROM {$tablepre}forumfields ff
						$accessadd2
						LEFT JOIN {$tablepre}moderators m ON m.fid='$moveto' AND m.uid='$discuz_uid'
						WHERE ff.fid='$moveto'");
				if((($priv['postperm'] && !in_array($groupid, explode("\t", $priv['postperm']))) || ($accessmasks && ($priv['allowview'] || $priv['allowreply'] || $priv['allowgetattach'] || $priv['allowpostattach']) && !$priv['allowpost'])) && !$priv['istargetmod']) {
					showmessage('admin_move_nopermission');
				}
			}

			$moderate = array();
			$stickmodify = 0;
			foreach($threadlist as $tid => $thread) {
				if(!$thread['special'] || substr(sprintf('%04b', $toforum['allowpostspecial']), -$thread['special'], 1)) {
					$moderate[] = $tid;
					if(in_array($thread['displayorder'], array(2, 3))) {
						$stickmodify = 1;
					}
					if($type == 'redirect') {
						$db->query("INSERT INTO {$tablepre}threads (fid, readperm, iconid, author, authorid, subject, dateline, lastpost, lastposter, views, replies, displayorder, digest, closed, special, attachment)
							VALUES ('$thread[fid]', '$thread[readperm]', '$thread[iconid]', '".addslashes($thread['author'])."', '$thread[authorid]', '".addslashes($thread['subject'])."', '$thread[dateline]', '$thread[dblastpost]', '$thread[lastposter]', '0', '0', '0', '0', '$thread[tid]', '0', '0')");
					}
				}
			}

			if(!$moderatetids = implode(',', $moderate)) {
				showmessage('admin_moderate_invalid');
			}

			$displayorderadd = $adminid == 3 ? ', displayorder=\'0\'' : '';
			$db->query("UPDATE {$tablepre}threads SET fid='$moveto', moderated='1' $displayorderadd WHERE tid IN ($moderatetids)");
			$db->query("UPDATE {$tablepre}posts SET fid='$moveto' WHERE tid IN ($moderatetids)");

			if($globalstick && $stickmodify) {
				require_once DISCUZ_ROOT.'./include/cache.func.php';
				updatecache('globalstick');
			}

			$modaction = 'MOV';

			updateforumcount($moveto);
			updateforumcount($fid);

		} elseif($operation == 'highlight') {

			$stylebin = '';
			for($i = 1; $i <= 3; $i++) {
				$stylebin .= empty($highlight_style[$i]) ? '0' : '1';
			}

			$highlight_style = bindec($stylebin);
			if($highlight_style < 0 || $highlight_style > 7 || $highlight_color < 0 || $highlight_color > 8) {
				showmessage('undefined_action', NULL, 'HALTED');
			}

			$db->query("UPDATE {$tablepre}threads SET highlight='$highlight_style$highlight_color', moderated='1' WHERE tid IN ($moderatetids)", 'UNBUFFERED');

			$modaction = ($highlight_style + $highlight_color) ? ($expiration ? 'EHL' : 'HLT') : 'UHL';
			$expiration = $modaction == 'UHL' ? 0 : $expiration;
			$db->query("UPDATE {$tablepre}threadsmod SET status='0' WHERE tid IN ($moderatetids) AND action IN ('HLT', 'UHL', 'EHL', 'UEH')", 'UNBUTTERED');

		} elseif($operation == 'type') {

			if(!isset($forum['threadtypes']['types'][$typeid]) && !($typeid == 0 && !$forum['threadtypes']['required'])) {
				showmessage('admin_move_invalid');
			}

			$db->query("UPDATE {$tablepre}threads SET typeid='$typeid', moderated='1' WHERE tid IN ($moderatetids)");

			$modaction = 'TYP';

		} elseif($operation == 'bump') {

			if($isbump) {
				$modaction = 'BMP';

				$thread = $threadlist;
				$thread = array_pop($thread);
				$thread['subject'] = addslashes($thread['subject']);
				$thread['lastposter'] = addslashes($thread['lastposter']);

				$db->query("UPDATE {$tablepre}threads SET lastpost='$timestamp', moderated='1' WHERE tid IN ($moderatetids)");
				$db->query("UPDATE {$tablepre}forums SET lastpost='$thread[tid]\t$thread[subject]\t$timestamp\t$thread[lastposter]' WHERE fid='$fid'");

			} else {
				$modaction = 'DWN';
				$downtime = $timestamp - 86400 * 730;
				$db->query("UPDATE {$tablepre}threads SET lastpost='$downtime', moderated='1' WHERE tid IN ($moderatetids)");
			}

			$forum['threadcaches'] && deletethreadcaches($thread['tid']);


		} elseif($operation == 'supe_push') {
			if(!$supe['status']) {
				showmessage('supe_pushsetting_noopen');
			}
			if(!$supe_allowpushthread) {
				showmessage('admin_nopermission');
			}
			if($forum['supe_pushsetting']['status'] != '2') {
				showmessage('supe_pushsetting_nohand');
			}
			$supe_pushstatus = in_array($supe_pushstatus, array('2', '-2')) ? $supe_pushstatus : '2';
			$db->query("UPDATE {$tablepre}threads SET supe_pushstatus='$supe_pushstatus' WHERE tid IN ($moderatetids)");

			$modaction = $supe_pushstatus == '2' ? 'PTS' : ($supe_pushstatus == '-2' ? 'RFS' : '');
		} elseif($operation == 'recommend') {

			$db->query("UPDATE {$tablepre}threads SET moderated='1' WHERE tid IN ($moderatetids)");

			$modaction = $isrecommend ? 'REC' : 'URE';

			$thread = daddslashes($thread, 1);

			$db->query("UPDATE {$tablepre}threadsmod SET status='0' WHERE tid IN ($moderatetids) AND action IN ('REC')", 'UNBUTTERED');
			if($isrecommend) {
				$recommendexpire = $recommendexpire ? intval($recommendexpire) : 0;
				$addthread = $comma = '';
				foreach($threadlist as $thread) {
					$addthread .= $comma."('$thread[fid]', '$thread[tid]', '0', '".addslashes($thread['subject'])."', '".addslashes($thread['author'])."', '$thread[authorid]', '$discuz_uid', '".($timestamp + $recommendexpire)."')";
					$comma = ', ';
				}

				if($addthread) {
					$db->query("REPLACE INTO {$tablepre}forumrecommend (fid, tid, displayorder, subject, author, authorid, moderatorid, expiration) VALUES $addthread");
				}
			} else {
				$db->query("DELETE FROM {$tablepre}forumrecommend WHERE fid='$fid' AND tid IN ($moderatetids)");
			}
		}
	}

	$resultarray = array(
	'redirect'	=> (preg_match("/^topicadmin/", ($redirect = dreferer("forumdisplay.php?fid=$fid"))) ? "forumdisplay.php?fid=$fid" : $redirect),
	'reasonpm'	=> ($sendreasonpm ? array('data' => $threadlist, 'var' => 'thread', 'item' => ($operation == 'move' ? 'reason_move' : 'reason_moderate')) : array()),
	'modtids'	=> ($operation == 'delete' && !$forum['recyclebin']) ? 0 : $moderatetids,
	'modlog'	=> $threadlist,
	'expiration'=> $expiration
	);
	if($iscircleadmin && $supe_referer) {
		$resultarray['redirect'] = $supe_referer;
	}

	if(in_array($operation, array('stick', 'digest', 'highlight')) && !empty($next) && $next != $operation && in_array($next, array('stick', 'digest', 'highlight'))) {
		if(count($moderate) == 1) {
			$resultarray['redirect'] = "topicadmin.php?tid=$moderate[0]&amp;fid=$fid&amp;action=$next";
		} else {
			$resultarray['redirect'] = "topicadmin.php?action=moderate&amp;fid=$fid&amp;operation=$next";
			if(is_array($moderate)) {
				foreach($moderate as $modtid) {
					$resultarray['redirect'] .= "&moderate[]=$modtid";
				}
			}
		}
		$resultarray['message'] = 'admin_succeed_next';
	}
}

?>