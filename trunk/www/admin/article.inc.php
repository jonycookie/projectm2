<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
include iPATH.'include/catalog.class.php';
switch($operation){
case 'add':
	$Admin->MP(array("menu_index_article_add","menu_article_add"));
	$catalog =new catalog();
	include(iPATH."include/fckeditor.php");
	$id=(int)$_GET['id'];
	$rs=array();
	$id && $rs=$iCMS->db->getRow("SELECT a.*,ad.tpl,ad.body,ad.subtitle FROM `#iCMS@__article` a LEFT JOIN `#iCMS@__articledata` ad ON a.id=ad.aid WHERE a.id='$id'",ARRAY_A);
	$editor = new FCKeditor('content') ;
	$editor->Value= $rs['body'];
	$rs['pubdate']=empty($id)?get_date('',"Y-m-d H:i:s"):get_date($rs['pubdate'],'Y-m-d H:i:s');
	$rootid=empty($rs['cid'])?intval($_GET['cid']):$rs['cid'];
	$cata_option=$catalog->select($rootid,0,1,'channel=1&list');
	empty($rs['editor']) && $rs['editor']=empty($Admin->admin->name)?$Admin->admin->username:$Admin->admin->name;
	empty($rs['userid']) && $rs['userid']=$Admin->uId;
	include iCMS_admincp_tpl("article.add");
break;
case 'manage':
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$time_start = $mtime[1] + $mtime[0];
	
	$Admin->MP(array("menu_article_manage","menu_article_draft","menu_article_user_manage","menu_article_user_draft"));
	$catalog =new catalog();

	$cid	= (int)$_GET['cid'];
	$act	= $_GET['act'];
	$sql	=" where ";
	$sql.=$_GET['type']=='draft'?"`visible` ='0'":"`visible` ='1'";
	$sql.=$act=='user'?" AND `postype`='0'":" AND `postype`='1'";
	if($_GET['st']=="title"){
		$_GET['keywords'] 		&& $sql.=" AND `title` REGEXP '{$_GET['keywords']}'";
	}else if($_GET['st']=="tkd"){
		$_GET['keywords'] 		&& $sql.=" AND CONCAT(title,keywords,description) REGEXP '{$_GET['keywords']}'";
	}
	$_GET['title'] 			&& $sql.=" AND `title` like '%{$_GET['title']}%'";
	$_GET['tag'] 			&& $sql.=" AND `tags` REGEXP '[[:<:]]".preg_quote(rawurldecode($_GET['tag']),'/')."[[:>:]]'";
	isset($_GET['at']) 		&& $sql.=" AND `type` REGEXP '[[:<:]]".preg_quote($_GET['at'], '/')."[[:>:]]'";
	isset($_GET['userid']) 	&& $sql.=" AND `userid`='".(int)$_GET['userid']."'";
	$cid=$Admin->CP($cid)?$cid:"0";
	if($cid){
		if(isset($_GET['sub'])){
			$sql.=" AND ( cid IN(".$catalog->id($cid).$cid.")";
		}else{
			$sql.=" AND ( cid ='$cid'";
		}
		$sql.=" OR `vlink` REGEXP '[[:<:]]".preg_quote($cid, '/')."[[:>:]]')";
	}else{
		$Admin->cpower && $sql.=" AND cid IN(".implode(',',$Admin->cpower).")";
	}
	isset($_GET['nopic'])&&$sql.=" AND `pic` =''";
	$_GET['starttime'] 	&& $sql.=" and `pubdate`>='".strtotime($_GET['starttime'])."'";
	$_GET['endtime'] 	&& $sql.=" and `pubdate`<='".strtotime($_GET['endtime'])."'";
	
	$act=='user' && $uri.='&act=user';
	$_GET['type']=='draft' && $uri.='&type=draft';
	isset($_GET['userid']) && $uri.='&userid='.(int)$_GET['userid'];
	isset($_GET['keyword']) && $uri.='&keyword='.$_GET['keyword'];
	isset($_GET['tag']) && $uri.='&tag='.$_GET['tag'];
	
	$orderby=$_GET['orderby']?$_GET['orderby']:"id DESC";
	$maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:20;
	$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__article` {$sql} order by {$orderby}");
	page($total,$maxperpage,"篇文章");
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__article` {$sql} order by {$orderby} LIMIT {$firstcount} , {$maxperpage}");
	$_count=count($rs);
	include iCMS_admincp_tpl("article.manage");
//		$mtime = microtime();
//		$mtime = explode(' ', $mtime);
//		$time_end = $mtime[1] + $mtime[0];
//		echo  "<h1>".($time_end - $time_start);
break;
case 'visible':
	$id=(int)$_GET['id'];
	$v=(int)$_GET['v'];
	if($v=='1'){
		$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '0' WHERE `id` ='$id'");
	}else{
		$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '1' WHERE `id` ='$id'");
	}
	_Header();
break;
case 'delvlink':
	$id	= (int)$_GET['id'];
	$cid= (int)$_GET['cid'];
	$id && $vlink=$iCMS->db->getValue("SELECT vlink FROM `#iCMS@__article` WHERE `id`='$id'");
	$vlinkArray	= explode(',',$vlink);
	$key		= array_search($cid,$vlinkArray);
	unset($vlinkArray[$key]);
	$vlink		= implode(',',$vlinkArray);
	$iCMS->db->query("UPDATE `#iCMS@__article` SET `vlink` = '$vlink' WHERE `id` ='$id'");
	_Header();
break;
case 'updateHTML':
	!$_GET['id'] && alert("请选择要更新的文章");
	include_once(iPATH."include/function/template.php");
	MakeArticleHtm(intval($_GET['id'])) && alert('更新完成!',"url:1");
break;
case 'del':
	$id=intval($_GET['id']);
	!$id && alert("请选择要删除的文章");
	delArticle($id) && alert('成功删除!',"url:1");
break;
case 'post':
	switch($action){
	case 'save':
		set_time_limit(0);
	    $aid		= intval($_POST['aid']);
	    $cid		= intval($_POST['catalog']);
	    $userid		= intval($_POST['userid']);
	    $top		= intval($_POST['top']);
	    $type		= empty($_POST['type'])?"0":implode(',',$_POST['type']);
	    $vlink		= empty($_POST['vlink'])?"":implode(',',$_POST['vlink']);
		$title		= dhtmlspecialchars($_POST['title']);
		$subtitle	= dhtmlspecialchars($_POST['subtitle']);
		$stitle		= dhtmlspecialchars($_POST['stitle']);
		$pic		= dhtmlspecialchars($_POST['pic']);
	    $source		= dhtmlspecialchars($_POST['source']);
	    $author		= dhtmlspecialchars($_POST['author']);
	    $editor		= dhtmlspecialchars($_POST['editor']);
	    $description= dhtmlspecialchars($_POST['description']);
	    $keywords	= dhtmlspecialchars($_POST['keywords']);
	    $tags		= dhtmlspecialchars($_POST['tag']);
	    $customlink	= dhtmlspecialchars($_POST['customlink']);
	    $url		= dhtmlspecialchars($_POST['url']);
	    $tpl		= dhtmlspecialchars($_POST['template']);
		$related	= dhtmlspecialchars($_POST['related']);
	    $pubdate	= _strtotime($_POST['pubdate']);
	    
	    $remote		= isset($_POST['remote'])	?true:false;
	    $dellink	= isset($_POST['dellink'])	?true:false;
	    $autopic	= isset($_POST['autopic'])	?true:false;
	    $visible	= isset($_POST['draft'])?"0":"1";
		$postype	= $_POST['postype']?$_POST['postype']:"1";
	    $body		= str_replace(array("\n","\r","\t"),"",$_POST['content']);

	    empty($title) && alert('标题不能为空！');
	    empty($cid) && alert('请选择所属栏目');
	    empty($body) && empty($url) && alert('文章内容不能为空！');
		WordFilter($title) && alert('标题包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($body) && alert('文章内容包含被系统屏蔽的字符，请返回重新填写。');
	    
	    if($customlink){
			for($i=0;$i<strlen($customlink);$i++){
				!preg_match("/[a-zA-Z0-9_\-~".preg_quote($iCMS->config['CLsplit'],'/')."]/",$customlink{$i}) && alert('自定链接只能由英文字母、数字或_-~组成(不支持中文)');
			}
		}
	    isset($_POST['keywordToTag']) && $tags=$keywords;
	    if($iCMS->config['autodesc']=="1" && !empty($iCMS->config['descLen']) && empty($description) && empty($url)){
	    	 $description=csubstr(HtmToText($body),$iCMS->config['descLen']);
	    }
	    $remote && remote($body);
	    (!$remote&&$autopic) && remote($body,true);
	    
		empty($customlink)&& $customlink=pinyin($title,$iCMS->config['CLsplit']);
		
		if(empty($aid)){
			empty($userid) && $userid=$Admin->uId;
			$hits=$digg=$comments=0;
		    $checkCL=$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__article` where `customlink` ='$customlink'");
		    if($iCMS->config['repeatitle']){
		    	$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__article` where `title` = '$title'") && alert('该标题的文章已经存在!请检查是否重复');
		    	$checkCL && alert('该自定链接已经存在!请另选一个');
		    }else{
				$checkCL && $customlink.=$iCMS->config['CLsplit'].random(6,1);
		    }
		    $iCMS->db->insert('article',compact('cid','title','stitle','customlink','url','source','author','editor','userid','postype','keywords','tags','description','related','pic','pubdate','hits' ,'digg','comments','type','vlink','top','visible'));
			$aid	= $iCMS->db->insert_id;
		    if(empty($url)){
			    $iCMS->db->insert('articledata',compact('aid','subtitle','tpl','body'));
				insert_db_remote($body,$aid);
			}
	    	addtags($tags);tags_cache();
			if($iCMS->config['ishtm'] && $visible){
				include_once(iPATH."include/function/template.php");
				MakeArticleHtm($aid);
			}
			$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='$cid' LIMIT 1 ");
			$moreaction=array(
				array("text"=>"编辑该文章","url"=>__SELF__."?do=article&operation=add&id=".$aid),
				array("text"=>"继续添加文章","url"=>__SELF__."?do=article&operation=add&cid=".$cid),
				array("text"=>"查看该文章","url"=>$iCMS->iurl('show',array('id'=>$aid,'link'=>$customlink,'url'=>$url,'dir'=>$iCMS->cdir($catalog->catalog[$cid]),'pubdate'=>$pubdate)),"o"=>'target="_blank"'),
				array("text"=>"查看网站首页","url"=>"../index.php","o"=>'target="_blank"')
			);
			redirect("文章添加完成!",__SELF__."?do=article&operation=manage",'10',$moreaction);
		}else{
		    $checkCL=$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__article` where `customlink` ='$customlink' AND `id` !='$aid'");
		    if($iCMS->config['repeatitle']){
		     	$checkCL && alert('该自定链接已经存在!请另选一个');
		    }else{
		    	$checkCL && $customlink.=$iCMS->config['CLsplit'].random(6,1);
		    }
			$art=$iCMS->db->getRow("SELECT `cid`,`tags` FROM `#iCMS@__article` where `id` ='$aid'");
			TagsDiff($tags,$art->tags);
			tags_cache();
			$iCMS->db->update('article',compact('cid','title','stitle','customlink','url','source','author','editor','userid','postype','keywords','tags','description','related','pic','pubdate','type','vlink','top','visible'),array('id'=>$aid));
			if(empty($url)){
				if($iCMS->db->getValue("SELECT `id` FROM `#iCMS@__articledata` where `aid` ='$aid'")){
					$iCMS->db->update('articledata',compact('tpl','subtitle','body'),compact('aid'));
				}else{
					$iCMS->db->insert('articledata',compact('aid','subtitle','tpl','body'));
				}
				insert_db_remote($body,$aid);
			}
			if($iCMS->config['ishtm'] && $visible){
				include_once(iPATH."include/function/template.php");
				MakeArticleHtm($aid);
			}
			if($art->cid!=$cid) {
				$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count-1 WHERE `id` ='{$art->cid}' LIMIT 1 ");
				$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='$cid' LIMIT 1 ");
			}
			redirect("文章编辑完成!",__SELF__."?do=article&operation=manage",'3');
		}
	break;
	case 'del':
		empty($_POST['id']) && alert("请选择要删除的文章");
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $id){delArticle($id);}
		}
		alert('全部成功删除!',"url:1");
	break;
	case 'passed':
		empty($_POST['id']) && alert("请选择要显示的文章");
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '1' WHERE `id` IN ($ids)");
		}
		_Header();
	break;
	case 'top':
		empty($_POST['id']) && alert("请选择要设置置顶权重的文章");
		$top=$_POST['top'];
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `top` = '$top' WHERE `id` IN ($ids)");
		}
		_Header();
	break;
	case 'passTimeALL':
		$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '1',pubdate='".time()."' WHERE `visible` = '0'");		
		_Header(__REF__);
	break;
	case 'passALL':
		$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '1' WHERE `visible` = '0'");		
		_Header(__REF__);
	break;
	case 'TimeALL':
		$iCMS->db->query("UPDATE `#iCMS@__article` SET pubdate='".time()."' WHERE `visible` = '0'");		
		_Header(__REF__);
	break;
	case 'cancel':
		empty($_POST['id']) && alert("请选择要隐藏的文章");
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '0' WHERE `id` IN ($ids)");
		}
		_Header();
	break;
	case 'updateHTML':
		empty($_POST['id']) && alert("请选择要更新的文章");
		include_once(iPATH."include/function/template.php");
		$i=0;
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $aid){
				MakeArticleHtm($aid) && $i++;
			}
		}
		alert($i.'个文件更新完成!',"url:1");
	break;
	case 'contentype':
		empty($_POST['id']) && alert("请选择要更改的文章");
	    $type = empty($_POST['type'])?"0":implode(',',$_POST['type']);
	    $ids=implode(',',$_POST['id']);
		$iCMS->db->query("UPDATE `#iCMS@__article` SET `type` = '$type' WHERE `id` IN ($ids)");
		alert('文章属性更改完成!',"url:1");
	break;
	case 'keyword':
		empty($_POST['id']) && alert("请选择文章");
		$ids=implode(',',$_POST['id']);
		if($_POST['pattern']=='replace'){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `keywords` = '".dhtmlspecialchars($_POST['keyword'])."' WHERE `id` IN ($ids)");
		}elseif($_POST['pattern']=='addto'){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `keywords` = CONCAT(keywords,',".dhtmlspecialchars($_POST['keyword'])."') WHERE `id` IN ($ids)");
		}
		alert('文章关键字更改完成!',"url:1");
	break;
	case 'tag':
		empty($_POST['id']) && alert("请选择文章");
		$ids=implode(',',$_POST['id']);
		if($_POST['pattern']=='replace'){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `tags` = '".dhtmlspecialchars($_POST['tag'])."' WHERE `id` IN ($ids)");
		}elseif($_POST['pattern']=='addto'){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `tags` = CONCAT(tags,',".dhtmlspecialchars($_POST['tag'])."') WHERE `id` IN ($ids)");
		}
		alert('文章标签更改完成!',"url:1");
	break;
	case 'vlink':
		empty($_POST['id']) && alert("请选择文章");
		$vlink	= empty($_POST['vlink'])?"":implode(',',$_POST['vlink']);
		$ids=implode(',',$_POST['id']);
		if($_POST['pattern']=='replace'){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `vlink` = '".$vlink."' WHERE `id` IN ($ids)");
		}elseif($_POST['pattern']=='addto'){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `vlink` = CONCAT(vlink,',".$vlink."') WHERE `id` IN ($ids)");
		}
		alert('文章虚拟链接更改完成!',"url:1");
	break;
	case 'thumb':
		empty($_POST['id']) && alert("请选择要提取缩略图的文章");
		if(is_array($_POST['id'])){
			$UploadDir 	= $iCMS->config['uploadfiledir']."/";
			foreach($_POST['id'] AS $id){
				$content	= $iCMS->db->getValue("SELECT ad.body FROM `#iCMS@__article` a, `#iCMS@__articledata` ad WHERE a.id=ad.aid and a.id='$id'");
				$img 		= array();
				preg_match_all("/src=[\"|'| ]{0,}(\/(.*)\.(gif|jpg|jpeg|bmp|png))/isU",$content,$img);
				$_array = array_unique($img[1]);
				foreach($_array as $key =>$value){
					$value = getfilepath(trim($value),'','-');					
					if(file_exists(getfilepath($value,iPATH,'+'))){
						$iCMS->db->query("UPDATE `#iCMS@__article` SET `pic` = '$value' WHERE `id` = '$id'");
						break;
					}
				}
			}
		}
		alert('成功提取缩略图',"url:1");
	break;
	case 'move':
		empty($_POST['id']) && alert("请选择要移动的文章");
		!$_POST['cataid'] && alert("请选择目标栏目");
		$cid=intval($_POST['cataid']);
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $id){
				$id=intval($id);
				$acid=$iCMS->db->getValue("SELECT `cid` FROM `#iCMS@__article` where `id` ='$id'");
				$iCMS->db->query("UPDATE `#iCMS@__article` SET cid='$cid' WHERE `id` ='$id'");
				if($acid!=$cid) {
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count-1 WHERE `id` ='{$acid}' LIMIT 1 ");
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='{$cid}' LIMIT 1 ");
				}
			}
		}
		alert('成功移动到目标栏目',"url:1");
	break;
	default:
		foreach($_POST['order'] AS $id=>$order){
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `order` = '$order' WHERE `id` ='$id'");
		}
		_Header();
	}
break;
}

function remote(&$content,$one=false){
	global $iCMS,$title;
	$content = stripslashes($content);
	$img = array();
	preg_match_all("/(src|SRC)=[\"|'| ]{0,}(http:\/\/(.*)\.(gif|jpg|jpeg|bmp|png))/isU",$content,$img);
	$_array = array_unique($img[2]);
	if($_array)foreach($_array AS $_k=> $imgurl){
		if(strstr(strtolower($imgurl),($iCMS->config['url']))) unset($_array[$_k]);
	}
	set_time_limit(0);
	$UploadDir 		= $iCMS->config['uploadfiledir']."/";
	$RelativePath	= $iCMS->dir.$UploadDir;//相对路径
	$RootPath		= iPATH.$UploadDir;//绝对路径
	$_CreateDir = "";
	if($iCMS->config['savedir']){
		$_CreateDir = str_replace(array('Y','y','m','n','d','j','EXT'),
		array(get_date('','Y'),get_date('','y'),get_date('','m'),get_date('','n'),get_date('','d'),get_date('','j'),$FileExt),
		$iCMS->config['savedir'])."/";
	}
	$RelativePath	= $RelativePath.$_CreateDir;
	$RootPath		= $RootPath.$_CreateDir;

	$milliSecond = 'remote_'.get_date('',"YmdHis").rand(1,99999);
	createdir($RootPath);
	foreach($_array as $key =>$value){
		$value = trim($value);
		preg_match("/\.([a-zA-Z0-9]{2,4})$/",$value,$exts);
		$FileExt=strtolower($exts[1]);//&#316;&#701;
		CheckValidExt($FileExt);//判断文件类型
		//过滤文件;
		strstr($FileExt, 'ph')&&$FileExt="phpfile";
		in_array($FileExt,array('cer','htr','cdx','asa','asp','jsp','aspx','cgi'))&& $FileExt.="file";
		
		$sFileName=$milliSecond.$key.".".$FileExt;
		$RootPath_FileName = $RootPath.$sFileName;
		$RelativePath_FileName = $RelativePath.$sFileName;
		$get_file = fopen_url($value);
		$get_file && writefile($RootPath_FileName,$get_file);
		if(in_array($FileExt,array('gif','jpg','jpeg','png'))){
			if($iCMS->config['isthumb'] &&($iCMS->config['thumbwidth']||$iCMS->config['thumbhight'])){
				list($width, $height,$imagetype) = GetImageSize($RootPath_FileName);
				if ( $width > $iCMS->config['thumbwidth'] || $height >$iCMS->config['thumbhight'] ) { 
					createdir($RootPath."thumb");
				}
				$Thumbnail=MakeThumbnail($RootPath, $RootPath_FileName, $milliSecond.$key);
				!empty($Thumbnail['filepath']) && imageWaterMark($Thumbnail['filepath']);
			}
			imageWaterMark($RootPath_FileName);
		}
		$_FileSize = @filesize($RootPath_FileName);
		$RelativePath_FileName	= getfilepath($RelativePath_FileName,'','-');
		$iCMS->db->query("INSERT INTO `#iCMS@__file` (`filename`,`ofilename`,`path`,`intro`,`ext`,`size` ,`time`,`type`) VALUES ('$sFileName', '$value', '$RelativePath_FileName','$title', '$FileExt', '$_FileSize', '".time()."', 'remote') ");
		$content = str_replace($value,$RelativePath_FileName,$content);
		if($one && $key==0)break;
	}
	$content = add_magic_quotes($content);
}
function insert_db_remote($content,$aid){
	global $iCMS,$autopic;
	$UploadDir 	= $iCMS->config['uploadfiledir']."/";
	$content = stripslashes($content);
	$img = array();
	preg_match_all("/(src|SRC)=[\"|'| ]{0,}(\/(.*)\.(gif|jpg|jpeg|bmp|png))/isU",$content,$img);
	$_array = array_unique($img[2]);
	set_time_limit(0);
	foreach($_array as $key =>$value){
		$value = getfilepath(trim($value),'','-');
		$filename = substr($value,strrpos($value,'/')+1);
		$pic=$iCMS->db->getValue("SELECT `pic` FROM `#iCMS@__article` WHERE `id` = '$aid'");
		($autopic && $key==0 && empty($pic)) && $iCMS->db->query("UPDATE `#iCMS@__article` SET `pic` = '$value' WHERE `id` = '$aid'");
		$faid=$iCMS->db->getValue("SELECT `aid` FROM `#iCMS@__file` WHERE `filename` ='$filename'");
		empty($faid) && $iCMS->db->query("UPDATE `#iCMS@__file` SET `aid` = '$aid' WHERE `filename` ='$filename'");
	}
}
?>
