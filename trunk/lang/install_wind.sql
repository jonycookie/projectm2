DROP TABLE IF EXISTS `cms_admin`;
CREATE TABLE `cms_admin` (
  `uid` tinyint(2) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,
  `logintime` int(10) NOT NULL default '0',
  `ip` varchar(20) NOT NULL,
  `priv` text NOT NULL,
  `privcate` text NOT NULL,
  `email` varchar(30) NOT NULL,
  `loginfail` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_adposition`;
CREATE TABLE `cms_adposition` (
  `pid` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `inad` varchar(255) NOT NULL,
  `showtype` tinyint(2) NOT NULL,
  `jsname` varchar(255) NOT NULL,
  `active` tinyint(2) NOT NULL,
  `intro` mediumtext NOT NULL,
  `setting` mediumtext NOT NULL,
  `width` mediumint(8) NOT NULL,
  `height` mediumint(8) NOT NULL,
  PRIMARY KEY  (`pid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_advert`;
CREATE TABLE `cms_advert` (
  `adid` int(10) unsigned NOT NULL auto_increment,
  `cid` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `intro` mediumtext NOT NULL,
  `config` mediumtext NOT NULL,
  `pid` mediumint(10) NOT NULL,
  `countview` int(8) NOT NULL,
  `views` tinyint(1) NOT NULL,
  `countclick` int(8) NOT NULL,
  `clicks` tinyint(1) NOT NULL,
  `linkurl` varchar(255) NOT NULL,
  `linktarget` varchar(255) NOT NULL,
  `linkalt` varchar(255) NOT NULL,
  `starttime` int(10) NOT NULL,
  `endtime` int(10) NOT NULL,
  PRIMARY KEY  (`adid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_attach`;
CREATE TABLE `cms_attach` (
  `aid` int(10) NOT NULL auto_increment,
  `type` varchar(20) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `fileintro` varchar(255) NOT NULL,
  `size` int(10) NOT NULL,
  `uploadtime` int(10) NOT NULL,
  `filesrc` varchar(255) NOT NULL,
  `isftp` tinyint(1) NOT NULL,
  PRIMARY KEY  (`aid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_attachindex`;
CREATE TABLE `cms_attachindex` (
  `mid` smallint(6) NOT NULL,
  `tid` int(10) NOT NULL,
  `aid` int(10) NOT NULL,
  KEY `mid` (`mid`,`tid`,`aid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_category`;
CREATE TABLE `cms_category` (
  `cid` smallint(6) unsigned NOT NULL auto_increment,
  `cname` varchar(50) NOT NULL,
  `up` smallint(6) NOT NULL,
  `depth` tinyint(2) NOT NULL,
  `mid` smallint(6) NOT NULL,
  `path` varchar(50) NOT NULL,
  `htmlpub` tinyint(1) NOT NULL,
  `listpub` tinyint(1) NOT NULL,
  `comment` tinyint(1) NOT NULL,
  `copyctrl` tinyint(1) NOT NULL,
  `autopub` tinyint(1) NOT NULL,
  `autoupdate` smallint(6) NOT NULL,
  `tpl_index` varchar(255) NOT NULL,
  `tpl_content` varchar(255) NOT NULL,
  `file_index` varchar(255) NOT NULL,
  `file_content` varchar(255) NOT NULL,
  `taxis` tinyint(2) NOT NULL,
  `total` mediumint(8) unsigned NOT NULL,
  `new` mediumint(8) unsigned NOT NULL,
  `link` varchar(50) NOT NULL,
  `addtion` varchar(255) NOT NULL,
  `listurl` varchar(255) NOT NULL,
  `metakeyword` varchar(255) NOT NULL,
  `metadescrip` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY  (`cid`),
  KEY `up` (`up`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_collection`;
CREATE TABLE `cms_collection` (
  `id` int(10) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  `md5url` varchar(255) NOT NULL,
  `gathertime` int(10) NOT NULL,
  `gid` smallint(6) NOT NULL,
  `tid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `md5url` (`md5url`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_comment`;
CREATE TABLE `cms_comment` (
  `id` int(10) NOT NULL auto_increment,
  `mid` smallint(6) NOT NULL,
  `cid` smallint(6) NOT NULL,
  `tid` int(10) NOT NULL,
  `author` varchar(50) NOT NULL,
  `message` mediumtext NOT NULL,
  `father` int(10) NOT NULL,
  `postdate` int(10) NOT NULL,
  `fromip` varchar(50) NOT NULL,
  `hideip` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`),
  KEY `cid` (`cid`,`tid`),
  KEY `author` (`author`),
  KEY `father` (`father`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_commentface`;
CREATE TABLE `cms_commentface` (
  `id` smallint(6) NOT NULL auto_increment,
  `facepath` varchar(255) NOT NULL,
  `faceintro` varchar(50) NOT NULL,
  `taxis` smallint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_config`;
CREATE TABLE `cms_config` (
  `db_name` varchar(30) NOT NULL default '',
  `db_value` text NOT NULL,
  `decrip` text NOT NULL,
  PRIMARY KEY  (`db_name`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_const`;
CREATE TABLE `cms_const` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `title` varchar(250) default NULL,
  `name` varchar(50) default NULL,
  `value` mediumtext,
  `type` varchar(30) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_content1`;
CREATE TABLE `cms_content1` (
  `tid` mediumint(8) unsigned NOT NULL auto_increment,
  `content` mediumtext NOT NULL,
  `intro` mediumtext NOT NULL,
  `author` varchar(255) NOT NULL,
  `fromsite` varchar(255) NOT NULL,
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_content2`;
CREATE TABLE `cms_content2` (
  `tid` mediumint(8) unsigned NOT NULL auto_increment,
  `linkorder` smallint(2) NOT NULL,
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_contentindex`;
CREATE TABLE `cms_contentindex` (
  `tid` int(10) NOT NULL auto_increment,
  `cid` smallint(6) NOT NULL,
  `mid` smallint(6) NOT NULL,
  `title` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `postdate` int(10) NOT NULL,
  `linkurl` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `fpageurl` mediumtext NOT NULL,
  `ifpub` tinyint(1) NOT NULL,
  `fpage` smallint(4) NOT NULL,
  `digest` tinyint(2) NOT NULL,
  `hits` smallint(6) NOT NULL,
  `comnum` smallint(6) NOT NULL,
  `publisher` varchar(50) NOT NULL,
  `template` varchar(255) NOT NULL,
  `titlestyle` varchar(255) NOT NULL,
  PRIMARY KEY  (`tid`),
  KEY `mid` (`mid`),
  KEY `cid` (`cid`),
  KEY `postdate` (`postdate`),
  KEY `comnum` (`comnum`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_contenttag`;
CREATE TABLE `cms_contenttag` (
  `tid` int(10) NOT NULL,
  `tagid` int(10) NOT NULL,
  `mid` smallint(6) NOT NULL,
  KEY `tid` (`tid`,`tagid`,`mid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_extension`;
CREATE TABLE `cms_extension` (
  `name` varchar(30) NOT NULL default '',
  `value` text NOT NULL,
  `decrip` text NOT NULL,
  PRIMARY KEY  (`name`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_field`;
CREATE TABLE `cms_field` (
  `fid` mediumint(6) NOT NULL auto_increment,
  `mid` smallint(6) NOT NULL,
  `fieldid` varchar(20) NOT NULL,
  `fieldname` varchar(50) NOT NULL,
  `fieldtype` varchar(50) NOT NULL,
  `fieldsize` varchar(50) NOT NULL,
  `inputtype` varchar(50) NOT NULL,
  `inputsize` smallint(4) NOT NULL,
  `getvalue` tinyint(2) NOT NULL default '0',
  `defaultvalue` varchar(255) NOT NULL,
  `inputlabel` varchar(255) NOT NULL,
  `ifgather` tinyint(1) NOT NULL,
  `vieworder` tinyint(4) NOT NULL,
  `ifindex` tinyint(1) NOT NULL,
  `ifsearch` tinyint(1) NOT NULL,
	`ifcontribute` tinyint(1) NOT NULL,
  PRIMARY KEY  (`fid`),
  KEY `mid` (`mid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_gather`;
CREATE TABLE `cms_gather` (
  `gid` smallint(6) NOT NULL auto_increment,
  `mid` smallint(6) unsigned NOT NULL,
  `gname` varchar(100) NOT NULL,
  `multi` tinyint(1) NOT NULL,
  `fromurl` varchar(255) NOT NULL,
  `listarea` mediumtext NOT NULL,
  `listurl` varchar(255) NOT NULL,
  `startpage` smallint(6) NOT NULL,
  `endpage` smallint(6) NOT NULL,
  `contenturl` mediumtext NOT NULL,
  `debarurl` mediumtext NOT NULL,
  `pageurl` mediumtext NOT NULL,
  `tags` varchar(255) NOT NULL,
  `fieldrule` mediumtext NOT NULL,
  `ifclearhtml` mediumtext NOT NULL,
  `clearhtml` mediumtext NOT NULL,
  `imgtolocal` mediumtext NOT NULL,
  `filtreit` tinyint(1) NOT NULL,
  `ignoretime` smallint(6) NOT NULL,
  `str1` mediumtext NOT NULL,
  `str2` mediumtext NOT NULL,
  `type` smallint(6) unsigned NOT NULL,
  `bindcid` smallint(6) unsigned NOT NULL,
  PRIMARY KEY  (`gid`),
  KEY `mid` (`mid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_help`;
CREATE TABLE `cms_help` (
  `hid` smallint(6) unsigned NOT NULL auto_increment,
  `hup` smallint(6) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `content` mediumtext NOT NULL,
  PRIMARY KEY  (`hid`),
  KEY `hup` (`hup`),
  KEY `title` (`title`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_module`;
CREATE TABLE `cms_module` (
  `mid` smallint(6) NOT NULL auto_increment,
  `mname` varchar(50) NOT NULL,
  `author` varchar(255) NOT NULL,
  `descrip` varchar(255) NOT NULL,
  `search` tinyint(1) NOT NULL,
  PRIMARY KEY  (`mid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_nav`;
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

DROP TABLE IF EXISTS `cms_notice`;
CREATE TABLE `cms_notice` (
  `nid` smallint(6) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `postdate` int(10) NOT NULL,
  `author` varchar(50) NOT NULL,
  PRIMARY KEY  (`nid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_polls`;
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

DROP TABLE IF EXISTS `cms_recycle`;
CREATE TABLE `cms_recycle` (
  `tid` int(10) NOT NULL,
  `cid` smallint(6) NOT NULL,
  `deltime` int(10) NOT NULL,
  `admin` varchar(30) NOT NULL
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_schcache`;
CREATE TABLE `cms_schcache` (
  `sid` mediumint(8) unsigned NOT NULL auto_increment,
  `sorderby` varchar(50) NOT NULL,
  `schkeyword` varchar(80) NOT NULL default '',
  `schtime` int(10) unsigned NOT NULL default '0',
  `total` mediumint(8) unsigned NOT NULL default '0',
  `schvalue` text NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `schline` (`schkeyword`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_select`;
CREATE TABLE `cms_select` (
  `selectid` smallint(4) NOT NULL auto_increment,
  `selectname` varchar(50) NOT NULL,
  PRIMARY KEY  (`selectid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_selectvalue`;
CREATE TABLE `cms_selectvalue` (
  `valueid` smallint(6) NOT NULL auto_increment,
  `selectid` smallint(4) NOT NULL,
  `usetime` int(10) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY  (`valueid`),
  KEY `selectid` (`selectid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_tags`;
CREATE TABLE `cms_tags` (
  `tagid` smallint(6) NOT NULL auto_increment,
  `tagname` varchar(50) NOT NULL,
  `num` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`tagid`),
  KEY `tagname` (`tagname`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `cms_wordfilter`;
CREATE TABLE `cms_wordfilter` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `srcword` varchar(100) NOT NULL default '',
  `tarword` varchar(100) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

INSERT INTO `cms_adposition` (`pid`, `name`, `type`, `inad`, `showtype`, `jsname`, `active`, `intro`, `setting`, `width`, `height`) VALUES
(1, '头部横幅广告', 'banner', '', 1, 'head', 1, '头部的横幅广告图片', 'a:8:{s:9:"floattype";s:1:"1";s:7:"poptype";s:1:"1";s:8:"showtype";i:1;s:4:"left";i:0;s:3:"top";i:0;s:5:"delta";d:0.15;s:5:"delay";i:50;s:10:"cookiehour";i:0;}', 950, 80),
(2, '首页中间栏', 'banner', '', 1, 'mid', 1, '', 'a:8:{s:9:"floattype";s:1:"1";s:8:"showtype";i:1;s:4:"left";i:0;s:3:"top";i:0;s:5:"delta";d:0.15;s:7:"poptype";i:1;s:5:"delay";i:50;s:10:"cookiehour";i:0;}', 950, 80),
(3, '广告位3', 'banner', '', 1, 'left', 1, '', 'a:8:{s:9:"floattype";s:1:"1";s:7:"poptype";s:1:"1";s:8:"showtype";i:1;s:4:"left";i:0;s:3:"top";i:0;s:5:"delta";d:0.15;s:5:"delay";i:50;s:10:"cookiehour";i:0;}', 175, 220);

INSERT INTO `cms_advert` (`adid`, `cid`, `type`, `name`, `priority`, `intro`, `config`, `pid`, `countview`, `views`, `countclick`, `clicks`, `linkurl`, `linktarget`, `linkalt`, `starttime`, `endtime`) VALUES 
(1, '0,2,1,6,3,4,5', 'img', '头部Banner', 1, '', 'a:8:{s:3:"url";s:36:"http://www.phpwind.com/vc/uu1001.jpg";s:5:"width";s:3:"950";s:6:"height";s:2:"80";s:10:"flashwmode";s:1:"0";s:7:"linkurl";s:0:"";s:10:"linktarget";s:0:"";s:7:"linkalt";s:0:"";s:8:"priority";s:1:"1";}', 1, 0, 0, 0, 0, 'http://www.uu1001.com/', '1', '', 1207008000, 1238544000),
(4, '0', 'img', '首页中间图片', 1, '', 'a:8:{s:3:"url";s:20:"images/verycmsv3.jpg";s:5:"width";s:3:"950";s:6:"height";s:2:"80";s:10:"flashwmode";s:1:"0";s:7:"linkurl";s:0:"";s:10:"linktarget";s:0:"";s:7:"linkalt";s:0:"";s:8:"priority";s:1:"1";}', 2, 0, 0, 0, 0, '', '1', '', 1207008000, 1238544000),
(5, '0', 'img', 'what', 1, '', 'a:8:{s:3:"url";s:15:"images/what.jpg";s:5:"width";s:3:"175";s:6:"height";s:3:"220";s:10:"flashwmode";s:1:"0";s:7:"linkurl";s:0:"";s:10:"linktarget";s:0:"";s:7:"linkalt";s:0:"";s:8:"priority";s:1:"1";}', 3, 0, 0, 0, 0, '', '1', '', 1207008000, 1238544000);

INSERT INTO `cms_category` (`cid`, `cname`, `up`, `depth`, `mid`, `path`, `htmlpub`, `listpub`, `comment`, `copyctrl`, `autopub`, `autoupdate`, `tpl_index`, `tpl_content`, `file_index`, `file_content`, `taxis`, `total`, `new`, `link`, `addtion`, `listurl`, `metakeyword`, `metadescrip`, `description`, `type`) VALUES
(1, '国际', 0, 1, 1, '', 1, 0, 1, 0, 0, 0, '', '', '', '', 7, 0, 0, '', '', '', '', '', '', 1),
(2, '国内', 0, 1, 1, '', 1, 0, 1, 0, 1, 0, '', '', '', '', 8, 0, 0, '', '', '', '', '', '', 1),
(3, '娱乐', 0, 1, 1, '', 1, 0, 1, 0, 0, 0, '', '', '', '', 5, 0, 0, '', '', '', '', '', '', 1),
(4, '体育', 0, 1, 1, '', 1, 0, 1, 0, 0, 0, '', '', '', '', 4, 0, 0, '', '', '', '', '', '', 1),
(5, '财经', 0, 1, 1, '', 1, 0, 1, 0, 0, 0, '', '', '', '', 3, 0, 0, '', '', '', '', '', '', 1),
(6, '科技', 0, 1, 1, '', 1, 0, 1, 0, 0, 0, '', '', '', '', 6, 0, 0, '', '', '', '', '', '关注科技动态，紧随IT潮流', 1),
(7, '友情链接', 0, 1, 2, '', 0, 0, 1, 0, 0, 0, '', '', '', '', 1, 1, 0, '', '', '', '', '', '', 0),
(8, 'PHPWind', 0, 1, 0, '', 1, 1, 0, 0, 0, 0, '', '', '', '', 2, 0, 0, 'http://www.phpwind.net', '', '', '', '', '', 1);

INSERT INTO `cms_commentface` (`id`, `facepath`, `faceintro`, `taxis`) VALUES
(1, '1.gif', '', 0),
(2, '10.gif', '', 0),
(3, '11.gif', '', 0),
(4, '12.gif', '', 0),
(5, '13.gif', '', 0),
(6, '14.gif', '', 0),
(7, '15.gif', '', 0),
(8, '16.gif', '', 0),
(9, '17.gif', '', 0),
(10, '18.gif', '', 0),
(11, '19.gif', '', 0),
(12, '2.gif', '', 0),
(13, '20.gif', '', 0),
(14, '21.gif', '', 0),
(15, '22.gif', '', 0),
(16, '23.gif', '', 0),
(17, '24.gif', '', 0),
(18, '25.gif', '', 0),
(19, '26.gif', '', 0),
(20, '27.gif', '', 0),
(21, '28.gif', '', 0),
(22, '29.gif', '', 0),
(23, '3.gif', '', 0),
(24, '30.gif', '', 0),
(25, '4.gif', '', 0),
(26, '5.gif', '', 0),
(27, '6.gif', '', 0),
(28, '7.gif', '', 0),
(29, '8.gif', '', 0),
(30, '9.gif', '', 0);

INSERT INTO `cms_config` (`db_name`, `db_value`, `decrip`) VALUES
('db_discate', '2,1,6,3,4,5', ''),
('db_lang', 'utf-8', ''),
('db_htmdir', 'www', ''),
('db_htmext', 'html', ''),
('db_htmmkdir', '2', ''),
('db_attachdir', 'attachment', ''),
('db_attachmkdir', '4', ''),
('db_loginip', '', ''),
('db_timedf', '8', ''),
('db_cvtime', '0', ''),
('db_datefm', 'Y-m-d H:i', ''),
('db_title', 'VeryCMS v3.3', ''),
('db_searchtime', '5', ''),
('db_searchmax', '100', ''),
('db_listpage', '', ''),
('db_rewrite', '0', ''),
('db_rewrite_dir', '.php?', ''),
('db_rewrite_ext', '.html', ''),
('db_rss_itemnum', '30', ''),
('db_rss_imagenum', '5', ''),
('db_rss_update', '15', ''),
('db_perpage', '5', ''),
('db_indexupdate', '1', ''),
('db_ifftp', '0', ''),
('db_bbs_forumsort', 'tpost', ''),
('db_bbs_membersort', 'todaypost', ''),
('db_blog_membersort', 'blogs', ''),
('db_blog_tagsort', 'blognum', '');

INSERT INTO `cms_const` (`id`, `title`, `name`, `value`, `type`) VALUES
(1, '新闻资讯', 'MID_news', '1', 'MID'),
(2, '友情链接', 'MID_links', '2', 'MID'),
(3, 'EXT_navhead', 'EXT_navhead', '&lt;script language=&quot;javascript&quot; src=&quot;script/verycms/nav_head.js?cid={$cid}&tid={$tid}&quot;&gt;&lt;/script&gt;', 'EXT'),
(4, 'EXT_navfoot', 'EXT_navfoot', '&lt;script language=&quot;javascript&quot; src=&quot;script/verycms/nav_foot.js?cid={$cid}&tid={$tid}&quot;&gt;&lt;/script&gt;', 'EXT');

INSERT INTO `cms_content2` (`tid`, `linkorder`) VALUES
(1, 99);

INSERT INTO `cms_contentindex` (`tid`, `cid`, `mid`, `title`, `photo`, `postdate`, `linkurl`, `url`, `fpageurl`, `ifpub`, `fpage`, `digest`, `hits`, `comnum`, `publisher`, `template`, `titlestyle`) VALUES
(1, 7, 2, 'PHPWind', 'http://www.phpwind.net/logo.gif', 1175831154, 'http://www.phpwind.net', '', '', 1, 0, 0, 0, 0, 'admin', '', '');

INSERT INTO `cms_field` (`fid`, `mid`, `fieldid`, `fieldname`, `fieldtype`, `fieldsize`, `inputtype`, `inputsize`, `getvalue`, `defaultvalue`, `inputlabel`, `ifgather`, `vieworder`, `ifindex`, `ifsearch`,`ifcontribute`) VALUES
(2, 1, 'title', '标题', 'varchar', '255', 'input', 70, 0, '', '', 1, 2, 1, 1,1),
(4, 1, 'content', '内容', 'mediumtext', '', 'edit', 20, 0, '', '', 1, 4, 0, 0,1),
(5, 1, 'intro', '内容摘要', 'text', '', 'basic', 20, 0, '', '', 0, 5, 0, 0,0),
(6, 1, 'author', '作者', 'varchar', '255', 'input', 20, 11, '', '', 0, 6, 0, 0,0),
(7, 1, 'fromsite', '新闻来源', 'varchar', '255', 'input', 20, 12, '', '', 0, 7, 0, 0,0),
(12, 2, 'title', '友情文字链接', 'varchar', '255', 'input', 20, 0, '', '', 0, 2, 1, 0,1),
(13, 2, 'linkorder', '显示权值', 'smallint', '2', 'input', 70, 0, '0', '按权值从大到小排序', 0, 99, 0, 0,0);

INSERT INTO `cms_gather` (`gid`, `mid`, `gname`, `multi`, `fromurl`, `listarea`, `listurl`, `startpage`, `endpage`, `contenturl`, `debarurl`, `pageurl`, `tags`, `fieldrule`, `ifclearhtml`, `clearhtml`, `imgtolocal`, `filtreit`, `ignoretime`, `str1`, `str2`, `type`) VALUES
(1, 1, 'Sina娱乐', 0, 'http://ent.sina.com.cn/star/mainland/more.html', '<tr><td colspan=2 class=f149>{DATA}</td></tr>', '', 0, 0, '', 'index', '', 'Sina,娱乐', 'a:2:{s:5:"title";s:37:"<title>{DATA}_影音娱乐_新浪网</title>";s:7:"content";s:44:"<!--正文内容开始-->{DATA}<!--正文内容结束-->";}', 'a:1:{s:7:"content";s:1:"1";}', 'a:2:{s:5:"title";a:1:{i:0;s:3:"img";}s:7:"content";a:4:{i:0;s:1:"p";i:1;s:2:"br";i:2;s:6:"center";i:3;s:3:"img";}}', 'N;', 0, 0, 'a:10:{i:1;s:0:"";i:2;N;i:3;N;i:4;N;i:5;N;i:6;N;i:7;N;i:8;N;i:9;N;i:10;N;}', 'a:10:{i:1;s:0:"";i:2;N;i:3;N;i:4;N;i:5;N;i:6;N;i:7;N;i:8;N;i:9;N;i:10;N;}', 0);

INSERT INTO `cms_help` (`hid`, `hup`, `title`, `content`) VALUES
(1, 0, 'content', '栏目内容管理'),
(2, 0, 'edit', '栏目内容编辑'),
(3, 0, 'category', '栏目结构管理'),
(4, 1, '内容精华', '对着星星区域点击鼠标右键，能够取消一篇内容的精华；而点击左键则能够设置一篇内容的精华。'),
(5, 1, '栏目设置', '如果栏目设置为静态，一定要发布内容才可以浏览。'),
(6, 1, '整合发布', '对于BBS/Blog等整合内容，如果设置了CMS站点浏览，而且为静态页面，需要把栏目内容发布一次。'),
(7, 2, '内容分页', '如果您要对一篇内容进行分页，可以利用编辑器中的分页按钮在您想要分页的地方插入标志。'),
(8, 2, '预设值管理器', '使用预设值管理器，系统可以自动保存您经常输入的信息，避免下次重复输入。'),
(9, 2, '自动提取内容图片', '如果您选择了自动提取内容中的图片，那么该图片将自动从内容中取出，只作为图片字段的内容出现。'),
(10, 3, '栏目内容模型', '一旦设置了一个栏目的内容模型，将无法再次进行编辑。'),
(11, 3, '系统负载', '如果没有特殊需要，推荐静态发布内容页和列表页，这将极大降低您的系统负载。'),
(12, 3, '系统负载', '如果您需要一个栏目首页自动更新，请设置自动更新时间，该时间数值不宜太低，这会给您的系统造成负担。');

INSERT INTO `cms_module` (`mid`, `mname`, `author`, `descrip`, `search`) VALUES
(1, '新闻资讯', 'PHPWind', '系统内嵌模型', 1),
(2, '友情链接', 'PHPWind', '系统内嵌模型', 0);

INSERT INTO `cms_nav` (`nid`, `title`, `style`, `link`, `alt`, `pos`, `target`, `view`) VALUES
(1, 'PHPWind', '|||', 'http://www.phpwind.net', 'PHPWind官方', 'foot', 1, 0),
(2, 'UU1001', '|||', 'http://www.uu1001.com', 'UU1001免费论坛', 'foot', 1, 0),
(3, 'VeryCMS', '|||', 'http://www.verycms.net', 'VeryCMS官方', 'foot', 0, 0),
(4, 'VeryCMS', 'red|1||', 'http://www.verycms.com', '官方演示站', 'head', 1, 1),
(6, 'PHPWind', 'blue||1|', 'http://www.phpwind.net', 'PHPWind论坛', 'head', 1, 2);

INSERT INTO `cms_select` (`selectid`, `selectname`) VALUES
(11, '常用作者'),
(12, '网址来源');