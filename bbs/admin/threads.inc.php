<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: threads.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./include/post.func.php';

cpheader();

$page = max(1, intval($page));

if(!$operation) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		$forumselect = '<select name="inforum"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect().'</select>';
		if(isset($inforum)) {
			$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}

		$typeselect = '<select name="intype"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
			'<option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>';
		$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].($type['description'] ? ' ('.$type['description'].')' : '').'</option>';
		}
		$typeselect .= '</select>';
		if(isset($intype)) {
			$typeselect = preg_replace("/(\<option value=\"$intype\")(\>)/", "\\1 selected=\"selected\" \\2", $typeselect);
		}

		$checkcins		= empty($cins) ? '' : 'checked';
		$checkhspecialthread	= array(intval($specialthread) => 'checked');
		$checksticky		= array(intval($sticky) => 'checked');
		$checkdigest		= array(intval($digest) => 'checked');
		$checkattach		= array(intval($attach) => 'checked');
		$checkblog		= array(intval($blog) => 'checked');
		$checkrate		= array(intval($rate) => 'checked');
		$checkhighlight		= array(intval($highlight) => 'checked');

		if(!empty($special)) {
			foreach($special as $id => $val) {
				$checkspecial[$val] = empty($special[$id]) ? '' : 'checked';
			}
		}
		shownav('menu_maint_threads');

?>
<script src="include/javascript/calendar.js"></script>
<script type="text/javascript">
function page(number) {
	$('threadforum').page.value=number;
	$('threadforum').searchsubmit.click();
}
</script>
<form id="threadforum" method="post" action="admincp.php?action=threads">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="page" value="<?=$page?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['threads_search']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_detail']?></td>
<td class="altbg2" align="right"><input class="checkbox" type="checkbox" name="detail" <?=($detail ? 'checked' : '')?> value="1"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_forum']?></td>
<td class="altbg2" align="right"><?=$forumselect?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_time']?></td>
<td class="altbg2" align="right">
<input type="text" name="starttime" size="10" value="<?=dhtmlspecialchars($starttime)?>" onclick="showcalendar(event, this);"> -
<input type="text" name="endtime" size="10" value="<?=dhtmlspecialchars($endtime)?>" onclick="showcalendar(event, this);">
</td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_user']?></td>
<td class="altbg2" align="right">
<?=$lang['case_insensitive']?> <input class="checkbox" type="checkbox" name="cins" value="1" <?=$checkcins?>>
<br /><input type="text" name="users" size="40" value="<?=$users?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_keyword']?></td>
<td class="altbg2" align="right"><input type="text" name="keywords" size="40" value="<?=dhtmlspecialchars($keywords)?>"></td>
</tr>

<tr><td class="altbg1">&nbsp;</td>
<td align="right" class="altbg2" style="text-align: right;"><input class="checkbox" type="checkbox" value="1" onclick="$('advanceoption').style.display = $('advanceoption').style.display == 'none' ? '' : 'none'; this.value = this.value == 1 ? 0 : 1; this.checked = this.value == 1 ? false : true"><?=$lang['more_options']?> &nbsp; </td></tr>

<tbody id="advanceoption" style="display: none">

<tr>
<td class="altbg1"><?=$lang['threads_search_type']?></td>
<td class="altbg2" align="right"><?=$typeselect?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_viewless']?></td>
<td class="altbg2" align="right"><input type="text" name="viewsless" size="40" value="<?=dhtmlspecialchars($viewsless)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_viewmore']?></td>
<td class="altbg2" align="right"><input type="text" name="viewsmore" size="40" value="<?=dhtmlspecialchars($viewsmore)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_replyless']?></td>
<td class="altbg2" align="right"><input type="text" name="repliesless" size="40" value="<?=dhtmlspecialchars($repliesless)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_replymore']?></td>
<td class="altbg2" align="right"><input type="text" name="repliesmore" size="40" value="<?=dhtmlspecialchars($repliesmore)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_readpermmore']?></td>
<td class="altbg2" align="right"><input type="text" name="readpermmore" size="40" value="<?=dhtmlspecialchars($readpermmore)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_pricemore']?></td>
<td class="altbg2" align="right"><input type="text" name="pricemore" size="40" value="<?=dhtmlspecialchars($pricemore)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_noreplyday']?></td>
<td class="altbg2" align="right"><input type="text" name="noreplydays" size="40" value="<?=dhtmlspecialchars($noreplydays)?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_special']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="specialthread" value="0" onclick="$('showspecial').style.display='none'" <?=$checkhspecialthread[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="specialthread" value="1" onclick="$('showspecial').style.display=''" <?=$checkhspecialthread[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="specialthread" value="2" onclick="$('showspecial').style.display=''" <?=$checkhspecialthread[2]?>> <?=$lang['threads_search_include_no']?>
<div id="showspecial" style="display:<?=($specialthread ? '' : 'none')?>">
<input class="checkbox" type="checkbox" name="special[]" value="1" <?=$checkspecial[1]?>> <?=$lang['threads_special_poll']?>&nbsp;
<input class="checkbox" type="checkbox" name="special[]" value="2" <?=$checkspecial[2]?>> <?=$lang['threads_special_trade']?>&nbsp;
<input class="checkbox" type="checkbox" name="special[]" value="3" <?=$checkspecial[3]?>> <?=$lang['threads_special_reward']?>&nbsp;
<input class="checkbox" type="checkbox" name="special[]" value="4" <?=$checkspecial[4]?>> <?=$lang['threads_special_activity']?>&nbsp;
<input class="checkbox" type="checkbox" name="special[]" value="5" <?=$checkspecial[5]?>> <?=$lang['threads_special_debate']?>&nbsp;
<input class="checkbox" type="checkbox" name="special[]" value="6" <?=$checkspecial[6]?>> <?=$lang['threads_special_videos']?>
</div>
</td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_sticky']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="sticky" value="0" <?=$checksticky[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="sticky" value="1" <?=$checksticky[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="sticky" value="2" <?=$checksticky[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_digest']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="digest" value="0" <?=$checkdigest[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="digest" value="1" <?=$checkdigest[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="digest" value="2" <?=$checkdigest[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_blog']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="blog" value="0" <?=$checkblog[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="blog" value="1" <?=$checkblog[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="blog" value="2" <?=$checkblog[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_search_attach']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="attach" value="0" <?=$checkattach[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="attach" value="1" <?=$checkattach[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="attach" value="2" <?=$checkattach[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_rate']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="rate" value="0" <?=$checkrate[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="rate" value="1" <?=$checkrate[1]?>>  <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="rate" value="2" <?=$checkrate[2]?>>  <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['threads_highlight']?></td>
<td class="altbg2" align="right">
<input class="radio" type="radio" name="highlight" value="0" <?=$checkhighlight[0]?>> <?=$lang['unlimited']?>&nbsp;
<input class="radio" type="radio" name="highlight" value="1" <?=$checkhighlight[1]?>>  <?=$lang['threads_search_include_yes']?>&nbsp;
<input class="radio" type="radio" name="highlight" value="2" <?=$checkhighlight[2]?>>  <?=$lang['threads_search_include_no']?></td>
</tr>
</tbody>

</table><br />
<center><input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} else {

	$tidsadd = isset($tids) ? 'tid IN ('.$tids.')' : 'tid IN ('.implodeids($tidarray).')';

	if($operation == 'moveforum') {

		if(!$db->result_first("SELECT fid FROM {$tablepre}forums WHERE type<>'group' AND fid='$toforum'")) {
			cpmsg('threads_move_invalid');
		}

		$db->query("UPDATE {$tablepre}threads SET fid='$toforum' WHERE $tidsadd");
		$db->query("UPDATE {$tablepre}posts SET fid='$toforum' WHERE $tidsadd");

		foreach(explode(',', $fids.','.$toforum) as $fid) {
			updateforumcount(intval($fid));
		}

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'movetype') {

		if($totype != 0) {
			if(!$db->result_first("SELECT typeid FROM {$tablepre}threadtypes WHERE typeid='$totype'")) {
				cpmsg('threads_move_invalid');
			}
		}

		$db->query("UPDATE {$tablepre}threads SET typeid='$totype' WHERE $tidsadd");

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'delete') {

		$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE $tidsadd");
		while($attach = $db->fetch_array($query)) {
			dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
		}

		if(!$donotupdatemember) {
			$tuidarray = $ruidarray = array();
			$query = $db->query("SELECT first, authorid FROM {$tablepre}posts WHERE $tidsadd");
			while($post = $db->fetch_array($query)) {
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

		$db->query("DELETE FROM {$tablepre}attachments WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}posts WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}threads WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}polloptions WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}polls WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}rewardlog WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}trades WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}rewardlog WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}activities WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}activityapplies WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}debates WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}debateposts WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}threadsmod WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}relatedthreads WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}typeoptionvars WHERE $tidsadd", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}videos WHERE $tidsadd", 'UNBUFFERED');

		if($globalstick) {
			updatecache('globalstick');
		}

		foreach(explode(',', $fids) as $fid) {
			updateforumcount(intval($fid));
		}

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'deleteattach') {

		$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE $tidsadd");
		while($attach = $db->fetch_array($query)) {
			dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
		}
		$db->query("DELETE FROM {$tablepre}attachments WHERE $tidsadd");
		$db->query("UPDATE {$tablepre}threads SET attachment='0' WHERE $tidsadd");
		$db->query("UPDATE {$tablepre}posts SET attachment='0' WHERE $tidsadd");

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'stick') {

		$db->query("UPDATE {$tablepre}threads SET displayorder='$stick_level' WHERE $tidsadd");
		if($globalstick) {
			updatecache('globalstick');
		}

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'adddigest') {

		$query = $db->query("SELECT tid, authorid, digest FROM {$tablepre}threads WHERE $tidsadd");
		while($thread = $db->fetch_array($query)) {
			updatecredits($thread['authorid'], $creditspolicy['digest'], $digest_level - $thread['digest'], 'digestposts=digestposts-1');
		}
		$db->query("UPDATE {$tablepre}threads SET digest='$digest_level' WHERE $tidsadd");

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'addstatus') {

		$db->query("UPDATE {$tablepre}threads SET closed='$status' WHERE $tidsadd");

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	} elseif($operation == 'supe_pushsetting' && $supe['status']) {

		$db->query("UPDATE {$tablepre}threads SET supe_pushstatus='$supe_pushstatus' WHERE $tidsadd");

		eval("\$cpmsg = \"".$msglang['threads_succeed']."\";");

	}

	$tids && deletethreadcaches($tids);
	$cpmsg = $cpmsg ? "alert('$cpmsg');" : '';

?>
<script><?=$cpmsg?>parent.$('threadforum').searchsubmit.click();</script>
<?

}

if(submitcheck('searchsubmit')) {

	$sql = '';

	if($inforum != '' && $inforum != 'all') {
		$sql .= " AND fid='$inforum'";
	}

	if($intype != '' && $intype != 'all') {
		$sql .= " AND typeid='$intype'";
	}

	if($viewsless != '') {
		$sql .= " AND views<'$viewsless'";
	}
	if($viewsmore != '') {
		$sql .= " AND views>'$viewsmore'";
	}

	if($repliesless != '') {
		$sql .= " AND replies<'$repliesless'";
	}
	if($repliesmore != '') {
		$sql .= " AND replies>'$repliesmore'";
	}

	if($readpermmore != '') {
		$sql .= " AND readperm>'$readpermmore'";
	}

	if($pricemore != '') {
		$sql .= " AND price>'$pricemore'";
	}

	if($beforedays != '') {
		$sql .= " AND dateline<'$timestamp'-'$beforedays'*86400";
	}
	if($noreplydays != '') {
		$sql .= " AND lastpost<'$timestamp'-'$noreplydays'*86400";
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= " AND dateline>'$starttime'";
	}

	if($endtime) {
		$endtime = strtotime($endtime);
		$sql .= " AND dateline<='$endtime'";
	}

	if(trim($keywords)) {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $keywords));
		for($i = 0; $i < count($keywords); $i++) {
			$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if(trim($users)) {
		$sql .= " AND ".(empty($cins) ? 'BINARY' : '')." author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')";
	}

	if($sticky == 1) {
		$sql .= " AND displayorder>'0'";
	} elseif($sticky == 2) {
		$sql .= " AND displayorder='0'";
	}
	if($digest == 1) {
		$sql .= " AND digest>'0'";
	} elseif($digest == 2) {
		$sql .= " AND digest='0'";
	}
	if($blog == 1) {
		$sql .= " AND blog>'0'";
	} elseif($blog == 2) {
		$sql .= " AND blog='0'";
	}
	if($attach == 1) {
		$sql .= " AND attachment>'0'";
	} elseif($attach == 2) {
		$sql .= " AND attachment='0'";
	}
	if($attach == 1) {
		$sql .= " AND attachment>'0'";
	} elseif($attach == 2) {
		$sql .= " AND attachment='0'";
	}
	if($rate == 1) {
		$sql .= " AND rate>'0'";
	} elseif($rate == 2) {
		$sql .= " AND rate='0'";
	}
	if($highlight == 1) {
		$sql .= " AND highlight>'0'";
	} elseif($highlight == 2) {
		$sql .= " AND highlight='0'";
	}
	if(!empty($special)) {
		$specials = $comma = '';
		foreach($special as $val) {
			$specials .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		if($specialthread == 1) {
			$sql .=  " AND special IN ($specials)";
		} elseif($specialthread == 2) {
			$sql .=  " AND special NOT IN ($specials)";
		}
	}

	$fids = array();
	$tids = $threadcount = '0';
	if($sql) {
		$sql = "digest>='0' AND displayorder>='0' $sql";
		if($detail) {
			$pagetmp = $page;
			do{
				$query = $db->query("SELECT fid, tid, readperm, price, subject, authorid, author, views, replies, lastpost FROM {$tablepre}threads WHERE $sql LIMIT ".(($pagetmp - 1) * $tpp).",$tpp");
				$pagetmp--;
			} while(!$db->num_rows($query) && $pagetmp);
			$threads = '';
			while($thread = $db->fetch_array($query)) {
				$thread['lastpost'] = gmdate("$dateformat $timeformat", $thread['lastpost'] + $timeoffset * 3600);
				$threads .= "<tr><td align=\"center\" class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" checked>\n".
					"<td class=\"altbg2\"><a href=\"viewthread.php?tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>".($thread['readperm'] ? " - [$lang[threads_readperm] $thread[readperm]]" : '').($thread['price'] ? " - [$lang[threads_price] $thread[price]]" : '')."</td>\n".
					"<td align=\"center\" class=\"altbg1\"><a href=\"forumdisplay.php?fid=$thread[fid]\" target=\"_blank\">{$_DCACHE[forums][$thread[fid]][name]}</a></td>\n".
					"<td align=\"center\" class=\"altbg2\"><a href=\"space.php?action=viewpro&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a></td>\n".
					"<td align=\"center\" class=\"altbg1\">$thread[replies]</td>\n".
					"<td align=\"center\" class=\"altbg2\">$thread[views]</td>\n".
					"<td align=\"center\" class=\"altbg1\">$thread[lastpost]</td></tr>\n";
			}
			$threadcount = $db->result_first("SELECT count(*) FROM {$tablepre}threads WHERE $sql");
			$multi = multi($threadcount, $tpp, $page, "admincp.php?action=threads");
			$multi = preg_replace("/href=\"admincp.php\?action=threads&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='admincp.php?action=threads&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$query = $db->query("SELECT fid, tid FROM {$tablepre}threads WHERE $sql");
			while($thread = $db->fetch_array($query)) {
				$fids[] = $thread['fid'];
				$tids .= ','.$thread['tid'];
			}
			$threadcount = $db->result_first("SELECT count(*) FROM {$tablepre}threads WHERE $sql");
			$multi = '';
		}
	}
	$fids = implode(',', array_unique($fids));

?>
<br /><form method="post" action="admincp.php?action=threads" target="threadframe">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['threads_result']?> <?=$threadcount?></td>
</tr>
<?

	if(!$threadcount) {

		echo '<tr><td class="altbg2" colspan="2">'.$lang['threads_thread_nonexistence'].'</td></tr>';

	} else {

		$typeselect = '';
		$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].($type['description'] ? ' ('.$type['description'].')' : '').'</option>';
		}

		if(!$detail) {
			echo '<input type="hidden" name="tids" value="'.$tids.'">';
		}
		echo '<input type="hidden" name="fids" value="'.$fids.'">';

?>
<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="moveforum" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_move_forum']?></td>
<td class="altbg2"><select name="toforum"><?=forumselect()?></select></td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="movetype" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_move_type']?></td>
<td class="altbg2"><select name="totype"><option value="0">&nbsp;&nbsp;> <?=$lang['threads_search_type_none']?></option><?=$typeselect?></select></td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="delete" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_delete']?></td>
<td class="altbg2"><input class="checkbox" type="checkbox" name="donotupdatemember" value="1" checked> <?=$lang['threads_delete_no_update_member']?></td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="stick" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_stick']?></td>
<td class="altbg2">
<input class="radio" type="radio" name="stick_level" value="0" checked> <?=$lang['threads_remove']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="stick_level" value="1"> <?=$lang['forums_stick_one']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="stick_level" value="2"> <?=$lang['forums_stick_two']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="stick_level" value="3"> <?=$lang['forums_stick_three']?></td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="adddigest" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_add_digest']?></td>
<td class="altbg2">
<input class="radio" type="radio" name="digest_level" value="0" checked> <?=$lang['threads_remove']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="digest_level" value="1"> <?=$lang['forums_digest_one']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="digest_level" value="2"> <?=$lang['forums_digest_two']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="digest_level" value="3"> <?=$lang['forums_digest_three']?></td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="addstatus" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_open_close']?></td>
<td class="altbg2">
<input class="radio" type="radio" name="status" value="0" checked> <?=$lang['open']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="status" value="1"> <?=$lang['closed']?> &nbsp; &nbsp;
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="deleteattach" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_delete_attach']?></td>
<td class="altbg2">&nbsp;</td>
</tr>

<tr>
<td class="altbg1"><input class="radio" type="radio" name="operation" value="supe_pushsetting" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_push_supesite']?></td>
<td class="altbg2">
<input class="radio" type="radio" name="supe_pushstatus" value="2" checked> <?=$lang['threads_push']?> &nbsp; &nbsp;
<input class="radio" type="radio" name="supe_pushstatus" value="-2"> <?=$lang['threads_remove_push']?> &nbsp; &nbsp;
</tr>
<?

		if($detail) {

?>
</table><br /><br /><?=$multi?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><input name="chkall" type="checkbox" class="checkbox" checked onclick="checkall(this.form, 'tidarray', 'chkall')"></td><td><?=$lang['subject']?></td><td><?=$lang['forum']?></td><td><?=$lang['author']?></td><td nowrap><?=$lang['threads_replies']?></td><td nowrap><?=$lang['threads_views']?></td><td><?=$lang['threads_lastpost']?></td></tr>
<?=$threads?>
<?

		}

	}

	echo '</table>'.$multi.'<br /><center><input class="button" type="submit" name="modsubmit" value="'.$lang['submit'].'" disabled></center></form><iframe name="threadframe" style="display:none"></iframe>';

}

?>