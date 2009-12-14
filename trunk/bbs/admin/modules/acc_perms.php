<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.kenkarpg.info // 1.0 Beta 6
//******************************************************//
// acc permissions admin module
// $Id: acc_perms.php 332 2005-08-03 15:17:53Z mutantmonkey $
//******************************************************//

class acc_perms
{
	function run()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->admin->page_title	= "ACC Permissions";
		
		switch($icebb->input['func'])
		{
			case 'template_import':
				$this->template_import();
				break;
			default:
				$this->home();
				break;
		}

		$icebb->admin->output();
	}
	
	function home()
	{
		global $icebb,$config,$db,$std;
	
		$icebb->admin->html		   .= $icebb->admin_skin->start_form('admin.php',array('act'=>'skintools','func'=>'template_import'));
		
		$icebb->admin_skin->table_titles[]	= array("{none}",'60%');
		$icebb->admin_skin->table_titles[]	= array("{none}",'40%');
		
		$icebb->admin->html		   .= $icebb->admin_skin->start_table("Import from Skin Files");
		$icebb->admin->html		   .= $icebb->admin_skin->table_row("Warning: This will overwrite any changes you have made that have not been cached.",'row2');
	
		$db->query("SELECT * FROM icebb_skins");
		while($s					= $db->fetch_row())
		{
			$skinsets[]				= array($s['skin_id'],$s['skin_name']);
		}
	
		$icebb->admin->html		   .= $icebb->admin_skin->table_row(array("For skin set?",$icebb->admin_skin->form_dropdown('onskin',$skinsets)));
		$icebb->admin->html		   .= $icebb->admin_skin->end_form("Import");
		$icebb->admin->html		   .= $icebb->admin_skin->end_table();
	}
	
	function template_import()
	{
		global $icebb,$config,$db,$std;
	
		$functions					= array();
	
		$dirhandle					= @opendir("skins/{$icebb->input['onskin']}");
		
		while($file					= @readdir($dirhandle))
		{
			$file2					= explode('.',$file);
		
			if($file2[1]			== 'php')
			{
				$content			= @file_get_contents("skins/{$icebb->input['onskin']}/{$file}");
		
				// clean up linebreaks
				$content			= str_replace("\r\n","\n",$content); 
				$content			= str_replace("\r","\n",$content);
				
				// explode
				$contente			= explode("\n",$content);
				
				foreach($contente as $c)
				{
					// check for Javascript
					if(preg_match("`<script`i",$c))
					{
						$in_script	= 1;
					}
					
					// ending?
					if(preg_match("`</script>`i",$c))
					{
						$in_script	= 0;
					}
					
					if($in_script	== 1)
					{
						// we have to clean up the javascript
						$c			= preg_replace("`if\s*\(`si","i[JS]f(",$c);
						$c			= preg_replace( "`else\s*if`si","el[JS]se i[JS]f",$c);
						$c			= preg_replace( "`else`si","el[JS]se",$c);
					}
					else {
						if(preg_match("`function\s*([A-Za-z0-9_]*)\(([A-Za-z0-9_,\$]*)\)`",$c,$match))
						{
							$functions[$file2[0]][$match[1]]= array($match[1],$match[2]);
							$on_function= $match[1];
						}
					}
					
					if(!empty($on_function))
					{
						$functions[$file2[0]][$on_function]['content'].= "{$c}\n";
					}
				}
			}
		}
		
		@closedir($dirhandle);
		
		$db->query("DELETE FROM icebb_skin_templates WHERE template_set='{$icebb->input['onskin']}'");
		
		foreach($functions as $fname => $file)
		{
			foreach($file as $func)
			{
				if(!empty($func[0]))
				{
					if(preg_match("`{(.*)}`si",$func['content'],$cmatch))
					{
						$fcontent[$func[0]]	= $cmatch[1];
						//$fcontent[$func[0]]	= preg_replace('`global \$icebb;`','',$fcontent[$func[0]]);
						//$fcontent[$func[0]]	= preg_replace('`return \$code;`','',$fcontent[$func[0]]);
						//$fcontent[$func[0]]	= str_replace("\$code .= <<<EOF","",$fcontent[$func[0]]);
						//$fcontent[$func[0]]	= preg_replace("`EOF;`","",$fcontent[$func[0]]);

						$cz			= $fcontent[$func[0]];

						// handle ifs() and such
						//$cz			= preg_replace("`if\s*\((.*)\)\n{(.*)}`si","<if=\"\\1\">\\2</if>",$cz);
						//$cz			= preg_replace( "`else\s*if\s*\(.*\)`si","<elseif=\"\\1\">",$cz);
						//$cz			= preg_replace( "`else`si","<else>",$cz);
						
						// convert Javascript back
						$cz			= preg_replace("`i[JS]f\(`si","if(",$cz);
						$cz			= preg_replace( "`el[JS]se i[JS]f`si","else if",$cz);
						$cz			= preg_replace( "`el[JS]se`si","else",$cz);
						
						$fcontent[$func[0]]= $cz;
					}
					
					$db->insert('icebb_skin_templates',array(
						'template_set'			=> $icebb->input['onskin'],
						'template_file'			=> $fname,
						'template_name'			=> $func[0],
						'template_code'			=> addslashes($fcontent[$func[0]]),
					));
				}
			}
		}
	}
}
?>