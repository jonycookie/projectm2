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

	DB_TYPE    : 数据库类型
	DB_CHARSET : 数据库字符集[*]
	DB_PREFIX  : 数据表前缀[*]
	KC_DB_ADMIN   : 管理员数据表前缀[*]
	  如果是多个KingCMS系统搭建的网站共用一个mysql的时候
	  前缀一致，就可以共用一组管理员账号

*/
	define('DB_TYPE','mysql');
	define('DB_CHARSET','utf8');
	define('DB_PRE','king');
	define('KC_DB_ADMIN','kc');

	define('DB_HOST','localhost');
	define('DB_DATA','test');
	define('DB_USER','root');
	define('DB_PASS','');

	define('DB_SQLITE','iw8ap2wpxo64.db3');

/* ------>>> 参数设置 <<<---------------------------- */


/** 默认语言 */

	define('LANGUAGE','zh-cn');

/** 缓存文件夹 */

	define('PATH_CACHE','_cache');
/**
	错误记录
		true  : 开启
		false : 关闭
*/
	define('DEBUG',false);