<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_pm.php 7265 2008-05-04 07:58:41Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

include_once S_ROOT.'./uc_client/client.php';

if($_GET['op'] == 'checknewpm') {
	
	//检查当前用户
	if($_SGLOBAL['supe_uid']) {
		$ucnewpm = uc_pm_checknew($_SGLOBAL['supe_uid']);
		if($_SGLOBAL['member']['newpm'] != $ucnewpm) {
			updatetable('session', array('newpm'=>$ucnewpm), array('uid'=>$_SGLOBAL['supe_uid']));
		}
	}
	ssetcookie('checkpm', 1, 30);
	exit();
	
} elseif($_GET['op'] == 'delete') {
	
	$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);
	$folder = $_GET['folder']=='inbox'?'inbox':'outbox';

	if(submitcheck('deletesubmit')) {
		$retrun = uc_pm_delete($_SGLOBAL['supe_uid'], $folder, array($pmid));
		if($retrun>0) {
			showmessage('do_success', $_POST['refer']);
		} else {
			showmessage('this_message_could_not_be_deleted');
		}
	}
} elseif($_GET['op'] == 'send') {
	
	//判断是否发布太快
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}
	
	$pmid = empty($_GET['pmid'])?0:floatval($_GET['pmid']);

	if(submitcheck('pmsubmit')) {

		//发送消息
		$username = empty($_POST['username'])?'':$_POST['username'];
		
		$subject = getstr($_POST['subject'], 80, 1, 1, 1);
		$message = getstr($_POST['message'], 0, 1, 1, 1);
		if(empty($subject) && empty($message)) {
			showmessage('unable_to_send_air_news');
		}
		
		$return = 0;
		if($uid) {
			//直接给一个用户发PM
			$return = uc_pm_send($_SGLOBAL['supe_uid'], $uid, $subject, $message, 1, $pmid, 0);
		} elseif($username) {
			$newusers = array();
			$users = explode(',', $username);
			foreach ($users as $value) {
				$value = trim($value);
				if($value) {
					$newusers[] = $value;
				}
			}
			if($newusers) {
				$return = uc_pm_send($_SGLOBAL['supe_uid'], implode(',', $newusers), $subject, $message, 1, $pmid, 1);
			}
		}
		if($return > 0) {
			//更新最后发布时间
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastpost='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
			showmessage('do_success', "cp.php?ac=pm&pmid=$return", 0);
		} else {
			showmessage('message_can_not_send');
		}
	}
	
} elseif($_GET['op'] == 'ignore') {
	if(submitcheck('ignoresubmit')) {
		uc_pm_blackls_set($_SGLOBAL['supe_uid'], $_POST['ignorelist']);
		showmessage('do_success', 'cp.php?ac=pm&view=ignore');
	}
} elseif($_GET['op'] == 'post') {
	//发送
	$friends = array();
	if($space['frienduid']) {
		$query = $_SGLOBAL['db']->query("SELECT fuid AS uid, fusername AS username FROM ".tname('friend')." WHERE uid=$_SGLOBAL[supe_uid] AND status='1'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['username'] = saddslashes($value['username']);
			$friends[] = $value;
		}
	}
} else {
	if($pmid) {
		
		$list = uc_pm_view($_SGLOBAL['supe_uid'], $pmid);

		$msgfromid = $list[0]['msgfromid'];
		$msgtoid = $list[0]['msgtoid'];

	} else {
		$view = (!empty($_GET['view']) && in_array($_GET['view'], array('inbox', 'outbox', 'newbox', 'announce', 'ignore')))?$_GET['view']:'newbox';
		$actives = array($view=>' class="active"');
			
		if($_GET['view'] == 'ignore') {
			$ignorelist = uc_pm_blackls_get($_SGLOBAL['supe_uid']);
		} else {
			//分页
			$perpage = 10;
			$page = empty($_GET['page'])?0:intval($_GET['page']);
			if($page<1) $page = 1;
			$start = ($page-1)*$perpage;
			
			if($view == 'announce') {
				$result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, 'inbox', 'announcepm', 100);
			} else {
				$result = uc_pm_list($_SGLOBAL['supe_uid'], $page, $perpage, $view, '', 100);
			}
			
			$count = $result['count'];
			$list = $result['data'];
		
			$multi = array();
			if($view != 'newbox') {
				$multi['html'] = multi($count, $perpage, $page, "cp.php?ac=pm&view=$view");
			}
			
			if($_SGLOBAL['member']['newpm']) {
				//取消新短消息提示
				updatetable('session', array('newpm'=>0), array('uid'=>$_SGLOBAL['supe_uid']));
				//UCenter
				uc_pm_ignore($_SGLOBAL['supe_uid']);
			}
		}
	}
}

include_once template("cp_pm");

?>