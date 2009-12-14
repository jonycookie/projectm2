<?php
$skin['name']				= "IceBB Default Skin";
$skin['author']				= "XAOS Interactive";
$skin['site']				= "http://icebb.net/";
$skin['icebb_ver']			= '0.9.3.1';
$skin['wrapper']			= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><#TITLE#></title>
<#CSS#>
<meta http-equiv='Content-Type' content="text/html; charset=utf-8" />
<#HEADER_EXTRA#>
<#JAVASCRIPT#>
</head>
<body>
<div id='initWrap'>
<#HEADER#>
<!--PM_NOTIFICATION-->
<#LOGIN_BAR#>
<#CRUMBS#>
<#REDIRECT_STUFF#>
<#CONTENT#>
<#FOOTER#>

<div id='footer'>
<#STATS#>
<#COPYRIGHT#>
</div>
</div>
</body>
</html>
	
	
	
	

EOF;
$skin['macros']			= array(
array("QUICK_REPLY","<img src='skins/<#SKIN#>/images/q_reply.png' alt='Quick Reply' />"),
array("ADD_REPLY","<img src='skins/<#SKIN#>/images/add_reply.png' alt='Add Reply' />"),
array("NEW_TOPIC","<img src='skins/<#SKIN#>/images/new_topic.png' alt='New Topic' />"),
array("ADD_REPLY_LOCKED","<img src='skins/<#SKIN#>/images/closed.png' alt='Topic Locked' />"),
array("P_EDIT","<img src='skins/<#SKIN#>/images/edit.png' alt='edit' />"),
array("P_DELETE","<img src='skins/<#SKIN#>/images/delete.png' alt='X' />"),
array("CAT_ICON","<img style='padding-right:2px' src='skins/<#SKIN#>/images/catPaper.png' alt='' />"),
array("PLUS","<img src='skins/<#SKIN#>/images/plus.png' border='0' alt='+' />"),
array("MINUS","<img src='skins/<#SKIN#>/images/minus.png' border='0' alt='-' />"),
array("P_REPORT","<img src='skins/<#SKIN#>/images/report.png' alt='Report post' />"),
array("P_REPLY","<img src='skins/<#SKIN#>/images/reply.png' alt='Reply' />"),
array("T_NONEW","<img src='skins/<#SKIN#>/images/t_nonew.png' alt='No new posts' title='No new posts' />"),
array("T_NEW","<img src='skins/<#SKIN#>/images/t_new.png' alt='New Post' />"),
array("T_LOCKED","<img src='skins/<#SKIN#>/images/t_lock.png' alt='Locked' title='Locked' />"),
array("F_NONEW","<img src='skins/<#SKIN#>/images/f_nonew.png' alt='No new posts in this forum' title='No new posts in this forum' />"),
array("F_NEW","<img src='skins/<#SKIN#>/images/f_new.png' alt='New posts in this forum' title='New posts in this forum' />"),
array("F_REDIRECT","<img src='skins/<#SKIN#>/images/f_redirect.png' alt='Redirect' />"),
array("PIP","<img src='skins/<#SKIN#>/images/pip.png' alt='*' />"),
array("STAR","<img src='skins/<#SKIN#>/images/star.png' alt='*' />"),
array("STAR_OFF","<img src='skins/<#SKIN#>/images/star_off.png' alt='' />"),
array("T_HOTNEW","<img src='skins/<#SKIN#>/images/t_hotnew.png' alt='Hot topic - new posts' title='Hot topic - no new posts' />"),
array("T_HOT","<img src='skins/<#SKIN#>/images/t_hot.png' alt='Hot topic - no new posts' title='Hot topic - no new posts' />"),
array("loading_ani","<img src='skins/<#SKIN#>/images/loading.png' alt='' />"),
array("RSS_ICON","<img src='skins/<#SKIN#>/images/feed-icon.png' alt='RSS Feed available' />"),

);
?>
