<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');

switch ($action) {
	case 'editor':
		$F=uploadfile("upload");
		echo json_encode(array('err'=>'','msg'=>uploadfile("upload")["FilePath"]));
	break;
	case 'Aupload':
		strpos($_POST['savedir'],'.')!==false && alert('目录不能带有.','javascript:void(0);');
		$F=uploadfile("file");
		alert($F["OriginalFileName"].'上传成功！','javascript:insert("'.$F["FilePath"].'","'.$_POST['in'].'");');
	break;
	case 'uploadfile':
		strpos($_POST['savedir'],'.')!==false && alert('目录不能带有.','javascript:void(0);');
		$F=uploadfile("file",'',$_POST['savedir']);
		alert($F["OriginalFileName"].'上传成功！','javascript:window.parent.location.reload();');
	break;
	case 'createdir':
		$dirname=$_POST['dirname'];
		$savedir=$_POST['savedir'];
		strpos($savedir,'.')!==false && alert('目录不能带有.','javascript:void(0);');
		strpos($dirname,'.')!==false && alert('目录不能带有.','javascript:void(0);');
		createdir(iPATH.$iCMS->config['uploadfiledir']."/".$savedir.$dirname);
		alert("目录[{$dirname}]创建成功！",'javascript:window.parent.location.reload();');
	break;
	case 'crop':
	//header('Content-type: image/jpeg');
		$tMap	= array( 1 => 'gif', 2 => 'jpeg', 3 => 'png' );
		$pic	= $_POST['pFile'];
		$iPic	= getfilepath($pic,iPATH,'+');
		list($width, $height,$type) = @getimagesize($iPic);
		$_width	= $_POST['width'];
		$_height= $_POST['height'];
		$w 		= $_POST['w'];
		$h 		= $_POST['h'];
		$x 		= $_POST['x'];
		$y 		= $_POST['y'];
		if($width==$w && $height==$h){
			alert('源图小于或等于剪裁尺寸,不剪裁!','javascript:insert("'.$pic.'","'.$_POST['in'].'");');
		}
		if($width==$_width && $height==$_height){//不对源图缩放
			$_img	= icf($tMap[$type],$iPic);
			$_Type	= $_img['type'];
		}else{
			$img= icf($tMap[$type],$iPic);
			$_Type	= $img['type'];
			if ($img['res']) {
				$thumb = imagecreatetruecolor($_width,$_height);
				imagecopyresampled($thumb, $img['res'], 0, 0, 0, 0, $_width,$_height, $width, $height);
				$_tmpfile=$iCMS->config['uploadfiledir'].'/crop_tmp_'.time().rand(1,999999);
				__image($thumb,$_Type,getfilepath($_tmpfile,iPATH,'+'));
				$_tmpfile.='.'.$_Type;
				$_img= icf($tMap[$type],$_tmpfile);
				delfile($_tmpfile);
			}
		}
		if ($_img['res']) {
			$_thumb = imagecreatetruecolor($w,$h);
			imagecopyresampled($_thumb,$_img['res'],0,0,$x,$y,$w,$h,$w,$h);
			$thumbpath	= substr($iPic,0,strrpos($iPic,'/'))."/thumb";
			$picName	= substr($iPic,0,strrpos($iPic,'.'));
			$picName	= substr($picName,strrpos($picName,'/'));
			$fileName	= $thumbpath.$picName.'_'.$w.'x'.$h;
			createdir($thumbpath);
			__image($_thumb,$_img['type'],$fileName);
			$fileName.='.'.$_Type;
			alert($pic.' 剪裁成功！','javascript:insert("'.$iCMS->dir.getfilepath($fileName,iPATH,'-').'","'.$_POST['in'].'");');
		}
	break;
}
?>