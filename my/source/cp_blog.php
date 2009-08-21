<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_blog.php 7353 2008-05-13 01:47:05Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//检查信息
$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$blog = array();
if($blogid) {
	$query = $_SGLOBAL['db']->query("SELECT bf.*, b.* FROM ".tname('blog')." b 
		LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid 
		WHERE b.blogid='$blogid'");
	$blog = $_SGLOBAL['db']->fetch_array($query);
}

//权限检查
if(empty($blog)) {
	if(!checkperm('allowblog')) {
		showmessage('no_authority_to_add_log');
	}
	//判断是否发布太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
} else {
	if($_GET['op'] != 'trace' && $_SGLOBAL['supe_uid'] != $blog['uid'] && !checkperm('manageblog')) {
		showmessage('no_authority_operation_of_the_log');
	}
}

//添加编辑操作
if(submitcheck('blogsubmit')) {
	include_once(S_ROOT.'./source/function_blog.php');
	if($blog = blog_post($_POST, $blog)) {
		showmessage('do_success', 'space.php?uid='.$blog['uid'].'&do=blog&id='.$blog['blogid'], 0);
	} else {
		showmessage('that_should_at_least_write_things');
	}
}

if($_GET['op'] == 'delete') {
	//删除
	if(submitcheck('deletesubmit')) {
		include_once(S_ROOT.'./source/function_delete.php');
		if(deleteblogs(array($blogid))) {
			showmessage('do_success', "space.php?uid=$blog[uid]&do=blog&view=me");
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
	
} elseif($_GET['op'] == 'trace') {
	if($blog['uid'] == $_SGLOBAL['supe_uid']) {
		showmessage('trace_no_self');
	}
	//检查是否留过脚印
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blog[blogid]' AND uid='$_SGLOBAL[supe_uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('trace_have');
	} else {
		$setarr = array(
			'blogid' => $blog['blogid'],
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp']
		);
		inserttable('trace', $setarr, 0, true);
		//更新日志脚印数
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET tracenum=tracenum+1 WHERE blogid='$blog[blogid]'");
	}
	showmessage('trace_success', "space.php?uid=$blog[uid]&do=blog&id=$blog[blogid]");
} else {
	//添加编辑
	//获取个人分类
	$classarr = $blog['uid']?getclassarr($blog['uid']):getclassarr($_SGLOBAL['supe_uid']);
	//获取相册
	$albums = getalbums($_SGLOBAL['supe_uid']);
	
	$tags = empty($blog['tag'])?array():unserialize($blog['tag']);
	$blog['tag'] = implode(' ', $tags);
	
	$blog['target_names'] = '';
	
	$friendarr = array($blog['friend'] => ' selected');
	
	$passwordstyle = $selectgroupstyle = 'display:none';
	if($blog['friend'] == 4) {
		$passwordstyle = '';
	} elseif($blog['friend'] == 2) {
		$selectgroupstyle = '';
		if($blog['target_ids']) {
			$names = array();
			$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('space')." WHERE uid IN ($blog[target_ids])");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$names[] = $value['username'];
			}
			$blog['target_names'] = implode(' ', $names);
		}
	}
	
	$blog['message'] = shtmlspecialchars($blog['message']);
	
	$allowhtml = checkperm('allowhtml');
	
	//好友组
	$groups = getfriendgroup();
	
	//菜单激活
	$menuactives = array('space'=>' class="active"');
}

include_once template("cp_blog");

?>