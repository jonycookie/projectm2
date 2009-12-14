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
// admin skin functions class
// $Id: skin_func.php 325 2005-08-02 03:15:45Z mutantmonkey $
//******************************************************//

define('ROOT_PATH'		, '../');

if(!defined('IN_ICEBB'))
{
	die('This file may not be accessed directly.');
}

class adm_skin
{
	var $skin_id		= 'default';

	function load_template($template)
	{
		global $icebb,$std,$error_handler;
		
		$this->output	= "";

		if(file_exists("skins/{$this->skin_id}/{$template}.php"))
		{
			require_once("skins/{$this->skin_id}/{$template}.php");
		}
		else {
			$error_handler->skin_error('load',"The template '{$template}' could not be loaded");
		}
		
		$this->loaded_templates[]= $template;
		
		$name_template		= "skin_{$template}";
		
		return new $name_template();
	}

	// TABLES
	// ------

	function start_table($tname='',$cellpadding='2',$cellspacing='0',$extra='')
	{
		global $icebb,$db,$config,$std;
		
		$this->in_table			= 1;
		
		$html					= "<div class='borderwrap'>\n";
		
		if(!empty($tname))
		{
			$html			   .= "<h3>{$tname}</h3>\n";
		}

		$html				   .= "<table width='100%' cellpadding='{$cellpadding}' cellspacing='{$cellspacing}' border='0'{$extra}>\n";

		if(is_array($this->table_titles))
		{
			$html			   .= "\t<tr>";
		
			foreach($this->table_titles as $t)
			{
				if(!empty($t[0]) && $t[0]!='{none}')
				{
					if(!empty($t[1]))
					{
						$widy	= " width='{$t[1]}'";
					}
					
					$html .= "\t\t<th{$widy} style='margin:-2px'>{$t[0]}</th>\n";
					
					$widy		= NULL;
				}
			}

			$html			   .= "\t</tr>\n";
		}

		return $html;
	}
	
	function table_row($row_data,$class='row1',$extra='')
	{
		global $icebb,$db,$config,$std;
		
		if(is_array($row_data))
		{
			$html			= "\t<tr>\n";
			
			foreach($row_data as $i => $r)
			{
				if($class=='row1')
				{
					$td_class= $i % 2 ? 'row1' : 'row2';
				}
				else {
					$td_class= $class;
				}
			
				$this->table_cols++;
			
				if($this->table_titles[$i][0]=='{none}' || $this->table_titles[$i][0]=='')
				{
					$width	= " width='".$this->table_titles[$i][1]."'";
				}
				else {
					$width	= '';
				}
			
				$html	   .= "\t\t<td class='{$td_class}'{$width}{$extra}>\n\t\t\t{$r}\n\t\t</td>\n";
			}
			
			$html		   .= "\t</tr>\n";
		}
		else {
			if($this->table_cols>=1)
			{
				$extre		= " colspan='{$this->table_cols}'";
			}
		
			$html			= "\t<tr class='{$class}'>\n\t\t<td{$extra}{$extre}>\n\t\t\t{$row_data}\n\t\t</td>\n\t</tr>\n";
		}
		
		$this->table_titles	= NULL;
		
		return $html;
	}
	
	function table_checkbox_row($label,$chk_name,$chk_val='',$class='row1',$extra='')
	{
		global $icebb,$db,$config,$std;
		
		if($this->table_cols>=1)
		{
			$extre		= " colspan='{$this->table_cols}'";
		}
		
		$html			= "\t<tr class='{$class}'>\n\t\t<td{$extra}{$extre}>\n\t\t\t".$this->form_checkbox($chk_name,$chk_val)." {$label}\n\t\t</td>\n\t</tr>\n";
		
		$this->table_titles	= NULL;
		
		return $html;
	}
	
	function end_table()
	{
		$html				= "</table>\n</div>";
	
		$this->in_table		= 0;
		$this->table_cols	= 0;
	
		return $html;
	}
	
	// END TABLES
	// ----------
	
	
	// FORMS
	// -----
	
	function start_form($hidden=array(),$method='post',$extra='',$ete='')
	{
		global $icebb;
	
		if(!is_array($hidden))
		{
			$hidden			= $method;
			$method			= $extra;
			$extra			= $ete;
		}
	
		$html				= "<form action='index.php' method='{$method}'{$extra}>\n";
	
		//if(array_key_exists('s',$hidden))
		//{
			$hidden['s']	= $icebb->adsess['asid'];
		//}
	
		foreach($hidden as $i => $h)
		{
			$html		   .= "<input type='hidden' name='{$i}' value='{$h}' />\n";
		}
	
		return $html;
	}
	
	function form_input($name,$value='',$size='30',$extra='')
	{
		$html				= "<input type='text' name='{$name}' value='{$value}' class='textbox' size='{$size}'{$extra} />\n";
	
		return $html;
	}
	
	function form_hidden($name,$value='',$extra='')
	{
		$html				= "<input type='hidden' name='{$name}' value='{$value}'{$extra} />\n";
	
		return $html;
	}
	
	function form_password($name,$value='',$size='30',$extra='')
	{
		$html				= "<input type='password' name='{$name}' value='{$value}' class='textbox' size='{$size}'{$extra} />\n";
	
		return $html;
	}
	
	function form_textarea($name,$value='',$rows='5',$cols='30',$extra='')
	{
		$html				= "<textarea name='{$name}' rows='{$rows}' cols='{$cols}'{$extra}>{$value}</textarea>\n";
	
		return $html;
	}
	
	function form_yes_no($name,$value='',$extra_yes='',$extra_no='')
	{
		if($value=='1')
		{
			$extra_yes	   .= " checked='checked'";
		}
		else {
			$extra_no	   .= " checked='checked'";
		}
		
		// render "yes" radio button (I wonder why they call them that... O_o)
		$html				= "<span class='choice-yes'><input type='radio' name='{$name}' value='1' id='{$name}_yes'{$extra_yes}><label for='{$name}_yes'>Yes</label></span>\n";
		
		// render "no" radio button
		$html			   .= "<span class='choice-no'><input type='radio' name='{$name}' value='0' id='{$name}_no'{$extra_no}><label for='{$name}_no'>No</label></span>\n";
	
		return $html;
	}
	
	function form_checkbox($name,$value='1',$checked=0,$extra='')
	{
		if($checked==1)
		{
			$extra			= " checked='checked'{$extra}";
		}
		
		$html				= "<input type='checkbox' name='{$name}' value='{$value}'{$extra} />\n";

		return $html;
	}
	
	function form_dropdown($name,$options=array(),$value='',$extra='')
	{
		$html				= "<select name='{$name}' class='dropdown'{$extra}>\n";
		foreach($options as $opt)
		{
			if($opt[0]==$value)
			{
				$extre		= " selected='selected'";
			}
		
			$html		   .= "\t<option value='{$opt[0]}'{$extre}>{$opt[1]}</option>\n";

			$extre			= null;
		}
		$html			   .= "</select>\n";

		return $html;
	}
	
	function form_multiselect($name,$options=array(),$value='',$size=7,$extra='')
	{
		$value				= explode(',',$value);
	
		$html				= "<select name='{$name}[]' class='dropdown' multiple='multiple' size='{$size}'{$extra}>\n";
		foreach($options as $opt)
		{
			if(in_array($opt[0],$value))
			{
				$extre		= " selected='selected'";
			}
		
			$html		   .= "\t<option value='{$opt[0]}'{$extre}>{$opt[1]}</option>\n";

			$extre			= null;
		}
		$html			   .= "</select>\n";

		return $html;
	}
	
	function form_button($name='submit',$button_text='Submit',$extra='')
	{
		//$html				= "<button name='{$name}' class='button'{$extra}>{$button_text}</button>\n";
		$html				= "<input type='submit' name='{$name}' value='{$button_text}'{$extra} />\n";
	
		return $html;
	}
	
	function end_form($button_text='Submit',$extra='')
	{
		if($this->in_table==1)
		{
			$html			= $this->table_row("<input type='submit' value='{$button_text}' class='button'{$extra} />\n",'row2'," align='center'");
		}
		else {
			$html			= "<input type='submit' value='{$button_text}' class='button'{$extra} />\n";
		}

		$html			   .= "</form>\n";
	
		return $html;
	}
	
	// END FORMS
	// ----------
	
	
	// render forum permissions
	function render_permissions_table($perms,$button='',$frmname='adminFrm')
	{
		global $icebb,$db,$config,$std;
		
		if(!is_array($perms))
		{
			$perms			= unserialize($icebb->cache['admin']['default_perms']);
		}
		
		$html				= <<<EOF
<script type='text/javascript'>
<!--
function _check_col(colname,num)
{
	f			= document.{$frmname};

	for(i=0;i<f.elements.length;i++)
	{
		if(f.elements[i].name.substring(0,colname.length) == colname)
		{
			f.elements[i].checked		= _is_checked('all',colname);
		}
	}
}

function _check_row(idnum)
{
	f			= document.{$frmname};

	if( _is_checked(idnum,'seeforum')==0 && 
		_is_checked(idnum,'read')==0 && 
		_is_checked(idnum,'createtopics')==0 && 
		_is_checked(idnum,'reply')==0 && 
		_is_checked(idnum,'attach')==0
	  )
	{
		eval('f.seeforum_'+idnum+'.checked=1;');
		eval('f.read_'+idnum+'.checked=1;');
		eval('f.createtopics_'+idnum+'.checked=1;');
		eval('f.reply_'+idnum+'.checked=1;');
		eval('f.attach_'+idnum+'.checked=1;');
		eval('f.all_'+idnum+'.checked=1;');
	}
	else {
		eval('f.seeforum_'+idnum+'.checked=0;');
		eval('f.read_'+idnum+'.checked=0;');
		eval('f.createtopics_'+idnum+'.checked=0;');
		eval('f.reply_'+idnum+'.checked=0;');
		eval('f.attach_'+idnum+'.checked=0;');
		eval('f.all_'+idnum+'.checked=0;');
	}
}

function _is_checked(idnum,what)
{
	eval('item = document.{$frmname}.'+what+'_'+idnum+';');
	
	return item.checked;
}
//-->
</script>
EOF;
		
		$this->table_titles[]= array('&nbsp;');
		$this->table_titles[]= array('See Forum','14%');
		$this->table_titles[]= array('Read Topics','14%');
		$this->table_titles[]= array('Create Topics','14%');
		$this->table_titles[]= array('Reply','14%');
		$this->table_titles[]= array('Attach Files','14%');
		$this->table_titles[]= array('&nbsp;','3%');
		
		$html			   .= $this->start_table("Permissions");
		
		$thisrow[0]			= "";
		$thisrow[1]			= $this->form_checkbox('seeforum_all',''," onclick='_check_col(\"seeforum\")'");
		$thisrow[2]			= $this->form_checkbox('read_all',''," onclick='_check_col(\"read\")'");
		$thisrow[3]			= $this->form_checkbox('createtopics_all',''," onclick='_check_col(\"createtopics\")'");
		$thisrow[4]			= $this->form_checkbox('reply_all',''," onclick='_check_col(\"reply\")'");
		$thisrow[5]			= $this->form_checkbox('attach_all',''," onclick='_check_col(\"attach\")'");
		$thisrow[6]			= '&nbsp;';
		
		//$html			   .= $this->table_row($thisrow,'row2');
		
		$db->query("SELECT * FROM icebb_forum_permgroups");
		while($p			= $db->fetch_row())
		{
			$numperms++;
			$thisrow		= array();
			
			$thisrow[0]		= "<b>{$p['permname']}</b>";
			$thisrow[1]		= $this->form_checkbox("seeforum_{$p['permid']}",'1',$perms[$p['permid']]['seeforum']);
			$thisrow[2]		= $this->form_checkbox("read_{$p['permid']}",'1',$perms[$p['permid']]['read']);
			$thisrow[3]		= $this->form_checkbox("createtopics_{$p['permid']}",'1',$perms[$p['permid']]['createtopics']);
			$thisrow[4]		= $this->form_checkbox("reply_{$p['permid']}",'1',$perms[$p['permid']]['reply']);
			$thisrow[5]		= $this->form_checkbox("attach_{$p['permid']}",'1',$perms[$p['permid']]['attach']);
			$thisrow[6]		= $this->form_checkbox("all_{$p['permid']}",'1',''," style='row3' onclick='_check_row(\"{$p['permid']}\")'");
		
			$html		   .= $this->table_row($thisrow);
		}
		
		if($button!='')
		{
			//$this->end_form($button);
		}
		
		$thisrow[0]			= "<label>".$this->form_checkbox('set_as_default','1','')."Set as default</label>";
		$thisrow[1]			= $this->form_checkbox('seeforum_all','',''," onclick='_check_col(\"seeforum\",{$numperms})'");
		$thisrow[2]			= $this->form_checkbox('read_all','',''," onclick='_check_col(\"read\",{$numperms})'");
		$thisrow[3]			= $this->form_checkbox('createtopics_all','',''," onclick='_check_col(\"createtopics\",{$numperms})'");
		$thisrow[4]			= $this->form_checkbox('reply_all','',''," onclick='_check_col(\"reply\",{$numperms})'");
		$thisrow[5]			= $this->form_checkbox('attach_all','',''," onclick='_check_col(\"attach\",{$numperms})'");
		$thisrow[6]			= '&nbsp;';
		
		$html			   .= $this->table_row($thisrow,'Subtitle');
		
		$html			   .= $this->end_table();
		
		return $html;
	}
}
?>
