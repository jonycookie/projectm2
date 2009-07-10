<?php
/**
 * This software is the proprietary information of PHPWind.com.
 * $Id: index.php 556 2008-04-07 05:57:47Z vcteam $
 * @Copyright (c) 2003-08 PHPWind.com Corporation.
 */
defined('IN_EXT') or die('Forbidden');
$job = GetGP('job');
if(!$job){
	$id = (int)GetGP('id');
	$mark = Char_cv(GetGP('mark'));
	$sql = " stime<='$timestamp' AND (stime=etime or etime>='$timestamp') ";
	if($id){
		$sql .= " AND id='$id' ";
	}elseif($mark){
		$sql .= " AND mark='$mark' ";
	}else{
		echo "document.write('Error ID');";
		exit;
	}

	$rt = $db->get_one("SELECT * FROM cms_polls WHERE $sql ORDER BY stime DESC");
	if(!$rt){
		throwError('ext_idnotexist');
	}
	extract($rt);
	$etime = get_date($etime,'Y-m-j');
	$stime = get_date($stime,'Y-m-j');
	$options = unserialize($options);
	require PrintExt('index');
	exit;
}elseif($job == 'vote'){
	if(GetCookie('poll')){
		throwError('ext_wait');
	}
	$id = GetGP('id');
	$poll = GetGP('poll');
	if(!$id || !$poll){
		throwError('ext_idnotexist');
	}
	$rt = $db->get_one("SELECT * FROM cms_polls WHERE id='$id' AND stime<'$timestamp' AND (stime=etime or etime>'$timestamp')");
	if($rt){
		$options = unserialize($rt['options']);
		if($rt['ismulti']){
			foreach($poll as $key=>$val){
				if($val){
					$key--;
					$options[$key]['stats']++;
				}
			}
		}else{
			$poll--;
			isset($options[$poll]) && $options[$poll]['stats']++;
		}
		$options = serialize($options);
		$stats = $rt['stats']+1;
		$db->update("UPDATE `cms_polls` SET `options`='$options',`stats`='$stats' WHERE id='$id'");
		Cookie('poll',1,$timestamp + 30);
	}
	Extmsg('ext_successful',$_SERVER['HTTP_REFERER']);
}elseif($job == 'view'){
	$id = (int)GetGP('id');
	if(!$id){
		echo "alert('Error ID');";
		exit;
	}
	$rt = $db->get_one("SELECT * FROM cms_polls WHERE id='$id'");
	extract($rt);
	$etime = get_date($etime,'Y-m-j');
	$stime = get_date($stime,'Y-m-j');
	$options = unserialize($options);
	foreach($options as $k=>$v){
		$stats_sum += $v[stats];
	}
	require_once(R_P.'require/class_cate.php');
	require_once(R_P.'require/class_cms.php');
	require_once(R_P.'require/class_extend.php');
	$cms	= new Cms();
	$cate	= new Cate();
	$extend = new Extend();
	$metakeyword = $metadescrip = $sys['title'].','.$ext_config[$E_name]['name'];
	require TemplateExt('view');
	exit;
}
?>