<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: post.php 11493 2007-12-05 07:19:46Z tiger $
*/

define('CURSCRIPT', 'post');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/post.func.php';

$_DTYPE = $checkoption = $optionlist = array();
if($typeid) {
	threadtype_checkoption();
}

if(empty($action)) {

	showmessage('undefined_action', NULL, 'HALTED');

} elseif($action == 'smilies' && $smileyinsert) {

	$smile = isset($_DCOOKIE['smile']) ? explode('D', $_DCOOKIE['smile']) : array();
	$stypeid = intval($stypeid ? $stypeid : $smile[0]);
	$stypeid = isset($_DCACHE['smileytypes'][$stypeid]) ? $stypeid : (isset($_DCACHE['smileytypes'][STYPEID]) ? STYPEID : key($_DCACHE['smileytypes']));
	$smilies = $_DCACHE['smilies_display'][$stypeid];

	$page = max(1, intval(isset($_GET['page']) ? $_GET['page'] : (!isset($_GET['stypeid']) || $_GET['stypeid'] == $smile[0] ? $smile[1] : 1)));
	$spp = $smcols * $smrows;
	$multipage = multi(count($smilies), $spp, $page, 'post.php?action=smilies&stypeid='.$stypeid.'&inajax=1', 0, 10, FALSE, TRUE);
        $smilies = arrayslice($smilies, $spp * ($page - 1), $spp);

	dsetcookie('smile', $stypeid.'D'.$page, 86400 * 365);
        include template('post_smilies');
	exit;

}elseif($action == 'threadtypes') {

	threadtype_optiondata();
	$template = intval($operate) ? 'search_typeoption' : 'post_typeoption';
	include template($template);
	exit;

} elseif(($forum['simple'] & 1) || $forum['redirect']) {
	showmessage('forum_disablepost');
}

require_once DISCUZ_ROOT.'./include/discuzcode.func.php';


$navigation = $navtitle = $thread = '';

if($action == 'edit' || $action == 'reply') {

	if($thread = $db->fetch_first("SELECT * FROM {$tablepre}threads WHERE tid='$tid'".($auditstatuson ? '' : " AND displayorder>='0'"))) {

		$navigation = "&raquo; <a href=\"viewthread.php?tid=$tid\">$thread[subject]</a>";
		$navtitle = $thread['subject'].' - ';
		if($thread['readperm'] && $thread['readperm'] > $readaccess && !$forum['ismoderator'] && $thread['authorid'] != $discuz_uid) {
			showmessage('thread_nopermission', NULL, 'NOPERM');
		}

		$fid = $thread['fid'];
		$special = $thread['special'];

	} else {
		showmessage('thread_nonexistence');
	}

}

$navigation = "&raquo; <a href=\"forumdisplay.php?fid=$fid".($extra ? '&'.preg_replace("/^(&)*/", '', $extra) : '')."\">$forum[name]</a> $navigation";
$navtitle = $navtitle.strip_tags($forum['name']).' - ';

if($forum['type'] == 'sub') {
	$fup = $db->fetch_first("SELECT name, fid FROM {$tablepre}forums WHERE fid='$forum[fup]'");
	$navigation = "&raquo; <a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> $navigation";
	$navtitle = $navtitle.strip_tags($fup['name']).' - ';
}

periodscheck('postbanperiods');

if($forum['password'] && $forum['password'] != $_DCOOKIE['fidpw'.$fid]) {
	dheader("Location: {$boardurl}forumdisplay.php?fid=$fid&amp;sid=$sid");
}

if(empty($forum['allowview'])) {
	if(!$forum['viewperm'] && !$readaccess) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($forum['viewperm'] && !forumperm($forum['viewperm'])) {
		showmessage('forum_nopermission', NULL, 'NOPERM');
	}
}

forumformulaperm($forum['formulaperm']);

if(!$adminid && $newbiespan && (!$lastpost || $timestamp - $lastpost < $newbiespan * 3600)) {
	if($timestamp - ($db->result_first("SELECT regdate FROM {$tablepre}members WHERE uid='$discuz_uid'")) < $newbiespan * 3600) {
		showmessage('post_newbie_span');
	}
}

$special = empty($special) || !is_numeric($special) || $special < 0 || $special > 6 ? 0 : intval($special);

$allowpostattach = !empty($forum['allowpostattach']) || (!$forum['postattachperm'] && $allowpostattach) || ($forum['postattachperm'] && forumperm($forum['postattachperm']));
$attachextensions = $forum['attachextensions'] ? $forum['attachextensions'] : $attachextensions;
$enctype = $allowpostattach ? 'enctype="multipart/form-data"' : '';
$maxattachsize_kb = $maxattachsize / 1024;

$postcredits = $forum['postcredits'] ? $forum['postcredits'] : $creditspolicy['post'];
$replycredits = $forum['replycredits'] ? $forum['replycredits'] : $creditspolicy['reply'];
$digestcredits = $forum['digestcredits'] ? $forum['digestcredits'] : $creditspolicy['digest'];
$postattachcredits = $forum['postattachcredits'] ? $forum['postattachcredits'] : $creditspolicy['postattach'];

$maxprice = isset($extcredits[$creditstrans]) ? $maxprice : 0;

$extra = rawurlencode($extra);
$blogcheck = empty($isblog) && empty($addtoblog) ? '' : 'checked="checked"';
$notifycheck = empty($emailnotify) ? '' : 'checked="checked"';
$stickcheck = empty($sticktopic) ? '' : 'checked="checked"';
$digestcheck = empty($addtodigest) ? '' : 'checked="checked"';

$subject = isset($subject) ? dhtmlspecialchars(censor(trim($subject))) : '';
$message = isset($message) ? censor(trim($message)) : '';
$polloptions = isset($polloptions) ? censor(trim($polloptions)) : '';
$readperm = isset($readperm) ? intval($readperm) : 0;
$price = isset($price) ? intval($price) : 0;

if(empty($bbcodeoff) && !$allowhidecode && !empty($message) && preg_match("/\[hide=?\d*\].+?\[\/hide\]/is", preg_replace("/(\[code\](.+?)\[\/code\])/is", ' ', $message))) {
	showmessage('post_hide_nopermission');
}

if(periodscheck('postmodperiods', 0)) {
	$modnewthreads = $modnewreplies = 1;
} else {
	$censormod = censormod($subject."\t".$message);
	$modnewthreads = (!$allowdirectpost || $allowdirectpost == 1) && ($forum['modnewposts'] || $censormod) ? 1 : 0;
	$modnewreplies = (!$allowdirectpost || $allowdirectpost == 2) && ($forum['modnewposts'] == 2 || $censormod) ? 1 : 0;
}

$urloffcheck = $usesigcheck = $smileyoffcheck = $codeoffcheck = $htmloncheck = $emailcheck = '';

$seccodecheck = ($seccodestatus & 4) && (!$seccodedata['minposts'] || $posts < $seccodedata['minposts']);
$secqaacheck = $secqaa['status'][2] && (!$secqaa['minposts'] || $posts < $secqaa['minposts']);

if($iscircle = $supe['status'] && $supe['circlestatus'] && $forum['status'] == 2) {
	unset($forum['threadtypes']);
}

$allowpostpoll = $allowpost && $allowpostpoll && ($forum['allowpostspecial'] & 1);
$allowposttrade = $allowpost && $allowposttrade && ($forum['allowpostspecial'] & 2);
$allowpostreward = $allowpost && $allowpostreward && ($forum['allowpostspecial'] & 4) && isset($extcredits[$creditstrans]);
$allowpostactivity = $allowpost && $allowpostactivity && ($forum['allowpostspecial'] & 8);
$allowpostdebate = $allowpost && $allowpostdebate && ($forum['allowpostspecial'] & 16);
$allowpostvideo = $allowpost && $allowpostvideo && ($forum['allowpostspecial'] & 32) && $videoopen;

$allowanonymous = $forum['allowanonymous'] || $allowanonymous ? 1 : 0;

if($action == 'newthread' && $forum['allowspecialonly'] && !$special) {
	if($allowpostpoll) {
		$special = 1;
	} elseif($allowposttrade) {
		$special = 2;
	} elseif($allowpostreward) {
		$special = 3;
	} elseif($allowpostactivity) {
		$special = 4;
	} elseif($allowpostdebate) {
		$special = 5;
	} elseif($allowpostvideo) {
		$special = 6;
	}
	if(!$special) {
		showmessage('undefined_action', NULL, 'HALTED');
	}
}


$editorid = 'posteditor';
$editoroptions = str_pad(decbin($editoroptions), 2, 0, STR_PAD_LEFT);
$editormode = $editormode == 2 ? $editoroptions{0} : $editormode;
$allowswitcheditor = $editoroptions{1};
$advanceeditor = $special ? 0 : 1;
$previewdisplay = !empty($previewpost) ? '' : 'none';

if(!empty($previewpost) || (empty($previewpost) && empty($topicsubmit) && empty($replysubmit) && empty($editsubmit))) {

	!$typeid && preg_replace("/.*typeid%3D(\d+).*/e", "\$typeid = \\1;", $extra);

	if($discuz_uid && $sigstatus && !$usesigcheck) {
		$usesigcheck = 'checked="checked"';
	}

	$trade = array();
	if(($action == 'newthread' || $action == 'reply') && $special == 2) {
		$trade['account'] = $db->result_first("SELECT alipay FROM {$tablepre}memberfields WHERE uid='$discuz_uid'");
		$trade['amount'] = 1;
		$trade['transport'] = 2;
	}

	$currtime = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	if(empty($previewpost)) {

		$subject = $message = $polloptions = '';

	} else {

		$subject = stripslashes($subject);
		$message = stripslashes($message);
		$message_preview = discuzcode($message, !empty($smileyoff), !empty($bbcodeoff), !empty($htmlon), $forum['allowsmilies'], $forum['allowbbcode'], $forum['allowimgcode'], $forum['allowhtml'], 0, 1);
		$message = $editormode == 1 && $bbinsert && !(isopera() && isopera() < 9) ? $message_preview : dhtmlspecialchars($message);
		$urloffcheck = !empty($parseurloff) ? 'checked="checked"' : '';
		$usesigcheck = !empty($usesig) ? 'checked="checked"' : '';
		$smileyoffcheck = !empty($smileyoff) ? 'checked="checked"' : '';
		$codeoffcheck = !empty($bbcodeoff) ? 'checked="checked"' : '';
		$htmloncheck = !empty($htmlon) ? 'checked="checked"' : '';
		$emailcheck = !empty($emailnotify) ? 'checked="checked"' : '';

		$topicsubmit = $replysubmit = $editsubmit = '';

	}

} else {
	if((!empty($topicsubmit) || !empty($replysubmit)) && (($seccodecheck && !isset($seccodeverify)) || ($secqaacheck && !isset($secanswer)))) {
		if($seccodecheck) {
			$seccode = random(6, 1) + $seccode{0} * 1000000;
		}
		if($secqaacheck) {
			$seccode = random(1, 1) * 1000000 + substr($seccode, -6);
		}

		$request = array
			(
			'method' => $_SERVER['REQUEST_METHOD'],
			'action' => $PHP_SELF,
			'elements' => ''
			);

		$quesand = '?';
		foreach($_GET as $key => $value) {
			$request['action'] .= $quesand.rawurlencode($key).'='.rawurlencode($value);
			$quesand = '&';
		}
		foreach($_POST as $key => $value) {
			if(is_array($value)) {
				foreach($value as $arraykey => $arrayvalue) {
					$request['elements'] .= '<input type="hidden" name="'.dhtmlspecialchars($key.'['.$arraykey.']').'" value="'.dhtmlspecialchars(stripslashes($arrayvalue)).'" />';
				}
			} else {
				$request['elements'] .= '<input type="hidden" name="'.dhtmlspecialchars($key).'" value="'.dhtmlspecialchars(stripslashes($value)).'" />';
			}
		}

		include template('post_seccode');
		dexit();
	}

}

if($action == 'newthread') {
	require_once DISCUZ_ROOT.'./include/newthread.inc.php';
} elseif($action == 'reply') {
	require_once DISCUZ_ROOT.'./include/newreply.inc.php';
} elseif($action == 'edit') {
	require_once DISCUZ_ROOT.'./include/editpost.inc.php';
} elseif($action == 'import') {
	require_once DISCUZ_ROOT.'./include/supesite_import.inc.php';
} elseif($action == 'newtrade') {
	require_once DISCUZ_ROOT.'./include/newtrade.inc.php';
}

?>