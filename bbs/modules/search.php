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
// search module
// $Id: search.php 635 2006-12-06 03:50:49Z mutantmonkey0 $
//******************************************************//

class search
{
	function run()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		require('includes/classes/post_parser.php');
		$post_parser					= new post_parser();
		
		if(!empty($icebb->input['findpost']))
		{
			$pdata						= $db->fetch_result("SELECT * FROM icebb_posts WHERE pid='{$icebb->input['findpost']}'");
			$std->redirect("{$icebb->base_url}topic={$pdata['ptopicid']}&pid={$pdata['pid']}");
		}
		
		$this->html						= $icebb->skin->load_template('search');
		$this->lang						= $std->learn_language('search');
		
		if($icebb->settings['cpu_login_to_search'] && $icebb->user['id'] == 0)
		{
			$std->error($this->lang['access_denied'], true);
		}
		
		$this->per_page					= empty($icebb->input['per_page']) ? 10 : intval($icebb->input['per_page']);
		if(!empty($icebb->input['page']))
		{
			$icebb->input['start']		= intval((intval($icebb->input['page'])*$this->per_page)-$this->per_page);
		}
		
		$this->start					= empty($icebb->input['start']) ? 0 : intval($icebb->input['start']);
		$this->qextra					= " LIMIT {$this->start},{$this->per_page}";
		
		$icebb->nav[]					= $this->lang['search'];
		
		require('includes/classes/search_func.php');
		$this->search_lib				= new search_func($this);
		
		if(!empty($icebb->input['author']))
		{
			$icebb->input['func']		= 'showposts';
		}
		
		if(!empty($icebb->input['search_id']) && empty($icebb->input['func']))
		{
			$icebb->input['func']		= 'results';
		}
		
		switch($icebb->input['func'])
		{
			case 'results':
				$this->display_results();
				break;
			case 'showposts':
				$this->all_posts_user();
				break;
			case 'xmlhttp':
				$this->do_xml_http();
				break;
			case 'newposts':
				$this->new_posts();
				break;
			case 'provider_xml':
				$this->provider_xml();
				break;
			default:
				$forumlist				= $std->get_forum_listing();
			
				//print_r($forumlist);
			
				$forum_listing			= $this->forum_list_children($forumlist,'0');
			
				$this->output			= $this->html->search_start($forum_listing);
				break;
		}
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function forum_list_children($list,$fn)
	{
		global $icebb,$db,$config,$std;
		
		$c						= 0;
		
		if(is_array($list))
		{
			foreach($list as $f)
			{
				// FS#347
				if(!empty($f['redirecturl']))
				{
					continue;
				}
			
				$l			   .= $this->html->forum_row($f);
				$l			   .= $this->forum_list_children($list[$c]['children'],$f['fid']);
				$c++;
			}
		}
		
		return $l;
	}
	
	function display_results()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		$ret						= $this->search_lib->do_search();
		
		if(!is_array($ret))
		{
			$std->error($this->lang['no_results_found']);
		}
		
		foreach($ret['posts'] as $p)
		{
			$search_results		   .= $this->html->search_result($p);
		}
		
		$start_show					= intval($ret['start']);
		$end_show					= $ret['topic_num'] > $this->per_page ? ($ret['start']+$this->per_page>$ret['topic_num'] ? $ret['topic_num'] : $ret['start']+$this->per_page) : $ret['topic_num'];
		
		$pagelinks					= $std->render_pagelinks(array('curr_start'=>$ret['start'],'total'=>$ret['topic_num'],'per_page'=>$this->per_page,'base_url'=>"{$icebb->base_url}act=search&amp;search_id={$ret['search_id']}&"));
		$this->output				= $this->html->results_page(array('query'=>$ret['search_query'],'start'=>$start_show+1,'end'=>$end_show,'total'=>$ret['topic_num'],'time'=>$ret['search_time']),$search_results,$pagelinks);
	}
	
	function do_xml_http()
	{
		global $icebb,$config,$db,$std,$post_parser;
			
		$this->search_lib->add_to_url= '&func=xmlhttp';
		$this->search_lib->per_page	= 10;
			
		$ret						= $this->search_lib->do_search();
		if(!is_array($ret))
		{
			echo $this->html->search_xmlhttp_nonefound();
			exit();
		}
		
		foreach($ret['posts'] as $p)
		{
			$search_results		   .= $this->html->search_xmlhttp_result($p);
		}
		
		$start_show					= $ret['start'] ? $ret['start'] : 1;
		$end_show					= $ret['topic_num'] > 10 ? ($ret['start']+10>$ret['topic_num'] ? $ret['topic_num'] : $ret['start']+10) : $ret['topic_num'];
		
		if($ret['start']-10		   >= 0)
		{
			$pagelinks			   .= $this->html->search_xmlhttp_prev_link($ret['search_id'],$ret['start']-10);
		}
		
		if($ret['start']+10			< $ret['topic_num'])
		{
			$pagelinks			   .= $this->html->search_xmlhttp_next_link($ret['search_id'],$ret['start']+10);
		}
		
		echo $this->html->search_xmlhttp(array('query'=>$ret['search_query'],'start'=>$start_show,'end'=>$end_show,'total'=>$ret['topic_num'],'time'=>$ret['search_time']),$search_results,$pagelinks);
		exit();
	}
	
	function all_posts_user()
	{
		global $icebb,$config,$db,$std,$post_parser;

		$total							= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_posts WHERE pauthor_id='{$icebb->input['author']}'");
		$t3h_query						= $db->query("SELECT p.*,t.*,f.perms FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON f.fid=t.forum WHERE pauthor_id='{$icebb->input['author']}' ORDER BY pdate DESC{$this->qextra}");


		if($db->get_num_rows($t3h_query)<= 0)
		{
			$std->error($this->lang['no_results_found']);
		}

		$pcount							= 0;
		while($p						= $db->fetch_row($t3h_query))
		{
			$p['perms']					= unserialize($p['perms']);
			if($p['perms'][$icebb->user['g_permgroup']]['read']=='1' && empty($p['password']))
			{
				$icebb->config['date_format_post'] = 'l, F j, Y @ g:i A';
				$icebb->config['date_format_joindate']= 'F j, Y';
			
				$r['pdate_formatted']	= $std->date_format($icebb->config['date_format_post'],$r['pdate']);
				$r['joindate_formatted']= $std->date_format($icebb->config['date_format_joindate'],$r['joindate']);
			
				$p['ptext']				= substr($p['ptext'],0,500).'...';
				$p['ptext']				= $post_parser->parse($p['ptext'],$p);
			
				$search_results		   .= $this->html->search_result($p);
				$pcount++;
			}
		}
		
		$start_show						= intval($this->start);
		$topic_num						= $start_show+$pcount;
		$end_show						= $topic_num > $this->per_page ? ($start_show+$this->per_page>$topic_num ? $topic_num : $start_show+$this->per_page) : $topic_num;
		
		$pagelinks						= $std->render_pagelinks(array('curr_start'=>$this->start,'total'=>$total['total'],'per_page'=>$this->per_page,'base_url'=>"{$icebb->base_url}act=search&amp;author={$icebb->input['author']}&amp;"));
		$this->output					= $this->html->results_page(array('author'=>1,'start'=>$start_show+1,'end'=>$end_show,'total'=>$total['total'],'time'=>$ret['search_time']),$search_results,$pagelinks);
	}
	
	function new_posts()
	{
		global $icebb,$db,$std,$timer,$post_parser;
	
		$ftread					= unserialize($icebb->user['ftread']);
	
		if($icebb->input['xmlhttp']	== '1')
		{
			$t3h_query				= $db->query("SELECT p.*,t.*,f.perms FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON f.fid=t.forum{$where} ORDER BY p.pdate DESC LIMIT 30");
			while($p				= $db->fetch_row($t3h_query))
			{
				if($ftread['topics'][$p['tid']]>= $p['lastpost_time'])
				{
					continue;
				}
				
				$p['perms']			= unserialize($p['perms']);
				if($p['perms'][$icebb->user['g_permgroup']]['read']!='1' || !empty($p['password']))
				{
					continue;
				}
			
				if(!empty($p['title']))
				{
					$output		    .= $this->html->newposts_xmlhttp_row($p);
				}
			}
			
			if(empty($output))
			{
				$output				= $this->lang['no_results_found_newposts'];
			}
			
			echo $this->html->newposts_xmlhttp($output);
			exit();
		}
		
		$total					= 0;
		
		$timer->start('searchtime');
		$t3h_query				= $db->query("SELECT p.*,t.*,f.perms FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON f.fid=t.forum{$where} ORDER BY p.pdate DESC LIMIT 30");
		$time					= $timer->stop('searchtime');
		while($p				= $db->fetch_row($t3h_query))
		{
			if($ftread['topics'][$p['tid']]>= $p['lastpost_time'])
			{
				continue;
			}
			
			$p['perms']			= unserialize($p['perms']);
			if($p['perms'][$icebb->user['g_permgroup']]['read']!='1' || !empty($p['password']))
			{
				continue;
			}
		
			if(!empty($p['title']))
			{
				$icebb->config['date_format_post'] = 'l, F j, Y @ g:i A';
				$icebb->config['date_format_joindate']= 'F j, Y';
			
				$r['pdate_formatted']	= date($icebb->config['date_format_post'],$r['pdate']-(3600*4));
				$r['joindate_formatted']= date($icebb->config['date_format_joindate'],$r['joindate']-(3600*4));
			
				$p['ptext']				= substr($post_parser->parse($p['ptext'],$p),0,255).'...';
			
				$search_results		   .= $this->html->search_result($p);
				$total++;
			}
		}
		
		if($total				   <= 0)
		{
			$std->error($this->lang['no_results_found_newposts']);
		}
		
		$end						= $total>$this->per_page ? $this->per_page : $total;
		
		$this->output				= $this->html->results_page(array('query'=>'new posts','start'=>1,'end'=>$end,'total'=>$total,'time'=>$time),$search_results,'');
	}
	
	function provider_xml()
	{
		global $icebb,$db,$std;
		
		@header("Content-type: text/xml");
		echo <<<EOF
<?xml version="1.0" encoding="UTF-8" ?> 
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
  <ShortName>{$icebb->settings['board_name']}</ShortName> 
  <Description>{$icebb->settings['board_name']} Search</Description> 
  <Url type="text/html" template="{$icebb->settings['board_url']}index.php?act=search&amp;func=results&amp;q={searchTerms}" /> 
</OpenSearchDescription>
EOF;
		exit();
	}
}
?>
