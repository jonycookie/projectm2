<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_thread.php 10789 2008-12-23 02:39:41Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

//��ҳ
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

if($id) {
	//����
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('thread')." WHERE tid='$id' LIMIT 1");
	if(!$thread = $_SGLOBAL['db']->fetch_array($query)) {
		showmessage('topic_does_not_exist');
	}
	realname_set($thread['uid'], $thread['username']);
	
	//Ⱥ����Ϣ
	$tagid = $thread['tagid'];

	$mtag = getmtag($tagid);

	if(empty($mtag['allowview'])) {
		showmessage('mtag_not_allow_to_do', "space.php?do=mtag&tagid=$tagid");
	}

	//�����б�
	$perpage = 30;
	$start = ($page-1)*$perpage;

	$count = $thread['replynum'];

	if($count % $perpage == 0) {
		$perpage = $perpage + 1;
	}
	//��鿪ʼ��
	ckstart($start, $perpage);

	$pid = empty($_GET['pid'])?0:intval($_GET['pid']);
	$psql = $pid?"(isthread='1' OR pid='$pid') AND":'';

	$list = array();
	$postnum = $start;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE $psql tid='$thread[tid]' ORDER BY dateline LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$value['num'] = $postnum;
		$list[] = $value;
		$postnum++;
	}

	//ȡ������
	if($list[0]['isthread']) {
		$thread['content'] = $list[0];
		include_once(S_ROOT.'./source/function_blog.php');
		$thread['content']['message'] = blog_bbcode($thread['content']['message']);
		unset($list[0]);
	} else {
		$thread['content'] = array();
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, "space.php?uid=$thread[uid]&do=$do&id=$id");

	//����ͳ��
	if(!$space['self']) {
		$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET viewnum=viewnum+1 WHERE tid='$id'");
		inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid'));//�ӳٸ���
	}

	//ʵ��
	realname_get();

	include_once template("space_thread_view");

} else {

	$perpage = 30;
	$start = ($page-1)*$perpage;
	
	//��鿪ʼ��
	ckstart($start, $perpage);

	//�����б�
	$wheresql = '';
	if(empty($_GET['view'])) {
		//�Ҽ����Ⱥ��
		$tagids = array();
		$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('tagspace')." WHERE uid='$space[uid]' AND grade>'-2'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$tagids[$value['tagid']] = $value['tagid'];
		}
		if($tagids) {
			//�����Ⱥ��
			$wheresql = "main.tagid IN (".simplode($tagids).")";
			$theurl = "space.php?uid=$space[uid]&do=$do";
			$f_index = 'USE INDEX (lastpost)';
		}
		$actives = array('we'=>' class="active"');
	} else {
		//�Լ���
		$wheresql = "main.uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$f_index = '';
		$actives = array('me'=>' class="active"');
	}

	$list = array();
	$mtags = array();	
	$count = 0;
	
	if($wheresql) {
		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('thread')." main WHERE $wheresql"),0);
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid,field.pic FROM ".tname('thread')." main $f_index
				LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid WHERE $wheresql
				ORDER BY main.lastpost DESC
				LIMIT $start,$perpage");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				realname_set($value['uid'], $value['username']);
				realname_set($value['lastauthorid'], $value['lastauthor']);
				$value['tagname'] = getstr($value['tagname'], 20);
				$list[] = $value;
				if(empty($value['pic'])) {
					$value['pic'] = 'image/nologo.jpg';
				}
				
				if(count($mtags)<5) {
					$mtags[$value['tagid']] = $value;
				}
			}
		}
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, $theurl);
	
	//ʵ��
	realname_get();

	include_once template("space_thread_list");
}


?>