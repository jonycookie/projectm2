<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_bbs($vars,&$iCMS){
	if($iCMS->config["bbs"]["call"]){
	    $maxperpage =isset($vars['row'])?(int)$vars['row']:"10";
		$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
		$bbsurl	=$iCMS->config["bbs"]["url"];
		$dbpre	=$iCMS->config["bbs"]["dbpre"];
		$dbname	=$iCMS->config["bbs"]["dbname"];
		$charset=$iCMS->config["bbs"]["charset"];
		if(empty($iCMS->config["bbs"]["dbuser"]) && empty($iCMS->config["bbs"]["dbpw"]) && $iCMS->config["bbs"]["dbhost"]=="localhost" ){
			$DB=$iCMS->db;
		}else{
			$DB = new iCMS_DB($iCMS->config["bbs"]["dbuser"], $iCMS->config["bbs"]["dbpw"], $dbname,$iCMS->config["bbs"]["dbhost"]);
			$DB->hide_errors();
		}
		if(strtolower($charset)!=DB_CHARSET&&!empty($charset)){
			$DB->query("SET NAMES '{$charset}'");
		}
		$by=$vars['by']=='ASC'?"ASC":"DESC";
		if($iCMS->config["bbs"]["type"]=="PHPWind"){
			empty($dbpre) && $dbpre='pw_';
			$dbname	= $dbname!=DB_NAME?$dbname.'.'.$dbpre:$dbpre;
			$threads= $dbname.'threads t';
			$forums	= $dbname.'forums f';
		    $vars['fid!'] && $whereSQL.= GetIDSQL($vars['fid!'],'t.fid','not');
		    $vars['fid']  && $whereSQL.= GetIDSQL($vars['fid'],'t.fid');
			if($vars['call']=="forum"){
			}else{
				switch ($vars['orderby']) {
					case "view":		$orderSQL=" ORDER BY t.hits $by";		break;
					case "hot":			$orderSQL=" ORDER BY t.replies $by";	break;
					case "lastpost":	$orderSQL=" ORDER BY t.lastpost $by";	break;
					case "new":			$orderSQL=" ORDER BY t.postdate $by";	break;
					case "rand":		$orderSQL=" ORDER BY rand() $by";		break;
					default:			$orderSQL=" ORDER BY t.tid $by";
				}
				$whereSQL="f.fid=t.fid ";
			    $vars['tid']  && $whereSQL.= GetIDSQL($vars['tid'],'t.tid');
			    $vars['tid!'] && $whereSQL.= GetIDSQL($vars['tid!'],'t.tid','not');
			    $offset	=0;
				if($vars['page']){
					$total=$DB->getValue("SELECT count(*) FROM {$threads},{$forums} WHERE {$whereSQL} {$orderSQL}");
					$iCMS->assign("total",$total);
					$pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
					$pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
					$offset	= $iCMS->multi(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>$iCMS->language('page:list'),'url'=>$iCMS->url,'nowindex'=>$GLOBALS['page'],'pagenav'=>$pagenav,'pnstyle'=>$pnstyle));
				}
				if($vars['cache']==false||isset($vars['page'])){
					$iCMS->config['iscache']=false;
					$rs = '';
				}else{
					$iCMS->config['iscache']=true;
					$cacheName='bbs/'.md5($iCMS->config["bbs"]["type"].$whereSQL.$orderSQL);
					$rs=$iCMS->cache($cacheName);
				}
				if(empty($rs)){
					$rs=$DB->getArray("SELECT t.*, f.name FROM {$threads},{$forums} WHERE {$whereSQL} {$orderSQL} LIMIT {$offset},{$maxperpage}");
					$_count=count($rs);
					for ($i=0;$i<$_count;$i++){
				       	$rs[$i]['forumname'] = $rs[$i]['name'];
				        $rs[$i]["url"]		=$bbsurl."/read.php?tid={$rs[$i]['tid']}";
				        $rs[$i]["forumurl"]	=$bbsurl."/thread.php?fid={$rs[$i]['fid']}";
				        if($iCMS->config["bbs"]["htmifopen"]){
				        	$db_dir=$iCMS->config["bbs"]["htmdir"];
				        	$db_ext=$iCMS->config["bbs"]["htmext"];
				        	$rs[$i]["url"]		=PHPWind_BBS_Htm_cv($rs[$i]["url"],$db_dir,$db_ext);
				        	$rs[$i]["forumurl"]	=PHPWind_BBS_Htm_cv($rs[$i]["forumurl"],$db_dir,$db_ext);
				        }
					}
					$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
				}
			}
		}elseif($iCMS->config["bbs"]["type"]=="Discuz"){
			empty($dbpre) && $dbpre='cdb_';
			$dbname	= $dbname!=DB_NAME?$dbname.'.'.$dbpre:$dbpre;
			$threads= $dbname.'threads t';
			$forums	= $dbname.'forums f';
		    $vars['fid!'] && $whereSQL.= GetIDSQL($vars['fid!'],'t.fid','not');
		    $vars['fid']  && $whereSQL.= GetIDSQL($vars['fid'],'t.fid');
			if($vars['call']=="forum"){
			}else{
				switch ($vars['orderby']) {
					case "view":		$orderSQL=" ORDER BY t.views $by";		break;
					case "hot":			$orderSQL=" ORDER BY t.replies $by";	break;
					case "lastpost":	$orderSQL=" ORDER BY t.lastpost $by";	break;
					case "new":			$orderSQL=" ORDER BY t.dateline $by";	break;
					case "rand":		$orderSQL=" ORDER BY rand() $by";		break;
					default:			$orderSQL=" ORDER BY t.tid $by";
				}
				$whereSQL="f.fid=t.fid";
			    $vars['reply'] && $whereSQL.= " AND t.closed NOT LIKE 'moved|%' AND t.replies !=0";
			    $vars['tid']  && $whereSQL.= GetIDSQL($vars['tid'],'t.tid');
			    $vars['tid!'] && $whereSQL.= GetIDSQL($vars['tid!'],'t.tid','not');
			    $offset	=0;
				if($vars['page']){
					$total=$DB->getValue("SELECT count(*) FROM {$threads},{$forums} WHERE {$whereSQL} {$orderSQL}");
					$iCMS->assign("total",$total);
					$pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
					$pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
					$offset	= $iCMS->multi(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>$iCMS->language('page:list'),'url'=>$iCMS->url,'nowindex'=>$GLOBALS['page'],'pagenav'=>$pagenav,'pnstyle'=>$pnstyle));
				}
				if($vars['cache']==false||$vars['page']){
					$iCMS->config['iscache']=false;
					$rs = '';
				}else{
					$cacheName='bbs/'.md5($iCMS->config["bbs"]["type"].$whereSQL.$orderSQL);
					$rs=$iCMS->cache($cacheName);
				}
				if(empty($rs)){
					$rs=$DB->getArray("SELECT t.*, f.name FROM {$threads},{$forums} WHERE {$whereSQL} {$orderSQL} LIMIT {$offset},{$maxperpage}");
					$_count=count($rs);
					for ($i=0;$i<$_count;$i++){
				       	$rs[$i]['forumname'] = $rs[$i]['name'];
				        if($rs[$i]['highlight']) {
				                $string = sprintf('%02d', $rs[$i]['highlight']);
				                $stylestr = sprintf('%03b', $string[0]);
				                $rs[$i]['highlight'] = 'style="';
				                $rs[$i]['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
				                $rs[$i]['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
				                $rs[$i]['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
				                $rs[$i]['highlight'] .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
				                $rs[$i]['highlight'] .= '"';
				        } else {
				                $rs[$i]['highlight'] = '';
				        }
				        if($iCMS->config["bbs"]["htmifopen"]){
				        	$rs[$i]["url"]		=$bbsurl."/thread-{$rs[$i]['tid']}-1-1.html";
				        	$rs[$i]["forumurl"]	=$bbsurl."/forum-{$rs[$i]['fid']}-1.html";
				        }else{
				        	$rs[$i]["url"]		=$bbsurl."/viewthread.php?tid={$rs[$i]['tid']}";
				        	$rs[$i]["forumurl"]	=$bbsurl."/forumdisplay.php?fid={$rs[$i]['fid']}";
				        }
					}
					$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
				}
			}
		}
	}
	return $rs;
}
function PHPWind_BBS_Htm_cv($url,$db_dir,$db_ext){
	if(ereg("^ftp|telnet|mms|rtsp|admin.php|rss.php",$url)===false){
		strpos($url,'#')!==false && $add = substr($url,strpos($url,'#'));
		$url = str_replace(array('.php?','=','&',$add),array($db_dir,'-','-',''),$url).$db_ext.$add;
	}
	return $url;
}

?>