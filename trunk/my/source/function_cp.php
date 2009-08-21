<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cp.php 7278 2008-05-05 06:06:48Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//保存图片
function pic_save($FILE, $albumid, $title) {
	global $_SGLOBAL, $_SCONFIG, $space, $_SC;

	//允许上传类型
	$allowpictype = array('jpg','gif','png');

	//检查
	$FILE['size'] = intval($FILE['size']);
	if(empty($FILE['size']) || empty($FILE['tmp_name']) || !empty($FILE['error'])) {
		return lang('lack_of_access_to_upload_file_size');
	}

	//判断后缀
	$fileext = fileext($FILE['name']);
	if(!in_array($fileext, $allowpictype)) {
		return lang('only_allows_upload_file_types');
	}

	//获取目录
	if(!$filepath = getfilepath($fileext, true)) {
		return lang('unable_to_create_upload_directory_server');
	}
	
	//检查空间大小
	if(empty($space)) {
		$query = $_SGLOBAL['db']->query("SELECT username, credit, groupid, attachsize, addsize FROM ".tname('space')." WHERE uid='$_SGLOBAL[supe_uid]'");
		$space = $_SGLOBAL['db']->fetch_array($query);
		$_SGLOBAL['supe_username'] = addslashes($space['username']);
	}
	$_SGLOBAL['member'] = $space;
			
	$maxattachsize = intval(checkperm('maxattachsize'));//单位MB
	if($maxattachsize) {//0为不限制
		if($space['attachsize'] + $FILE['size'] > $maxattachsize + $space['addsize']) {
			return lang('inadequate_capacity_space');
		}
	}

	//相册选择
	$albumfriend = 0;
	if($albumid) {
		preg_match("/^new\:(.+)$/i", $albumid, $matchs);
		if(!empty($matchs[1])) {
			$albumname = shtmlspecialchars(trim($matchs[1]));
			if(empty($albumname)) $albumname = sgmdate('Ymd');
			$albumid = album_creat(array('albumname' => $albumname));
		} else {
			$albumid = intval($albumid);
			if($albumid) {
				$query = $_SGLOBAL['db']->query("SELECT albumname,friend FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
				if($value = $_SGLOBAL['db']->fetch_array($query)) {
					$albumname = addslashes($value['albumname']);
					$albumfriend = $value['friend'];
				} else {
					$albumid = 0;
				}
			}
		}
	}

	//本地上传
	$new_name = $_SC['attachdir'].'./'.$filepath;
	$tmp_name = $FILE['tmp_name'];
	if(@copy($tmp_name, $new_name)) {
		@unlink($tmp_name);
	} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
	} elseif(@rename($tmp_name, $new_name)) {
	} else {
		return lang('mobile_picture_temporary_failure');
	}
	
	//缩略图
	include_once(S_ROOT.'./source/function_image.php');
	$thumbpath = makethumb($new_name);
	$thumb = empty($thumbpath)?0:1;
	
	//是否压缩
	//获取上传后图片大小
	if(@$newfilesize = filesize($new_name)) {
		$FILE['size'] = $newfilesize;
	}

	//水印
	if($_SCONFIG['allowwatermark']) {
		makewatermark($new_name);
	}

	//入库
	$title = getstr($title, 150, 1, 1, 1);
	
	//入库
	$setarr = array(
		'albumid' => $albumid,
		'uid' => $_SGLOBAL['supe_uid'],
		'dateline' => $_SGLOBAL['timestamp'],
		'filename' => addslashes($FILE['name']),
		'title' => $title,
		'type' => addslashes($FILE['type']),
		'size' => $FILE['size'],
		'filepath' => $filepath,
		'thumb' => $thumb
	);
	$setarr['picid'] = inserttable('pic', $setarr, 1);

	//更新附件大小
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$FILE[size]', updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");

	//相册更新
	$setarr['picflag'] = 1;
	if($albumid) {
		$file = $filepath.($thumb?'.thumb.jpg':'');
		$_SGLOBAL['db']->query("UPDATE ".tname('album')." 
			SET picnum=picnum+1, updatetime='$_SGLOBAL[timestamp]', pic='$file', picflag='$setarr[picflag]' 
			WHERE albumid='$albumid'");
	}

	//最后进行ftp上传,防止垃圾产生
	if($_SCONFIG['allowftp']) {
		include_once(S_ROOT.'./source/function_ftp.php');
		if(ftpupload($new_name, $filepath)) {
			$setarr['remote'] = 1;
			$setarr['picflag'] = 2;
			updatetable('pic', array('remote'=>$setarr['remote']), array('picid'=>$setarr['picid']));
			if($albumid) updatetable('album', array('picflag'=>$setarr['picflag']), array('albumid'=>$albumid));
		}
	}

	return $setarr;
}

//数据流保存，所有数据均为存放相册的所以写入的数据一定只能是图片
function stream_save($strdata, $albumid = 0, $fileext = 'jpg') {
	global $_SGLOBAL, $space, $_SCONFIG, $_SC;

	$setarr = array();
	$filepath = getfilepath($fileext, true);
	$newfilename = $_SC['attachdir'].'./'.$filepath;

	if($handle = fopen($newfilename, 'wb')) {
		if(fwrite($handle, $strdata) !== FALSE) {
			fclose($handle);
			$size = filesize($newfilename);
			//检查空间大小
			
			if(empty($space)) {
				$query = $_SGLOBAL['db']->query("SELECT username, credit, groupid, attachsize, addsize FROM ".tname('space')." WHERE uid='$_SGLOBAL[supe_uid]'");
				$space = $_SGLOBAL['db']->fetch_array($query);
				$_SGLOBAL['supe_username'] = addslashes($space['username']);
			}
			$_SGLOBAL['member'] = $space;
				
			$maxattachsize = intval(checkperm('maxattachsize'));//单位MB
			if($maxattachsize) {//0为不限制
				if($space['attachsize'] + $size > $maxattachsize + $space['addsize']) {
					@unlink($newfilename);
					return false;
				}
			}

			//缩略图
			include_once(S_ROOT.'./source/function_image.php');
			$thumbpath = makethumb($newfilename);
			$thumb = empty($thumbpath)?0:1;
		
			//大头帖不添加水印
			if($_SCONFIG['allowwatermark']) {
				makewatermark($newfilename);
			}

			//入库
			$filename = addslashes(substr(strrchr($filepath, '/'), 1));
			$title = '';
			if($albumid) {
				preg_match("/^new\:(.+)$/i", $albumid, $matchs);
				if(!empty($matchs[1])) {
					$albumname = shtmlspecialchars(trim($matchs[1]));
					if(empty($albumname)) $albumname = sgmdate('Ymd');
					$albumid = album_creat(array('albumname' => $albumname));
				} else {
					$albumid = intval($albumid);
					if($albumid) {
						$query = $_SGLOBAL['db']->query("SELECT albumname,friend FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
						if($value = $_SGLOBAL['db']->fetch_array($query)) {
							$albumname = addslashes($value['albumname']);
							$albumfriend = $value['friend'];
						} else {
							$albumid = 0;
						}
					}
				}
			}
			$setarr = array(
				'albumid' => $albumid,
				'uid' => $_SGLOBAL['supe_uid'],
				'dateline' => $_SGLOBAL['timestamp'],
				'filename' => $filename,
				'title' => $title,
				'type' => $fileext,
				'size' => $size,
				'filepath' => $filepath,
				'thumb' => $thumb
			);
			$setarr['picid'] = inserttable('pic', $setarr, 1);
		
			//更新附件大小
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$size', updatetime='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
		
			//相册更新
			if($albumid) {
				$file = $filepath.($thumb?'.thumb.jpg':'');
				$_SGLOBAL['db']->query("UPDATE ".tname('album')." 
					SET picnum=picnum+1, updatetime='$_SGLOBAL[timestamp]', pic='$file', picflag='1' 
					WHERE albumid='$albumid'");
			}
			
			//最后进行ftp上传,防止垃圾产生
			if($_SCONFIG['allowftp']) {
				include_once(S_ROOT.'./source/function_ftp.php');
				if(ftpupload($newfilename, $filepath)) {
					$setarr['remote'] = 1;
					updatetable('pic', array('remote'=>$setarr['remote']), array('picid'=>$setarr['picid']));
					if($albumid) updatetable('album', array('picflag'=>2), array('albumid'=>$albumid));
				}
			}
			
			return $setarr;
    	} else {
    		fclose($handle);
    	}
	}
	return false;
}

//创建相册
function album_creat($arr) {
	global $_SGLOBAL;
	//检查相册是否存在
	$albumid = getcount('album', array('albumname'=>$arr['albumname'], 'uid'=>$_SGLOBAL['supe_uid']), 'albumid');
	if($albumid) {
		return $albumid;
	} else {
		$arr['uid'] = $_SGLOBAL['supe_uid'];
		$arr['username'] = $_SGLOBAL['supe_username'];
		$arr['dateline'] = $arr['updatetime'] = $_SGLOBAL['timestamp'];
		$albumid = inserttable('album', $arr, 1);
		
		//事件
		$fs = array();
		$fs['icon'] = 'album';
	
		$fs['title_template'] = '{actor} '.lang('create_a_new_album').' {album}';
		$fs['title_data'] = array('album'=>"<a href=\"space.php?uid=$_SGLOBAL[supe_uid]&do=album&id=$albumid\">$arr[albumname]</a>");
		$fs['body_template'] = '';
		$fs['body_data'] = array();
		$fs['body_general'] = '';

		if(ckprivacy('album', 1)) {
			feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general']);
		}

		return $albumid;
	}
}

//获取上传路径
function getfilepath($fileext, $mkdir=false) {
	global $_SGLOBAL, $_SC;

	$filepath = "{$_SGLOBAL['supe_uid']}_{$_SGLOBAL['timestamp']}".random(4).".$fileext";
	$name1 = gmdate('Ym');
	$name2 = gmdate('j');

	if($mkdir) {
		$newfilename = $_SC['attachdir'].'./'.$name1;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $filepath;
			}
		}
		$newfilename .= '/'.$name2;
		if(!is_dir($newfilename)) {
			if(!@mkdir($newfilename)) {
				runlog('error', "DIR: $newfilename can not make");
				return $name1.'/'.$filepath;
			}
		}
	}
	return $name1.'/'.$name2.'/'.$filepath;
}

//获取目录
function sreaddir($dir, $extarr=array()) {
	$dirs = array();
	if($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(!empty($extarr) && is_array($extarr)) {
				if(in_array(strtolower(fileext($file)), $extarr)) {
					$dirs[] = $file;
				}
			} else if($file != '.' && $file != '..') {
				$dirs[] = $file;
			}
		}
		closedir($dh);
	}
	return $dirs;
}

//检查邮箱是否有效
function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

//获取相册封面图片
function getalbumpic($uid, $id) {
	global $_SGLOBAL;
	$query = $_SGLOBAL['db']->query("SELECT filepath, thumb FROM ".tname('pic')." WHERE albumid='$id' AND uid='$uid' ORDER BY thumb DESC, dateline DESC LIMIT 0,1");
	if($pic = $_SGLOBAL['db']->fetch_array($query)) {
		return $pic['filepath'].($pic['thumb']?'.thumb.jpg':'');
	} else {
		return '';
	}
}

//获取个人分类
function getclassarr($uid) {
	global $_SGLOBAL;

	$classarr = array();
	$query = $_SGLOBAL['db']->query("SELECT classid, classname FROM ".tname('class')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$classarr[$value['classid']] = $value;
	}
	return $classarr;
}

//获取相册
function getalbums($uid) {
	global $_SGLOBAL;
	
	$albums = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE uid='$uid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$albums[$value['albumid']] = $value;
	}
	return $albums;
}

//事件发布
function feed_add($icon, $title_template='', $title_data='', $body_template='', $body_data='', $body_general='', $images=array(), $image_links=array(), $target_ids='', $friend='') {
	global $_SGLOBAL;
	
	if(empty($_SGLOBAL['supe_uid'])) return false;
	
	$feedarr = array(
		'appid' => UC_APPID,//?获取appid
		'icon' => $icon,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'title_template' => $title_template,
		'body_template' => $body_template,
		'body_general' => $body_general,
		'image_1' => empty($images[0])?'':$images[0],
		'image_1_link' => empty($image_links[0])?'':$image_links[0],
		'image_2' => empty($images[1])?'':$images[1],
		'image_2_link' => empty($image_links[1])?'':$image_links[1],
		'image_3' => empty($images[2])?'':$images[2],
		'image_3_link' => empty($image_links[2])?'':$image_links[2],
		'image_4' => empty($images[3])?'':$images[3],
		'image_4_link' => empty($image_links[3])?'':$image_links[3],
		'target_ids' => $target_ids,
		'friend' => $friend
	);
	$feedarr = sstripslashes($feedarr);//去掉转义
	$feedarr['title_data'] = serialize(sstripslashes($title_data));//数组转化
	$feedarr['body_data'] = serialize(sstripslashes($body_data));//数组转化
	$feedarr['hash_template'] = md5($feedarr['title_template']."\t".$feedarr['body_template']);//喜好hash
	$feedarr['hash_data'] = md5($feedarr['title_template']."\t".$feedarr['title_data']."\t".$feedarr['body_template']."\t".$feedarr['body_data']);//合并hash
	$feedarr = saddslashes($feedarr);//增加转义
	
	inserttable('feed', $feedarr);
}

//分享发布
function share_add($type, $title_template, $body_template, $body_data, $body_general, $image='', $image_link='') {
	global $_SGLOBAL;
	
	$sharearr = array(
		'type' => $type,
		'uid' => $_SGLOBAL['supe_uid'],
		'username' => $_SGLOBAL['supe_username'],
		'dateline' => $_SGLOBAL['timestamp'],
		'title_template' => $title_template,
		'body_template' => $body_template,
		'body_general' => $body_general,
		'image' => empty($image)?'':$image,
		'image_link' => empty($image_link)?'':$image_link
	);
	$sharearr = sstripslashes($sharearr);//去掉转义
	$sharearr['body_data'] = serialize(sstripslashes($body_data));//数组转化
	$sharearr['hash_data'] = md5($sharearr['title_template']."\t".$sharearr['body_template']."\t".$sharearr['body_data']);//合并hash
	$sharearr = saddslashes($sharearr);//增加转义
	
	$sid = inserttable('share', $sharearr, 1);
	
	//添加feed
	$images = empty($image)?array():array($image);
	$image_links = empty($image_link)?array():array($image_link);
	if($type == 'link') {
		$body_data['link'] .= " (<a href=\"space.php?uid=$_SGLOBAL[supe_uid]&do=share&id=$sid\">".lang('comment')."</a>)";
	}
	if(ckprivacy('share', 1)) {
		feed_add('share', "{actor} $title_template", array(), $body_template, $body_data, $body_general, $images, $image_links);
	}
}

//通知
function notification_add($uid, $type, $note) {
	global $_SGLOBAL;

	$setarr = array(
		'uid' => $uid,
		'type' => $type,
		'new' => 1,
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'note' => addslashes(sstripslashes($note)),
		'dateline' => $_SGLOBAL['timestamp']
	);
	inserttable('notification', $setarr);
}


//更新好友状态
function friend_update($uid, $username, $fuid, $fusername, $op='add', $gid=0) {
	global $_SGLOBAL;
	
	//好友状态
	if($op == 'add' || $op == 'invite') {
		//自己
		if($uid != $fuid) {
			inserttable('friend', array('uid'=>$uid, 'fuid'=>$fuid, 'fusername'=>$fusername, 'status'=>1, 'gid'=>$gid), 0, true);
			//对方更新
			if($op == 'invite') {
				//邀请模式
				inserttable('friend', array('uid'=>$fuid, 'fuid'=>$uid, 'fusername'=>$username, 'status'=>1), 0, true);
			} else {
				updatetable('friend', array('status'=>1), array('uid'=>$fuid, 'fuid'=>$uid));
			}
			//用户中心添加
			include_once S_ROOT.'./uc_client/client.php';
			uc_friend_add($uid, $fuid);
			uc_friend_add($fuid, $uid);
		}
	} else {
		//删除
		$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE (uid='$uid' AND fuid='$fuid') OR (uid='$fuid' AND fuid='$uid')");
		//从用户中心删除
		include_once S_ROOT.'./uc_client/client.php';
		uc_friend_delete($uid, array($fuid));
		uc_friend_delete($fuid, array($uid));
	}
	
	//缓存
	friend_cache($uid);
	friend_cache($fuid);
}

//更新好友缓存
function friend_cache($uid) {
	global $_SGLOBAL, $space;
	
	if(!empty($space) && $space['uid'] == $uid) {
		$thespace = $space;
	} else {
		$thespace = getspace($uid);
	}
	if(empty($thespace)) {
		showmessage('space_does_not_exist');
	}
	$groupids = empty($thespace['privacy']['filter_gid'])?array():$thespace['privacy']['filter_gid'];
	
	//好友缓存
	$friendlist = $feedfriendlist = array();

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$uid' AND status='1'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$friendlist[] = $value['fuid'];
		if(empty($groupids) || !in_array($value['gid'], $groupids)) {
			$feedfriendlist[] = $value['fuid'];//feed显示
		}
	}
	updatetable('spacefield', array('friend'=>implode(',',$friendlist), 'feedfriend'=>implode(',',$feedfriendlist)), array('uid'=>$uid));
	//数量
	updatetable('space', array('friendnum'=>count($friendlist)), array('uid'=>$uid));
}

//检查验证码
function ckseccode($seccode) {
	global $_SCOOKIE;
	
	$check = true;
	$cookie_seccode = empty($_SCOOKIE['seccode'])?'':authcode($_SCOOKIE['seccode'], 'DECODE');
	if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
		$check = false;
	}
	return $check;
}

//相册产生feed
function album_feed($albumid) {
	global $_SGLOBAL, $space;
	
	if($albumid > 0) {
		//得到最新的4张图片
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('album')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]'");
		if(!$album = $_SGLOBAL['db']->fetch_array($query)) {
			return false;
		}
		if($album['friend']>2) return false;//隐私设置
	} else {
		$picnum = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('pic')." WHERE uid='$_SGLOBAL[supe_uid]' AND albumid='0'"), 0);
		if($picnum<1) return false;
		$album = array('uid'=>$_SGLOBAL['supe_uid'], 'albumid'=>-1, 'albumname'=>mlang('default_albumname'), 'picnum'=>$picnum, 'target_ids'=>'', 'friend'=>0);
		$albumid = 0;
	}
	if(empty($space)) {
		$space = getspace($_SGLOBAL['supe_uid']);
	}
	if(empty($space)) {
		return false;
	}

	//图片
	$fs = array();
	
	$nowdateline = $_SGLOBAL['timestamp']-600;//10分钟内上传的图片
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$albumid' AND uid='$_SGLOBAL[supe_uid]' AND dateline>'$nowdateline' ORDER BY dateline DESC LIMIT 0,4");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$fs['images'][] = mkpicurl($value);
		$fs['image_links'][] = "space.php?uid=$value[uid]&do=album&picid=$value[picid]";
	}
	if(empty($fs['images'])) return false;

	$fs['icon'] = 'album';
	
	$fs['title_template'] = '<b>{actor} '.lang('upload_a_new_picture').'</b>';
	$fs['title_data'] = array();
	
	$fs['body_template'] = '<b>{album}</b><br>'.lang('the_total_picture', array('{picnum}'));
	$fs['body_data'] = array(
		'album' => "<a href=\"space.php?uid=$album[uid]&do=album&id=$album[albumid]\">$album[albumname]</a>",
		'picnum' => $album['picnum']
	);
	$fs['body_general'] = '';
	
	if(ckprivacy('upload', 1)) {
		feed_add($fs['icon'], $fs['title_template'], $fs['title_data'], $fs['body_template'], $fs['body_data'], $fs['body_general'],$fs['images'], $fs['image_links'], $album['target_ids'], $album['friend']);
	}
}

//更新隐私设置
function privacy_update() {
	global $_SGLOBAL, $space;
	updatetable('spacefield', array('privacy'=>addslashes(serialize(sstripslashes($space['privacy'])))), array('uid'=>$_SGLOBAL['supe_uid']));
}

//邀请好友
function invite_update($inviteid, $uid, $username, $fuid, $fusername) {
	global $_SGLOBAL;
	
	if($uid != $fuid) {
		$friendstatus = getfriendstatus($uid, $fuid);
		if($friendstatus < 1) {
			friend_update($uid, $username, $fuid, $fusername, 'invite');
			//更新邀请
			updatetable('invite', array('fuid'=>$uid, 'fusername'=>$username), array('id'=>$inviteid));
			//更新积分
			$getcredit = creditrule('get', 'invite');
			if($getcredit) {
				$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit+$getcredit WHERE uid='$fuid'");
				$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET credit=credit+$getcredit WHERE uid='$fuid'");
			}
			//feed
			$_SGLOBAL['supe_uid'] = $fuid;
			$_SGLOBAL['supe_username'] = $fusername;
			$title_template = lang('feed_invite');
			$tite_data = array('username'=>'<a href="space.php?uid='.$uid.'">'.stripslashes($username).'</a>');
			feed_add('friend', $title_template, $tite_data);
			//通知
			$_SGLOBAL['supe_uid'] = $uid;
			$_SGLOBAL['supe_username'] = $username;
			notification_add($fuid, 'friend', lang('note_invite'));
		}
	}
}

//获得邀请
function invite_get($uid, $code) {
	global $_SGLOBAL;
	
	$invitearr = array();
	if($uid && $code) {
		$query = $_SGLOBAL['db']->query("SELECT i.*, s.username
			FROM ".tname('invite')." i
			LEFT JOIN ".tname('space')." s ON s.uid=i.uid
			WHERE i.uid='$uid' AND i.code='$code' AND i.fuid='0'");
		if($invitearr = $_SGLOBAL['db']->fetch_array($query)) {
			$invitearr = saddslashes($invitearr);
		}
	}
	return $invitearr;
}

?>