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
// add post module
// $Id: post.php 826 2007-05-24 22:08:40Z mutantmonkey0 $
//******************************************************//

class post
{
	function run()
	{
		global $icebb,$config,$db,$std,$post_parser;

		require('includes/classes/post_parser.php');
		$post_parser					= new post_parser;
		
		require('includes/classes/subscriptions.inc.php');
		$this->subscriptions			= new subscriptions;
		
		require('includes/apis/core.api.php');
		$api_core						= new api_core(&$icebb,&$db,&$std);
		require('includes/apis/post.api.php');
		$this->post_lib					= new post_lib(&$this,&$api_core);
		
		// tagging
		require('includes/classes/tagging.inc.php');
		$this->tagging					= new tagging;
		
		$this->lang						= $std->learn_language('post');
		$this->html						= $icebb->skin->load_template('post');
		
		$this->security_key				= $std->make_me_some_random_md5_kthxbye();

		$icebb->hooks->hook('post_init');

		if($icebb->user['disable_post']	== '1')
		{
			$std->error($this->lang['no_perms_post']);
		}
		
		if($icebb->user['id'] != '0' && $icebb->user['last_post']+$icebb->user['g_flood_control'] > time())
		{
			$remaining_time = $icebb->user['last_post']+$icebb->user['g_flood_control']-time();
			$std->error(sprintf($this->lang['flood_control'],$remaining_time));
		}
		
		if($icebb->input['func']!='smilies')
		{
			if(!empty($icebb->input['reply']))
			{
				$topic						= $db->fetch_result("SELECT t.*,f.perms FROM icebb_topics AS t LEFT JOIN icebb_forums AS f ON t.forum=f.fid WHERE t.tid='{$icebb->input['reply']}' LIMIT 1");
				$topic['perms']				= unserialize($topic['perms']);
				if(!$topic['perms'][$icebb->user['g_permgroup']]['reply'])
				{
					$std->error($this->lang['no_perms_reply'],1);
				}
			}
			else if(isset($icebb->input['edit']))
			{
				$topic						= $db->fetch_result("SELECT p.*,t.* FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid WHERE p.pid='{$icebb->input['edit']}' LIMIT 1");
			}
			else {
				$forum						= $db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$icebb->input['forum']}' LIMIT 1");
				$forum['perms']				= unserialize($forum['perms']);
				if(!$forum['perms'][$icebb->user['g_permgroup']]['createtopics'])
				{
					$std->error($this->lang['no_perms_newtopic'],1);
				}
			}
		}
		
		if($topic['is_locked']=='1' && $icebb->user['g_post_in_locked']!='1')
		{
			$std->error($this->lang['topic_locked']);
		}
		
		$this->topic						= $topic;
		
		$forum								= $db->fetch_result("SELECT * FROM icebb_forums WHERE fid='{$topic['forum']}'");
		// can we moderate here?
		if(in_array($forum['fid'],$icebb->user['moderate']))
		{
			$this->is_mod		= 1;
		}
		
		if(isset($icebb->input['submit']))
		{
			//print_r($icebb->input);die();
			// fix the posts
			if($icebb->input['wysiwyg']		== '1')
			{
				$icebb->input['post']		= $post_parser->html_to_bbcode($icebb->input['post']);
			}
		
			if($icebb->input['security_key']!=$this->security_key)
			{
				$std->error($this->lang['unauthorized']);
			}
		
			$icebb->input['post']			= trim($icebb->input['post']);
		
			if(empty($icebb->input['post']))
			{
				$this->show_post_form(array($this->lang['no_post_entered']));
				$icebb->skin->html_insert($this->output);
				$icebb->skin->do_output();
				exit();
			}
			
			if($icebb->user['id']=='0' && ($icebb->settings['use_word_verification']=='1' &&
			   $icebb->settings['use_word_verification_posting']=='1'))
			{
				$captcha_q					= $db->query("SELECT * FROM icebb_captcha WHERE id='{$icebb->input['captcha_code']}'");
				$captcha_data				= $db->fetch_row($captcha_q);
				$captcha_words				= @file("langs/{$icebb->lang_id}/captcha.dict");
				
				if($captcha_words[$captcha_data['word_num']]!=$_POST['captcha_word']."\n")
				{
					$this->show_post_form($this->lang['word_invalid']);
					$icebb->skin->html_insert($this->output);
					$icebb->skin->do_output();
					exit();
				}
			}
			
			$post							= wash_ebul_tags($icebb->input['post']);
		
			if(!empty($icebb->input['reply']))
			{
				$this->post_lib->new_reply($icebb->input['topic'],$icebb->user['id'],$post);

				$std->bouncy_bouncy($this->lang['reply_added'],"{$icebb->base_url}topic={$icebb->input['reply']}&show=lastpost");
			}
			else if(isset($icebb->input['edit']))
			{
				// can we edit this post?
				if($icebb->user['id'] != $this->topic['pauthor_id'] && $icebb->user['g_is_mod'] != '1' && $icebb->user['id'] != '0' && $this->is_mod != 1)
				{
					$std->error($this->lang['unauthorized']);
				}
			
				if($this->topic['pis_firstpost'] == '1')
				{
					$this->post_lib->edit_topic($icebb->input['edit'], $this->topic['tid'], $icebb->input['ptitle'], $icebb->input['pdesc'], $post, $icebb->input['picon']);
				}
				else {
					$this->post_lib->edit_reply($icebb->input['edit'], $post);
				}
				
				if(!empty($icebb->input['ajax']))
				{	
					$this->lang				= $std->learn_language('post','topics');
					$tophtml				= $icebb->skin->load_template('topic');
					
					$r						= $topic;
					$r['pedit_time']		= time();
					$r['ptext']				= $post_parser->parse($post);
					
					//if($r['pedit_show']	== '1' || ($icebb->user['g_is_mod']=='1' && $r['pedit_time']>0))
					if(1)					// we should always show the last edit line when using quick edit
					{
						$r['pedit_formatted']= gmdate($icebb->user['date_format'],$r['pedit_time']+$std->get_offset());
						$r['ptext']		   .= $tophtml->post_last_edit($r,($icebb->user['g_is_mod']=='1' && $r['pedit_time']>0));
					}
					
					echo $r['ptext'];
					exit();
				}

				$std->bouncy_bouncy($this->lang['post_edited'],"{$icebb->base_url}topic={$topic['ptopicid']}&pid={$icebb->input['edit']}");
			}
			else {
				$icebb->input['ptitle']= trim($icebb->input['ptitle']);
			
				if(empty($icebb->input['ptitle']))
				{
					$this->show_post_form(array($this->lang['no_topic_title_entered']));
					$icebb->skin->html_insert($this->output);
					$icebb->skin->do_output();
					exit();
				}
			
				$topicsq		= $db->query("SELECT * FROM icebb_topics ORDER BY tid DESC LIMIT 1");
				$last_topic		= $db->fetch_row($topicsq);
				$tid			= $last_topic['tid']+1;
				
				$postsq			= $db->query("SELECT * FROM icebb_posts ORDER BY pid DESC LIMIT 1");
				$last_post		= $db->fetch_row($postsq);
				$pid			= $last_post['pid']+1;
								
				// are we doing a poll?
				if(!empty($icebb->input['pollq']))
				{
					foreach($icebb->input['pollc'] as $id => $c)
					{
						if(!empty($c))
						{
							$poll_choice[$id]= array('cid'=>$id,'ctext'=>$c);
						}
					}
					
					$db->insert('icebb_polls',array(
						'polltid'				=> $tid,
						'pollq'					=> $icebb->input['pollq'],
						'type'					=> $icebb->input['polltype'],
						'pollopt'				=> serialize($poll_choice),
					));
			
					$this->post_lib->new_topic($icebb->input['forum'],$icebb->input['picon'],$icebb->input['ptitle'],$icebb->input['pdesc'],$icebb->user['id'],$post,1);
				}
				else {
					$this->post_lib->new_topic($icebb->input['forum'],$icebb->input['picon'],$icebb->input['ptitle'],$icebb->input['pdesc'],$icebb->user['id'],$post);
				}
				
				if(!empty($icebb->input['tags']))
				{
					$tags						= str_replace('&quot;','"',$icebb->input['tags']);
					$tags						= $this->tagging->split_tags($tags);
					foreach($tags as $newtag)
					{
						$this->tagging->add_tag('topic',$this->post_lib->last_topic_id,$newtag);
					}
				}
				
				if($icebb->user['g_is_mod']) // because the fields only "do bla bla bla"-fields are only visible if that is true
				{
					$do_stuff_sets = array();
					if($icebb->input['lock_after_post'] == 1)
					{
						$do_stuff_sets[] = "is_locked='1'";
					}
					if($icebb->input['pin_after_post'] == 1)
					{
						$do_stuff_sets[] = "is_pinned='1'";
					}
					
					if(count($do_stuff_sets))
					{
						$db->query("UPDATE icebb_topics SET ".join(',',$do_stuff_sets)." WHERE tid='{$this->post_lib->last_topic_id}' LIMIT 1");
					}
				}

				$std->bouncy_bouncy($this->lang['topic_added'],"index.php?topic={$tid}");
			}
		}
		else if($icebb->input['func']=='smilies')
		{
			$set				= $icebb->skin_data['smiley_set'];
			foreach($icebb->cache['smilies'][$set] as $s)
			{
				$s['code2']		= str_replace("'",urlencode("'"),$s['code']);
			
				$smiliez	   .= "<tr><td class='row1' style='font-size:80%' width='40%'>{$s['code']}</td><td class='row2'><a href='#' onclick='parent.opener.smiley(\"{$s['code2']}\",\"{$icebb->settings['board_url']}smilies/{$s['smiley_set']}/{$s['image']}\");window.close();return false'><img src='smilies/{$s['smiley_set']}/{$s['image']}' alt=\"{$s['code2 ']}\" /></a></td></tr>";
			}
			
			$output				= "<div class='borderwrap' style='width:288px;margin:6px'><h2>Smilies</h2><table width='100%' cellpadding='2' cellspacing='1'>{$smiliez}</table></div>";
			
			$icebb->skin->do_popup_window($output);
			exit();
		}
		else if(isset($icebb->input['upload']))
		{
			if($icebb->user['id'] == 0)
			{
				$std->error($this->lang['unable_upload']);
			}
		
			if(!empty($icebb->input['upload_url']) &&
			   $icebb->input['upload_url']!='http://')
			{
				if(substr($icebb->input['upload_url'],0,7)!='http://')
				{
					$icebb->input['upload_url']= 'http://'.$icebb->input['upload_url'];
				}
				
				$filedata	= @file_get_contents($icebb->input['upload_url']);
				if(empty($filedata))
				{
					$std->error($this->lang['unable_upload']);
				}
			
				$filename	= basename($icebb->input['upload_url']);
				$ext		= explode('.',$filename);
				$unique_id	= uniqid(md5(time()));
				$uploaddir	= $icebb->settings['board_url'].'uploads/';
				$filepath	= $uploaddir.$ext[0]."-{$unique_id}.upload";
				$uploadfile	= $icebb->settings['board_path']."uploads/{$ext[0]}-{$unique_id}.upload";
			 
				$db->insert('icebb_uploads',array(
					'uname'	=> $filename,
					'upath'	=> $filepath,
					'usize'	=> strlen($filedata),
					'uowner'=> $icebb->user['id'],
				));
				
				$fh			= @fopen($uploadfile,'w');
				if(!$fh)
				{
					$std->error($this->lang['unable_upload']);
				}
				@fwrite($fh,$filedata);
				@fclose($fh);
			}
			else {
				$ext		= explode('.',$_FILES['file']['name']);
				if($ext[1]=='php' || $ext[1]=='js' || $ext[1]=='vbs' || $ext[1]=='pl' || $ext[1]=='cgi')
				{
					//$ext[1]		= 'txt';
				}
				
				$unique_id	= uniqid(md5(time()));
				$uploaddir	= $icebb->settings['board_url'].'uploads/';
				$filepath	= $uploaddir.$ext[0]."-{$unique_id}.upload";
				$file		= $_FILES['file']['name'];
				$uploadfile	= $icebb->settings['board_path']."uploads/{$ext[0]}-{$unique_id}.upload";
			 
				if(!move_uploaded_file($_FILES['file']['tmp_name'],$uploadfile))
				{
					$std->error($this->lang['unable_upload']);
				}
				
				$db->insert('icebb_uploads',array(
					'uname'	=> $file,
					'upath'	=> $uploadfile,
					'usize'	=> $_FILES['file']['size'],
					'uowner'=> $icebb->user['id'],
				));
			}
			
			if($icebb->input['func']=='upload_form')
			{
				$newid		= $db->fetch_result("SELECT uid FROM icebb_uploads ORDER BY uid DESC LIMIT 1");
				$this->upload_form($js_extra);
			}
			else {
				$this->show_post_form();
			}
		}
		else if($icebb->input['func']=='upload_form')
		{
			$this->upload_form();
		}
		else if(!empty($icebb->input['preview']))
		{
			$this->output		= $this->html->post_preview($post_parser->parse($icebb->input['post']));
			$this->topic['ptext']= $icebb->input['post'];		

			$this->show_post_form();
		}
		else {
			$this->show_post_form();
		}
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function upload_form($extra_js='')
	{
		global $icebb,$db,$std;
		
		if($icebb->user['id'] == 0)
		{
			exit();
		}
		
		$db->query("SELECT * FROM icebb_uploads WHERE uowner='{$icebb->user['id']}'");
		while($u				= $db->fetch_row())
		{
			$current_uploads   .= $this->html->post_attach_attachment($u);
		}
		
		$output					= $this->html->post_attach_form_iframe($current_uploads,$extra_js);
		$output					= str_replace('<#SKIN#>',$icebb->skin->skin_id,$output);
		echo $output;
		exit();
	}
	
	function show_post_form($messages=array(),$ptext='',$ttitle='',$tdesc='')
	{
		global $icebb,$db,$config,$std,$post_parser;
		
		if(!empty($icebb->input['ptitle']))
		{
			$ttitle			= $icebb->input['ptitle'];
		}
		else if(isset($icebb->input['submit']))
		{
			$messages[] = $icebb->lang['no_title_entered'];
		}
		
		if(!empty($icebb->input['pdesc']))
		{
			$tdesc 			= $icebb->input['pdesc'];
		}
		
		if(!empty($icebb->input['tags']))
		{
			$ttags 			= $icebb->input['tags'];
		}
		
		if(!empty($icebb->input['post']))
		{
			$ptext			= $icebb->input['post'];
		}
		
		if(!empty($icebb->input['quote']))
		{
			$toquote		= $db->fetch_result("SELECT p.*,f.perms,u.username as pauthor2 FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON t.forum=f.fid LEFT JOIN icebb_users AS u ON u.id=p.pauthor_id WHERE p.pid='{$icebb->input['quote']}'");
			$toquote['perms']= unserialize($toquote['perms']);
			if(!$toquote['perms'][$icebb->user['g_permgroup']]['read'])
			{
				$std->error($this->lang['no_perms_quote']);
			}
			else {
				if(empty($toquote['pauthor']))
				{
					$toquote['pauthor']= $toquote['pauthor2'];
				}
			
				$toquote['ptext']= $post_parser->bad_words($toquote['ptext']);
				$ptext		= "[quote pid={$toquote['pid']} author=".str_replace("]",'',$toquote['pauthor'])." date={$toquote['pdate']}]{$toquote['ptext']}[/quote]\n\n";
			}
		}
		
		if(!empty($icebb->input['checkedpids']))
		{
			if(is_array($icebb->input['checkedpids']))
			{
				$pids_direct				= $icebb->input['checkedpids'];
			}
			else {
				$pids_direct				= explode(',',$icebb->input['checkedpids']);
			}
			
			foreach($pids_direct as $quote_pid)
			{
				$toquote		= $db->fetch_result("SELECT p.*,f.perms,u.username as pauthor2 FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON t.forum=f.fid LEFT JOIN icebb_users AS u ON u.id=p.pauthor_id WHERE p.pid='{$quote_pid}'");
				$toquote['perms']= unserialize($toquote['perms']);
				if(!$toquote['perms'][$icebb->user['g_permgroup']]['read'])
				{
					$std->error($this->lang['no_perms_quote']);
				}
				else {
					if(empty($toquote['pauthor']))
					{
						$toquote['pauthor']= $toquote['pauthor2'];
					}
				
					$toquote['ptext']= $post_parser->bad_words($toquote['ptext']);
					$ptext	  .= "[quote pid={$toquote['pid']} author=".str_replace("]",'',$toquote['pauthor'])." date={$toquote['pdate']}]{$toquote['ptext']}[/quote]\n\n";
				}
			}
		}
	
		if(isset($icebb->input['reply']))
		{
			$db->query("UPDATE icebb_session_data SET topic='{$icebb->input['reply']}' WHERE sid='{$icebb->user['sid']}' LIMIT 1",1);
		
			$type			= 'reply';
			$title			= $this->lang['add_reply'];
			$extra_fields	= "<input type='hidden' name='reply' value='{$icebb->input['reply']}' />";
			if(!empty($icebb->input['post']))
			{
				$ptext		= $icebb->input['post'];
			}
			
			$t				= $this->topic;
			$fparent		= $t['forum'];
			
			$topic_review	= $this->render_topic_review($icebb->input['reply']);
		}
		else if(isset($icebb->input['edit']))
		{
			// can we edit this post?
			if($icebb->user['id'] != $this->topic['pauthor_id'] && $icebb->user['g_is_mod'] !='1' && $icebb->user['id'] != '0' && $this->is_mod != 1)
			{
				$std->error($this->lang['unauthorized']);
			}
		
			if($icebb->input['del'] == '1')
			{
				$this->post_lib->delete_post($icebb->input['edit'],$icebb->user['id'],$this->topic,0);
				
				$std->bouncy_bouncy($this->lang['post_deleted'],"{$icebb->base_url}topic={$this->topic['tid']}");
			}
				
			// quick edit
			if(!empty($icebb->input['get_raw_post']))
			{
				$pdata						= $db->fetch_result("SELECT * FROM icebb_posts WHERE pid='{$icebb->input['edit']}'");
				echo $this->html->quick_edit_form($this->security_key,$icebb->input['edit'],$pdata['ptext']);
				exit();
			}
		
			$type				= 'edit';
			$title				= "Edit Post";
			$extra_fields		= "<input type='hidden' name='edit' value='{$icebb->input['edit']}' />";
			$ptext				= $this->topic['ptext'];
			
			$t					= $this->topic;
			$fparent			= $t['forum'];
			
			$topic_review		= $this->render_topic_review($icebb->input['edit']);
		}
		else {
			$type				= 'topic';
			$title				= $this->lang['new_topic'];
			$extra_fields		= "<input type='hidden' name='forum' value='{$icebb->input['forum']}' />";
			
			$t					= $db->fetch_result("SELECT name as forumname FROM icebb_forums WHERE fid='{$icebb->input['forum']}'"); 
		
			$fparent			= $icebb->input['forum'];
		}

		while($fparent		   != 0)
		{
			$f					= $icebb->cache['forums'][$fparent];
			$nav[]				= "<a href='{$icebb->base_url}forum={$f['fid']}'>{$f['name']}</a>";
			$fparent			= $f['parent'];
		}
		
		for($i = count($nav)-1; $i >= 0; $i--)
		{
			$icebb->nav[]		= $nav[$i];
		}

		if(empty($t['forumname']))
		{
			$icebb->nav[]		= "<a href='{$icebb->base_url}topic={$t['tid']}'>{$t['title']}</a>";
		}
		
		//$icebb->nav			= array($title);
		$icebb->nav[]			= $title;
		
		$set					= $icebb->skin_data['smiley_set'];
		if($icebb->cache['smilies'][$set])
		{
			foreach($icebb->cache['smilies'][$set] as $s)
			{
				if($s['clickable'] == '1')
				{
					$s['code']	= str_replace("'",urlencode("'"),$s['code']);
			
					$smilies   .= "<a href='#' onclick='smiley(\"{$s['code']}\",\"{$icebb->settings['board_url']}smilies/{$s['smiley_set']}/{$s['image']}\");return false'><img src='smilies/{$s['smiley_set']}/{$s['image']}' alt=\"{$s['code']}\" /></a> ";
				}
			}
		}
		
		$extra_fields		   .= "<input type='hidden' name='security_key' value='{$this->security_key}' />";
		
		$editor_style			= !empty($icebb->user['editor_style']) ? $icebb->user['editor_style'] : $icebb->settings['default_editor_style'];
		if($editor_style == '3')
		{
			$ptext				= $post_parser->parse(array('TEXT'=>$ptext,'SMILIES'=>1,'BBCODE'=>1,'BAD_WORDS'=>0,'ME_TAG'=>0,'YOU_TAG'=>0,'PARSE_ATTACHMENTS'=>0,'PARSE_QUOTES'=>0));
			$ptext				= preg_replace("`(\r|\n)`",'',$ptext);
			$ptext				= preg_replace("`rel=('|\")nofollow('|\")`i",'',$ptext);
			$editor				= $this->html->wysiwyg_editor('postFrm','post',$ptext);
		}
		else if($editor_style =='2')
		{
			$editor				= $this->html->richtext_editor('postFrm','post',$ptext);
		}
		else {
			$smilies			= '';
			$editor				= $this->html->basic_editor('postFrm','post',$ptext);
		}	
		
		$output				   .= $this->html->post_box($title,$smilies,$extra_fields,$t,$editor,$topic_review,$messages);
		
		if($type == 'topic')
		{
			// topic icons
			$icon_dir				= SKIN_DIR . "/{$icebb->skin->skin_id}/images/post_icons/";
			$ticons					= "";
			if(is_dir($icon_dir))
			{
				$dh					= opendir($icon_dir);
				while(($file = readdir($dh)) !== false)
				{
					$ext			= explode('.', $file);
					$ext			= $ext[count($ext) - 1];
					
					if($ext == 'gif' || $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg')
					{
						$ticons    .= "<label><input type='radio' name='picon' value='{$file}' /> <img src='{$icon_dir}{$file}' alt='{$file}' /></label>\n";
					}
				}
				@closedir($dh);
			}
		
			$output				= str_replace('<!--TOPIC_TITLE-->', $this->html->topic_title_fields($ttitle, $tdesc, $ttags, $ticons), $output);

			$output				= str_replace('<!--POLL_LINK-->', $this->html->post_attach_poll_link(),$output);
			$output				= str_replace('<!--POLL_FORM-->', $this->html->post_attach_poll_form(),$output);
		}
		
		if($this->topic['pis_firstpost']=='1')
		{
			//if($icebb->user['g_edit_own_ttitle']=='1' || $icebb->user['g_is_mod']=='1')
			if(1)
			{
				// tags
				$tags			= array();
				$db->query("SELECT tagged.*,tag.tag FROM icebb_tagged AS tagged LEFT JOIN icebb_tags AS tag ON tagged.tag_id=tag.id WHERE tagged.tag_type='topic' AND tagged.tag_objid='{$this->topic['tid']}'");
				while($tag		= $db->fetch_row())
				{
					if(strpos($tag['tag'],' ')!==false)
					{
						$tag['tag']= "\"{$tag['tag']}\"";
					}
				
					$tags[]		= $tag['tag'];
				}
				
				$tags			= implode(' ',$tags);
				
				// topic icons
				$icon_dir				= SKIN_DIR . "/{$icebb->skin->skin_id}/images/post_icons/";
				$icons					= "";
				if(is_dir($icon_dir))
				{
					$dh					= opendir($icon_dir);
					while(($file = readdir($dh)) !== false)
					{
						$ext			= explode('.', $file);
						$ext			= $ext[count($ext) - 1];
						
						$s				= ($this->topic['icon'] == $file) ? " checked='checked'" : "";
					
						if($ext == 'gif' || $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg')
						{
							$icons    .= "<label><input type='radio' name='picon' value='{$file}'{$s} /> <img src='{$icon_dir}{$file}' alt='{$file}' /></label>\n";
						}
					}
					@closedir($dh);
				}
			
				$output			= str_replace('<!--TOPIC_TITLE-->',$this->html->topic_title_fields($this->topic['title'], $this->topic['description'], $tags, $icons), $output);
			}
		}
		
		$output					= str_replace('<!--BOARD_RULES-->',$this->html->boardrules_line(),$output);
		
		if($icebb->user['id'] > 0)
		{
			$output				= str_replace('<!--UPLOAD_LINK-->',$this->html->post_attach_link(),$output);
			$output				= str_replace('<!--UPLOAD_FORM-->',$this->html->post_attach_form(),$output);
			if($type!='topic')
			{
				$output			= str_replace("$('post_attach_poll_form').style.display='none';$('attach_poll_tab').className='tab';",'',$output);
			}
		}

		if($icebb->user['id']!='0')
		{
			$db->query("SELECT * FROM icebb_uploads WHERE uowner='{$icebb->user['id']}' ORDER BY uid ASC");
			while($u			= $db->fetch_row())
			{
				$myuploads	   .= $this->html->post_attach_attachment($u);
			}
			$output				= str_replace('<#MY_UPLOADS#>',$myuploads,$output);
		}
		else {
			$output				= str_replace('<#MY_UPLOADS#>','',$output);
		}
		
		if($icebb->user['id']=='0' && ($icebb->settings['use_word_verification'] &&
		   $icebb->settings['use_word_verification_posting']))
		{
			$captcha_code		= $std->captcha_makecode();
	
			$output				= str_replace('<!--WORD_VERIFICATION-->',$this->html->word_verification($captcha_code),$output);
		}
		
		$this->output		  .= $output;
	}
	
	function render_topic_review($tid)
	{
		global $icebb,$db,$config,$std,$post_parser;
	
		$meinhtml		= $icebb->skin->load_template('topic');
			
		$db->query("SELECT p.*,u.id as uid,u.username as pauthor2,u.title,u.avatar,u.posts,u.joindate,u.siggie,u.date_format,g.g_title FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE ptopicid='{$tid}' ORDER BY pid ASC");
		while($r				= $db->fetch_row())
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
			$r['uauthor_username']= $meinhtml->uauthor($r);
			$r['uposts']		= $meinhtml->uposts($r['posts']);
			$r['ujoindate']		= $meinhtml->ujoindate($r['joindate_formatted']);
			$r['ugroup']		= $meinhtml->ugroup($r['g_title']);
			
			// are we a guest? if so, we need to remove a few things
			if($r['uid']		== '0')
			{
				$r['uauthor_username']= $meinhtml->uauthor_guest(array('pauthor_id'=>'0','pauthor'=>$r['pauthor']));
				$r['title']		= $this->lang['unregistered'];
				$r['uposts']	= '';
				$r['ujoindate']	= '';
				$r['group'] = '';
			}
			
			// Do user wish to view other user's avatars?
			if($icebb->user['view_av'] != '1')
			{
				$r['avatar'] = '';
				$r['avtype'] = 'none';
			}
		  
			// Do user wish to view other user's signatures?
			if($icebb->user['view_sig'] != '1')
			{
				$r['siggie'] = '';
			}
		  
			if(!empty($r['siggie']))
			{
				// my sig at the time this was added:
				// 	[18:23]   Kinky KN brain
				// 	[18:23] ^_^
				// 	[18:23] ^_^
				// 	[18:23] O_O
				// 	[18:23] my barin is not kinked
				// 	KN's brain must be kinked if he can't even spell brain :P
			
				$r['siggie']		= $meinhtml->siggie($post_parser->parse(array('TEXT'=>$r['siggie'],'SMILIES'=>0,'BBCODE'=>1,'ME_TAG'=>0),$pdata));
			}
		
			$r['ptext_unparsed']	= str_replace("\r\n","\\n",addslashes($r['ptext']));
			$r['ptext']				= $post_parser->parse($r['ptext'],$r);
		
			$this_row				= $meinhtml->post_row($r);
			
			$this_row				= str_replace('<{POST_EDIT}>','',$this_row);
			$this_row				= str_replace('<{POST_DELETE}>','',$this_row);
			$this_row				= str_replace('<{P_QUOTE}>','',$this_row);
			
			$this_row				= str_replace('<{MOD_OPTION_MULTI_SELECT}>','',$this_row);
			$this_row				= str_replace('<!--IP-->','',$this_row);
			$this_row				= str_replace('<{REPORT_LINK}>','&nbsp;',$this_row);
		
			//$topic_review.= $this_row;
			$rows[]					= $this_row;
			$this_row	= '';
		}
		
		for($i=count($rows);$i>=0;$i--)
		{
			$topic_review		   .= $rows[$i];
		}
			
		return $topic_review;
	}
}
?>
