<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   install.php - Module install script for Shoutbox
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

// tell the database our theme name...

mysql_query("UPDATE {$db_prefix}themes SET display_name='NovaBoard Default' WHERE theme_name='novaboard_default'");

?>