<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   move.php - move topic to another forum
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("forums/move.template.php", "start");

if ($can_move_topics=='0'){

	nova_redirect("index.php?page=error&error=8","error/8");

}

if ($_POST['forum']!=''){

$topic=$_POST['topic'];
$topic=escape_string($topic);

$token_id = $_POST['token_id'];
$token_id = escape_string($token_id);

$token_name = "token_move_$topic$token_id";

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

$forum=$_POST['forum'];
$forum=escape_string($forum);

mysql_query("UPDATE {$db_prefix}posts SET forum_id='$forum' WHERE topic_id = '$topic' ");

// perform auto-cache
include "scripts/php/auto_cache.php";	

	template_hook("forums/move.template.php", "form");

	$topic_title = topic_title($topic);	
	
	nova_redirect("index.php?topic=$topic","topic/$topic_title-$topic");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}
}

else{

$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$topic=$_GET['topic'];
$topic=escape_string($topic);

$token_name = "token_move_$topic$token_id";

$_SESSION[$token_name] = $token;

template_hook("forums/move.template.php", "2");

$query211 = "select ID, NAME from {$db_prefix}categories WHERE PARENT='0' ORDER BY FORUM_ORDER asc, ID desc" ;
$result211 = mysql_query($query211) or die("move.php - Error in query: $query211") ;                                  
while ($results211 = mysql_fetch_array($result211)){
$id = $results211['ID'];
$name = $results211['NAME'];

$name = strip_slashes($name);

template_hook("forums/move.template.php", "3");

$query2 = "select ID, NAME from {$db_prefix}categories WHERE PARENT='$id' ORDER BY FORUM_ORDER asc, ID desc" ;
$result2 = mysql_query($query2) or die("move.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$forum_id = $results2['ID'];
$forum_name = $results2['NAME'];

$forum_name = strip_slashes($forum_name);

template_hook("forums/move.template.php", "4");

$query_sub = "select ID, NAME from {$db_prefix}categories WHERE PARENT='$forum_id' ORDER BY FORUM_ORDER asc, ID desc" ;
$result_sub = mysql_query($query_sub) or die("move.php - Error in query: $query2") ;                                  
while ($results_sub = mysql_fetch_array($result_sub)){
$forum_id = $results_sub['ID'];
$forum_name = $results_sub['NAME'];

$forum_name = strip_slashes($forum_name);

template_hook("forums/move.template.php", "5");

}


}

template_hook("forums/move.template.php", "6");

}

template_hook("forums/move.template.php", "7");

}

template_hook("forums/move.template.php", "end");

?>