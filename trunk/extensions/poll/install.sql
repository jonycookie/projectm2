CREATE TABLE `cms_polls` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `options` mediumtext NOT NULL,
  `stats` int(10) unsigned NOT NULL,
  `ismulti` tinyint(1) NOT NULL,
  `stime` int(10) NOT NULL,
  `etime` int(10) NOT NULL,
  `mark` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `stime` (`stime`)
) TYPE=MyISAM;