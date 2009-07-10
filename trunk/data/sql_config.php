<?php
/**
* 以下为数据库配置信息，请根据你的数据库信息修改，倘若修改之后出现错误，请咨询空间提供商来获取争取的数据库帐号
*/
$dbhost		=	'localhost';		// 数据库服务器地址，一般为localhost
$dbuser		=	'm2';		// 数据库用户名
$dbpw		=	'841105';		// 数据库访问密码
$dbname		=	'cms';		// 数据库名称，注意不要跟用户名，数据库服务器地址混淆
$database	=	'mysql';		// 数据库类型，一般为MySQL，请勿修改
$_pre		=	'cms_';		// 数据表前缀，不同的前缀可以使得一个数据库内安装多次同样的程序而不至于冲突
$pconnect	=	'0';		//是否持续连接

/*
数据库所采用编码
*/
$charset		=		'utf8';

/**
* 系统创始人信息，使用创始人帐号登录，即使数据库损坏，也可以进入后台来修复数据
*/
$manager		=		'admin';		//创始人ID
$manager_pwd	=		'21232f297a57a5a743894a0e4a801fc3';
//创始人密码，此处非明文密码，而是经过MD5加密的密码字符串

/**
* 系统内容模型
*/
$moduledb=array(
	'1'=>array(
		'mid'=>'1',
		'mname'=>'新闻资讯',
		'author'=>'PHPWind',
		'descrip'=>'系统内嵌模型',
		'search'=>'1',
	),
	'2'=>array(
		'mid'=>'2',
		'mname'=>'友情链接',
		'author'=>'PHPWind',
		'descrip'=>'系统内嵌模型',
		'search'=>'0',
	),
	'-2'=>array(
		'mid'=>'-2',
		'mname'=>'论坛整合',
	),
	'-1'=>array(
		'mid'=>'-1',
		'mname'=>'博客整合',
	),

);
?>