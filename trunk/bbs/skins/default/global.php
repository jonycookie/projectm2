<?php
class skin_global
{

function header()
{
global $icebb;

if(!empty($icebb->settings['site_name']))
{
	$pre		= "<a href='{$icebb->settings['site_url']}'>{$icebb->settings['site_name']}</a> | ";
}

if(!empty($icebb->settings['rules_show_link']))
{
	$suf	   .= "<a href='{$icebb->base_url}act=boardrules'>{$icebb->lang['boardrules']}</a> ";
}

if(is_array($icebb->menu_extra))
{
	foreach($icebb->menu_extra as $m)
	{
		$suf   .= "<a href='{$icebb->base_url}{$m['url']}'>{$m['title']}</a> ";
	}
}
$code		   .= <<<EOF
<script type='text/javascript' src='skins/<#SKIN#>/skin.js'></script>

<div id='menu'>
{$pre}
<a href='{$icebb->base_url}'>{$icebb->lang['home']}</a>
<a href='{$icebb->base_url}act=members'>{$icebb->lang['members']}</a>
<a href='{$icebb->base_url}act=search'>{$icebb->lang['search']}</a>
{$suf}
</div>
<a href="{$icebb->base_url}" title="{$icebb->settings['board_name']}"><img src='skins/<#SKIN#>/images/icebb_logo.png' alt='{$icebb->settings['board_name']}' /></a>
EOF;



$code .= <<<EOF

EOF;

$code .= $this->user_bar($icebb->user);
$code .= $this->breadcrumbs($icebb->nav);

return $code;
}

function user_bar($user)
{
global $icebb;

if($icebb->skin->user_bar_disabled) return $this->user_bar_disabled($user);

if($user['id']!='0')
{

$code .= <<<EOF
<div id="Organizer">
<span style="float:right">
EOF;

if($user['g_is_admin']=='1')
{
$code .= " <a href='admin/'>{$icebb->lang['admin_control_center']}</a> &middot; ";
}

if($user['new_pms']>=1)
{
$code .= " <a href='{$icebb->base_url}act=pm' style='font-weight:bold'>{$icebb->lang['private_messages']} ({$user['new_pms']})</a> &middot; ";
}
else {
$code .= " <a href='{$icebb->base_url}act=pm'>{$icebb->lang['private_messages']}</a> &middot; ";
}

$code .= <<<EOF
<a href='{$icebb->base_url}act=search&amp;func=newposts'>{$icebb->lang['view_new_posts']}</a> &middot; <a href='{$icebb->base_url}act=ucp'>{$icebb->lang['user_control_panel']}</a> &middot; <a href='{$icebb->base_url}act=login&amp;func=logout'>{$icebb->lang['logout']}</a></span>
{$icebb->lang['welcome_user']}<a href='{$icebb->base_url}profile={$icebb->user['id']}'>{$user['username']}</a>

</div>

EOF;

if($icebb->user['away'] == true)
{
$reason = strlen($icebb->user['away_reason']) >=33 ? substr($icebb->user['away_reason'],0,30)."..." : $icebb->user['away_reason'];
$reason = substr($reason,-4)==' ...' ? substr($reason,0,strlen($reason)-4)."..." : $reason;
$reason = !empty($reason) ? ": {$reason}" : null;
$code .= <<<EOF
<div id="away_box">
<span style="float:right">

<a href='{$icebb->base_url}act=ucp&amp;func=away_system&amp;end=1'>{$icebb->lang['away_end']}</a> &middot; <a href='{$icebb->base_url}act=ucp&amp;func=away_system'>{$icebb->lang['away_settings']}</a></span>
{$icebb->lang['away']}{$reason}

</div>
EOF;
}

}
else {

$code .= <<<EOF
<div id='Organizer'>
<span style="float:right"><strong><a href='{$icebb->base_url}act=login&amp;func=register'>{$icebb->lang['register_new_account']}</a></strong> &middot; <strong><a href='{$icebb->base_url}act=login'>{$icebb->lang['login_to_account']}</a></strong></span>
{$icebb->lang['welcome_guest']}
</div>

EOF;

}

$code .= <<<EOF

EOF;

return $code;
}

function user_bar_disabled($user)
{
global $icebb;

$code .= <<<EOF
<div id='Organizer' class='disabled'>
{$icebb->lang['menu_disabled']}
</div>

EOF;

return $code;
}

function pm_notification($numnew)
{
global $icebb;

$new_pms = sprintf($icebb->lang['new_private_messages'],$numbew);

$code .= <<<EOF
<div class='border' id='pm_notification'>
<strong>{$new_pms}</strong>
<a href='{$icebb->base_url}act=pm'>{$icebb->lang['go_to_inbox']}</a> &middot; <a href='#' onclick="_getbyid('pm_notification').style.display='none';return false">{$icebb->lang['hide_message']}</a>
</div>

EOF;

return $code;
}

function breadcrumbs($cookie_monster)
{
global $icebb;

$code .= <<<EOF
<div id='Nav'><strong><a href='{$icebb->base_url}'>{$icebb->settings['board_name']}</a></strong>
EOF;

if(count($cookie_monster)>0) $code .= " &gt; ";
$tmp	= implode(' &gt; ',$cookie_monster);
$code  .= $tmp;

$code .= <<<EOF
</div>

EOF;

return $code;
}

function redirect($msg,$url)
{
global $icebb;

$code .= <<<EOF
<html>
<head>
<title>{$icebb->lang['stand_by']}</title>
<style type='text/css'>
@import 'skins/default/css.css';
body
{
	text-align:center;
}
</style>
<meta http-equiv="Refresh" content="2;url={$url}" />
</head>
<div class="borderwrap" style='width:500px;margin:200px auto;text-align:left'>
<h2>{$icebb->lang['stand_by']}</h2>
<div class="row1" style='padding:10px'>{$msg}</div>
<div class="row3" style='padding:4px;text-align:center'><a href='{$url}'>{$icebb->lang['skip']}</a></div>
</div>
</body>
</html>

EOF;

return $code;
}

function redirect_inpage($msg)
{
global $icebb;

$code .= <<<EOF
<div id='redirect_inpage' class='row2'>
{$msg}
</div>
<script type='text/javascript'>
// <![CDATA[
new Effect.Highlight($('redirect_inpage'));
setTimeout("new Effect.DropOut($('redirect_inpage'))",5000);
// ]]>
</script>

EOF;

return $code;
}

function board_offline($msg)
{
global $icebb;

$code = $this->header();
$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['board_offline']}</h2>
	<div class="row1" style="padding: 5px;">
		<div class="highlight_error">
{$msg}
		</div>
	</div>
</div>

<br />
<div class="borderwrap">
	<h2>{$icebb->lang['login_below']}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='login' />
		<input type='hidden' name='from' value='{$from}' />
		
		<fieldset>
			<legend>{$icebb->lang['username']}</legend>
			<div class='info'>{$icebb->lang['username_desc']}
			<a href='{$icebb->base_url}act=login&amp;func=register'>{$icebb->lang['username_none']}</a></div>
		
			<input type='text' name='user' id='user_1' value='' class='form_textbox' tabindex='1' />
		</fieldset>
		
		<fieldset>
			<legend>{$icebb->lang['password']}</legend>
			<div class='info'>{$icebb->lang['password_desc']}
			<a href='index.php?act=login&amp;func=forgotpass'>{$icebb->lang['password_forgot']}</a></div>
			
			<input type='password' name='pass' id='pass_1' value='' class='form_textbox' tabindex='2' />
		</fieldset>
				
		<fieldset>
			<legend>{$icebb->lang['additional_info']}</legend>
			
			<label><input type='checkbox' name='remember' value='true' class='checkbox' /> {$icebb->lang['remember']}</label><br />
			<label><input type='checkbox' name='invisible' value='true' class='checkbox' /> {$icebb->lang['invisible']}</label>
		</fieldset>
						
		<div class='buttonstrip'>
			<input type='submit' name='func' value='{$icebb->lang['login_button']}' class='form_button' />
		</div>
	</form>
</div>

EOF;
$code .= $this->footer();

return $code;
}

function paginate($data,$pages,$need_dots)
{
global $icebb;

if(empty($pages['main'])) return false;

foreach($pages['main'] as $pg => $pginfo)
{
	if($pginfo['active'])
	{
		$pages_html['main'][]= "<a href='{$data['base_url']}page={$pg}' class='pageon'><strong>{$pg}</strong></a>";
	}
	else {
		$pages_html['main'][]= "<a href='{$data['base_url']}page={$pg}'>{$pg}</a>";
	}
}

$pages_html['main']		= implode('',$pages_html['main']);
$pages_html['first']	= !empty($pages['first']['page']) ? "<a href='{$data['base_url']}page={$pages['first']['page']}' class='firstpage'>{$icebb->lang['pg_first']}</a>" : '';
$pages_html['last']		= !empty($pages['last']['page']) ? "<a href='{$data['base_url']}page={$pages['last']['page']}' class='lastpage'>{$icebb->lang['pg_last']}</a>" : '';

if($need_dots['before'])
{
	$dots['before']= "<span class='dots'>&hellip;</span>";
}
if($need_dots['after'])
{
	$dots['after']= "<span class='dots'>&hellip;</span>";
}

$code .= <<<EOF
<div class='pages'>
<strong class='title'>{$icebb->lang['pages']}</strong> {$pages_html['first']}{$dots['before']}{$pages_html['main']}{$dots['after']}{$pages_html['last']}<div class='clear'>&nbsp;</div>
</div>

EOF;

return $code;
}

function paginate_mini($data,$pages,$dots='')
{
global $icebb;

foreach($pages['main'] as $pg => $pginfo)
{
	if($pginfo['active'])
	{
		$pages_html['main'][]= "<a href='{$data['base_url']}page={$pg}' class='pageon'>{$pg}</a>";
	}
	else {
		$pages_html['main'][]= "<a href='{$data['base_url']}page={$pg}'>{$pg}</a>";
	}
}

$pages_html['main']		= implode(', ',$pages_html['main']);
$pages_html['last']		= !empty($pages['last']['page']) ? "<a href='{$data['base_url']}page={$pages['last']['page']}'>{$icebb->lang['pg_last_mini']}</a>" : null;

$code .= <<<EOF
<span class='mini-pages'>
({$icebb->lang['pages']} {$pages_html['main']}{$dots}{$pages_html['last']})
</span>

EOF;

return $code;
}

function forum_dropdown($forums)
{
global $icebb;

$code .= <<<EOF
<form action='{$icebb->base_url}' method='get'>
<select name='forum' class='form_dropdown' onchange="window.location='{$icebb->base_url}forum='+this.options[this.selectedIndex].value">
{$forums}
</select>
<input type='submit' value='{$icebb->lang['go']}' class='form_button' />
</form>

EOF;

return $code;
}

function forum_dropdown_forum($f)
{
global $icebb;

$code .= <<<EOF
<option value='{$f['fid']}'>{$f['name']}</option>

EOF;

return $code;
}

function forum_dropdown_forum_selected($f)
{
global $icebb;

$code .= <<<EOF
<option value='{$f['fid']}' selected='selected'>{$f['name']}</option>

EOF;

return $code;
}

function code_tag($the_code,$type='')
{
global $icebb;

$types['xml']	= ' (XML)';
$types['php']	= ' (PHP)';
$letyp			= $types[$type];
$code		   .= <<<EOF
<div class='code_tag'>
	<div class='Subtitle code-top'>CODE{$letyp}:</div>
	<pre>{$the_code}</pre>
</div>

EOF;

return $code;
}

function code_xml_tag($the_code)
{
global $icebb;

$code .= <<<EOF
<div class='code_tag'>
	<div class='Subtitle code-top'>XML:</div>
	<pre>{$the_code}</pre>
</div>

EOF;

return $code;
}

function quote_tag_top($title,$date,$link)
{
global $icebb;

$code .= <<<EOF
<div class='quote_tag'>
	<div class='Subtitle quote-top'>QUOTE: {$title}{$date}{$link}</div>

EOF;

return $code;
}

function quote_tag_bottom()
{
global $icebb;

$code .= <<<EOF
</div>

EOF;

return $code;
}

function popup_window()
{
global $icebb;

$code .= <<<EOF
<html>
<head>
<title>{$icebb->settings['board_name']}</title>
<#CSS#>
</head>
<body bgcolor='#ffffff'>
<div id='content' style='margin:-2px;width:100%'>
<#CONTENT#>
</div>
</body>
</html>

EOF;

return $code;
}

function skin_chooser($skins)
{
global $icebb;

$code .= <<<EOF
<div class='skin_chooser'>
<strong>Skin</strong>
<form action='index.php' method='get'>
<select name='skinid' class='form_dropdown' onchange="window.location='{$icebb->base_url}{$icebb->input['ICEBB_QUERY_STRING']}&amp;skinid='+this.value+'&amp;sticky'">
{$skins}
</select>
</form>
</div>

EOF;

return $code;
}

function lang_chooser($langs)
{
global $icebb;

$code .= <<<EOF
<div class='lang_chooser'>
<strong>Language</strong>
<form action='index.php' method='get'>
<select name='lang' class='form_dropdown' onchange="window.location='{$icebb->base_url}{$icebb->input['ICEBB_QUERY_STRING']}&amp;lang='+this.value+'&amp;sticky'">
{$langs}
</select>
</form>
</div>

EOF;

return $code;
}

function stats($queries,$time,$sload)
{
global $icebb;

$exec			= sprintf($icebb->lang['exec_time'],$time);
$load			= sprintf($icebb->lang['server_load'],$sload);
$query_count	= sprintf($icebb->lang['query_count'],$queries);

$code .= <<<EOF
<div id='stats'>
{$exec} &middot; {$load} &middot; {$query_count} &middot; <a href='{$icebb->base_url}{$icebb->input['ICEBB_QUERY_STRING']}&amp;debug=1'>{$icebb->lang['debug']}</a>
</div>

EOF;

return $code;
}

function footer()
{
global $icebb;

$code .= <<<EOF

EOF;

return $code;
}

}
?>
