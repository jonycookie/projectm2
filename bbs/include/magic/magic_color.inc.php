<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: magic_color.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(submitcheck('usesubmit')) {

	if(empty($highlight_color)) {
		showmessage('magics_info_nonexistence');
	}

	$thread = getpostinfo($tid, 'tid', array('fid'));
	checkmagicperm($magicperm['forum'], $thread['fid']);
	magicthreadmod($tid);

	$db->query("UPDATE {$tablepre}threads SET highlight='$highlight_color', moderated='1' WHERE tid='$tid'", 'UNBUFFERED');
	$expiration = $timestamp + 86400;

	usemagic($magicid, $magic['num']);
	updatemagiclog($magicid, '2', '1', '0', $tid);
	updatemagicthreadlog($tid, $magicid, $magic['identifier'], $expiration);
	showmessage('magics_operation_succeed', 'magic.php?action=user');

}

function showmagic() {
	global $tid, $lang;
	magicshowtype($lang['option'], 'top');
	magicshowsetting($lang['target_tid'], 'tid', $tid, 'text');
	magicshowsetting($lang['CCK_color'], '', '', '<table border="0" cellspacing="0" cellpadding="0"><tr>
	<td><input type="radio" class="radio" name="highlight_color" value="1" checked="checked" /></td><td width="20" bgcolor="red">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="2" /></td><td width="20" bgcolor="orange">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="3" /></td><td width="20" bgcolor="yellow">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="4" /></td><td width="20" bgcolor="green">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="5" /></td><td width="20" bgcolor="cyan">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="6" /></td><td width="20" bgcolor="blue">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="7" /></td><td width="20" bgcolor="purple">&nbsp;</td>
	<td> &nbsp; <input type="radio" class="radio" name="highlight_color" value="8" /></td><td width="20" bgcolor="gray">&nbsp;</td>
	</tr></table>');
	magicshowtype('', 'bottom');
}

?>