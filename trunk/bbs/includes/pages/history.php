<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   delete.php - deletes posts, topics and relevant polls & attachments
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

if ($can_edit_others_posts=='0'){

	nova_redirect("index.php?page=error&error=5","error/5");

}
else{
$row=$_GET['entry'];
$row=escape_string($row);

$post=$_GET['post'];
$post=escape_string($post);

mysql_query("DELETE FROM {$db_prefix}posts_edit WHERE row ='$row'");

template_hook("pages/history.template.php", "form");

	nova_redirect("index.php?page=findpost&post=$post","findpost/$post");

}
?>