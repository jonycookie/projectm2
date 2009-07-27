DROP TABLE IF EXISTS supe_[models]items;
CREATE TABLE supe_[models]items (
	itemid mediumint(8) unsigned NOT NULL auto_increment,
	catid smallint(6) unsigned NOT NULL default '0',
	uid mediumint(8) unsigned NOT NULL default '0',
	tid mediumint(8) unsigned NOT NULL default '0',
	username char(15) NOT NULL default '',
	subject char(80) NOT NULL default '',
	subjectimage char(80) NOT NULL default '',
	rates smallint(6) unsigned NOT NULL default '0',
	dateline int(10) unsigned NOT NULL default '0',
	lastpost int(10) unsigned NOT NULL default '0',
	viewnum mediumint(8) unsigned NOT NULL default '0',
	replynum mediumint(8) unsigned NOT NULL default '0',
	allowreply tinyint(1) NOT NULL default '0',
	grade tinyint(1) NOT NULL default '0',
	PRIMARY KEY (itemid),
	KEY catid (catid, itemid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS supe_[models]message;
CREATE TABLE supe_[models]message (
	nid mediumint(8) unsigned NOT NULL auto_increment,
	itemid mediumint(8) unsigned NOT NULL default '0',
	message text NOT NULL,
	postip varchar(15) NOT NULL default '',
	relativeitemids varchar(255) NOT NULL default '',
	PRIMARY KEY (nid),
	KEY itemid (itemid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS supe_[models]comments;
CREATE TABLE supe_[models]comments (
	cid int(10) unsigned NOT NULL auto_increment,
	itemid mediumint(8) unsigned NOT NULL default '0',
	authorid mediumint(8) unsigned NOT NULL default '0',
	author varchar(15) NOT NULL default '',
	ip varchar(15) NOT NULL default '',
	dateline int(10) unsigned NOT NULL default '0',
	message text NOT NULL default '',
	PRIMARY KEY (cid),
	KEY itemid (itemid, dateline)
) TYPE=MyISAM;


DROP TABLE IF EXISTS supe_[models]rates;
CREATE TABLE supe_[models]rates (
	rid int(10) unsigned NOT NULL auto_increment,
	itemid mediumint(8) unsigned NOT NULL default '0',
	authorid mediumint(8) unsigned NOT NULL default '0',
	author varchar(15) NOT NULL default '',
	ip varchar(15) NOT NULL default '',
	dateline int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (rid),
	KEY itemid (itemid, dateline)
) TYPE=MyISAM;



DROP TABLE IF EXISTS supe_[models]categories;
CREATE TABLE supe_[models]categories (
	catid smallint(6) unsigned NOT NULL auto_increment,
	upid smallint(6) unsigned NOT NULL default '0',
	name varchar(50) NOT NULL default '',
	note text NOT NULL default '',
	displayorder mediumint(6) unsigned NOT NULL default '0',
	url varchar(200) NOT NULL default '',
	subcatid varchar(200) NOT NULL default '',
	PRIMARY KEY (catid),
	KEY upid (upid),
	KEY displayorder (displayorder)
) TYPE=MyISAM;


DROP TABLE IF EXISTS supe_[models]folders;
CREATE TABLE supe_[models]folders (
	itemid mediumint(8) unsigned NOT NULL auto_increment,
	uid mediumint(8) unsigned NOT NULL default '0',
	subject char(80) NOT NULL default '',
	message text NOT NULL default '',
	dateline int(10) unsigned NOT NULL default '0',
	folder tinyint(1) NOT NULL default '0',
	PRIMARY KEY (itemid),
	KEY folder (folder, dateline)
) TYPE=MyISAM;
