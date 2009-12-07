<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
!$iCMS->config['ishtm']&&redirect("未开启生成HTML设置，请到系统设置－>静态生成设置",__SELF__.'?do=setting&operation=html',5);
include iPATH.'include/catalog.class.php';
$catalog =new catalog();
switch ($operation) {
	case 'index':
		$Admin->MP(array("menu_html_all","menu_html_index"));
		include iCMS_admincp_tpl("html.index");
	break;
	case 'all':
		$Admin->MP("menu_html_all");
		include iCMS_admincp_tpl("html.all");
	break;
	case 'catalog':
		$Admin->MP(array("menu_html_all","menu_html_catalog"));
		include iCMS_admincp_tpl("html.catalog");
	break;
	case 'article':
		$Admin->MP(array("menu_html_all","menu_html_article"));
		include iCMS_admincp_tpl("html.article");
	break;
	case 'tag':
		$Admin->MP(array("menu_html_all","menu_html_tag"));
		include iCMS_admincp_tpl("html.tag");
	break;
	case 'page':
		$Admin->MP(array("menu_html_all","menu_html_page"));
		include iCMS_admincp_tpl("html.page");
	break;
case 'create':
	set_time_limit(0);
	$action=$_GET['action'];
	$cTime=$_GET['time']?$_GET['time']:1;
	isset($_GET['all'])&& $QUERY_STRING='&all';
	require_once(iPATH."include/function/template.php");
	if($action=='all'){
		redirect("全站更新，开始生成文章.....",__SELF__.'?do=html&operation=create&action=article&cid=all&all');
	}
	if($action=='index'){
		if(isset($_GET['all'])){
			$_GET['indexTPL']=$iCMS->config['indexTPL'];
			$_GET['indexname']=$iCMS->config['indexname'];
		}
		updateConfig($_GET['indexTPL'],'indexTPL');
		updateConfig($_GET['indexname'],'indexname');
		CreateConfigFile();
		MakeIndexHtm($_GET['indexTPL'],$_GET['indexname']);
		if(isset($_GET['all'])){
			redirect("全站更新完成!",__SELF__.'?do=html&operation=all');
		}else{
			redirect("网站首页更新完成!",__SELF__.'?do=html&operation=index');
		}
	}
	if($action=='catalog'){
		$cids		= $_GET['cid'];
		$cpageNum	= $_GET['cpn'];
		empty($cids) && alert("请选择栏目");
		is_array($cids) && $cids = implode(",", $cids);
		if(strstr($cids,'all')){
			$cids=substr($catalog->id(),0,-1);
			if(empty($cids)){
				_redirect("生成独立页面","栏目更新完成",'create&action=page&cid=all','catalog');
			}else{
				_header(__SELF__.'?do=html&operation=create&action=catalog&time='.$cTime.'&cpn='.$cpageNum.'&cid='.$cids.$QUERY_STRING);
			}
		}else{
			$cArray	=explode(',',$cids);
			$cCount	=count($cArray);
			$cpage	=isset($_GET['cpage'])?$_GET['cpage']:1;
			$k		=isset($_GET['k'])?$_GET['k']:0;
			$loop	=isset($_GET['loop'])?$_GET['loop']:0;
			$c		=MakeCatalogHtm($cArray[$k],$cpage,$loop,$cpageNum);
//Array ( [name] => 栏目6 [page] => 51 [loop] => 3 [pagesize] => 101 ) 
//var_dump($c);
			$text=empty($cpageNum) ?'':"指定生成".$cpageNum."页，";
			if($c['loop']>0 && $c['page']<=$c['pagesize']){
//				if($c['page']>$cpageNum && !empty($cpageNum)){
//					redirect($c['name']."共".$c['pagesize']."页，指定生成".$cpageNum."页，已生成".$c['page']."页",__SELF__.'?do=html&operation=create&action=catalog&time='.$cTime.'&cpn='.$cpageNum.'&cid='.$cids.'&k='.($k+1).$QUERY_STRING,$cTime);
//				}else{
					redirect($c['name']."共".$c['pagesize']."页，{$text}已生成".$c['page']."页",__SELF__.'?do=html&operation=create&action=catalog&time='.$cTime.'&cpn='.$cpageNum.'&cid='.$cids.'&k='.$k.'&cpage='.$c['page'].'&loop='.($c['loop']-1).$QUERY_STRING,$cTime);

//				}
				//_header('admincp.php?do=html&operation=create&action=catalog&cid='.$cids.'&k='.$k.'&cpage='.$c['page'].'&loop='.($c['loop']-1).$QUERY_STRING);
			}elseif($cCount>1 && $k<$cCount){
				redirect($c['name']." {$text}更新完成",__SELF__.'?do=html&operation=create&action=catalog&time='.$cTime.'&cpn='.$cpageNum.'&cid='.$cids.'&k='.($k+1).$QUERY_STRING,$cTime);
		//		_header('admincp.php?do=html&operation=create&action=catalog&cid='.$cids.'&k='.($k+1).$QUERY_STRING);
			}else{
				_redirect("生成独立页面","栏目更新完成",'create&action=page&cid=all','catalog');
			}
		}
	}
	if($action=='tag'){
		var_dump($_GET);
		$speed		=50;//生成速度
		$sids=$_GET['sortid'];
		$startid	=(int)$_GET['startid'];
		$endid		=(int)$_GET['endid'];
		$starttime	=$_GET['starttime'];
		$endtime	=$_GET['endtime'];
		$cpageNum	=0; $_GET['cpn'];
		$totle		=isset($_GET['totle'])?$_GET['totle']:0;
		$loop		=isset($_GET['loop'])?$_GET['loop']:1;
		$i			=isset($_GET['i'])?$_GET['i']:0;
		if($sids){
			empty($sids) && alert("请选择分类");
			is_array($sids) && $sids = implode(",", $sids);
			if(strstr($sids,'all')){
				$tSort	= $iCMS->cache('tag.sort','include/syscache',0,true);
				if($tSort)foreach($tSort as $i=>$val){
					$_sid[]=$val['id'];
				}
				$sids=implode(",", $_sid);
				if(empty($sids)){
					_redirect("生成列表","标签更新完毕",'create&action=page&cid=all','tag');
				}else{
//					_header(__SELF__.'?do=html&operation=create&action=article&cid='.$cids.$QUERY_STRING);
					_header(__SELF__.'?do=html&operation=create&action=tag&time='.$cTime.'&cpn='.$cpageNum.'&sortid='.$sids.$QUERY_STRING);
				}
			}else{
				$sArray	=explode(',',$sids);
				$sCount	=count($sArray);
				$cpage	=isset($_GET['cpage'])?$_GET['cpage']:1;
				$k		=isset($_GET['k'])?$_GET['k']:0;
//				$totle	=isset($_GET['totle'])?$_GET['totle']:0;
//				$loop	=isset($_GET['loop'])?$_GET['loop']:1;
//				$i		=isset($_GET['i'])?$_GET['i']:0;
				$rs		=$iCMS->db->getArray("SELECT `id`,`name` FROM #iCMS@__tags WHERE `sortid` in ($sids) and `visible`='1' order by id DESC");
				empty($totle)&&$totle=count($rs);
				$tloop=ceil($totle/$speed);
				if($loop<=$tloop){
					$max=$i+$speed>$totle?$totle:$i+$speed;
					for($j=$i;$j<$max;$j++){
						$c		=MakeTagHtm($rs[$j]['id'],$cpage,$loop,$cpageNum);
						echo "标签: [".$rs[$j]['name']."] 生成…<span style='color:green;'>√</span><br />";flush();
					}
					_header(__SELF__.'?do=html&operation=create&action=tag&sortid='.$sids.'&totle='.$totle.'&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
				}else{
					_redirect("生成列表","标签更新完毕",'create&action=page&cid=all','tag');
				}
			}
		}elseif($startid && $endid){
			($startid>$endid &&!isset($_GET['g'])) && alert("开始ID不能大于结束ID");
			empty($totle)&&$totle=($endid-$startid)+1;
			empty($i)&&$i=$startid;
			$tloop=ceil($totle/$speed);
			if($loop<=$tloop){
				$max=$i+$speed>$endid?$endid:$i+$speed;
				for($j=$i;$j<=$max;$j++){
					MakeTagHtm($j);
					echo "标签ID:{$j}生成…<span style='color:green;'>√</span><br />";flush();
				}
 				_header(__SELF__.'?do=html&operation=create&action=tag&startid='.$startid.'&endid='.$endid.'&g&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
			}else{
				redirect("标签更新完毕！",__SELF__.'?do=html&operation=tag');
			}
		}elseif($starttime){
			$s	= strtotime($starttime);
			$e	= empty($endtime)?time()+86400:strtotime($endtime);
			$rs=$iCMS->db->getArray("SELECT id FROM #iCMS@__tags WHERE `updatetime`>='$s' and `updatetime`<='$e' and `visible`='1' order by id DESC");
			empty($totle)&&$totle=count($rs);
			$tloop=ceil($totle/$speed);
			if($loop<=$tloop){
				$max=$i+$speed>$totle?$totle:$i+$speed;
				for($j=$i;$j<$max;$j++){
					MakeTagHtm($rs[$j]['id']);
					echo "标签ID:".$rs[$j]['id']."生成…<span style='color:green;'>√</span><br />";flush();
				}
				_header(__SELF__.'?do=html&operation=create&action=tag&starttime='.$starttime.'&endtime='.$endtime.'&totle='.$totle.'&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
			}else{
				redirect("标签更新完毕！",__SELF__.'?do=html&operation=tag');
			}
		}else{
			alert("请选择方式");
		}
	}
	if($action=='page'){
		$cids=$_GET['cid'];
		empty($cids) && alert("请选择栏目");
		is_array($cids) && $cids = implode(",", $cids);
		if(strstr($cids,'all')){
			$cids=substr($catalog->id(0,'page'),0,-1);
			if(empty($cids)){
				_redirect("生成首页","独立页面更新完毕",'index','page');
			}else{
				_header(__SELF__.'?do=html&operation=create&action=page&cid='.$cids.$QUERY_STRING);
			}
		}else{
			$cArray	=explode(',',$cids);
			$k		=isset($_GET['k'])?$_GET['k']:0;
			if($pagename=MakePageHtm($cArray[$k])){
				redirect($pagename.'更新完毕',__SELF__.'?do=html&operation=create&action=page&cid='.$cids.'&k='.($k+1).$QUERY_STRING);
			}
		}
		_redirect("生成首页","独立页面更新完毕",'index','page');
	}
	if($action=='article'){
		$speed		=50;//生成速度
		$cids		=$_GET['cid'];
		$startid	=(int)$_GET['startid'];
		$endid		=(int)$_GET['endid'];
		$starttime	=$_GET['starttime'];
		$endtime	=$_GET['endtime'];
		$totle		=isset($_GET['totle'])?$_GET['totle']:0;
		$loop		=isset($_GET['loop'])?$_GET['loop']:1;
		$i			=isset($_GET['i'])?$_GET['i']:0;
		if($cids){
			empty($cids) && alert("请选择栏目");
			is_array($cids) && $cids = implode(",", $cids);
			if(strstr($cids,'all')){
				$cids=substr($catalog->id(),0,-1);
				if(empty($cids)){
					_redirect("生成列表","文章更新完毕",'create&action=catalog&cid=all','article');
				}else{
					_header(__SELF__.'?do=html&operation=create&action=article&cid='.$cids.$QUERY_STRING);
				}
			}else{
				$cArray	=explode(',',$cids);
				$cCount	=count($cArray);
				$k		=isset($_GET['k'])?$_GET['k']:0;				
				$rs		= $iCMS->cache('article.sort.id','include/syscache',0,true);
				if(empty($rs)){
					$rs		=$iCMS->db->getArray("SELECT id FROM #iCMS@__article WHERE cid in ($cids) and `visible`='1' order by id DESC");
					$iCMS->cache(false,'include/syscache',0,true,false);
					$iCMS->addcache('article.sort.id',$rs,86400);
				}
				empty($totle)&&$totle=count($rs);
				$tloop=ceil($totle/$speed);
				if($loop<=$tloop){
					$max=$i+$speed>$totle?$totle:$i+$speed;
					for($j=$i;$j<$max;$j++){
						MakeArticleHtm($rs[$j]['id']);
						echo "文章ID:".$rs[$j]['id']."生成…<span style='color:green;'>√</span><br />";flush();
					}
					_header(__SELF__.'?do=html&operation=create&action=article&cid='.$cids.'&totle='.$totle.'&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
				}else{
					_redirect("生成列表","文章更新完毕",'create&action=catalog&cid=all','article');
				}
			}
		}elseif($startid && $endid){
			($startid>$endid &&!isset($_GET['g'])) && alert("开始ID不能大于结束ID");
			empty($totle)&&$totle=($endid-$startid)+1;
			empty($i)&&$i=$startid;
			$tloop=ceil($totle/$speed);
			if($loop<=$tloop){
				$max=$i+$speed>$endid?$endid:$i+$speed;
				for($j=$i;$j<=$max;$j++){
					MakeArticleHtm($j);
					echo "文章ID:{$j}生成…<span style='color:green;'>√</span><br />";flush();
				}
 				_header(__SELF__.'?do=html&operation=create&action=article&startid='.$startid.'&endid='.$endid.'&g&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
			}else{
				redirect("文章更新完毕！",__SELF__.'?do=html&operation=article');
			}
		}elseif($starttime){
			$s	= strtotime($starttime);
			$e	= empty($endtime)?time()+86400:strtotime($endtime);
			$rs	= $iCMS->cache('article.time.id','include/syscache',0,true);
			if(empty($rs)){
				$rs=$iCMS->db->getArray("SELECT id FROM #iCMS@__article WHERE `pubdate`>='$s' and `pubdate`<='$e' and `visible`='1' order by id DESC");
				$iCMS->cache(false,'include/syscache',0,true,false);
				$iCMS->addcache('article.time.id',$rs,86400);
			}
			empty($totle)&&$totle=count($rs);
			$tloop=ceil($totle/$speed);
			if($loop<=$tloop){
				$max=$i+$speed>$totle?$totle:$i+$speed;
				for($j=$i;$j<$max;$j++){
					MakeArticleHtm($rs[$j]['id']);
					echo "文章ID:".$rs[$j]['id']."生成…<span style='color:green;'>√</span><br />";flush();
				}
				_header(__SELF__.'?do=html&operation=create&action=article&starttime='.$starttime.'&endtime='.$endtime.'&totle='.$totle.'&loop='.($loop+1).'&i='.$j.$QUERY_STRING);
			}else{
				redirect("文章更新完毕！",__SELF__.'?do=html&operation=article');
			}
		}else{
			alert("请选择方式");
		}
	}
break;
}
function _redirect($T1,$T2,$U1,$U2){
	global $QUERY_STRING;
	if(isset($_GET['all'])){
		redirect($T2.','.$T1."开始.....",__SELF__.'?do=html&operation='.$U1.$QUERY_STRING);
	}else{
		redirect($T2,__SELF__.'?do=html&operation='.$U2);
	}
}
?>
