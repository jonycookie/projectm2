<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3.1
//******************************************************//
// help module
// $Id: help.php 531 2006-10-03 00:40:12Z mutantmonkey0 $
//******************************************************//

class help
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$std->error("Coming in 1.0, until then you can e-mail pro5ive (at) gmail [dot] com for help.");
		
		$this->html						= $icebb->skin->load_template('help');
		$this->lang						= $std->learn_language("help");
		//$this->htmlt						= $icebb->skin->load_template('helped');
		
		$icebb->nav[]					= "<a href='{$icebb->base_url}act=help'>{$icebb->lang['help']}</a>";
		
		if(!empty($icebb->input['title']))
		{
			$db->query("SELECT * FROM icebb_helpbits WHERE hname='".str_replace('_',' ',$icebb->input['title'])."'");
			$h							= $db->fetch_row();
			$h['htext']					= nl2br($h['htext']);
			
			$this->output				= $this->html->help_topic($h);
		}
		else {
			$db->query("SELECT * FROM icebb_help_sections");
			while($s					= $db->fetch_row())
			{
				$sections[$s['hsid']]	= $s;
			}
		
			$db->query("SELECT * FROM icebb_helpbits");
			while($h					= $db->fetch_row())
			{
				$h['hname_']			= str_replace(' ','_',$h['hname']);
				$h['htext']				= html_entity_decode($h['htext'],ENT_QUOTES);
				$h['htext']				= nl2br($h['htext']);
				
				$help[$h['hsection']][]	= $h;
				//$actual_help[]			= $this->html->help_topic($h);
			}
			
			$this->output				= $this->html->help_page($sections,$help,$actual_help);
		}
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
}
?>