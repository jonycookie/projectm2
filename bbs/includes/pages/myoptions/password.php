<?php

/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   password.php - change member password
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/myoptions/password.template.php", "start");

$query1 = "select ID, PASSWORD, PASSWORD_TIME, PASS_SALT from {$db_prefix}members WHERE ID='$my_id'" ;
$result1 = mysql_query($query1) or die("username.php - Error in query: $query1") ;                                 
while ($results1 = mysql_fetch_array($result1)){
$current = $results1['PASSWORD'];
$password_time = $results1['PASSWORD_TIME'];
$pass_salt = $results1['PASS_SALT'];
}

$new_pass_time=time();


if ($_POST['password']!=''){

$token_id = $_POST['token_id'];
$token_id = escape_string($token_id);
$token_name = "token_password_$token_id";

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

$pass= md5($_POST['pass'] . $pass_salt); // current password

// Generate salt...
$salt = substr(md5(uniqid(rand(), true)), 0, 9);

$password= md5($_POST['password'] . $salt);
$check_password= md5($_POST['password'] . $pass_salt); // entered NEW password with database salt

if($check_password == $current){ // first check if posted NEW password = current password.

template_hook("pages/myoptions/password.template.php", "1");

}
elseif($pass != $current){
	nova_redirect("index.php?page=error&error=38","error/38");
}
else{ // second, check if posted current password does not equal current password.

mysql_query("UPDATE {$db_prefix}members SET password='$password', password_time='$new_pass_time', pass_salt='$salt' WHERE id='$my_id'");


$query2 = "select NAME, EMAIL from {$db_prefix}members WHERE ID='$my_id'" ;
$result2 = mysql_query($query2) or die("password.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$name = $results2['NAME'];
$email = $results2['EMAIL'];
}

	$lang['email_members_pass_title'] = str_replace("<%sitename>", $site_name, $lang['email_members_pass_title']);
	
	$lang['email_members_pass_content'] = str_replace("<%subscriber>", $name, $lang['email_members_pass_content']);
	$lang['email_members_pass_content'] = str_replace("<%sitename>", $site_name, $lang['email_members_pass_content']);
	$lang['email_members_pass_content'] = str_replace("<%password>", $_POST['password'], $lang['email_members_pass_content']);
	$lang['email_members_pass_content'] = str_replace("<%site>", $nova_domain, $lang['email_members_pass_content']);

$message=$lang['email_members_pass_content'];
$outgoing="$email";
$from="From: $site_name <$board_email>\r\n";
$subject=$lang['email_members_pass_title'];
mail($outgoing, $subject, $message, $from);

	template_hook("pages/myoptions/password.template.php", "form");

	nova_redirect("index.php?page=logout","logout");

}
}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}
else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_password_$token_id";

$_SESSION[$token_name] = $token;

$current_password_time= $password_time + ((60*60*24)*$change_pass_time);

template_hook("pages/myoptions/password.template.php", "3");

}

template_hook("pages/myoptions/password.template.php", "end");

?>