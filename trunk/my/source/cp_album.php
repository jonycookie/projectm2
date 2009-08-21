<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_album.php 7369 2008-05-15 03:26:49Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);

if($_GET['op'] == 'edit') {
	
	if($albumid < 1) {
		showmessage('photos_do_not_support_the_default_settings', "cp.php?ac=album&op=editpic", 0);
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
	if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}
	
	if(submitcheck('editsubmit')) {
		$_POST['albumname'] = getstr($_POST['albumname'], 50, 1, 1, 1);
		if(empty($_POST['albumname'])) {
			showmessage('album_name_errors');
		}
		
		//隐私
		$_POST['friend'] = intval($_POST['friend']);
		$_POST['target_ids'] = '';
		if($_POST['friend'] == 2) {
			//特定好友
			$uids = array();
			$names = empty($_POST['target_names'])?array():explode(' ', str_replace(lang('tab_space'), ' ', $_POST['target_names']));
			if($names) {
				$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE username IN (".simplode($names).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$uids[] = $value['uid'];
				}
			}
			if(empty($uids)) {
				$_POST['friend'] = 3;//仅自己可见
			} else {
				$_POST['target_ids'] = implode(',', $uids);
			}
		} elseif($_POST['friend'] == 4) {
			//加密
			$_POST['password'] = trim($_POST['password']);
			if($_POST['password'] == '') $_POST['friend'] = 0;//公开
		}
		if($_POST['friend'] !== 2) {
			$_POST['target_ids'] = '';
		}
		if($_POST['friend'] !== 4) {
			$_POST['password'] == '';
		}
		
		updatetable('album', array('albumname'=>$_POST['albumname'], 'friend'=>$_POST['friend'], 'password'=>$_POST['password'], 'target_ids'=>$_POST['target_ids']), array('albumid'=>$albumid));
		showmessage('do_success', "cp.php?ac=album&op=edit&albumid=$albumid");
	}
	
	$album['target_names'] = '';
	
	$friendarr = array($album['friend'] => ' selected');
	
	$passwordstyle = $selectgroupstyle = 'display:none';
	if($album['friend'] == 4) {
		$passwordstyle = '';
	} elseif($album['friend'] == 2) {
		$selectgroupstyle = '';
		if($album['target_ids']) {
			$names = array();
			$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('space')." WHERE uid IN ($album[target_ids])");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$names[] = $value['username'];
			}
			$album['target_names'] = implode(' ', $names);
		}
	}
	
	//好友组
	$groups = getfriendgroup();

} elseif($_GET['op'] == 'delete') {

	//获得相册
	$albums = getalbums($_SGLOBAL['supe_uid']);
	if(empty($albums[$albumid])) {
		showmessage('no_privilege');
	}
	
	if(submitcheck('deletesubmit')) {
		$_POST['moveto'] = intval($_POST['moveto']);
		if($_POST['moveto'] < 0) {
			//彻底删除
			include_once(S_ROOT.'./source/function_delete.php');
			if(!deletealbums(array($albumid))) {
				showmessage('no_privilege');
			}
		} else {
			//转移
			if($_POST['moveto'] && empty($albums[$_POST['moveto']])) {
				$_POST['moveto'] = 0;
			}
			if($_POST['moveto'] > 0) {
				$album = $albums[$_POST['moveto']];
				//更新图片
				updatetable('pic', array('albumid'=>$_POST['moveto']), array('albumid'=>$albumid));
				$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET picnum=picnum+$album[picnum] WHERE albumid='$albumid'");
				//删除相册
				$_SGLOBAL['db']->query("DELETE FROM ".tname('album')." WHERE albumid='$albumid'");
			} else {
				updatetable('pic', array('albumid'=>0), array('albumid'=>$albumid));
				$_SGLOBAL['db']->query("DELETE FROM ".tname('album')." WHERE albumid='$albumid'");
			}
		}
		showmessage('do_success', "space.php?do=album");
	}
} elseif($_GET['op'] == 'editpic') {
	
	include_once(S_ROOT.'./source/function_bbcode.php');
	
	if($albumid > 0) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
		if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('no_privilege');
		}
	}
	
	if(submitcheck('editpicsubmit')) {
		if($_GET['subop'] == 'delete') {
			//删除
			$updates = $deleteids = array();
			foreach ($_POST['title'] as $picid => $value) {
				if(empty($_POST['ids'][$picid])) {
					$title = getstr($value, 150, 1, 1, 1);
					updatetable('pic', array('title'=>$title), array('picid'=>$picid, 'uid'=>$_SGLOBAL['supe_uid']));
				} else {
					$deleteids[$picid] = $picid;
				}
			}
			if($deleteids) {
				include_once(S_ROOT.'./source/function_delete.php');
				deletepics($deleteids);
			}
			
		} elseif($_GET['subop'] == 'update') {
			
			foreach ($_POST['title'] as $picid => $value) {
				$title = getstr($value, 150, 1, 1, 1);
				updatetable('pic', array('title'=>$title), array('picid'=>$picid, 'uid'=>$_SGLOBAL['supe_uid']));
			}

		} elseif($_GET['subop'] == 'move') {
			//先更新title
			foreach ($_POST['title'] as $picid => $value) {
				$title = getstr($value, 150, 1, 1, 1);
				updatetable('pic', array('title'=>$title), array('picid'=>$picid, 'uid'=>$_SGLOBAL['supe_uid']));
			}
			//开始转移
			//检查相册ID
			if($_POST['ids']) {
				$_POST['newalbumid'] = intval($_POST['newalbumid']);
				if($_POST['newalbumid']) {
					$query = $_SGLOBAL['db']->query("SELECT albumid FROM ".tname('album')." WHERE albumid='$_POST[newalbumid]' AND uid='$_SGLOBAL[supe_uid]'");
					if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
						$_POST['newalbumid'] = 0;
					}
				}
				$_SGLOBAL['db']->query("UPDATE ".tname('pic')." SET albumid='$_POST[newalbumid]' WHERE picid IN (".simplode($_POST['ids']).") ");
				$updatecount = $_SGLOBAL['db']->affected_rows();
				if($updatecount) {
					if($albumid>0) {
						$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET picnum=picnum-$updatecount WHERE albumid='$albumid'");
					}
					if($_POST['newalbumid']) {
						$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET picnum=picnum+$updatecount WHERE albumid='$_POST[newalbumid]'");
					}
				}
			}
			
		}
		showmessage('do_success', "cp.php?ac=album&op=editpic&albumid=$albumid&start=$_POST[start]");
	}
	
	$perpage = 10;
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	//检查开始数
	ckstart($start, $perpage);
	
	$picid = empty($_GET['picid'])?0:intval($_GET['picid']);
	$picsql = $picid?"picid='$picid' AND ":'';
	
	if($albumid > 0) {
		$wheresql = "albumid='$albumid'";
	} else {
		$wheresql = "albumid='0' AND uid='$_SGLOBAL[supe_uid]'";
	}
	
	$count = 0;
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE $picsql $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['title'] = html2bbcode($value['title']);//转换
		$value['pic'] = mkpicurl($value);
		$value['bigpic'] = mkpicurl($value, 0);
		$list[] = $value;
		$count++;
	}
	
	$multi = smulti($start, $perpage, $count, "cp.php?ac=album&op=editpic&albumid=$albumid");
	
	//相册列表
	$albumlist = getalbums($_SGLOBAL['supe_uid']);
	
} elseif($_GET['op'] == 'setpic') {
	$picid = empty($_GET['picid'])?0:intval($_GET['picid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid='$picid' AND uid='$_SGLOBAL[supe_uid]'");
	if($pic = $_SGLOBAL['db']->fetch_array($query)) {
		if($pic['albumid']) {
			$pic['picflag'] = $pic['remote']?2:1;
			$pic['filepath'] = $pic['filepath'].($pic['thumb']?'.thumb.jpg':'');
			updatetable('album', array('pic'=>$pic['filepath'], 'picflag'=>$pic['picflag']), array('albumid'=>$pic['albumid']));
		}
	}
	showmessage('do_success');
}

include_once template("cp_album");

?>