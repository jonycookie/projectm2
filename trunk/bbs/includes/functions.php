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
// standard functions class
// $Id: functions.php 823 2007-05-16 22:00:07Z daniel159 $
//******************************************************//

define('ROOT_PATH'		, '../');

define('OFFSET_SERVER'	, 1);

require('classes/login_func.php');
require('classes/upload.php');

$login_func			= new login_func;
$upload				= new Upload;

class std_func
{
	function capture_input()
	{
		// GET data
		if(is_array($_GET))
		{
			foreach($_GET as $gname => $gdata)
			{
				if(is_array($_GET[$gname]))
				{
					foreach($_GET[$gname] as $gname2 => $gdata2)
					{
						$input[$this->clean_key($gname)][$this->clean_key($gname2)]= $this->_clean_val($gdata2);
					}
				}
				else {
					$input[$this->clean_key($gname)]	= $this->_clean_val($gdata);
				}
			}
		}
		
		// POST data
		if(is_array($_POST))
		{
			foreach($_POST as $pname => $pdata)
			{
				if(is_array($_POST[$pname]))
				{
					foreach($_POST[$pname] as $pname2 => $pdata2)
					{
						$input[$this->clean_key($pname)][$this->clean_key($pname2)]= $this->_clean_val($pdata2);
					}
				}
				else {
					$input[$this->clean_key($pname)]	= $this->_clean_val($pdata);
				}
			}
		}
		
		if(is_array($input))
		{
			foreach($input as $inpk => $inp)
			{
				$input['ICEBB_QUERY_STRING']   .= "&amp;{$inpk}={$inp}";
			}
		}
		
		$ip										= $this->clean_key($_SERVER['REMOTE_ADDR']);
		$input['ICEBB_USER_IP']					= $ip;
	
		// die $_GET and $_POST!
		//unset($_GET);
		//unset($_POST);
	
		return $input;
	}
	
	/**
	 * Key cleaning (not unicode-safe, but we don't need that in keys
	 */
	function clean_key($k)
	{
		return wash_key($k);
	}
	
	/**
	 * Unicode-safe string cleaning
	 */
	function clean_string($v)
	{
		if(!get_magic_quotes_gpc())
		{
			$v									= addslashes($v);
		}
		
		//$v										= htmlentities($v,ENT_QUOTES,'UTF-8');
		$v										= htmlspecialchars($v);
		$v										= preg_replace("/&amp;#0*([0-9]*);?/",'&#\\1;',$v);
		
		return $v;
	}
	
	/**
	 * @deprecated
	 */
	function _clean_val($v)
	{
		return $this->clean_string($v);
	}
	
	function learn_language()
	{
		global $icebb,$config,$db;
	
		$files					= func_get_args();
	
		include(PATH_TO_ICEBB."langs/{$icebb->lang_id}/global.php");
		$lang_global			= $lang;
		
		foreach($lang_global as $clang_is => $clang)
		{
			$return_lang[$clang_is]= stripslashes($clang);
		}
	
		foreach($files as $file)
		{
			include(PATH_TO_ICEBB."langs/{$icebb->lang_id}/{$file}.php");
			
			foreach($lang as $clang_is => $clang)
			{
				$return_lang[$clang_is]= stripslashes($clang);
			}
		
			unset($lang);
		}
		
		foreach($return_lang as $k => $v)
		{
			$icebb->lang[$k]	= $v;
		}
		
		return $return_lang;
	}
	
	function bakeCookie($name,$val,$ovenType='Kenmore',$httponly=false)	// mmm... cookies
	{
		global $icebb,$config,$db;
	
		// finally rewritten to use a switch
		switch($ovenType)
		{
			case 'Kenmore':
				// leave it for 30 days
				$expiryDate		= time()+60*60*24*30;
				break;
			case 'Whirlpool':
				// leave it for 60 days
				$expiryDate		= time()+60*60*24*60;
				break;
			case 'Kitchenade':
				// leave it for 90 days
				$expiryDate		= time()+60*60*24*90;
				break;
			case 'GE':
				// leave it for a year
				$expiryDate		= time()+60*60*24*365;
				break;
			case 'Old Spanish Bread Oven':			// don't ask
				// clear it!
				$expiryDate		= time()-60*60*24;
				break;
			default:
				// session cookie
				$expiryDate		= 0;
				break;
		}
	
		$_COOKIE[$icebb->settings['cookie_prefix'].$name]= $val;
		
		if($httponly)					// it's not perfect, and it only works in IE, but it helps
		{
			if(PHP_VERSION>=5.2)		// we're using PHP 5.2 or greater, we can use the httponly paramater
			{
				$ret			= setcookie($icebb->settings['cookie_prefix'].$name,$val,$expiryDate,$icebb->settings['cookie_path'],$icebb->settings['cookie_domain'],false,true);
			}
			else {						// fake it
				$ret			= setcookie($icebb->settings['cookie_prefix'].$name,$val,$expiryDate,$icebb->settings['cookie_path'],$icebb->settings['cookie_domain'].'; HttpOnly');
			}
		}
		else {
			$ret				= setcookie($icebb->settings['cookie_prefix'].$name,$val,$expiryDate,$icebb->settings['cookie_path'],$icebb->settings['cookie_domain']);
		}
		
		//if(!$ret) { trigger_error("Unable to set cookie",E_USER_WARNING); }
	
		return $ret;
	}
	
	function eatCookie($cookie_name)
	{
		global $icebb,$config,$db;
		
		if(isset($_COOKIE[$icebb->settings['cookie_prefix'].$cookie_name]))
		{
			return wash_key($_COOKIE[$icebb->settings['cookie_prefix'].$cookie_name]);
		}
		else {
			return false;
		}
	}
	
	function autoLogin()				// we already baked the cookies, right?
	{
		global $icebb,$login_func,$config,$DB,$input;
	
		if(!isset($icebb->user['sid']) && $this->eatCookie('user'))
		{
			$login_func->autoLogin();
		}
	}
	
	function bouncy_bouncy($msg,$bounce_to)		// IT'S SKIPPY! <Skippy> bounce bounce
	{
		global $icebb,$config,$db,$std;
	
		$bounce_to			= str_replace("&amp;","&",$bounce_to);
	
		if(!class_exists('skin_global'))
		{
			require("skins/{$icebb->skin->skin_id}/global.php");
		}
		$global				= new skin_global;
		
		/*if($icebb->settings['redirection_type'] == 'html')
		{
			$this->output		= $global->redirect($msg,$bounce_to);
			echo $this->output;
		}
		else if($icebb->settings['redirection_type'] == 'js')
		{
			$this->bakeCookie('redirect_msg',$msg,false);
			header("Location: {$bounce_to}");
		}
		else if($icebb->settings['redirection_type'] == 'silent')
		{
			header("Location: {$bounce_to}");
		}*/
		
		switch($icebb->settings['redirection_type'])
		{
			default:
			case 'html':
				$this->output		= $global->redirect($msg,$bounce_to);
				echo $this->output;
				break;
			case 'js':
				$this->bakeCookie('redirect_msg',$msg,false);
				header("Location: {$bounce_to}");
				break;
			case 'silent':
				header("Location: {$bounce_to}");
				break;
		}
		
		exit();
	}
	
	function redirect($to)
	{
		$to			= str_replace("&amp;","&",$to);
		@header("Location: {$to}");
		echo "<html><head><meta http-equiv='Refresh: 0;url={$to}' /><script type='text/javascript'>location.replace('{$to}')</script></head></html>";
		exit();
	}
	
	function recache($data,$what_to_recache='')
	{
		global $icebb,$config,$db,$std;
		
		if(is_array($data))
		{
			foreach($data as $data_key => $d)
			{
				if(!is_array($d))
				{
					$data[$data_key]= preg_replace("/&amp;#0*([0-9]*);?/",'&#\\1;',$d);
				}
			}
		}
		else {
			$data			= preg_replace("/&amp;#0*([0-9]*);?/",'&#\\1;',$data);
		}
		
		$data				= addslashes(serialize($data));
		$data				= str_replace('\"','"',$data);
		//echo $data;
		//exit();
		
		if(!empty($what_to_recache))
		{
			$extra			= " WHERE name='{$what_to_recache}'";
		}
		
		$query			= $db->fetch_result("SELECT * FROM icebb_cache{$extra}");
		if($query['result_num_rows_returned']<=0)
		{
			$db->insert('icebb_cache',array(
					'name'				=> $what_to_recache,
					'content'			=> $data,
				));
		}
		else {
			$db->query("UPDATE icebb_cache SET content='{$data}'{$extra}");
		}
	}

	/**
	 * Renders page links
	 * Writing and modifying this gives me a headache >_<
	 */
	function render_pagelinks($data=array(),$type='normal')
	{
		global $icebb,$db,$config;
		
		$global						= new skin_global;
		
		$totalpages					= ceil($data['total']/$data['per_page']);
		$pages_per_group			= 2;
		$active_page				= floor(($data['curr_start']/$data['per_page'])+1);
		$show_first					= true;
		$show_last					= true;
		
		if($type					== 'mini')
		{
			$pages_per_group		= 6;
			$active_page			= -1;
			$show_first				= false;
			
			if($totalpages > 5)
			{
				$dots_mini	= '&hellip;';
			}
			else {
				$dots_mini	= null;
				$show_last	= false;
			}
		}

		if($data['total']			> $data['per_page'])
		{
			// trying to fix this gave me a headache >_<
			for($pageon=1;$pageon<=$totalpages;$pageon++)
			{
				$pg					= array();
				$pg['start']		= ($pageon*$data['per_page'])-$data['per_page'];
			
				if($pageon			== $active_page)
				{
					$pg['active']	= true;
				}
				
				if(($pageon == $totalpages) && $show_last && 
				($pageon < $active_page-$pages_per_group || $pageon > $active_page+$pages_per_group))
				{
					$dots['after']	= true;
					$pg['page']		= $pageon;
					$pages['last']	= $pg;
					continue;
				}
				else if(($pageon == 1) && $show_first && 
				($pageon < $active_page-$pages_per_group || $pageon > $active_page+$pages_per_group))
				{
					$dots['before']	= true;
					$pg['page']		= $pageon;
					$pages['first']	= $pg;
					continue;
				}
				else if($pageon		< $active_page-$pages_per_group)
				{
					$dots['before']	= true;
					continue;
				}
				else if($pageon		> $active_page+$pages_per_group)
				{
					$dots['after']	= true;
					continue;
				}
				
				$pages['main'][$pageon]= $pg;
			}
		}
		
		if(!empty($pages))
		{
			if($type		== 'mini')
			{
				$t			= $global->paginate_mini($data,$pages,$dots_mini);
			}
			else {
				$t			= $global->paginate($data,$pages,$dots);
			}
		}
		
		return $t;
	}
	
	function get_forum_listing()
	{
		global $icebb,$db,$config;
		
		$db->query("SELECT * FROM icebb_forums ORDER BY sort");
		while($forum				= $db->fetch_row())
		//foreach($icebb->cache['forums'] as $forum)
		{
			$this->forums[intval($forum['parent'])][]= $forum;
		}
		
		foreach($this->forums[0] as $f)
		{
			$perms					= unserialize($f['perms']);
		
			if($perms[$icebb->user['g_permgroup']]['seeforum']=='1')
			{
				$f['children']			= $this->get_child_forums($f['fid']);
				$forum_listing[]		= $f;
			}
		}
		
		return $forum_listing;
	}
	
	function get_child_forums($forum='0',$front='--')
	{
		global $icebb,$db,$config;
		
		if(is_array($this->forums[$forum]))
		{
			foreach($this->forums[$forum] as $f)
			{
				$f['name']			= "{$front}{$f['name']}";
				$f['children']		= $this->get_child_forums($f['fid'],$front."--");;
		
				$data[]				= $f;
			}
		}
		
		return $data;
	}
	
	// date and time
	function get_offset($offset_type=0)
	{
		global $icebb;
		
		if($offset_type			== OFFSET_SERVER)
		{
			$time				= 5*3600;
			
			if(gmdate('I',$time))
			{
				$time			= $time+3600;
			}
		}
		else {
			$time				= ($icebb->user['gmt']*3600);
			
			if($icebb->user['dst']=='1' && date('I'))
			{
				$time			= $time+3600;
			}
		}
		
		return $time;
	}
	
	function date_format($format,$ftime)
	{
		global $icebb;
		
		$ftime				= $ftime;
		
		//echo $this->get_offset();
		
		$formatted			= gmdate($format,$ftime+$this->get_offset());
		
		$an_hour_ago		= time()-3600;
		$two_hours_ago		= time()-(3600*2);
		$today				= gmdate('m.d.Y',time()+$this->get_offset());
		$yesterday			= gmdate('m.d.Y',(time()-86400)+$this->get_offset());
			
		if($today		== gmdate('m.d.Y',$ftime+$this->get_offset()))
		{
			$formatted		= "Today, ".gmdate('g:i A',$ftime+$this->get_offset());
		}
		else if($yesterday	== gmdate('m.d.Y',$ftime+$this->get_offset()))
		{
			$formatted		= "Yesterday, ".gmdate('g:i A',$ftime+$this->get_offset());
		}
		
		return $formatted;
	}
	
	// log
	function log($type,$action,$user)
	{
		global $icebb,$db,$config;
				
		if($type=='failed-login-acc')
		{
			// this is to keep us from mailbombing the admin
			//@mail($config['admin_email'],"Failed Admin Control Center login attempt","IP: {$udata['ip']}\r\nTime: ".date('r')."\r\n\r\nThis e-mail is to inform you that someone failed logging in to the IceBB Admin Control Center using the username {$udata['username']} at {$icebb->settings['board_url']}.","From: {$config['admin_email']}");
		}
		
		$db->insert('icebb_logs',array(
			'time'				=> time(),
			'type'				=> $type,
			'user'				=> $user,
			'ip'				=> $this->clean_key($_SERVER['REMOTE_ADDR']),
			'action'			=> $action,
		));
	}
	
	function ra_log($action,$user="",$type='',$forum_id=0)
	{
		global $icebb,$db;
		
		if(empty($user))
		{
			$user				= $icebb->lang['guest'];
		}
	
		$newraid				= $db->fetch_result("SELECT id FROM icebb_ra_logs ORDER BY id DESC LIMIT 0,1");
		$newraid				= $newraid['id']+1;
				
		$userid					= $lastuser['id']+1;
		$ra 					= array(
			'id'				=> $newraid,
			'time'				=> time(),
			'user'				=> $user,
			'ip'				=> $icebb->client_ip,
			'type'				=> $type,
			'action'			=> $action,
			'forum_id'			=> $forum_id,
		);
		
		$db->insert('icebb_ra_logs',$ra);
	}
	
	/**
	 * Resizes an image
	 *
	 * @param		string			Filename
	 * @param		int				Max width
	 * @param		int				Max height
	 */
	function resize_image($filename,$max_w,$max_h)
	{
		global $icebb;
		
		$imgsize						= getimagesize($filename);
		
		list($ow,$oh)					= $imgsize;
		$mime							= $imgsize['mime'];
	
		if($ow >= $max_w || $oh >= $max_h)
		{
			switch($mime)
			{
				case 'image/jpeg':
					$function_create	= 'imagecreatefromjpeg';
					$function_new		= 'imagejpeg';
					break;
				case 'image/gif':
					$function_create	= 'imagecreatefromgif';
					$function_new		= 'imagegif';
					break;
				case 'image/png':
					$function_create	= 'imagecreatefrompng';
					$function_new		= 'imagepng';
					break;
				default:
					$this->error("The image is too large and could not be resized.");
					return false;
					break;
			}
			
			$img						= $function_create($filename);
			
			$w							= $ow;
			$h							= $oh;
			
			if($h > $max_h)
			{
				$w						= ($max_h / $h) * $w;
				$h						= $max_h;
			}
			
			if($w > $max_w)
			{
				$h						= ($max_w / $w) * $h;
				$w						= $max_w;
			}
			
			$newimg						= imagecreatetruecolor($w,$h);
			
			// preserve transparency if we're working with a PNG
			if($mime == 'image/png')
			{
				imagealphablending($newimg,false);
				imagesavealpha($newimg,true);
				
				$transparent			= imagecolorallocatealpha(255,255,255,127);
				imagefill($newimg,0,0,$transparent);
			}
			
			imagecopyresampled($newimg,$img,0,0,0,0,$w,$h,$ow,$oh);
			
			$function_new($newimg,$filename);
			
			return true;
		}
		else {
			return true;
		}
	}
	
	/** 
	 * Allows UTF-8 characters to be displayed
	 * @deprecated
	 */
	function make_utf8_safe($t)
	{
		/*$t				= html_entity_decode($t,ENT_QUOTES);
	
		$t				= str_replace("<","&#60;",$t);
		$t				= str_replace(">","&#62;",$t);
	
		$t				= str_replace("&quot;",htmlspecialchars('"'),$t);
		$t				= preg_replace("/&#0*([0-9]*);?/",'&#\\1;',$t);*/
		
		return $t;
	}
	
	/**
	 * Generates a CAPTCHA code
	 */
	function captcha_makecode()
	{
		global $icebb,$db;
	
		// get code
		$captcha_code		= explode(' ',microtime());
		$captcha_code		= md5($captcha_code[0]);
		
		// open up dictionary
		$words			= file("langs/{$icebb->lang_id}/captcha.dict");
		$words_num		= count($words)-1;
		$word_num		= rand(1,$words_num);
		
		// get IP
		$ip				= empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
		$ip				= $this->clean_key($ip);
		
		// delete old entries
		$db->query("DELETE FROM icebb_captcha WHERE id!='{$captcha_code}' AND ip='{$ip}'");
		
		// insert into db
		$db->query("INSERT INTO icebb_captcha VALUES('{$captcha_code}','{$word_num}','{$ip}')");

		return $captcha_code;
	}
	
	/**
	 * Generates a unique MD5 string - useful for preventing certain <iframe> exploits that post 
	 * under the current logged in member's name
	 */
	function make_me_some_random_md5_kthxbye()
	{
		global $icebb;
	
		if($icebb->user['id']=='0')
		{
			return md5('boo... guests are weird.');
		}
		else {
			return md5($icebb->user['username'].$icebb->user['joindate'].$icebb->user['id']);
		}
	}
	
	/**
	 * Sends an e-mail using our settings
	 */
	function send_mail($to,$title,$body,$headers=array(),$loggit=false)
	{
		global $icebb,$config;
		
		if(!is_array($headers))
		{
			$raw_headers		= $header;
		}
		else {
			if(empty($headers))
			{
				$headers[]		= "From: {$config['admin_email']}";
			}
			
			$headers[]			= "X-Mailer: PHP/".phpversion();
		}
	
		$extra_replaces			= array(
			'<#board_name#>'	=> $icebb->settings['board_name'],
			'<#board_url#>'		=> $icebb->settings['board_url'],
			'<#username#>'		=> $icebb->user['username'],
			'<#ip#>'			=> $icebb->client_ip,
		);
	
		$title					= str_replace(array_keys($extra_replaces),array_values($extra_replaces),$title);
		$body					= str_replace(array_keys($extra_replaces),array_values($extra_replaces),$body);
	
		if(empty($raw_headers))
		{
			$raw_headers		= implode("\r\n",$headers);
		}
	
		$r						= @mail($to,$title,$body,$raw_headers);
		
		if($loggit)
		{
			if($r)
			{
				$this->log('mail',"{$icebb->user['username']} sent an e-mail to {$to}",$icebb->user['username']);
			}
			else {
				$this->log('mail',"{$icebb->user['username']} failed to send an e-mail to {$to}",$icebb->user['username']);
			}
		}
		
		return $r;
	} 
	
	/**
	 * Displays an error message
	 *
	 * @param		string		String with error message
	 * @param		boolean		Show login screen if we're not logged in?
	*/
	function error($t='',$show_login=0)
	{
		global $icebb;
	
		if(!is_array($t))
		{
			$msg			= $t;
		}
		else {
			$msg			= $t['msg'];
			$type			= $t['type'];
		}
		
		if(($type			== 'login' ||
		   $show_login		== 1) && 
		   empty($icebb->user['id']))
		{
			$show_login	= 1;
		}
	
		$std				= &$this;
		
		if($show_login)
		{
			$icebb->lang	= $std->learn_language('login');
		}
		
		require("skins/{$icebb->skin->skin_id}/error.php");
		$error				= new skin_error;
		$output				= $error->error_page($msg,$show_login);
		$icebb->skin->html_insert($output);
		$icebb->skin->do_output();
		exit();
	}
}

// WHY THE HELL WEREN'T THESE INCLUDED IN PHP? FUNCTIONS
// -----------------------------------------------------

// a wubly function from "phillip" at php.net
function in_string($needle, $haystack, $insensitive = 0) {
   if ($insensitive) {
       return (false !== @stristr($haystack, $needle)) ? true : false;
   } else {
       return (false !== @strpos($haystack, $needle))  ? true : false;
   }
}

// give our key a good scrubbin'
function wash_key($k)
{
	$k		= htmlspecialchars($k,ENT_QUOTES);

	return $k;
}

// wash the ebul tags!
function wash_ebul_tags($k)
{
	//$k		= preg_replace("/javascript:/i"		, "j&#97;v&#97;script:"	,$k);

	return $k;
}

// make it safe!
function makeSqlSafe($t)
{
	$t		= htmlspecialchars($t);
	
	return $t;
}

// thanks v0rbiz@yahoo.com!
function uniord($u)
{
   $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
   $k1 = ord(substr($k, 0, 1));
   $k2 = ord(substr($k, 1, 1));
   return $k2 * 256 + $k1;
}

// make salt
function make_salt($length=5)
{
	// required for PHP <4.2.0
	srand((double)microtime()*1000000);

	for($i=0;$i<$length;$i++)
	{
		$numba			= rand(40,126);
		if($numba		== '92')
		{
			$numba		= '93';
		}
		$salt		   .= chr($numba);
	}
	
	return $salt;
}

function _wordwrap($str,$cols,$cut=" ")
{
	$len			= strlen($str);
	$tag			= 0;
	
	for($i=0;$i<$len;$i++)
	{
		$chr			= $str[$i];
		if($chr == '<')
		{
		   $tag++;
		}
		else if($chr == '>')
		{
		   $tag--;
		}
		// ignore &nbsp;
		else if($chr=='&' && $str[$i+1]=='n' && $str[$i+2]=='b' && $str[$i+3]=='s' && $str[$i+4]=='p' &&
			   $str[$i+5]==';')
		{
			// do nothing
		}
		else if((!$tag) && (ctype_space($chr)))
		{
			$wordlen = 0;
		}
		else if(!$tag)
		{
			$wordlen++;
		}

		if ((!$tag) && ($wordlen) && (!($wordlen % $cols)))
		{
			$chr .= $cut;
		}

		$result .= $chr;
	}

	// added by me to clean up htmlspecialchars()
	$result		= preg_replace("`& #([0-9]*);`","&#\\1; ",$result);
	$result		= preg_replace("`&#([0-9]*) ;`","&#\\1; ",$result);
	$result		= preg_replace("`&# ([0-9]*);`","&#\\1; ",$result);
	$result		= preg_replace("`&#[0-9] ([0-9]*);`","&#\\1\\2; ",$result);
	$result		= preg_replace("`&#([0-9]*) [0-9];`","&#\\1\\2; ",$result);

   return $result;
}

/**
 * Replacement function for substr() when support for html entities is required
 *
 * @author kovacsendre@kfhik.hu
 */
function html_substr($str, $start, $length = NULL) {
  if ($length === 0) return ""; //stop wasting our time ;)

  //check if we can simply use the built-in functions
  if (strpos($str, '&') === false) { //No entities. Use built-in functions
   if ($length === NULL)
     return substr($str, $start);
   else
     return substr($str, $start, $length);
  }

  // create our array of characters and html entities
  $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
  $html_length = count($chars);

  // check if we can predict the return value and save some processing time
  if (
       ($html_length === 0) /* input string was empty */ or
       ($start >= $html_length) /* $start is longer than the input string */ or
       (isset($length) and ($length <= -$html_length)) /* all characters would be omitted */
     )
   return "";

  //calculate start position
  if ($start >= 0) {
   $real_start = $chars[$start][1];
  } else { //start'th character from the end of string
   $start = max($start,-$html_length);
   $real_start = $chars[$html_length+$start][1];
  }

  if (!isset($length)) // no $length argument passed, return all remaining characters
   return substr($str, $real_start);
  else if ($length > 0) { // copy $length chars
   if ($start+$length >= $html_length) { // return all remaining characters
     return substr($str, $real_start);
   } else { //return $length characters
     return substr($str, $real_start, $chars[max($start,0)+$length][1] - $real_start);
   }
  } else { //negative $length. Omit $length characters from end
     return substr($str, $real_start, $chars[$html_length+$length][1] - $real_start);
  }

}


// defines
define('FORM_METHOD_GET', 2358241);
define('FORM_METHOD_POST',3352345);

function get_input($key,$method)
{
	if($method	== FORM_METHOD_GET)
	{
		$teh	= wash_key($_GET[$key]);
	}
	else {
		$teh	= wash_key($_POST[$key]);
	}

	return wash_key($teh);
}

// for PHP < 5.1
if(!function_exists('htmlspecialchars_decode'))
{
   function htmlspecialchars_decode($text)
   {
       return strtr($text,array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
   }
}

function pr($var, $return=false)
{
	return print_r($var, $return);
}
function vd($var)
{
	var_dump($var);
}

// END WHY THE HELL WEREN'T THESE INCLUDED IN PHP? FUNCTIONS
// ---------------------------------------------------------
?>
