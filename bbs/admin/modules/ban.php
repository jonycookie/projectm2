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
// ban (ip, hostname, e-mail address, etc.) admin module
// $Id: ban.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class ban
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang							= $icebb->admin->learn_language('ban');
		$this->html							= $icebb->admin_skin->load_template('ban');
		
		$icebb->admin->page_title			= $this->lang['ban'];
		
		$icebb->admin->html					= $icebb->admin_skin->start_form('admin.php',array('act'=>'ban'));

		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');

		$filters							= array();
		$filters['ip']						= array();
		$filters['username']				= array();
		$filters['email']					= array();
		
		$bfq								= $db->query("SELECT * FROM icebb_banfilters");
		while($f							= $db->fetch_row())
		{
			$filters[$f['type']][]			= $f;
		}
		
		/////////////////////////////////////////////
		// IP Filters
		/////////////////////////////////////////////
		
		if(isset($icebb->input['ipadd']))
		{
			$db->insert('icebb_banfilters',array(
				'type'						=> 'ip',
				'value'						=> $icebb->input['ip'],
			));
			
			$this->rebuild_cache();
			
			$std->log("admin","Added ban filter: {$icebb->input['ip']}",$icebb->adsess['user']);
			$icebb->admin->redirect("Ban filter added.",$icebb->base_url.'act=ban');
		}
		
		$ips								= array();
		foreach($filters['ip'] as $bf)
		{
			if($icebb->input['ipedit']		== $bf['bfid'])
			{
				if(isset($icebb->input['ipeditn']))
				{
					$db->query("UPDATE icebb_banfilters SET value='{$icebb->input['ip-'.$bf['bfid']]}' WHERE bfid='{$bf['bfid']}'");
					$this->rebuild_cache();
					
					$std->log("admin","Updated ban filter: {$bf['value']} -> {$icebb->input['ip-'.$bf['bfid']]}",$icebb->adsess['user']);
					
					$icebb->admin->redirect("Ban filter updated.",$icebb->base_url.'act=ban');
				}
				
				$bf['editing']				= true;
				$ips[]						= $bf;
			}
			else if($icebb->input['iprem']	== $bf['bfid'])
			{
				$db->query("DELETE FROM icebb_banfilters WHERE bfid='{$icebb->input['iprem']}' LIMIT 1");
				$this->rebuild_cache();
				
				$std->log("admin","Deleted ban filter: {$bf['value']}",$icebb->adsess['user']);
			}
			else {
				$ips[]						= $bf;
			}
		}
		
		/////////////////////////////////////////////
		// Username Filters
		/////////////////////////////////////////////
		
		if(isset($icebb->input['useradd']))
		{
			$db->insert('icebb_banfilters',array(
				'type'						=> 'username',
				'value'						=> $icebb->input['user'],
			));
			
			$this->rebuild_cache();
			
			$std->log("admin","Added ban filter: {$icebb->input['user']}",$icebb->adsess['user']);
			$icebb->admin->redirect("Ban filter added.",$icebb->base_url.'act=ban');
		}
		
		$usernames							= array();
		foreach($filters['username'] as $user)
		{
			if($icebb->input['useredit']	== $user['bfid'])
			{
				if(isset($icebb->input['usereditn']))
				{
					$db->query("UPDATE icebb_banfilters SET value='{$icebb->input['user-'.$user['bfid']]}' WHERE bfid='{$user['bfid']}'");
					$this->rebuild_cache();
					
					$std->log("admin","Updated ban filter: {$user['value']} -> {$icebb->input['user-'.$user['bfid']]}",$icebb->adsess['user']);
					
					$icebb->admin->redirect("Ban filter updated.",$icebb->base_url.'act=ban');
				}
				
				$em['editing']				= true;
				$emails[]					= $em;
			}
			else if($icebb->input['userrem']== $user['bfid'])
			{
				$db->query("DELETE FROM icebb_banfilters WHERE bfid='{$icebb->input['userrem']}' LIMIT 1");	
				$this->rebuild_cache();
				
				$std->log("admin","Deleted ban filter: {$user['value']}",$icebb->adsess['user']);
			}
			else {
				$usernames[]				= $user;
			}
		}
		
		/////////////////////////////////////////////
		// E-mail Address Filters
		/////////////////////////////////////////////
		
		if(isset($icebb->input['eadd']))
		{
			$db->insert('icebb_banfilters',array(
				'type'						=> 'email',
				'value'						=> $icebb->input['e'],
			));
			
			$this->rebuild_cache();
			
			$std->log("admin","Added ban filter: {$icebb->input['e']}",$icebb->adsess['user']);
			$icebb->admin->redirect("Ban filter added.",$icebb->base_url.'act=ban');
		}
		
		$emails								= array();
		foreach($filters['email'] as $em)
		{
			if($icebb->input['eedit']		== $em['bfid'])
			{
				if(isset($icebb->input['eeditn']))
				{
					$db->query("UPDATE icebb_banfilters SET value='{$icebb->input['ip-'.$em['bfid']]}' WHERE bfid='{$em['bfid']}'");
					$this->rebuild_cache();
					
					$std->log("admin","Updated ban filter: {$em['value']} -> {$icebb->input['e-'.$em['bfid']]}",$icebb->adsess['user']);
					
					$icebb->admin->redirect("Ban filter updated.",$icebb->base_url.'act=ban');
				}
				
				$em['editing']				= true;
				$emails[]					= $em;
			}
			else if($icebb->input['erem']	== $em['bfid'])
			{
				$db->query("DELETE FROM icebb_banfilters WHERE bfid='{$icebb->input['erem']}' LIMIT 1");	
				$this->rebuild_cache();
				
				$std->log("admin","Deleted ban filter: {$e['value']}",$icebb->adsess['user']);
			}
			else {
				$emails[]					= $em;
			}
		}

		$icebb->admin->html					= $this->html->show_main($ips,$usernames,$emails);
		
		$icebb->admin->output();
	}
	
	function rebuild_cache()
	{
		global $icebb,$db,$config,$std;
	
		$banfilters						= array();
		$db->query("SELECT * FROM icebb_banfilters");
		while($bf						= $db->fetch_row())
		{
			$banfilters[]				= $bf;
		}
		
		$std->recache($banfilters,'banfilters');
	}
}
?>