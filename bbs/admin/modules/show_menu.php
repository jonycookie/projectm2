<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.2
//******************************************************//
// show menu admin module
// $Id: show_menu.php 1362 2006-04-15 02:52:27Z mutantmonkey $
//******************************************************//

class show_menu
{
	function run()
	{
		global $icebb,$config,$db,$license_info;
		
		$tab_start			= empty($icebb->input['tab_start']) ? 1 : $icebb->input['tab_start'];
		
		require('pages.php');
		
		$icebb->admin->menu_cats	= $menu_cats;
		$icebb->admin->menu_pages	= $menu_pages;
		$icebb->hooks->hook('admin_menu');
		$menu_cats					= $icebb->admin->menu_cats;
		$menu_pages					= $icebb->admin->menu_pages;
		
		// settings
		$settingsq					= $db->query("SELECT * FROM icebb_settings_sections WHERE st_hidden!='1' ORDER BY st_sort");
		while($ss					= $db->fetch_row($settingsq))
		{
			$menu_pages[1][]		= array($ss['st_title'],"act=settings&group={$ss['st_id']}");
		}
		
		$icebb->admin->css			= "/* non-IE scrollbar fix */\r\nhtml { height:100.1%; }\r\n\r\n.border a { display:block;margin:2px; }";
		
		foreach($tabs as $i => $tab)
		//for($i=$tab_start;$i<=$tab_start+1;$i++)
		{
			if($i<$tab_start || $i>$tab_start+1)
			{
				continue;
			}
			
			if($i				== $tab_start)
			{
				$extra			= " style='height:14px;border-bottom:0px'";
			}
			else {
				$extra			= '';
			}
		
			$tabhtml		   .= "<a href='#tab-{$i}' id='tab-{$i}'{$extra} class='tab' onclick=\"return tab_switch_to('{$i}')\">{$tab}</a> ";
		
			if($i			   != $tab_start)
			{
				$extra_td	= " style='display:none'";
			}
			
			$textIZE		   .= "<div id='tabdata-{$i}'{$extra_td}>";
		
			foreach($menu_cats[$i] as $i => $mcats)
			{
				$textIZE	   .= "<div class='border'><h4>{$mcats[0]}</h4>";
			
				foreach($menu_pages[$i] as $pg)
				{
					$textIZE   .= "<a href='{$icebb->base_url}&{$pg[1]}' target='admin_main'>{$pg[0]}</a>";
				}
				
				$textIZE	   .= "</div>";
			}
			
			$textIZE		   .= "</div>";
		}
		
		if(count($tabs)>2)
		{
			$tab_start_min			= $tab_start-2<1 ? 1 : $tab_start-2;
			$tab_start_plus			= $tab_start+2;
		
			$tab_left				= " <a href='{$icebb->base_url}act=menu&amp;tab_start={$tab_start_min}' style='color:#fff;display:block;float:left;font-size:80%;text-decoration:none;margin-top:6px'>&laquo;</a>";
			$tab_right				= " <a href='{$icebb->base_url}act=menu&amp;tab_start={$tab_start_plus}' style='color:#fff;display:block;float:left;font-size:80%;text-decoration:none;margin-top:6px'>&raquo;</a>";
		}
		
		$icebb->admin->html = "<script type='text/javascript' src='tab.js'></script><script type='text/javascript'>tab_open='{$tab_start}'</script>";
		$icebb->admin->html		   .= <<<EOF
<div style="background:url('images/logobg.gif') #4C81BD">
	<img src='images/logo.gif' alt='IceBB Admin Control Center' />
</div>

<div style="background:url('images/tile_2.gif') #3F72AB repeat-x;padding-top:2px">
	<div class='smalllinks' style='color:#fff'>
		<a href='index.php' target='_top' style='color:#fff'>Back to board</a> &middot; 
		<a href='{$icebb->base_url}&act=home' target='admin_main' style='color:#fff'>Admin Home</a>
	</div>
	
	<div id='tabrow'>
		<span id='tab_bar_left'>{$tab_left}</span>
		<span id='tab_bar_right'>{$tab_right}</span>
		{$tabhtml}
	</div>
</div>

{$textIZE}

EOF;
		
		$icebb->admin->output();
	}
}
?>