<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admincp.php 11169 2007-11-08 06:30:00Z tiger $
*/

define('IN_ADMINCP', TRUE);
define('NOROBOT', TRUE);
require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./admin/global.func.php';
require_once DISCUZ_ROOT.'./include/cache.func.php';
$discuz_action = 211;
include language('admincp');
if($adminid <= 0) {

	$cpaccess = 0;

} else {

	if(!$discuz_secques && $admincp['forcesecques']) {
		cpheader();
		cpmsg('secques_invalid');
	}

	if($adminipaccess && $adminid == 1 && !ipaccess($onlineip, $adminipaccess)) {
		$cpaccess = 2;
	} else {
		$addonlineip = $admincp['checkip'] ? "AND ip='$onlineip'" : '';
		$query = $db->query("SELECT errorcount FROM {$tablepre}adminsessions WHERE uid='$discuz_uid' $addonlineip AND dateline+1800>'$timestamp'", 'SILENT');
		if($db->error()) {
			$db->query("DROP TABLE IF EXISTS {$tablepre}adminsessions");
			$db->query("CREATE TABLE {$tablepre}adminsessions (uid mediumint(8) UNSIGNED NOT NULL default '0', ip char(15) NOT NULL default '', dateline int(10) unsigned NOT NULL default '0', errorcount tinyint(1) NOT NULL default '0')");
			$cpaccess = 1;
		} else {
			if($session = $db->fetch_array($query)) {
				if($session['errorcount'] == -1) {
					$db->query("UPDATE {$tablepre}adminsessions SET dateline='$timestamp' WHERE uid='$discuz_uid'", 'UNBUFFERED');
					$cpaccess = 3;
				} elseif($session['errorcount'] <= 3) {
					$cpaccess = 1;
				} else {
					$cpaccess = 0;
				}
			} else {
				$db->query("DELETE FROM {$tablepre}adminsessions WHERE uid='$discuz_uid' OR dateline+1800<'$timestamp'");
				$db->query("INSERT INTO {$tablepre}adminsessions (uid, ip, dateline, errorcount)
					VALUES ('$discuz_uid', '$onlineip', '$timestamp', '0')");
				$cpaccess = 1;
			}
		}
	}

}

$username = !empty($username) ? dhtmlspecialchars($username) : '';
$action = !empty($action) && is_string($action) ? trim($action) : '';
$page = isset($page) ? intval((max(1, $page))) : 0;

if(!empty($action) && !in_array($action, array('main', 'header', 'menu', 'illegallog', 'ratelog', 'modslog', 'medalslog', 'creditslog', 'banlog', 'cplog', 'errorlog'))) {
	switch($cpaccess) {
		case 0:
			$extra = 'PERMISSION DENIED';
			break;
		case 1:
			$extra = 'AUTHENTIFICATION(ERROR #'.intval($session['errorcount']).')';
			break;
		case 2:
			$extra = 'IP ACCESS DENIED';
			break;
		case 3:
			$extra = $semicolon = '';
			if(is_array($_GET)) {
				foreach(array_merge($_GET, $_POST) as $key => $val) {
					if(!in_array($key, array('action', 'sid', 'formhash', 'admin_password')) && $val) {
						$extra .= $semicolon.$key.'=';
						if(is_array($val)) {
							$extra .= 'Array(';
							foreach($val as $arraykey => $arrayval) {
								$extra .= $arraykey.'='.cutstr($arrayval, 15).'; ';
							}
							$extra .= ')';
						} else {
							$extra .= cutstr($val, 15);
						}
						$semicolon = '; ';
					}
				}
				$extra = nl2br(dhtmlspecialchars($extra));
			}
			break;
	}
	$extralog = (($action == 'home' && isset($securyservice)) || ($action == 'insenz' && in_array($operation, array('register', 'binding')))) ? '' : $extra;
	writelog('cplog', dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$onlineip\t$action\t$extralog"));
	unset($extralog);
}

if($cpaccess == 0) {

	clearcookies();
	loginmsg('noaccess');
} elseif($cpaccess == 1) {
	if(!$admin_password || md5($admin_password) != $discuz_pw) {
		if($admin_password) {
			$db->query("UPDATE {$tablepre}adminsessions SET errorcount=errorcount+1 WHERE uid='$discuz_uid'");
			writelog('cplog', dhtmlspecialchars("$timestamp\t$discuz_userss\t$adminid\t$onlineip\t$action\tAUTHENTIFICATION(PASSWORD)"));
		}
		loginmsg('', '', 'login');
	} else {
		$db->query("UPDATE {$tablepre}adminsessions SET errorcount='-1' WHERE uid='$discuz_uid'");
		loginmsg('login_succeed', 'admincp.php?'.$_SERVER['QUERY_STRING'].'');
		if(!empty($url_forward)) {
			echo "<meta http-equiv=refresh content=\"0;URL=$url_forward\">";exit;
		}
	}
} elseif($cpaccess == 2) {

	loginmsg('noaccess_ip');

}
if(empty($action) || isset($frames)) {
	parse_str($_SERVER['QUERY_STRING'], $getarray);

	$extra = $and = '';
	foreach($getarray as $key => $value) {
		if($key == 'action' && in_array($value, array('header', 'menu'))) {
			$extra .= $and.$key.'=home';
		} elseif(!in_array($key, array('sid', 'frames'))) {
			@$extra .= $and.$key.'='.rawurlencode($value);
			$and = '&';
		}
	}

	$extra = $extra && $action ? $extra : (!empty($runwizard) ? 'action=runwizard' : 'action=home');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head>
<title>Discuz! Administrator's Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$charset?>">
<script src="include/javascript/common.js" type="text/javascript"></script>
<script src="include/javascript/iframe.js" type="text/javascript"></script>
</head>
<body style="margin: 0px" scroll="no">
<div style="position: absolute;top: 0px;left: 0px; z-index: 2;height: 65px;width: 100%">
<iframe frameborder="0" id="header" name="header" src="admincp.php?action=header&sid=<?=$sid?>" scrolling="no" style="height: 65px; visibility: inherit; width: 100%; z-index: 1;"></iframe>
</div>
<table border="0" cellPadding="0" cellSpacing="0" height="100%" width="100%" style="table-layout: fixed;">
<tr><td width="165" height="65"></td><td></td></tr>
<tr>
<td><iframe frameborder="0" id="menu" name="menu" src="admincp.php?action=menu&sid=<?=$sid?>" scrolling="yes" style="height: 100%; visibility: inherit; width: 100%; z-index: 1;overflow: auto;"></iframe></td>
<td><iframe frameborder="0" id="main" name="main" src="admincp.php?<?=$extra?>&sid=<?=$sid?>" scrolling="yes" style="height: 100%; visibility: inherit; width: 100%; z-index: 1;overflow: auto;"></iframe></td>
</tr></table>
</body>
</html>
<?

	exit();

}
$isfounder = isfounder();
if($action == 'menu') {
	require_once DISCUZ_ROOT.'./admin/menu.inc.php';
} elseif($action == 'header') {
	require_once DISCUZ_ROOT.'./admin/header.inc.php';
} elseif($action == 'logout') {
	$db->query("DELETE FROM {$tablepre}adminsessions WHERE uid='$discuz_uid'");
	loginmsg('logout_succeed', $indexname);
} else {
	$cpscript = '';
	if($adminid == 1) {

		if($action == 'home') {
			$cpscript = 'home';
		} elseif($action == 'runwizard' && isfounder()) {
			$cpscript = 'runwizard';
		} elseif($action == 'settings') {
			$cpscript = 'settings';
		} elseif($action == 'xspace') {
			$cpscript = 'supesite';
		} elseif($action == 'passport' || $action == 'shopex') {
			$cpscript = 'passport';
		} elseif($action == 'google_config') {
			$cpscript = 'google';
		} elseif($action == 'qihoo_config' || $action == 'qihoo_relatedthreads' || $action == 'qihoo_topics') {
			$cpscript = 'qihoo';
		} elseif($action == 'forumadd' || $action == 'forumsedit' || $action == 'forumsmerge' || $action == 'forumdetail' || $action == 'forumdelete' || $action == 'moderators' || $action == 'forumcopy' || $action == 'forumrecommend') {
			$cpscript = 'forums';
		} elseif($action == 'editmember' || $action == 'memberadd' || $action == 'members' || $action == 'membersmerge' || $action == 'editgroups' || $action == 'access' || $action == 'editcredits' || $action == 'editmedals' || $action == 'memberprofile' || $action == 'profilefields' || $action == 'ipban' ||  $action == 'banmember') {
			$cpscript = 'members';
		} elseif($action == 'usergroups' || $action == 'admingroups' || $action == 'ranks') {
			$cpscript = 'groups';
		} elseif($action == 'announcements') {
			$cpscript = 'announcements';
		} elseif($action == 'styles') {
			$cpscript = 'styles';
		} elseif($isfounder && ($action == 'templates' || $action == 'tpladd' || $action == 'tpledit' || $action == 'tplcopy')) {
			$cpscript = 'templates';
		} elseif($action == 'modmembers' || $action == 'modthreads' || $action == 'modreplies') {
			$cpscript = 'moderate';
		} elseif($action == 'recyclebin') {
			$cpscript = 'recyclebin';
		} elseif($action == 'alipay' || $action == 'orders' || $action == 'ec_credit') {
			$cpscript = 'ecommerce';
		} elseif($action == 'smilies') {
			$cpscript = 'smilies';
		} elseif($action == 'forumlinks' || $action == 'onlinelist' || $action == 'medals' || $action == 'censor' || $action == 'discuzcodes' || $action == 'icons' || $action == 'attachtypes' || $action == 'crons' || $action == 'creditslog' || $action == 'tags') {
			$cpscript = 'misc';
		} elseif($action == 'adv' || $action == 'advadd' || $action == 'advedit') {
			$cpscript = 'advertisements';
		} elseif($isfounder && ($action == 'export' || $action == 'optimize' || ($action == 'import' || $action == 'importzip' || $action == 'runquery'))) {
			$cpscript = 'database';
		} elseif($action == 'attachments') {
			$cpscript = 'attachments';
		} elseif($action == 'counter') {
			$cpscript = 'counter';
		} elseif($action == 'threads') {
			$cpscript = 'threads';
		} elseif($action == 'insenz') {
			$cpscript = 'insenz';
		} elseif($action == 'prune' || $action == 'pmprune') {
			$cpscript = 'prune';
		} elseif($action == 'updatecache' || $action == 'jswizard' || $action == 'fileperms') {
			$cpscript = 'tools';
		} elseif($action == 'filecheck' || $action == 'dbcheck' || $action == 'ftpcheck' || $action == 'mailcheck' || $action == 'imagepreview') {
			$cpscript = 'checktools';
		} elseif($action == 'creditwizard') {
			$cpscript = 'creditwizard';
		} elseif($action == 'plugins' || $action == 'pluginsconfig' || $action == 'pluginsedit' || $action == 'pluginhooks' || $action == 'pluginvars') {
			$cpscript = 'plugins';
		} elseif($action == 'illegallog' || $action == 'ratelog' || $action == 'modslog' || $action == 'medalslog' || $action == 'banlog' || $action == 'cplog' || $action == 'errorlog' || $action == 'invitelog') {
			$cpscript = 'logs';
		} elseif($action == 'tradelog') {
			$cpscript = 'tradelog';
		} elseif($action == 'faqlist' || $action == 'faqdetail') {
			$cpscript = 'faq';
		} elseif($action == 'magic_config' || $action == 'magic' || $action == 'magicadd' || $action == 'magicedit' || $action == 'magicmarket' || $action == 'magiclog') {
			$cpscript = 'magics';
		} elseif($action == 'upgrade' || $action == 'upgradedown' || $action == 'upgradeopenbbs') {
			$cpscript = 'upgrade';
		} elseif($action == 'project' || $action == 'projectadd' || $action == 'projectapply') {
			$cpscript = 'project';
		} elseif($action == 'threadtypes' || $action == 'typeoption' || $action == 'typedetail' || $action == 'optiondetail' || $action == 'typemodel' || $action == 'modeldetail') {
			$cpscript = 'threadtypes';
		} elseif($action == 'videoconfig' || $action == 'video' || $action == 'videobind' || $action == 'videoclass') {
			$cpscript = 'video';
		}

		if($radminid != $groupid) {
			$dactionarray = ($dactionarray = unserialize($db->result_first("SELECT disabledactions FROM {$tablepre}adminactions WHERE admingid='$groupid'"))) ? $dactionarray : array();
			if(in_array($action, $dactionarray)) {
				cpheader();
				cpmsg('action_noaccess');
			}
		}
	} elseif($adminid == 2 || $adminid == 3) {

		if($action == 'home') {
			$cpscript = 'home';
		} elseif((($allowedituser || $allowbanuser) && ($action == 'editmember' || $action == 'banmember')) || ($allowbanip && $action == 'ipban')) {
			$cpscript = 'members';
		} elseif($action == 'forumrules' || $action == 'forumrecommend') {
			$cpscript = 'forums';
		} elseif($allowpostannounce && $action == 'announcements') {
			$cpscript = 'announcements';
		} elseif(($allowmoduser && $action == 'modmembers') || ($allowmodpost && ($action == 'modthreads' || $action == 'modreplies'))) {
			$cpscript = 'moderate';
		} elseif(($allowcensorword && $action == 'censor') || $action == 'logout') {
			$cpscript = 'misc';
		} elseif($allowmassprune && $action == 'prune') {
			$cpscript = 'prune';
		} elseif($action == 'plugins') {
			$cpscript = 'plugins';
		} elseif($allowviewlog && ($action == 'ratelog' || $action == 'modslog' || $action == 'banlog')) {
			$cpscript = 'logs';
		}

	}
	if($cpscript) {
		require_once DISCUZ_ROOT.'./admin/'.$cpscript.'.inc.php';
	} else {
		cpheader();
		cpmsg('noaccess');
	}
	if($action != 'menu' && $action != 'header') {
		cpfooter();
	}

}
output();

function loginmsg($message, $url_forward = '', $msgtype = 'message') {
	extract($GLOBALS, EXTR_SKIP);
	$action = dhtmlspecialchars($action);
	$message = isset($msglang[$message]) ? $msglang[$message] : $message;

	if($msgtype == 'message') {
		$message = '<tr><td>&nbsp;</td><td align="center" colspan="3" >'.$message;
		if($url_forward) {
			$message .= "<br /><br /><a href=\"$url_forward\">$lang[message_redirect]</a>";
			$url_forward = transsid($url_forward);
			$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script><br /><br /><br /></td><td>&nbsp;</td></tr>";
		} else {
			$message .= '<br /><br /><br />';
		}
	} else {
		$extra = isset($action) && empty($frames) && $action != 'logout' ? '?frames=yes&'.$_SERVER['QUERY_STRING'] : (in_array($action, array('header', 'menu', 'logout')) ? '' : '?'.$_SERVER['QUERY_STRING']);
		$message = '<form method="post" name="login" action="admincp.php'.$extra.'">'.
			'<input type="hidden" name="sid" value="'.$sid.'">'.
			'<input type="hidden" name="frames" value="yes>'.
			'<input type="hidden" name="url_forward" value="'.$url_forward.'">'.
			'<tr><td>&nbsp;</td><td align="right">'.$lang['username'].':</td>'.
    			'<td>'.$discuz_userss.'</td><td><a href="'.$link_logout.'&referer='.$indexname.'" target="_blank">'.$lang['menu_logout'].'</a></td>'.
    			'<td>&nbsp;</td></tr>'.
    			'<tr><td>&nbsp;</td><td align="right">'.$lang['password'].':</td><td><input type="password" name="admin_password" size="25"></td>'.
    			'<td>&nbsp;</td><td>&nbsp;</td></tr>'.
  			'<tr><td>&nbsp;</td><td class="line1">&nbsp;</td>'.
    			'<td class="line1" align="center"><input type="submit" class="button" value="'.$lang['submit'].'" /></form><script language="JavaScript">document.login.admin_password.focus();</script></td>'.
    			'<td class="line1">&nbsp;</td><td>&nbsp;</td></tr>';

	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Discuz! Administrator's Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$charset?>">
<link href="./images/admincp/login.css" rel="stylesheet" type="text/css" />
</head>
<script language="JavaScript">
if(self.parent.frames.length != 0) {
	self.parent.location=document.location;
}
function redirect(url) {
	window.location.replace(url);
}
</script>
<br /><br /><br /><br />
<table width="600" border="0" cellpadding="8" cellspacing="0" class="logintable">
<tr class="loginheader"><td width="80"></td><td width="100"></td><td></td><td width="120"></td><td width="80"></td></tr>
<tr style="height:40px"><td>&nbsp;</td>
<td class="line1"><span style="color:#ffff66;font-size:14px;font-weight: bold;"><?=$lang['admin_page']?></span></td>
<td class="line1">&nbsp;</td>
<td class="line1">&nbsp;</td>
<td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td class="line2">&nbsp;</td><td class="line2">&nbsp;</td><td class="line2">&nbsp;</td><td>&nbsp;</td></tr>
<?=$message?>
<tr><td>&nbsp;</td><td class="line2">&nbsp;</td><td class="line2">&nbsp;</td><td class="line2">&nbsp;</td><td>&nbsp;</td></tr>
<tr><td colspan="5" align="center">Powered by <a href="http://www.discuz.net" target="_blank" style="color: #fff"><b>Discuz!</b></a>
&nbsp;&copy; 2001-2007 <a href="http://www.comsenz.com" target="_blank" style="color: #fff">Comsenz Inc.</a></td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table>
</html>
<?
	dexit();
}

?>