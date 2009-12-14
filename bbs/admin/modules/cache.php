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
// cache control admin module
// $Id: cache.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class cache
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('cache');		// I speak cache! I speak cash, too!
		$this->html					= $icebb->admin_skin->load_template('cache');	// okay, that was lame
		
		$this->caught_data			= array(
			'settings'				=> "Settings",
			'forums'				=> "Forums",
			'admin'					=> "Admin Settings",
			'smilies'				=> "Smilies",
			'bbcode'				=> "BBCode",
			'badwords'				=> "Bad Words",
			'stats'					=> "Board Statistics",
			'birthdays'				=> "Birthdays",
			'moderators'			=> "Moderators",
			'groups'				=> "Groups",
			'langs'					=> "Languages",
			'ranks'					=> "Ranks",
			'skins'					=> "Skins",
			'tasks'					=> "Tasks",
			'plugins'				=> "Plugins",
		);
		
		$icebb->admin->page_title	= $this->lang['manage_caches'];
		
		if(!empty($icebb->input['recache_selected']))
		{
			$icebb->input['func']		= 'recache';
		}
		
		switch($icebb->input['func'])
		{
			case 'recache':
				$this->recache();
				break;
			case 'view':
				$this->view_cache();
				break;
			default:
				$this->show_caches();
				break;
		}

		$icebb->admin->output();
	}
	
	function show_caches()
	{
		global $icebb,$config,$db,$std;

		$db->query("SELECT * FROM icebb_cache");
		while($c					= $db->fetch_row())
		{
			if(empty($this->caught_data[$c['name']]))
			{
				$c['desc']			= $c['name'];
			}
			else {
				$c['desc']			= $this->caught_data[$c['name']];
			}
		
			$caches[$c['desc']]		= $c;
		}
		
		ksort($caches);
		
		$icebb->admin->html	  		= $this->html->show_caches($caches);
	}
	
	function view_cache()
	{
		global $icebb,$config,$db,$std;
		
		$db->query("SELECT * FROM icebb_cache WHERE name='{$icebb->input['key']}' LIMIT 1");
		$c							= $db->fetch_row();
		
		if(empty($this->caught_data[$c['name']]))
		{
			$c['desc']				= $c['name'];
		}
		else {
			$c['desc']				= $this->caught_data[$c['name']];
		}

		$c['cache_data']			= unserialize($c['content']);

		foreach($c['cache_data'] as $fkey => $fval)
		{
			/*if(is_array($fval))
			{
				$fval				= "<pre>".wordwrap(print_r($fval,1),100,"<br />\n",1)."</pre>";
			}*/
		
			$data[$fkey]			= $fval;
		}
		
		$icebb->admin->html	   		= $this->html->show_cache($c,$data);
	}
	
	function recache()
	{
		global $icebb,$db,$config,$std;

		if(!empty($icebb->input['recache_selected']))
		{
			$db->query("SELECT * FROM icebb_cache");
			while($c					= $db->fetch_row())
			{
				if(empty($this->caught_data[$c['name']]))
				{
					$c['desc']			= $c['name'];
				}
				else {
					$c['desc']			= $this->caught_data[$c['name']];
				}
			
				$caches[$c['desc']]		= $c;
			}
			
			ksort($caches);
			
			foreach($caches as $cac)
			{
				$newcaches[$cac['id']]= $cac['name'];
			}
			
			if(count($icebb->input['cache']) > 0)
			{
				foreach($icebb->input['cache'] as $c => $bah)
				{
					$recache[$newcaches[$c]]= 1;
				}
			}
		}
		else if(!empty($icebb->input['key']))
		{
			$recache[$icebb->input['key']]= 1;
		}
		else {
			$icebb->admin->error($this->lang['nothing_to_do']);
		}

		if($recache['bbcode'])
		{
			// -- BBCODE -- //
			$bbcodeq			= $db->query("SELECT * FROM icebb_bbcode");
			while($b			= $db->fetch_row($bbcodeq))
			{
				$b['code']		= $b['code'];
				$b['replacement']= $b['replacement'];
				$b['php']		= $b['php'];
				$bbcode[]		= $b;
			}
			$std->recache($bbcode,'bbcode');
		}
		
		if($recache['smilies'])
		{
			// -- SMILIES -- //
			$smiliesq			= $db->query("SELECT * FROM icebb_smilies");
			while($s			= $db->fetch_row($smiliesq))
			{
				$s['code']		= $s['code'];
				$s['image']		= $s['image'];
				$smilies[$s['smiley_set']][]= $s;
			}
			$std->recache($smilies,'smilies');
		}
		
		if($recache['forums'])
		{
			// -- FORUMS -- //
			$db->query("SELECT * FROM icebb_forums");
			while($f			= $db->fetch_row())
			{
				$forums[$f['fid']]	= $f;
			}
			$std->recache($forums,'forums');
		}
		
		if($recache['moderators'])
		{
			// -- MODERATORS -- //
			$db->query("SELECT * FROM icebb_moderators");
			while($m			= $db->fetch_row())
			{
				foreach($m as $mkey => $mval)
				{
					$m[$mkey]	= wash_key(str_replace("&amp;","&",$mval));
				}
			
				$mods[$m['mid']]	= $m;
			}
			$std->recache($mods,'moderators');
		}

		if($recache['skins'])
		{	
			$db->query("SELECT * FROM icebb_skin_macros");
			while($m			= $db->fetch_row())
			{
				$macroso[$m['skin_id']][$m['string']]= $m['replacement'];
			}
			
			foreach($macroso as $skin => $ms)
			{
				$db->query("UPDATE icebb_skins SET skin_macro_cache='".addslashes(serialize($ms))."' WHERE skin_id='{$skin}'");
			}
		
			// -- SKINS -- //
			$db->query("SELECT * FROM icebb_skins");
			while($s			= $db->fetch_row())
			{
				foreach($s as $skey => $sval)
				{
					$s[$skey]	= str_replace("&amp;","&",$sval);
				}
			
				$skins[$s['skin_id']]= $s;
			}
			$std->recache($skins,'skins');
		}
		
		if($recache['langs'])
		{
			// -- LANGS -- //
			$db->query("SELECT * FROM icebb_langs");
			while($l			= $db->fetch_row())
			{
				foreach($l as $lkey => $lval)
				{
					$l[$lkey]	= wash_key(str_replace("&amp;","&",$lval));
				}
			
				$langs[$s['lang_short']]= $l;
				if($l['lang_is_default'])
				{
					$default	= $l['lang_short'];
				}
			}
			$langs['default']	= $default;
			$std->recache($langs,'langs');
		}
		
		if($recache['tasks'])
		{
			// -- TASKS -- //
			$db->query("SELECT * FROM icebb_tasks");
			while($t			= $db->fetch_row())
			{
				foreach($t as $tkey => $tval)
				{
					$t[$tkey]	= wash_key(str_replace("&amp;","&",$tval));
				}
			
				$tasks[]		= $t;
			}
			$std->recache($tasks,'tasks');
		}
		
		if($recache['groups'])
		{
			// -- GROUPS -- //
			$db->query("SELECT * FROM icebb_groups");
			while($g			= $db->fetch_row())
			{
				foreach($g as $gkey => $gval)
				{
					$g[$gkey]	= wash_key(str_replace("&amp;","&",$gval));
				}
			
				$groups[]		= $g;
			}
			$std->recache($groups,'groups');
		}
		
		if($recache['ranks'])
		{
			// -- RANKS -- //
			$db->query("SELECT * FROM icebb_ranks");
			while($r			= $db->fetch_row())
			{
				foreach($r as $rkey => $rval)
				{
					$r[$rkey]	= wash_key(str_replace("&amp;","&",$rval));
				}
			
				$ranks[]		= $r;
			}
			$std->recache($ranks,'ranks');
		}
		
		if($recache['birthdays'])
		{
			// -- BIRTHDAYS -- //
			$db->query("SELECT * FROM icebb_users");
			while($u			= $db->fetch_row())
			{
				if(!empty($u['birthdate']))
				{
					$bds['uid']	= $u['id'];
					$bds['username']= $u['username'];
					
					$u['birthdate']= @explode('.',@gmdate('m.d.Y',$u['birthdate']-$std->get_offset(OFFSET_SERVER)));
					$bd['bmonth']= $u['birthdate'][0];
					$bd['bday']= $u['birthdate'][1];
					$bd['byear']= $u['birthdate'][2];
					
					$bds['year']= $bd['byear'];
					
					$bdays[$bd['bmonth']][$bd['bday']][]= $bds;
				}
			}
			$std->recache($bdays,'birthdays');
		}
		
		if($recache['plugins'])
		{
			// -- PLUGINS -- //
			$pluginsq			= $db->query("SELECT * FROM icebb_plugins");
			while($p			= $db->fetch_row($pluginsq))
			{
				$plugins[]		= $p;
			}
			$std->recache($plugins,'plugins');
		}
		
		if($recache['settings'])
		{
			// -- SETTINGS -- //
			$settingsqO_O		= $db->query("SELECT * FROM icebb_settings");
			while($setting		= $db->fetch_row($settingsqO_O))
			{
				$settings[$setting['setting_key']]= $setting['setting_value'];
			}
			$std->recache($settings,'settings');
		}
		
		if($recache['stats'])
		{
			// -- STATS -- //
			$cache_result				= $db->fetch_result("SELECT COUNT(*) as count FROM icebb_users");
			$cache_result2				= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
			$cache_result3				= $db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
			$cache_result31				= $db->fetch_result("SELECT COUNT(*) as topics FROM icebb_topics");
			$cache_result32				= $db->fetch_result("SELECT COUNT(*) as replies FROM icebb_posts WHERE pis_firstpost!=1");
			$stocache['posts']			= $cache_result3['posts'];
			$stocache['topics']			= $cache_result31['topics'];
			$stocache['replies']		= $cache_result32['replies'];
			$stocache['user_count']		= $cache_result['count'];
			$stocache['user_newest']	= $cache_result2;
			$std->recache($stocache,'stats');
		}

		$icebb->admin->redirect($this->lang['recached'],"{$icebb->base_url}act=cache");
	}
}
?>
