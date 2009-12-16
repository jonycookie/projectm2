<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   structure.php - Selects theme and tells forum in what order to display things
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

	// Include functions...
	
		include "scripts/php/functions.php";

	// get board theme

		$query2		=	"select THEME from {$db_prefix}settings" ;
		$result2	=	mysql_query($query2) or die("Have you run install.php yet?") ;                                  
		$theme		=	mysql_result($result2, 0);
	
	//  does the person have a theme in a cookie?
	
		if (isset($_COOKIE['nova_theme'])){
			$theme=escape_string($_COOKIE['nova_theme']);
		}

	// get username details if set	
		if(isset($_COOKIE['nova_name'])){
		$nova_name=$_COOKIE['nova_name'];		
	   $name = nl2br($nova_name);
	   if(version_compare(phpversion(),"4.3.0")=="-1") {
		$nova_name = mysql_escape_string($nova_name);
	   }
	   else {
		$nova_name = mysql_real_escape_string($nova_name);
	   }
	   }

		if(isset($_COOKIE['nova_password'])){
		$password=$_COOKIE['nova_password'];

		   $password = nl2br($password);
		   if(version_compare(phpversion(),"4.3.0")=="-1") {
		     $password = mysql_escape_string($password);
		   } else {
		     $password = mysql_real_escape_string($password);
		   }

		}

		
	// remove the members selected theme

		unset($member_selected_theme);
		
	// if the username is set, get that members theme

		if (isset($nova_name)){

			$query_theme			=	"select THEME from {$db_prefix}members WHERE NAME='$nova_name' AND PASSWORD='$password'";
			$result_theme			=	mysql_query($query_theme) or die("structure.php - Error in query: $query_theme");
			$count_theme			=	mysql_num_rows($result_theme);			
			$theme_results			=	mysql_result($result_theme, 0);

			if ($theme_results!=''){
			
				// is it set in the database?
				
				$query_theme_exist		=	"select THEME_NAME from {$db_prefix}themes WHERE THEME_NAME='$theme_results'";
				$result_theme_exist		=	mysql_query($query_theme_exist) or die("structure.php - Error in query: $query_theme");
				$count_theme_exist		=	mysql_num_rows($result_theme_exist);			
				
			}
			
			if ($theme_results!='' && $count_theme_exist!='0'){
				$theme = $theme_results;
			}

		}

	// Does the forum have a theme?

		if (isset($_GET['forum']) && is_numeric($_GET['forum'])){

			$forum_id		=	$_GET['forum'];

			$query2			=	"select THEME from {$db_prefix}categories WHERE ID = '$forum_id'" ;
			$result2		=	mysql_query($query2) or die("structure.php - Error in query: $query2");
			$num_result		=	mysql_num_rows($result2);
			if ($num_result!='0'){
				$category_theme	=	mysql_result($result2, 0);
			}
			else{
				$category_theme	=	"";
			}	

		}
		
		if (isset($category_theme) && ($category_theme!='')){
			$theme	=	$category_theme;
		}

	// Does the topic's forum have a theme?

		if (isset($_GET['topic']) && is_numeric($_GET['topic'])){

			$topic_id		=	$_GET['topic'];

			$query2			=	"select FORUM_ID from {$db_prefix}posts WHERE TOPIC_ID = '$topic_id'" ;
			$result2		=	mysql_query($query2) or die("structure.php - Error in query: $query2");
			$num_result		=	mysql_num_rows($result2);
			if ($num_result!='0'){
				$forum_id	=	mysql_result($result2, 0);
			}
			else{
				$forum_id	=	"0";
			}

			$query2			=	"select THEME from {$db_prefix}categories WHERE ID = '$forum_id'" ;
			$result2		=	mysql_query($query2) or die("structure.php - Error in query: $query2");
			$num_result		=	mysql_num_rows($result2);
			if ($num_result!='0'){
				$category_theme	=	mysql_result($result2, 0);
			}
			else{
				$category_theme	=	"";
			}			

		}

		if (isset($category_theme) && ($category_theme!='')){
			$theme	=	$category_theme;
		}

	// if it doesn't exist, use the default
		if (!file_exists("themes/$theme")){
			$theme	=	"novaboard_default";
		}

		$theme_strip=str_replace("%20"," ",$theme);

	// now build up the board, starting with the header...
		
		if (file_exists("themes/$theme/includes/header.php")){
			include "themes/$theme/includes/header.php";
		}
		else{
			include "includes/header.php";
		}

		template_hook("structure.template.php", "start");

		template_hook("structure.template.php", "1");

	//  No page set? Go home.
	
		if (!isset($_GET['page'])){
			$page	=	"home";
		}
		
	// otherwise, prepare it
	
		else{
			$page	=	escape_string($_GET['page']);
		}
		
	// now grab the breadcrumbs...
	
		if (file_exists("themes/$theme/includes/breadcrumbs.php")){
			include "themes/$theme/includes/breadcrumbs.php";
		}
		else{
			include "includes/breadcrumbs.php";
		}

	// then the page itself...
			
		if (file_exists("themes/$theme/includes/pages/$page.php")){
			include "themes/$theme/includes/pages/$page.php";
		}
		elseif (@include("includes/pages/$page.php")){
		}
		else{
			header("HTTP/1.0 200 OK");
			header('Location: index.php?page=error&error=29');
			exit;
		}

		template_hook("structure.template.php", "2");

	// and now for the footer	
		
		if (file_exists("themes/$theme/includes/footer.php")){
			include "themes/$theme/includes/footer.php";
		}
		else{
			include "includes/footer.php";
		}
		
		template_hook("structure.template.php", "3");

		template_hook("structure.template.php", "4");

		template_hook("structure.template.php", "end");

	mysql_close($_dbConn);
ob_flush();
?>