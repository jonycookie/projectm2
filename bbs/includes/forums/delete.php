<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================
|+--------------------------------------------------------------------------
|   delete.php - deletes posts, topics and relevant polls & attachments
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

$post = (int) $_POST['post_delete_id'];

if ($can_delete_others_posts != 1)
{
		/*
		Check whether this is their post
	*/
	
		$query	= mysql_query('SELECT member FROM ' . $db_prefix . 'posts WHERE id = ' . $post);
		$row	= mysql_fetch_assoc($query);
		
		if ($row['member'] != $my_id || $can_delete_own_posts != 1)
		{
			nova_redirect("index.php?page=error&error=4","error/4");
		}
}

if ($_POST['post_delete'] == $lang['button_delete'] && tokenCheck('topic_post_delete', $post))
{
	$query21 = "select TITLE, TOPIC_ID, FORUM_ID from {$db_prefix}posts WHERE ID='$post'" ;
	$result21 = mysql_query($query21) or die("delete.php - Error in query: $query21") ;                                  
	while ($results21 = mysql_fetch_array($result21)){
		$title 		= $results21['TITLE'];
		$topic_id 	= $results21['TOPIC_ID'];
		$forum_id 	= $results21['FORUM_ID'];
	}
	
	if ($title!=''){

		$query212 = "select ID from {$db_prefix}posts WHERE TOPIC_ID='$topic_id'" ;
		$result212 = mysql_query($query212) or die("delete.php - Error in query: $query212") ;                                  
		while ($results212 = mysql_fetch_array($result212)){
			$remove_id 	= $results212['ID'];

				// first, delete attachments associated with these posts...

					$query2121 = "select FILENAME from {$db_prefix}attachments WHERE POSTID='$remove_id'" ;
					$result2121 = mysql_query($query2121) or die("delete.php - Error in query: $query2121") ;                                  
					while ($results2121 = mysql_fetch_array($result2121)){
						$filename 	= $results2121['FILENAME'];

						foreach (glob("uploads/attachments/$filename") as $filename_original) {
						   unlink($filename_original);
						}

						foreach (glob("uploads/attachments/t_$filename") as $filename_thumb) {
						   unlink($filename_thumb);
						}

						mysql_query("DELETE FROM {$db_prefix}attachments WHERE postid ='$remove_id'");

					}
					
				mysql_query("DELETE FROM {$db_prefix}moderate WHERE postid='$remove_id'");						
					
		}

		$query2 = "select ID from {$db_prefix}posts WHERE TOPIC_ID='$topic_id' AND TITLE=''" ;
		$result2 = mysql_query($query2) or die("newpost.php - Error in query: $query2");
		$number_of_posts = mysql_num_rows($result2); 

			// now remove the posts

				mysql_query("DELETE FROM {$db_prefix}posts WHERE topic_id ='$topic_id'");

			// and the stored edits

				mysql_query("DELETE FROM {$db_prefix}posts_edit WHERE topic ='$topic_id'");					


			$query2 = "select ID, TIME, FORUM_ID, TOPIC_ID from {$db_prefix}posts ORDER BY ID desc LIMIT 1" ;
			$result2 = mysql_query($query2) or die("newpost.php - Error in query: $query2") ;                                  
			while ($results2 = mysql_fetch_array($result2)){
				$post_id 		= $results2['ID'];
				$post_time 		= $results2['TIME'];
				$post_forum 	= $results2['FORUM_ID'];
				$post_topic 	= $results2['TOPIC_ID'];
			}

			$query2 = "select TITLE from {$db_prefix}posts WHERE TITLE!='' AND TOPIC_ID='$post_topic'" ;
			$result2 = mysql_query($query2) or die("newpost.php - Error in query: $query2") ;                                  
			$post_title = mysql_result($result2, 0);

				// delete polls also...
				
					$query21 = "select ID from {$db_prefix}polls WHERE TOPIC_ID='$topic_id'" ;
					$result21 = mysql_query($query21) or die("delete.php - Error in query: $query21") ;                                  
					$poll_id = mysql_result($result21, 0);

					mysql_query("DELETE FROM {$db_prefix}polls WHERE topic_id ='$topic_id'");
					mysql_query("DELETE FROM {$db_prefix}polls_votes WHERE poll_id ='$poll_id'");

					$redirect=$forum_id;

				// perform auto-cache
				
					include "scripts/php/auto_cache.php";	

					template_hook("forums/delete.template.php", "form_1");
					
					$forum_title = forum_title($redirect);
					
					nova_redirect("index.php?forum=$redirect","forum/$forum_title-$redirect");

	}
	else
	{

		$post=escape_string($_GET['post']);

		mysql_query("DELETE FROM {$db_prefix}moderate WHERE postid ='$post'");			

	// Replace the last reply in the database...

		$query21 = "select TOPIC_ID from {$db_prefix}posts WHERE ID='$post'" ;
		$result21 = mysql_query($query21) or die("delete.php - Error in query: $query21") ;                                  
		$topic_id = mysql_result($result21, 0);				
		
		mysql_query("DELETE FROM {$db_prefix}posts WHERE id ='$post'");
		
		$query2 = "select ID, TIME, FORUM_ID, TOPIC_ID from {$db_prefix}posts WHERE TOPIC_ID='$topic_id' ORDER BY ID desc LIMIT 1" ;
		$result2 = mysql_query($query2) or die("newpost.php - Error in query: $query2") ;                                  
		while ($results2 = mysql_fetch_array($result2)){
			$post_id 		= $results2['ID'];
			$post_time 		= $results2['TIME'];
			$post_forum 	= $results2['FORUM_ID'];
			$post_topic 	= $results2['TOPIC_ID'];
		}

		$query2 = "select TITLE from {$db_prefix}posts WHERE TITLE!='' AND TOPIC_ID='$post_topic'" ;
		$result2 = mysql_query($query2) or die("newpost.php - Error in query: $query2") ;                                  
		$post_title = mysql_result($result2, 0);

		$query21 = "select TIME from {$db_prefix}posts WHERE TOPIC_ID='$topic_id' ORDER BY ID desc" ;
		$result21 = mysql_query($query21) or die("delete.php - Error in query: $query21") ;                                  
		$time = mysql_result($result21, 0);

		mysql_query("UPDATE {$db_prefix}posts SET last_post_time='$time' WHERE topic_id = '$topic_id' AND TITLE!=''");

		$query2121 = "select FILENAME from {$db_prefix}attachments WHERE POSTID='$post'" ;
		$result2121 = mysql_query($query2121) or die("delete.php - Error in query: $query2121") ;                                  
		while ($results2121 = mysql_fetch_array($result2121)){
			$filename = $results2121['FILENAME'];

			foreach (glob("uploads/attachments/$filename") as $filename_original) {
			   unlink($filename_original);
			}

			foreach (glob("uploads/attachments/t_$filename") as $filename_thumb) {
			   unlink($filename_thumb);
			}

			mysql_query("DELETE FROM {$db_prefix}attachments WHERE postid ='$post'");

		}

		$redirect=$topic_id;

		// perform auto-cache
		
			include "scripts/php/auto_cache.php";	

		template_hook("forums/delete.template.php", "form_2");

		$topic_title = topic_title($redirect);							
		
		nova_redirect("index.php?topic=$redirect","topic/$topic_title-$redirect");

	}
}
else
{
	nova_redirect('index.php?page=error&error=28', 'error/28');
}
?>