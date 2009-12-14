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
// customer features module
// $Id: customer.php 811 2005-10-23 20:09:47Z mutantmonkey $
//******************************************************//

class customer
{
	var $root_users;
	
	function run()
	{
		global $icebb,$db,$std,$license_info;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title	= "Customer";
		
		if($icebb->adsess['is_root']!=1)
		{
			//$std->log('admin',"Tried to access log page without permissions",$icebb->adsess['user']);
			$icebb->admin->error("You are not authorized to view this page. Root permissions required.");
		}
		
		if(empty($license_info) || !is_array($license_info))
		{
			$icebb->admin->error("You must purchase a license to view this page");
		}
		
		switch($icebb->input['func'])
		{
			case 'license':
				$this->manage_license();
				break;
			case 'support':
				$this->support();
				break;
			case 'generate_key':
				$this->generate_access_key();
				break;
			case 'remove_key':
				$this->remove_access_key();
				break;
			default:
				$this->main();
				break;
		}
		
		$icebb->admin->html			= $this->html->header().$icebb->admin->html.$this->html->footer();
		
		$icebb->admin->output();
	}
	
	function manage_license()
	{
		global $icebb,$db,$std,$license_info;
		
		$cr						= $license_info['remove_copyright']=='1' ? 'Yes' : 'No';
		
		$icebb->admin->html		= $icebb->admin_skin->start_table("License info");
		$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<strong>Registered to:</strong>",$license_info['registered_to']));
		$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<strong>Copyright removal?</strong>",$cr));
		if(!empty($license_info['expiry']))
		{
			$icebb->admin->html.= $icebb->admin_skin->table_row(array("<strong>Expiration date:</strong>",date('m/d/Y',$license_info['expiry'])));
		}
		$icebb->admin->html	   .= $icebb->admin_skin->end_table();
	}
	
	function support()
	{
		global $icebb,$db,$std,$license_info;
		
		$icebb->admin->error("Use the forums");
	}
	
	function generate_access_key()
	{
		global $icebb,$db,$std,$license_info;
		
		$key					= md5(uniqid(microtime()+time())).md5(rand(0,999999));
		
		$fh						= @fopen('uploads/access_key.php','w');
		@fwrite($fh,"<?php \$access_key='{$key}' ?>");
		@fclose($fh);
		
		require('includes/classes/IXR_Library.inc.php');
		$client					= new IXR_Client("http://icebb.net/backend/support/xmlrpc.php");
		$worked					= $client->query('icebbsupport.receivekey',$icebb->settings['board_url'],$key);
		$response				= $client->getResponse();
		if(!$worked)
		{
			die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
		}
		
		$icebb->admin->html		= $icebb->admin_skin->start_table("Access key");
		$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("Your key is ".$icebb->admin_skin->form_input('key',$key)."<br />Write this down and do NOT give it out to ANYONE. IceBB team members automatically receive the key and will never ask for it."));
		$icebb->admin->html	   .= $icebb->admin_skin->end_table();
	}
	
	function remove_access_key()
	{
		global $icebb,$db,$std,$license_info;
		
		$fh						= @fopen('uploads/access_key.php','w');
		@fwrite($fh,'');
		@fclose($fh);
		@unlink('uploads/access_key.php');
		
		$icebb->admin->redirect("Access key disabled","act=customer&func=support");
	}
}
?>