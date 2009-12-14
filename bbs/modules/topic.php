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
// topic display module
// $Id: topic.php 829 2007-05-25 01:04:48Z mutantmonkey0 $
//******************************************************//

// [19:21] <Tom> put something on it where you can star topics like gmail

class topic
{
	function run()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		require('includes/classes/post_parser.php');
		$post_parser				= new post_parser();
		
		$icebb->lang				= $std->learn_language('topics');
		$this->html					= $icebb->skin->load_template('topic');
		
		$icebb->skin->rss_links[]	= array($icebb->lang['rss_feed_topic'],"rss.php?topic={$icebb->input['topic']}");
		
		if($icebb->input['print']=='1')
		{
			$this->pf				= 1;
			$icebb->input['func']	= 'print';
		}
		
		if(!empty($icebb->input['report']))
		{
			$icebb->input['func']	= 'report';
		}
		
		if(isset($icebb->input['phistory']))
		{
			$icebb->input['func']	= 'phistory';
		}
		
		switch($icebb->input['func'])
		{
			case 'favorite':
				$this->add_favorite();
				break;
			case 'report':
				$this->report_post();
				break;
			case 'phistory':
				$this->post_history();
				break;
			case 'print':
				$this->print_page();
				break;
			case 'email':
				$this->email_topic();
				break;
			default:
				$this->show_topic();
		}
	}
		
	function show_topic()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		$per_page				= empty($icebb->input['per_page']) ? 10 : intval($icebb->input['per_page']);
	
		switch($icebb->input['show'])
		{
			case 'lastpost':
				$lastpostq			= $db->query("SELECT pid FROM icebb_posts WHERE ptopicid='{$icebb->input['topic']}' ORDER BY pid DESC LIMIT 1");
				$lastpost			= $db->fetch_row($lastpostq);
			
				$total				= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_posts WHERE ptopicid='{$icebb->input['topic']}'");
				$lastpage			= (ceil($total['total']/$per_page)*$per_page)-$per_page;
			
				if($lastpage	   <= 0)
				{
					$lastpage		= 0;
				}
			
				$std->redirect("{$icebb->base_url}topic={$icebb->input['topic']}&start={$lastpage}&#post-{$lastpost['pid']}");
				break;
			case 'newpost':
				$topicread			= unserialize($icebb->user['ftread']);
				$last_view_date		= intval($topicread['topics'][$icebb->input['topic']]);
			
				$newpostq			= $db->query("SELECT pid FROM icebb_posts WHERE ptopicid='{$icebb->input['topic']}' ORDER BY pdate DESC LIMIT 1");
				$newpost			= $db->fetch_row($newpostq);
			
				$total				= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_posts WHERE ptopicid='{$icebb->input['topic']}'");
				$newpage			= (ceil($total['total']/$per_page)*$per_page)-$per_page;
			
				if($lastpage	   <= 0)
				{
					$lastpage		= 0;
				}

				$std->redirect("{$icebb->base_url}topic={$icebb->input['topic']}&start={$newpage}&#post-{$newpost['pid']}");
				break;
		}
		
		if(isset($icebb->input['pid']))
		{
			$lastpostq				= $db->query("SELECT * FROM icebb_posts WHERE pid=" . intval($icebb->input['pid']) . " AND ptopicid=" . intval($icebb->input['topic']));
			$lastpost				= $db->fetch_row($lastpostq);
		
			$total					= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_posts WHERE ptopicid='{$icebb->input['topic']}' AND pid<".intval($icebb->input['pid'])."");
			$pageon					= (ceil($total['total']/$per_page)*$per_page)-$per_page;
		
			if($pageon <= 0)
			{
				$pageon				= 0;
			}
		
			$std->redirect("{$icebb->base_url}topic={$lastpost['ptopicid']}&start={$pageon}&#post-{$lastpost['pid']}");
		}
		
		$this->modlang				= $std->learn_language('moderate'); 

		$topicid					= intval($icebb->input['topic']);

		// this had better make Snuffkin happy
		if(empty($topicid))
		{
			$topics					= $db->query("SELECT * FROM icebb_topics");
			while($topi				= $db->fetch_row())
			{
				if(preg_replace("`[!\.\?]`",'',str_replace(' ','-',strtolower($topi['title'])))==$icebb->input['topic'])
				{
					$topic			= $topi;
				}
			}
		}
		else {
			$topic					= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='".intval($icebb->input['topic'])."'");
		}
		
		// turn forum marker "off" when viewing forum
		$ftread						= unserialize($icebb->user['ftread']);
		$ftread['forums'][$topic['forum']] = time();
		$icebb->user['ftread']		= serialize($ftread);
		$db->query("UPDATE icebb_users SET ftread='{$icebb->user['ftread']}' WHERE id='{$icebb->user['id']}'");
		
		if(!empty($topic['moved_to']))
		{
			$std->redirect("{$icebb->base_url}topic={$topic['moved_to']}");
		}
		
		$icebb->skin->rss_links[]	= array($icebb->lang['rss_feed_forum'],"rss.php?forum={$topic['forum']}");

		$topic['title']				= $topic['title'];

		if(!empty($topic['description']))
		{
			$topic['description']	= $topic['description'];
			$topic['title']		   .= ", {$topic['description']}";
		}
		
		$tags						= array();
		$db->query("SELECT tagged.*,tag.tag FROM icebb_tagged AS tagged LEFT JOIN icebb_tags AS tag ON tagged.tag_id=tag.id WHERE tagged.tag_type='topic' AND tagged.tag_objid='{$topic['tid']}'");
		while($tag					= $db->fetch_row())
		{
			$topics['tags'][]		= $tag;
			$tags[]					= "<a href='{$icebb->base_url}tag={$tag['tag']}'>{$tag['tag']}</a>";
		}
		$topic['tag_html']			= implode(' ',$tags);
		
		$icebb->security_key		= $std->make_me_some_random_md5_kthxbye();
		
		$perms						= unserialize(html_entity_decode($icebb->cache['forums'][$topic['forum']]['perms']));
		if(!$perms[$icebb->user['g_permgroup']]['read'])
		{
			$std->error($icebb->lang['not_allowed_read'], 1);
		}
		
		if(($icebb->user['g_is_mod']!='1' && $icebb->user['g_is_admin']!='1') && 
		    $topic['is_hidden']=='1')
		{
			$std->error($icebb->lang['not_allowed_read'], 1);
		}
		
		$f						= $icebb->cache['forums'][$topic['forum']];
		if(!empty($f['password']))
		{
			$cookie				= $std->eatCookie($topic['forum'] . '_password');
			if($cookie != $f['password'])
			{
				if(md5($icebb->input['forum_password']) == $icebb->cache['forums'][$topic['forum']]['password'])
				{
					$std->bakeCookie($topic['forum'].'_password',md5($icebb->input['forum_password']),'GE');
				}
				else {
					$doutput		= $this->html->password_box($f, $topic['tid']);
					$icebb->skin->html_insert($doutput);
					$icebb->skin->do_output();
					exit();
				}
			}
		}
		
		$this->topic				= $topic;
		
		if($icebb->input['go'] == 'prev')
		{
			$tinfo					= $db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$topic['forum']}' AND lastpost_time<{$topic['lastpost_time']} ORDER BY lastpost_time DESC LIMIT 0,1");
			if(empty($tinfo['tid']))
			{
				$std->error($icebb->lang['no_topics_found']);
			}
			$std->redirect("{$icebb->base_url}topic={$tinfo['tid']}");
		}
		else if($icebb->input['go']	== 'next')
		{
			$tinfo					= $db->fetch_result("SELECT * FROM icebb_topics WHERE forum='{$topic['forum']}' AND lastpost_time>{$topic['lastpost_time']} ORDER BY lastpost_time ASC LIMIT 0,1");
			if(empty($tinfo['tid']))
			{
				$std->error($icebb->lang['no_topics_found']);
			}
			$std->redirect("{$icebb->base_url}topic={$tinfo['tid']}");
		}
		
		$digest					= unserialize($icebb->user['ftread']);
		$digest['topics'][$topic['tid']]= time();
		$db->query("UPDATE icebb_users SET ftread='".addslashes(serialize($digest))."' WHERE id='{$icebb->user['id']}'",1);
		
		// can we moderate here?
		if(in_array($forum['fid'],$icebb->user['moderate']))
		{
			$this->is_mod		= 1;
		}
		
		if($icebb->user['g_is_mod']=='1' || $this->is_mod)
		{
			$icebb->is_mod		= 1;
		}
		
		$db->query("UPDATE icebb_topics SET views=views+1 WHERE tid='{$topic['tid']}'",1);

		$fparent				= $topic['forum'];

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

		//$icebb->nav				= array("<a href='{$icebb->base_url}forum={$topic['forum']}'>{$forum['name']}</a>",$topic['title']);
		
		$icebb->nav[]			= $topic['title'];
		
		if($icebb->user['g_is_mod']!='1' && $icebb->user['g_is_admin']!='1')
		{
			$wextra				= " AND phide!='1'";
		}
		
		////////////////////////////////////////////////////////
		// Make sure we don't show blocked user's posts
		////////////////////////////////////////////////////////
		
		$to_block				= array();
		$buds					= @unserialize($icebb->user['buddies']);
		if(is_array($buds))
		{
			foreach($buds as $tb)
			{
				if($tb['type']!=2) continue;
			
				$to_block[]		= $tb['uid'];
			}
			
			/*if(is_array($to_block) && !empty($to_block))
			{
				$wextra 	  .= " AND pauthor_id NOT IN(".implode(',',$to_block).")";
			}*/
		}
		
		////////////////////////////////////////////////////////
		// Pagination
		////////////////////////////////////////////////////////
		
		if(!empty($icebb->input['page']))
		{
			$icebb->input['start']= intval((intval($icebb->input['page'])*$per_page)-$per_page);
		}
	
		$start					= empty($icebb->input['start']) ? 0 : intval($icebb->input['start']);
		if($start < 0)
		{
			$start = 0;
		}
		
		$qextra					= " LIMIT {$start},{$per_page}";
		
		$total					= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_posts WHERE ptopicid='{$topic['tid']}'{$wextra}");
		$postnum				= $start;
		
		////////////////////////////////////////////////////////
		// Get replies
		////////////////////////////////////////////////////////
		
		$contentq				= $db->query("SELECT p.*,u.id AS uid,u.username as pauthor2,u.title,u.avatar,u.avsize,u.posts,u.joindate,u.siggie,u.date_format,g.g_title,g.g_icon,g.g_suffix,g.g_prefix FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE ptopicid='{$topic['tid']}'{$wextra} ORDER BY pid ASC{$qextra}");
		while($r				= $db->fetch_row($contentq))
		{
			$postnum++;
			$r['postnum']		= $postnum;
			
			if(empty($r['pauthor']))
			{
				$r['pauthor']	= $r['pauthor2'];
			}
		
			$r['pdate_formatted']= $std->date_format($icebb->user['date_format'],$r['pdate']);
			$r['joindate_formatted']= gmdate("F j, Y",$r['joindate']+$std->get_offset());
			
			// get member information
			$r['uauthor_username']= $this->html->uauthor($r);
			$r['uposts']		= $this->html->uposts($r['posts']);
			$r['ujoindate']		= $this->html->ujoindate($r['joindate_formatted']);
			$r['ugroup']		= $this->html->ugroup($r['g_title'],$r['g_icon']);
			if($r['away'] == true)
			{
				$r['uaway']			= $this->html->uaway($r['away']);
			}

			// RANK
			foreach($icebb->cache['ranks'] as $ra)
			{
				if($r['posts']>=$ra['rposts'])
				{
					if(!empty($r['rimg']))
					{
						$r['pips']= "<img src='{$r['rimg']}' alt='' />";
					}
					else {
						$r['pips']	= '';
						for($on=1;$on<=$ra['rpips'];$on++)
						{
							$r['pips'].= "<macro:pip />";
						}
					}
					
					$r['rank']		= $ra['rtitle'];
				}
			}
			
			// are we a guest? if so, we need to remove a few things
			if($r['uid']		== '0')
			{
				$username		= ($r['pauthor']!=$icebb->lang['guest']) ? $r['pauthor'] : $icebb->lang['guest'];
			
				$r['uauthor_username']= $this->html->uauthor_guest(array('pauthor_id'=>'0','pauthor'=>$username));
				$r['title']		= $icebb->lang['unregistered'];
				$r['ugroup']	= '';
				$r['uposts']	= '';
				$r['ujoindate']	= '';
				$r['group']		= '';
				$r['pips']		= '';
				$r['rank']		= '';
			}
			
			// Does user wish to view other user's avatars?
			if($icebb->user['view_av'] != '1')
			{
				$r['avatar']	= '';
				$r['avtype']	= 'none';
			}
			else {
				$r['avsize']	= explode('x',$r['avsize']);
			}
          
			// Does user wish to view other user's signatures?
			if($icebb->user['view_sig'] != '1')
			{
				$r['siggie'] = '';
			}
          
			if(!empty($r['siggie']))
			{
				$r['siggie']		= $this->html->siggie($post_parser->parse(array('TEXT'=>$r['siggie'],'SMILIES'=>0,'BBCODE'=>1,'ME_TAG'=>0),$pdata));
			}
		
			$r['ptext']				= $post_parser->parse($r['ptext'],$r);
		
			if($r['pedit_show']		== '1' || ($icebb->user['g_is_mod']=='1' && $r['pedit_time']>0))
			{
				$r['pedit_formatted']= gmdate($icebb->user['date_format'],$r['pedit_time']+$std->get_offset());
				$r['ptext']		   .= $this->html->post_last_edit($r,($icebb->user['g_is_mod']=='1' && $r['pedit_time']>0));
			}
			
			$this_row				= $this->html->post_row($r);
			
			// are we blocking them?
			if(in_array($r['pauthor_id'],$to_block))
			{
				$this_row			= $this->html->post_row_blocked($r);
			}
			
			// remove delete button for first post
			if($postnum				== 1)
			{
				$this_row			= str_replace('<{POST_DELETE}>','',$this_row);
			}
			
			// can we edit this post?
			if($icebb->user['username']==$r['pauthor'] && $icebb->user['id']!='0' || $icebb->user['g_is_mod']=='1' || $this->is_mod==1)
			{
				$this_row			= str_replace('<{POST_EDIT}>',$this->html->post_edit($r['pid'],$icebb->user['quick_edit']),$this_row);
				$this_row			= str_replace('<{POST_DELETE}>',$this->html->post_delete($r['pid']),$this_row);
			}
			else {
				$this_row			= str_replace('<{POST_EDIT}>','',$this_row);
				$this_row			= str_replace('<{POST_DELETE}>','',$this_row);
			}
			
			// are we a mod?
			if($icebb->user['g_is_mod']=='1' || $this->is_mod==1)
			{
				//$this_row			= str_replace('<{MOD_OPTION_MULTI_SELECT}>',$this->html->moderator_tick($r['pid']),$this_row);
				$this_row			= str_replace('<!--IP-->',$this->html->uip($r['pauthor_ip'],$r['pauthor_ip_dns']),$this_row);
				$this_row			= str_replace('<{REPORT_LINK}>',$this->html->report_link($r),$this_row);
			}
			else {
				//$this_row			= str_replace('<{MOD_OPTION_MULTI_SELECT}>','',$this_row);
				$this_row			= str_replace('<!--IP-->','',$this_row);
				$this_row			= str_replace('<{REPORT_LINK}>',$this->html->report_link($r),$this_row);
			}
			
			$this_row				= str_replace('<{MOD_OPTION_MULTI_SELECT}>',$this->html->moderator_tick($r['pid']),$this_row);
		
			$posts				   .= $this_row;
		}
		
		if($topic['rating']			== 0)
		{
			$star[1]				= "<macro:star_off />";
			$star[2]				= "<macro:star_off />";
			$star[3]				= "<macro:star_off />";
			$star[4]				= "<macro:star_off />";
			$star[5]				= "<macro:star_off />";
		}
		else if($topic['rating']	== 1)
		{
			$star[1]				= "<macro:star />";
			$star[2]				= "<macro:star_off />";
			$star[3]				= "<macro:star_off />";
			$star[4]				= "<macro:star_off />";
			$star[5]				= "<macro:star_off />";
		}
		else if($topic['rating']	== 2)
		{
			$star[1]				= "<macro:star />";
			$star[2]				= "<macro:star />";
			$star[3]				= "<macro:star_off />";
			$star[4]				= "<macro:star_off />";
			$star[5]				= "<macro:star_off />";
		}
		else if($topic['rating']	== 3)
		{
			$star[1]				= "<macro:star />";
			$star[2]				= "<macro:star />";
			$star[3]				= "<macro:star />";
			$star[4]				= "<macro:star_off />";
			$star[5]				= "<macro:star_off />";
		}
		else if($topic['rating']	== 4)
		{
			$star[1]				= "<macro:star />";
			$star[2]				= "<macro:star />";
			$star[3]				= "<macro:star />";
			$star[4]				= "<macro:star />";
			$star[5]				= "<macro:star_off />";
		}
		else {
			$star[1]				= "<macro:star />";
			$star[2]				= "<macro:star />";
			$star[3]				= "<macro:star />";
			$star[4]				= "<macro:star />";
			$star[5]				= "<macro:star />";
		}
		
		$topic['rating_stars']		= "<span id='star1' onmouseover='update_stars(1)' onclick=\"topic_rate('{$topic['tid']}',curr_rating)\">{$star[1]}</span><span id='star2' onmouseover='update_stars(2)' onclick=\"topic_rate('{$topic['tid']}',curr_rating)\">{$star[2]}</span><span id='star3' onmouseover='update_stars(3)' onclick=\"topic_rate('{$topic['tid']}',curr_rating)\">{$star[3]}</span><span id='star4' onmouseover='update_stars(4)' onclick=\"topic_rate('{$topic['tid']}',curr_rating)\">{$star[4]}</span><span id='star5' onmouseover='update_stars(5)' onclick=\"topic_rate('{$topic['tid']}',curr_rating)\">{$star[5]}</span>";
		
		$db->query("SELECT * FROM icebb_favorites WHERE favuser={$icebb->user['id']} AND favtype='topic' AND favobjid={$topic['tid']} LIMIT 1");
		$is_fav = $db->get_num_rows() > 0 ? true : false;
		
		if($icebb->user['id'] != '0' && $icebb->user['last_post']+$icebb->user['g_flood_control'] > time())
		{
			$flood_control_remain = $icebb->user['last_post']+$icebb->user['g_flood_control']-time();
		}
		
		$pagelinks					= $std->render_pagelinks(array('curr_start'=>$start,'total'=>$total['total'],'per_page'=>$per_page,'base_url'=>"{$icebb->base_url}topic={$icebb->input['topic']}&amp;"));
		$output					   .= $this->html->topic_view($topic,$posts,$pagelinks,$is_fav,$flood_control_remain);
		
		// poll?
		if($topic['has_poll']		== '1')
		{
			$db->query("SELECT * FROM icebb_polls WHERE polltid='{$topic['tid']}' LIMIT 1");
			if($db->get_num_rows()>=1)
			{
				$poll					= $db->fetch_row();
				$c						= unserialize($poll['pollopt']);
				
				$db->query("SELECT * FROM icebb_poll_voters WHERE voteruser='{$icebb->user['id']}' AND voterpollid='{$poll['pollid']}'");
				if($db->get_num_rows()>=1)
				{
					$voted				= 1;
				}
				
				if($icebb->user['id']		== '0')
				{
					$voted					= 1;
				}
				
				if($t['is_locked']			== '1')
				{
					$voted					= 1;
				}
				
				if(isset($icebb->input['vote']) && $voted!=1)
				{
					if(is_array($icebb->input['poll']))
					{
						foreach($icebb->input['poll'] as $cid => $cval)
						{
							$c[$cid]['votes']++;
						}
					}
					else {
						$c[$icebb->input['poll']]['votes']++;
					}
				
					$db->query("UPDATE icebb_polls SET pollopt='".addslashes(serialize($c))."' WHERE pollid='{$poll['pollid']}' LIMIT 1");
				
					$db->insert('icebb_poll_voters',array('voterpollid'=>$poll['pollid'],'voteruser'=>$icebb->user['id']));
				
					$voted				= 1;
				}
				
				if($voted					== 1)
				{
					$totalvotes				= 0;
				
					foreach($c as $tv)
					{
						$totalvotes		   += $tv['votes'];
					}
				
					foreach($c as $co)
					{
						$co['votes']		= intval($co['votes']);
					
						$totalvotes			= $totalvotes ? $totalvotes : 1;
					
						$co['percent']		= round(($co['votes']/$totalvotes)*100);
						
						$co['ctext']		= $post_parser->parse(array('TEXT'=>$co['ctext'],'SMILIES'=>1,'BBCODE'=>1,'ME_TAG'=>0));
						$pollchoices	   .= $this->html->poll_result($co);
					}
					
					$output					= str_replace("<!--POLL-->",$this->html->poll_results($topic,$poll['pollq'],$pollchoices),$output);
				}
				else {
					foreach($c as $co)
					{
						$co['ctext']		= $post_parser->parse(array('TEXT'=>$co['ctext'],'SMILIES'=>1,'BBCODE'=>1,'ME_TAG'=>0));
						if($poll['type']	== '2')
						{
							$pollchoices   .= $this->html->poll_choice_multi($co);
						}
						else {
							$pollchoices   .= $this->html->poll_choice($co);
						}
					}
					
					$output					= str_replace("<!--POLL-->",$this->html->poll($topic,$poll['pollq'],$pollchoices),$output);
				}
			}
		}
		
		// is the topic locked?
		if($topic['is_locked']	   == '1')
		{
			$output					= str_replace("<{QUICK_REPLY}>","",$output);
			$output					= str_replace("<{ADD_REPLY}>","<{ADD_REPLY_LOCKED}>",$output);
		}
		
		if($icebb->user['g_is_mod']=='1' || $this->is_mod==1)
		{
			// TOPIC
			$modlinks['topic']	   .= intval($topic['is_pinned']) ? $this->html->moderator_options_addlink('topic_unpin',$this->modlang['topic_unpin']) : $this->html->moderator_options_addlink('topic_pin',$this->modlang['topic_pin']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_move',$this->modlang['topic_move']);
			$modlinks['topic']	   .= intval($topic['is_locked']) ? $this->html->moderator_options_addlink('topic_unlock',$this->modlang['topic_unlock']) : $this->html->moderator_options_addlink('topic_lock',$this->modlang['topic_lock']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_delete',$this->modlang['topic_delete']);
			$modlinks['topic']	   .= !intval($topic['is_hidden']) ? $this->html->moderator_options_addlink('topic_hideshow',$this->modlang['topic_hide']) : $this->html->moderator_options_addlink('topic_hideshow',$this->modlang['topic_show']);
			$modlinks['topic']	   .= $this->html->moderator_options_addlink('topic_merge',$this->modlang['topic_merge']);
			
			// POSTS
			$modlinks['posts']	   .= $this->html->moderator_options_addlink('posts_merge',$this->modlang['posts_merge']);
			$modlinks['posts']	   .= $this->html->moderator_options_addlink('posts_delete',$this->modlang['posts_delete']);
			$modlinks['posts']	   .= $this->html->moderator_options_addlink('posts_hideshow',$this->modlang['posts_hideshow']);
			
			$output					= str_replace('<!--MODERATOR.OPTIONS-->',$this->html->moderator_options($topic,$modlinks),$output);
		}
		
		if(!$icebb->settings['cpu_disable_users_viewing'])
		{
			$num['members']			= 1;
			$num['guests']			= 0;
			$num['total']			= 1;
		
			if($icebb->user['id']!='0')
			{
				$users_viewing		= "<a href='{$icebb->base_url}profile={$icebb->user['id']}'>{$icebb->user['g_prefix']}{$icebb->user['username']}{$icebb->user['g_suffix']}</a>";
			}
			else {				// eww... a guest
				$num['members']		= 0;
				$num['guests']		= 1;
			}
		
			$db->query("SELECT u.*,g.* FROM icebb_session_data AS s LEFT JOIN icebb_users AS u ON s.user_id=u.id LEFT JOIN icebb_groups AS g ON g.gid=u.user_group WHERE s.topic='{$topic['tid']}' AND s.last_action>".(time()-(15*60))."");
			while($u = $db->fetch_row())
			{
				if($u['id'] == '0')
				{
					$num['guests']++;
					$num['total']++;
				}
				else if(strpos($users_viewing,$u['username'])===false && !empty($u['username']))
				{
					if(isset($users_viewing))
					{
						$users_viewing .= ", ";
					}
			
					$users_viewing  .= "<a href='{$icebb->base_url}profile={$u['id']}'>{$u['g_prefix']}{$u['username']}{$u['g_suffix']}</a>";
				
					$num['members']++;
					$num['total']++;
				}
			}
		
			$output					= str_replace('<!--USERS_VIEWING-->',$this->html->users_viewing($num,$users_viewing),$output);
		}
		
		$forumlist					= $std->get_forum_listing();
		$forumlist					= $this->forum_list_children($forumlist,'');
		
		//$output						= str_replace("<!--FORUM_JUMP-->",$icebb->html->forum_dropdown($forumlist),$output);
		
		$output						= $this->html->display($output);
		$icebb->skin->html_insert($output);
		$icebb->skin->do_output();
	}
	
	function forum_list_children($list,$fn)
	{
		global $icebb,$db,$config,$std;
		
		$c						= 0;
		
		return;
		
		if(is_array($list))
		{
			foreach($list as $f)
			{
				if($f['fid']	== $this->topic['forum'])
				{
					$l		   .= $icebb->html->forum_dropdown_forum_selected($f);
				}
				else {
					$l		   .= $icebb->html->forum_dropdown_forum($f);
				}
				
				$l			   .= $this->forum_list_children($list[$c]['children'],$f['fid']);
				$c++;
			}
		}
		
		return $l;
	}
	
	function report_post()
	{
		global $icebb,$db,$config,$std;
		
		$p							= $db->fetch_result("SELECT * FROM icebb_posts WHERE pid='{$icebb->input['report']}'");
		$t							= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='{$p['ptopicid']}'");
		
		if(isset($icebb->input['submit']))
		{
			if(empty($icebb->input['reason']))
			{
				$icebb->input['reason']= $icebb->lang['no_reason'];
			}
			
			$title					= $icebb->lang['reported_post_title'];
			$text					= $icebb->lang['reported_post_text'];
			$text					= str_replace('<#pid#>',$p['pid'],$text);
			$text					= str_replace('<#title#>',$t['title'],$text);
			$text					= str_replace('<#reason#>',$icebb->input['reason'],$text);
			$text					= str_replace('<#url#>',$icebb->input['url'],$text);
			
			$db->query("SELECT u.*,g.* FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE g.g_is_mod='1'");
			while($u				= $db->fetch_row())
			{
				$std->send_mail($u['email'],$title,$text);
				$output				= $this->html->report_post_done($t,$p);
			}
		}
		else {
			$output					= $this->html->report_post_window($t,$p);
		}
		
		$icebb->skin->do_popup_window($output);
	}
	
	function add_favorite()
	{
		global $icebb,$db,$config,$std;
		
		if(!isset($icebb->input['topic']))
		{
			$std->error($icebb->lang['no_topic_selected']);
		}
		
		if($icebb->user['id']		== '0')
		{
			$std->error($icebb->lang['unauthorized'],1);
			exit();
		}
		
		$db->query("SELECT * FROM icebb_favorites WHERE favuser='{$icebb->user['id']}' AND favtype='topic' AND favobjid='{$icebb->input['topic']}'");
		if($db->get_num_rows()>=1)
		{
			$std->error($icebb->lang['fav_already_added']);
		}
		
		$db->insert('icebb_favorites',array(
			'favuser'				=> $icebb->user['id'],
			'favtype'				=> 'topic',
			'favobjid'				=> $icebb->input['topic'],
		));
		
		$std->bouncy_bouncy($icebb->lang['fav_added'],"{$icebb->base_url}topic={$icebb->input['topic']}");
	}
	
	
	function post_history()
	{
		global $icebb,$db,$std,$post_parser;
		
		if($icebb->user['g_is_mod']!='1')
		{
			exit();
		}
		
		if(!empty($icebb->input['rv1']) && !empty($icebb->input['rv2']))
		{
			require('includes/classes/Diff.inc.php');
		
			$db->query("SELECT p.*,u.* FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id WHERE p.pid='".intval($icebb->input['phistory'])."'");
			$p						= $db->fetch_row();
			$p['pedits']			= unserialize($p['pedits']);
			$p1diff					= explode("\n",$p['pedits'][$icebb->input['rv1']]['ptext']);
			$p2diff					= explode("\n",$p['pedits'][$icebb->input['rv2']]['ptext']);
			
			$diff					= new Text_Diff($p1diff,$p2diff);
			$renderer				= new Text_Diff_Renderer_inline();
			$diffs					= $renderer->render($diff);
			$diffs					= nl2br($diffs);
			$output					= $this->html->post_history_diff($p,$diffs);
		}
		else {
			$db->query("SELECT * FROM icebb_users");
			while($u				= $db->fetch_row())
			{
				$usernames[$u['id']]= $u['username'];
			}
		
			$db->query("SELECT * FROM icebb_posts WHERE pid='".intval($icebb->input['phistory'])."'");
			$p						= $db->fetch_row();
			$p['pedits']			= unserialize($p['pedits']);
			
			foreach($p['pedits'] as $edit_id => $r)
			{
				$postnum++;
				$r['postnum']		= $postnum;
			
				$r['pid']			= $p['pid'];
				$r['editid']		= $edit_id;
				$r['pdate_formatted']= $std->date_format($icebb->user['date_format'],$r['pdate']);
				$r['pauthor']		= $usernames[$r['pauthor_id']];
				$r['ptext']			= stripslashes($post_parser->parse($r['ptext'],$r));
			
				$edits			   .= $this->html->post_history_row($r);
			}
			
			$output					= $this->html->post_history($p,$edits);
		}
		
		$output						= $this->html->display($output);
		$icebb->skin->html_insert($output);
		$icebb->skin->do_output();
	}

	function print_page()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		$topicid				= intval($icebb->input['topic']);

		// this had better make Snuffkin happy
		if(empty($topicid))
		{
			$topics				= $db->query("SELECT * FROM icebb_topics");
			while($topi			= $db->fetch_row())
			{
				if(preg_replace("`[!\.\?]`",'',str_replace(' ','-',strtolower($topi['title'])))==$icebb->input['topic'])
				{
					$topic		= $topi;
				}
			}
		}
		else {
			$topic				= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='".intval($icebb->input['topic'])."'");
		}

		if(!empty($topic['description']))
		{
			$topic['title']	   .= ", {$topic['description']}";
		}

		$perms					= unserialize(html_entity_decode($icebb->cache['forums'][$topic['forum']]['perms']));
		if(!$perms[$icebb->user['g_permgroup']]['read'])
		{
			$std->error($icebb->lang['not_allowed_read'],1);
		}
		
		$topic['title_pf']		= sprintf($icebb->lang['printer_friendly_ver_t'],$topic['title']);
		
		$this->topic			= $topic;
		
		$db->query("UPDATE icebb_topics SET views=views+1 WHERE tid='{$topic['tid']}'",1);
		
		$contentq				= $db->query("SELECT p.*,u.id as uid,u.username as pauthor2,u.title,u.avatar,u.posts,u.joindate,u.siggie,u.date_format,g.g_title,g.g_suffix,g.g_prefix FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE ptopicid='{$topic['tid']}' ORDER BY pid ASC");
		while($r				= $db->fetch_row($contentq))
		{
			$r['pdate_formatted']= $std->date_format($icebb->user['date_format'],$r['pdate']);
			
			if(empty($r['pauthor']))
			{
				$r['pauthor']	= $r['pauthor2'];
			}
			
			// are we a guest? if so, we need to remove a few things
			if($r['uid']		== '0')
			{
				$r['pauthor']	= ($r['pauthor']!=$icebb->lang['guest']) ? $r['pauthor'] : $icebb->lang['guest'];
			}
			
			$r['ptext']				= preg_replace("`\[url=(.*)\](.*)\[/url\]`",'\\1',$r['ptext']);
			$r['ptext']				= $post_parser->parse($r['ptext'],$r);
		
			$posts				   .= $this->html->print_page_post($r);
		}


		if($icebb->skin_data['skin_css_method']=='1')
		{
			$css					= "<style type='text/css' media='screen'>@import 'skins/{$icebb->skin_data['skin_id']}/css.css'</style>";
		}
		else {
			$cssy					= str_replace('<#skin_images#>',"skins/{$icebb->skin_data['skin_id']}/images",$icebb->skin_data['skin_css']);
			$css					= "<style type='text/css' media='screen'>\n{$cssy}\n</style>";
		}
		
		
		$js							= "<script type='text/javascript' src='jscripts/global.js'></script>";
		$js						   .= "<script type='text/javascript' src='jscripts/xmlhttp.js'></script>";
		$js						   .= "<script type='text/javascript' src='jscripts/menu.js'></script>";
		$js						   .= "<script type='text/javascript'>icebb_base_url='{$icebb->base_url}';icebb_sessid='{$icebb->user['sid']}';icebb_cookied_domain='{$icebb->settings['cookie_domain']}';icebb_cookied_prefix='{$icebb->settings['cookie_prefix']}';icebb_cookied_path='{$icebb->settings['cookie_path']}';</script>";
		
		$output						= $this->html->print_page($topic,$posts);
		$output						= str_replace("<#CSS#>",$css,$output);
		$output						= str_replace("<#JAVASCRIPT#>",$js,$output);
	
		echo $output;
		exit();
	}

	function email_topic()
	{
		global $icebb,$db,$std;
		
		if($icebb->user['id'] <= 0)
		{
			$std->error($icebb->lang['please_login'], 1);
		}
		
		$topicid				= intval($icebb->input['topic']);

		// this had better make Snuffkin happy
		if(empty($topicid))
		{
			$topics				= $db->query("SELECT * FROM icebb_topics");
			while($topi			= $db->fetch_row())
			{
				if(preg_replace("`[!\.\?]`",'',str_replace(' ','-',strtolower($topi['title'])))==$icebb->input['topic'])
				{
					$topic		= $topi;
				}
			}
		}
		else {
			$topic				= $db->fetch_result("SELECT * FROM icebb_topics WHERE tid='".intval($icebb->input['topic'])."'");
		}

		if(!empty($topic['description']))
		{
			$topic['title']	   .= ", {$topic['description']}";
		}

		$perms					= unserialize(html_entity_decode($icebb->cache['forums'][$topic['forum']]['perms']));
		if(!$perms[$icebb->user['g_permgroup']]['read'])
		{
			$std->error($icebb->lang['not_allowed_read'],1);
		}
		
		if(!empty($icebb->input['submit']))
		{
			$icebb->lang['email_title']= str_replace('<#title#>',$topic['ttitle'],$icebb->lang['email_title']);
			$icebb->lang['email_msg']= str_replace('<#to#>',$icebb->input['mail_to_name'],$icebb->lang['email_msg']);
			$icebb->lang['email_msg']= str_replace('<#from#>',$icebb->user['username'],$icebb->lang['email_msg']);
			$icebb->lang['email_msg']= str_replace('<#msg#>',$icebb->input['msg'],$icebb->lang['email_msg']);
		
			$std->send_mail($icebb->input['mail_to'],$icebb->lang['email_title'],$icebb->lang['email_msg'],"From: {$icebb->user['username']} <{$icebb->user['email']}>",true);

			$std->redirect("{$icebb->base_url}topic={$topic['tid']}",$icebb->lang['email_sent']);
		}
	
		$output						= $this->html->email_topic($topic);
		$output						= $this->html->display($output);
		$icebb->skin->html_insert($output);
		$icebb->skin->do_output();
	}
}
?>
