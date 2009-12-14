<?php
require('global.php');

class skin_langs
{
	function skin_langs()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($langs=array())
	{
		global $icebb,$config;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
<h3>{$icebb->lang['langs']}</h3>
<form action='{$icebb->base_url}act=langs' method='post' name='langfrm'>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($langs as $l)
		{
			if(!$l['enabled'])
			{
				$l['options'][]= "<a href='{$icebb->base_url}act=langs&amp;code=enable&amp;lang={$l['lang_short']}'>{$icebb->lang['enable']}</a>";
				$l['defaulthtml']= " disabled='disabled'";
			}
			else {
				if($l['lang_short']!='en' && $l['lang_is_default']!=1)
				{
					$l['options'][]= "<a href='{$icebb->base_url}act=langs&amp;code=disable&amp;langid={$l['lang_id']}'>{$icebb->lang['disable']}</a>";
				}
				if($l['lang_is_default'] == 0)
				{
					$l['options'][] = "\t\t\t\t<a href='{$icebb->base_url}act=langs&amp;code=set_as_default&amp;langid={$l['lang_id']}'>{$icebb->lang['set_as_default']}</a>\n";
				}
			}
			
			if(count($l['options']))
 			{
				$l['options'] = join(" &middot; ",$l['options']);
			}
			
			if($l['lang_is_default']=='1')
			//if($l['lang_short']==$config['lang'])
			{
				$l['defaulthtml']= " checked='checked'";
			}
			
			$code		   .= <<<EOF
	<tr>
		<td class='row1' width='1%'>
			<input type='radio' name='default_lang'{$l['defaulthtml']} value='{$l['lang_id']}' class='form_radio' onclick='document.langfrm.submit();return false' />
		</td>
		<td class='row2'>
			<strong>{$l['lang_name']}</strong>
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			{$l['options']}
		</td>
	</tr>

EOF;
		}

		$code			   .= <<<EOF
</table>
</form>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
}
?>