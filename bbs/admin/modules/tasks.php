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
// manage tasks admin module
// $Id: tasks.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class tasks
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang							= $icebb->admin->learn_language('tasks');
		$this->html							= $icebb->admin_skin->load_template('tasks');
		
		$icebb->admin->page_title			= $this->lang['manage_tasks'];

		switch($icebb->input['func'])
		{
			case 'enable':
				$this->enable_task();
				break;
			case 'disable':
				$this->disable_task();
				break;
			default:
				$this->main();
				break;
		}
		
		$icebb->admin->output();
	}

	function main()
	{
		global $icebb,$db,$std;
	
		$db->query("SELECT * FROM icebb_tasks");
		while($t							= $db->fetch_row())
		{
			if(empty($t['task_lastrun']))
			{
				$t['lastrun']				= $this->lang['never'];
			}
			else {
				$t['lastrun']				= gmdate('m/j/Y @ g:i A',$t['task_lastrun']+$std->get_offset(OFFSET_SERVER));
			}
			
			$t['nextrun']					= gmdate('m/j/Y @ g:i A',$t['task_nextrun']+$std->get_offset(OFFSET_SERVER));
		
			$tasks[]						= $t;
		}
		
		$icebb->admin->html					= $this->html->show_main($tasks);
	}
	
	function gibberish_to_english($day_wk,$day_mo,$hr,$min)
	{
		global $icebb,$db,$std;
		
		if(empty($day_wk) && empty($day_mo) && empty($hr) && !empty($min))
		{
			if($min==1)
			{
				$ret							= "Every minute";
			}
			else {
				$ret							= "Every {$min} minutes";
			}
		}
		else if(empty($day_wk) && empty($day_mo) && !empty($hr))
		{
			if(!empty($min))
			{
				if($min==1)
				{
					$minappend						= ", 1 minute";
				}
				else {
					$minappend						= ", {$min} minutes";
				}
			}
		
			if($hr==1)
			{
				$ret							= "Every hour{$minappend}";
			}
			else {
				$ret							= "Every {$hr} hours{$minappend}";
			}
		}
		else if(!empty($day_wk) && empty($day_mo) && empty($hr) && empty($min))
		{
			if($day_wk							== 0)
			{
				$day_wk_friendly				= "Sunday";
			}
			else if($day_wk						== 1)
			{
				$day_wk_friendly				= "Monday";
			}
			else if($day_wk						== 2)
			{
				$day_wk_friendly				= "Tuesday";
			}
			else if($day_wk						== 3)
			{
				$day_wk_friendly				= "Wednesday";
			}
			else if($day_wk						== 4)
			{
				$day_wk_friendly				= "Thursday";
			}
			else if($day_wk						== 5)
			{
				$day_wk_friendly				= "Friday";
			}
			else if($day_wk						== 6)
			{
				$day_wk_friendly				= "Saturday";
			}
		
			$ret								= "Every {$day_wk_friendly}";
		}
		else if(empty($day_wk) && !empty($day_mo) && empty($hr) && empty($min))
		{
			$madetime							= mktime(0,0,0,1,$day_mo);
			$ret								= "The ".gmdate('jS',$madetime-$std->get_offset(SERVER_OFFSET))." of every month";
		}
		
		return $ret;
	}
	
	function enable_task()
	{
		global $icebb,$db,$std;
		
		$db->query("UPDATE icebb_tasks SET task_enabled=1 WHERE taskid='{$icebb->input['tid']}'");
		
		$icebb->admin->redirect($this->lang['task_enabled'],"{$icebb->base_url}act=tasks");
	}
	
	function disable_task()
	{
		global $icebb,$db,$std;
		
		$db->query("UPDATE icebb_tasks SET task_enabled=0 WHERE taskid='{$icebb->input['tid']}'");
		
		$icebb->admin->redirect($this->lang['task_disabled'],"{$icebb->base_url}act=tasks");
	}
	
	function rebuild_cache()
	{
		global $icebb,$db,$config,$std;
	
		$icebb_tasks					= array();
		$db->query("SELECT * FROM icebb_tasks");
		while($t						= $db->fetch_row())
		{
			$icebb_tasks[]				= $t;
		}
		
		$std->recache($icebb_tasks,'tasks');
	}
}
?>