<?php

/*
	[UCenter] (C)2001-2008 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: feed.php 12126 2008-01-11 09:40:32Z heyond $
*/

!defined('IN_UC') && exit('Access Denied');

/*note
DROP TABLE IF EXISTS uc_feeds;
CREATE TABLE uc_feeds (
  feedid mediumint(8) unsigned NOT NULL auto_increment,
  appid varchar(30) NOT NULL default '',
  icon varchar(30) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  hash_template varchar(32) NOT NULL default '',
  hash_data varchar(32) NOT NULL default '',
  title_template varchar(255) NOT NULL default '',
  title_data varchar(255) NOT NULL default '',
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
  target_ids varchar(255) NOT NULL default '',
  PRIMARY KEY  (feedid),
  KEY uid (uid,dateline)
) TYPE=MyISAM;
*/
class feedcontrol extends base {

	function feedcontrol() {
		$this->base();
	}

	function onadd($arr) {
		$this->load('misc');
		@extract($arr, EXTR_SKIP);//$appid, $icon, $appid, $uid, $username, $title_template, $title_data, $body_template, $body_data, $body_general, $target_ids, $image_1, $image_1_link, $image_2, $image_2_link, $image_3, $image_3_link, $image_4, $image_4_link
		$title_template = $this->_parsetemplate($title_template);
		$body_template = $this->_parsetemplate($body_template);
		$hash_template = md5($title_template.$body_template);
		$body_data = $_ENV['misc']->array2string($body_data);
		$title_data = $_ENV['misc']->array2string($title_data);
		$hash_data = md5($title_template.$title_data.$body_template.$body_data);
		$dateline = $this->time;
		$this->db->query("INSERT INTO ".UC_DBTABLEPRE."feeds SET appid='$appid', icon='$icon', uid='$uid', username='$username',
			title_template='$title_template', title_data='$title_data', body_template='$body_template', body_data='$body_data', body_general='$body_general',
			image_1='$image_1', image_1_link='$image_1_link', image_2='$image_2', image_2_link='$image_2_link',
			image_3='$image_3', image_3_link='$image_3_link', image_4='$image_4', image_4_link='$image_4_link',
			hash_template='$hash_template', hash_data='$hash_data', target_ids='$target_ids', dateline='$dateline'");
		return $this->db->insert_id();
	}

	//note public 取得事件的接口, 取完以后是否删除?
	function onget($arr) {
		@extract($arr, EXTR_SKIP);//limit
		$this->load('misc');
		$feedlist = $this->db->fetch_all("SELECT * FROM ".UC_DBTABLEPRE."feeds ORDER BY feedid LIMIT $limit");
		if($feedlist) {
			foreach($feedlist as $key=>$feed) {
				$feed['body_data'] = $_ENV['misc']->string2array($feed['body_data']);
				$feed['title_data'] = $_ENV['misc']->string2array($feed['title_data']);
				$feedlist[$key] = $feed;
			}
		}
		//note 删除过期的feed
		if(!empty($feedlist)) {
			$maxfeed = array_pop($feedlist);
			$maxfeedid = $maxfeed['feedid'];
			$feedlist = array_merge($feedlist, array($maxfeed));
			$this->_delete(0, $maxfeedid);
		}
		return $feedlist;
	}

	function _delete($start, $end) {
		$this->db->query("DELETE FROM ".UC_DBTABLEPRE."feeds WHERE feedid>='$start' AND feedid<='$end'");
	}

	function _parsetemplate($template) {
		$template = str_replace(array("\r", "\n"), '', $template);
		$template = str_replace(array('<br>', '<br />', '<BR>', '<BR />'), "\n", $template);
		$template = str_replace(array('<b>', '<B>'), '[B]', $template);
		$template = str_replace(array('<i>', '<I>'), '[I]', $template);
		$template = str_replace(array('<u>', '<U>'), '[U]', $template);
		$template = str_replace(array('</b>', '</B>'), '[/B]', $template);
		$template = str_replace(array('</i>', '</I>'), '[/I]', $template);
		$template = str_replace(array('</u>', '</U>'), '[/U]', $template);
		$template = htmlspecialchars($template);
		$template = nl2br($template);
		$template = str_replace(array('[B]', '[I]', '[U]', '[/B]', '[/I]', '[/U]'), array('<b>', '<i>', '<u>', '</b>', '</i>', '</u>'), $template);
		return $template;
	}

}

?>