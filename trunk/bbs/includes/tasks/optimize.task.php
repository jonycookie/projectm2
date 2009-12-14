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
// clean out task
// $Id: cleanout.php 195 2005-07-24 03:51:11Z mutantmonkey $
//******************************************************//

class task_optimize
{
	function run()
	{
		global $icebb,$db,$std;
	
		$tables_to_optimize		= array(
			'icebb_adsess',
			'icebb_cache',
			'icebb_failedlogin_block',
			'icebb_favorites',
			'icebb_search_results',
			'icebb_session_data',
			'icebb_subscriptions',
			'icebb_users_validating',
		);
		
		foreach($tables_to_optimize as $tbl)
		{
			$db->query("OPTIMIZE TABLE {$tbl}",1);
		}
	}
}
?>