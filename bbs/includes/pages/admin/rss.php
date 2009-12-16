<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   rss.php - set options for RSS feeds
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/rss.template.php", "start");

if ($can_change_forum_settings=='0'){

	nova_redirect("index.php?page=error","error");

}

else{

if ($_POST['form']!=''){

$token_id 		= escape_string($_POST['token_id']);
$token_name 	= "token_rss_$token_id";
$show_rss		= escape_string($_POST['rss']);
$show_rss_limit	= escape_string($_POST['list']);

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

mysql_query("UPDATE {$db_prefix}settings SET show_rss='$show_rss', show_rss_limit='$show_rss_limit'");

	template_hook("pages/admin/rss.template.php", "form");

	nova_redirect("index.php?page=admin&act=rss","admin/rss");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}
else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_rss_$token_id";

$_SESSION[$token_name] = $token;

$query2 = "select SHOW_RSS, SHOW_RSS_LIMIT from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("rss.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$show_rss = strip_slashes($results2['SHOW_RSS']);
$show_rss_limit = strip_slashes($results2['SHOW_RSS_LIMIT']);

template_hook("pages/admin/rss.template.php", "2");

}
}

}

template_hook("pages/admin/rss.template.php", "end");
?>