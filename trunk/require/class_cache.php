<?php
!function_exists('adminmsg') && die("Forbidden");

class Cache{
	var $cacheDir;

	function __construct(){
		$this->cacheDir = D_P.'data/cache/';
		if(!is_writeable($this->cacheDir)) throwError('cachedircannotwrite');
	}

	function Cache(){
		$this->__construct();
	}

	function update($array=''){
		if(empty($array) || !is_array($array)){
			if(R_P==D_P || !file_exists(D_P.'data/cache/config.php')){
				$this->config();
			}
			$this->extension();
			$very['aggrebbs'] && $this->bbs_config();
			$this->updatecache_ftp();
			$this->cate();
			$this->field();
			$this->select();
			$this->TplConst();
			$this->tags();
			$this->comment();
			$this->sqlclean();
			$this->commentclean();
			$this->templateclean();
		} else{
			foreach($array as $value){
				$this->$value();
			}
		}
	}

	function config(){
		global $db;
		$cache="<?php\n";
		$cache_array = "\$very = array(\n";
		$rs = $db->query("SELECT * FROM cms_config WHERE db_name LIKE 'db_%'");
		while ($sitedb = $db->fetch_array($rs)) {
			$key_name = addslashes(substr($sitedb['db_name'],3));
			$cache_array .= "\t'$key_name'=>".pw_var_export($sitedb['db_value'],0,"\t").",\n";
		}
		$cache_array .= ");\n";
		$cache .= $cache_array."?>";
		writeover($this->cacheDir.'config.php',$cache);
	}

	function extension(){
		global $db;
		$cache = "<?php\n";
		$rt = $db->get_one("SELECT * FROM cms_extension WHERE name='ext_config'");
		$rt['value'] = $rt['value'] ? (array)unserialize(str_replace(array("\\\\","\'"),array("\\","'"),$rt['value'])) : array();
		$cache .= "\$ext_config = ".pw_var_export($rt['value'],1).";\n";
		$cache .= $cache_array."?>";
		writeover($this->cacheDir.'ext_config.php',$cache);

	}

	function bbs_config(){
		global $very;
		if(!$very['aggrebbs']) {
			return false;
		}
		$bbs = newBBS($very['bbs_type']);
		$cache = "<?php\n";
		if($very['bbs_type']=='PHPWind'){
			$configdb = '';
			$face = "\$face=array(\n";
			$query = $bbs->mysql->query("SELECT db_name,db_value FROM {$very['bbs_dbpre']}config");
			$notcache = array('db_hackdb','db_hash','db_thread','db_union','db_adminreason','db_head','db_foot','$db_ipban');
			while(@extract(db_cv($bbs->mysql->fetch_array($query)))){
				if(strpos($db_name,'db_')!==false){
					$db_name = key_cv($db_name);
					if(in_array($db_name,$notcache))continue;
					$db_name = stripslashes($db_name);
					$configdb .= "\$$db_name=".pw_var_export($db_value,0).";\n";
				}
			}
			$rs = $bbs->mysql->query("SELECT * FROM {$very['bbs_dbpre']}smiles WHERE type=0 ORDER BY vieworder");
			while(@extract(db_cv($bbs->mysql->fetch_array($rs)))){
				$query = $bbs->mysql->query("SELECT * FROM {$very['bbs_dbpre']}smiles WHERE type='$id' ORDER BY vieworder");
				while($smile=db_cv($bbs->mysql->fetch_array($query))){
					$face .= "\t'$smile[id]'=>array('$path/$smile[path]','$smile[name]','$smile[descipt]'),\n";
				}
			}
			$face .= ");\n";
			$cache .= $configdb.$face;
		}
		$cache .= "?>";
		writeover($this->cacheDir.'bbs_cache.php',$cache);
	}

	function updatecache_ftp(){
		global $db;
		$ftpdb	= '';
		$query	= $db->query("SELECT * FROM cms_config WHERE db_name LIKE 'ftp\_%'");
		while(@extract(db_cv($db->fetch_array($query)))){
			$db_name = key_cv($db_name);
			$ftpdb	.= "\$$db_name='$db_value';\n";
		}
		writeover($this->cacheDir."ftp_config.php","<?php\n".$ftpdb."?>");
	}

	function cate(){
		global $db,$very;
		$cache = "<?php\n";
		$rs = $db->query("SELECT * FROM cms_category ORDER BY taxis DESC");
		$categorye=$subdb=array();
		while ($catedb = $db->fetch_array($rs)) {
			if($catedb['link']){
				$catedb['listurl']=$catedb['link'];
			}else{
				if($catedb['listpub']){
					$file_ext = strtolower(substr(strrchr($catedb['listurl'],"."),1));
					if($file_ext!=$very['htmext']){
						$catedb['listurl'] = str_replace($file_ext,$very['htmext'],$catedb['listurl']);
						$db->update("UPDATE cms_category SET listurl='$very[htmext]' WHERE cid='$catedb[cid]'");
					}
					$catedb['listurl']=$very['htmdir'].'/'.$catedb['listurl'];
				}else{
					$catedb['listurl']='list.php?cid='.$catedb['cid'];
				}
			}
			if ($catedb['up'] == 0) {
				$categorye[] = $catedb;
			} else {
				$subdb[$catedb['up']][] = $catedb;
			}
		}
		foreach ($categorye as $cate) {
			if (empty($cate)) continue;
			$catedb[$cate['cid']] = $cate;
			if (empty($subdb[$cate['cid']])) continue;
			$catedb += $this->get_subcate($subdb,$cate['cid']);
		}
		$cache .= '$catedb = '.$this->N_var_export($catedb,0).";\n";
		$cache .= "\n?>";
		writeover($this->cacheDir.'cate.php',$cache);
	}

	function N_var_export($input,$f = 1,$t = null) {
		$output = '';
		if (is_array($input)) {
			$output .= "array(\n";
			foreach ($input as $key => $value) {
				$output .= $t."\t".$this->N_var_export($key,$f,$t."\t").' => '.$this->N_var_export($value,$f,$t."\t");
				$output .= ",\n";
			}
			$output .= $t.')';
		} elseif (is_string($input)) {
			$output .= $f ? "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'" : "'$input'";
		} elseif (is_int($input) || is_double($input)) {
			$output .= "'".(string)$input."'";
		} elseif (is_bool($input)) {
			$output .= $input ? 'true' : 'false';
		} else {
			$output .= 'NULL';
		}
		return $output;
	}

	function get_subcate($array,$cid,$depth = 1){
		$depth++;
		foreach ($array[$cid] as $cate) {
			if (empty($cate)) continue;
			if ($cate['depth'] != $depth) {
				$cate['depth'] = $depth;
			}
			$catedb[$cate['cid']] = $cate;
			if (empty($array[$cate['cid']])) {
				continue;
			}
			$catedb += $this->get_subcate($array,$cate['cid'],$depth);
		}
		return $catedb;
	}
	/**
	 * 专题栏目缓存
	 *
	 */
	 /*
	function specialcate(){
		global $db,$very;
		$cache = "<?php\n\$catedb=array(\n";
		$rs = $db->query("SELECT * FROM cms_specialcategory ORDER BY taxis DESC");
		while ($catedb = $db->fetch_array($rs)) {
			$cache.="\t'$catedb[cid]'=>array(\n";
			if($catedb['link']){
				$catedb['listurl']=$catedb['link'];
			}else{
				if($catedb['listpub']){
					$catedb['listurl']=$very['htmdir'].'/'.$catedb['listurl'];
				}else{
					$catedb['listurl']='list.php?cid='.$catedb['cid'];
				}
			}
			$cache.=$this->getCache($catedb);

			$cache.="\t),\n";
		}
		$cache.=");\n?>";
		writeover($this->cacheDir.'specialcate.php',$cache);
	}
	*/

	/**
	 * 内容模型字段缓存
	 *
	 */
	function field(){
		global $db,$moduledb;
		$fielddb = array();
		$fieldcache = "\$fielddb=array(\n";
		$rs = $db->query("SELECT * FROM cms_module");
		$mids = array();
		while ($m = $db->fetch_array($rs)) {
			$mids[] = $m['mid'];
			$mid = $m['mid'];
			$fielddb[$mid] = array();
		}
		$rs = $db->query("SELECT * FROM cms_field ORDER BY mid,vieworder");
		while ($field = $db->fetch_array($rs)) {
			$fielddb[$field['mid']][$field['fid']] = $field;
		}
		foreach ($mids as $mid){
			if($mid<1) continue;
			$fieldcache .= "\t$mid => array(\n";
			foreach ($fielddb[$mid] as $fid=>$array){
				$fieldcache .= "\t\t'$fid'=> array(\n";
				$fieldcache .= $this->getCache($array);
				$fieldcache .= "\t\t),\n";
			}
			$fieldcache.="\t),\n";
		}
		$fieldcache.=");\n";
		writeover($this->cacheDir.'field.php',"<?php\n".$fieldcache."?>");
	}

	function singleCate($cid){ //对单一栏目生成缓存，避免频繁读取总的大缓存，一定程度缓解负载
		global $db;
		if(!$cid) return ;
		$cid = (int)$cid;
		$rs = $db->get_one("SELECT * FROM cms_category WHERE cid='$cid'");
		$cache = "<?php\n\$cateinfo = array(\n";
		if($catedb['link']){
			$catedb['listurl']=$catedb['link'];
		}else{
			if($catedb['listpub']){
				$catedb['listurl']=$very['htmdir'].'/'.$catedb['listurl'];
			}else{
				$catedb['listurl']='list.php?cid='.$catedb['cid'];
			}
		}
		$cache.=$this->getCache($rs);
		$cache.=");\n?>";
		writeover($this->cacheDir.'cate_'.$cid.'.php',$cache);
	}

	function delCate($cid){
		unlink($this->cacheDir.'cate_'.$cid.'.php');
		return ;
	}

	function select(){
		global $db;
		$cache = "<?php\n\$selectdb=array(\n";
		$rs = $db->query("SELECT * FROM cms_select");
		while ($s = $db->fetch_array($rs)) {
			$cache.="'$s[selectid]'=>array(\n";
			$cache.="\t'selectid'=>'$s[selectid]',\n";
			$cache.="\t'selectname'=>'".addslashes($s['selectname'])."',\n";
			$cache.="),\n";
		}
		$cache.=");\n?>";
		writeover($this->cacheDir.'select.php',$cache);
	}

	/**
	 * 倘若一个缓存类没有被实例化，被静态调用时，则使用此方法
	 *
	 * @param string $cachetype
	 */
	function writeCache($cachetype){
		$cache = new Cache();
		$cache->$cachetype();
	}

	/**
	 * 根据数组来格式化缓存内容
	 *
	 * @param Array $array
	 * @return String
	 */
	function getCache($array){
		$cache = '';
		foreach ($array as $key=>$value){
			!is_numeric($value) && $value = addslashes($value);
			$cache.="\t'$key'=>'$value',\n";
		}
		return $cache;
	}

	function writeVar($varname,$arrayvalue){
		$msg="\$$varname=array(\n";
		$i=0;
		foreach ($arrayvalue as $v){
			$i++;
			$msg.="\t'$i'=>array(\n";
			foreach ($v as $key=>$val) {
				$val=addslashes($val);
				$msg.="\t\t'$key'\t=>'$val',\n";
			}
			$msg.="\t),\n";
		}
		$msg.=");\n";
		return $msg;
	}

	function sql($setting=array()){
		global $db;
		require GetLang('dbset');
		include D_P.'data/sql_config.php';

		empty($pconnect) && $pconnect=0;

		if($setting && is_array($setting)){
			$setting['user'] && $manager = $setting['user'];
			$setting['pwd'] && $manager_pwd = $setting['pwd'];
		}
		$modulearray='';
		$rs = $db->query("SELECT * FROM cms_module");
		while ($mod = $db->fetch_array($rs)) {
			$modinfo[$mod['mid']] = $mod;
		}
		$modinfo['-2'] = array(
		'mid'=>'-2',
		'mname'=>"$lang[modbbs]",
		);
		$modinfo['-1'] = array(
		'mid'=>'-1',
		'mname'=>"$lang[modblog]",
		);
		foreach($modinfo as $key=>$value){
			$modulearray.="\t'$key'=>array(\n";
			foreach($value as $k => $val){
				$val = addslashes($val);
				$modulearray.="\t\t'$k'=>'$val',\n";
			}
			$modulearray.="\t),\n";
		}

		$writetofile=
		"<?php
/**
* $lang[info]
*/
\$dbhost\t\t=\t'$dbhost';\t\t// $lang[dbhost]
\$dbuser\t\t=\t'$dbuser';\t\t// $lang[dbuser]
\$dbpw\t\t=\t'$dbpw';\t\t// $lang[dbpw]
\$dbname\t\t=\t'$dbname';\t\t// $lang[dbname]
\$database\t=\t'mysql';\t\t// $lang[database]
\$_pre\t\t=\t'$_pre';\t\t// $lang[PW]
\$pconnect\t=\t'$pconnect';\t\t//$lang[pconnect]

/*
$lang[charset]
*/
\$charset\t\t=\t\t'$charset';

/**
* $lang[ma_info]
*/
\$manager\t\t=\t\t'$manager';\t\t//$lang[manager_name]
\$manager_pwd\t=\t\t'$manager_pwd';\n//$lang[manager_pwd]

/**
* $lang[module]
*/
\$moduledb=array(
$modulearray
);
".'?>';
		writeover(D_P.'data/sql_config.php',$writetofile);
	}

	function TplConst(){
		global $db;
		$vardb = "<?php";
		$varids = array();
		$query = $db->query("SELECT name,value,type FROM cms_const");
		while(@extract($db->fetch_array($query))){
			if($name){
				$varids[$name] = htmlchars_decode($value);
			}
		}
		$vardb .= "\n\$TplConstDB=".pw_var_export($varids).";\n?>";
		writeover($this->cacheDir."constcache.php",$vardb);
	}

	function tags(){
		global $db;
		$tagsdb = "<?php";
		$tags = array();
		$query = $db->query("SELECT tagid,tagname,num FROM cms_tags ORDER BY num DESC LIMIT 0,30");
		while($rt = $db->fetch_array($query)){
			$rt['tagname'] = htmlchars_decode($rt['tagname']);
			$tags[] = $rt;
		}
		$tagsdb .= "\n\$hottags=".pw_var_export($tags).";\n?>";
		writeover($this->cacheDir."tagscache.php",$tagsdb);
	}

	function comment(){
		global $db;
		$cache = "<?php\n\$facedb=array(\n";
		$rs = $db->query("SELECT * FROM cms_commentface ORDER BY taxis DESC");
		while ($face = $db->fetch_array($rs)) {
			$cache .= "'".$face['id']."'=>array(\n";
			$cache .= $this->getCache($face);
			$cache .= "),\n";
		}
		$cache.=");\n?>";
		writeover(D_P.'data/cache/face.php',$cache);
	}

	function sqlclean() {
		$fp = opendir(D_P.'data/sql');
		while ($filename = readdir($fp)) {
			if($filename=='..' || $filename=='.' || $filename=='index.html') continue;
			@unlink(D_P.'data/sql/'.$filename);
		}
		closedir($fp);
	}

	function commentclean() {
		$fp = opendir(D_P.'data/comment');
		while ($filename = readdir($fp)) {
			if($filename=='..' || $filename=='.' || $filename=='index.html') continue;
			@unlink(D_P.'data/comment/'.$filename);
		}
		closedir($fp);
	}

	function templateclean() {
		$fp = opendir(D_P.'data/tpl_cache');
		while ($filename = readdir($fp)) {
			if($filename=='..' || $filename=='.' || $filename=='index.html') continue;
			@unlink(D_P.'data/tpl_cache/'.$filename);
		}
		closedir($fp);
	}
}
?>