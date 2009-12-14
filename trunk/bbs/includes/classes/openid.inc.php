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
// OpenID class
// $Id$
//******************************************************//

if(!defined(Auth_OpenID_NO_MATH_SUPPORT)) define('Auth_OpenID_NO_MATH_SUPPORT',true);
if(!defined('Auth_OpenID_RAND_SOURCE')) define('Auth_OpenID_RAND_SOURCE',null);

/**
 * This class does all the work for OpenID logins.
 * Ported from IceBB 1.1
 *
 * @package		IceBB
 * @version		1.0
 */
class icebb_openid
{
	var $process_url;
	var $trust_root;
	
	// DO NOT TOUCH
	var $_store;
	var $_consumer;
	var $user_data					= array();
	
	/**
	 * Constructor
	 */
	function icebb_openid()
	{
		global $icebb;
	
		$this->get_openid();
		
		$this->trust_root			= $icebb->settings['board_url'];
		$this->process_url			= $icebb->settings['board_url'] . "index.php?act=login&openid_finish=1";
	}

	/**
	 * Get the JanRain OpenID library
	 */
	function get_openid()
	{
		global $icebb,$XRI_AUTHORITIES,$__Services_Yadis_xml_extensions;
		
		session_start();
	
		// set the include path
		$path_extra				= dirname(dirname(dirname(__FILE__))) . '/';
		$path_extra			   .= 'includes/classes/openid/';
		$path					= ini_get('include_path');
		$path					= $path_extra . PATH_SEPARATOR . $path;
		set_include_path($path);
	
		// get the OpenID consumer code
		require_once("Auth/OpenID/Consumer.php");
		
		// get MySQL store modules
		require_once('Auth/OpenID/MySQLStore.php');
		require_once('Auth/OpenID/DatabaseConnection.php');
		
		// get IceBB store module
		require_once('icebb_db_store.php');
		
		// get Simple Registration extension API
		require_once("Auth/OpenID/SReg.php");

		// create objects
		$this->_store			= new IceBB_DB_Store();
		$this->_consumer		= new Auth_OpenID_Consumer($this->_store);
		
		return true;
	}
	
	/**
	 * Try authenticating a user using OpenID
	 *
	 * @argument	string		OpenID URL
	 */
	function try_auth($openid)
	{
		global $icebb,$db,$login_func;
	
		if(!is_object($this->_consumer)) return false;
	
		// this allows for "remember me" to be used with OpenID
		$_SESSION['remember_me']	= $icebb->input['remember'];
	
		if(empty($openid))
		{
			$this->auth_error		= $icebb->lang['openid_not_url'];
			return false;
		}
		
		$auth_request				= $this->_consumer->begin($openid);

		if(!$auth_request)
		{
			$this->auth_error		= $icebb->lang['openid_invalid'];
			return false;
		}

		// have we logged in with OpenID before?
		$openid2					= Auth_OpenID::normalizeUrl($openid);
		$db->query("SELECT uid FROM icebb_openid_urls WHERE url='{$openid2}'");
		if($db->get_num_rows() <= 0)
		{
			$sreg_request			= Auth_OpenID_SRegRequest::build(
				// required
				array(),
				// optional
				array('email','gender','dob','language')
			);

			if($sreg_request)
			{
				$auth_request->addExtension($sreg_request);
			}
		}

		////////////////////////////////////////////////////////
		// Is their OpenID server blocked?
		////////////////////////////////////////////////////////

		if(method_exists($login_func,'is_available') &&
		   $login_func->is_available('openid_server',$auth_request->endpoint->server_url) < 1)
		{
			$this->auth_error			= $icebb->lang['openid_blocked'];
			return false;
		}

		////////////////////////////////////////////////////////
		// Send us to the OpenID server for authorization
		////////////////////////////////////////////////////////
		
		if($auth_request->shouldSendRedirect())
		{
			// Send us off to the OpenID server for verification...
			$redirect_url				= $auth_request->redirectURL($this->trust_root,$this->process_url);
			
			if(Auth_OpenID::isFailure($redirect_url))
			{
				$this->auth_error		= $redirect_url->message;
			}
			else {
				header("Location: " . $redirect_url);
			}
		}
		else {
			// Generate form markup and render it.
			$form_id					= 'openid_message';
			$form_html					= $auth_request->formMarkup($this->trust_root,$this->process_url,false,array('id' => $form_id));

			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if(Auth_OpenID::isFailure($form_html))
			{
				$this->auth_error		= $form_html->message;
			}
			else {
				$page_contents = array(
				"<html><head><title>",
				"OpenID transaction in progress",
				"</title></head>",
				"<body onload='document.getElementById(\"".$form_id."\").submit()'>",
				$form_html,
				"</body></html>");

				echo implode("\n",$page_contents);
				@ob_end_flush();
				exit();
			}
		}
	}
	
	/**
	 * Complete OpenID authentication
	 */
	function finish_auth()
	{
		global $icebb,$db,$std,$login_func;
	
		if(!is_object($this->_consumer)) return false;
			
		// Complete the authentication process using the server's response.
		$response							= $this->_consumer->complete($this->process_url);

		// check auth status
		if($response->status == Auth_OpenID_CANCEL)
		{
			$std->error($icebb->lang['openid_auth_cancelled']);
			return false;
		}
		else if($response->status == Auth_OpenID_FAILURE)
		{
			$std->error($icebb->lang['openid_auth_fail'].': '.$response->message);
			return false;
		}
		else if($response->status == Auth_OpenID_SUCCESS)
		{
			$openid							= $std->clean_string($response->identity_url);
			
			$sreg_resp						= Auth_OpenID_SRegResponse::fromSuccessResponse($response);
			$sreg							= $sreg_resp->contents();
		}
		
		// have we logged in with OpenID before?
		$db->query("SELECT o.uid AS id,u.username FROM icebb_openid_urls AS o LEFT JOIN icebb_users AS u ON u.id=o.uid WHERE o.url='{$openid}'");
		if($db->get_num_rows() > 0)
		{
			// we already have an account, so just load us
			$this->user_data				= $db->fetch_row();
		}
		else {
			$email							= $sreg['email'];
			
			$requested_info					= array();
			$requested_info[]				= 'username';
			if(empty($email))		$requested_info[]		= 'email';
			
			if($login_func->is_available('username',$username,true) < 1)
			{
				$requested_info[]			= 'username';
				$requested_why[]			= $icebb->lang['openid_username_taken'];
			}
			
			if(count($requested_info) > 0)
			{
				$this->request_info($openid,$sreg,$requested_info,$requested_why);
				return false;
			}
			
			$this->openid_create_account($openid,$sreg,$username,$email);
		}
		
		$this->user_data		= $login_func->create_session($this->user_data['username'],$this->user_data['id']);
		
		if($_SESSION['remember_me'])
		{
			$std->bakeCookie('uid', $this->user_data['id'], 'GE', true);
			$std->bakeCookie('login_key', $this->user_data['login_key'], 'GE', true);
		}
		
		$this->output			= $std->bouncy_bouncy($icebb->lang['loggin_you_in'],"index.php");
		
		return true;
	}
	
	/**
	 * Complete OpenID authentication (admin)
	 */
	function admin_finish_auth(&$admin)
	{
		global $icebb,$db,$std,$login_func;
	
		if(!is_object($this->_consumer)) return false;
			
		// Complete the authentication process using the server's response.
		$response							= $this->_consumer->complete($this->process_url);

		// check auth status
		if($response->status == Auth_OpenID_CANCEL)
		{
			//$admin->login_form($icebb->lang['openid_auth_cancelled']);
			die('auth cancelled');
			return false;
		}
		else if($response->status == Auth_OpenID_FAILURE)
		{
			//$admin->login_form($icebb->lang['openid_auth_fail'].': '.$response->message);
			die('auth fail: '.$response->message);
			return false;
		}
		else if($response->status == Auth_OpenID_SUCCESS)
		{
			$openid							= $std->clean_string($response->identity_url);
		}
		
		// have we logged in with OpenID before?
		$db->query("SELECT o.uid AS id,u.*,g.* FROM icebb_openid_urls AS o LEFT JOIN icebb_users AS u ON u.id=o.uid LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE o.url='{$openid}'");
		if($db->get_num_rows() > 0)
		{
			// we already have an account, so just load us
			$udata							= $db->fetch_row();
		}
		else {
			$admin->login_form($this->lang['openid_must_login_first']);
		}
		
		$admin->finish_login($udata);
		
		return true;
	}
	
	/**
	 * Create a user's account using their OpenID info
	 *
	 * @access private
	 * @argument		string		Valid OpenID URL
	 * @argument		array		Array containg $sreg data from OpenID server
	 * @argument		string		Username
	 * @argument		string		E-mail address
	 */
	function openid_create_account($openid, $sreg, $username, $email)
	{
		global $db,$std,$login_func;
	
		// create a new account
		$this->user_data		= array(
			'username'			=> $username,
			'email'				=> $email,
			//'gmt'				=> $tz,
			'langid'			=> strtolower($sreg['language']),
			'gender'			=> strtolower($sreg['gender']),
			'birthdate'			=> !empty($sreg['dob']) ? strtotime($sreg['dob']) : null,
		);

		$this->user_data		= $login_func->create_account($this->user_data,$password);
		
		$db->insert('icebb_openid_urls',array(
			'uid'				=> $this->user_data['id'],
			'url'				=> $openid,
		));
	}
	
	/**
	 * Request additional information from user
	 *
	 * @argument		string		Valid OpenID URL
	 * @argument		array		Array containg $sreg data from OpenID server
	 * @argument		array		What to request
	 * @argument		array		Why we're requesting it
	 */
	function request_info($openid, $sreg, $what=array(), $why=array())
	{
		global $icebb,$db,$std,$login_func,$cache_func;
		
		// use a PHP session to keep track of the data (a bit hacky, but oh well)
		//session_start();
		
		if(!empty($openid))
		{
			$_SESSION['openid']			= $openid;
			$_SESSION['openid_sreg']	= $sreg;
			$_SESSION['openid_what']	= $what;
			
			$firstpg					= true;
		}
		else {
			$openid						= $_SESSION['openid'];
			$sreg						= $_SESSION['openid_sreg'];
			$what						= $_SESSION['openid_what'];
		}
		
		$this->html						= $icebb->skin->load_template('login');
		
		if(in_array('username',$what))
		{
			if(!empty($icebb->input['user']))
			{
				if($login_func->is_available('username', $icebb->input['user'], true) < 1)
				{
					$what[]				= 'username';
					$why[]				= $icebb->lang['openid_username_taken'];
					
					$errors[]			= 'username';
				}
			}
			else {
				$errors[]				= 'username';
			}
		}
		
		if(in_array('email',$what))
		{
			if(!empty($icebb->input['email']))
			{
				if($login_func->is_available('email',$icebb->input['email'],true) < 1)
				{
					$what[]				= 'email';
					$why[]				= $icebb->lang['openid_email_taken'];
			
					$errors[]			= 'email';
				}
			}
			else {
				$errors[]				= 'email';
			}
		}

		// Okay, we're good. Let's log us in...
		if(!$firstpg && count($errors) < 1)
		{
			$username					= !empty($icebb->input['user']) ? $icebb->input['user'] : $sreg['nickname'];
			$email						= !empty($icebb->input['email']) ? $icebb->input['email'] : $sreg['email'];
		
			$this->openid_create_account($openid,$sreg,$username,$email);
			
			$this->user_data			= $login_func->create_session($this->user_data['username'],$this->user_data['id']);
			$this->output				= $std->bouncy_bouncy($icebb->lang['loggin_you_in'],"index.php");
		}
		
		$this->output				   .= $this->html->openid_more_needed($what,$why);
	
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output(true);
	}
	
	/**
	 * Merge accounts
	 */
	function merge_accounts()
	{
		global $icebb,$db,$std,$login_func;
	
		if(!is_object($this->_consumer)) return false;
			
		// Complete the authentication process using the server's response.
		$response							= $this->_consumer->complete($this->process_url);//$_GET);

		// check auth status
		if($response->status == Auth_OpenID_CANCEL)
		{
			$std->error($icebb->lang['openid_auth_cancelled']);
			return false;
		}
		else if($response->status == Auth_OpenID_FAILURE)
		{
			$std->error($icebb->lang['openid_auth_fail'].': '.$response->message);
			return false;
		}
		else if($response->status == Auth_OpenID_SUCCESS)
		{
			$openid							= $std->clean_string($response->identity_url);
			
			$sreg_resp						= Auth_OpenID_SRegResponse::fromSuccessResponse($response);
			$sreg							= $sreg_resp->contents();
		}
		
		$this->associate_existing($icebb->user['id'], $openid);
		
		$std->bouncy_bouncy($icebb->lang['loggin_you_in'],"index.php");
		
		return true;
	}
	
	/**
	 * Associate an existing account with an OpenID
	 *
	 * @argument		int			User ID
	 * @argument		string		Valid OpenID URL
	 */
	function associate_existing($user_id,$openid)
	{
		global $icebb,$db,$std,$login_func;
		
		$db->query("UPDATE icebb_users SET password='',pass_salt='' WHERE id = ".intval($user_id));
		
		$db->insert('icebb_openid_urls', array(
			'uid'				=> $user_id,
			'url'				=> $openid,
		));
	}
}
?>
