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
// error handler
// $Id: error_handler.php 371 2006-08-04 17:34:07Z daniel $
//******************************************************//

define('SQL_ERROR'		, 112790);
define('TEMPLATE_ERROR'	, 294305);

$error_handler			= new error_handler();

/**
 * A simple error handler class. Handles SQL errors and PHP errors.
 * Inspired by the feature in MyTopix
 *
 * @package		IceBB
 * @version		1.0 Beta 7
 * @date		July 23, 2005
 */
class error_handler
{
	var $show_code_snippet;

	/**
	 * Constructor
	 */
	function error_handler()
	{
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		set_error_handler(array(&$this,"__error_handle"));
		
		// hacky workaround to catch ALL errors, even fatal and parse errors:
		// http://us3.php.net/manual/en/function.set-error-handler.php#35622
		//@ob_start(array(&$this,"__catch_fatal_error"));
		
		$this->show_code_snippet	= 1;
		
		// friendly error messages
		$this->friendly_error		= array(
			E_ERROR					=> "PHP Fatal Error",
			E_WARNING				=> "PHP Warning",
			E_PARSE					=> "PHP Parse Error",
			1024					=> "PHP User Error",
			SQL_ERROR				=> "SQL Error",
			TEMPLATE_ERROR			=> "Skin Template Error",
		);
	}
	
	/**
	 * Handles skin error
	 * 
	 * @param		string	$errtype	Type of skin error: load, function call, etc.
	 * @param		string	$errstr		Error message
	 * @param		string	$errfile	Error file (will attempt to determine automatically if not included)
	 * @param		int		$errline	Error line (will attempt to determine automatically if not included)
	 * @param		string	$errcontext	Error context (???)
	 */
	function skin_error($errtype,$errstr,$errfile=0,$errline=0,$errcontext=array())
	{
		if(!error_reporting())
		{
			return;
		}
		
		$errno						= TEMPLATE_ERROR;
		
		if(empty($errfile) || empty($errline))
		{
			$backtrace				= debug_backtrace();
			$errfile				= $backtrace[1]['file'];
			$errline				= $backtrace[1]['line'];
		}
		
		$errfile					= str_replace(@getcwd(),'',$errfile);
		
		if($errtype					== 'load')
		{
		}
		
		$custarray					= array('Getting this message often?'=>"If you experience problems with this skin, you may <a href='index.php?skinid=1&amp;sticky'>use the default</a>. If you continue to have problems, contact a board admin.");
		
		$this->__error($errno,$errstr,$errfile,$errline,$custarray);
	}
	
	/**
	 * Handles errors
	 *
	 * @param		int		$errno		Error number
	 * @param		string	$errstr		Error string
	 * @param		string	$errfile	Error file
	 * @param		int		$errline	Error line
	 * @param		string	$errcontext	Error context (???)
	 */
	function __error_handle($errno,$errstr,$errfile,$errline,$errcontext)
	{
		if(!error_reporting())
		{
			return;
		}
		
		if($errno==8)
		{
			return;
		}
		
		switch($errno)
		{
			case E_WARNING:
				//$this->__warning($errno,$errstr,$errfile,$errline,$errcontext);
				echo "<strong>PHP Warning</strong> [{$errno}]: {$errstr} in {$errfile} on line {$errline}<br />";
				break;
			case E_PARSE:
				echo "<strong>PHP Warning</strong> [{$errno}]: {$errstr} in {$errfile} on line {$errline}<br />";
				break;
			case E_STRICT:
				break;
			case TEMPLATE_ERROR:
				//$errfile			= str_replace(@getcwd(),'',$errfile);
				
				//$custarray			= array('Getting this message often?'=>"If you experience problems with this skin, you may <a href='index.php?skinid=1&amp;sticky'>use the default</a>. If you continue to have problems, contact a board admin.");
			
				$this->skin_error($errstr,$errfile,$errline,$errcontext);
			
				//$this->__error($errno,$errstr,$errfile,$errline,$custarray);
				break;
			default:
				$errfile			= str_replace(@getcwd(),'',$errfile);
				$errmsg				= "{$errstr} on line {$errline} in {$errfile}";			
			
				$this->__error($errno,$errmsg,$errfile,$errline,$errcontext);
				break;
		}
	}
	
	/**
	 * Catch fatal errors
	 * http://us3.php.net/manual/en/function.set-error-handler.php#35622
	 */
	function __catch_fatal_error($buffer)
	{
		global $icebb;
	
		if(ereg("(error</b>:)(.+)(<br)",$buffer,$regs)) 
		{
			$err					= preg_replace("/<.*?>/","",$regs[2]);
			preg_match("`(.+?) in (.+?) on line ([0-9]*)`i",$err,$matches);
			
			//echo print_r($err,1);
			//echo $err;
			//ob_end_flush();
			//exit();
			
			if(strpos(strtolower($matches[1]),'syntax') || strpos(strtolower($matches[1]),'parse'))
			{
				$errno					= E_PARSE;
			}
			else {
				$errno					= E_ERROR;
			}
			
			$errstr					= $matches[1];
			$errfile				= str_replace($icebb->settings['board_path'],'',$matches[2]);
			$errline				= $matches[3];
			$errmsg					= "{$errstr} in /{$errfile} on line {$errline}";
			
			$admin_email			= $icebb->config['admin_email'];
			
			return <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>IceBB Application Error</title>
<script type='text/javascript'>
function _getbyid(objid)
{
	if(document.getElementById)
	{
		return document.getElementById(objid);
	}
	else if(document.all)
	{
		return eval("document.all."+objid);
	}
	else if(document.layers)
	{
		return eval("document."+objid);
	}
}

function _toggle_view(objid)
{
	obj			= _getbyid(objid);

	if(obj.style.display=='none')
	{
		obj.style.display='';
	}
	else {
		obj.style.display='none';
	}
}
</script>
<style type='text/css'>
body
{
    background-color:#FFFFFF;
    text-align:center;
    font-family: Verdana, Arial, sans-serif;
    font-size:13px;
    margin:0px;
    color:#2a2727;
	 line-height: 1.4em;
    text-align:center;
}

h1
{
background: #648cbf;
color: #f7f8f9;
padding: 4px;
margin: 0 0 1px 0;
font-size: 12px;
}

.border
{
background: #FFF;
border: 1px solid #383631;
padding: 1px;
	font-size:80%;
	width:99.6%;
	text-align:left;
	margin:6px auto;
}

th
{
background: #dee6f1;
color: #000000;
font-size: 11px;
padding: 2px;
}

.col1
{
   background-color: #f3f5f8;
}

.col2
{
   background-color: #eef1f5;
}

fieldset
{
   border:2px solid #ccddee;
}

fieldset legend
{
   color:#667788;
   font-weight:bold;
}
</style>
</head>
<body bgcolor='#ffffff'>
<div class='border'>
	<h1>Sorry, IceBB has encountered an application error and is unable to continue.</h1>
	
	<fieldset>
		<legend>Information</legend>
		IceBB encountered an error that prevents it from being able to continue. This error has been logged. Please contact an administrator at <a href='mailto:{$admin_email}'>{$admin_email}</a> if the problem persists.
	</fieldset>
	
	<fieldset>
		<legend>{$this->friendly_error[$errno]}:</legend>
		{$errmsg}
	</fieldset>
</div>

<div id='info'>IceBB {$version} / <a href='index.php' onclick='history.go(-1);return false'>Back to forum</a></div>
</body>
</html>
EOF;
		}
		
		return $buffer;
	}
	
	/**
	 * Displays the error screen
	 *
	 * @param		int		$errno		Error number
	 * @param		string	$errstr		Error string
	 * @param		string	$errfile	Error file
	 * @param		int		$errline	Error line
	 * @param		array	$custom		Array of custom error boxes
	 */
	function __error($errno,$errmsg,$errfile,$errline,$custom=array())
	{
		// custom boxes?
		foreach($custom as $cuid => $cut)
		{
			 $custom_messages	.= "<fieldset><legend>{$cuid}</legend>{$cut}</legend></fieldset>";
		}

		$admin_email			= $icebb->config['admin_email'];

		echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>IceBB Application Error</title>
<script type='text/javascript'>
function _getbyid(objid)
{
	if(document.getElementById)
	{
		return document.getElementById(objid);
	}
	else if(document.all)
	{
		return eval("document.all."+objid);
	}
	else if(document.layers)
	{
		return eval("document."+objid);
	}
}

function _toggle_view(objid)
{
	obj			= _getbyid(objid);

	if(obj.style.display=='none')
	{
		obj.style.display='';
	}
	else {
		obj.style.display='none';
	}
}
</script>
<style type='text/css'>
body
{
    background-color:#FFFFFF;
    text-align:center;
    font-family: Verdana, Arial, sans-serif;
    font-size:13px;
    margin:0px;
    color:#2a2727;
	 line-height: 1.4em;
    text-align:center;
}

h1
{
background: #648cbf;
color: #f7f8f9;
padding: 4px;
margin: 0 0 1px 0;
font-size: 12px;
}

.border
{
background: #FFF;
border: 1px solid #383631;
padding: 1px;
	width:99.6%;
	text-align:left;
	margin:6px auto;
	width:80%;
}

th
{
background: #dee6f1;
color: #000000;
font-size: 11px;
padding: 2px;
}

.col1
{
   background-color: #f3f5f8;
}

.col2
{
   background-color: #eef1f5;
}

fieldset
{
   border:2px solid #ccddee;
}

fieldset legend
{
   color:#667788;
   font-weight:bold;
}
</style>
</head>
<body bgcolor='#ffffff'>
<div class='border'>
	<h1>Sorry, IceBB has encountered an application error and is unable to continue.</h1>
	
	<fieldset>
		<legend>Information</legend>
		IceBB encountered an error that prevents it from being able to continue. This error has been logged. Please contact an administrator at <a href='mailto:{$admin_email}'>{$admin_email}</a> if the problem persists.
	</fieldset>
	
	<fieldset>
		<legend>{$this->friendly_error[$errno]}:</legend>
		{$errmsg}
	</fieldset>
	
	{$custom_messages}

EOF;

if($this->show_code_snippet==1)
{

$code_pre			= preg_replace("`\t`","    ",@file_get_contents(@getcwd().$errfile));
$code				= explode("<br />",highlight_string($code_pre,1));
$code2				= "<pre>";
$code2			   .= $code[$errline-3]."\n";
$code2			   .= $code[$errline-2]."\n";
$code2			   .= "<div style='background-color:#ffffcc;white-space:pre;'>".$code[$errline-1]."</div>";
$code2			   .= $code[$errline];
$code2			   .= "</code>";

echo <<<EOF
	<fieldset>
		<legend>Code Snippet</legend>
		{$code2}
	</fieldset>

EOF;

}

echo <<<EOF
</div>
</body>
</html>
EOF;
		@ob_end_flush();
		exit();
	}
}
?>