<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: supesite_circle.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !in_array(CURSCRIPT, array('forumdisplay', 'viewthread'))) {
	exit('Access Denied');
}

supe_dbconnect();

if(CURSCRIPT == 'forumdisplay') {
        $mycircles = $circle = array();
        if($discuz_uid) {
                $query = $supe['db']->query("SELECT gid, groupname FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND flag>0");
                while($mycircle = $supe['db']->fetch_array($query)) {
                        $mycircles[$mycircle['gid']] = cutstr($mycircle['groupname'], 30);
                }
        }

        if($sgids) {
                $query = $supe['db']->query("SELECT gid, groupname FROM {$supe[tablepre]}groups WHERE gid IN (".implode(',', $sgids).") AND flag=1");
                while($group = $supe['db']->fetch_array($query)) {
                        $circle[$group['gid']] = $group['groupname'];
                }
        }
        $cachefile = DISCUZ_ROOT.'./forumdata/cache/cache_updatecircles.php';
        if(@filemtime($cachefile) + 1800 < $timestamp) {
        	require_once DISCUZ_ROOT.'./include/cache.func.php';
        	updatecache('supe_updatecircles');
        }
        @require_once $cachefile;
        if($_DCACHE['supe_updatecircles']) {
        	$circlenum = count($_DCACHE['supe_updatecircles']);
        	foreach($_DCACHE['supe_updatecircles'] AS $k => $v) {
        		$_DCACHE['supe_updatecircles'][$k]['lastpost'] = gmdate($dateformat.' H:i', $v['lastpost'] + ($timeoffset * 3600));
        		$_DCACHE['supe_updatecircles'][$k]['logo'] = $v['logo'] ? $supe['attachurl'].'/'.$v['logo'] : 'images/common/circle_logo.gif';
        		$_DCACHE['supe_updatecircles'][$k]['width'] = $circlenum < 4 ? (1 / $circlenum * 100).'%' : '25%';
        	}
        }

} elseif(CURSCRIPT == 'viewthread') {
	$sgid = $thread['sgid'];
        $query = $supe['db']->query("SELECT g.groupname, g.ispublic, g.allowshare, g.password, gf.headerimage, gf.css FROM {$supe[tablepre]}groups g, {$supe[tablepre]}groupfields gf WHERE g.gid='$sgid' AND g.flag=1 AND g.gid=gf.gid");
        if($circle = $supe['db']->fetch_array($query)) {

        	if($action == 'pwverify') {
        		if(md5($pw) != $circle['password']) {
        			showmessage('supe_pwverify', NULL, 'HALTED');
        		} else {
        			dsetcookie('gidpw'.$sgid, md5($pw));
        			showmessage('supe_pwpass', "viewthread.php?tid=$tid");
        		}
        	}

                $incircle = 0;
                if($discuz_uid) {
                        $query = $supe['db']->query("SELECT COUNT(*) FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND gid='$sgid' AND flag>0");
                        $incircle = $supe['db']->result($query, 0);
                }

                if($circle['ispublic'] == 2 && !$incircle && $circle['password'] != $_DCOOKIE['gidpw'.$sgid] && !$forum['ismoderator']) {
                        include template('supesite_viewthread_passwd');
                        exit();
                }

                if(!$circle['ispublic'] && !$incircle && !$forum['ismoderator']) {
                        showmessage('supe_permission_limit');
                }

                if(!$circle['allowshare'] && !$incircle) {
                        $allowreply = 0;
                }
        } else {
                $db->query("UPDATE {$tablepre}threads SET sgid=0 WHERE tid='$tid'", 'UNBUFFERED');
        }
}

?>