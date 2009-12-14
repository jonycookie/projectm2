<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.0
//******************************************************//
// moderation module
// $Id: moderate.php 823 2007-05-16 22:00:07Z daniel159 $
//******************************************************//
// Quote of the day:
// [16:26] <C_J_Pro> kinky brits...wtf does kinky mean?!?! :P
// O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o_O_o
// I used the word "kinky" over 15 times in this module.
// (Wow, what a waste of time.)
//******************************************************//

class moderate
{
	function run()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		$this->html				= $icebb->skin->load_template('moderate');
		$icebb->lang			= $std->learn_language('moderate');
		$this->lang =& $icebb->lang; // fixes some stuff
		
		////////////////////////////////////////////////////////
		// Multiple topics
		////////////////////////////////////////////////////////
		
		if(!empty($icebb->input['checkedtids']))
		{
			$this->do_multi_tids($icebb->input['checkedtids']);
	
			$icebb->skin->html_insert($this->output);
			$icebb->skin->do_output();
			
			return;
		}
		
		////////////////////////////////////////////////////////
		// Single topic
		////////////////////////////////////////////////////////
		
		if($icebb->user['g_is_mod'] || $this->is_mod)
		{
			$topic_info				= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='{$icebb->input['topicid']}'");
			
			// can we moderate here?
			if(in_array($topic_info['forum'],$icebb->user['moderate']))
			{
				$this->is_mod		= 1;
			}
			
			$this->topic_info		= $topic_info;	
		
			// send our funcs to a kinky parser that does the kinky function
			switch($icebb->input['func'])
			{
				// -- TOPIC -- //
				case 'topic_pin':
					$this->topic('pin');
					break;
				case 'topic_unpin':
					$this->topic('unpin');
					break;
				case 'topic_move':
					$this->topic('move');
					break;
				case 'topic_lock':
					$this->topic('lock');
					break;
				case 'topic_unlock':
					$this->topic('unlock');
					break;
				case 'topic_delete':
					$this->topic('delete');
					break;
				case 'topic_merge':
					$this->topic('merge');
					break;
				case 'topic_hideshow':
					$this->topic('hide_show');
					break;
				case 'topic_hide':
					$this->topic('hide');
					break;
				case 'topic_show':
					$this->topic('show');
					break;
				case 'topic_edit_title':
					$this->topic('edit_title');
					break;
				// -- SELECTED POSTS -- //
				case 'posts_merge':
					$this->posts('merge');
					break;
				case 'posts_split':
					$this->posts('split');
					break;
				case 'posts_delete':
					$this->posts('delete');
					break;
				case 'posts_hideshow':
					$this->posts('hide_show');
					break;
				// -- OTHER -- //
				case 'prune':
					$this->prune();
					break;
				default:
					$std->error('kinky',1);
			}
		}
		else {
			$std->error($this->lang['unauthorized'],1);
		}
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function do_multi_tids($tids)
	{
		global $icebb,$db,$std;

		////////////////////////////////////////////////////////
		// What topics are we working with?
		////////////////////////////////////////////////////////

		if(is_array($tids))
		{
			$tits			= $tids;
		}
		else {
			$tits			= explode(',',$tids);
		}
		
		$tids				= array();
		foreach($tits as $k => $v)
		{
			if(!in_array($v,$tids) && !empty($v))
			{
				$tids[]		= $v;
			}
		}
		
		if(!is_array($tids) || count($tids) < 1)
		{
			$std->error($this->lang['select_some_topics']);
		}
		
		////////////////////////////////////////////////////////
		// Do it!
		////////////////////////////////////////////////////////
		
		// merge needs to be done outside of the foreach
		if($icebb->input['func'] == 'topic_merge')
		{
			$this->merge_topics($tids);
		}
		// so does move (to fix IceBB#366)
		if($icebb->input['func'] == 'topic_move')
		{
			return $this->move_topics($tids);
		}
		
		foreach($tids as $tid)
		{
			$topic_info				= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='{$tid}'");
			
			// can we moderate here?
			if($icebb->user['g_is_mod']=='1' || in_array($topic_info['forum'],$icebb->user['moderate']))
			{
				$this->is_mod		= 1;
			}
			else {
				$std->error($this->lang['unauthorized'],1);
			}
			
			$this->topic_info		= $topic_info;
			$icebb->input['topicid']= $tid;
		
			switch($icebb->input['func'])
			{
				// -- TOPIC -- //
				case 'topic_pin':
					$this->topic('pin');
					break;
				case 'topic_unpin':
					$this->topic('unpin');
					break;
				case 'topic_move':
					$this->topic('move');
					break;
				case 'topic_lock':
					$this->topic('lock');
					break;
				case 'topic_unlock':
					$this->topic('unlock');
					break;
				case 'topic_delete':
					$this->topic('delete');
					break;
				case 'topic_merge':
					break;
				case 'topic_hideshow':
					$this->topic('hide_show');
					break;
				case 'topic_hide':
					$this->topic('hide');
					break;
				case 'topic_show':
					$this->topic('show');
					break;
				case 'topic_edit_title':
					$this->topic('edit_title');
					break;
				default:
					$std->error('kinky');
			}
		}
		
		$std->bouncy_bouncy($this->lastmsg,$_SERVER['HTTP_REFERER']);
	}
	
	// HANDLE TOPIC MODERATION REQUESTS
	// --------------------------------
	// O_o
	
	function topic($handle)
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		$topic_info							= $this->topic_info;
		
		// handle the kinky handle
		switch($handle)
		{
			// pin the kinky topic
			case 'pin':
				$db->query("UPDATE icebb_topics SET is_pinned='1' WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
						
				$ttitle = $db->fetch_result("SELECT `title` FROM `icebb_topics` WHERE `tid`='{$icebb->input['topicid']}'");
				$std->ra_log("Topic Pinned: <a href=\'{$icebb->base_url}topic={$icebb->input['topicid']}\'>{$ttitle['title']}</a>",$icebb->user['username']);	

				$this->i_am_done($icebb->lang['topic_pinned'],"index.php?topic={$icebb->input['topicid']}");
				break;
			// pin the kinky topic
			case 'unpin':
				$db->query("UPDATE icebb_topics SET is_pinned='0' WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
				
				$ttitle = $db->fetch_result("SELECT `title` FROM `icebb_topics` WHERE `tid`='{$icebb->input['topicid']}'");
				$std->ra_log("Topic Unpinned: <a href=\'{$icebb->base_url}topic={$icebb->input['topicid']}\'>{$ttitle['title']}</a>",$icebb->user['username']);

				$this->i_am_done($icebb->lang['topic_unpinned'],"index.php?topic={$icebb->input['topicid']}");
				break;
			// move the kinky topic
			case 'move':
				if(!empty($icebb->input['move_where']))
				{
					if($icebb->input['move_where'] == $topic_info['forum'])
					{
						$std->error($this->lang['nowhere_to_move']);
					}
		
					$db->query("UPDATE icebb_topics SET forum='{$icebb->input['move_where']}' WHERE tid='{$topic_info['tid']}}' LIMIT 1");
			
					if(!empty($icebb->input['create_shadow_topic']))
					{
						$newtid				= $db->fetch_result("SELECT tid FROM icebb_topics ORDER BY tid DESC LIMIT 1");
				
						$db->insert('icebb_topics',array(
							'tid'			=> $newtid['tid']+1,
							'forum'			=> $topic_info['forum'],
							'icon'			=> $topic_info['icon'],
							'title'			=> $topic_info['title'],
							'description'	=> $topic_info['description'],
							'snippet'		=> $topic_info['snippet'],
							'replies'		=> $topic_info['replies'],
							'views'			=> $topic_info['views'],
							'starter'		=> $topic_info['starter'],
							'has_poll'		=> $topic_info['has_poll'],
							'rating'		=> $topic_info['rating'],
							'moved_to'		=> $topic_info['tid'],
							'lastpost_time'	=> $topic_info['lastpost_time'],
							'lastpost_author'=> $topic_info['lastpost_author'],
							'is_locked'		=> $topic_info['is_locked'],
							'is_pinned'		=> $topic_info['is_pinned'],
							'is_hidden'		=> $topic_info['is_hidden'],
						));
					}
					
					$num_replies			= (int) $topic_info['replies'];
				
					$lastpost				= $db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$icebb->input['move_where']}' ORDER BY lastpost_time DESC LIMIT 1");
					$db->query("UPDATE icebb_forums SET lastpostid='{$lastpost['tid']}',lastpost_title='" . $db->escape_string($lastpost['title']) . "',lastpost_author='{$lastpost['lastpost_author']}',topics=topics+1,replies=replies+{$num_replies} WHERE fid='{$icebb->input['move_where']}' LIMIT 1");

					$lastpost				= $db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$topic_info['forum']}' ORDER BY lastpost_time DESC LIMIT 1");
					$db->query("UPDATE icebb_forums SET lastpostid='{$lastpost['tid']}',lastpost_title='" . $db->escape_string($lastpost['title']) . "',lastpost_author='{$lastpost['lastpost_author']}',topics=topics-1,replies=replies-{$num_replies} WHERE fid='{$topic_info['forum']}' LIMIT 1");
					
					$std->log('moderate', "Topic #{$topic_info['tid']} moved", $icebb->user['username']);

					$this->i_am_done($icebb->lang['topic_moved'], "{$icebb->base_url}topic={$topic_info['tid']}}");
				}
				else {
					$this->create_proper_nav($topic_info['forum']);
					$icebb->nav[]				= $icebb->lang['topic_move'];
		
					$forumlist					= $std->get_forum_listing();
					$forumlist					= $this->forum_list_children($forumlist,'');
			
					$this->output				= $this->html->move_topic($topic_info,$forumlist);
				}
				break;
			// merge topics
			case 'merge':
				$merge_with						= intval($icebb->input['merge_with']);
				if(!empty($merge_with))
				{
					$db->query("UPDATE icebb_posts SET ptopicid={$merge_with} WHERE ptopicid='{$icebb->input['topicid']}'");
					$db->query("DELETE FROM icebb_topics WHERE tid='{$icebb->input['topicid']}'");
					
					// fix pis_firstpost (this probably isn't the best way to do this)
					$db->query("UPDATE icebb_posts SET pis_firstpost=0 WHERE ptopicid={$merge_with}");
					$db->query("UPDATE icebb_posts SET pis_firstpost=1 WHERE ptopicid={$merge_with} ORDER BY pdate LIMIT 1");
					
					$std->log('moderate',"Topics #{$icebb->input['topicid']} and #{$merge_with} merged",$icebb->user['username']);
					
					$this->i_am_done($icebb->lang['topic_merged'],"{$icebb->base_url}topic={$merge_with}");
				}
				else {
					$this->create_proper_nav($topic_info['forum']);
					$icebb->nav[]				= $icebb->lang['topic_merge'];
				
					$this->output				= $this->html->merge_topic($topic_info);
				}
				break;
			// lock the kinky topic
			case 'lock':
				$db->query("UPDATE icebb_topics SET is_locked='1' WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
				
				$ttitle = $db->fetch_result("SELECT `title` FROM `icebb_topics` WHERE `tid`='{$icebb->input['topicid']}'");
		
				$std->ra_log(sprintf($this->lang['topic_locked_ra'],$db->escape_string("<a href='{$icebb->base_url}topic={$icebb->input['topicid']}'>{$ttitle['title']}</a>")),$icebb->user['username']);
				
				$this->i_am_done($icebb->lang['topic_locked'],"index.php?topic={$icebb->input['topicid']}");
				break;
			// unlock the kinky topic
			case 'unlock':
				$db->query("UPDATE icebb_topics SET is_locked='0' WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
				
				$ttitle = $db->fetch_result("SELECT `title` FROM `icebb_topics` WHERE `tid`='{$icebb->input['topicid']}'");
				
				$std->ra_log(sprintf($this->lang['topic_unlocked_ra'],$db->escape_string("<a href='{$icebb->base_url}topic={$icebb->input['topicid']}'>{$ttitle['title']}</a>")),$icebb->user['username']);
				
				$this->i_am_done($icebb->lang['topic_unlocked'],"index.php?topic={$icebb->input['topicid']}");
				break;
			// delete the kinky topic
			case 'delete':
				//if($icebb->input['confirm']=='1')
				if(1==1)
				{
					require_once('includes/apis/core.api.php');
					$api_core			= new api_core(&$icebb,&$db,&$std);
					require_once('includes/apis/post.api.php');
					$this->post_lib		= new post_lib(&$this,&$api_core);
					
					$p					= $db->fetch_result("SELECT pauthor_id FROM icebb_posts WHERE ptopicid='{$topic_info['tid']}' ORDER BY pid DESC LIMIT 1");
					
					$this->post_lib->delete_post($p['pid'],$p['pauthor_id'],$topic_info,1);
					
					$std->log('moderate',"Post #{$p['pid']} deleted",$icebb->user['username']);
					
					$cache_result3				= $db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
					$cache_result31				= $db->fetch_result("SELECT COUNT(*) as topics FROM icebb_topics");
					$cache_result32				= $db->fetch_result("SELECT COUNT(*) as replies FROM icebb_posts WHERE pis_firstpost!=1");
					$icebb->cache['stats']['posts']= $cache_result3['posts'];
					$icebb->cache['stats']['topics']= $cache_result31['topics'];
					$icebb->cache['stats']['replies']= $cache_result32['replies'];
					$std->recache($icebb->cache['stats'],'stats');
					
					$this->i_am_done($icebb->lang['topic_deleted'],"index.php?forum={$topic_info['forum']}");
				}
				else {
					$this->output		= $this->html->confirm_delete('topic',$topic_info);
				}
				break;
			// change approved status
			case 'hide_show':
				if($topic_info['is_hidden']=='0')
				{
					$db->query("UPDATE icebb_topics SET is_hidden=1 WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
					$this->i_am_done($icebb->lang['topic_made_hidden'],"index.php?topic={$icebb->input['topicid']}");
				}
				else {
					$db->query("UPDATE icebb_topics SET is_hidden=0 WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
					$this->i_am_done($icebb->lang['topic_made_visible'],"index.php?topic={$icebb->input['topicid']}");
				}
				break;
			case 'hide':
				$db->query("UPDATE icebb_topics SET is_hidden=1 WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
				$this->i_am_done($icebb->lang['topic_made_hidden'],"index.php?topic={$icebb->input['topicid']}");
				break;
			case 'show':
				$db->query("UPDATE icebb_topics SET is_hidden=0 WHERE tid='{$icebb->input['topicid']}' LIMIT 1");
				$this->i_am_done($icebb->lang['topic_made_visible'],"index.php?topic={$icebb->input['topicid']}");
				break;
			case 'edit_title':
				$icebb->input['newtitle']	= trim($icebb->input['newtitle']);
				if(empty($icebb->input['newtitle']))
				{
					$std->error('empty',1);
					exit();
				}
			
				$db->query("UPDATE icebb_topics SET title='{$icebb->input['newtitle']}' WHERE tid='{$icebb->input['topicid']}'");

				$std->ra_log($this->lang['topic_edited_ra'],"<a href='{$icebb->base_url}topic={$icebb->input['topicid']}'>{$icebb->input['newtitle']}</a>",$icebb->user['username']);
				
				break;
		}
	}

	function merge_topics($tids)
	{
		global $icebb,$db,$std;
		
		$topic1_info					= $db->fetch_result("SELECT forum FROM icebb_topics WHERE tid='{$tids[0]}'");
		
		// can we moderate here?
		if(!$icebb->user['g_is_mod'] && !in_array($topic1_info['forum'],$icebb->user['moderate']))
		{
			$std->error($this->lang['unauthorized'],1);
		}
		
		rsort($tids);
		
		$merge_into						= intval($tids[count($tids)-1]);
		$to_merge						= $tids;
		unset($to_merge[count($to_merge)-1]);
		
		foreach($to_merge as $tid)
		{
			$tid						= intval($tid);
			
			$db->query("UPDATE icebb_posts SET ptopicid={$merge_into} WHERE ptopicid={$tid}");
			$db->query("DELETE FROM icebb_topics WHERE tid={$tid}");
		}
		
		// fix pis_firstpost (this probably isn't the best way to do this)
		$db->query("UPDATE icebb_posts SET pis_firstpost=0 WHERE ptopicid={$merge_into}");
		$db->query("UPDATE icebb_posts SET pis_firstpost=1 WHERE ptopicid={$merge_into} ORDER BY pdate LIMIT 1");
		
			
		$this->i_am_done($icebb->lang['topic_merged'],"{$icebb->base_url}topic={$merge_with}");
	}
	
	function move_topics($tids)
	{
		global $icebb,$db,$std;
		
		$num_replies					= 0;
		$topic1_info					= $db->fetch_result("SELECT forum,title FROM icebb_topics WHERE tid='{$tids[0]}'");
		
		// can we moderate here?
		if(!$icebb->user['g_is_mod'] && !in_array($topic1_info['forum'],$icebb->user['moderate']))
		{
			$std->error($this->lang['unauthorized'],1);
		}
		
		if(!empty($icebb->input['move_where']))
		{
			if($icebb->input['move_where'] == $topic1_info['forum'])
			{
				$std->error($this->lang['nowhere_to_move']);
			}
		
			foreach($tids as $tid)
			{
				$topic_info				= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='{$tid}'");
			
				$db->query("UPDATE icebb_topics SET forum='{$icebb->input['move_where']}' WHERE tid='{$tid}' LIMIT 1");
				
				if(!empty($icebb->input['create_shadow_topic']))
				{
					$newtid				= $db->fetch_result("SELECT tid FROM icebb_topics ORDER BY tid DESC LIMIT 1");
					
					$db->insert('icebb_topics',array(
						'tid'			=> $newtid['tid']+1,
						'forum'			=> $topic_info['forum'],
						'icon'			=> $topic_info['icon'],
						'title'			=> $topic_info['title'],
						'description'	=> $topic_info['description'],
						'snippet'		=> $topic_info['snippet'],
						'replies'		=> $topic_info['replies'],
						'views'			=> $topic_info['views'],
						'starter'		=> $topic_info['starter'],
						'has_poll'		=> $topic_info['has_poll'],
						'rating'		=> $topic_info['rating'],
						'moved_to'		=> $topic_info['tid'],
						'lastpost_time'	=> $topic_info['lastpost_time'],
						'lastpost_author'=> $topic_info['lastpost_author'],
						'is_locked'		=> $topic_info['is_locked'],
						'is_pinned'		=> $topic_info['is_pinned'],
						'is_hidden'		=> $topic_info['is_hidden'],
					));
					
					$num_replies	   += $topic_info['replies'];
				}
				
				$lastpost				= $db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$icebb->input['move_where']}' ORDER BY lastpost_time DESC LIMIT 1");
				$db->query("UPDATE icebb_forums SET lastpostid='{$lastpost['tid']}',lastpost_title='" . $db->escape_string($lastpost['title']) . "',lastpost_author='{$lastpost['lastpost_author']}',topics=topics+1,replies=replies+{$num_replies} WHERE fid='{$icebb->input['move_where']}' LIMIT 1");

				$lastpost				= $db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$topic1_info['forum']}' ORDER BY lastpost_time DESC LIMIT 1");
				$db->query("UPDATE icebb_forums SET lastpostid='{$lastpost['tid']}',lastpost_title='" . $db->escape_string($lastpost['title']) . "',lastpost_author='{$lastpost['lastpost_author']}',topics=topics-1,replies=replies-{$num_replies} WHERE fid='{$topic1_info['forum']}' LIMIT 1");

				$std->log('moderate',"Topic #{$tid} moved",$icebb->user['username']);
			}

			$std->redirect("{$icebb->base_url}forum={$icebb->input['move_where']}",$icebb->lang['topics_moved']);
		}
		else {
			$this->create_proper_nav($topic1_info['forum']);
			$icebb->nav[]				= $icebb->lang['topic_move'];
		
			$forumlist					= $std->get_forum_listing();
			$forumlist					= $this->forum_list_children($forumlist,'');
			
			$this->output				= $this->html->move_topic_multi($tids,$topic1_info,$forumlist);
		}
	}
	
	
	// HANDLE POST MODERATION REQUESTS
	// -------------------------------
	// O_o
	
	function posts($handle)
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		if(is_array($icebb->input['checkedpids']))
		{
			$pids_direct					= $icebb->input['checkedpids'];
		}
		else {
			$pids_direct					= explode(',',$icebb->input['checkedpids']);
		}
		
		$pids								= array();
		foreach($pids_direct as $pid)
		{
			if(!empty($pid))
			{
				$pids[]						= $pid;
				$post_info[$pid]			= $db->fetch_result("SELECT * FROM icebb_posts WHERE pid='".intval($pid)."'");
			}
		}
		
		if(!is_array($post_info))
		{
			$std->error('kinky');
		}
		
		ksort($post_info);
		
		// handle the kinky handle
		switch($handle)
		{
			// split the post into a topic
			case 'split':
				break;
			// merge the posts
			case 'merge':
				$pon							= 1;
				$newpost						= '';
				
				foreach($post_info as $p)
				{
					if($pon==1)
					{
						$post1					= $p;
					}
				
					$newpost.= "{$p['ptext']}\n\n";
					$db->query("DELETE FROM icebb_posts WHERE pid='{$p['pid']}' LIMIT 1");
					$pon++;
				}
				
				$db->insert('icebb_posts',array(
								'pid'			=> $post1['pid'],
								'ptopicid'		=> $post1['ptopicid'],
								'pauthor_id'	=> $post1['pauthor_id'],
								'pauthor'		=> $post1['pauthor'],
								'pauthor_ip'	=> $post1['pauthor_ip'],
								'pdate'			=> $post1['pdate'],
								'ptext'			=> $newpost,
				));
				
				$std->log('moderate',"Posts #{$p['pid']} and #{$post1['pid']} merged",$icebb->user['username']);
				
				$this->i_am_done($this->lang['posts_merged'],"{$icebb->base_url}topic={$post1['ptopicid']}&pid={$post1['pid']}");
				
				break;
			// delete the kinky post
			case 'delete':
				require('includes/apis/core.api.php');
				$api_core			= new api_core(&$icebb,&$db,&$std);
				require('includes/apis/post.api.php');
				$this->post_lib		= new post_lib(&$this,&$api_core);
			
				$lasttopic			= $db->fetch_result("SELECT tid FROM icebb_topics ORDER BY tid DESC LIMIT 1");				
			
				foreach($post_info as $p)
				{
					if($p['pis_firstpost']) continue;
				
					$topic				= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='{$p['ptopicid']}'");
					$this->post_lib->delete_post($p['pid'],$p['pauthor_id'],$topic,0);
					
					$std->log('moderate',"Post #{$p['pid']} deleted",$icebb->user['username']);
				}
				
				$cache_result3				= $db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
				$cache_result31				= $db->fetch_result("SELECT COUNT(*) as topics FROM icebb_topics");
				$cache_result32				= $db->fetch_result("SELECT COUNT(*) as replies FROM icebb_posts WHERE pis_firstpost!=1");
				$icebb->cache['stats']['posts']= $cache_result3['posts'];
				$icebb->cache['stats']['topics']= $cache_result31['topics'];
				$icebb->cache['stats']['replies']= $cache_result32['replies'];
				$std->recache($icebb->cache['stats'],'stats');
				
				$this->i_am_done($this->lang['posts_deleted'],"index.php?topic={$p['ptopicid']}");

				break;
			// change approved status
			case 'hide_show':
				foreach($post_info as $p)
				{
					if($p['phide']=='0')
					{
						$db->query("UPDATE icebb_posts SET phide='1' WHERE pid='{$p['pid']}' LIMIT 1");
						$this->i_am_done($icebb->lang['post_made_hidden'],"{$icebb->base_url}topic={$p['ptopicid']}");
					}
					else {
						$db->query("UPDATE icebb_posts SET phide='0' WHERE pid='{$p['pid']}' LIMIT 1");
						$this->i_am_done($icebb->lang['post_made_visible'],"{$icebb->base_url}topic={$p['ptopicid']}");
					}
				}
				break;
		}
	}
	
	// END HANDLE POST MODERATION REQUESTS
	// -----------------------------------
	
	// OTHER
	// -----------------------------------
	
	function prune()
	{
		global $icebb,$db,$std;
	
		$f						= $db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$icebb->input['forum']}'");
	
		$this->create_proper_nav($f['fid']);
	
		$icebb->nav[]			= $icebb->lang['prune_topics'];
	
		if(!empty($icebb->input['do_prune']))
		{
			////////////////////////////////////////////////////////
			// Check password for extra security
			// (otherwise if cookies are stolen, prune can be run)
			////////////////////////////////////////////////////////
		
			if(md5(md5($icebb->input['pass']).$icebb->user['pass_salt'])!=$icebb->user['password'])
			{
				$std->error($this->lang['invalid_password'],1);
			}
		
			////////////////////////////////////////////////////////
			// Generate query
			////////////////////////////////////////////////////////
			
			$where_clauses[]	= "forum={$f['fid']}";
			
			if(!empty($icebb->input['starter']))
			{
				$where_clauses[]= "started='{$icebb->input['starter']}'";
			}
			
			if(!empty($icebb->input['num_replies']))
			{
				if($icebb->input['num_replies_opt']=='more')
				{
					$symbol		= '>';
				}
				else {
					$symbol		= '<';
				}
				
				$where_clauses[]= "replies{$symbol}".intval($icebb->input['num_replies']);
			}
			
			if(!empty($icebb->input['no_replies_in']))
			{
				$cut_off		= time()-(86400*intval($icebb->input['no_replies_in']));
				$where_clauses[]= "lastpost_time<={$cut_off}";
			}
			
			if(!empty($icebb->input['topic_state']))
			{
				switch($icebb->input['topic_state'])
				{
					case 'unlocked':
						$where_clauses[]= "is_locked=0";
						break;
					case 'locked':
						$where_clauses[]= "is_locked=1";
						break;
					case 'moved':
						$where_clauses[]= "moved_to!=0";
						break;
				}
			}
			
			if(empty($icebb->input['pinned']))
			{
				$where_clauses[]= "pinned=0";
			}
			
			$where				= implode(' AND ',$where_clauses);
			
			////////////////////////////////////////////////////////
			// Execute query
			////////////////////////////////////////////////////////
			
			$db->query("SELECT * FROM icebb_topics WHERE {$where}");
			while($t			= $db->fetch_row())
			{
				$topics[]		= $t['tid'];
			}
			
			if(!is_array($topics))
			{
				$std->error($this->lang['no_topics_to_prune']);
			}
			
			$tids				= implode(',',$topics);
			
			$db->query("DELETE FROM icebb_topics WHERE tid IN({$tids})");
			$db->query("DELETE FROM icebb_posts WHERE ptopicid IN({$tids})");
			
			$std->log('moderate',"Pruned topics in forum {$f['fid']}",$icebb->user['username']);
			$std->bouncy_bouncy($icebb->lang['pruned'],"{$icebb->base_url}forum={$f['fid']}");
		}
		else {
			$forumlist			= $std->get_forum_listing();
			$forumlist			= $this->forum_list_children($forumlist,'');
		
			$this->output		= $this->html->show_prune_page($f,$forumlist);
		}
	}
	
	function i_am_done($msg,$url)
	{
		global $icebb,$db,$std;
		
		// log
		//$std->log('moderate',"Topic {$icebb->input['topicid']}: {$msg}",$icebb->user['username']);
		
		if(empty($icebb->input['checkedtids']))
		{
			$std->bouncy_bouncy($msg,$url);
		}
		
		$this->lastmsg			= $msg;
	}
	
	function forum_list_children($list,$fn)
	{
		global $icebb,$db,$config,$std;
		
		$c						= 0;
		
		if(is_array($list))
		{
			foreach($list as $f)
			{
				if(!empty($f['redirecturl']))
				{
					continue;
				}
			
				$l			   .= $this->html->forum_row($f);
				
				$l			   .= $this->forum_list_children($list[$c]['children'],$f['fid']);
				$c++;
			}
		}
		
		return $l;
	}
	
	function create_proper_nav($fparent)
	{
		global $icebb,$db,$std;

		while($fparent		   != 0)
		{
			$fo					= $icebb->cache['forums'][$fparent];
			$nav[]				= "<a href='{$icebb->base_url}forum={$fo['fid']}'>{$fo['name']}</a>";
			$fparent			= $fo['parent'];
		}
		
		for($i=count($nav)-1;$i>=0;$i--)
		{
			$icebb->nav[]		= $nav[$i];
		}
	}
}
?>
