<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.0 Beta 7
//******************************************************//
// skinwise class (idea by akurashy)
// $Id: skinwise.php 1 2006-04-25 22:10:16Z mutantmonkey $
//******************************************************//

class skinwise
{
	function skinwise()
	{
		global $icebb,$db,$std;
		
	}
	
	function get_wiser($html)
	{
		global $icebb,$db,$std;
		
		$warnings			= array();
		
		$warnings[]			= "SkinWise can't run because it doesn't know anything";
		
		return $warnings;
	}
}
?>