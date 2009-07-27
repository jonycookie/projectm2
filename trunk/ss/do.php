<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: do.php 7412 2008-05-20 02:45:44Z zhaofei $
*/

include_once('./common.php');

if(in_array($_GET['action'], array('register', 'seccode', 'lostpasswd'))) {
	include_once(S_ROOT.'./source/do_'.$_GET['action'].'.php');
} else {
	showmessage('no_permission');
}

?>