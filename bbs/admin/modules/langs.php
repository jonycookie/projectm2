<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.kenkarpg.info // 1.0 Beta 6
//******************************************************//
// language control admin module
// $Id: langs.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class langs
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('langs');
		$this->html					= $icebb->admin_skin->load_template('langs');
		
		$icebb->admin->page_title	= $this->lang['langs'];
		
		switch($icebb->input['code'])
		{
			case 'enable':
				$this->enable_lang();
				break;
			case 'disable':
				$this->disable_lang();
				break;
			case 'set_as_default':
				$this->set_as_default();
				break;
			default:
				$this->lang_listing();
				break;
		}

		$icebb->admin->output();
	}
	
	function lang_listing()
	{
		global $icebb,$config,$db,$std;
	
		if(!empty($icebb->input['default_lang']))
		{
			$db->query("UPDATE icebb_langs SET lang_is_default=0");
			$db->query("UPDATE icebb_langs SET lang_is_default=1 WHERE lang_id=".intval($icebb->input['default_lang']));
			$this->langs_recache();
			
			$icebb->admin->redirect($this->lang['lang_set_as_default'],"{$icebb->base_url}act=langs");
		}
	
		$icebb->admin->html			= $icebb->admin_skin->start_table("Languages");
		
		$db->query("SELECT * FROM icebb_langs");
		while($l					= $db->fetch_row())
		{
			$l['enabled']			= true;
			$langs[]				= $l;
			$installed[]			= $l['lang_short'];
		}
		
		$dh							= @opendir(PATH_TO_ICEBB.'langs');
		while(false !== ($dir = @readdir($dh)))
		{
			if(in_array($dir,$installed))
			{
				continue;
			}
			
			$could_include			= @include(PATH_TO_ICEBB."langs/{$dir}/lang_info.php");
			if($could_include)
			{
				$lang2['lang_short']= $dir;
				$lang2['lang_name']	= $lang['name'];
				$langs[]			= $lang2;
			}
		}
		@closedir($dh);
		
		$icebb->admin->html			= $this->html->show_main($langs);
	}
	
	function enable_lang()
	{
		global $icebb,$db,$std;

		$dir					= basename($icebb->input['lang']);

		$could_include			= @include(PATH_TO_ICEBB."langs/{$dir}/lang_info.php");
		if(!$could_include)
		{
			$icebb->admin->error($this->lang['cannot_load_info']);
		}

		$db->insert('icebb_langs',array(
			'lang_short'		=> $dir,
			'lang_name'			=> $lang['name'],
			'lang_charset'		=> $lang['charset'],
			'lang_is_default'	=> '0',
		));
		$this->langs_recache();

		$icebb->admin->redirect($this->lang['lang_enabled'],"{$icebb->base_url}act=langs");
	}
	
	function disable_lang()
	{
		global $icebb,$db,$std;

		$db->query("DELETE FROM icebb_langs WHERE lang_id='{$icebb->input['langid']}'");
		$this->langs_recache();
		
		$icebb->admin->redirect($this->lang['lang_disabled'],"{$icebb->base_url}act=langs");
	}
	
	function set_as_default()
	{
		global $icebb,$db,$std;
		
		$lang_info		= $db->fetch_result("SELECT * FROM icebb_langs WHERE lang_id='{$icebb->input['langid']}'");
		$default_lang	= $db->fetch_result("SELECT * FROM icebb_langs WHERE lang_is_default=1");
		
		if($lang_info['result_num_rows_returned'] >= 1)
		{
			if($lang_info['lang_id'] == $default_lang['lang_id'])
			{
				$icebb->admin->error($icebb->lang['no_disable_default_lang']);
			}
			
			$db->query("UPDATE icebb_users SET langid='{$default_lang['lang_short']}' WHERE langid='{$lang_info['lang_short']}'");
			$db->query("DELETE FROM icebb_langs WHERE lang_id='{$icebb->input['langid']}'");
			$this->langs_recache();
		
			$icebb->admin->redirect($this->lang['lang_disabled'],"{$icebb->base_url}act=langs");
		}
		else {
			$icebb->admin->error($icebb->lang['no_such_lang']);
		}
	}
	
	function langs_recache()
	{
		global $icebb,$db,$config,$std;
	
		$db->query("SELECT * FROM icebb_langs");
		while($l			= $db->fetch_row())
		{
			foreach($l as $lkey => $lval)
			{
				$l[$lkey]	= wash_key(str_replace("&amp;","&",$lval));
			}
		
			$langs[$l['lang_id']]= $l;
			
			if($l['lang_is_default'])
			{
				$default	= $l['lang_short'];
			}
		}
		$langs['default']	= $default;
		$std->recache($langs,'langs');
	}
}
?>