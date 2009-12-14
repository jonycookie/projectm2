<?php
//$updates[] = "ALTER TABLE icebb_ra_logs ADD forum_id TINYINT(11) NOT NULL;";
//$updates[] = "ALTER TABLE icebb_logs ADD forum_id TINYINT(11) NOT NULL;";
$inserts[] = "INSERT INTO `icebb_settings` (
`setting_id` ,
`setting_group` ,
`setting_title` ,
`setting_desc` ,
`setting_key` ,
`setting_type` ,
`setting_value` ,
`setting_default` ,
`setting_php` ,
`setting_sort` ,
`setting_protected`
)
VALUES (
NULL , '4', 'Restrict sessions to IP address?', 'Do you want to restrict sessions to the IP address that was used to log in? This improves security, but may be inconvenient for some members.', 'session_restrict_ip', 'yes_no', '1', '1', '', '4', '1'
);";

// 1.0-rc9
$inserts[] = "INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '4', 'Enable OpenID?', 'Do you want to allow users to login using their <a href=''http://openid.net/'' target=''_blank''>OpenID</a>?', 'enable_openid', 'yes_no', '1', '1', '', '2', '1'
);";

$inserts[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_associations` (
  `server_url` blob NOT NULL,
  `handle` varchar(255) NOT NULL default '',
  `secret` varchar(2047) default NULL,
  `issued` int(11) default NULL,
  `lifetime` int(11) default NULL,
  `assoc_type` varchar(64) default NULL,
  PRIMARY KEY  (`server_url`(255),`handle`)
) ENGINE=InnoDB;";

$inserts[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_nonces` (
  `nonce` char(8) NOT NULL default '',
  `expires` int(11) default NULL,
  PRIMARY KEY  (`nonce`),
  UNIQUE KEY `nonce` (`nonce`)
) ENGINE=InnoDB;";

$inserts[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_settings` (
  `setting` varchar(128) NOT NULL default '',
  `value` blob,
  PRIMARY KEY  (`setting`),
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB;";

$inserts[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_urls` (
  `uid` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`url`)
) ENGINE=MyISAM;";

$inserts[] = "ALTER TABLE `icebb_users` ADD `login_key` VARCHAR( 32 ) NOT NULL ;";
$inserts[] = "UPDATE icebb_users SET login_key=MD5(UNIX_TIMESTAMP() + RAND())";

$inserts[] = "INSERT INTO `icebb_settings_sections` ( `st_id` , `st_title` , `st_desc` , `st_sort` , `st_hidden` )
		VALUES (
		'12', 'CPU Saving', 'Settings that will enable you to disable certain features that will decrease the load on large boards.', '12', '0'
		);";

$inserts[] ="INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
		VALUES (
		NULL , '12', 'Disable recent actions', 'This will disable the ''recent actions'' on the main view.', 'cpu_disable_recent_actions', 'yes_no', '0', '0', '', '1', '1'
		), (
		NULL , '12', 'Require login to search', 'If this is turned on, then users who are not logged in will be denied access to the search feature.', 'cpu_login_to_search', 'yes_no', '0', '0', '', '2', '1'
		), (
		NULL , '12', 'Disable online members', 'If this is turned on then the ''users online'' wont be generated and displayed.', 'cpu_disable_online_members', 'yes_no', '0', '0', '', '3', '1'
		), (
		NULL , '12', 'Disable ''users viewing forum/topic''', 'If this is turned on then the ''users viewing forum/topic'' wont be generated and displayed.', 'cpu_disable_users_viewing', 'yes_no', '0', '0', '', '4', '1'
		), (
		NULL , '12', 'Show birthdays', 'If this is turned on then the birthdays of the current day will be shown on the main page.', 'cpu_show_birthdays', 'yes_no', '1', '1', '', '5', '1'
		);";
?>
