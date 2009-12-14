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
// SMF 1.1.x -> IceBB migration module
// $Id$
//******************************************************//

class migrate_smf1x
{
	// Basic information
	var $forum_type					= "SMF 1.1.x";
	var $can_migrate_pw				= false;
	var $can_migrate				= "users,groups,forums,topics,posts,moderators";
	var $notes						= "This script does not convert passwords or
	forum permissions. You will have to use the forgotten password feature to reset
	your password and go into the admin control center and manually set the
	permissions for each forum. Also, this script does not yet convert private
	messages, polls, smilies, or subscriptions.";

	// Database information (will be set according to user's selection)
	var $db_host					= 'localhost';
	var $db_user					= '';
	var $db_pass					= '';
	var $db_database				= '';
	var $db_prefix					= 'smf_';
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
		$results						= array();
	
		$result							= $this->run_query("SELECT * FROM {$this->db_prefix}members");
		while($r						= mysql_fetch_assoc($result))
		{
			$tr							= array();
			$tr['id']					= $r['ID_MEMBER'];
			$tr['username']				= $r['memberName'];
			
			// translate groups
			switch($r['ID_GROUP'])
			{
				case 1:					// Admin
					$tr['user_group']	= 1;
					break;
				case 2:					// Global moderator
				case 3:					// Moderator
					$tr['user_group']	= 6;
					break;
				default:
					$tr['user_group']	= 2;
			}
			
			// are we banned?
			if(!$r['is_activated'])
			{
				$tr['user_group']		= 5;
			}
			
			$tr['joindate']				= $r['dateRegistered'];
			$tr['posts']				= $r['posts'];
			$tr['siggie']				= $r['signature'];
			$tr['email']				= $r['emailAddress'];
			$tr['aim']					= $r['AIM'];
			$tr['msn']					= $r['MSN'];
			$tr['icq']					= $r['ICQ'];	// who uses ICQ anymore anyway? (removed in IceBB 1.1)
			$tr['yahoo']				= $r['YIM'];
			$tr['url']					= $r['websiteUrl'];
			$tr['gmt']					= $r['timeOffset'];
			$tr['posts']				= $r['posts'];
			$tr['date_format']			= !empty($r['timeFormat']) ? $r['timeFormat'] : "F j, Y @ g:i A";
			$tr['last_visit']			= $r['lastLogin'];
			
			// password - they won't work, but let's just get them anyways...
			$tr['password']				= $r['passwd'];
			//$tr['password']				= '322d3fef02fc39251436cb4522d29a71';
			$tr['pass_salt']			= $r['passwordSalt'];
			//$tr['pass_salt']			= 'abc';
			
			$this->stored_usernames[$tr['id']]= $tr['username'];
			
			$results[]					= $tr;
		}
		
		return $results;
	}
	
	function get_permgroups()
	{
	}
	
	function get_groups()
	{
		$results					= array();
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}membergroups WHERE ID_GROUP>3 AND minPosts=-1");
		while($r					= mysql_fetch_assoc($result))
		{
			$g						= array(
				'g_title'			=> $r['groupName'],
				'g_permgroup'		=> 2,
			);
		
			$results[]				= $g;
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
			$this->new_cat_ids[$r['ID_CAT']]= $fid;
		
			$tr						= array();
			$tr['fid']				= $fid;
			$tr['name']				= $r['name'];
			$tr['postable']			= 0;
			$tr['sort']				= $r['catOrder'];
			$tr['perms']			= serialize($perm_all);
			
			$results[]				= $tr;
		}
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}boards");
		while($r					= mysql_fetch_assoc($result))
		{
			$fid++;
			$this->new_fids[$r['ID_BOARD']]= $fid;
		
			$tr						= array();
			$tr['fid']				= $fid;
			$tr['sort']				= $r['boardOrder'];
			$tr['name']				= $r['name'];
			$tr['description']		= $r['description'];
			$parent_cat				= $this->new_cat_ids[$r['ID_CAT']];
			$tr['parent']			= !empty($parent_cat) ? $parent_cat : $this->new_fids[$r['ID_PARENT']];
			$tr['topics']			= $r['numTopics'];
			$tr['replies']			= $r['numPosts'];
			$tr['postable']			= 1;
			$tr['perms']			= serialize($perm);
			
			// set last post for updating later
			$this->forum_last_posts[]	= $r['ID_LAST_MSG'];
		
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
			$t['tid']				= $r['ID_TOPIC'];
			$t['forum']				= $this->new_fids[$r['ID_BOARD']];
			$t['replies']			= $r['numReplies'];
			$t['views']				= $r['numViews'];
			$t['starter']			= $this->stored_usernames[$r['ID_MEMBER_STARTED']];
			$t['lastpost_author']	= $this->stored_usernames[$r['ID_MEMBER_UPDATED']];
			$t['is_locked']			= $r['locked'];
			$t['is_pinned']			= $r['isSticky'];
			
			$this->is_first_post[$r['ID_FIRST_MSG']]= true;
			$this->topic_last_posts[]= $t['tid'];
		
			$results[]				= $t;
		}
		
		return $results;
	}

	function get_posts()
	{
		$results					= array();
		$lp_pids					= array();
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}messages");
		while($r					= mysql_fetch_assoc($result))
		{
			$p['pid']				= $r['ID_MSG'];
			$p['ptopicid']			= $r['ID_TOPIC'];
			$p['pauthor']			= empty($r['ID_MEMBER']) ? "" : $r['posterName'];
			$p['pauthor_id']		= $r['ID_MEMBER'];
			$p['pauthor_ip']		= $r['posterIP'];
			$p['pdate']				= $r['posterTime'];
			$p['ptext']				= str_replace('<br />',"\n",$r['body']);
			
			// is this a first post?
			if($this->is_first_post[$r['ID_MSG']])
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
				$tlp_subjects[]		= $r['subject'];
				$tlp_tids[]			= $p['ptopicid'];
				$tlp_uids[]			= $p['pauthor_id'];
				$tlp_times[]		= $p['pdate'];
			}
			
			$results[]				= $p;
		}
		
		// Yes, I do realize the code below is probably inefficient, but as far as
		// I know, this is the best way to do it. It's better than leaving things blank,
		// anyway.
		
		// topic last posts
		if(count($tlp_tids) > 0)
		{
			$last_post_tids			= implode(',',$tlp_tids);
			
			$c3						= 0;
			$c4						= 0;
			$to_update				= array();

			global $db;
			
			foreach($tlp_tids as $tid1)
			{
				$to_update[$tid1]['subject']= $tlp_subjects[$c4];
				$to_update[$tid1]['time']	= intval($lp_times[$c4]);
				$c4++;
			}
			
			foreach($to_update as $tid => $tu)
			{
				$db->query("UPDATE icebb_topics SET title='{$tu['subject']}',lastpost_time={$tu['time']} WHERE tid={$tid}");
			}
			
			// clean up
			unset($last_post_tids);
			unset($last_post_uids);
			unset($uids);
			unset($to_update);
		}
		
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
		
		return $results;
	}
	
	function get_moderators()
	{
		$moderators					= array();
	
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}moderators");
		while($r					= mysql_fetch_assoc($result))
		{
			$m						= array();
			$m['mforum']			= $this->new_fids[$r['ID_BOARD']];
			$m['muserid']			= $r['ID_MEMBER'];
			$m['muser']				= $this->stored_usernames[$m['muserid']];
			
			$m['medit']				= 1;
			$m['medit_topic']		= 1;
			$m['mdel']				= 1;
			$m['mdel_topic']		= 1;
			$m['mview_ip']			= 1;
			$m['mlock']				= 1;
			$m['munlock']			= 1;
			$m['m_multi_move']		= 1;
			$m['m_multi_del']		= 1;
			$m['mmove']				= 1;
			$m['mpin']				= 1;
			$m['munpin']			= 1;
			$m['mwarn']				= 1;
			$m['medit_user']		= 1;
			
			$moderators[]			= $m;
		}
		
		return $moderators;
	}
	
	function run_query($q)
	{
		$r							= mysql_query($q,$this->db);
		if(!$r) die("MySQL Error: ".mysql_error());
		
		return $r;
	}
}
?>
