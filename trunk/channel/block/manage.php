<?php require_once '../global.php';

function king_ajax_edt(){
	global $king;
	$king->access('block_edt');

	$kid=isset($_POST['kid']) ? kc_post('kid',2) : '';
	$kname=kc_post('kname');
	$kcontent=kc_post('kcontent');

	//check kname
	if(!isset($kname{0})){
		kc_error($king->lang->get('block/error/name',0));
	}else{
		if(False!==strpos($kname,"'") || False!==strpos($kname,'"')){
			kc_error($king->lang->get('block/error/name',1));
		}
		//kid为空的时候验证重复值
		if(empty($kid)){
			if($king->db->getRows_one("select kid from %s_block where kname='".$king->db->escape($kname)."' and kid1=0")){
				kc_error($king->lang->get('block/error/name',2));
			}
		}else{
			if($king->db->getRows_one("select kid from %s_block where kname='".$king->db->escape($kname)."' and kid1=0 and kid<>$kid")){
				kc_error($king->lang->get('block/error/name',2));
			}
		}
	}
	//check kcontent
	if(!isset($kcontent{0})){
		kc_error($king->lang->get('block/error/name',3));
	}

	if(empty($kid)){
		$array=array(
			'kname'=>$kname,
			'kcontent'=>$kcontent,
			'norder'=>$king->db->neworder('%s_block'),
		);

		$kid=$king->db->insert('%s_block',$array);

		kc_ajax('OK','<p class="k_ok">'.$king->lang->get('block/ok/add').'</p>'
		,"<a href=\"manage.php?action=edt&kid=$kid\">".$king->lang->get('system/common/enter')."</a>");//添加成功后返回的地址
		
	}else{
		$array=array(
			'kname'=>$kname,
			'kcontent'=>$kcontent,
		);
		$king->db->update('%s_block',$array,"kid=$kid");

		$array=array(
			'kname'=>$kname,
		);
		$king->db->update('%s_block',$array,"kid1=$kid");//更新子项目的kname

		kc_ajax('OK','<p class="k_ok">'.$king->lang->get('block/ok/edt').'</p>',0);//编辑成功后无需跳转

	}


}

function king_ajax_bind_edt(){
	global $king;
	$king->access('block_edt');

	$ntype=kc_post('ntype',2,1);
	$bid=kc_post('bid');
	$kcontent=kc_post('kcontent');
	$kid=kc_post('kid');
	$kid1=kc_post('kid1',2,1);
	//bid
	if(!isset($bid{0}))
		kc_error($king->lang->get('block/error/bid',0));
	if(!kc_validate($bid,2))
		kc_error($king->lang->get('block/error/bid',1));
	//kcontent
	if(!isset($kcontent{0})){
		kc_error($king->lang->get('block/error/name',3));
	}


/**
	补充相同验证 ntype bid
*/
	if(empty($kid)){//insert

		//验证重复
		if($king->db->getRows_one("select kid from %s_block where kid1=$kid1 and ntype=$ntype and bid=$bid"))
			kc_error($king->lang->get('block/error/bind'));


		$block=$king->block->infoBlock($kid1);
		$array=array(
			'kname'=>$block['kname'],
			'kcontent'=>$kcontent,
			'kid1'=>$kid1,
			'ntype'=>$ntype,
			'bid'=>$bid,
			'norder'=>$king->db->neworder('%s_block'),
		);
		$king->db->insert('%s_block',$array);

		$cmd='add';$url="<a href=\"manage.php?action=edt&kid=$kid1\">".$king->lang->get('system/common/enter')."</a>";

	}else{
		//kid
		$kid=kc_post('kid',2,1);

		//验证重复
		if($king->db->getRows_one("select kid from %s_block where kid1=$kid1 and ntype=$ntype and bid=$bid and kid<>$kid"))
			kc_error($king->lang->get('block/error/bind'));

		$array=array(
			'kcontent'=>$kcontent,
			'ntype'=>$ntype,
			'bid'=>$bid,
		);
		$king->db->update('%s_block',$array,"kid=$kid");

		$king->cache->del("block/info/$kid1");

		$cmd='edt';$url=0;

	}

	kc_ajax('OK','<p class="k_ok">'.$king->lang->get("block/ok/$cmd").'</p>',$url);//编辑成功后返回的地址


}
function king_ajax_delete(){
	global $king;
	$king->access('block_delete');

	$list=kc_getlist();

	$king->db->query("delete from %s_block where kid in ($list) or kid1 in ($list)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

}
function king_ajax_updown(){
	global $king;
	$king->access('block_updown');

	$kid=kc_get('kid',2,1);
	$king->db->updown('%s_block',$kid);

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
		$left['edt']=array(
			'href'=>'manage.php?action=edt&kid='.$_GET['kid'],
			'ico'=>'p8',
			'title'=>$king->lang->get('system/common/view'),
		);
	}
	$left[isset($_GET['kid']) ? 'view' : 'edt']=array(
		'href'=>'manage.php?action=edt',
		'ico'=>'p8',
		'title'=>$king->lang->get('system/common/add'),
	);

	return array($left,array());

}

function king_def(){
	global $king;
	$king->access('block');

	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"manage.php?action=edt&kid='+K[0]+'\">'+\$.kc_icon('q7','".addslashes($king->lang->get('system/common/edit'))."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+\$.kc_icon('q8','".addslashes($king->lang->get('system/common/del'))."')+'</a>'";
	$manage.="+\$.kc_updown(K[0])";

	$_js=array(
		"\$.kc_list(K[0],K[1],'manage.php?action=edt&kid='+K[0])",
		$manage,
		"'{king:block name=\''+K[1]+'\' /}'",
	);

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?pid=PID&rn=RN',$king->db->getRows_number('%s_block',"bid=0 and ntype=0")));

	$_sql="select kid,kname from %s_block where kid1=0 order by norder desc,kid desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$s.="ll('".$king->lang->get('block/list/name')."','manage','<i>".$king->lang->get('block/list/tag')."</i>',1);";

	foreach($res as $rs){
		$s.="ll({$rs['kid']},'".addslashes($rs['kname'])."',0);";
	}


	$s.=$king->closeList();

	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('block/title/center'),$left,$right,$s);
}
/**
	添加/编辑碎片
*/
function king_edt(){
	
	global $king;
	$king->access('block_edt');

	$kid=kc_get('kid',2);
	$sql="kid,kname,kcontent,ntype,bid";

	if(!$res=$king->db->getRows("select $sql from %s_block where kid=$kid"))
		$res=array();

	$array_type=array(
		1=>$king->lang->get('block/common/list'),
		2=>$king->lang->get('block/common/model'),
		3=>$king->lang->get('block/common/site'),
	);


	if(empty($kid)){//add
		$s=$king->openForm('',$king->lang->get('block/th/add'),'block_add');

		$s.=$king->htmForm($king->lang->get('block/label/name'),kc_htm_input('kname','',100,400));
		$s.=$king->htmForm($king->lang->get('block/label/content'),kc_htm_textarea('kcontent'));
		$s.=$king->htmForm(null,kc_htm_button($king->lang->get('system/common/add'),"\$.kc_ajax({CMD:'edt',FORM:'block_add'});",1));
		$s.=$king->closeForm('none');

	}else{//edit
		if(!$rs=$king->db->getRows_one("select $sql from %s_block where kid=$kid order by norder asc"))
			kc_error($king->lang->get('system/error/notrecord'));

		$s=$king->openForm('',$king->lang->get('block/th/default'),'block_edt');
		$but=" <input type=\"button\" value=\"".$king->lang->get('system/common/up')."\" onClick=\"\$.kc_ajax({CMD:'edt',kid:$kid,FORM:'block_edt'});\" />";
		$s.=$king->htmForm($king->lang->get('block/label/name'),kc_htm_input('kname',$rs['kname'],100,400).$but);
		$s.=$king->htmForm($king->lang->get('block/label/content'),kc_htm_textarea('kcontent',$rs['kcontent']));
		$s.=$king->closeForm('none');

		if(!$res=$king->db->getRows("select $sql from %s_block where kid1=$kid order by norder desc"))
			$res=array();

		foreach($res as $i=>$rs){
			$s.=$king->openForm('','','block_bind_'.$i);
			$b=kc_htm_select('ntype',$array_type,$rs['ntype']);
			$b.=" <span><label>".$king->lang->get('block/label/bid')."</label></span>";
			$b.=kc_htm_input('bid',$rs['bid'],10,50);
			$b.=" <input type=\"button\" value=\"".$king->lang->get('system/common/up')."\" onClick=\"\$.kc_ajax({CMD:'bind_edt',kid:{$rs['kid']},kid1:$kid,FORM:'block_bind_$i'});\" />";

			$s.=$king->htmForm($king->lang->get('block/label/bind'),$b);
			$s.=$king->htmForm($king->lang->get('block/label/content'),kc_htm_textarea('kcontent',$rs['kcontent']));
			$s.=$king->closeForm('none');
		}
		//添加
		$s.=$king->openForm('',$king->lang->get('block/th/bindadd'),'block_bind_edt');
		$b=kc_htm_select('ntype',$array_type,1);
		$b.=" <span><label>".$king->lang->get('block/label/bid')."</label></span>";
		$b.=kc_htm_input('bid','',10,50);
		$b.=kc_help('block/help/bind');
		$s.=$king->htmForm($king->lang->get('block/label/bind'),$b);
		$s.=$king->htmForm($king->lang->get('block/label/content'),kc_htm_textarea('kcontent'));
		$s.=$king->htmForm(null,kc_htm_button($king->lang->get('system/common/add'),"\$.kc_ajax({CMD:'bind_edt',FORM:'block_bind_edt',kid1:$kid});",1));
		$s.=$king->closeForm('none');
	}


	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('block/title/center'),$left,$right,$s);
}


?>