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
// ban (ip, hostname, e-mail address, etc.) admin module
// $Id: ban.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class bulkmail
{
	function run()
	{
		global $icebb,$db,$config,$std;
		
		$this->lang							= $icebb->admin->learn_language('bulkmail');
		$this->html							= $icebb->admin_skin->load_template('bulkmail');
		
		$icebb->admin->page_title			= $this->lang['bulkmail'];
		
		if($config['disable_bulk_mail'])
		{
			$icebb->admin->error("The bulk mailer has been disabled in the board configuration.");
		}
		
		$groups								= $icebb->cache['groups'];
		
		if(!empty($icebb->input['submit']))
		{
			if(empty($icebb->input['subject']) || empty($icebb->input['message']) || empty($icebb->input['group']))
			{
				$icebb->admin->error('Please fill out the form completely');
			}
			
			foreach($icebb->input['group'] as $g => $tim)
			{
				$group[]					= $g;
			}
			$groups							= implode(',',$group);
			
			if(!empty($icebb->input['respect']))
			{
				$wextra						= " AND email_admin='1'";
			}
		
			$sent_to						= 0;
			$db->query("SELECT email FROM icebb_users WHERE user_group IN ({$groups}){$wextra}");
			while($u						= $db->fetch_row())
			{
				if(empty($u['email'])) continue;
				
				$subject					= $icebb->input['subject'];
				$subject					= htmlspecialchars_decode($subject, ENT_QUOTES);
				
				$message					= $icebb->input['message'];
				$message					= htmlspecialchars_decode($message, ENT_QUOTES);
				//$message					= html_entity_decode($message, ENT_QUOTES);
				
				// send the e-mail
				$std->send_mail($u['email'], $subject, $message);
				
				$sent_to++;
			}
		
			$this->lang['sent']				= sprintf($this->lang['sent'],$sent_to);
			$icebb->admin->redirect($this->lang['sent'],"{$icebb->base_url}act=bulkmail");
		}
		
		$icebb->admin->html					= $this->html->show_create($groups);
		
		$icebb->admin->output();
	}
}
?>
