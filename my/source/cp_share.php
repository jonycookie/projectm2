<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_share.php 7411 2008-05-20 01:56:12Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$sid = intval($_GET['sid']);

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		deleteshares(array($sid));
		showmessage('do_success', $_POST['refer'], 0);
	}
} else {
	
	if(!checkperm('allowshare')) {
		showmessage('no_privilege');
	}

	$type = empty($_GET['type'])?'':$_GET['type'];
	$id = empty($_GET['id'])?0:intval($_GET['id']);
	$note_uid = 0;
	$note_message = '';
	
	$arr = array();
	
	switch ($type) {
		case 'space':
			$cspace = getspace($id);
			if(empty($cspace)) {
				showmessage('space_does_not_exist');
			}
			
			$arr['title_template'] = lang('share_space');
			$arr['body_template'] = '<b>{username}</b><br>{spacename}<br>{reside}';
			$arr['body_data'] = array(
				'username' => "<a href=\"space.php?uid=$id\">$cspace[username]</a>",
				'spacename' => $cspace['spacename'],
				'reside' => $cspace['resideprovince'].$cspace['residecity']
			);
			$arr['image'] = avatar($id, 'middle');
			$arr['image_link'] = "space.php?uid=$id";
			//通知
			$note_uid = $id;
			$note_message = lang('note_share_space');
			break;
		case 'blog':
			$query = $_SGLOBAL['db']->query("SELECT b.*,bf.message FROM ".tname('blog')." b 
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			if(!$blog = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('blog_does_not_exist');
			}
			if($blog['friend']) {
				showmessage('logs_can_not_share');
			}
			
			$arr['title_template'] = lang('share_blog');
			$arr['body_template'] = '<b>{subject}</b><br>{username}<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]\">$blog[subject]</a>",
				'username' => "<a href=\"space.php?uid=$blog[uid]\">$blog[username]</a>",
				'message' => getstr($blog['message'], 150, 0, 1, 0, 0, -1)
			);
			$arr['image'] = mkpicurl($blog);
			$arr['image_link'] = "space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]";
			//通知
			$note_uid = $blog['uid'];
			$note_message = lang('note_share_blog', array("space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]", $blog['subject']));
			break;
		case 'album':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$id'");
			if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('album_does_not_exist');
			}
			if($album['friend']) {
				showmessage('album_can_not_share');
			}
			
			$arr['title_template'] =  lang('share_album');
			$arr['body_template'] = '<b>{albumname}</b><br>{username}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"space.php?uid=$album[uid]&do=album&id=$album[albumid]\">$album[albumname]</a>",
				'username' => "<a href=\"space.php?uid=$album[uid]\">$album[username]</a>"
			);
			$arr['image'] = mkpicurl($album);
			$arr['image_link'] = "space.php?uid=$album[uid]&do=album&id=$album[albumid]";
			//通知
			$note_uid = $album['uid'];
			$note_message = lang('note_share_album', array("space.php?uid=$album[uid]&do=album&id=$album[albumid]", $album['albumname']));
			break;
		case 'pic':
			$query = $_SGLOBAL['db']->query("SELECT album.username, album.albumid, album.albumname, album.friend, pic.* FROM ".tname('pic')." pic
				LEFT JOIN ".tname('album')." album ON album.albumid=pic.albumid
				WHERE pic.picid='$id'");
			if(!$pic = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('image_does_not_exist');
			}
			if($pic['friend']) {
				showmessage('image_can_not_share');
			}
			if(empty($pic['albumid'])) $pic['albumid'] = 0;
			if(empty($pic['albumname'])) $pic['albumname'] = mlang('default_albumname');
			
			$arr['title_template'] = lang('share_image');
			$arr['body_template'] = lang('album').': <b>{albumname}</b><br>{username}<br>{title}';
			$arr['body_data'] = array(
				'albumname' => "<a href=\"space.php?uid=$pic[uid]&do=album&id=$pic[albumid]\">$pic[albumname]</a>",
				'username' => "<a href=\"space.php?uid=$pic[uid]\">$pic[username]</a>",
				'title' => getstr($pic['title'], 100, 0, 1, 0, 0, -1)
			);
			$arr['image'] = mkpicurl($pic);
			$arr['image_link'] = "space.php?uid=$pic[uid]&do=album&picid=$pic[picid]";
			//通知
			$note_uid = $pic['uid'];
			$note_message = lang('note_share_pic', array("space.php?uid=$pic[uid]&do=album&picid=$pic[picid]", $pic['albumname']));
			break;
		case 'thread':
			$query = $_SGLOBAL['db']->query("SELECT t.*, p.message FROM ".tname('thread')." t
				LEFT JOIN ".tname('post')." p ON p.tid=t.tid AND p.isthread='1'
				WHERE t.tid='$id'");
			if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('topics_does_not_exist');
			}
			
			include_once(S_ROOT.'./data/data_profield.php');
			
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$thread[tagid]'");
			$mtag = $_SGLOBAL['db']->fetch_array($query);
			$mtag['title'] = $_SGLOBAL['profield'][$mtag['fieldid']]['title'];
				
			$arr['title_template'] = lang('share_thread');
			$arr['body_template'] = '<b>{subject}</b><br>{username}<br>'.lang('mtag').': {mtag} ({field})<br>{message}';
			$arr['body_data'] = array(
				'subject' => "<a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]\">$thread[subject]</a>",
				'username' => "<a href=\"space.php?uid=$thread[uid]\">$thread[username]</a>",
				'mtag' => "<a href=\"space.php?do=mtag&tagid=$mtag[tagid]\">$mtag[tagname]</a>",
				'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>",
				'message' => getstr($thread['message'], 150, 0, 1, 0, 0, -1)
			);
			$arr['image'] = '';
			$arr['image_link'] = '';
			//通知
			$note_uid = $thread['uid'];
			$note_message = lang('note_share_thread', array("space.php?uid=$thread[uid]&do=thread&id=$thread[tid]", $thread['subject']));
			break;
		case 'mtag':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$id'");
			if(!$mtag = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('mtag_does_not_exist');
			}
			
			include_once(S_ROOT.'./data/data_profield.php');
			
			$mtag['title'] = $_SGLOBAL['profield'][$mtag['fieldid']]['title'];
				
			$arr['title_template'] = lang('share_mtag');
			$arr['body_template'] = '<b>{mtag}</b><br>{field}<br>'.lang('share_mtag_membernum');
			$arr['body_data'] = array(
				'mtag' => "<a href=\"space.php?do=mtag&tagid=$mtag[tagid]\">$mtag[tagname]</a>",
				'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>",
				'membernum' => $mtag['membernum']
			);
			$arr['image'] = $mtag['pic'];
			$arr['image_link'] = "space.php?do=mtag&tagid=$mtag[tagid]";
			break;
		case 'tag':
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE tagid='$id'");
			if(!$tag = $_SGLOBAL['db']->fetch_array($query)) {
				showmessage('tag_does_not_exist');
			}
			
			$arr['title_template'] = lang('share_tag');
			$arr['body_template'] = '<b>{tagname}</b><br>'.lang('share_tag_blognum');
			$arr['body_data'] = array(
				'tagname' => "<a href=\"space.php?do=tag&id=$tag[tagid]\">$tag[tagname]</a>",
				'blognum' => $tag['blognum']
			);
			$arr['image'] = '';
			$arr['image_link'] = '';
			break;
		case 'link':
			$link = shtmlspecialchars(trim($_POST['link']));
			if($link) {
				if(!preg_match("/^http\:\/\/.{4,300}$/i", $link)) $link = '';
			}
			if(empty($link)) {
				showmessage('url_incorrect_format');
			}
			$arr['title_template'] = lang('share_link');
			$arr['body_template'] = '{link}';
			
			$link_text = sub_url($link, 45);
			
			$arr['body_data'] = array('link'=>"<a href=\"$link\" target=\"_blank\">$link_text</a>", 'data'=>$link);
			break;
		default:
			showmessage('not_correctly_designated_to_share_information');
			break;
	}
	
	//添加分享
	if(submitcheck('sharesubmit')) {
		$arr['body_general'] = getstr($_POST['general'], 150, 1, 1, 1, 1);
		if(empty($arr['body_general'])) {
			showmessage('description_share_input');
		}
		share_add($type, $arr['title_template'], $arr['body_template'], $arr['body_data'], $arr['body_general'], $arr['image'], $arr['image_link']);

		//被分享通知当事人
		if($note_uid && $note_uid != $_SGLOBAL['supe_uid']) {
			notification_add($note_uid, 'sharenotice', $note_message);
		}
		
		showmessage('do_success', $_POST['refer'], 0);
	}
	
	$arr['body_data'] = serialize($arr['body_data']);
	$arr = mkshare($arr);
	$arr['username'] = $_SGLOBAL['supe_username'];
}

include template('cp_share');

?>