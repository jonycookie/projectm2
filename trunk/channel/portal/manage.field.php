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
	$king->access('portal_field_delete');

	$modelid=kc_get('modelid');
	$_list=kc_getlist();
	$_array=explode(',',$_list);

	$res=$king->db->getRows_one("select modeltable from %s_model where modelid=$modelid");
		$res
			? $modeltable=$res['modeltable']
			: kc_error($king->lang->get('system/error/not'));

	if(!$_res=$king->db->getRows("select kfield,ktitle from %s_field where kid in ($_list) and ntype<>0;"))
		$_res=array();

	foreach($_res as $rs){
		$_field=$rs['kfield'];
		$king->db->query("ALTER TABLE %s__$modeltable DROP $_field;",1);

		$king->cache->del('portal/model/model'.$modelid);
		$king->cache->rd('portal/_'.$modeltable);

		//写log
		$king->log(6,'Field:'.$rs['ktitle']);
	}

	$king->db->query("delete from %s_field where kid in ($_list) and ntype<>0;");


	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

} //!king_ajax_delete
 //上移下移
//king_ajax_updown
function king_ajax_updown(){
	global $king;
	$king->access('portal_field_updown');

	$kid=kc_get('kid',2,1);
	$kid1=kc_get('kid1',2,1);
//	$insql_kid1=$kid1?" and kid1=$kid1":'';
	$_res=$king->db->getRows_one("select modelid from %s_field where kid=$kid;");
		$_res['modelid']
			? $modelid=$_res['modelid']
			: kc_error($king->lang->get('system/error/not').'modelid');
	if(!$kid)
		kc_error($king->lang->get('system/error/not'));

	$king->db->updown('%s_field',$kid," modelid={$modelid} and kid1={$kid1}",0);
} //!king_ajax_updown
 //显示隐藏
//king_ajax_show
function king_ajax_show1(){
	global $king;
	$king->access('portal_field_edt');

	$_list=kc_getlist();
	$_cmd=CMD;
	$modelid=kc_get('modelid',22);
	$_cmd=='show1'
		? $_show=1
		: $_show=0;
	$king->db->query("update %s_field set isadmin1=$_show,isadmin2=$_show,isuser1=$_show,isuser2=$_show where kid in ($_list) and kfield<>'ktitle';");

	$king->cache->del('portal/model/model'.$modelid);

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('portal/ok/'.$_cmd)."</p>",1);
}
function king_ajax_show0(){
	call_user_func('king_ajax_show1');
}
function king_ajax_isshow(){
	global $king;
	$king->access('portal_field_edt');
	$kid=kc_get('kid',2,1);
	$field=kc_post('field');
	$is=kc_get('is',2,1)?1:0;
	$modelid=kc_get('modelid',22,1);

	$array_is=array('isadmin1','isadmin2','isuser1','isuser2','islist','issearch','isrelate');

	if(!in_array($field,$array_is))//防止非法输入
		kc_error($king->lang->get('system/error/param'));

	if($res=$king->db->getRows_one("select ktitle from %s_field where kid={$kid} and kfield='ktitle'"))
		kc_ajax('',kc_icon($is?'n2':'n1'),0,"alert('".$king->lang->get('portal/tip/noedt').": {$res['ktitle']}')");

	$array=array(
		$field=>$is,
	);
	$king->db->update('%s_field',$array,"kid=$kid");
	$king->cache->del('portal/model/model'.$modelid);
	$s=kc_icon($is?'n1':'n2');


	$js="\$('#{$field}_{$kid}').attr('rel','{CMD:\'isshow\',field:\'{$field}\',modelid:$modelid,kid:$kid,is:".(1-$is).",ID:\'{$field}_{$kid}\',IS:2}')";

	kc_ajax('',$s,0,$js);
}

/* ------>>> KingCMS for PHP <<<--------------------- */

 //列表页
//king_inc_list
function king_inc_list(){
	global $king,$action;

	$modelid=kc_get('modelid');
	$kid1=kc_get('kid1',2);
	$kid=kc_get('kid',2);

	$left=array();
	$right=array();

	if(!$modelid){//如果没有modelid值，则判断kid
		if($kid){
			$res=$king->db->getrows_one("select modelid from %s_field where kid=$kid;");
			$modelid=$res['modelid'];
		}
	}
/*
	if($action==''){
		$h2=$king->lang->get('portal/title/field');
	}else{
		$kid
			? $h2=$king->lang->get('portal/title/fieldedt')
			: $h2=$king->lang->get('portal/title/fieldadd');
	}
*/
	$type=kc_get('type');

	$left[]=array(
		'href'=>'manage.model.php',
		'title'=>$king->lang->get('portal/list/model'),
		'ico'=>'a2',
	);

	if($kid1){
		$left[]=array(
			'href'=>'manage.field.php?modelid='.$modelid,
			'ico'=>'a4',
			'title'=>$king->lang->get('portal/title/field'),
		);
		$left['']=array(
			'href'=>'manage.field.php?modelid='.$modelid.'&kid1='.$kid1,
			'title'=>$king->lang->get('system/common/sublist'),
			'ico'=>'a4',
		);
//		$left.='<a href="manage.field.php?modelid='.$modelid.'">'.kc_icon('a4').$king->lang->get('portal/title/field').'</a>';
//		$left.='<a'.$_array[''].' href="manage.field.php?modelid='.$modelid.'&kid1='.$kid1.'">'.kc_icon('a4').$king->lang->get('system/common/sublist').'</a>';
	}else{
		$left['']=array(
			'href'=>'manage.field.php?modelid='.$modelid,
			'ico'=>'a4',
			'title'=>$king->lang->get('portal/title/field'),
		);
//		$left.='<a'.$_array[''].' href="manage.field.php?modelid='.$modelid.'">'.kc_icon('a4').$king->lang->get('portal/title/field').'</a>';
	}
	if($kid){
		$left['edt']=array(
			'href'=>'manage.field.php?action=edt&kid='.$kid,
			'ico'=>'b4',
			'title'=>$king->lang->get('system/common/edit'),
		);
	}else{
		$left['edt']=array(
			'href'=>'manage.field.php?action=edt&modelid='.$modelid.'&kid1='.$kid1.'&type='.$type,
			'ico'=>'c4',
			'title'=>$king->lang->get('system/common/add'),
		);
	}
/*
	$kid
		? $left.='<a'.$_array['edt'].' href="manage.field.php?action=edt&kid='.$kid.'">'.kc_icon('b4').$king->lang->get('system/common/edit').'</a>'
		: $left.='<a'.$_array['edt'].' href="manage.field.php?action=edt&modelid='.$modelid.'&kid1='.$kid1.'&type='.$type.'">'.kc_icon('c4').$king->lang->get('system/common/add').'</a>';
*/
	if($action==''){
		$right[]=array(
			'href'=>'manage.model.php?action=edt&modelid='.$modelid,
			'title'=>$king->lang->get('portal/title/modeledt'),
			'ico'=>'b2',
		);
//		$right='<a href="manage.model.php?action=edt&modelid='.$modelid.'">'.$king->lang->get('portal/title/modeledt').'</a>';
	}
	$right[]=array(
		'href'=>'manage.php',
		'title'=>$king->lang->get('portal/title/list'),
		'ico'=>'a1',
	);


//	$right.='<a href="manage.php">'.$king->lang->get('portal/title/list').'</a>';

	return array($left,$right);
} //!king_list

/* ------>>> KingCMS for PHP <<<--------------------- */

 //默认执行页面
//king_def
function king_def(){
	global $king;

	$king->access("portal_field");
	$modelid=kc_get('modelid',2,1);
	$kid1=!empty($_GET['kid1']) ? kc_get('kid1',2,1) : 0;
	$model=$king->portal->infoModel($modelid);
//	kc_error('<pre>'.print_r($model,1));

	$_sql="select kid,ktitle,kfield,ntype,isadmin1,isadmin2,isuser1,isuser2,islist,issearch,isrelate from %s_field where modelid={$modelid} and kid1={$kid1} order by norder asc,kid asc";
	if(!$_res=$king->db->getRows($_sql,1))
		$_res=array();

	//准备开始列表
	$_cmd=array(
		$king->lang->get('system/common/del'),
		'delete'=>$king->lang->get('system/common/del'),
		$king->lang->get('system/common/setting'),
		'show1'=>$king->lang->get('portal/common/show1'),
		'show0'=>$king->lang->get('portal/common/show0'),
	);
	$_manage="'<a href=\"manage.field.php?action=edt&kid1={$kid1}&kid='+K[0]+'\">'+$.kc_icon('b4','".$king->lang->get('system/common/edit')."')+'</a>'";
	$_manage.="+isdelete(K[0],K[3])";
	$_manage.=$kid1?'':"+subfield(K[0],K[3],K[2])";
	$_manage.="+$.kc_updown(K[0],'updown')";//up

	$_js=array(
		"$.kc_list(K[0],K[1],titleLink(K[0],K[3],K[2]),0,1,iscog(K[3]))",
		$_manage,
		"'<i>'+setAttrib(K[0],'isadmin1',K[4],K[3])+'</i>'",
		"'<i>'+setAttrib(K[0],'isadmin2',K[5],K[3])+'</i>'",
		"'<i>'+setAttrib(K[0],'isuser1',K[6],K[3])+'</i>'",
		"'<i>'+setAttrib(K[0],'isuser2',K[7],K[3])+'</i>'",
		"'<i>'+setAttrib(K[0],'islist',K[8],K[3])+'</i>'",
		"'<i>'+setAttrib(K[0],'issearch',K[9],K[3])+'</i>'",
		"'<i>'+setAttrib(K[0],'isrelate',K[10],K[3])+'</i>'",
		"isSys(K[3],K[2])",
		"isSys(K[3],ntype[K[3]])",
		"outTag(K[2])"
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.field.php?modelid='.$modelid.'&pid=PID&rn=RN',$king->db->getRows_number('%s_field',"modelid=$modelid and kid1=$kid1")),array('modelid'=>$modelid,'kid1'=>$kid1));

	//设置ntype数组
	$_array=array(0=>$king->lang->get('portal/type/n0'));
	/*
	for($i=0;$i<=$king->portal->ntype;$i++){
		$_array+=array($i => $king->lang->get('portal/type/n'.$i));
	}
	*/
	foreach($king->portal->ntype as $val){
		$_array[$val]=$king->lang->get('portal/type/n'.$val);
	}

	$s.=kc_js2array('ntype',$_array);

	$_fun="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',modelid:$modelid,list:'+id+'}\">'";
	$_fun.="+$.kc_icon('d4','".$king->lang->get('system/common/del')."')+'</a>'".NL;
	$s.="function setAttrib(id,name,is,ntype){var I1,ico;is?ico='n1':ico='n2';
		if(ntype==0&&(name=='islist'||name=='issearch'||name=='isrelate')){return $.kc_icon('n3');};
		I1='<a id=\"'+name+'_'+id+'\" href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'isshow\',field:\''+name+'\',kid:'+id+',modelid:$modelid,is:'+(1-is)+',IS:2,ID:\''+name+'_'+id+'\'}\">'+$.kc_icon(ico)+'</a>';
		return I1;};";

	$s.="function isdelete(id,ntype){var I1;(ntype==0)?I1=$.kc_icon():I1=$_fun;return I1;};";
	$s.="function iscog(ntype){var I1;(ntype==0)?I1='l2':I1='k2';return I1;}";
	$s.="function isSys(num,val){var I1;num==0?I1='<span class=\"gray\">'+val+'</span>':I1=val;return I1;};";
	$s.="function subfield(id,ntype,kfield){var I1;ntype==99||kfield=='nattrib'
	?I1='<a href=\"manage.field.php?modelid={$modelid}&kid1='+id+'\">'+$.kc_icon('a4','".$king->lang->get('system/common/sublist')."')+'</a><a href=\"manage.field.php?action=edt&modelid={$modelid}&kid1='+id+'\">'+$.kc_icon('c4','".$king->lang->get('system/common/addsub')."')+'</a>'
	:I1=$.kc_icon()+$.kc_icon();return I1;};";
	$s.="function outTag(field){var I1;field.length==0||field=='nattrib'?I1='':I1='<span  onClick=\"window.clipboardData.setData(\'Text\',\'{king:'+field.substr(1)+'/}\');\">{king:'+field.substr(1)+'/}</span>';return I1;}";
	$s.="function titleLink(id,ntype,kfield){var I1=ntype==99||kfield=='nattrib'?'manage.field.php?modelid={$modelid}&kid1='+id:'manage.field.php?action=edt&kid1={$kid1}&kid='+id;return I1};";

	$s.=NL."ll('".$king->lang->get('portal/list/ktitle')."','manage','<i>".$king->lang->get('portal/list/admin1')."</i>','<i>".$king->lang->get('portal/list/admin2')."</i>','<i>".$king->lang->get('portal/list/user1')."</i>','<i>".$king->lang->get('portal/list/user2')."</i>','<i>".$king->lang->get('portal/list/islist')."</i>','<i>".$king->lang->get('portal/list/issearch')."</i>','<i>".$king->lang->get('portal/list/isrelate')."</i>','".$king->lang->get('portal/list/field')."','".$king->lang->get('portal/list/ntype')."','".$king->lang->get('system/common/tag')."',1);";

	foreach($_res as $_rs){//td
		$s.='ll('.$_rs['kid'].',\''.$_rs['ktitle'].'\',\''.($_rs['kfield']).'\','.$_rs['ntype'].','.$_rs['isadmin1'].','.$_rs['isadmin2'].','.$_rs['isuser1'].','.$_rs['isuser2'].','.$_rs['islist'].','.$_rs['issearch'].','.$_rs['isrelate'].',0);';
	}

	//结束列表
	$s.=$king->closeList();

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/field')."({$model['modelname']})",$left,$right,$s);

} //!king_def
 //添加&编辑列表
//king_edt
function king_edt(){
	global $king;

	$_htmlcode='';
	$_arraycheck=array();
	$_array_varchar=$king->portal->array_varchar;//varchar类型的字段
	
	$king->access('portal_field_edt');

	$_sql='ktitle,kfield,modelid,ntype,nvalidate,nsizemin,nsizemax,kdefault,koption,nstylewidth,nstyleheight,issearch,isadmin1,isadmin2,isuser1,isuser2,islist,khelp,isrelate,istitle';

	$modelid=kc_get('modelid');
	$kid=kc_get('kid',2);
	$type=kc_get('type',2);
	$kid1=kc_get('kid1',2);
	if($type=='') $type=1;

	$at_array=array(1,4,5,7,12,13,14);//允许添加的子字段

	//ntype参数验证
	if(($kid1&&!in_array($type,$at_array)) || !in_array($type,$king->portal->ntype)){
		kc_error($king->lang->get('system/error/param').kc_clew(__FILE__,__LINE__));
	}

	$fields=explode(',',$_sql);
	if($GLOBALS['ismethod']||$kid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			if(in_array($type,$_array_varchar)){
				$data['nsizemin']=1;
				$data['nsizemax']=255;
			
			}else{
				$data['nsizemin']=1;
				$data['nsizemax']=999999;
			}
			$data['nstylewidth']=400;
			$data['nstyleheight']=70;
			$data['isadmin1']=1;
			$data['isadmin2']=1;
			$data['isuser1']=1;
			$data['isuser2']=1;
			$data['istitle']=1;
			if($type==12){
				$data['nvalidate']=13;
				$data['kdefault']='#000000';
				$data['nsizemax']=7;
			}
			if($type==14){
				$data['nstylewidth']=100;
				$data['nsizemax']=10;
				$data['kdefault']='TODAY';
				$data['nvalidate']=9;//数据类型设置为日期类型
				$data['nsizemin']=10;
				$data['nsizemax']=10;
			}
		}else{
			if($kid!=''){
				$_res=$king->db->getRows_one('select ntype from %s_field where kid='.$kid);//上面kc_get('kid')中有数据类型验证，无安全隐患
				$_res
					? $type=$_res['ntype']
					: kc_error($king->lang->get('system/error/not'));
			}
		}
	}else{	//编辑数据，从数据库读出
		if($data=$king->db->getRows_one('select '.$_sql.' from %s_field where kid='.$kid.' limit 1;')){
			$type=$data['ntype'];
			$modelid=$data['modelid'];
		}else{
			kc_error($king->lang->get('system/error/param').'<br/>select '.$_sql.' from %s_field where kid='.$kid.' limit 1;<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
		}
	}
	$data=kc_data($fields,$data);

	$model=$king->portal->infoModel($modelid);

	$s='<script type="text/javascript">';
	$s.='function jumpmenu(obj){eval("parent.location=\'manage.field.php?action=edt&modelid='.$modelid.'&kid1='.$kid1.'&type="+obj.options[obj.selectedIndex].value+"\'");}';
	$s.='</script>';

	$s.=$king->openForm('manage.field.php?action=edt');

	//字段类型
	$_array=array(array('ntype',2));
	if($kid){
		$s.=$king->htmForm($king->lang->get('portal/list/ntype'),kc_htm_select('type',array($type=>$king->lang->get('portal/type/n'.$type)),'',' disabled="true"'),$_array);
	}else{
		$_array_select=array();
		$array_type=$kid1?$at_array:$king->portal->ntype;
		foreach($array_type as $val){
			$_array_select[$val]=$king->lang->get('portal/type/n'.$val);
		}
		$s.=$king->htmForm($king->lang->get('portal/list/ntype'),kc_htm_select('type',$_array_select,$type,' onChange="jumpmenu(this);"'),$_array);
	}

	//子项目中是否显示标题
	if($kid1&&$type!=13){
		$checked=$data['istitle']==1?' checked="checked"':'';
		$s_istitle=$kid1?'<input'.$checked.' type="checkbox" id="istitle" name="istitle" value="1"/><label for="istitle">'.$king->lang->get('portal/label/showtitle').'</label>':'';
	}else{
		$s_istitle='';
	}
	//字段标题
	$_array=array(array('ktitle',0,2,50));
	$s.=$king->htmForm($king->lang->get('portal/list/ktitle').' (2-50)','<input class="k_in w200" type="text" name="ktitle" value="'.htmlspecialchars($data['ktitle']).'" maxlength="50" />'.$s_istitle,$_array);


	if($type!=99){
		

		//字段名
		if($kid){//update
			$s.=$king->htmForm($king->lang->get('portal/list/kfield'),'<input class="k_in w200" type="text" disabled="true" value="'.htmlspecialchars($data['kfield']).'" />');
			$s.=kc_htm_hidden(array('kfield'=>$data['kfield']));
		}else{
			$_array=array(
				array('kfield',0,1,50),
				array('kfield',4),
				array('kfield',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select kid from %s_field where kfield='k_".$king->db->escape(kc_post('kfield'))."' and modelid=$modelid;"))
			);
			$s.=$king->htmForm($king->lang->get('portal/list/kfield').' (1-50)','<input class="k_in w200" type="text" name="kfield" value="'.htmlspecialchars($data['kfield']).'" maxlength="50" />',$_array);
		}

		//数据类型
		if(in_array($type,array(1,14))){
			$array=array(0,1,2,22,3,4,5,6,7,8,9,13);
			$_array_select=array();
			foreach($array as $val){
				$_array_select+=array($val=>$king->lang->get('portal/validate/n'.$val));
			}
			$s.=$king->htmForm($king->lang->get('portal/list/nvalidate'),kc_htm_select('nvalidate',$_array_select,$data['nvalidate']),array(array('nvalidate',2)));
		}

		//长度
		if((!in_array($type,array(0,4,5,6,7,12,13)))||$data['kfield']=='kcontent'){//系统标签和颜色值无需设置长度
			$_size='<input class="k_in w50" type="text" name="nsizemin" id="nsizemin" value="'.htmlspecialchars($data['nsizemin']).'" maxlength="6" />';
			$_size.=' - <input class="k_in w100" type="text" name="nsizemax" id="nsizemax" value="'.htmlspecialchars($data['nsizemax']).'" maxlength="11" />';
			$_array=array(
				array('nsizemin',2),
				array('nsizemax',2),
				array('nsizemin',0,1,6),
				array('nsizemax',0,1,11),
			);
			if(in_array($type,$_array_varchar)){
				$_lang='nsize';
				$_array[]=array('nsizemin',16,$king->lang->get('portal/check/nsize1'),0,255);
				$_array[]=array('nsizemax',16,$king->lang->get('portal/check/nsize2'),1,255);
			}else{
				$_lang='nsizetext';
			}
			$s.=$king->htmForm($king->lang->get('portal/label/'.$_lang),$_size,$_array);
		}else{
			$s.=kc_htm_hidden(array('nsizemin'=>$data['nsizemin'],'nsizemax'=>$data['nsizemax']));
		}

		//默认值
		if(in_array($type,array(1,4,5,6,7,8,10))){
			$_array=array(array('kdefault',0,0,255));
			$str='<input class="k_in w400" type="text" id="kdefault" name="kdefault" value="'.htmlspecialchars($data['kdefault']).'" maxlength="255" />';
			/**/
			if(in_array($type,array(8)))
				$str.=kc_f_brow('kdefault',$king->config('uppath').'/image',0);
			if(in_array($type,array(10)))
				$str.=kc_f_brow('kdefault',$king->config('uppath').'/file',1);
			
			/**/
			$s.=$king->htmForm($king->lang->get('portal/label/kdefault').' (0-255)',$str,$_array);
		}elseif(in_array($type,array(12))){//颜色
			$_array=array(
				array('kdefault',0,7,7),
				array('kdefault',13),
			);
			$str='<input class="k_in k_color" type="text" id="kdefault" name="kdefault" value="'.htmlspecialchars($data['kdefault']).'" maxlength="7" '.(kc_validate($data['kdefault'],13)?' style="background:'.$data['kdefault'].'"':'').'/>';
			$s.=$king->htmForm($king->lang->get('portal/label/kdefault'),$str,$_array,null,kc_f_color('kdefault'));
		}elseif(in_array($type,array(14))){
			$array=array(
				'TODAY'=>$king->lang->get('system/time/today'),
			);
			$_array=array(
				array('kdefault',0,0,20),
			);
			$s.=$king->htmForm($king->lang->get('portal/label/kdefault'),kc_htm_input('kdefault',$data['kdefault']),$_array,0,kc_htm_setvalue('kdefault',$array));
		}elseif(in_array($type,array(13))){
			$array=array(
				1=>$king->lang->get('system/common/yes'),
				0=>$king->lang->get('system/common/no'),
			);
			$s.=$king->htmForm($king->lang->get('portal/label/kdefault'),kc_htm_radio('kdefault',$array,$data['kdefault']));
		}else{
			$s.=kc_htm_hidden(array('kdefault'=>''));
		}
	}//end if($type==99)

	//选项
	if(in_array($type,array(4,5,6,7))){
		$array=array(
			array('koption',0,1,999999),
		);
		$_default='<table class="k_side" cellspacing="0"><tr><td><textarea name="koption" class="k_in w400" cols="130" rows="7">'.htmlspecialchars($data['koption']).'</textarea></td>';
		$_default.='<td>'.kc_help('portal/help/koption',360,310);
		$_default.='</td></tr></table>';
		$s.=$king->htmForm($king->lang->get('portal/label/koption'),$_default,$array);
	}elseif(in_array($type,array(1,8,10))||in_array($data['kfield'],array('nprice','nnumber','nweight'))){
		$array=array(
			array('koption',0,0,999999),
		);
		$_default='<table class="k_side" cellspacing="0"><tr><td><textarea name="koption" class="k_in w400" cols="130" rows="7">'.htmlspecialchars($data['koption']).'</textarea></td>';
		$_default.='<td>'.kc_help('portal/help/kdefault',360,260);
		$_default.='</td></tr></table>';
		$s.=$king->htmForm($king->lang->get('portal/label/kdefault1'),$_default,$array);
	}else{
		$s.=kc_htm_hidden(array('koption'=>''));
	}

	//尺寸
	if(in_array($type,array(2,3,6,9,11))||$data['kfield']=='kcontent'){//长X宽
		$_size='<input class="k_in w50" type="text" name="nstylewidth" id="nstylewidth" value="'.htmlspecialchars($data['nstylewidth']).'" maxlength="4" />';
		$_size.=' X <input class="k_in w50" type="text" name="nstyleheight" id="nstyleheight" value="'.htmlspecialchars($data['nstyleheight']).'" maxlength="4" />(px)';
		$_array=array(
			array('nstylewidth',2),
			array('nstyleheight',2),
			array('nstylewidth',0,1,4),
			array('nstyleheight',0,1,4),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/nstyle'),$_size,$_array);
	}elseif(in_array($type,array(1,8,10))){//长
		$_size='<input class="k_in w50" type="text" name="nstylewidth" id="nstylewidth" value="'.htmlspecialchars($data['nstylewidth']).'" maxlength="4" />';
		$_array=array(
			array('nstylewidth',2),
			array('nstyleheight',2),
			array('nstylewidth',0,1,4),
			array('nstyleheight',0,1,4),
		);
		$s.=kc_htm_hidden(array('nstyleheight'=>0));
		$s.=$king->htmForm($king->lang->get('portal/label/nstylewidth'),$_size,$_array,'',kc_htm_setvalue('nstylewidth',array(50=>'50 px',100=>'100 px',200=>'200 px',400=>'400 px')));
	}else{
		$s.=kc_htm_hidden(array('nstylewidth'=>0,'nstyleheight'=>0));
	}

	/**
	//上传文件类型
	if(in_array($type,array(8))){
		$_array=array(
			array('nupfile',0,0,255),
			array('nupfile',2),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/nupfile'),'<input class="k_in w300" type="text" name="nupfile" value="'.htmlspecialchars($data['nupfile']).'" maxlength="255" />',$_array);
	}else{
		$s.=kc_htm_hidden(array('nupfile'=>''));
	}
	/**/

	$_htmlcode='';
	//加入搜索和关联选项
	if(in_array($type,array(1,2))){
		$data['issearch']==1
				? $_checked=' checked="checked"'
				: $_checked='';
		$_htmlcode ='<span><input type="checkbox" value="1" id="issearch" name="issearch"'.$_checked.'/>';
		$_htmlcode.='<label for="issearch">'.$king->lang->get('portal/label/attrib/issearch').'</label></span>';
//		$s.=$king->htmForm($king->lang->get('system/common/option'),$_htmlcode);
	}else{
		$s.=kc_htm_hidden(array('issearch'=>0));
	}
	if(in_array($type,array(1,4,5))){
		$data['isrelate']==1
				? $_checked=' checked="checked"'
				: $_checked='';
		$_htmlcode.=' <span><input type="checkbox" value="1" id="isrelate" name="isrelate"'.$_checked.'/>';
		$_htmlcode.='<label for="isrelate">'.$king->lang->get('portal/label/attrib/isrelate1').'</label></span>';
//		$s.=$king->htmForm($king->lang->get('system/common/option'),$_htmlcode);
	}else{
		$s.=kc_htm_hidden(array('issearch'=>0));
	}
	if($_htmlcode){
		$s.=$king->htmForm($king->lang->get('system/common/option'),$_htmlcode);
	}

	//是否显示
	if(($type==0 && $data['kfield']=='ktitle')||$type==99){
		$s.=kc_htm_hidden(array(
			'isadmin1'=>1,
			'isadmin2'=>1,
			'isuser1'=>1,
			'isuser2'=>1,
		));
		
	}else{
		$_htmlcode ='<span>';

		$data['isadmin1']==1 ? $_checked=' checked="checked"' : $_checked='';
		$_htmlcode.='<input type="checkbox" value="1" id="isadmin1" name="isadmin1"'.$_checked.'/>';
		$_htmlcode.='<label for="isadmin1">'.$king->lang->get('portal/label/attrib/isadmin1').'</label>';

		$data['isadmin2']==1 ? $_checked=' checked="checked"' : $_checked='';
		$_htmlcode.='<input type="checkbox" value="1" id="isadmin2" name="isadmin2"'.$_checked.'/>';
		$_htmlcode.='<label for="isadmin2">'.$king->lang->get('portal/label/attrib/isadmin2').'</label>';

		$data['isuser1']==1 ? $_checked=' checked="checked"' : $_checked='';
		$_htmlcode.='<input type="checkbox" value="1" id="isuser1" name="isuser1"'.$_checked.'/>';
		$_htmlcode.='<label for="isuser1">'.$king->lang->get('portal/label/attrib/isuser1').'</label>';

		$data['isuser2']==1 ? $_checked=' checked="checked"' : $_checked='';
		$_htmlcode.='<input type="checkbox" value="1" id="isuser2" name="isuser2"'.$_checked.'/>';
		$_htmlcode.='<label for="isuser2">'.$king->lang->get('portal/label/attrib/isuser2').'</label>';

		if(!in_array($type,array(0,2,3,9,11))){
			$data['islist']==1 ? $_checked=' checked="checked"' : $_checked='';
			$_htmlcode.='<input type="checkbox" value="1" id="islist" name="islist"'.$_checked.'/>';
			$_htmlcode.='<label for="islist">'.$king->lang->get('portal/label/attrib/islist').'</label>';
		}

		$_htmlcode.='</span>';
		$s.=$king->htmForm($king->lang->get('portal/label/attrib/isshow'),$_htmlcode);
	}

	//khelp
	if($type!=0&&!$kid1){
		$s.=$king->htmForm($king->lang->get('portal/list/khelp'),'<table class="k_side" cellspacing="0"><tr><td><textarea class="k_in w400" rows="5" name="khelp" >'.htmlspecialchars($data['khelp']).'</textarea></td><td>'.kc_help('portal/help/khelp',300,160).'</td></tr></table>');
	}

	$s.=kc_htm_hidden(array('modelid'=>$modelid,'kid'=>$kid,'ntype'=>$type,'kid1'=>$kid1));
	$s.=$king->closeForm('save');


	//数据处理
	if($GLOBALS['ischeck']){

		$_sql='ktitle,nsizemin,nsizemax,kdefault,koption,nstylewidth,nstyleheight,khelp';

		$_array=array();
		$_array_sql=explode(',',$_sql);
		foreach($_array_sql as $val){
			if(isset($_POST[$val])) $_array[$val]=$data[$val];
		}
		$array_is=array('issearch','isadmin1','isadmin2','isuser1','isuser2','islist','isrelate','istitle');
		foreach($array_is as $val){
			$_array[$val]=$data[$val]==1?1:0;
		}
		$_array['nvalidate']= $data['nvalidate'] ? $data['nvalidate'] : 0;
		//添加&更新数据
		if($kid){//update
			if(!$resmt=$king->db->getRows_one("select modeltable from %s_model where modelid=$modelid;"))
				kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
			$_modeltable=$resmt['modeltable'];

			if(in_array($type,$_array_varchar)){//varchar类型
				$king->db->query('alter table %s__'.$king->db->escape($_modeltable).' modify '.$king->db->escape(kc_post('kfield')).' varchar('.$_array['nsizemax'].') null;');
			}
			$king->db->update('%s_field',$_array,'kid='.$kid);
			$_nlog=7;
		}else{
			$_array+=array(
				'ktitle'=>$data['ktitle'],
				'kfield'=>$data['kfield']?'k_'.$data['kfield']:'',
				'modelid'=>$data['modelid'],
				'ntype'=>$data['ntype'],
				'norder'=>$king->db->neworder('%s_field','modelid='.$modelid),
				'khelp'=>$data['khelp'],
				'kid1'=>($kid1?$kid1:0),
			);

			if(in_array($data['ntype'],array('10,11')))//当文件上传字段的时候，才可以上传文件
				$_array+=array('ntype'=>1);

			$king->db->insert('%s_field',$_array);
			$_nlog=5;

			$res=$king->db->getRows_one("select modeltable from %s_model where modelid=$modelid;");
			$_modeltable=$res['modeltable'];

			if(in_array($type,$_array_varchar)){//varchar类型
				$king->db->query('alter table %s__'.$king->db->escape($_modeltable).' add k_'.$king->db->escape($data['kfield']).' varchar('.$_array['nsizemax'].') null;');
			}elseif(in_array($type,array(13))){//tinyint
				$king->db->query('alter table %s__'.$king->db->escape($_modeltable).' add k_'.$king->db->escape($data['kfield']).' tinyint(1) not null default 0;');
			}elseif($type==99){
				//组选项不需要字段
			}else{
				$king->db->query('alter table %s__'.$king->db->escape($_modeltable).' add k_'.$king->db->escape($data['kfield']).' text null;');
			}
		}

		$king->cache->del('portal/model/model'.$modelid);
		$king->cache->rd('data/_'.$_modeltable);

		//写log
		$king->log($_nlog,'Field:'.$data['ktitle']);

		kc_goto($king->lang->get('system/goto/is'),'manage.field.php?action=edt&modelid='.$modelid.'&kid1='.$kid1,'manage.field.php?modelid='.$modelid.'&kid1='.$kid1);
	}

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/field'.($kid?'edt':'add'))."({$model['modelname']})",$left,$right,$s);

}








?>