<?php
!function_exists('adminmsg') && exit('Forbidden');

InitGP(array('action','page'));
$db_perpage = 20; //每页显示数目
$j_url=EncodeUrl("$basename&action=del");
if(file_exists($logfile)){
	$logfiledata=readlog($logfile);
} else{
	$logfiledata=array();
}
$logfiledata = array_reverse($logfiledata);
$count = count($logfiledata);
$numofpage = ceil($count/$db_perpage);
(!is_numeric($page) || $page < 1) && $page=1;
$page>$numofpage && $page=$numofpage;

if($action=='search'){
	$keyword = GetGP('keyword');
	$keyword = Char_cv($keyword);
	if(!$keyword){
		adminmsg('noenough_condition');
	}
	$num=0;
	$start=($page-1)*$db_perpage;
	foreach($logfiledata as $value){
		if(strpos($value,$keyword)!==false){
			if($num >= $start && $num < $start+$db_perpage){
				$detail=explode("|",$value);
				$winddate=get_date($detail[5],'m-d H:i');
				$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
				$detail[6]=htmlspecialchars($detail[6]);
				$adlogfor.="
<tr class=tr3 align='center'>
<td><img src=\"images/admin/log.gif\" align=\"absmiddle\" /></td>
<td>$detail[1]<br /></td>
<td>$detail[2]<br /></td>
<td>$detail[3]<br /></td>
<td>$detail[4]<br /></td>
<td>$winddate<br /></td>
<td>$detail[6]<br /></td>
</tr>";
			}
			$num++;
		}
	}
	$numofpage=ceil($num/$db_perpage);
	$pages=numofpage($num,$page,$numofpage,"$basename&action=search&keyword=".rawurlencode($keyword)."&");
} elseif($action=='del'){
	if ($admin_name == $manager){
		if($count>100){
			$output = '';
			$output = array_slice($logfiledata,0,100);
			$output = array_reverse($output);
			$output="<?php die;?>\r\n".implode("",$output);
			writeover($logfile,$output);
			adminmsg('log_del');
		}else{
			adminmsg('log_min');
		}
	} else {
		adminmsg('log_aminonly');
	}
}else{
	$pages=numofpage($count,$page,$numofpage,"$basename&");
	$start=($page-1)*$db_perpage;
	$logfiledata = array_slice($logfiledata,$start,$db_perpage);
	foreach ($logfiledata as $value){
		$detail=explode("|",$value);
		$winddate=get_date($detail[5],'m-d H:i');
		$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
		$detail[6]=htmlspecialchars($detail[6]);
		$adlogfor.="
<tr class=tr3>
<td><img src=\"images/admin/log.gif\" align=\"absmiddle\" /></td>
<td>$detail[1]<br /></td>
<td>$detail[2]<br /></td>
<td>$detail[3]<br /></td>
<td>$detail[4]<br /></td>
<td>$winddate<br /></td>
<td>$detail[6]<br /></td>
</tr>";
	}
}
require PrintEot('header');
require PrintEot('set_log');
adminbottom();
?>