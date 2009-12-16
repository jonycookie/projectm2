<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   unsubscribe.php - cancel subscription to topic/forum
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

if ($_GET['topic']!=''){
$topic=$_GET['topic'];
$topic=escape_string($topic);

mysql_query("DELETE FROM {$db_prefix}subscribe WHERE id ='$my_id' AND subscribed_topic='$topic'");
   
	template_hook("pages/unsubscribe.template.php", "form");

	$topic_title = topic_title($topic);
	
	nova_redirect("index.php?topic=$topic","topic/$topic_title-$topic");

}
elseif ($_GET['forum']!=''){
$forum=$_GET['forum'];
$forum=escape_string($forum);

mysql_query("DELETE FROM {$db_prefix}subscribe WHERE id ='$my_id' AND subscribed_forum='$forum'");
  
	$forum_title = forum_title($forum);
  
	nova_redirect("index.php?forum=$forum","forum/$forum_title-$forum");

}

?>