<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   topics.php - topic settings
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/topics.template.php", "start");

if ($can_change_forum_settings=='0'){

	nova_redirect("index.php?page=error&error=11","error/11");

}

else{

if ($_POST['list_posts']!=''){

$token_id				= escape_string($_POST['token_id']);
$token_name				= "token_topics_$token_id";

$list_topics			= escape_string($_POST['list_topics']);
$list_posts				= escape_string($_POST['list_posts']);
$hot_topic				= escape_string($_POST['hot_topic']);
$store_post_history		= escape_string($_POST['store_post_history']);
$quick_edit				= escape_string($_POST['quick_edit']);
$auto_merge				= escape_string($_POST['auto_merge']);

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

mysql_query("UPDATE {$db_prefix}settings SET list_topics='$list_topics', list_posts='$list_posts', hot_topic='$hot_topic', store_post_history='$store_post_history', quick_edit='$quick_edit', auto_merge='$auto_merge'");

	template_hook("pages/admin/topics.template.php", "form");

	nova_redirect("index.php?page=admin&act=topics","admin/topics");

}
else{
	nova_redirect("index.php?page=error&error=28","error/28");
}
}
else{


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$token_name = "token_topics_$token_id";

$_SESSION[$token_name] = $token;

$query2 = "select LIST_TOPICS, LIST_POSTS, HOT_TOPIC, STORE_POST_HISTORY, QUICK_EDIT, AUTO_MERGE from {$db_prefix}settings" ;
$result2 = mysql_query($query2) or die("topics.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$list_topics 		= strip_slashes($results2['LIST_TOPICS']);
$list_posts 		= strip_slashes($results2['LIST_POSTS']);
$hot_topic 			= strip_slashes($results2['HOT_TOPIC']);
$store_post_history = strip_slashes($results2['STORE_POST_HISTORY']);
$quick_edit 		= strip_slashes($results2['QUICK_EDIT']);
$auto_merge 		= strip_slashes($results2['AUTO_MERGE']);

template_hook("pages/admin/topics.template.php", "2");

}
}

}

template_hook("pages/admin/topics.template.php", "end");
?>