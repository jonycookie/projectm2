<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_mtag.php 7350 2008-05-12 09:36:04Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

if(!@include_once(S_ROOT.'./data/data_profield.php')) {
	include_once(S_ROOT.'./source/function_cache.php');
	profield_cache();
}
include_once(S_ROOT.'./source/function_space.php');

if(submitcheck('mtagsubmit')) {

	//先把以前的给清理掉
	$oldtags = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid, uid FROM ".tname('tagspace')." WHERE uid='$_SGLOBAL[supe_uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$oldtags[$value['tagid']] = $value['tagid'];
	}
	//新的tag
	$mtags = $fieldids = $tagnames = array();
	if(!empty($_POST['mtag'])) {
		foreach ($_POST['mtag'] as $fieldid => $values) {
			//栏目
			if(empty($values)) continue;
			
			$maxnum = 0;
			$thefield = $_SGLOBAL['profield'][$fieldid];
			if($thefield['formtype'] == 'text' && $thefield['inputnum']) {
				$maxnum = $thefield['inputnum'];
			}

			$i = 0;
			$onetagnames = array();
			foreach ($values as $tagname) {
				$tagname = getstr($tagname, 0, 1, 1, 1);
				$strlen = strlen($tagname);
				if($strlen > 0 && $strlen < 40 && (empty($onetagnames) || !in_array($tagname, $onetagnames))) {
					$tagnames[] = $tagname;
					$onetagnames[] = $tagname;
					$mtags[$fieldid][] = $tagname;
					$i++;
					if($maxnum && $i >= $maxnum) break;//超过允许的个数
				}
			}
			if($mtags[$fieldid]) {
				$fieldids[$fieldid] = $fieldid;
			}
		}
	} else {
		//全部清空
		if($oldtags) {
			$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum-1 WHERE tagid IN (".simplode($oldtags).")");
			$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE uid='$_SGLOBAL[supe_uid]'");
		}
	}
	
	$alltags = array();
	if($mtags) {
		//找出tag
		$tags = array();
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagname IN (".simplode($tagnames).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$thename = addslashes($value['tagname']);//加slashes
			$tags[$value['fieldid']][$value['tagid']] = $thename;
		}

		$tagidarr = array();
		foreach ($fieldids as $fieldid) {
			if(empty($mtags[$fieldid])) continue;

			$updatetagids = array();
			foreach ($mtags[$fieldid] as $tagname) {
				if(empty($tags[$fieldid]) || !in_array($tagname, $tags[$fieldid])) {
					//第一次使用
					$setarr = array(
						'tagname' => $tagname,
						'fieldid' => $fieldid,
						'membernum' => 1
					);
					if(empty($_SCONFIG['manualmoderator'])) {
						$setarr['moderator'] = $_SGLOBAL['supe_username'];
					}
					$theid = inserttable('mtag', $setarr, 1);
					$alltags[$theid] = $tagname;
					$tagidarr[] = $theid;
				} else {
					$theid = array_search($tagname, $tags[$fieldid]);//找到对于的key
					$updatetagids[] = $theid;
					$alltags[$theid] = $tagname;
					$tagidarr[] = $theid;
				}
			}
			if($updatetagids) {
				$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum+1 WHERE tagid IN (".simplode($updatetagids).")");
			}
		}
		
		if($oldtags) {
			$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum-1 WHERE tagid IN (".simplode($oldtags).")");
			$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE uid='$_SGLOBAL[supe_uid]'");
		}
		
		//插入关联
		$inserts = array();
		foreach ($tagidarr as $tagid) {
			$inserts[] = "('$tagid','$_SGLOBAL[supe_uid]','$_SGLOBAL[supe_username]')";
		}
		if($inserts) {
			$_SGLOBAL['db']->query("INSERT INTO ".tname('tagspace')." (tagid,uid,username) VALUES ".implode(',', $inserts));
		}
	}

	showmessage('update_on_successful_individuals', $_POST['guidemode']?'space.php?do=home&view=guide&step=3':'space.php?do=mtag', 0);
}

//显示
if($_GET['op'] == 'edit') {

	include_once(S_ROOT.'./source/function_bbcode.php');
	
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	$mtag = $tagid?getmtag($tagid, 1):array();
	if(empty($mtag['allowmanage'])) {
		showmessage('no_privilege');
	}
	
	if(submitcheck('editsubmit')) {
		//判断人员选择
		$setarr = array();
		if($mtag['isadmin']) {
			$muids = '';
			if(!empty($_POST['moderator'])) {
				$muidarr = array($_POST['moderator'][0] => $_POST['moderator'][0]);
				$query = $_SGLOBAL['db']->query("SELECT username FROM ".tname('tagspace')." WHERE tagid='$tagid' AND username IN (".simplode($_POST['moderator']).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					$muidarr[$value['username']] = $value['username'];
				}
				$muids = empty($muidarr)?'':implode("\t", $muidarr);
			}
			$setarr['moderator'] = $muids;
		}
		$setarr['pic'] = getpicurl($_POST['pic'],150);
		$announcement = getstr($_POST['announcement'], 150, 1, 1, 1, 2);
		$setarr['announcement'] = $announcement;

		updatetable('mtag', $setarr, array('tagid'=>$tagid));
		showmessage('do_success', $_POST['refer'], 0);
	}
	
	$moderator = array();
	if($mtag['moderator']) {
		$moderator = explode("\t", $mtag['moderator']);
	}
	
	$mtag['announcement'] = html2bbcode($mtag['announcement']);
	
} elseif($_GET['op'] == 'join') {
	
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	$mtag = $tagid?getmtag($tagid):array();
	
	if(submitcheck('joinsubmit')) {
		if($mtag['ismember']) {
			showmessage('you_are_already_a_member');
		}
		
		//加入选吧
		$field = $_SGLOBAL['profield'][$mtag['fieldid']];
		//自己在当前栏目下面的选吧
		$maxinputnum = 0;
		if($field['formtype'] == 'text') {
			$maxinputnum = intval($field['inputnum']);
		} elseif($field['formtype'] == 'select') {
			$maxinputnum = 1;
		}
		
		if($maxinputnum) {
			$query = $_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('tagspace')." ts, ".tname('mtag')." mtag 
				WHERE ts.tagid=mtag.tagid AND ts.uid='$_SGLOBAL[supe_uid]' AND mtag.fieldid='$mtag[fieldid]'");
			$count = $_SGLOBAL['db']->result($query, 0);
			if($count >= $maxinputnum) {
				showmessage('mtag_max_inputnum', '', 1, array($field['title'], $maxinputnum));
			}
		}
		
		//加入选吧
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum+1 WHERE tagid='$tagid'");
		$_SGLOBAL['db']->query("INSERT INTO ".tname('tagspace')." (tagid,uid,username) VALUES ('$tagid', '$_SGLOBAL[supe_uid]', '$_SGLOBAL[supe_username]')");
		
		//事件通知
		$fs = array();
		$fs['icon'] = 'mtag';
		
		$fs['title_template'] = lang('feed_mtag_join');
		$fs['title_data'] = array(
				'mtag' => "<a href=\"space.php?do=mtag&tagid=$tagid\">$mtag[tagname]</a>",
				'field' => "<a href=\"space.php?do=mtag&id=$mtag[fieldid]\">$mtag[title]</a>"
			);
		$fs['body_template'] = '';
		$fs['body_data'] = array();
		$fs['body_general'] = '';

		if(ckprivacy('mtag', 1)) {
			feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
		}

		showmessage('join_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=mtag&tagid=$tagid", 0);
	}

} elseif($_GET['op'] == 'out') {
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	$mtag = $tagid?getmtag($tagid):array();
	if(submitcheck('outsubmit')) {
		if(!$mtag['ismember']) {
			showmessage('you_are_not_a_member_of');
		}
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum-1 WHERE tagid='$tagid'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid='$tagid' AND uid='$_SGLOBAL[supe_uid]'");
		showmessage('do_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=mtag&tagid=$tagid", 0);
	}
	
} elseif($_GET['op'] == 'searchmtag') {
	
	$resultarr = array();
	$_GET['mtagname'] = shtmlspecialchars(strip_tags(trim($_GET['mtagname'])));
	$_GET['fieldid'] = empty($_GET['fieldid']) ? 0 : intval($_GET['fieldid']);
	$_GET['like'] = empty($_GET['like']) ? 0 : 1;
	$_GET['inputnum'] = empty($_GET['inputnum']) ? 0 : intval($_GET['inputnum']);
	$sqlstr = empty($_GET['like']) ? 'tagname=\''.$_GET[mtagname].'\'' : 'tagname like \'%'.$_GET[mtagname].'%\' ORDER BY membernum DESC';
	$query = $_SGLOBAL['db']->query('SELECT tagid, tagname, membernum, fieldid FROM '.tname('mtag').' WHERE fieldid=\''.$_GET['fieldid'].'\' AND '.$sqlstr);
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$resultarr[] = $value;
	}
	if(empty($resultarr) && empty($_GET['like'])) {
		$resultarr[] = array('tagid' => 0, 'tagname' => stripslashes($_GET['mtagname']), 'membernum' => 0, 'fieldid' => $_GET['fieldid']);
	}
	
	showmessage(getelementstring($resultarr, $_GET['like'], $_GET['inputnum']));
	
} else {
	//取热门的
	$hotlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." FORCE INDEX(membernum) ORDER BY membernum DESC LIMIT 0,100");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$hotlist[$value['fieldid']][] = $value;
	}
	
	//用户tag
	$mtags = array();
	$query = $_SGLOBAL['db']->query("SELECT tm.tagid, t.tagname, t.fieldid, t.membernum FROM ".tname('tagspace')." tm LEFT JOIN ".tname('mtag')." t ON t.tagid=tm.tagid 
									WHERE tm.uid='$_SGLOBAL[supe_uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$mtags[$value['fieldid']]['tagid'][] = $value['tagid'];
		$mtags[$value['fieldid']]['tagname'][] = $value['tagname'];
		$mtags[$value['fieldid']]['membernum'][] = $value['membernum'];
	}
	
	//选吧栏目表单
	$fields = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profield')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$fieldid = $value['fieldid'];
		$value['formhtml'] = '';
	
		if($value['formtype'] == 'text') {
			//input框长度
			$value['formhtml'] = array();
			$value['note'] = empty($value['note'])?'':$value['note'];
			if(!empty($mtags[$value['fieldid']])) {
				foreach($mtags[$value['fieldid']]['tagname'] as $tmpkey => $tmpvalue) {
					$resultarr = array();
					$resultarr[] = array('tagid' => $mtags[$value['fieldid']]['tagid'][$tmpkey], 'tagname' => $tmpvalue, 'membernum' => $mtags[$value['fieldid']]['membernum'][$tmpkey], 'fieldid' => $value['fieldid']);
					$value['formhtml'][] = getelementstring($resultarr);
				}
			}
		} else {
			if($value['choice']) {
				if($value['formtype'] == 'multi') {
					$optionarr = explode("\n", $value['choice']);
					$value['formhtml'] .= "<table><tr>";
					$i = 0;
					foreach ($optionarr as $ov) {
						$ov = trim($ov);
						if($ov) {
							$checkstr = !empty($mtags[$fieldid]['tagname']) && in_array($ov, $mtags[$fieldid]['tagname'])?' checked':'';
							$value['formhtml'] .= "<td><input type=\"checkbox\" name=\"mtag[$fieldid][]\" value=\"$ov\"$checkstr>$ov</td>";
							if($i%5==4) {
								$value['formhtml'] .= "</tr><tr>";
							}
							$i++;
						}
					}
					$value['formhtml'] .= "</tr></table>";
				} else {
					$value['formhtml'] .= "<select name=\"mtag[$fieldid][]\">";
					$value['formhtml'] .= "<option value=\"\">---</option>";
					$optionarr = explode("\n", $value['choice']);
					foreach ($optionarr as $ov) {
						$ov = trim($ov);
						if($ov) {
							$selectstr = !empty($mtags[$fieldid]['tagname']) && in_array($ov, $mtags[$fieldid]['tagname'])?' selected':'';
							$value['formhtml'] .= "<option value=\"$ov\"$selectstr>$ov</option>";
						}
					}
					$value['formhtml'] .= "</select>";
				}
			}
		}
		
		$fields[$value['fieldid']] = $value;
	}
}

include template("cp_mtag");

function getelementstring($arr, $like='', $inputnum=0) {
	$return = '';
	if(empty($like)) {
		if(empty($arr[0]['tagid'])) {
			$return .= $arr[0]['tagname'];
		} else {
			$return .= '<a href="space.php?do=mtag&tagid='.$arr[0]['tagid'].'" target="_blank">'.$arr[0]['tagname'].'</a><span class="gray"> ('.$arr[0]['membernum'].lang('person').')</span>';
		}
		$return .= '&nbsp;<a href="javascript:;" onclick="mtagDel(this);return false;" class="float_del">'.lang('delete').'</a><input type="hidden" name="mtag['.$arr[0]['fieldid'].'][]" value="'.$arr[0]['tagname'].'" />';
	} else {
		foreach($arr as $tmpvalue) {
			$return .= '<li><a href="javascript:;" onclick="mtagChoose(\''.$tmpvalue['fieldid'].'\', \''.saddslashes($tmpvalue['tagname']).'\');return false;">'.$tmpvalue['tagname'].'&nbsp;<span class="gray">('.$tmpvalue['membernum'].lang('person').')</span></a></li>';
		}
	}
	return $return;
}
?>