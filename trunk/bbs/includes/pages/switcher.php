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

$nova_theme=escape_string($_GET['theme']);

$nova_theme=str_replace("%20"," ",$nova_theme);

if ($_COOKIE['nova_name']==''){
setcookie("nova_theme", $nova_theme, time() +31536000);
}

else{
mysql_query("UPDATE {$db_prefix}members SET theme='$nova_theme' WHERE id='$my_id'");
}

// return to whence they came...

$referer=escape_string($_SERVER['HTTP_REFERER']);

			header("HTTP/1.0 200 OK");
			header("Location: $referer");
			exit;


?>