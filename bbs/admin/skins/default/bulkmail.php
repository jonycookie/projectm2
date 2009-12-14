<?php
require('global.php');

class skin_bulkmail
{
	function skin_bulkmail()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($messages=array())
	{
		global $icebb;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
<h3>{$icebb->lang['bulkmail']}</h3>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
</table>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
	
	function show_create($groups=array())
	{
		global $icebb;
		
		foreach($groups as $g)
		{
			if($g['gid']=='4') continue;
		
			$group_html	   .= "<label><input type='checkbox' name='group[{$g['gid']}]' value='1' checked='checked' /> {$g['g_title']}</label><br />";
		}
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>{$icebb->lang['create_bulkmail']}</h3>
	<form action='{$icebb->base_url}act=bulkmail' method='post'>
	<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
		<tr>
			<td class='row2' width='40%'>
				<strong>{$icebb->lang['subject']}</strong>
			</td>
			<td class='row1'>
				<input type='text' name='subject' value='' size='45' class='textbox' />
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['message']}</strong>
			</td>
			<td class='row1'>
				<textarea name='message' rows='10' cols='60'></textarea>
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['send_to']}</strong>
			</td>
			<td class='row1'>
{$group_html}
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['options']}</strong>
			</td>
			<td class='row1'>
				<label><input type='checkbox' name='respect' value='1' checked='checked' /> Respect "enable admin e-mail" setting?</label>
			</td>
		</tr>
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='submit' value='Send' class='button' />
	</div>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
}
?>