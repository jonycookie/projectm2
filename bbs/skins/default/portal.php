<?php
if(!class_exists('skin_global')) require('global.php');

class skin_portal
{
	function skin_portal()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

function layout($left,$center,$right)
{
global $icebb;

$code = $this->global->header();
$code .= <<<EOF
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td width='25%' valign='top'>
			{$left}
		</td>
		<td width='50%' valign='top'>
			{$center}
		</td>
		<td width='25%' valign='top'>
			{$right}
		</td>
	</tr>
</table>

EOF;
$code .= $this->global->footer();

return $code;
}

function block($block)
{
global $icebb;

$code .= <<<EOF
<div class='border lightpadded' style='margin-bottom:6px'>
	<h4>{$block['block_title']}</h4>
	{$block['block_data']}
</div>

EOF;

return $code;
}

}
?>