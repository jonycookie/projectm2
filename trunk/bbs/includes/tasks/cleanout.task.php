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
// clean out task
// $Id: cleanout.php 195 2005-07-24 03:51:11Z mutantmonkey $
//******************************************************//

class task_cleanout
{
	function run()
	{
		global $icebb,$db;
	
		/**
		 * All times are in seconds:
		 *   - 3600    = 1 hour
		 *   - 86400   = 1 day
		 *   - 1296000 = 15 days
		 *   - 2592000 = 1 month
		 */
		$cut_off			= array(
			'adsess'		=> time() - 86400,
			//'captcha'		=> time() - 3600,		// not used
			'ra_logs'		=> time() - 2592000,
			'search'		=> time() - 3600,
			'sessions'		=> time() - 1296000,
			'guest_sess'	=> time() - 3600,
		);
	
		$db->query("DELETE FROM icebb_adsess WHERE last_action<{$cut_off['adsess']}");
		
		$db->query("DELETE FROM icebb_captcha");
	
		$db->query("DELETE FROM icebb_ra_logs WHERE time<{$cut_off['ra_logs']}");
	
		$db->query("DELETE FROM icebb_search_results WHERE search_date<{$cut_off['search']}");
	
		$db->query("DELETE FROM icebb_session_data WHERE last_action<{$cut_off['sessions']} AND user_id>0");
		$db->query("DELETE FROM icebb_session_data WHERE last_action<{$cut_off['guest_sess']} AND user_id=0");
	}
}
?>
