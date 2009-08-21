<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_space.php 7185 2008-04-25 08:10:49Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');
@include_once(S_ROOT.'./data/data_profilefield.php');
$fields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];
	
//初始化
$gets = $multi = $list = array();

if(!empty($_GET['searchmode'])) {
	//需要登录
	checklogin();
	
	//判断是否搜索太快
	$waittime = interval_check('search');
	if($waittime > 0) {
		showmessage('search_short_interval');
	}
	$gets['username'] =  empty($_GET['username'])?'':stripsearchkey($_GET['username']);
	$gets['spacename'] =  empty($_GET['spacename'])?'':stripsearchkey($_GET['spacename']);
	$gets['fieldid'] = empty($_GET['fieldid'])?'':intval($_GET['fieldid']);
	if($gets['fieldid'] && !empty($_SGLOBAL['profield'][$gets['fieldid']])) {
		$gets['fieldname'] = empty($_GET['fieldname'])?'':stripsearchkey($_GET['fieldname']);
	} else {
		$gets['fieldid'] = $gets['fieldname'] = '';
	}
	$gets['tagid'] = empty($_GET['tagid'])?'':intval($_GET['tagid']);
	$gets['blood'] = empty($_GET['blood'])?'':stripsearchkey($_GET['blood']);
	$gets['birthprovince'] = empty($_GET['birthprovince'])?'':stripsearchkey($_GET['birthprovince']);
	$gets['birthcity'] = empty($_GET['birthcity'])?'':stripsearchkey($_GET['birthcity']);
	$gets['resideprovince'] = empty($_GET['resideprovince'])?'':stripsearchkey($_GET['resideprovince']);
	$gets['residecity'] = empty($_GET['residecity'])?'':stripsearchkey($_GET['residecity']);
	$gets['birthyear'] = empty($_GET['birthyear'])?'':intval($_GET['birthyear']);
	$gets['birthmonth'] = empty($_GET['birthmonth'])?'':intval($_GET['birthmonth']);
	$gets['birthday'] = empty($_GET['birthday'])?'':intval($_GET['birthday']);
	$gets['sex'] = empty($_GET['sex'])?'':intval($_GET['sex']);
	$gets['marry'] = empty($_GET['marry'])?'':intval($_GET['marry']);
	$gets['qq'] = empty($_GET['qq'])?'':stripsearchkey($_GET['qq']);
	$gets['msn'] = empty($_GET['msn'])?'':stripsearchkey($_GET['msn']);
	
	//搜索积分/不扣积分
	//cksearchcredit($ac);
	
	//开始搜索
	$wherearr = array();
	foreach (array('sex', 'birthyear', 'birthmonth', 'birthday', 'marry', 'blood', 'birthprovince', 'birthcity', 'resideprovince', 'residecity', 'qq', 'msn') as $value) {
		if($gets[$value]) {
			$wherearr[] = "spacefield.$value='$gets[$value]'";
		}
	}
	//自定义
	foreach ($fields as $fkey => $fvalue) {
		if($fvalue['allowsearch']) {
			$gets['field_'.$fkey] = empty($_GET['field_'.$fkey])?'':stripsearchkey($_GET['field_'.$fkey]);
			if($gets['field_'.$fkey]) {
				$wherearr[] = "spacefield.field_$fkey = '".$gets['field_'.$fkey]."'";
			}
		}
	}
	
	$next = true;
	if($gets['tagid']) {
		$tagid = $gets['tagid'];
	} elseif($gets['fieldid'] && $gets['fieldname']) {
		$tagid = getcount('mtag', array('tagname'=>$gets['fieldname'], 'fieldid'=>$gets['fieldid']), 'tagid');
		if(empty($tagid)) {
			$next = false;
		}
	}
	if($next) {
		$selectsql = $fromsql = '';
		if($tagid) {
			$selectsql = "tagspace.uid, tagspace.username";
			$wherearr[] = "tagspace.uid = spacefield.uid";
			$wherearr[] = "tagspace.tagid = '$tagid'";
			$fromsql .= ','.tname('tagspace').' tagspace';
		}
		if($gets['username'] || $gets['spacename']) {
			$selectsql = "space.uid, space.username, space.spacename";
			$wherearr[] = "space.uid = spacefield.uid";
			if($gets['username']) {
				$wherearr[] = "space.username LIKE '%$gets[username]%'";
			}
			if($gets['spacename']) {
				$wherearr[] = "space.spacename LIKE '%$gets[spacename]%'";
			}
			$fromsql .= ','.tname('space').' space';
		}
		if(empty($selectsql)) {
			$selectsql = "space.uid, space.username, space.spacename";
			$wherearr[] = "space.uid = spacefield.uid";
			$fromsql .= ','.tname('space').' space';
		}
		if(empty($wherearr)) {
			showmessage('set_the_correct_search_content');
		}
		
		$wheresql = implode(' AND ', $wherearr);
		$sql = "SELECT $selectsql FROM ".tname('spacefield')." spacefield $fromsql WHERE $wheresql";
		
		$fuids = array();
		$query = $_SGLOBAL['db']->query($sql.' LIMIT 0, 100');//最多100条
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$fuids[] = $value['uid'];
			$list[] = $value;
		}
		
		//在线状态
		$ols = array();
		if($fuids) {
			$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$ols[$value['uid']] = $value['lastactivity'];
			}
		}
		
		//更新最后操作时间
		if($_SGLOBAL['supe_uid']) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET lastsearch='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
		}
	}
	
} else {

	//分页
	$perpage = 20;
	$start = empty($_GET['start'])?0:intval($_GET['start']);
	if(empty($_SCONFIG['networkpage'])) $start = 0;
	
	//检查开始数
	ckstart($start, $perpage);

	//普通浏览模式
	$fuids = array();
	$count = 0;
	$query = $_SGLOBAL['db']->query("SELECT main.uid, main.username, main.spacename, field.* FROM ".tname('space')." main FORCE INDEX (updatetime)
		LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid 
		ORDER BY main.updatetime DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['p'] = rawurlencode($value['resideprovince']);
		$value['c'] = rawurlencode($value['residecity']);
		$fuids[] = $value['uid'];
		$list[] = $value;
		$count++;
	}
	
	//在线状态
	$ols = array();
	if($fuids) {
		$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$ols[$value['uid']] = $value['lastactivity'];
		}
	}
	
	//分页
	$multi = empty($_SCONFIG['networkpage'])?array('html'=>'networkpage'):smulti($start, $perpage, $count, $theurl);
}

//页面显示
//性别
$sexarr = array($gets['sex']=>' selected');

//生日:年
$birthyeayhtml = '';
$nowy = sgmdate('Y');
for ($i=1; $i<80; $i++) {
	$they = $nowy - $i;
	$selectstr = $they == $gets['birthyear']?' selected':'';
	$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
}
//生日:月
$birthmonthhtml = '';
for ($i=1; $i<13; $i++) {
	$selectstr = $i == $gets['birthmonth']?' selected':'';
	$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//生日:日
$birthdayhtml = '';
for ($i=1; $i<32; $i++) {
	$selectstr = $i == $gets['birthday']?' selected':'';
	$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//血型
$bloodhtml = '';
foreach (array('A','B','O','AB') as $value) {
	$selectstr = $value == $gets['blood']?' selected':'';
	$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
}
//婚姻
$marryarr = array($gets['marry']=>' selected');

//选吧
$fieldids = array($gets['fieldid']=>' selected');

//自定义
foreach ($fields as $fkey => $fvalue) {
	if($fvalue['allowsearch']) {
		if($fvalue['formtype'] == 'text') {
			$fvalue['html'] = '<input type="text" name="field_'.$fkey.'" value="'.$gets["field_$fkey"].'" class="t_input">';
		} else {
			$fvalue['html'] = "<select name=\"field_$fkey\"><option value=\"\">---</option>";
			$optionarr = explode("\n", $fvalue['choice']);
			foreach ($optionarr as $ov) {
				$ov = trim($ov);
				if($ov) {
					$selectstr = $gets["field_$fkey"]==$ov?' selected':'';
					$fvalue['html'] .= "<option value=\"$ov\"$selectstr>$ov</option>";
				}
			}
			$fvalue['html'] .= "</select>";
		}
		$fields[$fkey] = $fvalue;
	} else {
		unset($fields[$fkey]);
	}
}

//标题
$_TPL['titles'] = array(lang('network_space_user'), lang('casual_look'));

?>