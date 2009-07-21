<?php

define('KC_INDEX',True);

require_once '../global.php';

/**
	
	POST过来的参数有 ass、kname、tags、sign
	MD5(ass=[ass]&kname=[kname]&tags=[tags]{info['sign']}) == sign 当一致的时候，验证通过
*/
function king_def(){
	global $king;

	$ass=kc_post('ass');//isset($_POST['ass']) ?  : '';
	$kname=kc_post('kname');
	$tags=kc_post('tags');

	$sign=kc_post('sign');


	$tmp=new KC_Template_class;

	if($info=$tmp->infoConn($kname)){
		$postsign=md5("ass=$ass&kname=$kname&tags=$tags{$info['ksign']}");
		if($postsign==$sign){//验证通过
			$assign=unserialize(base64_decode($_POST['ass']));
			foreach($assign as $key => $val){
				$tmp->assign($key,$val);
			}
			exit($tmp->output($tags));
		}
	}
	exit('<!-- '.$king->lang->get('system/error/conn').' -->');

}


?>