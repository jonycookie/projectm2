<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_comment.php 7591 2008-06-13 10:29:21Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_bbcode.php');
	
//共用变量
$cspace = $pic = $blog = $album = array();

if(submitcheck('commentsubmit')) {

	if(!checkperm('allowcomment')) {
		showmessage('no_privilege');
	}

	//判断是否发布太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
	
	$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
	if(strlen($message) < 2) {
		showmessage('content_is_too_short');
	}
	
	//摘要
	$summay = getstr($message, 150, 1, 1, 0, 0, -1);

	$id = intval($_POST['id']);
	
	//引用评论
	$cid = empty($_POST['cid'])?0:intval($_POST['cid']);
	$comment = array();
	if($cid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND id='$id' AND idtype='$_POST[idtype]'");
		$comment = $_SGLOBAL['db']->fetch_array($query);
		if($comment && $comment['authorid'] != $_SGLOBAL['supe_uid']) {
			$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<span class=\"q\"\>.*?\<\/span\>\<\/div\>/is", '', $comment['message']);
			$message = addslashes("<div class=\"quote\"><span class=\"q\"><b>{$comment[author]}</b>: ".getstr($comment['message'], 150, 0, 0, 0, 0, -1).'</span></div>').$message;
			if($comment['idtype']=='uid') {
				$id = $comment['authorid'];
			}
		} else {
			$comment = array();
		}
	}

	//对输入的id、idtype进行检查
	checkcomment($id, $_POST['idtype']);
	
	//事件
	$fs = array();
	$fs['icon'] = 'comment';
	$fs['target_ids'] = $fs['friend'] = '';
	
	switch ($_POST['idtype']) {
		case 'uid':
			//事件
			$fs['icon'] = 'wall';
			$fs['title_template'] = lang('feed_comment_space');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$cspace[uid]\">$cspace[username]</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			$fs['images'] = array();
			$fs['image_links'] = array();
			break;
		case 'picid':
			//事件
			$fs['title_template'] = lang('feed_comment_image');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$cspace[uid]\">$cspace[username]</a>");
			$fs['body_template'] = '{pic_title}';
			$fs['body_data'] = array('pic_title'=>$pic['title']);
			$fs['body_general'] = $summay;
			$fs['images'] = array(mkpicurl($pic));
			$fs['image_links'] = array("space.php?uid=$cspace[uid]&do=album&picid=$pic[picid]");
			$fs['target_ids'] = $album['target_ids'];
			$fs['friend'] = $album['friend'];
			break;
		case 'blogid':
			//更新评论统计
			$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET replynum=replynum+1 WHERE blogid='$id'");
			//事件
			$fs['title_template'] = lang('feed_comment_blog');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$cspace[uid]\">$cspace[username]</a>", 'blog'=>"<a href=\"space.php?uid=$cspace[uid]&do=blog&id=$id\">$blog[subject]</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			$fs['target_ids'] = $blog['target_ids'];
			$fs['friend'] = $blog['friend'];
			break;
		case 'sid':
			//事件
			$fs['title_template'] = lang('feed_comment_share');
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$cspace[uid]\">$cspace[username]</a>", 'share'=>"<a href=\"space.php?uid=$cspace[uid]&do=share&id=$id\">".lang('share')."</a>");
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
			break;
	}
	
	$setarr = array(
		'uid' => $cspace['uid'],
		'id' => $id,
		'idtype' => $_POST['idtype'],
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'ip' => getonlineip()
	);
	//入库
	$cid = inserttable('comment', $setarr, 1);
	
	switch ($_POST['idtype']) {
		case 'uid':
			$n_url = "space.php?uid=$cspace[uid]&do=wall&cid=$cid";
			$note_type = 'wall';
			$note = lang('note_wall', array($n_url));
			$q_note = lang('note_wall_reply', array($n_url));
			if($comment) {
				$msg = 'note_wall_reply_success';
				$magvalues = array($cspace['username']);
			} else {
				$msg = 'do_success';
				$magvalues = array();
			}
			break;
		case 'picid':
			$n_url = "space.php?uid=$cspace[uid]&do=album&picid=$id&cid=$cid";
			$note_type = 'piccomment';
			$note = lang('note_pic_comment', array($n_url));
			$q_note = lang('note_pic_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			break;
		case 'blogid':
			//通知
			$n_url = "space.php?uid=$cspace[uid]&do=blog&id=$id&cid=$cid";
			$note_type = 'blogcomment';
			$note = lang('note_blog_comment', array($n_url, $blog['subject']));
			$q_note = lang('note_blog_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			break;
		case 'sid':
			//分享
			$n_url = "space.php?uid=$cspace[uid]&do=share&id=$id&cid=$cid";
			$note_type = 'sharecomment';
			$note = lang('note_share_comment', array($n_url));
			$q_note = lang('note_share_comment_reply', array($n_url));
			$msg = 'do_success';
			$magvalues = array();
			break;
	}
	
	if(empty($comment)) {
		//非引用评论
		if($cspace['uid'] != $_SGLOBAL['supe_uid']) {
			//事件发布
			if(ckprivacy('comment', 1)) {
				feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links'], $fs['target_ids'], $fs['friend']);
			}
			//发送通知
			notification_add($cspace['uid'], $note_type, $note);
			//留言发送短消息
			if($_POST['idtype'] == 'uid' && $cspace['updatetime'] == $cspace['dateline']) {
				include_once S_ROOT.'./uc_client/client.php';
				uc_pm_send($_SGLOBAL['supe_uid'], $cspace['uid'], lang('wall_pm_subject'), lang('wall_pm_message', array(getsiteurl().$n_url)), 1, 0, 0);
			}
		}
	} elseif($comment['authorid'] != $_SGLOBAL['supe_uid']) {
		notification_add($comment['authorid'], $note_type, $q_note);
	}
	
	//积分
	if($cspace['uid'] != $_SGLOBAL['supe_uid']) {
		updatespacestatus('get', 'comment');
	}
	
	showmessage($msg, $_POST['refer'], 0, $magvalues);
}

$cid = empty($_GET['cid'])?0:intval($_GET['cid']);

//编辑
if($_GET['op'] == 'edit') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid' AND authorid='$_SGLOBAL[supe_uid]'");
	if(!$comment = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}
	
	//提交编辑
	if(submitcheck('editsubmit')) {
	
		$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
		if(strlen($message) < 4) showmessage('content_is_too_short');
	
		updatetable('comment', array('message'=>$message), array('cid'=>$comment['cid']));
		
		showmessage('do_success', $_POST['refer'], 0);
	}
	
	//bbcode转换
	$comment['message'] = html2bbcode($comment['message']);//显示用

} elseif($_GET['op'] == 'delete') {

	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deletecomments(array($cid))) {
			showmessage('do_success', $_POST['refer'], 0);
		} else {
			showmessage('no_privilege');
		}
	}

} elseif($_GET['op'] == 'reply') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid='$cid'");
	if(!$comment = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('comments_do_not_exist');
	}

} else {
	
	showmessage('no_privilege');
}

include template('cp_comment');

//检查
function checkcomment($id, $idtype) {
	global $_SGLOBAL;
	global $cspace, $pic, $blog, $album;

	switch ($idtype) {
		case 'uid':
			//检索空间
			$cspace = getspace($id);
			break;
		case 'picid':
			//检索图片
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid='$id' LIMIT 1");
			$pic = $_SGLOBAL['db']->fetch_array($query);
			//图片不存在
			if(empty($pic)) {
				showmessage('view_images_do_not_exist');
			}
			
			//检索空间
			$cspace = getspace($pic['uid']);
			
			//获取相册
			$album = array();
			if($pic['albumid']) {
				$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$pic[albumid]'");
				if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
					updatetable('pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));//相册丢失
				} else {
					if($album['target_ids']) {
						$album['target_ids'] .= ",$album[uid]";
					}
				}
			}
			break;
		case 'blogid':
			//读取日志
			$query = $_SGLOBAL['db']->query("SELECT b.*, bf.target_ids
				FROM ".tname('blog')." b
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$id'");
			$blog = $_SGLOBAL['db']->fetch_array($query);
			//日志不存在
			if(empty($blog)) {
				showmessage('view_to_info_did_not_exist');
			}
			
			//是否允许评论
			if(!empty($blog['noreply'])) {
				showmessage('do_not_accept_comments');
			}
			if($blog['target_ids']) {
				$blog['target_ids'] .= ",$blog[uid]";
			}
			//检索空间
			$cspace = getspace($blog['uid']);
			break;
		case 'sid':
			//读取日志
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid='$id'");
			$share = $_SGLOBAL['db']->fetch_array($query);
			//日志不存在
			if(empty($share)) {
				showmessage('sharing_does_not_exist');
			}
			
			//检索空间
			$cspace = getspace($share['uid']);
			break;
		default:
			showmessage('non_normal_operation');
			break;
	}
	if(empty($cspace)) {
		showmessage('space_does_not_exist');
	}
}

?>