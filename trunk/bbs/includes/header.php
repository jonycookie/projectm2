<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   header.php - displays header and sets out global variables
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

       #---------------------- 
	   # Get modules 
	   #---------------------- 

			$query29 = "select id, module_name from {$db_prefix}modules ORDER BY id DESC";
			$result29 = mysql_query($query29) or die("modules.php - Error in query: $query");
			
			$modules = array();
				
			while ($row = mysql_fetch_assoc($result29))

				$modules[$row['id']] = $row;
			
			$content = '<?php' . "\n";
			
			if (!empty($modules))
				$content .= '$cache = ' . var_export($modules, true) . ';';
			else
				$content .= '$cache = array();';
			$content .= "\n" . '?>';
			$handle = fopen('cache/modules.php', 'w');
			fwrite($handle, $content);
			fclose($handle); 	 		
		
	// get site information

		$query2 = "select SITE_NAME, SITE_DESC, LIST_TOPICS, LIST_POSTS, HOT_TOPIC, ALLOW_ATTACHMENTS, ATTACH_AVATAR_SIZE, ATTACH_IMG_SIZE, SHOW_RSS, SHOW_RSS_LIMIT, SHOW_GAMER_TAGS, MAX_GUEST_CLICKS, MAX_WARN, TIME_OFFSET, SEF_URLS, ONLINE_YESTERDAY, BOARD_OFFLINE, BOARD_OFFLINE_REASON, GUEST_REGISTER, RULES, CHANGE_PASS_TIME, HOME, STORE_POST_HISTORY, QUICK_EDIT, BOARD_LANG, NOVA_VERSION, STATS_TOPICS, STATS_POSTS, STATS_MEMBER_ID, STATS_MEMBER_NAME, STATS_MEMBERS, STATS_POST_ID, STATS_POST_TITLE, STATS_POST_FORUM, STATS_POST_TIME, STATS_POST_TOPIC, BOARD_EMAIL, REGISTER_BAR, MODULE_ORDER, MODULE_LIMIT, MODULE_METHOD, THEME_ORDER, THEME_LIMIT, THEME_METHOD, AUTO_MERGE, AKISMET_KEY, RECAPTCHA_PUBLIC, RECAPTCHA_PRIVATE, username_length, usertitle_length from {$db_prefix}settings" ;
		$result2 = mysql_query($query2) or die("header.php - Error in query: $query2") ;                                  
		while ($results2 = mysql_fetch_array($result2)){
			$site_name				= strip_slashes($results2['SITE_NAME']);
			$site_desc				= strip_slashes($results2['SITE_DESC']);
			$list_topics			= $results2['LIST_TOPICS'];
			$list_posts				= $results2['LIST_POSTS'];
			$hot_topic				= $results2['HOT_TOPIC'];
			$allow_attachments		= $results2['ALLOW_ATTACHMENTS'];
			$attach_img_size		= strip_slashes($results2['ATTACH_IMG_SIZE']);
			$attach_avatar_size		= strip_slashes($results2['ATTACH_AVATAR_SIZE']);
			$show_rss				= $results2['SHOW_RSS'];
			$show_rss_limit			= $results2['SHOW_RSS_LIMIT'];
			$show_gamer_tags		= $results2['SHOW_GAMER_TAGS'];
			$max_guest_clicks		= $results2['MAX_GUEST_CLICKS'];
			$max_warn_level			= $results2['MAX_WARN'];
			$max_warn				= $results2['MAX_WARN'];
			$time_offset			= $results2['TIME_OFFSET'];
			$sef_urls				= $results2['SEF_URLS'];
			$online_yesterday		= $results2['ONLINE_YESTERDAY'];
			$board_offline			= $results2['BOARD_OFFLINE'];
			$board_offline_reason	= strip_slashes($results2['BOARD_OFFLINE_REASON']);
			$guest_register			= $results2['GUEST_REGISTER'];
			$rules					= strip_slashes($results2['RULES']);
			$change_pass_time		= $results2['CHANGE_PASS_TIME'];
			$home					= strip_slashes($results2['HOME']);
			$store_post_history	 	= $results2['STORE_POST_HISTORY'];
			$quick_edit				= $results2['QUICK_EDIT'];
			$board_lang				= strip_slashes($results2['BOARD_LANG']);
			$nova_version			= $results2['NOVA_VERSION'];
			$stats_topics			= $results2['STATS_TOPICS'];
			$stats_posts			= $results2['STATS_POSTS'];
			$stats_members			= $results2['STATS_MEMBERS'];
			$stats_member_id		= $results2['STATS_MEMBER_ID'];
			$stats_member_name		= strip_slashes($results2['STATS_MEMBER_NAME']);
			$stats_post_id			= $results2['STATS_POST_ID'];
			$stats_post_title		= $results2['STATS_POST_TITLE'];
			$stats_post_forum		= $results2['STATS_POST_FORUM'];
			$stats_post_time		= $results2['STATS_POST_TIME'];
			$stats_post_topic		= $results2['STATS_POST_TOPIC'];
			$default_board_email	= strip_slashes($results2['BOARD_EMAIL']);
			$register_bar			= $results2['REGISTER_BAR'];
			$module_order			= $results2['MODULE_ORDER'];
			$module_limit			= $results2['MODULE_LIMIT'];
			$module_method			= strip_slashes($results2['MODULE_METHOD']);
			$theme_order			= $results2['THEME_ORDER'];
			$theme_limit			= $results2['THEME_LIMIT'];
			$theme_method			= strip_slashes($results2['THEME_METHOD']);
			$auto_merge				= $results2['AUTO_MERGE'];
			$akismet_key			= strip_slashes($results2['AKISMET_KEY']);
			$recaptcha_private		= strip_slashes($results2['RECAPTCHA_PRIVATE']);
			$recaptcha_public		= strip_slashes($results2['RECAPTCHA_PUBLIC']);
			$username_length		= $results2['username_length'];
			$usertitle_length		= $results2['usertitle_length'];
		}

	// set default email address
	
		$board_email = "noreply@" . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'], 1);

	// remove .domain.com problem email address
	
		$board_email = str_replace("noreply@.", "noreply@", $board_email);
		
	// now set it as default address
		
		ini_set('sendmail_from', $board_email);

	// unset home
		if ($home==''){
			unset($home);
		}

	// check .htaccess exists. If it doesn't, don't use sef_url's
	
		if (!file_exists(".htaccess")){
			$sef_urls="0";
		}

	// Check their login details match what we've got held in the database
	// and if they are guff, chuck them off...

		if (isset($_COOKIE['nova_name'])){

			$nova_name		=	escape_string($_COOKIE['nova_name']);
			$nova_name		=	str_replace("'", "", $nova_name);
			
			if (!preg_match('|^[a-zA-Z0-9!@#$%^&*();:_.\\\\ /\t-]+$|', $nova_name) ) {
			
				setcookie("nova_name", $name, time() -1);
				setcookie("nova_password", $password, time() -1);

				nova_redirect("index.php?page=error&error=32","error/32");

			}
			$nova_password	=	escape_string($_COOKIE['nova_password']);

			// get member details...
			
				$query_member_stuff = "select ID, ROLE, WARN_LEVEL, SUSPEND_DATE, VERIFIED, PASSWORD_TIME, READ_ALL_POSTS, BOARD_LANG, BANNED, REGISTER_DATE, NEW_PMS, NATIONALITY, MODERATE, TIME_OFFSET from {$db_prefix}members WHERE NAME ='$name' AND PASSWORD='$password'" ;
				$result_member_stuff = mysql_query($query_member_stuff) or die("header.php - Error in query: $query_member_stuff") ;
				$secure = mysql_num_rows($result_member_stuff);                                  
				while ($results_member_stuff = mysql_fetch_array($result_member_stuff)){
					$role			= $results_member_stuff['ROLE'];
					$my_id			= $results_member_stuff['ID'];
					$warn_level		= $results_member_stuff['WARN_LEVEL'];
					$suspend_date	= $results_member_stuff['SUSPEND_DATE'];
					$verified		= $results_member_stuff['VERIFIED'];
					$read_all_posts = $results_member_stuff['READ_ALL_POSTS'];
					$member_lang	= $results_member_stuff['BOARD_LANG'];
					$member_banned	= $results_member_stuff['BANNED'];
					$password_time	= $results_member_stuff['PASSWORD_TIME'];
					$register_date	= $results_member_stuff['REGISTER_DATE'];
					$new_pms		= $results_member_stuff['NEW_PMS'];
					$nationality	= $results_member_stuff['NATIONALITY'];
					$moderated		= $results_member_stuff['MODERATE'];
					$member_offset	= $results_member_stuff['TIME_OFFSET'];					
					$time_offset	= $member_offset;					
					
					$password_time	= $password_time + ((24*60*60)*$change_pass_time);
					$current_time	= time();

				}

			// are they a guest? If so, set member group accordingly...
				
				if ($my_id < '0'){
					$role="4";
				}

		// check if paypal subscription is still valid...
		
			$query219 = "select SUBSCRIPTION, EXPIRES from {$db_prefix}group_upgrade_details WHERE MEMBER ='$my_id'" ;
			$result219 = mysql_query($query219) or die("members.php - Error in query: $query219");
			$subscribe_number = mysql_num_rows($result219);

			if ($subscribe_number!='0'){ 
			                                 
				while ($results219 = mysql_fetch_array($result219)){
					$subscription	= $results219['SUBSCRIPTION'];
					$expires		= $results219['EXPIRES'];
				}

				if ($current_time >= $expires){

				// downgrade them....
				
					$query29 = "select UPGRADE_FROM from {$db_prefix}group_upgrade WHERE UPGRADE_ID='$subscription'" ;
					$result29 = mysql_query($query29) or die("upgrade.php - Error in query: $query29") ;                                  
					$upgrade_from = mysql_result($result29, 0);

					mysql_query("UPDATE {$db_prefix}members SET role = '$upgrade_from' WHERE role = '$upgrade_to' AND ID='$my_id'");
					mysql_query("DELETE from {$db_prefix}group_upgrade_details WHERE member='$my_id' AND subscription='$subscription'");

				}

			}
			
		// if the member info is wrong, remove cookie and redirect

			if ($secure=='0'){
				setcookie("nova_name", $name, time() -1);
				setcookie("nova_password", $password, time() -1);
				if ($_GET['page']!='login'){

					template_hook("header.template.php", "form_1");
					nova_redirect("index.php?page=login","login");

				}
			}
			
		// still to verify?	
		
			elseif($verified=='0'){
				setcookie("nova_name", $name, time() -1);
				setcookie("nova_password", $password, time() -1);
				
				if ($_GET['page']!='verify'){

					template_hook("header.template.php", "form_2");
					nova_redirect("index.php?page=verify","verify");

				}
			}

		// has their password expired?	
			
			elseif($password_time < $current_time){
				if ($_GET['page']!='myoptions' && $_GET['act']!='password'){

					template_hook("header.template.php", "form_3");
					nova_redirect("index.php?page=myoptions&act=password","myoptions/password");

				}
			}


			else{
			
			// do nothing
			
			}
		}
		
	// not logged in? guest alert!

		elseif (!isset($_COOKIE['nova_name'])){
			$role="4";
		}
		
	// set language

		if (isset($member_lang) && $member_lang!=''){
			$board_lang="$member_lang";
		}

	// Do you speekee english cookie?
	
		if (isset($_COOKIE['nova_lang']) && (!isset($_COOKIE['nova_name']))){
			$board_lang = escape_string($_COOKIE['nova_lang']);
		}

	// Prepare all images to use...
	
		include "scripts/php/image_check.php";

	// Get global permissions...
	
		include "includes/pages/permissions.php";


	// Check in case themember is suspended...
	
		$current_date_and_time = time();

		if (isset($suspend_date)){

			if ($current_date_and_time <= $suspend_date){
				if ($_GET['page']!='suspended' && $_GET['page']!='logout'){
					template_hook("header.template.php", "form_4");
					nova_redirect("index.php?page=suspended","suspended");
				}
			}
		}
		
	// are they banned?	
	
		if (isset($warn_level) && isset($member_banned)){
			if ($max_warn_level <= $warn_level OR $member_banned=='1'){
				if ($_GET['page']!='banned' && $_GET['page']!='logout'){

					template_hook("header.template.php", "form_5");
					nova_redirect("index.php?page=banned","banned");

				}
			}
		}

	// is this a forum?
	
		if (isset($_GET['forum']) && !isset($_GET['page'])){

			$forum	=	escape_string($_GET['forum']);

			$query22 = "select NAME from {$db_prefix}categories WHERE ID ='$forum'" ;
			$result22 = mysql_query($query22) or die("header.php - Error in query: $query22") ;                                  
			while ($results22 = mysql_fetch_array($result22)){
				$location_name = $results22['NAME'];
				$location_name=strip_slashes($location_name);
			}
		}
		
	// or is it a topic?	
		
		elseif(isset($_GET['topic']) && !isset($_GET['page'])){

			$topic	=	escape_string($_GET['topic']);

			$query22 = "select TITLE from {$db_prefix}posts WHERE TOPIC_ID ='$topic' AND TITLE!=''" ;
			$result22 = mysql_query($query22) or die("header.php - Error in query: $query22") ;                                  
			while ($results22 = mysql_fetch_array($result22)){
				$location_name	= $results22['TITLE'];
				$location_name	= strip_slashes($location_name);
			}
		}

	// Check the set time offset...

		if(isset($_COOKIE['nova_name'])){
			$name=escape_string($_COOKIE['nova_name']);
		}
		
		if(isset($_COOKIE['nova_password'])){
			$password=escape_string($_COOKIE['nova_password']);
		}

	// check for the existance of private messages, moderated members
	// and reported posts
		
		if (isset($my_id)){

			$messages_number=$new_pms;

			if ($can_pm=='0'){
				$messages_number="0";
			}

			$query26 = "select ID from {$db_prefix}report" ;
			$result26 = mysql_query($query26) or die("header.php - Error in query: $query26");
			$report_number	= mysql_num_rows($result26);

			$query26 = "select ID from {$db_prefix}moderate" ;
			$result26 = mysql_query($query26) or die("header.php - Error in query: $query26");
			$moderate_number = mysql_num_rows($result26);

		}

		if (!isset($_COOKIE['nova_name'])){
			$new_posts="";
			$messages_number="";
		}
		
	// find out the number of unread posts

		if (isset($nova_name)){

			$unread_posts="0";

			// Now go through each forum...

				$query211 = "select FORUM_ID from {$db_prefix}permissions WHERE GROUP_ID='$role' AND CAN_READ_TOPICS='1' ORDER BY FORUM_ID desc" ;
				$result211 = mysql_query($query211) or die("header.php - Error in query: $query211");                                  
				while ($results211 = mysql_fetch_array($result211)){
					$forum_id	= $results211['FORUM_ID'];
					
					$query212 = "select TOPIC_ID from {$db_prefix}posts WHERE FORUM_ID='$forum_id' AND LAST_POST_TIME > '$read_all_posts' AND LAST_POST_TIME > '$register_date' AND APPROVED='1' AND TITLE!='' ORDER BY TOPIC_ID desc" ;
					$result212 = mysql_query($query212) or die("header.php - Error in query: $query212");
					while ($results212 = mysql_fetch_array($result212)){
						$topic_check_id = $results212['TOPIC_ID'];
	
						$query2118 = "select READ_TIME from {$db_prefix}posts_read WHERE MEMBER_ID='$my_id' AND TOPIC_ID='$topic_check_id'";
						$result2118 = mysql_query($query2118) or die("header.php - Error in query: $query2118");
						$read_count = mysql_num_rows($result2118);
						
						if ($read_count=='0'){
							$read_results="0";
						}
						else{
							$read_results = mysql_result($result2118, 0);
						}
								
							// now check posts...
							
							$query2129 = "select ID from {$db_prefix}posts WHERE TOPIC_ID='$topic_check_id' AND TIME > '$read_results' AND TIME > '$read_all_posts' AND APPROVED='1' AND MEMBER!='$my_id'";
							$result2129 = mysql_query($query2129) or die("header.php - Error in query: $query2129");
							while ($results2129 = mysql_fetch_array($result2129)){
								$post_id = $results2129['ID'];	
							
								$unread_posts	= $unread_posts + 1;

							}
					}
				}

			$new_posts=number_format($unread_posts);

		}
		else{
			$unread_posts="0";
		}

	// Get language files...

		include "lang/$board_lang/lang_forum.php";

		if (isset($_GET['page']) && $_GET['page'] == 'admin'){
			include "lang/$board_lang/lang_admin.php";
		}
	
		if (isset($_GET['page']) && $_GET['page'] == 'error'){
			include "lang/$board_lang/lang_error.php";
		}

		if (isset($_GET['page']) && $_GET['page'] == 'myoptions'){
			include "lang/$board_lang/lang_myoptions.php";
		}
	
		if (isset($_GET['page']) && $_GET['page'] == 'help'){
			include "lang/$board_lang/lang_help.php";
		}	

	// Set variables for some date things (trust me, we need this)

		$format_time	=	$lang['date_format'];
		$date_today		=	$lang['date_today'];
		$date_yesterday	= 	$lang['date_yesterday'];
		$date_minute	= 	$lang['date_minute'];
		$date_minutes	= 	$lang['date_minutes'];	
		$date_hour		= 	$lang['date_hour'];	
		$date_hours		= 	$lang['date_hours'];		

	// Get the script that handles locations

		include "scripts/php/location.php";

		$location_name = location_page("header");
		$location_text = "$site_name, $site_desc, $location_name";

	// prepare the SEO meta for topics	
		
		if (isset($_GET['topic']) && ($_GET['page']!='search')){

			$location_text="";

			$query211 = "select CONTENT from {$db_prefix}posts WHERE TOPIC_ID='$topic' AND TITLE!=''";
			$result211 = mysql_query($query211) or die("topic.php - Error in query: $query211");
			$location_results = mysql_num_rows($result211);	

			if ($location_results!='0'){
				$location_text_string = strip_slashes(mysql_result($result211, 0));

				$location_text_string = explode(" ", $location_text_string);
				for ($wordCounter=0; $wordCounter<30; $wordCounter++) {
					$location_text .= $location_text_string[$wordCounter]." ";
				}
				 $location_text = $location_text."...";
				 $location_text = str_replace(" ...", "...", $location_text);
				 $location_text = str_replace("<br />", "", $location_text);
				 $location_text = str_replace("\r\n", " ", $location_text);
				 
				 function stripBBCode($text_to_search) {
					 $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
					 $replace = '';
					 return preg_replace($pattern, $replace, $text_to_search);
				}

				$location_text = stripBBCode($location_text);
				$location_text = strip_tags($location_text); 
			}
		}
		
	// prepare the SEO meta for forums	
		
		elseif (isset($_GET['forum']) && ($_GET['page']!='search')){

			$location_text="";

			$query211 = "select DESCRIPTION from {$db_prefix}categories WHERE ID='$forum'";
			$result211 = mysql_query($query211) or die("topic.php - Error in query: $query211");
			$location_results = mysql_num_rows($result211);	

			if ($location_results!='0'){
				$location_text_string = strip_slashes(mysql_result($result211, 0));

				$location_text_string = explode(" ", $location_text_string);
				for ($wordCounter=0; $wordCounter<30; $wordCounter++) {
					$location_text .= $location_text_string[$wordCounter]." ";
				}
				 $location_text = $location_text."...";
				 $location_text = str_replace(" ...", "...", $location_text);
				 $location_text = str_replace("<br />", "", $location_text);
				 $location_text = str_replace("\r\n", " ", $location_text);
				 
				 function stripBBCode($text_to_search) {
					 $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
					 $replace = '';
					 return preg_replace($pattern, $replace, $text_to_search);
				}

				$location_text = stripBBCode($location_text);
				$location_text = strip_tags($location_text); 
			}
		}		

		template_hook("header.template.php", "start");
		template_hook("header.template.php", "1");

	// sort some cache control
	
		header("Cache-Control: private");
		header("Pragma: private");
		
	// pull in some clever templates

		template_hook("header.template.php", "before_body");
		template_hook("header.template.php", "after_body");

	// parse lang file to show number of new messages	
		
		$lang['navbar_message_new'] = str_replace("<%1>",$messages_number, $lang['navbar_message_new']);

	// now even more templates	
		
		template_hook("nav_bar.template.php", "start");
		template_hook("nav_bar.template.php", "1");
		template_hook("nav_bar.template.php", "2");
		template_hook("nav_bar.template.php", "3");
		template_hook("nav_bar.template.php", "end");
		
		template_hook("member_bar.template.php", "start");
		template_hook("member_bar.template.php", "1");
		template_hook("member_bar.template.php", "2");
		template_hook("member_bar.template.php", "end");

		template_hook("header.template.php", "2");

	// Now include the members session information
	
		include "includes/forums/session.php";

	// if the board is offline, redirect if not admin
		
		if ($can_change_site_settings!='1' && $board_offline=='1' && $_GET['page']!='offline' && $_GET['page']!='login' && $_GET['page']!='verify'){
			template_hook("header.template.php", "form_6");
			nova_redirect("index.php?page=offline","offline");
		}

	// require registration to view board?	
		
		if ($can_view_board=='0' && $_GET['page']!='error' && $_GET['page']!='register' && $_GET['page']!='login' && $_GET['page']!='verify'){
			nova_redirect("index.php?page=error&error=30","error/30");
		}

	// final template hook. PHEW!	
	
		template_hook("header.template.php", "end");

?>