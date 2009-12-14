<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.0 Beta 7
//******************************************************//
// update RSS feeds task
// $Id: update_rss.php 195 2005-07-24 03:51:11Z mutantmonkey $
//******************************************************//

/*
Note about this task:
---------------------
Do _NOT_ run this task more than every 15 minutes, doing so will cause webmasters
to get pissed at you and could get you banned from their site
*/

class task_update_rss
{
	function run()
	{
		global $icebb,$db,$std;
	
		require($icebb->settings['board_path'].'includes/classes/xml.lib.php');
		$xml						= new xml_parser();
	
		// read RSS
		$rread						= $db->query("SELECT fid,rss FROM icebb_forums WHERE rss!=''");
		while($f					= $db->fetch_row($rread))
		{
			$rss					= unserialize($f['rss']);
			$rss['fid']				= $f['fid'];
		
			$to_read[]				= $rss;
		}
		
		if(is_array($to_read))
		{
			$topicsq		= $db->query("SELECT * FROM icebb_topics ORDER BY tid DESC LIMIT 1");
			$last_topic		= $db->fetch_row($topicsq);
			$tid			= $last_topic['tid']+1;
			
			$postsq			= $db->query("SELECT * FROM icebb_posts ORDER BY pid DESC LIMIT 1");
			$last_post		= $db->fetch_row($postsq);
			$pid			= $last_post['pid']+1;
		
			foreach($to_read as $r)
			{
				$doc					= $xml->xml2array(file_get_contents($r['url']));
				$to_insert				= $doc['channel'][0]['item'];
				if(is_array($to_insert))
				{
					foreach($to_insert as $i => $item)
					{
						$pubdate			= empty($item['pubDate']) ? $item['dc:date'] : $item['pubDate'];
						$pubdate			= strtotime($pubdate);
					
						if($pubdate		   <= $r['last_check'])
						{
							break;
						}
					
						$items[$i]['title']	= $item['title'];
						$items[$i]['link']	= $item['link'];
						$items[$i]['description']= $item['description'];
						$items[$i]['pubdate']= $pubdate;
					}
				}
				
				if(is_array($items))
				{
					krsort($items);
					
					foreach($items as $i)
					{
						$db->insert('icebb_topics',array(
										'tid'			=> $tid,
										'forum'			=> $r['fid'],
										'icon'			=> '',
										'title'			=> addslashes($i['title']),
										'description'	=> '',
										'snippet'		=> substr(addslashes($i['description']),0,255),
										'starter'		=> "RSS Feed",
										'lastpost_time'	=> $i['pubdate'],
										'lastpost_author'=> "RSS Feed",
										'has_poll'		=> 0,
										'views'			=> 0,
										));
										
						$db->insert('icebb_posts',array(
										'pid'			=> $pid,
										'ptopicid'		=> $tid,
										'pauthor_id'	=> '0',
										'pauthor'		=> "RSS Feed",
										'pauthor_ip'	=> '127.0.0.1',
										'pdate'			=> $i['pubdate'],
										'ptext'			=> addslashes($i['description'])."\n\n[url=".addslashes($i['link'])."]".addslashes($i['link'])."[/url]",
										'pis_firstpost'	=> '1',
										));
						
						$lastpost_time	= intval($i['pubdate']);
						
						$tid++;
						$pid++;
					}
				
					$r2fid						= $r['fid'];
					unset($r['fid']);	
					$r['last_check']			= $lastpost_time;
					$db->query("UPDATE icebb_forums SET rss='".addslashes(serialize($r))."',topics=topics+".count($items).",lastpostid='{$tid}',lastpost_title='".addslashes($i['title'])."',lastpost_time='{$lastpost_time}',lastpost_author='RSS Feed' WHERE fid='{$r2fid}'");
	
					// update stats
					$cache_result3				= $db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
					$cache_result31				= $db->fetch_result("SELECT COUNT(*) as topics FROM icebb_topics");
					$cache_result32				= $db->fetch_result("SELECT COUNT(*) as replies FROM icebb_posts WHERE pis_firstpost!=1");
					$icebb->cache['stats']['posts']= $cache_result3['posts'];
					$icebb->cache['stats']['topics']= $cache_result31['topics'];
					$icebb->cache['stats']['replies']= $cache_result32['replies'];
					$std->recache($icebb->cache['stats'],'stats');
				}
			}
		}
	}
}
?>