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
// admin functions include
// $Id: admin_functions.php 248 2005-07-28 22:11:24Z mutantmonkey $
//******************************************************//

define('ROOT_PATH'		, '../');

if(!defined('IN_ICEBB'))
{
	die('This file may not be accessed directly.');
}

require('skin_func.php');

class adm_func
{
	function output()
	{
		global $icebb;

		echo $this->html;
		
		$ver			= ICEBB_VERSION;
	}
	
	function learn_language($file)
	{
		global $icebb,$config,$db;
	
		include("langs/{$config['lang']}/global.php");
		$lang_global			= $lang;
		
		foreach($lang_global as $clang_is => $clang)
		{
			$return_lang[$clang_is]= stripslashes($clang);
		}
	
		include("langs/{$config['lang']}/{$file}.php");
		
		foreach($lang as $clang_is => $clang)
		{
			$return_lang[$clang_is]= stripslashes($clang);
		}
		
		unset($lang);
		
		foreach($return_lang as $k => $v)
		{
			$icebb->lang[$k]	= $v;
		}
		
		return $return_lang;
	}
	
	function redirect($text,$url,$target='')
	{
		global $icebb,$config,$db,$std;
	
		$this->page_title		= "&nbsp;";
	
		if($target				== '_top')
		{
			$this->html			= "<script type='text/javascript'>setTimeout(\"top.location.replace('{$url}')\",3000)</script>\n";
		}
		else {
			$this->html			= "<script type='text/javascript'>setTimeout(\"location.replace('{$url}')\",3000)</script>\n";
		}
		
		$this->html			   .= $icebb->admin_skin->start_table("Please stand by...");
		$this->html			   .= $icebb->admin_skin->table_row($text);
		$this->html			   .= $icebb->admin_skin->end_table();
	
		if(!class_exists('skin_global')) require('skins/default/global.php');
		$hehe					= new skin_global;
		$this->html				= $hehe->header().$this->html.$hehe->footer();
	
		$this->output();
		exit();
	}
	
	function error($msg)
	{
		global $icebb;
	
		$this->html			   .= $icebb->admin_skin->start_table("Error");
		$this->html			   .= $icebb->admin_skin->table_row($msg);
		$this->html			   .= $icebb->admin_skin->end_table();
		
		if(!class_exists('skin_global')) require('skins/default/global.php');
		$hehe					= new skin_global;
		$this->html				= $hehe->header().$this->html.$hehe->footer();
	
		$this->output();
		exit();
	}
}
?>