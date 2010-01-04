<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: header.inc.php 10930 2007-10-21 10:00:20Z monkey $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

@dheader('Content-Type: text/html; charset='.$charset);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" type="text/css" id="css" href="./images/admincp/admincp.css">
<script>
var menus = new Array('basic', 'forums', 'users', 'posts', 'api', 'others', 'tools', 'home', 'insenz');
function togglemenu(id) {
	if(parent.menu) {
		for(k in menus) {
			if(parent.menu.document.getElementById(menus[k])) {
				parent.menu.document.getElementById(menus[k]).style.display = menus[k] == id ? '' : 'none';
			}
		}
	}
}

function sethighlight(n) {
	var lis = document.getElementsByTagName('li');
	for(var i = 0; i < lis.length; i++) {
		lis[i].id = '';
	}
	lis[n].id = 'menuon';
}

<?php

$menu = !empty($_GET['menu']) ? $_GET['menu'] : (!empty($_POST['menu']) ? $_POST['menu'] : 'home');
if($menu) {
	echo 'togglemenu("'.$menu.'");';
}

?>

</script>
<script src="include/javascript/common.js" type="text/javascript"></script>
<script src="include/javascript/menu.js" type="text/javascript"></script>
</head>
<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="topmenubg">
<tr>
<td rowspan="2" width="160px">
<div class="logo">
<a href="http://www.discuz.net/" target="_blank"><img src="./images/admincp/logo.gif" alt="Discuz!" class="logoimg" border="0"/></a>
<span class="editiontext">Discuz! <span class="editionnumber"><?=$version?></span><br /><?=$lang['admincp']?></span>
</div>
</td><td>
<div class="topmenu">
<ul>

<?if($adminid == 1) {?>

<li><span><a href="#" onclick="sethighlight(0); togglemenu('basic'); parent.main.location='admincp.php?action=settings&do=basic';return false;"><?=$lang['header_global']?></a></span></li>
<li><span><a href="#" onclick="sethighlight(1); togglemenu('forums'); parent.main.location='admincp.php?action=forumsedit';return false;"><?=$lang['header_forum']?></a></span></li>
<li><span><a href="#" onclick="sethighlight(2); togglemenu('users'); parent.main.location='admincp.php?action=members';return false;"><?=$lang['header_user']?></a></span></li>
<li><span><a href="#" onclick="sethighlight(3); togglemenu('posts'); parent.main.location='admincp.php?action=modthreads';return false;"><?=$lang['header_topic']?></a></span></li>
<li><span><a href="#" onclick="sethighlight(4); togglemenu('api'); parent.main.location='admincp.php?action=pluginsconfig';return false;"><?=$lang['header_extended']?></a></span></li>
<li><span><a href="#" onclick="sethighlight(5); togglemenu('others'); parent.main.location='admincp.php?action=announcements';return false;"><?=$lang['header_misc']?></a></span></li>
<?

	echo '<li><span><a href="#" onclick="sethighlight(6); togglemenu(\'insenz\'); parent.main.location=\'admincp.php?action=insenz&operation=campaignlist&c_status=2\';return false;">'.$lang['header_insenz'].'</a></span></li>';

	if($isfounder && checkpermission('dbimport', 0)) {
		echo '<li><span><a href="#" onclick="sethighlight(7); togglemenu(\'tools\'); parent.main.location=\'admincp.php?action=export\';return false;">'.$lang['header_tools'].'</a></span></li>';
	} else {
		echo '<li><span><a href="#" onclick="sethighlight(7); togglemenu(\'tools\'); parent.main.location=\'admincp.php?action=counter\';return false;">'.$lang['header_tools'].'</a></span></li>';
	}

} else {
?>

<li><span><a href="#" onClick="parent.location='<?=$indexname?>'"><?=$lang['header_home']?></a></span></li>
<li><span><a href="#" onClick="parent.menu.location='admincp.php?action=menu'; parent.main.location='admincp.php?action=home';return false;"><?=$lang['header_admin']?></a></span></li>
<li><span><a href="admincp.php?action=logout&sid=<?=$sid?>" target="_top"><?=$lang['menu_logout']?></a></span></li>

<?}?>

</ul>
</div>
</td></tr>
</table>
</body></html>