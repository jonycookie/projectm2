<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_profile.php 9732 2008-11-14 01:51:43Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//����
$rncredit = creditrule('pay', 'realname');

if(submitcheck('profilesubmit')) {

	if(!@include_once(S_ROOT.'./data/data_profilefield.php')) {
		include_once(S_ROOT.'./source/function_cache.php');
		profilefield_cache();
	}
	$profilefields = empty($_SGLOBAL['profilefield'])?array():$_SGLOBAL['profilefield'];

	//�ύ���
	$setarr = array(
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
	
	//�Ա�
	$_POST['sex'] = intval($_POST['sex']);
	if($_POST['sex'] && empty($space['sex'])) $setarr['sex'] = $_POST['sex'];

	foreach ($profilefields as $field => $value) {
		if($value['formtype'] == 'select') $value['maxsize'] = 255;
		$setarr['field_'.$field] = getstr($_POST['field_'.$field], $value['maxsize'], 1, 1);
		if($value['required'] && empty($setarr['field_'.$field])) {
			showmessage('field_required', '', 1, array($value['title']));
		}
	}
	updatetable('spacefield', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));

	//����ʵ��
	$setarr = array(
		'name' => getstr($_POST['name'], 10, 1, 1, 1),
		'namestatus' => $_SCONFIG['namecheck']?0:1
	);
	if(checkperm('managename')) {
		 $setarr['namestatus'] = 1;
	}

	if(strlen($setarr['name']) < 4) {//����С��4���ַ�
		$setarr['namestatus'] = 0;
		$setarr['name'] = '';
	}
	if($setarr['name'] != $space['name'] || $setarr['namestatus']) {
		//�ۼ�����
		if($_SCONFIG['realname'] && $space['namestatus'] && !checkperm('managename')) {
			if($space['name'] && $setarr['name'] != $space['name'] && $rncredit) {
				if($space['credit'] >= $rncredit) {
					$setarr['credit'] = $space['credit'] - $rncredit;
				} else {
					showmessage('integral_inadequate', '', 1, array($space['credit'], $rncredit));
				}
			}
		}
		updatetable('space', $setarr, array('uid'=>$_SGLOBAL['supe_uid']));
	}

	//�����¼
	if($_SCONFIG['my_status']) {
		inserttable('userlog', array('uid'=>$_SGLOBAL['supe_uid'], 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
	}
	
	//����feed
	feed_add('profile', cplang('feed_profile_update'));

	if($_POST['guidemode']) {
		showmessage('update_on_successful_individuals', 'space.php?do=home&view=guide&step=3', 0);
	} else {
		showmessage('update_on_successful_individuals', 'cp.php?ac=profile');
	}
}

//�Ա�
$sexarr = array($space['sex']=>' checked');

//����:��
$birthyeayhtml = '';
$nowy = sgmdate('Y');
for ($i=0; $i<100; $i++) {
	$they = $nowy - $i;
	$selectstr = $they == $space['birthyear']?' selected':'';
	$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
}
//����:��
$birthmonthhtml = '';
for ($i=1; $i<13; $i++) {
	$selectstr = $i == $space['birthmonth']?' selected':'';
	$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//����:��
$birthdayhtml = '';
for ($i=1; $i<32; $i++) {
	$selectstr = $i == $space['birthday']?' selected':'';
	$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
}
//Ѫ��
$bloodhtml = '';
foreach (array('A','B','O','AB') as $value) {
	$selectstr = $value == $space['blood']?' selected':'';
	$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
}
//����
$marryarr = array($space['marry'] => ' selected');

//��Ŀ��
$profilefields = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('profilefield')." ORDER BY displayorder");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$fieldid = $value['fieldid'];
	$value['formhtml'] = '';

	if($value['formtype'] == 'text') {
		//input�򳤶�
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

if(empty($_SCONFIG['namechange'])) {
	$_GET['namechange'] = 0;//�������޸�
}

include template("cp_profile");

?>