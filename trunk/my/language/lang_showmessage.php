<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: lang_showmessage.php 7352 2008-05-13 01:41:40Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['msglang'] = array(

	//common
	'do_success' => '进行的操作完成了',
	'no_privilege' => '您没有权限进行此操作',

	//mt.php
	'designated_election_it_does_not_exist' => '指定的选吧不存在',

	//do.php
	'enter_the_space' => '进入个人空间页面',
	'enter_the_network' => '进入随便看看页面',

	//network.php
	'points_deducted_yes_or_no' => '本次操作将扣减您 \\1 个积分，确认继续？<p><a href="\\2" class="submit">继续操作</a> &nbsp; <a href="network.php?ac=\\3" class="submit">取消操作</a></p>',

	//source/cp_album.php
	'photos_do_not_support_the_default_settings' => '默认相册不支持本设置',
	'album_name_errors' => '您没有正确设置相册名',
	
	//source/space_app.php
	'correct_choice_for_application_show' => '请选择正确的应用进行查看',

	//source/do_login.php
	'users_were_not_empty_please_re_login' => '对不起，用户名不能为空，请重新登录',
	'login_failure_please_re_login' => '对不起,登录失败,请重新登录',

	//source/cp_blog.php
	'no_authority_to_add_log' => '您目前没有权限添加日志',
	'no_authority_operation_of_the_log' => '您没有权限操作该日志',
	'that_should_at_least_write_things' => '至少应该写一点东西',
	'failed_to_delete_operation' => '删除失败，请检查操作',
	'trace_no_self' => '自己不能给自己留脚印',
	'trace_have' => '您已经留过脚印了',
	'trace_success' => '您成功留下自己的脚印了',

	//source/cp_class.php
	'did_not_specify_the_type_of_operation' => '没有正确指定要操作的分类',
	'enter_the_correct_class_name' => '请正确输入分类名',

	'note_wall_reply_success' => '已经回复到 \\1 的留言板',

	//source/cp_comment.php

	'operating_too_fast' => "两次发布操作太快了，请等 \\1 秒钟再试",
	'content_is_too_short' => '输入的内容不能少于4个字符',
	'comments_do_not_exist' => '指定的评论不存在',
	'do_not_accept_comments' => '该日志不接受评论',
	'sharing_does_not_exist' => '评论的分享不存在',
	'non_normal_operation' => '非正常操作',

	//source/cp_common.php
	'security_exit' => '你已经安全退出了\\1',

	//source/cp_doing.php
	'should_write_that' => '至少应该写一点东西',

	//source/cp_import.php
	'blog_import_no_result' => '"无法获取原数据，请确认已正确输入的站点URL和帐号信息，服务器返回:<br /><textarea name=\"tmp[]\" style=\"width:98%;\" rows=\"4\">\\1</textarea>"',
	'blog_import_no_data' => '获取数据失败，请参考服务器返回:<br />\\1',
	'support_function_has_not_yet_opened fsockopen' => '站点尚未开启fsockopen函数支持，还不能使用本功能',
	'integral_inadequate' => "您现在的积分 \\1 不足以完成本次操作。本操作将需要积分: \\2",
	'url_is_not_correct' => '输入的网站URL不正确',
	'choose_at_least_one_log' => '请至少选择一个要导入的日志',

	//source/cp_friend.php
	'friends_add' => '您和 \\1 成为好友了',
	'you_have_friends' => '你们已经是好友了',
	'enough_of_the_number_of_friends' => '您当前的好友数目达到系统限制，请先删除部分好友',
	'request_has_been_sent' => '好友请求已经发送，请等待对方验证中',
	'waiting_for_the_other_test' => '正在等待对方验证中',
	'you_are_friends' => "你们是好友了",
	'please_correct_choice_groups_friend' => '请正确选择分组好友',
	'specified_user_is_not_your_friend' => '指定的用户还不是您的好友',

	//source/cp_mtag.php
	'mtag_max_inputnum' => '无法加入，您在栏目 "\\1" 中的选吧数目已达到 \\2 个限制数目',
	'you_are_already_a_member' => '您已经是该选吧的成员了',
	'join_success' => '加入成功，您现在是该选吧的成员了',
	'the_discussion_topic_does_not_exist' => '对不起，参与讨论的话题不存在',
	'content_is_not_less_than_four_characters' => '对不起，内容不能少于4个字符',
	'you_are_not_a_member_of' => '您不是该选吧的成员',

	//source/cp_pm.php
	'this_message_could_not_be_deleted' => '指定的短消息不能被删除',
	'correctly_designated_recipient' => '指定的收件人还没有在本空间激活，不能发送短消息',
	'unable_to_send_air_news' => '不能发送空消息',
	'message_can_not_send' => '短消息无法进行发送',
	'not_to_their_own_greeted' => '不能向自己打招呼',
	'has_been_hailed_overlooked' => '招呼已经忽略了',

	//source/cp_profile.php
	'update_on_successful_individuals' => '个人资料更新成功了',

	//source/cp_share.php
	'blog_does_not_exist' => '指定的日志不存在',
	'logs_can_not_share' => '指定的日志因隐私设置不能够被分享',
	'album_does_not_exist' => '指定的相册不存在',
	'album_can_not_share' => '指定的相册因隐私设置不能够被分享',
	'image_does_not_exist' => '指定的图片不存在',
	'image_can_not_share' => '指定的图片因隐私设置不能够被分享',
	'topics_does_not_exist' => '指定的话题不存在',
	'mtag_does_not_exist' => '指定的选吧不存在',
	'tag_does_not_exist' => '指定的标签不存在',
	'url_incorrect_format' => '分享的网址格式不正确',
	'not_correctly_designated_to_share_information' => '没有正确指定要分享的信息',
	'description_share_input' => '请输入分享的描述',

	//source/cp_space.php
	'domain_length_error' => '设置的二级域名长度不能小于\\1个字符',
	'credits_exchange_invalid' => '兑换的积分方案有错，不能进行兑换，请返回修改。',
	'credits_transaction_amount_invalid' => '您要转账或兑换的积分数量输入有误，请返回修改。',
	'credits_password_invalid' => '您没有输入密码或密码错误，不能进行积分操作，请返回。',
	'credits_balance_insufficient' => '对不起，您的积分余额不足，兑换失败，请返回。',
	'extcredits_dataerror' => '兑换失败，请与管理员联系。',
	'domain_be_retained' => '您设定的域名被系统保留，请选择其他域名',
	'not_enabled_this_feature' => '系统还没有开启本功能',
	'space_size_inappropriate' => '请正确指定要兑换的上传空间大小',
	'space_does_not_exist' => '指定的空间不存在',
	'integral_convertible_unopened' => '系统目前没有开启积分兑换功能',
	'two_domain_have_been_occupied' => '设置的二级域名已经有人使用了',
	'only_two_names_from_english_composition_and_figures' => '设置的二级域名需要由英文字母开头且只由英文和数字构成',
	'two_domain_length_not_more_than_30_characters' => '设置的二级域名长度不能超过30个字符',
	'old_password_invalid' => '您没有输入旧密码或旧密码错误，请返回重新填写。',
	'no_change' => '没有做任何修改',
	'protection_of_users' => '受保护的用户，没有权限修改',

	//source/cp_theme.php
	'theme_does_not_exist' => '指定的风格不存在',

	//source/cp_upload.php
	'upload_images_completed' => '上传图片完成了',

	//source/cp_thread.php
	'to_login' => '请先登录再进行本操作',
	'title_not_too_little' => '标题不能少于4个字符',
	'posting_does_not_exist' => '指定的话题不存在',
	'settings_of_your_mtag' => '有了选吧才能发话题，你需要先选吧一下你的选吧。<br>选吧，就是自己选择的志同道合的交流圈。<br>在吧内，不仅可以结识与你有相同选择的朋友，更可以一起交流话题。<br><br><a href="cp.php?ac=mtag" class="submit">设置我的选吧</a>',
	'first_select_a_mtag' => '你至少应该选择一个选吧才能发话题。<br><br><a href="cp.php?ac=mtag" class="submit">设置我的选吧</a>',
	'no_mtag_allow_thread' => '当前你参与的选吧加入人数不足，还不能进行发话题操作。<br><br><a href="cp.php?ac=mtag" class="submit">设置我的选吧</a>',
	'mtag_close' => '选择的选吧已经被锁定，不能进行本操作',

	//source/space_album.php
	'to_view_the_photo_does_not_exist' => '出问题了，您要查看的相册不存在',

	//source/space_blog.php
	'view_to_info_did_not_exist' => '出问题了，您要查看的信息不存在或者已经被删除',

	//source/space_pic.php
	'view_images_do_not_exist' => '您要查看的图片不存在',

	//source/mt_thread.php
	'topic_does_not_exist' => '指定的话题不存在',

	//source/do_inputpwd.php
	'news_does_not_exist' => '指定的信息不存在',
	'proved_to_be_successful' => '验证成功，现在进入查看页面',
	'password_is_not_passed' => '输入的密码验证不通过,请返回重新确认',



	//source/do_login.php
	'login_success' => '登录成功了，现在引导您进入登录前页面 \\1',
	'not_open_registration' => '非常抱歉，本站目前暂时不开放注册',
	'not_open_registration_invite' => '非常抱歉，本站目前暂时不允许用户直接注册，需要有好友邀请链接才能注册',
	
	//source/do_lostpasswd.php
	'getpasswd_account_notmatch' => '用户名，Email 地址不匹配，请返回修改。',
	'getpasswd_send_succeed' => '取回密码的方法已经通过 Email 发送到您的信箱中，<br />请在 3 天之内修改您的密码。',
	'user_does_not_exist' => '该用户不存',
	'getpasswd_illegal' => '您所用的 ID 不存在或已经过期，无法取回密码。',
	'profile_passwd_illegal' => '密码空或包含非法字符，请返回重新填写。',
	'getpasswd_succeed' => '您的密码已重新设置，请使用新密码登录。',
	'getpasswd_account_invalid' => '对不起，创始人不能使用取回密码功能，请返回。',

	//source/do_register.php
	'registered' => '注册成功了，进入个人空间',
	'system_error' => '系统错误，未找到UCenter Client文件',
	'password_inconsistency' => '两次输入的密码不一致',
	'profile_passwd_illegal' => '密码空或包含非法字符，请重新填写。',
	'user_name_is_not_legitimate' => '用户名不合法',
	'include_not_registered_words' => '用户名包含不允许注册的词语',
	'user_name_already_exists' => '用户名已经存在',
	'email_format_is_wrong' => 'Email 格式有误',
	'email_not_registered' => 'Email 不允许注册',
	'email_has_been_registered' => 'Email 已经被注册',
	'register_error' => '注册失败',

	//tag.php
	'tag_does_not_exist' => '指定的标签不存在',

	//cp_poke.php
	'poke_success' => '已经发送，\\1下次访问时会收到通知',
	'mtag_minnum_erro' => '本选吧成员数不足 \\1 个，还不能进行本操作',
	'mtag_no_manage' => '你不是本选吧成员，不能进行本操作<br />你可以进行如下设置:<br /><a href="\\1">设置你的选吧</a>，并在 " \\2 " 选项中填写(或选择) " \\3 "',

	//source/function_common.php
	'information_contains_the_shielding_text' => '对不起，发布的信息中包含站点屏蔽的文字',
	'site_temporarily_closed' => '站点暂时关闭',
	'ip_is_not_allowed_to_visit' => '不能访问，您当前的IP不在站点允许访问的范围内。',
	'no_data_pages' => '指定的页面已经没有数据了',
	'length_is_not_within_the_scope_of' => '分页数不在允许的范围内',

	//source/function_block.php
	'page_number_is_beyond' => '页数是否超出范围',
	//source/function_cp.php
	'incorrect_code' => '输入的验证码不正确，请重新确认',

	//source/network_album.php
	'search_short_interval' => '两次搜索间隔太短，请稍后再进行搜索',
	'set_the_correct_search_content' => '对不起，请设置正确的查找内容',

	//source/space_share.php
	'share_does_not_exist' => '要查看的分享不存在',

	//source/space_tag.php
	'tag_locked' => '标签已经被锁定',

	//admin/admincp_block.php
	'enter_the_next_step' => '进入下一步操作',
	'invite_error' => '无法获取好友邀请码，请确认您是否有足够的积分来进行本操作',
	'invite_code_error' => '对不起，您访问的邀请链接不正确，请确认。',
	'invite_code_fuid' => '对不起，您访问的邀请链接已经被他人使用了。',
	
	//source/do_invite.php
	'should_not_invite_your_own' => '对不起，您不能通过访问自己的邀请链接来邀请自己。',
	'close_invite' => '对不起，系统已经关闭了好友邀请功能',
	
	'field_required' => '个人资料中的必填项目“\\1” 不能为空，请确认',
	'firend_self_error' => '对不起，您不能加自己为好友'

);

?>