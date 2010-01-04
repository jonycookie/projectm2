<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: supesite.inc.php 11271 2007-11-16 01:49:13Z monkey $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable IN ('supe', 'supe_siteurl', 'supe_sitename', 'supe_status', 'supe_circlestatus', 'supe_tablepre')");
while($setting = $db->fetch_array($query)) {
	$settings[$setting['variable']] = $setting['value'];
}
$settings['supe'] = (array)unserialize($settings['supe']);
$settings = daddslashes($settings, 1);

if(!isset($settings['supe_siteurl'])) {

	shownav('supe_settings');
	showtips('supe_tips_unstalled');

} else {

	if(!submitcheck('settingsubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';
                if($settings['supe_circlestatus']) {
        	        $query = $db->query("SELECT fid FROM {$tablepre}forums WHERE status=2");
        	        $circlefids = $delimiter = '';
        	        while($forum = $db->fetch_array($query)) {
        	                $circlefids .= $delimiter.$forum['fid'];
        	                $delimiter = '|';
        	        }
                        $forumselect = '<select name="circlefids[]" size="10" multiple="multiple">'.preg_replace("/(\<option value=\"(".$circlefids.")\")(\>)/", "\\1 selected=\"selected\"\\3", forumselect()).'</select>';
                }

		$orderbycheck = array($settings['supe']['items']['orderby'] => 'checked');
		$dbmodecheck = array($settings['supe']['dbmode'] => 'checked');

		shownav('supe_settings');
		showtips('supe_tips_installed');

		echo '<form method="post" name="settings" action="admincp.php?action=xspace">';
		echo '<input type="hidden" name="formhash" value="'.FORMHASH.'">';

		showtype('supe_settings', 'top');
		showsetting('supe_status', 'settingsnew[supe][status]', $settings['supe_status'], 'radio');
		showsetting('supe_tablepre', 'settingsnew[supe][tablepre]', $settings['supe_tablepre'], 'text');
		showsetting('supe_siteurl', 'settingsnew[supe][siteurl]', $settings['supe_siteurl'], 'text');
		showsetting('supe_sitename', 'settingsnew[supe][sitename]', $settings['supe_sitename'], 'text');
		showsetting('supe_last_newuser', 'settingsnew[supe][maxupdateusers]', $settings['supe']['maxupdateusers'], 'text');

		showsetting('supe_hot', 'settingsnew[supe][items][status]', $settings['supe']['items']['status'], 'radio', '', '', 1);
		showsetting('supe_hot_hours', 'settingsnew[supe][items][hours]', $settings['supe']['items']['hours'], 'text');
		showsetting('supe_hot_lines', 'settingsnew[supe][items][rows]', $settings['supe']['items']['rows'], 'text');
		showsetting('supe_hot_cols', 'settingsnew[supe][items][columns]', $settings['supe']['items']['columns'], 'text');

		echo '<tr><td class="altbg1"><b>'.$lang['supe_hot_order'].'</b><br />'.$lang['supe_hot_order_comment'].'</td>';
		echo '<td class="altbg2"><input class="radio" type="radio" name="settingsnew[supe][items][orderby]" value="1" '.$orderbycheck[1].'>'.$lang['supe_hot_order_views'].'<br /><input class="radio" type="radio" name="settingsnew[supe][items][orderby]" value="2" '.$orderbycheck[2].'>'.$lang['supe_hot_order_replies'].'<br /><input class="radio" type="radio" name="settingsnew[supe][items][orderby]" value="3" '.$orderbycheck[3].'>'.$lang['supe_hot_lasttime'].'<br /><input class="radio" type="radio" name="settingsnew[supe][items][orderby]" value="4" '.$orderbycheck[4].'>'.$lang['supe_hot_lasttime_reply'].'</td></tr>';
		echo '</tbody><tbody>';
		echo '<tr><td class="altbg1"><b>'.$lang['supe_status_batch'].'</b><br />'.$lang['supe_collection_mode_comment'].'</td>';
		echo '<td class="altbg2"><input class="radio" type="radio" name="supe_pushsetting[status]" value="-1" onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'" checked>'.$lang['supe_status_batch_noopt'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="0" onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'">'.$lang['supe_pushsetting_status_0'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="1" onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'">'.$lang['supe_pushsetting_status_1'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="2" onclick="$(\'supe_pushsetting_filter_div\').style.display=\'none\'">'.$lang['supe_pushsetting_status_2'].'<br /><input class="radio" type="radio" name="supe_pushsetting[status]" value="3" onclick="$(\'supe_pushsetting_filter_div\').style.display=\'block\'">'.$lang['supe_pushsetting_status_3'];
		echo '<div id="supe_pushsetting_filter_div" style="display:'.($forum['supe_pushsetting']['status'] == 3 ? 'block' : 'none').'">';
		echo ''.$lang['supe_pushsetting_views'].' >= <input type="input" name="supe_pushsetting[filter][views]" value="'.$forum['supe_pushsetting']['filter']['views'].'" size="8">';
		echo '<br />'.$lang['supe_pushsetting_replys'].' >= <input type="input" name="supe_pushsetting[filter][replies]" value="'.$forum['supe_pushsetting']['filter']['replies'].'" size="8">';
		echo '<br />'.$lang['supe_pushsetting_digest'].' >= <select name="supe_pushsetting[filter][digest]"><option value="0"></option><option value="1">'.$lang['forums_digest_one'].'</option><option value="2">'.$lang['forums_digest_two'].'</option><option value="3">'.$lang['forums_digest_three'].'</option></select>';
		echo '<br />'.$lang['supe_pushsetting_stick'].' >= <select name="supe_pushsetting[filter][displayorder]"><option value="0"></option><option value="1">'.$lang['forums_stick_one'].'</option><option value="2">'.$lang['forums_stick_two'].'</option><option value="3">'.$lang['forums_stick_three'].'</option></select>';

		echo '</div><br />';
		echo '<select name="collectfids[]" size="10" multiple="multiple">'.forumselect().'</select>';
		echo '</td></tr>';

		if($settings['supe_circlestatus']) {
			showsetting('sup_group_discuss_show', 'settingsnew[supe][updatecircles]', $settings['supe']['updatecircles'], 'radio');
		        showsetting('sup_group_discuss', '', '', $forumselect);
		}

		showtype('', 'bottom');
		echo '<br />';
		showtype('supe_dbsettings', 'top');
		showsetting('supe_dbmode', 'settingsnew[supe][dbmode]', $settings['supe']['dbmode'], 'radio', '', '', 1);
		showsetting('supe_dbhost', 'settingsnew[supe][dbhost]', $settings['supe']['dbhost'], 'text');
		showsetting('supe_dbuser', 'settingsnew[supe][dbuser]', $settings['supe']['dbuser'], 'text');
		showsetting('supe_dbpw', 'settingsnew[supe][dbpw]', $settings['supe']['dbpw'], 'text');
		showsetting('supe_dbname', 'settingsnew[supe][dbname]', $settings['supe']['dbname'], 'text');
		showtype('', 'bottom');



?>
<br /><center><input class="button" type="submit" name="settingsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$settingsnew['supe']['maxupdateusers'] = intval($settingsnew['supe']['maxupdateusers']);
                $supe = $settingsnew['supe'];
                $supe['circlestatus'] = $settings['supe_circlestatus'];

		if($supe['dbmode'] == 1) {
			if($supe['dbhost'] == $dbhost) {
				$supe['dbmode'] = 0;
			} else {
				$supe_dblink = @mysql_connect($supe['dbhost'], $supe['dbuser'], $supe['dbpw'], 1);
				if(!$supe_dblink) {
					cpmsg('supe_database_connect_error');
				} elseif(!@mysql_select_db($supe['dbname'], $supe_dblink)) {
					cpmsg('supe_database_select_db_error');
				} else {
					mysql_close($supe_dblink);
				}
			}
		}
                $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe', '".addslashes(serialize($supe))."')");
                $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_status', '$supe[status]')");
                $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_tablepre', '$supe[tablepre]')");
                $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_siteurl', '$supe[siteurl]')");
                $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_sitename', '$supe[sitename]')");

		if($supe_pushsetting['status'] != '-1' && $collectfids = implodeids($collectfids)) {
			$db->query("UPDATE {$tablepre}forumfields SET supe_pushsetting='".serialize($supe_pushsetting)."' WHERE fid IN ($collectfids)");
		}

		updatecache('settings');
		updatecache(array('supe_updateusers', 'supe_updateitems', 'supe_updatecircles'));

		if($settings['supe_circlestatus'] && $circlefids && is_array($circlefids)) {
			$db->query("UPDATE {$tablepre}forums SET status=1 WHERE fid NOT IN (".implode(',', $circlefids).") AND status=2");
			$db->query("UPDATE {$tablepre}forums SET status=2 WHERE fid IN (".implode(',', $circlefids).")");
		}
		$db->query("TRUNCATE TABLE {$tablepre}spacecaches", 'UNBUFFERED');

		cpmsg('supe_update_succeed');

	}
}

?>