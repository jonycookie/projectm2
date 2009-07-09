<?php
!defined('IN_CMS') && die('Forbidden');
/* 本类获取一个目录下所有文件并以固定格式返回 */
class path {
	var $direct;
	var $viewurl;
	var $fileurl;
	var $thepath; //当前路径，绝对路径
	var $currentpath; //当前路径，相对路径

	function __construct($direct){ //设定要访问的根目录
		$this->direct = $direct;
	}

	function path($direct){
		$this->__construct($direct);
	}

	function setDir($direct){ //设置子目录
		$this->currentpath = $direct;
		if($direct){
			$this->thepath = $this->direct.'/'.$direct;
		}else{
			$this->thepath = $this->direct;
		}
	}

	function up(){
		if($this->thepath==$this->direct){ //防止跨越目录，此设置防止
			return ;
		}
		$len = strlen(end(explode("/",$this->thepath)));
		$len++; //把斜杠的这一个长度也要计算进去才能去掉完整的最下层目录名得到其上级目录
		$this->thepath= substr($this->thepath,0,-$len);
		$this->currentpath = substr($this->currentpath,0,-$len);
	}

	function getDir(){ //获取到目录内容
		if(strpos($this->thepath,$this->direct)!==0){ //目录安全需要，比较目录是否跨越了根目录之外
			$this->thepath = $this->direct;
			$this->currentpath = '';
		}
		$files = array();
		$files_img = "images/admin/file/";
		$this->currentpath && $currentpath = $this->currentpath.'/';
		$d = opendir($this->thepath);
		while ($filename = readdir($d)) {
			if($filename=='.' || $filename=='..') continue;
			if(is_dir($this->thepath.'/'.$filename)){
				$files[]=array(
					'type'=>'dir',
					'name'=>$filename,
					'icon'=>$files_img."dir.gif",
					'size'=>'',
					'time'=>'',
					'url'=>$this->viewurl."direct=".$currentpath.$filename,
					'disable'=>"disabled",
				);
			}else{
				$fileext = end(explode('.',$filename));
				$icon = $files_img.$fileext.".gif";
				!file_exists(D_P.$icon) && $icon=$files_img."none.gif";
				$size = filesize($this->thepath.'/'.$filename);
				$size = floor($size/1024);
				$filetime = filemtime($this->thepath.'/'.$filename);
				$filetime = get_date($filetime,"y-m-d H:i");
				$files[]=array(
					'type'=>$fileext,
					'name'=>$filename,
					'icon'=>$icon,
					'size'=>$size." kB",
					'time'=>$filetime,
					'url'=>"javascript:".$this->fileurl."('".$currentpath.$filename."');",
					'disable'=>'',
				);
			}
		}
		closedir($d);
		usort($files,'compare');
		return $files;
	}
}

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