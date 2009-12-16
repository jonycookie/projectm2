<?php

/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   switcher.php - change theme from drop-down list
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

$nova_lang=$_GET['language'];
$nova_lang = escape_string($nova_lang);

if ($_COOKIE['nova_name']==''){
setcookie("nova_lang", $nova_lang, time() +31536000);
}

else{
mysql_query("UPDATE {$db_prefix}members SET board_lang='$nova_lang' WHERE id='$my_id'");
}

// return to whence they came...

$referer=$_SERVER['HTTP_REFERER'];

			header("HTTP/1.0 200 OK");
			header("Location: $referer");
			exit;

?>