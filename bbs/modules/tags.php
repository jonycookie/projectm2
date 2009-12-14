<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.1
//******************************************************//
// browse by tag module
// $Id$
//******************************************************//

class tags
{
	function run()
	{
		global $icebb,$db,$std;
		
		require('includes/classes/post_parser.php');
		$this->post_parser				= new post_parser();
		
		require('includes/classes/tagging.inc.php');
		$this->tagging					= new tagging;
		
		$this->lang						= $std->learn_language('tags','forum');
		$this->html						= $icebb->skin->load_template('tags');
		
		$this->per_page					= empty($icebb->input['per_page']) ? 10 : intval($icebb->input['per_page']);
		$this->start					= empty($icebb->input['start']) ? 0 : intval($icebb->input['start']);
		$this->qextra					= " LIMIT {$this->start},{$this->per_page}";
		
		$icebb->nav[]					= "<a href='{$icebb->base_url}act=tags'>{$this->lang['tags']}</a>";
		
		if(!empty($icebb->input['tag']))
		{
			$this->do_tag($icebb->input['tag']);
		}
		else {
			$this->listall();
		}
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function do_tag($tag_id)
	{
		global $icebb,$db,$std;
		
		$tag					= $db->fetch_result("SELECT * FROM icebb_tags WHERE tag='{$tag_id}' AND type='topic'");
		$total					= $db->fetch_result("SELECT COUNT(*) as total FROM icebb_tagged WHERE tag_id='{$tag['id']}'{$this->qextra}");
		
		$icebb->nav[]			= $tag['tag'];
		
		$db->query("SELECT tag.*,t.* FROM icebb_topics AS t LEFT JOIN icebb_tagged AS tag ON t.tid=tag.tag_objid WHERE tag.tag_id='{$tag['id']}'{$this->qextra}");
		if($db->get_num_rows()<= 0)
		{
			$std->error($this->lang['no_topics_with_tag']);
		}

		while($t				= $db->fetch_row())
		{
			//$t['perms']			= unserialize($t['perms']);
			//if($t['perms'][$icebb->user['g_permgroup']]['read']=='1')
			//{
				$icebb->config['date_format_post'] = 'l, F j, Y @ g:i A';
				$icebb->config['date_format_joindate']= 'F j, Y';
			
				$t['pdate_formatted']	= $std->date_format($icebb->config['date_format_post'],$t['pdate']);
				$t['joindate_formatted']= $std->date_format($icebb->config['date_format_joindate'],$t['joindate']);
					
				$t['ptext']				= substr($this->post_parser->parse($t['ptext'],$t),0,255).'...';
				
				$t['lastpost_time_formatted']= $std->date_format($icebb->user['date_format'],$t['lastpost_time']);
			
				$topic_cutoff= $microwaved[$t['tid']]>$icebb->user['last_visit'] ? $microwaved[$t['tid']] : $icebb->user['last_visit'];
				$topic_cutoff= $microwaved[$t['tid']];
			
				if($t['lastpost_time']>$topic_cutoff && $t['lastpost_time']!='0')
				{
					$marker	= "<macro:t_new />";
				}
				else {
					$marker	= "<macro:t_nonew />";
				}
			
				$this_row				= $this->html->tag_row($t);
			
				if($icebb->user['g_is_mod']=='1' || $this->is_mod==1)
				{
					$this_row			= str_replace('<{MOD_OPTIONS}>',$this->html->moderator_tick_perforum($t['tid']),$this_row);
				}
				else {
					$this_topic			= str_replace('<{MOD_OPTIONS}>','',$this_row);
				}
			
				$results			   .= $this_row;
			//}
		}
		
		$end							= ($this->start+$this->per_page>$total['total']) ? $total['total'] : $this->start+$this->per_page;
		
		$pagelinks						= $std->render_pagelinks(array('curr_start'=>$this->start,'total'=>$total['total'],'per_page'=>$this->per_page,'base_url'=>"{$icebb->base_url}act=search&amp;author={$icebb->input['author']}&amp;"));
		$this->output					= $this->html->show_tag(array('query'=>'','start'=>$this->start+1,'end'=>$end,'total'=>$total['total'],'time'=>0),$tag,$results,$pagelinks);
	}
	
	function listall()
	{
		global $icebb,$db,$std;
		
		$db->query("SELECT * FROM icebb_tags WHERE type='topic' ORDER BY tag");
		while($tg						= $db->fetch_row())
		{
			$tags[]						= $tg;
			$tag_count[]				= $tg['count'];
		}
		
		if(empty($tags))
		{
			$std->error($this->lang['no_topics_tagged']);
		}
		
		$min_font_size					= 10;
		$max_font_size					= 26;
		$min_qty						= min($tag_count);
		$max_qty						= max($tag_count);
		$divideby						= ($max_qty-$min_qty)==0 ? 1 : $max_qty-$min_qty;
		$step							= ($max_font_size-$min_font_size)/$divideby;
		foreach($tags as $t)
		{
			$t_url						= urlencode($t['tag']);
			$font_size					= $min_font_size+($t['count']-min($tag_count))*$step;
			$tag_html				   .= $this->html->listall_tag($t,$t_url,$font_size);
		}
		
		$this->output					= $this->html->listall($tag_html);
	}
}
?>