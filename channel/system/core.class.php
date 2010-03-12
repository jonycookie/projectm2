<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

 /*   系统后台框架


 +	  成员变量

	@param public  bool $isHtm    是否输出页脚HTML代码
		True : 输出
		False: 不输出(默认)
	@param public  object $lang    语言包对象
	@param public  array  $admin   管理员信息
		adminid      : 管理员id
		adminname    : 管理员名称
		adminlevel   : 管理员访问权限
		admineditor  : 使用的编辑器
		adminpass    : 加密后的密码
		adminmode    : 模式
			2: 完全模式(标签/Meta信息)
			1: 专业录入员模式(Meta信息)
			0: 入门使用者模式
		adminskins   : 风格
		adminlanguage: 语言
	@param  private $mList         openList和closeList间传递列表结束HTML值


*/


class KingCMS_class{

//public  $isHtm = False;	//是否输出完整HTML，当执行tophtm($_title)的时候为True,默认为False
public  $lang;//语言
public  $cache;
public  $skin;
public  $db;
public  $admin=array();	//管理员信息
private $mList;	//List的内部函数
private $arrayInfo=array();
private $isMng=0;//前后台判断默认0为前台
public  $holdmodule=array('system','nav','pagelist','list',
	'menu','menu1','menu2','menu3','menu4','menu5',
	'const','skin','home','login','default','data');//需要保护的模块目录
public  $devname='2010 Released';//版本名称
public  $version='6.0.808';//内部版本名称

private $dbver=104;//当前系统数据库版本


public function __construct(){

	kc_script_runtime();

	$this->lang=new KC_Language_class;

	$dbClassName="KC_".DB_TYPE."_class";

	$this->db=new $dbClassName;

	$this->cache=new KC_Cache_class;

	$this->skin=new KC_Skin_class;

}
public function __destruct(){

	

	//注销大型数组
	if(!empty($GLOBALS['file_get_contents_array']))
		unset($GLOBALS['file_get_contents_array']);

}
private function install_update($ver){
	if($ver<101){//版本101或更小版本的时候升级
		$sql='ktitle varchar(100) NULL';
		$this->db->alterTable('%s_upfile',$sql);
	}

	if($ver<102){
		if($res=$this->db->getRows_one("select norder,cid from %s_system where kname='rewriteend'")){
			$array=array(
				'cid'=>$res['cid'],
				'kname'=>'rewritetag',
				'kmodule'=>'system',
				'kvalue'=>'0',
				'ntype'=>4,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=> ++$res['norder'],
			);
			$this->db->insert('%s_system',$array);
		}
	}

	if($ver<103){
		$sql='kname char(50) not null,
		ksign char(32) null,
		urlpath char(255) not null,
		connid int not null default 0,
		norder int not null default 0,
		INDEX(connid)';
		$this->db->createTable('%s_conn',$sql,'kid');
	}

	if($ver<104){
		$sql="nshow tinyint(1) not null default 1";
		$this->db->alterTable('%s_module',$sql);
	}

	return True;

}
/**
	页面执行引擎
*/
public function pageEngine(){
	global $action;

	$pself=strtolower($_SERVER['PHP_SELF']);
	if(False===strpos($pself,'install.php')){//如果没有数据链接，则不做验证
		//先升级数据库
		$dbver=(int)$this->config('dbver');
		if($this->dbver > $dbver){
			if($this->install_update($dbver)){//成功则返回True，则更新数据库中的版本信息
				$array=array(
					'kvalue'=>$this->dbver,
				);
				$this->db->update('%s_system',$array,"kname='dbver'");
				$this->cache->del('system/config/system');
				$GLOBALS['file_get_contents_array']=array();//重写内存里的缓存
			}
		}
		if(!($this->config('switch'))){//网站开关
			if((False===strpos($pself,'manage.')) && (False===strpos($pself,'system/login.php')) && (False===strpos($pself,'install.php'))){
				kc_error($this->lang->get('system/error/siteclose'));
			}
		}
	}


	if(!defined('KC_INDEX')){
		$path=basename(dirname($_SERVER['PHP_SELF']));

		if(!in_array($path,array('system'))){
			$this->Load($path);
		}
	}

	if(!defined('KC_CALL_FUNC')){
		if($action=='ajax' || $action=='iframe'){
			$func="king_{$action}_".CMD;
			if((function_exists($func))){
				call_user_func($func);
			}else{
				kc_error($this->lang->get('system/error/func'));
			}
		}else{
			$func= $action==''?'king_def':"king_$action";
			if(function_exists($func)){
				call_user_func($func);
			}else{
				kc_error($this->lang->get('system/error/func'));
			}
		}

	}
}
/**
	加载模块
	@param
	@return
*/
public function Load($module){
	//判断这个module是否存在，若不存在则提示安装提示

	$modules=explode(',',$module);

	foreach($modules as $val){
		//检查是否在模块列表里
		if(!$this->isModule($val)){
			//如果不在模块列表里，则判断是否被锁定
			if($res=$this->db->getRows_one('SELECT islock FROM %s_module where kpath=\''.$this->db->escape($val).'\';')){
				if($res['islock']){//如果被锁定，则输出错误提示
					kc_error($this->lang->get('system/error/lockmodule'));
				}
			}

			//输出安装提示？

			kc_error($this->lang->get('system/module/tip').'<br/><br/><a href="../system/manage.php?action=module&module='.$val.'">'.$this->lang->get('system/module/install').' : '.$val.'</a>');
		}
		//加载core.class.php，并$this->$val=new $val;
		if(is_file(ROOT.$val.'/core.class.php')){
			require_once(ROOT.$val.'/core.class.php');
			$classname=$val.'_class';
			$this->$val=new $classname();
		}
	}

}
/**

判断模块是否已经被安装

@param string $name  : 模块名称

@return bool

*/
public function isModule($name){
	return in_array(strtolower($name),$this->getModule())||$name=='system';
}
/**

返回模块列表

@return array

*/
public function getModule($nshow=NULL){

	$insql='';

	if($nshow===NULL){
		$path='system/module/all';
	}elseif((int)$nshow===1){
		$path='system/module/1';
		$insql=" and nshow=1";
	}else{
		$path='system/module/0';
		$insql=" and nshow=0";
	}

	$module=$this->cache->get($path);
	if($module){
		return $module;
	}else{
		if(!$res=$this->db->getRows("select kpath from %s_module where islock=0 $insql order by norder,kid"))
			$res=array();
		$array=array();
		foreach($res as $rs){
			$array[]=$rs['kpath'];
		}
		$this->cache->put($path,$array);
		return $array;
	}

}

/**

系统信息

@param string $name  网站参数
@return array

*/
public function config($name,$module='system'){

	$cachepath='system/config/'.$module;

	if($module==null) $module='system';

	if(!array_key_exists($module,$this->arrayInfo)){//数据库加载

		$this->arrayInfo[$module]=$this->cache->get($cachepath,time()-300);
		if(!$this->arrayInfo[$module]){
			if(!$res=$this->db->getRows("select kname,kvalue,ntype from %s_system where kmodule='{$module}';"))
				$res=array();
			foreach($res as $val){
				$val['ntype']==3
					? $this->arrayInfo[$module][$val['kname']]=$val['kvalue']
					: $this->arrayInfo[$module][$val['kname']]=$val['kvalue'];
			}
			$this->cache->put($cachepath,$this->arrayInfo[$module]);
		}
	}
	return $this->arrayInfo[$module][$name];
}
/**

管理员访问权限验证
@param string $_level  访问验证参数，如果是多重访问验证，则用逗号分开
@param 上台领 $_title  输出HTML页面头信息
@return string

*/
public function access($_level){

	list($_name,$_pass)=isset($_COOKIE['KingCMS_Admin']) ? kc_explode("\t" , $_COOKIE['KingCMS_Admin'],2) : array(NULL,NULL);

	if(isset($_name{0}) && isset($_pass{0})){//判断COOKIE是否存在
	}else{	 //若COOKIE值为空
		kc_error($this->lang->get("system/error/logintimeout"));
	}

	//读取cache验证一下账号密码

	if($this->cache->get('system/admin/'.$_name,1)){//从缓存读取,管理员不用分组
		$this->admin=$this->cache->get('system/admin/'.$_name);
		$isCache=true;
	}else{//从数据库读取
		$_sql="adminid,adminname,adminlevel,admineditor,adminpass,adminmode,adminskins,adminlanguage,siteurl";    //$admin[]
		$_sql="select $_sql from %a_admin where adminname='".$this->db->escape($_name)."';";
		$this->admin=$this->db->getRows_one($_sql);
		$this->admin['cookiepass']=md5($_name.$this->admin['adminpass']);
		$isCache=false;
	}

	if($this->admin['cookiepass']==$_pass){//如果密码一致
		if(!$this->acc($_level))
			kc_error($this->lang->get('system/error/level'));
		if(!$isCache){
			$this->cache->put('system/admin/'.$_name,$this->admin);
		}

		$this->isMng=1;

	}else{//密码不一致

		//写登陆错误log 尝试用已存在的帐号来登陆
		$this->log(4,$_name);

		kc_error($this->lang->get('system/error/login'));
	}

}
/**

管理员操作权限验证

@param string $_level  当前操作的所需权限列表，若是多重访问权限验证，则用逗号分开

@return bool

*/
public function acc($_level){
	if($this->admin['adminlevel']=='admin'){
		return true;
	}else{
		$_array_admin=explode(',',$this->admin['adminlevel']);
		$_array_level=explode(',',$_level);
		foreach($_array_level as $_value){
			//没有对应的权限，则输出错误提示
			if(!in_array($_value,$_array_admin))
				return false;
		}
	}
	return true;
}

/**
	进度条
	@param string $id        进度条的ID
	@param string $title     进度条中显示的内容
	@param int    $num       当前项目数
	@param int    $count     项目总数

	@$_GET['time'] 函数外参数,页面开始执行时间,必须值
*/
public function progress($id='progress',$title=null,$num=0,$count=1){

	if(!$title)
		$title=$this->lang->get('system/progress/loading');

	list($_msec,$_sec)=explode(' ',microtime());
	$thistime=$_sec+$_msec;

	switch($num){
		case 0:
			$count===0
				? $s='<script>window.parent.$.kc_progress(\''.$id.'\',\''.$this->lang->get('system/progress/ok').' ('.$this->lang->get('system/progress/not').')\',300)</script>'
				: $s='<p class="k_progress"><span><em id="'.$id.'" style="width:0px;"><i>'.$title.' - 0%</i></em></span></p>';

		break;

		case $num>=$count:
//			$diffstart=$thistime-$GLOBALS['KC_START_SCRIPT_RUNTIME'];//开始时间差
			$diffstart=$thistime-$_GET['time'];//开始时间差
			$str=$this->lang->get('system/progress/alltime').': '.kc_formattime($diffstart);
			$str.='; '.$this->lang->get('system/progress/aver').': '.kc_formattime($diffstart/$count);
			$s='<script>window.parent.$.kc_progress(\''.$id.'\',\''.$this->lang->get('system/progress/ok').' ('.$count.'/'.$count.') '.$str.'\',300)</script>';
		break;

		default:

			//判断客户端的链接状态
			if(connection_aborted()) exit;

			$prop=$num/$count;

			$percent=round($prop*100,1);//百分比

			list($_msec,$_sec)=explode(' ',microtime());
			$time=$_sec+$_msec;//当前时间

			if(empty($GLOBALS['KC_PROGRESS_TIME'])){//有上次执行时间
				$GLOBALS['KC_PROGRESS_TIME']=$GLOBALS['KC_START_SCRIPT_RUNTIME'];
			}
			$timediff=$time-$GLOBALS['KC_PROGRESS_TIME'];//单次运行时差
			$GLOBALS['KC_PROGRESS_TIME']=$time;//设置时间


			$diff_this=$time-$_GET['time'];//当前到最初的时间差

			$str=$this->lang->get('system/progress/remainder').': '.kc_formattime(($diff_this/$num)*($count-$num));

			$s='<script>window.parent.$.kc_progress(\''.$id.'\',\''.addslashes($title).' ('.$num.'/'.$count.') '.$str.' - '.$percent.'%\','.round($prop*300).')</script>';

			usleep((int)($this->config('proptime')*$timediff*1000000));

			list($_msec,$_sec)=explode(' ',microtime());
			$GLOBALS['KC_PROGRESS_TIME']=$_sec+$_msec;//重新设置时间
	}

	$GLOBALS['KC_LAST_PROGRESS_RUNTIME']=$thistime;//设置当前时间

	return $s;
}
/**
	添加日志
	@param string $nlog  日志类型
		1  成功登录
		2  登录失败
		3  注销
		4  非法登录
		5  新建
		6  删除
		7  编辑
	@param string $text  内容
	@return
*/
public function log($nlog,$text){

	if($nlog<=4){
		$adminname=$text;
	}else{
		$adminname=$this->admin['adminname'];
	}

	$_array=array(
		'adminname' =>$adminname,
		'nip'       =>kc_getip(),
		'nlog'      =>$nlog,
		'ndate'     =>time(),
		'ktext'     =>$text,
		);
	$this->db->insert("%s_log",$_array);
}


/**
	解析表单项目
	@param array $rs    数据构成
	@param array $data  表单值
	@return string

	$rs['ntype']值
	1  单行文本
	2  多行文本(不支持编辑器)
	3  多行文本 (支持编辑器)
	4  单选 (下拉列表)
	5  单选 (radio)
	6  多选 (多选列表)
	7  复选框
	8  文件上传(图片类型)
	9  图片列表
	10 文件上传(文件类型)
	11 文件列表
	12 颜色框
	13 选择模板
*/
public function formdecode($rs,$data){
	$c=array();
	$s='';
	$h=kc_help($rs['khelp']?$rs['kmodule'].'/'.$rs['khelp']:'');

	switch($rs['ntype']){
	case 1:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		if($rs['nvalidate']!=0)
			$c[]=array($rs['kname'],$rs['nvalidate']);
		$s='<input type="text" name="'.$rs['kname'].'" id="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
		$h.=kc_htm_setvalue_nl($rs['kname'],$rs['koption']);
	break;

	case 2:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$s='<textarea name="'.$rs['kname'].'" class="k_in" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;" >'.htmlspecialchars($data[$rs['kname']]).'</textarea>';
	break;

	case 3:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$s=kc_htm_editor($rs['kname'],$data[$rs['kname']],$rs['nstylewidth'],$rs['nstyleheight']);
	break;

	case 4:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$array_def=explode(NL,$rs['koption']);
		$array_select=array();
		foreach($array_def as $val){
			if(isset($val{0})){//不能为空值
				$array_val=explode('|',$val,2);
				if(count($array_val)>1){//有分割符号
					$array_select+=array($array_val[0]=>$array_val[1]);
				}else{
					$array_select+=array($val=>$val);
				}
			}
		}
		$s=kc_htm_select($rs['kname'],$array_select,$data[$rs['kname']]);
	break;

	case 5:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$array_def=explode(NL,$rs['koption']);
		$array_radio=array();
		foreach($array_def as $val){
			if(isset($val{0})){//不能为空值
				$array_val=explode('|',$val,2);
				if(count($array_val)>1){//有分割符号
					$array_radio+=array($array_val[0]=>$array_val[1]);
				}else{
					$array_radio+=array($val=>$val);
				}
			}
		}
		$s=kc_htm_radio($rs['kname'],$array_radio,$data[$rs['kname']]);
	break;


	case 6:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$array_def=explode(NL,$rs['koption']);
		$array_select=array();
		foreach($array_def as $val){
			if(isset($val{0})){//不能为空值
				$array_val=explode('|',$val,2);
				if(count($array_val)>1){//有分割符号
					$array_select+=array($array_val[0]=>$array_val[1]);
				}else{
					$array_select+=array($val=>$val);
				}
			}
		}
		$s=kc_htm_select($rs['kname'],$array_select,$data[$rs['kname']],' multiple="multiple" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;"');
	break;

	case 7:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$array_def=explode(NL,$rs['koption']);
		$array_radio=array();
		foreach($array_def as $val){
			if(isset($val{0})){//不能为空值
				$array_val=explode('|',$val,2);
				if(count($array_val)>1){//有分割符号
					$array_radio+=array($array_val[0]=>$array_val[1]);
				}else{
					$array_radio+=array($val=>$val);
				}
			}
		}
		$s=kc_htm_checkbox($rs['kname'],$array_radio,$data[$rs['kname']]);
	break;

	case 8:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$s='<input type="text" name="'.$rs['kname'].'" id="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
		$h.=kc_f_brow($rs['kname'],$this->config('uppath').'/image',0);
	break;

	case 9:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);

		$s='<input type="hidden" id="'.$rs['kname'].'" name="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'"/>';
		$s.='<table class="k_side" cellspacing="0"><tr><td id="k'.$rs['kname'].'show" class="k_in imglist" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;">';
		$s.='Loading...';
		$s.='</td><td>';
		$s.=kc_f_brow($rs['kname'],$this->config('uppath').'/image',0,1,$jsfun='k'.$rs['kname'].'show();');
		$s.='</td></tr></table>';
		$s.=kc_help($rs['khelp']?$rs['kmodule'].'/'.$rs['khelp']:'');
		$s.="<script>function k{$rs['kname']}show(){\$.kc_ajax('{CMD:\'imageload\',label:\'{$rs['kname']}\',VAL:\'{$rs['kname']}\',listid}\'}');k{$rs['kname']}show();</script>";

	break;

	case 10:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$s='<input type="text" name="'.$rs['kname'].'" id="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
		$h.=kc_f_brow($rs['kname'],$this->config('uppath').'/file',1);
	break;

	case 11:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);

		$s='<input type="hidden" id="'.$rs['kname'].'" name="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'"/>';
		$s.='<table class="k_side" cellspacing="0"><tr><td id="k'.$rs['kname'].'show" class="k_in filelist" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;">';
		$s.='Loading...';
		$s.='</td><td>';
		$s.=kc_f_brow($rs['kname'],$this->config('uppath').'/file',1,1,$jsfun='k'.$rs['kname'].'show();');
		$s.='</td></tr></table>';
		$s.=kc_help($rs['khelp']?$rs['kmodule'].'/'.$rs['khelp']:'');
		$s.="<script>function k{$rs['kname']}show(){\$.kc_ajax('{CMD:\'filesload\',label:\'{$rs['kname']}\',VAL:\'{$rs['kname']},listid\',ID:\'k{$rs['kname']}show\'}')};k{$rs['kname']}show();</script>";
	break;

	case 12:
		$c[]=array('kcolor',0,6,6);
		$s='<input class="k_in w50" type="text" id="'.$rs['kname'].'" name="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'" maxlength="6" />';
		$h.=kc_f_color($rs['kname']);
	break;

	case 13:
		$c[]=array($rs['kname'],0,$rs['nsizemin'],$rs['nsizemax']);
		$s='<input type="text" name="'.$rs['kname'].'" id="'.$rs['kname'].'" value="'.htmlspecialchars($data[$rs['kname']]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
		$h.=kc_f_brow($rs['kname'],$this->config('templatepath'),2);
	break;

	}

	$htm=$this->htmForm($this->lang->get($rs['kmodule'].'/const/'.$rs['kname']),$s,$c,'config.'.$rs['kmodule'].'.'.$rs['kname'],$h);

	return $htm;

}
/* ------>>> GET 函数 <<<---------------------------- */

/**
	对URL进行数组处理

	@return array
		url       : This SERVER_NAME
		classname : This ClassName
		path_info : $_SERVER['PATH_INFO']值

*/
public function getUrl(){
	$path_info=isset($_SERVER['PATH_INFO'])
		? $_SERVER['PATH_INFO']
		:(isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');

	if( isset($path_info{0}) && False!==strpos($_SERVER['SERVER_SOFTWARE'],'IIS') ) $path_info=iconv('GBK','UTF-8',$path_info);

	if(substr($path_info,0,10)=='/index.php'){
		$path_info=substr($path_info,10);
	}

	if($path_info==''||$path_info=='/'){

		$array=array(
			'url'=>$_SERVER['SERVER_NAME'],
			'classname'=>'portal_class',
			'path_info'=>'',
		);
	}else{
		$url=substr($path_info,1,strlen($path_info)-strlen($this->config('rewriteend'))-1);
		if(substr($url,0,10)=='index.php/')
			$url=substr($url,10);

		$array=kc_explode($this->config('rewriteline'),$url,4);

		$array['url']=$_SERVER['SERVER_NAME'];

		in_array($array[0],array('page','list','tag'))
			? $module='portal'
			: $module=$array[0];

		$array['classname']=$module.'_class';
		$array['path_info']=$path_info;
		//判断是否存在这个模块
		if(!$this->isModule($module))
			kc_error($this->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__.'<br/>'.$module.'<br/>'.$path_info);
	}

	return $array;
}
/**

	判断文件与否，并输出格式化路径

	@param string $path   路径或文件名

*/
public function getFpath($path){

	if(substr($path,-1,1)=='/'){
		$path.=$this->config('file');
	}
	return $path;
}
/*
*/
/**

	根据文件类型返回文件扩展名

	@param int   $type
		0: 图片类型(png|jpg|gif...)
		1: 文件类型(zip|rar...)
		2: 模板类型(htm|html...)

*/
public function getFext($type){

	switch($type){
		case 0:$ftype=$this->config('upimg');break;
		case 1:$ftype=$this->config('upfile');break;
		case 2:$ftype=$this->config('templateext');break;
		default:;
	}
	return $ftype;
}
/**
	返回系统参数
	@param string $name
	@return string
*/
public function getConfig($name){
	list($module,$param)=explode('.',$name);
	return $this->config($param,$module);
}
/**
	返回模块版本
	@param string $module
	@return int
*/
public function getModuleVer($module){
	$cachepath='system/modulever';
	if(!$array=$this->cache->get($cachepath,1)){
		if($res=$this->db->getRows("select kpath,ndbver from %s_module")){
			foreach($res as $rs){
				$array[$rs['kpath']]=$rs['ndbver'];
			}
		}
		$this->cache->put($cachepath,$array);
	}
	return isset($array[$module]) ? $array[$module] : 100;
}


/* ------>>> 构造列表 <<<---------------------------- */

/**

列表 - 开始

@param array $_cmd     命令列表
	array(
		$key=>$value,
		'delete'=>'删除',
		'-',
		'create'=>'生成',
	)
@param array $right    右键菜单
	array(
		
	);
@param array $_js      构造javascript函数function ll()
	array(
	$value,
	每个值对应的是一个HTML标签：<td>$value</td>
	)
@param string $_plist  分页HTML代码
@param array  $_val    预设的隐藏域 及值

@return string

*/
public function openList($_cmd=null,$right=array(),$_js=null,$_plist=null,$_ext=array()){


	$i=0;
	$fly='';
	$s ='<form id="k_form_list" name="k_form_list">'.kc_htm_hidden($_ext).'<script type="text/javascript">var REQUEST_URL=\''.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'\';';

	$s.='function kc_button(){var I1=\'';
	$s.='<table cellspacing="0" class="k_button"><tr><td><div class="k_submit">';

	if(is_array($_cmd)){
		$s_but='<span class="select"><a href="javascript:;" class="k_aselect" >'.addslashes($this->lang->get('system/common/aselect')).'</a>/';//onClick="kc_aselect()"
		$s_but.='<a href="javascript:;" class="k_rselect">'.addslashes($this->lang->get('system/common/rselect')).'</a></span>';
		$but='';

		$cmd="<a href=\"javascript:;\" class=\"k_cmd\">".$this->lang->get('system/common/morecmd')."</a>";

		$fly='<ul id="k_cmd_Fly" style="display:none;">';
		$is=False;
		foreach($_cmd as $key => $val){
			if(kc_validate($key,2)){
				if($val=='-'){
					$is=True;
				}else{
					$fly.='<li class="hr2">'.($val).'</li>';
				}
			}else{
				$fly.='<li'.($is?' class="hr1"':'').'><a href="javascript:;" class="k_ajax" rel="{CMD:\''.$key.'\',FORM:\'k_form_list\'}">'.$val.'</a></li>';
				$is=0;
				if($key=='create'){//有create的时候，设置but
					$but.='<a href="javascript:;" class="button k_ajax" rel="{CMD:\\\''.$key.'\\\',FORM:\\\'k_form_list\\\'}">'.addslashes($this->lang->get('system/common/create')).'</a>';
				}
				if($key=='delete'||substr($key,0,7)=='delete_'){//有delete的时候，设置but
					$but.='<a href="javascript:;" class="button k_ajax" rel="{CMD:\\\''.$key.'\\\',FORM:\\\'k_form_list\\\'}">'.addslashes($this->lang->get('system/common/del')).'</a>';
				}
			}
		}
		$fly.='</ul>';

		$s.=$s_but.$but.(count($_cmd)==1&&isset($but{0})?'':$cmd);

	}
	if(is_array($right)){
		$fly.="<div id=\"k_list_right_Fly\" class=\"none\" onClick=\"\$(this).fadeOut(300)\">";
		foreach($right as $key => $val){
			if($val=='-'){
				$fly.='<i></i>';
			}else{
				if(is_array($val)){
					$ico=kc_val($val,'ico');
					$href=kc_val($val,'href');
				}else{
					$ico='';
					$href=$val;
				}
				if(substr($href,0,1)=='{' || substr($href,-1,1)=='}'){//ajax操作
					$fly.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"$href\">".kc_icon($ico).$key."</a>";
				}else{
					$fly.="<a href=\"$href\">".kc_icon($ico).$key."</a>";
				}
			}
		}
		$fly.="<i></i>";
		$fly.="<a href=\"javascript:;\" onClick=\"\$('k_list_right_Fly').fadeOut(300)\">".kc_icon('k8').$this->lang->get('system/common/cancel')."</a>";
		$fly.="</div>";
	}

	$s.='</div></td>\'';//</div>
	if($_plist!=null)
		$s.='+\'<td>'.addslashes($_plist).'</td>\'';
	$s.='+\'</tr></table>\';return I1;};document.write(kc_button());';

	$s.='function ll(){var K=ll.arguments;if(K[K.length-1]==1){for(i=0;i<K.length-1;i++){if(K[i]!=\'manage\'){document.write(\'<th class="th\'+i+\'">\'+K[i]+\'</th>\')}else{document.write(\'<th class="th\'+i+\'">'.$this->lang->get('system/common/manage').'</th>\')}}}else{var II=\'<tr id="tr_\'+K[0]+\'">\''.NL;

	if(is_array($_js)){
		foreach($_js as $_value){
			$s.='+\'<td id="td_\'+K[0]+\'_'.(++$i).'">\'+$.kc_nbsp('.$_value.')+\'</td>\''.NL;
		}

	}

	$s.='+\'</tr>\';if(K[K.length-1]==0){document.write(II)}else{return II}}};'.NL;
	$s.='document.write(\'<table class="k_table_list" cellspacing="0" id="k_table_list">\');'.NL;

	$this->mList=NL.'document.write(\'</table>\'+kc_button());';

	$this->mList.='</script></form>'.$fly;//灰调显示按钮
	return $s;
}
/**
	功能参数同上，此为数据列表
	@param string $array 要输出的内容
	@param int $last  最后一个数字
	@return string
*/
public function tdList($array,$last=0){
	$s='ll(\'';
	$s.=implode('\',\'',$array);
	$s.='\','.$last.')';
	$s.= $last<2 ? ';'.NL :'';
	return $s;
}
/**
列表 - 结束
@return string
*/
public function closeList(){
	if($this->mList!=null)
		$s=$this->mList;
	return $s;
}

/* ------>>> 构造表单 <<<---------------------------- */
/**
	开始显示表单
	@param string $action  表单提交地址
	@param string $caption 表格名称
	@param int    $enctype 0：post类型，1：form-data类型 default：Ajax调用
	@return
*/
public function openForm($action=null,$caption=null,$enctype=0){

	$is=$this->isMng;

	switch((string)$enctype){
	case '0':
		$s= empty($action) ? '<form>' : '<form name="k_formlist" method="post" id="k_formlist" action="'.$action.'">';
	break;

	case '1':
		$s='<form name="k_formlist" method="post" id="k_formlist" action="'.$action.'" enctype="multipart/form-data">';
	break;

	default:
		$s="<form id=\"$enctype\" name=\"$enctype\">";
	}


	if($this->config('htmlframe'.$is)==1){//1代表的是table结构

		$s.=$caption? "<h3 class=\"caption\">$caption</h3>" : '';
		$s.='<table class="k_table_form" cellspacing="0">';
	}else{
		$s.=$caption ? "<h3 class=\"caption\">$caption</h3>" : '';
	}

	return $s;
}
/**
	拆分表单显示
	@param
	@return
*/
public function splitForm($caption=null){
	if($this->config('htmlframe'.$this->isMng)==1){//table结构
		$s='</table>'.($caption?"<h3 class=\"caption\">$caption</h3>":'').'<table class="k_table_form" cellspacing="0">';
	}else{
		$s=$caption ? "<h3 class=\"caption\">$caption</h3>" : '<p class="split"></p>';
	}
	return $s;
}
/**

	返回htm标签

	@param string $_label       Label标签中的内容
	@param string $_htmlcode    表单的主体内容
	@param array  $_arraycheck  表单验证，详见kc_check()
	@param string $_tag         表单对应的KingCMS标签
	@param string $_help        帮助

	@return string

*/
public function htmForm($_label=null,$_htmlcode=null,$_arraycheck=null,$_tag=null,$_help=null){

	if($this->config('htmlframe'.$this->isMng)==1 && (!in_array($GLOBALS['action'],array('ajax','iframe'))) ){//1代表的是table结构
		$s='<tbody><tr>';
		if(!empty($_label)){
			$s.='<th>';
			preg_match_all ("/^([^\(]+)((\(.*\))?)$/",$_label,$_label_array, PREG_PATTERN_ORDER);//拆分标题和提示内容
			$s.=$_label_array[1][0];

			$s.='</th>';
		}else{
			$s.="<th>&nbsp;</th>";
		}
		$s.='<td>';

		$s_tag='';

		if($this->isMng){
			if($this->admin['adminmode']==2){
				if(isset($_tag{0})){
					$array=explode(',',$_tag);
					$s_tag.=' <var>';
					foreach($array as $val){

						list($prefix,$name)= (substr($val,0,7)=='config.') ? explode('.',$val,2) : array($val,'');

						/*
						if(substr($val,0,7)=='config.'){
							$kctag='{config:'.$name.'/}';
						}else{
							$kctag='{king:'.$val.'/}';
						}
*/
						$kctag=($prefix=='config')?'{config:'.$name.'/}':'{king:'.$val.'/}';
						$s_tag.='<i title="'.$this->lang->get('system/common/tag').':{king:'.$val.'/}" onClick="window.clipboardData.setData(\'Text\',\''.$kctag.'\');">'.$kctag.'</i>';
					}
					$s_tag.='</var>';
				}
			}
		}

		$s.='<table class="k_side" cellspacing="0"><tr><td>';
		$s.=$_htmlcode;

		if($_arraycheck)
			$s.=kc_check($_arraycheck);
		$s.='</td>';

		$s.=isset($_help{0})?"<td>{$_help}</td>":'';

		$s.=isset($s_tag{0})?"<td>{$s_tag}</td>":'';

		$s.='</tr></table>';

		$s.='</td></tr></tbody>';
	}else{

		$s=isset($_label{0})?'<div class="k_htm">':'<div class="k_htm_in">';

		if($_label)
			$s.='<label>'.$_label;

		if($this->isMng&&isset($_tag{0})){
			if($this->admin['adminmode']==2){
				$array=explode(',',$_tag);
				$s.=' <var>';
				foreach($array as $val){
					list($prefix,$name)=kc_explode('.',$val,2);
/*
					strtolower($prefix)=='config'
						? $kctag='{const:'.$name.'/}'
						: $kctag='{king:'.$val.'/}';
*/
					$kctag=($prefix=='config')?'{config:'.$name.'/}':'{king:'.$val.'/}';

					$s.='<i title="'.$this->lang->get('system/common/tag').':{king:'.$val.'/}" onClick="window.clipboardData.setData(\'Text\',\''.$kctag.'\');">'.$kctag.'</i>';
				}
				$s.='</var>';
			}
		}

		if($_label)
			$s.='</label>';

		$s.='<p>';

		if(isset($_help{0})){
//			$s.='<table class="k_side" cellspacing="0"><tr><td>';
			$s.=$_htmlcode;


//			$s.='</td><td>';
			$s.="<tt>{$_help}</tt>";

			if($_arraycheck)
				$s.=kc_check($_arraycheck);

//			$s.='</td></tr></table>';
		}else{
			$s.=$_htmlcode;
			if($_arraycheck)
				$s.=kc_check($_arraycheck);
		}

		$s.='</p></div>';

	}

	return $s;

}
/**
	结束类
	@param string $submit 在结束类的同时，调用按钮
	@param int    $click  0:默认的submit ; onClick值
	@param string $exp    扩展
	@return string
*/
public function closeForm($submit=null,$click=null,$exp=null){


	$s=($this->config('htmlframe'.$this->isMng)==1) ? '</table>' : '';//1代表的是table结构

	$s.='<p class="k_submit">';

	if(empty($click)){
		if($submit==null){
			$submit=$this->lang->get('system/common/submit');
			$s.='<input type="submit" value="'.$submit.'[S]" class="big" accesskey="s"/>'.$exp;
		}elseif($submit=='save'){
			$submit=$this->lang->get('system/common/save');
			$s.='<input type="submit" value="'.$submit.'[S]" class="big" accesskey="s"/>';
			if($this->isMng){//管理员的时候才显示其他内容
				$s.='<input type="reset" value="'.$this->lang->get('system/common/reset').'[R]" class="big" accesskey="r"/>';
				$s.='<input type="button" onclick="javascript:history.back(-1)" value="'.$this->lang->get('system/common/back').'[B]" class="big" accesskey="b"/>'.$exp;
			}
		}elseif($submit=='none'){
			$s=($this->config('htmlframe'.$this->isMng)==1) ? '</table></form>' : '</form>';//1代表的是table结构
			return $s;
		}else{
			$s.='<input type="submit" value="'.$submit.'"  class="big" />'.$exp;
		}
	}else{
		$s.="<input type=\"button\" value=\"$submit\" class=\"big\" onClick=\"$click\" />".$exp;
	}

	$s.='</p>';
	$s.='</form>';

	return $s;
}
/**
	ajax表单
	@param
	@return
public function divForm($label='',$htmlcode){
	$s="<div class=\"k_htm\">";
	$s.=empty($label) ? "" : "<label>$label</label>";
	$s.="<p>$htmlcode</p>";
	$s.="</div>";

	return $s;
}
*/




}//!KingCMS_class


?>