<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: index.php 10115 2007-08-24 00:58:08Z cnteacher $
*/

$kw_spiders		= 'Bot|Crawl|Spider';
			// keywords regular expression of search engine spiders

$kw_browsers		= 'MSIE|Netscape|Opera|Konqueror|Mozilla';
			// keywords regular expression of Internet browsers

$kw_searchengines	= 'google|yahoo|msn|baidu|yisou|sogou|iask|zhongsou|sohu|sina|163';
			// keywords regular expression of search engine names

error_reporting(0);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

ob_start();

$runtime = explode (' ', microtime());
$starttime = $runtime[1] + $runtime[0];

define('DISCUZ_ROOT', '../');
define('IN_DISCUZ', TRUE);
define('CURSCRIPT', 'archiver');

require_once '../forumdata/cache/cache_settings.php';
require_once '../forumdata/cache/cache_archiver.php';

if(!$_DCACHE['settings']['archiverstatus']) {
	exit('Sorry, Discuz! Archiver is not available.');
} elseif($_DCACHE['settings']['bbclosed']) {
	exit('Sorry, the bulletin board has been closed temporarily.');
}

$_SERVER = empty($_SERVER) ? $HTTP_SERVER_VARS : $_SERVER;

require_once '../config.inc.php';
require_once '../include/db_'.$database.'.class.php';
require_once '../templates/default/archiver.lang.php';
require_once '../forumdata/cache/cache_forums.php';
require_once '../forumdata/cache/style_'.$_DCACHE['settings']['styleid'].'.php';

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
$db->select_db($dbname);
unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$boardurl = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF, 0, strpos($PHP_SELF, 'archiver/'));

$groupid = 7;
$extgroupids = '';

header('Content-Type: text/html; charset='.$charset);

$navtitle = $meta_contentadd = '';
$fid = $page = $tid = 0;
$qm = $_DCACHE['settings']['rewritestatus'] & 16 ? '' : '?';
$fullversion = array('title' => $_DCACHE['settings']['bbname'], 'link' => $_DCACHE['settings']['indexname']);
$querystring = preg_replace("/\.html$/i", '', trim($_SERVER['QUERY_STRING']));
if($querystring) {
	$queryparts = explode('-', $querystring);
	$lastpart = '';
	foreach($queryparts as $querypart) {
		if(empty($lastpart)) {
			$lastpart = in_array($querypart, array('fid', 'page', 'tid')) ? $querypart : '';
		} else {
			$$lastpart = intval($querypart);
			$lastpart = '';
		}
	}
}

if($tid) {
	$action = 'thread';
	$forward = 'viewthread.php?tid='.$tid;
} elseif($fid) {
	$action = 'forum';
	$forward = 'forumdisplay.php?fid='.$fid;
} else {
	$action = 'index';
	$forward = 'index.php';
}

if($_DCACHE['settings']['archiverstatus'] != 1 && !preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT']) &&
	(($_DCACHE['settings']['archiverstatus'] == 2 && preg_match("/($kw_searchengines)/", $_SERVER['HTTP_REFERER'])) ||
	($_DCACHE['settings']['archiverstatus'] == 3 && preg_match("/($kw_browsers)/", $_SERVER['HTTP_USER_AGENT'])))) {
	header("Location: $boardurl$forward");
	exit;
}

if(($globaladvs = $_DCACHE['settings']['globaladvs']) || !empty($_DCACHE['advs'])) {
        $redirectadvs = $_DCACHE['settings']['redirectadvs'];
	require_once '../include/advertisements.inc.php';
}

$headerbanner = !empty($advlist['headerbanner']) ? $advlist[headerbanner] : '';

require_once "./include/$action.inc.php";

$runtime = explode(' ', microtime());
$totaltime = round(($runtime[1] + $runtime[0] - $starttime), 6);

?>
<div class="archiver_fullversion"><?=$lang['full_version']?>: <strong><a href="<?=$fullversion['link']?>" target="_blank"><?=$fullversion['title']?></a></strong></div>
</div>
<?
if(!empty($advlist['footerbanner1'])) {
	echo '<div class="archiver_banner">'.$advlist[footerbanner1].'</div>';
}
if(!empty($advlist['footerbanner2'])) {
	echo '<div class="archiver_banner">'.$advlist[footerbanner2].'</div>';
}
if(!empty($advlist['footerbanner3'])) {
	echo '<div class="archiver_banner">'.$advlist[footerbanner3].'</div>';
}
?>
<p id="copyright">Powered by <strong><a href="http://www.discuz.net" target="_blank">Discuz! Archiver</a></strong> <em><?=$_DCACHE['settings']['version']?></em>&nbsp;
&copy; 2001-2006 <a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a><br /><br /></p>
</body>
</html>
<?

function multi($total, $page, $perpage, $link) {
	$pages = @ceil($total / $perpage) + 1;
	$pagelink = '';
	if($pages > 1) {
		$pagelink .= "{$GLOBALS[lang][page]}: \n";
		$pagestart = $page - 10 < 1 ? 1 : $page - 10;
		$pageend = $page + 10 >= $pages ? $pages : $page + 10;
		for($i = $pagestart; $i < $pageend; $i++) {
			$pagelink .= ($i == $page ? "<strong>[$i]</strong>" : "<a href=archiver/$link-page-$i.html>$i</a>")." \n";
		}
	}
	return $pagelink;
}

function forumperm($viewperm) {
	return (empty($viewperm) || ($viewperm && strstr($viewperm, "\t7\t")));
}

function forumformulaperm($formula) {
	$formula = unserialize($formula);$formula = $formula[1];
	if(!$formula) {
		return TRUE;
	}
	$_DSESSION = array();
	@eval("\$formulaperm = ($formula) ? TRUE : FALSE;");
	return $formulaperm;
}

?>