<?php

// Language definitions used in install.php
$lang_install = array(

// Install Form
'Install PunBB'				=>	'安装 PunBB %s',
'Install intro'				=>	'要进行 PunBB 论坛程序安装，您必须填写下列表单內容以提供必要的安装信息。在完成表单填写前，请先阅读页面中提供的指示说明。如果您在安装程序中遇到困难，请参考您下载回来的 PunBB 程序压缩档里的说明文件。',
'Part1'						=>	'步骤 1 - 数据库设定',
'Part1 intro'				=>	'为了进行 PunBB 论坛数据库设定，请您填写一些必要的信息。要进行安装程序，您必须清楚下列栏位中所需求的內容。请完成本步骤中表单栏位的数据填写。',
'Database type'				=>	'数据库种类',
'Database name'				=>	'数据库名称',
'Database server'			=>	'数据库主机位址',
'Database username'			=>	'数据库使用者帐号',
'Database password'			=>	'数据库使用者密码',
'Database user pass'		=>	'数据库使用者帐号和密码',
'Table prefix'				=>	'数据表前置字元',
'Database type info'		=>	'PunBB 目前支持 MySQL, PostgreSQL 和 SQLite。假如您在下拉视窗中找不到您要的数据库选项，代表您目前的 PHP 环境中並沒有提供那项数据库种类支持。更多关于各类数据库版本支持信息可以参考 FAQ 文件说明。',
'Mysql type info'			=>	'PunBB 侦测到您的 PHP 环境支持两种不同运作方式的 MySQL。您可以发现有两种选项可供选择 "<em>MySQL Standard</em>" 和 "<em>MySQL Improved</em>"。如果您无法确定要选择哪一种来使用的话，可以试试选择 MySQL Improved，不行的话再选用 MySQL Standard。',
'MySQL InnoDB info'			=>	'PunBB 侦测到您的 MySQL 服务器可能有支持 <a href="http://dev.mysql.com/doc/refman/5.0/en/innodb-overview.html">InnoDB 储存引擎</a>。如果您计划成立一个大型网站的话，选择使用 InnoDB 储存引擎会是个不错的決定。如果您不确定是否有这项功能，建议您不要使用 InnoDB 储存引擎。',
'Database server info'		=>	'请填写数据库服务器主机位址 (例如: <em>localhost</em>，<em>mysql1.example.com</em> 或是 <em>208.77.188.166</em>)。如果您的数据库服务主机並不是採用预设的埠号，您可以在主机位址后面加上指定的埠号 (例如: <em>localhost:3580</em>)。在 SQLite 数据库使用方面，您可以隨意填写或者填写\'localhost\'。',
'Database name info'		=>	'请填写要用来安装 PunBB 的数据库名称，而且这个数据库名称必须是确定存在的。在 SQLite 数据库使用方面，这里应该填写包含路径的数据库。如果这个数据库並不存在的话，PunBB 将会试著建立这个档案。',
'Database username info'	=>	'请填写您要连结的数据库使用者帐号与密码。使用 SQLite 数据库可以忽略这两个项目。',
'Table prefix info'			=>	'(可选用) - 如果您有使用上的需要，您可以指定数据表前置字元。这样您就可以在同一个数据库里安装两份以上的 PunBB 论坛或者是其他应用程序 (例如在每个数据表前加上: <em>foo_</em>)。',
'Part1 legend'				=>	'数据库信息',
'Database type help'		=>	'选择数据库种类。',
'Database server help'		=>	'请填写您的数据库服务器主机位址。使用 SQLite 者可免填本栏。',
'Database name help'		=>	'请填写要用来安装 PunBB 的数据库名称，该数据库名称必须已存在。',
'Database username help'	=>	'连结数据库用的使用者帐号。使用 SQLite 者请忽略本栏。',
'Database password help'	=>	'连结数据库用的使用者密码。使用 SQLite 者请忽略本栏。',
'Table prefix help'			=>	'可选用的数据表前置字元，例如: "foo_"。',
'Part2'						=>	'步骤 2 - 论坛管理员设定',
'Part2 legend'				=>	'论坛管理员信息',
'Part2 intro'				=>	'为了要在安装程序中设定管理 PunBB 论坛的管理员，请您填写一些必要的信息。您可以在稍后建立更多位论坛管理员和版面管理员。',
'Admin username'			=>	'论坛管理员帐号',
'Admin password'			=>	'论坛管理员密码',
'Admin confirm password'	=>	'再确认一次密码',
'Admin e-mail'				=>	'论坛管理员电子邮件',
'Username help'				=>	'介于 2 至 25 个字元。',
'Password help'				=>	'最少 4 个字元，且有区分英文字大小写。',
'Confirm password help'		=>	'再填写一次以供确认。',
'E-mail address help'		=>	'论坛管理员的电子邮件。',
'Part3'						=>	'步骤 3 - 论坛设定',
'Part3 legend'				=>	'论坛信息',
'Part3 intro'				=>	'请您填写一些必要的信息。特別注意安装网址的填写，以及仔细阅读下列所做的说明。',
'Board title'				=>	'论坛名称',
'Board title and desc'		=>	'论坛名称与描述',
'Board description'			=>	'论坛描述',
'Base URL'					=>	'安装网址',
'Board title info'			=>	'请为您安装的 PunBB 论坛填写一个论坛名称以及简短的说明。这些信息将会显示在论坛中每个页面的上部。保持栏位空白将会使用预设的论坛名称和描述。这两项信息稍后都可以在管理控制台更改。',
'Base URL info'				=>	'在填写安装网址时请您特別注意。您必须正确地填写安装网址，不然您的论坛可能无法正常运作。这里所指的网址是您安装 PunBB 论坛的网址 (网址最后不含 / 斜线，例如: <em>http://forum.example.com</em> 或 <em>http://example.com/~myuser</em>)。请注意下列栏位里的数据是 PunBB 安装程序自动预填的，您必须再一次仔细确认。',
'Base URL help'				=>	'PunBB 论坛安装网址 (网址最后不含 / 斜线)。请阅读上面所提供的说明。',
'Pun repository'			=>	'Pun 程序库',
'Pun repository help'		=>	'在 PunBB 安装完成后，安装 pun_repository 扩展控件 ("点一下就安装"扩展控件下载器)。',
'Start install'				=>	'开始安装', // Label for submit button
'Required'					=>	'(必填)',
'Required warn'				=>	'在完成这个安装流程前，所有标示著%s的栏位必须完成填写。',
'Default language'			=>	'预设语言',
'Default language help'		=>	'PunBB 安装之后系统预设的语言。这个项目可以在安装之后另作改变。',
'Choose language'			=>	'更改安装语言',
'Choose language help'		=>	'如果觉得使用您自己的母语可以让您更容易地进行安装，请从清单中选择您要使用的语言。',
'Installer language'		=>	'安装程序语言',
'Choose language legend'	=>	'安装程序语言',

// Install errors
'No database support'		=>	'目前使用的 PHP 环境中並沒有提供 PunBB 所支持的数据库种类。要安装 PunBB，您的 PHP 环境必须至少支持 MySQL、PostgreSQL 或者是 SQLite 其中一种数据库。',
'Missing database name'		=>	'您必须填写一个数据库名称。请返回再做更正。',
'Username too long'			=>	'帐号名称不可超过 25 个字元。请返回再做更正。',
'Username too short'		=>	'帐号名称至少要 2 个字元。请返回再做更正。',
'Pass too short'			=>	'密码至少要 4 个字元。请返回再做更正。',
'Pass not match'			=>	'密码不相符。请返回再做更正。',
'Username guest'			=>	'帐号名称 "guest" 为系统保留字。请返回再做更正。',
'Username BBCode'			=>	'帐号名称內不可包含任何在论坛中使用的 BBCode 标签。请返回再做更正。',
'Username reserved chars'	=>	'帐号名称不可包含 \', " 和 [ 或 ] 等符号。请返回再做更正。',
'Username IP'				=>	'帐号名称不可使用 IP 地址。请返回再做更正。',
'Invalid email'				=>	'您填写的论坛管理员电子邮件不正确。请返回再做更正。',
'Missing base url'			=>	'您必须填写安装网址。请返回再做更正。',
'No such database type'		=>	'\'%s\' 不是一个正确的数据库类型。',
'Invalid MySQL version'		=>	'您目前使用的 MySQL 版本为 %1$s，PunBB 至少需要 MySQL %2$s 才能运作正常。在继续安装之前，您必须先升级您的 MySQL 版本。',
'Invalid table prefix'		=>	'数据表前置字元 \'%s\' 包含不合规定的字元或者名称太长。前置字元可以包含英文字母 a 到 z，任何数字和底线字元。然而它们不可以用数字当做开头。最大长度是 40 个字元。请选择另一个前置字元。',
'SQLite prefix collision'	=>	'数据表前置字元 \'sqlite_\' 为 SQLite 数据库所使用的保留字。请选择另一个前置字元。',
'PunBB already installed'	=>	'一个名为 "%1$susers" 的数据表名称已经存在于 "%2$s" 数据库中。这代表 PunBB 可能已经安装过了或者是其他安装在同一个数据库中的应用程序占用了一个或数个 PunBB 所需要用的数据表名称。如果您要安装数个 PunBB 论坛在同一个数据库里，您必须各別选用不同的数据表前置字元。',
'InnoDB not enabled'		=>	'InnoDB 储存引擎未启用。请选择一个不支持 InnoDB 储存引擎的数据库连结，或者启用您的 MySQL 服务器里的 InnoDB 储存引擎功能。',
'Invalid language'			=>	'您选择的语言不存在或者不正确。请检查之后再试一次。',

// Used in the install
'Default announce heading'	=>	'公告样本',
'Default announce message'	=>	'<p>在这里填写您的公告內容。</p>',
'Default maint message'		=>	"本论坛暂时关闭以进行系统维护。请稍后再来拜访。<br />\\n<br />\\n/论坛管理员",
'Default rules'				=>	'在这里填写您的论坛规则。',
'Default category name'		=>	'测试分区',
'Default forum name'		=>	'测试版面',
'Default forum descrip'		=>	'这只是一个测试版面',
'Default topic subject'		=>	'测试文章',
'Default post contents'		=>	'如果您正在看这里 (我猜您正在这么做)，安装的 PunBB 出现在这里代表它已经在运作！请您现在就登录，然后前往管理控制台去设定您的论坛。',
'Default rank 1'			=>	'新会员',
'Default rank 2'			=>	'会员',

// Installation completed form
'Success description'		=>	'恭喜您! PunBB %s 已经成功安装。',
'Success welcome'			=>	'请依照接下来的说明来完成最后的安装程序。',
'Final instructions'		=>	'最后说明',
'No write info 1'			=>	'重要！要完成最后的安装程序，请您点击下方按钮来下载一个名为 "config.php" 的设定档。接著，您需要将这个设定档上传到您安装 PunBB 的根目录里。',
'No write info 2'			=>	'当您上传 "config.php" 设定档后，PunBB 的安装将会完成！您可以在上传完成后%s。',
'Go to index'				=>	'前往论坛首页',
'Warning'					=>	'警告！',
'No cache write'			=>	'<strong>数据快取目录 (cache) 无法写入！</strong> 为了使 PunBB 运作正常，在安装路径下的 <em>cache</em> 目录必须要具备写入数据的权限才行。请使用 chmod 指令来设定该目录的权限。如果您无法肯定该设成什么权限的话，可以将目录权限设成 0777。',
'No avatar write'			=>	'<strong>储存头像图片的目录无法写入！</strong> 如果您打算开放论坛会员上传他们的头像图片到您的论坛目录里，您必须使存放图片的目录 <em>img/avatars</em> 具备写入数据的权限才行。您可以在稍后自行決定要存放头像图片的路径 (相关功能设定请見管理控制台)。请使用 chmod 指令来设定该目录的权限。如果您无法肯定该设成什么权限的话，可以将目录权限设成 0777。',
'File upload alert'			=>	'<strong>档案似乎无法上传到服务主机！</strong> 如果您打算开放论坛会员上传他们的头像图片您必须在 PHP 中启用 "file_uploads" 配置设定。当档案上传的功能有启用，上传头像图片的相关功能就能在管理控制台中设定使用。',
'Download config'			=>	'下载 config.php 设定档', // Label for submit button
'Write info'				=>	'PunBB 已完成安装！您现在可以 %s。',
);