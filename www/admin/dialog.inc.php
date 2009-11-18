<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
$in=$_GET['in'];
switch ($operation) {
	case 'editor':
		echo json_encode(array('err'=>'????','msg'=>uploadfile("upload")["FilePath"]));
	break;
	case in_array($operation,array('file','template')):
		$dir=trim($_GET["dir"]);
		$type=$_GET["type"];
		$hit=$_GET["hit"];
		$from=$_GET["from"];
		$Folder=$operation=='template'?'templates':$iCMS->config['uploadfiledir'];
		$L=GetFolderList($dir,$Folder,$type);
		include iCMS_admincp_tpl('dialog.file');
	break;
	case 'SQLfile':
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
		include iCMS_admincp_tpl('dialog.SQLfile');
	break;
	case 'Aupload':
		include iCMS_admincp_tpl('dialog.Aupload');
	break;
	case 'post':
		include iCMS_admincp_tpl('dialog.post');
	break;
	case 'article':
		include iPATH.'include/catalog.class.php';
		$catalog =new catalog();
		$cid	= (int)$_GET['cid'];
		$sql	=" where ";
		$sql.=$_GET['type']=='draft'?"`visible` ='0'":"`visible` ='1'";
		$sql.=$act=='user'?" AND `postype`='0'":" AND `postype`='1'";
		$_GET['keywords'] && $sql.=" AND CONCAT(title,keywords,description) REGEXP '{$_GET['keywords']}'";
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
		isset($_GET['keyword']) && $uri.='&keyword='.$_GET['keyword'];
		
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__article` {$sql} order by id DESC");
		page($total,$maxperpage,"篇文章");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__article`{$sql} order by id DESC LIMIT {$firstcount} , {$maxperpage}");
		$_count=count($rs);
	//	echo $iCMS->db->func_call;
		include iCMS_admincp_tpl('dialog.article');
	break;
	case 'showpic':
		include iCMS_admincp_tpl('dialog.showpic');
	break;
	case 'cutpic':
		$pFile=$_GET['pic'];
		$iFile=getfilepath($pFile,iPATH,'+');
		$in=$_GET['in'];
		list($width, $height,$imagetype) = @getimagesize($iFile);
		$pw	=$width>500?500:$width;
		$tw	= (int)$iCMS->config['thumbwidth'];
		$th	= (int)$iCMS->config['thumbhight'];
		$rate=round($pw/$width,2)*100;
		$sliderMin=round($tw/$width,2)*100;
		include iCMS_admincp_tpl('dialog.cutpic');
	break;
}
?>
