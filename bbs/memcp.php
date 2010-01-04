<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: memcp.php 11169 2007-11-08 06:30:00Z tiger $
*/

define('NOROBOT', TRUE);
require_once './include/common.inc.php';

$discuz_action = 7;
$avatarextarray = array('gif', 'jpg', 'png');

if(!$discuz_uid) {
	showmessage('not_loggedin', NULL, 'HALTED');
}

$action = !empty($action) ? $action : '';
$operation = !empty($operation) ? $operation : '';

$maxbiosize = $maxbiosize ? $maxbiosize : 200;
$maxbiotradesize = $maxbiotradesize ? $maxbiotradesize : 400;

if(!$action) {

	if($allowavatar || $allownickname) {
		$member = $db->fetch_first("SELECT mf.nickname, mf.avatar, mf.avatarwidth, mf.avatarheight, m.gender, m.groupid
			FROM {$tablepre}memberfields mf, {$tablepre}members m WHERE m.uid='$discuz_uid' AND mf.uid=m.uid");
	} else {
		$member = array('nickname' => '', 'avatar' => '');
	}

	$avatar = $member['avatar'] ? "<div class=\"avatar\" style=\"width: ".$member['avatarwidth']."\"><img src=\"$member[avatar]\" width=\"$member[avatarwidth]\" height=\"$member[avatarheight]\" border=\"0\" alt=\"\" /></div>" : '<img class="avatar" src="images/avatars/noavatar.gif" alt="" />';


	$validating = array();
	if($regverify == 2 && $groupid == 8) {
		if($validating = $db->fetch_first("SELECT * FROM {$tablepre}validating WHERE uid='$discuz_uid'")) {
			$validating['moddate'] = $validating['moddate'] ? gmdate("$dateformat $timeformat", $validating['moddate'] + $timeoffset * 3600) : 0;
			$validating['adminenc'] = rawurlencode($validating['admin']);
		}
	}

	$member = $db->fetch_first("SELECT uid, posts, digestposts, oltime, regdate, regip, lastvisit, lastip, lastpost FROM {$tablepre}members WHERE uid='$discuz_uid'");

	$member['postperday'] = $timestamp - $member['regdate'] > 86400 ? round(86400 * $member['posts'] / ($timestamp - $member['regdate']), 2) : $member['posts'];
	$member['regdate'] = gmdate("$dateformat", $member['regdate'] + $timeoffset * 3600);
	$member['lastvisit'] = gmdate("$dateformat $timeformat", $member['lastvisit'] + ($timeoffset * 3600));
	$member['lastpost'] = $member['lastpost'] ? gmdate("$dateformat $timeformat", $member['lastpost'] + ($timeoffset * 3600)) : 'x';

	require_once DISCUZ_ROOT.'./include/misc.func.php';
	$member['regiplocation'] = convertip($member['regip']);
	$member['lastiplocation'] = convertip($member['lastip']);

	$msgexists = 0;
	$msglist = array();
	$query = $db->query("SELECT * FROM {$tablepre}pms WHERE msgtoid='$discuz_uid' AND folder='inbox' AND delstatus!='2' ORDER BY dateline DESC LIMIT 0, 5");
	while($message = $db->fetch_array($query)) {
		$msgexists = 1;
		$message['dateline'] = gmdate("$dateformat $timeformat", $message['dateline'] + $timeoffset * 3600);
		$message['subject'] = $message['new'] ? "<b>$message[subject]</b>" : $message['subject'];

		$msglist[] = $message;
	}

	$loglist = array();
	$query = $db->query("SELECT * FROM {$tablepre}creditslog WHERE uid='$discuz_uid' ORDER BY dateline DESC LIMIT 5");
	while($log = $db->fetch_array($query)) {
		$log['fromtoenc'] = rawurlencode($log['fromto']);
		$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
		$loglist[] = $log;
	}

	include template('memcp_home');

} elseif($action == 'profile') {

	$typeid = empty($typeid) || !in_array($typeid, array(1, 2, 3, 4, 5)) ? 2 : $typeid;
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_profilefields.php';

	$member = $db->fetch_first("SELECT * FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		WHERE m.uid='$discuz_uid'");

	$seccodecheck = substr(sprintf('%05b', $seccodestatus), -5, 1) && (!$seccodedata['minposts'] || $posts < $seccodedata['minposts']);
	$passport_status = $passport_status == 'shopex' && $passport_shopex ? '' : $passport_status;

	if(!submitcheck('editsubmit', 0, $seccodecheck)) {

		require_once DISCUZ_ROOT.'./include/editor.func.php';

		$enctype = $allowavatar == 3 && $typeid == 4 ? 'enctype="multipart/form-data"' : '';

		if($typeid == 1) {

			if($seccodecheck) {
				$seccode = random(6, 1) + $seccode{0} * 1000000;
			}

		} elseif($typeid == 2) {

			$gendercheck = array($member['gender'] => 'checked="checked"');

		} elseif($typeid == 4) {

			if(substr(trim($member['avatar']), 0, 14) == 'customavatars/' && !file_exists(DISCUZ_ROOT.'./'.$member['avatar'])) {
				$db->query("UPDATE {$tablepre}memberfields SET avatar='', avatarwidth='0', avatarheight='0' WHERE uid='$discuz_uid'");
				$member['avatar'] = '';
			}

			$bio = explode("\t\t\t", $member['bio']);
			$member['bio'] = html2bbcode($bio[0]);
			$member['biotrade'] = html2bbcode($bio[1]);
			$member['signature'] = html2bbcode($member['sightml']);
			$member['avatarwidth'] = !empty($member['avatarwidth']) ? $member['avatarwidth'] : '*';
			$member['avatarheight'] = !empty($member['avatarheight']) ? $member['avatarheight'] : '*';

		} elseif($typeid == 5) {

			$invisiblechecked = $member['invisible'] ? 'checked="checked"' : '';
			$emailchecked = $member['showemail'] ? 'checked="checked"' : '';
			$newschecked = $member['newsletter'] ? 'checked="checked"' : '';
			$tppchecked = array($member['tpp'] => 'selected="selected"');
			$pppchecked = array($member['ppp'] => 'selected="selected"');
			$toselect = array(strval((float)$member['timeoffset']) => 'selected="selected"');
			$pscheck = array(intval($member['pmsound']) => 'checked="checked"');
			$emcheck = array($member['editormode'] => 'selected="selected"');
			$tfcheck = array($member['timeformat'] => 'checked="checked"');
			$dfcheck = array($member['dateformat'] => 'selected="selected"');

			$styleselect = '';
			$query = $db->query("SELECT styleid, name FROM {$tablepre}styles WHERE available='1'");
			while($style = $db->fetch_array($query)) {
				$styleselect .= "<option value=\"$style[styleid]\" ".
					($style['styleid'] == $member['styleid'] ? 'selected="selected"' : NULL).
					">$style[name]</option>\n";
			}

			$customshow = str_pad(base_convert($member['customshow'], 10, 3), 3, 0, STR_PAD_LEFT);
			$sschecked = array($customshow{0} => 'selected="selected"');
			$sachecked = array($customshow{1} => 'selected="selected"');
			$sichecked = array($customshow{2} => 'selected="selected"');

			$dateformatlist = array();
			if(!empty($userdateformat) && ($count = count($userdateformat))) {
				for($num =1; $num <= $count; $num ++) {
					$dateformatlist[$num] = str_replace(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy'), $userdateformat[$num-1]);
				}
			}

		}

		include template('memcp_profile');

	} else {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$membersql = $memberfieldsql = $authstradd1 = $authstradd2 = $newpasswdadd = '';
		if($typeid == 1) {

			if(!$passport_status) {
				$secquesnew = $questionidnew == -1 ? $discuz_secques : quescrypt($questionidnew, $answernew);
				if($newpassword || $secquesnew != $discuz_secques) {
					if(md5($oldpassword) != $discuz_pw) {
						showmessage('profile_passwd_wrong', NULL, 'HALTED');
					}
					if($newpassword) {
						if($newpassword != addslashes($newpassword)) {
							showmessage('profile_passwd_illegal');
						} elseif($newpassword != $newpassword2) {
							showmessage('profile_passwd_notmatch');
						}
						$newpasswdadd = ", password='".md5($newpassword)."'";
					}
				}

				if(($adminid == 1 || $adminid == 2 || $adminid == 3) && !$secquesnew && $admincp['forcesecques']) {
					showmessage('profile_admin_security_invalid');
				}
			}

			if($emailnew != $member['email']) {
				if(md5($oldpassword) != $discuz_pw && !$passport_status) {
					showmessage('profile_passwd_wrong', NULL, 'HALTED');
				} else {
					$emailnew = $passport_status ? $member['email'] : $emailnew;
					$accessexp = '/('.str_replace("\r\n", '|', preg_quote($accessemail, '/')).')$/i';
					$censorexp = '/('.str_replace("\r\n", '|', preg_quote($censoremail, '/')).')$/i';
					$invalidemail = $accessemail ? !preg_match($accessexp, $emailnew) : $censoremail && preg_match($censorexp, $emailnew);
					if(!isemail($emailnew) || $invalidemail) {
						showmessage('profile_email_illegal');
					}
				}
			}

			$emailnew = dhtmlspecialchars($emailnew);

			if($regverify == 1 && $adminid == 0 && (($grouptype == 'member' && $adminid == 0) || $groupid == 8)) {
				if($emailnew != $db->result_first("SELECT email FROM {$tablepre}members WHERE uid='$discuz_uid'")) {
					if(!$doublee) {
						if($db->result_first("SELECT uid FROM {$tablepre}members WHERE email='$emailnew' LIMIT 1")) {
							showmessage('profile_email_duplicate');
						}
					}

					$idstring = random(6);
					$groupid = 8;

					require_once DISCUZ_ROOT.'./forumdata/cache/usergroup_8.php';

					$authstradd1 = ", groupid='8'";
					$authstradd2 = "authstr='$timestamp\t2\t$idstring'";
					sendmail("$discuz_userss <$emailnew>", 'email_verify_subject', 'email_verify_message');
				}
			}

			$membersql = "secques='$secquesnew', email='$emailnew' $newpasswdadd $authstradd1";
			$memberfieldsql = $authstradd2;

		} elseif($typeid == 2) {

			$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censoruser = trim($censoruser)), '/')).')$/i';
			if($censoruser && (@preg_match($censorexp, $nicknamenew) || @preg_match($censorexp, $cstatusnew))) {
				showmessage('profile_nickname_cstatus_illegal');
			}

			if($msnnew && !isemail($msnnew)) {
				showmessage('profile_alipay_msn');
			}

			if($alipaynew && !isemail($alipaynew)) {
				showmessage('profile_alipay_illegal');
			}

			$sitenew = dhtmlspecialchars(trim(preg_match("/^https?:\/\/.+/i", $sitenew) ? $sitenew : ($sitenew ? 'http://'.$sitenew : '')));
			$icqnew = preg_match ("/^([0-9]+)$/", $icqnew) && strlen($icqnew) >= 5 && strlen($icqnew) <= 12 ? $icqnew : '';
			$qqnew = preg_match ("/^([0-9]+)$/", $qqnew) && strlen($qqnew) >= 5 && strlen($qqnew) <= 12 ? $qqnew : '';
			$bdaynew = datecheck($bdaynew) ? $bdaynew : '0000-00-00';
			$yahoonew = dhtmlspecialchars($yahoonew);
			$msnnew = dhtmlspecialchars($msnnew);
			$taobaonew = dhtmlspecialchars($taobaonew);
			$alipaynew = dhtmlspecialchars($alipaynew);
			$nicknamenew = $allownickname ? cutstr(censor(dhtmlspecialchars($nicknamenew)), 30) : '';
			$cstatusadd = $allowcstatus ? ', customstatus=\''.cutstr(censor(dhtmlspecialchars($cstatusnew)), 30).'\'' : '';
			$gendernew = empty($gendernew) ? 0 : intval($gendernew);
			$locationnew = cutstr(censor(dhtmlspecialchars($locationnew)), 30);

			$membersql = "gender='$gendernew', bday='$bdaynew'";
			$memberfieldsql = "nickname='$nicknamenew', site='$sitenew', location='$locationnew', icq='$icqnew', qq='$qqnew', yahoo='$yahoonew', msn='$msnnew', taobao='$taobaonew', alipay='$alipaynew' $cstatusadd";

		} elseif($typeid == 3 && ($_DCACHE['fields_required'] || $_DCACHE['fields_optional'])) {

			$fieldadd = array();
			foreach(array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']) as $field) {
				$field_key = 'field_'.$field['fieldid'];
				$field_val = trim(${'field_'.$field['fieldid'].'new'});
				if($field['required'] && $field_val == '' && !($field['unchangeable'] && $member[$field_key])) {
					showmessage('profile_required_info_invalid');
				} elseif($field['selective'] && $field_val != '' && !isset($field['choices'][$field_val])) {
					showmessage('undefined_action', NULL, 'HALTED');
				} elseif(!$field['unchangeable'] || !$member[$field_key]) {
					$fieldadd[] = "$field_key='".dhtmlspecialchars($field_val)."'";
				}
			}

			$memberfieldsql = implode(', ', $fieldadd);

		} elseif($typeid == 4) {

			if($maxsigsize) {
				if(strlen($signaturenew) > $maxsigsize) {
					showmessage('profile_sig_toolong');
				}
			} else {
				$signaturenew = '';
			}

			$avataradd = $avatar = '';
			$avatarimagesize = array();
			if($allowavatar == 3 && disuploadedfile($_FILES['customavatar']['tmp_name']) && $_FILES['customavatar']['tmp_name'] != 'none' && $_FILES['customavatar']['tmp_name'] && trim($_FILES['customavatar']['name'])) {
				$_FILES['customavatar']['name'] = daddslashes($_FILES['customavatar']['name']);
				$avatarext = strtolower(fileext($_FILES['customavatar']['name']));
				if(is_array($avatarextarray) && !in_array($avatarext, $avatarextarray)) {
					showmessage('profile_avatar_invalid');
				}
				$avatar = 'customavatars/'.$discuz_uid.'.'.$avatarext;
				$avatartarget = DISCUZ_ROOT.'./'.$avatar;
				if(!@copy($_FILES['customavatar']['tmp_name'], $avatartarget)) {
					@move_uploaded_file($_FILES['customavatar']['tmp_name'], $avatartarget);
				}
				$avatarimagesize = @getimagesize($avatartarget);
				if(!$avatarimagesize || ($maxavatarsize && @filesize($avatartarget) > $maxavatarsize)) {
					@unlink($avatartarget);
					showmessage($avatarimagesize ? 'profile_avatar_toobig' : 'profile_avatar_invalid');
				}
				$avatarwidthnew = $avatarimagesize[0];
				$avatarheightnew = $avatarimagesize[1];
			} elseif(($allowavatar == 2 || $allowavatar == 3) && $urlavatar) {
				if(!preg_match("/^(http:\/\/.+?)|(images\/avatars\/.+?)|(customavatars\/.+?)$/i", $urlavatar)) {
					showmessage('profile_avatar_invalid');
				} elseif(!intval($avatarwidthnew) || !intval($avatarheightnew)) {
					$avatarimagesize = @getimagesize($urlavatar);
				}
				$avatar = $urlavatar;
			} elseif(($allowavatar == 1 || $allowavatar == 2 || $allowavatar == 3) && $urlavatar) {
				if(!preg_match("/^(images\/avatars\/.+?)$/i", $urlavatar)) {
					showmessage('profile_avatar_invalid');
				}
				$avatarimagesize = @getimagesize($urlavatar);
				$avatar = $urlavatar;
			}

			if($avatar) {
				if(!in_array(strtolower(fileext($avatar)), array('gif', 'jpg', 'png'))) {
					showmessage('profile_avatar_invalid');
				}
				$avatar = dhtmlspecialchars(trim($avatar));
				if(!intval($avatarwidthnew) || !intval($avatarheightnew)) {
					@list($avatarwidthnew, $avatarheightnew) = $avatarimagesize ? $avatarimagesize : array($avatarwidthnew, $avatarheightnew);
				}
				$maxsize = max($avatarwidthnew, $avatarheightnew);
				if($maxsize > $maxavatarpixel) {
					$avatarwidthnew = $avatarwidthnew * $maxavatarpixel / $maxsize;
					$avatarheightnew = $avatarheightnew * $maxavatarpixel / $maxsize;
				}
				$avataradd = ", avatar='$avatar', avatarwidth='$avatarwidthnew', avatarheight='$avatarheightnew'";
			} else {
				$avataradd = ", avatar='', avatarwidth='', avatarheight=''";
			}


			$signaturenew = censor($signaturenew);
			$sigstatusnew = $signaturenew ? 1 : 0;
			$bionew = censor(dhtmlspecialchars($bionew));

			$sightmlnew = addslashes(discuzcode(stripslashes($signaturenew), 1, 0, 0, 0, $allowsigbbcode, $allowsigimgcode, 0, 0, 1));
			$biohtmlnew = addslashes(discuzcode(stripslashes($bionew), 1, 0, 0, 0, $allowbiobbcode, $allowbioimgcode, 0, 0, 1));
			$biohtmlnew .= "\t\t\t".addslashes(discuzcode(stripslashes($biotradenew), 1, 0, 0, 0, 1, 1, 0, 0, 1));

			$membersql = "sigstatus='$sigstatusnew'";
			$memberfieldsql = "bio='$biohtmlnew', sightml='$sightmlnew' $avataradd";

		} else {

			$tppnew = in_array($tppnew, array(10, 20, 30)) ? $tppnew : 0;
			$pppnew = in_array($pppnew, array(5, 10, 15)) ? $pppnew : 0;
			$editormodenew = in_array($editormodenew, array(0, 1, 2)) ? $editormodenew : 2;
			$ssnew = in_array($ssnew, array(0, 1)) ? $ssnew : 2;
			$sanew = in_array($sanew, array(0, 1)) ? $sanew : 2;
			$sinew = in_array($sinew, array(0, 1)) ? $sinew : 2;
			$customshownew = base_convert($ssnew.$sanew.$sinew, 3, 10);
			$dateformatnew = ($dateformatnew = intval($dateformatnew)) && !empty($userdateformat[$dateformatnew -1]) ? $dateformatnew : 0;
			$invisiblenew = $allowinvisible && $invisiblenew ? 1 : 0;
			$showemailnew = empty($showemailnew) ? 0 : 1;
			$styleid = empty($styleidnew) ? $styleid : $styleidnew;

			$membersql = "styleid='$styleidnew', showemail='$showemailnew', timeoffset='$timeoffsetnew', tpp='$tppnew', ppp='$pppnew', editormode='$editormodenew', customshow='$customshownew', newsletter='$newsletternew', invisible='$invisiblenew', timeformat='$timeformatnew', dateformat='$dateformatnew', pmsound='$pmsoundnew'";

		}

		if($membersql) {
			$db->query("UPDATE {$tablepre}members SET $membersql WHERE uid='$discuz_uid'");
		}

		$query = $db->query("SELECT uid FROM {$tablepre}memberfields WHERE uid='$discuz_uid'");
		if(!$db->num_rows($query)) {
			$db->query("REPLACE INTO {$tablepre}memberfields (uid) VALUES ('$discuz_uid')");
		}

		if($memberfieldsql) {
			$db->query("UPDATE {$tablepre}memberfields SET $memberfieldsql WHERE uid='$discuz_uid'");
		}

		if($type == 1 && !empty($authstradd1) && !empty($authstradd2)) {
			showmessage('profile_email_verify');
		} else {
			showmessage('profile_succeed', 'memcp.php?action=profile&typeid='.$typeid);
		}
	}

} elseif($action == 'credits') {

	$taxpercent = sprintf('%1.2f', $creditstax * 100).'%';

	if(!$operation) {
		if($exchangestatus) {
			$operation = 'exchange';
		} elseif($transferstatus) {
			$operation = 'transfer';
		} elseif($ec_ratio) {
			$operation = 'addfunds';
		}
	}

	if($operation == 'transfer' && $transferstatus) {

		if(!submitcheck('creditssubmit')) {

			include template('memcp_credits');

		} else {

			$amount = intval($amount);

			if(md5($password) != $discuz_pw) {
				showmessage('credits_password_invalid');
			} elseif($amount <= 0) {
				showmessage('credits_transaction_amount_invalid');
			} elseif(${'extcredits'.$creditstrans} - $amount < ($minbalance = $transfermincredits)) {
				showmessage('credits_balance_insufficient');
			} elseif(!($netamount = floor($amount * (1 - $creditstax)))) {
				showmessage('credits_net_amount_iszero');
			}

			$member = $db->fetch_first("SELECT uid, username FROM {$tablepre}members WHERE username='$to'");
			if(!$member) {
				showmessage('credits_transfer_send_nonexistence');
			} elseif($member['uid'] == $discuz_uid) {
				showmessage('credits_transfer_self');
			}

			$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-'$amount' WHERE uid='$discuz_uid'");
			$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+'$netamount' WHERE uid='$member[uid]'");
			$db->query("INSERT INTO {$tablepre}creditslog (uid, fromto, sendcredits, receivecredits, send, receive, dateline, operation)
				VALUES ('$discuz_uid', '".addslashes($member['username'])."', '$creditstrans', '$creditstrans', '$amount', '0', '$timestamp', 'TFR'),
				('$member[uid]', '$discuz_user', '$creditstrans', '$creditstrans', '0', '$netamount', '$timestamp', 'RCV')");

			if(!empty($transfermessage)) {
				$transfermessage = stripslashes($transfermessage);
				$transfertime = gmdate($GLOBALS['_DCACHE']['settings']['dateformat'].' '.$GLOBALS['_DCACHE']['settings']['timeformat'], $timestamp + $timeoffset * 3600);
				sendpm($member['uid'], 'transfer_subject', 'transfer_message');
			}

			showmessage('credits_transaction_succeed', 'memcp.php?action=creditslog&amp;operation=creditslog');

		}

	} elseif($operation == 'exchange' && $exchangestatus) {

		if(!submitcheck('creditssubmit')) {

			$extcredits_exchange = array();

			if(!empty($extcredits)) {
				foreach($extcredits as $key => $value) {
					if($value['allowexchangein'] || $value['allowexchangeout']) {
						$extcredits_exchange['extcredits'.$key] = array('title' => $value['title'], 'unit' => $value['unit']);
					}
				}
			}

			include template('memcp_credits');

		} elseif($extcredits[$fromcredits]['ratio'] && $extcredits[$tocredits]['ratio']) {

			$amount = intval($amount);

			if(md5($password) != $discuz_pw) {
				showmessage('credits_password_invalid');
			} elseif($fromcredits == $tocredits) {
				showmessage('credits_exchange_invalid');
			} elseif($amount <= 0) {
				showmessage('credits_transaction_amount_invalid');
			} elseif(${'extcredits'.$fromcredits} - $amount < ($minbalance = $exchangemincredits)) {
				showmessage('credits_balance_insufficient');
			} elseif(!($netamount = floor($amount * $extcredits[$fromcredits]['ratio'] * (1 - $creditstax) / $extcredits[$tocredits]['ratio']))) {
				showmessage('credits_net_amount_iszero');
			}
			if(!$extcredits[$fromcredits]['allowexchangeout']) {
				showmessage('extcredits_disallowexchangeout');
			}
			if(!$extcredits[$tocredits]['allowexchangein']) {
				showmessage('extcredits_disallowexchangein');
			}

			$db->query("UPDATE {$tablepre}members SET extcredits$fromcredits=extcredits$fromcredits-'$amount', extcredits$tocredits=extcredits$tocredits+'$netamount' WHERE uid='$discuz_uid'");
			$db->query("INSERT INTO {$tablepre}creditslog (uid, fromto, sendcredits, receivecredits, send, receive, dateline, operation)
				VALUES ('$discuz_uid', '$discuz_user', '$fromcredits', '$tocredits', '$amount', '$netamount', '$timestamp', 'EXC')");

			showmessage('credits_transaction_succeed', 'memcp.php?action=creditslog&amp;operation=creditslog');

		}

	} elseif($operation == 'addfunds' && $ec_ratio) {

		if(!submitcheck('creditssubmit')) {

			include template('memcp_credits');

		} else {

			include language('misc');
			$amount = intval($amount);
			if(!$amount || ($ec_mincredits && $amount < $ec_mincredits) || ($ec_maxcredits && $amount > $ec_maxcredits)) {
				showmessage('credits_addfunds_amount_invalid');
			}

			if($db->result_first("SELECT COUNT(*) FROM {$tablepre}orders WHERE uid='$discuz_uid' AND submitdate>='$timestamp'-180 LIMIT 1")) {
				showmessage('credits_addfunds_ctrl');
			}

			if($ec_maxcreditspermonth) {
				$query = $db->query("SELECT SUM(amount) FROM {$tablepre}orders WHERE uid='$discuz_uid' AND submitdate>='$timestamp'-2592000 AND status IN (2, 3)");
				if(($db->result($query, 0)) + $amount > $ec_maxcreditspermonth) {
					showmessage('credits_addfunds_toomuch');
				}
			}

			$price = ceil($amount / $ec_ratio * 100) / 100;
			$orderid = gmdate('YmdHis', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600).random(18);

			$query = $db->query("SELECT orderid FROM {$tablepre}orders WHERE orderid='$orderid'");
			if($db->num_rows($query)) {
				showmessage('credits_addfunds_order_invalid');
			}

			$db->query("INSERT INTO {$tablepre}orders (orderid, status, uid, amount, price, submitdate)
				VALUES ('$orderid', '1', '$discuz_uid', '$amount', '$price', '$timestamp')");

			require_once DISCUZ_ROOT.'./api/alipayapi.php';
			showmessage('credits_addfunds_succeed', credit_payurl($price, $orderid));

		}

	} else {
		showmessage('undefined_action', NULL, 'HALTED');
	}

} elseif($action == 'creditslog') {

	if($operation == 'paymentlog') {

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}paymentlog WHERE uid='$discuz_uid'");
		$multipage = multi($db->result($query, 0), $tpp, $page, "memcp.php?action=creditslog&amp;operation=paymentlog");

		$loglist = array();
		$query = $db->query("SELECT p.*, f.fid, f.name, t.subject, t.author, t.dateline AS tdateline FROM {$tablepre}paymentlog p
			LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
			WHERE p.uid='$discuz_uid' ORDER BY p.dateline DESC
			LIMIT $start_limit, $tpp");
		while($log = $db->fetch_array($query)) {
			$log['authorenc'] = rawurlencode($log['authorenc']);
			$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
			$log['tdateline'] = gmdate("$dateformat $timeformat", $log['tdateline'] + $timeoffset * 3600);
			$loglist[] = $log;
		}

		include template('memcp_credits');

	} elseif($operation == 'incomelog') {

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}paymentlog WHERE authorid='$discuz_uid'");
		$multipage = multi($db->result($query, 0), $tpp, $page, "memcp.php?action=creditslog&amp;operation=incomelog");

		$loglist = array();
		$query = $db->query("SELECT p.*, m.username, f.fid, f.name, t.subject, t.dateline AS tdateline FROM {$tablepre}paymentlog p
			LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
			LEFT JOIN {$tablepre}members m ON m.uid=p.uid
			WHERE p.authorid='$discuz_uid' ORDER BY p.dateline DESC
			LIMIT $start_limit, $tpp");
		while($log = $db->fetch_array($query)) {
			$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
			$log['tdateline'] = gmdate("$dateformat $timeformat", $log['tdateline'] + $timeoffset * 3600);
			$loglist[] = $log;
		}

		include template('memcp_credits');

	} elseif($operation == 'rewardpaylog') {

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}rewardlog WHERE authorid='$discuz_uid'");
		$multipage = multi($db->result($query, 0), $tpp, $page, "memcp.php?action=creditslog&amp;operation=incomelog");

		$loglist = array();
		$query = $db->query("SELECT
			r.*, m.uid, m.username
			, f.fid, f.name, t.subject, t.price
			FROM
			{$tablepre}rewardlog r
			LEFT JOIN {$tablepre}threads t ON t.tid=r.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
			LEFT JOIN {$tablepre}members m ON m.uid=r.answererid
			WHERE r.authorid='$discuz_uid' ORDER BY r.dateline DESC
			LIMIT $start_limit, $tpp");
		while($log = $db->fetch_array($query)) {
			$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
			$log['price'] = abs($log['price']);
			$loglist[] = $log;
		}

		include template('memcp_credits');

	} elseif($operation == 'rewardincomelog') {

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}rewardlog WHERE answererid='$discuz_uid'");
		$multipage = multi($db->result($query, 0), $tpp, $page, "memcp.php?action=creditslog&amp;operation=incomelog");

		$loglist = array();
		$query = $db->query("SELECT r.*, m.uid, m.username, f.fid, f.name, t.subject, t.price FROM {$tablepre}rewardlog r
			LEFT JOIN {$tablepre}threads t ON t.tid=r.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
			LEFT JOIN {$tablepre}members m ON m.uid=r.authorid
			WHERE r.answererid='$discuz_uid' and r.authorid>0 ORDER BY r.dateline DESC
			LIMIT $start_limit, $tpp");
		while($log = $db->fetch_array($query)) {
			$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
			$log['price'] = abs($log['price']);
			$loglist[] = $log;
		}

		include template('memcp_credits');

	} else {

		$operation = 'creditslog';

		$page = max(1, intval($page));
		$start_limit = ($page - 1) * $tpp;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}creditslog WHERE uid='$discuz_uid'");
		$multipage = multi($db->result($query, 0), $tpp, $page, "memcp.php?action=creditslog&amp;operation=creditslog");

		$loglist = array();
		$query = $db->query("SELECT * FROM {$tablepre}creditslog WHERE uid='$discuz_uid' ORDER BY dateline DESC LIMIT $start_limit, $tpp");
		while($log = $db->fetch_array($query)) {
			$log['fromtoenc'] = rawurlencode($log['fromto']);
			$log['dateline'] = gmdate("$dateformat $timeformat", $log['dateline'] + $timeoffset * 3600);
			$loglist[] = $log;
		}

		include template('memcp_credits');

	}

} elseif($action == 'usergroups') {

	if(!$allowmultigroups) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	}

	$switchmaingroup = $grouppublic || $grouptype == 'member' ? 1 : 0;

	if(empty($type)) {

		$groupterms = unserialize($db->result_first("SELECT groupterms FROM {$tablepre}memberfields WHERE uid='$discuz_uid'"));

		$grouplist = array();
		$extgroupidarray = explode("\t", $extgroupids);

		$query = $db->query("SELECT groupid, grouptitle, type, system, allowmultigroups FROM {$tablepre}usergroups WHERE (type='special' AND system<>'private' AND radminid='0') OR (type='member' AND '$credits'>=creditshigher AND '$credits'<creditslower) OR groupid IN ('$groupid'".($extgroupids ? ', '.str_replace("\t", ',', $extgroupids) : '').") ORDER BY type, system");
		while($group = $db->fetch_array($query)) {
			if(in_array($group['groupid'], $extgroupidarray) && ($group['groupid'] == $groupid || ($group['type'] != 'member' && $group['system'] == 'private'))) {
				$group['grouptitle'] = '<b><i>'.$group['grouptitle'].'</i></b>';
			} elseif(!$group['allowmultigroups']) {
				$group['grouptitle'] = '<u>'.$group['grouptitle'].'</u>';
			}
			$group['mainselected'] = $group['groupid'] == $groupid ? 'checked="checked"' : '';
			$group['maindisabled'] = $switchmaingroup && (($group['system'] != 'private' && ($group['system'] == "0\t0" || $group['groupid'] == $groupid || in_array($group['groupid'], $extgroupidarray))) || $group['type'] == 'member') ? '' : 'disabled';
			$group['dailyprice'] = $group['minspan'] = 0;

			if($group['system'] != 'private') {
				list($group['dailyprice'], $group['minspan']) = explode("\t", $group['system']);
			}

			if($group['groupid'] == $groupid && !empty($groupterms['main'])) {
				$group['expiry'] = gmdate($dateformat, $groupterms['main']['time'] + $timeoffset * 3600);
			} elseif(isset($groupterms['ext'][$group['groupid']])) {
				$group['expiry'] = gmdate($dateformat, $groupterms['ext'][$group['groupid']] + $timeoffset * 3600);
			} else {
				$group['expiry'] = 'N/A';
			}

			$grouplist[$group['groupid']] = $group;
		}

		include template('memcp_usergroups');

	} else {

		if($type == 'main' && submitcheck('groupsubmit') && $switchmaingroup) {

			$group = $db->fetch_first("SELECT groupid, type, system, grouptitle FROM {$tablepre}usergroups WHERE groupid='$groupidnew' AND (".($extgroupids ? 'groupid IN ('.str_replace("\t", ',', $extgroupids).') OR ' : '')."(type='special' AND system='0\t0' AND radminid='0') OR (type='member' AND '$credits'>=creditshigher AND '$credits'<creditslower))");
			if(!$group) {
				showmessage('undefined_action', NULL, 'HALTED');
			}

			$extgroupidsnew = $groupid;
			foreach(explode("\t", $extgroupids) as $extgroupid) {
				if($extgroupid && $extgroupid != $groupidnew) {
					$extgroupidsnew .= "\t".$extgroupid;
				}
			}
			$adminidnew = in_array($adminid, array(1, 2, 3)) ? $adminid : ($group['type'] == 'special' ? -1 : 0);

			$db->query("UPDATE {$tablepre}members SET groupid='$groupidnew', adminid='$adminidnew', extgroupids='$extgroupidsnew' WHERE uid='$discuz_uid'");
			showmessage('usergroups_update_succeed', 'memcp.php?action=usergroups');

		} elseif($type == 'extended') {

			$group = $db->fetch_first("SELECT groupid, type, system, grouptitle FROM {$tablepre}usergroups WHERE groupid='$edit' AND (".($extgroupids ? 'groupid IN ('.str_replace("\t", ',', $extgroupids).') OR ' : '')."(type='special' AND system<>'private' AND radminid='0'))");
			if(!$group) {
				showmessage('undefined_action', NULL, 'HALTED');
			}

			$join = !in_array($group['groupid'], explode("\t", $extgroupids));
			$group['dailyprice'] = $group['minspan'] = 0;

			if($group['system'] != 'private') {
				list($group['dailyprice'], $group['minspan']) = explode("\t", $group['system']);
			}

			if(!isset($extcredits[$creditstrans])) {
				showmessage('credits_transaction_disabled');
			}

			if(!submitcheck('groupsubmit')) {

				$group['minamount'] = $group['dailyprice'] * $group['minspan'];

				include template('memcp_usergroups');

			} else {

				$groupterms = unserialize($db->result_first("SELECT groupterms FROM {$tablepre}memberfields WHERE uid='$discuz_uid'"));

				if($join) {

					$extgroupidsarray = array();
					foreach(array_unique(array_merge(explode("\t", $extgroupids), array($edit))) as $extgroupid) {
						if($extgroupid) {
							$extgroupidsarray[] = $extgroupid;
						}
					}
					$extgroupidsnew = implode("\t", $extgroupidsarray);

					if($group['dailyprice']) {
						if(($days = intval($days)) < $group['minspan']) {
							showmessage('usergroups_span_invalid');
						}

						if(${'extcredits'.$creditstrans} - ($amount = $days * $group['dailyprice']) < ($minbalance = 0)) {
							showmessage('credits_balance_insufficient');
						}

						$groupexpirynew = $timestamp + $days * 86400;
						$groupterms['ext'][$edit] = $groupexpirynew;

						$groupexpirynew = groupexpiry($groupterms);

						$db->query("UPDATE {$tablepre}members SET groupexpiry='$groupexpirynew', extgroupids='$extgroupidsnew', extcredits$creditstrans=extcredits$creditstrans-'$amount' WHERE uid='$discuz_uid'");
						$db->query("UPDATE {$tablepre}memberfields SET groupterms='".addslashes(serialize($groupterms))."' WHERE uid='$discuz_uid'");
						$db->query("INSERT INTO {$tablepre}creditslog (uid, fromto, sendcredits, receivecredits, send, receive, dateline, operation)
							VALUES ('$discuz_uid', '$discuz_user', '$creditstrans', '0', '$amount', '0', '$timestamp', 'UGP')");
					} else {
						$db->query("UPDATE {$tablepre}members SET extgroupids='$extgroupidsnew' WHERE uid='$discuz_uid'");
					}

					showmessage('usergroups_join_succeed', 'memcp.php?action=usergroups');

				} else {

					if($edit != $groupid) {
						if(isset($groupterms['ext'][$edit])) {
							unset($groupterms['ext'][$edit]);
						}
						$groupexpirynew = groupexpiry($groupterms);
						$db->query("UPDATE {$tablepre}memberfields SET groupterms='".addslashes(serialize($groupterms))."' WHERE uid='$discuz_uid'");
					} else {
						$groupexpirynew = 'groupexpiry';
					}

					$extgroupidsarray = array();
					foreach(explode("\t", $extgroupids) as $extgroupid) {
						if($extgroupid && $extgroupid != $edit) {
							$extgroupidsarray[] = $extgroupid;
						}
					}
					$extgroupidsnew = implode("\t", array_unique($extgroupidsarray));
					$db->query("UPDATE {$tablepre}members SET groupexpiry='$groupexpirynew', extgroupids='$extgroupidsnew' WHERE uid='$discuz_uid'");

					showmessage('usergroups_exit_succeed', 'memcp.php?action=usergroups');

				}

			}

		} else {

			showmessage('usergroups_nonexistence');

		}

	}

} elseif($action == 'spacemodule') {

	if($supe['status'] && $xspacestatus) {
		dheader("location:$supe[siteurl]/spacecp.php?docp=1");
	}

	if(!$spacestatus) {
		showmessage('undefined_action', NULL, 'HALTED');
	}

	require_once DISCUZ_ROOT.'./include/space.func.php';
	include_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
	include_once language('spaces');

	foreach($modulesettings as $module => $setting) {
		if(empty($spacedata['limit'.$module]) && $module != 'userinfo' && $module != 'calendar') {
			unset($modulesettings[$module]);
		}
	}

	$spacestyledir = DISCUZ_ROOT.'./mspace/';
	$sdir = opendir($spacestyledir);
	$spacestyles = array();
	$i = 3;
	while($entry = readdir($sdir)) {
		if($entry != '.' && $entry != '..' && file_exists("$spacestyledir/$entry/style.ini")) {
			$spacestyles[$entry] = file("$spacestyledir/$entry/style.ini");
			if($i == 3) {
				$spacestyles[$entry]['row'] = 1;
				$i = 0;
			}
			$i++;
		}
	}

	if(submitcheck('spacesubmit')) {
		$spacelayout = !empty($spacelayout) && is_array($spacelayout) ? $spacelayout : array();
		if(empty($spacelayout[1])) {
			showmessage('space_layout_nocenter');
		} else {
			if(empty($spacelayout[0])) {
				$spaceside = 2;
			}
			if(empty($spacelayout[2])) {
				$spaceside = 1;
			}
		}
		$db->query("UPDATE {$tablepre}memberfields SET spacename = '".dhtmlspecialchars(trim($spacename))."' WHERE uid='$discuz_uid'");
		$spacelayout = implode("\t", $spacelayout);
		$spaceside = intval($spaceside);
		if(!array_key_exists($spacestyle, $spacestyles)) {
			showmessage('space_style_nofound', NULL, 'HALTED');
		}
		$spacestyle = addslashes($spacestyle);
		$db->query("UPDATE {$tablepre}memberspaces SET description= '".dhtmlspecialchars($spacedescription)."', layout='$spacelayout', side='$spaceside', style='$spacestyle' WHERE uid='$discuz_uid'");
		$db->query("DELETE FROM {$tablepre}spacecaches WHERE uid='$uid'");
		showmessage('space_setting_succeed', "space.php?$discuz_uid");
	}

	$spacename = $db->result_first("SELECT spacename FROM {$tablepre}memberfields WHERE uid='$discuz_uid'");
	$spacesettings = getspacesettings($discuz_uid);
	$spacesettings['name'] = $spacename;
	$spacepath = 'mspace/';
	$menulist = $modulelist = array();

	$spacesettings['layout'] = explode("\t", $spacesettings['layout']);
	foreach($spacesettings['layout'] as $k => $layoutitem) {
		$layout[$k] = explode('][', ']'.$layoutitem.'[');
		$layout[$k] = array_slice($layout[$k], 1, count($layout[$k])-2);
	}
	$menuarray = array_flip($listmodule);
	ksort($menuarray);
	$layoutjs = '\''.implode('\',\'', $spacesettings['layout']).'\'';

	include template('space_module');
	include template('memcp_spacemodule');

}

?>