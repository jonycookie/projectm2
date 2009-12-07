<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
//error_reporting(E_ERROR | E_PARSE);
require_once(dirname(__FILE__)."/../global.php");

//新SQL
$sqlfile = iPATH.'install/iCMS_Install_SQL.sql';
if(!file_exists($sqlfile)) {
	show_msg('最新的SQL不存在,请先将最新的数据库结构文件 iCMS_Install_SQL.sql 已经上传到 ./install 目录下面后，再运行本升级程序');
}

$lockfile = iPATH.'include/update.lock';
if(file_exists($lockfile)) {
	show_msg('警告!您已经升级过iCMS数据库结构<br>
		为了保证数据安全，请立即手动删除 update.php 文件<br>
		如果您想再次升级iCMS，请删除 ./include/update.lock 文件，再次运行安装文件');
}

//提交处理
if(submitcheck('delsubmit')) {
	//删除表
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			$iCMS->db->query("DROP TABLE ".tname($tname));
		}
	}
	//删除字段
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					$iCMS->db->query("ALTER TABLE ".tname($tname)." DROP PRIMARY KEY");//屏蔽错误
				} elseif($col == 'KEY') {
					foreach ($indexs as $index => $value) {
						$iCMS->db->query("ALTER TABLE ".tname($tname)." DROP INDEX `$index`");//屏蔽错误
					}
				} else {
					$iCMS->db->query("ALTER TABLE ".tname($tname)." DROP `$col`");
				}
			}
		}
	}

	show_msg('删除表和字段操作完成了', '?step=2');
}

//处理开始
if(empty($_GET['step'])) {
	//开始
	$_GET['step'] = 0;

	show_msg('<a href="?step=1">升级开始</a><br>本升级程序会参照最新的SQL文,对你的数据库进行升级<br>请确保你已经上传最新的文件 install/iCMS_Install_SQL.sql');

} elseif ($_GET['step'] == 1) {

	//新的SQL
	$sql = openfile($sqlfile);
	preg_match_all("/CREATE\s+TABLE\s+#iCMS@\_\_(.+?)\s+\((.+?)\)\s+(TYPE|ENGINE)\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('最新的SQL不存在,请先将最新的数据库结构文件 iCMS_Install_SQL.sql 已经上传到 ./install 目录下面后，再运行本升级程序');
	}

	//升级表
	$i = empty($_GET['i'])?0:intval($_GET['i']);
	if($i>=count($newtables)) {
		//处理完毕
		show_msg('进入下一步操作', '?step=2');
	}
	//当前处理表
	$newtable = $newtables[$i];
	$newcols = getcolumn($newsqls[$i]);
	//获取当前SQL
	if(!$query = $iCMS->db->query("SHOW CREATE TABLE ".tname($newtable))) {
		//添加表
		preg_match("/(CREATE TABLE .+?)\s+[TYPE|ENGINE]+\=/is", $newsqls[$i], $maths);
		$type = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(DB_CHARSET?" DEFAULT CHARSET=".DB_CHARSET:''): " TYPE=MYISAM";
		$usql = $maths[1].$type;
		$usql = str_replace("CREATE TABLE #iCMS@__", 'CREATE TABLE '.DB_PREFIX, $usql);
		if($iCMS->db->query($usql)) {
			show_msg('添加表 '.tname($newtable).' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>'.shtmlspecialchars($usql));
		} else {
			$msg = '添加表 '.tname($newtable).' 完成';
		}
	} else {

		$value = $iCMS->db->getRow(null,ARRAY_A);
		$oldcols = getcolumn($value['Create Table']);

		//获取升级SQL文
		$updates = array();
		foreach ($newcols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) $updates[] = "DROP PRIMARY KEY";
					$updates[] = "ADD PRIMARY KEY $value";
				}
			} elseif ($key == 'KEY') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['KEY'][$subkey])) {
						if($subvalue != $oldcols['KEY'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(str_replace('mediumtext', 'text', $value) != str_replace('mediumtext', 'text', $oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$updates[] = "ADD `$key` $value";
				}
			}
		}

		//升级处理
		if(!empty($updates)) {
			$usql = "ALTER TABLE ".tname($newtable)." ".implode(', ', $updates);
			if($iCMS->db->query($usql)) {
				show_msg('升级表 '.tname($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.shtmlspecialchars($usql)."</div>");
			} else {
				$msg = '升级表 '.tname($newtable).' 完成';
			}
		} else {
			$msg = '检查表 '.tname($newtable).' 完成，不需升级';
		}
	}

	//处理下一个
	$next = '?step=1&i='.($_GET['i']+1);
	show_msg($msg, $next);

} elseif ($_GET['step'] == 2) {
	//检查需要删除的字段

	//老表集合
	$oldtables = array();
	$rs=$iCMS->db->getArray("SHOW TABLES LIKE '".DB_PREFIX."%'",ARRAY_N);
	$_count=count($rs);
	for($i=0;$i<$_count;$i++){
		$values = array_values($rs[$i]);
		$oldtables[] = $values[0];//分表、缓存
	}

	//新表集合
	$sql = openfile($sqlfile);
	preg_match_all("/CREATE\s+TABLE\s+#iCMS@\_\_(.+?)\s+\((.+?)\)\s+(TYPE|ENGINE)\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];

	//需要删除的表
	$deltables = array();
	$delcolumns = array();

	//老的有，新的没有
	foreach ($oldtables as $tname) {
		$tname = substr($tname, strlen(DB_PREFIX));
		if(in_array($tname, $newtables)) {
			//比较字段是否多余
			$cvalue = $iCMS->db->getRow("SHOW CREATE TABLE ".tname($tname),ARRAY_A);
			$oldcolumns = getcolumn($cvalue['Create Table']);

			//新的
			$i = array_search($tname, $newtables);
			$newcolumns = getcolumn($newsqls[$i]);

			//老的有，新的没有的字段
			foreach ($oldcolumns as $colname => $colstruct) {
				if(!strexists($colname, 'field_')) {
					if($colname == 'PRIMARY') {
						//关键字
						if(empty($newcolumns[$colname])) {
							$delcolumns[$tname][] = 'PRIMARY';
						}
					} elseif($colname == 'KEY') {
						//索引
						foreach ($colstruct as $key_index => $key_value) {
							if(empty($newcolumns[$colname][$key_index])) {
								$delcolumns[$tname]['KEY'][$key_index] = $key_value;
							}
						}
					} else {
						//普通字段
						if(empty($newcolumns[$colname])) {
							$delcolumns[$tname][] = $colname;
						}
					}
				}
			}
		} else {
			$deltables[] = $tname;
		}
	}

	//显示
	show_header();
	echo '<form method="post" action="update.php?step=2">';

	//删除表
	$deltablehtml = '';
	if($deltables) {
		$deltablehtml .= '<table>';
		foreach ($deltables as $tablename) {
			$deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>".DB_PREFIX."$tablename</td></tr>";
		}
		$deltablehtml .= '</table>';
		echo "<p>以下 数据表 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
	}

	//删除字段
	$delcolumnhtml = '';
	if($delcolumns) {
		$delcolumnhtml .= '<table>';
		foreach ($delcolumns as $tablename => $cols) {
			foreach ($cols as $col) {
				if (is_array($col)) {
					foreach ($col as $index => $indexvalue) {
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][KEY][$index]\" value=\"1\"></td><td>".DB_PREFIX."$tablename</td><td>索引 $index $indexvalue</td></tr>";
					}
				} elseif($col == 'PRIMARY') {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][PRIMARY]\" value=\"1\"></td><td>".DB_PREFIX."$tablename</td><td>主键 PRIMARY</td></tr>";
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>".DB_PREFIX."$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 字段 与标准数据库相比是多余的:<br>请删除多余字段</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"?step=3\">请点击进入下一步</a></p>";
	} else {
		echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"?step=3\">直接进入下一步</a></p>";
	}
	show_footer();
	exit();

} elseif ($_GET['step'] == 3) {
	//数据处理
	$iCMS->db->query("INSERT INTO #iCMS@__config VALUES('68','pagehtmdir','html/page/')");
	//更新网站设置
	$tmp=$iCMS->db->getArray("SELECT * FROM `#iCMS@__config`");
	$config_data="<?php\n\t\$config=array(\n";
	for ($i=0;$i<count($tmp);$i++){
		if($tmp[$i]['name']=='rewrite'||$tmp[$i]['name']=='bbs'){
			$_config.="\t\t\"".$tmp[$i]['name']."\"=>\"".addslashes($tmp[$i]['value'])."\",\n";
		}else{
			$_config.="\t\t\"".$tmp[$i]['name']."\"=>\"".$tmp[$i]['value']."\",\n";
		}
	}
	$config_data.=substr($_config,0,-2);
	$config_data.="\t\n);?>";
	writefile(iPATH.'include/site.config.php',$config_data);
	//写log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, 'iCMS');
		fclose($fp);
	}

	show_msg('升级完成，请到后台工具->更新缓存.为了您的数据安全，避免重复升级，请登录FTP删除本文件!');
}


//正则匹配,获取字段/索引/关键字信息
function getcolumn($creatsql) {

	preg_match("/\((.+)\)/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);//特使字符替换
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);//去掉末尾逗号

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if(strtoupper($cname) == 'KEY') {
			$subvalue = trim(substr($value, 3));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols['KEY'][$subcname] = trim(substr($value, (5+strlen($subcname))));
		} elseif(strtoupper($cname) == 'INDEX') {
			$subvalue = trim(substr($value, 5));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols['KEY'][$subcname] = trim(substr($value, (7+strlen($subcname))));
		} elseif(strtoupper($cname) == 'PRIMARY') {
			$newcols['PRIMARY'] = trim(substr($value, 11));
		} else {
			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

//整理sql文
function remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));//空格标准化
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )'), array('', ',', ',','(',')'), $value);//去掉无用符号
	$value = preg_replace('/(text NOT NULL) default \'\'/i',"\\1", $value);//去掉无用符号
	return $value;
}
//ob
function obclean() {
	ob_end_clean();
	if (function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}
//显示
function show_msg($message, $url_forward='') {
	global $_iGLOBAL;

	obclean();

	if($url_forward) {
		$_iGLOBAL['extrahead'] = '<meta http-equiv="refresh" content="1; url='.$url_forward.'">';
		$message = "<a href=\"$url_forward\">$message(跳转中...)</a>";
	} else {
		$_iGLOBAL['extrahead'] = '';
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
	show_footer();
	exit();
}


//页面头部
function show_header() {
	global $_iGLOBAL;

	$nowarr = array($_GET['step'] => ' class="current"');

	if(empty($_iGLOBAL['extrahead'])) $_iGLOBAL['extrahead'] = '';
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	$_iGLOBAL[extrahead]
	<title> iCMS 数据库升级程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #EAEAEA; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #cccccc; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #F7F7F7; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
	.showtable td { padding: 3px; }
	.showtable strong { color: #5086A5; }
	.datatable { width: 100%; margin: 10px auto 25px; }
	.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	.button { margin: 10px auto 20px; width: 100%; }
	.button td { text-align: center; }
	.button input, .button button { border: solid; border-color:#F90; border-width: 1px 1px 3px; padding: 5px 10px; color: #090; background: #FFFAF0; cursor: pointer; }
	#footer { line-height: 40px; background: #F7F7F7; text-align: center; height: 38px; overflow: hidden; color: #333333; margin-top: 20px; font-family: "Courier New", Courier, monospace; }
	</style>
	</head>
	<body>
	<div class="bodydiv"><img src="http://www.idreamsoft.cn/doc/iCMS.logo.gif" width="172" height="68"  style="margin:5px 0px 3px 5px"/>
	<h1>iCMS数据库升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>升级开始</td>
	<td{$nowarr[1]}>数据库结构添加/升级</td>
	<td{$nowarr[2]}>数据库结构删除</td>
	<td{$nowarr[3]}>升级完成</td>
	</tr>
	</table>
	<br>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<div id="footer">&copy; iDreamSoft Inc. 2007-2009 http://www.idreamsoft.cn</div>
	</div>
	<br>
	</body>
	</html>
END;
}
//判断提交是否正确
function submitcheck($var) {
	if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))) {
			return true;
		} else {
			showmessage('submit_invalid');
		}
	} else {
		return false;
	}
}
//获取到表名
function tname($name) {
	return DB_PREFIX.$name;
}
//对话框
function showmessage(){
		if(!empty($url_forward)) {
			$second = $second * 1000;
			$message .= "<script>setTimeout(\"window.location.href ='$url_forward';\", $second);</script>";
		}
}

//取消HTML代码
function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}
?>