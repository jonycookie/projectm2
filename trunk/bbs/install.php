<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: install4.php 10301 2007-08-25 08:24:28Z cnteacher $
*/
error_reporting(E_ERROR | E_WARNING | E_PARSE);

@set_time_limit(1000);
set_magic_quotes_runtime(0);

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
}

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', '');

$installfile = basename(__FILE__);
$sqlfile = './install/discuz.sql';
$lockfile = './forumdata/install.lock';
$attachdir = './attachments';
$attachurl = 'attachments';
$quit = FALSE;

@include './install/install.lang.php';
@include './install/global.func.php';
@include './config.inc.php';
@include './include/db_'.$database.'.class.php';

$inslang = defined('INSTALL_LANG') ? INSTALL_LANG : '';
$version = '6.0.0 '.$lang[$inslang];

if(!defined('INSTALL_LANG') || !function_exists('instmsg') || !is_readable($sqlfile)) {
	exit("Please upload all files to install Discuz! Board<br />&#x5b89;&#x88c5; Discuz! &#x8bba;&#x575b;&#x60a8;&#x5fc5;&#x987b;&#x4e0a;&#x4f20;&#x6240;&#x6709;&#x6587;&#x4ef6;&#xff0c;&#x5426;&#x5219;&#x65e0;&#x6cd5;&#x7ee7;&#x7eed;");
} elseif(!isset($dbhost) || !isset($cookiepre)) {
	instmsg('config_nonexistence');
} elseif(!ini_get('short_open_tag')) {
	instmsg('short_open_tag_invalid');
} elseif(file_exists($lockfile)) {
	instmsg('lock_exists');
} elseif(!class_exists('dbstuff')) {
	instmsg('database_nonexistence');
}

if(function_exists('instheader')) {
	instheader();
}

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

$action = $_POST['action'] ? $_POST['action'] : $_GET['action'];
if(in_array($action, array('check', 'config'))) {
	if(is_writeable('./config.inc.php')) {
		$writeable['config'] = result(1, 0);
		$write_error = 0;
	} else {
		$writeable['config'] = result(0, 0);
		$write_error = 1;
	}
}

if(!$action) {

	$discuz_license = str_replace('  ', '&nbsp; ', $lang['license']);

?>
<tr><td><b><?=$lang['current_process']?> </b><font color="#0000EE"><?=$lang['show_license']?></font></td></tr>
<tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td><br />
<table width="90%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr><td class="altbg1">
<table width="99%" cellspacing="1" border="0" align="center">
<tr><td><?=$discuz_license?></td></tr>
</table></td></tr></table>
</td></tr>
<tr><td align="center">
<br /><form method="post" action="<?=$installfile?>">
<input type="hidden" name="action" value="check">
<input type="submit" name="submit" value="<?=$lang['agreement_yes']?>" style="height: 25">&nbsp;
<input type="button" name="exit" value="<?=$lang['agreement_no']?>" style="height: 25" onclick="javascript: window.close();">
</form></td></tr>
<?

} elseif($action == 'check') {

?>
<tr><td><b><?=$lang['current_process']?> </b><font color="#0000EE"> <?=$lang['check_config']?></font></td></tr>
<tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td><br />
<?php

	$msg = '';
	$curr_os = PHP_OS;

	if(!function_exists('mysql_connect')) {
		$curr_mysql = $lang['unsupport'];
		$msg .= "<li>$lang[mysql_unsupport]</li>";
		$quit = TRUE;
	} else {
		$curr_mysql = $lang['support'];
	}

	$curr_php_version = PHP_VERSION;
	if($curr_php_version < '4.0.6') {
		$msg .= "<li>$lang[php_version_406]</li>";
		$quit = TRUE;
	}

	if(@ini_get(file_uploads)) {
		$max_size = @ini_get(upload_max_filesize);
		$curr_upload_status = $lang['attach_enabled'].$max_size;
	} else {
		$curr_upload_status = $lang['attach_disabled'];
		$msg .= "<li>$lang[attach_disabled_info]</li>";
	}

	$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';

	$checkdirarray = array(
				'tpl' => './templates',
				'avatar' => './customavatars',
				'attach' => $attachdir,
				'forumdata' => './forumdata',
				'ftemplate' => './forumdata/templates',
				'cache' => './forumdata/cache',
				'threadcache' => './forumdata/threadcaches',
				'log' => './forumdata/logs'
			);

	foreach($checkdirarray as $key => $dir) {
		if(dir_writeable($dir)) {
			$writeable[$key] = result(1, 0);
		} else {
			$writeable[$key] = result(0, 0);
			$langkey = $key.'_unwriteable';
			$msg .= "<li>$lang[$langkey]</li>";
			$quit = TRUE;
		}
	}

	if($quit) {
		$submitbutton = '<input type="button" name="submit" value=" '.$lang['recheck_config'].' " style="height: 25" onclick="window.location=\'?action=check\'">';
	} else {
		$submitbutton = '<input type="submit" name="submit" value=" '.$lang['new_step'].' " style="height: 25">';
		$msg = $lang['preparation'];
	}

?>
<tr><td align="center">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" width="32%"><?=$lang['tips_message']?></td>
</tr><tr>
<td class="message"><?=$msg?></td>
</tr></table><br />
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr class="header"><td></td><td><?=$lang['env_required']?></td><td><?=$lang['env_best']?></td><td><?=$lang['env_current']?></td>
</tr><tr class="option">
<td class="altbg1"><?=$lang['env_os']?></td>
<td class="altbg2"><?=$lang['unlimited']?></td>
<td class="altbg1">UNIX/Linux/FreeBSD</td>
<td class="altbg2"><?=$curr_os?></td>
</tr><tr class="option">
<td class="altbg1"><?=$lang['env_php']?></td>
<td class="altbg2">4.0.6+</td>
<td class="altbg1">4.3.5+</td>
<td class="altbg2"><?=$curr_php_version?></td>
</tr><tr class="option">
<td class="altbg1"><?=$lang['env_attach']?></td>
<td class="altbg2"3><?=$lang['unlimited']?></td>
<td class="altbg1"><?=$lang['enabled']?></td>
<td class="altbg2"><?=$curr_upload_status?></td>
</tr><tr class="option">
<td class="altbg1"><?=$lang['env_mysql']?></td>
<td class="altbg2"><?=$lang['support']?></td>
<td class="altbg1"><?=$lang['support']?></td>
<td class="altbg2"><?=$curr_mysql?></td>
</tr><tr class="option">
<td class="altbg1"><?=$lang['env_diskspace']?></td>
<td class="altbg2">10M+</td>
<td class="altbg1"><?=$lang['unlimited']?></td>
<td class="altbg2"><?=$curr_disk_space?></td>
</tr></table><br />
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr class="header"><td width="33%"><?=$lang['check_catalog_file_name']?></td><td width="33%"><?=$lang['check_need_status']?></td><td width="33%"><?=$lang['check_currently_status']?></td></tr>
<tr class="option">
<td class="altbg1">config.inc.php</td>
<td class="altbg2"><?=$lang['readable']?></td>
<td class="altbg1"><?=$writeable['config']?></td>
</tr><tr class="option">
<td class="altbg1">./templates </td>
<td class="altbg2"><?=$lang['readable']?></td>
<td class="altbg1"><?=$writeable['tpl']?></td>
</tr><tr class="option">
<td class="altbg1"><?=$attachdir?></td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['attach']?></td>
</tr><tr class="option">
<td class="altbg1">./customavatars</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['avatar']?></td>
</tr><tr class="option">
<td class="altbg1">./forumdata</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['forumdata']?></td>
</tr><tr class="option">
<td class="altbg1">./forumdata/templates</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['ftemplate']?></td>
</tr><tr class="option">
<td class="altbg1">./forumdata/cache</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['cache']?></td>
</tr><tr class="option">
<td class="altbg1">./forumdata/threadcaches</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['threadcache']?></td>
</tr><tr class="option">
<td class="altbg1">./forumdata/logs</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['log']?></td>
</tr></table></tr></td>
<tr><td align="center">
<br /><form method="post" action="<?=$installfile?>">
<input type="hidden" name="action" value="config">
<input type="button" name="submit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="window.location='<?=$installfile?>'">&nbsp;
<?=$submitbutton?>
</form></td></tr>
<?php

} elseif($action == 'config') {

?>
<tr><td><b><?=$lang['current_process']?> </b><font color="#0000EE"> <?=$lang['edit_config']?></font></td></tr>
<tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td><br />
<?php

	$inputreadonly = $write_error ? 'readonly' : '';
	$msg = '<li>'.$lang['config_comment'].'</li>';

	if($_POST['saveconfig']) {
		$msg = '';
		$dbhost = setconfig($_POST['dbhost']);
		$dbuser = setconfig($_POST['dbuser']);
		$dbpw = setconfig($_POST['dbpw']);
		$dbname = setconfig($_POST['dbname']);
		$adminemail = setconfig($_POST['adminemail']);
		$tablepre = setconfig($_POST['tablepre']);
		if(empty($dbname)) {
			$msg .= '<li>'.$lang['dbname_invalid'].'</li>';
			$quit = TRUE;
		} else {
			if(!@mysql_connect($dbhost, $dbuser, $dbpw)) {
				$errormsg = 'database_errno_'.mysql_errno();
				$msg .= '<li>'.$lang[$errormsg].'</li>';
				$quit = TRUE;
			} else {
				if(mysql_get_server_info() > '4.1') {
					mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET $dbcharset");
				} else {
					mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`");
				}
				if(mysql_errno()) {
					$errormsg = 'database_errno_'.mysql_errno();
					$msg .= "'<li>$errormsg ".$lang[$errormsg].'</li>';
					$quit = TRUE;
				}

				mysql_close();
			}
		}

		if(strstr($tablepre, '.')) {
			$msg .= '<li>'.$lang['tablepre_invalid'].'</li>';
			$quit = TRUE;
		}

		if(!$quit){
			if(!$write_error) {
				$fp = fopen('./config.inc.php', 'r');
				$configfile = fread($fp, filesize('./config.inc.php'));
				fclose($fp);

				$configfile = preg_replace("/[$]dbhost\s*\=\s*[\"'].*?[\"'];/is", "\$dbhost = '$dbhost';", $configfile);
				$configfile = preg_replace("/[$]dbuser\s*\=\s*[\"'].*?[\"'];/is", "\$dbuser = '$dbuser';", $configfile);
				$configfile = preg_replace("/[$]dbpw\s*\=\s*[\"'].*?[\"'];/is", "\$dbpw = '$dbpw';", $configfile);
				$configfile = preg_replace("/[$]dbname\s*\=\s*[\"'].*?[\"'];/is", "\$dbname = '$dbname';", $configfile);
				$configfile = preg_replace("/[$]adminemail\s*\=\s*[\"'].*?[\"'];/is", "\$adminemail = '$adminemail';", $configfile);
				$configfile = preg_replace("/[$]tablepre\s*\=\s*[\"'].*?[\"'];/is", "\$tablepre = '$tablepre';", $configfile);
				$configfile = preg_replace("/[$]cookiepre\s*\=\s*[\"'].*?[\"'];/is", "\$cookiepre = '".random(3)."_';", $configfile);
				$configfile = preg_replace("/[$]forumfounders\s*\=\s*[\"'].*?[\"'];/is", "\$forumfounders = '1';", $configfile);

				$fp = fopen('./config.inc.php', 'w');
				fwrite($fp, trim($configfile));
				fclose($fp);
			}
			redirect("$installfile?action=admin");
		}
	}

?>
<tr><td align="center">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" width="32%"><?=$lang['tips_message']?></td>
</tr><tr>
<td class="message"><?=$msg?></td>
</tr></table><br />
<form method="post" action="<?=$installfile?>">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr class="header">
<td width="20%"><?=$lang['variable']?></td><td width="30%"><?=$lang['value']?></td><td width="50%"><?=$lang['comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;<span class="redfont"><?=$lang['dbhost']?></span></td>
<td class="altbg2"><input type="text" name="dbhost" value="<?=$dbhost?>" <?=$inputreadonly?> size="30"></td>
<td class="altbg1">&nbsp;<?=$lang['dbhost_comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['dbuser']?></td>
<td class="altbg2"><input type="text" name="dbuser" value="<?=$dbuser?>" <?=$inputreadonly?> size="30"></td>
<td class="altbg1">&nbsp;<?=$lang['dbuser_comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['dbpw']?></td>
<td class="altbg2"><input type="password" name="dbpw" value="<?=$dbpw?>" <?=$inputreadonly?> size="30"></td>
<td class="altbg1">&nbsp;<?=$lang['dbpw_comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['dbname']?></td>
<td class="altbg2"><input type="test" name="dbname" value="<?=$dbname?>" <?=$inputreadonly?> size="30"></td>
<td class="altbg1">&nbsp;<?=$lang['dbname_comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['email']?></td>
<td class="altbg2"><input type="text" name="adminemail" value="<?=$adminemail?>" <?=$inputreadonly?> size="30"></td>
<td class="altbg1">&nbsp;<?=$lang['email_comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;<span class="redfont"><?=$lang['tablepre']?></span></td>
<td class="altbg2"><input type="text" name="tablepre" value="<?=$tablepre?>" <?=$inputreadonly?> size="30"></td>
<td class="altbg1">&nbsp;<?=$lang['tablepre_comment']?></td>
</tr></table><br />
<input type="hidden" name="action" value="config">
<input type="hidden" name="saveconfig" value="1">
<input type="button" name="submit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="window.location='?action=check'">&nbsp;
<input type="submit" name="submit" value=" <?=$lang['new_step']?> " style="height: 25">
</form></td></tr>
<?php

} elseif($action == 'admin') {

?>
<tr><td><b><?=$lang['current_process']?> </b><font color="#0000EE"> <?=$lang['check_env']?></font></td></tr>
<tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td><br />
<?php

	$msg = '<li>'.$lang['add_admin'].'</li>';
	if(!@mysql_connect($dbhost, $dbuser, $dbpw)) {
		$errormsg = 'database_errno_'.mysql_errno();
		$msg .= '<li>'.($lang[$errormsg] ? $lang[$errormsg] : mysql_error()) .'</li>';
		$quit = TRUE;
	} else {
		$curr_mysql_version = mysql_get_server_info();
		if($curr_mysql_version < '3.23') {
			$msg .= '<li>'.$lang['mysql_version_323'].'</li>';
			$quit = TRUE;
		}

		$sqlarray = array(
				'createtable' => 'CREATE TABLE cdb_test (test TINYINT (3) UNSIGNED)',
				'insert' => 'INSERT INTO cdb_test (test) VALUES (1)',
				'select' => 'SELECT * FROM cdb_test',
				'update' => 'UPDATE cdb_test SET test=\'2\' WHERE test=\'1\'',
				'delete' => 'DELETE FROM cdb_test WHERE test=\'2\'',
				'droptable' => 'DROP TABLE cdb_test'
			);

		foreach($sqlarray as $key => $sql) {
			mysql_select_db($dbname);
			mysql_query($sql);
			if(mysql_errno()) {
				$errnolang = 'dbpriv_'.$key;
				$msg .= '<li>'.$lang[$errnolang].'</li>';
				$quit = TRUE;
			}
		}

		$result = mysql_query("SELECT COUNT(*) FROM $tablepre"."settings");
		if($result) {
			$msg .= '<li><font color="#FF0000">'.$lang['db_not_null'].'</font></li>';
			$alert = " onSubmit=\"return confirm('$lang[db_drop_table_confirm]');\"";
		}
	}

	if($_POST['submit']) {

		$username = $_POST['username'];
		$email = $_POST['email'];
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];

		if($username && $email && $password1 && $password2) {
			if($password1 != $password2) {
				$msg .= '<li><font color="#FF0000">'.$lang['admin_password_invalid'].'</font></li>';
				$quit = TRUE;
			} elseif(strlen($username) > 15 || preg_match("/^$|^c:\\con\\con$|��|[,\"\s\t\<\>&]|^�ο�|^Guest/is", $username)) {
				$msg = $lang['admin_username_invalid'];
				$quit = TRUE;
			} elseif(!strstr($email, '@') || $email != stripslashes($email) || $email != htmlspecialchars($email)) {
				$msg = $lang['admin_email_invalid'];
				$quit = TRUE;
			}
		} else {
			$msg .= '<li><font color="#FF0000">'.$lang['admin_invalid'].'</font></li>';
			$quit = TRUE;
		}

		if(!$quit){
			redirect("$installfile?action=install&username=".rawurlencode($username)."&email=".rawurlencode($email)."&password=".md5($password1));
		}

	}

?>
<tr><td align="center">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" width="32%"><?=$lang['tips_message']?></td></tr>
<tr><td class="message"><?=$msg?></td></tr></table><br />
</td></tr>
<tr><td align="center">
<form method="post" action="<?=$installfile?>" <?=$alert?>>
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273">
<td style="color: #FFFFFF; padding-left: 10px" colspan="2"><?=$lang['add_admin']?></td>
</tr><tr>
<td class="altbg1" width="20%">&nbsp;<?=$lang['username']?></td>
<td class="altbg2" width="80%">&nbsp;<input type="text" name="username" value="admin" size="30"></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['admin_email']?></td>
<td class="altbg2">&nbsp;<input type="text" name="email" value="name@domain.com" size="30"></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['password']?></td>
<td class="altbg2">&nbsp;<input type="password" name="password1" size="30"></td>
</tr><tr>
<td class="altbg1">&nbsp;<?=$lang['repeat_password']?></td>
<td class="altbg2">&nbsp;<input type="password" name="password2" size="30"></td>
</tr></table><br />
<input type="hidden" name="action" value="admin">
<input type="button" name="submit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="window.location='?action=config'">&nbsp;
<input type="submit" name="submit" value=" <?=$lang['new_step']?> " style="height: 25">
</form></td></tr>
<?php

} elseif($action == 'install') {

	$username = htmlspecialchars($_GET['username']);
	$email = htmlspecialchars($_GET['email']);
	$password = htmlspecialchars($_GET['password']);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

	$cron_pushthread_week = rand(1, 7);
	$cron_pushthread_hour = rand(1, 8);

	$extcredits = Array
		(
		1 => Array
			(
			'title' => $lang['init_credits_karma'],
			'showinthread' => '',
			'available' => 1
			),
		2 => Array
			(
			'title' => $lang['init_credits_money'],
			'showinthread' => '',
			'available' => 1
			)
		);


$extrasql = <<<EOT
UPDATE cdb_forumlinks SET name='$lang[init_link]', description='$lang[init_link_note]' WHERE id='1';

UPDATE cdb_forums SET name='$lang[init_default_forum]' WHERE fid='2';

UPDATE cdb_onlinelist SET title='$lang[init_group_1]' WHERE groupid='1';
UPDATE cdb_onlinelist SET title='$lang[init_group_2]' WHERE groupid='2';
UPDATE cdb_onlinelist SET title='$lang[init_group_3]' WHERE groupid='3';
UPDATE cdb_onlinelist SET title='$lang[init_group_0]' WHERE groupid='0';

UPDATE cdb_ranks SET ranktitle='$lang[init_rank_1]' WHERE rankid='1';
UPDATE cdb_ranks SET ranktitle='$lang[init_rank_2]' WHERE rankid='2';
UPDATE cdb_ranks SET ranktitle='$lang[init_rank_3]' WHERE rankid='3';
UPDATE cdb_ranks SET ranktitle='$lang[init_rank_4]' WHERE rankid='4';
UPDATE cdb_ranks SET ranktitle='$lang[init_rank_5]' WHERE rankid='5';

UPDATE cdb_usergroups SET grouptitle='$lang[init_group_1]' WHERE groupid='1';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_2]' WHERE groupid='2';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_3]' WHERE groupid='3';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_4]' WHERE groupid='4';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_5]' WHERE groupid='5';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_6]' WHERE groupid='6';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_7]' WHERE groupid='7';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_8]' WHERE groupid='8';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_9]' WHERE groupid='9';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_10]' WHERE groupid='10';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_11]' WHERE groupid='11';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_12]' WHERE groupid='12';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_13]' WHERE groupid='13';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_14]' WHERE groupid='14';
UPDATE cdb_usergroups SET grouptitle='$lang[init_group_15]' WHERE groupid='15';

UPDATE cdb_crons SET name='$lang[init_cron_1]' WHERE cronid='1';
UPDATE cdb_crons SET name='$lang[init_cron_2]' WHERE cronid='2';
UPDATE cdb_crons SET name='$lang[init_cron_3]' WHERE cronid='3';
UPDATE cdb_crons SET name='$lang[init_cron_4]' WHERE cronid='4';
UPDATE cdb_crons SET name='$lang[init_cron_5]' WHERE cronid='5';
UPDATE cdb_crons SET name='$lang[init_cron_6]' WHERE cronid='6';
UPDATE cdb_crons SET name='$lang[init_cron_7]' WHERE cronid='7';
UPDATE cdb_crons SET name='$lang[init_cron_8]' WHERE cronid='8';
UPDATE cdb_crons SET name='$lang[init_cron_9]' WHERE cronid='9';
UPDATE cdb_crons SET name='$lang[init_cron_10]' WHERE cronid='10';
UPDATE cdb_crons SET name='$lang[init_cron_11]', weekday='$cron_pushthread_week', hour='$cron_pushthread_week' WHERE cronid='11';

UPDATE cdb_settings SET value='$lang[init_dataformat]' WHERE variable='dateformat';
UPDATE cdb_settings SET value='$lang[init_modreasons]' WHERE variable='modreasons';
UPDATE cdb_settings SET value='$lang[init_threadsticky]' WHERE variable='threadsticky';
UPDATE cdb_settings SET value='$lang[init_qihoo_searchboxtxt]' WHERE variable='qihoo_searchboxtxt';

UPDATE cdb_styles SET name='$lang[init_default_style]' WHERE styleid='1';

UPDATE cdb_templates SET name='$lang[init_default_template]', copyright='$lang[init_default_template_copyright]' WHERE templateid='1';

UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_1]' WHERE id='1';
UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_2]' WHERE id='2';
UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_3]' WHERE id='3';
UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_4]' WHERE id='4';
UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_5]' WHERE id='5';
UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_6]' WHERE id='6';
UPDATE cdb_bbcodes SET explanation='$lang[init_bbcode_7]' WHERE id='7';
EOT;

?>
<tr><td><b><?=$lang['current_process']?> </b><font color="#0000EE"> <?=$lang['start_install']?></font></td></tr>
<tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td align="center"><br />
<script type="text/javascript">
	function showmessage(message) {
		document.getElementById('notice').value += message + "\r\n";
	}
</script>
<textarea name="notice" style="width: 80%; height: 400px" readonly id="notice"></textarea>

<br /><br />
<input type="button" name="submit" value=" <?=$lang['install_in_processed']?> " disabled style="height: 25" onclick="window.location='index.php'" id="laststep"><br /><br />
<br />
</td></tr>
<?php
	instfooter();

	$fp = fopen($sqlfile, 'rb');
	$sql = fread($fp, filesize($sqlfile));
	fclose($fp);

	runquery($sql);
	runquery($extrasql);

	$timestamp = time();
	$backupdir = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].substr($timestamp, 0, 4)), 8, 6);
	@mkdir('forumdata/backup_'.$backupdir, 0777);

	$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 6)), 8, 6).random(10);

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($onlineip.$timestamp), 0, 4).random(6);

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('authkey', '$authkey')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('siteuniqueid', '$siteuniqueid')");

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('backupdir', '".$backupdir."')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('extcredits', '".addslashes(serialize($extcredits))."')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('attachdir', '$attachdir')");
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('attachurl', '$attachurl')");

	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('videoinfo', '".addslashes(serialize($videoinfo))."')");

	$db->query("DELETE FROM {$tablepre}members");
	$db->query("DELETE FROM {$tablepre}memberfields");
	$db->query("INSERT INTO {$tablepre}members (uid, username, password, secques, adminid, groupid, regip, regdate, lastvisit, lastpost, email, dateformat, timeformat, showemail, newsletter, timeoffset) VALUES ('1', '$username', '$password', '', '1', '1', 'hidden', '".time()."', '".time()."', '".time()."', '$email', '', '0', '1', '1', '9999');");
	$db->query("INSERT INTO {$tablepre}memberfields (uid, bio, sightml, ignorepm, groupterms) VALUES ('1', '', '', '', '')");
	$db->query("UPDATE {$tablepre}crons SET lastrun='0', nextrun='".($timestamp + 3600)."'");

	foreach($optionlist as $optionid => $option) {
		$db->query("INSERT INTO {$tablepre}typeoptions VALUES ('$optionid', '$option[classid]', '$option[displayorder]', '$option[title]', '', '$option[identifier]', '$option[type]', '".addslashes(serialize($option['rules']))."');");
	}

	$db->query("ALTER TABLE {$tablepre}typeoptions AUTO_INCREMENT=3001");

	$yearmonth = date('Ym_', time());

	loginit($yearmonth.'ratelog');
	loginit($yearmonth.'illegallog');
	loginit($yearmonth.'modslog');
	loginit($yearmonth.'cplog');
	loginit($yearmonth.'errorlog');
	loginit($yearmonth.'banlog');

	dir_clear('./forumdata/templates');
	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/threadcaches');

	@touch(DISCUZ_ROOT.$lockfile);

	echo '<script type="text/javascript">document.getElementById("laststep").disabled = false; </script>'."\r\n";
	echo '<script type="text/javascript">document.getElementById("laststep").value = \''.$lang['install_succeed'].'\'; </script>'."\r\n";
	echo '<iframe width="0" height="0" src="index.php"></iframe>';
}
?>
