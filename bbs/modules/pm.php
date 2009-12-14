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
// pm module
// $Id: pm.php 777 2007-02-24 17:27:37Z mutantmonkey0 $
//******************************************************//

class pm
{
	function run()
	{
		global $icebb,$config,$db,$std;
				
		$this->html						= $icebb->skin->load_template('pm');
		$this->thtml					= $icebb->skin->load_template('topic');
		$this->posthtml					= $icebb->skin->load_template('post');
		$this->postlang					= $std->learn_language('post');
		$this->lang						= $std->learn_language('topics','pm');
		$icebb->nav[]					= "<a href='{$icebb->base_url}act=pm'>{$icebb->lang['title']}</a>";
		
		if($icebb->user['id']			== '0')
		{
			$std->error($this->lang['please_login'],1);
		}
		
		if($icebb->user['disable_pm']	== '1')
		{
			$std->error($this->lang['no_perms_pm']);
		}
		
		// tagging
		require('includes/classes/tagging.inc.php');
		$this->tagging					= new tagging;
		
		$this->security_key				= $std->make_me_some_random_md5_kthxbye();
		$icebb->security_key			= $this->security_key;
		
		if(!empty($icebb->input['read']))
		{
			$icebb->input['func']		= 'read'; 
		}
		
		switch($icebb->input['func'])
		{
			case 'write':
				$this->write();
				break;
			case 'read':
				$this->read();
				break;
			case 'del':
				$this->del();
				break;
			case 'fwd':
				$this->fwd();
				break;
			case 'tag':
				$this->tag();
				break; 
			default:
				$this->list_pm();
				break;
		}
		
		$this->output					= $this->html->layout($this->output);
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function list_pm()
	{
		global $icebb,$config,$db,$std;

		if(!empty($icebb->input['tag']))
		{
			//$tag					= $db->fetch_result("SELECT id FROM icebb_tags WHERE tag='{$icebb->input['tag']}' AND type='pm'");
			$tag					= $icebb->input['tag'];
			$db->query("SELECT t.*,tag.* FROM icebb_pm_topics AS t LEFT JOIN icebb_tagged AS tag ON t.tid=tag.tag_objid WHERE t.owner='{$icebb->user['id']}' AND t.deleted=0 AND tag.tag_id='{$tag}' AND tag.tag_uid='{$icebb->user['id']}' ORDER BY lastpost_time DESC");
			while($pm_info			= $db->fetch_row())
			{
				//$pms			   .= $this->html->list_pm($pm_info);
				$pms[]				= $pm_info;
			}
		}
		else {
			$db->query("SELECT t.*,u.id FROM icebb_pm_topics AS t LEFT JOIN icebb_users AS u ON t.starter=u.username WHERE owner='{$icebb->user['id']}' AND deleted=0 ORDER BY lastpost_time DESC");
			while($pm_info			= $db->fetch_row())
			{
				//$pms			   .= $this->html->list_pm($pm_info);
				$pms[]				= $pm_info;
			}
		}
		
		if($db->get_num_rows()<=0)
		{
				//$pms			= "<em>{$icebb->lang['no']}</em>";
		}
		
		$db->query("SELECT * FROM icebb_tags WHERE type='pm' AND owner='{$icebb->user['id']}'");
		while($tag				= $db->fetch_row())
		{
			$tags[]				= $tag;
		}
			
		$this->output		 	= $this->html->pm_list($pms,$tags);
	}
	
	function read()
	{
		global $icebb,$db,$config,$std,$post_parser;
		
		$per_page				= empty($icebb->input['per_page']) ? 10 : intval($icebb->input['per_page']);
		$start					= empty($icebb->input['start']) ? 0 : intval($icebb->input['start']);
		$qextra					= " LIMIT {$start},{$per_page}";
		
		require('includes/classes/post_parser.php');
		$post_parser					= new post_parser();
		
		if($icebb->user['new_pms']>='1')
		{
			$db->query("UPDATE icebb_users SET new_pms=new_pms-1 WHERE id='{$icebb->user['id']}'");
		}
		
		$this->modlang			= $std->learn_language('moderate'); 
		
		$topicq					= $db->query("SELECT * FROM icebb_pm_topics WHERE tid='{$icebb->input['read']}'{$qextra}");
		$topic					= $db->fetch_row($topicq);

		if($icebb->input['show']=='lastpost')
		{
			$lastpostq			= $db->query("SELECT * FROM icebb_pm_posts WHERE ptopicid='{$topic['pm_identifier']}' ORDER BY pid DESC");
			$lastpost			= $db->fetch_row($lastpostq);
		
			$std->redirect("{$icebb->base_url}act=pm&read={$topic['pm_identifier']}&#post-{$lastpost['pid']}");
		}
		
		if(isset($icebb->input['pid']))
		{
			$lastpostq			= $db->query("SELECT * FROM icebb_pm_posts WHERE pid='{$icebb->input['pid']}' AND ptopicid='{$topic['pm_identifier']}'");
			$lastpost			= $db->fetch_row($lastpostq);
		
			$std->redirect("{$icebb->base_url}act=pm&read={$topic['pm_identifier']}&#post-{$lastpost['pid']}");
		}

		if($db->get_num_rows($topicq)<=0 || $topic['deleted']=='1')
		{
			$std->error($icebb->lang['pm_not_exist'],1);
		}
		
		if($topic['owner']!=$icebb->user['id'] && $topic['starter']!=$icebb->user['username'])
		{
			$std->error($icebb->lang['not_yours'],1);
		}

		$icebb->nav[]			= $topic['title'];
		$icebb->lang['title_content']= $topic['title'];
		
		$total					= $db->fetch_result("SELECT COUNT(*) AS total FROM icebb_pm_posts WHERE ptopicid='{$icebb->input['pm_identifier']}'");
		$postnum				= $start;
		
		////////////////////////////////////////////////////////
		// Get replies
		////////////////////////////////////////////////////////
		
		$db->query("SELECT p.*,u.id as uid,u.title,u.avatar,u.posts,u.joindate,u.siggie,u.date_format,g.g_title,g.g_suffix,g.g_prefix FROM icebb_pm_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE ptopicid='{$topic['pm_identifier']}' ORDER BY pid ASC");
		while($r				= $db->fetch_row())
		{
			$postnum++;
			$r['postnum']		= $postnum;
		
			$r['pdate_formatted']= $std->date_format($icebb->user['date_format'],$r['pdate']);
			$r['joindate_formatted']= gmdate("F j, Y",$r['joindate']+$std->get_offset());
			
			// get member information
			$r['uauthor_username']= $this->thtml->uauthor($r);
			$r['uposts']		= $this->thtml->uposts($r['posts']);
			$r['ujoindate']		= $this->thtml->ujoindate($r['joindate_formatted']);
			$r['ugroup']		= $this->thtml->ugroup($r['g_title']);
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
			
			// normally we would deal with guests here, but guests can't send PMs
			
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
				// my sig at the time this was added:
				// 	[18:23]   Kinky KN brain
				// 	[18:23] ^_^
				// 	[18:23] O_O
				// 	[18:23] my barin is not kinked
				// 	KN's brain must be kinked if he can't even spell brain :P
			
				$r['siggie']		= $this->thtml->siggie($post_parser->parse(array('TEXT'=>$r['siggie'],'SMILIES'=>0,'BBCODE'=>1,'ME_TAG'=>0),$pdata));
			}
		
			$r['ptext']				= $post_parser->parse($r['ptext'],$r);
		
			$this_row				= $this->html->post_row($r);
			
			$this_row				= str_replace('<{P_REPLY}>','',$this_row);
			$this_row				= str_replace('<{POST_EDIT}>','',$this_row);
			$this_row				= str_replace('<{POST_DELETE}>','',$this_row);
			$this_row				= str_replace('<{REPORT_LINK}>','',$this_row);
			$this_row				= str_replace('<{MOD_OPTION_MULTI_SELECT}>','',$this_row);

			$posts				   .= $this_row;
		}
		
		$tags						= array();
		$db->query("SELECT tagged.*,tag.id,tag.tag FROM icebb_tagged AS tagged LEFT JOIN icebb_tags AS tag ON tagged.tag_id=tag.id WHERE tagged.tag_type='pm' AND tagged.tag_objid='{$topic['tid']}'");
		while($tag					= $db->fetch_row())
		{
			$topics['tags'][]		= $tag;
			$tags[]					= "<a href='{$icebb->base_url}act=pm&amp;tag={$tag['id']}'>{$tag['tag']}</a>";
		}
		$topic['tag_html']			= implode(' ',$tags);
		
		$pagelinks					= $std->render_pagelinks(array('curr_start'=>$start,'total'=>$total['total'],'per_page'=>$per_page,'base_url'=>"{$icebb->base_url}topic={$icebb->input['topic']}&amp;"));
		
		$this->output				= $this->html->topic_view($topic,$posts,$pagelinks);
	}
	
	function tag()
	{
		global $icebb,$db,$std;
		
		// stop watching me :P 
		
		$pm							= $db->fetch_result("SELECT * FROM icebb_pm_topics WHERE tid='{$icebb->input['id']}' AND owner='{$icebb->user['id']}'");
		
		if(!empty($icebb->input['tags']))
		{
			$tags						= str_replace('&quot;','"',$icebb->input['tags']);
			$tags						= $this->tagging->split_tags($tags);
			foreach($tags as $newtag)
			{
				$this->tagging->add_tag('pm',$pm['tid'],$newtag);
			}
		}
		else if(!empty($icebb->input['deltag']))
		{
			$this->tagging->del_tag('pm',$pm['tid'],$icebb->input['deltag']);
		}
				
		$std->bouncy_bouncy($this->lang['pm_tags_updated'],"{$icebb->base_url}act=pm&read={$pm['tid']}");
	}
	
	function del()
	{
		global $icebb,$db,$std;
		
		//$db->query("DELETE FROM icebb_pm_topics WHERE tid='{$icebb->input['id']}' AND owner='{$icebb->user['id']}' LIMIT 1");
		$db->query("UPDATE icebb_pm_topics SET deleted=1 WHERE tid='{$icebb->input['id']}' AND owner='{$icebb->user['id']}' LIMIT 1");
		$std->bouncy_bouncy($this->lang['pm_deleted'],"{$icebb->base_url}act=pm");
	}
	
	function write()
	{
		global $icebb,$db,$config,$std;
		
		$icebb->nav[]					= $this->lang['compose'];
		
		if(!empty($icebb->input['reply']))
		{
			$hidden_fields			   .= "<input type='hidden' name='reply' value='{$icebb->input['reply']}' />";

			$topic						= $db->fetch_result("SELECT * FROM icebb_pm_topics WHERE tid='{$icebb->input['reply']}'");
			if(strtolower($topic['owner'])!=strtolower($icebb->user['id']) && $topic['starter']!=$icebb->user['username'])
			{
				$std->error($icebb->lang['not_yours'],1);
			}
		}
		else if(!empty($icebb->input['edit']))
		{
			$ptoedit					= $db->fetch_result("SELECT * FROM icebb_pm_posts WHERE pid='{$icebb->input['edit']}'");
			$ptext						= $ptoedit['ptext'];
			$hidden_fields			   .= "<input type='hidden' name='edit' value='{$icebb->input['edit']}' />";
		}
		else {
			$show_to_and_subj			= true; 
		}
		
		if(!isset($icebb->input['submit']))
		{
			$this->show_compose_form($ptext, $show_to_and_subj);
		}
		else {
			if($icebb->input['security_key'] != $this->security_key)
			{
				$std->error($this->lang['unauthorized']);
			}
		
			if(empty($icebb->input['body']))
			{
				$this->show_compose_form(null, $show_to_and_subj, $this->lang['complete_all']);
				return;
			}
		
			// fix the posts
			if($icebb->input['wysiwyg'] == '1')
			{
				$icebb->input['body']		= $post_parser->html_to_bbcode($icebb->input['body']);
			}
		
			$post							= wash_ebul_tags($icebb->input['body']);
		
			if(isset($icebb->input['reply']))
			{
				$topicsq		= $db->query("SELECT * FROM icebb_pm_topics WHERE tid='{$icebb->input['reply']}' LIMIT 1");
				$last_topic		= $db->fetch_row($topicsq);
				$tid			= $last_topic['pm_identifier'];
				
				$otha_guy		= $db->fetch_result("SELECT owner FROM icebb_pm_topics WHERE pm_identifier='{$tid}' AND tid!='{$last_topic['tid']}'");
				$other_guy		= intval($otha_guy['owner']);
				
				// are we being blocked?
				$budds			= $db->fetch_result("SELECT buddies FROM icebb_users WHERE id={$other_guy}");
				$buds			= @unserialize($budds['buddies']);
				if(is_array($buds))
				{
					foreach($buds as $tb)
					{
						if($tb['type']!=2) continue;
					
						if($tb['uid']==$icebb->user['id'])
						{
							$this->show_compose_form(null, $show_to_and_subj, $this->lang['you_blocked']);
							return;
						}
					}
				}
			
				$db->insert('icebb_pm_posts',array(
								'pid'			=> $pid,
								'ptopicid'		=> $tid,
								'pauthor_id'	=> $icebb->user['id'],
								'pauthor'		=> $icebb->user['username'],
								'pauthor_ip'	=> $icebb->user['ip'],
								'pdate'			=> time(),
								'ptext'			=> $post,
								));

				$db->query("UPDATE icebb_pm_topics SET replies=replies+1,deleted=0,lastpost_time='".time()."',lastpost_author='{$icebb->user['username']}' WHERE pm_identifier='{$tid}'");

				$db->query("UPDATE icebb_users SET new_pms=new_pms+1 WHERE id=".intval($other_guy));
				
				$std->bouncy_bouncy($this->postlang['reply_added'],"{$icebb->base_url}act=pm&amp;read={$icebb->input['reply']}&amp;");
			}
			else if(isset($icebb->input['edit']))
			{
				$pdata			= $db->fetch_result("SELECT * FROM icebb_pm_posts WHERE pid='{$icebb->input['edit']}'");
				// can we edit this post?
				if($icebb->user['username']!= $pdata['pauthor'] && $icebb->user['g_is_mod']!='1')
				{
					$std->error($this->lang['access_denied'],1);
				}
			
				$db->query("UPDATE icebb_pm_posts SET ptext='{$post}',pedit_show='1',pedit_author='{$icebb->user['username']}',pedit_time='".time()."' WHERE pid='{$icebb->input['edit']}'");
			
				$std->bouncy_bouncy($this->lang['pm_edited'],"{$icebb->base_url}act=pm&amp;read={$pdata['ptopicid']}&amp;pid={$icebb->input['edit']}");
			}
			else {
				$icebb->input['subject']	= trim($icebb->input['subject']);
				if(empty($icebb->input['to']) || empty($icebb->input['subject']))
				{
					$this->show_compose_form($post, $show_to_and_subj, $this->lang['complete_all']);
					return;
				}
				
				$pm_tod						= $db->fetch_result("SELECT id,buddies FROM icebb_users WHERE username='{$icebb->input['to']}'");
				$pm_to						= $pm_tod['id'];
				
				// does the user even_exist
				if(empty($pm_to))
				{
					$this->show_compose_form($post, $show_to_and_subj, $this->lang['user_not_exist']);
					return;
				}
				
				// are we being blocked?
				$buds						= @unserialize($pm_tod['buddies']);
				if(is_array($buds))
				{
					foreach($buds as $tb)
					{
						if($tb['type']!=2) continue;
					
						if($tb['uid'] == $icebb->user['id'])
						{
							$this->show_compose_form($post, $show_to_and_subj, $this->lang['you_blocked']);
							return;
						}
					}
				}
			
				$topicsq					= $db->query("SELECT * FROM icebb_pm_topics ORDER BY tid DESC LIMIT 1");
				$last_topic					= $db->fetch_row($topicsq);
				$tid						= $last_topic['tid']+1;
					
				$postsq						= $db->query("SELECT * FROM icebb_pm_posts ORDER BY pid DESC LIMIT 1");
				$last_post					= $db->fetch_row($postsq);
				$pid						= $last_post['pid']+1;
					   
				$db->insert('icebb_pm_topics',array(
					'title'					=> $icebb->input['subject'],
					'snippet'				=> substr($post,0,255),
					'starter'				=> $icebb->user['username'],
					'owner'					=> $icebb->user['id'],
					'lastpost_time'			=> time(),
					'lastpost_author'		=> $icebb->user['username'],
					'pm_identifier' 		=> $tid,
				));
				
				$db->insert('icebb_pm_topics',array(
					'title'					=> $icebb->input['subject'],
					'snippet'				=> substr($post,0,255),
					'starter'				=> $icebb->user['username'],
					'owner'					=> $pm_to,
					'lastpost_time'			=> time(),
					'lastpost_author'		=> $icebb->user['username'],
					'pm_identifier'			=> $tid,
				));
				
				$db->insert('icebb_pm_posts',array(
					'pid'					=> $pid,
					'ptopicid'				=> $tid,
					'pauthor_id'			=> $icebb->user['id'],
					'pauthor'				=> $icebb->user['username'],
					'pauthor_ip'			=> $icebb->user['ip'],
					'pdate'					=> time(),
					'ptext'					=> $post,
				));
				
				$db->query("UPDATE icebb_users SET new_pms=new_pms+1 WHERE username='{$icebb->input['to']}'");
				
				if(!empty($icebb->input['tags']))
				{
					$tags					= str_replace('&quot;','"',$icebb->input['tags']);
					$tags					= $this->tagging->split_tags($tags);
					foreach($tags as $newtag)
					{
						$this->tagging->add_tag('pm',$tid,$newtag);
					}
				}
				
				$std->bouncy_bouncy($this->lang['pm_sent'],$icebb->base_url.'act=pm');
			}
		}
	}
	
	function show_compose_form($ptext="", $show_to = true, $message="")
	{
		global $icebb,$db,$std,$post_parser;
		
		////////////////////////////////////////////////////////
		// Messages to display?
		////////////////////////////////////////////////////////
		
		$messages			= array();
		
		if(!empty($message))
		{
			$messages[]		= $message;
		}
		
		////////////////////////////////////////////////////////
		// Smilies
		////////////////////////////////////////////////////////
		
		foreach($icebb->cache['smilies'] as $s)
		{
			if($s['clickable']=='1')
			{
				$s['code']	= str_replace("'",urlencode("'"),$s['code']);
			
				$smilies   .= "<a href='javascript:smiley(\"{$s['code']}\",\"{$icebb->settings['board_url']}smilies/{$s['smiley_set']}/{$s['image']}\")'><img src='smilies/{$s['smiley_set']}/{$s['image']}' alt=\"{$s['code']}\" /></a> ";
			}
		}
		
		////////////////////////////////////////////////////////
		// Editor
		////////////////////////////////////////////////////////
		
		$editor_style		= !empty($icebb->user['editor_style']) ? $icebb->user['editor_style'] : $icebb->settings['default_editor_style'];
		if($editor_style	== '3')
		{
			$ptext			= $post_parser->parse(array('TEXT'=>$ptext,'SMILIES'=>1,'BBCODE'=>1,'BAD_WORDS'=>0,'ME_TAG'=>0,'YOU_TAG'=>0,'PARSE_ATTACHMENTS'=>0,'PARSE_QUOTES'=>0));
			$ptext			= preg_replace("`(\r|\n)`",'',$ptext);
			$ptext			= preg_replace("`rel=('|\")nofollow('|\")`i",'',$ptext);
			$editor			= $this->posthtml->wysiwyg_editor('postFrm','body',$ptext);
		}
		else if($editor_style=='2')
		{
			$editor			= $this->posthtml->richtext_editor('postFrm','body',$ptext);
		}
		else {
			$editor			= $this->posthtml->basic_editor('postFrm','body',$ptext);
		}
		
		$hidden_fields	   .= "<input type='hidden' name='security_key' value='{$this->security_key}' />";
		$pform				= $this->html->pm_form($smilies,$hidden_fields,$editor,$ptext,$messages);
		
		if($show_to)
		{
			if(!empty($icebb->input['send_to']))
			{
				$db->query("SELECT username FROM icebb_users WHERE id='".intval($icebb->input['send_to'])."'");
				$sendtou			= $db->fetch_row();
				$sendto				= $sendtou['username'];
			}
		
			// get our buddy list
			$buds					= @unserialize($icebb->user['buddies']);
			if(is_array($buds))
			{
				$uids				= array();
			
				foreach($buds as $tb)
				{
					if($tb['type']==2) continue;
				
					$buddies[$tb['uid']]= $tb;
					$uids[]			= $tb['uid'];
				}
					
				$db->query("SELECT id,username FROM icebb_users WHERE id IN (".implode(',',$uids).")");
				while($b			= $db->fetch_row())
				{
					$buddies[$b['id']]['username']= $b['username'];
				}
			}
		
			$pform					= str_replace("<!--TO.INPUT-->",$this->html->pm_to_input($sendto,$buddies),$pform);
			$pform					= str_replace("<!--SUBJECT.INPUT-->",$this->html->pm_subject_input(),$pform);
		}
		
		$this->output				= $pform;
	}
}
?>
