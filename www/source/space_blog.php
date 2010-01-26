<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_blog.php 10785 2008-12-22 08:22:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$classid = empty($_GET['classid'])?0:intval($_GET['classid']);

if($id) {
	//��ȡ��־
	$query = $_SGLOBAL['db']->query("SELECT bf.*, b.* FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid='$id' AND b.uid='$space[uid]'");
	$blog = $_SGLOBAL['db']->fetch_array($query);
	//��־������
	if(empty($blog)) {
		showmessage('view_to_info_did_not_exist');
	}
	//������Ȩ��
	if(!ckfriend($blog)) {
		//û��Ȩ��
		include template('space_privacy');
		exit();
	} elseif(!$space['self'] && $blog['friend'] == 4) {
		//������������
		$cookiename = "view_pwd_blog_$blog[blogid]";
		$cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
		if($cookievalue != md5(md5($blog['password']))) {
			$invalue = $blog;
			include template('do_inputpwd');
			exit();
		}
	}

	//����
	$blog['tag'] = empty($blog['tag'])?array():unserialize($blog['tag']);
	$blog['pic'] = mkpicurl($blog);

	//������Ƶ��ǩ
	include_once(S_ROOT.'./source/function_blog.php');
	$blog['message'] = blog_bbcode($blog['message']);

	//��ȡ�����Դ
	//��Ч��
	if($_SCONFIG['uc_tagrelatedtime'] && ($_SGLOBAL['timestamp'] - $blog['relatedtime'] > $_SCONFIG['uc_tagrelatedtime'])) {
		$blog['related'] = '';
	}
	$blog['related'] = '';
	if($blog['tag'] && empty($blog['related'])) {
		@include_once(S_ROOT.'./data/data_tagtpl.php');

		$b_tagids = $b_tags = $blog['related'] = array();
		$tag_count = -1;
		foreach ($blog['tag'] as $key => $value) {
			$b_tags[] = $value;
			$b_tagids[] = $key;
			$tag_count++;
		}
		if(!empty($_SCONFIG['uc_tagrelated'])) {
			if(!empty($_SGLOBAL['tagtpl']['limit'])) {
				include_once(S_ROOT.'./uc_client/client.php');
				$tag_index = mt_rand(0, $tag_count);
				$blog['related'] = uc_tag_get($b_tags[$tag_index], $_SGLOBAL['tagtpl']['limit']);
			}
		} else {
			//����TAG
			$tag_blogids = array();
			$query = $_SGLOBAL['db']->query("SELECT DISTINCT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($b_tagids).") AND blogid<>'$blog[blogid]' ORDER BY blogid DESC LIMIT 0,10");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$tag_blogids[] = $value['blogid'];
			}
			if($tag_blogids) {
				$query = $_SGLOBAL['db']->query("SELECT uid,username,subject,blogid FROM ".tname('blog')." WHERE blogid IN (".simplode($tag_blogids).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
					realname_set($value['uid'], $value['username']);//ʵ��
					$value['url'] = "space.php?uid=$value[uid]&do=blog&id=$value[blogid]";
					$blog['related'][UC_APPID]['data'][] = $value;
				}
				$blog['related'][UC_APPID]['type'] = 'UCHOME';
			}
		}
		if(!empty($blog['related']) && is_array($blog['related'])) {
			foreach ($blog['related'] as $appid => $values) {
				if(!empty($values['data']) && $_SGLOBAL['tagtpl']['data'][$appid]['template']) {
					foreach ($values['data'] as $itemkey => $itemvalue) {
						if(!empty($itemvalue) && is_array($itemvalue)) {
							$searchs = $replaces = array();
							foreach (array_keys($itemvalue) as $key) {
								$searchs[] = '{'.$key.'}';
								$replaces[] = $itemvalue[$key];
							}
							$blog['related'][$appid]['data'][$itemkey]['html'] = stripslashes(str_replace($searchs, $replaces, $_SGLOBAL['tagtpl']['data'][$appid]['template']));
						} else {
							unset($blog['related'][$appid]['data'][$itemkey]);
						}
					}
				} else {
					$blog['related'][$appid]['data'] = '';
				}
				if(empty($blog['related'][$appid]['data'])) {
					unset($blog['related'][$appid]);
				}
			}
		}
		updatetable('blogfield', array('related'=>addslashes(serialize(sstripslashes($blog['related']))), 'relatedtime'=>$_SGLOBAL['timestamp']), array('blogid'=>$blog['blogid']));//����
	} else {
		$blog['related'] = empty($blog['related'])?array():unserialize($blog['related']);
	}

	//����
	$perpage = 30;
	$start = ($page-1)*$perpage;

	//��鿪ʼ��
	ckstart($start, $perpage);

	$count = $blog['replynum'];

	$list = array();
	if($count) {
		$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
		$csql = $cid?"cid='$cid' AND":'';

		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$id' AND idtype='blogid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['authorid'], $value['author']);//ʵ��
			$list[] = $value;
		}
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, "space.php?uid=$blog[uid]&do=$do&id=$id");

	//��ӡ
	$tracelist = array();
	if($blog['tracenum']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blog[blogid]' ORDER BY dateline DESC LIMIT 0,18");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);//ʵ��
			$tracelist[] = $value;
		}
	}

	//����ͳ��
	if(!$space['self']) {
		$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET viewnum=viewnum+1 WHERE blogid='$blog[blogid]'");
		inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid'));//�ӳٸ���
	}

	//ʵ��
	realname_get();

	include_once template("space_blog_view");

} else {
	//��ҳ
	$perpage = 10;
	$start = ($page-1)*$perpage;
	
	//��鿪ʼ��
	ckstart($start, $perpage);

	//ժҪ��ȡ
	$summarylen = 300;

	$classarr = array();
	$list = array();
	$userlist = array();
	$count = $pricount = 0;

	//�����ѯ
	$f_index = '';
	if($_GET['view'] == 'trace') {
		//�ȹ�����־
		$theurl = "space.php?uid=$space[uid]&do=$do&view=trace";
		$actives = array('trace'=>' class="active"');

		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('trace')." tr WHERE tr.uid='$space[uid]'"),0);
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT bf.message, bf.target_ids, b.* FROM ".tname('trace')." tr
				LEFT JOIN ".tname('blog')." b ON b.blogid=tr.blogid
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=tr.blogid
				WHERE tr.uid='$space[uid]'
				ORDER BY tr.dateline DESC LIMIT $start,$perpage");
		}
	} else {
		if($_GET['view'] == 'all') {
			//��ҵ���־
			$wheresql = "b.friend='0'";
			$theurl = "space.php?uid=$space[uid]&do=$do&view=all";
			$actives = array('all'=>' class="active"');

		} elseif(empty($space['feedfriend']) || $classid) {
			$wheresql = "b.uid='$space[uid]'";
			$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
			$actives = array('me'=>' class="active"');
			//��־����
			$query = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname('class')." WHERE uid='$space[uid]'");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$classarr[$value['classid']] = $value['classname'];
			}
		} else {
			$wheresql = "b.uid IN ($space[feedfriend])";
			$theurl = "space.php?uid=$space[uid]&do=$do";
			$f_index = 'USE INDEX(dateline)';
			$actives = array('we'=>' class="active"');
		}

		//����
		if($classid) {
			$wheresql .= " AND b.classid='$classid'";
			$theurl .= "&classid=$classid";
		}

		//����Ȩ��
		$_GET['friend'] = intval($_GET['friend']);
		if($_GET['friend']) {
			$wheresql .= " AND b.friend='$_GET[friend]'";
			$theurl .= "&friend=$_GET[friend]";
		}

		$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('blog')." b WHERE $wheresql"),0);
		if($count) {
			$query = $_SGLOBAL['db']->query("SELECT bf.message, bf.target_ids, b.* FROM ".tname('blog')." b $f_index
				LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid
				WHERE $wheresql
				ORDER BY b.dateline DESC LIMIT $start,$perpage");
		}
	}

	if($count) {
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(ckfriend($value)) {
				realname_set($value['uid'], $value['username']);
				$value['message'] = $value['friend']==4?'':getstr($value['message'], $summarylen, 0, 0, 0, 0, -1);
				$value['pic'] = mkpicurl($value);
				$list[] = $value;
				$userlist[$value['uid']] = $value['username'];
			} else {
				$pricount++;
			}
		}
	}

	//��ҳ
	$multi = multi($count, $perpage, $page, $theurl);

	//ʵ��
	realname_get();

	include_once template("space_blog_list");
}

?>