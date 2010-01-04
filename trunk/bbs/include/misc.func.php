<?php

/*
[Discuz!] (C)2001-2007 Comsenz Inc.
This is NOT a freeware, use is subject to license terms

$Id: misc.func.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function convertip($ip) {

	$return = '';

	if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {

		$iparray = explode('.', $ip);

		if($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
			$return = '- LAN';
		} elseif($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
			$return = '- Invalid IP Address';
		} else {
			$tinyipfile = DISCUZ_ROOT.'./ipdata/tinyipdata.dat';
			$fullipfile = DISCUZ_ROOT.'./ipdata/wry.dat';
			if(@file_exists($tinyipfile)) {
				$return = convertip_tiny($ip, $tinyipfile);
			} elseif(@file_exists($fullipfile)) {
				$return = convertip_full($ip, $fullipfile);
			}
		}
	}

	return $return;

}

function convertip_tiny($ip, $ipdatafile) {

	static $fp = NULL, $offset = array(), $index = NULL;

	$ipdot = explode('.', $ip);
	$ip    = pack('N', ip2long($ip));

	$ipdot[0] = (int)$ipdot[0];
	$ipdot[1] = (int)$ipdot[1];

	if($fp === NULL && $fp = @fopen($ipdatafile, 'rb')) {
		$offset = unpack('Nlen', fread($fp, 4));
		$index  = fread($fp, $offset['len'] - 4);
	} elseif($fp == FALSE) {
		return  '- Invalid IP data file';
	}

	$length = $offset['len'] - 1028;
	$start  = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);

	for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {

		if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
			$index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
			$index_length = unpack('Clen', $index{$start + 7});
			break;
		}
	}

	fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
	if($index_length['len']) {
		return '- '.fread($fp, $index_length['len']);
	} else {
		return '- Unknown';
	}

}

function convertip_full($ip, $ipdatafile) {

	if(!$fd = @fopen($ipdatafile)) {
		return '- Invalid IP data file';
	}

	$ip = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
	@$ipbegin = implode('', unpack('L', $DataBegin));
	if($ipbegin < 0) $ipbegin += pow(2, 32);
	@$ipend = implode('', unpack('L', $DataEnd));
	if($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

	$BeginNum = $ip2num = $ip1num = 0;
	$ipAddr1 = $ipAddr2 = '';
	$EndNum = $ipAllNum;

	while($ip1num > $ipNum || $ip2num < $ipNum) {
		$Middle= intval(($EndNum + $BeginNum) / 2);

		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if(strlen($ipData1) < 4) {
			fclose($fd);
			return '- System Error';
		}
		$ip1num = implode('', unpack('L', $ipData1));
		if($ip1num < 0) $ip1num += pow(2, 32);

		if($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}

		$DataSeek = fread($fd, 3);
		if(strlen($DataSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if(strlen($ipData2) < 4) {
			fclose($fd);
			return '- System Error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if($ip2num < 0) $ip2num += pow(2, 32);

		if($ip2num < $ipNum) {
			if($Middle == $BeginNum) {
				fclose($fd);
				return '- Unknown';
			}
			$BeginNum = $Middle;
		}
	}

	$ipFlag = fread($fd, 1);
	if($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if(strlen($ipSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}

	if($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if(strlen($AddrSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}

		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;

		$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
		fseek($fd, $AddrSeek);

		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;

		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;
	}
	fclose($fd);

	if(preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$ipaddr = "$ipAddr1 $ipAddr2";
	$ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
	$ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
	$ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
	if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
		$ipaddr = '- Unknown';
	}

	return '- '.$ipaddr;

}

function procthread($thread) {
	global $dateformat, $timeformat, $timeoffset, $ppp, $colorarray;

	if(empty($colorarray)) {
		$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
	}

	$thread['icon'] = isset($GLOBALS['_DCACHE']['icons'][$thread['iconid']]) ? '<img src="images/icons/'.$GLOBALS['_DCACHE']['icons'][$thread['iconid']].'" alt="Icon'.$thread['iconid'].'" class="icon" />' : '&nbsp;';
	$thread['forumname'] = $GLOBALS['_DCACHE']['forums'][$thread['fid']]['name'];
	$thread['dateline'] = gmdate($dateformat, $thread['dateline'] + $timeoffset * 3600);
	$thread['lastpost'] = gmdate("$dateformat $timeformat", $thread['lastpost'] + $timeoffset * 3600);
	$thread['lastposterenc'] = rawurlencode($thread['lastposter']);

	if($thread['replies'] > $thread['views']) {
		$thread['views'] = $thread['replies'];
	}

	$postsnum = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
	$thread['special'] == 3 && $thread['price'] < 0 && $thread['replies']--;
	$pagelinks = '';
	if($postsnum  > $ppp) {
		$posts = $postsnum;
		$topicpages = ceil($posts / $ppp);
		for($i = 1; $i <= $topicpages; $i++) {
			$pagelinks .= '<a href="viewthread.php?tid='.$thread['tid'].'&page='.$i.'" target="_blank">'.$i.'</a> ';
			if($i == 6) {
				$i = $topicpages + 1;
			}
		}
		if($topicpages > 6) {
			$pagelinks .= ' .. <a href="viewthread.php?tid='.$thread['tid'].'&page='.$topicpages.'" target="_blank">'.$topicpages.'</a> ';
		}
		$thread['multipage'] = ' &nbsp; '.$pagelinks;
	} else {
		$thread['multipage'] = '';
	}

	if($thread['highlight']) {
		$string = sprintf('%02d', $thread['highlight']);
		$stylestr = sprintf('%03b', $string[0]);

		$thread['highlight'] = 'style="';
		$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
		$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
		$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$thread['highlight'] .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
		$thread['highlight'] .= '"';
	} else {
		$thread['highlight'] = '';
	}

	if($thread['attachment']) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		$thread['attachment'] = attachtype($thread['attachment']).' ';
	} else {
		$thread['attachment'] = '';
	}

	return $thread;
}

function updateviews($table, $idcol, $viewscol, $logfile) {
	global $db, $tablepre;

	$viewlog = $viewarray = array();
	if(@$viewlog = file($logfile = DISCUZ_ROOT.$logfile)) {
		@unlink($logfile);
		$viewlog = array_count_values($viewlog);
		foreach($viewlog as $id => $views) {
			$viewarray[$views] .= ($id > 0) ? ','.intval($id) : '';
		}
		foreach($viewarray as $views => $ids) {
			$db->query("UPDATE LOW_PRIORITY $tablepre$table SET $viewscol=$viewscol+'$views' WHERE $idcol IN (0$ids)", 'UNBUFFERED');
		}
	}
}

function modlog($thread, $action) {
	global $discuz_user, $adminid, $onlineip, $timestamp, $forum, $reason;
	writelog('modslog', dhtmlspecialchars("$timestamp\t$discuz_user\t$adminid\t$onlineip\t$forum[fid]\t$forum[name]\t$thread[tid]\t$thread[subject]\t$action\t$reason"));
}

function checkreasonpm() {
	global $reason;
	$reason = trim(strip_tags($reason));
	if(($GLOBALS['reasonpm'] == 1 || $GLOBALS['reasonpm'] == 3) && !$reason) {
		showmessage('admin_reason_invalid');
	}
}

function sendreasonpm($var, $item) {
	global $$var;
	${$var}['subject'] = strtr(${$var}['subject'], array_flip(get_html_translation_table(HTML_ENTITIES)));
	${$var}['dateline'] = gmdate($GLOBALS['_DCACHE']['settings']['dateformat'].' '.$GLOBALS['_DCACHE']['settings']['timeformat'], ${$var}['dateline'] + ($GLOBALS['timeoffset'] * 3600));
	sendpm(${$var}['authorid'], $item.'_subject', $item.'_message');
}

function modreasonselect() {
	global $_DCACHE;
	if(!isset($_DCACHE['modreasons']) || !is_array($_DCACHE['modreasons'])) {
		@include DISCUZ_ROOT.'./forumdata/cache/cache_topicadmin.php';
	}
	$select = '';
	foreach($_DCACHE['modreasons'] as $reason) {
		$select .= $reason ? '<option value="'.dhtmlspecialchars($reason).'">'.$reason.'</option>' : '<option value="">--------</option>';
	}
	return $select;
}

function logincheck() {
	global $db, $tablepre, $onlineip, $timestamp;
	if($login = $db->fetch_first("SELECT count, lastupdate FROM {$tablepre}failedlogins WHERE ip='$onlineip'")) {
		if($timestamp - $login['lastupdate'] > 900) {
			return 3;
		} elseif($login['count'] < 5) {
			return 2;
		} else {
			return 0;
		}
	} else {
		return 1;
	}
}

function loginfailed($permission) {
	global $db, $tablepre, $onlineip, $timestamp;
	switch($permission) {
		case 1:
			$db->query("REPLACE INTO {$tablepre}failedlogins (ip, count, lastupdate) VALUES ('$onlineip', '1', '$timestamp')");
			break;
		case 2:
			$db->query("UPDATE {$tablepre}failedlogins SET count=count+1, lastupdate='$timestamp' WHERE ip='$onlineip'");
			break;
		case 3:
			$db->query("UPDATE {$tablepre}failedlogins SET count='1', lastupdate='$timestamp' WHERE ip='$onlineip'");
			$db->query("DELETE FROM {$tablepre}failedlogins WHERE lastupdate<$timestamp-901", 'UNBUFFERED');
			break;
	}
}

?>