<?php require_once '../global.php';
//已读未读
function king_ajax_read(){
	global $king;
	$king->access('feedback_edt');
	$kid=kc_get('list',2,1);
	$value=kc_get('value',2,1);
	$king->db->query("update %s_feedback set nread=$value where kid=$kid;");
	$value ? $ico='n2':$ico='n1';
	kc_ajax('',kc_icon($ico,($value?$king->lang->get('feedback/list/unread'):$king->lang->get('feedback/list/read'))),0,"$('#nread_{$kid}').attr('rel','{CMD:\'read\',value:".(1-$value).",ID:\'nread_{$kid}\',list:\'$kid\',IS:2}')");
}

//删除留言
function king_ajax_delete(){
	global $king;
	$king->access('feedback_delete');
	$list=kc_getlist();
	$king->db->query("delete from %s_feedback where kid in ($list)");
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}

//排序
function king_ajax_updown(){
	global $king;
	$king->access('feedback_updown');

	$kid=kc_get('kid',2,1);
	$king->db->updown('%s_feedback',$kid);
}

/**
菜单调用
*/
function inc_menu(){
	global $king;
	$left=array(
		''=>array(
			'href'=>'manage.php',
			'ico'=>'p7',
			'title'=>$king->lang->get('system/common/list'),
			),
	);
	if(isset($_GET['kid'])){
		$left['view']=array(
			'href'=>'manage.php?action=view&kid='.$_GET['kid'],
			'ico'=>'p8',
			'title'=>$king->lang->get('system/common/view'),
		);
	}
	return array($left,array());
}

function king_def(){
	global $king;
	$king->access('feedback');

	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"manage.php?action=view&kid='+K[0]+'\">'+\$.kc_icon('q7','".addslashes($king->lang->get('system/common/view'))."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+\$.kc_icon('q8','".addslashes($king->lang->get('system/common/del'))."')+'</a>'";
	$manage.="+\$.kc_updown(K[0])";

	$_js=array(
		"\$.kc_list(K[0],K[1],'manage.php?action=view&kid='+K[0])",
		$manage,
		"'<i>'+isread('manage.php',K[0],K[2])+'</i>'",//状态
		"K[3]",
		"K[4]",
		"K[5]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?pid=PID&rn=RN',$king->db->getRows_number('%s_feedback','kid!=0')));

	$_sql="select kid,ktitle,nread,kname,kemail,ndate from %s_feedback order by norder desc,kid desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$s.="function isread(url,id,is){var I1,ico;is?ico='n2':ico='n1';";
	$s.="I1='<a id=\"nread_'+id+'\" class=\"k_ajax\" rel=\"{CMD:\'read\',value:'+ (1-is) +',ID:\'nread_'+id+'\',list:'+id+',IS:2}\" >'+$.kc_icon(ico,(is?'".$king->lang->get('feedback/list/unread')."':'".$king->lang->get('feedback/list/read')."'))+'</a>';return I1;};";
	$s.="ll('".$king->lang->get('feedback/list/title')."','manage','<i>".$king->lang->get('feedback/list/status')."<i>','".$king->lang->get('feedback/list/name')."','".$king->lang->get('feedback/list/email')."','".$king->lang->get('feedback/list/date')."',1);";

	foreach($res as $rs){
		$s.="ll({$rs['kid']},'".addslashes($rs['ktitle'])."',".$rs['nread'].",'".addslashes($rs['kname'])."','".addslashes($rs['kemail'])."','".kc_formatdate($rs['ndate'])."',0);";
	}

	$s.=$king->closeList();

	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('feedback/title/center'),$left,$right,$s);
}

function king_view(){
	global $king;
	$king->access('feedback');
	
	$kid=kc_get('kid',2);
	$sql="kid,ktitle,kname,kemail,kqq,kphone,kcontent,ndate";

	if(!$res=$king->db->getRows("select $sql from %s_feedback where kid=$kid"))
		$res=array();

	if(empty($kid)){
		kc_error($king->lang->get('system/error/param'));
	}else{
		if(!$rs=$king->db->getRows_one("select $sql from %s_feedback where kid=$kid order by norder asc"))
			kc_error($king->lang->get('system/error/notrecord'));

		foreach ($rs as &$r) {
			$r=htmlspecialchars($r);
		}
		$rs['kcontent']=nl2br($rs['kcontent']);

		$s=$king->openForm($king->lang->get('feedback/name'),'','feedback_edt');
		$s.=$king->htmForm($king->lang->get('feedback/label/title'),$rs['ktitle']);
		$s.=$king->htmForm($king->lang->get('feedback/label/name'),$rs['kname']);
		$s.=$king->htmForm($king->lang->get('feedback/label/email'),'<a href="mailto:'.$rs['kemail'].'" title="'.$king->lang->get('feedback/list/sendmail').$rs['kname'].'">'.$rs['kemail'].'</a>');
		$s.=$king->htmForm($king->lang->get('feedback/label/qq'),$rs['kqq']);
		$s.=$king->htmForm($king->lang->get('feedback/label/phone'),$rs['kphone']);
		$s.=$king->htmForm($king->lang->get('feedback/label/content'),$rs['kcontent']);
		$s.=$king->htmForm($king->lang->get('feedback/label/date'),kc_formatdate($rs['ndate']));
		$but='<input type="button" onclick="javascript:history.back(-1)" value="'.$king->lang->get('system/common/back').'[B]" class="big" accesskey="b"/>';
		$s.=$king->htmForm(null,$but);
		$s.=$king->closeForm('none');
	}


	//设置为已读状态
	$king->db->update('%s_feedback',array('nread'=>1),'kid='.$kid);

	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('feedback/title/center'),$left,$right,$s);

}


?>