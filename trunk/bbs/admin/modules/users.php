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
// user admin module
// $Id: users.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class users
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title	= "Manage Users";
		
		switch($icebb->input['func'])
		{
			case 'search':
				$this->show_search_results();
				break;
			case 'new':
				$this->new_user();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'chgname':
				$this->chg_name();
				break;
			case 'prune':
				// not done
				break;
			case 'suspend':
				$this->suspend();
				break;
			case 'iptools':
				$this->iptools();
				break;
			default:
				$this->show_search_form();
				break;
		}
		
		$icebb->admin->html			= $this->html->header().$icebb->admin->html.$this->html->footer();

		$icebb->admin->output();
	}
	
	function show_search_form()
	{
		global $icebb,$config,$db,$std;

		$icebb->admin->html					= $icebb->admin_skin->start_form('admin.php',array('act'=>'users','func'=>'search'));
		
		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');
		
		$icebb->admin->html			       .= $icebb->admin_skin->start_table("Search for users"); 

		$options							= array(array('sw','Starts with'),array('ew','Ends with'),array('co','Contains'),array('is','Is'));
		$icebb->admin->html				   .= $icebb->admin_skin->table_row(array("<strong>Username</strong>",$icebb->admin_skin->form_dropdown('search_how',$options).$icebb->admin_skin->form_input('username')));

		$icebb->admin->html				   .= $icebb->admin_skin->end_form("Search");

		$icebb->admin->html				   .= $icebb->admin_skin->end_table();
	}
	
	function show_search_results()
	{
		global $icebb,$config,$db,$std;

		if($icebb->input['search_how']=='sw')
		{
			$where							= " LIKE '{$icebb->input['username']}%'";
			$wheretype_english				= "%d users have usernames that start with %s";
		}
		else if($icebb->input['search_how']=='ew')
		{
			$where							= " LIKE '%{$icebb->input['username']}'";
			$wheretype_english				= "%d users have usernames that end with %s";
		}
		else if($icebb->input['search_how']=='co')
		{
			$where							= " LIKE '%{$icebb->input['username']}%'";
			$wheretype_english				= "%d users have usernames that contain %s";
		}
		else if($icebb->input['search_how']=='is')
		{
			$where							= "='{$icebb->input['username']}'";
			$wheretype_english				= "%d users have usernames that are %s";
		}

		$db->query("SELECT COUNT(*) as count FROM icebb_users WHERE username{$where}");
		$count								= $db->fetch_row();

		$db->query("SELECT u.*,g.g_title FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE username{$where}");
		while($u							= $db->fetch_row())
		{
			$thisrow[]						= <<<EOF
<div style='text-align:center'>
	<a href='{$icebb->base_url}act=users&func=edit&uid={$u['id']}&search_how={$icebb->input['search_how']}&searchq={$icebb->input['username']}'><img src='{$u['avatar']}' border='0' alt='' width='64' height='64' /><br />
	{$u['username']}</a><br />
	<a href='{$icebb->base_url}act=users&func=chgname&uid={$u['id']}&search_how={$icebb->input['search_how']}&searchq={$icebb->input['username']}'>Change username</a><br />
	<a href='{$icebb->base_url}act=users&func=suspend&uid={$u['id']}&search_how={$icebb->input['search_how']}&searchq={$icebb->input['username']}'>Suspend user</a><br />
	<span style='font-size:70%'>{$u['title']}</span>
</div>
EOF;
			$ucount++;

			if(!($ucount % 4) || $ucount==$count['count'])
			{
				$result_html			   .= $icebb->admin_skin->table_row($thisrow,'col1'," valign='top'");
				$thisrow					= array();
			}
		}
		
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		
		$icebb->admin->html			       .= $icebb->admin_skin->start_table(sprintf($wheretype_english,$count['count'],$icebb->input['username'])); 

		$icebb->admin->html				   .= $result_html;

		$icebb->admin->html				   .= $icebb->admin_skin->end_table();
	}

	function edit()
	{
		global $icebb,$config,$db,$std;
		
		$root = explode(',',$config['root_users']);
		
		$this_user = $db->fetch_result("SELECT * FROM icebb_users WHERE username='{$icebb->adsess['user']}' LIMIT 1");
		$edit_user = $db->fetch_result("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}' LIMIT 1");
		
		if(in_array($icebb->input['uid'],$root) && $this_user['id']!=$edit_user['id'])
		{
			$icebb->admin->error("You are not allowed to edit this user");
			$std->log('admin',"Tried to edit protected user: {$edit_user}",$icebb->adsess['user']);
		}
		
		if(isset($icebb->input['submit']))
		{
			$db->query("UPDATE icebb_users SET away='{$icebb->input['away']}',away_reason='{$icebb->input['away_reason']}',user_group='{$icebb->input['user_group']}',email_admin='{$icebb->input['email_admin']}',email_member='{$icebb->input['email_member']}',date_format='{$icebb->input['date_format']}',gmt='{$icebb->input['gmt']}',view_av='{$icebb->input['view_av']}',view_sig='{$icebb->input['view_sig']}',notepad='{$icebb->input['notepad']}',url='{$icebb->input['url']}',msn='{$icebb->input['msn']}',yahoo='{$icebb->input['yahoo']}',aim='{$icebb->input['aim']}',location='{$icebb->input['location']}',jabber='{$icebb->input['jabbber']}',icq='{$icebb->input['icq']}',title='{$icebb->input['title']}',avatar='{$icebb->input['avatar']}',email='{$icebb->input['email']}',posts='{$icebb->input['posts']}',siggie='{$icebb->input['siggie']}' WHERE id='{$icebb->input['uid']}' LIMIT 1");
			
			$u						= $db->fetch_result("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}'");
			
			$std->log('admin',"Edited user: {$u['username']}",$icebb->adsess['user']);
			
			$icebb->admin->redirect("User edited",$icebb->base_url."act=users&func=search&search_how={$icebb->input['search_how']}&username={$icebb->input['searchq']}");
		}
		$db->query("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}' LIMIT 1");
		$u							= $db->fetch_row();

		$db->query("SELECT * FROM icebb_groups");
		while($g					= $db->fetch_row())
		{
			$ugroups[]				= array($g['gid'],$g['g_title']);
		}
		
		$tz = array();
		
		$tz['-12'] = '(GMT -12 Hours) Eniwetok, Kwajalein';
		$tz['-11'] = '(GMT -11 Hours) Midway Island, Samoa';
		$tz['-10'] = '(GMT -10 Hours) Hawaii';
		$tz['-9'] = '(GMT -9 Hours) Alaska';
		$tz['-8'] = '(GMT -8 Hours) Pacific Time (US & Canada)';
		$tz['-7'] = '(GMT -7 Hours) Mountain Time (US & Canada)';
		$tz['-6'] = '(GMT -6 Hours) Central Time (US & Canada), Mexico City';
		$tz['-5'] = 'GMT -5 Hours) Eastern Time (US & Canada), Bogota, Lima, Quito';
		$tz['-4'] = '(GMT -4 Hours) Atlantic Time (Canada), Caracas, La Paz';
		$tz['-3.5'] = '(GMT -3.5 Hours) Newfoundland';
		$tz['-3'] = '(GMT -3 Hours) Brazil, Buenos Aires, Georgetown';
		$tz['-2'] = '(GMT -2 Hours) Mid-Atlantic</option><option value="-1">(GMT -1 Hour) Azores, Cape Verde Islands';
		$tz['-1'] = '(GMT -1 Hour) Azores, Cape Verde Islands';
		$tz['0'] = '(GMT) Western Europe Time, London, Lisbon, Casablanca, Monrovia';
		$tz['1'] = '(GMT +1 Hour) CET(Central Europe Time), Berlin, Brussels, Madrid, Paris';
		$tz['2'] = '(GMT +2 Hours) EET(Eastern Europe Time), Kaliningrad, South Africa';
		$tz['3'] = '(GMT +3 Hours) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg, Nairobi';
		$tz['3.5'] = '(GMT +3.5 Hours) Tehran';
		$tz['4'] = '(GMT +4 Hours) Abu Dhabi, Muscat, Baku, Tbilisi';
		$tz['4.5'] = '(GMT +4.5 Hours) Kabul';
		$tz['5'] = '(GMT +5 Hours) Ekaterinburg, Islamabad, Karachi, Tashkent';
		$tz['5.5'] = '(GMT +5.5 Hours) Bombay, Calcutta, Madras, New Delhi';
		$tz['5.75'] = '(GMT +5.75 Hours) Kathmandu';
		$tz['6'] = '(GMT +6 Hours) Almaty, Dhaka, Colombo';
		$tz['6.5'] = '(GMT +6.5 Hours)';
		$tz['7'] = '(GMT +7 Hours) Bangkok, Hanoi, Jakarta';
		$tz['8'] = '(GMT +8 Hours) Beijing, Perth, Singapore, Hong Kong, Urumqi, Taipei';
		$tz['9'] = '(GMT +9 Hours) Tokyo, Seoul, Osaka, Sapporo, Yakutsk';
		$tz['9.5'] = '(GMT +9.5 Hours) Adelaide, Darwin';
		$tz['10'] = '(GMT +10 Hours) EAST(East Australian Standard), Guam, Papua New Guinea';
		$tz['11'] = '(GMT +11 Hours) Magadan, Solomon Islands, New Caledonia';
		$tz['12'] = '(GMT +12 Hours) Auckland, Wellington, Fiji, Kamchatka, Marshall Island';
		$tz['13'] = '(GMT +13 Hours) Nuku\'alofa';
		
		foreach($tz as $k => $v)
		{
			$timezones[]			= array($k,$v);
		}
     
		$icebb->admin->page_title	= "Edit User";

		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('s'=>$icebb->adsess['sessid'],'act'=>'users','func'=>'edit','uid'=>$icebb->input['uid'],'search_how'=>$icebb->input['search_how'],'searchq'=>$icebb->input['searchq'],'submit'=>'1'),'post'," name='adminfrm'");

		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Edit {$u['username']}");
		
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>User title</b>",$icebb->admin_skin->form_input('title',$u['title'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Avatar</b>",$icebb->admin_skin->form_input('avatar',$u['avatar'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Group</b>",$icebb->admin_skin->form_dropdown('user_group',$ugroups,$u['user_group'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Posts</b>",$icebb->admin_skin->form_input('posts',$u['posts'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>E-mail address</b>",$icebb->admin_skin->form_input('email',$u['email'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Signature</b>",$icebb->admin_skin->form_textarea('siggie',$u['siggie'],'5','50')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>MSN</b>",$icebb->admin_skin->form_input('msn',$u['msn'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>ICQ</b>",$icebb->admin_skin->form_input('icq',$u['icq'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>AIM</b>",$icebb->admin_skin->form_input('aim',$u['aim'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>YIM</b>",$icebb->admin_skin->form_input('yahoo',$u['yahoo'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Jabber</b>",$icebb->admin_skin->form_input('jabber',$u['jabber'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Location</b>",$icebb->admin_skin->form_input('location',$u['location'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>URL</b>",$icebb->admin_skin->form_input('url',$u['url'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>May admins mail?</b>",$icebb->admin_skin->form_yes_no('email_admin',$u['email_admin'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>May members mail?</b>",$icebb->admin_skin->form_yes_no('email_member',$u['email_member'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Date Format</b>",$icebb->admin_skin->form_input('date_format',$u['date_format'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Time Zone</b>",$icebb->admin_skin->form_dropdown('gmt',$timezones,$u['gmt'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View avatars?</b>",$icebb->admin_skin->form_yes_no('view_av',$u['view_av'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View signatures?</b>",$icebb->admin_skin->form_yes_no('view_sig',$u['view_sig'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Notepad</b>",$icebb->admin_skin->form_textarea('notepad',$u['notepad'],'5','50')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Away?</b>",$icebb->admin_skin->form_yes_no('away',$u['away'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Away reason</b>",$icebb->admin_skin->form_textarea('away_reason',$u['away_reason'],'5','50')));
     
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>IP Address</strong>","<a href='{$icebb->base_url}act=users&amp;func=iptools&amp;ipaddr={$u['ip']}' title=\"".@gethostbyaddr($u['ip'])."\">{$u['ip']}</a>"));
     
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Save Changes");
		
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}

	function new_user()
	{
		global $icebb,$config,$db,$std;
     
		if(isset($icebb->input['submit']))
		{
			$errors			= array();
		
			if(!isset($icebb->input['username']))
			{
				$errors[]	= "No username entered";
			}
			else {
				$icebb->input['user']= htmlspecialchars(wash_ebul_tags($icebb->input['username']));
				
				$usersq		= $db->query("SELECT * FROM icebb_users WHERE username='{$icebb->input['username']}'");
				if($db->get_num_rows($usersq)>=1)
				{
					$errors[]= "Username taken";
				}
			}
			
			if(!isset($icebb->input['password']))
			{
				$errors[]	= "Password not entered";
			}

			if(!isset($icebb->input['email']))
			{
				$errors[]	= "Email not entered";
			}
			
			if(count($errors)<=0)
			{
				$salty			= md5(crypt(make_salt(27)));
				$pass_hashed	= md5(md5($icebb->input['password']).$salty);
			
				$lastuser		= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC LIMIT 1");
			
				$db->insert('icebb_users',array(
											'id'		=> $lastuser['id']+1,
											'username'	=> $icebb->input['username'],
											'password'	=> $pass_hashed,
											'pass_salt'	=> $salty,
											'email'		=> $icebb->input['email'],
											'user_group'=> $icebb->input['group'],
											'joindate'	=> time(),
                                            'notepad'	=> 'Account made by an admin',
                                            'login_key'	=> md5(uniqid(rand(), true)),
										  ));
							
				$cache_result	= $db->fetch_result("SELECT COUNT(*) as count FROM icebb_users");
				$cache_result2	= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
				$icebb->cache['stats']['user_count']		= $cache_result['count'];
				$icebb->cache['stats']['user_newest']		= $cache_result2;
				$std->recache($icebb->cache['stats'],'stats');
					
			
				$std->log('admin',"Created user: {$icebb->input['username']}",$icebb->adsess['user']);
										  
               $icebb->admin->redirect("User added","{$icebb->base_url}act=users&func=new");
			}
          else
          {
          	foreach($errors as $errors)
               {
               	$err_msg .= $errors."<br />";
               }
          	$icebb->admin->error($errors);
          }
		}
		
		$db->query("SELECT * FROM icebb_groups WHERE gid!=3 AND gid!=4 AND gid!=5");
		while($g					= $db->fetch_row())
		{
			$ugroups[]				= array($g['gid'],$g['g_title']);
		}
     
		$icebb->admin->page_title	= "Add New User";

		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('s'=>$icebb->adsess['sessid'],'act'=>'users','func'=>'new','submit'=>'1'),'post'," name='adminfrm'");

		$icebb->admin->html		   .= $icebb->admin_skin->start_table("New user information");
		
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Username</b>",$icebb->admin_skin->form_input('username','')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Password</b>",$icebb->admin_skin->form_password('password','')));
   		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Email</b>",$icebb->admin_skin->form_input('email','')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Group</b>",$icebb->admin_skin->form_dropdown('group',$ugroups)));
     
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Register User");
		
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
     
    }
    
    function chg_name()
    {
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['nusername']))
		{
			$u						= $db->fetch_result("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}'");
			$ou						= $u['username'];
			$nu						= $icebb->input['nusername'];
		
			$db->query("UPDATE icebb_users SET username='{$nu}' WHERE id='{$icebb->input['uid']}'");
			$db->query("UPDATE icebb_posts SET pauthor='{$nu}' WHERE pauthor='{$ou}'");
			$db->query("UPDATE icebb_topics SET starter='{$nu}' WHERE starter='{$ou}'");
			$db->query("UPDATE icebb_topics SET lastpost_author='{$nu}' WHERE lastpost_author='{$ou}'");
			$db->query("UPDATE icebb_ra_logs SET user='{$nu}' WHERE user='{$ou}'");
			$db->query("UPDATE icebb_pm_posts SET pauthor='{$nu}' WHERE pauthor='{$ou}'");
			$db->query("UPDATE icebb_pm_topics SET starter='{$nu}' WHERE starter='{$ou}'");
			$db->query("UPDATE icebb_forums SET lastpost_author='{$nu}' WHERE lastpost_author='{$ou}'");
			$db->query("UPDATE icebb_logs SET user='{$nu}' WHERE user='{$ou}'");
			$db->query("UPDATE icebb_moderators SET muser='{$nu}' WHERE muser='{$ou}'");
			$db->query("UPDATE icebb_poll_voters SET voteruser='{$nu}' WHERE voteruser='{$ou}'");
			$db->query("UPDATE icebb_session_data SET username='{$nu}' WHERE username='{$ou}'");
			$db->query("UPDATE icebb_users_validating SET user='{$nu}' WHERE user='{$ou}'");
		
			$icebb->admin->redirect("Username changed",$icebb->base_url."act=users&func=search&search_how={$icebb->input['search_how']}&username={$icebb->input['nusername']}");
		}
		
		$icebb->admin->page_title	= "Change Username";

		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'users','func'=>'chgname','uid'=>$icebb->input['uid'],'search_how'=>$icebb->input['search_how'],'submit'=>'1'),'post'," name='adminfrm'");
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Change username");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>New Username</strong>",$icebb->admin_skin->form_input('nusername','')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Change Username");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
    }
    
    function suspend()
    {
    	global $icebb,$std,$db,$config;
     
		$db->query("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}' LIMIT 1");
		$u = $db->fetch_row();	
    	
    	$time = array();

    	$time['hour'] = 'hour(s)';
		$time['day'] == 'day(s)';
		$time['month'] == 'month(s)';
		$time['year'] == 'year(s)';
     
		foreach($time as $a => $b)
		{
			$times[]			= array($a,$b);
		}
     
     if(!isset($icebb->input['submit']))
     {
     
     	$icebb->admin->page_title	= "Suspend User";
	
			$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));
	
			$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('s'=>$icebb->adsess['sessid'],'act'=>'users','func'=>'suspend','uid'=>$icebb->input['uid'],'search_how'=>$icebb->input['search_how'],'searchq'=>$icebb->input['searchq'],'submit'=>'1'),'post'," name='adminfrm'");
	
			$icebb->admin->html		   .= $icebb->admin_skin->start_table("Suspend {$u['username']}");
			
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Amount of time</b>",$icebb->admin_skin->form_input('time_value','')." ".$icebb->admin_skin->form_dropdown('time_name',$times)));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Reason</b>",$icebb->admin_skin->form_textarea('reason','')));
     
			$icebb->admin->html		   .= $icebb->admin_skin->end_form("Suspend");
		
			$icebb->admin->html		   .= $icebb->admin_skin->end_table();
     }
     else
     {
     	if(!$icebb->input['time_value'] || !$icebb->input['reason'] || !$icebb->input['time_name'])
          {
          	$std->error('Please fill out <em>all</em> fields!');
          }
          
			$suspend_time = strtotime("+".$icebb->input['time_value']." ".$icebb->input['time_name']);
			
			$date_normal = date('l dS F Y - H:i:s',$suspend_time);
			
			$headers .= "From: {$icebb->config['admin_email']}\r\n";
			$headers .= "Reply-To: {$icebb->config['admin_email']}\r\n";          
			$headers  = "MIME-Version: 1.0\r\n";
			
			$suspend_body = <<<EOF
Hi {$u['username']},

You have recieved this mail to inform you that you have been suspended untill {$date_normal}.
You do not have to do anything to make the suspension go away, this is done automatically.

Reason for suspension:
{$icebb->input['reason']}
EOF;
          
			mail($u['email'],"Suspension Notice",$suspend_body,$headers);
                    
			$db->query("UPDATE icebb_users SET temp_ban='{$suspend_time}' WHERE id='{$u['id']}' LIMIT 1");
          
			$std->log('admin',"Suspended user: {$icebb->input['username']} until {$date_normal}",$icebb->adsess['user']);
          
			$icebb->admin->redirect("User suspended",$icebb->base_url."act=users&func=search&search_how={$icebb->input['search_how']}&username={$icebb->input['searchq']}");
     }
    
    }
    
    function iptools()
    {
		global $icebb,$db,$config,$std;
		
		$icebb->admin->page_title			= "IP Tools";
		
		if(isset($icebb->input['ipaddr']))
		{
			$ip								= $icebb->input['ipaddr'];
		
			$icebb->admin->html			   .= $icebb->admin_skin->start_table($icebb->input['ipaddr']);
			
			$icebb->admin->html			   .= $icebb->admin_skin->table_row("IP address resolves to <em>".gethostbyaddr($ip)."</em>",'darkrow');
			
			$icebb->admin->html			   .= $icebb->admin_skin->table_row("Members using this IP",'darkrow');
			$db->query("SELECT * FROM icebb_users WHERE ip='{$ip}'");
			while($u						= $db->fetch_row())
			{
				$icebb->admin->html		   .= $icebb->admin_skin->table_row("&nbsp;&nbsp;<a href='{$icebb->base_url}act=users&amp;func=edit&amp;uid={$u['id']}'>{$u['username']}</a>");
			}
			
			$icebb->admin->html			   .= $icebb->admin_skin->table_row("Posts by members using this IP",'darkrow');
			$db->query("SELECT * FROM icebb_posts WHERE pauthor_ip='{$ip}'");
			while($p						= $db->fetch_row())
			{
				$icebb->admin->html		   .= $icebb->admin_skin->table_row("&nbsp;&nbsp;<a href='index.php?topic={$p['ptopicid']}&amp;pid={$p['pid']}' target='_blank'>Post #{$p['pid']}</a> by {$p['pauthor']}");
			}
			
			$icebb->admin->html			   .= $icebb->admin_skin->end_table();
		}
		else {
			$icebb->admin->html				= $icebb->admin_skin->start_form('admin.php',array('act'=>'users','func'=>'iptools'));
			
			$icebb->admin_skin->table_titles[]= array("{none}",'40%');
			$icebb->admin_skin->table_titles[]= array("{none}",'60%');
			
			$icebb->admin->html		       .= $icebb->admin_skin->start_table("Tell me everything you know about an IP"); 

			$icebb->admin->html			   .= $icebb->admin_skin->table_row(array("<strong>IP Address</strong>",$icebb->admin_skin->form_input('ipaddr')));
	
			$icebb->admin->html			   .= $icebb->admin_skin->end_form("Tell me!");
	
			$icebb->admin->html			   .= $icebb->admin_skin->end_table();
		}
    }
}
?>
