<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'manage':
		$Admin->MP("menu_file_manage");
		$method=$_GET['method'];
		if($method=='database'){
			$sql="";
			$_GET['aid'] && $sql=" WHERE `aid`='".(int)$_GET['aid']."'";
			$_GET['type']=='image' && $sql=" WHERE ext IN('jpg','gif','png','bmp','jpeg')";
			$_GET['type']=='other' && $sql=" WHERE ext NOT IN('jpg','gif','png','bmp','jpeg')";
			$maxperpage =30;
			$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__file` {$sql} order by `id` DESC");
			$totalSize=$iCMS->db->getValue("SELECT SUM(size) FROM `#iCMS@__file` {$sql} order by `id` DESC");
			page($total,$maxperpage,"个文件");
			$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__file` {$sql} order by `id` DESC LIMIT {$firstcount} , {$maxperpage}");
			$_count=count($rs);
			include iCMS_admincp_tpl("file.manage.database");
		}else{
			include iCMS_admincp_tpl("file.manage.file");
		}
	break;
	case 'page':
		include_once(iPATH."include/fckeditor.php") ;
		$cid=(int)$_GET['cid'];
		$catalog=$iCMS->db->getRow("SELECT * FROM `#iCMS@__catalog` WHERE id='$cid'");
		$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__page` WHERE cid='$cid'");
		if(empty($rs)){
			$rs->createtime=$rs->updatetime=time();
			$rs->creater=$rs->updater=$administrator;
			$rs->body='';
		}
		$editor = new FCKeditor('body');
		$editor->Value= $rs->body;
	//	$editor->CreateHtml()
		$rs->createtime=get_date($rs->createtime,'Y-m-d H:i:s');
		$rs->updatetime=get_date($rs->updatetime,'Y-m-d H:i:s');
		include iCMS_admincp_tpl("file.page");
	break;
	case 'delete':
		deletefile((int)$_GET['fid'])&&_header();
	break;
	case 'reremote':
		$get_file = fopen_url(urldecode($_GET['url']));
		$path=urldecode($_GET['path']);
		writefile(iPATH.$path,$get_file);
		redirect("下载完成!",$_SERVER['HTTP_REFERER']);
	break;
	case 'reupload':
		$fid=(int)$_GET['fid'];
		$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__file` WHERE `id`='$fid' LIMIT 1");
		$path=str_replace($rs->filename,'',$rs->path);
		include iCMS_admincp_tpl("file.reupload");
	break;
	case 'swfupload':
		$F=uploadfile("Filedata");
		echo '<div><ul><li>文件:'.$F["OriginalFileName"].'上传成功！</li><li>路径:'.$F["FilePath"].'</ul></div>';
	break;
	case 'upload':
		$Admin->MP("menu_file_upload");
		include iCMS_admincp_tpl("file.upload");
	break;
	case 'extract':
		$Admin->MP("menu_extract_pic");
		include iPATH.'include/catalog.class.php';
		if(empty($_GET['o'])){
			$catalog =new catalog();
			include iCMS_admincp_tpl("file.extract.pic");
		}else{
			set_time_limit(0);
			$action		=$_GET['action'];
			$QUERY_STRING="&o=1&action=".$action;
			$speed		=100;//提取速度
			$cids		=$_GET['cid'];
			$startid	=(int)$_GET['startid'];
			$endid		=(int)$_GET['endid'];
			$starttime	=$_GET['starttime'];
			$endtime	=$_GET['endtime'];
			$totle		=isset($_GET['totle'])?$_GET['totle']:0;
			$loop		=isset($_GET['loop'])?$_GET['loop']:1;
			$i			=isset($_GET['i'])?$_GET['i']:0;
			empty($action) && alert("请选择操作");
			if($cids){
				empty($cids) && alert("请选择栏目");
				is_array($cids) && $cids = implode(",", $cids);
				if(strstr($cids,'all')){
					$catalog =new catalog();
					$cids=substr($catalog->id(),0,-1);
					if(empty($cids)){
						redirect("提取完毕",__SELF__.'?do=file&operation=extract');
					}else{
						_header(__SELF__.'?do=file&operation=extract&cid='.$cids.$QUERY_STRING);
					}
				}else{
					$cArray	=explode(',',$cids);
					$_Ccount=count($cArray);
					$k		=isset($_GET['k'])?$_GET['k']:0;
					$rs=$iCMS->db->getArray("SELECT id FROM #iCMS@__article WHERE cid in ($cids) and `visible`='1'");
					empty($totle)&&$totle=count($rs);
					$tloop=ceil($totle/$speed);
					if($loop<=$tloop){
						$max=$i+$speed>$totle?$totle:$i+$speed;
						for($j=$i;$j<$max;$j++){
							if($action=="thumb"){
								if(extractThumb($rs[$j]['id'])){
									echo "文章ID:".$rs[$j]['id']."提取…<span style='color:green;'>√</span><br />";flush();
								}
							}elseif($action=="into"){
								if(into($rs[$j]['id'])){
									echo "文章ID:".$rs[$j]['id']."提取…<span style='color:green;'>√</span><br />";flush();
								}
							}
						}
						_header(__SELF__.'?do=file&operation=extract&cid='.$cids.'&totle='.$totle.'&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
					}else{
						redirect("提取完毕",__SELF__.'?do=file&operation=extract');
					}
				}
			}elseif($startid && $endid){
				($startid>$endid &&!isset($_GET['g'])) && alert("开始ID不能大于结束ID");
				empty($totle)&&$totle=($endid-$startid)+1;
				empty($i)&&$i=$startid;
				$tloop=ceil($totle/$speed);
				if($loop<=$tloop){
					$max=$i+$speed>$endid?$endid:$i+$speed;
					for($j=$i;$j<=$max;$j++){
							if($action=="thumb"){
								if(extractThumb($j)){
									echo "文章ID:".$j."提取…<span style='color:green;'>√</span><br />";flush();
								}
							}elseif($action=="into"){
								if(into($j)){
									echo "文章ID:".$j."提取…<span style='color:green;'>√</span><br />";flush();
								}
							}
					}
					_header(__SELF__.'?do=file&operation=extract&startid='.$startid.'&endid='.$endid.'&g&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
				}else{
					redirect("提取完毕",__SELF__.'?do=file&operation=extract');
				}
			}elseif($starttime){
				$s	= strtotime($starttime);
				$e	= empty($endtime)?time()+86400:strtotime($endtime);
				$rs=$iCMS->db->getArray("SELECT id FROM #iCMS@__article WHERE `pubdate`>='$s' and `pubdate`<='$e' and `visible`='1'");
				empty($totle)&&$totle=count($rs);
				$tloop=ceil($totle/$speed);
				if($loop<=$tloop){
					$max=$i+$speed>$totle?$totle:$i+$speed;
					for($j=$i;$j<$max;$j++){
							if($action=="thumb"){
								if(extractThumb($rs[$j]['id'])){
									echo "文章ID:".$rs[$j]['id']."提取…<span style='color:green;'>√</span><br />";flush();
								}
							}elseif($action=="into"){
								if(into($rs[$j]['id'])){
									echo "文章ID:".$rs[$j]['id']."提取…<span style='color:green;'>√</span><br />";flush();
								}
							}
					}
					_header(__SELF__.'?do=file&operation=extract&starttime='.$starttime.'&endtime='.$endtime.'&totle='.$totle.'&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
				}else{
					redirect("提取完毕",__SELF__.'?do=file&operation=extract');
				}
			}else{
				alert("请选择方式");
			}
		}
	break;
	case 'post':
		if($action=='pagedit'){
		    $id				= intval($_POST['id']);
		    $cid			= intval($_POST['cid']);
		    $name			= dhtmlspecialchars($_POST['name']);
		    $title			= dhtmlspecialchars(HtmToText($_POST['title']));
		    $keyword		= dhtmlspecialchars(HtmToText($_POST['keyword']));
		    $description	= dhtmlspecialchars(HtmToText($_POST['description']));
		    $body			= $_POST['body'];
		    $creater		= $updater=$administrator;
		    $createtime		= _strtotime($_POST['createtime']);
		    $updatetime		= time();
		    $data			= compact('cid','title','keyword','description','body','creater','updater','createtime','updatetime');
		    if(empty($id)){
		    	$iCMS->db->insert('page',$data);
		    	redirect($name."页面添加完成!",__SELF__."?do=catalog");
		    }else{
		    	$iCMS->db->update('page',$data,compact('id'));
		    	redirect($name."编辑完成!",__SELF__."?do=catalog");
		    }
		}
		if($action=='reupload'){
			$fid=(int)$_POST['fid'];
			$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__file` WHERE `id`='$fid' LIMIT 1");
			$path=str_replace(array($iCMS->config['uploadfiledir'].'/',$rs->filename),'',$rs->path);
			uploadfile('file','',$path,$rs->filename,'reupload');
			alert($rs->filename.'重新上传成功！','javascript:window.parent.location.reload();');	
		}
		if(isset($_POST['delete'])){
			$i=0;
			foreach($_POST['delete'] as $fid){
				deletefile($fid)&&$i++;
			}
			alert("共删除{$i}个文件！","url:1");
		}else{
			_header();
		}
	break;
}
function deletefile($fid){
	global $iCMS;
	$rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__file` WHERE `id`='$fid' LIMIT 1");
	$thumbfilepath=gethumb($rs->path,'','',true,true);
	delfile(iPATH.$rs->path,false);
	echo $rs->path.' 文件删除…<span style="color:green;">√</span><br />';
	if($thumbfilepath)foreach($thumbfilepath as $wh=>$fp){
		delfile($fp,false);
		echo '缩略图 '.$wh.' 文件删除…<span style="color:green;">√</span><br />';
	}
	$iCMS->db->query("UPDATE `#iCMS@__article` SET `pic`='' WHERE `pic`='{$rs->path}'");
	$iCMS->db->query("DELETE FROM `#iCMS@__file` WHERE `id`='$fid' LIMIT 1");
	return true;
}
function extractThumb($id){
	global $iCMS;
	$UploadDir 	= $iCMS->config['uploadfiledir']."/";
	$content	= $iCMS->db->getValue("SELECT ad.body FROM `#iCMS@__article` a, `#iCMS@__articledata` ad WHERE a.id=ad.aid and a.id='$id'");
	$img 		= array();
	preg_match_all("/src=[\"|'| ]{0,}(\/(.*)\.(gif|jpg|jpeg|bmp|png))/isU",$content,$img);
	$_array = array_unique($img[1]);
	foreach($_array as $key =>$value){
		$value = getfilepath(trim($value),'','-');
		$rootpf	= getfilepath($value,iPATH,'+');
		if(file_exists($rootpf)){
			return $iCMS->db->query("UPDATE `#iCMS@__article` SET `pic` = '$value' WHERE `id` = '$id'");
			break;
		}
	}
}
function into($id){
	global $iCMS;
	$UploadDir 	= $iCMS->config['uploadfiledir']."/";
	$rs	= $iCMS->db->getRow("SELECT a.title,ad.body FROM `#iCMS@__article` a, `#iCMS@__articledata` ad WHERE a.id=ad.aid and a.id='$id'");
	$img 		= array();
	preg_match_all("/src=[\"|'| ]{0,}(\/(.*)\.(gif|jpg|jpeg|bmp|png))/isU",$rs->body,$img);
	$_array = array_unique($img[1]);
	foreach($_array as $key =>$value){
		$value = getfilepath(trim($value),'','-');
		$rootpf	= getfilepath($value,iPATH,'+');
		if(file_exists($rootpf)){
			$filename	=substr(strrchr($value, "/"), 1);
			$FileExt	=substr(strrchr($filename, "."), 1);
			$_FileSize	= @filesize($rootpf);
			if($iCMS->db->getValue("SELECT `id` FROM `#iCMS@__file` WHERE `path`='$value'")){
				echo "库中已有…<span style='color:green;'>×</span><br />";flush();
			}else{
				$iCMS->db->query("INSERT INTO `#iCMS@__file` (`aid`,`filename`,`ofilename`,`path`,`intro`,`ext`,`size` ,`time`,`type`) VALUES ('$id','$filename', '', '$value','{$rs->title}', '$FileExt', '$_FileSize', '".time()."', 'upload')");
				echo "图片: ".$value." 入库…<span style='color:green;'>√</span><br />";flush();
			}
		}else{
			$data="AID: ".$id." 路径: ".$value." 标题: ".$rs->title."\n";
			writefile(iPATH."admin/logs/pic_exist.txt",$data,true,"a+");
		}
	}
}
?>