<?php
!function_exists('readover') && exit('Forbidden');
@include(D_P.'data/cache/ftp_config.php');
set_time_limit(1000);
Class FTP {
	var $sock;
	var $resp;

	function __construct($ftp_server,$ftp_port,$ftp_user,$ftp_pass,$ftp_dir=''){
		$this->resp = "";
		if($this->connect($ftp_server,$ftp_port)){
			$this->login($ftp_user,$ftp_pass);
			if($ftp_dir){
				$this->cwd($ftp_dir);
			}
		}
	}

	function FTP($ftp_server,$ftp_port,$ftp_user,$ftp_pass,$ftp_dir='') {
		$this->__construct($ftp_server,$ftp_port,$ftp_user,$ftp_pass,$ftp_dir);
	}
	function connect($ftp_server,$ftp_port) {
		$this->sock = @fsockopen($ftp_server, $ftp_port, $errno, $errstr, 30);
		if(!$this->sock || !$this->check()){
			Showmsg('ftp_connect_failed');
		}
		return true;
	}

	function login($user,$pass){
		$this->command("USER",$user);
		if(!$this->check()){
			Showmsg('ftp_user_failed');
		}
		$this->command("PASS",$pass);
		if(!$this->check()){
			Showmsg('ftp_pass_failed');
		}
		return true;
	}

	function pwd(){
		$this->command("PWD");
		if(!$this->check()){
			Showmsg("Error : PWD command failed");
		}

		return preg_replace("/^[0-9]{3} \"(.+)\" .+\r\n/", "\\1", $this->resp);
	}

	function cwd($pathname){
		$this->command("CWD", $pathname);
		$response = $this->check();
		if(!$response){
			Showmsg('ftp_cwd_failed');
		}
		return $response;
	}

	function mkd($pathname){
		$this->command("MKD", $pathname);
		if($this->check()){
			$this->site("CHMOD 0777 $pathname");
		}
		return true;
	}

	function type($mode=''){
		if($mode){
			$type = "I"; //Binary mode
		} else{
			$type = "A"; //ASCII mode
		}
		$this->command("TYPE", $type);
		$response = $this->check();
		if(!$response){
			Showmsg("Error : TYPE command failed");
		}
		return true;
	}

	function size($pathname){
		$this->command("SIZE", $pathname);
		if(!$this->check()){
			Showmsg("Error : SIZE command failed");
		}

		return preg_replace("/^[0-9]{3} ([0-9]+)\r\n/", "\\1", $this->resp);
	}

	function upload($filename,$source,$mode=''){
		if(strpos($source,'..')!==false || strpos($source,'.php.')!==false || eregi("\.php$",$source)){
			exit('illegal file type!');
		}
		if($GLOBALS['filedir']){
			$this->mkd($GLOBALS['filedir']);
		}
		if(!$fp = @fopen($filename, "r")){
			P_unlink($filename);
			Showmsg("Error : Cannot read file \"".$filename."\"");
		}

		$this->type($mode);
		if(!($string = $this->pasv())){
			return false;
		}
		$this->command("STOR", $source);

		$sock_data = $this->open_data_connection($string);
		if(!$sock_data || !$this->check()){
			P_unlink($filename);
			Showmsg("Error : Cannot connect to remote host");
		}

		while(!feof($fp)){
			fputs($sock_data, fread($fp, 4096));
		}
		fclose($fp);

		$this->close_data_connection($sock_data);

		$response = $this->check();
		if(!$response){
			P_unlink($filename);
			Showmsg("Error : PUT command failed");
		}else{
			$this->site("CHMOD 0777 $source");
		}

		return $this->size($source);
	}

	function delete($pathname){
		$this->command("DELE", $pathname);
		return $this->check();
	}

	function command($cmd,$arg = ""){
		if($arg != ""){
			$cmd = $cmd." ".$arg;
		}
		fputs($this->sock,$cmd."\r\n");

		return true;
	}

	function site($command){
		$this->command("SITE",$command);
		$response = $this->check();
		if(!$response){
			Showmsg("Error : SITE command failed");
		}
		return $response;
	}

	function check(){
		$this->resp = "";
		do {
			$res = fgets($this->sock, 512);
			$this->resp .= $res;
		} while(substr($res, 3, 1) != " ");

		if(!ereg("^[123]", $this->resp)) {
			return false;
		}
		return true;
	}

	function dir_exists($pathname){
		if(!$pathname) return false;
		$list = $this->nlist();
		
		if(in_array($pathname,$list)){
			return true;
		}
		return false;
	}

	function nlist($pathname = ""){
		if(!($string = $this->pasv())){
			return false;
		}

		$this->command("NLST", $pathname);
		
		$sock_data = $this->open_data_connection($string);

		if(!$sock_data || !$this->check()){
			Showmsg("Error : Cannot connect to remote host<br />Error : LIST command failed");
		}

		while(!feof($sock_data)){
			$sock_get = preg_replace("[\r\n]", "", fgets($sock_data, 512));
			if($sock_get && strpos($sock_get,".")===false){
				$list[] = $sock_get;
			}
		}

		$this->close_data_connection($sock_data);

		if(!$this->check()){
			Showmsg("Error : LIST command failed");
		}

		return $list;
	}

	function pasv(){
		$this->command("PASV");
		if(!$this->check()){
			Showmsg("Error : PASV command failed");
		}
		$ip_port = preg_replace("/^.+\s\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\)?.*\r\n$/i","\\1",$this->resp);
		return $ip_port;
	}

	function open_data_connection($ip_port){
		if(!ereg("[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+", $ip_port)){
			Showmsg("Error : Illegal ip-port format(".$ip_port.")");
		}

		$DATA = explode(",", $ip_port);
		$ipaddr = $DATA[0].".".$DATA[1].".".$DATA[2].".".$DATA[3];
		$port   = $DATA[4]*256 + $DATA[5];

		$data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);
		if(!$data_connection){
			Showmsg("Error : Cannot open data connection to ".$ipaddr.":".$port."<br />Error : ".$errstr." (".$errno.")");
		}

		return $data_connection;
	}

	function close_data_connection($sock){
		return fclose($sock);
	}

	function close(){
		$this->command("QUIT");
		if(!$this->check() || !fclose($this->sock)){
			Showmsg("Error : QUIT command failed");
		}
		return true;
	}
}
$ftp = new FTP($ftp_server,$ftp_port,$ftp_user,$ftp_pass,$ftp_dir);
?>