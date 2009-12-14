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
// forum display module
// $Id: forum.php 742 2007-02-10 07:30:57Z daniel159 $
//******************************************************//

class forum
{
	function run()
	{
		global $icebb,$db,$config,$std;

		$this->lang				= $std->learn_language('forum');
		$this->html				= $icebb->skin->load_template('forum');
		
		if(isset($icebb->input['mark_all_read']))
		{
			$this->mark_all_read();
		}
		
		if(!empty($icebb->input['go']))
		{
			$this->handle_go();
		}
		
		$icebb->skin->rss_links[]= array($this->lang['rss_feed_forum'],"rss.php?forum={$icebb->input['forum']}");
		$icebb->skin->rss_links[]= array($this->lang['rss_feed_forum2'],"rss.php?forum={$icebb->input['forum']}&topics_only");
		
		$this->bla(); // DELETE WHEN DONE
	}
	
	function mark_all_read()
	{
		global $icebb,$db,$std;
		
		$ftread			= unserialize($icebb->user['ftread']);
		
		$db->query("SELECT fid FROM icebb_forums");
		while($f			= $db->fetch_row())
		{
			$ftread['forums'][$f['fid']]= time();
		}
		
		$db->query("SELECT tid FROM icebb_topics");
		while($t			= $db->fetch_row())
		{
			$ftread['topics'][$t['tid']]= time();
		}
		
		$db->query("UPDATE icebb_users SET ftread='".addslashes(serialize($ftread))."' WHERE id='{$icebb->user['id']}'");
		
		$std->bouncy_bouncy($this->lang['all_marked_read'],$icebb->base_url);
		exit();
	}
	
	function handle_go()
	{
		global $icebb,$db,$std;
		
		$f					= $db->fetch_result("SELECT parent FROM icebb_forums WHERE fid='{$icebb->input['forum']}'");
		
		switch($icebb->input['go'])
		{
			case 'next':
				$forum		= $db->fetch_result("SELECT fid FROM icebb_forums WHERE fid>".intval($icebb->input['forum'])." ORDER BY sort LIMIT 1");
				if($forum['result_num_rows_returned']<=0)
				{
					$std->error($this->lang['forum_not_exist'],1);
				}
				
				$std->redirect("{$icebb->base_url}forum={$forum['fid']}");
				break;
			case 'prev':
				$forum		= $db->fetch_result("SELECT fid FROM icebb_forums WHERE fid<".intval($icebb->input['forum'])." ORDER BY sort LIMIT 1");
				if($forum['result_num_rows_returned']<=0)
				{
					$std->error($this->lang['forum_not_exist'],1);
				}
				
				$std->redirect("{$icebb->base_url}forum={$forum['fid']}");
				break;
			default:
				break;
		}
	}
	
	function bla()
	{
		global $icebb,$db,$std;
		
		// turn forum marker "off" when entering forum
		$ftread = unserialize($icebb->user['ftread']);
		$ftread['forums'][$icebb->input['forum']] = time();
		$icebb->user['ftread'] = serialize($ftread);
		$db->query("UPDATE icebb_users SET ftread='{$icebb->user['ftread']}' WHERE id='{$icebb->user['id']}'");
		
		$contentq	= $db->query("SELECT f.*,u.id as lastpost_authorid FROM icebb_forums AS f LEFT JOIN icebb_users AS u ON f.lastpost_author=u.username ORDER BY f.sort");
		while($r	= $db->fetch_row($contentq))
		{
			if($r['fid']==$icebb->input['forum'])
			{
				$cats[]= $r;
			}
			else {
				$r['lastpost_title']= $r['lastpost_title'];
			
				if(strlen($r['lastpost_title'])>26)
				{
					$r['lastpost_title']= html_substr($r['lastpost_title'],0,26).'...';
				}
			
				$forums[$r['parent']][]= $r;
			}
		}
		
		if($db->get_num_rows($contentq)<=0)
		{
			$std->error($this->lang['forum_not_exist'],1);
		}
		
		$cperms			= unserialize($cats[0]['perms']);
		if(!$cperms[$icebb->user['g_permgroup']]['seeforum'])
		{
			$std->error($this->lang['no_perms_forum'],1);
		}
		
		if(!empty($cats[0]['redirecturl']))
		{
			$db->query("UPDATE icebb_forums SET redirect_hits=redirect_hits+1 WHERE fid='{$cats[0]['fid']}'");
			$std->redirect($cats[0]['redirecturl']);
		}
		
		if(!empty($icebb->input['subscribe']))
		{
			if($icebb->user['id']		== '0')
			{
				$std->error($this->lang['unauthorized'],1);
				exit();
			}
		
			$db->query("SELECT COUNT(*) as count FROM icebb_subscriptions WHERE suid='{$icebb->user['id']}' AND sforum='{$cats[0]['fid']}'");
			$s				= $db->fetch_row();
			if($s['count']>=1)
			{
				$db->query("DELETE FROM icebb_subscriptions WHERE suid='{$icebb->user['id']}' AND sforum='{$cats[0]['fid']}'");
			}
			else {
				$db->insert('icebb_subscriptions',array(
					'suid'	=> $icebb->user['id'],
					'sforum'=> $cats[0]['fid'],
				));
			
				$std->bouncy_bouncy($this->lang['subscribed'],"{$icebb->base_url}forum={$cats[0]['fid']}");
			}
		}
		else if(!empty($icebb->input['favorite']))
		{
			if($icebb->user['id']		== '0')
			{
				$std->error($this->lang['unauthorized'],1);
				exit();
			}
		
			$db->query("SELECT COUNT(*) as count FROM icebb_favorites WHERE favuser='{$icebb->user['id']}' AND favtype='forum' AND favobjid='{$cats[0]['fid']}'");
			$s				= $db->fetch_row();
			if($s['count']>=1)
			{
				$db->query("DELETE FROM icebb_favorites WHERE favuser='{$icebb->user['id']}' AND favtype='forum' AND favobjid='{$cats[0]['fid']}'");
			}
			else {
				$db->insert('icebb_favorites',array(
					'favuser'=> $icebb->user['id'],
					'favtype'=> 'forum',
					'favobjid'=> $cats[0]['fid'],
				));
			
				$std->bouncy_bouncy($this->lang['fav_added'],"{$icebb->base_url}forum={$cats[0]['fid']}");
			}
		}
		
		if(is_array($forums))
		{
			foreach($cats as $cat)
			{
				$cperms				= unserialize($cat['perms']);
			
				if(is_array($forums[$cat['fid']]))
				{
					foreach($forums[$cat['fid']] as $forum)
					{
						$perms			= unserialize($forum['perms']);
	
						if($perms[$icebb->user['g_permgroup']]['seeforum']=='1')
						{
							$forum['description']= nl2br($forum['description']);
							//$forum['description']= str_replace("&lt;","<",$forum['description']);
							//$forum['description']= str_replace("&gt;",">",$forum['description']);
							$forum['lastpost_time_formatted']= $std->date_format($icebb->user['date_format'],$forum['lastpost_time']);
						
							//$edible		= unserialize($std->eatCookie('forumread'));
							$edible2		= unserialize($icebb->user['ftread']);
							$edible			= $edible2['forums'];
							$forum_cutoff	= $edible[$forum['fid']]>$icebb->user['last_visit'] ? $edible[$forum['fid']] : $icebb->user['last_visit'];
							$forum_cutoff	= $edible[$forum['fid']];
					
							if($forum['lastpost_time']>$forum_cutoff && $forum['lastpost_time']!='0')
							{
								$marker		= "<macro:f_new />";
							}
							else {
								$marker		= "<macro:f_nonew />";
							}
						
							if(is_array($icebb->cache['moderators']))
							{
								foreach($icebb->cache['moderators'] as $m)
								{
									if($m['mforum']==$forum['fid'])
									{
										if(empty($forum['_moderators']))
										{
											$m['before']= '';
										}
										else {
											$m['before']= ", ";
										}
										$forum['moderators'][]= $m;
									}
								}
							}
							
							if(empty($forum['lastpost_author']))
							{
								$forum['lastpost_author']	= $this->lang['guest'];
							}
							
							if(empty($forum['lastpost_time']))
							{
								$forum['lastpost_time_formatted']= $this->lang['no_posts_in_forum'];
								$forum['lastpostid']		= '0';
								$forum['lastpost_title']	= "";
								$forum['lastpost_authorid']	= '0';
								$forum['lastpost_author']	= "";
							}
										
							$password_cookie = $std->eatCookie("{$forum['fid']}_password");
							if(!empty($forum['password']) && $forum['password']!=$password_cookie)
							{
								$forum['lastpost_time_formatted']= $this->lang['forum_requires_pass'];
								$forum['lastpostid']		= '0';
								$forum['lastpost_title']	= "";
								$forum['lastpost_authorid']	= '0';
								$forum['lastpost_author']	= "";
							}
							
							$forum['marker']				= $marker;
							
							// subforums
							$forum['subforums']				= $forums[$forum['fid']];
						
							$this_forums[]= $forum;
						}
					}
				}
				
				if(!empty($this_forums))
				{
					$cat['description']= nl2br($cat['description']);
					//$cat['description']= str_replace("&lt;","<",$cat['description']);
					//$cat['description']= str_replace("&gt;",">",$cat['description']);
				
					if($std->eatCookie("cat-{$cat['fid']}-cstate")=='1')
					{
						//$this->output.= "<script type='text/javascript'>_getbyid('cat-{$cat['fid']}-collapsed').style.display='';_getbyid('cat-{$cat['fid']}').style.display='none';</script>";
					}
					
					$nforums[]	= array($cat,$this_forums);
				}
				else if($cperms[$icebb->user['g_permgroup']]['seeforum']=='1' && $cat['topics']>='1' && $cat['postable']=='1')
				{
					$postsq		= $db->query("SELECT * FROM icebb_topics WHERE forum='{$cat['fid']}' ORDER BY lastpost_time DESC LIMIT 3");
					while($t	= $db->fetch_row($postsq))
					{
						$t['lastpost_time_formatted']= $std->date_format($icebb->user['date_format'],$t['lastpost_time']);
					
						$topic_cutoff= $microwaved[$t['tid']]>$icebb->user['last_visit'] ? $microwaved[$t['tid']] : $icebb->user['last_visit'];
						$topic_cutoff= $microwaved[$t['tid']];
					
						if($t['lastpost_time']>$topic_cutoff && $t['lastpost_time']!='0')
						{
							$marker	= "<macro:t_new />";
						}
						else {
							$marker	= "<macro:t_nonew />";
						}
					}
				}
				
				$this_forums		= array();
			}
		}
		else {
			if(empty($cats[0]['lastpost_author']))
			{
				$cats[0]['lastpost_authorid']= '0';
				$cats[0]['lastpost_author']= $this->lang['guest'];
			}
		
			$this_forums[]			= array();
		}
		
		//$digest					= unserialize($std->eatCookie('forumread'));
		$digest						= unserialize($icebb->user['ftread']);
		if($icebb->input['make_read']=='1')
		{
			//$digest[$cats[0]['fid']]= time();
			//$std->bakeCookie('forumread',serialize($digest));
			$digest['forums'][$cats[0]['fid']]= time();
			
			$db->query("SELECT tid FROM icebb_topics WHERE forum='{$cats[0]['fid']}'");
			while($mrt				= $db->fetch_row())
			{
				$digest['topics'][$mrt['tid']]= time();
			}
			
			$db->query("UPDATE icebb_users SET ftread='".addslashes(serialize($digest))."' WHERE id='{$icebb->user['id']}'",1);
		}
		
		$microwaved				= $digest['topics'];
		$digested				= $digest['forums'];
		
		$fparent				= $cats[0]['fid'];

		while($fparent		   != 0)
		{
			$f					= $icebb->cache['forums'][$fparent];
			$nav[]				= "<a href='{$icebb->base_url}forum={$f['fid']}'>{$f['name']}</a>";
			$fparent			= $f['parent'];
		}
		
		for($i=count($nav)-1;$i>=0;$i--)
		{
			$icebb->nav[]		= $nav[$i];
		}
		
		if($cats[0]['postable']==1)
		{
			if(!empty($cats[0]['password']))
			{
				$cookie				= $std->eatCookie($cats[0]['fid'].'_password');
				if($cookie!=$cats[0]['password'])
				{
					if(md5($icebb->input['forum_password'])==$cats[0]['password'])
					{
						$std->bakeCookie($cats[0]['fid'].'_password',md5($icebb->input['forum_password']),'GE');
					}
					else {
						$doutput= $this->html->password_box($cats[0]);
						$icebb->skin->html_insert($doutput);
						$icebb->skin->do_output();
						exit();
					}
				}
			}
			
			// so, are we a mod?
			if($icebb->user['g_is_mod']!='1' && $icebb->user['g_is_admin']!='1')
			{
				$where_extra		   .= " AND is_hidden!='1'";
			}
		
			// favorite topics
			$favorite_topics			= array();
			$db->query("SELECT * FROM icebb_favorites WHERE favuser='{$icebb->user['id']}' AND favtype='topic'");
			while($f					= $db->fetch_row())
			{
				$favorite_topics[]		= $f['favtopic'];
			}
		
			$sort_order['order_by']		= empty($icebb->input['order_by']) ? 'lastpost' : $icebb->input['order_by'];
			$sort_order['sort_order']	= empty($icebb->input['sort_order']) ? 'desc' : $icebb->input['sort_order'];
			$sort_order['startdate']	= empty($icebb->input['startdate']) ? 'all' : $icebb->input['startdate'];
			
			/////////////////////////////////////////////////////////
			// Generate order clause
			/////////////////////////////////////////////////////////
			
			if($sort_order['order_by'] == 'topic_title')
			{
				$order_clause			= "title";
			}
			else if($sort_order['order_by'] == 'lastpost')
			{
				$order_clause			= "lastpost_time";
			}
			else if($sort_order['order_by'] == 'replies')
			{
				$order_clause			= 'replies';
			}
			else if($sort_order['order_by'] == 'views')
			{
				$order_clause			= 'views';
			}
			else {
				$order_clause			= "tid";
			}
			
			$order_clause			   .= $sort_order['sort_order']=='desc' ? " DESC" : " ASC";
			
			if($sort_order['startdate'] != 'all')
			{
				switch($sort_order['startdate'])
				{
					case 'today':
						$start			= gmmktime(0,0,0);
						break;
					case 'week':
						$start			= gmmktime(0,0,0,gmdate('m'));
						break;
					case 'month':
						$start			= gmmktime(0,0,0,gmdate('m'),1);
						break;
				}
				
				$where_extra		   .= " AND lastpost_time>=".intval($start);
			}
		
			////////////////////////////////////////////////////////
			// Make sure we don't show blocked user's posts
			////////////////////////////////////////////////////////
			
			$buds						= @unserialize($icebb->user['buddies']);
			if(is_array($buds))
			{
				foreach($buds as $tb)
				{
					if($tb['type']!=2) continue;
				
					$to_block[]			= $tb['uid'];
				}
				
				if(is_array($to_block) && !empty($to_block))
				{
					//$where_extra	   .= " AND starter_id NOT IN(".implode(',',$to_block).")";
				}
			}
		
			////////////////////////////////////////////////////////
			// Pagination
			////////////////////////////////////////////////////////
		
			$per_page					= empty($icebb->input['per_page']) ? 15 : intval($icebb->input['per_page']);

			if(!empty($icebb->input['page']))
			{
				$icebb->input['page']	= (int) $icebb->input['page'];
				$icebb->input['start']	= (int) ($icebb->input['page'] * $per_page) - $per_page;
			}

			$start						= empty($icebb->input['start']) ? 0 : intval($icebb->input['start']);
			$qextra						= " LIMIT {$start},{$per_page}";
		
			$total						= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_topics WHERE forum='{$icebb->input['forum']}'{$where_extra} ORDER BY {$order_clause}");
			$total_topics				= $total['total'];
			$tnum						= $start;
			
			////////////////////////////////////////////////////////
			// Get announcements
			////////////////////////////////////////////////////////
			
			$result = $db->query("SELECT * FROM icebb_announcements WHERE aforums LIKE '%{$icebb->input['forum']},%' ORDER BY `adate` DESC LIMIT 0,1");
			
			if($db->get_num_rows()>=1)
			{
				require_once('includes/classes/post_parser.php');
				$post_parser		= new post_parser;
				
				while($a			= $db->fetch_row($result))
				{
					$a['atext']		= html_entity_decode($post_parser->parse($a['atext']),ENT_QUOTES);
					$an			   .= $this->html->announce($a["atitle"],$a["atext"]);
				}
			}
			
			////////////////////////////////////////////////////////
			// Users viewing this forum
			////////////////////////////////////////////////////////
			
			if(!$icebb->settings['cpu_disable_users_viewing'])
			{
				$users_viewing				= array();
			
				$num['members']				= 1;
				$num['guests']				= 0;
				$num['total']				= 1;
				if($icebb->user['id']!='0')
				{
				        $users_viewing[]	= "<a href='{$icebb->base_url}profile={$icebb->user['id']}'>{$icebb->user['g_prefix']}{$icebb->user['username']}{$icebb->user['g_suffix']}</a>";
				}
				else {				// eww... a guest
					$num['members']			= 0;
					$num['guests']			= 1;
				}
				$db->query("SELECT u.*,g.* FROM icebb_session_data AS s LEFT JOIN icebb_users AS u ON s.user_id=u.id LEFT JOIN icebb_groups AS g ON g.gid=u.user_group WHERE s.forum='{$cats[0]['fid']}' AND s.last_action>".(time()-(15*60))."");
				while($u					= $db->fetch_row())
				{
					if($u['id']				== '0')
					{
						$num['guests']++;
						$num['total']++;
					}
	 				else if($u['id']		== $icebb->user['id'])
	  				{
	 					continue;
	 				}
					else if(strpos($users_viewing,$u['username'])===false && !empty($u['username']))
					{
						$users_viewing[]	= "<a href='{$icebb->base_url}profile={$u['id']}'>{$u['g_prefix']}{$u['username']}{$u['g_suffix']}</a>";
					
						$num['members']++;
						$num['total']++;
					}
				}
				
				$viewing_html				= $this->html->users_viewing($num, $users_viewing);
			}
			
			////////////////////////////////////////////////////////
			// Get topics
			////////////////////////////////////////////////////////
		
			// DO REMOVE THIS LATER
			$db->query("SELECT id,username FROM icebb_users");
			while($u					= $db->fetch_row())
			{	
				$uids_array[$u['username']]= $u['id'];
			}
		
			$posten						= array();
			$num_pinned					= 0;
		
			// get pinned
			$pinnedq					= $db->query("SELECT * FROM icebb_topics WHERE forum='{$icebb->input['forum']}' AND is_pinned=1{$where_extra} ORDER BY {$order_clause}");
			while($t1					= $db->fetch_row($pinneq))
			{
				$posten[]				= $t1;
				$num_pinned++;
				$limit_left--;
			}
			
			//$limit_left					= $per_page - $num_pinned;
			//$total_topics				= $total_topics - $num_pinned;
			
			//$qextra						= " LIMIT {$start},{$limit_left}";
		
			// get others
			$postsq						= $db->query("SELECT * FROM icebb_topics WHERE forum='{$icebb->input['forum']}' AND is_pinned=0{$where_extra} ORDER BY {$order_clause}{$qextra}");
			while($t					= $db->fetch_row($postsq))
			{
				$posten[]				= $t;
			}
			
			foreach($posten as $t)
			{
				$t['lastpost_time_formatted']= $std->date_format($icebb->user['date_format'],$t['lastpost_time']);
			
				$topic_cutoff			= $microwaved[$t['tid']]>$icebb->user['last_visit'] ? $microwaved[$t['tid']] : $icebb->user['last_visit'];
				$topic_cutoff			= $microwaved[$t['tid']];
			
				if($t['replies'] >= 15)
				{
					if($t['lastpost_time']>$topic_cutoff && $t['lastpost_time']!='0')
					{
						$marker			= '<macro:t_hotnew />';
					}
					else {
						$marker			= '<macro:t_hot />';
					}
				}
				else {
					if($t['lastpost_time']>$topic_cutoff && $t['lastpost_time']!='0')
					{
						$marker			= '<macro:t_new />';
					}
					else {
						$marker			= '<macro:t_nonew />';
					}
				}
				
				$t['title']				= $t['title'];
				$t['description']		= $t['description'];
				
				if($t['is_locked'])
				{
					//$t['prepend']		= "Locked: ";
					$marker				= '<macro:t_locked />';
				}
				
				if($t['is_pinned'])
				{
					$t['prepend']		= $this->lang['prefix_pinned'];
				}
				
				if($t['is_hidden'])
				{
					$t['append']		= $this->lang['suffix_hidden'];
				}
				
				if(in_array($t['tid'],$favorite_topics))
				{
					$t['prepend']		=  $this->lang['prefix_favorite'];
				}
				
				if(!empty($t['moved_to']))
				{
					$t['prepend']		= $this->lang['prefix_moved'];
				}
			
				if(!empty($t['icon']))
				{
					$t['post_icon']		= "<img src='skins/{$icebb->skin->skin_id}/images/post_icons/{$t['icon']}' alt='' />";
				}
				
				if(empty($t['lastpost_author']))
				{
					$t['lastpost_author']= $this->lang['guest'];
				}
				else {
					$t['lastpost_author_id']	= $uids_array[$t['lastpost_author']];
				}
				
				if(empty($t['starter']))
				{
					$t['starter']		= $this->lang['guest'];
				}
				else {
					$t['starter_id']	= $uids_array[$t['starter']];
				}
				
				$topics_per_page		= 10;
				if($t['replies']+1>$topics_per_page)
				{
					$curr_start			= (ceil($t['replies']/10)*10)-10;
					$t['append']		= $std->render_pagelinks(array('curr_start'=>$curr_start,'total'=>$t['replies']+1,'per_page'=>$topics_per_page,'base_url'=>"{$icebb->base_url}topic={$t['tid']}&amp;"),'mini');
				}
				
				$this_topic				= $this->html->topic_row($t,$marker);
				
				if($icebb->user['g_is_mod']=='1' || $this->is_mod==1)
				{
					$this_topic			= str_replace('<{MOD_OPTIONS}>',$this->html->moderator_tick_perforum($t['tid']),$this_topic);
				}
				else {
					$this_topic			= str_replace('<{MOD_OPTIONS}>','',$this_topic);
				}
				
				if($t['is_pinned']	== '1' || 
				   in_array($t['tid'],$favorite_topics))
				{
					$topic_listing_pinned.= $this_topic;
				}
				else {
					$topic_listing  .= $this_topic;
				}
			}
			
			$orderme['order_by']	= $this->select_box(array(
				'topic_title'		=> $this->lang['order_topic_title'],
				'lastpost'			=> $this->lang['order_lastpost'],
				'replies'			=> $this->lang['order_replies'],
				'views'				=> $this->lang['order_views'],
			),$sort_order['order_by']);
			
			$orderme['sort_order']	= $this->select_box(array(
				'asc'				=> $this->lang['order_asc'],
				'desc'				=> $this->lang['order_desc'],
			),$sort_order['sort_order']);
			
			$orderme['startdate']	= $this->select_box(array(
				'today'				=> $this->lang['startdate_today'],
				'week'				=> $this->lang['startdate_week'],
				'month'				=> $this->lang['startdate_month'],
				'all'				=> $this->lang['startdate_all'],
			),$sort_order['startdate']);
		
			////////////////////////////////////
			
			$permos					= array();
			$perms					= unserialize($cats[0]['perms']);
			$perms					= $perms[$icebb->user['g_permgroup']];
			
			if($perms['createtopics'])	$permos[]= $this->lang['may_post_topics'];
			if($perms['reply'])			$permos[]= $this->lang['may_reply'];
			if(in_array($cats[0]['fid'],$icebb->user['moderate']) || $icebb->user['g_is_mod']=='1')
			{
				$permos[]			= $this->lang['may_mod'];
			}
			
			$cats[0]['permissions_are_a_huge_pain_in_the_ass']= implode('<br />',$permos);
			
			/////////////////////////////////////
			
			$db->query("SELECT * FROM icebb_favorites WHERE favuser={$icebb->user['id']} AND favtype='forum' AND favobjid={$cats[0]['fid']} LIMIT 1");
			$is_fav					= $db->get_num_rows() > 0 ? true : false;
			
			/////////////////////////////////////
			
			$pagelinks				= $std->render_pagelinks(array('curr_start'=>$start,'total'=>$total_topics,'per_page'=>$per_page,'base_url'=>"{$icebb->base_url}forum={$icebb->input['forum']}&amp;"));
			$topics					= $this->html->topic_listing($cats[0],$topic_listing_pinned,$topic_listing,$orderme,$pagelinks,$viewing_html,$is_fav,$announcements);
		}
		
		$output						=  $this->html->forum_view($an,$nforums,$topics,$this->html->users_viewing($num,$users_viewing));
		
		if($icebb->user['g_is_mod']=='1' || $this->is_mod==1)
		{
			$this->modlang			= $std->learn_language('moderate');
		
			// TOPIC
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_unpin',$this->modlang['topic_unpin']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_pin',$this->modlang['topic_pin']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_move',$this->modlang['topic_move']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_lock',$this->modlang['topic_lock']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_unlock',$this->modlang['topic_unlock']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_delete',$this->modlang['topic_delete']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_show',$this->modlang['topic_show']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_hide',$this->modlang['topic_hide']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_merge',$this->modlang['topic_merge']);
			
			// DISPLAY
			$output					= str_replace('<!--MODERATOR.OPTIONS-->',$this->html->moderator_options($topic,$modlinks),$output);
		}
		
		$icebb->skin->html_insert($output);
		$icebb->skin->do_output();
	}
	
	function select_box($vals,$val)
	{
		global $icebb;
		
		foreach($vals as $k => $v)
		{
			if($k				== $val)
			{
				$t			   .= "<option value='{$k}' selected='selected'>{$v}</option>";
			}
			else {
				$t			   .= "<option value='{$k}'>{$v}</option>";
			}
		}
		
		return $t;
	}
}
?>
