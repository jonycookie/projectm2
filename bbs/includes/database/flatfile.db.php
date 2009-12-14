<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9
//******************************************************//
// flat file class
// $Id$
//******************************************************//

// Remove everything up to and including this line when using this library
/*
This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
or view it online at http://www.gnu.org/copyleft/lesser.html
*/

// THESE SHOULD BE CHANGED TO RELFECT YOUR SETUP
define('ROOT_PATH'			, '../../');

//////////////////////////////////////////////////////////////////////////
//		YOU SHOULDN'T NEED TO CHANGE ANYTHING BELOW THIS LINE			//
//////////////////////////////////////////////////////////////////////////

require('flatfile.db');

class flatfile
{
	var $queries			= 0;
	var $total_time			= 0;
	var $libversion			= "1.0";

	function flatfile()
	{
		global $icebb,$config,$adodb,$flatfile_data;
	
		$this->ffdata		= unserialize($flatfile_data);
	
		$connection			= true;

		return $connection;
	}
	
	function query($sql)
	{
		global $icebb,$timer;
	
		// what type of query is this?
		if(preg_match("/\bSELECT (.*) FROM (.*)\b(.*)/",$sql,$sqlreturn))
		{
			// this is a select query
			
			// any wheres or joins or other shit?
			if(!empty($sqlreturn[3]))
			{
				if(preg_match("/\bWHERE\n/",$sql,$sqlret2))
				{
					// parse the where
				}
			}
			else {
				$result				= $this->ffdata[$sqlreturn[2]];
			}
		}
	
		//$timer->start($sql);
		
		//$result					= mysql_query($sql);
		
		/*if($result===false)
		{
			$this->mysql_error($sql,mysql_errno(),mysql_error());
		}
		
		$query_time				= $timer->stop($sql);
		
		$this->total_time		= $this->total_time+$query_time;
		
		//$num_rows				= $this->get_num_rows($result);
		
		$icebb->debug_html	   .= <<<EOF
<table width='100%' cellpadding='2' cellspacing='1' border='0'>
	<tr>
		<th colspan='2'>
			{$sql}
		</th>
	</tr>
	<tr>
		<td class='col2' width='40%'>
			Rows returned:
		</td>
		<td>
			{$num_rows}
		</td>
	</tr>
	<tr>
		<td class='col2' width='40%'>
			Time:
		</td>
		<td>
			{$query_time}sec
		</td>
	</tr>
</table>
EOF;*/
	
		$this->queries++;
		
		$this->last_result		= $result;
		
		return $result;
	}
	
	function fetch_row($result='')
	{
		if($result=='')
		{
			$result		= $this->last_result;
		}
	
		$r				=  $result;
		
		return $r;
	}
	
	function fetch_result($query)
	{
		$query_result	= $this->query($query);
		$result			= $this->fetch_row($query_result);
		$result['result_num_rows_returned']= $this->get_num_rows($query_result);
		
		return $result;
	}
	
	function get_num_rows($result='')
	{
		if($result=='')
		{
			$result		= $this->last_result;
		}
	
		$r				= count($result);
	
		return $r;
	}
	
	function insert($tbl,$vals=array())
	{
		foreach($vals as $vid => $vtxt)
		{
			if($this->started!=1)
			{
				$values_used	= $vid;
				$actual_values	= "'{$vtxt}'";
				$this->started	= 1;
			}
			else {
				$values_used  .= ",{$vid}";
				$actual_values.= ",'{$vtxt}'";
			}
		}

		// reset variables
		$this->started			= 0;

		$query_to_run			= "INSERT INTO {$tbl} ({$values_used}) VALUES({$actual_values})";

		$query					= $this->query($query_to_run);
		
		return $query;
	}
	
	function get_version()
	{
		return $this->libversion;
	}
	
	function mysql_error($sql_query,$errno,$errmsg)
	{
		global $icebb,$config;
	
		$time					= date('r');
	
		@mail($config['admin_email'],'IceBB mySQL error',"Time: {$time}\n\nError ({$errno}): {$errmsg}\n\nQuery: {$sql_query}",'From: noreply@noexist.com');
	
		echo <<<EOF
<html>
<head>
<title>IceBB mySQL error</title>
<script type='text/javascript'>
<!--
function _getbyid(id)
{
	item=null;

	if(document.getElementById)
	{
		item=document.getElementById(id);
	}
	else if(document.all)
	{
		item=document.all[id];
	}
	else if(document.layers)
	{
		item=document.layers[id];
	}
	
	return item;
}

function _toggle_view(id)
{
	item		= _getbyid(id);

	if(item.style.display=='none')
	{
		item.style.display='';
	}
	else {
		item.style.display='none';
	}
}
//-->
</script>
</head>
<body bgcolor='#ffffff'>
<b>Sorry, IceBB has encountered an error.</b><br />
<small>IceBB encountered a mySQL error. An administrator has been notified of this problem and it will be resolved shortly.<br /><br /><a href="javascript:_toggle_view('sql_error_msg')">Details</a></small>
<div id='sql_error_msg' style='display:none'>
<form action='' onsubmit='return false'>
<textarea rows='15' cols='50' name='sqlerror'>Query: {$sql_query}

Error Number: {$errno}
Error Message: {$errmsg}</textarea>
</form>
</div>
</body>
</html>
EOF;
		
		exit();
	}
}
?>