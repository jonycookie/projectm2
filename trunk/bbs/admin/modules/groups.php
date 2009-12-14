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
// group admin module
// $Id: groups.php 267 2005-07-29 15:03:12Z mutantmonkey $
//******************************************************//

class groups
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
 		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title	= "Manage Groups";
		
		switch($icebb->input['func'])
		{
			case 'new':
				$this->new_group();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'del':
				$this->delete();
				// NICHT DA!
				// Warum schreibst deutsch MutantMonkey?
				// Ich weiss nicht :P 
				break;
			case 'permgroups':
				$this->permgroups();
				break;
			case 'ranks':
				$this->ranks();
				break;
			default:
				$this->show_list();
				break;
		}

		$icebb->admin->html					= $this->html->header().$icebb->admin->html.$this->html->footer();

		$icebb->admin->output();
	}
	
	function show_list()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->admin_skin->table_titles[]		= array("{none}",'60%');
		$icebb->admin_skin->table_titles[]		= array("{none}",'40%');

		$icebb->admin->html.= $icebb->admin_skin->start_table("Groups"); 
	
		$tforum				= $db->query("SELECT * FROM icebb_groups");
		while($r			= $db->fetch_row($tforum))
		{
			if(in_array($r['gid'],array(3,4))) // don't show "Guests" and "Validating members". By request of David
			{
				continue;
			}
			
			$thisrow		= array();
			$thisrow[0]		= "<b>{$r['g_prefix']}{$r['g_title']}{$r['g_suffix']}</b>";
			$thisrow[1]		= "<div style='text-align:right'><a href='{$icebb->base_url}act=groups&func=edit&gid={$r['gid']}'>Edit</a>";
			$thisrow[1] .= $r['gid'] > 5 ? " &middot; <a href='{$icebb->base_url}act=groups&func=del&amp;gid={$r['gid']}'>Remove</a></div>" : "</div>"; // Hiding the delete link for the default groups
		
			$icebb->admin->html.= $icebb->admin_skin->table_row($thisrow);
		}
		
		$icebb->admin->html.= $icebb->admin_skin->end_table();
		
		$icebb->admin->html		   .= "<div class='buttonrow'><input type='button' value='New Group' onclick=\"window.location='{$icebb->base_url}act=groups&amp;func=new'\" class='button' /></div></form>";
	}
	
	function show_search_results()
	{
		global $icebb,$config,$db,$std;

		if($icebb->input['search_how']=='sw')
		{
			$where							= " LIKE '{$icebb->input['username']}%'";
			$wheretype_english				= "%d users have usernames that start with %s";
		}
		else if($icebb->input['search_how']=='ew')
		{
			$where							= " LIKE '%{$icebb->input['username']}'";
			$wheretype_english				= "%d users have usernames that end with %s";
		}
		else if($icebb->input['search_how']=='co')
		{
			$where							= " LIKE '%{$icebb->input['username']}%'";
			$wheretype_english				= "%d users have usernames that contain with %s";
		}
		else if($icebb->input['search_how']=='is')
		{
			$where							= "='{$icebb->input['username']}'";
			$wheretype_english				= "%d users have usernames that are %s";
		}

		$db->query("SELECT COUNT(*) as count FROM icebb_users WHERE username{$where}");
		$count								= $db->fetch_row();

		$db->query("SELECT u.*,g.g_title FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE username{$where}");
		while($u							= $db->fetch_row())
		{
			$thisrow[]						= "<div style='text-align:center'><a href='{$icebb->base_url}act=users&func=edit&uid={$u['id']}&search_how={$icebb->input['search_how']}&searchq={$icebb->input['username']}'><img src='{$u['avatar']}' border='0' alt='' width='64' height='64' /><br />{$u['username']}</a><br /><span style='font-size:70%'>{$u['title']}</span></div>";
			$ucount++;

			if($ucount==4 || $ucount==$count['count'])
			{
				$result_html			   .= $icebb->admin_skin->table_row($thisrow,'col1'," valign='top'");
				$thisrow					= array();
				$ucount=0;
			}
		}
		
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'25%');
		
		$icebb->admin->html			       .= $icebb->admin_skin->start_table(sprintf($wheretype_english,$count['count'],$icebb->input['username'])); 

		$icebb->admin->html				   .= $result_html;

		$icebb->admin->html				   .= $icebb->admin_skin->end_table();
	}

	function edit()
	{
		global $icebb,$config,$db,$std;

		if(isset($icebb->input['submit']))
		{
			if(!get_magic_quotes_gpc())
			{
				$icebb->input['suffix']		= addslashes($icebb->input['suffix']);
				$icebb->input['prefix']		= addslashes($icebb->input['prefix']);
			}
			
			$icebb->input['suffix']			= html_entity_decode($icebb->input['suffix'],ENT_QUOTES);
			$icebb->input['prefix']			= html_entity_decode($icebb->input['prefix'],ENT_QUOTES);
			
			$icebb->input['suffix']			= $db->escape_string($icebb->input['suffix']);
			$icebb->input['prefix']			= $db->escape_string($icebb->input['prefix']);
			$icebb->input['flood_control']	= intval($icebb->input['flood_control']);
		
			if(is_array($icebb->input['permgroup']))
			{
				foreach($icebb->input['permgroup'] as $pg)
				{
					$permgroups_a[]			= $pg;
				}
				
				$permgroups					= implode($permgroups_a);
			}
			else {
				$permgroups					= $icebb->input['permgroup'];
			}
			
			//$db->query("UPDATE icebb_groups SET g_flood_control='{$icebb->input['flood_control']}',g_suffix='{$icebb->input['suffix']}',g_prefix='{$icebb->input['prefix']}',g_status='{$icebb->input['status']}',g_mods='{$icebb->input['mods']}',g_icon='{$icebb->input['icon']}',g_desc='{$icebb->input['desc']}',g_title='{$icebb->input['title']}',g_view_board='{$icebb->input['view_board']}',g_is_mod='{$icebb->input['is_mod']}',g_post_in_locked='{$icebb->input['post_in_locked']}',g_is_admin='{$icebb->input['is_admin']}',g_view_offline_board='{$icebb->input['view_offline_board']}',g_permgroup='{$permgroups}',g_promote_group='{$icebb->input['promote_group']}',g_promote_posts='{$icebb->input['promote_posts']}' WHERE gid='{$icebb->input['gid']}' LIMIT 1");
			$db->query("UPDATE icebb_groups SET g_flood_control='{$icebb->input['flood_control']}',g_suffix='{$icebb->input['suffix']}',g_prefix='{$icebb->input['prefix']}',g_icon='{$icebb->input['icon']}',g_title='{$icebb->input['title']}',g_view_board='{$icebb->input['view_board']}',g_is_mod='{$icebb->input['is_mod']}',g_post_in_locked='{$icebb->input['post_in_locked']}',g_is_admin='{$icebb->input['is_admin']}',g_view_offline_board='{$icebb->input['view_offline_board']}',g_permgroup='{$permgroups}',g_promote_group='{$icebb->input['promote_group']}',g_promote_posts='{$icebb->input['promote_posts']}' WHERE gid='{$icebb->input['gid']}' LIMIT 1");
			
			// recache groups
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
			
			$icebb->admin->redirect("Group edited",$icebb->base_url."act=groups");
		}

		$db->query("SELECT * FROM icebb_forum_permgroups");
		while($pg					= $db->fetch_row())
		{
			$permgroups[]			= array($pg['permid'],$pg['permname']);
		}

		$db->query("SELECT * FROM icebb_groups WHERE gid='{$icebb->input['gid']}' LIMIT 1");
		$g							= $db->fetch_row();

		$icebb->admin->page_title	= "Edit Group";
		
		$status1['1'] = 'Open';
		$status1['2'] = 'Closed';
		$status1['3'] = 'Hidden';
		
		foreach($status1 as $k => $v)
		{
			$status[]			= array($k,$v);
		}
		
		$g['suffix']				= html_entity_decode($g['suffix'],ENT_QUOTES);
		$g['prefix']				= html_entity_decode($g['prefix'],ENT_QUOTES);
		
		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('s'=>$icebb->adsess['sessid'],'act'=>'groups','func'=>'edit','gid'=>$icebb->input['gid'],'submit'=>'1'),'post'," name='adminfrm'");
		
		if($g['gid']=='1')
		{
			$icebb->admin->html		   .= $icebb->admin_skin->start_table("Notice");
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("You are editing the main administrator group. Some options will not be available."),'row1',' colspan="2"');
			$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		}
		
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Edit {$g['g_title']}");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Group Title</b>",$icebb->admin_skin->form_input('title',$g['g_title'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Permission Group</b>",$icebb->admin_skin->form_multiselect('permgroup',$permgroups,$g['g_permgroup'],6)));
		/*$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Description</b>",$icebb->admin_skin->form_textarea('desc',$g['g_desc'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Status</b>",$icebb->admin_skin->form_dropdown('status',$status,$g['g_status'])));*/
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Prefix</b>",$icebb->admin_skin->form_input('prefix',$g['g_prefix'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Suffix</b>",$icebb->admin_skin->form_input('suffix',$g['g_suffix'])));
		//$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Group mods</b><br /><small>(Seperate IDs with a comma)</small>",$icebb->admin_skin->form_input('mods',$g['g_mods'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Team Icon</b><small>(Filename of image in /skins/##/icons/)</small>",$icebb->admin_skin->form_input('icon',$g['g_icon'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Permissions");
		if($g['gid']=='1')
		{
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View Board?</b>",						$icebb->admin_skin->form_yes_no('view_board',1,null,' disabled="disabled"')));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Is Global Moderator?</b>",			$icebb->admin_skin->form_yes_no('is_mod',1,null,' disabled="disabled"')));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Allow replies in locked topics?</b>",	$icebb->admin_skin->form_yes_no('post_in_locked',1,null,' disabled="disabled"')));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Is Administrator?</b>",				$icebb->admin_skin->form_yes_no('is_admin',1,null,' disabled="disabled"')));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View Offline Board?</b>",				$icebb->admin_skin->form_yes_no('view_offline_board',1,null,' disabled="disabled"')));
		}
		else {
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View Board?</b>",						$icebb->admin_skin->form_yes_no('view_board',$g['g_view_board'])));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Is Global Moderator?</b>",			$icebb->admin_skin->form_yes_no('is_mod',$g['g_is_mod'])));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Allow replies in locked topics?</b>",	$icebb->admin_skin->form_yes_no('post_in_locked',$g['g_post_in_locked'])));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Is Administrator?</b>",				$icebb->admin_skin->form_yes_no('is_admin',$g['g_is_admin'])));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View Offline Board?</b>",				$icebb->admin_skin->form_yes_no('view_offline_board',$g['g_view_offline_board'])));
		}
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
				
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Security");
		if($g['gid']=='1')
		{
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>Flood control</b><br /><small>(Prevent a user from posting for <em>x</em> seconds after their previous post)",$icebb->admin_skin->form_input('flood_control',(string)'0',30,' disabled="disabled"')));
		}
		else {
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>Flood control</b><br /><small>(Prevent a user from posting for <em>x</em> seconds after their previous post)",$icebb->admin_skin->form_input('flood_control',$g['g_flood_control'])));
		}
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		/*if($g['gid']			   != '1')
		{
			$groups[]				= array('0',"Disable Promotion");
			foreach($icebb->cache['groups'] as $gr)
			{
				if($gr['gid']		== '1' ||
				   $gr['gid']		== $g['gid'])
				{
					continue;
				}
			
				$groups[]			= array($gr['gid'],$gr['g_title']);
			}
			
			$icebb->admin->html	   .= $icebb->admin_skin->start_table("Promotions");
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>Promote to:</b>",$icebb->admin_skin->form_dropdown('group',$groups,$g['promote_group'])));
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>after</b>",$icebb->admin_skin->form_input('posts',$g['promote_posts'],3).' posts'));
			$icebb->admin->html	   .= $icebb->admin_skin->end_table();
		}*/
		
		$icebb->admin->html		   .= "<div class='buttonrow'><input type='submit' value='Save Changes' class='button' /></div></form>";
	}
	
	function delete()
	{
		global $icebb,$db,$std;
		
		$gid = intval($icebb->input['gid']);
		
		if($gid <= 5)
		{
			$icebb->admin->error("Sorry, you cannot remove the default groups.");
		}
		
		$db->query("SELECT * FROM icebb_groups WHERE gid={$gid} LIMIT 1");
		if($db->get_num_rows() <= 0)
		{
			$icebb->admin->error("Sorry, that group does not exist.");
		}
		else {
			$ginfo = $db->fetch_row();
			if($icebb->input['submit']==1)
			{
				if($icebb->input['confirm']=='1')
				{
					$db->query("SELECT * FROM icebb_groups WHERE gid='{$icebb->input['move_to']}'");
					if($db->get_num_rows() <= 0 && !isset($icebb->input['no_users_in_group']))
					{
						$icebb->admin->error("Can't move to that group. It do not exist.");
					}
					else {
						$db->query("UPDATE icebb_users SET user_group='{$icebb->input['move_to']}' WHERE user_group={$gid}");
						$db->query("DELETE FROM icebb_groups WHERE gid={$gid} LIMIT 1");
						$msg = isset($icebb->input['no_users_in_group']) ? 'Group deleted' : 'Users moved and groups deleted';
						$icebb->admin->redirect($msg,"{$icebb->base_url}act=groups");
					}
				}
				else {
					$icebb->admin->redirect("Group removal aborted","{$icebb->base_url}act=groups");
				}
			}
			else {
				$db->query("SELECT gid,g_title FROM icebb_groups WHERE gid!={$gid} AND gid!=4 AND gid!=5 ORDER BY g_title ASC");
				while($g = $db->fetch_row())
				{
					$mgroups[] = array($g['gid'],$g['g_title']);
				}
				
				$icebb->admin->page_title	= "Delete group: {$ginfo['g_title']}";
		
				$icebb->admin_skin->table_titles[]= array('{none}','40%');
				$icebb->admin_skin->table_titles[]= array('{none}','60%');
				$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'groups','func'=>'del','gid'=>$gid,'submit'=>'1'));
				$icebb->admin->html		   .= $icebb->admin_skin->start_table("Delete group: {$ginfo['g_title']}");
				$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Are you sure you wish to delete the group?</b>",$icebb->admin_skin->form_yes_no('confirm','0')));
				$db->query("SELECT * FROM icebb_users WHERE user_group={$gid}");
				if($db->get_num_rows() > 0)
				{
					$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>Move users in this group to...</b>",$icebb->admin_skin->form_dropdown('move_to',$mgroups)));
				}
				else {
					$icebb->admin->html	   .=$icebb->admin_skin->form_hidden('no_users_in_group','yes');
				}
				$icebb->admin->html		   .= $icebb->admin_skin->end_form("Submit");
				$icebb->admin->html		   .= $icebb->admin_skin->end_table();
			}
		}
	}

	function new_group()
	{
		global $icebb,$config,$db,$std;

		if(isset($icebb->input['submit']))
		{
			if(!get_magic_quotes_gpc())
			{
				$icebb->input['suffix']		= addslashes($icebb->input['suffix']);
				$icebb->input['prefix']		= addslashes($icebb->input['prefix']);
			}
		
			$icebb->input['suffix']			= html_entity_decode($icebb->input['suffix']);
			$icebb->input['prefix']			= html_entity_decode($icebb->input['prefix']);
			
			$icebb->input['flood_control']	= intval($icebb->input['flood_control']);
			
			$db->insert('icebb_groups',array(
				'g_title'			=> $icebb->input['title'],
				'g_view_board'		=> $icebb->input['view_board'],
				'g_is_mod'			=> $icebb->input['is_mod'],
				'g_post_in_locked'	=> $icebb->input['post_in_locked'],
				'g_is_admin'		=> $icebb->input['is_admin'],
				'g_view_offline_board'=> $icebb->input['view_offline_board'],
				//'g_desc'			=> $icebb->input['desc'],
				//'g_status'			=> $icebb->input['status'],
				'g_suffix'			=> $icebb->input['suffix'],
				'g_prefix'			=> $icebb->input['prefix'],
				'g_icon'			=> $icebb->input['icon'],
				//'g_mods'			=> $icebb->input['mods'],
				'g_promote_group'	=> $icebb->input['promote_group'],
				'g_promote_posts'	=> $icebb->input['promote_posts'],
				'g_flood_control'	=> $icebb->input['flood_control'],
			));
			
			$icebb->admin->redirect("Group added",$icebb->base_url."act=groups");
		}

		$db->query("SELECT * FROM icebb_forum_permgroups");
		while($pg					= $db->fetch_row())
		{
			$permgroups[]			= array($pg['permid'],$pg['permname']);
		}

		$icebb->admin->page_title	= "New Group";

		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'groups','func'=>'new','submit'=>'1'),'post'," name='adminfrm'");

		$icebb->admin->html		   .= $icebb->admin_skin->start_table("New Group");
		
		$status1['1'] = 'Open';
		$status1['2'] = 'Closed';
		$status1['3'] = 'Hidden';
		
		foreach($status1 as $k => $v)
		{
			$status[]			= array($k,$v);
		}
		
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Group Title</b>",$icebb->admin_skin->form_input('title',$g['g_title'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Permission Group</b>",$icebb->admin_skin->form_multiselect('permgroup',$permgroups,$g['g_permgroup'],6)));
		/*$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Description</b>",$icebb->admin_skin->form_textarea('desc',$g['g_desc'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Status</b>",$icebb->admin_skin->form_dropdown('status',$status,$g['g_status'])));*/
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Prefix</b>",$icebb->admin_skin->form_input('prefix',htmlspecialchars($g['g_prefix'],ENT_QUOTES))));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Suffix</b>",$icebb->admin_skin->form_input('suffix',$g['g_suffix'])));
		//$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Group mods</b><br /><small>(Seperate IDs with a comma)</small>",$icebb->admin_skin->form_input('mods',$g['g_mods'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Team Icon</b><br /><small>(Filename of image in /skins/##/icons/)</small>",$icebb->admin_skin->form_input('icon',$g['g_icon'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Permissions");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View Board?</b>",$icebb->admin_skin->form_yes_no('view_board')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Is Global Moderator?</b>",$icebb->admin_skin->form_yes_no('is_mod')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Allow replies in locked topics?</b>",$icebb->admin_skin->form_yes_no('post_in_locked')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Is Administrator?</b>",$icebb->admin_skin->form_yes_no('is_admin')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>View Offline Board?</b>",$icebb->admin_skin->form_yes_no('view_offline_board')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Security");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<b>Flood control</b><br /><small>(Prevent a user from posting for <em>x</em> seconds after their previous post)",$icebb->admin_skin->form_input('flood_control',$g['g_flood_control'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		if($g['gid']			   != '1')
		{
			$groups[]				= array('0',"Disable Promotion");
			foreach($icebb->cache['groups'] as $gr)
			{
				if($gr['gid']		== '1')
				{
					continue;
				}
			
				$groups[]			= array($gr['gid'],$gr['g_title']);
			}
			
			$icebb->admin->html	   .= $icebb->admin_skin->start_table("Promotions");
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>Promote to:</b>",$icebb->admin_skin->form_dropdown('group',$groups,'')));
			$icebb->admin->html	   .= $icebb->admin_skin->table_row(array("<b>after</b>",$icebb->admin_skin->form_input('posts','',3).' posts'));
			$icebb->admin->html	   .= $icebb->admin_skin->end_table();
		}

		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Create Group");
	}
	
	function recache_forums()
	{
		global $icebb,$db,$config,$std;

		$forums				= array();

		$db->query("SELECT * FROM icebb_forums");
		while($f			= $db->fetch_row())
		{
			foreach($f as $fkey => $fval)
			{
				$f[$fkey]	= str_replace("'","\'",$fval);
			}
		
			$forums[$f['fid']]	= $f;
		}
		
		//echo "<pre>";
		
		//print_r($forums);
		
		//$serial_killer	= serialize($forums);
		//$serial_catcher	= unserialize($serial_killer);
		//print_r($serial_catcher);
		
		//echo wordwrap(serialize($forums),100,"\n",1);
		//exit();
		
		$std->recache($forums,'forums');
	}
	
	
	//////////////////////////////////////////////////////////////////////////
	// PERMISSION GROUPS
	//////////////////////////////////////////////////////////////////////////
	
	function permgroups()
	{
		global $icebb,$db,$std;
		
		$icebb->admin->page_title		= "Manage Permission Groups";
		
		switch($icebb->input['code'])
		{
			case 'add':
				$this->permgroup_add();
				break;
			case 'edit':
				$this->permgroup_edit();
				break;
			case 'del':
				$this->permgroup_del();
				break;
			default:
				$this->permgroups_list();
				break;
		}
	}
	
	function permgroup_add()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			$db->insert('icebb_forum_permgroups',array(
				'permname'			=> $icebb->input['name'],
			));

			$icebb->admin->redirect("Permission group added","{$icebb->base_url}act=groups&func=permgroups");
		}
		
		$icebb->admin->page_title	= "New Permission Group";
		
		$icebb->admin_skin->table_titles[]= array('{none}','40%');
		$icebb->admin_skin->table_titles[]= array('{none}','60%');
		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'groups','func'=>'permgroups','code'=>'add','submit'=>'1'));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("New Permission Group");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Name</strong>",$icebb->admin_skin->form_input('name','')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("New Permission Group");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function permgroup_edit()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			$db->query("UPDATE icebb_forum_permgroups SET permname='{$icebb->input['name']}' WHERE permid='{$icebb->input['id']}'");
			
			$icebb->admin->redirect("Permission group edited","{$icebb->base_url}act=groups&func=permgroups");
		}
		
		$r							= $db->fetch_result("SELECT * FROM icebb_forum_permgroups WHERE permid='{$icebb->input['id']}'");
		
		$icebb->admin->page_title	= "Edit Permission Group: {$r['permname']}";
		
		$icebb->admin_skin->table_titles[]= array('{none}','40%');
		$icebb->admin_skin->table_titles[]= array('{none}','60%');
		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'groups','func'=>'permgroups','code'=>'edit','id'=>$r['permid'],'submit'=>'1'));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Edit Rank: {$r['rtitle']}");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Name</strong>",$icebb->admin_skin->form_input('name',$r['permname'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Edit Permission Group");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function permgroup_del()
	{
		global $icebb,$db,$std;
		
		$db->query("DELETE FROM icebb_forum_permgroups WHERE permid='{$icebb->input['id']}'");
		$this->recache_ranks();
		
		$icebb->admin->redirect("Permission group removed","{$icebb->base_url}act=groups&func=permgroups");
	}
	
	function permgroups_list()
	{
		global $icebb,$db,$std;
	
		$icebb->admin_skin->table_titles[]= array('Name','60%');
		$icebb->admin_skin->table_titles[]= array('&nbsp;','40%');
	
		$icebb->admin->html				= $icebb->admin_skin->start_table("Permission Groups");
		
		$db->query("SELECT * FROM icebb_forum_permgroups");
		while($r						= $db->fetch_row())
		{
			$row						= array();
			$row[]						= "<strong>{$r['permname']}</strong>";
			$row[]						= "<div style='text-align:right'><a href='{$icebb->base_url}act=groups&amp;func=permgroups&amp;code=edit&amp;id={$r['permid']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=groups&amp;func=permgroups&amp;code=del&amp;id={$r['permid']}'>Remove</a></div>";
		
			$icebb->admin->html		   .= $icebb->admin_skin->table_row($row);
		}
		
		$icebb->admin->html			   .= $icebb->admin_skin->end_table();

		$icebb->admin->html			   .= "<form action='#'><div class='buttonrow'><input type='button' value='New Permission Group' onclick=\"window.location='{$icebb->base_url}act=groups&amp;func=permgroups&amp;code=add'\" class='button' /></div></form>";
	}
	
	//////////////////////////////////////////////////////////////////////////
	// RANKS
	//////////////////////////////////////////////////////////////////////////
	
	function ranks()
	{
		global $icebb,$db,$std;
		
		$icebb->admin->page_title		= "Manage Ranks";
		
		switch($icebb->input['code'])
		{
			case 'add':
				$this->rank_add();
				break;
			case 'edit':
				$this->rank_edit();
				break;
			case 'del':
				$this->rank_del();
				break;
			default:
				$this->ranks_list();
				break;
		}
	}
	
	function rank_add()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			$db->insert('icebb_ranks',array(
				'rtitle'			=> $icebb->input['title'],
				'rpips'				=> $icebb->input['pips'],
				'rposts'			=> $icebb->input['posts'],
			));
			$this->recache_ranks();
			
			$icebb->admin->redirect("Rank added","{$icebb->base_url}act=groups&func=ranks");
		}
		
		$icebb->admin->page_title	= "New Rank";
		
		$icebb->admin_skin->table_titles[]= array('{none}','40%');
		$icebb->admin_skin->table_titles[]= array('{none}','60%');
		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'groups','func'=>'ranks','code'=>'add','submit'=>'1'));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("New Rank");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Rank title</strong>",$icebb->admin_skin->form_input('title','')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Number of pips</strong>",$icebb->admin_skin->form_input('pips','',3)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Number of posts</strong>",$icebb->admin_skin->form_input('posts','',3)));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("New Rank");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function rank_edit()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			$db->query("UPDATE icebb_ranks SET rtitle='{$icebb->input['title']}',rpips='{$icebb->input['pips']}',rposts='{$icebb->input['posts']}' WHERE rid='{$icebb->input['id']}'");
			$this->recache_ranks();
			
			$icebb->admin->redirect("Rank edited","{$icebb->base_url}act=groups&func=ranks");
		}
		
		$r							= $db->fetch_result("SELECT * FROM icebb_ranks WHERE rid='{$icebb->input['id']}'");
		
		$icebb->admin->page_title	= "Edit Rank: {$r['rtitle']}";
		
		$icebb->admin_skin->table_titles[]= array('{none}','40%');
		$icebb->admin_skin->table_titles[]= array('{none}','60%');
		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'groups','func'=>'ranks','code'=>'edit','id'=>$r['rid'],'submit'=>'1'));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Edit Rank: {$r['rtitle']}");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Rank title</strong>",$icebb->admin_skin->form_input('title',$r['rtitle'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Number of pips</strong>",$icebb->admin_skin->form_input('pips',$r['rpips'],3)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Number of posts</strong>",$icebb->admin_skin->form_input('posts',$r['rposts'],3)));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Edit Rank");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function rank_del()
	{
		global $icebb,$db,$std;
		
		$db->query("DELETE FROM icebb_ranks WHERE rid='{$icebb->input['id']}'");
		$this->recache_ranks();
		
		$icebb->admin->redirect("Rank removed","{$icebb->base_url}act=groups&func=ranks");
	}
	
	function ranks_list()
	{
		global $icebb,$db,$std;
	
		$icebb->admin_skin->table_titles[]= array('&nbsp;','20%');
		$icebb->admin_skin->table_titles[]= array('Rank','55%');
		$icebb->admin_skin->table_titles[]= array('Posts','10%');
		$icebb->admin_skin->table_titles[]= array('&nbsp;','15%');
	
		$icebb->admin->html				= $icebb->admin_skin->start_table("Ranks");
		
		$db->query("SELECT * FROM icebb_ranks");
		while($r						= $db->fetch_row())
		{
			$pips						= '';
			for($p=1;$p<=$r['rpips'];$p++)
			{
				$pips				   .= "<img src='skins/1/images/pip.gif' alt='*' />";
			}
		
			$row						= array();
			$row[]						= $pips;
			$row[]						= "<strong>{$r['rtitle']}</strong>";
			$row[]						= $r['rposts'];
			$row[]						= "<div style='text-align:right'><a href='{$icebb->base_url}act=groups&amp;func=ranks&amp;code=edit&amp;id={$r['rid']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=groups&amp;func=ranks&amp;code=del&amp;id={$r['rid']}'>Remove</a></div>";
		
			$icebb->admin->html		   .= $icebb->admin_skin->table_row($row);
		}
		
		$icebb->admin->html			   .= $icebb->admin_skin->end_table();

		$icebb->admin->html			   .= "<form action='#'><div class='buttonrow'><input type='button' value='New Rank' onclick=\"window.location='{$icebb->base_url}act=groups&amp;func=ranks&amp;code=add'\" class='button' /></div></form>";
	}
	
	function recache_ranks()
	{
		global $icebb,$db,$std;
		
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
}
?>