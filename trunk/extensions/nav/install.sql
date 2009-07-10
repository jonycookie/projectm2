CREATE TABLE `cms_nav` (
  `nid` smallint(4) NOT NULL auto_increment,
  `title` char(50) NOT NULL,
  `style` char(50) NOT NULL,
  `link` char(100) NOT NULL,
  `alt` char(50) NOT NULL,
  `pos` char(10) NOT NULL,
  `target` tinyint(1) NOT NULL,
  `view` smallint(4) NOT NULL,
  PRIMARY KEY  (`nid`)
) TYPE=MyISAM;

INSERT INTO `cms_nav` (`nid`, `title`, `style`, `link`, `alt`, `pos`, `target`, `view`) VALUES
(1, 'PHPWind', '|||', 'http://www.phpwind.net', 'PHPWind官方', 'foot', 1, 0),
(2, 'UU1001', '|||', 'http://www.uu1001.com', 'UU1001免费论坛', 'foot', 1, 0),
(3, 'CMS', '|||', 'http://www.cms.net', 'CMS官方', 'foot', 0, 0),
(4, 'CMS', 'red|1||', 'http://www.cms.com', '官方演示站', 'head', 1, 1),
(6, 'PHPWind', 'blue||1|', 'http://www.phpwind.net', 'PHPWind论坛', 'head', 1, 2);