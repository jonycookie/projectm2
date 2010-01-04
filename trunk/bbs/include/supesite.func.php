<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: supesite.func.php 10115 2007-08-24 00:58:08Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function supe_xspace2forum($itemid, &$subject, &$message, &$special, &$iconid, &$trade) {
	global $timestamp, $timeoffset, $dateformat, $timeformat, $supe;
	include_once language('misc');

	supe_dbconnect();

	$query = $supe['db']->query("SELECT * FROM {$supe[tablepre]}spaceitems WHERE itemid='$itemid'");
	if(!$item = $supe['db']->fetch_array($query)) {
		return array();
	}

	$subject .= str_replace('"', '&quot;', $item['subject']);

	switch($item['type']) {
		case 'blog':
			$special = 0;
			$query = $supe['db']->query("SELECT message, postip, weather, mood, customfieldid, customfieldtext FROM {$supe[tablepre]}spaceblogs WHERE itemid='$itemid'");
			$item += $supe['db']->fetch_array($query);
			$message .= supe_html2bbcode($item['message']);
			break;
		case 'image':
			$special = 0;
			$query = $supe['db']->query("SELECT message, postip, customfieldid, customfieldtext FROM {$supe[tablepre]}spaceimages WHERE itemid='$itemid'");
			$item += $supe['db']->fetch_array($query);
			$message .= "[b]$language[supe_picture_story]:[/b]  \r\n ".supe_html2bbcode($item[message]);
			break;
		case 'goods':
			$special = 2;
			$query = $supe['db']->query("SELECT * FROM {$supe[tablepre]}spacegoods WHERE itemid='$itemid'");
			$item += $supe['db']->fetch_array($query);
			$message .= supe_html2bbcode($item['message']);

			$trade['trade_expiration'] = $timestamp + 30 * 86400;
			$trade['seller'] = $item['alipay'];
			$trade['item_name'] = $item['subject'];
			$trade['item_price'] = $item['price'];
			$trade['item_number'] = $item['salednum'] ? $item['salednum'] : 1;
			$trade['item_quality'] = $item['quality'] == 'new' ? 1 : 2;
			$trade['item_locus'] = $item['province'].' '.$item['city'];
			$trade['item_transport'] = $item['chargemode'] == 'buy' ? 2 : 1;
			$trade['postage_mail'] = $item['chargemail'];
			$trade['postage_express'] = $item['chargeexpress'];
			$trade['postage_ems'] = $item['chargeems'];
			$trade['item_type'] = 1;
			break;
		case 'file':
			$special = 0;
			$query = $supe['db']->query("SELECT message, relativetags, postip, relativeitemids, customfieldid, customfieldtext, includetags, filesize, filesizeunit, version, producer, downfrom, language, permission, system, remoteurl FROM {$supe[tablepre]}spacefiles WHERE itemid='$itemid'");
			$item += $supe['db']->fetch_array($query);
			$message .= '[list]';
			$message .= "[*][b]$language[supe_software_size]:[/b] $item[filesize]\r\n";
			$message .= "[*][b]$language[supe_software_version]:[/b] $item[version]\r\n";
			$message .= "[*][b]$language[supe_software_producer]:[/b] $item[producer]\r\n";
			$message .= "[*][b]$language[supe_software_downfrom]:[/b] $item[downfrom]\r\n";
			$message .= "[*][b]$language[supe_software_language]:[/b] $item[language]\r\n";
			$message .= "[*][b]$language[supe_software_permission]:[/b] $item[permission] \r\n";
			$message .= "[*][b]$language[supe_software_system]:[/b] $item[system]\r\n";
			$item['digest'] = $language['supe_digest_'.intval($item['digest'])];
			$message .= "[*][b]$language[supe_digest_level]:[/b] $item[digest] \r\n";
			$message .= "[*][b]$language[supe_software_introduce]:[/b]\r\n".supe_html2bbcode($item[message])."\r\n\r\n";
			if($item['remoteurl'] = unserialize($item['remoteurl'])) {
				$message .= "[*][b]$language[supe_download_from_remote]:[/b]\r\n";
				foreach($item['remoteurl'] as $val) {
					$message .= "[url=$val[remoteurl]][/b]{$val[remoteurlname]}[/b][/url]\r\n";
				}
			}
			$message .= '[/list]';
			break;
		case 'link':
			$special = 0;
			$query = $supe['db']->query("SELECT url, message, postip, customfieldid, customfieldtext FROM {$supe[tablepre]}spacelinks WHERE itemid='$itemid'");
			$item += $supe['db']->fetch_array($query);
			$message .= '[list]';
			$message .= "[*][b]$language[supe_linkurl]:[/b] [url]{$item[url]}[/url]\r\n";
			$message .= "[*][b]$language[supe_snapshot]:[/b] [url={$supe[siteurl]}/batch.snapshot.php?itemid=$item[itemid]]$language[supe_viewsnapshot][/url] \r\n";
			$message .= "[*][b]$language[supe_urldescription]:[/b]\r\n";
			$message .= supe_html2bbcode($item['message']);
			$message .= '[/list]';
			break;
		default:
			$special = 0;
	}
	$message_customfield = '';
	if($item['customfieldid'] && $item['customfieldtext']) {

		$customfielddata = unserialize($item['customfieldtext']);
		$query = $supe['db']->query("SELECT name, customfieldtext FROM {$supe[tablepre]}customfields WHERE customfieldid='$item[customfieldid]'");

		$querydata = $supe['db']->fetch_array($query);
		$customfieldname = $querydata['name'];
		$customfieldstruct = unserialize($querydata['customfieldtext']);
		unset($querydata);
		$len = count($customfieldstruct);
		$message_customfield .= "[quote][b]{$customfieldname}[/b]:\r\n";

		for($i = 0; $i < $len; $i++) {
			switch($customfieldstruct[$i]['type']) {
				case 'input':
				case 'textarea':
				case 'select':
					$message_customfield .= "[*][b]{$customfieldstruct[$i][name]}[/b] : {$customfielddata[$i]} \r\n";
					break;
				case 'checkbox':
					$message_customfield .= "[*][b]{$customfieldstruct[$i][name]}[/b] : ".join(',', $customfielddata[$i])."\r\n";
					break;
			}
		}
		unset($customfieldstruct, $customfieldname, $len);
		$message_customfield .= "[/quote]\r\n";
	}

	$message_attachments = '';
	if($item['haveattach']) {

		$message_attachments = $item['type'] != 'file' ? "\r\n[b]$language[attach]:[/b]\r\n" : "[b]$language[supe_donwload_from_local]:[/b]  \r\n";
		$query = $supe['db']->query("SELECT aid, dateline, filename, subject, attachtype, isimage, size, filepath, thumbpath, downloads FROM {$supe[tablepre]}attachments WHERE hash='$item[hash]'");
		while($attach = $supe['db']->fetch_array($query)) {

			if($attach['isimage']) {
				$attach['dateline'] = gmdate("$dateformat $timeformat", $attach['dateline'] + $timeoffset * 3600);

				if($item['message'] && !preg_match("/src=\"[^\"]*".preg_quote($attach[filepath], '/')."\"/is", $item['message'])) {
					$message_attachments .= "[url={$supe[siteurl]}/attachments/$attach[filepath]][img]{$supe[siteurl]}/attachments/{$attach[thumbpath]}[/img][/url]\r\n";
					$message_attachments .= "[b]{$attach[subject]}[/b]  [$language[supe_dateline]:$attach[dateline]]\r\n";
				}

			} else {
				require_once DISCUZ_ROOT.'./include/attachment.func.php';

				$attach['dateline'] = gmdate("$dateformat $timeformat", $attach['dateline'] + $timeoffset * 3600);
				$attach['filesize'] = sizecount($attach['filesize']);
				$attach['filetype'] = supe_html2bbcode(attachtype(fileext($attach['attachment'])."\t".$attach['filetype']));
				$message_attachments .= "$attach[filetype] [url={$supe[siteurl]}/batch.download.php?aid=$attach[aid]][b]{$attach[filename]}[/b][/url]\r\n";
				$message_attachments .= "[$language[supe_dateline]:$attach[dateline] - $language[supe_download_count]:$attach[downloads]]\r\n";
			}
		}
	}
	$message .= $message_customfield.$message_attachments;
	return $item;
}

function supe_html2bbcode($text) {
	global $forum, $allowhtml;
	require_once DISCUZ_ROOT.'./include/editor.func.php';

	return $forum['allowhtml'] || $allowhtml ? $text : html2bbcode($text);
}

function supe_circlepermission($gid, $permission = 'allowshare') {
        global $supe, $discuz_uid, $_DCOOKIE;

        supe_dbconnect();

        $query = $supe['db']->query("SELECT ispublic, allowshare, password FROM {$supe[tablepre]}groups WHERE gid='$gid' AND flag=1");
        $circle = $supe['db']->fetch_array($query);
        $incircle = $discuz_uid ? $supe['db']->result($supe['db']->query("SELECT COUNT(*) FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND gid='$gid' AND flag>0"), 0) : 0;
        return $permission == 'allowshare' ? ($circle['allowshare'] && ($circle['ispublic'] == 1 || ($circle['ispublic'] == 2 && $circle['password'] == $_DCOOKIE['gidpw'.$gid]))) || $incircle : $incircle;
}

?>