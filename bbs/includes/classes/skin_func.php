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
// skin functions class
// $Id: skin_func.php 749 2007-02-16 14:07:05Z daniel159 $
//******************************************************//

class skin_func
{
	var $output;
	var $loaded_templates	= array();
	var $skin_id;
	var $rss_link			= '';
	var $rss_links			= array();
	var $header_extra;

	function load_skin($skin_id='')
	{
		global $icebb,$db,$error_handler;

		if(empty($skin_id))
		{
			// load from cache, r0x0ring of course :P 
			if(is_array($icebb->cache['skins']))
			{
				foreach($icebb->cache['skins'] as $da => $val)
				{
					if($val['skin_is_default']=='1')
					{
						foreach($icebb->cache['skins'][$da] as $daz => $valt)
						{
							$skindata[$daz]= html_entity_decode($valt);
						}
						
						$this->default_skin=$da;
					}
				}
			}
			else {
				$skinq		= $db->query("SELECT * FROM icebb_skins WHERE skin_is_default=1 LIMIT 1");
				$skindata	= $db->fetch_row($skinq);
				$this->default_skin=$skindata['skin_id'];
			}
		}
		else {
			// load from cache, r0x0ring of course :P 
			if(is_array($icebb->cache['skins'][$skin_id]))
			{
				foreach($icebb->cache['skins'][$skin_id] as $da => $val)
				{
					$skindata[$da]= html_entity_decode($val);
				}
			}
			else {
				$skinq		= $db->query("SELECT * FROM icebb_skins WHERE skin_id='{$skin_id}' LIMIT 1");
				$skindata	= $db->fetch_row($skinq);
			}
		}
		
		if($skindata['skin_is_hidden']=='1' && $icebb->user['g_is_admin']!='1')
		{
			$error_handler->skin_error('load',"This skin is hidden from members");
		}
		
		//$this->skin_id		= $skindata['skin_id'];
		$this->skin_id		= empty($skindata['skin_folder']) ? $skindata['skin_id'] : $skindata['skin_folder'];
		
		return $skindata;
	}

	function load_template($template)
	{
		global $icebb,$std,$error_handler;
		
		$this->output	= "";

		if(file_exists("skins/{$this->skin_id}/{$template}.php"))
		{
			require_once("skins/{$this->skin_id}/{$template}.php");
		}
		else {
			$error_handler->skin_error('load',"The template '{$template}' could not be loaded");
		}
		
		$this->loaded_templates[]= $template;
		
		$name_template		= "skin_{$template}";
		
		return new $name_template();
	}

	function html_insert($html)
	{
		$this->output  .= $html;
	}

	function do_popup_window($output2)
	{
		global $icebb,$db,$timer,$std;
	
		$wrapper			= $icebb->skin_data['skin_wrapper'];
		$wrapper			= str_replace('<#content_type#>',$icebb->skin_data['use_content_type'],$wrapper);
		$output				= $wrapper;
		//$output				= $icebb->html_global->popup_window();
		
		//$css				= "<style type='text/css' media='screen'>@import 'skins/{$this->skin_id}/css.css'</style>\n";
		$css				= "<link rel='stylesheet' type='text/css' href='skins/{$this->skin_id}/css.css' />\n";
		$css			   .= "<!--[if lte IE 6]>
<link rel='stylesheet' type='text/css' href='skins/{$this->skin_id}/ie6.css' />
<![endif]-->";
	
		
		$jscripts[]			= "<script type='text/javascript' src='jscripts/prototype/prototype.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/scriptaculous/scriptaculous.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/global.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/xmlhttp.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/menu.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/md5.js'></script>";
		$jscripts[]			= "<script type='text/javascript'>icebb_base_url='{$icebb->base_url}';icebb_sessid='{$icebb->user['sid']}';icebb_cookied_domain='{$icebb->settings['cookie_domain']}';icebb_cookied_prefix='{$icebb->settings['cookie_prefix']}';icebb_cookied_path='{$icebb->settings['cookie_path']}';</script>";
		$js					= implode("\n", $jscripts);
		
		$replacies			= array(
			'TITLE'			=> $icebb->settings['board_name'],
			'CSS'			=> $css,
			'JAVASCRIPT'	=> $js,
			'CONTENT'		=> $output2,
			'HEADER_EXTRA'	=> '',
			'HEADER'		=> '',
			'LOGIN_BAR'		=> '',
			'CRUMBS'		=> '',
			'REDIRECT_STUFF'=> '',
			'FOOTER'		=> '',
			'STATS'			=> '',
			'COPYRIGHT'		=> '',
		);
		
		foreach($replacies as $replacemeat => $replaceme)
		{
			$output			= str_replace("<#{$replacemeat}#>",$replaceme,$output);
		}
		die($output);
		$macros				= array();
		
		// might as well do macros
		$macroq				= $db->query("SELECT * FROM icebb_skin_macros");
		while($m			= $db->fetch_row($macroq))
		{
			$macros[$m['string']]= $m['replacement'];
		}
		
		foreach($macros as $macros_are_not_good_to_eat => $please_dont_macrosoft_me)
		{
			$output			= str_replace("<{{$macros_are_not_good_to_eat}}>",$please_dont_macrosoft_me,$output);
			$output			= str_replace("<macro:".strtolower($macros_are_not_good_to_eat)." />",$please_dont_macrosoft_me,$output);
		}
		
		// <#SKIN#>
		$output				= str_replace("<#SKIN#>",$icebb->skin_data['skin_id'],$output);
				
		//echo $output;
		$icebb->set_output($output2);
	}

	function do_output()
	{
		global $icebb,$db,$timer,$std,$license_info,$config;
	
		// are we supposed to be showing the debug page instead?
		if($icebb->input['debug']=='1' && $icebb->settings['enable_debug'])
		{
			$this->loaded_templates[]= 'global';
			$loaded_templates= implode(', ',$this->loaded_templates);
		
			if($icebb->settings['hide_version_in_cpy']!='1')
			{
				$version	= ICEBB_VERSION;
			}
		
			$engine			= empty($config['db_engine']) ? 'mysql' : $config['db_engine'];
		
			$debug_output	= <<<EOF
<html>
<head>
<title>IceBB Debug</title>
<style type='text/css'>
@import 'skins/{$this->skin_id}/debug.css';
</style>
</head>
<body bgcolor='#ffffff'>
<div class='border'>
<h1>IceBB Debug</h1>
<h2>SQL (Total Queries: {$db->queries} / Total Time: {$db->total_time}sec)</h2>
<table width='100%' cellpadding='2' cellspacing='1' border='0'>
	<tr>
		<td class='col2'>DB Engine:</td>
		<td class='col1'>{$engine}</td>
	</tr>
{$db->debug_html}
</table>

<h2>Skin</h2>
<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<td class='col2'>
			Skin ID:
		</td>
		<td class='col1'>
			{$this->skin_id}
		</td>
	</tr>
	<tr>
		<td class='col2'>
			Loaded templates:
		</td>
		<td class='col1'>
			{$loaded_templates}
		</td>
	</tr>
</table>
</div>

<div id='info'>IceBB {$version} / <a href='index.php' onclick='history.go(-1);return false'>Back to forum</a></div>
</body>
</html>
EOF;
			$icebb->set_output($debug_output);
			exit();
		}
		
		$icebb->skin_data['use_content_type']= 'text/html';
	
		$wrapper			= $icebb->skin_data['skin_wrapper'];
		$wrapper			= str_replace('<#content_type#>',$icebb->skin_data['use_content_type'],$wrapper);
		$output				= $wrapper;
		
		// ha ha
		//@header("Content-type: {$icebb->skin_data['use_content_type']}");
		
		//$css			= "<style type='text/css' media='screen'>@import 'skins/{$this->skin_id}/css.css'</style>\n";
		$css				= "<link rel='stylesheet' type='text/css' href='skins/{$this->skin_id}/css.css' />\n";
		$css			   .= "<!--[if lte IE 6]>
<link rel='stylesheet' type='text/css' href='skins/{$this->skin_id}/ie6.css' />
<![endif]-->";
		
		$jscripts[]			= "<script type='text/javascript' src='jscripts/prototype/prototype.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/scriptaculous/scriptaculous.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/global.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/xmlhttp.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/menu.js'></script>";
		$jscripts[]			= "<script type='text/javascript' src='jscripts/md5.js'></script>";
		
		$baseurl			= str_replace('&amp;','&',$icebb->base_url);
		$jscripts[]			= <<<EOF
<script type='text/javascript'>
// <![CDATA[
icebb_base_url='{$baseurl}';icebb_sessid='{$icebb->user['sid']}';icebb_cookied_domain='{$icebb->settings['cookie_domain']}';icebb_cookied_prefix='{$icebb->settings['cookie_prefix']}';icebb_cookied_path='{$icebb->settings['cookie_path']}';
// ]]>
</script>

EOF;
		
		$js					= implode("\n",$jscripts);
		
		if($icebb->settings['clean_urls']=='1')
		{
			$css			= "<base href='{$icebb->settings['board_url']}' />\r\n".$css;
		}
		
		if(!class_exists('skin_global'))
		{
			require("skins/{$this->skin_id}/global.php");
		}
		$global				= new skin_global;
		
		// are we going to be showing the debug stats?
		if($icebb->settings['enable_debug'])
		{
			$loadavg_file	= @file_get_contents('/proc/loadavg');
			$loadavg_file	= explode(' ',$loadavg_file);
			$loadavg		= trim($loadavg_file[0]);
			
			if(empty($loadavg))
			{
				$uptime_exec= explode(' ',@exec('uptime'));
				
				if(!empty($uptime_exec))
				{
					$loadavg= $uptime_exec[count($uptime_exec)-1];
				}
				else {
					$loadavg= '--';
				}
			}
		
			$stats			= $global->stats($db->queries,$timer->stop('main',6),$loadavg);
		}
		
		if(is_array($icebb->cache['skins']))
		{
			foreach($icebb->cache['skins'] as $skin)
			{
				if($skin['skin_is_hidden']=='1')
				{
					if($icebb->user['g_is_admin']=='1')
					{
						if($skin['skin_id']==$icebb->skin_data['skin_id'])
						{
							$skin_options.= "\t<option value='{$skin['skin_id']}' selected='selected'>{$skin['skin_name']} (Hidden)</option>\n";
						}
						else {
							$skin_options.= "\t<option value='{$skin['skin_id']}'>{$skin['skin_name']} (Hidden)</option>\n";
						}
					}
				}
				else {
					if($skin['skin_id']==$icebb->skin_data['skin_id'])
					{
						$skin_options.= "\t<option value='{$skin['skin_id']}' selected='selected'>{$skin['skin_name']}</option>\n";
					}
					else {
						$skin_options.= "\t<option value='{$skin['skin_id']}'>{$skin['skin_name']}</option>\n";
					}
				}
			}
		}
		
		if(is_array($icebb->cache['langs']))
		{
			foreach($icebb->cache['langs'] as $lang)
			{
				if($lang['lang_short']==$icebb->lang_id)
				{
					$lang_options.= "\t<option value='{$lang['lang_short']}' selected='selected'>{$lang['lang_name']}</option>\n";
				}
				else {
					$lang_options.= "\t<option value='{$lang['lang_short']}'>{$lang['lang_name']}</option>\n";
				}
			}
			
			foreach($icebb->cache['langs'] as $lang)
			{
				if($lang['lang_is_default']=='1')
				{
					$to_replace_langbits= unserialize($lang['lang_bits_cache']);
					if(is_array($to_replace_langbits))
					{
						foreach($to_replace_langbits as $lbit)
						{
							$output= str_replace("<lang:{$lbit['langbit_group']}='{$lbit['langbit_name']}' />");
						}
					}
				}
			}
		}
		
		$title				= strip_tags($icebb->nav[count($icebb->nav)-1]);
		$title			   .= empty($title) ? "" : ' - ';
		$title			   .= $icebb->settings['board_name'];
		
		if(!empty($this->rss_link))
		{
			$this->rss_links[]= array($icebb->lang['rss_feed'],$this->rss_link);
		}
		
		if(!empty($this->rss_links))
		{
			foreach($this->rss_links as $l)
			{
				$this->header_extra.= "<link rel='alternate' type='application/rss+xml' title='{$l[0]}' href='{$l[1]}' />";
			}
		}
		
		$redirect_msg		= $std->eatCookie('redirect_msg');
		if(!empty($redirect_msg))
		{
			$redirect_stuff	= $global->redirect_inpage($redirect_msg);
			$std->bakeCookie('redirect_msg','','Old Spanish Bread Oven'); // clear the cookie
		}
		else {
			$redirect_stuff	= "";
		}
		
		$replacies			= array(
			'TITLE'			=> $title,
			'CSS'			=> $css,
			'JAVASCRIPT'	=> $js,
			'HEADER_EXTRA'	=> $this->header_extra,
			'HEADER'		=> $header,
			'LOGIN_BAR'		=> $user_bar,
			'CONTENT'		=> $this->output,
			'SKIN_CHOOSER'	=> $global->skin_chooser($skin_options),
			'LANG_CHOOSER'	=> $global->lang_chooser($lang_options),
			'FOOTER'		=> '',
			'CRUMBS'		=> '',
			'STATS'			=> $stats,
			'REDIRECT_STUFF'=> $redirect_stuff,
		);
		
		foreach($replacies as $replacemeat => $replaceme)
		{
			$output			= str_replace("<#{$replacemeat}#>",$replaceme,$output);
		}
		
		$macros				= unserialize($icebb->skin_data['skin_macro_cache']);
		
		/*
		// might as well do macros
		$macroq				= $db->query("SELECT * FROM icebb_skin_macros");
		while($m			= $db->fetch_row($macroq))
		{
			$macroso[$m['string']]= $m['replacement'];
		}
		
		$db->query("UPDATE icebb_skins SET skin_macro_cache='".addslashes(serialize($macroso))."' WHERE skin_id='{$icebb->skin_data['skin_id']}'");
		*/
		
		foreach($macros as $macros_are_not_good_to_eat => $please_dont_macrosoft_me)
		{
			$output			= str_replace("<{{$macros_are_not_good_to_eat}}>",$please_dont_macrosoft_me,$output);
			$output			= str_replace("<macro:".strtolower($macros_are_not_good_to_eat)." />",$please_dont_macrosoft_me,$output);
		}
		
		// <#SKIN#>
		$output				= str_replace("<#SKIN#>",$this->skin_id,$output);
		
		if($icebb->settings['hide_version_in_cpy']!='1')
		{
			$version_show	= ICEBB_VERSION;
		}
		
		// YOU CANNOT REMOVE OR CHANGE THE COPYRIGHT BELOW
		// PLEASE BE AWARE THAT MODIFICATION OR REMOVAL OF THE FOLLOWING LINE WILL PREVENT YOU FROM GETTING SUPPORT
		$copyright			= "<div class='copyright'>Powered by <a href='http://icebb.net/'>IceBB</a> {$version_show} &copy; ".date('Y')." XAOS Interactive</div>";

		// just a hook for plugin/skin authors to add additional info to the copyright
		$copyright		   .= $icebb->copyright_addition;

		// YOU DON'T NEED TO CHANGE THESE LINES, THE COPYRIGHT CAN BE REMOVED OR CHANGED ABOVE
		if(!in_string("<#COPYRIGHT#>",$output))
		{
			$output		   .= $copyright;
		}
		$output				= str_replace('<#COPYRIGHT#>',$copyright,$output);
		
		// using GZIP?
		if($icebb->settings['enable_gzip']=='1')
		{
			@ob_end_flush();
			@ob_start('ob_gzhandler');
		}
		
		$icebb->set_output($output);
		
		if($icebb->settings['enable_gzip']=='1')
		{
			@ob_end_flush();
		}
		
		$this->output	= "";
	}
}
?>
