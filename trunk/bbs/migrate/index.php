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
// migrate module
// $Id: index.php 822 2007-05-13 18:01:27Z mutantmonkey0 $
//******************************************************//

define('IN_ICEBB'				, '1');			// to prevent external access

// turn off time limit
@set_time_limit(0);

// temporary
ini_set('allow_call_time_pass_reference','true');

// migrate script locked?
if(@file_exists('migrate.lock'))
{
	echo "For security reasons, you must remove migrate.lock from this directory if you want to use the migrate script.";
	exit();
}

require('../includes/classes/error_handler.php');

require('../config.php');

require('../includes/classes/timer.inc.php');
require('../includes/database/mysql.db.php');

$engine							= "db_{$config['db_engine']}";
if(!class_exists($engine)) require("includes/database/{$config['db_engine']}.db.php");
$db								= new $engine();

require('../includes/functions.php');
$std					= new std_func;

$timer->start('main');

$input					= $std->capture_input();

// WRAP IT UP
// ----------
// Wrap everything up in a nice reusable class here...

$icebb					= new icebb();
class icebb
{
	// change this
	var $forums			= array(
		//array('ipb13x'	, "Invision Power Board 1.2.x/1.3.x"),
		//array('ipb21x'	, "Invision Power Board 2.1.x"),
		array('phpbb2x'	, "phpBB 2.x"),
		array('punbb12x', "PunBB 1.2.x"),
		array('smf1x'	, "SMF 1.x"),
	);

	var $config;
	var $skin;
	var $input;
	var $html;
	var $base_url		= "index.php?";
	var $output;

	function icebb()
	{
		global $db,$std,$config,$input,$session;
	
		$this->input		= $input;
	}
	
	function do_output()
	{
		$curr_year			= date('Y');
	
		echo <<<EOF
<html>
<head>
<title>IceBB Migrate</title>
<style type='text/css'>
@import '../install/install.css';
</style>
<script type='text/javascript' src='../jscripts/prototype/prototype.js'></script>
<script type='text/javascript' src='../jscripts/global.js'></script>
<script type='text/javascript' src='../install/install.js'></script>
</head>
<body bgcolor='#ffffff'>
<div id="welcome">
	<div id="header">
		<h1>We're glad you decided to make the move to IceBB</h1>

		<div id='info'>We just need some information from you so we can import the information from your existing forum</div>
	</div>
</div>

<!-- HELP -->
{$this->help_html}
<!-- /HELP -->

<div id="starter">
{$this->html}
</div>
	
	<div id="footer">
		Copyright &copy; {$curr_year} XAOS Interactive
	</div>
</div>
</div>
</body>
</html>
EOF;
	}
}

function db_query($q)
{
	global $db;
	
	return mysql_query($q);
}

function db_fetch($q)
{
	global $db;
	
	return mysql_fetch_assoc($q);
}

// END WRAP IT UP
// --------------

$installer				= new migrate();
class migrate
{
	function migrate()
	{
		global $icebb;
		
		switch($icebb->input['step'])
		{
			default:
				$icebb->html		 .= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Have you already installed IceBB?</h3>
			<em>IceBB needs to be installed before this script can be run</em>
		</div>
		<ul>
			<li><a href='../install/'>No, I have not yet installed IceBB</a></li>
			<li><a href='index.php?step=2'>Yes, I have already installed IceBB</a></li>
		</ul>
		
		<p style='margin:0px'><strong>Please be aware that using the migrate script will delete your existing
		many of the customizations you have made to your existing installation of IceBB.</strong> If you
		are starting from a clean install, you do not need to worry about this.</p>
	</div>
</div>

EOF;
				
				$icebb->do_output();
				break;
			case '2':
				$this->show_chooser_page();
				break;
			case '3':
				$this->check_proceed();
				break;
			case '4':
				$this->do_install();
				break;
			case '5':
				$this->do_recache();
				break;
			case '6':
				$this->done_migrate();
				break;
		}
	}
	
	function show_chooser_page($msg=array())
	{
		global $icebb;
		
		$icebb->help_html	= <<<EOF
<div class='help' id='help-sql_host'>
This is the server your existing forum installation uses connect to for storing or reading topics, posts, users, and more.
Unless your forum software is configured otherwise or is on a different server, you can leave this as localhost.
</div>
<div class='help' id='help-sql_user'>
This is the username your existing forum installation connects as when storing or reading topics, posts, users, and more.
</div>
<div class='help' id='help-sql_pass'>
This is the password your existing forum software uses to connect to the database.
</div>
<div class='help' id='help-sql_database'>
This is the database your existing forum software stores its information in.
</div>
<div class='help' id='help-sql_prefix'>
If you have changed the default database prefix (if applicable) in your forum software's configuration, please enter your
custom prefix here. Otherwise, this can be left blank.
</div>

EOF;

		foreach($msg as $mi => $me)
		{
			$msg[$mi]= <<<EOF
<div class='border'>
	<div class='block'>
		<h3 class='title'>Error:</h3>
		{$me}
	</div>
</div> 

EOF;
		}
		
		$db_host				= empty($icebb->input['db_host']) ? 'localhost' : $icebb->input['db_host'];
		$icebb->input['db_prefix']= empty($icebb->input['db_prefix']) ? '' : $icebb->input['db_prefix'];
		
		foreach($icebb->forums as $fk => $f)
		{
			$forum_html		    .= <<<EOF
				<tr>
					<td width='1%'>
						<input type='radio' name='migratefrom' value='{$fk}' id='forum-{$fk}' />
					</td>
					<td>
						<label for='forum-{$fk}'><strong>{$f[1]}</strong><label>
					</td>
				</tr>

EOF;
		}
		
		$icebb->html			.= <<<EOF
<div class='border backup-reminder'>
	<div class='block'>
		<h3>Please remember to back up before using the migrate script!</h3>
		<em>Although IceBB should not touch your existing forum, <strong>it is always a good idea to back
		up</strong> just in case something goes wrong.</em>
	</div>
</div>

<form action='index.php' method='post'>
	<input type='hidden' name='step' value='3' />
	
	{$msg['whatforum']}
	<div class='border'>
		<div class='block'>
			<div class='bottom'>
				<h3>What forum software are you migrating from?</h3>
				<em>If you are not sure, check the copyright notice. It will usually say "Powered by" followed by the name of the
				software and version</em>
			</div>
			<table width='100%' cellpadding='2' cellspacing='1' border='0'>
{$forum_html}
			</table>
		</div>
	</div>
	
	{$msg['options']}
	<div class='border'>
		<div class='block'>
			<div class='bottom'>
				<h3>Options</h3>
				<em>These can usually be left as the default, unless you have special requirements</em>
			</div>
			<table width='100%' cellpadding='2' cellspacing='1' border='0'>
				<tr>
					<td width='1%'>
						<input type='checkbox' name='convert_pass' value='' class='forminput' checked='checked' id='cp' />
					</td>
					<td>
						<label for='cp'><strong>Attempt to convert passwords</strong><br />
						<em>If this is left unchecked, all users will be forced to change their password.</em></label>
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	{$msg['db']}
	<div class='border'>
		<div class='block'>
			<div class='bottom'>
				<h3>Database Configuration</h4>
				<em>Enter your existing forum's database information here</em>
			</div>
			
			<table width='100%' cellpadding='5' cellspacing='2' border='0'>
				<tr>
					<td width='40%'>
						<strong>SQL Host</strong><br />
						<em>99% of the time this is localhost</em>
					</td>
					<td>
						<input type='text' name='db_host' value='{$db_host}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Username</strong><br />
						<em>The username you use to connect to your existing forum's database</em>
					</td>
					<td>
						<input type='text' name='db_user' value='{$icebb->input['db_user']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Password</strong><br />
						<em>The password you use to connect to your existing forum's database</em>
					</td>
					<td>
						<input type='text' name='db_pass' value='{$icebb->input['db_pass']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Database</strong><br />
						<em>The database your existing forum is installed in</em>
					</td>
					<td>
						<input type='text' name='db_database' value='{$icebb->input['db_database']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Table Prefix</strong><br />
						<em>Leave this blank unless you have changed your existing forum software's default</em>
					</td>
					<td>
						<input type='text' name='db_prefix' value='{$icebb->input['db_prefix']}' class='textbox' />
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<div class='border'>
		<div class='block' style='text-align:center'>
			<input type='submit' name='submit' value='Migrate Now' class='button' />
		</div>
	</div>
</form>
EOF;
		
		$icebb->do_output();
		exit();
	}
	
	function check_proceed()
	{
		global $icebb;
	
		if(!isset($icebb->input['migratefrom']))
		{
			$this->show_chooser_page(array('whatforum'=>"Choose a forum!"));
		}
		else if(empty($icebb->input['db_host']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database host!"));
		}
		else if(empty($icebb->input['db_user']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database username!"));
		}
		else if(empty($icebb->input['db_pass']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database password!"));
		}
		else if(empty($icebb->input['db_database']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database!"));
		}
		
		$input2					= $icebb->input;
		unset($input2['ICEBB_QUERY_STRING']);unset($input2['ICEBB_USER_IP']);unset($input2['step']);
		foreach($input2 as $k => $v)
		{
			$hiddens		   .= "\t\t\t\t<input type='hidden' name='{$k}' value='{$v}' />\n";
		}
	
		$forum					= $icebb->forums[$icebb->input['migratefrom']];
		
		require("{$forum[0]}.migrate.php");
		$migrate_class		= "migrate_{$forum[0]}";
		$migrate			= new $migrate_class;
	
		$can_migrate		= explode(',',$migrate->can_migrate);
		foreach($can_migrate as $m)
		{
			switch($m)
			{
				case 'permgroups':
					$m		= "Forum permission groups";
					break;
				case 'userpw':
					$m		= "User passwords";
					break;
				default:
					$m		= strtoupper($m[0]).substr($m,1,strlen($m));
			}
		
			$migrated	   .= "\t\t\t<li>{$m}</li>\n";
		}
		
		$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>The following will be migrated from {$migrate->forum_type}:</h3>
		</div>
		
		<ul>
{$migrated}
		</ul>
	</div>
</div>

EOF;

		if(!empty($migrate->notes))
		{
			$icebb->html   .= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Please Note</h3>
		</div>
		
		<div style='padding:6px'>
{$migrate->notes}
		</div>
	</div>
</div>

EOF;
		}

		$icebb->html	   .= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Do you wish to proceed?</h3>
		</div>

		<div style='text-align:center'>
			<form action='index.php?step=4&' method='post'>
{$hiddens}
				<input type='submit' value='Yes' />
				<input type='button' value='No' onclick="history.go(-1)" />
			</form>
		</div>
	</div>
</div>

EOF;
		$icebb->do_output();
	}
	
	function do_install()
	{
		global $icebb;
		
        //require('../includes/database/mysql.db.php');
        $database				= new db_mysql();
		
		if(!isset($icebb->input['migratefrom']))
		{
			$this->show_chooser_page(array('whatforum'=>"Choose a forum!"));
		}
		else if(empty($icebb->input['db_host']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database host!"));
		}
		else if(empty($icebb->input['db_user']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database username!"));
		}
		else if(empty($icebb->input['db_pass']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database password!"));
		}
		else if(empty($icebb->input['db_database']))
		{
			$this->show_chooser_page(array('db'=>"Enter a database!"));
		}
		
		$forum					= $icebb->forums[$icebb->input['migratefrom']];
		
		require("{$forum[0]}.migrate.php");
		$migrate_class			= "migrate_{$forum[0]}";
		$migrate				= new $migrate_class;
		$migrate->setup($icebb->input['db_host'],$icebb->input['db_user'],$icebb->input['db_pass'],$icebb->input['db_database'],$icebb->input['db_prefix']);

		$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Please stand by, migration in progress...</h3>
			<em id='migrate_msg_1'>Migration time will vary depending on the size of the forum you are migrating.</em>
			<em id='migrate_msg_2' style='display:none'>Migrated data, preparing to rebuild caches..</em>
		</div>
	</div>
</div>

EOF;
		$icebb->do_output();
		@ob_flush();@flush();

		$this->do_migrate($migrate);
		
		echo <<<EOF
<script type='text/javascript'>
// <![CDATA[
$('migrate_msg_1').style.display	= 'none';
$('migrate_msg_2').style.display	= 'block';

setTimeout("location.replace('index.php?step=5&')",3000);
// ]]>
</script>

EOF;
		@ob_flush();@flush();
	}
	
	function do_migrate(&$migrate)
	{
		global $icebb,$db,$std;
	
		// empty tables
		$db->query("DELETE FROM icebb_users WHERE id>0");
		$db->query("TRUNCATE TABLE icebb_forums");
		$db->query("TRUNCATE TABLE icebb_topics");
		$db->query("TRUNCATE TABLE icebb_posts");
	
		// get existing users
		/*$lastu					= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
		$db->query("SELECT * FROM icebb_users");
		while($db->fetch_row())
		{
			$userslist[$u['username']]= $u;
		}*/
		
		/////////////////////////////////////////////////////////
		// The basics (found on all forums)
		/////////////////////////////////////////////////////////
		
		// migrate groups
		$groups					= $migrate->get_groups();
		if(is_array($groups))
		{
			// we need to choose the group ids, else we just risk overwriting important built-in groups in icebb
			$gid = 7; // we start at 7 since the highest gid in icebb by default is 6
			foreach($groups as $g)
			{
				$g['gid'] = $gid;
				$db->insert('icebb_groups',$g);
				$gid++;
			}
		}
		
		// migrate permission groups
		$permgroups				= $migrate->get_permgroups();
		if(is_array($permgroups))
		{
			foreach($permgroups as $pg)
			{
				$db->insert('icebb_forum_permgroups',$pg);
			}
		}
		
		// migrate users
		$users					= $migrate->get_users();
		if(is_array($users))
		{
			foreach($users as $u)
			{
				$db->insert('icebb_users',$u);
			}
		}
		
		// migrate forums
		$forums					= $migrate->get_forums();
		if(is_array($forums))
		{
			foreach($forums as $f)
			{
				$db->insert('icebb_forums',$f);
			}
		}
		
		// migrate topics
		$topics				= $migrate->get_topics();
		if(is_array($topics))
		{
			foreach($topics as $t)
			{
				$db->insert('icebb_topics',$t);
			}
		}
		
		// migrate posts
		$posts				= $migrate->get_posts();
		if(is_array($posts))
		{
			foreach($posts as $p)
			{
				$db->insert('icebb_posts',$p);
			}
		}
		
		/////////////////////////////////////////////////////////
		// Extras (not found on all forums)
		/////////////////////////////////////////////////////////
		
		// migrate settings if we can
		if(method_exists($migrate,'get_settings'))
		{
			$settings		= $migrate->get_settings();
			if(is_array($settings))
			{
				foreach($settings as $k => $v)
				{
					$db->query("UPDATE icebb_settings SET setting_value='{$v}' WHERE setting_key='{$k}'");
				}
			}
			
			// recache settings
			$settingsq			= $db->query("SELECT * FROM icebb_settings");
			while($set			= $db->fetch_row($settingsq))
			{
				$settings[$set['setting_key']]	= $set['setting_value'];
			}
			$std->recache($settings,'settings');
		}
		
		$this->migrate_if_possible($migrate,'moderators');
		$this->migrate_if_possible($migrate,'banfilters');
		$this->migrate_if_possible($migrate,'pm_topics');
		$this->migrate_if_possible($migrate,'pm_posts');
	}
	
	function migrate_if_possible(&$migrate,$type)
	{
		global $icebb,$db,$std;
		
		$func					= "get_{$type}";
		if(method_exists($migrate,$func))
		{
			$data				= $migrate->$func();
			if(is_array($data))
			{
				foreach($data as $d)
				{
					$db->insert("icebb_{$type}",$d);
				}
			}
		}
	}
	
	function update_lastposts()
	{
		global $icebb,$db,$std;
		
		// for future use
	}
	
	function do_recache()
	{
		global $icebb,$db,$std;
		
		require_once('../includes/classes/cache.inc.php');
		$cache_func				= new cache_func();
		
		$cache_func->rebuild_cache('moderators');
		$cache_func->rebuild_cache('banfilters');
		$cache_func->rebuild_cache('groups');
		$cache_func->rebuild_cache('forums');
		$cache_func->rebuild_cache('settings');
		$cache_func->rebuild_cache('stats');
		
		$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Please stand by, rebuilding cache...</h3>
			<em>Caches rebuilt, preparing to clean up...</em>
		</div>
	</div>
</div>

EOF;

		@header("Refresh: 3;url=index.php?step=6&");
		$icebb->do_output();
	}

	function done_migrate()
	{
		global $icebb;
		
		$fh						= @fopen('migrate.lock','w');
		@fwrite($fh,'Remove this file if you want to use the migrate script.');
		@fclose($fh);

		if(!$fh)
		{
			$warning			= "<strong class='nomercy'>Please remove this script immediately; not doing so could cause your board to be overwritten.</strong>";
		}
		else {
			$warning			= "You may now delete the migrate directory if you wish; you won't need it anymore.";
		}

		$icebb->html			= <<<EOF
<h2>Sucess, all information migrated!</h2>
<p>
All information from your existing board has been moved over to IceBB. You may now
return to your forum.
</p>
<p>
If you or any other members have trouble logging in, they will have to use the
forgotten password feature. This is because some forums store passwords using a
method that is not compatible with IceBB.
</p>

<p>{$warning}</p>

<ul>
	<li>Go to the <a href='../index.php'>Forums</a></li>
	<li>Go to the <a href='../admin.php'>Admin Control Center</a></li>
</ul>

EOF;
		$icebb->do_output();
	}
}
?>
