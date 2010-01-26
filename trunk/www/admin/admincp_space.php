<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_space.php 10791 2008-12-23 03:08:11Z zhengqingpeng $
*/

if(!defined('IN_UCHOME') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./uc_client/client.php');
include_once(S_ROOT.'./data/data_usergroup.php');
@include_once(S_ROOT.'./data/data_profilefield.php');
$profilefields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];

//权限
$managespace = checkperm('managespace');
$managename = checkperm('managename');

if(!$managespace && !$managename) {
	cpmessage('no_authority_management_operation');
}

$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$result = '';
if($uid) {
	$query = $_SGLOBAL['db']->query("SELECT s.*, sf.* FROM ".tname('space')." s
		LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
		WHERE s.uid='$uid'");
	if(!$member = $_SGLOBAL['db']->fetch_array($query)) {
		cpmessage('designated_users_do_not_exist');
	}
	$member['addsize'] = intval($member['addsize']/(1024*1024));
	$member['ip'] = strlen($member['ip'])<9?'-':intval(substr($member['ip'], 0, 3)).'.'.intval(substr($member['ip'], 3, 3)).'.'.intval(substr($member['ip'], 6, 3)).'.1~255';
}
if($uid != $_SGLOBAL['supe_uid']) {
	//创始人
	if(ckfounder($uid)) {
		cpmessage('not_have_permission_to_operate_founder');
	}
}

if(submitcheck('usergroupsubmit')) {

	if(!$managespace || empty($member)) {
		cpmessage('no_authority_management_operation');
	}

	$setarr = array(
		'name' => getstr($_POST['name'], 20, 1, 1),
		'namestatus' => intval($_POST['namestatus']),
		'domain' => trim($_POST['domain']),
		'addsize'=>intval($_POST['addsize'])*1024*1024,
		'credit'=>intval($_POST['credit'])
	);

	//删除保护
	include_once(S_ROOT.'./uc_client/client.php');
	if($_POST['flag'] == 1) {
		$result = uc_user_addprotected(array($member['username']), $_SGLOBAL['supe_username']);
	} else {
		$_POST['flag'] = 0;
		$result = uc_user_deleteprotected(array($member['username']), $_SGLOBAL['supe_username']);
	}
	if($result) {
		$setarr['flag'] = $_POST['flag'];
	}

	if($uid != $_SGLOBAL['supe_uid'] || ckfounder($_SGLOBAL['supe_uid'])) {
		if(empty($_POST['groupid'])) {
			$_POST['groupid'] = getgroupid($_POST['credit'], 0);
		}
		$setarr['groupid'] = intval($_POST['groupid']);
	}
	updatetable('space', $setarr, array('uid'=>$uid));

	//附属表
	$setarr = array(
		'email' => getstr($_POST['email'], 100, 1, 1),
		'emailcheck' => intval($_POST['emailcheck']),
		'qq' => getstr($_POST['qq'], 20, 1, 1),
		'msn' => getstr($_POST['msn'], 80, 1, 1),
		'sex' => intval($_POST['sex']),
		'birthyear' => intval($_POST['birthyear']),
		'birthmonth' => intval($_POST['birthmonth']),
		'birthday' => intval($_POST['birthday']),
		'blood' => getstr($_POST['blood'], 5, 1, 1),
		'marry' => intval($_POST['marry']),
		'birthprovince' => getstr($_POST['birthprovince'], 20, 1, 1),
		'birthcity' => getstr($_POST['birthcity'], 20, 1, 1),
		'resideprovince' => getstr($_POST['resideprovince'], 20, 1, 1),
		'residecity' => getstr($_POST['residecity'], 20, 1, 1)
	);
	foreach ($profilefields as $field => $value) {
		if($value['formtype'] == 'select') $value['maxsize'] = 255;
		$setarr['field_'.$field] = getstr($_POST['field_'.$field], $value['maxsize'], 1, 1);
	}
	
	//清空
	if($_POST['clearcss']) $setarr['css'] = '';
	
	updatetable('spacefield', $setarr, array('uid'=>$uid));

	//生成用户变更日志
	if($_SCONFIG['my_status']) inserttable('userlog', array('uid'=>$uid, 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);

	cpmessage('do_success', "admincp.php?ac=space&op=manage&uid=$uid");

} elseif (submitcheck('listsubmit')) {

	if($ac != 'space' && !in_array($_POST['optype'], array(1,2,3,5))) {
		$_POST['optype'] = 0;
	}
	if($_POST['uids'] && is_array($_POST['uids']) && $_POST['optype']) {
		$createlog = false;
		$url = "admincp.php?ac=$ac&perpage=$_GET[perpage]&page=$_GET[page]";
		switch ($_POST['optype']) {

			case '1':
				//通过实名认证
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET namestatus='1' WHERE uid IN (".simplode($_POST['uids']).") AND name!=''");
				$url .= 'namestatus=0';
				$createlog = true;
				break;
			case '2':
				//取消实名认证
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET namestatus='0' WHERE uid IN (".simplode($_POST['uids']).")");
				$url .= 'namestatus=1';
				$createlog = true;
				break;
			case '3':
				//清空姓名
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET name='',namestatus='0' WHERE uid IN (".simplode($_POST['uids']).")");
				break;
			case '4':
				//发送邮件通知
				//批量发送邮件
				$uids = implode(',', $_POST['uids']);
				include template('admin/tpl/space_manage');
				exit();
				break;
			case '5':
				//批量打招呼
				$uids = implode(',', $_POST['uids']);
				include template('admin/tpl/space_manage');
				exit();
				break;
			case '6':
				//清空用户个性设置
				$_SGLOBAL['db']->query("UPDATE ".tname('spacefield')." SET css='' WHERE uid IN (".simplode($_POST['uids']).")");
				$createlog = true;
				break;
		}
		if($createlog) {
			$comma = '';
			foreach($_POST['uids'] as $key => $uid) {
				$uid = intval($uid);
				$values .= "$comma('$uid', 'update', '$_SGLOBAL[timestamp]')";
				$comma = ',';
			}
			if($_SCONFIG['my_status']) $_SGLOBAL['db']->query("REPLACE INTO ".tname('userlog')." (uid, action, dateline) VALUES $values");
		}

	}
	cpmessage('do_success', $url);

} elseif (submitcheck('sendemailsubmit')) {

	$touids = empty($_POST['uids'])?array():explode(',', $_POST['uids']);
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);
	if(empty($subject) && empty($message)) $touids = array();

	if($touids) {
		include_once(S_ROOT.'./source/function_cp.php');
		$query = $_SGLOBAL['db']->query("SELECT email, emailcheck FROM ".tname('spacefield')." WHERE uid IN (".simplode($touids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if($value['email']) {
				smail(0, $value['email'], $subject, $message);
			}
		}
	}
	cpmessage('do_success', "admincp.php?ac=$ac&perpage=$_GET[perpage]&page=$_GET[page]");

} elseif (submitcheck('pokesubmit')) {
	//打招呼
	$touids = empty($_POST['uids'])?array():explode(',', $_POST['uids']);
	$note = getstr($_POST['note'], 50, 1, 1);
	if($touids) {
		$replaces = array();
		foreach ($touids as $touid) {
			if($touid && $touid != $_SGLOBAL['supe_uid']) {
				$replaces[] = "('$touid','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]','$note','$_SGLOBAL[timestamp]')";
			}
		}
		if($replaces) {
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('poke')." (uid,fromuid,fromusername,note,dateline) VALUES ".implode(',', $replaces));
		}
	}
	cpmessage('do_success', "admincp.php?ac=$ac&perpage=$_GET[perpage]&page=$_GET[page]");
}

if($_GET['op'] == 'delete') {

	if(!$managespace) {
		cpmessage('no_authority_management_operation');
	}

	include_once(S_ROOT.'./source/function_delete.php');
	$_GET['uid'] = intval($_GET['uid']);
	if(!empty($_GET['uid']) && deletespace($_GET['uid'])) {
		cpmessage('do_success', 'admincp.php?ac=space');
	} else {
		cpmessage('choose_to_delete_the_space', 'admincp.php?ac=space');
	}
} elseif($_GET['op'] == 'deleteavatar') {
	
	$uid = intval($_GET['uid']);
	uc_user_deleteavatar($uid);
	updatetable('space', array('avatar'=>0), array('uid'=>$uid));
	cpmessage('do_success', 'admincp.php?ac=space&op=manage&uid='.$uid);
	
} elseif($_GET['op'] == 'manage') {

	if(!$managespace) {
		cpmessage('no_authority_management_operation');
	}

	//性别
	$sexarr = array($member['sex']=>' checked');

	//生日:年
	$birthyeayhtml = '';
	$nowy = sgmdate('Y');
	for ($i=1; $i<80; $i++) {
		$they = $nowy - $i;
		$selectstr = $they == $member['birthyear']?' selected':'';
		$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
	}
	//生日:月
	$birthmonthhtml = '';
	for ($i=1; $i<13; $i++) {
		$selectstr = $i == $member['birthmonth']?' selected':'';
		$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
	}
	//生日:日
	$birthdayhtml = '';
	for ($i=1; $i<32; $i++) {
		$selectstr = $i == $member['birthday']?' selected':'';
		$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
	}
	//血型
	$bloodhtml = '';
	foreach (array('A','B','O','AB') as $value) {
		$selectstr = $value == $member['blood']?' selected':'';
		$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
	}
	//婚姻
	$marryarr = array($member['marry'] => ' selected');

	//栏目表单
	$profilefields = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profilefield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$fieldid = $value['fieldid'];
		$value['formhtml'] = '';

		if($value['formtype'] == 'text') {
			//input框长度
			$value['note'] = empty($value['note'])?'':$value['note'];
			$value['formhtml'] = "<input type=\"text\" name=\"field_$fieldid\" value=\"".$member["field_$fieldid"]."\" class=\"t_input\">";
		} else {
			$value['formhtml'] .= "<select name=\"field_$fieldid\">";
			if(empty($value['required'])) {
				$value['formhtml'] .= "<option value=\"\">---</option>";
			}
			$optionarr = explode("\n", $value['choice']);
			foreach ($optionarr as $ov) {
				$ov = trim($ov);
				if($ov) {
					$selectstr = $member["field_$fieldid"]==$ov?' selected':'';
					$value['formhtml'] .= "<option value=\"$ov\"$selectstr>$ov</option>";
				}
			}
			$value['formhtml'] .= "</select>";
		}

		$profilefields[$value['fieldid']] = $value;
	}

	$groupidarr = array($member['groupid'] => ' selected');

	include template('admin/tpl/space_manage');
	exit();
}

$mpurl = 'admincp.php?ac='.$ac;

//处理搜索
$intkeys = array('uid', 'groupid', 'namestatus', 'avatar');
$strkeys = array('username');
$randkeys = array(array('sstrtotime','dateline'), array('sstrtotime','updatetime'), array('intval','credit'));
$likekeys = array('name');
$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 's.');
$wherearr = $results['wherearr'];
$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
$mpurl .= '&'.implode('&', $results['urls']);

if(isset($_GET['namestatus']) && $_GET['namestatus']=='0') {
	$wheresql.=" AND s.name!=''";
}


//排序
$orders = getorders(array('dateline', 'updatetime', 'friendnum', 'credit', 'viewnum'), '', 's.');
$ordersql = $orders['sql'];
if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
$orderby = array($_GET['orderby']=>' selected');
$ordersc = array($_GET['ordersc']=>' selected');

//显示分页
$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
if(!in_array($perpage, array(20,50,100))) $perpage = 20;
$mpurl .= '&perpage='.$perpage;
$perpages = array($perpage => ' selected');

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;
//检查开始数
ckstart($start, $perpage);

$list = array();
$multi = '';

$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('space')." s WHERE $wheresql"), 0);
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT s.* FROM ".tname('space')." s WHERE $wheresql $ordersql LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['grouptitle'] = $_SGLOBAL['usergroup'][$value['groupid']]['grouptitle'];
		$value['addsize'] = formatsize($value['addsize']);
		$list[] = $value;
	}
	$multi = multi($count, $perpage, $page, $mpurl);
}

?>