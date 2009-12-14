<?php
require('global.php');

class skin_stats
{

function skin_stats()
{
	global $icebb,$global;
	
	$global			= new skin_global;
}

function leaders($data)
{
global $icebb,$global;

if(count($data['admins']) > 0)
{
	foreach($data['admins'] as $admin)
	{
		$admins_h .= <<<EOF
		<tr>
			<td class='row2' width='30%'>
				<a href='{$icebb->base_url}profile={$admin['id']}'>{$admin['username']}</a>
			</td>
			<td class='row1' width='40%'>
				{$icebb->lang['all_forums']}
			</td>
			<td class='row2'>
				<a href='{$icebb->base_url}act=pm&func=write&send_to={$admin['id']}'>{$icebb->lang['pm']}</a>
			</td>
		</tr>
	
EOF;
	}
}
else {
	$admins_h = <<<EOF
		<tr>
			<td class='row2' colspan='3'>
				<em>{$icebb->lang['no_admins']}</em>
			</td>
		</tr>
	
EOF;
}

if(count($data['global_moderators']) > 0)
{
	foreach($data['global_moderators'] as $gmod)
	{
		$gmods_h .= <<<EOF
		<tr>
			<td class='row2' width='30%'>
				<a href='{$icebb->base_url}profile={$gmod['id']}'>{$gmod['username']}</a>
			</td>
			<td class='row1' width='40%'>
				{$icebb->lang['all_forums']}
			</td>
			<td class='row2'>
				<a href='{$icebb->base_url}act=pm&func=write&send_to={$gmod['id']}'>{$icebb->lang['pm']}</a>
			</td>
		</tr>
	
EOF;
	}
}
else {
	$gmods_h = <<<EOF
		<tr>
			<td class='row2' colspan='3'>
				<em>{$icebb->lang['no_global_mods']}</em>
			</td>
		</tr>
	
EOF;
}

if(count($data['moderators']) > 0)
{
	foreach($data['moderators'] as $mod)
	{
		$forums  = "<select onchange=\"if(this.value) { location.href=icebb_base_url+'forum='+this.value; }\" style='width: 15em;'>\n";
		$forums .= "\t\t\t\t<option value='' selected='selected'>---</option>\n";
		foreach($mod['forums'] as $forum)
		{
			$forums .= "\t\t\t\t<option value='{$forum['id']}'>{$forum['name']}</option>\n";
		}
		$forums .= "\t\t\t</select>";
		
		$mods_h .= <<<EOF
	<tr>
		<td class='row2' width='30%'>
			<a href='{$icebb->base_url}profile={$mod['user_id']}'>{$mod['username']}</a>
		</td>
		<td class='row1' width='40%'>
			{$forums}
		</td>
		<td class='row2'>
			<a href='{$icebb->base_url}act=pm&func=write&send_to={$mod['user_id']}'>{$icebb->lang['pm']}</a>
		</td>
	</tr>

EOF;
	}
}
else {
	$mods_h .= <<<EOF
	<tr>
		<td class='row2' colspan='3'>
			<em>{$icebb->lang['no_mods']}</em>
		</td>
	</tr>

EOF;
}

$code .= $global->header();
$code .= <<<EOF
<div class="borderwrap">
	<h2>{$icebb->lang['forum_leaders']}</h2>
	<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
		<tr>
			<th colspan='3'>
				{$icebb->lang['admins']}
			</th>
		</tr>
{$admins_h}		<tr>
			<th colspan='3'>
				{$icebb->lang['global_mods']}
			</th>
		</tr>
{$gmods_h}		<tr>
			<th colspan='3'>
				{$icebb->lang['mods']}
			</th>
		</tr>
{$mods_h}	</table>
</div>

EOF;
$code .= $global->footer();

return $code;
}

// NOT CURRENTLY IN USE
function online_list_detailed($num_online,$rows)
{
global $icebb,$global;

$code	= $global->header();
$code .= <<<EOF
<div class='borderwrap'>
	<h2>Users Online ({$num_online})</h2>
	<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
		<tr>
			<th>Username</th>
			<th>Last Click</th>
			<th>Location</th>
		</tr>
{$rows}
	</table>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function online_list_detailed_row($r)
{
global $icebb;

$code .= <<<EOF
		<tr>
			<td>
				{$r['username_format']}{$r['uip']}
			</td>
			<td>
				{$r['last_click']}
			</td>
			<td>
				{$r['location']}
			</td>
		</tr>

EOF;

return $code;
}

}
?>