<?php
if(!class_exists('skin_global')) require('global.php');

class skin_error
{
	function skin_error()
	{
		$this->global			= new skin_global;
	}

	function error_page($msg,$show_login=0)
	{
		global $icebb;
		
		$code					= $this->global->header();
		$code				   .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['error_title']}</h2>
	<div class="row1" style="padding: 5px;">
		<strong>{$icebb->lang['error_details']}</strong>
		<div class="highlight_error">
{$msg}
		</div>
		
		<div class="other_opt">
			<strong>{$icebb->lang['error_links']}</strong>
			<ul style='margin:0px;padding:0px;list-style-type:none'>
				<li><a href='{$icebb->base_url}act=login&amp;func=register'>{$icebb->lang['register_new_account']}</a></li>
				<li><a href='{$icebb->base_url}act=login&amp;func=forgotpass'>{$icebb->lang['forgot_your_password']}</a></li>
			</ul>
		</div>
	</div>
</div>

EOF;

		if($show_login && empty($icebb->user['id']))
		{
			$code			   .= <<<EOF
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
		
EOF;

		if($icebb->settings['enable_openid'])
		{
			$code .= <<<EOF
		<fieldset>
			<legend>{$icebb->lang['openid']} <a href='http://openid.net/'>({$icebb->lang['whats_this']})</a></legend>
			<div class='info'>{$icebb->lang['openid_desc']}</div>
		
			<input type='text' name='openid_url' id='openid_url_1' value='' class='form_textbox openid_login' tabindex='3' />
		</fieldset>

EOF;
		}

$code .= <<<EOF
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
		}

		$code				    .= "<br />\n";
		$code				    .= $this->global->footer();

		return $code;
	}

}
?>
