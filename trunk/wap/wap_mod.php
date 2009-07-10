<?php
!function_exists('readover') && exit('Forbidden');

function wap_header($id,$title,$url="",$t=""){
	header("Content-type: text/vnd.wap.wml;");
	require PrintEot('wap_header');
}
function wap_footer(){
	global $sys;
	require_once PrintEot('wap_footer');
	$output = trim(str_replace(array('<!--<!---->','<!---->',"\r"),'',ob_get_contents()),"\n");
	if($sys['lang'] != 'utf8'){
		$chs = new Chinese($sys['lang'],($sys['wapcharset'] ? 'UTF8' : 'UNICODE'));
		$output=$chs->Convert($output);
	}
	ob_end_clean();
	$sys['gzip'] == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	echo $output;
	flush;
	exit;
}
function wap_output($output){
	echo $output;
}
function wap_msg($msg,$url="",$t="30"){
	@extract($GLOBALS, EXTR_SKIP);
	global $sys;
	ob_end_clean();
	$sys['gzip'] == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	wap_header('msg',$sys['title'],$url,$t);
	require GetLang('error');
	$lang[$msg] && $msg=$lang[$msg];
	wap_output("<p>$msg</p>\n");
	wap_footer();
}

function wap_quest($question,$customquest,$answer){
	$question = $customquest ? $customquest : $question;
	return $question ? substr(md5(md5($question).md5($answer)),8,10) : '';
}
?>