<?php
require('global.php');

class skin_login
{
	function skin_login()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function loginPage($login_msg,$from='index.php')
{
global $icebb,$global;

$code .= $global->header();
$code .= <<<EOF

EOF;

if($login_msg!='')
{

$code .= <<<EOF
<div class='borderwrap' style='margin-bottom:6px;'>
<h2>{$icebb->lang['login_error']}</h2>
<div class="row1" style="padding: 5px;">
	<div class='highlight_error'>
{$login_msg}
	</div>
</div>
</div>

EOF;

}

$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['login']}</h2>
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
			
			<label><input type='checkbox' name='remember' value='true' class='checkbox' tabindex='3' /> {$icebb->lang['remember']}</label>
		</fieldset>
						
		<div class='buttonstrip'>
			<input type='submit' name='func' value='{$icebb->lang['login_button']}' class='form_button' tabindex='5' />
		</div>
	</form>
</div>
EOF;

$code			   .= $global->footer();

return $code;
}

function registerPage_terms($tos)
{
global $icebb,$global;

$code .= $global->header();
$code .= <<<EOF
<div class='borderwrap row2'>
	<h2>{$icebb->lang['toc_title']}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='login' />
		<input type='hidden' name='func' value='Register' />
		
		<div class="row1" style="padding:5px">
			<strong>{$icebb->lang['toc_question']}</strong>
			<div class="highlight_error">
{$tos}
			</div>
		</div>
		
		<div class='buttonstrip'>
			<input type='submit' name='terms' value='{$icebb->lang['toc_agree']}' class='form_button' />
			<input type='submit' name='terms_dis' value='{$icebb->lang['toc_disagree']}' class='form_button' />
		</div>
	</form>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function registerPage($reg_msg,$time_zones='')
{
global $icebb,$global;

$code .= $global->header();
$code .= <<<EOF
EOF;

if($reg_msg!='')
{
$code .= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
<h2>{$icebb->lang['error']}</h2>
<div class='error'>
{$reg_msg}
</div>
</div>
EOF;
}

$code .= <<<EOF
<script type='text/javascript'>
var pword			= "{$icebb->lang['password_tips']}";
</script>
<script type='text/javascript' src='jscripts/tooltip.js'></script>

<div class='borderwrap row2'>
	<h2>{$icebb->lang['register']}</h2>
EOF;

$code .= <<<EOF
	
	<form action='index.php' method='post'>
	<input type='hidden' name='act' value='login' />
	<input type='hidden' name='func' value='Register' />
	<input type='hidden' name='terms' value='1' />

	<fieldset>
		<legend>{$icebb->lang['username']}</legend>
		<div class='info'>{$icebb->lang['reg_username_desc']}</div>
		
		<input type='text' name='user' id='user_1' value='' class='form_textbox' tabindex='1' />
	</fieldset>
	
	<fieldset>
		<legend>{$icebb->lang['password']}</legend>
		<div class='info'>{$icebb->lang['reg_password_desc']}
		(<a href='#' onclick="return false" onmouseover="return showTip('pword')" onmousemove="return showTip('pword')" onmouseout="return hideTip('pword')">?</a>)</div>
		
		<input type='password' name='pass' id='pass_1' value='' class='form_textbox' tabindex='2' /> 
		<input type='password' name='pass2' id='pass_2' value='' class='form_textbox' tabindex='3' />
	</fieldset>
	
	<fieldset>
		<legend>{$icebb->lang['email']}</legend>
		<div class='info'>{$icebb->lang['reg_email_desc']}</div>
		
		<input type='text' name='email' id='email_1' value='' class='form_textbox' tabindex='4' />
	</fieldset>
	
	<fieldset>
		<legend>{$icebb->lang['time_zone']}</legend>
		<select name='gmt' class='form_dropdown'>
		{$time_zones}
		</select>
	</fieldset>
	
	<!--WORD_VERIFICATION-->
			
	<div class='buttonstrip'>
		<input type='submit' name='submit' value='{$icebb->lang['reg_button']}' class='form_button' />
	</div>
	</form>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function forgotPassPage()
{
global $icebb,$global;

$code .= $global->header();
$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['forgot_password']}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='login' />
		<input type='hidden' name='func' value='forgotpass' />
		<table width='100%' cellpadding='6' cellspacing='0' border='0' id='login_form'>
			<tr>
				<td valign='top' class='row2'>
					<fieldset>
						<legend>{$icebb->lang['forgot_email']}</legend>
						<input type='text' name='email' id='email_1' value='' class='form_textbox' tabindex='1' />
					</fieldset>
				</td>
				<td valign='top' class='row2'>
					<!--WORD_VERIFICATION-->
				</td>
			</tr>
		</table>
		<div class='buttonstrip'>
			<input type='submit' name='submit' value='{$icebb->lang['forgot_button']}' class='form_button' />
		</div>
	</form>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function resend_validate_link($id)
{
global $icebb;

$code .= <<<EOF
<a href='{$icebb->base_url}act=login&amp;func=resend_validate&amp;id={$id}'>{$icebb->lang['click_here']}</a>
EOF;

return $code;
}

function word_verification($captcha_code)
{
global $icebb;

$code .= <<<EOF
				<fieldset>
					<legend>{$icebb->lang['word_verification']}</legend>
					<div class='info'>{$icebb->lang['word_verification_desc']}</div>
					
					<input type='hidden' name='captcha_code' value='{$captcha_code}' /> 
					<a href='#' onclick="document.captcha_img.src='index.php?act=login&amp;func=captcha&amp;s={$captcha_code}&amp;redraw='+Math.random();return false" title="{$icebb->lang['word_verification_link']}"><img src='index.php?act=login&amp;func=captcha&amp;s={$captcha_code}' name='captcha_img' alt='' /></a><br /> 
					<input type='text' name='captcha_word' id='captchaword' value='' class='form_textbox' style='margin-top:2px' />
				</fieldset>

EOF;

return $code;
}

function error_msg($error)
{
global $icebb;

$code .= <<<EOF
<div> - {$error}</div>

EOF;
return $code;
}


	function openid_more_needed($what, $why=array())
	{
		global $icebb, $global;

		$code					= $global->header();

		if(count($why) > 0)
		{
			foreach($why as $w)
			{
				$why_msg	   .= "\t\t\t<li>{$w}</li>";
			}
			
			$code			   .= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
	<h2>{$icebb->lang['error']}</h2>
	<div class='error'>
		<ul>
{$why_msg}
		</ul>
	</div>
</div>

EOF;
		}

		$code				   .= <<<EOF
<div class='borderwrap row2'>
	<h2>{$icebb->lang['openid_more_needed']}</h2>
	<div class='Subtitle'>{$icebb->lang['openid_more_needed_info']}</div>
	
	<form action='{$icebb->base_url}act=login' method='post'>
	<input type='hidden' name='openid_info' value='1' />

EOF;

		if(in_array('username',$what))
		{
			$code			   .= <<<EOF
	<fieldset>
		<legend>{$icebb->lang['username']}</legend>
		<div class='info'>{$icebb->lang['reg_username_desc']}</div>
		
		<input type='text' name='user' id='user_1' value='' class='form_textbox' tabindex='1' />
	</fieldset>


EOF;
		}
		
		if(in_array('email',$what))
		{
			$code			   .= <<<EOF
	<fieldset>
		<legend>{$icebb->lang['email']}</legend>
		<div class='info'>{$icebb->lang['reg_email_desc']}</div>
		
		<input type='text' name='email' id='email_1' value='' class='form_textbox' tabindex='4' />
	</fieldset>


EOF;
		}
		
		$code				   .= <<<EOF
	<div class='buttonstrip'>
		<input type='submit' name='submit' value='{$icebb->lang['login_button']}' class='form_button' />
	</div>
	</form>
</div>

EOF;
		$code				   .= $global->footer();

		return $code;
	}

}
?>
