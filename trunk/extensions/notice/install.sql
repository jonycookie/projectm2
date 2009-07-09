CREATE TABLE `cms_notice` (
  `nid` smallint(6) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `postdate` int(10) NOT NULL,
  `author` varchar(50) NOT NULL,
  PRIMARY KEY  (`nid`)
) TYPE=MyISAM;