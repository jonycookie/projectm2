<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: lang_source.php 7267 2008-05-04 10:02:31Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['sourcelang'] = array(

	'by' => '通过',
	'album' => '相册',
	'mtag' => '选吧',
	'share' => '分享',
	'delete' => '删除',
	'add' => '添加',
	'person' => '人',
	'colon' => '：',
	'tab_space' => ' ',
	'comment' => '评论',
	
	'friend_group_default' => '默认分组',
	'friend_group' => '好友分组',
	
	'feed_comment_space' => '{actor} 在 {touser} 的留言板留了言',
	'feed_comment_image' => '{actor} 评论了 {touser} 的图片',
	'feed_comment_blog' => '{actor} 评论了 {touser} 的日志 {blog}',
	'feed_comment_share' => '{actor} 评论了 {touser} 的{share}',
	'feed_invite' => '{actor} 发起邀请，跟 {username} 成为了好友',
	
	'note_wall' => '在留言板上给你<a href="\\1" target="_blank">留言</a>',
	'note_wall_reply' => '回复了你的<a href="\\1" target="_blank">留言</a>',
	'note_wall_reply_success' => '已经回复到 \\1 的留言板',
	'note_pic_comment' => '评论了你的<a href="\\1" target="_blank">图片</a>',
	'note_pic_comment_reply' => '回复了你的<a href="\\1" target="_blank">图片评论</a>',
	'note_blog_comment' => '评论了你的日志 <a href="\\1" target="_blank">\\2</a>',
	'note_blog_comment_reply' => '回复了你的<a href="\\1" target="_blank">日志评论</a>',
	'note_share_comment' => '评论了你的 <a href="\\1" target="_blank">分享</a>',
	'note_share_comment_reply' => '回复了你的<a href="\\1" target="_blank">分享评论</a>',
	'note_share_space' => '分享了你的空间',
	'note_share_blog' => '分享了你的日志 <a href="\\1" target="_blank">\\2</a>',
	'note_share_album' => '分享了你的相册 <a href="\\1" target="_blank">\\2</a>',
	'note_share_pic' => '分享了你的相册 \\2 中的<a href="\\1" target="_blank">图片</a>',
	'note_share_thread' => '分享了你的话题 <a href="\\1" target="_blank">\\2</a>',
	
	'note_doing_reply' => '针对你的<a href="\\1" target="_blank">迷你博客</a>进行了<a href="\\2" target="_blank">评论</a>',
	
	'feed_friend_title' => '{actor} 跟 {touser} 成为了好友',
	'note_friend_add' => '跟你成为了好友',
	'note_invite' => '接受了您的好友邀请',
	
	'friend_invite_subject' => '\\1在\\2邀请你为好友',
	'friend_invite_message' => '\\1 在 \\2 站点邀请你为好友。<br />请复制下面的链接到浏览器窗口查看，成为 \\1 的好友：<br />\\3do.php?ac=invite&code=\\4<br /><br />\\2 是属于你自己的空间。<br />在这里，你可以一句话记录生活中的点点滴滴，方便快捷地发布信息和图片，与志同道合的朋友们一起交流与分享。<br />(这是站内自动发送的系统消息，你不需要回复)<br />\\3<br />',
	
	'feed_mtag_join' => '{actor} 加入了选吧 {mtag} ({field})',
	
	'share_space' => '分享了一个用户',
	'share_blog' => '分享了一篇日志',
	'share_album' => '分享了一个相册',
	'share_image' => '分享了一张图片',
	'share_thread' => '分享了一个话题',
	'share_mtag' => '分享了一个选吧',
	'share_mtag_membernum' => '现有 {membernum} 名成员',
	'share_tag' => '分享了一个标签',
	'share_tag_blognum' => '现有 {blognum} 篇日志',
	'share_link' => '分享了一个网址',
	
	'feed_thread' => '<b>{actor} 发起了新话题</b>',
	'feed_thread_reply' => '{actor} 回复了 {touser} 的话题 {thread}',
	'note_thread_reply' => '回复了你的话题',
	'note_post_reply' => '在话题 <a href=\"\\1\" target="_blank">\\2</a> 中回复了你的<a href=\"\\3\" target="_blank">回帖</a>',
	
	'feed_blog' => '<b>{actor} 发表了新日志</b>',
	'feed_blog_password' => '{actor} 发表了新加密日志 {subject}',
	
	'not_allow_upload' => '您现在没有权限上传图片',
	'the_default_style' => '默认风格',
	'the_diy_style' => '自定义风格',
	'lack_of_access_to_upload_file_size' => '无法获取上传文件大小',
	'only_allows_upload_file_types' => '只允许上传jpg、gif、png的图片',
	'unable_to_create_upload_directory_server' => '服务器无法创建上传目录',
	'inadequate_capacity_space' => '空间容量不足，不能上传新附件',
	'mobile_picture_temporary_failure' => '无法转移临时图片到服务器指定目录',
	'create_a_new_album' => '创建了新相册',
	'upload_a_new_picture' => '上传了新图片',
	'file_is_too_big' => '文件过大',
	'the_total_picture' => '共 \\1 张图片',
	'network_space_user' => '用户',
	'casual_look' => '随便看看',
	'get_passwd_subject' => '取回密码邮件',
	'get_passwd_message' => '您只需在提交请求后的三天之内，通过点击下面的链接重置您的密码：<br />\\1<br />(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)<br />上面的页面打开后，输入新的密码后提交，之后您即可使用新的密码登录了。',
	
	'wall_pm_subject' => '您好，我给您留言了',
	'wall_pm_message' => '我在您的留言板给你留言了，[url=\\1]点击这里去留言板看看吧[/url]',
	'password_message' => '详细内容已经加密'
);

?>