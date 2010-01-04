<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: prune.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

$page = max(1, intval($page));

if($action == 'prune') {

	require_once DISCUZ_ROOT.'./include/misc.func.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	if(!submitcheck('prunesubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		if($adminid == 1 || $adminid == 2) {
			$forumselect = '<select name="forums"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
				'<option value="">&nbsp;</option>'.forumselect().'</select>';

			if($forums) {
				$forumselect = preg_replace("/(\<option value=\"$forums\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
			}
		} else {
			$forumselect = $comma = '';
			$query = $db->query("SELECT f.name FROM {$tablepre}moderators m, {$tablepre}forums f WHERE m.uid='$discuz_uid' AND m.fid=f.fid");
			while($forum = $db->fetch_array($query)) {
				$forumselect .= $comma.$forum['name'];
				$comma = ', ';
			}
			$forumselect = $forumselect ? $forumselect : $lang['none'];
		}

		$checkcins = empty($cins) ? '' : 'checked';

		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? gmdate('Y-n-j', $timestamp + $timeoffset * 3600 - 86400 * 7) : $starttime;
		$endtime = $adminid == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? gmdate('Y-n-j', $timestamp + $timeoffset * 3600) : $endtime;

		shownav('menu_maint_prune');
		showtips('prune_tips');

?>
<script type="text/javascript" src="include/javascript/calendar.js"></script>
<script type="text/javascript">
function page(number) {
	$('pruneforum').page.value=number;
	$('pruneforum').searchsubmit.click();
}
</script>
<form id="pruneforum" method="post" action="admincp.php?action=prune">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="page" value="<?=$page?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['prune_search']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_detail']?></td>
<td align="right" class="altbg2"><input class="checkbox" type="checkbox" name="detail" checked value="1"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_forum']?></td>
<td align="right" class="altbg2"><?=$forumselect?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_time']?></td>
<td align="right" class="altbg2">
<input type="text" name="starttime" size="10" value="<?=$starttime?>" onclick="showcalendar(event, this)"> -
<input type="text" name="endtime" size="10" value="<?=dhtmlspecialchars($endtime)?>" <?=($adminid != 1 ? 'disabled' : '')?> onclick="showcalendar(event, this)">
</td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_user']?></td>
<td align="right" class="altbg2">
<?=$lang['case_insensitive']?> <input class="checkbox" type="checkbox" name="cins" value="1" <?=$checkcins?>>
<br /><input type="text" name="users" value="<?=dhtmlspecialchars($users)?>" size="40"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_ip']?></td>
<td align="right" class="altbg2"><input type="text" name="useip" value="<?=dhtmlspecialchars($useip)?>" size="40"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_keyword']?></td>
<td align="right" class="altbg2"><input type="text" name="keywords" value="<?=dhtmlspecialchars($keywords)?>" size="40"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['prune_search_lengthlimit']?></td>
<td align="right" class="altbg2"><input type="text" name="lengthlimit" value="<?=dhtmlspecialchars($lengthlimit)?>" size="40"></td>
</tr>

</table><br />
<center><input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$tidsdelete = $pidsdelete = '0';
		$pids = authcode($pids, 'DECODE');
		$pidsadd = $pids ? 'pid IN ('.$pids.')' : 'pid IN ('.implodeids($pidarray).')';

		$query = $db->query("SELECT fid, tid, pid, first, authorid FROM {$tablepre}posts WHERE $pidsadd");
		while($post = $db->fetch_array($query)) {
			$prune['forums'][] = $post['fid'];
			$prune['thread'][$post['tid']]++;

			$pidsdelete .= ",$post[pid]";
			$tidsdelete .= $post['first'] ? ",$post[tid]" : '';
		}

		if($pidsdelete) {
			require_once DISCUZ_ROOT.'./include/post.func.php';

			$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE pid IN ($pidsdelete) OR tid IN ($tidsdelete)");
			while($attach = $db->fetch_array($query)) {
				dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			}

			if(!$donotupdatemember) {
				$postsarray = $tuidarray = $ruidarray = array();
				$query1 = $db->query("SELECT pid, first, authorid FROM {$tablepre}posts WHERE pid IN ($pidsdelete)");
				$query2 = $db->query("SELECT pid, first, authorid FROM {$tablepre}posts WHERE tid IN ($tidsdelete)");
				while(($post = $db->fetch_array($query1)) || ($post = $db->fetch_array($query2))) {
					$postsarray[$post['pid']] = $post;
				}
				foreach($postsarray as $post) {
					if($post['first']) {
						$tuidarray[] = $post['authorid'];
					} else {
						$ruidarray[] = $post['authorid'];
					}
				}
				if($tuidarray) {
					updatepostcredits('-', $tuidarray, $creditspolicy['post']);
				}
				if($ruidarray) {
					updatepostcredits('-', $ruidarray, $creditspolicy['reply']);
				}
			}

			$db->query("DELETE FROM {$tablepre}attachments WHERE pid IN ($pidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threadsmod WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threadsmod WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($tidsdelete)");
			$deletedthreads = $db->affected_rows();
			$db->query("DELETE FROM {$tablepre}posts WHERE pid IN ($pidsdelete)");
			$deletedposts = $db->affected_rows();
			$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($tidsdelete)");
			$deletedposts += $db->affected_rows();
			$db->query("DELETE FROM {$tablepre}polloptions WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}trades WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}activities WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}activityapplies WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}typeoptionvars WHERE tid IN ($tidsdelete)", 'UNBUFFERED');

			if(count($prunt['thread']) < 50) {
				foreach($prune['thread'] as $tid => $decrease) {
					updatethreadcount($tid);
				}
			} else {
				$repliesarray = array();
				foreach($prune['thread'] as $tid => $decrease) {
					$repliesarray[$decrease][] = $tid;
				}
				foreach($repliesarray as $decrease => $tidarray) {
					$db->query("UPDATE {$tablepre}threads SET replies=replies-$decrease WHERE tid IN (".implode(',', $tidarray).")");
				}
			}

			if($globalstick) {
				updatecache('globalstick');
			}

			foreach(array_unique($prune['forums']) as $fid) {
				updateforumcount($fid);
			}

		}

		$deletedthreads = intval($deletedthreads);
		$deletedposts = intval($deletedposts);
		updatemodworks('DLP', $deletedposts);
		eval("\$cpmsg = \"".$msglang['prune_succeed']."\";");

?>
<script>alert('<?=$cpmsg?>');parent.$('pruneforum').searchsubmit.click();</script>
<?

	}

	if(submitcheck('searchsubmit')) {

		$pids = $postcount = '0';
		$sql = $error = '';

		$keywords = trim($keywords);
		$users = trim($users);
		if(($starttime == '0' && $endtime == '0') || ($keywords == '' && $useip == '' && $users == '')) {
			$error = 'prune_condition_invalid';
		}

		if($adminid == 1 || $adminid == 2) {
			if($forums) {
				$sql .= " AND p.fid='$forums'";
			}
		} else {
			$forums = '0';
			$query = $db->query("SELECT fid FROM {$tablepre}moderators WHERE uid='$discuz_uid'");
			while($forum = $db->fetch_array($query)) {
				$forums .= ','.$forum['fid'];
			}
			$sql .= " AND p.fid IN ($forums)";
		}

		if($users != '') {
			$uids = '-1';
			$query = $db->query("SELECT uid FROM {$tablepre}members WHERE ".(empty($cins) ? 'BINARY' : '')." username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
			while($member = $db->fetch_array($query)) {
				$uids .= ",$member[uid]";
			}
			$sql .= " AND p.authorid IN ($uids)";
		}
		if($useip != '') {
			$sql .= " AND p.useip LIKE '".str_replace('*', '%', $useip)."'";
		}
		if($keywords != '') {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $keywords));
			for($i = 0; $i < count($keywords); $i++) {
				if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
					$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
					$sqlkeywords .= " $or p.subject REGEXP '".$keywords[$i]."' OR p.message REGEXP '".$keywords[$i]."'";
				} else {
					$sqlkeywords .= " $or p.subject LIKE '%".$keywords[$i]."%' OR p.message LIKE '%".$keywords[$i]."%'";
				}
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}

		if($lengthlimit != '') {
			$lengthlimit = intval($lengthlimit);
			$sql .= " AND LENGTH(p.message) < $lengthlimit";
		}

		if($starttime != '0') {
			$starttime = strtotime($starttime);
			$sql .= " AND p.dateline>'$starttime'";
		}
		if($adminid == 1 && $endtime != gmdate('Y-n-j', $timestamp + $timeoffset * 3600)) {
			if($endtime != '0') {
				$endtime = strtotime($endtime);
				$sql .= " AND p.dateline<'$endtime'";
			}
		} else {
			$endtime = $timestamp;
		}
		if(($adminid == 2 && $endtime - $starttime > 86400 * 16) || ($adminid == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'prune_mod_range_illegal';
		}

		if(!$error) {
			if($detail) {
				$pagetmp = $page;
				do{
					$query = $db->query("SELECT p.fid, p.tid, p.pid, p.author, p.authorid, p.dateline, t.subject, p.message FROM {$tablepre}posts p LEFT JOIN {$tablepre}threads t USING(tid) WHERE t.digest!='-1' $sql LIMIT ".(($pagetmp - 1) * $ppp).",$ppp");
					$pagetmp--;
				} while(!$db->num_rows($query) && $pagetmp);
				$posts = '';
				while($post = $db->fetch_array($query)) {
					$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
					$post['subject'] = cutstr($post['subject'], 30);
					$post['message'] = dhtmlspecialchars(cutstr($post['message'], 50));
					$posts .= "<tr><td align=\"center\" class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"pidarray[]\" value=\"$post[pid]\" checked>\n".
						"<td class=\"altbg2\"><a href=\"redirect.php?goto=findpost&pid=$post[pid]&ptid=$post[tid]\" target=\"_blank\">$post[subject]</a></td>\n".
						"<td class=\"altbg1\">$post[message]</td>\n".
						"<td align=\"center\" class=\"altbg2\"><a href=\"forumdisplay.php?fid=$post[fid]\" target=\"_blank\">{$_DCACHE[forums][$post[fid]][name]}</a></td>\n".
						"<td align=\"center\" class=\"altbg1\"><a href=\"space.php?action=viewpro&uid=$post[authorid]\" target=\"_blank\">$post[author]</a></td>\n".
						"<td align=\"center\" class=\"altbg2\">$post[dateline]</td></tr>\n";
				}
				$postcount = $db->result_first("SELECT count(*) FROM {$tablepre}posts p LEFT JOIN {$tablepre}threads t USING(tid) WHERE t.digest!='-1' $sql");
				$multi = multi($postcount, $ppp, $page, "admincp.php?action=prune");
				$multi = preg_replace("/href=\"admincp.php\?action=prune&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='admincp.php?action=prune&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$postcount = 0;
				$query = $db->query("SELECT pid FROM {$tablepre}posts p LEFT JOIN {$tablepre}threads t USING(tid) WHERE t.digest!='-1' $sql");
				while($post = $db->fetch_array($query)) {
					$pids .= ','.$post['pid'];
					$postcount++;
				}
				$multi = '';
			}

			if(!$postcount) {
				$error = 'prune_post_nonexistence';
			}
		}

?>
<br /><br /><form method="post" action="admincp.php?action=prune" target="pruneframe">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="pids" value="<?=authcode($pids, 'ENCODE')?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['prune_result']?> <?=$postcount?></td>
</tr>
<?

		if($error) {
			echo "<tr><td class=\"altbg2\"><b>$lang[discuz_message]: $lang[$error]</b></td></tr>";
		} else {

?>
<tr>
<td class="altbg1"><?=$lang['prune']?></td>
<td class="altbg2"><input class="checkbox" type="checkbox" name="donotupdatemember" value="1" checked> <?=$lang['prune_no_update_member']?></td>
</tr>
<?

			if($detail) {

?>
</table><br /><br /><?=$multi?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><input name="chkall" type="checkbox" class="checkbox" checked onclick="checkall(this.form, 'pidarray', 'chkall')"><?=$lang['del']?></td><td><?=$lang['subject']?></td><td><?=$lang['message']?></td><td><?=$lang['forum']?></td><td><?=$lang['author']?></td><td><?=$lang['time']?></td></tr>
<?=$posts?>
<?

			}

		}

		echo '</table>'.$multi.'<br /><center><input class="button" type="submit" name="prunesubmit" value="'.$lang['submit'].'" '.($error ? 'disabled' : '').'></center></form><iframe name="pruneframe" style="display:none"></iframe>';

	}

} elseif($action == 'pmprune') {

	if(!submitcheck('prunesubmit', 1)) {
		shownav('menu_maint_pmprune');

?>
<form method="post" action="admincp.php?action=pmprune">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['prune_pm']?></td></tr>

<tr><td class="altbg1"><?=$lang['prune_pm_ignore_new']?></td>
<td class="altbg2" align="right"><input class="checkbox" type="checkbox" name="ignorenew" value="1"></td></tr>

<tr><td class="altbg1"><?=$lang['prune_pm_day']?></td>
<td class="altbg2" align="right"><input type="text" name="days" size="7"></td></tr>

<tr><td class="altbg1"><?=$lang['prune_pm_user']?></td>
<td class="altbg2" align="right">
<?=$lang['case_insensitive']?> <input class="checkbox" type="checkbox" name="cins" value="1">
<br /><input type="text" name="users" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['prune_search_keywords']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="srchtype" value="title" checked> <?=$lang['prune_search_title']?> &nbsp; <input class="radio" type="radio" name="srchtype" value="fulltext"> <?=$lang['prune_search_fulltext']?>
<br /><input type="text" name="srchtxt" size="40" maxlength="40"><br /><?=$lang['pruce_rules']?></td></tr>

</table><br />
<center><input class="button" type="submit" name="prunesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if(!$confirmed || !isset($pmids) || !preg_match("/[\d,]/", $pmids)) {

			if($days == '') {
				cpmsg('prune_pm_range_invalid');
			} else {
				$uids = 0;
				$users = str_replace(',', '\',\'', str_replace(' ', '', $users));
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE ".(empty($cins) ? 'BINARY' : '')." username IN ('$users')");
				while($member = $db->fetch_array($query)) {
					$uids .= ','.$member['uid'];
				}

				$prunedateadd = $days != 0 ? "AND dateline<='".($timestamp - $days * 86400)."'" : '';
				$pruneuseradd = $users ? "AND ((msgfromid IN ($uids) AND folder='outbox') OR (msgtoid IN ($uids) AND folder='inbox'))" : '';
				$prunenewadd = $ignorenew ? "AND new='0'" : '';

				$prunetxtadd = '';
				if($srchtxt) {
					if(preg_match("(AND|\+|&|\s)", $srchtxt) && !preg_match("(OR|\|)", $srchtxt)) {
						$andor = ' AND ';
						$sqltxtsrch = '1';
						$srchtxt = preg_replace("/( AND |&| )/is", "+", $srchtxt);
					} else {
						$andor = ' OR ';
						$sqltxtsrch = '0';
						$srchtxt = preg_replace("/( OR |\|)/is", "+", $srchtxt);
					}
					$srchtxt = str_replace('*', '%', addcslashes($srchtxt, '%_'));
					foreach(explode('+', $srchtxt) as $text) {
						$text = trim($text);
						if($text) {
							$sqltxtsrch .= $andor;
							$sqltxtsrch .= $srchtype == 'fulltext' ? "(message LIKE '%".str_replace('_', '\_', $text)."%' OR subject LIKE '%$text%')" : "subject LIKE '%$text%'";
						}
					}
					$prunetxtadd = " AND ($sqltxtsrch)";
				}

				$pmids = 0;
				$query = $db->query("SELECT pmid FROM {$tablepre}pms WHERE 1 $prunedateadd $pruneuseradd $prunetxtadd $prunenewadd");
				while($pm = $db->fetch_array($query)) {
					$pmids .= ','.$pm['pmid'];
				}

				$pmnum = $db->num_rows($query);
				cpmsg('prune_pm_confirm', "admincp.php?action=pmprune&prunesubmit=yes", 'form', '<input type="hidden" name="pmids" value="'.$pmids.'">');
			}

		} else {

			$db->query("DELETE FROM {$tablepre}pms WHERE pmid IN ($pmids)");
			$num = $db->affected_rows();

			cpmsg('prune_pm_succeed');

		}
	}

}

?>