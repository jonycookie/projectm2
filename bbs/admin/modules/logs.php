<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.kenkarpg.info // 1.0 Beta 6
//******************************************************//
// log viewer admin module
// $Id: logs.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class logs
{
	var $root_users;
	
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title	= "Manage Logs";
		
		if($icebb->adsess['is_root']!=1)
		{
			//$std->log('admin',"Tried to access log page without permissions",$icebb->adsess['user']);
			$icebb->admin->error("You are not authorized to view this page. Root permissions required.");
		}
		
		switch($icebb->input['func'])
		{
			case 'by_user':
				$this->by_user();
				break;
			default:
				$this->main();
				break;
		}
		
		$icebb->admin->html			= $this->html->header().$icebb->admin->html.$this->html->footer();
		
		$icebb->admin->output();
	}
	
	function by_user()
	{
		global $icebb,$config,$db,$std;
		
		if(isset($icebb->input['id']))
		{
			$userq		= $db->query("SELECT * FROM icebb_users WHERE id='{$icebb->input['id']}' LIMIT 1");
			$u			= $db->fetch_row($userq);
			$logsq		= $db->query("SELECT * FROM icebb_logs WHERE user='{$u['username']}' ORDER BY time DESC");
			
			if($db->get_num_rows($logsq) <= 0) {
				$logs = "<em>No log entries</em>\n";
			}
			else {
				$logs .= "<tr class='darkrow'><td><strong>Time:</strong></td><td><strong>IP:</strong></td><td><strong>Type:</strong></td><td><strong>Action:</strong></td></tr>\n";
				while($r = $db->fetch_row($logsq)) {
					$logs .= "<tr class='row1'><td class='col1'>".date('n/d/Y g:i A',$r['time'])."</td><td class='col1'><a href='{$icebb->base_url}act=users&amp;func=iptools&amp;ipaddr={$r['ip']}' title=\"".@gethostbyaddr($r['ip'])."\">{$r['ip']}</a></td><td class='col2'>{$r['type']}</td><td class='col1'>{$r['action']}</td></tr>\n";
				}
			}
			$icebb->admin->html .= $icebb->admin_skin->start_table("User logs ({$u['username']})");
			$icebb->admin->html .= $logs;
			$icebb->admin->html .= $icebb->admin_skin->end_table();
		}
		else {
			$icebb->admin->error("No user selected");
		}
	}
	
	function main()
	{
		global $icebb,$config,$db,$std;
		
		$last_fiveq		= $db->query("SELECT * FROM icebb_logs ORDER BY id DESC LIMIT 5");
		
		$db->query("SELECT * FROM icebb_logs");
		while($log		= $db->fetch_row())
		{
			$logs[$log['user']][]= $log;
		}
		
		$usersq			= $db->query("SELECT * FROM icebb_users WHERE user_group='1' ORDER BY id ASC");
		
		if(count($logs)<=0)
		{
			$users = "<em>No logs</em>\n";
		}
		else {
			while($u	= $db->fetch_row($usersq))
			{
				$root = (in_array($u['id'],explode(',',$config['root_users']))) ? " (Root Admin)" : false;
				
				if(count($logs[$u['username']])>=1)
				{
					$users		   .= "<tr class='row1'><td class='row1'><a href='{$icebb->base_url}act=logs&amp;func=by_user&amp;id={$u['id']}'>{$u['username']}</a>{$root}</td></tr>\n";
				}
			}
		}
		
		if($db->get_num_rows($last_fiveq) <= 0)
		{
			$tlogs = "<em>No log entries</em>\n";
		}
		else {
			$tlogs .= "<tr class='darkrow'><td><strong>Time:</strong></td><td><strong>User:</strong></td><td><strong>IP:</strong></td><td><strong>Type:</strong></td><td><strong>Action:</strong></td></tr>\n";
			while($r = $db->fetch_row($last_fiveq)) {
				$tlogs .= "<tr class='row1'><td class='col1'>".date('n/d/Y g:i A',$r['time'])."</td><td class='col2'>{$r['user']}</td><td class='col1'><a href='{$icebb->base_url}act=users&amp;func=iptools&amp;ipaddr={$r['ip']}' title=\"".@gethostbyaddr($r['ip'])."\">{$r['ip']}</a></td><td class='col2'>{$r['type']}</td><td class='col1'>{$r['action']}</td></tr>\n";
			} 
		}
		
		$icebb->admin->html .= $icebb->admin_skin->start_table("View logs by user");
		$icebb->admin->html .= $users;
		$icebb->admin->html .= $icebb->admin_skin->end_table();
		$icebb->admin->html .= $icebb->admin_skin->start_table("Last 5 log entries"); 
		$icebb->admin->html .= $tlogs;
		$icebb->admin->html .= $icebb->admin_skin->end_table();
	}
}
?>
