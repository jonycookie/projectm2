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
// login functions class
// $Id: login_func.php 825 2007-05-18 09:06:08Z daniel159 $
//******************************************************//

class login_func
{
	var $session_id;

	/**
	 * Authorize a user (used when logging in)
	 *
	 * @param		string		Username
	 * @param		string		Password
	 * @param		boolean		I don't know, I'll have to check
	 */
	function authorize($user_entered,$pass_entered,$nodo=0)
	{
		global $icebb,$db,$config,$std;
	
		$userq			= $db->query("SELECT * FROM icebb_users WHERE username='{$user_entered}' LIMIT 1");
		$udata			= $db->fetch_row($userq);
	
		if($db->get_num_rows($userq)<=0)
		{
			$ret		= $icebb->hooks->hook('login_authorize',$user_entered,$pass_entered);
		
			if(!$ret)
			{
				$is_user_valid= 0;
				$is_pass_valid= 0;
			}
		}
		else {
			if(md5(md5($pass_entered).$udata['pass_salt'])==$udata['password'])
			{
				$is_user_valid	= 1;
				$is_pass_valid	= 1;
			
				$sessid		= md5(uniqid(microtime()));
				$ip			= $icebb->client_ip;
				$user_agent	= $std->clean_string($_SERVER['HTTP_USER_AGENT']);
			
				// Does the user have a cat?
				// TODO: cat detection
			
				//$db->query("DELETE FROM icebb_session_data WHERE username='{$udata['username']}' OR ip='{$ip}'",1);
				if($nodo	== 1)
				{
					return $udata;
				}
				else {
					$this->create_session($udata['username'],$udata['id']);
					
					if($icebb->input['remember']=='true')
					{
						$std->bakeCookie('uid', $udata['id'], 'GE', true);
						$std->bakeCookie('login_key', $udata['login_key'], 'GE', true);
					}
				
					$icebb->user	= $udata;
					return true;
				}
			}
			else {
				$is_user_valid	= 1;
				$is_pass_valid	= 0;
			}
		}
		
		if($udata['id']=='0')
		{
			$std->error($icebb->lang['cant_login_as_guest']);
		}
		
		$ret				= array(
			'user_valid'	=> intval($is_user_valid),
			'pass_valid'	=> intval($is_pass_valid),
		);
		
		return $ret;
	}
	
	/**
	 * Load a user by username and password
	 *
	 * @param		string		Username
	 * @param		string		MD5 hash of password
	 */
	function load_user($username,$pass_md5)
	{
		global $icebb,$db,$std;
	
		$icebb->hooks->hook('login_load_user');
	
		$userq			= $db->query("SELECT * FROM icebb_users WHERE username='{$user_entered}' LIMIT 1");
		$udata			= $db->fetch_row($userq);
	
		if($db->get_num_rows($userq)<=0)
		{
			$is_user_valid		= 0;
		}
		else {
			if(md5($pass_md5.$udata['pass_salt'])==$udata['password'])
			{
				$is_user_valid	= 1;
				$is_pass_valid	= 1;
			
				return $udata;
			}
			else {
				$is_user_valid	= 1;
				$is_pass_valid	= 0;
			}
		}
		
		$ret				= array(
			'user_valid'	=> intval($is_user_valid),
			'pass_valid'	=> intval($is_pass_valid),
		);
		
		return $ret;
	}
	
	/**
	 * Log a user out
	 */
	function logout()
	{
		global $icebb,$db,$config,$std;
		
		$icebb->hooks->hook('login_logout');
		
		$db->query("DELETE FROM icebb_session_data WHERE username='{$icebb->user['username']}' OR ip='".$std->eatCookie('sessid')."'");
		
		$std->bakeCookie('sessid','','Old Spanish Bread Oven');
		$std->bakeCookie('uid','','Old Spanish Bread Oven');
		$std->bakeCookie('login_key','','Old Spanish Bread Oven');
	}
	
	/**
	 * Login a user automatically (if remember me was checked)
	 */
	function autoLogin()
	{
		global $icebb,$db,$config,$std;
	
		$uid				= $std->eatCookie('uid');
		$login_key			= $std->eatCookie('login_key');
	
		$icebb->hooks->hook('login_autoLogin', $uid, $login_key);
	
		$userq				= $db->query("SELECT u.*,g.* FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE u.id=".intval($uid)." AND u.login_key='{$login_key}' LIMIT 1");
		$udata				= $db->fetch_row($userq);
		
		if($db->get_num_rows($userq)>=1)
		{
			if($std->eatCookie('pass')==$udata['password'])
			{
				$sessid		= md5(uniqid(microtime()));
				$ip			= $icebb->client_ip;
				$user_agent	= $std->clean_string($_SERVER['HTTP_USER_AGENT']);
			
				//$db->query("DELETE FROM icebb_session_data WHERE username='{$udata['username']}' OR ip='{$ip}'",1);

				$sessdata	= $this->create_session($udata['username'],$udata['id'],false,true);
				
				$return		= array_merge($udata,$sessdata);
				
				return $return;
			}
			else {
				return false;
			}
		}
	}
	
	function load_session()
	{
		global $icebb,$db,$config,$std;
	
		$session_cookie				= $std->eatCookie('sessid');
		$session_id					= empty($this->session_id) ? $session_cookie : $this->session_id;
		$session_id					= preg_replace("`[^a-z0-9]`i",'',$session_id);

		if($icebb->settings['session_restrict_ip'])
		{
			$where					= "s.sid='{$session_id}' AND s.ip='{$icebb->client_ip}'";
		}
		else {
			$where					= "s.sid='{$session_id}'";
		}
		
		// IP matching for guests to fix IceBB#407
		$where						= "({$where}) OR (u.id=0 AND s.ip='{$icebb->client_ip}')";

		$sessionq					= $db->query("SELECT s.sid,s.ip,s.user_agent,s.last_action,u.*,g.* FROM icebb_session_data AS s LEFT JOIN icebb_users AS u ON s.username=u.username LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE {$where}");
		$sessdata					= $db->fetch_row($sessionq);
		
		if($db->get_num_rows($sessionq)<=0)
		{
			$sessdata				= $this->autoLogin();
			
			if(!is_array($sessdata))
			{
				$sessdata			= $this->poor_mister_guesty_westy();
			}
		}
		
		// are we a moderator... somewhere? anywhere?
		if(is_array($icebb->cache['moderators']))
		{
			$moderators				= $icebb->cache['moderators'];
		}
		else {
			$db->query("SELECT * FROM icebb_moderators");
			while($mo				= $db->fetch_row())
			{
				$moderators[]		= $mo;
			}
		}
		
		$sessdata['moderate']		= array();
		
		if(is_array($moderators))
		{
			foreach($moderators as $i => $m)
			{
				if($m['muserid']	= $sessdata['id'])
				{
					$sessdata['moderate'][]= $m;
				}
			}
		}
		
		$db->query("UPDATE icebb_session_data SET last_action='".time()."',act='{$icebb->input['act']}',func='{$icebb->input['func']}',topic='{$icebb->input['topic']}',forum='{$icebb->input['forum']}',profile='{$icebb->input['profile']}' WHERE sid='{$session_id}'",1);
		
		return $sessdata;
	}
	
	/**
	 * Create a new session
	 *
	 * @param
	 * @param		integer		User ID
	 * @param		boolean		Skip creating a cookie?
	 */
	function create_session($user,$user_id=0,$skip_cookie=false)
	{
		global $icebb,$db,$config,$std;
		
		// clear any old sessions
		if($user_id > 0)
		{
			$db->query("DELETE FROM icebb_session_data WHERE user_id='{$user_id}' OR ip='{$ip}'",1);
		}
		
		$sessid		= md5(uniqid(microtime()));
		$ip			= $icebb->client_ip;
		$user_agent	= $std->clean_string($_SERVER['HTTP_USER_AGENT']);	

		$sessdata	= array(
					'sid'					=> $sessid,
					'user_id'				=> $user_id,
					'username'				=> wash_key($user),
					'ip'					=> $ip,
					'user_agent'			=> $user_agent,
					'last_action'			=> time(),
		);
		
		//print_r($sessdata);
		$icebb->hooks->hook('login_create_session');
		$db->insert_shutdown('icebb_session_data',$sessdata);
		$db->query("UPDATE icebb_users SET ip='{$ip}',last_visit='".time()."' WHERE id='{$user_id}'",1);
		
		if(!$skip_cookie)
		{
			$std->bakeCookie('sessid', $sessid);// or $std->redirect("{$PHP_SELF}s={$sessid}");
		}
		
		return $sessdata;
	}
	
	function poor_mister_guesty_westy()			// O_O
	{
		global $icebb,$db,$config,$std;
		
		$sessid		= md5(microtime());
		$ip			= $icebb->client_ip;
		$user_agent	= $std->clean_string($_SERVER['HTTP_USER_AGENT']);

		//$sessdata	= $this->create_session($icebb->lang['guest'],'0','1');
		$sessdata	= $this->create_session($icebb->lang['guest'],0); // if guests don't get a cookie, then the guest count will increase each time a guest refreshes (cf. FS#230)
		
		$udata		= $db->fetch_result("SELECT u.*,g.* FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE u.id=0 LIMIT 1");
		
		$sessdata	= array_merge($sessdata,$udata);
		
		$icebb->input['s']= $sessdata['sid'];
		//$icebb->base_url.= "s={$sessdata['sid']}&amp;";
		
		return $sessdata;
	}
	
	/**
	 * Generates a salt
	 */
	function generate_password_salt()
	{
		return make_salt(mt_rand(6, 10));
	}
	
	/**
	 * Encrypts a password using a salted MD5 hash
	 *
	 * @param		string		Password
	 * @param		string		The salt to use (if left blank, one will be generated
	 * @param		boolean		Return the salt?
	 */
	function encrypt_password($password, $salt=null, $return_salt=true)
	{
		$salt				= empty($salt) ? $this->generate_password_salt() : $salt;
		$encrypted			= md5(md5($password) . $salt);
		
		if($return_salt == true)
		{
			return array('password' => $encrypted, 'salt' => $salt);
		}
		else {
			return $encrypted;
		}
	}
	
	/**
	 * Create a new account
	 *
	 * @param		array		Array of user information
	 * @param		string		Plaintext password. If left blank, the encrypted password in the array will be used
	 *
	 * @return		array		Array of user information
	 */
	function create_account($udata, $password_plaintext='')
	{
		global $icebb, $db, $std, $cache_func;
		
		$lastuser				= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC LIMIT 1");
		$new_id					= $lastuser['id']+1;
		$udata['id']			= $new_id;
		
		if(empty($udata['user_group']))
		{
			$udata['user_group']= 2;
		}
		
		// check and see whether we need to encrypt the password
		if(!empty($password_plaintext))
		{
			$p					= $this->encrypt_password($password_plaintext);
			$udata['password']	= $p['password'];
			$udata['pass_salt']	= $p['salt'];
		}
		
		$udata['joindate']		= !empty($udata['joindate']) ? $udata['joindate'] : time();
		
		// generate unique login key
		$udata['login_key']		= md5(uniqid(rand(), true));
		
		// insert into DB
		$db->insert('icebb_users',$udata);
			
		////////////////////////////////////////////////////////
		// Update stats and cache
		////////////////////////////////////////////////////////

		$cache_result	= $db->fetch_result("SELECT COUNT(*) as count FROM icebb_users");
		$cache_result2	= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
		$icebb->cache['stats']['user_count']		= $cache_result['count'];
		$icebb->cache['stats']['user_newest']		= $cache_result2;
		$std->recache($icebb->cache['stats'], 'stats');
		
		return $udata;
	}
	
	
	/**
	 * Is the selected object (i.e. username) available for use?
	 *
	 * @param		string		Type of object (username, email, etc.)
	 * @param		string		Value of object
	 * @param		boolean		Make the string safe? (defaults to false)
	 */
	function is_available($object, $value, $clean_string=false)
	{
		global $icebb, $db, $std;
		
		///////////////////////////////////////////////////////
		// EXPLANATION OF RETURNED INTEGERS
		///////////////////////////////////////////////////////
		// -2	: unavailable (banned)
		// -1	: unavailable (taken)
		// 0	: unavailable (unspecified reason)
		// 1	: available
		
		if($clean_string)
		{
			$value					= $std->clean_string($value);
		}
	
		switch($object)
		{	
			////////////////////////////////////////////////////////
			// E-mail address
			////////////////////////////////////////////////////////
			
			case 'email':
				// ban filters
				if(key_exists('banfilters', $icebb->cache) && is_array($icebb->cache['banfilters']))
				{
					foreach($icebb->cache['banfilters'] as $bf)
					{
						if($bf['type'] == 'email')
						{
							if(strpos($bf['value'],'*') !== false)
							{
								$bf['value']			= str_replace('*','.*',preg_quote($bf['value'],'`'));
								
								if(preg_match("`{$bf['value']}`",$value))
								{
									return -2;
								}
							}
							else {
								if($value == $bf['value'])
								{
									return -2;
								}
							}
						}
					}
				}
				
				// taken?
				$db->query("SELECT COUNT(*) FROM icebb_users WHERE email='{$value}'");
				$count					= $db->fetch_row();
				if($count['COUNT(*)'] > 0)
				{
					return -1;
				}
				
				break;
				
			////////////////////////////////////////////////////////
			// OpenID
			////////////////////////////////////////////////////////
			
			case 'openid_server':
				// banned?
				if(key_exists('banfilters', $icebb->cache) && is_array($icebb->cache['banfilters']))
				{
					foreach($icebb->cache['banfilters'] as $bf)
					{
						if($bf['type'] == 'openid')
						{
							if(strpos($value,$bf['value']) !== false)
							{
								return -2;
							}
						}
					}
				}
			
				break;
				
			////////////////////////////////////////////////////////
			// Username
			////////////////////////////////////////////////////////
			
			case 'username':
				// ban filters
				if(key_exists('banfilters', $icebb->cache) && is_array($icebb->cache['banfilters']))
				{
					foreach($icebb->cache['banfilters'] as $bf)
					{
						// banned usernames
						if($bf['type'] == 'username')
						{
							if($value == $bf['value'])
							{
								return -2;
							}
						}
					}
				}
				
				// taken?
				$db->query("SELECT COUNT(*) FROM icebb_users WHERE username='{$value}'");
				$count					= $db->fetch_row();
				if($count['COUNT(*)'] > 0)
				{
					return -1;
				}
				
				break;
		}
		
		return 1;
	}
}
?>
