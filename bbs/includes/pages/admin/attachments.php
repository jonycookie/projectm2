<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   attachments.php - set attachments options
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/attachments.template.php", "start");

if ($can_change_site_settings=='0'){

	nova_redirect("index.php?page=error&error=11","error/11");

}

else{

if ($_POST['post_form']!=''){

$token_id 				= escape_string($_POST['token_id']);
$token_name 			= "token_attachments_$token_id";
$allow_attachments		= escape_string($_POST['allow_attachments']);
$attach_img_size		= escape_string($_POST['attach_img_size']);
$attach_avatar_size	= escape_string($_POST['attach_avatar_size']);

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

mysql_query("UPDATE {$db_prefix}settings SET allow_attachments='$allow_attachments', attach_img_size='$attach_img_size', attach_avatar_size='$attach_avatar_size'");

	template_hook("pages/admin/attachments.template.php", "form");

	nova_redirect("index.php?page=admin&act=attachments","admin/attachments");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}
else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_attachments_$token_id";

$_SESSION[$token_name] = $token;

$query2 = "select ALLOW_ATTACHMENTS from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("attachments.php - Error in query: $query2") ;                                  
$allow_attachments = strip_slashes(mysql_result($result2, 0));

template_hook("pages/admin/attachments.template.php", "2");

}
}

template_hook("pages/admin/attachments.template.php", "end");

?>