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
	if($admin_name==$manager|| !$admindb['privcate']){
		$root = $db->query("SELECT * FROM cms_category WHERE depth=1 ORDER BY taxis DESC");
	}else{
		$pricate = array();
		foreach($admindb['privcate'] as $val){
			$fathercid = getFatherCid($val);
			!in_array($fathercid,$pricate) && $pricate[] = $fathercid;
		}
		$pricate = implode(',',$pricate);
		$root = $db->query("SELECT * FROM cms_category WHERE cid IN($pricate) ORDER BY taxis DESC");
	}
	require PrintEot('header');
	require PrintEot('tree');
	adminbottom(0);
}elseif ($action=='showXML'){
	if($cid){
		$xmlmsg = "<?xml version=\"1.0\" encoding=\"$very[lang]\"?>\n\t<tree>\n";
		if($admin_name==$manager|| !$admindb['privcate']){
			$rs = $db->query("SELECT * FROM cms_category WHERE up='$cid'");
		}else{
			$sqlfathercids = implode(',',$fathercids);
			$rs = $db->query("SELECT * FROM cms_category WHERE up='$cid' AND cid IN($sqlfathercids)");
		}
		
		while ($child = $db->fetch_array($rs)) {
			$child['cname'] = htmlspecialchars($child['cname']);
			if($admin_name==$manager|| !$admindb['privcate'] ||($admindb['privcate'] && in_array($child['cid'],$admindb['privcate']))){
				$xmlmsg.="\t\t<tree text=\"$child[cname]\" action=\"javascript:goMain('$admin_file?adminjob=content&amp;action=view&amp;cid=$child[cid]');\" cId=\"$child[cid]\"  ";
			}else{
				$xmlmsg.="\t\t<tree text=\"$child[cname]\" action=\"javascript:void(0);\" cId=\"nopriv\"  ";
			}
			if(count($children[$child['cid']])>0){
				$xmlmsg.="src=\"$admin_file?adminjob=tree&amp;action=showXML&amp;cid=$child[cid]&amp;timestamp=$timestamp\"";
			}
			$xmlmsg.="/>\n";
		}
		$xmlmsg.="\t</tree>";
		header("Content-type: application/xml");
		print $xmlmsg;
	}
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