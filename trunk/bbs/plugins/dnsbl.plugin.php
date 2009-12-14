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
// DNSBL plugin
// $Id$
//******************************************************//

class plugin_dnsbl
{
	var $name					= "DNSBL";
	var $author					= "XAOS Interactive";
	var $author_url				= "http://xaos-ia.com/";
	var $version				= '0.1';
	var $icebb_ver				= '0.9.3';
	
	var $dnsbl					= ".dnsbl.njabl.org";

	function plugin_dnsbl(&$class)
	{
		$this->icebb			= &$class;
	}
	
	function hook_register_init()
	{
		global $std;
		
		return $this->do_ip_check();
	}
	
	function hook_post_init()
	{
		global $std;
		
		return $this->do_ip_check();
	}
	
	
	function do_ip_check()
	{
		global $std;
		
		// first, reverse their IP
		$reversed_ip			= implode('.',array_reverse(explode('.',$this->icebb->client_ip)));
		
		// now, let's query NJABL
		$lookup					= $reversed_ip.$this->dnsbl;
		$result					= gethostbyname($lookup);

		if($result != $lookup && substr(0,8,$result) != '127.0.0.')
		{
			// we've been a naughtly little IP address, haven't we?
			
			$lang['dnsbl']		= "Your IP address has been blacklisted, see <a href='http://njabl.org/lookup?%s'>this page</a> for details.";
			$std->error(sprintf($lang['dnsbl'],$this->icebb->client_ip));
			
			return false;
		}
		else {
			// we're not listed, good
			return true;
		}
	}
}
?>