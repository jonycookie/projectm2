<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3.1
//******************************************************//
// group control center module
// $Id: groupcp.php 501 2006-09-30 03:03:36Z mutantmonkey0 $
//******************************************************//

class groupcp
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$std->error("Disabled");
		
		$this->ucc_html					= $icebb->skin->load_template('usercp');
		$this->ucc_lang					= $std->learn_language('usercp');
		$this->html						= $icebb->skin->load_template('groupcp');
		$this->lang						= $std->learn_language('groupcp');
		
		$icebb->nav[]					= "<a href='{$icebb->base_url}act=ucp'>{$this->ucc_lang['title']}</a>";
		$icebb->nav[]					= "<a href='{$icebb->base_url}act=groups'>{$this->lang['title']}</a>";
		
		if($icebb->user['id']=='0')
		{
			$std->error($this->lang['unauthorized'],1);
		}
		
		switch($icebb->input['func'])
		{
			case 'view':
				$this->view();
				break;
			case 'remove_mod':
				$this->remove_mod();
				break;
			case 'join':
				$this->join_group();
				break;
			case 'kick':
				$this->kick();
				break;
			case 'leave':
				$this->leave();
				break;
			default:
				$this->main();
				break;
		}
			
		$this->output2			= $this->output;
		$this->output			= $this->ucc_html->layout($this->output2);
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function main()
	{
		global $icebb,$config,$db,$std;
		
		$db->query("SELECT * FROM icebb_groups ORDER BY gid ASC");
		
		$this->output		   .= $this->html->main_top();
		while($g				= $db->fetch_row())
		{
			switch($g['g_status'])
			{
				case '1':
					$status 	= $this->lang['perm_open'];
					$show		= true;
					break;
				case '2':
					$status		= $this->lang['perm_closed'];
					$show		= true;
					break;
				default:
					$status		= $this->lang['perm_hidden'];
					$show		= false;
					break;
			}
			
			if($show || $icebb->user['gid'] == $g['gid'] || $icebb->user['g_is_admin']=='1')
			{
				$this->output	.= $this->html->main_row($g['gid'],$g['g_title'],$status,$g['g_prefix'],$g['g_suffix']);
			}
		}
		
		$this->output		.= $this->html->main_bottom();
	}
	
	function remove_mod()
	{
		global $icebb,$config,$db,$std;
		
		if(empty($icebb->input['gid']))
		{
			$std->error($this->lang['err_no_gid']);
		}
		
		$groupq = $db->query("SELECT * FROM icebb_groups WHERE gid='{$icebb->input['gid']}' LIMIT 1");
		
		if($db->get_num_rows($groupq) <= 0)
		{
			$std->error($this->lang['err_no_such_group']);
		}
		
		$g = $db->fetch_row($groupq);
		
		$group_mods = explode(',',$g['g_mods']);
		
		if(/*!in_array($icebb->user['id'],$group_mods) || */$icebb->user['g_is_admin']!=1)
		{
			$std->error($this->lang['err_no_permission']);
		}
		
		if(empty($icebb->input['uid']))
		{
			$std->error($this->lang['err_del_no_user']);
		}
		
		$userq = $db->query("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}' LIMIT 1");
		
		if($db->get_num_rows($userq) <= 0)
		{
			$std->error($this->lang['err_del_no_such_user']);
		}
		
		$u = $db->fetch_row($userq);
		
		if(!in_array($u['id'],$group_mods))
		{
			$std->error($this->lang['err_del_user_isnt_mod']);
		}
		
		foreach($group_mods as $gm)
		{
			if($gm != $icebb->input['uid'])
			{
				$new_group_mods[] = $gm;
			}
		}
		
		foreach($new_group_mods as $ngm)
		{
			if(isset($newgm))
			{
				$newgm .= ",{$ngm}";
			}
			else
			{
				$newgm = $ngm;
			}
		}
		
		$db->query("UPDATE icebb_groups SET g_mods='{$newgm}' WHERE gid='{$icebb->input['gid']}' LIMIT 1");
		
		$std->bouncy_bouncy($this->lang['is_deleted_mod'],$icebb->base_url."act=groups&func=view&gid={$icebb->input['gid']}");
	}
	
	function join_group()
	{
		global $icebb,$db,$std;
		
		if(empty($icebb->input['gid']))
		{
			$std->error($this->lang['err_no_gid']);
		}
		
		$db->query("SELECT * FROM icebb_groups WHERE gid='{$icebb->input['gid']}' LIMIT 1");
		if($db->get_num_rows()<=0)
		{
			$std->error($this->lang['err_no_such_group']);
		}
		
		$g				= $db->fetch_row();
		
		if($g['g_status']!= '1' || $icebb->user['user_group']==1)
		{
			$std->error($this->lang['cannot_join']);
		}
		
		$db->query("UPDATE icebb_users SET user_group=".intval($g['gid'])." WHERE id=".intval($icebb->user['id']));
		
		$std->bouncy_bouncy($this->lang['joined_group'],$icebb->base_url."act=groups&func=view&gid={$icebb->input['gid']}");
	}
	
	function kick()
	{
		global $icebb,$config,$db,$std;
		
		if(empty($icebb->input['gid']))
		{
			$std->error($this->lang['err_no_gid']);
		}
		
		$groupq = $db->query("SELECT * FROM icebb_groups WHERE gid='{$icebb->input['gid']}' LIMIT 1");
		
		if($db->get_num_rows($groupq) <= 0)
		{
			$std->error($this->lang['err_no_such_group']);
		}
		
		$g = $db->fetch_row($groupq);
		
		$group_mods = explode(',',$g['g_mods']);
		
		if(!in_array($icebb->user['id'],$group_mods) || $icebb->user['g_is_admin']!=1)
		{
			$std->error($this->lang['err_no_permission']);
		}
		
		if(empty($icebb->input['uid']))
		{
			$std->error($this->lang['err_del_no_user']);
		}
		
		$userq = $db->query("SELECT * FROM icebb_users WHERE id='{$icebb->input['uid']}' LIMIT 1");
		
		if($db->get_num_rows($userq) <= 0)
		{
			$std->error($this->lang['err_del_no_such_user']);
		}
		
		$u = $db->fetch_row($userq);
		
		if($u['user_group']!=$icebb->input['gid'])
		{
			$std->error($this->lang['err_kick_not_in_group']);
		}
		
		if($u['user_group']==2)
		{
			$db->query("UPDATE icebb_users SET user_group=5 WHERE id='{$icebb->input['uid']}' LIMIT 1");
		}
		else if($u['user_group']==5)
		{
			$std->error($lang['err_kick_in_banned']);
		}
		else if($u['user_group']==1)
		{
			$std->error($lang['err_kick_in_admin']);
		}
		else
		{
			$db->query("UPDATE icebb_users SET user_group=2 WHERE id='{$icebb->input['uid']}' LIMIT 1");
		}
		
		$std->bouncy_bouncy($this->lang['is_kicked'],$icebb->base_url."act=groups&func=view&gid={$icebb->input['gid']}");
	}
	
	function leave()
	{
		global $icebb,$db,$std;
		
		$db->query("UPDATE icebb_users SET user_group=2 WHERE id=".intval($icebb->user['id']));
		
		$std->bouncy_bouncy($this->lang['left_group'],$icebb->base_url."act=groups&func=view&gid=2");
	}
	
	function view()
	{
		global $icebb,$config,$db,$std;
		
		if(empty($icebb->input['gid']))
		{
			$std->error($this->lang['err_no_gid']);
		}
		
		$db->query("SELECT * FROM icebb_groups WHERE gid='{$icebb->input['gid']}' LIMIT 1");
		if($db->get_num_rows() <= 0)
		{
			$std->error($this->lang['err_no_such_group']);
		}
		
		$g							= $db->fetch_row($groupq);
		
		$icebb->nav[]				= $g['g_title'];
		
		$group_mods					= explode(',',$g['g_mods']);
		
		if($g['g_status']==3 && (!in_array($icebb->user['id'],$group_mods) || $icebb->user['g_is_admin']!=1 || $icebb->user['user_group']!=$g['gid']))
		{
			$std->error($this->lang['err_no_permission']);
		}
		
		$info['admin']				= $g['g_is_admin']				? $this->lang['yes']	: $this->lang['no'];
		$info['mod']				= $g['g_is_mod']				? $this->lang['yes']	: $this->lang['no'];
		$info['access']				= $g['g_view_board']			? $this->lang['yes']	: $this->lang['no'];
		$info['access_offline']		= $g['g_view_offline_board']	? $this->lang['yes']	: $this->lang['no'];
		
		switch($g['g_status'])
		{
			case '1':
				$info['status']		= $this->lang['perm_open'];
				break;
			case '2':
				$info['status']		= $this->lang['perm_closed'];
				break;
			default:
				$info['status']		= $this->lang['perm_hidden'];
				break;
		}
		
		if(empty($g['g_desc']))
		{
			$g['g_desc']			= "<em>{$this->lang['no_info']}</em>";
		}

		$uq							= $db->query("SELECT * FROM icebb_users");

		while($u					= $db->fetch_row($uq))
		{
			if(!in_array($icebb->user['id'],$group_mods) || $icebb->user['g_is_admin']!=1)
			{
				$del_mod_link		= "";
				$kick_link			= "";
			}
			else
			{
				$del_mod_link		= $this->html->del_mod_link($u['id']);
				$kick_link			= $this->html->kick_link($u['id']);
			}
		
			if(in_array($u['id'],$group_mods))
			{
				$gmods_html		   .= $this->html->mod_list_row($u,$del_mod_link);
			}
			else if($u['user_group']== $icebb->input['gid'])
			{
				$gmembers_html	   .= $this->html->user_list_row($u,$kick_link);
			}
			
			if($icebb->user['id']	== $icebb->input['gid'] && $icebb->input['gid']>5)
			{
				$info['lg']			= $this->html->leave_group();
			}
		}
		
		$this->output			   .= $this->html->view_top($g,$info,$gmods_html,$gmembers_html);
	}
}
?>