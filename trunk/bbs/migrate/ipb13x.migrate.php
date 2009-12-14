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
// IPB 1.2.x/1.3.x -> IceBB migration module
// $Id: migrate_ipb2x.php 195 2005-07-24 03:51:11Z mutantmonkey $
//******************************************************//

class migrate_ipb13x
{
	// Basic information
	var $forum_type					= "Invision Power Board 1.2.x/1.3.x";
	var $can_migrate_pw				= true;

	// Database information (will be set according to user's selection)
	var $db_host					= 'localhost';
	var $db_user					= '';
	var $db_pass					= '';
	var $db_database				= '';
	var $db_prefix					= 'ibf_';
	var $db;
	
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
		$result							= $this->run_query("SELECT m.*,me.* FROM {$this->db_prefix}members AS m LEFT JOIN {$this->db_prefix}member_extra AS me ON m.id=me.id");
		while($r						= mysql_fetch_assoc($result))
		{
			$tr['id']					= $r['id'];
			$tr['username']				= $r['name'];
			
			// translate groups
			switch($r['mgroup'])
			{
				case 4:					// Administrators
					$tr['user_group']	= 1;
					break;
				case 2:					// Guests
					$tr['user_group']	= 4;
					break;
				case 3:					// Members
					$tr['user_group']	= 2;
					break;
				case 1:					// Validating
					$tr['user_group']	= 3;
					break;
				case 5:					// Banned
					$tr['user_group']	= 5;
					break;
				default:
					$tr['user_group']	= $tr['user_group'];
			}
			
			$tr['title']				= $t['title'];
			$tr['joindate']				= $r['joined'];
			$tr['siggie']				= $r['signature'];
			$tr['email']				= $r['email'];
			$tr['aim']					= $r['aim_name'];
			$tr['msn']					= $r['msname'];
			$tr['icq']					= $r['icq_number'];	// who uses ICQ anymore anyway? (removed in IceBB 1.1)
			$tr['yahoo']				= $r['yahoo'];
			$tr['url']					= $r['website'];
			$tr['notepad']				= $r['notes'];
			$tr['gmt']					= $r['time_offset'];
			$tr['posts']				= $r['posts'];
			
			// password
			$salty						= md5(crypt(make_salt(27)));
			$tr['password']				= md5($r['password'].$salty);
			$tr['pass_salt']			= $salty;
			
			$results[]					= $tr;
		}
		
		return $results;
	}
	
	function get_permgroups()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}forum_perms WHERE perm_id>5");
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
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}groups WHERE g_id>5");
		while($r					= mysql_fetch_assoc($result))
		{
			$g						= array(
				'gid'				=> $r['g_id'],
				'g_title'			=> $r['g_title'],
				'g_view_board'		=> $r['g_view_board'],
				'g_is_mod'			=> $r['g_is_supmod'],
				'g_is_admin'		=> $r['g_access_cp'],
				'g_view_offline_board'=> $r['g_access_offline'],
				'g_prefix'			=> $r['prefix'],
				'g_suffix'			=> $r['suffix'],
			);
		
			$results[]				= $g;
		}
		
		return $results;
	}
	
	function get_forums()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}forums");
		while($r					= mysql_fetch_assoc($result))
		{
			$tr['sort']				= $r['sort_order'];
			$tr['name']				= $r['name'];
			$tr['description']		= $r['description'];
			$tr['parent']			= $r['parent_id'];
			$tr['password']			= $r['password'];
			$tr['topics']			= $r['topics'];
			$tr['replies']			= $r['posts'];
			$tr['postable']			= 1;
			$tr['lastpostid']		= $r['last_post'];
			$tr['lastpost_author']	= $r['last_poster_name'];
			
			// PERMISSIONS
			$perms					= unserialize($r['permission_array']);
		
			$results[]	= $tr;
		}
		
		return $results;
	}

	function get_topics()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}topics");
		while($r					= mysql_fetch_assoc($result))
		{
			$t['tid']				= $r['tid'];
			$t['title']				= $r['title'];
			$t['description']		= $r['description'];
			$t['replies']			= $r['replies'];
			$t['starter']			= $r['starter_name'];
			$t['is_pinned']			= $r['pinned'];
			$t['moved_to']			= $r['moved_to'];
			$t['lastpost_time']		= $r['last_post'];
		
			$results[]				= $t;
		}
		
		return $results;
	}

	function get_posts()
	{
		$result						= $this->run_query("SELECT * FROM {$this->db_prefix}posts");
		while($r					= mysql_fetch_assoc($result))
		{
			$p['pid']				= $r['pid'];
			$p['ptopicid']			= $r['topic_id'];
			$p['pauthor']			= empty($r['author_id']) ? "" : $r['author'];
			$p['pauthor_id']		= $r['author_id'];
			$p['pauthor_ip']		= $r['ip_address'];
			$p['pedit_show']		= $r['append_edit'];
			$p['pedit_author']		= $r['edit_name'];
			$p['pedit_time']		= $r['edit_time'];
			$p['pis_firstpost']		= $r['new_topic'];
			$p['ptext']				= $r['post'];
		
			$results[]				= $p;
		}
		
		return $results;
	}
	
	function run_query($q)
	{
		return mysql_query($q,$this->db);
	}
}
?>