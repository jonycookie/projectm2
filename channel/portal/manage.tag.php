<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

 //删除
//king_ajax_delete
function king_ajax_delete(){
	global $king;
	$king->access('portal_tag_delete');

	$_list=kc_getlist();
	$_array=explode(',',$_list);

	if(!$_res=$king->db->getRows("select kid,ktag from %s_tag where kid in ({$_list});"))
		kc_error($king->lang->get('system/error/not'));

	foreach($_res as $rs){
		//写log
		$king->log(6,'Tag:'.$rs['ktag']);
	}

	$king->db->query("delete from %s_tag where kid in ({$_list});");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

}
 //上移下移
//king_ajax_updown
function king_ajax_updown(){
	global $king;
	$king->access('portal_tag_edt');

	$kid=kc_get('kid',2,1);

	$king->db->updown('%s_tag',$kid);
}

function king_ajax_commend(){
	global $king;
	$king->access('portal_tag_edt');
	$kid=kc_get('kid',2,1);
	$is=kc_get('is',2,1);
	$ico= $is ? 'n1':'n2';

	$king->db->update('%s_tag',array('iscommend'=>$is),"kid=$kid");

	kc_ajax('',kc_icon($ico),'',"$('#commend_{$kid}').attr('rel','{CMD:\'commend\',ID:\'commend_{$kid}\',is:".(1-$is).",kid:{$kid},IS:2}')");

//{CMD:\commend\',is:'+is+',ID:\'commend_'+id+'\',kid:'+id+',IS:2}
}

/* ------>>> KingCMS for PHP <<<--------------------- */

 //列表页
//king_inc_list
function king_inc_list(){
	global $king;

	$left=array(
		''=>array(
			'href'=>'manage.tag.php',
			'ico'=>'i5',
			'title'=>$king->lang->get('system/common/list'),
		),
		'edt'=>array(
			'href'=>'manage.tag.php?action=edt',
			'ico'=>'j5',
			'title'=>$king->lang->get('system/common/add'),
		),
	);

	$right = !$king->acc('portal_list') ? array() : array(
		array(
			'href'=>'manage.php',
			'title'=>$king->lang->get('portal/title/list'),
			'ico'=>'a1',
		),
	);

	return array($left,$right);
} //!king_list


/* ------>>> KingCMS for PHP <<<--------------------- */

 //默认执行页面
//king_def
function king_def(){
	global $king;

	$king->access('portal_tag');
	
	$_sql='select kid,ktag,kcolor,nsize,isbold,iscommend from %s_tag order by norder desc';

	//准备开始列表
	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$brow_link=($king->config('rewritetag') ? "../tag" : "../index.php/tag").$king->config('rewriteline');
	$manage ="'<a target=\"_blank\" href=\"{$brow_link}'+(K[6])+'".$king->config('rewriteend')."\">'+$.kc_icon('h7','".$king->lang->get('system/common/brow')."')+'</a>'";
	$manage.="+'<a href=\"manage.tag.php?action=edt&kid='+K[0]+'\">'+$.kc_icon('k5','".$king->lang->get('system/common/edit')."')+'</a>'";
	$manage.="+'<a class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+$.kc_icon('l5','".$king->lang->get('system/common/del')."')+'</a>'";
	$manage.="+$.kc_updown(K[0])";

	$_js=array(
		"$.kc_list(K[0],K[1],'manage.tag.php?action=edt&kid='+K[0])",
		$manage,
		"kstyle(K[1],K[2],K[3],K[4])",
		"'<i>'+isset(K[0],K[5])+'</i>'",
	);
	
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.tag.php?pid=PID&rn=RN',$king->db->getRows_number('%s_tag')));
	$s.="function kstyle(l1,l2,l3,l4){var I1;l4 ? I1='<font style=\"font-size:'+l3+'px;color:'+l2+';font-weight:bold;\">'+l1+'</font>' : I1='<font style=\"font-size:'+l3+'px;color:'+l2+';\">'+l1+'</font>';return I1;};";
	$s.="function isset(id,is){var I1,ico;is?ico='n1':ico='n2';";
	$s.="I1='<a id=\"commend_'+id+'\" class=\"k_ajax\" rel=\"{CMD:\'commend\',is:'+(1-is)+',ID:\'commend_'+id+'\',kid:'+id+',IS:2}\" >'+$.kc_icon(ico)+'</a>';return I1;};";

	if(!$res=$king->db->getRows($_sql,1))
		$res=array();
	$s.='ll(\''.$king->lang->get('portal/label/ktag').'\',\'manage\',\''.$king->lang->get('portal/list/effect').'\',\'<i>'.$king->lang->get('portal/label/attrib/iscommend').'</i>\',1);';
	foreach($res as $rs){
		$s.='ll('.$rs['kid'].',\''.$rs['ktag'].'\',\''.$rs['kcolor'].'\','.$rs['nsize'].','.$rs['isbold'].','.$rs['iscommend'].',\''.urlencode($rs['ktag']).'\',0);';
	}
	//结束列表
	$s.=$king->closeList();

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/tag'),$left,$right,$s);

} //!king_def
 //添加&编辑
//king_edt
function king_edt(){

	global $king;
	
	$king->access("portal_tag_edt");

	$kid=kc_get('kid');

	$_sql='ktag,kimage,kkeywords,kdescription,kcolor,nsize,isbold,iscommend,ktemplate1,ktemplate2';

	if($GLOBALS['ismethod']||$kid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['kcolor']='#000000';
			$data['nsize']=12;

			$tpath=$king->config('templatepath');
			$tdefa=$king->config('templatedefault');

			$ktemplate1=$tpath.'/'.$tdefa;
			$data['ktemplate1']=is_file(ROOT.$ktemplate1) ? $ktemplate1 : '';

			$ktemplate2=$tpath.'/inside/tag/'.$tdefa;
			$data['ktemplate2']=is_file(ROOT.$ktemplate2) ? $ktemplate2 : '';
		}
	}else{	//编辑数据，从数据库读出
		$data=$king->db->getRows_one('select '.$_sql.' from %s_tag where kid='.$kid.' limit 1;');
	}
	$fields=explode(',',$_sql);
	$data=kc_data($fields,$data);

	$s=$king->openForm('manage.tag.php?action=edt');

	//ktag
	$_array=array(
		array('ktag',0,1,100),
	);
	$kid
		? array_push($_array,array('ktag',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select kid from %s_tag where ktag='".$king->db->escape($data['ktag'])."' and kid<>$kid;")))
		: array_push($_array,array('ktag',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select kid from %s_tag where ktag='".$king->db->escape($data['ktag'])."';")));

	$s.=$king->htmForm($king->lang->get('portal/label/ktag').' (1-100)','<input class="k_in w200" type="text" name="ktag" value="'.htmlspecialchars($data['ktag']).'" maxlength="100" />',$_array);
	//kkeywords
	$_array=array(
		array('kkeywords',0,0,100),
	);
	$s.=$king->htmForm($king->lang->get('system/common/keywords').' (0-100)','<input class="k_in w400" type="text" name="kkeywords" value="'.htmlspecialchars($data['kkeywords']).'" maxlength="100" />',$_array);
	//kdescription
	$_array=array(
		array('kdescription',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('system/common/description').' (0-255)','<textarea rows="4" cols="100" class="k_in w400" name="kdescription" maxlength="255" >'.htmlspecialchars($data['kdescription']).'</textarea>',$_array);
	//kimage
	$_array=array(
		array('kimage',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('system/common/image').' (0-255)','<input class="k_in w400" type="text" id="kimage" name="kimage" value="'.htmlspecialchars($data['kimage']).'" maxlength="255" />'.kc_f_brow('kimage',$king->config('uppath').'/image',0),$_array);
	//iscommend
	$data['iscommend']==1?$checked='checked="checked"':$checked='';
	$str='<span><input type="checkbox" name="iscommend" id="iscommend" value="1" '.$checked.'/><label for="iscommend">'.$king->lang->get('portal/label/attrib/iscommend').'</label></span>';
	$s.=$king->htmForm($king->lang->get('system/common/attrib'),$str);
	//kcolor,nsize,isbold
	$_array=array(
		array('kcolor',0,7,7),
		array('nsize',0,1,2),
		array('nsize',2),
		array('kcolor',13),
	);

	$str='<span><input type="text" class="k_in w60" name="nsize" value="'.$data['nsize'].'"  maxlength="2"/><label> px</label>';
	$data['isbold']==1?$checked='checked="checked"':$checked='';
	$str.=' <input type="checkbox" name="isbold" id="isbold" value="1" '.$checked.'/><label for="isbold">'.$king->lang->get('portal/label/bold').'</label>';
	$str.=' <label for="kcolor">'.$king->lang->get('portal/label/kcolor').':</label><input class="k_in w50" type="text" id="kcolor" name="kcolor" value="'.htmlspecialchars($data['kcolor']).'" maxlength="7"'.(kc_validate($data['kcolor'],13)?' style="background:'.$data['kcolor'].'"':'').' />'.kc_f_color('kcolor',$data['kcolor']).'</span>';
	$s.=$king->htmForm($king->lang->get('system/common/style'),$str,$_array);

	//ktemplate1
	$_array=array(
		array('ktemplate1',0,5,255),
		array('ktemplate1',15),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/templatetag1').' (5-255)','<input class="k_in w400" type="text" name="ktemplate1" id="ktemplate1" value="'.htmlspecialchars($data['ktemplate1']).'" maxlength="255" />'.kc_f_brow('ktemplate1',$king->config('templatepath'),2).kc_help('portal/help/template',455,455),$_array);
	//ktemplate2
	$_array=array(
		array('ktemplate2',0,5,255),
		array('ktemplate2',15),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/templatetag2').' (5-255)','<input class="k_in w400" type="text" name="ktemplate2" id="ktemplate2" value="'.htmlspecialchars($data['ktemplate2']).'" maxlength="255" />'.kc_f_brow('ktemplate2',$king->config('templatepath').'/inside/tag',2),$_array);

	$s.=kc_htm_hidden(array('kid'=>$kid));

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){
		$array=array();
		$array_sql=explode(',',$_sql);

		$data['isbold']==1?$data['isbold']=1:$data['isbold']=0;
		$data['iscommend']?$data['iscommend']=1:$data['iscommend']=0;

		foreach($array_sql as $val){
			$array+=array($val=>$data[$val]);
		}

/**
		检查kkeywords，如果没有，则自动补充其值
		如果有，则更新列表
*/
		if(!$array['kkeywords']){
			$array+=array('kkeywords'=>$king->portal->getkey($array['ktag']));
		}else{
			$array['kkeywords']=$king->portal->getkey($array['ktag'],$array['kkeywords']);
		}

		if($kid){//update
			$king->db->update('%s_tag',$array,'kid='.$kid);
			$nlog=7;
		}else{//insert
			$array+=array(
				'norder'=>$king->db->neworder('%s_tag')
			);
			$king->db->insert('%s_tag',$array);;
			$nlog=5;
		}

		$md5path=preg_replace('/(\w{2})(\w+)/',"\$1/\$2",md5($data['ktag']));
		$xmlpath=$king->config('xmlpath','portal').'/portal/tag/'.$md5path.'.xml';
		kc_f_delete($xmlpath);

		//写log
		$king->log($nlog,'Tag:'.$data['ktag']);

		kc_goto($king->lang->get('system/goto/is'),'manage.tag.php?action=edt','manage.tag.php');
	}

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/tag'.($kid?'edt':'add')),$left,$right,$s);

}








?>