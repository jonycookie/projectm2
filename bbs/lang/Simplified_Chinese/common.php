<?php

// Language definitions for frequently used strings
$lang_common = array(

// Text orientation and encoding
'lang_direction'			=>	'ltr',	// ltr (Left-To-Right) or rtl (Right-To-Left)
'lang_identifier'			=>	'zh-CN',

// Number formatting
'lang_decimal_point'		=>	'.',
'lang_thousands_sep'		=>	',',

// Notices
'Bad request'				=>	'存取错误。您使用的连结有误或已失效。',
'No view'					=>	'您沒有权限浏览讨论区。',
'No permission'				=>	'您沒有权限浏览此页。',
'CSRF token mismatch'		=>	'无法确认安全标记。一个可能的原因，也许是您进入某个页面、打算提交表单，或是点击连结后停滯的时间过长。如果您要继续执行您原本的动作，请点击确定按钮。不然您应该点击取消来回到您原先所处的页面。',
'No cookie'					=>	'您已经成功登录，然而 cookie 的部分卻无法设定。请检查您的设定，可能的话您应该启用 cookies 功能来进入这个网站。',


// Miscellaneous
'Forum index'				=>	'论坛首页',
'Submit'					=>	'提交',	// "name" of submit buttons
'Cancel'					=>	'取消', // "name" of cancel buttons
'Preview'					=>	'预览',	// submit button to preview message
'Delete'					=>	'删除',
'Split'						=>	'分离',
'Ban message'				=>	'您已被停权。',
'Ban message 2'				=>	'封锁期满日期在 %s 之后。',
'Ban message 3'				=>	'将您停权的论坛管理员或版面管理员留给您的训息是:',
'Ban message 4'				=>	'有任何疑问请透过 %s 与论坛管理员联系。',
'Never'						=>	'无',
'Today'						=>	'今天',
'Yesterday'					=>	'昨天',
'Forum message'				=>	'论坛信息',
'Maintenance warning'		=>	'<strong>警告！%s已启用。</strong> 请勿做退出的动作，否则您将无法再登录。',
'Maintenance mode'			=>	'论坛维护模式',
'Redirecting'				=>	'载入中',
'Forwarding info'			=>	'在 %s %s后您应该会自动地被导引到新的页面。',
'second'					=>	'秒',	// singular
'seconds'					=>	'秒',	// plural
'Click redirect'			=>	'如果您不想再等，或是您的浏览器沒有自动载入新的页面，请点击此处。',
'Invalid e-mail'			=>	'您输入的电子邮件地址不正确。',
'New posts'					=>	'最新主题',	// the link that leads to the first new post
'New posts title'			=>	'列出自您上次来访以来有新文章的主题。',	// the popup text for new posts links
'Active topics'				=>	'热门主题',
'Active topics title'		=>	'列出包含最新回复的主题。',
'Unanswered topics'			=>	'尚未回复的主题',
'Unanswered topics title'	=>	'列出尚未有回复的主题。',
'Username'					=>	'帐号名称',
'Registered'				=>	'注册日期',
'Write message'				=>	'编写內容:',
'Forum'						=>	'版面',
'Posts'						=>	'文章数',
'Pages'						=>	'页次',
'Page'						=>	'页',
'BBCode'					=>	'BBCode',	// You probably shouldn't change this
'Smilies'					=>	'表情符号',
'Images'					=>	'图片',
'You may use'				=>	'您可以使用: %s',
'and'						=>	'及',
'Image link'				=>	'图片',	// This is displayed (i.e. <image>) instead of images when "Show images" is disabled in the profile
'wrote'						=>	'写',	// For [quote]'s (e.g., User wrote:)
'Code'						=>	'程序代码',		// For [code]'s
'Forum mailer'				=>	'%s 论坛邮件',	// 系统发函签名可自行修改 As in "MyForums Mailer" in the signature of outgoing e-mails
'Write message legend'		=>	'编写您的文章',
'Required information'		=>	'必要信息',
'Reqmark'					=>	'*',
'Required'					=>	'(必填)',
'Required warn'				=>	'所有标示为 %s 的栏位在表单提交前必须完成填写。',
'Crumb separator'			=>	' &#187;&#160;', // The character or text that separates links in breadcrumbs
'Title separator'			=>	' - ',
'Page separator'			=>	'&#160;', //The character or text that separates page numbers
'Spacer'					=>	'&#8230;', // Ellipsis for paginate
'Paging separator'			=>	'&#160;', //The character or text that separates page numbers for page navigation generally
'Previous'					=>	'上一页',
'Next'						=>	'下一页',
'Cancel redirect'			=>	'操作已取消。载入中 &#8230;',
'No confirm redirect'		=>	'尚未确认。操作已取消。载入中 &#8230;',
'Please confirm'			=>	'请确认:',
'Help page'					=>	'使用说明: %s',
'Re'						=>	'回复:',
'Page info'					=>	'页码 [ 第 %1$s 页 共 %2$s 页 ]',
'Item info single'			=>	'%s [ %s ]',
'Item info plural'			=>	'%s [ 第 %s 至 %s 则 共 %s 则 ]', // e.g. Topics [ 10 to 20 of 30 ]
'Info separator'			=>	' ', // e.g. 1 Page | 10 Topics
'Powered by'				=>	'Powered by <strong>%s</strong>',
'Maintenance'				=>	'论坛维护',

// CSRF confirmation form
'Confirm'					=>	'确认',	// Button
'Confirm action'			=>	'确认动作',
'Confirm action head'		=>	'请确认或取消您最后的动作',

// Title
'Title'						=>	'头衔',
'Member'					=>	'会员',	// Default title
'Moderator'					=>	'版面管理员',
'Administrator'				=>	'论坛管理员',
'Banned'					=>	'停权',
'Guest'						=>	'访客',

// Stuff for include/parser.php
'BBCode error 1'			=>	'使用 [/%1$s] 标签沒有相对应的 [%1$s] 标签',
'BBCode error 2'			=>	'[%s] 标签为空',
'BBCode error 3'			=>	'[%1$s] 标签不可嵌入在 [%2$s] 标签里',
'BBCode error 4'			=>	'[%s] 标签不可嵌入在相同类型的标签里',
'BBCode error 5'			=>	'使用 [%1$s] 标签沒有相对应的 [/%1$s] 标签',
'BBCode error 6'			=>	'[%s] 标签里的属性值为空值',
'BBCode nested list'		=>	'[list] 标签不能嵌套使用',
'BBCode code problem'		=>	'您使用的 [code] 标签有问题',

// Stuff for the navigator (top of every page)
'Index'						=>	'首页',
'User list'					=>	'会员列表',
'Rules'						=>  '站规',
'Search'					=>  '搜索',
'Register'					=>  '注册',
'register'					=>	'注册',
'Login'						=>  '登录',
'login'						=>	'登录',
'Not logged in'				=>  '您尚未登录。',
'Profile'					=>	'个人信息',
'Logout'					=>	'退出',
'Logged in as'				=>	'欢迎再度莅临本论坛, %s。',
'Admin'						=>	'管理',
'Last visit'				=>	'您上次来访的时间是: %s',
'Mark all as read'			=>	'把所有文章标记成已读',
'Login nag'					=>	'请选择登录或是注册一个新帐号。',
'New reports'				=>	'新的检举报告',

// Alerts
'New alerts'				=>	'新警告',
'Maintenance alert'			=>	'<strong>警告！</strong>目前论坛正处于维护模式，请勿做退出。一但您在此模式中退出，将无法再登录。',
'Updates'					=>	'PunBB 更新:',
'Updates failed'			=>	'最近一次连接到 punbb.informer.com 的更新服务尝试检查更新失败。有可能是官方提供的更新服务负载过重或是发生故障。如果说这项警告训息在这一两天內都沒有消失，您应该取消自动检查更新的功能，然后改用手动的方式来做程序更新。',
'Updates version n hf'		=>	'PunBB 有新版本，版本号为 %s，可以在 <a href="http://punbb.informer.com/">punbb.informer.com</a> 下载档案。此外，在管理控制台的 "扩展控件" 功能页有新的扩展控件修正档可供安装使用。',
'Updates version'			=>	'PunBB 有新版本，版本号为 %s，可以在 <a href="http://punbb.informer.com/">punbb.informer.com</a> 下载档案。',
'Updates hf'				=>	'在管理控制台的 "扩展控件" 功能页有新的扩展控件修正档可供安装使用。',
'Database mismatch'			=>	'数据库版本不搭配',
'Database mismatch alert'	=>	'您目前使用的 PunBB 数据库必须与最新版的 PunBB 程序相互搭配。否则您的论坛可能无法正常运作。请将您的论坛程序更新到最新的版本。',

// Stuff for Jump Menu
'Go'						=>	'前往',		// submit button in forum jump
'Jump to'					=>	'前往版面:',

// For extern.php RSS feed
'ATOM Feed'					=>	'Atom',
'RSS Feed'					=>	'RSS',
'RSS description'			=>	'%s 里最新发表的主题',
'RSS description topic'		=>	'%s 里最新回复的文章',
'RSS reply'					=>	'回复: ',	// The topic subject will be appended to this string (to signify a reply)

// Accessibility
'Skip to content'			=>	'跳至论坛內容',

// Debug information
'Querytime'					=>	'页面生成时间 %1$s 秒, 共执行查询 %2$s 条',
'Debug table'				=>	'除错信息',
'Debug summary'				=>	'数据库查询性能总结',
'Query times'				=>	'次数',
'Query'						=>	'查询',
'Total query time'			=>	'总查询时间',

);
