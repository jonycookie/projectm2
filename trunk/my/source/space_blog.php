<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_blog.php 7589 2008-06-13 10:11:32Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$start = empty($_GET['start'])?0:intval($_GET['start']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
$classid = empty($_GET['classid'])?0:intval($_GET['classid']);

if($id) {
	//读取日志
	$query = $_SGLOBAL['db']->query("SELECT bf.*, b.* FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid='$id' AND b.uid='$space[uid]'");
	$blog = $_SGLOBAL['db']->fetch_array($query);
	//日志不存在
	if(empty($blog)) {
		showmessage('view_to_info_did_not_exist');
	}
	//检查好友权限
	if(!ckfriend($blog)) {
		//没有权限
		$space['css'] = $space['theme'] = '';
		include template('space_privacy');
		exit();
	} elseif(!$space['self'] && $blog['friend'] == 4) {
		//密码输入问题
		$cookiename = "view_pwd_blog_$blog[blogid]";
		$cookievalue = empty($_SCOOKIE[$cookiename])?'':$_SCOOKIE[$cookiename];
		if($cookievalue != md5(md5($blog['password']))) {
			$invalue = $blog;
			include template('do_inputpwd');
			exit();
		}
	}
	
	//整理
	$blog['tag'] = empty($blog['tag'])?array():unserialize($blog['tag']);
	$blog['pic'] = mkpicurl($blog);
	
	//处理内容
	$width = '420';
	$height = '315';
	//flash
	$blog['message'] = preg_replace("/\[flash\](.+?)\[\/flash\]/i", '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'.$width.'" height="'.$height.'">
			<param name="movie" value="\\1">
			<param name="allowscriptaccess" value="always">
			<param name="wmode" value="transparent">
			<embed src="\\1" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'" allowfullscreen="true" wmode="transparent" allowscriptaccess="always"></embed>
			</object>', $blog['message']);
	//media
	$blog['message'] = preg_replace("/\[flash\=media\](.+?)\[\/flash\]/i", '<object classid="clsid:6bf52a52-394a-11d3-b153-00c04f79faa6" width="'.$width.'" height="'.$height.'">
			<param name="autostart" value="0">
			<param name="url" value="\\1">
			<embed autostart="false" src="\\1" type="video/x-ms-wmv" width="'.$width.'" height="'.$height.'" controls="imagewindow" console="cons"></embed>
			</object>', $blog['message']);
	//real
	$blog['message'] = preg_replace("/\[flash\=real\](.+?)\[\/flash\]/i", '<object classid="clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa" width="'.$width.'" height="'.$height.'">
			<param name="autostart" value="0">
			<param name="src" value="\\1">
			<param name="controls" value="controlpanel">
			<param name="console" value="cons">
			<embed autostart="false" src="\\1" type="audio/x-pn-realaudio-plugin" width="'.$width.'" height="'.$height.'" controls="controlpanel" console="cons"></embed>
			</object>', $blog['message']);
	
	//获取相关资源
	//有效期
	if($_SCONFIG['uc_tagrelatedtime'] && ($_SGLOBAL['timestamp'] - $blog['relatedtime'] > $_SCONFIG['uc_tagrelatedtime'])) {
		$blog['related'] = '';
	}
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
			//自身TAG
			$tag_blogids = array();
			$query = $_SGLOBAL['db']->query("SELECT DISTINCT blogid FROM ".tname('tagblog')." WHERE tagid IN (".simplode($b_tagids).") AND blogid<>'$blog[blogid]' ORDER BY blogid DESC LIMIT 0,10");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$tag_blogids[] = $value['blogid'];
			}
			if($tag_blogids) {
				$query = $_SGLOBAL['db']->query("SELECT uid,username,subject,blogid FROM ".tname('blog')." WHERE blogid IN (".simplode($tag_blogids).")");
				while ($value = $_SGLOBAL['db']->fetch_array($query)) {
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
				}
				if(empty($blog['related'][$appid]['data'])) {
					unset($blog['related'][$appid]);
				}
			}
		}
		updatetable('blogfield', array('related'=>addslashes(serialize(sstripslashes($blog['related']))), 'relatedtime'=>$_SGLOBAL['timestamp']), array('blogid'=>$blog['blogid']));//更新
	} else {
		$blog['related'] = empty($blog['related'])?array():unserialize($blog['related']);
	}
	
	//评论
	$perpage = 100;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page < 1) $page = 1;
	$start = ($page-1)*$perpage;

	//检查开始数
	ckstart($start, $perpage);

	$count = $blog['replynum'];
	
	$list = array();
	if($count) {
		$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
		$csql = $cid?"cid='$cid' AND":'';
		
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$id' AND idtype='blogid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$list[] = $value;
		}
	}
	
	//分页
	$multi = array();
	$multi['html'] = multi($count, $perpage, $page, "space.php?uid=$blog[uid]&do=$do&id=$id");
	
	//脚印
	$tracelist = array();
	if($blog['tracenum']) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blog[blogid]' ORDER BY dateline DESC LIMIT 0,6");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$tracelist[] = $value;
		}
	}

	//访问统计
	inserttable('log', array('id'=>$blog['blogid'], 'idtype'=>'blogid'));
	
	include_once template("space_blog_view");

} else {
	//分页
	$perpage = 10;
	//检查开始数
	ckstart($start, $perpage);

	//摘要截取
	$summarylen = 300;

	$classarr = array();
	$list = array();
	$count = 0;
		
	//处理查询
	if($_GET['view'] == 'trace') {
		//踩过的日志
		$theurl = "space.php?uid=$space[uid]&do=$do&view=trace";
		$actives = array('trace'=>' class="active"');
		
		$query = $_SGLOBAL['db']->query("SELECT bf.message, bf.target_ids, b.* FROM ".tname('trace')." tr
			LEFT JOIN ".tname('blog')." b ON b.blogid=tr.blogid
			LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=tr.blogid
			WHERE tr.uid='$space[uid]'
			ORDER BY tr.dateline DESC LIMIT $start,$perpage");
	} else {
		if(empty($space['friend']) || $classid) {
			$wheresql = "b.uid='$space[uid]'";
			$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
			$f_index = '';
			$actives = array('me'=>' class="active"');
			//日志分类
			$query = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname('class')." WHERE uid='$space[uid]'");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$classarr[$value['classid']] = $value['classname'];
			}
		} else {
			$wheresql = "b.uid IN ($space[friend])";
			$theurl = "space.php?uid=$space[uid]&do=$do";
			$f_index = 'FORCE INDEX(dateline)';
			$actives = array('we'=>' class="active"');
		}
		
		//分类
		if($classid) {
			$wheresql .= " AND b.classid='$classid'";
			$theurl .= "&classid=$classid";
		}

		$query = $_SGLOBAL['db']->query("SELECT bf.message, bf.target_ids, b.* FROM ".tname('blog')." b $f_index
			LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid 
			WHERE $wheresql 
			ORDER BY b.dateline DESC LIMIT $start,$perpage");
	}

	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(ckfriend($value)) {
			$value['message'] = $value['friend']==4?lang('password_message'):getstr($value['message'], $summarylen, 0, 0, 0, 0, -1);
			$value['pic'] = mkpicurl($value);
			$list[] = $value;
		}
		$count++;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, $theurl);
	
	include_once template("space_blog_list");
}

?>