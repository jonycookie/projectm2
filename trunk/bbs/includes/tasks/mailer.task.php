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
// bulk mailer task
// $Id: mailer.php 195 2005-07-24 03:51:11Z mutantmonkey $
//******************************************************//

/*
Note about this task:
---------------------
This task is special, it will be run daily by default to see if it
needs to send out any e-mails that are told to be run automatically.
If it is told to start sending out an e-mail either by the builk
mailer or by this task itself, it will automatically update the next
run time and being sending out mails.
*/

class task_mailer
{
	function run()
	{
		global $icebb,$db,$std;
	
		// send the mail
	}
	
	function sendmail()
	{
		global $icebb,$db,$std;
	
	}
}
?>