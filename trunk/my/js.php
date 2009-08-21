<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: js.php 6565 2008-03-14 09:26:09Z liguode $
*/

@define('IN_UCHOME', TRUE);

$id = empty($_GET['id'])?0:intval($_GET['id']);
$adid = empty($_GET['adid'])?0:intval($_GET['adid']);

if($id) {
	//模块
	include_once('./data/data_block.php');
	if(!isset($_SGLOBAL['block'][$id])) {
		echo 'document.write(\'No data.\')';
		exit();
	}
	
	$updatetime = $_SGLOBAL['block'][$id];
	
	//缓存
	$cachefile = "./data/block_cache/block_$id.js";
	if($updatetime > 0 && file_exists($cachefile) && (time() - filemtime($cachefile) < $updatetime)) {
		if(@$fp = fopen($cachefile, 'r')) {
			@$content = fread($fp, filesize($cachefile));
			fclose($fp);
		} else {
			$content = 'document.writeln(\'No data.\')';
		}
		echo $content;
		exit();
	}
	
	//读取数据
	include('./common.php');
	
	//禁止缓存
	$_SCONFIG['allowcache'] = 0;
	
	include template("data/blocktpl/$id");
	
	$obcontent = ob_get_contents();
	obclean();
	
	$s = array("/(\r|\n)/", "/\<div\s+class=\"pages\"\>.+?\<\/div\>/is");
	$r = array("\n", '');

	$content = '';
	if($obcontent) {
		$obcontent = preg_replace($s, $r, $obcontent);
		$lines = explode("\n", $obcontent);
		foreach ($lines as $line) {
			$line = addcslashes(trim($line), '/\'\\');
			$content .= "document.writeln('$line');\n";
		}
	} else {
		$content .= "document.writeln('NO DATA')";
	}
	if($updatetime > 0) swritefile($cachefile, $content);
	echo $content;

} elseif ($adid) {
	//读取广告文件
	$file = './data/adtpl/'.$adid.'.htm';
	if(@$lines = file($file)) {
		foreach ($lines as $line) {
			$line = addcslashes(trim($line), '/\'\\');
			echo "document.writeln('$line');\n";
		}
	} else {
		echo "document.writeln('NO AD.')";
	}
}

?>