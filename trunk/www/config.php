<?php
/* ======= >>> 配置文件 <<<========================== *

 +   许可协议: http://www.KingCMS.com/license/        +

 +   官方网站: http://www.KingCMS.com/                +

 +   电子邮件: KingCMS(a)Gmail.com                    +

 +   系统信息: 开始写于->2008-4-12                    +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== *

     define('[常量名称]' , '[常量值]');

     [*] 代表安装完成后不能修改的参数

     原则上都一次性设置完成后，不要轻易修改参数


 **

	服务器信息

	KC_DB_TYPE    : 数据库类型
	KC_DB_CHARSET : 数据库字符集[*]
	KC_DB_PREFIX  : 数据表前缀[*]
	KC_DB_ADMIN   : 管理员数据表前缀[*]
	  如果是多个KingCMS系统搭建的网站共用一个mysql的时候
	  前缀一致，就可以共用一组管理员账号

*/
	define('KC_DB_TYPE','mysql');
	define('KC_DB_CHARSET','utf8');
	define('KC_DB_PRE','sys');
	define('KC_DB_ADMIN','www');

	define('KC_DB_HOST','localhost');
	define('KC_DB_DATA','www');
	define('KC_DB_USER','root');
	define('KC_DB_PASS','');

	define('KC_DB_SQLITE','fx6i9chs9u63.db3');

/* ------>>> 参数设置 <<<---------------------------- */


/** 默认语言 */

	define('KC_CONFIG_LANGUAGE','zh-cn');

/** 缓存文件夹 */

	define('KC_CACHE_PATH','cache');
/**
	错误记录
		true  : 开启
		false : 关闭
*/
	define('KC_CONFIG_DEBUG',false);