<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: help.php 9055 2008-10-21 06:22:45Z liguode $
*/

include_once('./common.php');

if(empty($_GET['ac'])) $_GET['ac'] = 'register';

$actives = array($_GET['ac'] => ' style="font-weight:bold;"');

include template('help');

?>