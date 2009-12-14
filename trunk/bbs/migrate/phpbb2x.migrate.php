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
// phpBB 2.x -> IceBB migration module
// $Id$
//******************************************************//

class migrate_phpbb2x
{
	// Basic information
	var $forum_type					= "phpBB 2.x";
	var $can_migrate_pw				= true;
	var $can_migrate				= "users,userpw,permgroups,groups,forums,topics,posts,settings,banfilters";
	var $notes						= "This script does not convert forum
	permissions; after migrating, you will have to go to the admin control center
	and manually change these. This script also does not currently convert private
	messages, polls, smilies, or watched topics.";

	// Database information (will be set according to user's selection)
	var $db_host					= 'localhost';
	var $db_user					= '';
	var $db_pass					= '';
	var $db_database				= '';
	var $db_prefix					= 'phpbb_';
	var $db;
	
	// storage for later
	var $stored_usernames			= array();
	var $forum_last_posts			= array();
	var $topic_last_posts			= array();
	var $is_first_post				= array();
	
	function setup($dbhost,$dbuser,$dbpass,$dbdb,$dbprefix)
	{
		$this->db_host				= empty($dbhost) ? $this->db_host : $dbhost;
		$this->db_user				= $dbuser;
		$this->db_pass				= $dbpass;
		$this->db_database			= $dbdb;
		$this->db_prefix			= empty($dbprefix) ? $this->db_prefix : $dbprefix;
		
		$this->db					= mysql_connect($this->db_host,$this->db_user,$this->db_pass);
		$connection					= mysql_select_db($this->db_database,$this->db);
		
		if(!$this->db || !$connection) return false;
	}

	function get_users()
	{
		$result							= $this->run_query("SELECT * FROM {$this->db_prefix}users WHERE user_id>-1");
		while($r						= mysql_fetch_assoc($result))
		{
			$tr['id']					= $r['user_id'];
			$tr['username']				= $r['username'];
			
			// translate groups
			switch($r['user_level'])
			{
				case 0:					// Guests
					$tr['user_group']	= 4;
					break;
				case 1:					// Admin
					$tr['user_group']	= 1;
					break;
				default:
					$tr['user_group']	= 2;
			}
			
			// are we banned?
			if(!$r['user_active'])
			{
				$tr['user_group']		= 5;
			}
			
			$tr['joindate']				= $r['user_regdate'];
			$tr['siggie']				= $r['user_sig'];
			$tr['email']				= $r['user_email'];
			$tr['aim']					= $r['user_aim'];
			$tr['msn']					= $r['user_msnm'];
			$tr['icq']					= $r['user_icq'];	// who uses ICQ anymore anyway? (removed in IceBB 1.1)
			$tr['yahoo']				= $r['user_yim'];
			$tr['url']					= $r['user_website'];
			$tr['gmt']					= $r['user_timezone'];
			$tr['posts']				= $r['user_posts'];
			$tr['date_format']			= $r['user_dateformat'];
			$tr['last_visit']			= $r['user_lastvisit'];
			$tr['interests']			= $r['user_interests'];
			
			// password
			$salty						= md5(crypt(make_salt(27)));
			$tr['password']				= md5($r['user_password'].$salty);
			$tr['pass_salt']			= $salty;
			
			$this->stored_usernames[$tr['id']]= $tr['username'];
			
			$results[]					= $tr;
		}
		
		return $results;
	}
	
	function get_permgroups()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}auth_access");
		while($r					= mysql_fetch_assoc($result))
		{
			$pg						= array(
				'permid'			=> $r['perm_id'],
				'permname'			=> $r['perm_name'],
			);
			
			$results[]				= $pg;
		}
		
		return $results;
	}
	
	function get_groups()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}groups WHERE group_id>2");
		while($r					= mysql_fetch_assoc($result))
		{
			$g						= array(
				//'gid'				=> $r['group_id'],
				'g_title'			=> $r['group_name'],
			);
		
			$results[]				= $g;
		}
		
		return $results;
	}
	
	function get_forums()
	{
		// permissions
		/*$pq							= $this->run_query("SELECT * FROM {$this->db_prefix}auth_access");
		while($p					= mysql_fetch_assoc($pq))
		{
			$per					= array(
				'seeforum'			=> intval($p['auth_view']),
				'read'				=> intval($p['auth_read']),
				'createtopics'		=> intval($p['auth_post']),
				'reply'				=> intval($p['auth_reply']),
				'attach'			=> intval($p['auth_attachments']),
			);
			
			$permissions[$p['forum_id']][$p['group_id']]= $per;
		}*/
		
		$perm[1]					= array(
			'seeforum'				=> 1,
			'read'					=> 1,
			'createtopics'			=> 1,
			'reply'					=> 1,
			'attach'				=> 1,
		);
		
		$perm[2]					= array(
			'seeforum'				=> 1,
			'read'					=> 1,
			'createtopics'			=> 1,
			'reply'					=> 1,
			'attach'				=> 1,
		);
		
		for($i=3;$i<=5;$i++)
		{
			$perm[$i]				= array(
				'seeforum'			=> 1,
				'read'				=> 1,
				'createtopics'		=> 0,
				'reply'				=> 0,
				'attach'			=> 0,
			);
		}
		
		for($i=1;$i<=5;$i++)
		{
			$perm_all[$i]			= array(
				'seeforum'			=> 1,
				'read'				=> 1,
				'createtopics'		=> 0,
				'reply'				=> 0,
				'attach'			=> 0,
			);
		}
	
		$fid						= 0;
		$this->new_fids				= array();
		$this->new_cat_ids			= array();
	
		// categories
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}categories");
		while($r					= mysql_fetch_assoc($result))
		{
			$fid++;
			$this->new_cat_ids[$r['cat_id']]= $fid;
		
			$tr['fid']				= $fid;
			$tr['name']				= $r['cat_title'];
			$tr['postable']			= 0;
			$tr['sort']				= $r['cat_order'];
			$tr['perms']			= serialize($perm_all);
			
			$results[]				= $tr;
		}
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}forums");
		while($r					= mysql_fetch_assoc($result))
		{
			$fid++;
			$this->new_fids[$r['forum_id']]= $fid;
		
			$tr['fid']				= $fid;
			$tr['name']				= $r['forum_name'];
			$tr['description']		= $r['forum_desc'];
			$tr['parent']			= $this->new_cat_ids[$r['cat_id']];
			$tr['password']			= $r['password'];
			$tr['topics']			= $r['forum_topics'];
			$tr['replies']			= $r['forum_posts'];
			$tr['postable']			= 1;
			
			// set last post for updating later
			$this->forum_last_posts[]	= $r['forum_last_post_id'];
			
			// permissions - why did phpBB make this so complicated?
			$permi['seeforum']		= ($r['auth_read']);
			$permi['read']			= ($r['auth_read']);
			$permi['createtopics']	= ($r['auth_post']);
			$permi['reply']			= ($r['auth_reply']);
			$permi['attach']		= 1;
			
			// I need to work out phpBB's permission system
			$tr['perms']			= serialize($perm);
		
			$results[]	= $tr;
		}
		
		return $results;
	}

	function get_topics()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}topics");
		while($r					= mysql_fetch_assoc($result))
		{
			$t['tid']				= $r['topic_id'];
			$t['forum']				= $this->new_fids[$r['forum_id']];
			$t['title']				= $r['topic_title'];
			$t['replies']			= $r['topic_replies'];
			$t['views']				= $r['topic_views'];
			$t['starter']			= $this->stored_usernames[$r['topic_poster']];
			//$t['is_pinned']			= $r['pinned'];
			$t['moved_to']			= $r['topic_moved_id'];
			
			$this->is_first_post[$r['topic_first_post_id']]= true;
			$this->topic_last_posts[]= $t['tid'];
		
			$results[]				= $t;
		}
		
		return $results;
	}

	function get_posts()
	{
		$lp_pids					= array();
	
		$result						= $this->run_query("SELECT p.*,t.post_text FROM {$this->db_prefix}posts AS p LEFT JOIN {$this->db_prefix}posts_text AS t ON p.post_id=t.post_id");
		while($r					= mysql_fetch_assoc($result))
		{
			$p['pid']				= $r['post_id'];
			$p['ptopicid']			= $r['topic_id'];
			$p['pauthor']			= empty($r['poster_id']) ? "" : $r['poster_username'];
			$p['pauthor_id']		= $r['poster_id'];
			$p['pauthor_ip']		= $this->decode_ip($r['poster_ip']);
			$p['pdate']				= $r['post_time'];
			$p['ptext']				= $r['post_text'];
			
			// is this a first post?
			if($this->is_first_post[$r['post_id']])
			{
				$p['pis_firstpost']	= 1;
			}
			
			// forum last posts
			if(in_array($p['pid'],$this->forum_last_posts))
			{
				$lp_tids[]			= $p['ptopicid'];
				$lp_uids[]			= $p['pauthor_id'];
				$lp_times[]			= $p['pdate'];
			}
			
			// topic last posts
			if(in_array($p['pid'],$this->topic_last_posts))
			{
				$tlp_tids[]			= $p['ptopicid'];
				$tlp_uids[]			= $p['pauthor_id'];
				$tlp_times[]		= $p['pdate'];
			}
			
			$results[]				= $p;
		}
		
		// Yes, I do realize the code below is probably inefficient, but as far as
		// I know, this is the best way to do it. It's better than leaving things blank,
		// anyway.
		
		// forum last posts
		if(count($lp_tids) > 0)
		{
			$last_post_tids			= implode(',',$lp_tids);
			$last_post_uids			= implode(',',$lp_uids);
			
			$c1						= 0;
			$c2						= 0;
			$to_update				= array();

			global $db;
			
			$q1						= $db->query("SELECT username FROM icebb_users WHERE id IN({$last_post_uids})");
			while($u				= mysql_fetch_assoc($q1))
			{
				$uids[$c1]			= $u['username'];
				$c1++;
			}
			
			$q2						= $db->query("SELECT forum,title FROM icebb_topics WHERE tid IN({$last_post_tids})");
			while($p2				= mysql_fetch_assoc($q2))
			{
				$to_update[$p2['forum']]['id']		= intval($lp_tids[$c2]);
				$to_update[$p2['forum']]['time']	= intval($lp_times[$c2]);
				$to_update[$p2['forum']]['author']	= $uids[$c2];
				$to_update[$p2['forum']]['title']	= $p2['title'];
				
				$c2++;
			}
			
			foreach($to_update as $fid => $tu)
			{
				$db->query("UPDATE icebb_forums SET lastpostid={$tu['id']},lastpost_time={$tu['time']},lastpost_author='".$db->escape_string($tu['author'])."',lastpost_title='".$db->escape_string($tu['title'])."' WHERE fid={$fid}");
			}
			
			// clean up
			unset($last_post_tids);
			unset($last_post_uids);
			unset($uids);
			unset($to_update);
		}
		
		// topic last posts
		if(count($tlp_tids) > 0)
		{
			$last_post_tids			= implode(',',$tlp_tids);
			$last_post_uids			= implode(',',$tlp_uids);
			
			$c3						= 0;
			$c4						= 0;
			$to_update				= array();

			global $db;
			
			$q1						= $db->query("SELECT username FROM icebb_users WHERE id IN({$last_post_uids})");
			while($u				= mysql_fetch_assoc($q1))
			{
				$uids[$c3]			= $u['username'];
				$c3++;
			}
			
			foreach($tlp_tids as $tid1)
			{
				$to_update[$tid1]['time']	= intval($lp_times[$c4]);
				$to_update[$tid1]['author']	= $uids[$c4];
				$c4++;
			}
			
			foreach($to_update as $tid => $tu)
			{
				$db->query("UPDATE icebb_topics SET lastpost_time={$tu['time']},lastpost_author='".$db->escape_string($tu['author'])."' WHERE tid={$tid}");
			}
			
			// clean up
			unset($last_post_tids);
			unset($last_post_uids);
			unset($uids);
			unset($to_update);
		}
		
		return $results;
	}
	
	function get_settings()
	{
		$settings							= array();
	
		$q									= $this->run_query("SELECT * FROM phpbb_config");
		while($r							= mysql_fetch_assoc($q))
		{
			$val							= $r['config_value'];
		
			switch($r['config_name'])
			{
				case 'sitename':
					$settings['board_name']	= $val;
					break;
				case 'gzip_compress':
					$settings['enable_gzip']= $val;
					break;
			}
		}
	
		return $settings;
	}
	
	function get_banfilters()
	{
		$banfilters							= array();
	
		$q									= $this->run_query("SELECT * FROM phpbb_banlist");
		while($r							= mysql_fetch_assoc($q))
		{
			if(!empty($r['ban_ip']))
			{
				$b['type']					= 'ip';
				$b['value']					= $this->decode_ip($r['ban_ip']);
			}
			else if(!empty($r['ban_email']))
			{
				$b['type']					= 'email';
				$b['value']					= $r['ban_email'];
			}
			else {
				continue;
			}
			
			$banfilters[]					= $b;
		}
	
		return $banfilters;
	}
	
	// decode IP
	function decode_ip($ip_in)
	{
		$hexip				= explode('.',chunk_split($ip_in,2,'.'));
		$ip_out				= hexdec($hexip[0]).'.'.hexdec($hexip[1]).'.'.hexdec($hexip[2]).'.'.hexdec($hexip[3]);

		return $ip_out;
	}
	
	function run_query($q)
	{
		$r							= mysql_query($q,$this->db);
		if(!$r) die(mysql_error());
		
		return $r;
	}
}
?>
