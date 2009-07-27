-- phpMyAdmin SQL Dump
-- version 2.9.0
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2007 年 02 月 02 日 01:31
-- 服务器版本: 5.0.21
-- PHP 版本: 5.1.6
--
-- 数据库: 'supesite'
--

-- --------------------------------------------------------

--
-- 表的结构 'supe_ads'
--

DROP TABLE IF EXISTS supe_ads;
CREATE TABLE supe_ads (
  adid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '1',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  adtype enum('echo','js','iframe','text','code','image','flash') NOT NULL default 'text',
  pagetype varchar(20) NOT NULL default '',
  `type` mediumtext NOT NULL default '',
  parameters mediumtext NOT NULL,
  system tinyint(1) NOT NULL default '0',
  style varchar(30) NOT NULL default '',
  PRIMARY KEY  (adid),
  KEY system (system)
) TYPE=MyISAM;
-- --------------------------------------------------------

--
-- 表的结构 'supe_adminsession'
--

DROP TABLE IF EXISTS supe_adminsession;
CREATE TABLE supe_adminsession (
  uid mediumint(8) unsigned NOT NULL default '0',
  ip char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  errorcount tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MEMORY;

-- --------------------------------------------------------

--
-- 表的结构 'supe_announcements'
--

DROP TABLE IF EXISTS supe_announcements;
CREATE TABLE supe_announcements (
  id smallint(6) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  `subject` varchar(80) NOT NULL default '',
  announcementsurl varchar(255) NOT NULL default '',
  displayorder tinyint(3) unsigned NOT NULL default '0',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  PRIMARY KEY  (id),
  KEY timespan (starttime,endtime)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_attachments'
--

DROP TABLE IF EXISTS supe_attachments;
CREATE TABLE supe_attachments (
  aid mediumint(8) unsigned NOT NULL auto_increment,
  isavailable tinyint(1) NOT NULL default '0',
  `type` char(20) NOT NULL default '',
  itemid mediumint(8) unsigned NOT NULL default '0',
  catid smallint(6) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  filename char(150) NOT NULL default '',
  `subject` char(80) NOT NULL default '',
  attachtype char(10) NOT NULL default '',
  isimage tinyint(1) NOT NULL default '0',
  size int(10) unsigned NOT NULL default '0',
  filepath char(200) NOT NULL default '',
  thumbpath char(200) NOT NULL default '',
  downloads mediumint(8) unsigned NOT NULL default '0',
  `hash` char(16) NOT NULL default '',
  PRIMARY KEY  (aid),
  KEY `hash` (`hash`),
  KEY itemid (itemid),
  KEY uid (uid,`type`,dateline),
  KEY `type` (`type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_attachmenttypes'
--

DROP TABLE IF EXISTS supe_attachmenttypes;
CREATE TABLE supe_attachmenttypes (
  id smallint(6) unsigned NOT NULL auto_increment,
  fileext char(10) NOT NULL default '',
  maxsize int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_blocks'
--

DROP TABLE IF EXISTS supe_blocks;
CREATE TABLE supe_blocks (
  blockid smallint(6) unsigned NOT NULL auto_increment,
  dateline int(10) unsigned NOT NULL default '0',
  blocktype varchar(20) NOT NULL default '',
  blockname varchar(80) NOT NULL default '',
  blockmodel tinyint(1) NOT NULL default '1',
  blocktext text NOT NULL,
  blockcode text NOT NULL,
  PRIMARY KEY  (blockid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_cache'
--

DROP TABLE IF EXISTS supe_cache;
CREATE TABLE supe_cache (
  cachekey varchar(16) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  cachename varchar(20) NOT NULL default '',
  `value` mediumtext NOT NULL,
  updatetime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (cachekey)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_categories'
--

DROP TABLE IF EXISTS supe_categories;
CREATE TABLE supe_categories (
  catid smallint(6) unsigned NOT NULL auto_increment,
  upid smallint(6) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  note text NOT NULL,
  `type` varchar(15) NOT NULL default '',
  ischannel tinyint(1) NOT NULL default '0',
  displayorder mediumint(6) unsigned NOT NULL default '0',
  tpl varchar(80) NOT NULL default '',
  viewtpl varchar(80) NOT NULL default '',
  thumb varchar(150) NOT NULL default '',
  image varchar(150) NOT NULL default '',
  haveattach tinyint(1) NOT NULL default '0',
  bbsmodel tinyint(1) NOT NULL default '0',
  bbsurltype varchar(15) NOT NULL default '',
  blockmodel tinyint(1) NOT NULL default '1',
  blockparameter text NOT NULL,
  blocktext text NOT NULL,
  url varchar(255) NOT NULL default '',
  subcatid text NOT NULL,
  PRIMARY KEY  (catid),
  KEY `type` (`type`),
  KEY upid (upid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_channels'
--

DROP TABLE IF EXISTS supe_channels;
CREATE TABLE supe_channels (
  nameid char(30) NOT NULL default '',
  `name` char(50) NOT NULL default '',
  url char(200) NOT NULL default '',
  tpl char(50) NOT NULL default '',
  `type` char(20) NOT NULL default '',
  displayorder smallint(3) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (nameid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_crons'
--

DROP TABLE IF EXISTS supe_crons;
CREATE TABLE supe_crons (
  cronid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  `type` enum('user','system') NOT NULL default 'user',
  `name` char(50) NOT NULL default '',
  filename char(50) NOT NULL default '',
  lastrun int(10) unsigned NOT NULL default '0',
  nextrun int(10) unsigned NOT NULL default '0',
  weekday tinyint(1) NOT NULL default '0',
  `day` tinyint(2) NOT NULL default '0',
  `hour` tinyint(2) NOT NULL default '0',
  `minute` char(36) NOT NULL default '',
  PRIMARY KEY  (cronid),
  KEY nextrun (available,nextrun)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_customfields'
--

DROP TABLE IF EXISTS supe_customfields;
CREATE TABLE supe_customfields (
  customfieldid smallint(6) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `name` varchar(80) NOT NULL default '',
  displayorder smallint(6) unsigned NOT NULL default '0',
  customfieldtext text NOT NULL,
  isdefault tinyint(1) NOT NULL default '0',
  isshare tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (customfieldid),
  KEY uid (uid,`type`),
  KEY isshare (isshare,`type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_forums'
--

DROP TABLE IF EXISTS supe_forums;
CREATE TABLE supe_forums (
  fid mediumint(8) unsigned NOT NULL default '0',
  fup mediumint(8) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `type` enum('group','forum','sub') NOT NULL DEFAULT 'forum',
  allowshare tinyint(1) NOT NULL default '0',
  pushsetting text NOT NULL,
  updateline int(10) NOT NULL,
  displayorder tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (fid)
) TYPE=MyISAM;
-- --------------------------------------------------------

--
-- 表的结构 'supe_friendlinks'
--

DROP TABLE IF EXISTS supe_friendlinks;
CREATE TABLE supe_friendlinks (
    id smallint(6) unsigned NOT NULL auto_increment,
    displayorder tinyint(3) NOT NULL default '0',
    name varchar(100) NOT NULL default '',
    url varchar(100) NOT NULL default '',
    description varchar(100) NOT NULL default '',
    logo varchar(100) NOT NULL default '',
	PRIMARY KEY  (id)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_members'
--

DROP TABLE IF EXISTS supe_members;
CREATE TABLE supe_members (
  uid mediumint(8) unsigned NOT NULL default '0',
  groupid smallint(6) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  email char(100) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  lastlogin int(10) unsigned NOT NULL default '0',
  flag tinyint(1) unsigned NOT NULL default '0',
  ip char(15) NOT NULL default '',
  lastsearchtime int(10) unsigned NOT NULL default '0',
  lastcommenttime int(10) unsigned NOT NULL default '0',
  authstr char(20) NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_modelcolumns'
--

DROP TABLE IF EXISTS supe_modelcolumns;
CREATE TABLE supe_modelcolumns (
  id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  upid smallint(6) unsigned NOT NULL DEFAULT '0',
  mid smallint(6) unsigned NOT NULL DEFAULT '0',
  fieldname varchar(30) NOT NULL DEFAULT '',
  fieldcomment varchar(60) NOT NULL DEFAULT '',
  fieldtype varchar(20) NOT NULL DEFAULT '',
  fieldlength int(5) unsigned NOT NULL DEFAULT '0',
  fielddefault text NOT NULL,
  formtype varchar(20) NOT NULL DEFAULT '',
  fielddata text NOT NULL,
  displayorder tinyint(2) unsigned NOT NULL DEFAULT '0',
  allowindex tinyint(1) NOT NULL DEFAULT '0',
  allowshow tinyint(1) NOT NULL DEFAULT '0',
  allowlist tinyint(1) NOT NULL DEFAULT '0',
  allowsearch tinyint(1) NOT NULL DEFAULT '0',
  allowpost tinyint(1) NOT NULL DEFAULT '0',
  isfixed tinyint(1) NOT NULL DEFAULT '0',
  isbbcode tinyint(1) NOT NULL DEFAULT '0',
  ishtml tinyint(1) NOT NULL DEFAULT '0',
  isrequired tinyint(1) NOT NULL DEFAULT '0',
  isfile varchar(50) NOT NULL DEFAULT '',
  isimage varchar(50) NOT NULL DEFAULT '',
  isflash varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY mid (mid,displayorder)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_modelinterval'
--

DROP TABLE IF EXISTS supe_modelinterval;
CREATE TABLE supe_modelinterval (
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  `type` tinyint(1) unsigned NOT NULL default '0',
  KEY uid (uid,`type`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_models'
--

DROP TABLE IF EXISTS supe_models;
CREATE TABLE supe_models (
  mid smallint(6) unsigned NOT NULL auto_increment,
  modelname char(20) NOT NULL default '',
  modelalias char(60) NOT NULL default '',
  allowpost tinyint(1) NOT NULL default '0',
  allowguest tinyint(1) NOT NULL default '0',
  allowgrade tinyint(1) NOT NULL default '0',
  allowcomment tinyint(1) NOT NULL default '0',
  allowrate tinyint(1) NOT NULL default '0',
  allowguestsearch tinyint(1) NOT NULL default '0',
  allowfeed tinyint(1) NOT NULL default '1',
  searchinterval smallint(6) unsigned NOT NULL default '0',
  allowguestdownload tinyint(1) NOT NULL default '0',
  downloadinterval smallint(6) unsigned NOT NULL default '0',
  allowfilter tinyint(1) NOT NULL default '0',
  listperpage tinyint(3) unsigned NOT NULL default '0',
  seokeywords char(200) NOT NULL default '',
  seodescription char(200) NOT NULL default '',
  thumbsize char(19) NOT NULL default '',
  tpl char(20) NOT NULL default '',
  fielddefault char(255) NOT NULL default '',
  PRIMARY KEY  (mid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_polls'
--

DROP TABLE IF EXISTS supe_polls;
CREATE TABLE supe_polls (
  pollid smallint(6) unsigned NOT NULL auto_increment,
  pollnum mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  ismulti tinyint(1) NOT NULL default '0',
  `subject` varchar(80) NOT NULL default '',
  pollsurl varchar(255) NOT NULL default '',
  summary text NOT NULL,
  options text NOT NULL,
  voters text NOT NULL,
  PRIMARY KEY  (pollid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_prefields'
--

DROP TABLE IF EXISTS supe_prefields;
CREATE TABLE supe_prefields (
  id smallint(6) unsigned NOT NULL auto_increment,
  `type` char(10) NOT NULL default '',
  field char(20) NOT NULL default '',
  `value` char(50) NOT NULL default '',
  isdefault tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY `type` (`type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_reports'
--

DROP TABLE IF EXISTS supe_reports;
CREATE TABLE supe_reports (
  reportid mediumint(8) unsigned NOT NULL auto_increment,
  itemid mediumint(8) unsigned NOT NULL default '0',
  reportuid mediumint(8) unsigned NOT NULL default '0',
  reporter char(15) NOT NULL default '',
  reportdate int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (reportid),
  KEY itemid (itemid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_robotitems'
--

DROP TABLE IF EXISTS supe_robotitems;
CREATE TABLE supe_robotitems (
  itemid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  catid smallint(6) unsigned NOT NULL default '0',
  robotid smallint(6) unsigned NOT NULL default '0',
  robottime int(10) unsigned NOT NULL default '0',
  `subject` char(80) NOT NULL default '',
  author char(35) NOT NULL default '',
  itemfrom char(50) NOT NULL default '',
  dateline char(50) NOT NULL default '',
  isimport tinyint(1) NOT NULL default '0',
  haveattach tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (itemid),
  KEY robotid (robotid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_robotmessages'
--

DROP TABLE IF EXISTS supe_robotmessages;
CREATE TABLE supe_robotmessages (
  msgid mediumint(8) unsigned NOT NULL auto_increment,
  itemid mediumint(8) unsigned NOT NULL default '0',
  robotid smallint(6) unsigned NOT NULL default '0',
  message text NOT NULL,
  picurls text NOT NULL,
  flashurls text NOT NULL,
  PRIMARY KEY  (msgid),
  KEY itemid (itemid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_robots'
--

DROP TABLE IF EXISTS supe_robots;
CREATE TABLE supe_robots (
  robotid smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  lasttime int(10) unsigned NOT NULL default '0',
  importcatid smallint(6) unsigned NOT NULL default '0',
  robotnum smallint(6) unsigned NOT NULL default '0',
  listurltype varchar(10) NOT NULL default '',
  listurl text NOT NULL,
  listpagestart smallint(6) unsigned NOT NULL default '0',
  listpageend smallint(6) unsigned NOT NULL default '0',
  reverseorder tinyint(1) NOT NULL default '1',
  allnum smallint(6) unsigned NOT NULL default '0',
  pernum smallint(6) unsigned NOT NULL default '0',
  savepic tinyint(1) NOT NULL default '0',
  encode varchar(20) NOT NULL default '',
  picurllinkpre text NOT NULL,
  saveflash tinyint(1) NOT NULL default '0',
  subjecturlrule text NOT NULL,
  subjecturllinkrule text NOT NULL,
  subjecturllinkpre text NOT NULL,
  subjectrule text NOT NULL,
  subjectfilter text NOT NULL,
  subjectreplace text NOT NULL,
  subjectreplaceto text NOT NULL,
  subjectkey text NOT NULL,
  subjectallowrepeat tinyint(1) NOT NULL default '0',
  datelinerule text NOT NULL,
  fromrule text NOT NULL,
  authorrule text NOT NULL,
  messagerule text NOT NULL,
  messagefilter text NOT NULL,
  messagepagetype varchar(10) NOT NULL default '',
  messagepagerule text NOT NULL,
  messagepageurlrule text NOT NULL,
  messagepageurllinkpre text NOT NULL,
  messagereplace text NOT NULL,
  messagereplaceto text NOT NULL,
  autotype tinyint(1) NOT NULL default '0',
  wildcardlen tinyint(1) NOT NULL default '0',
  subjecturllinkcancel text NOT NULL,
  subjecturllinkfilter text NOT NULL,
  subjecturllinkpf text NOT NULL,
  subjectkeycancel text NOT NULL,
  messagekey text NOT NULL,
  messagekeycancel text NOT NULL,
  messageformat tinyint(1) NOT NULL default '0',
  messagepageurllinkpf text NOT NULL,
  uidrule text NOT NULL,
  defaultdateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (robotid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_robotlog'
--

DROP TABLE IF EXISTS supe_robotlog;
CREATE TABLE supe_robotlog (
  hash  char(32) NOT NULL default '',
  PRIMARY KEY (hash)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_rss'
--

DROP TABLE IF EXISTS supe_rss;
CREATE TABLE supe_rss (
  uid mediumint(8) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `data` mediumtext NOT NULL,
  updatetime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,`type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_settings'
--

DROP TABLE IF EXISTS supe_settings;
CREATE TABLE supe_settings (
  variable varchar(32) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (variable)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_sitemaplogs'
--

DROP TABLE IF EXISTS supe_sitemaplogs;
CREATE TABLE supe_sitemaplogs (
  slogid smallint(6) unsigned NOT NULL auto_increment,
  mapname varchar(50) NOT NULL default '',
  maptype varchar(20) NOT NULL default '',
  mapnum int(10) unsigned NOT NULL default '0',
  createtype tinyint(1) unsigned NOT NULL default '0',
  mapdata mediumtext NOT NULL,
  lastitemid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) NOT NULL default '0',
  changefreq varchar(20) NOT NULL default '',
  lastfileid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (slogid)
) TYPE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 'supe_spacecomments'
--

DROP TABLE IF EXISTS supe_spacecomments;
CREATE TABLE supe_spacecomments (
  cid int(10) unsigned NOT NULL auto_increment,
  itemid mediumint(8) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  rates smallint(6) NOT NULL default '0',
  url varchar(150) NOT NULL default '',
  `subject` varchar(100) NOT NULL default '',
  message text NOT NULL,
  PRIMARY KEY  (cid),
  KEY itemid (itemid,dateline),
  KEY uid (uid,dateline)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_spaceitems'
--

DROP TABLE IF EXISTS supe_spaceitems;
CREATE TABLE supe_spaceitems (
  itemid mediumint(8) unsigned NOT NULL auto_increment,
  catid smallint(6) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  itemtypeid mediumint(8) unsigned NOT NULL default '0',
  `type` char(10) NOT NULL default '',
  subtype char(10) NOT NULL default '',
  `subject` char(80) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  viewnum mediumint(8) unsigned NOT NULL default '0',
  replynum mediumint(8) unsigned NOT NULL default '0',
  goodrate int(10) unsigned NOT NULL default '0',
  badrate int(10) unsigned NOT NULL default '0',
  digest tinyint(1) NOT NULL default '0',
  top tinyint(1) NOT NULL default '0',
  allowreply tinyint(1) NOT NULL default '1',
  `hash` char(16) NOT NULL default '',
  folder tinyint(1) NOT NULL default '1',
  haveattach tinyint(1) NOT NULL default '0',
  grade tinyint(1) NOT NULL default '0',
  gid mediumint(8) unsigned NOT NULL default '0',
  gdigest tinyint(1) NOT NULL default '0',
  `password` char(10) NOT NULL default '',
  `styletitle` char(11) NOT NULL default '',
  picid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (itemid),
  KEY uid (uid,`type`,folder,top,dateline),
  KEY catid (catid,folder,dateline),
  KEY `type` (`type`,folder),
  KEY gid (gid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_spacenews'
--

DROP TABLE IF EXISTS supe_spacenews;
CREATE TABLE supe_spacenews (
  nid mediumint(8) unsigned NOT NULL auto_increment,
  itemid mediumint(8) unsigned NOT NULL default '0',
  message text NOT NULL,
  relativetags text NOT NULL,
  postip varchar(15) NOT NULL default '',
  relativeitemids varchar(255) NOT NULL default '',
  customfieldid smallint(6) unsigned NOT NULL default '0',
  customfieldtext text NOT NULL,
  includetags text NOT NULL,
  newsauthor varchar(20) NOT NULL default '',
  newsfrom varchar(50) NOT NULL default '',
  newsfromurl varchar(150) NOT NULL default '',
  newsurl varchar(255) NOT NULL default '',
  pageorder smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (nid),
  KEY itemid (itemid, pageorder, nid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_spacetags'
--

DROP TABLE IF EXISTS supe_spacetags;
CREATE TABLE supe_spacetags (
  itemid mediumint(8) unsigned NOT NULL default '0',
  tagid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  `type` char(10) NOT NULL default '',
  PRIMARY KEY  (itemid,tagid),
  KEY tagid (tagid,dateline)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- 表的结构 'supe_styles'
--

DROP TABLE IF EXISTS supe_styles;
CREATE TABLE supe_styles (
  tplid smallint(6) unsigned NOT NULL auto_increment,
  tplname varchar(80) NOT NULL default '',
  tplnote text NOT NULL,
  tpltype varchar(20) default NULL,
  tplfilepath varchar(80) NOT NULL default '',
  PRIMARY KEY  (tplid),
  KEY tpltype (tpltype)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_tagcache'
--

DROP TABLE IF EXISTS supe_tagcache;
CREATE TABLE supe_tagcache (
  cachekey varchar(16) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  cachename varchar(20) NOT NULL default '',
  `value` mediumtext NOT NULL,
  updatetime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (cachekey)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_tags'
--

DROP TABLE IF EXISTS supe_tags;
CREATE TABLE supe_tags (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  tagname char(20) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  `close` tinyint(1) NOT NULL default '0',
  spacenewsnum mediumint(8) unsigned NOT NULL default '0',
  relativetags char(255) NOT NULL default '',
  PRIMARY KEY  (tagid),
  KEY tagname (tagname),
  KEY tagid (tagid,dateline)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_usergroups'
--

DROP TABLE IF EXISTS supe_usergroups;
CREATE TABLE supe_usergroups (
  groupid smallint(6) unsigned NOT NULL auto_increment,
  grouptitle char(20) NOT NULL default '',
  system tinyint(1) NOT NULL default '0',
  managespacenews tinyint(1) NOT NULL default '0',
  needcheck tinyint(1) NOT NULL default '0',
  managerobots tinyint(1) NOT NULL default '0',
  managerobotmessages tinyint(1) NOT NULL default '0',
  managecategories tinyint(1) NOT NULL default '0',
  managesettings tinyint(1) NOT NULL default '0',
  manageusergroups tinyint(1) NOT NULL default '0',
  manageannouncements tinyint(1) NOT NULL default '0',
  managead tinyint(1) NOT NULL default '0',
  manageblocks tinyint(1) NOT NULL default '0',
  managebbs tinyint(1) NOT NULL default '0',
  managebbsforums tinyint(1) NOT NULL default '0',
  managethreads tinyint(1) NOT NULL default '0',
  manageuchome tinyint(1) NOT NULL default '0',
  managemodels tinyint(1) NOT NULL default '0',
  managechannel tinyint(1) NOT NULL default '0',
  managemember tinyint(1) NOT NULL default '0',
  managedelmembers tinyint(1) NOT NULL default '0',
  managehtml tinyint(1) NOT NULL default '0',
  managecache tinyint(1) NOT NULL default '0',
  managewords tinyint(1) NOT NULL default '0',
  manageattachmenttypes tinyint(1) NOT NULL default '0',
  managedatabase tinyint(1) NOT NULL default '0',
  managetpl tinyint(1) NOT NULL default '0',
  managecrons tinyint(1) NOT NULL default '0',
  managecheck tinyint(1) NOT NULL default '0',
  managecss tinyint(1) NOT NULL default '0',
  managefriendlinks tinyint(1) NOT NULL default '0',
  manageprefields tinyint(1) NOT NULL default '0',
  managesitemap tinyint(1) NOT NULL default '0',
  manageitems tinyint(1) NOT NULL default '0',
  managecomments tinyint(1) NOT NULL default '0',
  manageattachments tinyint(1) NOT NULL default '0',
  managetags tinyint(1) NOT NULL default '0',
  managereports tinyint(1) NOT NULL default '0',
  managepolls tinyint(1) NOT NULL default '0',
  managecustomfields tinyint(1) NOT NULL default '0',
  managestyles tinyint(1) NOT NULL default '0',
  managestyletpl tinyint(1) NOT NULL default '0',
  managemodelmanages tinyint(1) NOT NULL default '0',
  managemodelfolders tinyint(1) NOT NULL default '0',
  managemodelcategories tinyint(1) NOT NULL default '0',
  allowundelete tinyint(1) NOT NULL default '0',
  modelneedcheck tinyint(1) NOT NULL default '0',
  allowmodelundelete tinyint(1) NOT NULL default '0',
  closeignore tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (groupid)
) TYPE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_userlog'
--

CREATE TABLE supe_userlog (
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  action char(10) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- 表的结构 'supe_words'
--

DROP TABLE IF EXISTS supe_words;
CREATE TABLE supe_words (
  id smallint(6) unsigned NOT NULL auto_increment,
  admin varchar(15) NOT NULL default '',
  find varchar(255) NOT NULL default '',
  replacement varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) ENGINE=MyISAM;