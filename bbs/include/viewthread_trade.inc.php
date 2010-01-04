<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_trade.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($do) || $do == 'tradeinfo') {

	if($do == 'tradeinfo') {
		$tradelistadd = "pid = '$pid'";
		$tradelistlimit = '';
	} else {
		$tradenum = $db->result_first("SELECT count(*) FROM {$tablepre}trades WHERE tid='$tid'");
		$tradelistadd = 'displayorder>0';
		$tradelistlimit = '';
		!$tradenum && $allowpostreply = FALSE;
	}

	$query = $db->query("SELECT * FROM {$tablepre}trades WHERE tid='$tid' AND $tradelistadd ORDER BY displayorder $tradelistlimit");
	$trades = array();$tradelist = 0;
	if(empty($do)) {
		$sellerid = 0;
		$listcount = $db->num_rows($query);
		$tradelist = $tradenum - $listcount;
	}
	while($trade = $db->fetch_array($query)) {
		if($trade['expiration']) {
			$trade['expiration'] = ($trade['expiration'] - $timestamp) / 86400;
			if($trade['expiration'] > 0) {
				$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
				$trade['expiration'] = floor($trade['expiration']);
			} else {
				$trade['expiration'] = -1;
			}
		}
		$tradesaids[] = $trade['aid'];
		$tradespids[] = $trade['pid'];
		$trades[$trade['pid']] = $trade;
	}

	$tradespids = implodeids($tradespids);
	unset($trade);

	if($tradespids) {
		$query = $db->query("SELECT a.* FROM {$tablepre}attachments a WHERE a.pid IN ($tradespids)");
		while($attach = $db->fetch_array($query)) {
			if($attach['isimage'] && is_array($tradesaids) && in_array($attach['aid'], $tradesaids)) {
				$trades[$attach['pid']]['attachurl'] = ($attach['remote'] ? $ftp['attachurl'] : $attachurl).'/'.$attach['attachment'];
				$trades[$attach['pid']]['thumb'] = $trades[$attach['pid']]['attachurl'].($attach['thumb'] ? '.thumb.jpg' : '');
			}
		}
	}

	if($do == 'tradeinfo') {
		$subjectpos = strrpos($navigation, '&raquo; ');
		$subject = substr($navigation, $subjectpos + 8);
		$navigation = substr($navigation, 0, $subjectpos).'&raquo; <a href="viewthread.php?tid='.$tid.'">'.$subject.'</a>';
		$trade = $trades[$pid];
		unset($trades);

		$post = $db->fetch_first("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
			m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
			m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
			mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
			mf.avatarheight, mf.customstatus, mf.spacename, mf.buyercredit, mf.sellercredit $fieldsadd
			FROM {$tablepre}posts p
			LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			WHERE pid='$pid'");

		$postlist[$post['pid']] = viewthread_procpost($post);

		if($attachpids) {
			require_once DISCUZ_ROOT.'./include/attachment.func.php';
			parseattach($attachpids, $attachtags, $postlist, $showimages, array($trade['aid']));
		}

		$post = $postlist[$pid];

		$post['buyerrank'] = 0;
		if($post['buyercredit']){
			foreach($ec_credit['rank'] AS $level => $credit) {
				if($post['buyercredit'] <= $credit) {
					$post['buyerrank'] = $level;
					break;
				}
			}
		}
		$post['sellerrank'] = 0;
		if($post['sellercredit']){
			foreach($ec_credit['rank'] AS $level => $credit) {
				if($post['sellercredit'] <= $credit) {
					$post['sellerrank'] = $level;
					break;
				}
			}
		}

		$navtitle = $trade['subject'].' - ';

		$tradetypeid = $trade['typeid'];

		$typetemplate = '';
		$optiondata = $optionlist = array();
		if($tradetypeid && isset($tradetypes[$tradetypeid])) {
			if(@include_once DISCUZ_ROOT.'./forumdata/cache/threadtype_'.$tradetypeid.'.php') {
				$query = $db->query("SELECT optionid, value FROM {$tablepre}tradeoptionvars WHERE pid='$pid'");
				while($option = $db->fetch_array($query)) {
					$optiondata[$option['optionid']] = $option['value'];
				}

				foreach($_DTYPE as $optionid => $option) {
					$optionlist[$option['identifier']]['title'] = $_DTYPE[$optionid]['title'];
					if($_DTYPE[$optionid]['type'] == 'checkbox') {
						$optionlist[$option['identifier']]['value'] = '';
						foreach(explode("\t", $optiondata[$optionid]) as $choiceid) {
							$optionlist[$option['identifier']]['value'] .= $_DTYPE[$optionid]['choices'][$choiceid].'&nbsp;';
						}
					} elseif(in_array($_DTYPE[$optionid]['type'], array('radio', 'select'))) {
						$optionlist[$option['identifier']]['value'] = $_DTYPE[$optionid]['choices'][$optiondata[$optionid]];
					} elseif($_DTYPE[$optionid]['type'] == 'image') {
						$maxwidth = $_DTYPE[$optionid]['maxwidth'] ? 'width="'.$_DTYPE[$optionid]['maxwidth'].'"' : '';
						$maxheight = $_DTYPE[$optionid]['maxheight'] ? 'height="'.$_DTYPE[$optionid]['maxheight'].'"' : '';
						$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\"><img src=\"$optiondata[$optionid]\"  $maxwidth $maxheight border=\"0\"></a>" : '';
					} elseif($_DTYPE[$optionid]['type'] == 'url') {
						$optionlist[$option['identifier']]['value'] = $optiondata[$optionid] ? "<a href=\"$optiondata[$optionid]\" target=\"_blank\">$optiondata[$optionid]</a>" : '';
					} else {
						$optionlist[$option['identifier']]['value'] = $optiondata[$optionid];
					}
				}

				$typetemplate = $_DTYPETEMPLATE ? preg_replace(array("/\[(.+?)value\]/ies", "/{(.+?)}/ies"), array("showoption('\\1', 'value')", "showoption('\\1', 'title')"), $_DTYPETEMPLATE) : '';
			}

			$post['subject'] = '['.$tradetypes[$tradetypeid].'] '.$post['subject'];
		}

		include template('trade_info');

	} else {
		$post = $db->fetch_first("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
			m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
			m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
			mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
			mf.avatarheight, mf.customstatus, mf.spacename, mf.buyercredit, mf.sellercredit $fieldsadd
			FROM {$tablepre}posts p
			LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			WHERE p.tid='$tid' AND first=1 LIMIT 1");

		$tmp = explode("\t\t\t", $post['message']);
		$post['message'] = count($tmp) == 2 ? $tmp[0] : '';
		$postlist[$post['pid']] = viewthread_procpost($post);
		viewthread_parsetags();
		$postlist[$post['pid']]['counterdesc'] = $postlist[$post['pid']]['message'];
		$post = $postlist[$post['pid']];

		$post['buyerrank'] = 0;
		if($post['buyercredit']){
			foreach($ec_credit['rank'] AS $level => $credit) {
				if($post['buyercredit'] <= $credit) {
					$post['buyerrank'] = $level;
					break;
				}
			}
		}
		$post['sellerrank'] = 0;
		if($post['sellercredit']){
			foreach($ec_credit['rank'] AS $level => $credit) {
				if($post['sellercredit'] <= $credit) {
					$post['sellerrank'] = $level;
					break;
				}
			}
		}

		include template('viewthread_trade');
	}

} elseif($do == 'viewtradelist') {
	$tradepp = 10;
	$start_limit = ($page - 1) * $tradepp;
	$query = $db->query("SELECT typeid FROM {$tablepre}trades WHERE tid='$tid' AND displayorder<=0");
	$trades = $threadtradetypes = array();
	$showtradetypemenu = FALSE;
	while($tradetype = $db->fetch_array($query)) {
		$threadtradetypes[$tradetype['typeid']] = $tradetype['typeid'];
		$tradetype['typeid'] && !$showtradetypemenu && $showtradetypemenu = TRUE;
	}
	$typeadd = isset($tradetypeid) ? ' AND typeid=\''.intval($tradetypeid).'\'' : '';
	$listcount = $db->result_first("SELECT count(*) FROM {$tablepre}trades WHERE tid='$tid' AND displayorder<=0 $typeadd");
	$multipage = multi($listcount, $tradepp, $page, "viewthread.php?do=viewtradelist&tid=$tid".(isset($tradetypeid) ? "&amp;tradetypeid=$tradetypeid" : '').(isset($highlight) ? "&amp;highlight=".rawurlencode($highlight) : ''));
	$query = $db->query("SELECT * FROM {$tablepre}trades WHERE tid='$tid' AND displayorder<=0 $typeadd ORDER BY displayorder DESC LIMIT $start_limit, $tradepp");
	while($trade = $db->fetch_array($query)) {
		if($trade['expiration']) {
			$trade['expiration'] = ($trade['expiration'] - $timestamp) / 86400;
			if($trade['expiration'] > 0) {
				$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
				$trade['expiration'] = floor($trade['expiration']);
			} else {
				$trade['expiration'] = -1;
			}
		}
		$trades[$trade['pid']] = $trade;
	}

	include template('viewthread_trade_list');

} elseif($do == 'viewfirstpost') {
	$multipage = '';
	$post = $db->fetch_first("SELECT p.*,m.username,m.adminid,m.groupid,m.credits FROM {$tablepre}posts p LEFT JOIN {$tablepre}members m ON m.uid=p.authorid WHERE tid='$tid' AND p.invisible='0' AND p.first='1' LIMIT 1");
	$tmp = explode("\t\t\t", $post['message']);
	$post['message'] = count($tmp) == 2 ? $tmp[1] : $tmp[0];
	$postlist[$post['pid']] = viewthread_procpost($post, 1);

	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $postlist, $showimages);
	}

	viewthread_parsetags();

	$thread = $postlist[$post['pid']];

	include template('viewthread_trade_post');

} elseif(in_array($do, array('viewall', 'viewpost', 'viewtrade'))) {

	$sqladd = $do == 'viewall' ? "WHERE p.tid='$tid' AND p.invisible='0' AND p.first='0'" :
		($do == 'viewpost' ? "LEFT JOIN {$tablepre}trades tr ON p.pid=tr.pid WHERE p.tid='$tid' AND p.invisible='0' AND tr.tid IS null AND p.first='0'" :
		"INNER JOIN {$tablepre}trades tr ON p.pid=tr.pid WHERE p.tid='$tid' AND p.invisible='0'");
	$repostnum = $db->result_first("SELECT count(*) FROM {$tablepre}posts p $sqladd");

	$ppp = $forum['threadcaches'] && !$discuz_uid ? $_DCACHE['settings']['postperpage'] : $ppp;
	$start_limit = $numpost = ($page - 1) * $ppp;
	if($start_limit > $repostnum) {
		$start_limit = $numpost = 0;
		$page = 1;
	}
	$multipage = multi($repostnum, $ppp, $page, "viewthread.php?tid=$tid&amp;do=$do&amp;extra=$extra".(isset($highlight) ? "&amp;highlight=".rawurlencode($highlight) : ''));

	$query = $db->query("SELECT p.*,m.username,m.adminid,m.groupid,m.credits FROM {$tablepre}posts p LEFT JOIN {$tablepre}members m ON m.uid=p.authorid $sqladd ORDER BY p.dateline LIMIT $start_limit, $ppp");

	$tradespids = array();
	while($post = $db->fetch_array($query)) {
		$post['first'] = 0;
		$post = viewthread_procpost($post, 1);
		$postlist[$post['pid']] = $post;
		$tradespids[] = $post['pid'];
	}

	if(($do == 'viewtrade' || $do == 'viewall') && $tradespids) {
		$query = $db->query("SELECT * FROM {$tablepre}trades WHERE pid IN (".implodeids($tradespids).")");
		while($trade = $db->fetch_array($query)) {
			if($trade['expiration']) {
				$trade['expiration'] = ($trade['expiration'] - $timestamp) / 86400;
				if($trade['expiration'] > 0) {
					$trade['expirationhour'] = floor(($trade['expiration'] - floor($trade['expiration'])) * 24);
					$trade['expiration'] = floor($trade['expiration']);
				} else {
					$trade['expiration'] = -1;
				}
			}
			$trades[$trade['pid']] = $trade;
			$tradesaids[] = $trade['aid'];
		}
	}

	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $postlist, $showimages, $tradesaids);
	}

	if($tradespids) {
		$tradespids = implodeids($tradespids);
		$query = $db->query("SELECT a.* FROM {$tablepre}attachments a WHERE a.pid IN ($tradespids)");
		while($attach = $db->fetch_array($query)) {
			if($attach['isimage'] && is_array($tradesaids) && in_array($attach['aid'], $tradesaids)) {
				$trades[$attach['pid']]['attachurl'] = ($attach['remote'] ? $ftp['attachurl'] : $attachurl).'/'.$attach['attachment'];
				$trades[$attach['pid']]['thumb'] = $trades[$attach['pid']]['attachurl'].($attach['thumb'] ? '.thumb.jpg' : '');
			}
		}
	}

	include template('viewthread_trade_post');

} elseif($do == 'viewrelatedtrade') {

	$multipage = '';
	$relatedtrades = !empty($insenz['relatedtrades']) ? stripslashes($insenz['relatedtrades']) : '';

	include template('viewthread_trade_post');

}

exit;

?>