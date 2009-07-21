<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('template/default/cp_credit|template/default/header|template/default/cp_header|template/default/footer', '1248083522', 'template/default/cp_credit');?><?php if(empty($_SGLOBAL['inajax'])) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$_SC['charset']?>" />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<title><?php if($_TPL['titles']) { ?><?php if(is_array($_TPL['titles'])) { foreach($_TPL['titles'] as $value) { ?><?php if($value) { ?><?=$value?> - <?php } ?><?php } } ?><?php } ?><?php if($space) { ?><?=$_SN[$space['uid']]?> - <?php } ?><?=$_SCONFIG['sitename']?> - Powered by UCenter Home</title>
<link rel="edituri" type="application/rsd+xml" title="rsd" href="xmlrpc.php?rsd=<?=$space['uid']?>" />
<link type="text/css" href="template/default/style.css" rel="stylesheet" />
<script type="text/javascript" src="script/cookie.js"></script>
<script type="text/javascript" src="script/common.js"></script>
<script type="text/javascript" src="script/menu.js"></script>
<script type="text/javascript" src="script/ajax.js"></script>
<script type="text/javascript" src="script/face.js"></script>
<script type="text/javascript" src="script/manage.js"></script>
<?php if(!empty($_SGLOBAL['space_css'])) { ?>
<?=$_SGLOBAL['space_css']?>
<?php } ?>
</style>
</head>
<body>
<div id="append_parent"></div>
<div id="ajaxwaitid"></div>

<div id="header">
<?php if($_SGLOBAL['ad']['header']) { ?><div id="ad_header"><?php adshow('header'); ?></div><?php } ?>
<div class="headerwarp">
<h1 class="logo"><a href="index.php"><img src="<?=$_SCONFIG['sitelogo']?>" alt="<?=$_SCONFIG['sitename']?>" /></a></h1>
<ul class="menu">
<?php if($_SGLOBAL['supe_uid']) { ?>
<li><a href="space.php?do=home">首页</a></li>
<li><a href="space.php">个人主页</a></li>
<li><a href="space.php?do=friend">好友</a></li>
<?php } else { ?>
<li><a href="index.php">首页</a></li>
<?php } ?>

<li><a href="network.php">随便看看</a></li>

<?php if($_SGLOBAL['appmenu']) { ?>
<?php if($_SGLOBAL['appmenus']) { ?>
<li class="dropmenu" id="ucappmenu" onmouseover="showMenu(this.id)">
<a href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>" target="_blank"><?=$_SGLOBAL['appmenu']['name']?></a>
</li>
<?php } else { ?>
<li><a href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>" target="_blank"><?=$_SGLOBAL['appmenu']['name']?></a></li>
<?php } ?>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>
<li><a href="space.php?do=pm<?php if(!empty($_SGLOBAL['member']['newpm'])) { ?>&filter=newpm<?php } ?>">消息<?php if(!empty($_SGLOBAL['member']['newpm'])) { ?>(新)<?php } ?></a></li>
<?php if(!empty($_SGLOBAL['member']['notenum'])) { ?><li class="notify"><a href="space.php?do=notice"><?=$_SGLOBAL['member']['notenum']?>条新通知</a></li><?php } ?>
<?php } else { ?>
<li><a href="help.php">帮助</a></li>
<?php } ?>
</ul>

<div class="nav_account">
<?php if($_SGLOBAL['supe_uid']) { ?>
<a href="space.php?uid=<?=$_SGLOBAL['supe_uid']?>" class="login_thumb"><img src="<?php echo avatar($_SGLOBAL[supe_uid],small); ?>" alt="<?=$_SN[$_SGLOBAL['supe_uid']]?>" width="20" height="20" /></a>
<a href="space.php?uid=<?=$_SGLOBAL['supe_uid']?>" class="loginName"><?=$_SN[$_SGLOBAL['supe_uid']]?></a>
<?php if($_SN[$_SGLOBAL['supe_uid']]!=$_SGLOBAL['username']) { ?>(<?=$_SGLOBAL['supe_username']?>)<?php } ?>
<br />
<?php if(empty($_SCONFIG['closeinvite'])) { ?><a href="cp.php?ac=invite">邀请</a> | <?php } ?><a href="cp.php">设置</a> | <a href="cp.php?ac=privacy">隐私</a> | <a href="cp.php?ac=common&op=logout">退出</a>
<?php } else { ?>
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>" class="login_thumb"><img src="<?php echo avatar($_SGLOBAL[supe_uid],small); ?>" width="20" height="20" /></a>
欢迎您<br>
<a href="do.php?ac=<?=$_SCONFIG['login_action']?>">登录</a> | 
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>">注册</a>
<?php } ?>
</div>
</div>
</div>

<div id="wrap">

<?php if(empty($_TPL['nosidebar'])) { ?>
<div id="main">


<div id="app_sidebar" style="display:none;">
<?php if($_SGLOBAL['supe_uid']) { ?>
<ul class="app_list" id="default_userapp">
<li><img src="image/app/doing.gif"><a href="space.php?do=doing">记录</a></li>
<li><img src="image/app/album.gif"><a href="space.php?do=album">相册</a><em><a href="cp.php?ac=upload">上传</a></em></li>
<li><img src="image/app/blog.gif"><a href="space.php?do=blog">日志</a><em><a href="cp.php?ac=blog">发表</a></em></li>
<li><img src="image/app/mtag.gif"><a href="space.php?do=thread">群组</a><em><a href="cp.php?ac=thread">话题</a></em></li>
<li><img src="image/app/share.gif"><a href="space.php?do=share">分享</a></li>

<?php if($_SCONFIG['my_status']) { ?>
<?php if(is_array($_SGLOBAL['userapp'])) { foreach($_SGLOBAL['userapp'] as $value) { ?>
<li><img src="http://appicon.manyou.com/icons/<?=$value['appid']?>"><a href="userapp.php?id=<?=$value['appid']?>"><?=$value['appname']?></a></li>
<?php } } ?>
<?php } ?>
</ul>

<?php if($_SCONFIG['my_status']) { ?>
<ul class="app_list" id="my_userapp">
<?php if(is_array($_SGLOBAL['my_menu'])) { foreach($_SGLOBAL['my_menu'] as $value) { ?>
<li id="userapp_li_<?=$value['appid']?>"><img src="http://appicon.manyou.com/icons/<?=$value['appid']?>"><a href="userapp.php?id=<?=$value['appid']?>" title="<?=$value['appname']?>"><?=$value['appname']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['my_menu_more']) { ?>
<p class="app_more"><a href="javascript:;" id="a_app_more" onclick="userapp_open();" class="off">展开</a></p>
<?php } ?>

<?php if($_SCONFIG['my_status']) { ?>
<div class="app_m">
<ul>
<li><img src="image/app_add.gif"><a href="cp.php?ac=userapp&my_suffix=%2Fapp%2Flist" class="addApp">添加应用</a></li>
<li><img src="image/app_set.gif"><a href="cp.php?ac=userapp&op=menu" class="myApp">管理应用</a></li>
</ul>
</div>
<?php } ?>

<?php } else { ?>
<div class="bar_text">
<form id="loginform" name="loginform" action="do.php?ac=<?=$_SCONFIG['login_action']?>&ref" method="post">
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
<p class="title">登录站点</p>
<p>用户名</p>
<p><input type="text" name="username" id="username" class="t_input" size="15" value="" /></p>
<p>密码</p>
<p><input type="password" name="password" id="password" class="t_input" size="15" value="" /></p>
<p><input type="checkbox" id="cookietime" name="cookietime" value="315360000" checked /><label for="cookietime">记住我</label></p>
<p>
<input type="submit" id="loginsubmit" name="loginsubmit" value="登录" class="submit"  />
<a href="do.php?ac=<?=$_SCONFIG['register_action']?>" class="button">注册</a>
</p>
</form>
</div>
<?php } ?>
</div>


<div id="mainarea">

<?php if($_SGLOBAL['ad']['contenttop']) { ?><div id="ad_contenttop"><?php adshow('contenttop'); ?></div><?php } ?>
<?php } ?>

<?php } ?>

<h2 class="title"><img src="image/icon/profile.gif">个人设置</h2>
<div class="tabs_header">
<ul class="tabs">
<li<?=$actives['avatar']?>><a href="cp.php?ac=avatar"><span>我的头像</span></a></li>
<li<?=$actives['profile']?>><a href="cp.php?ac=profile"><span>个人资料</span></a></li>
<li<?=$actives['theme']?>><a href="cp.php?ac=theme"><span>主页风格</span></a></li>
<?php if($_SCONFIG['allowdomain'] && $_SCONFIG['domainroot'] && checkperm('domainlength')) { ?>
<li<?=$actives['domain']?>><a href="cp.php?ac=domain"><span>我的域名</span></a></li>
<?php } ?>
<?php if($_SCONFIG['sendmailday']) { ?>
<li<?=$actives['sendmail']?>><a href="cp.php?ac=sendmail"><span>邮件提醒</span></a></li>
<?php } ?>
<li<?=$actives['password']?>><a href="cp.php?ac=password"><span>账号设置</span></a></li>
<li<?=$actives['credit']?>><a href="cp.php?ac=credit"><span>积分</span></a></li>
<li<?=$actives['advance']?>><a href="cp.php?ac=advance"><span>高级管理</span></a></li>
</ul>
</div>

<div id="content" class="c_form">
<?php if(empty($_GET['op'])) { ?>	

<div class="c_mgs">
<table cellspacing="0" cellpadding="0" class="formtable">
<caption>
<h2>我的个人概括</h2>
</caption>
<tr><th width="30%">目前您的积分数</th><td><span style="color:red;font-size:20px;font-weight:bold;"><?=$space['credit']?></span> <?php echo getstar($space[credit]); ?> (<a href="network.php?ac=space&view=credit">查看排名</a>)</td></tr>
<tr><th width="30%">积分图标说明</th><td style="padding:0 0 10px 0;line-height:180%;">
积分每满 <strong><?=$_SCONFIG['starcredit']?></strong> 个，就会拥有一个初级图标 <img src="image/star_level1.gif" align="absmiddle"><br>
每满 <strong><?=$_SCONFIG['starlevelnum']?></strong> 个当前图标就升级为 <strong>1</strong> 个上级图标<br>
图标等级由高到低为：<?php for($i=1;$i<11;$i++){ ?><img src="image/star_level<?=$i?>.gif"><?php } ?></td></tr>
<tr><th>所在的用户组</th><td><span<?php g_color($space[groupid]); ?>><?=$space['grouptitle']?></span><?php g_icon($space[groupid]); ?></td></tr>
<tr><th>访问量</th><td><?=$space['viewnum']?> (<a href="network.php?ac=space&view=viewnum">查看排名</a>)</td></tr>
<tr><th>创建时间</th><td><?php echo sgmdate('Y-m-d',$space[dateline],1); ?></td></tr>
<tr><th>上次登录</th><td><?php echo sgmdate('Y-m-d',$space[lastlogin],1); ?></td></tr>
<tr><th>最后更新</th><td><?php echo sgmdate('Y-m-d',$space[updatetime],1); ?></td></tr>

<tr>
<th>空间容量</th>
<td> 最大空间 <?=$maxattachsize?>, 已用 <?=$space['attachsize']?> (<?=$percent?>%)</td>
</tr>
<?php if($space['haveattachsize']) { ?><tr><th>剩余空间</th><td><?=$space['haveattachsize']?></td></tr><?php } ?>
</table>
</div>

<table cellspacing="0" cellpadding="0" class="formtable">
<caption>
<h2>积分增加的规则</h2>
<p>以下操作会使你的积分增加</p>
</caption>
<tr><th width="30%">发布日志</th><td>+ <?=$get['blog']?></td></tr>
<tr><th>上传图片</th><td>+ <?=$get['pic']?></td></tr>
<tr><th>发布评论/留言</th><td>+ <?=$get['comment']?></td></tr>
<tr><th>发起话题</th><td>+ <?=$get['thread']?></td></tr>
<tr><th>发布回帖</th><td>+ <?=$get['post']?></td></tr>
<tr><th>邀请好友注册成功</th><td>+ <?=$get['invite']?></td></tr>
<tr><th>参与有奖活动</th><td>+ 增加活动奖励积分</td></tr>
</table>

<table cellspacing="0" cellpadding="0" class="formtable">
<caption>
<h2>积分减少的规则</h2>
<p>以下操作会扣减你的积分</p>
</caption>
<tr><th width="30%">日志被删除</th><td>- <?=$pay['blog']?></td></tr>
<tr><th>图片被删除</th><td>- <?=$pay['pic']?></td></tr>
<tr><th>评论/留言被删除</th><td>- <?=$pay['comment']?></td></tr>
<tr><th>话题被删除</th><td>- <?=$pay['thread']?></td></tr>
<tr><th>回帖被删除</th><td>- <?=$pay['post']?></td></tr>
<tr><th>获取注册邀请码</th><td>- <?=$pay['invite']?></td></tr>
<tr><th>搜索一次</th><td>- <?=$pay['search']?></td></tr>
<tr><th>增加1M的上传空间</th><td>- <?=$pay['attach']?></td></tr>
<tr><th>日志导入</th><td>- <?=$pay['xmlrpc']?></td></tr>
</table>

<table cellspacing="0" cellpadding="0" class="formtable">
<caption>
<h2>用户组与积分对应关系</h2>
<p>用户组越高，所拥有的权限会越多</p>
</caption>
<tr><th width="30%">用户组名</th><td>积分范围</td></tr>
<?php if(is_array($groups)) { foreach($groups as $value) { ?>
<tr><th><?=$value['grouptitle']?></th><td><?=$value['creditlower']?> ~ <?=$value['credithigher']?></td></tr>
<?php } } ?>
</table>

<?php } elseif($_GET['op'] == 'exchange') { ?>

<form method="post" action="cp.php?ac=credit&op=exchange">
<table cellspacing="0" cellpadding="0" class="formtable">
<caption>
<h2>积分兑换</h2>
<p>您可以将自己的积分兑换到本站其他的应用（比如论坛）里面。</p>
</caption>
<tr><th width="30%">目前您的积分数</th><td> <?=$space['credit']?></td></tr>
<tr>
<th><label for="password">密码</label></th>
<td><input type="password" name="password" class="t_input" /></td>
</tr>
<tr>
<th>支出积分</th>
<td><input type="text" id="amount" name="amount" value="0" class="t_input" onkeyup="calcredit();" /></td>
</tr>
<tr>
<th>兑换成</th>
<td>
<input type="text" id="desamount" value="0" class="t_input" disabled />&nbsp;&nbsp;
<select name="tocredits" id="tocredits" onChange="calcredit();">
<?php if(is_array($_CACHE['creditsettings'])) { foreach($_CACHE['creditsettings'] as $id => $ecredits) { ?>
<?php if($ecredits['ratio']) { ?>
<option value="<?=$id?>" unit="<?=$ecredits['unit']?>" title="<?=$ecredits['title']?>" ratio="<?=$ecredits['ratio']?>"><?=$ecredits['title']?></option>
<?php } ?>
<?php } } ?>
</select>
</td>
</tr>
<tr>
<th>兑换比率</th>
<td><span class="bold">1</span>&nbsp;<span id="orgcreditunit">积分</span><span id="orgcredittitle"></span>&nbsp;兑换&nbsp;<span class="bold" id="descreditamount"></span>&nbsp;<span id="descreditunit"></span><span id="descredittitle"></span></td>
</tr>
<tr><th>&nbsp;</th><td><input type="submit" name="exchangesubmit" value="兑换积分" class="submit"></td></tr>
</table>
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
</form>
<script type="text/javascript">
function calcredit() {
tocredit = $('tocredits')[$('tocredits').selectedIndex];
$('descreditunit').innerHTML = tocredit.getAttribute('unit');
$('descredittitle').innerHTML = tocredit.getAttribute('title');
$('descreditamount').innerHTML = Math.round(1/tocredit.getAttribute('ratio') * 100) / 100;
$('amount').value = $('amount').value.toInt();
if($('amount').value != 0) {
$('desamount').value = Math.floor(1/tocredit.getAttribute('ratio') * $('amount').value);
} else {
$('desamount').value = $('amount').value;
}
}
String.prototype.toInt = function() {
var s = parseInt(this);
return isNaN(s) ? 0 : s;
}
calcredit();
</script>
<?php } elseif($_GET['op'] == 'addsize') { ?>

<form method="post" action="cp.php?ac=credit&op=addsize">
<table cellspacing="0" cellpadding="0" class="formtable">
<caption>
<h2>兑换容量</h2>
<p>可以使用自己的积分来兑换附件容量，上传更多的图片。</p>
</caption>
<tr><th width="30%">附件空间使用</th><td> 已用 <?=$space['attachsize']?> / <?=$maxattachsize?> ，使用比例 <?=$sizewidth?>%
<table cellspacing="0" cellpadding="0" width="100%"><tr><?php if($sizewidth) { ?><td width="<?=$sizewidth?>%" bgcolor="red">&nbsp;</td><?php } ?><td bgcolor="green">&nbsp;</td></tr></table>
</td></tr>
<tr><th>拥有的积分</th><td><?=$space['credit']?></td></tr>
<tr><th>兑换规则</th><td>兑换1M的上传空间，需要积分数: <?=$pay['attach']?></td></tr>
<tr><th>要兑换的空间大小</th><td><input type="text" name="addsize" value="1" size="5"> M</td></tr>
<tr><th>&nbsp;</th><td><input type="submit" name="addsizesubmit" value="兑换" class="submit"></td></tr>
</table>
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
</form>

<?php } ?>

</div>
<div id="sidebar" style="width:150px;">
<div class="cat">
<h3>积分操作</h3>
<ul class="post_list line_list">
<li<?php if(!$_GET['op']) { ?> class="current"<?php } ?>><a href="cp.php?ac=credit">我的积分</a></li>
<li<?php if($_GET['op']=='addsize') { ?> class="current"<?php } ?>><a href="cp.php?ac=credit&op=addsize">兑换上传容量</a></li>
<li<?php if($_GET['op']=='exchange') { ?> class="current"<?php } ?>><a href="cp.php?ac=credit&op=exchange">积分兑换</a></li>
<li><a href="network.php?ac=space&view=credit">积分排行榜</a></li>
</ul>
</div>
</div>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
<?php if(empty($_TPL['nosidebar'])) { ?>
<?php if($_SGLOBAL['ad']['contentbottom']) { ?><br style="line-height:0;clear:both;"/><div id="ad_contentbottom"><?php adshow('contentbottom'); ?></div><?php } ?>
</div>

<!--/mainarea-->
</div>
<!--/main-->
<?php } ?>

<div id="footer" title="<?php echo debuginfo(); ?>">
<?php if($_TPL['templates']) { ?>
<div class="chostlp" title="切换风格"><img id="chostlp" src="<?=$_TPL['default_template']['icon']?>" onmouseover="showMenu(this.id)" alt="<?=$_TPL['default_template']['name']?>" /></div>
<ul id="chostlp_menu" class="chostlp_drop" style="display: none">
<?php if(is_array($_TPL['templates'])) { foreach($_TPL['templates'] as $value) { ?>
<li><a href="cp.php?ac=common&op=changetpl&name=<?=$value['name']?>" title="<?=$value['name']?>"><img src="<?=$value['icon']?>" alt="<?=$value['name']?>" /></a></li>
<?php } } ?>
</ul>
<?php } ?>

<p class="r_option">
<a href="javascript:;" onclick="window.scrollTo(0,0);" id="a_top" title="TOP"><img src="image/top.gif" alt="" style="padding: 5px 6px 6px;" /></a>
</p>

<?php if($_SGLOBAL['ad']['footer']) { ?>
<p style="padding:5px 0 10px 0;"><?php adshow('footer'); ?></p>
<?php } ?>

<p>
<?=$_SCONFIG['sitename']?> - 
<a href="mailto:<?=$_SCONFIG['adminemail']?>">联系我们</a>
<?php if($_SCONFIG['miibeian']) { ?> - <a  href="http://www.miibeian.gov.cn" target="_blank"><?=$_SCONFIG['miibeian']?></a><?php } ?>
</p>
<p>
Powered by <a  href="http://u.discuz.net" target="_blank"><strong>UCenter Home</strong></a> <span title="<?php echo X_RELEASE; ?>"><?php echo X_VER; ?></span>
<?php if(!empty($_SCONFIG['licensed'])) { ?><a  href="http://license.comsenz.com/?pid=7&host=<?=$_SERVER['HTTP_HOST']?>" target="_blank">Licensed</a><?php } ?>
&copy; 2001-2009 <a  href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>
</p>
</div>
</div>
<!--/wrap-->

<?php if($_SGLOBAL['appmenu']) { ?>
<ul id="ucappmenu_menu" class="dropmenu_drop" style="display:none;">
<li><a href="<?=$_SGLOBAL['appmenu']['url']?>" title="<?=$_SGLOBAL['appmenu']['name']?>" target="_blank"><?=$_SGLOBAL['appmenu']['name']?></a></li>
<?php if(is_array($_SGLOBAL['appmenus'])) { foreach($_SGLOBAL['appmenus'] as $value) { ?>
<li><a href="<?=$value['url']?>" title="<?=$value['name']?>" target="_blank"><?=$value['name']?></a></li>
<?php } } ?>
</ul>
<?php } ?>

<?php if($_SGLOBAL['supe_uid']) { ?>
<?php if(!isset($_SCOOKIE['checkpm'])) { ?>
<script language="javascript"  type="text/javascript" src="cp.php?ac=pm&op=checknewpm&rand=<?=$_SGLOBAL['timestamp']?>"></script>
<?php } ?>
<?php if(!isset($_SCOOKIE['synfriend'])) { ?>
<script language="javascript"  type="text/javascript" src="cp.php?ac=friend&op=syn&rand=<?=$_SGLOBAL['timestamp']?>"></script>
<?php } ?>
<?php } ?>
<?php if(!isset($_SCOOKIE['sendmail'])) { ?>
<script language="javascript"  type="text/javascript" src="do.php?ac=sendmail&rand=<?=$_SGLOBAL['timestamp']?>"></script>
<?php } ?>

<?php if($_SGLOBAL['ad']['couplet']) { ?>
<script language="javascript" type="text/javascript" src="script/couplet.js"></script>
<div id="uch_couplet" style="z-index: 10; position: absolute; display:none">
<div id="couplet_left" style="position: absolute; left: 2px; top: 60px; overflow: hidden;">
<div style="position: relative; top: 25px; margin:0.5em;" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('uch_couplet');"><img src="image/advclose.gif"></div>
<?php adshow('couplet'); ?>
</div>
<div id="couplet_rigth" style="position: absolute; right: 2px; top: 60px; overflow: hidden;">
<div style="position: relative; top: 25px; margin:0.5em;" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('uch_couplet');"><img src="image/advclose.gif"></div>
<?php adshow('couplet'); ?>
</div>
<script type="text/javascript">
lsfloatdiv('uch_couplet', 0, 0, '', 0).floatIt();
</script>
</div>
<?php } ?>

</body>
</html>
<?php } ?>
<?php ob_out();?>