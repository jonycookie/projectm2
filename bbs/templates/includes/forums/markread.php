<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|   markread.php - marks all posts as read
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

if ($_COOKIE['nova_name']!=''){

// Get member ID...

$name=$_COOKIE['nova_name'];
$name=escape_string($name);

$password=$_COOKIE['nova_password'];
$password=escape_string($password);

$query211 = "select ID from {$db_prefix}members WHERE NAME='$name' AND PASSWORD='$password'" ;
$result211 = mysql_query($query211) or die("markread.php - Error in query: $query211") ;                                  
$id = mysql_result($result211, 0);

$time=time();

mysql_query("UPDATE {$db_prefix}members SET read_all_posts='$time' WHERE id = '$id' ");

// Delete previous entries in posts_read

mysql_query("DELETE FROM {$db_prefix}posts_read WHERE member_id ='$id'");

			header("HTTP/1.0 200 OK");
			header("Location: $nova_domain");
			exit;

}
?>