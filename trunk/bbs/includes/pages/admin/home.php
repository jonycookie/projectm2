<?php

/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   home.php - admin home page
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/home.template.php", "start");

if ($_POST['form']!=''){

$token_id = escape_string($_POST['token_id']);

$token_name = "token_home_$token_id";

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

$content=escape_string($_POST['content']);

// Update it...
mysql_query("UPDATE {$db_prefix}settings SET whiteboard='$content'");

template_hook("pages/admin/home.template.php", "form");

	nova_redirect("index.php?page=admin","admin");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}
else{

// show quick info on forum settings
$query2 = "select BOARD_OFFLINE, GUEST_REGISTER, ALLOW_ATTACHMENTS, AKISMET_KEY from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("home.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$check_board_offline = strip_slashes($results2['BOARD_OFFLINE']);
$check_guest_register = strip_slashes($results2['GUEST_REGISTER']);
$check_allow_attachments = strip_slashes($results2['ALLOW_ATTACHMENTS']);
$akismet_key = strip_slashes($results2['AKISMET_KEY']);
}

		
			if ($akismet_key==''){
				$spam_warn = "1";
			}
			else{
				$spam_warn="0";
			}
if ($recaptcha_public=='' && $recaptcha_private==''){
$recaptcha_warning="1";
}
else{
$recaptcha_warning="0";
}
template_hook("pages/admin/home.template.php", "1");

if ($check_board_offline=='1'){
template_hook("pages/admin/home.template.php", "3");
}
else{
template_hook("pages/admin/home.template.php", "4");
}
if ($check_guest_register=='0'){
template_hook("pages/admin/home.template.php", "5");
}
else{
template_hook("pages/admin/home.template.php", "6");
}
if ($check_allow_attachments=='0'){
template_hook("pages/admin/home.template.php", "8");
}
else{
template_hook("pages/admin/home.template.php", "9");
}
if ($spam_warn=='1' && $recaptcha_warning=='1'){
template_hook("pages/admin/home.template.php", "10");
}
elseif($spam_warn=='0' && $recaptcha_warning=='0'){
template_hook("pages/admin/home.template.php", "11");
}
else{
template_hook("pages/admin/home.template.php", "12");
}

template_hook("pages/admin/home.template.php", "7");



$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_home_$token_id";

$_SESSION[$token_name] = $token;

$query2 = "select WHITEBOARD from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("home.php - Error in query: $query2") ;                                  
$content = strip_slashes(mysql_result($result2, 0));

$content=str_replace("<br />","",$content);

template_hook("pages/admin/home.template.php", "2");

}

template_hook("pages/admin/home.template.php", "end");

?>