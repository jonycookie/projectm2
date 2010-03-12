<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_ajax_delete(){
	global $king;

	$king->access('portal_comment_delete');

	$list=kc_getlist();
	
	$cachepath='portal/comment';
	$king->cache->rd($cachepath);
	
	$king->db->query("delete from %s_comment where cid in ($list)");	

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}

/**
菜单调用
*/
function inc_menu(){
	global $king;
	$left=array(
		''=>array(
			'href'=>'manage.comment.php',
			'ico'=>'p7',
			'title'=>$king->lang->get('system/common/list'),
			),
	);
	if(isset($_GET['cid'])){
		$left['view']=array(
			'href'=>'manage.comment.php?action=view&cid='.$_GET['cid'],
			'ico'=>'p8',
			'title'=>$king->lang->get('system/common/view'),
		);
	}
	return array($left,array());
}

function king_def(){
	global $king,$action;

	$king->access("portal_comment");

	$_sql="select cid,kcontent,username,ndate,isshow from %s_comment order by isshow,cid desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	//准备开始列表
	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"manage.comment.php?action=view&cid='+K[0]+'\">'+\$.kc_icon('q7','".addslashes($king->lang->get('system/common/view'))."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+\$.kc_icon('p3','".$king->lang->get('system/common/del')."')+'</a>'";
	$_js=array(
		"\$.kc_list(K[0],K[1],'manage.comment.php?action=view&cid='+K[0])",
		$manage,
		"'<i>'+isshow('manage.comment.php',K[0],K[4])+'</i>'",//状态
		"'<b>'+K[2]+'</b>'",
		"K[3]",
	);

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.comment.php?pid=PID&rn=RN',$king->db->getRows_number('%s_comment')));
	$status=array();
	$s.="function isshow(url,id,is){var I1,ico;is?ico='n2':ico='n1';";
	$s.="I1='<a id=\"nshow_'+id+'\" class=\"k_ajax\" rel=\"{CMD:\'show\',value:'+ (1-is) +',ID:\'nshow_'+id+'\',list:'+id+',IS:2}\" >'+$.kc_icon(ico)+'</a>';return I1;};";
	
	
	$s.="ll('".$king->lang->get('portal/label/content')."','manage','<i>".$king->lang->get('portal/common/show1')."</i>','<b>".$king->lang->get('portal/label/author')."</b>','<b>".$king->lang->get('portal/label/date')."</b>',1);";

	foreach($res as $rs){//td
		$s.='ll('.$rs['cid'].',\''.addslashes(substr($rs['kcontent'],0,20)).'\',\''.addslashes($rs['username']).'\',\''.kc_formatdate($rs['ndate']).'\','.$rs['isshow'].',0);';
	}

	//结束列表
	$s.=$king->closeList();

	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('portal/title/comment'),$left,$right,$s);
}

function king_view(){
	global $king;
	$king->access('portal_comment');
	
	$cid=kc_get('cid',2);
	$sql="cid,kcontent,username,nip,ndate";

	if(empty($cid)){
		kc_error($king->lang->get('system/error/param'));
	}else{
		if(!$rs=$king->db->getRows_one("select $sql from %s_comment where cid=$cid"))
			kc_error($king->lang->get('system/error/notrecord'));

		foreach ($rs as &$r) {
			$r=htmlspecialchars($r);
		}
		$rs['kcontent']=nl2br($rs['kcontent']);

		$s=$king->openForm($king->lang->get('portal/title/comment'),'','comment_view');
		$s.=$king->htmForm($king->lang->get('portal/label/author'),$rs['username']);
		$s.=$king->htmForm($king->lang->get('portal/label/content'),$rs['kcontent']);
		$s.=$king->htmForm($king->lang->get('portal/label/date'),kc_formatdate($rs['ndate']));
		$but='<input type="button" onclick="javascript:history.back(-1)" value="'.$king->lang->get('system/common/back').'[B]" class="big" accesskey="b"/>';
		$s.=$king->htmForm(null,$but);
		$s.=$king->closeForm('none');
	}


	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('portal/title/comment'),$left,$right,$s);

}
?>