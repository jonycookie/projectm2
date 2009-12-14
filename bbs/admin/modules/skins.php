<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net// 1.0
//******************************************************//
// skins admin module
// $Id: skins.php 559 2005-08-26 18:06:10Z mutantmonkey $
//******************************************************//

class skins
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
 		$this->lang					= $icebb->admin->learn_language('skins');
		$this->html					= $icebb->admin_skin->load_template('skins');
		
		$icebb->admin->page_title	= $this->lang['skins'];
		
		switch($icebb->input['func'])
		{
			case 'enable':
				$this->enable_skin();
				break;
			case 'disable':
				$this->disable_skin();
				break;
			case 'export':
				$this->export_skin();
				break;
			case 'css':
				$this->css();
				break;
			case 'wrapper':
				$this->wrapper();
				break;
			case 'templates':
				$this->templates();
				break;
			case 'macros':
				$this->macros();
				break;
			default:
				$this->skin_listing();
				break;
		}
		
		$icebb->admin->html					= $icebb->admin->html;

		$icebb->admin->output();
	}
	
	function skin_listing()
	{
		global $icebb,$config,$db,$std;
		
		if(!empty($icebb->input['default_skin']))
		{
			$db->query("UPDATE icebb_skins SET skin_is_default=0");
			$db->query("UPDATE icebb_skins SET skin_is_default=1 WHERE skin_id=".intval($icebb->input['default_skin']));
			$this->skins_recache();
			
			$icebb->admin->redirect($this->lang['skin_set_as_default'],"{$icebb->base_url}act=skins");
		}
		
		$skins						= $db->query("SELECT * FROM icebb_skins");
		while($s					= $db->fetch_row($skins))
		{
			$s['enabled']			= true;
			$el_skins[]				= $s;
			$installed[]			= $s['skin_folder'];
		}
		
		$dh							= @opendir(PATH_TO_ICEBB.'skins');
		while(false !== ($dir = @readdir($dh)))
		{
			if(in_array($dir,$installed))
			{
				continue;
			}
			
			$could_include			= @include(PATH_TO_ICEBB."skins/{$dir}/skin_info.php");
			if($could_include)
			{
				/*if(version_compare(ICEBB_VERSION,$skin['icebb_ver'])<0)
				{
					continue;
				}*/
			
				foreach($skin as $sk => $s)
				{
					$skin2["skin_{$sk}"]= $s;
				}
				
				$skin2['directory']	= $dir;
			
				if(is_array($skin))
				{
					$el_skins[]		= $skin2;
				}
			}
		}
		@closedir($dh);

		$icebb->admin->html			= $this->html->show_main($el_skins);
	}
	
	function enable_skin()
	{
		global $icebb,$db,$std;

		$dir					= basename($icebb->input['skinfolder']);

		$could_include			= @include(PATH_TO_ICEBB."skins/{$dir}/skin_info.php");
		if(!$could_include)
		{
			$icebb->admin->error($this->lang['cannot_load_info']);
		}
		
		if(version_compare(ICEBB_VERSION,$skin['icebb_ver'])<0)
		{
			$icebb->admin->error(sprintf($this->lang['not_compatible'],$skin['icebb_ver']));
		}

		$db->insert('icebb_skins',array(
			'skin_name'			=> $skin['name'],
			'skin_author'		=> $skin['author'],
			'skin_site'			=> $skin['site'],
			'skin_folder'		=> $dir,
			'skin_wrapper'		=> $db->escape_string($skin['wrapper']),
			'skin_macro_cache'	=> $db->escape_string(serialize($skin['macros'])),
			'smiley_set'		=> 'default',
		));
		
		$skin_id				= $db->get_insert_id();
		
		foreach($skin['macros'] as $m)
		{
			$db->insert('icebb_skin_macros',array(
				'skin_id'		=> $skin_id,
				'string'		=> $db->escape_string($m[0]),
				'replacement'	=> $db->escape_string($m[1]),
			));
		}
		
		$icebb->admin->redirect($this->lang['skin_enabled'],"{$icebb->base_url}act=skins");
	}
	
	function disable_skin()
	{
		global $icebb,$db,$std;

		$db->query("DELETE FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}'");
		$db->query("DELETE FROM icebb_skin_macros WHERE skin_id='{$icebb->input['skinid']}'");
		
		$icebb->admin->redirect($this->lang['skin_disabled'],"{$icebb->base_url}act=skins");
	}
	
	function export_skin()
	{
		global $icebb,$db,$std;
		
		$s					= $db->fetch_result("SELECT * FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}' LIMIT 1");
		$icebb_ver			= ICEBB_VERSION;
		
		$db->query("SELECT * FROM icebb_skin_macros WHERE skin_id='{$icebb->input['skinid']}'");
		while($m			= $db->fetch_row())
		{
			$m['replacement']= addslashes($m['replacement']);
			$m['replacement']= str_replace("\'","'",$m['replacement']);
			$macros		   .= "array(\"{$m['string']}\",\"{$m['replacement']}\"),\n";
		}

		@header("Content-type: text/plain");
		echo <<<AKU
<?php
\$skin['name']				= "{$s['skin_name']}";
\$skin['author']			= "{$s['skin_author']}";
\$skin['site']				= "{$s['skin_site']}";
\$skin['icebb_ver']			= '{$icebb_ver}';
\$skin['wrapper']			= <<<EOF
{$s['skin_wrapper']}

EOF;
\$skin['macros']			= array(
{$macros}
);
?>

AKU;

		exit();
	}
	
	function css()
	{
		global $icebb,$config,$db,$std;
	
		if(isset($icebb->input['save']))
		{
			$this->css_save();
		}
		else {
			$this->css_editor();
		}
	}
	
	function css_save()
	{
		global $icebb,$config,$db,$std;
	
		$icebb->admin->page_title	= $this->lang['css_editor'];
		
		$s							= $db->fetch_result("SELECT * FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}' LIMIT 1");
		
		$csstowrite					= html_entity_decode($icebb->input['t3h_css'],ENT_QUOTES);
		$csstowrite					= str_replace('<#skin_images#>','images',$csstowrite);
		
		$cssfh						= @fopen(PATH_TO_ICEBB."skins/{$s['skin_folder']}/css.css",'w');
		@fwrite($cssfh,$csstowrite);
		@fclose($cssfh);
		
		$icebb->admin->redirect($this->lang['css_updated'],"{$icebb->base_url}&act=skins&func=css&skinid={$icebb->input['skinid']}");
	}
	
	function css_editor()
	{
		global $icebb,$config,$db,$std;
	
		$icebb->admin->page_title	= $this->lang['css_editor'];
	
		$s							= $db->fetch_result("SELECT * FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}' LIMIT 1");
		$path						= PATH_TO_ICEBB."skins/{$s['skin_folder']}/css.css";
		$css						= @file_get_contents($path);
		if(!is_writeable($path))
		{
			$extra['textarea']		= " readonly='readonly'";
			$extra['save']			= " disabled='disabled'";
		}

		$icebb->admin->html			= $this->html->file_editor('css','t3h_css',$css,$extra);
	}
	
	function wrapper()
	{
		global $icebb,$config,$db,$std;
	
		$icebb->admin->page_title	= $this->lang['wrapper_editor'];
	
		if(isset($icebb->input['save']))
		{
			$icebb->input['t3h_wrapper']	= html_entity_decode($icebb->input['t3h_wrapper'],ENT_QUOTES);
			$icebb->input['t3h_wrapper']	= addslashes($icebb->input['t3h_wrapper']);

			$db->query("UPDATE icebb_skins SET skin_wrapper='{$icebb->input['t3h_wrapper']}' WHERE skin_id='{$icebb->input['skinid']}' LIMIT 1");
			
			$this->skins_recache();
			
			$icebb->admin->redirect($this->lang['wrapper_updated'],"{$icebb->base_url}&act=skins&func=wrapper&skinid={$icebb->input['skinid']}");
		}
			
		$s							= $db->fetch_result("SELECT * FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}' LIMIT 1");
	
		$icebb->admin->html			= $this->html->file_editor('wrapper','t3h_wrapper',$s['skin_wrapper']);
	}
	
	function templates()
	{
		global $icebb,$config,$db,$std;
		
		switch($icebb->input['code'])
		{
			case 'edit':
				$this->template_editor();
				break;
			default:
				$this->template_listing();
				break;
		}
	}
	
	function template_listing()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->admin->page_title			= $this->lang['edit_templates'];
	
		$s									= $db->fetch_result("SELECT * FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}'");
		$path								= PATH_TO_ICEBB."skins/{$s['skin_folder']}";
	
		$templates							= glob("{$path}/*.php");
		
		$icebb->admin->html					= $this->html->template_list($templates);
	}
	
	function template_editor()
	{
		global $icebb,$db,$std;
	
		$s									= $db->fetch_result("SELECT * FROM icebb_skins WHERE skin_id='{$icebb->input['skinid']}'");
		$path								= PATH_TO_ICEBB."skins/{$s['skin_folder']}/".basename($icebb->input['template']).".php";
	
		if(isset($icebb->input['submit']))
		{
			// SkinWise
			require('includes/classes/skinwise.php');
			$skinwise						= new skinwise();	
				
			$icebb->input['t3h_template']	= html_entity_decode(html_entity_decode($icebb->input['t3h_template']));	
			
			$warnings						= $skinwise->get_wiser($icebb->input['t3h_template']);
			
			if(!empty($warnings))
			{
				$icebb->admin->html	= $icebb->admin_skin->start_table('SkinWise Message');
			
				foreach($warnings as $msg)
				{
					$icebb->admin->html.= $icebb->admin_skin->table_row($msg);
				}
				
				$icebb->admin->html.= $icebb->admin_skin->end_table();
			}
			else {
				$fh							= @fopen($path);
				@fwrite($fh,$icebb->input['t3h_template']);
				@fclose($fh);
			
				$icebb->admin->redirect($this->lang['template_updated'],"{$icebb->base_url}act=skins&func=templates&skinid={$icebb->input['skinid']}&code=edit&tid={$icebb->input['tid']}");
			}
		}
	
		$icebb->admin->page_title			= $this->lang['template_editor'];
	
		$template_code						= @file_get_contents($path);
		if(!is_writeable($path))
		{
			$extra['textarea']				= " readonly='readonly'";
			$extra['save']					= " disabled='disabled'";
		}
		
		$extra['hidden']					= "<input type='hidden' name='code' value='edit' />\n<input type='hidden' name='template' value='{$icebb->input['template']}' />\n";

		$icebb->admin->html					= $this->html->file_editor('templates','t3h_template',$template_code,$extra);
	}
	
	function macros()
	{
		global $icebb,$db,$std;
		
		switch($icebb->input['code'])
		{
			case 'recache':
				$this->macros_recache();
				break;
			default:
				$this->macro_editor();
				break;
		}
	}
	
	function macro_editor()
	{
		global $icebb,$db,$std;
		
		$icebb->admin->page_title= $this->lang['macro_editor'];
		
		$db->query("SELECT * FROM icebb_skin_macros WHERE skin_id='{$icebb->input['skinid']}' ORDER BY string");
		while($m			= $db->fetch_row())
		{
			$macros[]		= $m;
		}
		
		if(!empty($icebb->input['save']))
		{
			foreach($icebb->input['replacement'] as $mid => $r)
			{
				$r			= html_entity_decode($r,ENT_QUOTES);
				$r			= addslashes($r);
				
				$db->query("UPDATE icebb_skin_macros SET replacement='{$r}' WHERE id='{$mid}'");
			}
			
			$this->macros_recache();
			
			$icebb->admin->redirect($this->lang['macros_updated'],"{$icebb->base_url}act=skins");
		}
		
		$icebb->admin->html	= $this->html->macro_editor($macros);
	}
	
	function macros_recache()
	{
		global $icebb,$db,$std;
	
		$macroq				= $db->query("SELECT * FROM icebb_skin_macros WHERE skin_id='{$icebb->input['skinid']}'");
		while($m			= $db->fetch_row($macroq))
		{
			$macroso[$m['string']]= $m['replacement'];
		}
		
		$db->query("UPDATE icebb_skins SET skin_macro_cache='".addslashes(serialize($macroso))."' WHERE skin_id='{$icebb->input['skinid']}'");
			
		$this->skins_recache();
	}
	
	function skins_recache()
	{
		global $icebb,$db,$std;
	
		$db->query("SELECT * FROM icebb_skins");
		while($s			= $db->fetch_row())
		{
			$skins[$s['skin_id']]= $s;
		}
		$std->recache($skins,'skins');
	}
}
?>
