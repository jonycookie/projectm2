<?php
require('global.php');

class skin_plugins
{
	function skin_plugins()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($plugins=array())
	{
		global $icebb,$config;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
<h3>{$icebb->lang['plugins']}</h3>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($plugins as $p)
		{
			if(!$p['enabled'])
			{
				$p['options']= "<a href='{$icebb->base_url}act=plugins&amp;enable={$p['file']}'>{$icebb->lang['enable']}</a>\n";
			}
			else {
				$p['options']= "<a href='{$icebb->base_url}act=plugins&amp;disable={$p['file']}'>{$icebb->lang['disable']}</a>\n";
			}
			
			if(!empty($p['author_url']))
			{
				$p['author']= "<a href='{$p['author_url']}'>{$p['author']}</a>";
			}
		
			$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<strong>{$p['name']}</strong> {$p['version']}<br />
			by {$p['author']}
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			{$p['options']}
		</td>
	</tr>

EOF;
		}

		$code			   .= <<<EOF
</table>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
}
?>