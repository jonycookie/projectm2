<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id$
*/

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function dir_clear($dir) {
	global $lang;

	showjsmessage($lang['clear_dir'].' '.$dir);
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	$directory->close();
	@touch($dir.'/index.htm');
	result(1, 1, 0);
}

function instheader() {
	global $charset, $lang, $version;

	echo "<html><head>".
		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">".
		"<title>Discuz! Board Installation Wizard $version </title>".
		"<link rel=\"stylesheet\" type=\"text/css\" id=\"css\" href=\"install/style.css\"></head>".
		"<body bgcolor=\"#3A4273\" text=\"#000000\">".
		"<table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\" align=\"center\"><tr><td>".
      		"<table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tr>".
          	"<td class=\"install\" height=\"30\" valign=\"bottom\"><font color=\"#FF0000\">&gt;&gt;</font> Discuz! $version&nbsp;$lang[install_wizard] ".
          	"</td></tr><tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr>";
}

function instfooter() {
	global $version;

	echo "<tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr>".
        	"<tr><td align=\"center\">".
            	"<b style=\"font-size: 11px\">Powered by <a href=\"http://discuz.net\" target=\"_blank\">Discuz! $version".
          	"</a> &nbsp; Copyright &copy; <a href=\"http://www.comsenz.com\" target=\"_blank\">Comsenz Inc.</a> 2001-2007</b><br /><br />".
          	"</td></tr></table></td></tr></table>".
		"</body></html>";
}

function instmsg($message, $url_forward = '') {
	global $lang, $msglang;

	instheader();

	$message = $msglang[$message] ? $msglang[$message] : $message;

	if($url_forward) {
		$message .= "<br /><br /><br /><a href=\"$url_forward\">$message</a>";
		$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
	} elseif(strpos($message, $lang['return'])) {
		$message .= "<br /><br /><br /><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a>";
	}

	echo 	"<tr><td style=\"padding-top:100px; padding-bottom:100px\"><table width=\"560\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">".
		"<tr bgcolor=\"#3A4273\"><td width=\"20%\" style=\"color: #FFFFFF; padding-left: 10px\">$lang[error_message]</td></tr>".
  		"<tr align=\"center\" bgcolor=\"#E3E3EA\"><td class=\"message\">$message</td></tr></table></tr></td>";

	instfooter();
	exit;
}

function loginit($logfile) {
	global $lang;
	showjsmessage($lang['init_log'].' '.$logfile);
	$fp = @fopen('./forumdata/logs/'.$logfile.'.php', 'w');
	@fwrite($fp, '<'.'?PHP exit(); ?'.">\n");
	@fclose($fp);
	result(1, 1, 0);
}

function showjsmessage($message) {
	echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
	flush();
	ob_flush();
}

function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

function result($result = 1, $output = 1, $html = 1) {
	global $lang;

	if($result) {
		$text = $html ? '<font color="#0000EE">'.$lang['writeable'].'</font><br />' : $lang['writeable']."\n";
		if(!$output) {
			return $text;
		}
		echo $text;
	} else {
		$text = $html ? '<font color="#FF0000">'.$lang['unwriteable'].'</font><br />' : $lang['unwriteable']."\n";
		if(!$output) {
			return $text;
		}
		echo $text;
	}
}

function redirect($url) {

	echo "<script>".
		"function redirect() {window.location.replace('$url');}\n".
		"setTimeout('redirect();', 0);\n".
		"</script>";
	exit();

}

function runquery($sql) {
	global $lang, $dbcharset, $tablepre, $db;

	$sql = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				showjsmessage($lang['create_table'].' '.$name.' ... '.$lang['succeed']);
				$db->query(createtable($query, $dbcharset));

			} else {
				$db->query($query);
			}

		}
	}
}

function setconfig($string) {
	if(!get_magic_quotes_gpc()) {
		$string = str_replace('\'', '\\\'', $string);
	} else {
		$string = str_replace('\"', '"', $string);
	}
	return $string;
}

?>