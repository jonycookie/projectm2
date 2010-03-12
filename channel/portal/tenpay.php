<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

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
		$oid=kc_get('attach',2,1);//attach来传递oid参数

		$tmp=new KC_Template_class($king->config('templateorders'),$king->config('templatepath').'/inside/user/orders_show.htm');

		$tmp->assign('oid',$oid);
		$tmp->assign('title',$king->lang->get('portal/title/buyok'));

		echo $tmp->output();

	}else{
		kc_error($king->lang->get('portal/error/payment'));
	}

}


?>