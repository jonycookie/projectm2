<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

/*
清理购物车Cookie
*/
function king_ajax_clear_cookie(){
	global $king;
	setcookie('KingCMS_Cart','',time()+86400000,$king->config('inst'));
	$js="\$.kc_ajax('{URL:\'".$king->config('inst')."portal/cart.php\',CMD:\'buy\',IS:1}')";
	kc_ajax('','','',$js);
}
/*
购物车
*/
function king_ajax_buy(){
	global $king;
	$cart=isset($_COOKIE['KingCMS_Cart']) ? unserialize($_COOKIE['KingCMS_Cart']) :array();
	
	$listid=kc_get('listid',2);

	if($listid){//当有listid和kid值的时候，更新Cookie
		$kid=kc_get('kid',2,1);

		if(kc_post('number')){
			if(!kc_validate(kc_post('number'),2)){
				$js="alert('".$king->lang->get('portal/error/number')."');\$.kc_ajax('{URL:\'".$king->config('inst')."portal/cart.php\',CMD:\'buy\',IS:1}')";
				kc_ajax('','','',$js);
			}
		}

		$num=isset($cart[$listid.'-'.$kid]) ? $cart[$listid.'-'.$kid] :1;
		if(kc_post('number')) $num=kc_post('number');
//		$num=kc_post('number') ? kc_post('number') : $cart[$listid.'-'.$kid];
		$cart[$listid.'-'.$kid]=$num ? $num : 1;
		setcookie('KingCMS_Cart',serialize($cart),time()+86400000,$king->config('inst'));
	}

	if(!$cart){//如果购物车为空，则输出错误提示
		kc_ajax($king->lang->get('system/common/error'),'<p class="k_err">'.$king->lang->get('portal/cart/not').'</p>');
	}

	$s='<table class="k_table_list" cellspacing="0">';
	$s.='<tr><th class="c">ID</th><th>'.$king->lang->get('portal/list/prodname').'</th><th class="c">'.$king->lang->get('portal/list/unitprice').'</th><th>'.$king->lang->get('system/common/number').'</th><th class="c">'.$king->lang->get('system/common/subtotal').'</th><th class="c">'.$king->lang->get('system/common/del').'</th></tr>';

	$weight=0;//总重
	$total=0;//物品费用
	$i=0;

	foreach($cart as $key => $number){
		list($listid,$kid)=explode('-',$key);
		$ID=$king->portal->infoID($listid,$kid);
		
		$s.='<tr><td class="c">'.$kid.'</td><td title="'.addslashes($ID['ktitle']).'">'.kc_substr($ID['ktitle'],0,40).'</td>';
		$s.='<td class="c">'.number_format($ID['nprice'],2).'</td>';
		$s.="<td><input id=\"k_orders_{$i}\" type=\"text\" class=\"k_in w50\" size=\"2\" maxlength=\"6\" value=\"{$number}\" onKeydown=\"if(event.keyCode==13){\$.kc_ajax('{URL:\'".$king->config('inst')."portal/cart.php\',CMD:\'buy\',number:\''+\$(this).val()+'\',listid:{$listid},kid:{$kid}}');}\"/>";
		$s.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{URL:'".$king->config('inst')."portal/cart.php',CMD:'buy',number:\$('#k_orders_{$i}').val(),listid:{$listid},kid:{$kid},IS:1}\">".kc_icon('o7',$king->lang->get('system/common/up'))."</a></td>";
		$s.='<td class="c">'.number_format(($number*$ID['nprice']),2).'</td>';
		$s.="<td class=\"c\"><a href=\"javascript:;\" class=\"k_ajax\" rel=\"{URL:'".$king->config('inst')."portal/cart.php',CMD:'delete_prod',listid:$listid,kid:$kid,IS:1}\">".kc_icon('j2')."</a></td></tr>";

		$weight+=$number*$ID['nweight'];
		$total+=$number*$ID['nprice'];
		$i++;
	}
	$s.='</table><br/>';

	$array=array();
	$express=$king->portal->getExpress();
	$default= isset($_COOKIE['orders_express_default']) ? $_COOKIE['orders_express_default'] : 1;
	$_array=array();
	foreach($express as $eid => $rs){
		$price=$rs['nsprice'] + $rs['niprice'] * ceil($weight>500 ? $weight/500 -1 : 0);
		$array[$eid]=htmlspecialchars($rs['kname']).'('.$price.'元)';
		$_array[$eid]=$price;
	}

	$s.='<script type="text/javascript" charset="UTF-8">'.kc_js2array('K_ORDERS_EXPRESS',$_array).'</script>';
	$s.='<table class="k_table_list" cellspacing="0">';
	$s.='<tr><th class="w150">'.$king->lang->get('portal/list/total').'</th><td>'.number_format($total,2).'<input type="hidden" id="k_orders_total" value="'.$total.'"/></td></tr>';

	if($weight===0){//若重量为0，无需物流
		$s.='<tr><th>'.$king->lang->get('portal/list/selexpress').'</th><td>'.$king->lang->get('portal/list/notexpress').kc_htm_hidden(array('eid'=>0)).'</td></tr>';
		$s.='<tr><th>'.$king->lang->get('portal/list/alltotal').'</th><td>'.number_format($total,2).'</td></tr>';
		$js='';
	}else{
		$s.='<tr><th>'.$king->lang->get('portal/list/selexpress').'</th><td>'.kc_htm_select('eid',$array,$default,"onChange=\"\$('#k_orders_alltotal').text(\$.number_format(\$('#k_orders_total').val()*1+K_ORDERS_EXPRESS[\$(this).val()]*1,2));\$.setCookie('orders_express_default',$(this).val())\"").'</td></tr>';
		$js='$(\'#k_orders_alltotal\').text($.number_format($(\'#k_orders_total\').val()*1 + K_ORDERS_EXPRESS[$(\'#eid\').val()]*1,2));';
		$js.='$.setCookie(\'orders_express_default\',$(\'#eid\').val())';
		$s.='<tr><th>'.$king->lang->get('portal/list/alltotal').'</th><td id="k_orders_alltotal"></td></tr>';
	}

	$s.='</table>';


	$s.='<p>';
	$s.='<a href="javascript:;" class="k_ajax" rel="{URL:\''.$king->config('inst').'portal/cart.php\',CMD:\'buy\',IS:1}">'.kc_icon('o3').$king->lang->get('portal/cart/refresh').'</a>';
	$s.='<a href="javascript:;" class="k_ajax" rel="{URL:\''.$king->config('inst').'portal/cart.php\',CMD:\'clear_cookie\',IS:1}">'.kc_icon('p3').$king->lang->get('portal/cart/clear').'</a>';
	$s.='<a href="javascript:;" class="k_ajax" rel="{URL:\''.$king->config('inst').'portal/cart.php\',CMD:\'orders\',METHOD:\'GET\',IS:1}">'.kc_icon('q3').$king->lang->get('portal/cart/checkout').'</a>';
	$s.='</p>';

	$but=kc_htm_a($king->lang->get('portal/cart/checkout'),'{URL:\''.$king->config('inst').'portal/cart.php\',CMD:\'orders\',METHOD:\'GET\',IS:1}');

	kc_ajax($king->lang->get('portal/title/mycart'),$s,$but,$js,600,350);

}
/*
订单处理
*/
function king_ajax_orders(){
	global $king;

	//显示物流方式选择页，并显示对应的物流费用
	//订单insert到数据库，并返回订单号。以便客户查询订单，也为邮政付款的用户提供收据上传功能
	//清空购物记录

	$king->Load('user');
	$tip= ($user=$king->user->checkLogin()) ? '':'<a href="javascript:;" class="k_user_login">'.$king->lang->get('portal/user/nologin').'</a> <a href="javascript:;" class="k_user_register">'.$king->lang->get('portal/user/regshop').'</a>';

	$array_sql=array('usermail','realname','useraddress','userpost','usertel','kfeedback');

	if($GLOBALS['ismethod']){
		$data=$_POST;
	}else{
		$data=array();
		if(is_array($user)){//用户已登录
			foreach($array_sql as $val){
				$data[$val]=kc_val($user,$val);
			}
		}
	}
	$data=kc_data($array_sql,$data);


	//kconsignee
	$array=array(
		array('realname',0,2,30),
	);
	$s=$king->htmForm($king->lang->get('portal/orders/realname'),kc_htm_input('realname',$data['realname'],30,100),$array,null,$tip);
	//ktel
	$array=array(
		array('usertel',0,6,30),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/tel'),kc_htm_input('usertel',$data['usertel'],30,200),$array);
	//kmail
	$array=array(
		array('usermail',0,6,32),
		array('usermail',5),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/mail'),kc_htm_input('usermail',$data['usermail'],32,200),$array);
	//kaddress
	$array=array(
		array('useraddress',0,5,250),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/address'),'<textarea cols="10" id="useraddress" name="useraddress" rows="3" class="k_in w400">'.htmlspecialchars($data['useraddress']).'</textarea>',$array);
	//kpost
	$array=array(
		array('userpost',0,6,6),
		array('userpost',2),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/post'),kc_htm_input('userpost',$data['userpost'],6,50),$array);
	//kfeedback
	$array=array(
		array('kfeedback',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('portal/orders/feedback'),'<textarea cols="10" rows="4" name="kfeedback" id="kfeedback" class="k_in w400">'.htmlspecialchars($data['kfeedback']).'</textarea>',$array);

	if($GLOBALS['ischeck']){

		$cart=kc_cookie('KingCMS_Cart');
		$eid=kc_post('eid');

		if(!($cart && isset($eid)))
			kc_error($king->lang->get('system/error/param'));

		$weight=0;
		$total=0;
		$nnum=0;
		$cart_array=unserialize($cart);

		//要过滤掉的内容
		$array_black=str_split('<>\'"%');

		foreach($cart_array as $key => $number){
			list($listid,$kid)=explode('-',$key);
			$ID=$king->portal->infoID($listid,$kid);

			if($total===0){//第一次运算
				$mch_name=kc_substr(str_replace($array_black,'',$ID['ktitle']),0,16);
			}

			$weight+=$number*$ID['nweight'];
			$total+=$number*$ID['nprice'];
			$nnum+=$number;
		}

		$nexpress=0;//运费
		if($weight!==0){
			$express=$king->portal->getExpress();
			$nexpress= $express[$eid]['nsprice'] + $express[$eid]['niprice'] *  ceil($weight>500 ? $weight/500 -1 : 0);
		}

		$ono=kc_formatdate(time(),'Ymd').sprintf("%08.0d",$king->db->neworder('%s_orders','','oid'));

		$array=array(
			'kname'=>$mch_name,
			'userid'=>is_array($user) ? $user['userid'] : 0,
			'kcontent'=>$cart,
			'ndate'=>time(),
			'nip'=>kc_getip(),
			'eid'=>$eid,
			'ntotal'=>round($total,2),
			'ono'=>$ono,
			'nnumber'=>$nnum,
			'kfeedback'=>$data['kfeedback'],
			'nweight'=>$weight,
			'nexpress'=>$nexpress,
		);

		foreach($array_sql as $val){
			$array[$val]=kc_val($data,$val);
		}

		$oid=$king->db->insert('%s_orders',$array);

		setcookie('KingCMS_Cart','',-86400000,$king->config('inst'));


		$js="\$.kc_ajax('{URL:\'".$king->config('inst')."portal/cart.php\',CMD:\'payment\',IS:1,oid:{$oid}}')";
		kc_ajax('','','',$js);

	}


	$but=kc_htm_a($king->lang->get('portal/cart/backcart'),"{URL:'".$king->config('inst')."portal/cart.php',CMD:'buy',IS:1}");
	$but.=kc_htm_a($king->lang->get('portal/cart/suborders'),"{URL:'".$king->config('inst')."portal/cart.php',CMD:'orders',eid:".kc_post('eid').",IS:1}");

	kc_ajax($king->lang->get('portal/cart/suborders'),$s,$but,'',600,350 + $GLOBALS['check_num']*15);
}
/*
输出订单号并显示在线付款页面
*/
function king_ajax_payment(){
	global $king;

//	setcookie('KingCMS_Cart',serialize($cart),time()+86400000,$king->config('inst'));

	$oid=kc_get('oid',2,1);
	$array_black=str_split('<>\'"%');

	if(!$data=$king->db->getRows_one("select ono,kname,nnumber,ntotal,kfeedback,eid,nexpress from %s_orders where oid=$oid")){
		kc_error($king->lang->get('system/error/param'));
	}

	$s='<table class="k_table_list" cellspacing="0">';
	$s.='<caption>'.$king->lang->get('portal/cart/prodinfo').'</caption>';
	$s.='<tr><th class="w150">'.$king->lang->get('portal/cart/youorders').'</th><td><strong class="red">'.$data['ono'].'</strong></td></tr>';
	$s.='<tr><th>'.$king->lang->get('portal/cart/prodname').'</th><td>'.$data['kname'].'</td></tr>';
	$s.='<tr><th>'.$king->lang->get('portal/cart/total').'</th><td>'.$data['nnumber'].'件</td></tr>';
	$s.='<tr><th>'.$king->lang->get('portal/cart/alltotal').'</th><td>'.number_format($data['ntotal'],2).'</td></tr>';
	$s.='</table>';

	$s.='<br/>';
	

	$height=0;
	$s.='<table class="k_table_list" cellspacing="0">';
	$s.='<caption>'.$king->lang->get('portal/cart/payment').'</caption>';

	$server_name=$_SERVER['SERVER_NAME'];
	//财付通付款
	if($king->config('tenpaykey','portal') && $king->config('tenpayseller','portal')){
		$height++;

		$s.='<tr><th>'.$king->lang->get('portal/cart/tenpay').'</th><td>';

		$payurl="https://www.tenpay.com/cgi-bin/med/show_opentrans.cgi?";

		$href="attach=".$oid;//利用attach传递oid值
		$href.="&chnid=".$king->config('tenpayseller','portal');
		$href.="&cmdno=12";
		$href.="&encode_type=2";
		$desc=kc_substr(str_replace($array_black,'',$data['kfeedback']),0,32);//留言信息
		$href.=isset($desc{0}) ? "&mch_desc=".$desc : '';//需要过滤<>’”%
		$href.="&mch_name={$data['kname']}";//需要过滤<>’”%
		$href.="&mch_price=".($data['ntotal']*100);
		$href.="&mch_returl=http://".$server_name.$king->config('inst').'api/tenpay.php';//通知URL
		$href.="&mch_type=1";//交易类型1实物交易2虚拟交易
		$href.="&mch_vno=".substr($data['ono'],-12);//交易号
		$href.="&need_buyerinfo=2";
		$href.="&seller=".$king->config('tenpayseller','portal');//收款财付通帐号
		$href.="&show_url=http://".$server_name.$king->config('inst').'portal/tenpay.php';
		//快递信息
		$express=$king->portal->getExpress();
		if(isset($express[$data['eid']])){//有快递信息的时候
			$ename=$express[$data['eid']]['kname'];
			$href.=isset($ename{0}) ? "&transport_desc=".$ename : '';
		}

		$href.="&transport_fee=" . ($data['nexpress']*100);//物流费用
		$href.="&version=2";

		$md5_sign=strtoupper(md5($href.'&key='.$king->config('tenpaykey','portal')));
		$href.="&sign=".$md5_sign;
		$href=$payurl.$href;

		$s.='<p><a href="'.$href.'" target="_blank"><img src="'.$king->config('inst').'portal/images/tenpay.gif"/></a></p>';

		$s.='</td></tr>';
		
	}

	//支付宝付款
	if($king->config('alipayregmail','portal') && $king->config('alipaypartner','portal') && $king->config('alipaykey','portal')){
		$height++;

		$payurl="http://www.alipay.com/cooperate/gateway.do?";
		$body=kc_substr($data['kfeedback'],0,200);//留言信息

		switch($data['eid']){
			case 1:$logistics_type='EMS';break;//这是默认的方案,客户自行改动则没办法。
			case 2:$logistics_type='POST';break;
			default:$logistics_type='EXPRESS';
		}

		$hrf=array();
		$hrf['_input_charset']='utf-8';//编码
		$hrf['service']='trade_create_by_buyer';//接口名称
		$hrf['seller_email']=$king->config('alipayregmail','portal');//支付宝注册邮箱
		$hrf['partner']=$king->config('alipaypartner','portal');//商户ID
		$hrf['payment_type']=1;//支付类型

		$hrf['out_trade_no']=$data['ono'];//订单号
		$hrf['subject']=$data['kname'];//商品名称
		if(isset($body{0})) $hrf['body']=$body;//内容
		$hrf['price']=number_format($data['ntotal'],2);//单价
		$hrf['quantity']=1;//数量

		$hrf['logistics_type']=$logistics_type;//物流类型
		$hrf['logistics_payment']='BUYER_PAY';//由谁负责物流费用
		$hrf['logistics_fee']=number_format($data['nexpress'],2);//物流费用

		$hrf['notify_url']='http://'.$server_name.'/api/alipay.php';//通知URL

		ksort($hrf);

		$href=$payurl;
		$hrf_sign=array();
		foreach($hrf as $key => $val){
			$hrf_sign[]=$key.'='.$val;
			$href.=$key.'='.urlencode($val).'&';
		}

		$sign=md5(implode('&',$hrf_sign).$king->config('alipaykey','portal'));//生成签名

		$href.="sign={$sign}&sign_type=MD5";

		$s.='<tr><th>'.$king->lang->get('portal/cart/alipay').'</th><td>';

		$s.='<p><a href="'.$href.'" target="_blank"><img src="'.$king->config('inst').'portal/images/alipay.gif"/></a></p>';
		
	}
	//银行汇款
	$s.='<tr><th class="w150">'.$king->lang->get('portal/const/transfer').'</th><td>';
	$s.='<p>';
	$s.='<a href="'.$king->config('inst').'portal/cart.php?action=bank&oid='.$oid.'" target="_blank"><img src="'.$king->config('inst').'portal/images/bank.gif"/></a><br/>';
	$s.='<a href="javascript:;" rel="{URL:\''.$king->config('inst').'portal/cart.php\',CMD:\'transfer\',IS:1}" class="k_ajax">'.$king->lang->get('portal/orders/viewmethod').'</a>';
	$s.='</p>';
	$s.='</td></tr>';
	$s.='</table>';
	kc_ajax($king->lang->get('portal/cart/myorders'),$s,0,'',500,310+($height*50));

}

/*
显示付款方式
*/
function king_ajax_transfer(){
	global $king;
	$s=$king->config('transfer','portal');

	kc_ajax($king->lang->get('portal/cart/paymode'),$s,0,'',500,350);
}

function king_ajax_delete_prod(){
	global $king;
	$listid=kc_get('listid',2,1);
	$kid=kc_get('kid',2,1);

	$cart=$_COOKIE['KingCMS_Cart'] ? unserialize($_COOKIE['KingCMS_Cart']) :array();
	$cart=array_diff_key($cart,array($listid.'-'.$kid=>''));
	setcookie('KingCMS_Cart',serialize($cart),time()+86400000,$king->config('inst'));

	$js="\$.kc_ajax('{URL:\'".$king->config('inst')."portal/cart.php\',CMD:\'buy\',IS:1}')";
	kc_ajax('','','',$js);

}
/**
	处理订单反馈信息页
*/
function king_def(){

	echo "这个页面做会员订单查询?";
}

/**
	上传付款凭证
*/
function king_bank(){
	global $king;

	$oid=kc_get('oid',2,1);

	if(!$rs=$king->db->getRows_one("select ono,kname,nnumber,ntotal,kfeedback,eid,nexpress,userid,nstatus from %s_orders where oid=$oid")){
		kc_error($king->lang->get('system/error/param'));
	}

	if($rs['userid']>0){
		$king->Load('user');
		$king->user->access();//如果有记录用户，则做登录验证
		if($king->user->userid!=$rs['userid']){
			$king->portal->error($king->lang->get('system/common/error'),$king->lang->get('portal/error/cart'));
		}
	}
	if((int)$rs['nstatus']!==2){
		$king->portal->error($king->lang->get('system/common/error'),$king->lang->get('portal/error/status'));
	}

	$s='<table class="k_table_list" cellspacing="0">';
	$s.='<caption>'.$king->lang->get('portal/cart/prodinfo').'</caption>';
	$s.='<tr><th class="w150">'.$king->lang->get('portal/cart/youorders').'</th><td><strong class="red">'.$rs['ono'].'</strong></td>';
	$s.='<th>'.$king->lang->get('portal/cart/prodname').'</th><td>'.$rs['kname'].'</td></tr>';
	$s.='<tr><th>'.$king->lang->get('portal/cart/total').'</th><td>'.$rs['nnumber'].'件</td>';
	$s.='<th>'.$king->lang->get('portal/cart/alltotal').'</th><td>'.number_format($rs['ntotal'],2).'</td></tr>';
	$s.='</table>';

	if($_FILES){
		$ext=strtolower(kc_f_ext($_FILES['bankfile']['name']));
		kc_f_md($king->config('uppath')."/orders");
		if(!in_array($ext,array('jpg','jpeg'))){
			$s.='<p class="k_error">'.$king->lang->get('portal/error/ext').'</p>';//提示文件类型不正确
			$s.='<p><a href="cart.php?action=bank&oid='.$oid.'">'.$king->lang->get('portal/cart/reup').'</a></p>';
		}elseif(move_uploaded_file($_FILES['bankfile']['tmp_name'],ROOT.$king->config('uppath')."/orders/$oid.jpg")){
			$s.='<p>'.$king->lang->get('portal/cart/upok').'</p>';
			$array=array(
				'paymethod'=>'bank',
			);
			$king->db->update('%s_orders',$array,"oid=$oid");
		}else{
			$s.='<p class="k_error">'.$king->lang->get('portal/error/upbank').'</p>';
		}
	}else{
		$s.=$king->openForm('cart.php?action=bank',null,1);
		$s.='<p>'.$king->lang->get('portal/cart/bankmemo').'</p>';
		$s.='<p>'.$king->lang->get('portal/cart/bankmemo1').'</p>';
		$s.=$king->htmForm($king->lang->get('portal/cart/upbank'),"<input type=\"file\" name=\"bankfile\" class=\"k_in w400\" />");
		$hide=array(
			'oid'=>$oid,
			'MAX_FILE_SIZE'=>204800,
		);
		$s.=kc_htm_hidden($hide);
		$s.=$king->closeForm($king->lang->get('system/common/upfile'));
	}



	$tmp=new KC_Template_class($king->config('templateorders','portal'));

	$tmp->assign('oid',$oid);
	$tmp->assign('title',$king->lang->get('portal/cart/upbank'));
	$tmp->assign('nav',$king->lang->get('portal/cart/upbank'));
	$tmp->assign('type','edit');
	$tmp->assign('inside',$s);

	echo $tmp->output();


}
?>