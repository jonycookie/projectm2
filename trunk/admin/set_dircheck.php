<?php
!function_exists('adminmsg') && die('Foribidden');
$yes = "<span style=\"color:green\">YES</span>";
$no = "<span style=\"color:red\">NO</span>";
$htm_check = is_writeable(D_P.$sys['htmdir']) ? $yes : $no;
$img_check = is_writeable(D_P.$sys['attachdir']) ? $yes : $no;
$imgtemp_check = is_writeable(D_P.$sys['attachdir'].'/temp') ? $yes : $no;
$imgs_check = is_writeable(D_P.$sys['attachdir'].'/s') ? $yes : $no;
$usertpl_check = is_writeable(D_P.$user_tplpath) ? $yes : $no;
$tpl_check = is_writeable(D_P.'data/tpl_cache') ? $yes : $no;
$sql_check = is_writeable(D_P.'data/sql') ? $yes : $no;
$rss_check = is_writeable(D_P.'data/rss') ? $yes : $no;
$data_check = is_writeable(D_P.'data') ? $yes : $no;
$cache_check = is_writeable(D_P.'data/cache') ? $yes : $no;
$jscache_check = is_writeable(D_P.'script/cms') ? $yes : $no;
require PrintEot('set_dircheck');
adminbottom();
?>