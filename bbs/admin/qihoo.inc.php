<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: qihoo.inc.php 10468 2007-09-03 02:21:46Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='qihoo'");
$qihoo = ($qihoo = $db->result($query, 0)) ? unserialize($qihoo) : array();

if($action == 'qihoo_config') {

	if(!submitcheck('qihoosubmit')) {

		$checks = array();
		$checkstatus = array($qihoo['status'] => 'checked');
		$checklocation = array($qihoo['location'] => 'checked');
		$checkrelatedsort = array($qihoo['relatedsort'] => 'checked');
		$qihoo['searchbox'] = sprintf('%03b', $qihoo['searchbox']);
		for($i = 1; $i <= 3; $i++) {
			$checks[$i] = $qihoo['searchbox'][3 - $i] ? 'checked' : '';
		}

		shownav('qihoo_settings_basic');
		showtips('qihoo_tips');

?>
<form method="post" name="settings" action="admincp.php?action=qihoo_config">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('qihoo','top');
		showsetting('qihoo_status', '', '', '<input class="radio" type="radio" name="qihoonew[status]" value="1" '.$checkstatus[1].'> '.$lang['qihoo_status_enable'].'<br /><input class="radio" type="radio" name="qihoonew[status]" value="2" '.$checkstatus[2].'> '.$lang['qihoo_status_enable_default'].'<br /><input class="radio" type="radio" name="qihoonew[status]" value="0" '.$checkstatus[0].'> '.$lang['qihoo_status_disable']);
		showsetting('qihoo_searchbox', '', '', '<input class="checkbox" type="checkbox" name="qihoonew[searchbox][1]" value="1" '.$checks[1].'> '.$lang['qihoo_searchbox_index'].'<br /><input class="checkbox" type="checkbox" name="qihoonew[searchbox][2]" value="1" '.$checks[2].'> '.$lang['qihoo_searchbox_forumdisplay'].'<br /><input class="checkbox" type="checkbox" name="qihoonew[searchbox][3]" value="1" '.$checks[3].'> '.$lang['qihoo_searchbox_viewthread']);
		showsetting('qihoo_summary', 'qihoonew[summary]', $qihoo['summary'], 'radio');
		showsetting('qihoo_jammer_allow', 'qihoonew[jammer]', $qihoo['jammer'], 'radio');
		showsetting('qihoo_maxtopics', 'qihoonew[maxtopics]', $qihoo['maxtopics'], 'text');
		showsetting('qihoo_keywords', 'qihoonew[keywords]', $qihoo['keywords'], 'textarea');
		showsetting('qihoo_adminemail', 'qihoonew[adminemail]', $qihoo['adminemail'], 'text');
		showtype('', 'bottom');

		echo '<br /><center><input class="button" type="submit" name="qihoosubmit" value="'.$lang['submit'].'"></form>';

	} else {

		$qihoonew['searchbox'] = bindec(intval($qihoonew['searchbox'][3]).intval($qihoonew['searchbox'][2]).intval($qihoonew['searchbox'][1]));
		$qihoonew['validity'] = $qihoonew['validity'] < 1 ? 1 : intval($qihoonew['validity']);

		if($qihoonew['status'] && $qihoonew['adminemail']) {
			if(!isemail($qihoonew['adminemail'])) {
				cpmsg('qihoo_adminemail_invalid');
			}
			if($qihoonew['adminemail'] != $qihoo['adminemail']) {
				dfopen('http://search.qihoo.com/corp/discuz.html?site='.site().'&key='.md5(site().'qihoo_discuz'.gmdate("Ymd", $timestamp)).'&email='.$qihoonew['adminemail']);
			}
		}

		foreach((array)$qihoonew AS $key => $value) {
			$qihoo[$key] = in_array($key, array('keywords', 'adminemail')) ? $value : intval($value);
		}

		$db->query("UPDATE {$tablepre}settings SET value='".addslashes(serialize($qihoo))."' WHERE variable='qihoo'");
		updatecache('settings');
		cpmsg('qihoo_succeed', 'admincp.php?action=qihoo_config');

	}

} elseif($action == 'qihoo_relatedthreads') {

	if(!submitcheck('qihoosubmit')) {

		$checktype = array();
		$settings = is_array($qihoo['relatedthreads']) ? $qihoo['relatedthreads'] : array();

		$checkposition = array($settings['position'] => 'checked');
		$checkrelatedsort = array($settings['order'] => 'checked');
		foreach((array)$settings['type'] AS $type) {
			$checktype[$type] = 'checked';
		}

		shownav('qihoo_settings_basic');
		showtips('qihoo_tips');

?>
<form method="post" name="settings" action="admincp.php?action=qihoo_relatedthreads">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		showtype('qihoo_relatedthreads','top');
		showsetting('qihoo_relatedthreads', 'settingsnew[bbsnum]', $settings['bbsnum'], 'text');
		showsetting('qihoo_relatedthreads_web', 'settingsnew[webnum]', $settings['webnum'], 'radio');
		showsetting('qihoo_relatedthreads_type', '', '', '<input class="checkbox" type="checkbox" name="settingsnew[type][blog]" value="blog" '.$checktype['blog'].'> '.$lang['qihoo_relatedthreads_type_blog'].'<br /><input class="checkbox" type="checkbox" name="settingsnew[type][news]" value="news" '.$checktype['news'].'> '.$lang['qihoo_relatedthreads_type_news'].'<br /><input class="checkbox" type="checkbox" name="settingsnew[type][bbs]" value="bbs" '.$checktype['bbs'].'> '.$lang['qihoo_relatedthreads_type_bbs']);
		showsetting('qihoo_relatedthreads_banurl', 'settingsnew[banurl]', $settings['banurl'], 'textarea');
		showsetting('qihoo_relatedthreads_position', '', '', '<input class="radio" type="radio" name="settingsnew[position]" value="0" '.$checkposition[0].'> '.$lang['qihoo_relatedthreads_position_mode_top'].'<br /><input class="radio" type="radio" name="settingsnew[position]" value="1" '.$checkposition[1].'> '.$lang['qihoo_relatedthreads_position_mode_under']);
		//showsetting('qihoo_relatedthreads_validity', 'settingsnew[validity]', $settings['validity'], 'text');

		showtype('', 'bottom');

		echo '<br /><center><input class="button" type="submit" name="qihoosubmit" value="'.$lang['submit'].'"></form>';

	} else {

		$qihoo['relatedthreads'] = array();
		foreach((array)$settingsnew AS $key => $value) {
			$qihoo['relatedthreads'][$key] = in_array($key, array('bbsnum', 'webnum', 'position', 'order', 'validity')) ? intval($value) : $value;
		}
		$db->query("UPDATE {$tablepre}settings SET value='".addslashes(serialize($qihoo))."' WHERE variable='qihoo'");
		updatecache('settings');
		cpmsg('qihoo_succeed', 'admincp.php?action=qihoo_relatedthreads');
	}

} elseif($action == 'qihoo_topics') {

	if(!submitcheck('topicsubmit')) {
		$topics = '';
		foreach((is_array($qihoo['topics']) ? $qihoo['topics'] : array()) AS $key => $value) {
			$checkstype = array($value['stype'] => 'selected="selected"');
			$checkrelate = array($value['relate'] => 'selected="selected"');
			$topics .= "<tr align=\"center\">\n".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[$key]\" value=\"".$value['topic']."\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"20\" name=\"qihoo_topics[$key][topic]\" id=\"qihoo_topics[$key][topic]\" value=\"$value[topic]\"></td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"30\" name=\"qihoo_topics[$key][keyword]\" id=\"qihoo_topics[$key][keyword]\" value=\"$value[keyword]\"></td>\n".
				"<td class=\"altbg2\"><input type=\"text\" size=\"10\" name=\"qihoo_topics[$key][length]\" id=\"qihoo_topics[$key][length]\" value=\"$value[length]\"></td>\n".
				"<td class=\"altbg1\"><select name=\"qihoo_topics[$key][stype]\" id=\"qihoo_topics[$key][stype]\"><option value=\"0\" $checkstype[0]>$lang[qihoo_topics_type_fulltext]</option><option value=\"title\" $checkstype[title]>$lang[qihoo_topics_type_title]</option></select></td>\n".
				"<td class=\"altbg2\"><select name=\"qihoo_topics[$key][relate]\" id=\"qihoo_topics[$key][relate]\"><option value=\"score\" $checkrelate[score]>$lang[qihoo_topics_orderby_relation]</option><option value=\"pdate\" $checkrelate[pdate]>$lang[qihoo_topics_orderby_dateline]</option><option value=\"rdate\" $checkrelate[rdate]>$lang[qihoo_topics_orderby_lastpost]</option></select></td>\n".
				"<td class=\"altbg1\"><a href=\"###\" onClick=\"window.open('topic.php?topic='+$('qihoo_topics[$key][topic]').value+'&keyword='+$('qihoo_topics[$key][keyword]').value+'&stype='+$('qihoo_topics[$key][stype]').value+'&length='+$('qihoo_topics[$key][length]').value+'&relate='+$('qihoo_topics[$key][relate]').value+'');\">[$lang[preview]]</a></tr>\n";
		}
		shownav('qihoo_settings_special');
		showtips('qihoo_topics_tips');

?>
<form method="post" action="admincp.php?action=qihoo_topics">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><input class="checkbox" type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['qihoo_topics_name']?></td><td><?=$lang['qihoo_topics_keywords']?></td><td><?=$lang['qihoo_topics_length']?></td><td><?=$lang['qihoo_topics_type']?></td><td><?=$lang['qihoo_topics_orderby']?></td><td><?=$lang['preview']?></td></tr>
<?=$topics?>
<tr class="altbg1" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="20" name="newtopic" id="newtopic"></td>
<td><input type="text" size="30" name="newkeyword" id="newkeyword"></td>
<td><input type="text" size="10" name="newlength" id="newlength" value="0"></td>
<td><select name="newstype" id="newstype"><option value="0" selected><?=$lang['qihoo_topics_type_fulltext']?></option><option value="1"><?=$lang['qihoo_topics_type_title']?></option></select></td>
<td><select name="newrelate" id="newrelate"><option value="score"><?=$lang['qihoo_topics_orderby_relation']?></option><option value="pdate"><?=$lang['qihoo_topics_orderby_dateline']?></option><option value="rdate"><?=$lang['qihoo_topics_orderby_lastpost']?></option></select></td>
<td><a href="###" onClick="window.open('topic.php?topic='+$('newtopic').value+'&keyword='+$('newkeyword').value+'&stype='+$('newstype').value+'&length='+$('newlength').value+'&relate='+$('newrelate').value+'');">[<?=$lang['preview']?>]</a></td>
</tr></table><br />
<center><input class="button" type="submit" name="topicsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		$qihoo['topics'] = array();
		foreach((array)$qihoo_topics AS $key => $value) {
			if(isset($delete[$key])) {
				unset($qihoo['topics'][$key]);
			} else {
				$qihoo['topics'][$key] = array(
					'topic'		=> dhtmlspecialchars(stripslashes($value['topic'])),
					'keyword'	=> $value['keyword'] = trim($value['keyword']) ? dhtmlspecialchars(stripslashes($value['keyword'])) : $value['topic'],
					'length'	=> intval($value['length']),
					'stype'		=> $value['stype'],
					'relate'	=> $value['relate']
				);
			}
		}

		if($newtopic) {
			$qihoo['topics'][] = array(
				'topic'		=> dhtmlspecialchars(stripslashes($newtopic)),
				'keyword'	=> $newkeyword = trim($newkeyword) ? dhtmlspecialchars(stripslashes($newkeyword)) : $newtopic,
				'length'	=> intval($newlength),
				'stype'		=> $newstype > 1 ? 1 : intval($newstype),
				'relate'	=> $newrelate
			);
		}

		$db->query("UPDATE {$tablepre}settings SET value='".addslashes(serialize($qihoo))."' WHERE variable='qihoo'");
		updatecache('settings');
		cpmsg('qihoo_topics_succeed', 'admincp.php?action=qihoo_topics');

	}

}

?>