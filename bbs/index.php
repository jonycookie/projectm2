<?php

@ini_set('allow_url_fopen', 1);
@ini_set('zlib.output_compression', 1);

// set nova variables

$my_address="http://".$_SERVER['HTTP_HOST']."".$_SERVER['PHP_SELF'];

$nova_domain 	= str_replace('/index.php', '', $my_address); 	// returns http://myforum.com/forum style address
$nova_root		= str_replace('index.php', '', __FILE__); 		// returns /home/account/{account}/path/to/forum style address

// turn off error reporting
error_reporting(0);

// stupid magic quotes!
set_magic_quotes_runtime(0); 

ob_start();

session_start();

define("NOVA_RUN", 1);

// is config.php present?

if(!file_exists("includes/config.php")){ 
	header("HTTP/1.0 200 OK");
	header("Location: install.php"); 
	exit;
}

include "includes/config.php";

include "includes/structure.php";

?>