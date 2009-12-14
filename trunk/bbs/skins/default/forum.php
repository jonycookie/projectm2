<?php
require('global.php');
require('forum_global.php');

class skin_forum extends skin_forum_global
{
	function skin_forum()
	{
		global $icebb,$global,$forum_global;
	
		$global				= new skin_global;
	}

	function forum_view($an, $nforums,$topics,$users_viewing)
	{
		global $icebb,$global;
		
		$code				= $global->header();
		$code			   .= "<script type='text/javascript' src='jscripts/forum.js'></script>\n";
		
		$code              .= $an;
		
		if(is_array($nforums))
		{
			$code		   .= $this->forum_category($nforums[0][0],$nforums[0][1],$users_viewing);
		}
		

		$code			   .= <<<EOF
		
{$topics}

EOF;
		$code			   .= $global->footer();
		
		return $code;
	}

function subforums_top()
{
global $icebb;

$code .= <<<EOF
<script type='text/javascript'>
// <![CDATA[
marker_nonew="<macro:f_nonew />";
// ]]>
</script>

EOF;

return $code;
}

function announce($title, $text) 
{
$code .= "<table width='100%'><tr><th>{$title}</th></tr><tr><td class='row1'>{$text}</td></tr></table>"; 

	return $code;
}

function topic_row($r,$marker='<macro:t_nonew />')
{
global $icebb;

if($marker=='<macro:t_new />')
{
	$marker			= "<a href='{$icebb->base_url}topic={$r['tid']}&amp;show=newpost' title='{$icebb->lang['go_most_recent']}'>{$marker}</a>";
}
else if($marker=='<macro:t_hotnew />')
{
	$marker			= "<a href='{$icebb->base_url}topic={$r['tid']}&amp;show=newpost' title='{$icebb->lang['go_most_recent']}'>{$marker}</a>";
}

$code .= <<<EOF
  <tr>
    <td width="1%" class="row1">{$marker}</td>
    <td width="1%" class="row1">{$r['post_icon']}</td>
			<td width="49%" class="row1">
				<div>{$r['prepend']}<span id='topic-title-{$r['tid']}'><a href='{$icebb->base_url}topic={$r['tid']}' title="{$r['snippet']}">{$r['title']}</a></span>{$r['append']}</div>
					<span class='desc' id='topic-desc-{$r['tid']}'>{$r['description']}</span></td>

EOF;
if(empty($r['starter_id']))
{
$code .= <<<EOF
			<td width="14%" style='text-align:center' class="row1">{$r['starter']}</td>

EOF;
}
else {
$code .= <<<EOF
			<td width="14%" style='text-align:center' class="row1"><a href="{$icebb->base_url}profile={$r['starter_id']}" title="View profile of {$r['starter']}">{$r['starter']}</a></td>

EOF;
}
$code .= <<<EOF
			<td width="7%" style='text-align:center' class="row2">{$r['replies']}</td>
			<td width="7%" style='text-align:center' class="row2">{$r['views']}</td>
			<td width="21%" class="row2">
				<div class='small-light'><img src='skins/<#SKIN#>/images/paper.png' alt='' /> {$r['lastpost_time_formatted']}</div>

EOF;
if(empty($r['lastpost_author_id']))
{
$code .= <<<EOF
				{$r['lastpost_author']} <a href='{$icebb->base_url}topic={$r['tid']}&amp;show=lastpost'>&raquo;</a>

EOF;
}
else {
$code .= <<<EOF
				<a href="{$icebb->base_url}profile={$r['lastpost_author_id']}" title="{$icebb->lang['view_profile_of']} {$r['starter']}">{$r['lastpost_author']}</a> <a href='{$icebb->base_url}topic={$r['tid']}&amp;show=lastpost'>&raquo;</a>

EOF;
}

$code .= <<<EOF
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

function moderator_options($topic,$links)
{
global $icebb;

$code .= <<<EOF

<input type='hidden' name='act' value='moderate' />
<select name='func' onchange="if(this.selectedIndex!=0) document.forum_frm.submit()" class='form_dropdown'>
	<option value='--' selected='selected' style='font-weight:bold'>{$icebb->lang['mod_options']}</option>
	<option disabled='disabled' class='optgroup'>{$icebb->lang['mod_selected_topics']}</option>
{$links['topic']}
</select>
<script type='text/javascript'>
<!--
document.forum_frm.func.selectedIndex=0;
//-->
</script>

EOF;

return $code;
}

function moderator_options_addlink($value,$text)
{
global $icebb;

$code .= <<<EOF
        <option value='{$value}'>{$text}</option>

EOF;

return $code;
}

function password_box($f,$tid='')
{
global $icebb,$global;

$code = $global->header();
$code .= <<<EOF
<div class='borderwrap'>
<div class="row2">
	<h2>{$icebb->lang['pass_protected_title']}</h2>
	<div class="row1" style="padding: 3px">
		<div class="highlight_error">
			{$icebb->lang['pass_protected']}
		</div>
	</div>
	
	<form action='{$icebb->base_url}' method='post'>
EOF;

if(!empty($tid))
{

$code .= <<<EOF
		<input type='hidden' name='topic' value='{$tid}' />

EOF;

}
else {

$code .= <<<EOF
		<input type='hidden' name='forum' value='{$f['fid']}' />

EOF;

}
	
$code .= <<<EOF
		<div style='padding:5px'>
			<label>
				<strong>{$icebb->lang['password']}</strong>
				<input type='password' name='forum_password' value='' class='form_textbox' />
			</label>
		</div>
		
		<div class="row3" style="text-align:center;padding:4px">
			<input type='submit' value='{$icebb->lang['password_button']}' class='form_button' />
		</div>
	</div>
	</form>
</div>

EOF;
$code .= $global->footer();

return $code;
}

function users_viewing($num,$users)
{
global $icebb;

if(count($users)>0)
{
$users= implode(', ',$users);
}
else {
$users= '';
}

$viewing = sprintf($icebb->lang['viewing'],$num['total'],$num['guests']);

$code .= <<<EOF
	<tr>
		<td colspan='8' class="row3">
			<strong>{$viewing}</strong>
		</td>
	</tr>
	<tr>
		<td colspan='8' class="row2">
{$users}
		</td>
	</tr>

EOF;

return $code;
}

}
?>
