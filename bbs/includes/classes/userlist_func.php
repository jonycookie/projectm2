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
// user list functions class
// $Id: userlist_func.php 423 2006-08-24 17:51:27Z mutantmonkey $
//******************************************************//

class userlist
{
	var $ollist;
	var $online;
	var $online_users				= array();
	var $online_info				= array();
	var $online_groups;

	function userlist()
	{
		global $icebb,$db,$std;
	
		//$this->run();
	}
	
	function run()
	{
		global $icebb,$db,$std;
		
		$this->online['total']		= 0;
		$this->online['users']		= 0;
		$this->online['guests']		= 0;
		//$this->online_groups[$icebb->user['user_group']]= 1;
		
		if($icebb->user['id'] > 0)
		{
			$this->add_user($icebb->user);
			$this->online_info[]	= $icebb->user;
		}
		else {				// eww... a guest
			$this->online['total']	= 0;
			$this->online['users']	= 0;
			$this->online['guests']	= 0;
		}
		
		$bobette		= $db->query("SELECT u.id,u.user_group,s.*,g.* FROM icebb_session_data AS s LEFT JOIN icebb_users AS u ON s.user_id=u.id LEFT JOIN icebb_groups AS g ON g.gid=u.user_group WHERE last_action>".(time()-(15*60))."");
		while($u		= $db->fetch_row($bobette))
		{
			if($u['id']!='0')
			{		
				if($u['id']			== $icebb->user['id'])
				{
					continue;
				}
				
				$this->add_user($u);
			}
			else {
				$spider_list	= explode("\n",$icebb->settings['spider_list']);
				if(is_array($spider_list))
				{
					foreach($spider_list as $spidere)
					{
						$spider			= explode('=',$spidere);
						$spider[1]		= str_replace("\r",'',$spider[1]);
						if(in_string($spider[0],$u['user_agent'],1) && !empty($spider[1]) && !in_array($spider[1],$this->online_users))
						{
							$this->online_users[]= "{$spider[1]}";
						}
					}
				}
			
				$this->online['guests']++;
				$this->online['total']++;
			}
				
			$this->online_info[]= $u;
		}
		
		if(is_array($this->online_users))
		{
			$this->ollist		= implode(', ',$this->online_users);
		}
		
		return array($this->ollist,array($this->online,$this->online_groups));
	}
	
	function add_user($u)
	{
		global $icebb,$db,$std;
		
		$thise			   .= "<a href='{$icebb->base_url}profile={$u['id']}'>{$u['g_prefix']}{$u['username']}{$u['g_suffix']}</a>";
		
		if(in_array($thise,$this->online_users) || empty($thise))
		{
			return;
		} 
		
		$this->online_users[]= $thise;
		
		$this->online['users']++;
		$this->online['total']++;
		$this->online_groups[$u['user_group']]++;
	}
}
?>
