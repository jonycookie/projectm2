<?php
class skin_groupcp
{


function main_top()
{
global $icebb;

$code .= <<<EOF
<table width='100%' cellpadding='2' cellspacing='1'>

EOF;

return $code;
}

function main_row($id,$name,$status,$prefix=null,$suffix=null)
{
global $icebb;

$code .= <<<EOF
	<tr>
		<td class='row1' width='40%'>
			<a href='{$icebb->base_url}act=groups&amp;func=view&amp;gid={$id}'>{$prefix}{$name}{$suffix}</a>
		</td>
		<td class='row2'>
			{$status}
		</td>
	</tr>

EOF;

return $code;
}

function main_bottom()
{
global $icebb;

$code .= <<<EOF
</table>

EOF;

return $code;
}

function view_top($g,$info,$group_mods,$group_members)
{
global $icebb;

$code .= <<<EOF
<div class='Subtitle'>{$icebb->lang['info_name']} {$g['g_prefix']}{$g['g_title']}{$g['g_suffix']}</div>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td class='row2' width='25%'>
			<strong>{$icebb->lang['perm_admin']}</strong>
		</td>
		<td class='row1'>
			{$info['admin']}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['perm_mod']}</strong>
		</td>
		<td class='row1'>
			{$info['mod']}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['perm_access']}</strong>
		</td>
		<td class='row1'>
			{$info['access']}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['perm_access_offline']}</strong>
		</td>
		<td class='row1'>
			{$info['access_offline']}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['perm_status']}</strong>
		</td>
		<td class='row1'>
			{$info['status']}
		</td>
	</tr>
	<tr>
		<td class='row2'>
			<strong>{$icebb->lang['info_desc']}</strong>
		</td>
		<td class='row1'>
			{$g['g_desc']}
		</td>
	</tr>
{$info['lg']}
	<tr>
		<td colspan='2' class='row3'>{$icebb->lang['info_gm']}</td>
	</tr>
	<tr>
		<td colspan='2' class='row1'>
			<ul>
{$group_mods}
			</ul>
		</td>
	</tr>
	<tr>
		<td colspan='2' class='row3'>{$icebb->lang['info_members']}</td>
	</tr>
	<tr>
		<td colspan='2' class='row1'>
			<ul>
{$group_members}
			</ul>
		</td>
	</tr>
</table>

EOF;

return $code;
}

	function leave_group()
	{
		global $icebb;
			
		$code .= <<<EOF
	<tr>
		<td class='row2' style='text-align:center' colspan='2'>
			<a href='{$icebb->base_url}act=groupcp&amp;func=leave'>{$icebb->lang['leave_group']}</a>
		</td>
	</tr>

EOF;
		
		return $code;
	}

	function del_mod_link($uid)
	{
		global $icebb;
		
		$code .= <<<EOF
 (<a href='{$icebb->base_url}act=groups&amp;func=remove_mod&amp;gid={$icebb->input['gid']}&amp;uid={$uid}'>{$icebb->lang['delete']}</a>)
EOF;

		return $code;
	}
	
	function kick_link($uid)
	{
		global $icebb;
		
		$code .= <<<EOF
 (<a href='{$icebb->base_url}act=groups&amp;func=kick&amp;gid={$icebb->input['gid']}&amp;uid={$uid}'>{$icebb->lang['kick']}</a>)
EOF;

		return $code;
	}
	
	function mod_list_row($u,$link)
	{
		global $icebb;
		
		$code .= <<<EOF
				<li><a href='{$icebb->base_url}profile={$u['id']}'>{$u['username']}</a>{$link}</li>

EOF;
		
		return $code;
	}
	
	function user_list_row($u,$link)
	{
		global $icebb;
		
		$code .= <<<EOF
				<li><a href='{$icebb->base_url}profile={$u['id']}'>{$u['username']}</a>{$link}</li>

EOF;
		
		return $code;
	}
}
?>