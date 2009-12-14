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
// plug-ins admin module
// $Id: plugins.php 332 2005-08-03 15:17:53Z mutantmonkey $
//******************************************************//

class plugins
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('plugins');
		$this->html					= $icebb->admin_skin->load_template('plugins');
		
		$icebb->admin->page_title	= $this->lang['plugins'];
		
		if(!empty($icebb->input['enable']))
		{
			$icebb->input['func']	= 'enable';
		}
		else if(!empty($icebb->input['disable']))
		{
			$icebb->input['func']	= 'disable';
		}
		
		switch($icebb->input['func'])
		{
			case 'enable':
				$this->enable_plugin();
				break;
			case 'disable':
				$this->disable_plugin();
				break;
			default:
				$this->manage();
				break;
		}

		$icebb->admin->output();
	}
	
	function manage()
	{
		global $icebb,$db,$std;
	
		$plugin_db			= array();
		
		$db->query("SELECT * FROM icebb_plugins");
		while($p			= $db->fetch_row())
		{
			$plugin_db[]	= $p['filename'];
		}
	
		$dh					= @opendir('../plugins/');
		while($file			= @readdir($dh))
		{
			$file			= explode('.',$file);
			
			if($file[1]!= 'plugin' && $file[2]!='php')
			{
				continue;
			}
		
			$plug['file']	= $file[0];
		
			if(in_array($file[0],$plugin_db))
			{
				$plug['enabled']= 1;
			}
			else {
				$plug['enabled']= 0;
			}
		
			$plugins[]= $plug;
		}
		@closedir($dh);
		
		@ksort($plugins);
		
		foreach($plugins as $p)
		{
			if(!$p['enabled'])
			{
				include(PATH_TO_ICEBB."plugins/{$p['file']}.plugin.php");
			}
		
			$class			= "plugin_{$p['file']}";
			$plugin			= new $class(&$icebb);
			
			if(version_compare(ICEBB_VERSION,$plugin->icebb_ver)<0)
			{
				continue;
			}
			
			$p['name']		= $plugin->name;
			$p['version']	= $plugin->version;
			$p['author']	= $plugin->author;
			$p['author_url']= $plugin->author_url;
			
			$plugins2[]		= $p;
		}
		
		$icebb->admin->html	= $this->html->show_main($plugins2);
	}

	function enable_plugin()
	{
		global $icebb,$db,$std;
		
		$db->insert('icebb_plugins',array(
			'filename'			=> $icebb->input['enable'],
		));
		
		$icebb->admin->redirect("Plugin enabled",$icebb->base_url."act=plugins");
	}
	
	function disable_plugin()
	{
		global $icebb,$db,$std;
		
		$db->query("DELETE FROM icebb_plugins WHERE filename='{$icebb->input['disable']}' LIMIT 1");
		
		$icebb->admin->redirect("Plugin disabled",$icebb->base_url."act=plugins");
	}
	
	function recache()
	{
		global $icebb,$db,$std;
	
		$db->query("SELECT * FROM icebb_plugins");
		while($p					= $db->fetch_row())
		{
			$plugins[$p['pid']]		= $p;
		}
		
		$std->recache($plugins,'plugins');
	}
}
?>