<?php
if(!class_exists('skin_global')) require('global.php');

class skin_profile
{
	function skin_profile()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function profile_view($u)
{
global $icebb,$global;

$p      = sprintf($icebb->lang['view_profile_u'],$u['username']);
$g		= sprintf($icebb->lang['group'],$u['g_title']);

if(!empty($u['avatar']))
{
	$avatar = <<<EOF

			<td width='1' class="row1">
				<img src='{$u['avatar']}' border='0' alt='' class='photo' />
			</td>

EOF;
}
$title = !empty($u['title']) ? "{$u['title']}<br />\n" : null;

$code	= $global->header();
$code .= <<<EOF
<div class='borderwrap vcard'>
	<h2>{$p}</h2>
	<table width="100%"  border="0" cellspacing="1" cellpadding="1" style='margin-bottom:-1px'>
		<tr>{$avatar}
			<td class='row2' height='100'>
				<div style='font-size:140%'>{$u['g_prefix']}<span class='fn'>{$u['username']}</span>{$u['g_suffix']}</div>
				{$title}{$g}<br />
				{$u['banned']}
			</td>
			<td class='row1' width='20%'>
				<ul style='list-style:none;margin:0px;padding:0px'>
					<li><a href='{$icebb->base_url}act=pm&amp;func=write&amp;send_to={$u['id']}'>{$icebb->lang['send_pm']}</a></li>
					<li><a href='{$icebb->base_url}act=ucp&amp;func=buddies&amp;add={$u['username']}&amp;buddy=1'>{$icebb->lang['add_buddy']}</a></li>
					<li><a href='{$icebb->base_url}act=ucp&amp;func=buddies&amp;block_add={$u['username']}&amp;block=1'>{$icebb->lang['block']}</a></li>
				</ul>
			</td>
		</tr>
	</table>
	<table width="100%"  border="0" cellspacing="1" cellpadding="1">

EOF;
if($u['away'] == true)
{
	$reason = empty($u['away_reason']) ? "<em>{$icebb->lang['no_reason']}</em>" : $u['away_reason'];
	$code .= <<<EOF
		<tr>
			<td class='row3' class='row2' colspan='2'>
				<strong>{$icebb->lang['away']}</strong>
			</td>
		</tr>
		<tr>
			<td class='row2' colspan='2'>
				<em>{$icebb->lang['away_reason']}</em><br />
{$reason}
			</td>
		</tr>

EOF;
}

$ppd		= sprintf($icebb->lang['pro_posts_per_day'],$u['posts_per_day']);
$apb		= sprintf($icebb->lang['pro_posts_view_all_long'],$u['username']);

if(!empty($u['url']))
{
}

$code .= <<<EOF
		<tr>
			<td width="47%" class='row3'>
				<strong>{$icebb->lang['forum_info']}</strong>
			</td>
			<td width="53%" class='row3'>
				<strong>{$icebb->lang['personal_info']}</strong>
			</td>
		</tr>
		<tr>
			<td valign="top" class="row2">
				<table width="100%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="30%">
							<strong>{$icebb->lang['pro_member_since']}</strong>
						</td>
						<td>
							{$u['joindate']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_posts']}</strong>
						</td>
						<td>
							{$u['posts']}
							({$ppd} /
							<a href='{$icebb->base_url}act=search&amp;author={$u['id']}' title="{$apb}">{$icebb->lang['pro_posts_view_all']}</a>)
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_lv']}</strong>
						</td>
						<td>
							{$u['last_visit_formatted']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_bloc']}</strong>
						</td>
						<td>
							{$u['blocation']}
						</td>
					</tr>
				</table>
			</td>
			<td valign="top" class="row2" rowspan='3'>
				<table width="100%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="30%">
							<strong>{$icebb->lang['pro_gender']}</strong>
						</td>
						<td>
							{$u['gender']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_bday']}</strong>
						</td>
						<td>
							{$u['birthdate']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_age']}</strong>
						</td>
						<td>
							{$u['age']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_loc']}</strong>
						</td>
						<td>
							{$u['location']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['pro_site']}</strong>
						</td>
						<td>
							{$u['url']}
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class='row3'>
				<strong>{$icebb->lang['contact_info']}</strong>
			</td>
		</tr>
		<tr>
			<td valign="top" class="row2">
				<table width="100%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="30%">
							<strong>{$icebb->lang['msn']}</strong>
						</td>
						<td>
							{$u['msn']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['yim']}</strong>
						</td>
						<td>
							{$u['yahoo']}
						</td>
					</tr>
					<tr>
						<td>
							<strong>{$icebb->lang['aim']}</strong>
						</td>
						<td>
							{$u['aim']}
						</td>
					</tr>
					<!-- ICQ removed: who uses ICQ anymore, anyway? -->
					<tr>
						<td>
							<strong>{$icebb->lang['jabber']}</strong>
						</td>
						<td>
							{$u['jabber']}
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class='row3'>
				<strong>{$icebb->lang['signature']}</strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="row2">
				{$u['siggie']}
			</td>
		</tr>
	</table>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function email($u)
{
global $icebb,$global;

$s		= sprintf($icebb->lang['send_mail_to'],"<a href='{$icebb->base_url}profile={$u['id']}'>{$u['username']}</a>");

$code  = $global->header();
$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$s}</h2>
	<form action='{$PHP_SELF}' method='post'>
		<table width='100%' cellpadding='2' cellspacing='1'>
			<tr>
				<td class='row2' width='40%'>
					<strong>{$icebb->lang['subject']}</strong>
				</td>
				<td class='row1'>
					<input type='text' name='subject' value=''class='form_textbox' size='40' />
				</td>
			</tr>
			<tr>
				<td class='row2'>
					<strong>{$icebb->lang['message']}</strong>
				</td>
				<td class='row1'>
					<textarea cols='40' rows='6' name='body' class='form_textarea'></textarea>
				</td>
			</tr>
			<tr>
				<td class='buttonstrip' colspan='2'>
					<input type='submit' value="{$icebb->lang['send_mail']}" class='form_button' />
				</td>
			</tr>
		</table>
	</form>
</div>
EOF;
$code .= $global->footer();

return $code;
}

		// NOT YET IMPLEMENTED - for later use
		function warn($u,$curr_actions)
		{
		global $icebb,$global;

		$code  = $global->header();
		$code .= <<<EOF
<div class='borderwrap'>
	<h2>Warn {$u['username']}</h2>
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<td width='40%' class='row2'>
				<strong>Warning Level:</strong>
			</td>
			<td class='row1'>
				-<span id='warn-pips'>{$u['warn_pips']}</span><span onclick="_getbyid('warn-pips').innerHTML+=''">+</span> <span id='warn-severity'><strong style='color:#ff9900'>Mild</strong></span>
			</td>
		</tr>
		<tr>
			<td width='40%' class='row2'>
				<strong>Actions to take:</strong>
			</td>
			<td class='row1'>
				Mod Queue
				Ban from this topic
				Ban from this forum
				Disable PMs
				Disable Posting
				Ban
			</td>
		</tr>
	</table>
</div>

EOF;
		$code .= $global->footer();

		return $code;
		}

	function get_uim_menu($u)
	{
		global $icebb;
		
		$code .= <<<EOF
<ul>
	<li><a href='{$icebb->base_url}profile={$u['id']}'>{$icebb->lang['view_profile']}</a></li>
	<li><a href='#' onclick='return false' style='cursor:default'>{$u['online_offline']}</a></li>

EOF;

		if($icebb->user['g_is_mod']=='1' || $icebb->user['g_is_admin']=='1')
		{
			$code .= <<<EOF
	<li><a href='{$icebb->base_url}profile={$u['id']}&amp;func=warn' style='border-top-width:2px'>Discipline</a></li>
	<li><a href='{$icebb->base_url}profile={$u['id']}&amp;func=warn&amp;ban=inf' onclick="s=confirm('Are you sure you want to PERMANENTLY ban this member?');if(!s) return false"> &middot; Ban this member</a></li>
	<li><a href='{$icebb->base_url}profile={$u['id']}&amp;func=warn&amp;prune=allposts' onclick="s=confirm('Are you sure you want to prune ALL posts by this member?');if(!s) return false"> &middot; Delete all posts</a></li>
	<li><a href='{$icebb->base_url}profile={$u['id']}&amp;func=warn' onclick=""> &middot; Warning level: 0/10</a></li>

EOF;
		}

		$code .= <<<EOF
</ul>

EOF;

		return $code;
	}
}
?>
