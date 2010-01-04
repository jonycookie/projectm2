<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: settings.inc.php 13217 2008-04-01 02:18:13Z heyond $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$operation = $operation ? $operation : (!empty($do) ? $do : '');

cpheader();
$query = $db->query("SELECT * FROM {$tablepre}settings");
while($setting = $db->fetch_array($query)) {
	$settings[$setting['variable']] = $setting['value'];
}

if(!$isfounder) {
	unset($settings['ftp']);
}

$extbutton = '';

if(!submitcheck('settingsubmit')) {


	shownav($operation == 'basic' ? 'settings_general' : 'settings_'.$operation);

?>
<form method="post" name="settings" id="settings" action="admincp.php?action=settings&edit=yes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="operation" value="<?=$operation?>">
<?

	if($operation == 'access') {

		$wmsgcheck = array($settings['welcomemsg'] =>'checked');
		$settings['inviteconfig'] = unserialize($settings['inviteconfig']);
		$settings['extcredits'] = unserialize($settings['extcredits']);

		$buycredits = $rewardcredist = '';
		for($i = 0; $i <= 8; $i++) {
			$extcredit = 'extcredits'.$i.($settings['extcredits'][$i]['available'] ? ' ('.$settings['extcredits'][$i]['title'].')' : '');
			$buycredits .= '<option value="'.$i.'" '.($i == intval($settings['inviteconfig']['invitecredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			$rewardcredits .= '<option value="'.$i.'" '.($i == intval($settings['inviteconfig']['inviterewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
		}

		$groupselect = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type='special'");
		while($group = $db->fetch_array($query)) {
			$groupselect .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $settings['inviteconfig']['invitegroupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		showtype('settings_subtitle_register', 'top', 'settingsubmit');
		showsetting('settings_regstatus', array('settingsnew[regstatus]', array(
			array(0, $lang['settings_register_close'], array('showinvite' => 'none')),
			array(1, $lang['settings_register_open'], array('showinvite' => 'none')),
			array(2, $lang['settings_register_invite'], array('showinvite' => '')),
			array(3, $lang['settings_register_open_invite'], array('showinvite' => '')))), $settings['regstatus'], 'mradio');

		echo '</tbody><tbody class="sub" id="showinvite" style="display: '.($settings['regstatus'] > 1 ? '' : 'none').'">';
		showsetting('settings_register_invite_credit', '', '', '<select name="settingsnew[inviteconfig][inviterewardcredit]">'.$rewardcredits.'</select>');
		showsetting('settings_register_invite_addcredit', 'settingsnew[inviteconfig][inviteaddcredit]', $settings['inviteconfig']['inviteaddcredit'], 'text');
		showsetting('settings_register_invite_invitedcredit', 'settingsnew[inviteconfig][invitedaddcredit]', $settings['inviteconfig']['invitedaddcredit'], 'text');
		showsetting('settings_register_invite_addfriend', 'settingsnew[inviteconfig][inviteaddbuddy]', $settings['inviteconfig']['inviteaddbuddy'], 'radio');
		showsetting('settings_register_invite_group', '', '', '<select name="settingsnew[inviteconfig][invitegroupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$groupselect.'</select>');
		echo '</tbody><tbody>';
		showsetting('settings_reg_name', 'settingsnew[regname]', $settings['regname'], 'text');
		showsetting('settings_reglink_name', 'settingsnew[reglinkname]', $settings['reglinkname'], 'text');
		showsetting('settings_register_advanced', 'settingsnew[regadvance]', $settings['regadvance'], 'radio');
		showsetting('settings_censoruser', 'settingsnew[censoruser]', $settings['censoruser'], 'textarea');
		showsetting('settings_regverify', array('settingsnew[regverify]', array(
			array(0, $lang['none']),
			array(1, $lang['settings_regverify_email']),
			array(2, $lang['settings_regverify_manual']))), $settings['regverify'], 'select');
		showsetting('settings_doublee', 'settingsnew[doublee]', $settings['doublee'], 'radio');
		showsetting('settings_email_allowurl', 'settingsnew[accessemail]', $settings['accessemail'], 'textarea');
		showsetting('settings_censoremail', 'settingsnew[censoremail]', $settings['censoremail'], 'textarea');
		showsetting('settings_regctrl', 'settingsnew[regctrl]', $settings['regctrl'], 'text');
		showsetting('settings_regfloodctrl', 'settingsnew[regfloodctrl]', $settings['regfloodctrl'], 'text');
		showsetting('settings_newbiespan', 'settingsnew[newbiespan]', $settings['newbiespan'], 'text');
		showsetting('settings_welcomemsg', array('settingsnew[welcomemsg]', array(
			array(0, $lang['settings_welcomemsg_nosend'], array('welcomemsgext' => 'none')),
			array(1, $lang['settings_welcomemsg_pm'], array('welcomemsgext' => '')),
			array(2, $lang['settings_welcomemsg_email'], array('welcomemsgext' => '')))), $settings['welcomemsg'], 'mradio');
		echo '</tbody><tbody class="sub" id="welcomemsgext" style="display: '.($settings['welcomemsg'] ? '' : 'none').'">';
		showsetting('settings_welcomemsgtitle', 'settingsnew[welcomemsgtitle]', $settings['welcomemsgtitle'], 'text');
		showsetting('settings_welcomemsgtxt', 'settingsnew[welcomemsgtxt]', $settings['welcomemsgtxt'], 'textarea');
		echo '</tbody><tbody>';
		showsetting('settings_bbrules', 'settingsnew[bbrules]', $settings['bbrules'], 'radio', '', '', 1);
		showsetting('settings_bbrulestxt', 'settingsnew[bbrulestxt]', $settings['bbrulestxt'], 'textarea');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_access', 'top', 'settingsubmit');
		showsetting('settings_ipregctrl', 'settingsnew[ipregctrl]', $settings['ipregctrl'], 'textarea');
		showsetting('settings_ipaccess', 'settingsnew[ipaccess]', $settings['ipaccess'], 'textarea');
		showsetting('settings_adminipaccess', 'settingsnew[adminipaccess]', $settings['adminipaccess'], 'textarea');

	} elseif($operation == 'styles') {

		$stylelist = "<select name=\"settingsnew[styleid]\">\n";
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$selected = $style['styleid'] == $settings['styleid'] ? 'selected="selected"' : NULL;
			$stylelist .= "<option value=\"$style[styleid]\" $selected>$style[name]</option>\n";
		}
		$stylelist .= '</select>';

		$showsettings = str_pad(decbin($settings['showsettings']), 3, 0, STR_PAD_LEFT);
		$settings['showsignatures'] = $showsettings{0};
		$settings['showavatars'] = $showsettings{1};
		$settings['showimages'] = $showsettings{2};
		$settings['postnocustom'] = implode("\n", (array)unserialize($settings['postnocustom']));

		showtips('settings_tips');
		showtype('settings_subtitle_style', 'top', 'settingsubmit');
		showsetting('settings_styleid', '', '', $stylelist);
		showsetting('settings_stylejump', 'settingsnew[stylejump]', $settings['stylejump'], 'radio');
		showsetting('settings_frameon', array('settingsnew[frameon]', array(
			array(0, $lang['settings_frameon_0'], array('frameonext' => 'none')),
			array(1, $lang['settings_frameon_1'], array('frameonext' => '')),
			array(2, $lang['settings_frameon_2'], array('frameonext' => '')))), $settings['frameon'], 'mradio');
		echo '</tbody><tbody class="sub" id="frameonext" style="display: '.($settings['frameon'] ? '' : 'none').'">';
		showsetting('settings_framewidth', 'settingsnew[framewidth]', $settings['framewidth'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_index', 'top', 'settingsubmit');
		showsetting('settings_subforumsindex', 'settingsnew[subforumsindex]', $settings['subforumsindex'], 'radio');
		showsetting('settings_forumlinkstatus', 'settingsnew[forumlinkstatus]', $settings['forumlinkstatus'], 'radio');
		showsetting('settings_index_members', 'settingsnew[maxbdays]', $settings['maxbdays'], 'text');
		showsetting('settings_moddisplay', array('settingsnew[moddisplay]', array(
			array('flat', $lang['settings_moddisplay_flat']),
			array('selectbox', $lang['settings_moddisplay_selectbox']))), $settings['moddisplay'], 'mradio');
		showsetting('settings_whosonline', array('settingsnew[whosonlinestatus]', array(
			array(0, $lang['settings_display_none']),
			array(1, $lang['settings_whosonline_index']),
			array(2, $lang['settings_whosonline_forum']),
			array(3, $lang['settings_whosonline_both']))), $settings['whosonlinestatus'], 'select');
		showsetting('settings_whosonline_contract', 'settingsnew[whosonline_contract]', $settings['whosonline_contract'], 'radio');
		showsetting('settings_online_more_members', 'settingsnew[maxonlinelist]', $settings['maxonlinelist'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_forumdisplay', 'top', 'settingsubmit');
		showsetting('settings_tpp', 'settingsnew[topicperpage]', $settings['topicperpage'], 'text');
		showsetting('settings_threadmaxpages', 'settingsnew[threadmaxpages]', $settings['threadmaxpages'], 'text');
		showsetting('settings_hottopic', 'settingsnew[hottopic]', $settings['hottopic'], 'text');
		showsetting('settings_fastpost', 'settingsnew[fastpost]', $settings['fastpost'], 'radio');
		showsetting('settings_globalstick', 'settingsnew[globalstick]', $settings['globalstick'], 'radio');
		showsetting('settings_stick', 'settingsnew[threadsticky]', $settings['threadsticky'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_viewthread', 'top', 'settingsubmit');
		showsetting('settings_ppp', 'settingsnew[postperpage]', $settings['postperpage'], 'text');
		showsetting('settings_starthreshold', 'settingsnew[starthreshold]', $settings['starthreshold'], 'text');
		showsetting('settings_maxsigrows', 'settingsnew[maxsigrows]', $settings['maxsigrows'], 'text');
		showsetting('settings_rate_number', 'settingsnew[ratelogrecord]', $settings['ratelogrecord'], 'text');
		showsetting('settings_show_signature', 'settingsnew[showsignatures]', $settings['showsignatures'], 'radio');
		showsetting('settings_show_face', 'settingsnew[showavatars]', $settings['showavatars'], 'radio');
		showsetting('settings_show_images', 'settingsnew[showimages]', $settings['showimages'], 'radio');
		showsetting('settings_zoomstatus', 'settingsnew[zoomstatus]', $settings['zoomstatus'], 'radio');
		showsetting('settings_vtonlinestatus', array('settingsnew[vtonlinestatus]', array(
			array(0, $lang['settings_display_none']),
			array(1, $lang['settings_online_easy']),
			array(2, $lang['settings_online_exactitude']))), $settings['vtonlinestatus'], 'select');
		showsetting('settings_userstatusby', array('settingsnew[userstatusby]', array(
			array(0, $lang['settings_display_none']),
			array(1, $lang['settings_userstatusby_usergroup']),
			array(2, $lang['settings_userstatusby_rank']))), $settings['userstatusby'], 'select');
		showsetting('settings_postno', 'settingsnew[postno]', $settings['postno'], 'text');
		showsetting('settings_postnocustom', 'settingsnew[postnocustom]', $settings['postnocustom'], 'textarea');
		showsetting('settings_maxsmilies', 'settingsnew[maxsmilies]', $settings['maxsmilies'], 'text');
		echo '</tbody><tbody><tr><td colspan="2" class="altbg1"><a name="customauthorinfo"></a><b>'.$lang['settings_customauthorinfo'].'</b><br />'.$lang['settings_customauthorinfo_comment'].'</td></tr><tr><td colspan="2" style="padding: 0">';

		$authorinfoitems = array(
			'uid' => 'UID',
			'posts' => $lang['authorinfoitems_posts'],
			'digest' => $lang['authorinfoitems_digest'],
			'credits' => $lang['authorinfoitems_credits'],
		);
		if(!empty($extcredits)) {
			foreach($extcredits as $key => $value) {
				if($value['showinthread']) {
					$authorinfoitems['extcredits'.$key] = $value['title'];
				}
			}
		}
		$query = $db->query("SELECT fieldid,title FROM {$tablepre}profilefields WHERE available='1' AND invisible='0' AND showinthread='1' ORDER BY displayorder");
		while($profilefields = $db->fetch_array($query)) {
			$authorinfoitems['field_'.$profilefields['fieldid']] = $profilefields['title'];
		}
		$authorinfoitems = array_merge($authorinfoitems, array(
			'readperm' => $lang['authorinfoitems_readperm'],
			'gender' => $lang['authorinfoitems_gender'],
			'location' => $lang['authorinfoitems_location'],
			'oltime' => $lang['authorinfoitems_oltime'],
			'regtime' => $lang['authorinfoitems_regtime'],
			'lastdate' => $lang['authorinfoitems_lastdate'],
		));

		$authorinfoitemsetting = '<table width="100%" cellspacing="0"><tr class="category"><td width="25%">&nbsp;</td><td width="25%">'.$lang['authorinfoitems_left'].'</td><td width="25%">'.$lang['authorinfoitems_special'].'</td><td width="25%">'.$lang['authorinfoitems_menu'].'</td></tr>';
		$settings['customauthorinfo'] = unserialize($settings['customauthorinfo']);
		$settings['customauthorinfo'] = $settings['customauthorinfo'][0];

		foreach($authorinfoitems as $key => $value) {
			$authorinfoitemsetting .= '<tr><td>'.$value.
				'</td><td><input name="settingsnew[customauthorinfo]['.$key.'][left]" type="checkbox" class="checkbox" value="1" '.($settings['customauthorinfo'][$key]['left'] ? 'checked' : '').'>'.
				'</td><td><input name="settingsnew[customauthorinfo]['.$key.'][special]" type="checkbox" class="checkbox" value="1" '.($settings['customauthorinfo'][$key]['special'] ? 'checked' : '').'>'.
				'</td><td><input name="settingsnew[customauthorinfo]['.$key.'][menu]" type="checkbox" class="checkbox" value="1" '.($settings['customauthorinfo'][$key]['menu'] ? 'checked' : '').'>'.
				'</td></tr>';
		}
		$authorinfoitemsetting .= '</table></td></tr></table><br />';
		echo $authorinfoitemsetting;

		showtype('settings_subtitle_member', 'top', 'settingsubmit');
		showsetting('settings_mpp', 'settingsnew[memberperpage]', $settings['memberperpage'], 'text');
		showsetting('settings_membermaxpages', 'settingsnew[membermaxpages]', $settings['membermaxpages'], 'text');
		echo '</tbody></table><br />';

		$settings['msgforward'] = !empty($settings['msgforward']) ? unserialize($settings['msgforward']) : array();
		$settings['msgforward']['messages'] = !empty($settings['msgforward']['messages']) ? implode("\n", $settings['msgforward']['messages']) : '';

		showtype('settings_subtitle_refresh', 'top', 'settingsubmit');
		showsetting('settings_refresh_refreshtime', 'settingsnew[msgforward][refreshtime]', $settings['msgforward']['refreshtime'], 'text');
		showsetting('settings_refresh_quick', 'settingsnew[msgforward][quick]', $settings['msgforward']['quick'], 'radio', '', '', 1);
		showsetting('settings_refresh_messages', 'settingsnew[msgforward][messages]', $settings['msgforward']['messages'], 'textarea');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_other', 'top', 'settingsubmit');
		showsetting('settings_hideprivate', 'settingsnew[hideprivate]', $settings['hideprivate'], 'radio');
		showsetting('settings_visitedforums', 'settingsnew[visitedforums]', $settings['visitedforums'], 'text');

	} elseif($operation == 'seo') {

		showtips('settings_tips');
		showtype('settings_seo', 'top', 'settingsubmit');
		showsetting('settings_archiverstatus', array('settingsnew[archiverstatus]', array(
			array(0, $lang['settings_archiverstatus_none']),
			array(1, $lang['settings_archiverstatus_full']),
			array(2, $lang['settings_archiverstatus_searchengine']),
			array(3, $lang['settings_archiverstatus_browser']))), $settings['archiverstatus'], 'mradio');
		showsetting('settings_rewritestatus', array('settingsnew[rewritestatus]', array(
			$lang['settings_rewritestatus_forumdisplay'],
			$lang['settings_rewritestatus_viewthread'],
			$lang['settings_rewritestatus_space'],
			$lang['settings_rewritestatus_tag'],
			$lang['settings_rewritestatus_archiver'])), $settings['rewritestatus'], 'mcheckbox');
		showsetting('settings_rewritecompatible', 'settingsnew[rewritecompatible]', $settings['rewritecompatible'], 'radio');
		showsetting('settings_seotitle', 'settingsnew[seotitle]', $settings['seotitle'], 'text');
		showsetting('settings_seokeywords', 'settingsnew[seokeywords]', $settings['seokeywords'], 'text');
		showsetting('settings_seodescription', 'settingsnew[seodescription]', $settings['seodescription'], 'text');
		showsetting('settings_seohead', 'settingsnew[seohead]', $settings['seohead'], 'textarea');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_sitemap', 'top', 'settingsubmit');
		showsetting('settings_sitemap_baidu_open', 'settingsnew[baidusitemap]', $settings['baidusitemap'], 'radio', '', '', 1);
		showsetting('settings_sitemap_baidu_expire', 'settingsnew[baidusitemap_life]', $settings['baidusitemap_life'], 'text');

	} elseif($operation == 'functions') {

		$jsmenu = array();
		$settings['jsmenustatus'] = sprintf('%b', $settings['jsmenustatus']);
		for($i = 1; $i <= strlen($settings['jsmenustatus']); $i++) {
			$jsmenu[$i] = substr($settings['jsmenustatus'], -$i, 1) ? 'checked' : '';
		}

		$editoroptions = str_pad(decbin($settings['editoroptions']), 2, 0, STR_PAD_LEFT);
		$settings['defaulteditormode'] = $editoroptions{0};
		$settings['allowswitcheditor'] = $editoroptions{1};

		showtips('settings_tips');
		showtype('settings_subtitle_menu', 'top', 'settingsubmit');
		showsetting('settings_jsmenu', 'settingsnew[forumjump]', $settings['forumjump'], 'radio');
		showsetting('settings_jsmenu_enable', '', '', '<input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][1]" value="1" '.$jsmenu[1].'> '.$lang['settings_jsmenu_enable_jump'].'<br /><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][2]" value="1" '.$jsmenu[2].'> '.$lang['settings_jsmenu_enable_memcp'].'<br /><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][3]" value="1" '.$jsmenu[3].'> '.$lang['settings_jsmenu_enable_stat'].'<br /><input class="checkbox" type="checkbox" name="settingsnew[jsmenustatus][4]" value="1" '.$jsmenu[4].'> '.$lang['settings_jsmenu_enable_my'].'<br />');
		showsetting('settings_pluginjsmenu', 'settingsnew[pluginjsmenu]', $settings['pluginjsmenu'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_editor', 'top', 'settingsubmit');
		showsetting('settings_editor_mode_default', array('settingsnew[defaulteditormode]', array(
			array(0, $lang['settings_editor_mode_discuzcode']),
			array(1, $lang['settings_editor_mode_wysiwyg']))), $settings['defaulteditormode'], 'mradio');
		showsetting('settings_editor_swtich_enable', 'settingsnew[allowswitcheditor]', $settings['allowswitcheditor'], 'radio');
		showsetting('settings_bbinsert', 'settingsnew[bbinsert]', $settings['bbinsert'], 'radio');
		showsetting('settings_smileyinsert', 'settingsnew[smileyinsert]', $settings['smileyinsert'], 'radio', '', '', 1);
		showsetting('settings_smthumb', 'settingsnew[smthumb]', $settings['smthumb'], 'text');
		showsetting('settings_smcols', 'settingsnew[smcols]', $settings['smcols'], 'text');
		showsetting('settings_smrows', 'settingsnew[smrows]', $settings['smrows'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_stat', 'top', 'settingsubmit');
		showsetting('settings_statstatus', 'settingsnew[statstatus]', $settings['statstatus'], 'radio');
		showsetting('settings_statscachelife', 'settingsnew[statscachelife]', $settings['statscachelife'], 'text');
		showsetting('settings_pvfrequence', 'settingsnew[pvfrequence]', $settings['pvfrequence'], 'text');
		showsetting('settings_oltimespan', 'settingsnew[oltimespan]', $settings['oltimespan'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_mod', 'top', 'settingsubmit');
		showsetting('settings_modworkstatus', 'settingsnew[modworkstatus]', $settings['modworkstatus'], 'radio');
		showsetting('settings_maxmodworksmonths', 'settingsnew[maxmodworksmonths]', $settings['maxmodworksmonths'], 'text');
		showsetting('settings_myfunction_savetime', 'settingsnew[myrecorddays]', $settings['myrecorddays'], 'text');
		showsetting('settings_losslessdel', 'settingsnew[losslessdel]', $settings['losslessdel'], 'text');
		showsetting('settings_modreasons', 'settingsnew[modreasons]', $settings['modreasons'], 'textarea');
		showsetting('settings_bannedmessages', 'settingsnew[bannedmessages]', $settings['bannedmessages'], 'radio');
		echo '</tbody></table><br /><a name="subtitle_tags"></a>';

		showtype('settings_subtitle_tags', 'top', '');
		showsetting('settings_tagstatus', 'settingsnew[tagstatus]', $settings['tagstatus'], 'radio', '', '', 1);
		showsetting('settings_index_hottags', 'settingsnew[hottags]', $settings['hottags'], 'text');
		showsetting('settings_viewthtrad_hottags', 'settingsnew[viewthreadtags]', $settings['viewthreadtags'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_other', 'top', '');
		showsetting('settings_rssstatus', 'settingsnew[rssstatus]', $settings['rssstatus'], 'radio');
		showsetting('settings_rssttl', 'settingsnew[rssttl]', $settings['rssttl'], 'text');
		showsetting('settings_csscache', 'settingsnew[allowcsscache]', $settings['allowcsscache'], 'radio');
		showsetting('settings_send_birthday', 'settingsnew[bdaystatus]', $settings['bdaystatus'], 'radio');
		showsetting('settings_debug', 'settingsnew[debug]', $settings['debug'], 'radio');
		showsetting('settings_activity_type', 'settingsnew[activitytype]', $settings['activitytype'], 'textarea');


	} elseif($operation == 'credits') {

		showtips('settings_credits_tips');

		if(!empty($projectid)) {
			$settings = @array_merge($settings, unserialize($db->result_first("SELECT value FROM {$tablepre}projects WHERE id='$projectid'")));
		}

		$projectselect = "<select name=\"projectid\" onchange=\"window.location='admincp.php?action=settings&do=credits&projectid='+this.options[this.options.selectedIndex].value\"><option value=\"0\" selected=\"selected\">".$lang['none']."</option>";
		$query = $db->query("SELECT id, name FROM {$tablepre}projects WHERE type='extcredit'");
		while($project = $db->fetch_array($query)) {
			$projectselect .= "<option value=\"$project[id]\" ".($project['id'] == $projectid ? 'selected="selected"' : NULL).">$project[name]</option>\n";
		}
		$projectselect .= '</select>';

		showtype('settings_credits_scheme_title', 'top');
		showsetting('settings_credits_scheme', '', '', $projectselect);
		echo '</tbody></table><br />';
		echo '<script>
			function switchpolicy(obj, col) {
				var status = !obj.checked;
				$("policy" + col).disabled = status;
				var policytable = $("policytable");
				for(var row=2; row<14; row++) {
					if(is_opera) {
						policytable.rows[row].cells[col].firstChild.disabled = true;
					} else {
						policytable.rows[row].cells[col].disabled = status;
					}
				}
			}
		</script>';
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
			'<tr class="header"><td colspan="9">'.$lang['settings_credits_extended'].'</td></tr>'.
			'<tr align="center" class="category"><td>'.$lang['credits_id'].'</td><td>'.$lang['credits_title'].'</td><td>'.$lang['credits_unit'].'</td><td>'.$lang['settings_credits_ratio'].'</td><td>'.$lang['settings_credits_init'].'</td><td>'.$lang['settings_credits_available'].'</td><td>'.$lang['settings_credits_show_in_thread'].'</td><td>'.$lang['credits_inport'].'</td><td>'.$lang['credits_import'].'</td></tr>';
		$settings['extcredits'] = unserialize($settings['extcredits']);
		$settings['initcredits'] = explode(',', $settings['initcredits']);
		for($i = 1; $i <= 8; $i++) {
			echo "<tr align=\"center\"><td class=\"altbg1\">extcredits$i</td>".
				"<td class=\"altbg2\"><input type=\"text\" size=\"8\" name=\"settingsnew[extcredits][$i][title]\" value=\"{$settings['extcredits'][$i]['title']}\"></td>".
				"<td class=\"altbg1\"><input type=\"text\" size=\"5\" name=\"settingsnew[extcredits][$i][unit]\" value=\"{$settings['extcredits'][$i]['unit']}\"></td>".
				"<td class=\"altbg2\"><input type=\"text\" size=\"3\" name=\"settingsnew[extcredits][$i][ratio]\" value=\"".(float)$settings['extcredits'][$i]['ratio']."\" onkeyup=\"if(this.value != '0' && \$('allowexchangeout$i').checked == false && \$('allowexchangein$i').checked == false) {\$('allowexchangeout$i').checked = true;\$('allowexchangein$i').checked = true;} else if(this.value == '0') {\$('allowexchangeout$i').checked = false;\$('allowexchangein$i').checked = false;}\"></td>".
				"<td class=\"altbg1\"><input type=\"text\" size=\"3\" name=\"settingsnew[initcredits][$i]\" value=\"".intval($settings['initcredits'][$i])."\"></td>".
				"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"settingsnew[extcredits][$i][available]\" value=\"1\" ".($settings['extcredits'][$i]['available'] ? 'checked' : '')." onclick=\"switchpolicy(this, $i)\"></td>".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"settingsnew[extcredits][$i][showinthread]\" value=\"1\" ".($settings['extcredits'][$i]['showinthread'] ? 'checked' : '')."></td>".
				"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" size=\"3\" name=\"settingsnew[extcredits][$i][allowexchangeout]\" value=\"1\" ".($settings['extcredits'][$i]['allowexchangeout'] ? 'checked' : '')." id=\"allowexchangeout$i\"></td>".
				"<td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" size=\"3\" name=\"settingsnew[extcredits][$i][allowexchangein]\" value=\"1\" ".($settings['extcredits'][$i]['allowexchangein'] ? 'checked' : '')." id=\"allowexchangein$i\"></td></tr>";
		}
		echo '<tr><td class="altbg1" colspan="9">'.$lang['settings_credits_extended_comment'].'</td></tr>'.
			'</table><br />';

		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder" id="policytable">'.
			'<tr class="header"><td colspan="11">'.$lang['settings_credits_policy'].'</td></tr>'.'<tr align="center" class="category"><td width="12%">'.$lang['credits_id'].'</td>';
		$settings['creditspolicy'] = unserialize($settings['creditspolicy']);
		for($i = 1; $i <= 8; $i++) {
			echo "<td id=\"policy$i\" ".($settings['extcredits'][$i]['available'] ? '' : 'disabled')."  class=\"category\" align=\"center\"> extcredits$i<br />".($settings['extcredits'][$i]['title'] ? '('.$settings['extcredits'][$i]['title'].')' : '')."</td>";
		}
		echo '<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_post_comment'].'"><td>'.$lang['settings_credits_policy_post'].'</td>'.creditsrow('post').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_reply_comment'].'"><td>'.$lang['settings_credits_policy_reply'].'</td>'.creditsrow('reply').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_digest_comment'].'"><td>'.$lang['settings_credits_policy_digest'].'</td>'.creditsrow('digest').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_post_attach_comment'].'"><td>'.$lang['settings_credits_policy_post_attach'].'</td>'.creditsrow('postattach').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_get_attach_comment'].'"><td>'.$lang['settings_credits_policy_get_attach'].'</td>'.creditsrow('getattach').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_send_pm_comment'].'"><td>'.$lang['settings_credits_policy_send_pm'].'</td>'.creditsrow('pm').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_search_comment'].'"><td>'.$lang['settings_credits_policy_search'].'</td>'.creditsrow('search').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_promotion_visit_comment'].'"><td>'.$lang['settings_credits_policy_promotion_visit'].'</td>'.creditsrow('promotion_visit').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_promotion_register_comment'].'"><td>'.$lang['settings_credits_policy_promotion_register'].'</td>'.creditsrow('promotion_register').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_trade_comment'].'"><td>'.$lang['settings_credits_policy_trade'].'(+)</td>'.creditsrow('tradefinished').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_policy_poll_comment'].'"><td>'.$lang['settings_credits_policy_poll'].'(+)</td>'.creditsrow('votepoll').'</tr>'.
			'<tr align="center" class="altbg1" title="'.$lang['settings_credits_lowerlimit_comment'].'"><td>'.$lang['settings_credits_lowerlimit'].'</td>'.creditsrow('lowerlimit').'</tr>'.
			'<tr><td class="altbg1" colspan="12">'.$lang['settings_credits_policy_comment'].'</td></tr></table><br />';

		showtype('settings_credits', 'top', 'settingsubmit');
		showsetting('settings_creditsformula', 'settingsnew[creditsformula]', $settings['creditsformula'], 'textarea');

		$creditstrans = '';
		for($i = 0; $i <= 8; $i++) {
			$creditstrans .= '<option value="'.$i.'" '.($i == intval($settings['creditstrans']) ? 'selected' : '').'>'.($i ? 'extcredits'.$i.($settings['extcredits'][$i]['title'] ? '('.$settings['extcredits'][$i]['title'].')' : '') : $lang['none']).'</option>';
		}
		showsetting('settings_creditstrans', '', '', '<select name="settingsnew[creditstrans]">'.$creditstrans.'</select>');
		showsetting('settings_creditstax', 'settingsnew[creditstax]', $settings['creditstax'], 'text');
		showsetting('settings_transfermincredits', 'settingsnew[transfermincredits]', $settings['transfermincredits'], 'text');
		showsetting('settings_exchangemincredits', 'settingsnew[exchangemincredits]', $settings['exchangemincredits'], 'text');
		showsetting('settings_maxincperthread', 'settingsnew[maxincperthread]', $settings['maxincperthread'], 'text');
		showsetting('settings_maxchargespan', 'settingsnew[maxchargespan]', $settings['maxchargespan'], 'text');

		$extbutton = '&nbsp;&nbsp;&nbsp;<input name="projectsave" type="hidden" value="0"><input class="button" type="button" onclick="$(\'settings\').projectsave.value=1;$(\'settings\').settingsubmit.click()" value="'.$lang['saveconf'].'">';

	} elseif($operation == 'serveropti') {

		$checkgzipfunc = !function_exists('ob_gzhandler') ? 1 : 0;

		showtips('settings_tips');
		showtype('settings_serveropti', 'top', 'settingsubmit');
		showsetting('settings_gzipcompress', 'settingsnew[gzipcompress]', $settings['gzipcompress'], 'radio', '', $checkgzipfunc);
		showsetting('settings_delayviewcount', array('settingsnew[delayviewcount]', array(
			array(0, $lang['none']),
			array(1, $lang['settings_delayviewcount_thread']),
			array(2, $lang['settings_delayviewcount_attach']),
			array(3, $lang['settings_delayviewcount_thread_attach']))), $settings['delayviewcount'], 'select');
		showsetting('settings_nocacheheaders', 'settingsnew[nocacheheaders]', $settings['nocacheheaders'], 'radio');
		showsetting('settings_transsidstatus', 'settingsnew[transsidstatus]', $settings['transsidstatus'], 'radio');
		showsetting('settings_maxonlines', 'settingsnew[maxonlines]', $settings['maxonlines'], 'text');
		showsetting('settings_onlinehold', 'settingsnew[onlinehold]', $settings['onlinehold'], 'text');
		showsetting('settings_loadctrl', 'settingsnew[loadctrl]', $settings['loadctrl'], 'text');
		showsetting('settings_floodctrl', 'settingsnew[floodctrl]', $settings['floodctrl'], 'text');
		showsetting('settings_admode', array('settingsnew[admode]', array(
			array(0, $lang['settings_admode_0']),
			array(1, $lang['settings_admode_1']))), $settings['admode'], 'select');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_search', 'top', 'settingsubmit');
		showsetting('settings_searchctrl', 'settingsnew[searchctrl]', $settings['searchctrl'], 'text');
		showsetting('settings_maxspm', 'settingsnew[maxspm]', $settings['maxspm'], 'text');
		showsetting('settings_maxsearchresults', 'settingsnew[maxsearchresults]', $settings['maxsearchresults'], 'text');

	} elseif($operation == 'seccode') {

		$checksc = array();
		$settings['seccodedata'] = unserialize($settings['seccodedata']);

		$seccodetypearray = array(
			array(0, $lang['settings_seccodetype_image'], array('seccodeimageext' => '')),
			array(1, $lang['settings_seccodetype_chnfont'], array('seccodeimageext' => ''))
		);
		extension_loaded('ming') && $seccodetypearray[] = array(2, $lang['settings_seccodetype_flash'], array('seccodeimageext' => 'none'));

		showtips('settings_seccode_tips');
		showtype('settings_seccode', 'top', 'settingsubmit');
		showsetting('settings_seccodestatus', array('settingsnew[seccodestatus]', array(
			$lang['settings_seccodestatus_register'],
			$lang['settings_seccodestatus_login'],
			$lang['settings_seccodestatus_post'],
			$lang['settings_seccodestatus_sendpm'],
			$lang['settings_seccodestatus_profile'])), $settings['seccodestatus'], 'mcheckbox');
		showsetting('settings_seccodeminposts', 'settingsnew[seccodedata][minposts]', $settings['seccodedata']['minposts'], 'text');
		showsetting('settings_seccodeloginfailedcount', 'settingsnew[seccodedata][loginfailedcount]', $settings['seccodedata']['loginfailedcount'], 'radio');
		showsetting('settings_seccodewidth', 'settingsnew[seccodedata][width]', $settings['seccodedata']['width'], 'text');
		showsetting('settings_seccodeheight', 'settingsnew[seccodedata][height]', $settings['seccodedata']['height'], 'text');
		showsetting('settings_seccodetype', array('settingsnew[seccodedata][type]', $seccodetypearray), $settings['seccodedata']['type'], 'mradio');
		echo '</tbody><tbody class="sub" id="seccodeimageext" style="display: '.($settings['seccodedata']['type'] != 2 ? '' : 'none').'">';
		showsetting('settings_seccodebackground', 'settingsnew[seccodedata][background]', $settings['seccodedata']['background'], 'radio');
		showsetting('settings_seccodeadulterate', 'settingsnew[seccodedata][adulterate]', $settings['seccodedata']['adulterate'], 'radio');
		showsetting('settings_seccodettf', 'settingsnew[seccodedata][ttf]', $settings['seccodedata']['ttf'], 'radio', '', !function_exists('imagettftext'));
		showsetting('settings_seccodeangle', 'settingsnew[seccodedata][angle]', $settings['seccodedata']['angle'], 'radio');
		showsetting('settings_seccodecolor', 'settingsnew[seccodedata][color]', $settings['seccodedata']['color'], 'radio');
		showsetting('settings_seccodesize', 'settingsnew[seccodedata][size]', $settings['seccodedata']['size'], 'radio');
		showsetting('settings_seccodeshadow', 'settingsnew[seccodedata][shadow]', $settings['seccodedata']['shadow'], 'radio');
		showsetting('settings_seccodeanimator', 'settingsnew[seccodedata][animator]', $settings['seccodedata']['animator'], 'radio', '', !function_exists('imagegif'));

		echo '<script language="JavaScript">var seccodedata = ['.$settings['seccodedata']['width'].', '.$settings['seccodedata']['height'].', '.$settings['seccodedata']['type'].'];updateseccode()</script>';

	} elseif($operation == 'secqaa') {

		$settings['secqaa'] = unserialize($settings['secqaa']);
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * 10;
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}itempool");
		$secqaanums = $db->result($query, 0);
		$multipage = multi($secqaanums, 10, $page, 'admincp.php?action=settings&do=secqaa');

		$query = $db->query("SELECT * FROM {$tablepre}itempool LIMIT $start_limit, 10");
		$secqaa = '';
		while($item = $db->fetch_array($query)) {
			$secqaa .= '<tr align="center"><td class="altbg1" ><input class="checkbox" type="checkbox" name="delete[]" value="'.$item['id'].'"></td><td class="altbg1"><textarea name="question['.$item['id'].']" rows="3" cols="60">'.dhtmlspecialchars($item['question']).'</textarea></td><td class="altbg2"><input type="text" name="answer['.$item['id'].']" size="30" maxlength="50" value="'.$item['answer'].'"></td></tr>';
		}

		showtips('settings_secqaa_tips');
		showtype('settings_secqaa', 'top', 'settingsubmit');
		showsetting('settings_secqaa_status', array('settingsnew[secqaa][status]', array(
			$lang['settings_seccodestatus_register'],
			$lang['settings_seccodestatus_post'],
			$lang['settings_seccodestatus_sendpm'])), $settings['secqaa']['status'], 'mcheckbox');
		showsetting('settings_secqaa_minposts', 'settingsnew[secqaa][minposts]', $settings['secqaa']['minposts'], 'text');

		echo '</table><br /><table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
			'<tr class="header"><td colspan="3">'.$lang['settings_secqaa_qaa'].'</td></tr>';
		echo '<tr class="category"><td><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form, \'delete\')">'.$lang['del'].'</td><td>'.$lang['settings_secqaa_question'].'</td><td>'.$lang['settings_secqaa_answer'].'</td></tr>'.
			$secqaa.($multipage ? '<tr><td colspan=5>'.$multipage.'</td></tr>' : '').'<tbody id="secqaabody"><tr align="center"><td class="altbg1">'.$lang['add_new'].'<a href="###" onclick="newnode = $(\'secqaabodyhidden\').firstChild.cloneNode(true); $(\'secqaabody\').appendChild(newnode)">[+]</a></td><td class="altbg1"><textarea name="newquestion[]" rows="3" cols="60"></textarea></td><td class="altbg2"><input type="text" name="newanswer[]" size="30" maxlength="50"></td></tr></tbody><tbody id="secqaabodyhidden" style="display:none"><tr align="center"><td class="altbg1"></td><td class="altbg1"><textarea name="newquestion[]" rows="3" cols="60"></textarea></td><td class="altbg2"><input type="text" name="newanswer[]" size="30" maxlength="50"></td></tr></tbody>';
		echo '<tr><td colspan=3>'.$lang['settings_secqaa_comment'].'</td></tr></table>';

	} elseif($operation == 'datetime') {

		$checktimeformat = array($settings['timeformat'] == 'H:i' ? 24 : 12 => 'checked');

		$settings['userdateformat'] = dateformat($settings['userdateformat']);
		$settings['dateformat'] = dateformat($settings['dateformat']);

		showtype('settings_subtitle_datetime', 'top', 'settingsubmit');
		showsetting('settings_dateformat', 'settingsnew[dateformat]', $settings['dateformat'], 'text');
		showsetting('settings_timeformat', '', '', '<input class="radio" type="radio" name="settingsnew[timeformat]" value="24" '.$checktimeformat[24].'> 24 '.$lang['hour'].' <input class="radio" type="radio" name="settingsnew[timeformat]" value="12" '.$checktimeformat[12].'> 12 '.$lang['hour'].'');
		showsetting('settings_timeoffset', 'settingsnew[timeoffset]', $settings['timeoffset'], 'text');
		showsetting('settings_customformat', 'settingsnew[userdateformat]', $settings['userdateformat'], 'textarea');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_periods', 'top', 'settingsubmit');
		showsetting('settings_visitbanperiods', 'settingsnew[visitbanperiods]', $settings['visitbanperiods'], 'textarea');
		showsetting('settings_postbanperiods', 'settingsnew[postbanperiods]', $settings['postbanperiods'], 'textarea');
		showsetting('settings_postmodperiods', 'settingsnew[postmodperiods]', $settings['postmodperiods'], 'textarea');
		showsetting('settings_ban_downtime', 'settingsnew[attachbanperiods]', $settings['attachbanperiods'], 'textarea');
		showsetting('settings_searchbanperiods', 'settingsnew[searchbanperiods]', $settings['searchbanperiods'], 'textarea');

	} elseif($operation == 'permissions') {

		showtype('settings_permissions', 'top', 'settingsubmit');
		showsetting('settings_memliststatus', 'settingsnew[memliststatus]', $settings['memliststatus'], 'radio');
		showsetting('settings_reportpost', array('settingsnew[reportpost]', array(
			array(0, $lang['settings_reportpost_none']),
			array(1, $lang['settings_reportpost_level_1']),
			array(2, $lang['settings_reportpost_level_2']),
			array(3, $lang['settings_reportpost_level_3']))), $settings['reportpost'], 'select');
		showsetting('settings_minpostsize', 'settingsnew[minpostsize]', $settings['minpostsize'], 'text');
		showsetting('settings_maxpostsize', 'settingsnew[maxpostsize]', $settings['maxpostsize'], 'text');
		showsetting('settings_favorite_storage', 'settingsnew[maxfavorites]', $settings['maxfavorites'], 'text');
		showsetting('settings_subscriptions', 'settingsnew[maxsubscriptions]', $settings['maxsubscriptions'], 'text');
		showsetting('settings_maxavatarsize', 'settingsnew[maxavatarsize]', $settings['maxavatarsize'], 'text');
		showsetting('settings_maxavatarpixel', 'settingsnew[maxavatarpixel]', $settings['maxavatarpixel'], 'text');
		showsetting('settings_maxpolloptions', 'settingsnew[maxpolloptions]', $settings['maxpolloptions'], 'text');
		showsetting('settings_edittimelimit', 'settingsnew[edittimelimit]', $settings['edittimelimit'], 'text');
		showsetting('settings_editby', 'settingsnew[editedby]', $settings['editedby'], 'radio');
		echo '</tbody></table><br />';

		showtype('settings_subtitle_rate', 'top', 'settingsubmit');
		showsetting('settings_karmaratelimit', 'settingsnew[karmaratelimit]', $settings['karmaratelimit'], 'text');
		showsetting('settings_modratelimit', 'settingsnew[modratelimit]', $settings['modratelimit'], 'radio');
		showsetting('settings_dupkarmarate', 'settingsnew[dupkarmarate]', $settings['dupkarmarate'], 'radio');

	} elseif($operation == 'attachments') {

		$checkwm = array($settings['watermarkstatus'] => 'checked');
		$checkmkdirfunc = !function_exists('mkdir') ? 'disabled' : '';
		$settings['watermarktext'] = unserialize($settings['watermarktext']);
		$settings['watermarktext']['fontpath'] = str_replace(array('ch/', 'en/'), '', $settings['watermarktext']['fontpath']);

		showtype('settings_attachments', 'top', 'settingsubmit');
		showsetting('settings_attachdir', 'settingsnew[attachdir]', $settings['attachdir'], 'text');
		showsetting('settings_attachurl', 'settingsnew[attachurl]', $settings['attachurl'], 'text');
		showsetting('settings_attachimgpost', 'settingsnew[attachimgpost]', $settings['attachimgpost'], 'radio');
		showsetting('settings_attachrefcheck', 'settingsnew[attachrefcheck]', $settings['attachrefcheck'], 'radio');
		showsetting('settings_attachsave', array('settingsnew[attachsave]', array(
			array(0, $lang['settings_attachsave_default']),
			array(1, $lang['settings_attachsave_forum']),
			array(2, $lang['settings_attachsave_type']),
			array(3, $lang['settings_attachsave_month']),
			array(4, $lang['settings_attachsave_day']))), $settings['attachsave'], 'select', '', $checkmkdirfunc);
		echo '</tbody></table><br />';

		showtype('settings_pictureattachments', 'top', 'settingsubmit');
		showsetting('settings_imagelib', array('settingsnew[imagelib]', array(
			array(0, $lang['settings_watermarktype_GD'], array('imagelibext' => 'none')),
			array(1, $lang['settings_watermarktype_IM'], array('imagelibext' => '')))), $settings['imagelib'], 'mradio');
		echo '</tbody><tbody class="sub" id="imagelibext" style="display: '.($settings['imagelib'] ? '' : 'none').'">';
		showsetting('settings_imageimpath', 'settingsnew[imageimpath]', $settings['imageimpath'], 'text');
		echo '</tbody><tbody>';
		showsetting('settings_thumbstatus', array('settingsnew[thumbstatus]', array(
			array(0, $lang['settings_thumbstatus_none']),
			array(1, $lang['settings_thumbstatus_add']),
			array(2, $lang['settings_thumbstatus_replace']))), $settings['thumbstatus'], 'mradio');
		showsetting('settings_thumbwidthheight', '', '', '<input name="settingsnew[thumbwidth]" size="20" value="'.intval($settings['thumbwidth']).'"> <span style="vertical-align: middle">X</span> <input name="settingsnew[thumbheight]" size="20" value="'.intval($settings['thumbheight']).'">');
		showsetting('settings_watermarkstatus', '', '', '<table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" class="tableborder" style="margin-bottom: 3px; margin-top:3px;"><tr><td colspan="3"><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="0" '.$checkwm[0].'>'.$lang['settings_watermarkstatus_none'].'</td></tr><tr align="center" class="altbg2"><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="1" '.$checkwm[1].'> #1</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="2" '.$checkwm[2].'> #2</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="3" '.$checkwm[3].'> #3</td></tr><tr align="center" class="altbg2"><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="4" '.$checkwm[4].'> #4</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="5" '.$checkwm[5].'> #5</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="6" '.$checkwm[6].'> #6</td></tr><tr align="center" class="altbg2"><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="7" '.$checkwm[7].'> #7</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="8" '.$checkwm[8].'> #8</td><td><input class="radio" type="radio" name="settingsnew[watermarkstatus]" value="9" '.$checkwm[9].'> #9</td></tr></table>');
		showsetting('settings_watermarkminwidthheight', '', '', '<input name="settingsnew[watermarkminwidth]" size="20" value="'.intval($settings['watermarkminwidth']).'"> <span style="vertical-align: middle">X</span> <input name="settingsnew[watermarkminheight]" size="20" value="'.intval($settings['watermarkminheight']).'">');
		showsetting('settings_watermarktype', array('settingsnew[watermarktype]', array(
			array(0, $lang['settings_watermarktype_gif'], array('watermarktypeext' => 'none')),
			array(1, $lang['settings_watermarktype_png'], array('watermarktypeext' => 'none')),
			array(2, $lang['settings_watermarktype_text'], array('watermarktypeext' => '')))), $settings['watermarktype'], 'mradio');
		showsetting('settings_watermarktrans', 'settingsnew[watermarktrans]', $settings['watermarktrans'], 'text');
		showsetting('settings_watermarkquality', 'settingsnew[watermarkquality]', $settings['watermarkquality'], 'text');
		echo '</tbody><tbody class="sub" id="watermarktypeext" style="display: '.($settings['watermarktype'] == 2 ? '' : 'none').'">';
		showsetting('settings_watermarktext_text', 'settingsnew[watermarktext][text]', $settings['watermarktext']['text'], 'textarea');
		showsetting('settings_watermarktext_fontpath', 'settingsnew[watermarktext][fontpath]', $settings['watermarktext']['fontpath'], 'text');
		showsetting('settings_watermarktext_size', 'settingsnew[watermarktext][size]', $settings['watermarktext']['size'], 'text');
		showsetting('settings_watermarktext_angle', 'settingsnew[watermarktext][angle]', $settings['watermarktext']['angle'], 'text');
		showsetting('settings_watermarktext_color', 'settingsnew[watermarktext][color]', $settings['watermarktext']['color'], 'color');
		showsetting('settings_watermarktext_shadowx', 'settingsnew[watermarktext][shadowx]', $settings['watermarktext']['shadowx'], 'text');
		showsetting('settings_watermarktext_shadowy', 'settingsnew[watermarktext][shadowy]', $settings['watermarktext']['shadowy'], 'text');
		showsetting('settings_watermarktext_shadowcolor', 'settingsnew[watermarktext][shadowcolor]', $settings['watermarktext']['shadowcolor'], 'color');
		showsetting('settings_watermarktext_imtranslatex', 'settingsnew[watermarktext][translatex]', $settings['watermarktext']['translatex'], 'text');
		showsetting('settings_watermarktext_imtranslatey', 'settingsnew[watermarktext][translatey]', $settings['watermarktext']['translatey'], 'text');
		showsetting('settings_watermarktext_imskewx', 'settingsnew[watermarktext][skewx]', $settings['watermarktext']['skewx'], 'text');
		showsetting('settings_watermarktext_imskewy', 'settingsnew[watermarktext][skewy]', $settings['watermarktext']['skewy'], 'text');

		if($isfounder) {
			$settings['ftp'] = unserialize($settings['ftp']);
			$settings['ftp'] = is_array($settings['ftp']) ? $settings['ftp'] : array();
			$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($authkey));
			$settings['ftp']['password'] = $settings['ftp']['password'] ? $settings['ftp']['password']{0}.'********'.$settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} : '';
			echo '</tbody></table><br />';
			showtype('settings_remote', 'top', 'settingsubmit');
			showsetting('settings_remote_enabled', array('settingsnew[ftp][on]', array(
				array(1, $lang['yes'], array('ftpext' => '', 'ftpcheckbutton' => '')),
				array(0, $lang['no'], array('ftpext' => 'none', 'ftpcheckbutton' => 'none')))), $settings['ftp']['on'], 'mradio');
			echo '</tbody><tbody class="sub" id="ftpext" style="display: '.($settings['ftp']['on'] ? '' : 'none').'">';
			showsetting('settings_remote_enabled_ssl', 'settingsnew[ftp][ssl]', $settings['ftp']['ssl'], 'radio');
			showsetting('settings_remote_ftp_host', 'settingsnew[ftp][host]', $settings['ftp']['host'], 'text');
			showsetting('settings_remote_ftp_port', 'settingsnew[ftp][port]', $settings['ftp']['port'], 'text');
			showsetting('settings_remote_ftp_user', 'settingsnew[ftp][username]', $settings['ftp']['username'], 'text');
			showsetting('settings_remote_ftp_pass', 'settingsnew[ftp][password]', $settings['ftp']['password'], 'text');
			showsetting('settings_remote_ftp_pasv', 'settingsnew[ftp][pasv]', $settings['ftp']['pasv'], 'radio');
			showsetting('settings_remote_dir', 'settingsnew[ftp][attachdir]', $settings['ftp']['attachdir'], 'text');
			showsetting('settings_remote_url', 'settingsnew[ftp][attachurl]', $settings['ftp']['attachurl'], 'text');
			showsetting('settings_remote_hide_dir', 'settingsnew[ftp][hideurl]', $settings['ftp']['hideurl'], 'radio');
			showsetting('settings_remote_timeout', 'settingsnew[ftp][timeout]', $settings['ftp']['timeout'], 'text');
			$extbutton = '<span id="ftpcheckbutton" style="display: '.($settings['ftp']['on'] ? '' : 'none').'">&nbsp;&nbsp;&nbsp;<input class="button" type="submit" name="ftpcheck" value="'.$lang['settings_remote_ftpcheck'].'" onclick="this.form.action=\'admincp.php?action=ftpcheck\';this.form.target=\'ftpcheckiframe\';"></span><iframe name="ftpcheckiframe" style="display: none"></iframe>';
		}

	} elseif($operation == 'javascript') {

		showtype('jswizard', 'top');
		echo '<tr><td class="altbg2"><a href="admincp.php?action=settings&do=javascript">'.$lang['jswizard_basesettings'].'</a>&nbsp;&nbsp;';
		if($settings['jsstatus']) {
			echo '<a href="admincp.php?action=jswizard">'.$lang['jswizard_project'].'</a>';
		}
		echo '</td></tr>';
		showtype('', 'bottom');
		echo '<br />';


		$settings['jsdateformat'] = dateformat($settings['jsdateformat']);

		showtype('settings_javascript', 'top', 'settingsubmit');
		showsetting('settings_jsstatus', 'settingsnew[jsstatus]', $settings['jsstatus'], 'radio', '', '', 1);
		showsetting('settings_jscachelife', 'settingsnew[jscachelife]', $settings['jscachelife'], 'text');
		showsetting('settings_jsdateformat', 'settingsnew[jsdateformat]', $settings['jsdateformat'], 'text');
		showsetting('settings_jsrefdomains', 'settingsnew[jsrefdomains]', $settings['jsrefdomains'], 'textarea');

	} elseif($operation == 'wap') {

		$settings['wapdateformat'] = dateformat($settings['wapdateformat']);

		showtype('settings_wap', 'top', 'settingsubmit');
		showsetting('settings_wapstatus', 'settingsnew[wapstatus]', $settings['wapstatus'], 'radio', '', '', 1);
		showsetting('settings_wap_register', 'settingsnew[wapregister]', $settings['wapregister'], 'radio');
		showsetting('settings_wapcharset', array('settingsnew1[wapcharset]', array(
			array(1, 'UTF-8'),
			array(2, 'UNICODE'))), $settings['wapcharset'], 'mradio');
		showsetting('settings_waptpp', 'settingsnew[waptpp]', $settings['waptpp'], 'text');
		showsetting('settings_wapppp', 'settingsnew[wapppp]', $settings['wapppp'], 'text');
		showsetting('settings_wapdateformat', 'settingsnew[wapdateformat]', $settings['wapdateformat'], 'text');
		showsetting('settings_wapmps', 'settingsnew[wapmps]', $settings['wapmps'], 'text');

	} elseif($operation == 'space') {

		$settings['spacedata'] = unserialize($settings['spacedata']);

		showtype('settings_space', 'top', 'settingsubmit');
		showsetting('settings_spacestatus', 'settingsnew[spacestatus]', $settings['spacestatus'], 'radio', '', '', 1);
		showsetting('settings_spacecachelife', 'settingsnew[spacedata][cachelife]', $settings['spacedata']['cachelife'], 'text');
		showsetting('settings_spacelimitmythreads', 'settingsnew[spacedata][limitmythreads]', $settings['spacedata']['limitmythreads'], 'text');
		showsetting('settings_spacelimitmyreplies', 'settingsnew[spacedata][limitmyreplies]', $settings['spacedata']['limitmyreplies'], 'text');
		showsetting('settings_spacelimitmyrewards', 'settingsnew[spacedata][limitmyrewards]', $settings['spacedata']['limitmyrewards'], 'text');
		showsetting('settings_spacelimitmytrades', 'settingsnew[spacedata][limitmytrades]', $settings['spacedata']['limitmytrades'], 'text');
		showsetting('settings_spacelimitmyvideos', 'settingsnew[spacedata][limitmyvideos]', $settings['spacedata']['limitmyvideos'], 'text');
		showsetting('settings_spacelimitmyblogs', 'settingsnew[spacedata][limitmyblogs]', $settings['spacedata']['limitmyblogs'], 'text');
		showsetting('settings_spacelimitmyfriends', 'settingsnew[spacedata][limitmyfriends]', $settings['spacedata']['limitmyfriends'], 'text');
		showsetting('settings_spacelimitmyfavforums', 'settingsnew[spacedata][limitmyfavforums]', $settings['spacedata']['limitmyfavforums'], 'text');
		showsetting('settings_spacelimitmyfavthreads', 'settingsnew[spacedata][limitmyfavthreads]', $settings['spacedata']['limitmyfavthreads'], 'text');
		showsetting('settings_spacetextlength', 'settingsnew[spacedata][textlength]', $settings['spacedata']['textlength'], 'text');

	} elseif($operation == 'cachethread') {

		include_once DISCUZ_ROOT.'./include/forum.func.php';
		$forumselect = '<select name="fids[]" multiple="multiple" style="width: 70%" size="10"><option value="all">'.$lang['all_forum'].'</option><option value="">&nbsp;</option>'.forumselect().'</select>';
		showtype('settings_cachethread', 'top', 'settingsubmit');
		showsetting('settings_cachethread_indexlife', 'settingsnew[cacheindexlife]', $settings['cacheindexlife'], 'text');
		showsetting('settings_cachethread_life', 'settingsnew[cachethreadlife]', $settings['cachethreadlife'], 'text');
		showsetting('settings_cachethread_dir', 'settingsnew[cachethreaddir]', $settings['cachethreaddir'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_cachethread_coefficient_set', 'top', 'settingsubmit');
		showsetting('settings_cachethread_coefficient', 'settingsnew[threadcaches]', '', "<input type=\"text\" size=\"30\" name=\"settingsnew[threadcaches]\" value=\"\">");
		showsetting('settings_cachethread_coefficient_forum', '', '', $forumselect);

	} elseif($operation == 'ecommerce') {

		if($from == 'creditwizard') {

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td><?=$lang['menu_tools_creditwizard']?></td></tr>
<tr><td><?=$lang['creditwizard_tips']?></td></tr></table><br />
<?

		}

		$settings['tradetypes'] = unserialize($settings['tradetypes']);

		$query = $db->query("SELECT * FROM {$tablepre}threadtypes WHERE special='1' ORDER BY displayorder");
		$tradetypeselect = '';
		while($type = $db->fetch_array($query)) {
			$tradetypeselect .= '<input class="checkbox" type="checkbox" name="settingsnew[tradetypes][]" value="'.$type['typeid'].'" '.(@in_array($type['typeid'], $settings['tradetypes']) ? 'checked' : '').'> '.$type['name'].'<br />';
		}

		showtype('settings_ecommerce_sub_credittrade', 'top', 'settingsubmit');
		showsetting('alipay_ratio', 'settingsnew[ec_ratio]', $settings['ec_ratio'], 'text');
		showsetting('alipay_mincredits', 'settingsnew[ec_mincredits]', $settings['ec_mincredits'], 'text');
		showsetting('alipay_maxcredits', 'settingsnew[ec_maxcredits]', $settings['ec_maxcredits'], 'text');
		showsetting('alipay_maxcreditspermonth', 'settingsnew[ec_maxcreditspermonth]', $settings['ec_maxcreditspermonth'], 'text');
		echo '</tbody></table><br />';

		showtype('settings_ecommerce_sub_goodstrade', 'top', 'settingsubmit');
		showsetting('settings_trade_biosize', 'settingsnew[maxbiotradesize]', $settings['maxbiotradesize'], 'text');
		showsetting('settings_trade_imagewidthheight', '', '', '<input name="settingsnew[tradeimagewidth]" size="20" value="'.intval($settings['tradeimagewidth']).'"> <span style="vertical-align: middle">X</span> <input name="settingsnew[tradeimageheight]" size="20" value="'.intval($settings['tradeimageheight']).'">');
		showsetting('settings_trade_type', '', '', $tradetypeselect);

	} elseif($operation == 'mail' && $isfounder) {

		$settings['mail'] = unserialize($settings['mail']);

		showtype('settings_mail', 'top', 'settingsubmit');
		showsetting('settings_mail_send', array('settingsnew[mail][mailsend]', array(
			array(1, $lang['settings_mail_send_1'], array('hidden1' => 'none', 'hidden2' => 'none')),
			array(2, $lang['settings_mail_send_2'], array('hidden1' => '', 'hidden2' => '')),
			array(3, $lang['settings_mail_send_3'], array('hidden1' => '', 'hidden2' => 'none')))), $settings['mail']['mailsend'], 'mradio');
		echo '</tbody><tbody class="sub" id="hidden1" style="display:'.($settings['mail']['mailsend'] == 1 ? ' none' : '').'">';
		showsetting('settings_mail_server', 'settingsnew[mail][server]', $settings['mail']['server'], 'text');
		showsetting('settings_mail_port', 'settingsnew[mail][port]', $settings['mail']['port'], 'text');
		echo '</tbody><tbody class="sub" id="hidden2" style="display:'.($settings['mail']['mailsend'] != 2 ? ' none' : '').'">';
		showsetting('settings_mail_auth', 'settingsnew[mail][auth]', $settings['mail']['auth'], 'radio');
		showsetting('settings_mail_from', 'settingsnew[mail][from]', $settings['mail']['from'], 'text');
		showsetting('settings_mail_username', 'settingsnew[mail][auth_username]', $settings['mail']['auth_username'], 'text');
		showsetting('settings_mail_password', 'settingsnew[mail][auth_password]', $settings['mail']['auth_password'], 'text');
		echo '</tbody><tbody>';
		showsetting('settings_mail_delimiter', array('settingsnew[mail][maildelimiter]', array(
			array(1, $lang['settings_mail_delimiter_crlf']),
			array(0, $lang['settings_mail_delimiter_lf']),
			array(2, $lang['settings_mail_delimiter_cr']))),  $settings['mail']['maildelimiter'], 'mradio');
		showsetting('settings_mail_includeuser', 'settingsnew[mail][mailusername]', $settings['mail']['mailusername'], 'radio');
		showsetting('settings_mail_silent', 'settingsnew[mail][sendmail_silent]', $settings['mail']['sendmail_silent'], 'radio');
		echo '</table><br />';

		showtype('settings_mail_test', 'top', 'settingsubmit');
		showsetting('settings_mail_test_from', 'test_from', '', 'text');
		showsetting('settings_mail_test_to', 'test_to', '', 'textarea');
		echo '</tbody>';

		$extbutton = '&nbsp;&nbsp;&nbsp;<input class="button" type="submit" name="mailcheck" value="'.$lang['settings_mailcheck'].'" onclick="this.form.action=\'admincp.php?action=mailcheck\';this.form.target=\'mailcheckiframe\'"><iframe name="mailcheckiframe" style="display: none"></iframe>';
	} else {

		$operation = 'basic';
		showtype('settings_general', 'top');
		showsetting('settings_bbname', 'settingsnew[bbname]', $settings['bbname'], 'text');
		showsetting('settings_sitename', 'settingsnew[sitename]', $settings['sitename'], 'text');
		showsetting('settings_siteurl', 'settingsnew[siteurl]', $settings['siteurl'], 'text');
		showsetting('settings_index_name', 'settingsnew[indexname]', $settings['indexname'], 'text');
		showsetting('settings_icp', 'settingsnew[icp]', $settings['icp'], 'text');
		showsetting('settings_boardlicensed', 'settingsnew[boardlicensed]', $settings['boardlicensed'], 'radio');
		showsetting('settings_bbclosed', 'settingsnew[bbclosed]', $settings['bbclosed'], 'radio');
		showsetting('settings_closedreason', 'settingsnew[closedreason]', $settings['closedreason'], 'textarea');
	}
	showtype('', 'bottom');

	echo '<br /><center><input type="hidden" name="from" value="'.$from.'"><input class="button" type="submit" name="settingsubmit" value="'.$lang['submit'].'">'.$extbutton.'</center></form>';

} else {

	if(isset($settingsnew['bbname'])) {
		$settingsnew['bbname'] = dhtmlspecialchars($settingsnew['bbname']);
	}

	if(isset($settingsnew['regname'])) {
		$settingsnew['regname'] = dhtmlspecialchars($settingsnew['regname']);
	}

	if(isset($settingsnew['reglinkname'])) {
		$settingsnew['reglinkname'] = dhtmlspecialchars($settingsnew['reglinkname']);
	}

	if(isset($settingsnew['censoruser'])) {
		$settingsnew['censoruser'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['censoruser']));
	}

	if(isset($settingsnew['censoremail'])) {
		$settingsnew['censoremail'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['censoremail']));
	}

	if(isset($settingsnew['ipregctrl'])) {
		$settingsnew['ipregctrl'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['ipregctrl']));
	}

	if(isset($settingsnew['ipaccess'])) {
		if($settingsnew['ipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['ipaccess']))) {
			if(!ipaccess($onlineip, $settingsnew['ipaccess'])) {
				cpmsg('settings_ipaccess_invalid');
			}
		}
	}

	if(isset($settingsnew['adminipaccess'])) {
		if($settingsnew['adminipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['adminipaccess']))) {
			if(!ipaccess($onlineip, $settingsnew['adminipaccess'])) {
				cpmsg('settings_adminipaccess_invalid');
			}
		}
	}

	if(isset($settingsnew['welcomemsgtitle'])) {
		$settingsnew['welcomemsgtitle'] = cutstr(trim(dhtmlspecialchars($settingsnew['welcomemsgtitle'])), 75);
	}

	if(isset($settingsnew['showsignatures']) && isset($settingsnew['showavatars']) && isset($settingsnew['showimages'])) {
		$settingsnew['showsettings'] = bindec($settingsnew['showsignatures'].$settingsnew['showavatars'].$settingsnew['showimages']);
	}

	if(!empty($settingsnew['globalstick'])) {
		updatecache('globalstick');
	}

	if(isset($settingsnew['inviteconfig'])) {
		$settingsnew['inviteconfig'] = addslashes(serialize($settingsnew['inviteconfig']));
	}

	if($operation == 'functions') {
		$jsmenumax = is_array($settingsnew['jsmenustatus']) ? max(array_keys($settingsnew['jsmenustatus'])) : 0;
		$jsmenustatus = '';
		for($i = $jsmenumax; $i > 0; $i --) {
			$jsmenustatus .= intval($settingsnew['jsmenustatus'][$i]);
		}
		$settingsnew['jsmenustatus'] = bindec($jsmenustatus);
		$settingsnew['smthumb'] = intval($settingsnew['smthumb']) >= 20 && intval($settingsnew['smthumb']) <= 40 ? intval($settingsnew['smthumb']) : 20;
	}

	if(isset($settingsnew['defaulteditormode']) && isset($settingsnew['allowswitcheditor'])) {
		$settingsnew['editoroptions'] = bindec($settingsnew['defaulteditormode'].$settingsnew['allowswitcheditor']);
	}

	if(isset($settingsnew['myrecorddays'])) {
		$settingsnew['myrecorddays'] = intval($settingsnew['myrecorddays']) > 0 ? intval($settingsnew['myrecorddays']) : 30;
	}

	if(!empty($settingsnew['thumbstatus']) && !function_exists('imagejpeg')) {
		$settingsnew['thumbstatus'] = 0;
	}

	if(isset($settingsnew['creditsformula']) && isset($settingsnew['extcredits']) && isset($settingsnew['creditspolicy']) && isset($settingsnew['initcredits']) && isset($settingsnew['creditstrans']) && isset($settingsnew['creditstax'])) {
		if(!preg_match("/^([\+\-\*\/\.\d\(\)]|((extcredits[1-8]|digestposts|posts|pageviews|oltime)([\+\-\*\/\(\)]|$)+))+$/", $settingsnew['creditsformula']) || !is_null(@eval(preg_replace("/(digestposts|posts|pageviews|oltime|extcredits[1-8])/", "\$\\1", $settingsnew['creditsformula']).';'))) {
			cpmsg('settings_creditsformula_invalid');
		}

		$extcreditsarray = array();
		if(is_array($settingsnew['extcredits'])) {
			foreach($settingsnew['extcredits'] as $key => $value) {
				if($value['available'] && !$value['title']) {
					cpmsg('settings_credits_title_invalid');
				}
				$extcreditsarray[$key] = array
					(
					'title'	=> dhtmlspecialchars(stripslashes($value['title'])),
					'unit' => dhtmlspecialchars(stripslashes($value['unit'])),
					'ratio' => ($value['ratio'] > 0 ? (float)$value['ratio'] : 0),
					'available' => $value['available'],
					'lowerlimit' => intval($settingsnew['creditspolicy']['lowerlimit'][$key]),
					'showinthread' => $value['showinthread'],
					'allowexchangein' => $value['allowexchangein'],
					'allowexchangeout' => $value['allowexchangeout']
					);
				$settingsnew['initcredits'][$key] = intval($settingsnew['initcredits'][$key]);
			}
		}
		if(is_array($settingsnew['creditspolicy'])) {
			foreach($settingsnew['creditspolicy'] as $key => $value) {
				for($i = 1; $i <= 8; $i++) {
					if(empty($value[$i])) {
						unset($settingsnew['creditspolicy'][$key][$i]);
					} else {
						$value[$i] = $value[$i] > 99 ? 99 : ($value[$i] < -99 ? -99 : $value[$i]);
						$settingsnew['creditspolicy'][$key][$i] = intval($value[$i]);
					}
				}
			}
		} else {
			$settingsnew['creditspolicy'] = array();
		}

		if($settingsnew['creditstrans'] && empty($settingsnew['extcredits'][$settingsnew['creditstrans']]['available'])) {
			cpmsg('settings_creditstrans_invalid');
		}
		$settingsnew['creditspolicy'] = addslashes(serialize($settingsnew['creditspolicy']));

		$settingsnew['creditsformulaexp'] = $settingsnew['creditsformula'];
		foreach(array('digestposts', 'posts', 'oltime', 'pageviews', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8') as $var) {
			if($extcreditsarray[$creditsid = preg_replace("/^extcredits(\d{1})$/", "\\1", $var)]['available']) {
				$replacement = $extcreditsarray[$creditsid]['title'];
			} else {
				$replacement = $lang['settings_creditsformula_'.$var];
			}
			$settingsnew['creditsformulaexp'] = str_replace($var, '<u>'.$replacement.'</u>', $settingsnew['creditsformulaexp']);
		}
		$settingsnew['creditsformulaexp'] = addslashes('<u>'.$lang['settings_creditsformula_credits'].'</u>='.$settingsnew['creditsformulaexp']);

		$initformula = str_replace('posts', '0', $settingsnew['creditsformula']);
		for($i = 1; $i <= 8; $i++) {
			$initformula = str_replace('extcredits'.$i, $settingsnew['initcredits'][$i], $initformula);
		}
		eval("\$initcredits = round($initformula);");

		$settingsnew['extcredits'] = addslashes(serialize($extcreditsarray));
		$settingsnew['initcredits'] = $initcredits.','.implode(',', $settingsnew['initcredits']);
		if($settingsnew['creditstax'] < 0 || $settingsnew['creditstax'] >= 1) {
			$settingsnew['creditstax'] = 0;
		}
	}

	if(isset($settingsnew['gzipcompress'])) {
		if(!function_exists('ob_gzhandler') && $settingsnew['gzipcompress']) {
			cpmsg('settings_gzip_invalid');
		}
	}

	if(isset($settingsnew['maxonlines'])) {
		if($settingsnew['maxonlines'] > 65535 || !is_numeric($settingsnew['maxonlines'])) {
			cpmsg('settings_maxonlines_invalid');
		}

		$db->query("ALTER TABLE {$tablepre}sessions MAX_ROWS=$settingsnew[maxonlines]");
		if($settingsnew['maxonlines'] < $settings['maxonlines']) {
			$db->query("DELETE FROM {$tablepre}sessions");
		}
	}

	if(isset($settingsnew['seccodedata'])) {
		$settingsnew['seccodedata']['width'] = intval($settingsnew['seccodedata']['width']);
		$settingsnew['seccodedata']['height'] = intval($settingsnew['seccodedata']['height']);
		$settingsnew['seccodedata']['width'] = $settingsnew['seccodedata']['width'] < 100 ? 100 : ($settingsnew['seccodedata']['width'] > 200 ? 200 : $settingsnew['seccodedata']['width']);
		$settingsnew['seccodedata']['height'] = $settingsnew['seccodedata']['height'] < 50 ? 50 : ($settingsnew['seccodedata']['height'] > 80 ? 80 : $settingsnew['seccodedata']['height']);
		$settingsnew['seccodedata']['loginfailedcount'] = !empty($settingsnew['seccodedata']['loginfailedcount']) ? 3 : 0;
		$settingsnew['seccodedata'] = addslashes(serialize($settingsnew['seccodedata']));
	}

	if($operation == 'seccode' || isset($settingsnew['seccodestatus'])) {
		$settingsnew['seccodestatus'] = bindec(intval($settingsnew['seccodestatus'][5]).intval($settingsnew['seccodestatus'][4]).intval($settingsnew['seccodestatus'][3]).intval($settingsnew['seccodestatus'][2]).intval($settingsnew['seccodestatus'][1]));
	}

	if($operation == 'seo') {
		$settingsnew['rewritestatus'] = bindec(intval($settingsnew['rewritestatus'][5]).intval($settingsnew['rewritestatus'][4]).intval($settingsnew['rewritestatus'][3]).intval($settingsnew['rewritestatus'][2]).intval($settingsnew['rewritestatus'][1]));
		$settingsnew['baidusitemap_life'] = max(1, min(24, intval($settingsnew['baidusitemap_life'])));
	}

	if($operation == 'secqaa') {
		if(is_array($delete)) {
			$db->query("DELETE FROM	{$tablepre}itempool WHERE id IN (".implodeids($delete).")");
		}

		if(is_array($question)) {
			foreach($question AS $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($answer[$key])), 50);
				if($q && $a) {
					$db->query("UPDATE {$tablepre}itempool SET question='$q', answer='$a' WHERE id='$key'");
				}
			}
		}

		if(is_array($newquestion) && is_array($newanswer)) {
			foreach($newquestion AS $key => $q) {
				$q = trim($q);
				$a = cutstr(dhtmlspecialchars(trim($newanswer[$key])), 50);
				if($q && $a) {
					$db->query("INSERT INTO	{$tablepre}itempool (question, answer) VALUES ('$q', '$a')");
				}
			}
		}

		updatecache('secqaa');

		$settingsnew['secqaa']['status'] = bindec(intval($settingsnew['secqaa']['status'][3]).intval($settingsnew['secqaa']['status'][2]).intval($settingsnew['secqaa']['status'][1]));

		$settingsnew['secqaa'] = serialize($settingsnew['secqaa']);

	}

	if($operation == 'ecommerce') {
		if($settingsnew['ec_ratio']) {
			if($settingsnew['ec_ratio'] < 0) {
				cpmsg('alipay_ratio_invalid');
			}
		} else {
			$settingsnew['ec_mincredits'] = $settingsnew['ec_maxcredits'] = 0;
		}
		foreach(array('ec_ratio', 'ec_mincredits', 'ec_maxcredits', 'ec_maxcreditspermonth', 'tradeimagewidth', 'tradeimageheight') as $key) {
			$settingsnew[$key] = intval($settingsnew[$key]);
		}
		$settingsnew['tradetypes'] = addslashes(serialize($settingsnew['tradetypes']));
	}

	if(isset($settingsnew['visitbanperiods']) && isset($settingsnew['postbanperiods']) && isset($settingsnew['postmodperiods']) && isset($settingsnew['searchbanperiods'])) {
		foreach(array('visitbanperiods', 'postbanperiods', 'postmodperiods', 'searchbanperiods') as $periods) {
			$periodarray = array();
			foreach(explode("\n", $settingsnew[$periods]) as $period) {
				if(preg_match("/^\d{1,2}\:\d{2}\-\d{1,2}\:\d{2}$/", $period = trim($period))) {
					$periodarray[] = $period;
				}
			}
			$settingsnew[$periods] = implode("\r\n", $periodarray);
		}
	}

	if(isset($settingsnew['timeformat'])) {
		$settingsnew['timeformat'] = $settingsnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
	}

	if(isset($settingsnew['dateformat'])) {
		$settingsnew['dateformat'] = dateformat($settingsnew['dateformat'], 'format');
	}

	if(isset($settingsnew['userdateformat'])) {
		$settingsnew['userdateformat'] = dateformat($settingsnew['userdateformat'], 'format');
	}

	if($isfounder && isset($settingsnew['ftp'])) {
		$settings['ftp'] = unserialize($settings['ftp']);
		$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($authkey));
		if(!empty($settingsnew['ftp']['password'])) {
			$pwlen = strlen($settingsnew['ftp']['password']);
			if($pwlen < 3) {
				cpmsg('ftp_password_short');
			}
			if($settingsnew['ftp']['password']{0} == $settings['ftp']['password']{0} && $settingsnew['ftp']['password']{$pwlen - 1} == $settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} && substr($settingsnew['ftp']['password'], 1, $pwlen - 2) == '********') {
				$settingsnew['ftp']['password'] = $settings['ftp']['password'];
			}
			$settingsnew['ftp']['password'] = authcode($settingsnew['ftp']['password'], 'ENCODE', md5($authkey));
		}
		$settingsnew['ftp'] = serialize($settingsnew['ftp']);
	}

	if($isfounder && isset($settingsnew['mail'])) {
		$settingsnew['mail'] = serialize($settingsnew['mail']);
	}

	if(isset($settingsnew['jsrefdomains'])) {
		$settingsnew['jsrefdomains'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['jsrefdomains']));
	}

	if(isset($settingsnew['jsdateformat'])) {
		$settingsnew['jsdateformat'] = dateformat($settingsnew['jsdateformat'], 'format');
	}

	if(isset($settingsnew['wapdateformat'])) {
		$settingsnew['wapdateformat'] = dateformat($settingsnew['wapdateformat'], 'format');
	}

	if(isset($settingsnew['cachethreaddir']) && isset($settingsnew['threadcaches'])) {
		if($settingsnew['cachethreaddir'] && !is_writable(DISCUZ_ROOT.'./'.$settingsnew['cachethreaddir'])) {
			cpmsg('cachethread_dir_noexists');
		}
		if(!empty($fids)) {
			$sqladd = in_array('all', $fids) ? '' :  " WHERE fid IN ('".implode("', '", $fids)."')";
			$settingsnew['threadcaches'] = min($settingsnew['threadcaches'], 100);
			$db->query("UPDATE {$tablepre}forums SET threadcaches='$settingsnew[threadcaches]'$sqladd");
		}
	}

	if(isset($settingsnew['watermarktext'])) {
		$settingsnew['watermarktext']['size'] = intval($settingsnew['watermarktext']['size']);
		$settingsnew['watermarktext']['angle'] = intval($settingsnew['watermarktext']['angle']);
		$settingsnew['watermarktext']['shadowx'] = intval($settingsnew['watermarktext']['shadowx']);
		$settingsnew['watermarktext']['shadowy'] = intval($settingsnew['watermarktext']['shadowy']);
		$settingsnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $settingsnew['watermarktext']['fontpath']);
		if($settingsnew['watermarktype'] == 2 && $settingsnew['watermarktext']['fontpath']) {
			$fontpath = $settingsnew['watermarktext']['fontpath'];
			$fontpathnew = 'ch/'.$fontpath;
			$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
			if(!$settingsnew['watermarktext']['fontpath']) {
				$fontpathnew = 'en/'.$fontpath;
				$settingsnew['watermarktext']['fontpath'] = file_exists('images/fonts/'.$fontpathnew) ? $fontpathnew : '';
			}
			if(!$settingsnew['watermarktext']['fontpath']) {
				cpmsg('watermarkpreview_fontpath_error');
			}
		}
		$settingsnew['watermarktext'] = addslashes(serialize($settingsnew['watermarktext']));
	}

	if(isset($settingsnew['msgforward'])) {
		if(!empty($settingsnew['msgforward']['messages'])) {
			$tempmsg = explode("\n", $settingsnew['msgforward']['messages']);
			$settingsnew['msgforward']['messages'] = array();
			foreach($tempmsg as $msg) {
				if($msg = strip_tags(trim($msg))) {
					$settingsnew['msgforward']['messages'][] = $msg;
				}
			}
		} else {
			$settingsnew['msgforward']['messages'] = array();
		}

		$tmparray = array(
			'refreshtime' => intval($settingsnew['msgforward']['refreshtime']),
			'quick' => $settingsnew['msgforward']['quick'] ? 1 : 0,
			'messages' => $settingsnew['msgforward']['messages']
		);
		$settingsnew['msgforward'] = addslashes(serialize($tmparray));
	}

	if(isset($settingsnew['onlinehold'])) {
		$settingsnew['onlinehold'] = intval($settingsnew['onlinehold']) > 0 ? intval($settingsnew['onlinehold']) : 15;
	}


	if(isset($settingsnew['postno'])) {
		$settingsnew['postno'] = trim($settingsnew['postno']);
	}
	if(isset($settingsnew['postnocustom'])) {
		$settingsnew['postnocustom'] = addslashes(serialize(explode("\n", $settingsnew['postnocustom'])));
	}

	if($operation == 'styles') {
		$settingsnew['customauthorinfo'] = addslashes(serialize(array($settingsnew['customauthorinfo'])));
	}

	if(isset($settingsnew['spacedata'])) {
		$settingsnew['spacedata'] = serialize($settingsnew['spacedata']);
	}

	$updatecache = FALSE;

	foreach($settingsnew AS $key => $val) {
		if((isset($settings[$key]) || $key == 'admode') && $settings[$key] != $val) {
			$$key = $val;
			$updatecache = TRUE;
			if(in_array($key, array('newbiespan', 'topicperpage', 'postperpage', 'memberperpage', 'hottopic', 'starthreshold', 'delayviewcount',
				'visitedforums', 'maxsigrows', 'timeoffset', 'statscachelife', 'pvfrequence', 'oltimespan', 'seccodestatus',
				'maxprice', 'rssttl', 'rewritestatus', 'bdaystatus', 'maxonlines', 'loadctrl', 'floodctrl', 'regctrl', 'regfloodctrl',
				'searchctrl', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6',
				'extcredits7', 'extcredits8', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan',
				'maxspm', 'maxsearchresults', 'maxsmilies', 'threadmaxpages', 'membermaxpages', 'maxpostsize', 'minpostsize', 'maxavatarsize',
				'maxavatarpixel', 'maxpolloptions', 'karmaratelimit', 'losslessdel', 'edittimelimit', 'smcols',
				'watermarktrans', 'watermarkquality', 'jscachelife', 'waptpp', 'wapppp', 'wapmps', 'maxmodworksmonths', 'frameon', 'maxonlinelist', 'admode'))) {
				$val = (float)$val;
			}
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$key', '$val')");
		}
	}

	if($updatecache) {
		updatecache('settings');
		if(isset($settingsnew['forumlinkstatus']) && $settingsnew['forumlinkstatus'] != $settings['forumlinkstatus']) {
			updatecache('forumlinks');
		}
		if(isset($settingsnew['userstatusby']) && $settingsnew['userstatusby'] != $settings['userstatusby']) {
			updatecache('usergroups');
			updatecache('ranks');
		}
		if((isset($settingsnew['tagstatus']) && $settingsnew['tagstatus'] != $settings['tagstatus']) || (isset($settingsnew['hottags']) && $settingsnew['hottags'] != $settings['hottags']) || (isset($settingsnew['viewthreadtags']) && $settingsnew['viewthreadtags'] != $settings['viewthreadtags'])) {
			updatecache(array('tags_index', 'tags_viewthread'));
		}
		if((isset($settingsnew['smthumb']) && $settingsnew['smthumb'] != $settings['smthumb']) || (isset($settingsnew['smcols']) && $settingsnew['smcols'] != $settings['smcols']) || (isset($settingsnew['smrows']) && $settingsnew['smrows'] != $settings['smrows'])) {
			updatecache('smilies_display');
		}
		if(isset($settingsnew['customauthorinfo']) && $settingsnew['customauthorinfo'] != $settings['customauthorinfo']) {
			updatecache('custominfo');
		}
		if($operation == 'credits') {
			updatecache('custominfo');
		}
	}

	if($operation == 'credits' && $projectsave) {
		$projectid = intval($projectid);
		dheader("Location: {$boardurl}admincp.php?action=projectadd&type=extcredit&projectid=$projectid");
	}
	cpmsg('settings_update_succeed', 'admincp.php?action=settings&operation='.$operation.(!empty($from) ? '&from='.$from : ''));
}

function creditsrow($rowname) {
	global $settings;
	$policyrow = '';
	for($i = 1; $i <= 8; $i++) {
		$policyrow .="<td ".($settings['extcredits'][$i]['available'] ? '' : 'disabled')." class=\"altbg".(is_int($i/2) ? 1 : 2)."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][$rowname][$i]\" ".($settings['extcredits'][$i]['available'] ? '' : 'readonly')." value=\"".intval($settings['creditspolicy'][$rowname][$i])."\"></td>";
	}
	return $policyrow;
}

function dateformat($string, $operation = 'formalise') {
	$string = htmlspecialchars(trim($string));
	$replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
	return str_replace($replace[0], $replace[1], $string);
}

?>