<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$RCSfile: pm.php,v $
$Revision: 1.72 $
$Date: 2007/07/23 12:36:56 $
*/

define('CURSCRIPT', 'pm');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';

if(empty($discuz_uid)) {
	showmessage('not_loggedin', NULL, 'NOPERM');
} elseif($maxpmnum == 0 && $action == 'send') {
	showmessage('group_nopermission', NULL, 'NOPERM');
}

if($action == 'noprompt') {

	$db->query("UPDATE {$tablepre}pms SET new='2' WHERE msgtoid='$discuz_uid' AND folder='inbox' AND delstatus!='2' AND new='1'");
	$db->query("UPDATE {$tablepre}members SET newpm='0' WHERE uid='$discuz_uid'");
	if($supe['status'] && $xspacestatus) {
		supe_dbconnect();
		$supe['db']->query("UPDATE {$supe[tablepre]}members SET newpm='0' WHERE uid='$discuz_uid'", 'SILENT');
	}
	showmessage('pm_noprompt_succeed', dreferer());

} elseif($inajax && $action == 'send' && !submitcheck('pmsubmit')) {

	if(isset($uid) && is_numeric($uid)) {
		$touser = dhtmlspecialchars($db->result_first("SELECT username FROM {$tablepre}members WHERE uid='$uid'"));
	} else {
		$touser = '';
	}

	$seccodecheck = substr(sprintf('%05b', $seccodestatus), -4, 1) && (!$seccodedata['minposts'] || $posts < $seccodedata['minposts']);
	$secqaacheck = $secqaa['status'][3] && (!$secqaa['minposts'] || $posts < $secqaa['minposts']);

	include template('pm_send_ajax');
	exit;
}

$discuz_action = 101;
require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

$pm_outbox = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms WHERE msgfromid='$discuz_uid' AND folder='outbox'");

$pm_inbox = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' AND delstatus!='2'");
$pm_inbox_newpm = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' AND delstatus!='2' AND new>0");

$pm_total = $pm_outbox + $pm_inbox;

@$storage_percent = round((100 * $pm_total / $maxpmnum) + 1).'%';

$ftdisabled = $allowsearch != 2 ? 'disabled' : '';
$folder = isset($folder) ? $folder : 'inbox';
$filter = isset($filter) ? $filter : '';
$action = isset($action) ? $action : '';

if(empty($action)) {

	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;
	$announce_pmlist = array();

	switch($folder) {

		case 'outbox':
			$pmnum = $pm_outbox;
			$query = $db->query("SELECT p.*, m.username AS msgto FROM {$tablepre}pms p
				LEFT JOIN {$tablepre}members m ON m.uid=p.msgtoid
				WHERE p.msgfromid='$discuz_uid' AND p.folder='outbox'
				ORDER BY p.dateline DESC LIMIT $start_limit, $tpp");
			break;

		case 'track':
			$pmnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms WHERE msgfromid='$discuz_uid' AND folder='inbox' AND delstatus!='1'");

			$query = $db->query("SELECT p.*, m.username AS msgto FROM {$tablepre}pms p
				LEFT JOIN {$tablepre}members m ON m.uid=p.msgtoid
				WHERE p.msgfromid='$discuz_uid' AND p.folder='inbox' AND delstatus!='1'
				ORDER BY p.dateline DESC LIMIT $start_limit, $tpp");
			break;

		default:
			$folder = 'inbox';
			if($filter == 'newpm') {
				$pmnum = $pm_inbox_newpm;
				$filteradd = 'AND new>0';
			} else {
				$pmnum = $pm_inbox;
				$filteradd ='';
			}

			$readapmids = !empty($_DCOOKIE['readapmid']) ? explode('D', $_DCOOKIE['readapmid']) : array();
			$query = $db->query("SELECT id as pmid, subject, groups, starttime as dateline FROM {$tablepre}announcements WHERE type=2 AND starttime<='$timestamp' ORDER BY displayorder, starttime DESC, id DESC");
			while($announce = $db->fetch_array($query)) {
				if(empty($announce['groups']) || in_array($groupid, explode(',', $announce['groups']))) {
					$announce['announce'] = TRUE;
					$announce['dateline'] = gmdate("$dateformat", $announce['dateline'] + $timeoffset * 3600);
					$announce['class'] = !in_array($announce['pmid'], $readapmids) ? 'style="font-weight:800"' : '';
					$announce_pmlist[] = $announce;
				}
			}

			$query = $db->query("SELECT * FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' $filteradd AND delstatus!='2' ORDER BY dateline DESC LIMIT $start_limit, $tpp");
	}
	$filterurl = ($filter == 'newpm' && $folder == 'inbox') ? 'filter=newpm' :'';
	$multipage = multi($pmnum, $tpp, $page, "pm.php?folder=$folder&$filterurl");

	$pmlist = array();
	while($pm = $db->fetch_array($query)) {
		$pm['dateline'] = gmdate("$dateformat $timeformat", $pm['dateline'] + $timeoffset * 3600);
		$pm['class'] = $pm['new'] ? 'style="font-weight:800"' : '';
		$pmlist[] = $pm;
	}
	$pmlist = array_merge($announce_pmlist, $pmlist);

} elseif($action == 'view') {

	$pm_inbox_newpm = $pm_inbox_newpm > 0 ? $pm_inbox_newpm - 1 : 0;

	if($folder != 'announce') {

		if($pm_total > $maxpmnum) {
			showmessage('pm_box_isfull', 'pm.php');
		}

		$pm = $db->fetch_first("SELECT p.*, m.username AS msgto FROM {$tablepre}pms p
					LEFT JOIN {$tablepre}members m ON m.uid=p.msgtoid
					WHERE pmid='$pmid' AND (msgtoid='$discuz_uid' OR msgfromid='$discuz_uid')");
		if(!$pm) {
			showmessage('pm_nonexistence');
		}

		if($pm['new'] && !($pm['msgfromid'] == $discuz_uid && $pm['msgtoid'] != $discuz_uid && $pm['folder'] == 'inbox')) {
			$db->query("UPDATE {$tablepre}pms SET new='0' WHERE pmid='$pmid'");
		}

		$folder = $folder == 'track' ? $folder : $pm['folder'];

		$pm['dateline'] = gmdate("$dateformat $timeformat", $pm['dateline'] + $timeoffset * 3600);
		$pm['message'] = discuzcode($pm['message'], 0, 0);
		$announcepm = FALSE;

	} else {

		$pm = $db->fetch_first("SELECT * FROM {$tablepre}announcements WHERE id='$pmid' AND type=2 AND starttime<='$timestamp' AND (endtime='0' OR endtime>'$timestamp')");
		if(!$pm) {
			showmessage('pm_nonexistence');
		}
		if($pm['groups'] && !in_array($groupid, explode(',', $pm['groups']))) {
			showmessage('pm_nonexistence');
		}
		$folder = 'inbox';
		$pm['dateline'] = gmdate("$dateformat", $pm['starttime'] + $timeoffset * 3600);
		$pm['message'] = nl2br(discuzcode($pm['message'], 0, 0, 1, 1, 1, 1, 1));
		$pm['msgtoid'] = $discuz_uid;
		$pm['msgto'] = $discuz_user;
		$announcepm = TRUE;
		if(!empty($_DCOOKIE['readapmid']) && !in_array($pmid, explode('D', $_DCOOKIE['readapmid']))) {
			$_DCOOKIE['readapmid'] .= 'D'.$pmid;
		} else {
			$_DCOOKIE['readapmid'] = $pmid;
		}
		dsetcookie('readapmid', $_DCOOKIE['readapmid'], 15552000);

	}
	if($inajax) {
		include template('pm_view_ajax');
		exit;
	}

} elseif($action == 'send') {

	if(!$adminid && $newbiespan && (!$lastpost || $timestamp - $lastpost < $newbiespan * 3600)) {
		if($timestamp - ($db->result_first("SELECT regdate FROM {$tablepre}members WHERE uid='$discuz_uid'")) < $newbiespan * 3600) {
			showmessage('pm_newbie_span', NULL, 'HALTED');
		}
	}

	if($pm_total > $maxpmnum) {
		showmessage('pm_box_isfull', 'pm.php', 'HALTED');
	}

	checklowerlimit($creditspolicy['pm'], -1);

	$subject = !empty($subject) ? cutstr(dhtmlspecialchars(censor(trim($subject))), 75) : '';
	$message = !empty($message) ? trim(censor($message)) : '';
	$do = isset($do) ? $do : '';

	$seccodecheck = substr(sprintf('%05b', $seccodestatus), -4, 1) && (!$seccodedata['minposts'] || $posts < $seccodedata['minposts']);
	$secqaacheck = $secqaa['status'][3] && (!$secqaa['minposts'] || $posts < $secqaa['minposts']);

	if(!submitcheck('pmsubmit', 0, $seccodecheck, $secqaacheck)) {

		$buddylist = array();
		$query = $db->query("SELECT b.buddyid, m.username AS buddyname FROM {$tablepre}buddys b
					LEFT JOIN {$tablepre}members m ON m.uid=b.buddyid
					WHERE b.uid='$discuz_uid'");
		while($buddy = $db->fetch_array($query)) {
			$buddylist[] = $buddy;
		}

		$subject = $message = '';

		if(isset($pmid)) {
			$pm = $db->fetch_first("SELECT * FROM {$tablepre}pms WHERE pmid='$pmid' AND (msgtoid='$discuz_uid' OR msgfromid='$discuz_uid')");

			$pm['subject'] = $message = preg_replace("/^(Re:|Fw:)\s*/", "", $pm['subject']);
			$username = $pm['msgfrom'];

			if($do == 'reply') {
				$subject = "Re: $pm[subject]";
				$message = '[quote]'.dhtmlspecialchars(trim(preg_replace("/(\[quote])(.*)(\[\/quote])/siU", '', $pm['message']))).'[/quote]'."\n";
				$touser = $pm['msgfrom'];
			} elseif($do == 'forward') {
				$pm['dateline'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $pm['dateline'] + $timeoffset * 3600);
				$subject = "Fw: $pm[subject]";
				$message = '[quote]'.dhtmlspecialchars($pm['message']).'[/quote]'."\n";
				$touser = '';
			} elseif($folder == 'outbox') {
				$subject = $pm['subject'];
				$message = dhtmlspecialchars($pm['message']);
				$touser = dhtmlspecialchars($db->result_first("SELECT username FROM {$tablepre}members WHERE uid='$pm[msgtoid]'"));
			}

		} elseif(isset($uid)) {

			$touser = dhtmlspecialchars($db->result_first("SELECT username FROM {$tablepre}members WHERE uid='$uid'"));

			if(isset($tradepid)) {
				include_once language('misc');

				$tradepid = intval($tradepid);
				$trade = $db->fetch_first("SELECT * FROM {$tablepre}trades WHERE pid='$tradepid'");
				if($trade) {
					$subject = $language['post_trade_pm_subject'].$trade['subject'];
					$message = '[url='.$boardurl.'viewthread.php?do=tradeinfo&tid='.$trade['tid'].'&pid='.$tradepid.']'.$trade['subject']."[/url]\n";
					$message .= $trade['costprice'] ? $language['post_trade_costprice'].': '.$trade['costprice']."\n" : '';
					$message .= $language['post_trade_price'].': '.$trade['price']."\n";
					$message .= $language['post_trade_transport_type'].': ';
					if($trade['transport'] == 1) {
						$message .= $language['post_trade_transport_seller'];
					} elseif($trade['transport'] == 2) {
						$message .= $language['post_trade_transport_buyer'];
					} elseif($trade['transport'] == 3) {
						$message .= $language['post_trade_transport_virtual'];
					} elseif($trade['transport'] == 4) {
						$message .= $language['post_trade_transport_physical'];
					}
					if($trade['transport'] == 1 or $trade['transport'] == 2 or $trade['transport'] == 4) {
						if(!empty($trade['ordinaryfee'])) {
							$message .= ', '.$language['post_trade_transport_mail'].' '.$trade['ordinaryfee'].' '.$language['payment_unit'];
						}
						if(!empty($trade['expressfee'])) {
							$message .= ', '.$language['post_trade_transport_express'].' '.$trade['expressfee'].' '.$language['payment_unit'];
						}
						if(!empty($trade['emsfee'])) {
							$message .= ', EMS '.$trade['emsfee'].' '.$language['payment_unit'];
						}
					}
					$message .= "\n".$language['post_trade_locus'].': '.$trade['locus']."\n\n";
					$message .= $language['post_trade_pm_buynum'].": \n";
					$message .= $language['post_trade_pm_wishprice'].": \n";
					$message .= $language['post_trade_pm_reason'].": \n";
				}
			}

		} else {

			$touser = isset($touser) ? dhtmlspecialchars($touser) : '';

		}

		if($seccodecheck) {
			$seccode = random(6, 1) + $seccode{0} * 1000000;
		}
		if($secqaacheck) {
			$seccode = random(1, 1) * 1000000 + substr($seccode, -6);
		}

	} else {

		$floodctrl = $floodctrl * 2;
		if($floodctrl && !$disablepostctrl && $timestamp - $lastpost < $floodctrl) {
			showmessage('pm_flood_ctrl', NULL, 'HALTED');
		}

		if(empty($msgto) && is_array($msgtobuddys)) {
			$msgto = $msgtobuddys;
		} else {
			$msgtoid = 0;
			$query = $db->query("SELECT m.uid, m.username FROM {$tablepre}members m WHERE username='$msgto'");
			while($member = $db->fetch_array($query)) {
				if(!strcasecmp(addslashes($member['username']), $msgto)) {
					$msgtoid = $member['uid'];
					break;
				}
			}

			if(!$msgtoid) {
				showmessage('pm_send_nonexistence', NULL, 'HALTED');
			}

			if(is_array($msgtobuddys)) {
				$msgto = array_merge($msgtobuddys, array($msgtoid));
			} else {
				$msgto = array($msgtoid);
			}
		}

		if(empty($message) || empty($subject)) {
			showmessage('pm_send_invalid', NULL, 'HALTED');
		}

		$uids = $comma = '';
		foreach($msgto as $uid) {
			if(!is_numeric($uid)) {
				showmessage('pm_send_invalid', NULL, 'HALTED');
			} else {
				$uids .= $comma."'$uid'";
				$comma = ',';
			}
		}

		$maxpmsend = ceil($maxpmnum / 10);
		$msgto_count = count($msgto);
		if($msgto_count > $maxpmsend) {
			showmessage('pm_send_toomany', NULL, 'HALTED');
		} elseif(!$msgto_count) {
			showmessage('pm_send_nonexistence', NULL, 'HALTED');
		}

		$ignorenum = 0;
		$query = $db->query("SELECT m.username, mf.ignorepm, u.maxpmnum FROM {$tablepre}usergroups u, {$tablepre}members m
			LEFT JOIN {$tablepre}memberfields mf USING(uid)
			WHERE m.uid IN ($uids) AND m.groupid=u.groupid");

		if($msgto_count <> $db->num_rows($query)) {
			showmessage('pm_send_nonexistence', NULL, 'HALTED');
		}

		while($member = $db->fetch_array($query)) {
			if($member['maxpmnum'] < 1 || preg_match("/(^{ALL}$|(,|^)\s*".preg_quote($discuz_user, '/')."\s*(,|$))/i", $member['ignorepm'])) {
				showmessage('pm_send_ignore', NULL, 'HALTED');
			}
		}
		if(!$saveoutbox) {

			updatecredits($discuz_uid, $creditspolicy['pm'], -1);

			foreach($msgto as $uid) {
				$db->query("INSERT INTO {$tablepre}pms (
				msgfrom, msgfromid, msgtoid, folder, new, subject, dateline, message )VALUES(
				'$discuz_user', '$discuz_uid', '$uid', 'inbox', '1', '$subject', '$timestamp', '$message')");
			}

			$db->query("UPDATE {$tablepre}members SET newpm='1' WHERE uid IN ($uids)", 'UNBUFFERED');

			if($supe['status'] && $xspacestatus) {
				supe_dbconnect();
				$supe['db']->query("UPDATE {$supe[tablepre]}members SET newpm='1' WHERE uid IN ($uids)", 'SILENT');
			}

			if($floodctrl) {
				$db->query("UPDATE {$tablepre}members SET lastpost='$timestamp' WHERE uid='$discuz_uid'");
			}

			showmessage('pm_send_succeed', 'pm.php');

		} else {

			if($pmid) {
				$db->query("UPDATE {$tablepre}pms SET
				msgtoid= '$msgto[0]', subject='$subject', dateline='$timestamp', message='$message'
				WHERE pmid='$pmid' AND folder='outbox' AND msgfromid='$discuz_uid'
				");
			} else {
				$db->query("INSERT INTO {$tablepre}pms (
				msgfrom, msgfromid, msgtoid
				, folder, new, subject
				, dateline, message
				) VALUES (
				'$discuz_user', '$discuz_uid', '$msgto[0]'
				, 'outbox', '1', '$subject'
				, '$timestamp', '$message'
				)");
			}

			showmessage('pm_saved_succeed', 'pm.php?folder=outbox');

		}

	}

} elseif($action == 'search') {

	$cachelife_text = 3600;

	if(!$allowsearch) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	}

	if(!submitcheck('searchsubmit', 1) && empty($_GET['page'])) {

		$ftdisabled = $allowsearch != 2 ? 'disabled' : '';

	} else {

		$orderby = isset($orderby) && $orderby == 'msgfrom' ? 'msgfrom' : 'dateline';
		$ascdesc = isset($ascdesc) && $ascdesc == 'asc' ? 'asc' : 'desc';

		if(isset($searchid)) {

			$page = max(1, intval($page));
			$start_limit = ($page - 1) * $tpp;

			$index = $db->fetch_first("SELECT searchstring, keywords, pms, pmids FROM {$tablepre}pmsearchindex WHERE searchid='$searchid'");
			if(!$index) {
				showmessage('search_id_invalid');
			}
			$index['keywords'] = rawurlencode($index['keywords']);
			$index['folder'] = preg_replace("/^\d+\|([a-z]+)\|.*/", "\\1", $index['searchstring']);

			$pmlist = array();
			$query = $db->query("SELECT p.*, m.username AS msgto FROM {$tablepre}pms p
				LEFT JOIN {$tablepre}members m ON p.msgtoid=m.uid
				WHERE p.pmid IN ($index[pmids])
				ORDER BY p.$orderby $ascdesc LIMIT $start_limit, $tpp");

			while($pm = $db->fetch_array($query)) {
				$pm['dateline'] = gmdate("$dateformat $timeformat", $pm['dateline'] + $timeoffset * 3600);
				$pm['class'] = $pm['new'] ? 'style="font-weight:800"' : '';
				$pmlist[] = $pm;
			}

			$multipage = multi($index['pms'], $tpp, $page, "pm.php?action=search&amp;searchid=$searchid&amp;orderby=$orderby&amp;ascdesc=$ascdesc&amp;searchsubmit=yes");

		} else {

			checklowerlimit($creditspolicy['search'], -1);

			$srchtxt = isset($srchtxt) ? trim($srchtxt) : '';
			$srchuname = isset($srchuname) ? trim($srchuname) : '';
			$srchfolder = in_array($srchfolder, array('inbox', 'outbox', 'track')) ? $srchfolder : 'inbox';

			if($allowsearch == 2 && $srchtype == 'fulltext') {
				periodscheck('searchbanperiods');
			} else {
				$srchtype = 'title';
			}

			if(empty($srchread) && empty($srchunread)) {
				$srchread = $srchunread = 1;
			}

			$searchstring = $discuz_uid.'|'. $srchfolder.'|'.$srchtype.'|'.addslashes($srchtxt).'|'.trim($srchuname).'|'.intval($srchread).'|'.intval($srchunread).'|'.intval($srchfrom).'|'.intval($before);
			$searchindex = array('id' => 0, 'dateline' => '0');

			$query = $db->query("SELECT searchid, dateline,
				('$searchctrl'<>'0' AND uid='$discuz_uid' AND $timestamp-dateline<$searchctrl) AS flood,
				(searchstring='$searchstring' AND expiration>'$timestamp') AS indexvalid
				FROM {$tablepre}pmsearchindex
				WHERE ('$searchctrl'<>'0' AND uid='$discuz_uid' AND $timestamp- dateline <$searchctrl) OR (searchstring='$searchstring' AND expiration>'$timestamp')
				ORDER BY flood");

			while($index = $db->fetch_array($query)) {
				if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
					$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
					break;
				} elseif($index['flood']) {
					showmessage('search_ctrl');
				}
			}

			if($searchindex['id']) {

				$searchid = $searchindex['id'];

			} else {

				if(!$srchtxt && !$srchuname) {
					showmessage('search_invalid');
				}

				if($maxspm) {
					if($db->result_first("SELECT COUNT(*) FROM {$tablepre}pmsearchindex WHERE dateline>'$timestamp'-60") >= $maxspm) {
						showmessage('search_toomany');
					}
				}

				$sqlsrch = '';

				if($srchfolder == 'outbox') {
					$sqlsrch .= "msgfromid='$discuz_uid' AND folder='outbox'";
				} elseif($srchfolder == 'track') {
					$sqlsrch .= "msgfromid='$discuz_uid' AND folder='inbox'";
				} else {
					$sqlsrch .= "msgtoid='$discuz_uid' AND folder='inbox'";
				}

				if($srchread == 1 && empty($srchunread)) {
					$sqlsrch .= " AND new='0'";
				}
				if($srchunread == 1 && empty($srchread)) {
					$sqlsrch .= " AND new>'0'";
				}

				$srchuid = '';
				if($srchuname) {
					$comma = '';
					$srchuname = str_replace('*', '%', addcslashes($srchuname, '%_'));
					$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username LIKE '".str_replace('_', '\_', $srchuname)."' LIMIT 50");
					while($member = $db->fetch_array($query)) {
						$srchuid .= "$comma'$member[uid]'";
						$comma = ', ';
					}
					if(!$srchuid) {
						$sqlsrch .= ' AND 0';
					}
				}

				if($srchtxt) {
					if(preg_match("(AND|\+|&|\s)", $srchtxt) && !preg_match("(OR|\|)", $srchtxt)) {
						$andor = ' AND ';
						$sqltxtsrch = '1';
						$srchtxt = preg_replace("/( AND |&| )/is", "+", $srchtxt);
					} else {
						$andor = ' OR ';
						$sqltxtsrch = '0';
						$srchtxt = preg_replace("/( OR |\|)/is", "+", $srchtxt);
					}
					$srchtxt = str_replace('*', '%', addcslashes($srchtxt, '%_'));
					foreach(explode('+', $srchtxt) as $text) {
						$text = trim($text);
						if($text) {
							$sqltxtsrch .= $andor;
							$sqltxtsrch .= $srchtype == 'fulltext' ? "(message LIKE '%".str_replace('_', '\_', $text)."%' OR subject LIKE '%$text%')" : "subject LIKE '%$text%'";
						}
					}
					$sqlsrch .= " AND ($sqltxtsrch)";
				}

				if($srchuid) {
					$sqlsrch .= ' AND '.($srchfolder == 'inbox' ? 'msgfromid' : 'msgtoid')." IN ($srchuid)";
				}

				if(!empty($srchfrom)) {
					$searchfrom = ($before ? '<=' : '>=').($timestamp - $srchfrom);
					$sqlsrch .= " AND dateline$searchfrom";
				}
				$keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
				$expiration = $timestamp + $cachelife_text;

				$pmids = 0;
				$query = $db->query("SELECT pmid FROM {$tablepre}pms WHERE $sqlsrch ORDER BY pmid DESC LIMIT $maxsearchresults");
				while($pm = $db->fetch_array($query)) {
					$pmids .= ','.$pm['pmid'];
				}
				$pms = $db->num_rows($query);
				$db->free_result($query);

				$db->query("INSERT INTO {$tablepre}pmsearchindex (keywords, searchstring, uid, dateline, expiration, pms, pmids)
						VALUES ('$keywords', '$searchstring', '$discuz_uid', '$timestamp', '$expiration', '$pms', '$pmids')");
				$searchid = $db->insert_id();

				updatecredits($discuz_uid, $creditspolicy['search'], -1);

			}

			showmessage('search_redirect', "pm.php?action=search&amp;searchid=$searchid&amp;orderby=$orderby&amp;ascdesc=$ascdesc&amp;searchsubmit=yes");

		}

	}

} elseif($action == 'delete' && in_array($folder, array('inbox', 'outbox', 'track'))) {

	$pmsadd = '';
	if($pmids = implodeids($delete)) {
		$pmsadd = "pmid IN ($pmids)";
	} elseif($pmid = intval($pmid)) {
		$pmsadd = "pmid='$pmid'";
	}

	if($pmsadd) {
		if($folder == 'inbox') {
			$sql = "folder='inbox' AND msgtoid='$discuz_uid' AND $pmsadd AND (delstatus=1 OR msgfromid=0)";
			$msg_field = 'msgtoid';
			$deletestatus = 2;
		} elseif($folder == 'track') {
			$sql = "folder='inbox' AND msgfromid='$discuz_uid' AND $pmsadd AND delstatus=2";
			$msg_field = 'msgfromid';
			$deletestatus = 1;
		} else {
			$sql = "folder='outbox' AND msgfromid='$discuz_uid' AND $pmsadd";
			$msg_field = 'msgfromid';
		}
		$db->query("DELETE FROM {$tablepre}pms WHERE $sql", 'UNBUFFERED');
		if($deletestatus) {
			$db->query("UPDATE {$tablepre}pms SET delstatus='$deletestatus' WHERE $msg_field='$discuz_uid' AND $pmsadd", 'UNBUFFERED');
		}
	}

	showmessage('pm_delete_succeed', "pm.php?folder=$folder");

} elseif($action == 'markunread' && !empty($pmid)) {

	$db->query("UPDATE {$tablepre}pms SET new='2' WHERE pmid='$pmid' AND msgtoid='$discuz_uid'");
	showmessage('pm_mark_unread_succeed', "pm.php?folder=$folder");

} elseif($action == 'announcearchive') {

	$pm = $db->fetch_first("SELECT * FROM {$tablepre}announcements WHERE id='$pmid' AND type=2 AND starttime<='$timestamp' AND (endtime='0' OR endtime>'$timestamp')");
	if(!$pm) {
		showmessage('pm_nonexistence');
	}
	if($pm['groups'] && !in_array($groupid, explode(',', $pm['groups']))) {
		showmessage('pm_nonexistence');
	}
	$folder = 'inbox';
	$pm['dateline'] = gmdate("$dateformat", $pm['starttime'] + $timeoffset * 3600);
	$pm['message'] = nl2br(discuzcode($pm['message'], 0, 0, 1, 1, 1, 1, 1));
	$pm['msgtoid'] = $discuz_uid;
	$pm['msgto'] = $discuz_user;
	$pmlist[] = $pm;
	$announcepm = TRUE;

	ob_end_clean();
	dheader('Content-Encoding: none');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
	dheader('Content-Disposition: attachment; filename="AnnouncePM_'.$discuz_userss.'_'.gmdate('ymd_Hi', $timestamp + $timeoffset * 3600).'.htm"');
	dheader('Pragma: no-cache');
	dheader('Expires: 0');

	include template('pm_archive_html');
	dexit();

} elseif($action == 'archive' && (!empty($pmid) || submitcheck('archivesubmit'))) {

	$sql = $limitadd = '';

	if(empty($pmid)) {
		$days = intval($days);
		$amount = intval($amount);
		$sql .= $folder == 'inbox' ? " AND p.folder='inbox' AND p.msgtoid='$discuz_uid' AND p.delstatus!='2'" : " AND p.folder='outbox' AND p.msgfromid='$discuz_uid'";
		$sql .= $days > 0 ? ' AND p.dateline'.($newerolder == 'older' ? '<' : '>').($timestamp - intval($days) * 86400) : '';
		$limitadd = 'LIMIT '.(($amount > 0 AND $amount <= $maxpmnum ) ? $amount : $maxpmnum);
	} else {
		$sql = "AND p.pmid='$pmid' AND ((p.folder='inbox' AND p.msgtoid='$discuz_uid') OR (p.folder='outbox' AND p.msgfromid='$discuz_uid'))";
	}

	$pmids = 0;
	$pmlist = array();
	$query = $db->query("SELECT p.pmid, p.folder, p.msgfrom, p.msgfromid, m.username AS msgto, p.msgtoid, p.subject, p.dateline, p.message
		FROM {$tablepre}pms p LEFT JOIN {$tablepre}members m ON m.uid=p.msgtoid
		WHERE 1 $sql ORDER BY p.folder, p.dateline DESC $limitadd");

	while($pm = $db->fetch_array($query)) {
		$pmids .= ','.$pm['pmid'];
		$pm['dateline'] = gmdate("$dateformat $timeformat", $pm['dateline'] + $timeoffset * 3600);
		$pm['message'] = discuzcode($pm['message'], 0, 0);
		$pmlist[] = $pm;
	}

	if(!$pmlist) {
		showmessage('pm_nonexistence');
	} elseif($delete) {

		$deleteadd = $folder == 'inbox' ? "AND delstatus = '1'" : '';
		$db->query("DELETE FROM {$tablepre}pms WHERE pmid IN ($pmids) $deleteadd", 'UNBUFFERED');
		if($deleteadd) {
			$db->query("UPDATE {$tablepre}pms SET delstatus='2' WHERE pmid IN ($pmids)", 'UNBUFFERED');
		}

	}

	ob_end_clean();
	dheader('Content-Encoding: none');
	dheader('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
	dheader('Content-Disposition: attachment; filename="PM_'.$discuz_userss.'_'.gmdate('ymd_Hi', $timestamp + $timeoffset * 3600).'.htm"');
	dheader('Pragma: no-cache');
	dheader('Expires: 0');

	include template('pm_archive_html');
	dexit();

} elseif($action == 'ignore') {

	if(!submitcheck('ignoresubmit')) {
		$ignorepm = $db->result_first("SELECT ignorepm FROM {$tablepre}memberfields WHERE uid='$discuz_uid'");
	} else {
		$db->query("UPDATE {$tablepre}memberfields SET ignorepm='$ignorelist' WHERE uid='$discuz_uid'");
		showmessage('pm_ignore_succeed', 'pm.php');
	}

}

include template('pm');

?>