<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_domain.php 9257 2008-10-29 07:11:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//��������
$domainlength = checkperm('domainlength');

if($_SCONFIG['allowdomain'] && $_SCONFIG['domainroot'] && $domainlength) {
	$dcredit = creditrule('pay', 'domain');
} else {
	showmessage('no_privilege');
}

if(submitcheck('domainsubmit')) {

	$setarr = array();
	//��������
	$_POST['domain'] = strtolower(trim($_POST['domain']));
	if($_POST['domain'] != $space['domain']) {
		
		//����
		if($space['domain'] && $dcredit) {
			if($space['credit'] >= $dcredit) {
				$setarr['credit'] = $space['credit'] - $dcredit;
			} else {
				showmessage('integral_inadequate', '', 1, array($space['credit'], $dcredit));
			}
		}
		
		if(empty($domainlength) || empty($_POST['domain'])) {
			$setarr['domain'] = '';
		} else {
			if(strlen($_POST['domain']) < $domainlength) {
				showmessage('domain_length_error', '', 1, array($domainlength));
			}
			if(strlen($_POST['domain']) > 30) {
				showmessage('two_domain_length_not_more_than_30_characters');
			}
			if(!preg_match("/^[a-z][a-z0-9]*$/", $_POST['domain'])) {
				showmessage('only_two_names_from_english_composition_and_figures');
			}

			if(isholddomain($_POST['domain'])) {
				showmessage('domain_be_retained');//debug
			}

			$count = getcount('space', array('domain'=>$_POST['domain']));
			if($count) {
				showmessage('two_domain_have_been_occupied');
			}
			
			$setarr['domain'] = $_POST['domain'];
		}
	}
	if($setarr) updatetable('space', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	
	showmessage('do_success', 'cp.php?ac=domain');
}

$actives = array($ac => ' class="active"');

include_once template("cp_domain");

?>