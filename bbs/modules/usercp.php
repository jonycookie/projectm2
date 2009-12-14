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
// user control center module
// $Id: usercp.php 802 2007-04-14 18:14:22Z mutantmonkey0 $
//******************************************************//

class usercp
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->html						= $icebb->skin->load_template('usercp');
		$this->posthtml					= $icebb->skin->load_template('post');
		$this->lang						= $std->learn_language('usercp');
		$this->postlang					= $std->learn_language('post');
		
		$icebb->nav						= array("<a href='{$icebb->base_url}act=ucp'>{$this->lang['title']}</a>");
		
		if(empty($icebb->user['id']))
		{
			$std->error($this->lang['please_login'],1);
		}
		
		$icebb->hooks->hook('ucp_init');
		
		switch($icebb->input['func'])
		{
			case 'profile':
				$this->profile();
				break;
			case 'signature':
				$this->signature();
				break;
			case 'email':
				$this->email();
				break;
			case 'password':
				$this->pass();
				break;
			case 'avatar':
				$this->avatar();
				break;
			case 'uploadav':
				$this->avup();
				break;
			case 'favorites':
				$this->fav();
				break;
			case 'uploads':
				$this->uploads();
				break;
			case 'emailset':
				$this->emailset();
				break;
			case 'settings':
				$this->settings();
				break;
			case 'dateset':
				$this->dateset();
				break;
			case 'iptools':
				$this->iptools();
				break;
			case 'away_system':
				$this->away_system();
				break;
			case 'buddies':
				$this->buddies();
				break;
			default:
				$this->main();
				break;
		}
		
		$this->output				= $this->html->layout($this->output);
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function main()
	{
		global $icebb,$config,$db,$std;
		
		if(!empty($icebb->input['notepad']))
		{
			$db->query("UPDATE icebb_users SET notepad='{$icebb->input['notepad']}' WHERE id='{$icebb->user['id']}' LIMIT 1");
			
			//$std->bouncy_bouncy("Your notepad has been saved","{$icebb->base_url}act=ucp");
			$std->redirect("{$icebb->base_url}act=ucp");
		}
		
		$icebb->user['joined_formatted']= $std->date_format($icebb->user['date_format'],$icebb->user['joindate']);
		
		$icebb->user['days_registered']= (time()-$icebb->user['joindate'])/(3600*24);
		$icebb->user['posts_per_day']	= round($icebb->user['posts']/$icebb->user['days_registered'],2);
		
		$db->query("SELECT f.*,t.* FROM icebb_favorites AS f LEFT JOIN icebb_topics AS t ON f.favobjid=t.tid WHERE f.favuser='{$icebb->user['id']}' AND f.favtype='topic'");
		while($f			= $db->fetch_row())
		{
			$favorite_topics.= $this->html->main_favtopic($f);
		}
		
		$db->query("SELECT * FROM icebb_pm_topics WHERE owner='{$icebb->user['id']}' AND deleted=0");
		$pm['topics']		= $db->get_num_rows();
		
		$db->query("SELECT t.*,p.* FROM icebb_pm_topics AS t RIGHT JOIN icebb_pm_posts AS p ON p.ptopicid=t.tid WHERE t.owner='{$icebb->user['id']}' AND t.deleted=0");
		$pm['posts']		= $db->get_num_rows();
		
		$icebb->hooks->hook('ucp_main');
		
		$this->output		= $this->html->main($favorite_topics,$pm);
	}
	
	function profile()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->lang			= $std->learn_language('usercp','profile');
		
		if($icebb->user['icq'] == '0')
		{
			$icebb->user['icq']	= '';
		}
		
		$g						= array();
		$g['u']					= "\t<option value='u'>-</option>";
		$g['m']					= "\t<option value='m'>{$icebb->lang['male']}</option>";
		$g['f']					= "\t<option value='f'>{$icebb->lang['female']}</option>";
		switch($icebb->user['gender'])
		{
			case 'm':
				$g['m']			= "\t<option value='m' selected='selected'>{$icebb->lang['male']}</option>";
				break;
			case 'f':
				$g['f']			= "\t<option value='f' selected='selected'>{$icebb->lang['female']}</option>";
				break;
			default:
				$g['u']			= "\t<option value='u' selected='selected'>-</option>";
				break;
		}
		
		if(empty($icebb->input['submit']))
		{
			$user_dob_clean		= explode('.',date('m.d.Y',$icebb->user['birthdate']));
		
			$months[1]			= $icebb->lang['month_jan'];
			$months[2]			= $icebb->lang['month_feb'];
			$months[3]			= $icebb->lang['month_mar'];
			$months[4]			= $icebb->lang['month_apr'];
			$months[5]			= $icebb->lang['month_may'];
			$months[6]			= $icebb->lang['month_jun'];
			$months[7]			= $icebb->lang['month_jul'];
			$months[8]			= $icebb->lang['month_aug'];
			$months[9]			= $icebb->lang['month_sep'];
			$months[10]			= $icebb->lang['month_oct'];
			$months[11]			= $icebb->lang['month_nov'];
			$months[12]			= $icebb->lang['month_dec'];
		
			for($i=1;$i<=12;$i++)
			{
				if($i			== $user_dob_clean[0])
				{
					$dob['month'].= "<option value='{$i}' selected='selected'>{$months[$i]}</option>";
				}
				else {
					$dob['month'].= "<option value='{$i}'>{$months[$i]}</option>";
				}
			}
		
			for($i=1;$i<=31;$i++)
			{
				if($i			== $user_dob_clean[1])
				{
					$dob['day'].= "<option value='{$i}' selected='selected'>{$i}</option>";
				}
				else {
					$dob['day'].= "<option value='{$i}'>{$i}</option>";
				}
			}
			
			for($i=date('Y');$i>=1900;$i--)
			{
				if($user_dob_clean[2]==$i)
				{
					$dob['year'].= "<option value='{$i}' selected='selected'>{$i}</option>";
				}
				else {
					$dob['year'].= "<option value='{$i}'>{$i}</option>";
				}
			}
		
			$icebb->hooks->hook('ucp_profile_edit');
		
			$this->output		= $this->html->editProfile($dob,$g,$this->plugin_extra);
		}
		else {
			require('includes/classes/post_parser.php');
			$post_parser		= new post_parser();
		
			// URL
			$url				= $icebb->input['url'];
			if(substr($url, 0, 7) != 'http://')
			{
				$url			= 'http://' . $url;
			}
		
			if(!empty($icebb->input['dob_month']) && !empty($icebb->input['dob_day']) && !empty($icebb->input['dob_year']))
			{
				$date_of_birth	= gmmktime(12,0,0,intval($icebb->input['dob_month']),intval($icebb->input['dob_day']),intval($icebb->input['dob_year']));
			}
		
			$update				= array(
				'title'			=> $db->escape_string($icebb->input['title']),
				'location'		=> $db->escape_string($icebb->input['location']),
				'gender'		=> $db->escape_string($icebb->input['gender']),
				'url'			=> $db->escape_string($url),
				'msn'			=> $db->escape_string($icebb->input['msn']),
				'yahoo'			=> $db->escape_string($icebb->input['yahoo']),
				'aim'			=> $db->escape_string($icebb->input['aim']),
				'icq'			=> $db->escape_string($icebb->input['icq']),
				'jabber'		=> $db->escape_string($icebb->input['jabber']),
				'birthdate'		=> $db->escape_string($date_of_birth),
			);
			
			$icebb->hooks->hook('ucp_profile_edit_update');
			
			foreach($update as $k => $v)
			{
				$update_sects[]	= "{$k}='{$v}'";
			}
		
			$updateq = $db->query("UPDATE icebb_users SET ".implode(',',$update_sects)." WHERE id='{$icebb->user['id']}' LIMIT 1");

			if(!empty($date_of_birth))
			{
				$db->query("SELECT * FROM icebb_users");
				while($u			= $db->fetch_row())
				{
					if(!empty($u['birthdate']))
					{
						$bds['uid']	= $u['id'];
						$bds['username']= $u['username'];
				
						$u['birthdate']= @explode('.',@gmdate('m.d.Y',$u['birthdate']));
						$bd['bmonth']= $u['birthdate'][0];
						$bd['bday']= $u['birthdate'][1];
						$bd['byear']= $u['birthdate'][2];
				
						$bds['year']= $bd['byear'];
				
						$bdays[$bd['bmonth']][$bd['bday']][]= $bds;
					}
				}
				$std->recache($bdays,'birthdays');
			}
			
			$std->bouncy_bouncy($this->lang['saved'],"{$icebb->base_url}act=ucp&func=profile");
		}
	}
	
	function signature()
	{
		global $icebb,$config,$db,$std,$post_parser;
				
		require('includes/classes/post_parser.php');
		$post_parser							= new post_parser();
				
		if(!isset($icebb->input['sig']))
		{
			$currsig			= $post_parser->parse(array('TEXT'=>$icebb->user['siggie'],'SMILIES'=>0,'BBCODE'=>1,'ME_TAG'=>0));

			$editor_style		= !empty($icebb->user['editor_style']) ? $icebb->user['editor_style'] : $icebb->settings['default_editor_style'];
			if($editor_style	== '3')
			{
				$form			= $this->posthtml->wysiwyg_editor('signatureInfo','sig',nl2br($icebb->user['siggie']));
			}
			else if($editor_style== '2')
			{
				$form			= $this->posthtml->richtext_editor('signatureInfo','sig',$icebb->user['siggie']);
			}
			else {
				$form			= $this->posthtml->basic_editor('signatureInfo','sig',$icebb->user['siggie']);
			}
		
			$this->output		= $this->html->editSignature($currsig,$form);
		}
		else {
			// fix it if we're coming from WYSIWYG
			if($icebb->input['wysiwyg']		== '1')
			{
				$icebb->input['sig']		= $post_parser->html_to_bbcode($icebb->input['sig']);
			}
		
			$db->query("UPDATE icebb_users SET siggie='{$icebb->input['sig']}' WHERE id='{$icebb->user['id']}' LIMIT 1");
			
			$std->bouncy_bouncy($this->lang['sig_update'],"{$icebb->base_url}act=ucp&func=signature");
		}
	}
	
	function email()
	{
		global $icebb,$config,$db,$std;
		
		if(!$icebb->input['mail'] && !$icebb->input['mailc'])
		{
			$this->output		= $this->html->editEmail();
		}
		else if($icebb->input['mail'] === $icebb->user['email'])
		{
			$std->error($this->lang['email_same']);
		}
		else if(empty($icebb->input['mail']) || empty($icebb->input['mailc']))
		{
			$std->error($this->lang['empty']);
		}
		else if($icebb->input['mail'] != $icebb->input['mailc'])
		{
			$std->error($this->lang['email_match']);
		}
		else {
			$db->query("UPDATE icebb_users SET email='{$icebb->input['mail']}' WHERE id='{$icebb->user['id']}' LIMIT 1");
			
			$std->send_mail($icebb->input['mail'], $this->lang['email_subject'], $this->lang['email_msg']);
			
			$std->bouncy_bouncy($this->lang['saved'], "{$icebb->base_url}act=ucp&func=email");
		}
	}
	
	// Password Change
	function pass()
	{
		global $icebb,$config,$db,$std,$login_func;
		
		if(empty($icebb->user['password']))
		{
			$this->output		= $this->html->editPassNone();
			return;
		}
				
		$salty			= md5(crypt(make_salt(27)));
		
		$current_date = date('l dS of F Y h:i:s A');
		if(!$icebb->input['pass_confirm'] && !$icebb->input['pass_new'] && !$icebb->input['pass_confirm'])
		{
			$this->output		= $this->html->editPass();
		}
		else if($pass_new_md5 === $icebb->user['password'])
		{
			$std->error($this->lang['pass_same']);
		}
		else if(!$icebb->input['pass_old'] || !$icebb->input['pass_new'] || !$icebb->input['pass_confirm'])
		{
			$std->error($this->lang['empty']);
		}
		else if($icebb->input['pass_new'] != $icebb->input['pass_confirm'])
		{
			$std->error($this->lang['pass_match']);
		}
		else {
			if(md5(md5($icebb->input['pass_old']).$icebb->user['pass_salt'])!=$icebb->user['password'])
			{
				$std->error($this->lang['pass_wrong_error']);
			}
			
			$pass_new_md5	= md5(md5($icebb->input['pass_new']).$salty);
			
			$login_key		= md5(uniqid(rand(), true));
			
			$updateq		= $db->query("UPDATE icebb_users SET password='{$pass_new_md5}',pass_salt='{$salty}',login_key='{$login_key}' WHERE id='{$icebb->user['id']}' LIMIT 1");
			
			$std->send_mail($icebb->user['mail'], $this->lang['pass_email_subject'], $this->lang['pass_email_notice']);
			$std->bouncy_bouncy($this->lang['saved'],"{$icebb->base_url}act=ucp&func=password");
		}
	}
	
	function avatar()
	{
		global $icebb,$config,$db,$std;
		
		if(isset($icebb->input['submit']))
		{
			switch($icebb->input['avtype'])
			{
				case 'upload':
					$this->avup();
					$std->bouncy_bouncy($this->lang['saved'],$icebb->base_url.'act=ucp&func=avatar');
					break;
					
				case 'url':
					if(!empty($icebb->input['av_url']))
					{
						list($w,$h)			= @getimagesize($icebb->input['av_url']);
						$db->query("UPDATE icebb_users SET avatar='{$icebb->input['av_url']}',avtype='url',avsize='{$w}x{$h}' WHERE id='{$icebb->user['id']}' LIMIT 1");
				
						$std->bouncy_bouncy($this->lang['saved'],$icebb->base_url.'act=ucp&func=avatar');
					}
					break;
					
				case 'none':
					$db->query("UPDATE icebb_users SET avatar='',avtype='none',avsize='' WHERE id='{$icebb->user['id']}' LIMIT 1");
				
					$std->bouncy_bouncy($this->lang['saved'],$icebb->base_url.'act=ucp&func=avatar');
					break;
				
				default:
					$std->error($this->lang['unauthorized']);
					//$std->bouncy_bouncy('Redirecting you to skenmy\'s favorite site.','http://goatse.cx/');
					break;
			}
		}
		else {
			$this->output		= $this->html->editAvatar($icebb->user);
		}
	}
	
	function avup()
	{
		global $icebb,$config,$db,$std;
		
		$retval					= true;
		
		$ext					= explode('.',$_FILES['file']['name']);
		$ext					= $ext[count($ext)-1];
		
		$allowed_exts			= array('jpg','jpeg','png','gif');
		//if(!in_array($ext[1],$allowed_exts)) imagine the following filename: file.jpg.whatever - $ext[0]="file"; $ext[1]="jpg"; $ext[2]="whatever"; you must always check the last one, not the second one ;)
		if(!in_array($ext,$allowed_exts))
		{
			$std->error($this->lang['av_type_not_allowed']);
		}
		else if(@getimagesize($_FILES['file']['tmp_name']) == false) // if it's an image, then this will return true
		{
			$std->error($this->lang['av_type_not_allowed']);
		}
		
		$uploaddir				= $icebb->settings['board_path'].'uploads/';
		$uploadfilename			= "av-{$icebb->user['id']}.{$ext}";
		$uploadfile				= "{$uploaddir}{$uploadfilename}";
		$file					= "uploads/{$uploadfilename}";
		    
		if(!@move_uploaded_file($_FILES['file']['tmp_name'],$uploadfile))
		{
			$std->error($this->lang['something_went_wrong']);
		}
		else {
			list($w,$h)			= getimagesize($uploadfile);
		
			@chmod($uploadfile,0777);
		
			if($w > 100 || $h > 100)
			{
				$retval				= $std->resize_image($uploadfile,100,100);
				list($w,$h)			= getimagesize($uploadfile); 
			}
		}
		
		$db->query("UPDATE icebb_users SET avatar='{$file}',avtype='upload',avsize='{$w}x{$h}' WHERE id='{$icebb->user['id']}'");
		return $retval;
	}
	
    // No easter egg here - Go away :P
	function fav()
	{
		global $icebb,$std,$config,$db;
		
		if(!$icebb->input['opt'] && !$icebb->input['tid'])
		{
			$db->query("SELECT f.*,t.* FROM icebb_favorites AS f LEFT JOIN icebb_topics AS t ON f.favobjid=t.tid WHERE f.favuser='{$icebb->user['id']}' AND f.favtype='topic'");
			while($f			= $db->fetch_row())
			{
				$f['favtopic']	= $f['objid'];
				$favorite_topics.= $this->html->favorite_topic($f);
			}
			
			if(empty($favorite_topics))
			{
				$favorite_topics= $this->html->favorites_none();
			}
			
			$db->query("SELECT f.*,forum.* FROM icebb_favorites AS f LEFT JOIN icebb_forums AS forum ON f.favobjid=forum.fid WHERE f.favuser='{$icebb->user['id']}' AND f.favtype='forum'");
			while($f			= $db->fetch_row())
			{
				$f['favtopic']	= $f['objid'];
				$favorite_forums.= $this->html->favorite_forum($f);
			}
			
			if(empty($favorite_forums))
			{
				$favorite_forums= $this->html->favorites_none();
			}
			
			$this->output		= $this->html->favoriteView($favorite_topics,$favorite_forums);
		}
		else if($icebb->input['opt'] == 'delete' && $icebb->input['id'] && $icebb->input['type'])
		{
			$db->query("DELETE FROM icebb_favorites WHERE favuser={$icebb->user['id']} AND favtype='{$icebb->input['type']}' AND favobjid={$icebb->input['id']} LIMIT 1");
			
			$std->bouncy_bouncy($this->lang['fav_del'],$_SERVER['HTTP_REFERER']);
		}
	}
	
	function uploads()
	{
		global $icebb,$std,$db;
		
		if(!empty($icebb->input['del']))
		{
			$u				= $db->fetch_result("SELECT * FROM icebb_uploads WHERE uid='{$icebb->input['del']}'");
			$db->query("DELETE FROM icebb_uploads WHERE uid='{$icebb->input['del']}'");
			
			@unlink(str_replace($icebb->settings['board_url'],$icebb->settings['board_path'],$u['upath']));
		
			$std->bouncy_bouncy($this->lang['upload_deleted'],"{$icebb->base_url}act=ucp&func=uploads");
		}
		
		$db->query("SELECT * FROM icebb_uploads WHERE uowner='{$icebb->user['id']}'");
		if($db->get_num_rows() <= 0)
		{
			$uploads		= $this->html->uploads_none();
		}
		else {
			while($u		= $db->fetch_row())
			{
				$u['usize']	= $this->size_translate($u['usize']);
				$uploads   .= $this->html->uploads_row($u);
			}
		}
		
		$this->output		= $this->html->uploads($uploads);
	}
	
	function size_translate($filesize)
	{
	   $array = array(
		   'MB' => 1024 * 1024,
		   'KB' => 1024,
	   );
	   
	   if($filesize <= 1024)
	   {
		   $filesize = $filesize . ' Bytes';
	   }
	   
	   foreach($array AS $name => $size)
	   {
		   if($filesize > $size || $filesize == $size)
		   {
			   $filesize = round((round($filesize / $size * 100) / 100), 2) . ' ' . $name;
		   }
	   }
	   
	   return $filesize;
	}
	
	function emailset()
	{
		global $icebb,$std,$config,$db;
		
		if($icebb->user['email_admin'] == '1')
		{
			$a_send = "<input type='radio' name='admin' value='1' checked='checked' /> {$this->lang['yes']} - <input type='radio' name='admin' value='0' /> {$this->lang['no']}";
		}
		else if($icebb->user['email_admin'] == '0')
		{
			$a_send = "<input type='radio' name='admin' value='1' /> {$this->lang['yes']} - <input type='radio' name='admin' value='0' checked='checked' /> {$this->lang['no']}";
		}
		
		if($icebb->user['email_member'] == '1')
		{
			$m_send = "<input type='radio' name='member' value='1' checked='checked' /> {$this->lang['yes']} - <input type='radio' name='member' value='0' /> {$this->lang['no']}";
		}
		else if($icebb->user['email_member'] == '0')
		{
			$m_send = "<input type='radio' name='member' value='1' /> {$this->lang['yes']} - <input type='radio' name='member' value='0' checked='checked' /> {$this->lang['no']}";
		}
		
		if(!$icebb->input['member'] && !$icebb->input['admin'])
		{
			$this->output		= $this->html->emailset($m_send,$a_send);
		}
		else {
			$db->query("UPDATE icebb_users SET email_admin='{$icebb->input['admin']}' , email_member='{$icebb->input['member']}' WHERE id='{$icebb->user['id']}'");
			
			$std->bouncy_bouncy($this->lang['saved'],$icebb->base_url.'act=ucp&func=emailset');
		}
	}
	
    function settings()
    {
    	global $icebb,$std,$config,$db;
		
		// we seriouslah need a better way to do this
		
		// yeah, we need it like it is in the acc - Daniel
		
		if($icebb->user['view_av'] == '1')
		{
			$view_av = "<input type='radio' name='view_av' value='1' checked='checked' /> {$this->lang['yes']} - <input type='radio' name='view_av' value='no' /> {$this->lang['no']}";
		}
		else {
			$view_av = "<input type='radio' name='view_av' value='1' /> {$this->lang['yes']} - <input type='radio' name='view_av' value='no' checked='checked' /> {$this->lang['no']}";
		}
		
		if($icebb->user['view_sig'] == '1')
		{
			$view_sig		= "<input type='radio' name='view_sig' value='1' checked='checked' /> {$this->lang['yes']} - <input type='radio' name='view_sig' value='no' /> {$this->lang['no']}";
		}
		else {
			$view_sig		= "<input type='radio' name='view_sig' value='1' /> {$this->lang['yes']} - <input type='radio' name='view_sig' value='no' checked='checked' /> {$this->lang['no']}";
		}
		
		if($icebb->user['view_smileys'] == '1')
		{
			$view_smileys	= "<input type='radio' name='view_smileys' value='1' checked='checked' /> {$this->lang['yes']} - <input type='radio' name='view_smileys' value='no' /> {$this->lang['no']}";
		}
		else {
			$view_smileys	= "<input type='radio' name='view_smileys' value='1' /> {$this->lang['yes']} - <input type='radio' name='view_smileys' value='no' checked='checked' /> {$this->lang['no']}";
		}
		
		$editor_style		= !empty($icebb->user['editor_style']) ? $icebb->user['editor_style'] : $icebb->settings['default_editor_style'];
		if($editor_style	== '3')
		{
			$editor['3']	= " selected='selected'";
		}
		else if($editor_style == '2')
		{
			$editor['2']	= " selected='selected'";
		}
		else {
			$editor['1']	= " selected='selected'";
		}
		
		if($icebb->user['quick_edit'] == '1')
		{
			$quick_edit		= "<input type='radio' name='quick_edit' value='1' checked='checked' /> {$this->lang['yes']} - <input type='radio' name='quick_edit' value='no' /> {$this->lang['no']}";
		}
		else {
			$quick_edit		= "<input type='radio' name='quick_edit' value='1' /> {$this->lang['yes']} - <input type='radio' name='quick_edit' value='no' checked='checked' /> {$this->lang['no']}";
		}
		
		if(is_array($icebb->cache['skins']))
		{
			$skins					= $icebb->cache['skins'];
			$skins[0]				= array('skin_id'=>'0','skin_name'=>$this->lang['forum_default']);
			ksort($skins);
			foreach($skins as $skin)
			{
				if($skin['skin_is_hidden']=='1')
				{
					if($icebb->user['g_is_admin']=='1')
					{
						if($skin['skin_id']==$icebb->user['skinid'])
						{
							$skin_options.= "\t<option value='{$skin['skin_id']}' selected='selected'>{$skin['skin_name']} (Hidden)</option>\n";
						}
						else {
							$skin_options.= "\t<option value='{$skin['skin_id']}'>{$skin['skin_name']} (Hidden)</option>\n";
						}
					}
				}
				else {
					if($skin['skin_id']==$icebb->user['skinid'])
					{
						$skin_options.= "\t<option value='{$skin['skin_id']}' selected='selected'>{$skin['skin_name']}</option>\n";
					}
					else {
						$skin_options.= "\t<option value='{$skin['skin_id']}'>{$skin['skin_name']}</option>\n";
					}
				}
			}
		}
		
		if(is_array($icebb->cache['langs']))
		{
			$langs					= $icebb->cache['langs'];
			$langs[0]				= array('lang_short'=>'0','lang_name'=>$this->lang['forum_default']);
			unset($langs['default']);ksort($langs);
			foreach($langs as $lang)
			{
				if($lang['lang_short']==$icebb->user['langid'])
				{
					$lang_options.= "\t<option value='{$lang['lang_short']}' selected='selected'>{$lang['lang_name']}</option>\n";
				}
				else {
					$lang_options.= "\t<option value='{$lang['lang_short']}'>{$lang['lang_name']}</option>\n";
				}
			}
		}
		
		$icebb->hooks->hook('ucp_settings_edit');
		
		if(empty($icebb->input['view_av']) || empty($icebb->input['view_sig']) || empty($icebb->input['view_smileys']))
		{
			$this->output	= $this->html->settings($view_av,$view_sig,$editor,$quick_edit,$skin_options,$lang_options,$view_smileys,$this->plugin_extra);
		}
		else {
			if($icebb->input['view_av'] == 'no')
			{
				$icebb->input['view_av']= '0';
			}
			
			if($icebb->input['view_sig']== 'no')
			{
				$icebb->input['view_sig']= '0';
			}
			
			if($icebb->input['view_smileys']== 'no')
			{
				$icebb->input['view_smileys']= '0';
			}
			
			$update				= array(
				'view_av'		=> $icebb->input['view_av'],
				'view_sig'		=> $icebb->input['view_sig'],
				'view_smileys'	=> $icebb->input['view_smileys'],
				'editor_style'	=> $icebb->input['editor_style'],
				'quick_edit'	=> $icebb->input['quick_edit'],
				'skinid'		=> $icebb->input['skin'],
				'langid'		=> $icebb->input['lang'],
			);
			
			$icebb->hooks->hook('ucp_settings_edit_update');
			
			foreach($update as $k => $v)
			{
				$update_sects[]	= "{$k}='{$v}'";
			}
			
			$db->query("UPDATE icebb_users SET ".implode(',',$update_sects)." WHERE id='{$icebb->user['id']}' LIMIT 1");
			
			$std->bouncy_bouncy($this->lang['settings_updated'],$icebb->base_url.'act=ucp&func=settings');
		}
    }
    
    function dateset()
    {
    	global $icebb,$std,$config,$db;
     
		$tz=array();
			
		$tz['-12'] = '(GMT -12 Hours) Eniwetok, Kwajalein';
		$tz['-11'] = '(GMT -11 Hours) Midway Island, Samoa';
		$tz['-10'] = '(GMT -10 Hours) Hawaii';
		$tz['-9'] = '(GMT -9 Hours) Alaska';
		$tz['-8'] = '(GMT -8 Hours) Pacific Time (US & Canada)';
		$tz['-7'] = '(GMT -7 Hours) Mountain Time (US & Canada)';
		$tz['-6'] = '(GMT -6 Hours) Central Time (US & Canada), Mexico City';
		$tz['-5'] = '(GMT -5 Hours) Eastern Time (US & Canada), Bogota, Lima';
		$tz['-4'] = '(GMT -4 Hours) Atlantic Time (Canada), Caracas, La Paz';
		$tz['-3.5'] = '(GMT -3.5 Hours) Newfoundland';
		$tz['-3'] = '(GMT -3 Hours) Brazil, Buenos Aires, Georgetown';
		$tz['-2'] = '(GMT -2 Hours) Mid-Atlantic';
		$tz['-1'] = '(GMT -1 Hour) Azores, Cape Verde Islands';
		$tz['0'] = '(GMT) London, Lisbon, Casablanca, Monrovia';
		$tz['1'] = '(GMT +1 Hour) Berlin, Brussels, Madrid, Paris';
		$tz['2'] = '(GMT +2 Hours) Kaliningrad, South Africa';
		$tz['3'] = '(GMT +3 Hours) Baghdad, Riyadh, Moscow, Nairobi';
		$tz['3.5'] = '(GMT +3.5 Hours) Tehran';
		$tz['4'] = '(GMT +4 Hours) Abu Dhabi, Muscat, Baku, Tbilisi';
		$tz['4.5'] = '(GMT +4.5 Hours) Kabul';
		$tz['5'] = '(GMT +5 Hours) Ekaterinburg, Islamabad, Karachi, Tashkent';
		$tz['5.5'] = '(GMT +5.5 Hours) Bombay, Calcutta, Madras, New Delhi';
		$tz['5.75'] = '(GMT +5.75 Hours) Kathmandu';
		$tz['6'] = '(GMT +6 Hours) Almaty, Dhaka, Colombo';
		$tz['6.5'] = '(GMT +6.5 Hours)';
		$tz['7'] = '(GMT +7 Hours) Bangkok, Hanoi, Jakarta';
		$tz['8'] = '(GMT +8 Hours) Beijing, Singapore, Hong Kong, Taipei';
		$tz['9'] = '(GMT +9 Hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk';
		$tz['9.5'] = '(GMT +9.5 Hours) Adelaide, Darwin';
		$tz['10'] = '(GMT +10 Hours) Guam, Papua New Guinea';
		$tz['11'] = '(GMT +11 Hours) Magadan, Solomon Islands, New Caledonia';
		$tz['12'] = '(GMT +12 Hours) Auckland, Wellington, Fiji, Marshall Island';
    
		foreach($tz as $offset => $desc)
		{
			if($offset == $icebb->user['gmt'])
			{
				$gmt_select .= "\t<option value='{$offset}' selected='selected'>".htmlentities($desc, ENT_QUOTES)."</option>\n";
			}
			else
			{
				$gmt_select .= "\t<option value='{$offset}'>".htmlentities($desc, ENT_QUOTES)."</option>\n";
			}
		}
		
		if($icebb->user['dst']=='1')
		{
			$dst				= " checked='checked'";
		}
     
		if(!$icebb->input['gmt'] && !$icebb->input['date_format'])
		{
			$date				= gmdate($icebb->user['date_format'],time()+$std->get_offset());
		
			$this->output		= $this->html->dateset($date,$gmt_select,$dst);
		}
		else if(empty($icebb->input['date_format']))
		{
			$std->error($this->lang['empty']);
		}
		else {
			$db->query("UPDATE icebb_users SET gmt='{$icebb->input['gmt']}',dst='".intval($icebb->input['dst'])."',date_format='{$icebb->input['date_format']}' WHERE id='{$icebb->user['id']}' LIMIT 1");
			$std->bouncy_bouncy($this->lang['settings_updated'],$icebb->base_url.'act=ucp&func=dateset');
		}
    }
    
    function iptools()
    {
		global $icebb,$db,$std;
		
		if($icebb->user['g_is_mod']!='1')
		{
			$std->error($this->lang['unauthorized'],1);
		}
		
		if(!empty($icebb->input['ajax_dns_lookup']))
		{
			echo @gethostbyaddr($icebb->input['ajax_dns_lookup']);
			exit();
		}
		
		$ip['ip']				= long2ip(ip2long($icebb->input['ip']));
		$ip['resolved']			= @gethostbyaddr($ip['ip']);
		
		$db->query("SELECT * FROM icebb_users WHERE ip='{$ip['ip']}'");
		while($u						= $db->fetch_row())
		{
			$ip['members_using'][]		= $u;
		}
		
		$db->query("SELECT p.*,u.username as pauthor2 FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id WHERE p.pauthor_ip='{$ip['ip']}'");
		while($p						= $db->fetch_row())
		{
			if(empty($p['pauthor']))
			{
				$p['pauthor']			= $p['pauthor2'];
			}
		
			$ip['posts_using'][]		= $p;
		}
		
		$this->output			= $this->html->iptools($ip);
		
		//@header("Location: http://dnstools.com/?lookup=on&arin=on&target={$icebb->input['ip']}");
    }
    
    function away_system()
    {
    	global $icebb,$std,$db;
    	
    	if(isset($icebb->input['end']))
    	{
    		$db->query("UPDATE icebb_users SET away='0' WHERE id='{$icebb->user['id']}' LIMIT 1");
    		$redirect_to = empty($_SERVER['HTTP_REFERER']) ? $icebb->base_url : $_SERVER['HTTP_REFERER'];
    		$std->bouncy_bouncy($this->lang['away_status_updated'],$redirect_to);
    		exit();
    	}
    	
    	if(isset($icebb->input['away']) && isset($icebb->input['away_reason']))
    	{
    		$away = $icebb->input['away']==1 ? (string) '1' : (string) '0';
    		$db->query("UPDATE icebb_users SET away='{$away}',away_reason='{$icebb->input['away_reason']}' WHERE id='{$icebb->user['id']}' LIMIT 1");
    		$std->bouncy_bouncy($this->lang['away_status_updated'],$icebb->base_url.'act=ucp&func=away_system');
    	}
    	else {
    		$this->output = $this->html->away_system();
    	}
    }
	
	function buddies()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['buddy']))
		{
			$db->query("SELECT id FROM icebb_users WHERE username='{$icebb->input['add']}'");
			if($db->get_num_rows()<=0)
			{
				$std->error($this->lang['user_not_found']);
			}
			
			$u				= $db->fetch_row();
		
			$db->insert('icebb_buddies',array(
				'owner'		=> $icebb->user['id'],
				'type'		=> 1,
				'uid'		=> $u['id'],
			));
			
			$this->recache_buddies();
		}
		else if(!empty($icebb->input['block']))
		{
			$db->query("SELECT id FROM icebb_users WHERE username='{$icebb->input['block_add']}'");
			if($db->get_num_rows()<=0)
			{
				$std->error($this->lang['user_not_found']);
			}
			
			$u				= $db->fetch_row();
		
			$db->insert('icebb_buddies',array(
				'owner'		=> $icebb->user['id'],
				'type'		=> 2,
				'uid'		=> $u['id'],
			));
			
			$this->recache_buddies();
		}
		else if(!empty($icebb->input['del']))
		{
			$db->query("DELETE FROM icebb_buddies WHERE id=".intval($icebb->input['del']));
			$this->recache_buddies();
		}
		
		$buddies			= array(1=>array(),2=>array());
		
		$db->query("SELECT b.*,u.username FROM icebb_buddies AS b LEFT JOIN icebb_users AS u ON b.uid=u.id WHERE b.owner={$icebb->user['id']}");
		while($b			= $db->fetch_row())
		{
			//$b['type']		= $b['type']	= 1;
			
			$buddies[$b['type']][]= $b;
		}
		
		$this->output		= $this->html->buddy_list($buddies[1],$buddies[2]);
	}
	
	function recache_buddies()
	{
		global $icebb,$db,$std;
	
		$db->query("SELECT * FROM icebb_buddies WHERE owner={$icebb->user['id']}");
		while($b			= $db->fetch_row())
		{
			$buddies[]		= $b;
		}
		
		$buddies_cache		= serialize($buddies);
		$buddies_cache		= addslashes($buddies_cache);
		
		$db->query("UPDATE icebb_users SET buddies='{$buddies_cache}' WHERE id={$icebb->user['id']}");
	}
}
?>
