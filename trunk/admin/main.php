<?php
!defined('IN_ADMIN') && die('Forbidden');

$query = $db->query("SHOW TABLE STATUS");
while ($rs = $db->fetch_array($query)) {
	if (ereg("^$PW",$rs['Name'])){
		$pw_size = $pw_size + $rs['Data_length'] + $rs['Index_length'];
	} else{
		$o_size = $o_size + $rs['Data_length'] + $rs['Index_length'];
	}
}

$filepath 	= R_P;
$o_size		= number_format($o_size/(1024*1024),2);
$pw_size	= number_format($pw_size/(1024*1024),2);
$systemtime	= gmdate("Y-m-d H:i",time()+$sys['timedf']*3600);
$altertime	= gmdate("Y-m-d H:i",$timestamp+$sys['timedf']*3600);
$sysversion = PHP_VERSION;
if(function_exists("gd_info")){
	$gd			= gd_info();
	$gdinfo		= $gd['GD Version'];
}else {
	$gdinfo		= '<span style="color:red">Unknown</span>';
}
$allowurl	= ini_get('allow_url_fopen') ? '<span style="color:green">Supported</span>' : '<span style="color:red">Not supported</span>';
$freetype	= $gd['FreeType Support'] ? '<span style="color:green">Supported</span>' : '<span style="color:red">Not supported</span>';
$sysos      = $_SERVER['SERVER_SOFTWARE'];
$max_upload = ini_get('file_uploads') ? ini_get('upload_max_filesize') : '<span style="color:red">Disabled</span>';
$max_ex_time= ini_get('max_execution_time').' seconds';

@extract($db->get_one("SELECT VERSION() AS dbversion"));
require PrintEot('main');
adminbottom();
?>