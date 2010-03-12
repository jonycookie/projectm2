<?php

define('KC_INDEX',True);

require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_def(){
	global $king;

	$fields=array('notify_type','notify_id','notify_time','trade_no','out_trade_no','subject','body','price','discount','quantity'
	,'total_fee','payment_type','use_coupon','coupon_discount','is_total_fee_adjust','trade_status','refund_status','logistics_status'
	,'logistics_type','logistics_fee','logistics_payment','receive_name','receive_address','receive_zip','receive_phone','receive_mobile'
	,'seller_email','seller_id','buyer_id','buyer_email','gmt_create','gmt_payment','gmt_send_goods','gmt_refund','gmt_close','gmt_logistics_modify');

	natsort($fields);


	$array=array();
	foreach($fields as $val){
		if(isset($_POST[$val])){//首先这些值需要先存在
			$array[]=$val.'='.$_POST[$val];
		}
	}
	if(is_array($array)){//万一不是数组就得输出错误提示
		$sign=md5(implode('&',$array).$king->config('alipaykey','portal'));
		if($sign!=kc_post('sign')){
			exit('fail');
		}
	}else{
		exit('fail');
	}

	/* 这里开始写数据更新过程 */
	switch(kc_post('trade_status')){
		case 'WAIT_BUYER_PAY':$trade_status=1;break;
		case 'WAIT_SELLER_SEND_GOODS':$trade_status=3;break;
		case 'WAIT_BUYER_CONFIRM_GOODS':$trade_status=4;break;
		case 'TRADE_FINISHED':$trade_status=5;break;
		case 'TRADE_CLOSED':$trade_status=10;break;
		case 'modify.tradeBase.totalFee':$trade_status=11;break;
	}
	if(isset(kc_post('refund_status'))){
		switch(kc_post('refund_status')){
			case 'WAIT_SELLER_AGREE':$trade_status=8;break;
			case 'REFUND_SUCCESS':$trade_status=9;break;
			case 'REFUND_CLOSED':$trade_status=10;break;
		}
	}
	$array=array(
		'nstatus'=>$trade_status,//交易状态
		'tid'=>kc_post('trade_no'),//财付通的交易id
		'buyer_id'=>kc_post('buyer_email'),//买家的付款帐号
		'seller'=>kc_post('seller_email'),//卖家的收款帐号
		'paymethod'=>'alipay',//支付方式
		//补充modeltable中的nnumber数量扣除
	);
	$ono=kc_get('out_trade_no',2,1);
	$king->db->update('%s_orders',$array,"ono='$ono'");


	exit('success');
}


?>