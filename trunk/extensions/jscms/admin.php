<?php
defined('IN_EXT') or die('Forbidden');
$action = GetGP('action');
if($action=='show'){
	require_once(R_P.'require/class_extend.php');
	InitGP(array('type','id',/*'stime','etime',*/'num','order','digest','pre','length','end'),'P');
	/**时间段调用未开启
	$stime = PwStrtoTime($stime);
	$etime = PwStrtoTime($etime);
	*/
	$pre = intval($pre);
	$id = explode(',',$id);
	$ids = array();
	foreach($id as $val){
		if(is_numeric($val)){
			$ids[] = $val;
		}
	}
	$id = implode(',',$ids);
	if(!in_array($type,array('tid','cid','mid'))){
		Showmsg('ext_jscmsIdError');
	}
	$num = is_numeric($num) ? intval($num) : 10;
	$length = is_numeric($length) ? intval($length) : 30;
	$digest = intval($digest);
	foreach($end as $key=>$val){
		$tmp .= "$key=1&";
		$$key = 'checked';
	}
	${'type_'.$type} = 'checked';
	${'order_'.$order} = 'selected';
	${'pre_'.$pre} = 'checked';
	${'digest_'.$digest} = 'selected';
	$script = "<script language=\"javascript\" src=\"".Extend::URL($E_name)."&type=$type&id=$id&num=$num&order=$order&digest=$digest&pre=$pre&length=$length&$tmp\"></script>";
	$jscode = htmlspecialchars($script);
}elseif($action == 'view'){
	$jscode = GetGP('jscode','P');
	$script = stripslashes($jscode);
	$jscode = htmlspecialchars(stripslashes($jscode));
}
require PrintExt('header');
require PrintExt('admin');
adminbottom();
?>