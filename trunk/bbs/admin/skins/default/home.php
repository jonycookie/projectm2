<?php
require('global.php');

class skin_home
{
	function home()
	{
		global $icebb;
		
		//$this->global		= new skin_global;
	}

	function display($server_info=array(), $notes='', $show_updates = true, $recent_actions='')
	{
		global $icebb;
		
		if($show_updates)
		{
			$updates		= <<<EOF
<div id='acc_messages'>
	<div class='loading_image' style='padding-top:36px;text-align:center'>
		<img src='skins/default/images/loading.gif' alt='' />
	</div>
</div>

<script type='text/javascript'>
// <![CDATA[
new Ajax.Updater({success : 'acc_messages'}, '{$icebb->base_url}get_updates');
// ]]>
</script>

EOF;
		}
		
		$code				= skin_global::header();
		$code			   .= <<<EOF
<table width='100%' cellpading='0' cellspacing='0' border='0'>
	<tr>
		<td width='50%' valign='top'>
			<form action='index.php' method='post'>
			<input type='hidden' name='act' value='home' />
			<input type='hidden' name='save_notes' value='1' />
			<input type='hidden' name='s' value='{$icebb->adsess['asid']}' />
			<div class='borderwrap'>
				<h3>Message Box</h3>
				<div style='text-align:center'>
					<textarea name='message_box' rows='5' cols='30'  id='notes_box'>{$notes}</textarea>
					<input type='submit' value='Save' class='button' />
				</div>
			</div>
			</form>
		</td>
		<td valign='top'>
			{$updates}
			{$messages}
		</td>
	</tr>
	<tr>
		<td valign='top'>
			<div class='borderwrap'>
				<h3>Quick Tools</h3>
				<table width='100%' cellpadding='2' cellspacing='1' border='0'>
					<tr>
						<td class='row2' width='50%'>
							<strong>Edit member:</strong>
						</td>
						<td class='row1'>
							<form action='{$icebb->base_url}act=users' method='post'>
							<input type='hidden' name='func' value='search' />
							<input type='hidden' name='search_how' value='co' />
							<input type='text' name='username' value='' class='textbox' size='20' />
							<input type='submit' value='Go' class='button' />
							</form>
						</td>
					</tr>
					<tr>
						<td class='row2'>
							<strong>Find IP:</strong>
						</td>
						<td class='row1'>
							<form action='{$icebb->base_url}act=users&amp;func=iptools' method='post'>
							<input type='text' name='ipaddr' value='' class='textbox' size='20' />
							<input type='submit' value='Go' class='button' />
							</form>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td valign='top'>
			<div class='borderwrap'>
				<h3>Server Information</h3>
				<table width='100%' cellpadding='2' cellspacing='1' border='0'>
					<tr>
						<td class='row2' width='50%'>
							<strong>IceBB Version:</strong>
						</td>
						<td class='row1'>
							{$server_info['icebb_ver']}
						</td>
					</tr>
					<tr>
						<td class='row2'>
							<strong>PHP Version:</strong>
						</td>
						<td class='row1'>
							<a href='{$icebb->base_url}act=home&amp;phpinfo=1'>{$server_info['php_ver']}</a>
						</td>
					</tr>
					<tr>
						<td class='row2'>
							<strong>MySQL Version:</strong>
						</td>
						<td class='row1'>
							{$server_info['mysql_ver']}
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>

{$recent_actions}

EOF;
		$code			   .= skin_global::footer();
		
		return $code;
	}
	
	function registration_info($license,$expired=false)
	{
		global $icebb;
		
		if($expired)
		{
			$code			= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
	<h3 class='importante'>License Expired</h3>
	<p>The license key for this board has expired. You may still continue to use this board, but you won't get
	priority support or a registered message. To remove this message, simply remove customer.php from your board
	directory. To renew your license, please visit the customer area.</p>
	<a href='http://icebb.net/customer/' target='_blank'>Customer Area</a>
</div>

EOF;
		}
		else {
			$code			= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
	<h3>Registered Board</h3>
	<p>Thank you for supporting IceBB. If you need to change this license or need support, please visit the
	customer area below.</p>
	<a href='http://icebb.net/customer/' target='_blank'>Customer Area</a>
</div>

EOF;
		}
		
		return $code;
	}
	
	function updates_box($update)
	{
		global $icebb;
		
		$code			   .= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
	<h3 class='importante'>New Version Available!</h3>
	{$update}<br />
	<a href='http://icebb.net/downloads/' target='_blank'><strong>Download Now</strong></a>
</div>

EOF;

		return $code;
	}
	
	function security_updates($updates)
	{
		global $icebb;
		
		$code			   .= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
	<h3 class='importante'>Security Warning</h3>
	<div class='Subtitle'>You can safely ignore these if you have already applied the patches.</div>
	{$updates}
</div>

EOF;

		return $code;
	}
	
	function message_box($msg)
	{
		global $icebb;
		
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>Message</h3>
	{$msg}
</div>

EOF;

		return $code;
	}
	
	function recent_actions($actions)
	{
		global $icebb;
		
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>Recent Actions</h3>
	<table width='100%' cellpadding='2' cellspacing='1'>
	<tr>
		<th>Date</th>
		<th>Username</th>
		<th>IP</th>
		<th>Action</th>
	</tr>

EOF;

		foreach($actions as $log)
		{
			$time			= date('n/d/Y g:i A',$log['time']);
			$code		   .= <<<EOF
			<tr>
				<td class='row2'>{$time}</td>
				<td class='row1'>{$log['user']}</td>
				<td class='row2'>{$log['ip']}</td>
				<td class='row1'>{$log['action']}</td>
			</tr>
EOF;
		}

		$code			   .= <<<EOF
	</table>
</div>

EOF;

		return $code;
	}
}
?>
