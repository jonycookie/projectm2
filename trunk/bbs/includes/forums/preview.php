<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   preview.php - preview post before submitting
*/

define("NOVA_RUN", 1);

# Stop annoying notices
error_reporting(E_ALL ^ E_NOTICE);

// set nova variables

$my_address="http://".$_SERVER['HTTP_HOST']."".$_SERVER['PHP_SELF'];

$nova_domain 	= str_replace('/includes/forums/preview.php', '', $my_address); 	// returns http://myforum.com/forum style address

include "../config.php";
include "../../scripts/php/functions.php";

$nova_name	=	escape_string($_COOKIE['nova_name']);

$subject = $_POST['subject'];
$subject = strip_slashes($subject);
$subject = stripslashes($subject);

if ($subject==''){
$subject="&nbsp;";
}

$content = $_POST['content'];

$content = str_replace("<br />", "&lt;br /&gt;", $content);

// Protect against XSS correctly
$_POST['content'] = htmlentities($content, ENT_QUOTES);

global $db_prefix;

$query2 = "select THEME, ALLOW_ATTACHMENTS, SEF_URLS, BOARD_LANG from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("header.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$allow_attachments = $results2['ALLOW_ATTACHMENTS'];
$sef_urls = $results2['SEF_URLS'];
$board_lang = $results2['BOARD_LANG'];
$theme = $results2['THEME'];
}

if (isset($_COOKIE['nova_theme'])){
$member_selected_theme=escape_string($_COOKIE['nova_theme']);
}

if (isset($nova_name)){

$query_theme = "select THEME, ROLE, BOARD_LANG from {$db_prefix}members WHERE NAME='$nova_name'" ;
$result_theme = mysql_query($query_theme) or die("structure.php - Error in query: $query_theme") ;                                  
while ($results_theme = mysql_fetch_array($result_theme)){
$member_selected_theme = $results_theme['THEME'];
$member_group = $results_theme['ROLE'];
$member_lang = $results_theme['BOARD_LANG'];
}

// check theme is available to use,,,,

$query_theme = "select THEME_NAME from {$db_prefix}themes WHERE THEME_NAME='$member_selected_theme'" ;
$result_theme = mysql_query($query_theme) or die("structure.php - Error in query: $query_theme") ;                                  
$check_theme = mysql_num_rows($result_theme);

if ($check_theme!='0' && $member_selected_theme!=''){
	$theme = $member_selected_theme;
}
}
else{
$member_group="4";
}

$can_use_html="0"; // set default

$query2 = "select CAN_USE_HTML from {$db_prefix}groups WHERE GROUP_ID='$member_group'" ;
$result2 = mysql_query($query2) or die("structure.php - Error in query: $query2"); 
while ($results2 = mysql_fetch_array($result2)){
$can_use_html = $results2['CAN_USE_HTML'];  
}                               

// select language to use...

		if (isset($member_lang) && $member_lang!=''){
			$board_lang="$member_lang";
		}
		
		if (isset($_COOKIE['nova_lang']) && (!isset($_COOKIE['nova_name']))){
			$board_lang = escape_string($_COOKIE['nova_lang']);
		}

include "../../lang/$board_lang/lang_forum.php";		
		
include "../../scripts/php/image_check.php";

if (file_exists("../../themes/$theme/scripts/php/parse.php")){
	include "../../themes/$theme/scripts/php/parse.php";
}
else{
	include "../../scripts/php/parse.php";				
}

			$template_hook="1";

		if (file_exists("../../themes/$theme/templates/includes/forums/preview.template.php")){
			$template_hook="1";
			include "../../themes/$theme/templates/includes/forums/preview.template.php";
		}
		else{
			include "../../templates/includes/forums/preview.template.php";
		}

?>