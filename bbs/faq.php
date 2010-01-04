<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: faq.php 11169 2007-11-08 06:30:00Z tiger $
*/

require_once './include/common.inc.php';

$discuz_action = 51;

if(empty($action)) {

	$faqparent = $faqsub = array();
	$query = $db->query("SELECT id, fpid, title FROM {$tablepre}faqs ORDER BY displayorder");
	while($faq = $db->fetch_array($query)) {
		if(empty($faq['fpid'])) {
			$faqparent[$faq['id']] = $faq;
		} else {
			$faqsub[$faq['fpid']][] = $faq;
		}
	}

} elseif($action == 'message') {

	if($faq = $db->fetch_first("SELECT * FROM {$tablepre}faqs WHERE id='$id'")) {

		$navigation = "&raquo; $faq[title]";
		$otherlist = array();
		$query = $db->query("SELECT id, fpid, title FROM {$tablepre}faqs WHERE fpid='$faq[fpid]' AND id!='$faq[id]' ORDER BY displayorder");
		while($other = $db->fetch_array($query)) {
			$otherlist[] = $other;
		}

	} else {
		showmessage("faq_content_empty", 'faq.php');
	}

} elseif($action == 'search') {

	if(submitcheck('searchsubmit')) {
		$navigation = "&raquo; ".$lang['faq_search_help'];
		$keyword = isset($keyword) ? trim($keyword) : '';
		if($keyword) {
			$sqlsrch = '';
			$searchtype = in_array($searchtype, array('all', 'title', 'message')) ? $searchtype : 'all';
			switch($searchtype) {
				case 'all':
					$sqlsrch = "WHERE title LIKE '%$keyword%' OR message LIKE '%$keyword%'";
					break;
				case 'title':
					$sqlsrch = "WHERE title LIKE '%$keyword%'";
					break;
				case 'message':
					$sqlsrch = "WHERE message LIKE '%$keyword%'";
					break;
			}

			$keyword = stripslashes($keyword);
			$faqlist = array();
			$query = $db->query("SELECT fpid, title, message FROM {$tablepre}faqs $sqlsrch ORDER BY displayorder");
			while($faq = $db->fetch_array($query)) {
				if(!empty($faq['fpid'])) {
					$faq['title'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($keyword, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", stripslashes($faq['title']));
					$faq['message'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($keyword, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", stripslashes($faq['message']));
					$faqlist[] = $faq;
				}
			}
		} else {
			showmessage('faq_keywords_empty', 'faq.php');
		}
	}

} else {
	showmessage('undefined_action', NULL, 'HALTED');
}

include template('faq');

?>