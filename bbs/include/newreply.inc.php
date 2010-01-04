<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: newreply.inc.php 13219 2008-04-01 02:54:58Z heyond $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$discuz_action = 12;

if($special == 5) {
	$debate = array_merge($thread, $db->fetch_first("SELECT * FROM {$tablepre}debates WHERE tid='$tid'"));
	$standquery = $db->query("SELECT stand FROM {$tablepre}debateposts WHERE tid='$tid' AND uid='$discuz_uid' AND stand<>'0' ORDER BY dateline LIMIT 1");
	$firststand = $db->result($standquery, 0);
	if($debate['endtime'] && $debate['endtime'] < $timestamp) {
		showmessage('debate_end');
	}
}

if($iscircle && ($sgid = $thread['sgid'])) {
	supe_dbconnect();
        $query = $supe['db']->query("SELECT g.ispublic, g.allowshare, g.password, g.groupname, gf.headerimage, gf.css FROM {$supe[tablepre]}groups g, {$supe[tablepre]}groupfields gf WHERE g.gid='$sgid' AND g.flag=1 AND g.gid=gf.gid", 'SILENT');
        $circle = $supe['db']->fetch_array($query);
        $incircle = $discuz_uid ? $supe['db']->result($supe['db']->query("SELECT COUNT(*) FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND gid='$sgid' AND flag>0", 'SILENT'), 0) : 0;
        if(!$incircle && !($circle['allowshare'] && ($circle['ispublic'] == 1 || ($circle['ispublic'] == 2 && $circle['password'] == $_DCOOKIE['gidpw'.$gid])))) {
        	showmessage('circle_nopermission');
        }
}

if(!$discuz_uid && !((!$forum['replyperm'] && $allowreply) || ($forum['replyperm'] && forumperm($forum['replyperm'])))) {
	showmessage('group_nopermission', NULL, 'NOPERM');
} elseif(empty($forum['allowreply'])) {
	if(!$forum['replyperm'] && !$allowreply) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($forum['replyperm'] && !forumperm($forum['replyperm'])) {
		showmessage('post_forum_newreply_nopermission', NULL, 'HALTED');
	}
}

if(empty($thread)) {
	showmessage('thread_nonexistence');
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$discuz_uid) {
	showmessage('group_nopermission', NULL, 'NOPERM');
}

checklowerlimit($replycredits);

if(!supe_submitcheck() && !submitcheck('replysubmit', 0, $seccodecheck, $secqaacheck)) {

	if($thread['special'] == 2 && ((!isset($addtrade) || $thread['authorid'] != $discuz_uid) && !$tradenum = $db->result($db->query("SELECT count(*) FROM {$tablepre}trades WHERE tid='$tid'"), 0))) {
		showmessage('trade_newreply_nopermission', NULL, 'HALTED');
	}

	include_once language('misc');
	if(isset($repquote)) {

		$thaquote = $db->fetch_first("SELECT tid, fid, author, authorid, first, message, useip, dateline, anonymous, status FROM {$tablepre}posts WHERE pid='$repquote' AND invisible='0'");
		if($thaquote['tid'] != $tid) {
			showmessage('undefined_action', NULL, 'HALTED');
		}

		if(!($thread['price'] && !$thread['special'] && $thaquote['first'])) {
			$quotefid = $thaquote['fid'];
			$message = $thaquote['message'];

			if($bannedmessages && $thaquote['authorid']) {
				$author = $db->fetch_first("SELECT groupid FROM {$tablepre}members WHERE uid='$thaquote[authorid]'");
				if(!$author['groupid'] || $author['groupid'] == 4 || $author['groupid'] == 5) {
					$message = $language['post_banned'];
				} elseif($thaquote['status']) {
					$message = $language['post_single_banned'];
				}
			}

			$time = gmdate("$dateformat $timeformat", $thaquote['dateline'] + ($timeoffset * 3600));
			$bbcodes = 'b|i|u|color|size|font|align|list|indent|url|email|code|free|table|tr|td|img|swf|attach|payto|float'.($_DCACHE['bbcodes_display'] ? '|'.implode('|', array_keys($_DCACHE['bbcodes_display'])) : '');
			$message = cutstr(strip_tags(preg_replace(array(
					"/\[hide=?\d*\](.+?)\[\/hide\]/is",
					"/\[quote](.*)\[\/quote]/siU",
					$language['post_edit_regexp'],
					"/\[($bbcodes)=?.*\]/iU",
					"/\[\/($bbcodes)\]/i",
				), array(
					"[b]$language[post_hidden][/b]",
					'',
					'',
					'',
					''
				), $message)), 200);

			$thaquote['useip'] = substr($thaquote['useip'], 0, strrpos($thaquote['useip'], '.')).'.x';
			if($thaquote['author'] && $thaquote['anonymous']) {
			    $thaquote['author'] = '[i]Anonymous[/i]';
			} elseif(!$thaquote['author']) {
			    $thaquote['author'] = '[i]Guest[/i] from '.$thaquote['useip'];
			} else {
			    $thaquote['author'] = '[i]'.$thaquote['author'].'[/i]';
			}

			eval("\$language['post_reply_quote'] = \"$language[post_reply_quote]\";");
			$message = "[quote]$language[post_reply_quote] [url={$boardurl}redirect.php?goto=findpost&pid=$repquote&ptid=$tid][img]{$boardurl}images/common/back.gif[/img][/url]\n$message [/quote]\n";
		}

	}

	if(isset($addtrade) && $thread['special'] == 2 && $allowposttrade && $thread['authorid'] == $discuz_uid) {
		$expiration_7days = date('Y-m-d', $timestamp + 86400 * 7);
		$expiration_14days = date('Y-m-d', $timestamp + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
	}

	if($thread['replies'] <= $ppp) {
		$postlist = array();
		$query = $db->query("SELECT p.* ".($bannedmessages ? ', m.groupid ' : '').
			"FROM {$tablepre}posts p ".($bannedmessages ? "LEFT JOIN {$tablepre}members m ON p.authorid=m.uid " : '').
			"WHERE p.tid='$tid' AND p.invisible='0' ".($thread['price'] > 0 && $thread['special'] == 0 ? 'AND p.first = 0' : '')." ORDER BY p.dateline DESC");
		while($post = $db->fetch_array($query)) {

			$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);

			if($bannedmessages && ($post['authorid'] && (!$post['groupid'] || $post['groupid'] == 4 || $post['groupid'] == 5))) {
				$post['message'] = $language['post_banned'];
			} elseif($post['status']) {
				$post['message'] = $language['post_single_banned'];
			} else {
				$post['message'] = preg_replace("/\[hide=?\d*\](.+?)\[\/hide\]/is", "[b]$language[post_hidden][/b]", $post['message']);
				$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], $forum['allowimgcode'], $forum['allowhtml'], $forum['jammer']);
			}

			$postlist[] = $post;
		}
	}

	if($special == 2 && isset($addtrade) && $thread['authorid'] == $discuz_uid) {
		$tradetypeselect = '';
		$forum['tradetypes'] = $forum['tradetypes'] == '' ? -1 : unserialize($forum['tradetypes']);
		if($tradetypes && !empty($forum['tradetypes'])) {
			$tradetypeselect = '<select name="tradetypeid" onchange="ajaxget(\'post.php?action=threadtypes&tradetype=yes&typeid=\'+this.options[this.selectedIndex].value+\'&sid='.$sid.'\', \'threadtypes\', \'threadtypeswait\')"><option value="0">&nbsp;</option>';
			foreach($tradetypes as $typeid => $name) {
				if($forum['tradetypes'] == -1 || @in_array($typeid, $forum['tradetypes'])) {
					$tradetypeselect .= '<option value="'.$typeid.'">'.strip_tags($name).'</option>';
				}
			}
			$tradetypeselect .= '</select><span id="threadtypeswait"></span>';
		}
		include template('post_newreply_trade');
	} else {
		include template('post_newreply');
	}

} else {

	require_once DISCUZ_ROOT.'./include/forum.func.php';

	if($subject == '' && $message == '') {
		showmessage('post_sm_isnull');
	} elseif($thread['closed'] && !$forum['ismoderator']) {
		showmessage('post_thread_closed');
	} elseif($post_autoclose = checkautoclose()) {
		showmessage($post_autoclose);
	} elseif($post_invalid = checkpost()) {
		showmessage($post_invalid);
	} elseif(checkflood()) {
		showmessage('post_flood_ctrl');
	}

	if(!empty($trade) && $thread['special'] == 2 && $allowposttrade) {

		$item_price = floatval($item_price);
		if(!trim($item_name)) {
			showmessage('trade_please_name');
		} elseif($maxtradeprice && ($mintradeprice > $item_price || $maxtradeprice < $item_price)) {
			showmessage('trade_price_between');
		} elseif(!$maxtradeprice && $mintradeprice > $item_price) {
			showmessage('trade_price_more_than');
		} elseif($item_number < 1) {
			showmessage('tread_please_number');
		}

		threadtype_checkoption(1, 1);

		$optiondata = array();
		if($tradetypes && $typeoption && $checkoption) {
			$optiondata = threadtype_validator($typeoption);
		}

		if(!empty($_FILES['tradeattach']['tmp_name'][0])) {
			$_FILES['attach'] = array_merge_recursive((array)$_FILES['attach'], $_FILES['tradeattach']);
		}

	}

	$attachnum = 0;
	if($allowpostattach && !empty($_FILES['attach']) && is_array($_FILES['attach'])) {
		foreach($_FILES['attach']['name'] as $attachname) {
			if($attachname != '') {
				$attachnum ++;
			}
		}
		$attachnum && checklowerlimit($postattachcredits, $attachnum);
	} else {
		$_FILES = array();
	}

	$attachments = $attachnum ? attach_upload() : array();
	$attachment = empty($attachments) ? 0 : 1;

	$subscribed = $thread['subscribed'] && $timestamp - $thread['lastpost'] < 7776000;
	$newsubscribed = !empty($emailnotify) && $discuz_uid;

	if($subscribed && !$modnewreplies) {
		$db->query("UPDATE {$tablepre}subscriptions SET lastpost='$timestamp' WHERE tid='$tid' AND uid<>'$discuz_uid'", 'UNBUFFERED');
	}

	if($newsubscribed) {
		$db->query("REPLACE INTO {$tablepre}subscriptions (uid, tid, lastpost, lastnotify)
			VALUES ('$discuz_uid', '$tid', '".($modnewreplies ? $thread['lastpost'] : $timestamp)."', '$timestamp')", 'UNBUFFERED');
	}

	$bbcodeoff = checkbbcodes($message, !empty($bbcodeoff));
	$smileyoff = checksmilies($message, !empty($smileyoff));
	$parseurloff = !empty($parseurloff);
	$htmlon = $allowhtml && !empty($htmlon) ? 1 : 0;
	$usesig = !empty($usesig) ? 1 : 0;

	$isanonymous = $allowanonymous && !empty($isanonymous)? 1 : 0;
	$author = empty($isanonymous) ? $discuz_user : '';

	$pinvisible = $modnewreplies ? -2 : 0;
	$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
			VALUES ('$fid', '$tid', '0', '$discuz_user', '$discuz_uid', '$subject', '$timestamp', '$message', '$onlineip', '$pinvisible', '$isanonymous', '$usesig', '$htmlon', '$bbcodeoff', '$smileyoff', '$parseurloff', '$attachment')");
	$pid = $db->insert_id();
	$db->query("REPLACE INTO {$tablepre}myposts (uid, tid, pid, position, dateline, special) VALUES ('$discuz_uid', '$tid', '$pid', '".($thread['replies'] + 1)."', '$timestamp', '$special')", 'UNBUFFERED');

	if($special == 3 && $thread['authorid'] != $discuz_uid && $thread['price'] > 0) {

		$rewardlog = $db->fetch_first("SELECT * FROM {$tablepre}rewardlog WHERE tid='$tid' AND answererid='$discuz_uid'");
		if(!$rewardlog) {
			$db->query("INSERT INTO {$tablepre}rewardlog (tid, answererid, dateline) VALUES ('$tid', '$discuz_uid', '$timestamp')");
		}

	} elseif($special == 5) {

		$stand = intval($stand);

		if(!$db->num_rows($standquery)) {
			if($stand == 1) {
				$db->query("UPDATE {$tablepre}debates SET affirmdebaters=affirmdebaters+1 WHERE tid='$tid'");
			} elseif($stand == 2) {
				$db->query("UPDATE {$tablepre}debates SET negadebaters=negadebaters+1 WHERE tid='$tid'");
			}
		} else {
			$stand = $firststand;
		}
		if($stand == 1) {
			$db->query("UPDATE {$tablepre}debates SET affirmreplies=affirmreplies+1 WHERE tid='$tid'");
		} elseif($stand == 2) {
			$db->query("UPDATE {$tablepre}debates SET negareplies=negareplies+1 WHERE tid='$tid'");
		}
		$db->query("INSERT INTO {$tablepre}debateposts (tid, pid, uid, dateline, stand, voters, voterids) VALUES ('$tid', '$pid', '$discuz_uid', '$timestamp', '$stand', '0', '')");
	}

	$tradeaid = 0;
	if($attachment) {
		$searcharray = $pregarray = $replacearray = array();
		foreach($attachments as $key => $attach) {
			$db->query("INSERT INTO {$tablepre}attachments (tid, pid, dateline, readperm, price, filename, description, filetype, filesize, attachment, downloads, isimage, uid, thumb, remote)
				VALUES ('$tid', '$pid', '$timestamp', '$attach[perm]', '$attach[price]', '$attach[name]', '$attach[description]', '$attach[type]', '$attach[size]', '$attach[attachment]', '0', '$attach[isimage]', '$attach[uid]', '$attach[thumb]', '$attach[remote]')");
			$searcharray[] = '[local]'.$localid[$key].'[/local]';
			$pregarray[] = '/\[localimg=(\d{1,3}),(\d{1,3})\]'.$localid[$key].'\[\/localimg\]/is';
			$insertid = $db->insert_id();
			$replacearray[] = '[attach]'.$insertid.'[/attach]';
		}
		if(!empty($trade) && $thread['special'] == 2 && !empty($_FILES['tradeattach']['tmp_name'][0])) {
			$tradeaid = $insertid;
		}
		$message = str_replace($searcharray, $replacearray, preg_replace($pregarray, $replacearray, $message));
		$db->query("UPDATE {$tablepre}posts SET message='$message' WHERE pid='$pid'");
		updatecredits($discuz_uid, $postattachcredits, count($attachments));
	}

	$replymessage = 'post_reply_succeed';

	if($special == 2 && $allowposttrade && $thread['authorid'] == $discuz_uid && !empty($trade) && !empty($item_name) && !empty($item_price)) {

		if($tradetypes && $optiondata) {
			foreach($optiondata as $optionid => $value) {
				$db->query("INSERT INTO {$tablepre}tradeoptionvars (typeid, pid, optionid, value)
					VALUES ('$tradetypeid', '$pid', '$optionid', '$value')");
			}
		}

		require_once DISCUZ_ROOT.'./api/alipayapi.php';
		trade_create(array(
			'tid' => $tid,
			'pid' => $pid,
			'aid' => $tradeaid,
			'typeid' => $tradetypeid,
			'item_expiration' => $item_expiration,
			'thread' => $thread,
			'discuz_uid' => $discuz_uid,
			'author' => $author,
			'seller' => $seller,
			'item_name' => $item_name,
			'item_price' => $item_price,
			'item_number' => $item_number,
			'item_quality' => $item_quality,
			'item_locus' => $item_locus,
			'transport' => $transport,
			'postage_mail' => $postage_mail,
			'postage_express' => $postage_express,
			'postage_ems' => $postage_ems,
			'item_type' => $item_type,
			'item_costprice' => $item_costprice
		));

		$replymessage = 'trade_add_succeed';

	}

	if($thread['replies'] < $ppp) {
		$forum['threadcaches'] && deletethreadcaches($tid);
	}

	if($modnewreplies) {
		$db->query("UPDATE {$tablepre}forums SET todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');

		if($newsubscribed) {
			$db->query("UPDATE {$tablepre}threads SET subscribed='1' WHERE tid='$tid'", 'UNBUFFERED');
		}
		if(!$allowuseblog || empty($isblog)) {
			showmessage('post_reply_mod_succeed', "forumdisplay.php?fid=$fid");
		} else {
			showmessage('post_reply_mod_blog_succeed', "blog.php?tid=$tid&starttime=$starttime&endtime=$endtime&page=$page");
		}
	} else {
		$db->query("UPDATE {$tablepre}threads SET lastposter='$author', lastpost='$timestamp', replies=replies+1 ".($attachment ? ', attachment=\'1\'' : '').", subscribed='".($subscribed || $newsubscribed ? 1 : 0)."' WHERE tid='$tid'", 'UNBUFFERED');

		updatepostcredits('+', $discuz_uid, $replycredits);

		$lastpost = "$thread[tid]\t".addslashes($thread['subject'])."\t$timestamp\t$author";
		$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
		if($forum['type'] == 'sub') {
			$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
		}

		if(!$allowuseblog || empty($isblog)) {
			showmessage($replymessage, "viewthread.php?tid=$tid&pid=$pid&page=".(@ceil(($thread['special'] ? $thread['replies'] + 1 : $thread['replies'] + 2) / $ppp))."&extra=$extra#pid$pid");
		} else {
			showmessage('post_reply_blog_succeed', "blog.php?tid=$tid&starttime=$starttime&endtime=$endtime&page=".(@ceil(($thread['replies'] + 1) / $ppp))."#bottom");
		}
	}

}

?>