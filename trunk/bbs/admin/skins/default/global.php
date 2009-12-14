<?php
class skin_global
{
	function header()
	{
		global $icebb;
	
		$code .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>{$icebb->lang['admin_control_center']}</title>
<style type='text/css' media='screen'>
@import 'skins/default/css.css';
</style>
<meta http-equiv='Content-Type' content="text/html; charset=utf-8" />
<script type='text/javascript' src='../jscripts/global.js'></script>
<script type='text/javascript' src='../jscripts/xmlhttp.js'></script>
<script type='text/javascript' src='../jscripts/menu.js'></script>
<script type='text/javascript' src='../jscripts/md5.js'></script>
<script type='text/javascript' src='../jscripts/prototype/prototype.js'></script>
<script type='text/javascript' src='../jscripts/scriptaculous/scriptaculous.js'></script>
{$icebb->admin->header_extra}
<script type='text/javascript'>icebb_base_url='{$icebb->base_url}';</script>
</head>
<body>
<h1>{$icebb->lang['admin_control_center']}</h1>

<div id='topnav'>
	<a href='{$icebb->base_url}act=home'>Admin Home</a> &middot;
	<a href='../index.php'>Back to board</a>
</div>

<div id='initWrap'>

EOF;

		$code .= skin_global::load_nav();
		
		if(!empty($icebb->admin->page_title))
		{
			$code .= "<h2>{$icebb->admin->page_title}</h2>\n";
		}

		$code .= <<<EOF

EOF;

		return $code;
	}
	
	function load_nav()
	{
		global $icebb,$db;
		
		if(empty($icebb->adsess['asid']))
		{
			return;
		}
		
		foreach($icebb->menu_cats as $mc)
		{
			foreach($mc as $k => $menuc)
			{
				$mcats[$k]= $menuc;
			}
		}
		
		foreach($icebb->menu_pages as $p => $pg)
		{
			$mcats[$p]['pages']= $pg;
		}
		
		$db->query("SELECT * FROM icebb_settings_sections WHERE st_hidden!='1' ORDER BY st_sort");
		while($ss					= $db->fetch_row())
		{
			$mcats[1]['pages'][]	= array($ss['st_title'],"act=settings&group={$ss['st_id']}");
		}

		$code			   .= <<<EOF
<table width='100%' cellpadding='2' cellspacing='1' style='margin-bottom:6px'>
	<tr>

EOF;

		$z					= 0;
		foreach($mcats as $ck => $c)
		{
			$z++;
		
			if($z>10)
			{
				$code	   .= "</tr><tr>\n";
				$z			= 1;
			}
			
			if($z<10 && $z<count($mcats))
			{
				$border		= ";border-right:1px solid #eee";
			}
			else {
				$border		= '';
			}
		
			$code		   .= <<<EOF
		<td valign='top' style='font-size:80%{$border}'>
			<strong>{$c[0]}</strong><br />

EOF;

			foreach($c['pages'] as $page)
			{
				$code	   .= "<a href='{$icebb->base_url}{$page[1]}'>{$page[0]}</a><br />\n";
			}
			
			$code		   .= <<<EOF
		</td>

EOF;
		}
		
		$code			   .= <<<EOF
	</tr>
</table>

EOF;
		
		return $code;
	}
	
	function footer()
	{
		$code .= <<<EOF

</div>
</body>
</html>
EOF;

		return $code;
	}
}
?>
