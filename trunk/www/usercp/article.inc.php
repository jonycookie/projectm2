<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
include iPATH.'include/catalog.class.php';
switch($operation){
case 'add':
	$catalog =new catalog();
	$id=(int)$_GET['id'];
	$rs=array();
	$id && $rs=$iCMS->db->getRow("SELECT a.*,ad.tpl,ad.body,ad.subtitle FROM `#iCMS@__article` a LEFT JOIN `#iCMS@__articledata` ad ON a.id=ad.aid WHERE a.id='$id' and a.userid='$member->uId'",ARRAY_A);
	$rs['pubdate']=empty($id)?get_date('',"Y-m-d H:i:s"):get_date($rs['pubdate'],'Y-m-d H:i:s');
	$rootid=empty($rs['cid'])?intval($_GET['cid']):$rs['cid'];
	$cata_option=user_catalog_select($rootid,0,1,'channel=1&list');
	empty($rs['editor']) && $rs['editor']=empty($member->admin->name)?$member->admin->username:$member->admin->name;
	empty($rs['userid']) && $rs['userid']=$member->uId;
	include iCMS_usercp_tpl("article.add");
break;
case 'del':
	$id=intval($_GET['id']);
	!$id && alert("请选择要删除的文章");
	delArticle($id,$member->uId,0) && alert('成功删除!',"url:1");
break;
case 'manage':
	$catalog =new catalog();
	$cid	= (int)$_GET['cid'];
	$sql	=" where 1=1";
	$cid && $sql.=" AND `cid` ='$cid'";
//	$sql.=$_GET['type']=='draft'?"`visible` ='0'":"`visible` ='1'";
	$orderby=$_GET['orderby']?$_GET['orderby']:"id DESC";
	$maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:10;
	$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__article` {$sql} AND `postype`='0' AND `userid`='$member->uId' order by {$orderby}");
	page($total,$maxperpage,"篇文章");
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__article` {$sql} AND `postype`='0' AND `userid`='$member->uId' order by {$orderby} LIMIT {$firstcount} , {$maxperpage}");
	$_count=count($rs);
	include iCMS_usercp_tpl("home");
break;
case 'post':
	switch($action){
	case 'save':
		set_time_limit(0);
	    $aid		= intval($_POST['aid']);
	    $cid		= intval($_POST['catalog']);
		$title		= dhtmlspecialchars($_POST['title']);
		$pic		= dhtmlspecialchars($_POST['pic']);
	    $source		= dhtmlspecialchars($_POST['source']);
	    $author		= dhtmlspecialchars($_POST['author']);
	    $editor		= dhtmlspecialchars($_POST['editor']);
	    $description= dhtmlspecialchars($_POST['description']);
	    $keywords	= dhtmlspecialchars($_POST['keywords']);
	    $pubdate	= _strtotime($_POST['pubdate']);
//	    $visible	= isset($_POST['draft'])?"0":"1";
	    $tags		= $keywords;
	    $userid		= $member->uId;
	    $top		= '0';
	    $type		= "0";
	    $vlink		= "";
		$subtitle	= '';
		$stitle		= '';
	    $tags		= '';
	    $customlink	= '';
	    $url		= '';
	    $tpl		= '';
		$related	= '';
	    
	    $remote		= false;
	    $dellink	= false;
	    $autopic	= false;
		$postype	= "0";
	    $body		= str_replace(array("\n","\r","\t"),"",$_POST['content']);

	    empty($title) && alert('标题不能为空！');
	    empty($cid) && alert('请选择所属栏目');
	    empty($body) && empty($url) && alert('文章内容不能为空！');
		WordFilter($title) && alert('标题包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($pic) && alert('缩略图包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($source) && alert('出处包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($author) && alert('作者包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($description) && alert('摘要包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($keywords) && alert('关键字包含被系统屏蔽的字符，请返回重新填写。');
		WordFilter($body) && alert('文章内容包含被系统屏蔽的字符，请返回重新填写。');
	    
	    if($iCMS->config['autodesc']=="1" && !empty($iCMS->config['descLen']) && empty($description) && empty($url)){
	    	 $description=csubstr(HtmToText($body),$iCMS->config['descLen']);
	    }
//	    $remote && remote($body);
//	    (!$remote&&$autopic) && remote($body,true);
	    
		empty($customlink)&& $customlink=pinyin($title,$iCMS->config['CLsplit']);
		$catalog	= new catalog();
		$isexamine	= $catalog->catalog[$cid]['isexamine'];
		$visible	= $isexamine?'0':'1';
		if(empty($aid)){
			empty($userid) && $userid=$member->uId;
			$hits=$digg=$comments=0;
		    $iCMS->db->getValue("SELECT `id` FROM `#iCMS@__article` where `title` = '$title'") && alert('该标题的文章已经存在!请检查是否重复');
		    $iCMS->db->insert('article',compact('cid','title','stitle','customlink','url','source','author','editor','userid','postype','keywords','tags','description','related','pic','pubdate','hits' ,'digg','comments','type','vlink','top','visible'));
			$aid	= $iCMS->db->insert_id;
			$iCMS->db->insert('articledata',compact('aid','subtitle','tpl','body'));
			//insert_db_remote($body,$aid);
		    addtags($tags);tags_cache();
			if($isexamine){
				alert("此栏目文章需要管理员审核,请稍候..",'url:'.__SELF__."?do=article&operation=manage");
			}else{
				if($iCMS->config['ishtm'] && $visible){
					include_once(iPATH."include/function/template.php");
					MakeArticleHtm($aid);
				}
				$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='$cid' LIMIT 1 ");
				alert("文章添加完成!",'url:'.__SELF__."?do=article&operation=manage");
			}
		}else{
			$art=$iCMS->db->getRow("SELECT `cid`,`tags` FROM `#iCMS@__article` where `id` ='$aid'");
			TagsDiff($tags,$art->tags);tags_cache();
			$iCMS->db->update('article',compact('cid','title','stitle','customlink','url','source','author','editor','userid','postype','keywords','tags','description','related','pic','pubdate','type','vlink','top','visible'),array('id'=>$aid));
			if($iCMS->db->getValue("SELECT `id` FROM `#iCMS@__articledata` where `aid` ='$aid'")){
				$iCMS->db->update('articledata',compact('tpl','subtitle','body'),compact('aid'));
			}else{
				$iCMS->db->insert('articledata',compact('aid','subtitle','tpl','body'));
			}
//			insert_db_remote($body,$aid);
			if($isexamine){
				alert("此栏目文章需要管理员审核,请稍候..",'url:'.__SELF__."?do=article&operation=manage");
			}else{
				if($iCMS->config['ishtm'] && $visible){
					include_once(iPATH."include/function/template.php");
					MakeArticleHtm($aid);
				}
				if($art->cid!=$cid) {
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count-1 WHERE `id` ='{$art->cid}' LIMIT 1 ");
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='$cid' LIMIT 1 ");
				}
				alert("文章编辑完成!",'url:'.__SELF__."?do=article&operation=manage");
			}
		}
	break;
	case 'del':
		empty($_POST['id']) && alert("请选择要删除的文章");
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $id){delArticle($id,$member->uId,0);}
		}
		alert('全部成功删除!',"url:1");
	break;
	case 'pub':
		empty($_POST['id']) && alert("请选择要显示的文章");
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '1' WHERE `id` IN ($ids) AND `postype`='0' AND `userid`='$member->uId'");
		}
		_Header();
	break;
	case 'draft':
		empty($_POST['id']) && alert("请选择要移到草稿箱的文章");
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__article` SET `visible` = '0' WHERE `id` IN ($ids) AND `postype`='0' AND `userid`='$member->uId'");
		}
		_Header();
	break;
	}
break;
}
?>