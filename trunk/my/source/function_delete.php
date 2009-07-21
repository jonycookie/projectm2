<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_delete.php 10665 2008-12-12 03:19:56Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//ɾ������
function deletecomments($cids) {
	global $_SGLOBAL;

	$blognums = $spaces = $newcids = $dels = array();
	$allowmanage = checkperm('managecomment');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE cid IN (".simplode($cids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['authorid'] == $_SGLOBAL['supe_uid'] || $value['uid'] == $_SGLOBAL['supe_uid']) {
			$newcids[] = $value['cid'];
			if($value['idtype'] == 'blogid') {
				$blognums[$value['id']]++;
			}
			if($value['authorid'] != $value['uid']) {
				$spaces[$value['authorid']]++;
			}
			$dels[] = $value;
		}
	}
	if(empty($dels)) return array();

	//����ɾ��
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE cid IN (".simplode($newcids).")");

	//ͳ������
	$nums = renum($blognums);
	foreach ($nums[0] as $num) {
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET replynum=replynum-$num WHERE blogid IN (".simplode($nums[1][$num]).")");
	}

	//����
	if($spaces) {
		updatespaces($spaces, 'comment');
	}

	return $dels;
}

//ɾ������
function deleteblogs($blogids) {
	global $_SGLOBAL;

	//��ȡ������Ϣ
	$spaces = $blogs = $newblogids = array();
	$allowmanage = checkperm('manageblog');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE blogid IN (".simplode($blogids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			$blogs[] = $value;
			$newblogids[] = $value['blogid'];
			//��Ҫ����ͳ��
			//�ռ�
			$spaces[$value['uid']]++;
			//tag
			$tags = array();
			$subquery = $_SGLOBAL['db']->query("SELECT tagid, blogid FROM ".tname('tagblog')." WHERE blogid='$value[blogid]'");
			while ($tag = $_SGLOBAL['db']->fetch_array($subquery)) {
				$tags[] = $tag['tagid'];
			}
			if($tags) {
				$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum=blognum-1 WHERE tagid IN (".simplode($tags).")");
				$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE blogid='$value[blogid]'");
			}
		}
	}
	if(empty($blogs)) return array();

	//�ռ����
	updatespaces($spaces, 'blog');

	//����ɾ��
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blog')." WHERE blogid IN (".simplode($newblogids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blogfield')." WHERE blogid IN (".simplode($newblogids).")");

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE id IN (".simplode($newblogids).") AND idtype='blogid'");
	
	//ɾ���ٱ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id IN (".simplode($newblogids).") AND idtype='blog'");
	
	//ɾ����ӡ
	$_SGLOBAL['db']->query("DELETE FROM ".tname('trace')." WHERE blogid IN (".simplode($newblogids).")");

	return $blogs;
}

//ɾ���¼�
function deletefeeds($feedids) {
	global $_SGLOBAL;

	$allowmanage = checkperm('managefeed');

	$feeds = $newfeedids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE feedid IN (".simplode($feedids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {//����Ա/����
			$newfeedids[] = $value['feedid'];
			$feeds[] = $value;
		}
	}
	if(empty($newfeedids)) return array();

	$_SGLOBAL['db']->query("DELETE FROM ".tname('feed')." WHERE feedid IN (".simplode($newfeedids).")");

	return $feeds;
}


//ɾ������
function deleteshares($sids) {
	global $_SGLOBAL;

	$allowmanage = checkperm('manageshare');

	$shares = $newsids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid IN (".simplode($sids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {//����Ա/����
			$newsids[] = $value['sid'];
			$shares[] = $value;
		}
	}
	if(empty($newsids)) return array();

	$_SGLOBAL['db']->query("DELETE FROM ".tname('share')." WHERE sid IN (".simplode($newsids).")");
	
	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE id IN (".simplode($newsids).") AND idtype='sid'");
	
	//ɾ���ٱ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id IN (".simplode($newsids).") AND idtype='share'");
	return $shares;
}


//ɾ����¼
function deletedoings($ids) {
	global $_SGLOBAL;

	$allowmanage = checkperm('managedoing');
	$doings = $newdoids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE doid IN (".simplode($ids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {//����Ա/����
			$newdoids[] = $value['doid'];
			$doings[] = $value;
		}
	}
	if(empty($newdoids)) return array();

	$_SGLOBAL['db']->query("DELETE FROM ".tname('doing')." WHERE doid IN (".simplode($newdoids).")");
	//ɾ������
	$_SGLOBAL['db']->query("DELETE FROM ".tname('docomment')." WHERE doid IN (".simplode($newdoids).")");

	return $doings;
}

//ɾ������
function deletethreads($tagid, $tids) {
	global $_SGLOBAL;

	$tnums = $pnums = $delthreads = $newids = $spaces = array();
	$allowmanage = checkperm('managethread');

	//Ⱥ��
	$wheresql = '';
	if(empty($allowmanage) && $tagid) {
		$mtag = getmtag($tagid);
		if($mtag['grade'] >=8) {
			$allowmanage = 1;
			$wheresql = " AND t.tagid='$tagid'";
		}
	}

	$query = $_SGLOBAL['db']->query("SELECT t.* FROM ".tname('thread')." t WHERE t.tid IN(".simplode($tids).") $wheresql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			$newids[] = $value['tid'];
			$value['isthread'] = 1;
			$delthreads[] = $value;
			$spaces[$value['uid']]++;
		}
	}
	if(empty($delthreads)) return array();

	//ɾ��
	$_SGLOBAL['db']->query("DELETE FROM ".tname('thread')." WHERE tid IN(".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE tid IN(".simplode($newids).")");
	
	//ɾ���ٱ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id IN (".simplode($newids).") AND idtype='thread'");

	//����
	updatespaces($spaces, 'thread');

	return $delthreads;
}

//ɾ������
function deleteposts($tagid, $pids) {
	global $_SGLOBAL;

	//ͳ��
	$postnums = $mpostnums = $tids = $delposts = $newids = $spaces = array();
	$allowmanage = checkperm('managethread');

	//Ⱥ��
	$wheresql = '';
	if(empty($allowmanage) && $tagid) {
		$mtag = getmtag($tagid);
		if($mtag['grade'] >=8) {
			$allowmanage = 1;
			$wheresql = " AND p.tagid='$tagid'";
		}
	}

	$query = $_SGLOBAL['db']->query("SELECT p.* FROM ".tname('post')." p WHERE p.pid IN (".simplode($pids).") $wheresql ORDER BY p.isthread DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			if($value['isthread']) {
				$tids[] = $value['tid'];
			} else {
				if(!in_array($value['tid'], $tids)) {
					$newids[] = $value['pid'];
					$delposts[] = $value;
					$postnums[$value['tid']]++;
					$spaces[$value['uid']]++;
				}
			}
		}
	}
	$delthreads = array();
	if($tids) {
		$delthreads = deletethreads($tagid, $tids);
	}
	if(empty($delposts)) {
		return $delthreads;
	}

	//����
	$nums = renum($postnums);
	foreach ($nums[0] as $pnum) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET replynum=replynum-$pnum WHERE tid IN (".simplode($nums[1][$pnum]).")");
	}

	//ɾ��
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE pid IN (".simplode($newids).")");

	//����
	updatespaces($spaces, 'post');

	return $delposts;
}

//ɾ���ռ�
function deletespace($uid, $force=0) {
	global $_SGLOBAL, $_SC, $_SCONFIG;

	$delspace = array();
	$allowmanage = checkperm('managespace');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$uid'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($force || $allowmanage && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$delspace = $value;
			//�������ǿ��ɾ������ɾ����¼��
			if(!$force) {
				$setarr = array(
					'uid' => $value['uid'],
					'username' => saddslashes($value['username']),
					'opuid' => $_SGLOBAL['supe_uid'],
					'opusername' => $_SGLOBAL['supe_username'],
					'flag' => '-1',
					'dateline' => $_SGLOBAL['timestamp']
				);
				inserttable('spacelog', $setarr, 0, true);
			}
		}
	}
	if(empty($delspace)) return array();

	//space
	$_SGLOBAL['db']->query("DELETE FROM ".tname('space')." WHERE uid='$uid'");
	//spacefield
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spacefield')." WHERE uid='$uid'");

	//feed
	$_SGLOBAL['db']->query("DELETE FROM ".tname('feed')." WHERE uid='$uid'");

	//��¼
	$_SGLOBAL['db']->query("DELETE FROM ".tname('doing')." WHERE uid='$uid'");
	
	//ɾ����¼�ظ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('docomment')." WHERE uid='$uid'");
	
	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('share')." WHERE uid='$uid'");

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('album')." WHERE uid='$uid'");
	
	//ɾ��֪ͨ
	$_SGLOBAL['db']->query("DELETE FROM ".tname('notification')." WHERE (uid='$uid' OR authorid='$uid')");
	
	//ɾ�����к�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('poke')." WHERE (uid='$uid' OR fromuid='$uid')");
	
	//pic
	//ɾ��ͼƬ����
	$pics = array();
	$query = $_SGLOBAL['db']->query("SELECT filepath FROM ".tname('pic')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$pics[] = $value;
	}
	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('pic')." WHERE uid='$uid'");

	//blog
	$blogids = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid FROM ".tname('blog')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$blogids[$value['blogid']] = $value['blogid'];
		//tag
		$tags = array();
		$subquery = $_SGLOBAL['db']->query("SELECT tagid, blogid FROM ".tname('tagblog')." WHERE blogid='$value[blogid]'");
		while ($tag = $_SGLOBAL['db']->fetch_array($subquery)) {
			$tags[$tag['tagid']] = $tag['tagid'];
		}
		if($tags) {
			$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum=blognum-1 WHERE tagid IN (".simplode($tags).")");
			$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE blogid='$value[blogid]'");
		}
	}
	//����ɾ��
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blog')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blogfield')." WHERE uid='$uid'");

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE (uid='$uid' OR authorid='$uid' OR (id='$uid' AND idtype='uid'))");

	//�ÿ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('visitor')." WHERE (uid='$uid' OR vuid='$uid')");
	
	//ɾ�����¼
	$_SGLOBAL['db']->query("DELETE FROM ".tname('usertask')." WHERE uid='$uid'");

	//class
	$_SGLOBAL['db']->query("DELETE FROM ".tname('class')." WHERE uid='$uid'");

	//friend
	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE (uid='$uid' OR fuid='$uid')");

	//member
	$_SGLOBAL['db']->query("DELETE FROM ".tname('member')." WHERE uid='$uid'");
	
	//ɾ����ӡ
	$_SGLOBAL['db']->query("DELETE FROM ".tname('trace')." WHERE uid='$uid'");
	
	//ɾ��������
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blacklist')." WHERE (uid='$uid' OR buid='$uid')");
	
	//ɾ�������¼
	$_SGLOBAL['db']->query("DELETE FROM ".tname('invite')." WHERE (uid='$uid' OR fuid='$uid')");
	
	//ɾ���ʼ�����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mailcron').", ".tname('mailqueue')." USING ".tname('mailcron').", ".tname('mailqueue')." WHERE ".tname('mailcron').".touid='$uid' AND ".tname('mailcron').".cid=".tname('mailqueue').".cid");

	//��������
	$_SGLOBAL['db']->query("DELETE FROM ".tname('myinvite')." WHERE (touid='$uid' OR fromuid='$uid')");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('userapp')." WHERE uid='$uid'");
	
	//mtag
	//thread
	$tids = array();
	$query = $_SGLOBAL['db']->query("SELECT tid, tagid FROM ".tname('thread')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tids[$value['tagid']][] = $value['tid'];
	}
	foreach ($tids as $tagid => $v_tids) {
		deletethreads($tagid, $v_tids);
	}

	//post
	$pids = array();
	$query = $_SGLOBAL['db']->query("SELECT pid, tagid FROM ".tname('post')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$pids[$value['tagid']][] = $value['pid'];
	}
	foreach ($pids as $tagid => $v_pids) {
		deleteposts($tagid, $v_pids);
	}
	$_SGLOBAL['db']->query("DELETE FROM ".tname('thread')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE uid='$uid'");

	//session
	$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$uid'");
	
	//���а�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('show')." WHERE uid='$uid'");

	//Ⱥ��
	$mtagids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$mtagids[$value['tagid']] = $value['tagid'];
	}
	if($mtagids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum-1 WHERE tagid IN (".simplode($mtagids).")");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE uid='$uid'");
	}
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtaginvite')." WHERE (uid='$uid' OR fromuid='$uid')");
	
	//ɾ��ͼƬ
	deletepicfiles($pics);//ɾ��ͼƬ
	//ɾ���ٱ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id='$uid' AND idtype='space'");
	//�����¼
	if($_SCONFIG['my_status']) inserttable('userlog', array('uid'=>$uid, 'action'=>'delete', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);

	return $delspace;
}

//ɾ��ͼƬ
function deletepics($picids) {
	global $_SGLOBAL, $_SC;

	$delpics = $albumnums = $newids = $sizes = $auids = $spaces = array();
	$allowmanage = checkperm('managealbum');

	$pics = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid IN (".simplode($picids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			//ɾ���ļ�
			$pics[] = $value;
			$newids[] = $value['picid'];
			$delpics[] = $value;
			$allsize = $allsize + $value['size'];
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			if($value['albumid']) {
				$auids[$value['albumid']] = $value['uid'];
				$albumnums[$value['albumid']]++;
			}
			$spaces[$value['uid']]++;
		}
	}
	if(empty($delpics)) return array();

	$pic_credit = creditrule('pay', 'pic');

	foreach ($spaces as $uid => $picnum) {
		$attachsize = intval($sizes[$uid]);
		$setsql = $pic_credit?(",credit=credit-".($picnum*$pic_credit)):"";
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize-$attachsize $setsql WHERE uid='$uid'");
	}

	if($newids) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('pic')." WHERE picid IN (".simplode($newids).")");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE id IN (".simplode($newids).") AND idtype='picid'");
		
		//ɾ���ٱ�
		$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id IN (".simplode($newids).") AND idtype='picid'");
	}
	if($albumnums) {
		include_once(S_ROOT.'./source/function_cp.php');
		foreach ($albumnums as $id => $num) {
			$thepic = getalbumpic($auids[$id], $id);
			$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET pic='$thepic', picnum=picnum-$num WHERE albumid='$id'");
		}
	}

	//ɾ��ͼƬ
	deletepicfiles($pics);

	return $delpics;
}

//ɾ��ͼƬ�ļ�
function deletepicfiles($pics) {
	global $_SGLOBAL, $_SC;
	$remotes = array();
	foreach ($pics as $pic) {
		if($pic['remote']) {
			$remotes[] = $pic;
		} else {
			$file = $_SC['attachdir'].'./'.$pic['filepath'];
			if(!@unlink($file)) {
				runlog('PIC', "Delete pic file '$file' error.", 0);
			}
			if($pic['thumb']) {
				if(!@unlink($file.'.thumb.jpg')) {
					runlog('PIC', "Delete pic file '{$file}.thumb.jpg' error.", 0);
				}
			}
		}
	}
	//ɾ��Զ�̸���
	if($remotes) {
		include_once(S_ROOT.'./data/data_setting.php');
		include_once(S_ROOT.'./source/function_ftp.php');
		$ftpconnid = sftp_connect();
		foreach ($remotes as $pic) {
			$file = $pic['filepath'];
			if($ftpconnid) {
				if(!sftp_delete($ftpconnid, $file)) {
					runlog('FTP', "Delete pic file '$file' error.", 0);
				}
				if($pic['thumb'] && !sftp_delete($ftpconnid, $file.'.thumb.jpg')) {
					runlog('FTP', "Delete pic file '{$file}.thumb.jpg' error.", 0);
				}
			} else {
				runlog('FTP', "Delete pic file '$file' error.", 0);
				if($pic['thumb']) {
					runlog('FTP', "Delete pic file '{$file}.thumb.jpg' error.", 0);
				}
			}
		}
	}
}

//ɾ�����
function deletealbums($albumids) {
	global $_SGLOBAL, $_SC;

	$dels = $newids = $sizes = $spaces = array();
	$allowmanage = checkperm('managealbum');

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid IN (".simplode($albumids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			$dels[] = $value;
			$newids[] = $value['albumid'];
		}
	}
	if(empty($dels)) return array();

	$pics = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid IN (".simplode($newids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
		$pics[] = $value;
		$spaces[$value['uid']]++;
	}

	if($sizes) {
		$nums = renum($sizes);
		foreach ($nums[0] as $num) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize-$num WHERE uid IN (".simplode($nums[1][$num]).")");
		}
		$_SGLOBAL['db']->query("DELETE FROM ".tname('pic')." WHERE albumid IN (".simplode($newids).")");
	}
	
	//����
	$pic_credit = creditrule('pay', 'pic');
	if($pic_credit && $spaces) {
		$nums = renum($spaces);
		foreach ($nums[0] as $num) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-".($pic_credit*$num)." WHERE uid IN (".simplode($nums[1][$num]).")");
		}
	}

	$_SGLOBAL['db']->query("DELETE FROM ".tname('album')." WHERE albumid IN (".simplode($newids).")");
	//ɾ���ٱ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id IN (".simplode($newids).") AND idtype='album'");

	//ɾ��ͼƬ
	if($pics) {
		deletepicfiles($pics);//ɾ��ͼƬ
	}

	return $dels;
}

//ɾ��tag
function deletetags($tagids) {
	global $_SGLOBAL;

	if(!checkperm('managetag')) return false;

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).")");

	return true;
}

//ɾ��mtag
function deletemtag($tagids) {
	global $_SGLOBAL;

	if(!checkperm('manageprofield') && !checkperm('managemtag')) return array();

	$dels = $newids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE tagid IN (".simplode($tagids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newids[] = $value['tagid'];
		$dels[] = $value;
	}
	if(empty($newids)) return array();

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('thread')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtaginvite')." WHERE tagid IN (".simplode($newids).")");
	
	//ɾ���ٱ�
	$_SGLOBAL['db']->query("DELETE FROM ".tname('report')." WHERE id IN (".simplode($newids).") AND idtype='mtag'");
	return $dels;
}

//ɾ���û���Ŀ
function deleteprofilefield($fieldids) {
	global $_SGLOBAL;

	if(!checkperm('manageprofilefield')) return false;

	//ɾ������
	$_SGLOBAL['db']->query("DELETE FROM ".tname('profilefield')." WHERE fieldid IN (".simplode($fieldids).")");
	//���ı�ṹ
	foreach ($fieldids as $id) {
		$_SGLOBAL['db']->query("ALTER TABLE ".tname('spacefield')." DROP `field_$id`", 'SILENT');
	}

	return true;
}

//ɾ����Ŀ
function deleteprofield($fieldids, $newfieldid) {
	global $_SGLOBAL;

	if(!checkperm('manageprofield')) return false;

	//ɾ������
	$_SGLOBAL['db']->query("DELETE FROM ".tname('profield')." WHERE fieldid IN (".simplode($fieldids).")");

	//������Ŀ
	$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET fieldid='$newfieldid' WHERE fieldid IN (".simplode($fieldids).")");

	return true;
}

//���ɾ��
function deleteads($adids) {
	global $_SGLOBAL;

	if(!checkperm('managead')) return false;

	$dels = $newids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('ad')." WHERE adid IN (".simplode($adids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		//ɾ��ģ����ģ������ļ�
		$tpl = S_ROOT."./data/adtpl/$value[adid].htm";//ԭʼ
		swritefile($tpl, ' ');

		$newids[] = $value['adid'];
		$dels[] = $value;
	}
	if(empty($dels)) return array();

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('ad')." WHERE adid IN (".simplode($newids).")");

	return $dels;
}

//ģ��ɾ��
function deleteblocks($bids) {
	global $_SGLOBAL;

	if(!checkperm('managead')) return false;

	$dels = $newids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('block')." WHERE bid IN (".simplode($bids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		//ɾ��ģ����ģ������ļ�
		$tpl = S_ROOT."./data/blocktpl/$value[bid].htm";//ԭʼ
		swritefile($tpl, ' ');

		$newids[] = $value['bid'];
		$dels[] = $value;
	}
	if(empty($dels)) return array();

	//����
	$_SGLOBAL['db']->query("DELETE FROM ".tname('block')." WHERE bid IN (".simplode($newids).")");

	return $dels;
}

//���¿ռ�
function updatespaces($spaces, $type) {
	global $_SGLOBAL;

	//�ռ�����
	if(!$credit = creditrule('pay', $type)) {
		return false;//ɾ�����۷�
	}
	$nums = renum($spaces);
	foreach ($nums[0] as $num) {
		//����
		$newcredit = $num * $credit;
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$newcredit WHERE uid IN (".simplode($nums[1][$num]).")");
	}
}

?>