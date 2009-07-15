<?php
!defined('IN_ADMIN') && die('Forbidden');

class module{
	var $mid;
	/**
	 * 系统内嵌字段，禁止删除
	 *
	 * @var array
	 */
	var $system_field = array('tid','cid','title','digest','photo','linkurl','titlestyle');

	/**
	 * 系统保留字段名，禁止命名
	 *
	 * @var array
	 */
	var $hold_field = array('tid','cid','title','photo','titlestyle','publisher','url','digest','hits','comnum','fpage','linkurl','postdate','imagetolocal','selectimage');
	var $table;

	function __construct(){
		global $mid;
		if($mid){
			$this->mid = intval($mid);
			$this->mid<1 && Showmsg('mod_cannotedit');
			$this->table = 'cms_content'.$this->mid;
		}
	}

	function module(){
		$this->__construct();
	}

	function doIt($action){
		switch ($action){
			case 'addfield':
				$this->addField();
				break;
			case 'editfield':
				$this->editField();
				break;
			case 'delfield':
				$this->delField();
				break;
			case 'add':
				$this->addModule();
				break;
			case 'edit':
				$this->editModule();
				break;
			case 'del':
				$this->delModule();
				break;
			case 'vieworder':
				$this->viewOrder();
				break;
			case 'export':
				$this->exportModule();
				break;
			case 'import':
				$this->importModule();
				break;
			case 'editindex':
				$this->editIndex();
				break;
			default:
				$this->show();
				break;
		}
	}

	/**
	 * 显示内容模型
	 *
	 */
	function show(){
		global $moduledb,$basename;
		sort($moduledb);
		require PrintEot('header');
		require PrintEot('module');
	}

	/**
	 * 添加内容模型字段
	 *
	 */
	function addField(){
		global $action,$step,$basename,$moduledb;
		if(!$step){
			$mname = $moduledb[$this->mid]['mname'];
			require PrintEot('header');
			require PrintEot('module');
		}elseif ($step==2){
			$array = Init_GP(array('fieldname','fieldid','fieldtype','fieldsize','inputtype','getvalue','inputsize','defaultvalue','inputlabel','ifgather','ifindex','ifsearch','ifcontribute'),'P');
			$this->saveField($array,'add');
			adminmsg('mod_addfieldok',$basename."&mid=$this->mid&action=edit");
		}
	}

	/**
	 * 编辑内容模型字段
	 *
	 */
	function editField(){
		global $db,$basename,$action,$step,$fid,$moduledb;
		if(!$step){
			@extract($db->get_one("SELECT * FROM cms_field WHERE fid='$fid'"));
			!$fieldid && Showmsg('data_error');
			if(in_array($fieldid,$this->system_field)){
				$sysedit = 'DISABLED';
			}
			ifcheck($ifgather,'ifgather');
			ifcheck($ifsearch,'ifsearch');
			ifcheck($ifindex,'ifindex');
			ifcheck($ifcontribute,'ifcontribute');
			extract($GLOBALS['checks']);
			${$fieldtype.'_s'} = 'selected';
			${$inputtype.'_i'} = 'selected';
			${'getvalue_'.$getvalue} = 'selected';
			require PrintEot('header');
			require PrintEot('module');
		}elseif ($step==2){
			$array = Init_GP(array('fieldname','fieldid','fieldtype','fieldsize','inputtype','getvalue','inputsize','defaultvalue','inputlabel','ifgather','ifindex','ifsearch','ifcontribute'),'P');
			$this->saveField($array,'edit');
			adminmsg('mod_editfieldok',$basename."&mid=$this->mid&action=edit");
		}
	}

	/**
	 * 保存内容模型字段
	 *
	 * @param Array $array 字段数据
	 * @param String $action 添加或编辑
	 */
	function saveField($array,$action){
		global $db;
		extract($array,EXTR_SKIP);
		if($action=='edit'){
			$oldid = GetGP('oldid','P');
			!ereg("^[a-z]{3,12}$",$oldid) && Showmsg('mod_fieldiderror');
			$sqladd =  "AND fieldid<>'$oldid'";
			in_array($oldid,$this->system_field) && $sysedit = 1 && $fieldid = $oldid;

		}else{
			$oldid = '';
			$sqladd = '';
			$sysedit = '';
		}
		empty($fieldname) && Showmsg('mod_nofieldname');
		empty($fieldid) && Showmsg('mod_nofieldid');
		$fieldid = strtolower($fieldid);
		!ereg("^[a-z]{3,12}$",$fieldid) && Showmsg('mod_fieldiderror');
		!$sysedit && in_array($fieldid,$this->system_field) && Showmsg('mod_sysfield');
		!$sysedit && in_array($fieldid,$this->hold_field) && Showmsg('mod_holdfield');

		$needdefault = array('select','radio','checkbox');
		if (in_array($inputtype,$needdefault)) { //此类输入方式需要设置默认值
			(empty($defaultvalue) || empty($inputlabel)) && Showmsg('mod_needdefaultvalue');
		}
		if(strpos($defaultvalue,',')!==false) Showmsg('mod_comma');//默认值不能含有逗号
		if($fieldsize && (!is_numeric($fieldsize) || $fieldsize<=0)) Showmsg('mod_fieldsizeerror');
		$rt = $db->get_one("SELECT * FROM cms_field WHERE mid='$this->mid' AND fieldid='$fieldid'");
		if($rt && ($action=='add' || $oldid!=$fieldid)) Showmsg('mod_fieldidrepeat');
		if($inputtype=='edit'){//一个内容模型最多只能有一个字段使用编辑器输入
			$rs = $db->get_one("SELECT * FROM cms_field WHERE mid='$this->mid' AND inputtype='edit' $sqladd");
			if($rs) Showmsg('mod_onlyoneeidt');
		}
		$defaultvalue = Char_cv($defaultvalue);
		$inputlabel = Char_cv($inputlabel);
		$fieldname = Char_cv($fieldname);
		$fieldsize = (int)$fieldsize;
		$inputsize = (int)$inputsize;
		$getvalue = (int)$getvalue;
		$ifgather = $ifgather ? 1 : 0;
		$ifsearch = $ifsearch ? 1 : 0;
		$ifcontribute = $ifcontribute ? 1 : 0;
		if(in_array($inputtype,array('checkbox','select','radio'))) {//本版本搜索不支持单选、多选、选择项
			$ifsearch = 0;
		}
		if($inputtype == 'mselect') {		//固定菜单固定预设值
			$getvalue=4;
			if(in_array($fieldtype,array('tinyint','smallint','int'))) {
				$fieldtype = 'text';
			}
		}
		$getvalue && $getvalue<10 && $ifcontribute && Showmsg('mod_nocontribute');
		$ifindex = $ifindex ? 1 : 0;
		if($inputsize>120){
			$inputsize=120; //max value
		}elseif ($inputsize<=20){
			$inputsize=20; //min value
		}
		switch ($fieldtype){
			case 'tinyint':
				$fieldsize=1;
				break;
			case 'smallint':
				empty($fieldsize) && $fieldsize=6;
				break;
			case 'int':
				if($fieldsize<6 || $fieldsize>10){
					$fieldsize=10;
				}
				break;
			case 'varchar':
				if($fieldsize>255){
					$fieldsize=255;
				}elseif ($fieldsize<=50){
					$fieldsize=50;
				}
				break;
			default:
				$fieldsize='';
				break;
		}
		if($inputtype == 'checkbox'){//对于一个复选框类型的内容来说，他的存储格式只可能是varchar
			$fieldtype = 'varchar';
			$fieldsize = 255;
		}
		if($action=='edit' && $sysedit){
			$query = "cms_field SET
					fieldname='$fieldname'
			";
		}else{
			$query = "cms_field SET
					mid='$this->mid',
					fieldname='$fieldname',
					fieldid='$fieldid',
					fieldtype='$fieldtype',
					fieldsize='$fieldsize',
					inputtype='$inputtype',
					getvalue='$getvalue',
					inputsize='$inputsize',
					defaultvalue='$defaultvalue',
					inputlabel='$inputlabel',
					ifgather='$ifgather',
					ifindex='$ifindex',
					ifsearch='$ifsearch',
					ifcontribute='$ifcontribute'
			";
		}
		$fieldsize!='' && $fieldsize="($fieldsize)";
		if($action=='edit'){
			global $fid;
			!$sysedit && $db->update("ALTER TABLE `$this->table` CHANGE `$oldid` `$fieldid` $fieldtype$fieldsize NOT NULL");
			$query = "UPDATE ". $query . "WHERE fid='$fid'";
		}elseif ($action=='add'){
			$db->update("ALTER TABLE `$this->table` ADD `$fieldid` $fieldtype$fieldsize NOT NULL");
			$query = "INSERT INTO ". $query;
		}
		$db->update($query);
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->field();
	}

	/**
	 * 删除内容模型中的字段
	 *
	 */
	function delField(){ //删除字段
		global $db,$fid,$basename;
		@extract($db->get_one("SELECT * FROM cms_field WHERE fid='$fid'"));
		!$fieldid && Showmsg('data_error');
		in_array($fieldid,$this->system_field) && Showmsg('mod_sysfield');
		$db->update("DELETE FROM cms_field WHERE fid='$fid'");
		$db->update("ALTER TABLE `$this->table` DROP `$fieldid`");
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->field();
		adminmsg('mod_delfield',"$basename&action=edit&mid=$this->mid");
	}

	/**
	 * 内容模型字段排序
	 *
	 */
	function viewOrder(){
		global $db,$basename;
		$vieworder = GetGP('vieworder');
		foreach ($vieworder as $fid=>$value){
			$fid = (int)$fid;
			$value = (int)$value;
			$db->update("UPDATE cms_field SET vieworder='$value' WHERE fid='$fid'");
		}
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->field();
		adminmsg('operate_success',"$basename&action=edit&mid=$this->mid");
	}

	/**
	 * 编辑内容模型字段索引
	 *
	 */
	function editIndex(){
		global $fid,$db,$basename;
		$rs = $db->get_one("SELECT fieldid,fieldtype,ifindex FROM cms_field WHERE fid='$fid'");
		!$rs['fieldid'] && Showmsg('data_error');
		in_array($rs['fieldid'],$this->system_field) && Showmsg('mod_sysfield');
		if($rs['ifindex']){
			$value = 0;
			$sql = "ALTER TABLE `$this->table` DROP INDEX `$rs[fieldid]` ";
			$msg = 'mod_dropindex';
		}else{
			$indextype = in_array($rs['fieldtype'],array('text','longtext','mediumtext')) ? 'FULLTEXT' : 'INDEX';
			$value = 1;
			$sql = "ALTER TABLE `$this->table` ADD $indextype(`$rs[fieldid]`)";
			$msg = 'mod_addindex';
		}
		$db->query($sql);
		$db->update("UPDATE cms_field SET ifindex='$value' WHERE fid='$fid'");
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->field();
		adminmsg($msg,"$basename&action=edit&mid=$this->mid");
	}

	/**
	 * 添加内容模型
	 *
	 */
	function addModule(){
		global $db,$basename;
		$mname = GetGP('mname','P');
		$descrip = GetGP('descrip','P');
		$varname = GetGP('varname','P');
		empty($mname) && Showmsg('mod_nomname');
		$mname = Char_cv($mname);
		$descrip = Char_cv($descrip);
		$varname = Char_cv($varname);
		if($varname){
			require_once(R_P.'require/class_const.php');
			$const = new TplConst('MID');
			$vararray = array('title'=>$mname,'name'=>$varname,'value'=>$this->mid);
			$const->setConst($vararray);
		}
		$this->saveModule($mname,$descrip);
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->sql();
		$cache->field();
		adminmsg('operate_success',$basename."&action=edit&mid=".$this->mid);
	}

	/**
	 * 创建一个新的内容模型
	 *
	 * @param string $mname 模型名称
	 * @param string $descrip 模型描述
	 * @param string $author 模型作者
	 */
	function saveModule($mname,$descrip,$author=''){
		global $mid,$charset,$db,$sys;
		require Getlang('module');
		$author=='' && $author = $sys['title'].' ('.$sys['url'].')';
		$sqladd = '';
		if($mid){
			global $moduledb;
			$moduledb[$mid] && Showmsg('mod_midrepeat');
			$sqladd = ",mid='$mid'";
		}
		$descrip = Char_cv($descrip);
		$author = Char_cv($author);
		$db->update("INSERT INTO cms_module SET mname='$mname',descrip='$descrip',author='$author' $sqladd");
		$this->mid = $db->insert_id();
		if($db->server_info() > '4.1'){
			$extra = $charset ? "ENGINE=MyISAM DEFAULT CHARSET=$charset;" : "ENGINE=MyISAM;";
		}else{
			$extra = "TYPE=MyISAM;";
		}
		// 创建一个默认的表
		$this->table = 'cms_content'.$this->mid;
		$query ="CREATE TABLE IF NOT EXISTS `$this->table` (
				  `tid` mediumint(8) unsigned NOT NULL,
				  PRIMARY KEY  (`tid`)
				) $extra";
		$db->query($query);
		//插入默认字段
		/**
		$db->update("INSERT INTO `cms_field` (`mid`, `fieldid`, `fieldname`, `fieldtype`, `fieldsize`, `inputtype`, `inputsize`, `getvalue`, `defaultvalue`, `inputlabel`, `ifgather`, `vieworder`, `ifindex`, `ifsearch`)
			VALUES ('$this->mid', 'photo', '$lang[photo]', 'varchar', '255', 'input', 70, 2, '', '$lang[photo_label]', 0, 1, 0, 0)"); //图片
		$db->update("INSERT INTO `cms_field` (`mid`, `fieldid`, `fieldname`, `fieldtype`, `fieldsize`, `inputtype`, `inputsize`, `getvalue`, `defaultvalue`, `inputlabel`, `ifgather`, `vieworder`, `ifindex`, `ifsearch`)
			VALUES ('$this->mid', 'titlecolor', '$lang[titlecolor]', 'varchar', '10', 'input', 70, 1, '', '', 0, 0, 0, 0)"); //标题颜色
		$db->update("INSERT INTO `cms_field` (`mid`, `fieldid`, `fieldname`, `fieldtype`, `fieldsize`, `inputtype`, `inputsize`, `getvalue`, `defaultvalue`, `inputlabel`, `ifgather`, `vieworder`, `ifindex`, `ifsearch`)
			VALUES ('$this->mid', 'digest', '$lang[digest]', 'smallint', '6', 'radio', 20, 0, '0|1|2|3', '$lang[digest_0]|$lang[digest_1]|$lang[digest_2]|$lang[digest_3]', 0, -2, 1, 0)"); //推荐字段
		$db->update("INSERT INTO `cms_field` (`mid`, `fieldid`, `fieldname`, `fieldtype`, `fieldsize`, `inputtype`, `inputsize`, `getvalue`, `defaultvalue`, `inputlabel`, `ifgather`, `vieworder`, `ifindex`, `ifsearch`)
			VALUES ('$this->mid', 'linkurl', '$lang[linkurl]', 'varchar', '255', 'input', 70, 0, '', '$lang[linkurllabel]', 0, 100, 0, 0)"); //外部链接字段
		*/
		$db->update("INSERT INTO `cms_field` (`mid`, `fieldid`, `fieldname`, `fieldtype`, `fieldsize`, `inputtype`, `inputsize`, `getvalue`, `defaultvalue`, `inputlabel`, `ifgather`, `vieworder`, `ifindex`, `ifsearch`, `ifcontribute`)
			VALUES ('$this->mid', 'title', '$lang[title]', 'varchar', '255', 'input', 70, 0, '', '', 1, 0, 1, 1,1)"); //标题字段
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->sql();
	}

	/**
	 * 编辑内容模型
	 *
	 */
	function editModule(){
		global $step,$db,$action,$basename,$mid;
		if(!$step){
			global $moduledb;
			$mname = $moduledb[$this->mid]['mname'];
			$rs = $db->query("SELECT * FROM cms_field WHERE mid='$this->mid' ORDER BY vieworder");
			$fielddb = array();
			while ($f = $db->fetch_array($rs)) {
				$f['fieldsize'] && $f['fieldsize']='('.$f['fieldsize'].')';
				$f['ifgather'] = $f['ifgather'] ? 'YES' : 'NO';
				$f['ifindex'] = $f['ifindex'] ? 'index' : 'index_none';
				$fielddb[] = $f;
			}
			ifcheck($moduledb[$this->mid]['search'],'ifsearch');
			extract($GLOBALS['checks']);
			require_once(R_P.'require/class_const.php');
			$const = new TplConst('MID');
			$vars = $const->getConstByValue($this->mid);
			require PrintEot('header');
			require PrintEot('module');
		}elseif ($step==2){
			$mname = GetGP('mname','P');
			$search = GetGP('search','P');
			$varname = GetGP('varname','P');
			$varid = GetGP('varid','P');
			empty($mname) && Showmsg('mod_nomname');
			$mname = Char_cv($mname);
			$varname = Char_cv($varname);
			$search = $search ? 1 : 0;
			$db->update("UPDATE cms_module SET mname='$mname',search='$search' WHERE mid='$this->mid'");

			require_once(R_P.'require/class_const.php');
			$const = new TplConst('MID');
			$oldconst = $const->getConstByValue($this->mid);
			if($varname && $oldconst) {
				if($oldconst['name']!=$varname) {
					$vararray = array('id'=>$oldconst['id'],'title'=>$mname,'name'=>$varname,'value'=>$this->mid,'varid'=>$varid);
					$const->setConst($vararray);
				}
			}elseif($oldconst && !$varname) {
				$const->delConstByValue($mid);
			}elseif($varname && !$oldconst) {
					$vararray = array('title'=>$mname,'name'=>$varname,'value'=>$this->mid,'id'=>$varid);
					$const->setConst($vararray);
			}

			require_once(R_P.'require/class_cache.php');
			$cache = new Cache();
			$cache->sql();
			$cache->field();
			adminmsg('operate_success');
		}
	}

	/**
	 * 删除内容模型
	 *
	 */
	function delModule(){
		global $db;
		if($this->mid<=2) Showmsg('mod_cannotdel'); //系统内置模型禁止删除
		$rs=$db->get_one("SELECT COUNT(*) as total FROM cms_category WHERE mid='$this->mid'");
		$rs['total']>=1 && Showmsg('mod_delfail');
		$db->update("DELETE FROM cms_module WHERE mid='$this->mid'");
		$db->update("DROP TABLE IF EXISTS `$this->table`");
		$db->update("DELETE FROM cms_field WHERE mid='$this->mid'");
		require_once(R_P.'require/class_const.php');
		$const = new TplConst('MID');
		$const->delConstByValue($this->mid);
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->sql();
		$cache->field();
		adminmsg('operate_success');
	}

	/**
	 * 导出一个内容模型
	 *
	 */
	function exportModule(){
		global $db,$sys,$charset;
		require_once('require/chinese.php');
		$chs = new Chinese($charset,'UTF8');
		$m = $db->get_one("SELECT * FROM cms_module WHERE mid='$this->mid'");
		$moduleConfig ="<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		$moduleConfig .= "<module>\n";
		$moduleConfig .= "<mname>".$m['mname']."</mname>\n";
		$moduleConfig .= "<author>".$m['autor']."</author>\n";
		$moduleConfig .= "<descrip>".$m['descrip']."</descrip>\n";
		$moduleConfig .= "<fields>\n";
		$f = $db->query("SELECT * FROM cms_field WHERE mid='$this->mid' ORDER BY vieworder");
		while ($field = $db->fetch_array($f)) {
			$moduleConfig.= "\t<field ";
			foreach ($field as $key=>$value) {
				if($key == 'mid' || $key=='fid') continue;
				$value = addslashes($value);
				$moduleConfig.= " ".$key."=\"".$value."\" ";
			}
			$moduleConfig.= "></field>\n";
		}
		$moduleConfig .= "</fields>\n";
		$moduleConfig .= "</module>";
		$moduleConfig = $chs->Convert($moduleConfig);
		$filename = 'CMS_mod_'.randomStr(5).'.xml';
		$filesize = strlen($moduleConfig);
		ob_end_clean();
		header('Pragma: no-cache');
		header('Content-Encoding: none');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-type:xml');
		header('Content-Length: '.$filesize);
		echo $moduleConfig;
		exit();
	}

	/**
	 * 导入一个内容模型
	 *
	 */
	function importModule(){
		global $charset,$basename;
		if(empty($_FILES)) Showmsg('mod_nofile');
		foreach ($_FILES as $key=>$value){
			if($key!=='xmlfile') continue;
			$i++;
			if(is_array($value)){
				$filename = $value['name'];
				$tmpfile = $value['tmp_name'];
				$filesize = $value['size'];
			}else{
				$filename = ${$key.'_name'};
				$tmpfile = $$key;
				$filesize = ${$key.'_size'};
			}
		}
		$ext = end(explode('.',$filename));
		if(strtolower($ext)!=='xml') Showmsg('mod_fileexterror');
		$newname = $GLOBALS['timestamp'].'.xml';
		require_once(R_P.'require/class_attach.php');
		$attach = new Attach();
		$attach->postupload($tmpfile,D_P.'data/'.$newname);
		$moduleInfo = $this->xml2php(D_P.'data/'.$newname);
		$this->addModuleInfo($moduleInfo);
		unlink(D_P.'data/'.$newname);
		adminmsg('mod_importok');
	}

	/**
	 * 添加内容模型相关信息
	 *
	 * @param Array $array
	 */
	function addModuleInfo($array){
		global $charset;
		require_once('require/chinese.php');
		$chs = new Chinese('UTF8',$charset);
		$fieldInfo = $moduleInfo = array();
		$i = 0;
		foreach ($array as $element){
			switch ($element['tag']){
				case 'MNAME':
					$moduleInfo['mname'] = $chs->Convert($element['value']);
					break;
				case 'AUTHOR':
					$moduleInfo['author'] = $chs->Convert($element['value']);
					break;
				case 'DESCRIP':
					$moduleInfo['descrip'] = $chs->Convert($element['value']);
					break;
				case 'FIELD':
					$i++;
					foreach ($element['attributes'] as $key=>$value){
						$key = strtolower($key);
						$fieldInfo[$i][$key] = $chs->Convert($value);
					}
					break;
			}
		}
		$this->saveModule($moduleInfo['mname'],$moduleInfo['descrip'],$moduleInfo['author']);
		foreach ($fieldInfo as $field){
			Add_S($field);
			if(in_array($field['fieldid'],$this->system_field)) continue;
			$this->saveField($field,'add');
		}
	}

	function xml2php($url){
		$xml_parser = xml_parser_create();
		$contents = file_get_contents($url);
		xml_parse_into_struct($xml_parser, $contents, $arr_vals);
		xml_parser_free($xml_parser);
		return $arr_vals;
	}
}
InitGP(array('action','step','fid'));
$fid = (int)$fid;
$mod = new module();
$mod->doIt($action);
adminbottom();
?>