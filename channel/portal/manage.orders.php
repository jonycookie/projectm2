<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_ajax_select(){
	global $king;

	$s="<label>宝贝名称:</label>".kc_htm_input('kname','',50,100);
	$s.="<label>成交时间:</label>";

	kc_ajax_query($s);

}

function king_ajax_delete(){
	global $king;

	$king->access('portal_orders_delete');

	$list=kc_getlist();
	$array=explode(',',$list);

	$king->db->query("delete from %s_orders where oid in ($list)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}
/**
	发货
*/
function king_ajax_express(){
	global $king;
	$king->access('portal_orders_delivery');

	$oid=kc_get('oid',2,1);

	$sql="eid,expressnumber,kremark,nsenddate";
	$array_sql=explode(',',$sql);
	if($GLOBALS['ismethod']){
		$data=$_POST;
	}else{
		if(!$data=$king->db->getRows_one("select $sql from %s_orders where oid=$oid"))
			kc_error($king->lang->get('system/error/notre'));
	}
	$data=kc_data($array_sql,$data);

	$s='';
	//eid
	$express=$king->portal->getExpress();
	$array_express=array();
	foreach($express as $eid => $rs){
		$array_express[$eid]=htmlspecialchars($rs['kname']);
	}
	$array=array(
		array('eid',0,1,11),
		array('eid',2),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/express'),kc_htm_select('eid',$array_express,$data['eid']),$array);

	//expressnumber
	$array=array(
		array('expressnumber',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/express/expressnumber'),kc_htm_input('expressnumber',$data['expressnumber'],30,200),$array);
	//kremark
	$array=array(
		array('kremark',0,0,3000),
	);
	$s.=$king->htmForm($king->lang->get('portal/common/remark'),'<textarea name="kremark" id="kremark" rows="8" cols="100" class="k_in w400">'.htmlspecialchars($data['kremark']).'</textarea>',$array);


	if($GLOBALS['ischeck']){
		$array=array();
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}
		if(empty($data['nsenddate'])) $array['nsenddate']=time();//如果nsenddate为空，则填写当前时间戳
		$array['nstatus']=4;//交易状态设置为发货

		$king->db->update('%s_orders',$array,"oid=$oid");

		$js='setTimeout("parent.location=\'manage.orders.php\'",1000)';
		kc_ajax('','','',$js);
	}



	$but=kc_htm_a($king->lang->get('portal/common/delivery'),"{CMD:'express',oid:'$oid',IS:1,nsenddate:'{$data['nsenddate']}'}");
	kc_ajax($king->lang->get('portal/title/delivery'),$s,$but,'',440,290+$GLOBALS['check_num']*15);



}
function king_def(){
	global $king,$action;

	$king->access("portal_orders");

	switch($action){
		case '':
			$time=time()-86400 * 30;//最近一个月
			$where="ndate>$time";break;
		case 'paid':$where="nstatus=3";break;
		case 'all':$where="";break;
	}
	$sql_where=isset($where{0}) ? " where $where" : '';

	$_sql="select oid,ono,nstatus,kname,userid,nnumber,nip,ndate,paymethod,buyer_id,ntotal,nexpress from %s_orders {$sql_where} order by oid desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	//准备开始列表
	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'express\',oid:'+K[0]+',METHOD:\'GET\'}\">'+\$.kc_icon('j6','".$king->lang->get('portal/express/pub')."')+'</a>'";
	$manage.="+'<a href=\"manage.orders.php?action=edt&oid='+K[0]+'\">'+\$.kc_icon('p4','".$king->lang->get('system/common/edit')."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+\$.kc_icon('p3','".$king->lang->get('system/common/del')."')+'</a>'";
	$_js=array(
		"\$.kc_list(K[0],K[1],'manage.orders.php?action=edt&oid='+K[0])",
		$manage,
		"'<i class=\"c'+K[2]+'\">'+orders_status[K[2]]+'</i>'",
		"'<i>'+K[3]+'</i>'",
		"'<i>'+K[4]+'</i>'",
		"K[5]",
		"'<b>'+K[7]+'</b>'",
		"'<b>'+K[8]+'</b>'",
		"'<b>'+K[9]+'</b>'",
		"K[6]",
	);

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.orders.php?pid=PID&rn=RN',$king->db->getRows_number('%s_orders',$where)));
	$status=array();
	for($i=1;$i<=11;$i++){
		$status[$i]=$king->lang->get("portal/orders/status/s$i");
	}
	$s.=kc_js2array('orders_status',$status);

	$s.="ll('".$king->lang->get('portal/orders/no')."','manage','<i>".$king->lang->get('portal/orders/statu')."</i>','".$king->lang->get('portal/orders/name')."','<i>".$king->lang->get('portal/orders/number')."</i>','".$king->lang->get('portal/orders/paymethod')."','<b>".$king->lang->get('portal/orders/prod')."</b>','<b>".$king->lang->get('portal/orders/express')."</b>','<b>".$king->lang->get('portal/orders/total')."</b>','".$king->lang->get('portal/orders/date')."',1);";

	foreach($res as $rs){//td
		$s.='ll('.$rs['oid'].',\''.$rs['ono'].'\',\''.$rs['nstatus'].'\',\''.$rs['kname'].'\','.$rs['nnumber'].',\''. ($rs['paymethod'] ? $king->lang->get('portal/orders/method/'.$rs['paymethod']) : '--') .'\',\''.kc_formatdate($rs['ndate']).'\',\''.number_format($rs['ntotal'],2).'\',\''.number_format($rs['nexpress'],2).'\',\''.number_format($rs['ntotal']+$rs['nexpress'],2).'\',0);';
	}

	//结束列表
	$s.=$king->closeList();


	$left=array(
		''=>array(
			'href'=>'manage.orders.php',
			'ico'=>'q5',
			'title'=>$king->lang->get('portal/title/ordersdef'),
			),
		'paid'=>array(
			'href'=>'manage.orders.php?action=paid',
			'ico'=>'q6',
			'title'=>$king->lang->get('portal/title/orderspaid'),
		),
		'all'=>array(
			'href'=>'manage.orders.php?action=all',
			'ico'=>'q4',
			'title'=>$king->lang->get('portal/title/ordersall'),
		),
/*
		array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'select\'}',
			'ico'=>'m1',
			'title'=>$king->lang->get('system/common/search'),
		),
*/
	);
	$right=array(
		array('href'=>'manage.express.php','title'=>$king->lang->get('portal/title/express'),'ico'=>'j6'),
		array('href'=>'manage.php','title'=>$king->lang->get('portal/title/list'),'ico'=>'a1'),
	);
	
	$king->skin->output($king->lang->get('portal/title/orders'),$left,$right,$s);
}
function king_paid(){
	call_user_func('king_def');
}
function king_all(){
	call_user_func('king_def');
}

/**
	编辑详细信息
*/
function king_edt(){
	global $king;
	$king->access('portal_orders_edt');

	$oid=kc_get('oid',2,1);
	$sql="kname,nstatus,realname,useraddress,userpost,usertel,usermail,ntotal,nexpress,kremark";

	if($GLOBALS['ismethod']){
		$data=$_POST;
	}else{
		if(!$data=$king->db->getRows_one("select $sql,ono,userid,kcontent from %s_orders where oid=$oid"))
			kc_error($king->lang->get('system/error/notre'));
	}


	$s=$king->openForm('manage.orders.php?action=edt',$king->lang->get('portal/orders/odinfo'));
	//ono
	$s.=$king->htmForm($king->lang->get('portal/orders/no'),kc_htm_input('ono',$data['ono'],16,150,'readonly="true"'));
	//kname
	$array=array(
		array('kname',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/name'),kc_htm_input('kname',$data['kname'],30,300),$array);
	//nstatus
	$array_statu=array();
	for($i=1;$i<=11;$i++){
		$array_statu[$i]=$king->lang->get("portal/orders/status/s$i");
	}
	$array=array(
		array('nstatus',2),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/statu'),kc_htm_select('nstatus',$array_statu,$data['nstatus']),$array);
	//ntotal
	$array=array(
		array('ntotal',3),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/total'),kc_htm_input('ntotal',$data['ntotal'],14,100).$king->lang->get('portal/common/y'),$array);
	//nexpress
	$array=array(
		array('nexpress',3),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/express'),kc_htm_input('nexpress',$data['nexpress'],14,100).$king->lang->get('portal/common/y'),$array);
	//nalltotal
	$s.=$king->htmForm($king->lang->get('portal/list/alltotal'),'<span>'.number_format($data['ntotal']+$data['nexpress'],2).$king->lang->get('portal/common/y').'</span>');;

	$s.=$king->splitForm();

	$contents=unserialize($data['kcontent']);
	$list="<table class=\"k_side\">";
	foreach($contents as $key => $num){
		list($listid,$kid)=explode('-',$key);
		$info=$king->portal->infoList($listid);
		$id=$king->portal->infoID($listid,$kid);
		$kpath=$king->portal->pathPage($info,$id['kid'],$id['kpath']);
		$list.="<tr><td><a target=\"_blank\" href=\"manage.content.php?action=edt&listid=$listid&kid=$kid\">".kc_icon('e5',$king->lang->get('system/common/edit'))."</a>";
		$list.="<a target=\"_blank\" href=\"{$kpath}\">{$id['ktitle']}</a></td>";
		$list.="<td>".kc_icon('q1').number_format($id['nprice'],2)." x {$num}</tr>";
	}
	$list.="</table>";
	$s.=$king->htmForm($king->lang->get('portal/orders/value'),$list);

	$s.=$king->splitForm($king->lang->get('portal/orders/userinfo'));

	$king->Load('user');
	$user=$king->user->infoUser($data['userid']);
	$s.=$king->htmForm($king->lang->get('portal/user/name'),$user['username']);
	//realname
	$array=array(
		array('realname',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/realname'),kc_htm_input('realname',$data['realname'],30,100),$array);
	//useraddress
	$array=array(
		array('useraddress',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/address'),'<textarea name="useraddress" id="useraddress" rows="4" cols="100" class="k_in w400">'.htmlspecialchars($data['useraddress']).'</textarea>',$array);
	//userpost
	$array=array(
		array('userpost',0,6,6),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/post'),kc_htm_input('userpost',$data['userpost'],6,100),$array);
	//usertel
	$array=array(
		array('usertel',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/tel'),kc_htm_input('usertel',$data['usertel'],30,200),$array);
	//usermail
	$array=array(
		array('usermail',0,5,32),
		array('usermail',5),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/mail'),kc_htm_input('usermail',$data['usermail'],32,200),$array);

	$s.=$king->splitForm($king->lang->get('portal/common/remark'));

	//kremark
	$array=array(
		array('kremark',0,0,3000),
	);
	$s.=$king->htmForm($king->lang->get('portal/common/remark'),'<textarea name="kremark" id="kremark" rows="8" cols="100" class="k_in w400">'.htmlspecialchars($data['kremark']).'</textarea>',$array);
	//隐藏域
	$s.=kc_htm_hidden(array(
		'oid'=>$oid,
		'userid'=>$data['userid'],
		'ono'=>$data['ono'],
		'kcontent'=>$data['kcontent'],
	));

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){
		$array_sql=explode(',',$sql);
		$array=array();
		foreach($array_sql as $val){
			$array[$val]=$data[$val];
		}
		$king->db->update('%s_orders',$array,"oid=$oid");

		kc_goto($king->lang->get('system/goto/saveok'),"manage.orders.php?action=edt&oid=$oid");
	}

	$left=array(
		''=>array(
			'href'=>'manage.orders.php',
			'ico'=>'q5',
			'title'=>$king->lang->get('portal/title/ordersdef'),
			),
		'paid'=>array(
			'href'=>'manage.orders.php?action=paid',
			'ico'=>'q6',
			'title'=>$king->lang->get('portal/title/orderspaid'),
		),
		'all'=>array(
			'href'=>'manage.orders.php?action=all',
			'ico'=>'q4',
			'title'=>$king->lang->get('portal/title/ordersall'),
		),
		'edt'=>array(
			'href'=>'manage.orders.php?action=edt&oid='.$oid,
			'ico'=>'e7',
			'title'=>$king->lang->get('system/common/edit'),
		),
	);
	$right=array(
		array('href'=>'manage.php','title'=>$king->lang->get('portal/title/list'),'ico'=>'a1'),
	);
	
	$king->skin->output($king->lang->get('portal/title/orders'),$left,$right,$s);


}


?>