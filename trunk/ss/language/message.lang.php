<?

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: message.lang.php 11588 2009-03-11 03:03:23Z zhaofei $
*/

if(!defined('IN_SUPESITE')) {
	exit('Access Denied');
}

$mlang = array (
	'title' => '提示消息',
	'back' => '返回上一页',
	'index' => '进入首页',
	'confirm' => '确定',
	'close' => '关闭',
	'do_success' => '进行的操作完成了',
	'site_close' => '站点临时关闭，请稍后再访问',
	'not_found' => '出错了，您请求的页面没有找到',
	'not_found_msg' => '出错了，您要查看的页面内容信息没有找到',
	'not_view' => '出错了，您要查看的信息没有公开发布',
	'no_permission' => '出错了，您现在没有权限进行本操作',
	'seccode_error' => '出错了，您输入的验证码不正确，请确认',
	'no_login' => '出错了，请您先登录系统后再进行本操作',
	'system_error' => '出错了，您的操作不正确，请检查您的操作',
	'message_length_error' => '出错了，您输入的内容长度不符合要求，请返回检查',
	'no_reply' => '出错了，您没有权限对该主题进行评论，请返回',
	'login_error' => '出错了，您输入的账号信息不正确，请尝试重新登录',
	'user_delete' => '用户被删除，请联系管理员',
	'login_succeed' => '操作完成，您已经成功登录站点系统了',
	'logout_succeed' => '操作完成，您已经成功退出站点系统了',
	'poll_repeat' => '出错了，您已经投过票了，不能重复投票',
	'no_votekey' => '出错了，您没有选择要投票的选项',
	'the_system_does_not_allow_searches'=>'您未登录，系统不允许搜索',
	'inquiries_about_the_short_time_interval'=>'出错了，您两次查询的时间间隔太短，请稍后再继续搜索',
	'not_find_relevant_data'=>'没有找到相关数据，请更换查询关键字试试',
	'search_types_of_incorrect_information'=>'出错了，搜索信息类型不正确',
	'keyword_import_inquiry'=>'出错了，请输入您要查询的关键字',
	'kwyword_import_short' => '出错了,输入的关键字长度需大于2个字符',
	'page_limit' => '出错了，您要查看页数太大了，请选择其他条件查看列表',
	'view_images_do_not_exist' => '出错了，查看的图片不存在',
	'error_view' => '出错了，相册不存在或者您没有权限查看',

	//admincp.php
	'admincp_login' => '您没有登录站内系统，请先登录',

	//batch.comment.php
	'words_can_not_publish_the_shield' => '出错了，您输入的内容中因为含有被屏蔽的词语而不能发布',
	'comment_too_much' => '您评论太快了，稍等半分钟再试试',

	//register.php
	'start_listcount_error' => '出错了，您要查看页数不存在',
    'not_found_tag' => '暂时没有找到指定的tag信息',

	//do_register.php
	'incorrect_code' => '输入的验证码不符，请重新确认',
	'submit_invalid' => '您的请求来路不正确或表单验证串不符，无法提交。请尝试使用标准的web浏览器进行操作。',
	'not_open_registration' => '非常抱歉，本站目前暂时不开放注册',
	'registered' => '注册成功了',
	'system_error' => '系统错误，未找到UCenter Client文件',
	'password_inconsistency' => '两次输入的密码不一致',
	'profile_passwd_illegal' => '密码空或包含非法字符，请重新填写。',
	'user_name_is_not_legitimate' => '用户名不合法',
	'include_not_registered_words' => '用户名包含不允许注册的词语',
	'email_format_is_wrong' => 'Email 格式有误',
	'email_not_registered' => 'Email 不允许注册',
	'email_has_been_registered' => 'Email 已经被注册',
	'register_error' => '注册失败',
	'user_name_already_exists' => '用户名已经存在',

	//batch.epitome.php,batch.thumb.php
	'parameter_chenged' => '禁止篡改参数',
	'GD_lib_no_load' => '没有加载GD库。',
	'image_little' => '图片太小，无法裁切',

	//batch.modeldownload.php
	'visit_the_channel_does_not_exist' => '您访问的频道不存在,请返回首页.',
	'downloading_short_time_interval' => '出错了，您下载的时间间隔太短,请稍后再继续下载.',

	//viewpro.php
	'uc_client_dir_error' => 'UCenter连接有误，请与管理员联系。',
	'space_does_not_exist' => '指定的用户空间不存在',
	
	//blogdetail.php
	'blog_no_info' => '日志不存在',

	//source/do_lostpasswd.php
	'user_does_not_exist' => '该用户不存在',
	'getpasswd_illegal' => '您所用的 ID 不存在或已经过期，无法取回密码。',
	'getpasswd_succeed' => '您的密码已重新设置，请使用新密码登录。',
	'getpasswd_account_invalid' => '对不起，创始人、受保护用户或有站点设置权限的用户不能使用取回密码功能，请返回。',
	'mail_send_fail' => '邮件发送失败!请联系管理员',
	'email_username_does_not_match' => '输入的Email地址与用户名不匹配，请重新确认。',
	'email_send_success' => '取回密码的方式已经发送到您的邮箱中，请于3天之内取回您的密码',
    'link_failure' => '链接失效'
);

?>