<?php
if(!class_exists('skin_global')) require('global.php');

class skin_usercp
{
	function skin_usercp()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function layout($content)
{
global $icebb,$global;

$code	= $global->header();
$code .= <<<EOF

<table width="100%"  border="0" cellspacing="1" cellpadding="1">
	<tr>
		<td valign="top" width="24%" style='padding-right:10px'>
			<div class="borderwrap">
				<div class="row1 ucp-menu">
				<h2>{$icebb->lang['menu']}</h2>
				<div class='Subtitle'>{$icebb->lang['personal_profile']}</div>
				<ul>
					<li><a href='{$icebb->base_url}act=ucp'>{$icebb->lang['overview']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=profile">{$icebb->lang['edit_profile']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=avatar">{$icebb->lang['edit_avatar']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=signature">{$icebb->lang['edit_sig']}</a></li>
					<!--<li><a href='{$icebb->base_url}act=groups'>{$icebb->lang['user_groups']}</a></li>-->
					<li><a href='{$icebb->base_url}act=ucp&amp;func=away_system'>{$icebb->lang['away_system']}</a></li>
				</ul>

				<div class='Subtitle'>{$icebb->lang['tools']}</div>
				<ul>
					<li><a href='{$icebb->base_url}act=ucp&amp;func=buddies'>{$icebb->lang['buddy_list']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=favorites">{$icebb->lang['manage_favs']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=uploads">{$icebb->lang['manage_uploads']}</a></li>
				</ul>
				
				<div class='Subtitle'>{$icebb->lang['settings']}</div>
				<ul>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=password">{$icebb->lang['menu_password']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=email">{$icebb->lang['menu_email']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=dateset">{$icebb->lang['menu_date']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=settings">{$icebb->lang['menu_settings']}</a></li>
					<li><a href="{$icebb->base_url}act=ucp&amp;func=emailset">{$icebb->lang['menu_email_settings']}</a></li>
				</ul>
				</div>
			</div>
		</td>
		<td valign="top" width="76%">
			<div class="borderwrap">
				<h2>{$icebb->lang['title']}</h2>
				{$content}
			</div>
		</td>
	</tr>
</table>


EOF;
$code .= $global->footer;

return $code;
}


function main($favorite_topics='',$pm_info=array())
{
global $icebb;

$ppd				= sprintf($icebb->lang['posts_per_day'],$icebb->user['posts_per_day']);
$pm_info['new']		= intval($pm_info['new']);
$pm_info['posts']	= intval($pm_info['posts']);
$pm_info['topics']	= intval($pm_info['topics']);

$code .= <<<EOF
<table width="100%" border="0" cellspacing="5" cellpadding="3" style='clear:both'>
	<tr>
		<td valign="top" width='50%'>
			<fieldset>
				<legend>{$icebb->lang['account_info']}</legend>
				<ul>
					<li><strong>{$icebb->lang['group']}</strong> {$icebb->user['g_title']}</li>
					<li><strong>{$icebb->lang['posts']}</strong> {$icebb->user['posts']} ({$ppd} / <a href='{$icebb->base_url}act=search&amp;author={$icebb->user['id']}'>{$icebb->lang['posts_view_all']}</a>)</li>
					<li><strong>{$icebb->lang['joined']}</strong> {$icebb->user['joined_formatted']}</li>
				</ul>
				</fieldset>
		</td>
		<td valign="top">
			<fieldset>
				<legend>{$icebb->lang['private_messages']}</legend>
				<ul>
					<li><strong>{$icebb->lang['pm_new']}</strong> {$icebb->user['new_pms']}</li>
					<li><strong>{$icebb->lang['pm_topics']}</strong> {$pm_info['topics']}</li>
					<li><strong>{$icebb->lang['pm_posts']}</strong> {$pm_info['posts']}</li>
				</ul>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td colspan='2'>
			<fieldset>
				<legend>{$icebb->lang['favorite_topics']}</legend>
EOF;
if(strlen($favorite_topics) > 0)
{
	$code .= <<<EOF
				<ul>
{$favorite_topics}
				</ul>
EOF;
}
$code .= <<<EOF
			</fieldset>
		</td>
	</tr>
</table>
<br />

	<div class='Subtitle'><strong>{$icebb->lang['your_notepad']}</strong></div>
	<form action='{$PHP_SELF}' method='post' name='notepad' style='display:block;text-align:center'>
		<textarea cols='60' rows='6' name='notepad' class='form_textarea' style='width:96%'>{$icebb->user['notepad']}</textarea>
		<div class='buttonrow'><input type='submit' value="{$icebb->lang['save_your_notes']}" class='form_button' /></div>
	</form>

EOF;

return $code;
}

function main_favtopic($f)
{
global $icebb;

$code .= <<<EOF
					<li><a href='{$icebb->base_url}topic={$f['tid']}'>{$f['title']}</a></li>

EOF;

return $code;
}

function editProfile($dob,$g)
{
global $icebb;

$gender			= implode("\n",$g);

$code .= <<<EOF
<form action='{$PHP_SELF}' method='post' name='profileInfo'>
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<td class='row1' width='40%'>
				<strong>{$icebb->lang['pro_title']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='title' value='{$icebb->user['title']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['pro_loc']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='location' value='{$icebb->user['location']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1' width='40%'>
				<strong>{$icebb->lang['pro_gender']}</strong>
			</td>
			<td class='row2'>
				<select name='gender'>
{$gender}
				</select>
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['msn_long']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='msn' value='{$icebb->user['msn']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['aim_long']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='aim' value='{$icebb->user['aim']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['yim_long']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='yahoo' value='{$icebb->user['yahoo']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['jabber']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='jabber' value='{$icebb->user['jabber']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['pro_site']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='url' value='{$icebb->user['url']}' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['pro_bday']}</strong>
			</td>
			<td class='row2'>
				<select name='dob_month' class='form_dropdown'>{$dob['month']}</select> <select name='dob_day' class='form_dropdown'>{$dob['day']}</select> <select name='dob_year' class='form_dropdown'>{$dob['year']}</select>
			</td>
		</tr>
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='submit' value="{$icebb->lang['save_profile']}" class='form_button' /> 
		<input type='reset' value="{$icebb->lang['clear']}" class='form_button' />
	</div>
</form>

EOF;

return $code;
}

function editSignature($currsig,$editor)
{
global $icebb;

$code .= <<<EOF
<div class='Subtitle'>{$icebb->lang['sig_your']}</div>
{$currsig}<br />
<br />
<form action='{$PHP_SELF}' method='post' name='signatureInfo'>
	<div class='Subtitle'>{$icebb->lang['sig_edit']}</div>
	{$editor}
	<div class='buttonstrip'>
		<input type='submit' value="{$icebb->lang['sig_button']}" class='form_button' />
	</div>
</form>

EOF;

return $code;
}

function editEmail()
{
global $icebb;

$code .= <<<EOF
<form action='{$PHP_SELF}' method='post' name='emailInfo'>
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<td class='row1' width='40%'>
				<strong>{$icebb->lang['pass_current']}</strong>
			</td>
			<td class='row2'>
				<input type='password' name='pass_old' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['email_new']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='mail' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['email_new_confirm']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='mailc' class='form_textbox' />
			</td>
		</tr>
	</table>
	<div class='buttonstrip'>
		<input type='submit' value="{$icebb->lang['email_save']}" class='form_button' />
	</div>
</form>

EOF;

return $code;
}

function editPass()
{
global $icebb;

$code .= <<<EOF
<form action='{$PHP_SELF}' method='post' name='passInfo'>
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<td class='row1' width='40%'>
				<strong>{$icebb->lang['pass_current']}</strong>
			</td>
			<td class='row2'>
				<input type='password' name='pass_old' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['pass_new']}</strong>
			</td>
			<td class='row2'>
				<input type='password' name='pass_new' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['pass_new_confirm']}</strong>
			</td>
			<td class='row2'>
				<input type='password' name='pass_confirm' class='form_textbox' />
			</td>
		</tr>
	</table>
	<div class='buttonstrip'>
		<input type='submit' value="{$icebb->lang['pass_button']}" class='form_button' />
	</div>
</form>
EOF;

return $code;
}

function editPassNone()
{
global $icebb;

$code = <<<EOF
<div class="row1" style="padding: 5px;">
	<div class="highlight_error">
		{$icebb->lang['openid_no_password']}
	</div>
</div>

EOF;

return $code;
}

function editAvatar($u)
{
global $icebb;

$code .= <<<EOF
<div class='row1'>

EOF;

if($icebb->input['avtype']!='none' && !empty($icebb->user['avsize']))
{

$code .= <<<EOF
<div class='Subtitle'>{$icebb->lang['av_current']}</div>
<div class='row2' style='text-align:center;padding:6px'>
<img src='{$icebb->user['avatar']}' alt='' /><br />
{$icebb->user['avsize']} {$icebb->lang['av_pixels']}
</div>

EOF;

}

$code .= <<<EOF
<div class='Subtitle'>{$icebb->lang['av_change']}</div>
<form action='{$PHP_SELF}' method='post' name='avatarInfo' enctype='multipart/form-data'>
	<div style='padding:6px'>
		<input type='radio' name='avtype' value='upload' id='avtype_upload' onchange="$('url').style.display='none';$('upload').style.display='block';$('gallery').style.display='none'" /><label for='avtype_upload'><strong>{$icebb->lang['av_upload']}</strong></label><br />
		<div id='upload' class='under_radio_button' style='display:none'>
			<input type='file' name='file' value='' class='form_textbox' />
			<div class='desc'>{$icebb->lang['av_upload_url']}</div>
		</div>
		
		<input type='radio' name='avtype' value='url' id='avtype_url' onchange="$('url').style.display='block';$('upload').style.display='none';$('gallery').style.display='none'" /><label for='avtype_url'><strong>{$icebb->lang['av_url']}</strong></label><br />
		<div id='url' class='under_radio_button' style='display:none'>
			<input type='text' name='av_url' value='{$u['avatar']}' class='form_textbox' size='40' /><br />
		</div>
		
		<input type='radio' name='avtype' value='none' id='avtype_none' onchange="$('url').style.display='none';$('upload').style.display='none';$('gallery').style.display='none'" /><label for='avtype_none'><strong>{$icebb->lang['av_none']}</strong></label>
	</div>
	
	<div class='buttonstrip'>
		<input type='submit' name='submit' value="{$icebb->lang['av_button']}" class='form_button' />
	</div>
</form>
</div>
<script type='text/javascript'>
$('avtype_{$u['avtype']}').checked=true;
$('{$u['avtype']}').style.display='block';
</script>

EOF;

return $code;
}

function favoriteView($favorite_topics,$favorite_forums)
{
global $icebb;

$code .= <<<EOF
<div class='Subtitle'>{$icebb->lang['favorite_forums']}</div>
<table width='100%' cellpadding='2' cellspacing='1'>
{$favorite_forums}
</table>

<div class='Subtitle'>{$icebb->lang['favorite_topics']}</div>
<table width='100%' cellpadding='2' cellspacing='1'>
{$favorite_topics}
</table>

EOF;

return $code;
}

function favorite_topic($f)
{
global $icebb;

$code .= <<<EOF
	<tr>
		<td width='1%' class='row1'>
			{$f['macro']}
		</td>
		<td class='row2'>
			<a href='{$icebb->base_url}topic={$f['favobjid']}'><strong>{$f['title']}</strong></a>
		</td>
		<td class='row1' style='text-align:right' width='20%'>
			<a href='{$icebb->base_url}act=ucp&amp;func=favorites&amp;opt=delete&amp;type=topic&amp;id={$f['favobjid']}'>{$icebb->lang['rem']}</a>
		</td>
	</tr>

EOF;

return $code;
}

function favorite_forum($f)
{
global $icebb;

$code .= <<<EOF
	<tr>
		<td width='1%' class='row1'>
			&nbsp;
		</td>
		<td class='row2'>
			<a href='{$icebb->base_url}forum={$f['favobjid']}'><strong>{$f['name']}</strong></a>
		</td>
		<td class='row1' style='text-align:right' width='20%'>
			<a href='{$icebb->base_url}act=ucp&amp;func=favorites&amp;opt=delete&amp;type=forum&amp;id={$f['favobjid']}'>{$icebb->lang['rem']}</a>
		</td>
	</tr>

EOF;

return $code;
}

function favorites_none()
{
global $icebb;

$code .= <<<EOF
	<tr>
		<td colspan='3' class='row2'>
			<em class='desc'>{$icebb->lang['fav_none']}</em>
		</td>
	</tr>

EOF;

return $code;
}

function uploads($uploads)
{
global $icebb;

$code .= <<<EOF
<div class='Subtitle'>{$icebb->lang['your_uploads']}</div>
<table width='100%' cellpadding='2' cellspacing='1'>
{$uploads}
</table>

EOF;

return $code;
}

function uploads_none()
{
global $icebb;

$code .= <<<EOF
	<tr>
		<td class='row1'>
			<em>{$icebb->lang['no_uploads']}</em>
		</td>
	</tr>

EOF;

return $code;
}

function uploads_row($u)
{
global $icebb;

$code .= <<<EOF
	<tr>
		<td class='row1'>
			<strong>{$u['uname']}</strong>
		</td>
		<td class='row2'>
			{$u['usize']}
		</td>
		<td class='row1'>
			<a href='{$icebb->base_url}act=ucp&amp;func=uploads&amp;del={$u['uid']}'>{$icebb->lang['rem']}</a>
		</td>
	</tr>

EOF;

return $code;
}

function emailset($m_send,$a_send)
{
global $icebb;

$code .= <<<EOF
<form action='{$PHP_SELF}' method='post'>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td class='row2' width='40%'>
			<strong>{$icebb->lang['allow_admin_contact']}</strong>
		</td>
		<td class='row1'>
			{$a_send}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['allow_mem_contact']}</strong>
		</td>
		<td class='row1'>
			{$m_send}
		</td>
	</tr>
</table>

	<div class='buttonstrip'>
		<input type='submit' value="{$icebb->lang['save_settings']}" class='form_button' />
	</div>
</form>
EOF;

return $code;
}

function settings($view_av,$view_sig,$editor,$quick_edit,$skin_choices,$lang_choices,$view_smileys)
{
global $icebb;

$code .= <<<EOF
<form action='{$PHP_SELF}' method='post'>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td width='40%' class='row2'>
			<strong>{$icebb->lang['view_avs']}</strong>
		</td>
		<td class='row1'>
			{$view_av}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['view_sigs']}</strong>
		</td>
		<td class='row1'>
			{$view_sig}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['view_smileys']}</strong>
		</td>
		<td class='row1'>
			{$view_smileys}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['editor_style']}</strong><br />
			{$icebb->lang['editor_style_desc']}
		</td>
		<td class='row1'>
			<select name='editor_style' class='form_dropdown'>
				<option value='1'{$editor['1']}>{$icebb->lang['editor_1']}</option>
				<option value='2'{$editor['2']}>{$icebb->lang['editor_2']}</option>
				<option value='3'{$editor['3']}>{$icebb->lang['editor_3']}</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['enable_quick_edit']}</strong>
		</td>
		<td class='row1'>
			{$quick_edit}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['skin_choice']}</strong>
		</td>
		<td class='row1'>
			<select name='skin' class='form_dropdown'>
{$skin_choices}
			</select>
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['lang_choice']}</strong>
		</td>
		<td class='row1'>
			<select name='lang' class='form_dropdown'>
{$lang_choices}
			</select>
		</td>
	</tr>
</table>

<div class='buttonstrip'>
	<input type='submit' value="{$icebb->lang['save_settings']}" class='form_button' />
</div>
</form>
EOF;

return $code;
}

function dateset($time,$gmt_select,$dst)
{
global $icebb;

$t		= sprintf($icebb->lang['your_current_time'],$time);

$code .= <<<EOF
<form action='{$PHP_SELF}' method='post'>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td colspan='2' class='row3'>
			{$t}
		</td>
	</tr>
	<tr>
		<td class='row2' width='40%'>
			<strong>{$icebb->lang['your_time_zone']}</strong>
		</td>
		<td class='row1'>
			<select name='gmt' class='form_dropdown'>
{$gmt_select}
			</select>
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['enable_dst']}</strong>
		</td>
		<td class='row1'>
			<input type='checkbox' name='dst' value='1'{$dst} />
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['date_format_select']}</strong><br />
			{$icebb->lang['date_format_select_php']}
		</td>
		<td class='row1'>
			<input type='text' name='date_format' value='{$icebb->user['date_format']}' class='form_textbox' />
		</td>
	</tr>
</table>

<div class='buttonstrip'>
<input type='submit' value="{$icebb->lang['save_settings']}" class='form_button' />
</div>
</form>
EOF;

return $code;
}

	function iptools($ip)
	{
		global $icebb;
		
		$code				   .= <<<EOF
<div class='Subtitle'>{$ip['ip']}</div>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['iptools_resolves']}</strong>
		</td>
		<td class='row1'>
			{$ip['resolved']}
		</td>
	</tr>
	<tr>
		<td class='row3' colspan='2'>
			{$icebb->lang['iptools_members']}
		</td>
	</tr>

EOF;

		if(is_array($ip['members_using']))
		{
			foreach($ip['members_using'] as $u)
			{
			$code			   .= <<<EOF
	<tr>
		<td class='row1' colspan='2'>
			<a href='{$icebb->base_url}profile={$u['id']}'>{$u['username']}</a>
		</td>
	</tr>

EOF;
			}
		}

		$code				   .= <<<EOF
	<tr>
		<td class='row3' colspan='2'>
			{$icebb->lang['iptools_posts']}
		</td>
	</tr>

EOF;

		if(is_array($ip['posts_using']))
		{
			foreach($ip['posts_using'] as $p)
			{
				$pu				= sprintf($icebb->lang['iptools_posts_post'],$p['pid']);
				$code		   .= <<<EOF
	<tr>
		<td class='row1' colspan='2'>
			<a href='{$icebb->base_url}act=search&amp;findpost={$p['pid']}'>{$pu}</a> {$icebb->lang['by']} <a href='{$icebb->base_url}profile={$p['pauthor_id']}'>{$p['pauthor']}</a>
		</td>
	</tr>

EOF;
			}
		}

		$code				   .= <<<EOF
	<tr>
		<td class='row3' colspan='2'>
			<a href='http://dnstools.com/?lookup=on&arin=on&target={$ip['ip']}'>{$icebb->lang['additional_info']}</a>
		</td>
	</tr>
</table>

EOF;

		return $code;
	}
	
	

function away_system()
{
global $icebb;

$code .= <<<EOF
<form action='{$icebb->base_url}act=ucp&amp;func=away_system' method='post'>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td class='row2' width='40%'>
			<strong>{$icebb->lang['away_on_off']}</strong>
		</td>
		<td class='row1'>

EOF;
		if($icebb->user['away'] == true)
		{
			$code .= "<label><input type='radio' name='away' value='1' checked='checked' /> {$icebb->lang['yes']}</label> <label><input type='radio' name='away' value='0' /> {$icebb->lang['no']}</label>";
		}
		else {
			$code .= "<label><input type='radio' name='away' value='1' /> {$icebb->lang['yes']}</label> <label><input type='radio' name='away' value='0' checked='checked' /> {$icebb->lang['no']}</label>";
		}
$code .= <<<EOF

		</td>
	</tr>
	<tr>
		<td class='row2' valign='top'>
			<strong><label for='away_reason'>{$icebb->lang['away_reason']}</label></strong>
		</td>
		<td class='row1'>
			<textarea name='away_reason' cols='40' rows='5' id='away_reason'>{$icebb->user['away_reason']}</textarea>
		</td>
	</tr>
</table>

	<div class='buttonstrip'>
		<input type='submit' value='{$icebb->lang['away_button']}' class='form_button' />
	</div>
</form>
EOF;
	
	return $code;
	}
	
	function buddy_list($buddies,$blocked)
	{
		global $icebb;
		
		foreach($buddies as $bud)
		{
			$buddies_h .= <<<EOF
	<tr>
		<td class='row2'>
			<a href='{$icebb->base_url}profile={$bud['uid']}'>{$bud['username']}</a>
		</td>
		<td class='row1' width='20%' style='text-align:right'>
			<a href='{$icebb->base_url}act=ucp&amp;func=buddies&amp;del={$bud['id']}'>{$icebb->lang['rem']}</a>
		</td>
	</tr>

EOF;
		}
		
		foreach($blocked as $blo)
		{
			$blocked_h .= <<<EOF
	<tr>
		<td class='row2'>
			<a href='{$icebb->base_url}profile={$blo['uid']}'>{$blo['username']}</a>
		</td>
		<td class='row1' width='20%' style='text-align:right'>
			<a href='{$icebb->base_url}act=ucp&amp;func=buddies&amp;del={$blo['id']}'>{$icebb->lang['rem']}</a>
		</td>
	</tr>

EOF;
		}
		
		$code .= <<<EOF
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
	<tr>
		<th colspan='2'>
			{$icebb->lang['bud_buddies']}
		</th>
	</tr>
{$buddies_h}
	<tr>
		<td class='row1' colspan='2'>
			<form action='' method='post'>
				<input type='text' name='add' id='buddy_add' value='' class='user-autocomplete form_textbox' />
				<span class='autocomplete hide' id="buddy_complete"></span>
				<input type='submit' name='buddy' value="{$icebb->lang['add']}" class='form_button' />
				
				<script type="text/javascript">
					new Ajax.Autocompleter('buddy_add','buddy_complete',icebb_base_url+'act=members&amp;ajax_search=1',{})
				</script>
			</form>
		</td>
	</tr>

	<tr>
		<th colspan='2'>
			{$icebb->lang['bud_blocked']}
		</th>
	</tr>
{$blocked_h}
	<tr>
		<td class='row1' colspan='2'>
			<form action='' method='post'>
				<input type='text' name='block_add' id='block_add' value='' class='user-autocomplete form_textbox' />
				<span class='autocomplete hide' id="block_complete"></span>
				<input type='submit' name='block' value="{$icebb->lang['add']}" class='form_button' />
				
				<script type="text/javascript">
					new Ajax.Autocompleter('block_add','block_complete',icebb_base_url+'act=members&amp;ajax_search=1',{})
				</script>
			</form>
		</td>
	</tr>
</table>

EOF;
		
		return $code;
	}
}
?>
