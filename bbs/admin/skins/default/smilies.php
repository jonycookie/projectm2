<?php
require('global.php');

class skin_smilies
{
	function skin_smilies()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($smilies=array(),$default_smiley_set='default')
	{
		global $icebb;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
<h3>{$icebb->lang['smiley_sets']}</h3>
<form action='{$icebb->base_url}act=smilies' method='post' name='smiliesfrm'>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($smilies as $s)
		{
			if($default_smiley_set == $s['smiley_set'])
			{
				$s['defaulthtml']= " checked='checked'";
			}
			else {
				$s['defaulthtml']= "";
			}
		
			$code		   .= <<<EOF
	<tr>
		<td class='row1' width='1%'>
			<input type='radio' name='default_smilies'{$s['defaulthtml']} value='{$s['smiley_set']}' class='form_radio' onclick='document.smiliesfrm.submit();return false' />
		</td>
		<td class='row2'>
			<strong>{$s['smiley_set']}</strong>
		</td>
		<td class='row1' width='30%' style='text-align:right'>
			<a href='{$icebb->base_url}act=smilies&amp;func=manage&amp;set={$s['smiley_set']}'>{$icebb->lang['manage']}</a> &middot;
			<a href='{$icebb->base_url}act=smilies&amp;func=delete&amp;set={$s['smiley_set']}' onclick='javascript:if(!confirm("{$icebb->lang['confirm_remove']}")) return false;'>{$icebb->lang['remove']}</a>
		</td>
	</tr>

EOF;
		}

		$code			   .= <<<EOF
	<tr>
		<td class='row3' colspan='3' style='text-align:center'>
			<a href='{$icebb->base_url}act=smilies&amp;func=add_folder'>{$icebb->lang['add_new_set']}</a>
		</td>
	</tr>
</table>
</form>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
	
	function show_set($smilies=array(),$images=array())
	{
		global $icebb;

		$set				= sprintf($icebb->lang['set_title'],$icebb->input['set']);

		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
<h3>{$set}</h3>
<form action='{$icebb->base_url}' method='post'>
<input type='hidden' name='act' value='smilies' />
<input type='hidden' name='func' value='manage' />
<input type='hidden' name='set' value='{$icebb->input['set']}' />
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
	<tr>
		<th width='45%'>Code</th>
		<th width='50%'>Image</th>
		<th width='5%'>Clickable?</th>
	</tr>

EOF;
	
		foreach($smilies as $s)
		{
			// generate select list
			$image_options		= "";
			foreach($images as $i)
			{
				if($i == $s['filename'])
				{
					$image_options.= "\t\t\t\t<option value='{$i}' selected='selected'>{$i}</option>\n";
				}
				else {
					$image_options.= "\t\t\t\t<option value='{$i}'>{$i}</option>\n";
				}
			}
	
			$clickable		= $s['clickable'] ? "<input type='checkbox' name='clickable[{$s['id']}]' value='1' checked='checked' />" : "<input type='checkbox' name='clickable[{$s['id']}]' value='1' />";

			$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<input type='text' name='smiley_code[{$s['id']}]' value="{$s['code']}" class='textbox' size='30' />
		</td>
		<td class='row1'>
			<select name='image[{$s['id']}]' class='dropdown' onchange="$('smiley-{$s['id']}').src='{$icebb->settings['board_url']}smilies/{$icebb->input['set']}/'+this.options[this.selectedIndex].value;return false">
{$image_options}
			</select>
		
			<img src='{$icebb->settings['board_url']}smilies/{$icebb->input['set']}/{$s['filename']}' alt='{$s['code']}' title="{$s['code']}" id='smiley-{$s['id']}' />
		</td>
		<td class='row2'>
			{$clickable}
		</td>
	</tr>

EOF;
			}
			
			////////////////////////////////////////////////////////
			// Blank row
			////////////////////////////////////////////////////////
			// generate select list
			$image_options		= "";
			foreach($images as $i)
			{
				$image_options.= "\t\t\t\t<option value='{$i}'>{$i}</option>\n";
			}
	
			$code		   .= <<<EOF
	<tr>
		<td class='row2'>
			<input type='text' name='smiley_code[new]' value='' class='textbox' size='30' />
		</td>
		<td class='row1'>
			<select name='image[new]' class='dropdown' onchange="$('smiley-new').src='{$icebb->settings['board_url']}smilies/{$icebb->input['set']}/'+this.options[this.selectedIndex].value;return false">
{$image_options}
			</select>
		
			<img src='{$icebb->settings['board_url']}smilies/{$icebb->input['set']}/blank.gif' alt='' title="" id='smiley-new' />
		</td>
		<td class='row2'>
			<input type='checkbox' name='clickable[new]' value='1' />
		</td>
	</tr>
	<tr>
		<td class='row3' colspan='3' style='text-align:center'>
			<input type='submit' name='submit' value='{$icebb->lang['save_changes']}' class='button' />
		</td>
	</tr>
</table>
</form>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
}
?>
