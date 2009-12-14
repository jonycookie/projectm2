<?php
require('global.php');

class skin_ban
{
	function skin_ban()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($ips=array(),$usernames=array(),$emails=array())
	{
		global $icebb,$config;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<p>{$icebb->lang['ban_description']}</p>

<form action='{$icebb->base_url}act=ban' method='post'>
<div class='borderwrap'>
<h3>{$icebb->lang['banned_ips']}</h3>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($ips as $ip)
		{
			if($ip['editing'])
			{
				$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<input type='hidden' name='ipedit' value='{$ip['bfid']}' />
			<input type='text' name='ip-{$ip['bfid']}' value='{$ip['value']}' class='textbox' />
			<input type='submit' name='ipeditn' value='Edit' class='button' />
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			&nbsp;
		</td>
	</tr>

EOF;
			}
			else {
				$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			{$ip['value']}
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			<a href='{$icebb->base_url}act=ban&amp;ipedit={$ip['bfid']}'>Edit</a> &middot;
			<a href='{$icebb->base_url}act=ban&amp;iprem={$ip['bfid']}'>Remove</a>
		</td>
	</tr>

EOF;
			}
		}

		$code			   .= <<<EOF
	<tr>
		<td class='row2' colspan='2'>
			<input type='text' name='ip' value='' class='textbox' />
			<input type='submit' name='ipadd' value='Add' class='button' />
		</td>
	</tr>
</table>
</div>

<div class='borderwrap'>
<h3>{$icebb->lang['banned_usernames']}</h3>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($usernames as $u)
		{
			if($u['editing'])
			{
				$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<input type='hidden' name='useredit' value='{$u['bfid']}' />
			<input type='text' name='user-{$u['bfid']}' value='{$u['value']}' class='textbox' />
			<input type='submit' name='usereditn' value='Edit' class='button' />
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			&nbsp;
		</td>
	</tr>

EOF;
			}
			else {
				$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			{$u['value']}
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			<a href='{$icebb->base_url}act=ban&amp;useredit={$u['bfid']}'>Edit</a> &middot;
			<a href='{$icebb->base_url}act=ban&amp;userrem={$u['bfid']}'>Remove</a>
		</td>
	</tr>

EOF;
			}
		}

		$code			   .= <<<EOF
	<tr>
		<td class='row2' colspan='2'>
			<input type='text' name='user' value='' class='textbox' />
			<input type='submit' name='useradd' value='Add' class='button' />
		</td>
	</tr>
</table>
</div>

<div class='borderwrap'>
<h3>{$icebb->lang['banned_emails']}</h3>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($emails as $e)
		{
			if($e['editing'])
			{
				$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<input type='hidden' name='eedit' value='{$e['bfid']}' />
			<input type='text' name='e-{$e['bfid']}' value='{$e['value']}' class='textbox' />
			<input type='submit' name='eeditn' value='Edit' class='button' />
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			&nbsp;
		</td>
	</tr>

EOF;
			}
			else {
				$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			{$e['value']}
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			<a href='{$icebb->base_url}act=ban&amp;eedit={$e['bfid']}'>Edit</a> &middot;
			<a href='{$icebb->base_url}act=ban&amp;erem={$e['bfid']}'>Remove</a>
		</td>
	</tr>

EOF;
			}
		}

		$code			   .= <<<EOF
	<tr>
		<td class='row2' colspan='2'>
			<input type='text' name='e' value='' class='textbox' />
			<input type='submit' name='eadd' value='Add' class='button' />
		</td>
	</tr>
</table>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
}
?>