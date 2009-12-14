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
// settings admin module
// $Id: settings.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class settings
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('home');
		$this->html					= $icebb->admin_skin->load_template('settings');
		
		$icebb->admin->page_title	= "Settings";
		
		if(isset($icebb->input['group']))
		{
			$this->show_settings_in_group();
		}
		else {
			$this->show_groups();
		}

		$icebb->admin->output();
	}
	
	function show_settings_in_group()
	{
		global $icebb,$config,$db,$std;
		
		if(isset($icebb->input['save']))
		{
			$settingsq				= $db->query("SELECT * FROM icebb_settings WHERE setting_group='{$icebb->input['group']}'");
			while($setting			= $db->fetch_row($settingsq))
			{
				if(isset($icebb->input[$setting['setting_key']]))
				{
					if(!is_array($this->error_msg))
					{
						$value = $db->escape_string($icebb->input[$setting['setting_key']]);
						
						if($setting['setting_key']=='validate_email' && $value==0 && $setting['setting_value']==1)
						{
							$db->query("UPDATE icebb_users SET user_group=2 WHERE user_group=3");
							$db->query("TRUNCATE TABLE icebb_users_validating");
						}
						
						$db->query("UPDATE icebb_settings SET setting_value='{$value}' WHERE setting_id='{$setting['setting_id']}' LIMIT 1");
					}
				}
			}
			
			$this->recache_settings();
			
			if(!is_array($this->error_msg))
			{
				$icebb->admin->redirect("Settings Updated","{$icebb->base_url}&act=settings&group={$icebb->input['group']}");
			}
		}
		
		$group						= $db->fetch_result("SELECT * FROM icebb_settings_sections WHERE st_id='{$icebb->input['group']}' LIMIT 1");
		
		if(is_array($this->error_msg))
		{
			$icebb->admin->html	   .= "<div class='border'><h4>Error</h4>";
			
			foreach($this->error_msg as $err)
			{
				$icebb->admin->html.= "{$err}<br />";
			}

			$icebb->admin->html	   .= "</div>";
		}
		
		$settings_in_group			= $db->query("SELECT * FROM icebb_settings WHERE setting_group='{$group['st_id']}' ORDER BY setting_sort");	
		
		if($db->get_num_rows()<=0)
		{
			$icebb->admin->html	   .= $icebb->admin_skin->table_row("There are no settings in this group");
			$icebb->admin->html	   .= $icebb->admin_skin->end_table();
			return;
		}
		
		while($s					= $db->fetch_row($settings_in_group))
		{
			$s['setting_desc']		= nl2br($s['setting_desc']);
		
			if(empty($s['setting_value']) && $s['setting_value']!='0')
			{
				$s['setting_value']	= $s['setting_default'];
			}
		
			switch($s['setting_type'])
			{
				case 'yes_no':
					$form				= $icebb->admin_skin->form_yes_no($s['setting_key'],$s['setting_value']);
					break;
				case 'textarea':
					$form				= $icebb->admin_skin->form_textarea($s['setting_key'],$s['setting_value']);
					break;
				case 'dropdown':
					$options			= array();
					$options1			= explode("\n",$s['setting_php']);
					foreach($options1 as $opt)
					{
						$opt			= explode(':',$opt);
						$options[]		= $opt;
					}
					
					$form				= $icebb->admin_skin->form_dropdown($s['setting_key'],$options,$s['setting_value']);
					break;
				case 'forum_select':
					$icebb->user['g_permgroup']=1;
					$forumlist			= $std->get_forum_listing();
					$forumslist			= $this->forum_list_children($forumlist,'0');
					
					$form				= $icebb->admin_skin->form_dropdown($s['setting_key'],$forumslist,$s['setting_value']);
					break;
				default:
					$form				= $icebb->admin_skin->form_input($s['setting_key'],$s['setting_value']);
					break;
			}
		
			$s['input']				= $form;
			$settingos[]			= $s;
		}

		$icebb->admin->html			= $this->html->display($group,$settingos);
	}
	
	function recache_settings()
	{
		global $icebb,$db,$config,$std;

		$settingsqO_O		= $db->query("SELECT * FROM icebb_settings");
		while($setting		= $db->fetch_row($settingsqO_O))
		{
			$icebb->settings[$setting['setting_key']]= $setting['setting_value'];
		}
		
		$std->recache($icebb->settings,'settings');
	}
	
	function forum_list_children($list,$fn)
	{
		global $icebb,$db,$std;
		
		$c						= 0;
		
		if(is_array($list))
		{
			foreach($list as $f)
			{
				$l[]			= array($f['fid'],$f['name']);
				$la				= $this->forum_list_children($f['children'],$f['fid']);
				if(is_array($la))
				{
					foreach($la as $lz)
					{
						$l[]	= $lz;
					}
				}

				$c++;
			}
		}
		
		return $l;
	}
}
?>
