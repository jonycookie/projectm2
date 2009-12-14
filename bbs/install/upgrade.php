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
// upgrade module
// $Id$
//******************************************************//

error_reporting(E_ERROR | E_PARSE | E_WARNING);

// DEFINE EVERYTHING
// -----------------

define('IN_ICEBB'				, '1');			// to prevent external access
define('VERID'					, '100000');

// END DEFINE EVERYTHING
// ---------------------

//die("There haven't been any database changes, so the upgrade script is not necessary.");

if(@file_get_contents('upgrade.lock') == VERID)
{
	echo "The upgrade script is locked. The upgrade script can only be run once.";
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

$db						= new db_mysql();

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
	var $verid;

	function icebb()
	{
		global $db,$std,$config,$input,$session;
	
		$this->input		= $input;
		$this->verid		= VERID;
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
		<div id='info'>This script will help you upgrade IceBB.</div>
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
					<strong>Existing version of IceBB:</strong>
				</td>
				<td class='col1'>
EOF;

if(file_exists('../config.php'))
{
	$icebb->html   .= "<span class='span'>Installed</span>";
}
else {
	@header('Location: index.php');
	$icebb->html   .= "<span class='nomercy'>Not Installed</span>";
	$unsupported[]	= 'icebb';
}

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
			<form action='upgrade.php' method='get'>
				<input type='hidden' name='step' value='2' />
				<input type='submit' value='Continue' class='form_input' />
			</form>
		</td>
	</tr>
					
EOF;
				}

$icebb->html	   .= <<<EOF
		</tr>
	</tr>
</table>
</div>
EOF;
				
				$icebb->do_output();
				break;
			case '2':
				$this->run_queries();
				break;
			case '3':
				$this->recache();
				break;
			case '4':
				$this->be_done();
				break;
		}
	}
	
	function run_queries()
	{
		global $icebb,$std;
		
        //require('../includes/classes/mysql.php');
		$database				= new db_mysql();
		// if I'm lucky, this will actually work
		include('dbstructure_upgrade.php');
		
		if(is_array($drops))
		{
			foreach($drops as $query)
			{
				$database->query($query);
			}
		}
		
		if(is_array($creates))
		{
			foreach($creates as $query)
			{
				$database->query($query);
			}
		}
			
		if(is_array($inserts))
		{
			foreach($inserts as $query)
			{
				$database->query($query);
			}
		}
		
		if(is_array($alters))
		{
			foreach($alters as $query)
			{
				$database->query($query);
			}
		}
        
		/*$database->query("DELETE FROM icebb_skins");
        $database->query(file_get_contents('skin.sql'));*/

		$icebb->html		= <<<EOF
<div class='border'>
	<div class='block'>
		<div class='bottom'>
			<h3>Please stand by, IceBB is being upgraded...</h3>
			<em>Database sucessfully upgraded, preparing to recache commonly used information</em>
	</div>
</div>

EOF;
		@header("Refresh: 3;url=upgrade.php?step=3");
		
		$icebb->do_output();
	}
	
	function recache()
	{
		global $icebb,$db,$config,$std;

		//$db					= new mysql();

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
		
		$fh						= @fopen('upgrade.lock','w');
		@fwrite($fh,$icebb->verid);
		@fclose($fh);
		
		if(!$fh)
		{
			$warning			= "<strong class='nomercy'>Please remove this script immediately; not doing so could cause your board to be overwritten.</strong>";
		}
		else {
			$warning			= "You may now delete the install directory if you wish.";
		}
		
		
		$icebb->html			= <<<EOF
<h2>Upgrade successful.</h2>
<p>
IceBB has been upgraded.
</p>

<p>
{$warning}
</p>

<ul>
	<li>Go to the <a href='../index.php'>Forums</a></li>
	<li>Go to the <a href='../admin/'>Admin Control Center</a></li>
</ul>

EOF;
		
		$icebb->do_output();
	}
}
?>
