<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3
//******************************************************//
// session class
// $Id: sessions.inc.php 825 2007-05-18 09:06:08Z daniel159 $
//******************************************************//

class sessions
{
	var $session_id;
	var $session_ip;
	var $session_ua;

	function load($sessid='')
	{
		global $icebb,$db,$std,$login_func;
		
		$sessid							= preg_replace("`[^a-z0-9]`i",'',$sessid);
		
		// auto login if possible
		$user							= $std->eatCookie('user');
		$pass							= $std->eatCookie('pass');
		if(empty($sessid) && !empty($user) && !empty($pass))
		{
			$sessdata					= $login_func->autoLogin();
		}
		
		if($icebb->settings['session_restrict_ip'])
		{
			$where					= "s.sid='{$sessid}' AND s.ip='{$icebb->client_ip}'";
		}
		else {
			$where					= "s.sid='{$sessid}'";
		}
		
		// IP matching for guests to fix IceBB#407
		$where						= "({$where}) OR (u.id=0 AND s.ip='{$icebb->client_ip}')";
		
		if(!is_array($sessdata) || count($sessdata) < 1)
		{
			//$db->query("SELECT s.sid,s.ip,s.user_agent,s.last_action,u.*,g.* FROM icebb_session_data AS s LEFT JOIN icebb_users AS u ON s.user_id=u.id LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE {$where} ORDER BY s.sid DESC LIMIT 1");
			$db->query("SELECT * FROM `icebb_session_data` AS s LEFT JOIN `icebb_users` AS u ON s.user_id=u.id LEFT JOIN `icebb_groups` AS g ON u.user_group=g.gid WHERE {$where} ORDER BY `id` DESC LIMIT 1");
			if($db->get_num_rows()<=0 && !is_array($sessdata))
			{
				$sessdata				= $login_func->poor_mister_guesty_westy();
			}
			else {
				$sessdata				= $db->fetch_row();
			}
		}
		
		if($sessdata['id']				== '0')
		{
			$sessdata['username']		= $cebb->lang['guest'];
		}
		
		#print_r($sessdata);
		
		//else {
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
			
			$this->session_id			= $sessid;
			$this->session_ip			= $sessdata['ip'];
			$this->session_ua			= $sessdata['user_agent'];
			
			$db->query("UPDATE icebb_session_data SET last_action='".time()."',act='{$icebb->input['act']}',func='{$icebb->input['func']}',topic=".intval($icebb->input['topic']).",forum=".intval($icebb->input['forum']).",profile=".intval($icebb->input['profile'])." WHERE sid='{$sessid}'");
		//}
		
		return $sessdata;
	}
	
	function create($u)
	{
	}
	
	function create_guest()
	{
	}
}
?>
