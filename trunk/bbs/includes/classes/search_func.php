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
// search functions class
// $Id: search_func.php 563 2006-10-22 02:31:31Z mutantmonkey0 $
//******************************************************//

class search_func extends search
{
	var $search;
	var $add_to_url;
	var $per_page;
	var $fulltext;

	function search_func($meine_lieblingsmutter) // O_O
	{
		global $post_parser;
	
		$this->search				= $meine_lieblingsmutter;
		$this->start				= $this->search->start;
		$this->per_page				= $this->search->per_page;
	}

	function do_search_forum($query,$author)
	{
	}
	
	function do_search()
	{
		global $icebb,$db,$std,$timer,$post_parser;
		
		$ret							= array();
		
		if(empty($icebb->input['search_id']))
		{
			if($this->fulltext			== 0)
			{
				$can_fulltext			= 0;
			}
			else {
				$mysql_version			= $db->get_version();
				
				if($mysql_version	   >= 32323)
				{
					$can_fulltext		= 1;
				}
				else {
					$can_fulltext		= 0;
				}
			
				if($mysql_version	   >= 40010)
				{
					$ftext_boolean		= " IN BOOLEAN MODE";
				}
			}
			
			$query						= trim($icebb->input['q']);
			
			////////////////////////////////////////////////////////
			// Used everywhere
			////////////////////////////////////////////////////////
			
			$where_clauses				= array();
			
			// are we searching in a specific forum?
			if(is_array($icebb->input['search_forums']))
			{
				foreach($icebb->input['search_forums'] as $k => $v)
				{
					$forums[$k]			= intval($v);
				}
			
				$forums					= implode(',',$forums);
			}
			
			if(!empty($forums))
			{
				$where_clauses[]		= "t.forum IN ({$forums})";
			}
			
			// prevent passworded things from showing up
			$where_clauses[]			= "f.password=''";
			
			// are we searching for topics or posts?
			if($icebb->input['search_limit_post_type'] == 'topics')
			{
				$where_clauses[]		= "p.pis_firstpost=1";
			}
			
			// limit date
			if($icebb->input['search_limit_how_long_ago'] > 0)
			{
				$subtract				= intval($icebb->input['search_limit_how_long_ago'])*86400;
				$start_date				= time()-$subtract;
				$where_clauses[]		= "p.pdate>={$start_date}";
			}
			
			////////////////////////////////////////////////////////
			// Special cases
			////////////////////////////////////////////////////////
			
			// searching for a user?
			$icebb->input['search_user']= trim($icebb->input['search_user']);
			if(!empty($icebb->input['search_user']))
			{
				$db->query("SELECT id FROM icebb_users WHERE username='{$icebb->input['search_user']}'");
				$id						= $db->fetch_row();
				
				if($db->get_num_rows() <= 0)
				{
					return false;
				}
			
				$where_clauses[]		= "p.pauthor_id={$id['id']}";
			}
			
			// searching in a topic?
			if(!empty($icebb->input['topic']))
			{
				$where_clauses[]		= "p.ptopicid='{$icebb->input['topic']}'";
			}
			
			if(count($where_clauses)   >= 1)
			{
				$extra_where			= "AND ".implode(' AND ',$where_clauses);
			}
			else if(empty($query))
			{
				// wtf are you trying to do... you can't view EVERY post...
				return false;
			}
			
			if($can_fulltext			== 1)
			{
				$total_query			= "
				SELECT COUNT(*) as total FROM icebb_posts AS p 
				LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid
				LEFT JOIN icebb_forums AS f ON f.fid=t.forum
				WHERE MATCH (ptext) AGAINST('{$query}'{$ftext_boolean})
				{$extra_where}
				";
				
				$orderby				= "score DESC";
				$t3h_query				= "
				SELECT p.*,t.*,f.perms,MATCH (p.ptext) AGAINST('{$query}') as score 
				FROM icebb_posts AS p 
				LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid
				LEFT JOIN icebb_forums AS f ON f.fid=t.forum
				WHERE MATCH (p.ptext) AGAINST('{$query}'{$ftext_boolean})
				{$extra_where}
				ORDER BY {$orderby}{$this->qextra}";
			}
			else {
				$total_query			= "
				SELECT COUNT(*) as total FROM icebb_posts AS p 
				LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid
				LEFT JOIN icebb_forums AS f ON f.fid=t.forum
				WHERE ptext LIKE '%{$query}%'
				{$extra_where}
				";
				
				$t3h_query				= "
				SELECT p.*,t.*,f.perms,MATCH (p.ptext) AGAINST('{$query}') as score 
				FROM icebb_posts AS p 
				LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid
				LEFT JOIN icebb_forums AS f ON f.fid=t.forum
				WHERE p.ptext LIKE '%{$query}%'
				{$extra_where}
				{$this->qextra}";
			}
			
			$total_query				= preg_replace('`\s+`',' ',trim($total_query));
			$teh_query					= preg_replace('`\s+`',' ',trim($t3h_query));
	
			$db->query($total_query);
			$result_count				= $db->fetch_row();
			if($result_count['total']	== 0)
			{
				if($can_fulltext		== 1)
				{
					$this->fulltext		= 0;
					return $this->do_search();
				}
				else {
					return false;
				}
			}
			
			$search_id					= md5(uniqid(microtime(),1));
	
			/*if($db->get_num_rows($t3h_query)<= 0)
			{
				$query					= preg_replace("#[\+\?\*]#",'',$query);
			}*/
			
			$db->insert('icebb_search_results',array(
				'search_id'						=> $search_id,
				'search_query'					=> $query,
				'topic_ids'						=> '',
				'topic_num'						=> $result_count['total'],
				'post_ids'						=> '',
				'search_date'					=> time(),
				'search_uid'					=> $icebb->user['id'],
				'search_uip'					=> $icebb->client_ip,
				'search_sort'					=> $orderby,
				'search_query_cache'			=> addslashes($t3h_query),
			));
			
			$std->redirect("{$icebb->base_url}act=search{$this->add_to_url}&search_id={$search_id}");
			exit();
		}
		else {
			$search_id							= $icebb->input['search_id'];
			$start								= $this->start;
			
			$db->query("SELECT * FROM icebb_search_results WHERE search_id='{$search_id}'");
			$search								= $db->fetch_row();
			
			$search_query						= $search['search_query_cache'];							
			
			// time it
			$timer->start('search');
			// run query
			$db->query($search_query." LIMIT {$start},{$this->per_page}");
			// stop timing it
			$search_time						= $timer->stop('search');
			
			while($p							= $db->fetch_row())
			{
				$p['perms']						= unserialize($p['perms']);
				if($p['perms'][$icebb->user['g_permgroup']]['read']=='1')
				{
					$icebb->config['date_format_post'] = 'l, F j, Y @ g:i A';
					$icebb->config['date_format_joindate']= 'F j, Y';
				
					$p['pdate_formatted']		= date($icebb->config['date_format_post'],$p['pdate']+$std->get_offset());
					$p['joindate_formatted']	= date($icebb->config['date_format_joindate'],$p['joindate']+$std->get_offset());
				
					//$p['ptext']					= substr($post_parser->parse($p['ptext'],$p),0,255).'...';
					$p['ptext']					= $post_parser->parse($p['ptext']);
				
					$search_results[]			= $p;
				}
			}
			
			$ret['start']						= $start;
			$ret['search_id']					= $search_id;
			$ret['search_query']				= $search['search_query'];
			$ret['topic_num']					= $search['topic_num'];
			$ret['search_time']					= $search_time;
			$ret['posts']						= $search_results;
		}
		
		return $ret;
	}
}
?>
