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
// login module
// $Id: login.php 734 2007-02-10 03:49:24Z mutantmonkey0 $
//******************************************************//

class login
{
	function run()
	{
		global $icebb,$db,$config,$std;
	
		$this->lang			= $std->learn_language('login');
		$this->html			= $icebb->skin->load_template('login');

		// load OpenID if it's enabled
		if($icebb->settings['enable_openid'])
		{
			require('includes/classes/openid.inc.php');
			$this->openid			= new icebb_openid();
			
			if(!empty($icebb->input['openid_finish']))
			{
				$this->openid->finish_auth();
				exit();
			}
			else if(!empty($icebb->input['openid_info']))
			{
				$this->openid->request_info('','');
				exit();
			}
			else if(!empty($icebb->input['openid_merge']))
			{
				$this->openid->merge_accounts();
			}
		}

		// account lockdown
		if($icebb->settings['account_lockdown'])
		{
			$lockdown				= intval($icebb->settings['account_lockdown_time'])*60;
			$cut_off_back			= time()-$lockdown;

			$db->query("SELECT * FROM icebb_failed_login_attempt_block WHERE ip='{$icebb->client_ip}'");
			if($db->get_num_rows() > 0)
			{
				$binfo				= $db->fetch_row();
				$this->locked_out($lockdown,$binfo);
			}
			else {
				$db->query("SELECT * FROM icebb_failedlogin_attempts WHERE attempt_ip='{$icebb->client_ip}' AND attempt_where='board' AND attempt_time>{$cut_off_back}");
				if($db->get_num_rows() >= $icebb->settings['account_lockdown_tries'])
				{
					$binfo			= array(
						'ip'		=> $icebb->client_ip,
						'time'		=> time(),
					);
				
					$db->insert('icebb_failed_login_attempt_block',$binfo);
					
					$this->locked_out($lockdown,$binfo);
				}
			}
		}
	
		switch(strtolower($icebb->input['func']))
		{
		    case 'login':
				$this->do_login();
				break;
		    case 'logout':
				$this->logout();
				break;
		    case 'register':
				$this->register();
				break;
		    case 'captcha':
				$this->captcha_img();
				break;
		    case 'forgotpass':
				$this->forgotpass();
				break;
			case 'validate_email':
				$this->validate_email();
				break;
			case 'clear_cookies':
				$this->clear_cookies();
				break;
			case 'resend_validate':
				$this->new_validation_code();
				break;
		    default:
				$this->do_login();
				break;
		}
	}
	
	function do_login()
	{
		global $icebb,$db,$config,$std,$login_func,$input;
	
		$login_msg				= '';
		$from					= $icebb->base_url;
	
		if($icebb->settings['enable_openid'] && !empty($icebb->input['openid_url']))
		{
			// do we need to merge accounts?
			if(!empty($icebb->input['user']) && !empty($icebb->input['pass']))
			{
				list($ret,$errors)			= $this->login_auth_check($icebb->input['user'],$icebb->input['pass'],$errors);
			
				if(is_array($errors) && count($errors) > 0)
				{
					foreach($errors as $error)
					{
						$login_msg		   .= $this->html->error_msg($error);
						$this->output		= $this->html->loginPage($login_msg, $from);
						exit();
					}
				}
				else {
					//$std->error("Account merging not yet implemented, sorry!");exit();
					$this->openid->process_url= $icebb->settings['board_url']."index.php?act=login&openid_merge=1";
				}
			}
			
			$openid_result					= $this->openid->try_auth($icebb->input['openid_url']);
			if(!$openid_result)
			{
				$this->output				= $this->html->loginPage($this->openid->auth_error);
				$icebb->skin->html_insert($this->output);
				$icebb->skin->do_output();
			}
			
			exit();
		}
		else if(isset($icebb->input['from']))
		{
			list($ret,$errors)					= $this->login_auth_check($icebb->input['user'], $icebb->input['pass'],$errors);
			
			if(is_array($errors))
			{
				$db->insert('icebb_failedlogin_attempts',array(
					'attempt_time'				=> time()+(3600/2),
					'attempt_ip'				=> $icebb->client_ip,
					'attempt_userid'			=> $ret['id'],
					'attempt_where'				=> 'board',
				));
				
				foreach($errors as $error)
				{
					$login_msg.= $this->html->error_msg($error);
				}
			}
			else if($ret==true || !empty($ret['username']))
			{
				if(!empty($icebb->input['from']))
				{
					$this->output	= $std->bouncy_bouncy($this->lang['loggin_you_in'], $icebb->input['from']);
				}
				else {
					$this->output	= $std->bouncy_bouncy($this->lang['loggin_you_in'], "index.php");
				}
			}
		}
		
		$this->output		= $this->html->loginPage($login_msg);
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function login_auth_check($user, $pass, $errors=array())
	{
		global $icebb,$db,$std,$login_func;
	
		$ret				= $login_func->authorize($icebb->input['user'], $icebb->input['pass']);
			
		if($ret['user_valid']=='0')
		{
			$errors[]		= $this->lang['invalid_user'];
		}
		
		if($ret['pass_valid']=='0')
		{
			$errors[]		= $this->lang['invalid_pass'];
		}
		
		if($icebb->user['user_group']=='3')
		{
			//echo "{$icebb->settings['board_url']}index.php?act=login&func=validate_email&confirm_code={$confirmation_code}";
		
			$login_func->logout();
		
			$errors[]		= str_replace('<#resend_link#>',$this->html->resend_validate_link($icebb->user['id']),$this->lang['not_validated']);
		}
		
		return array($ret,$errors);
	}
	
	function logout()
	{
		global $icebb,$config,$db,$std,$login_func;
	
		$login_func->logout();

		$std->bouncy_bouncy($this->lang['loggin_you_out'],$_SERVER['HTTP_REFERER']);
	}
	
	function register()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->hooks->hook('register_init');
		
		if($icebb->user['id'] != '0')
		{
			$std->error($this->lang['already_have_an_account']);
		}
		
		if(!$icebb->settings['enable_registration'])
		{
			$std->error($this->lang['registration_disabled']);
		}
		
		if(isset($icebb->input['submit']))
		{
			$errors			= array();
			
			$_POST['user']	= trim($_POST['user']);
		
			if(empty($_POST['user']))
			{
				$errors[]	= $this->lang['user_empty'];
			}
			else {			
				$icebb->input['user']= htmlspecialchars(wash_ebul_tags(wash_key($_POST['user'])));
				
				if(strlen($icebb->input['user'])>64)
				{
					$errors[]	= $this->lang['user_too_long'];
				}
				
				$usersq		= $db->query("SELECT * FROM icebb_users WHERE username='{$icebb->input['user']}'");
				if($db->get_num_rows($usersq)>=1)
				{
					$errors[]= $this->lang['user_taken'];
				}
			}
			
			/*if(strlen($_POST['pass'])>32)
			{
				$errors[]	= $this->lang['pass_too_long'];
			}*/
			
			if(empty($_POST['pass']))
			{
				$errors[]	= $this->lang['pass_empty'];
			}

			if($_POST['pass'] != $_POST['pass2'])
			{
				$errors[]	= $this->lang['pass_not_match'];
			}
			
			if(empty($_POST['email']))
			{
				$errors[]	= $this->lang['email_empty'];
			}
			
			/////////////////////////////////////////////////
			// ban filters
			/////////////////////////////////////////////////
			
			if(is_array($icebb->cache['banfilters']))
			{
				foreach($icebb->cache['banfilters'] as $bf)
				{
					// banned usernames
					if($bf['type']=='username')
					{
						if($icebb->input['user']	== $bf['value'])
						{
							$ubanned				= true;
						}
					}
					// banned e-mails
					else if($bf['type']=='email')
					{
						if(strpos($bf['value'],'*')!==false)
						{
							$bf['value']			= str_replace('*','.*',preg_quote($bf['value'],'`'));
							
							if(preg_match("`{$bf['value']}`",$_POST['email']))
							{
								$email_banned		= true;
							}
						}
						else {
							if($_POST['email']		== $bf['value'])
							{
								$email_banned		= true;
							}
						}
					}
				}
				
				if($ubanned)
				{
					$errors[]						= $this->lang['username_banned'];
				}
				else if($email_banned)
				{
					$errors[]						= $this->lang['email_banned'];
				}
			}
			
			//if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3,4})$",$icebb->input['email']))
			//{
			//	$errors[]	= "invalid email format";
			//}
			
			if($icebb->settings['use_word_verification']=='1')
			{
				$captcha_q	= $db->query("SELECT * FROM icebb_captcha WHERE id='{$icebb->input['captcha_code']}'");
				$captcha_data= $db->fetch_row($captcha_q);
				$captcha_words= @file("langs/{$icebb->lang_id}/captcha.dict");
				
				if($captcha_words[$captcha_data['word_num']]!=$_POST['captcha_word']."\n")
				{
					$errors[]= $this->lang['word_invalid'];
				}
			}
			
			if(count($errors)<=0)
			{
				$salty			= md5(crypt(make_salt(27)));
				$pass_hashed	= md5(md5($icebb->input['pass']).$salty);
			
				$lastuser		= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC LIMIT 1");
			
				$register_group	= $icebb->settings['validate_email'] ? '3' : '2';
			
				$db->insert('icebb_users',array(
											'id'			=> $lastuser['id']+1,
											'username'		=> $icebb->input['user'],
											'password'		=> $pass_hashed,
											'pass_salt'		=> $salty,
											'email'			=> $db->escape_string($_POST['email']),
											'user_group'	=> $register_group,
											'joindate'		=> time(),
											'gmt'			=> $_POST['gmt'],
											'login_key'		=> md5(uniqid(rand(), true)),
										  ));
							
				$cache_result	= $db->fetch_result("SELECT COUNT(*) as count FROM icebb_users");
				$cache_result2	= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
				$icebb->cache['stats']['user_count']		= $cache_result['count'];
				$icebb->cache['stats']['user_newest']		= $cache_result2;
				$std->recache($icebb->cache['stats'],'stats');
				
				$userid										= $lastuser['id']+1;
					
				$std->ra_log("Registered: <a href=\'{$icebb->base_url}profile={$userid}\'>{$icebb->input['user']}</a>",$icebb->input['user'],'register');
										  
				if($icebb->settings['validate_email']=='1')
				{
					$confirmation_code			= substr(md5(uniqid(microtime())),0,10);
				
					$db->query("INSERT INTO icebb_users_validating VALUES('{$confirmation_code}','{$userid}','{$tuser_info['email']}','email','".time()."')");  

					$text						= $this->lang['validate_mail_text'];
					$text						= str_replace('<#url#>',"{$icebb->settings['board_url']}index.php?act=login&func=validate_email&confirm_code={$confirmation_code}",$text);

					$std->send_mail($icebb->input['email'],$this->lang['validate_mail_title'],$text);
				
					$std->bouncy_bouncy($this->lang['registered_validate'],"{$icebb->base_url}act=login");
				}
				else {
					$std->bouncy_bouncy($this->lang['registered'],"{$icebb->base_url}act=login");
				}
			}
			else {
				foreach($errors as $error)
				{
					$reg_msg.= $this->html->error_msg($error);
				}
			}
		}

		if(isset($icebb->input['terms']))
		{
			$tz = array();
			
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
				$gmt_select .= "\t<option value='{$offset}' selected='selected'>{$desc}</option>\n";
			  }
			  else
			  {
				  $gmt_select .= "\t<option value='{$offset}'>{$desc}</option>\n";
			  }
			}
		
			$this->output	= $this->html->registerPage($reg_msg,$gmt_select);
			
			if($icebb->settings['use_word_verification']=='1')
			{
				$captcha_code= $this->captcha_makecode();
			
				$this->output= str_replace('<!--WORD_VERIFICATION-->',$this->html->word_verification($captcha_code),$this->output);
			}
		}
		else if(isset($icebb->input['terms_dis']))
		{
			$std->error($this->lang['terms_not_agree'],1);
		}
		else {
			if(!empty($icebb->settings['board_rules_reg']))
			{
				$rules		= nl2br($icebb->settings['board_rules_reg']);
				$rules		= str_replace("&lt;","<",$rules);
				$rules		= str_replace("&gt;",">",$rules);
			}
			else {
				$rules		= nl2br($icebb->settings['board_rules']);
				$rules		= str_replace("&lt;","<",$rules);
				$rules		= str_replace("&gt;",">",$rules);
			}
		
			$this->output	= $this->html->registerPage_terms($rules);
		}
	
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function forgotpass()
	{
		global $icebb,$db,$config,$std;
		
		if(isset($icebb->input['submit']))
		{
			$tuser_infoq		= $db->query("SELECT * FROM icebb_users WHERE email='{$icebb->input['email']}'");
			if($db->get_num_rows($tuser_infoq)<=0)
			{
				$std->error($this->lang['email_invalid'],1);
			}
			
			$tuser_info		= $db->fetch_row($tuser_infoq);
			
			$confirmation_code= substr(md5(uniqid(microtime())),0,10);
			
			$db->query("INSERT INTO icebb_users_validating VALUES('{$confirmation_code}','{$tuser_info['id']}','{$tuser_info['email']}','forgot_pass','".time()."')");
			
			$text			= $this->lang['forgotten_password_text'];
			$text			= str_replace('<#url#>',"{$icebb->settings['board_url']}index.php?act=login&func=forgotpass&confirm_code={$confirmation_code}",$text);
			
			$std->send_mail($tuser_info['email'],$this->lang['forgotten_password_title'],$text);
		
			$std->bouncy_bouncy($this->lang['pass_sent'],$_SERVER['HTTP_REFERER']);
			exit();
		}
		else if(isset($icebb->input['confirm_code']))
		{
			$confirm_q		= $db->query("SELECT * FROM icebb_users_validating WHERE id='{$icebb->input['confirm_code']}'");
			$confirm		= $db->fetch_row($confirm_q);
			
			if($db->get_num_rows($confirm_q)<=0)
			{
				$std->error($this->lang['confirm_code_invalid'],1);
			}
			else {
				$db->query("DELETE FROM icebb_users_validating WHERE id='{$confirm['id']}'");
			
				$newpass		= $this->randPass();
				$salty			= md5(crypt(make_salt(27)));
				$pass_hashed	= md5(md5($newpass).$salty);
		
				$db->query("UPDATE icebb_users SET password='{$pass_hashed}',pass_salt='{$salty}' WHERE id='{$confirm['user']}'");
		
				$this->output		= $this->html->loginPage("<b>{$this->lang['new_password_is']} {$newpass}<br />{$this->lang['new_password_is2']}</b><br /><br />");
			
				$icebb->skin->html_insert($this->output);
				$icebb->skin->do_output();
			
				exit();
			}
		}

		$this->output	       .= $this->html->forgotPassPage($captcha_code);
			
		if($icebb->settings['use_word_verification']=='1')
		{
			$captcha_code		= $this->captcha_makecode();
		
			$this->output		= str_replace('<!--WORD_VERIFICATION-->',$this->html->word_verification($captcha_code),$this->output);
		}

		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function captcha_makecode()
	{
		global $std;

		return $std->captcha_makecode();
	}
	
	function captcha_img()
	{
		global $icebb,$config,$db;
	
		include('make_image.php');
	}
	
	function randPass()
	{
	  $salt = "abchefghjkmnpqrstuvwxyz0123456789";
	  srand((double)microtime()*1000000);
	      $i = 0;
	      while ($i <= 7) {
		    $num = rand() % 33;
		    $tmp = substr($salt, $num, 1);
		    $pass = $pass . $tmp;
		    $i++;
	      } 
		
		return $pass;
	}
	
	function validate_email()
	{
		global $icebb,$db,$config,$std;
	
		$db->query("SELECT * FROM icebb_users_validating WHERE id='{$icebb->input['confirm_code']}'");
		$confirm		= $db->fetch_row($confirm_q);
		
		if($db->get_num_rows()<=0)
		{
			$std->error($this->lang['confirm_code_invalid']);
		}
		else {
			$userq			= $db->query("SELECT * FROM icebb_users WHERE id='{$confirm['user']}' LIMIT 1");
			$user			= $db->fetch_row($userq);
		
			$db->query("UPDATE icebb_users SET user_group='2' WHERE id='{$user['id']}'");
		
			if($db->get_num_rows($userq)<=0)
			{
				$std->error($this->lang['cannot_validate']);
			}
			else {
				$db->query("DELETE FROM icebb_users_validating WHERE id='{$confirm['id']}'");
		
				$std->bouncy_bouncy($this->lang['email_validated'],"{$icebb->base_url}act=login");
			}
		
			//exit();
		}
	}
	
	function new_validation_code()
	{
		global $icebb,$db,$std;
	
		$icebb->user				= $db->fetch_result("SELECT * FROM icebb_users WHERE id='{$icebb->input['id']}'");
	
		$confirmation_code			= substr(md5(uniqid(microtime())),0,10);
	
		$db->query("INSERT INTO icebb_users_validating VALUES('{$confirmation_code}','{$icebb->user['id']}','{$icebb->user['email']}','valide_email','".time()."')");  

		$text						= $this->lang['validate_mail_text'];
		$text						= str_replace('<#url#>',"{$icebb->settings['board_url']}index.php?act=login&func=validate_email&confirm_code={$confirmation_code}",$text);

		$std->send_mail($icebb->user['email'],$this->lang['validate_mail_title'],$text);

		$std->bouncy_bouncy($this->lang['validation_resent'],"{$icebb->base_url}act=login");
	}
	
	function locked_out($lockdown,$binfo)
	{
		global $icebb,$db,$std;
		
		$unlock_time					= intval($binfo['time'])+$lockdown;
		
		if($unlock_time <= time())
		{
			$db->query("DELETE FROM icebb_failed_login_attempt_block WHERE id={$binfo['id']}");
			return;
		}
		
		$icebb->skin->user_bar_disabled	= 1;
		
		$mins_left						= ceil((intval($unlock_time)-time())/60);
		$std->error(sprintf($icebb->lang['too_many_failed'],$mins_left));
		exit();
	}
	
	function clear_cookies()
	{
		global $icebb,$db,$config,$std;
	
		$std->bakeCookie('sessid','','Old Spanish Bread Oven');
		$std->bakeCookie('user','','Old Spanish Bread Oven');
		$std->bakeCookie('pass','','Old Spanish Bread Oven');
		
		$std->bouncy_bouncy("Cookies cleared",$_SERVER['HTTP_REFERER']);
	}
}
?>
