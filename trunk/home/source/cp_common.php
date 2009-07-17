<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_common.php 10978 2009-01-14 02:39:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':trim($_GET['op']);

if($op == 'logout') {
	//删除session
	if($_SGLOBAL['supe_uid']) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$_SGLOBAL[supe_uid]'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]'");//管理平台
	}

	include_once S_ROOT.'./uc_client/client.php';
	$ucsynlogout = uc_user_synlogout();
	
	clearcookie();
	ssetcookie('_refer', '');
	showmessage('security_exit', 'index.php', 1, array($ucsynlogout));

} elseif($op == 'seccode') {
	
	if(ckseccode(trim($_GET['code']))) {
		showmessage('succeed');
	} else {
		showmessage('incorrect_code');
	}
	
} elseif($op == 'report') {
	
	$_GET['idtype'] = trim($_GET['idtype']);
	$_GET['id'] = intval($_GET['id']);
	$uidarr = $report = array();
	
	if(!in_array($_GET['idtype'], array('picid', 'blog', 'album', 'mtag', 'thread', 'share', 'space')) || empty($_GET['id'])) {
		showmessage('report_error');
	}
	//获取举报记录
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('report')." WHERE id='$_GET[id]' AND idtype='$_GET[idtype]'");
	if($report = $_SGLOBAL['db']->fetch_array($query)) {
		$uidarr = unserialize($report['uids']);
		if($uidarr[$space['uid']]) {
			showmessage('repeat_report');
		}
	}
	
	if(submitcheck('reportsubmit')) {
		$reason = getstr($_POST['reason'], 150, 1, 1);
		
		$reason = "<li><strong><a href=\"space.php?uid=$space[uid]\" target=\"_blank\">$_SGLOBAL[supe_username]</a>:</strong> ".$reason.' ('.sgmdate('m-d H:i').')</li>';
		
		if($report) {
			$uidarr[$space['uid']] = $space['username'];
			$uids = addslashes(serialize($uidarr));
			$reason = addslashes($report['reason']).$reason;
			$_SGLOBAL['db']->query("UPDATE ".tname('report')." SET num=num+1, reason='$reason', dateline='$_SGLOBAL[timestamp]', uids='$uids' WHERE rid='$report[rid]'");
		} else {
			$uidarr[$space['uid']] = $space['username'];
			
			$setarr = array(
				'id' => $_GET['id'],
				'idtype' => $_GET['idtype'],
				'num' => 1,
				'new' => 1,
				'reason' => $reason,
				'uids' => addslashes(serialize($uidarr)),
				'dateline' => $_SGLOBAL['timestamp']
			);
			inserttable('report', $setarr);
		}
		showmessage('report_success');
	}
	
	//判断是否是被忽略的举报
	if(isset($report['num']) && $report['num'] < 1) {
		showmessage('the_normal_information');
	}
	
	$reason = explode("\r\n", trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", data_get('reason'))));
	if(is_array($reason) && count($reason) == 1 && empty($reason[0])) {
		$reason = array();
	}
	
} elseif($op == 'manage') {
	
	$_GET['idtype'] = trim($_GET['idtype']);
	$_GET['id'] = intval($_GET['id']);
	
	$uid = $perm = '';
	switch ($_GET['idtype']) {
		case 'picid':
			$uid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT uid FROM ".tname('pic')." WHERE picid='$_GET[id]'"), 0);
			$perm = 'managealbum';
			$urlplus = "ac=pic&picid=$_GET[id]";
			break;
		case 'album':
			$uid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT uid FROM ".tname('album')." WHERE albumid='$_GET[id]'"), 0);
			$perm = 'managealbum';
			$urlplus = "ac=album&albumid=$_GET[id]";
			break;
		case 'blog':
			$uid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT uid FROM ".tname('blog')." WHERE blogid='$_GET[id]'"), 0);
			$perm = 'manageblog';
			$urlplus = "ac=blog&blogid=$_GET[id]";
			break;
		case 'thread':
			$uid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT uid FROM ".tname('thread')." WHERE tid='$_GET[id]'"), 0);
			$perm = 'managethread';
			$urlplus = "ac=thread&tid=$_GET[id]";
			break;
		case 'mtag':
			$uid = 0;
			$perm = 'managemtag';
			$urlplus = "ac=mtag&tagid=$_GET[id]";
			break;
		case 'share':
			$uid = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT uid FROM ".tname('share')." WHERE sid='$_GET[id]'"), 0);
			$perm = 'manageshare';
			$urlplus = "ac=share&sid=$_GET[id]";
			break;
		default:
			break;
	}
	
	if($uid != $space['uid'] && !checkperm($perm)) {
		showmessage('manage_no_perm');
	}
	
	if(submitcheck('managesubmit')) {
		showmessage('do_success', "admincp.php?searchsubmit=yes&perpage=20&$urlplus", 0);
	}
} elseif($op == 'ignore') {
	
	$type = empty($_GET['type'])?'':preg_replace("/[^0-9a-zA-Z\_\-\.]/", '', $_GET['type']);
	if(submitcheck('ignoresubmit')) {
		$authorid = empty($_POST['authorid']) ? 0 : intval($_POST['authorid']);
		if($type) {
			$type_uid = $type.'|'.$authorid;
			if(empty($space['privacy']['filter_note']) || !is_array($space['privacy']['filter_note'])) {
				$space['privacy']['filter_note'] = array();
			}
			$space['privacy']['filter_note'][$type_uid] = $type_uid;
			privacy_update();
		}
		showmessage('do_success', $_POST['refer']);
	}
	$formid = random(8);

} elseif($op == 'getuserapp') {
	//处理
	if(empty($_GET['subop'])) {
		//展开
		$my_userapp = array();
		foreach ($_SGLOBAL['my_userapp'] as $value) {
			if($value['allowsidenav'] && !isset($_SGLOBAL['userapp'][$value['appid']])) {
				$my_userapp[] = $value;
			}
		}
	} else {
		$my_userapp = $_SGLOBAL['my_menu'];
	}
} elseif($op == 'closefeedbox') {
	
	ssetcookie('closefeedbox', 1);
	
} elseif($op == 'changetpl') {
	
	$dir = empty($_GET['name'])?'':str_replace('.','', trim($_GET['name']));
	if($dir && file_exists(S_ROOT.'./template/'.$dir.'/style.css')) {
		ssetcookie('mytemplate', $dir, 3600*24*365);//长期有效
	}
	showmessage('do_success', 'space.php?do=feed', 0);
}

include template('cp_common');

?>