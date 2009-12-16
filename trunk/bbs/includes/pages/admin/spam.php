<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   spam.php - admin options for spam-flagged posts
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

require_once "scripts/php/captcha/recaptchalib.php";

$recaptcha_site = str_replace("http://", "", $nova_domain);

$recaptcha_address = recaptcha_get_signup_url($recaptcha_site, "NovaBoard");

$lang_admin['spam_recaptcha_desc'] = str_replace("%recaptcha_website%", $recaptcha_address, $lang_admin['spam_recaptcha_desc']);

// Your WordPress API key
$GLOBALS["akismet_key"]		= $akismet_key;

// The name of the blog you're protecting
$GLOBALS["akismet_home"]	= $nova_domain;

// Your User-Agent string
$GLOBALS["akismet_ua"]		= "NovaBoard/1.0";

/**
 * Advanced settings below, only change these if you know what you're doing
 */

// The Akismet hostname
$GLOBALS["akismet_host"]	= "rest.akismet.com";

// Base URL to append to host and prepend to all queries
$GLOBALS["akismet_url"]		= "1.1";

		
		include "scripts/php/akismet.php";
		
		if (_akismet_login() === false)
		{	
			$invalid_key = true;
			
			mysql_query("UPDATE {$db_prefix}settings SET akismet_key=''");
		}
		else
		{
			$invalid_key = false;
		}

template_hook("pages/admin/spam.template.php", "start");

if ($can_change_site_settings=='0'){

	nova_redirect("index.php?page=error","error");

}

if ($_POST['form']!='' && !$invalid_key)
{
	$akismet_key		= escape_string($_POST['akismet_key']);
	$recaptcha_private	= escape_string($_POST['recaptcha_private']);
	$recaptcha_public	= escape_string($_POST['recaptcha_public']);

	if (tokenCheck('spam', $askismet_key))
	{
		mysql_query('
			UPDATE
				' . $db_prefix . 'settings
			SET
				akismet_key = "' . $akismet_key . '",
				recaptcha_public = "'. $recaptcha_public . '",
				recaptcha_private = "' . $recaptcha_private . '"
		');

		nova_redirect('index.php?page=admin&act=spam', 'admin/spam');
	}
	else
	{
		nova_redirect('index.php?page=error&error=28', 'error/28');
	}
}
else{
     
			$query2 = "select akismet_key from {$db_prefix}settings";
			$result2 = mysql_query($query2) or die("header.php - Error in query: $query2") ; 
			$akismet_key = strip_slashes(mysql_result($result2, 0));

	
template_hook("pages/admin/spam.template.php", "1");

if ($akismet_key==''){
	template_hook("pages/admin/spam.template.php", "2");
}
else{
	template_hook("pages/admin/spam.template.php", "3");
}

list($token_id, $token, $token_name) = tokenCreate('spam', $akismet_key);

template_hook("pages/admin/spam.template.php", "4");
}

template_hook("pages/admin/spam.template.php", "end");
?>