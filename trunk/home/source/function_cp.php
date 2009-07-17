<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_cp.php 10978 2009-01-14 02:39:06Z liguode $
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
		return cplang('lack_of_access_to_upload_file_size');
	}

	//判断后缀
	$fileext = fileext($FILE['name']);
	if(!in_array($fileext, $allowpictype)) {
		return cplang('only_allows_upload_file_types');
	}

	//获取目录
	if(!$filepath = getfilepath($fileext, true)) {
		return cplang('unable_to_create_upload_directory_server');
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
			return cplang('inadequate_capacity_space');
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
					$albumname = sgmdate('Ymd');
					$albumid = album_creat(array('albumname' => $albumname));
				}
			}
		}
	} else {
		$albumname = sgmdate('Ymd');
		$albumid = album_creat(array('albumname' => $albumname));
	}

	//本地上传
	$new_name = $_SC['attachdir'].'./'.$filepath;
	$tmp_name = $FILE['tmp_name'];
	if(@copy($tmp_name, $new_name)) {
		@unlink($tmp_name);
	} elseif((function_exists('move_uploaded_file') && @move_uploaded_file($tmp_name, $new_name))) {
	} elseif(@rename($tmp_name, $new_name)) {
	} else {
		return cplang('mobile_picture_temporary_failure');
	}
	
	//检查是否图片
	if(function_exists('getimagesize') && !@getimagesize($new_name)) {
		@unlink($new_name);
		return cplang('only_allows_upload_file_types');
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

	//进行ftp上传
	if($_SCONFIG['allowftp']) {
		include_once(S_ROOT.'./source/function_ftp.php');
		if(ftpupload($new_name, $filepath)) {
			$pic_remote = 1;
			$album_picflag = 2;
		} else {
			@unlink($new_name);
			@unlink($new_name.'.thumb.jpg');
			runlog('ftp', 'Ftp Upload '.$new_name.' failed.');
			return cplang('ftp_upload_file_size');
		}
	} else {
		$pic_remote = 0;
		$album_picflag = 1;
	}
	
	//入库
	$title = getstr($title, 150, 1, 1, 1);

	//入库
	$setarr = array(
		'albumid' => $albumid,
		'uid' => $_SGLOBAL['supe_uid'],
		'dateline' => $_SGLOBAL['timestamp'],
		'filename' => addslashes($FILE['name']),
		'postip' => getonlineip(),
		'title' => $title,
		'type' => addslashes($FILE['type']),
		'size' => $FILE['size'],
		'filepath' => $filepath,
		'thumb' => $thumb,
		'remote' => $pic_remote
	);
	$setarr['picid'] = inserttable('pic', $setarr, 1);

	//更新附件大小
	//积分
	$setsql = '';
	if($pic_credit = creditrule('get', 'pic')) {
		$setsql = ",credit=credit+$pic_credit";
	}
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$FILE[size]', updatetime='$_SGLOBAL[timestamp]' $setsql WHERE uid='$_SGLOBAL[supe_uid]'");

	//相册更新
	if($albumid) {
		$file = $filepath.($thumb?'.thumb.jpg':'');
		$_SGLOBAL['db']->query("UPDATE ".tname('album')."
			SET picnum=picnum+1, updatetime='$_SGLOBAL[timestamp]', pic='$file', picflag='$album_picflag'
			WHERE albumid='$albumid'");
	}

	return $setarr;
}

//数据流保存，所有数据均为存放相册的所以写入的数据一定只能是图片
function stream_save($strdata, $albumid = 0, $fileext = 'jpg', $name='', $title='', $delsize=0) {
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
				if($space['attachsize'] + $size - $delsize > $maxattachsize + $space['addsize']) {
					@unlink($newfilename);
					return -1;
				}
			}
			
			//检查是否图片
			if(function_exists('getimagesize') && !@getimagesize($newfilename)) {
				@unlink($newfilename);
				return -2;
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
			$filename = addslashes(($name ? $name : substr(strrchr($filepath, '/'), 1)));
			$title = $title;
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
							$albumname = sgmdate('Ymd');
							$albumid = album_creat(array('albumname' => $albumname));
						}
					}
				}
			} else {
				$albumname = sgmdate('Ymd');
				$albumid = album_creat(array('albumname' => $albumname));
			}

			$setarr = array(
				'albumid' => $albumid,
				'uid' => $_SGLOBAL['supe_uid'],
				'dateline' => $_SGLOBAL['timestamp'],
				'filename' => $filename,
				'postip' => getonlineip(),
				'title' => $title,
				'type' => $fileext,
				'size' => $size,
				'filepath' => $filepath,
				'thumb' => $thumb
			);
			$setarr['picid'] = inserttable('pic', $setarr, 1);

			//更新附件大小
			//积分
			$setsql = '';
			if($pic_credit = creditrule('get', 'pic')) {
				$setsql = ",credit=credit+$pic_credit";
			}
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET attachsize=attachsize+'$size', updatetime='$_SGLOBAL[timestamp]' $setsql WHERE uid='$_SGLOBAL[supe_uid]'");

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
			$siteurl = '';
			if(empty($setarr['remote'])) {
				$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
				$siteurl = 'http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strexists($uri, '/api') ? (strrpos($uri, '/')-3):(strrpos($uri, '/')+1));
			}
			$setarr['filepathall'] = $siteurl.mkpicurl($setarr, 0);

			return $setarr;
    	} else {
    		fclose($handle);
    	}
	}
	return -3;
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

		$fs['title_template'] = '{actor} '.cplang('create_a_new_album').' {album}';
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
function feed_add($icon, $title_template='', $title_data=array(), $body_template='', $body_data=array(), $body_general='', $images=array(), $image_links=array(), $target_ids='', $friend='', $appid=UC_APPID, $returnid=0) {
	global $_SGLOBAL;

	$feedarr = array(
		'appid' => $appid,//获取appid myop为0
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
	
	//去重
	$query = $_SGLOBAL['db']->query("SELECT feedid FROM ".tname('feed')." WHERE uid='$feedarr[uid]' AND hash_data='$feedarr[hash_data]' LIMIT 0,1");
	if($oldfeed = $_SGLOBAL['db']->fetch_array($query)) {
		updatetable('feed', $feedarr, array('feedid'=>$oldfeed['feedid']));
		return 0;
	}
	
	if($returnid) {
		return inserttable('feed', $feedarr, $returnid);
	} else {
		inserttable('feed', $feedarr);
		return 1;
	}

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
	
	if(ckprivacy('share', 1)) {
		$body_data['sid'] = $sid;
		feed_add('share', "{actor} $title_template", array(), $body_template, $body_data, $body_general, $images, $image_links);
	}
}

//通知
function notification_add($uid, $type, $note, $returnid=0) {
	global $_SGLOBAL;

	//获取对方的筛选条件
	$user = getspace($uid);
	$filter = empty($user['privacy']['filter_note'])?array():array_keys($user['privacy']['filter_note']);
	
	$setarr = array(
		'uid' => $uid,
		'type' => $type,
		'new' => 1,
		'authorid' => $_SGLOBAL['supe_uid'],
		'author' => $_SGLOBAL['supe_username'],
		'note' => addslashes(sstripslashes($note)),
		'dateline' => $_SGLOBAL['timestamp']
	);
	if(cknote_uid($setarr, $filter)) {
		//更新我的好友关系热度
		$_SGLOBAL['db']->query("UPDATE ".tname('friend')." SET num=num+1 WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'");
	
		//更新用户通知
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET notenum=notenum+1 WHERE uid='$uid'");
	
		if($returnid) {
			return inserttable('notification', $setarr, $returnid);
		} else {
			inserttable('notification', $setarr);
		}
	}
}

//检查屏蔽通知
function cknote_uid($note, $filter) {
	
	if($filter) {
		$key = $note['type'].'|0';
		if(in_array($key, $filter)) {
			return false;
		} else {
			$key = $note['type'].'|'.$note['authorid'];
			if(in_array($key, $filter)) {
				return false;
			}
		}
	}
	return true;
}

//更新好友状态
function friend_update($uid, $username, $fuid, $fusername, $op='add', $gid=0) {
	global $_SGLOBAL, $_SCONFIG;

	if(empty($uid) || empty($fuid)) return false;

	$flog = array(
			'uid' => $uid > $fuid ? $uid : $fuid,
			'fuid' => $uid > $fuid ? $fuid : $uid,
			'dateline' => $_SGLOBAL['timestamp']
		);
	//好友状态
	if($op == 'add' || $op == 'invite') {
		//自己
		if($uid != $fuid) {
			inserttable('friend', array('uid'=>$uid, 'fuid'=>$fuid, 'fusername'=>$fusername, 'status'=>1, 'gid'=>$gid, 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
			//对方更新
			if($op == 'invite') {
				//邀请模式
				inserttable('friend', array('uid'=>$fuid, 'fuid'=>$uid, 'fusername'=>$username, 'status'=>1, 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
			} else {
				updatetable('friend', array('status'=>1, 'dateline'=>$_SGLOBAL['timestamp']), array('uid'=>$fuid, 'fuid'=>$uid));
			}
			//用户中心添加
			include_once S_ROOT.'./uc_client/client.php';
			uc_friend_add($uid, $fuid);
			uc_friend_add($fuid, $uid);

			$flog['action'] = 'add';
		}
	} else {
		//删除
		$_SGLOBAL['db']->query("DELETE FROM ".tname('friend')." WHERE (uid='$uid' AND fuid='$fuid') OR (uid='$fuid' AND fuid='$uid')");
		//从用户中心删除
		include_once S_ROOT.'./uc_client/client.php';
		uc_friend_delete($uid, array($fuid));
		uc_friend_delete($fuid, array($uid));

		$flog['action'] = 'delete';
	}

	if($_SCONFIG['my_status']) inserttable('friendlog', $flog, 0, true);
	//缓存
	friend_cache($uid);
	friend_cache($fuid);
}

//更新好友缓存
function friend_cache($uid) {
	global $_SGLOBAL, $space, $_SCONFIG;

	if(!empty($space) && $space['uid'] == $uid) {
		$thespace = $space;
	} else {
		$thespace = getspace($uid);
	}
	if(empty($thespace)) {
		return false;
	}
	$groupids = empty($thespace['privacy']['filter_gid'])?array():$thespace['privacy']['filter_gid'];

	//好友缓存
	$max_friendnum = 500;//最多显示feed好友数
	$friendlist = $fmod = $feedfriendlist = $ffmod = '';
	$i = $count = 0;
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$uid' AND status='1' ORDER BY num DESC, dateline DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['fuid']) {
			$friendlist .= $fmod.$value['fuid'];
			$fmod = ',';
			if($i < $max_friendnum && (empty($groupids) || !in_array($value['gid'], $groupids))) {
				$feedfriendlist .= $ffmod.$value['fuid'];
				$ffmod = ',';
				$i++;
			}
			$count++;
		}
	}
	if($count > 5000) {
		$friendlist = '';//超过5000不再缓存
	}
	updatetable('spacefield', array('friend'=>$friendlist, 'feedfriend'=>$feedfriendlist), array('uid'=>$uid));
	//数量
	if($thespace['friendnum'] != $count) {
		updatetable('space', array('friendnum'=>$count), array('uid'=>$uid));
	}
	//变更记录
	if($_SCONFIG['my_status']) {
		inserttable('userlog', array('uid'=>$uid, 'action'=>'update', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);
	}
}

//检查验证码
function ckseccode($seccode) {
	global $_SGLOBAL, $_SCOOKIE, $_SCONFIG;

	$check = true;
	if($_SCONFIG['questionmode']) {
		include_once(S_ROOT.'./data/data_spam.php');
		$cookie_seccode = intval($_SCOOKIE['seccode']);
		$seccode = trim($seccode);
		if($seccode != $_SGLOBAL['spam']['answer'][$cookie_seccode]) {
			$check = false;
		}
	} else {
		$cookie_seccode = empty($_SCOOKIE['seccode'])?'':authcode($_SCOOKIE['seccode'], 'DECODE');
		if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
			$check = false;
		}
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
		$album = array('uid'=>$_SGLOBAL['supe_uid'], 'albumid'=>-1, 'albumname'=>cplang('default_albumname'), 'picnum'=>$picnum, 'target_ids'=>'', 'friend'=>0);
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

	$fs['title_template'] = '{actor} '.cplang('upload_a_new_picture');
	$fs['title_data'] = array();

	$fs['body_template'] = '<b>{album}</b><br>'.cplang('the_total_picture', array('{picnum}'));
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
function invite_update($inviteid, $uid, $username, $m_uid, $m_username, $appid=0) {
	global $_SGLOBAL, $_SN;

	if($uid && $uid != $m_uid) {
		$friendstatus = getfriendstatus($uid, $m_uid);
		if($friendstatus < 1) {
			
			friend_update($uid, $username, $m_uid, $m_username, 'invite');
			
			//查找邀请记录
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('invite')." WHERE uid='$m_uid' AND fuid='$uid'");
			if($oldinvite = $_SGLOBAL['db']->fetch_array($query)) {
				//已经邀请过
				return false;
			}
			
			//奖励积分
			$getcredit = creditrule('get', 'invite');
			$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit+$getcredit WHERE uid='$m_uid'");

			//feed
			$_SGLOBAL['supe_uid'] = $m_uid;
			$_SGLOBAL['supe_username'] = $m_username;

			//实名
			realname_set($uid, $username);
			realname_get();

			$title_template = cplang('feed_invite');
			$tite_data = array('username'=>'<a href="space.php?uid='.$uid.'">'.stripslashes($_SN[$uid]).'</a>');
			feed_add('friend', $title_template, $tite_data);

			//通知
			$_SGLOBAL['supe_uid'] = $uid;
			$_SGLOBAL['supe_username'] = $username;
			notification_add($m_uid, 'friend', cplang('note_invite'));

			//更新邀请
			$setarr = array('fuid'=>$uid, 'fusername'=>$username, 'appid'=>$appid);
			if($inviteid) {
				updatetable('invite', $setarr, array('id'=>$inviteid));
			} else {
				$setarr['uid'] = $m_uid;
				inserttable('invite', $setarr, 0, true);//插入邀请记录
			}
		}
	}
}

//获得邀请
function invite_get($uid, $code) {
	global $_SGLOBAL, $_SN;

	$invitearr = array();
	if($uid && $code) {
		$query = $_SGLOBAL['db']->query("SELECT i.*, s.username, s.name, s.namestatus
			FROM ".tname('invite')." i
			LEFT JOIN ".tname('space')." s ON s.uid=i.uid
			WHERE i.uid='$uid' AND i.code='$code' AND i.fuid='0'");
		if($invitearr = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$invitearr = saddslashes($invitearr);
		}
	}
	return $invitearr;
}

//实名认证
function ckrealname($type, $return=0) {
	global $_SCONFIG, $space;
	$result = true;
	if($_SCONFIG['realname'] && empty($space['namestatus']) && empty($_SCONFIG['name_allow'.$type])) {
		if(empty($return)) showmessage('no_privilege_realname');
		$result = false;
	}
	return $result;
}

//新用户发言
function cknewuser($return=0) {
	global $_SGLOBAL, $_SCONFIG, $space;
	$result = true;
	//见习时间
	if($_SCONFIG['newusertime'] && $_SGLOBAL['timestamp']-$space['dateline']<$_SCONFIG['newusertime']*3600) {
		if(empty($return)) showmessage('no_privilege_newusertime', '', 1, array($_SCONFIG['newusertime']));
		$result = false;
	}
	//需要上传头像
	if($_SCONFIG['need_avatar'] && empty($space['avatar'])) {
		if(empty($return)) showmessage('no_privilege_avatar');
		$result = false;
	}
	//强制新用户好友个数
	if($_SCONFIG['need_friendnum'] && $space['friendnum']<$_SCONFIG['need_friendnum']) {
		if(empty($return)) showmessage('no_privilege_friendnum', '', 1, array($_SCONFIG['need_friendnum']));
		$result = false;
	}
	//强制新用户好友个数
	if($_SCONFIG['need_email'] && empty($space['emailcheck'])) {
		if(empty($return)) showmessage('no_privilege_email');
		$result = false;
	}
	return $result;
}

//发送邮件到队列
function smail($touid, $email, $subject, $message='') {
	global $_SGLOBAL, $_SCONFIG;
	
	$cid = 0;
	if($touid && $_SCONFIG['sendmailday']) {
		//获得空间
		$tospace = getspace($touid);
		if(empty($tospace)) return false;
		
		$sendmail = empty($tospace['sendmail'])?array():unserialize($tospace['sendmail']);
		if($tospace['emailcheck'] && $tospace['email'] && $_SGLOBAL['timestamp'] - $tospace['lastlogin'] > $_SCONFIG['sendmailday']*86400 && (empty($sendmail) || !empty($sendmail[$mailtype]))) {
			//获得下次发送时间
			if(empty($tospace['lastsend'])) {
				$tospace['lastsend'] = $_SGLOBAL['timestamp'];
			}
			if(empty($sendmail['frequency'])) $sendmail['frequency'] = 0;
			$sendtime = $tospace['lastsend'] + $sendmail['frequency'];
			
			//检查是否存在当前用户队列
			$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mailcron')." WHERE touid='$touid' LIMIT 1");
			if($value = $_SGLOBAL['db']->fetch_array($query)) {
				$cid = $value['cid'];
				if($value['sendtime'] < $sendtime) $sendtime = $value['sendtime'];
				updatetable('mailcron', array('email'=>addslashes($tospace['email']), 'sendtime'=>$sendtime), array('cid'=>$cid));
			} else {
				$cid = inserttable('mailcron', array('touid'=>$touid, 'email'=>addslashes($tospace['email']), 'sendtime'=>$sendtime), 1);
			}
		}
	} elseif($email) {
		//直接插入邮件
		$email = getstr($email, 80, 1, 1);
		
		//检查是否存在当前队列
		$cid = 0;
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('mailcron')." WHERE email='$email' LIMIT 1");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			$cid = $value['cid'];
		} else {
			$cid = inserttable('mailcron', array('email'=>$email), 1);
		}
	}
	
	if($cid) {
		//插入邮件内容队列
		$setarr = array(
			'cid' => $cid,
			'subject' => addslashes(stripslashes($subject)),
			'message' => addslashes(stripslashes($message)),
			'dateline' => $_SGLOBAL['timestamp']
		);
		inserttable('mailqueue', $setarr);
	}
}

//检查黑名单
function isblacklist($to_uid) {
	global $_SGLOBAL;
	return getcount('blacklist', array('uid'=>$to_uid, 'buid'=>$_SGLOBAL['supe_uid']));
}

?>