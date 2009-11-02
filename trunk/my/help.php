<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: help.php 7278 2008-05-05 06:06:48Z liguode $
*/

include_once('./common.php');

if(empty($_GET['ac'])) $_GET['ac'] = 'register';

$actives = array($_GET['ac'] => ' style="font-weight:bold;"');
include template('help');

?>