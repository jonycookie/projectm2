<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: batch.panel.php 11626 2009-03-13 02:27:20Z zhanglijun $
*/

include_once('./common.php');
include_once(S_ROOT.'./language/batch.lang.php');

$uid = $_SGLOBAL['supe_uid'];
$ucurl = avatar($uid);
$siteurl = S_URL_ALL;
if(!empty($uid)) {
	if($channels['menus']['bbs']) {
		$bbshtml = ' | <a href="'.$_SC['bbsurl'].'" target="_blank">'.$blang['forum_visit'].'</a>';
	}
	if($channels['menus']['uchblog'] || $channels['menus']['uchimage']) {
		$uchhtml = ' | <a href="'.$_SC['uchurl'].'" target="_blank">'.$blang['home_visit'].'</a>';
	}
	$showpost = 0;
	$showposturl = '';
	$divhtml = '<div id="contribute_op" style="display:none;"><ul>';
	if(!in_array('news', $_SCONFIG['closechannels']) && !empty($_SGLOBAL['group']['managespacenews'])) {
		$divhtml .= '<li><a href="'.$siteurl.'/admincp.php?action=spacenews&op=add" target="_blank" onclick="hidendivop();">'.$lang['news'].'</a></li>';
		$showpost++;
		$showposturl = $siteurl.'/admincp.php?action=spacenews&op=add';
	}
	
	include_once(S_ROOT.'./function/model.func.php');
	$midarr = getuserspacemid();
	if(!empty($midarr)) {
		foreach($midarr as $tmpkey => $tmpvalue) {		
			$divhtml .= '<li><a href="'.$siteurl.'/admincp.php?action=modelmanages&mid='.$tmpvalue['mid'].'&op=add" onclick="hidendivop();" target="_blank">'.$tmpvalue['modelalias'].'</a></li>';
			$showpost++;
			$showposturl = $siteurl.'/admincp.php?action=modelmanages&mid='.$tmpvalue['mid'].'&op=add';
		}
	}
	
	if($showpost == 1) {
		$showposturl = "document.write('<a href=\"$showposturl\" class=\"contribute_txt\" target=\"_blank\">$lang[pannel_contribution]</a> ');";
	} elseif($showpost > 1) {
		$showposturl = "document.write('<a href=\"javascript:contributeop();\" class=\"contribute_txt\">$lang[pannel_contribution]</a> ');";
	}
	
	$divhtml .= '</ul></div>';
	
	print <<<END
	function contributeop() {
		if($('contribute_op').style.display != 'block') {
			$('contribute_op').style.display = 'block';
		} else {
			$('contribute_op').style.display = 'none';
		}	
	}
	function hidendivop(){
		$('contribute_op').style.display = 'none';
	}
	document.write('<div id="user_login_position">');
	document.write('<h3>$blang[user_panel]</h3>');
	document.write('<div class="user_info">');
	document.write('<dl>');
	document.write('<dt><a href="$siteurl/space.php?uid=$uid"><img src="$ucurl" alt=""></a></dt>');
	document.write('<dd>');
	document.write('$blang[welcome], <a href="$siteurl/space.php?uid=$uid">$_SGLOBAL[supe_username]</a> [<a href="$siteurl/batch.login.php?action=logout">$blang[safe_logout]</a>]<br />');
	document.write('<a class="tx_blue" href="$siteurl/space.php?uid=$uid">$blang[my_space]</a>');
	document.write('</dd>');
	document.write('</dl>');
    document.write('<div class="user_op">');
	$showposturl
	document.write(' <span><a href="$siteurl/batch.search.php">$blang[search]</a>');
	document.write('$bbshtml');
	document.write('$uchhtml');
	document.write(' | <a href="$siteurl/admincp.php" target="_blank">$blang[management]</a> </span></div>');
	document.write('</div>$divhtml</div>');
END;
} else {

	$formhash = formhash();
	print <<<END
	var noseccode = $_SCONFIG[noseccode];
	document.write('<div id="user_login_position">');
	document.write('<div id="login_authcode_img" style="display:none"><img src="$siteurl/do.php?action=seccode" alt="$lang[verification_code]" id="img_seccode" /></div>');
	document.write('<h3>$blang[user_login]</h3>');
	document.write('<form id="login_box" action="$siteurl/batch.login.php?action=login" method="post">');
	document.write('<input type="hidden" name="formhash" value="$formhash" />');
	document.write('<fieldset><legend>$blang[user_login]</legend>');
	document.write('<p><label>$blang[username]:</label> <input type="text"  name="username" class="input_tx" size="23" onfocus="addseccode();" /></p>');
	document.write('<p><label>$blang[password]:</label> <input type="password" name="password" class="input_tx" size="23" onfocus="addseccode();" /></p>');
	document.write('<p id="login_authcode_input" style="display:none"><label>$lang[verification_code]:</label> <input type="text" class="input_tx" name="seccode" size="10" onfocus="showseccode()"; /> <a href="javascript:updateseccode();">$lang[changge_verification_code]</a></p>');
	document.write('<div id="login_showclose" style="display:none"><a href="javascript:hidesec();">&nbsp;</a></div>');
	document.write('<div class="clearfix">');
	document.write('<input id="cookietime" type="checkbox" value="315360000" name="cookietime" class="input_remember"/>');
	document.write('<label class="label_remember" for="cookietime">$blang[i_remember]</label>');
	document.write('<input type="submit" name="loginsubmit" class="input_sub" value="$blang[login]" />');
	document.write('</div>');
	document.write('<p class="login_ext"><a href="$siteurl/do.php?action=register">$blang[registration]</a> | <a href="$siteurl/do.php?action=lostpasswd">$blang[find_passwords]</a></p>');
	document.write('</fieldset></form></div>');
END;
}

/**
 * 取得用户后台模型mid
 * return array
 */
function getuserspacemid() {
	
	$cachefile = S_ROOT.'./cache/model/model.cache.php';
	$cacheinfo = '';
	if(file_exists($cachefile)) {
		include_once($cachefile);
	}
	if(!empty($cacheinfo) && is_array($cacheinfo)) {
		return $cacheinfo;
	} else {
		include_once(S_ROOT.'./function/cache.func.php');
		return updateuserspacemid();
	}
}

?>
