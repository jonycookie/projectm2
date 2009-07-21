<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('template/default/space_feed|template/default/header|template/default/space_menu|template/default/space_feed_li|template/default/footer', '1248082007', 'template/default/space_feed');?><?php $_TPL['titles'] = array('首页动态'); ?>
<?php if(empty($_SGLOBAL['inajax'])) { ?>
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


<div id="content">

<?php if($space['self']) { ?>
<div class="composer_header">

<img src="<?php echo avatar($_SGLOBAL[supe_uid],middle); ?>" alt="<?=$_SN[$_SGLOBAL['supe_uid']]?>" width="120" />

<div class="composer">
<h3 class="index_name">
<a href="space.php?uid=<?=$space['uid']?>"<?php g_color($space[groupid]); ?>><?=$_SN[$space['uid']]?></a>
<?php g_icon($space[groupid]); ?>
</h3>
<p>
已有 <a href="space.php?uid=<?=$space['uid']?>&do=friend&view=visitor"><?=$space['viewnum']?></a> 人次访问, <a href="cp.php?ac=credit"><?=$space['credit']?></a>个积分 <a href="cp.php?ac=credit"><?=$space['creditstar']?></a>
</p>
<div class="current_status" id="mystate">
<?php if($space['mood']) { ?><a href="space.php?uid=<?=$space['uid']?>&do=mood" title="同心情"><img src="image/face/<?=$space['mood']?>.gif" alt="同心情" class="face" /></a> <?php } ?>
<?php if($space['spacenote']) { ?>
<?=$space['spacenote']?>
<?php } elseif(empty($space['mood'])) { ?>
您在做什么？
<?php } ?>
&nbsp;(<a href="javascript:;" onclick="mood_from();" title="更新状态">更新状态</a><span class="pipe">|</span><a href="space.php?uid=<?=$space['uid']?>&do=mood">同心情</a>)
</div>

<ul class="u_setting">
<li><a href="cp.php?ac=avatar">修改头像</a></li>
<li><a href="cp.php?ac=profile">个人资料</a></li>
<li><a href="cp.php?ac=password">账号设置</a></li>
<li><a href="cp.php?ac=privacy">隐私筛选</a></li>
</ul>
</div>
</div>

<div class="mgs_list">
<?php if(!empty($_SGLOBAL['member']['notenum'])) { ?>
<div><img src="image/icon/notice.gif"><a href="space.php?do=notice"><strong><?=$_SGLOBAL['member']['notenum']?></strong> 条新通知</a></div>
<?php } ?>
<?php if($addfriendcount) { ?><div><img src="image/icon/friend.gif" alt="" /><a href="cp.php?ac=friend&op=request"><strong><?=$addfriendcount?></strong> 个好友请求</a></div><?php } ?>
<?php if($mtaginvitecount) { ?><div><img src="image/icon/mtag.gif" alt="" /><a href="cp.php?ac=mtag&op=mtaginvite"><strong><?=$mtaginvitecount?></strong> 个群组邀请</a></div><?php } ?>
<?php if($myinvitecount) { ?><div><img src="image/icon/userapp.gif" alt="" /><a href="space.php?do=notice&view=userapp"><strong><?=$myinvitecount?></strong> 个应用消息</a></div><?php } ?>
<?php if(!empty($_SGLOBAL['member']['newpm'])) { ?><div><img src="image/icon/pm.gif" alt="" /><a href="space.php?do=pm"><strong><?=$_SGLOBAL['member']['newpm']?></strong> 条新短消息</a></div><?php } ?>
<?php if($pokecount) { ?><div><img src="image/icon/poke.gif" alt="" /><a href="cp.php?ac=poke"><strong><?=$pokecount?></strong> 个新招呼</a></div><?php } ?>
<?php if($newreport) { ?><div><img src="image/icon/report.gif" alt="" /><a href="admincp.php?ac=report"><strong><?=$newreport?></strong> 个举报</a></div><?php } ?>
<?php if($namestatus) { ?><div><img src="image/icon/profile.gif" alt="" /><a href="admincp.php?ac=name&perpage=20&namestatus=0&searchsubmit=1"><strong><?=$namestatus?></strong> 个待认证用户</a></div><?php } ?>
</div>

<div class="tabs_header" style="padding-top:10px;">

<?php if($_SCONFIG['my_status']) { ?>
<ul class="tabs">
<li id="viewall" onmouseover="showMenu(this.id)"<?=$my_actives['all']?>><a href="<?=$theurl?>&filter=all"><span>全部动态 <img src="image/tri.gif" alt="" /></span></a></li>
<li id="viewsite" onmouseover="showMenu(this.id)"<?=$my_actives['site']?>><a href="<?=$theurl?>&filter=site"><span>站内 <img src="image/tri.gif" alt="" /></span></a></li>
<li id="viewmyapp" onmouseover="showMenu(this.id)"<?=$my_actives['myapp']?>><a href="<?=$theurl?>&filter=myapp"><span>应用 <img src="image/tri.gif" alt="" /></span></a></li>
</ul>
<?php } else { ?>
<ul class="tabs">
<li<?=$actives['we']?>><a href="space.php?do=home&view=we"><span>好友的动态</span></a></li>
<li<?=$actives['all']?>><a href="space.php?do=home&view=all"><span>大家的</span></a></li>
<li<?=$actives['me']?>><a href="space.php?do=home&view=me"><span>自己的</span></a></li>
</ul>
<?php } ?>
</div>
<?php } else { ?>
<?php $_TPL['spacetitle'] = "动态"; ?>
<div class="c_header a_header">
<div class="avatar48"><a href="space.php?uid=<?=$space['uid']?>"><img src="<?php echo avatar($space[uid],small); ?>" alt="<?=$_SN[$space['uid']]?>" /></a></div>
<p style="font-size:14px"><?=$_SN[$space['uid']]?>的<?=$_TPL['spacetitle']?></p>
<a href="space.php?uid=<?=$space['uid']?>" class="spacelink"><?=$_SN[$space['uid']]?>的主页</a>
<?php if($_TPL['spacemenus']) { ?>
<?php if(is_array($_TPL['spacemenus'])) { foreach($_TPL['spacemenus'] as $value) { ?><span class="pipe">|</span> <?=$value?><?php } } ?>
<?php } else { ?>
<span class="pipe">|</span> <a href="cp.php?ac=poke&op=send&uid=<?=$space['uid']?>" id="a_poke" onclick="ajaxmenu(event, this.id, 99999, '', -1)">打个招呼</a>
<?php } ?>
</div>
<div class="tabs_header">
<ul class="tabs">
<?php if(ckprivacy('index')) { ?><li<?php if(empty($do)) { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>"><span>主页</span></a></li><?php } ?>
<?php if(ckprivacy('doing')) { ?><li<?php if($do=='doing') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=doing&view=me"><span>记录</span></a></li><?php } ?>
<?php if(ckprivacy('blog')) { ?><li<?php if($do=='blog') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=blog&view=me"><span>日志</span></a></li><?php } ?>
<?php if(ckprivacy('album')) { ?><li<?php if($do=='album') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=album&view=me"><span>相册</span></a></li><?php } ?>
<?php if(ckprivacy('share')) { ?><li<?php if($do=='share') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=share&view=me"><span>分享</span></a></li><?php } ?>
<?php if(ckprivacy('mtag')) { ?><li<?php if($do=='mtag'||$do=='thread') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=thread&view=me"><span>话题</span></a></li><?php } ?>
<?php if(ckprivacy('wall')) { ?><li<?php if($do=='wall') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=wall&view=me"><span>留言</span></a></li><?php } ?>
<?php if(ckprivacy('friend')) { ?><li<?php if($do=='friend') { ?> class="active"<?php } ?>><a href="space.php?uid=<?=$space['uid']?>&do=friend&view=me"><span>好友</span></a></li><?php } ?>
</ul>
</div>

<?php } ?>

<div class="feed">

<?php if(empty($_SCOOKIE['closefeedbox']) && $_SGLOBAL['ad']['feedbox']) { ?>
<div id="feed_box">
<div class="task_notice">
<a title="忽略" class="float_cancel" href="javascript:;" onclick="close_feedbox();">忽略</a>
<div class="task_notice_body">
<?php adshow('feedbox'); ?>
</div>
</div>
</div>
<?php } ?>

<?php if($list) { ?>
<div id="feed_div" class="enter-content">
<?php if(is_array($list)) { foreach($list as $day => $values) { ?>
<?php if($day=='yesterday') { ?><h4 class="feedtime">昨天</h4><?php } elseif($day!='today') { ?><h4 class="feedtime"><?=$day?></h4><?php } ?>
<ul>
<?php if(is_array($values)) { foreach($values as $value) { ?>
<li id="feed_<?=$value['feedid']?>_li">
<div style="width:100%;overflow:hidden;"<?=$value['style']?>>
<?php if($value['uid'] && $value['uid']==$_SGLOBAL['supe_uid']) { ?>
<a href="cp.php?ac=feed&op=delete&feedid=<?=$value['feedid']?>" class="float_delete" id="a_feed_<?=$value['feedid']?>" onclick="ajaxmenu(event, this.id, 99999)" title="删除">删除</a>
<?php } elseif($value['uid'] && $space['self'] && $notime) { ?>
<a href="cp.php?ac=feed&op=ignore&icon=<?=$value['icon']?>&uid=<?=$value['uid']?>&feedid=<?=$value['feedid']?>" id="a_feedicon_<?=$value['feedid']?>" onclick="ajaxmenu(event, this.id, 99999)" class="float_cancel" title="屏蔽">屏蔽</a>
<?php } ?>
<div class="avatar48"><a href="space.php?uid=<?=$value['uid']?>"><img src="<?php echo avatar($value[uid],small); ?>" alt="<?=$_SN[$value['uid']]?>" width="48" height="48" /></a></div>
<a class="type" href="space.php?uid=<?=$_GET['uid']?>&do=feed&view=<?=$_GET['view']?>&appid=<?=$value['appid']?>&icon=<?=$value['icon']?>" title="只看此类动态"><img src="<?=$value['icon_image']?>" /></a><?=$value['title_template']?> 
<?php if($value['appid']==UC_APPID) { ?>
<?php if($value['body_data']['sid']) { ?>
(<a href="space.php?uid=<?=$value['uid']?>&do=share&id=<?=$value['body_data']['sid']?>"<?=$value['target']?>>评论</a>)
<?php } elseif($value['body_data']['doid']) { ?>
(<a href="space.php?uid=<?=$value['uid']?>&do=doing&doid=<?=$value['body_data']['doid']?>"<?=$value['target']?>>回复</a>)
<?php } ?>
<?php } ?>
<?php if(empty($notime)) { ?><span class="time"><?php echo sgmdate('m-d H:i',$value[dateline],1); ?></span><?php } ?>
<?php if($value['body_general']) { ?>
<div class="popup"><span class="q"><?=$value['body_general']?></span></div>
<?php } ?>
<div class="feed_content">
<?php if($value['image_1']) { ?>
<a href="<?=$value['image_1_link']?>"<?=$value['target']?>><img src="<?=$value['image_1']?>" class="summaryimg" /></a>
<?php } ?>
<?php if($value['image_2']) { ?>
<a href="<?=$value['image_2_link']?>"<?=$value['target']?>><img src="<?=$value['image_2']?>" class="summaryimg" /></a>
<?php } ?>
<?php if($value['image_3']) { ?>
<a href="<?=$value['image_3_link']?>"<?=$value['target']?>><img src="<?=$value['image_3']?>" class="summaryimg" /></a>
<?php } ?>
<?php if($value['image_4']) { ?>
<a href="<?=$value['image_4_link']?>"<?=$value['target']?>><img src="<?=$value['image_4']?>" class="summaryimg" /></a>
<?php } ?>
<?php if($value['body_template']) { ?>
<div class="detail" <?php if($value['image_3']) { ?>style="clear: both;"<?php } ?>>
<?=$value['body_template']?>
</div>
<?php } ?>
<?php if($value['appid']==UC_APPID) { ?>
<?php if(!empty($value['body_data']['flashvar'])) { ?>
<div class="media">
<img src="image/vd.gif" alt="点击播放" onclick="javascript:showFlash('<?=$value['body_data']['host']?>', '<?=$value['body_data']['flashvar']?>', this, '<?=$value['feedid']?>');" style="cursor:pointer;" />
</div>
<?php } elseif(!empty($value['body_data']['musicvar'])) { ?>
<div class="media">
<img src="image/music.gif" alt="点击播放" onclick="javascript:showFlash('music', '<?=$value['body_data']['musicvar']?>', this, '<?=$value['feedid']?>');" style="cursor:pointer;" />
</div>
<?php } elseif(!empty($value['body_data']['flashaddr'])) { ?>
<div class="media">
<img src="image/flash.gif" alt="点击查看" onclick="javascript:showFlash('flash', '<?=$value['body_data']['flashaddr']?>', this, '<?=$value['feedid']?>');" style="cursor:pointer;" />
</div>
<?php } ?>
<?php } ?>
</div>
</div>
</li>


<?php } } ?>
</ul>
<?php } } ?>
</div>
<?php if($space['feedfriend'] && $count==$perpage) { ?>
<div class="page" style="padding-top:20px;"><a href="javascript:;" onclick="feed_more();" id="a_feed_more">&gt;&gt; 查看更多动态</a></div>
<div id="ajax_wait"></div>
<?php } ?>
<?php } else { ?>
<div class="c_form">
还没有相关动态，<a href="space.php?do=home&view=all">去看看大家的动态</a>。
</div>
<?php } ?>
</div>
</div>
<!--/content-->

<div id="sidebar">
<?php if($task) { ?>
<div class="task_notice" style="width:230px;">
<a title="忽略" class="float_cancel" href="cp.php?ac=task&taskid=<?=$task['taskid']?>&op=ignore">忽略</a>
<div class="task_notice_body">
<img src="<?=$task['image']?>" alt="" class="icon" />
<h3><a href="cp.php?ac=task&op=do&taskid=<?=$task['taskid']?>"><?=$task['name']?></a></h3>
<p>可获得 <span class="num"><?=$task['credit']?></span> 积分</p>
</div>
</div>
<?php } ?>

<?php if($visitorlist) { ?>
<div class="sidebox">
<h2 class="title">
<p class="r_option">
<a href="space.php?uid=<?=$space['uid']?>&do=friend&view=visitor">全部</a>
</p>
最近来访
</h2>
<ul class="avatar_list">
<?php if(is_array($visitorlist)) { foreach($visitorlist as $key => $value) { ?>
<li>
<div class="avatar48"><a href="space.php?uid=<?=$value['vuid']?>"><img src="<?php echo avatar($value[vuid],small); ?>" alt="<?=$_SN[$value['vuid']]?>" /></a></div>
<p<?php if($ols[$value['vuid']]) { ?> class="online_icon_p" title="在线"<?php } ?>><a href="space.php?uid=<?=$value['vuid']?>" title="<?=$_SN[$value['vuid']]?>"><?=$_SN[$value['vuid']]?></a></p>
<p class="time"><?php echo sgmdate('n月j日',$value[dateline],1); ?></p>
</li>
<?php } } ?>
</ul>
</div>
<?php } ?>

<?php if($olfriendlist) { ?>
<div class="sidebox">
<h2 class="title">
<p class="r_option">
<a href="space.php?uid=<?=$space['uid']?>&do=friend">全部</a>
</p>
我的好友
</h2>
<ul class="avatar_list">
<?php if(is_array($olfriendlist)) { foreach($olfriendlist as $key => $value) { ?>
<li>
<div class="avatar48"><a href="space.php?uid=<?=$value['uid']?>"><img src="<?php echo avatar($value[uid],small); ?>" alt="<?=$_SN[$value['uid']]?>" /></a></div>
<p<?php if($value['isonline']) { ?> class="online_icon_p" title="在线"<?php } ?>><a href="space.php?uid=<?=$value['uid']?>" title="<?=$_SN[$value['uid']]?>"><?=$_SN[$value['uid']]?></a></p>
<p class="time"><?php if($value['lastactivity']) { ?><?php echo sgmdate('H:i',$value[lastactivity],1); ?><?php } else { ?>热度(<?=$value['num']?>)<?php } ?></p>
</li>
<?php } } ?>
</ul>
</div>
<?php } ?>

<?php if($birthlist) { ?>
<div class="searchfirend">
<h3>好友生日提醒</h3>
<div class="box">
<table cellpadding="2" cellspacing="4">
<?php if(is_array($birthlist)) { foreach($birthlist as $key => $values) { ?>
<tr>
<td align="right" valign="top" style="padding-left:10px;">
<?php if($values['0']['istoday']) { ?>今天<?php } else { ?><?=$values['0']['birthmonth']?>-<?=$values['0']['birthday']?><?php } ?>
</td>
<td style="padding-left:10px;">
<ul>
<?php if(is_array($values)) { foreach($values as $value) { ?>
<li><a href="space.php?uid=<?=$value['uid']?>"><?=$_SN[$value['uid']]?></a></li>
<?php } } ?>
</ul>
</td>
</tr>
<?php } } ?>
</table>
</div>
</div>
<?php } ?>

<div class="searchfirend">
<h3>快速定位</h3>
<form method="post" action="cp.php?ac=friend">
<p>
<?php if($_SCONFIG['realname']) { ?>姓名: <?php } else { ?>用户名:<?php } ?> 
<input type="text" name="username" value="" class="t_input" size="15" />
<input type="hidden" name="searchmode" value="1" />
<input type="hidden" name="findsubmit" value="1" />
<input type="submit" name="findsubmit_btn" value="找人" class="submit" />
</p>
<p><a href="cp.php?ac=friend&op=find">查找我可能认识的人</a><span class="pipe">|</span>
<a href="cp.php?ac=invite">邀请我的好友</a></p>
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
</form>
</div>

</div>
<!--/sidebar-->


<div id="mood_form" style="display:none">
<form method="post" action="cp.php?ac=doing" id="moodform">
<table cellpadding="0" cellspacing="0">
<tr>
<td width="40"><a href="###" id="face" onclick="showFace(this.id, 'message');"><img src="image/facelist.gif" align="absmiddle" /></a></td>
<td>
<input type="text" name="message" id="message" value="" size="30" class="t_input" />
<input type="hidden" name="addsubmit" value="true" />
<input type="hidden" name="spacenote" value="true" />
</td>
<td>&nbsp;<input type="button" id="add" name="add" value="更新" class="submit" onclick="ajaxpost('moodform', 'mystate', 'reloadMood');" /></td>
<td>&nbsp;<a href="javascript:;" onclick="mood_form_cancel();">取消</a></td>
</tr>
</table>
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
</form>
</div>


<?php if($_SCONFIG['my_status']) { ?>
<ul id="viewall_menu" class="dropmenu_drop" style="display: none">
<li><a href="space.php?do=home&view=we&filter=all"<?=$actives['we']?>>好友的</a></li>
<li><a href="space.php?do=home&view=all&filter=all"<?=$actives['all']?>>大家的</a></li>
<li><a href="space.php?do=home&view=me&filter=all"<?=$actives['me']?>>自己的</a></li>
</ul>
<ul id="viewsite_menu" class="dropmenu_drop" style="display: none">
<li><a href="space.php?do=home&view=we&filter=site"<?=$actives['we']?>>好友的</a></li>
<li><a href="space.php?do=home&view=all&filter=site"<?=$actives['all']?>>大家的</a></li>
<li><a href="space.php?do=home&view=me&filter=site"<?=$actives['me']?>>自己的</a></li>
</ul>
<ul id="viewmyapp_menu" class="dropmenu_drop" style="display: none">
<li><a href="space.php?do=home&view=we&filter=myapp"<?=$actives['we']?>>好友的</a></li>
<li><a href="space.php?do=home&view=all&filter=myapp"<?=$actives['all']?>>大家的</a></li>
<li><a href="space.php?do=home&view=me&filter=myapp"<?=$actives['me']?>>自己的</a></li>
</ul>
<?php } ?>

<script type="text/javascript">
var old_html = '';
var next = <?=$start?>;
function reloadMood(showid, result) {
var x = new Ajax();
x.get('do.php?ac=ajax&op=getmood', function(s){
$('mystate').innerHTML = s;
});
}
function mood_from() {
old_html = $('mystate').innerHTML;
$('mystate').innerHTML = $('mood_form').innerHTML;
}
function mood_form_cancel() {
$('mystate').innerHTML = old_html;
}
function feed_more() {
var x = new Ajax('XML', 'ajax_wait');
next = next + <?=$perpage?>;
x.get('cp.php?ac=feed&op=get&start='+next+'&view=<?=$_GET['view']?>&appid=<?=$_GET['appid']?>&icon=<?=$_GET['icon']?>&filter=<?=$_GET['filter']?>', function(s){
$('feed_div').innerHTML += s;
});
}
function close_feedbox() {
var x = new Ajax();
x.get('cp.php?ac=common&op=closefeedbox', function(s){
$('feed_box').style.display = 'none';
});
}
</script>

<?php my_checkupdate(); ?>
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