CREATE TABLE `cms_wordfilter` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `srcword` varchar(100) NOT NULL default '',
  `tarword` varchar(100) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;