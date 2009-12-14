<?php
require('global.php');

class skin_skins
{
	function skin_skins()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_main($skins=array())
	{
		global $icebb;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
<h3>{$icebb->lang['skins']}</h3>
<form action='{$icebb->base_url}act=skins' method='post' name='skinfrm'>
<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>

EOF;

		foreach($skins as $s)
		{
			if(!$s['enabled'])
			{
				$s['options']= "<a href='{$icebb->base_url}act=skins&amp;func=enable&amp;skinfolder={$s['directory']}'>{$icebb->lang['enable']}</a>";
				$s['defaulthtml']= " disabled='disabled'";
			}
			else {
				if($s['skin_folder']!='default')
				{
					$s['options']= "\t\t\t\t<a href='{$icebb->base_url}act=skins&amp;func=disable&amp;skinid={$s['skin_id']}'>{$icebb->lang['disable']}</a><br />\n";
				}
			
				$s['options'].= <<<EOF
				<a href='{$icebb->base_url}act=skins&amp;func=css&amp;skinid={$s['skin_id']}'>{$icebb->lang['edit_css']}</a> &middot;
				<a href='{$icebb->base_url}act=skins&amp;func=wrapper&amp;skinid={$s['skin_id']}'>{$icebb->lang['edit_wrapper']}</a> &middot;
				<a href='{$icebb->base_url}act=skins&amp;func=templates&amp;skinid={$s['skin_id']}'>{$icebb->lang['edit_templates']}</a> &middot;
				<a href='{$icebb->base_url}act=skins&amp;func=macros&amp;skinid={$s['skin_id']}'>{$icebb->lang['edit_macros']}</a>

EOF;
			}
		
			if($s['skin_is_default']=='1')
			{
				$s['defaulthtml']= " checked='checked'";
			}
			
			if(!empty($s['skin_site']))
			{
				$s['skin_author']= "<a href='{$s['skin_site']}'>{$s['skin_author']}</a>";
			}
		
			$code		   .= <<<EOF
	<tr>
		<td class='row1' width='1%'>
			<input type='radio' name='default_skin'{$s['defaulthtml']} value='{$s['skin_id']}' class='form_radio' onclick='document.skinfrm.submit();return false' />
		</td>
		<td class='row2'>
			<strong>{$s['skin_name']}</strong><br />
			by {$s['skin_author']}
		</td>
		<td class='row1' width='40%' style='text-align:right'>
			{$s['options']}
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
	
	function file_editor($func,$name,$file_code,$extra=array())
	{
		global $icebb;
		
		if($extra['save']	== " disabled='disabled'")
		{
			$save			= "<div style='padding:3px'>{$icebb->lang['file_not_writeable']}</div>";
		}
		else {
			$save			= "<input type='submit' name='save' value='{$icebb->lang['save']}' class='button'{$extra['save']} /><br />";
		}
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<form action='{$icebb->base_url}act=skins' method='post'>
	<input type='hidden' name='func' value='{$func}' />
	<input type='hidden' name='skinid' value='{$icebb->input['skinid']}' />
	{$extra['hidden']}
	<textarea name='{$name}' cols='60' rows='20' class='monospaced' style='width:100%'{$extra['textarea']}>
{$file_code}
	</textarea>
	<div class='buttonrow'>
		{$save}
		<a href='{$icebb->base_url}act=skins&amp;skinid={$icebb->input['skinid']}&amp;func={$func}' style='padding:3px'>{$icebb->lang['cancel']}</a>
	</div>
</form>

EOF;
		$code			   .= $this->global->footer();

		return $code;
	}
	
	function template_list($templates)
	{
		global $icebb;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>{$icebb->lang['templates']}</h3>
	<table width='100%' cellpadding='2' cellspacing='1'>
EOF;

		foreach($templates as $t)
		{
			$t				= basename($t);
			$t1				= explode('.',$t);
			$t1				= $t1[0];
			
			if($t1			== 'skin_info')
			{
				continue;
			}
			
			$code		   .= <<<EOF
		<tr>
			<td class='row2'>
				<a href='{$icebb->base_url}act=skins&amp;func=templates&amp;skinid={$icebb->input['skinid']}&amp;code=edit&amp;template={$t1}'>{$t}</a>
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
	
	function macro_editor($macros)
	{
		global $icebb;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>{$icebb->lang['macros']}</h3>
	<form action='{$icebb->base_url}act=skins&amp;func=macros' method='post'>
	<input type='hidden' name='skinid' value='{$icebb->input['skinid']}' />
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<th width='40%'>
				String
			</th>
			<th>
				Replacement
			</th>
		</tr>

EOF;

		foreach($macros as $m)
		{
			$code		   .= <<<EOF
		<tr>
			<td class='row2'>
				{$m['string']}
			</td>
			<td class='row1'>
				<textarea name='replacement[{$m['id']}]' rows='1' cols='30' class='monospaced' style='width:80%'>{$m['replacement']}</textarea>
			</td>
		</tr>

EOF;
		}

		$code			   .= <<<EOF
	</table>
	<div class='buttonrow'>
		<input type='submit' name='save' value='{$icebb->lang['save']}' class='button' />
	</div>
	</form>
</div>

EOF;
		$code			   .= $this->global->footer();

		return $code;
	}
}
?>