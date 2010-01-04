<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: runwizard.inc.php 16736 2008-11-17 05:25:55Z cnteacher $
*/


if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}


$step = in_array($step, array(1, 2, 3, 4)) ? $step : 1;
$runwizardhistory = array();
$runwizardfile = DISCUZ_ROOT.'./forumdata/logs/runwizardlog.php';
if($fp = @fopen($runwizardfile, 'r')) {
	fseek($fp, 13);
	$runwizardhistory = @unserialize(fread($fp, 99999));
	fclose($fp);
}

cpheader();
$runwizardwarnning = empty($runwizard) ? '<li>'.$lang['runwizard_cover_old'].'</li>' : '';
$listchecked[$step] = ' style="font-weight: 800"';

shownav('menu_runwizard');

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr class="altbg1"><td><ul><li><?=$lang['runwizard_help']?></li></ul><ul>
<li><?=$lang['runwizard_step_guide']?></li>
<ul>
<li<?=$listchecked[1]?>><?=$lang['runwizard_step_1']?></li>
<li<?=$listchecked[2]?>><?=$lang['runwizard_step_2']?></li>
<li<?=$listchecked[3]?>><?=$lang['runwizard_step_3']?></li>
<li<?=$listchecked[4]?>><?=$lang['runwizard_step_4']?></li></ul>
<?=$runwizardwarnning?>
</ul>
</td></tr></table>
<?

if($step == 1) {
	$sizecheckedid = isset($runwizardhistory['step1']['size']) ?  $runwizardhistory['step1']['size'] : 1;
	$sizechecked   = array($sizecheckedid => ' checked');
	$safecheckedid = isset($runwizardhistory['step1']['safe']) ?  $runwizardhistory['step1']['safe'] : 0;
	$safechecked   = array($safecheckedid => 'checked');
	$funccheckedid = isset($runwizardhistory['step1']['func']) ?  $runwizardhistory['step1']['func'] : 1;
	$funcchecked   = array($funccheckedid => 'checked');

?>
<br /><form method="post" action="admincp.php?action=runwizard&step=2">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['menu_runwizard']?> - <?=$lang['runwizard_step_menu_1']?></td></tr>
<tr><td class="altbg1">
<ul>
<br /><b><?=$lang['runwizard_forum_scope']?></b>
<ul>
<input class="radio" type="radio" name="size" value="0"<?=$sizechecked[0]?>> <?=$lang['runwizard_forum_scope_small']?><br />
<input class="radio" type="radio" name="size" value="1"<?=$sizechecked[1]?>> <?=$lang['runwizard_forum_scope_midding']?><br />
<input class="radio" type="radio" name="size" value="2"<?=$sizechecked[2]?>> <?=$lang['runwizard_forum_scope_big']?><br />
</ul><br />
<b><?=$lang['runwizard_security']?></b>
<ul>
<input class="radio" type="radio" name="safe" value="2"<?=$safechecked[2]?>> <?=$lang['runwizard_security_high']?><br />
<input class="radio" type="radio" name="safe" value="1"<?=$safechecked[1]?>> <?=$lang['runwizard_security_midding']?><br />
<input class="radio" type="radio" name="safe" value="0"<?=$safechecked[0]?>> <?=$lang['runwizard_security_low']?><br />
</ul><br />
<b><?=$lang['runwizard_hobby']?></b>
<ul>
<input class="radio" type="radio" name="func" value="0"<?=$funcchecked[0]?>> <?=$lang['runwizard_hobby_concision']?><br />
<input class="radio" type="radio" name="func" value="1"<?=$funcchecked[1]?>> <?=$lang['runwizard_hobby_commonly']?><br />
<input class="radio" type="radio" name="func" value="2"<?=$funcchecked[2]?>> <?=$lang['runwizard_hobby_abundance']?><br />
</ul></ul><br /><br /></td></tr></table><br />
<center><input class="button" type="submit" name="step1submit" value=" <?=$lang['next']?> "></center>
</form>
<?

	$db->query("DELETE FROM {$tablepre}settings WHERE variable='runwizard'");
	updatecache('settings');

} elseif($step == 2) {
	if(submitcheck('step1submit')) {
		$runwizardhistory['step1']['size'] = $size;
		$runwizardhistory['step1']['safe'] = $safe;
		$runwizardhistory['step1']['func'] = $func;
		saverunwizardhistory();
	}
	$settings = &$_DCACHE['settings'];
	$settings['bbname']   = empty($runwizard) && $runwizardhistory['step2']['bbname'] ? $runwizardhistory['step2']['bbname'] : $settings['bbname'];
	$settings['sitename'] = empty($runwizard) && $runwizardhistory['step2']['sitename'] ? $runwizardhistory['step2']['sitename'] : $settings['sitename'];
	$settings['siteurl']  = empty($runwizard) && $runwizardhistory['step2']['siteurl'] ? $runwizardhistory['step2']['siteurl'] : $settings['siteurl'];

?>
<br /><form method="post" action="admincp.php?action=runwizard&step=3">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['menu_runwizard']?> - <?=$lang['runwizard_step_menu_2']?></td></tr>
<?

	showsetting('settings_bbname', 'settingsnew[bbname]', $settings['bbname'], 'text');
	showsetting('settings_sitename', 'settingsnew[sitename]', $settings['sitename'], 'text');
	showsetting('settings_siteurl', 'settingsnew[siteurl]', $settings['siteurl'], 'text');

?>
</table><br />
<center><input class="button" type="button" name="step2submit" value=" <?=$lang['prev']?> " onclick="history.back();"> <input class="button" type="submit" name="step2submit" value=" <?=$lang['next']?> "></center>
</form>
<?

} elseif($step == 3) {
	if(submitcheck('step2submit')) {
		$runwizardhistory['step2']['bbname']   = $settingsnew['bbname'];
		$runwizardhistory['step2']['sitename'] = $settingsnew['sitename'];
		$runwizardhistory['step2']['siteurl']  = $settingsnew['siteurl'];
		saverunwizardhistory();
	}

?>
<br /><form method="post" action="admincp.php?action=runwizard&step=4">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['menu_runwizard']?> - <?=$lang['runwizard_step_menu_3']?></td></tr>
<tr><td class="altbg1" colspan="2"><br />
<style type="text/css">
.ulforum li{
	margin:6px;
}
</style>
<ul class="ulforum">
<li><?=$lang['runwizard_cat']?> 1: <input type="text" name="newcat[0]" size="30" value="<?=$runwizardhistory['step3']['cates'][0]?>"></li><ul>
<li><?=$lang['runwizard_forum']?> 1: <input type="text" name="newforum[0][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][0][0]?>"></li>
<li><?=$lang['runwizard_forum']?> 2: <input type="text" name="newforum[0][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][0][1]?>"></li>
<li><?=$lang['runwizard_forum']?> 3: <input type="text" name="newforum[0][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][0][2]?>"></li>
</ul></ul>
<ul class="ulforum">
<li><?=$lang['runwizard_cat']?> 2: <input type="text" name="newcat[1]" size="30" value="<?=$runwizardhistory['step3']['cates'][1]?>"></li><ul>
<li><?=$lang['runwizard_forum']?> 4: <input type="text" name="newforum[1][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][1][0]?>"></li>
<li><?=$lang['runwizard_forum']?> 5: <input type="text" name="newforum[1][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][1][1]?>"></li>
<li><?=$lang['runwizard_forum']?> 6: <input type="text" name="newforum[1][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][1][2]?>"></li>
</ul></ul>
<ul class="ulforum">
<li><?=$lang['runwizard_cat']?> 3: <input type="text" name="newcat[2]" size="30" value="<?=$runwizardhistory['step3']['cates'][2]?>"></li><ul>
<li><?=$lang['runwizard_forum']?> 7: <input type="text" name="newforum[2][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][2][0]?>"></li>
<li><?=$lang['runwizard_forum']?> 8: <input type="text" name="newforum[2][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][2][1]?>"></li>
<li><?=$lang['runwizard_forum']?> 9: <input type="text" name="newforum[2][]"" size="30" value="<?=$runwizardhistory['step3']['forums'][2][2]?>"></li>
</ul></ul><br /><br />
</td></tr></table>
<br /><center><input class="button" type="button" name="step2submit" value=" <?=$lang['prev']?> " onclick="history.back();"> <input class="button" type="submit" name="step3submit" value=" <?=$lang['next']?> "> </center>
</form>
<?

} elseif($step == 4) {
	if(submitcheck('step3submit')) {
		foreach($newcat as $k=>$catename) {
			if(!$catename) {
				unset($newcat[$k]);
				unset($newforum[$k]);
			} else {
				foreach($newforum[$k] as $k2=>$forumname) {
					if(!$forumname) {
						unset($newforum[$k][$k2]);
					}
				}
			}
		}

		$runwizardhistory['step3']['cates']   = $newcat ? $newcat : array();
		$runwizardhistory['step3']['forums']   = $newforum ? $newforum : array();

		saverunwizardhistory();
	}

?>
<br />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['menu_runwizard']?> - <?=$lang['runwizard_step_menu_4']?></td></tr>
<tr><td class="altbg1" colspan="2">
<?

	if($confirm != 'yes') {

?>
<br /><ul>
<?=$lang['runwizard_forum_initialization']?><br />
<hr width="80%" align="left" noshade style="height:1px;"><br />
<b><?=$lang['runwizard_forum_scope']?></b> <?=$lang['runwizard_size_'.$runwizardhistory['step1']['size']]?><br /><br />
<b><?=$lang['runwizard_security']?></b> <?=$lang['runwizard_safe_'.$runwizardhistory['step1']['safe']]?><br /><br />
<b><?=$lang['runwizard_hobby']?></b> <?=$lang['runwizard_func_'.$runwizardhistory['step1']['func']]?><br /><br />
<b><?=$lang['settings_bbname']?></b> <?=$runwizardhistory['step2']['bbname']?><br /><br />
<b><?=$lang['settings_sitename']?></b> <?=$runwizardhistory['step2']['sitename']?><br /><br />
<b><?=$lang['settings_siteurl']?></b> <?=$runwizardhistory['step2']['siteurl']?><br /><br />
<b><?=$lang['runwizard_forum_add']?></b>
<?

	if($runwizardhistory['step3']['cates']) {
		echo '<br /><br />';
		foreach($runwizardhistory['step3']['cates'] as $id=>$catename) {
			echo '<ul><li>'.$catename.'</li><ul>';
			foreach($runwizardhistory['step3']['forums'][$id] as $forumname) {
				echo '<li>'.$forumname.'</li>';
			}
			echo '</ul></ul>';
		}
	}  else {
		echo $lang['none'];
	}

?>
</ul><br /><br />
</td></tr></table><br />
<form method="POST" action="admincp.php?action=runwizard&step=4&confirm=yes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<center> <input class="button" type="button" " value=" <?=$lang['prev']?> " onclick="history.back();"> <input class="button" type="submit" name="step4submit" value=" <?=$lang['submit']?> "></center></form>
<?

	} else {

		$sizesettings = array(
			'attachsave' => array('1', '3', '4'),
			'delayviewcount' => array('0', '0', '3'),
			'fullmytopics' => array('1', '0', '0'),
			'maxonlines' => array('500', '5000', '50000'),
			'pvfrequence' => array('30', '60', '100'),
			'qihoo_status' => array('0', '0', '1'),
			//'starthreshold' => array('2', '2', '2'),
			'searchctrl' => array('10', '30', '60'),
			'hottopic' => array('10', '20', '50'),
			'losslessdel' => array('365', '200', '100'),
			'maxmodworksmonths' => array('5', '3', '1'),
			'maxsearchresults' => array('200', '500', '1000'),
			'statscachelife' => array('90', '180', '360'),
			'moddisplay' => array('flat', 'flat', 'selectbox'),
			'topicperpage' => array('30', '20', '15'),
			'postperpage' => array('20', '15', '10'),
			'maxpolloptions' => array('10', '10', '15'),
			'maxpostsize' => array('10000', '10000', '20000'),
			'myrecorddays' => array('100', '60', '30'),
			'maxfavorites' => array('500', '200', '100'),
			'maxsubscriptions' => array('500', '200', '100'),

		);
		$safesettings = array(
			'attachrefcheck' => array('', '1', '1'),
			'bannedmessages' => array('', '1', '1'),
			'doublee' => array('1', '0', '0'),
			'dupkarmarate' => array('1', '0', '0'),
			'hideprivate' => array('0', '1', '1'),
			'memliststatus' => array('1', '1', '0'),
			'seccodestatus' => array('0', '1', '1'),
			'bbrules' => array('0', '1', '1'),
			'edittimelimit' => array('0', '20', '10'),
			'floodctrl' => array('0', '10', '30'),
			'karmaratelimit' => array('0', '1', '4'),
			'passport_status' => array('0', '0', '0'),
			'newbiespan' => array('', '1', '4'),
			'showemail' => array('0', '1', '1'),
			'maxchargespan' => array('0', '1', '2'),
			'regctrl' => array('0', '12', '48'),
			'regfloodctrl' => array('0', '100', '50'),
			'regstatus' => array('1', '1', '1'),
			'regverify' => array('0', '1', '2'),
		);
		$funcsettings = array(
			'archiverstatus' => array('0', '1', '1'),
			//'attachimgpost' => array('0', '1', '1'),
			'bdaystatus' => array('0', '0', '1'),
			'fastpost' => array('0', '1', '1'),
			'editedby' => array('0', '1', '1'),
			'forumjump' => array('0', '1', '1'),
			//'globalstick' => array('0', '0', '1'),
			'gzipcompress' => array('', '', '1'),
			//'loadctrl' => array('', '', ''),
			'newsletter' => array('', '', '1'),
			'modworkstatus' => array('0', '0', '1'),
			'reportpost' => array('0', '1', '1'),
			'rewritestatus' => array('0', '0', '0'),
			'rssstatus' => array('0', '1', '1'),
			'wapstatus' => array('0', '1', '1'),
			'maxbdays' => array('0', '100', '500'),
			'smileyinsert' => array('', '1', '1'),
			'smcols' => array('', '3', '3'),
			'statstatus' => array('0', '0', '1'),
			'stylejump' => array('0', '0', '1'),
			'subforumsindex' => array('0', '0', '1'),
			'transsidstatus' => array('0', '0', '1'),
			'visitedforums' => array('0', '10', '20'),
			'vtonlinestatus' => array('0', '1', '1'),
			'welcomemsg' => array('0', '0', '1'),
			'jsstatus' => array('0', '0', '1'),
			'watermarkstatus' => array('0', '0', '1'),
			'whosonlinestatus' => array('0', '1', '1'),
			'debug' => array('0', '1', '1'),
			'regadvance' => array('0', '0', '1'),
			'jsmenustatus' => array('0', '1', '15'),
			'showsettings' => array('0', '7', '7'),
			'editoroptions' => array('0', '1', '1'),
		);

		$safeforums = array(
			'modnewposts' => array('0', '0', '1'),
			'recyclebin' => array('0', '1', '1'),
			'jammer' => array('0', '0', '1'),
		);
		$funcforums = array(
			'allowsmilies' => array('0', '1', '1'),
			'allowbbcode' => array('0', '1', '1'),
			'allowimgcode' => array('0', '1', '1'),
			'allowanonymous' => array('0', '0', '1'),
			'allowpostspecial' => array('', '1', '127'),
			'disablewatermark' => array('1', '0', '0'),
			'threadcaches' => array('0', '0', '1'),
			'allowshare' => array('0', '1', '1'),
			);
		$sizeforums = array(
			'threadcaches' => array('0', '0', '1'),
		);

		$sqladd = $comma = '';

		foreach($sizesettings as $fieldname=>$val) {
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$fieldname', '{$val[$runwizardhistory[step1][size]]}')");
		}
		foreach($sizeforums as $fieldname=>$val) {
			$sqladd .= $comma."$fieldname='".$val[$runwizardhistory['step1']['size']]."'";
			$comma = ',';
		}

		foreach($safesettings as $fieldname=>$val) {
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$fieldname', '{$val[$runwizardhistory[step1][safe]]}')");
		}
		foreach($safeforums as $fieldname=>$val) {
			$sqladd .= $comma."$fieldname='".$val[$runwizardhistory['step1']['safe']]."'";
		}

		foreach($funcsettings as $fieldname=>$val) {
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$fieldname', '{$val[$runwizardhistory[step1][func]]}')");
		}
		foreach($funcforums as $fieldname=>$val) {
			$sqladd .= $comma."$fieldname='".$val[$runwizardhistory['step1']['func']]."'";
		}

		$db->query("UPDATE {$tablepre}forums SET $sqladd");

		$maxonlines = $sizesettings['maxonlines'][$runwizardhistory['step1']['size']];
		$db->query("ALTER TABLE {$tablepre}sessions MAX_ROWS=$maxonlines");

		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
			VALUES ('bbname', '{$runwizardhistory[step2][bbname]}')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
			VALUES ('sitename', '{$runwizardhistory[step2][sitename]}')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
			VALUES ('siteurl', '{$runwizardhistory[step2][siteurl]}')");

		updatecache('settings');

		foreach($runwizardhistory['step3']['cates'] as $id=>$catename) {
			$db->query("INSERT INTO {$tablepre}forums (type, name, status)
				VALUES ('group', '$catename', '1')");
			$fup = $fid = $db->insert_id();
			$db->query("INSERT INTO {$tablepre}forumfields (fid)
				VALUES ('$fid')");
			foreach($runwizardhistory['step3']['forums'][$id] as $forumname) {
				$db->query("INSERT INTO {$tablepre}forums (fup, type, name, status, allowsmilies, allowbbcode, allowimgcode, allowshare, allowpostspecial)
					VALUES ('$fup', 'forum', '$forumname', '1', '1', '1', '1', '1', '15')");
				$fid = $db->insert_id();
				$db->query("INSERT INTO {$tablepre}forumfields (fid)
					VALUES ('$fid')");
			}
		}

		updatecache('forums');

		$runwizardhistory['step3']['cates'] = array();
		$runwizardhistory['step3']['forums'] = array();
		saverunwizardhistory();

?>
<br /><center><b><?=$lang['runwizard_succeed']?></b><hr width="80%" noshade style="height:1px;"></center>
<br />
<ul>
<?=$lang['runwizard_modreasons']?><br /><br />
<ul>
<li><a href="admincp.php?action=settings&do=basic" onclick="if(parent.header)parent.header.togglemenu('basic')"><?=$lang['runwizard_particular']?></a></li>
<li><a href="admincp.php?action=forumadd" onclick="if(parent.header)parent.header.togglemenu('forums')"><?=$lang['menu_forums_add']?></a></li>
<li><a href="admincp.php?action=forumsedit" onclick="if(parent.header)parent.header.togglemenu('forums')"><?=$lang['menu_forums_edit']?></a></li>
<li><a href="admincp.php?action=fileperms" onclick="if(parent.header)parent.header.togglemenu('tools')"><?=$lang['menu_tools_fileperms']?></a></li>
</ul>
<br/><?=$lang['runwizard_database_backup']?><br /><br />
<ul>
<li><a href="admincp.php?action=export" onclick="if(parent.header)parent.header.togglemenu('tools')"><?=$lang['menu_database_export']?></a></li>
<li><a href="admincp.php?action=import" onclick="if(parent.header)parent.header.togglemenu('tools')"><?=$lang['menu_database_import']?></a></li>
</ul>
</ul><br /><br />
<?}?>
</td></tr></table></div>
<?

}

function saverunwizardhistory() {
	global $runwizardfile, $runwizardhistory;
	$fp = fopen($runwizardfile, 'w');
	$s = '<?php exit;?>';
	$s .= serialize($runwizardhistory);
	fwrite($fp, $s);
	fclose($fp);
}

?>