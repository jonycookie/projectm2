<?php
!function_exists('readover') && exit('Forbidden');

class DB {
	var $query_num = 0;
	var $linkId = 0;
	var $charset;

	function DB($dbhost, $dbuser, $dbpwd, $dbname,$charset,$pconnect = 0) {
		$this->__construct($dbhost, $dbuser, $dbpwd, $dbname,$charset,$pconnect);
	}

	function __construct($dbhost, $dbuser, $dbpwd, $dbname,$charset,$pconnect = 0){
		$this->charset = $charset;
		$this->connect($dbhost, $dbuser, $dbpwd, $dbname, $pconnect);
	}

	function connect($dbhost, $dbuser, $dbpwd, $dbname, $pconnect = 0) {
		$this->linkId = $pconnect==0 ? @mysql_connect($dbhost, $dbuser, $dbpwd,true) : @mysql_pconnect($dbhost, $dbuser, $dbpwd);
		mysql_errno($this->linkId)!=0 && $this->halt("Connect($pconnect) to MySQL failed");
		if($this->server_info() > '4.1' && $this->charset){
			mysql_query("SET character_set_connection=".$this->charset.", character_set_results=".$this->charset.", character_set_client=binary",$this->linkId);
		}
		if($this->server_info() > '5.0'){
			mysql_query("SET sql_mode=''",$this->linkId);
		}
		if($dbname) {
			if (!@mysql_select_db($dbname,$this->linkId)){
				$this->halt('Cannot use database ');
			}
		}
	}

	function close() {
		return mysql_close($this->linkId);
	}

	function select_db($dbname){
		if (!@mysql_select_db($dbname,$this->linkId)){
			$this->halt('Cannot use database');
		}
	}

	function server_info(){
		return mysql_get_server_info($this->linkId);
	}

	function query($SQL,$method='') {
		$GLOBALS['very']['debug'] && writeover(D_P.'data/db_query.txt',"$SQL\t$GLOBALS[timestamp]\n",'ab');
		$GLOBALS['_pre']=='cms_' or $SQL=str_replace('cms_',$GLOBALS['_pre'],$SQL);
		if($method=='U_B' && function_exists('mysql_unbuffered_query')){
			$query = mysql_unbuffered_query($SQL,$this->linkId);
		}else{
			$query = mysql_query($SQL,$this->linkId);
		}
		$GLOBALS['queryNum']++;
		$this->query_num++;

		if (!$query)  $this->halt('Query Error: ' . $SQL);
		return $query;
	}

	function get_one($SQL){
		$GLOBALS['very']['debug'] && writeover(D_P.'data/db_query.txt',"$SQL\t$GLOBALS[timestamp]\n",'ab');
		$query=$this->query($SQL,'U_B');
		$rs =& mysql_fetch_array($query, MYSQL_ASSOC);
		return $rs;
	}

	function pw_update($SQL_1,$SQL_2,$SQL_3){
		$rt=$this->get_one($SQL_1);
		if($rt){
			$this->update($SQL_2);
		} else{
			$this->update($SQL_3);
		}
	}

	function update($SQL) {
		$GLOBALS['very']['debug'] && writeover(D_P.'data/db_query.txt',"$SQL\t$GLOBALS[timestamp]\n",'ab');
		$GLOBALS['_pre']=='cms_' or $SQL=str_replace('cms_',$GLOBALS['_pre'],$SQL);
		if($GLOBALS['db_lp']==1){
			if(substr($SQL,0,7)=='REPLACE'){
				$SQL=substr($SQL,0,7).' LOW_PRIORITY'.substr($SQL,7);
			} else{
				$SQL=substr($SQL,0,6).' LOW_PRIORITY'.substr($SQL,6);
			}
		}
		if(function_exists('mysql_unbuffered_query')){
			$query = mysql_unbuffered_query($SQL,$this->linkId);
		}else{
			$query = mysql_query($SQL,$this->linkId);
		}
		$GLOBALS['queryNum']++;
		$this->query_num++;

		if (!$query)  $this->halt('Update Error: ' . $SQL);
		return $query;
	}
	
	function get_value($SQL,$result_type = MYSQL_NUM,$field=0){
		$query = $this->query($SQL,'U_B');
		$rt =& mysql_fetch_array($query,$result_type);
		if (isset($rt[$field])) {
			return $rt[$field];
		}
		return false;
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function affected_rows() {
		return mysql_affected_rows($this->linkId);
	}

	function num_rows($query) {
		$rows = mysql_num_rows($query);
		return $rows;
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		$id = mysql_insert_id($this->linkId);
		return $id;
	}

	function halt($msg='') {
		global $very,$REQUEST_URI,$dbhost;
		$sqlerror = mysql_error($this->linkId);
		$sqlerrno = mysql_errno($this->linkId);
		$sqlerror = str_replace($dbhost,'dbhost',$sqlerror);
		ob_end_clean();
		($very['gzip'] == 1 && function_exists('ob_gzhandler')) ? ob_start('ob_gzhandler') : ob_start();
		echo "<html><head><title>$very[name]</title><style type='text/css'>P,BODY{FONT-FAMILY:tahoma,arial,sans-serif;FONT-SIZE:12px;}A { TEXT-DECORATION: none;}a:hover{ text-decoration: underline;}TD { BORDER-RIGHT: 1px; BORDER-TOP: 0px; FONT-SIZE: 12px; COLOR: #000000;}</style><body>\n\n";
		echo "<div style='border:1px solid #FF000;padding:10px;margin:auto'>$msg";
		echo "<br><br><b>The URL is</b>:<br>http://$_SERVER[HTTP_HOST]$REQUEST_URI";
		echo "<br><br><b>MySQL server error</b>:<br>$sqlerror  ( $sqlerrno )";
		echo "<br><br><b>You can get help in</b>:<br><a target=_blank href=http://www.phpwind.net><b>http://www.phpwind.net</b></a>";
		echo "</div>";
		exit;
	}

	/**
	 * 析构函数 PHP5
	 *
	 */
//	function __destruct()
//	{
//		$this->close();
//	}
}
?>