<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_blog.php 7226 2008-04-29 07:35:18Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//添加博客
function blog_post($POST, $olds=array()) {
	global $_SGLOBAL, $_SC;
	
	//操作者角色切换
	if(!empty($olds)) {
		$__SGLOBAL = $_SGLOBAL;
		$_SGLOBAL['supe_uid'] = $olds['uid'];
		$_SGLOBAL['supe_username'] = addslashes($olds['username']);
	}

	//标题
	$POST['subject'] = getstr($POST['subject'], 80, 1, 1, 1);
	$POST['friend'] = intval($POST['friend']);
	
	//隐私
	$POST['target_ids'] = '';
	if($POST['friend'] == 2) {
		//特定好友
		$uids = array();
		$names = empty($_POST['target_names'])?array():explode(' ', str_replace(lang('tab_space'), ' ', $_POST['target_names']));
		if($names) {
			$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('space')." WHERE username IN (".simplode($names).")");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$uids[] = $value['uid'];
			}
		}
		if(empty($uids)) {
			$POST['friend'] = 3;//仅自己可见
		} else {
			$POST['target_ids'] = implode(',', $uids);
		}
	} elseif($POST['friend'] == 4) {
		//加密
		$POST['password'] = trim($POST['password']);
		if($POST['password'] == '') $POST['friend'] = 0;//公开
	}
	if($POST['friend'] !== 2) {
		$POST['target_ids'] = '';
	}
	if($POST['friend'] !== 4) {
		$POST['password'] == '';
	}

	$POST['tag'] = shtmlspecialchars(trim($POST['tag']));
	$POST['tag'] = getstr($POST['tag'], 500, 1, 1, 1);	//语词屏蔽

	//内容
	$POST['message'] = checkhtml($POST['message']);
	$POST['message'] = getstr($POST['message'], 0, 1, 0, 1, 0, 1);
	$POST['message'] = preg_replace("/\<div\>\<\/div\>/i", '', $POST['message']);
	$message = $POST['message'];

	//个人分类
	if(empty($olds['classid']) || $POST['classid'] != $olds['classid']) {
		if(!empty($POST['classid']) && substr($POST['classid'], 0, 4) == 'new:') {
			//分类名
			$classname = shtmlspecialchars(trim(substr($POST['classid'], 4)));
			$classname = getstr($classname, 0, 1, 1, 1);
			if(empty($classname)) {
				$classid = 0;
			} else {
				$classid = getcount('class', array('classname'=>$classname, 'uid'=>$_SGLOBAL['supe_uid']), 'classid');
				if(empty($classid)) {
					$setarr = array(
						'classname' => $classname,
						'uid' => $_SGLOBAL['supe_uid'],
						'dateline' => $_SGLOBAL['timestamp']
					);
					$classid = inserttable('class', $setarr, 1);
				}
			}
		} else {
			$classid = intval($POST['classid']);

		}
	} else {
		$classid = $olds['classid'];
	}
	if($classid && empty($classname)) {
		//是否是自己的
		$classname = getcount('class', array('classid'=>$classid, 'uid'=>$_SGLOBAL['supe_uid']), 'classname');
		if(empty($classname)) $classid = 0;
	}
	
	//主表
	$blogarr = array(
		'subject' => $POST['subject'],
		'classid' => $classid,
		'friend' => $POST['friend'],
		'password' => $POST['password'],
		'noreply' => empty($_POST['noreply'])?0:1
	);

	//标题图片
	$titlepic = '';
	
	//获取上传的图片
	$uploads = array();
	if(!empty($POST['picids'])) {
		$picids = array_keys($POST['picids']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE picid IN (".simplode($picids).") AND uid='$_SGLOBAL[supe_uid]'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			if(empty($titlepic) && $value['thumb']) {
				$titlepic = $value['filepath'].'.thumb.jpg';
				$blogarr['picflag'] = $value['remote']?2:1;
			}
			$uploads[$POST['picids'][$value['picid']]] = $value;
		}
		if(empty($titlepic) && $value) {
			$titlepic = $value['filepath'];
			$blogarr['picflag'] = $value['remote']?2:1;
		}
	}
	
	//插入文章
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
		//未插入文章
		foreach ($uploads as $value) {
			$picurl = mkpicurl($value, 0);
			$message .= "<div class=\"uchome-message-pic\"><img src=\"$picurl\"><p>$value[title]</p></div>";
		}
	}
	
	//出现问题
	if(empty($message) && empty($POST['subject'])) {
		return false;//没有任何内容
	}
	
	//添加slashes
	$message = addslashes($message);
	
	//从内容中读取图片
	if(empty($titlepic)) {
		$titlepic = getmessagepic($message);
		$blogarr['picflag'] = 0;
	}
	$blogarr['pic'] = $titlepic;

	if($olds) {
		//更新
		$blogid = $olds['blogid'];
		updatetable('blog', $blogarr, array('blogid'=>$blogid));
		
		$fuids = array();
		
		$blogarr['uid'] = $olds['uid'];
		$blogarr['username'] = $olds['username'];
	} else {
		$blogarr['uid'] = $_SGLOBAL['supe_uid'];
		$blogarr['username'] = $_SGLOBAL['supe_username'];
		$blogarr['dateline'] = empty($POST['dateline'])?$_SGLOBAL['timestamp']:$POST['dateline'];
		$blogid = inserttable('blog', $blogarr, 1);
	}
	
	$blogarr['blogid'] = $blogid;
	
	//附表
	$fieldarr = array(
		'message' => $message,
		'postip' => getonlineip(),
		'target_ids' => $POST['target_ids']
	);
	
	//TAG
	$oldtagstr = addslashes(empty($olds['tag'])?'':implode(' ', unserialize($olds['tag'])));
	

	$tagarr = array();
	if($POST['tag'] != $oldtagstr) {
		if(!empty($olds['tag'])) {
			//先把以前的给清理掉
			$oldtags = array();
			$query = $_SGLOBAL['db']->query("SELECT tagid, blogid FROM ".tname('tagblog')." WHERE blogid='$blogid'");
			while ($value = $_SGLOBAL['db']->fetch_array($query)) {
				$oldtags[] = $value['tagid'];
			}
			if($oldtags) {
				$_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum=blognum-1 WHERE tagid IN (".simplode($oldtags).")");
				$_SGLOBAL['db']->query("DELETE FROM ".tname('tagblog')." WHERE blogid='$blogid'");
			}
		}
		$tagarr = tag_batch($blogid, $POST['tag']);
		//更新附表中的tag
		$fieldarr['tag'] = empty($tagarr)?'':addslashes(serialize($tagarr));
	}

	if($olds) {
		//更新
		updatetable('blogfield', $fieldarr, array('blogid'=>$blogid));
	} else {
		$fieldarr['blogid'] = $blogid;
		$fieldarr['uid'] = $blogarr['uid'];
		inserttable('blogfield', $fieldarr);
	}

	//空间更新
	if($olds) {
		//空间更新
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
	} else {
		//更新空间note
		updatetable('spacefield', array('note'=>$blogarr['subject']), array('uid'=>$_SGLOBAL['supe_uid']));
		
		//积分
		updatespacestatus('get', 'blog');
	}
	
	//feed
	if(empty($olds) && $blogarr['friend']!=3) {
		//事件feed
		$fs = array();
		$fs['icon'] = 'blog';

		$fs['title_data'] = array();
		$fs['images'] = $fs['image_links'] = array();
		
		if($blogarr['friend'] == 4) {
			//加密日志feed
			$fs['title_template'] = lang('feed_blog_password');
			$fs['title_data'] = array(
				'subject' => "<a href=\"space.php?uid=$_SGLOBAL[supe_uid]&do=blog&id=$blogid\">$blogarr[subject]</a>"
			);
			$fs['body_template'] = '';
			$fs['body_data'] = array();
		} else {
			if($blogarr['pic']) {
				$fs['images'] = array(mkpicurl($blogarr));
				$fs['image_links'] = array("space.php?uid=$_SGLOBAL[supe_uid]&do=blog&id=$blogid");
			}
			$fs['title_template'] = lang('feed_blog');
			$fs['body_template'] = '<b>{subject}</b><br>{summary}';
			$fs['body_data'] = array(
				'subject' => "<a href=\"space.php?uid=$_SGLOBAL[supe_uid]&do=blog&id=$blogid\">$blogarr[subject]</a>",
				'summary' => getstr($message, 150, 1, 1, 0, 0, -1)
			);
		}
		
		$fs['body_general'] = '';
		
		 $fs['target_ids'] = $fieldarr['target_ids'];
		 $fs['friend'] = $blogarr['friend'];

		if(ckprivacy('blog', 1)) {
			include_once(S_ROOT.'./source/function_cp.php');
			feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links'], $fs['target_ids'], $fs['friend']);
		}
	}

	//角色切换
	if(!empty($__SGLOBAL)) $_SGLOBAL = $__SGLOBAL;

	return $blogarr;
}

//处理tag
function tag_batch($blogid, $tags) {
	global $_SGLOBAL;

	$tagarr = array();
	$tagnames = empty($tags)?array():array_unique(explode(' ', $tags));
	if(empty($tagnames)) return $tagarr;

	$vtags = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid, tagname, close FROM ".tname('tag')." WHERE tagname IN (".simplode($tagnames).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['tagname'] = addslashes($value['tagname']);
		$vkey = md5($value['tagname']);
		$vtags[$vkey] = $value;
	}
	$updatetagids = array();
	foreach ($tagnames as $tagname) {
		if(!preg_match('/^([\x7f-\xff_-]|\w){3,20}$/', $tagname)) continue;
		
		$vkey = md5($tagname);
		if(empty($vtags[$vkey])) {
			$setarr = array(
				'tagname' => $tagname,
				'uid' => $_SGLOBAL['supe_uid'],
				'dateline' => $_SGLOBAL['timestamp'],
				'blognum' => 1
			);
			$tagid = inserttable('tag', $setarr, 1);
			$tagarr[$tagid] = $tagname;
		} else {
			if(empty($vtags[$vkey]['close'])) {
				$tagid = $vtags[$vkey]['tagid'];
				$updatetagids[] = $tagid;
				$tagarr[$tagid] = $tagname;
			}
		}
	}
	if($updatetagids) $_SGLOBAL['db']->query("UPDATE ".tname('tag')." SET blognum=blognum+1 WHERE tagid IN (".simplode($updatetagids).")");
	$tagids = array_keys($tagarr);
	$inserts = array();
	foreach ($tagids as $tagid) {
		$inserts[] = "('$tagid','$blogid')";
	}
	if($inserts) $_SGLOBAL['db']->query("REPLACE INTO ".tname('tagblog')." (tagid,blogid) VALUES ".implode(',', $inserts));

	return $tagarr;
}

//获取日志图片
function getmessagepic($message) {
	$pic = '';
	$message = stripslashes($message);
	preg_match("/src\=[\"\']*([^\>\s]{25,105})\.(jpg|gif|png)/i", $message, $mathes);
	if(!empty($mathes[1]) || !empty($mathes[2])) {
		$pic = "{$mathes[1]}.{$mathes[2]}";
	}
	return addslashes($pic);
}

//屏蔽html
function checkhtml($html) {
	$html = stripslashes($html);
	if(!checkperm('allowhtml')) {
		$html = preg_replace("/\<script.*?\>.*?\<\/script\>/is", '', $html);//去掉script
		preg_match_all("/\<([^\<]+)\>/is", $html, $ms);
		$searchs = $replaces = array();
		if($ms[1]) {
			$allowtags = 'img|a|font|div|table|tbody|caption|tr|td|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|object|param|embed';//允许的标签
			$ms[1] = array_unique($ms[1]);
			foreach ($ms[1] as $value) {
				$value = shtmlspecialchars($value);
				$searchs[] = "&lt;".$value."&gt;";
				$value = str_replace(array('\\','/*'), array('.','/.'), $value);
				$value = preg_replace(array("/(javascript|script|eval|behaviour|expression)/i", "/(\s+|&quot;|')on/i"), array('.', ' .'), $value);
				if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
					$value = '';
				}
				$replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
			}
		}
		$html = shtmlspecialchars($html);
		if($searchs) {
			$html = str_replace($searchs, $replaces, $html);
		}
		$html = preg_replace("/\&amp\;lt\;(.*?)\&amp\;gt\;/is", '&lt;\\1&gt;', $html);//恢复<>显示
	}
	$html = preg_replace("/\<(.*?)width[=|:].*?(\s|\>|\'|\"|;)/is", '<\\1\\2', $html);
	$html = addslashes($html);
	
	return $html;
}

?>
