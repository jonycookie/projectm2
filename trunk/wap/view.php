<?php
require_once('global.php');

if($tid){
	$cms_content = "cms_content1";//目前只支持文章模型
	$rt = $db->get_one("SELECT t.cid,t.tid,t.title,t.publisher,t.postdate,tm.content FROM cms_contentindex t LEFT JOIN $cms_content tm ON tm.tid=t.tid WHERE t.tid='$tid' AND t.ifpub=1");
	if(!$rt){
		wap_msg('illegal_tid');
	}
	catecheck($rt['cid']);
	InitGP(array('page'));
	(!is_numeric($page) || $page < 1) && $page=1;
	$sys['waplimit'] = (int)$sys['waplimit'];
	$rt['title']  = wap_cv($rt['title']);
	if($sys['waplimit']){
		$content_array = getPageContent(str_replace('<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;</span></div>','',$rt['content']),$sys['waplimit']);	
	}elseif($rt['fpage']){
		$content_array = explode('<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>',$rs['content']);
	}
	if($content_array){
		$rt['content'] = $content_array[$page-1];
		$next = $page<count($content_array) ? $page+1:'';
		$pre  = $page>1 ? $page-1:'';
	}
	$rt['content']  = htm2wml($rt['content']);
	$rt['postdate']	= get_date($rt['postdate']);
	$rt['publisher']= wap_cv($rt['publisher']);
} else{
	wap_msg('wap_illegal_tid');
}
wap_header('view',$sys['title']);
require_once PrintEot('wap_view');
wap_footer();
?>