<?php
if(!class_exists('skin_global')) require('global.php');
if(!class_exists('skin_topic')) require('topic.php');

class skin_pm extends skin_topic
{
	function skin_pm()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function layout($content)
{
global $icebb,$global;

$code	= $global->header();
$code .= <<<EOF
{$content}

EOF;
$code .= $global->footer();

return $code;
}

function pm_list($pms,$tags=array())
{
global $icebb;

$count				= count($pms);

if($icebb->user['new_pms']>0)
{
	$inbox_link		= sprintf($icebb->lang['inbox_link_new'],$icebb->user['new_pms']);
	$inbox_link		= "<strong>{$inbox_link}</strong>";
}
else {
	$inbox_link		= $icebb->lang['inbox_link'];
}

$code .= <<<EOF
<table width='100%' cellpadding='1' cellspacing='1'>
	<tr>
	<td width="24%" valign="top" style='padding-right:10px'>
		<div class='borderwrap'>
			<div class='row1 pm-menu'>
				<h2>{$icebb->lang['menu']}</h2>
				<div class='Subtitle'>{$icebb->lang['pm_nav']}</div>
				<ul>
					<li><a href="{$icebb->base_url}act=pm">{$inbox_link}</a></li>
					<li><a href="{$icebb->base_url}act=pm&amp;func=write" title="{$icebb->lang['compose2']}">{$icebb->lang['compose']}</a></li>
				</ul>

EOF;

if(is_array($tags))
{
	$code		   .= <<<EOF
				<div class='Subtitle'>{$icebb->lang['tags']}</div>
				<ul>

EOF;

	foreach($tags as $tag)
	{
		$code	   .= "\t\t\t\t\t<li><a href='{$icebb->base_url}act=pm&amp;tag={$tag['id']}'>{$tag['tag']}</li>\n"; 
	}
	
	$code		   .= "\t\t\t\t</ul>\n";
}

$new_pms			= sprintf($icebb->lang['new_pms'],$count);
$code			   .= <<<EOF
			</div>
		</div>
	</td>
	<td width='76%' valign="top">
		<div class="borderwrap">
			<form action='{$icebb->base_url}act=pm'>
			<h2>{$icebb->lang['pm_title']}</h2>
			<table cellspacing="1">
				<tr>
					<td class='row1' colspan='2' style='font-weight:bold'>
						{$new_pms}
					</td>
				</tr>
				<tr>
					<th width="1%">&nbsp;</th>
					<th width="97%" style="text-align: left; ">{$icebb->lang['subject']}</th>
				</tr>

EOF;

$code .= <<<EOF

EOF;

if(is_array($pms) && count($pms)>0)
{
	foreach($pms as $r)
	{
		$r['marker']= "<macro:t_nonew />";
		$code .= <<<EOF
				<tr>
					<td width="1%" class="row1">{$r['marker']}</td>
					<td width="97%"  class="row1" valign="top"><a href='{$icebb->base_url}act=pm&amp;read={$r['tid']}'>{$r['title']}</a> <br /> <a href="{$icebb->base_url}profile={$r['id']}"><span class="Author">{$r['starter']}</span></a></td>
				</tr>
	
	
EOF;
	}
}
else {
	$code .= "<tr><td colspan='5' class='row1'>{$icebb->lang['no']}</td></tr>\n";
}

$code .= <<<EOF
			</table>
			</form>
		</div>
	</td>
</tr>
</table>

EOF;

return $code;
}

function topic_view($topic,$posts,$pagelinks='')
{
global $icebb;

$code .= <<<EOF
<script type='text/javascript' src='jscripts/topic.js'></script>

<div class='Topicname' id='topic-{$topic['tid']}'>
	<h1>{$topic['title']}</h1>
	<div class="t_opt"><a href='{$icebb->base_url}act=pm&amp;func=del&amp;id={$topic['tid']}'>{$icebb->lang['del_topic']}</a></div>
</div>
<div>{$pagelinks}</div>
{$posts}

{$pagelinks}

<div class='borderwrap'>
	<div class="row2">
		<div style='float:right;font-size:80%'>
			<form action='{$icebb->base_url}act=pm' method='post'>
				<input type='hidden' name='func' value='tag' />
				<input type='hidden' name='id' value='{$topic['tid']}' />
				<input type='text' name='tags' value='' class='form_textbox' />
				<input type='submit' value="{$icebb->lang['tag2']}" class='form_button' />
			</form>
		</div>
		
		<div style='padding:4px'>
			{$icebb->lang['tags2']} {$topic['tag_html']}
		</div>
		
		<div style='clear:both'></div>
	</div>
</div><br />

<a href="javascript:_toggle_view('qreply_box')"><{QUICK_REPLY}></a> <a href="{$icebb->base_url}act=pm&amp;func=write&amp;reply={$topic['tid']}"><{ADD_REPLY}></a>

<div class='borderwrap' id='qreply_box' style='display:none'>
    <h2>{$icebb->lang['quick_reply']}</h2>
    <div class="row2" style='text-align:center'>
    <form action='index.php' method='post' name='postFrm' style='text-align:center'>
        <input type='hidden' name='act' value='pm' />
		<input type='hidden' name='func' value='write' />
        <input type='hidden' name='reply' value='{$topic['tid']}' />
        <input type='hidden' name='security_key' value='{$icebb->security_key}' />
        <textarea name='body' rows='5' cols='50' style='width:80%' class='form_textarea'></textarea>
        <div class='buttonrow'>
            <input type='submit' name='submit' value='{$icebb->lang['add_reply']}' class='form_button default' />
            <input type='submit' value='{$icebb->lang['advanced']}' class='form_button' />
            <input type='button' value='{$icebb->lang['smilies']}' class='form_button' onclick="window.open('{$icebb->base_url}act=post&amp;func=smilies','smiliesBox','height=400,width=300');return false" />
        </div>
        <script type='text/javascript'>
		ta_obj=document.postFrm.post;
		</script>
		<script type='text/javascript' src='jscripts/editor.js'></script>
    </form>
</div>
</div>

EOF;

return $code;
}

/*function post_row($r)
{
global $icebb;

$code .= <<<EOF
<!-- PM ID ({$r['pid']}) -->

<!-- New topic view, in improvement -->
<div class='borderwrap' style="margin-bottom: 10px;">
<h2>{$topic['title']}<select>
   <option value="1" selected="selected">{$icebb->lang['labels']}</option>
   </select></h2>

<table cellpadding="3" cellspacing="1" width="100%">
<tr>
<td class="row3">
<strong>{$icebb->lang['subject2']}</strong> {$r['title']}<br />
<strong>{$icebb->lang['created_by']}</strong> {$r['uauthor_username']}<br />
<strong>{$icebb->lang['created_on']}</strong> {$r['pdate_formatted']} <br />
{$icebb->lang['forward']} | {$icebb->lang['delete']} | {$icebb->lang['report']} | {$icebb->lang['ignore']}
</td>
</tr>
	<tr>
	

		<td width="80%" class="row2" valign="top">
		{$r['ptext']}
		<br /> <br />
<a href='#'>{$icebb->lang['reply_quote']}</a> | {$icebb->lang['reply']}
		</td>
		</tr>
	
</table>
</div>
<!-- END of PM ID ({$r['pid']}) -->
EOF;

return $code;
}*/

function list_pm($r,$marker='<macro:t_nonew />')
{
global $icebb;

$code .= <<<EOF
<tr>
<td width="1%" class="{$pm_colors}" >{$marker}</td>
<td width="1%"  class="{$pm_colors}">[]</td>
<td width="97%"  class="{$pm_colors}" valign="top"><a href='{$icebb->base_url}act=pm&amp;read={$r['tid']}'>{$r['title']}</a> <br /> <a href="#"><span class="Author">{$r['starter']}</span></a></td>
<td width="1%"  class="{$pm_colors}"><input type="checkbox" name="test" value="test"></td>
</tr>

EOF;

return $code;
}

function pm_form($smilies,$extra_fields,$editor,$ptext='',$messages=array())
{
global $icebb;

$code = <<<EOF
<div class="borderwrap">
<h2>{$icebb->lang['compose']}</h2>
<form action='index.php' method='post' name='postFrm'>
	<input type='hidden' name='act' value='pm' />
	<input type='hidden' name='func' value='write' />
	{$extra_fields}
	<table width='100%' cellpadding='2' cellspacing='1' border='0'>

EOF;

if(count($messages)>0)
{
	$code .= <<<EOF
<tr>
	<td colspan='4' class='row2'>
		<div class='highlight_error'>
			<span class='title'>{$icebb->lang['errors_title']}</span>
			<ul>
EOF;
	foreach($messages as $message)
	{
		$code .= <<<EOF
				<li>{$message}</li>

EOF;
	}
	$code .= <<<EOF
			</ul>
		</div>
	</td>
</tr>

EOF;
}

$code .= <<<EOF
		<!--TO.INPUT-->
		<!--SUBJECT.INPUT-->
		<tr> 
			<td class="row3" colspan="4">
				<strong>{$icebb->lang['add_content']}</strong>
			</td>
		</tr>
		<tr>
			<td class="row1" valign="top" width="20%" style='text-align:center'>
				<strong>{$icebb->lang['smilies']}</strong><br />
				<div style='width:60%;margin:0px auto'>
					{$smilies}<br />
				</div>
				<a href='{$icebb->base_url}act=post&amp;func=smilies' onclick="window.open(this.href,'smiliesBox','height=400,width=300');return false">{$icebb->lang['more']}</a>
			</td>
			<td valign='top' class='row2' colspan='3'>
				{$editor}
			</td>
		</tr>
	</table>
	<div class='buttonstrip'>
		<input type='submit' name='submit' value='{$icebb->lang['send_pm']}' class='form_button' />
	</div>
</form>
</div>
EOF;

return $code;
}

function pm_subject_input($val='')
{
global $icebb;

$code .= <<<EOF
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['subject2']}</strong>
			</td>
			<td class='row2'>
				<input type='text' value='{$val}' name='subject' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row1' width='20%'>
				<strong>{$icebb->lang['t_tags']}</strong><br />
				<em>{$icebb->lang['sep_with_space']}</em>
			</td>
			<td class='row2'>
				<input type='text' name='tags' value='{$tags}' class='form_textbox' tabindex='3' />
			</td>
		</tr>

EOF;

return $code;
}				

function pm_to_input($val='',$buddies=array())
{
global $icebb;

if(is_array($buddies) && count($buddies)>0)
{
	foreach($buddies as $bud)
	{
		$bhtml2.= "\t\t\t\t\t<li><a href='#' onclick='document.postFrm.to.value=this.innerHTML;return false'>{$bud['username']}</a></li>\n";
	}
	
	$bhtml	= <<<EOF
			<td class='row1' rowspan='3' width='50%' valign='top'>
				<strong style='float:left;font-size:110%'>&laquo;</strong>
				<ul style='margin:0px;float:left;padding-left:16px'>
{$bhtml2}
				</ul>
			</td>

EOF;
}

$code .= <<<EOF
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['to']}</strong>
			</td>
			<td class='row2'>
				<input type='text' value='{$val}' name='to' id='to' class='form_textbox' />
				<span class='autocomplete hide' id="to_complete"></span>
				<script type="text/javascript">
					new Ajax.Autocompleter('to','to_complete',icebb_base_url+'act=members&amp;ajax_search=1',{})
				</script>
			</td>
{$bhtml}
		</tr>

EOF;

return $code;
}

function bottom()
{
global $icebb;

$code .= <<<EOF
</div>

EOF;

return $code;
}

}
?>
