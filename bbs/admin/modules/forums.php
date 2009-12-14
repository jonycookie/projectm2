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
// forums admin module
// $Id: forums.php 946 2005-11-18 03:58:13Z mutantmonkey $
//******************************************************//

class forums
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('forums');
		
		$icebb->admin->page_title	= "Forums";
		
		switch($icebb->input['func'])
		{
			case 'new':
				$this->new_forum();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'children':
				$this->show_children();
				break;
			case 'del':
				$this->delete();
				break;
			case 'mod':
				$this->moderator();
				break;
			case 'duplicate':
				$this->duplicate();
				break;
			case 'announce':
				$this->announce();
				break;
			case 'new_announce':
				$this->new_announce();
				break;
			case 'edit_announce':
				$this->edit_announce();
				break;
			default:
				$this->show_forums();
				break;
		}
		
		$icebb->admin->html			= $this->html->display($icebb->admin->html);
		
		$icebb->admin->output();
	}
	
	function duplicate()
	{
		global $icebb,$config,$db,$std;
		
		if(empty($icebb->input['fid']))
		{
			$icebb->admin->error("No Forum ID choosen.");
		}
		
		if(isset($icebb->input['submit']))
		{
			$fq = $db->query("SELECT * FROM icebb_forums WHERE fid={$icebb->input['fid']} LIMIT 1");
			
			if($db->get_num_rows($fq) <= 0)
			{
				$icebb->admin->error("No such forum");
			}
			
			$f							= $db->fetch_row($fq);
			
			$lastfq = $db->query("SELECT * FROM icebb_forums ORDER BY sort ASC LIMIT 1");
			$lastforum = $db->fetch_row($lastfq);
			
			$db->insert('icebb_forums',array(
							'sort'			=> $lastforum['sort']+1,
							'name'			=> $icebb->input['name'],
							'description'	=> $icebb->input['description'],
							'parent'		=> $icebb->input['parent'],
							'postable'		=> $f['postable'],
							'redirecturl'	=> $f['redirecturl'],
							'perms'			=> $f['perms'],
							'password'		=> $f['password'],
							'moderators'	=> $f['moderators'],
						));
			
			$icebb->admin->redirect("Duplicate created",$icebb->base_url."act=forums");
		}
		else 
		{
			$fq = $db->query("SELECT * FROM icebb_forums WHERE fid={$icebb->input['fid']} LIMIT 1");
			
			if($db->get_num_rows($fq) <= 0)
			{
				$icebb->admin->error("No such forum");
			}
			
			$f							= $db->fetch_row($fq);
			
			$icebb->user['g_permgroup']	= 1;
			$forumlist					= $std->get_forum_listing();
			$forumslist					= $this->forum_list_children($forumlist,'0');
			$parentlist[]				= array('0','Root (Make this forum a category)');
			foreach($forumslist as $f2)
			{
				$parentlist[]			= $f2;
			}
			
			$icebb->admin->page_title	= "Duplicate forum: {$f['name']}";

			$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

			$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'duplicate','fid'=>$icebb->input['fid']),'post'," name='adminfrm'");
			
			$icebb->admin->html		   .= $icebb->admin_skin->start_table("Settings");
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Forum Name</strong>",$icebb->admin_skin->form_input('name',$f['name'])));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Forum Description</strong><br />Enter a description for the forum. HTML is allowed, but you do not need to use &lt;br&gt; tags.",$icebb->admin_skin->form_textarea('description',$f['description'])));
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Parent Forum</strong><br />The \"parent\" of the forum. That is, the category or forum that this forum will appear in.",$icebb->admin_skin->form_dropdown('parent',$parentlist,$f['parent'])));
			$icebb->admin->html		   .= $icebb->admin_skin->end_table();
			
			$icebb->admin->html		   .= "<div class='buttonrow'><input type='submit' name='submit' value='Create Duplicate' class='button' /></div></form>";
		}
	}
	
	function show_forums()
	{
		global $icebb,$config,$db,$std;

		if(isset($icebb->input['reorder']))
		{
			if(is_array($icebb->input['reorder-root-forums']))
			{
				$icebb->input['forum_sort']	= array();
				$c							= 0;
				foreach($icebb->input['reorder-root-forums'] as $ro => $rf)
				{
					$c++;
					$icebb->input['forum_sort'][$rf]= $c;
				}
			}
		
			foreach($icebb->input['forum_sort'] as $k => $v)
			{
				$db->query("UPDATE icebb_forums SET sort='{$v}' WHERE fid='{$k}' LIMIT 1");
			}
			
			$this->recache_forums();
			
			$icebb->admin->redirect("Forums reordered","{$icebb->base_url}act=forums");
		}

		$db->query("SELECT COUNT(*) FROM icebb_forums WHERE parent='0' ORDER BY sort");
		$fr						= $db->fetch_row();
		$frsort					= $fr['COUNT(*)'];
		
		for($i=1; $i <= $frsort; $i++)
		{
			$sortlist[]			= array($i,$i);
		}
			
		foreach($icebb->cache['forums'] as $fo)
		{
			$this->subforums[$fo['parent']][]= $fo;
		}
		
		if(is_array($icebb->cache['moderators']))
		{
			foreach($icebb->cache['moderators'] as $mo)
			{
				$this->moderators[$mo['mforum']][]= $mo;
			}
		}
		
		$z						= 0;
		
		// parent forums
		$parentq				= $db->query("SELECT * FROM icebb_forums WHERE parent='0' ORDER BY sort");
		$total					= $db->get_num_rows();
		while($c				= $db->fetch_row($parentq))
		{
			$z++;
		
			$c['total']			= $total;
			$c['on']			= $z;
		
			$tforum				= $db->query("SELECT * FROM icebb_forums WHERE parent='{$c['fid']}' ORDER BY sort");
			while($r			= $db->fetch_row($tforum))
			{
				$children2		= array();
				$tas			= $db->query("SELECT fid,name FROM icebb_forums WHERE parent='{$r['fid']}' ORDER BY sort");
				while($t		= $db->fetch_row($tas))
				{
					$children2[]= $t;
				}
				
				$children	   .= $this->html->list_forums_forum_child($r,$children2);
			}
			
			//$children			= $this->get_childrenof($c['fid']);
			
			$forums	    	   .= $this->html->list_forums_forum($c,$children);
			$children			= '';
		}
		
		$icebb->admin->html		= $this->html->list_forums($forums);
	}
	
	function get_childrenof($fid,$before='')
	{
		global $icebb,$db,$std;
		
		if(is_array($this->subforums[$fid]))
		{
			foreach($this->subforums[$fid] as $sf)
			{
				$data	   .= $this->html->list_forums_forum_child_child($sf,$before."&nbsp;'-"," style='display:none'");
			
				if(count($this->subforums[$sf['fid']])>=1)
				{
					$data  .= $this->get_childrenof($sf['fid'],$before.'&nbsp;&nbsp;&nbsp;&nbsp;');
				}
			}
		}
		
		return $data;
	}

	function show_children()
	{
		global $icebb,$config,$db,$std;

		if(isset($icebb->input['reorder']))
		{
			foreach($icebb->input['forum_sort'] as $k => $v)
			{
				$db->query("UPDATE icebb_forums SET sort='{$v}' WHERE fid='{$k}' LIMIT 1");
			}
			
			$this->recache_forums();
			
			$icebb->admin->redirect("Forums reordered","{$icebb->base_url}act=forums");
		}

		// how many to sort by?
		$db->query("SELECT COUNT(*) FROM icebb_forums WHERE parent='{$icebb->input['fid']}'");
		$fr						= $db->fetch_row();
		$frsort					= $fr['COUNT(*)'];
		
		for($i=1; $i <= $frsort; $i++)
		{
			$sortlist[]			= array($i,$i);
		}

		// parent forums
		$parentq				= $db->query("SELECT * FROM icebb_forums WHERE fid='{$icebb->input['fid']}' ORDER BY sort");
		while($c				= $db->fetch_row($parentq))
		{
			$elforums			= array();
		
			$tforum				= $db->query("SELECT * FROM icebb_forums WHERE parent='{$c['fid']}' ORDER BY sort");
			while($r			= $db->fetch_row($tforum))
			{
				$elforums[]		= $r;
			}
			$icebb->admin->html.= $this->html->show_children($c,$elforums);
		}
	}

	function edit()
	{
		global $icebb,$config,$db,$std;

		if(isset($icebb->input['submit']))
		{
			$db->query("SELECT * FROM icebb_forum_permgroups");
			while($p			= $db->fetch_row())
			{
				$perms[$p['permid']] = array(
					'seeforum'		=> intval($icebb->input["seeforum_{$p['permid']}"]),
					'read'			=> intval($icebb->input["read_{$p['permid']}"]),
					'createtopics'	=> intval($icebb->input["createtopics_{$p['permid']}"]),
					'reply'			=> intval($icebb->input["reply_{$p['permid']}"]),
					'attach'		=> intval($icebb->input["attach_{$p['permid']}"]),
				);
			}
		
			$perms_serialized		= serialize($perms);
			
			if(!empty($icebb->input['set_as_default']))
			{
				$icebb->cache['admin']['default_perms']= $perms_serialized;
				$std->recache($icebb->cache['admin'],'admin');
			}
		
			if(!empty($icebb->input['password']))
			{
				if($icebb->input['password']!=$icebb->input['pass2'])
				{
					$icebb->admin->error("The passwords do not match");
				}
			
				$add_me				= ",password='".md5($icebb->input['password'])."'";

				if($icebb->input['password']=='clear')
				{
					$add_me			= ",password=''";
				}
			}
		
			$db->query("UPDATE icebb_forums SET name='{$icebb->input['name']}',description='{$icebb->input['description']}',parent='{$icebb->input['parent']}',postable='{$icebb->input['is_postable']}',redirecturl='{$icebb->input['redirecturl']}',perms='{$perms_serialized}'{$add_me} WHERE fid='{$icebb->input['fid']}' LIMIT 1");
			
			$this->recache_forums();
			
			$icebb->admin->redirect("Forum edited",$icebb->base_url."act=forums");
		}
		
		$icebb->user['g_permgroup']=1;
		$forumlist					= $std->get_forum_listing();
		$forumslist					= $this->forum_list_children($forumlist,'0');
		$parentlist[]				= array('0','Root (Make this forum a category)');
		foreach($forumslist as $f)
		{
			$parentlist[]			= $f;
		}
		
		$db->query("SELECT * FROM icebb_forums WHERE fid='{$icebb->input['fid']}' LIMIT 1");
		$f							= $db->fetch_row();

		$icebb->admin->page_title	= "Edit Forum: {$f['name']}";

		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'edit','fid'=>$icebb->input['fid']),'post'," name='adminfrm'");

		$icebb->admin->html		   .= $icebb->admin_skin->start_table("General Settings");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Forum Name</strong>",$icebb->admin_skin->form_input('name',$f['name'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Forum Description</strong><br />Enter a description for the forum. HTML is allowed, but you do not need to use &lt;br&gt; tags.",$icebb->admin_skin->form_textarea('description',$f['description'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Parent Forum</strong><br />The \"parent\" of the forum. That is, the category or forum that this forum will appear in.",$icebb->admin_skin->form_dropdown('parent',$parentlist,$f['parent'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Postable?</strong><br />Do you want to allow people to make posts in this forum?",$icebb->admin_skin->form_yes_no('is_postable',$f['postable'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	
		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));	
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("URL Redirection");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>URL to redirect to</strong><br />The web address to redirect this forum to. Leave blank to disable.",$icebb->admin_skin->form_input('redirecturl',$f['redirecturl'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	
		/*$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));	
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Automatic Pruning");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Enable automatic pruning</strong>",$icebb->admin_skin->form_checkbox('autoprune','')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>No replies in the past <em>x</em> days</strong>",$icebb->admin_skin->form_input('autoprune_days','',6)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Include pinned?</strong>",$icebb->admin_skin->form_checkbox('autoprune_pinned','')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();*/
	
		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));	
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Miscellaneous");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Password</strong><br />Enter a password twice to change the password, leave blank to leave alone, and enter 'clear' (without the quotes) in both boxes to clear the password.",$icebb->admin_skin->form_input('password','').$icebb->admin_skin->form_input('pass2','')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		$icebb->admin->html		   .= $icebb->admin_skin->render_permissions_table(unserialize($f['perms']),"Save Changes",'adminfrm');
		
		$icebb->admin->html		   .= "<div class='buttonrow'><input type='submit' name='submit' value='Save Changes' class='button' /></div></form>";
		
		//$icebb->admin->html		   .= $icebb->admin_skin->end_form("Create Forum");
	}

	function new_forum()
	{
		global $icebb,$config,$db,$std;

		if(isset($icebb->input['submit']))
		{
			$db->query("SELECT * FROM icebb_forum_permgroups");
			while($p			= $db->fetch_row())
			{
				$perms[$p['permid']] = array(
					'seeforum'		=> intval($icebb->input["seeforum_{$p['permid']}"]),
					'read'			=> intval($icebb->input["read_{$p['permid']}"]),
					'createtopics'	=> intval($icebb->input["createtopics_{$p['permid']}"]),
					'reply'			=> intval($icebb->input["reply_{$p['permid']}"]),
					'attach'		=> intval($icebb->input["attach_{$p['permid']}"]),
				);
			}
		
			$perms_serialized		= serialize($perms);
			
			if(!empty($icebb->input['set_as_default']))
			{
				$icebb->cache['admin']['default_perms']= $perms_serialized;
				$std->recache($icebb->cache['admin'],'admin');
			}
		
			$db->query("SELECT * FROM icebb_forums ORDER BY sort ASC LIMIT 1");
			$lastforum				= $db->fetch_row();
		
			if(!empty($icebb->input['password']))
			{
				if($icebb->input['password']!=$icebb->input['pass2'])
				{
					$icebb->admin->error("The passwords do not match");
				}
			
				$icebb->input['password']= md5($icebb->input['password']);
			}

			$db->insert('icebb_forums',array(
				'sort'				=> $lastforum['sort']+1,
				'name'				=> $icebb->input['name'],
				'description'		=> $icebb->input['description'],
				'parent'			=> $icebb->input['parent'],
				'postable'			=> $icebb->input['is_postable'],
				'redirecturl'		=> $icebb->input['redirecturl'],
				'perms'				=> $perms_serialized,
				'password'			=> $icebb->input['password'],
			));
			
			$this->recache_forums();
			
			$icebb->admin->redirect("Forum created",$icebb->base_url."act=forums");
		}
		
		$icebb->user['g_permgroup']=1;
		$forumlist					= $std->get_forum_listing();
		$forumslist					= $this->forum_list_children($forumlist,'0');
		$parentlist[]				= array('0','Root (Make this forum a category)');
		foreach($forumslist as $f)
		{
			$parentlist[]			= $f;
		}

		$icebb->admin->page_title	= "New Forum";

		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));

		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'new'),'post'," name='adminfrm'");

		$icebb->admin->html		   .= $icebb->admin_skin->start_table("General Settings");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Forum Name</strong></small>",$icebb->admin_skin->form_input('name')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Forum Description</strong><br />Enter a description for the forum. HTML is allowed, but you do not need to use &lt;br&gt; tags.",$icebb->admin_skin->form_textarea('description')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Parent Forum</strong><br />The \"parent\" of the forum. That is, the category or forum that this forum will appear in.",$icebb->admin_skin->form_dropdown('parent',$parentlist,$icebb->input['parent'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Postable?</strong><br />Do you want to allow people to make posts in this forum?",$icebb->admin_skin->form_yes_no('is_postable',1)));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("URL Redirection");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>URL to redirect to</strong><br />The web address to redirect this forum to. Leave blank to disable.",$icebb->admin_skin->form_input('redirecturl')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	
		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));	
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Miscellaneous");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Password</strong><br />Enter a password twice to set a password for this forum. Leave blank to disable.",$icebb->admin_skin->form_input('password','').$icebb->admin_skin->form_input('pass2','')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		$icebb->admin->html		   .= $icebb->admin_skin->render_permissions_table($perms,"Create Forum",'adminfrm');
		
		$icebb->admin->html		   .= "<div class='buttonrow'><input type='submit' name='submit' value='Create Forum' class='button' /></div></form>";
		
		//$icebb->admin->html		   .= $icebb->admin_skin->end_form("Create Forum");
	}
    
    function delete()
    {
    	global $icebb,$db,$config,$std;
     
     	$fid						= intval($icebb->input['fid']);
		$fids						= array($fid);
		
		// Get forum IDs to delete
		$fids						= $this->_delete_subforums($fid,$fids);
		$fids						= implode(',',$fids);
		
		// Delete the forums
		$db->query("DELETE FROM icebb_forums WHERE fid IN ({$fids})");
		
		// Delete the forums' topics
		$db->query("DELETE FROM icebb_topics WHERE forum IN ({$fids})");
		
		// Recache
		$this->recache_forums();
		  
		// Redirect
		$icebb->admin->redirect("Forum deleted",$icebb->base_url."act=forums");
    }
    
    function _delete_subforums($fid,$fids=array())
    {
    	global $icebb,$db,$std;
    
		$db->query("SELECT fid FROM icebb_forums WHERE parent={$fid}");
		while($f					= $db->fetch_row())
		{
			$fids[]					= $f['fid'];
			
			$fids					= $this->_delete_subforums($f['fid'],$fids);
		}
    
    	return $fids;
	}
    
    function moderator()
    {
    	global $icebb,$db,$std;
	
		switch($icebb->input['code'])
		{
			case 'add':
				$this->moderator_add();
				break;
			case 'edit':
				$this->moderator_edit();
				break;
			case 'del':
				$this->moderator_del();
				break;
			default:
				break;
		}
	}
	
	function moderator_add()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			$u						= $db->fetch_result("SELECT * FROM icebb_users WHERE username='{$icebb->input['username']}'");
			
			if($u['result_num_rows_returned'] == 0)
			{
				$icebb->admin->error("Sorry, the user '{$icebb->input['username']}' do not exist.");
			}
			
			$db->insert('icebb_moderators',array(
				'mforum'			=> $icebb->input['fid'],
				'muserid'			=> $u['id'],
				'muser'				=> $u['username'],
				'medit'				=> $icebb->input['medit'],
				'medit_topic'		=> $icebb->input['medit_topic'],
				'mdel'				=> $icebb->input['mdel'],
				'mdel_topic'		=> $icebb->input['mdel_topic'],
				'mview_ip'			=> $icebb->input['mview_ip'],
				'munlock'			=> $icebb->input['munlock'],
				'mlock'				=> $icebb->input['mlock'],
				'm_multi_move'		=> $icebb->input['m_multi_move'],
				'm_multi_del'		=> $icebb->input['m_multi_del'],
				'mmove'				=> $icebb->input['mmove'],
				'mpin'				=> $icebb->input['mpin'],
				'munpin'			=> $icebb->input['munpin'],
				'mwarn'				=> $icebb->input['mwarn'],
				'medit_user'		=> $icebb->input['medit_user'],
				'm_is_group'		=> '0',
				'mgroup_id'			=> '',
				'mgroup'			=> '',
			));
			
			$this->recache_moderators();
			
			$icebb->admin->redirect("Moderator added","{$icebb->base_url}act=forums");
		}
		
		$icebb->admin_skin->table_titles= array(array('{none}','40%'),array('{none}','60%'));
		$icebb->admin->html			= $icebb->admin_skin->start_form(array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'mod','code'=>'add','fid'=>$icebb->input['fid'],'submit'=>'1'),'post'," name='adminfrm'");
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Add Moderator");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Username</strong>",$icebb->admin_skin->form_input('username','')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can edit other's posts?</strong>",$icebb->admin_skin->form_yes_no('medit',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can edit other's topics?</strong>",$icebb->admin_skin->form_yes_no('medit_topic',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can delete other's posts?</strong>",$icebb->admin_skin->form_yes_no('mdel',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can delete other's topics?</strong>",$icebb->admin_skin->form_yes_no('mdel_topic',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can view posters IP addresses?</strong>",$icebb->admin_skin->form_yes_no('mview_ip',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can open locked topics?</strong>",$icebb->admin_skin->form_yes_no('munlock',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can lock topics?</strong>",$icebb->admin_skin->form_yes_no('mlock',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can pin topics?</strong>",$icebb->admin_skin->form_yes_no('mpin',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can unpin topics?</strong>",$icebb->admin_skin->form_yes_no('munpin',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can move topics?</strong>",$icebb->admin_skin->form_yes_no('mmove',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can warn users?</strong>",$icebb->admin_skin->form_yes_no('mwarn',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can edit users?</strong>",$icebb->admin_skin->form_yes_no('medit_user',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can mass move topics?</strong>",$icebb->admin_skin->form_yes_no('m_multi_move',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Can mass delete topics?</strong>",$icebb->admin_skin->form_yes_no('m_multi_del',0)));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Add Moderator");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function moderator_edit()
	{
		global $icebb,$db,$std;
		
		$this->moderator_del();
	}
	
	function moderator_del()
	{
		global $icebb,$db,$std;
		
		$db->query("DELETE FROM icebb_moderators WHERE mid='{$icebb->input['mid']}' LIMIT 1");
			
		$this->recache_moderators();
			
		$icebb->admin->redirect("Moderator removed","{$icebb->base_url}act=forums");
	}
    	
	function recache_forums()
	{
		global $icebb,$db,$config,$std;

		$forums				= array();

		$db->query("SELECT * FROM icebb_forums");
		while($f			= $db->fetch_row())
		{
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
	
	function recache_moderators()
	{
		global $icebb,$db,$std;

		$moderators				= array();

		$db->query("SELECT * FROM icebb_moderators");
		while($m			= $db->fetch_row())
		{
			foreach($m as $mkey => $mval)
			{
				$m[$mkey]	= wash_key(str_replace("&amp;","&",$mval));
			}
		
			$moderators[$m['mid']]	= $m;
		}
		
		$std->recache($moderators,'moderators');
	}
	
	function announce()
	{
		global $icebb,$db,$std;
		
		$icebb->admin_skin->table_titles= array(array('{none}','50%'));
			
		$icebb->admin->html .= $icebb->admin_skin->start_table("Manage Annoucements");
		$icebb->admin->html .= $icebb->admin_skin->table_row(array("<strong>Annoucement Title</string>","<strong>Posted By</strong>","<strong>Posted On</strong>","<strong>Actions</strong>"));
		$db->query("SELECT * FROM `icebb_announcements`");
		while($a = $db->fetch_row())
		{
			$icebb->admin->html .= $icebb->admin_skin->table_row(array($a['atitle'],$a['aauthor'],gmdate("F j, Y",$a['adate']),"<a href=\"{$icebb->base_url}act=forums&func=edit_announce&id={$a['aid']}\">Edit</a> - <a href=\"{$icebb->base_url}act=forums&func=edit_announce&delete=1&id={$a['aid']}\">Delete</a>"));
		}
		$icebb->admin->html .= $icebb->admin_skin->end_table();
	}

	function new_announce()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			$newaid	 = $db->fetch_result("SELECT * FROM icebb_announcements ORDER BY aid DESC LIMIT 1");
			$newaid = $newaid['aid'] + 1;
			
			$aforums = implode(',', $icebb->input["aforums"]);
			$aforums .= ",";
			
			$db->insert('icebb_announcements',array(
						'aid'       => $newaid,
						'aauthor'   => $icebb->adsess["username"],
						'aauthorid' => $icebb->adsess["userid"],
						'adate'     => time(),
						'atitle'    => $icebb->input["title"],
						'atext'     => $icebb->input["body"],
						'aforums'   => $aforums));
						
			$icebb->admin->redirect("Annoucement added","{$icebb->base_url}act=forums&func=announce");
		}
		else
		{
			$icebb->admin->page_title	= "New Announcement";

			$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'new_announce'),'post'," name='adminfrm'");
			
			$icebb->admin->html		   .= $icebb->admin_skin->start_table("New Announcement");
			$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Announcement Title</strong>",$icebb->admin_skin->form_input('title')));
			$icebb->admin->html        .= $icebb->admin_skin->table_row(array("<strong>Announcement Body</strong>",$icebb->admin_skin->form_textarea('body')));
			
			$icebb->user['g_permgroup']=1;
			$forumlist					= $std->get_forum_listing();
			$forumslist					= $this->forum_list_children($forumlist,'0');
			
			$icebb->admin->html        .= $icebb->admin_skin->table_row(array("<strong>Activate For Forums</strong>",$icebb->admin_skin->form_multiselect("aforums",$forumslist)));
			$icebb->admin->html		   .= $icebb->admin_skin->end_table();
			
			$icebb->admin->html		   .= "<div class='buttonrow'><input type='submit' name='submit' value='Add Announcement' class='button' /></div></form>";
		}
	}
	
	function edit_announce()
	{
		global $icebb,$db,$std;
		
		if($icebb->input['delete'] == 1)
		{
			if($icebb->input['confirm'] == "Yes")
			{
				$db->query("DELETE FROM icebb_announcements WHERE aid='{$icebb->input["id"]}'");
				$icebb->admin->redirect('Announcement deleted',"{$icebb->base_url}act=forums&func=announce");
			}
			else if($icebb->input["confirm"] == "No")
			{
				$icebb->admin->redirect('Going back to Announcements',"{$icebb->base_url}act=forums&func=announce");
			}
			else
			{
				$icebb->admin->html	.= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'edit_announce','delete'=>'1','id'=>$icebb->input["id"]),'post'," name='adminfrm'");
			
				$icebb->admin->html .= $icebb->admin_skin->start_table("Confirm Delete Announcement");
				$icebb->admin->html .= $icebb->admin_skin->table_row(array("<b>Are you sure you want to remove this announcement?</b>"));
				$icebb->admin->html .= $icebb->admin_skin->end_table();
				
				$icebb->admin->html .= "<div class='buttonrow'><input type='submit' name='confirm' value='Yes' /><input type='submit' name='confirm' value='No' /></div></form>";
			}
		}
		else
		{
			if(!empty($icebb->input["submit"]))
			{
				
				$aforums = implode(',', $icebb->input["aforums"]);
				$aforums .= ","; // Whats the point in that? I'll leave it in case it has to be there...
					
				$db->query("UPDATE icebb_announcements SET aauthor='{$icebb->input['author']}',aauthorid='{$icebb->input['authorid']}',adate='{$icebb->input['date']}',atitle='{$icebb->input['title']}',atext='{$icebb->input['body']}',aforums='{$aforums}' WHERE aid='{$icebb->input['aid']}'");
				
						
				$icebb->admin->redirect("Announcement changes saved","{$icebb->base_url}act=forums&func=announce");
			}
			else
			{
				$a = $db->fetch_result("SELECT * FROM icebb_announcements WHERE aid='{$icebb->input["id"]}'");
				
				$icebb->admin->page_title	= "Edit Announcement";

				$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('sessid'=>$icebb->adsess['sessid'],'act'=>'forums','func'=>'edit_announce','aid'=>$icebb->input["id"],'author'=>$a['aauthor'],'authorid'=>$a['aauthorid'],'date'=>$a['adate']),'post'," name='adminfrm'");
				
				$icebb->admin->html		   .= $icebb->admin_skin->start_table("New Announcement");
				$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Announcement Title</strong>",$icebb->admin_skin->form_input('title',$a["atitle"])));
				$icebb->admin->html        .= $icebb->admin_skin->table_row(array("<strong>Announcement Body</strong>",$icebb->admin_skin->form_textarea('body',$a["atext"],5,45)));
				
				$icebb->user['g_permgroup']=1;
				$forumlist					= $std->get_forum_listing();
				
				$forumslist					= $this->forum_list_children($forumlist,'0');
				
				$icebb->admin->html        .= $icebb->admin_skin->table_row(array("<strong>Activate For Forums</strong>",$icebb->admin_skin->form_multiselect("aforums",$forumslist,$a['aforums'])));
				$icebb->admin->html		   .= $icebb->admin_skin->end_table();
				
				$icebb->admin->html		   .= "<div class='buttonrow'><input type='submit' name='submit' value='Save Changes' class='button' /></div></form>";
			}
		}
	}
	
	function forum_list_children($list,$fn)
	{
		global $icebb,$db,$config,$std;
		
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
