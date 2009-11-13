# iCMS Backup SQL File
# Version:V3.1.2
# Time: 2009-10-24 16:15:01
# iCMS: http://www.iDreamSoft.CN
# --------------------------------------------------------


DROP TABLE IF EXISTS #iCMS@__config;
CREATE TABLE #iCMS@__config (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` mediumtext NOT NULL,
  PRIMARY KEY (id),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__article;
CREATE TABLE #iCMS@__article (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  cid int(10) unsigned NOT NULL DEFAULT '0',
  `order` smallint(6) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  stitle varchar(255) NOT NULL DEFAULT '',
  customlink varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  `source` varchar(100) NOT NULL DEFAULT '',
  author varchar(50) NOT NULL DEFAULT '',
  editor varchar(200) NOT NULL DEFAULT '',
  userid int(10) unsigned NOT NULL DEFAULT '0',
  pic varchar(255) NOT NULL DEFAULT '',
  keywords varchar(255) NOT NULL DEFAULT '',
  tags varchar(255) NOT NULL DEFAULT '',
  description varchar(255) NOT NULL DEFAULT '',
  related text NOT NULL,
  pubdate int(10) unsigned NOT NULL DEFAULT '0',
  hits int(10) unsigned NOT NULL DEFAULT '0',
  digg int(10) unsigned NOT NULL DEFAULT '0',
  comments int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  vlink varchar(255) NOT NULL DEFAULT '',
  top smallint(6) NOT NULL DEFAULT '0',
  visible enum('0','1') NOT NULL DEFAULT '1',
  postype tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY cid (cid),
  KEY customlink (customlink),
  KEY visible (visible),
  KEY `type` (`type`),
  KEY hits_digg_comments (hits,digg,comments),
  KEY postype (postype)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__articledata;
CREATE TABLE #iCMS@__articledata (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  aid int(10) unsigned NOT NULL DEFAULT '0',
  subtitle varchar(255) NOT NULL DEFAULT '',
  tpl varchar(255) NOT NULL DEFAULT '',
  body mediumtext NOT NULL,
  PRIMARY KEY (id),
  KEY aid (aid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__catalog;
CREATE TABLE #iCMS@__catalog (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  mid int(10) NOT NULL DEFAULT '0',
  rootid int(10) unsigned NOT NULL DEFAULT '0',
  `order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  keywords varchar(200) NOT NULL DEFAULT '',
  description varchar(200) NOT NULL DEFAULT '',
  dir varchar(255) NOT NULL DEFAULT '',
  domain varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  icon varchar(255) NOT NULL DEFAULT '',
  tpl_index varchar(100) NOT NULL DEFAULT '',
  tpl_list varchar(100) NOT NULL DEFAULT '',
  tpl_contents varchar(100) NOT NULL DEFAULT '',
  attr varchar(10) NOT NULL DEFAULT 'channel',
  count int(10) unsigned NOT NULL DEFAULT '0',
  isexamine enum('0','1') NOT NULL DEFAULT '0',
  issend enum('0','1') NOT NULL DEFAULT '1',
  ishidden enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY NewIndex1 (ishidden,`order`,id),
  KEY NewIndex2 (dir)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__comment;
CREATE TABLE #iCMS@__comment (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  mid int(10) unsigned NOT NULL DEFAULT '0',
  aid int(10) unsigned NOT NULL DEFAULT '0',
  sortid int(10) unsigned NOT NULL DEFAULT '0',
  username varchar(50) NOT NULL DEFAULT '',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  atitle varchar(100) NOT NULL DEFAULT '',
  quote int(10) unsigned NOT NULL DEFAULT '0',
  contents mediumtext NOT NULL,
  reply mediumtext NOT NULL,
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  isexamine enum('0','1') NOT NULL DEFAULT '0',
  up int(10) unsigned NOT NULL DEFAULT '0',
  `against` int(10) unsigned NOT NULL DEFAULT '0',
  zt enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY NewIndex1 (mid,sortid,isexamine,aid,id),
  KEY NewIndex2 (mid,sortid,isexamine,aid,addtime),
  KEY NewIndex3 (up,`against`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__advertise;
CREATE TABLE #iCMS@__advertise (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  varname varchar(50) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  style enum('code','text','image','flash') NOT NULL DEFAULT 'code',
  starttime int(10) unsigned NOT NULL DEFAULT '0',
  endtime int(10) unsigned NOT NULL DEFAULT '0',
  `code` mediumtext NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY varname (varname),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__contentype;
CREATE TABLE #iCMS@__contentype (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  val varchar(50) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__message;
CREATE TABLE #iCMS@__message (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` mediumtext NOT NULL,
  `text` mediumtext NOT NULL,
  reply mediumtext NOT NULL,
  secret enum('on','off') NOT NULL DEFAULT 'off',
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  ip varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__field;
CREATE TABLE #iCMS@__field (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  field varchar(100) NOT NULL DEFAULT '',
  mid int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(100) NOT NULL DEFAULT '',
  `default` varchar(200) NOT NULL DEFAULT '',
  validate varchar(200) NOT NULL DEFAULT '',
  hidden enum('0','1') NOT NULL DEFAULT '0',
  description varchar(100) NOT NULL DEFAULT '',
  rules text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS #iCMS@__model;
CREATE TABLE #iCMS@__model (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `table` varchar(100) NOT NULL DEFAULT '',
  description varchar(200) NOT NULL DEFAULT '',
  field mediumtext NOT NULL,
  listpage varchar(200) NOT NULL DEFAULT '',
  showpage varchar(200) NOT NULL DEFAULT '',
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS #iCMS@__file;
CREATE TABLE #iCMS@__file (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  aid int(10) unsigned NOT NULL DEFAULT '0',
  filename varchar(200) NOT NULL DEFAULT '',
  ofilename varchar(200) NOT NULL DEFAULT '',
  path varchar(250) NOT NULL DEFAULT '',
  thumbpath varchar(250) NOT NULL DEFAULT '',
  intro varchar(200) NOT NULL DEFAULT '',
  ext varchar(10) NOT NULL DEFAULT '',
  size int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('upload','remote') NOT NULL DEFAULT 'upload',
  PRIMARY KEY (id),
  KEY aid (aid),
  KEY filename (filename),
  KEY ext (ext),
  KEY path (path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__page;
CREATE TABLE #iCMS@__page (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  cid int(10) unsigned NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  keyword text NOT NULL,
  description text NOT NULL,
  body mediumtext NOT NULL,
  creater varchar(255) NOT NULL DEFAULT '',
  updater varchar(255) NOT NULL DEFAULT '',
  createtime int(10) unsigned NOT NULL DEFAULT '0',
  updatetime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY `hash` (cid),
  KEY `name` (title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__keywords;
CREATE TABLE #iCMS@__keywords (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  keyword varchar(200) NOT NULL DEFAULT '',
  `replace` text NOT NULL,
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  visible enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY keyword (keyword)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__tags;
CREATE TABLE #iCMS@__tags (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  sortid int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  count int(10) unsigned NOT NULL DEFAULT '0',
  ordernum smallint(6) unsigned NOT NULL DEFAULT '0',
  visible enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY vs (visible,sortid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__links ;
CREATE TABLE #iCMS@__links  (
  id int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  logo varchar(200) NOT NULL DEFAULT '',
  url varchar(200) NOT NULL DEFAULT '',
  `desc` tinytext NOT NULL,
  orderid tinyint(3) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY id (id),
  KEY orderid (orderid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__admin;
CREATE TABLE #iCMS@__admin (
  uid int(10) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  gender tinyint(1) unsigned NOT NULL DEFAULT '0',
  email char(40) NOT NULL DEFAULT '',
  info mediumtext NOT NULL,
  power mediumtext NOT NULL,
  cpower mediumtext NOT NULL,
  lastip char(15) NOT NULL DEFAULT '',
  lastlogintime int(10) unsigned NOT NULL DEFAULT '0',
  logintimes smallint(6) unsigned NOT NULL DEFAULT '0',
  post int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY username (username),
  KEY groupid (groupid),
  KEY email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__members;
CREATE TABLE #iCMS@__members (
  uid int(10) unsigned NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  groupid smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  gender tinyint(1) unsigned NOT NULL DEFAULT '0',
  email char(40) NOT NULL DEFAULT '',
  info mediumtext NOT NULL,
  power mediumtext NOT NULL,
  cpower mediumtext NOT NULL,
  lastip char(15) NOT NULL DEFAULT '',
  lastlogintime int(10) unsigned NOT NULL DEFAULT '0',
  logintimes smallint(6) unsigned NOT NULL DEFAULT '0',
  post int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid),
  KEY username (username),
  KEY groupid (groupid),
  KEY email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__group;
CREATE TABLE #iCMS@__group (
  gid int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `order` smallint(6) unsigned NOT NULL DEFAULT '0',
  power mediumtext NOT NULL,
  cpower mediumtext NOT NULL,
  `type` enum('a','u') NOT NULL DEFAULT 'a',
  PRIMARY KEY (gid),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS #iCMS@__search;
CREATE TABLE #iCMS@__search (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  search varchar(200) NOT NULL DEFAULT '',
  times int(10) unsigned NOT NULL DEFAULT '0',
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY search (search,times),
  KEY searchid (search,id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

INSERT INTO #iCMS@__config VALUES('1','name','iCMS内容管理系统');
INSERT INTO #iCMS@__config VALUES('2','url','http://localhost.thisoft.cn/test/iCMS');
INSERT INTO #iCMS@__config VALUES('3','keywords','iCMS内容管理系统');
INSERT INTO #iCMS@__config VALUES('4','description','iCMS 是一个采用 PHP 和 MySQL 数据库构建的高效内容管理系统,为中小型网站提供一个完美的解决方案。');
INSERT INTO #iCMS@__config VALUES('5','icp','ICP备案号');
INSERT INTO #iCMS@__config VALUES('6','masteremail','admin@domain.com');
INSERT INTO #iCMS@__config VALUES('7','ishtm','0');
INSERT INTO #iCMS@__config VALUES('8','htmdir','html/');
INSERT INTO #iCMS@__config VALUES('9','htmnamerule','ids');
INSERT INTO #iCMS@__config VALUES('10','customlink','0');
INSERT INTO #iCMS@__config VALUES('11','permalink_structure','');
INSERT INTO #iCMS@__config VALUES('12','collect','');
INSERT INTO #iCMS@__config VALUES('13','iscomment','1');
INSERT INTO #iCMS@__config VALUES('14','anonymous','1');
INSERT INTO #iCMS@__config VALUES('15','uploadfiledir','uploadfiles');
INSERT INTO #iCMS@__config VALUES('16','savedir','Y-m-d');
INSERT INTO #iCMS@__config VALUES('17','fileext','gif,jpg,rar,swf');
INSERT INTO #iCMS@__config VALUES('18','thumbwidth','140');
INSERT INTO #iCMS@__config VALUES('19','thumbhight','140');
INSERT INTO #iCMS@__config VALUES('20','iswatermark','0');
INSERT INTO #iCMS@__config VALUES('21','template','default');
INSERT INTO #iCMS@__config VALUES('22','customcataloglink','1');
INSERT INTO #iCMS@__config VALUES('23','isthumb','1');
INSERT INTO #iCMS@__config VALUES('24','isexamine','0');
INSERT INTO #iCMS@__config VALUES('25','indexTPL','{TPL}/index.htm');
INSERT INTO #iCMS@__config VALUES('26','indexname','index');
INSERT INTO #iCMS@__config VALUES('27','anonymousname','网友');
INSERT INTO #iCMS@__config VALUES('28','searchprepage','100');
INSERT INTO #iCMS@__config VALUES('29','ServerTimeZone','8');
INSERT INTO #iCMS@__config VALUES('30','cvtime','0');
INSERT INTO #iCMS@__config VALUES('31','htmlext','.html');
INSERT INTO #iCMS@__config VALUES('32','language','zh-cn');
INSERT INTO #iCMS@__config VALUES('33','dateformat','Y-m-d H:i:s');
INSERT INTO #iCMS@__config VALUES('34','rewrite','a:3:{s:3:\"dir\";s:5:\".php?\";s:5:\"split\";s:1:\"/\";s:3:\"ext\";s:5:\".html\";}');
INSERT INTO #iCMS@__config VALUES('35','linkmode','id');
INSERT INTO #iCMS@__config VALUES('36','keywordToTag','0');
INSERT INTO #iCMS@__config VALUES('37','remote','0');
INSERT INTO #iCMS@__config VALUES('38','autopic','0');
INSERT INTO #iCMS@__config VALUES('39','waterwidth','120');
INSERT INTO #iCMS@__config VALUES('40','waterheight','120');
INSERT INTO #iCMS@__config VALUES('41','waterpos','0');
INSERT INTO #iCMS@__config VALUES('42','waterimg','watermark.gif');
INSERT INTO #iCMS@__config VALUES('43','watertext','DreamArticle');
INSERT INTO #iCMS@__config VALUES('44','waterfont','');
INSERT INTO #iCMS@__config VALUES('45','waterfontsize','12');
INSERT INTO #iCMS@__config VALUES('46','watercolor','#000000');
INSERT INTO #iCMS@__config VALUES('47','waterpct','80');
INSERT INTO #iCMS@__config VALUES('48','autodesc','1');
INSERT INTO #iCMS@__config VALUES('49','descLen','100');
INSERT INTO #iCMS@__config VALUES('50','pagerule','file');
INSERT INTO #iCMS@__config VALUES('51','htmdircreaterule','2');
INSERT INTO #iCMS@__config VALUES('52','customhtmdircreaterule','C/Y-m-d');
INSERT INTO #iCMS@__config VALUES('53','bbs','a:14:{s:4:\"call\";s:1:\"0\";s:4:\"type\";s:7:\"PHPWind\";s:3:\"url\";s:0:\"\";s:6:\"dbhost\";s:0:\"\";s:6:\"dbuser\";s:0:\"\";s:4:\"dbpw\";s:0:\"\";s:6:\"dbname\";s:0:\"\";s:7:\"charset\";s:0:\"\";s:5:\"dbpre\";s:0:\"\";s:7:\"picpath\";s:0:\"\";s:9:\"attachdir\";s:0:\"\";s:9:\"htmifopen\";s:1:\"0\";s:6:\"htmdir\";s:5:\"-htm-\";s:6:\"htmext\";s:5:\".html\";}');
INSERT INTO #iCMS@__config VALUES('54','seotitle','iDreamSoft');
INSERT INTO #iCMS@__config VALUES('55','repeatitle','0');
INSERT INTO #iCMS@__config VALUES('56','CLsplit',',');
INSERT INTO #iCMS@__config VALUES('57','iscache','0');
INSERT INTO #iCMS@__config VALUES('58','cachedir','cache');
INSERT INTO #iCMS@__config VALUES('59','cachelevel','0');
INSERT INTO #iCMS@__config VALUES('60','cachetime','300');
INSERT INTO #iCMS@__config VALUES('61','iscachegzip','0');
INSERT INTO #iCMS@__config VALUES('62','sortdirrule','parent');
INSERT INTO #iCMS@__config VALUES('63','sortpagePre','list_');
INSERT INTO #iCMS@__config VALUES('64','taghtmdir','html/tag/');
INSERT INTO #iCMS@__config VALUES('65','tagrule','dir');
INSERT INTO #iCMS@__config VALUES('66','taghtmrule','id');
INSERT INTO #iCMS@__config VALUES('67','listhtmdir','html/');
INSERT INTO #iCMS@__config VALUES('68','pagehtmdir','html/page/');





INSERT INTO #iCMS@__advertise VALUES('1','全站顶部广告','','code','1222704000','0','a:4:{s:4:\"code\";a:1:{s:4:\"html\";s:51:\"请直接输入需要展现的广告的 HTML 代码\";}s:4:\"text\";a:3:{s:5:\"title\";s:0:\"\";s:4:\"link\";s:0:\"\";s:4:\"size\";s:0:\"\";}s:5:\"image\";a:5:{s:3:\"url\";s:0:\"\";s:4:\"link\";s:0:\"\";s:5:\"width\";s:0:\"\";s:6:\"height\";s:0:\"\";s:3:\"alt\";s:0:\"\";}s:5:\"flash\";a:3:{s:3:\"url\";s:0:\"\";s:5:\"width\";s:0:\"\";s:6:\"height\";s:0:\"\";}}','1');

INSERT INTO #iCMS@__contentype VALUES('1','首页头条','1','article');
INSERT INTO #iCMS@__contentype VALUES('2','幻灯片显示','2','article');
INSERT INTO #iCMS@__contentype VALUES('3','滚动显示','3','article');


INSERT INTO #iCMS@__field VALUES('1','栏目','cid','0','number','','0','0','所属栏目','a:2:{s:6:\"maxnum\";s:0:\"\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('2','排序','order','0','number','','N','0','排序','a:2:{s:6:\"maxnum\";s:1:\"6\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('3','标题','title','0','text','','0','0','','a:1:{s:9:\"maxlength\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('4','自定义链接','customlink','0','text','','N','0','自定义链接','a:1:{s:9:\"maxlength\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('5','标签','tags','0','text','','N','0','','a:1:{s:9:\"maxlength\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('6','发布时间','pubdate','0','calendar','','N','0','','N;');
INSERT INTO #iCMS@__field VALUES('7','点击数','hits','0','number','','N','0','','a:2:{s:6:\"maxnum\";s:0:\"\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('8','顶','digg','0','number','','N','0','','a:2:{s:6:\"maxnum\";s:0:\"\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('9','回复数','comments','0','number','','N','0','','a:2:{s:6:\"maxnum\";s:0:\"\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('10','属性','type','0','text','','N','0','内容附加属性','a:1:{s:9:\"maxlength\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('11','虚链接','vlink','0','text','','N','0','','a:1:{s:9:\"maxlength\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('12','置顶权重','top','0','number','','N','0','','a:2:{s:6:\"maxnum\";s:1:\"6\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('13','是否显示','visible','0','number','','N','0','1 显示 0不显示','a:2:{s:6:\"maxnum\";s:0:\"\";s:6:\"minnum\";s:0:\"\";}');
INSERT INTO #iCMS@__field VALUES('14','编辑','editor','0','text','','N','0','','a:1:{s:9:\"maxlength\";s:0:\"\";}');

INSERT INTO #iCMS@__group VALUES('1','超级管理员','1','ADMINCP,header_index,menu_index_home,menu_index_catalog_add,menu_index_article_add,menu_index_comment,menu_index_article_user_draft,menu_index_link,menu_index_advertise,header_setting,menu_setting_all,menu_setting_config,menu_setting_seo,menu_setting_html,menu_setting_cache,menu_setting_attachments,menu_setting_watermark,menu_setting_publish,menu_setting_time,menu_setting_other,menu_setting_bbs,header_article,menu_catalog_add,menu_catalog_manage,menu_article_add,menu_article_manage,menu_article_draft,menu_article_user_manage,menu_article_user_draft,menu_comment,menu_articletype,menu_article_default,menu_tag,menu_keywords,header_user,menu_user_manage,menu_account_manage,menu_account_edit,menu_group_manage,menu_group_add,header_extend,menu_model_manage,menu_plugin_manage,menu_modifier_manage,header_html,menu_html_all,menu_html_index,menu_html_catalog,menu_html_article,menu_html_page,menu_setting_html,header_tools,menu_link,menu_file_manage,menu_file_upload,menu_advertise,menu_message,menu_cache,menu_template_manage,menu_database_backup,menu_database_recover,menu_database_repair,Allow_View_Article,Allow_Edit_Article','1,4,5,2,3,6,7,8,9,10,1005','a');
INSERT INTO #iCMS@__group VALUES('2','管理员','2','','','a');
INSERT INTO #iCMS@__group VALUES('3','编辑','3','','','a');
INSERT INTO #iCMS@__group VALUES('4','会员','1','','','u');


