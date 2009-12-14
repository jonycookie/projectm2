<?php
require('global.php');

class skin_boardrules
{
	function skin_boardrules()
	{
		global $global;
	
		$global				= new skin_global;
	}
	
	function rules_page($rules)
	{
		global $icebb,$global;

		$code				= $global->header();
		$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['boardrules']}</h2>
	<div class="row1" style="padding:5px">
		<div class="highlight_error">
{$rules}
		</div>
	</div>
</div>

EOF;
		$code			   .= $global->footer();

		return $code;
	}
}
?>
