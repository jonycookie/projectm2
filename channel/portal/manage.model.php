<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

 //删除字段
//king_ajax_delete
function king_ajax_delete(){
	global $king;
	$king->access('portal_model_delete');

	$_list=kc_getlist();
	$_array=explode(',',$_list);

	if(!$_res=$king->db->getRows("select modelid,modeltable,modelname from %s_model where modelid in ($_list);"))
		$_res=array();

	foreach($_res as $rs){
		$modeltable=$rs['modeltable'];
		$modelid=$rs['modelid'];

		//判断要删除的模型是否已经被应用
		$count=$king->db->getRows_one("select count(*) from %s_list where modelid=$modelid");
		if($count[0]>0){
			
			$king->cache->del('portal/model/model'.$modelid);
			$king->cache->del('portal/model');

			kc_error($king->lang->get('portal/error/istmodel'));
		}

		$king->db->query("DROP TABLE %s__$modeltable;",1);
		$king->db->query("delete from %s_model where modelid=$modelid;");

		$king->db->query("delete from %s_field where modelid=$modelid;");

		$king->cache->del('portal/model/model'.$modelid);

		//写log
		$king->log(6,'Model:'.$rs['modelname']);
	}

	$king->cache->del('portal/model');
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

} //!king_ajax_delete
 //上移下移
//king_ajax_updown
function king_ajax_updown(){
	global $king;
	$king->access('portal_model_updown');

	$modelid=kc_get('kid',22,1);

	$king->db->updown('%s_model',$modelid,null,1,'modelid','norder');
} //!king_ajax_updown
 //拷贝模型
//king_ajax_dbcopy
function king_ajax_dbcopy(){
	global $king;
	$king->access('portal_model_dbcopy');
	$modelid=kc_get('modelid');
	$modeltable=kc_get('modeltable',1);
	$_array_varchar=array(1,4,5,6,7,8);

	$fields=array('modelname','modeltable');
	$data=kc_data($fields);

	//模型名称
	$_array=array(
		array('modelname',0,2,50),
	);
	$s =$king->htmForm($king->lang->get('portal/label/newmodelname').' (2-50)','<input class="k_in w200" type="text" id="modelname" name="modelname" value="'.htmlspecialchars($data['modelname']).'" maxlength="50" />',$_array);

	$_array=array(
		array('modeltable',0,1,50),
		array('modeltable',1),
		array('modeltable',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select modelid from %s_model where modeltable='".$king->db->escape(kc_post('modeltable'))."';")),
		array('modeltable',18,null,$king->portal->holdmodel),//保留的模型名称

	);
	$s.=$king->htmForm($king->lang->get('portal/label/newtable').' (1-50)','<input class="k_in w200" type="text" id="modeltable" name="modeltable" value="'.htmlspecialchars($data['modeltable']).'" maxlength="50" />',$_array);

	$s.=kc_htm_hidden(array('modelid'=>$modelid));
	$but=kc_htm_a($king->lang->get("system/common/save"),"{CMD:'dbcopy',IS:1}");

	if($GLOBALS['ischeck']){

		$king->portal->unModelCode($king->portal->enModelCode($modelid),$data['modelname'],$data['modeltable']);

		$king->cache->del('portal/model');

		//写log
		$king->log(5,'Model:'.$data['modelname']);
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/goto/ok')."</p>",1);
	}

	kc_ajax($king->lang->get('portal/title/dbcopy'),$s,$but,'',300,120+$GLOBALS['check_num']*15);
}

function king_ajax_dbout(){
	global $king;
	$king->access('portal_model_dbout');
	$modelid=kc_get('modelid',22,1);

	$code=$king->portal->enModelCode($modelid);

	$s=$king->htmForm($king->lang->get('system/common/code'),'<textarea id="copycode" class="k_in w400" style="height:175px;font-size:10px;line-height:10px;">'.$code.'</textarea>');


	$but=kc_htm_button($king->lang->get('portal/common/copycode'),'window.clipboardData.setData(\'Text\',$(\'#copycode\').val());');

	kc_ajax($king->lang->get('portal/list/dbout'),$s,$but,null,435,235);

}

function king_ajax_incode(){
	global $king;
	$king->access('portal_model_dbin');

	/*
	if($GLOBALS['ismethod']){//POST过程
		$data=$_POST;
	}
	*/
	$fields=array('modelname','modeltable','incode');
	$data=kc_data($fields);

	//模型名称
	$_array=array(
		array('modelname',0,2,50),
	);
	$s =$king->htmForm($king->lang->get('portal/label/newmodelname').' (2-50)','<input class="k_in w200" type="text" id="modelname" name="modelname" value="'.htmlspecialchars($data['modelname']).'" maxlength="50" />',$_array);
	//数据表名称
	$_array=array(
		array('modeltable',0,1,50),
		array('modeltable',1),
		array('modeltable',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select modelid from %s_model where modeltable='".$king->db->escape($data['modeltable'])."';")),
		array('modeltable',18,null,$king->portal->holdmodel),//保留的模型名称

	);
	$s.=$king->htmForm($king->lang->get('portal/label/newtable').' (1-50)','<input class="k_in w200" type="text" id="modeltable" name="modeltable" value="'.htmlspecialchars($data['modeltable']).'" maxlength="50" />',$_array);

	//数据表代码
	if($GLOBALS['ischeck']){
		$_array=array(
			array('incode',0,10,9999999),
			array('incode',12,$king->lang->get('portal/check/incode'),!$king->portal->unModelCode($data['incode'],$data['modelname'],$data['modeltable'])),
		);
		
	}else{
		$_array=array();
	}
	$s.=$king->htmForm($king->lang->get('system/common/code'),'<textarea id="incode" name="incode" class="k_in w400" style="height:135px;font-size:10px;line-height:10px;">'.htmlspecialchars($data['incode']).'</textarea>',$_array);


	$but=kc_htm_a($king->lang->get("system/common/save"),"{CMD:'incode'}");

	if($GLOBALS['ischeck']){//如果以上几个都正确的话，就开始执行验证
		$king->cache->del('portal/model');
		$king->cache->rd('portal/model');

		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/add')."</p>",1);
	}

	$height=290+$GLOBALS['check_num']*15;
	kc_ajax($king->lang->get('portal/list/dbin'),$s,$but,null,435,$height);
}

/* ------>>> KingCMS for PHP <<<--------------------- */

 //列表页
//king_list
function king_inc_list(){
	global $king,$action;

	$modelid=kc_get('modelid');

	$left=array();
	$right=array();

	$left['']=array(
		'href'=>'manage.model.php',
		'ico'=>'a2',
		'title'=>$king->lang->get('portal/list/model'),
	);
	if($modelid){
		$left['edt']=array(
			'href'=>'manage.model.php?action=edt&modelid='.$modelid,
			'ico'=>'b2',
			'title'=>$king->lang->get('system/common/edit'),
		);
	}else{
		$left['edt']=array(
			'href'=>'manage.model.php?action=edt',
			'ico'=>'c2',
			'title'=>$king->lang->get('system/common/add'),
		);
	}

	if($action==''){
		$left[]=array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'incode\',METHOD:\'GET\'}',
			'ico'=>'i2',
			'title'=>$king->lang->get('portal/common/incode'),
		);
	}

	if($king->acc('portal_list')){
		if($action=='edt' && $modelid){
			$right[]=array(
				'href'=>'manage.field.php?modelid='.$modelid,
				'title'=>$king->lang->get('portal/title/field'),
				'ico'=>'a4',
			);
			//$right='<a href="manage.field.php?modelid='.$modelid.'">'.$king->lang->get('portal/title/field').'</a>';
		}
		$right[]=array(
			'href'=>'manage.php',
			'title'=>$king->lang->get('portal/title/list'),
			'ico'=>'a1',
		);
	}

	return array($left,$right);
} //!king_list

/* ------>>> KingCMS for PHP <<<--------------------- */

 //默认执行页面
//king_def
function king_def(){
	global $king;

	$king->access("portal_model");

//	kc_error('<pre>'.print_r($king->portal->infoModel(19),1));

	$_sql='select modelid,modelname,modeltable,klanguage from %s_model order by norder desc,modelid desc';
	if(!$_res=$king->db->getRows($_sql,1))
		$_res=array();

	//准备开始列表
	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$_manage="'<a title=\"".$king->lang->get('system/common/editattrib')."\" href=\"manage.model.php?action=edt&modelid='+K[0]+'\">'+$.kc_icon('b2','".$king->lang->get('system/common/edit')."')+'</a>'";//attrib
	$_manage.="+'<a title=\"".$king->lang->get('portal/list/dbdelete')."\" href=\"javascript:;\" class=\"k_ajax\" rel=\"{list:'+K[0]+',CMD:\'delete\'}\">'";
	$_manage.="+$.kc_icon('d2','".$king->lang->get('system/common/del')."')+'</a>'";//db_delete
	$_manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'dbcopy\',METHOD:\'GET\',modelid:'+K[0]+'}\">'";
	$_manage.="+$.kc_icon('e2','".$king->lang->get('portal/list/dbcopy')."')+'</a>'";//db_copy
	$_manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'dbout\',modelid:'+K[0]+'}\">'+$.kc_icon('i2','".$king->lang->get('portal/list/dbout')."')+'</a>'";//db_out
	$_manage.="+'<a title=\"".$king->lang->get('portal/title/field')."\" href=\"manage.field.php?modelid='+K[0]+'\">'+$.kc_icon('a4','".$king->lang->get('portal/common/fieldlist')."')+'</a>'";//db_field
	$_manage.="+'<a title=\"".$king->lang->get('portal/title/fieldadd')."\" href=\"manage.field.php?action=edt&modelid='+K[0]+'\">'+$.kc_icon('c4','".$king->lang->get('portal/common/addfield')."')+'</a>'";//db_field_add
	$_manage.="+$.kc_updown(K[0])";//up


	$_js=array(
		"$.kc_list(K[0],K[1],'manage.field.php?modelid='+K[0],1)",
		$_manage,
		"'".DB_PRE."__'+K[2]",
		"'{King:Portal.'+K[2]+'}<span class=\"i green\">&lt;-- INNER --&gt;</span>{/King:Portal.'+K[2]+'}'",
		"'<i>'+K[3]+'</i>'",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('model.php?pid=PID&rn=RN',$king->db->getRows_number('%s_model')));

	$s.="ll('".$king->lang->get('portal/list/modelname')."','manage','".$king->lang->get('portal/list/table')."','".$king->lang->get('portal/list/tag')."','".$king->lang->get('portal/list/klanguage')."',1);";

	foreach($_res as $_rs){	 //td
		$s.='ll('.$_rs['modelid'].',\''.$_rs['modelname'].'\',\''.($_rs['modeltable']).'\',\''.kc_getlang($_rs['klanguage']).'\',0);';
	}

	//结束列表
	$s.=$king->closeList();

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/model'),$left,$right,$s);
} //!king_def
 //添加&编辑列表
//king_edt
function king_edt(){
	global $king;

	$king->access('portal_model_edt');

	$_htmlcode='';
	$_arraycheck=array();
	
	/**
	$_isattrib='isshow,ishead,iscommend,isup,isfocus,ishot';
	$_sql=$_isattrib.',issearch,modelname,modeltable,klanguage,isid,klistorder,kpageorder,nlocktime,nshowtime,ktemplatesearch,ktemplatepublish';
*/
	$_sql='issearch,modelname,modeltable,klanguage,isid,klistorder,kpageorder,nlocktime,nshowtime,ktemplatesearch,ktemplatepublish,npagenumber,nlistnumber,ispublish1,ispublish2,ktemplatecomment,ncommentnumber';
	$modelid=kc_get('modelid');

	$fields=explode(',',$_sql);
	if($GLOBALS['ismethod']||$modelid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$_array=array('istag','iscontent','isshow','ispath','iscommend','iskeyword','isdescription','isimage','isrelate');
			foreach($_array as $_value){
				$data[$_value]=1;
			}
			$data['kretitle']=$king->lang->get('system/common/title');
			$data['klanguage']=$_COOKIE['language'];
			$data['nlocktime']=24;
			$data['nshowtime']=0;
			$data['isid']=1;

			//默认排序
			$data['klistorder']='nup desc,norder desc';
			$data['kpageorder']='norder,kid';

			//默认显示数
			$data['nlistnumber']=20;
			$data['npagenumber']=1;
			$data['ncommentnumber']=20;

			//默认模板
			$tpath=$king->config('templatepath');
			$tdefa=$king->config('templatedefault');

			$ktemplate=$tpath.'/'.$tdefa;
			$data['ktemplatesearch']=is_file(ROOT.$ktemplate) ? $ktemplate : '';
			$data['ktemplatepublish']=is_file(ROOT.$ktemplate) ? $ktemplate : '';
			$data['ktemplatecomment']=is_file(ROOT.$ktemplate) ? $ktemplate : '';

		}
	}else{	//编辑数据，从数据库读出
		$data=$king->db->getRows_one('select '.$_sql.' from %s_model where modelid='.$modelid.' limit 1;');
		$data['modeltable']=DB_PREFIX.'__'.$data['modeltable'];
	}
	$data=kc_data($fields,$data);

	$s=$king->openForm('manage.model.php?action=edt',$king->lang->get('portal/caption/basic'));

	//模型名称
	$_array=array(
		array('modelname',0,2,50),
	);
	$s.=$king->htmForm($king->lang->get('portal/list/modelname').' (2-50)','<input class="k_in w200" type="text" name="modelname" value="'.htmlspecialchars($data['modelname']).'" maxlength="50" />',$_array);

	//数据表名
	if(empty($modelid)){//update
		$_array=array(
			array('modeltable',0,1,50),
			array('modeltable',1),
			array('modeltable',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select modelid from %s_model where modeltable='".$king->db->escape(kc_post('modeltable'))."';")),
			array('modeltable',18,null,$king->portal->holdmodel),//保留的模型名称
		);
		$s.=$king->htmForm($king->lang->get('portal/list/table').' (1-50)','<input class="k_in w200" type="text" name="modeltable" value="'.htmlspecialchars($data['modeltable']).'" maxlength="50" />',$_array);
	}else{
		$s.=$king->htmForm($king->lang->get('portal/list/table').' (1-50)','<input class="k_in w200" type="text" disabled="true" value="'.htmlspecialchars($data['modeltable']).'" />');
		$s.=kc_htm_hidden(array('modeltable'=>$data['modeltable']));
	}

	//klanguage
	$s.=$king->htmForm($king->lang->get('system/common/language'),kc_htm_select('klanguage',kc_htm_selectlang(),$data['klanguage']));

/**
	//[ATTRIB]
	$_array_attrib=explode(',',$_isattrib);
	foreach($_array_attrib as $_value){
		$data[$_value]==1
			? $_checked=' checked="checked"'
			: $_checked='';
		$_htmlcode.='<input type="checkbox" value="1" id="'.$_value.'" name="'.$_value.'"'.$_checked.'/>';
		$_htmlcode.='<label for="'.$_value.'">'.$king->lang->get('portal/label/attrib/'.$_value).'</label> ';
	}
	$s.=$king->htmForm($king->lang->get('portal/label/attrib1'),"<span>$_htmlcode</span>");
*/

	//加入搜索
	$data['issearch']==1
			? $_checked=' checked="checked"'
			: $_checked='';
	$_htmlcode ='<span><input type="checkbox" value="1" id="issearch" name="issearch"'.$_checked.'/>';
	$_htmlcode.='<label for="issearch">'.$king->lang->get('portal/label/attrib/issearch').'</label></span>';

	//是否在列表中显示id
	$data['isid']==1
			? $_checked=' checked="checked"'
			: $_checked='';
	$_htmlcode.=' <span><input type="checkbox" value="1" id="isid" name="isid"'.$_checked.'/>';
	$_htmlcode.='<label for="isid">'.$king->lang->get('portal/label/attrib/isid').'</label></span>';
	$s.=$king->htmForm($king->lang->get('system/common/option'),$_htmlcode);

	$s.=$king->splitForm($king->lang->get('portal/caption/all'));

	//klistorder
	$array_select=array(
		'nup desc,norder desc'=>$king->lang->get('portal/order/list1'),
		'nup desc,norder'=>$king->lang->get('portal/order/list2'),
		'nup desc,nlastdate desc'=>$king->lang->get('portal/order/list3'),
		'nup desc,nhit desc'=>$king->lang->get('portal/order/list4'),
		'nup desc,nhitlate desc'=>$king->lang->get('portal/order/list5'),
		'nup desc,ndigg1 desc'=>$king->lang->get('portal/order/list6'),
		'nup desc,ndigg0 desc'=>$king->lang->get('portal/order/list7'),
		'nup desc,ndigg desc'=>$king->lang->get('portal/order/list8'),
		'nup desc,nprice asc'=>$king->lang->get('portal/order/list9'),
		'nup desc,nprice desc'=>$king->lang->get('portal/order/list10'),
	);

	$array=array(
		array('klistorder',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/listorder'),kc_htm_input('klistorder',$data['klistorder'],255,200),$array,'',kc_htm_setvalue('klistorder',$array_select,300,1).kc_help('portal/help/listorder',500,400));

	//nlistnumber
	$array=array(
		array('nlistnumber',0,1,3),
		array('nlistnumber',2),
		array('nlistnumber',16,$king->lang->get('portal/check/listnumber'),1,100),
	);
	$array_select=array(10=>10,20=>20,25=>25,30=>30);
	$s.=$king->htmForm($king->lang->get('portal/label/mlistnumber'),kc_htm_input('nlistnumber',$data['nlistnumber'],3,50),$array,'',kc_htm_setvalue('nlistnumber',$array_select,200));

	//kpageorder
	$array_select=array(
		'norder,kid'=>$king->lang->get('portal/order/page1'),
		'norder desc,kid desc'=>$king->lang->get('portal/order/page2'),
		'kid'=>$king->lang->get('portal/order/page3'),
		'kid desc'=>$king->lang->get('portal/order/page4'),
		'kid1,kid'=>$king->lang->get('portal/order/page5'),
		'kid1,kid desc'=>$king->lang->get('portal/order/page6'),
		'kid1,isok desc,kid desc'=>$king->lang->get('portal/order/page7'),
	);
	$array=array(
		array('kpageorder',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/pageorder'),kc_htm_input('kpageorder',$data['kpageorder'],255,200),$array,'',kc_htm_setvalue('kpageorder',$array_select,300,1).kc_help('portal/help/pageorder',500,400));
	//npagenumber
	$array=array(
		array('npagenumber',0,1,3),
		array('npagenumber',2),
		array('npagenumber',16,$king->lang->get('portal/check/pagenumber'),1,100),
	);
	$array_select=array(1=>1,10=>10,20=>20,30=>30);
	$s.=$king->htmForm($king->lang->get('portal/label/mpagenumber'),kc_htm_input('npagenumber',$data['npagenumber'],3,50),$array,'',kc_htm_setvalue('npagenumber',$array_select,200));

	//ispublish1
	$_array_radio=array(
		0=>$king->lang->get('portal/label/pub0'),
		1=>$king->lang->get('portal/label/pub1'),
		2=>$king->lang->get('portal/label/pub2'),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/mpublish1'),kc_htm_radio('ispublish1',$_array_radio,$data['ispublish1']));

	//ispublish2
	$_array_radio=array(
		0=>$king->lang->get('portal/label/pub0'),
		1=>$king->lang->get('portal/label/pub1'),
		2=>$king->lang->get('portal/label/pub2'),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/mpublish2'),kc_htm_radio('ispublish2',$_array_radio,$data['ispublish2']));


	//nlocktime
	$array_value=array(
		24=>$king->lang->get('system/time/oneday'),
		168=>$king->lang->get('system/time/hebdomad'),
		360=>$king->lang->get('system/time/halfmoon'),
		0=>$king->lang->get('system/time/always'),
	);
	$_array=array(
		array('nlocktime',0,1,10),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/locktime').' ('.$king->lang->get('portal/label/unit').')','<input class="k_in w100" type="text" name="nlocktime" id="nlocktime" value="'.htmlspecialchars($data['nlocktime']).'" maxlength="10" />',$_array,null,kc_htm_setvalue('nlocktime',$array_value).kc_help('portal/help/locktime'));

	//nshowtime
	$array_value=array(
		168=>$king->lang->get('system/time/hebdomad'),
		360=>$king->lang->get('system/time/halfmoon'),
		720=>$king->lang->get('system/time/jan'),
		4368=>$king->lang->get('system/time/halfyear'),
		0=>$king->lang->get('system/time/always'),
	);
	$_array=array(
		array('nshowtime',0,1,10),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/showtime').' ('.$king->lang->get('portal/label/unit').')','<input class="k_in w100" type="text" name="nshowtime" id="nshowtime" value="'.htmlspecialchars($data['nshowtime']).'" maxlength="10" />',$_array,null,kc_htm_setvalue('nshowtime',$array_value,200).kc_help('portal/help/showtime'));

	//ktemplatepublish
	$_array=array(
		array('ktemplatepublish',0,5,255),
		array('ktemplatepublish',15),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/templatepublish').' (5-255)','<input class="k_in w400" type="text" id="ktemplatepublish" name="ktemplatepublish" value="'.htmlspecialchars($data['ktemplatepublish']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatepublish',$king->config('templatepath'),2).kc_help('portal/help/template',455,455));

	//ktemplatesearch
	$_array=array(
		array('ktemplatesearch',0,5,255),
		array('ktemplatesearch',15),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/templatesearch').' (5-255)','<input class="k_in w400" type="text" id="ktemplatesearch" name="ktemplatesearch" value="'.htmlspecialchars($data['ktemplatesearch']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatesearch',$king->config('templatepath'),2).kc_help('portal/help/template',455,455));

	//ktemplatecomment
	$_array=array(
		array('ktemplatecomment',0,5,255),
		array('ktemplatecomment',15),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/templatecomment').' (5-255)','<input class="k_in w400" type="text" id="ktemplatecomment" name="ktemplatecomment" value="'.htmlspecialchars($data['ktemplatecomment']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatecomment',$king->config('templatepath'),2).kc_help('portal/help/template',455,455));

	//ncommentnumber
	$array=array(
		array('ncommentnumber',0,1,3),
		array('ncommentnumber',2),
		array('ncommentnumber',16,$king->lang->get('portal/check/commentnumber'),1,100),
	);
	$array_select=array(10=>10,20=>20,25=>25,30=>30);
	$s.=$king->htmForm($king->lang->get('portal/label/mcommentnumber'),kc_htm_input('ncommentnumber',$data['ncommentnumber'],3,50),$array,'',kc_htm_setvalue('ncommentnumber',$array_select,200));
	
	$s.=kc_htm_hidden(array('modelid'=>$modelid));

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){

/**
		$_array=array();
		$_array_sql=explode(',',$_isattrib.',issearch,isid');
		foreach($_array_sql as $_value){
			$data[$_value]==1
				? $_val=1
				: $_val=0;
			$_array+=array($_value=>$_val);
		}
*/
		$_array=array(
			'issearch'=>($data['issearch']?1:0),
			'isid'=>($data['isid']?1:0),
			'ispublish1'=>$data['ispublish1'],
			'ispublish2'=>$data['ispublish2'],
			'modelname'=>$data['modelname'],
			'klanguage'=>$data['klanguage'],
			'klistorder'=>$data['klistorder'],
			'kpageorder'=>$data['kpageorder'],
			'npagenumber'=>$data['npagenumber'],
			'nlistnumber'=>$data['nlistnumber'],
			'nshowtime'=>$data['nshowtime'],
			'nlocktime'=>$data['nlocktime'],
			'ktemplatesearch'=>$data['ktemplatesearch'],
			'ktemplatepublish'=>$data['ktemplatepublish'],
			'ktemplatecomment'=>$data['ktemplatecomment'],
			'ncommentnumber'=>$data['ncommentnumber'],
			);
		//添加&更新数据
		if(!empty($modelid)){
			$king->db->update('%s_model',$_array,'modelid='.$modelid);
			$_nlog=7;
		}else{
			$_array+=array(
				'modeltable'=>strtolower($data['modeltable']),
				'norder'=>$king->db->neworder('%s_model'),
				);
			$_nlog=5;
			$_newid=$king->db->insert('%s_model',$_array);

			//__[modeltable]
			$king->portal->installmodeltable($data['modeltable']);

			$_array_sql=array('ktitle','ksubtitle','kimage','kcontent','kkeywords','ktag','kdescription','kpath','krelate','nprice','nnumber','nweight','nattrib');

			//补充循环添加的内容。
			$i=0;
			foreach($_array_sql as $val){
				$_array=array(
					'modelid'=>$_newid,
					'ktitle'=>$king->lang->get('system/common/'.substr($val,1)),
					'kfield'=>$val,
					'norder'=>$i+1,
				);
				if($val=='kcontent'){//内容设置长度
					$_array['nsizemin']=10;
					$_array['nsizemax']=999999;
					$_array['nstylewidth']=780;
					$_array['nstyleheight']=360;
				}
				$i++;
				$new_kid=$king->db->insert('%s_field',$_array);//循环最后一个获得的值为nattrib的newid
			}
			//
			$_array_sql=array('show','head','commend','up','focus','hot');
			foreach($_array_sql as $val){
				$_array=array(
					'modelid'=>$_newid,
					'ktitle'=>$king->lang->get('portal/label/attrib/is'.$val),
					'kfield'=>'n'.$val,
					'kid1'=>$new_kid,
					'norder'=>$i+1,
					'isuser1'=>0,
					'isuser2'=>0,
					'islist'=>1,
				);
				$i++;
				$king->db->insert('%s_field',$_array);
			}

		}

		$king->cache->del('portal/model/model'.$modelid);
		$king->cache->del('portal/model/name');
		$king->cache->del('portal/model/table');

/**/
		if(!$res=$king->db->getRows("select listid from %s_list where modelid=$modelid;"))
			$res=array();
		foreach($res as $rs){
			//更新列表信息
			$king->portal->lastUpdated($rs['listid'],'list');

			$king->cache->del('portal/list/'.$rs['listid']);

		}
		$king->cache->del('portal/model');
		$king->cache->del('portal/model/table');
		$king->cache->del('portal/model/name');
/**/
		//写log
		$king->log($_nlog,'Model:'.$data['modelname']);

		kc_goto($king->lang->get('system/goto/is'),'manage.model.php?action=edt','manage.model.php');
	}

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/model'.($modelid?'edt':'add')),$left,$right,$s);

}








?>