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
// subscriptions class
// $Id: subscriptions.inc.php 1 2006-04-25 22:10:16Z mutantmonkey $
//******************************************************//

class subscriptions
{
	/**
	 Constructor
	 */
	function subscriptions()
	{
		global $icebb,$db,$std;
	
		//require('includes/classes/mailer.php');
		//$this->mailer		= new mailer;
	}

	function notify($t,$p)
	{
		global $icebb,$db,$std;
		
		$db->query("SELECT s.*,u.* FROM icebb_subscriptions AS s LEFT JOIN icebb_users AS u ON s.suid=u.id WHERE sforum='{$t['forum']}'");
		while($u			= $db->fetch_row())
		{
			$title			= "({$icebb->settings['board_name']}) New post in {$t['title']} on ".gmdate('M j, Y H:i A',time()+$std->get_offset());
			$msg			= $p['ptext'];
			$msg		   .= "\r\n{$icebb->settings['board_url']}index.php?topic={$t['tid']}&pid={$p['pid']}\r\nWe have no control over this message, blah, blah, blah";
		
			// don't send e-mails without a receiver
			if(empty($u['email'])) continue;
		
			$std->send_mail($u['email'], $title, $msg);
		}
	}
}
?>
