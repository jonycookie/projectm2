<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
include iPATH.'include/catalog.class.php';
if($operation!='post'){
	$id			= (int)$_GET['id'];
	$mid		= (int)$_GET['mid'];
	$table		= $_GET['table'];
	$__TABLE__	= $table.'_content';
	(empty($mid)||empty($table)) && alert("参数错误!");
}
switch($operation){
case 'add':
	include iPATH.'include/from.fun.php';
	$model	= $iCMS->db->getRow("SELECT * FROM `#iCMS@__model` where id='$mid'");
//	$__MODEL__	= $iCMS->cache('model.id','include/syscache',0,true);
//	$model		= $__MODEL__[$mid];
	$fArray	= explode(',',$model->field);
	$_count	= count($fArray);
	$rs=array();
	$id && $rs=$iCMS->db->getRow("SELECT * FROM `#iCMS@__$__TABLE__` WHERE id='$id'",ARRAY_A);
	$rs['cid']=empty($rs['cid'])?intval($_GET['cid']):$rs['cid'];
	$rs['pubdate']=empty($id)?get_date('',"Y-m-d H:i:s"):get_date($rs['pubdate'],'Y-m-d H:i:s');
	empty($rs['editor']) && $rs['editor']=empty($Admin->admin->name)?$Admin->admin->username:$Admin->admin->name;
	empty($rs['userid']) && $rs['userid']=$Admin->uId;
	$rs['mid'] =$mid;
	$form	= FormArray($mid,$fArray,$rs);
	$fcount	= count($form);
	include iCMS_admincp_tpl("content.add");
break;
case 'manage':
//	$Admin->MP(array("menu_article_manage","menu_article_draft","menu_article_user_manage","menu_article_user_draft"));
	$__MODEL__	= $iCMS->cache('model.id','include/syscache',0,true);
	$model		= $__MODEL__[$mid];
	$catalog 	= new catalog();

	$cid	= (int)$_GET['cid'];
	$act	= $_GET['act'];
	$sql	=" where ";
	$sql.=$_GET['type']=='draft'?"`visible` ='0'":"`visible` ='1'";
	$sql.=$act=='user'?" AND `postype`='0'":" AND `postype`='1'";

	$_GET['title'] 			&& $sql.=" AND `title` like '%{$_GET['title']}%'";
	$_GET['tag'] 			&& $sql.=" AND `tags` REGEXP '[[:<:]]".preg_quote(rawurldecode($_GET['tag']),'/')."[[:>:]]'";
	isset($_GET['at']) 		&& $sql.=" AND `type` REGEXP '[[:<:]]".preg_quote($_GET['at'], '/')."[[:>:]]'";
	isset($_GET['userid']) 	&& $sql.=" AND `userid`='".(int)$_GET['userid']."'";
	$cid=$Admin->CP($cid)?$cid:"0";
	if($cid){
		if(isset($_GET['sub'])){
			$sql.=" AND ( cid IN(".$catalog->id($cid).$cid.")";
		}else{
			$sql.=" AND ( cid ='$cid'";
		}
		$sql.=" OR `vlink` REGEXP '[[:<:]]".preg_quote($cid, '/')."[[:>:]]')";
	}else{
		$Admin->cpower && $sql.=" AND cid IN(".implode(',',$Admin->cpower).")";
	}
//	isset($_GET['nopic'])&&$sql.=" AND `pic` =''";
	$_GET['starttime'] 	&& $sql.=" and `pubdate`>='".strtotime($_GET['starttime'])."'";
	$_GET['endtime'] 	&& $sql.=" and `pubdate`<='".strtotime($_GET['endtime'])."'";
	
	$act=='user' && $uri.='&act=user';
	$_GET['type']=='draft' && $uri.='&type=draft';
	isset($_GET['userid']) && $uri.='&userid='.(int)$_GET['userid'];
	isset($_GET['keyword']) && $uri.='&keyword='.$_GET['keyword'];
	isset($_GET['tag']) && $uri.='&tag='.$_GET['tag'];
	
	$orderby=$_GET['orderby']?$_GET['orderby']:"id DESC";
	$maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:20;
	$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__$__TABLE__` {$sql} order by {$orderby}");
	page($total,$maxperpage,"条记录");
	$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__$__TABLE__`{$sql} order by {$orderby} LIMIT {$firstcount} , {$maxperpage}");
	$_count=count($rs);
//echo $iCMS->db->func_call;
	include iCMS_admincp_tpl("content.manage");
break;
case 'visible':
	$v=(int)$_GET['v'];
	if($v=='1'){
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `visible` = '0' WHERE `id` ='$id'");
	}else{
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `visible` = '1' WHERE `id` ='$id'");
	}
	_Header();
break;
case 'delvlink':
	$cid= (int)$_GET['cid'];
	$id && $vlink=$iCMS->db->getValue("SELECT vlink FROM `#iCMS@__$__TABLE__` WHERE `id`='$id'");
	$vlinkArray	= explode(',',$vlink);
	$key		= array_search($cid,$vlinkArray);
	unset($vlinkArray[$key]);
	$vlink		= implode(',',$vlinkArray);
	$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `vlink` = '$vlink' WHERE `id` ='$id'");
	_Header();
break;
case 'updateHTML':
	empty($id) && alert("请选择要更新的内容");
	include_once(iPATH."include/function/template.php");
	MakeContentHtm($mid,$id) && alert('更新完成!',"url:1");
break;
case 'del':
	!$id && alert("请选择要删除的内容");
	delContent($mid,$id) && alert('成功删除!',"url:1");
break;
case 'post':
	switch($action){
	case 'save':
		set_time_limit(0);
		//系统默认字段
		$SystemField= getSystemField();
		//字段定义
		$__FIELD__	= $iCMS->cache('field.model','include/syscache',0,true);
		$__MODEL__	= $iCMS->cache('model.id','include/syscache',0,true);
		$id			= $_POST['id'];
		$mid		= $_POST['mid'];
		$table		= $_POST['table'];
		$model		= $__MODEL__[$mid];
		$varArray   = array();
		if($_POST['mVal'])foreach($_POST['mVal'] as $field=>$value){
			if(in_array($field,$SystemField)){
			    switch($field){
			    	case "userid":	$value	= $userid=intval($value);break;
			    	case "cid":
			    		$value	= $cid	= intval($value);
					    empty($value) && alert('请选择所属栏目');
			    	break;
			    	case "order":	$value	= intval($value);break;
			    	case "top":		$value	= intval($value);break;
			    	case "title":	
				    	$value	= $title = dhtmlspecialchars($value);
				    	empty($value) && alert('标题不能为空！');
			    	break;
			    	case "editor":	$value	= dhtmlspecialchars($value);break;
			    	case "tags":	$value	= dhtmlspecialchars($value);break;
			    	case "type":	$value	= empty($value)?'0':implode(',',$value);break;
			    	case "vlink":	$value	= implode(',',$value);break;
			    	case "postype":	$value	= empty($value)?intval($value):"1";break;
			    	case "pubdate":	$value	= $pubdate = _strtotime($value);break;
			    	case "customlink":
			    		$value	= $customlink =dhtmlspecialchars($value);
					    if($value){
							for($i=0;$i<strlen($value);$i++){
								!preg_match("/[a-zA-Z0-9_\-~".preg_quote($iCMS->config['CLsplit'],'/')."]/",$value{$i}) && alert('自定链接只能由英文字母、数字或_-~组成(不支持中文)');
							}
						}
			    	break;
			    }
			}elseif($info = $__FIELD__[$field][$mid]?$__FIELD__[$field][$mid]:$__FIELD__[$field][0]){
				switch($info['type']){
					case "number":
						$value = intval($value);
					break;
					case "calendar":
						$value = _strtotime($value);
					break;
					case in_array($info['type'],array('text','textarea','radio','select','email','url','image','upload')):
						$value = dhtmlspecialchars($value);
					break;
					case in_array($info['type'],array('checkbox','multiple')):
						$value	= implode(',',$value);
					break;
					case 'editor':
						$value = $value;
					break;
					default:$value = dhtmlspecialchars($value);
				}
			}
			WordFilter($value) && alert($field.'字段包含被系统屏蔽的字符，请返回重新填写。');
			$varArray[$field] = $value;
			$PF[]=$field;
		}
		empty($varArray['customlink']) && $varArray['customlink']=pinyin($varArray['title'],$iCMS->config['CLsplit']);
//	    $remote		= isset($_POST['remote'])	?true:false;
//	    $dellink	= isset($_POST['dellink'])	?true:false;
//	    $autopic	= isset($_POST['autopic'])	?true:false;
//	    $visible	= isset($_POST['draft'])?"0":"1";
//	    $remote && remote($body);
//	    (!$remote&&$autopic) && remote($body,true);	    
//		$col 		= $iCMS->db->getCol("describe `#iCMS@__$__TABLE__`");
		$__TABLE__	= $table.'_content';
		$MF			= explode(',',$model['field']);
		$diff		= array_diff_values($PF,$MF);
		if($diff['-'])foreach($diff['-'] AS $field){$varArray[$field] = '';}//缺少的字段 填认空值

		if(empty($id)){
			empty($userid) && $varArray['userid']=$Admin->uId;
			$varArray['hits']=$varArray['digg']=$varArray['comments']=0;
			$varArray['visible']="1";
		    $checkCL=$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__$__TABLE__` where `customlink` ='$customlink'");
		    if($iCMS->config['repeatitle']){
		    	$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__$__TABLE__` where `title` = '$title'") && alert('该标题内容已经存在!请检查是否重复');
		    	$checkCL && alert('该自定链接已经存在!请另选一个');
		    }else{
				$checkCL && $customlink.=$iCMS->config['CLsplit'].random(6,1);
		    }
		    
		    $iCMS->db->insert($__TABLE__,$varArray);
			$id	= $iCMS->db->insert_id;
	    	addtags($varArray['tags']);tags_cache();
			$iCMS->config['ishtm'] && include_once(iPATH."include/function/template.php");
			MakeContentHtm($mid,$id); //生成静态
			$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='$cid' LIMIT 1 ");
			$moreaction=array(
				array("text"=>"编辑该内容","url"=>__SELF__."?do=content&operation=add&table=".$table."&mid=".$mid."&id=".$id),
				array("text"=>"继续添加内容","url"=>__SELF__."?do=content&operation=add&table=".$table."&mid=".$mid."&cid=".$cid),
				array("text"=>"查看该内容","url"=>$iCMS->iurl('content',array('mId'=>$mid,'id'=>$id,'link'=>$customlink,'dir'=>$iCMS->cdir($catalog->catalog[$cid]),'pubdate'=>$pubdate)),"o"=>'target="_blank"'),
				array("text"=>"查看网站首页","url"=>"../index.php","o"=>'target="_blank"')
			);
			redirect("添加完成!",__SELF__."?do=content&operation=manage&table=".$table."&mid=".$mid,'10',$moreaction);
		}else{
		    $checkCL=$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__$__TABLE__` where `customlink` ='$customlink' AND `id` !='$id'");
		     if($iCMS->config['repeatitle']){
		     	$checkCL && alert('该自定链接已经存在!请另选一个');
		     }else{
		     	$checkCL && $customlink.=$iCMS->config['CLsplit'].random(6,1);
		     }
			$art=$iCMS->db->getRow("SELECT `cid`,`tags` FROM `#iCMS@__$__TABLE__` where `id` ='$id'");
			TagsDiff($tags,$art->tags);
			tags_cache();
			$iCMS->db->update($__TABLE__,$varArray,array('id'=>$id));
			$iCMS->config['ishtm'] && include_once(iPATH."include/function/template.php");
			MakeContentHtm($mid,$id);
			if($art->cid!=$cid) {
				$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count-1 WHERE `id` ='{$art->cid}' LIMIT 1 ");
				$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='$cid' LIMIT 1 ");
			}
			redirect("编辑完成!",__SELF__."?do=content&operation=manage&table=".$table."&mid=".$mid,'3');
		}
	break;
	case 'del':
		empty($_POST['id']) && alert("请选择要删除的内容");
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $id){delArticle($id);}
		}
		alert('全部成功删除!',"url:1");
	break;
	case 'passed':
		empty($_POST['id']) && alert("请选择要显示的内容");
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `visible` = '1' WHERE `id` IN ($ids)");
		}
		_Header();
	break;
	case 'top':
		empty($_POST['id']) && alert("请选择要设置置顶权重的内容");
		$top=$_POST['top'];
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `top` = '$top' WHERE `id` IN ($ids)");
		}
		_Header();
	break;
	case 'passTimeALL':
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `visible` = '1',pubdate='".time()."' WHERE `visible` = '0'");		
		_Header(__REF__);
	break;
	case 'passALL':
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `visible` = '1' WHERE `visible` = '0'");		
		_Header(__REF__);
	break;
	case 'TimeALL':
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET pubdate='".time()."' WHERE `visible` = '0'");		
		_Header(__REF__);
	break;
	case 'cancel':
		empty($_POST['id']) && alert("请选择要隐藏的内容");
		if(is_array($_POST['id'])){
			$ids=implode(',',$_POST['id']);
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `visible` = '0' WHERE `id` IN ($ids)");
		}
		_Header();
	break;
	case 'updateHTML':
		empty($_POST['id']) && alert("请选择要更新的内容");
		include_once(iPATH."include/function/template.php");
		$i=0;
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $aid){
				MakeArticleHtm($aid) && $i++;
			}
		}
		alert($i.'个文件更新完成!',"url:1");
	break;
	case 'contentype':
		empty($_POST['id']) && alert("请选择要更改的内容");
	    $type = empty($_POST['type'])?"0":implode(',',$_POST['type']);
	    $ids=implode(',',$_POST['id']);
		$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `type` = '$type' WHERE `id` IN ($ids)");
		alert('内容属性更改完成!',"url:1");
	break;
	case 'keyword':
		empty($_POST['id']) && alert("请选择内容");
		$ids=implode(',',$_POST['id']);
		if($_POST['pattern']=='replace'){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `keywords` = '".dhtmlspecialchars($_POST['keyword'])."' WHERE `id` IN ($ids)");
		}elseif($_POST['pattern']=='addto'){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `keywords` = CONCAT(keywords,',".dhtmlspecialchars($_POST['keyword'])."') WHERE `id` IN ($ids)");
		}
		alert('内容关键字更改完成!',"url:1");
	break;
	case 'tag':
		empty($_POST['id']) && alert("请选择内容");
		$ids=implode(',',$_POST['id']);
		if($_POST['pattern']=='replace'){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `tags` = '".dhtmlspecialchars($_POST['tag'])."' WHERE `id` IN ($ids)");
		}elseif($_POST['pattern']=='addto'){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `tags` = CONCAT(tags,',".dhtmlspecialchars($_POST['tag'])."') WHERE `id` IN ($ids)");
		}
		alert('内容标签更改完成!',"url:1");
	break;
	case 'vlink':
		empty($_POST['id']) && alert("请选择内容");
		$vlink	= empty($_POST['vlink'])?"":implode(',',$_POST['vlink']);
		$ids=implode(',',$_POST['id']);
		if($_POST['pattern']=='replace'){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `vlink` = '".$vlink."' WHERE `id` IN ($ids)");
		}elseif($_POST['pattern']=='addto'){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `vlink` = CONCAT(vlink,',".$vlink."') WHERE `id` IN ($ids)");
		}
		alert('内容虚拟链接更改完成!',"url:1");
	break;
	case 'thumb':
		empty($_POST['id']) && alert("请选择要提取缩略图的内容");
		if(is_array($_POST['id'])){
			$UploadDir 	= $iCMS->config['uploadfiledir']."/";
			foreach($_POST['id'] AS $id){
				$content	= $iCMS->db->getValue("SELECT ad.body FROM `#iCMS@__$__TABLE__` a, `#iCMS@__$__TABLE__data` ad WHERE a.id=ad.aid and a.id='$id'");
				$img 		= array();
				preg_match_all("/src=[\"|'| ]{0,}(\/(.*)\.(gif|jpg|jpeg|bmp|png))/isU",$content,$img);
				$_array = array_unique($img[1]);
				foreach($_array as $key =>$value){
					$value = getfilepath(trim($value),'','-');
					if(file_exists(getfilepath($value,iPATH,'+'))){
						$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `pic` = '$value' WHERE `id` = '$id'");
						break;
					}
				}
			}
		}
		alert('成功提取缩略图',"url:1");
	break;
	case 'move':
		empty($_POST['id']) && alert("请选择要移动的内容");
		!$_POST['cataid'] && alert("请选择目标栏目");
		$cid=intval($_POST['cataid']);
		if(is_array($_POST['id'])){
			foreach($_POST['id'] AS $id){
				$id=intval($id);
				$acid=$iCMS->db->getValue("SELECT `cid` FROM `#iCMS@__$__TABLE__` where `id` ='$id'");
				$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET cid='$cid' WHERE `id` ='$id'");
				if($acid!=$cid) {
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count-1 WHERE `id` ='{$acid}' LIMIT 1 ");
					$iCMS->db->query("UPDATE `#iCMS@__catalog` SET `count` = count+1 WHERE `id` ='{$cid}' LIMIT 1 ");
				}
			}
		}
		alert('成功移动到目标栏目',"url:1");
	break;
	default:
		foreach($_POST['order'] AS $id=>$order){
			$iCMS->db->query("UPDATE `#iCMS@__$__TABLE__` SET `order` = '$order' WHERE `id` ='$id'");
		}
		_Header();
	}
break;
}
?>
