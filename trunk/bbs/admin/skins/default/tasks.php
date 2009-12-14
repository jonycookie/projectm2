<?php
require('global.php');

class skin_tasks
{
	function skin_tasks()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($tasks=array())
	{
		global $icebb,$config;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<p>{$icebb->lang['tasks_description']}</p>

<div class='borderwrap'>
<h3>{$icebb->lang['tasks']}</h3>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($tasks as $t)
		{
			if(!$t['task_enabled'])
			{
				$t['options']= "<a href='{$icebb->base_url}act=tasks&amp;func=enable&amp;tid={$t['taskid']}'>{$icebb->lang['enable']}</a>\n";
			}
			else {
				$t['options']= "<a href='{$icebb->base_url}act=taskss&amp;func=disable&amp;tid={$t['taskid']}'>{$icebb->lang['disable']}</a>\n";
			}
		
			$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<strong>{$t['task_name']}</strong><br />
			{$t['task_desc']}
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			{$t['options']}
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