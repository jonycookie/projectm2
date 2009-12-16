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
|   lang_myoptions.php - Language file - User CP Areas - English
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.<br /><i>NovaBoard Version: 0.8</i>";
	exit();
}

$lang_user = array (

	// This script deals with languages in the myoptions area

		// avatar
			'avatar_title'      		=> "Your Forum Avatar",
			'avatar_change'				=> "Change My Avatar",
			'avatar_online'				=> "Online Avatar",
			'avatar_online_desc'		=> "You can specify a link to an online image to use as your avatar here, or alternatively, you can upload one using the form below.",
			
		// email
			'email_title'				=> "Email Settings",
			'email_change'				=> "Change My Email Address",
			'email_desc'				=> "If you wish to change your email address, type the new address into the box below. Please make sure that this email address is active and that you have access to it.",
			'email_address'				=> "Email Address",
			
		// home
			'home_title'				=> "My Whiteboard",
			'home_desc'					=> "Keep note snippets here",
			
		// information
			'information_title'			=> "Profile Information",
			'information_location'		=> "Location & Nationality",
			'information_location_desc'	=> "Enter your current location and select the country of your birth from the drop down menu below.",
			'information_loc'			=> "Location:",
			'information_nat'			=> "Nationality:",
			'information_com'			=> "Communication",
			'information_com_desc'		=> "If you want people to be able to contact you via Instant Messaging clients, enter your details below in the appropriate field. This information will be shown to members only as a spam prevention measure.",
			'information_wlm'			=> "Windows Live Messenger:",
			'information_aol'			=> "AOL Instant Messenger:",
			'information_yim'			=> "Yahoo! Instant Messenger:",
			'information_skype'			=> "Skype Contact Name",
			
			'information_tags'			=> "Gamer Tags",
			'information_tags_desc'		=> "Fill in your gamer tags for consoles you have to play against members of this forum. Please refer to your consoles technical manual for instructions on how to obtain your gamer tag.",
			'information_tags_xbox'		=> "Microsoft XBox 360:",
			'information_tags_wii'		=> "Nintendo Wii:",
			'information_tags_ps3'		=> "Playstation 3:",
			
			'information_custom'		=> "Custom Fields",
			'information_custom_desc'	=> "Your forum administrators have created extra fields that they wish you to fill in.",
		
			'information_admin_email_title' => "Board Email Options",
			'information_admin_email'	=> "Indicate if you are willing to receive emails from board administrators and moderators.",
			'information_admin_email_desc' => "Allow board emails?",
		
		// password
			'password_error'			=> "Error: Your new password must not match your previous one.",
			'password_title'			=> "Password Settings",
			'password_change'			=> "Change My Password",
			'password_desc'				=> "If you wish to change your password, type the new password into the box below, followed by your current password. When submitted, you will need to log in again.",
			'password_expired'			=> "Your password has expired. Please enter a new one.",
			'password_new'				=> "New Password",
			'password_current'			=> "Current Password",
			
		// signature
			'signature_title'			=> "Signature Settings",
			'signature_change'			=> "Change My Signature",
			'signature_desc'			=> "If you wish to change your signature please do so in the below textarea. Please keep images to a respectable size. Large signatures may be removed by moderators/administrators of the forum.",

		// subscriptions
			'subscriptions_forum'		=> "Forum Subscriptions",
			'subscriptions_forum_title'	=> "Active Subscriptions to Forums",
			'subscriptions_forum_desc'	=> "Here you can view all the forums you have opted to subscribe to. You can click a forum title to view that forum. Clicking the Delete icon will remove that subscription so you will not receive any further emails when new topics are posted in that particular forum.",			
	
			'subscriptions_topic'		=> "Topic Subscriptions",
			'subscriptions_topic_title'	=> "Active Subscriptions to Topics",
			'subscriptions_topic_desc'	=> "Here you can view all the topics you have opted to subscribe to. You can click a topic title to view that topic. Clicking the Delete icon will remove that subscription so you will not receive any further emails when new replies are posted in that particular topic.",			

			'subscriptions_pm'			=> "Private Message Subscription",
			'subscriptions_pm_title'	=> "Alter settings for new PM notification",
			'subscriptions_pm_desc'		=> "By checking the box below, you will be sent an email whenever you receive a new Private Message from another member on this forum.",			
			'subscriptions_pm_option'	=> "Subscribe to PM's?",
			
	
		// theme
			'theme_title'				=> "Theme Settings",
			'theme_change'				=> "Change Forum Theme",
			'theme_desc'				=> "If you wish to change your forum theme, select a different theme from the drop down box below.",
			'theme_select'				=> "Select A Theme:",
			
		// timezone
			'timezone_title'			=> "Timezone Settings",
			'timezone_change'			=> "Change Timezone",
			'timezone_desc'				=> "If the boards default time is not correct to your timezone, select how many hours it should offset by until it is correct.",
			'timezone_default'			=> "Default Time:",
			'timezone_set'				=> "My Set Time:",
			'timezone_select'			=> "Select A Timezone:",
			'timezone_current'			=> "Current Timezone",
			'timezone_hours'			=> "hours",
			
		// username
			'username_title'			=> "Username Settings",
			'username_change'			=> "Change My Username",
			'username_desc'				=> "If you wish to change your username, type the new name into the box below. When submitted, you'll be sent an email to your registered email address confirming your action and will be asked to login again.",
			'username_new'				=> "New Username:",
				
		// usertitle
			'usertitle_title'			=> "Usertitle Settings",
			'usertitle_change'			=> "Change My Usertitle",
			'usertitle_desc'			=> "If you wish to change your usertitle, type the new title into the box below. Your usertitle displays under your name in forum topics and in your profile.",
			'usertitle_new'				=> "New Usertitle:",
			'usertitle_length'			=> 'The administrators have set a maximum length of <span style="font-weight: bold">%d</span> characters available.',
			'usertitle_disabled'		=> 'Usertitles are not enabled.',
			
);

?>