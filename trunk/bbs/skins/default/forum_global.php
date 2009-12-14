<?php
class skin_forum_global
{

function forum_category($r,$inner_forums)
{
global $icebb;

$code .= <<<EOF

<div class='borderwrap' style="margin-bottom: 10px;">
<h2><{CAT_ICON}><a href='{$icebb->base_url}forum={$r['fid']}'>{$r['name']}</a></h2>

<table width="100%" border="0" cellspacing="1" cellpadding="2" style='margin-top:-1px'>
	<tr>
		<th colspan="2" width="60%">
			{$icebb->lang['forum_name']}
		</th>
		<th width="7%" style='text-align:center'>
			{$icebb->lang['topics']}
		</th>
		<th width="7%" style='text-align:center'>
			{$icebb->lang['replies']}
		</th>
		<th width="26%">
			{$icebb->lang['last_post']}
		</th>
	</tr>
EOF;

foreach($inner_forums as $f)
{
	$f['description']		= html_entity_decode($f['description']);

	if(empty($f['redirecturl']))
	{
		$code .= $this->forum($f,$f['marker']);
	}
	else {
		$code .= $this->forum_redirect($f);
	}
}

$code .= <<<EOF
</table>
<div class="catEnd"><!-- End of Category --></div>
</div>


EOF;

return $code;
}


function forum($r,$macro='f_nonew')
{
global $icebb;

if($macro=='f_new')
{
	$macro = "<a href='{$icebb->base_url}forum={$r['fid']}&amp;make_read=1' onclick='s=openURL(this.href);if(s){this.parentNode.innerHTML=marker_nonew;return false}'><macro:f_new /></a>";
}
else {
	$macro = "<macro:f_nonew />";
}

if($icebb->settings['use_trash_can'] && $icebb->settings['trash_can_forum']==$r['fid'])
{
	$macro = "<macro:f_nonew />";
	
	$r['topics']				= '-';
	$r['replies']				= '-';
	$r['lastpost_time_formatted']= "(Trash can)";
	$r['lastpostid']			= '';
	$r['lastpost_title']		= '';
	$r['lastpost_authorid']		= '';
	$r['lastpost_author']		= '';
}

if(is_array($r['subforums']))
{
	foreach($r['subforums'] as $sf)
	{
		$subfs[]				= "<a href='{$icebb->base_url}forum={$sf['fid']}'>{$sf['name']}</a>";
	}
	
	$subforums					= "<em class='desc subforums' style='display:block'>{$icebb->lang['subforums']}".implode(', ',$subfs)."</em>"; 
}

if(is_array($r['moderators']))
{
	foreach($r['moderators'] as $m)
	{
		$mods[]				= "<a href='{$icebb->base_url}profile={$m['muserid']}'>{$m['muser']}</a>";
	}
	
	$moderators				= "<em class='desc moderators' style='display:block'>{$icebb->lang['moderators']}".implode(', ',$mods)."</em>"; 
}

$code .= <<<EOF
	<tr>
		<td width='3%' class="row1">{$macro}</td>
		<td class="row1">
			<strong><a href='{$icebb->base_url}forum={$r['fid']}'>{$r['name']}</a></strong> <br />
			<span class='desc'>{$r['description']}</span>
			{$subforums}
			{$moderators}
		</td>
		<td class="row2" style='text-align:center'>{$r['topics']}</td>
		<td class="row2" style='text-align:center'>{$r['replies']}</td>
		<td class='row2'>
			<div class="small-light">{$r['lastpost_time_formatted']}</div>
			<strong>{$icebb->lang['post_in']}</strong> <img src='skins/<#SKIN#>/images/arrow_right.png' alt='' /><a href='{$icebb->base_url}topic={$r['lastpostid']}&amp;show=newpost' title="{$icebb->lang['newpost_goto']}">{$r['lastpost_title']}</a><br />
			<strong>{$icebb->lang['post_by']}</strong> <a href='{$icebb->base_url}profile={$r['lastpost_authorid']}'>{$r['lastpost_author']}</a>
		</td>
	</tr>


EOF;

return $code;
}

function forum_redirect($r)
{
global $icebb;

$redirect = sprintf($icebb->lang['redirect_hits'],$r['redirect_hits']);

$code .= <<<EOF
     <tr>
    <td width='3%' class="row1"><{F_REDIRECT}></td>
    <td class="row1">
		<strong><a href='{$icebb->base_url}forum={$r['fid']}'>{$r['name']}</a></strong><br />
		<span class='desc'>{$r['description']}</span>
	</td>
    <td class="row2" style='text-align:center'>-</td>
    <td class="row2" style='text-align:center'>-</td>
    <td class="row2" style='text-align:center'>{$redirect}</td>
  </tr>


EOF;

return $code;
}

function forum_moderators($mods)
{
global $icebb;

$code .= <<<EOF
<div class='desc' style='font-style:italic'><strong>{$icebb->lang['moderators']}</strong> {$mods}</div>

EOF;

return $code;
}

function forum_moderators_mod($m)
{
global $icebb;

$code .= <<<EOF
{$m['before']}<a href='{$icebb->base_url}profile={$m['muserid']}'>{$m['muser']}</a>
EOF;

return $code;
}

function moderator_tick_perforum($tid,$checked=0)
{
global $icebb;

$this->tick_count++;

if($checked)
{
	$check			= " checked='checked'";
}

$code .= <<<EOF
	<input type='checkbox' name='checkedtids[{$this->tick_count}]' value='{$tid}' title="{$icebb->lang['select_topic_mod']}"{$check} />
	<!--a href='#' onclick="return open_menu(this)" id='topicm-{$tid}'><img src='{$icebb->settings['board_url']}skins/<#SKIN#>/images/check_menu.gif' alt='' style='padding-top:1px' name='checkmenu{$tid}' /></a><br />

	<div class='border lightpadded menu' id='topicm-{$tid}-menu' style='width:150px;text-align:left;display:none'>
		<ul>
			<li><a href='#' onclick="return _mod_edit_ttitle('{$tid}')">Edit Topic Title</a></li>
			<li><a href='#' onclick="alert('not done');return false;return _mod_edit_tdesc('{$tid}')">Edit Topic Description</a></li>
		</ul>
	</div-->
EOF;

return $code;
}

function subforums_bottom()
{
global $icebb;

$code .= <<<EOF

<br />

EOF;

return $code;
}

function forum_view($subforums,$topics,$onlineusers)
{
global $icebb;

$code .= <<<EOF
<script type='text/javascript'>
check_on="{$icebb->settings['board_url']}skins/<#SKIN#>/images/check_on.gif";
check_off="{$icebb->settings['board_url']}skins/<#SKIN#>/images/check_off.gif";
</script>
<script type='text/javascript' src='jscripts/forum.js'></script>
{$subforums}
{$topics}
{$onlineusers}

EOF;

return $code;
}

function topic_listing($forum,$topics_pinned,$topics,$sortme,$pagelinks,$users_viewing,$is_fav,$announcements)
{
global $icebb;

if(empty($topics_pinned) && empty($topics))
{
	$topics				= <<<EOF
	<tr>
		<td colspan='7' class='row1' style='text-align:center'>
			<em>{$icebb->lang['no_topics_found']}</em>
		</td>
	</tr>

EOF;
}

$code .= <<<EOF

<a href='{$icebb->base_url}act=post&amp;forum={$forum['fid']}'><{NEW_TOPIC}></a>
{$pagelinks}


<div class="borderwrap">
	<h2>{$forum['name']}</h2>
	<form action='{$icebb->base_url}' name='forum_frm' method='post'>
	<table width="100%"  border="0" cellspacing="1" cellpadding="1" style='margin-top:-1px'>
		<tr>
			<th width="1%">&nbsp;</th>
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

$code .= <<<EOF
		</tr>
{$announcements}
{$topics_pinned}
{$topics}
{$users_viewing}
		<tr>
			<td colspan='8' class="row3">
				<span style="float:right"><!--MODERATOR.OPTIONS--></span>
				<!-- I know, but this is the only way to get it to work: -->
				<form action='{$icebb->base_url}' method='post'>
					<input type='hidden' name='forum' value='{$forum['fid']}' />
					{$icebb->lang['order_by']}
					<select name='order_by' class='form_dropdown'>
						{$sortme['order_by']}
					</select>
					{$icebb->lang['order_in']}
					<select name='sort_order' class='form_dropdown'>
						{$sortme['sort_order']}
					</select>
					{$icebb->lang['order_from']}
					<select name='startdate' class='form_dropdown'>
						{$sortme['startdate']}
					</select>
		
					<input type='submit' value="{$icebb->lang['order_button']}" class='form_button' />
				</form>
			</td>
		</tr>
	</table>
	</form>
</div>
	
<span style='float:left;padding:2px 0px'>
	{$pagelinks}
</span>

<span style="float:right"><a href='{$icebb->base_url}act=post&amp;forum={$forum['fid']}'><{NEW_TOPIC}></a></span><br />
</form>

<div style='clear:both'><!-- --></div>

<!-- Legends -->
<div class="borderwrap">
<table cellspacing="0" cellpadding="0" width='100%'>
	<tr>
		<td class="row3" valign="top" width="35%" colspan='2'>
			<strong>{$icebb->lang['forum_legend']}</strong>
		</td>
		<td class="row3" valign="top" width="25%">
			<strong>{$icebb->lang['permissions']}</strong>
		</td>
		<td class="row3" valign="top" width="40%">
			<strong>{$icebb->lang['forum_options']}</strong>
		</td>
	</tr>
	<tr>
		<td class="row1" valign="top">
			<macro:t_new /> {$icebb->lang['new_topic']}<br />
			<macro:t_nonew /> {$icebb->lang['no_new_topic']}<br />
			<macro:t_locked /> {$icebb->lang['locked_topic']}
		</td>
		<td class="row1" valign="top">
			<macro:t_hotnew /> {$icebb->lang['new_hot_topic']}<br />
			<macro:t_hot /> {$icebb->lang['hot_topic']}
		</td>
		<td class="row1" valign="top" width="25%">
{$forum['permissions_are_a_huge_pain_in_the_ass']}
		</td>
		<td class="row1" valign="top" width="50%">
			<a href='{$icebb->base_url}forum={$forum['fid']}&amp;subscribe=1'>{$icebb->lang['subscribe_to_forum']}</a><br />

EOF;
if($is_fav)
{
	$code .= <<<EOF
			<a href='{$icebb->base_url}act=ucp&amp;func=favorites&amp;opt=delete&amp;type=forum&amp;id={$forum['fid']}'>{$icebb->lang['fav_remove']}</a><br />

EOF;
}
else {
	$code .= <<<EOF
			<a href='{$icebb->base_url}forum={$forum['fid']}&amp;favorite=1'>{$icebb->lang['fav_add']}</a><br />

EOF;
}
$code .= <<<EOF
			<a href='rss.php?forum={$forum['fid']}'>{$icebb->lang['rss_feed_forum']} <macro:rss_icon /></a><br /> 
		</td>
	</tr>
	<tr>
		<td class='row3' colspan='3'>
			<a name='search_forum'></a>
			<form action='index.php' method='post' name='searchf_frm'>
				<input type='hidden' name='act' value='search' />
				<input type='hidden' name='func' value='results' />
				<input type='hidden' name='search_forums' value='{$forum['fid']}' />
				<input type='text' id='searchy' name='q' value='{$icebb->lang['search_forum']}' onclick="if(this.value=='{$icebb->lang['search_forum']}') this.value=''" class='form_textbox small' />
				<input type='submit' value='{$icebb->lang['go']}' class='form_button small' style='display:none' />
			</form>
		</td>
		<td class='row3' style='text-align:right'>
			<a href='{$icebb->base_url}forum={$forum['fid']}&amp;go=prev'>{$icebb->lang['prev_forum']}</a>
			&middot; <a href='{$icebb->base_url}forum={$forum['fid']}&amp;go=next'>{$icebb->lang['next_forum']}</a>
		</td>
	</tr>
</table>
<div style='clear:both'><!-- --></div>
</div>



EOF;

return $code;
}

function topic_row($r,$marker='<macro:t_nonew />')
{
global $icebb;

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
