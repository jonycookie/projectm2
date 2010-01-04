<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: menu.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Discuz! Administrator's Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$charset?>">
<link rel="stylesheet" type="text/css" id="css" href="./images/admincp/admincp.css">
<script src="include/javascript/common.js" type="text/javascript"></script>
<script src="include/javascript/iframe.js" type="text/javascript"></script>
<script>
var collapsed = getcookie('<?=$tablepre?>collapse');
function collapse_change(menucount) {
	if($('menu_' + menucount).style.display == 'none') {
		$('menu_' + menucount).style.display = '';collapsed = collapsed.replace('[' + menucount + ']' , '');
		$('menuimg_' + menucount).src = './images/admincp/menu_reduce.gif';
	} else {
		$('menu_' + menucount).style.display = 'none';collapsed += '[' + menucount + ']';
		$('menuimg_' + menucount).src = './images/admincp/menu_add.gif';
	}
	setcookie('<?=$tablepre?>collapse', collapsed, 2592000);
}
</script>
</head>

<body style="margin:5px!important;margin:3px;">

<?

		$menu = !empty($menu) ? $menu : 'home';
		$collapse = isset($_DCOOKIE['collapse']) ? $_DCOOKIE['collapse'] : '';
		$menucount = 0;
		if($adminid == 1) {
			echo '<table width="146" border="0" cellspacing="0" align="center" cellpadding="0" class="leftmenulist" style="margin-bottom: 5px;">'.
				'<tr class="leftmenutext"><td><div align="center"><a href="'.$indexname.'" target="_blank">'.$lang['header_home'].'</a>&nbsp;&nbsp;<a href="#"  onClick="parent.menu.location=\'admincp.php?action=menu\'; parent.main.location=\'admincp.php?action=home\';return false;">'.$lang['header_admin'].'</a></div></td></tr>'.
				'</table>';
			echo '<div id="home">';
			showmenu($lang['menu_general'],	array(array('name' => $lang['menu_forums_edit'], 'url' => 'admincp.php?action=forumsedit'),
							array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=members'),
							array('name' => $lang['menu_members_edit_ban_user'], 'url' => 'admincp.php?action=banmember'),
							array('name' => $lang['menu_misc_announces'], 'url' => 'admincp.php?action=announcements'),
							array('name' => $lang['menu_maint_threads'], 'url' => 'admincp.php?action=threads'),
							array('name' => $lang['menu_maint_prune'], 'url' => 'admincp.php?action=prune'),
							array('name' => $lang['menu_moderate_modthreads'], 'url' => 'admincp.php?action=modthreads'),
							array('name' => $lang['menu_moderate_modreplies'], 'url' => 'admincp.php?action=modreplies'),
							$isfounder ? array('name' => $lang['menu_runwizard'], 'url' => 'admincp.php?action=runwizard') : array()));

			showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_login'], 'url' => 'admincp.php?action=illegallog'),
							array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=ratelog'),
							array('name' => $lang['menu_logs_credit'], 'url' => 'admincp.php?action=creditslog'),
							array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog'),
							array('name' => $lang['menu_logs_medal'], 'url' => 'admincp.php?action=medalslog'),
							array('name' => $lang['menu_logs_ban'], 'url' => 'admincp.php?action=banlog'),
							array('name' => $lang['menu_logs_admincp'], 'url' => 'admincp.php?action=cplog'),
							array('name' => $lang['menu_logs_magic'], 'url' => 'admincp.php?action=magiclog'),
							array('name' => $lang['menu_logs_invite'], 'url' => 'admincp.php?action=invitelog'),
							array('name' => $lang['menu_logs_error'], 'url' => 'admincp.php?action=errorlog')));
			echo '</div><div id="basic" style="display: none">';
			showmenu($lang['menu_settings'],array(array('name' => $lang['settings_general'], 'url' => 'admincp.php?action=settings&do=basic'),
							array('name' => $lang['settings_access'], 'url' => 'admincp.php?action=settings&do=access'),
							array('name' => $lang['settings_styles'], 'url' => 'admincp.php?action=settings&do=styles'),
							array('name' => $lang['settings_seo'], 'url' => 'admincp.php?action=settings&do=seo'),
							array('name' => $lang['settings_cachethread'], 'url' => 'admincp.php?action=settings&do=cachethread'),
							array('name' => $lang['settings_functions'], 'url' => 'admincp.php?action=settings&do=functions'),
							array('name' => $lang['settings_credits'], 'url' => 'admincp.php?action=settings&do=credits'),
							array('name' => $lang['settings_serveropti'], 'url' => 'admincp.php?action=settings&do=serveropti'),
							$isfounder ? array('name' => $lang['settings_mail'], 'url' => 'admincp.php?action=settings&do=mail') : array(),
							array('name' => $lang['settings_seccode'], 'url' => 'admincp.php?action=settings&do=seccode'),
							array('name' => $lang['settings_secqaa'], 'url' => 'admincp.php?action=settings&do=secqaa'),
							array('name' => $lang['settings_datetime'], 'url' => 'admincp.php?action=settings&do=datetime'),
							array('name' => $lang['settings_permissions'], 'url' => 'admincp.php?action=settings&do=permissions'),
							array('name' => $lang['settings_attachments'], 'url' => 'admincp.php?action=settings&do=attachments'),
							array('name' => $lang['settings_wap'], 'url' => 'admincp.php?action=settings&do=wap'),

							array('name' => $lang['settings_space'], 'url' => 'admincp.php?action=settings&do=space')));
			echo '</div><div id="forums" style="display: none">';
			showmenu($lang['menu_forums'],	array(array('name' => $lang['menu_forums_add'], 'url' => 'admincp.php?action=forumadd'),
							array('name' => $lang['menu_forums_edit'], 'url' => 'admincp.php?action=forumsedit'),
							array('name' => $lang['menu_forums_merge'], 'url' => 'admincp.php?action=forumsmerge'),
							array('name' => $lang['menu_forums_threadtypes'], 'url' => 'admincp.php?action=threadtypes')));
			showmenu($lang['menu_forums_types'], array(array('name' => $lang['menu_forums_infotypes'], 'url' => 'admincp.php?action=threadtypes&amp;special=1'),
							array('name' => $lang['menu_forums_infomodel'], 'url' => 'admincp.php?action=typemodel'),
							array('name' => $lang['menu_forums_infooption'], 'url' => 'admincp.php?action=typeoption')));
			if($isfounder) {
				showmenu($lang['menu_styles'],	array(array('name' => $lang['menu_styles'], 'url' => 'admincp.php?action=styles'),
								array('name' => $lang['menu_styles_templates'], 'url' => 'admincp.php?action=templates')));
			} else {
				showmenu($lang['menu_styles'],	array(array('name' => $lang['menu_styles'], 'url' => 'admincp.php?action=styles')));
			}
			echo '</div><div id="users" style="display: none">';
			showmenu($lang['menu_members'], array(array('name' => $lang['menu_members_add'], 'url' => 'admincp.php?action=memberadd'),
							array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=members'),
							array('name' => $lang['menu_members_edit_ban_user'], 'url' => 'admincp.php?action=banmember'),
							array('name' => $lang['menu_members_merge'], 'url' => 'admincp.php?action=membersmerge'),
							array('name' => $lang['menu_members_ipban'], 'url' => 'admincp.php?action=ipban'),
							array('name' => $lang['menu_members_credits'], 'url' => 'admincp.php?action=members&submitname=creditsubmit'),
							array('name' => $lang['menu_moderate_modmembers'], 'url' => 'admincp.php?action=modmembers'),
							array('name' => $lang['menu_members_profile_fields'], 'url' => 'admincp.php?action=profilefields')));
			showmenu($lang['menu_groups'],	array(array('name' => $lang['menu_admingroups'], 'url' => 'admincp.php?action=admingroups'),
							array('name' => $lang['menu_usergroups'], 'url' => 'admincp.php?action=usergroups'),
							array('name' => $lang['menu_ranks'], 'url' => 'admincp.php?action=ranks')));
			echo '</div><div id="posts" style="display: none">';
			showmenu($lang['menu_moderate'],array(array('name' => $lang['menu_moderate_modthreads'], 'url' => 'admincp.php?action=modthreads'),
							array('name' => $lang['menu_moderate_modreplies'], 'url' => 'admincp.php?action=modreplies')));
			showmenu($lang['menu_maint'],	array(array('name' => $lang['menu_maint_threads'], 'url' => 'admincp.php?action=threads'),
							array('name' => $lang['menu_maint_prune'], 'url' => 'admincp.php?action=prune'),
							array('name' => $lang['menu_maint_attaches'], 'url' => 'admincp.php?action=attachments'),
							array('name' => $lang['menu_recommend'], 'url' => 'admincp.php?action=forumrecommend')));
			showmenu($lang['menu_posting'],	array(array('name' => $lang['menu_posting_discuzcodes'], 'url' => 'admincp.php?action=discuzcodes'),
							array('name' => $lang['menu_posting_tags'], 'url' => 'admincp.php?action=tags'),
							array('name' => $lang['menu_posting_censors'], 'url' => 'admincp.php?action=censor'),
							array('name' => $lang['menu_posting_smilies'], 'url' => 'admincp.php?action=smilies'),
							array('name' => $lang['menu_thread_icon'], 'url' => 'admincp.php?action=icons'),
							array('name' => $lang['menu_posting_attachtypes'], 'url' => 'admincp.php?action=attachtypes'),
							array('name' => $lang['menu_moderate_recyclebin'], 'url' => 'admincp.php?action=recyclebin')));
			echo '</div><div id="api" style="display: none">';
			showmenu($lang['menu_plugins'],	array(array('name' => $lang['menu_plugins_edit'], 'url' => 'admincp.php?action=plugins'),
							array('name' => $lang['menu_plugins_config'], 'url' => 'admincp.php?action=pluginsconfig')));
			showmenu($lang['menu_supesite'],array(array('name' => $lang['supe_settings'], 'url' => 'admincp.php?action=xspace')));
			showmenu($lang['menu_passport'],array(array('name' => $lang['menu_passport_settings'], 'url' => 'admincp.php?action=passport'),
							array('name' => $lang['menu_passport_shopex'], 'url' => 'admincp.php?action=shopex')));
			showmenu($lang['menu_google'], array(array('name' => $lang['menu_google_config'], 'url' => 'admincp.php?action=google_config')));
			showmenu($lang['menu_qihoo'], array(array('name' => $lang['menu_qihoo_config'], 'url' => 'admincp.php?action=qihoo_config'),
							array('name' => $lang['menu_qihoo_topics'], 'url' => 'admincp.php?action=qihoo_topics'),
							array('name' => $lang['menu_qihoo_relatedthreads'], 'url' => 'admincp.php?action=qihoo_relatedthreads')));
			showmenu($lang['menu_video'],array(array('name' => $lang['menu_video_config'], 'url' => 'admincp.php?action=videoconfig'),
							array('name' => $lang['menu_video_bind'], 'url' => 'admincp.php?action=videobind'),
							array('name' => $lang['menu_video_class'], 'url' => 'admincp.php?action=videoclass')));
			showmenu($lang['menu_ecommerce'], array(
							array('name' => $lang['settings_general'], 'url' => 'admincp.php?action=settings&do=ecommerce'),
							array('name' => $lang['menu_ecommerce_alipay'], 'url' => 'admincp.php?action=alipay'),
							array('name' => $lang['menu_ecommerce_credit'], 'url' => 'admincp.php?action=ec_credit'),
							array('name' => $lang['menu_ecommerce_credit_orders'], 'url' => 'admincp.php?action=orders'),
							array('name' => $lang['menu_ecommerce_trade_orders'], 'url' => 'admincp.php?action=tradelog')));
			echo '</div><div id="others" style="display: none">';
			showmenu($lang['menu_magics'],	array(array('name' => $lang['menu_magics_config'], 'url' => 'admincp.php?action=magic_config'),
							array('name' => $lang['menu_magics_edit'], 'url' => 'admincp.php?action=magic'),
							array('name' => $lang['menu_magics_market'], 'url' => 'admincp.php?action=magicmarket')));
			showmenu($lang['menu_misc'], 	array(array('name' => $lang['menu_misc_announces'], 'url' => 'admincp.php?action=announcements'),
							array('name' => $lang['menu_misc_medals'], 'url' => 'admincp.php?action=medals'),
							array('name' => $lang['menu_misc_advertisements'], 'url' => 'admincp.php?action=adv'),
							array('name' => $lang['menu_misc_links'], 'url' => 'admincp.php?action=forumlinks'),
							array('name' => $lang['menu_misc_crons'], 'url' => 'admincp.php?action=crons'),
							array('name' => $lang['menu_misc_help'], 'url' => 'admincp.php?action=faqlist'),
							array('name' => $lang['menu_misc_onlinelist'], 'url' => 'admincp.php?action=onlinelist')));
			echo '</div><div id="tools" style="display: none">';
			showmenu($lang['menu_tools'],	array(array('name' => $lang['menu_members_newsletter'], 'url' => 'admincp.php?action=members&submitname=newslettersubmit'),
							array('name' => $lang['menu_tools_updatecaches'], 'url' => 'admincp.php?action=updatecache'),
							array('name' => $lang['menu_tools_updatecounters'], 'url' => 'admincp.php?action=counter'),
							array('name' => $lang['menu_tools_javascript'], 'url' => 'admincp.php?action=jswizard'),
							array('name' => $lang['menu_tools_creditwizard'], 'url' => 'admincp.php?action=creditwizard'),
							array('name' => $lang['menu_tools_fileperms'], 'url' => 'admincp.php?action=fileperms'),
							array('name' => $lang['menu_tools_filecheck'], 'url' => 'admincp.php?action=filecheck'),
							array('name' => $lang['menu_maint_pmprune'], 'url' => 'admincp.php?action=pmprune'),
							array('name' => $lang['menu_forum_scheme'], 'url' => 'admincp.php?action=project')
							));

			if($isfounder) {
				showmenu($lang['menu_database'],array(array('name' => $lang['menu_database_export'], 'url' => 'admincp.php?action=export'),
								checkpermission('dbimport', 0) ? array('name' => $lang['menu_database_import'], 'url' => 'admincp.php?action=import') : array(),
								array('name' => $lang['menu_database_query'], 'url' => 'admincp.php?action=runquery'),
								array('name' => $lang['menu_database_optimize'], 'url' => 'admincp.php?action=optimize'),
								array('name' => $lang['menu_tools_dbcheck'], 'url' => 'admincp.php?action=dbcheck')));
			}

			showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_login'], 'url' => 'admincp.php?action=illegallog'),
							array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=ratelog'),
							array('name' => $lang['menu_logs_credit'], 'url' => 'admincp.php?action=creditslog'),
							array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog'),
							array('name' => $lang['menu_logs_medal'], 'url' => 'admincp.php?action=medalslog'),
							array('name' => $lang['menu_logs_ban'], 'url' => 'admincp.php?action=banlog'),
							array('name' => $lang['menu_logs_admincp'], 'url' => 'admincp.php?action=cplog'),
							array('name' => $lang['menu_logs_magic'], 'url' => 'admincp.php?action=magiclog'),
							array('name' => $lang['menu_logs_invite'], 'url' => 'admincp.php?action=invitelog'),
							array('name' => $lang['menu_logs_error'], 'url' => 'admincp.php?action=errorlog')));
			echo '</div>';
			echo '</div><div id="insenz" style="display: none">';
			$insenz = ($insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'")) ? unserialize($insenz) : array();
			showmenu($lang['menu_insenz_settings'], array(array('name' => $lang['menu_insenz_settings_basic'], 'url' => 'admincp.php?action=insenz&operation=settings&do=basic'),
							array('name' => $lang['menu_insenz_settings_softad'], 'url' => 'admincp.php?action=insenz&operation=settings&do=softad'),
							array('name' => $lang['menu_insenz_settings_hardad'], 'url' => 'admincp.php?action=insenz&operation=settings&do=hardad'),
							$insenz['topicrelatedad'] || $insenz['traderelatedad'] ? array('name' => $lang['menu_insenz_settings_relatedad'], 'url' => 'admincp.php?action=insenz&operation=settings&do=relatedad') : '',
							$insenz['topicstatus'] ? array('name' => $lang['menu_insenz_settings_virtualforum'], 'url' => 'admincp.php?action=insenz&operation=settings&do=virtualforum') : ''));
			showmenu($lang['menu_insenz_softad'], array(array('name' => $lang['menu_insenz_campaigns_new'], 'url' => 'admincp.php?action=insenz&operation=campaignlist&c_status=2'),
							array('name' => $lang['menu_insenz_campaigns_accepted'], 'url' => 'admincp.php?action=insenz&operation=campaignlist&c_status=6'),
							array('name' => $lang['menu_insenz_campaigns_finished'], 'url' => 'admincp.php?action=insenz&operation=campaignlist&c_status=7')));
			$insenz['topicstatus'] && showmenu($lang['menu_insenz_virtualforum'], array(array('name' => $lang['menu_insenz_campaigns_new'], 'url' => 'admincp.php?action=insenz&operation=virtualforum&do=&c_status=2'),
							array('name' => $lang['menu_insenz_campaigns_accepted'], 'url' => 'admincp.php?action=insenz&operation=virtualforum&do=&c_status=6'),
							array('name' => $lang['menu_insenz_campaigns_finished'], 'url' => 'admincp.php?action=insenz&operation=virtualforum&do=&c_status=7')));
			showmenu($lang['menu_insenz_tools'], array(array('name' => $lang['menu_insenz_tools_myinsenz'], 'url' => 'http://www.insenz.com/publishers/', 'target' => '_blank'),
							array('name' => $lang['menu_insenz_tools_faq'], 'url' => 'http://www.insenz.com/publishers/faq/', 'target' => '_blank')));
			echo '</div>';
			echo '<table width="146" border="0" cellspacing="0" align="center" cellpadding="0" class="leftmenulist">'.
				'<tr class="leftmenutext"><td><div style="margin-left:48px;"><a href="admincp.php?action=logout" target="_top">'.$lang['menu_logout'].'</a></td></tr>'.
				'</table>';
		} else {
			//showmenu($lang['menu_home'],	'admincp.php?action=home');
			$menuarray = array();
			$menuarray[] = array('name' => $lang['menu_forums_rules'], 'url' => 'admincp.php?action=forumrules');
			if($allowedituser || $allowbanuser || $allowbanip || $allowpostannounce || $allowcensorword || $allowmassprune) {
				if($allowedituser) {
					$menuarray[] = array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=editmember');
				}
				if($allowbanuser) {
					$menuarray[] = array('name' => $lang['menu_members_edit_ban_user'], 'url' => 'admincp.php?action=banmember');
				}
				if($allowbanip) {
					$menuarray[] = array('name' => $lang['menu_members_ipban'], 'url' => 'admincp.php?action=ipban');
				}
				if($allowpostannounce) {
					$menuarray[] = array('name' => $lang['menu_misc_announces'], 'url' => 'admincp.php?action=announcements');
				}
				if($allowcensorword) {
					$menuarray[] = array('name' => $lang['menu_posting_censors'], 'url' => 'admincp.php?action=censor');
				}
				if($allowmassprune) {
					$menuarray[] = array('name' => $lang['menu_maint_prune'], 'url' => 'admincp.php?action=prune');
				}
			}
			$menuarray[] = array('name' => $lang['menu_recommend'], 'url' => 'admincp.php?action=forumrecommend');
			showmenu($lang['menu_moderation'], $menuarray);
			unset($menuarray);

			if($allowmoduser || $allowmodpost) {
				$menuarray = array();
				if($allowmoduser) {
					$menuarray[] = array('name' => $lang['menu_moderate_modmembers'], 'url' => 'admincp.php?action=modmembers');
				}
				if($allowmodpost) {
					$menuarray[] = array('name' => $lang['menu_moderate_modthreads'], 'url' => 'admincp.php?action=modthreads');
					$menuarray[] = array('name' => $lang['menu_moderate_modreplies'], 'url' => 'admincp.php?action=modreplies');
				}
				showmenu($lang['menu_moderate'], $menuarray);
				unset($menuarray);
			}

			showmenu($lang['menu_plugins'],	array(array('name' => $lang['menu_plugins'], 'url' => 'admincp.php?action=plugins')));

			if($allowviewlog) {
				showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=ratelog'),
								array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog'),
								array('name' => $lang['menu_logs_ban'], 'url' => 'admincp.php?action=banlog')));
			}

		}

?>

</body>
</html>