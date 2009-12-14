<?php
require('global.php');

class skin_forums
{
	function skin_forums()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}
	
	function display($html)
	{
		global $icebb;
	
		$code				= $this->global->header();
		$code			   .= $html;
		$code			   .= $this->global->footer();

		return $code;
	}
	
	function list_forums($forums)
	{
		global $icebb;
		
		$code				= <<<EOF
<style type='text/css'>
.forum_block h3
{
	cursor: move;
}

/*.reorder-select
{
	display: none;
}

#reorder_button
{
	display: none;
}*/
</style>

<form action='{$icebb->base_url}' method='post'>
<input type='hidden' name='act' value='forums' />

<div id='reorder-root-forums' style='position:relative'>
{$forums}
</div>

<div class='buttonstrip'>
	<input type='submit' name='reorder' value='Reorder Categories' class='button' id='reorder_button' />
	<input type='button' value='New Forum' onclick="window.location='{$icebb->base_url}act=forums&amp;func=new'" class='button' />
</div>
</form>

<script type='text/javascript'>
// <![CDATA[
/*Sortable.create('reorder-root-forums ',
	{
		tag						: 'div',
		class					: 'forum_block',
		handle					: 'h3',
		onUpdate				: function()
		{
			options				= {
				method			: 'get',
				parameters		: Sortable.serialize('reorder-root-forums'),
				onSuccess		: function(r){
					alert(r.responseText);
				}
			};
			
			new Ajax.Request(icebb_base_url+'act=forums&reorder=1',options);
		}
	});*/
// ]]>
</script>

EOF;

		return $code;
	}
	
	function list_forums_forum($f,$children)
	{
		global $icebb;
		
		for($z=1;$z<=$f['total'];$z++)
		{
			if($z	== $f['on'])
			{
				$sort.= "<option value='{$z}' selected='selected'>{$z}</option>\n";
			}
			else {
				$sort.= "<option value='{$z}'>{$z}</option>\n";
			}
		}
		
		$code				= <<<EOF
<div class='borderwrap forum_block' id='forum_{$f['fid']}'>
	<h3 id='forum_{$f['fid']}-handle'><a href='{$icebb->base_url}act=forums&amp;func=children&amp;fid={$f['fid']}'>{$f['name']}</a></h3>
	<div class='Subtitle' style='text-align:right'>
		<a href='{$icebb->base_url}act=forums&amp;func=new&amp;parent={$f['fid']}'>New Forum</a> &middot; 
		<a href='{$icebb->base_url}act=forums&amp;func=duplicate&amp;fid={$f['fid']}'>Duplicate</a> &middot; 
		<a href='{$icebb->base_url}act=forums&amp;func=edit&amp;fid={$f['fid']}'>Edit</a> &middot; 
		<a href='{$icebb->base_url}act=forums&amp;func=del&amp;fid={$f['fid']}' onclick="return confirm('WARNING: This will PERMANENTLY DELETE this forum, its subforums, and all of the topics posted in those forums. Are you sure you want to continue?')">X</a>
		<span id='reorder-f{$f['fid']}' class='reorder-select'> &middot; 
		<select name='forum_sort[{$f['fid']}]'>
{$sort}
		</select></span>
	</div>
	<table width='100%' cellpadding='2' cellspacing='1'>
{$children}
	</table>
</div>


EOF;

		return $code;
	}
	
	function list_forums_forum_child($f,$children2)
	{
		global $icebb;
	
		if(is_array($this->moderators[$f['fid']]))
		{
			foreach($this->moderators[$f['fid']] as $m)
			{
				if(!empty($m['muser']))
				{
					$mods[]	= "<a href='{$icebb->base_url}act=forums&amp;func=mod&amp;code=edit&amp;mid={$m['mid']}'>{$m['muser']}</a>";
				}
			}
		}
	
		$mods[]				= "<a href='{$icebb->base_url}act=forums&amp;func=mod&amp;code=add&amp;fid={$f['fid']}'><em>Add a moderator</em></a>";
		$mods				= implode(', ',$mods);
		
		###
		
		if(is_array($children2))
		{
			foreach($children2 as $c)
			{
				$child[]	= "<a href='{$icebb->base_url}act=forums&amp;func=children&amp;fid={$c['fid']}'>{$c['name']}</a>";
			}
		}
		
		$child[]			= "<a href='{$icebb->base_url}act=forums&amp;func=new&amp;parent={$f['fid']}'><em>Add a subforum</em></a>";
		$subforums			= implode(', ',$child);
		
		###
	
		$code			   .= <<<EOF
		<tr>
			<td class='row1'>
				<a href='{$icebb->base_url}act=forums&amp;func=children&amp;fid={$f['fid']}' onclick="_toggle_view('settings-{$f['fid']}');try{_toggle_view('children-{$f['fid']}');}catch(e){};return false" style='text-decoration:none'>{$before}{$f['name']}</a>
			</td>
		</tr>
		<tr id='settings-{$f['fid']}' style='display:none'>
			<td class='row2' id='children-{$f['parent']}'>
				<div style='padding-left:24px'>
					<div style='margin-bottom:6px'>
						<a href='{$icebb->base_url}act=forums&amp;func=edit&amp;fid={$f['fid']}'>Edit</a> &middot;
						<a href='{$icebb->base_url}act=forums&amp;func=duplicate&amp;fid={$f['fid']}'>Duplicate</a> &middot;
						<a href='{$icebb->base_url}act=forums&amp;func=del&amp;fid={$f['fid']}'  onclick="return confirm('WARNING: This will PERMANENTLY DELETE this forum, its subforums, and all of the topics posted in those forums. Are you sure you want to continue?')">Remove</a>
					</div>
					<strong>Subforums:</strong> {$subforums}<br />
					<strong>Moderators:</strong> {$mods}
				</div>
			</td>
		</tr>
			
EOF;

		return $code;
	}
	
	function show_children($c,$children)
	{
		global $icebb;
		
		$code				= <<<EOF
<form action='{$icebb->base_url}' method='post'>
<input type='hidden' name='act' value='forums' />
<input type='hidden' name='func' value='children' />
<div class='borderwrap'>
	<h3>{$c['name']}</h3>
	<div class='Subtitle' style='text-align:right'>
		<a href='{$icebb->base_url}act=forums'>Up</a> &middot;
		<a href='{$icebb->base_url}act=forums&amp;func=edit&amp;fid={$c['fid']}'>Edit forum</a> &middot;
		<a href='{$icebb->base_url}act=forums&amp;func=new&amp;parent={$c['fid']}'>New subforum</a>
	</div>
	<table width='100%' cellpadding='2' cellspacing='1'>

EOF;

		if(is_array($children))
		{
			foreach($children as $r)
			{
				$sort		= '';
				for($z=1;$z<=count($children);$z++)
				{
					if($z	== $r['sort'])
					{
						$sort.= "<option value='{$z}' selected='selected'>{$z}</option>\n";
					}
					else {
						$sort.= "<option value='{$z}'>{$z}</option>\n";
					}
				}
			
				$code	   .= <<<EOF
		<tr>
			<td class='row1' width='60%'>
				<a href='{$icebb->base_url}act=forums&amp;func=children&amp;fid={$r['fid']}'>{$r['name']}</a>
			</td>
			<td class='row2' width='37%' style='text-align:right'>
				<a href='{$icebb->base_url}act=forums&amp;func=edit&amp;fid={$r['fid']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=forums&amp;func=del&amp;fid={$r['fid']}' onclick="return confirm('WARNING: This will PERMANENTLY DELETE this forum, its subforums, and all of the topics posted in those forums. Are you sure you want to continue?')">Remove</a>
			</td>
			<td class='row1'>
				<select name='forum_sort[{$r['fid']}]'>
{$sort}
				</select>
			</td>
		</tr>

EOF;
			}
		}

		$code			   .= <<<EOF
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='reorder' value='Reorder' class='button' />
	</div>
</div>
</form>

EOF;
		
		return $code;
	}
}
?>
