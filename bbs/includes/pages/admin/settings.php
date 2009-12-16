<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================
|+--------------------------------------------------------------------------
|   settings.php - general settings for forum
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/settings.template.php", "start");

if ($can_change_site_settings=='0'){

	nova_redirect("index.php?page=error","error");
	
}

else{

if ($_POST['site_name']!=''){

$token_id = $_POST['token_id'];
$token_id = escape_string($token_id);

$token_name = "token_settings_$token_id";

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

$new_theme				= escape_string($_POST['theme']);
$site_name				= escape_string($_POST['site_name']);

	if ($_POST['site_desc']==''){
		$site_desc=$site_desc;
	}
	else{
	$site_desc				= escape_string($_POST['site_desc']);
	}

$max_guest_clicks		= escape_string($_POST['max_guest_clicks']);
$show_gamer_tags		= escape_string($_POST['show_gamer_tags']);
$max_warn				= escape_string($_POST['max_warn']);
$sef_urls				= escape_string($_POST['sef_urls']);
$time_offset			= escape_string($_POST['time_offset']);
$guest_register			= escape_string($_POST['guest_register']);
$register_bar			= escape_string($_POST['register_bar']);
$board_offline			= escape_string($_POST['board_offline']);
$board_offline_reason	= escape_string($_POST['board_offline_reason']);
$online_yesterday		= escape_string($_POST['online_yesterday']);
$rules					= escape_string($_POST['rules']);
$change_pass_time		= escape_string($_POST['change_pass_time']);
if ($change_pass_time == '0'){
	$change_pass_time = "1";
}
$home					= escape_string($_POST['home']);
$board_lang				= escape_string($_POST['board_lang']);
$board_email			= escape_string($_POST['board_email']);
$username_length		= ($_POST['username_length'] < 3) ? 3 : (int) $_POST['username_length'];
$usertitle_length		= (int) $_POST['usertitle_length'];

mysql_query("UPDATE {$db_prefix}settings SET site_name='$site_name', site_desc='$site_desc', max_guest_clicks='$max_guest_clicks', show_gamer_tags='$show_gamer_tags', max_warn='$max_warn', theme='$new_theme', sef_urls='$sef_urls', time_offset='$time_offset', guest_register='$guest_register', register_bar='$register_bar', board_offline='$board_offline', board_offline_reason='$board_offline_reason', online_yesterday='$online_yesterday', rules='$rules', change_pass_time='$change_pass_time', home='$home', board_lang='$board_lang', board_email='$board_email', username_length = " . $username_length . ", usertitle_length = " . $usertitle_length);

if ($_POST['sef_urls']=='1'){

htaccess_create();

}

else{

foreach (glob(".htaccess") as $filename) {
   unlink($filename);
}

}

if ($_POST['force_theme']=='1'){
mysql_query("UPDATE {$db_prefix}members SET theme=''");
}

// to be safe, make sef_url off and return...

$sef_urls="0";

	template_hook("pages/admin/settings.template.php", "form");

	nova_redirect("index.php?page=admin&act=settings","admin/settings");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}
else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_settings_$token_id";

$_SESSION[$token_name] = $token;

$query2 = "select SITE_NAME, SITE_DESC, REGISTER_BAR, TIME_OFFSET, MAX_GUEST_CLICKS, SHOW_GAMER_TAGS, MAX_WARN, SEF_URLS, GUEST_REGISTER, BOARD_OFFLINE, BOARD_OFFLINE_REASON, ONLINE_YESTERDAY, RULES, CHANGE_PASS_TIME, HOME, BOARD_LANG, THEME, BOARD_EMAIL from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("settings.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$site_name = strip_slashes($results2['SITE_NAME']);
$site_desc = strip_slashes($results2['SITE_DESC']);
$time_offset = $results2['TIME_OFFSET'];
$max_guest_clicks = $results2['MAX_GUEST_CLICKS'];
$show_gamer_tags = $results2['SHOW_GAMER_TAGS'];
$max_warn = $results2['MAX_WARN'];
$current_theme = strip_slashes($results2['THEME']);
$sef_urls = $results2['SEF_URLS'];
$guest_register = $results2['GUEST_REGISTER'];
$board_offline = $results2['BOARD_OFFLINE'];
$board_offline_reason = strip_slashes($results2['BOARD_OFFLINE_REASON']);
$online_yesterday = $results2['ONLINE_YESTERDAY'];
$rules = strip_slashes($results2['RULES']);
$change_pass_time = $results2['CHANGE_PASS_TIME'];
$home = strip_slashes($results2['HOME']);
$board_lang = strip_slashes($results2['BOARD_LANG']);
$default_board_email = strip_slashes($results2['BOARD_EMAIL']);
$register_bar = $results2['REGISTER_BAR'];

$query21 = "select DISPLAY_NAME from {$db_prefix}themes WHERE THEME_NAME='$current_theme'" ;
$result21 = mysql_query($query21) or die("style.php - Error in query: $query21") ;                                  
$current_theme_name = strip_slashes(mysql_result($result21, 0));

						// explode into 2 parts {lang}_{flag} then put into options for selections

							$board_lang_option = explode("_", $board_lang);

						// Capital letters please...

							$board_lang_name = ucfirst($board_lang_option[0]);

						
template_hook("pages/admin/settings.template.php", "2");

$start_time = time() - 43200;
$start_time_value = "-12";

while($start_time_value!="14"){

$time_offset="0";
$formatted_start_time = format_date($start_time, '%A, %d %b %Y (%H:%M)');

template_hook("pages/admin/settings.template.php", "time");

$start_time = $start_time + 1800;
$start_time_value = $start_time_value + 0.5;

}

template_hook("pages/admin/settings.template.php", "aftertime");

list_themes("themes/");

template_hook("pages/admin/settings.template.php", "4");

}
}

}

template_hook("pages/admin/settings.template.php", "end");
?>