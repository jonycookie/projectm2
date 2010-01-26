<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: lang_cp.php 10978 2009-01-14 02:39:06Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['cplang'] = array(

	'by' => '通过',
	'tab_space' => ' ',
	'note_blog_trace' => '踩了一脚你的日志 <a href="\\1" target="_blank">\\2</a>',
	'feed_trace' => '{actor} 踩了一脚 {username} 的日志 {blog}',
	'feed_comment_space' => '{actor} 在 {touser} 的留言板留了言',
	'feed_comment_image' => '{actor} 评论了 {touser} 的图片',
	'feed_comment_blog' => '{actor} 评论了 {touser} 的日志 {blog}',
	'feed_comment_share' => '{actor} 对 {touser} 分享的 {share} 发表了评论',
	'share' => '分享',
	'share_action' => '分享了',
	'note_wall' => '在留言板上给你<a href="\\1" target="_blank">留言</a>',
	'note_wall_reply' => '回复了你的<a href="\\1" target="_blank">留言</a>',
	'note_pic_comment' => '评论了你的<a href="\\1" target="_blank">图片</a>',
	'note_pic_comment_reply' => '回复了你的<a href="\\1" target="_blank">图片评论</a>',
	'note_blog_comment' => '评论了你的日志 <a href="\\1" target="_blank">\\2</a>',
	'note_blog_comment_reply' => '回复了你的<a href="\\1" target="_blank">日志评论</a>',
	'note_share_comment' => '评论了你的 <a href="\\1" target="_blank">分享</a>',
	'note_share_comment_reply' => '回复了你的<a href="\\1" target="_blank">分享评论</a>',
	'wall_pm_subject' => '您好，我给您留言了',
	'wall_pm_message' => '我在您的留言板给你留言了，[url=\\1]点击这里去留言板看看吧[/url]',
	'note_showcredit' => '赠送给您 \\1 个竞价积分，帮助提升在<a href="network.php?ac=space&view=show" target="_blank">竞价排行榜</a>中的名次',
	'feed_showcredit' => '{actor} 赠送给 {fusername} 竞价积分 {credit} 个，帮助好友提升在<a href="network.php?ac=space&view=show" target="_blank">竞价排行榜</a>中的名次',
	'feed_showcredit_self' => '{actor} 增加竞价积分 {credit} 个，提升自己在<a href="network.php?ac=space&view=show" target="_blank">竞价排行榜</a>中的名次',
	'feed_doing_title' => '{actor}：{message}',
	'note_doing_reply' => '在<a href="\\1" target="_blank">记录</a>中对你进行了回复',
	'feed_friend_title' => '{actor} 和 {touser} 成为了好友',
	'note_friend_add' => '和你成为了好友',
	
	
	
	'friend_subject' => '<a href="\\2" target="_blank">\\1 请求加你为好友</a>',
	'comment_friend' =>'<a href="\\2" target="_blank">\\1 给你留言了</a>',
	'photo_comment' => '<a href="\\2" target="_blank">\\1 评论了你的照片</a>',
	'blog_comment' => '<a href="\\2" target="_blank">\\1 评论了你的日志</a>',
	'share_comment' => '<a href="\\2" target="_blank">\\1 评论了你的分享</a>',
	'friend_pm' => '<a href="\\2" target="_blank">\\1 给你发私信了</a>',
	'poke_subject' => '<a href="\\2" target="_blank">\\1 向你打招呼</a>',
	'mtag_reply' => '<a href="\\2" target="_blank">\\1 回复了你的话题</a>',

	'friend_pm_reply' => '\\1 回复了你的私信',
	'comment_friend_reply' => '\\1 回复了你的留言',
	'blog_comment_reply' => '\\1 回复了你的日志评论',
	'photo_comment_reply' => '\\1 回复了你的照片评论',
	'share_comment_reply' => '\\1 回复了你的分享评论',
	
	'invite_subject' => '\\1邀请您加入\\2，并成为TA的好友',
	'invite_massage' => '<table border="0">
		<tr>
		<td valign="top">\\1</td>
		<td valign="top">
		<h3>Hi，我是\\2，在\\3上建立了个人主页，邀请您也加入并成为我的好友</h3><br>
		请加入到我的好友中，你就可以通过我的个人主页了解我的近况，分享我的照片，随时与我保持联系<br>
		<br>
		邀请附言：<br>
		\\4
		<br><br>
		<strong>请你点击以下链接，接受好友邀请：</strong><br>
		<a href="\\5">\\5</a><br>
		<br>
		<strong>如果你拥有\\3上面的账号，请点击以下链接查看我的个人主页：</strong><br>
		<a href="\\6">\\6</a><br>
		</td></tr></table>',
	
	'app_invite_subject' => '\\1邀请您加入\\2，一起来玩\\3',
	'app_invite_massage' => '<table border="0">
		<tr>
		<td valign="top">\\1</td>
		<td valign="top">
		<h3>Hi，我是\\2，在\\3上玩 \\7，邀请您也加入一起玩</h3><br>
		<br>
		邀请附言：<br>
		\\4
		<br><br>
		<strong>请你点击以下链接，接受好友邀请一起玩\\7：</strong><br>
		<a href="\\5">\\5</a><br>
		<br>
		<strong>如果你拥有\\3上面的账号，请点击以下链接查看我的个人主页：</strong><br>
		<a href="\\6">\\6</a><br>
		</td></tr></table>',
	
	'feed_mtag_add' => '{actor} 创建了新群组 {mtags}',
	'note_members_grade_-9' => '将你从群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 请出',
	'note_members_grade_-2' => '将你在群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的成员身份修改为 待审核',
	'note_members_grade_-1' => '将你在群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 中禁言',
	'note_members_grade_0' => '将你在群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的成员身份修改为 普通成员',
	'note_members_grade_1' => '将你设为了群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的明星成员',
	'note_members_grade_8' => '将你设为了群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的副群主',
	'note_members_grade_9' => '将你设为了群组 <a href="space.php?do=mtag&tagid=\\1" target="_blank">\\2</a> 的群主',
	'feed_mtag_join' => '{actor} 加入了群组 {mtag} ({field})',
	'mtag_joinperm_2' => '需邀请才可加入',
	'feed_mtag_join_invite' => '{actor} 接受 {fromusername} 的邀请，加入了群组 {mtag} ({field})',
	'person' => '人',
	'delete' => '删除',
	
	'active_email_subject' => '您的邮箱激活邮件',
	'active_email_msg' => '请复制下面的激活链接到浏览器进行访问，以便激活你的邮箱。<br>邮箱激活链接:<br><a href="\\1" target="_blank">\\1</a>',
	'share_space' => '分享了一个用户',
	'note_share_space' => '分享了你的空间',
	'share_blog' => '分享了一篇日志',
	'note_share_blog' => '分享了你的日志 <a href="\\1" target="_blank">\\2</a>',
	'share_album' => '分享了一个相册',
	'note_share_album' => '分享了你的相册 <a href="\\1" target="_blank">\\2</a>',
	'default_albumname' => '默认相册',
	'share_image' => '分享了一张图片',
	'album' => '相册',
	'note_share_pic' => '分享了你的相册 \\2 中的<a href="\\1" target="_blank">图片</a>',
	'share_thread' => '分享了一个话题',
	'mtag' => '群组',
	'note_share_thread' => '分享了你的话题 <a href="\\1" target="_blank">\\2</a>',
	'share_mtag' => '分享了一个群组',
	'share_mtag_membernum' => '现有 {membernum} 名成员',
	'share_tag' => '分享了一个标签',
	'share_tag_blognum' => '现有 {blognum} 篇日志',
	'share_link' => '分享了一个网址',
	'share_video' => '分享了一个视频',
	'share_music' => '分享了一个音乐',
	'share_flash' => '分享了一个 Flash',
	'feed_task' => '{actor} 参与了有奖活动 {task}',
	'feed_task_credit' => '{actor} 参与了有奖活动 {task}，领取了 {credit} 个奖励积分',
	'the_default_style' => '默认风格',
	'the_diy_style' => '自定义风格',
	'feed_thread' => '{actor} 发起了新话题',
	
	'feed_thread_reply' => '{actor} 回复了 {touser} 的话题 {thread}',
	'note_thread_reply' => '回复了你的话题',
	'note_post_reply' => '在话题 <a href=\\"\\1\\" target="_blank">\\2</a> 中回复了你的<a href=\\"\\3\\" target="_blank">回帖</a>',
	'thread_edit_trail' => '<ins class="modify">[本话题由 \\1 于 \\2 编辑]</ins>',
	'create_a_new_album' => '创建了新相册',
	'not_allow_upload' => '您现在没有权限上传图片',
	'get_passwd_subject' => '取回密码邮件',
	'get_passwd_message' => '您只需在提交请求后的三天之内，通过点击下面的链接重置您的密码：<br />\\1<br />(如果上面不是链接形式，请将地址手工粘贴到浏览器地址栏再访问)<br />上面的页面打开后，输入新的密码后提交，之后您即可使用新的密码登录了。',
	'file_is_too_big' => '文件过大',
	'feed_blog_password' => '{actor} 发表了新加密日志 {subject}',
	'feed_blog' => '{actor} 发表了新日志',
	'lack_of_access_to_upload_file_size' => '无法获取上传文件大小',
	'only_allows_upload_file_types' => '只允许上传jpg、gif、png标准格式的图片',
	'unable_to_create_upload_directory_server' => '服务器无法创建上传目录',
	'inadequate_capacity_space' => '空间容量不足，不能上传新附件',
	'mobile_picture_temporary_failure' => '无法转移临时图片到服务器指定目录',
	'ftp_upload_file_size' => '远程上传图片失败',
	'comment' => '评论',
	'upload_a_new_picture' => '上传了新图片',
	'the_total_picture' => '共 \\1 张图片',
	'feed_invite' => '{actor} 发起邀请，和 {username} 成为了好友',
	'note_invite' => '接受了您的好友邀请',
	'space_open_subject' => '快来打理一下您的个人主页吧',
	'space_open_message' => 'hi，我今天去拜访了一下您的个人主页，发现您自己还没有打理过呢。赶快来看看吧。地址是：\\1space.php',
	'feed_space_open' => '{actor} 开通了自己的个人主页',
	'feed_profile_update' => '{actor} 更新了自己的个人资料',
	'apply_mtag_manager' => '想申请成为 <a href="\\1" target="_blank">\\2</a> 的群主，理由如下:\\3。<a href="\\1" target="_blank">(点击这里进入管理)</a>',
	'feed_add_attachsize' => '{actor} 用 {credit} 个积分兑换了 {size} 附件空间，可以上传更多的图片啦(<a href="cp.php?ac=credit&op=addsize">我也来兑换</a>)'
	
);

?>