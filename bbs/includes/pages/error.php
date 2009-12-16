<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   error.php - default error message
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

$error=$_GET['error'];
$error=escape_string($error);

template_hook("pages/error.template.php", "start");

template_hook("pages/error.template.php", "1");

template_hook("pages/error.template.php", "end");

?>