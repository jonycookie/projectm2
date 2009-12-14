<?php
require('global.php');

class skin_login_screen
{
	function skin_login_screen()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function display($msg='Please login',$username='')
	{
		global $icebb;
		
		$code			   .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>{$icebb->lang['admin_control_center']}</title>
<style type='text/css' media='screen'>
@import '{$icebb->settings['board_url']}admin/skins/default/css.css';
</style>
<meta http-equiv='Content-Type' content="text/html; charset=utf-8" />
<script type='text/javascript' src='{$icebb->settings['board_url']}jscripts/prototype/prototype.js'></script>
<script type='text/javascript' src='{$icebb->settings['board_url']}jscripts/scriptaculous/scriptaculous.js'></script>
<script type='text/javascript' src='{$icebb->settings['board_url']}jscripts/global.js'></script>
<script type='text/javascript' src='{$icebb->settings['board_url']}jscripts/xmlhttp.js'></script>
<script type='text/javascript' src='{$icebb->settings['board_url']}jscripts/menu.js'></script>
<script type='text/javascript' src='{$icebb->settings['board_url']}jscripts/sha256.js'></script>
{$icebb->admin->header_extra}
<script type='text/javascript'>
icebb_base_url='{$icebb->settings['board_url']}admin/{$icebb->base_url}';
icebb_main_base_url='{$icebb->settings['board_url']}index.php?';
</script>
</head>
<body id='login_screen'>
<div style='text-align:center'>
<form action='index.php' method='post' onsubmit="this.password_sha256.value=SHA256(this.password.value);this.password.value=''">
<input type='hidden' name='return' value='{$icebb->input['return']}' />
<input type='hidden' name='restrict_ip' value='1' />
<div class='borderwrap' style='width:60%;margin:0px auto;text-align:left'>
	<h3>{$icebb->lang['acc_title']}</h3>
	<div class='Subtitle'>{$msg}</div>
	
	<img src='skins/default/images/icebb_logo.png' alt='' style='float:left;vertical-align:middle' />
	
	<fieldset>
		<legend>{$icebb->lang['username']}</legend>
	
		<input type='text' name='username' id='user_1' value='{$username}' class='form_textbox' tabindex='1' />
	</fieldset>
	
	<fieldset>
		<legend>{$icebb->lang['password']}</legend>
		
		<input type='hidden' name='password_sha256' value='' />
		<input type='password' name='password' id='pass_1' value='' class='form_textbox' tabindex='2' />
	</fieldset>
	
EOF;

		if($icebb->settings['enable_openid'])
		{
			$code .= <<<EOF
		<fieldset>
			<legend>{$icebb->lang['openid']}</legend>
		
			<input type='text' name='openid_url' id='openid_url_1' value='' class='form_textbox openid_login' tabindex='3' />
		</fieldset>

EOF;
		}

$code .= <<<EOF
	<div id='login_screen_links'>
		<a href='{$icebb->settings['board_url']}index.php'>Back to board</a>
	</div>
	
	<div style='clear:both'></div>
					
	<div class='buttonstrip'>
		<input type='submit' name='submit' value="{$icebb->lang['login']}" class='form_button' />
	</div>
</div>
</form>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
}
?>
