<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_album.php 10785 2008-12-22 08:22:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$id = empty($_GET['id'])?0:intval($_GET['id']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;

if($id) {
	//ͼƬ�б�
	$perpage = 20;
	$start = ($page-1)*$perpage;
	
	//��鿪ʼ��
	ckstart($start, $perpage);

	//��ѯ���
	if($id > 0) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$id' AND uid='$space[uid]' LIMIT 1");
		$album = $_SGLOBAL['db']->fetch_array($query);
		//��᲻����
		if(empty($album)) {
			showmessage('to_view_the_photo_does_not_exist');
		}

		//������Ȩ��
		ckfriend_album($album);

		//��ѯ
		$wheresql = "albumid='$id'";
		$count = $album['picnum'];
	} else {
		//Ĭ�����
		$wheresql = "albumid='0' AND uid='$space[uid]'";
		$count = getcount('pic', array('albumid'=>0, 'uid'=>$space['uid']));

		$album = array(
			'uid' => $space['uid'],
			'albumid' => -1,
			'albumname' => lang('default_albumname'),
			'picnum' => $count
		);
	}

	//ͼƬ�б�
	$list = array();
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['pic'] = mkpicurl($value);
			$list[] = $value;
		}
	}
	//��ҳ
	$multi = multi($count, $perpage, $page, "space.php?uid=$album[uid]&do=$do&id=$id");

	include_once template("space_album_view");

} elseif ($picid) {

	if(empty($_GET['goto'])) $_GET['goto'] = '';

	//����ͼƬ
	//����ͼƬ
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid='$picid' AND uid='$space[uid]' LIMIT 1");
	$pic = $_SGLOBAL['db']->fetch_array($query);
	//ͼƬ������
	if(empty($pic)) {
		showmessage('view_images_do_not_exist');
	}

	if($_GET['goto']=='up') {
		//��һ��
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$pic[albumid]' AND uid='$space[uid]' AND picid>$picid ORDER BY picid LIMIT 1");
		if(!$newpic = $_SGLOBAL['db']->fetch_array($query)) {
			//��ͷת�������һ��
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$pic[albumid]' AND uid='$space[uid]' ORDER BY picid LIMIT 1");
			$pic = $_SGLOBAL['db']->fetch_array($query);
		} else {
			$pic = $newpic;
		}
	} elseif($_GET['goto']=='down') {
		//��һ��
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$pic[albumid]' AND uid='$space[uid]' AND picid<$picid ORDER BY picid DESC LIMIT 1");
		if(!$newpic = $_SGLOBAL['db']->fetch_array($query)) {
			//��ͷת�����µ�һ��
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$pic[albumid]' AND uid='$space[uid]' ORDER BY picid DESC LIMIT 1");
			$pic = $_SGLOBAL['db']->fetch_array($query);
		} else {
			$pic = $newpic;
		}
	}
	$picid = $pic['picid'];

	//��ȡ���
	$album = array();
	if($pic['albumid']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$pic[albumid]'");
		if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
			updatetable('pic', array('albumid'=>0), array('albumid'=>$pic['albumid']));//��ᶪʧ?
		}
	}

	if($album) {
		//������Ȩ��
		ckfriend_album($album);

		//��ǰ����
		if($_GET['goto']=='down') {
			$sequence = empty($_SCOOKIE['pic_sequence'])?$album['picnum']:intval($_SCOOKIE['pic_sequence']);
			$sequence++;
			if($sequence>$album['picnum']) {
				$sequence = 1;
			}
		} elseif($_GET['goto']=='up') {
			$sequence = empty($_SCOOKIE['pic_sequence'])?$album['picnum']:intval($_SCOOKIE['pic_sequence']);
			$sequence--;
			if($sequence<1) {
				$sequence = $album['picnum'];
			}
		} else {
			$sequence = 1;
		}
		ssetcookie('pic_sequence', $sequence);
	} else {
		$album['albumid'] = $pic['albumid'] = '-1';
	}

	//ͼƬ��ַ
	$pic['pic'] = mkpicurl($pic, 0);
	$pic['size'] = formatsize($pic['size']);

	//ͼƬ��EXIF��Ϣ
	$exifs = array();
	$allowexif = function_exists('exif_read_data');
	if(isset($_GET['exif']) && $allowexif) {
		include_once(S_ROOT.'./source/function_exif.php');
		$exifs = getexif($pic['pic']);
	}

	//ͼƬ����
	$perpage = 50;
	$start = ($page-1)*$perpage;
	
	//��鿪ʼ��
	ckstart($start, $perpage);

	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';
	$siteurl = getsiteurl();
	$list = array();
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('comment')." WHERE $csql id='$pic[picid]' AND idtype='picid'"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$pic[picid]' AND idtype='picid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['authorid'], $value['author']);
			$list[] = $value;
		}
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, "space.php?uid=$pic[uid]&do=$do&picid=$picid");

	//����
	if(empty($album['albumname'])) $album['albumname'] = lang('default_albumname');

	//ʵ��
	realname_get();

	//ͼƬȫ·��
	$pic_url = $pic['pic'];
	if(!preg_match("/^http\:\/\/.+?/i", $pic['pic'])) {
		$pic_url = getsiteurl().$pic['pic'];
	}
	$pic_url2 = rawurlencode($pic['pic']);

	//����ͳ��
	if(!$space['self']) {
		inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid'));//�ӳٸ���
	}

	include_once template("space_album_pic");

} else {
	//����б�
	$perpage = 10;
	$start = ($page-1)*$perpage;
	
	//��鿪ʼ��
	ckstart($start, $perpage);

	//Ȩ�޹���
	$_GET['friend'] = intval($_GET['friend']);

	//�����ѯ
	$default = array();
	$f_index = '';
	if($_GET['view'] == 'all') {
		//��ҵ����
		$wheresql = "friend='0'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=all";
		$actives = array('all'=>' class="active"');

	} elseif(empty($space['feedfriend'])) {
		$wheresql = "uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$actives = array('me'=>' class="active"');

		//��ȡĬ�����
		if(empty($start) && empty($_GET['friend'])) {
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='0' AND uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,1");
			if($default = $_SGLOBAL['db']->fetch_array($query)) {
				$default['pic'] = mkpicurl($default);
				$default['albumid'] = '-1';
				$default['updatetime'] = $default['dateline'];
			}
		}

	} else {
		$wheresql = "uid IN ($space[feedfriend])";
		$theurl = "space.php?uid=$space[uid]&do=$do";
		$f_index = 'USE INDEX(updatetime)';
		$actives = array('we'=>' class="active"');
	}

	//����Ȩ��
	if($_GET['friend']) {
		$wheresql .= " AND friend='$_GET[friend]'";
		$theurl .= "&friend=$_GET[friend]";
	}

	$list = array();
	$pricount = 0;
	
	if($default) $list[] = $default;
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('album')." WHERE $wheresql"),0);
	if($count) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." $f_index WHERE $wheresql ORDER BY updatetime DESC LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(ckfriend($value)) {
				realname_set($value['uid'], $value['username']);
				$value['pic'] = mkpicurl($value);
				$list[] = $value;
			} else {
				$pricount++;
			}
		}
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, $theurl);

	//ʵ��
	realname_get();

	include_once template("space_album_list");
}

//������Ȩ��
function ckfriend_album($album) {
	global $_SGLOBAL, $_SC, $_SCONFIG, $_SCOOKIE, $space, $_SN;

	if(!ckfriend($album)) {
		//û��Ȩ��
		include template('space_privacy');
		exit();
	} elseif(!$space['self'] && $album['friend'] == 4) {
		//������������
		$cookiename = "view_pwd_album_$album[albumid]";
		$cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
		if($cookievalue != md5(md5($album['password']))) {
			$invalue = $album;
			include template('do_inputpwd');
			exit();
		}
	}
}

?>