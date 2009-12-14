<?php
require('global.php');

class skin_sql
{
	function skin_sql()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function display($main)
	{
		global $icebb;
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
{$main}

<div class='borderwrap'>
	<h3>Run a query</h3>
	<div class='Subtitle'>All actions will be logged!</div>
	<form action='{$icebb->base_url}' method='post'>
	<input type='hidden' name='act' value='sql' />
	<input type='hidden' name='func' value='runquery' />
	<table width='100%' cellpadding='2' cellspacing='0' border='0'>
		<tr class=''>
			<td style='text-align:center'>
				<textarea name='query' rows='5' cols='50'></textarea>
			</td>
		</tr>
		<tr class='row2'>
			<td style='text-align:center'>
				<input type='submit' value='Run Query' class='button' />
			</td>
		</tr>
	</table>
</form>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
	
	function database_display($dbname,$letables,$logs)
	{
		global $icebb;
		
		if(count($logs))
		{
			foreach($logs as $log)
			{
				$time			= date('n/d/Y g:i A',$log['time']);
				$sql_logs	   .= <<<EOF
				<tr>
					<td class='row2'>{$time}</td>
					<td class='row1'>{$log['user']}</td>
					<td class='row2'>{$log['ip']}</td>
					<td class='row1'><div style='width:100%;display:block;overflow:auto'>{$log['action']}</div></td>
				</tr>
EOF;
			}
		}
		else {
			$sql_logs .= <<<EOF
				<tr>
					<td class='row2' colspan='4'>There are no logs in the database.</td>
				</tr>
EOF;
		}
		
		foreach($letables as $tbl)
		{
			$tables	    .= "<tr><td class='row2'>{$tbl}</td></tr>\n";
		}
		
		$code			.= <<<EOF
<div class='borderwrap'>
	<h3>{$dbname}</h3>
	<table width='100%' cellpadding='2' cellspacing='1'>
{$tables}
	</table>
</div>

<div class='borderwrap'>
	<h3>SQL Logs</h3>
	<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<th>Date</th>
		<th>Username</th>
		<th>IP</th>
		<th>Query</th>
	</tr>
{$sql_logs}
	<tr>
		<td class='buttonstrip' colspan='4'>
			<a href='{$icebb->base_url}act=sql&amp;func=logs'>Show all</a>
		</td>
	</tr>
	</table>
</div>

EOF;

		return $code;
	}
	
	function table_overview($table)
	{
		global $icebb;
		
		$code		   .= <<<EOF

EOF;
		
		return $code;
	}
	
	
	function do_logs($logs)
	{
		global $icebb;
		
		foreach($logs as $log)
		{
			$time			= date('n/d/Y g:i A',$log['time']);
			$sql_logs	   .= <<<EOF
			<tr>
				<td class='row2'>{$time}</td>
				<td class='row1'>{$log['user']}</td>
				<td class='row2'>{$log['ip']}</td>
				<td class='row1'><div style='width:100%;display:block;overflow:auto'>{$log['action']}</div></td>
			</tr>
EOF;
		}
		
		$code			.= <<<EOF
<div class='borderwrap'>
	<h3>SQL Logs</h3>
	<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<th>Date</th>
		<th>Username</th>
		<th>IP</th>
		<th>Query</th>
	</tr>
{$sql_logs}
	</table>
</div>

EOF;

		return $code;
	}
}
?>