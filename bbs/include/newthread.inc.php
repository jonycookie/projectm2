<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: newthread.inc.php 11511 2007-12-07 03:13:02Z monkey $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$discuz_action = 11;

if(empty($forum['fid']) || $forum['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if(($special == 1 && !$allowpostpoll) || ($special == 2 && !$allowposttrade) || ($special == 3 && !$allowpostreward) || ($special == 4 && !$allowpostactivity) || ($special == 5 && !$allowpostdebate) || ($special == 6 && !$allowpostvideo)) {
	showmessage('group_nopermission', NULL, 'NOPERM');
}

$sgid = intval($sgid);
if($iscircle) {
        $mycircles = array();
        if($discuz_uid) {
        	supe_dbconnect();
                $query = $supe['db']->query("SELECT gid, groupname FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND flag>0", 'SILENT');
                while($mycircle = $supe['db']->fetch_array($query)) {
                        $mycircles[$mycircle['gid']] = cutstr($mycircle['groupname'], 30);
                }
        }
        if($sgid) {
		supe_dbconnect();
	        $query = $supe['db']->query("SELECT g.groupname, gf.headerimage, gf.css FROM {$supe[tablepre]}groups g, {$supe[tablepre]}groupfields gf WHERE g.gid='$sgid' AND g.flag=1 AND g.gid=gf.gid", 'SILENT');
	        $circle = $supe['db']->fetch_array($query);
	        if(!$discuz_uid || !$supe['db']->result($supe['db']->query("SELECT COUNT(*) FROM {$supe[tablepre]}groupuid WHERE uid='$discuz_uid' AND gid='$sgid' AND flag>0", 'SILENT'), 0)) {
	        	showmessage('circle_nopermission');
	        }
        }
}

if(!$discuz_uid && !((!$forum['postperm'] && $allowpost) || ($forum['postperm'] && forumperm($forum['postperm'])))) {
	showmessage('group_nopermission', NULL, 'NOPERM');
} elseif(empty($forum['allowpost'])) {
	if(!$forum['postperm'] && !$allowpost) {
		showmessage('group_nopermission', NULL, 'NOPERM');
	} elseif($forum['postperm'] && !forumperm($forum['postperm'])) {
		showmessage('post_forum_newthread_nopermission', NULL, 'HALTED');
	}
}

$isblog = empty($isblog) ? '' : 'yes';
if($isblog && (!$allowuseblog || !$forum['allowshare'])) {
	showmessage('post_newthread_blog_invalid', NULL, 'HALT');
}

if($url && !empty($qihoo['relate']['webnum'])) {
	$from = in_array($from, array('direct', 'iframe')) ? $from : '';
	if($data = @implode('', file("http://search.qihoo.com/sint/content.html?surl=$url&md5=$md5&ocs=$charset&ics=$charset&from=$from"))) {
		preg_match_all("/(\w+):([^\>]+)/i", $data, $data);
		if(!$data[2][1]) {
			$subject = trim($data[2][3]);
			$message = !$editormode ? str_replace('[br]', "\n", trim($data[2][4])) : str_replace('[br]', '<br />', trim($data[2][4]));
		} else {
			showmessage('reprint_invalid');
		}
	}
}

checklowerlimit($postcredits);

if(!submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {
	$special = !$allowspecialonly ? intval($special) : 'only';
	$modelid = $modelid ? intval($modelid) : '';
	$typeselect = typeselect($typeid, $special, '', $modelid);

	$icons = '';
	if(!$special && is_array($_DCACHE['icons'])) {
		$key = 1;
		foreach($_DCACHE['icons'] as $id => $icon) {
			$icons .= ' <input class="radio" type="radio" name="iconid" value="'.$id.'" /><img src="images/icons/'.$icon.'" alt="" />';
			$icons .= !(++$key % 10) ? '<br />' : '';
		}
	}

	if($special == 2 && $allowposttrade) {
		$expiration_7days = date('Y-m-d', $timestamp + 86400 * 7);
		$expiration_14days = date('Y-m-d', $timestamp + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));

		$tradetypeselect = '';
		$forum['tradetypes'] = $forum['tradetypes'] == '' ? -1 : unserialize($forum['tradetypes']);
		if($tradetypes && !empty($forum['tradetypes'])) {
			$tradetypeselect = '<select name="tradetypeid" onchange="ajaxget(\'post.php?action=threadtypes&tradetype=yes&typeid=\'+this.options[this.selectedIndex].value+\'&sid='.$sid.'\', \'threadtypes\', \'threadtypeswait\')"><option value="0">&nbsp;</option>';
			foreach($tradetypes as $typeid => $name) {
				if($forum['tradetypes'] == -1 || @in_array($typeid, $forum['tradetypes'])) {
					$tradetypeselect .= '<option value="'.$typeid.'">'.strip_tags($name).'</option>';
				}
			}
			$tradetypeselect .= '</select><span id="threadtypeswait"></span>';
		}

	} elseif($special == 6 && $allowpostvideo) {
		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='videoinfo'");
		$settings = unserialize($db->result($query, 0));
		$vclassesselect = "<style type=\"text/css\">#vclassesdiv {list-style: none;}\r\n";
		$vclassesselect .= "#vclassesdiv li{width: 80px; float: left;}\r\n";
		$vclassesselect .= '</style><ul id="vclassesdiv">';
		foreach($settings['vclasses'] as $key => $vclass) {
			if(in_array($key, $settings['vclassesable'])) {
				$vclassesselect .= '<li><input type="radio" class="radio" name="vclass" value="'.$key.'" '.$checked.'> '.$vclass.'</li>';
			}
		}
		$vclassesselect .= '</ul>';
	}
	if($special == 2) {
		include template('post_newthread_trade');
	} elseif($special == 4) {
		$activitytypelist = $activitytype ? explode("\n", trim($activitytype)) : '';
		include template('post_newthread_activity');
	} else {
		include template('post_newthread');
	}

} else {

	if($subject == '' || $message == '') {
		showmessage('post_sm_isnull');
	}

	if($post_invalid = checkpost()) {
		showmessage($post_invalid);
	}

	if(checkflood()) {
		showmessage('post_flood_ctrl');
	}

	if($allowpostattach && is_array($_FILES['attach'])) {
		foreach($_FILES['attach']['name'] as $attachname) {
			if($attachname != '') {
				checklowerlimit($postattachcredits);
				break;
			}
		}
	}

	$typeid = isset($typeid) && isset($forum['threadtypes']['types'][$typeid]) ? $typeid : 0;
	$iconid = !empty($iconid) && isset($_DCACHE['icons'][$iconid]) ? $iconid : 0;
	$displayorder = $modnewthreads ? -2 : (($forum['ismoderator'] && !empty($sticktopic)) ? 1 : 0);
	$digest = ($forum['ismoderator'] && !empty($addtodigest)) ? 1 : 0;
	$blog = $allowuseblog && $forum['allowshare'] && !empty($addtoblog) ? 1 : 0;
	$readperm = $allowsetreadperm ? $readperm : 0;
	$isanonymous = $isanonymous && $allowanonymous ? 1 : 0;
	$price = intval($price);
	$price = $maxprice && !$special ? ($price <= $maxprice ? $price : $maxprice) : 0;

	if(!$typeid && $forum['threadtypes']['required'] && !$special) {
		showmessage('post_type_isnull');
	}

	if($price > 0 && floor($price * (1 - $creditstax)) == 0) {
		showmessage('post_net_price_iszero');
	}

	if($special == 1) {

		$pollarray = array();
		$polloptions = explode("\n", $polloptions);
		foreach($polloptions as $key => $value) {
			if(!$value = trim($value)) {
				unset($polloptions[$key]);
			}
		}
		if(count($polloptions) > $maxpolloptions) {
			showmessage('post_poll_option_toomany');
		} elseif(count($polloptions) < 2) {
			showmessage('post_poll_inputmore');
		}

		$maxchoices = $maxchoices >= count($polloptions) ? count($polloptions) : $maxchoices;
		$pollarray['options'] = $polloptions;
		$pollarray['multiple'] = !empty($multiplepoll);
		$pollarray['visible'] = empty($visiblepoll);

		if(preg_match("/^\d*$/", trim($maxchoices)) && preg_match("/^\d*$/", trim($expiration))) {
			if(!$pollarray['multiple']) {
				$pollarray['maxchoices'] = 1;
			} elseif(empty($maxchoices)) {
				$pollarray['maxchoices'] = 0;
			} elseif($maxchoices == 1) {
				$pollarray['multiple'] = 0;
				$pollarray['maxchoices'] = $maxchoices;
			} else {
				$pollarray['maxchoices'] = $maxchoices;
			}
			if(empty($expiration)) {
				$pollarray['expiration'] = 0;
			} else {
				$pollarray['expiration'] = $timestamp + 86400 * $expiration;
			}
		} else {
			showmessage('poll_maxchoices_expiration_invalid');
		}

	} elseif($special == 3) {

		$rewardprice = intval($rewardprice);
		if($rewardprice < 1) {
			showmessage('reward_credits_please');
		} elseif($rewardprice > 32767) {
			showmessage('reward_credits_overflow');
		} elseif($rewardprice < $minrewardprice || ($maxrewardprice > 0 && $rewardprice > $maxrewardprice)) {
			showmessage('reward_credits_between');
		} elseif(($realprice = $rewardprice + ceil($rewardprice * $creditstax)) > $_DSESSION["extcredits$creditstrans"]) {
			showmessage('reward_credits_shortage');
		}

		$price = $rewardprice;

		$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans-$realprice WHERE uid='$discuz_uid'");

	} elseif($special == 4) {

		if(empty($starttimefrom[$activitytime])) {
			showmessage('activity_fromtime_please');
		} elseif(@strtotime($starttimefrom[$activitytime]) === -1 || @strtotime($starttimefrom[$activitytime]) === FALSE) {
			showmessage('activity_fromtime_error');
		} elseif(@strtotime($starttimefrom[$activitytime]) < $timestamp) {
			showmessage('activity_smaller_current');
		} elseif($activitytime && ((@strtotime($starttimefrom) > @strtotime($starttimeto) || !$starttimeto))) {
			showmessage('activity_fromtime_error');
		} elseif(!trim($activityclass)) {
			showmessage('activity_sort_please');
		} elseif(!trim($activityplace)) {
			showmessage('activity_address_please');
		} elseif(trim($activityexpiration) && (@strtotime($activityexpiration) === -1 || @strtotime($activityexpiration) === FALSE)) {
			showmessage('activity_totime_error');
		}

		$activity = array();
		$activity['class'] = dhtmlspecialchars(trim($activityclass));
		$activity['starttimefrom'] = @strtotime($starttimefrom[$activitytime]);
		$activity['starttimeto'] = $activitytime ? @strtotime($starttimeto) : 0;
		$activity['place'] = dhtmlspecialchars(trim($activityplace));
		$activity['cost'] = intval($cost);
		$activity['gender'] = intval($gender);
		$activity['number'] = intval($activitynumber);

		if($activityexpiration) {
			$activity['expiration'] = @strtotime($activityexpiration);
		} else {
			$activity['expiration'] = 0;
		}
		if(trim($activitycity)) {
			$subject .= '['.dhtmlspecialchars(trim($activitycity)).']';
		}

	} elseif($special == 5) {

		if(empty($affirmpoint) || empty($negapoint)) {
			showmessage('debate_position_nofound');
		} elseif(!empty($endtime) && (!($endtime = @strtotime($endtime)) || $endtime < $timestamp)) {
			showmessage('debate_endtime_invalid');
		} elseif(!empty($umpire)) {
			if(!$db->result_first("SELECT COUNT(*) FROM {$tablepre}members WHERE username='$umpire'")) {
				$umpire = dhtmlspecialchars($umpire);
				showmessage('debate_umpire_invalid');
			}
		}
		$affirmpoint = dhtmlspecialchars($affirmpoint);
		$negapoint = dhtmlspecialchars($negapoint);
		$stand = intval($stand);
	}

	$typeid = $special && $forum['threadtypes']['special'][$typeid] ? 0 : $typeid;
	$typeexpiration = intval($typeexpiration);

	if($forum['threadtypes']['expiration'][$typeid] && !$typeexpiration) {
		showmessage('threadtype_expiration_invalid');
	}

	$optiondata = array();
	if($forum['threadtypes']['special'][$typeid] && $typeoption && is_array($typeoption) && $checkoption && !$allowspecialonly) {
		$optiondata = threadtype_validator($typeoption);
	}

	$author = !$isanonymous ? $discuz_user : '';

	$moderated = $digest || $displayorder > 0 ? 1 : 0;

	$attachment = ($allowpostattach && $attachments = attach_upload()) ? 1 : 0;

	$subscribed = !empty($emailnotify) && $discuz_uid ? 1 : 0;

	$supe_pushstatus = $supe['status'] && $forum['supe_pushsetting']['status'] == 1 && !$modnewthreads ? '1' : '0';

	$sgidadd1 = $sgidadd2 = '';
	if($iscircle) {
		$sgidadd1 = ', sgid';
		$sgidadd2 = ", '$sgid'";
	}
	$db->query("INSERT INTO {$tablepre}threads (fid, readperm, price, iconid, typeid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, blog, special, attachment, subscribed, moderated, supe_pushstatus $sgidadd1)
		VALUES ('$fid', '$readperm', '$price', '$iconid', '$typeid', '$author', '$discuz_uid', '$subject', '$timestamp', '$timestamp', '$author', '$displayorder', '$digest', '$blog', '$special', '$attachment', '$subscribed', '$moderated', '$supe_pushstatus' $sgidadd2)");
	$tid = $db->insert_id();

	if($subscribed) {
		$db->query("REPLACE INTO {$tablepre}subscriptions (uid, tid, lastpost, lastnotify)
			VALUES ('$discuz_uid', '$tid', '$timestamp', '$timestamp')", 'UNBUFFERED');
	}

	if($special == 3 && $allowpostreward) {
		$db->query("INSERT INTO {$tablepre}rewardlog (tid, authorid, netamount, dateline) VALUES ('$tid', '$discuz_uid', $realprice, '$timestamp')");
	}

	$db->query("REPLACE INTO {$tablepre}mythreads (uid, tid, dateline, special) VALUES ('$discuz_uid', '$tid', '$timestamp', '$special')", 'UNBUFFERED');

	if($moderated) {
		updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
		updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
	}

	if($special == 1) {
		$db->query("INSERT INTO {$tablepre}polls (tid, multiple, visible, maxchoices, expiration)
			VALUES ('$tid', '$pollarray[multiple]', '$pollarray[visible]', '$pollarray[maxchoices]', '$pollarray[expiration]')");
		foreach($pollarray['options'] as $polloptvalue) {
			$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
			$db->query("INSERT INTO {$tablepre}polloptions (tid, polloption) VALUES ('$tid', '$polloptvalue')");
		}

	} elseif($special == 4 && $allowpostactivity) {
		$db->query("INSERT INTO {$tablepre}activities (tid, uid, cost, starttimefrom, starttimeto, place, class, gender, number, expiration)
			VALUES ('$tid', '$discuz_uid', '$activity[cost]', '$activity[starttimefrom]', '$activity[starttimeto]', '$activity[place]', '$activity[class]', '$activity[gender]', '$activity[number]', '$activity[expiration]')");

	} elseif($special == 5 && $allowpostdebate) {

		$db->query("INSERT INTO {$tablepre}debates (tid, uid, starttime, endtime, affirmdebaters, negadebaters, affirmvotes, negavotes, umpire, winner, bestdebater, affirmpoint, negapoint, umpirepoint)
			VALUES ('$tid', '$discuz_uid', '$timestamp', '$endtime', '0', '0', '0', '0', '$umpire', '', '', '$affirmpoint', '$negapoint', '')");

	} elseif($special == 6 && $allowpostvideo) {

		$vid = dhtmlspecialchars($vid);
		$vclass = intval($vclass);
		$visup = intval($visup);
		$vautoplay = intval($vautoplay);
		$code = urlencode(authcode("vid=$vid&isup=$visup&vautoplay=$vautoplay&vshare=$vshare&vtitle=$subjectu8&vtag=$tagsu8&vclass=$vclass", 'ENCODE', $vkey));
		$returninfo = dfopen("http://union.bokecc.com/discuz2/addv.bo?siteid=$vsiteid&code=$code");

		list($vthumb, $shareurl) = explode(',', $returninfo);
		$vthumb = dhtmlspecialchars(addslashes($vthumb));
		$query = $db->query("INSERT INTO {$tablepre}videos (vid, tid, uid, dateline, vthumb, vtitle, vclass, visup, vautoplay)
			VALUES ('$vid', '$tid', '$discuz_uid', '$timestamp', '$vthumb', '$subject', '$vclass', '$visup', '$vautoplay')", 'SILENT');
		$videotags = preg_split("/[\s,]+/", str_replace(array(chr(0xa1).chr(0xa1), chr(0xa1).chr(0x40), chr(0xe3).chr(0x80).chr(0x80)), '', $tags));
		if($videotags) {
			$i = 0;
			foreach($videotags as $videotag) {
				if($i++ > 5) {
					break;
				}
				$videotag = trim($videotag);
				if(preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $videotag)) {
					$vid && $vid != '-1' && $db->query("INSERT INTO {$tablepre}videotags(tagname, vid) VALUES ('$videotag', '$vid')", 'SILENT');
				}
			}
		}

	}

	if($forum['threadtypes']['special'][$typeid] && $optiondata && is_array($optiondata)) {
		foreach($optiondata as $optionid => $value) {
			$db->query("INSERT INTO {$tablepre}typeoptionvars (typeid, tid, optionid, value, expiration)
				VALUES ('$typeid', '$tid', '$optionid', '$value', '".($typeexpiration ? $timestamp + $typeexpiration : 0)."')");
		}
	}

	$bbcodeoff = checkbbcodes($message, !empty($bbcodeoff));
	$smileyoff = checksmilies($message, !empty($smileyoff));
	$parseurloff = !empty($parseurloff);
	$htmlon = bindec(($tagstatus && !empty($tagoff) ? 1 : 0).($allowhtml && !empty($htmlon) ? 1 : 0));

	$pinvisible = $modnewthreads ? -2 : 0;
	$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip, invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff, attachment)
		VALUES ('$fid', '$tid', '1', '$discuz_user', '$discuz_uid', '$subject', '$timestamp', '$message', '$onlineip', '$pinvisible', '$isanonymous', '$usesig', '$htmlon', '$bbcodeoff', '$smileyoff', '$parseurloff', '$attachment')");
	$pid = $db->insert_id();

	if($tagstatus && $tags != '') {
		$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
		if(strexists($tags, ',')) {
			$tagarray = array_unique(explode(',', $tags));
		} else {
			$tags = str_replace(array(chr(0xa1).chr(0xa1), chr(0xa1).chr(0x40), chr(0xe3).chr(0x80).chr(0x80)), ' ', $tags);
			$tagarray = array_unique(explode(' ', $tags));
		}
		$tagcount = 0;
		foreach($tagarray as $tagname) {
			$tagname = trim($tagname);
			if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $tagname)) {
				$query = $db->query("SELECT closed FROM {$tablepre}tags WHERE tagname='$tagname'");
				if($db->num_rows($query)) {
					if(!$tagstatus = $db->result($query, 0)) {
						$db->query("UPDATE {$tablepre}tags SET total=total+1 WHERE tagname='$tagname'", 'UNBUFFERED');
					}
				} else {
					$db->query("INSERT INTO {$tablepre}tags (tagname, closed, total)
						VALUES ('$tagname', 0, 1)", 'UNBUFFERED');
					$tagstatus = 0;
				}
				if(!$tagstatus) {
					$db->query("INSERT {$tablepre}threadtags (tagname, tid) VALUES ('$tagname', $tid)", 'UNBUFFERED');
				}
				$tagcount++;
				if($tagcount > 4) {
					unset($tagarray);
					break;
				}
			}
		}
	}

	$tradeaid = 0;
	if($attachment) {
		$searcharray = $pregarray = $replacearray = array();
		foreach($attachments as $key => $attach) {
			$db->query("INSERT INTO {$tablepre}attachments (tid, pid, dateline, readperm, price, filename, description, filetype, filesize, attachment, downloads, isimage, uid, thumb, remote)
				VALUES ('$tid', '$pid', '$timestamp', '$attach[perm]', '$attach[price]', '$attach[name]', '$attach[description]', '$attach[type]', '$attach[size]', '$attach[attachment]', '0', '$attach[isimage]', '$attach[uid]', '$attach[thumb]', '$attach[remote]')");
			$searcharray[] = '[local]'.$localid[$key].'[/local]';
			$pregarray[] = '/\[localimg=(\d{1,3}),(\d{1,3})\]'.$localid[$key].'\[\/localimg\]/is';
			$replacearray[] = '[attach]'.$db->insert_id().'[/attach]';
		}
		$message = str_replace($searcharray, $replacearray, preg_replace($pregarray, $replacearray, $message));
		$db->query("UPDATE {$tablepre}posts SET message='$message' WHERE pid='$pid'");
		updatecredits($discuz_uid, $postattachcredits, count($attachments));
	}

	if($iscircle && $sgid) {
		supe_dbconnect();
		$query = $supe['db']->query("UPDATE {$supe[tablepre]}groups SET lastpost='$timestamp' WHERE gid='$sgid'", 'SILENT');
	}

	if($modnewthreads) {

		$db->query("UPDATE {$tablepre}forums SET todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
		$allowuseblog && $isblog && $blog ? showmessage('post_newthread_mod_blog_succeed', "blog.php?uid=$discuz_uid") :
			showmessage('post_newthread_mod_succeed', "forumdisplay.php?fid=$fid");

	} else {

		if($digest) {
			foreach($digestcredits as $id => $addcredits) {
				$postcredits[$id] = (isset($postcredits[$id]) ? $postcredits[$id] : 0) + $addcredits;
			}
		}
		updatepostcredits('+', $discuz_uid, $postcredits);

		$subject = str_replace("\t", ' ', $subject);
		$lastpost = "$tid\t$subject\t$timestamp\t$author";
		$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$fid'", 'UNBUFFERED');
		if($forum['type'] == 'sub') {
			$db->query("UPDATE {$tablepre}forums SET lastpost='$lastpost' WHERE fid='$forum[fup]'", 'UNBUFFERED');
		}

		if($allowuseblog && $isblog && $blog) {
			showmessage('post_newthread_blog_succeed', "blog.php?tid=$tid");
		} else {
			showmessage('post_newthread_succeed', "viewthread.php?tid=$tid&extra=$extra".(!empty($frombbs) ? "&frombbs=$frombbs" : ''));
		}

	}

}

?>