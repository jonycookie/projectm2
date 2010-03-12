<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

class KC_Skin_class {

private $mPath;

/**
	输出代码
	@param string $title  标题
	@param string $left   左侧按钮
	@param string $right  右侧按钮
	@param string $inside 主体内容
	@return string
*/
public function output($title,$leftmenu=array(),$rightmenu=array(),$inside=null){

	global $king,$action;
	
	$module=$this->getPath();//获得当前的模块地址

	$skinpath=$king->admin?$king->admin['adminskins']:'default';

	$outTmp='system/skins/'.$skinpath.'/'.$module;
	!file_exists(ROOT.$outTmp) && $outTmp='system/skins/'.$skinpath.'/default.htm';//设置默认的模板

	$tmp=new KC_template_class($outTmp);

	//左侧按钮数组格式化
	if(is_array($leftmenu)){
		foreach($leftmenu as $key => $val){
			if($key===$action){//当键值和$action相同时，增加class值sel
				$leftmenu[$key]['class'] = isset($val['class']) ? $val['class'].' sel' : 'sel';
			}
			$leftmenu[$key]['title']=htmlspecialchars($val['title']);
			//当href为空的时候，设置值为javascript:;
			$leftmenu[$key]['href'] = isset($val['href']) ? $val['href'] : 'javascript:;' ;
		}
	}
	//右侧按钮数组格式化
	if(is_array($rightmenu)){
		foreach($rightmenu as $key => $val){
			$rightmenu[$key]['title']=htmlspecialchars($val['title']);
			$rightmenu[$key]['href'] = isset($val['href']) ? $val['href'] : 'javascript:;' ;
		}
	}

	$tmp->assign('title',$title);
	$tmp->assign('leftmenu',$leftmenu);
	$tmp->assign('rightmenu',$rightmenu);
	$tmp->assign('inside',$inside);
	if($king->admin){
		$tmp->assign('logout','<a href="javascript:;" class="k_ajax" rel="{CMD:\'logout\',URL:\'../system/login.php\'}">'.$king->lang->get('system/login/logout').'</a>');
		$tmp->assign('resetpwd','<a href="javascript:;" class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'pass\',METHOD:\'GET\'}" title="'.$king->lang->get('system/admin/pass').'">'.$king->lang->get('system/admin/pass').'</a>');
		$tmp->assign('adminname',$king->admin['adminname']);
		$tmp->assign('adminskins',$king->admin['adminskins']);
		$tmp->assign('faq','<a href="javascript:;" class="k_ajax" rel="{CMD:\'faq\',URL:\'../system/manage.php\'}" title="'.$king->lang->get('system/common/faq').'">'.kc_icon('l9').'</a>');
	}
	$tmp->assign('copyright','Copyright &copy; <a href="http://www.kingcms.com/" target="_blank"><strong>King</strong>CMS<i>.com</i></a> All Rights Reserved.');
	$tmp->assign('runtime',kc_formattime(kc_script_runtime()));
	$output=$tmp->output()."\n<!--\n";
	foreach($GLOBALS['KC_RUNTIME'] as $key => $val){
		$output.="{$key}	[{$val['number']}][".kc_formattime($val['runtime'])."]\n";
//.print_r($GLOBALS['KC_RUNTIME'],1).		
	}

	$output.="-->";

	

	if($king->config('gzencode')&&$king->admin){//启用gzip压缩
		header("Content-Encoding: gzip");
		exit(gzencode($output,9));
	}else{//不使用
		exit($output);
	}
}
/**
	获取当前模块目录
	@return string
*/
private function getPath(){
	if(!isset($this->mPath{0})){
		$path=$_SERVER['PHP_SELF'];
		$this->mPath=basename(dirname($path)).'.htm';
	}
	return $this->mPath;
}
/**
	设置m页面调用的模板名称,模板文件都是放在skin/{SKIN}/下面
	@param string $path 地址
	@return void
*/
public function setPath($path){
	$this->mPath=$path;
}

/**
	数组输出管理员列表
	@return array
*/
private function admins(){
	global $king;
	$cachepath='system/admins';

	$array=$king->cache->get($cachepath);
	if(!$array){
		if(!$res=$king->db->getRows("select adminid,adminname from %a_admin;"))
			$res=array();
		$array=array();
		foreach($res as $rs){
			$array[$rs['adminid']]=$rs['adminname'];
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/**
	tagmenu的管理
*/
public function tagmenu(){
	global $king;

	$cachepath='skin/tagmenu/'.$king->admin['adminid'];
	$number=kc_get('number',2);
	$title=kc_post('title');
	$url=kc_post('url');

	if(!$number) $number=7;

	$pid=kc_get('pid',2);
	if(!$pid) $pid=1;


	if(!$array=$king->cache->get($cachepath))
		$array=array();

	if(isset($title{0})){//如果有title，则是要删除对应的键值
		$array=array_diff_key($array,array($title=>''));
		$king->cache->put($cachepath,$array);
	}

	$count=count($array);

	//删除menu后，可能出现pid大于总页数的情况，则做如下判断
	if($pid-1>=($count/$number)) $pid--;

	$array_new=array_chunk($array,$number,True);
	if(!$array_new1=$array_new[$pid-1])
		$array_new1=array();

	$s='';
	if($pid>1)
		$s.='<a class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'tagmenu\',ID:\'k_tagmenu\',number:'.$number.',pid:'.($pid-1).',url:\''.urlencode($url).'\'}">'.kc_icon('c9').'</a>';
	foreach($array_new1 as $key => $val){
		$val==$url
			? $s.='<span class="red"><a href="'.$val.'">'.htmlspecialchars($key).'</a>'
			: $s.='<span><a href="'.$val.'">'.htmlspecialchars($key).'</a>';
		$s.="<img src=\"../system/images/white.gif\" class=\"k_ajax k8 os\" rel=\"{URL:'../system/manage.php',ID:'k_tagmenu',CMD:'tagmenu',number:$number,url:".urlencode($url).",pid:$pid,title:\'".urlencode($key)."\'}\"/></span>";
	}
	if(($count/$number)>$pid)
		$s.='<a class="k_ajax" rel="{URL:\'../system/manage.php\',ID:\'k_tagmenu\',CMD:\'tagmenu\',number:'.$number.',pid:'.($pid+1).',url:\''.urlencode($url).'\'}">'.kc_icon('d9').'</a>';
	kc_ajax('',$s);
}

/* ------>>> 标签解析 <<<---------------------------- */
/**
	Tag解析
	@param string $name   开头
	@param string $inner  循环体
	@param array  $ass    外部变量
	@param array  $attrib 属性
	@return string
*/
public function tag($name,$inner,$ass,$attrib){
	switch($name){
		case 'skin.mainmenu': return $this->tag_mainmenu();
		case 'skin.lnk': return $this->tag_lnk($inner,$ass,$attrib);
		case 'skin.msg': return $this->tag_msg();
		case 'skin.info': return $this->tag_info();
		case 'skin.tagmenu': return $this->tag_tagmenu($ass,$attrib);
		case 'skin.float':return $this->tag_float();
		case 'skin.module':return $this->tag_module($inner,$attrib);
		case 'skin.menu':return $this->tag_menu($inner,$ass,$attrib);
		case 'skin.other':
	}
}
/**
	系统信息输出
	@param
	@return
*/
public function tag_info(){
	global $king;

	$array_dirs=array(PATH_CACHE,'config.php');
	$array_func=array('mysql_connect','file_get_contents','file_put_contents','simplexml_load_file');//,'fsockopen'

	$s='<table class="k_table" id="k_info" cellspacing="0">';
	$s.='<tr><th colspan="3">'.$king->lang->get('system/skin/sys').'</th></tr>';
	$s.='<tr class="red"><td>'.$king->lang->get('system/skin/obj').'</td><td>'.$king->lang->get('system/skin/required').'</td><td>'.$king->lang->get('system/skin/this').'</td></tr>';
	$s.='<tr><td>'.$king->lang->get('system/skin/os').'</td><td>ALL</td><td>'.PHP_OS.'</td></tr>';
	$s.='<tr><td>'.$king->lang->get('system/skin/phpver').'</td><td>5.1.0+</td><td>'.PHP_VERSION.'</td></tr>';
	list($dbver)=explode('-',$king->db->version());
	$s.='<tr><td>'.$king->lang->get('system/skin/dbver').'</td><td>MySQL5.0+<br/>SQLite3.x</td><td>'.$dbver.'</td></tr>';


	$s.='<tr><th colspan="3">'.$king->lang->get('system/skin/writeinfo').'</th></tr>';
	$s.='<tr class="red"><td>'.$king->lang->get('system/skin/filedir').'</td><td>'.$king->lang->get('system/skin/required').'</td><td>'.$king->lang->get('system/skin/this').'</td></tr>';
	foreach($array_dirs as $val){
		$s.='<tr><td>'.$val.'</td><td>'.$king->lang->get('system/skin/write/w1').'</td><td>'.$king->lang->get('system/skin/write/w'.(is_writable(ROOT.$val)?1:0)).'</td></tr>';
	}
	$s.='<tr><th colspan="3">'.$king->lang->get('system/skin/func').'</th></tr>';
	$s.='<tr class="red"><td>'.$king->lang->get('system/skin/funs').'</td><td>'.$king->lang->get('system/skin/required').'</td><td>'.$king->lang->get('system/skin/this').'</td></tr>';
	foreach($array_func as $val){
		$s.='<tr><td>'.$val.'()</td><td>'.$king->lang->get('system/skin/fun/f1').'</td><td>'.$king->lang->get('system/skin/fun/f'.(function_exists($val)?1:0)).'</td></tr>';
	}
	$s.='<tr><th colspan="3">'.$king->lang->get('system/skin/other').'</th></tr>';
	$s.='<tr class="red"><td>'.$king->lang->get('system/skin/obj').'</td><td>'.$king->lang->get('system/skin/advice').'</td><td>'.$king->lang->get('system/skin/this').'</td></tr>';
	$s.='<tr><td>'.$king->lang->get('system/skin/browser').'</td><td>MSIE 7.0</td><td>'.kc_browser().'</td></tr>';
	$s.='<tr><td>'.$king->lang->get('system/skin/safemode').'</td><td>--</td><td>'.$king->lang->get('system/skin/open/o'.(ini_get('safe_mode')?1:0)).'</td></tr>';
	$s.='<tr><td>'.$king->lang->get('system/skin/maxetime').'</td><td>--</td><td>'.ini_get('max_execution_time').'s</td></tr>';
	$s.='</table>';
	return $s;
}
/**
	短信息
	@return string
*/
private function tag_msg(){

	global $king;

	$s='<form id="k_msg_form" name="k_msg_form"><table class="k_msg" cellspacing="0">';
	$s.='<tr><td><div id="k_msg"></div></td></tr>';
	$s.='<tr><td>';
	$s.="<script>$.kc_ajax('{CMD:\'msg\',ID:\'k_msg\',URL:\'../system/manage.php\'}')</script>";

	$array=array(
		0=>'+ All +',
	);
	$array=array_merge($array,$this->admins());
	$array=array_diff_assoc($array,array($king->admin['adminid']=>$king->admin['adminname']));

//	$s.='</div>';
	$s.=kc_htm_select('adminid',$array,'');
//	$s.='<input class="k_in w300" type="text" name="k_message" id="k_message" maxlength="100" onKeyDown="event.keyCode==13?$.kc_ajax(\'{CMD:\\\'msg_add\\\',ID:\\\'k_msg\\\',FORM:\\\'k_msg_form\\\',IS:1,URL:\\\'../system/manage.php\\\'}\'):void(0);"/></td><tr></table></form>';
	$s.='<input class="k_in w300" type="text" name="k_message" id="k_message" maxlength="100" onkeydown="if(event.keyCode==13){$.kc_ajax({CMD:\'msg_add\',ID:\'k_msg\',FORM:\'k_msg_form\',IS:1,URL:\'../system/manage.php\'})}"/></td><tr></table></form>';

	return $s;
}
/**
	返回mainmenu值
	@return string
*/
private function tag_mainMenu(){
	global $king;

	$cachepath='system/mainmenu/'.$king->admin['adminid'].'/'.$king->admin['adminlanguage'];

	$s=$king->cache->get($cachepath);

	if($s) return $s;

	if(!$king->acc('#open_setting') && !$king->acc('#open_help')) return '';

	$str='';
	//language
	$_array=kc_f_getdir('system/language','xml');
	if(count($_array)>1){
		$_array=array_map('kc_f_name',$_array);
		$i=0;
		foreach($_array as $_value){
			$i
				? $str.='<li>'
				: $str.='<li class="hr">';

			$str.='<a class="k_ajax" rel="{CMD:\'language\',lang:\''.$_value.'\',URL:\'../system/manage.php\'}">';

			$str.='<img src="../system/images/white.gif" class="os '.($king->admin['adminlanguage']==$_value ? 'n8' :'').'"/>';

			$str.=kc_getlang($_value).'</a></li>';

			$i++;
		}

	}
	//skins
	$_array=kc_f_getdir('system/skins/','dir');
	if(count($_array)>1){
		$i=0;
		foreach($_array as $_value){
			$i
				? $str.='<li>'
				: $str.='<li class="hr">';

			$str.='<a class="k_ajax" rel="{CMD:\'skins\',URL:\'../system/manage.php\',skins:\''.$_value.'\'}">';

			$str.='<img src="../system/images/white.gif" class="os '.($king->admin['adminskins']==$_value ? 'n8' :'').'"/>';
				
			$str.=kc_getlang($_value).'</a></li>';

			$i++;
		}
	}
	//editor
	$_array=kc_f_getdir('system/editor/','dir');
	if(count($_array)>1){
		$i=0;
		foreach($_array as $_value){
			$i
				? $str.='<li>'
				: $str.='<li class="hr">';

			$str.='<a class="k_ajax" rel="{CMD:\'editor\',URL:\'../system/manage.php\',editor:\''.$_value.'\'}">';
			$str.='<img src="../system/images/white.gif" class="os '.($king->admin['admineditor']==$_value ? 'n8' :'').'"/>';

			$str.=kc_getlang($_value).'</a></li>';

			$i++;
		}
	}



	$s='<ul class="k_menu" id="k_mainmenu">';

		if($king->acc('#open_setting')){
			

			$s.='<li>

				<a href="javascript:;">'.$king->lang->get('system/common/tools').'</a>

					<ul>';
					if($king->acc('#systemcache'))
						$s.='<li><a class="k_ajax" rel="{CMD:\'clearcache\',URL:\'../system/manage.php\'}"><img src="../system/images/white.gif" class="os d8"/>'.$king->lang->get('system/common/clearcache').'</a></li>';
					if($king->acc('#systeminfo'))
						$s.='<li class="hr"><a href="../system/manage.php?action=config"><img src="../system/images/white.gif" class="os e7"/>'.$king->lang->get('system/title/system').'</a></li>';
					if($king->admin['adminlevel']=='admin')
						$s.='<li><a href="../system/manage.php?action=admin"><img src="../system/images/white.gif" class="os e6"/>'.$king->lang->get('system/title/admin').'</a></li>';
					if($king->acc('#module'))
						$s.='<li><a href="../system/manage.php?action=module"><img src="../system/images/white.gif" class="os m8"/>'.$king->lang->get('system/menu/module').'</a></li>';
					if($king->acc('#conn'))
						$s.='<li class="hr"><a href="../system/manage.php?action=conn"><img src="../system/images/white.gif" class="os r5"/>'.$king->lang->get('system/title/conn').'</a></li>';
					if($king->acc('#lnkclass'))
						$s.='<li><a href="../system/manage.php?action=lnk"><img src="../system/images/white.gif" class="os r1"/>'.$king->lang->get('system/title/lnk').'</a></li>';
					if($king->acc('#upfile'))
						$s.='<li><a href="../system/manage.php?action=upfile"><img src="../system/images/white.gif" class="os m5"/>'.$king->lang->get('system/title/upfile').'</a></li>';
	/*
					if($king->acc('#timingtask'))
						$s.='<li><a href="../system/manage.php?action=timingtask">'.$king->lang->get('system/title/timingtask').'</a></li>';
	*/
					if($king->acc('#log'))
						$s.='<li class="hr"><a href="../system/manage.php?action=log"><img src="../system/images/white.gif" class="os h6"/>'.$king->lang->get('system/title/log').'</a></li>';
					if($king->acc('#event'))
						$s.='<li><a href="../system/manage.php?action=event"><img src="../system/images/white.gif" class="os g9"/>'.$king->lang->get('system/title/event').'</a></li>';
					if($king->acc('#bot'))
						$s.='<li><a href="../system/manage.php?action=bot"><img src="../system/images/white.gif" class="os o8"/>'.$king->lang->get('system/title/bot').'</a></li>';

					$s.=$str.'
					</ul>
			</li>';

		}

		if($king->acc('#open_help')){
			

			$s.='
			<li>
				<a href="javascript:;">'.$king->lang->get('system/common/help').'</a>
				<ul>
					<li><a href="http://help.kingcms.com/" target="_blank"><img src="../system/images/white.gif" class="os q9"/>'.$king->lang->get('system/common/kchelp').'</a></li>
					<li><a href="javascript:;" class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'faq\'}"><img src="../system/images/white.gif" class="os g6"/>'.$king->lang->get('system/common/faq').'</a></li>
					<li class="hr"><a href="http://www.kingcms.com/" target="_blank"><img src="../system/images/white.gif" class="os b9"/>'.$king->lang->get('system/menu/official').'</a></li>
					<li><a href="http://bbs.kingcms.com/" target="_blank"><img src="../system/images/white.gif" class="os r2"/>'.$king->lang->get('system/menu/club').'</a></li>
					<li><a href="http://bbs.kingcms.com/forum-17-1.html" target="_blank"><img src="../system/images/white.gif" class="os r3"/>'.$king->lang->get('system/menu/bug').'</a></li>
					<li class="hr"><a href="http://www.kingcms.com/license/" target="_blank"><img src="../system/images/white.gif" class="os r4"/>'.$king->lang->get('system/menu/license').'</a></li>';
/*
					<li class="hr"><a class="k_ajax" rel="{CMD:\'about\',URL:\'../system/login.php\'}"><img src="../system/images/white.gif" class="os"/>'.$king->lang->get('system/menu/checknew').'</a></li>';
*/
					if($king->acc('#phpinfo'))
						$s.='<li><a href="../system/manage.php?action=phpinfo"><img src="../system/images/white.gif" class="os a3"/>PHPINFO()</a></li>';
					$s.='<li class="hr"><a class="k_ajax" rel="{CMD:\'about\',URL:\'../system/login.php\'}"><img src="../system/images/white.gif" class="os h2"/>'.$king->lang->get("system/common/about").'..</a></li>
				</ul>
			</li>';
		}

	$s.='</ul>';

	$king->cache->put($cachepath,$s);

	return $s;

}
public function tag_module($inner='',$attrib=array()){
	global $king;

	$s='';

	$nshow=kc_val($attrib,'show',NULL);

	$pArray=$king->getModule($nshow);

	if(empty($inner)){
		$s='<ul class="k_menu" id="k_modulelist"><li>

				<a href="javascript:;">'.$king->lang->get('system/common/module').'</a>

					<ul>
						<li><a href="../system/manage.php?action=module"><img src="../system/images/white.gif" class="os m8"/>'.$king->lang->get('system/menu/module').'</a></li>
						<li class="hr"><a href="../system/manage.php"><img src="../system/images/white.gif" class="os k2"/>'.$king->lang->get('system/name').'</a></li>';
						foreach($pArray as $val){
							$s.='<li><a href="../'.$val.'/manage.php"><img src="../system/images/white.gif" class="os k7"/>'.$king->lang->get($val.'/name').'</a></li>';
						}
						$s.='
					</ul>
			</li></ul>';
	}else{
		$tmp=new KC_Template_class();
		foreach($pArray as $val){
			if($king->acc($val)){
				$tmp->assign('name',htmlspecialchars($king->lang->get($val.'/name')));
				$tmp->assign('url',"../$val/manage.php");
				$tmp->assign('path',$val);
				$s.=$tmp->output($inner);
			}
		}
	}

	return $s;
}
/**
	按钮
*/
public function tag_menu($inner,$ass,$attrib){
	global $king;

	$module=$attrib['module'];

	if(!$king->isModule($module))
		return;

	$language= is_file(ROOT.$module.'/language/'.$king->admin['adminlanguage'].'.xml') ? $king->admin['adminlanguage'] : LANGUAGE;

	$xml=new KC_XML_class;
	$xml->load_file($module.'/language/'.$language.'.xml');

	$array_kingcms=$xml->xml2array();
	$array_channel=$array_kingcms['CHANNEL'];

	$tmp=new KC_Template_class();
	$s='';
	if($array_channel){
		foreach($array_channel as $key => $val){
			$arr=$xml->attrib('CHANNEL->'.$key);
			if($king->acc($arr['access'])){
				$tmp->assign('name',htmlspecialchars($val));
				$tmp->assign('href',$arr['href']);
				$tmp->assign('target',$arr['target']?' target="'.$arr['target'].'"':'');
				$tmp->assign('key',$key);
				$tmp->assign('access',$arr['access']);
				$tmp->assign('onclick',$arr['onclick']?' onclick="'.$arr['onclick'].'"':'');
				$tmp->assign('rel',$arr['rel']?' rel="'.$arr['rel'].'"':'');
				$tmp->assign('class',$arr['class']);
				$tmp->assign('img',$arr['img'] ? $arr['img']: '');
				$s.=$tmp->output($inner);
			}
		}
	}
	return $s;
}
/**
返回管理首页的快捷菜单列表
@return array
*/
public function tag_lnk($inner,$ass,$attrib){

	global $king;

	if($inner){
		$tmp=new KC_Template_class;
		if(!$res=$king->db->getRows("select kid,kname,kpath,konclick,kimage,isblank,ktitle from %s_lnk where adminid=".$king->admin['adminid']." order by norder desc"))
			$res=array();

		foreach($res as $rs){
			$tmp->assign('id',$rs['kid']);
			$tmp->assign('name',htmlspecialchars($rs['kname']));
			$tmp->assign('href',htmlspecialchars($rs['kpath']));
			$tmp->assign('onclick',htmlspecialchars($rs['konclick']));
			$tmp->assign('image','system/images/lnk/'.$rs['kimage']);
			$tmp->assign('target',$rs['isblank']?'target="_blank"':'');
			$tmp->assign('title',htmlspecialchars($rs['ktitle']));
			$s.=$tmp->output($inner);
		}

	}else{//默认输出的项目
		$cachepath='system/lnk/'.$king->admin['adminid'];
		$s=$king->cache->get($cachepath);
		if(!$s){
			if(!$res=$king->db->getRows("SELECT kid,kname,kpath,konclick,kimage,isblank,ktitle FROM %s_lnk where adminid=".$king->admin['adminid']." order by norder desc")){
				$res=array(
					array(
						'norder'=>10,'kname'=>'栏目管理','ktitle'=>'栏目管理中心','kpath'=>'../portal/manage.php',
						'kimage'=>'panel.gif','adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>9,'kname'=>'爬虫管理','ktitle'=>'爬虫访问管理','kpath'=>'../system/manage.php?action=bot',
						'kimage'=>'bot.gif','adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>8,'kname'=>'管理日志','ktitle'=>'管理员访问操作日志','kpath'=>'../system/manage.php?action=log',
						'kimage'=>'log.gif','adminid'=>$king->admin['adminid'],'konclick'=>'',),
					array(
						'norder'=>7,'kname'=>'附件管理','ktitle'=>'已上传文件管理','kpath'=>'../system/manage.php?action=upfile',
						'kimage'=>'upfile.gif','adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>6,'kname'=>'首选项','ktitle'=>'CMS系统参数设置','kpath'=>'../system/manage.php?action=config',
						'kimage'=>'system.gif','adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>5,'kname'=>'管理员','ktitle'=>'管理员信息及密码设置','kpath'=>'../system/manage.php?action=admin',
						'kimage'=>'admin.gif','adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>4,'kname'=>'模块管理','ktitle'=>'模块管理','kpath'=>'../system/manage.php?action=module',
						'kimage'=>'module.gif','adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>3,'kname'=>'KingCMS','ktitle'=>'KingCMS官方网站','kpath'=>'http://www.kingcms.com/',
						'kimage'=>'lnk.gif','isblank'=>1,'adminid'=>$king->admin['adminid'],'konclick'=>''),
					array(
						'norder'=>2,'kname'=>'Forums','ktitle'=>'KingCMS论坛','kpath'=>'http://bbs.kingcms.com/',
						'kimage'=>'lnk.gif','isblank'=>1,'adminid'=>$king->admin['adminid'],'konclick'=>''),
				);

				foreach($res as $rs){
					$king->db->insert('%s_lnk',$rs);
				}
			}

			$s='<div id="k_lnk">';

			foreach($res as $rs){
				$s.='<a href="'.htmlspecialchars($rs['kpath']).'" '.(!empty($rs['isblank'])?'target="_blank"':'').' onclick="'.htmlspecialchars($rs['konclick']).'" title="'.htmlspecialchars($rs['ktitle']).'"><img src="images/lnk/'.$rs['kimage'].'"/><i>'.htmlspecialchars($rs['kname']).'</i></a>';
			}
			$s.='<a href="http://www.kingcms.com/license/" target="_blank" title="'.$king->lang->get('system/common/license').'"><img src="images/lnk/license.gif"/><i>'.$king->lang->get('system/common/license').'</i></a>';
			$s.='<a href="javascript:;" class="k_ajax" rel="{URL:\'../system/manage.php\',CMD:\'faq\'}"><img src="images/lnk/faq.gif" title="'.$king->lang->get('system/common/faq').'"/><i>'.$king->lang->get('system/common/faq').'</i></a>';
			$s.='<a href="manage.php?action=lnk" title="'.$king->lang->get('system/title/lnk').'"><img src="images/lnk/modify.gif"/><i>'.$king->lang->get('system/level/lnk').'</i></a>';
			$s.='</div>';

			$king->cache->put($cachepath,$s);

		}
	}

	return $s;
}

/**
	最近访问的页面，标签方式显示
*/
public function tag_tagMenu($ass,$attrib){
	global $king;

	$cachepath='system/tagmenu/'.$king->admin['adminid'];
	$number=$attrib['number'];
	if(!$array=$king->cache->get($cachepath))
		$array=array();

	//标题从$ass['title']中获取，路径从当前页的路径里获取，读取cache，加到数组前面
	if(!$GLOBALS['ismethod']){
		//$array[$ass['title']]=$_SERVER['PHP_SELF'];
		if(array_key_exists($ass['title'],$array)){
			$array[$ass['title']]=$_SERVER['REQUEST_URI'];//如果存在这个键的话，替换值
		}else{//若没有这个键值，则新建
			$array=array_merge(array($ass['title']=>$_SERVER['REQUEST_URI']),$array);
		}
		$king->cache->put($cachepath,$array);
		
		//寻找当前页面的所在页，即重定向pid
		$url=$_SERVER['REQUEST_URI'];
		$title_new=array_search($url,$array);//返回当前url对应的标题
	}else{
		$title_new=$ass['title'];
		$url=$array[$title_new];
	}
	$array_new=array_chunk($array,$number,True);

	foreach($array_new as $key=>$val){
		if(array_key_exists($title_new,$val)){
			$pid=$key+1;
		}
	}

	$s='<div id="k_tagmenu"></div>';
	$s.="<script>\$.kc_ajax('{URL:\'../system/manage\',CMD:\'tagmenu\',ID:\'k_tagmenu\',number:$number,url:\'".urlencode($url)."\',pid:$pid}');</script>";
	return $s;
}
/**
	弹出来的快捷方式
	@param
	@return
*/
private function tag_float(){
	global $king;

	$cachepath='system/lnk/flo_'.$king->admin['adminid'];
	$s=$king->cache->get($cachepath);
	
	if(!$s){
		$_sql="SELECT kid,kname,ktitle,kimage,kpath,konclick,isblank,ntop,nleft FROM %s_lnk where adminid=".$king->admin['adminid']." and isflo=1";
		if(!$res=$king->db->getRows($_sql))
			$res=array();
		foreach($res as $rs){
			$s.='<div class="k_float" id="k_float_'.$rs['kid'].'" style="top:'.$rs['ntop'].'px;left:'.$rs['nleft'].'px;"><a href="'.$rs['kpath'].'" onclick="'.$rs['konclick'].'" title="'.htmlspecialchars($rs['ktitle']).'"';
			if($rs['isblank'])
				$s.=' target="_blank"';
			$s.='><img src="../system/images/lnk/'.$rs['kimage'].'"/></a><i>'.htmlspecialchars($rs['kname']).'</i></div>';
		}
//		$s.='<script type="text/javascript" src="../system/js/move.js"></script>';
		$king->cache->put($cachepath,$s.'<!--lnk-->');
	}
	return $s;
}



}
?>
