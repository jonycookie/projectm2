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
// post parser class
// $Id: post_parser.php 763 2007-02-16 21:59:29Z mutantmonkey0 $
//******************************************************//

/**
 * A post parser class
 *
 * @package		IceBB
 * @version		1.0
 * @date		$Date$
 */
class post_parser
{
	var $smilies_list		= array();
	var $word_filters		= array();
	var $parse_quotes		= 1;
	var $quote_open			= 0;
	var $quote_closed		= 0;
	var $wysiwyg			= 0;

	var $url_regex_protocol	= "(ftp|http|https|ed2k|imap|irc|news|pop|sftp|ssh|telnet|ventrilo|webcal)://";
	var $url_regex_main		= "([\w\.\?/&-:;,\-@%\+\=#~]+)";
	var $url_regex			= "";

	/**
	 * Constructor
	 */
	function post_parser()
	{
		global $icebb,$db,$config,$std;
		
		$this->url_regex			= $this->url_regex_protocol . $this->url_regex_main;
		
		$set				= $icebb->skin_data['smiley_set'];
		if(is_array($icebb->cache['smilies'][$set]))
		{
			foreach($icebb->cache['smilies'][$set] as $s)
			{
				$this->smilies_list[]= $s;
			}
		}
		
		if(is_array($icebb->cache['word_filters']))
		{
			foreach($icebb->cache['word_filters'] as $bw)
			{
				$this->word_filters[]= $bw;
			}
		}
		
		$db->query("SELECT * FROM icebb_uploads");
		while($u			= $db->fetch_row())
		{
			$this->uploads[$u['uid']]= $u;
		}
		
		if(!class_exists('skin_global'))
		{
			require(PATH_TO_ICEBB."skins/{$icebb->skin->skin_id}/global.php");
		}
		$this->html_global			= new skin_global;
	}

	/**
	 * Parses BBCode
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function bbcode($t)
	{
		global $icebb,$db,$config,$std;
		
		// this is to keep a few things from breaking, shouldn't break anything
		$t							= str_replace('$', '&#36;', $t);
		
		//  get rid of empty BBCode, is there a point in having excess markup?
		$t							= preg_replace("`\[(b|i|u|url|mail|img|quote|code|php|tt)\]\[/(b|i|u|url|mail|img|quote|code|php|tt)\]`",'',$t);
		
		// first handle defaults, as much as I'd like to make everything customizable I can't >_< 
		$t							= preg_replace("`\[b\](.*)\[/b\]`sUi","<b>\\1</b>",$t);
		$t							= preg_replace("`\[i\](.*)\[/i\]`sUi","<i>\\1</i>",$t);
		$t							= preg_replace("`\[u\](.*)\[/u\]`sUi","<u>\\1</u>",$t);
		
		$t							= preg_replace("`\[tt\](.*)\[/tt\]`sUi","<tt class='bbcode'>\\1</tt>",$t);

		$t							= preg_replace("`\[color=(#[a-z0-9]*|[a-z]*)\](.*)\[/color\]`sUi","<span style='color:\\1'>\\2</span>",$t);
		$t							= preg_replace("`\[size=([0-9]*)\](.*)\[/size\]`sUie","\$this->bbcode_handle_size('\\1','\\2')",$t);
		$t							= preg_replace("`\[font=([a-z0-9@, ]*)\](.*)\[/font\]`sUi","<span style='font-family:\\1'>\\2</span>",$t);

		$t							= preg_replace("`\[url\]{$this->url_regex}\[/url\]`sUi","<a href='$1://$2'>$1://$2</a>",$t);
		$t							= preg_replace("`\[url\={$this->url_regex}\](.+?)\[/url\]`si","<a href='$1://$2'>$3</a>",$t);
		
		$t							= preg_replace("`\[mail\](.+?)\[/mail\]`ei","\$this->bbcode_handle_mail('\\1')",$t);
		
		$t							= preg_replace("`\[img\]{$this->url_regex_main}\[/img\]`isU","<img src='\\1' alt='Attached Image' />",$t);

		//$t							= preg_replace("`\[quote\](.*)\[/quote\]`sie","\$this->bbcode_handle_quote('\\1')",$t);
		//$t							= preg_replace("`\[quote\=([\w]*[:\/\/]*[\w\.\?\/&=\;, -@]+)\](.*)\[/quote\]`sie","\$this->bbcode_handle_quote('\\2','\\1')",$t);
		if(preg_match("`\[quote(.+?)?\](.*)\[/quote\]`si",$t))
		{
			$t						= $this->bbcode_handle_quote_do($t);
		}
		
		// left, right, center
		$t							= preg_replace("`\[left\](.*)\[/left\]`siU","<div style='text-align:left'>\\1</div>",$t);
		$t							= preg_replace("`\[center\](.*)\[/center\]`siU","<div style='text-align:center'>\\1</div>",$t);
		$t							= preg_replace("`\[right\](.*)\[/right\]`siU","<div style='text-align:right'>\\1</div>",$t);
		
		// code tags
		$t							= preg_replace_callback("`\[code(\=([a-z]+))?\]((?:[^[]|\[(?!/?code\])|(?R))+)\[/code\]`i",array(&$this,'bbcode_handle_code'),$t);
	
		$t							= preg_replace("`\[noparse\](.*)\[/noparse\]`seiU","\$this->bbcode_handle_noparse('\\1')",$t);
		
		return $t;
	}
	
	/**
	 * Undoes the parsing of BBCode
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function bbcode_undo($t)
	{
		global $icebb,$db,$config,$std;
		
		// this is to keep a few things from breaking, shouldn't break anything
		$t							= str_replace('$','&#36;',$t);
		
		// first handle defaults, as much as I'd like to make everything customizable I can't >_< 
		$t							= preg_replace("`<b>(.*)<\/b>`sUi","[b]\\1[/b]",$t);
		$t							= preg_replace("`<i>(.*)<\/i>`sUi","[i]\\1[/i]",$t);
		$t							= preg_replace("`<u>(.*)<\/u>`sUi","[u]\\1[/u]",$t);

		$t							= preg_replace("`<a href\='{$this->url_regex_main}'>(.+?)<\/a>`is", "[url=$1]$2[/url]", $t);

		$t							= preg_replace("`<img src\='{$this->url_regex_main}' alt='Attached Image' />`is","[img]$1[/img]",$t);

		// left, right, center
		$t							= preg_replace("`<div style\='text-align:left'>(.+?)<\/div>`si", "[left]\\1[/left]", $t);
		$t							= preg_replace("`<div style\='text-align:center'>(.+?)<\/div>`si", "[center]\\1[/center]", $t);
		$t							= preg_replace("`<div style\='text-align:right'>(.+?)<\/div>`si", "[right]\\1[/right]", $t);


		return $t;
	}
	
	function bbcode_handle_size($size,$t)
	{
		$size						= intval($size)+7;
		
		if($size					> 30)
		{
			$size					= 30;
		}
		
		$t							= "<span style='font-size:{$size}pt'>{$t}</span>";
		
		return $t;
	}
	
	function bbcode_handle_mail($q)
	{
		$encoded		= '';
		
		for($i=0;$i<strlen($q);$i++)
		{
			$encoded   .= "&#".ord($q{$i}).";";
		}
	
		$q				= "<a href='mailto:{$encoded}'>{$encoded}</a>";
		
		return $q;
	}
	
	function bbcode_handle_noparse($t)
	{
		$t				= $this->smilies_undo($t);
		$t				= $this->bbcode_undo($t);
	
		return $t;
	}
	
	function bbcode_handle_php($q)
	{
		$q		= $this->smilies_undo($q);
		$q		= str_replace('?',htmlspecialchars('?'),$q);
		$q		= str_replace('$','&#36;',$q);
		$q		= stripslashes(html_entity_decode(html_entity_decode(html_entity_decode($q))));
		$q		= highlight_string($q,1);
		// /me wubs php.net
		$q		= preg_replace('#<font color="([^\']*)">([^\']*)</font>#', '<span style="color: \\1">\\2</span>', $q);
		$q		= preg_replace('#<font color="([^\']*)">([^\']*)</font>#U', '<span style="color: \\1">\\2</span>', $q);
		$q		= str_replace('<br />','',$q);
		//$q		= str_replace('&(amp;?)(amp;?)#36;','&#36;',$q);
	
		$t		= "<div class='code_tag'><div class='ctop'>PHP:</div><div class='code'>";
		$t	   .= $q;
		$t	   .= "</div></div>";

		return $t;
	}
	
	function bbcode_handle_code($tb)
	{
		$type					= $tb[2];
		$tb						= $tb[3];
		
		$tb						= $this->smilies_undo($tb);
		$tb						= $this->bbcode_undo($tb);
		$tb						= str_replace("<br />","",$tb);
		
		switch($type)
		{
			default:
			case 'php':
				if(version_compare(phpversion(),"5.0.0","<")) break;
				
				$tb				= str_replace('&nbsp;', ' ', $tb);
			
				// decode the string so highlight_string() will work
				$tb				= html_entity_decode($tb);
				$tb				= htmlspecialchars_decode($tb);
				
				// okay, let's highlight the string...
				$tb				= highlight_string($tb,true);
				$tb				= str_replace("<br />","",$tb);
				$tb				= str_replace("<code>","",$tb);
				$tb				= str_replace("</code>","",$tb);
				$tb				= preg_replace("`<span style=\"color: #000000\">(.*)</span>\n`is","$1",$tb);
				break;
			case 'xml':
				$tb				= explode("\n",$tb);
				foreach($tb as $on => $piece)
				{
					$piece		= preg_replace("/('(.+?)'|&quot;(.+?)&quot;|\"(.+?)\")/","<span style='color:#DD0000'>\\0</span>",$piece);
					$piece		= preg_replace('/\&#60;(.+?)\&#62;/i',"<span style='color:#007700'>\\0</span>",$piece);
		
					$tb[$on]	= $piece;
				}
				$tb				= implode($tb);
				break;
		}
		
		$t						= $this->html_global->code_tag($tb,$type);

		return $t;
	}
	
	function bbcode_handle_xml($source)
	{	
		$source			= $this->smilies_undo($source);
	
		$source			= explode("\n",$source);
		foreach($source as $on => $piece)
		{
			//$piece = htmlspecialchars($piece);
			$piece		= preg_replace("/'.*?'/", "<span style='color:#0000CC;'>\\0</span>", $piece);
			$piece		= preg_replace('/&quot;.*?&quot;/', "<span style='color:#0000CC;'>\\0</span>", $piece);
			$piece		= preg_replace('/&lt;.*?&gt;/', "<span style='color:#008800;'>\\0</span>", $piece);

			$source[$on]= $piece;
		}
		
		$output			= implode($source);
		$output			= str_replace('<br />','',$output);
		
		$output2		= $this->html_global->code_tag($output,'xml');
		
		
		return $output2;
	
	}
	
	function bbcode_handle_quote($q,$title='')
	{
		if(!empty($title))
		{
			$title				= str_replace("]",'&#093;',$title);
		}
	
		$t						= $this->bbcode_quote_top($title);
		$t					   .= $this->bbcode_handle_quote_do($q);
		$t					   .= $this->bbcode_quote_bottom();
		
		return $t;
	}
	
	function bbcode_handle_quote_do($t)
	{
		if(!$this->parse_quotes)
		{
			return $t;
		}
	
		$t							= preg_replace("`\[quote\]`sie","\$this->bbcode_handle_quote_start_old()",$t);
		$t							= preg_replace("`\[quote\=([\w]*[:\/\/]*[\w\.\?\/&=\;, -@]+)\]`sie","\$this->bbcode_handle_quote_start_old('\\1')",$t);
		$t							= preg_replace("`\[quote pid\=([0-9]*) author\=([\w]*[:\/\/]*[\w\.\?\/&=\;, -@]+) date\=([0-9]+)\]`sie","\$this->bbcode_handle_quote_start('\\1','\\2','\\3')",$t);
		$t							= preg_replace("`\[/quote\]`sie","\$this->bbcode_handle_quote_end()",$t);
		
		if(preg_match("`\[quote(.?)\](.*)\[/quote\]`si",$t))
		{
			//$t						= $this->bbcode_handle_quote_do($t);
		}
			
		return $t;
	}
	
	function bbcode_handle_quote_start($pid='',$title='',$date='')
	{
		global $icebb,$std;
	
		$this->quotes_open++;

		$date				= gmdate($icebb->user['date_format'],$date+$std->get_offset());

		$t					= $this->bbcode_quote_top($pid,$title,$date);
	
		return $t;
	}
	
	function bbcode_handle_quote_start_old($title='')
	{
		global $icebb,$std;
	
		$this->quotes_open++;

		$title				= preg_replace("`,time=([0-9]+)`e","\", \".gmdate(\$icebb->user['date_format'],\\1+\$std->get_offset())",$title);

		$t					= $this->bbcode_quote_top('',$title,'');
	
		return $t;
	}
	
	function bbcode_quote_top($pid='',$title='',$date='')
	{
		global $icebb;
	
		if(!empty($pid))
		{
			$link			= " <a href='{$icebb->base_url}act=search&amp;findpost={$pid}'>(view in context)</a>";
		}
	
		if(!empty($date))
		{
			$date			= ", {$date}";
		}
	
		$t					= $this->html_global->quote_tag_top($title,$date,$link);
	
		return $t;
	}
	
	function bbcode_handle_quote_end()
	{
		if($this->quotes_open>=1)
		{
			$this->quotes_closed++;
			$t				= $this->html_global->quote_tag_bottom();
		}
	
		return $t;
	}
	
	function bbcode_quote_bottom()
	{
		$t					= $this->html_global->quote_tag_bottom();
	
		return $t;
	}

	/**
	 * Parses smilies
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function smilies($t)
	{
		global $icebb,$db,$config,$std;
		
		if(is_array($this->smilies_list))
		{
			foreach($this->smilies_list as $s)
			{
				$s['code']		= $this->xss_is_bad($s['code']);
				$smiley_code	= preg_quote($s['code'],"`");
               
				$t				= preg_replace("`(?<=^|[\n ]|\.){$smiley_code}`","<img src='{$icebb->settings['board_url']}smilies/{$s['smiley_set']}/{$s['image']}' border='0' alt='{$s['code']}' />",$t);
			}
		}
		
		return $t;
	}

	/**
	 * Undos the parsing of smilies
	 *
	 * @param		string		Parsed smilies string
	 * @return		string		Unparsed smilies string
	 */
	function smilies_undo($t)
	{
		global $icebb,$db,$config,$std;
		
		if(is_array($this->smilies_list))
		{
			foreach($this->smilies_list as $s)
			{
				$s['code']		= $this->xss_is_bad($s['code']);
				$smiley_code	= preg_quote($s['code'],"`");
				$find			= preg_quote("<img src='{$icebb->settings['board_url']}smilies/{$s['smiley_set']}/{$s['image']}' border='0' alt='{$s['code']}' />",'`');
               
				$t				= preg_replace("`{$find}`i",$s['code'],$t);
			}
		}
		
		return $t;
	}

	/**
	 * Parses bad words
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function bad_words($t)
	{
		global $icebb,$db,$std;
		
		if(is_array($this->word_filters))
		{
			foreach($this->word_filters as $bw)
			{
				$word			= preg_quote($bw['bw_word'],'`');
				//echo $word;
				//$word			= str_replace('.','\.',$word);
				//$word			= str_replace('\*','(.?)',$word);
				//echo substr($word,strlen($word)-2,2);
				if(substr($word,0,2)!='\*')
				{
					$word		= "(^|\b)".$word;
				}
				if(substr($word,strlen($word)-2,2)!='\*')
				{
					$word	   .= "(\b|\.|!|\?|,|$)";
				}
				$word		= str_replace('\*','',$word);
				//echo $word;
				$t				= preg_replace("`{$word}`i",$bw['bw_replacement'],$t);
			}
		}
		
		return $t;
	}
	

	/**
	 * Parses a string with the settings you specify
	 *
	 * @param		array		$t		Array including settings and string to parse
	 * @param		array		$pdata	Info about a post
	 * @return		string		Parsed string
	 */
	function parse($t,$pdata=array())
	{
		global $icebb,$std,$db;
	
		if(!is_array($t))
		{
			// hmm, why did I do these uppercase? lol
			$opt			= array('TEXT'=>$t,'SMILIES'=>1,'BBCODE'=>1,'BAD_WORDS'=>1,'ME_TAG'=>1,'YOU_TAG'=>1,'PARSE_ATTACHMENTS'=>1,'PARSE_QUOTES'=>1);
		}
		else {
			$opt			= $t;
			$t				= $opt['TEXT'];
		}
		
		//$t					= html_entity_decode($t,ENT_QUOTES);
		$t					= $this->xss_is_bad($t);
		
		// fix up a few things
		$t					= preg_replace("`\(c\)`",'&copy;',$t);
		$t					= preg_replace("`\(tm\)`",'&#153;',$t);
		$t					= preg_replace("`\(r\)`",'&reg;',$t);
		$t					= preg_replace("`\t`",'    ',$t);
		
		if($icebb->user['view_smileys'] == 0)
		{
			$opt['SMILIES'] = 0;
		}
		
		if(!isset($opt['PARSE_QUOTES']))
		{
			$opt['PARSE_QUOTES']= 1;
		}
		
		// quotes disabled?
		$this->parse_quotes	= $opt['PARSE_QUOTES'];
	
		// do teh smilies
		if($opt['SMILIES'] == 1)
		{
			$t				= $this->smilies($t);
		}
		
		// word wrap
		//$t					= _wordwrap($t,100," ");
		$t					= nl2br($t);
		$t					= preg_replace("`&amp;#`","&#",$t);
		
		// do teh bbcode
		if($opt['BBCODE']  == 1)
		{
			$t				= $this->parse_links($t);
			$t				= $this->bbcode($t);
		}
		
		// do teh bad words
		if($opt['BAD_WORDS']  == 1)
		{
			$t				= $this->bad_words($t);
		}
		
		// me tag - something unique :o
		if($opt['ME_TAG']  == 1)
		{
			$t				= preg_replace("`/me (.*)`","<span class='medoes'>* {$pdata['pauthor']} \\1</span>",$t);
			// You tag ^_^
			$t				= preg_replace("`/you (.*)`","<span class='medoes'>* {$icebb->user['username']} \\1</span>",$t);
		}
		
		// you tag - this will scare some people lol - *[you]* is a faggot
		if($opt['YOU_TAG']  == 1)
		{
			$t				= preg_replace("`\*\[you\]\*`",$icebb->user['username'],$t);
			// Theese two are gonna be funny:
			$t				= preg_replace("`\*\[you_ip\]\*`",$icebb->client_ip,$t);
			$t				= preg_replace("`\*\[you_host\]\*`",$_SERVER['REMOTE_HOST'],$t);
		}
		// option tag
		if($opt['PARSE_ATTACHMENTS']== 1)
		{
			$t				= preg_replace("`\[attachment=([0-9]+)\]`ie","\$this->parse_attachment(\$this->uploads[\\1])",$t);
		}
		
		return $t;
	}
	
	/**
	 * Parses attachments
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function parse_attachment($u)
	{
		global $icebb,$std;
		
		$ext				= explode('.',$u['uname']);
		
		$u['upath_real']	= str_replace($icebb->settings['board_url'],'',$u['upath']);
		$u['upath']			= "{$icebb->base_url}act=attach&amp;upload={$u['uid']}";
		
		if(class_exists('skin_func'))
		{
			$html			= $icebb->skin->load_template('topic');
			
			if(strtolower($ext[1])=='jpg' || strtolower($ext[1])=='jpeg' || 
			   strtolower($ext[1])=='gif' || strtolower($ext[1])=='png')
			{
				list($w,$h)	= getimagesize($u['upath-real']);
				$t			= $html->attachment_view_image($u,$w,$h);
			}
			else {
				$t			= $html->attachment_view($u);
			}
		}
		
		return $t;
	}

	/**
	 * Parses links
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function parse_links($t)
	{
		$t					= preg_replace("`\[url\]{$this->url_regex}\[/url\]`","[url]$1://$2[/url]",$t);
		$t					= preg_replace("`\[url\]www\.{$this->url_regex_main}\[/url\]`","[url]http://www.$1[/url]",$t);
		$t					= preg_replace("`(?<=^|[\n ]){$this->url_regex}`","[url]$1://$2[/url]",$t);
		$t					= preg_replace("`(?<=^|[\n ])www\.{$this->url_regex_main}`","[url=http://www.$1]www.$1[/url]",$t);
	
		return $t;
	}
	
	/**
	 * Gets rid of all known XSS vulnerabilities. Created with a lot of help from
	 * http://blog.bitflux.ch/wiki/XSS_Prevention
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	*/
	function xss_is_bad($t)
	{
		//echo "&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;";
		
		//$t				= html_entity_decode($t,ENT_QUOTES,'UTF-8');
		$t				= htmlspecialchars_decode($t,ENT_QUOTES);
	
		$t				= str_replace("<","&#60;",$t);
		$t				= str_replace(">","&#62;",$t);
	
		//$t				= str_replace("&quot;","&quot;",$t);
		$t				= preg_replace("/&#0*([0-9]*);?/",'&#\\1;',$t);
		$t				= str_replace('&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;','javascript:',$t);
	
		//$t				= html_entity_decode($t,ENT_QUOTES);
		//echo $t;
	
		$t				= preg_replace("/javascript:/i"		, "nojava"/*&#97;v&#97;*/."script:"	,$t);
		$t				= preg_replace("/vbscript:/i"		, "novb"/*&#98;*/."script:"	,$t);
		//$t				= preg_replace('/&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;/i','j&#97;v&#97;script:',$t);
		//$t				= preg_replace('#(<[^>]+[\s\r\n\"\'])(on|xmlns)[^>]*\]#iU',"$1]",$t);
	
		//$t				= htmlspecialchars($t,ENT_QUOTES);
		
		//$t					= htmlentities($t,ENT_QUOTES);
		
		//$t					= preg_replace("`&#38;#([0-9]+);`s",'&#\\1;',$t);
	
		return $t;
	}
	

	/**
	 * Removes "faked" characters
	 *
	 * @param		string		String to parse
	 * @return		string		Parsed string
	 */
	function remove_fakechars($t)
	{
		$replace	= array(
			'&#1072;'=>'a',
			'&#1077;'=>'e',
			'&#1086;'=>'o',
			'&#1088;'=>'p',
			'&#1089;'=>'c',
			'&#1091;'=>'y',
			'&#1093;'=>'x',
		);
		
		//print_r($arran);
		
		$t			= str_replace(array_keys($replace),$replace,$t);
		
		return $t;
	}
	
	/**
	 * Changes HTML (from the WYSIWYG editor) to BBCode
	 *
	 * @param		string		String to parse
	 * @param		string		Parsed string
	 */
	function html_to_bbcode($t)
	{
		global $icebb,$db,$std;
		
		// take care of smilies first
		if(is_array($this->smilies_list))
		{
			foreach($this->smilies_list as $s)
			{
				$s['code']		= $this->xss_is_bad($s['code']);
				$smiley_code	= preg_quote($s['code'],"`");
				
				$t				= preg_replace("`(&#60;|&lt;)img src=(&#34;|&quot;)({$icebb->settings['board_url']})?smilies/{$s['smiley_set']}/{$s['image']}(&#34;|&quot;)(\s/)?(&#62;|&gt;)`i","{$s['code']}",$t);
			}
		}
		
		// then newlines
		$t			= preg_replace("`(&#60;|&lt;)br(\s/)?(&#62;|&gt;)`is","\n",$t);
		$t			= preg_replace("`(&#60;|&lt;)p(&#62;|&gt;)(.+?)(&#60;|&lt;)/p(&#62;|&gt;)`is","\\3\n\n",$t);
		
		// then some BBCode
		$t			= preg_replace("`(&#60;|&lt;)b(&#62;|&gt;)(.+?)(&#60;|&lt;)/b(&#62;|&gt;)`is","[b]\\3[/b]",$t);
		$t			= preg_replace("`(&#60;|&lt;)u(&#62;|&gt;)(.+?)(&#60;|&lt;)/u(&#62;|&gt;)`is","[u]\\3[/u]",$t);
		$t			= preg_replace("`(&#60;|&lt;)i(&#62;|&gt;)(.+?)(&#60;|&lt;)/i(&#62;|&gt;)`is","[i]\\3[/i]",$t);
		
		$t			= preg_replace("`(&#60;|&lt;)img src=(&#34;|&quot;)(.+?)(&#34;|&quot;)(\s/)?(&#62;|&gt;)`i","[img]\\3[/img]",$t);
		$t			= preg_replace("`(&#60;|&lt;)a href=(&#34;|&quot;)(.+?)(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/a(&#62;|&gt;)`is","[url=\\3]\\6[/url]",$t);
		
		$t			= preg_replace("`(&#60;|&lt;)p align=(&#34;|&quot;)left(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/p(&#62;|&gt;)`is","[left]\\5[/left]",$t);
		$t			= preg_replace("`(&#60;|&lt;)p align=(&#34;|&quot;)center(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/p(&#62;|&gt;)`is","[center]\\5[/center]",$t);
		$t			= preg_replace("`(&#60;|&lt;)p align=(&#34;|&quot;)right(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/p(&#62;|&gt;)`is","[right]\\5[/right]",$t);
		
		$t			= preg_replace("`(&#60;|&lt;)div align=(&#34;|&quot;)left(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/div(&#62;|&gt;)`is","[left]\\5[/left]",$t);
		$t			= preg_replace("`(&#60;|&lt;)div align=(&#34;|&quot;)center(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/div(&#62;|&gt;)`is","[center]\\5[/center]",$t);
		$t			= preg_replace("`(&#60;|&lt;)div align=(&#34;|&quot;)right(&#34;|&quot;)(&#62;|&gt;)(.+?)(&#60;|&lt;)/div(&#62;|&gt;)`is","[right]\\5[/right]",$t);
		
		
		// font - I WANT TO FUCKING KILL THE WYSIWYG EDITOR >_<
		$t			= $this->_recurse_html_regex('font',"`(&#60;|&lt;)font(.+?)(&#62;|&gt;)(.+?)(&#60;|&lt;)/font(&#62;|&gt;)`ise","\$this->_handle_font_html('$2','$4')",$t);

		// clean up extras
		$t			= str_replace("&amp;nbsp;",' ',$t);

		return $t;
	}
	
	function _recurse_html_regex($tag,$regex,$replace,$r,$recursion=0)
	{
		//if($recursion>15) return $r;
	
		$r			= preg_replace($regex,$replace,$r);
	
		if(preg_match("`(&#60;|&lt;){$tag}`i",$r))
		{
			//echo "<br />STILL MORE ({$recursion})<br />";
			$r		= $this->_recurse_html_regex($tag,$regex,$replace,$r,$recursion+1);
		}
		
		return $r;
	}
	
	function _handle_font_html($attributes,$r)
	{
		global $db;
	
		$attributes		= trim($attributes);
		$attributes		= html_entity_decode($attributes,ENT_QUOTES);
		$attributes		= $this->_attribute_split($attributes);
		//$attr			= trim($attributes);
		
		foreach($attributes as $attr)
		{
			$at		= explode('=',$attr);
			$at[1]	= preg_replace("`(&#34;|&quot;)`i",'',$at[1]);
			$at[1]	= $db->escape_string($at[1]);
			
			switch($at[0])
			{
				case 'color':
					$r= "[color={$at[1]}]{$r}[/color]";
					break;
				case 'face':
					$r= "[font={$at[1]}]{$r}[/font]";
					break;
				case 'size':
					$at[1]= intval($at[1])*7;
					$r= "[size={$at[1]}]{$r}[/size]";
					break;
			}
		}
		
		return $r;
	}
	
	function _attribute_split($raw)
	{
		$in_quotes			= 0;
		$counter			= 0;
		for($i=0;$i<=strlen($raw);$i++)
		{
			$chr			= $raw{$i};
			if($chr			== '"')
			{
				$in_quotes= $in_quotes ? 0 : 1;
				$chr		= '';
			}
			
			if(!$in_quotes && $chr==' ')
			{
				$chr		= '';
				$counter++;
			}
			
			$attrs[$counter].= $chr;
		}
		
		return $attrs;
	}
}
?>
