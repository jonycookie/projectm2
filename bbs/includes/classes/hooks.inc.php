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
// hooks class
// $Id: hooks.inc.php 734 2007-02-10 03:49:24Z mutantmonkey0 $
//******************************************************//

/**
 * A class that allows for "hooks" in various parts of the board that
 * allow for less modding
 *
 * @package		IceBB
 * @version		1.0
 * @date		July 23, 2005
 */
class hooks
{
	/**
	 * Constructor
	 */
	function hooks()
	{
		global $icebb,$db,$std;
	
		$plugins							= array();
	
		if(!is_array($icebb->cache['plugins']))
		{
			$db->query("SELECT * FROM icebb_plugins");
			while($p						= $db->fetch_row())
			{
				$plugins[]					= $p;
			}
			$std->recache($plugins,'plugins');
		}
		else {
			$plugins						= (array)$icebb->cache['plugins'];
		}
		
		foreach($plugins as $p)
		{
			if(!file_exists("{$icebb->path_to_icebb}plugins/{$p['filename']}.plugin.php")) continue;
		
			$plugin_class					= "plugin_".basename($p['filename']);
		
			include_once("{$icebb->path_to_icebb}plugins/{$p['filename']}.plugin.php");
			$this->plugins[$p['filename']]	= new $plugin_class($icebb);
		}
	}

	/**
	 * Run a hook
	 *
	 * @param		string		$hook		The hook function name you want to run
	 */
	function hook($hook)
	{
		$args				= array();
		$ret				= false;
	
		if(func_num_args()>1)
		{
			$args			= func_get_args();
			array_shift($args);
		}
	
		if(is_array($this->plugins))
		{
			foreach($this->plugins as $p)
			{
				if(is_callable(array($p,"hook_{$hook}")))
				{
					$return	= call_user_func_array(array($p,"hook_{$hook}"),$args);
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Add a plugin (only use is for customer registration at the moment)
	 *
	 * @param		string		$filename	Plugin filename
	 * @param		object		$inst		Plugin instance
	 */
	function add_plugin($filename,$inst)
	{
		$this->plugins[$filename]	= $inst;
	}
}
?>
