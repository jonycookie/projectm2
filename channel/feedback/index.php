<?php require_once '../global.php';
//添加留言
function king_ajax_add(){
	global $king;
	
	$fbtime=kc_cookie("fbtime");//获得上次操作时间

	$ktitle=kc_post('ktitle');
	$kname=kc_post('kname');
	$kemail=kc_post('kemail');
	$kphone=kc_post('kphone');
	$kqq=kc_post('kqq');
	$kcontent=kc_post('kcontent');

	//check ktitle
	if(!isset($ktitle{1}) || strlen($ktitle)>50){
		kc_error($king->lang->get('feedback/error/name',0));
	}
	//check kname
	if(!isset($kname{1}) || strlen($kname)>30){
		kc_error($king->lang->get('feedback/error/name',1));
	}
	//check kemail
	if(!kc_validate($kemail,5)){
		kc_error($king->lang->get('feedback/error/name',2));
	}

	//check kcontent
	if(!isset($kcontent{9})){
		kc_error($king->lang->get('feedback/error/name',3));
	}

	if($fbtime>time()-3600){
		kc_ajax($king->lang->get('system/common/tip'),$king->lang->get('feedback/error/name',5),0);
	}else{
		//记录本次发布时间
		setcookie("fbtime",time(),time()+3600,'/');	

		$array=array(
			'ktitle'=>$ktitle,
			'kname'=>$kname,
			'kemail'=>$kemail,
			'kphone'=>$kphone,
			'kqq'=>$kqq,
			'kcontent'=>$kcontent,
			'norder'=>$king->db->neworder('%s_feedback'),
			'ndate' =>time(),
		);

		$king->db->insert('%s_feedback',$array);

		kc_ajax('OK','<p class="k_ok">'.$king->lang->get('feedback/ok/add').'</p>'
		,"<a href=\"index.php\">".$king->lang->get('system/common/enter')."</a>");//添加成功后返回的地址
	}
}


/**
	添加/编辑碎片
*/
function king_def(){
	
	global $king;
	$sql="ktitle,kname,kemail,kqq,kphone,kcontent";
	$s=$king->openForm($king->lang->get('feedback/name'),'','feedback_add');
	$s.=$king->htmForm($king->lang->get('feedback/label/title'),kc_htm_input('ktitle','',50,400).'*');
	$s.=$king->htmForm($king->lang->get('feedback/label/name'),kc_htm_input('kname','',30,400).'*');
	$s.=$king->htmForm($king->lang->get('feedback/label/email'),kc_htm_input('kemail','',100,400).'*');
	$s.=$king->htmForm($king->lang->get('feedback/label/qq'),kc_htm_input('kqq','',30,400));
	$s.=$king->htmForm($king->lang->get('feedback/label/phone'),kc_htm_input('kphone','',20,400));
	$s.=$king->htmForm($king->lang->get('feedback/label/content'),kc_htm_textarea('kcontent').'*');
	$s.=$king->htmForm(null,kc_htm_button($king->lang->get('system/common/add'),"\$.kc_ajax({CMD:'add',FORM:'feedback_add'});",1));
	$s.=$king->closeForm('none');

	$tmp=new KC_Template_class($king->config('templatepath').'/default.htm',$king->config('templatepath').'/inside/feedback/default.htm');
	$tmp->assign('content',$s);
	$tmp->assign('title',$king->lang->get('feedback/name'));
	$tmp->assign('type','add');

	echo $tmp->output();

}
?>