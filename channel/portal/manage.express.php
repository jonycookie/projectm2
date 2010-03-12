<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

/**
	删除
*/
function king_ajax_delete(){
	global $king;
	$king->access('portal_express_delete');

	$list=kc_getlist();

	$king->db->query("delete from %s_express where eid in ($list)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

}

/**
	编辑
*/
function king_ajax_edt(){
	global $king;

	$king->access('portal_express_edt');

	$eid=kc_get('eid',2);

	$sql="kname,nsprice,niprice,kremark,kaddress";
	$array_sql=explode(',',$sql);
	if($GLOBALS['ismethod'] || empty($eid)){
		$data=$_POST;
		if(!$GLOBALS['ismethod']){
			$data['kaddress']='http://';
		}
	}else{
		if(!$data=$king->db->getRows_one("select $sql from %s_express where eid=$eid"))
			kc_error($king->lang->get('system/error/notre'));
	}
	$data=kc_data($array_sql,$data);
	//kname
	$array=array(
		array('kname',0,1,50),
	);
	$s=$king->htmForm($king->lang->get('portal/express/name'),kc_htm_input('kname',$data['kname'],50,200),$array);
	//nsprice
	$array=array(
		array('nsprice',0,1,5),
		array('nsprice',2),
	);
	$s.=$king->htmForm($king->lang->get('portal/express/sprice'),kc_htm_input('nsprice',$data['nsprice'],5,50),$array);
	//nsprice
	$array=array(
		array('niprice',0,1,5),
		array('niprice',2),
	);
	$s.=$king->htmForm($king->lang->get('portal/express/iprice'),kc_htm_input('niprice',$data['niprice'],5,50),$array);
	//kaddress
	$array=array(
		array('kaddress',0,1,255),
		array('kaddress',6),
	);
	$s.=$king->htmForm($king->lang->get('portal/express/address'),kc_htm_input('kaddress',$data['kaddress'],255,400),$array);
	//kremark
	$array=array(
		array('kremark',0,0,3000),
	);
	$s.=$king->htmForm($king->lang->get('portal/common/remark'),'<textarea name="kremark" id="kremark" rows="6" cols="100" class="k_in w400">'.htmlspecialchars($data['kremark']).'</textarea>',$array);

	if($GLOBALS['ischeck']){
		$array=array();
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}
		if(empty($eid)){//insert
			$king->db->insert('%s_express',$array);
		}else{
			$king->db->update('%s_express',$array,"eid=$eid");
		}

		$js='setTimeout("parent.location=\'manage.express.php\'",1000)';
		kc_ajax('','','',$js);
	}

	$but=kc_htm_a($king->lang->get('system/common/save'),"{CMD:'edt',eid:'$eid',IS:1}");
	kc_ajax($king->lang->get('portal/title/expressedt'),$s,$but,'',440,350+$GLOBALS['check_num']*15);


}


function king_def(){
	global $king;
	$king->access('portal_express');

	$sql="eid,kname,nsprice,niprice";
	if(!$res=$king->db->getRows("select $sql from %s_express order by norder desc"))
		$res=array();
	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'edt\',eid:'+K[0]+',METHOD:\'GET\'}\">'+\$.kc_icon('p6','".$king->lang->get('system/common/edit')."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+\$.kc_icon('l6','".$king->lang->get('system/common/del')."')+'</a>'";
	$_js=array(
		"\$.kc_list(K[0],K[1],'{CMD:\'edt\',eid:'+K[0]+',METHOD:\'GET\'}')",
		$manage,
		"\$.kc_icon('q1')+K[2]",
		"\$.kc_icon('q1')+K[3]",
	);

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.express.php?pid=PID&rn=RN',$king->db->getRows_number('%s_express')));

	$s.="ll('".$king->lang->get('portal/express/name')."','manage','".$king->lang->get('portal/express/sprice')."','".$king->lang->get('portal/express/iprice')."',1);";

	foreach($res as $key => $rs){
		$s.="ll({$rs['eid']},'{$rs['kname']}','".number_format($rs['nsprice'],2)."','".number_format($rs['niprice'],2)."',0);";
	}
	$s.=$king->closeList();


	$left=array(
		''=>array(
			'href'=>'manage.express.php',
			'ico'=>'j6',
			'title'=>$king->lang->get('system/common/list'),
		),
		'edt'=>array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'edt\',METHOD:\'GET\'}',
			'ico'=>'k6',
			'title'=>$king->lang->get('system/common/add'),
		),
	);
	$right=array(
		array('href'=>'manage.orders.php','title'=>$king->lang->get('portal/title/orders'),'ico'=>'q5'),
		array('href'=>'manage.php','title'=>$king->lang->get('portal/title/list'),'ico'=>'a1'),
	);
	
	$king->skin->output($king->lang->get('portal/title/express'),$left,$right,$s);

}
?>