<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.kenkarpg.info // 1.0 Beta 6
//******************************************************//
// recounter admin module
// $Id: recount.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class recount
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang					= $icebb->admin->learn_language('global');
		$this->html					= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title	= "Recount";
		
		switch($icebb->input['func'])
		{
			case 'topicsandreplies':
				$this->recount_topicsandr();
				break;
			case 'treplies':
				$this->recount_treplies();
				break;
			default:
				$this->show_home();
				break;
		}
		
		$icebb->admin->html			= $this->html->header().$icebb->admin->html.$this->html->footer();

		$icebb->admin->output();
	}
	
	function show_home()
	{
		global $icebb,$config,$db,$std;

		$icebb->admin->html			= <<<EOF
<ul>
	<li><a href='{$icebb->base_url}act=recount&amp;func=topicsandreplies'>Topics and replies on forum listing</a></li>
	<li><a href='{$icebb->base_url}act=recount&amp;func=treplies'>Topic replies in all forums</a></li>
</ul>

EOF;
	}
	
	function recount_topicsandr()
	{
		global $icebb,$config,$db,$std;
		
		$numtopics			= array();
		$numreplies			= array();
		
		$db->query("SELECT * FROM icebb_topics");
		while($t			= $db->fetch_row())
		{
			$topic[$t['tid']]= $t;
			$numtopics[$t['forum']]++;
		}
		
		$db->query("SELECT * FROM icebb_posts WHERE pis_firstpost!=1");
		while($p			= $db->fetch_row())
		{
			$fid			= $topic[$p['ptopicid']]['forum'];
			$numreplies[$fid]++;
		}

		$fq					= $db->query("SELECT * FROM icebb_forums");
		while($f			= $db->fetch_row($fq))
		{
			$ntopics		= intval($numtopics[$f['fid']]);
			$nreplies		= intval($numreplies[$f['fid']]);
			$icebb->admin->html.= "{$f['fid']}: {$ntopics}/{$nreplies}<br />";
			$db->query("UPDATE icebb_forums SET topics={$ntopics},replies={$nreplies} WHERE fid={$f['fid']}"); 
		}
	}
	
	function recount_treplies()
	{
		global $icebb,$config,$db,$std;
		
		$numreplies			= array();
		
		$db->query("SELECT tid FROM icebb_topics");
		while($t			= $db->fetch_row())
		{
			$topic[$t['tid']]= $t;
		}
		
		$db->query("SELECT ptopicid FROM icebb_posts WHERE pis_firstpost!=1");
		while($p			= $db->fetch_row())
		{
			$tid			= $p['ptopicid'];
			$numreplies[$tid]++;
		}

		foreach($topic as $t)
		{
			$nreplies		= intval($numreplies[$t['tid']]);
			$icebb->admin->html.= "{$t['tid']}: {$nreplies}<br />";
			$db->query("UPDATE icebb_topics SET replies={$nreplies} WHERE tid={$t['tid']}"); 
		}
	}
}
?>