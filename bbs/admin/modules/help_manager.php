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
// help manager admin module
// $Id: help_manager.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class help_manager
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title	= "Manage Help";
		
		switch($icebb->input['func'])
		{
			case 'add':
				$this->add_topic();
				break;
			case 'edit':
				$this->edit_topic();
				break;
			case 'del':
				$this->del_topic();
				break;
			default:
				$this->manage();
				break;
		}
		
		$icebb->admin->html				= $this->html->header().$icebb->admin->html.$this->html->footer();

		$icebb->admin->output();
	}
	
	function manage()
	{
		global $icebb,$config,$db,$std;
	
		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');
	
		$icebb->admin->html			= $icebb->admin_skin->start_table("Help Topics");
		
		$db->query("SELECT * FROM icebb_helpbits");
		while($h					= $db->fetch_row())
		{
			$thisrow[0]	   			= "{$h['hname']}";
			$thisrow[1]				= "<div style='text-align:right'><a href='{$icebb->base_url}act=help_manager&amp;func=edit&amp;id={$h['hid']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=help_manager&amp;func=del&amp;id={$h['hid']}'>Remove</a></div>";
			
			$icebb->admin->html	   .= $icebb->admin_skin->table_row($thisrow);
		}
		
		$icebb->admin->html		   .= $icebb->admin_skin->table_row("<center><a href='{$icebb->base_url}act=help_manager&amp;func=add'>Add Help Topic</a></center>",'row2');
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}

	function add_topic()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			if(empty($icebb->input['title']) || empty($icebb->input['answer']))
			{
				$icebb->admin->error("Complete all fields");
			}
		
			$db->insert('icebb_helpbits',array(
				'hname'						=> $icebb->input['title'],
				'htext'						=> html_entity_decode($icebb->input['answer']),
			));
			
			$icebb->admin->redirect("Help topic added","{$icebb->base_url}act=help_manager");
		}
		
		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');
	
		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'help_manager','func'=>'add','submit'=>'1',));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Add a help topic");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Question</strong>",$icebb->admin_skin->form_input('title','')));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Answer</strong>",$icebb->admin_skin->form_textarea('answer','')));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Add Help Topic");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function edit_topic()
	{
		global $icebb,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
			if(empty($icebb->input['title']) || empty($icebb->input['answer']))
			{
				$icebb->admin->error("Complete all fields");
			}
		
			if(!get_magic_quotes_gpc())
			{
				$icebb->input['answer']		= addslashes($icebb->input['answer']);
			}
		
			$htext							= html_entity_decode($icebb->input['answer']);
			$db->query("UPDATE icebb_helpbits SET hname='{$icebb->input['title']}',htext='{$icebb->input['answer']}' WHERE hid='{$icebb->input['id']}'");

			$icebb->admin->redirect("Help topic edited","{$icebb->base_url}act=help_manager");
		}
		
		$db->query("SELECT * FROM icebb_helpbits WHERE hid='{$icebb->input['id']}'");
		$h							= $db->fetch_row();
		
		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');
	
		$icebb->admin->html			= $icebb->admin_skin->start_form('admin.php',array('act'=>'help_manager','func'=>'edit','id'=>$icebb->input['id'],'submit'=>'1',));
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Edit a help topic");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Question</strong>",$icebb->admin_skin->form_input('title',$h['hname'])));
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("<strong>Answer</strong>",$icebb->admin_skin->form_textarea('answer',$h['htext'])));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Save Changes");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function del_topic()
	{
		global $icebb,$db,$std;
		
		$db->query("DELETE FROM icebb_helpbits WHERE hid='{$icebb->input['id']}'");
		$icebb->admin->redirect("Help topic removed","{$icebb->base_url}act=help_manager");
	}
}
?>