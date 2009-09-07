<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_delete.php 7591 2008-06-13 10:29:21Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//删除评论
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
	
	//数据删除
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE cid IN (".simplode($newcids).")");

	//统计数据
	$nums = renum($blognums);
	foreach ($nums[0] as $num) {
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET replynum=replynum-$num WHERE blogid IN (".simplode($nums[1][$num]).")");
	}
	
	//积分
	if($spaces) {
		updatespaces($spaces, 'comment');
	}

	return $dels;
}

//删除博客
function deleteblogs($blogids) {
	global $_SGLOBAL;
	
	//获取博客信息
	$spaces = $blogs = $newblogids = array();
	$allowmanage = checkperm('manageblog');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('blog')." WHERE blogid IN (".simplode($blogids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			$blogs[] = $value;
			$newblogids[] = $value['blogid'];
			//需要更新统计
			//空间
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

	//空间积分
	updatespaces($spaces, 'blog');

	//数据删除
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blog')." WHERE blogid IN (".simplode($newblogids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blogfield')." WHERE blogid IN (".simplode($newblogids).")");
	
	//评论
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE id IN (".simplode($newblogids).") AND idtype='blogid'");
	
	return $blogs;
}

//删除事件
function deletefeeds($feedids) {
	global $_SGLOBAL;
	
	$allowmanage = checkperm('managefeed');

	$feeds = $newfeedids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE feedid IN (".simplode($feedids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {//管理员/作者
			$newfeedids[] = $value['feedid'];
			$feeds[] = $value;
		}
	}
	if(empty($newfeedids)) return array();
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('feed')." WHERE feedid IN (".simplode($newfeedids).")");

	return $feeds;
}


//删除迷你博客
function deletedoings($ids) {
	global $_SGLOBAL;
	
	$allowmanage = checkperm('managedoing');

	$doings = $newdoids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE doid IN (".simplode($ids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {//管理员/作者
			$newdoids[] = $value['doid'];
			$doings[] = $value;
		}
	}
	if(empty($newdoids)) return array();
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('doing')." WHERE doid IN (".simplode($newdoids).")");

	return $doings;
}

//删除话题
function deletethreads($tids) {
	global $_SGLOBAL;
	
	$tnums = $pnums = $delthreads = $newids = $spaces = array();
	$allowmanage = checkperm('managethread');
	
	$query = $_SGLOBAL['db']->query("SELECT t.*, mt.moderator FROM ".tname('thread')." t LEFT JOIN ".tname('mtag')." mt ON mt.tagid=t.tagid WHERE t.tid IN(".simplode($tids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid'] || ismoderator($value['moderator'])) {
			$newids[] = $value['tid'];
			$value['isthread'] = 1;
			$delthreads[] = $value;
			$spaces[$value['uid']]++;
		}
	}
	if(empty($delthreads)) return array();

	//删除
	$_SGLOBAL['db']->query("DELETE FROM ".tname('thread')." WHERE tid IN(".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE tid IN(".simplode($newids).")");
	
	//积分
	updatespaces($spaces, 'thread');
	
	return $delthreads;
}

//删除讨论
function deleteposts($pids) {
	global $_SGLOBAL;
	
	//统计
	$postnums = $mpostnums = $tids = $delposts = $newids = $spaces = array();
	$allowmanage = checkperm('managethread');
	
	$query = $_SGLOBAL['db']->query("SELECT p.*, mt.moderator FROM ".tname('post')." p LEFT JOIN ".tname('mtag')." mt ON mt.tagid=p.tagid WHERE p.pid IN (".simplode($pids).") ORDER BY p.isthread DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid'] || ismoderator($value['moderator'])) {
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
		$delthreads = deletethreads($tids);
	}
	if(empty($delposts)) {
		return $delthreads;
	}

	//整理
	$nums = renum($postnums);
	foreach ($nums[0] as $pnum) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET replynum=replynum-$pnum WHERE tid IN (".simplode($nums[1][$pnum]).")");
	}

	//删除
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE pid IN (".simplode($newids).")");
	
	//积分
	updatespaces($spaces, 'post');

	return $delposts;
}

//删除空间
function deletespace($uid) {
	global $_SGLOBAL, $_SC;
	
	$delspace = array();
	$allowmanage = checkperm('managespace');
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$uid'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage && $value['uid'] != $_SGLOBAL['supe_uid']) {
			$delspace = $value;
		}
	}
	if(empty($delspace)) return array();
	
	//space
	$_SGLOBAL['db']->query("DELETE FROM ".tname('space')." WHERE uid='$uid'");
	//spacefield
	$_SGLOBAL['db']->query("DELETE FROM ".tname('spacefield')." WHERE uid='$uid'");
	
	//feed
	$_SGLOBAL['db']->query("DELETE FROM ".tname('feed')." WHERE uid='$uid'");

	//数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('album')." WHERE uid='$uid'");
	
	//pic
	//删除图片附件
	$pics = array();
	$query = $_SGLOBAL['db']->query("SELECT filepath FROM ".tname('pic')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$pics[] = $value;
	}
	//数据
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
	//数据删除
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blog')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('blogfield')." WHERE uid='$uid'");

	//评论
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE authorid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE id='$uid' AND idtype='uid'");

	//class
	$_SGLOBAL['db']->query("DELETE FROM ".tname('class')." WHERE uid='$uid'");
	
	//friend
	//好友
	$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE uid='$uid'");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE fuid='$uid'");

	//member
	$_SGLOBAL['db']->query("DELETE FROM ".tname('member')." WHERE uid='$uid'");
	
	//mtag
	//thread
	//清空内容
	$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET message='' WHERE uid='$uid'");
	
	//session
	$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$uid'");
	
	//群组
	$mtagids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$mtagids[$value['tagid']] = $value['tagid'];
	}
	if($mtagids) {
		$_SGLOBAL['db']->query("UPDATE ".tname('mtag')." SET membernum=membernum-1 WHERE tagid IN (".simplode($mtagids).")");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE uid='$uid'");
	}

	//删除图片
	deletepicfiles($pics);//删除图片
	
	return $delspace;
}

//删除图片
function deletepics($picids) {
	global $_SGLOBAL, $_SC;
	
	$delpics = $albumnums = $newids = $sizes = $auids = $spaces = array();
	$allowmanage = checkperm('managealbum');
	
	$pics = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid IN (".simplode($picids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($allowmanage || $value['uid'] == $_SGLOBAL['supe_uid']) {
			//删除文件
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
	
	if($sizes) {
		$nums = renum($sizes);
		foreach ($nums[0] as $num) {
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize-$num WHERE uid IN (".simplode($nums[1][$num]).")");
		}
	}
	if($newids) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('pic')." WHERE picid IN (".simplode($newids).")");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('comment')." WHERE id IN (".simplode($newids).") AND idtype='picid'");
	}
	if($albumnums) {
		include_once(S_ROOT.'./source/function_cp.php');
		foreach ($albumnums as $id => $num) {
			$thepic = getalbumpic($auids[$id], $id);
			$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET pic='$thepic', picnum=picnum-$num WHERE albumid='$id'");
		}
	}
	
	//删除图片
	deletepicfiles($pics);

	return $delpics;
}

//删除图片文件
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
	//删除远程附件
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

//删除相册
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
	
	$_SGLOBAL['db']->query("DELETE FROM ".tname('album')." WHERE albumid IN (".simplode($newids).")");
	
	//删除图片
	if($pics) {
		deletepicfiles($pics);//删除图片
	}

	return $dels;
}

//删除tag
function deletetags($tagids) {
	global $_SGLOBAL;
	
	if(!checkperm('managetag')) return false;

	//数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE tagid IN (".simplode($tagids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tag')." WHERE tagid IN (".simplode($tagids).")");
	
	return true;
}

//删除mtag
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
	
	//数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('tagspace')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('mtag')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('thread')." WHERE tagid IN (".simplode($newids).")");
	$_SGLOBAL['db']->query("DELETE FROM ".tname('post')." WHERE tagid IN (".simplode($newids).")");

	return $dels;
}

//删除用户栏目
function deleteprofilefield($fieldids) {
	global $_SGLOBAL;

	if(!checkperm('manageprofilefield')) return false;
	
	//删除数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('profilefield')." WHERE fieldid IN (".simplode($fieldids).")");
	//更改表结构
	foreach ($fieldids as $id) {
		$_SGLOBAL['db']->query("ALTER TABLE ".tname('spacefield')." DROP `field_$id`", 'SILENT');
	}
	
	return true;
}

//删除栏目
function deleteprofield($fieldids) {
	global $_SGLOBAL;

	if(!checkperm('manageprofield')) return false;
	
	$dels = $newids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." WHERE fieldid IN (".simplode($fieldids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newids[] = $value['tagid'];
		$dels[] = $value;
	}
	//删除数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('profield')." WHERE fieldid IN (".simplode($fieldids).")");

	//mtag数据
	if($newids) deletemtag($newids);
	
	return true;
}

//广告删除
function deleteads($adids) {
	global $_SGLOBAL;

	if(!checkperm('managead')) return false;
	
	$dels = $newids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('ad')." WHERE adid IN (".simplode($adids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		//删除模板与模板编译文件
		$tpl = S_ROOT."./data/adtpl/$value[adid].htm";//原始
		swritefile($tpl, ' ');

		$newids[] = $value['adid'];
		$dels[] = $value;
	}
	if(empty($dels)) return array();
	
	//数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('ad')." WHERE adid IN (".simplode($newids).")");

	return $dels;
}

//模块删除
function deleteblocks($bids) {
	global $_SGLOBAL;

	if(!checkperm('managead')) return false;
	
	$dels = $newids = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('block')." WHERE bid IN (".simplode($bids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		//删除模板与模板编译文件
		$tpl = S_ROOT."./data/blocktpl/$value[bid].htm";//原始
		swritefile($tpl, ' ');

		$newids[] = $value['bid'];
		$dels[] = $value;
	}
	if(empty($dels)) return array();
	
	//数据
	$_SGLOBAL['db']->query("DELETE FROM ".tname('block')." WHERE bid IN (".simplode($newids).")");

	return $dels;
}

//更新空间
function updatespaces($spaces, $type) {
	global $_SGLOBAL;

	//空间数据
	if(!$credit = creditrule('pay', $type)) {
		return false;//删除不扣分
	}
	$nums = renum($spaces);
	foreach ($nums[0] as $num) {
		//积分
		$newcredit = $num * $credit;
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit-$newcredit WHERE uid IN (".simplode($nums[1][$num]).")");
		$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET credit=credit-$newcredit WHERE uid IN (".simplode($nums[1][$num]).")");
	}
}

?>