<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   logout.php - logout script
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

setcookie("nova_name", "", time() -3600);
setcookie("nova_password", "", time() -3600);
// Remove sessions...
mysql_query("DELETE FROM {$db_prefix}sessions WHERE id='$my_id'");

header("HTTP/1.0 200 OK");
header("Location: $nova_domain");
exit;

?>