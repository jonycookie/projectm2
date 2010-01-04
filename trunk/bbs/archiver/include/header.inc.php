<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: header.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<base href="<?=$boardurl?>" />
<title><?=$navtitle?> <?=$_DCACHE['settings']['bbname']?> <?=$_DCACHE['settings']['seotitle']?> - Powered by Discuz! Archiver</title>
<?=$_DCACHE['settings']['seohead']?>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$charset?>" />
<meta name="keywords" content="Discuz!,Board,Comsenz,forums,bulletin board,<?=$_DCACHE['settings']['seokeywords']?>" />
<meta name="description" content="<?=$meta_contentadd?> <?=$_DCACHE['settings']['bbname']?> <?=$_DCACHE['settings']['seodescription']?> - Discuz! Archiver" />
<meta name="generator" content="Discuz! Archiver <?=$_DCACHE['settings']['version']?>" />
<meta name="author" content="Discuz! Team & Comsenz UI Team" />
<meta name="copyright" content="2001-2007 Comsenz Inc." />
<link rel="stylesheet" type="text/css" href="forumdata/cache/style_<?=$_DCACHE['settings']['styleid']?>.css" />
<style type="text/css">


</style>
</head>
<body class="archiver">
<?if($headerbanner) {?><div class="archiver_banner"><?=$headerbanner?></div><?}?>
<div class="wrap">
