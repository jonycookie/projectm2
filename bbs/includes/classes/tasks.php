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
// run task class
// $Id: tasks.php 52 2006-05-11 21:40:25Z mutantmonkey $
//******************************************************//

class tasks
{
	function tasks()
	{
		global $icebb,$db,$std;
		
		@register_shutdown_function(array($this,'run_me'));
	}
	
	function run_me()
	{
		global $icebb,$db,$std;
	
		// get our tasks, do we need to run any?
		$tasks					= $icebb->cache['tasks'];
		
		$db->query("SELECT * FROM icebb_tasks");
		while($task				= $db->fetch_row())
		{
			if($task['task_nextrun'] <= time())
			{
				$tfile			= explode('.',$task['task_file']);
				if(file_exists($icebb->settings['board_path'] . "includes/tasks/{$task['task_file']}"))
				{
					include($icebb->settings['board_path'] . "includes/tasks/{$task['task_file']}");
					$tname			= 'task_'.$tfile[0];
					$t				= new $tname;
					$ret			= $t->run();
					
					$nextrun1		= time();
					
					if(!empty($task['task_hr']))
					{
						$nextrun1	= $nextrun1+(3600*$task['task_hr']);
					}
					
					$nextrun		= empty($ret['nextrun']) ? $nextrun1 : $ret['nextrun'];
					
					$db->query("UPDATE icebb_tasks SET task_lastrun=".time().",task_nextrun={$nextrun} WHERE taskid='{$task['taskid']}' LIMIT 1",1);
				}
			} 
		}
	}
}
?>
