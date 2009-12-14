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
// miscellaneous features module
// $Id: stats.php 303 2005-07-31 03:30:41Z mutantmonkey $
//******************************************************//

class misc
{
	function run()
	{
		global $icebb,$db,$std;
		
		$this->lang				= $std->learn_language('stats');
		$this->html				= $icebb->skin->load_template('stats');
		
		switch($icebb->input['func'])
		{
			case 'leaders':
				$this->leaders();
				break;
			case 'spellcheck':
				$this->spellcheck();
				break;
			case 'userxml':
				$this->userxml();
				break;
			default:
				break;
		}
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function leaders()
	{
		global $icebb,$db,$std;

		$db->query("SELECT u.id,u.username FROM icebb_users AS u INNER JOIN icebb_groups AS g ON u.user_group=g.gid WHERE g.g_is_admin='1' ORDER BY u.username ASC");
		while($u = $db->fetch_row())
		{
			$data['admins'][] = $u;
		}
		
		$db->query("SELECT u.id,u.username FROM icebb_users AS u INNER JOIN icebb_groups AS g ON u.user_group=g.gid WHERE g.g_is_mod='1' AND g.g_is_admin='0' ORDER BY u.username ASC");
		while($u = $db->fetch_row())
		{
			$data['global_moderators'][] = $u;
		}
		
		if(count($icebb->cache['moderators']) <= 0)
		{
			$mods = array();
		}
		else {
			foreach($icebb->cache['moderators'] as $mod)
			{
				if($mod['m_is_group'] != 1)
				{
					$mods[$mod['muserid']]['forums'][] = intval($mod['mforum']);
					$mods[$mod['muserid']]['username'] = $mod['muser'];
				}
				else {
					$mod_groups[$mod['mgroup_id']]['forums'][] = intval($mod['mforum']);
				}
			}
		}
		
		if(count($mod_groups) > 0)
		{
			foreach($mod_groups as $groupid => $mod_data)
			{
				$db->query("SELECT id,username FROM icebb_users WHERE user_group='{$groupid}'");
				if($db->get_num_rows() > 0)
				{
					while($u = $db->fetch_row())
					{
						if(is_array($mods[$u['id']]['forums']))
						{
							array_merge($mods[$u['id']]['forums'],$mod_data['forums']);
						}
						else {
							$mods[$u['id']]['forums']	= $mod_data['forums'];
						}
						$mods[$u['id']]['username']		= $u['username'];
					}
				}
			}
		}
		
		if(count($mods) > 0)
		{
			foreach($mods as $userid => $mod_data)
			{
				foreach($mod_data['forums'] as $forum)
				{
					$forums[] = array('name'=>$icebb->cache['forums'][$forum]['name'],'id'=>$forum);
				}
				
				$data['moderators'][] = array(
						'user_id'	=> $userid,
						'username'	=> $mod_data['username'],
						'forums'	=> $forums,
					);
				unset($forums);
			}
		}
		else {
			$data['moderators'] = array();
		}
		
		$this->output			= $this->html->leaders($data);
	}

	function spellcheck()
	{
		global $icebb,$db,$std;
		
		if(function_exists('pspell_new'))
		{
			$pspell				= @pspell_new('en');
		}
		
		if(!$pspell)
		{
			echo 'false';
			exit();
		}
		else {
			if(!empty($icebb->input['suggest']))
			{
				$suggestions	= pspell_suggest($pspell,$icebb->input['suggest']);
				
				echo implode(',',$suggestions);
				exit();
			}
			else {
				$typos			= array();
				$words			= preg_split('/[\W]+?/',$icebb->input['string']);
				foreach($words as $word)
				{
					if(!pspell_check($pspell,$word))
					{
						$typos[]= $word;
					}
				}
				
				if($icebb->input['xmlhttp']=='1')
				{
					foreach($words as $i => $w)
					{
						if(in_array($w,$typos))
						{
							$ret.= "<span id='word-{$i}' class='misspelled' onclick=\"return spell_check_suggest(this,'{$w}','{$i}')\">{$w} </span>";
						}
						else {
							$ret.= "<span id='word-{$i}'>{$w} </span>";
						}
					}
					
					echo $ret;
					exit();
				}
				else {
					echo implode(',',$typos);
					exit();
				}
			}
		}
	}
	
	function userxml()
	{
		global $icebb,$db,$std;
		
		$db->query("SELECT * FROM icebb_users WHERE id='{$icebb->input['id']}' LIMIT 1");
		$u					= $db->fetch_row();
		unset($u['password']);
		unset($u['pass_salt']);
		unset($u['ip']);
		unset($u['email']);
		unset($u['warn_level']);
		unset($u['disable_pm']);
		unset($u['disable_post']);
		unset($u['banned_from']);
		unset($u['temp_ban']);
		
		foreach($u as $k => $v)
		{
			$uxml		   .= "\t<user key='{$k}'>{$v}</user>\n";
		}
		
		@header("Content-type: application/xml");
		echo "<?xml version='1.0'?>\n<icebb>\n{$uxml}\n</icebb>";
		exit();
	}
}
?>