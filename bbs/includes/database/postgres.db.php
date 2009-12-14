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
// PostgreSQL class
// $Id$
//******************************************************//

/**
 * The IceBB PostgreSQL database driver class
 *
 * @package		IceBB
 * @version		0.9
 * @date		October 17, 2005
 */
class db_postgres
{
	var $queries			= 0;
	var $total_time			= 0;
	var $debug_html;
	var $shutdown_queries	= array();
	
	var $host;
	var $user;
	var $password;
	var $database;
	var $prefix;
	
	var $pgsql;

	/**
	 * Constructor
	 *
	 * Connects to the DB
	 *
	 * @return		resource		the PostgreSQL connection
	 */
	function postgres()
	{
		global $config;
	
		$this->database		= $config['db_database'];
		$this->prefix		= empty($config['db_prefix']) ? 'icebb_' : $config['db_prefix'];
	
		$this->pgsql		= @pg_connect("host={$config['db_host']} user={$config['db_user']} password={$config['db_pass']} dbname={$config['db_database']}") or $this->pgsql_error("Connect",pg_last_error());

		return $this->pgsql;
	}
	
	/**
	 * Runs a query
	 *
	 * @param		string		$sql		The SQL query to run
	 * @param		boolean		$shutdown	Run this query at shutdown?
	 * @return		resource	The result of the query
	 */
	function query($sql,$shutdown=0)
	{
		global $icebb,$timer;
	
		$timer_id				= md5(uniqid(microtime()));
	
		// fix up the prefix
		if(!empty($icebb->config['db_prefix']) && $icebb->config['db_prefix']!='icebb_')
		{
			//$sql				= preg_replace("/\sicebb_(\S+?)([\s\.,]|$)/","{$icebb->config['db_prefix']}\\1\\2",$sql);
			$sql = preg_replace('#(?<=\s)icebb_(?=\S)#', $icebb->config['db_prefix'], $sql);
		}
	
		if($_GET['debug']		== '1')
		{
			$timer->start($timer_id);
		}
		
		if($shutdown			== 1)
		{
			// it's not fancy, but it gets the job done, eh?
			register_shutdown_function(create_function('',"pg_query(\"{$sql}\");"));
		}
		else {
			$result				= pg_query($sql);
		
			if($result===false)
			{
				$this->pgsql_error($sql,pg_last_error());
			}
		}
		
		if($_GET['debug']=='1')
		{
			$query_time			= $timer->stop($timer_id);
		
			$this->total_time	= $this->total_time+$query_time;
		
			$query_info			= @pg_fetch_assoc(pg_query("EXPLAIN {$sql}"));
			
			$keys_and_stuff		= "";
			if($shutdown		== 1)
			{
				$keys_and_stuff.= "<tr><td class='col2' style='text-align:center' colspan='2'>Shutdown Query</td></tr>";
			}
			if(!empty($query_info['table']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Table:</td><td class='col1'>{$query_info['table']}</td></tr>";
			}
			if(!empty($query_info['type']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Type:</td><td class='col1'>{$query_info['type']}</td></tr>";
			}
			if(!empty($query_info['possible_keys']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Possible keys:</td><td class='col1'>{$query_info['possible_keys']}</td></tr>";
			}
			if(!empty($query_info['key']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Key:</td><td class='col1'>{$query_info['key']}</td></tr>";
			}
			if(!empty($query_info['key_len']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Key Length:</td><td class='col1'>{$query_info['key_len']}</td></tr>";
			}
			if(!empty($query_info['ref']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Reference:</td><td class='col1'>{$query_info['ref']}</td></tr>";
			}
			if(!empty($query_info['rows']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Rows returned:</td><td class='col1'>{$query_info['rows']}</td></tr>";
			}
			if(!empty($query_info['extra']))
			{
				$keys_and_stuff.= "<tr><td class='col2'>Extra:</td><td class='col1'>{$query_info['extra']}</td></tr>";
			}
		
			$this->debug_html   .= <<<EOF
	<tr>
		<th colspan='2'>
			{$sql}
		</th>
	</tr>
	{$keys_and_stuff}
	<tr>
		<td class='col2' width='40%'>
			Time:
		</td>
		<td class='col1'>
			{$query_time}sec
		</td>
	</tr>

EOF;
		}
	
		// should we really count the query if it's run at shutdown? I don't think so :P 
		if($shutdown		   != 1)
		{
			$this->queries++;
		}
		$this->queries_inc_shutdown++;
		
		$this->last_result		= $result;
		
		return $result;
	}
	
	/**
	 * Fetches the result as an associative array
	 *
	 * @param		resource	The SQL query result
	 * @return		array		Associative array of the result
	 */
	function fetch_row($result='')
	{
		if($result=='')
		{
			$result		= $this->last_result;
		}
	
		$r				= @pg_fetch_assoc($result);	
		return $r;
	}
	
	/**
	 * Runs a query and fetches the result
	 *
	 * @param		string		$query		The query to run
	 * @return		array		An associative array of the result
	 */
	function fetch_result($query)
	{
		$query_result	= $this->query($query);
		$result			= $this->fetch_row($query_result);
		$result['result_num_rows_returned']= $this->get_num_rows($query_result);
		
		return $result;
	}
	
	/**
	 * Gets the number of rows returned
	 *
	 * @param		resource	The SQL query result
	 * @return		int			Number of rows
	 */
	function get_num_rows($result='')
	{
		if($result=='')
		{
			$result		= $this->last_result;
		}
	
		$r				= pg_num_rows($result);
	
		return $r;
	}
	
	/**
	 * Inserts an array ($vals) into a table ($tbl)
	 *
	 * @param		string		$tbl		The table to insert into
	 * @param		array		$vals		The array of things to insert
	 * @return		resource	The SQL query result
	 */
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
	
	/**
	 * Same as mysql::insert(), except runs on shutdown
	 *
	 * @param		string		$tbl		The table to insert into
	 * @param		array		$vals		The array of things to insert
	 * @return		resource	The SQL query result
	 */
	function insert_shutdown($tbl,$vals=array())
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

		$query					= $this->query($query_to_run,1);
		
		return $query;
	}
	
	/**
	 * Gets the ID of the last inserted row
	 *
	 * @return		int			Last inserted row ID
	 */
	function get_insert_id()
	{
		return pgsql_insert_id();
	}
	
	/**
	 * Gets the MySQL version
	 *
	 * @param		boolean		Display the version as user-readable or not?
	 * @return		int			PostgreSQL Version
	 */
	function get_version($friendly=0)
	{
		global $icebb;
		
		$version				= pg_version($this->pgsql);
		
		$version				= $version['client'];
		$version				= explode('.',$version);
		
		$version[1]				= isset($version[1]) ? $version[1] : 00;
		$version[2]				= isset($version[2]) ? $version[2] : 00;
		
		$this->pgsql_version	= sprintf('%d%02d%02d',$version[0],$version[1],intval($version[2]));
		$this->pgsql_version	= (int)$this->pgsql_version;
		
		if($friendly==1)
		{
			$ret				= $version['client'];
		}
		else {
			$ret				= $this->pgsql_version;
		}
		
		return $ret;
	}
	
	/**
	 * Gets a list of tables in a database
	 */
	function list_tables($dbname)
	{
		return pg_list_tables($dbname);
	}
	
	/**
	 * Close connection
	 * @return		boolean		Did the command succeed? 
	 */
	function close()
	{
		return pg_close();
	}
	
	/**
	 * Runs queries queued for shutdown
	 */
	function on_shutdown()
	{
		foreach($this->shutdown_queries as $query)
		{
			pg_query($query);
		}
	}
	
	/**
	 * Escapes the string so it's safe
	 */
	function escape_string($string)
	{
		return pg_escape_string($string);
	}
	
	/**
	 * Displays a MySQL error message
	 *
	 * @param		string		$sql_query		The SQL query
	 * @param		int			$errno			The error number
	 * @param		string		$errmsg			The error message
	 */
	function pgsql_error($sql_query,$errno,$errmsg)
	{
		global $icebb,$config,$error_handler;
	
		$time					= date('r');
		$time2					= gmdate('m/d/Y H:i');
	
		//@mail($config['admin_email'],'IceBB mySQL error',"Time: {$time}\n\nError ({$errno}): {$errmsg}\n\nQuery: {$sql_query}",'From: noreply@noexist.com');
	
		$fh							= @fopen('uploads/pgsql_error_log.txt','a');
		@chmod('uploads/pgsql_error_log.txt',0777);
		@fwrite($fh,"[{$time2}]         Error: {$errmsg}\r\n");
		@fwrite($fh,"                           Query: {$sql_query}\r\n\r\n------------------\r\n\r\n");
		
		$errorcount_today			= 0;
		$file_contents				= @file_get_contents('uploads/pgsql_error_log.txt');
		$mysql_errors				= explode("\r\n\r\n------------------\r\n\r\n",$file_contents);
		$array_broked_errors		= explode("\r\n\r\n------------------\r\n\r\n[ADMIN_NOTIFY_BREAK]\r\n\r\n------------------\r\n\r\n",$file_contents);
		foreach($array_broked_errors as $erv)
		{
			$broked_errors			= explode("\r\n\r\n------------------\r\n\r\n",$erv);
		}
		
		foreach($broked_errors as $err)
		{
			if(substr($err,1,10)==gmdate('m/d/Y'))
			{
				$errorcount_today++;
			}
		}
		
		if($errorcount_today>=10)
		{
			$extra_details			= "\r\nThe administrator has been notified because {$errorcount_today} errors have occured today since the last e-mail.";

			$url					= "http://{$_SERVER['SERVER_NAME']}";
			$url				   .= $_SERVER['SERVER_PORT']=='80' ? '' : ":{$_SERVER['SERVER_PORT']}";
			$url				   .= preg_replace('`(index\.php|admin\.php)`','',$_SERVER['PHP_SELF']);
			@mail($config['admin_email'],'IceBB PostgreSQL error',"Time: {$time}\r\nThis message is to inform you that at least 10 PostgreSQL errors have occurred today. Please check the PostgreSQL error log at {$url}uploads/pgsql_error_log.txt for details on these errors.",'From: noreply@noexist.com');

			@fwrite($fh,"[ADMIN_NOTIFY_BREAK]\r\n\r\n------------------\r\n\r\n");
		}
		
		@fclose($fh);
		
		$errfile			= str_replace(@getcwd(),'',__FILE__);
		$errstr				= $errmsg;
		
		$errline			= 1;
		
		$pos				= strpos($errstr," near '");
		if($pos			   !== false)
		{
			$text_found		= substr($errstr,$pos+7,strlen($errstr)-$pos-7);
			$pos2			= strpos($sql_query,$text_found);
			
			if($pos2	   !== false)
			{
				$pos_end	= $pos2+strlen($text_found);
			
				$coded		= substr($sql_query,0,$pos2);
				$coded	   .= "<span style='background-color:#ffffcc;white-space:pre;'>{$text_found}</span>";
				$coded	   .= substr($sql_query,$pos_end+1,strlen($sql_query)-$pos_end-1);
			}
		}
		
		if(empty($coded))
		{
			$coded			= $sql_query;
		}
		
		$code_pre			= preg_replace("`\t`","    ",$coded);
		$code2				= "<pre>";
		$code2			   .= $coded;
		$code2			   .= "</code>";

		$err_extra['Query']		= $code2;

		if(is_callable(array($error_handler,'__error')))
		{
			$error_handler->show_code_snippet= 0;
			$error_handler->__error(SQL_ERROR,$errstr,__FILE__,__LINE__,$err_extra);
			$error_handler->show_code_snippet= 1;
		}
		else {
			die("PostgreSQL error: {$errstr}");
		}
		
		exit();
	}
}
?>