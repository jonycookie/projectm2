<?php

/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   permissions.php - gets users permissions from database
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

	//Get all global permissions for the member...
	$query_permissions = "select group_id, group_name, group_color, can_view_board, can_warn_members, can_edit_members, can_delete_members, can_ban_members, can_change_site_settings, can_change_forum_settings, can_change_style, can_use_avatar, can_change_user_title, can_use_sig, can_change_own_name, can_pm, can_edit_own_posts, can_edit_others_posts, can_delete_own_posts, can_delete_others_posts, can_sticky_topics, can_global_announce, can_move_topics, can_lock_topics, can_split_topics, can_merge_topics, can_add_polls, can_see_reported_posts, can_use_html, can_moderate_members, avoid_caspian from {$db_prefix}groups WHERE group_id ='$role'" ;
	$result_permissions = mysql_query($query_permissions) or die("permissions.php - Error in query: $query_permissions") ;                                  
	while ($results_permissions = mysql_fetch_array($result_permissions)){
		$group_id = $results_permissions['group_id'];
		$group_name = $results_permissions['group_name'];
		$group_color = $results_permissions['group_color'];
		$can_view_board = $results_permissions['can_view_board'];
		$can_warn_members = $results_permissions['can_warn_members'];
		$can_edit_members = $results_permissions['can_edit_members'];
		$can_delete_members = $results_permissions['can_delete_members'];
		$can_ban_members = $results_permissions['can_ban_members'];
		$can_change_site_settings = $results_permissions['can_change_site_settings'];
		$can_change_forum_settings = $results_permissions['can_change_forum_settings'];
		$can_change_style = $results_permissions['can_change_style'];
		$can_use_avatar = $results_permissions['can_use_avatar'];
		$can_change_user_title = $results_permissions['can_change_user_title'];
		$can_use_sig = $results_permissions['can_use_sig'];
		$can_change_own_name = $results_permissions['can_change_own_name'];
		$can_pm = $results_permissions['can_pm'];
		$can_edit_own_posts = $results_permissions['can_edit_own_posts'];
		$can_edit_others_posts = $results_permissions['can_edit_others_posts'];
		$can_delete_own_posts = $results_permissions['can_delete_own_posts'];
		$can_delete_others_posts = $results_permissions['can_delete_others_posts'];
		$can_sticky_topics = $results_permissions['can_sticky_topics'];
		$can_global_announce = $results_permissions['can_global_announce'];
		$can_move_topics = $results_permissions['can_move_topics'];
		$can_lock_topics = $results_permissions['can_lock_topics'];
		$can_split_topics = $results_permissions['can_split_topics'];
		$can_merge_topics = $results_permissions['can_merge_topics'];
		$can_add_polls = $results_permissions['can_add_polls'];
		$can_see_reported_posts = $results_permissions['can_see_reported_posts'];
		$can_use_html = $results_permissions['can_use_html'];
		$can_moderate_members = $results_permissions['can_moderate_members'];
		$avoid_caspian = $results_permissions['avoid_caspian'];
	}

if (isset($my_id)){

// is the member a moderator? if so, give
// them access to the moderator control panel

	//Get all global permissions for the member...
	$query_permissions = "select can_see_reported_posts, can_moderate_members from {$db_prefix}moderators WHERE member_id ='$my_id'" ;
	$result_permissions = mysql_query($query_permissions) or die("permissions.php - Error in query: $query_permissions") ;
	$number_results = mysql_num_rows($result_permissions);
                                 
if ($number_results>='1'){
$can_change_forum_settings="1";
}

// okay, so can they do these things?
// if this is a forum or a topic, check in case the member
// is a moderator, and if so, give them extra permissions
// set up.

if (($number_results>='1') && isset($_GET['forum']) && is_numeric($_GET['forum']) && $_GET['func'] != 'merge' && $_GET['page']!='admin'){

$forum_id = escape_string($_GET['forum']);

	$query_permissions = "select can_warn_members, can_edit_members, can_ban_members, can_edit_own_posts, can_edit_others_posts, can_delete_own_posts, can_delete_others_posts, can_sticky_topics, can_move_topics, can_lock_topics, can_split_topics, can_merge_topics, can_add_polls, can_see_reported_posts, can_use_html, can_moderate_members from {$db_prefix}moderators WHERE member_id ='$my_id' AND forum_id='$forum_id'";
	$result_permissions = mysql_query($query_permissions) or die("permissions.php - Error in query: $query_permissions");                                  
	while ($results_permissions = mysql_fetch_array($result_permissions)){
		$can_warn_members = $results_permissions['can_warn_members'];
		$can_edit_members = $results_permissions['can_edit_members'];
		$can_delete_members = $results_permissions['can_delete_members'];
		$can_ban_members = $results_permissions['can_ban_members'];
		$can_change_forum_settings = $results_permissions['can_change_forum_settings'];
		$can_edit_own_posts = $results_permissions['can_edit_own_posts'];
		$can_edit_others_posts = $results_permissions['can_edit_others_posts'];
		$can_delete_own_posts = $results_permissions['can_delete_own_posts'];
		$can_delete_others_posts = $results_permissions['can_delete_others_posts'];
		$can_sticky_topics = $results_permissions['can_sticky_topics'];
		$can_move_topics = $results_permissions['can_move_topics'];
		$can_lock_topics = $results_permissions['can_lock_topics'];
		$can_split_topics = $results_permissions['can_split_topics'];
		$can_merge_topics = $results_permissions['can_merge_topics'];
		$can_add_polls = $results_permissions['can_add_polls'];
		$can_see_reported_posts = $results_permissions['can_see_reported_posts'];
		$can_use_html = $results_permissions['can_use_html'];
		$can_moderate_members = $results_permissions['can_moderate_members'];
	}

}

elseif (($number_results>='1') && isset($_GET['topic']) && is_numeric($_GET['topic'])){

$topic_id = escape_string($_GET['topic']);
	$query2 = "select forum_id from {$db_prefix}posts WHERE topic_id ='$topic_id' AND title!=''" ;
	$result2 = mysql_query($query2) or die("permissions.php - Error in query: $query2") ;                                  
	$forum_id = mysql_result($result2, 0);


	$query_permissions = "select can_warn_members, can_edit_members, can_ban_members, can_edit_own_posts, can_edit_others_posts, can_delete_own_posts, can_delete_others_posts, can_sticky_topics, can_move_topics, can_lock_topics, can_split_topics, can_merge_topics, can_add_polls, can_see_reported_posts, can_use_html, can_moderate_members from {$db_prefix}moderators WHERE member_id ='$my_id' AND forum_id='$forum_id'";
	$result_permissions = mysql_query($query_permissions) or die("permissions.php - Error in query: $query_permissions");                                    
	while ($results_permissions = mysql_fetch_array($result_permissions)){
		$can_warn_members = $results_permissions['can_warn_members'];
		$can_edit_members = $results_permissions['can_edit_members'];
		$can_delete_members = $results_permissions['can_delete_members'];
		$can_ban_members = $results_permissions['can_ban_members'];
		$can_change_forum_settings = $results_permissions['can_change_forum_settings'];
		$can_edit_own_posts = $results_permissions['can_edit_own_posts'];
		$can_edit_others_posts = $results_permissions['can_edit_others_posts'];
		$can_delete_own_posts = $results_permissions['can_delete_own_posts'];
		$can_delete_others_posts = $results_permissions['can_delete_others_posts'];
		$can_sticky_topics = $results_permissions['can_sticky_topics'];
		$can_move_topics = $results_permissions['can_move_topics'];
		$can_lock_topics = $results_permissions['can_lock_topics'];
		$can_split_topics = $results_permissions['can_split_topics'];
		$can_merge_topics = $results_permissions['can_merge_topics'];
		$can_add_polls = $results_permissions['can_add_polls'];
		$can_see_reported_posts = $results_permissions['can_see_reported_posts'];
		$can_use_html = $results_permissions['can_use_html'];
		$can_moderate_members = $results_permissions['can_moderate_members'];
	}

}

}
?>