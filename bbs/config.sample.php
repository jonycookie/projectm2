<?php
//******************************************************//
// IceBB Configuration File
//******************************************************//
// This is a sample configuration file. In most cases,
// the IceBB installer will automatically generate
// config.php for you, so you usually don't have to
// worry about this. If you do use this file, rename it
// to config.php
//******************************************************//

// You shouldn't touch these unless you're moving to a
// different server
$config['db_engine']	= 'mysql';
$config['db_host']		= 'localhost';
$config['db_user']		= 'root';
$config['db_pass']		= '';
$config['db_prefix']	= 'icebb_';
$config['db_database']		= 'icebb';

$config['cookie_prefix']	= 'icebb_';

// Change this if you don't want to receive MySQL errors via
// e-mail
$config['admin_email']	= 'yourmail@example.com';

$config['lang']			= 'en';

// You can change the root users here. You'll need their ID which
// can be determined by their profile URL. The username will NOT
// work. Example: $config['root_users']	= '1,5,6';
$config['root_users']	= '1';
?>
