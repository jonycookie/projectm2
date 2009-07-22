<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_doing.php 10761 2008-12-18 06:55:26Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$id = empty($_GET['id'])?0:intval($_GET['id']); 

if(empty($_POST['refer'])) $_POST['refer'] = "space.php?do=doing&view=me";

if(submitcheck('addsubmit')) {

	$add_doing = 1;
	if(empty($_POST['spacenote'])) {
		if(!checkperm('allowdoing')) {
			showmessage('no_privilege');
		}
		
		//实名认证
		ckrealname('doing');
		
		//新用户见习
		cknewuser();
	
		//验证码
		if(checkperm('seccode') && !ckseccode($_POST['seccode'])) {
			showmessage('incorrect_code');
		}
	
		//判断是否操作太快
		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast', '', 1, array($waittime));
		}
	} else {
		if(!checkperm('allowdoing')) {
			$add_doing = 0;
		}
		if(checkperm('seccode') && !ckseccode($_POST['seccode'])) {
			$add_doing = 0;
		}
		//实名
		if(!ckrealname('doing', 1)) {
			$add_doing = 0;
		}
		//新用户
		if(!cknewuser(1)) {
			$add_doing = 0;
		}
		$waittime = interval_check('post');
		if($waittime > 0) {
			$add_doing = 0;
		}
	}
	
	//获取心情
	$mood = 0;
	preg_match("/\[em\:(\d+)\:\]/s", $_POST['message'], $ms);
	$mood = empty($ms[1])?0:intval($ms[1]);

	$message = getstr($_POST['message'], 200, 1, 1, 1);
	//替换表情
	$message = preg_replace("/\[em:(.+?):]/is", "<img src=\"image/face/\\1.gif\" class=\"face\">", $message);
	$message = preg_replace("/\<br.*?\>/is", ' ', $message);
	
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}
	
	if($add_doing) {
		$setarr = array(
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp'],
			'message' => $message,
			'mood' => $mood,
			'ip' => getonlineip()
		);
		//入库
		$newdoid = inserttable('doing', $setarr, 1);
	}
	
	//更新空间note
	$setarr = array(mood=>$mood, 'updatetime'=>$_SGLOBAL['timestamp']);
	updatetable('space', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	
	$note_text = getstr($_POST['message'], 200, 1, 1, 1, 0, -1);
	$note_message = strlen($message)>200?$note_text:$message;
	$setarr = array('note'=>$note_message);
	if(!empty($_POST['spacenote'])) {
		$setarr['spacenote'] = $note_text;
	}
	updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	

	//事件feed
	$fs = array();
	$fs['icon'] = 'doing';
	$fs['title_template'] = cplang('feed_doing_title');
	$fs['title_data'] = array('message'=>$message);
	
	$fs['body_template'] = '';
	$fs['body_data'] = array('doid'=>$newdoid);
	$fs['body_general'] = '';

	if($add_doing && ckprivacy('doing', 1)) {
		feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
	}

	showmessage('do_success', 'space.php?do=doing&view=me', 0);

} elseif (submitcheck('commentsubmit')) {
	
	if(!checkperm('allowdoing')) {
		showmessage('no_privilege');
	}
	
	//实名认证
	ckrealname('doing');
	
	//新用户见习
	cknewuser();
	
	//判断是否操作太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', 1, array($waittime));
	}
	
	$message = getstr($_POST['message'], 200, 1, 1, 1);
	//替换表情
	$message = preg_replace("/\[em:(.+?):]/is", "<img src=\"image/face/\\1.gif\" class=\"face\">", $message);
	$message = preg_replace("/\<br.*?\>/is", ' ', $message);
	if(strlen($message) < 1) {
		showmessage('should_write_that');
	}
	
	$updo = array();
	if($id) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('docomment')." WHERE id='$id'");
		$updo = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($updo) && $doid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE doid='$doid'");
		$updo = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($updo)) {
		showmessage('docomment_error');
	} else {
		//黑名单
		if(isblacklist($updo['uid'])) {
			showmessage('is_blacklist');
		}
	}
	
	$updo['id'] = intval($updo['id']);
	$updo['grade'] = intval($updo['grade']);
	
	$setarr = array(
		'doid' => $updo['doid'],
		'upid' => $updo['id'],
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'ip' => getonlineip(),
		'grade' => $updo['grade']+1
	);
	
	//最多层级
	if($updo['grade'] >= 3) {
		$setarr['upid'] = $updo['upid'];//更母一个级别
	}

	$newid = inserttable('docomment', $setarr, 1);
	
	//更新回复数
	$_SGLOBAL['db']->query("UPDATE ".tname('doing')." SET replynum=replynum+1 WHERE doid='$updo[doid]'");
	
	//通知
	if($updo['uid'] != $_SGLOBAL['supe_uid']) {
		$note = cplang('note_doing_reply', array("space.php?do=doing&doid=$updo[doid]&highlight=$newid"));
		notification_add($updo['uid'], 'doing', $note);
	}

	$_POST['refer'] = preg_replace("/((\#|\&highlight|\-highlight|\.html).*?)$/", '', $_POST['refer']);
	if(strexists($_POST['refer'], '?')) {
		$_POST['refer'] .= "&highlight={$newid}#dl{$updo[doid]}";
	} else {
		$_POST['refer'] .= "-highlight-{$newid}.html#dl{$updo[doid]}";
	}
	showmessage('do_success', $_POST['refer'], 0);

}

//删除
if($_GET['op'] == 'delete') {
	
	if(submitcheck('deletesubmit')) {
		if($id) {
			$allowmanage = checkperm('managedoing');
			$query = $_SGLOBAL['db']->query("SELECT dc.*, d.uid as duid FROM ".tname('docomment')." dc, ".tname('doing')." d WHERE dc.id='$id' AND dc.doid=d.doid");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid'] ||  $value['duid'] == $_SGLOBAL['supe_uid'] ) {
					$_SGLOBAL['db']->query("DELETE FROM ".tname('docomment')." WHERE (id='$id' || upid='$id')");
					$replynum = getcount('docomment', array('doid'=>$value['doid']));
					updatetable('doing', array('replynum'=>$replynum), array('doid'=>$value['doid']));
				}
			}
		} else {
			include_once(S_ROOT.'./source/function_delete.php');
			deletedoings(array($doid));
		}
		
		showmessage('do_success', $_POST['refer'], 0);
	}
}

include template('cp_doing');

?>