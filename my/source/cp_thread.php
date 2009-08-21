<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_thread.php 7591 2008-06-13 10:29:21Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_bbcode.php');

if(submitcheck('threadsubmit')) {
	
	if(!checkperm('allowthread')) {
		showmessage('no_privilege');
	}

	//发新话题
	$tagid = empty($_POST['tagid'])?0:intval($_POST['tagid']);
	$mtag = ckmtagspace($tagid);

	//判断是否操作太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
	
	$subject = getstr($_POST['subject'], 80, 1, 1, 1);
	if(strlen($subject) < 4) showmessage('title_not_too_little');
	
	$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
	if(strlen($message) < 2) showmessage('content_is_not_less_than_four_characters');

	$setarr = array(
		'tagid' => $tagid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'subject' => $subject,
		'lastpost' => $_SGLOBAL['timestamp'],
		'lastauthor' => $_SGLOBAL['supe_username'],
		'lastauthorid' => $_SGLOBAL['supe_uid']
	);
	$tid = inserttable('thread', $setarr, 1);
	$psetarr = array(
		'tagid' => $tagid,
		'tid' => $tid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'ip' => getonlineip(),
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'pic' => getpicurl($_POST['pic']),
		'isthread' => 1
	);
	inserttable('post', $psetarr);
	
	//积分
	updatespacestatus('get', 'thread');

	//事件
	$fs = array();
	$fs['icon'] = 'thread';
	
	$fs['title_template'] = lang('feed_thread');
	$fs['title_data'] = array();
	
	$fs['body_template'] = '<b>{subject}</b><br>'.lang('mtag').': {mtag}<br>{summary}';
	$fs['body_data'] = array(
		'subject' => "<a href=\"space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid\">$setarr[subject]</a>",
		'mtag' => "<a href=\"space.php?do=mtag&tagid=$mtag[tagid]\">$mtag[tagname]</a>",
		'summary' => getstr($message, 150, 1, 1, 0, 0, -1)
	);
	$fs['body_general'] = '';
	
	if($psetarr['pic']) {
		$fs['images'] = array($psetarr['pic']);
		$fs['image_links'] = array("space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid");
	} else {
		$fs['images'] = $fs['image_links'] = array();
	}

	if(ckprivacy('thread', 1)) {
		feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links']);
	}
	
	showmessage('do_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid", 0);

} elseif(submitcheck('postsubmit')) {

	if(!checkperm('allowpost')) {
		showmessage('no_privilege');
	}
	
	//判断是否操作太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
	
	//获得话题
	$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
	$thread = array();
	if($tid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$tid' LIMIT 1");
		$thread = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($thread)) showmessage('the_discussion_topic_does_not_exist');

	//权限
	$mtag = ckmtagspace($thread['tagid']);

	$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
	if(strlen($message) < 4) {
		showmessage('content_is_not_less_than_four_characters');
	}

	//摘要
	$summay = getstr($message, 150, 1, 1);
	
	//引用回复
	$pid = empty($_POST['pid'])?0:intval($_POST['pid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid' AND tid='$tid' AND isthread='0'");
	$post = $_SGLOBAL['db']->fetch_array($query);
	if($post) {
		$post['message'] = preg_replace("/\<div class=\"quote\"\>\<span class=\"q\"\>.*?\<\/span\>\<\/div\>/is", '', $post['message']);
		$message = addslashes("<div class=\"quote\"><span class=\"q\"><b>{$post[username]}</b>: ".getstr($post['message'], 150, 0, 0, 0, 0, -1).'</span></div>').$message;
	}
	
	$setarr = array(
		'tagid' => $tagid,
		'tid' => $tid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'ip' => getonlineip(),
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'pic' => getpicurl($_POST['pic'])
	);
	$pid = inserttable('post', $setarr, 1);
	
	//更新统计数据
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')." 
		SET replynum=replynum+1, lastpost='$_SGLOBAL[timestamp]', lastauthor='$_SGLOBAL[supe_username]', lastauthorid='$_SGLOBAL[supe_uid]' 
		WHERE tid='$tid'");
	
	//积分
	updatespacestatus('get', 'post');
	
	//普通回复
	if(empty($post) && $thread['uid'] != $_SGLOBAL['supe_uid']) {
		$fs = array();
		$fs['icon'] = 'post';
		$fs['body_template'] = '';
		$fs['body_data'] = array();
		$fs['body_general'] = '';
	
		$fs['title_template'] = lang('feed_thread_reply');
		$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$thread[uid]\">$thread[username]</a>", 'thread'=>"<a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]\">$thread[subject]</a>");
		
		if(ckprivacy('post', 1)) {
			feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
		}

		//通知
		$note = lang('note_thread_reply')." <a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]&pid=$pid\" target=\"_blank\">$thread[subject]</a>";
		notification_add($thread['uid'], 'post', $note);
		
	} elseif ($post) {
		$note = lang('note_post_reply', array("space.php?uid=$thread[uid]&do=thread&id=$thread[tid]", $thread['subject'], "space.php?uid=$thread[uid]&do=thread&id=$thread[tid]&pid=$pid"));
		notification_add($post['uid'], 'post', $note);
	}

	//跳转
	showmessage('do_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid&pid=$pid", 0);

}

$pid = empty($_GET['pid'])?0:intval($_GET['pid']);

//回帖编辑
if($_GET['op'] == 'edit') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid' AND uid='$_SGLOBAL[supe_uid]'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}
	
	//主题帖
	if($post['isthread']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$post[tid]'");
		$thread = $_SGLOBAL['db']->fetch_array($query);
	}

	//提交编辑
	if(submitcheck('posteditsubmit')) {
	
		$message = getstr($_POST['message'], 0, 1, 1, 1, 2);
		if(strlen($message) < 4) showmessage('content_is_too_short');
		
		//主题帖
		if($post['isthread']) {
			$subject = getstr($_POST['subject'], 80, 1, 1, 1);
			if(strlen($subject) < 4) showmessage('title_not_too_little');

			updatetable('thread', array('subject'=>$subject), array('tid'=>$post['tid']));
		}
		
		//内容
		updatetable('post', array('message'=>$message, 'pic'=>getpicurl($_POST['pic'])), array('pid'=>$post['pid']));
		
		showmessage('do_success', $_POST['refer'], 0);
	}
	
	$post['message'] = html2bbcode($post['message']);//显示用

} elseif($_GET['op'] == 'delete') {
	
	include_once(S_ROOT.'./source/function_delete.php');
	
	if(submitcheck('postdeletesubmit')) {
		if($delposts = deleteposts(array($pid))) {
			$post = $delposts[0];
			if($post['isthread']) {
				$url = "space.php?uid=$post[uid]&do=thread&tagid=$post[tagid]";
			} else {
				$url = $_POST['refer'];
			}
			showmessage('do_success', $url, 0);
		} else {
			showmessage('no_privilege');
		}
	}

} elseif($_GET['op'] == 'reply') {
	
	$pid = intval($_GET['pid']);
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('posting_does_not_exist');
	}

} elseif($_GET['op'] == 'digest') {
	$tid = intval($_GET['tid']);
	
	include_once(S_ROOT.'./source/function_op.php');
	digestthreads(array($tid), isset($_GET['cancel'])?0:1);
	showmessage('do_success');
	
} elseif($_GET['op'] == 'top') {
	$tid = intval($_GET['tid']);
	
	include_once(S_ROOT.'./source/function_op.php');
	topthreads(array($tid), isset($_GET['cancel'])?0:1);
	showmessage('do_success');
	
} else {
	
	if(!checkperm('allowthread')) {
		showmessage('no_privilege');
	}
	
	//发起话题
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	if($tagid) {
		$mtag = ckmtagspace($tagid);
	}
	if(!$mtag) {
		include_once(S_ROOT.'./data/data_profield.php');
		
		$tagid = 0;
		
		//我的选吧列表
		$mtaglist = array();
		$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid,field.close FROM ".tname('tagspace')." main 
			LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid 
			WHERE uid='$_SGLOBAL[supe_uid]'");
		$havemtag = false;
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$havemtag = true;
			if(empty($value['close']) && $value['membernum']>=$_SCONFIG['mtagminnum']) {
				$mtaglist[$value['fieldid']][$value['tagid']] = $value;
			}
		}
		
		if(empty($mtaglist)) {
			if($havemtag) {
				showmessage('no_mtag_allow_thread');
			} else {
				showmessage('settings_of_your_mtag');
			}
		}
	}
}

//模板
include template('cp_thread');

//判读是否是组员
function ckmtagspace($tagid) {
	global $_SGLOBAL, $_SCONFIG;
	
	$count = 0;
	if($tagid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$tagid'");
		if($mtag = $_SGLOBAL['db']->fetch_array($query)) {
			//判断是否关闭
			if($mtag['close']) {
				showmessage('mtag_close');
			}
			if(checkperm('managethread')) {
				$count = 1;
			} else {
				$count = getcount('tagspace', array('tagid'=>$tagid, 'uid'=>$_SGLOBAL['supe_uid']));
			}
			//判断是否满足人数要求
			if($mtag['membernum'] < $_SCONFIG['mtagminnum']) {
				showmessage('mtag_minnum_erro', '', 1, array($_SCONFIG['mtagminnum']));
			}
		}
	} else {
		showmessage('first_select_a_mtag');
	}
	if(empty($count)) {
		include_once(S_ROOT.'./data/data_profield.php');
		$mtag['title'] = $_SGLOBAL['profield'][$mtag['fieldid']]['title'];
		showmessage('mtag_no_manage', '', 1, array("cp.php?ac=mtag", $mtag['title'], $mtag['tagname']));
	}
	return $mtag;
}

?>