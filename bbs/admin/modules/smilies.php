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
// smiley admin module
// $Id: smilies.php 990 2007-08-13 01:20:39Z mutantmonkey0 $
//******************************************************//

class smilies
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang							= $icebb->admin->learn_language('smilies');
		$this->html							= $icebb->admin_skin->load_template('smilies');
		
		$icebb->admin->page_title			= $this->lang['smilies'];
			
		$skinq								= $db->query("SELECT * FROM icebb_skins WHERE skin_is_default=1 LIMIT 1");
		$skindata							= $db->fetch_row($skinq);
		$this->default_smiley_set			= $skindata['smiley_set'];
		
		switch($icebb->input['func'])
		{
			case 'delete':
				$this->delete();
				break;
			case 'add':
				$this->add();
				break;
			case 'manage':
				$this->manage();
				break;
			case 'add_folder':
				$this->add_folder();
				break;
			default:
				$this->main();
				break;
		}
		
		$icebb->admin->output();
	}
	
	function delete()
	{
		global $icebb,$config,$db,$std;
		
		$db->query("DELETE FROM icebb_smilies WHERE smiley_set='{$icebb->input['set']}'");
		
		if(@ini_get('safe_mode'))
		{
			$icebb->admin->redirect("Smiley set and smilies deleted from database. PHP Safe Mode has been detected, so you will have to delete {$config['dir']}/smilies/{$icebb->input['set']}/ yourself.");
		}
		else {
			if(rmdir($icebb->settings['board_path']."smilies/{$icebb->input['set']}/") == true)
			{
				$icebb->admin->redirect("Smiley set \"{$icebb->input['set']}}\" deleted.",$icebb->base_url."act=smilies");
			}
			else {
				$icebb->admin->error("Directory could not be deleted, please delete {$icebb->settings['board_path']}/smilies/{$icebb->input['set']}/ yourself.");
			}
		}
	}
	
	function manage()
	{
		global $icebb,$config,$db,$std,$cache_func;
		
		if(!isset($icebb->input['submit']))
		{
			$icebb->admin->html .= $icebb->admin_skin->start_table("Smilies ({$icebb->input['set']})");
			
			$smilies			= array();
			$dh					= opendir("{$icebb->settings['board_path']}smilies/{$icebb->input['set']}");
			while(($file = @readdir($dh)) !== false)
			{
				if($file=='.' || $file=='..' || $file=='.svn' || $file=='Thumbs.db' || $file=='index.html')
				{
					continue;
				}
				
				$smilies[]		= $file;
			}
			@closedir($dh);
			sort($smilies);
			
			// get smilies in database
			$da_smilies			= array();
			$db->query("SELECT * FROM icebb_smilies WHERE smiley_set='{$icebb->input['set']}' ORDER BY id ASC");
			while($s			= $db->fetch_row())
			{
				$s['filename']	= !empty($s['image']) ? $s['image'] : 'blank.gif';
				
				$da_smilies[]	= $s;
			}
			
			$icebb->admin->html	= $this->html->show_set($da_smilies,$smilies);
		}
		else {
			foreach($icebb->input['smiley_code'] as $id => $code)
			{
				$img				= $icebb->input['image'][$id];
				$clickable			= $icebb->input['clickable'][$id];
				
				if(empty($code) || empty($img))
				{
					continue;
				}
				
				if($id == 'new')
				{
					$db->insert('icebb_smilies',array(
						'smiley_set'=> $icebb->input['set'],
						'code'		=> $code,
						'image'		=> $img,
						'clickable'	=> $clickable,
					));
				}
				else {
					$db->query("UPDATE icebb_smilies SET code='{$code}',image='{$img}',clickable='{$clickable}' WHERE smiley_set='{$icebb->input['set']}' AND id='{$id}'");
				}
			}

			$this->rebuild_smilies();
			
			$icebb->admin->redirect("Smilies updated","{$icebb->base_url}act=smilies&func=manage&set={$icebb->input['set']}");
		}
	}
	
	function add_folder()
	{
	
		global $icebb,$config,$db,$std,$cache_func;
		
		if(!isset($icebb->input['submit']))
		{
			$icebb->admin->html	.= $icebb->admin_skin->start_form('admin.php',array('s'=>$icebb->adsess['sessid'],'act'=>'smilies','func'=>'add_folder','submit'=>'1'),'post'," name='adminfrm'");
			$icebb->admin->html .= $icebb->admin_skin->start_table("Add a smiley set");
			$icebb->admin->html	.= $icebb->admin_skin->table_row(array("<b>Set Name</b>",$icebb->admin_skin->form_input('name',$icebb->input['name'])));
			$icebb->admin->html .= $icebb->admin_skin->end_form("Add Smiley Set");
			$icebb->admin->html .= $icebb->admin_skin->end_table();
		}
		else
		{
			mkdir("{$icebb->settings['board_path']}smilies/{$icebb->input['name']}", 0700);
			$this->rebuild_smilies();	
			$icebb->admin->redirect("Smiley set added","{$icebb->base_url}act=smilies&func=manage&set={$icebb->input['name']}");
		}
		
		$icebb->admin->html			= $this->html->global->header().$icebb->admin->html.$this->html->global->footer();
	}
	
	function main()
	{
		global $icebb,$config,$db,$std,$cache_func;
		
		if(!empty($icebb->input['default_smilies']))
		{
			$db->query("UPDATE icebb_skins SET smiley_set='{$icebb->input['default_smilies']}'");// WHERE skin_id=".intval($icebb->input['default_skin']));
			$this->skins_recache();
			
			$icebb->admin->redirect($this->lang['smilies_set_as_default'],"{$icebb->base_url}act=smilies");
		}
		
		$db->query("SELECT DISTINCT smiley_set FROM icebb_smilies ORDER BY id ASC");
		while($r				= $db->fetch_row($smilies_query))
		{
			$smilies[]			= $r;
		}
		
		$icebb->admin->html		= $this->html->show_main($smilies,$this->default_smiley_set);
	}
	
	function skins_recache()
	{
		global $icebb,$db,$std;
	
		$db->query("SELECT * FROM icebb_skins");
		while($s			= $db->fetch_row())
		{
			foreach($s as $skey => $sval)
			{
				$s[$skey]	= wash_key(str_replace("&amp;","&",$sval));
			}
		
			$skins[$s['skin_id']]= $s;
		}
		$std->recache($skins,'skins');
	}
	
	function rebuild_smilies()
	{
		global $icebb,$db,$std;
	
		$smiliesq				= $db->query("SELECT * FROM icebb_smilies");
		while($s				= $db->fetch_row($smiliesq))
		{
			$s['code']			= $s['code'];
			$s['image']			= $s['image'];
			$data[$s['smiley_set']][]= $s;
		}
		
		$std->recache($data,'smilies');
	}
}
?>
