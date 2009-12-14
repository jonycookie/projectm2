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
// cache class
// $Id: cache.inc.php 1 2006-04-25 22:10:16Z mutantmonkey $
//******************************************************//

/**
 * A class that allows for basic caching of the contents of a table
 *
 * Usage (when this class is instated):
 * $data		= $cache->get_cache($cacheid);
 * $cache->rebuild_cache($cacheid,$data);
 *
 * @package		IceBB
 * @version		1.0
 */
class cache_func
{
	/**
	 * Constructor: loads cache
	 */
	function cache_func()
	{
		global $icebb,$db,$std;
		
		if(is_array($icebb->cache))
		{
			$this->cache			= $icebb->cache;
		}
		else {
			$db->query("SELECT * FROM icebb_cache");
			while($c				= $db->fetch_row())
			{
				$this->cache[$c['name']]= unserialize($c['content']);
			}
		}
	}
	
	/**
	 * Rebuilds a cache
	 *
	 * @param		string 		the cache you want to rebuild
	 * @param		array		the data you want to be cached (optional)
	 */
	function rebuild_cache($cacheid,$data=null)
	{
		global $icebb,$db,$std;
	
		if($data == null)
		{
			switch($cacheid)
			{
				case 'bbcode':
					$bbcodeq			= $db->query("SELECT * FROM icebb_bbcode");
					while($b			= $db->fetch_row($bbcodeq))
					{
						$b['code']		= $b['code'];
						$b['replacement']= $b['replacement'];
						$b['php']		= $b['php'];
						$data[]			= $b;
					}
					break;
				case 'smiles':
					$smiliesq			= $db->query("SELECT * FROM icebb_smilies");
					while($s			= $db->fetch_row($smiliesq))
					{
						$s['code']		= $s['code'];
						$s['image']		= $s['image'];
						$data[]			= $s;
					}
					break;
				case 'forums':
					$db->query("SELECT * FROM icebb_forums");
					while($f			= $db->fetch_row())
					{
						foreach($f as $fkey => $fval)
						{
							$f[$fkey]	= wash_key(str_replace("&amp;","&",$fval));
						}
					
						$data[$f['fid']]= $f;
					}
					break;
				case 'moderators':
					$db->query("SELECT * FROM icebb_moderators");
					while($m			= $db->fetch_row())
					{
						foreach($m as $mkey => $mval)
						{
							$m[$mkey]	= wash_key(str_replace("&amp;","&",$mval));
						}
					
						$data[$m['mid']]	= $m;
					}
					break;
				case 'skins':
					$db->query("SELECT * FROM icebb_skin_macros");
					while($m			= $db->fetch_row())
					{
						$macroso[$m['skin_id']][$m['string']]= $m['replacement'];
					}
					
					foreach($macroso as $skin => $ms)
					{
						$db->query("UPDATE icebb_skins SET skin_macro_cache='".addslashes(serialize($ms))."' WHERE skin_id='{$skin}'");
					}
				
					// -- SKINS -- //
					$db->query("SELECT * FROM icebb_skins");
					while($s			= $db->fetch_row())
					{
						foreach($s as $skey => $sval)
						{
							$s[$skey]	= wash_key(str_replace("&amp;","&",$sval));
						}
					
						$data[$s['skin_id']]= $s;
					}
					break;
				case 'langs':
					$db->query("SELECT * FROM icebb_langs");
					while($l			= $db->fetch_row())
					{
						foreach($l as $lkey => $lval)
						{
							$l[$lkey]	= wash_key(str_replace("&amp;","&",$lval));
						}
					
						$data[$s['lang_short']]= $l;
						if($l['lang_is_default'])
						{
							$default	= $l['lang_short'];
						}
					}
					$data['default']	= $default;
					break;
				case 'tasks':
					$db->query("SELECT * FROM icebb_tasks");
					while($t			= $db->fetch_row())
					{
						foreach($t as $tkey => $tval)
						{
							$t[$tkey]	= wash_key(str_replace("&amp;","&",$tval));
						}
					
						$data[]			= $t;
					}
					break;
				case 'groups':
					$db->query("SELECT * FROM icebb_groups");
					while($g			= $db->fetch_row())
					{
						foreach($g as $gkey => $gval)
						{
							$g[$gkey]	= wash_key(str_replace("&amp;","&",$gval));
						}
					
						$data[]			= $g;
					}
					break;
				case 'ranks':
					$db->query("SELECT * FROM icebb_ranks");
					while($r			= $db->fetch_row())
					{
						foreach($r as $rkey => $rval)
						{
							$r[$rkey]	= wash_key(str_replace("&amp;","&",$rval));
						}
					
						$data[]			= $r;
					}
					break;
				case 'birthdays':
					$db->query("SELECT * FROM icebb_users");
					while($u			= $db->fetch_row())
					{
						if(!empty($u['birthdate']))
						{
							$bds['uid']	= $u['id'];
							$bds['username']= $u['username'];
							
							$u['birthdate']= @explode('.',@gmdate('m.d.Y',$u['birthdate']-$std->get_offset(OFFSET_SERVER)));
							$bd['bmonth']= $u['birthdate'][0];
							$bd['bday']= $u['birthdate'][1];
							$bd['byear']= $u['birthdate'][2];
							
							$bds['year']= $bd['byear'];
							
							$data[$bd['bmonth']][$bd['bday']][]= $bds;
						}
					}
					break;
				case 'plugins':
					$pluginsq			= $db->query("SELECT * FROM icebb_plugins");
					while($p			= $db->fetch_row($pluginsq))
					{
						$data[]			= $p;
					}
					break;
				case 'settings':
					$settingsqO_O		= $db->query("SELECT * FROM icebb_settings");
					while($setting		= $db->fetch_row($settingsqO_O))
					{
						$data[$setting['setting_key']]= $setting['setting_value'];
					}
					break;
				case 'stats':
					$cache_result				= $db->fetch_result("SELECT COUNT(*) as count FROM icebb_users");
					$cache_result2				= $db->fetch_result("SELECT * FROM icebb_users ORDER BY id DESC");
					$cache_result3				= $db->fetch_result("SELECT COUNT(*) as posts FROM icebb_posts");
					$cache_result31				= $db->fetch_result("SELECT COUNT(*) as topics FROM icebb_topics");
					$cache_result32				= $db->fetch_result("SELECT COUNT(*) as replies FROM icebb_posts WHERE pis_firstpost!=1");
					$stocache['posts']			= $cache_result3['posts'];
					$stocache['topics']			= $cache_result31['topics'];
					$stocache['replies']		= $cache_result32['replies'];
					$stocache['user_count']		= $cache_result['count'];
					$stocache['user_newest']	= $cache_result2;
					$data						= $stocache;
					break;
			}
		}
		
		$this->cache[$cacheid]					= $data;
		
		return $std->recache($data,$cacheid);
	}

	/**
	 * Gets a cache
	 *
	 * @param		string		the cache you want to retrieve
	 */
	function get_cache($cacheid)
	{
		global $icebb,$db,$std;
	
		return $this->cache[$cacheid];
	}
}
?>
