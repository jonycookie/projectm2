<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp_mtag.php 7257 2008-05-04 02:20:28Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managemtag')) {
	cpmessage('no_authority_management_operation');
}

@include_once(S_ROOT.'./data/data_profield.php');

if(submitcheck('opsubmit')) {
	if($_POST['optype'] == 'delete') {
		include_once(S_ROOT.'./source/function_delete.php');
		if(!empty($_POST['ids']) && deletemtag($_POST['ids'])) {
			cpmessage('do_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_delete_the_columns_tag', $_POST['mpurl']);
		}
	} elseif($_POST['optype'] == 'merge') {
		$_POST['newfieldid'] = intval($_POST['newfieldid']);
		$_POST['newtagname'] = shtmlspecialchars(trim($_POST['newtagname']));
		//检索新tag存在否
		$newtagid = getcount('mtag', array('tagname'=>$_POST['newtagname'], 'fieldid'=>$_POST['newfieldid']), 'tagid');
		if(empty($newtagid)) {
			cpmessage('designated_to_merge_the_columns_do_not_exist', $_POST['mpurl']);
		}
		//开始合并
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && mergemtag($_POST['ids'], $newtagid)) {
			cpmessage('the_successful_merger_of_the_designated_columns', $_POST['mpurl']);
		} else {
			cpmessage('columns_option_to_merge_the_tag', $_POST['mpurl']);
		}
	} elseif($_POST['optype'] == 'close' || $_POST['optype'] == 'open') {
		include_once(S_ROOT.'./source/function_op.php');
		if(!empty($_POST['ids']) && closemtag($_POST['ids'], $_POST['optype'])) {
			cpmessage('lock_open_designated_columns_tag_success', $_POST['mpurl']);
		} else {
			cpmessage('choose_to_operate_columns_tag', $_POST['mpurl']);
		}
	}
} elseif(submitcheck('moderatorsubmit')) {
	
	$tagid = intval($_POST['tagid']);
	if(empty($tagid)) {
		cpmessage('moderator_choice_to_set_up_the_election_it');
	}
	
	//判断人员选择
	$muids = '';
	if(!empty($_POST['moderator'])) {
		$muidarr = array($_POST['moderator'][0] => $_POST['moderator'][0]);
		$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('tagspace')." WHERE tagid='$tagid' AND username IN (".simplode($_POST['moderator']).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$muidarr[$value['username']] = $value['username'];
		}
		$muids = empty($muidarr)?'':implode("\t", $muidarr);
	}
	
	//更新版主
	updatetable('mtag', array('moderator'=>$muids), array('tagid'=>$tagid));
	
	cpmessage('do_success', $_POST['refer']);
}

if(empty($_GET['op'])) {
	$mpurl = 'admincp.php?ac=mtag';
	
	//处理搜索
	$intkeys = array('close', 'fieldid');
	$strkeys = array();
	$randkeys = array(array('intval','membernum'));
	$likekeys = array('tagname');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
	$wherearr = $results['wherearr'];
	
	$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
	$mpurl .= '&'.implode('&', $results['urls']);

	//排序
	$orders = getorders(array('membernum'), 'tagid DESC');
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
	
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('mtag')." WHERE $wheresql"), 0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE $wheresql $ordersql LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
		$multi = multi($count, $perpage, $page, $mpurl);
	}
	
} elseif ($_GET['op'] = 'moderator') {

	$tagid = intval($_GET['tagid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid='$tagid'");
	if(!$mtag = $_SGLOBAL['db']->fetch_array($query)) {
		cpmessage('moderator_choice_to_set_up_the_election_it');
	}
	
	//现在的版主
	$moderator = array();
	if($mtag['moderator']) {
		$moderator = explode("\t", $mtag['moderator']);
	}

	//已有成员
	$memberlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$memberlist[] = $value;
	}
	
	include template('admin/tpl/mtag_moderator');
	exit();
}

?>