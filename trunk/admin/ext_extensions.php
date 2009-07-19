<?php
!defined('IN_ADMIN') && die('Forbidden');
/**
 * 插件后台管理
 */

class AdminExt{
	var $extdb;
	var $chs;

	function AdminExt(){
		$this->__construct();
	}

	function __construct(){
		global $charset,$ext_config;
		$this->extdb = $ext_config;
		$this->chs = new Chinese("utf-8",$charset);
	}

	function show(){
		global $basename;
		$installdb = $uninstalldb = $upgradedb = array();
		foreach ($this->extdb as $key => $value) {//已安装扩展
			$value['name'] = htmlspecialchars($value['name']);
			${$value['dir'].'_'.$value['ifopen']} = 'SELECTED';
			$value['uninstall'] = EncodeUrl("$basename&extensionset=del&extdir=$value[dir]");
			$installdb[$key] = $value;
		}
		if ($fp = opendir(R_P.'extensions')) {
			$infodb = array();
			while (($extdir = readdir($fp))) {
				if (strpos($extdir,'.')===false) {
					$infodb = $this->getInfo($extdir);
					!$infodb['name'] && $infodb['name'] = $extdir;
					$infodb['dir'] = $extdir;
					if(empty($this->extdb[$extdir])){//未安装扩展
						$infodb['url'] = EncodeUrl("$basename&extensionset=add&extdir=$extdir&extname=".rawurlencode($infodb['name'])."&extopen=$infodb[ifopen]&extver=$infodb[version]");
						$uninstalldb[] = $infodb;
					}elseif($this->extdb[$extdir]['version'] != $infodb['version']){//可更新扩展
						$infodb['url'] = EncodeUrl("$basename&extensionset=upgrade&E_name=$extdir");
						$infodb['oldversion'] = $this->extdb[$extdir]['version'];
						$upgradedb[] = $infodb;
					}
				}
			}
			closedir($fp);
		}
		require PrintEot('ext_extensions');
		adminbottom();
	}

	function edit(){
		extract(Init_GP(array('name','ifopen')));
		foreach ($name as $key => $value) {
			$value = str_replace(array("\t","\n","\r",'  '),array('&nbsp; &nbsp; ','<br />','','&nbsp; '),$value);
			if($value && $this->extdb[$key]['dir']==$key && ($this->extdb[$key]['name'] != $value || $this->extdb[$key]['ifopen'] != (int)$ifopen[$key])) {
				$this->extdb[$key]['name'] = stripslashes($value);
				$this->extdb[$key]['ifopen'] = (int)$ifopen[$key];
			}
		}
		$this->extdb = addslashes(serialize($this->extdb));
		$this->save();
		adminmsg('operate_success');
	}

	function del(){
		$extdir = GetGP('extdir');
		empty($this->extdb[$extdir]) && Showmsg('ext_centerdel');
		unset($this->extdb[$extdir]);
		$sqlarray = file_exists(R_P."extensions/$extdir/install.sql") ? $this->FileArray($extdir) : array();
		!empty($sqlarray) && $this->SQLDrop($sqlarray);
		$this->extdb = addslashes(serialize($this->extdb));
		$this->save();
		adminmsg('operate_success');
	}

	function add(){
		extract(Init_GP(array('extdir','extname','extopen','extver'),'G',1));
		!empty($this->extdb[$extdir]) && Showmsg('ext_centeradd');
		$sqlarray = file_exists(R_P."extensions/$extdir/install.sql") ? $this->FileArray($extdir) : array();
		!empty($sqlarray) && $this->SQLCreate($sqlarray);
		$this->extdb[$extdir] = array('name'=>$extname,'dir'=>$extdir,'ifopen'=>$extopen,'version'=>$extver);
		$this->extdb = addslashes(serialize($this->extdb));
		$this->save();
		adminmsg('operate_success');
	}

	function about(){
		global $basename,$extensionset;
		extract(Init_GP(array('extdir','type')));
		if (strpos($extdir,'.')===false) {
			$infodb = $this->getInfo($extdir);
			if($type == 'install' && $this->extdb[$extdir]){
				$infodb['name'] = $this->extdb[$extdir]['name'];
				$infodb['version'] = $this->extdb[$extdir]['version'];
			}
			!$infodb['name'] && $infodb['name'] = $extdir;
		}
		require PrintEot('ext_extensions');
		adminbottom();
	}

	function upgrade(){
		global $E_name,$basename;
		$E_name = GetGP('E_name');
		if($this->extdb[$E_name]){
			define('E_P',R_P."extensions/$E_name/");
			$basename = "$basename&extensionset=upgrade&E_name=$E_name";
			if(file_exists(E_P.'upgrade.php')){
				include_once Pcv(E_P.'upgrade.php');
			}
			$infodb = $this->getInfo($E_name);
			$this->extdb[$E_name]['version'] = $infodb['version'];
			$this->extdb = addslashes(serialize($this->extdb));
			$this->save();
		}
		adminmsg('operate_success',"$admin_file?adminjob=ext_extensions");
	}

	function save(){
		global $db;
		$rt = $db->get_one("SELECT name FROM cms_extension WHERE name='ext_config'");
		if (!empty($rt)) {
			$db->update("UPDATE cms_extension SET value='$this->extdb' WHERE name='ext_config'");
		} else {
			$db->update("INSERT INTO cms_extension(name,value) VALUES ('ext_config','$this->extdb')");
		}
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->extension();
	}


	function getInfo($extdir){
		$infodb = array();
		$XMLDoc = new XMLDoc();
		$phpversion = array();
		$phpversion = explode('.',PHP_VERSION);
		$phpversion = array_shift($phpversion);
		if($XMLDoc->LoadFromFile(R_P."extensions/$extdir/info.xml")){
			$XMLDoc->parse();
			$element = $XMLDoc->GetDocumentElement();
			$child = $element->GetChild();
			foreach($child as $val){
				if($phpversion<5) {
					$infodb[$val->GetTagName()] = $val->GetData();
				}else {
					$infodb[$val->GetTagName()] = $this->chs->Convert($val->GetData());
				}
			}
		}
		return $infodb;
	}

	function SQLCreate($sqlarray) {
		global $db,$charset;
		$query = '';
		foreach ($sqlarray as $value) {
			if ($value[0]!='#') {
				$query .= $value;
				if (substr($value,-1)==';' && !in_array(strtolower(substr($query,0,5)),array('drop ','delet','updat'))) {
					$lowquery = strtolower(substr($query,0,5));
					if (in_array($lowquery,array('creat','alter','inser','repla'))) {
						$next = $this->CheckDrop($query);
						if ($lowquery == 'creat') {
							if (!$next) continue;
							strpos($query,'IF NOT EXISTS')===false && $query = str_replace('TABLE','TABLE IF NOT EXISTS',$query);
							$extra1 = trim(substr(strrchr($value,')'),1));
							$tabtype = substr(strchr($extra1,'='),1);
							$tabtype = substr($tabtype,0,strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
							if ($db->server_info() >= '4.1') {
								$extra2 = "ENGINE=$tabtype".($charset ? " DEFAULT CHARSET=$charset" : '');
							} else {
								$extra2 = "TYPE=$tabtype";
							}
							$query = str_replace($extra1,$extra2.';',$query);
						} elseif (in_array($lowquery,array('inser','repla'))) {
							if (!$next) continue;
							$lowquery == 'inser' && $query = 'REPLACE '.substr($query,6);
						} elseif ($lowquery == 'alter' && !$next && strpos(strtolower($query),'drop')!==false) {
							continue;
						}

						$db->query($query);
						$query = '';
					}
				}
			}
		}
	}

	function SQLDrop($sqlarray) {
		global $db;
		foreach ($sqlarray as $query) {
			$lowquery = strtolower(substr($query,0,6));
			$next = $this->CheckDrop($query);
			if ($next && $lowquery == 'create') {
				$t_name = trim(substr($query,0,strpos($query,'(')));
				$t_name = substr($t_name,strrpos($t_name,' ')+1);
				$db->query("DROP TABLE IF EXISTS $t_name");
			}
		}
	}

	function FileArray($extdir){
		if (function_exists('file_get_contents')) {
			$filedata = @file_get_contents(Pcv(R_P."extensions/$extdir/install.sql"));
		} else {
			$filedata = readover(Pcv(R_P."extensions/$extdir/install.sql"));
		}
		$filedata = trim(str_replace(array("\t","\r",";\n","\n","\tEND;"),array('','',"\tEND;",'',";\n"),$filedata));
		$sqlarray = $filedata ? explode("\n",$filedata) : array();
		return $sqlarray;
	}

	function CheckDrop($query){
		global $db;
		require_once(R_P.'admin/table.php');
		$next = true;
		foreach ($tabledb as $value) {
			if (strpos(strtolower($query),strtolower($value))!==false) {
				$next = false;
				break;
			}
		}
		return $next;
	}

	function doIt($extensionset){
		switch($extensionset){
			case 'show':
				$this->show();
				break;
			case 'add':
				$this->add();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'about':
				$this->about();
				break;
			case 'del':
				$this->del();
				break;
			case 'upgrade':
				$this->upgrade();
				break;
			default:
				$this->show();
				break;
		}
	}
}
include_once(D_P.'data/cache/ext_config.php');
$extensionset = GetGP('extensionset');
!$extensionset && $extensionset = 'show';
if($extensionset == 'manage'){
	$E_name = GetGP('E_name');
	if(!preg_match("/^[a-zA-Z0-9_]{1,}$/",$E_name) || !$ext_config[$E_name] || !is_dir(R_P."extensions/$E_name") || !file_exists(R_P."extensions/$E_name/admin.php")){
		adminmsg('ext_error');
	}
	define('IN_EXT',true);
	define('E_P',R_P."extensions/$E_name/");
	$basename = "$basename&extensionset=manage&E_name=$E_name";
	$ext_tplpath	= "extensions/$E_name/template";
	$ext_imgpath	= "extensions/$E_name/images";
	$ext_langpath	= "extensions/$E_name/lang";
	require_once(E_P.'admin.php');
}else{
	require_once(R_P.'require/chinese.php');
	require_once(R_P.'require/class_xml.php');
	$ext = new AdminExt();
	$ext->doIt($extensionset);
}

/**
 * 返回一个插件模板文件的编译路径
 *
 * @param string $tplname
 * @return string
 */
function TemplateExt($tplname){
	$tplname = R_P."$ext_tplpath/$tplname";
	require_once(R_P.'require/template.php');
	return Tpl($tplname,true);
}

/**
 * 返回一个插件模板文件的路径
 *
 * @param string $template
 * @return string
 */
function PrintExt($template,$EXT="htm"){
	if (file_exists(E_P."template/$template.$EXT")) {
		return Pcv(E_P."template/$template.$EXT");
	}
	return PrintEot($template,$EXT);
}
?>