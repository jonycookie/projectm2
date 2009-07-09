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

INSERT INTO `cms_advert` (`adid`, `cid`, `type`, `name`, `priority`, `intro`, `config`, `pid`, `countview`, `views`, `countclick`, `clicks`, `linkurl`, `linktarget`, `linkalt`, `starttime`, `endtime`) VALUES
(1, '0,2,1,6,3,4,5', 'img', '头部Banner', 1, '', 'a:8:{s:3:"url";s:45:"http://www.phpwind.com/downloads/cms/idc1.gif";s:5:"width";s:3:"950";s:6:"height";s:2:"80";s:10:"flashwmode";s:1:"0";s:7:"linkurl";s:0:"";s:10:"linktarget";s:0:"";s:7:"linkalt";s:0:"";s:8:"priority";s:1:"1";}', 1, 0, 0, 0, 0, 'http://idc.phpwind.com/', '1', '', 1176422400, 1207958400),
(4, '0', 'img', '首页中间图片', 1, '', 'a:8:{s:3:"url";s:20:"images/verycmsv3.jpg";s:5:"width";s:3:"950";s:6:"height";s:2:"80";s:10:"flashwmode";s:1:"0";s:7:"linkurl";s:0:"";s:10:"linktarget";s:0:"";s:7:"linkalt";s:0:"";s:8:"priority";s:1:"1";}', 2, 0, 0, 0, 0, '', '1', '', 1176768000, 1208304000),
(5, '0', 'img', 'what', 1, '', 'a:8:{s:3:"url";s:15:"images/what.jpg";s:5:"width";s:3:"175";s:6:"height";s:3:"220";s:10:"flashwmode";s:1:"0";s:7:"linkurl";s:0:"";s:10:"linktarget";s:0:"";s:7:"linkalt";s:0:"";s:8:"priority";s:1:"1";}', 3, 0, 0, 0, 0, '', '1', '', 1176768000, 1208304000);
INSERT INTO `cms_adposition` (`pid`, `name`, `type`, `inad`, `showtype`, `jsname`, `active`, `intro`, `setting`, `width`, `height`) VALUES
(1, '头部横幅广告', 'banner', '', 1, 'head', 1, '头部的横幅广告图片', 'a:8:{s:9:"floattype";s:1:"1";s:7:"poptype";s:1:"1";s:8:"showtype";i:1;s:4:"left";i:0;s:3:"top";i:0;s:5:"delta";d:0.15;s:5:"delay";i:50;s:10:"cookiehour";i:0;}', 950, 80),
(2, '首页中间栏', 'banner', '', 1, 'mid', 1, '', 'a:8:{s:9:"floattype";s:1:"1";s:8:"showtype";i:1;s:4:"left";i:0;s:3:"top";i:0;s:5:"delta";d:0.15;s:7:"poptype";i:1;s:5:"delay";i:50;s:10:"cookiehour";i:0;}', 950, 80),
(3, '广告位3', 'banner', '', 1, 'left', 1, '', 'a:8:{s:9:"floattype";s:1:"1";s:7:"poptype";s:1:"1";s:8:"showtype";i:1;s:4:"left";i:0;s:3:"top";i:0;s:5:"delta";d:0.15;s:5:"delay";i:50;s:10:"cookiehour";i:0;}', 175, 220);
