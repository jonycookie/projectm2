<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.1
//******************************************************//
// board rules module
// $Id: boardrules.php 316 2006-07-06 13:43:15Z daniel $
//******************************************************//

class boardrules
{
	function run()
	{
		global $icebb,$config,$db,$std;
	
		if($icebb->input['the_sexy_error_handler'])
		{
			trigger_error("The script exploded");
		}
		
		$this->html						= $icebb->skin->load_template('boardrules');
		$std->learn_language("boardrules");
		
		$rules							= nl2br($icebb->settings['board_rules']);
		$rules							= str_replace("&lt;","<",$rules);
		$rules							= str_replace("&gt;",">",$rules);
		
		$output							= $this->html->rules_page($rules);
		
		$icebb->skin->html_insert($output);
		$icebb->skin->do_output();
	}
}
?>