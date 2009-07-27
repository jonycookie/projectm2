<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_spacenews.php 11970 2009-04-22 06:12:52Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managespacenews')) {
	showmessage('no_authority_management_operation');
}

$relativetidnum = 10;	//相关信息显示数目
$allowmax = 100;		//最大上传数量
$type = 'news';
$catarr = array();
$perpage = intval(postget('perpage'));			//默认每页显示列表数目
if(empty($perpage)) $perpage = 20;

//附件识别码
$hashstr = smd5($_SGLOBAL['supe_uid'].'/'.$_SGLOBAL['timestamp'].random(6));
if(in_array('news', $_SCONFIG['closechannels'])) {
	showmessage('usetype_no_open');
}

//是否拥有审阅其他人资讯的权限
if(!checkperm('managecheck')) {
	$isuid = 1;
} else {
	$isuid = 0;
}

//获取的变量初始化
$_SGET['page'] = intval(postget('page'));
$_SGET['catid'] = intval(postget('catid'));
$_SGET['itemtypeid'] = intval(postget('itemtypeid'));
$_SGET['folder'] = intval(postget('folder'));
$_SGET['digest'] = intval(postget('digest'));
$_SGET['order'] = postget('order');
$_SGET['sc'] = postget('sc');
$_SGET['searchid'] = intval(postget('searchid'))==0 ? '' : intval(postget('searchid'));
$_SGET['searchkey'] = stripsearchkey(postget('searchkey'));

if(empty($_SGET['folder'])) $_SGET['folder'] = 1;
if(empty($_SGET['subtype'])) $_SGET['subtype'] = '';
($_SGET['page'] < 1) ? $_SGET['page'] = 1 : '';
if(!in_array($_SGET['order'], array('dateline', 'lastpost', 'uid', 'viewnum', 'replynum', 'goodrate', 'badrate'))) {
	$_SGET['order'] = '';
}
if(!in_array($_SGET['sc'], array('ASC', 'DESC'))) {
	$_SGET['sc'] = 'DESC';
}
$urlplus = '&catid='.$_SGET['catid'].'&itemtypeid='.$_SGET['itemtypeid'].'&folder='.$_SGET['folder'].'&digest='.$_SGET['digest'].'&order='.$_SGET['order'].'&sc='.$_SGET['sc'].'&subtype='.$_SGET['subtype'].'&perpage='.$perpage.'&searchkey='.rawurlencode($_SGET['searchkey']);
$newurl = $theurl.$urlplus.'&page='.$_SGET['page'];

if(!empty($_GET['openwindow'])) setcookie('_openwindow', 1);
if(!empty($_COOKIE['_openwindow'])) {
	$_SGET['openwindow'] = 1;
} else {
	$_SGET['openwindow'] = 0;
}

$gradearr = array(
	'0' => $alang['general_state'],
	'1' => $alang['check_grade_1'],
	'2' => $alang['check_grade_2'],
	'3' => $alang['check_grade_3'],
	'4' => $alang['check_grade_4'],
	'5' => $alang['check_grade_5']
);
if(!empty($_SCONFIG['checkgrade'])) {
	$newgradearr = explode("\t", $_SCONFIG['checkgrade']);
	for($i=0; $i<5; $i++) {
		if(!empty($newgradearr[$i])) $gradearr[$i+1] = $newgradearr[$i];
	}
}
	
//INIT RESULT VAR
$showurlarr = $thevalue = $dellistarr = $listarr = array();

//POST METHOD
if (submitcheck('listvaluesubmit')) {
	
	if($_POST['operation'] == 'movefolder' && $_POST['opfolder'] == '1') {
		if(!checkperm('managecheck')) {
			showmessage('spacenews_no_popedom_check');
		}
	}
	
	//LIST UPDATE
	$itemidarr = $tagidarr = array();	//初始化itemidarr、tagidarr数组
	if(empty($_POST['item'])) {		//判断提交过来的是否存在待操作的记录，如果没有，则显示提示信息并退出
		showmessage('space_no_item');
	}
	$itemidstr = simplode($_POST['item']);	//用逗号链接所有的操作ID
	//对提交的数据进行检查
	if($isuid) {	//指定操作用户ID将其连入用SQL语句
		$uidsql = 'AND uid='.$_SGLOBAL['supe_uid'];
	} else {
		$uidsql = '';
	}
	$newidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spaceitems')." WHERE itemid IN ($itemidstr) AND type='news' $uidsql");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$newidarr[] = $value['itemid'];
	}
	if(empty($newidarr)) {
		showmessage('space_no_item');
	}
	$itemidstr = simplode($newidarr);

	switch ($_POST['operation']) {	//跟据操作类型做相应的操作处理
		case 'movecat':		//更改记录分类
			$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET catid=\''.$_POST['opcatid'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'movefolder':	//移动文件夹
			if($_POST['opfolder'] != 1) {
				deleteitemhtml($itemidarr);		//删除已生成的HTML文件
			} else {
				if($_POST['oldfolder'] == 3 && !checkperm('allowundelete')) {
					showmessage('spaceblog_no_popedom');
				}
			}
			//更新文件夹id
			$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET folder=\''.$_POST['opfolder'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'check':	//移动文件夹
			//更新等级
			if(!checkperm('managecheck')) showmessage('you_had_no_competence_to_examine');
			$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET grade=\''.intval($_POST['opcheck']).'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'digest':	//设置精华
			if(!checkperm('managecheck')) showmessage('you_had_no_competence_to_examine');
			$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET digest=\''.$_POST['opdigest'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'top':		//设置置顶
			if(!checkperm('managecheck')) showmessage('you_had_no_competence_to_examine');
			$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET top=\''.$_POST['optop'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'allowreply':	//是否允许评论
			if(!checkperm('managecheck')) showmessage('you_had_no_competence_to_examine');
			$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET allowreply=\''.$_POST['opallowreply'].'\' WHERE itemid IN ('.$itemidstr.')');
			break;
		case 'delete':		//删除操作
			//删除html
			deleteitems('itemid', $itemidstr, $_POST['opdelete']);
			break;
	}
	
	showmessage('do_success', $theurl);
} elseif (submitcheck('valuesubmit')) {
	
	include_once(S_ROOT.'./function/item.func.php');
	if(empty($_POST['page'])) $_POST['page'] = 1;
	$page = intval($_POST['page']);
	if($page < 1) $page = 1;
	$itemid = intval($_POST['itemid']);
	//初始化用户的分页
	if(submitcheck('makepageorder')) { 
		$query = $_SGLOBAL['db']->query('SELECT pageorder,nid FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' ORDER BY pageorder ASC, nid ASC '); 
		$newpageorder = 1;
		while ($row = $_SGLOBAL['db']->fetch_array($query)) {
			updatetable('spacenews', array('pageorder'=>$newpageorder), array('nid'=>$row['nid']));
			$newpageorder++;
		}
	}
	//更新用户的分页
	$_POST['nid'] = empty($_POST['nid'])?0:intval($_POST['nid']);
	$pageorder = intval($_POST['pageorder']);
	if($pageorder < $page) {  //判断用户修改了页面顺序
		$_SGLOBAL['db']->query('UPDATE '.tname('spacenews').' SET pageorder = pageorder+1 WHERE itemid = '.$itemid.' AND pageorder >='.$pageorder.' AND pageorder < '.$page);
	}elseif($pageorder > $page) {
		$_SGLOBAL['db']->query('UPDATE '.tname('spacenews').' SET pageorder = pageorder-1 WHERE itemid = '.$itemid.' AND pageorder <='.$pageorder.' AND pageorder > '.$page);
	}
	
	//更新用户最新更新时间
	if(empty($itemid)) {
		updatetable('members', array('updatetime'=>$_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
	}
	
	//第一个页面处理
	if(empty($itemid) || $page == 1) {
		
		//输入检查
		$_POST['catid'] = intval($_POST['catid']);
		$_POST['customfieldid'] = intval($_POST['customfieldid']);
		$_POST['folder'] = intval($_POST['folder']);
		$_POST['picid'] = empty($_POST['picid'])?0:intval($_POST['picid']);	//图文资讯标志
		
		//发布资讯需要审阅
		if(checkperm('needcheck')) {
			$_POST['folder'] = 2;
			$newurl .= '&folder=2';
		}
	
		//检查输入
		$_POST['subject'] = shtmlspecialchars(trim($_POST['subject']));//标题支持html
		if(strlen($_POST['subject']) < 2 || strlen($_POST['subject']) > 80) {
			showmessage('space_suject_length_error');
		}
		if(empty($_POST['catid'])) {
			showmessage('admin_func_catid_error');
		}

		//自定义信息
		$setcustomfieldtext = empty($_POST['customfieldtext'][$_POST['customfieldid']])?serialize(array()):addslashes(serialize(shtmlspecialchars(sstripslashes($_POST['customfieldtext'][$_POST['customfieldid']]))));

		//TAG处理
		if(empty($_POST['tagname'])) $_POST['tagname'] = '';
		$tagarr = posttag($_POST['tagname']);
		
		//构建数据
		$setsqlarr = array(
			'catid' => $_POST['catid'],
			'subject' => scensor($_POST['subject'], 1),
			'folder' => $_POST['folder'],
			'hash' => $_POST['hash'],
			'picid' => $_POST['picid']
		);

		//等级审核
		if(checkperm('managecheck')) {
			//标题样式
			empty($_POST['strong'])?$_POST['strong']='':$_POST['strong']=1;
			empty($_POST['underline'])?$_POST['underline']='':$_POST['underline']=1;
			empty($_POST['em'])?$_POST['em']='':$_POST['em']=1;
			empty($_POST['fontcolor'])?$_POST['fontcolor']='':$_POST['fontcolor']=$_POST['fontcolor'];
			empty($_POST['fontsize'])?$_POST['fontsize']='':$_POST['fontsize']=$_POST['fontsize'];
			$setsqlarr['styletitle'] = sprintf("%6s%2s%1s%1s%1s",substr($_POST['fontcolor'], -6),substr($_POST['fontsize'],-4,2),$_POST['em'],$_POST['strong'],$_POST['underline']);

			if($setsqlarr['styletitle'] === '           ') {
				$setsqlarr['styletitle']  = '';
			}
		
			$setsqlarr['digest'] = intval($_POST['digest']);
			$setsqlarr['top'] = intval($_POST['top']);
			$setsqlarr['allowreply'] = intval($_POST['allowreply']);
			$setsqlarr['grade'] = intval($_POST['grade']);
		}
		
		//附件
		if(!empty($_POST['divupload']) && is_array($_POST['divupload'])) {
			$setsqlarr['haveattach'] = 1;
			$picflag = 1;
		} else {
			$setsqlarr['haveattach'] = 0;
		}		
		
		//发布时间
		if(empty($_POST['dateline'])) {
			$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
		} else {
			$setsqlarr['dateline'] = sstrtotime($_POST['dateline']);
			if($setsqlarr['dateline'] > $_SGLOBAL['timestamp']) {
				$setsqlarr['dateline'] = $_SGLOBAL['timestamp'];
			}
		}

		if(empty($itemid)) {
			//添加数据
			$op = 'add';
			$setsqlarr['tid'] = empty($_POST['tid'])?0:intval($_POST['tid']);
			$setsqlarr['type'] = $type;
			$setsqlarr['uid'] = $_SGLOBAL['supe_uid'];
			$setsqlarr['username'] = $_SGLOBAL['supe_username'];
			$setsqlarr['lastpost'] = $setsqlarr['dateline'];
			
			//插入数据
			$itemid = inserttable('spaceitems', $setsqlarr, 1);
			//feed
			if (allowfeed() && $setsqlarr['folder'] == 1 && $_POST['addfeed']) {
				$feed['icon'] = 'comment';
				$feed['title_template'] = 'feed_news_title';
				$feed['body_template'] = 'feed_news_message';
				$subjecturl = geturl('action/viewnews/itemid/'.$itemid);
				
				if(empty($_SCONFIG['siteurl'])) {
					$siteurl = getsiteurl();
					$subjecturl = $siteurl.$subjecturl; 
				}
				$feed['body_data'] = array(
					'subject' => '<a href="'.$subjecturl.'">'.$_POST['subject'].'</a>',
					'message' => cutstr(strip_tags(preg_replace("/\[.+?\]/is", '', $_POST['message'])), 150)
				);
				$picurl = getmessagepic(stripslashes($_POST['message']));

				if($picurl && (strpos($picurl, '://') === false)) {
					$picurl = $siteurl.'/'.$picurl;
				}
				if(!empty($picurl)) {
					
					$feed['images'][] = array('url'=>$picurl, 'link'=>$subjecturl);
				}
				
				postfeed($feed);
			}
			//信息与tag关联处理
			postspacetag('add', $type, $itemid, $tagarr);

		} else {
			//更新
			$op = 'update';
			if(!checkperm('managecheck')) $setsqlarr['grade'] = 0;	//编辑后，审核等级归为0
			updatetable('spaceitems', $setsqlarr, array('itemid'=>$itemid));
			
			//信息与tag关联处理
			postspacetag('update', $type, $itemid, $tagarr);
		}
		
		//附件
		if($setsqlarr['haveattach']) {
			$_SGLOBAL['db']->query('UPDATE '.tname('attachments').' SET isavailable=1, type=\''.$type.'\', itemid='.$itemid.', catid=\''.$_POST['catid'].'\' WHERE hash=\''.$_POST['hash'].'\'');
		}
		
		//内容 图片路径和附件路径处理
		$_POST['message'] = preg_replace_callback("/src\=(.{2})([^\>\s]{10,105})\.(jpg|gif|png)/i", 'addurlhttp', $_POST['message']);
		$_POST['message'] = str_replace('href=\"batch.download.php', 'href=\"'.S_URL.'/batch.download.php', $_POST['message']);
		
		$setsqlarr = array(
			'message' => scensor($_POST['message'], 1),
			'postip' => $_SGLOBAL['onlineip'],
			'customfieldid' => $_POST['customfieldid'],
			'customfieldtext' => $setcustomfieldtext
		);
				
		//相关TAG
		$tagnamearr = array_merge($tagarr['existsname'], $tagarr['nonename']);
		$setsqlarr['relativetags'] = addslashes(serialize($tagnamearr));
	
		//包含tag
		$setsqlarr['includetags'] = postgetincludetags($_POST['message'], $tagnamearr);
	
		//相关阅读
		$setsqlarr['relativeitemids'] = getrelativeitemids($itemid, array('news'));
	
		//额外信息
		$setsqlarr['newsauthor'] = shtmlspecialchars(trim($_POST['newsauthor']));
		$setsqlarr['newsfrom'] = shtmlspecialchars(trim($_POST['newsfrom']));
		$setsqlarr['newsurl'] = shtmlspecialchars(trim($_POST['newsurl']));
		$setsqlarr['newsfromurl'] = shtmlspecialchars(trim($_POST['newsfromurl']));
		$setsqlarr['pageorder'] = $pageorder;
		$setsqlarr['itemid'] = $itemid;
		
		if($op == 'add') {
			
			//添加内容
			$arraymessage = explode('###NextPage###', $setsqlarr['message']);
			$firstmessage = 0;
			$insertarr = array();
			
			foreach($arraymessage as $message) {
				$message = trim($message);
				if($firstmessage == 1){
					unset($setsqlarr['customfieldid']);
					unset($setsqlarr['relativetags']);
					unset($setsqlarr['relativeitemids']);
					unset($setsqlarr['includetags']);
					unset($setsqlarr['customfieldtext']);
				}
				$firstmessage++;
				$setsqlarr['message'] = $message;
				$setsqlarr['pageorder'] = $firstmessage;
				if($firstmessage == 1){
					inserttable('spacenews', $setsqlarr);
				} else {
					$insertarr[] = $setsqlarr;
				}
			}
			if(!empty($insertarr)) {
				$insertkeys = array_keys($insertarr[0]);
				$insertarr_str = array();
				foreach($insertarr as $_index=>$_value) {
					$insertarr_str[] = "('".implode("','",$_value)."')";
				}
				$_SGLOBAL['db']->query("INSERT INTO ".tname('spacenews')." (`".implode('`,`',$insertkeys)."`) VALUES ".implode(',',$insertarr_str));
			}
			ssetcookie('newsauthor', $setsqlarr['newsauthor'], 86400);
			ssetcookie('newsfrom', $setsqlarr['newsfrom'], 86400);
		} else {
			//更新内容
			$insertarr = array();
			$firstmessage = $page;
			$arraymessage = explode('###NextPage###', $setsqlarr['message']);
			foreach($arraymessage as $message) {
				$message = trim($message);
				$setsqlarr['message'] = $message;
				$setsqlarr['pageorder'] = $pageorder;
				if($firstmessage == $_SGET['page']) {
					updatetable('spacenews', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
				} else {
					$insertarr[] = $setsqlarr;
				}
				$firstmessage++;
				$pageorder++;
			}
			if(!empty($insertarr)) {

				$insertkeys = array_keys($insertarr[0]);
				$insertarrnow = count($insertarr);
				$_SGLOBAL['db']->query("UPDATE ".tname('spacenews')." SET pageorder = pageorder+".$insertarrnow." WHERE itemid = ".$itemid." AND pageorder >".$_SGET['page']);
				$insertarr_str = array();
				foreach($insertarr as $_index=>$_value) {
					$insertarr_str[] = "('".implode("','",$_value)."')";
				}
				$_SGLOBAL['db']->query("INSERT INTO ".tname('spacenews')." (`".implode('`,`',$insertkeys)."`) VALUES ".implode(',',$insertarr_str));

			}			
		}
		$showurlarr[] = array('action/viewnews/itemid/'.$itemid.'/php/1', $alang['spacenews_newspage']);
		$showurlarr[] = array('action/category/catid/'.$_POST['catid'].'/php/1', $alang['spacenews_typepage']);

	} else {
		//额外信息
		$_POST['newsauthor'] = shtmlspecialchars(trim($_POST['newsauthor']));
		$_POST['newsfrom'] = shtmlspecialchars(trim($_POST['newsfrom']));
		$_POST['newsurl'] = isset($_POST['newurl']) ? shtmlspecialchars(trim($_POST['newsurl'])) : '';
		$_POST['newsfromurl'] = shtmlspecialchars(trim($_POST['newsfromurl']));

		//其余分页处理
		$setsqlarr = array(
			'postip' => $_SGLOBAL['onlineip'],
			'itemid' => $itemid,
			'newsauthor' => $_POST['newsauthor'],
			'newsfrom' => $_POST['newsfrom'],
			'newsfromurl' => $_POST['newsfromurl'],
			'newsurl' => $_POST['newsurl'],
		);
		$insertarr = array();
		$arraymessage = explode('###NextPage###', scensor($_POST['message'], 1));
		$firstmessage = $page;
		foreach($arraymessage as $message) {
			$message = trim($message);
			$setsqlarr['message'] = $message;
			$setsqlarr['pageorder'] = $pageorder;
			if($firstmessage == $_SGET['page']) {
				updatetable('spacenews', $setsqlarr, array('nid'=>$_POST['nid'], 'itemid'=>$itemid));
			} else {
				$insertarr[] = $setsqlarr;
			}
			$firstmessage++;
			$pageorder++;
		}
		if(!empty($insertarr)) {
			$insertkeys = array_keys($insertarr[0]);
			$insertarrnow = count($insertarr);
			$_SGLOBAL['db']->query("UPDATE ".tname('spacenews')." SET pageorder = pageorder+".$insertarrnow." WHERE itemid = ".$itemid." AND pageorder >".$_SGET['page']);
			$insertarr_str = array();
			foreach($insertarr as $_index=>$_value) {
				$insertarr_str[] = "('".implode("','",$_value)."')";
			}
			$_SGLOBAL['db']->query("INSERT INTO ".tname('spacenews')." (`".implode('`,`',$insertkeys)."`) VALUES ".implode(',',$insertarr_str));

		}

		$showurlarr[] = array('action/viewnews/itemid/'.$itemid.'/php/1', $alang['spacenews_newspage']);
		$showurlarr[] = array('action/category/catid/'.$_POST['catid'].'/php/1', $alang['spacenews_typepage']);
	}
}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {
	if(empty($showurlarr)) {
		//CATEGORY
		$catarr = getcategory($type);

		//LIST
		$rtarr = array();
		
		//LIST VIEW
		if(!empty($_SGET['searchid'])) {
			$wheresqlstr = ' itemid = \''.$_SGET['searchid'].'\'';
		} else {
			$wheresqlarr = array();
			$wheresqlarr['type'] = $type;
			if($isuid) $wheresqlarr['uid'] = $_SGLOBAL['supe_uid'];
			
			if(!empty($_SGET['catid'])) {
				$wheresqlarr['catid'] = $_SGET['catid'];
			}
			if(!empty($_SGET['itemtypeid'])) {
				$wheresqlarr['itemtypeid'] = $_SGET['itemtypeid'];
			}
			if(!empty($_SGET['folder'])) {
				$wheresqlarr['folder'] = $_SGET['folder'];
			}
			if(!empty($_SGET['digest'])) {
				$wheresqlarr['digest'] = $_SGET['digest'];
			}
			if(!empty($_SGET['subtype'])) {
				$wheresqlarr['subtype'] = $_SGET['subtype'];
			}
		
			$wheresqlstr = getwheresql($wheresqlarr);
			if(!empty($_SGET['searchkey'])) {
				$wheresqlstr .= ' AND subject LIKE \'%'.$_SGET['searchkey'].'%\'';
			}
		}
	
		$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' WHERE '.$wheresqlstr);
		$listcount = $_SGLOBAL['db']->result($query, 0);
		$multipage = '';
		$listarr = array();
		$hasharr = array();
		if($listcount) {			
			//order
			if(empty($_SGET['order'])) {
				$order = 'top DESC, dateline DESC';
			} else {
				$order = $_SGET['order'].' '.$_SGET['sc'];
			}
			$start = ($_SGET['page']-1)*$perpage;
			
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE '.$wheresqlstr.' ORDER BY '.$order.' LIMIT '.$start.','.$perpage);
			while ($item = $_SGLOBAL['db']->fetch_array($query)) {
				$hasharr[] = $item['hash'];
				$listarr[] = $item;
			}
			$multipage = multi($listcount, $perpage, $_SGET['page'], $theurl.$urlplus);
		}
		
		$rtarr['listcount'] = $listcount;
		$rtarr['multipage'] = $multipage;
		$rtarr['listarr'] = $listarr;
		$rtarr['hasharr'] = $hasharr;
		$multipage = $rtarr['multipage'];
		$viewclass = ' class="active"';
	}
	
} elseif ($_GET['op'] == 'edit') {
	//GET ONE VALUE
	$multipage = $alang['spacenews_title_message_page_none'];

	if(empty($_GET['page'])) $_GET['page'] = 1;
	$itemid = intval($_GET['itemid']);
	$page = intval($_GET['page']);
	if($page < 1 || empty($_GET['nextpage'])) $page = 1;
	
	if($isuid) {
		$sqlplus = ' AND uid=\''.$_SGLOBAL['supe_uid'].'\'';
	} else {
		$sqlplus = '';
	}
	$pageorderarr = array();
	$makepageorder = 0;
	$query = $_SGLOBAL['db']->query('SELECT pageorder FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' ORDER BY pageorder ASC, nid ASC '); 

	while ($pageorder = $_SGLOBAL['db']->fetch_array($query)) {
			$pageorderarr[] = intval($pageorder['pageorder']);
	}
	$listcount = count($pageorderarr);
	if(md5(serialize($pageorderarr)) != md5(serialize(range(1,$listcount)))) {
		$pageorderarr = range(1,$listcount);
		$makepageorder = 1;
	}
	if($page > $listcount) $page = 1;
	if(!empty($_GET['last'])) $page = $listcount;
	if($page < 1) $page = 1;
	
	if($listcount > 1) $multipage = multi($listcount, 1, $page, $theurl.'&op=edit&nextpage=1&itemid='.$itemid);

	$query = $_SGLOBAL['db']->query('SELECT ii.*, i.* FROM '.tname('spacenews').' ii LEFT JOIN '.tname('spaceitems').' i ON i.itemid=ii.itemid WHERE ii.itemid=\''.$itemid.'\' ORDER BY ii.pageorder ASC, ii.nid ASC LIMIT '.($page-1).', 1');
	$thevalue = $_SGLOBAL['db']->fetch_array($query);
	
	if($page == 1) {
		$thevalue['allowmax'] = $allowmax;
		
		if(empty($thevalue['hash'])) $thevalue['hash'] = $hashstr;
		
		//TAG
		$tags = array();
		$query = $_SGLOBAL['db']->query('SELECT t.tagname FROM '.tname('spacetags').' st LEFT JOIN '.tname('tags').' t ON t.tagid=st.tagid WHERE st.itemid=\''.$itemid.'\'');
		while ($itemtag = $_SGLOBAL['db']->fetch_array($query)) {
			$tags[md5($itemtag['tagname'])] = $itemtag['tagname'];
		}
		$thevalue['tagname'] = implode(' ', $tags);

		//UPLOAD
		$thevalue['uploadarr'] = array();
		if(!empty($thevalue['haveattach'])) {
			$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('attachments').' WHERE itemid=\''.$itemid.'\' ORDER BY dateline');
			while ($attach = $_SGLOBAL['db']->fetch_array($query)) {
				$thevalue['uploadarr'][] = $attach;
			}
			if(empty($thevalue['uploadarr'])) {
				$setsqlarr = array('haveattach' => 0);
				$wheresqlarr = array('itemid' => $itemid);
				updatetable('spaceitems', $setsqlarr, $wheresqlarr);
			}
		}
	}
	
} elseif ($_GET['op'] == 'add') {
	
	if(!checkperm('managespacenews')) {
		showmessage('spacenews_no_popedom_add');
	}

	//ONE ADD
	$thevalue = array(
		'itemid' => 0,
		'itemtypeid' => 0,
		'catid' => $_SGET['catid'],
		'type' => $type,
		'subject' => '',
		'dateline' => $_SGLOBAL['timestamp'],
		'digest' => '0',
		'top' => '0',
		'allowreply' => '1',
		'hash' => $hashstr,
		'folder' => '1',
		'message' => '',
		'tagname' => '',
		'uploadarr' => array(),
		'allowmax' => $allowmax,
		'customfieldid' => 0,
		'customfieldtext' => '',
		'haveattach' => 0,
		'replynum' => 0,
		'tid' => 0,
		'grade' => 0,
		'picid' => 0,
		'hottagarr' => array(),
		'lasttagarr' => array()
	);
	
	$thevalue['newsurl'] = '';
	$thevalue['nid'] = 0;
	$thevalue['newsfromurl'] = '';
	
	$page =1;
	$listcount = 1;
	$multipage = '';

	//论坛导入
	$tid = 0;
	if(!empty($_GET['tid'])) $tid = intval($_GET['tid']);
	if(!empty($tid) && discuz_exists()) {
		if($_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query('SELECT itemid FROM '.tname('spaceitems').' WHERE tid=\''.$tid.'\' AND type=\''.$type.'\' LIMIT 1'))) {
			showmessage('bbsimport_imported');
		}
		include_once(S_ROOT.'./include/bbsimport.inc.php');
	}

	$addclass = ' class="active"';
} elseif ($_GET['op'] == 'view') {
	
	if(empty($_GET['page'])) $_GET['page'] = 1;
	$itemid = intval($_GET['itemid']);
	$page = intval($_GET['page']);
	if($page < 1 || empty($_GET['nextpage'])) $page = 1;

	$pageorderarr = array();
	$makepageorder = 0;
	$query = $_SGLOBAL['db']->query('SELECT pageorder FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' ORDER BY pageorder ASC, nid ASC '); 
	while ($pageorder = $_SGLOBAL['db']->fetch_array($query)) {
			$pageorderarr[] = intval($pageorder['pageorder']);
	}
	$listcount = count($pageorderarr);
	
	if(md5(serialize($pageorderarr)) != md5(serialize(range(1,$listcount)))) {
		$pageorderarr = range(1,$listcount);
		$makepageorder = 1;
	}
	if($page > $listcount) $page = 1;
	if(!empty($_GET['last'])) $page = $listcount;
	if($page < 1) $page = 1;
	if($listcount > 1) $multipage = multi($listcount, 1, $page, $theurl.'&op=view&nextpage=1&itemid='.$itemid);

	$query = $_SGLOBAL['db']->query('SELECT ii.*, i.* FROM '.tname('spacenews').' ii LEFT JOIN '.tname('spaceitems').' i ON i.itemid=ii.itemid WHERE ii.itemid=\''.$itemid.'\' ORDER BY ii.pageorder ASC, ii.nid ASC LIMIT '.($page-1).', 10');
	$news = $_SGLOBAL['db']->fetch_array($query);
	$news['message'] = shtmlspecialchars($news['message']);
	$news['message'] = preg_replace("/&lt;(\/)*(p|br)(?:\s*)(\/)*&gt;/", '<\1\2>', $news['message']);
	if(empty($news)) {
		showmessage('prefield_none_exists');
	}
	
	$news['custom'] = array('name'=>'', 'key'=>array(), 'value'=>array());
	if($page == 1 && !empty($news['customfieldid'])) {
		$news['custom']['value'] = unserialize($news['customfieldtext']);
		if(!empty($news['custom']['value'])) {
			foreach ($news['custom']['value'] as $key => $value) {
				if(is_array($value)) {
					$news['custom']['value'][$key] = implode(', ', $value);
				}
			}
		}
		$query = $_SGLOBAL['db']->query('SELECT name, customfieldtext FROM '.tname('customfields').' WHERE customfieldid=\''.$news['customfieldid'].'\'');
		$fields= $_SGLOBAL['db']->fetch_array($query);
		$news['custom']['name'] = $fields['name'];
		$news['custom']['key'] = unserialize($fields['customfieldtext']);
	}

	$categories = getcategory($type);
	$viewhtml = '';
	$viewhtml .= '<table cellspacing="0" cellpadding="0" width="100%"  class="maintable">';
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['spacenews_title_subject'].'</th>';
	$viewhtml .= '<td>'.shtmlspecialchars($news['subject']).'</td>';
	$viewhtml .= '</tr>'."\n";
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['check_catname'].'</th>';
	$viewhtml .= '<td>'.$categories[$news['catid']]['name'].'</td>';
	$viewhtml .= '</tr>'."\n";
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['check_dateline'].'</th>';
	$viewhtml .= '<td>'.sgmdate($news['dateline']).'</td>';
	$viewhtml .= '</tr>'."\n";
	if($multipage) {
		$viewhtml .= '<tr>'."\n";
		$viewhtml .= '<th>'.$alang['spacenews_title_message_page'].'</th>';
		$viewhtml .= '<td>'.$multipage.'</td>';
		$viewhtml .= '</tr>'."\n";
	}
	if(empty($news['custom']['key'])) {
		foreach ($news['custom']['key'] as $key=>$value) {
			$viewhtml .= '<tr>'."\n";
			$viewhtml .= '<th>'.$value['name'].'('.$news['custom']['name'].')</th>';
			$viewhtml .= '<td>'.shtmlspecialchars($news['custom']['value'][$key]).'</td>';
			$viewhtml .= '</tr>'."\n";
		}
	}
	
	$viewhtml .= '<tr>'."\n";
	$viewhtml .= '<th>'.$alang['spacenews_title_message'].'</th>';
	$viewhtml .= '<td>'.$news['message'].'</td>';
	$viewhtml .= '</tr>'."\n";
	$viewhtml .= '</table>';
	$viewhtml .= label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$theurl, 'other'=>' onSubmit="return listsubmitconfirm(this)"'));
	$viewhtml .= '<input value="delete" name="operation" type="hidden" /><input value="0" name="opdelete" type="hidden" /><input name="listvaluesubmit" type="hidden" value="yes" /><input type="hidden" value="'.$itemid.'" name="item[]"/>';
	$viewhtml .= '<div class="buttons">';
	$viewhtml .= '<img src="'.S_URL.'/images/base/icon_edit.gif" align="absmiddle"> <a href="'.$theurl.'&op=edit&itemid='.$itemid.'">'.$alang['space_edit'].'</a>&nbsp;';
	$viewhtml .= label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['completely_erased']));
	$viewhtml .= '</div>'."\n";

	$viewhtml .= label(array('type'=>'form-end'));

} elseif ($_GET['op'] == 'addpage') {
	//ONE DELETE
	$itemid = intval($_GET['itemid']);
	$setsqlarr = array('itemid' => $itemid);
	if(!empty($itemid)) {
		$query = $_SGLOBAL['db']->query("SELECT max(pageorder) as bid FROM ".tname('spacenews')." WHERE itemid =".$itemid);
		$setsqlarr['pageorder'] = $_SGLOBAL['db']->result($query, 0) + 1;
		inserttable('spacenews', $setsqlarr);
		header('Location: '.$theurl.'&op=edit&itemid='.$itemid.'&last=1');
	} else {
		showmessage('spacenews_page_need_submit');
	}

} elseif ($_GET['op'] == 'deletepage') {
	$itemid = intval($_GET['itemid']);
	$nid = intval($_GET['nid']);
	$pageorder = intval($_GET['pageorder']);
	if(!empty($itemid) && !empty($nid)) {
		$_SGLOBAL['db']->query('UPDATE '.tname('spacenews').' SET pageorder = pageorder-1 WHERE itemid=\''.$itemid.'\' AND pageorder >\''.$pageorder.'\'');
		$_SGLOBAL['db']->query('DELETE FROM '.tname('spacenews').' WHERE itemid=\''.$itemid.'\' AND nid=\''.$nid.'\'');
	}
	header('Location: '.$theurl.'&op=edit&itemid='.$itemid.'&last=1');
	
} elseif ($_GET['op'] == 'deleteallwaste') {
	
	$catarr = getcategory($type);
	$_GET['delnum']= empty($_GET['delnum'])?0:intval($_GET['delnum']);
	$dellistarr = $wheresqlarr = array();
	$wheresqlarr['type'] = $type;
	if($isuid) $wheresqlarr['uid'] = $_SGLOBAL['supe_uid'];

	$wheresqlarr['folder'] = 3;
	
	$wheresqlstr = getwheresql($wheresqlarr);

	if(empty($_GET['all'])) {
		$all = $_SGLOBAL['db']->result($_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('spaceitems').' WHERE '.$wheresqlstr), 0);
	} else {
		$all = intval($_GET['all']);
	}
	$allitemids = empty($_GET['all'])?$rtarr['listcount']:intval($_GET['all']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spaceitems').' WHERE '.$wheresqlstr.' ORDER BY dateline DESC LIMIT 0,'.$perpage);
	while ($item = $_SGLOBAL['db']->fetch_array($query)) {
		$dellistarr[] = $item['itemid'];
	}
	$dels = count($dellistarr);

	$delnum = $_GET['delnum']+$dels;
}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['spacenews_title'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['spacenews_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['spacenews_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//view
if($viewhtml) {
	echo $viewhtml;
}

//FILTER SHOW
if (!empty($catarr)) {

	$isuid = 0;

	$digestarr = array(
		'' => $alang['space_all_digest'],
		'1' => $alang['space_digest_1'],
		'2' => $alang['space_digest_2'],
		'3' => $alang['space_digest_3']
	);
	$orderarr = array(
		'' => $alang['space_order_default'],
		'dateline' => $alang['space_order_dateline'],
		'lastpost' => $alang['space_order_lastpost'],
		'viewnum' => $alang['space_order_viewnum'],
		'replynum' => $alang['space_order_replynum'],
		'goodrate' => $alang['space_order_goodrate'],
		'badrate' => $alang['space_order_badrate']
	);
	$scarr = array(
		'ASC' => $alang['space_sc_asc'],
		'DESC' => $alang['space_sc_desc']
	);
	
	$catselectstr = '<select name="catid">';
	$catselectstr .= '<option value="">'.$alang['space_all_catid'].'</option>';
	foreach ($catarr as $key => $value) {
		$checkstr = postget('catid') == $value['catid']?' selected':'';
		$catselectstr .= '<option value="'.$value['catid'].'"'.$checkstr.'>'.$value['pre'].$value['name'].'</option>';
	}
	$catselectstr .= '</select>';

	$itemtypeselectstr = '';

	$digestselectstr = getselectstr('digest', $digestarr);
	$orderselectstr = getselectstr('order', $orderarr);
	$scselectstr = getselectstr('sc', $scarr);
	
	$active1 = $active2 = $active3 = '';
	if($_SGET['folder'] == 1) {
		$active1 = ' class="active"';
	} elseif ($_SGET['folder'] == 2) {
		$active2 = ' class="active"';
	} elseif ($_SGET['folder'] == 3) {
		$active3 = ' class="active"';
	}
	
	//资讯
	$alang['space_folder_2'] = $alang['space_folder_4'];

	$htmlstr = '';
	if(checkperm('needcheck')) {
	$htmlstr .= '<table cellspacing="2" cellpadding="2" class="helptable"><tr><td><ul>
			<li>'.$alang['spacenews_needcheck'].'</li>
			</ul></td></tr></table>';
	}
	
	$folderselectstr = '<div id="newslisttab">
	<ul>
	<li'.$active1.'><a href="'.$theurl.'&folder=1"><img src="admin/images/icon_folder.gif" align="absmiddle"> '.$alang['space_folder_1'].'</a></li>
	<li'.$active2.'><a href="'.$theurl.'&folder=2"><img src="admin/images/icon_folder2.gif" align="absmiddle"> '.$alang['space_folder_2'].'</a></li>
	<li'.$active3.'><a href="'.$theurl.'&folder=3"><img src="admin/images/icon_folder3.gif" align="absmiddle"> '.$alang['space_folder_3'].'</a></li></ul>
	</div>';
	
	$htmlstr .= $folderselectstr;
	$htmlstr .= label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
	$htmlstr .= label(array('type'=>'table-start', 'class'=>'toptable'));
	$htmlstr .= '<tr><td>';
	$htmlstr .= 'itemid:</label> <input type="text" name="searchid" id="searchid" value="'.$_SGET['searchid'].'" size="5" /> ';
	$htmlstr .= $lang['subject'].':</label> <input type="text" name="searchkey" id="searchkey" value="'.$_SGET['searchkey'].'" size="10" /> ';

	$htmlstr .= $alang['space_select_filter'].': '.$catselectstr.' '.$itemtypeselectstr.' '.$digestselectstr.' '.$alang['space_order_filter'].': '.$orderselectstr.' '.$scselectstr;
	$htmlstr .= ' '.$alang['space_perpage_loop_1'].':</label> <input type="text" name="perpage" id="perpage" value="'.$perpage.'" size="2" />'.$alang['space_perpage_loop_2'];
	$htmlstr .= ' <input type="submit" name="filtersubmit" value="GO">';
	
	$htmlstr .= '</td></tr>';
	$htmlstr .= label(array('type'=>'table-end'));
	$htmlstr .= label(array('type'=>'form-end'));
	echo $htmlstr;
}

//LIST SHOW
if($listarr) {

	if(checkperm('managecheck')) {
		$alang['space_move_folder'] = $alang['admin_func_move_folder'];

		$adminmenuarr = array(
			'noop' => $alang['space_no_op'],
			'movefolder' => $alang['space_move_folder']
		);
	
		if($_SGET['folder'] != 3) $adminmenuarr['check'] = $alang['grades_audit'];
	}
	
	if($_SGET['folder'] == 3 && !checkperm('allowundelete')) {
		unset($adminmenuarr['movefolder']);
	}
	if($_SGET['folder'] != 3 && checkperm('managecheck')) {
		$adminmenuarr['movecat'] = $alang['space_move_cat'];
		if(checkperm('managecheck')) {
			$adminmenuarr['digest'] = $alang['space_digest'];
			$adminmenuarr['top'] = $alang['space_top'];
			$adminmenuarr['allowreply'] = $alang['space_allowreply'];
		}
	}
	$adminmenuarr['delete'] = $alang['space_delete'];

	$adminmenu = $alang['space_batch_op'].'</th><th>';
	$adminmenu .= '<input type="checkbox" name="chkall" onclick="checkall(this.form, \'item\')">'.$alang['space_select_all'];
	foreach ($adminmenuarr as $key => $value) {
		if($key == 'noop') {
			$acheck = ' checked';
		} else {
			$acheck = '';
		}
		$adminmenu .= '<input type="radio" name="operation" value="'.$key.'" onClick="jsop(this.value)"'.$acheck.'> '.$value;
	}
	$admintbl['noop'] = '<tr id="divnoop" style="display:none"><td></td><td></td></tr>';
	$admintbl['movecat'] = label(array('type'=>'select-div', 'alang'=>'space_op_category', 'name'=>'opcatid', 'id'=>"divmovecat", 'radio'=>1, 'options'=>$catarr, 'display'=>'none'));
	$movefolderarr = array(
		'1' => $alang['space_folder_1'],
		'2' => $alang['space_folder_2']
	);
	$admintbl['movefolder'] = label(array('type'=>'radio', 'alang'=>'space_op_folder', 'name'=>'opfolder', 'id'=>"divmovefolder", 'options'=>$movefolderarr, 'value'=>'1', 'display'=>'none'));

	if(checkperm('managecheck')) {
		global $gradearr;
		$checkarr = $gradearr;
		$admintbl['check'] = label(array('type'=>'radio', 'alang'=>'examination_grades', 'name'=>'opcheck', 'id'=>"divcheck", 'options'=>$checkarr, 'value'=>'0', 'display'=>'none'));
	
		$spacegoodsdigestarr = array(
			'0' => $alang['space_digest_0'],
			'1' => $alang['space_digest_1'],
			'2' => $alang['space_digest_2'],
			'3' => $alang['space_digest_3']
		);
		$admintbl['digest'] = label(array('type'=>'radio', 'alang'=>'space_digest', 'name'=>'opdigest', 'id'=>"divdigest", 'options'=>$spacegoodsdigestarr, 'value'=>'0', 'display'=>'none'));
		$spacegoodstoparr = array(
			'0' => $alang['space_top_0'],
			'1' => $alang['space_top_1'],
			'2' => $alang['space_top_2'],
			'3' => $alang['space_top_3']
		);	
		$admintbl['top'] = label(array('type'=>'radio', 'alang'=>'space_top', 'name'=>'optop', 'id'=>"divtop", 'options'=>$spacegoodstoparr, 'value'=>'0', 'display'=>'none'));
		$spacegoodsallowreplyarr = array(
			'1' => $alang['space_allowreply_1'],
			'0' => $alang['space_allowreply_0']
		);	
		$admintbl['allowreply'] = label(array('type'=>'radio', 'alang'=>'space_allowreply', 'name'=>'opallowreply', 'id'=>"divallowreply", 'options'=>$spacegoodsallowreplyarr, 'value'=>'1', 'display'=>'none'));
	}
	$spacegoodsdeletearr = array(
		'0' => $alang['space_delete_0'],
		'1' => $alang['space_delete_1']
	);
	if($_SGET['folder'] == 3) unset($spacegoodsdeletearr[1]);
	$admintbl['delete'] = label(array('type'=>'radio', 'alang'=>'space_delete', 'name'=>'opdelete', 'id'=>"divdelete", 'options'=>$spacegoodsdeletearr, 'value'=>'0', 'display'=>'none'));
	
	$htmlarr = array();
	$htmlarr['js'] = '
	<script language="javascript">
	<!--
	function jsop(radionvalue) {'."\n";
	foreach ($adminmenuarr as $adminkey => $adminvalue) {
		$htmlarr['js'] .= 'document.getElementById(\'div'.$adminkey.'\').style.display = "none";'."\n";
	}
	$htmlarr['js'] .= '
	if(radionvalue == \'noop\') {
	} else {
		document.getElementById(\'div\'+radionvalue).style.display = "";
	}
	}
	//-->
	</script>
	';
	
	$htmlarr['html'] = '<tr><th width="12%">'.$adminmenu.'</th></tr>';
	foreach ($adminmenuarr as $adminkey => $adminvalue) {
		$htmlarr['html'] .= $admintbl[$adminkey];
	}
	$adminhtmlarr = $htmlarr;
	
	echo $adminhtmlarr['js'];
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl, 'other'=>' onSubmit="return listsubmitconfirm(this)"'));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th width="30">'.$alang['space_select'].'</th>';
	echo '<th width="50">itemid</th>';
	echo '<th>'.$alang['spaceblog_subject'].'</th>';
	echo '<th width="100">'.$alang['spaceblog_title_catid'].'</th>';
	echo '<th width="80">'.$alang['spacenews_title_author'].'</th>';
	echo '<th width="140">'.$alang['space_dateline'].'</th>';
	echo '<th width="70">'.$alang['audit_level'].'</th>';
	echo '<th width="60">'.$alang['space_op'].'</th>';
	echo '</tr>';
	foreach ($listarr as $listvalue) {
		empty($class) ? $class=' class="darkrow"': $class='';

		$listvalue['dateline'] = sgmdate($listvalue['dateline']);
		$listvalue['lastpost'] = sgmdate($listvalue['lastpost']);
		$listvalue['grade'] = $gradearr[$listvalue['grade']];
		
		$subjectpre = getsubjectpre($listvalue);
		
		echo '<tr'.$class.'>';
		echo '<td><input name="item[]" type="checkbox" value="'.$listvalue['itemid'].'" /></td>';
		echo '<td>'.$listvalue['itemid'].'</td>';
		if($_SGET['folder'] != 1) {
			echo '<td>'.$subjectpre.'<a href="'.$theurl.'&op=view&itemid='.$listvalue['itemid'].'" target="_blank">'.$listvalue['subject'].'</a></td>';			
		} else {
			echo '<td>'.$subjectpre.'<a href="'.geturl('viewnews/'.$listvalue['itemid']).'" target="_blank">'.$listvalue['subject'].'</a></td>';
		}
		
		echo '<td align="center"><a href="'.$theurl.'&catid='.$listvalue['catid'].'">'.(empty($catarr[$listvalue['catid']]['name'])?'':$catarr[$listvalue['catid']]['name']).'</a></td>';
		echo '<td align="center"><a href="'.S_URL.'/space.php?uid='.$listvalue['uid'].'" target="_blank">'.$listvalue['username'].'</a></td>';
		echo '<td>'.$listvalue['dateline'].'</td><td>'.$listvalue['grade'].'</td><td align="center">';
		if($_SGET['folder'] == 3) {
			echo '-';
		} else {
			echo '<img src="'.S_URL.'/images/base/icon_edit.gif" align="absmiddle"> <a href="'.$theurl.'&op=edit&itemid='.$listvalue['itemid'].'">'.$alang['space_edit'].'</a>';
		}
		echo '</td></tr>';
	}
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo $adminhtmlarr['html'];
	echo label(array('type'=>'table-end'));
	
	if(!empty($multipage)) {
		echo label(array('type'=>'table-start', 'class'=>'listpage'));
		echo '<tr><td>'.$multipage.'</td></tr>';
		echo label(array('type'=>'table-end'));
	}
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value'=>$alang['common_reset']));
	if($_SGET['folder'] == 3) {
		echo '<button value="delete" type="button" class="warningbtn" name="batchdeletesubmit" onclick="if(confirm(\''.$alang[delete_all_note].'\')){window.location.href=\''.$newurl.'&op=deleteallwaste\';}">'.$alang['spaceimage_delete_images'].'</button>';
	}
	echo '</div>';
	echo '<input name="listvaluesubmit" type="hidden" value="yes" />';
	echo '<input name="oldfolder" type="hidden" value="'.$_SGET['folder'].'" />';
	echo label(array('type'=>'form-end'));
} elseif(empty($_GET['op']) && !submitcheck('listvaluesubmit') && !submitcheck('valuesubmit')) {
	echo '
	<table cellspacing="0" cellpadding="0" width="100%"  class="listtable">
	<tr><td align="center">'.$alang['spacenews_info_null'].'</td></tr></table>';
}

//清空垃圾箱信息
if(is_array($dellistarr) && !empty($dellistarr)) {

	$itemidstr = simplode($dellistarr);
	deleteitems('itemid', $itemidstr);
	$residual = $all-$delnum;
	echo label(array('type'=>'table-start', 'class'=>'listtable'));
	echo '<tr>';
	echo '<th>'.$alang['delete_all_message_0'].$all.$alang['delete_all_message_1'].$residual.$alang['delete_all_message_3'].'</th>';
	echo '</tr>';
	echo label(array('type'=>'table-end'));
	if($residual) {
		jumpurl($newurl.'&op=deleteallwaste&all='.$all.'&delnum='.$delnum, 1000, 'meta');
	} else {
		jumpurl($newurl, 1000, 'meta');
	}
}

//完成后的url
if(is_array($showurlarr) && $showurlarr) {
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	foreach ($showurlarr as $url) {
		$turl = geturl($url[0]);
		echo '<tr><td><a href="'.$turl.'" target="_blank"><strong>'.$url[1].'</strong> '.$alang['spaceblog_viewpage_success'].'</a></td></tr>';
	}
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '
	<div class="buttons">
	<input type="button" name="continuesubmit4" value="'.$alang['spacenews_op_add'].'" onclick="window.location.href=\''.$theurl.'&op=add\'"> 
	<input type="button" name="continuesubmit3" value="'.$alang['spacenews_title_message_op_add'].'" onclick="window.location.href=\''.$theurl.'&op=addpage&itemid='.$itemid.'\'"> 
	<input type="button" name="continuesubmit2" value="'.$alang['continue_the_current_editorial_page'].'" onclick="window.location.href=\''.$theurl.'&op=edit&itemid='.$itemid.'&page='.$page.'\'"> 
	<input type="button" name="continuesubmit1" value="'.$alang['common_continue_list_edit'].'" onclick="window.location.href=\''.$theurl.'\'"> 
	</div>';
}

//THE VALUE SHOW
if($thevalue) {
	
	//缩略图
	//CUSTOM FIELD
	if($page == 1) {
		$isuid = 0;		
		
		$cfhtmlselect = array('0'=>$alang['space_customfield_none']);
		
		$wheresqlarr = array();
		$wheresqlarr['type'] = $type;
		$plussql = 'ORDER BY displayorder';
		$allcfarr = selecttable('customfields', array(), $wheresqlarr, $plussql);
		
		$cfhtml = '';
		$tbodynum = 0;
		foreach ($allcfarr as $cfkey => $cfvalue) {
			if(empty($thevalue['customfieldid'])) {
				if($cfvalue['isdefault']) {
					$thevalue['customfieldid'] = $cfvalue['customfieldid'];
				}
			}
			$cfhtmlselect[$cfvalue['customfieldid']] = $cfvalue['name'];
			$cfarr = unserialize($cfvalue['customfieldtext']);
			if(is_array($cfarr) && $cfarr) {
				if(!empty($thevalue['customfieldid']) && $thevalue['customfieldid'] == $cfvalue['customfieldid']) {
					$tbodydisplay = '';
					if(empty($thevalue['customfieldtext'])) {
						$thecfarr = array();
					} else {
						$thecfarr = unserialize($thevalue['customfieldtext']);
					}
				} else {
					$tbodydisplay = 'none';
					$thecfarr = array();
				}
				$tbodynum++;
				$cfhtml .= '<tbody id="cf_'.$tbodynum.'" style="display:'.$tbodydisplay.'">';
				
				foreach ($cfarr as $ckey => $cvalue) {
					$inputstr = '';
					if(empty($thecfarr[$ckey])) $thecfarr[$ckey] = '';
					$cfoptionarr = array();
					if($cvalue['type'] == 'select' || $cvalue['type'] == 'checkbox') {
						$cfoptionstr = $cvalue['option'];
						$coarr = explode("\n", $cfoptionstr);
						$coarr = sarray_unique($coarr);
						foreach ($coarr as $covalue) {
							$covalue = trim($covalue);
							$cfoptionarr[$covalue] = $covalue;
						}
					}
					switch ($cvalue['type']) {
						case 'input':
							$inputstr = '<input name="customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']" type="text" size="30" value="'.$thecfarr[$ckey].'" />';
							break;
						case 'textarea':
							$inputstr = '<textarea name="customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']" rows="5" cols="60">'.$thecfarr[$ckey].'</textarea>';
							break;
						case 'select':
							$inputstr = getselectstr('customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']', $cfoptionarr, $thecfarr[$ckey]);
							break;
						case 'checkbox':
							$inputstr = getcheckboxstr('customfieldtext['.$cfvalue['customfieldid'].']['.$ckey.']', $cfoptionarr, $thecfarr[$ckey]);
							break;
					}
					$cfhtml .= '<tr><th>'.$cvalue['name'].'</th><td>'.$inputstr.'</td></tr>';
				}
				$cfhtml .= '</tbody>';
			}
		}

		//处理语言包
		$alang['space_title_customfield'] = str_replace('spacecp.php', 'admincp.php', $alang['space_title_customfield']);
		eval("\$alang['space_title_customfield'] = \"".$alang['space_title_customfield']."\";");
		$cfhtml = '<tr><th>'.$alang['space_title_customfield'].'</th><td>'.getselectstr('customfieldid', $cfhtmlselect, $thevalue['customfieldid'], 'onchange="showdivcustomfieldtext()"').'</td></tr>'."\n".$cfhtml;

		$jscftext = '
		<script language="javascript">
		<!--
		function showdivcustomfieldtext() {
			var cfindex = document.getElementById("customfieldid").selectedIndex;
			showtbody(cfindex);
		}	
		function showtbody(id) {
			for(i=1;i<='.$tbodynum.';i++){
				obj=document.getElementById("cf_"+i);
				if(i == id) {
					obj.style.display="";
				} else {
					obj.style.display="none";
				}
			}
		}
		function textCounter(obj, showid, maxlimit) {
			var len = strLen(obj.value);
			var showobj = document.getElementById(showid);
			if(len < maxlimit) {
				showobj.innerHTML = maxlimit - len;
			} else {
				obj.value = getStrbylen(obj.value, maxlimit);
				showobj.innerHTML = "0";
			}
		}
		function strLen(str) {
			var charset = is_ie ? document.charset : document.characterSet;
			var len = 0;
			for(var i = 0; i < str.length; i++) {
				len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset.toLowerCase() == "utf-8" ? 3 : 2) : 1;
			}
			return len;
		}
		//-->
		</script>
		<script src="'.S_URL.'/include/js/selectdate.js"></script>';
		
		$cfarr = array('js'=>$jscftext, 'html'=>$cfhtml);
	
		$cfjs = $cfarr['js'];
		$cfhtml = $cfarr['html'];
	
		//CATEGORIES
		$clistarr = getcategory($type);
		$categorylistarr = array('0'=>array('pre'=>'', 'name'=>'------'));
		foreach ($clistarr as $key => $value) {
			$categorylistarr[$key] = $value;
		}
		
		//MORE SETTING
		$alang['space_folder_2'] = $alang['space_folder_4'];
		$digestarr = array(
			'0' => $alang['space_digest_0'],
			'1' => $alang['space_digest_1'],
			'2' => $alang['space_digest_2'],
			'3' => $alang['space_digest_3']
		);
		$toparr = array(
			'0' => $alang['space_top_0'],
			'1' => $alang['space_top_1'],
			'2' => $alang['space_top_2'],
			'3' => $alang['space_top_3']
		);
		$allowreplyarr = array(
			'1' => $alang['space_allowreply_1'],
			'0' => $alang['space_allowreply_0']
		);
		$foldertarr = array(
			'1' => $alang['space_folder_1'],
			'2' => $alang['space_folder_2']
		);
	
		$mshtml = '';
		if(!checkperm('needcheck')) $mshtml .= label(array('type'=>'radio', 'alang'=>'space_title_folder', 'name'=>'folder', 'options'=>$foldertarr, 'value'=>$thevalue['folder']));
		if(checkperm('managecheck')) {
			global $gradearr;
			$mshtml .= label(array('type'=>'radio', 'alang'=>'space_title_digest', 'name'=>'digest', 'options'=>$digestarr, 'value'=>$thevalue['digest']));
			$mshtml .= label(array('type'=>'radio', 'alang'=>'space_title_top', 'name'=>'top', 'options'=>$toparr, 'value'=>$thevalue['top']));
			$mshtml .= label(array('type'=>'radio', 'alang'=>'space_title_allowreply', 'name'=>'allowreply', 'options'=>$allowreplyarr, 'value'=>$thevalue['allowreply']));
			$mshtml .= label(array('type'=>'radio', 'alang'=>'examination_grades', 'name'=>'grade', 'options'=>$gradearr, 'value'=>$thevalue['grade']));
		}

		//JAVASCRIPT
		echo $cfjs;
	}

	//PRE FIELD
	$rarr = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('prefields').' WHERE type=\''.$type.'\'');
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$rarr[$value['field']][] = $value;
	}
	$prefieldarr = $rarr;

	//读取最后一次提交值
	if(!empty($_COOKIE[$_SC['cookiepre'].'newsauthor'])) {
		array_unshift($prefieldarr['newsauthor'], array('id' => 0, 'type' => 'news', 'field' => 'newsauthor', 'value' => $_COOKIE[$_SC['cookiepre'].'newsauthor'], 'isdefault' => 1));
	}
	if(!empty($_COOKIE[$_SC['cookiepre'].'newsfrom'])) {
		array_unshift($prefieldarr['newsfrom'], array('id' => 0, 'type' => 'news', 'field' => 'newsfrom', 'value' => $_COOKIE[$_SC['cookiepre'].'newsfrom'], 'isdefault' => 1));
	}

	//NEWS AUTHOR
	$newsauthorstr = prefieldhtml($thevalue, $prefieldarr, 'newsauthor', 1, '20');
	
	//NEWS FROM
	$newsfromstr = prefieldhtml($thevalue, $prefieldarr, 'newsfrom', 1, '20');

	if(!empty($thevalue['itemid'])) {
		$optext = '<a href="'.$theurl.'&op=addpage&itemid='.$thevalue['itemid'].'"><img src="admin/images/icon_folder.gif" align="absmiddle" border="0" /> '.$alang['spacenews_title_message_op_add'].'</a>';
	} else {
		$optext = $alang['spacenews_page_need_submit'];
	}
	if($listcount > 1) {
		$optext .= ' &nbsp;&nbsp; <a href="'.$theurl.'&op=deletepage&itemid='.$thevalue['itemid'].'&nid='.$thevalue['nid'].'&pageorder='.$thevalue['pageorder'].'"><img src="admin/images/icon_folder3.gif" align="absmiddle" border="0" /> '.$alang['spacenews_title_message_op_delete'].'</a>';
	}

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$newurl, 'other'=>' onSubmit="return validate(this)"'));
	
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	if(checkperm('needcheck')) {
		echo label(array('type'=>'text', 'alang'=>'common_note', 'text'=>$alang['spacenews_needcheck']));
	}
	if($page == 1) {
		if(empty($thevalue['styletitle'])) {
			$mktitlestyle = '';
		} else {
			$mktitlestyle = mktitlestyle($thevalue['styletitle']);
		}
		echo '<tr id="tr_subject"><th>'.$alang['spacenews_title_subject'].'</th><td><input name="subject" type="text" id="subject" onblur="relatekw();" size="60" maxlength="80" value="'.shtmlspecialchars($thevalue['subject']).'" style="width: 500px;'.$mktitlestyle.'"  onkeyup="textCounter(this, \'maxlimit\', 80);" /><br />'.$alang['spacenews_title_subject_note'].'</td></tr>';
		if(checkperm('managecheck')) {
			printjs();
			print <<<EOF
			<tr>
			<th>$alang[titlestyle]
			</th>
			<td>$alang[titlestylecolor]
				<select name="fontcolor" id="fontcolor" onChange="settitlestyle();" style="width: 80px;background-color: #000000">
	   			   <option value="" selected="selected">default</option>
				</select> 
				$alang[titlestylesize]
			     	<select name="fontsize" id="fontsize" onChange="settitlestyle();">
	   			   <option value="" selected="selected">default</option>
	   			   <option value="12px">12px</option>
	   			   <option value="13px">13px</option>
	   			   <option value="14px">14px</option>
	   			   <option value="16px">16px</option>
	   			   <option value="18px">18px</option>
	   			   <option value="24px">24px</option>
	   			   <option value="36px">36px</option>
	   			   <option value="48px">48px</option>
	   			   <option value="72px">72px</option>
	   			 </select>
			     <img src="admin/images/ti.gif" /><input type="checkbox" name="em" id="em" value="italic" onClick="settitlestyle();" />
			     <img src="admin/images/tb.gif" /><input type="checkbox" name="strong" id="strong" value="bold" onClick="settitlestyle();" />
			     <img src="admin/images/tu.gif" /><input type="checkbox" name="underline" id="underline" value="underline" onClick="settitlestyle();" />
	
			</td>
			</tr>
EOF;
		}

		$thevalue['dateline'] = sgmdate($thevalue['dateline']);
		print <<<EOF
		<tr>
		<th>$alang[spacenews_dateline]</th>
		<td>
		<input type="text" name="dateline" id="dateline" readonly="readonly" value="$thevalue[dateline]" /><img src="$siteurl/admin/images/time.gif" onClick="getDatePicker('dateline', event, 21)" />
		</td>
		</tr>
EOF;
		echo label(array('type'=>'input', 'alang'=>'spacenews_title_newsurl', 'name'=>'newsurl', 'size'=>60, 'value'=>$thevalue['newsurl']));
		echo label(array('type'=>'select-div', 'alang'=>'space_title_catid', 'name'=>'catid', 'radio'=>1, 'options'=>$categorylistarr, 'width'=>'30%', 'value'=>$thevalue['catid']));
	} else {
		echo label(array('type'=>'text', 'alang'=>'spacenews_title_subject', 'text'=>'<strong>'.$thevalue['subject'].'</strong>'));
	}

	echo label(array('type'=>'text', 'alang'=>'spacenews_title_message_op', 'text'=>$optext));
	if($listcount > 1) {
		$options =  array();
		foreach($pageorderarr as $key => $value) {
			if($key == 0)  {
				$options[$value] = $alang['spacenews_title_message_start'];
			} elseif($key == count($pageorderarr)-1) {
				$options[$value] = $alang['spacenews_title_message_end'];
			} else {
				$options[$value] = $alang['spacenews_title_message_no1'].$value.$alang['spacenews_title_message_no2'];
			}
		}
		echo label(array('type'=>'select', 'name'=>'pageorder', 'alang'=>'spacenews_title_message_send', 'options'=>$options, 'value'=>$page));
	}

	echo label(array('type'=>'text', 'alang'=>'spacenews_title_message_page', 'text'=>$multipage));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'table-start', 'class'=>'edittable'));
	echo label(array('type'=>'edit', 'alang'=>'spacenews_title_message', 'name'=>'message', 'value'=>$thevalue['message'], 'op'=>1));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	if($page == 1) {
		echo '<input name="subjectpic" id="subjectpic" type="hidden" value="'.$thevalue['picid'].'" />';
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
		echo label(array('type'=>'upload', 'alang'=>'spacenews_title_upload', 'name'=>'upload', 'hash'=>$thevalue['hash'], 'allowmax'=>$thevalue['allowmax'], 'allowtype'=>'', 'thumb'=>$_SCONFIG['thumbarray']['news'], 'values'=>$thevalue['uploadarr']));
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
	}
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	
	print <<<EOF
	<tr>
	<th>$alang[extract_content]</th>
	<td>
	<input type="text" name="referurl" id="referurl" size="60" value="" /><br />
	<select name="robotlevel">
	<option value="1">$alang[simple_extraction]</option>
	<option value="2" selected="selected">$alang[smart_extraction]</option>
	</select>
	<span id="scharset" name="scharset" style="display:none">
	<select name="charset" id="charset">
	<option value="">$alang[automatic_analysis_of_coding]</option>
	<option value="GBK">GBK</option>
	<option value="GB2312">GB2312</option>
	<option value="BIG5">BIG5</option>
	<option value="UTF-8">UTF-8</option>
	<option value="UNICODE">UNICODE</option>
	</select>
	</span>
	<input type="button"  value="$alang[extract_the_contents_of_the_current_location_url]" onclick="return robotReferUrl('getrobotmsg');" />
	<p class="textmsg" id="divshowrobotmsg" style="display:none"></p>
	<p class="textmsg succ" id="divshowrobotmsgok" style="display:none"></p>
	</td>
	</tr>
EOF;
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	if($page == 1) {
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
		echo '<tr id="tr_tagname">
			<th>'.$alang['space_title_tagname'].'</th>
			<td><input name="tagname" type="text" id="tagname" size="30" value="'.$thevalue['tagname'].'" /><input type="button"  value="'.$alang['get_ralatekw'].'" onclick="relatekw();return false;" /></td>
			</tr>';
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));

	} else {
		//发件箱
		echo '<input name="folder" type="hidden" value="'.$thevalue['folder'].'" />';
		echo '<input name="catid" type="hidden" value="'.$thevalue['catid'].'" />';
	}
	
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));

	echo label(array('type'=>'text', 'alang'=>'spacenews_title_newsauthor', 'text'=>$newsauthorstr));
	echo label(array('type'=>'text', 'alang'=>'spacenews_title_newsfrom', 'text'=>$newsfromstr));
	echo label(array('type'=>'input', 'alang'=>'spacenews_title_newsfromurl', 'name'=>'newsfromurl', 'width'=>'30%', 'size'=>'60', 'value'=>$thevalue['newsfromurl']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	if($page == 1) {

		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
		echo $cfhtml;
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
		
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'table-start'));
		echo $mshtml;
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
		if(checkperm('managecheck')) {
			echo '<script language="javascript" type="text/javascript">makeselectcolor(\'fontcolor\');loadtitlestyle();var theboj = document.getElementById(\'subject\');textCounter(theboj, \'maxlimit\', 80);</script>';
		}
	}
	
	if($_SCONFIG['allowfeed']) {
		$feedchecked = ($_SCONFIG['customaddfeed'] & 1) ? array(1=>'checked="checked"') : array(0=>'checked="checked"');
		echo label(array('type'=>'table-start'));
		echo <<<EOF
					<tr>
					<th>$lang[pushed_to_the_feed]</th>
					<td>
					<input type="radio" $feedchecked[1] value="1" name="addfeed"/>$lang[yes]
					<input type="radio" $feedchecked[0] value="0" name="addfeed"/>$lang[no]
					</td>
					</tr>
					</table>
EOF;
		echo label(array('type'=>'table-end'));
	}
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'other'=>' onclick="publish_article();"', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	if($makepageorder == 1) {
		echo '<input name="makepageorder" type="hidden" value="yes" />';
	}
	echo '<input name="itemid" type="hidden" value="'.$thevalue['itemid'].'" />';
	echo '<input name="nid" type="hidden" value="'.$thevalue['nid'].'" />';
	echo '<input name="page" type="hidden" value="'.$page.'" />';
	echo '<input name="listcount" type="hidden" value="'.$listcount.'" />';
	echo '<input name="hash" type="hidden" value="'.$thevalue['hash'].'" />';
	echo '<input name="tid" type="hidden" value="'.$thevalue['tid'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	
	echo label(array('type'=>'form-end'));
	
}


function getcheckboxstr($var, $optionarray, $value='', $other='') {
	$html = '<table><tr>';
	$i=0;
	foreach ($optionarray as $okey => $ovalue) {
		$html .= '<td style="border:0"><input name="'.$var.'[]" type="checkbox" value="'.$okey.'"'.$other.' />'.$ovalue.'</td>';
		if($i%5==4) $html .= '</tr><tr>';
		$i++;
	}
	$html .= '</tr></table>';

	$valuearr = array();
	if(!empty($value)) {
		if(is_array($value)) {
			$valuearr = $value;
		} else {
			$valuearr = explode(',', $value);
		}
	}

	if(!empty($valuearr)) {
		foreach ($valuearr as $ovalue) {
			$html = str_replace('value="'.$ovalue.'"', 'value="'.$ovalue.'" checked', $html);
		}
	}

	return $html;
}

function printjs() {
print <<<EOF
	<script type="text/javascript">
		function settitlestyle() {
			var objsubject=document.getElementById('subject');
			var objfontcolor=document.getElementById('fontcolor');
			var objfontsize=document.getElementById('fontsize');
			var objem=document.getElementById('em');
			var objstrong=document.getElementById('strong');
			var objunderline=document.getElementById('underline');
			objsubject.style.color = objfontcolor.value;
			objfontcolor.style.backgroundColor = objfontcolor.value;
			objsubject.style.fontSize = objfontsize.value;
			objsubject.style.width = 500;
			if(objem.checked == true) {
				objsubject.style.fontStyle = "italic";
			} else {
				objsubject.style.fontStyle = "";
			}
			if(objstrong.checked == true) {
				objsubject.style.fontWeight = "bold";
			} else {
				objsubject.style.fontWeight = "";
			}
			if(objunderline.checked == true) {
				objsubject.style.textDecoration = "underline";
			} else {
				objsubject.style.textDecoration = "none";
			}
		}
		function loadtitlestyle() {
			var objsubject=document.getElementById('subject');
			var objfontcolor=document.getElementById('fontcolor');
			var objfontsize=document.getElementById('fontsize');
			var objem=document.getElementById('em');
			var objstrong=document.getElementById('strong');
			var objunderline=document.getElementById('underline');
			objfontcolor.style.backgroundColor = objsubject.style.color;
			objfontcolor.value = objsubject.style.color;
			var colorstr = objsubject.style.color;
			if(isFirefox=navigator.userAgent.indexOf("Firefox")>0 && colorstr != ""){
				colorstr = rgbToHex(colorstr);
			}
			if(colorstr != "") {
				objfontcolor.options.selectedIndex = getbyid(colorstr).index;
				objfontcolor.options.selected = true;
			}
			objfontsize.value = objsubject.style.fontSize;
			if(objsubject.style.fontWeight == "bold") {
				objstrong.checked = true;
			} else {
				objstrong.checked = false;
			}
			if(objsubject.style.fontStyle == "italic") {
				objem.checked = true;
			} else {
				objem.checked = false;
			}
			if(objsubject.style.textDecoration == "underline") {
				objunderline.checked = true;
			} else {
				objunderline.checked = false;
			}		
		}
		function makeselectcolor(selectname){
			subcat = new Array('00','33','66','99','CC','FF');
			var length = subcat.length;
			var RED = subcat;
			var GREEN = subcat;
			var BLUE = subcat;
			var b,r,g;
			var objsubject=document.getElementById(selectname);
			for(r=0;r < length;r++){
				for(g=0;g < length;g++){
					for(b=0;b < length;b++){
						var oOption = document.createElement("option");
						oOption.style.backgroundColor="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.style.color="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.value="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.text="#"+RED[r]+GREEN[g]+BLUE[b];
						oOption.id="#"+RED[r]+GREEN[g]+BLUE[b];
						objsubject.appendChild(oOption);
						}
					}
				}
		}
		function rgbToHex(color) {
			color=color.replace("rgb(","")
			color=color.replace(")","")
			color=color.split(",")
			
			r=parseInt(color[0]);
			g=parseInt(color[1]);
			b=parseInt(color[2]);
			
			r = r.toString(16);
			if (r.length == 1) {
				r = '0' + r;
			}
			g = g.toString(16);
			if (g.length == 1) {
				g = '0' + g;
			}
			b = b.toString(16);
			if (b.length == 1) {
				b = '0' + b;
			}
			return ("#" + r + g + b).toUpperCase();
		}
			
	</script>
EOF;

}
function addurlhttp($m) {
	if (preg_grep("/^http\:/", array($m[2])) || preg_grep("/^\//", array($m[2]))) {
		return 'src="'.$m[2].'.'.$m[3];
	} else {
		return 'src="'.S_URL_ALL.'/'.$m[2].'.'.$m[3];
	}
		
}

//显示扩充信息选择列表
function prefieldhtml($thevalue, $prefieldarr, $var, $input=1, $size='20', $isarray=0) {
	global $alang;

	if($isarray) {
		$optionstr = '';
		foreach ($prefieldarr as $nakey => $navalue) {
			$optionstr .= '<option value="'.$nakey.'">'.$navalue.'</option>';
		}
	} else {
		if(empty($prefieldarr[$var])) {
			$vararr = array();
		} else {
			$vararr = $prefieldarr[$var];
		}
		$optionstr = '';
		foreach ($vararr as $navalue) {
			$optionstr .= '<option value="'.$navalue['value'].'">'.$navalue['value'].'</option>';
			if(empty($thevalue[$var]) && !empty($navalue['isdefault'])) {
				$thevalue[$var] = $navalue['value'];
			}
		}
	}
	$varstr = '';
	if($input) {
		if(empty($thevalue[$var])) $thevalue[$var] = '';
		$varstr .= '<input name="'.$var.'" type="text" id="'.$var.'" size="'.$size.'" value="'.$thevalue[$var].'" />';
		$varstr .= ' <select name="varop" onchange="changevalue(\''.$var.'\', this.value)">';
		$varstr .= '<option value="">'.$alang['prefield_option_'.$var].'</option>';
	} else {
		$varstr .= '<select name="'.$var.'">';
		if(!empty($optionstr)) {
			$optionstr = str_replace('value="'.$thevalue[$var].'"', 'value="'.$thevalue[$var].'" selected', $optionstr);
		}
	}

	$varstr .= $optionstr;
	$varstr .= '</select>';
	return $varstr;
}

//获取相关TAG
function postgetincludetags($message, $tagnamearr) {
	global $_SGLOBAL;
	
	$postincludetags = '';
	if(!file_exists(S_ROOT.'./data/system/tag.cache.php')) {
		include_once(S_ROOT.'./include/cron/tagcontent.php');
	}
	@include_once(S_ROOT.'./data/system/tag.cache.php');
	if(empty($_SGLOBAL['tagcontent'])) $_SGLOBAL['tagcontent'] = '';
	$tagtext = implode('|', $tagnamearr).'|'.$_SGLOBAL['tagcontent'];
	$postincludetags = getincluetags($message, $tagtext);
	return $postincludetags;
}

//获取内容中包含的TAG
function getincluetags($text, $tagtext) {
	$resultarr = array();
	$tagtext = str_replace('/', '\/', $tagtext);
	preg_match_all("/($tagtext)/", $text, $matches);
	if(!empty($matches[1]) && is_array($matches[1])) {
		foreach ($matches[1] as $value) {
			if(strlen($value)>2) $resultarr[$value] = $value;
		}
	}
	return implode("\t", $resultarr);
}

//信息TAG关联处理
function postspacetag($op, $type, $itemid, $tagarr) {
	global $_SGLOBAL;

	$colnumname = "space{$type}num";
	$deletetagidarr = $addtagidarr = $spacetagidarr = array();
	if($op == 'add') {
		if(!empty($tagarr['existsid'])) {
			$addtagidarr = $tagarr['existsid'];
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET '.$colnumname.'='.$colnumname.'+1 WHERE tagid IN ('.simplode($tagarr['existsid']).')');
		}
	} else {
		$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('spacetags').' WHERE itemid=\''.$itemid.'\'');
		while ($spacetag = $_SGLOBAL['db']->fetch_array($query)) {
			if(!empty($tagarr['existsid']) && in_array($spacetag['tagid'], $tagarr['existsid'])) {
				$spacetagidarr[] = $spacetag['tagid'];
			} else {
				$deletetagidarr[] = $spacetag['tagid'];
			}
		}
		foreach ($tagarr['existsid'] as $etagid) {
			if(!empty($spacetagidarr) && in_array($etagid, $spacetagidarr)) {
			} else {
				$addtagidarr[] = $etagid;
			}
		}
		if(!empty($deletetagidarr)) {
			$_SGLOBAL['db']->query('DELETE FROM '.tname('spacetags').' WHERE itemid='.$itemid.' AND tagid IN ('.simplode($deletetagidarr).')');
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET  '.$colnumname.'='.$colnumname.'-1 WHERE tagid IN ('.simplode($deletetagidarr).')');
		}
		if(!empty($addtagidarr)) {
			$_SGLOBAL['db']->query('UPDATE '.tname('tags').' SET '.$colnumname.'='.$colnumname.'+1 WHERE tagid IN ('.simplode($addtagidarr).')');
		}
	}
	//TAG
	if(!empty($tagarr['nonename'])) {
		foreach ($tagarr['nonename'] as $posttagname) {
			$insertsqlarr = array(
				'tagname' => $posttagname,
				'uid' => $_SGLOBAL['supe_uid'],
				'username' => $_SGLOBAL['supe_username'],
				'dateline' => $_SGLOBAL['timestamp'],
				$colnumname => 1
			);
			$addtagidarr[] = inserttable('tags', $insertsqlarr, 1);			
		}
	}
	if(!empty($addtagidarr)) {
		$insertstr = $comma = '';
		foreach ($addtagidarr as $tagid) {
			$insertstr .= $comma.'(\''.$itemid.'\',\''.$tagid.'\',\''.$_SGLOBAL['timestamp'].'\',\''.$type.'\')';
			$comma = ',';
		}
		$_SGLOBAL['db']->query('REPLACE INTO '.tname('spacetags').' (itemid, tagid, dateline, type) VALUES '.$insertstr);
	}
}

//获取相关信息ID
function getrelativeitemids($itemid, $typearr=array(), $num=10) {
	global $_SGLOBAL;

	$tagidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('spacetags')." WHERE itemid='$itemid'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagidarr[] = $value['tagid'];
	}
	if(empty($tagidarr)) return '';
	
	$sqlplus = '';
	if(!empty($typearr)) $sqlplus = "AND type IN (".simplode($typearr).")";
	$itemidarr = array();
	$query = $_SGLOBAL['db']->query("SELECT itemid FROM ".tname('spacetags')." WHERE tagid IN (".simplode($tagidarr).") AND itemid<>'$itemid' $sqlplus ORDER BY itemid DESC LIMIT 0, $num");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$itemidarr[] = $value['itemid'];
	}
	return implode(',', $itemidarr);
	
}
?>
