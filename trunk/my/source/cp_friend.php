<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_friend.php 7281 2008-05-05 07:36:12Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$space['theme'] = $space['css'] = '';

$op = empty($_GET['op'])?'':$_GET['op'];
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);

$actives = array($op=>' class="active"');

if($op == 'add') {
	
	//检测用户
	if($uid == $_SGLOBAL['supe_uid']) {
		showmessage('firend_self_error');
	}
	$tospace = getspace($uid);
	if(empty($tospace)) {
		showmessage('space_does_not_exist');
	}
	
	//用户组
	$groups = getfriendgroup();
	
	//检测现在状态
	$status = getfriendstatus($_SGLOBAL['supe_uid'], $uid);
	if($status == 1) {
		showmessage('you_have_friends');
	} else {
		//对方是否把自己加为了好友
		$fstatus = getfriendstatus($uid, $_SGLOBAL['supe_uid']);
		if($fstatus == -1) {
			//对方没有加好友
			if($status == -1) {
				//检查数目
				$maxfriendnum = checkperm('maxfriendnum');
				if($maxfriendnum && $space['friendnum'] >= $maxfriendnum) {
					showmessage('enough_of_the_number_of_friends');
				}
				
				//添加单向好友
				if(submitcheck('addsubmit')) {
					$setarr = array(
						'uid' => $_SGLOBAL['supe_uid'],
						'fuid' => $uid,
						'fusername' => addslashes($tospace['username']),
						'gid' => intval($_POST['gid']),
						'note' => getstr($_POST['note'], 50, 1, 1)
					);
					inserttable('friend', $setarr);
					
					showmessage('request_has_been_sent');
				} else {
					include_once template('cp_friend');
					exit();
				}
			} else {
				showmessage('waiting_for_the_other_test');
			}
		} else {
			//对方加了好友
			if(submitcheck('add2submit')) {
				//成为好友
				$gid = intval($_POST['gid']);
				
				friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $uid, addslashes($tospace['username']), 'add', $gid);
				
				//事件发布
				//加好友不发布事件
				$fs = array();
				$fs['icon'] = 'friend';
				
				$fs['title_template'] = lang('feed_friend_title');
				$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$tospace[uid]\">$tospace[username]</a>");
				
				$fs['body_template'] = '';
				$fs['body_data'] = array();
				$fs['body_general'] = '';
	
				if(ckprivacy('friend', 1)) {
					feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
				}
				
				//通知
				notification_add($uid, 'friend', lang('note_friend_add'));
				
				//更新uc
				include_once(S_ROOT.'./uc_client/client.php');
				uc_friend_add($_SGLOBAL['supe_uid'], $uid);
				
				showmessage('friends_add', $_POST['refer'], 1, array($tospace['username']));
			} else {
				$op = 'add2';
				include_once template('cp_friend');
				exit();
			}
		}
	}

} elseif($op == 'ignore') {
	
	//检测用户
	if(!empty($_GET['confirm'])) {
		//撤消好友
		if(empty($_POST['refer'])) $_POST['refer'] = 'space.php?do=friend';
		friend_update($_SGLOBAL['supe_uid'], $_SGLOBAL['supe_username'], $uid, '', 'ignore');
		friend_cache($_SGLOBAL['supe_uid']);
		showmessage('do_success', $_POST['refer'], 0);
	}
	
} elseif($op == 'syn') {
	
	//获取用户中心我的fans列表
	if(isset($_SCOOKIE['synfriend'])) {
		exit();
	}
	
	include_once S_ROOT.'./uc_client/client.php';
	$buddylist = uc_friend_ls($_SGLOBAL['supe_uid'], 1, 999, 999, 2);//别人加了我

	$havas = array();
	if($buddylist && is_array($buddylist)) {
		foreach($buddylist as $key => $buddy) {
			$uids[] = $buddy['uid'];
		}
		$members = array();
		if($uids) {
			$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE uid IN (".simplode($uids).")");
			while($member = $_SGLOBAL['db']->fetch_array($query)) {
				$members[] = $member['uid'];
			}
		}
		if($members) {
			foreach($buddylist as $key => $buddy) {
				if(in_array($buddy['uid'], $members)) {
					$havas[$buddy['uid']] = $buddy;
				}
			}
		}
	}

	//查找当前好友
	if($havas) {
		$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('friend')." WHERE fuid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(isset($havas[$value['uid']])) {
				unset($havas[$value['uid']]);
			}
		}
	}
	
	//添加好友
	$inserts = array();
	if($havas) {
		foreach ($havas as $value) {
			if($_SGLOBAL['supe_uid'] != $value['uid']) {
				$value['username'] = addslashes($value['username']);
				if($value['direction'] == 3) {//双向
					$inserts[] = "('$_SGLOBAL[supe_uid]','$value[uid]','$value[username]','1')";
					$inserts[] = "('$value[uid]','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','1')";
				} else {//别人加我
					$inserts[] = "('$value[uid]','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','0')";
				}
			}
		}
	}
	if($inserts) {
		$_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status) VALUES ".implode(',',$inserts));
	}
	
	friend_cache($_SGLOBAL['supe_uid']);
	
	ssetcookie('synfriend', 1, 900);//15分钟检查一次
	exit();
	
} elseif($op == 'find') {
	
	@include_once(S_ROOT.'./data/data_profield.php');
	@include_once(S_ROOT.'./data/data_profilefield.php');
	$fields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];

	//生日:年
	$birthyeayhtml = '';
	$nowy = sgmdate('Y');
	for ($i=1; $i<80; $i++) {
		$they = $nowy - $i;
		$birthyeayhtml .= "<option value=\"$they\">$they</option>";
	}
	//生日:月
	$birthmonthhtml = '';
	for ($i=1; $i<13; $i++) {
		$birthmonthhtml .= "<option value=\"$i\">$i</option>";
	}
	//生日:日
	$birthdayhtml = '';
	for ($i=1; $i<32; $i++) {
		$birthdayhtml .= "<option value=\"$i\">$i</option>";
	}
	//血型
	$bloodhtml = '';
	foreach (array('A','B','O','AB') as $value) {
		$bloodhtml .= "<option value=\"$value\">$value</option>";
	}
	
	//自定义
	foreach ($fields as $fkey => $fvalue) {
		if($fvalue['allowsearch']) {
			if($fvalue['formtype'] == 'text') {
				$fvalue['html'] = '<input type="text" name="field_'.$fkey.'" value="" class="t_input">';
			} else {
				$fvalue['html'] = "<select name=\"field_$fkey\"><option value=\"\">---</option>";
				$optionarr = explode("\n", $fvalue['choice']);
				foreach ($optionarr as $ov) {
					$ov = trim($ov);
					if($ov) {
						$fvalue['html'] .= "<option value=\"$ov\">$ov</option>";
					}
				}
				$fvalue['html'] .= "</select>";
			}
			$fields[$fkey] = $fvalue;
		} else {
			unset($fields[$fkey]);
		}
	}
	
	//自动找好友
	$maxnum = 6;
	//好友的好友
	$nouids = $space['frienduid']?($space['frienduid'].','.$space['uid']):$space['uid'];
	$friendlist = array();
	if($space['frienduid']) {
		$query = $_SGLOBAL['db']->query("SELECT DISTINCT fuid AS uid, fusername AS username FROM ".tname('friend')."
			WHERE uid IN (".$space['frienduid'].") AND fuid NOT IN ($nouids) LIMIT 0,$maxnum");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$friendlist[] = $value;
		}
	}
	
	//居住地好友
	$residelist = array();
	$warr = array();
	if($space['resideprovince']) {
		$warr[] = "sf.resideprovince='".addslashes($space['resideprovince'])."'";
	}
	if($space['residecity']) {
		$warr[] = "sf.residecity='".addslashes($space['residecity'])."'";
	}
	if($warr) {
		$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username FROM ".tname('spacefield')." sf
			LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
			WHERE ".implode(' AND ', $warr)." AND sf.uid NOT IN ($nouids)
			LIMIT 0,$maxnum");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$residelist[] = $value;
		}
	}
	
	//性别好友
	$sexlist = array();
	$warr = array();
	if(empty($space['marry']) || $space['marry'] < 2) {//单身
		$warr[] = "sf.marry='1'";//单身
	}
	if(empty($space['sex']) || $space['sex'] < 2) {//男生
		$warr[] = "sf.sex='2'";//女生
	} else {
		$warr[] = "sf.sex='1'";//男生
	}
	if($warr) {
		$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username FROM ".tname('spacefield')." sf
			LEFT JOIN ".tname('space')." s ON s.uid=sf.uid
			WHERE ".implode(' AND ', $warr)." AND sf.uid NOT IN ($nouids)
			LIMIT 0,$maxnum");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$sexlist[] = $value;
		}
	}
	
	//人气最高的好友
	$hotlist = array();
	$query = $_SGLOBAL['db']->query("SELECT uid, username FROM ".tname('space')." WHERE uid NOT IN ($nouids) ORDER BY friendnum DESC LIMIT 0,$maxnum");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$hotlist[] = $value;
	}
	
} elseif($op == 'changegroup') {
	
	if(submitcheck('changegroupsubmit')) {
		updatetable('friend', array('gid'=>intval($_POST['group'])), array('uid'=>$_SGLOBAL['supe_uid'], 'fuid'=>$uid));
		friend_cache($_SGLOBAL['supe_uid']);
		showmessage('do_success', $_SGLOBAL['refer']);
	}
	
	//获得当前用户group
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'");
	if(!$friend = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('specified_user_is_not_your_friend');
	}
	$groupselect = array($friend['gid'] => ' selected');
	
	$groups = getfriendgroup();
	
} elseif($op == 'group') {
	
	if(submitcheck('groupsubmin')) {
		if(empty($_POST['fuids'])) {
			showmessage('please_correct_choice_groups_friend');
		}
		$ids = simplode($_POST['fuids']);
		$groupid = intval($_POST['group']);
		updatetable('friend', array('gid'=>$groupid), "uid='$_SGLOBAL[supe_uid]' AND fuid IN ($ids) AND status='1'");
		friend_cache($_SGLOBAL['supe_uid']);
		showmessage('do_success', $_SGLOBAL['refer']);
	}
	
	$perpage = 50;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	
	$list = array();
	$multi = array();
	if($space['friendnum']) {
		$groups = getfriendgroup();
		
		$theurl = 'cp.php?ac=friend&op=group';
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		}
		
		$query = $_SGLOBAL['db']->query("SELECT main.fuid AS uid,main.fusername AS username, main.gid FROM ".tname('friend')." main 
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid 
			WHERE main.uid='$space[uid]' AND main.status='1' $wheresql
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['group'] = $groups[$value['gid']];
			$list[] = $value;
		}
		$multi['html'] = multi($space['friendnum'], $perpage, $page, $theurl);
	}
	$groups = getfriendgroup();
	$groupselect = array($group => 'selected');
	
	$actives = array('groupname'=>' class="active"');
	
} elseif($op == 'request') {
	
	if(submitcheck('requestsubmin')) {
		showmessage('do_success', $_SGLOBAL['refer']);
	}
	
	//好友请求
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT f.*, s.username FROM ".tname('friend')." f 
		LEFT JOIN ".tname('space')." s ON s.uid=f.uid 
		WHERE f.fuid='$space[uid]' AND f.status='0'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}

} elseif($op == 'groupname') {
	
	if(submitcheck('groupnamesubmit')) {
		//用户组名
		foreach ($_POST['privacy']['groupname'] as $key => $value) {
			$space['privacy']['groupname'][$key] = getstr($value, 20, 1, 1);
		}
		privacy_update();
		showmessage('do_success', 'cp.php?ac=friend&op=groupname');
	}
	
	$groups = getfriendgroup();
	
	$actives = array('groupname'=>' class="active"');
}

include template('cp_friend');

?>