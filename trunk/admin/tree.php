<?php
!defined('IN_ADMIN') && die('Forbidden');
require_once(D_P.'data/cache/cate.php');
if($admin_name!=$manager && $admindb['privcate']){
	foreach($admindb['privcate'] as $val){
		$fathercids = getAllFatherCid($val);
	}
}
$children = array();
foreach ($catedb as $key => $cate){
	$children[$key]=array();
	foreach ($catedb as $c){
		if($admin_name==$manager|| !$admindb['privcate']){
			$c['up']==$key && $children[$key][]=$c['cid'];
		}else{
			$c['up']==$key && in_array($c['cid'],$fathercids) && $children[$key][]=$c['cid'];
		}
	}
}

$action = GetGP('action');


if(!$action){
	require PrintEot('tree');
	adminbottom(0);
}elseif ($action=='showXML'){
	$xmlmsg = "<ul class=\"jqueryFileTree\">\n";
	if($admin_name==$manager|| !$admindb['privcate']){
		$rs = $db->query("SELECT * FROM cms_category WHERE up='$cid'");
	}else{
		$sqlfathercids = implode(',',$fathercids);
		$rs = $db->query("SELECT * FROM cms_category WHERE up='$cid' AND cid IN($sqlfathercids)");
	}

	while ($child = $db->fetch_array($rs)) {
		$child['cname'] = htmlspecialchars($child['cname']);
		if(count($children[$child['cid']])>0){
			$xmlmsg.="<li class=\"directory collapsed\"><a href=\"#\" rel=\"$child[cid]\">$child[cname]</a></li>";
		}else{
			$xmlmsg.="<li class=\"file\"><a href=\"$admin_file?adminjob=content&amp;action=view&amp;cid=$child[cid]\" target=\"mainFrame\">$child[cname]</a></li>";
		}
	}
	$xmlmsg.="\t</ul>";
	print $xmlmsg;
}


function getFatherCid($cid){
	global $catedb;
	if(!$catedb[$cid]['up']){
		return $cid;
	}else{
		return getFatherCid($catedb[$cid]['up']);
	}
}

function getAllFatherCid($cid){
	global $catedb,$fathercids;
	if(!$fathercids){
		$fathercids = array();
	}
	!in_array($cid,$fathercids) && $fathercids[] = $cid;
	if(!$catedb[$cid]['up']){
		return $fathercids;
	}else{
		return getAllFatherCid($catedb[$cid]['up']);
	}
}
?>