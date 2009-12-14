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
// "home" module
// $Id: home.php 753 2007-02-16 16:56:55Z mutantmonkey0 $
//******************************************************//

class home
{
	function run()
	{
		global $icebb,$config,$db,$std;

		$this->html					= $icebb->skin->load_template('home');
		$this->lang					= $std->learn_language("home",'forum');
		
		$icebb->skin->rss_links[]	= array($this->lang['rss_feed'],'rss.php');

		if(!$icebb->settings['cpu_disable_online_members'])
		{
			require('includes/classes/userlist_func.php');
			$userlist				= new userlist;
			$uo						= $userlist->run();
			$userso_list			= $uo[0];
			$online					= $uo[1][0];
			$online_groups			= $uo[1][1];

			// sort alphabetically
			$glist					= array();
			foreach($icebb->cache['groups'] as $gro)
			{
				$gro['count']		= intval($online_groups[$gro['gid']]);
				$glist[$gro['g_title']]= $gro;
			}
			ksort($glist);

			if($online['total']				> $icebb->cache['stats']['most_online_ever'])
			{
				$icebb->cache['stats']['most_online_ever']= $online['total'];
				$icebb->cache['stats']['most_online_ever_time']= time();
				$std->recache($icebb->cache['stats'],'stats');
			}

			$statse							= array();
			// no, not goatse
			// oh, yes goatse

			$statse['most_online_ever']		= number_format($icebb->cache['stats']['most_online_ever']);
			$statse['most_online_ever_time']= $std->date_format($icebb->user['date_format'],$icebb->cache['stats']['most_online_ever_time']);

			$statse['posts']				= number_format($icebb->cache['stats']['posts']);
			$statse['topics']				= number_format($icebb->cache['stats']['topics']);
			$statse['replies']				= number_format($icebb->cache['stats']['replies']);

			$statse['users']				= number_format($icebb->cache['stats']['user_count']-1);
			$statse['newest_user']			= $icebb->cache['stats']['user_newest'];

			$stats['online']				= array('count'=>$userlist->online,'users'=>$userlist->online_users,'groups'=>$glist);
		}
		else {
			$stats['online']				= array('count' => 0, 'users' => 0, 'groups' => 0, 'disabled' => true);
		}
		
		if(!$icebb->settings['cpu_disable_recent_actions'])
		{
			$forums_can_show				= array();
			foreach($icebb->cache['forums'] as $f)
			{
				$forum_perms[$f['fid']]		= unserialize(html_entity_decode($f['perms']));
				
				if($forum_perms[$f['fid']][$icebb->user['g_permgroup']]['seeforum'])
				{
					$password_cookie		= $std->eatCookie("{$f['fid']}_password");
					if($password_cookie == $f['password'] || empty($f['password']))
					{
						$forums_can_show[]	= intval($f['fid']);
					}
				}
			}
			
			if(count($forums_can_show) > 0)
			{
				$fcs					= implode(',',$forums_can_show);
				$res					= $db->query("SELECT * FROM icebb_ra_logs WHERE forum_id IN({$fcs}) ORDER BY time DESC LIMIT 0,5");
				while($ra = $db->fetch_row($res))
				{
					$ra['action']		= stripslashes($ra['action']);
					
					$time				= $std->date_format($icebb->user['date_format'], $ra['time']);
					$user				= ($ra['user'] == $icebb->lang['guest']) ? $icebb->lang['guest'] : $ra['user'];//"<a href='{$icebb->base_url}profile={$ra['uid']}'>{$ra['user']}</a>";
					$stats_string		= "({$time}) {$ra['action']} by {$user}<br />";
					
					$stats['recent']   .= $stats_string;
				}
			}
			
			
			/*$res = $db->query("SELECT `time`,`user`,`action` FROM `icebb_ra_logs` ORDER BY `time` DESC LIMIT 0,5");
			while($ra = $db->fetch_row($res))
			{
				$time = $std->date_format($icebb->user['date_format'],$ra['time']);
				$stats['recent'] .= "({$time}) {$ra['action']} by {$ra['user']}<br />";
			}*/
		}
		else {
			$stats['recent']		= $this->lang['feature_disabled'];
		}
		
		if($icebb->settings['cpu_show_birthdays'])
		{
			if(is_array($icebb->cache['birthdays']))
			{
				$m							= gmdate('m',time()+$std->get_offset());
				$d							= gmdate('d',time()+$std->get_offset());
		
				if(is_array($icebb->cache['birthdays'][$m][$d]))
				{
					foreach($icebb->cache['birthdays'][$m][$d] as $bday => $bd)
					{
						if(!isset($bdays))
						{
							$bdays			= "<a href='{$icebb->base_url}profile={$bd['uid']}'>{$bd['username']}</a>";
						}
						else {
							$bdays		   .= ", <a href='{$icebb->base_url}profile={$bd['uid']}'>{$bd['username']}</a>";
						}
	
						if(!empty($bd['year']))
						{
							$turning		= gmdate('Y',time()+$std->get_offset())-$bd['year'];
	
							$bdays		   .= " ({$turning})";
						}
					}
				}
			}
		}

		$stats['bday']					= $bdays;
		$stats['board']					= $statse;
		$this->stats					= $stats;

		if($icebb->input['view'] != 'alt')
		{
			$this->forum_index();
		}
		else {
			$this->alt_home();
		}

		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}


	/*
	This feature requested by Tom on IZE IRC:
	[15:36] <Tom> I have an idea for icebb
	[15:36] <Tom> keep records or statistics on what forums people visit within a board
	[15:37] <Tom> as people generally only look at a couple of the forums unless they are the moderator or otherwise interested
	[15:37] <Tom> so make the board keep records on what you like looking, then make a new layout that displays 5 topics from each forum
	[15:37] <Tom> and that refreshes every minute or so
	[15:37] <Tom> so you don't have to click 50 times to get a view of all the topics you want
	[15:38] <Tom> and you should be able to choose if things are displayed on this view by clicking little stars or something
	[15:38] <Tom> so I would use this view instead of the main board view
	*/
	function alt_home()
	{
		global $icebb,$db,$std;

		$icebb->skin->rss_links[]	= array($this->lang['rss_feed_favorite'],'rss.php?favs=1');

		$this->flang				= $std->learn_language('forum');

		$digest						= unserialize($icebb->user['ftread']);
		$microwaved					= $digest['topics'];

		$db->query("SELECT * FROM icebb_favorites WHERE favuser='{$icebb->user['id']}' AND favtype='forum'");
		while($fav					= $db->fetch_row())
		{
			$favorite_forums[]		= $fav['favobjid'];
		}

		if(!is_array($favorite_forums))
		{
			$std->error($this->lang['no_favs']);
		}

		$hehehe						= implode(",",$favorite_forums);

		$db->query("SELECT * FROM icebb_forums WHERE fid IN({$hehehe})");
		while($f					= $db->fetch_row())
		{
			$forums[$f['fid']]		= $f;
		}

		$db->query("SELECT * FROM icebb_topics ORDER BY lastpost_time DESC");
		while($t					= $db->fetch_row())
		{
			$topics[$t['forum']][]	= $t;
		}

		foreach($forums as $forum)
		{
			if(!is_array($topics[$forum['fid']]))
			{
				$nforums[]			= array($forum);
				continue;
			}

			$cperms					= unserialize($forum['perms']);
			if(!$cperms[$icebb->user['g_permgroup']]['seeforum'])
			{
				continue;
			}

			$this_topics			= array();

			$count					= 1;
			foreach($topics[$forum['fid']] as $topic)
			{
				if($count			> 5)
				{
					break;
				}

				$topic['lastpost_time_formatted']= $std->date_format($icebb->user['date_format'],$topic['lastpost_time']);

				$topic_cutoff		= $microwaved[$topic['tid']]>$icebb->user['last_visit'] ? $microwaved[$topic['tid']] : $icebb->user['last_visit'];
				$topic_cutoff		= $microwaved[$topic['tid']];

				if(time()>$topic_cutoff)
				{
					continue;
				}

				$this_topics[]		= $topic;

				$count++;
			}

			//$noutput			   .= $this->html->althome_forum($forum,$this_topics);
			$nforums[]				= array($forum,$this_topics);
		}

		$this->output				= $this->html->display(1,$nforums,$this->stats);
	}

	function forum_index()
	{
		global $icebb,$db,$std;

		$contentq	= $db->query("SELECT f.*,u.id as lastpost_authorid FROM icebb_forums AS f LEFT JOIN icebb_users AS u ON f.lastpost_author=u.username ORDER BY f.sort ASC");
		while($r	= $db->fetch_row($contentq))
		{
			if($r['parent']=='0')
			{
				$cats[]= $r;
			}
			else {
				$r['lastpost_title']= $std->make_utf8_safe($r['lastpost_title']);

				if(strlen($r['lastpost_title'])>26)
				{
					$r['lastpost_title']= html_substr($r['lastpost_title'],0,26).'...';
				}

				$forums[$r['parent']][]= $r;
			}
		}

		if(is_array($cats))
		{
			foreach($cats as $cat)
			{
				$catperms				= unserialize($cat['perms']);

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
								$marker		= "f_new";
							}
							else {
								$marker		= "f_nonew";
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
								$forum['lastpost_author']	= $icebb->lang['guest'];
							}

							if(empty($forum['lastpost_time']))
							{
								$forum['lastpost_time_formatted']= $this->lang['lastpost_no_post'];
								$forum['lastpostid']		= '0';
								//$forum['lastpost_title']	= "</a>--<a href='#'>";
								$forum['lastpost_title']	= "";
								$forum['lastpost_authorid']	= '0';
								//$forum['lastpost_author']	= "</a>--<a href='#'>";
								$forum['lastpost_author']	= "";
							}

							$password_cookie = $std->eatCookie("{$forum['fid']}_password");
							if(!empty($forum['password']) && $forum['password']!=$password_cookie)
							{
								$forum['lastpost_time_formatted']= $this->lang['lastpost_password'];
								$forum['lastpostid']		= '0';
								//$forum['lastpost_title']	= "</a>--<a href='#'>";
								$forum['lastpost_title']	= "";
								$forum['lastpost_authorid']	= '0';
								//$forum['lastpost_author']	= "</a>--<a href='#'>";
								$forum['lastpost_author']	= "";
							}

							$forum['marker']				= $marker;
													
							// subforums
							$forum['subforums']				= $forums[$forum['fid']];

							$this_forums[]= $forum;
						}
					}
				}
			//}

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
					//print_r(array($cat,$this_forums));
					//exit();
				}
				else if($catperms[$icebb->user['g_permgroup']]['seeforum']=='1' && $cat['topics']>='1' && $cat['postable']=='1')
				{
					$postsq		= $db->query("SELECT * FROM icebb_topics WHERE forum='{$cat['fid']}' ORDER BY lastpost_time DESC LIMIT 3");
					while($t	= $db->fetch_row($postsq))
					{
						$t['lastpost_time_formatted']= $std->date_format($icebb->user['date_format'],$t['lastpost_time']);

						$topic_cutoff= $microwaved[$t['tid']]>$icebb->user['last_visit'] ? $microwaved[$t['tid']] : $icebb->user['last_visit'];
						$topic_cutoff= $microwaved[$t['tid']];

						if($t['lastpost_time']>$topic_cutoff && $t['lastpost_time']!='0')
						{
							$marker	= "t_new";
						}
						else {
							$marker	= "t_nonew";
						}
					}
				}

				$this_forums		= array();
			}
		}
		else
		{
			$std->error($this->lang['no_forums']);
		}

		$this->output				= $this->html->display(0,$nforums,$this->stats);
	}
}
?>
