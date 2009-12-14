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
// icebb core
// $Id: icebb.php 696 2007-01-18 03:06:48Z mutantmonkey0 $
//******************************************************//

error_reporting(E_ERROR | E_PARSE | E_WARNING);

// DEFINE EVERYTHING
// -----------------

define('ICEBB_VERSION'			, '1.0');		// [major].[minor].[patch]
define('ICEBB_VERID'			, '100002');		// [major][minor][patch][release]
define('IN_ICEBB'				, '1');				// to prevent external access

// LOAD REQUIRED CLASSES, ETC.
// ---------------------------

require(PATH_TO_ICEBB . 'includes/classes/error_handler.php');

@include(PATH_TO_ICEBB . 'config.php');
if(empty($config['db_user']))
{
	@header("Location: install/index.php");
	echo "<meta http-equiv='Refresh: 3;install/index.php' /><a href='install/index.php'>Click here if you are not redirected...</a>";
	exit();
}

@include(PATH_TO_ICEBB . 'customer.php');

$icebb->input['debug']			= $_GET['debug'];

require(PATH_TO_ICEBB . "includes/database/{$config['db_engine']}.db.php");
$engine							= "db_{$config['db_engine']}";
$db								= new $engine();

require(PATH_TO_ICEBB . 'includes/classes/timer.php');
$timer->start('main');

// MODULES
// -------

// ICEBB DEVELOPER REFERENCE
// Using modules
// ------------------------------------------------------------
// Add your module to the array below. Your module must be
// uploaded to modules/ with an extension of .php.
// ------------------------------------------------------------
// See the documentation for more information.
// ------------------------------------------------------------

$modules				= array(
'home'					=> 'home',
'login'					=> 'login',
'forum'					=> 'forum',
'topic'					=> 'topic',
'post'					=> 'post',
'profile'				=> 'profile',
'moderate'				=> 'moderate',
'search'				=> 'search',
'ucp'					=> 'usercp',
'ucc'					=> 'usercp',
'pm'					=> 'pm',
'members'				=> 'members',
'boardrules'			=> 'boardrules',
'groups'				=> 'groupcp',
'help'					=> 'help',
'misc'					=> 'misc',
'tags'					=> 'tags',
'attach'				=> 'attach',
);


// WRAP IT UP
// ----------
// Wrap everything up in a nice reusable class here...

$icebb					= new icebb_globals();
class icebb_globals
{
	var $config;
	var $skin;
	var $skin_data;
	var $html_global;
	var $lang;
	var $lang_id;
	var $input;
	var $html;
	var $output;
	var $base_url			= "index.php?";
	var $debug_html;
	var $nav;
	var $path_to_icebb;
	var $url_to_icebb;
	var $client_ip;

	function icebb_globals()
	{
		global $db,$std,$config,$input,$session,$modules;

		$this->config		= $config;
		$this->nav			= array();
		$this->modules		= $modules;
		//$this->lang_id		= 'en';
	}

	function set_output($output)
	{
		$this->output		= $output;
		echo $this->output;
	}
}

class icebb
{
	var $output;

	function init()
	{
		global $icebb,$db,$std,$login_func;

		$this->load_cache();
		$this->load_functions();
		$this->load_extra_classes();		// initialize hooks
		$this->init_session();
		$this->init_skin();
		$this->is_banned();
		$this->is_offline();
		$icebb->hooks->hook('init');		// run first hook
		$this->init_modules();
	}

	function load_cache()
	{
		global $icebb,$db,$std;

		$cache_query		= $db->query("SELECT * FROM icebb_cache");
		while($cached_data	= $db->fetch_row($cache_query))
		{
			$icebb->cache[$cached_data['name']]= unserialize($cached_data['content']);
			$icebb->cache_expiry[$cached_data['name']]= $cached_data['cache_expiry'];
		}

		$icebb->settings	= $icebb->cache['settings'];
		$icebb->forums		= $icebb->cache['forums'];
		$icebb->langs		= $icebb->cache['langs'];
		$icebb->lang_id		= $icebb->langs['default'];
	}


	function load_functions()
	{
		global $icebb,$db,$std,$login_func;

		require(PATH_TO_ICEBB . 'includes/functions.php');
		$std				= new std_func;
		require(PATH_TO_ICEBB . 'includes/classes/skin_func.php');

		$input				= $std->capture_input();
		$icebb->input		= $input;
		$icebb->client_ip	= $input['ICEBB_USER_IP'];
		
		$icebb->lang		= $std->learn_language('global');
	}

	function init_session()
	{
		global $icebb,$db,$std,$login_func;

		if(!empty($icebb->input['s']))
		{
			$icebb->base_url.= "s={$icebb->input['s']}&amp;";
		}

		// read cookies into a nice array
		foreach($_COOKIE as $cook => $dat)
		{
			if(strpos($cook,$icebb->settings['cookie_prefix']))
			{
				$cook			= str_replace($icebb->settings['cookie_prefix'],'',$cook);
				$icebb->cookies[$cook]= wash_key($dat);
			}
		}

		// run tasks if possible
		include(PATH_TO_ICEBB . 'includes/classes/tasks.php');
		$icebb->taskrunner	= new tasks();

		require(PATH_TO_ICEBB . 'includes/classes/sessions.inc.php');
		$sessfunc			= new sessions;
		$session_id			= empty($icebb->input['s']) ? $std->eatCookie('sessid') : $icebb->input['s'];
		$icebb->user		= $sessfunc->load($session_id);
		
		$icebb->lang_id		= empty($icebb->user['langid']) ? $icebb->lang_id : $icebb->user['langid'];
	}

	function init_skin()
	{
		global $icebb,$db,$std;

		$icebb->skin		= new skin_func;
		$skin_id			= empty($icebb->input['skinid']) ? empty($icebb->user['skinid']) ? '' : $icebb->user['skinid'] : $icebb->input['skinid'];

		if(!empty($icebb->input['skinid']) && isset($icebb->input['sticky']) && $icebb->user['id'] > 0)
		{
			$db->query("UPDATE icebb_users SET skinid='".intval($icebb->input['skinid'])."' WHERE id='{$icebb->user['id']}'");
		}
		else if(!empty($icebb->input['skinid']))
		{
			$icebb->base_url.= "skinid={$skin_id}&amp;";
		}

		$icebb->skin_data	= $icebb->skin->load_skin($skin_id);
		//$icebb->html_global	= $icebb->skin->load_template('global');
	}

	function is_banned()
	{
		global $icebb,$db,$std;

		$ubanned					= false;

		// ban filters
		if(is_array($icebb->cache['banfilters']))
		{
			foreach($icebb->cache['banfilters'] as $bf)
			{
				if($bf['type']=='ip')
				{
					if(strpos($bf['value'],'*'))
					{
						$bf['value']		= str_replace('*','.*',preg_quote($bf['value'],'`'));

						if(preg_match("`{$bf['value']}`",$icebb->client_ip))
						{
							$ubanned=true;
						}
					}
					else {
						if($bf['value']==$icebb->client_ip)
						{
							$ubanned=true;
						}
					}
				}
			}
		}

		if($icebb->user['temp_ban']!='0' && time()<$icebb->user['temp_ban'])
		{
			$ubanned=true;
		}
		else if($icebb->user['g_view_board'] == "0")
		{
			$ubanned=true;
		}

		if($ubanned==true && $icebb->input['act'] != "login" && $icebb->input['func'] != "logout")
		{
			$icebb->skin->user_bar_disabled=1;
			$std->error($icebb->lang['banned'],1);
		}
	}

	function is_offline()
	{
		global $icebb,$db,$std;

		if($icebb->settings['is_board_online']=='0' && $icebb->user['g_view_offline_board']!='1' && ($icebb->input['act'] != "login"))
		{
			$icebb->lang= $std->learn_language('login');
		
			$icebb->skin->user_bar_disabled=1;

			if(!class_exists('skin_global')) require(PATH_TO_ICEBB . "skins/{$icebb->skin->skin_id}/global.php");
			$global		= new skin_global;

			$output		= $global->board_offline($icebb->settings['board_offline_msg']);
			$icebb->skin->html_insert($output);
			$icebb->skin->do_output();
			exit();
		}
	}

	function load_extra_classes()
	{
		global $icebb,$db,$std,$hooks,$modules;

		require(PATH_TO_ICEBB . 'includes/classes/hooks.inc.php');
		$hooks					= new hooks;
		$icebb->hooks			= $hooks;
		
		if(function_exists('icebb_customer_init')) icebb_customer_init($icebb);
	}

	function init_modules()
	{
		global $icebb,$db,$std,$modules;

		if($icebb->cancel_init) return;

		if(isset($icebb->input['topic']) && !isset($icebb->input['act']))
		{
			$icebb->input['act']	= 'topic';
		}

		if(isset($icebb->input['forum']) && !isset($icebb->input['act']))
		{
			$icebb->input['act']	= 'forum';
		}

		if(isset($icebb->input['profile']) && !isset($icebb->input['act']))
		{
			$icebb->input['act']	= 'profile';
		}

		if(isset($icebb->input['tag']) && !isset($icebb->input['act']))
		{
			$icebb->input['act']	= 'tags';
		}

		if(!isset($modules[strtolower($icebb->input['act'])]) || empty($modules[strtolower($icebb->input['act'])]))
		{
			if($icebb->settings['portal_main_page']=='1')
			{
				$icebb->input['act']	= 'portal';
			}
			else {
				$icebb->input['act']	= 'home';
			}
		}

		if(file_exists(PATH_TO_ICEBB . "modules/{$modules[strtolower($icebb->input['act'])]}.php"))
		{
			require_once(PATH_TO_ICEBB . "modules/".$modules[strtolower($icebb->input['act'])].".php");
			$doityourself			= new $modules[strtolower($icebb->input['act'])];
			$doityourself->run();
		}
		else {
			$std->error("Failed opening module file '{$modules[strtolower($icebb->input['act'])]}' for inclusion.");
		}
		
		//$db->close();
	}
}
?>
