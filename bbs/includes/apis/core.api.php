<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9
//******************************************************//
// API core
// $Id$
//******************************************************//

/**
 * IceBB API core
 *
 * @package		IceBB
 * @version		0.9
 * @date		October 23, 2005
 */

class api_core
{
	/**
	 * Constructor:
	 * Loads required classes to run the APIs
	 */
	function api_core($icebb_instance='',$db_instance='',$std_instance='')
	{
		if(empty($icebb_instance))
		{
			require(PATH_TO_ICEBB.'icebb.php');
			$this->icebb			= &$icebb;
			$this->icebb_instance	= new icebb;
			$this->icebb_instance->load_cache();
			$this->icebb_instance->load_functions();
			$this->db				= &$db;
			$this->std				= &$std;
		}
		else {
			$this->icebb			= &$icebb_instance;
			$this->db				= &$db_instance;
			$this->std				= &$std_instance;
		}
	}
}
 ?>