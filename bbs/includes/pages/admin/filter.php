<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   filter.php - word censor page
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/filter.template.php", "start");

if ($can_change_forum_settings=='0'){

	nova_redirect("index.php?page=error&error=11","error/11");

}

else{

if ($_GET['id']!=''){

$id	=	escape_string($_GET['id']);

mysql_query("DELETE FROM {$db_prefix}censor WHERE row ='$id'");

	template_hook("pages/admin/filter.template.php", "form_1");

	nova_redirect("index.php?page=admin&act=filter","admin/filter");

}


elseif ($_POST['old_word']!=''){

$token_id 	= escape_string($_POST['token_id']);
$token_name = "token_filter_$token_id";
$old_word	= escape_string($_POST['old_word']);
$new_word	= escape_string($_POST['new_word']);

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

mysql_query("INSERT INTO {$db_prefix}censor (word, new_word) VALUES ('$old_word', '$new_word')");

	template_hook("pages/admin/filter.template.php", "form_2");

	nova_redirect("index.php?page=admin&act=filter","admin/filter");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}



else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_filter_$token_id";

$_SESSION[$token_name] = $token;

template_hook("pages/admin/filter.template.php", "3");

$query2 = "select ROW, WORD, NEW_WORD from {$db_prefix}censor" ;
$result2 = mysql_query($query2) or die("filter.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$row = strip_slashes($results2['ROW']);
$word = strip_slashes($results2['WORD']);
$new_word = strip_slashes($results2['NEW_WORD']);

template_hook("pages/admin/filter.template.php", "4");

}

template_hook("pages/admin/filter.template.php", "5");

}

}

template_hook("pages/admin/filter.template.php", "end");
?>