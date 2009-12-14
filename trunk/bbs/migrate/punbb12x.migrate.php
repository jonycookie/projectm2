<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net
//******************************************************//
// PunBB -> IceBB migration module
// $Id$
//******************************************************//

class migrate_punbb12x
{
	// Basic information
	var $forum_type					= "PunBB 1.2.x";
	var $can_migrate_pw				= false;
	var $can_migrate				= "users,groups,forums,topics,posts";
	var $notes						= "This script does not convert passwords or
	forum permissions. You will have to use the forgotten password feature to reset
	your password and go into the admin control center and manually set the
	permissions for each forum. Also, this script does not yet convert private
	messages, polls, smilies, or subscriptions.";

	// Database information (will be set according to user's selection)
	var $db_host		= 'localhost';
	var $db_user		= '';
	var $db_pass		= '';
	var $db_database	= '';
	var $db_prefix		= '';
	var $db;
	
	// storage for later
	var $forums		= array();
	var $categories	= array();
	
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
		$result							= db_query("SELECT * FROM {$this->prefix}users WHERE id>1");
		while($r						= db_fetch($result))
		{
			$tr							= array();
			$tr['id']					= $r['id'];
			$tr['username']				= $r['username'];
			
			switch($r['group_id'])
			{
				case '1':
					$tr['user_group']	= '1';
					break;
				case '3':
					$tr['user_group']	= '4';
					$tr['id']			= '0';
					$tr['username']		= 'Guest';
					break;
				default:
					$tr['user_group']	= '2';
					break;
			}
			
			$tr['title']				= $t['title'];
			$tr['joindate']				= $r['registered'];
			$tr['siggie']				= $r['signature'];
			$tr['email']				= $r['email'];
			$tr['aim']					= $r['aim'];
			$tr['msn']					= $r['msn'];
			$tr['icq']					= $r['icq'];
			$tr['yahoo']				= $r['yahoo'];
			$tr['url']					= $r['url'];
			$tr['gmt']					= $r['timezone'];
			$tr['view_smileys']			= $r['show_smilies'];
			$tr['view_av']				= $r['show_avatars'];
			$tr['view_sig']				= $r['show_sig'];
			$tr['posts']				= $r['num_posts'];
			$tr['ip']					= $r['registration_ip'];
			$tr['location']				= $r['location'];
			
			// password
			$salty						= md5(crypt(make_salt(27)));
			$tr['password']				= md5(md5('123456').$salty);
			$tr['pass_salt']			= $salty;
			
			$this->stored_usernames[$tr['id']]= $tr['username'];
			
			$results[]	= $tr;
		}
		
		return $results;
	}
	
	function get_permgroups()
	{
	}
	
	function get_groups()
	{
		$result			= db_query("SELECT * FROM {$this->prefix}groups WHERE g_id>4");
		while($r		= db_fetch($result))
		{
			$tr = array(
						'g_title'		=> $r['g_title'],
						'g_view_board'	=> $r['g_read_board'],
					);
			
			$results[]	= $tr;
		}
		
		return $results;
	}
	
	function get_forums()
	{
		$results					= array();
	
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
			$this->new_cat_ids[$r['id']]= $fid;
		
			$tr						= array();
			$tr['fid']				= $fid;
			$tr['name']				= $r['cat_name'];
			$tr['postable']			= 0;
			$tr['sort']				= $r['disp_position'];
			$tr['perms']			= serialize($perm_all);
			
			$results[]				= $tr;
		}
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}forums");
		while($r					= mysql_fetch_assoc($result))
		{
			$fid++;
			$this->new_fids[$r['id']]= $fid;
		
			$tr						= array();
			$tr['fid']				= $fid;
			$tr['sort']				= $r['disp_position'];
			$tr['name']				= $r['forum_name'];
			$tr['description']		= $r['forum_desc'];
			$tr['parent']			= $this->new_cat_ids[$r['cat_id']];
			$tr['topics']			= $r['num_topics'];
			$tr['replies']			= $r['num_posts'];
			$tr['postable']			= 1;
			$tr['perms']			= serialize($perm);
			
			$tr['lastpostid']		= $r['last_post_id'];
			$tr['lastpost_time']	= $r['last_post'];
			$tr['lastpost_author']	= $r['last_poster'];
			
			// set last post for updating later
			$this->forum_last_posts[]	= $r['last_post_id'];
		
			$results[]	= $tr;
		}
		
		return $results;
	}

	function get_topics()
	{
		$results					= array();
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}topics");
		while($r					= mysql_fetch_assoc($result))
		{
			$t						= array();
			$t['tid']				= $r['id'];
			$t['forum']				= $this->new_fids[$r['forum_id']];
			$t['title']				= $r['subject'];
			$t['replies']			= $r['num_replies'];
			$t['views']				= $r['num_views'];
			$t['starter']			= $r['poster'];
			$t['lastpost_time']		= $r['last_post'];
			$t['lastpost_author']	= $r['last_poster'];
			$t['is_locked']			= $r['closed'];
			$t['is_pinned']			= $r['sticky'];
		
			$results[]				= $t;
		}
		
		return $results;
	}

	function get_posts()
	{
		$results					= array();
		$lp_pids					= array();
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}posts");
		while($r					= mysql_fetch_assoc($result))
		{
			$p['pid']				= $r['id'];
			$p['ptopicid']			= $r['topic_id'];
			$p['pauthor']			= empty($r['poster_id']) ? "" : $r['poster'];
			$p['pauthor_id']		= $r['poster_id'];
			$p['pauthor_ip']		= $r['poster_ip'];
			$p['pdate']				= $r['posted'];
			$p['ptext']				= $r['message'];
			
			// is this a first post?
			/*if($this->is_first_post[$r['id']])
			{
				$p['pis_firstpost']	= 1;
			}*/
			
			// forum last posts
			/*if(in_array($p['pid'],$this->forum_last_posts))
			{
				$lp_tids[]			= $p['ptopicid'];
				$lp_uids[]			= $p['pauthor_id'];
				$lp_times[]			= $p['pdate'];
			}
			
			// topic last posts
			if(in_array($p['pid'],$this->topic_last_posts))
			{
				$tlp_subjects[]		= $r['subject'];
				$tlp_tids[]			= $p['ptopicid'];
				$tlp_uids[]			= $p['pauthor_id'];
				$tlp_times[]		= $p['pdate'];
			}*/
			
			$results[]				= $p;
		}
		
		return $results;
	}
	
	function run_query($q)
	{
		$r							= mysql_query($q,$this->db);
		if(!$r) die("MySQL Error: ".mysql_error());
		
		return $r;
	}
}
?>
