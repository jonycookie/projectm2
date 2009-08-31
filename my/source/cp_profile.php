<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_profile.php 7241 2008-04-30 07:20:51Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$space['theme'] = $space['css'] = '';

if(submitcheck('profilesubmit')) {

	if(!@include_once(S_ROOT.'./data/data_profilefield.php')) {
		include_once(S_ROOT.'./source/function_cache.php');
		profilefield_cache();
	}
	$profilefields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];
	
	//提交检查
	$setarr = array(
		'sex' => intval($_POST['sex']),
		'email' => isemail($_POST['email'])?$_POST['email']:'',
		'qq' => getstr($_POST['qq'], 20, 1, 1),
		'msn' => getstr($_POST['msn'], 80, 1, 1),
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
		if($value['required'] && empty($setarr['field_'.$field])) {
			showmessage('field_required', '', 1, array($value['title']));
		}
	}
	updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	showmessage('update_on_successful_individuals', 'cp.php?ac=profile');
}

//性别
$sexarr = array($space['sex']=>' checked');

//生日:年
$birthyeayhtml = '';
$nowy = sgmdate('Y');
for ($i=1; $i<80; $i++) {
	$they = $nowy - $i;
	$selectstr = $they == $space['birthyear']?' selected':'';
	$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
}
//生日:月
$birthmonthhtml = '';
for ($i=1; $i<13; $i++) {
	$selectstr = $i == $space['birthmonth']?' selected':'';
	$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//生日:日
$birthdayhtml = '';
for ($i=1; $i<32; $i++) {
	$selectstr = $i == $space['birthday']?' selected':'';
	$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//血型
$bloodhtml = '';
foreach (array('A','B','O','AB') as $value) {
	$selectstr = $value == $space['blood']?' selected':'';
	$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
}
//婚姻
$marryarr = array($space['marry'] => ' selected');

//头像
include_once S_ROOT.'./uc_client/client.php';
$uc_avatarflash = uc_avatar($_SGLOBAL['supe_uid']);

//栏目表单
$profilefields = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profilefield')." ORDER BY displayorder");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$fieldid = $value['fieldid'];
	$value['formhtml'] = '';

	if($value['formtype'] == 'text') {
		//input框长度
		$value['note'] = empty($value['note'])?'':$value['note'];
		$value['formhtml'] = "<input type=\"text\" name=\"field_$fieldid\" value=\"".$space["field_$fieldid"]."\" class=\"t_input\">";
	} else {
		$value['formhtml'] .= "<select name=\"field_$fieldid\">";
		if(empty($value['required'])) {
			$value['formhtml'] .= "<option value=\"\">---</option>";
		}
		$optionarr = explode("\n", $value['choice']);
		foreach ($optionarr as $ov) {
			$ov = trim($ov);
			if($ov) {
				$selectstr = $space["field_$fieldid"]==$ov?' selected':'';
				$value['formhtml'] .= "<option value=\"$ov\"$selectstr>$ov</option>";
			}
		}
		$value['formhtml'] .= "</select>";
	}
	
	$profilefields[$value['fieldid']] = $value;
}

include template("cp_profile");

?>