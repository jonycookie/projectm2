<?php
if(!class_exists('skin_global')) require('global.php');

class skin_topic
{
	function skin_topic()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}
	
	
	function display($html)
	{
		global $icebb,$global;
		
		$code				= $global->header();
		$code			   .= $html;
		$code			   .= $global->footer();
		
		return $code;
	}

function poll($t,$question,$choices)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap' style='margin-bottom:10px'>
	<h2>{$icebb->lang['poll']}: {$question}</h2>
	<form action='index.php' method='post'>
	<input type='hidden' name='topic' value='{$t['tid']}' />
	<table width='100%' cellpadding='6' cellspacing='1'>
{$choices}
		<tr>
			<td colspan='2' class='buttonrow'>
				<input type='submit' name='vote' value='{$icebb->lang['vote_button']}' class='form_button' />
			</td>
		</tr>
	</table>
	</form>
</div>

EOF;

return $code;
}

function poll_choice($c)
{
global $icebb;

$code .= <<<EOF
		<tr>
			<td width='2%' class='row1' style='text-align:right'>
				<input type='radio' name='poll' value='{$c['cid']}' />
			</td>
			<td class='row2'>
				{$c['ctext']}
			</td>
		</tr>

EOF;

return $code;
}

function poll_choice_multi($c)
{
global $icebb;

$code .= <<<EOF
		<tr>
			<td width='2%' class='row1' style='text-align:right'>
				<input type='checkbox' name='poll[{$c['cid']}]' value='{$c['cid']}' />
			</td>
			<td class='row2'>
				{$c['ctext']}
			</td>
		</tr>

EOF;

return $code;
}

function poll_results($t,$question,$results)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap' style='margin-bottom:10px'>
	<h2>{$icebb->lang['poll']}: {$question}</h2>
	<table width='100%' cellpadding='6' cellspacing='1'>
{$results}
	</table>
</div>

EOF;

return $code;
}

function poll_result($c)
{
global $icebb;

$code .= <<<EOF
		<tr>
			<td class='row2' width='40%'>
				{$c['ctext']}
			</td>
			<td class='row1'>
				<img src='skins/<#SKIN#>/images/pip.png' height='12' width='{$c['percent']}%' alt='{$c['percent']}%' />
				<div style='font-size:80%'>{$c['votes']} votes</div>
			</td>
		</tr>

EOF;

return $code;
}

function topic_view($topic,$posts,$pagelinks='',$is_fav=0,$flood_control_remain=0)
{
global $icebb;

$code .= <<<EOF
<script type='text/javascript' src='jscripts/topic.js'></script>
<script type='text/javascript'>
// <![CDATA[
topic_rating	= {$topic['rating']};
curr_rating		= {$topic['rating']};
star_on			= "<macro:star />";
star_off		= "<macro:star_off />";

function update_stars(curr_rate)
{
	if(curr_rate			== 1)
	{
		_getbyid('star1').innerHTML= star_on;
		_getbyid('star2').innerHTML= star_off;
		_getbyid('star3').innerHTML= star_off;
		_getbyid('star4').innerHTML= star_off;
		_getbyid('star5').innerHTML= star_off;
	}
	else if(curr_rate		== 2)
	{
		_getbyid('star1').innerHTML= star_on;
		_getbyid('star2').innerHTML= star_on;
		_getbyid('star3').innerHTML= star_off;
		_getbyid('star4').innerHTML= star_off;
		_getbyid('star5').innerHTML= star_off;
	}
	else if(curr_rate		== 3)
	{
		_getbyid('star1').innerHTML= star_on;
		_getbyid('star2').innerHTML= star_on;
		_getbyid('star3').innerHTML= star_on;
		_getbyid('star4').innerHTML= star_off;
		_getbyid('star5').innerHTML= star_off;
	}
	else if(curr_rate		== 4)
	{
		_getbyid('star1').innerHTML= star_on;
		_getbyid('star2').innerHTML= star_on;
		_getbyid('star3').innerHTML= star_on;
		_getbyid('star4').innerHTML= star_on;
		_getbyid('star5').innerHTML= star_off;
	}
	else if(curr_rate		== 5)
	{
		_getbyid('star1').innerHTML= star_on;
		_getbyid('star2').innerHTML= star_on;
		_getbyid('star3').innerHTML= star_on;
		_getbyid('star4').innerHTML= star_on;
		_getbyid('star5').innerHTML= star_on;
	}
	else {
		_getbyid('star1').innerHTML= star_off;
		_getbyid('star2').innerHTML= star_off;
		_getbyid('star3').innerHTML= star_off;
		_getbyid('star4').innerHTML= star_off;
		_getbyid('star5').innerHTML= star_off;
	}
	
	curr_rating				= curr_rate;
}

function topic_rate(tid,rate)
{
	if(!http.req)
	{
		return true;
	}
		
	rate						= curr_rating;
	
	http.request_func			= function()
	{
		curr_rating				= parseInt(http.req.responseText);
		topic_rating			= parseInt(http.req.responseText);
		update_stars(curr_rating);
	}
	
	http.open(icebb_base_url+"topic="+tid+"&rate="+rate);
	
	return false;
}

function before_reply(tid)
{
	checked_pids				= new Array();
	for(i=1;i<={$this->tick_count};i++)
	{
		frmelement				= document.topicfrm.elements["checkedpids["+i+"]"];
		if(frmelement.checked)
		{
			val					= frmelement.value;
			checked_pids[checked_pids.length]= val;
		}
	}
	
	checked_pids_joined			= checked_pids.join(',');
	
	window.location				= icebb_base_url+'act=post&reply='+tid+'&checkedpids='+checked_pids_joined;
	
	return false;
}
// ]]>
</script>

<div class='Topicname' id='topic-{$topic['tid']}'>
	
	<h1>{$topic['title']}  <a href='rss.php?topic={$topic['tid']}' title="{$this->lang['rss_feed_topic']}"><{RSS_ICON}></a></h1>
	<div class="t_opt">
EOF;
if($is_fav)
{
	$code .= <<<EOF
<a href='{$icebb->base_url}act=ucp&amp;func=favorites&amp;opt=delete&amp;type=topic&amp;id={$topic['tid']}'>{$icebb->lang['fav_remove']}</a>
EOF;
}
else {
	$code .= <<<EOF
<a href='{$icebb->base_url}topic={$topic['tid']}&amp;func=favorite'>{$icebb->lang['fav_add']}</a>
EOF;
}
$code .= <<<EOF
 |
 <a href='{$icebb->base_url}topic={$topic['tid']}&amp;func=email' title="{$icebb->lang['email_desc']}">{$icebb->lang['email']}</a> |
 <a href='{$icebb->base_url}topic={$topic['tid']}&amp;func=print' title="{$icebb->lang['print']}">{$icebb->lang['print']}</a></div>


</div>
<div>{$pagelinks}</div>
<!--POLL-->

<form action='index.php' name='topicfrm' method='post'>
{$posts}



	<div class='subtitle'>
		<span style='float:right;font-weight:normal;margin:-2px 0px -3px 0px'>
			<!--MODERATOR.OPTIONS-->
		</span>
		
		<span>
			<a href='{$icebb->base_url}topic={$topic['tid']}&amp;go=prev'>&laquo; {$icebb->lang['prev_topic']}</a> 
			&middot; <a href='{$icebb->base_url}topic={$topic['tid']}&amp;go=next'>{$icebb->lang['next_topic']} &raquo;</a>
		</span>
	</div>

</form>

			{$pagelinks}

		<span style='font-weight:normal'>
			<a name='search_topic'></a>
			<!--form action='index.php' method='post' name='searcht_frm'>
				<input type='hidden' name='act' value='search' />
				<input type='hidden' name='func' value='results' />
				<input type='hidden' name='topic' value='{$topic['tid']}' />
				<input type='text' id='searchy' name='q' value="{$icebb->lang['search_topic']}" onclick="if(this.value=='{$icebb->lang['search_topic']}') this.value=''" class='form_textbox' />
				<input type='submit' value="{$icebb->lang['go']}" class='form_button' />
			</form-->
		</span><br />

<div class='borderwrap'>
<div class="row2">{$icebb->lang['tags']} {$topic['tag_html']}</div>
</div><br />

<a href='#' onclick="_toggle_view('qreply_box');return false"><{QUICK_REPLY}></a> <a href="{$icebb->base_url}act=post&amp;reply={$topic['tid']}" onclick="return before_reply('{$topic['tid']}')"><{ADD_REPLY}></a> <a href='{$icebb->base_url}act=post&amp;forum={$topic['forum']}'><{NEW_TOPIC}></a>

<div class='borderwrap' id='qreply_box' style='display:none'>
    <h2>{$icebb->lang['quick_reply']}</h2>
    <div class="row2" style='text-align:center'>

EOF;
if($flood_control_remain > 0)
{
	$fcontrol = sprintf($icebb->lang['flood_control'],$flood_control_remain);
	$code .= "<strong>{$fcontrol}</strong>";
}
else {
$code .= <<<EOF
    <form action='index.php' method='post' name='postFrm' style='text-align:center'>
        <input type='hidden' name='act' value='post' />
        <input type='hidden' name='reply' value='{$topic['tid']}' />
        <input type='hidden' name='security_key' value='{$icebb->security_key}' />
        <textarea name='post' rows='5' cols='50' style='width:80%' class='form_textarea'></textarea>
        <div class='buttonrow'>
            <input type='submit' name='submit' value='{$icebb->lang['add_reply']}' class='form_button default' />
            <input type='submit' value='{$icebb->lang['advanced']}' class='form_button' />
            <input type='button' value='{$icebb->lang['smilies']}' class='form_button' onclick="window.open(icebb_base_url+'act=post&amp;func=smilies','smiliesBox','height=400,width=350,scrollbars=yes');return false" />
        </div>
        <script type='text/javascript'>
		ta_obj=document.postFrm.post;
		</script>
		<script type='text/javascript' src='jscripts/editor.js'></script>
    </form>

EOF;
}
$code .= <<<EOF
</div>
</div>
<!--USERS_VIEWING-->

EOF;

return $code;
}

function post_row($r)
{
global $icebb;

$wh						= ($r['avsize'][0] > 0 && $r['avsize'][1] > 0) ? "width='{$r['avsize'][0]}' height='{$r['avsize'][1]}' " : "";
$avatar					= !empty($r['avatar']) ? "\n\t\t\t<img src='{$r['avatar']}' {$wh}alt='' class='avatar' />" : "";

$code .= <<<EOF

<!-- Post #{$r['postnum']} -->
<a name='post-{$r['pid']}'></a>
<div class='borderwrap post' style="margin-bottom: 10px;">
	<h2>
		<span style="display:inline;float:right">
			<a href='#' title="{$icebb->lang['top_desc']}">{$icebb->lang['top']}</a>
			<{MOD_OPTION_MULTI_SELECT}>
		</span>
		<a href='{$icebb->settings['board_url']}index.php?topic={$r['ptopicid']}&amp;pid={$r['pid']}' onclick="prompt('{$icebb->lang['use_this_url']}',this.href);return false">#{$r['postnum']}</a>
	</h2>

	<div class='post-content row2'>
		<div class='post-left row1' id='post-left-{$r['pid']}'>
			{$r['uauthor_username']} <span style="font-size: 8px"><!--IP--></span><br />
			{$r['title']}<br />{$avatar}
			<div title='{$r['rank']}'>{$r['pips']}</div>
			{$r['uposts']}
			{$r['ujoindate']}
			{$r['ugroup']}
			{$r['uaway']}
		</div>
		<div class='post-right row2' id='post-right-{$r['pid']}'>
			<div class="t_bar">{$r['pdate_formatted']}</div>
			<div id='ptext-{$r['pid']}'>{$r['ptext']}</div>
		
			<div style='float:right'>
				<a href='{$icebb->base_url}act=post&amp;reply={$r['ptopicid']}&amp;quote={$r['pid']}'><{P_REPLY}></a><{POST_EDIT}><{POST_DELETE}>
			</div>
		
			{$r['siggie']}
		</div>
		<div class='clear'></div>
	</div>
</div>

<!-- workaround to make the left post bit fill the full height -->
<script type='text/javascript'>
if($('post-left-{$r['pid']}').offsetHeight < $('post-right-{$r['pid']}').offsetHeight)
{
	$('post-left-{$r['pid']}').style.height=$('post-right-{$r['pid']}').offsetHeight+'px';
}
</script>

<!-- END of Post #{$r['postnum']} -->

EOF;

return $code;
}

function post_row_blocked($r)
{
global $icebb;

$mah	= $this->post_row($r);
$b		= $icebb->lang['post_blocked'];
$b		= str_replace('<#id#>',$r['postnum'],$b);
$b		= str_replace('<#user#>',$r['uauthor_username'],$b);

$code .= <<<EOF

<!-- Post #{$r['postnum']} HIDDEN -->
<a name='post-{$r['pid']}'></a>
<div class='borderwrap' style='text-align:center;margin-bottom:10px' id='post-{$r['postnum']}-msg'>
	<div class='row2'>
		{$b}<br />
		<a href='#post-{$r['pid']}' onclick="$('post-{$r['postnum']}-msg').style.display='none';$('post-{$r['postnum']}-content').style.display='block';return false">{$icebb->lang['view_anyway']}</a>
	</div>
</div>

<div id='post-{$r['postnum']}-content' style='display:none'>
{$mah}
</div>
<!-- END of Post #{$r['postnum']} -->

EOF;

return $code;
}

function uauthor($r)
{
global $icebb;

$code .= <<<EOF
<a href='index.php?profile={$r['pauthor_id']}' class='username_uim'>{$r['g_prefix']}{$r['pauthor']}{$r['g_suffix']}</a>

EOF;

return $code;
}

function uauthor_guest($r)
{
global $icebb;

$code .= <<<EOF
{$r['pauthor']}

EOF;

return $code;
}

function uposts($posts)
{
global $icebb;

$code .= <<<EOF
{$icebb->lang['posts']} {$posts}<br />

EOF;

return $code;
}

function ujoindate($joindate)
{
global $icebb;

$code .= <<<EOF
{$icebb->lang['joined']} {$joindate}<br />

EOF;

return $code;
}

function ugroup($group,$icon="")
{
global $icebb;

$icon					= !empty($icon) ? "<img src='skins/<#SKIN#>/images/icons/{$icon}' alt='' />" : "";

$code .= <<<EOF
{$icebb->lang['group']} {$group}<br />
{$icon}<br />

EOF;

return $code;
}

function uaway($away)
{
global $icebb;

$code .= <<<EOF
<strong>{$icebb->lang['user_is_away']}</strong><br />
EOF;

return $code;
}

function uip($ip,$dnslookup='')
{
global $icebb;

$code .= <<<EOF
<em>(<a href='{$icebb->base_url}act=ucp&amp;func=iptools&amp;ip={$ip}' class='ip_wanna_dns' style='cursor:help'>{$ip}</a>)</em>
EOF;

return $code;
}

function post_edit($pid,$quick_edit)
{
global $icebb;

if($quick_edit)
{
	$e	= " onclick='return !quick_edit_start({$pid})'";
}

$code .= <<<EOF
<a href='{$icebb->base_url}act=post&amp;edit={$pid}'{$e}><{P_EDIT}></a>

EOF;

return $code;
}

function post_delete($pid)
{
global $icebb;

$code .= <<<EOF
  <a href='{$icebb->base_url}act=post&amp;edit={$pid}&amp;del=1'><{P_DELETE}></a>

EOF;

return $code;
}

function post_last_edit($r,$hist=false)
{
global $icebb;

if($hist)
{
$histo = <<<EOF
 <a href='{$icebb->base_url}topic={$r['ptopicid']}&phistory={$r['pid']}'>{$icebb->lang['view_history']}</a>
EOF;
}

$last_edit = sprintf($icebb->lang['last_edit'],$r['pedit_author'],$r['pedit_formatted']);
$code .= <<<EOF
<br />
<br />
<div class='last-edit' style='font-style:italic;font-size:80%'>{$last_edit}{$histo}</div>

EOF;

return $code;
}

function moderator_tick($pid,$checked=0)
{
global $icebb;

$this->tick_count++;

if($icebb->is_mod)
{
	$title			= $icebb->lang['select_post_mod'];
}
else {
	$title			= $icebb->lang['select_post'];
}

if($checked)
{
	$check			= " checked='checked'";
}

$code			   .= <<<EOF
&nbsp; <input type='checkbox' name='checkedpids[{$this->tick_count}]' value='{$pid}' title="{$title}"{$check} />
EOF;

return $code;
}

function attachment_view($u)
{
global $icebb;

$code .= <<<EOF
 [ <strong>{$icebb->lang['attachment']}</strong> <a href='{$u['upath']}'>{$u['uname']}</a> ] 
EOF;

return $code;
}

function attachment_view_image($u,$w,$h)
{
global $icebb;

$code .= <<<EOF

<div style='margin:6px 0px'>
<strong>{$icebb->lang['attached_image']}</strong><br />
<img src='{$u['upath']}' alt='{$u['uname']}' />
</div>

EOF;

return $code;
}

function attachment_view_image_thumb($u)
{
global $icebb;

$code .= <<<EOF

<div style='margin:6px 0px'>
<strong>{$icebb->lang['attached_image']}</strong><br />
<img src='{$u['upath']}' alt='{$u['uname']}' />
</div>

EOF;

return $code;
}

function report_link($p)
{
global $icebb;

$code .= <<<EOF
<a href='{$icebb->base_url}topic={$p['ptopicid']}&amp;report={$p['pid']}' onclick="window.open(this.href,'reportWin','width=300,height=300');return false" title="{$icebb->lang['report_desc']}">{$icebb->lang['report']}</a> / 
EOF;

return $code;
}

function report_post_window($t,$p)
{
global $icebb;

$report_title = sprintf($icebb->lang['report_title'],$t['title']);
$code .= <<<EOF
<div class='border' style='width:100%'>
	<h2>{$report_title}</h2>
	<div class='border lightpadded' style='text-align:center'>
		<strong>{$icebb->lang['report_only']}</strong>
	</div>
	<form action='index.php' method='post'>
	<input type='hidden' name='topic' value='{$t['tid']}' />
	<input type='hidden' name='report' value='{$p['pid']}' />
	<table width='100%' cellpadding='2' cellspacing='1' border='0' style='font-size:100%'>
		<tr>
			<td width='40%'>
				<strong>{$icebb->lang['report_reason']}</strong><br />
				{$icebb->lang['report_reason_why']}
			</td>
			<td>
				<textarea name='reason' rows='5' cols='20' class='form_textarea'></textarea>
			</td>
		</tr>
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='submit' value="{$icebb->lang['report_post']}" class='form_button' />
		<input type='button' value="{$icebb->lang['report_cancel']}" onclick="window.close()" class='form_button' />
	</div>
	</form>
</div>

EOF;

return $code;
}

function report_post_done($t,$p)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['report_done']}</h2>
	{$icebb->lang['report_done_msg']}
</div>

EOF;

return $code;
}

function siggie($sig)
{
global $icebb;

$code .= <<<EOF
------------------------------------------
<div class='sig'>
{$sig}
</div>

EOF;

return $code;
}

function moderator_options($topic,$links)
{
global $icebb;

$code .= <<<EOF
<script type='text/javascript'>
<!--
function moderate_frm_subm(tform, mode)
{
	if(mode == 1)
	{
		if(tform.selectedIndex==0)
		{
			return false;
		}
		
		if(tform.options[tform.selectedIndex].value=='topic_delete')
		{
			s			= confirm('Are you sure you want to delete this topic?');
			if(!s)
			{
				tform.selectedIndex=0;
				return false;
			}
		}
		
		if(tform.options[tform.selectedIndex].value=='posts_delete')
		{
			s			= confirm('Are you sure you want to delete these posts?');
			if(!s)
			{
				tform.selectedIndex=0;
				return false;
			}
		}
	}
	else
	{
		document.topicfrm.func.selectedIndex=0;
		document.topicfrm.func.value='multimod';
	}
	
	document.topicfrm.submit();
}
//-->
</script>
<input type='hidden' name='act' value='moderate' />
<input type='hidden' name='topicid' value='{$topic['tid']}' />
<select name='func' onchange="moderate_frm_subm(this, 1)" class='form_dropdown'>
    <option value='--' selected='selected' style='font-weight:bold'>{$icebb->lang['mod_options']}</option>
    <option disabled='disabled' class='optgroup'>{$icebb->lang['topic']}</option>
        {$links['topic']}
    <option disabled='disabled' class='optgroup'>{$icebb->lang['selected_posts']}</option>
        {$links['posts']}
</select><br />
<script type='text/javascript'>
<!--
document.topicfrm.func.selectedIndex=0;
//-->
</script>

EOF;

return $code;
}

function moderator_options_addlink($value,$text,$extra='')
{
global $icebb;

$code .= <<<EOF
        <option value='{$value}'{$extra}>{$text}</option>

EOF;

return $code;
}

function users_viewing($num,$users)
{
global $icebb;

$u		= $icebb->lang['users_viewing_sub'];
$u		= str_replace('<#members#>',$num['members'],$u);
$u		= str_replace('<#guests#>',$num['guests'],$u);
$u		= str_replace('<#total#>',$num['total'],$u);

$code .= <<<EOF
<br />
<div class='borderwrap'>
<h2>{$icebb->lang['users_viewing']}</h2>
<div class="row3">{$u}</div>
<div style="padding: 3px" class="row1">{$users}</div>
</div>

EOF;

return $code;
}

function post_history($p,$edits)
{
global $icebb;

$p			= sprintf($icebb->lang['post_history_view'],$p['pid']);

$code .= <<<EOF
<div class='borderwrap'>
<h2>{$p}</h2>
{$edits}
</div>

EOF;

return $code;
}

function post_history_row($r)
{
global $icebb;

$code .= <<<EOF
<fieldset style='border:1px solid black;'>
<legend><strong>{$r['pauthor']}, {$r['pdate_formatted']}</strong></legend>
<!--<div class='border lightpadded row2' style='margin-bottom:6px'>-->
<!--<div class='subtitle' style='margin:-2px -2px 2px -2px'><strong>{$r['pauthor']}, {$r['pdate_formatted']}</strong></div>-->
{$r['ptext']}
<!--</div>-->
</fieldset>

EOF;

return $code;
}

function post_history_diff($p,$diffs)
{
global $icebb;

$p			= sprintf($icebb->lang['post_history_view'],$p['pid']);

$code .= <<<EOF
<div class='borderwrap lightpadded post_diff'>
<h2>{$p}</h2>
{$diffs}
</div>

EOF;

return $code;
}

function print_page($topic,$posts)
{
global $icebb;

$y		= gmdate('Y');

$code .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>{$topic['title_pf']}</title>
<style type='text/css'>
body
{
	text-align:center;
}

#pf_content
{
	width:80%;
	margin:0px auto;
	text-align:left;
}

#pf_title
{
	margin:6px 30px 12px 6px;
}

#pf_title h2
{
	font-size:130%;
	color:#666;
	font-weight:normal;
	margin:0px;
}

#pf_title a
{
	font-size:70%;
	color:#666699;
	text-decoration:underline;
}

.pf_post
{
	border-bottom:1px solid #ccc;
	padding-bottom:6px;
	font-size:80%;
}

.pf_post .pf_author
{
	display:block;
	background-color:#eee;
	color:#666;
	padding:2px;
	margin-bottom:2px;
	font-size:100%;
}

.pf_copyright
{
	font-size:80%;
	color:#666;
}
</style>
<#JAVASCRIPT#>
</head>
<body id='printerfriendly'>
<div id='pf_content'>
	<div id='pf_title'>
		<h2>{$topic['title_pf']}</h2>
		<a href='{$icebb->base_url}topic={$topic['tid']}'>{$icebb->lang['pf_original_version']}</a>
	</div>
	
	<div class='pf_topic'>
{$posts}
	</div>
	
	<div class='pf_copyright'>Powered by IceBB &copy; 2004-{$y} XAOS Interactive</div>
</div>
</body>
</html>

EOF;

return $code;
}

function print_page_post($p)
{
global $icebb;

$a			= $icebb->lang['print_posted_by'];
$a			= str_replace('<#author#>',$p['pauthor'],$a);
$a			= str_replace('<#date#>',$p['pdate_formatted'],$a);

$code .= <<<EOF
		<div class='pf_post'>
			<strong class='pf_author'>{$a}</strong>
			{$p['ptext']}
		</div>

EOF;

return $code;
}

function email_topic($topic)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['email_this_topic']}</h2>
	<form action='{$icebb->base_url}topic={$topic['tid']}&amp;func=email' method='post'>
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<td class='row2' width='40%'>
				<strong>{$icebb->lang['recipient_name']}</strong>
			</td>
			<td class='row1'>
				<input type='text' name='mail_to_name' value='' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row2' width='40%'>
				<strong>{$icebb->lang['recipient_email']}</strong>
			</td>
			<td class='row1'>
				<input type='text' name='mail_to' value='' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row2' width='40%'>
				<strong>{$icebb->lang['message']}</strong>
			</td>
			<td class='row1'>
				<textarea name='msg' rows='5' cols='30' class='form_textarea'>{$icebb->lang['thought_you_might_be_interested']}
{$icebb->settings['board_url']}index.php?topic={$topic['tid']}</textarea>
			</td>
		</tr>
	</table>
	<div class='buttonrow'><input type='submit' name='submit' value="{$icebb->lang['send']}" class='form_button' /></div>
	</form>
</div>

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

}
?>
