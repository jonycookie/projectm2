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
// login screen admin module
// $Id: login_screen.php 1353 2006-04-13 22:18:16Z mutantmonkey $
//******************************************************//

class login_screen
{
	function run()
	{
		global $icebb,$config,$db,$std,$login_func;
		
		$this->lang								= $icebb->admin->learn_language('login_screen');
		$this->html								= $icebb->admin_skin->load_template('login_screen');
		
		// load OpenID if it's enabled
		if($icebb->settings['enable_openid'])
		{
			require('../includes/classes/openid.inc.php');
			$this->openid						= new icebb_openid();
			$this->openid->process_url			= $icebb->settings['board_url']."admin/index.php?act=login_screen&openid_finish=1";
			
			if(!empty($icebb->input['openid_finish']))
			{
				$this->openid->admin_finish_auth($this);
				exit();
			}
		}
		
		$icebb->admin->page_title				= "";
		
		/* Replaced with a new configurable system
		$failedattemptq							= $db->query("SELECT * FROM icebb_failedlogin_attempts WHERE attempt_ip='{$icebb->client_ip}' AND attempt_time>".time());
		if($db->get_num_rows($failedattemptq)>=5)
		{
			$icebb->admin->error($this->lang['too_many_failed']);
		}*/
		
		if(!isset($icebb->input['username']) && !isset($icebb->input['password']))
		{
			$this->login_form($this->lang['no_sess_found']);
		}
		else {
			if($icebb->settings['enable_openid'] && !empty($icebb->input['openid_url']))
			{
				$openid_result					= $this->openid->try_auth($icebb->input['openid_url']);
				if(!$openid_result)
				{
					$this->login_form($this->openid->auth_error);
				}
			
				exit();
			}
		
			$userq			= $db->query("SELECT u.*,g.g_is_admin FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE u.username='{$icebb->input['username']}' LIMIT 1");
			$udata			= $db->fetch_row($userq);
			
			if($db->get_num_rows($userq)<=0)
			{
				$this->login_form($this->lang['invalid_user_or_pass']);
			}
			
			if(!empty($icebb->input['password_md5']))
			{
				$password	= $icebb->input['password_md5'];
			}
			else {
				$password	= $icebb->input['password'];
				$password	= md5($password);
			}
			
			if(md5($password.$udata['pass_salt'])==$udata['password'])
			{
				$this->finish_login($udata);
			}
			else {
				// log the attempt
				$std->log('failed-login-acc',"Failed admin control center login attempt by {$icebb->client_ip} using the username {$icebb->input['username']}",$icebb->input['username']);
				
				// store it like vB's strike system
				$db->insert('icebb_failedlogin_attempts',array(
					'attempt_time'				=> time()+(3600/2),
					'attempt_ip'				=> $icebb->client_ip,
					'attempt_userid'			=> $udata['id'],
					'attempt_where'				=> 'acc',
				));
				
				$this->login_form($this->lang['invalid_user_or_pass']);
			}
		}
		
		$icebb->admin->output();
	}
	
	function login_form($msg='')
	{
		global $icebb,$db,$config,$std,$login_func;
	
		$icebb->user				= $login_func->load_session();
		
		$icebb->user['username'] = ($icebb->user['username']=='Guest') ? '' : $icebb->user['username'] ;
		
		/*$icebb->admin->html		   .= $icebb->admin_skin->start_form('index.php',array('password_md5'=>'',),'post'," onsubmit='this.password_md5.value=hex_md5(this.password.value);this.password.value=\"\"'");
		$icebb->admin->html		   .= $icebb->admin_skin->start_table($msg);
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("Username:",$icebb->admin_skin->form_input('username',$icebb->user['username'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("Password:",$icebb->admin_skin->form_password('password')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Login");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();*/
		$icebb->admin->html			= $this->html->display($msg,$icebb->user['username']);
	}
	
	function finish_login($udata)
	{
		global $icebb, $db, $std, $login_func;
		
		if($udata['g_is_admin']=='1')
		{					
			$sessid							= md5(microtime());
			$ip								= $icebb->client_ip;
			$user_agent						= $std->clean_string($_SERVER['HTTP_USER_AGENT']);
		
			$db->insert('icebb_adsess', array(
							'asid'			=> $sessid,
							'user'			=> $udata['username'],
							'ip'			=> $ip,
							'logintime'		=> time(),
							'location'		=> 'home',
							'last_action'	=> time(),
						));
			
			$lstring		= explode('&amp;',$icebb->input['return']);
			$lstring[]		= "s={$sessid}"; 
			$lestring		= implode('&',$lstring);
			
			//$icebb->admin->redirect("Logging you in...","index.php?s={$sessid}&loadframes=1");
			//$std->redirect("index.php?s={$sessid}&loadframes=1");
			$std->redirect("index.php?{$lestring}");
		}
		else {
			$std->log('acc-no-access',"{$icebb->client_ip} tried to log in as {$icebb->input['username']}, but {$icebb->input['username']} does not have admin access",$icebb->input['username']);
			$this->login_form($this->lang['no_admin_access']);
		}
	}
}
?>
