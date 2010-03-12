<?php

define('KC_INDEX',True);

require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

/*
财付通通知
*/
function king_def(){
	global $king;

	$array=array('version','cmdno','retcode','status','seller','total_fee','trade_price','transport_fee','buyer_id','chnid','cft_tid','mch_vno','attach');

	$buffer='';
	foreach($array as $val){
		if(isset($_GET[$val])){
			$buffer.=$val.'='.$_GET[$val].'&';
			//$ret[$val]=$val.'='.$_GET[];
		}
	}

	$md5_sign = strtoupper(md5( $buffer.'key='.$king->config('tenpaykey','portal') ));

	if($md5_sign=$_GET['sign']){
		//更新
		$oid=kc_get('attach',2,1);//attach来传递oid参数
		$array=array(
			'nstatus'=>kc_get('status',2,1),//交易状态
			'tid'=>$_GET['cft_tid'],//财付通的交易id
			'buyer_id'=>$_GET['buyer_id'],//买家的付款帐号
			'seller'=>$_GET['seller'],//卖家的收款帐号
			'paymethod'=>'tenpay',//支付方式
			//补充modeltable中的nnumber数量扣除
		);
		$king->db->update('%s_orders',$array,"oid=$oid");

		echo "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">";
		echo $king->lang->get('portal/orders/status/s'.$_GET['status']);

	}else{
		kc_error('fail 签名错误!');
	}
}


?>