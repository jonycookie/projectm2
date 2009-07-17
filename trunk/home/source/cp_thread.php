<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_thread.php 10586 2008-12-10 06:53:47Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

include_once(S_ROOT.'./source/function_bbcode.php');
include_once(S_ROOT.'./source/function_blog.php');
	
if(submitcheck('threadsubmit')) {

	$tid = $_POST['tid'] = intval($_POST['tid']);
	$tagid = empty($_POST['tagid'])?0:intval($_POST['tagid']);
	
	if(empty($_POST['tid'])) {
		//���
		if(!checkperm('allowthread')) {
			showmessage('no_privilege');
		}
		
		//��֤��
		if(checkperm('seccode') && !ckseccode($_POST['seccode'])) {
			showmessage('incorrect_code');
		}
		
		//ʵ����֤
		ckrealname('thread');
		
		//���û���ϰ
		cknewuser();
		
		//���»���
		$mtag = ckmtagspace($tagid);
	
		//�ж��Ƿ����̫��
		$waittime = interval_check('post');
		if($waittime > 0) {
			showmessage('operating_too_fast','',1,array($waittime));
		}
	} else {
		//���Ⱥ��
		$mtag = ckmtagspace($tagid);
	}

	$subject = getstr($_POST['subject'], 80, 1, 1, 1);
	if(strlen($subject) < 2) showmessage('title_not_too_little');
	
	$_POST['message'] = checkhtml($_POST['message']);
	$_POST['message'] = getstr($_POST['message'], 0, 1, 0, 1, 0, 1);
	$_POST['message'] = preg_replace("/\<div\>\<\/div\>/i", '', $_POST['message']);	
	$message = $_POST['message'];
	
	//����ͼƬ
	$titlepic = '';
	
	//��ȡ�ϴ���ͼƬ
	$uploads = array();
	if(!empty($_POST['picids'])) {
		$picids = array_keys($_POST['picids']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid IN (".simplode($picids).") AND uid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(empty($titlepic) && $value['thumb']) {
				$titlepic = mkpicurl($value, 1);
			}
			$uploads[$_POST['picids'][$value['picid']]] = $value;
		}
		if(empty($titlepic) && $value) {
			$titlepic = mkpicurl($value, 0);
		}
	}
	
	//��������
	if($uploads) {
		preg_match_all("/\<img\s.*?\_uchome\_localimg\_([0-9]+).+?src\=\"(.+?)\"/i", $message, $mathes);
		if(!empty($mathes[1])) {
			$searchs = $idsearchs = array();
			$replaces = array();
			foreach ($mathes[1] as $key => $value) {
				if(!empty($mathes[2][$key]) && !empty($uploads[$value])) {
					$searchs[] = $mathes[2][$key];
					$idsearchs[] = "_uchome_localimg_$value";
					$replaces[] = mkpicurl($uploads[$value], 0);
					unset($uploads[$value]);
				}
			}
			if($searchs) {
				$message = str_replace($searchs, $replaces, $message);
				$message = str_replace($idsearchs, 'uchomelocalimg[]', $message);
			}
		}
		//δ��������
		foreach ($uploads as $value) {
			$picurl = mkpicurl($value, 0);
			$message .= "<div class=\"uchome-message-pic\"><img src=\"$picurl\"><p>$value[title]</p></div>";
		}
	}
	
	//û����д�κζ���
	$ckmessage = preg_replace("/(\<div\>|\<\/div\>|\s)+/is", '', $message);
	if(strlen($message) < 2) {
		showmessage('content_is_not_less_than_four_characters');
	}
	
	//���slashes
	$message = addslashes($message);
	
	if(empty($_POST['tid'])) {
		//�������ж�ȡͼƬ
		if(empty($titlepic)) {
			$titlepic = getmessagepic($message);
		}
		$setarr = array(
			'tagid' => $tagid,
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'dateline' => $_SGLOBAL['timestamp'],
			'subject' => $subject,
			'lastpost' => $_SGLOBAL['timestamp'],
			'lastauthor' => $_SGLOBAL['supe_username'],
			'lastauthorid' => $_SGLOBAL['supe_uid']
		);
		$tid = inserttable('thread', $setarr, 1);
		$psetarr = array(
			'tagid' => $tagid,
			'tid' => $tid,
			'uid' => $_SGLOBAL['supe_uid'],
			'username' => $_SGLOBAL['supe_username'],
			'ip' => getonlineip(),
			'dateline' => $_SGLOBAL['timestamp'],
			'message' => $message,
			'isthread' => 1
		);
		//���
		inserttable('post', $psetarr);
		
		//����
		updatespacestatus('get', 'thread');
	
		//�¼�
		if(empty($mtag['viewperm'])) {
			$fs = array();
			$fs['icon'] = 'thread';
		
			$fs['title_template'] = cplang('feed_thread');
			$fs['title_data'] = array();
		
			$fs['body_template'] = '<b>{subject}</b><br>'.cplang('mtag').': {mtag}<br>{summary}';
			$fs['body_data'] = array(
				'subject' => "<a href=\"space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid\">$setarr[subject]</a>",
				'mtag' => "<a href=\"space.php?do=mtag&tagid=$mtag[tagid]\">$mtag[tagname]</a>",
				'summary' => getstr($message, 150, 1, 1, 0, 0, -1)
			);
			$fs['body_general'] = '';
		
			if($titlepic) {
				$fs['images'] = array($titlepic);
				$fs['image_links'] = array("space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid");
			}
		
			if(ckprivacy('thread', 1)) {
				feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links']);
			}
		}
	} else {
		$setarr = array(
			'tagid' => $tagid,
			'dateline' => $_SGLOBAL['timestamp'],
			'subject' => $subject
		);
		updatetable('thread', $setarr, array('tid'=>$_POST['tid'], 'uid'=>$_SGLOBAL['supe_uid']));

		$psetarr = array(
			'tagid' => $tagid,
			'ip' => getonlineip(),
			'message' => $message,
			'pic' => ''
		);
		if(checkperm('edittrail')) {
			$message = $message.saddslashes(cplang('thread_edit_trail', array($_SGLOBAL['supe_username'], sgmdate('Y-m-d H:i:s'))));
		}
		updatetable('post', $psetarr, array('tid'=>$_POST['tid'], 'isthread'=>1, 'uid'=>$_SGLOBAL['supe_uid']));
	}

	showmessage('do_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid", 0);

} elseif(submitcheck('postsubmit')) {

	if(!checkperm('allowpost')) {
		showmessage('no_privilege');
	}

	//ʵ����֤
	ckrealname('post');
	
	//���û���ϰ
	cknewuser();

	//�ж��Ƿ����̫��
	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast','',1,array($waittime));
	}

	//��û���
	$tid = empty($_POST['tid'])?0:intval($_POST['tid']);
	$thread = array();
	if($tid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$tid' LIMIT 1");
		$thread = $_SGLOBAL['db']->fetch_array($query);
	}
	if(empty($thread)) showmessage('the_discussion_topic_does_not_exist');

	//������
	if(isblacklist($thread['uid'])) {
		showmessage('is_blacklist');
	}
			
	//Ȩ��
	$mtag = ckmtagspace($thread['tagid']);
	
	$message = $_POST['message'];
	//��������ͼƬ
	if(!empty($_POST['pics'])) {
		foreach($_POST['pics'] as $key => $pic) {
			$picurl = getpicurl($pic);
			if(!empty($picurl)) {
				$message .= "\n[img]".$picurl."[/img]";
			}
		}
	}

	$message = getstr($message, 0, 1, 1, 1, 2);
	if(strlen($message) < 2) {
		showmessage('content_is_not_less_than_four_characters');
	}

	//ժҪ
	$summay = getstr($message, 150, 1, 1);

	//���ûظ�
	$pid = empty($_POST['pid'])?0:intval($_POST['pid']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid' AND tid='$tid' AND isthread='0'");
	
	$post = $_SGLOBAL['db']->fetch_array($query);
	if($post) {
		//������
		if(isblacklist($post['uid'])) {
			showmessage('is_blacklist');
		}
		
		//ʵ��
		realname_set($post['uid'], $post['username']);
		realname_get();
		
		$post['message'] = preg_replace("/\<div class=\"quote\"\>\<span class=\"q\"\>.*?\<\/span\>\<\/div\>/is", '', $post['message']);
		//�Ƴ��༭��¼
		$post['message'] = preg_replace("/<ins class=\"modify\".+?<\/ins>/is", '',$post['message']);
		$post['message'] = html2bbcode($post['message']);//��ʾ��
		$message = addslashes("<div class=\"quote\"><span class=\"q\"><b>".$_SN[$post['uid']]."</b>: ".getstr($post['message'], 150, 0, 0, 0, 2, 1).'</span></div>').$message;
	}

	$setarr = array(
		'tagid' => intval($thread['tagid']),
		'tid' => $tid,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'ip' => getonlineip(),
		'dateline' => $_SGLOBAL['timestamp'],
		'message' => $message,
		'pic' => getpicurl($_POST['pic'])
	);
	$pid = inserttable('post', $setarr, 1);

	//�ʼ�֪ͨ
	smail($thread['uid'], '', cplang('mtag_reply',array($_SN[$space['uid']], shtmlspecialchars(getsiteurl()."space.php?uid=$thread[uid]&do=thread&id=$thread[tid]"))));

	//����ͳ������
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')."
		SET replynum=replynum+1, lastpost='$_SGLOBAL[timestamp]', lastauthor='$_SGLOBAL[supe_username]', lastauthorid='$_SGLOBAL[supe_uid]'
		WHERE tid='$tid'");

	//����
	updatespacestatus('get', 'post');

	//��ͨ�ظ�
	if(empty($post) && $thread['uid'] != $_SGLOBAL['supe_uid']) {
		
		realname_set($thread['uid'], $thread['username']);
		realname_get();
		
		if(empty($mtag['viewperm'])) {
			$fs = array();
			$fs['icon'] = 'post';
			$fs['body_template'] = '';
			$fs['body_data'] = array();
			$fs['body_general'] = '';
	
			$fs['title_template'] = cplang('feed_thread_reply');
	
			$fs['title_data'] = array('touser'=>"<a href=\"space.php?uid=$thread[uid]\">".$_SN[$thread['uid']]."</a>", 'thread'=>"<a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]\">$thread[subject]</a>");
	
			if(ckprivacy('post', 1)) {
				feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
			}
		}

		//֪ͨ
		$note = cplang('note_thread_reply')." <a href=\"space.php?uid=$thread[uid]&do=thread&id=$thread[tid]&pid=$pid\" target=\"_blank\">$thread[subject]</a>";
		notification_add($thread['uid'], 'post', $note);

	} elseif ($post) {
		$note = cplang('note_post_reply', array("space.php?uid=$thread[uid]&do=thread&id=$thread[tid]", $thread['subject'], "space.php?uid=$thread[uid]&do=thread&id=$thread[tid]&pid=$pid"));
		notification_add($post['uid'], 'post', $note);
	}

	//��ת
	showmessage('do_success', "space.php?uid=$_SGLOBAL[supe_uid]&do=thread&id=$tid&pid=$pid", 0);

} elseif(submitcheck('posteditsubmit')) {
	
	$pid = empty($_POST['pid'])?0:intval($_POST['pid']);
	
	$message = $_POST['message'];
	//��������ͼƬ
	if(!empty($_POST['pics'])) {
		foreach($_POST['pics'] as $key => $pic) {
			$picurl = getpicurl($pic);
			if(!empty($picurl)) {
				$message .= "\n[img]".$picurl."[/img]";
			}
		}
	}
	$message = getstr($message, 0, 1, 1, 1, 2);
	if(strlen($message) < 2) showmessage('content_is_too_short');
	
	//�����༭��¼
	if(checkperm('edittrail')) {
		$message = $message.saddslashes(cplang('thread_edit_trail', array($_SGLOBAL['supe_username'], sgmdate('Y-m-d H:i:s'))));
	}
	
	//����
	updatetable('post', array('message'=>$message), array('pid'=>$pid, 'uid'=>$_SGLOBAL['supe_uid']));

	showmessage('do_success', $_POST['refer'], 0);
}

$pid = empty($_GET['pid'])?0:intval($_GET['pid']);
$tid = empty($_GET['tid'])?0:intval($_GET['tid']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
$thread = $post = array();

//�����༭
if($_GET['op'] == 'edit') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid' AND uid='$_SGLOBAL[supe_uid]'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('no_privilege');
	}
	//�Ƴ��༭��¼
	$post['message'] = preg_replace("/<ins class=\"modify\".+?<\/ins>/is", '',$post['message']);
	
	//���Ȩ��
	$tagid = $post['tagid'];
	$mtag = ckmtagspace($post['tagid']);

	//������
	if($post['isthread']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$post[tid]'");
		$thread = $_SGLOBAL['db']->fetch_array($query);
	}
	
	if($thread) {
		$post['message'] = str_replace('&amp;', '&amp;amp;', $post['message']);
		$post['message'] = shtmlspecialchars($post['message']);
		
		$_GET['op'] = '';
		$albums = getalbums($_SGLOBAL['supe_uid']);
		if($post['pic']) {
			$post['message'] .= "<div><img src=\"$post[pic]\"></div>";
		}
	} else {
		$post['message'] = html2bbcode($post['message']);//��ʾ��
	}

} elseif($_GET['op'] == 'delete') {

	include_once(S_ROOT.'./source/function_delete.php');

	if(submitcheck('postdeletesubmit')) {
		if($delposts = deleteposts($tagid, array($pid))) {
			$post = $delposts[0];
			if($post['isthread']) {
				$url = "space.php?uid=$post[uid]&do=mtag&tagid=$post[tagid]&view=list";
			} else {
				$url = $_POST['refer'];
			}
			showmessage('do_success', $url, 0);
		} else {
			showmessage('no_privilege');
		}
	}

} elseif($_GET['op'] == 'reply') {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE pid='$pid'");
	if(!$post = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('posting_does_not_exist');
	}

} elseif($_GET['op'] == 'digest') {

	include_once(S_ROOT.'./source/function_op.php');
	digestthreads($tagid, array($tid), isset($_GET['cancel'])?0:1);
	showmessage('do_success');

} elseif($_GET['op'] == 'top') {

	include_once(S_ROOT.'./source/function_op.php');
	topthreads($tagid, array($tid), isset($_GET['cancel'])?0:1);
	showmessage('do_success');

} else {

	if(!checkperm('allowthread')) {
		showmessage('no_privilege');
	}
	//ʵ����֤
	ckrealname('thread');

	//���û���ϰ
	cknewuser();
	
	//������
	$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);
	if($tagid) {
		$mtag = ckmtagspace($tagid);
	}
	//��ȡ���
	$albums = getalbums($_SGLOBAL['supe_uid']);

	if(!$mtag) {
		include_once(S_ROOT.'./data/data_profield.php');

		$tagid = 0;
		
		//�ҵ�Ⱥ���б�
		$mtaglist = array();
		$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid,field.close FROM ".tname('tagspace')." main
			LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid
			WHERE main.uid='$_SGLOBAL[supe_uid]' AND main.grade>=0");
		$havemtag = false;
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$havemtag = true;
			if(empty($value['close']) && $value['membernum']>=$_SGLOBAL['profield'][$value['tagid']]['mtagminnum']) {
				$mtaglist[$value['fieldid']][$value['tagid']] = $value;
			}
		}

		if(empty($mtaglist)) {
			if($havemtag) {
				showmessage('no_mtag_allow_thread');
			} else {
				showmessage('settings_of_your_mtag');
			}
		}
	}
}

//ģ��
include template('cp_thread');

//�ж��Ƿ�����Ա
function ckmtagspace($tagid) {
	global $_SGLOBAL, $_SCONFIG;

	$count = 0;
	$mtag = array();
	if($tagid) {
		$mtag = getmtag($tagid);
		if($mtag) {
			//�ж��Ƿ�ر�
			if($mtag['close']) {
				showmessage('mtag_close');
			}
			//�Ƿ��������
			if(empty($mtag['allowview'])) {
				showmessage('mtag_not_allow_to_do');
			}
			//�Ƿ�����
			if(empty($mtag['allowpost'])) {
				showmessage('no_privilege');
			}
			//�ж��Ƿ���������Ҫ��
			if($mtag['field']['mtagminnum'] && $mtag['membernum'] < $mtag['field']['mtagminnum']) {
				showmessage('mtag_minnum_erro', '', 1, array($mtag['field']['mtagminnum']));
			}
		}
	}
	if(empty($mtag)) {
		showmessage('first_select_a_mtag');
	}
	return $mtag;
}

?>