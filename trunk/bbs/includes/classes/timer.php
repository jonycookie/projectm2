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
// timer class
// $Id: timer.php 1 2006-04-25 22:10:16Z mutantmonkey $
//******************************************************//

$timer					= new timer;

class timer
{
	var $timers			= array();

	function start($name)
	{
		$starttime		= microtime();
		$starttime		= explode(' ',$starttime);
		$this->timers[$name]= $starttime[0]+$starttime[1];
	}
	
	function stop($name,$round=6)
	{
		$stoptime		= microtime();
		$stoptime		= explode(' ',$stoptime);
		$stoptime		= $stoptime[0]+$stoptime[1];
	
		$totaltime		= $stoptime-$this->timers[$name];
		$totaltime		= round($totaltime,$round);
	
		return $totaltime;
	}
}
?>