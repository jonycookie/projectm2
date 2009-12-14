<?php
require('global.php');

class skin_help
{
	function skin_help()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

	function help_page($sections,$help,$topics)
	{
		global $icebb,$global;

		foreach($sections as $k => $s)
		{
			if(!is_array($help[$k])) continue;
		
			$bleh			= array();
			foreach($help[$k] as $h)
			{
				$bleh[]		= "\t\t\t<li><a href='{$icebb->base_url}act=help&amp;title={$h['hname_']}'>{$h['hname']}</a></li>";
			}
			$bleh			= implode("\n",$bleh);
			
			$sect[]			= <<<EOF
	<div class='row2 help-section' id='sect-{$k}' style='padding-bottom:4px'>
		<div class='Subtitle'>{$s['title']}</div>
		<ul>
{$bleh}
		</ul>
	</div>

EOF;
		}
		
		$list				= implode("\n",$sect);
		//$ha					= implode("\n",$topics);

		$code				= $global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['help']}</h2>
{$list}
</div><br />

{$ha}

EOF;

		$code			   .= $global->footer();

		return $code;
	}

	function help_topic($h)
	{
		global $icebb,$global;

		$code				= $global->header();
		$code .= <<<EOF
<a name='{$h['hid']}'></a>
<div class='borderwrap'>
	<h2>{$h['hname']}</h2>
	<div class='row2' style='padding:2px'>
{$h['htext']}
	</div>
	<div class='Subtitle'><a href='{$icebb->base_url}act=help'>{$icebb->lang['back']}</a></div>
</div><br />

EOF;
		$code			   .= $global->footer();

		return $code;
	}
}
?>