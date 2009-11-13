<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_comment($vars,&$iCMS){
	if(!$iCMS->config['iscomment']){return false;}
    $mid =isset($vars['mid'])?(int)$vars['mid']:"0";
	if(isset($vars['call'])){
		$iCMS->assign('mid',$mid);
		if(in_array($vars['call'],array('js','frame'))){
			echo $iCMS->iPrint("iSYSTEM","comment_show_".$vars['call']);
		}
	}else if(isset($vars['editor']) && $iCMS->config['iscomment']){
		$width=$vars['width']?$vars['width']:'98%';
		$height=$vars['height']?$vars['height']:'140';
		if($vars['editor']=='yes'){
			include_once(iPATH."include/fckeditor.php");
			$editor = new FCKeditor('commentext') ;
			$editor->BasePath	= $iCMS->config['url'];
			$editor->Width	= $width ;
			$editor->Height	= $height;
			$editor->ToolbarSet = 'Basic';
			$editor->Value="&nbsp;";
			$iCMS->assign('iseditor',true);
			$iCMS->assign('editor',$editor->CreateHtml());
		}else{
			$iCMS->assign('iseditor',false);
			$iCMS->assign('style',array('width'=>$width,'height'=>$height));
		}
		$iCMS->assign('isanonymous',$iCMS->config['anonymous']);
		$iCMS->assign('title',$iCMS->get['title']);
		$iCMS->assign('aid',(int)$iCMS->get['id']);
		echo $iCMS->iPrint("iSYSTEM","comment.editor");
	}else{
		$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
    	$maxperpage =isset($vars['row'])?(int)$vars['row']:"10";
    	$whereSQL="`mid`='$mid' and `isexamine`='1'";
    	isset($vars['sortid']) && $whereSQL.=" and `sortid`='".(int)$vars['sortid']."'";
    	$iCMS->get['id'] && $vars['type']!='all' && $whereSQL.=" AND `aid`='".(int)$iCMS->get['id']."'";
		switch ($vars['orderby']) {
			case "hot":	$orderSQL=" ORDER BY up+against DESC";	break;
			case "new":	$orderSQL=" ORDER BY `addtime` DESC";		break;
			default:	$orderSQL=" ORDER BY `id` DESC";
		}
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__comment` WHERE {$whereSQL}");
		$offset	=0;
		if($vars['page']){
			$pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
			$pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
			$offset	= $iCMS->multi(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>$iCMS->language('page:comment'),'url'=>$iCMS->url,'nowindex'=>$GLOBALS['page'],'pagenav'=>$pagenav,'pnstyle'=>$pnstyle));
		}
		if($vars['cache']==false||isset($vars['page'])){
			$iCMS->config['iscache']=false;
			$rs = '';
		}else{
			$iCMS->config['iscache']=true;
			$cacheName='comment/'.md5($whereSQL.$orderSQL);
			$rs=$iCMS->cache($cacheName);
		}
		if(empty($rs)){
			include_once(iPATH.'include/ubb.fun.php');
			$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__comment` WHERE {$whereSQL}{$orderSQL} LIMIT {$offset},{$maxperpage}");
//echo $iCMS->db->last_query;
//$iCMS->db->last_query='explain '.$iCMS->db->last_query;
//$explain=$iCMS->db->getRow($iCMS->db->last_query);
//var_dump($explain);
			$_count=count($rs);
			$ln=($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
			for ($i=0;$i<$_count;$i++){
				$rs[$i]['title']=$rs[$i]['atitle'];
				$rs[$i]['url']=($iCMS->config['ishtm']?$iCMS->config['url'].'/':$iCMS->dir).'comment.php?aid='.$rs[$i]['aid'].'&mid='.$rs[$i]['mid'].'&sortid='.$rs[$i]['sortid'];
				$rs[$i]['lou']=$total-($i+$ln*$maxperpage);
				$rs[$i]['content']=ubb($rs[$i]['contents']);
				$rs[$i]['contents']=cQuote($rs[$i]['quote']).$rs[$i]['content'];
				if($rs[$i]['reply']){
					$reply=explode('||',$rs[$i]['reply']);
					$rs[$i]['reply']=$reply[0]=='admin'?'<strong>'.$iCMS->language('reply:admin').'</strong>'.$reply[1]:'<strong>'.$iCMS->language('reply:author').'</strong>'.$reply[1];
				}
			}
			$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
		}
		$iCMS->assign('title',$iCMS->get['title']);
		return $rs;
	}
}
function cQuote($cid=0,$i=0){
	global $iCMS;
	if($cid){
		$i++;
		$rs=$iCMS->db->getRow("SELECT `quote`,`username`,`addtime`,`contents` FROM `#iCMS@__comment` WHERE  `id`='$cid'");
		$text='<div class="quote">';
		$i<52 && $rs->quote && $text.=cQuote($rs->quote,$i);
//		$text.='<span>----- 以下引用 <strong><em>'.$rs->username.'</em></strong> 于 '.get_date($rs->addtime,'Y-m-d H:i').' 的发言 -----</span><p>'.$rs->contents. '</p></div>';
		$text.='<span>'.$rs->username.'的原贴：</span><br /><p>'.$rs->contents. '</p></div>';
		//84<span style="float: right;">#'.$i.'楼</span>
		//$text.='</div>';
		return $text;
	}
}
?>