<?php
defined('IN_EXT') or die('Forbidden');

$action = GetGP('action');
if ($action=='add' || $action=='edit') {
	InitGP(array('title','link','view','color','b','ii','u','alt','target','pos'));
	empty($title) && Showmsg('empty_title');
	empty($link) && Showmsg('empty_link');
	!is_numeric($view) && !empty($view) && adminmsg('nav_notnum');
	$title=Char_cv($title);
	$style=$color.'|'.$b.'|'.$ii.'|'.$u;
	$link=Char_cv($link);
	$alt=Char_cv($alt);
	if($action=='add'){
		$db->query("INSERT INTO cms_nav (title,style,link,alt,pos,target,view) VALUES('$title','$style','$link','$alt','$pos','$target','$view')");
	}elseif ($action=='edit') {
		$nid = (int)GetGP('nid');
		!$nid && adminmsg('nav_nonid');
		$db->update("UPDATE cms_nav set
		 	title='$title',
		 	style='$style',
		 	link='$link',
		 	alt='$alt',
		 	pos='$pos',
			target='$target',
		 	view='$view'
		  WHERE nid='$nid'");
	}
	require_once(E_P.'include/cache.class.php');
	navCache::cache();
	$jumpto = $pos=='foot' ? 'viewfoot' : 'viewhead';
	adminmsg("operate_success","$basename&job=$jumpto");
}elseif ($action=='del'){
	$nid = (int)GetGP('nid');
	!$nid && adminmsg('nav_nonid');
	$db->update("DELETE from cms_nav WHERE nid='$nid'");
	require_once(E_P.'include/cache.class.php');
	navCache::cache();
	adminmsg('operate_success');
}elseif ($action=='editview'){
	$view = GetGP('view');
	foreach ($view as $key=>$val){
		!is_numeric($val) && Showmsg('nav_notnum');
		$db->update("UPDATE cms_nav SET view='$val' WHERE nid='$key'");
	}
	require_once(E_P.'include/cache.class.php');
	navCache::cache();
	adminmsg('operate_success');
}elseif ($action == 'navcache'){
	require_once(E_P.'include/cache.class.php');
	navCache::cache();
	adminmsg('operate_success');
}
require_once(R_P.'require/color.php');
$job = GetGP('job');
if(!$job || $job=='add'){
	$actionvalue = 'add';
	$head_check = 'checked';
	$tar_check_N = 'checked';
	foreach ($colors as $c){
		$color_select .= "<option value=\"$c\" style=\"background-color:$c;color:$c\">$c</option>";
	}
}elseif ($job=='edit'){
	$nid = (int)GetGP('nid');
	!$nid && adminmsg('nav_nonid');
	$actionvalue = 'edit';
	@extract($db->get_one("select * from cms_nav where nid='$nid'"));
	$style_array = explode('|',$style);
	$style_array[1] && $b_check = 'checked';
	$style_array[2] && $i_check = 'checked';
	$style_array[3] && $u_check = 'checked';
	$pos=='foot' ? $foot_check = 'checked' : $head_check='checked';
	ifcheck($target,'tar_check');
	foreach ($colors as $c){
		$ifselect = $c==$style_array[0] ? 'selected' : '';
		$color_select .= "<option value=\"$c\" style=\"background-color:$c;color:$c\" $ifselect>$c</option>";
	}
}elseif ($job=='viewfoot' || $job=='viewhead'){
	if ($job=='viewfoot') {
		$pos='foot';
	}elseif ($job=='viewhead'){
		$pos='head';
	}
	$rs=$db->query("select * from cms_nav where pos='$pos' order by view");
	$nav=array();
	while ($navdb=$db->fetch_array($rs)) {
		$style_array=explode('|',$navdb['style']);
		$style_array[1] && $navdb['title']='<b>'.$navdb['title'].'</b>';
		$style_array[2] && $navdb['title']='<i>'.$navdb['title'].'</i>';
		$style_array[3] && $navdb['title']='<u>'.$navdb['title'].'</u>';
		$style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
		$nav[]=$navdb;
	}
}
require PrintExt('header');
require PrintExt('admin');
adminbottom();
?>