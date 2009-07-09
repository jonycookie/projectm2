<?php
defined('IN_EXT') or die('Forbidden');
class advCache{
	function jsCache($adpinfo,$advinfo){
		global $db;
		$jscode = '';
		$zoneAD = "ZoneAD_{$adpinfo[pid]}";
		if($adpinfo['type'] == 'banner'){
			$jscode = readover(E_P.'script/adBanner.js');
			$jscode .= "
var {$zoneAD} = new BannerZoneAD(\"$zoneAD\");
{$zoneAD}.ZoneID      = $adpinfo[pid];
{$zoneAD}.ZoneWidth   = $adpinfo[width];
{$zoneAD}.ZoneHeight  = $adpinfo[height];
{$zoneAD}.ShowType    = $adpinfo[showtype];
{$zoneAD}.Active      = $adpinfo[active];
";
		} elseif($adpinfo['type'] == 'fixed'){
			$jscode = readover(E_P.'script/adFixed.js');
			$jscode .= "
var {$zoneAD} = new FixedZoneAD(\"$zoneAD\");
{$zoneAD}.ZoneID      = $adpinfo[pid];
{$zoneAD}.ZoneWidth   = $adpinfo[width];
{$zoneAD}.ZoneHeight  = $adpinfo[height];
{$zoneAD}.ShowType    = $adpinfo[showtype];
{$zoneAD}.Left        = {$adpinfo[setting][left]};
{$zoneAD}.Top         = {$adpinfo[setting][top]};
{$zoneAD}.Active      = $adpinfo[active];
";
		} elseif($adpinfo['type'] == 'pop'){
			$jscode = readover(E_P.'script/adPop.js');
			$jscode .= "
var {$zoneAD} = new PopZoneAD(\"$zoneAD\");
{$zoneAD}.ZoneID      = $adpinfo[pid];
{$zoneAD}.ZoneWidth   = $adpinfo[width];
{$zoneAD}.ZoneHeight  = $adpinfo[height];
{$zoneAD}.ShowType    = $adpinfo[showtype];
{$zoneAD}.Left        = {$adpinfo[setting][left]};
{$zoneAD}.Top         = {$adpinfo[setting][top]};
{$zoneAD}.PopType     = {$adpinfo[setting][poptype]};
{$zoneAD}.CookieHour  = {$adpinfo[setting][cookiehour]};
{$zoneAD}.Active      = $adpinfo[active];
";
		} elseif($adpinfo['type'] == 'float'){
			$jscode = readover(E_P.'script/adFloat.js');
			$jscode .= "
var {$zoneAD} = new FloatZoneAD(\"$zoneAD\");
{$zoneAD}.ZoneID      = $adpinfo[pid];
{$zoneAD}.ZoneWidth   = $adpinfo[width];
{$zoneAD}.ZoneHeight  = $adpinfo[height];
{$zoneAD}.ShowType    = $adpinfo[showtype];
{$zoneAD}.Left        = {$adpinfo[setting][left]};
{$zoneAD}.Top         = {$adpinfo[setting][top]};
{$zoneAD}.floattype     = {$adpinfo[setting][floattype]};
{$zoneAD}.delay  = {$adpinfo[setting][delay]};
{$zoneAD}.Active      = $adpinfo[active];
";
		} elseif($adpinfo['type'] == 'move'){
			$jscode = readover(E_P.'script/adMove.js');
			$jscode .= "
var {$zoneAD} = new MoveZoneAD(\"$zoneAD\");
{$zoneAD}.ZoneID      = $adpinfo[pid];
{$zoneAD}.ZoneWidth   = $adpinfo[width];
{$zoneAD}.ZoneHeight  = $adpinfo[height];
{$zoneAD}.ShowType    = $adpinfo[showtype];
{$zoneAD}.Left        = {$adpinfo[setting][left]};
{$zoneAD}.Top         = {$adpinfo[setting][top]};
{$zoneAD}.floattype     = {$adpinfo[setting][floattype]};
{$zoneAD}.Delta  = {$adpinfo[setting][delta]};
{$zoneAD}.Active      = $adpinfo[active];
";
		} elseif($adpinfo['type'] == 'code'){
			$jscode = readover(E_P.'script/adCode.js');
			$jscode .= "
var {$zoneAD} = new CodeZoneAD(\"$zoneAD\");
{$zoneAD}.ZoneID      = $adpinfo[pid];
{$zoneAD}.Active      = $adpinfo[active];
";
		}

		foreach($advinfo as $value){
		$jscode .="
var objAD = new ObjectAD();
objAD.ADID           = $value[adid];
objAD.ADType         = $value[type];
objAD.ADName         = \"$value[name]\";
objAD.ImgUrl         = \"{$value[config][url]}\";
objAD.ImgWidth       = {$value[config][width]};
objAD.ImgHeight      = {$value[config][height]};
objAD.FlashWmode     = {$value[config][flashwmode]};
objAD.ADIntro        = \"$value[intro]\";
objAD.LinkUrl        = \"$value[linkurl]\";
objAD.LinkTarget     = $value[linktarget];
objAD.LinkAlt        = \"$value[linkalt]\";
objAD.Priority       = $value[priority];
objAD.CountView      = 0;
objAD.CountClick     = 0;
objAD.InstallDir     = \"/\";
objAD.ADDIR          = \"\";
objAD.StartTime      = $value[starttime];
objAD.EndTime        = $value[endtime];
objAD.Cid			 = \"$value[cid]\";
{$zoneAD}.AddAD(objAD);
";
		}
		$jscode .= "{$zoneAD}.Show();";
		writeover(R_P.'script/verycms/'.$adpinfo['jsname'].'.js',$jscode);
	}
}