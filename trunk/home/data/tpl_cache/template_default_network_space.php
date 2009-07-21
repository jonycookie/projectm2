<?php if(!defined('IN_UCHOME')) exit('Access Denied');?><?php subtplcheck('template/default/network_space|template/default/header|template/default/network_header|template/default/space_list|template/default/footer', '1248083690', 'template/default/network_space');?><?php $_TPL['titles'] = array('成员', '随便看看'); ?>
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

<h2 class="title"><img src="image/icon/network.gif">随便看看<?php if($_TPL['titles']['0']) { ?> - <?=$_TPL['titles']['0']?><?php } ?></h2>
<div class="tabs_header">
<ul class="tabs">
<li<?=$actives['index']?>><a href="network.php"><span>全部</span></a></li>
<li<?=$actives['doing']?>><a href="network.php?ac=doing"><span>记录</span></a></li>
<li<?=$actives['blog']?>><a href="network.php?ac=blog"><span>日志</span></a></li>
<li<?=$actives['album']?>><a href="network.php?ac=album"><span>相册</span></a></li>
<li<?=$actives['share']?>><a href="network.php?ac=share"><span>分享</span></a></li>
<li<?=$actives['thread']?>><a href="network.php?ac=thread"><span>话题</span></a></li>
<li<?=$actives['mtag']?>><a href="network.php?ac=mtag"><span>群组</span></a></li>
<li<?=$actives['space']?>><a href="network.php?ac=space"><span>成员</span></a></li>
</ul>
</div>

<div id="content" style="width:640px;">

<div class="c_mgs">
<div id="m_search"<?php if(!empty($gets)) { ?> style="display:none;"<?php } ?>>
<form method="get" action="network.php">
用户名 <input type="text" name="username" value="<?=$_GET['username']?>" class="t_input" />
<input type="hidden" name="ac" value="<?=$ac?>" />
<input type="hidden" name="searchmode" value="1" />
<input type="submit" name="findsubmit" value="找人" class="submit" />
<a href="javascript:;" onclick="document.getElementById('m_search').style.display='none';document.getElementById('adv_search').style.display='block'">高级搜索</a>
</form>
</div>
<form method="get" action="network.php">
<table cellspacing="0" cellpadding="0" class="formtable" id="adv_search"<?php if(empty($gets)) { ?> style="display:none;"<?php } ?>>
<caption>
<h2>高级搜索</h2>
<p>您可以自己设置搜索条件，寻找与自己志同道合的好友</p>
</caption>
<tr>
<th>用户名</th>
<td>
<input type="text" class="t_input" name="username" value="<?=$_GET['username']?>" />
&nbsp; 姓名 <input type="text" class="t_input" name="name" value="<?=$_GET['name']?>" />
</td>
</tr>
<tr>
<th>性别</th>
<td>
<select id="sex" name="sex">
<option value="0">任意</option>
<option value="1"<?=$sexarr['1']?>>男</option>
<option value="2"<?=$sexarr['2']?>>女</option>
</select>
&nbsp; 生日 
<select id="birthyear" name="birthyear">
<option value="0">年</option>
<?=$birthyeayhtml?>
</select> 
<select id="birthmonth" name="birthmonth">
<option value="0">月</option>
<?=$birthmonthhtml?>
</select> 
<select id="birthday" name="birthday">
<option value="0">日</option>
<?=$birthdayhtml?>
</select> 
</td>
</tr>

<tr>
<th>血型</th>
<td>
<select id="blood" name="blood">
<option value="">任意</option>
<?=$bloodhtml?>
</select>
&nbsp; 婚恋 
<select id="marry" name="marry">
<option value="0">任意</option>
<option value="1"<?=$marryarr['1']?>>单身</option>
<option value="2"<?=$marryarr['2']?>>非单身</option>
</select>
</td>
</tr>

<tr>
<th>出生地</th>
<td>
<script type="text/javascript" src="script/city.js"></script>
<script type="text/javascript">
<!--
showprovince('birthprovince', 'birthcity', '<?=$_GET['birthprovince']?>');
showcity('birthcity', '<?=$_GET['birthcity']?>');
//-->
</script>
&nbsp; 居住地 
<script type="text/javascript">
<!--
showprovince('resideprovince', 'residecity', '<?=$_GET['resideprovince']?>');
showcity('residecity', '<?=$_GET['residecity']?>');
//-->
</script>
</td>
</tr>
<tr>
<th>QQ</th>
<td>
<input type="text" name="qq" value="<?=$_GET['qq']?>" class="t_input" />
&nbsp; MSN 
<input type="text" name="msn" value="<?=$_GET['msn']?>" class="t_input" />
</td>
</tr>
<tr>
<th>年龄段</th>
<td>
<input type="text" name="startage" value="<?=$_GET['startage']?>" size="10" class="t_input" /> ~ <input type="text" name="endage" value="<?=$_GET['endage']?>" size="10" class="t_input" />
</td>
</tr>

<tr>
<th>群组</th>
<td>
<select name="fieldid">
<option value="0">请选择</option>
<?php if(is_array($_SGLOBAL['profield'])) { foreach($_SGLOBAL['profield'] as $fieldid => $value) { ?>
<option value="<?=$fieldid?>"<?=$fieldids[$fieldid]?>><?=$value['title']?></option>
<?php } } ?>
</select>
<input type="text" name="fieldname" value="<?=$_GET['fieldname']?>" class="t_input" />
</td>
</tr>

<?php if(is_array($fields)) { foreach($fields as $fkey => $fvalue) { ?>
<?php if($fvalue['allowsearch']) { ?>
<tr>
<th><?=$fvalue['title']?></th>
<td>
<?=$fvalue['html']?>
</td>
</tr>
<?php } ?>
<?php } } ?>


<tr>
<th>&nbsp;</th>
<td>
<input type="hidden" name="ac" value="space" />
<input type="hidden" name="searchmode" value="1" />
<input type="submit" name="findsubmit" value="找人" class="submit" />
</td>
</tr>
</table>
</form>
</div>

<?php if($now_pos >= 0) { ?>
<div class="c_mgs">
排行榜公告：<br>
<?php if($_GET['view']=='show') { ?>
<?php if($space['showcredit']) { ?>
自己当前的竞价积分为：<?=$space['showcredit']?>，当前排名 <span style="font-size:20px;color:red;"><?=$now_pos?></span> ，再接再励！
<?php } else { ?>
您现在还没有上榜。让自己上榜吧，这会大大提升您的主页曝光率。
<?php } ?>
<br>竞价积分越多，竞价排名越靠前，您的主页曝光率也会越高；
<br>上榜用户的主页被别人有效浏览一次，其竞价积分将减少1个(恶意刷新访问不扣减)。
<?php } else { ?>
<?php if($_GET['view']=='credit') { ?>
<a href="cp.php?ac=credit">自己当前的积分：<?=$space['credit']?></a>
<?php } elseif($_GET['view']=='friendnum') { ?>
<a href="space.php?do=friend">自己当前的好友数：<?=$space['friendnum']?></a>
<?php } else { ?>
<a href="space.php">自己当前的访问量：<?=$space['viewnum']?></a>
<?php } ?>
，当前排名 <span style="font-size:20px;color:red;"><?=$now_pos?></span> ，再接再励！
<?php } ?>
<?php if($cache_mode) { ?>
<p>下面列出的为前100名排行，数据每 <?=$cache_time?> 分钟更新一次。</p>
<?php } ?>
</div>
<?php } ?>

<?php if($_GET['view']=='show') { ?>
<div class="c_mgs">
<table width="100%">
<tr><td width="50%" valign="top">
<div class="l_status"><strong>帮助好友来上榜</strong></div>
<div class="content">
<form method="post" action="cp.php?ac=credit" onsubmit="return checkCredit('stakecredit');">
<p>
要帮助的好友用户名<br />
<input type="text" name="fusername" value="" size="20" class="t_input" /><br />
赠送竞价积分(<span class="gray">不要超过自己的积分:<?=$space['credit']?></span>)<br />
<input type="text" id="stakecredit" name="stakecredit" value="100" size="5" class="t_input" onblur="checkCredit('stakecredit');" /> <input type="submit" name="friend_submit" value="赠送" class="submit" />
</p>
<input type="hidden" name="friendsubmit" value="true" />
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" />
</form>
</div>
</td>
<td width="50%" valign="top">
<div class="l_status"><strong>我也要上榜</strong></div>
<div class="content">
<form method="post" action="cp.php?ac=credit" onsubmit="return checkCredit('showcredit');">
<p>
我的上榜宣言(<span class="gray">最多50个汉字，会显示在榜单中</span>)
<br />
<input type="text" name="note" value="" size="35" class="t_input" /><br />
增加竞价积分(<span class="gray">不要超过自己的积分:<?=$space['credit']?></span>)<br />
<input type="text" id="showcredit" name="showcredit" value="100" size="5" class="t_input" onblur="checkCredit('showcredit');" /> <input type="submit" name="show_submit" value="增加" class="submit" />
</p>
<input type="hidden" name="showsubmit" value="true" />
<input type="hidden" name="formhash" value="<?php echo formhash(); ?>" /></form>
</div>
</td>
</tr></table>
<script type="text/javascript">
function checkCredit(id) {
var maxCredit = parseInt(<?=$space['credit']?>);
var idval = parseInt($(id).value);
if(idval > maxCredit) {
alert("您的当前积分为:"+maxCredit+",请填写一个小于该值的数字");
return false;
} else if(idval < 1) {
alert("您所填写的积分值不能小于1");
return false;
}
return true;
}
</script>
</div>
<?php } ?>

<div class="space_list">
<?php if($list) { ?>
<table cellspacing="0" cellpadding="0" width="100%">
<thead>
<tr>
<td width="30">&nbsp;</td>
<td width="55">&nbsp;</td>
<td>
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td>用户名</td>
<td width="50">性别</td>
<td width="55"><?php if($_GET['view']=='show') { ?>竞价<?php } else { ?>积分<?php } ?></td>
<td width="55">人气</td>
<td width="55">好友</td>
<td width="50">&nbsp;</td>
</tr>
</table>
</td>
</tr>
</thead>
<?php if(is_array($list)) { foreach($list as $key => $value) { ?>
<tr class="<?php if($key%2==1) { ?>  alt<?php } ?>">
<td><?php echo ($start+$key+1); ?></td>
<td><div class="avatar48"><a href="space.php?uid=<?=$value['uid']?>"><img src="<?php echo avatar($value[uid],small); ?>" alt="<?=$_SN[$value['uid']]?>" /></a></div></td>
<td>
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td>
<p<?php if($ols[$value['uid']]) { ?> class="online_icon_p"<?php } ?>>
<a href="space.php?uid=<?=$value['uid']?>" title="<?=$_SN[$value['uid']]?>"<?php g_color($value[groupid]); ?>><?=$_SN[$value['uid']]?></a>
<?php if($value['username'] && $_SN[$value['uid']]!=$value['username']) { ?><span class="gray">(<?=$value['username']?>)</span><?php } ?>
<?php g_icon($value[groupid]); ?>
</p>
</td>
<td width="50"><?php if($value['sex']==2) { ?>美女<?php } elseif($value['sex']==1) { ?>帅哥<?php } else { ?>保密<?php } ?></td>
<td width="55" title="积分"><?=$value['credit']?></td>
<td width="55" title="人气"><?=$value['viewnum']?></td>
<td width="55" title="好友"><?=$value['friendnum']?></td>
<td width="50">
<a href="cp.php?ac=poke&op=send&uid=<?=$value['uid']?>" id="a_poke_<?=$key?>" onclick="ajaxmenu(event, this.id, 99999, '', -1)" title="打招呼"><img src="image/icon/poke.gif" align="absmiddle"></a>
<?php if(!$value['isfriend']) { ?><a href="cp.php?ac=friend&op=add&uid=<?=$value['uid']?>" id="a_friend_<?=$key?>" onclick="ajaxmenu(event, this.id, 99999, '', -1)" title="加好友"><img src="image/icon/friend.gif" align="absmiddle"></a><?php } ?>	
</td>
</tr>
</table>
<?php if($ols[$value['uid']]) { ?>
<div class="topline">
当前在线(<?php echo sgmdate('H:i',$ols[$value[uid]]); ?>)
</div>
<?php } ?>
<?php if($value['note']) { ?>
<div class="note">
<?=$value['note']?>
</div>
<?php } ?>
</td>
</tr>
<?php } } ?>
</table>
<div class="page"><?=$multi?></div>
<?php } else { ?>
<div class="c_form">没有相关成员。</div>
<?php } ?>
</div>



</div>

<div id="sidebar" style="width:150px;">
<div class="cat">
<h3>分类查看</h3>
<ul class="post_list line_list">
<li<?=$sub_actives['all']?>><a href="network.php?ac=space" title="按照最新更新排序"><span>全部成员</span></a>
<li<?=$sub_actives['online']?>><a href="network.php?ac=space&view=online"><span>在线成员</span></a>
<li<?=$sub_actives['show']?>><a href="network.php?ac=space&view=show"><span>竞价排行</span></a>
<li<?=$sub_actives['mm']?>><a href="network.php?ac=space&view=mm"><span>美女排行</span></a>
<li<?=$sub_actives['gg']?>><a href="network.php?ac=space&view=gg"><span>帅哥排行</span></a>
<li<?=$sub_actives['credit']?>><a href="network.php?ac=space&view=credit"><span>积分排行</span></a>
<li<?=$sub_actives['friendnum']?>><a href="network.php?ac=space&view=friendnum"><span>好友数排行</span></a>
<li<?=$sub_actives['viewnum']?>><a href="network.php?ac=space&view=viewnum"><span>访问量排行</span></a>
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