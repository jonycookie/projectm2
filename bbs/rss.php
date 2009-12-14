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
// RSS module
// $Id: rss.php 825 2007-05-18 09:06:08Z daniel159 $
//******************************************************//

error_reporting(0);

define('IN_ICEBB'		, 1);
define('PATH'			, '');
define('PATH_TO_ICEBB'	, '');

require(PATH.'config.php');

require(PATH.'includes/classes/timer.php');
require(PATH."includes/database/{$config['db_engine']}.db.php");
$engine							= "db_{$config['db_engine']}";
$db								= new $engine();

// WRAP IT UP
// ----------
// Wrap everything up in a nice reusable class here...

$icebb					= new icebb();
class icebb
{
	var $config;
	var $skin;
	var $skin_data;
	var $html_global;
	var $lang;
	var $input;
	var $html;
	var $base_url		= "index.php?";
	var $debug_html;
	var $cache;
	var $hooks;

	function icebb()
	{
		global $db,$std,$config,$input,$session;
	
		$this->config		= $config;
	}
}

$cache_query				= $db->query("SELECT * FROM icebb_cache");
while($cached_data			= $db->fetch_row($cache_query))
{
	$icebb->cache[$cached_data['name']]= unserialize(stripslashes($cached_data['content']));
}

$icebb->settings			= $icebb->cache['settings'];
$icebb->forums				= $icebb->cache['forums'];

require(PATH.'includes/functions.php');
$std						= new std_func;
$icebb->input				= $std->capture_input();
$icebb->client_ip			= $icebb->input['ICEBB_USER_IP'];
$icebb->skin->skin_id		= 'default';

require(PATH.'includes/classes/post_parser.php');
$post_parser				= new post_parser();

require('includes/classes/hooks.inc.php');
$hooks						= new hooks;
$icebb->hooks				= $hooks;

require('includes/classes/sessions.inc.php');
$sessfunc					= new sessions;
$session_id					= empty($icebb->input['s']) ? $std->eatCookie('sessid') : $icebb->input['s'];
//print_r($session_id); die();
$icebb->user				= $sessfunc->load($session_id);

$icebb->input				= $std->capture_input();
// END WRAP IT UP
// --------------

$rss							= new rss();
class rss
{
	function rss()
	{
		global $icebb;
	
		// limit
		$this->limit			= empty($icebb->input['limit']) ? 15 : intval($icebb->input['limit']);
	
		if(!empty($icebb->input['forum']))
		{
			$this->where_clauses[]	= "t.forum='{$icebb->input['forum']}'";
		}
		
		if(!empty($icebb->input['author']) && !empty($icebb->input['topic']))
		{
			$this->where_clauses[]= "p.pauthor_id=".intval($icebb->input['author']);
		}
		
		if(isset($icebb->input['topics_only']))
		{
			$this->where_clauses[]	= "p.pis_firstpost=1";
		}
			
		if(!empty($this->where_clauses) && is_array($this->where_clauses))
		{
			$this->where_clause		= " WHERE ".implode(' AND ',$this->where_clauses)." ";
		}
		
		if(!empty($icebb->input['topic']))
		{
			$this->topic($icebb->input['topic']);
		}
		else if(!empty($icebb->input['author']))
		{
			$this->author();
		}
		else {
			$this->home();
		}


		@header("Content-type: text/xml");
		echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<rss version='2.0'>
	<channel>
		<title>{$icebb->settings['board_name']}{$this->title_extra}</title>
		<link>{$icebb->settings['board_url']}{$this->link_extra}</link>
		<description>RSS 2.0 feed of {$icebb->settings['board_name']}</description>
		<language>en</language>
		<generator>IceBB</generator>

{$this->output}
	</channel>
</rss>
EOF;
	}
	
	function topic($tid)
	{
		global $icebb,$db,$post_parser;

		$ton				= 0;
	
		$db->query("SELECT p.*,u.username as pauthor2 FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id WHERE ptopicid='{$tid}' ORDER BY pid DESC LIMIT {$this->limit}");
		while($pdata		= $db->fetch_row())
		{
			$p[]			= $pdata;
			
			if(empty($pdata['pauthor']))
			{
				$pdata['pauthor']= $pdata['pauthor2'];
			}
			
			$pubdate		= date('R',$p['pdate']);
			$pdata['ptext']	= $post_parser->parse($pdata['ptext']);
			
/*switch($icebb->input['type'])
{

case 'rss0.92':		*/
$datas		   .= <<<EOF
		<item>
			<title>Post #{$pdata['pid']} by {$pdata['pauthor']}</title>
			<description>
<![CDATA[
{$pdata['ptext']}
]]>
			</description>
			<link>{$icebb->settings['board_url']}index.php?topic={$pdata['ptopicid']}&amp;pid={$pdata['pid']}</link>
		</item>

EOF;
/*break;

default:		
$datas		   .= <<<EOF
		<item>
			<title>Post #{$pdata['pid']} by {$pdata['pauthor']}</title>
			<description>
<![CDATA[
{$pdata['ptext']}
]]>
			</description>
			<link>{$icebb->settings['board_url']}index.php?topic={$pdata['ptopicid']}&amp;pid={$pdata['pid']}</link>
			<pubDate>{$pubdate}</pubdate>
			<guid>{$pdata['pid']}</guid>
		</item>

EOF;
break;

}*/
		}
	
		$db->query("SELECT t.*,f.perms FROM icebb_topics AS t LEFT JOIN icebb_forums AS f ON t.forum=f.fid WHERE tid='{$tid}'");
		$t				= $db->fetch_row();
		$t['perms']		= unserialize($t['perms']);
		if($t['perms'][$icebb->user['g_permgroup']]['read']==1)
		{
			$this->title_extra= " &gt; {$t['title']}";
			$this->link_extra= "&amp;topic={$t['tid']}";
			$this->output= $datas;
		}
	}
	
	function author()
	{
		global $icebb,$db,$post_parser;
	
		$ton				= 0;
	
		$db->query("SELECT p.*,u.username as pauthor2,t.tid,t.title,t.forum,f.perms FROM icebb_posts AS p LEFT JOIN icebb_users AS u ON u.id=p.pauthor_id LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON t.forum=f.fid WHERE p.pauthor_id=".intval($icebb->input['author'])." ORDER BY p.pdate DESC LIMIT {$this->limit}");
		while($r			= $db->fetch_row())
		{
			$r['perms']		= unserialize($r['perms']);
			if($r['perms'][$icebb->user['g_permgroup']]['read']==1)
			{
				$pubdate		= date('r',$r['pdate']);
				$p[$r['tid']]['ptext']= $post_parser->parse($p[$r['tid']]['ptext']);
				$r['pauthor']	= empty($r['pauthor']) ? $r['pauthor2'] : $r['pauthor'];
				
				if($r['pis_firstpost']!='1')
				{
					$r['title']	= "Re: {$r['title']}";
				}
			
				$this->output  .= <<<EOF
		<item>
			<title>{$r['title']}</title>
			<description>
<![CDATA[
{$r['ptext']}
]]>
			</description>
			<link>{$icebb->settings['board_url']}index.php?topic={$r['tid']}&amp;pid={$r['pid']}</link>
			<pubDate>{$pubdate}</pubDate>
			<author>{$r['pauthor']}</author>
			<guid isPermaLink='false'>{$r['pid']}</guid>
		</item>

EOF;

				$ton++;
			}
		}
	}
	
	function home()
	{
		global $icebb,$db,$post_parser;
	
		$ton							= 0;
		
		if(!empty($icebb->input['alt']))
		{
			$favorite_forums			= array();
			$db->query("SELECT * FROM icebb_favorites WHERE favuser='{$icebb->user['id']}' AND favtype='forum'");
			while($fav					= $db->fetch_row())
			{
				$favorite_forums[]		= $fav['favobjid'];
			}
			
			if(!empty($favorite_forums))
			{
				$this->where_clause		= " WHERE f.fid IN(".join(',',$favorite_forums).")";
			}
		}
	
		$db->query("SELECT p.*,t.tid,t.title,t.forum,f.perms,u.username AS pauthor2 FROM icebb_posts AS p LEFT JOIN icebb_topics AS t ON p.ptopicid=t.tid LEFT JOIN icebb_forums AS f ON t.forum=f.fid LEFT JOIN icebb_users AS u ON p.pauthor_id=u.id{$this->where_clause} ORDER BY p.pdate DESC LIMIT {$this->limit}");
		while($r						= $db->fetch_row())
		{
			$r['perms']					= unserialize($r['perms']);
			if($r['perms'][$icebb->user['g_permgroup']]['read']==1)
			{
				$pubdate				= date('r',$r['pdate']);
				$r['pauthor']			= !empty($r['pauthor']) ? $r['pauthor'] : $r['pauthor2'];
				$r['ptext']				= $post_parser->parse($r['ptext']);
				
				if(empty($r['ptext']))
				{
					continue;
				}
				
				if($r['pis_firstpost']!='1')
				{
					$r['title']	= "Re: {$r['title']}";
				}
			
switch($icebb->input['type'])
{

case 'rss0.92':		
$this->output		   .= <<<EOF
		<item>
			<title>{$r['title']} by {$r['pauthor']}</title>
			<description>
<![CDATA[
{$r['ptext']}
]]>
			</description>
			<pubdate>{$r['pdate']}</pubdate>
			<link>{$icebb->settings['board_url']}index.php?topic={$r['tid']}&amp;pid={$r['pid']}</link>
		</item>

EOF;
break;

default:		
$this->output		   .= <<<EOF
		<item>
			<title>{$r['title']}</title>
			<description>
<![CDATA[
{$r['ptext']}
]]>
			</description>
			<link>{$icebb->settings['board_url']}index.php?topic={$r['tid']}&amp;pid={$r['pid']}</link>
			<pubDate>{$pubdate}</pubDate>
			<author>{$r['pauthor']}</author>
			<guid isPermaLink='false'>{$r['pid']}</guid>
		</item>

EOF;
break;

}

				$ton++;
			}
		}
	}
	
	// INTERNAL FUNCTIONS
	
	function _parse($string)
	{
		if(preg_match('`[\[\]\|<>]`i',$string))
		{
			$string			= "<![CDATA[{$string}]]>";
		}
		
		return $string;
	}
}
?>
