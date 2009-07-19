<?php
!defined('IN_ADMIN') && die('Forbidden');
$step = GetGP('step');
if(!$step){
	include_once(D_P.'data/cache/config.php');
	ifcheck($sys['open'],'open');
	ifcheck($sys['rewrite'],'rewrite');
	ifcheck($sys['gzip'],'gzip');
	ifcheck($sys['debug'],'debug');
	ifcheck($sys['hidehelp'],'hidehelp');
	ifcheck($sys['autopage'],'autopage');
	ifcheck($sys['rewrite'],'rewrite');
	ifcheck($sys['aggrebbs'],'aggrebbs');
	ifcheck($sys['aggreblog'],'aggreblog');
	ifcheck($sys['ckcomment'],'ckcomment');
	ifcheck($sys['ckadmin'],'ckadmin');
	ifcheck($sys['skipgif'],'skipgif');
	ifcheck($sys['ckwater'],'ckwater');
	ifcheck($sys['wapifopen'],'wapifopen');
	ifcheck($sys['wapcharset'],'wapcharset');
	$num=0;
	$forumcheck="<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
	$query=$db->query("SELECT cid,cname FROM cms_category WHERE type>0 AND mid=1");
	$wapcids = explode(',',$sys['wapcids']);
	while($rt=$db->fetch_array($query)){
		$num++;
		$htm_tr = $num % 2 == 0 ? '</tr><tr>' : '';
		$checked = in_array($rt[cid],$wapcids)!=false ? 'checked' : '';
		$forumcheck.="<td><input type='checkbox' name='wapcids[]' value='$rt[cid]' $checked>$rt[cname]</td>$htm_tr";
	}
	unset($wapcids);
	$forumcheck.="</tr></table>";
	$sys['waterpos'] = $sys['waterpos']?$sys['waterpos']:0;
	//$sys['waterimg'] = $sys['waterimg']?$sys['waterimg']:"mark.gif";
	${'waterpos_ck_'.$sys['waterpos']}='checked';
	if($sys['attachmkdir']){
		$attachdir_ck[$sys['attachmkdir']]="checked";
	}
	if($sys['htmmkdir']){
		$htmmkdir[$sys['htmmkdir']]="checked";
	}
	$discate = explode(',',$sys['discate']);
	require_once(R_P.'require/class_cate.php');
	$cate = new Cate();
	$cate_select=$cate->tree();
	foreach ($discate as $cid){
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);
		$cate_moreset .= "<tr class=tr1><td>".$catedb[$cid]['cname']."</td><td>  <input type=\"checkbox\" name=\"includechild[]\" value=\"".$cid."\"";
		in_array($cid,$includechild) && $cate_moreset.=" CHECKED";
		$cate_moreset .= "> &nbsp;&nbsp;".$lang['includechild']."</td><td>&nbsp;&nbsp;  ";
		if($displaynum[$cid]<=0) $displaynum[$cid]=10; //默认显示数量
		$cate_moreset .= " <input type=\"text\" name=\"displaynum[$cid]\" class=\"input\" size=3 value=\"".$displaynum[$cid]."\"/>  ".$lang['displaynum']."</td></tr>   ";
	}
	if($sys['aggrebbs']){
		require_once GetLang('extension');
		$bbsmembers = array(
		'todaypost'	=>	$lang['bbs_tpost'],
		'monthpost'	=>	$lang['bbs_mpost'],
		'postnum'	=>	$lang['bbs_postnum'],
		'money'		=>	$lang['bbs_money'],
		'rvrc'		=>	$lang['bbs_rvrc'],
		'credit'	=>	$lang['bbs_credit'],
		);
		$bbsforums = array(
		'tpost'		=>	$lang['bbs_tpost'],
		'article'	=>	$lang['bbs_article'],
		'topic'		=>	$lang['bbs_topic'],
		);
		foreach ($bbsforums as $key=>$f){
			$bbs_forum_select.="<option value=\"$key\"";
			($key==$sys['bbs_forumsort']) && $bbs_forum_select.=" SELECTED";
			$bbs_forum_select.=">$f</option>";
		}
		foreach ($bbsmembers as $key=>$m){
			$bbs_member_select.="<option value=\"$key\"";
			($key==$sys['bbs_membersort']) && $bbs_member_select.=" SELECTED";
			$bbs_member_select.=">$m</option>";
		}
	}
	if($sys['aggreblog']){
		require_once GetLang('extension');
		$blogmembers = array(
		'blogs'		=>	$lang['blog_num'],
		'msgs'		=>	$lang['blog_msgs'],
	//	'friendview'=>	$lang['blog_foot'],
		'todaypost'	=>	$lang['blog_tpost'],
		);
		$blogtags = array(
		'blognum'	=>	$lang['blog_blognum'],
		'photonum'	=>	$lang['blog_photonum'],
		'bookmarknum'=>	$lang['blog_bookmarknum'],
		'musicnum'	=>	$lang['blog_musicnum'],
		'allnum'	=>	$lang['blog_allnum'],
		);
		foreach ($blogtags as $key=>$f){
			$blog_tags_select.="<option value=\"$key\"";
			($key==$sys['blog_tagsort']) && $blog_tags_select.=" SELECTED";
			$blog_tags_select.=">$f</option>";
		}
		foreach ($blogmembers as $key=>$m){
			$blog_member_select.="<option value=\"$key\"";
			($key==$sys['blog_membersort']) && $blog_member_select.=" SELECTED";
			$blog_member_select.=">$m</option>";
		}
	}
	//$sys['loginip = str_replace(",","\n",$sys['loginip);
	if($sys['lang']=='gbk'){
		$gbkselect='selected';
	}elseif ($sys['lang']=='utf-8'){
		$utf8select='selected';
	}elseif($sys['lang']=='big5'){
		$big5select='selected';
	}
	$defaulttmp		= array();
	$templatedir	= R_P."template";
	$d = opendir($templatedir);
	while ($filename = readdir($d)) {
		if($filename=='.' || $filename=='..') continue;
		if(is_dir($templatedir.'/'.$filename) && !in_array($filename,array('admin','user','wap'))){
			$defaulttmp[$filename] = "template/$filename";
		}
	}
}elseif ($step==2){
	$config = GetGP('config','P');
	$discate = GetGP('discate','P');
	InitGP(array('wapcids'),'P');
	$config['wapcids']=implode(',',$wapcids);
	!is_dir(D_P.$config['htmdir']) && Showmsg('config_htmdir');
	!is_writable(D_P.$config['htmdir']) && Showmsg('config_htmdirwrite');
	!is_dir(D_P.$config['attachdir']) && Showmsg('config_attachdir');
	!is_writable(D_P.$config['attachdir']) && Showmsg('config_attachdirwrite');
	empty($config['title']) && Showmsg('config_notitle');
	empty($config['datefm']) && Showmsg('config_notimedf');
	$config['searchrange'] && $config['searchrange'] = (int)$config['searchrange'];
	if($config['ckwater']) {
		//print_r(D_P."images/water".$config['waterimg']);exit;
		$config['waterpct']		= (int)$config['watewaterpctrfont'];
		$config['jpgquality']	= (int)$config['jpgquality'];
		$config['waterfont']	= (int)$config['waterfont'];
		empty($config['waterimg']) && empty($config['watertext']) && Showmsg('config_nowaterinfo');
		empty($config['watertext'])&& !file_exists(D_P."images/water/".$config['waterimg']) && Showmsg('config_nowaterimg');
		empty($config['watercolor']) && $config['watercolor'] = "#FF0000";
		empty($config['waterpct'])   && $config['waterpct']   = "75";
		empty($config['jpgquality']) && $config['jpgquality'] = "75";
		empty($config['waterfont'])  && $config['waterfont']  = "10";
		$config['watertextlib'] && !file_exists(D_P."require/encode/".$config['watertextlib']) && Showmsg('config_nowaterinfo');
	}
	$d = array();
	foreach ($discate as $c){
		$c = intval($c);
		$d[] = $c;
	}
	$config['discate']=implode(',',$d);
	$config['cvtime']=(int)$config['cvtime'];
	$config['perpage']=(int)$config['perpage'];
	foreach ($config as $key=>$value){
		$key = 'db_'.$key;
		$value = addslashes($value);
		$db->pw_update(
		"SELECT * FROM cms_config WHERE db_name='$key'",
		"UPDATE cms_config SET db_value='$value' WHERE db_name='$key'",
		"INSERT INTO cms_config (db_name,db_value) VALUES('$key','$value')"
		);
	}
	require_once(R_P.'require/class_cache.php');
	$cache = new Cache();
	$cache->config();
	adminmsg('set_saveok');
}
require PrintEot('set_config');
adminbottom();
?>