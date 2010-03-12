<?php

class webftp_class{

private $path='webftp'; //当前模块目录
private $dbver=100; //当前模块的数据库版本

public function __construct(){
	global $king;

	$dbver=(int)$king->getModuleVer($this->path);
	if($this->dbver > $dbver){
		if($this->install_update($dbver)){//成功则返回True，则更新数据库中的版本信息
			$array=array(
				'ndbver'=>$this->dbver,
			);
			$king->db->update('%s_module',$array,"kpath='{$this->path}'");
		}
	}
}

/* ------>>> 安装部分 <<<---------------------------- */

public function install(){
	global $king;

	$this->install_update(100);

	//写模块安装记录
	if(!$king->db->getRows_one('SELECT * FROM %s_module where kpath=\''.$this->path.'\';')){
		$_array=array(
			'kname' =>$king->lang->get($this->path.'/name'),
			'kpath' =>$this->path,
			'kdb'   =>'',
			'ndate' =>time(),
			'ndbver'=>$this->dbver,
			'norder'=>$king->db->neworder('%s_module'),
			'nshow'=>0,
			);
		$kid1=$king->db->insert("%s_module",$_array);

		return true;
	}else{
		return false;
	}
}

public function install_update($ver){


	return True;
}

}

?>
