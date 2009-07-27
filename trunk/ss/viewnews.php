<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: viewnews.php 11880 2009-03-30 05:52:43Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$itemid = empty($_SGET['itemid']) ? 0 : intval($_SGET['itemid']);
$page = empty($_SGET['page']) ? 1 : intval($_SGET['page']);
$page = ($page<2) ? 1 : $page;
$styletitle = '';

//页面跳转
if($itemid && !empty($_SCONFIG['htmlviewnews'])) {
	$_SHTML['action'] = 'viewnews';
	$_SHTML['itemid'] = $itemid;
	$_SHTML['page'] = $page;
	$_SGLOBAL['htmlfile'] = gethtmlfile($_SHTML);
	ehtml('get', $_SCONFIG['htmlviewnewstime']);
	$_SCONFIG['debug'] = 0;
}

$news = array();
if($itemid) {
	$query = $_SGLOBAL['db']->query('SELECT i.* FROM '.tname('spaceitems').' i WHERE i.itemid=\''.$itemid.'\' AND type=\'news\'');
	$news = $_SGLOBAL['db']->fetch_array($query);
}
if(empty($news)) showmessage('not_found', S_URL);

if($news['folder'] != 1) {
	$_SCONFIG['htmlviewnews'] = 0;
	if($_SGLOBAL['supe_uid'] != $news['uid'] && $_SGLOBAL['group']['groupid'] != 1) showmessage('not_view');
}


//更新统计数
$isupdate = freshcookie($itemid);
if($isupdate || !$_SCONFIG['updateview']) updateviewnum($itemid);

$query = $_SGLOBAL['db']->query('SELECT f.*, ff.name AS upname FROM '.tname('categories').' f LEFT JOIN '.tname('categories').' ff ON ff.catid=f.upid WHERE f.catid=\''.$news['catid'].'\'');
$thecat = $_SGLOBAL['db']->fetch_array($query);

$listcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\''), 0);
if($page > $listcount) $_SHTML['page'] = $page = 1;
$start = $page - 1;
$query = $_SGLOBAL['db']->query('SELECT ii.* FROM '.tname('spacenews').' ii WHERE ii.itemid=\''.$itemid.'\' ORDER BY ii.pageorder, ii.nid LIMIT '.$start.', 1');
if($msg = $_SGLOBAL['db']->fetch_array($query)) {
	$news = array_merge($news, $msg);
} else {
	updatetable('spaceitems', array('folder'=>3), array('itemid'=>$itemid));
}

if(!empty($news['newsurl'])) {
	sheader($news['newsurl']);
}

$news['attacharr'] = array();

$multipage = '';
if ($listcount > 1) {
	$urlarr = array('action'=>'viewnews', 'itemid'=>$itemid);
	$multipage = multi($listcount, 1, $page, $urlarr, 0);
} else {
	if($page == 1 && $news['haveattach']) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE itemid=\''.$itemid.'\'');
		while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
			if(strpos($news['message'], $attach['thumbpath']) === false && strpos($news['message'], $attach['filepath']) === false && strpos($news['message'], 'batch.download.php?aid='.$attach['aid']) === false) {
				$attach['filepath'] = A_URL.'/'.$attach['filepath'];
				$attach['thumbpath'] = A_URL.'/'.$attach['thumbpath'];
				$attach['url'] = S_URL.'/batch.download.php?aid='.$attach['aid'];
				$news['attacharr'][] = $attach;
			}
		}
	}
}

if(empty($news['newsauthor'])) $news['newsauthor'] = $news['username'];

$description = str_replace(array('&nbsp;', "\r", "\n", '\'', '"'), '', cutstr(trim(strip_tags($news['message'])), 200));

if($_SSCONFIG['newsjammer']) {
	mt_srand((double)microtime() * 1000000);
	$news['message'] = preg_replace("/(\<br\>|\<br\ \/\>|\<br\/\>|\<p\>|\<\/p\>)/ie", "sjammer('\\1')", $news['message']);
}
$newtagarr = array();
if(!empty($news['includetags'])) {	
	$newtagarr = explode("\t", $news['includetags']);
	if(!empty($_SCONFIG['allowtagshow'])) $news['message'] = tagshow($news['message'], $newtagarr);
}
$relativetagarr = array();
if(!empty($news['relativetags'])) {	
	$relativetagarr = unserialize($news['relativetags']);
}

$news['custom'] = array('name'=>'', 'key'=>array(), 'value'=>array());
if($page == 1 && !empty($news['customfieldid'])) {
	$news['custom']['value'] = unserialize($news['customfieldtext']);
	if(!empty($news['custom']['value'])) {
		foreach ($news['custom']['value'] as $key => $value) {
			if(is_array($value)) {
				$news['custom']['value'][$key] = implode(', ', $value);
			}
		}
	}
	$query = $_SGLOBAL['db']->query('SELECT name, customfieldtext FROM '.tname('customfields').' WHERE customfieldid=\''.$news['customfieldid'].'\'');
	$value = $_SGLOBAL['db']->fetch_array($query);
	$news['custom']['name'] = $value['name'];
	$news['custom']['key'] = unserialize($value['customfieldtext']);
}

$listcount = $news['replynum'];
$_SCONFIG['viewspace_pernum'] = intval($_SCONFIG['viewspace_pernum']);
if($listcount) {
	$query = $_SGLOBAL['db']->query('SELECT c.* FROM '.tname('spacecomments').' c WHERE c.itemid=\''.$news['itemid'].'\' ORDER BY c.dateline DESC LIMIT 0, '.$_SCONFIG['viewspace_pernum']);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['message'] = snl2br($value['message']);
		if(empty($value['author'])) $value['author'] = 'Guest';
		$commentlist[] = $value;
	}
}

if(empty($newtagarr)) $newtagarr = array($news['subject'], $lang['news']);
$keywords = implode(',', $newtagarr);

$guidearr = array();
$guidearr[] = array('url' => geturl('action/news'),'name' => $channels['menus']['news']['name']);
if(!empty($thecat['upname'])) {
	$guidearr[] = array('url' => geturl('action/category/catid/'.$thecat['upid']),'name' => $thecat['upname']);
}
$guidearr[] = array('url' => geturl('action/category/catid/'.$thecat['catid']),'name' => $thecat['name']);


$title = $news['subject'].' - '.$_SCONFIG['sitename'];

if(!empty($thecat['viewtpl']) && file_exists(S_ROOT.'./templates/'.$_SCONFIG['template'].'/'.$thecat['viewtpl'].'.html.php')) {
	$tplname = $thecat['viewtpl'];
} else {
	$tplname = 'news_view';
}

if(!empty($news['styletitle'])) {
	$news['styletitle'] = mktitlestyle($news['styletitle']);
}

$title = strip_tags($title);
$keywords = strip_tags($keywords);
$description = strip_tags($description);

include template($tplname);

ob_out();

if(!empty($_SCONFIG['htmlviewnews'])) {
	ehtml('make');
} else {
	maketplblockvalue('cache');
}

function freshcookie($itemid) {
	global $_SC, $_SGLOBAL;

	$isupdate = 1;
	$old = empty($_COOKIE[$_SC['cookiepre'].'supe_refresh_items'])?0:trim($_COOKIE[$_SC['cookiepre'].'supe_refresh_items']);
	$itemidarr = explode('_', $old);
	if(in_array($itemid, $itemidarr)) {
		$isupdate = 0;
	} else {
		$itemidarr[] = trim($itemid);
		ssetcookie('supe_refresh_items', implode('_', $itemidarr));
	}
	if(empty($_COOKIE)) $isupdate = 0;

	return $isupdate;
}

function updateviewnum($itemid) {
	global $_SGLOBAL;

	$logfile = S_ROOT.'./log/viewcount.log';
	if(@$fp = fopen($logfile, 'a+')) {
		fwrite($fp, $itemid."\n");
		fclose($fp);
		@chmod($logfile, 0777);
	} else {
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET viewnum=viewnum+1 WHERE itemid=\''.$itemid.'\'');
	}
}

function sjammer($str) {
	global $_SGLOBAL, $_SCONFIG;

	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<span style="display:none">'.$_SCONFIG['sitename'].$randomstr.'</span>'.$str :
		$str.'<span style="display:none">'.$randomstr.$_SGLOBAL['supe_uid'].'</span>';
}
?>