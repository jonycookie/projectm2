<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_admincp_tpl($p){
	return 'templates/'.$p.'.php';
}
function iCMS_admincp_head($title=''){
	include iCMS_admincp_tpl('header');
}
/*
 * @版权信息禁止更改
 * @请见iCMS使用许可协议<http://www.idreamsoft.cn/doc/iCMS.License.html>
 */
function iCMS_admincp_login(){
	include iCMS_admincp_tpl('login');
}
function redirect($msg, $url="", $t='3',$more="") {
	include iCMS_admincp_tpl('redirect');
}
function cpurl($type = 'parameter', $filters = array('frames')) {
	parse_str($_SERVER['QUERY_STRING'], $getarray);
	$extra = $and = '';
	foreach($getarray as $key => $value) {
		if(!in_array($key, $filters)) {
			@$extra .= $and.$key.($type == 'parameter' ? '%3D' : '=').rawurlencode($value);
			$and = $type == 'parameter' ? '%26' : '&';
		}
	}
	return $extra;
}

function lang($name, $force = true) {
	global $lang;
	return isset($lang[$name]) ? $lang[$name] : ($force ? $name : '');
}
//==========================================================================
function MakeIndexHtm($indexTPL,$indexname=''){
	global $iCMS;
	$GLOBALS["LOADCACHE"]=true;
	if(!$iCMS->config['ishtm']) return false;
	$iCMS->mode='CreateHtml';
	empty($indexname) && $indexname='index';
	writefile(path(iPATH.$indexname).$iCMS->config['htmlext'],$iCMS->Index($indexTPL,$indexname),false);
	return true;
}
function MakeCatalogHtm($cid,$p=1,$loop=0,$cpn=0){
	global $iCMS;//,$Admin;
	if(!$iCMS->config['ishtm']||empty($cid)) return false;
	$catalog=$iCMS->cache('catalog.cache','include/syscache',0,true);
	$rs=$catalog[$cid];
	if(empty($rs)||$rs['url'])return false;
	$cdir=$iCMS->cdir($rs);
	$RootDir=path(iPATH.$iCMS->config['listhtmdir'].$cdir);
	createdir($RootDir);
	$iCMS->url=$iCMS->domain($rs['id']).$iCMS->cper($rs);
	$GLOBALS['page']=$p;
	$iCMS->mode='CreateHtml';
	$htmldate=$iCMS->iList($cid);
	$GLOBALS['cpn']=$cpn;
	$iCMS->pagesize<$cpn && $GLOBALS['cpn']=$cpn=$iCMS->pagesize;
	$p==1 && writefile($RootDir.'/index'.$iCMS->config['htmlext'],$htmldate,false);
	writefile($RootDir.'/'.$iCMS->cper($rs).$p.$iCMS->config['htmlext'],$htmldate,false);
	$iCMS->pagesize>0 && $p++;
	empty($loop) && $loop=ceil($iCMS->pagesize/25);
	if($p<$cpn||empty($cpn)){
		if($p<=$iCMS->pagesize && $iCMS->pagesize>0 && $loop==ceil(($iCMS->pagesize-$p)/25)){
				MakeCatalogHtm($cid,&$p,&$loop,$cpn);
		}
		return array('name'=>$rs['name'],'page'=>$p,'loop'=>$loop,'pagesize'=>$iCMS->pagesize);
	}else{
		return array('name'=>$rs['name'],'page'=>$p,'loop'=>0,'pagesize'=>$iCMS->pagesize);
	}
}
function MakeArticleHtm($aid='',$p='1'){
	global $iCMS;
	if(!$iCMS->config['ishtm']||empty($aid)) return false;
	$iCMS->mode='CreateHtml';
	$htmldate=$iCMS->Show($aid,$p);
	$total=$iCMS->result->pagetotal;
	$RootDir	= path(iPATH.$iCMS->config['htmdir'].$iCMS->dirule(array('id'=>$iCMS->result->id,'dir'=>$iCMS->result->catalogdir,'pubdate'=>$iCMS->result->pubdate)));
	$filename	= $iCMS->filerule(array('id'=>$iCMS->result->id,'link'=>$iCMS->result->customlink,'pubdate'=>$iCMS->result->pubdate));
	$total>1&&$p!=1&&$filename.='_'.$p;
	$FilePath=$RootDir.'/'.$filename.$iCMS->config['htmlext'];
	createdir($RootDir);
	writefile($FilePath,$htmldate,false);
	($total>1&&$p<$total)&&MakeArticleHtm($aid,$p+1);
	return true;
}
function MakeContentHtm($mid,$id){
	global $iCMS;
	if(!$iCMS->config['ishtm']||empty($id)) return false;
	$iCMS->mode='CreateHtml';
	$htmldate	= $iCMS->content($mid,$id);
	$RootDir	= path(iPATH.$iCMS->config['htmdir'].$iCMS->dirule(array('id'=>$iCMS->result->id,'dir'=>$iCMS->result->catalogdir,'pubdate'=>$iCMS->result->pubdate)));
	$filename	= $mid.'_'.$iCMS->filerule(array('id'=>$iCMS->result->id,'link'=>$iCMS->result->customlink,'pubdate'=>$iCMS->result->pubdate));
	$FilePath	= $RootDir.'/'.$filename.$iCMS->config['htmlext'];
	createdir($RootDir);
	writefile($FilePath,$htmldate,false);
	return true;
}	
function MakePageHtm($cid){
	global $iCMS;
	if(!$iCMS->config['ishtm']||empty($cid)){return false;}
	$rs=$iCMS->db->getRow("SELECT * FROM #iCMS@__catalog WHERE id ='$cid'");
	if(empty($rs))return false;
	$iCMS->mode='CreateHtml';
	$RootDir=path(iPATH.$iCMS->config['pagehtmdir']);
	if($iCMS->config['pagerule']=="dir"){
		$RootDir.=$rs->dir;
		createdir($RootDir);
		$filename=$RootDir.'/index'.$iCMS->config['htmlext'];
	}elseif($iCMS->config['pagerule']=="file"){
		createdir($RootDir);
		$filename=$RootDir.$rs->dir.$iCMS->config['htmlext'];
	}
	writefile($filename,$iCMS->page($rs->dir),false);
	return $rs->name;
}
function MakeTagHtm($tid,$p=1,$loop=0,$cpn=0){
	global $iCMS;//,$Admin;
	if(!$iCMS->config['ishtm']||empty($tid)||$iCMS->config['tagrule']=="php") return false;
	$tags=$iCMS->cache('tags.id','include/syscache',0,true);
	$rs=$tags[$tid];
	$name=$rs['name'];
	if(empty($rs)||$rs['visible']=="0")return false;
	$tdir=$iCMS->taghtmrule($rs);
	$RootDir=path(iPATH.$iCMS->config['taghtmdir'].$tdir);
	if($iCMS->config['tagrule']=="dir"){
		createdir($RootDir);$tfile='/index';
	}else{
		createdir(path(iPATH.$iCMS->config['taghtmdir']));
	}
	$iCMS->url=path($iCMS->config['url'].'/'.$iCMS->config['taghtmdir'].$tdir.$tfile.'_');
	$GLOBALS['page']=$p;
	$GLOBALS['cpn']=$cpn;
	$iCMS->mode='CreateHtml';
	$htmldate=$iCMS->tag($name);
	$p==1 && writefile($RootDir.$tfile.$iCMS->config['htmlext'],$htmldate,false);
	writefile($RootDir.$tfile.'_'.$p.$iCMS->config['htmlext'],$htmldate,false);
	$iCMS->pagesize>0 && $p++;
	empty($loop) && $loop=ceil($iCMS->pagesize/25);
	if($p<$cpn||empty($cpn)){
		if($p<=$iCMS->pagesize && $iCMS->pagesize>0 && $loop==ceil(($iCMS->pagesize-$p)/25)){
				MakeTagHtm($tid,&$p,&$loop,$cpn);
		}
		return array('name'=>$name,'page'=>$p,'loop'=>$loop,'pagesize'=>$iCMS->pagesize);
	}else{
		return array('name'=>$name,'page'=>$p,'loop'=>0,'pagesize'=>$iCMS->pagesize);
	}
}
function geticon($fn){
	$ext = strtoupper(getext($fn));
	switch ($ext){
		case "TXT":$icon = "txt.gif";break;
		case "CHM":$icon = "hlp.gif";break;
		case "HLP":$icon = "hlp.gif";break;
		case "DOC":$icon = "doc.gif";break;
		case "PDF":$icon = "pdf.gif";break;
		case "MDB":$icon = "mdb.gif";break;
		case "GIF":$icon = "gif.gif";break;
		case "JPG":$icon = "jpg.gif";break;
		case "JPEG":$icon = "jpg.gif";break;
		case "BMP":$icon = "bmp.gif";break;
		case "PNG":$icon = "pic.gif";break;
		case "ASP":$icon = "code.gif";break;
		case "JSP":$icon = "code.gif";break;
		case "JS":$icon = "js.gif";break;
		case "PHP":$icon = "php.gif";break;
		case "PHP3":$icon = "php.gif";break;
		case "ASPX":$icon = "code.gif";break;
		case "HTM":$icon = "htm.gif";break;
		case "CSS":$icon = "code.gif";break;
		case "HTML":$icon = "htm.gif";break;
		case "SHTML":$icon = "htm.gif";break;
		case "ZIP":$icon = "zip.gif";break;
		case "RAR":$icon = "rar.gif";break;
		case "EXE":$icon = "exe.gif";break;
		case "AVI":$icon = "wmv.gif";break;
		case "MPG":$icon = "wmv.gif";break;
		case "MPEG":$icon = "wmv.gif";break;
		case "ASF":$icon = "mp.gif";break;
		case "RA":$icon = "rm.gif";break;
		case "RM":$icon = "rm.gif";break;
		case "MP3":$icon = "mp3.gif";break;
		case "MID":$icon = "wmv.gif";break;
		case "MIDI":$icon = "mid.gif";break;
		case "WAV":$icon = "audio.gif";break;
		case "XLS":$icon = "xls.gif";break;
		case "PPT":$icon = "ppt.gif";break;
		case "PPS":$icon = "ppt.gif";break;
		case "PHPFILE":$icon = "php.gif";break;
		case "FILE":$icon = "common.gif";break;
		case "SWF":$icon = "swf.gif";break;
		default:$icon = "unknow.gif";break;
	}
	return "<img border='0' src='admin/images/file/{$icon}' align='absmiddle' id='icon'>";
}
//翻页函数
Function page($totle,$displaypg=20,$strunit="",$url='',$target=''){
	global $page,$firstcount,$pagenav;
	$firstcount=intval($firstcount);
	$displaypg=intval($displaypg);
	$page=$page?intval($page):1;
	$lastpg=ceil($totle/$displaypg); //最后页，也是总页数
	$page=min($lastpg,$page);
	$prepg=(($page-1)<0)?"0":$page-1; //上一页
	$nextpg=($page==$lastpg ? 0 : $page+1); //下一页
	$firstcount=($page-1)*$displaypg;
	$firstcount<0 && $firstcount=0;
	$REQUEST_URI=$_SERVER['QUERY_STRING']?$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']:$_SERVER['PHP_SELF'];
	!$url && $url=$_SERVER["REQUEST_URI"]?$_SERVER["REQUEST_URI"]:$REQUEST_URI;
	$_parse_url=parse_url($url);
	$url_query=$_parse_url["query"]; //单独取出URL的查询字串
	if($url_query){
		$url_query=ereg_replace("(^|&)page=$page","",$url_query);
		$url=str_replace($_parse_url["query"],$url_query,$url);
		$url.=$url_query?"&page":"page";
	}else {
		$url.="?page";
	}
	$pagenav=" <a href='$url=1' target='_self'>首页</a> ";
	$pagenav.=$prepg?" <a href='$url=$prepg' target='_self'>上一页</a> ":" 上一页 ";
	$flag=0;
	for($i=$page-2;$i<=$page-1;$i++){
		if($i<1) continue;
		$pagenav.="<a href='$url=$i' target='_self'>[$i]</a>";
	}
	$pagenav.="&nbsp;<b>$page</b>&nbsp;";
	for($i=$page+1;$i<=$lastpg;$i++){
		$pagenav.="<a href='$url=$i' target='_self'>[$i]</a>";
		$flag++;
		if($flag==4) break;
	}
	$pagenav.=$nextpg?" <a href='$url=$nextpg' target='_self'>下一页</a> ":" 下一页 ";
	$pagenav.=" <a href='$url=$lastpg' target='_self'>末页</a> ";
	$pagenav.="共{$totle}{$strunit}，{$displaypg}{$strunit}/页 ";
	$pagenav.=" 共{$lastpg}页";
	for($i=1;$i<=$lastpg;$i=$i+5){
		$s=$i==$page?' selected="selected"':'';
		$select.="<option value=\"$i\"{$s}>$i</option>";
	}
	if($lastpg>200){
		$pagenav.=" 跳到 <input name=\"pageselect\" type=\"text\" id=\"pageselect\" style=\"width:36px\" />页 <input type=\"button\" onClick=\"window.location='{$url}='+$('#pageselect').val();\" value=\"跳转\" />";
	}else{
		$pagenav.=" 跳到 <select name=\"pageselect\" id=\"pageselect\" onchange=\"window.location='{$url}='+this.value\">{$select}</select>页";
	}
	(int)$lastpg<2 &&$pagenav='';
}
//-------------------------------------------
function delArticle($id,$uid='-1',$postype='1'){
	global $iCMS;
	$sql=$uid!="-1"?"and `userid`='$uid' and `postype`='$postype'":"";
	$id=(int)$id;
	$art=$iCMS->db->getRow("SELECT * FROM `#iCMS@__article` WHERE id='$id' {$sql} Limit 1");
	if($art->pic) {
		$usePic=$iCMS->db->getValue("SELECT id FROM `#iCMS@__article` WHERE `pic`='{$art->pic}' and `id`<>'$id'");
		if(empty($usePic)){
			$thumbfilepath=gethumb($art->pic,'','',true,true);
			delfile(iPATH.$art->pic);
			echo $art->pic.' 文件删除…<span style="color:green;">√</span><br />';
			if($thumbfilepath)foreach($thumbfilepath as $wh=>$fp){
				delfile($fp);
				echo '缩略图 '.$wh.' 文件删除…<span style="color:green;">√</span><br />';
			}
			$iCMS->db->query("DELETE FROM `#iCMS@__file` WHERE `path` = '{$art->pic}'");
			echo $art->pic.' 数据删除…<span style="color:green;">√</span><br />';
		}else{
			echo $art->pic.'文件 其它文章正在使用,请到文件管理删除…<span style="color:green;">×</span><br />';
		}
	}
	$catalog=$iCMS->cache('catalog.cache','include/syscache',0,true);
	$art->catalogdir= $iCMS->cdir($catalog[$art->cid]);
	$_urlArray		= array('id'=>$art->id,'link'=>$art->customlink,'url'=>$art->url,'dir'=>$art->catalogdir,'pubdate'=>$art->pubdate);
	$body=$iCMS->db->getValue("SELECT `body` FROM `#iCMS@__articledata` WHERE aid='$id' Limit 1");
	if($iCMS->config['ishtm'] && empty($art->url)){
		$bArray=explode('<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>',$body);
		$total=count($bArray);
		if($total>1){
			for($i=0;$i<=$total;$i++){
				$filename=$iCMS->iurl('show',$_urlArray,$i,iPATH);
				echo getfilepath($filename,iPATH,'-').' 静态文件删除…<span style="color:green;">√</span><br />';
				delfile($filename,false);
			}
		}
	}
	$frs=$iCMS->db->getArray("SELECT `path` FROM `#iCMS@__file` WHERE `aid`='$id'");
	for($i=0;$i<count($frs);$i++){
		if(!empty($frs[$i])){
			$frs[$i]['path'] && delfile(iPATH.$frs[$i]['path']);
			echo $frs[$i]['path'].' 文件删除…<span style="color:green;">√</span><br />';
		}
	}
	if($art->tags){
		$tagArray=explode(",",$art->tags);
		foreach($tagArray AS $k=>$v){
			if($iCMS->db->getValue("SELECT `count` FROM `#iCMS@__tags` WHERE `name`='$v'")=="1"){
				$iCMS->db->query("DELETE FROM `#iCMS@__tags`  WHERE `name`='$v'");
			}else{
				$iCMS->db->query("UPDATE `#iCMS@__tags` SET  `count`=count-1  WHERE `name`='$v'");
			}
		}
		echo ' 标签更新…<span style="color:green;">√</span><br />';
	}
	$iCMS->db->query("DELETE FROM `#iCMS@__file` WHERE `aid`='$id'");
	echo ' 相关文件数据删除…<span style="color:green;">√</span><br />';
	$iCMS->db->query("DELETE FROM `#iCMS@__comment` WHERE aid='$id'");
	echo ' 评论数据删除…<span style="color:green;">√</span><br />';
	$iCMS->db->query("DELETE FROM `#iCMS@__article` WHERE id='$id'");
	$iCMS->db->query("DELETE FROM `#iCMS@__articledata` WHERE `id`='$id'");
	echo ' 文章数据删除…<span style="color:green;">√</span><br />';
	$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count-1 WHERE `id` ='{$art->cid}' LIMIT 1");
	echo ' 栏目数据更新…<span style="color:green;">√</span><br />';
	echo ' 删除完成…<span style="color:green;">√</span><br />';
	return true;
}
function addtags($tags){
	$a	= explode(',',$tags);
	$c	= count($a);
	for($i=0;$i<$c;$i++){
		TagUI($a[$i]);
	}
}
function TagUI($tag){
	global $iCMS,$Admin;
	$tag	= trim($tag);
	$tid	= $iCMS->db->getValue("SELECT `id` FROM `#iCMS@__tags` WHERE `name`='$tag'");
	if(empty($tid) && $tag!=""){
		$iCMS->db->query("INSERT INTO `#iCMS@__tags`(`uid`,`sortid`,`name`,`count`,`ordernum`,`visible`)VALUES ('$Admin->uId','0','$tag','1',0,'1')");
	}else{
		$iCMS->db->query("UPDATE `#iCMS@__tags` SET  `count`=count+1  WHERE `id`='$tid'");
	}
}
function TagsDiff($Ntags,$Otags){
	global $iCMS,$Admin;
	$N		= TagsArray($Ntags);
	$O		= TagsArray($Otags);
	$diff	= array_diff_values($N,$O);
	if($diff['+'])foreach($diff['+'] AS $tag){//新增
		TagUI($tag);
	}
	if($diff['-'])foreach($diff['-'] AS $tid=>$tag){//减少
		$c	=	$iCMS->db->getValue("SELECT `count` FROM `#iCMS@__tags` WHERE `id`='$tid'");
		if($c=="1"){
			$iCMS->db->query("DELETE FROM `#iCMS@__tags`  WHERE `id`='$tid'");
		}else{
			$iCMS->db->query("UPDATE `#iCMS@__tags` SET  `count`=count-1  WHERE `id`='$tid'");
		}
	}
}
function TagsArray($tags){
	global $iCMS;
	$a	= explode(',',$tags);
	$c	= count($a);
	for($i=0;$i<$c;$i++){
		$id=$iCMS->db->getValue("SELECT id FROM `#iCMS@__tags` WHERE `name`='$a[$i]'");
		empty($id)?$tag[]=$a[$i]:$tag[$id]=$a[$i];
	}
	return $tag;
}
//------------------------cache---------------------------
function keywords_cache(){
	global $iCMS;
	$res=$iCMS->db->getArray("SELECT `keyword`,`replace` FROM `#iCMS@__keywords` order by id DESC");
	$iCMS->cache(false,'include/syscache',0,true,false);
	$iCMS->addcache('keywords.cache',$res,0);
}
function search_cache(){
	global $iCMS;
	$res=$iCMS->db->getArray("SELECT `search` FROM `#iCMS@__search` order by times DESC");
	$iCMS->cache(false,'include/syscache',0,true,false);
	$iCMS->addcache('search.cache',$res,0);
}
function tags_cache(){
	global $iCMS;
	$rs=$iCMS->db->getArray("SELECT `id`,`name`,`count`,`visible` FROM `#iCMS@__tags` order by id DESC");
	$_count=count($rs);
	for($i=0;$i<$_count;$i++){
		$key=substr(md5($rs[$i]['name']),8,16);
		$rs[$i]['link']=pinyin($rs[$i]['name'],$iCMS->config['CLsplit']);
		$res[$key]=$rs[$i];
		$resID[$rs[$i]['id']]=$rs[$i];
	}
	$iCMS->cache(false,'include/syscache',0,true,false);
	$iCMS->addcache('tags.cache',$res,0);
	$iCMS->addcache('tags.id',$resID,0);
}
function model_cache(){
	global $iCMS;
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__model` order by id DESC");
	$_count=count($rs);
	for($i=0;$i<$_count;$i++){
		$res[$rs[$i]['table']][$rs[$i]['id']]=$rs[$i];
		$idRes[$rs[$i]['id']]=$rs[$i];
	}
	$iCMS->cache(false,'include/syscache',0,true,false);
	$iCMS->addcache('model.cache',$rs,0);
	$iCMS->addcache('model.id',$idRes,0);
	$iCMS->addcache('model.table',$res,0);
}
function field_cache(){
	global $iCMS;
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__field` order by id DESC");
	$_count=count($rs);
	for($i=0;$i<$_count;$i++){
		$rs[$i]['rules']=unserialize($rs[$i]['rules']);
		if($rs[$i]['rules']['choices']){
			$rs[$i]['rules']=getFieldChoices($rs[$i]['rules']['choices']);
		}
		$rs[$i]['typeText']		=getFieldType($rs[$i]['type']);
		$rs[$i]['validateText']	=getFieldvalidate($rs[$i]['validate']);
		
		$res[$rs[$i]['field']][$rs[$i]['mid']]=$rs[$i];
		$mres[$rs[$i]['mid']][$rs[$i]['field']]=$rs[$i];
	}
	$iCMS->cache(false,'include/syscache',0,true,false);
	$iCMS->addcache('model.field',$mres,0);
	$iCMS->addcache('field.model',$res,0);
	$iCMS->addcache('field.cache',$rs,0);
}
//----------------------------------------------------------------------
function da_var_export($input,$f = 1,$t = null) {
	$output = '';
	if(is_array($input)){
		$output .= "array(\r\n";
		foreach($input as $key => $value){
			$output .= $t."\t".da_var_export($key,$f,$t."\t").' => '.da_var_export($value,$f,$t."\t");
			$output .= ",\r\n";
		}
		$output .= $t.')';
	} elseif(is_string($input)){
		$output .= $f ? "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'" : "'$input'";
	} elseif(is_int($input) || is_double($input)){
		$output .= "'".(string)$input."'";
	} elseif(is_bool($input)){
		$output .= $input ? 'true' : 'false';
	} else{
		$output .= 'NULL';
	}
	return $output;
}
function _strtotime($T){
	global $iCMS;
	$T	= strtotime($T.' GMT');
    $timeoffset = $iCMS->config['ServerTimeZone'] == '111' ? 0 : $iCMS->config['ServerTimeZone'];
	$iCMS->config['cvtime']&&$cvtime=$iCMS->config['cvtime']*60;
	$T+=-$timeoffset*3600-$cvtime;
	$T<0 && $T=0;
	return $T;
}

function updateConfig($v="",$n){
	global $iCMS;
	$iCMS->db->query("UPDATE `#iCMS@__config` SET `value` = '$v' WHERE `name` ='$n'");
}
function CreateConfigFile(){
	global $iCMS;
	$tmp=$iCMS->db->getArray("SELECT * FROM `#iCMS@__config`");
	$config_data="<?php\n\t\$config=array(\n";
	for ($i=0;$i<count($tmp);$i++){
		if($tmp[$i]['name']=='rewrite'||$tmp[$i]['name']=='bbs'){
			$_config.="\t\t\"".$tmp[$i]['name']."\"=>\"".addslashes($tmp[$i]['value'])."\",\n";
		}else{
			$_config.="\t\t\"".$tmp[$i]['name']."\"=>\"".$tmp[$i]['value']."\",\n";
		}
	}
	$config_data.=substr($_config,0,-2);
	$config_data.="\t\n);?>";
	writefile(iPATH.'include/site.config.php',$config_data);
}
function contenType($T="article"){
	global $iCMS;
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__contentype` where `type`='$T'");
	$c=count($rs);
	for ($i=0;$i<$c;$i++){
		$opt.="<option value='{$rs[$i]['val']}'>{$rs[$i]['name']}[type='{$rs[$i]['val']}'] </option>";
	}
	return $opt;
}
//日志
function admincp_log() {
	global $_GET, $_POST,$_iGLOBAL;
	
	$log_message = '';
	if($_GET) {
		$log_message .= 'GET{';
		foreach ($_GET as $g_k => $g_v) {
			$g_v = is_array($g_v)?serialize($g_v):$g_v;
			$log_message .= "{$g_k}={$g_v};";
		}
		$log_message .= '}';
	}
	if($_POST) {
		$log_message .= 'POST{';
		foreach ($_POST as $g_k => $g_v) {
			$g_v = is_array($g_v)?serialize($g_v):$g_v;
			$log_message .= "{$g_k}={$g_v};";
		}
		$log_message .= '}';
	}
	runlog('admincp', $log_message);
}
///----------------------------------------------------------
function getmodel($id){
	global $iCMS;
	if($id){
		$rs	= $iCMS->db->getRow("SELECT * FROM `#iCMS@__model` where id='$id'",ARRAY_A);
		$rs['table']	= $rs['table'].'_content';
		return $rs;
	}else{
		return false;
	}
}
//数据类型
function getSqlType($type,$len,$default){
	switch($type){
		case "number":
			(empty($len)||$len>10) &&$len='11';
			$default=='' && $default='0';
			$sql =" int($len) unsigned NOT NULL  default '".$default."'";
		break;
		case "calendar":
			(empty($len)||$len>10) &&$len='10';
			$default=='' && $default='0';
			$sql =" int($len) unsigned NOT NULL  default '".$default."'";
		break;
		case in_array($type,array('text','checkbox','radio','select','multiple','email','url','image','upload')):
			(empty($len)||$len>255) &&$len='255';
			$sql =" varchar($len) NOT NULL  default '".$default."'";
		break;
		case in_array($type,array('textarea','editor')):
			$sql =" mediumtext NOT NULL";
		break;
	}
	return 	$sql;
}
function getFieldType($type){
	switch($type){
		case "number":	$text='数字(number)';break;
		case "text":	$text='字符串(text)';break;
		case "radio":	$text='单选(radio)';break;
		case "checkbox":$text='多选(checkbox)';break;
		case "textarea":$text='文本(textarea)';break;
		case "editor":	$text='编辑器(editor)';break;
		case "select":	$text='选择(select)';break;
		case "multiple":$text='多选选择(multiple)';break;
		case "calendar":$text='日历(calendar)';break;
		case "email":	$text='电子邮件(email)';break;
		case "url":		$text='超级链接(url)';break;
		case "image":	$text='图片(image)';break;
		case "upload":	$text='上传(upload)';break;
	}
	return 	$text;
}
function getFieldvalidate($type){
	switch($type){
        case "N":$text='不验证';break;
        case "0":$text='不能为空';break;
        case "1":$text='匹配字母';break;
        case "2":$text='匹配数字';break;
        case "4":$text='Email验证';break;
        case "5":$text='url验证';break;
        default: $text='自定义正则';
 	}
    return 	$text;
}
//$FieldArray=array(
//	'TYPE'=>array(
//		"number"=>'数字(number)',
//		"text"=>'字符串(text)',
//		"radio"=>'单选(radio)',
//		"checkbox"=>'多选(checkbox)',
//		"textarea"=>'文本(textarea)',
//		"editor"=>'编辑器(editor)',
//		"select"=>'选择(select)',
//		"calendar"=>'日历(calendar)',
//		"email"=>'电子邮件(email)',
//		"url"=>'超级链接(url)',
//		"image"=>'图片(image)',
//		"upload"=>'上传(upload)',
//	),
//	'VALIDATE'=>array(
//        "N"=>'不验证',
//        "0"=>'不能为空',
//        "1"=>'匹配字母',
//        "2"=>'匹配数字',
//        "4"=>'Email验证',
//        "5"=>'url验证',
//	),
//);

//选项 choice
function getFieldChoices($choices){
	foreach(explode("\n",$choices) as $item) {
		list($index, $choice) = explode('=', $item);
		$option[trim($index)] = trim($choice);
	}
	return $option;
}
function getModelselect($id){
	global $iCMS;
	$model=$iCMS->cache('model.cache','include/syscache',0,true);
    $_mCount=count($model);
    $opt='<optgroup label="-----自定义模型-----"></optgroup>';
    for($i=0;$i<$_mCount;$i++){
    	$selected= ($model[$i]['id']==$id) ? ' selected="selected"':'';
    	$opt.="<option value='{$model[$i]['id']}'{$selected}>{$model[$i]['name']}</option>";
    }
    return $opt;
}
function tagsort($id=""){
	global $iCMS;
	$tSort	= $iCMS->cache('tag.sort','include/syscache',0,true);
//	$output='<select name="sortid" id="sortid" style="width:auto;">';
	if($tSort)foreach($tSort as $i=>$val){
		$selected=$val['id']==$id?' selected="selected"':'';
		$output.='<option value="'.$val['id'].'"'.$selected.'>'.$val['name'].'</option>';
	}
//	$output.='</select>';
	return $output;
}
?>