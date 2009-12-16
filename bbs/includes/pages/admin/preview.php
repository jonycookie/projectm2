<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   preview.php - admin options for mod previewed posts
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/preview.template.php", "start");

if ($can_change_forum_settings=='0'){

	nova_redirect("index.php?page=error","error");

}

$query2 = "select CAN_MODERATE_MEMBERS from {$db_prefix}moderators WHERE MEMBER_ID='$my_id' AND CAN_MODERATE_MEMBERS='1'" ;
$result2 = mysql_query($query2) or die("preview.php - Error in query: $query2") ;                                  
$can_moderate_members_count=mysql_num_rows($result2);

if ($can_moderate_members_count!='0'){
$can_moderate_members="1";
}

if ($can_moderate_members=='0'){

	nova_redirect("index.php?page=error","error");

}

else{

if ($_GET['id']!='' && $_GET['action']=='approve'){

$id	=	escape_string($_GET['id']);

$query2 = "select POSTID from {$db_prefix}moderate WHERE ID='$id'" ;
$result2 = mysql_query($query2) or die("preview.php - Error in query: $query2") ;                                  
$post = mysql_result($result2, 0);

mysql_query("DELETE FROM {$db_prefix}moderate WHERE id ='$id'");

mysql_query("UPDATE {$db_prefix}posts SET approved='1' WHERE id='$post'");

// auto-cache
include "scripts/php/auto_cache.php";

	template_hook("pages/admin/preview.template.php", "form_1");

	nova_redirect("index.php?page=admin&act=preview","admin/preview");

}

elseif ($_GET['id']!='' && $_GET['action']=='reject'){

$id = escape_string($_GET['id']);

$query2 = "select POSTID from {$db_prefix}moderate WHERE ID='$id'" ;
$result2 = mysql_query($query2) or die("preview.php - Error in query: $query2") ;                                  
$post = mysql_result($result2, 0);

mysql_query("DELETE FROM {$db_prefix}moderate WHERE id ='$id'");

mysql_query("DELETE FROM {$db_prefix}posts WHERE id ='$post'");

// auto-cache
include "scripts/php/auto_cache.php";

	template_hook("pages/admin/preview.template.php", "form_2");

if ($_GET['post']!=''){

$post = escape_string($_GET['post']);

	nova_redirect("index.php?page=findpost&post=$post","findpost/$post");

}
else{

	nova_redirect("index.php?page=admin&act=preview","admin/preview");

}

}


else{

template_hook("pages/admin/preview.template.php", "2");

$query2 = "select ID, POSTID, TITLE, MEMBER_ID, MEMBER_NAME, TIME from {$db_prefix}moderate ORDER BY ID desc" ;
$result2 = mysql_query($query2) or die("preview.php - Error in query: $query2") ;                                  
while ($results2 = mysql_fetch_array($result2)){
$id = $results2['ID'];
$post = $results2['POSTID'];
$member_id = $results2['MEMBER_ID'];
$member_name = strip_slashes($results2['MEMBER_NAME']);
$title = strip_slashes($results2['TITLE']);
$time = $results2['TIME'];

$time = format_date($time);

if ($title==''){
$title="Missing Topic Subject";
}

template_hook("pages/admin/preview.template.php", "3");

}

template_hook("pages/admin/preview.template.php", "4");

}

}

template_hook("pages/admin/preview.template.php", "end");
?>