<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   usertitle.php - change member usertitle
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.<br /><i>NovaBoard Version: 0.8</i>";
	exit();
}

template_hook("pages/myoptions/usertitle.template.php", "start");

if ($can_change_user_title=='0'){

	nova_redirect("index.php?page=error&error=22","error/22");

}

elseif ($_POST['form']!=''){

$token_id = $_POST['token_id'];
$token_id = escape_string($token_id);

$token_name = "token_usertitle_$token_id";

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

$usertitle = (int) $_POST['usertitle'];

	/*
	Check length is not greater than that allowed
*/

	if (strlen($usertitle) > $usertitle_length)
	{
		$usertitle = substr($usertitle, 0, $usertitle_length);
	}

mysql_query("UPDATE {$db_prefix}members SET usertitle='$usertitle' WHERE id='$my_id'");

	template_hook("pages/myoptions/usertitle.template.php", "form");

	nova_redirect("index.php?page=myoptions&act=usertitle","myoptions/usertitle");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}
else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_usertitle_$token_id";

$_SESSION[$token_name] = $token;

$query2 = "select ID, USERTITLE from {$db_prefix}members WHERE ID='$my_id'" ;
$result2 = mysql_query($query2) or die("usertitle.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$id = $results2['ID'];
$usertitle = $results2['USERTITLE'];

$usertitle=strip_slashes($usertitle);

# Maximum characters available
$lang_user['usertitle_length'] = sprintf($lang_user['usertitle_length'], $usertitle_length);

template_hook("pages/myoptions/usertitle.template.php", "2");

}
}

template_hook("pages/myoptions/usertitle.template.php", "end");

?>