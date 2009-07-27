<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: viewcomment.php 11952 2009-04-16 06:07:31Z zhaolei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

if(submitcheck('submitcomm', 1)) {
	$itemid = empty($_POST['itemid'])?0:intval($_POST['itemid']);
	if(empty($itemid)) showmessage('not_found', S_URL);

	if(empty($_SGLOBAL['supe_uid'])) {
		if(empty($_SCONFIG['allowguest'])) {
			setcookie('_refer', rawurlencode(geturl('action/viewcomment/itemid/'.$itemid, 1)));
			showmessage('no_login', geturl('action/login'));
		}
	}
	if(!empty($_SCONFIG['commenttime']) && $_SGLOBAL['group']['groupid'] != 1) {
		if($_SGLOBAL['timestamp'] - $_SGLOBAL['member']['lastcommenttime'] < $_SCONFIG['commenttime']) {
			showmessage('comment_too_much');
		}
	}
	
	//更新用户最新更新时间
	if($_SGLOBAL['supe_uid']) {
		updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp'], 'lastcommenttime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));	
	}
	
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE itemid=\''.$itemid.'\' AND type=\'news\' AND folder=\'1\' AND allowreply=\'1\'');
	if(!$item = $_SGLOBAL['db']->fetch_array($query)) showmessage('no_permission', S_URL);

	$_POST['message'] = shtmlspecialchars(trim($_POST['message']));
	if(strlen($_POST['message']) < 2 || strlen($_POST['message']) > 10000) showmessage('message_length_error');
	$_POST['message'] = str_replace('[br]', '<br>', $_POST['message']);
	$_POST['message'] = preg_replace("/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is", "<blockquote class=\"xspace-quote\">\\1</blockquote>", $_POST['message']);
	
	//回复词语屏蔽
	$_POST['message'] = censor($_POST['message']);

	$setsqlarr = array(
		'itemid' => $itemid,
		'type' => 'news',
		'uid' => '0',
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'ip' => $_SGLOBAL['onlineip'],
		'dateline' => $_SGLOBAL['timestamp'],
		'rates' => $_POST['rates'],
		'message' => $_POST['message']
	);
	inserttable('spacecomments', $setsqlarr);

	if(allowfeed() && $item['uid'] != $_SGLOBAL['supe_uid'] && $_POST['addfeed']) {
		$feed['icon'] = 'post';
		$feed['title_template'] = 'feed_news_comment_title';
		$feed['title_data'] = array(
			'author' =>'<a href="space.php?uid='.$item['uid'].'" >'.$item['username'].'</a>',
			'mommentpost' =>'<a href="'.geturl('action/viewnews/itemid/'.$itemid).'" >'.$item['subject'].'</a>'
		);
		postfeed($feed);
	}
	
	if($_POST['rates']>0) {
		$goodrate = $_POST['rates'];
		$badrate = 0;
	} elseif($_POST['rates'] < 0) {
		$goodrate = 0;
		$badrate = 0 - $_POST['rates'];
	} else {
		$goodrate = 0;
		$badrate = 0;
	}
	$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET lastpost='.$_SGLOBAL['timestamp'].', replynum=replynum+1,goodrate=goodrate+'.$goodrate.',badrate=badrate+'.$badrate.' WHERE itemid=\''.$itemid.'\'');

	showmessage('do_success', geturl('action/viewcomment/itemid/'.$itemid));
}

if(!empty($_SGET['op']) && $_SGET['op'] == 'delete') {

	$cid = empty($_SGET['cid'])?0:intval($_SGET['cid']);
	if(empty($cid)) showmessage('not_found', S_URL);
	$itemid = empty($_SGET['itemid'])?0:intval($_SGET['itemid']);
	if(empty($itemid)) showmessage('not_found', S_URL);

	$deleteflag = false;

	if(empty($_SGLOBAL['group'])) {
		showmessage('no_permission');
	}

	if($cid && $itemid && $_SGLOBAL['supe_uid']) {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacecomments').' WHERE cid=\''.$cid.'\'');
		if($comment = $_SGLOBAL['db']->fetch_array($query)) {
			if($_SGLOBAL['group']['groupid'] == 1 || $comment['authorid'] == $_SGLOBAL['supe_uid']) {
				if($comment['rates'] > 0) {
					$ratesql = ',goodrate=goodrate-'.$comment['rates'];
				} elseif ($comment['rates'] < 0) {
					$ratesql = ',badrate=badrate-'.abs($comment['rates']);
				} else {
					$ratesql = '';
				}
				$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET replynum=replynum-1'.$ratesql.' WHERE itemid=\''.$comment['itemid'].'\'');
				$_SGLOBAL['db']->query('DELETE FROM '.tname('spacecomments').' WHERE cid=\''.$cid.'\'');
				$deleteflag = true;
			}
		}
	}
	if($deleteflag) {
		showmessage('do_success', geturl('action/viewcomment/itemid/'.$itemid));
	} else {
		showmessage('no_permission');
	}
}

$perpage = 20;

$page = empty($_SGET['page'])?0:intval($_SGET['page']);
$page = ($page<1)?1:$page;
$start = ($page-1)*$perpage;

$itemid = empty($_SGET['itemid']) ? 0 : intval($_SGET['itemid']);
if(empty($itemid)) showmessage('not_found', S_URL);

$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE itemid=\''.$itemid.'\' AND type=\'news\' AND folder=\'1\' AND allowreply=\'1\'');
if(!$item = $_SGLOBAL['db']->fetch_array($query)) showmessage('not_found', S_URL);

$listcount = $item['replynum'];
$iarr = array();
$multipage = '';
if($listcount) {
	$i = ($page-1)*$perpage + 1;
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacecomments').' WHERE itemid=\''.$itemid.'\' ORDER BY dateline DESC LIMIT '.$start.','.$perpage);
	while ($comment = $_SGLOBAL['db']->fetch_array($query)) {
		$comment['message'] = snl2br($comment['message']);
		$comment['num'] = $i;
		$i++;
		if(empty($comment['author'])) $comment['author'] = 'Guest';
		$iarr[] = $comment;
	}
	$urlarr = array('action'=>'viewcomment', 'itemid' => $itemid);
	$multipage = multi($listcount, $perpage, $page, $urlarr, 0);
}

$title = $item['subject'].' - '.$_SCONFIG['sitename'];

include template('news_viewcomment');

ob_out();

?>