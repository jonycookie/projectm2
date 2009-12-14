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
// installer module
// $Id: index.php 821 2007-05-05 19:31:45Z mutantmonkey0 $
//******************************************************//

// DEFINE EVERYTHING
// -----------------

define('IN_ICEBB'				, '1');			// to prevent external access

$database_engines				= array(
'mysql'							=> "MySQL",
//'mysqli'						=> "MySQLi (PHP 5+, MySQL 4.1+)",
//'postgres'					=> "PostgreSQL",
//'sqlite'						=> "SQlite",
);

// END DEFINE EVERYTHING
// ---------------------

if(@file_exists('install.lock'))
{
	echo "The installer is locked. You must remove install.lock from this directory should you wish to reinstall IceBB.";
	exit();
}

require('../includes/classes/error_handler.php');

@include('../config.php');

require('../includes/classes/timer.php');
require('../includes/database/mysql.db.php');

require('../includes/functions.php');
$std					= new std_func;

$timer->start('main');

$input					= $std->capture_input();

if(!empty($config) && $input['step']>4)
{
	$db					= new db_mysql();
}

// WRAP IT UP
// ----------
// Wrap everything up in a nice reusable class here...

$icebb					= new icebb();
class icebb
{
	var $config;
	var $skin;
	var $input;
	var $html;
	var $base_url		= "index.php?";
	var $output;
	var $help_html;

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
<title>IceBB Installer</title>
<style type='text/css'>
@import 'install.css';
</style>
<script type='text/javascript' src='../jscripts/global.js'></script>
<script type='text/javascript' src='install.js'></script>
</head>
<body bgcolor='#ffffff'>
<div id="welcome">
	<div id="header">
		<h1>Welcome</h1>
		<div id='info'>We need some information from you before you can use IceBB</div>
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

// END WRAP IT UP
// --------------

$installer				= new install();
class install
{
	function install()
	{
		global $icebb;
		
		switch($icebb->input['step'])
		{
			default:
				$icebb->help_html	= <<<EOF
<div class='help' id='help-createconfig'>
Please create the file config.php in your IceBB root directory. Then, use the CHMOD command in your FTP client. Set the
CHMOD value to 0777.
</div>
<div class='help' id='help-writable'>
To make this file writable, use the CHMOD command in your FTP client. For the value, enter 0777.
</div>
<div class='help' id='help-old_php'>
Your version of PHP is out of date. Please ask your web host to upgrade PHP to the latest version.
</div>

EOF;
			
				$unsupported		= array();
			
$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Checking server setup...</h3>
			<em>To ensure that your server is compatible with IceBB</em>
		</div>
		<table width='100%' cellpadding='5' cellspacing='2' border='0'>
			<tr>
				<td class='col2' width='40%'>
					<strong>PHP Version:</strong><br />
					<em>4.3.x+ is required</em>
				</td>
				<td class='col1'>
EOF;

$phpversion			= explode('.',phpversion());
if(($phpversion[0]==4 && $phpversion[1]>=2) || $phpversion[0]>=5)
{
	$icebb->html   .= "<span class='span'>Compatible</span>";
}
else {
	$icebb->html   .= "<span class='nomercy'>Not Compatible (<a href='#' onmouseover=\"show_help('old_php',this)\" onmouseout=\"hide_help('old_php')\">?</a>)</span>";
	$unsupported[]	= 'php';
}

$icebb->html	   .= <<<EOF
				</td>
			</tr>
			<tr>
				<td class='col2'>
					<strong>config.php writable?</strong>
				</td>
				<td class='col1'>
EOF;
if(is_writable('../config.php'))
{
	$icebb->html   .= "<span class='span'>Writable</span>";
}
else if(!file_exists('../config.php'))
{	
	if (!is_writable("../")) 
	{
		$icebb->html 	.= "<span class='nomercy'>Does not exist (<a href='#' onmouseover=\"show_help('createconfig',this)\" onmouseout=\"hide_help('createconfig')\">?</a>)</span>";
	}
	else {
		if (touch("../config.php"))
		{
			$icebb->html .= "<span class='span'>File created.</span>";
		} else {
			$icebb->html .= "<span class='nomercy'>Unable to create file.</span>";
		}
	}
}	
else {
	$icebb->html   .= "<span class='nomercy'>Not Writable (<a href='#' onmouseover=\"show_help('writable',this)\" onmouseout=\"hide_help('writable')\">?</a>)</span>";
	$notwritable[]	= 'config.php';
}
$icebb->html	   .= <<<EOF
				</td>
			</tr>
			<tr>
				<td class='col2'>
					<strong>uploads/ folder</strong>
				</td>
				<td class='col1'>
EOF;
if(is_writable('../uploads/'))
{
	$icebb->html   .= "<span class='span'>Writable</span>";
}
else {
	$icebb->html   .= "<span class='nomercy'>Not Writable (<a href='#' onmouseover=\"show_help('writable',this)\" onmouseout=\"hide_help('writable')\">?</a>)</span>";
	$notwritable[]	= 'uploads/';
}
$icebb->html	   .= <<<EOF
				</td>
			</tr>
			<tr>
				<td class='col2'>
					<strong>skins/ folders</strong>
				</td>
				<td class='col1'>
EOF;
if(is_writable('../skins/1/') || is_writable('../skins/2/') || is_writable('../skins/'))
{
	$icebb->html   .= "<span class='span'>Writable</span>";
}
else {
	$icebb->html   .= "<span class='nomercy'>Not Writable (<a href='#' onmouseover=\"show_help('writable',this)\" onmouseout=\"hide_help('writable')\">?</a>)</span>";
	$notwritable[]	= 'skins/';
}
$icebb->html	   .= <<<EOF
				</td>
			</tr>

EOF;
/*</table>
</div>
EOF;*/

				if(count($unsupported)>=1)
				{
					//$icebb->html	= "<div class='border row2' style='width:80%;margin:6px auto 0px auto;padding:3px;text-align:center'><span style='float:left;color:#660000;font-size:150%;font-weight:normal;margin-top:-6px;padding:2px 2px 2px 2px'>X</span>Please check the IceBB system requirements and try again after you have updated your server.</div>".$icebb->html;
				}
				else if(count($notwritable)>=1)
				{
					//$icebb->html	= "<div class='border row2' style='width:80%;margin:6px auto 6px auto;padding:3px;text-align:center'><span style='float:left;color:#660000;font-size:150%;font-weight:normal;margin-top:-6px;padding:2px 2px 2px 2px'>X</span>Please CHMOD the files marked with an X 777.</div>".$icebb->html;
				}
				else {
					$icebb->html  .= <<<EOF
			<tr>
				<td colspan='2' style='text-align:center'>
					<form action='index.php' method='get'>
						<input type='hidden' name='step' value='2' />
						<input type='submit' value='Continue' class='form_input' />
					</form>
				</td>
			</tr>
					
EOF;
				}

$icebb->html	   .= <<<EOF
		</table>
	</div>
</div>

EOF;
				
				$icebb->do_output();
				break;
			case '2':
				$this->show_configinator_page();
				break;
			case '3':
				$this->do_install();
				break;
			case '4':
				$this->run_queries();
				break;
			case '5':
				$this->recache();
				break;
			case '6':
				$this->be_done();
				break;
		}
	}
	
	function show_configinator_page($msg=array())
	{
		global $icebb,$database_engines;
		
		$icebb->help_html	= <<<EOF
<div class='help' id='help-sql_host'>
This is the server your IceBB installation will connect to for storing or reading topics, posts, users, and more.
Unless your web host has told you otherwise, you can leave this as localhost.
</div>
<div class='help' id='help-sql_user'>
This is the username IceBB will connect as when storing or reading topics, posts, users, and more. IceBB cannot create
this for you; you'll have to create it yourself in your hosting control panel.
</div>
<div class='help' id='help-sql_pass'>
This is the password IceBB uses to connect to the database.
</div>
<div class='help' id='help-sql_database'>
This is the database IceBB will store it's information in. Please ensure the user you have entered above has permission to
access this database.
</div>
<div class='help' id='help-sql_prefix'>
If you're trying to install more than one IceBB in the same database, you'll have to change this value to keep the installer
from overwriting your previous installation. This can be changed to anything you want.
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
		$icebb->input['db_prefix']= empty($icebb->input['db_prefix']) ? 'icebb_' : $icebb->input['db_prefix'];
		$icebb->input['board_name']= empty($icebb->input['board_name']) ? 'My IceBB Board' : $icebb->input['board_name'];
		$icebb->input['board_path']= empty($icebb->input['board_path']) ? preg_replace('`install(/?)`i','',@getcwd()) : $icebb->input['board_path'];
		$icebb->input['board_path']= str_replace('\\','/',$icebb->input['board_path']); // I hate backslashes in paths...
		$url			= "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
		$url			= preg_replace('`install(.*)`','',$url);
		$icebb->input['board_url']= empty($icebb->input['board_url']) ? $url : $icebb->input['board_url'];
		
		foreach($database_engines as $real => $fake)
		{
			if($icebb->input['db_engine']==$real)
			{
				$extra			= " selected='selected'";
			}
			else {
				$extra			= '';
			}
		
			$dbengine		   .= "\t\t\t\t\t\t\t<option value='{$real}'{$extra}>{$fake}</option>";
		}
		
		if(count($database_engines)>1)
		{
			$dbengine_html		= <<<EOF
						<select name='db_engine'>
{$dbengine}
						</select>

EOF;
		}
		else {
			$dbe				= array_values($database_engines);
			$dbengine_html		= "{$dbe[0]}";
		}
		
		$icebb->html			.= <<<EOF
<form action='index.php' method='post'>
	<input type='hidden' name='step' value='3' />
	{$msg['db']}
	<div class='border'>
		<div class='block'>
			<div class='bottom'>
				<h3>Database Configuration</h4>
				<em>Enter your database information here. If you're not sure, contact your web host as we cannot provide or fix this for you</em>
			</div>
			
			<table width='100%' cellpadding='5' cellspacing='2' border='0'>
				<tr>
					<td width='40%'>
						<strong>Database Engine</strong><br />
						<em>MySQL (or MySQLi) is the best choice, however you may use others if you prefer</em>
					</td>
					<td>
{$dbengine_html}
					</td>
				</tr>
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
						<em>The username you use to connect to your database</em>
					</td>
					<td>
						<input type='text' name='db_user' value='{$icebb->input['db_user']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Password</strong><br />
						<em>The password you use to connect to your database</em>
					</td>
					<td>
						<input type='text' name='db_pass' value='{$icebb->input['db_pass']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Database</strong><br />
						<em>The database you want to install IceBB in - must already exist</em>
					</td>
					<td>
						<input type='text' name='db_database' value='{$icebb->input['db_database']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>SQL Table Prefix</strong><br />
						<em>Useful for installing multiple IceBBs in the same database</em>
					</td>
					<td>
						<input type='text' name='db_prefix' value='{$icebb->input['db_prefix']}' class='textbox' />
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	{$msg['settings']}
	<div class='border'>
		<div class='block'>
			<div class='bottom'>
				<h3>General Settings</h3>
				<em>You're almost there, don't give up yet!</em>
			</div>
			
			<table width='100%' cellpadding='5' cellspacing='2' border='0'>
				<tr>
					<td width='40%'>
							<strong>Board Name</strong>
					</td>
					<td>
						<input type='text' name='board_name' value='{$icebb->input['board_name']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
							<strong>Board Path</strong><br />
							<em>The path to your board (not URL!); this can usually be left alone</em>
					</td>
					<td>
						<input type='text' name='board_path' value='{$icebb->input['board_path']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
							<strong>Board URL</strong><br />
							<em>The URL to your board; this can also usually be left alone</em>
					</td>
					<td>
						<input type='text' name='board_url' value='{$icebb->input['board_url']}' class='textbox' />
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	{$msg['user']} 
	<div class='border'>
		<div class='block'>
			<div class='bottom'>
				<h3>Administrator Account</h3>
				<em>Here you can create the account that you will use to login to your board</em>
			</div>
			<table width='100%' cellpadding='5' cellspacing='2' border='0'>
				<tr>
					<td width='40%'>
							<strong>Username</strong>
					</td>
					<td>
						<input type='text' name='admin_user' value='{$icebb->input['admin_user']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>Password</strong>
					</td>
					<td>
						<input type='password' name='admin_pass' value='{$icebb->input['admin_pass']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>Password again</strong>
					</td>
					<td>
						<input type='password' name='admin_pass2' value='{$icebb->input['admin_pass2']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>E-mail address</strong><br />
						<em>Your e-mail address - must be valid!</em>
					</td>
					<td>
						<input type='text' name='admin_email' value='{$icebb->input['admin_email']}' class='textbox' />
					</td>
				</tr>
				<tr>
					<td width='40%'>
						<strong>E-mail address again</strong>
					</td>
					<td>
						<input type='text' name='admin_email2' value='{$icebb->input['admin_email2']}' class='textbox' />
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<div class='border'>
		<div class='block' style='text-align:center'>
			<input type='submit' name='submit' value='Install' class='button' />
		</div>
	</div>
</form>
EOF;
		
		$icebb->do_output();
	}
	
	function do_install()
	{
		global $icebb,$database_engines;
		
		$engines			= array_keys($database_engines);
		if(in_array($icebb->input['db_engine'],$engines))
		{
			$engine			= $icebb->input['db_engine'];
		}
		else {
			$engine			= 'mysql';
		}
		
		$towrite			= "<?php
//******************************************************//
// IceBB Configuration File
//******************************************************//
// Please do not edit this file unless you know what
// you're doing. This stores all the information
//******************************************************//

// You shouldn't touch these unless you're moving to a
// different server
\$config['db_engine']	= '{$engine}';
\$config['db_host']		= '{$icebb->input['db_host']}';
\$config['db_user']		= '{$icebb->input['db_user']}';
\$config['db_pass']		= '{$icebb->input['db_pass']}';
\$config['db_prefix']	= '{$icebb->input['db_prefix']}';
\$config['db_database']		= '{$icebb->input['db_database']}';

\$config['cookie_prefix']	= 'icebb_';

// Change this if you don't want to receive MySQL errors via
// e-mail
\$config['admin_email']	= '{$icebb->input['admin_email']}';

\$config['lang']			= 'en';

// You can change the root users here. You'll need their ID which
// can be determined by their profile URL. The username will NOT
// work. Example: \$config['root_users']	= '1,5,6';
\$config['root_users']	= '1';
?>"; 
		
		$fh						= @fopen('../config.php','w');
		if(!$fh)
		{
			die("You fool, don't try to trick the installer into allowing you to install without write permissions on config.php");
		}
		else {
			@fwrite($fh,$towrite);
			@fclose($fh);
		}
		
		if(empty($icebb->input['admin_user']))
		{
			$this->show_configinator_page(array('user'=>"You must enter a username."));
			exit();
		}
		
		if(empty($icebb->input['admin_pass']))
		{
			$this->show_configinator_page(array('user'=>"You must enter a password."));
			exit();
		}
		
		if($icebb->input['admin_pass']!=$icebb->input['admin_pass2'])
		{
			$this->show_configinator_page(array('user'=>"Passwords do not match"));
			exit();
		}
		
		if(empty($icebb->input['admin_email']))
		{
			$this->show_configinator_page(array('user'=>"You must enter an e-mail address."));
			exit();
		}
		
		if($icebb->input['admin_email']!=$icebb->input['admin_email2'])
		{
			$this->show_configinator_page(array('user'=>"E-mail addresses do not match"));
			exit();
		}
		
		//require('../config.php');
		
		$config					= array();
		$config['db_engine']	= $engine;
		$config['db_host']		= $icebb->input['db_host'];
		$config['db_user']		= $icebb->input['db_user'];
		$config['db_pass']		= $icebb->input['db_pass'];
		$config['db_prefix']	= $icebb->input['db_prefix'];
		$config['db_database']	= $icebb->input['db_database'];
		
		$icebb->config			= $config;
		
		//print_r($config);
		//exit();
		
		$led					= mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
		$led2					= mysql_select_db($config['db_database']);
		if(!$led || !$led2)
		{
			$this->show_configinator_page(array('db'=>"Unable to connect to database. Please check your database information.<br /><strong>Error: ".mysql_error()."</strong>"));
			exit();
		}
		
		$mysql_version			= mysql_query("SELECT VERSION() as version");
		if(!$m					= mysql_fetch_assoc($mysql_version))
		{
			$mysql_version2		= mysql_query("SHOW VARIABLES LIKE 'version'");
			$m					= mysql_fetch_assoc($mysql_version2);
		}
		$version				= $m['version'];
		$version				= explode('.',$version);
		
		$version[1]				= isset($version[1]) ? $version[1] : 00;
		$version[2]				= isset($version[2]) ? $version[2] : 00;
		$mversion				= sprintf('%d%02d%02d',$version[0],$version[1],intval($version[2]));
		
		if($mversion	   	   <= 40000)
		{
			$this->show_configinator_page(array('db'=>"IceBB requires at least MySQL 4.0. You'll have to get your web host to upgrade."));
		}
		
		if(empty($icebb->html))
		{
			$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Please stand by, IceBB is installing...</h3>
			<em>config.php updated, preparing to insert into the database</em>
	</div>
</div>

EOF;

			@header("Refresh: 3;url=index.php?step=4&board_name={$_POST['board_name']}&board_path={$_POST['board_path']}&board_url={$_POST['board_url']}&admin_user={$_POST['admin_user']}&admin_pass=".md5($_POST['admin_pass'])."&");
		}
		
		$icebb->do_output();
	}
	
	function run_queries()
	{
		global $icebb,$std,$db,$config;
		
        //require('../includes/classes/mysql.php');
		$db						= new db_mysql();
		$db->prefix				= $config['db_prefix'];
		// if I'm lucky, this will actually work
		include('dbstructure.php');
		foreach($drops as $query)
		{	
			$db->query($query);
        }
		foreach($creates as $query)
		{
			$db->query($query);
        }
		foreach($inserts as $query)
		{
			$db->query($query);
        }
        
        $db->query(file_get_contents('skin.sql'));
		
		$salty			= md5(crypt(make_salt(27)));
		$pass_hashed	= md5($icebb->input['admin_pass'].$salty);
		
		$db->insert('icebb_users',array(
			'id'				=> '0',
			'username'			=> 'Guest',
			'user_group'		=> '4',
			'date_format'		=> 'F j, Y @ g:i A',
		));
		
		$db->insert('icebb_users',array(
			'id'				=> '1',
			'username'			=> $icebb->input['admin_user'],
			'password'			=> $pass_hashed,
			'pass_salt'			=> $salty,
			'user_group'		=> '1',
			'joindate'			=> time(),
			'posts'				=> '0',
		));
		
		$db->insert('icebb_forums',array(
			'fid'				=> '1',
			'sort'				=> '1',
			'name'				=> "Welcome",
			'perms'				=> 'a:5:{i:1;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:1;s:5:"reply";i:1;s:6:"attach";i:1;}i:2;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:1;s:5:"reply";i:1;s:6:"attach";i:1;}i:3;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:0;s:5:"reply";i:0;s:6:"attach";i:0;}i:4;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:0;s:5:"reply";i:0;s:6:"attach";i:0;}i:5;a:5:{s:8:"seeforum";i:0;s:4:"read";i:0;s:12:"createtopics";i:0;s:5:"reply";i:0;s:6:"attach";i:0;}}',
		));
		
		$db->insert('icebb_forums',array(
			'fid'				=> '2',
			'sort'				=> '1',
			'parent'			=> '1',
			'name'				=> "Welcome to IceBB",
			'description'		=> "Welcome, and thank you for choosing IceBB. This forum contains some information you might want to read.",
			'perms'				=> 'a:5:{i:1;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:1;s:5:"reply";i:1;s:6:"attach";i:1;}i:2;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:1;s:5:"reply";i:1;s:6:"attach";i:1;}i:3;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:0;s:5:"reply";i:0;s:6:"attach";i:0;}i:4;a:5:{s:8:"seeforum";i:1;s:4:"read";i:1;s:12:"createtopics";i:0;s:5:"reply";i:0;s:6:"attach";i:0;}i:5;a:5:{s:8:"seeforum";i:0;s:4:"read";i:0;s:12:"createtopics";i:0;s:5:"reply";i:0;s:6:"attach";i:0;}}',
			'postable'			=> '1',
			'topics'			=> '1',
			'lastpostid'		=> '1',
			'lastpost_title'	=> "Welcome to IceBB",
			'lastpost_time'		=> time(),
			'lastpost_author'	=> "XAOS Interactive",
		));
				
		$msg					= <<<EOF
Hello,

We'd like to welcome you to IceBB, a powerful, fast, and free forum solution. We've
spent many years on its development, so we hope you find a use for it.

You can login to your admin control center by logging in using the username and
password you chose during the installation process. If you forgot these, you will need
to run the installer again.

Good luck creating your community,

XAOS Interactive
EOF;
		
		$msg			= addslashes($msg);
		
		$t				= array(
						'tid'			=> '1',
						'forum'			=> '2',
						'title'			=> 'Welcome to IceBB',
						'snippet'		=> substr($msg,0,255),
						'starter'		=> 'XAOS Interactive',
						'lastpost_time'	=> time(),
						'lastpost_author'=> 'XAOS Interactive',
						'has_poll'		=> 0,
						'views'			=> 0,
						);
	
		$db->insert('icebb_topics',$t);
		
		$p				= array(
						'pid'			=> '1',
						'ptopicid'		=> '1',
						'pauthor_id'	=> '0',
						'pauthor_ip'	=> '127.0.0.1',
						'pdate'			=> time(),
						'ptext'			=> $msg,
						'pis_firstpost'	=> '1',
						);
		
		$db->insert('icebb_posts',$p);
		
		$db->query("UPDATE icebb_forums SET topics=1,lastpostid='1',lastpost_time=".time().",lastpost_title='Welcome to IceBB',lastpost_author='XAOS Interactive' WHERE fid='2' LIMIT 1");
		
		$cache['stats']['posts']= 1;
		$cache['stats']['topics']= 1;
		$cache['stats']['replies']= 0;
		$std->recache($cache['stats'],'stats');
		
		$db->query("UPDATE icebb_settings SET setting_value='{$icebb->input['board_name']}' WHERE setting_key='board_name'");
		$db->query("UPDATE icebb_settings SET setting_value='{$icebb->input['board_url']}' WHERE setting_key='board_url'");
		$db->query("UPDATE icebb_settings SET setting_value='".str_replace('\\','&#92;',$icebb->input['board_path'])."' WHERE setting_key='board_path'");

		$db->query("UPDATE icebb_settings SET setting_value='' WHERE setting_key='cookie_domain'");
		$db->query("UPDATE icebb_settings SET setting_value='' WHERE setting_key='cookie_path'");
		$db->query("UPDATE icebb_settings SET setting_value='icebb_' WHERE setting_key='cookie_prefix'");

		$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Please stand by, IceBB is installing...</h3>
			<em>Information successfully inserted into the database, preparing to cache commonly used information</em>
	</div>
</div>

EOF;
		@header("Refresh: 3;url=index.php?step=5");
		
		$icebb->do_output();
	}
	
	function recache()
	{
		global $icebb,$db,$config,$std;

		// -- SETTINGS -- //
		$settingsq			= $db->query("SELECT * FROM icebb_settings");
		while($set			= $db->fetch_row($settingsq))
		{
			$settings[$set['setting_key']]	= $set['setting_value'];
		}
		$std->recache($settings,'settings');


		// -- BBCODE -- //
		$bbcodeq			= $db->query("SELECT * FROM icebb_bbcode");
		while($b			= $db->fetch_row($bbcodeq))
		{
			$b['code']		= $b['code'];
			$b['replacement']= $b['replacement'];
			$b['php']		= $b['php'];
			$bbcode[]		= $b;
		}
		$std->recache($bbcode,'bbcode');
		

		// -- SMILIES -- //
		$smiliesq			= $db->query("SELECT * FROM icebb_smilies");
		while($s			= $db->fetch_row($smiliesq))
		{
			$s['code']		= $s['code'];
			$s['image']		= $s['image'];
			$smilies[$s['smiley_set']][]= $s;
		}
		$std->recache($smilies,'smilies');
		
		// -- FORUMS -- //
		$db->query("SELECT * FROM icebb_forums");
		while($f			= $db->fetch_row())
		{
			$forums[$f['fid']]	= $f;
		}
		$std->recache($forums,'forums');

		// -- SKINS -- //
		$db->query("SELECT * FROM icebb_skins");
		while($s			= $db->fetch_row())
		{
			$skins[$s['skin_id']]= $s;
		}
		$std->recache($skins,'skins');
		
		// -- LANGS -- //
		$db->query("SELECT * FROM icebb_langs");
		while($l			= $db->fetch_row())
		{
			foreach($l as $lkey => $lval)
			{
				$l[$lkey]	= wash_key(str_replace("&amp;","&",$lval));
			}
		
			$langs[$s['lang_short']]= $l;
			if($l['lang_is_default'])
			{
				$default	= $l['lang_short'];
			}
		}
		$langs['default']	= $default;
		$std->recache($langs,'langs');
		
		// -- TASKS -- //
		$db->query("SELECT * FROM icebb_tasks");
		while($t			= $db->fetch_row())
		{
			foreach($t as $tkey => $tval)
			{
				$t[$tkey]	= wash_key(str_replace("&amp;","&",$tval));
			}
		
			$tasks[]		= $t;
		}
		$std->recache($tasks,'tasks');
		
		// -- GROUPS -- //
		$db->query("SELECT * FROM icebb_groups");
		while($g			= $db->fetch_row())
		{
			foreach($g as $gkey => $gval)
			{
				$g[$gkey]	= wash_key(str_replace("&amp;","&",$gval));
			}
		
			$groups[]		= $g;
		}
		$std->recache($groups,'groups');
		
		// -- RANKS -- //
		$db->query("SELECT * FROM icebb_ranks");
		while($r			= $db->fetch_row())
		{
			foreach($r as $rkey => $rval)
			{
				$r[$rkey]	= wash_key(str_replace("&amp;","&",$rval));
			}
		
			$ranks[]		= $r;
		}
		$std->recache($ranks,'ranks');
		
		// -- BIRTHDAYS -- //
		$db->query("SELECT * FROM icebb_users");
		while($u			= $db->fetch_row())
		{
			if(!empty($u['birthdate']))
			{
				$bds['uid']	= $u['id'];
				$bds['username']= $u['username'];
				$bds['agetobe']= 0;
				
				$u['birthdate']= @explode('.',@date('m.d.Y',$u['birthdate']));
				$bd['bmonth']= $u['birthdate'][0];
				$bd['bday']= $u['birthdate'][1];
				$bd['byear']= $u['birthdate'][2];
				
				$bdays[$bd['bmonth']][$bd['bday']][]= $bds;
			}
		}
		$std->recache($bdays,'birthdays');
		
		// -- STATS -- //
		$cache_result				= $db->fetch_result("SELECT COUNT(*) as count FROM icebb_users");
		$cache_result2				= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
		$cache_result3				= $db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
		$stocache['posts']			= $cache_result3['posts'];
		$stocache['user_count']		= $cache_result['count'];
		$stocache['user_newest']	= $cache_result2;
		$std->recache($stocache,'stats');

		$this->be_done();
	}
	
	function be_done()
	{
		global $icebb;
		
		$fh						= @fopen('install.lock','w');
		@fwrite($fh,'install.lock -_-');
		@fclose($fh);
		
		if(!$fh)
		{
			$warning			= "<strong class='nomercy'>Please remove this script immediately; not doing so could cause your board to be overwritten.</strong>";
		}
		else {
			$warning			= "You may now delete the install directory if you wish.";
		}
		
		
		$icebb->html			= <<<EOF
<h2>Welcome to IceBB!</h2>
<p>
On behalf of the XAOS Interactive team, we'd like to welcome you to IceBB. Your board has been installed sucessfully. You may
follow the links below to set up your board. Alternatively, you may choose to <a href='../migrate/'>import your existing forum</a>.
</p>

<p>
{$warning}
</p>

<ul>
	<li>Go to the <a href='../index.php'>Forums</a></li>
	<li>Go to the <a href='../admin/'>Admin Control Center</a></li>
	<li>Help me <a href='../migrate/'>Import my existing forum</a></li>
</ul>

EOF;
		
		$icebb->do_output();
	}
}
?>
