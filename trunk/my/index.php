<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: index.php 10953 2009-01-12 02:55:37Z liguode $
*/

include_once('./common.php');

if(is_numeric($_SERVER['QUERY_STRING'])) {
	showmessage('enter_the_space', "space.php?uid=$_SERVER[QUERY_STRING]", 0);
}

//��������
if(!isset($_GET['do']) && $_SCONFIG['allowdomain']) {
	$hostarr = explode('.', $_SERVER['HTTP_HOST']);
	$domainrootarr = explode('.', $_SCONFIG['domainroot']);
	if(count($hostarr) > 2 && count($hostarr) > count($domainrootarr) && $hostarr[0] != 'www' && !isholddomain($hostarr[0])) {
		showmessage('enter_the_space', $_SCONFIG['siteallurl'].'space.php?domain='.$hostarr[0], 0);
	}
}

if($_SGLOBAL['supe_uid']) {
	//�ѵ�¼��ֱ����ת������ҳ
	showmessage('enter_the_space', 'space.php?do=home', 0);
}

//����¼��
$membername = empty($_SCOOKIE['loginuser'])?'':sstripslashes($_SCOOKIE['loginuser']);
$wheretime = $_SGLOBAL['timestamp']-3600*24*30;

//�ܻ�Ա
$spacecount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('space')), 0);

//��û���
$caches = data_get('index_cache', 1);
$caches['dateline'] = intval($caches['dateline']);
if($_SCONFIG['networkupdate'] && $_SGLOBAL['timestamp'] - $caches['dateline'] < $_SCONFIG['networkupdate']) {
	@$caches['datavalue'] = unserialize($caches['datavalue']);
	@extract($caches['datavalue']);
	
} else {
	
	//������־
	$bloglist = array();
	$query = $_SGLOBAL['db']->query("SELECT blogid,subject,uid,username FROM ".tname('blog')." WHERE friend='0' AND dateline>'$wheretime' ORDER BY replynum DESC LIMIT 0,11");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$bloglist[] = $value;
	}
	
	//������µ����
	$albumlist = array();
	$query = $_SGLOBAL['db']->query("SELECT albumid,albumname,picnum,pic,picflag,uid,username FROM ".tname('album')." WHERE friend='0' AND picnum>0 ORDER BY updatetime DESC LIMIT 0,7");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['pic'] = mkpicurl($value, 1);
		$albumlist[] = $value;
	}
	
	//��ҵ����¶�̬
	$feedlist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('feed')." WHERE friend='0' ORDER BY dateline DESC LIMIT 0,10");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$feedlist[] = $value;
	}
	
	//����Ⱥ��
	$mtaglist = $threadlist = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid,tagname,membernum,pic FROM ".tname('mtag')." ORDER BY membernum DESC LIMIT 0,2");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['pic'])) {
			$value['pic'] = 'image/nologo.jpg';
		}
		$mtaglist[] = $value;
		
		//���»���
		$query2 = $_SGLOBAL['db']->query("SELECT tid,subject,uid,username FROM ".tname('thread')." WHERE tagid='$value[tagid]' ORDER BY dateline DESC LIMIT 0,3");
		while ($thread = $_SGLOBAL['db']->fetch_array($query2)) {
			$threadlist[$value['tagid']][] = $thread;
		}
	}
	
	//���ſռ�
	$spacelist = array();
	$query = $_SGLOBAL['db']->query("SELECT uid,username,name,namestatus FROM ".tname('space')."
		WHERE updatetime>'$wheretime' ORDER BY viewnum DESC LIMIT 0,6");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);//ʵ��
		$spacelist[] = $value;
	}
	
	//�����Ӧ��
	$myapplist = $onlielist = array();
	if($_SCONFIG['my_status']) {
		$query = $_SGLOBAL['db']->query("SELECT appid,appname FROM ".tname('myapp')." WHERE flag>=0 ORDER BY flag DESC, displayorder LIMIT 0,5");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$myapplist[] = $value;
		}
	} else {
		$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('session')." ORDER BY lastactivity DESC LIMIT 0,5");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$onlinelist[] = $value;
		}
	}

	//����
	data_set('index_cache', array(
		'bloglist' => $bloglist,
		'albumlist' => $albumlist,
		'feedlist' => $feedlist,
		'mtaglist' => $mtaglist,
		'threadlist' => $threadlist,
		'spacelist' => $spacelist,
		'myapplist' => $myapplist,
		'onlinelist' => $onlinelist,
		'_SN' => $_SN
	));
}

//ͼƬ�õ�
$piclist = array();
@include_once(S_ROOT.'./data/data_network.php');
if(empty($netcache['piclist'])) {
	$query = $_SGLOBAL['db']->query("SELECT p.* FROM ".tname('pic')." p, ".tname('album')." a
		WHERE a.friend='0' AND a.albumid=p.albumid
		ORDER BY p.dateline DESC LIMIT 0,5");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['pic'] = mkpicurl($value, 0);
		$value['title'] = getstr($value['title'], 50, 0, 1, 0, 0, -1);
		$piclist[] = $value;
	}
} else {
	$piclist = $netcache['piclist'];
}

//��ȡʵ��
realname_get();

//��ʽ����̬
foreach ($feedlist as $key => $value) {
	$feedlist[$key] = mkfeed($value);
}

include template('index');

?>