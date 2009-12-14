<?php
require('global.php');

class skin_settings
{
	function settings()
	{
		global $icebb;
		
		//$this->global		= new skin_global;
	}

	function display($group,$settings)
	{
		global $icebb;
		
		$code				= skin_global::header();
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>{$group['st_title']}</h3>
	<form action='index.php' method='post'>
	<input type='hidden' name='s' value='{$icebb->adsess['asid']}' />
	<input type='hidden' name='act' value='settings' />
	<input type='hidden' name='group' value='{$group['st_id']}' />
	<table width='100%' cellpadding='2' cellspacing='1'>

EOF;

		foreach($settings as $s)
		{
			$code		   .= <<<EOF
		<tr>
			<td width='40%' class='row2'>
				<strong>{$s['setting_title']}</strong>
				<div class='desc'>{$s['setting_desc']}</div>
			</td>
			<td width='60%' class='row1'>
				{$s['input']}
			</td>
		</tr>

EOF;
		}
		
		$code			   .= <<<EOF
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='save' value='Save Changes' class='button' />
	</div>
	</form>
</div>

EOF;
		$code			   .= skin_global::footer();
		
		return $code;
	}
}
?>