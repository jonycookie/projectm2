<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_swfupload.php 7280 2008-05-05 06:42:55Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_cp.php');

$op = empty($_GET['op'])?'':$_GET['op'];
$isupload = empty($_GET['cam']) ? true : false;

if(!empty($_POST['uid'])) {
	$_SGLOBAL['supe_uid'] = intval($_POST['uid']);
	if(empty($_SGLOBAL['supe_uid']) || $_POST['hash'] != md5($_SGLOBAL['supe_uid'].UC_KEY)) {
		exit();
	}
} elseif (empty($_SGLOBAL['supe_uid'])) {
	showmessage('to_login', 'do.php?ac=login', 0);
}

if($op == "finish") {

	$albumid = intval($_GET['albumid']);
	//feed
	album_feed($albumid);
	exit();

} elseif($op == 'config') {
	
	$hash = md5($_SGLOBAL['supe_uid'].UC_KEY);
	
	if($isupload && !checkperm('allowupload')) {
		$hash = '';
	} else {
		
		$directory = sreaddir(S_ROOT.'./image/foreground');
		foreach($directory as $key => $value) {
			$dirstr = S_ROOT.'./image/foreground/'.$value;
			if(is_dir($dirstr)) {
				$filearr = sreaddir($dirstr, array('jpg','jpeg','gif','png'));
				if(!empty($filearr)) {
					if(is_file($dirstr.'/categories.txt')) {
						$catfile = @file($dirstr.'/categories.txt');
						$dirarr[$key][0] = trim($catfile[0]);
					} else {
						$dirarr[$key][0] = trim($value);
					}
					$dirarr[$key][1] = trim('image/foreground/'.$value.'/');
					$dirarr[$key][2] = $filearr;
				}
			}
		}
	}
	$max = @ini_get(upload_max_filesize);
	$unit = strtolower(substr($max, -1, 1));
	if($unit == 'k') {
		$max = intval($max)*1024;
	} elseif($unit == 'm') {
		$max = intval($max)*1024*1024;
	} elseif($unit == 'g') {
		$max = intval($max)*1024*1024*1024;
	}
	$albums = getalbums($_SGLOBAL['supe_uid']);
	
} elseif($op == "screen") {
	
	if(empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
		$GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
	}
	$uploadfiles = $status = "failure";
	
	//如果为空则代表发送过来的流有错误
	if(!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
		$_SERVER['HTTP_ALBUMID'] = addslashes(siconv(urldecode($_SERVER['HTTP_ALBUMID']), $_SC['charset'], "UTF-8"));
		$uploadfilesarr = stream_save($GLOBALS['HTTP_RAW_POST_DATA'], $_SERVER['HTTP_ALBUMID'], 'jpg');
	}
	$uploadResponse = true;
	$proid = $albumid = 0;
	if($uploadfilesarr && is_array($uploadfilesarr)) {
		$uploadfiles = $status = "success";
		$albumid = $uploadfilesarr['albumid'];
	}

} elseif($_FILES && $_POST) {
	
	if($_FILES["Filedata"]['error']) {
		$uploadfiles = lang('file_is_too_big');
	} else {
		$_FILES["Filedata"]['name'] = addslashes(siconv(urldecode($_FILES["Filedata"]['name']), $_SC['charset'], "UTF-8"));
		$_POST['albumid'] = addslashes(siconv(urldecode($_POST['albumid']), $_SC['charset'], "UTF-8"));
		$uploadfiles = pic_save($_FILES["Filedata"], $_POST['albumid'], addslashes(siconv(urldecode($_POST['title']), $_SC['charset'], "UTF-8")));
	}
	$proid = $_POST['proid'];
	$uploadResponse = true;
	$albumid = 0;
	if($uploadfiles && is_array($uploadfiles)) {
		$status = "success";
		$albumid = $uploadfiles['albumid'];
	} else {
		$status = "failure";
	}
}

include template("do_swfupload");

$outxml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$outxml .= siconv(ob_get_contents(), 'UTF-8');
obclean();
@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");
@header("Content-type: application/xml");
echo $outxml;

?>