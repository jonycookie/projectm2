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
// SQL control admin module
// $Id: sql.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class sql
{
	var $root_users;
	
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->settings['log_sql_commands']= 1;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('sql');
		
		$icebb->admin->page_title	= "Manage SQL";
		
		/*// I'm lazy, so for now...
		if(file_exists('../phpmyadmin/index.php'))
		{
			@header("Location: ../phpmyadmin/index.php");
		}
		else {
			$icebb->admin->error("Go use phpMyAdmin :P <br />(this isn't done yet)");
		}*/
		
		if($icebb->adsess['is_root']!=1)
		{
			//$std->log('admin',"Tried to access SQL page without permissions",$icebb->adsess['user']);
			$icebb->admin->error("You are not authorized to view this page. Root permissions required.");
		}
		
		if(!empty($icebb->input['table']))
		{
			$icebb->input['func']	= 'table';
		}
		
		switch($icebb->input['func'])
		{
			case 'table':
				$this->do_table();
				break;
			case 'runquery':
				$this->run_query();
				break;
			case 'logs':
				$this->do_logs();
				break;
			default:
				$this->main();
				break;
		}
		
		$icebb->admin->html			= $this->html->display($icebb->admin->html);
		
		$icebb->admin->output();
	}
	
	function main()
	{
		global $icebb,$db,$std,$config;
		
		$dblist					= $db->list_tables($db->database);
		while($currdb			= $db->fetch_row($dblist))
		{
			$tables[]			= $currdb['Tables_in_'.$db->database];
		}
		
		foreach($tables as $tbl)
		{
			if(substr($tbl,0,strlen($db->prefix))==$db->prefix)
			{
				$letables[]		= "<a href='{$icebb->base_url}act=sql&amp;table={$tbl}'>{$tbl}</a>";
			}
		}
		
		$db->query("SELECT * FROM icebb_logs WHERE type='sql' ORDER BY time DESC LIMIT 5");
		while($log					= $db->fetch_row())
		{
			$logs[]					= $log;
		}
		
		$icebb->admin->html		= $this->html->database_display($db->database,$letables,$logs);
	}
	
	function do_table()
	{
		global $icebb,$db,$std;
		
		$icebb->input['table']		= preg_replace("`('|\"|\$|\s)`",'',$icebb->input['table']);
		
		if(!empty($icebb->input['browse']))
		{
			$titles[]				= "<a href='{$icebb->base_url}act=sql&amp;table={$icebb->input['table']}'>Overview</a>";
			$titles[]				= "Browse";
		}
		else {
			$titles[]				= "Overview";
			$titles[]				= "<a href='{$icebb->base_url}act=sql&amp;table={$icebb->input['table']}&amp;browse=1'>Browse</a>";
		}
		
		$icebb->admin->html			= $icebb->admin_skin->start_table();
		$icebb->admin->html		   .= $icebb->admin_skin->table_row($titles,'row2');
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
		
		if(!empty($icebb->input['browse']))
		{
			$db->query("SHOW COLUMNS FROM {$icebb->input['table']}");
			while($column			= $db->fetch_row())
			{
				//print_r($column);
				//echo $column['Field'];
				$icebb->admin_skin->table_titles[]= array($column['Field'],'');
			}
			
			$icebb->admin->html	   .= $icebb->admin_skin->start_table($icebb->input['table']);
			
			$db->query("SELECT * FROM {$icebb->input['table']}");
			while($r				= $db->fetch_row())
			{
				$icebb->admin->html.= $icebb->admin_skin->table_row($r);
			}
			
			$icebb->admin->html	   .= $icebb->admin_skin->end_table();
		}
		else {
			$icebb->admin_skin->table_titles[]= array("Name",'30%');
			$icebb->admin_skin->table_titles[]= array("Type",'10%');
			$icebb->admin_skin->table_titles[]= array("Null?",'5%');
			$icebb->admin_skin->table_titles[]= array("Key",'5%');
			$icebb->admin_skin->table_titles[]= array("Default",'20%');
			$icebb->admin_skin->table_titles[]= array("Extra",'20%');
		
			$icebb->admin->html	   .= $icebb->admin_skin->start_table($icebb->input['table']);
			
			$db->query("SHOW KEYS FROM {$icebb->input['table']}");
			while($key				= $db->fetch_row())
			{
				$tkeys[$key['Column_name']]= $key;
			}
			
			$db->query("SHOW COLUMNS FROM {$icebb->input['table']}");
			while($column			= $db->fetch_row())
			{
				if(!empty($column['Null']))
				{
					$column['nullval']= 'No';
				}
				else {
					$column['nullval']= 'Yes';
				}
				
				$column['keyval']= $tkeys[$column['Field']]['Key_name'];
				
				$icebb->admin->html.= $icebb->admin_skin->table_row(array("<strong>{$column['Field']}</strong>",$column['Type'],$column['nullval'],$column['keyval'],$column['Default'],$column['Extra']));
			}
			
			$icebb->admin->html	   .= $icebb->admin_skin->end_table();
		}
	}
	
	function run_query()
	{
		global $icebb,$db,$std;

		if(empty($icebb->input['query']))
		{
			$this->run_query_result		= "The query may not be left empty.";
		}
		else if(preg_match("`(DROP|CREATE|FLUSH)`i",$icebb->input['query']))
		{
			$this->run_query_result		= "Drop queries, create queries, and flush queries are disabled to ensure the safety of your board";
		}
		else {
			$mainq						= $db->query($icebb->input['query']);
		
			if(preg_match("`SELECT (.+?) FROM ([A-Za-z0-9_]*)`i",$icebb->input['query'],$match))
			{
				$table 					= $match[2];
			
				$db->query("SHOW COLUMNS FROM {$table}");
				while($column			= $db->fetch_row())
				{
					//print_r($column);
					//echo $column['Field'];
					$icebb->admin_skin->table_titles[]= array($column['Field'],'');
				}
				
				$icebb->admin->html	   .= $icebb->admin_skin->start_table("{$table} (Results of query {$icebb->input['query']})");
				
				while($r				= $db->fetch_row($mainq))
				{
					$icebb->admin->html.= $icebb->admin_skin->table_row($r);
				}
				
				$icebb->admin->html	   .= $icebb->admin_skin->end_table();
			
				$this->run_query_result	= "The select query was executed successfully; ".$db->get_num_rows($mainq)." rows were found. Results are above.";
			}
			else {
				$this->run_query_result	= "The query was executed successfully";
			}
		}
		
		$icebb->admin->html	   .= $icebb->admin_skin->start_form('admin.php',array('act'=>'sql','func'=>'runquery'));
		$icebb->admin->html	   .= $icebb->admin_skin->start_table("Run a query");
		if(!empty($this->run_query_result))
		{
			$icebb->admin->html.= $icebb->admin_skin->table_row($this->run_query_result,'row2');
		}
		$icebb->admin->html	   .= $icebb->admin_skin->table_row($icebb->admin_skin->form_textarea('query',$icebb->input['query'],5,50),''," style='text-align:center'");
		$icebb->admin->html	   .= $icebb->admin_skin->end_form("Run Query");
		$icebb->admin->html	   .= $icebb->admin_skin->end_table();
	}
	
	function do_logs()
	{
		global $icebb,$db,$std;
			
		$db->query("SELECT * FROM icebb_logs WHERE type='sql' ORDER BY time DESC");
		while($log					= $db->fetch_row())
		{
			$logs[]					= $log;
		}
		
		$icebb->admin->html			= $this->html->do_logs($logs);
	}
}
?>