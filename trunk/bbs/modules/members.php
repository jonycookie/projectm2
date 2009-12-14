<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.1
//******************************************************//
// member list module
// $Id: members.php 493 2006-09-24 02:48:17Z mutantmonkey0 $
//******************************************************//

class members
{
	function run()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		$this->html						= $icebb->skin->load_template('members');
		$this->lang						= $std->learn_language("members");
		
		$icebb->nav[]					= $this->lang['member_list'];
		
		$columns						= array('username','title','user_group','location','gender','url');
		$columns_2						= array();
		foreach($columns as $k)
		{
			$columns_2[$k]				= array();
		}
		
		$per_page						= 30;
		if(!empty($icebb->input['page']))
		{
			$icebb->input['start']		= intval((intval($icebb->input['page'])*$per_page)-$per_page);
		}
		$start							= empty($icebb->input['start']) ? 0 : intval($icebb->input['start']);
		$qlimit							= " LIMIT {$start},{$per_page}";
		
		$qextra							= " ORDER BY username ASC";
		
		$fleh							= $icebb->input;
		unset($fleh['act']);
		unset($fleh['ICEBB_QUERY_STRING']);
		unset($fleh['ICEBB_USER_IP']);
		foreach($fleh as $k => $g)
		{
			if(!in_array($k,$columns)) continue;
		
			$columns_2[$k]				= array($fleh[$k.'_filter'],$g);
			
			$url_extra				   .= "{$k}={$g}&amp;";
		
			if(empty($g)) continue;
			
			$g							= $db->escape_string($g);
		
			switch($fleh[$k.'_filter'])
			{
				case 'startswith':
					$where_clauses[]	= "{$k} LIKE '{$g}%'";
					break;
				case 'contains':
					$where_clauses[]	= "{$k} LIKE '%{$g}%'";
				default:
					$where_clauses[]	= "{$k}='{$g}'";
					break;
			}
		}
		
		$where_clauses[]				= "id!=0";
		
		$this->qwhere					= implode(' AND ',$where_clauses);
		if(!empty($this->qwhere))
		{
			$this->qwhere				= " WHERE {$this->qwhere}";
		}

		$total							= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_users{$this->qwhere}{$qextra}");

		$db->query("SELECT u.*,g.* FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid{$this->qwhere}{$qextra}{$qlimit}");
		while($u						= $db->fetch_row())
		{
			$u['MemberJoined']  		= $std->date_format($icebb->user['date_format'],$u['joindate']);
			$u['MemberTitle']			= $u['title'];
			$members				   .= $this->html->user_row($u);
	
		}
	
		$pagelinks						= $std->render_pagelinks(array('curr_start'=>$start,'total'=>$total['total'],'per_page'=>$per_page,'base_url'=>"{$icebb->base_url}act=members&amp;{$urlextra}"));
		$this->output					= $this->html->memberlist($members,$pagelinks,$columns_2);
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
}
?>
