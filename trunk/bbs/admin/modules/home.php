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
// home admin module
// $Id: home.php 781 2005-10-16 19:55:44Z mutantmonkey $
//******************************************************//

class home
{
	function run()
	{
		global $icebb,$config,$db,$std,$login_func,$license_info;
		
		$this->lang					= $icebb->admin->learn_language('home');
		$this->html					= $icebb->admin_skin->load_template('home');
		
		if(isset($icebb->input['get_updates']))
		{
			$this->get_updates();
			exit();
		}
		
		$icebb->admin->page_title	= "";
		
		// saving?
		if(isset($icebb->input['message_box']))
		{
			if(get_magic_quotes_gpc())
			{
				$icebb->input['message_box']= stripslashes($icebb->input['message_box']);
			}
		
			$dadmin			= array('messagebox'=>$icebb->input['message_box']);
			$std->recache($dadmin,'admin');
			$icebb->cache['admin']['messagebox']= $icebb->input['message_box'];
		}
		
		$show_updates				= (bool) GET_MESSAGES_FROM_ICEBB_DOT_NET;
		
		if(isset($icebb->input['phpinfo']))
		{
			phpinfo();
			exit();
		}
		
		$php_sapi_name				= @php_sapi_name();
		if($php_sapi_name		   != 'apache' && $php_sapi_name!='apache2handler' && !empty($php_sapi_name))
		{
			$phpvere				= "-{$php_sapi_name}";
		}
		
		if(@ini_get('safe_mode')	== 1) 
		{
			$phpvere			   .= " (Safe Mode)";
		}
		
		$server_info['icebb_ver']	= ICEBB_VERSION;
		$server_info['php_ver']		= phpversion().$phpvere;
		$server_info['mysql_ver']	= $db->get_version(1);
		
		$whatever					= array();
		
		$db->query("SELECT * FROM icebb_logs WHERE type!='sql' UNION SELECT * FROM icebb_ra_logs ORDER BY time DESC LIMIT 5");
		while($log					= $db->fetch_row())
		{
			$log['action']			= preg_replace("`<a href='index.php(.*)'>`i","<a href='../index.php$1'>",$log['action']);
			$whatever[]				= $log;
		}
		
		$recent_actions				= $this->html->recent_actions($whatever);
		
		$reg_info					= !empty($license_info['reg_key']) ? $this->html->registration_info($license_info,$license_info['expired']) : null;
		$acc_message				= $reg_info.$acc_message;
		$icebb->admin->html		   .= $this->html->display($server_info, $icebb->cache['admin']['messagebox'], $show_updates, $recent_actions);
		
		/*$icebb->admin_skin->start_table("Statistics");
		
		$icebb->admin_skin->table_titles[]= array('Date','10%');
		$icebb->admin_skin->table_titles[]= array('Username','15%');
		$icebb->admin_skin->table_titles[]= array('IP','15%');
		$icebb->admin_skin->table_titles[]= array('Action','60%');
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Recent Actions");
		
		$db->query("SELECT * FROM icebb_logs ORDER BY id DESC LIMIT 3");
		while($log					= $db->fetch_row())
		{
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array(date('n/d/Y g:i A',$log['time']),$log['user'],$log['ip'],$log['action']));
		}
		
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();*/

		$icebb->admin->output();
	}
	
	function get_updates()
	{
		global $icebb;
	
		$acc_updates			= "";
		$acc_message			= "";
	
		$url					= "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
		$url					= preg_replace('`admin/(.*)`','',$url);
		$url					= urlencode($url);
		$get_url				= "http://icebb.net/backend/remote.php?verid=" . ICEBB_VERSION . "&url={$url}";

		// can we use CURL?
		if(function_exists('curl_init'))
		{
			$curl			= curl_init();
			curl_setopt($curl, CURLOPT_URL, $get_url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$remote			= curl_exec($curl);
			curl_close($curl);
		}
		else {
			// nope, use file_get_contents() instead
			@ini_set('default_socket_timeout', 4);
			$remote				= file_get_contents($get_url);
		}
		
		$remote					= @unserialize($remote);
		if(is_array($remote))
		{
			if(version_compare($remote['latest_version'], ICEBB_VERSION)>0)
			{
				$acc_updates	= $this->html->updates_box($remote['latest_version']);
			}
		
			// any security updates for me?
			if(is_array($remote['security']))
			{
				foreach($remote['security'] as $sec)
				{
					if(version_compare($sec[0], ICEBB_VERSION) >= 0)
					{
						$acc_updates		= $this->html->security_updates("<a href='{$sec[1]}'>More Information</a>").$acc_updates;
						break;
					}
				}
			}
		
			// display any messages, can be board specific or global
			$admin_message					= $remote['message'];
		}
		
		if(!empty($admin_message))
		{
			$acc_message					= $this->html->message_box($admin_message);
		}
		
		$icebb->admin->html					= $acc_updates . "\n" . $acc_message;
		echo $icebb->admin->html;
	}
}
?>
