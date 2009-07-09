<?php
!defined('IN_CMS') && die('Forbidden');
/**
 * 获取一个模板的编译路径 绝对路径
 *
 * @param string $tplname
 * @return string
 */
function Tpl($tplname,$isPlugTpl=false){
	global $cid,$user_tplpath,$default_tplpath,$catedb,$very;
	if(!$catedb) require_once(D_P.'data/cache/cate.php');
	if($isPlugTpl){
		$tplname = $tplname.'.htm';
	}elseif ($tplname==''){
		switch (SCR){
			case 'list':
				if($catedb[$cid]['tpl_index']){
					$tplname = $user_tplpath.'/'.$catedb[$cid]['tpl_index'];
				}else{
					$tplname = $default_tplpath.'/list.htm';
				}
				break;
			case 'view':
				if($catedb[$cid]['tpl_content']){
					$tplname = $user_tplpath.'/'.$catedb[$cid]['tpl_content'];
				}else{
					$tplname = $default_tplpath.'/content.htm';
				}
				break;
			default:
				$webtplname = 'template_'.strtolower(SCR);
				if($very[$webtplname]){
					$tplname = $user_tplpath.'/'.$very[$webtplname];
				}else{
					$tplname = $default_tplpath.'/'.strtolower(SCR).'.htm';
				}
				break;
		}
	}elseif (strpos($tplname,'.')===false){
		$tplname = $default_tplpath.'/'.$tplname.'.htm';
	}else{
		$tplname = $user_tplpath.'/'.$tplname;
	}
	$tplname = Pcv($tplname);
	return getTplName($tplname);
}

/**
 * 编译模板文件并返回编译过的路径
 *
 * @param string $tplname
 * @return string
 */
function getTplName($tplname){
	!file_exists(R_P.$tplname) && throwError($tplname);
	$cache_name = str_replace(array('template/','/'),array('','_'),$tplname);
	$cache_name = Pcv(D_P.'data/tpl_cache/'.$cache_name);

	if(!file_exists($cache_name) || filemtime(R_P.$tplname)>filemtime($cache_name)){
		global $includefile;
		$includefile = array();
		$file_str = readover(R_P.$tplname);
		tplRarseConst($file_str);
		$s = array(
		"{@","@}",
		"\$cms::","\$bbs::","\$cate::","\$blog::","\$db::","\$extend::","\$comment::",
		"<!--#","#-->",
		);
		$e = array(
		"\nEOT;\necho ",";print <<<EOT\n",
		"\$cms->","\$bbs->","\$cate->","\$blog->","\$db->","\$extend->","\$comment->",
		"\nEOT;\n","print <<<EOT\n",
		);
		if(function_exists('str_ireplace')){
			$file_str = str_ireplace($s,$e,$file_str);
		}else{
			$file_str = str_replace($s,$e,$file_str);
		}
		tplRarseVery($file_str);
		tplRarseCMS($file_str);
		tplRarseLoop($file_str);
		tplRarseAjax($file_str);
		$file = "<?php\nif(!defined('IN_CMS')){header('HTTP/1.0 404 Not Found');exit;}\n";
		$includefile = array_unique($includefile);
		foreach($includefile as $val){
			$file .= $val."\n";
		}
		$file .= "print <<<EOT\n$file_str\nEOT;\n?>";
		$file = preg_replace("/print \<\<\<EOT[\n\r]*EOT;/s","",$file);
		writeover($cache_name,$file);
	}

	return $cache_name;
}

/**
 * 解析CMS标签
 *
 * @author aileenguan
 * @param string $string
 * @return string
 */
function tplRarseCMS(&$string){
	global $includefile;
	preg_match_all("/<cms(.+?)\/>/i",$string,$reg);
	$replace = $condition = array();
	foreach ($reg[1] as $id=>$val){
		$parameter = '';
		$condition[$id] = array();
		preg_match_all("/[[:blank:]]+?[a-zA-Z\-_]+=(['|\"]?).*?(\\1)/",$val,$match);
		foreach ($match[0] as $cmsReg){
			$pos = strpos($cmsReg,"=");
			$key = trim(strtolower(substr($cmsReg,0,$pos)));
			$value = substr($cmsReg,$pos+1);
			if (preg_match("/^('|\")(.*?)(\\1)$/",$value,$newValue)) {
				$value = trim($newValue[2]); //去掉单引号和双引号
			}
			$condition[$id][$key] = $value;
			if(!in_array($key,array('type','action','return','name','param'))){
				$parameter.="$key:$value;";
			}
		}
		if (!$condition[$id]['action']) { //Action参数表明要执行的类的方法
			throwError('tpl_noaction');
		}
		if (!$condition[$id]['type']) { //Type参数表明是要调用什么类，默认为cms类
			$condition[$id]['type']='cms';
		}
		if (!$condition[$id]['return']) {
			$condition[$id]['return'] = 'array';
		}
		$replace[$id] = "\nEOT;\n\$".$condition[$id]['return']." = \$".$condition[$id]['type']."->";
		if ($condition[$id]['type'] == 'cms') { //内容列表
			if($condition[$id]['action']=='thread'){
				empty($condition[$id]['param']) && $condition[$id]['param'] = $parameter;
				$replace[$id] .= $condition[$id]['action']."(\"".$condition[$id]['param']."\");";
			}elseif($condition[$id]['action']=='parseTids') {
				if(empty($condition[$id]['param'])){
					$condition[$id]['param'] = $condition[$id]['tids'] ? $condition[$id]['tids'] : 'null';
					$condition[$id]['param'] .= $condition[$id]['num'] ? ',\''.$condition[$id]['num'].'\'' : ',0';
				}
				$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['param'].");";
			}else{
				$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['param'].");";
			}
		}elseif ($condition[$id]['type'] == 'bbs' || $condition[$id]['type'] == 'blog') { //BBS排行
			if(empty($condition[$id]['param'])){
				$condition[$id]['param'] = $condition[$id]['num'] ? $condition[$id]['num'] : 'null';
				$condition[$id]['param'] .= $condition[$id]['order'] ? ',"'.$condition[$id]['order'].'"' : ',null';
				$condition[$id]['param'] .= $condition[$id]['fid'] ? ','.$condition[$id]['fid'] : ',null';
			}
			$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['param'].");";
		}elseif ($condition[$id]['type'] == 'cate'){ //栏目导航类
			if ($condition[$id]['action']=='menu'){
				$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['cid'].");";
			}elseif ($condition[$id]['action']=='child'){
				if($condition[$id]['cid']){
					$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['cid'].");";
				}elseif($condition[$id]['mid']){
					$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['mid'].",1);";
				}
			}
		}elseif ($condition[$id]['type'] == 'extend'){
			if($condition[$id]['name']){
				$replace[$id] = "\nEOT;\n\$".$condition[$id]['return']." = ".$condition[$id]['action']."(".$condition[$id]['param'].");";
				$includefile[] = "include(R_P.'".Pcv('extensions/'.$condition[$id]['name'].'/template.php')."');";
			}else{
				$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['param'].");";
			}
		}elseif ($condition[$id]['type'] == 'comment'){
			if(empty($condition[$id]['param'])){
				$condition[$id]['param'] = $condition[$id]['cid'] ? $condition[$id]['cid'] : 'null';
				$condition[$id]['param'] .= $condition[$id]['num'] ? ',"'.$condition[$id]['num'].'"' : ',null';
			}
			$replace[$id] .= $condition[$id]['action']."(".$condition[$id]['param'].");";
		}
		$replace[$id] .= "print <<<EOT\n";
	}
	$string = str_replace($reg[0],$replace,$string);
}

/**
 * 解析{@ VAR @}标签
 *
 * @author VeryCMS
 * @param string $string
 * @return string
 */
function tplRarseConst(&$string){
	include(D_P."data/cache/constcache.php");
	preg_match_all("/\{@(.+?)@\}/",$string,$reg);
	$replace = $source = array();
	foreach ($reg[1] as $id=>$val){
		if(!preg_match("/^[a-zA-Z0-9_]{3,}$/",$val)){
			continue;
		}
		if(isset($TplConstDB[$val])){
			$replace[] = $TplConstDB[$val];
			$source[] = $reg[0][$id];
		}
	}
	$string = str_replace($source,$replace,$string);
}

/**
 * 解析Loop标签
 *
 * @author VeryCMS
 * @param string $string
 * @return string
 */
function tplRarseLoop(&$string){
	$string = eregi_replace("<loop>","\nEOT;\nforeach(\$array as \$key=>\$val){print <<<EOT\n",$string);
	$string = eregi_replace("</loop>","\nEOT;\n}print <<<EOT\n",$string);
	preg_match_all("/<loop(.+?)>/i",$string,$reg);
	$replace = $condition = array();
	foreach ($reg[1] as $id=>$val){
		$parameter = '';
		$condition = array();
		preg_match_all("/[[:blank:]]+?[a-zA-Z\-_]+=(['|\"]?).*?(\\1)/",$val,$match);
		foreach ($match[0] as $cmsReg){
			$pos = strpos($cmsReg,"=");
			$key = trim(strtolower(substr($cmsReg,0,$pos)));
			$value = substr($cmsReg,$pos+1);
			if (preg_match("/^('|\")(.*?)(\\1)$/",$value,$newValue)){
				$value = trim($newValue[2]);
			}
			$condition[$key] = $value;
		}
		if(!$condition['name']){
			$condition['name']='array';
		}
		if(!$condition['key']){
			$condition['key'] = 'key';
		}
		if(!$condition['value']){
			$condition['value'] = 'val';
		}
		$replace[$id] = "\nEOT;\nforeach(\$".$condition['name']." as \$".$condition['key']."=>\$".$condition['value']."){print <<<EOT\n";
	}
	$string = str_replace($reg[0],$replace,$string);
}

/**
 * 解析Very标签
 *
 * @author VeryCMS
 * @param string $string
 * @return string
 */
function tplRarseVery(&$string){
	include(D_P."data/cache/constcache.php");
	preg_match_all("/<very(.+?)>/i",$string,$reg);
	$replace = $condition = array();
	foreach ($reg[1] as $id=>$val){
		$parameter = '';
		$condition = array();
		preg_match_all("/([[:blank:]]+?[a-zA-Z\-_]+)?=(['|\"]?).*?(\\2)/",$val,$match);
		foreach ($match[0] as $cmsReg){
			$pos = strpos($cmsReg,"=");
			$key = trim(strtolower(substr($cmsReg,0,$pos)));
			$value = substr($cmsReg,$pos+1);
			if (preg_match("/^('|\")(.*?)(\\1)$/",$value,$newValue)){
				$value = trim($newValue[2]);
			}
			empty($key) && $key = 'value';
			$condition[$key] = $value;
		}
		if(!$condition['name']){
			$condition['name']='very';
		}
		if($condition['name'] == 'const'){//常量
			$replace[$id] = $TplConstDB[$condition['value']];
		}elseif($condition['name'] == 'cate'){//栏目变量$catedb
			$replace[$id] = "\$catedb[".$condition['cid']."][".$condition['value']."]";
		}elseif($condition['name'] == 'module'){//模型变量$moduledb
			$replace[$id] = "\$moduledb[".$condition['mid']."][".$condition['value']."]";
		}elseif($condition['name'] == 'very'){//站点全局变量$very
			$replace[$id] = "\$very[".$condition['value']."]";
		}elseif($condition['name'] == 'view'){//内容页面单内容变量$view
			$replace[$id] = "\$view[".$condition['value']."]";
		}else{
			$replace[$id] = '';
		}
	}
	$string = str_replace($reg[0],$replace,$string);
}

/**
 * 解析ajax标签
 *
 * @author VeryCMS
 * @param string $string
 * @return string
 */
function tplRarseAjax(&$string){
	preg_match_all("/<ajax(.+?)\/>/i",$string,$reg);
	$replace = $condition = array();
	$i=1;
	foreach ($reg[1] as $id=>$val){
		$parameter = '';
		$condition[$id] = array();
		preg_match_all("/[[:blank:]]+?[a-zA-Z\-_]+=(['|\"]?).*?(\\1)/",$val,$match);
		foreach ($match[0] as $cmsReg){
			$pos = strpos($cmsReg,"=");
			$key = trim(strtolower(substr($cmsReg,0,$pos)));
			$value = substr($cmsReg,$pos+1);
			if (preg_match("/^('|\")(.*?)(\\1)$/",$value,$newValue)) {
				$value = trim($newValue[2]); //去掉单引号和双引号
			}
			$condition[$id][$key] = $value;
			if(!in_array($key,array('styleid','thisid','param'))){
				$parameter.="$key:$value;";
			}
		}
		if (!$condition[$id]['mid'] || is_int($condition[$id]['mid'])) { //mid为必要参数
			throwError('mod_nomid');
		}
		if (!$condition[$id]['num']) {
			throwError('cms_nonum');
		}
		if (!$condition[$id]['styleid']) {
			throwError('cms_nostyleid');
		}
		if (!$condition[$id]['thisid']) {
			throwError('cms_nothisid');
		}
		empty($condition[$id]['param']) && $condition[$id]['param'] = $parameter;
		if($i==1){
			$replace[$id] = "<script src=\"script/template.js\"></script>\r\n";
		}
		$replace[$id] .= "\nEOT;\n\$cms->getAjax(\"".$condition[$id]['param']."\",\"".$condition[$id]['thisid']."\",\"".$condition[$id]['styleid']."\");\nprint <<<EOT\n";
		$i++;
//		$ajaxfile = substr(md5($condition[$id]['param']),0,32).".js";
//		if(!file_exists(D_P."data/js/".$ajaxfile)||time()-filemtime(D_P."data/js/".$ajaxfile)>60){
//			!is_object($ajax) && $ajax = new Ajax();
//			$ajax->mid		 = $condition[$id]['mid'];
//			$ajax->condition = $condition[$id]['param'];
//			$ajax->cachefile = D_P."data/js/".$ajaxfile;
//			$ajax->thisid	 = $condition[$id]['thisid'];
//			$ajax->getThread();
//		}
//		$replace[$id] = "<script src=\"$very[url]/data/js/".$ajaxfile."\"></script>\r\n";
	}
	$string = str_replace($reg[0],$replace,$string);
}
?>