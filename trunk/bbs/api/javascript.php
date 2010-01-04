<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: javascript.php 10804 2007-10-11 01:45:13Z monkey $
*/

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(0);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', '../');

if(PHP_VERSION < '4.1.0') {
	$_GET		=	&$HTTP_GET_VARS;
	$_SERVER	=	&$HTTP_SERVER_VARS;
}

require_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_jswizard.php';

if($_DCACHE['settings']['gzipcompress']) {
	ob_start('ob_gzhandler');
}

$jsstatus	=	isset($_DCACHE['settings']['jsstatus']) ? $_DCACHE['settings']['jsstatus'] : 1;

if(!$jsstatus) {
	exit("document.write(\"<font color=red>The webmaster did not enable this feature.</font>\");");
}

$jsrefdomains	=	isset($_DCACHE['settings']['jsrefdomains']) ? $_DCACHE['settings']['jsrefdomains'] : preg_replace("/([^\:]+).*/", "\\1", (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL));
$REFERER	= 	parse_url($_SERVER['HTTP_REFERER']);
if($jsrefdomains && (empty($REFERER) | !in_array($REFERER['host'], explode("\r\n", trim($jsrefdomains))))) {
	exit("document.write(\"<font color=red>Referer restriction is taking effect.</font>\");");
}

if(!empty($_GET['key']) && !empty($_DCACHE['jswizard'][$_GET['key']]['url'])) {
	$cachefile	=	DISCUZ_ROOT.'./forumdata/cache/javascript_'.$_GET['key'].'.php';
	parse_str($_DCACHE['jswizard'][$_GET['key']]['url'], $_GET);
} else {
	$authkey	=	isset($_DCACHE['settings']['authkey']) ? $_DCACHE['settings']['authkey'] : '';
	$jsurl		=	preg_replace("/^(.+?)\&verify\=[0-9a-f]{32}$/", "\\1", $_SERVER['QUERY_STRING']);
	$cachefile	=	DISCUZ_ROOT.'./forumdata/cache/javascript_'.md5($jsurl).'.php';
	$verify		=	isset($_GET['verify']) ? $_GET['verify'] : NULL;
	if(!$verify || !$jsurl || $verify != md5($authkey.$jsurl)) {
		exit("document.write(\"<font color=red>Authentication failed.</font>\");");
	}
}

$expiration	=	0;
$timestamp	=	time();

if((@!include($cachefile)) || $expiration < $timestamp) {

	require_once DISCUZ_ROOT.'./config.inc.php';
	require_once DISCUZ_ROOT.'./include/db_'.$database.'.class.php';
	require_once DISCUZ_ROOT.'./include/global.func.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	$function = isset($_GET['function']) ? $_GET['function'] : NULL;
	$dateformat	=	!empty($_DCACHE['settings']['jsdateformat']) ? $_DCACHE['settings']['jsdateformat'] : (!empty($_DCACHE['settings']['dateformat']) ? $_DCACHE['settings']['dateformat'] : 'm/d');
	$timeformat	=	isset($_DCACHE['settings']['timeformat']) ? $_DCACHE['settings']['timeformat'] : 'H:i';
	$PHP_SELF	=	$_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$boardurl	=	'http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';
	$jstemplate	=	!empty($_GET['jstemplate']) ? (get_magic_quotes_gpc() ? stripslashes($_GET['jstemplate']) : $_GET['jstemplate']) : '';
	$jstemplate	=	preg_replace("/\r\n|\n|\r/", '\n', $jstemplate);
	$nocache	= 	!empty($_GET['nocache']) ? 1 : 0;
	$jscachelife	=	(isset($_GET['cachelife']) && $_GET['cachelife'] != '') ? $_GET['cachelife'] : (isset($_DCACHE['settings']['jscachelife']) ? $_DCACHE['settings']['jscachelife'] : 1800);
	$jscharset	=	$_GET['jscharset'];

	if($function != 'custom') {

		$jstemplatebody = '';
		if(preg_match("/\[node\](.+?)\[\/node\]/is", $jstemplate, $node)) {
			$jstemplatebody = $jstemplate;
			$jstemplate = $node[1];
		}

		$datalist = $writedata = procdata('document.write("'.jsmodule($function).'");');
		if(!$nocache) {
			$writedata = "\$datalist = '".addcslashes($writedata, '\\\'')."';";
			UpdateCache($cachefile, $writedata);
		}

	} else {

		$customcachefile = $cachefile;
		$customnocache = $nocache;
		$jscachelife = (isset($_GET['cachelife']) && $_GET['cachelife'] != '') ? $_GET['cachelife'] : (isset($_DCACHE['settings']['jscachelife']) ? $_DCACHE['settings']['jscachelife'] : 1800);
		$writedata = preg_match_all("/\[module\](.+?)\[\/module\]/is", $jstemplate, $modulelist);
		$modulelist = array_unique($modulelist[1]);
		$writedata = $jstemplate;

		$nocache = TRUE;
		foreach($modulelist as $key) {
			if(!empty($_DCACHE['jswizard'][$key]['url'])) {
				parse_str($_DCACHE['jswizard'][$key]['url'], $_GET);
				$function = isset($_GET['function']) ? $_GET['function'] : NULL;
				$find = "/\[module\]".preg_quote($key)."\[\/module\]/is";
				$jstemplate = !empty($_GET['jstemplate']) ? (get_magic_quotes_gpc() ? stripslashes($_GET['jstemplate']) : $_GET['jstemplate']) : '';
				$jstemplate = preg_replace("/\r\n|\n|\r/", '\n', $jstemplate);
				$jstemplatebody = '';
				if(preg_match("/\[node\](.+?)\[\/node\]/is", $jstemplate, $node)) {
					$jstemplatebody = $jstemplate;
					$jstemplate = $node[1];
				}
				$writedata = preg_replace($find, jsmodule($function), $writedata);
			}
		}
		$nocache = $customnocache;

		$datalist = $writedata = procdata('document.write("'.$writedata.'");');
		if(!$nocache) {
			$writedata = "\$datalist = '".addcslashes($writedata, '\\\'')."';";
			UpdateCache($customcachefile, $writedata);
		}

	}

}

echo $datalist;

function jsmodule($function) {
	extract($GLOBALS, EXTR_SKIP);

	$fids		=	isset($_GET['fids']) ? $_GET['fids'] : NULL;
	$startrow	=	isset($_GET['startrow']) ? intval($_GET['startrow']) : 0;
	$items		=	isset($_GET['items']) ? intval($_GET['items']) : 10;
	$digest		=	isset($_GET['digest']) ? intval($_GET['digest']) : 0;
	$stick		=	isset($_GET['stick']) ? intval($_GET['stick']) : 0;
	$newwindow	=	isset($_GET['newwindow']) ? $_GET['newwindow'] : 1;
	$LinkTarget	=	$newwindow == 1 ? " target='_blank'" : ($newwindow == 2 ? " target='main'" : NULL);

	if($function == 'threads') {
		$orderby	=	isset($_GET['orderby']) ? (in_array($_GET['orderby'],array('lastpost','dateline','replies','views')) ? $_GET['orderby'] : 'lastpost') : 'lastpost';
		$highlight	=	isset($_GET['highlight']) ? $_GET['highlight'] : 0;
		$picpre		=	isset($_GET['picpre']) ? urldecode($_GET['picpre']) : NULL;
		$maxlength	=	!empty($_GET['maxlength']) ? intval($_GET['maxlength']) : 50;
		$fnamelength	=	isset($_GET['fnamelength']) ? intval($_GET['fnamelength']) : 0;
		$blog		=	!empty($_GET['blog']) ? 1 : 0;
		$tids		=	isset($_GET['tids']) ? $_GET['tids'] : NULL;
		$keyword	=	!empty($_GET['keyword']) ? $_GET['keyword'] : NULL;
		$typeids	=	isset($_GET['typeids']) ? $_GET['typeids'] : NULL;
		$special	=	isset($_GET['special']) ? intval($_GET['special']) : 0;
		$rewardstatus	=	isset($_GET['rewardstatus']) ? intval($_GET['rewardstatus']) : 0;
		$threadtype	=	isset($_GET['threadtype']) ? intval($_GET['threadtype']) : 0;
		$tag		=	!empty($_GET['tag']) ? trim($_GET['tag']) : NULL;

		require DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

		$datalist = array();
		$threadtypeids = array();
		if($keyword) {
			if(preg_match("(AND|\+|&|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
				$andor 		= ' AND ';
				$keywordsrch 	= '1';
				$keyword 	= preg_replace("/( AND |&| )/is", "+", $keyword);
			} else {
				$andor 		= ' OR ';
				$keywordsrch 	= '0';
				$keyword 	= preg_replace("/( OR |\|)/is", "+", $keyword);
			}
			$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
			foreach(explode('+', $keyword) as $text) {
				$text = trim($text);
				if($text) {
					$keywordsrch .= $andor;
					$keywordsrch .= "t.subject LIKE '%$text%'";
				}
			}
			$keyword = " AND ($keywordsrch)";
		} else {
			$keyword = '';
		}
		$sql	=	($fids ? ' AND t.fid IN (\''.str_replace('_', '\',\'', $fids).'\')' : '')
					.$keyword
					.($blog ? ' AND t.blog = 1' : '')
					.($tids ? ' AND t.tid IN (\''.str_replace('_', '\',\'', $tids).'\')' : '')
					.($typeids ? ' AND t.typeid IN (\''.str_replace('_', '\',\'', $typeids).'\')' : '')
					.(($special >= 0 && $special < 127) ? threadrange($special, 'special', 7) : '')
					.((($special & 8) && $rewardstatus) ? ($rewardstatus == 1 ? ' AND t.price < 0' : ' AND t.price > 0') : '')
					.(($digest > 0 && $digest < 15) ? threadrange($digest, 'digest') : '')
					.(($stick > 0 && $stick < 15) ? threadrange($stick, 'displayorder') : '');
		$sqlfrom = strexists($jstemplate, '{message}') ? ",p.message FROM `{$tablepre}threads` t LEFT JOIN `{$tablepre}posts` p ON p.tid=t.tid AND p.first='1'" : "FROM `{$tablepre}threads` t";
		if($tag) {
			$tags = explode(' ', $tag);
			foreach($tags as $tagk => $tagv) {
				if(!preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $tagv)) {
					unset($tags[$tagk]);
				}
			}
			if($tags = implode("','", $tags)) {
				$sqlfrom .= " INNER JOIN `{$tablepre}threadtags` tag ON tag.tid=t.tid AND tag.tagname IN ('$tags')";
			}
		}
		$query	=	$db->query("SELECT t.tid,t.fid,t.readperm,t.author,t.authorid,t.subject,t.dateline,t.lastpost,t.lastposter,t.views,t.replies,t.highlight,t.digest,t.typeid
					$sqlfrom WHERE t.readperm='0'
					$sql
					AND t.displayorder>='0'
					AND t.fid>'0'
					ORDER BY t.$orderby DESC
					LIMIT $startrow,$items;"
					);
		while($data = $db->fetch_array($query))	{
			$datalist[$data['tid']]['fid']			=	$data['fid'];
			$datalist[$data['tid']]['fname']		=	isset($_DCACHE['forums'][$data['fid']]['name']) ? str_replace('\'', '&nbsp;',addslashes($_DCACHE['forums'][$data['fid']]['name'])) : NULL;
			$datalist[$data['tid']]['fnamelength']		=	strlen($datalist[$data['tid']]['fname']);
			$datalist[$data['tid']]['subject']		=	isset($data['subject']) ? str_replace('\'', '&nbsp;',addslashes($data['subject'])) : NULL;
			$datalist[$data['tid']]['dateline']		=	gmdate("$dateformat $timeformat",$data['dateline'] + $_DCACHE['settings']['timeoffset'] * 3600);
			$datalist[$data['tid']]['lastpost']		=	gmdate("$dateformat $timeformat",$data['lastpost'] + $_DCACHE['settings']['timeoffset'] * 3600);
			$datalist[$data['tid']]['lastposter']		=	$data['lastposter'];
			$datalist[$data['tid']]['views']		=	$data['views'];
			$datalist[$data['tid']]['replies']		=	$data['replies'];
			$datalist[$data['tid']]['highlight']		=	$data['highlight'];
			$datalist[$data['tid']]['message']		=	str_replace(array('\'',"\n","\r"), array('&nbsp;','',''), addslashes(cutstr(dhtmlspecialchars(preg_replace("/(\[.+\])/s", '', strip_tags(nl2br($data['message'])))), 255)));
			if($data['author']) {
				$datalist[$data['tid']]['author'] = "<a href='".$boardurl."space.php?uid=$data[authorid]'$LinkTarget>$data[author]</a>";
			} else {
				$datalist[$data['tid']]['author'] = 'Anonymous';
			}
			if($data['lastposter']) {
				$datalist[$data['tid']]['lastposter'] = "<a href='".$boardurl."space.php?username=".rawurlencode($data['lastposter'])."'$LinkTarget>$data[lastposter]</a>";
			} else {
				$datalist[$data['tid']]['lastposter'] = 'Anonymous';
			}
			$datalist[$data['tid']]['typeid']		=	$data['typeid'];
			$threadtypeids[]				=	$data['typeid'];
		}
		if($threadtype && $threadtypeids) {
			$typelist = array();
			$query = $db->query("SELECT typeid, name FROM {$tablepre}threadtypes WHERE typeid IN ('".implode('\',\'', $threadtypeids)."')");
			while($typearray = $db->fetch_array($query)) {
				$typelist[$typearray['typeid']] = $typearray['name'];
			}
			foreach($datalist AS $tid=>$value) {
				if($value['typeid'] && isset($typelist[$value['typeid']])) {
					$datalist[$tid]['subject'] = '['.$typelist[$value['typeid']].']'.$value['subject'];
				}
			}
		}
		$writedata = '';
		if(is_array($datalist)) {
			$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
			$prefix	= $picpre ? "<img src='$picpre' border='0' align='absmiddle'>" : NULL;
			$jstemplate = !$jstemplate ? '{prefix} {subject}<br />' : $jstemplate;
			foreach($datalist AS $tid=>$value) {
				$SubjectStyles	=	'';
				if($highlight && $value['highlight']) {
					$string			= sprintf('%02d', $value['highlight']);
					$stylestr		= sprintf('%03b', $string[0]);
					$SubjectStyles	.= " style='";
					$SubjectStyles	.= $stylestr[0] ? 'font-weight: bold;' : NULL;
					$SubjectStyles	.= $stylestr[1] ? 'font-style: italic;' : NULL;
					$SubjectStyles	.= $stylestr[2] ? 'text-decoration: underline;' : NULL;
					$SubjectStyles	.= $string[1] ? 'color: '.$colorarray[$string[1]] : NULL;
					$SubjectStyles	.= "'";
				}
				$replace['{link}']		= $boardurl.($blog ? 'blog' : 'viewthread').".php?tid=$tid";
				$replace['{subject_nolink}']	= cutstr($value['subject'],($fnamelength ? ($maxlength - $value['fnamelength']) : $maxlength));
				$replace['{subject_full}']	= $value['subject'];
				$replace['{prefix}'] 		= $prefix;
				$replace['{forum}'] 		= "<a href='".$boardurl."forumdisplay.php?fid=$value[fid]'$LinkTarget>$value[fname]</a>&nbsp;";
				$replace['{dateline}'] 		= $value['dateline'];
				$replace['{subject}'] 		= "<a href='".$boardurl.($blog ? 'blog' : 'viewthread').".php?tid=$tid' title='$value[subject]'$SubjectStyles$LinkTarget>".$replace['{subject_nolink}']."</a>";
				$replace['{message}'] 		= $value['message'];
				$replace['{author}'] 		= $value['author'];
				$replace['{lastposter}'] 	= $value['lastposter'];
				$replace['{lastpost}'] 		= $value['lastpost'];
				$replace['{views}'] 		= $value['views'];
				$replace['{replies}'] 		= $value['replies'];
				$writedata .= str_replace(array_keys($replace), $replace, $jstemplate);
			}
		}
	} elseif($function == 'forums') {
		$fups		=	isset($_GET['fups']) ? $_GET['fups'] : NULL;
		$orderby	=	isset($_GET['orderby']) ? (in_array($_GET['orderby'],array('displayorder','threads','posts')) ? $_GET['orderby'] : 'displayorder') : 'displayorder';
		$datalist = array();
		$query	=	$db->query("SELECT `fid`,`fup`,`name`,`status`,`threads`,`posts`,`todayposts`,`displayorder`,`type`
					FROM `{$tablepre}forums`
					WHERE `type`!='group'
					".($fups ? "AND `fup` IN ('".str_replace('_', '\',\'', $fups)."') " : "")."
					AND `status`='1'
					ORDER BY ".($orderby == 'displayorder' ? " `displayorder` ASC " : " `$orderby` DESC")."
					LIMIT $startrow,".($items > 0 ? $items : 65535).";"
					);
		while($data = $db->fetch_array($query)) {
			$datalist[$data['fid']]['name'] 		= str_replace('\'', '&nbsp;',addslashes($data['name']));
			$datalist[$data['fid']]['threads']		= $data['threads'];
			$datalist[$data['fid']]['posts']		= $data['posts'];
			$datalist[$data['fid']]['todayposts']		= $data['todayposts'];
		}
		$writedata = '';
		if(is_array($datalist)) {
			$jstemplate = !$jstemplate ? '{forumname}<br />' : $jstemplate;
			foreach($datalist AS $fid=>$value) {
				$replace['{link}']		= $boardurl."forumdisplay.php?fid=$fid";
				$replace['{forumname_nolink}']	= $value['name'];
				$replace['{forumname}'] 	= "<a href='".$boardurl."forumdisplay.php?fid=$fid'$LinkTarget>$value[name]</a>";
				$replace['{threads}'] 		= $value['threads'];
				$replace['{posts}'] 		= $value['posts'];
				$replace['{todayposts}'] 	= $value['todayposts'];
				$writedata .= str_replace(array_keys($replace), $replace, $jstemplate);
			}
		}
	} elseif($function == 'memberrank') {
		$orderby	=	isset($_GET['orderby']) ? (in_array($_GET['orderby'],array('credits','posts','digestposts','regdate','todayposts')) ? $_GET['orderby'] : 'credits') : 'credits';
		$datalist = array();
		switch($orderby) {
			case 'credits':
				$sql = "SELECT m.`username`,m.`uid`,m.`credits`,mf.`avatar`,mf.`avatarwidth` FROM `{$tablepre}members` m LEFT JOIN `{$tablepre}memberfields` mf USING(`uid`) ORDER BY m.`credits` DESC";
			break;
			case 'posts':
				$sql = "SELECT m.`username`,m.`uid`,m.`posts`,mf.`avatar`,mf.`avatarwidth` FROM `{$tablepre}members` m LEFT JOIN `{$tablepre}memberfields` mf USING(`uid`) ORDER BY m.`posts` DESC";
			break;
			case 'digestposts':
				$sql = "SELECT m.`username`,m.`uid`,m.`digestposts`,mf.`avatar`,mf.`avatarwidth` FROM `{$tablepre}members` m LEFT JOIN `{$tablepre}memberfields` mf USING(`uid`) ORDER BY m.`digestposts` DESC";
			break;
			case 'regdate':
				$sql = "SELECT m.`username`,m.`uid`,m.`regdate`,mf.`avatar`,mf.`avatarwidth` FROM `{$tablepre}members` m LEFT JOIN `{$tablepre}memberfields` mf USING(`uid`) ORDER BY m.`regdate` DESC";
			break;
			case 'todayposts':
				$sql = "SELECT DISTINCT(p.author) AS username,p.authorid AS uid,COUNT(p.pid) AS postnum,mf.`avatar`,mf.`avatarwidth` FROM `{$tablepre}posts` p LEFT JOIN `{$tablepre}memberfields` mf ON mf.`uid` = p.`authorid` WHERE p.`dateline`>=".($timestamp - 86400)." AND p.`authorid`!='0' GROUP BY p.`author` ORDER BY `postnum` DESC";
			break;
		}
		$query = $db->query($sql." LIMIT $startrow,$items;");
		while($data = $db->fetch_array($query,MYSQL_NUM)) {
			$data[2] = $orderby == 'regdate' ? gmdate($dateformat,$data[2] + $_DCACHE['settings']['timeoffset'] * 3600) : $data[2];
			$datalist[] = $data;
		}
		$writedata = '';
		if(is_array($datalist)) {
			$jstemplate = !$jstemplate ? '{regdate} {member} {value}<br />' : $jstemplate;
			foreach($datalist AS $value) {
				$replace['{regdate}'] = $replace['{value}'] = '';
				if($orderby == 'regdate') {
					$replace['{regdate}'] = $value[2];
				} else {
					$replace['{value}'] = $value[2];
				}
				$replace['{member}'] = "<a href='".$boardurl."space.php?uid=$value[1]'$LinkTarget>$value[0]</a>";
				$replace['{avatar}'] = $value[3] ? "<a href='".$boardurl."space.php?uid=$value[1]'$LinkTarget><img src='".(preg_match('/^http:\/\//i', $value[3]) ? $value[3] :
					$boardurl.'/'.$value[3])."' width='".($value[4] < $_DCACHE['settings']['maxavatarpixel'] ? $value[4] : $_DCACHE['settings']['maxavatarpixel'])."' border=0 alt='' /></a>" : '';
				$writedata .= str_replace(array_keys($replace), $replace, $jstemplate);
			}
		}
	} elseif($function == 'stats') {
		$info = isset($_GET['info']) ? $_GET['info'] : NULL;
		if(is_array($info)) {
			$language = $info;
			$info_index = '';
			$statsinfo = array();
			$statsinfo['forums'] = $statsinfo['threads'] = $statsinfo['posts'] = 0;
			$query = $db->query("SELECT `status`,`threads`,`posts`
					FROM `{$tablepre}forums` WHERE
					`status`='1';
					");
			while($forumlist = $db->fetch_array($query)) {
				$statsinfo['forums']++;
				$statsinfo['threads'] += $forumlist['threads'];
				$statsinfo['posts'] += $forumlist['posts'];
			}
			unset($info['forums'],$info['threads'],$info['posts']);
			foreach($info AS $index=>$value) {
				if($index == 'members') {
					$sql = "SELECT COUNT(*) FROM `{$tablepre}members`;";
				} elseif($index == 'online') {
					$sql = "SELECT COUNT(*) FROM `{$tablepre}sessions`;";
				} elseif($index == 'onlinemembers') {
					$sql = "SELECT COUNT(*) FROM `{$tablepre}sessions` WHERE `uid`>'0';";
				}
				if($index == 'members' || $index == 'online' || $index == 'onlinemembers') {
					$query = $db->query($sql);
					$statsinfo[$index] = $db->result($query, 0);
				}
			}
			unset($index, $value);
			$writedata = '';
			$jstemplate = !$jstemplate ? '{name} {value}<br />' : $jstemplate;
			foreach($language AS $index=>$value) {
				$replace['{name}'] = $value;
				$replace['{value}'] = $statsinfo[$index];
				$writedata .= str_replace(array_keys($replace), $replace, $jstemplate);
			}
		}
	} elseif($function == 'images') {
		$maxwidth	=	isset($_GET['maxwidth']) ? $_GET['maxwidth'] : 0;
		$maxheight	=	isset($_GET['maxheight']) ? $_GET['maxheight'] : 0;
		$blog		=	!empty($_GET['blog']) ? 1 : 0;

		require DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
		$datalist	=	array();
		$sql		=	($fids ? ' AND `fid` IN (\''.str_replace('_', '\',\'', $fids).'\')' : '')
					.(($digest > 0 && $digest < 15) ? threadrange($digest, 'digest') : '');
		$blogsql	= 	$blog ? 'AND `t`.`blog` = 1' : '';
		$query		=	$db->query("SELECT attach.*,t.tid,t.fid,t.digest,t.author,t.dateline,t.subject,t.displayorder
						FROM `{$tablepre}attachments` attach
						LEFT JOIN `{$tablepre}threads` t
						ON `t`.`tid`=`attach`.`tid` $blogsql
						WHERE `attach`.`readperm`='0'
						AND `displayorder`>='0'
						AND `isimage` = '1'
						$sql
						GROUP BY `attach`.`tid`
						ORDER BY `attach`.`dateline` DESC,`attach`.`tid` DESC
						LIMIT $startrow,$items;"
						);
		$attachurl = $_DCACHE['settings']['attachurl'];
		$attachurl = preg_match("/^((https?|ftps?):\/\/|www\.)/i", $attachurl) ? $attachurl : $boardurl.$attachurl;

		while($data = $db->fetch_array($query)) {
			$datalist[$data['tid']]['threadlink']		=	$boardurl.($blog ? 'blog' : 'viewthread').".php?tid=$data[tid]";
			$datalist[$data['tid']]['imgfile']		=	($data['remote'] ? $_DCACHE['settings']['ftp']['attachurl'] : $attachurl)."/$data[attachment]".($_DCACHE['settings']['thumbstatus'] && $data['thumb'] ? '.thumb.jpg' : '');
			$datalist[$data['tid']]['subject']		=	str_replace('\'', '&nbsp;',$data['subject']);
			$datalist[$data['tid']]['author']		=	addslashes($data['author']);
			$datalist[$data['tid']]['dateline']		=	gmdate("$dateformat $timeformat",$data['dateline'] + $_DCACHE['settings']['timeoffset'] * 3600);
			$datalist[$data['tid']]['fname']		=	isset($_DCACHE['forums'][$data['fid']]['name']) ? str_replace('\'', '&nbsp;',addslashes($_DCACHE['forums'][$data['fid']]['name'])) : NULL;
			$datalist[$data['tid']]['description']		=	$data['description'] ? str_replace('\'', '&nbsp;',addslashes($data['description'])) : NULL;
		}
		$writedata = '';
		if(is_array($datalist)) {
			$imgsize = ($maxwidth ? " width='$maxwidth'" : NULL).($maxheight ? " height='$maxheight'" : NULL);
			$jstemplate = !$jstemplate ? '{image}' : $jstemplate;
			foreach($datalist AS $value) {
				$replace['{link}'] = $value['threadlink'];
				$replace['{imgfile}'] = $value['imgfile'];
				$replace['{subject}'] = $value['subject'];
				$replace['{image}'] = "<a href='$value[threadlink]'$LinkTarget><img$imgsize src='$value[imgfile]' border='0' alt='"
					.($value['description'] ? "$value[description]&#13&#10" : NULL)
					."$value[subject]&#13&#10$value[author]($value[dateline])&#13&#10$value[fname]' /></a>";
				$writedata .= str_replace(array_keys($replace), $replace, $jstemplate);
			}
		}
	} else {
		exit("document.write(\"<font color=red>Undefined action.</font>\");");
	}
	return parsenode($writedata);
}

function parsenode($data) {
	global $jstemplatebody;
	if($jstemplatebody) {
		$data = preg_replace("/\[node\](.+?)\[\/node\]/is", $data, $jstemplatebody, 1);
		$data = preg_replace("/\[node\](.+?)\[\/node\]/is", '', $data);
	}
	return $data;
}

function procdata($data) {
	global $boardurl, $_DCACHE, $charset, $jscharset;
	if($_DCACHE['settings']['rewritestatus']) {
		$searcharray = $replacearray = array();
		if($_DCACHE['settings']['rewritestatus'] & 1) {
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."forumdisplay\.php\?fid\=(\d+)\'/";
			$replacearray[] = "<a href='{$boardurl}forum-\\1-1.html'";
		}
		if($_DCACHE['settings']['rewritestatus'] & 2) {
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."viewthread\.php\?tid\=(\d+)\'/";
			$replacearray[] = "<a href='{$boardurl}thread-\\1-1-1.html'";
		}
		if($_DCACHE['settings']['rewritestatus'] & 4) {
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."space\.php\?uid\=(\d+)\'/";
			$searcharray[] = "/\<a href\=\'".preg_quote($boardurl, '/')."space\.php\?username\=([^&]+?)\'/";
			$replacearray[] = "<a href='{$boardurl}space-uid-\\1.html'";
			$replacearray[] = "<a href='{$boardurl}space-username-\\1.html'";
		}
		$data = preg_replace($searcharray, $replacearray, $data);
	}
	if($jscharset) {
		include DISCUZ_ROOT.'include/chinese.class.php';
		if(strtoupper($charset) != 'UTF-8') {
			$c = new Chinese($charset, 'utf8');
		} else {
			$c = new Chinese('utf8', $jscharset == 1 ? 'gbk' : 'big5');
		}
		$data = $c->Convert($data);
	}
	return $data;
}

function UpdateCache($cachfile,$data='') {
	global $timestamp, $jscachelife, $_DCACHE;
	if(!$fp = @fopen($cachfile, 'wb')) {
		exit("document.write(\"Unable to write to cache file!<br />Please chmod ./forumdata/cache to 777 and try again.\");");
	}
	$fp = @fopen($cachfile, 'wb');
	$cachedata = "if(!defined('IN_DISCUZ')) exit('Access Denied');\n\$expiration = '".($timestamp + $jscachelife)."';\n".$data."\n";
	@fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!".
			"\n//Created: ".date("M j, Y, G:i").
			"\n//Identify: ".md5(basename($cachfile).$cachedata.$_DCACHE['settings']['authkey'])."\n\n$cachedata?>");
	@fclose($fp);
}

function threadrange($range, $field, $params = 4) {
	$range	=	intval($range);
	$range	=	sprintf("%0".$params."d", decbin($range));
	$range	=	"$range";
	$range_filed	=	'';
	for($i = 0; $i < $params - 1; $i ++) {
		$range_filed	.=	$range[$i] == 1 ? ($i + 1) : '';
	}
	$range_filed	.=	$range[$params - 1] == 1 ? 0 : '';
	return ' AND `'.$field.'` IN (\''.str_replace('_', '\',\'', substr(chunk_split($range_filed,1,"_"),0,-1)).'\')';
}


?>