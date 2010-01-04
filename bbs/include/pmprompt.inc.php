<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: pmprompt.inc.php 10530 2007-09-04 02:01:18Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

if($maxpmnum == 0) {

	$query = $db->query("DELETE FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' AND delstatus='1'", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}pms SET delstatus='2' WHERE msgtoid='$discuz_uid' AND folder='inbox'", 'UNBUFFERED');
	$db->query("UPDATE {$tablepre}members SET newpm='0' WHERE uid='$discuz_uid'");

	if($supe['status'] && $xspacestatus) {
		supe_dbconnect();
		$supe['db']->query("UPDATE {$supe[tablepre]}members SET newpm='0' WHERE uid='$discuz_uid'", 'SILENT');
	}

} else {

	$newpmexists = 0;
	$pmlist = $pmlist ? $pmlist : array();

	$query = $db->query("SELECT pmid, msgfrom, msgfromid, subject, message FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' AND delstatus!='2' AND new='1'");
	if($newpmnum = $db->num_rows($query)) {
		$newpmexists = 1;
		if($newpmnum <= 10) {
			$pmdetail = '';
			while($pm = $db->fetch_array($query)) {
				$pm['subject'] = cutstr($pm['subject'], 60);
				$pmlist[] = $pm;
			}
		}

	} else {
		$db->query("UPDATE {$tablepre}members SET newpm='0' WHERE uid='$discuz_uid'");
		if($supe['status'] && $xspacestatus) {
			supe_dbconnect();
			$supe['db']->query("UPDATE {$supe[tablepre]}members SET newpm='0' WHERE uid='$discuz_uid'", 'SILENT');
		}

	}
}

?>