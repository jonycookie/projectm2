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
// post API
// $Id$
//******************************************************//

/**
 * IceBB Post API
 *
 * @package		IceBB
 * @version		0.9
 * @date		October 23, 2005
 */
 
class post_lib
{
	var $last_topic_id;
	var $last_post_id;

	/**
	 * Constructor
	 */
	function post_lib(&$calling_class,&$api_core)
	{
		global $icebb,$db;
	
		$this->calling_class= &$calling_class;
		$this->api_core	= &$api_core;
	}

	/**
	 * Create a new topic
	 *
	 * @argument		$fid		int			Forum ID
	 * @argument		$icon		string		Topic icon
	 * @argument		$title		string		Topic title
	 * @argument		$desc		string		Topic description
	 * @argument		$author_id	int			Author ID
	 * @argument		$content	string		Post content
	 * @argument		$has_poll	boolean		Does this topic have a poll?
	 */
	function new_topic($fid,$icon,$title,$desc,$author_id,$content,$has_poll=0)
	{
		global $icebb,$db;
		
		// fix backslash
		$content		= $this->api_core->db->escape_string($content);
		
		$topicsq		= $this->api_core->db->query("SELECT * FROM icebb_topics ORDER BY tid DESC LIMIT 1");
		$last_topic		= $this->api_core->db->fetch_row($topicsq);
		$tid			= $last_topic['tid']+1;
		$this->last_topic_id= $tid;
		
		$postsq			= $this->api_core->db->query("SELECT * FROM icebb_posts ORDER BY pid DESC LIMIT 1");
		$last_post		= $this->api_core->db->fetch_row($postsq);
		$pid			= $last_post['pid']+1;
		$this->last_post_id= $pid;
		
		$pedits			= serialize(array('original'=>array('ptext'=>$content,'pauthor_id'=>$author_id,'pdate'=>time())));
	
		$t				= array(
						'tid'			=> $tid,
						'forum'			=> $fid,
						'icon'			=> $icon,
						'title'			=> $title,
						'description'	=> $desc,
						'snippet'		=> substr($content,0,255),
						'starter'		=> $this->api_core->icebb->user['username'],
						'lastpost_time'	=> time(),
						'lastpost_author'=> $this->api_core->icebb->user['username'],
						'has_poll'		=> $has_poll,
						'views'			=> 0,
						);
	
		$this->api_core->db->insert('icebb_topics',$t);
		
		$p				= array(
						'pid'			=> $pid,
						'ptopicid'		=> $tid,
						'pauthor_id'	=> $author_id,
						'pauthor_ip'	=> $this->api_core->icebb->user['ip'],
						'pdate'			=> time(),
						'ptext'			=> $content,
						'pedits'		=> $pedits,
						'pis_firstpost'	=> '1',
						);
		
		$this->api_core->db->insert('icebb_posts',$p);
		
		$newraid = $this->api_core->db->fetch_result("SELECT `id` FROM `icebb_ra_logs` ORDER BY `id` DESC LIMIT 0,1");
		$newraid = $newraid['id'] + 1;
		
		$ra 			= array(
						'id'		=> $newraid,
						'time'		=> time(),
						'user'		=> $this->api_core->icebb->user['username'],
						'ip'		=> $this->api_core->icebb->client_ip,
						'action'	=> "Topic Created: <a href=\'{$this->api_core->icebb->base_url}topic={$tid}\'>{$title}</a>",
						'forum_id'	=> $fid,
				);
		
		$this->api_core->db->insert('icebb_ra_logs',$ra);
						
		$this->api_core->db->query("UPDATE icebb_forums SET topics=topics+1,lastpostid='{$tid}',lastpost_time=".time().",lastpost_title='{$this->api_core->icebb->input['ptitle']}',lastpost_author='{$this->api_core->icebb->user['username']}' WHERE fid='{$this->api_core->icebb->input['forum']}' LIMIT 1");
		$f								= $this->api_core->db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$this->api_core->icebb->input['forum']}'");
		while($f['parent']			   != '0')
		{
			$this->api_core->db->query("UPDATE icebb_forums SET topics=topics+1,lastpostid='{$tid}',lastpost_time=".time().",lastpost_title='{$this->api_core->icebb->input['ptitle']}',lastpost_author='{$this->api_core->icebb->user['username']}' WHERE fid='{$f['parent']}' LIMIT 1");
			$f							= $this->api_core->db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$f['parent']}'");
		}	

		$this->_update_post_count($author_id);

		$this->_update_stats();
		
		// update users last post timestamp
		if($icebb->user['id'] != '0')
		{
			$db->query("UPDATE icebb_users SET last_post='".time()."' WHERE id='{$icebb->user['id']}' LIMIT 1");
		}
		
		// subscriptions
		$this->calling_class->subscriptions->notify($t,$p);
	}
	
	/**
	 * Attach a poll to a topic
	 *
	 * @argument		$tid		int			Topic ID
	 * @argument		$question	string		Poll question
	 * @argument		$type		string		Poll type
	 * @argument		$choices	array		Poll choices
	 */
	function attach_poll($tid,$question,$type,$choices)
	{
		foreach($choices as $id => $c)
		{
			if(!empty($c))
			{
				$poll_choice[$id]= array('cid'=>$id,'ctext'=>$c);
			}
		}
		
		$this->api_core->db->insert('icebb_polls',array(
			'polltid'				=> $tid,
			'pollq'					=> $question,
			'type'					=> $type,
			'pollopt'				=> serialize($poll_choice),
		));
		
		$forum = $this->db->fetch_result("SELECT forum FROM icebb_topics WHERE tid='{$tid}' LIMIT 1");
		$forum = $forum['forum'];
		
		$newraid = $this->api_core->db->fetch_result("SELECT `id` FROM `icebb_ra_logs` ORDER BY `id` DESC LIMIT 0,1");
		$newraid = $newraid['id']+1;
		
		$ra 			= array(
						'id'		=> $newraid,
						'time'		=> time(),
						'user'		=> $this->api_core->icebb->user['username'],
						'ip'		=> $this->api_core->icebb->client_ip,
						'action'	=> "Poll Attached: <a href=\'{$this->api_core->icebb->base_url}topic={$tid}\'>{$title}</a>",
						'forum_id'	=> $forum,
				);
		
		$this->api_core->db->insert('icebb_ra_logs',$ra);
	}

	/**
	 * Edit a topic
	 *
	 * @argument		$pid		int			Post ID
	 * @argument		$tid		int			Topic ID
	 * @argument		$title		string		Topic title
	 * @argument		$desc		string		Topic description
	 * @argument		$content	string		Post content
	 * @argument		$icon		string		Topic icon (added at end for compatibility)
	 */
	function edit_topic($pid, $tid, $title, $desc, $content, $icon='')
	{
		// fix backslash
		$content			= $this->api_core->db->escape_string($content);
	
		$p					= $this->api_core->db->fetch_result("SELECT pedits FROM icebb_posts WHERE pid='{$pid}'");
		$pedits				= unserialize($p['pedits']);
		$edit_append		= time();
		$pedits["edit_{$edit_append}"]= array('ptext'=>$content,'pauthor_id'=>$this->api_core->icebb->user['id'],'pdate'=>time());
		$pedits				= $this->api_core->db->escape_string(serialize($pedits));

		//if($this->api_core->icebb->user['g_is_mod'])
		if(true)
		{
			$title			= trim($title);
			$desc			= trim($desc);
		
			if(empty($title))
			{
				$this->calling_class->show_post_form(array($this->lang['no_topic_title_entered']));
				exit();
			}
			
			if(!empty($icon))
			{
				$i			= ", icon='{$icon}'";
			}
		
			$this->api_core->db->query("UPDATE icebb_topics SET title='{$title}',description='{$desc}'{$i} WHERE tid='{$tid}'");
		}
		
		if($this->api_core->icebb->input['hide_edit_line'] == 1)
		{
			$show_edit = 0;
		}
		else
		{
			$show_edit = 1;
		}
		
		$this->api_core->db->query("UPDATE icebb_posts SET ptext='{$content}',pedit_show='{$show_edit}',pedit_author='{$this->api_core->icebb->user['username']}',pedit_time='".time()."',pedits='{$pedits}' WHERE pid='{$pid}'");
	}
 
	/**
	 * Add a reply to a topic
	 *
	 * @argument		$tid		int			Topic ID
	 * @argument		$author_id	int			Author ID
	 * @argument		$content	string		Post content
	 */
	function new_reply($tid,$author_id,$content)
	{
		global $icebb,$db;
		
		// fix backslash
		$content		= $this->api_core->db->escape_string($content);
		
		$topicsq		= $this->api_core->db->query("SELECT * FROM icebb_topics WHERE tid='{$this->api_core->icebb->input['reply']}' LIMIT 1");
		$last_topic		= $this->api_core->db->fetch_row($topicsq);
		$tid			= $last_topic['tid'];
		$this->last_topic_id= $pid;
		
		$postsq			= $this->api_core->db->query("SELECT * FROM icebb_posts ORDER BY pid DESC LIMIT 1");
		$last_post		= $this->api_core->db->fetch_row($postsq);
		$pid			= $last_post['pid']+1;
		$this->last_post_id= $pid;
	
		$pedits			= serialize(array('original'=>array('ptext'=>$content,'pauthor_id'=>$author_id,'pdate'=>time())));
	
		$p				= array(
						'pid'			=> $pid,
						'ptopicid'		=> $tid,
						'pauthor_id'	=> $author_id,
						'pauthor_ip'	=> $this->api_core->icebb->user['ip'],
						'pdate'			=> time(),
						'pedits'		=> $pedits,
						'ptext'			=> $content,
						);
	
		$this->api_core->db->insert('icebb_posts',$p);
		
		$newraid = $this->api_core->db->fetch_result("SELECT `id` FROM `icebb_ra_logs` ORDER BY `id` DESC LIMIT 0,1");
		$newraid = $newraid['id'] + 1;
		
		$ttitle = $this->api_core->db->fetch_result("SELECT `title` FROM `icebb_topics` WHERE `tid`='{$tid}'");
		
		$ra 			= array (
						'id'		=> $newraid,
						'time'		=> time(),
						'user'		=> $this->api_core->icebb->user['username'],
						'ip'		=> $this->api_core->icebb->client_ip,
						'action'	=> "New Reply in <a href=\'{$this->api_core->icebb->base_url}topic={$tid}\'>{$ttitle['title']}</a>",
						'forum_id'	=> $last_topic['forum'],
				);
		
		$this->api_core->db->insert('icebb_ra_logs',$ra);
		
		$this->api_core->db->query("UPDATE icebb_forums SET replies=replies+1,lastpostid='{$tid}',lastpost_time=".time().",lastpost_title='".addslashes($last_topic['title'])."',lastpost_author='{$this->api_core->icebb->user['username']}' WHERE fid='{$last_topic['forum']}' LIMIT 1");
		$f								= $this->api_core->db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$last_topic['forum']}'");
		while($f['parent']			   != '0')
		{
			$this->api_core->db->query("UPDATE icebb_forums SET replies=replies+1,lastpostid='{$tid}',lastpost_time=".time().",lastpost_title='".addslashes($last_topic['title'])."',lastpost_author='{$this->api_core->icebb->user['username']}' WHERE fid='{$f['parent']}' LIMIT 1");
			$f							= $this->api_core->db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$f['parent']}'");
		}

		$this->api_core->db->query("UPDATE icebb_topics SET replies=replies+1,lastpost_time='".time()."',lastpost_author='{$this->api_core->icebb->user['username']}' WHERE tid='{$tid}' LIMIT 1");

		$this->_update_post_count($author_id);
		
		$this->_update_stats();
		
		// update users last post timestamp
		if($icebb->user['id'] != '0')
		{
			$db->query("UPDATE icebb_users SET last_post='".time()."' WHERE id='{$icebb->user['id']}' LIMIT 1");
		}
		
		// subscriptions
		$this->calling_class->subscriptions->notify($last_topic,$p);
	}
	
	/**
	 * Edit a reply
	 *
	 * @argument		$pid		int			Post ID
	 * @argument		$content	string		Post content
	 */
	function edit_reply($pid,$content)
	{
		// fix backslash
		$content			= $this->api_core->db->escape_string($content);
	
		$p					= $this->api_core->db->fetch_result("SELECT pedits FROM icebb_posts WHERE pid='{$pid}'");
		$pedits				= unserialize($p['pedits']);
		$edit_append		= time();
		$pedits["edit_{$edit_append}"]= array('ptext'=>$post,'pauthor_id'=>$this->api_core->icebb->user['id'],'pdate'=>time());
		$pedits				= addslashes(serialize($pedits));
		
		if($this->api_core->icebb->input['hide_edit_line'] == 1)
		{
			$show_edit = 0;
		}
		else
		{
			$show_edit = 1;
		}
		
		$this->api_core->db->query("UPDATE icebb_posts SET ptext='{$content}',pedit_show='{$show_edit}',pedit_author='{$this->api_core->icebb->user['username']}',pedit_time='".time()."',pedits='{$pedits}' WHERE pid='{$this->api_core->icebb->input['edit']}'");
	}
	
	/**
	 * Delete a post
	 *
	 * @argument		$pid		int			Post ID
	 * @argument		$uid		int			Author ID
	 * @argument		$topic		array		Topic result array
	 * @argument		$is_topic	boolean		Is this post a topic?
	*/
	function delete_post($pid,$uid,$topic,$is_topic=0)
	{
		if($is_topic)
		{
			$replies				= array();
			$repliers				= array();
			$this->api_core->db->query("SELECT pid,pauthor_id FROM icebb_posts WHERE ptopicid='{$topic['tid']}'");
			while($r				= $this->api_core->db->fetch_row())
			{
				$replies[]			= $r;
				$repliers[$r['pauthor_id']]++;
			}
		
			// remove topic ratings and favorites
			$this->api_core->db->query("DELETE FROM icebb_favorites WHERE favtype='topic' AND favobjid='{$topic['tid']}'");
			
			// remove tags
			$tag_remove				= array();
			$this->api_core->db->query("SELECT tag_id FROM icebb_tagged WHERE tag_type='topic' AND tag_objid='{$topic['tid']}'");
			while($tag				= $this->api_core->db->fetch_row())
			{
				$tag_remove[$tag['tag_id']]++;
			}
			
			$tag_remove					= array_keys($tag_remove);
			if(count($tag_remove) > 0)
			{
				$this->api_core->db->query("DELETE FROM icebb_tagged WHERE tag_type='topic' AND tag_objid='{$topic['tid']}'");
				$this->api_core->db->query("UPDATE icebb_tags SET count=count-1 WHERE id IN (".implode(',',$tag_remove).")");
				$this->api_core->db->query("DELETE FROM icebb_tags WHERE count=0");
			}
			
			// does this go to the trash can or does it get removed for good?
			if($this->api_core->icebb->settings['use_trash_can'] && $topic['forum']!=$this->api_core->icebb->settings['trash_can_forum'])
			{
				$this->api_core->db->query("UPDATE icebb_topics SET forum='{$this->api_core->icebb->settings['trash_can_forum']}' WHERE tid='{$this->api_core->icebb->input['topicid']}' LIMIT 1");
			}
			else {
				$this->api_core->db->query("DELETE FROM icebb_topics WHERE tid='{$this->api_core->icebb->input['topicid']}' LIMIT 1");
				$this->api_core->db->query("DELETE FROM icebb_posts WHERE ptopicid='{$this->api_core->icebb->input['topicid']}'");
			}
			
			// update user's post count
			foreach($repliers as $rk => $replier)
			{
				$this->_update_post_count($rk,-$replier);
			}
			
			$num_replies		= count($replies);
			$num_replies--;
			
			// update number of topics and replies and last post
			$lastpost			= $this->api_core->db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$topic['forum']}' ORDER BY lastpost_time DESC LIMIT 1");
			$this->api_core->db->query("UPDATE icebb_forums SET lastpostid='{$lastpost['tid']}',lastpost_title='{$lastpost['title']}',lastpost_author='{$lastpost['lastpost_author']}',topics=topics-1,replies=replies-{$num_replies} WHERE fid='{$topic['forum']}'");
		}
		else {
			// update number of replies
			$this->api_core->db->query("UPDATE icebb_topics SET replies=replies-1 WHERE tid='{$topic['tid']}'");
		
			if($this->api_core->icebb->settings['use_trash_can']=='1' && $topic['forum']!=$this->api_core->icebb->settings['trash_can_forum'])
			{
				$lasttopic			= $this->api_core->db->fetch_result("SELECT tid FROM icebb_topics ORDER BY tid DESC LIMIT 1");
			
				$hmm				= $this->api_core->db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$this->api_core->icebb->settings['trash_can_forum']}' AND description='Deleted from {$topic['tid']}'");
			
				if(!empty($hmm['tid']))
				{
					unset($hmm['result_num_rows_returned']);
					$newtopic		= $hmm;
				}
				else {
					$newtopic		= $topic;
					foreach($newtopic as $ntk => $nt)
					{
						if($ntk{0}=='p')
						{
							unset($newtopic[$ntk]);
						}
					}
					unset($newtopic['result_num_rows_returned']);
					
					$newtopic['tid']	= $lasttopic['tid']+1;
					$newtopic['forum']	= $this->api_core->icebb->settings['trash_can_forum'];
					$newtopic['description']= "Deleted from {$topic['tid']}";
					$newtopic['replies']= 1;
					$newtopic['views']	= 0;
					$newtopic['lastpost_author']= $topic['pauthor'];
					$newtopic['lastpost_time']= $topic['pdate'];
					
					$this->api_core->db->insert('icebb_topics',$newtopic);
				}
				
				$this->api_core->db->query("UPDATE icebb_posts SET ptopicid='{$newtopic['tid']}' WHERE pid='{$pid}' LIMIT 1");
			}
			else {
				$this->api_core->db->query("DELETE FROM icebb_posts WHERE pid='{$pid}' LIMIT 1");
			
				// update number of topics and replies and last post
				$lastpost			= $this->api_core->db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$topic['forum']}' ORDER BY lastpost_time DESC LIMIT 1");
				$this->api_core->db->query("UPDATE icebb_forums SET lastpostid='{$lastpost['tid']}',lastpost_title='{$lastpost['title']}',lastpost_author='{$lastpost['lastpost_author']}',replies=replies-1 WHERE fid='{$topic['forum']}'");
			}
				
			$this->_update_post_count($uid,-1);
		}
	}
	
	function _update_post_count($uid,$change=1)
	{
		$newpostsq				= $change>0 ? 'posts+'.intval($change) : 'posts-'.intval($change);
	
		if(!empty($this->api_core->icebb->cache[$this->api_core->icebb->user['user_group']]['g_promote_group']) &&
		   $this->api_core->icebb->cache[$this->api_core->icebb->user['user_group']]['g_promote_posts']<=$this->api_core->icebb->user['posts']+1)
		{
			// yes? update post count and group
			$this->api_core->db->query("UPDATE icebb_users SET posts={$newpostsq},user_group='{$this->api_core->icebb->cache[$this->api_core->icebb->user['user_group']]['g_promote_group']}' WHERE id='{$this->api_core->icebb->user['id']}' LIMIT 1");
		}
		else {
			// no? just update post count
			$this->api_core->db->query("UPDATE icebb_users SET posts={$newpostsq} WHERE id='{$this->api_core->icebb->user['id']}' LIMIT 1");
		}
	}
	
	function _update_stats()
	{
		$cache_result3					= $this->api_core->db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
		$cache_result31					= $this->api_core->db->fetch_result("SELECT COUNT(*) as topics FROM icebb_topics");
		$cache_result32					= $this->api_core->db->fetch_result("SELECT COUNT(*) as replies FROM icebb_posts WHERE pis_firstpost!=1");
		$this->api_core->icebb->cache['stats']['posts']= $cache_result3['posts'];
		$this->api_core->icebb->cache['stats']['topics']= $cache_result31['topics'];
		$this->api_core->icebb->cache['stats']['replies']= $cache_result32['replies'];
		$this->api_core->std->recache($this->api_core->icebb->cache['stats'],'stats');
	}
}
 ?>
