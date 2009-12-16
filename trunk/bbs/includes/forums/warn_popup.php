<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   warn_popup.php - displays members warnings
*/

define("NOVA_RUN", 1);

include "../config.php";
include "../../scripts/php/functions.php";

// set nova variables

$member_id = escape_string($_GET['id']);

$my_address="http://".$_SERVER['HTTP_HOST']."".$_SERVER['PHP_SELF'];

$nova_domain 	= str_replace('/includes/forums/warn_popup.php', '', $my_address); 	// returns http://myforum.com/forum style address

global $db_prefix;

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

$warn_count_alt="";

$query2 = "select THEME, BOARD_LANG, SEF_URLS from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("structure.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$theme = $results2['THEME'];
$board_lang = $results2['BOARD_LANG'];
$sef_urls = $results2['SEF_URLS'];
}

if (isset($_COOKIE['nova_theme'])){
$member_selected_theme=escape_string($_COOKIE['nova_theme']);
}

if(isset($_COOKIE['nova_name'])){
$nova_name=escape_string($_COOKIE['nova_name']);

}

if(isset($_COOKIE['nova_password'])){
$password=escape_string($_COOKIE['nova_password']);
}

if (isset($nova_name)){

$query_theme = "select THEME, BOARD_LANG from {$db_prefix}members WHERE NAME='$nova_name' AND PASSWORD='$password'" ;
$result_theme = mysql_query($query_theme) or die("structure.php - Error in query: $query_theme") ;                                  
while ($results_theme = mysql_fetch_array($result_theme)){
$member_selected_theme = $results_theme['THEME'];
$member_lang = $results_theme['BOARD_LANG'];
}

}

// select language to use...

		if (isset($member_lang) && $member_lang!=''){
			$board_lang="$member_lang";
		}
		
		if (isset($_COOKIE['nova_lang']) && (!isset($_COOKIE['nova_name']))){
			$board_lang = escape_string($_COOKIE['nova_lang']);
		}

include "../../lang/$board_lang/lang_forum.php";

// check theme is available to use,,,,

$query_theme = "select THEME_NAME from {$db_prefix}themes WHERE THEME_NAME='$member_selected_theme'" ;
$result_theme = mysql_query($query_theme) or die("structure.php - Error in query: $query_theme") ;                                  
$check_theme = mysql_num_rows($result_theme);

if ($check_theme!='0' && $member_selected_theme!=''){
	$theme = $member_selected_theme;
}

$theme_strip=str_replace("%20"," ",$theme);


$template_hook="1";
if (file_exists("../../themes/$theme/templates/includes/forums/warn_popup.template.php")){
include "../../themes/$theme/templates/includes/forums/warn_popup.template.php";
}
else{
include "../../templates/includes/forums/warn_popup.template.php";
}


// Get warn details...

$query_warnings = "select NOTES, DATE, WARNED_BY, ACTION from {$db_prefix}warn WHERE MEMBER = '$member_id' ORDER BY DATE desc" ;
$result_warnings = mysql_query($query_warnings) or die("topic.php - Error in query: $query_warnings") ;                                  
while ($results_warnings = mysql_fetch_array($result_warnings)){
$warn_notes = $results_warnings['NOTES'];
$warn_date = $results_warnings['DATE'];
$warn_warned_by = $results_warnings['WARNED_BY'];
$warn_action = $results_warnings['ACTION'];

$warn_notes=strip_slashes($warn_notes);

$warn_date = format_date($warn_date, '%d %B %Y'); 

$query_warn_admin = "select NAME from {$db_prefix}members WHERE ID = '$warn_warned_by'" ;
$result_warn_admin = mysql_query($query_warn_admin) or die("topic.php - Error in query: $query_warn_admin") ;                                  
$warn_member_name = mysql_result($result_warn_admin, 0);

$warn_member_name = member_link($warn_warned_by, 0, 1);

$lang['topic_warn_add_details'] = str_replace("<%name>", "$warn_member_name", $lang['topic_warn_add_details']);
$lang['topic_warn_remove_details'] = str_replace("<%name>", "$warn_member_name", $lang['topic_warn_remove_details']);

$lang['topic_warn_add_details'] = str_replace("<%date>", "$warn_date", $lang['topic_warn_add_details']);
$lang['topic_warn_remove_details'] = str_replace("<%date>", "$warn_date", $lang['topic_warn_remove_details']);

	$warn_count_alt=$warn_count_alt+1;

	$check_odd = checkNum($warn_count_alt);

	if ($check_odd===TRUE){
		$alt_td_class="";
	}
	else{
		$alt_td_class="-alt";	
	}

$template_hook="2";
if (file_exists("../../themes/$theme/templates/includes/forums/warn_popup.template.php")){
include "../../themes/$theme/templates/includes/forums/warn_popup.template.php";
}
else{
include "../../templates/includes/forums/warn_popup.template.php";
}

}


$template_hook="3";
if (file_exists("../../themes/$theme/templates/includes/forums/warn_popup.template.php")){
include "../../themes/$theme/templates/includes/forums/warn_popup.template.php";
}
else{
include "../../templates/includes/forums/warn_popup.template.php";
}

?>
