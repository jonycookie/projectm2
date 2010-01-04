<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: faq.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder');

if($action == 'faqlist') {

	if(!submitcheck('faqsubmit')) {

		$faqparent = $faqsub = array();
		$faqlists = $faqselect = '';
		$query = $db->query("SELECT * FROM {$tablepre}faqs ORDER BY displayorder");
		while($faq = $db->fetch_array($query)) {
			if(empty($faq['fpid'])) {
				$faqparent[$faq['id']] = $faq;
				$faqselect .= "<option value=\"$faq[id]\">$faq[title]</option>";
			} else {
				$faqsub[$faq['fpid']][] = $faq;
			}
		}

		foreach($faqparent as $parent) {
			$disabled = !empty($faqsub[$parent['id']]) ? 'disabled' : '';
			$faqlists .= "<tr class=\"altbg1\">\n".
					"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$parent[id]\" $disabled></td>".
					"<td class=\"altbg2\"><input type=\"text\" size=\"30\" name=\"title[$parent[id]]\" value=\"".dhtmlspecialchars($parent['title'])."\"></td>".
					"<td class=\"altbg1\"><input type=\"text\" size=\"3\" name=\"displayorder[$parent[id]]\" value=\"$parent[displayorder]\"></td>".
					"<td class=\"altbg2\">$lang[none]</td>".
					"<td class=\"altbg1\">[<a href=\"admincp.php?action=faqdetail&id=$parent[id]\">".$lang['detail']."</a>]</td>".
					"</tr>";
			if(!empty($faqsub[$parent['id']])) {
				foreach($faqsub[$parent['id']] as $sub) {
					$faqlists .= "<tr class=\"altbg1\">\n".
							"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$sub[id]\"></td>".
							"<td class=\"altbg2\" style=\"padding-left:45px\"><input type=\"text\" size=\"30\" name=\"title[$sub[id]]\" value=\"".dhtmlspecialchars($sub['title'])."\"></td>".
							"<td class=\"altbg1\"><input type=\"text\" size=\"3\" name=\"displayorder[$sub[id]]\" value=\"$sub[displayorder]\"></td>".
							"<td class=\"altbg2\">{$faqparent[$sub['fpid']][title]}</td>".
							"<td class=\"altbg1\">[<a href=\"admincp.php?action=faqdetail&id=$sub[id]\">".$lang['detail']."</a>]</td>".
							"</tr>";
				}
			}
		}
		shownav('faq_forum');

?>
<form method="post" action="admincp.php?action=faqlist">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><input class="checkbox" type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['faq_thread']?></td><td><?=$lang['display_order']?></td><td><?=$lang['faq_sortup']?></td><td><?=$lang['detail']?></td></tr>
<?=$faqlists?>
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="30" name="newtitle"></td>
<td><input type="text" size="3"	name="newdisplayorder"></td>
<td><select name="newfpid"><option value="0"><?=$lang['none']?></option><?=$faqselect?></select></td>
<td></td>
</tr></table><br />
<center><input class="button" type="submit" name="faqsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		if($ids = implodeids($delete)) {
			$db->query("DELETE FROM	{$tablepre}faqs WHERE id IN ($ids)");
		}

		if(is_array($title)) {
			foreach($title as $id => $val) {
				$db->query("UPDATE {$tablepre}faqs SET displayorder='$displayorder[$id]', title='$title[$id]' WHERE id='$id'");
			}
		}

		if($newtitle != '') {
			$newdisplayorder = intval($newdisplayorder);
			$db->query("INSERT INTO	{$tablepre}faqs (fpid, displayorder, title)
					VALUES ('$newfpid', '$newdisplayorder', '$newtitle')");
		}

		cpmsg('faq_list_update', 'admincp.php?action=faqlist');

	}

} elseif($action == 'faqdetail') {

	if(!submitcheck('detailsubmit')) {

		$faq = $db->fetch_first("SELECT * FROM {$tablepre}faqs WHERE id='$id'");
		if(!$faq) {
			cpmsg('undefined_action');
		}

		$query = $db->query("SELECT * FROM {$tablepre}faqs WHERE fpid='0' ORDER BY displayorder, fpid ");
		while($parent = $db->fetch_array($query)) {
			$faqselect .= "<option value=\"$parent[id]\" ".($faq['fpid'] == $parent['id'] ? 'selected' : '').">$parent[title]</option>";
		}

		shownav('faq_forum');

		echo "<form method=\"post\" action=\"admincp.php?action=faqdetail&id=$id&formhash=".FORMHASH."\">";

		showtype('faq_edit', 'top');
		showsetting('faq_title', 'titlenew', $faq['title'], 'text');
		if(!empty($faq['fpid'])) {
			showsetting('faq_sortup', '', '', '<select name="fpidnew"><option value=\"\">'.$lang['none'].'</option>'.$faqselect.'</select>');
			showsetting('faq_identifier', 'identifiernew', $faq['identifier'], 'text');
			showsetting('faq_keywords', 'keywordnew', $faq['keyword'], 'text');
			showsetting('faq_content', 'messagenew', $faq['message'], 'textarea');
		}
		showtype('', 'bottom');

		echo "<br /><center><input class=\"button\" type=\"submit\" name=\"detailsubmit\" value=\"".$lang['submit']."\"></center></form>";

	} else {

		if(!$titlenew) {
			cpmsg('faq_no_title');
		}

		if(!empty($identifiernew)) {
			$query = $db->query("SELECT id FROM {$tablepre}faqs WHERE identifier='$identifiernew' AND id!='$id'");
			if($db->num_rows($query)) {
				cpmsg('faq_identifier_invalid');
			}
		}

		if(strlen($keywordnew) > 50) {
			cpmsg('faq_keyword_toolong');
		}

		$fpidnew = $fpidnew ? intval($fpidnew) : 0;
		$titlenew = trim($titlenew);
		$messagenew = trim($messagenew);
		$identifiernew = trim($identifiernew);
		$keywordnew = trim($keywordnew);

		$db->query("UPDATE {$tablepre}faqs SET fpid='$fpidnew', identifier='$identifiernew', keyword='$keywordnew', title='$titlenew', message='$messagenew' WHERE id='$id'");

		updatecache('faqs');
		cpmsg('faq_list_update', 'admincp.php?action=faqlist');

	}

}

?>