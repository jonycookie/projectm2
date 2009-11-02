-- 
-- ucenter home 数据库SQL
-- 生成日期: 2008 年 1 月 1 日 00:00
-- 

-- 
-- 数据库: 'uchome'
-- 

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_ad'
-- 

CREATE TABLE uchome_ad (
  adid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '1',
  title varchar(50) NOT NULL default '',
  pagetype varchar(20) NOT NULL default '',
  adcode text NOT NULL,
  system tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (adid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_adminsession'
-- 

CREATE TABLE uchome_adminsession (
  uid mediumint(8) unsigned NOT NULL default '0',
  ip char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  errorcount tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_album'
-- 

CREATE TABLE uchome_album (
  albumid mediumint(8) unsigned NOT NULL auto_increment,
  albumname varchar(50) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  picnum smallint(6) unsigned NOT NULL default '0',
  pic varchar(60) NOT NULL default '',
  picflag tinyint(1) NOT NULL default '0',
  friend tinyint(1) NOT NULL default '0',
  `password` varchar(10) NOT NULL default '',
  target_ids text NOT NULL,
  PRIMARY KEY  (albumid),
  KEY uid (uid,updatetime),
  KEY friend (friend,updatetime),
  KEY updatetime (updatetime)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_app'
-- 

CREATE TABLE uchome_app (
  uid mediumint(8) unsigned NOT NULL default '0',
  appid smallint(6) unsigned NOT NULL default '0',
  num mediumint(8) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,appid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_block'
-- 

CREATE TABLE uchome_block (
  bid smallint(6) unsigned NOT NULL auto_increment,
  blockname varchar(40) NOT NULL default '',
  blocksql text NOT NULL,
  cachename varchar(30) NOT NULL default '',
  cachetime smallint(6) unsigned NOT NULL default '0',
  num tinyint(3) unsigned NOT NULL default '0',
  perpage tinyint(3) unsigned NOT NULL default '0',
  htmlcode text NOT NULL,
  PRIMARY KEY  (bid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_blog'
-- 

CREATE TABLE uchome_blog (
  blogid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  `subject` char(80) NOT NULL default '',
  classid smallint(6) unsigned NOT NULL default '0',
  viewnum mediumint(8) unsigned NOT NULL default '0',
  replynum mediumint(8) unsigned NOT NULL default '0',
  tracenum mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  pic char(120) NOT NULL default '',
  picflag tinyint(1) NOT NULL default '0',
  noreply tinyint(1) NOT NULL default '0',
  friend tinyint(1) NOT NULL default '0',
  `password` char(10) NOT NULL default '',
  PRIMARY KEY  (blogid),
  KEY uid (uid,dateline),
  KEY friend (friend,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_blogfield'
-- 

CREATE TABLE uchome_blogfield (
  blogid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  tag varchar(255) NOT NULL default '',
  message mediumtext NOT NULL,
  postip varchar(20) NOT NULL default '',
  related text NOT NULL,
  relatedtime int(10) unsigned NOT NULL default '0',
  target_ids text NOT NULL,
  PRIMARY KEY  (blogid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_cache'
-- 

CREATE TABLE uchome_cache (
  cachekey varchar(16) NOT NULL default '',
  `value` mediumtext NOT NULL,
  mtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (cachekey)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_class'
-- 

CREATE TABLE uchome_class (
  classid mediumint(8) unsigned NOT NULL auto_increment,
  classname char(40) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (classid),
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_comment'
-- 

CREATE TABLE uchome_comment (
  cid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  id mediumint(8) unsigned NOT NULL default '0',
  idtype varchar(20) NOT NULL default '',
  authorid mediumint(8) unsigned NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  ip varchar(20) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  PRIMARY KEY  (cid),
  KEY id (id,idtype,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_config'
-- 

CREATE TABLE uchome_config (
  var varchar(30) NOT NULL default '',
  datavalue text NOT NULL,
  PRIMARY KEY  (var)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_cron'
-- 

CREATE TABLE uchome_cron (
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
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_data'
-- 

CREATE TABLE uchome_data (
  var varchar(20) NOT NULL default '',
  datavalue text NOT NULL,
  PRIMARY KEY  (var)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_doing'
-- 

CREATE TABLE uchome_doing (
  doid mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message char(200) NOT NULL default '',
  ip char(20) NOT NULL default '',
  PRIMARY KEY  (doid),
  KEY uid (uid,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------
-- 
-- 表的结构 'uchome_friend'
-- 

CREATE TABLE uchome_friend (
  uid mediumint(8) unsigned NOT NULL default '0',
  fuid mediumint(8) unsigned NOT NULL default '0',
  fusername char(15) NOT NULL default '',
  status tinyint(1) NOT NULL default '0',
  gid smallint(6) unsigned NOT NULL default '0',
  note char(50) NOT NULL default '',
  PRIMARY KEY  (uid,fuid),
  KEY fuid (fuid),
  KEY status (uid, status)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_feed'
-- 

CREATE TABLE uchome_feed (
  feedid mediumint(8) unsigned NOT NULL auto_increment,
  appid smallint(6) unsigned NOT NULL default '0',
  icon varchar(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  friend tinyint(1) NOT NULL default '0',
  hash_template varchar(32) NOT NULL default '',
  hash_data varchar(32) NOT NULL default '',
  title_template text NOT NULL,
  title_data text NOT NULL,
  body_template text NOT NULL,
  body_data text NOT NULL,
  body_general text NOT NULL,
  image_1 varchar(255) NOT NULL default '',
  image_1_link varchar(255) NOT NULL default '',
  image_2 varchar(255) NOT NULL default '',
  image_2_link varchar(255) NOT NULL default '',
  image_3 varchar(255) NOT NULL default '',
  image_3_link varchar(255) NOT NULL default '',
  image_4 varchar(255) NOT NULL default '',
  image_4_link varchar(255) NOT NULL default '',
  target_ids text NOT NULL,
  PRIMARY KEY  (feedid),
  KEY uid (uid,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;


-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_invite'
-- 

CREATE TABLE uchome_invite (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  code char(20) NOT NULL default '',
  fuid mediumint(8) unsigned NOT NULL default '0',
  fusername char(15) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_log'
-- 

CREATE TABLE uchome_log (
  logid mediumint(8) unsigned NOT NULL auto_increment,
  id mediumint(8) unsigned NOT NULL default '0',
  idtype char(20) NOT NULL default '',
  PRIMARY KEY  (logid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_member'
-- 

CREATE TABLE uchome_member (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  username char(15) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_mtag'
-- 

CREATE TABLE uchome_mtag (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  tagname varchar(40) NOT NULL default '',
  fieldid smallint(6) NOT NULL default '0',
  membernum mediumint(8) unsigned NOT NULL default '0',
  moderator varchar(255) NOT NULL default '',
  `close` tinyint(1) NOT NULL default '0',
  announcement varchar(255) NOT NULL default '',
  pic varchar(150) NOT NULL default '',
  PRIMARY KEY  (tagid),
  KEY tagname (tagname),
  KEY fieldid (fieldid,membernum),
  KEY membernum (membernum)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_notification'
-- 

CREATE TABLE uchome_notification (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  `type` varchar(20) NOT NULL default '',
  `new` tinyint(1) NOT NULL default '0',
  authorid mediumint(8) unsigned NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  note text NOT NULL,
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_pic'
-- 

CREATE TABLE uchome_pic (
  picid mediumint(8) NOT NULL auto_increment,
  albumid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  filename char(100) NOT NULL default '',
  title char(150) NOT NULL default '',
  `type` char(20) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  filepath char(60) NOT NULL default '',
  thumb tinyint(1) NOT NULL default '0',
  remote tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (picid),
  KEY albumid (albumid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_poke'
-- 

CREATE TABLE uchome_poke (
  uid mediumint(8) unsigned NOT NULL default '0',
  fromuid mediumint(8) unsigned NOT NULL default '0',
  fromusername char(15) NOT NULL default '',
  note char(50) NOT NULL default '',
  PRIMARY KEY  (uid,fromuid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_post'
-- 

CREATE TABLE uchome_post (
  pid mediumint(8) unsigned NOT NULL auto_increment,
  tagid mediumint(8) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  ip varchar(20) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  message text NOT NULL,
  pic varchar(255) NOT NULL default '',
  isthread tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (pid),
  KEY tid (tid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_profield'
-- 

CREATE TABLE uchome_profield (
  fieldid smallint(6) unsigned NOT NULL auto_increment,
  title varchar(80) NOT NULL default '',
  note varchar(255) NOT NULL default '',
  formtype varchar(20) NOT NULL default '0',
  inputnum smallint(3) unsigned NOT NULL default '0',
  choice text NOT NULL,
  displayorder tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fieldid)
) ENGINE=MyISAM;


-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_profilefield'
-- 

CREATE TABLE uchome_profilefield (
  fieldid smallint(6) unsigned NOT NULL auto_increment,
  title varchar(80) NOT NULL default '',
  note varchar(255) NOT NULL default '',
  formtype varchar(20) NOT NULL default '0',
  maxsize tinyint(3) unsigned NOT NULL default '0',
  required tinyint(1) NOT NULL default '0',
  invisible tinyint(1) NOT NULL default '0',
  allowsearch tinyint(1) NOT NULL default '0',
  choice text NOT NULL,
  displayorder tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fieldid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_session'
-- 

CREATE TABLE uchome_session (
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  groupid smallint(6) unsigned NOT NULL default '0',
  credit int(10) NOT NULL default '0',
  lastactivity int(10) unsigned NOT NULL default '0',
  newpm tinyint(1) NOT NULL default '0',
  nocss tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid)
) ENGINE=MEMORY;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_share'
-- 

CREATE TABLE uchome_share (
  sid mediumint(8) unsigned NOT NULL auto_increment,
  type varchar(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  hash_data varchar(32) NOT NULL default '',
  title_template text NOT NULL,
  body_template text NOT NULL,
  body_data text NOT NULL,
  body_general text NOT NULL,
  image varchar(255) NOT NULL default '',
  image_link varchar(255) NOT NULL default '',
  PRIMARY KEY  (sid),
  KEY uid (uid,dateline),
  KEY dateline (dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_space'
-- 

CREATE TABLE uchome_space (
  uid mediumint(8) unsigned NOT NULL default '0',
  groupid smallint(6) unsigned NOT NULL default '0',
  credit int(10) NOT NULL default '0',
  username char(15) NOT NULL default '',
  domain char(15) NOT NULL default '',
  spacename char(30) NOT NULL default '',
  viewnum int(10) unsigned NOT NULL default '0',
  friendnum int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  updatetime int(10) unsigned NOT NULL default '0',
  lastsearch int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  attachsize int(10) NOT NULL default '0',
  addsize int(10) NOT NULL default '0',
  flag tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (uid),
  KEY username (username),
  KEY domain (domain),
  KEY updatetime (updatetime)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_spacefield'
-- 

CREATE TABLE uchome_spacefield (
  uid mediumint(8) unsigned NOT NULL default '0',
  sex tinyint(1) NOT NULL default '0',
  email varchar(100) NOT NULL default '',
  qq varchar(20) NOT NULL default '',
  msn varchar(80) NOT NULL default '',
  birthyear smallint(6) unsigned NOT NULL default '0',
  birthmonth tinyint(3) unsigned NOT NULL default '0',
  birthday tinyint(3) unsigned NOT NULL default '0',
  blood varchar(5) NOT NULL default '',
  marry tinyint(1) NOT NULL default '0',
  birthprovince varchar(20) NOT NULL default '',
  birthcity varchar(20) NOT NULL default '',
  resideprovince varchar(20) NOT NULL default '',
  residecity varchar(20) NOT NULL default '',
  note varchar(255) NOT NULL default '',
  authstr varchar(20) NOT NULL default '',
  nocss tinyint(1) NOT NULL default '0',
  theme varchar(20) NOT NULL default '',
  css text NOT NULL,
  privacy text NOT NULL,
  friend text NOT NULL,
  feedfriend text NOT NULL,
  PRIMARY KEY  (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_tag'
-- 

CREATE TABLE uchome_tag (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  tagname char(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  blognum smallint(6) unsigned NOT NULL default '0',
  `close` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tagid),
  KEY tagname (tagname)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_tagblog'
-- 

CREATE TABLE uchome_tagblog (
  tagid mediumint(8) unsigned NOT NULL default '0',
  blogid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (tagid,blogid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_tagspace'
-- 

CREATE TABLE uchome_tagspace (
  tagid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  PRIMARY KEY  (tagid,uid),
  KEY uid (uid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_thread'
-- 

CREATE TABLE uchome_thread (
  tid mediumint(8) unsigned NOT NULL auto_increment,
  tagid mediumint(8) unsigned NOT NULL default '0',
  `subject` char(80) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  viewnum mediumint(8) unsigned NOT NULL default '0',
  replynum mediumint(8) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  lastauthor char(15) NOT NULL default '',
  lastauthorid mediumint(8) unsigned NOT NULL default '0',
  displayorder tinyint(1) unsigned NOT NULL default '0',
  digest tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY tagid (tagid,displayorder,lastpost),
  KEY uid (uid,lastpost),
  KEY lastpost (lastpost)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_trace'
-- 

CREATE TABLE uchome_trace (
  blogid mediumint(8) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (blogid,uid),
  KEY dateline (blogid,dateline),
  KEY uid (uid,dateline)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_usergroup'
-- 

CREATE TABLE uchome_usergroup (
  gid smallint(6) unsigned NOT NULL auto_increment,
  grouptitle char(20) NOT NULL default '',
  system tinyint(1) NOT NULL default '0',
  creditlower int(10) NOT NULL default '0',
  maxfriendnum smallint(6) unsigned NOT NULL default '0',
  maxattachsize int(10) unsigned NOT NULL default '0',
  allowhtml tinyint(1) NOT NULL default '0',
  allowcomment tinyint(1) NOT NULL default '0',
  searchinterval smallint(6) unsigned NOT NULL default '0',
  postinterval smallint(6) unsigned NOT NULL default '0',
  allowblog tinyint(1) NOT NULL default '0',
  allowdoing tinyint(1) NOT NULL default '0',
  allowupload tinyint(1) NOT NULL default '0',
  allowshare tinyint(1) NOT NULL default '0',
  allowthread tinyint(1) NOT NULL default '0',
  allowpost tinyint(1) NOT NULL default '0',
  domainlength smallint(6) unsigned NOT NULL default '0',
  closeignore tinyint(1) NOT NULL default '0',
  manageconfig tinyint(1) NOT NULL default '0',
  managenetwork tinyint(1) NOT NULL default '0',
  manageprofilefield tinyint(1) NOT NULL default '0',
  manageprofield tinyint(1) NOT NULL default '0',
  manageusergroup tinyint(1) NOT NULL default '0',
  managefeed tinyint(1) NOT NULL default '0',
  manageshare tinyint(1) NOT NULL default '0',
  managedoing tinyint(1) NOT NULL default '0',
  manageblog tinyint(1) NOT NULL default '0',
  managetag tinyint(1) NOT NULL default '0',
  managetagtpl tinyint(1) NOT NULL default '0',
  managealbum tinyint(1) NOT NULL default '0',
  managecomment tinyint(1) NOT NULL default '0',
  managemtag tinyint(1) NOT NULL default '0',
  managethread tinyint(1) NOT NULL default '0',
  managespace tinyint(1) NOT NULL default '0',
  managecensor tinyint(1) NOT NULL default '0',
  managead tinyint(1) NOT NULL default '0',
  managebackup tinyint(1) NOT NULL default '0',
  manageblock tinyint(1) NOT NULL default '0',
  managetemplate tinyint(1) NOT NULL default '0',
  managestat tinyint(1) NOT NULL default '0',
  managecache tinyint(1) NOT NULL default '0',
  managecredit tinyint(1) NOT NULL default '0',
  managecron tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (gid)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- 表的结构 'uchome_visitor'
-- 

CREATE TABLE uchome_visitor (
  uid mediumint(8) unsigned NOT NULL default '0',
  vuid mediumint(8) unsigned NOT NULL default '0',
  vusername char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,vuid),
  KEY dateline (uid,dateline)
) ENGINE=MyISAM;
