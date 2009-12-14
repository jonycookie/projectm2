<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.kenkarpg.info // 0.9.3
//******************************************************//
// word filters admin module
// $Id: wordfilters.php 68 2005-07-12 17:19:36Z icebborg $
//******************************************************//

class wordfilters
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$this->lang							= $icebb->admin->learn_language('global');
		$this->html							= $icebb->admin_skin->load_template('global');
		
		$icebb->admin->page_title			= "Manage Word Filters";
		$icebb->admin->html					= <<<EOF
<p>Here you can choose which words are not allowed to be posted. They will be replaced with the replacement text
you specify. You may use * before and after the word. You may leave the replacement blank to have it replaced
with an asterisk (*) for each letter of the word.</p>
<p><strong>Examples:</strong> *ass* matches ass, asshole, and grass;
ass* matches ass, asshole, and assist; *ass matches ass and grass; ass matches ass</p>

EOF;
		
		$icebb->admin->html				   .= $icebb->admin_skin->start_form('admin.php',array('act'=>'wordfilters'));

		if(isset($icebb->input['wordadd']))
		{
			for($st=1;$st<=strlen(str_replace('*','',$icebb->input['word']));$st++)
			{
				$length_stars			   .= '*';
			}
	
			$icebb->input['replacement']	= empty($icebb->input['replacement']) ? $length_stars : $icebb->input['replacement'];
		
			$db->insert('icebb_wordfilters',array(
				'bw_word'					=> $icebb->input['word'],
				'bw_replacement'			=> $icebb->input['replacement'],
			));
			
			$this->rebuild_cache();
			
			$std->log("admin","Added word filter: {$icebb->input['word']}",$icebb->adsess['user']);
			$icebb->admin->redirect("Word filter added.",$icebb->base_url.'act=wordfilters');
		}

		$icebb->admin_skin->table_titles[]	= array("Word",'30%');
		$icebb->admin_skin->table_titles[]	= array("Replacement",'30%');
		$icebb->admin_skin->table_titles[]	= array("&nbsp;",'40%');

		$icebb->admin->html				   .= $icebb->admin_skin->start_table("Word Filters");
		$bwq								= $db->query("SELECT * FROM icebb_wordfilters");
		while($bw							= $db->fetch_row($bwq))
		{
			if($icebb->input['word_edit']	== $bw['bw_id'])
			{
				if(isset($icebb->input['wordeditn']))
				{
					$db->query("UPDATE icebb_wordfilters SET bw_word='{$icebb->input['word-'.$bw['bw_id']]}',bw_replacement='{$icebb->input['replace-'.$bw['bw_id']]}' WHERE bw_id='{$bw['bw_id']}'");
					$this->rebuild_cache();
					
					$ipdata = $db->fetch_result("SELECT bw_word FROM icebb_wordfilters WHERE bw_id='{$icebb->input['word_edit']}' LIMIT 1");
					
					$std->log("admin","Updated word filter: {$bw['bw_word']} -> {$icebb->input['word-'.$bw['bw_id']]}",$icebb->adsess['user']);
					
					$icebb->admin->redirect("Word filter updated.",$icebb->base_url.'act=wordfilters');
				}
				
				$icebb->admin->html		   .= "<input type='hidden' name='word_edit' value='{$bw['bw_id']}' />";
				$icebb->admin->html		   .= $icebb->admin_skin->table_row(array($icebb->admin_skin->form_input("word-{$bw['bw_id']}",$bw['bw_word']),$icebb->admin_skin->form_input("replace-{$bw['bw_id']}",$bw['bw_replacement']),$icebb->admin_skin->form_button('wordeditn',"Edit")));
			}
			else if($icebb->input['word_rem']== $bw['bw_id'])
			{
				$ipdata = $db->fetch_result("SELECT bw_word FROM icebb_wordfilters WHERE bw_id='{$icebb->input['word_rem']}' LIMIT 1");
				$db->query("DELETE FROM icebb_wordfilters WHERE bw_id='{$icebb->input['word_rem']}' LIMIT 1");
				$std->log("admin","Deleted ban filter: {$bw['bw_word']}",$icebb->adsess['user']);
				$this->rebuild_cache();
			}
			else {
				$icebb->admin->html		   .= $icebb->admin_skin->table_row(array($bw['bw_word'],$bw['bw_replacement'],"<div style='text-align:right'><a href='{$icebb->base_url}act=wordfilters&amp;word_edit={$bw['bw_id']}'>Edit</a> &middot; <a href='{$icebb->base_url}act=wordfilters&amp;word_rem={$bw['bw_id']}'>Remove</a></div>"));
			}
		}
		$icebb->admin->html				   .= $icebb->admin_skin->table_row(array($icebb->admin_skin->form_input('word'),$icebb->admin_skin->form_input('replacement'),$icebb->admin_skin->form_button('wordadd',"Add")),'row2');
		$icebb->admin->html				   .= $icebb->admin_skin->end_table();
		$icebb->admin->html				   .= "</form>";
		
		$icebb->admin->html			= $this->html->header().$icebb->admin->html.$this->html->footer();
		
		$icebb->admin->output();
	}
	
	function rebuild_cache()
	{
		global $icebb,$db,$config,$std;
	
		$wordfilters						= array();
		$db->query("SELECT * FROM icebb_wordfilters");
		while($bw						= $db->fetch_row())
		{
			$wordfilters[]				= $bw;
		}
		
		$std->recache($wordfilters,'word_filters');
	}
}
?>
