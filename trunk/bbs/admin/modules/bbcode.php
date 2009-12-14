<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9
//******************************************************//
// custom bbcode admin module
// $Id: bbcode.php 597 2005-09-02 21:41:23Z mutantmonkey $
//******************************************************//

define('ROOT_PATH'		, '../');

if(!defined('IN_ICEBB'))
{
	die('This file may not be accessed directly.');
}

class bbcode
{
	function run()
	{
		global $icebb,$db,$std;
		
		$this->lang				= $icebb->admin->learn_language('global');
		$this->html				= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->error('Not done yet!');
		
		switch($icebb->input['func'])
		{
			case 'add':
				$this->add_bbcode();
				break;
			case 'edit':
				$this->edit_bbcode();
				break;
			case 'del':
				$this->del_bbcode();
				break;
			default:
				$this->list_bbcode();
				break;
		}
		
		$icebb->admin->html		= $this->html->header().$icebb->admin->html.$this->html->footer();
		
		$icebb->admin->output();
	}

	function list_bbcode()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->admin->page_title			= "Manage BBCode";

		$icebb->admin_skin->table_titles[]	= array("Word",'30%');
		$icebb->admin_skin->table_titles[]	= array("Replacement",'30%');
		$icebb->admin_skin->table_titles[]	= array("&nbsp;",'40%');

		$icebb->admin->html					= $icebb->admin_skin->start_table("Custom BBCode");
		
		$bwq								= $db->query("SELECT * FROM icebb_bbcode");
		while($b							= $db->fetch_row($bwq))
		{
			$icebb->admin->html			   .= $icebb->admin_skin->table_row(array($b['bw_word'],$b['bw_replacement'],"<div style='text-align:right'><a href='{$icebb->base_url}act=bbcode&amp;func=edit&amp;id={$b['bb_id']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=wordfilters&amp;func=del&amp;id={$bw['bb_id']}'>Remove</a></div>"));
		}
		
		$icebb->admin->html				   .= $icebb->admin_skin->table_row("<a href='{$icebb->base_url}act=bbcode&amp;func=add'>New BBCode</a>",'buttonstrip'," colspan='3'");
		$icebb->admin->html				   .= $icebb->admin_skin->end_table();
	}
	
	function add_bbcode()
	{
		global $icebb,$config,$db,$std;
		
		if(!empty($icebb->input['submit']))
		{
		}
		
		$icebb->admin->page_title			= "Add BBCode";

		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');

		$icebb->admin->html					= $icebb->admin_skin->start_table("Add BBCode");
		
		$bwq								= $db->query("SELECT * FROM icebb_bbcode");
		while($b							= $db->fetch_row($bwq))
		{
			$icebb->admin->html			   .= $icebb->admin_skin->table_row(array($b['bw_word'],$b['bw_replacement'],"<div style='text-align:right'><a href='{$icebb->base_url}act=bbcode&amp;func=edit&amp;id={$b['bb_id']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=wordfilters&amp;func=del&amp;id={$bw['bb_id']}'>Remove</a></div>"));
		}
		
		$icebb->admin->html				   .= $icebb->admin_skin->end_table();
	}
	
	function del_bbcode()
	{
		global $icebb,$db,$std;
		
		$db->query("DELETE FROM icebb_bbcode WHERE id='{$icebb->input['id']}' LIMIT 1");
		
		$std->redirect("BBCode Removed","{$icebb->base_url}act=bbcode");
	}
	
	function rebuild_cache()
	{
		global $icebb,$db,$config,$std;
	
		$wordfilters						= array();
		$db->query("SELECT * FROM icebb_bbcode");
		while($bw						= $db->fetch_row())
		{
			$bbcode[]					= $bw;
		}
		
		$std->recache($bbcode,'bbcode');
	}
}
?>
