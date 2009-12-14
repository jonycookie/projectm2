<?php
require('global.php');

class skin_members
{
	function skin_members()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function memberlist($members,$pagelinks,$columns=array())
{
global $icebb,$global;

if(empty($members))
{
	$members	= "<tr><td colspan='5' class='row2' style='text-align:center'><em>{$icebb->lang['none_found']}</em></td></tr>\n";
}

$code	= $global->header();
$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['member_list']}</h2>
	<table width="100%" cellspacing="1" cellpadding="1" style='margin-top:-1px'>
		<tr> 
			<th width="34%">{$icebb->lang['name']}</th>
			<th width="12%" style='text-align:center'>{$icebb->lang['group']}</th>
			<th width="10%" style='text-align:center'>{$icebb->lang['joined']}</th>
			<th width="5%" style='text-align:center'>{$icebb->lang['posts']}</th>
			<th width="6%" style='text-align:center'>{$icebb->lang['email']}</th>
		</tr>
{$members}
	</table>
</div>

{$pagelinks}<br />

<div class='borderwrap'>
	<h2>{$icebb->lang['member_search']}</h2>
	<form action='{$icebb->base_url}act=members' method='post'>
	<table width='100%' cellpadding='2' cellspacing='1'>
EOF;

foreach($columns as $col => $val)
{

$code .= <<<EOF
		<tr>
			<td class='row2' width='40%'>
				<strong>{$icebb->lang['searchby_'.$col]}</strong>
			</td>
			<td class='row1'>

EOF;

switch($col)
{
	case 'user_group':
		$code .= "<select name='user_group' class='form_dropdown'>\n";
		$code .= "\t<option value='0'>{$icebb->lang['any']}</option>\n";

		foreach($icebb->cache['groups'] as $g)
		{
			if($val[1]==$g['gid'])
			{
				$g['sel']= " selected='selected'";
			}

			$code .= "\t<option value='{$g['gid']}'{$g['sel']}>{$g['g_title']}</option>";
		}

		$code .= "</select>\n";
		break;
	case 'gender':
		switch($val[1])
		{
			case 'm':
				$m		= " selected='selected'";
				break;
			case 'f':
				$f		= " selected='selected'";
				break;
			case 'u':
				$u		= " selected='selected'";
				break;
		}
	
		$code .= <<<EOF
<select name='gender' class='form_dropdown'>
	<option value=''>{$icebb->lang['any']}</option>
	<option value='m'{$m}>{$icebb->lang['male']}</option>
	<option value='f'{$f}>{$icebb->lang['female']}</option>
	<option value='u'{$u}>{$icebb->lang['unspecified']}</option>
</select>

EOF;
		break;
	default:
		$code .= <<<EOF
				<select name='{$col}_filter' class='form_dropdown'>
					<option value='startswith'>{$icebb->lang['filter_starts']}</option>
					<option value='contains'>{$icebb->lang['filter_contains']}</option>
					<option value='is'>{$icebb->lang['filter_is']}</option>
				</select>
				<input type='text' name='{$col}' value='{$val[1]}' class='form_textbox' />

EOF;
		break;
}
			
$code .= <<<EOF
			</td>
		</tr>

EOF;
}

$code .= <<<EOF
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='search' value="{$icebb->lang['search']}" class='form_button' />
	</div>
	</form>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function user_row($u)
{
global $icebb;

$code .= <<<EOF
	<tr> 
		<td class="row2" width="34%"><a href='{$icebb->base_url}profile={$u['id']}'>{$u['username']}</a><br />{$u['title']}</td>
		<td class="row2" width="12%" style='text-align:center'>{$u['g_title']}</td>
		<td class="row1" width="17%" style='text-align:center'>{$u['MemberJoined']}</td>
		<td class="row1" width="10%" style='text-align:center'>{$u['posts']}</td>
		<td class="row2" width="13%" style='text-align:center'><a href="{$icebb->base_url}profile={$u['id']}&amp;func=mail"><img src='skins/<#SKIN#>/images/email.gif' alt='{$icebb->lang['email_img_alt']}' /></a></td>
	</tr>

EOF;

return $code;
}

}
?>
