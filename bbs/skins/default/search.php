<?php
if(!class_exists('skin_global')) require('global.php');

class skin_search
{
	function skin_search()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function search_start($forum_listing)
{
global $icebb,$global;

$code						= $global->header();
$code .= <<<EOF
<div class='borderwrap'>
<h2>{$icebb->lang['search']}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='search' />
		<input type='hidden' name='func' value='results' />
		<table width='100%' cellpadding='2' cellspacing='1' border='0'>
			<tr>
				<th>
					{$icebb->lang['search_by_keyword']}
				</th>
				<th>
					{$icebb->lang['author']}
				</th>
			</tr>
			<tr>
				<td width='50%' valign='top' class="row1">
					<input type='text' name='q' value='' size='32' class='form_textbox' title="{$icebb->lang['enter_search_terms']}" />
				</td>
				<td valign='top' class="row1">
					<input type='text' name='search_user' id='search_user' value='' size='32' class='form_textbox' title="{$icebb->lang['enter_username']}" />
					<span class='autocomplete hide' id="search_user_complete"></span>
					
					<script type="text/javascript">
						new Ajax.Autocompleter('search_user','search_user_complete',icebb_base_url+'act=members&amp;ajax_search=1',{})
					</script>
				</td>
			</tr>
			<tr>
				<th>
					{$icebb->lang['in_these_forums']}
				</th>
				<th>
					{$icebb->lang['limit_to']}
				</th>
			</tr>
			<tr>
				<td valign='top' class="row1">
					<select name='search_forums[]' class='form_dropdown' multiple='multiple' size='7'>
						{$forum_listing}
					</select>
				</td>
				<td valign='top' class="row1">
					<select name='search_limit_post_type' class='form_dropdown'>
						<option value='posts'>{$icebb->lang['posts']}</option>
						<option value='topics'>{$icebb->lang['topics']}</option>
					</select>
					{$icebb->lang['posted']}
					<select name='search_limit_how_long_ago' class='form_dropdown'>
						<option value='30'>{$icebb->lang['last_30']}</option>
						<option value='60'>{$icebb->lang['last_60']}</option>
						<option value='90'>{$icebb->lang['last_90']}</option>
						<option value='365'>{$icebb->lang['last_year']}</option>
						<option value='0'>{$icebb->lang['last_ever']}</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class='buttonstrip' colspan='2'>
					<input type='submit' name='submit' value="{$icebb->lang['search']}" class='form_button' />
				</td>
			</tr>
		</table>
	</form>
</div>

EOF;
$code					   .= $global->footer();

return $code;
}

function forum_row($f)
{
global $icebb;

$code .= <<<EOF
<option value='{$f['fid']}'>{$f['name']}</option>

EOF;

return $code;
}

function results_page($info,$results,$pagelinks)
{
global $icebb,$global;

$s							= sprintf($icebb->lang['search_results_for'],$info['query']);
$ss							= $icebb->lang['showing_results'];
$ss							= str_replace('<#start#>',$info['start'],$ss);
$ss							= str_replace('<#end#>',$info['end'],$ss);
$ss							= str_replace('<#total#>',$info['total'],$ss);

$code						= $global->header();
$code .= <<<EOF
	<div class="borderwrap">
	<h2>
		<span style="float:right">{$ss}</span>
		{$s}
	</h2>
	
	<div class='row3' style='clear:both;text-align:right;padding:4px;margin-bottom:-16px'>
{$pagelinks}
	</div>
	
{$results}

	<div class='row3' style='padding:4px'>
		<div style='float:right;padding:4px'>
{$pagelinks}
		</div>
	
		<form action='{$icebb->base_url}' method='post' style='font-weight:normal'>
		<input type='hidden' name='act' value='search' />
		<input type='hidden' name='func' value='results' />
		<label>
			<strong>{$icebb->lang['try_again']}</strong> 
			<input type='text' name='q' value='{$info['query']}' class='form_textbox' />
		</label>
		<input type='submit' value='{$icebb->lang['go']}' class='form_button' />
		</form>
	</div>
</div>

EOF;
$code					   .= $global->footer();

return $code;
}

function search_result($p)
{
global $icebb;

$code .= <<<EOF

<div class="result">
<strong>{$icebb->lang['topic']}</strong> <em><a href='index.php?topic={$p['ptopicid']}&pid={$p['pid']}'>{$p['title']}</a></em> <br />
<div class="highlight_result">{$p['ptext']}</div>
</div>


EOF;

return $code;
}

// anything below this line is NO LONGER IN USE
///////////////////////////////////////////////////////
function search_xmlhttp($info,$results,$pagelinks)
{
global $icebb;

$code .= <<<EOF
<div class='row2' style='padding:2px'>Search results {$info['start']}-{$info['end']}/{$info['total']} for {$info['query']}</div>
<ul>
{$results}
</ul>
<div class='row1' style='padding:2px'>
{$pagelinks}
<a href='#' onclick="_getbyid('search_div').innerHTML=search_form_html;return false">New Search</a>
</div>

EOF;

return $code;
}

function search_xmlhttp_nonefound()
{
global $icebb;

$code .= <<<EOF
No results found for your query.
<div class='row1' style='padding:2px'>
<a href='#' onclick="_getbyid('search_div').innerHTML=search_form_html;return false">New Search</a>
</div>

EOF;

return $code;
}

function search_xmlhttp_result($r)
{
global $icebb;

$code .= <<<EOF
	<li><a href='{$icebb->base_url}topic={$r['tid']}&amp;pid={$r['pid']}'>{$r['title']}</a></li>

EOF;

return $code;
}

function search_xmlhttp_prev_link($search_id,$prev)
{
global $icebb;

$code .= <<<EOF
<a href='index.php?act=search&amp;search_id={$search_id}' onclick="return do_search('ICEBBchange_pageICEBB',{$prev},'{$search_id}')">&laquo; Previous</a> &middot; 

EOF;

return $code;
}


function search_xmlhttp_next_link($search_id,$next)
{
global $icebb;

$code .= <<<EOF
<a href='index.php?act=search&amp;search_id={$search_id}' onclick="return do_search('ICEBBchange_pageICEBB',{$next},'{$search_id}')">Next &raquo; </a> &middot; 

EOF;

return $code;
}

function newposts_xmlhttp($newposts)
{
global $icebb;

$code .= <<<EOF
<ul>
{$newposts}
</ul>

EOF;

return $code;
}

function newposts_xmlhttp_row($r)
{
global $icebb;

$code .= <<<EOF
	<li><a href='{$icebb->base_url}topic={$r['tid']}&amp;pid={$r['pid']}'>{$r['title']}</a></li>

EOF;

return $code;
}

}
?>