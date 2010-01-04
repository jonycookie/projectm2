<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewpro.php 10320 2007-08-26 01:19:05Z cnteacher $
*/

$uid = intval($_GET['uid']);
$username = urlencode(trim($_GET['username']));
if($uid) {
	header("Location: space.php?action=viewpro&uid=$uid");
} else {
	header("Location: space.php?action=viewpro&username=$username");
}

?>