<?php
if(!class_exists('skin_global')) require('global.php');

class skin_moderate
{
	function skin_moderate()
	{
		$this->global			= new skin_global;
	}

	function move_topic($t,$forum_listing)
	{
		global $icebb;

		$wha	= sprintf($icebb->lang['where_move'],$t['title']);
		
		$code	= $this->global->header();
		$code  .= <<<EOF
<div class='borderwrap'>
	<h2>{$wha}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='moderate' />
		<input type='hidden' name='func' value='topic_move' />
		<input type='hidden' name='topicid' value='{$t['tid']}' />
{$da_tids}
		<table width='100%' cellpadding='2' cellspacing='1'>
			<tr>
				<td class='row2' width='20%'>
					{$icebb->lang['forum_select']}
				</td>
				<td class='row1'>
					<select name='move_where' class='form_dropdown'>
						{$forum_listing}
					</select>
				</td>
			</tr>
			<tr>
				<td class='row1' colspan='2'>
					<label><input type='checkbox' name='create_shadow_topic' value='1' checked='checked' /> {$icebb->lang['create_shadow_topic']}</label>
				</td>
			</tr>
			<tr>
				<td class='row3' colspan='2' style='text-align:center'>
					<input type='submit' value='{$icebb->lang['topic_move']}' class='form_button' />
				</td>
			</tr>
		</table>
	</form>
</div>

EOF;
		$code  .= $this->global->footer();

		return $code;
	}

	function move_topic_multi($tids,$t,$forum_listing)
	{
		global $icebb;
		
		$da_tids	= "";
		foreach($tids as $tid)
		{
			$da_tids .= "\t\t<input type='hidden' name='checkedtids[]' value='{$tid}' />\n";
		}

		$code	= $this->global->header();
		$code  .= <<<EOF
<div class='borderwrap'>
	<h2>{$icebb->lang['where_move_multi']}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='moderate' />
		<input type='hidden' name='func' value='topic_move' />
{$da_tids}
		<table width='100%' cellpadding='2' cellspacing='1'>
			<tr>
				<td class='row2' width='20%'>
					{$icebb->lang['forum_select']}
				</td>
				<td class='row1'>
					<select name='move_where' class='form_dropdown'>
						{$forum_listing}
					</select>
				</td>
			</tr>
			<tr>
				<td class='row1' colspan='2'>
					<label><input type='checkbox' name='create_shadow_topic' value='1' checked='checked' /> {$icebb->lang['create_shadow_topic']}</label>
				</td>
			</tr>
			<tr>
				<td class='row3' colspan='2' style='text-align:center'>
					<input type='submit' value='{$icebb->lang['topic_move']}' class='form_button' />
				</td>
			</tr>
		</table>
	</form>
</div>

EOF;
		$code  .= $this->global->footer();

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

	function confirm_delete($type,$to_delete)
	{
		global $icebb;

		$code		= $this->global->header();
		$code	   .= <<<EOF
<div class='borderwrap'>
EOF;

		if($type=='topic')
		{
			$delete_confirm= sprintf($icebb->lang['topic_delete_confirm'],$to_delete['title']);
		
			$code  .= <<<EOF
<h2>{$delete_confirm}</h2>
<center style='font-size:140%'>
<a href='{$icebb->base_url}act=moderate&func=topic_delete&topicid={$to_delete['tid']}&confirm=1'>{$icebb->lang['yes']}</a> &nbsp; <a href='{$icebb->base_url}topic={$to_delete['tid']}'>{$icebb->lang['no']}</a>
</center>

EOF;
		}
		else {
			$code  .= <<<EOF
<h2>{$icebb->lang['post_delete_confirm']}</h2>
<center style='font-size:140%'>
<a href='{$icebb->base_url}act=moderate&func=posts_delete&pid={$to_delete['pid']}&confirm=1'>{$icebb->lang['yes']}</a> &nbsp; <a href='{$icebb->base_url}topic={$to_delete['ptopicid']}'>{$icebb->lang['no']}</a>
</center>

EOF;
		}

		$code	   .= <<<EOF
</div>

EOF;
		$code	   .= $this->global->footer();

		return $code;
	}

	function merge_topic($t)
	{
		global $icebb;

		$witwat= sprintf($icebb->lang['topic_merge_withwhat'],$t['title']);

		$code  = $this->global->header();
		$code .= <<<EOF
<div class='borderwrap'>
	<h2>{$witwat}</h2>
	<form action='index.php' method='post'>
		<input type='hidden' name='act' value='moderate' />
		<input type='hidden' name='func' value='topic_merge' />
		<input type='hidden' name='topicid' value='{$t['tid']}' />
		<table width='100%' cellpadding='2' cellspacing='1'>
			<tr>
				<td class='row2' width='40%'>
					{$icebb->lang['topic_merge_id']}
				</td>
				<td class='row1'>
					<input type='text' name='merge_with' value='' class='form_textbox' />
				</td>
			</tr>
			<tr>
				<td class='row3' colspan='2' style='text-align:center'>
					<input type='submit' value='{$icebb->lang['topic_merge']}' class='form_button' />
				</td>
			</tr>
		</table>
	</form>
</div>

EOF;
		$code .= $this->global->footer();

		return $code;
	}

	function show_prune_page($f,$forumlist)
	{
		global $icebb;

		$title	= sprintf($icebb->lang['prune_topics_in_forum'],$f['name']);

		$code	= $this->global->header();
		$code  .= <<<EOF
<div class='borderwrap'>
	<h2>{$title}</h2>
	<form action='{$icebb->base_url}act=moderate&amp;func=prune' method='post'>
	<input type='hidden' name='forum' value='{$f['fid']}' />
	<table width='100%' cellpadding='2' cellspacing='1' style='margin-top:-1px'>
		<tr>
			<td class='row3' colspan='2'>
				<strong>{$icebb->lang['filter']}</strong>
			</td>
		</tr>
		<tr>
			<td class='row2' width='40%'>
				<strong>{$icebb->lang['started_by']}</strong>
			</td>
			<td class='row1'>
				<input type='text' name='starter' value='' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['num_replies']}</strong>
			</td>
			<td class='row1'>
				<select name='num_replies_opt' class='form_dropdown'>
					<option value='less'>{$icebb->lang['lt']}</option>
					<option value='more'>{$icebb->lang['gt']}</option>
				</select>
				<input type='text' name='num_replies' value='' size='5' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['no_replies_in']}</strong>
			</td>
			<td class='row1'>
				<input type='text' name='no_replies_in' value='' size='5' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['topic_state']}</strong>
			</td>
			<td class='row1'>
				<select name='topic_state' class='form_dropdown'>
					<option value='all'>{$icebb->lang['topic_state_all']}</option>
					<option value='unlocked'>{$icebb->lang['topic_state_unlocked']}</option>
					<option value='locked'>{$icebb->lang['topic_state_locked']}</option>
					<option value='moved'>{$icebb->lang['topic_state_ml']}</option>
				</select>
			</td>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['incl_pinned']}</strong>
			</td>
			<td class='row1'>
				<input type='checkbox' name='pinned' checked='checked' value='1' class='form_checkbox' />
			</td>
		</tr>
		<tr>
			<td class='row3' colspan='2'>
				<strong>{$icebb->lang['opt']}</strong>
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['move_to']}</strong>
			</td>
			<td class='row1'>
				<select name='move_to' class='form_dropdown'>
					<option value='nomove' selected='selected'>{$icebb->lang['move_dont']}</option>
					<option disabled='disabled' class='optgroup'>------------</option>
{$forumlist}
				</select>
			</td>
		</tr>
		<tr>
			<td class='row3' colspan='2'>
				<strong>{$icebb->lang['secure_pass']}</strong>
			</td>
		</tr>
		<tr>
			<td class='row2'>
				<strong>{$icebb->lang['current_pass']}</strong>
			</td>
			<td class='row1'>
				<input type='password' name='pass' value='' class='form_textbox' />
			</td>
		</tr>
		<tr>
			<td class='row3' colspan='2' style='text-align:center'>
				<input type='submit' name='do_prune' value='{$icebb->lang['topic_prune_start']}' onclick="s=confirm('{$icebb->lang['topic_prune_confirm']}');if(!s) return false" class='form_button' />
				<!--input type='submit' name='preview' value='Preview' class='form_button' --/>
			</td>
		</tr>
	</table>
</div>

EOF;
		$code  .= $this->global->footer();

		return $code;
	}
}
?>
