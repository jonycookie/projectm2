<?php
if(!class_exists('skin_global')) require('global.php');
if(!class_exists('skin_forum_global')) require('forum_global.php');

class skin_tags extends skin_forum_global
{
	function skin_tags()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}
	
	function listall($tags)
	{
		global $icebb,$global;
		
		$code				= $global->header();
		$code			   .= <<<EOF
<div class='borderwrap lightpadded'>
	<h2>{$icebb->lang['all_tags']}</h2>
{$tags}
</div>

EOF;
		$code			   .= $global->footer();
		
		return $code;
	}
	
	function listall_tag($t,$t_url,$size)
	{
		global $icebb;
		
		$code				= <<<EOF
<a href='{$icebb->base_url}act=tags&amp;tag={$t_url}' style='font-size:{$size}px'>{$t['tag']}</a>

EOF;
		
		return $code;
	}
	
	function show_tag($info,$tag,$topics,$pagelinks)
	{
		global $icebb,$global;
		
		$title				= sprintf($icebb->lang['tag_viewing'],$tag['tag']);
		
		$code				= $global->header();
		$code			   .= <<<EOF
<script type='text/javascript' src='jscripts/forum.js'></script>
<div class='borderwrap'>
<h2>{$title}</h2>

<table width="100%"  border="0" cellspacing="1" cellpadding="1" style='margin-top:-1px'>
	<tr>
		<th width="1%">&nbsp;</th>
		<th width="49%">{$icebb->lang['topic_name']}</th>
		<th width="14%" style='text-align:center'>{$icebb->lang['started_by']}</th>
		<th width="7%" style='text-align:center'>{$icebb->lang['replies']}</th>
		<th width="7%" style='text-align:center'>{$icebb->lang['views']}</th>
		<th width="21%">{$icebb->lang['last_post']}</th>
			
EOF;

if($icebb->user['g_is_mod']=='1' || $icebb->is_mod_in_forum==1)
{

$code .= <<<EOF
		<th>&nbsp;</th>
EOF;

}

		$code			   .= <<<EOF
	</tr>
{$topics}
</table>
</div><br />

<div class='border'>
	<div class='subtitle'>
		<span style='float:right;font-weight:normal;margin:-2px 0px -3px 0px'>
			<!--MODERATOR.OPTIONS-->
		</span>
	</div>
	<div class='darkrow1' style='text-align:right'>
		<span style='float:left;padding:2px 0px'>
			{$pagelinks}
		</span>
	</div>
</div><br />

<div style='text-align:right'>
	<div class='align_left'>
		<a href='{$icebb->base_url}tag={$tag['tag']}&amp;subscribe=1'>{$icebb->lang['tag_subscribe']}</a>
	</div>
</div>

EOF;
		$code			   .= $global->footer();
		
		return $code;
	}
	
	function tag_row($r,$marker='<macro:t_nonew />')
	{
		global $icebb,$global;

$code .= <<<EOF
  <tr>
    <td width="1%" class="row1">{$marker}</td>
			<td width="49%" class="row1">
				<div>{$r['prepend']}{$r['post_icon']}<span id='topic-title-{$r['tid']}'><a href='{$icebb->base_url}topic={$r['tid']}' title="{$r['snippet']}">{$r['title']}</a></span>{$r['append']}</div>
					<span class='desc' id='topic-desc-{$r['tid']}'>{$r['description']}</span></td>
			<td width="14%" style='text-align:center' class="row2">{$r['starter']}</td>
			<td width="7%" style='text-align:center' class="row1">{$r['replies']}</td>
			<td width="7%" style='text-align:center' class="row2">{$r['views']}</td>
			<td width="21%" class="row1">
				<div class='small-light'>{$r['lastpost_time_formatted']}</div>
				{$r['lastpost_author']} <a href='{$icebb->base_url}topic={$r['tid']}&amp;show=lastpost'>&raquo;</a>
</td>

EOF;

if($icebb->user['g_is_mod']=='1' || $icebb->is_mod_in_forum==1)
{

$code .= <<<EOF
			<td class='row2' style='text-align:center'>
				<{MOD_OPTIONS}>
			</td>
EOF;

}

$code .= <<<EOF
		</tr>

EOF;

		return $code;
	}
}
?>