<?php
!defined('IN_CMS') && die('Forbidden');
require_once(R_P.'require/class_image.php');
/**
 * 此类对附件进行各类操作
 * 包括：浏览，上传，删除，缩略
*/
class Attach{
	var $uploadnum;	//一个统计上传个数的计数器
	var $displaynum;//显示附件数目
	var $pages;
	var $picheight; //附件图片缩略高度
	var $picwidth;	//附件图片缩略宽度
	var $type;		//上传方式：ajax

	/**
	 * 浏览附件
	 *
	 * @param integer $page
	 * @param string $type
	 * @return array
	 */
	function show($page,$type='img',$keyword){
		global $db,$basename,$inputname,$inputtype;
		empty($type) && $type='img';
		$img_ext   = array('gif','jpg','jpeg','png');
		$media_ext = array('swf','wma','mp3','wmv','avi','asx','mov','rm','rmvb','mid');
		$attach_ext = array('doc','xls','ppt','rar','zip','7z','txt','wps','docx');
		if($type=='img'){
			$img_exts = implode(',',$img_ext);
			$img_exts = "'".str_replace(",","','",$img_exts)."'";
			$sqladd = " WHERE a.type IN($img_exts) ";
		}else if ($type =='flash'){
			$media_exts = implode(',',$media_ext);
			$media_exts = "'".str_replace(",","','",$media_exts)."'";
			$sqladd = " WHERE a.type IN($media_exts) ";
		}else if ($type =='attach'){
			$attach_exts = implode(',',$attach_ext);
			$attach_exts = "'".str_replace(",","','",$attach_exts)."'";
			$sqladd = " WHERE a.type IN($attach_exts) ";
		}else if ($type =='rar'){
			$attach_exts = implode(',',array('rar','zip'));
			$attach_exts = "'".str_replace(",","','",$attach_exts)."'";
			$sqladd = " WHERE a.type IN($attach_exts) ";
		}else if ($type =='txt'){
			$attach_exts = implode(',',array('doc','xls','ppt','txt','wps','docx'));
			$attach_exts = "'".str_replace(",","','",$attach_exts)."'";
			$sqladd = " WHERE a.type IN($attach_exts) ";
		}else if ($type == 'all'){
			$sqladd = "  ";
		}
		if($keyword){
			if($type=='all') {
				$sqladd ="WHERE a.filename LIKE '%$keyword%' ";
			}else{
				$sqladd.=" AND a.filename LIKE '%$keyword%' ";
			}
		}
		($page<=0 || !is_numeric($page)) && $page=1;
		!$this->displaynum && $this->displaynum = 15;
		//$rt = $db->get_one("SELECT DISTINCT COUNT(*) AS total FROM cms_attach a LEFT JOIN cms_attachindex ai ON a.aid=ai.aid $sqladd GROUP BY a.aid DESC");
		$rt = $db->get_one("SELECT COUNT(*) AS total FROM cms_attach a $sqladd");
		$total		=	$rt['total'];
		$start		=	($page-1)*$this->displaynum;
		$numofpage	=	ceil($total/$this->displaynum);
		if($type=='attach'){
			$this->pages = numofpage($total,$page,$numofpage,"$basename&action=addattach&type=$type&inputname=$inputname&inputtype=$inputtype&");
		}else if($type=='img'){
			$this->pages = numofpage($total,$page,$numofpage,"$basename&action=addimage&type=$type&inputname=$inputname&inputtype=$inputtype&");
		}else{
			$this->pages = numofpage($total,$page,$numofpage,"$basename&action=view&type=$type&displaynum=$this->displaynum&");
		}
		$files_img	=	"images/admin/file/";
		$rs			=	$db->query("SELECT a.*,ai.tid FROM cms_attach a LEFT JOIN cms_attachindex ai ON a.aid=ai.aid $sqladd ORDER BY a.aid DESC LIMIT $start,$this->displaynum");
		$files		=	array();
		while ($att = $db->fetch_array($rs)){
			$att['icon'] = $files_img.$att['type'].'.gif';
			if(in_array(strtolower($att['type']),$img_ext)){
				$att['url'] = "javascript:showpic('$att[filepath]');";
			}else{
				$att['url'] = 'javascript:void(0);';
			}
			$att['filesize'] = round($att['size']/1000,2);
			$att['filesize'].=' KB';
			$att['uploadtime'] = get_date($att['uploadtime'],'y-m-d H:i');
			$att['tid'] && $att['disable']='disabled';
			$files[] = $att;
		}
		return $files;
	}

	/**
	 * 删除附件
	 *
	 * @param integer $aid
	 */
	function del($aid){
		global $db,$very;
		$path = D_P.$very['attachdir'].'/';
		if(is_array($aid)){
			$delaid = $delfile = array();
			foreach ($aid as $d){
				$d = (int)$d;
				$delaid[] = $d;
			}
			$aids = implode(',',$delaid);
			$rs = $db->query("SELECT filepath,aid,isftp FROM cms_attach WHERE aid IN($aids)");
			while ($dels = $db->fetch_array($rs)) {
				$delfile[] = $dels['filepath'];
			}
			foreach ($delfile as $filename){
				if($very['ifftp'] && $file['isftp'] && require_once(R_P.'require/class_ftp.php')) {
					$ftp->delete($filename);
				}else {
					P_unlink($path.$filename);
				}
			}
			$db->update("DELETE FROM cms_attach WHERE aid IN($aids)");
		}else{
			$file = $db->get_one("SELECT filepath,aid,isftp FROM cms_attach WHERE aid='$aid'");
			$filename = $path.$file['filepath'];
			if($very['ifftp'] && $file['isftp'] && require_once(R_P.'require/class_ftp.php')) {
				$ftp->delete($file['filepath']);
			}else {
				P_unlink($filename);
			}
			if ($ftp) {
				$ftp->close(); unset($ftp);
			}
			$db->update("DELETE FROM cms_attach WHERE aid='$aid'");
		}
	}

	/**
	 * 上传附件
	 *
	 */
	function upload(){
		global $timestamp,$db,$very,$filedir;
		$forbidden_ext = array('php','php3','asp','aspx','asa','jsp','cgi','exe','pl','htm','html');
		//禁止上传这些文件
		$pic_ext = array('png','jpg','jpeg','gif'); //要进行缩略处理的图片文件后缀
		extract(Init_GP(array('automini','width','height','quality'),'P'));
		$isftp	 = '';
		if($automini){
			if(empty($height) || empty($width)) Showmsg('pub_nosize');
			$quality = (int)$quality;
			$quality>100 && $quality=100;
			$quality<=50 && $quality=50;
		}
		$i = $this->uploadnum = 0;

		foreach ($_FILES as $key=>$value){
			$very['ifftp'] && empty($ftp) && require_once(R_P.'require/class_ftp.php');//是否远程上传
			$i++;
			if(is_array($value)){
				$filename	= $value['name'];
				$tmpfile	= $value['tmp_name'];
				$filesize	= $value['size'];
			}else{
				$filename	= ${$key.'_name'};
				$tmpfile	= $$key;
				$filesize	= ${$key.'_size'};
			}
			if($this->type=='ajax') {
				if($very['lang'] != 'utf8'){
					require_once(R_P.'require/chinese.php');
					$chs = new Chinese('UTF8',$very['lang']);
					$filename = $chs->Convert($filename);
				}
			}

			if(!$this->if_uploaded_file($tmpfile))	continue;

			$file_ext = strtolower(substr(strrchr($filename,"."),1));//文件后缀
			in_array($file_ext,$forbidden_ext) && $this->showerror('pub_uploadext','415');

			$savedir = $this->saveDir($file_ext);
			$filedir = $savedir;
			if($very['ifftp']) {
				$savedir = D_P.$very['attachdir'].'/temp';
			}else {
				$savedir = D_P.$very['attachdir'].'/'.$savedir;
			}

			$randvar	= substr(md5($timestamp+$this->uploadnum),10,10);
			$upfilename = $randvar.'.'.$file_ext;
			$target		= $savedir.'/'.$upfilename;
			if(!$this->postupload($tmpfile,$target)){
				$this->showerror('pub_uploadfail','500');
			}
			$newimg='s_'.$upfilename;
			$newtarget=$savedir.'/'.$newimg;
			if(in_array($file_ext,$pic_ext) && ($automini || $very['ckwater'])){
				if(!($img_size=getimagesize($target)) && $very['watermark']){
					P_unlink($target);
					$this->showerror('pub_uploadfail','500');
				}
				$water = new image($target);
				if($img_size[2]<'4' && $very['ckwater'] && $img_size[0]>$very['waterwidth'] && $img_size[1]>$very['waterheight']){//图片水印
					if(function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($very['waterimg'] && function_exists('imagecopymerge') || !$very['waterimg'] && function_exists('imagettfbbox'))){
						//$water->setSrcImg($target);
						if($very['waterimg']) {
							!$very['jpgquality'] && $very['jpgquality'] = "75";
							!$very['waterpct']	 && $very['waterpct']	= "75";
							$water->setMaskImg(D_P."images/water/".$very['waterimg']);
							$water->setMaskImgPct($very['jpgquality']);
							$water->setMaskTxtPct($very['waterpct']);
						}else{
							!$very['watertext'] && $this->showerror('config_nowaterinfo',412);
							!$very['waterfont'] && $very['waterfont']  = "10";
							!$very['watercolor']&& $very['watercolor'] = "#FF0000";
							$very['watertextlib']&& $water->setMaskFont($very['watertextlib']);

							$water->setMaskFontSize($very['waterfont']);
							$water->setMaskFontColor($very['watercolor']);
							$water->setMaskWord($very['watertext']);
						}
						$water->setMaskPosition($very['waterpos']);
					}
				}
				if($automini){//缩略图
					if($img_size[0]<$width && $img_size[1]<$height) {
						$water->setDstImg($target);
						$water->createImg(100);
						$filepath = $filedir.'/'.$upfilename;
					}else{
						$newimg		= 's_'.$upfilename;
						$newtarget	= $savedir.'/'.$newimg;
						$water->setImgCreateQuality($quality);
						$water->setDstImg($newtarget);
						$water->createImg($width,$height);
						P_unlink($target);
						$filepath = $filedir.'/'.$newimg;
					}
				}else{
					$water->setDstImg($target);
					$water->createImg(100);
					$filepath = $filedir.'/'.$upfilename;
				}
			}else{
				$filepath = $filedir.'/'.$upfilename;
			}

			if($very['ifftp'] && isset($ftp) && $ftpsize=$ftp->upload($target,$filepath)) {//远程上传图片
				P_unlink($target);
				$isftp = '1';
			}

			$intro = 'fileintro'.$i;
			extract(Init_GP(array("$intro")));
			$fileintro = $$intro;
			$fileintro = Char_cv($fileintro);
			$db->update("INSERT INTO cms_attach SET
				type='$file_ext',
				filename='$filename',
				filepath='$filepath',
				fileintro='$fileintro',
				size='$filesize',
				uploadtime='$timestamp',
				isftp='$isftp'
			");
			$this->uploadnum++;
			$this->fileName = $filename;
			$this->filePath = $filepath;
		}
		if ($ftp) {
			$ftp->close(); unset($ftp);
		}

	}

	/**
	 * 图片本地化，本方法不同于Gather类的同名方法
	 *
	 * @param string $data
	 * @return string 本地化之后的内容
	 */
	function imageToLocal($data){
		global $very,$db,$timestamp;
		$chunklist = array ();
		$chunklist = explode("\n", $data);
		$links = array ();
		$regs = array ();
		$source = array();
		$i = 0;
		while(list ($id, $chunk) = each($chunklist)){
			if (strstr(strtolower($chunk), "img") && strstr(strtolower($chunk), "src")){
				while (preg_match("/(img[^>]*src[[:blank:]]*)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/is", $chunk, $regs)) {
					if($regs[2]){
						$i++;
						$source[$i] = $regs[2];
						//$imglinks[$i] = $this->realUrl($regs[2]);
					}
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}
		}
		$newImg = array();
		$savedir = $this->saveDir('image');
		foreach ($source as $key=>$imgsrc){
			if(strpos($imgsrc,"http://")!==false && !strpos($imgsrc,$very['url'])){
				//确认是外部图片需要本地化
				$file_ext = strtolower(substr(strrchr($imgsrc,"."),1));
				if(!in_array($file_ext,array('jpg','jpeg','png','gif'))) $file_ext='jpg';
				//如果不是指定格式，则强制格式，防止本地化可能带来的安全问题
				$imgname = substr(md5($imgsrc),10,10).'.'.$file_ext;
				$filepath = $savedir.'/'.$nameadd.$imgname;
				$newImgSrc = $very['attachdir'].'/'.$filepath;
				$TargetImg = D_P.$newImgSrc;
				if(!file_exists($TargetImg)){
					if(!(getContent::copy($imgsrc,$TargetImg))){
						unset($source[$key]);
						continue;
					}
/**
					if($very['ckwater']){//本地化的图片暂时关闭水印
						if(!($img_size=getimagesize($TargetImg))){
						}else{
							$water = new image($TargetImg);
							if($img_size[2]<'4' && $img_size[0]>$very['waterwidth'] && $img_size[1]>$very['waterheight']){
								if(function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($very['waterimg'] && function_exists('imagecopymerge') || !$very['waterimg'] && function_exists('imagettfbbox'))){
									if($very['waterimg']) {
										!$very['jpgquality'] && $very['jpgquality'] = "75";
										!$very['waterpct']	 && $very['waterpct']	= "75";
										$water->setMaskImg(D_P."images/water/".$very['waterimg']);
										$water->setMaskImgPct($very['jpgquality']);
										$water->setMaskTxtPct($very['waterpct']);
									}else{
										!$very['waterfont'] && $very['waterfont']  = "10";
										!$very['watercolor']&& $very['watercolor'] = "#FF0000";
										$very['watertextlib']&& $water->setMaskFont($very['watertextlib']);
										$water->setMaskFontSize($very['waterfont']);
										$water->setMaskFontColor($very['watercolor']);
										$water->setMaskWord($very['watertext']);
									}
									$water->setMaskPosition($very['waterpos']);
								}
							}
							$water->setDstImg($TargetImg);
							$water->createImg(100);
						}
					}
**/
					$filename = end(explode('/',$imgsrc));
					$filesize = filesize($TargetImg);
					$db->update("INSERT INTO cms_attach SET
						type='$file_ext',
						filename='$filename',
						filepath='$filepath',
						fileintro='内容图片本地化',
						size='$filesize',
						uploadtime='$timestamp'
					");
				}
				$newImg[$key] = $newImgSrc;
			}else{
				unset($source[$key]);
			}
		}
		return str_replace($source,$newImg,$data); //再把内容中图片地址更换成对应的本地图片地址
	}

	/**
	 * 文件保存目录
	 *
	 * @return string
	 */
	function saveDir($file_ext){
		global $very;
		switch($very['attachmkdir']){
			case 1: $savedir = ''; break;
			case 2: 
				if(in_array(strtolower($file_ext),array('gif','jpg','jpeg','png','image'))) {
					$file_ext = 'image';
				}elseif(in_array(strtolower($file_ext),array('swf','wma','mp3','wmv','avi','asx','mov','rm','rmvb','mid','media'))) {
					$file_ext = 'media';
				}elseif(in_array(strtolower($file_ext),array('doc','xls','ppt','rar','zip','7z','txt','wps','docx'))) {
					$file_ext = 'other';
				}else {
					$file_ext = 'other';
				}
				$savedir = $file_ext; 
				break;
			case 3: $savedir = date('ym'); break;
			case 4: $savedir = date('ymd'); break;
			default:$savedir = date('ymd'); break;
		}
		if(!$very['ifftp'] && !is_dir(D_P.$very['attachdir'].'/'.$savedir)){
			@mkdir(D_P.$very['attachdir'].'/'.$savedir);
			@chmod(D_P.$very['attachdir'].'/'.$savedir, 0777);
			@fclose(@fopen(D_P.$very['attachdir'].'/'.$savedir.'/index.html', 'w'));
			@chmod(D_P.$very['attachdir'].'/'.$savedir.'/index.html', 0777);
		}
		return $savedir; //返回路径
	}

	function postupload($tmp_name,$filename){
		if(strpos($filename,'..')!==false || eregi("\.php$",$filename)){
			exit('illegal file type!');
		}
		if(function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name,$filename)){
			@chmod($filename,0777);
			return true;
		}elseif(@copy($tmp_name, $filename)){
			@chmod($filename,0777);
			return true;
		}elseif(is_readable($tmp_name)){
			writeover($filename,readover($tmp_name));
			if(file_exists($filename)){
				@chmod($filename,0777);
				return true;
			}
		}
		return false;
	}

	function if_uploaded_file($tmp_name){
		if(!$tmp_name || $tmp_name=='none'){
			return false;
		}elseif(function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))){
			return false;
		}else{
			return true;
		}
	}

	function getAttachPath($imgsrc){ //获取到一个附件的绝对路径和文件名称
		global $very;
		if($this->picheight && $this->picwidth){
			$nameadd=$this->picwidth.'_'.$this->picheight.'_';
		}
		$SourceImg = $TargetImg = $SmallImg ='';
		if (strpos($imgsrc,'http://')===0){ //说明是一个网址
			$temppath = D_P.$very['attachdir'].'/temp/'; //此路径为临时图片保存目录
			$file_ext = end(explode('.',$imgsrc));
			$imgname = substr(md5($imgsrc),10,10).'.'.$file_ext;
			//$savedir = $this->saveDir();
			$SourceImg = $temppath.$imgname; //绝对路径
			$SmallImg = $very['attachdir'].'/s/'.$nameadd.$imgname;
			$TargetImg = D_P.$SmallImg;
			if(!file_exists($SourceImg)){
				if(!(getContent::copy($imgsrc,$SourceImg))){
					return false;
				}
			}
			/* 将图片保存至本地 */
		}else{
			$i_a = pathinfo($imgsrc);
			$SmallImg = $i_a['dirname'].'/'.'s_'.$nameadd.$i_a['basename'];
			$SourceImg = D_P.$imgsrc; //绝对路径
			$TargetImg = D_P.$SmallImg;
		}
		return array($SourceImg,$TargetImg,$SmallImg);
	}

	function resize_image($oldimg='', $newimg='', $picwidth=0, $picheight=0, $quality=80){ //缩略附件图片
		$picwidth==0 && $picwidth = $this->picwidth;
		$picheight==0 && $picheight = $this->picheight;

		if(!trim($oldimg) || !trim($newimg) || !is_file($oldimg)){
			return 0;
		}
		if(!$picwidth && !$picheight){
			return 0;
		}
		if($picwidth<0 || $picheight<0 || $quality<1){
			return 0;
		}
		// Get the extend name of the old file
		$filename = $oldimg;
		if (strstr($oldimg, "/")){
			$filename = end(explode("/", $oldimg));
		}
		if (!strstr($filename, ".")){
			return 0;
		}

		$extname = strtolower(end(explode(".",$filename)));

		//检验新文件
		$filename = $newimg;
		if (strstr($newimg,"/")){
			$filename = end(explode("/",$newimg));
		}
		if (!strstr($filename,".")){
			return 0;
		}
		$nextname = strtolower(end(explode(".",$filename)));
		//文件检验完毕
		// Select the format of the new image

		switch ($extname){
			case "jpg"	:
				$im = imagecreatefromjpeg($oldimg);
				break;
			case "jpeg"	:
				$im = imagecreatefromjpeg($oldimg);
				break;
			case "gif"	:
				$im = imagecreatefromgif($oldimg);
				break;
			case "png"	:
				$im = imagecreatefrompng($oldimg);
				break;
			default		:
				return 0;
				break;
		}
		$color = imagecolorallocate($im, 255, 255, 255);
		$filesize = getimagesize($oldimg);
		if($filesize[1]==$picheight && $filesize[0]==$picwidth){
			copy($oldimg,$newimg);
			return 1;
		}
		if ($picwidth && !$picheight){
			$picheight = $filesize[1]*$picwidth/$filesize[0];
		}else if (!$picwidth && $picheight){
			$picwidth = $filesize[0]*$picheight/$filesize[1];
		}

		$p = ($picwidth/$picheight);
		if($filesize[0]/$filesize[1] > $p){ //说明宽度太大
			$newheight = $filesize[1];
			$newwidth = $newheight*$p;
		}else{
			$newwidth = $filesize[0];
			$newheight = $newwidth/$p;
		}
		if ($nextname != 'gif' && function_exists('imagecreatetruecolor')) {
			$output = imagecreatetruecolor($picwidth,$picheight);
		}else{
			$output = imagecreate($picwidth,$picheight);
		}
		if (function_exists('imagecopyresampled')){
			imagecopyresampled($output,$im,0,0,0,0,$picwidth,$picheight,$newwidth,$newheight);
		} else{
			imagecopyresized($output,$im,0,0,0,0,$picwidth,$picheight,$newwidth,$newheight);
		}
		switch($nextname){
			case "jpg" :
			case "jpeg":
				$result = imagejpeg($output, $newimg , $quality);
				break;
			case "gif" :
				$result = imagegif($output, $newimg);
				break;
			case "png" :
				$result = imagepng($output, $newimg);
				break;
			default    :
				$result = 0;
				break;
		}
		imagedestroy($output);
		if ( $result ){
			return 1;
		}else{
			return 0;
		}
	}
	
	function showerror($error,$errornum) {
		if($this->type=='ajax' && $errornum) {
			header('HTTP/1.0 ' . $errornum.$error);
			die('Error ' . $errornum.$error);
		}else {
			Showmsg($error);
		}
	}
}

class getContent{
	var $data;

	function copyFile($from,$to){
		if(strpos($to,"..")!==false) return ;
		if(ini_get('allow_url_fopen')){
			copy($from,$to);
			/*
			$this->data = file_get_contents($from);
			writeover($to,$this->data);
			*/
		}else{
			$this->open($from);
			$this->send();
			$this->getUrlContent();
			if($this->data) {
				writeover($to,$this->data);
			}else {
				return false;
			}
			
		}
		return true;
	}

	function open($url){
		$path = parse_url($url);
		$this->host=$path['host'];
		$this->port=$path['port'];
		$this->path=$path['path'];
		if($path['query']) $this->path .= "?".$path['query'];
		if(empty($this->port)){
			$this->port=80;
		}elseif ($path['scheme']=='https'){
			$this->port=443;
		}elseif ($path['scheme']=='http'){
			$this->port=80;
		}
		$this->scheme = $this->port==80 ? "http://" : "https://";
		$this->connect(); //开始连接
	}

	function connect(){
		$timeout = 10;
		$errorno = 0;
		$errorstr= '';

		$this->fp=@fsockopen($this->host,$this->port,$errorno,$errorstr,$timeout);
		if(!$this->fp){
			return false;
		}
	}

	/**
	 * 向远程主机写入信息
	 *
	 */
	function send(){
		if(!$this->fp){
			return false;
		}
		$user_agent=$_SERVER['HTTP_USER_AGENT'];
		$http="GET $this->path HTTP/1.1\r\n";
		$http.="Host: $this->host:$this->port\r\n";
		$http.="Accept:*/*\r\nAccept-Encoding: identity\r\n";
		$http.="User-Agent: $user_agent\r\n\r\n";
		fputs($this->fp,$http);
	}

	/**
	 * 获取网址中的页面内容
	 *
	 */
	function getUrlContent(){
		if(!$this->fp){
			return false;
		}
		while (!feof($this->fp)){
			$this->data .= fgets($this->fp,8192);
		}
		fclose($this->fp); //关闭连接
		$pos = strpos($this->data,"\r\n\r\n");
		$this->data = trim(substr($this->data,$pos+4)); //截取头信息
	}

	/**
	 * 静态调用方法
	 *
	 * @param string $from 来源网址
	 * @param string $to 目标文件
	 */
	function copy($from,$to){
		$getContent = new getContent();
		if(!$getContent->copyFile($from,$to)){
			return false;
		}else{
			return true;
		}
	}

}
?>