-- MySQL Changes - 06/09/2006
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '2', 'Log SQL Commands?', 'Enabling this will log every SQL command run. This will add additional load to the server and should only be turned on if you''re concerned about security or for debugging purposes.', 'log_sql_commands', 'yes_no', '1', '0', '', '5', '1'
);

-- MySQL Changes - 06/17/2006
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Use word verification when posting?', 'Do you wish to require word verification for guests that are posting? Please note that if word verification is disabled above, then this setting will have no effect.', 'use_word_verification_posting', 'yes_no', '1', '1', '', '4', '1'
);

-- MySQL Changes - 06/21/2006
INSERT INTO `icebb_settings_sections` VALUES (11, 'Trash Can', 'Set up a trash can to store deleted posts', 11, 0);
INSERT INTO `icebb_settings` VALUES (32, 11, 'Use trash can?', 'If disabled, these options will have no effect', 'use_trash_can', 'yes_no', '1', '0', '', 1, 1);
INSERT INTO `icebb_settings` VALUES (33, 11, 'Trash can forum ID', 'Enter the forum ID here. Note to monkey: replace this with a dropdown', 'trash_can_forum', 'forum_select', '10', '0', '', 1, 1);

-- MySQL Changes - 06/22/2006

CREATE TABLE `icebb_failed_login_attempt_block` (
`id` TINYINT( 11 ) NOT NULL AUTO_INCREMENT ,
`ip` VARCHAR ( 15 ) NOT NULL ,
`time` INT( 32 ) NOT NULL ,
UNIQUE (
`id`
)
) ENGINE = MYISAM ;

ALTER TABLE `icebb_failedlogin_attempts` ADD `attempt_where` ENUM( 'acc', 'board' ) NOT NULL ;

INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Activate failed login attempts?', 'If enabled IceBB will check if an IP has X failed login attempts within X minutes. If the IP has it will be blocked for X minutes.', 'failed_login_attempts', 'yes_no', '1', '1', '', '0', '1'
);

INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Max failed login attempts', 'This setting controls how many attempts the user have to enter his/her correct password before getting IP blocked for X minutes.', 'failed_login_attempts_attempts', 'input', '5', '5', '', '0', '1'
);

INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Failed login attempts check range', 'This controls how many minutes back the IceBB checks for failed attempts.', 'failed_login_attempts_check_range', 'input', '30', '30', '', '0', '1'
);

INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Failed login attempts block time', 'This controls how many minutes an IP will be blocked if it has too many failed login attempts.', 'failed_login_attempts_check_block_time', 'input', '30', '30', '', '0', '1'
);


-- MySQL Changes - 06/24/2006
ALTER TABLE `icebb_groups` ADD `g_flood_control` INT( 32 ) NOT NULL ;
ALTER TABLE `icebb_users` ADD `last_post` INT( 32 ) NOT NULL ;

-- MySQL Changes - 06/29/2006
ALTER TABLE `icebb_users` ADD `away` ENUM( '0', '1' ) NOT NULL ,
ADD `away_reason` TEXT NOT NULL ;

-- MySQL Changes - 07/07/2006
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Type of word verification to use?', 'Either ''gd'' or ''imagemagick'' (Note to monkey: replace with dropdown)', 'img_engine', 'dropdown[gd|imagemagick]', 'gd', 'gd', '', '3', '1'
);

-- MySQL Changes - 07/08/2006
ALTER TABLE `icebb_users` CHANGE `langid` `langid` VARCHAR( 5 ) NOT NULL DEFAULT '0' ;

-- MySQL Changes - 07/10/2006
UPDATE `icebb_settings` SET `setting_group` = '2',
`setting_sort` = '6' WHERE `setting_id` =38 LIMIT 1 ;

INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '2', 'Path to ImageMagick convert binary', 'You can ignore this if you have GD set above', 'imagemagick_convert_path', 'input', '/usr/bin/convert', '/usr/bin/convert', '', '7', '1'
);

UPDATE `icebb_settings` SET `setting_desc` = 'Choose GD if unsure. If word verification doesn''t work using either option, then disable it under Security.',
`setting_type` = 'dropdown',
`setting_php` = 'gd:GD
imagemagick:ImageMagick' WHERE `setting_id` =38 LIMIT 1 ;

-- MySQL Changes - 07/10/2006
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '4', 'Enable registration?', 'Do you want to allow users to register on their own?', 'enable_registration', 'yes_no', '1', '1', '', '1', '1'
);

UPDATE `icebb_settings` SET `setting_sort` = '2' WHERE `setting_id` =10 LIMIT 1 ;

-- MySQL Changes - 07/10/2006
ALTER TABLE `icebb_langs` DROP `lang_bits_cache` ;

-- MySQL Changes - 08/06/2006
ALTER TABLE `icebb_users` ADD `buddies` TEXT NOT NULL ;

-- MySQL Changes - 08/07/2006
ALTER TABLE `icebb_moderators` ADD `m_is_group` ENUM( '0', '1' ) NOT NULL DEFAULT '0';

-- MySQL Changes - 08/14/2006
CREATE TABLE `icebb_buddies` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL default '0',
  `type` tinyint(1) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Buddies and blocked users' ;

-- MySQL Changes - 08/23/2006
ALTER TABLE `icebb_tags` ADD `owner` INT( 11 ) NOT NULL ;

-- MySQL Changes - 08/23/2006
DROP TABLE icebb_plugins;
CREATE TABLE `icebb_plugins` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='IceBB Plugins' ;

-- MySQL Changes - 08/25/2006
ALTER TABLE `icebb_users` ADD `quick_edit` TINYINT( 1 ) NOT NULL AFTER `editor_style` ;

-- sometime between 8/25 and 9/9:
ALTER TABLE `icebb_users` CHANGE `gender` `gender` ENUM( 'm', 'f', 'u' ) NOT NULL DEFAULT 'u';

-- no idea when this was added, sorry
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL, '2', 'Redirect notice type', 'Set how people will be noticed about a redirection.', 'redirection_type', 'dropdown', 'js', 'html', 'silent:Don''t display a message\r\nhtml:On a redirection page\r\njs:Using javascript', '0', '1'
);

-- 9/9/06 - default editor setting
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '2', 'Default editor', 'Which editor would you like to be used as the default?', 'default_editor_style', 'dropdown', '2', '2', '1:WYSIWYG Editor\r\n2:Extended Editor\r\n3:Basic Editor', '8', '1'
);
ALTER TABLE `icebb_users` CHANGE `editor_style` `editor_style` INT( 1 ) NOT NULL DEFAULT '0';
UPDATE `icebb_users` SET `gender` = 'u',`editor_style` = '0' WHERE `id` =0 LIMIT 1 ;

-- 9/10/06 - add option to disable debug info
INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '3', 'Show debug info?', 'Do you wish to show the debug info at the bottom of each page?', 'enable_debug', 'yes_no', '1', '1', '', '9', '1'
);
UPDATE `icebb_settings` SET `setting_sort` = '3' WHERE `setting_id` =25 LIMIT 1 ;
UPDATE `icebb_settings` SET `setting_sort` = '5' WHERE `setting_id` =34 LIMIT 1 ;
UPDATE `icebb_settings` SET `setting_sort` = '6' WHERE `setting_id` =35 LIMIT 1 ;
UPDATE `icebb_settings` SET `setting_sort` = '7' WHERE `setting_id` =36 LIMIT 1 ;
UPDATE `icebb_settings` SET `setting_sort` = '8' WHERE `setting_id` =37 LIMIT 1 ;

-- 9/11/06 - add option to not show smileys
ALTER TABLE `icebb_users` ADD `view_smileys` ENUM( '0', '1' ) NOT NULL DEFAULT '1';
UPDATE `icebb_users` SET `view_smileys` = '1';

-- 9/23/06 - bunch of macro updates - RECACHE OF MACROS REQUIRED!
TRUNCATE TABLE icebb_skin_macros;
INSERT INTO `icebb_skin_macros` (`id`, `skin_id`, `string`, `replacement`) VALUES (1, 1, 'QUICK_REPLY', '<img src=''skins/<#SKIN#>/images/q_reply.png'' alt=''Quick Reply'' />'),
(2, 1, 'ADD_REPLY', '<img src=''skins/<#SKIN#>/images/add_reply.png'' alt=''Add Reply'' />'),
(3, 1, 'NEW_TOPIC', '<img src=''skins/<#SKIN#>/images/new_topic.png'' alt=''New Topic'' />'),
(4, 1, 'ADD_REPLY_LOCKED', '<img src=''skins/<#SKIN#>/images/closed.png'' alt=''Topic Locked'' />'),
(5, 1, 'P_EDIT', '<img src=''skins/<#SKIN#>/images/edit.png'' alt=''edit'' />'),
(6, 1, 'P_DELETE', '<img src=''skins/<#SKIN#>/images/delete.png'' alt=''X'' />'),
(44, 1, 'CAT_ICON', '<img style=''padding-right:2px'' src=''skins/<#SKIN#>/images/catPaper.png'' alt='''' />'),
(9, 1, 'PLUS', '<img src=''skins/<#SKIN#>/images/plus.png'' border=''0'' alt=''+'' />'),
(10, 1, 'MINUS', '<img src=''skins/<#SKIN#>/images/minus.png'' border=''0'' alt=''-'' />'),
(11, 1, 'P_REPORT', '<img src=''skins/<#SKIN#>/images/report.png'' alt=''Report post'' />'),
(12, 1, 'P_REPLY', '<img src=''skins/<#SKIN#>/images/reply.png'' alt=''Reply'' />'),
(13, 1, 'T_NONEW', '<img src=''skins/<#SKIN#>/images/t_nonew.png'' alt=''No new posts'' title=''No new posts'' />'),
(14, 1, 'T_NEW', '<img src=''skins/<#SKIN#>/images/t_new.png'' alt=''New Post'' />'),
(15, 1, 'T_LOCKED', '<img src=''skins/<#SKIN#>/images/t_lock.png'' alt=''Locked'' title=''Locked'' />'),
(16, 1, 'F_NONEW', '<img src=''skins/<#SKIN#>/images/f_nonew.png'' alt=''No new posts in this forum'' title=''No new posts in this forum'' />'),
(17, 1, 'F_NEW', '<img src=''skins/<#SKIN#>/images/f_new.png'' alt=''New posts in this forum'' title=''New posts in this forum'' />'),
(18, 1, 'F_REDIRECT', '<img src=''skins/<#SKIN#>/images/f_redirect.png'' alt=''Redirect'' />'),
(19, 1, 'PIP', '<img src=''skins/<#SKIN#>/images/pip.png'' alt=''*'' />'),
(20, 1, 'STAR', '<img src=''skins/<#SKIN#>/images/star.png'' alt=''*'' />'),
(21, 1, 'STAR_OFF', '<img src=''skins/<#SKIN#>/images/star_off.png'' alt='''' />'),
(22, 1, 'T_HOTNEW', '<img src=''skins/<#SKIN#>/images/t_hotnew.png'' alt=''Hot topic - new posts'' title=''Hot topic - no new posts'' />'),
(23, 1, 'T_HOT', '<img src=''skins/<#SKIN#>/images/t_hot.png'' alt=''Hot topic - no new posts'' title=''Hot topic - no new posts'' />'),
(24, 1, 'loading_ani', '<img src=''skins/<#SKIN#>/images/loading.png'' alt='''' />'),
(43, 1, 'RSS_ICON', '<img src=''skins/<#SKIN#>/images/feed-icon.png'' alt=''RSS Feed available'' />');

-- 9/27/06 - help improvements
CREATE TABLE `icebb_help_sections` (
`hsid` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 255 ) NOT NULL
) COMMENT = 'IceBB help sections';

-- 9/28/06 - group changes
UPDATE icebb_groups SET g_status=0 WHERE gid=1;

-- 12/17/06 - failed login attempts cleanup
UPDATE `icebb_settings` SET `setting_desc` = 'How long does the user have to wait to try to login again?' WHERE `icebb_settings`.`setting_id` =37 LIMIT 1 ;
DELETE FROM icebb_settings WHERE setting_id=35;
UPDATE `icebb_settings` SET `setting_title` = 'Account lockdown attempts',
`setting_desc` = 'This setting controls how many attempts members have to correctly enter their password. If they exceed this number, then their IP address will be locked down temporarily.' WHERE `icebb_settings`.`setting_id` =36 LIMIT 1 ;

-- 2/16/07 - FS#225
UPDATE `icebb_settings` SET `setting_title` = 'Trash can forum',
`setting_desc` = 'Select the forum you wish to use as a trash can. This has no effect if the above option is set to &quot;no.&quot;' WHERE `icebb_settings`.`setting_id` =33 LIMIT 1 ;