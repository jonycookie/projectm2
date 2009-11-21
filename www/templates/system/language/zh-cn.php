<?php
//Language Name: 简体中文
//Language Flie: zh-cn.php
//Language URI: http://www.iDreamSoft.cn
//Language Description:  iCMS 简体中文言语包
//Language Version: 3.1
//Author: 枯木
//Author URI: http://G.iDreamSoft.cn
$language=array(
	//错误提示
	'error'=>array(
		'notpl'=>'找不到相关模板',
		'page'=>'找不到该页！',
		'seccode'=>'验证码错误！',
		'unknown'=>'未知错误！请联系管理员.',
		'model.exit'=>'自定义模型不存在！',
		'word.disable'=>'包含被系统屏蔽的字符，请返回重新填写。',
		'email'=>'E-mail格式错误!'
	),
	//翻页相关
	'page'=>array(
		'index'=>'首页',
		'prev'=>'上一页',
		'next'=>'下一页',
		'last'=>'末页',
		'other'=>'共',
		'unit'=>'页',
		'list'=>'篇文章',
		'sql'=>'条记录',
		'tag'=>'个标签',
		'comment'=>'条评论',
		'message'=>'条留言'
	),
	//内容页上/下篇
	'show'=>array(
		'first'=>'已经是第一篇',
		'last'=>'已经是最后一篇'
	),
	//内容页上/下篇
	'content'=>array(
		'first'=>'已经是第一条记录',
		'last'=>'已经是最后一条记录'
	),
	//评论/留言回复
	'reply'=>array(
		'admin'=>'管理员回复：',
		'author'=>'文章发布者回复：'
	),
	//模板标签
	'empty'=>array(
		'TPL_siteName'=>'iCMS:site 标签中name不能为空',
		'TPL_advName'=>'iCMS:advertise 标签中name不能为空',
		'sql'=>'SQL语句不能为空',
	),
	//留言提示
	'message'=>array(
		'empty'=>'您还是说点什么吧~!憋着对身体不好!',
		'finish'=>'谢谢您的留言!'
	),
	//注册提示
	'register'=>array(
		'usernameusr'=>'该用户名已被使用.请另选择一个!',
		'usernameShort'=>'用户名长度小于3',
		'usernameLong'=>'用户名长度大于12',
		'emailerror'=>'Email格式出错',
		'different'=>'密码与确认密码不一致!',
		'nicknamelong'=>'昵称长度大于12',
		'finish'=>'注册成功'
	),
	//搜索
	'search'=>array(
		'keywordempty'=>'你要找什么?',
		'typempty'=>'请选择搜索类型'
	),
	//标签
	'tag'=>array(
		'empty'=>'空标签!'
	),
	//Digg	
	'digged'=>'这篇文章您已经顶过了！',
	
	//评论提示	
	'comment'=>array(
		'emptyusername'=>'用户名不能为空!',
		'emptypassword'=>'密码不能为空!',
		'error'=>'用户名/密码错误!',
		'empty'=>'评论内容不能为空!',
		'examine'=>'您的评论已提交,请等待管理的审核!',
		'post'=>'您的评论已提交!',
		'Unknown'=>'发生未知错误.请联系管理员!',
	),
	'filter'=>array(
		'content'=>'内容包含被系统屏蔽的字符，请返回重新填写。',
		'username'=>'用户名包含被系统屏蔽的字符，请返回重新填写。',
		'title'=>'标题包含被系统屏蔽的字符，请返回重新填写。',
	),
	//投稿	
	'post'=>array(
		'verifycode'=>'验证码错误！',
		'checktitlempty'=>'标题不能为空！',
		'checkcid'=>'请选择所属栏目',
		'checkbody'=>'文章内容不能为空！',
		'checktitle'=>'该标题的文章已经存在!请检查是否重复!',
		'examine'=>'您的文章已提交,请等待管理员审核!',
		'finish'=>'您的文章已提交!',
	),
	//其它
	'guest'=>'游客',
	//导航
	'navTag'=>' &gt; '
);
?>