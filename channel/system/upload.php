<?php
/*!
 * upload demo for php
 * @requires xhEditor
 * 
 * @author Yanis.Wang<yanis.wang@gmail.com>
 * @site http://pirate9.com/
 * @licence LGPL(http://www.opensource.org/licenses/lgpl-license.php)
 * 
 * @Version: 0.9.2 build 100225
 * 
 * 注：本程序仅为演示用，请您根据自己需求进行相应修改，或者重开发。
 */
//header('Content-Type: text/html; charset=UTF-8');
//error_reporting(0);


require '../global.php';

function uploadfile($inputname)
{
	global $king;
	$king->access('#brow_upfile');
	
	$immediate=kc_get('immediate');
	$attachdir=$king->config('uppath');//上传文件保存路径，结尾不要带/
	$dirtype=1;//1:按天存入目录 2:按月存入目录 3:按扩展名存目录  建议使用按天存
	$maxattachsize=20971520;//最大上传大小，默认是20M
	$upext='txt,rar,zip,jpg,jpeg,gif,png,swf,wmv,avi,wma,mp3,mid,doc,docx,xls,xlsx,pdf';//上传扩展名
	$msgtype=2;//返回上传参数的格式：1，只返回url，2，返回参数数组
	
	$err = "";
	$msg = "";
	$upfile=$_FILES[$inputname];
	if(!empty($upfile['error']))
	{
		switch($upfile['error'])
		{
			case '1':
				$err = '文件大小超过了php.ini定义的upload_max_filesize值';
				break;
			case '2':
				$err = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
				break;
			case '3':
				$err = '文件上传不完全';
				break;
			case '4':
				$err = '无文件上传';
				break;
			case '6':
				$err = '缺少临时文件夹';
				break;
			case '7':
				$err = '写文件失败';
				break;
			case '8':
				$err = '上传被其它扩展中断';
				break;
			case '999':
			default:
				$err = '无有效错误代码';
		}
	}
	elseif(empty($upfile['tmp_name']) || $upfile['tmp_name'] == 'none')$err = '无文件上传';
	else
	{
		$temppath=$upfile['tmp_name'];
		$fileinfo=pathinfo($upfile['name']);
		$extension=$fileinfo['extension'];
		if(preg_match('/'.str_replace(',','|',$upext).'/i',$extension))
		{
			$filesize=filesize($temppath);
			if($filesize > $maxattachsize)$err='文件大小超过'.$maxattachsize.'字节';
			else
			{
				switch($dirtype)
				{
					case 1: $attach_subdir = date('Y/m/d'); break;
					case 2: $attach_subdir = date('Y/m'); break;
					case 3: $attach_subdir = $extension; break;
				}

				switch (strtolower($extension)) {
					case 'txt':$typepath='file';break;
					case 'rar':$typepath='file';break;
					case 'zip':$typepath='file';break;
					case 'pdf':$typepath='file';break;
					case 'xls':$typepath='file';break;
					case 'xlsx':$typepath='file';break;
					case 'doc':$typepath='file';break;
					case 'docx':$typepath='file';break;
					case 'jpg':$typepath='image';break;
					case 'jpeg':$typepath='image';break;
					case 'gif':$typepath='image';break;
					case 'png':$typepath='image';break;
					case 'swf':$typepath='flash';break;
					case 'wmv':$typepath='movie';break;
					case 'avi':$typepath='movie';break;
					case 'wma':$typepath='movie';break;
					case 'mp3':$typepath='movie';break;
					case 'mid':$typepath='movie';break;
				}
				$attach_dir = $attachdir.'/'.$typepath.'/'.$attach_subdir;
				kc_f_md($attach_dir);
				/*
				if(!is_dir($attach_dir))
				{
					@mkdir($attach_dir, 0777);
					@fclose(fopen($attach_dir.'/index.htm', 'w'));
				}
				 */
				PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
				$filename=date("YmdHis").mt_rand(1000,9999).'.'.$extension;
				$target = $attach_dir.'/'.$filename;

				move_uploaded_file($upfile['tmp_name'],ROOT.$target);
				if($immediate=='1')$target='!'.$target;
				if($msgtype==1)$msg=$target;
				else{
					//写入上传文件记录到数据库
					$array=array(
						'kpath'=>$target,
						'ndate'=>time(),
						'adminid'=>$king->admin['adminid'],
						'ntype'=>$extension,
					);
					$kid=$king->db->insert('%s_upfile',$array);
					$msg=array('url'=>$king->config('inst').$target,'localname'=>$upfile['name'],'id'=>$kid);//id参数固定不变，仅供演示，实际项目中可以是数据库ID
					
				}
			}
		}
		else $err='上传文件扩展名必需为：'.$upext;

		@unlink($temppath);
	}
	return array('err'=>$err,'msg'=>$msg);
}

function king_def(){
	$state=uploadfile('filedata');
	echo json_encode($state);
}

?>