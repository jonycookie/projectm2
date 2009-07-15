<?php
!defined('IN_ADMIN') && die('Forbidden');
InitGP(array('action','job','step'));
$allow_ext = array('htm','html');
if(!$action){
	$directory = GetGP('directory');
	if($job=='up'){
		$len = strlen(end(explode("/",$directory)));
		$len++;
		$directory = substr($directory,0,-$len);
	}
	if(!$directory){
		$directory = $user_tplpath;
	}else{
		$path = $directory;
		$directory = "$user_tplpath/$directory";
	}
	$files = array();
	$files_img = "images/admin/file/";
	$d = dir($directory);
	while ($filename = $d->read()) {
		if($filename=='.' || $filename=='..') continue;
		if(is_dir($directory.'/'.$filename)){
			$files[]=array(
				'type'=>'dir',
				'name'=>$filename,
				'icon'=>$files_img."dir.gif",
				'size'=>'',
				'time'=>'',
				'url'=>$basename."&directory=".$path."/".$filename,
			);
		}else{
			$fileext = end(explode('.',$filename));
			$icon = $files_img.$fileext.".gif";
			!file_exists(D_P.$icon) && $icon=$files_img."none.gif";
			$size = filesize($directory.'/'.$filename);
			$size = floor($size/1024);
			$filetime = filemtime($directory.'/'.$filename);
			$filetime = get_date($filetime,"y-m-d H:i");
			$files[]=array(
				'type'=>$fileext,
				'name'=>$filename,
				'icon'=>$icon,
				'size'=>$size." kB",
				'time'=>$filetime,
				'url'=>"$basename&action=edit&path=$path&file=$filename",
			);
		}
	}
	$d->close();
	usort($files,'compare');
}elseif ($action=='edit'){
	InitGP(array('path','filepath'));
	if(!$step){
		$url = urlencode($basename);
		if($path){
			$filepath = "$user_tplpath/$path/$file";
		}else{
			$filepath = "$user_tplpath/$file";
		}
		if(strpos($filepath,'..')) die('Forbidden');
		$filecontent = readover($filepath);
	}elseif ($step==2){
		$filecontent = $_POST['filecontent'];
		if(strpos($filepath,$user_tplpath)!=0 || strpos($filepath,"..") || !in_array(strtolower(end(explode('.',$filepath))),$allow_ext)){
			Showmsg('undefined_action');
		}
		$filecontent = stripslashes($filecontent);
		writeover($filepath,$filecontent);
		adminmsg('file_editok');
	}
}elseif ($action=='new'){
	InitGP(array('filename','path'));
	$url = urlencode($basename);
	if($step==2){
		$filepath = '';
		$filecontent = $_POST['filecontent'];
		!ereg("^[0-9a-zA-Z_\-]+$",$filename) && Showmsg('file_filenameerror');
		empty($filecontent) && Showmsg('file_nofilecontent');
		if($path){
			$filepath = "$user_tplpath/$path/$filename";
		}else{
			$filepath = "$user_tplpath/$filename";
		}
		$filepath = $filepath.'.htm';
		$filepath = Pcv($filepath);
		$filecontent = stripslashes($filecontent);
		writeover($filepath,$filecontent);
		adminmsg('file_newok');
	}
}elseif ($action=='rename'){
	InitGP(array('path','filename','oldfilename'));
	if($path){
		$filepath = "$user_tplpath$path/";
	}else{
		$filepath = "$user_tplpath/";
	}
	$newname = $filepath.$filename;
	$oldname = $filepath.$oldfilename;
	if(is_dir($oldname)){
		if(!ereg("^[0-9a-zA-Z_\-]+$",$filename)){
			echo "formaterror";
			exit();
		}
	}else{
		if(!ereg("^[0-9a-zA-Z_\-]+\.htm$",$filename)){
			echo "formaterror";
			exit();
		}
	}
	$newname = Pcv($newname);
	$oldname = Pcv($oldname);
	if(!rename($oldname,$newname)){
		echo "renamefail";
		exit();
	}else{
		echo "ok";
		exit();
	}
}elseif ($action=='del'){
	InitGP(array('path','file'));
	if($path){
		$filepath = "$user_tplpath$path/";
	}else{
		$filepath = "$user_tplpath/";
	}
	$filename=$filepath.'/'.$file;
	$filename = Pcv($filename);
	if(!unlink($filename)){
		Showmsg('file_unlinkfail');
	}else{
		adminmsg('file_unlinkok');
	}
}elseif ($action=='mkdir'){
	InitGP(array('filename','directory'));
	empty($filename) && Showmsg('file_nofilename');
	!ereg("^[0-9a-zA-Z_\-]+$",$filename) && Showmsg('file_filenameerror');
	if(!$directory){
		$directory = $user_tplpath;
	}else{
		$path = $directory;
		$directory = "$user_tplpath/$directory";
	}
	$newdir = $directory."/$filename";
	$newdir = Pcv($newdir);
	mkdir($newdir);
	chmod($newdir,0777);
	adminmsg('file_mkdirok',"$basename&directory=$path");
}
require PrintEot('header');
require PrintEot('file_tpl');
adminbottom();

function compare($a,$b){
	if($a['type']=='dir' && $b['type']=='dir'){
		return strcasecmp($a['name'],$b['name']);
	}elseif ($a['type']=='dir' && $b['type']!='dir'){
		return -1;
	}elseif ($a['type']!='dir' && $b['type']=='dir'){
		return 1;
	}else{
		return strcasecmp($a['name'],$b['name']);
	}
}
?>