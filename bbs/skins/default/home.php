<?php
require('global.php');
require('forum_global.php');

class skin_home extends skin_forum_global
{
	function skin_home()
	{
		global $icebb,$global,$forum_global;
	
		$global				= new skin_global;
		//$forum_global		= new skin_forum_global;
	}
	
	function display($althome=0)
	{
		global $icebb,$global,$forum_global;
	
		$forums				= func_get_arg(1);
		$stats				= func_get_arg(2);
	
		$code				= $global->header();
	
		if($althome)
		{
			$code		   .= $this->althome($forums,$stats);
		}
		else {
			$code		   .= $this->home($forums,$stats);
		}
	
		$code			   .= $global->footer();
	
		return $code;
	}
	
	function althome($forums,$stats)
	{
		global $icebb,$forum_global;
	
		$code				= <<<EOF
<script type='text/javascript'>
// <![CDATA[
marker_nonew="<macro:f_nonew />";
// ]]>
</script>

EOF;

		if($icebb->user['id']!='0')
		{
			$code .= <<<EOF
<div class='tabs' id='home-view-select'>
	{$icebb->lang['view_select']}
	<ul>
		<li><a href='{$icebb->base_url}act=home&amp;view=default&amp;sticky'>{$icebb->lang['view_standard']}</a></li>
		<li><a href='{$icebb->base_url}act=home&amp;view=alt&amp;sticky' class='active'>{$icebb->lang['view_customized']}</a></li>
	</ul>
</div>

EOF;
		}

		foreach($forums as $fi)
		{
			$f			= $fi[0];
			$topics		= $fi[1];
			
			if(!is_array($topics) || empty($topics))
			{
				$code  .= <<<EOF
<div class='borderwrap'>
	<h2><a href='{$icebb->base_url}forum={$f['fid']}'>{$f['name']}</a></h2>
	<table width="100%" cellspacing="1" cellpadding="1" style='margin-top:-1px'>
		<tr>
			<td class='row2'>
				<em>{$icebb->lang['no_topics_found']}</em>
			</td>
		</tr>
	</table>
</div><br />
		
EOF;
				continue;
			}
		
			$code	   .= <<<EOF
<div class='borderwrap'>
	<h2><a href='{$icebb->base_url}forum={$f['fid']}'>{$f['name']}</a></h2>
	<table width="100%" cellspacing="1" cellpadding="1" style='margin-top:-1px'>
		<tr>
			<th width="1%">&nbsp;</td>
			<th width="40%">{$icebb->lang['topic_name']}</td>
			<th width="13%" style='text-align:center'>{$icebb->lang['author']}</td>
			<th width="10%" style='text-align:center'>{$icebb->lang['replies']}</td>
			<th width="10%" style='text-align:center'>{$icebb->lang['views']}</td>
			<th>{$icebb->lang['last_post']}</td>
		</tr>

EOF;

			foreach($topics as $t)
			{
				if($t['replies']>15)
				{
					$marker= '<macro:t_hot_new />';
				}
				else {
					$marker= '<macro:t_new />';
				}
			
				$code   .= <<<EOF
		<tr>
			<td class="row1" style='text-align:center'>
				<a href='{$icebb->base_url}topic={$t['tid']}&amp;show=newpost'>{$marker}</a>
			</td>
			<td class="row2" id='topic-title-{$r['tid']}'>
				<div>{$t['prepend']}{$t['post_icon']}<span><a href='{$icebb->base_url}topic={$t['tid']}' title="{$t['snippet']}">{$t['title']}</a></span>{$t['append']}</div>
					<span class='desc'>{$t['description']}</span>
			</td>
			<td class="row2" style='text-align:center'>
				{$t['starter']}
			</td>
			<td class="row1" style='text-align:center'>
				{$t['replies']}
			</td>
			<td class="row1" style='text-align:center'>
				{$t['views']}
			</td>
			<td class="row2">
				<div class='small-light'>{$t['lastpost_time_formatted']}</div>
				{$t['lastpost_author']} <a href='{$icebb->base_url}topic={$t['tid']}&amp;show=lastpost'>&raquo;</a>
			</td>
		</tr>

EOF;
			}

			$code	   .= <<<EOF
	</table>
</div><br />

EOF;
		}
		
		$code		   .= $this->stats($stats);
	
		return $code;
	}

	function home($forums,$stats)
	{
		global $icebb,$std;

		$code		   .= <<<EOF
<script type='text/javascript'>
// <![CDATA[
marker_nonew="<macro:f_nonew />";
// ]]>
</script>
EOF;

		if($icebb->user['id']!='0')
		{
			$code .= <<<EOF
<div class='tabs' id='home-view-select'>
	{$icebb->lang['view_select']}
	<ul>
		<li><a href='{$icebb->base_url}act=home&amp;view=default&amp;sticky' class='active'>{$icebb->lang['view_standard']}</a></li>
		<li><a href='{$icebb->base_url}act=home&amp;view=alt&amp;sticky'>{$icebb->lang['view_customized']}</a></li>
	</ul>
</div>

EOF;
		}
		
		if(count($forums)>0)
		{
			foreach($forums as $cat)
			{
				$code	   .= $this->forum_category($cat[0],$cat[1]);
			}
		}
		else {
			$std->error($icebb->lang['no_forums']);
		}

		$code		   .= $this->stats($stats);
	
		return $code;
	}

	function stats($stats)
	{
		global $icebb;
		
		$code			= <<<EOF

<div class='borderwrap' style="margin-bottom: 15px;">
<h2>{$icebb->lang['forum_stats']}</h2>
<table width="100%" border="0" cellspacing="1" cellpadding="1" style='margin-top:-1px' >

EOF;

		if(!$icebb->settings['cpu_disable_online_members'])
		{
			$code	   .= $this->stats_online($stats['online']['count'],$stats['online']['users'],$stats['online']['groups']);
		}

		if(!empty($stats['bday']))
		{
			$code	   .= $this->stats_birthdays($stats['bday']);
		}
		
		$code		   .= $this->stats_board($stats['board'],$stats['recent']);
		$code		   .= <<<EOF
</table>
</div>

EOF;
		
		return $code;
	}

	function stats_online($count,$users,$groups)
	{
		global $icebb;
	
		if(count($users)>0)
		{
			foreach($users as $k => $u)
			{
				//$users_html.= "<a href='{$icebb->base_url}profile={$u['id']}'>{$u['username']}</a>";
				$user_html[]= $u;
			}
			$users_html	= implode(', ',$user_html);
		}
	
		foreach($groups as $k => $g)
		{
			// guests shouldn't be shown, nor should banned or validating
			if($g['gid'] == 4 || $g['g_view_board'] == 0 || $g['gid'] == 3)
			{
				continue;
			}
			
			$g['g_prefix']			= html_entity_decode($g['g_prefix']);
			$g['g_suffix']			= html_entity_decode($g['g_suffix']);
		
			$group_html[]= "<a href='{$icebb->base_url}act=members&amp;user_group={$g['gid']}'>{$g['g_prefix']}{$g['g_title']}{$g['g_suffix']}</a> ({$g['count']})";
		}
		$groups_html	= implode(', ',$group_html);
		
		$users_online	= sprintf($icebb->lang['users_online'],$count['total']);
		$users_online2	= sprintf($icebb->lang['users_online2'],$count['users'],$count['guests']);
		
		$code		   .= <<<EOF
	<tr>
		<th colspan="2">
			<div style="display:inline;float:right">
				[ <a href='{$icebb->base_url}act=misc&amp;func=leaders'>{$icebb->lang['forum_leaders']}</a> &middot;
				<a href='{$icebb->base_url}act=forum&amp;mark_all_read=1'>{$icebb->lang['mark_all_forums_read']}</a> &middot;
				<a href='{$icebb->base_url}act=login&amp;func=clear_cookies'>{$icebb->lang['clear_cookies_set']}</a> ]
			</div>
			{$users_online}
		</th>
	</tr>
	<tr>
		<td width="100%" class="row2" colspan="2">
			{$users_online2}
			<div class="userpad">{$users_html}</div>
			{$groups_html}
		</td>
	</tr>

EOF;

		return $code;
	}
	
	function stats_birthdays($users)
	{
		global $icebb;
	
		$code		   .= <<<EOF
		<tr>
			<th colspan='2'>{$icebb->lang['birthdays']}</th>
		</tr>
		<tr class='forum'>
			<td width="100%" class="row2" colspan="2">
				{$icebb->lang['happy_birthday']}<br />
				{$users}
			</td>
		</tr>
	
EOF;
	
		return $code;
	}
		
	function stats_board($info,$recent)
	{
		global $icebb;
		
		$code		   .= <<<EOF
		<tr>
			<th>{$icebb->lang['stats']}</th>
			<th width="50%" id='board_timeline_title'>
				<div id='btt_vis'>{$icebb->lang['recent_actions']} <a href='#' onclick='return !hide_timeline()'>&raquo;</a></div>
				<div id='btt_hide' style='display:none'><a href='#' onclick='return !show_timeline()'>&laquo;</a></div>
			</th>
		</tr>
		<tr>
			<td class="row2" valign="top">
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td valign="top">
							<strong>{$icebb->lang['total_posts']}</strong> {$info['posts']}<br />
							<strong>{$icebb->lang['total_topics']}</strong> {$info['topics']} <br />
							<strong>{$icebb->lang['total_replies']}</strong> {$info['replies']}<br />
						</td>
						<td valign="top" style='text-align:right'>
							<strong>{$icebb->lang['most_members_online']}</strong> {$info['most_online_ever']}, {$info['most_online_ever_time']}<br />
							<strong>{$icebb->lang['total_members']}</strong> {$info['users']}<br />
							{$icebb->lang['newest_member']} <strong><a href='{$icebb->base_url}profile={$info['newest_user']['id']}'>{$info['newest_user']['username']}</a></strong>
						</td>
					</tr>
				</table>
			</td>
			<td class="row2" valign="top" id='board_timeline'>
				{$recent}
			</td>
		</tr>
EOF;
		
		return $code;
	}
}
?>
