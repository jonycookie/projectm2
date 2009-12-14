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
// admin module
// $Id$
//******************************************************//
// ICEBB IS FREE SOFTWARE.
// http://icebb.net/license/
//******************************************************//

error_reporting(E_ERROR | E_PARSE | E_WARNING);

// DEFINE EVERYTHING
// -----------------

define('ICEBB_VERSION'						, '1.0');	// [major].[minor].[patch]
define('ICEBB_VERID'						, '100002');	// [major][minor][patch][release]
define('IN_ICEBB'							, '1');			// to prevent external access

define('PATH_TO_ICEBB'						, '../');

define('GET_MESSAGES_FROM_ICEBB_DOT_NET'	, 1); // 1 = enable - 0 = disable

// END DEFINE EVERYTHING
// ---------------------

require('../includes/classes/error_handler.php');

require('../config.php');

@include('../customer.php');

require('../includes/functions.php');
require('admin_functions.php');
require('../includes/classes/timer.php');
require("../includes/database/{$config['db_engine']}.db.php");
$std					= new std_func;

$engine					= "db_{$config['db_engine']}";
$db						= new $engine();

$timer->start('main');

$input					= $std->capture_input();

// WRAP IT UP
// ----------
// Wrap everything up in a nice reusable class here...

$icebb					= new icebb();
class icebb
{
	var $config;
	var $skin;
	var $input;
	var $html;
	var $lang;
	var $lang_id;
	var $client_ip;
	
	var $menu_cats;
	var $menu_pages;

	function icebb()
	{
		global $db,$std,$config,$input,$session;
	
		$this->config		= $config;
		
		$this->input		= $input;
		$this->client_ip	= $input['ICEBB_USER_IP'];
		
		$this->admin		= new adm_func;
		$this->admin_skin	= new adm_skin;
		
		$this->lang_id		= $config['lang'];
		
		$this->path_to_icebb= '../';
	}
}

// END WRAP IT UP
// --------------

$icebb->lang				= $icebb->admin->learn_language("global");

require('modules/pages.php');
$icebb->menu_cats			= $menu_cats;
$icebb->menu_pages			= $menu_pages;

// LOAD HOOKS
// ----------

require('../includes/classes/hooks.inc.php');
$icebb->hooks				= new hooks;

if(function_exists('icebb_customer_init')) icebb_customer_init($icebb);
$icebb->hooks->hook('admin_init');

// LOAD SESSION
// ------------

$root_users					= explode(',',$config['root_users']);
$icebb->input['s']			= str_replace("'",'',$icebb->input['s']);
$icebb->adsess				= $db->fetch_result("SELECT adsess.*,u.id as userid,u.username,u.temp_ban,g.g_view_board FROM icebb_adsess AS adsess LEFT JOIN icebb_users AS u ON u.username=adsess.user LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE adsess.asid='{$icebb->input['s']}' AND adsess.ip='{$icebb->client_ip}' LIMIT 1");
if(in_array($icebb->adsess['userid'],$root_users))
{
	$icebb->adsess['is_root']= 1;
}

if($icebb->adsess['result_num_rows_returned']<=0)
{
	$icebb->input['act']	= 'login';
}

if(!empty($icebb->adsess['asid']))
{
	$icebb->base_url		= "index.php?s={$icebb->adsess['asid']}&";
	
	// last action too long ago?
	if($icebb->adsess['last_action']+(3600*2)<time())
	{
		$qstring			= array();
		$query_string		= $icebb->input;
		unset($query_string['s']);
		unset($query_string['ICEBB_USER_IP']);
		unset($query_string['ICEBB_QUERY_STRING']);
		foreach($query_string as $k => $v)
		{
			$qstring[]		= "{$k}={$v}";
		}
		
		$db->query("DELETE FROM icebb_adsess WHERE asid='{$icebb->adsess['asid']}' LIMIT 1");
		$icebb->admin->redirect($icebb->lang['sess_expired'],"index.php?return=".implode('%26',$qstring),'_top');
	}
}
else {
	$icebb->base_url		= "index.php?";
}

$db->query("UPDATE icebb_adsess SET last_action='".time()."' WHERE asid='{$icebb->adsess['asid']}'",1);

// END LOAD SESSION
// ----------------


// GET CACHE
// ---------

$cache_query				= $db->query("SELECT * FROM icebb_cache");
while($cached_data			= $db->fetch_row($cache_query))
{
	$icebb->cache[$cached_data['name']]= unserialize(stripslashes($cached_data['content']));
}

$icebb->settings	= $icebb->cache['settings'];
$icebb->forums		= $icebb->cache['forums'];

// END GET CACHE
// -------------


// IS BANNED?
//-----------
$ubanned					= false;

// ban filters
if(key_exists('banfilters',$icebb->cache) && is_array($icebb->cache['banfilters']))
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

if($icebb->adsess['temp_ban']!='0' && time()<$icebb->adsess['temp_ban'])
{
	$ubanned=true;
}
else if($icebb->adsess['g_view_board'] == "0")
{
	$ubanned=true;
}

if($ubanned==true && isset($icebb->input['s']) && $icebb->adsess['is_root']==false)
{
	$icebb->admin->error("You have been banned");
}
// END IS BANNED?
//---------------


// FAILED LOGIN ATTEMPTS
// ---------------------

if($icebb->settings['failed_login_attempts'] == 1)
{
	$db->query("SELECT * FROM icebb_failed_login_attempt_block WHERE ip='{$icebb->client_ip}' AND time>".time());
	if($db->get_num_rows() > 0)
	{
		$binfo	= $db->fetch_row();
		$date	= date('d-m-Y H:i:s',$binfo['time']);
		$icebb->admin->error(sprintf($icebb->lang['too_many_failed'],$date));
	}
	else {
		$check_time = time()-($icebb->settings['failed_login_attempts_check_range']*60);
		$db->query("SELECT * FROM icebb_failedlogin_attempts WHERE attempt_ip='{$icebb->client_ip}' AND attempt_where='acc'");
		if($db->get_num_rows() >= $icebb->settings['failed_login_attempts_attempts'])
		{
			$db->insert('icebb_failed_login_attempt_block',array(
							'ip'	=> $icebb->client_ip,
							'time'	=> time()+($icebb->settings['failed_login_attempts_check_block_time']*60),
					));
			@header("Location: index.php");
			exit();
		}
	}
}

// END FAILED LOGIN ATTEMPTS
// -------------------------


// MODULES
// -------

// ICEBB DEVELOPER REFERENCE
// Using modules
// ------------------------------------------------------------
// Add your module to the array below. Your module must be
// uploaded to modules/admin/ with an extension of .php.
// ------------------------------------------------------------
// See the documentation for more information.
// ------------------------------------------------------------

$modules				= array(
'login'					=> 'login_screen',
'home'					=> 'home',
'menu'					=> 'show_menu',
'settings'				=> 'settings',
'users'					=> 'users',
'groups'				=> 'groups',
'forums'				=> 'forums',
'skins'					=> 'skins',
'skintools'				=> 'skintools',
'langs'					=> 'langs',
'ban'					=> 'ban',
'cache'					=> 'cache',
'tasks'					=> 'tasks',
'recount'				=> 'recount',
'logs'					=> 'logs',
'smilies'				=> 'smilies',
'sql'					=> 'sql',
'wordfilters'			=> 'wordfilters',
'bbcode'				=> 'bbcode',
'plugins'				=> 'plugins',
'help_manager'			=> 'help_manager',
'bulkmail'				=> 'bulkmail',
);

if($icebb->cancel_init) return;

if(empty($modules[strtolower($icebb->input['act'])]))
{
	if(!isset($icebb->input['s']))
	{
		$icebb->input['act']= 'login';
	}
	else {
		$icebb->input['act']= 'home';
	}
}

if(file_exists("modules/{$modules[strtolower($icebb->input['act'])]}.php"))
{
	require_once("modules/".$modules[strtolower($icebb->input['act'])].".php");
	$doityourself			= new $modules[strtolower($icebb->input['act'])];
	$doityourself->run();
}
else {
	$icebb->admin->error("Failed opening module file '{$modules[strtolower($icebb->input['act'])]}' for inclusion.",1);
}

// END MODULES
// -----------
?>
