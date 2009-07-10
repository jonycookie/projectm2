<?php
defined('IN_EXT') or die('Forbidden');
require_once(D_P.'data/cache/cate.php');
InitGP(array('type','id',/*'stime','etime',*/'num','order','digest','pre','length','author','hits','comnum','postdate','channel'),'G');
if(!$type || !$id || !$num || !$order){
	echo "document.write('parameter error!');";
	exit;
}
$prefix  = array('<li>','◇','·','○','●','- ','□-');
$pre = $prefix[$pre] ? $prefix[$pre] : $prefix['0'];
$num = is_numeric($num) ? intval($num) : 10;
$length = is_numeric($length) ? intval($length) : 30;
if(!in_array($order,array('postdate','comnum','hits'))){
	echo "document.write('parameter error!');";
	exit;
}
$digest = is_numeric($digest) ? intval($digest) : -1;
if($digest>=0 && $digest<=3){
	$digest = " AND digest='$digest' ";
}else{
	$digest = '';
}
/**时间段调用未开启
$stime = PwStrtoTime($stime);
$etime = PwStrtoTime($etime);
*/
$id = explode(',',$id);
$ids = array();
foreach($id as $val){
	if(is_numeric($val)){
		$ids[] = $val;
	}
}
$id = implode(',',$ids);
if(in_array($type,array('tid','cid','mid'))){
	$sql = " AND $type IN ($id) ";
}else{
	echo "document.write('parameter error!');";
	exit;
}
$rs = $db->query("SELECT tid,cid,mid,title,photo,postdate,linkurl,url,digest,hits,comnum,publisher,titlestyle FROM cms_contentindex WHERE ifpub='1' $digest $sql ORDER BY $order DESC LIMIT $num");
$newlist = '';
while($rt = $db->fetch_array($rs)){
	$rt['postdate'] = get_date($rt['postdate'],'m-d H:i');
	$rt['title'] = substrs($rt['title'],$length);
	$article = "$pre <a href='$very[url]/view.php?tid=$rt[tid]&cid=$rt[cid]' target='_blank'>$rt[title]</a>";
	if($author){
		$article .= "($rt[publisher])";
	}
	if($hits){
		$article .= "($rt[hits])";
	}
	if($comnum){
		$article .= "($rt[comnum])";
	}
	if($postdate){
		$article .= "($rt[postdate])";
	}
	if($channel){
		$article .= "({$catedb[$rt[cid]][cname]})";
	}
	$newlist .= "document.write(\"$article<br>\");\n";
}
echo $newlist;
exit;
?>