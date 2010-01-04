<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: moderate.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

cpheader();

if($action == 'modmembers') {

	//Table validating status: 0=Awaiting for moderation; 1=Invalidated; 2=Validated;
	if(!submitcheck('modsubmit') && !submitcheck('prunesubmit', 1)) {

		$query = $db->query("SELECT status, COUNT(*) AS count FROM {$tablepre}validating GROUP BY status");
		while($num = $db->fetch_array($query)) {
			$count[$num['status']] = $num['count'];
		}

		$sendemail = isset($sendemail) ? $sendemail : 1;
		$checksendemail = $sendemail ? 'checked' : '';

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $memberperpage;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}validating WHERE status='0'");
		$multipage = multi($db->result($query, 0), $memberperpage, $page, 'admincp.php?action=modmembers&sendemail=$sendemail');

		$vuids = '0';
		$members = '';
		$query = $db->query("SELECT m.uid, m.username, m.groupid, m.email, m.regdate, m.regip, v.message, v.submittimes, v.submitdate, v.moddate, v.admin, v.remark
			FROM {$tablepre}validating v, {$tablepre}members m
			WHERE v.status='0' AND m.uid=v.uid ORDER BY v.submitdate DESC LIMIT $start_limit, $memberperpage");
		while($member = $db->fetch_array($query)) {
			if($member['groupid'] != 8) {
				$vuids .= ','.$member['uid'];
				continue;
			}
			$member['regdate'] = gmdate("$dateformat $timeformat", $member['regdate'] + $timeoffset * 3600);
			$member['submitdate'] = gmdate("$dateformat $timeformat", $member['submitdate'] + $timeoffset * 3600);
			$member['moddate'] = $member['moddate'] ? gmdate("$dateformat $timeformat", $member['moddate'] + $timeoffset * 3600) : $lang['none'];
			$member['admin'] = $member['admin'] ? "<a href=\"space.php?action=viewpro&username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
			$members .= "<tr class=\"smalltxt\"><td class=\"altbg2\"><input class=\"radio\" type=\"radio\" name=\"mod[$member[uid]]\" value=\"invalidate\"> $lang[invalidate]<br /><input class=\"radio\" type=\"radio\" name=\"mod[$member[uid]]\" value=\"validate\" checked> $lang[validate]<br />\n".
				"<input class=\"radio\" type=\"radio\" name=\"mod[$member[uid]]\" value=\"delete\"> $lang[delete]<br /><input class=\"radio\" type=\"radio\" name=\"mod[$member[uid]]\" value=\"ignore\"> $lang[ignore]</td><td class=\"altbg1\"><b><a href=\"space.php?action=viewpro&uid=$member[uid]\" target=\"_blank\">$member[username]</a></b>\n".
				"<br />$lang[members_edit_regdate] $member[regdate]<br />$lang[members_edit_regip] $member[regip]<br />Email: $member[email]</td>\n".
				"<td class=\"altbg2\" align=\"center\"><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[message]</textarea></td>\n".
				"<td class=\"altbg1\">$lang[moderate_members_submit_times]: $member[submittimes]<br />$lang[moderate_members_submit_time]: $member[submitdate]<br />$lang[moderate_members_admin]: $member[admin]<br />\n".
				"$lang[moderate_members_mod_time]: $member[moddate]</td><td class=\"altbg1\"><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 95%; word-break: break-all\">$member[remark]</textarea></td></tr>\n";
		}

		if($vuids) {
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($vuids)", 'UNBUFFERED');
		}

		shownav('menu_moderate_modmembers');
		showtips('moderate_members_tips');

?>
<form method="post" action="admincp.php?action=modmembers">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['moderate_members_prune']?></td></tr>

<tr><td class="altbg1"><?=$lang['moderate_members_prune_submitmore']?></td>
<td align="right" class="altbg2"><input type="text" name="submitmore" size="40" value="5"></td></tr>

<tr><td class="altbg1"><?=$lang['moderate_members_prune_regbefore']?></td>
<td align="right" class="altbg2"><input type="text" name="regbefore" size="40" value="30"></td></tr>

<tr><td class="altbg1"><?=$lang['moderate_members_prune_modbefore']?></td>
<td align="right" class="altbg2"><input type="text" name="modbefore" size="40" value="15"></td></tr>

<tr><td class="altbg1"><?=$lang['moderate_members_prune_regip']?></td>
<td align="right" class="altbg2"><input type="text" name="regip" size="40"></td></tr>

</table><br />
<center><input class="button" type="submit" name="prunesubmit" value="<?=$lang['submit']?>"></center>
</form><br />

<form method="post" action="admincp.php?action=modmembers">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['moderate_members']?></td></tr>
<tr class="category"><td colspan="5">
<table cellspacing="0" cellpadding="0" width="100%"><tr><td style="border:none"><input class="button" type="button" value="<?=$lang['moderate_all_invalidate']?>" onclick="checkalloption(this.form, 'invalidate')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_validate']?>" onclick="checkalloption(this.form, 'validate')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')">
</td><td align="right" style="border:none"><input class="checkbox" type="checkbox" name="sendemail" value="1" <?=$checksendemail?>> <?=$lang['moderate_members_email']?></td></tr></table>
</td></tr>
<tr align="center" class="header"><td width="10%"><?=$lang['operation']?></td><td width="25%"><?=$lang['members_edit_info']?></td><td width="20%"><?=$lang['moderate_members_message']?></td><td width="25%"><?=$lang['moderate_members_info']?></td><td width="20%"><?=$lang['moderate_members_remark']?></td></tr>
<?=$members?>
</table>

<?=$multipage?>
<br /><center>
<input class="button" type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} elseif(submitcheck('modsubmit')) {

		$moderation = array('invalidate' => array(), 'validate' => array(), 'delete' => array(), 'ignore' => array());

		$uids = 0;
		if(is_array($mod)) {
			foreach($mod as $uid => $action) {
				$uid = intval($uid);
				$uids .= ','.$uid;
				$moderation[$action][] = $uid;
			}
		}

		$members = array();
		$uidarray = array(0);
		$query = $db->query("SELECT v.*, m.uid, m.username, m.email, m.regdate FROM {$tablepre}validating v, {$tablepre}members m
			WHERE v.uid IN ($uids) AND m.uid=v.uid AND m.groupid='8'");
		while($member = $db->fetch_array($query)) {
			$members[$member['uid']] = $member;
			$uidarray[] = $member['uid'];
		}

		$uids = implode(',', $uidarray);
		$numdeleted = $numinvalidated = $numvalidated = 0;

		if(!empty($moderation['delete']) && is_array($moderation['delete'])) {
			$deleteuids = '\''.implode('\',\'', $moderation['delete']).'\'';
			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($deleteuids) AND uid IN ($uids)");
			$numdeleted = $db->affected_rows();

			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($deleteuids) AND uid IN ($uids)");
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($deleteuids) AND uid IN ($uids)");
		} else {
			$moderation['delete'] = array();
		}

		if(!empty($moderation['validate']) && is_array($moderation['validate'])) {
			$newgroupid = $db->result_first("SELECT groupid FROM {$tablepre}usergroups WHERE creditshigher<=0 AND 0<creditslower LIMIT 1");
			$validateuids = '\''.implode('\',\'', $moderation['validate']).'\'';
			$db->query("UPDATE {$tablepre}members SET adminid='0', groupid='$newgroupid' WHERE uid IN ($validateuids) AND uid IN ($uids)");
			$numvalidated = $db->affected_rows();

			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($validateuids) AND uid IN ($uids)");
		} else {
			$moderation['validate'] = array();
		}

		if(!empty($moderation['invalidate']) && is_array($moderation['invalidate'])) {
			foreach($moderation['invalidate'] as $uid) {
				$numinvalidated++;
				$db->query("UPDATE {$tablepre}validating SET moddate='$timestamp', admin='$discuz_user', status='1', remark='".dhtmlspecialchars($remark[$uid])."' WHERE uid='$uid' AND uid IN ($uids)");
			}
		} else {
			$moderation['invalidate'] = array();
		}

		if($sendemail) {
			foreach(array('delete', 'validate', 'invalidate') as $operation) {
				foreach($moderation[$operation] as $uid) {
					if(isset($members[$uid])) {
						$member = $members[$uid];
						$member['regdate'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $member['regdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
						$member['submitdate'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $member['submitdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
						$member['moddate'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
						$member['operation'] = $lang[$operation];
						$member['remark'] = $remark[$uid] ? dhtmlspecialchars($remark[$uid]) : $lang['none'];

						sendmail("$member[username] <$member[email]>", 'moderate_member_subject', 'moderate_member_message');
					}
				}
			}
		}

		cpmsg('moderate_members_succeed', "admincp.php?action=modmembers&page=$page");

	} elseif(submitcheck('prunesubmit', 1)) {

		$sql = '1';
		$sql .= $submitmore ? " AND v.submittimes>'$submitmore'" : '';
		$sql .= $regbefore ? " AND m.regdate<'".($timestamp - $regbefore * 86400)."'" : '';
		$sql .= $modbefore ? " AND v.moddate<'".($timestamp - $modbefore * 86400)."'" : '';
		$sql .= $regip ? " AND m.regip LIKE '$regip%'" : '';

		$query = $db->query("SELECT v.uid FROM {$tablepre}validating v, {$tablepre}members m
			WHERE $sql AND m.uid=v.uid AND m.groupid='8'");

		$membernum = $db->num_rows($query);

		if(!$confirmed) {
			cpmsg('members_delete_confirm', "admincp.php?action=modmembers&submitmore=".rawurlencode($submitmore)."&regbefore=".rawurlencode($regbefore)."&regip=".rawurlencode($regip)."&prunesubmit=yes", 'form');
		} else {
			$uids = 0;
			while($member = $db->fetch_array($query)) {
				$uids .= ','.$member['uid'];
			}

			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
			$numdeleted = $db->affected_rows();

			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)");
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($uids)");

			cpmsg('members_delete_succeed');
		}
	}

} else {

	require_once DISCUZ_ROOT.'./include/forum.func.php';
	require_once DISCUZ_ROOT.'./include/post.func.php';

	$modfid = !empty($modfid) ? intval($modfid) : 0;

	$fids = 0;
	$recyclebins = $forumlist = array();
	if($adminid == 3) {
		$query = $db->query("SELECT m.fid, f.name, f.recyclebin FROM {$tablepre}moderators m LEFT JOIN {$tablepre}forums f ON f.fid=m.fid  WHERE m.uid='$discuz_uid'");
		while($forum = $db->fetch_array($query)) {
			$fids .= ','.$forum['fid'];
			$recyclebins[$forum['fid']] = $forum['recyclebin'];
			$forumlist[$forum['fid']] = strip_tags($forum['name']);
		}

		if(empty($forumlist)) {
			cpmsg('moderate_posts_no_access_all');
		} elseif($modfid && empty($forumlist[$modfid])) {
			cpmsg('moderate_posts_no_access_this');
		}

	} else {
		$query = $db->query("SELECT fid, name, recyclebin FROM {$tablepre}forums WHERE status>0 AND type<>'group'");
		while($forum = $db->fetch_array($query)) {
			$recyclebins[$forum['fid']] = $forum['recyclebin'];
			$forumlist[$forum['fid']] = $forum['name'];
		}
	}

	if($modfid) {
		$fidadd = array('fids' => "fid='$modfid'", 'and' => ' AND ', 't' => 't.', 'p' => 'p.');
	} else {
		$fidadd = $fids ? array('fids' => "fid IN ($fids)", 'and' => ' AND ', 't' => 't.', 'p' => 'p.') : array();
	}

	if(isset($filter) && $filter == 'ignore') {
		$displayorder = -3;
		$filteroptions = '<option value="normal">'.$lang['moderate_none'].'</option><option value="ignore" selected>'.$lang['moderate_ignore'].'</option>';
	} else {
		$displayorder = -2;
		$filter = 'normal';
		$filteroptions = '<option value="normal" selected>'.$lang['moderate_none'].'</option><option value="ignore">'.$lang['moderate_ignore'].'</option>';
	}

	$forumoptions = '<option value="all"'.(empty($modfid) ? ' selected' : '').'>'.$lang['moderate_all_fields'].'</option>';
	foreach($forumlist as $fid => $forumname) {
		$selected = $modfid == $fid ? ' selected' : '';
		$forumoptions .= '<option value="'.$fid.'" '.$selected.'>'.$forumname.'</option>'."\n";
	}

	require_once DISCUZ_ROOT.'./include/misc.func.php';
	$modreasonoptions = '<option value="">'.$lang['none'].'</option><option value="">--------</option>'.modreasonselect();

}

if($action == 'modthreads') {

	shownav('menu_moderate_modthreads');

	$validatedthreads = array();

	if(submitcheck('modsubmit')) {

		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		if(is_array($mod)) {
			foreach($mod as $tid => $action) {
				$moderation[$action][] = intval($tid);
			}
		}

		if($moderation['ignore']) {
			$ignoretids = '\''.implode('\',\'', $moderation['ignore']).'\'';
			$db->query("UPDATE {$tablepre}threads SET displayorder='-3' WHERE tid IN ($ignoretids) AND displayorder='-2'");
		}

		$threadsmod = 0;
		$pmlist = array();
		if($moderation['delete']) {
			$deletetids = '0';
			$recyclebintids = '0';
			$query = $db->query("SELECT tid, fid, authorid, subject FROM {$tablepre}threads WHERE tid IN ('".implode('\',\'', $moderation['delete'])."') AND displayorder='$displayorder' $fidadd[and]$fidadd[fids]");
			while($thread = $db->fetch_array($query)) {
				if($recyclebins[$thread['fid']]) {
					$recyclebintids .= ','.$thread['tid'];
				} else {
					$deletetids .= ','.$thread['tid'];
				}
				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
						'act' => 'modthreads_delete_',
						'authorid' => $thread['authorid'],
						'thread' =>  $thread['subject'],
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($recyclebintids) {
				$db->query("UPDATE {$tablepre}threads SET displayorder='-1', moderated='1' WHERE tid IN ($recyclebintids)");
				updatemodworks('MOD', $db->affected_rows());

				$db->query("UPDATE {$tablepre}posts SET invisible='-1' WHERE tid IN ($recyclebintids)");
				updatemodlog($recyclebintids, 'DEL');
			}

			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($deletetids)");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			}

			$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}polloptions WHERE tid IN ($deletetids)");
			$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}trades WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($deletetids)", 'UNBUFFERED');
		}

		if($moderation['validate']) {

			$forums = array();
			$validatetids = '\''.implode('\',\'', $moderation['validate']).'\'';

			$tids = $supe_pushtids = $comma = $comma2 = '';
			$authoridarray = $moderatedthread = array();
			$query = $db->query("SELECT t.fid, t.tid, t.authorid, t.subject, t.author, t.dateline, t.supe_pushstatus, ff.postcredits, ff.supe_pushsetting FROM {$tablepre}threads t
				LEFT JOIN {$tablepre}forumfields ff USING(fid)
				WHERE t.tid IN ($validatetids) AND t.displayorder='$displayorder' $fidadd[and]$fidadd[t]$fidadd[fids]");
			while($thread = $db->fetch_array($query)) {
				$supe_pushsetting = unserialize($thread['supe_pushsetting']);
				if($supe['status'] && $thread['supe_pushstatus'] == 0 && $supe_allowpushthread && $supe_pushsetting['status'] == '1') {
					$supe_pushtids .= $comma2.$thread['tid'];
					$comma2 = ',';
				}
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				if($thread['postcredits']) {
					updatepostcredits('+', $thread['authorid'], unserialize($thread['postcredits']));
				} else {
					$authoridarray[] = $thread['authorid'];
				}
				$forums[] = $thread['fid'];
				$validatedthreads[] = $thread;

				$pm = 'pm_'.$thread['tid'];
				if(isset($$pm) && $$pm <> '' && $thread['authorid']) {
					$pmlist[] = array(
							'act' => 'modthreads_validate_',
							'authorid' => $thread['authorid'],
							'tid' => $thread['tid'],
							'thread' => $thread['subject'],
							'reason' => dhtmlspecialchars($$pm)
							);
				}
			}


			if($supe_pushtids) {
				$db->query("UPDATE {$tablepre}threads SET supe_pushstatus='1' WHERE tid IN ($supe_pushtids)");
			}

			if($tids) {

				if($authoridarray) {
					updatepostcredits('+', $authoridarray, $creditspolicy['post']);
				}

				$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE tid IN ($tids)");
				$db->query("UPDATE {$tablepre}threads SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
				$threadsmod = $db->affected_rows();

				foreach(array_unique($forums) as $fid) {
					updateforumcount($fid);
				}

				updatemodworks('MOD', $threadsmod);
				updatemodlog($tids, 'MOD');

			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$threadsubject = $pm['thread'];
				$tid = intval($pm['tid']);
				sendpm($pm['authorid'], $pm['act'].'subject', $pm['act'].'message', $fromid = '', $from = '');
			}
		}

	}

	if(!empty($validatedthreads)) {
?>
<form id="topicadmin" name="topicadmin" method="POST" action="topicadmin.php" target="_blank">
<input type="hidden" name="tid" value="">
<input type="hidden" name="fid" value="">
<input type="hidden" name="action" value="">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['moderate_validate_list']?></td></tr>
<tr class="category" align="center"><td>Tid</td><td><?=$lang['subject']?></td><td><?=$lang['author']?></td><td><?=$lang['dateline']?></td><td><?=$lang['front_moderation']?></td></tr>
<?

		foreach($validatedthreads as $thread) {
			echo '<tr><td width="8%" class="altbg1">'.$thread['tid'].'</td>'.
				'<td width="45%" class="altbg2"><a href="viewthread.php?tid='.$thread['tid'].'" target="_blank">'.$thread['subject'].'</a></td>'.
				'<td width="12%" class="altbg1"><a href="space.php?action=viewpro&uid='.$thread['authorid'].'" target="_blank">'.$thread['author'].'</a></td>'.
				'<td width="20%" class="altbg2">'.gmdate("$dateformat $timeformat", $thread['dateline'] + 3600 * $timeoffset).'</td>'.
				'<td width="15%" class="altbg1"><select name="action2" id="action2" onchange="if(this.options[this.selectedIndex].value != \'\') {$(\'topicadmin\').action.value= this.options[this.selectedIndex].value; $(\'topicadmin\').tid.value='.$thread['tid'].'; $(\'topicadmin\').fid.value='.$thread['fid'].'; $(\'topicadmin\').submit();}">
				<option value="" selected>'.$lang['admin_modoptions'].'</option>
				<option value="delete">'.$lang['admin_delthread'].'</option>
				<option value="close">'.$lang['admin_close'].'</option>
				<option value="move">'.$lang['admin_move'].'</option>
				<option value="copy">'.$lang['admin_copy'].'</option>
				<option value="highlight">'.$lang['admin_highlight'].'</option>
				<option value="digest">'.$lang['admin_digest'].'</option>
				<option value="stick">'.$lang['admin_stick'].'</option>
				<option value="merge">'.$lang['admin_merge'].'</option>
				<option value="bump">'.$lang['admin_bump'].'</option>
				<option value="repair">'.$lang['admin_repair'].'</option>
				</select></td></tr>';
		}
		echo '</table></form><br />';
	}

	require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

	$tpp = 10;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;

	$modcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE $fidadd[fids]$fidadd[and] displayorder='$displayorder'");
	$multipage = multi($modcount, $tpp, $page, "admincp.php?action=modthreads&filter=$filter&modfid=$modfid");

	$threads = '';
	$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
			t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.attachment,
			p.pid, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff
			FROM {$tablepre}threads t
			LEFT JOIN {$tablepre}posts p ON p.tid=t.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
			WHERE $fidadd[t]$fidadd[fids]$fidadd[and] t.displayorder='$displayorder'
			ORDER BY t.dateline DESC LIMIT $start_limit, $tpp");

	while($thread = $db->fetch_array($query)) {
		if($thread['authorid'] && $thread['author']) {
			$thread['author'] = "<a href=\"space.php?action=viewpro&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>";
		} elseif($thread['authorid'] && !$thread['author']) {
			$thread['author'] = "<a href=\"space.php?action=viewpro&uid=$thread[authorid]\" target=\"_blank\">$lang[anonymous]</a>";
		} else {
			$thread['author'] = $lang['guest'];
		}

		$thread['dateline'] = gmdate("$dateformat $timeformat", $thread['dateline'] + $timeoffset * 3600);
		$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], sprintf('%00b', $thread['htmlon']), $thread['allowsmilies'], $thread['allowbbcode'], $thread['allowimgcode'], $thread['allowhtml']);

		$thisbg = $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';

		$threads .= "<tr><td colspan=2 style=\"height: 2px\"></td></tr><tr class=\"altbg1\" id=\"mod_$thread[tid]_row1\"><td width=\"15%\" height=\"100%\">\n".
		"<b>$thread[author]</b> ({$thread[useip]})</td>\n".
		"<td><a href=\"forumdisplay.php?fid=$thread[fid]\" target=\"_blank\">$thread[forumname]</a> <b>&raquo;</b> <b>$thread[subject]</b></td></tr>\n".
		"<tr class=\"altbg2\" id=\"mod_$thread[tid]_row2\"><td valign=\"middle\" >".
		"<input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"validate\" checked  onclick=\"mod_setbg($thread[tid], 'validate');\">$lang[validate]<br />\n".
		"<input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"delete\" onclick=\"mod_setbg($thread[tid], 'delete');\">$lang[delete]<br />\n".
		"<input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" onclick=\"mod_setbg($thread[tid], 'ignore');\">$lang[ignore]\n".
		"</td>\n".
		"<td style=\"border-left: 1px #BBDCF1 solid; padding: 4px;\"><div style=\"overflow: auto; overflow-x: hidden; height:120px; word-break: break-all\">$thread[message]";

		if($thread['attachment']) {
			require_once DISCUZ_ROOT.'./include/attachment.func.php';

			$queryattach = $db->query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM {$tablepre}attachments WHERE tid='$thread[tid]'");
			while($attach = $db->fetch_array($queryattach)) {
				$attachurl = $attach['remote'] ? $ftp['attachurl'] : $attachurl;
				$attach['url'] = $attach['isimage']
						? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"$attachurl/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
						 : "<a href=\"$attachurl/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$threads .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).$attach['url'];
			}
		}
		$threads .= "</div></td></tr><tr class=altbg2 id=\"mod_$thread[tid]_row3\"><td style=\"text-align: center; padding: 0px;\">$thread[dateline]</td><td style=\"border-left: 1px #BBDCF1 solid; padding: 2px 10px 2px 10px;\">\n".
			"<a href=\"post.php?action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&page=1&mod=edit\" target=\"_blank\">".$lang['moderate_edit_thread']."</a> ".
			"&nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=text size=30 name=pm_$thread[tid] id=pm_$thread[tid] style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$thread[tid]').value=this.value\">$modreasonoptions</select>".
			"</td></tr>\n";
	}

	$threads = $threads ? $threads : '<tr><td colspan="2" class="altbg1"><a href="admincp.php?action=modreplies">'.$lang['moderate_threads_none'].'</a></td></tr>';

?>
<style type="text/css">
.mod_validate td{ background: #FFFFFF; }
.mod_delete td{	background: #FFEBE7; }
.mod_ignore td{	background: #EEEEEE; }
</style>
<script type="text/javascript">
function mod_setbg(tid, value) {
	if(value == 'validate') {
		$('mod_' + tid + '_row1').className = 'altbg1';
		$('mod_' + tid + '_row2').className = 'altbg2';
		$('mod_' + tid + '_row3').className = 'altbg2';
	}else {
		$('mod_' + tid + '_row1').className = 'mod_' + value;
		$('mod_' + tid + '_row2').className = 'mod_' + value;
		$('mod_' + tid + '_row3').className = 'mod_' + value;
	}
}
</script>
<form method="post" action="admincp.php?action=modthreads&page=<?=$page?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="ignore" value="<?=$ignore?>">
<input type="hidden" name="filter" value="<?=$filter?>">
<input type="hidden" name="modfid" value="<?=$modfid?>">
<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['moderate_threads']?> - <?=$lang['moderate_bound']?> -
<select style="margin: 0px;" onchange="if(this.options[this.selectedIndex].value != '') {window.location=('admincp.php?action=modthreads&modfid=<?=$modfid?>&filter='+this.options[this.selectedIndex].value+'&amp;sid=5ScwCd');
}"><?=$filteroptions?></select>
- <?=$lang['moderate_forum']?> - <select style="margin: 0px;" onchange="if(this.options[this.selectedIndex].value != '') {window.location=('admincp.php?action=modthreads&filter=<?=$filter?>&modfid='+this.options[this.selectedIndex].value+'&amp;sid=5ScwCd');
}"><?=$forumoptions?></select></td></tr>
<tr><td colspan="2" class="category">
<input class="button" type="button" value="<?=$lang['moderate_all_validate']?>" onclick="checkalloption(this.form, 'validate')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')"> &nbsp;
<?=$threads?>
</table>
<?=$multipage?>
<br /><center><input class="button" type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} elseif($action == 'modreplies') {

	if(!submitcheck('modsubmit')) {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
		$ppp = 10;
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $ppp;

		$modcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[fids]");
		$multipage = multi($modcount, $ppp, $page, "admincp.php?action=modreplies&filter=$filter&modfid=$modfid");

		$posts = '';
		$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
			p.pid, p.fid, p.tid, p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment,
			p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject
			FROM {$tablepre}posts p
			LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=p.fid
			WHERE p.invisible='$displayorder' AND p.first='0' $fidadd[and]$fidadd[p]$fidadd[fids]
			ORDER BY p.dateline DESC LIMIT $start_limit, $ppp");

		while($post = $db->fetch_array($query)) {
			$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $post['allowsmilies'], $post['allowbbcode'], $post['allowimgcode'], $post['allowhtml']);

			$thisbg = $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';

			$posts .= "<tr><td colspan=2 style=\"height: 2px\"></td></tr><tr class=\"altbg1\" id=\"mod_$post[pid]_row1\"><td width=\"15%\" height=\"100%\">\n".
				"<b>$post[author]</b>($post[useip])</td>\n".
				"<td><a href=\"forumdisplay.php?fid=$post[fid]\" target=\"_blank\">$post[forumname]</a> <b>&raquo;</b> ".
				"<a href=\"viewthread.php?tid=$post[tid]\" target=\"_blank\">$post[tsubject]</a> <b>&raquo;</b> $post[subject]</a></td></tr>\n".
				"<tr class=\"altbg2\" id=\"mod_$post[pid]_row2\"><td valign=\"middle\" >".
				"<input class=\"radio\" type=\"radio\" name=\"mod[$post[pid]]\" value=\"validate\" id=\"mod_$post[pid]_1\" checked  onclick=\"mod_setbg($post[pid], 'validate');\">$lang[validate]<br />\n".
				"<input class=\"radio\" type=\"radio\" name=\"mod[$post[pid]]\" value=\"delete\" id=\"mod_$post[pid]_2\" onclick=\"mod_setbg($post[pid], 'delete');\">$lang[delete]<br />\n".
				"<input class=\"radio\" type=\"radio\" name=\"mod[$post[pid]]\" value=\"ignore\" id=\"mod_$post[pid]_3\" onclick=\"mod_setbg($post[pid], 'ignore');\">$lang[ignore]<br />\n".
				"</td>".
				"<td style=\"border-left: 1px #BBDCF1 solid; padding: 4px;\"><div style=\"overflow: auto; overflow-x: hidden; height:120px; word-break: break-all\">$post[message]";

			if($post['attachment']) {
				require_once DISCUZ_ROOT.'./include/attachment.func.php';

				$queryattach = $db->query("SELECT aid, filename, filetype, filesize, attachment, isimage, remote FROM {$tablepre}attachments WHERE pid='$post[pid]'");
				while($attach = $db->fetch_array($queryattach)) {
					$attachurl = $attach['remote'] ? $ftp['attachurl'] : $attachurl;
					$attach['url'] = $attach['isimage']
					 		? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"$attachurl/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
						 	 : "<a href=\"$attachurl/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
					$posts .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t".$attach['filetype']).$attach['url'];
				}
			}

			$posts .= "</div></td></tr><tr class=altbg2 id=\"mod_$post[pid]_row3\"><td style=\"text-align: center; padding: 0px;\">$post[dateline]</td><td style=\"border-left: 1px #BBDCF1 solid; padding: 2px 10px 2px 10px;\">\n".
			"<a href=\"post.php?action=edit&fid=$thread[fid]&tid=$post[tid]&pid=$post[pid]&page=1&mod=edit\" target=\"_blank\">".$lang['moderate_edit_post']."</a> ".
			"&nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=text size=30 name=pm_$post[pid] id=pm_$post[pid] style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$post[pid]').value=this.value\">$modreasonoptions</select>".
			"</td></tr>\n";

		}

		$posts = $posts ? $posts : '<tr><td colspan="2" class="altbg1"><a href="admincp.php?action=modthreads">'.$lang['moderate_posts_none'].'</a></td></tr>';

		shownav('menu_moderate_modreplies');
?>
<style type="text/css">
.mod_validate td{ background: #FFFFFF; }
.mod_delete td{	background: #FFEBE7; }
.mod_ignore td{ background: #EEEEEE; }
</style>
<script type="text/javascript">
function mod_setbg(tid, value) {
	if(value == 'validate') {
		$('mod_' + tid + '_row1').className = 'altbg1';
		$('mod_' + tid + '_row2').className = 'altbg2';
		$('mod_' + tid + '_row3').className = 'altbg2';
	}else {
		$('mod_' + tid + '_row1').className = 'mod_' + value;
		$('mod_' + tid + '_row2').className = 'mod_' + value;
		$('mod_' + tid + '_row3').className = 'mod_' + value;
	}
}
</script>
<form method="post" action="admincp.php?action=modreplies&page=<?=$page?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="filter" value="<?=$filter?>">
<input type="hidden" name="modfid" value="<?=$modfid?>">
<?=$multipage?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['moderate_posts']?> - <?=$lang['moderate_bound']?> -
<select style="margin: 0px;" onchange="if(this.options[this.selectedIndex].value != '') {window.location=('admincp.php?action=modreplies&modfid=<?=$modfid?>&filter='+this.options[this.selectedIndex].value+'&amp;sid=5ScwCd');
}"><?=$filteroptions?></select>
- <?=$lang['moderate_forum']?> - <select style="margin: 0px;" onchange="if(this.options[this.selectedIndex].value != '') {window.location=('admincp.php?action=modreplies&filter=<?=$filter?>&modfid='+this.options[this.selectedIndex].value+'&amp;sid=5ScwCd');
}"><?=$forumoptions?></select></td></tr>
<tr><td colspan="2" style="line-height:32px;" class="p_header">
<input class="button" type="button" value="<?=$lang['moderate_all_validate']?>" onclick="checkalloption(this.form, 'validate')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input class="button" type="button" value="<?=$lang['moderate_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')"></td></tr>
<?=$posts?>
</table>
<?=$multipage?>
<br /><center><input class="button" type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		$pmlist = array();

		if(is_array($mod)) {
			foreach($mod as $pid => $action) {
				$moderation[$action][] = intval($pid);
			}
		}

		if($ignorepids = implodeids($moderation['ignore'])) {
			$db->query("UPDATE {$tablepre}posts SET invisible='-3' WHERE pid IN ($ignorepids) AND invisible='-2' AND first='0' $fidadd[and]$fidadd[fids]");
		}

		if($deletepids = implodeids($moderation['delete'])) {
			$query = $db->query("SELECT pid, authorid, tid, message FROM {$tablepre}posts WHERE pid IN ($deletepids) AND invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[fids]", 'UNBUFFERED');
			$pids = '0';
			while($post = $db->fetch_array($query)) {
				$pids .= ','.$post['pid'];
				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
						'act' => 'modreplies_delete_',
						'authorid' => $post['authorid'],
						'tid' => $post['tid'],
						'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($pids) {
				$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE pid IN ($deletepids)");
				while($attach = $db->fetch_array($query)) {
					dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
				}
				$db->query("DELETE FROM {$tablepre}attachments WHERE pid IN ($pids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}posts WHERE pid IN ($pids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}trades WHERE pid IN ($pids)", 'UNBUFFERED');
			}
			updatemodworks('DLP', count($moderation['delete']));
		}

		$repliesmod = 0;
		if($validatepids = implodeids($moderation['validate'])) {
			$forums = $threads = $lastpost = $attachments = $pidarray = $authoridarray = array();
			$query = $db->query("SELECT t.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, p.message, p.anonymous, ff.replycredits
				FROM {$tablepre}posts p
				LEFT JOIN {$tablepre}forumfields ff ON ff.fid=p.fid
				LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
				WHERE p.pid IN ($validatepids) AND p.invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[p]$fidadd[fids]");

			while($post = $db->fetch_array($query)) {
				$repliesmod ++;
				$pidarray[] = $post['pid'];
				if($post['replycredits']) {
					updatepostcredits('+', $post['authorid'], unserialize($post['replycredits']));
				} else {
					$authoridarray[] = $post['authorid'];
				}

				$forums[] = $post['fid'];

				$threads[$post['tid']]['posts']++;
				$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
					", lastpost='$post[dateline]', lastposter='".($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]))."'" : '';
				$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';

				$pm = 'pm_'.$post['pid'];
				if(isset($$pm) && $$pm <> '' && $post['authorid']) {
					$pmlist[] = array(
						'act' => 'modreplies_validate_',
						'authorid' => $post['authorid'],
						'tid' => $post['tid'],
						'post' =>  dhtmlspecialchars(cutstr($post['message'], 30)),
						'reason' => dhtmlspecialchars($$pm)
					);
				}
			}

			if($authoridarray) {
				updatepostcredits('+', $authoridarray, $creditspolicy['reply']);
			}

			foreach($threads as $tid => $thread) {
				$db->query("UPDATE {$tablepre}threads SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
			}

			foreach(array_unique($forums) as $fid) {
				updateforumcount($fid);
			}

			if(!empty($pidarray)) {
				$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE pid IN (0,".implode(',', $pidarray).")");
				$repliesmod = $db->affected_rows();
				updatemodworks('MOD', $repliesmod);
			} else {
				updatemodworks('MOD', 1);
			}
		}

		if($pmlist) {
			foreach($pmlist as $pm) {
				$reason = $pm['reason'];
				$post = $pm['post'];
				$tid = intval($pm['tid']);
				sendpm($pm['authorid'], $pm['act'].'subject', $pm['act'].'message', $fromid = '', $from = '');
			}
		}

		cpmsg('moderate_replies_succeed', "admincp.php?action=modreplies&page=$page&filter=$filter&modfid=$modfid");

	}

}

?>